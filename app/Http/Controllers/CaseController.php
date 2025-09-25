<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Log as DBLog;
use App\Models\PoliceCase;
use App\Models\Complaint;
use App\Models\User;
use App\Models\CaseAssignment;
use App\Models\Department;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use App\Services\CaseTypeDetectionService;

class CaseController extends Controller
{
    /**
     * Automatically convert a complaint into a police case.
     */
public function autoConvert($id, CaseTypeDetectionService $detector)
{
    try {
        $complaint = Complaint::findOrFail($id);

        // Prevent reconversion
        if ($complaint->is_converted) {
            return response()->json([
                'success' => false,
                'message' => 'This complaint has already been converted to a case.'
            ], 409);
        }

        $caseType = $detector->detectCaseType($complaint->statement);
        $meta = $detector->getCaseDetails($caseType);

        if (!$caseType || !$meta) {
            return response()->json([
                'success' => false,
                'message' => 'Only criminal complaints can be converted to cases.'
            ], 422);
        }

        $date = now()->format('Ymd');
        $caseTypeCode = strtoupper(str_replace(' ', '_', $caseType));
        $existingCount = PoliceCase::where('case_type', $caseType)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $caseNumber = "{$caseTypeCode}-{$date}-" . str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);

        $case = PoliceCase::create([
            'complaint_id' => $complaint->complaint_id,
            'department_id' => $meta['department_id'],
            'case_number' => $caseNumber,
            'case_type' => $caseType,
            'case_status' => 'Open',
            'priority' => $meta['priority'],
        ]);

        // Blockchain logging (optional, non-blocking)
        try {
            $txHash = hash('sha256', $case->case_number . now());
            Http::timeout(5)->post('http://localhost:3001/log-case-transaction', [
                'caseId' => $case->case_number,
                'txHash' => $txHash,
            ]);
        } catch (\Exception $e) {
            // Ignore failure silently
        }

        // Mark complaint as converted
        $complaint->is_converted = true;
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => "Complaint converted to case successfully.",
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Conversion failed: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Generate a report of all cases with pagination and statistics.
     */



public function report(Request $request)
{
    $from = $request->input('from');
    $to = $request->input('to');
    $status = $request->input('status');
    $assignedTo = $request->input('assigned_to');

    $query = PoliceCase::query();

    if ($from) {
        $query->whereDate('created_at', '>=', $from);
    }

    if ($to) {
        $query->whereDate('created_at', '<=', $to);
    }

    if ($status && $status !== 'all') {
        $query->where('case_status', $status);
    }

    if ($assignedTo) {
        $query->whereHas('assignments', function ($q) use ($assignedTo) {
            $q->whereHas('staff.user', function ($q2) use ($assignedTo) {
                $q2->whereRaw("CONCAT(fname, ' ', sname) LIKE ?", ["%{$assignedTo}%"]);
            })->orWhereHas('supervisor.user', function ($q2) use ($assignedTo) {
                $q2->whereRaw("CONCAT(fname, ' ', sname) LIKE ?", ["%{$assignedTo}%"]);
            });
        });
    }

    $cases = $query->orderByDesc('created_at')->paginate(10);

    // Stats base
    $statsBase = PoliceCase::query();

    if ($from) {
        $statsBase->whereDate('created_at', '>=', $from);
    }

    if ($to) {
        $statsBase->whereDate('created_at', '<=', $to);
    }

    if ($assignedTo) {
        $statsBase->whereHas('assignments', function ($q) use ($assignedTo) {
            $q->whereHas('staff.user', function ($q2) use ($assignedTo) {
                $q2->whereRaw("CONCAT(fname, ' ', sname) LIKE ?", ["%{$assignedTo}%"]);
            })->orWhereHas('supervisor.user', function ($q2) use ($assignedTo) {
                $q2->whereRaw("CONCAT(fname, ' ', sname) LIKE ?", ["%{$assignedTo}%"]);
            });
        });
    }

    $stats = [
        'total' => $statsBase->count(),
        'open' => (clone $statsBase)->where('case_status', 'Open')->count(),
        'closed' => (clone $statsBase)->whereIn('case_status', ['Closed', 'Temporary Closed', 'Permanant Closed'])->count(),
    ];

    $graphData = [
        'open' => $stats['open'],
        'closed' => $stats['closed'],
    ];

    // Line chart data for last 6 months
    $start = $from ? Carbon::parse($from)->startOfMonth() : Carbon::now()->subMonths(5)->startOfMonth();
    $end = $to ? Carbon::parse($to)->endOfMonth() : Carbon::now()->endOfMonth();

    $period = new \DatePeriod(
        $start,
        \DateInterval::createFromDateString('1 month'),
        $end->copy()->addMonth()->startOfMonth()
    );

    $months = [];
    $openCases = [];
    $closedCases = [];

    foreach ($period as $date) {
        $months[] = $date->format('M Y');

        $openCases[] = PoliceCase::where('case_status', 'Open')
            ->whereMonth('created_at', $date->format('m'))
            ->whereYear('created_at', $date->format('Y'))
            ->count();

        $closedCases[] = PoliceCase::whereIn('case_status', ['Closed', 'Temporary Closed', 'Permanant Closed'])
            ->whereMonth('created_at', $date->format('m'))
            ->whereYear('created_at', $date->format('Y'))
            ->count();
    }

    $lineChartData = [
        'labels' => $months,
        'open' => $openCases,
        'closed' => $closedCases,
    ];

    return view('reports.case_report', compact(
        'cases',
        'stats',
        'graphData',
        'lineChartData',
        'from',
        'to',
        'status',
        'assignedTo'
    ));
}



public function exportReport(Request $request)
{
    $from = $request->input('from');
    $to = $request->input('to');

    $query = PoliceCase::query();

    if ($from) {
        $query->whereDate('created_at', '>=', $from);
    }

    if ($to) {
        $query->whereDate('created_at', '<=', $to);
    }

    $cases = $query->latest()->get();

    $pdf = Pdf::loadView('reports.pdf', [
        'cases' => $cases,
        'from' => $from,
        'to' => $to
    ]);

    return $pdf->download('case_report_' . now()->format('Ymd_His') . '.pdf');
}

    /**
     * Display a listing of the cases.
     */

    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dateRange = $request->input('date_range');

        $cases = PoliceCase::with(['department', 'assignments.user'])
            ->when($search, fn($q) =>
                $q->where('case_number', 'like', "%{$search}%")
            )
            ->when($status, fn($q) =>
                $q->where('case_status', $status)
            )
            ->when($dateRange, function ($q, $range) {
                if ($range === 'week') return $q->where('created_at', '>=', now()->subWeek());
                if ($range === 'month') return $q->where('created_at', '>=', now()->subMonth());
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('pages.all_cases', compact('cases', 'search', 'status', 'dateRange'));
    }

    public function show($id)
    {
        $case = PoliceCase::with(['assignments.user', 'complaint'])->findOrFail($id);
        return view('pages.cases_show', compact('case'));
    }

    public function edit($id)
    {
        $case = PoliceCase::with(['assignments'])->findOrFail($id);
        return view('pages.edit_case', compact('case'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'case_status' => 'required|in:Open,Pending,Closed',
            'case_description' => 'required|string|max:1000',
        ]);

        $case = PoliceCase::findOrFail($id);
        $case->update([
            'case_status' => $request->case_status,
            'case_description' => $request->case_description,
        ]);

        Log::create([
            'user_id' => auth()->id(),
            'action' => 'Updated Case',
            'module' => 'Cases',
            'description' => "Updated Case #{$case->case_number} status to '{$request->case_status}'",
        ]);

        return redirect()->route('cases.index')->with('success', 'Case updated successfully.');
    }

    public function bulkAction(Request $request)
{
    $request->validate([
        'action' => 'required|string',
        'case_ids' => 'required|array',
        'case_ids.*' => 'integer|exists:cases,case_id',
    ]);

    $action = $request->input('action');
    $caseIds = $request->input('case_ids');

    switch ($action) {
        case 'assign':
            // Your logic to assign selected cases to staff
            // For example, redirect to an assignment page with these case IDs
            return redirect()->route('cases.assign')->with('case_ids', $caseIds);
        case 'close':
            // Close selected cases
            PoliceCase::whereIn('case_id', $caseIds)->update(['case_status' => 'Closed']);
            return redirect()->back()->with('success', 'Selected cases closed.');
        case 'export':
            // Logic to export selected cases - for example, generate a CSV or PDF
            // Placeholder - implement your export logic here
            return back()->with('success', 'Export functionality not implemented yet.');
        default:
            return back()->withErrors('Invalid bulk action selected.');
    }
}


    public function exportPdf()
    {
        $cases = PoliceCase::with(['assignments.user'])->get();
        $pdf = Pdf::loadView('exports.cases_pdf', compact('cases'));
        return $pdf->download('cases_report.pdf');
    }
    
}

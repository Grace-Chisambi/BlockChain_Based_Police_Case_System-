<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CaseAssignment;
use App\Models\InvestigationProgress;
use Illuminate\Support\Facades\Http;
use App\Models\PoliceCase;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Evidence;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Log;
use Carbon\Carbon;

class InvestigatorController extends Controller
{
    public function complaints()
    {
        return view('investigator.complaint');
    }

    public function dashboard()
    {
        $months = collect();
        $openCases = collect();
        $pendingCases = collect();
        $closedCases = collect();
        $totalLogs = Log::count();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('F Y');
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();

            $months->push($month);

            $openCases->push(
                PoliceCase::where('case_status', 'Open')->whereBetween('created_at', [$start, $end])->count()
            );
            $pendingCases->push(
                PoliceCase::where('case_status', 'Pending')->whereBetween('created_at', [$start, $end])->count()
            );
            $closedCases->push(
                PoliceCase::where('case_status', 'Closed')->whereBetween('created_at', [$start, $end])->count()
            );
        }

        return view('investigator.dash', [
            'months' => $months,
            'openCases' => $openCases,
            'pendingCases' => $pendingCases,
            'closedCases' => $closedCases,
            'casesCount' => PoliceCase::count(),
            'complaintsCount' => Complaint::count(),
            'usersCount' => User::count(),
            'totalLogs' => Log::count(),
            'latestLogs' => Log::latest()->take(5)->limit(2)->get()
        ]);
    }

    public function assign()
    {
        $userId = auth()->user()->user_id;

        $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();
        if (!$staffProfile) {
            return back()->with('error', 'Your staff profile is missing.');
        }

        $staffId = $staffProfile->staff_id;

        $assignedCases = DB::table('cases')
            ->join('case_assignments', 'cases.case_id', '=', 'case_assignments.case_id')
            ->where('case_assignments.staff_id', $staffId)
            ->where('case_assignments.role', 'investigator')
            ->select('cases.*')
            ->orderByDesc('cases.created_at')
            ->latest()->paginate(10);

        foreach ($assignedCases as $case) {
            $case->created_at = Carbon::parse($case->created_at);
        }

        return view('investigator.assign', compact('assignedCases'));
    }

public function assignmentReport(Request $request)
{
    $query = DB::table('case_assignments')
        ->join('police_staff', 'case_assignments.staff_id', '=', 'police_staff.staff_id')
        ->join('users', 'police_staff.user_id', '=', 'users.user_id')
        ->join('cases', 'case_assignments.case_id', '=', 'cases.case_id')
        ->where('case_assignments.role', 'investigator');

    // Apply filters if provided
    if ($request->filled('from')) {
        $query->whereDate('cases.created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('cases.created_at', '<=', $request->to);
    }
    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('cases.case_status', $request->status);
    }

    $assignmentStats = $query
        ->select('users.sname', DB::raw('COUNT(cases.case_id) as cases_count'))
        ->groupBy('users.user_id', 'users.sname')
        ->get();

    return view('investigator.reports.assignment_report', compact('assignmentStats'));
}

    // HTML view of the case report
    public function showCaseReport($caseId)
    {
        $data = $this->getCaseReportData($caseId);
        return view('investigator.reports.case_detail', $data);
    }

    // PDF export of the case report
    public function showCaseReportPdf($caseId)
    {
        $data = $this->getCaseReportData($caseId);

        $pdf = Pdf::loadView('investigator.reports.case_detail_pdf', $data)
                  ->setPaper('a4', 'portrait');

        return $pdf->download("CaseReport_{$data['case']->case_number}.pdf");
    }

    // Shared data retrieval logic
    private function getCaseReportData($caseId)
    {
        $case = DB::table('cases')->where('case_id', $caseId)->first();
        if (!$case) {
            abort(404, 'Case not found.');
        }

        return [
            'case' => $case,
            'complaint' => DB::table('complaints')->where('complaint_id', $case->complaint_id)->first(),
            'suspects' => DB::table('suspects')->where('case_id', $caseId)->get(),
            'progress' => DB::table('investigation_progress')
                            ->where('case_id', $caseId)
                            ->orderBy('date', 'asc')
                            ->get(),
            'closure' => DB::table('case_closures')
                            ->where('case_id', $caseId)
                            ->orderByDesc('closure_date')
                            ->first(),
            'user' => auth()->user()
        ];
    }

public function exportAssignmentReport(Request $request)
{
    $query = PoliceCase::query();

    if ($request->filled('from')) {
        $query->whereDate('created_at', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('created_at', '<=', $request->to);
    }
    if ($request->filled('status')) {
        $query->where('case_status', $request->status);
    }

    // Same as above, filter by investigator if necessary
    // $query->where('investigator_id', auth()->id());

    $assignedCases = $query->orderBy('created_at', 'desc')->get();

    $pdf = Pdf::loadView('investigator.reports.assignment_report_pdf', [
        'assignedCases' => $assignedCases,
        'from' => $request->from,
        'to' => $request->to,
        'status' => $request->status,
    ]);

    return $pdf->download('assignment_report_' . now()->format('Ymd_His') . '.pdf');
}


   public function show($id)
{
    $userId = auth()->user()->user_id;
    $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

    if (!$staffProfile) {
        return redirect()->route('investigator.dash')->with('error', 'Your staff profile is missing.');
    }

    $case = PoliceCase::with(['complaint', 'evidence', 'assignments'])->findOrFail($id);
    $isAssigned = $case->assignments->contains('staff_id', $staffProfile->staff_id);

    if (!$isAssigned) {
        return redirect()->route('investigator.dash')->with('error', 'You are not assigned to this case.');
    }

  //section to calculate chart percentages
    $reviewSummary = [
        'Approved' => $case->evidence->where('review_status', 'Approved')->count(),
        'Rejected' => $case->evidence->where('review_status', 'Rejected')->count(),
        'Pending'  => $case->evidence->where('review_status', 'Pending')->count(),
    ];

    return view('investigator.case_show_investigator', compact('case', 'reviewSummary'));
}

public function storeEvidence(Request $request)
{
    $validated = $request->validate([
        'case_id' => 'required|exists:cases,case_id',
        'description' => 'required|string|max:1000',
        'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp4,mov,avi|max:20480',
    ]);

    $userId = auth()->user()->user_id;
    $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

    if (!$staffProfile) {
        return response()->json([
            'success' => false,
            'message' => 'Your staff profile is missing.'
        ], 403);
    }

    $path = null;
    if ($request->hasFile('file')) {
        $path = $request->file('file')->store('evidence_files', 'public');
    }

    $evidence = Evidence::create([
        'case_id' => $validated['case_id'],
        'description' => $validated['description'],
        'file_path' => $path,
        'uploaded_by_staff_id' => $staffProfile->staff_id,
    ]);

    $case = PoliceCase::findOrFail($validated['case_id']);

    // Generate temporary hash for blockchain logging
    $transactionHash = hash('sha256', $evidence->description . now());

    // Attempt to log to blockchain service, but don't block main flow
    try {
        $blockchainResponse = Http::timeout(5)->post('http://localhost:3001/log-evidence', [
            'caseId' => $case->case_number,
            'evidenceHash' => $transactionHash,
        ]);

        if (!$blockchainResponse->ok()) {
            logger()->warning('Blockchain logging failed for evidence', [
                'response' => $blockchainResponse->body()
            ]);
        }
    } catch (\Exception $e) {
        logger()->error('Blockchain service unreachable for evidence logging', ['error' => $e->getMessage()]);
        // Do not interrupt response, evidence is already saved
    }

    return response()->json([
        'success' => true,
        'case_number' => $case->case_number,
        'transaction_hash' => $transactionHash,
    ]);
}

    public function store(Request $request)
    {
        $request->validate([
            'case_id' => 'required|exists:cases,case_id',
            'date' => 'required|date',
            'notes' => 'required|string|max:2000',
        ]);

        $staffProfile = DB::table('police_staff')->where('user_id', Auth::id())->first();

        if (!$staffProfile) {
            return back()->with('error', 'Your staff profile is missing.');
        }

        InvestigationProgress::create([
            'case_id' => $request->case_id,
            'staff_id' => $staffProfile->staff_id,
            'date' => $request->date,
            'notes' => $request->notes,
        ]);

       return back()->with('success', 'Progress logged successfully.');

    }
}

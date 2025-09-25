<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Evidence;
use App\Models\CaseClosure;
use App\Models\PoliceCase;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ProsecutorController extends Controller
{
   public function dashboard()
{
    $user = Auth::user();

    $staff = DB::table('police_staff')->where('user_id', $user->user_id)->first();
    if (!$staff) {
        return back()->with('error', 'Staff profile not found.');
    }

    $staffId = $staff->staff_id;

    // Get prosecutor's case IDs
    $caseIds = DB::table('case_assignments')
        ->where('staff_id', $staffId)
        ->where('role', 'prosecutor')
        ->pluck('case_id');

    // Metrics
    $prosecutionsCount = count($caseIds);
    $reviewedCasesCount = DB::table('evidence')
        ->whereIn('case_id', $caseIds)
        ->whereNotNull('review_status')
        ->distinct('case_id')
        ->count('case_id');

    $pendingHearingsCount = DB::table('cases')
        ->whereIn('case_id', $caseIds)
        ->where('case_status', 'Open')
        ->count();

    // Logs (activity)
    $recentActions = DB::table('logs')
        ->where('user_id', $user->user_id)
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();

    // Monthly trend (last 6 months)
    $monthlyData = DB::table('cases')
        ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as prosecutions")
        ->whereIn('case_id', $caseIds)
        ->whereDate('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderByRaw("MIN(DATE_FORMAT(created_at, '%m'))")
        ->get();

    $months = $monthlyData->pluck('month');
    $monthlyProsecutions = $monthlyData->pluck('prosecutions');

    $monthlyPending = DB::table('cases')
        ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as pending")
        ->whereIn('case_id', $caseIds)
        ->where('case_status', 'Open')
        ->whereDate('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderByRaw("MIN(DATE_FORMAT(created_at, '%m'))")
        ->pluck('pending');

    return view('prosecutor.dashboard', compact(
        'prosecutionsCount',
        'reviewedCasesCount',
        'pendingHearingsCount',
        'recentActions',
        'months',
        'monthlyProsecutions',
        'monthlyPending'
    ));
}

public function index(Request $request)
{
    $userId = auth()->user()->user_id;

    // Get staff profile from police_staff
    $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

    if (!$staffProfile) {
        return back()->with('error', 'Your staff profile is missing.');
    }

    $staffId = $staffProfile->staff_id;

    // Build base query
    $query = PoliceCase::join('case_assignments', 'cases.case_id', '=', 'case_assignments.case_id')
        ->where('case_assignments.staff_id', $staffId)
        ->where('case_assignments.role', 'prosecutor')
        ->select('cases.*');

    // Apply filters
    if ($request->filled('case_number')) {
        $query->where('cases.case_number', 'LIKE', '%' . $request->case_number . '%');
    }

    if ($request->filled('status')) {
        $query->where('cases.case_status', $request->status);
    }

    if ($request->filled('from')) {
        $query->whereDate('cases.created_at', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $query->whereDate('cases.created_at', '<=', $request->to);
    }

    // Get paginated result
    $cases = $query->orderBy('cases.created_at', 'desc')->paginate(10);

    return view('prosecutor.index', compact('cases'));
}
    public function exportSelectedPdf(Request $request)
    {
        $request->validate([
            'selected_cases' => 'required|array|min:1',
            'selected_cases.*' => 'integer|exists:court_appearances,case_id', // adjust table and column names
        ]);

        $caseIds = $request->input('selected_cases');

        $appearances = CourtAppearance::whereIn('case_id', $caseIds)->get();

        if ($appearances->isEmpty()) {
            return back()->with('error', 'No valid cases selected for export.');
        }

        $pdf = PDF::loadView('prosecutor.appearances_pdf', compact('appearances'));

        return $pdf->download('court_appearances_' . now()->format('Ymd_His') . '.pdf');
    }




    public function show($case_id)
    {
        $case = PoliceCase::with('evidence')->findOrFail($case_id);
        return view('prosecutor.show', compact('case'));
    }

    public function reviewEvidence(Request $request, $evidence_id)
    {
        $request->validate([
            'review_status' => 'required|string',
            'review_comment' => 'nullable|string',
        ]);

        $evidence = Evidence::findOrFail($evidence_id);
        $evidence->review_status = $request->review_status;
        $evidence->review_comment = $request->review_comment;
        $evidence->reviewed_at = now();
        $evidence->save();

        return redirect()->back()->with('success', 'Evidence reviewed successfully.');
    }

   public function closeCase(Request $request)
{
    $validated = $request->validate([
        'case_id' => 'required|exists:cases,case_id',
        'closure_type' => 'required|in:permanent,temporary,withdrawn',
        'reason' => 'required|string',
        'closure_date' => 'required|date',
    ]);

    $user = Auth::user();

    $staffProfile = DB::table('police_staff')
        ->where('user_id', $user->user_id)
        ->first();

    if (!$staffProfile) {
        return response()->json([
            'success' => false,
            'message' => 'Your staff profile is missing.'
        ], 403);
    }

    $staffId = $staffProfile->staff_id;

    $assignedCase = DB::table('case_assignments')
        ->where('case_id', $validated['case_id'])
        ->where('staff_id', $staffId)
        ->where('role', $user->role)
        ->first();

    if (!$assignedCase) {
        return response()->json([
            'success' => false,
            'message' => 'You are not assigned to this case.'
        ], 403);
    }

    CaseClosure::create([
        'case_id' => $validated['case_id'],
        'staff_id' => $staffId,
        'closure_type' => $validated['closure_type'],
        'reason' => $validated['reason'],
        'closure_date' => $validated['closure_date'],
    ]);

    $case = PoliceCase::find($validated['case_id']);
    $case->case_status = $validated['closure_type'] === 'temporary' ? 'temporarily_closed' : 'closed';
    $case->save();

    $closureHash = hash('sha256', $case->case_number . $validated['closure_type'] . $validated['closure_date'] . now());

    try {
        $blockchainResponse = Http::timeout(5)->post('http://localhost:3001/log-case-closure', [
            'caseId' => $case->case_number,
            'closureHash' => $closureHash,
        ]);

        if (!$blockchainResponse->ok()) {
            logger()->warning('Blockchain logging failed (case closure)', ['response' => $blockchainResponse->body()]);

            return response()->json([
                'success' => true,
                'message' => 'Closure recorded. Blockchain logging failed.',
                'case_number' => $case->case_number,
                'transaction_hash' => null,
            ]);
        }

        $txHash = $blockchainResponse->json('tx') ?? $closureHash;

    } catch (\Exception $e) {
        logger()->error('Blockchain logging failed (case closure): ' . $e->getMessage());

        return response()->json([
            'success' => true,
            'message' => 'Closure recorded. Blockchain service unavailable.',
            'case_number' => $case->case_number,
            'transaction_hash' => null,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Case closure recorded successfully.',
        'case_number' => $case->case_number,
        'transaction_hash' => $txHash,
    ]);
}
public function view()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['prosecutor', 'police_officer'])) {
            abort(403, 'Unauthorized action.');
        }

        // Get staff profile by user_id
        $staffProfile = DB::table('police_staff')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$staffProfile) {
            return back()->with('error', 'Your staff profile is missing.');
        }

        $staffId = $staffProfile->staff_id;

        // Get active cases assigned to this staff and role, excluding closed cases
        $cases = DB::table('cases')
            ->join('case_assignments', 'cases.case_id', '=', 'case_assignments.case_id')
            ->where('case_assignments.staff_id', $staffId)
            ->where('case_assignments.role', $user->role)
            ->whereNotIn('cases.case_status', ['closed', 'temporarily_closed'])
            ->select('cases.*')
            ->orderByDesc('cases.created_at')
            ->get();

        return view('prosecutor.case_closures', compact('cases'));
    }
public function reports(Request $request)
{
    $userId = auth()->user()->user_id;

    $staff = DB::table('police_staff')->where('user_id', $userId)->first();
    if (!$staff) {
        return back()->with('error', 'Staff profile not found.');
    }

    $staffId = $staff->staff_id;

    $caseIds = DB::table('case_assignments')
        ->where('staff_id', $staffId)
        ->where('role', 'prosecutor')
        ->pluck('case_id');

    // Total counts
    $totalCases = count($caseIds);
    $closedCases = DB::table('cases')
        ->whereIn('case_id', $caseIds)
        ->where('case_status', 'Closed')
        ->count();

    $openCases = DB::table('cases')
        ->whereIn('case_id', $caseIds)
        ->where('case_status', 'Open')
        ->count();

    // Monthly trends
    $monthlyData = DB::table('cases')
        ->selectRaw("DATE_FORMAT(created_at, '%b') as month, COUNT(*) as count")
        ->whereIn('case_id', $caseIds)
        ->when($request->filled('from'), fn($q) => $q->whereDate('created_at', '>=', $request->from))
        ->when($request->filled('to'), fn($q) => $q->whereDate('created_at', '<=', $request->to))
        ->groupBy('month')
        ->orderByRaw("MIN(DATE_FORMAT(created_at, '%m'))")
        ->get();

    $monthlyLabels = $monthlyData->pluck('month');
    $monthlyCounts = $monthlyData->pluck('count');

    // Case type breakdown
    $caseTypeBreakdown = DB::table('cases')
        ->select('case_type', DB::raw('count(*) as count'))
        ->whereIn('case_id', $caseIds)
        ->groupBy('case_type')
        ->pluck('count', 'case_type');

    return view('prosecutor.reports', compact(
        'totalCases',
        'openCases',
        'closedCases',
        'monthlyLabels',
        'monthlyCounts',
        'caseTypeBreakdown'
    ));
}


    public function exportPdf()
    {
        $userId = auth()->user()->user_id;

        $staff = DB::table('police_staff')->where('user_id', $userId)->first();
        if (!$staff) {
            return back()->with('error', 'Staff profile not found.');
        }

        $staffId = $staff->staff_id;

        $caseIds = DB::table('case_assignments')
            ->where('staff_id', $staffId)
            ->where('role', 'prosecutor')
            ->pluck('case_id');

        $totalCases = count($caseIds);
        $closedCases = DB::table('cases')
            ->whereIn('case_id', $caseIds)
            ->where('case_status', 'Closed')
            ->count();

        $totalEvidence = DB::table('evidence')->whereIn('case_id', $caseIds)->count();
        $reviewedEvidence = DB::table('evidence')->whereIn('case_id', $caseIds)->whereNotNull('review_status')->count();
        $approvedEvidence = DB::table('evidence')->whereIn('case_id', $caseIds)->where('review_status', 'Approved')->count();
        $rejectedEvidence = DB::table('evidence')->whereIn('case_id', $caseIds)->where('review_status', 'Rejected')->count();

        $pdf = Pdf::loadView('prosecutor.reports_pdf', compact(
            'totalCases',
            'closedCases',
            'totalEvidence',
            'reviewedEvidence',
            'approvedEvidence',
            'rejectedEvidence'
        ));

        return $pdf->download('prosecutor_report.pdf');
    }


public function upcomingAppearances()
{
    $userId = auth()->user()->user_id;

    // Get staff profile
    $staff = DB::table('police_staff')->where('user_id', $userId)->first();
    if (!$staff) {
        return back()->with('error', 'Staff profile not found.');
    }

    $staffId = $staff->staff_id;

    // Get open cases assigned to this prosecutor
    $cases = PoliceCase::join('case_assignments', 'cases.case_id', '=', 'case_assignments.case_id')
        ->where('case_assignments.staff_id', $staffId)
        ->where('case_assignments.role', 'prosecutor')
        ->where('cases.case_status', 'Open')
        ->select('cases.*')
        ->get();

    $appearances = new Collection();

    foreach ($cases as $case) {
        $appearances->push((object)[
            'case_id' => $case->case_id,
            'case_number' => $case->case_number,
            'date' => $case->created_at->addDays(7)->format('Y-m-d'),
            'time' => '09:00:00',  // default time for court appearance
            'court_name' => 'High Court',
            'location' => 'Court Room 1',
        ]);
    }

    return view('prosecutor.court_appearances', compact('appearances'));

}
}

<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\PoliceCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CaseAssignment;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EvidenceReviewed;
use Barryvdh\DomPDF\Facade\Pdf;

class EvidenceController extends Controller
{

// Show pending evidence cases for current user based on their role
public function casesWithPendingEvidence(Request $request)
{
    $userId = auth()->user()->user_id;
    $user = DB::table('users')->where('user_id', $userId)->first();
    $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

    if (!$user || !$staffProfile) {
        abort(403, 'Unauthorized: Missing user or staff profile.');
    }

    $userRole = strtolower(trim($user->role));
    $isProsecution = $staffProfile->department_id == 4;

    if ($userRole !== 'supervisor') {
        abort(403, 'Unauthorized: This page is for supervisors only.');
    }

    // Fetch cases depending on supervisor type
    if ($isProsecution) {
        // Prosecution supervisor sees all cases with pending evidence
        $casesQuery = PoliceCase::with(['evidence'])
            ->withCount(['evidence as pending_count' => function ($q) {
                $q->where('review_status', 'Pending');
            }])
            ->whereHas('evidence', function ($q) {
                $q->where('review_status', 'Pending');
            });
    } else {
        // Other supervisors only see cases they assigned
        $assignedCaseIds = DB::table('case_assignments')
            ->where('assigned_by', $staffProfile->staff_id)
            ->pluck('case_id');

        $casesQuery = PoliceCase::whereIn('case_id', $assignedCaseIds)
            ->with(['evidence'])
            ->withCount(['evidence as pending_count' => function ($q) {
                $q->where('review_status', 'Pending');
            }]);
    }

    // Optional filters
    if ($request->filled('search')) {
        $casesQuery->where('case_number', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('priority')) {
        $casesQuery->where('priority', $request->priority);
    }

     $cases = $casesQuery->paginate(10)->appends($request->only(['search', 'priority']));

    return view('pages.pending_cases', compact('cases'))
        ->with('search', $request->search)
        ->with('priority', $request->priority);
}



    public function handleBulkAction(Request $request)
{
    $request->validate([
        'bulk_action' => 'required|string|in:mark_reviewed,flag_urgent',
        'case_ids' => 'required|array|min:1',
    ]);

    $userId = auth()->user()->user_id;
    $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

    if (!$staffProfile) {
        return back()->with('error', 'Missing staff profile.');
    }

    $cases = PoliceCase::whereIn('case_id', $request->case_ids)->get();

    foreach ($cases as $case) {
        if ($request->bulk_action === 'mark_reviewed') {
            // Mark all pending evidence in this case as Approved (or Reviewed)
            Evidence::where('case_id', $case->case_id)
                ->where('review_status', 'Pending')
                ->update([
                    'review_status' => 'Approved',
                    'review_comment' => 'Bulk reviewed by supervisor',
                    'staff_id' => $staffProfile->staff_id,
                    'reviewed_at' => now(),
                ]);
        }

        if ($request->bulk_action === 'flag_urgent') {
            // Set case priority to High
            $case->update(['priority' => 'High']);
        }
    }

    return back()->with('success', 'Bulk action applied to selected cases.');
}


    // Show the review page for evidence for a specific case
public function showReviewPage($case_id)
{
    $userId = auth()->user()->user_id;
    $user = DB::table('users')->where('user_id', $userId)->first();
    $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

    if (!$user || !$staffProfile) {
        abort(403, 'Unauthorized: Missing user or staff profile.');
    }

    $case = PoliceCase::findOrFail($case_id);
    $userRole = strtolower(trim($user->role ?? ''));
    $isProsecution = $staffProfile->department_id == 4;

    $result = [
        'userId' => $userId,
        'userRole' => $userRole,
        'staffId' => $staffProfile->staff_id ?? null,
        'caseId' => $case_id,
    ];

    if ($userRole === 'supervisor') {
        if ($isProsecution) {
            // Prosecution supervisors only view reviewed evidence
            $hasReviewedEvidence = Evidence::where('case_id', $case_id)
                ->whereIn('review_status', ['Approved', 'Rejected'])
                ->exists();

            if (!$hasReviewedEvidence) {
                abort(403, 'Forbidden: No reviewed evidence for prosecution supervisor.');
            }
        } else {
            // Normal supervisors: must have assigned the case
            $hasAssignment = DB::table('case_assignments')
                ->where('case_id', $case_id)
                ->where('assigned_by', $staffProfile->staff_id)
                ->exists();

            $result['hasAssignment'] = $hasAssignment;

            if (!$hasAssignment) {
                abort(403, 'Forbidden: Supervisor has no assignment');
            }
        }
    } elseif ($userRole === 'investigator') {
        $isAssigned = DB::table('case_assignments')
            ->where('case_id', $case_id)
            ->where('staff_id', $staffProfile->staff_id)
            ->exists();

        $result['isAssigned'] = $isAssigned;

        if (!$isAssigned) {
            abort(403, 'Forbidden: Investigator not assigned');
        }
    } else {
        abort(403, 'Forbidden: Invalid role');
    }

    // Only show reviewed evidence to prosecution supervisors
 $pendingEvidence = ($isProsecution)
    ? Evidence::where('case_id', $case_id)
        ->whereRaw('0 = 1') // always false to return empty result
        ->paginate(5, ['*'], 'pending_page')
    : Evidence::where('case_id', $case_id)
        ->where('review_status', 'Pending')
        ->paginate(5, ['*'], 'pending_page');


    $reviewedEvidence = Evidence::where('case_id', $case_id)
        ->whereIn('review_status', ['Approved', 'Rejected'])
        ->with(['reviewer', 'uploader.user'])
        ->paginate(5, ['*'], 'reviewed_page');

    return view('pages.review_evidence', compact('case', 'pendingEvidence', 'reviewedEvidence'));
}


    // Submit evidence review (Supervisor only)

public function submitReview(Request $request, $evidence_id)
{
    $request->validate([
        'review_status' => 'required|in:Approved,Rejected',
        'review_comment' => 'nullable|string|max:1000',
    ]);

    $userId = auth()->user()->user_id;
    $user = User::find($userId);
    $staffProfile = Staff::where('user_id', $userId)->first();

    if (!$user || !$staffProfile) {
        abort(403, 'Unauthorized: Missing user or staff profile.');
    }

    if (strtolower(trim($user->role ?? '')) !== 'supervisor') {
        abort(403, 'Unauthorized: Only supervisors can review evidence.');
    }

    $evidence = Evidence::with('case')->findOrFail($evidence_id);

    $hasAssignment = CaseAssignment::where('case_id', $evidence->case->case_id)
        ->where('assigned_by', $staffProfile->staff_id)
        ->exists();

    if (!$hasAssignment) {
        abort(403, 'Forbidden: Supervisor did not assign investigator to this case.');
    }

    // Update review info
    $evidence->update([
        'review_status' => $request->review_status,
        'review_comment' => $request->review_comment,
        'staff_id' => $staffProfile->staff_id,
        'reviewed_at' => now(),
    ]);

    // Notify uploader if available
    if ($evidence->uploader && $evidence->uploader->user) {
        $evidence->uploader->user->notify(new EvidenceReviewed($evidence));
    }

    // Notify all investigators assigned to the case
    $investigators = CaseAssignment::where('case_id', $evidence->case->case_id)
        ->where('role', 'investigator')
        ->get();

    foreach ($investigators as $assignment) {
        $staff = Staff::where('staff_id', $assignment->staff_id)->first();

        if ($staff && $staff->user_id) {
            $investigatorUser = User::find($staff->user_id);

            if ($investigatorUser) {
                $investigatorUser->notify(new EvidenceReviewed($evidence));
            }
        }
    }

    return back()->with('success', 'Evidence reviewed and notifications sent.');
}

    // Export reviewed evidence to PDF
    public function exportReviewedEvidence($case_id)
    {
        $case = PoliceCase::with(['evidence' => function ($q) {
            $q->whereIn('review_status', ['Approved', 'Rejected'])->with(['staff', 'uploader']);
        }])->findOrFail($case_id);

        $pdf = Pdf::loadView('exports.evidence_summary', ['case' => $case]);
        return $pdf->download("Case_{$case_id}_Reviewed_Evidence.pdf");
    }
}

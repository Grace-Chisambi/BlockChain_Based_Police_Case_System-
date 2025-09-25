<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Suspect;
use App\Notifications\SuspectReviewComplete;
use Illuminate\Support\Facades\Http;
use App\Models\PoliceCase;
use App\Models\Staff;
use App\Models\User;
use App\Notifications\CaseAssigned;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    // ================== DASHBOARD + ASSIGNMENT ==================

    const PROSECUTION_DEPARTMENT_ID = 4; // <-- Replace with actual ID

    public function supervisorAssignPage()
    {
        $supervisorUserId = auth()->user()->user_id;
        $staffProfile = DB::table('police_staff')->where('user_id', $supervisorUserId)->first();

        if (!$staffProfile) {
            return back()->with('error', 'Your staff profile is missing.');
        }

        $departmentId = $staffProfile->department_id;
        $isProsecution = $departmentId == self::PROSECUTION_DEPARTMENT_ID;
        $roleToAssign = $isProsecution ? 'prosecutor' : 'investigator';

        // Unassigned cases
        $casesQuery = DB::table('cases')
            ->whereNotIn('case_id', function ($query) use ($roleToAssign) {
                $query->select('case_id')->from('case_assignments')->where('role', $roleToAssign);
            });

        if (!$isProsecution) {
            $casesQuery->where('department_id', $departmentId);
        }

        $cases = $casesQuery->limit(1)->get();

        // Staff list (investigators or prosecutors)
        $investigatorsQuery = DB::table('users')
            ->join('police_staff', 'users.user_id', '=', 'police_staff.user_id')
            ->where('users.role', $roleToAssign)
            ->where('police_staff.available', 1);

        if (!$isProsecution) {
            $investigatorsQuery->where('police_staff.department_id', $departmentId);
        }

        $investigators = $investigatorsQuery
            ->select('police_staff.staff_id', 'users.fname', 'users.sname')
            ->get();

        // Metrics
        $departmentCasesCount = DB::table('cases');
        $assignedCasesCount = DB::table('case_assignments')
            ->join('cases', 'case_assignments.case_id', '=', 'cases.case_id')
            ->where('case_assignments.role', $roleToAssign);

        $evidenceUploadedCount = DB::table('evidence')
            ->join('cases', 'evidence.case_id', '=', 'cases.case_id')
            ->distinct('evidence.case_id');

        $recentAssignments = DB::table('case_assignments')
            ->join('cases', 'case_assignments.case_id', '=', 'cases.case_id')
            ->join('police_staff', 'case_assignments.staff_id', '=', 'police_staff.staff_id')
            ->join('users', 'police_staff.user_id', '=', 'users.user_id')
            ->where('case_assignments.role', $roleToAssign)
            ->select(
                'cases.case_number', 'cases.case_type', 'cases.case_status', 'cases.case_id',
                'police_staff.staff_id', 'users.fname', 'users.sname',
                'case_assignments.created_at as assigned_at'
            )
            ->orderBy('assigned_at', 'desc')
            ->limit(3);

        $casesWithEvidence = DB::table('cases')
            ->join('evidence', 'cases.case_id', '=', 'evidence.case_id')
            ->select(
                'cases.case_number', 'cases.case_type', 'cases.case_status', 'cases.case_id',
                DB::raw('count(evidence.evidence_id) as evidence_count')
            )
            ->groupBy('cases.case_id', 'cases.case_number', 'cases.case_type', 'cases.case_status')
            ->orderByDesc('cases.case_id')
            ->limit(3);

        if (!$isProsecution) {
            $departmentCasesCount->where('department_id', $departmentId);
            $assignedCasesCount->where('cases.department_id', $departmentId);
            $evidenceUploadedCount->where('cases.department_id', $departmentId);
            $recentAssignments->where('cases.department_id', $departmentId);
            $casesWithEvidence->where('cases.department_id', $departmentId);
        }

        return view('pages.assign', [
            'cases' => $cases,
            'investigators' => $investigators,
            'departmentCasesCount' => $departmentCasesCount->count(),
            'assignedCasesCount' => $assignedCasesCount->count(),
            'evidenceUploadedCount' => $evidenceUploadedCount->count('evidence.case_id'),
            'assignedCases' => $recentAssignments->get()->map(function ($case) {
                $case->assigned_at = Carbon::parse($case->assigned_at);
                return $case;
            }),
            'casesWithEvidence' => $casesWithEvidence->get()
        ]);
    }

    public function assignInvestigator(Request $request)
    {
        try {
            $validated = $request->validate([
                'case_id' => 'required|exists:cases,case_id',
                'investigator_id' => 'required|exists:police_staff,staff_id',
            ]);

            $caseId = $validated['case_id'];
            $investigatorId = $validated['investigator_id'];
            $authUser = auth()->user();

            $staff = DB::table('police_staff')->where('user_id', $authUser->user_id)->first();
            if (!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your staff profile is missing.'
                ], 400);
            }

            $assignedBy = $staff->staff_id;
            $roleToAssign = ($staff->department_id == self::PROSECUTION_DEPARTMENT_ID) ? 'prosecutor' : 'investigator';

            $assignedCasesCount = DB::table('case_assignments')
                ->where('staff_id', $investigatorId)
                ->where('role', $roleToAssign)
                ->count();

            if ($assignedCasesCount >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'This staff member is already assigned to 5 cases.'
                ], 400);
            }

            $assignmentHash = hash('sha256', $caseId . $investigatorId . now());

            DB::table('case_assignments')->insert([
                'case_id' => $caseId,
                'staff_id' => $investigatorId,
                'role' => $roleToAssign,
                'assigned_by' => $assignedBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::create([
                'user_id' => $authUser->user_id,
                'action' => 'Assigned ' . ucfirst($roleToAssign),
                'module' => 'Case Assignment',
                'description' => ucfirst($roleToAssign) . " ID $investigatorId assigned to Case ID $caseId. Hash: $assignmentHash",
            ]);

            $user = User::whereHas('policeStaff', fn($q) => $q->where('staff_id', $investigatorId))->first();
            $case = PoliceCase::find($caseId);

            if ($user && $case && !empty($case->case_number)) {
                $user->notify(new CaseAssigned($case->case_number));
            } else {
                \Log::warning("Notification not sent: user or case missing or case_number empty", [
                    'user_id' => $user?->id,
                    'case_id' => $caseId,
                    'case_number' => $case?->case_number,
                ]);
            }

            try {
                $blockchainResponse = Http::timeout(5)->post('http://localhost:3001/log-assignment', [
                    'caseId' => $case->case_number ?? $caseId,
                    'assignmentHash' => $assignmentHash,
                ]);

                if (!$blockchainResponse->ok()) {
                    logger()->warning('Blockchain logging failed', [
                        'response' => $blockchainResponse->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                logger()->error('Blockchain sync failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'case_id' => $caseId,
                'case_number' => $case->case_number ?? null,
                'assignment_hash' => $assignmentHash,
                'transaction_hash' => $assignmentHash,
            ]);

        } catch (\Exception $e) {
            logger()->error('Assign error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error during assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ================== UNASSIGNED CASES ==================
    public function unassignedCases()
    {
        $userId = Auth::id();
        $staff = DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staff) {
            return back()->with('error', 'Staff profile not found.');
        }

        $isProsecution = $staff->department_id == self::PROSECUTION_DEPARTMENT_ID;

        $cases = DB::table('cases')
            ->when(!$isProsecution, fn($query) => $query->where('department_id', $staff->department_id))
            ->whereNotIn('case_id', function ($query) {
                $query->select('case_id')
                      ->from('case_assignments')
                      ->where('role', 'investigator');
            })
            ->orderByDesc('case_id')
            ->paginate(10);

        // Show only investigators from investigation dept(s)
        $investigators = DB::table('users')
            ->join('police_staff', 'users.user_id', '=', 'police_staff.user_id')
            ->where('users.role', 'investigator')
            ->where('police_staff.available', 1)
            ->when(!$isProsecution, fn($q) => $q->where('police_staff.department_id', $staff->department_id))
            ->select('police_staff.staff_id', 'users.fname', 'users.sname')
            ->get();

        return view('pages.unassigned-cases', compact('cases', 'investigators'));
    }

    // ================== SUSPECT REVIEW LIST ==================
    public function pendingSuspectReviews()
    {
        $userId = Auth::id();
        $staff = DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staff) {
            return back()->with('error', 'Staff profile not found.');
        }

        $isProsecution = $staff->department_id == self::PROSECUTION_DEPARTMENT_ID;

        $suspects = DB::table('suspects')
            ->join('cases', 'suspects.case_id', '=', 'cases.case_id')
            ->when(!$isProsecution, fn($q) => $q->where('cases.department_id', $staff->department_id))
            ->whereNull('suspects.decision')
            ->select('suspects.*')
            ->get();

        return view('supervisor.pending_suspects', compact('suspects'));
    }

    // ================== VIEW SUSPECT DETAILS ==================
    public function reviewSuspect($id)
    {
        $userId = Auth::id();
        $staff = DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staff) {
            return back()->with('error', 'Staff profile not found.');
        }

        $suspect = Suspect::with('case')->findOrFail($id);

        // Only allow reviewing if same department AND not prosecution
        if ($suspect->case->department_id !== $staff->department_id ||
            $staff->department_id == self::PROSECUTION_DEPARTMENT_ID) {
            abort(403, 'Unauthorized');
        }

        return view('supervisor.review_suspect', compact('suspect'));
    }

    // ================== SUBMIT SUSPECT REVIEW ==================
    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'recommendation' => 'required|string',
            'decision' => 'required|in:detain,release',
        ]);

        $userId = auth()->user()->user_id;
        $staff = DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staff) {
            return back()->with('error', 'Staff profile not found.');
        }

        $suspect = Suspect::with('case')->findOrFail($id);

        // Block submission if user is prosecution or not same department
        if ($suspect->case->department_id !== $staff->department_id ||
            $staff->department_id == self::PROSECUTION_DEPARTMENT_ID) {
            abort(403, 'Unauthorized');
        }

        $suspect->update([
            'recommendation' => $request->recommendation,
            'decision' => $request->decision,
            'reviewed_by_staff_id' => $staff->staff_id,
            'review_date' => now(),
        ]);

        // Mark case as reviewed if all suspects are reviewed
        $caseId = $suspect->case_id;

        $allReviewed = Suspect::where('case_id', $caseId)
            ->whereNull('decision')
            ->doesntExist();

        if ($allReviewed) {
            PoliceCase::where('case_id', $caseId)->update([
                'suspect_reviewed' => true
            ]);

            $case = PoliceCase::find($caseId);

            if ($case) {
                $investigatorUsers = User::whereIn('user_id', function ($query) use ($caseId) {
                    $query->select('police_staff.user_id')
                        ->from('case_assignments')
                        ->join('police_staff', 'case_assignments.staff_id', '=', 'police_staff.staff_id')
                        ->where('case_assignments.case_id', $caseId)
                        ->where('case_assignments.role', 'investigator');
                })->get();

                foreach ($investigatorUsers as $user) {
                    $user->notify(new SuspectReviewComplete($case->case_number));
                }
            }
        }

        Log::create([
            'user_id' => $userId,
            'action' => 'Reviewed Suspect',
            'module' => 'Suspect Review',
            'description' => "Reviewed suspect ID {$suspect->id} with decision: {$request->decision}",
        ]);

        return redirect()->route('supervisor.suspects.pending')
                         ->with('success', 'Suspect reviewed successfully.');
    }
}


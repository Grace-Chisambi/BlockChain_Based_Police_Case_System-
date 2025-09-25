<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ProgressApprovalNotification;

class ProgressController extends Controller
{
    /**
     * View progress for supervisor side.
     */
public function viewProgress(Request $request)
{
    $statusFilter = $request->input('status');
    $priorityFilter = $request->input('priority');
    $search = $request->input('search');
    $date = $request->input('date');

    $supervisorUserId = auth()->user()->user_id;

    $staffProfile = DB::table('police_staff')->where('user_id', $supervisorUserId)->first();
    if (!$staffProfile) {
        return back()->with('error', 'Your staff profile is missing.');
    }

    $departmentId = $staffProfile->department_id;
    $isProsecution = ($departmentId == 4);

    // tracking investigators — prosecution supervisors review their work
    $query = DB::table('case_assignments')
        ->join('cases', 'case_assignments.case_id', '=', 'cases.case_id')
        ->join('police_staff', 'case_assignments.staff_id', '=', 'police_staff.staff_id')
        ->join('users', 'police_staff.user_id', '=', 'users.user_id')
        ->where('case_assignments.role', 'investigator')
        ->select(
            'cases.case_number', 'cases.case_type', 'cases.case_status', 'cases.priority', 'cases.case_id',
            'police_staff.staff_id', 'users.fname', 'users.sname',
            'case_assignments.created_at as assigned_at'
        );

    // Filter by department only if supervisor is not prosecution
    if (!$isProsecution) {
        $query->where('cases.department_id', $departmentId);
    }

    // Optional filters
    if ($statusFilter) {
        $query->where('cases.case_status', ucfirst($statusFilter));
    }

    if ($priorityFilter) {
        $query->where('cases.priority', ucfirst($priorityFilter));
    }

    if ($search) {
        $query->where('cases.case_number', 'like', '%' . $search . '%');
    }

    if ($date) {
        $query->whereDate('case_assignments.created_at', '=', $date);
    }

    $assignedCases = $query->orderByDesc('assigned_at')->paginate(10);

    // Attach recent progress logs (up to 3 per case)
    foreach ($assignedCases as $case) {
        $case->progress = DB::table('investigation_progress as ip')
            ->join('police_staff as ps', 'ip.staff_id', '=', 'ps.staff_id')
            ->join('users as u', 'ps.user_id', '=', 'u.user_id')
            ->leftJoin('police_staff as sup', 'ip.action_by', '=', 'sup.staff_id')
            ->leftJoin('users as su', 'sup.user_id', '=', 'su.user_id')
            ->where('ip.case_id', $case->case_id)
            ->select(
                'ip.*',
                'u.fname', 'u.sname',
                'su.fname as supervisor_fname', 'su.sname as supervisor_sname'
            )
            ->orderByDesc('ip.date')
            ->limit(3)
            ->get();
    }

    return view('pages.progress', compact('assignedCases', 'statusFilter', 'priorityFilter'));
}



    /**
     * Show progress log form for investigators.
     */
    public function logForm()
    {
        $userId = auth()->user()->user_id;

        $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staffProfile) {
            return back()->with('error', 'Staff profile not found.');
        }

        // Fetch assigned cases (only open cases)
        $assignedCases = DB::table('case_assignments as ca')
            ->join('cases', 'ca.case_id', '=', 'cases.case_id')
            ->where('ca.staff_id', $staffProfile->staff_id)
            ->where('ca.role', 'investigator')
            ->where('cases.case_status', 'Open') // <-- only open cases here
            ->select('cases.case_id', 'cases.case_number', 'cases.case_type')
            ->get();

        // Fetch progress entries for this staff (approved or rejected)
        $progressEntries = DB::table('investigation_progress as ip')
            ->join('police_staff as ps', 'ip.staff_id', '=', 'ps.staff_id')
            ->join('users as u', 'ps.user_id', '=', 'u.user_id')
            ->leftJoin('police_staff as sup', 'ip.action_by', '=', 'sup.staff_id')
            ->leftJoin('users as su', 'sup.user_id', '=', 'su.user_id')
            ->where('ip.staff_id', $staffProfile->staff_id)
            ->whereIn('ip.action', ['approve', 'reject'])
            ->select(
                'ip.*',
                'u.fname', 'u.sname',
                'su.fname as supervisor_fname', 'su.sname as supervisor_sname'
            )
            ->orderByDesc('ip.date')
            ->paginate(2);

        // Fetch progress counts grouped by case_id and action for all progress of this staff
        $progressCountsRaw = DB::table('investigation_progress')
            ->select('case_id', 'action', DB::raw('count(*) as total'))
            ->where('staff_id', $staffProfile->staff_id)
            ->groupBy('case_id', 'action')
            ->get();

        // Initialize counts with zero for each case and action
        $progressCounts = [];
        foreach ($assignedCases as $case) {
            $progressCounts[$case->case_id] = [
                'approve' => 0,
                'pending' => 0,
                'reject' => 0,
            ];
        }

        // Fill actual counts from DB
        foreach ($progressCountsRaw as $row) {
            $action = $row->action ?? 'pending'; // null action means pending
            if (!isset($progressCounts[$row->case_id])) {
                // Skip cases that are not assigned or open
                continue;
            }
            $progressCounts[$row->case_id][$action] = $row->total;
        }

        return view('investigator.progress_form', [
            'assignedCases' => $assignedCases,
            'progressEntries' => $progressEntries,
            'progressCounts' => $progressCounts,
        ]);
    }

    /**
     * Store progress log submitted by investigator.
     */
    public function storeProgress(Request $request)
    {
        $request->validate([
            'case_id' => 'required|exists:cases,case_id',
            'date' => 'required|date',
            'notes' => 'required|string|max:2000',
        ]);

        $userId = auth()->user()->user_id;
        $staffProfile = DB::table('police_staff')->where('user_id', $userId)->first();
        if (!$staffProfile) {
            return back()->with('error', 'Your staff profile is missing.');
        }

        DB::table('investigation_progress')->insert([
            'case_id' => $request->case_id,
            'staff_id' => $staffProfile->staff_id,
            'date' => $request->date,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Progress logged successfully.');
    }

    /**
     * Approve or reject a progress update.
     */
    public function approve(Request $request)
    {
        $request->validate([
            'progress_id' => 'required|exists:investigation_progress,progress_id',
            'action' => 'required|in:approve,reject',
            'recommendation' => 'nullable|string|max:1000',
        ]);

        $supervisorUserId = auth()->user()->user_id;
        $staffProfile = DB::table('police_staff')->where('user_id', $supervisorUserId)->first();
        if (!$staffProfile) {
            return back()->with('error', 'Supervisor staff profile not found.');
        }

        $staffId = $staffProfile->staff_id;
        $approved = $request->action === 'approve';

        DB::table('investigation_progress')
            ->where('progress_id', $request->progress_id)
            ->update([
                'recommendations' => $request->recommendation,
                'action' => $request->action,
                'action_by' => $staffId,
                'updated_at' => now(),
            ]);

        $progress = DB::table('investigation_progress')->where('progress_id', $request->progress_id)->first();
        if ($progress) {
            $case = DB::table('cases')->where('case_id', $progress->case_id)->first();
            $investigatorStaff = DB::table('police_staff')->where('staff_id', $progress->staff_id)->first();
            if ($investigatorStaff) {
                $user = User::where('user_id', $investigatorStaff->user_id)->first();
                if ($user) {
                    Notification::send($user, new ProgressApprovalNotification($progress, $approved, $request->recommendation, $case->case_id, $case->case_number));
                }
            }
        }

        return back()->with('success', 'Progress update has been ' . ($approved ? 'approved' : 'rejected') . ' successfully.');
    }
}

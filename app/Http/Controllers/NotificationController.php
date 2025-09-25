<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications;

        $staff = Staff::where('user_id', $user->user_id)->first();

        if (!$staff) {
            abort(403, 'Unauthorized: No staff profile found.');
        }

        $role = strtolower(trim($staff->role ?? ''));

        if ($role === 'supervisor') {
            $notifications = $notifications->filter(function ($notification) use ($staff) {
                $caseDeptId = $notification->data['case_department_id'] ?? null;

                // Fallback: show all notifications if key is missing (for debugging)
                if (!$caseDeptId) return true;

                return $caseDeptId == $staff->department_id;
            })->values();

        } elseif ($role === 'investigator') {
            $assignedCaseIds = $staff->assignedCases()->pluck('case_id')->toArray();

            $notifications = $notifications->filter(function ($notification) use ($staff, $assignedCaseIds) {
                $data = $notification->data ?? [];

                $isUploader = isset($data['evidence_id']) &&
                    Evidence::where('evidence_id', $data['evidence_id'])
                        ->where('uploaded_by_staff_id', $staff->staff_id)
                        ->exists();

                $isAssigned = isset($data['case_id']) &&
                    in_array($data['case_id'], $assignedCaseIds);

                return $isUploader || $isAssigned;
            })->values();
        }

        // Paginate manually since we're not using Eloquent paginate here
        $perPage = 10;
        $page = request()->get('page', 1);
        $paged = $notifications->forPage($page, $perPage);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $paged,
            $notifications->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('investigator.notifications.index', ['notifications' => $paginated]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return redirect()->back()->with('error', 'Notification not found.');
        }

        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }
}

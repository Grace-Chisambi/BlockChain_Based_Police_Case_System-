<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Staff;

class AdminController extends Controller
{
    public function index()
    {
        // Fetch staff list with department info
        $staffList = DB::table('police_staff')
            ->leftJoin('departments', 'police_staff.department_id', '=', 'departments.department_id')
            ->leftJoin('users', 'police_staff.user_id', '=', 'users.user_id')
            ->select(
                'police_staff.staff_id',
                'police_staff.user_id',
                'users.sname as sname',
                'departments.name as department_name',
                'police_staff.available',
                'police_staff.specialization',
                'police_staff.created_at'
            )
            ->orderBy('police_staff.created_at', 'desc')
            ->paginate(5);


        // Metrics
        $casesCount = DB::table('cases')->count();
        $complaintsCount = DB::table('complaints')->count();
        $usersCount = DB::table('users')->count();

        // Latest Logs (activity)
        $latestLogs = DB::table('blockchain_logs')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($log) {
                $log->created_at = Carbon::parse($log->created_at);
                return $log;
            });


        return view('admin.admin_dash', compact(
            'staffList',
            'casesCount',
            'complaintsCount',
            'usersCount',
            'latestLogs',
           
        ));
    }
}

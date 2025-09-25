<?php

namespace App\Http\Controllers;

use App\Models\PoliceCase;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; 

class DashboardController extends Controller
{
    public function index()
    {
        $casesCount = PoliceCase::count();
        $complaintsCount = Complaint::count();
        $totalLogs = Log::count();
        $usersCount = User::count();
        $latestLogs = Log::latest()->limit(5)->get();

        // Fetch crime locations from complaints
        $crimeLocations = PoliceCase::join('complaints', 'cases.complaint_id', '=', 'complaints.complaint_id')
            ->select(
                'cases.case_number',
                'cases.case_type',
                'complaints.latitude',
                'complaints.longitude'
            )
            ->get();

        return view('pages.admin', compact(
            'casesCount',
            'complaintsCount',
            'usersCount',
            'totalLogs',
            'latestLogs',
            'crimeLocations'
        ));
    }

    /**
     * Show case report in HTML
     */
    public function showCaseReport($caseId)
    {
        $data = $this->getCaseReportData($caseId);
        return view('investigator.reports.case_detail', $data);
    }

    /**
     * Generate and download case report as PDF
     */
    public function showCaseReportPdf($caseId)
    {
        $data = $this->getCaseReportData($caseId);

        $pdf = Pdf::loadView('investigator.reports.case_detail_pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download("CaseReport_{$data['case']->case_number}.pdf");
    }

    /**
     * Shared logic to retrieve case report data
     */
    private function getCaseReportData($caseId)
    {
        $case = DB::table('cases')->where('case_id', $caseId)->first();
        if (!$case) {
            abort(404, 'Case not found.');
        }

        $complaint = DB::table('complaints')->where('complaint_id', $case->complaint_id)->first();
        $suspects = DB::table('suspects')->where('case_id', $caseId)->get();
        $progress = DB::table('investigation_progress')
            ->where('case_id', $caseId)
            ->orderBy('date', 'asc')
            ->get();
        $closure = DB::table('case_closures')
            ->where('case_id', $caseId)
            ->orderByDesc('closure_date')
            ->first();

        // Reporter (investigator)
        $user = DB::table('users')->where('user_id', $case->reported_by)->first();

        // Supervisor for this case, from police_staff table
        $supervisor = DB::table('police_staff as ps')
            ->join('users as u', 'ps.assigned_by', '=', 'u.user_id')
            ->where('ps.case_id', $caseId)
            ->where('ps.staff_id', $case->reported_by)
            ->where('ps.role', 'supervisor')
            ->select('u.*')
            ->first();

        return compact('case', 'complaint', 'suspects', 'progress', 'closure', 'user', 'supervisor');
    }
}

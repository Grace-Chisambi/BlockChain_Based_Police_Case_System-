<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
 use Illuminate\Support\Facades\Http;
use App\Models\CaseClosure;
use App\Models\PoliceCase;

class CaseClosureController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        if (!in_array($user->role, ['investigator', 'police_officer'])) {
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

        return view('investigator.case_closures', compact('cases'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'case_id' => 'required|exists:cases,case_id',
        'closure_type' => 'required|in:permanent,temporary,withdrawn',
        'reason' => 'required|string',
        'closure_date' => 'required|date',
    ]);

    $user = Auth::user();

    // Fetch staff profile
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

    // Ensure user is assigned to the case
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

    // Record the closure
    CaseClosure::create([
        'case_id' => $validated['case_id'],
        'staff_id' => $staffId,
        'closure_type' => $validated['closure_type'],
        'reason' => $validated['reason'],
        'closure_date' => $validated['closure_date'],
    ]);

    // Update case status
    $case = PoliceCase::find($validated['case_id']);
    $case->case_status = $validated['closure_type'] === 'temporary' ? 'temporarily_closed' : 'closed';
    $case->save();

    // Generate closure hash for blockchain
    $closureHash = hash('sha256', $case->case_number . $validated['closure_type'] . $validated['closure_date'] . now());

    // Call blockchain logging service with timeout & error handling
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
}

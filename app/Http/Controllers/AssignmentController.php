<?php

namespace App\Http\Controllers;

use App\Models\PoliceCase;
use App\Models\CaseAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    // Show the assignment form
    public function create()
    {
        // Fetch unassigned cases (those that do not have an entry in the case_assignments table)
        $unassignedCases = PoliceCase::whereNotIn('case_id', function ($query) {
            $query->select('case_id')->from('case_assignments');
        })->latest()->get();

        // Get the logged-in user's ID
        $userId = Auth::id();

        // Pass the unassigned cases and user ID to the view
        return view('assignments.create', compact('unassignedCases', 'userId'));
    }

    // Store the assignment in the database
    public function store(Request $request)
    {
        // Validate form input
        $request->validate([
            'case_id' => 'required|integer|exists:cases,case_id', 
            'role' => 'required|in:investigator,officer,prosecutor',
        ]);

        // Insert data into case_assignments table
        CaseAssignment::create([
            'case_id' => $request->case_id,
            'user_id' => $request->user_id,
            'role' => $request->role,
        ]);

        // Redirect or return success message
        return redirect()->route('assignments.create')->with('success', 'Assignment created successfully!');
    }
    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

}

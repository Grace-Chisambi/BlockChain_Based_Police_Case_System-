<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suspect;
use App\Models\PoliceCase;
use Illuminate\Support\Facades\Auth;

class SuspectsController extends Controller
{
    public function index()
    {
        $suspects = Suspect::latest()->paginate(10);
        return view('pages.suspects', compact('suspects'));
    }

    public function investigatorIndex()
    {
        $staffProfile = \DB::table('police_staff')->where('user_id', Auth::id())->first();

        if (!$staffProfile) {
            return back()->withErrors(['error' => 'Your staff profile is missing.']);
        }

        $staffId = $staffProfile->staff_id;

        $assignedCases = PoliceCase::whereHas('assignments', function ($query) use ($staffId) {
            $query->where('staff_id', $staffId)
                  ->where('role', 'investigator');
        })->get();

        $suspects = Suspect::whereIn('case_id', $assignedCases->pluck('case_id'))->get();

        return view('investigator.suspect', compact('assignedCases', 'suspects'));
    }

    // Show the form to add a new suspect
    public function create()
    {
        $userId = Auth::id();

        $staffProfile = \DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staffProfile) {
            return back()->withErrors(['error' => 'Your staff profile is missing.']);
        }

        $staffId = $staffProfile->staff_id;

        $assignedCases = PoliceCase::whereHas('assignments', function ($query) use ($staffId) {
            $query->where('staff_id', $staffId)
                  ->where('role', 'investigator');
        })->get();

        $suspects = Suspect::whereIn('case_id', $assignedCases->pluck('case_id'))->get();

        return view('investigator.suspect', compact('assignedCases', 'suspects'));
    }

    // Store a new suspect
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'required|exists:cases,case_id',
            'fname' => 'required|string|max:255',
            'sname' => 'required|string|max:255',
            'age' => 'required|integer',
            'village' => 'required|string|max:255',
            'job' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'statement' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $staffProfile = \DB::table('police_staff')->where('user_id', Auth::id())->first();

        if (!$staffProfile) {
            return back()->withErrors(['error' => 'Your staff profile is missing.']);
        }

        $staffId = $staffProfile->staff_id;

        // Check that the investigator is assigned to the case
        $isAssigned = PoliceCase::where('case_id', $request->case_id)
            ->whereHas('assignments', function ($query) use ($staffId) {
                $query->where('staff_id', $staffId)
                      ->where('role', 'investigator');
            })->exists();

        if (!$isAssigned) {
            return back()->withErrors(['error' => 'You are not assigned to this case.']);
        }

        Suspect::create($validated);

        return redirect()->route('suspect.create')->with('success', 'Suspect added successfully!');
    }

    // Edit form
    public function edit($id)
    {
        $suspect = Suspect::findOrFail($id);
        $userId = Auth::id();

        $staffProfile = \DB::table('police_staff')->where('user_id', $userId)->first();

        if (!$staffProfile) {
            return back()->withErrors(['error' => 'Your staff profile is missing.']);
        }

        $staffId = $staffProfile->staff_id;

        $cases = PoliceCase::whereHas('assignments', function ($query) use ($staffId) {
            $query->where('staff_id', $staffId)
                  ->where('role', 'investigator');
        })->get();

        return view('investigator.suspect', compact('suspect', 'cases'));
    }

    // Update suspect details
    public function update(Request $request, $id)
    {
        $suspect = Suspect::findOrFail($id);

        $validated = $request->validate([
            'case_id' => 'required|exists:cases,case_id',
            'fname' => 'required|string|max:255',
            'sname' => 'required|string|max:255',
            'age' => 'required|integer',
            'village' => 'required|string|max:255',
            'job' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'statement' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $suspect->update($validated);

        return redirect()->route('investigator.suspect')->with('success', 'Suspect updated successfully!');
    }

    public function show($id)
    {
        $suspect = Suspect::findOrFail($id);
        return view('investigator.show_suspect', compact('suspect'));
    }
}

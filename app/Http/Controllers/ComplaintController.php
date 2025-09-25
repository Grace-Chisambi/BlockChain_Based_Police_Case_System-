<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class ComplaintController extends Controller
{
    public function review()
    {
        $complaints = Complaint::where('is_converted', false)->latest()->paginate(10);
        return view('pages.review', compact('complaints'));
    }

    public function show($id)
    {
        $complaint = Complaint::findOrFail($id);
        return view('pages.complaint_show', compact('complaint'));
    }

    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return redirect()->route('complaints.review')->with('success', 'Complaint deleted successfully.');
    }

    public function create()
    {
        return view('investigator.complaint');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'sname' => 'required|string|max:255',
            'age' => 'required|integer',
            'village' => 'required|string|max:255',
            'job' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'statement' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        Complaint::create($validated);

        return redirect()->route('complaints.review')->with('success', 'Complaint created successfully.');
        
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;
use App\Models\Department;

class UserController extends Controller
{
    // Display all users with optional search
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%$search%")
                  ->orWhere('sname', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('role', 'like', "%$search%");
            });
        }

        $users = $query->get();

        return view('pages.user_management', compact('users'));
    }

    // Show the form to create a new user
    public function create()
    {
        $departments = Department::all();
        return view('pages.user_create', compact('departments'));
    }

    // Store a new user and staff profile
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'sname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:admin,investigator,supervisor,prosecutor,police_officer',
            'password' => 'required|string|min:6|confirmed',

            'department_id' => 'required_if:role,investigator,prosecutor,police_officer,supervisor|exists:departments,department_id',
            'available' => 'nullable|boolean',
            'specialization' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'fname' => $validated['fname'],
                'sname' => $validated['sname'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
                'is_active' => true, // default active
            ]);

            if (in_array($validated['role'], ['investigator','prosecutor', 'police_officer', 'supervisor'])) {
                Staff::create([
                    'user_id' => $user->user_id,
                    'department_id' => $validated['department_id'],
                    'available' => $validated['available'] ?? false,
                    'specialization' => $validated['specialization'],
                ]);
            }
        });

        return redirect()->route('user_management.page')->with('success', 'User created successfully!');
    }

    // Show the form to edit an existing user
    public function edit($id)
    {
        $user = User::with('policeStaff')->findOrFail($id);
        $departments = Department::all();
        return view('pages.user_edit', compact('user', 'departments'));
    }

    // Update an existing user and staff profile
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'sname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id . ',user_id',
            'role' => 'required|string|in:admin,investigator,supervisor,prosecutor,police_officer',

            'department_id' => 'required_if:role,investigator,prosecutor,police_officer,supervisor|exists:departments,department_id',
            'available' => 'nullable|boolean',
            'specialization' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $user = User::findOrFail($id);
            $user->update([
                'fname' => $validated['fname'],
                'sname' => $validated['sname'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ]);

            if (in_array($validated['role'], ['investigator','prosecutor', 'police_officer', 'supervisor'])) {
                Staff::updateOrCreate(
                    ['user_id' => $user->user_id],
                    [
                        'department_id' => $validated['department_id'],
                        'available' => $validated['available'] ?? false,
                        'specialization' => $validated['specialization'],
                    ]
                );
            } else {
                Staff::where('user_id', $user->user_id)->delete();
            }
        });

        return redirect()->route('user_management.page')->with('success', 'User updated successfully.');
    }

    // Instead of delete, toggle active status for single user
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('user_management.page')->with('success', "User {$status} successfully.");
    }

    // Bulk activate or deactivate users
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $userIds = $request->input('user_ids', []);

        if (empty($userIds)) {
            return redirect()->route('user_management.page')->with('error', 'No users selected.');
        }

        switch ($action) {
            case 'activate':
                User::whereIn('user_id', $userIds)->update(['is_active' => true]);
                return redirect()->route('user_management.page')->with('success', 'Selected users activated.');
            case 'deactivate':
                User::whereIn('user_id', $userIds)->update(['is_active' => false]);
                return redirect()->route('user_management.page')->with('success', 'Selected users deactivated.');
            default:
                return redirect()->route('user_management.page')->with('error', 'Invalid action.');
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user || !$user->role) {
            Auth::logout();
            return redirect('/')->withErrors(['error' => 'Unauthorized role access.']);
        }

        switch ($user->role) {
            case 'admin':
                return redirect()->intended('admin/admin_dash');
            case 'supervisor':
                return redirect()->intended('/admin');
            case 'investigator':
            case 'police_officer':
                return redirect()->intended('/investigator/dash');
            case 'prosecutor':
                return redirect()->intended('/prosecutor/dashboard');

            default:
                Auth::logout();
                return redirect('/')->withErrors(['error' => 'Role not recognized.']);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

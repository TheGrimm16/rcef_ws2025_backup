<?php

namespace App\Http\Controllers\SeedReplacement;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SRDashboardController extends Controller
{
    public function index()
    {
        // Detect which guard is active
        if (Auth::guard('seed_replacement')->check()) {
            $guard = 'seed_replacement';
        } elseif (Auth::guard('web')->check()) {
            $guard = 'web';
        } else {
            return redirect()->route('login');
        }

        $user = Auth::guard('seed_replacement')->user()->load('roles');

        // Example: show roles in view
        foreach ($user->roles as $role) {
            // echo $role->display_name ?: $role->name;
        }

        // Example: check a specific role
        if ($user->hasRole('admin')) {
            // grant admin features
        }

        // Pass user + roles to view
        return view('seed_replacement.dashboard.index', compact('user'));
    }
}

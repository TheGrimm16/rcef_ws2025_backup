<?php

namespace App\Http\Controllers\ReplacementSeeds;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RSDashboardController extends Controller
{
    public function index()
    {
        // Detect which guard is active
        if (Auth::guard('replacement_seeds')->check()) {
            $guard = 'replacement_seeds';
        } elseif (Auth::guard('web')->check()) {
            $guard = 'web';
        } else {
            return redirect()->route('login');
        }

        $user = Auth::guard('replacement_seeds')->user()->load('roles');

        // Example: show roles in view
        foreach ($user->roles as $role) {
            echo $role->display_name ?: $role->name;
        }

        // Example: check a specific role
        if ($user->hasRole('admin')) {
            // grant admin features
        }

        // Pass user + roles to view
        return view('replacement_seeds.dashboard.index', compact('user'));
    }
}

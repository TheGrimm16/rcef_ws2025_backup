<?php

namespace App\Http\Controllers\SeedReplacement;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SRDashboardController extends Controller
{
    public function index()
    {
        // Prefer seed_replacement user if available
        if (Auth::guard('seed_replacement')->check()) {
            $user = Auth::guard('seed_replacement')->user()->load('roles');
        } elseif (request()->has('logMw_user')) {
            $user = request()->get('logMw_user'); // from session injected by middleware
        } else {
            // This should never happen, middleware already blocks
            abort(403, 'Unauthorized access');
        }

        return view('seed_replacement.dashboard.index', compact('user'));
    }

}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SeedReplacementAuth
{
    public function handle($request, Closure $next)
    {
        $guard = Auth::guard('seed_replacement');

        if (!$guard->check()) {
            // Preserve intended URL and flash warning
            return redirect()->route('replacement.login')
                 ->with('warning', 'Please login first to access that page.')
                 ->with('url.intended', $request->fullUrl());

        }

        return $next($request);
    }
}

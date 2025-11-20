<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MultiGuardAuth
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('seed_replacement')->check() ||
            Auth::guard('web')->check()) {

            return $next($request);
        }

        return redirect()->route('replacement.login')
            ->with('warning', 'Please login first to access that page.')
            ->with('url.intended', $request->fullUrl());
    }
}

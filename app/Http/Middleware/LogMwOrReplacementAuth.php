<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class LogMwOrReplacementAuth
{
    public function handle($request, Closure $next)
    {
        // Check logMw session
        $logMwPassed = $request->session()->get('logMw_passed', false);

        // Check seed_replacement guard
        $replacementPass = Auth::guard('seed_replacement')->check();

        if ($logMwPassed) {
            // Optionally attach a "logMw user" object to request
            $request->merge(['logMw_user' => $request->session()->get('logMw_user')]);
        }

        if ($logMwPassed || $replacementPass) {
            return $next($request);
        }

        return abort(403, 'Unauthorized access');
    }
}

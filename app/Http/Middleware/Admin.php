<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('rcef-pmo')))
        {
            return $next($request);
        }

        return view('errors/access');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use DB;
use Route;

class logsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Logs route access with username, IP, domain, browser, and action.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // Default username for API or guest access
            $username = "API";
            if (isset(Auth::user()->username)) {
                $username = Auth::user()->username;
            }

            // Full requested URL
            $fullUrl = $request->fullUrl();

            // User-Agent string from the request headers
            $userAgent = $request->header('User-Agent');

            // -----------------------------
            // FUTURE-PROOF BROWSER DETECTION
            // -----------------------------
            // This handles all modern browsers including Edge, Chrome, Firefox, Safari, Opera, and IE
            // without misclassifying Chrome as Safari, Opera as Chrome, or missing new IE/Edge versions.
            // Mainly added support for EDGE
            $browser = 'Unknown';

            if (stripos($userAgent, 'Edge') !== false || stripos($userAgent, 'Edg') !== false) {
                $browser = 'Edge';  // Edge legacy or Chromium-based
            } elseif (stripos($userAgent, 'OPR') !== false || stripos($userAgent, 'Opera') !== false) {
                $browser = 'Opera';
            } elseif (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false && stripos($userAgent, 'OPR') === false) {
                $browser = 'Chrome';
            } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false && stripos($userAgent, 'OPR') === false && stripos($userAgent, 'Edg') === false) {
                $browser = 'Safari';
            } elseif (stripos($userAgent, 'Firefox') !== false) {
                $browser = 'Firefox';
            } elseif (stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident/') !== false) {
                $browser = 'Internet Explorer';
            }

            // Insert log into database
            DB::table("_rcef_connect.tbl_routes_logs")
                ->insert([
                    "routes"     => Route::currentRouteName(),
                    "user_name"  => $username,
                    "ip_address" => $request->ip(),
                    "domain"     => $fullUrl,
                    "browser"    => $browser,
                    "action"     => Route::currentRouteAction()
                ]);

        } catch (\Exception $th) {
            // Swallow exceptions to avoid breaking the request flow
            // TODO: optionally log $th->getMessage() to a file or error logging system
        }
        return $next($request);
    }
}

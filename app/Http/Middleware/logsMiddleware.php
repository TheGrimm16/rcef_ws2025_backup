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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            $username = "API";
           if(isset(Auth::user()->username)){
               $username =  Auth::user()->username;
           }
            // //LOGS DATA
            $fullUrl = $request->fullUrl();
            $userAgent = $request->header('User-Agent');
            $browser = 'Unknown';
            if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
                $browser = 'Internet Explorer';
            } elseif (preg_match('/Firefox/i', $userAgent)) {
                $browser = 'Firefox';
            } elseif (preg_match('/Chrome/i', $userAgent)) {
                $browser = 'Google Chrome';
            } elseif (preg_match('/Safari/i', $userAgent)) {
                $browser = 'Safari';
            } elseif (preg_match('/Opera/i', $userAgent)) {
                $browser = 'Opera';
            }
    
                DB::table("_rcef_connect.tbl_routes_logs")
                    ->insert([
                        "routes" => Route::currentRouteName(),
                        "user_name" => $username,
                        "ip_address" => $request->ip(),
                        "domain" => $fullUrl,
                        "browser" => $browser,
                        "action" => Route::currentRouteAction()
                    ]);
            
            } catch (\Throwable $th) {
                //throw $th;
            }



        return $next($request);
    }
}

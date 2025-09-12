<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    
    public function boot()
    {
        // Season prefix
        $prefix = Cache::rememberForever('season_prefix', function () {
            return env('SEASON_PREFIX', 'ws2025_');
        });
        Config::set('app.season_prefix', $prefix);
        $GLOBALS['season_prefix'] = $prefix;

        // Python path
        $pythonPath = Cache::rememberForever('python_path', function () {
            $path = null;

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec('where python', $output, $return_var);
                if ($return_var === 0 && !empty($output)) $path = $output[0];
            } else {
                exec('which python3', $output, $return_var);
                if ($return_var === 0 && !empty($output)) $path = $output[0];
                else {
                    exec('which python', $output, $return_var);
                    if ($return_var === 0 && !empty($output)) $path = $output[0];
                }
            }

            return $path ?: 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
        });

        $GLOBALS['python_path'] = $pythonPath;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

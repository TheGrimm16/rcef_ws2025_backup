<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class TotalYieldCalculator
{   
    public static function getNationalYield()
    {
        $pythonPath = $GLOBALS['python_path'];
        $scriptPath = base_path('app/Http/PyScript/dashboard/national-yield.py');
        $command = escapeshellcmd("$pythonPath \"$scriptPath\"");

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Log the command, output, and status
        Log::info('TotalYieldCalculator: Executed command', [
            'command' => $command,
            'return_var' => $return_var,
            'output' => $output
        ]);

        if ($return_var !== 0 || empty($output)) {
            Log::error('TotalYieldCalculator: Script failed or returned no output', [
                'return_var' => $return_var,
                'output' => $output
            ]);
            return null;
        }

        // Attempt to parse and log the result
        $result = (float) trim($output[0]);
        Log::info('TotalYieldCalculator: Grand total parsed', ['grand_total' => $result]);

        return $result;
    }

    public static function getTotalYield()
    {
        $pythonPath = $GLOBALS['python_path'];
        $scriptPath = base_path('app/Http/PyScript/dashboard/total-yield.py');
        $command = escapeshellcmd("$pythonPath \"$scriptPath\"");

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Log the command, output, and status
        Log::info('TotalYieldCalculator: Executed command', [
            'command' => $command,
            'return_var' => $return_var,
            'output' => $output
        ]);

        if ($return_var !== 0 || empty($output)) {
            Log::error('TotalYieldCalculator: Script failed or returned no output', [
                'return_var' => $return_var,
                'output' => $output
            ]);
            return null;
        }

        // Attempt to parse and log the result
        $result = (float) trim($output[0]);
        Log::info('TotalYieldCalculator: Grand total parsed', ['grand_total' => $result]);

        return $result;
    }

    public static function finalAverageYield()
    {
        $pythonPath = $GLOBALS['python_path'];
        $scriptPath = base_path('app/Http/PyScript/dashboard/average-yield.py');
        $command = escapeshellcmd("$pythonPath \"$scriptPath\"");

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Log the command, output, and status
        Log::info('TotalYieldCalculator: Executed command', [
            'command' => $command,
            'return_var' => $return_var,
            'output' => $output
        ]);

        if ($return_var !== 0 || empty($output)) {
            Log::error('TotalYieldCalculator: Script failed or returned no output', [
                'return_var' => $return_var,
                'output' => $output
            ]);
            return null;
        }

        // Attempt to parse and log the result
        $result = (float) trim($output[0]);
        Log::info('TotalYieldCalculator: Grand total parsed', ['grand_total' => $result]);

        return $result;
    }

}

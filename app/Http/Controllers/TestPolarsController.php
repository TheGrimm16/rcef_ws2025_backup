<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Carbon\Carbon;

use DB;

class TestPolarsController extends Controller
{
    public static function index(Request $request)
    {
        $pythonPath = $GLOBALS['python_path'];
        $scriptPath = base_path('app/Http/PyScript/Testing_Polars.py');

        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbPort = 3306;
        $seasonPrefix = $GLOBALS['season_prefix'] ? : 'ws2025_';
        $dbName = 'sdms_db_dev';
        $tableNameBrgy = 'lib_geocodes';
        $tableNameMun = 'lib_municipalities';
        $tableNameProv = 'lib_provinces';
        // $coopAccreditation = $request->coop_accreditation ? : '';

        $command = escapeshellcmd(
            "$pythonPath \"$scriptPath\" "
            . escapeshellarg($dbHost) . " "
            . escapeshellarg($dbUser) . " "
            . escapeshellarg($dbPass) . " "
            . escapeshellarg($dbPort) . " "
            . escapeshellarg($seasonPrefix) . " "
            . escapeshellarg($dbName) . " "
            . escapeshellarg($tableNameBrgy) . " "
            . escapeshellarg($tableNameMun) . " "
            . escapeshellarg($tableNameProv) . " "
            // . escapeshellarg($coopAccreditation)
        );

        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Join lines and decode JSON
        $jsonString = implode("\n", $output);
        $data = json_decode($jsonString, true);

        dd($data);

        // Return as JSON response
        return response()->json([
            'success' => $return_var === 0,
            'data' => $data,
        ]);
    }

}
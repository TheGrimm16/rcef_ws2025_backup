<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Datatables;
use Auth;
use App\File;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ffrsDataUploaderController extends Controller
{
    public function index()
    {
        return view("ffrsDataUploader.index");
    }


    public function upload(Request $request)
    {
        $season = $GLOBALS['season_prefix'];
        $path = $request->file('inputFile')->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $headers = $data[0]; // The first row contains the headers
        $rows = array_slice($data, 1); // The rest are the data rows
        $mappedData = [];
        foreach ($rows as $row) {
            // Combine the headers with the corresponding row to form an associative array
            $mappedData[] = array_combine($headers, $row);
        }
        // unset($data[0]);  // Remove header, for example
        // dd($mappedData);

        $data = $mappedData; // Or fetch the data however you're doing it

        // Convert array to JSON
        $jsonData = json_encode($data);
        // Save the JSON data to a temporary file
        $filePath = storage_path('data.json');   
        
        // Uncomment for live
        // $filePath = 'C:\\Apache24\\htdocs\\rcef_ds2025\\storage\\data.json';
        // if (!file_exists($filePath)) {
        //     // If the file does not exist, create it
        //     $file = fopen($filePath, "w");
        //     fclose($file);
        // }

        file_put_contents($filePath, $jsonData);

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
        $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

        $scriptPath = base_path('app//Http//PyScript//ffrsDataUploader//dataUpload.py');

        $escapedSeason = escapeshellarg($season);
        $command = "$pythonPath \"$scriptPath\" $filePath $escapedSeason";
        
        $process = new Process($command);

        try {
            $process->mustRun();
            $farmer_profile_final = $process->getOutput();
            // dd($farmer_profile_final);
            return $farmer_profile_final;
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
    }


    

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Yajra\Datatables\Facades\Datatables;
use App\utility;

class FarmerKYCMonitoringController extends Controller
{
    public function home_ui(){


        // if(Auth::user()->roles->first()->name != "rcef-programmer"){
        //     $mss = "Under Development";
        //         return view("utility.pageClosed")
        //     ->with("mss",$mss);
        // }

        $totalClustersVerified = 0;
        $totalClusters = 0;
        $totalProfilesVerified = 0;
        $totalProfilesVerified = 0;
		$clustersPercentage = 0;
		$profilesPercentage = 0;

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//farmerKYC//statistics.py');


		$command = "$pythonPath \"$scriptPath\" ";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
			if (strlen($data) > 5)
			{
                $finalData = json_decode(json_decode($data));

                // dd($finalData);
                $totalClustersVerified = number_format($finalData[0]->totalClustersVerified);
                $totalClusters = number_format($finalData[0]->totalClusters);
                $totalProfilesVerified = number_format($finalData[0]->totalProfilesVerified);
                $totalProfiles = number_format($finalData[0]->totalProfiles);
				$clustersPercentage = number_format(($finalData[0]->totalClustersVerified/$finalData[0]->totalClusters) * 100, 2);
				$profilesPercentage = number_format(($finalData[0]->totalProfilesVerified/$finalData[0]->totalProfiles) * 100, 2);

			}
			else
			{
				return ("No data available");
			}
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

        // return view('farmerVerification.encoderMonitor');

        return view('farmerVerification.encoderMonitor',
        compact(
            'totalClustersVerified',
            'totalClusters',
            'totalProfiles',
            'totalProfilesVerified',
			'clustersPercentage',
            'profilesPercentage'
        ));    
    }


    public function getOverallData(){
        $today = Carbon::now();
        $dateToday = $today->format('Y-m-d');

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//farmerKYC//monitoring.py');

		$escapedDateToday = escapeshellarg($dateToday);

		$command = "$pythonPath \"$scriptPath\" $escapedDateToday";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
			if (strlen($data) > 5)
			{
                $finalData = json_decode(json_decode($data));   
                $finalData = collect($finalData);
        
                return Datatables::of($finalData)
                ->make(true);
			}
			else
			{
				return ("No data available");
			}
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}
    }

    public function getDatedOverallData(Request $request)
    {
        $date = $request->date;

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//farmerKYC//monitoring.py');

		$escapedDate = escapeshellarg($date);

		$command = "$pythonPath \"$scriptPath\" $escapedDate";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
			if (strlen($data) > 5)
			{
                $finalData = json_decode(json_decode($data));   
                $finalData = collect($finalData);
        
                return Datatables::of($finalData)
                ->make(true);
			}
			else
			{
				return ("No data available");
			}
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}
    }
    
}

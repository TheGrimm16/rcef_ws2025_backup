<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;
use Auth;
use App\Transfer;
use Illuminate\Support\Facades\Hash;
use Excel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
class plantingIntentionController extends Controller
{
	public function getStatistics($prv){
		// $GLOBALS['season_prefix'] = "ws2025_";
		// dd($GLOBALS['season_prefix']);
		$season = $GLOBALS['season_prefix'];

		$pythonPath = $GLOBALS['python_path'];

		$scriptPath = base_path('app//Http//PyScript//API//plantingIntentionStatistics.py');

		$escapedSeason = escapeshellarg($season);
		$escapedPrv = escapeshellarg($prv);
		$command = "$pythonPath \"$scriptPath\" $escapedPrv $escapedSeason";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
			if (strlen($data) > 5)
			{
				return json_decode(json_encode($data));
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

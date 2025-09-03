<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;
use Hash;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;
use App\utility;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DeliveryStatusController extends Controller
{
    public function index()
    {
        $season = $GLOBALS['season_prefix'];
        $coops = DB::table($season.'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('isActive', 1)
        ->get();

        return view('deliveryStatus.index', compact('coops'));
    }

    public function getCoopData(Request $request)
    {
        $season = $GLOBALS['season_prefix'];
        $coop = $request->coop;


        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		$pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//coopDeliveryStatus.py');

		$escapedSeason = escapeshellarg($season);
		$escapedCoop = escapeshellarg($coop);
		$command = "$pythonPath \"$scriptPath\" $escapedSeason $escapedCoop";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            $data = json_decode($process->getOutput(), true);
            return Datatables::of(collect($data))->make(true);
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

        
    }
}

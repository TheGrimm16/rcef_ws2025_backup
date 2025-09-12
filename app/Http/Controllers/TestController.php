<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use DB;
use Session;
use Auth;
use Excel;
use QrCode;
use Carbon\Carbon;
use Yajra\Datatables\Facades\Datatables;
class TestController extends Controller
{
    function index(){

            if(Auth::user()->roles->first()->name != "rcef-programmer"){
                $mss = "Under Development";
                    return view("utility.pageClosed")
                ->with("mss",$mss);
            }
            // $pre_reg_province = array("PAMPANGA");

            $provinces_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            // ->whereIn('province',$pre_reg_province)
            ->groupBy('province')
            ->orderBy('prv', 'ASC')
            ->get();

            return view('FarGeneration.pre_registration')
                ->with(compact('provinces_list', $provinces_list));

        // return view('FarGeneration.pre_registration');
			// ->with('region_list', $regions)
            // ->with('seed_variety', $seed_variety);
    }
}
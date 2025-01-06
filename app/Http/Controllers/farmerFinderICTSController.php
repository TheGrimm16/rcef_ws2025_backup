<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class farmerFinderICTSController extends Controller
{
    public function index(){
        return view('nrp.icts.index');
    }

    public function currentStatus(Request $request){
        $prvCode =  str_replace("-","",substr($request->rsbsa,0,5));
        $checker =  DB::table($GLOBALS['season_prefix'].'prv_'.$prvCode.'.farmer_information_final')
        ->where('rsbsa_control_no',$request->rsbsa)
        ->count();
        return  $checker;

    }
    
    
}

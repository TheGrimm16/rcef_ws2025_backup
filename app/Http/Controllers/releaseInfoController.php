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

use Yajra\Datatables\Facades\Datatables;
use App\utility;

class releaseInfoController extends Controller
{
        public function home_ui(){
        $regionNames = array();
        $regionCodes = array();
        $regionsArray = array();
        $munArray = array();
        $provArray = array();

        $getRegions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('regCode','regionName')
        ->groupBy('regCode')
        ->where('regCode','!=',99)
        ->get();

        // dd($getRegions);
        
        foreach($getRegions as $reg)
        {
            array_push($regionNames, $reg->regionName);
            array_push($regionCodes, $reg->regCode);
        }

        // dd($regionNames,$regionCodes);
        
        // return view('replacements.home',
        //  compact(
        //     'regionNames',
        //     'regionCodes'
        //  ));
        return view('releaseInfo.home',
         compact(
            'regionNames',
            'regionCodes'
         ));
        
    }

    public function releaseInfoProvinces(Request $request)
    {
        $getProvinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('province')
        ->where('regionName',$request->reg)
        ->groupBy('province')
        ->get();
        return $getProvinces;
    }
    

    public function releaseInfoMunicipalities(Request $request)
    {
        $getMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('municipality')
        ->where('province',$request->prov)
        ->groupBy('municipality')
        ->get();
        return $getMunicipalities;
    }

    
    public function getreleaseInfo(Request $request)
    {
        
        $getPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();
        
        $prv = $getPrv->prv_code;

        if($request->rsbsa==''){
            $getreleaseInfo = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.new_released')
            ->get();
        }
        else{
            $getFarmerInfo = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
            ->where('rsbsa_control_no','LIKE',$request->rsbsa)
            ->get();

            foreach($getFarmerInfo as $farmer)
            {
                $getreleaseInfo = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.new_released')
                ->where('db_ref','LIKE',$farmer->db_ref)
                ->get();
            }
        }


        $getreleaseInfo = collect($getreleaseInfo);

        return Datatables::of($getreleaseInfo)
            ->make(true);
    }


    
}

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

class fcaTaggingController extends Controller
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
        return view('fcaTagging.home',
         compact(
            'regionNames',
            'regionCodes'
         ));
        
    }

    public function getFCAProvinces(Request $request)
    {
        $getProvinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('province')
        ->where('regionName',$request->reg)
        ->groupBy('province')
        ->get();
        return $getProvinces;
    }
    

    public function getFCAMunicipalities(Request $request)
    {
        $getMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('municipality')
        ->where('province',$request->prov)
        ->groupBy('municipality')
        ->get();
        return $getMunicipalities;
    }

    
    public function getFCAFarmers(Request $request)
    {
        $getPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();
        
        $prv = $getPrv->prv_code;

        $getFarmerInfo = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
        // ->where('is_new','!=',9)
        // ->where('is_new','!=',7)
        ->get();
        // dd($getFarmerInfo);

        $getFarmerInfo = collect($getFarmerInfo);

        return Datatables::of($getFarmerInfo)
        ->addColumn('action', function($row){
            return  "<input type='checkbox' class='toReplace' data-claimingPrv='".$row->claiming_prv."' data-dbref='".$row->db_ref."' data-rsbsa='".$row->rsbsa_control_no."'>";            
        })
            ->make(true);
    }

    public function tagFCA(Request $request)
    {
        $test= [];
        $getPrvCode = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();

        $prv = $getPrvCode->prv_code;

        $toBeTagged = $request->toBeTagged;

        foreach($toBeTagged as $row)
        { 
            DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
            ->where('rsbsa_control_no','LIKE', $row['rsbsa'])
            ->where('db_ref','LIKE', $row['dbref'])
            ->update([
                "is_new" => 9
            ]);
        }

        return 1;
    }

    public function tagHomeClaim(Request $request)
    {
        $test= [];
        $getPrvCode = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();

        $prv = $getPrvCode->prv_code;

        $toBeTagged = $request->toBeTagged;

        foreach($toBeTagged as $row)
        { 
            DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
            ->where('rsbsa_control_no','LIKE', $row['rsbsa'])
            ->where('db_ref','LIKE', $row['dbref'])
            ->update([
                "is_new" => 7
            ]);
        }

        return 1;
    }

    
}

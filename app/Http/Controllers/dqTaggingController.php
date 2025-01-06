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

class dqTaggingController extends Controller
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
        return view('dqTagging.home',
         compact(
            'regionNames',
            'regionCodes'
         ));
        
    }

    public function getDQProvinces(Request $request)
    {
        $getProvinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('province')
        ->where('regionName',$request->reg)
        ->groupBy('province')
        ->get();
        return $getProvinces;
    }
    

    public function getDQMunicipalities(Request $request)
    {
        $getMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('municipality')
        ->where('province',$request->prov)
        ->groupBy('municipality')
        ->get();
        return $getMunicipalities;
    }

    
    public function getDQFarmers(Request $request)
    {
        $getPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();
        
        $prv = $getPrv->prv_code;

        $getFarmerInfo = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
        // ->where('is_new','!=',9)
        // ->where('prev_claimed','!=',0)
        ->get();
        // dd($getFarmerInfo[0]);

        $getFarmerInfo = collect($getFarmerInfo);

        return Datatables::of($getFarmerInfo)
        ->addColumn('action', function($row){
            return  "<input type='checkbox' class='toReplace' data-claimingPrv='".$row->claiming_prv."' data-dbref='".$row->db_ref."' data-rsbsa='".$row->rsbsa_control_no."'>";            
        })
        // ->addColumn('action2', function($row){
        //     return "<button type='button' class='btn btn-success btn-sm' data-toggle='modal' data-target='#reasonModal'>Add reason</button>";         
        // })
            ->make(true);
    }

    public function tagDQ(Request $request)
    {
        $test= [];
        $getPrvCode = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();
        dd($getPrvCode);
        $prv = $getPrvCode->prv_code;
        
        $toBeTagged = $request->toBeTagged;
        $reason = $request->reason;
        

        foreach($toBeTagged as $row)
        { 
            $verifyData = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
            ->where('rsbsa_control_no','LIKE', $row['rsbsa'])
            ->where('db_ref','LIKE', $row['dbref'])
            ->first();
            // ->update([
            //     "is_new" => 9
            // ]);

            
            if ($verifyData && round($verifyData->prev_claimed_area, 1) > round($verifyData->prev_final_area, 1) && $verifyData->prev_claimed > $verifyData->prev_claimable) 
            {
                $reason = 'The system detected that this farmer profile is tagged as not-eligible to claim seeds, reason: exceeded claim, during DS2024 - total parcel area is '.round($verifyData->prev_final_area, 1).'ha equivalent to '.$verifyData->prev_claimable.' bags but actual claim is '.round($verifyData->prev_claimed_area, 1).'ha equivalent to '.$verifyData->prev_claimed.' bags.';
            }
            else
            {
                $reason = $request->reason;
            }

        }

        return 1;
    }


    
}

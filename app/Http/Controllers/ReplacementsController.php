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

class ReplacementsController extends Controller
{
        public function home_ui(){
        $regionNames = array();
        $regionCodes = array();
        $regionsArray = array();
        $munArray = array();
        $provArray = array();

        $getRegions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select('region')
        ->groupBy('region')
        ->get();

        // dd($getRegions);
        
        foreach($getRegions as $reg){
            $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                ->select('regCode')
                                ->where('regionName', $reg->region)
                                ->limit(1)
                                ->get();
                            array_push($regionNames, $reg->region);
                            array_push($regionCodes, $regions[0]->regCode);
        }

        // dd($regionNames,$regionCodes);
        
        // return view('replacements.home',
        //  compact(
        //     'regionNames',
        //     'regionCodes'
        //  ));
        return view('replacements.home',
         compact(
            'regionNames',
            'regionCodes'
         ));
        
    }

    public function getReplacementProvinces(Request $request)
    {
        $getProvinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select('province')
        ->where('region',$request->reg)
        ->groupBy('province')
        ->get();
        return $getProvinces;
    }
    

    public function getReplacementMunicipalities(Request $request)
    {
        $getMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select('municipality')
        ->where('province',$request->prov)
        ->groupBy('municipality')
        ->get();
        return $getMunicipalities;
    }

    
    public function getFarmers(Request $request)
    {

        $getPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code','prv')
        ->where('province', 'LIKE',$request->prov)
        ->where('municipality', 'LIKE', $request->muni)
        ->groupBy('prv_code')
        ->first();
        
        // dd($getPrv);
        $prv = $getPrv->prv_code;
        $muni = $getPrv->prv;

        $getFarmerInfo = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
        ->whereRaw("REPLACE(claiming_prv, '-', '') LIKE ?", [$muni])
        ->where('is_claimed', 1)
        ->where('is_replacement', 0)
        ->get();
        // dd($getFarmerInfo);

        $getFarmerInfo = collect($getFarmerInfo);

        return Datatables::of($getFarmerInfo)
        ->addColumn('action', function($row){
            return  "<input type='checkbox' class='toReplace' data-claimingPrv='".$row->claiming_prv."' data-dbref='".$row->db_ref."' data-rsbsa='".$row->rsbsa_control_no."'>";            
        })
            ->make(true);
    }

    public function tagReplacements(Request $request)
    {
        // dd($request->all());
        $test= [];
        $getPrvCode = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code')
        ->where('province', 'LIKE',$request->prov)
        ->groupBy('prv_code')
        ->first();

        $prv = $getPrvCode->prv_code;

        $toBeReplaced = $request->toBeReplaced;

        foreach($toBeReplaced as $row)
        { 
            DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
            ->where('rsbsa_control_no','LIKE', $row['rsbsa'])
            ->where('db_ref','LIKE', $row['dbref'])
            ->update([
                "is_replacement" => 1,
                "replacement_reason" => $request->reason
            ]);
        }

        return 1;
    }

    
}

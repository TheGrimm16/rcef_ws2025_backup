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

class BepCoopCheckerController extends Controller
{
        public function home_ui(){
        $regionNames = array();
        $regionCodes = array();
        $regionsArray = array();
        $munArray = array();
        $provArray = array();

        $getRegions = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
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
        return view('BepCoopChecker.home',
         compact(
            'regionNames',
            'regionCodes'
         ));
        
    }

    public function getBepCoopCheckerProvinces(Request $request)
    {
        $getProvinces = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
        ->select('province')
        ->where('regionName',$request->reg)
        ->groupBy('province')
        ->get();
        return $getProvinces;
    }
    

    public function getBepCoopCheckerMunicipalities(Request $request)
    {
        $getMunicipalities = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
        ->select('municipality')
        ->where('province',$request->prov)
        ->groupBy('municipality')
        ->get();
        return $getMunicipalities;
    }

    
    public function updateCoops(Request $request)
    {
        $toBeUpdated = [];
        $noUpdate = [];
        $updatedEntries = 0;
        $getTblClaim = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
        ->where('province', 'LIKE',$request->prov)
        ->where('municipality','LIKE',$request->muni)
        ->get();

        foreach($getTblClaim as $claim)
        {   $seedTag = explode('/',$claim->seedTag);
            $labNo = $seedTag[0];
            $lotNo = $seedTag[1];

            $getRLA = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('labNo',$labNo)
            ->where('lotNo',$lotNo)
            ->get();
            if($getRLA){
                if(count($getRLA) > 1)
                {
                }
                else{
    
                    if($claim->coopAccreditation == $getRLA[0]->coopAccreditation)
                    {
    
                    }
                    else{
                        DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                        ->where('seedTag',$claim->seedTag)
                        ->update([
                            'coopAccreditation' => $getRLA[0]->coopAccreditation
                        ]);
                        $updatedEntries++;
                    }
                }
            }

        }

        // dd($noUpdate,$toBeUpdated,$updatedEntries);
        return $updatedEntries;
    }


    public function getMultipleRLA(Request $request)
    {
        $noUpdate = [];
        $getTblClaim = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
        ->where('province', 'LIKE',$request->prov)
        ->where('municipality','LIKE',$request->muni)
        ->get();

        foreach($getTblClaim as $claim)
        {   $seedTag = explode('/',$claim->seedTag);
            $labNo = $seedTag[0];
            $lotNo = $seedTag[1];

            $getRLA = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('labNo',$labNo)
            ->where('lotNo',$lotNo)
            ->get();
            if($getRLA){
                if(count($getRLA) > 1)
                {
                    array_push($noUpdate,$claim);
                }
                else{
                }
            }

        }

        $noUpdate = collect($noUpdate);

        return Datatables::of($noUpdate)
            ->make(true);
    }


    
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Config;
use DB;


class HistoryMonitoring extends Model
{
    function changeRegionName($region){
        $data = array(
            "REGION I" => "ILOCOS",
            "REGION II" => "CAGAYAN VALLEY",
            "REGION III" => "CENTRAL LUZON",
            "REGION IV-A" => "CALABARZON",
            "REGION IV-B" => "MIMAROPA",
            "REGION V" => "BICOL",
            "REGION VI" => "WESTERN VISAYAS",
            "REGION VII" => "CENTRAL VISAYAS",
            "REGION VIII" => "EASTERN VISAYAS",
            "REGION IX" => "ZAMBOANGA PENINSULA",
            "REGION X" => "NORTHERN MINDANAO",
            "REGION XI" => "DAVAO",
            "REGION XII" => "BARMM",
            "NCR" => "NCR",
            "CAR" => "CAR",
            "BARMM" => "BARMM",
            "REGION XIII" => "CARAGA",
        );

        return $data;

    }

    function retrieveDOP($dopID){
        
        if($dopID!=''){
            $dop_details = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
         ->select('*')
         ->where('prv_dropoff_id', $dopID)
         ->first();
        
        $data = $dop_details->province.' - '.$dop_details->municipality.' -> '.$dop_details->dropOffPoint;
        }else{
        $data = "No Drop off Point ID stored";
        }

        

        return $data;     
    }


    function getTransferLogInfo($infos,$batch_number,$dopID){  //CSTOCS
        $tbc = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('*')
            ->where('prv_dropoff_id', $dopID)
            ->where('remarks','like','%'.$batch_number.'%')
            ->sum('totalBagCount');  
         $counts = $tbc;
        //dd($counts);
        
        
        $dataInfo =  DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->select('*')
            ->where('destination_dop_id', $dopID)
            ->where('batch_number', $batch_number)
            //->where('bags', '=',intval($counts))
            ->where('seed_variety', 'PARTIAL_SEEDS_TRANSFER')
            ->first();


        if(isset($dataInfo->$infos)){
            $data = $dataInfo->$infos;
        }else{
            $data = 0;
        }


          // $data = $dataInfo->origin_dop_id;
       // return $data;
           return $data;
    }


    function getTransferLogInfo2($infos,$seedVariety,$dopID,$bags){  //PSTOCS
        
        $dataInfo =  DB::connection('ls_rcep_transfers_db')->table('transfer_logs')
            ->select('*')
            ->where('prv_dropoff_id', $dopID)
            ->where('seed_variety', $seedVariety)
            ->where('bags', '=',intval($bags))
            ->first();


        if(isset($dataInfo->$infos)){
            $data = $dataInfo->$infos;
        }else{
            $data = "NO INFO";
        }

       // dd($data);
          // $data = $dataInfo->origin_dop_id;
       // return $data;
           return $data;
    }




}

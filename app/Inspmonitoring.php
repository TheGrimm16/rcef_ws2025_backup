<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class Inspmonitoring extends Model {

    function _isReplacement($batch){
        $check = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch)
                ->where('isBuffer', 9)
                ->first();
        if(count($check)>0){
            return 1;
        }else{
            return 0;
        }
    }

    function _isPartialReplacement($batch){
       $check = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->where('remarks', 'transferred from batch: '.$batch)
                ->where('isBuffer', 0)
                ->first();
        if(count($check)>0){
            return 1;
        }else{
            return 0;
        } 
    }


    
     function set_rpt_db($conName,$database_name,$host,$port,$user,$pass){
        try {
            \Config::set('database.connections.'.$conName.'.host', $host);
            \Config::set('database.connections.'.$conName.'.port', $port);
            \Config::set('database.connections.'.$conName.'.database', $database_name);
            \Config::set('database.connections.'.$conName.'.username', $user);
            \Config::set('database.connections.'.$conName.'.password', $pass);
            DB::purge($conName);

            DB::connection($conName)->getPdo();
            return "Connected";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Refused";
            //return "error";
        }
    }


    function _get_pushed_data($batch){
            //WS2021
            $con = $this->set_rpt_db("ls_rcep_transfers_db","rcep_delivery_inspection","172.16.10.25","4409","rcef_web","SKF9wzFtKmNMfwy");
            //dd($con);
            if($con=="Connected"){
                 $data = DB::connection("ls_rcep_transfers_db")->table("tbl_actual_delivery")
                    ->where('remarks', "LIKE", '%transferred from previous season batch: '.$batch.'%')
                    ->groupBy("remarks")
                    ->get();
                  //  dd($data);
                //WS2020
                $con = $this->set_rpt_db("ls_rcep_transfers_db","rcep_transfers_ws","172.16.10.25","4406","rcef_user","SKF9wzFtKmNMfwyz");
                return $data;
            }else{
                return null;
            }
    }

    function _get_partial_list($batch){
        $data = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->where('remarks', "LIKE", '%transferred from batch: '.$batch.'%')
                ->groupBy("batchTicketNumber")
                ->get();
           
        return $data;
    }


    function _inspected_provinces() {
        $provinces = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('province', 'prv', 'prv_dropoff_id')
                ->where('batchTicketNumber', '!=', 'TRANSFER')
                ->orderBy('province', 'asc')
                ->groupBy("province")
                ->get();

        return $provinces;
    }

    function _inspected_provinces_filtered() {
        $station_prv = DB::table('lib_station')
            ->where('stationID', Auth::user()->stationId)
            ->get();

            $stprv = [];
            foreach($station_prv as $s){
                $stprv[] = $s->province;
            }
        $provinces = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('province', 'prv', 'prv_dropoff_id')
                ->where('batchTicketNumber', '!=', 'TRANSFER')
                ->whereIn('province', $stprv)
                ->orderBy('province', 'asc')
                ->groupBy("province")
                ->get();

        return $provinces;
    }

    function _inspected_municipalities($province) {
        $municipalities = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('municipality', 'prv', 'prv_dropoff_id')
                ->where('province', $province)
                ->orderBy('municipality', 'asc')
                ->groupBy("municipality")
                ->get();

        return $municipalities;
    }

    function _batch_varieties($batch) {
        $data = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('seedVariety')
                ->where('batchTicketNumber', "LIKE", $batch)
                ->groupBy("seedVariety")
                ->get();
        $return = '';
        foreach ($data as $item):
            $return .= $item->seedVariety . ',<br> ';
        endforeach;

        $return = rtrim($return, ",<br> ");

        return $return;
    }

    function _batch_varieties_inspectionData($batch) {
        $data = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('seedVariety')
                ->where('batchTicketNumber', "LIKE", $batch)
                ->groupBy("seedVariety")
                ->get();
        $return = '';
        foreach ($data as $item):
            $return .= $item->seedVariety . ', ';
        endforeach;

        $return = rtrim($return, ", ");

        return $return;
    }

    function _coop_details($number) {
        $data = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives" . '.tbl_cooperatives')
                ->select('*')
                ->where('accreditation_no', "LIKE", $number)
                ->first();

        return $data;
    }

    function _inspection_details($batch, $flag) {
        if ($flag == 1) {
            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.tbl_actual_delivery')
                    ->select(DB::RAW('SUM(totalBagCount) as inspected'), 'dateCreated', 'qrValStart')
                    ->where('batchTicketNumber', $batch)
                    ->first();
        } else {

            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.tbl_actual_delivery')
                    ->select(DB::RAW('SUM(totalBagCount) as inspected'), 'dateCreated')
                    ->where('batchTicketNumber', $batch)
                    ->count();
        }
        return $data;
    }

    function _get_status($batch) {
        $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.tbl_delivery_status')
                ->select('status')
                ->where('batchTicketNumber', $batch)
                ->orderBy('deliveryStatusId', "desc")
                ->first();


        if(count($data)>0){
            return $data->status;
        }else{
            return "undefined";
        }


        
    }

    function _get_reject_status($batch){
        $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection".".tbl_actual_delivery")
            //->select("is_rejected")
            ->where("batchTicketNumber", $batch)
            ->count("actualDeliveryId");

        $reject = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection".".tbl_actual_delivery")
            //->select("is_rejected")
            ->where("batchTicketNumber", $batch)
            ->where("isRejected", 1)
            ->count("actualDeliveryId");

            if($reject > 0){
                if($data > $reject){
                    return 1;
                }else{
                    return 2;
                }
            }else{
                return 3;
            }
    }


    function _inspector_details($batch, $flag) {
        if ($flag == 1) {
            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.tbl_schedule as S')
                    ->select('U.firstName', 'U.lastName', 'U.userId', 'S.userId')
                    ->where('S.batchTicketNumber', $batch)
                    ->join($GLOBALS['season_prefix'].'sdms_db_dev.users as U', 'S.userId', '=', 'U.userId')
                    ->first();
        } else {
            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.tbl_schedule as S')
                    ->select('U.firstName', 'U.lastName', 'U.userId', 'S.userId')
                    ->where('S.batchTicketNumber', $batch)
                    ->join($GLOBALS['season_prefix'].'sdms_db_dev.users as U', 'S.userId', '=', 'U.userId')
                    ->count();
        }
        return $data;
    }

    function _get_batches($dropoff, $prv_name) {
        $data = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select(DB::RAW('SUM(totalBagCount) as confirmed'), 'coopAccreditation', 'totalBagCount', 'batchTicketNumber', 'deliveryDate', 'isBuffer')
                ->where('prv_dropoff_id', $dropoff)
                ->where('dropOffPoint', $prv_name)
                
                ->orderBy('dropOffPoint', 'asc')
                ->groupBy("batchTicketNumber")
                ->get();
             
        return $data;
    }

    function _get_inspection_data() {
        /*$data = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select(DB::RAW('SUM(totalBagCount) as confirmed'), 'coopAccreditation', 'totalBagCount', 'tbl_delivery.batchTicketNumber', 'deliveryDate', 'region', 'province', 'municipality', 'dropOffPoint')
                //->join('tbl_delivery_status', 'tbl_delivery.batchTicketNumber', '=', 'tbl_delivery_status.batchTicketNumber')
                //->where('tbl_delivery_status.status', '=', 1)
                ->groupBy("tbl_delivery.batchTicketNumber")
                ->orderBy('region', 'ASC')
                ->orderBy('province', 'ASC')
                ->orderBy('municipality', 'ASC')
                ->get();

        return $data;*/
        
        $data = DB::connection('delivery_inspection_db')
            ->table('tbl_actual_delivery')
            ->select(DB::RAW('SUM(totalBagCount) as confirmed'), 'batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint')
            ->where('batchTicketNumber',"!=","TRANSFER")
            ->groupBy("batchTicketNumber")
            ->orderBy('region', 'ASC')
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();

        return $data;

    }

    /** FOR INSPECTION DATA (EXCEL) **/
    public function _get_transferred_inspection_data(){
        $data = DB::connection('delivery_inspection_db')
            ->table('tbl_actual_delivery')
            ->select(DB::RAW('SUM(totalBagCount) as total_bags'), 'batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 'prv_dropoff_id')
            ->where('batchTicketNumber',"TRANSFER")
            ->groupBy("dropOffPoint")
            ->orderBy('region', 'ASC')
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();

        return $data;

    }
    function _batch_varieties_inspectionDataTransferred($prv_id) {
        $data = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select('seedVariety')
                ->where('prv_dropoff_id', $prv_id)
                ->groupBy("seedVariety")
                ->get();
        $return = '';
        foreach ($data as $item):
            $return .= $item->seedVariety . ', ';
        endforeach;

        $return = rtrim($return, ", ");

        return $return;
    }
    /** FOR INSPECTION DATA (EXCEL) **/

    function _inspected_dropoff($province, $municipality) {
        $dropoff_points = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('dropOffPoint', 'prv_dropoff_id', 'prv')
                ->where('province', $province)
                ->where('municipality', $municipality)
                // ->where("is_cancelled", 0)
                ->orderBy('dropOffPoint', 'asc')
                ->groupBy("prv_dropoff_id")
                ->get();

        return $dropoff_points;
    }

}

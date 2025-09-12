<?php

namespace App;

use DB;
use Auth;
use Request;

class DeliveryInspect
{
    // function delivery_accreditation($accno, $seed_variety) {
    //     $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
    //     ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection as inspect', 'inspect.ticketNumber', '=', 'delivery.ticketNumber')
    //     ->select('*')
    //     ->where('delivery.seedVariety', "=", $seed_variety)
    //     ->where('delivery.sgAccreditation', "=", $accno)
    //     ->orWhere('delivery.coopAccreditation', "=", $accno)
    //     ->get();
    //
    //     return $delivery;
    // }
    function get_coop_accno($coopId){
        $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as coop')
        ->select('*')
        ->where('coop.coopId', $coopId)
        ->first();

        return $coop;
    }
    function coop_sg_producers($coopId) {
        // $delivery = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        // ->leftJoin('seed_growers.seed_growers_all as profile', 'profile.Code_Number', '=', 'producers.Accreditation_Number')
        // ->select('*')
        // ->where('producers.coopId', $coopId)
        // ->get();

        // return $delivery;
        return "";
    }
    function coop_batch_delivery($coopId){
        $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as coop')
        ->select('accreditation_no')
        ->where('coop.coopId', $coopId)
        ->first();
        if (count($coop) > 0) {
            $delivery = $this->delivery_accreditation($coop->accreditation_no);
        }
        return $delivery;
    }
    function delivery_accreditation($accno) {
        $accreNo = explode("-", $accno);
        $no = "null";
        if(isset($accreNo[5])){
            $no = $accreNo[5];
        }
        elseif(isset($accreNo[4])){
            $no = $accreNo[4];
        }elseif(isset($accreNo[3])){
            $no = $accreNo[3];
        }elseif(isset($accreNo[2])){
            $no = $accreNo[2];
        }elseif(isset($accreNo[1])){
            $no = $accreNo[1];
        }
        // $no = (isset($accreNo[3])) ? $accreNo[3] :  "null";
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.coopAccreditation', "like", "%".$no)
        ->get();

        // foreach ($delivery as $value) {
        //     $accreNo = explode("-", $value->coopAccreditation);
        //     $coopId = $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        //     ->select('coopId')
        //     ->where(producers.accreditation_no, "like", "%$accreNo['3']")
        //     ->distinct()
        //     ->get();
        // }

        return $delivery;
    }
    function group_batch($accno){
        $accreNo = explode("-", $accno);
        $no = "null";
        if(isset($accreNo[5])){
            $no = $accreNo[5];
        }
        elseif(isset($accreNo[4])){
            $no = $accreNo[4];
        }elseif(isset($accreNo[3])){
            $no = $accreNo[3];
        }elseif(isset($accreNo[2])){
            $no = $accreNo[2];
        }elseif(isset($accreNo[1])){
            $no = $accreNo[1];
        }
        // $no = (isset($accreNo[3])) ? $accreNo[3] :  "null";
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.coopAccreditation', "like", "%".$no)
        ->groupBy("delivery.batchTicketNumber")
        ->get();

        return $delivery;
    }
    function group_variety($batch){
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.batchTicketNumber', "=", $batch)
        ->groupBy("delivery.seedVariety")
        ->get();

        return $delivery;
    }
    function get_variety($batch, $variety){
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.batchTicketNumber', "=", $batch)
        ->where('delivery.seedVariety', "=", $variety)
        ->get();

        return $delivery;
    }
    function get_DOP($id){
        $dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as dop')
        ->select('*')
        ->where('dop.dropoffPointId', "=", $id)
        ->first();

        return $dop;
    }
    function get_inspection($batch){
        $insp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection as insp')
        ->select("*")
        ->where('insp.batchTicketNumber', "=", $batch)
        ->get();

        return $insp;
    }
    function get_delivery_status($batch){
        $ds = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_status as tds')
        ->select("*")
        ->where('tds.batchTicketNumber', "=", $batch)
        ->orderByRaw('deliveryStatusId DESC')
        ->first();

        return $ds;
    }
    function prov_db_code($code){
        $prv = DB::table($GLOBALS['season_prefix'].'rcep_db_logs.imported_tbl_logs')
        ->select("db_name")
        ->where('province', $code)
        ->orderBy("id", "desc")
        ->first();

        return $prv;
    }
    function get_distribution($batch, $code){
        $prv_code = $this->prov_db_code($code);
        $dis = array();
        if(count($prv_code) > 0){
            $dis = DB::table($prv_code->db_name.'.new_released as rel')
            ->select("*")
            ->where('rel.batch_ticket_no', "=", $batch)
            ->get();
        }

        return $dis;
    }
    function get_actual_delivery($batch,$variety){
        $ad = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
        ->select("*")
        ->where('ad.batchTicketNumber', "=", $batch)
        ->where('ad.seedVariety', "=", $variety )
        ->get();

        return $ad;
    }
	function check_actual_delivery($batch){
        $ad = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
        ->select("*")
        ->where('ad.batchTicketNumber', "=", $batch)
        ->get();

        return $ad;
    }
	
	function gad_batch($batch){
        $ad = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
        ->select("*")
        ->where('ad.batchTicketNumber', "=", $batch)
        ->get();

        return $ad;
    }
	
    function gad_total($batch){
        $ad = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
        ->where('ad.batchTicketNumber', "=", $batch)
        ->sum('totalBagCount');

        return $ad;
    }

    public function gad_total_batch($batchTicketNumbers){
        $totals = array();
        if(empty($batchTicketNumbers)) return $totals;

        $rows = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
            ->select('ad.batchTicketNumber', DB::raw('SUM(ad.totalBagCount) as total'))
            ->whereIn('ad.batchTicketNumber', $batchTicketNumbers)
            ->groupBy('ad.batchTicketNumber')
            ->get();

        foreach($rows as $r){
            $totals[$r->batchTicketNumber] = $r->total;
        }

        return $totals;
    }
/*    uncomment if inspection table is populated
		function inspection_date($batch){
        $insp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection as insp')
        ->select("*")
        ->where('insp.batchTicketNumber', "=", $batch)
        ->first();

        return $insp->dateInspected;
    }
 */	
	function inspection_date($batch){
        $insp = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as insp')
        ->select("*")
        ->where('insp.batchTicketNumber', "=", $batch)
        ->first();

        return $insp->dateCreated;
    }

    function get_delivery($batch){
/*      OLD CODE  
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.batchTicketNumber', $batch)
        ->first(); */

        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('delivery.*', 'transaction.seed_distribution_mode')
        ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction as transaction', 'delivery.batchTicketNumber', '=', 'transaction.batchTicketNumber')
        ->where('delivery.batchTicketNumber', $batch)
        ->first();

        return $delivery;
    }
    function get_delivery_batch($batch){
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.batchTicketNumber', $batch)
        ->get();

        return $delivery;
    }
    function get_prov_code($provId){
         $code = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces as sdd')
        ->select("provCode")
        ->where('sdd.id', "=", $provId)
        ->first();

        return substr($code->provCode, 2, 3);
    }
    function get_coop($accno){
        $accreNo = explode("-", $accno);
        $no = "null";
		if(isset($accreNo[5])){
            $no = $accreNo[5];
        }
        elseif(isset($accreNo[4])){
            $no = $accreNo[4];
        }elseif(isset($accreNo[3])){
            $no = $accreNo[3];
        }elseif(isset($accreNo[2])){
            $no = $accreNo[2];
        }elseif(isset($accreNo[1])){
            $no = $accreNo[1];
        }

        $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as coop')
        ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces as province', 'province.id', '=', 'coop.provinceId')
        ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities as mun', 'mun.id', '=', 'coop.municipalityId')
        ->select('*')
        ->where('coop.accreditation_no', "like", "%".$no)
        ->first();

        // $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as coop')
        // ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv as location', function($join) {
        //     $join->on('location.provCode', '=', 'coop.provinceId')
        //          ->on('location.munCode', '=', 'coop.municipalityId');
        // })
        // ->select('*')
        // ->where('coop.accreditation_no', "like", "%".$no)
        // ->first();

        return $coop;
    }

    function check_variety($batch){
        $actualVariety = array();
        $ad = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
        ->select('*')
        ->where('ad.batchTicketNumber', $batch)
        ->get();
        foreach ($ad as $value) {
            $dvar = $this->get_variety($batch, $value->seedVariety);
            if (count($dvar) == 0) {
                if ($value->seedVariety == "NSIC Rc 222" || $value->seedVariety == "NSIC Rc 160" || $value->seedVariety == "NSIC Rc 216") {
                    array_push($actualVariety, $value);
                }
            }
        }
        
        return $actualVariety;
    }

    function check_iar_logs2(){
        $month = date('m');
        $year = date('Y');
        $logs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs as logs')
        ->select('*')
        ->whereYear('dateCreated',"=", $year)
        ->whereMonth('dateCreated', "=", $month)
        ->orderBy('dateCreated', 'desc')
        ->get();

        return $logs;
    }
    function check_iar_logs(){
        $month = date('m');
        $year = date('Y');
        $logs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs as logs')
        ->select('*')
        ->whereYear('dateCreated',"=", $year)
        ->whereMonth('dateCreated', "=", $month)
        ->orderBy('logsId', 'desc')
        ->first();

        return $logs;
    }
    // function check_iar_logs_batch($batch){
    //     $logs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs as logs')
    //     ->select('*')
    //     ->where('batchTicketNumber', $batch)
    //     ->get();

    //     return $logs;
    // }

    // function insert_logs($batch){
    //     /*$date = date('Y-m-d');
    //     $logs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
    //     ->insert([
    //         'iarCode' => $code,
    //         'batchTicketNumber' => $batch,
    //         'dateCreated' => $date
    //     ]);*/
		
	// 	$date = date('Y-m-d');
    //     $log_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
    //     ->insertGetId([
    //         //'iarCode' => $code,
    //         'iarCode' => '',
    //         'batchTicketNumber' => $batch,
    //         'dateCreated' => $date
    //     ]);

    //     //update last inserted record
    //     $code = date('Y'). "-" .date('m'). "-" .str_pad($log_id, 4, '0', STR_PAD_LEFT);
    //     DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
    //     ->where('logsId', $log_id)
    //     ->update([
    //         'iarCode' => $code,
    //     ]);
        
    //     return $code;
    // }
    
    // IAR UPDDATE
    function check_iar_logs_batch($batch){
        $logs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs as logs')
        ->select('*')
        ->where('batchTicketNumber', $batch)
        ->where('is_printed', '=', 1)
        ->get();

        return $logs;
    }

    function insert_logs($batch){
        $logs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
        ->select('iarCode')
        ->where('batchTicketNumber', $batch)
        ->first();

        if(count($logs) > 0){
            $code_sql = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('batchTicketNumber', $batch)
            ->update([
                'is_printed' => 1,
            ]);
            $code = $logs->iarCode;
        }else{
            $date = date('Y-m-d');
            $log_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->insertGetId([
                //'iarCode' => $code,
                'iarCode' => '',
                'batchTicketNumber' => $batch,
                'dateCreated' => $date,
                'is_printed' => 1
            ]);

            //update last inserted record
            $code = date('Y'). "-" .date('m'). "-" .str_pad($log_id, 4, '0', STR_PAD_LEFT);
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('logsId', $log_id)
            ->update([
                'iarCode' => $code,
            ]);
        }

        $info = $log_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs_info')
        ->insertGetId([
            'iar_code_fk' => $code,
            'userd_id_fk' =>  Auth::id(),
            'ip_address' => Request::ip(),
        ]);
 
        return $code;
    }

    // IAR UPDDATE END

    public function get_delivery_dop($prv, $mun, $dop){
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
        ->select('*')
        ->where('delivery.prv_dropoff_id', $dop)
        ->where("isBuffer", "!=", 9)
        ->groupBy('delivery.batchTicketNumber')
        ->get();

        return $delivery;        
    }
	
	function get_iar_logs($batch){
		$delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs as logs')
        ->select('*')
        ->where('logs.batchTicketNumber', $batch)
        ->first();

        return $delivery;
	}
	
	function get_inspector($batch){
		$delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_schedule as sched')
        ->select('*')
        ->where('sched.batchTicketNumber', $batch)
        ->first();
		
		$inspector = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users as ins')
		 ->select('*')
        ->where('ins.userId', $delivery->userId)
        ->first();
		
        return $inspector;
	}

    function _delivery_provinces() {
        $provinces = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('province', 'prv', 'prv_dropoff_id')
                ->orderBy('province', 'asc')
                ->groupBy("province")
                ->get();

        return $provinces;
    }

    function _delivery_provinces_filtered() {
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
                ->whereIn('province', $stprv)
                ->orderBy('province', 'asc')
                ->groupBy("province")
                ->get();

        return $provinces;
    }

    function _delivery_municipalities($province) {
        $municipalities = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('municipality', 'prv', 'prv_dropoff_id')
                ->where('province', $province)
                ->orderBy('municipality', 'asc')
                ->groupBy("municipality")
                ->get();

        return $municipalities;
    }

    public function prepare_iar_data($id)
    {
        $datenow = date('Y-m-d');// m/d/Y format

        // Consolidated delivery and coop query
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as d')
            ->select(
                'd.*',
                'dt.seed_distribution_mode',
                'c.coopName',
                'c.current_moa',
                'c.provinceId',
                'c.municipalityId',
                'c.full_address',
                'p.provDesc',
                'm.citymunDesc'
            )
            ->leftJoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction as dt', 'd.batchTicketNumber', '=', 'dt.batchTicketNumber')
            ->leftJoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as c', 'c.accreditation_no', '=','d.coopAccreditation')
            ->leftJoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces as p', 'p.id', '=', 'c.provinceId')
            ->leftJoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities as m', 'm.id', '=', 'c.municipalityId')

            ->where('d.batchTicketNumber', $id)
            ->first();
        
        
        // Fallback if no result
        if (!$delivery) {
            return null;
        }
        $coopAdd = (empty($delivery->provinceId) || empty($delivery->municipalityId))
            ? $delivery->full_address
            : (
                empty($delivery->citymunDesc) && empty($delivery->provDesc)
                    ? ''
                    : (empty($delivery->citymunDesc) ? $delivery->provDesc
                    : (empty($delivery->provDesc) ? $delivery->citymunDesc
                        : $delivery->citymunDesc . ', ' . $delivery->provDesc))
            );

        // dd($coopAdd);
                
        // Get delivery batch list (multiple rows, same batchTicketNumber)
        $deliveryList = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as delivery')
            ->select('*')
            ->where('delivery.batchTicketNumber', $id)
            ->get();
        
        // IAR Code generation and logging
        $existingLog = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('batchTicketNumber', $id)
            ->first();

        if ($existingLog) {
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->where('batchTicketNumber', $id)
                ->update(['is_printed' => 1]);

            $iarCode = $existingLog->iarCode;
        } else {
            $log_id = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->insertGetId([
                    'iarCode' => '',
                    'batchTicketNumber' => $id,
                    'dateCreated' => $datenow,
                    'is_printed' => 1
                ]);

            $iarCode = date('Y') . '-' . date('m') . '-' . str_pad($log_id, 4, '0', STR_PAD_LEFT);

            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->where('logsId', $log_id)
                ->update(['iarCode' => $iarCode]);
        }

        // Log print metadata
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs_info')
            ->insert([
                'iar_code_fk' => $iarCode,
                'userd_id_fk' => Auth::id(),
                'ip_address' => Request::ip(),
            ]);
        
        $deliveryDateFormatted = date('D, M d, Y', strtotime($delivery->deliveryDate));
        
        // dd($delivery);
        $provCode = isset($delivery->prv) ? substr($delivery->prv, 0, 4) : null;

        $rawSignatories = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_signatories_person_provinces as a')
            ->select(
                'b.id','b.honorific_prefix','b.complete_name','b.post_nominal','b.sex','b.civil_status',
                'c.name as position_name','b.cell_number','b.email','b.is_right'
            )
            ->leftJoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_signatories_person as b', 'a.person_id', '=', 'b.id')
            ->leftJoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_signatories_position as c', 'b.position_id', '=', 'c.id')
            ->where('a.provCode', $provCode)
            ->where('b.is_active', 1)
            ->get();

        // Initialize slots
        $leftData  = null;
        $rightData = null;

        // Manually separate into left/right
        foreach ($rawSignatories as $sig) {
            if ($sig->is_right == 0 && $leftData === null) {
                $leftData = $sig;
            }
            if ($sig->is_right == 1 && $rightData === null) {
                $rightData = $sig;
            }
        }

        // safeName helper
        $safeName = function($prefix, $name, $postNominal) {
            $parts = [];
            if (!empty($prefix)) $parts[] = $prefix;
            if (!empty($name)) $parts[] = $name;
            if (!empty($postNominal)) $parts[] = $postNominal;
            return implode(' ', $parts);
        };

        // Defaults (placeholders to keep alignment intact)
        $sigNameLeft  = 'Name and Signature';
        $sigNameRight = 'Name and Signature';
        $sigPosLeft   = '';
        $sigPosRight  = '';

        // Fill left if available
        if (!empty($leftData)) {
            $sigNameLeft = $safeName($leftData->honorific_prefix, $leftData->complete_name, $leftData->post_nominal);
            if (empty($sigNameLeft)) $sigNameLeft = 'Name and Signature';
            $sigPosLeft = !empty($leftData->position_name) ? $leftData->position_name : '';
        }

        // Fill right if available
        if (!empty($rightData)) {
            $sigNameRight = $safeName($rightData->honorific_prefix, $rightData->complete_name, $rightData->post_nominal);
            if (empty($sigNameRight)) $sigNameRight = 'Name and Signature';
            $sigPosRight = !empty($rightData->position_name) ? $rightData->position_name : '';
        }

        // return as before
        return [
            'coop-name'      => $delivery->coopName,
            'coop-address'   => $coopAdd,
            'region'         => $delivery->region,
            'province'       => $delivery->province,
            'municipality'   => $delivery->municipality,
            'drop_off_point' => $delivery->dropOffPoint,
            'IAR_no'         => $iarCode,
            'Date'           => $datenow,
            'MOA'            => $delivery->current_moa,
            'seed-type'      => $delivery->seed_distribution_mode,
            'coopName'       => $delivery->coopName,
            'dop'            => $delivery->dropOffPoint,
            'delivery'       => $deliveryList,
            'date'           => $deliveryDateFormatted,
            'ticket'         => $id,

            'sig-name-left'  => $sigNameLeft,
            'sig-name-right' => $sigNameRight,
            'sig-pos-left'   => $sigPosLeft,
            'sig-pos-right'  => $sigPosRight,
        ];

    }

}

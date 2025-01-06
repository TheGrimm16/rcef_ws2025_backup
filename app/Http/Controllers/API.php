<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;

use App\Farmer;
use App\DropoffPoints;

class API extends Controller
{
    public function __construct(UrlGenerator $url){
        $this->url = $url;
    }
	
	// public function ebinhi_coop_monitoring($coop_no, $date_from, $date_to){



        
	// 	$coop_no = str_replace("_","/",$coop_no);
	// 	$date_start = $date_from." 01:00:00";
	// 	$date_end = $date_to." 23:59:59";

	// 	//dd($date_from);
		
	// 	if($date_from == 0 && $date_to == 0){
	// 		$coop_monitoring_sql = DB::select("SELECT `coopAccreditation`, count(beneficiary_id), sum(total_bags) FROM (SELECT coopAccreditation, paymaya_code, beneficiary_id, fullName, count(paymaya_code) as total_bags FROM `rcep_paymaya`.`tbl_claim` where coopAccreditation=? group by beneficiary_id) as ebinhi_result", [$coop_no]);
	// 	}else{
	// 		//dd($date_start);
	// 		DB::enableQueryLog();
	// 		$coop_monitoring_sql = DB::select("SELECT `coopAccreditation`, count(beneficiary_id), sum(total_bags) FROM (SELECT coopAccreditation, paymaya_code, beneficiary_id, fullName, count(paymaya_code) as total_bags FROM `rcep_paymaya`.`tbl_claim` where coopAccreditation=? and date_created >= ? and date_created <= ? group by beneficiary_id) as ebinhi_result", [$coop_no, $date_start, $date_end]);
	// 	}
		
	// 	return json_encode($coop_monitoring_sql);
	// }

    public function ebinhi_coop_inventory(Request $request){

        
        $coop_no = $request->coop_number;
        $current_date = $request->current_date;

       // $coop_no = str_replace("_","/",$coop_no);

        /*$ebinhi_dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select('tbl_actual_delivery.dropOffPoint as location',DB::raw("SUM(tbl_actual_delivery.totalBagCount) as dop_delivered"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery', 'tbl_delivery.batchTicketNumber', "=", 'tbl_actual_delivery.batchTicketNumber')
            ->where('tbl_delivery.coopAccreditation', $coop_no)
            ->where('tbl_actual_delivery.qrStart', '!=', '')
            ->where('tbl_actual_delivery.qrEnd', '!=', '')
            ->groupBy('tbl_actual_delivery.dropOffPoint')
            ->get();

        $return_data = array();
        $distribution_data = array();
        $dop_list = array();
        $total_distributed = 0;
        foreach($ebinhi_dop as $dop_data){

            array_push($distribution_data, array(
                'location' => $dop_data->location,
                'bags_distributed' => $total_delivered
            ));

            array_push($dop_list, array($dop_data->location));
        }
     
        return json_encode(
            array(
                "dop_list" => $dop_list,
                "distribution_data" => $distribution_data
            )
        );*/

        $ebinhi_dop_list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('province','municipality','claimLocation', DB::raw('COUNT(beneficiary_id) as total_distributed'))
            ->where('coopAccreditation', $coop_no)
            ->groupBy('claimLocation')->get();

        $ebinhi_covered_provinces = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('province')
            ->where('coopAccreditation', $coop_no)
            ->groupBy('province')->get();
        
        $dop_list = array();
        $distribution_data = array();
        $total_distributed = 0;
        $total_bags_overall = 0;

        /*foreach($ebinhi_covered_provinces as $prov_row){
            $overall_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->select(DB::raw("SUM(tbl_actual_delivery.totalBagCount) as total_delivered"))
                ->Leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery', 'tbl_delivery.batchTicketNumber', "=", 'tbl_actual_delivery.batchTicketNumber')
                ->where('tbl_delivery.coopAccreditation', $coop_no)
                ->where('tbl_actual_delivery.qrStart', '>', '0')
                ->where('tbl_actual_delivery.qrEnd', '>', '0')
                ->where('tbl_actual_delivery.province', $prov_row->province)
                ->first();

            $total_bags_overall += $overall_data->total_delivered;
        }*/


        // $overall_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        //     ->select(DB::raw("SUM(tbl_actual_delivery.totalBagCount) as total_delivered"))
        //     ->where('tbl_actual_delivery.qrStart', '>', '0')
        //     ->where('tbl_actual_delivery.qrEnd', '>', '0')
        //     ->where('tbl_actual_delivery.province', 'PAMPANGA')
        //     ->first();
        //     $total_bags_overall += $overall_data->total_delivered;

        
        $batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->select("batchTicketNumber")
            ->where("coopAccreditation", $coop_no)
            ->groupBy('batchTicketNumber')
            ->get();
    
        $batches = json_decode(json_encode($batches), true);

        $total_bags_overall = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->whereIn("batchTicketNumber", $batches)
            ->where('tbl_actual_delivery.qrStart', '>', '0')
            ->where('tbl_actual_delivery.qrEnd', '>', '0')
            
            ->sum("totalBagCount");

            // transferred from batch: 596-BCH-1699854392

        foreach($batches as $trans_batch){
          $total_bags_overall += DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->where("remarks", "transferred from batch: ".$trans_batch["batchTicketNumber"])
                ->where('tbl_actual_delivery.qrStart', '>', '0')
                ->where('tbl_actual_delivery.qrEnd', '>', '0')
                ->sum("totalBagCount");
        }


        foreach($ebinhi_dop_list as $dop){
            array_push($dop_list, array("location" => $dop->claimLocation));
            
            $per_day = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select(DB::raw("STR_TO_DATE(date_created, '%Y-%m-%d') as date"), DB::raw('COUNT(beneficiary_id) as total_distributed'))
            ->where('coopAccreditation', $coop_no)
            ->where("claimLocation", $dop->claimLocation)
            ->groupBy(DB::raw("STR_TO_DATE(date_created, '%Y-%m-%d')"))->get();
           


            array_push($distribution_data, array(
                "location" => $dop->claimLocation,
                "bags_distributed" => $dop->total_distributed,
                "data_per_day" => $per_day
            ));

            $total_distributed += $dop->total_distributed;
        }

        $remaining_bags = $total_bags_overall - $total_distributed;
        return json_encode(
            array(
                "total_bags_overall" => $total_bags_overall,
                "remaining_bags_overall" => $remaining_bags,
                "distributed_bags_overall" => $total_distributed,
                "as_of" => $current_date,
                "dop_list" => $dop_list,
                "distribution_data" => $distribution_data,
            )
        );
    }

    public function ebinhi_coop_inventory_debug(Request $request){
        return "no tresspassing";
        
        $coop_no = $request->coop_number;
        $current_date = $request->current_date;

       // $coop_no = str_replace("_","/",$coop_no);

        /*$ebinhi_dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select('tbl_actual_delivery.dropOffPoint as location',DB::raw("SUM(tbl_actual_delivery.totalBagCount) as dop_delivered"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery', 'tbl_delivery.batchTicketNumber', "=", 'tbl_actual_delivery.batchTicketNumber')
            ->where('tbl_delivery.coopAccreditation', $coop_no)
            ->where('tbl_actual_delivery.qrStart', '!=', '')
            ->where('tbl_actual_delivery.qrEnd', '!=', '')
            ->groupBy('tbl_actual_delivery.dropOffPoint')
            ->get();

        $return_data = array();
        $distribution_data = array();
        $dop_list = array();
        $total_distributed = 0;
        foreach($ebinhi_dop as $dop_data){

            array_push($distribution_data, array(
                'location' => $dop_data->location,
                'bags_distributed' => $total_delivered
            ));

            array_push($dop_list, array($dop_data->location));
        }
     
        return json_encode(
            array(
                "dop_list" => $dop_list,
                "distribution_data" => $distribution_data
            )
        );*/

        $ebinhi_dop_list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('province','municipality','claimLocation', DB::raw('COUNT(beneficiary_id) as total_distributed'))
            ->where('coopAccreditation', $coop_no)
            ->groupBy('claimLocation')->get();

        $ebinhi_covered_provinces = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('province')
            ->where('coopAccreditation', $coop_no)
            ->groupBy('province')->get();
        
        $dop_list = array();
        $distribution_data = array();
        $total_distributed = 0;
        $total_bags_overall = 0;

        /*foreach($ebinhi_covered_provinces as $prov_row){
            $overall_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->select(DB::raw("SUM(tbl_actual_delivery.totalBagCount) as total_delivered"))
                ->Leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery', 'tbl_delivery.batchTicketNumber', "=", 'tbl_actual_delivery.batchTicketNumber')
                ->where('tbl_delivery.coopAccreditation', $coop_no)
                ->where('tbl_actual_delivery.qrStart', '>', '0')
                ->where('tbl_actual_delivery.qrEnd', '>', '0')
                ->where('tbl_actual_delivery.province', $prov_row->province)
                ->first();

            $total_bags_overall += $overall_data->total_delivered;
        }*/


        // $overall_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        //     ->select(DB::raw("SUM(tbl_actual_delivery.totalBagCount) as total_delivered"))
        //     ->where('tbl_actual_delivery.qrStart', '>', '0')
        //     ->where('tbl_actual_delivery.qrEnd', '>', '0')
        //     ->where('tbl_actual_delivery.province', 'PAMPANGA')
        //     ->first();
        //     $total_bags_overall += $overall_data->total_delivered;

        
        $batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->select("batchTicketNumber")
            ->where("coopAccreditation", $coop_no)
            ->get();
    
        $batches = json_decode(json_encode($batches), true);

        $total_bags_overall = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->whereIn("batchTicketNumber", $batches)
            ->where('tbl_actual_delivery.qrStart', '>', '0')
            ->where('tbl_actual_delivery.qrEnd', '>', '0')
            
            ->sum("totalBagCount");

            // transferred from batch: 596-BCH-1699854392
        //dd($batches);
        foreach($batches as $trans_batch){
          $total_bags_overall += DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->where("remarks", "transferred from batch: ".$trans_batch["batchTicketNumber"])
                ->where('tbl_actual_delivery.qrStart', '>', '0')
                ->where('tbl_actual_delivery.qrEnd', '>', '0')
                ->sum("totalBagCount");
        }


        foreach($ebinhi_dop_list as $dop){
            array_push($dop_list, array("location" => $dop->claimLocation));
            
            $per_day = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select(DB::raw("STR_TO_DATE(date_created, '%Y-%m-%d') as date"), DB::raw('COUNT(beneficiary_id) as total_distributed'))
            ->where('coopAccreditation', $coop_no)
            ->where("claimLocation", $dop->claimLocation)
            ->groupBy(DB::raw("STR_TO_DATE(date_created, '%Y-%m-%d')"))->get();
           


            array_push($distribution_data, array(
                "location" => $dop->claimLocation,
                "bags_distributed" => $dop->total_distributed,
                "data_per_day" => $per_day
            ));

            $total_distributed += $dop->total_distributed;
        }

        $remaining_bags = $total_bags_overall - $total_distributed;
        return json_encode(
            array(
                "total_bags_overall" => $total_bags_overall,
                "remaining_bags_overall" => $remaining_bags,
                "distributed_bags_overall" => $total_distributed,
                "as_of" => $current_date,
                "dop_list" => $dop_list,
                "distribution_data" => $distribution_data,
            )
        );
    }
	
	public function get_dop_coops(){
		$data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->select('coopId', 'coopName', 'acronym', 'accreditation_no')->where('isActive', 1)->get();
		return json_encode($data);
	}
	
	public function get_lib_prv(){
		$data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->select('prv', 'regCode', 'regionName', 'province', 'municipality')->get();
		return json_encode($data);
	}

    public function commitmentByRegion(Request $request){
        $commitment = DB::connection('mysql')
            ->table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->select('coopName', 'acronym', 'region_name', 'tbl_cooperatives.accreditation_no', 'current_moa')
            ->addSelect(DB::raw("SUM(volume) as commited_volume"))
            ->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional', 'tbl_commitment_regional.accreditation_no', "=", 'tbl_cooperatives.accreditation_no')
            ->where([
                'tbl_cooperatives.accreditation_no' => $request->accreditation_no,
                'tbl_commitment_regional.region_name' => $request->region,
                'isActive' => 1
            ])
            ->orderBy('region_name', 'asc')
            ->groupBy('region_name')
            ->get();
        
        return $commitment;
    }

    public function insert_dropoff(Request $request) {
        $prvCode = $request->prv_code;
        $coopAccreditation = $request->accreditation_no;
        $dropoffpoint = $request->dropoffpoint;
        $createdBy = $request->created_by;
        //convert slash to rcef3310, rcef3310 to slash
        $code = 'rcef3310';

        $coopAccreditation = str_ireplace($code, "/", $coopAccreditation);
        $dropoffpoint = str_ireplace($code, "/", $dropoffpoint);

        $farmer = new Farmer();
        $check_dropoff_coop = $farmer->_check_dropoff_coop($prvCode, $dropoffpoint, $coopAccreditation);
		$count_other = 0;
        if ($check_dropoff_coop == 0) {
            $check_dropoff = $farmer->_check_dropoff($prvCode, $dropoffpoint, 1);
            if ($check_dropoff == 0) {
                $count_other = $farmer->_check_other_dropoff($prvCode, $dropoffpoint);
				//echo $count_other;
                $suffix = $count_other + 1;
                $dropoff_id = $prvCode . '-' . $suffix;
            } else {
                $drop_offdata = $farmer->_check_dropoff($prvCode, $dropoffpoint, 2);
                $dropoff_id = $drop_offdata->prv_dropoff_id;
            }
            $get_geonames = $farmer->_get_geonames($prvCode);

            $data = array(
                'prv_dropoff_id' => $dropoff_id,
                'coop_accreditation' => $coopAccreditation,
                'region' => $get_geonames->regionName,
                'province' => $get_geonames->province,
                'municipality' => $get_geonames->municipality,
                'dropOffPoint' => $dropoffpoint,
                'prv' => $prvCode,
                'is_active' => 1,
                'date_created' => date("Y:m:d H:i:s"),
                'created_by' => $createdBy
            );
            $drop_off = $farmer->_insert_dropoff($data);
            echo json_encode($drop_off);
        } else {
            echo json_encode("existing");
        }
    }

    public function insert_delivery_schedule(Request $request){
        $dop = new DropoffPoints;
        $instructed_delivery_volume = $request->instructed_delivery_volume;
        $region = $request->region;
        $prv_dropoff_id = $request->prv_dropoff_id;
        $moa_number = $request->moa_number;
        $accreditation_no = $request->accreditation_no;
        $delivery_date = $request->delivery_date;
        $date_created = $request->date_created;
        $batchTicketNumber = $request->batchTicketNumber;
        $status = 0; 
        $confirmed_delivery = 0;
        $user_id = $request->user_id; 
        $isBuffer = $request->isBuffer;

        // $dop->getCoopRegPending($accreditation_no, $region);
        // $dop->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
        // $dop->getCoopRegInspected($accreditation_no, $region);
        // $dop->hasRegionCommitment($accreditation_no, $region);
        // return $dop->getCoopRegionalCommitment($accreditation_no, $region )->total;

        $hasRegionCommitment =  $dop->hasRegionCommitment($accreditation_no, $region);
        $coopRegTotalCommit =  $dop->getCoopRegionalCommitment($accreditation_no, $region);
        $coopRegPending =  $dop->getCoopRegPending($accreditation_no, $region);
        $coopRegConfirmed  =  $dop->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
        $coopRegInspected =  $dop->getCoopRegInspected($accreditation_no, $region);
        $coopRegAllocated =  $coopRegPending + $coopRegConfirmed + $coopRegInspected;
        if(isset($coopRegTotalCommit->total)){
            $total = $coopRegTotalCommit->total;
        }else{
            $total = 0;
        }
        $remainingBalance = $total - $coopRegAllocated; 
        
        $coopRegTotalCommit =  $dop->getCoopRegionalCommitment($accreditation_no, $region);
        $coopRegPending =  $dop->getCoopRegPending($accreditation_no, $region);
        $coopRegConfirmed  =  $dop->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
        $coopRegInspected =  $dop->getCoopRegInspected($accreditation_no, $region);

        if($instructed_delivery_volume <= $remainingBalance){

            $data = [
                'instructed_delivery_volume' => $instructed_delivery_volume,
                'prv_dropoff_id' => $prv_dropoff_id,
                'moa_number' => $moa_number,
                'accreditation_no' => $accreditation_no,
                'delivery_date' => $delivery_date,
                'date_created' => $date_created,
                'status' => $status,
                'batchTicketNumber' => $batchTicketNumber,
                'confirmed_delivery' => $confirmed_delivery,
                'user_id' => $user_id,
                'region' => $region,
                'isBuffer' => $isBuffer,
            ];
            if($dop->insert_delivery_sched($data)){
                //success
                //check balance	
                $hasRegionCommitment = $dop->hasRegionCommitment($accreditation_no, $region);
                $coopRegTotalCommit = $dop->getCoopRegionalCommitment($accreditation_no, $region);
                $coopRegPending = $dop->getCoopRegPending($accreditation_no, $region);
                $coopRegConfirmed  = $dop->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
                $coopRegInspected = $dop->getCoopRegInspected($accreditation_no, $region);
                $coopRegAllocated =  $coopRegPending + $coopRegConfirmed + $coopRegInspected;
                if(isset($coopRegTotalCommit->total)){
                    $total = $coopRegTotalCommit->total;
                }else{
                    $total = 0;
                }
                $remainingBalance = $total - $coopRegAllocated; 
                
                //result
                $result["remainingBalance"] = $remainingBalance;
                $result["instructed_delivery_volume"] = $instructed_delivery_volume;
                $result["status"] = 1;
                $result["message"] = "success";
            }
            else{
                //failed
                //success
                $result["remainingBalance"] = $remainingBalance;
                $result["instructed_delivery_volume"] = $instructed_delivery_volume;
                $result["status"] = 2;
                $result["message"] = "Failed to create new batch ticket";
            }					
        }
        else{
            $result["remainingBalance"] = $remainingBalance;
            $result["instructed_delivery_volume"] = $instructed_delivery_volume;
            $result["status"] = 3;
            $result["message"] = "Insufficient balance";
        }
        
        return $result;
    }

    public function mun_farmers_yield(){
        // DB::enableQueryLog(); // Enable query log
        $data = [];
        $sql = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports as a')
            ->select('province', 'municipality', 'yield')
            ->groupBy('province', 'municipality')
            ->get();
        
        foreach ($sql as $d) {
            
            $municipality = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                ->select(DB::raw('province, municipality, prv'))
                ->where(['province' => $d->province, 'municipality' => $d->municipality])
                ->groupBy('province', 'municipality')
                ->first();

            $psa = $municipality->prv . "000";
           
            $data[] = [
                'province' => $d->province,
                'municipality' => $d->municipality,
                'psa_code' => $psa,
                'yield' => $d->yield." (T/ha)",
            ];
        }   
        // dd(DB::getQueryLog()); // Show results of log
        return $data;
    }

    // API for SED caller to fetch data from database
    public function fetch_farmer_sed_caller(Request $request){
        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_assignment')
            ->where('assigned_to', $request->user_code)
            ->where('assigned_to', $request->user_id)
            ->where('isNew', 0)
            ->get();
        
        if(count($data) > 0){
            $status = 1;
            $message = "Success";
        }else{
            $status = 0;
            $message = "No Data";
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    public function fetch_farmer_sed_caller_callback(Request $request){
        foreach($request->data as $d){
                    
            $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_assignment')
            ->where('assign_id', $d['assign_id'])
            ->where('isNew', 0)
            ->update([
                'isNew' => 1
            ]);
       }
    }

    public function fetch_sed_caller_updates(Request $request){
        DB::beginTransaction();
        try {

            $central_update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_id', $request->sed_id)
                ->where('muni_code', $request->muni_code)
                ->update([
                    'secondary_contact_no' => $request->secondary_contact_no,
                    'ver_sex' => $request->ver_sex,
                    'committed_area' => $request->committed_area,
                    'preffered_variety1' => $request->preffered_variety1,
                    'preffered_variety2' => $request->preffered_variety2,
                    'status' => $request->status,
                    'sowing_year' => $request->sowing_year,
                    'sowing_month' => $request->sowing_month,
                    'sowing_week' => $request->sowing_week,
                    'enableEdit' => $request->enableEdit,
                    'agreed_to_dsa' => $request->agreed_to_dsa,
                    'isActive' => $request->isActive,
                    'isParticipatingAfter' => $request->isParticipatingAfter,
                    'hasDecreasedYield' => $request->hasDecreasedYield,
                    'hasDecreasedFarmArea' => $request->hasDecreasedFarmArea,
                    'date_sync' => date("Y-m-d H:i:s")
                ]);
                
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
        return "success";
    }

    public function fetch_paymaya_for_edit(Request $request){
        
        $data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->where('created_by', $request->user_code)
            ->where('enableEdit', 1)
            ->get();

        
        return $data;
    }

}
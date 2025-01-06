<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Filesystem\Filesystem;
use DB;



class UtilDelTransactionController extends Controller
{
    
	public function get_batchNumber(Request $request){
		$batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
		->orderBy('delivery_date', 'desc')
		->where('status','0')
		->get();

		//$pdf_name = "[".$mun_code."] FLSAR_".strtoupper($province_name)."_".strtoupper($municipality_name).".pdf";

		$return_str = ""; 
		$return_str .= "<option value='0'>.....Please Select Batch Number.....</option>";
			//$i=0;
		foreach($batch as $row){
			$return_str .= "<option value='".$row->id."'>$row->batchTicketNumber</option>";
			//$return_str .= "<option value='$row->prv'>$row->province < $row->municipality</option>";
			//$i++;
		}
			//$return_str .= "<option value='".$i."'>$i</option>";
		return $return_str;
	}

//pullDelInfo

	public function pullDelInfo($batchID){
		$batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
		->orderBy('delivery_date', 'desc')
		->where('status','0')
		->where('id', $batchID)
		->first();

		//return $batch;
		echo json_encode($batch);
	}

	//pullDopInfo

	public function pullDopInfo($dopID,$moa){
		$data = array();

		$dop_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
        ->where('prv_dropoff_id', $dopID)
        ->first();
        $data[0] = $dop_details->province.' - '.$dop_details->municipality.' -> '.$dop_details->dropOffPoint;
       
         $coop_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
		->where('current_moa', $moa)
		->first();

		$data[1] = $coop_details->coopName;
        
		//return $batch;
		echo json_encode($data);
	}

	public function cancelDelivery($batchID){
		$batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
		->where('id', $batchID)
		->update(['status'=>3]); 
		//return $batch

		return $batch;
	}


}

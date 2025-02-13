<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Transfer;
use Auth;
use Yajra\Datatables\Datatables;

use DB;
use Session;

class TransferController extends Controller {

	//NEW TRANSFER PREVIOUS TO CURRENT


	public function index() {
            /*
            if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga"){

            }else{

                return view('utility.pageClosed')
                    ->with('mss', "This module is temporary close");
            }

            */


        $season = strtoupper(str_replace('_','',$GLOBALS['last_season_prefix']));
        $transfers = new Transfer();
        $drop_offpoints = $transfers->_drop_offpoints();

        $dropoff = array();

        $province_name = $transfers->_province_name();
        foreach ($drop_offpoints as $item) {
            $data = array(
                'prv_dropoff_id' => $item->prv_dropoff_id,
                'dropOffPoint' => $item->municipality . ' - ' . $item->dropOffPoint
            );
            array_push($dropoff, $data);
        }

        /*
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
        
                return view('transfer.pstocs.index')
                                ->with(compact('dropoff'))
                                ->with(compact('province_name'));}
        else{
            $mss = "Page is temporary closed.";
                return view('utility.pageClosed',compact("mss"));
        } */

                return view('transfer.pstocs.index')
                                ->with(compact('dropoff'))
                                ->with(compact('province_name'))
                                ->with(compact('season'));

    }

	public function getBatchOldSeason(Request $request){
        //SHOULD BE FOR LAST SEASON DATA
        $batch_delivery_list = DB::connection('ls_inspection_db')->table('tbl_delivery as d')
           ->select('a.dateCreated','a.batchTicketNumber', DB::raw('SUM(a.totalBagCount) as total_bags'),'d.coopAccreditation','a.seedTag','a.seedVariety','a.region','a.province','a.municipality','a.dropOffPoint','a.prv_dropoff_id','a.prv','is_hold')
           ->join('tbl_actual_delivery as a', function($join)
                {   $join->on('d.batchTicketNumber','=','a.batchTicketNumber');
                    $join->on('d.seedTag','=','a.seedTag');
                    $join->on('d.prv_dropoff_id','=','a.prv_dropoff_id');    
                }
            ) 
           ->where('d.coopAccreditation', $request->coop_id)
           ->where('a.transferCategory','!=','P')
           //->where('a.is_hold', 0)
           //->where("a.batchTicketNumber", '612-BCH-1629737804')
           ->groupBy('batchTicketNumber')
           ->get();

           //dd($batch_delivery_list);

        $data_arr = array();
        foreach($batch_delivery_list as $row){
            $variety_list = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->select('seedVariety')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                //->where("is_hold", 0)
                ->groupBy('seedVariety')
                ->get();

            $variety_str = "";
            foreach($variety_list as $variety_row){
                $variety_str .= $variety_row->seedVariety."<br> ";
            }

            $variety_str = rtrim($variety_str, "<br> ");

            $seed_tag_list = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->select('seedTag')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                //->where("is_hold", 0)
                ->groupBy('seedTag')
                ->get();

            $seed_tags = "";
            foreach($seed_tag_list as $seedTag_row){
                $seed_tags .= $seedTag_row->seedTag."<br> ";
            }

            $seed_tags = rtrim($seed_tags, "<br> ");


            $checkPartial = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')

                ->where("remarks", "LIKE", "transferred from batch: ".$row->batchTicketNumber)
                ->where("is_hold", 0)
                ->groupBy("batchTicketNumber")
                ->get();

              
                    $partialTicket = "";
                


                foreach ($checkPartial as $checkPartial_index => $checkPartial_value) {
                  if($partialTicket != "") $partialTicket .= ";";
                  $partialTicket .= $checkPartial_value->batchTicketNumber."|".$checkPartial_value->municipality ;
                }


              array_push($data_arr, array(
                        'batchTicketNumber' => $row->batchTicketNumber,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'variety_list' => $variety_str,
                        'seed_tags' => $seed_tags,
                        'total_bags' => number_format($row->total_bags)." bag(s)",
                        'date_inspected' => date("Y-m-d", strtotime($row->dateCreated)),
                        'coop_name' => $request->coop_name,
                        'coop_acre' => $request->coop_id,
                        'partialTicket' => $partialTicket,
                        'is_hold' => $row->is_hold,
                    ));
        


        }
          
 





        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->addColumn('action', function($row) {
            $acre = str_replace('/', '*', $row['coop_acre']);
          // if($row['is_hold']!="0"){
           //     $btn_class = "btn btn-dark btn-round btn-sm";
            //    $btn_dis = "disabled";
           //     $link = "#";
           // }else{
                $btn_class = "btn btn-success btn-round btn-sm";
                $btn_dis = "";
                $link = "https://rcef-seed.philrice.gov.ph/rcef_ws2025/transfers/pstocs/".$row['batchTicketNumber']."/".$acre; 
           // }

            
            $btn = "<a type='button' class='".$btn_class."' href='".$link."' ".$btn_dis."><i class='fa fa-arrow-circle-right'></i> ALL DELIVERIES</a>";
            if($row['partialTicket'] != ""){

                $partial_arr =explode(";", $row['partialTicket']);
                    foreach ($partial_arr as $key_partial => $value_partial) {
                        $btch = explode("|", $value_partial);
                 $btn .= "<br><a type='button' class='btn btn-warning btn-round btn-sm' href='https://rcef-seed.philrice.gov.ph/rcef_ws2025/transfers/pstocs/".$btch[0]."/".$acre."'><i class='fa fa-arrow-circle-right'></i> PARTIAL TRANSFERRED TO ".$btch[1]."</a>";
                    }



            }


            return $btn;

        })
        ->make(true);
    }

    public function dataPreparation($batch_number,$coop_acre){

     //SHOULD BE LAST SEASON
        $batch_details = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->where("is_hold", '0')
            ->get();

            //dd($batch_details);

            //CURRENT SEASON
        //$provinces = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('province')->orderBy('province', 'ASC')->get();
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province', 'ASC')->get();

        $original_location = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->select('region','province','municipality','dropOffPoint','prv_dropoff_id',DB::raw('SUM(totalBagCount) as total_bags'))

            ->groupBy('batchTicketNumber')
            ->first();
           // dd($coop_acre);
		
		   $coop_details = DB::connection('ls_seed_coop')->table('tbl_cooperatives')
                    ->where('accreditation_no', str_replace('*', '/', $coop_acre))
                    ->first();
		   
		   
        return view('transfer.pstocs.oldseason')
            ->with('batch_details', $batch_details)
            ->with('coop_name', $coop_details->coopName)
            ->with('coop_acre', $coop_details->accreditation_no)
            ->with('batch_number', $batch_number)
            ->with('provinces', $provinces)
            ->with('original_location_region', $original_location->region)
            ->with('original_location_province', $original_location->province)
            ->with('original_location_municipality', $original_location->municipality)
            ->with('original_location_dop', $original_location->prv_dropoff_id)
            ->with('original_location_dop_name', $original_location->dropOffPoint)
            ->with('total_bags', number_format($original_location->total_bags)." bag(s)")
            ->with('total_bags_intval', $original_location->total_bags); 
    }

    public function check_seedtag_oldseason(Request $request){

        //SHOULD BE FROM OLD SEASON
        $actual_delivery_batch = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $request->batch_number)
            ->where("is_hold", 0)
            ->where('seedTag', $request->seed_tag)
            ->first();

        if($actual_delivery_batch->totalBagCount >= $request->seed_tag_value){
            $prv_dropoff_id = $actual_delivery_batch->prv_dropoff_id;
            $over_all_count = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("prv_dropoff_id", $prv_dropoff_id)
                    ->where("seedVariety", $actual_delivery_batch->seedVariety)
                    ->sum('totalBagCount');
            $prv_db = substr($prv_dropoff_id,0,4);
            $distributed = DB::connection("ls_inspection_db")->table($GLOBALS['last_season_prefix']."prv_".$prv_db.".new_released")
                    ->where("prv_dropoff_id", $prv_dropoff_id)
                    ->where("seed_variety", $actual_delivery_batch->seedVariety)
                    ->sum("bags_claimed");

            // $total_bags = $request->seed_tag_value + $distributed;
            $stock_count = $over_all_count - $distributed;

            if($stock_count >= $request->seed_tag_value){
                return array(
                    'msg' => "amount_to_transfer_ok",
                    'card_id' => $actual_delivery_batch->actualDeliveryId,
                );
                
            }else{
                return array(
                    'msg' => "amount_to_transfer_exceeds",
                    'max_tag' => number_format($stock_count)." bag(s)",
                );
            }





            
        }else{
            return array(
                'msg' => "amount_to_transfer_exceeds",
                'max_tag' => number_format($actual_delivery_batch->totalBagCount)." bag(s)",
            );
        }
    }

    public function get_seedTag_details_oldseason(Request $request){
        $actual_delivery_batch = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
            ->where('actualDeliveryId', $request->actual_delivery_id)
            ->where("is_hold", 0)
            ->first();

        return array(
            'seed_tag' => $actual_delivery_batch->seedTag,
            'seed_variety' => $actual_delivery_batch->seedVariety,
        );
    }



     public function confirm_transfer_oldseason(Request $request){
          $transfer = new Transfer();

        $transfer_str = rtrim($request->transfer_str,"*|*");
        $transfer_list = explode("*|*", $transfer_str);
        
        DB::beginTransaction();
        try{
			
			$dropoff_point_data_oldseason = DB::connection('ls_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();

            //ADD LIBRARY PRV
                if($transfer->_check_dropoff($request->destination_dropoff)<=0){
                        $data = array(
                        'prv_dropoff_id' => $dropoff_point_data_oldseason->prv_dropoff_id,
                        'coop_accreditation' => $dropoff_point_data_oldseason->coop_accreditation,
                        'region' => $dropoff_point_data_oldseason->region,
                        'province' =>$dropoff_point_data_oldseason->province,
                        'municipality' => $dropoff_point_data_oldseason->municipality,
                        'dropOffPoint' => $dropoff_point_data_oldseason->dropOffPoint,
                        'prv' => $dropoff_point_data_oldseason->prv,
                        'is_active' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
                        'created_by' => Auth::user()->username
                    );
                   $insPRV =  $transfer->_add_details($data);
                }
			
            //get destination region,province,municipality,dropoff etc. based on destination dop_id
            $dropoff_point_data = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();

            $new_batch_number = Auth::user()->userId."-BCH-".time();
            //$new_batch_number = 'TRANSFER';
            $log_str = "";
            $log_count = 1;
            $bag_count = 0;
            $seed_variety_log = "";
            //add data to tbl_actual_delivery for each seed tag

            foreach($transfer_list as $str_row){
                $transfer_details = explode("&", $str_row);
                $seed_tag = $transfer_details[0];
                $seedTag_bags = $transfer_details[1];
				$seedType = $transfer_details[2];

                //get seed tag details based on batch number & seed tag on tbl_actual_delivery
                //OLD SEASON
                $seedtag_details = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $request->batch_number)
                    ->where("is_hold", 0)
                    ->where('seedTag', $seed_tag)
                    ->first();

                 $coop_details = DB::connection('ls_seed_coop')->table('tbl_cooperatives')
                    ->where('accreditation_no', $request->coop_acre)
                    ->first();

					
                //UPDATE ON OLD SEASON
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $request->batch_number)
                ->where("is_hold", 0)
                ->where('seedTag', $seed_tag)
                ->update([
                    'totalBagCount' => intval($seedtag_details->totalBagCount) - intval($seedTag_bags),
                ]); 
                 
                    DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->insert([
                        'batchTicketNumber' => $new_batch_number,
                        'region' => $dropoff_point_data->region,
                        'province' => $dropoff_point_data->province,
                        'municipality' => $dropoff_point_data->municipality,
                        'dropOffPoint' => $dropoff_point_data->dropOffPoint,
                        'seedVariety' => $seedtag_details->seedVariety,
                        'totalBagCount' => $seedTag_bags,
                        'prv' => $dropoff_point_data->prv,
                        'seedTag' => $seed_tag,
                        'send' => 1,
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                        'app_version' => "v1.3.1",
                        'isRejected' => '0',
                        'is_transferred' => '1',
                        'remarks' => 'transferred from previous season batch: '.$request->batch_number,
                        'moa_number' => $coop_details->current_moa,
						'transferCategory' => 'P',
						'seedType' => $seedType,
                        
						
                    ]);
					
					
                    $seed_variety_log = $seed_variety_log.' | '.$seedtag_details->seedVariety;
                //generate string to store to lib_logs to store step by step transfer
                $log_str .= "($log_count) seedTag: $seed_tag, seedVariety: $seedtag_details->seedVariety, amounting to $seedTag_bags bag(s) : ";
                $log_count += 1;
                $bag_count += intval($seedTag_bags);
            }

			
           //record transaction in transder logs table last season //
           DB::connection('ls_rcep_transfers_db')->table('transfer_logs')
          // $d = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ws.transfer_logs')
            ->insert([
                'coop_accreditation' => $request->coop_acre,
                'seed_variety' => $request->batch_number,
                'bags' => $bag_count,
                'date_created' => date("Y-m-d H:i:s"),
                'created_by' => Auth::user()->username,
                'prv_dropoff_id' => $request->destination_dropoff,
            ]); 
 
             DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
          // $d = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ws.transfer_logs')
            ->insert([
                'batch_number' => $request->batch_number,
                'origin_province' => $request->original_province,
                'origin_municipality' => $request->original_municipality,
                'origin_dop_id' =>  $request->origin_dop_id,
                'destination_province' => $request->destination_province,
                'destination_municipality' => $request->destination_municipality,
                'destination_dop_id' => $request->destination_dropoff,
                'seed_variety' => $seed_variety_log,
                'bags' => $bag_count,
                'transferred_by' => Auth::user()->username,
            ]); 
 
            //record transaction in lib_logs
            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'TRANSFER_FROM_LAST_SEASON',
                'description' => 'Transferred seeds of batch ticket #: `'.$request->batch_number.'` from (prv_dropoff_id) from:'.$request->origin_dop_id.', to:'.$request->destination_dropoff.', amounting to a total of: '.$bag_count.' || summary of transfer: '.$log_str,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
            DB::commit();

            Session::flash('success', 'You have successfully transferred the delivery please refer to the details to double check for the changes that had been applied.');
            
            return route('rcef.transfers');
        }catch(\Illuminate\Database\QueryException $ex){
            return "sql_error";
            DB::rollback();
        }
    }

	
	public function transfer_dropoffList_PSTOCS(Request $request){
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->select('prv_dropoff_id', 'dropOffPoint')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->groupBy('prv_dropoff_id')
            ->orderBy('dropOffPoint')
            ->get();
        
        $return_str = "";

        foreach($data as $row){
            $return_str .= "<option value='$row->prv_dropoff_id'>$row->dropOffPoint</option>";
        }

        return $return_str;
    }


//END OF NEW TRANSFER CURRENT TO END













    public function index2() {

        $transfers = new Transfer();
        $drop_offpoints = $transfers->_drop_offpoints();

        $dropoff = array();

        $province_name = $transfers->_province_name();
        foreach ($drop_offpoints as $item) {
            $data = array(
                'prv_dropoff_id' => $item->prv_dropoff_id,
                'dropOffPoint' => $item->municipality . ' - ' . $item->dropOffPoint
            );
            array_push($dropoff, $data);
        }
        return view('transfer.index')
                        ->with(compact('dropoff'))
                        ->with(compact('province_name'));
    }

    public function loadscript() {

        return view('transfer.loadscript');
    }

    public function transfer_proceed(Request $request) {

        $transfer = new Transfer();
        $input = $request->all();
        $check_drop_off = $transfer->_check_dropoff($input['drop_id']);

        $get_province = $transfer->_get_prv_details();

        $explode_text = explode(" - ", $input['drop_name']);
        if ($check_drop_off == 0) {
            $data = array(
                'prv_dropoff_id' => $input['drop_id'],
                'coop_accreditation' => $input['coop_id'],
                'region' => $get_province->regionName,
                'province' => $get_province->province,
                'municipality' => $explode_text[0],
                'dropOffPoint' => $explode_text[1],
                'prv' => substr($input['drop_id'], 0, 6),
                'is_active' => 1,
                'date_created' => date("Y-m-d H:i:s"),
                'created_by' => Auth::user()->username
            );
            $transfer->_add_details($data);
        }

        $exp = explode("<", $input['temp_transfer']);
        for ($i = 0; $i < count(explode("<", $input['temp_transfer'])); $i++) {
			
            $explode = explode(">", $exp[$i]);
            if (count(explode(">", $exp[$i])) > 2) {
                if ($explode[1] != '') {
                    $data = array(
                        'batchTicketNumber' => "TRANSFER",
                        'region' => $get_province->regionName,
                        'province' => $get_province->province,
                        'municipality' => $explode_text[0],
                        'dropOffPoint' => $explode_text[1],
                        'seedVariety' => $explode[1],
                        'totalBagCount' => $explode[2],
                        'prv' => substr($input['drop_id'], 0, 6),
                        'send' => 1,
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'prv_dropoff_id' => $input['drop_id'],
                        'app_version' => "v1.3.1"
                    );
                    $transfer->_add_details_transfer($data);
                }
            }
        }
        echo json_encode("success");
    }

	public function central_transfer_dropoffs(Request $request){
		$data = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->select('prv_dropoff_id', 'region', 'province', 'municipality', 'dropOffPoint', 'prv')
            ->where('province', '=', $request->province_name)
            ->where('municipality', '=', $request->municipality_name)
            ->orderBy('dropOffPoint', 'asc')
            ->get();

        if(count($data) > 0){
            $return_str = "";
            foreach($data as $row){
                $return_str .= "<option value='$row->prv_dropoff_id'>$row->dropOffPoint</option>";
            }

            return json_encode($return_str);
        }else{
            return json_encode("no_dropoff");
        }
	}
	
	public function transfer_municipalities(Request $request){
        /*
        $data = DB::connection('delivery_inspection_db')
            ->table('lib_prv')
            ->select('provCode', 'munCode', 'municipality')
            ->where('provCode', '=', $request->province)
            ->orderBy('municipality', 'asc')
            ->get(); */
          //  dd($request->province_name);
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->select('municipality')
            ->where('province', $request->province_name)
            ->orderBy('municipality', 'ASC')
            ->groupBy('municipality')
            ->get();
        
        $return_str = "";
        foreach($data as $row){
            $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
        }
       // dd($return_str);    
        return $return_str;
    }
	
	public function ws_index(){
        /*
        if(Auth::user()->username == "19-0922" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga"){

            }else{

                return view('utility.pageClosed')
                    ->with('mss', "This module is temporary close");
            } */


        $provinces = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('province')->orderBy('province', 'ASC')->get();
        return view('transfer.ws2020.index')
            
        ->with('provinces', $provinces);
    }

    public function transfer_dropoffList(Request $request){
        $data = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->select('prv_dropoff_id', 'dropOffPoint')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->groupBy('prv_dropoff_id')
            ->orderBy('dropOffPoint')
            ->get();
        
        $return_str = "";
        foreach($data as $row){
            $return_str .= "<option value='$row->prv_dropoff_id'>$row->dropOffPoint</option>";
        }

        return $return_str;
    }

    public function transfer_delivery_tbl(Request $request){
        $batch_delivery_list = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('batchTicketNumber',DB::raw('SUM(totalBagCount) as total_bags'), 'region', 'province', 'municipality', 'dropOffPoint', "dateCreated")
            ->where('province', $request->province)
            ->where('municipality', $request->municipality)
            ->where('prv_dropoff_id', $request->dropoff)
			->where('batchTicketNumber', '!=', 'TRANSFER')
            ->groupBy('batchTicketNumber')
            ->get();

        $data_arr = array();
        foreach($batch_delivery_list as $row){
            $variety_list = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('seedVariety')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->groupBy('seedVariety')
                ->get();

            $variety_str = "";
            foreach($variety_list as $variety_row){
                $variety_str .= $variety_row->seedVariety.",<br> ";
            }

            $variety_str = rtrim($variety_str, ",<br> ");

            $seed_tag_list = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('seedTag')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->groupBy('seedTag')
                ->get();

            $seed_tags = "";
            foreach($seed_tag_list as $seedTag_row){
                $seed_tags .= $seedTag_row->seedTag."<br> ";
            }

            $seed_tags = rtrim($seed_tags, "<br> ");

            array_push($data_arr, array(
                'batchTicketNumber' => $row->batchTicketNumber,
                'province' => $row->province,
                'municipality' => $row->municipality,
                'variety_list' => $variety_str,
                'seed_tags' => $seed_tags,
                'total_bags' => number_format($row->total_bags)." bag(s)",
                'date_inspected' => date("Y-m-d", strtotime($row->dateCreated))
            ));
        }

        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->addColumn('action', function($row) {
            return "<a type='button' class='btn btn-success btn-round btn-sm' href='#' data-toggle='modal' data-target='#proceed_transfer_modal' data-batch='".$row['batchTicketNumber']."'><i class='fa fa-arrow-circle-right'></i> SELECT DELIVERY</a>";
        })
        ->make(true);
    }

    public function transfer_whole_home($batch_number){
        $batch_details = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->get();

       // $provinces = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('province')->orderBy('province', 'ASC')->get();
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province', 'ASC')->get();

        $original_location = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->select('region','province','municipality','dropOffPoint','prv_dropoff_id',DB::raw('SUM(totalBagCount) as total_bags'))
            ->groupBy('batchTicketNumber')
            ->first();
        
        return view('transfer.ws2020.whole_delivery')
            ->with('batch_details', $batch_details)
            ->with('batch_number', $batch_number)
            ->with('provinces', $provinces)
            ->with('original_location_region', $original_location->region)
            ->with('original_location_province', $original_location->province)
            ->with('original_location_municipality', $original_location->municipality)
            ->with('original_location_dop', $original_location->prv_dropoff_id)
            ->with('original_location_dop_name', $original_location->dropOffPoint)
            ->with('total_bags', number_format($original_location->total_bags)." bag(s)")
            ->with('total_bags_intval', $original_location->total_bags); 
    }

    public function confirm_transfer_whole(Request $request){
        DB::beginTransaction();
        try{
            
            //get destination region,province,municipality,dropoff etc. based on destination dop_id
            $dropoff_point_data = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();

            //update data in tbl_actual_delivery
            DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $request->batch_number)
            ->update([
                'region' => $dropoff_point_data->region,
                'province' => $dropoff_point_data->province,
                'municipality' => $dropoff_point_data->municipality,
                'dropOffPoint' => $dropoff_point_data->dropOffPoint,
                'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                'prv' => $dropoff_point_data->prv,
                'is_transferred' => '1',
				'transferCategory' => 'W',
            ]);

            //get coop accreditation number on tbl_delivery
            $coop_accreditation = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('coopAccreditation', 'batchTicketNumber')
            ->where('batchTicketNumber', $request->batch_number)
            ->groupBy('batchTicketNumber')
            ->value('coopAccreditation');

            DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->insert([
                'batch_number' => $request->batch_number,
                'origin_province' => $request->original_province,
                'origin_municipality' => $request->original_municipality,
                'origin_dop_id' => $request->origin_dop_id,
                'destination_province' => $request->destination_province,
                'destination_municipality' => $request->destination_municipality,
                'destination_dop_id' => $request->destination_dropoff,
                'seed_variety' => "ALL_SEEDS_TRANSFER",
                'bags' => $request->total_bags_intval,
                'transferred_by' => Auth::user()->username
            ]);


            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'TRANSFER_WHOLE',
                'description' => 'Transferred seeds of batch ticket #: `'.$request->batch_number.'` from (prv_dropoff_id) from:'.$request->origin_dop_id.', to:'.$request->destination_dropoff.', amounting to '.$request->total_bags_intval." bag(s)",
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
            DB::commit();
            
            Session::flash('success', 'You have successfully transferred the delivery please refer to the details to double check for the changes that had been applied.');
            return route('transfers.ws2020.whole', $request->batch_number);

        }catch(\Illuminate\Database\QueryException $ex){
            return "sql_error";
            DB::rollback();
        }
        
    }

    public function transfer_partial_home($batch_number){
        $batch_details = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->get();

        $provinces = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('province')->orderBy('province', 'ASC')->get();

        $original_location = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->select('region','province','municipality','dropOffPoint','prv_dropoff_id',DB::raw('SUM(totalBagCount) as total_bags'))
            ->groupBy('batchTicketNumber')
            ->first();
        
        return view('transfer.ws2020.partial_delivery')
            ->with('batch_details', $batch_details)
            ->with('batch_number', $batch_number)
            ->with('provinces', $provinces)
            ->with('original_location_region', $original_location->region)
            ->with('original_location_province', $original_location->province)
            ->with('original_location_municipality', $original_location->municipality)
            ->with('original_location_dop', $original_location->prv_dropoff_id)
            ->with('original_location_dop_name', $original_location->dropOffPoint)
            ->with('total_bags', number_format($original_location->total_bags)." bag(s)")
            ->with('total_bags_intval', $original_location->total_bags);
    }

    public function check_seedtag(Request $request){
        $actual_delivery_batch = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $request->batch_number)
            ->where('seedTag', $request->seed_tag)
            ->first();



            //EBINHI CHECKER
            if($actual_delivery_batch != null){
                if($actual_delivery_batch->qrValStart != ""){
                   $deliveryBinhiData =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        ->where('seedTag', $request->seed_tag)
                        ->where('province', $actual_delivery_batch->province)
                        ->where('municipality', $actual_delivery_batch->municipality)
                        ->whereRaw("qrValStart != '' ")
                        ->sum("totalBagCount");

                    $binhiClaimData = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                        ->where("seedTag", $request->seed_tag )
                        ->where('province', $actual_delivery_batch->province)
                        ->where('municipality', $actual_delivery_batch->municipality)
                        ->count("claimId");
                
                    $actual_count = $deliveryBinhiData - $binhiClaimData;
                    
                }else{
                    // $actual_count = $actual_delivery_batch->totalBagCount;

                    $prv_dropoff_id = $actual_delivery_batch->prv_dropoff_id;
                    $over_all_count =DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where("prv_dropoff_id", $prv_dropoff_id)
                            ->where("seedVariety", $actual_delivery_batch->seedVariety)
                            ->sum('totalBagCount');
                    $prv_db = substr($prv_dropoff_id,0,4);
                    $distributed = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".new_released")
                            ->where("prv_dropoff_id", $prv_dropoff_id)
                            ->where("seed_variety", $actual_delivery_batch->seedVariety)
                            ->sum("bags_claimed");
        
                    // $total_bags = $request->seed_tag_value + $distributed;
                    $actual_count = $over_all_count - $distributed;
        
                 



                }

                
                if($request->seed_tag_value > 0){
                    if($actual_count >= $request->seed_tag_value){
                        return array(
                            'msg' => "amount_to_transfer_ok",
                            'card_id' => $actual_delivery_batch->actualDeliveryId,
                        );
                    }else{
                        return array(
                            'msg' => "amount_to_transfer_exceeds",
                            'max_tag' => number_format($actual_count)." bag(s)",
                        );
                    }
                }else{
                    return "No Bag To transfer";
                }

              



            }







      
    }

    public function get_seedTag_details(Request $request){
        $actual_delivery_batch = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('actualDeliveryId', $request->actual_delivery_id)
            ->first();

        return array(
            'seed_tag' => $actual_delivery_batch->seedTag,
            'seed_variety' => $actual_delivery_batch->seedVariety,
        );
    }

    public function confirm_transfer_partial(Request $request){
        $transfer_str = rtrim($request->transfer_str,"*|*");
        $transfer_list = explode("*|*", $transfer_str);
        
        DB::beginTransaction();
        try{
            //get destination region,province,municipality,dropoff etc. based on destination dop_id
            $dropoff_point_data = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();

            $new_batch_number = Auth::user()->userId."-BCH-".time();
            $log_str = "";
            $log_count = 1;
            $bag_count = 0;

            //add data to tbl_actual_delivery for each seed tag
            foreach($transfer_list as $str_row){
                $transfer_details = explode("&", $str_row);
                $seed_tag = $transfer_details[0];
                $seedTag_bags = $transfer_details[1];

                //get seed tag details based on batch number & seed tag on tbl_actual_delivery
                $seedtag_details = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $request->batch_number)
                    ->where('seedTag', $seed_tag)
                    ->first();
                // // STOPPED HERE
               


                //update total bags per seed tag in tbl_actual_delivery of original batch ticket #
                DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $request->batch_number)
                ->where('seedTag', $seed_tag)
                ->update([
                    'totalBagCount' => intval($seedtag_details->totalBagCount) - intval($seedTag_bags),
                ]);

                 //insert in tbl_actual_delivery
                DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->insert([
                    'batchTicketNumber' => $new_batch_number,
                    'region' => $dropoff_point_data->region,
                    'province' => $dropoff_point_data->province,
                    'municipality' => $dropoff_point_data->municipality,
                    'dropOffPoint' => $dropoff_point_data->dropOffPoint,
                    'seedVariety' => $seedtag_details->seedVariety,
                    'totalBagCount' => $seedTag_bags,
                    'dateCreated' => date("Y-m-d"),
                    'send' => 1,
                    'seedTag' => $seed_tag,
                    'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                    'prv' => $dropoff_point_data->prv,
                    'moa_number' => $seedtag_details->moa_number,
                    'app_version' => '',
                    'batchSeries' => '',
                    'remarks' => 'transferred from batch: '.$request->batch_number,
                    'isRejected' => '0',
                    'is_transferred' => '1',
					'transferCategory' => 'T',
                ]);

                //generate string to store to lib_logs to store step by step transfer
                $log_str .= "($log_count) seedTag: $seed_tag, seedVariety: $seedtag_details->seedVariety, amounting to $seedTag_bags bag(s) : ";
                $log_count += 1;
                $bag_count += intval($seedTag_bags);

//15950
//    // STOPPED HERE

               

            }

            $coop_details_trans = DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction')
            ->where('batchTicketNumber', $request->batch_number)
            ->first();

            if($coop_details_trans != null){

                $transfered_data = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        ->where("batchTicketNumber", $new_batch_number)
                        ->sum('totalBagCount');

                DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction')
                ->insert([
                    'instructed_delivery_volume' => $transfered_data,
                    'region' => $dropoff_point_data->region,
                    'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                    'moa_number' => $coop_details_trans->moa_number,
                    'accreditation_no' => $coop_details_trans->accreditation_no,
                    'delivery_date' =>$coop_details_trans->delivery_date,
                    'date_created' => $coop_details_trans->date_created,
                    'status' => 0,
                    'batchTicketNumber' => $new_batch_number,
                    'confirmed_delivery' => $transfered_data,
                    'actual_delivered' => $transfered_data,
                    'user_id' => Auth::user()->userId,
                    'isBuffer' => 0,
                    'is_for_replacement' => 0,
                    'seed_distribution_mode' => 'Transfer'
                ]);

            }



            //record transaction in transder logs table
            DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->insert([
                'batch_number' => $request->batch_number,
                'origin_province' => $request->original_province,
                'origin_municipality' => $request->original_municipality,
                'origin_dop_id' => $request->origin_dop_id,
                'destination_province' => $request->destination_province,
                'destination_municipality' => $request->destination_municipality,
                'destination_dop_id' => $request->destination_dropoff,
                'seed_variety' => "PARTIAL_SEEDS_TRANSFER",
                'bags' => $bag_count,
                'transferred_by' => Auth::user()->username
            ]);

            //record transaction in lib_logs
            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'TRANSFER_PARTIAL',
                'description' => 'Transferred seeds of batch ticket #: `'.$request->batch_number.'` from (prv_dropoff_id) from:'.$request->origin_dop_id.', to:'.$request->destination_dropoff.', amounting to a total of: '.$bag_count.' || summary of transfer: '.$log_str,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            DB::commit();

            Session::flash('success', 'You have successfully transferred the delivery please refer to the details to double check for the changes that had been applied.');
            return route('transfers.ws2020.partial', $request->batch_number);
        }catch(\Illuminate\Database\QueryException $ex){
            return array(
				"msg" => $ex,
				"alert" => "sql_error"
			);
            DB::rollback();
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Session;
use Excel;

use Yajra\Datatables\Facades\Datatables;

class ReportGoogleSheetController extends Controller
{

    public function refresh_seedList(Request $request){
        $seed_varities = DB::table('seed_seed.seed_characteristics')->groupBy('variety')->orderBy('variety_name')->get();
    
        $return_str = ""; 
		$return_str .= "<option value='0'>Please select a seed variety</option>";
		foreach($seed_varities as $row){
			$return_str .= "<option value='".$row->variety."'>$row->variety</option>";
		}

		return $return_str;
    }

    public function get_municipalities(Request $request){
        if(isset($request->view)){
            if($request->view == "WITH_BALANCE"){
                $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
					//->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->province)
                    ->where('current_balance', '!=', 0)
                    ->groupBy('municipality')
                    ->orderBy('municipality')
                    ->get();
            }else if($request->view == "ALL"){
                $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
					//->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->province)
                    ->groupBy('municipality')
                    ->orderBy('municipality')
                    ->get();
            }
            
        }else{
            $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
				//->where('station_id', Auth::user()->stationId)
                ->where('province', $request->province)
                ->where('current_balance', '!=', 0)
                ->groupBy('municipality')
                ->orderBy('municipality')
                ->get();
        }
        

		$return_str = ""; 
		$return_str .= "<option value='0'>Please select a municipality</option>";
		foreach($municipalities as $row){
            $available_bags = number_format($row->current_balance);
			$return_str .= "<option value='".$row->municipality."'>$row->municipality = $available_bags bag(s)</option>";
		}

		return $return_str;
    }

    public function get_PCList(Request $request){
        $assigned_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->where('province', $request->province)
            ->value('prv');

        //get list from users table
        $pc_list = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
            ->where('province', substr($assigned_prv, 0, 4))
            ->get();

        //check if role == 1
        $pc_list_filtered = array();
        foreach($pc_list as $pc_row){
            $pc_role = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.role_user')
                ->where('userId', $pc_row->userId)
                ->where('roleId', 1)
                ->first();

            if(count($pc_role) > 0){
                array_push($pc_list_filtered, array(
                    "id" => $pc_row->userId,
                    "username" => $pc_row->username,
                    "firstName" => $pc_row->firstName,
                    "lastName" => $pc_row->lastName
                ));
            }
        }
        

		$return_str = ""; 
		$return_str .= "<option value='0'>Please select a PC</option>";
		foreach($pc_list_filtered as $row){
            $pc_name = $row['firstName']." ".$row['lastName'];
			$return_str .= "<option value='".$row['username']."'>".$pc_name."</option>";
		}

		return $return_str;
    }

    /********************************************************************************************************************** */

    public function shedule_home(){
        $schedule_list = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->select(DB::raw('SUM(from_bags_total) as total_bags'),
                'transaction_code', 'seed_type', 'source', 'status', 'from_coop',
                'from_province', 'from_municipality', 'from_dop', 'from_assigned_pc',
                'to_province', 'to_municipality', 'to_dop', 'to_delivery_date', 'to_transfer_date',
                'to_assigned_pc', 'date_recorded', 'edit_final_flag', 'edit_draft_flag')
            ->groupBy('transaction_code')
            ->get();

        $cooperatives  = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
        //$province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
		$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();

        return view('reports.google.schedule.form_home_new')
            ->with('schedule_list', $schedule_list)
            ->with('cooperatives', $cooperatives)
            ->with('provinces', $province);
    }

    public function shedule_form(){
        $cooperatives  = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
        $seed_varities = DB::table('seed_seed.seed_characteristics')->groupBy('variety')->orderBy('variety_name')->get();
        $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
        $station_name = DB::table('geotag_db2.tbl_station')->where('stationId', Auth::user()->stationId)->value('stationName');
        $station_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
            ->select(DB::raw('SUM(current_balance) as total_current_balance'),
                DB::raw('SUM(original_balance) as total_original_balance'))
            ->where('station_id', Auth::user()->stationId)
            ->first();
			
		//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('station_id', Auth::user()->stationId)->groupBy('province')->orderBy('province')->get();

        return view('reports.google.schedule.form_entry')
            ->with('cooperatives', $cooperatives)
            ->with('seed_varities', $seed_varities)
            ->with('provinces', $province)
            ->with('station_name', $station_name)
            ->with('total_current_balance', number_format($station_balance->total_current_balance))
            ->with('total_original_balance', number_format($station_balance->total_original_balance));
    }

    public function get_transaction_details(Request $request){
        $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->where('transaction_code', $request->transaction_code)
            ->groupBy('transaction_code')
            ->first();

        return array(
            "transaction_title" => $schedule_details->transaction_title,
            "transaction_code" => $schedule_details->transaction_code,
            "delivery_status" => $schedule_details->delivery_status,
            "document_status" => $schedule_details->document_status,
            "document_status_remarks" => $schedule_details->document_status_remarks
        );
    }

    public function update_transaction_status(Request $request){
        DB::beginTransaction();
        try {
            if($request->document_status == 1){
                //update remarks
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->where('transaction_code', $request->transaction_code)
                ->update(
                    [
                        "delivery_status" => $request->delivery_status,
                        "document_status" => $request->document_status,
                        "document_status_remarks" => $request->document_status_remarks
                    ]
                );
    
            }else{
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->where('transaction_code', $request->transaction_code)
                ->update(
                    [
                        "delivery_status" => $request->delivery_status,
                        "document_status" => $request->document_status,
                        "document_status_remarks" => ""
                    ]
                );
            }

            DB::commit();
            return "update_ok";

        } catch (\Exception $ex) {
            DB::rollback();
            return "sql_error";
        }
    }
	
	public function schedule_tbl(Request $request){

        if($request->search_filter_seedType == "ALL"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');
        }else{
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('source', $request->search_filter_seedType)
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');
        }

        if($request->search_filter == "ALL"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');
        
        }else if($request->search_filter == "TRANSACTION_CODE"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');
        
        }else if($request->search_filter == "DATE_RANGE"){
            $date_range_str = explode(" - ", $request->date_range);
            if($request->date_category == "date_recorded"){
                $from_date = date('Y-m-d', strtotime($date_range_str[0]))." 00:00:00";
                $to_data = date('Y-m-d', strtotime($date_range_str[1]))." 23:59:59";
            }else{
                $from_date = date('Y-m-d', strtotime($date_range_str[0]));
                $to_data = date('Y-m-d', strtotime($date_range_str[1]));
            }
            
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where($request->date_category, '>=', "$from_date")
                ->Where($request->date_category, '<=', "$to_data")
                ->Where($request->date_category, '!=', "0000-00-00")
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');

        }else if($request->search_filter == "SEARCH_TRANSACTION"){
            $date_range_str = explode(" - ", $request->date_range);
            $from_date = date('Y-m-d', strtotime($date_range_str[0]));
            $to_data = date('Y-m-d', strtotime($date_range_str[1]));
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where($request->date_category, '>=', "$from_date")
                ->Where($request->date_category, '<=', "$to_data")
                ->Where($request->date_category, '!=', "0000-00-00")
                ->where('transaction_title', 'like', "%$request->transaction_title%")
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');

        }else if($request->search_filter == "TITLE_OF_TRANSACTION"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('transaction_title', 'like', "%$request->transaction_title%")
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');

        }else if($request->search_filter == "FILTER_SEED_COOP"){
            $where_query = "WHERE ";
            if($request->from_coop != "0"){ $where_query .= "from_coop_accreditation = '$request->from_coop' AND "; }
            if($request->to_province != "0"){ $where_query .= "to_province = '$request->to_province' AND "; }
            if($request->to_municipality != "0"){ $where_query .= "to_municipality = '$request->to_municipality' AND "; }
            if($request->to_dop_name != ""){ $where_query .= "to_dop = '$request->to_dop_name' AND "; }
            if($request->to_delivery_date != ""){ $where_query .= "to_delivery_date = '$request->to_delivery_date' AND "; }
            if($request->to_assigned_pc != ""){ $where_query .= "to_assigned_pc = '$request->to_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);

        }else if($request->search_filter == "FILTER_PHILRICE_WAREHOUSE"){
            $where_query = "WHERE ";
            if($request->from_coop != "0"){ $where_query .= "from_coop_accreditation = '$request->from_coop' AND "; }
            if($request->from_province != "0"){ $where_query .= "from_province = '$request->from_province' AND "; }
            if($request->from_municipality != "0"){ $where_query .= "from_municipality = '$request->from_municipality' AND "; }
            if($request->from_dop_name != ""){ $where_query .= "from_dop = '$request->from_dop_name' AND "; }
            if($request->to_province != "0"){ $where_query .= "to_province = '$request->to_province' AND "; }
            if($request->to_municipality != "0"){ $where_query .= "to_municipality = '$request->to_municipality' AND "; }
            if($request->to_dop_name != ""){ $where_query .= "to_dop = '$request->to_dop_name' AND "; }
            if($request->to_delivery_date != ""){ $where_query .= "to_delivery_date = '$request->to_delivery_date' AND "; }
            if($request->to_assigned_pc != ""){ $where_query .= "to_assigned_pc = '$request->to_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);
        
        }else if($request->search_filter == "FILTER_LGU_STOCKS"){
            $where_query = "WHERE ";
            if($request->from_province != "0"){ $where_query .= "from_province = '$request->from_province' AND "; }
            if($request->from_municipality != "0"){ $where_query .= "from_municipality = '$request->from_municipality' AND "; }
            if($request->from_dop_name != ""){ $where_query .= "from_dop = '$request->from_dop_name' AND "; }
            if($request->from_assigned_pc != ""){ $where_query .= "from_assigned_pc = '$request->from_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);
       
        }else if($request->search_filter == "FILTER_TRANSFERRED"){
            $where_query = "WHERE ";
            if($request->from_province != "0"){ $where_query .= "from_province = '$request->from_province' AND "; }
            if($request->from_municipality != "0"){ $where_query .= "from_municipality = '$request->from_municipality' AND "; }
            if($request->from_dop_name != ""){ $where_query .= "from_dop = '$request->from_dop_name' AND "; }
            if($request->from_assigned_pc != ""){ $where_query .= "from_assigned_pc = '$request->from_assigned_pc' AND "; }
            
            if($request->to_province != "0"){ $where_query .= "to_province = '$request->to_province' AND "; }
            if($request->to_municipality != "0"){ $where_query .= "to_municipality = '$request->to_municipality' AND "; }
            if($request->to_dop_name != ""){ $where_query .= "from_dop = '$request->to_dop_name' AND "; }
            if($request->to_assigned_pc != ""){ $where_query .= "to_assigned_pc = '$request->to_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);
        }


        return Datatables::of($query)
        ->addColumn('status_str', function($row){
            if($row->status == "APPROVED"){
                return '<span class="badge badge-success">Approved</span>';
            }else if($row->status == "RESCHEDULED"){
                return '<span class="badge badge-warning">Re-Scheduled</span>';
            }else if($row->status == "CANCELLED"){
                return '<span class="badge badge-danger">Cancelled</span>';
            }
        })
        ->addColumn('seed_col', function($row){
            return "<strong><u>".$row->transaction_title."</u></strong><br>".$row->seed_type.", ".$row->source."<br>".$row->total_bags." bag(s) - 20kg/bag";            
        })
        ->addColumn('origin_col', function($row){
            $return_from_str = '';
            //FROM
            if($row->seed_type == "NEW" AND $row->source == "SEED_COOP" OR
                $row->seed_type == "INVENTORY_WS" AND $row->source == "SEED_COOP" OR
                $row->seed_type == "INVENTORY_DS" AND $row->source == "SEED_COOP"){
                    
                    $return_from_str .= "<strong>COOP: </strong>".$row->from_coop."<br>";

            }else if($row->seed_type == "INVENTORY_WS" AND $row->source == "PHILRICE_WAREHOUSE" OR
                $row->seed_type == "INVENTORY_DS" AND $row->source == "PHILRICE_WAREHOUSE"){

                    $return_from_str .= "<strong>COOP: </strong>".$row->from_coop."<br>";
                    $return_from_str .= "<strong>PROVINCE: </strong>".strtoupper($row->from_province)."<br>";
                    $return_from_str .= "<strong>MUNICIpALITY: </strong>".strtoupper($row->from_municipality)."<br>";
                    $return_from_str .= "<strong>DOP: </strong>".strtoupper($row->from_dop)."<br>";

            }else if($row->seed_type == "INVENTORY_WS" AND $row->source == "LGU_STOCKS" OR
                $row->seed_type == "INVENTORY_DS" AND $row->source == "LGU_STOCKS"){

                    $return_from_str .= "<strong>PROVINCE: </strong>".strtoupper($row->from_province)."<br>";
                    $return_from_str .= "<strong>MUNICIpALITY: </strong>".strtoupper($row->from_municipality)."<br>";
                    $return_from_str .= "<strong>DOP: </strong>".strtoupper($row->from_dop)."<br>";

            }else if($row->seed_type == "INVENTORY_WS" AND $row->source == "TRANSFERRED_SEEDS" OR
                $row->seed_type == "INVENTORY_DS" AND $row->source == "TRANSFERRED_SEEDS" OR
                $row->seed_type == "NEW" AND $row->source == "TRANSFERRED_SEEDS"){

                    $return_from_str .= "<strong>PROVINCE: </strong>".strtoupper($row->from_province)."<br>";
                    $return_from_str .= "<strong>MUNICIpALITY: </strong>".strtoupper($row->from_municipality)."<br>";
                    $return_from_str .= "<strong>DOP: </strong>".strtoupper($row->from_dop)."<br>";
            }

            return $return_from_str;
        })
        ->addColumn('destination_col', function($row){
            $return_to_str = '';
            //TO
            if($row->seed_type == "INVENTORY_WS" AND $row->source == "LGU_STOCKS" OR
                $row->seed_type == "INVENTORY_DS" AND $row->source == "LGU_STOCKS"){

                $return_to_str .= "N/A";
            }else{
                $return_to_str .= "<strong>PROVINCE: </strong>".strtoupper($row->to_province)."<br>";
                $return_to_str .= "<strong>MUNICIpALITY: </strong>".strtoupper($row->to_municipality)."<br>";
                $return_to_str .= "<strong>DOP: </strong>".strtoupper($row->to_dop)."<br>";
            }

            return $return_to_str;
        })
        ->addColumn('status_col', function($row){
            $delivery_status = "";
            $document_status = "";
            $document_details = "";
            $edit_satus_btn = "";

            if($row->delivery_status == 0){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-dark">Delivery Status: N/A</span><br>';
            }else if($row->delivery_status == 1){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-warning">Delivery Status: On Process</span><br>';
            }else if($row->delivery_status == 2){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-success">Delivery Status: Paid</span><br>';
            }else if($row->delivery_status == 3){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-danger">Delivery Status: Unpaid</span><br>';
            }

            if($row->document_status == 0){
                $document_status .= '<span style="font-size: 12px;border-radius: 10px;margin-top: 5px;" class="badge badge-warning">Document status: not submitted</span><br>';
                $document_details = "<textarea style='width:100%;margin-top: 10px;' class='form-control' rows='3' disabled>N/A</textarea>";
            }else if($row->document_status == 1){
                $document_status .= '<span style="font-size: 12px;border-radius: 10px;margin-top: 5px;" class="badge badge-success">Document status: submitted</span><br>';
                $document_details = "<textarea style='width:100%;margin-top: 10px;' class='form-control' rows='3' disabled>$row->document_status_remarks</textarea>";
            }

            if(Auth::user()->roles->first()->name == "rcef-pmo"){
                $edit_satus_btn .= "<button data-id='$row->transaction_code' data-toggle='modal' data-target='#show_status_modal' class='btn btn-success btn-sm btn-block' style='margin-top: 5px;'><i class='fa fa-edit'></i> Edit status</button>";
                return $delivery_status.$document_status.$document_details.$edit_satus_btn;
            }else{
                $edit_satus_btn .= "<button class='btn btn-success btn-sm btn-block' style='margin-top: 5px;' disabled><i class='fa fa-edit'></i> Edit status</button>";
                return $delivery_status.$document_status.$document_details;
            }

            
        })
        ->addColumn('action', function($row){
            if($row->edit_final_flag == 1){
                //$view_btn   = "<a href='".route('rcep.google_sheet.view', $row->transaction_code)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-eye'></i> VIEW SCHEDULE</a><br>";
                $view_btn   = "<button class='btn btn-default btn-sm' style='border-radius:20px;'><i class='fa fa-lock'></i> EDIT NOT AVAILABLE</button>";
                $show_btn   = "<a href='#' data-toggle='modal' data-target='#show_more_modal' data-id='$row->transaction_code' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-search-plus'></i> SHOW MORE DETAILS</a>";

                $check_actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->where('transaction_code', $row->transaction_code)->first();
                if(count($check_actual) > 0){
                    $actual_btn = "<button class='btn btn-default btn-sm' style='border-radius:20px;'><i class='fa fa-lock'></i> FINALIZED REPORT</button>";
                }else{
                    $actual_btn = "<a href='".route('rcep.google_sheet.actual', $row->transaction_code)."' class='btn btn-danger btn-sm' style='border-radius:20px;'><i class='fa fa-check-circle'></i> SEND ACTUAL DATA</a>";
                }
                
                return $view_btn.$actual_btn.$show_btn;
            }else{
                $view_btn = "<a href='".route('rcep.google_sheet.view', $row->transaction_code)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-eye'></i> VIEW SCHEDULE</a>";
                $show_btn   = "<a href='#' data-toggle='modal' data-target='#show_more_modal' data-id='$row->transaction_code' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-search-plus'></i> SHOW MORE DETAILS</a>";
                $save_btn = "<a href='".route('rcep.google_sheet.save_as_final', $row->transaction_code)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-check'></i> FINALIZE SCHEDULE</a>";
                
                return $view_btn.$save_btn.$show_btn;
            }            
        })
        ->make(true);
    }

    public function schedule_tbl_old(Request $request){
        
        if($request->search_filter == "ALL"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');
        
        }else if($request->search_filter == "TRANSACTION_CODE"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');
        
        }else if($request->search_filter == "DATE_RANGE"){
            $date_range_str = explode(" - ", $request->date_range);
            if($request->date_category == "date_recorded"){
                $from_date = date('Y-m-d', strtotime($date_range_str[0]))." 00:00:00";
                $to_data = date('Y-m-d', strtotime($date_range_str[1]))." 23:59:59";
            }else{
                $from_date = date('Y-m-d', strtotime($date_range_str[0]));
                $to_data = date('Y-m-d', strtotime($date_range_str[1]));
            }
            
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where($request->date_category, '>=', "$from_date")
                ->Where($request->date_category, '<=', "$to_data")
                ->Where($request->date_category, '!=', "0000-00-00")
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');

        }else if($request->search_filter == "SEARCH_TRANSACTION"){
            $date_range_str = explode(" - ", $request->date_range);
            $from_date = date('Y-m-d', strtotime($date_range_str[0]));
            $to_data = date('Y-m-d', strtotime($date_range_str[1]));
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where($request->date_category, '>=', "$from_date")
                ->Where($request->date_category, '<=', "$to_data")
                ->Where($request->date_category, '!=', "0000-00-00")
                ->where('transaction_title', 'like', "%$request->transaction_title%")
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');

        }else if($request->search_filter == "TITLE_OF_TRANSACTION"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select('*', DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('transaction_title', 'like', "%$request->transaction_title%")
                ->orderBy('date_recorded', 'DESC')
                ->groupBy('transaction_code');

        }else if($request->search_filter == "FILTER_SEED_COOP"){
            $where_query = "WHERE ";
            if($request->from_coop != "0"){ $where_query .= "from_coop_accreditation = '$request->from_coop' AND "; }
            if($request->to_province != "0"){ $where_query .= "to_province = '$request->to_province' AND "; }
            if($request->to_municipality != "0"){ $where_query .= "to_municipality = '$request->to_municipality' AND "; }
            if($request->to_dop_name != ""){ $where_query .= "to_dop = '$request->to_dop_name' AND "; }
            if($request->to_delivery_date != ""){ $where_query .= "to_delivery_date = '$request->to_delivery_date' AND "; }
            if($request->to_assigned_pc != ""){ $where_query .= "to_assigned_pc = '$request->to_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);

        }else if($request->search_filter == "FILTER_PHILRICE_WAREHOUSE"){
            $where_query = "WHERE ";
            if($request->from_coop != "0"){ $where_query .= "from_coop_accreditation = '$request->from_coop' AND "; }
            if($request->from_province != "0"){ $where_query .= "from_province = '$request->from_province' AND "; }
            if($request->from_municipality != "0"){ $where_query .= "from_municipality = '$request->from_municipality' AND "; }
            if($request->from_dop_name != ""){ $where_query .= "from_dop = '$request->from_dop_name' AND "; }
            if($request->to_province != "0"){ $where_query .= "to_province = '$request->to_province' AND "; }
            if($request->to_municipality != "0"){ $where_query .= "to_municipality = '$request->to_municipality' AND "; }
            if($request->to_dop_name != ""){ $where_query .= "to_dop = '$request->to_dop_name' AND "; }
            if($request->to_delivery_date != ""){ $where_query .= "to_delivery_date = '$request->to_delivery_date' AND "; }
            if($request->to_assigned_pc != ""){ $where_query .= "to_assigned_pc = '$request->to_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);
        
        }else if($request->search_filter == "FILTER_LGU_STOCKS"){
            $where_query = "WHERE ";
            if($request->from_province != "0"){ $where_query .= "from_province = '$request->from_province' AND "; }
            if($request->from_municipality != "0"){ $where_query .= "from_municipality = '$request->from_municipality' AND "; }
            if($request->from_dop_name != ""){ $where_query .= "from_dop = '$request->from_dop_name' AND "; }
            if($request->from_assigned_pc != ""){ $where_query .= "from_assigned_pc = '$request->from_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);
       
        }else if($request->search_filter == "FILTER_TRANSFERRED"){
            $where_query = "WHERE ";
            if($request->from_province != "0"){ $where_query .= "from_province = '$request->from_province' AND "; }
            if($request->from_municipality != "0"){ $where_query .= "from_municipality = '$request->from_municipality' AND "; }
            if($request->from_dop_name != ""){ $where_query .= "from_dop = '$request->from_dop_name' AND "; }
            if($request->from_assigned_pc != ""){ $where_query .= "from_assigned_pc = '$request->from_assigned_pc' AND "; }
            
            if($request->to_province != "0"){ $where_query .= "to_province = '$request->to_province' AND "; }
            if($request->to_municipality != "0"){ $where_query .= "to_municipality = '$request->to_municipality' AND "; }
            if($request->to_dop_name != ""){ $where_query .= "from_dop = '$request->to_dop_name' AND "; }
            if($request->to_assigned_pc != ""){ $where_query .= "to_assigned_pc = '$request->to_assigned_pc' AND "; }
            
            $where_query = rtrim($where_query, "AND ");
            //dd($where_query);
            $query = DB::select(DB::raw("SELECT *, 
                SUM(from_bags_total) as total_bags FROM rcep_google_sheets.lib_seed_schedule ".$where_query." AND seed_type = '$request->seed_type' AND source = '$request->source' AND `status` = '$request->status'
                GROUP BY transaction_code ORDER BY date_recorded DESC"));
            $query = collect($query);
        }


        return Datatables::of($query)
        ->addColumn('status_str', function($row){
            if($row->status == "APPROVED"){
                return '<span class="badge badge-success">Approved</span>';
            }else if($row->status == "RESCHEDULED"){
                return '<span class="badge badge-warning">Re-Scheduled</span>';
            }else if($row->status == "CANCELLED"){
                return '<span class="badge badge-danger">Cancelled</span>';
            }
        })
        ->addColumn('info_col', function($row){
            $info_str = "";
            if($row->seed_type == "NEW" AND $row->source == "SEED_COOP" OR
                $row->seed_type == "INVENTORY_WS" AND $row->source == "SEED_COOP" OR
                $row->seed_type == "INVENTORY_DS" AND $row->source == "SEED_COOP"){

                $info_str .= "<div style='font-size:18px;'><strong>TITLE: <u>$row->transaction_title</u></strong></div>";
                $info_str .= "<div><strong>SOURCE: </strong>$row->seed_type, $row->source</div>";
                $info_str .= "<div><strong>SEED COOPERATIVE: </strong>$row->from_coop</div>";
                $info_str .= "<div><strong>BAGS FOR DELIVERY: </strong>$row->total_bags bag(s)</div>";
                $info_str .= "<div><strong>DESTINATION: </strong>".strtoupper($row->to_province)." < ".strtoupper($row->to_municipality)." < ".strtoupper($row->to_dop)."</div>";
                $info_str .= "<div><strong>DELIVERY DATE: </strong>".date("F j, Y", strtotime($row->to_delivery_date))."</div>";    
                $info_str .= "<div><strong>DATE RECORDED: </strong>".date("F j, Y g:i A", strtotime($row->date_recorded))."</div>";
            
            }else if($row->seed_type == "INVENTORY_WS" AND $row->source == "PHILRICE_WAREHOUSE" OR
                     $row->seed_type == "INVENTORY_DS" AND $row->source == "PHILRICE_WAREHOUSE"){
                $info_str .= "<div style='font-size:18px;'><strong>TITLE: <u>$row->transaction_title</u></strong></div>";
                $info_str .= "<div><strong>SOURCE: </strong>$row->seed_type, $row->source</div>";
                $info_str .= "<div><strong>SEED COOPERATIVE: </strong>$row->from_coop</div>";
                $info_str .= "<div><strong>BAGS FOR DELIVERY: </strong>$row->total_bags bag(s)</div>";
                $info_str .= "<div><strong>PLACE OF ORIGIN: </strong>".strtoupper($row->from_province)." < ".strtoupper($row->from_municipality)." < ".strtoupper($row->from_dop)."</div>";
                $info_str .= "<div><strong>DESTINATION: </strong>".strtoupper($row->to_province)." < ".strtoupper($row->to_municipality)." < ".strtoupper($row->to_dop)."</div>";
                $info_str .= "<div><strong>DELIVERY DATE: </strong>".date("F j, Y", strtotime($row->to_delivery_date))."</div>";
                $info_str .= "<div><strong>DATE RECORDED: </strong>".date("F j, Y g:i A", strtotime($row->date_recorded))."</div>";                
            
            }else if($row->seed_type == "INVENTORY_WS" AND $row->source == "LGU_STOCKS" OR
                     $row->seed_type == "INVENTORY_DS" AND $row->source == "LGU_STOCKS"){
                $info_str .= "<div style='font-size:18px;'><strong>TITLE: <u>$row->transaction_title</u></strong></div>";
                $info_str .= "<div><strong>SOURCE: </strong>$row->seed_type, $row->source</div>";
                $info_str .= "<div><strong>BAGS REMAINING IN LGU: </strong>$row->total_bags bag(s)</div>";
                $info_str .= "<div><strong>PLACE OF ORIGIN: </strong>".strtoupper($row->from_province)." < ".strtoupper($row->from_municipality)." < ".strtoupper($row->from_dop)."</div>";
                $info_str .= "<div><strong>ASSIGNED PC: </strong>$row->from_assigned_pc</div>";
                $info_str .= "<div><strong>DATE RECORDED: </strong>".date("F j, Y g:i A", strtotime($row->date_recorded))."</div>";
            
            }else if($row->seed_type == "INVENTORY_WS" AND $row->source == "TRANSFERRED_SEEDS" OR
                    $row->seed_type == "INVENTORY_DS" AND $row->source == "TRANSFERRED_SEEDS" OR
                    $row->seed_type == "NEW" AND $row->source == "TRANSFERRED_SEEDS"){
                $info_str .= "<div style='font-size:18px;'><strong>TITLE: <u>$row->transaction_title</u></strong></div>";
                $info_str .= "<div><strong>SOURCE: </strong>$row->seed_type, $row->source</div>";
                $info_str .= "<div><strong>BAGS TRANSFERRED: </strong>$row->total_bags bag(s)</div>";
                $info_str .= "<div><strong>PLACE OF ORIGIN: </strong>".strtoupper($row->from_province)." < ".strtoupper($row->from_municipality)." < ".strtoupper($row->from_dop)."</div>";
                $info_str .= "<div><strong>DESTINATION: </strong>".strtoupper($row->to_province)." < ".strtoupper($row->to_municipality)." < ".strtoupper($row->to_dop)."</div>";
                $info_str .= "<div><strong>ASSIGNED PC: </strong>".$row->from_assigned_pc."</div>";
                $info_str .= "<div><strong>TRANSFER DATE: </strong>".date("F j, Y", strtotime($row->to_transfer_date))."</div>";                
                $info_str .= "<div><strong>DATE RECORDED: </strong>".date("F j, Y g:i A", strtotime($row->date_recorded))."</div>";
            }

            return $info_str;
        })
        ->addColumn('status_col', function($row){
            $delivery_status = "";
            $document_status = "";
            $document_details = "";
            $edit_satus_btn = "";

            if($row->delivery_status == 0){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-dark">Delivery Status: N/A</span><br>';
            }else if($row->delivery_status == 1){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-warning">Delivery Status: On Process</span><br>';
            }else if($row->delivery_status == 2){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-success">Delivery Status: Paid</span><br>';
            }else if($row->delivery_status == 3){
                $delivery_status .= '<span style="font-size: 12px;border-radius: 10px;" class="badge badge-danger">Delivery Status: Unpaid</span><br>';
            }

            if($row->document_status == 0){
                $document_status .= '<span style="font-size: 12px;border-radius: 10px;margin-top: 5px;" class="badge badge-warning">Document status: not submitted</span><br>';
                $document_details = "<textarea style='width:100%;margin-top: 10px;' class='form-control' rows='3' disabled>N/A</textarea>";
            }else if($row->document_status == 1){
                $document_status .= '<span style="font-size: 12px;border-radius: 10px;margin-top: 5px;" class="badge badge-success">Document status: submitted</span><br>';
                $document_details = "<textarea style='width:100%;margin-top: 10px;' class='form-control' rows='3' disabled>$row->document_status_remarks</textarea>";
            }

            if(Auth::user()->roles->first()->name == "rcef-pmo"){
                $edit_satus_btn .= "<button data-id='$row->transaction_code' data-toggle='modal' data-target='#show_status_modal' class='btn btn-success btn-sm btn-block' style='margin-top: 5px;'><i class='fa fa-edit'></i> Edit status</button>";
                return $delivery_status.$document_status.$document_details.$edit_satus_btn;
            }else{
                $edit_satus_btn .= "<button class='btn btn-success btn-sm btn-block' style='margin-top: 5px;' disabled><i class='fa fa-edit'></i> Edit status</button>";
                return $delivery_status.$document_status.$document_details;
            }

            
        })
        ->addColumn('action', function($row){
            if($row->edit_final_flag == 1){
                //$view_btn   = "<a href='".route('rcep.google_sheet.view', $row->transaction_code)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-eye'></i> VIEW SCHEDULE</a><br>";
                $view_btn   = "<button class='btn btn-default btn-sm' style='border-radius:20px;'><i class='fa fa-lock'></i> EDIT NOT AVAILABLE</button>";
                
                $check_actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->where('transaction_code', $row->transaction_code)->first();
                if(count($check_actual) > 0){
                    $actual_btn = "<button class='btn btn-default btn-sm' style='border-radius:20px;'><i class='fa fa-lock'></i> FINALIZED REPORT</button>";
                }else{
                    $actual_btn = "<a href='".route('rcep.google_sheet.actual', $row->transaction_code)."' class='btn btn-danger btn-sm' style='border-radius:20px;'><i class='fa fa-check-circle'></i> SEND ACTUAL DATA</a>";
                }
                
                return $view_btn.$actual_btn;
            }else{
                $view_btn = "<a href='".route('rcep.google_sheet.view', $row->transaction_code)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-eye'></i> VIEW SCHEDULE</a>";
                $save_btn = "<a href='".route('rcep.google_sheet.save_as_final', $row->transaction_code)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-check'></i> FINALIZE SCHEDULE</a>";
                
                return $view_btn.$save_btn;
            }            
        })
        ->make(true);
    }


    public function get_variety_id($variety_name){
        return DB::table('seed_seed.seed_characteristics')
            ->where('variety', $variety_name)
            ->groupBy('variety')
            ->value('id');
    }

    public function get_schedule_view($transaction_id){
        //get shedule details and return corresponding view
        $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $transaction_id)
            ->groupBy('transaction_code')
            ->first();
        
        if($schedule_details->seed_type == "NEW" AND $schedule_details->source == "SEED_COOP" OR
           $schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "SEED_COOP" OR
           $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "SEED_COOP"){
               
            $cooperatives  = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('station_id', Auth::user()->stationId)->groupBy('province')->orderBy('province')->get();

            $to_municipality_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $schedule_details->to_province)
                ->where('municipality', $schedule_details->to_municipality)
                ->value('current_balance');

            return view('reports.google.schedule.views.view_new_seedCoop')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('status', $schedule_details->status  )
                ->with('cooperatives', $cooperatives)
                ->with('provinces', $province)
                ->with('schedule_coop', $schedule_details->from_coop_accreditation)
                ->with('schedule_bagsForDelivery', $schedule_details->from_bags_total)
                ->with('schedule_to_province', $schedule_details->to_province)
                ->with('schedule_to_municipality', $schedule_details->to_municipality)
                ->with('schedule_to_municipality_balance', $to_municipality_balance)
                ->with('schedule_to_dop', $schedule_details->to_dop)
                ->with('schedule_to_delivery_date', $schedule_details->to_delivery_date)
                ->with('schedule_to_assigned_pc', $schedule_details->to_assigned_pc)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_title', $schedule_details->transaction_title);
        
        }else if($schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "PHILRICE_WAREHOUSE" OR
                 $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "PHILRICE_WAREHOUSE"){

            $cooperatives  = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('station_id', Auth::user()->stationId)->groupBy('province')->orderBy('province')->get();

            $from_municipality_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $schedule_details->from_province)
                ->where('municipality', $schedule_details->from_municipality)
                ->value('current_balance');

            $to_municipality_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $schedule_details->to_province)
                ->where('municipality', $schedule_details->to_municipality)
                ->value('current_balance');

            return view('reports.google.schedule.views.view_inventory_warehouse')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('status', $schedule_details->status)
                ->with('cooperatives', $cooperatives)
                ->with('provinces', $province)
                ->with('schedule_coop', $schedule_details->from_coop_accreditation)
                ->with('schedule_bagsForDelivery', $schedule_details->from_bags_total)
                ->with('schedule_from_province', $schedule_details->from_province)
                ->with('schedule_from_municipality', $schedule_details->from_municipality)
                ->with('schedule_from_municipality_balance', $from_municipality_balance)
                ->with('shceduled_from_dop', $schedule_details->from_dop)
                ->with('schedule_to_province', $schedule_details->to_province)
                ->with('schedule_to_municipality', $schedule_details->to_municipality)
                ->with('schedule_to_municipality_balance', $to_municipality_balance)
                ->with('schedule_to_dop', $schedule_details->to_dop)
                ->with('schedule_to_delivery_date', $schedule_details->to_delivery_date)
                ->with('schedule_to_assigned_pc', $schedule_details->to_assigned_pc)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_title', $schedule_details->transaction_title);

        }else if($schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "LGU_STOCKS" OR
                 $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "LGU_STOCKS"){
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
            //$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('station_id', Auth::user()->stationId)->groupBy('province')->orderBy('province')->get();
			
            return view('reports.google.schedule.views.view_inventory_lgu')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('status', $schedule_details->status)
                ->with('provinces', $province)
                ->with('schedule_bagsInLgu', $schedule_details->from_bags_total)
                ->with('schedule_from_province', $schedule_details->from_province)
                ->with('schedule_from_municipality', $schedule_details->from_municipality)
                ->with('shceduled_from_dop', $schedule_details->from_dop)
                ->with('shcedule_from_assigned_pc', $schedule_details->from_assigned_pc)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_title', $schedule_details->transaction_title);
        
        }else if($schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "TRANSFERRED_SEEDS" OR 
                 $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "TRANSFERRED_SEEDS" OR
                 $schedule_details->seed_type == "NEW" AND $schedule_details->source == "TRANSFERRED_SEEDS"){
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('station_id', Auth::user()->stationId)->groupBy('province')->orderBy('province')->get();
            $from_municipality_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $schedule_details->from_province)
                ->where('municipality', $schedule_details->from_municipality)
                ->value('current_balance');

            $to_municipality_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $schedule_details->to_province)
                ->where('municipality', $schedule_details->to_municipality)
                ->value('current_balance');

            return view('reports.google.schedule.views.view_inventory_transferred')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('status', $schedule_details->status)
                ->with('provinces', $province)
                ->with('schedule_bagsForTransfer', $schedule_details->from_bags_total)
                ->with('schedule_from_province', $schedule_details->from_province)
                ->with('schedule_from_municipality', $schedule_details->from_municipality)
                ->with('schedule_from_municipality_balance', $from_municipality_balance)
                ->with('shceduled_from_dop', $schedule_details->from_dop)
                ->with('schedule_to_province', $schedule_details->to_province)
                ->with('schedule_to_municipality', $schedule_details->to_municipality)
                ->with('schedule_to_municipality_balance', $to_municipality_balance)
                ->with('schedule_to_dop', $schedule_details->to_dop)
                ->with('schedule_to_transfer_date', $schedule_details->to_transfer_date)
                ->with('schedule_to_assigned_pc', $schedule_details->to_assigned_pc)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_title', $schedule_details->transaction_title);
        }
    }



    public function get_schedule_actual($transaction_id){
        //get shedule details and return corresponding view
        $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $transaction_id)
            ->groupBy('transaction_code')
            ->first();

        if($schedule_details->seed_type == "NEW" AND $schedule_details->source == "SEED_COOP" OR
            $schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "SEED_COOP" OR
            $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "SEED_COOP"){
                
            $cooperatives  = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
            $seed_varities = DB::select( DB::raw("SELECT * FROM seed_seed.seed_characteristics GROUP BY variety ORDER BY variety_name"));
            //dd($seed_varities);

            return view('reports.google.actual.actual_new_seedCoop')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('seed_varities', $seed_varities)
                ->with('status', $schedule_details->status)
                ->with('cooperatives', $cooperatives)
                ->with('provinces', $province)
                ->with('schedule_coop', $schedule_details->from_coop_accreditation)
                ->with('shedule_coop_name', $schedule_details->from_coop)
                ->with('schedule_bagsForDelivery', $schedule_details->from_bags_total)
                ->with('schedule_to_province', $schedule_details->to_province)
                ->with('schedule_to_municipality', $schedule_details->to_municipality)
                ->with('schedule_to_dop', $schedule_details->to_dop)
                ->with('schedule_to_delivery_date', $schedule_details->to_delivery_date)
                ->with('schedule_to_assigned_pc', $schedule_details->to_assigned_pc)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_title', $schedule_details->transaction_title);
        
        }else if($schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "PHILRICE_WAREHOUSE" OR
            $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "PHILRICE_WAREHOUSE"){
            
            $cooperatives  = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->orderBy('coopName')->get();
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
			//$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
            $seed_varities = DB::select( DB::raw("SELECT * FROM seed_seed.seed_characteristics GROUP BY variety ORDER BY variety_name"));
            //dd($seed_varities);

            return view('reports.google.actual.actual_inventory_warehouse')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('seed_varities', $seed_varities)
                ->with('status', $schedule_details->status)
                ->with('cooperatives', $cooperatives)
                ->with('provinces', $province)
                ->with('schedule_coop', $schedule_details->from_coop_accreditation)
                ->with('shedule_coop_name', $schedule_details->from_coop)
                ->with('schedule_bagsForDelivery', $schedule_details->from_bags_total)
                ->with('schedule_from_province', $schedule_details->from_province)
                ->with('schedule_from_municipality', $schedule_details->from_municipality)
                ->with('shcedule_from_dop', $schedule_details->from_dop)
                ->with('schedule_to_province', $schedule_details->to_province)
                ->with('schedule_to_municipality', $schedule_details->to_municipality)
                ->with('schedule_to_dop', $schedule_details->to_dop)
                ->with('schedule_to_delivery_date', $schedule_details->to_delivery_date)
                ->with('schedule_to_assigned_pc', $schedule_details->to_assigned_pc)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_title', $schedule_details->transaction_title);

        }else if($schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "LGU_STOCKS" OR
            $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "LGU_STOCKS"){
            
            $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
            $province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
            $seed_varities = DB::select( DB::raw("SELECT * FROM seed_seed.seed_characteristics GROUP BY variety ORDER BY variety_name"));

            return view('reports.google.actual.actual_inventory_lgu')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('status', $schedule_details->status)
                ->with('seed_varities', $seed_varities)
                ->with('provinces', $province)
                ->with('shedule_coop_name', $schedule_details->from_coop)
                ->with('schedule_bagsInLgu', $schedule_details->from_bags_total)
                ->with('schedule_from_province', $schedule_details->from_province)
                ->with('schedule_from_municipality', $schedule_details->from_municipality)
                ->with('shcedule_from_dop', $schedule_details->from_dop)
                ->with('shcedule_from_assigned_pc', $schedule_details->from_assigned_pc)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_title', $schedule_details->transaction_title);
            
        }else if($schedule_details->seed_type == "INVENTORY_WS" AND $schedule_details->source == "TRANSFERRED_SEEDS" OR
            $schedule_details->seed_type == "INVENTORY_DS" AND $schedule_details->source == "TRANSFERRED_SEEDS" OR
            $schedule_details->seed_type == "NEW" AND $schedule_details->source == "TRANSFERRED_SEEDS"){ 
            
            //$province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
			$province = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->groupBy('province')->orderBy('province')->get();
            $seed_varities = DB::select( DB::raw("SELECT * FROM seed_seed.seed_characteristics GROUP BY variety ORDER BY variety_name"));

            return view('reports.google.actual.actual_inventory_transferred')
                ->with('source', $schedule_details->seed_type)
                ->with('seed_type', $schedule_details->seed_type)
                ->with('status', $schedule_details->status)
                ->with('seed_varities', $seed_varities)
                ->with('provinces', $province)
                ->with('schedule_bagsForTransfer', $schedule_details->from_bags_total)
                ->with('schedule_from_province', $schedule_details->from_province)
                ->with('schedule_from_municipality', $schedule_details->from_municipality)
                ->with('shcedule_from_dop', $schedule_details->from_dop)
                ->with('schedule_to_province', $schedule_details->to_province)
                ->with('schedule_to_municipality', $schedule_details->to_municipality)
                ->with('schedule_to_dop', $schedule_details->to_dop)
                ->with('schedule_to_transfer_date', $schedule_details->to_transfer_date)
                ->with('schedule_to_assigned_pc', $schedule_details->to_assigned_pc)
                ->with('schedule_edit_draft_flag', $schedule_details->edit_draft_flag)
                ->with('schedule_edit_final_flag', $schedule_details->edit_final_flag)
                ->with('schedule_transaction_code', $schedule_details->transaction_code)
                ->with('schedule_title', $schedule_details->transaction_title);
        }
    }

    /********************************************************************************************************************** */

    public function generate_all_months_in_year(){
        $month_arr = array();
        for ($m=1; $m<=12; $m++) {
            $month = date('F', mktime(0,0,0,$m, 1, date('Y')));
            array_push($month_arr, array(
                "month_id" => $m,
                "month_name" => $month
            ));
        }

        return $month_arr;
    }

    public function dashboard(){
        $station_list = DB::table('geotag_db2.tbl_station')->get();
        $scheduled = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->select(DB::raw('SUM(from_bags_total) as total_bags'))
            ->where('status', 'APPROVED')
            ->first();
        $total_bags_scheduled = $scheduled->total_bags;

        $actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
            ->select(DB::raw('SUM(total_bags) as total_actual_bags'))
            ->first();

        /*$reported_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_national')
            ->select(DB::raw('SUM(total_bags) as total_bags_distributed'),
                DB::raw('SUM(total_farmers) as total_farmer_beneficiaries'),
                DB::raw('SUM(total_actual_area) as total_area_planted'))
            ->first();*/
        $month = date("Y-m");
        $reported_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')
        ->select(DB::raw('SUM(total_bags) as total_bags_distributed'),
            DB::raw('SUM(total_farmers) as total_farmer_beneficiaries'),
            DB::raw('SUM(total_actual_area) as total_area_planted'))
        ->where('date_generated', '>=',$month."-01") //FROM
        ->where('date_generated', '<=',$month."-31") //TO
        ->first();

        $months = $this->generate_all_months_in_year();
            
        return view('reports.google.dashboard.home')
            ->with('station_list', $station_list)
            ->with('total_bags_scheduled', number_format($total_bags_scheduled))
            ->with('total_actual_bags', number_format($actual->total_actual_bags))

            ->with('reported_total_bags', number_format($reported_data->total_bags_distributed))
            ->with('reported_total_beneficiaries', number_format($reported_data->total_farmer_beneficiaries))
            ->with('reported_area_planted', number_format($reported_data->total_area_planted,2,".",","))

            ->with('months', $months);
    }
	
	function weeks_in_month($month, $year) {
		 // Start of month
		 $start = mktime(0, 0, 0, $month, 1, $year);
		 // End of month
		 $end = mktime(0, 0, 0, $month, date('t', $start), $year);
		 // Start week
		 $start_week = date('W', $start);
		 // End week
		 $end_week = date('W', $end);
		 
		 if ($end_week < $start_week) { // Month wraps
					//year has 52 weeks
					$weeksInYear = 52;
					//but if leap year, it has 53 weeks
					if($year % 4 == 0) {
						$weeksInYear = 53;
					}
					return (($weeksInYear + $end_week) - $start_week) + 1;
				}
		 
		 return ($end_week - $start_week) + 1;
	}

    function generate_dashboard_chart(Request $request){
        $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('status', 'APPROVED')->groupBy('source')->get();
        //dd($schedule_data);
        $category_arr = array();
        $schedule_arr = array();
        $actual_arr   = array();
        foreach($schedule_data as $row){
            $category_text = "";
            if($row->source == "LGU_STOCKS"){
                $category_text = "Stocks in LGU";
            }else if($row->source == "PHILRICE_WAREHOUSE"){
                $category_text = "PhilRice Warehouse";
            }else if($row->source == "SEED_COOP"){
                $category_text = "Seed Cooperative";
            }else if($row->source == "TRANSFERRED_SEEDS"){
                $category_text = "Transferred Seeds";
            }

            array_push($category_arr, $category_text);

            $schedule_list = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_total) as total_bags'), 'transaction_code')
                ->where('source', $row->source)
                ->groupBy('transaction_code')
                ->get();

            $schedule_total_value = 0;
            $actual_total_value = 0;
            foreach($schedule_list as $list){
                $schedule_total_value_in_bags = (int)$list->total_bags;
                $schedule_total_value += $schedule_total_value_in_bags;

                $actual_total_value += DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                    ->where('transaction_code', $list->transaction_code)
                    ->groupBy('transaction_code')
                    ->sum('total_bags');
            }

            array_push($schedule_arr, $schedule_total_value);
            array_push($actual_arr, (int)$actual_total_value);
        }

        //get data of current month
        $month_name = date("F");
        $month_index = date("m", strtotime($month_name));
        $week_bags = array();
        $week_farmers = array();
        $week_area = array();
		$week_label = array();
		
		$total_weeks_in_month = $this->weeks_in_month($month_index, date("Y"));
		        
        for($i=1;$i<=$total_weeks_in_month;$i++){
            $week_dates = $this->getFirstandLastDate(date("Y"), $month_index, $i);
            $week_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')
                ->where('date_generated', '>=',$week_dates["week_start"]) //FROM
                ->where('date_generated', '<=',$week_dates["week_end"]) //TO
                ->get();

            $week_total_bags = 0;
            $week_total_farmers = 0;
            $week_total_area = 0;
            foreach($week_data as $week_row){
                $week_total_bags += $week_row->total_bags;
                $week_total_farmers += $week_row->total_farmers;
                $week_total_area += $week_row->total_actual_area;
            }
                
            array_push($week_bags, $week_total_bags);
            array_push($week_farmers, $week_total_farmers);
            array_push($week_area, $week_total_area);
			array_push($week_label, "Week ".$i);
        }


        return array(
            "category_chart" => $category_arr,
            "category_schedule" => $schedule_arr,
            "category_actual" => $actual_arr,

			"week_label" => $week_label,
            "week_bags" => $week_bags,
            "week_farmers" => $week_farmers,
            "week_area" => $week_area,
        );
    }

    function getFirstandLastDate($year, $month, $week) {

        $thisWeek = 1;
    
        for($i = 1; $i < $week; $i++) {
            $thisWeek = $thisWeek + 7;
        }
    
        $currentDay = date('Y-m-d',mktime(0,0,0,$month,$thisWeek,$year));
    
        $monday = strtotime('monday this week', strtotime($currentDay));
        $sunday = strtotime('sunday this week', strtotime($currentDay));
    
        $weekStart = date('Y-m-d 00:00:00', $monday);
        $weekEnd = date('Y-m-d 23:59:59', $sunday);
        $month_name = date("F", strtotime($currentDay));
    
        //return $weekStart . ' - ' . $weekEnd;
        return array(
            "week_start" => $weekStart,
            "week_end" => $weekEnd,
            "month_name" => $month_name
        );
    }

    public function dashboard_filtered(Request $request){
        $month = $request->week_month;
        $week = $request->week_number;
        $year = date("Y");

        //dd($week_dates["week_start"]);

        /**
         * SEARCH QUERY
         */
        if($month == "ALL"){
            $scheduled = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('status', 'APPROVED')
                ->first();
            $total_bags_scheduled = $scheduled->total_bags;

            $actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                ->select(DB::raw('SUM(total_bags) as total_actual_bags'))
                ->first();

            $current_month = date("F");

            /*$reported_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_national')
                ->select(DB::raw('SUM(total_bags) as total_bags_distributed'),
                    DB::raw('SUM(total_farmers) as total_farmer_beneficiaries'),
                    DB::raw('SUM(total_actual_area) as total_area_planted'))
                ->where('current_month', $current_month)
                ->first();*/
            $month = date("Y-m");
            $reported_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')
            ->select(DB::raw('SUM(total_bags) as total_bags_distributed'),
                DB::raw('SUM(total_farmers) as total_farmer_beneficiaries'),
                DB::raw('SUM(total_actual_area) as total_area_planted'))
            ->where('date_generated', '>=',$month."-01") //FROM
            ->where('date_generated', '<=',$month."-31") //TO
            ->first();

            return array(
                "total_bags_scheduled" => number_format($total_bags_scheduled),
                "total_actual_bags" => number_format($actual->total_actual_bags),
                "reported_total_bags" => number_format($reported_data->total_bags_distributed),
                "reported_total_beneficiaries" => number_format($reported_data->total_farmer_beneficiaries),
                "reported_area_planted" => number_format($reported_data->total_area_planted,2,".",",")
            );
            
        }else{
            $week_dates = $this->getFirstandLastDate($year, $month, $week);
            $scheduled = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                ->where('status', 'APPROVED')
                ->first();
            $total_bags_scheduled = $scheduled->total_bags;

            $actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                ->select(DB::raw('SUM(total_bags) as total_actual_bags'))
                ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                ->first();

            $reported_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')
                ->select(DB::raw('SUM(total_bags) as total_bags_distributed'),
                    DB::raw('SUM(total_farmers) as total_farmer_beneficiaries'),
                    DB::raw('SUM(total_actual_area) as total_area_planted'))
                ->where('date_generated', '>=',$week_dates["week_start"]) //FROM
                ->where('date_generated', '<=',$week_dates["week_end"]) //TO
                ->first();

            return array(
                "total_bags_scheduled" => number_format($total_bags_scheduled),
                "total_actual_bags" => number_format($actual->total_actual_bags),
                "reported_total_bags" => number_format($reported_data->total_bags_distributed),
                "reported_total_beneficiaries" => number_format($reported_data->total_farmer_beneficiaries),
                "reported_area_planted" => number_format($reported_data->total_area_planted,2,".",",")
            );
        }
    }

    public function category_filtered(Request $request){
        $month = $request->week_month;
        $week = $request->week_number;
        $year = date("Y");
        $week_dates = $this->getFirstandLastDate($year, $month, $week);

        $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
            ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
            ->where('status', 'APPROVED')
            ->groupBy('source')
            ->get();

        //dd($schedule_data);
        $category_arr = array();
        $schedule_arr = array();
        $actual_arr   = array();
        foreach($schedule_data as $row){
            $category_text = "";
            if($row->source == "LGU_STOCKS"){
                $category_text = "Stocks in LGU";
            }else if($row->source == "PHILRICE_WAREHOUSE"){
                $category_text = "PhilRice Warehouse";
            }else if($row->source == "SEED_COOP"){
                $category_text = "Seed Cooperative";
            }else if($row->source == "TRANSFERRED_SEEDS"){
                $category_text = "Transferred Seeds";
            }

            array_push($category_arr, $category_text);

            $schedule_list = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_total) as total_bags'), 'transaction_code')
                ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                ->where('source', $row->source)
                ->groupBy('transaction_code')
                ->get();

            $schedule_total_value = 0;
            $actual_total_value = 0;
            foreach($schedule_list as $list){
                $schedule_total_value_in_bags = (int)$list->total_bags;
                $schedule_total_value += $schedule_total_value_in_bags;

                $actual_total_value += DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                    ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                    ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                    ->where('transaction_code', $list->transaction_code)
                    ->groupBy('transaction_code')
                    ->sum('total_bags');
            }

            array_push($schedule_arr, $schedule_total_value);
            array_push($actual_arr, (int)$actual_total_value);
        }

        //get data of current month
        $month = date('F');
        $month_name = date("F");
        $month_index = date("m", strtotime($month_name));
        $week_bags = array();
        $week_farmers = array();
        $week_area = array();
		$week_label = array();
		
		$total_weeks_in_month = $this->weeks_in_month($month_index, date("Y"));
        
        for($i=1;$i<=$total_weeks_in_month;$i++){

            $week_dates = $this->getFirstandLastDate(date("Y"), $month_index, $i);
            $week_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')
                ->where('date_generated', '>=',$week_dates["week_start"]) //FROM
                ->where('date_generated', '<=',$week_dates["week_end"]) //TO
                ->get();

            $week_total_bags = 0;
            $week_total_farmers = 0;
            $week_total_area = 0;
            foreach($week_data as $week_row){
                $week_total_bags += $week_row->total_bags;
                $week_total_farmers += $week_row->total_farmers;
                $week_total_area += $week_row->total_actual_area;
            }
                
            array_push($week_bags, $week_total_bags);
            array_push($week_farmers, $week_total_farmers);
            array_push($week_area, $week_total_area);
			array_push($week_label, "Week ".$i);
        }

        return array(
            "category_chart" => $category_arr,
            "category_schedule" => $schedule_arr,
            "category_actual" => $actual_arr,

			"week_label" => $week_label,
            "week_bags" => $week_bags,
            "week_farmers" => $week_farmers,
            "week_area" => $week_area,
        );
    }
    
    /********************************************************************************************************************** */

    function get_current_week(){
        if(date('D')!='Mon'){    
            $staticstart = date('Y-m-d',strtotime('last Monday'));
            $filter_start = date('m-d-Y',strtotime('last Monday'));
        }else{
            $staticstart = date('Y-m-d');
            $filter_start = date('m-d-Y');   
        }

        if(date('D')!='Sun'){
            $staticfinish = date('Y-m-d',strtotime('next Saturday'));
            $filter_end = date('m-d-Y',strtotime('next Saturday'));
        }else{
            $staticfinish = date('Y-m-d');
            $filter_end = date('m-d-Y'); 
        }

        return array(
            'start' => $staticstart,
            'end' => $staticfinish,
            'filter_start' => $filter_start,
            'filter_end' => $filter_end
        );
    }

    public function view_weekly_ui_home(){
        $current_week = $this->get_current_week();
        $report_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
            ->select(DB::raw('SUM(bags_distributed) as total_bags_distributed'),
                DB::raw('SUM(farmer_beneficiaries) as total_farmer_beneficiaries'),
                DB::raw('SUM(area_planted) as total_area_planted'))
            ->first();

        $stations = DB::table('geotag_db2.tbl_station')->get();
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')->groupBy('region')->orderBy('region')->get();

        return view('reports.google.weekly.list_home')
            ->with('filter_start', $current_week['filter_start'])
            ->with('filter_end', $current_week['filter_end'])
            ->with('stations', $stations)
            ->with('regions', $regions)
            ->with('total_bags_distributed', number_format($report_details->total_bags_distributed))
            ->with('total_farmer_beneficiaries', number_format($report_details->total_farmer_beneficiaries))
            ->with('total_area_planted', number_format($report_details->total_area_planted,2,".",","));
    }

    public function view_weekly_ui(){
        $current_week = $this->get_current_week();
        $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
        $station_name = DB::table('geotag_db2.tbl_station')->where('stationId', Auth::user()->stationId)->value('stationName');

        $station_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
            ->select(DB::raw('SUM(bags_distributed) as total_bags_distributed'),
                DB::raw('SUM(farmer_beneficiaries) as total_farmer_beneficiaries'),
                DB::raw('SUM(area_planted) as total_area_planted'))
            ->where('station_id', Auth::user()->stationId)
            ->first();

        return view('reports.google.weekly.home')
            ->with('filter_start', $current_week['filter_start'])
            ->with('filter_end', $current_week['filter_end'])
            ->with('provinces', $province)
            ->with('station_name', $station_name)
            ->with('total_bags_distributed', number_format($station_details->total_bags_distributed))
            ->with('total_farmer_beneficiaries', number_format($station_details->total_farmer_beneficiaries))
            ->with('total_area_planted', number_format($station_details->total_area_planted,2,".",","));        
    }

    public function weekly_report_tbl(Request $request){
        if($request->search_filter == "ALL"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report');
        }else if($request->search_filter == "STATION_FILTER"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')->where('station_id', $request->station);
        }else if($request->search_filter == "DATE_RANGE_FILTER"){
            $date_range_str = explode(" - ", $request->date_range);
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
                ->where('date_from', '>=', $date_range_str[0])
                ->where('date_to', '<=', $date_range_str[1]);
        }else if($request->search_filter == "REGION_FILTER"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')->where('region', $request->region);
        }else if($request->search_filter == "PROVINCE_FILTER"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
                ->where('province', $request->province)
                ->where('region', $request->region);
        }else if($request->search_filter == "MUNICIPALITY_FILTER"){
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
                ->where('province', $request->province)
                ->where('region', $request->region)
                ->where('municipality', $request->municipality);

        }else if($request->search_filter == "ALL_FILTER"){
            $date_range_str = explode(" - ", $request->date_range);
            $query = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
                ->where('station_id', $request->station)
                ->where('date_from', '>=', $date_range_str[0])
                ->where('date_to', '<=', $date_range_str[1])
                ->where('province', $request->province)
                ->where('region', $request->region)
                ->where('municipality', $request->municipality);
        }

        return Datatables::of($query)
            ->addColumn('report_col', function($row){
                $station_name = DB::table('geotag_db2.tbl_station')->where('stationId', $row->station_id)->value('stationName');

                $date_from = date("F j, Y", strtotime($row->date_from));
                $date_to = date("F j, Y", strtotime($row->date_to));

                $total_bags_distributed = number_format($row->bags_distributed);
                $total_farmer_beneficiaries = number_format($row->farmer_beneficiaries);
                $total_area_planted = number_format($row->area_planted);

                $report_str = "";
                $report_str .= "<div style='font-size:18px;'><strong><u>$station_name</u></strong></div>";
                $report_str .= "<div><strong>AREA LOCATION: </strong>$row->region < $row->province < $row->municipality</div>";
                $report_str .= "<div><strong>DATE (FROM & TO): </strong>$date_from - $date_to</div>";
                $report_str .= "<div><strong>REPORT DETAILS: </strong>$total_bags_distributed bag(s), $total_farmer_beneficiaries farmer(s), $total_area_planted (ha)</div>";
                $report_str .= "<div><strong>AUTHOR: </strong>$row->created_by</div>";
                
                return $report_str;
            })
            ->addColumn('action', function($row){
                $current_week = $this->get_current_week();
                $date_from = $current_week['start']." 00:00:00";
                $date_to = $current_week['end']." 23:59:59";
                $date_recorded = $row->date_recorded;

                if($date_recorded >= $date_from AND $date_recorded <= $date_to){
                    return "<a href='".route('rcep.google_sheet.weekly.edit', $row->id)."' class='btn btn-warning btn-sm' style='border-radius:20px;'><i class='fa fa-edit'></i> EDIT REPORT</a>";
                }else{
                    return "<button class='btn btn-default btn-sm' style='border-radius:20px;'><i class='fa fa-lock'></i> LOCKED REPORT</button>";
                }
                           
            })
        ->make(true);
    }

    public function get_weeklyReport_provinces(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
            ->where('region', $request->region)
            ->groupBy('province')
            ->orderBy('province')
            ->get();

		$return_str = ""; 
		$return_str .= "<option value='0'>Please select a province</option>";
		foreach($provinces as $row){
			$return_str .= "<option value='".$row->province."'>$row->province</option>";
		}

		return $return_str;
    }

    public function get_weeklyReport_municipalities(Request $request){
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();

		$return_str = ""; 
		$return_str .= "<option value='0'>Please select a province</option>";
		foreach($regions as $row){
			$return_str .= "<option value='".$row->municipality."'>$row->municipality</option>";
		}

		return $return_str;
    }
	
	public function get_weeklyReport_DOPmunicipalities(Request $request){
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();

		$return_str = ""; 
		$return_str .= "<option value='0'>Please select a province</option>";
		foreach($regions as $row){
			$return_str .= "<option value='".$row->municipality."'>$row->municipality</option>";
		}

		return $return_str;
    }

    public function weekly_report_edit($id){
        $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('province')->get();
        $station_name = DB::table('geotag_db2.tbl_station')->where('stationId', Auth::user()->stationId)->value('stationName');

        $station_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
            ->select(DB::raw('SUM(bags_distributed) as total_bags_distributed'),
                DB::raw('SUM(farmer_beneficiaries) as total_farmer_beneficiaries'),
                DB::raw('SUM(area_planted) as total_area_planted'))
            ->where('station_id', Auth::user()->stationId)
            ->first();

        $report_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')->where('id', $id)->first();

        return view('reports.google.weekly.list_edit')
            ->with('provinces', $province)
            ->with('station_name', $station_name)
            ->with('total_bags_distributed', number_format($station_details->total_bags_distributed))
            ->with('total_farmer_beneficiaries', number_format($station_details->total_farmer_beneficiaries))
            ->with('total_area_planted', number_format($station_details->total_area_planted,2,".",","))
            ->with('report_province', $report_details->province)
            ->with('report_municipality', $report_details->municipality)
            ->with('report_date_from', $report_details->date_from)
            ->with('report_date_to', $report_details->date_to)
            ->with('report_bags_distributed', $report_details->bags_distributed)
            ->with('report_farmer_beneficiaries', $report_details->farmer_beneficiaries)
            ->with('report_area_planted', $report_details->area_planted)
            ->with('report_id', $id);
    }

    public function export_schedule($month, $week, $category){
        $year = date("Y");
        $list = array();
        if($month != "ALL"){
            $week_dates = $this->getFirstandLastDate($year, $month, $week);
        }

        if($category == "SEED_COOP"){
            if($month == "ALL"){
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 'from_coop',
                        DB::raw('SUM(from_bags_delivered) as total_bags'), 'to_province', 'to_municipality', 'to_dop',
                        'to_delivery_date', 'date_recorded')
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }else{
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 'from_coop',
                        DB::raw('SUM(from_bags_delivered) as total_bags'), 'to_province', 'to_municipality', 'to_dop',
                        'to_delivery_date', 'date_recorded')
                    ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                    ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }
            

            foreach($schedule_data as $row){
                //get actual data - total bags
                $actual_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                    ->select(DB::raw('SUM(total_bags) as total_bags'))
                    ->where('transaction_code', $row->transaction_code)
                    ->where('source', $row->source)
                    ->first();

                array_push($list, array(
                    "transaction_title" => $row->transaction_title,
                    "transaction_code" => $row->transaction_code,
                    "category" => $row->seed_type.", ".$row->source,
                    "status" => $row->status,
                    "from_coop" => $row->from_coop,
                    "schedule_total_bags" => (int)$row->total_bags,
                    "actual_total_bags" => $actual_data->total_bags == 0 ? "0" : $actual_data->total_bags,
                    "to_province" => $row->to_province,
                    "to_municipality" => $row->to_municipality,
                    "to_dop" => $row->to_dop,
                    "to_delivery_date" => $row->to_delivery_date,
                    "date_recorded" => $row->date_recorded
                )); 
            }

            $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
            return Excel::create("SCHEDULE_"."$category"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("SCHEDULE LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        'Transaction Title', "Transaction Code", "Category", "Status", "Seed Cooperative", "Total Bags (SCHEDULE)",
                        "Total Bags (ACTUAL)", "Province", "Municipality", "Dropoff Point", "Delivery Date", "Date Recorded"
                    ));
                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');
        
        }else if($category == "PHILRICE_WAREHOUSE"){
            if($month == "ALL"){
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 'from_coop',
                        DB::raw('SUM(from_bags_delivered) as total_bags'), 'from_province', 'from_municipality', 'from_dop',
                        'to_province', 'to_municipality', 'to_dop', 'to_delivery_date', 'date_recorded')
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }else{
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 'from_coop',
                        DB::raw('SUM(from_bags_delivered) as total_bags'), 'from_province', 'from_municipality', 'from_dop',
                        'to_province', 'to_municipality', 'to_dop', 'to_delivery_date', 'date_recorded')
                    ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                    ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }
            
            foreach($schedule_data as $row){
                //get actual data - total bags
                $actual_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                    ->select(DB::raw('SUM(total_bags) as total_bags'))
                    ->where('transaction_code', $row->transaction_code)
                    ->where('source', $row->source)
                    ->first();

                array_push($list, array(
                    "transaction_title" => $row->transaction_title,
                    "transaction_code" => $row->transaction_code,
                    "category" => $row->seed_type.", ".$row->source,
                    "status" => $row->status,
                    "from_coop" => $row->from_coop,
                    "schedule_total_bags" => (int)$row->total_bags,
                    "actual_total_bags" => $actual_data->total_bags == 0 ? "0" : $actual_data->total_bags,
                    "from_province" => $row->from_province,
                    "from_municipality" => $row->from_municipality,
                    "from_dop" => $row->from_dop,
                    "to_province" => $row->to_province,
                    "to_municipality" => $row->to_municipality,
                    "to_dop" => $row->to_dop,
                    "to_delivery_date" => $row->to_delivery_date,
                    "date_recorded" => $row->date_recorded
                )); 
            }

            $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
            return Excel::create("SCHEDULE_"."$category"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("SCHEDULE LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        'Transaction Title', "Transaction Code", "Category", "Status", "Seed Cooperative", "Total Bags (SCHEDULE)",
                        "Total Bags (ACTUAL)", "Province (FROM)", "Municipality (FROM)", "Dropoff Point (FROM)",
                        "Province (TO)", "Municipality (TO)", "DropoffPoint (TO)",  "Delivery Date", "Date Recorded"
                    ));
                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');

        }else if($category == "LGU_STOCKS"){
            if($month == "ALL"){
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 
                        DB::raw('SUM(from_bags_in_lgu) as total_bags'), 'from_province', 'from_municipality', 'from_dop',
                        'from_assigned_pc', 'to_delivery_date', 'date_recorded')
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }else{
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 
                        DB::raw('SUM(from_bags_in_lgu) as total_bags'), 'from_province', 'from_municipality', 'from_dop',
                        'from_assigned_pc', 'to_delivery_date', 'date_recorded')
                    ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                    ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }
            

            foreach($schedule_data as $row){
                //get actual data - total bags
                $actual_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                    ->select(DB::raw('SUM(total_bags) as total_bags'))
                    ->where('transaction_code', $row->transaction_code)
                    ->where('source', $row->source)
                    ->first();

                array_push($list, array(
                    "transaction_title" => $row->transaction_title,
                    "transaction_code" => $row->transaction_code,
                    "category" => $row->seed_type.", ".$row->source,
                    "status" => $row->status,
                    "schedule_total_bags" => (int)$row->total_bags,
                    "actual_total_bags" => $actual_data->total_bags == 0 ? "0" : $actual_data->total_bags,
                    "from_province" => $row->from_province,
                    "from_municipality" => $row->from_municipality,
                    "from_dop" => $row->from_dop,
                    "from_assigned_pc" => $row->from_assigned_pc,
                    "date_recorded" => $row->date_recorded
                )); 
            }

            $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
            return Excel::create("SCHEDULE_"."$category"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("SCHEDULE LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        'Transaction Title', "Transaction Code", "Category", "Status", "Total Bags (SCHEDULE)",
                        "Total Bags (ACTUAL)", "Province", "Municipality", "Dropoff Point",
                        "Assigned PC", "Date Recorded"
                    ));
                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');
        
        }else if($category == "TRANSFERRED_SEEDS"){
            if($month == "ALL"){
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 
                        DB::raw('SUM(from_bags_for_transfer) as total_bags'), 'from_province', 'from_municipality', 'from_dop',
                        'to_province', 'to_municipality', 'to_dop', 'to_assigned_pc', 'to_transfer_date', 'date_recorded')
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }else{
                $schedule_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->select('transaction_title', 'transaction_code', 'seed_type', 'source', 'status', 
                        DB::raw('SUM(from_bags_for_transfer) as total_bags'), 'from_province', 'from_municipality', 'from_dop',
                        'to_province', 'to_municipality', 'to_dop', 'to_assigned_pc', 'to_transfer_date', 'date_recorded')
                    ->where('date_recorded', '>=',$week_dates["week_start"]) //FROM
                    ->where('date_recorded', '<=',$week_dates["week_end"]) //TO
                    ->where('source', $category)
                    ->groupBy('transaction_code')
                    ->get();
            }
            

            foreach($schedule_data as $row){
                //get actual data - total bags
                $actual_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                    ->select(DB::raw('SUM(total_bags) as total_bags'))
                    ->where('transaction_code', $row->transaction_code)
                    ->where('source', $row->source)
                    ->first();

                array_push($list, array(
                    "transaction_title" => $row->transaction_title,
                    "transaction_code" => $row->transaction_code,
                    "category" => $row->seed_type.", ".$row->source,
                    "status" => $row->status,
                    "schedule_total_bags" => (int)$row->total_bags,
                    "actual_total_bags" => $actual_data->total_bags == 0 ? "0" : $actual_data->total_bags,
                    "from_province" => $row->from_province,
                    "from_municipality" => $row->from_municipality,
                    "from_dop" => $row->from_dop,
                    "to_province" => $row->to_province,
                    "to_municipality" => $row->to_municipality,
                    "to_dop" => $row->to_dop,
                    "to_assigned_pc" => $row->to_assigned_pc,
                    "to_transfer_date" => $row->to_transfer_date,
                    "date_recorded" => $row->date_recorded
                )); 
            }

            $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
            return Excel::create("SCHEDULE_"."$category"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("SCHEDULE LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        'Transaction Title', "Transaction Code", "Category", "Status", "Total Bags (SCHEDULE)",
                        "Total Bags (ACTUAL)", "Province (FROM)", "Municipality (FROM)", "Dropoff Point (FROM)",
                        "Province (TO)", "Municipality (TO)", "Dropoff Point (TO)", "Assigned PC", "Date of Transfer",
                        "Date Recorded"
                    ));
                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');
        }            
    }

    /**
     * 10-21-2020 NEW ROUTES 
     */
    public function view_summary(){
        $seed_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->get();
        $seed_array = array();
        foreach($seed_balance as $row){
            //check data from existing seed schedule
            $schedule_delivered = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('to_province', $row->province)
                ->where('to_municipality', $row->municipality)
                ->value('total_bags');

            $actual_distributed = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                ->select(DB::raw('SUM(total_bags) as total_bags'))
                ->where('to_province', $row->province)
                ->where('to_municipality', $row->municipality)
                ->value('total_bags');

            $ongoing_distributed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select('total_bags')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->sum('totalBagCount');

            array_push($seed_array, array(
                "balance_id" => $row->id,
                "station" => $row->station_name,
                "region" => $row->region,
                "province" => $row->province,
                "municipality" => $row->municipality,
                "ds2021_allocated" => number_format($row->ds2021_allocated),
                "schedule_delivered" => (int)$schedule_delivered,
                "actual_distributed" => (int)$actual_distributed,
                "ongoing_distribution" => number_format((int)$ongoing_distributed)
            ));
        }

        return view('reports.google.summary.home')
            ->with('seed_array', $seed_array);
    }

    public function show_more_function(Request $request){
        $seed_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('id', $request->balance_id)->first();
        
        $schedule_delivered = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->select(DB::raw('SUM(from_bags_total) as total_bags'))
            ->where('to_province', $seed_balance->province)
            ->where('to_municipality', $seed_balance->municipality)
            ->value('total_bags');

        $actual_distributed = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
            ->select(DB::raw('SUM(total_bags) as total_bags'))
            ->where('to_province', $seed_balance->province)
            ->where('to_municipality', $seed_balance->municipality)
            ->value('total_bags');

        $ongoing_distributed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select('total_bags')
            ->where('province', $seed_balance->province)
            ->where('municipality', $seed_balance->municipality)
            ->sum('totalBagCount');

        $sms_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('province', $seed_balance->province)
            ->where('municipality', $seed_balance->municipality)
            ->first();

        return array(
            "station" => $seed_balance->station_name,
            "region" => $seed_balance->region,
            "province" => $seed_balance->province,
            "municipality" => $seed_balance->municipality,
            "ds2021_allocated" => number_format($seed_balance->ds2021_allocated),
            "schedule_delivered" => (int)$schedule_delivered,
            "actual_distributed" => (int)$actual_distributed,
            "ongoing_distribution" => number_format((int)$ongoing_distributed),
            "sms_beneficiaries" => number_format((int)$sms_data->total_farmers),
            "sms_bags" => number_format((int)$sms_data->total_farmers),
            "sms_area" => number_format((int)$sms_data->total_actual_area)
        );
    }
	
	public function export_google_summary(){
        $seed_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->get();
        $seed_array = array();
        foreach($seed_balance as $row){
            //check data from existing seed schedule
            $schedule_delivered = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_total) as total_bags'))
                ->where('to_province', $row->province)
                ->where('to_municipality', $row->municipality)
                ->value('total_bags');

            $actual_distributed = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                ->select(DB::raw('SUM(total_bags) as total_bags'))
                ->where('to_province', $row->province)
                ->where('to_municipality', $row->municipality)
                ->value('total_bags');

            $ongoing_distributed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select('total_bags')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->sum('totalBagCount');

            $actual_distributed = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                ->select(DB::raw('SUM(total_bags) as total_bags'))
                ->where('to_province', $row->province)
                ->where('to_municipality', $row->municipality)
                ->value('total_bags');
    
            $sms_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->first();
            
            $sms_beneficiaries = 0;
            $sms_bags = 0;
            $sms_area = 0;
            if(count($sms_data) > 0){
                $sms_beneficiaries = number_format((int)$sms_data->total_farmers);
                $sms_bags = number_format((int)$sms_data->total_farmers);
                $sms_area = number_format((int)$sms_data->total_actual_area);
            }else{
                $sms_beneficiaries = 0;
                $sms_bags = 0;
                $sms_area = 0;
            }

            array_push($seed_array, array(
                "station" => $row->station_name,
                "region" => $row->region,
                "province" => $row->province,
                "municipality" => $row->municipality,
                "ds2021_allocated" => $row->ds2021_allocated == 0 ? '0' : (int)number_format($row->ds2021_allocated),
                "schedule_delivered" => $schedule_delivered == 0 ? '0' : (int)$schedule_delivered,
                "actual_distributed" => $actual_distributed == 0 ? '0' : (int)$actual_distributed,
                "ongoing_distribution" => $ongoing_distributed == 0 ? '0' : number_format((int)$ongoing_distributed),
                "sms_beneficiaries" => $sms_beneficiaries == 0 ? '0' : $sms_beneficiaries,
                "sms_bags" => $sms_bags == 0 ? '0' : $sms_bags,
                "sms_area" => $sms_area == 0 ? '0' : $sms_area
            ));
        }

        $seed_array = json_decode(json_encode($seed_array), true); //convert collection to associative array to be converted to excel
        return Excel::create("GOOGLE_SUMMARY"."_".date("Y-m-d g:i A"), function($excel) use ($seed_array) {
            $excel->sheet("SUMMARY", function($sheet) use ($seed_array) {
                $sheet->fromArray($seed_array, null, 'A1', false, false);
                $sheet->prependRow(1, array(
                    'Station Name', "Region", "Province", "Municipality", "DS2021 Allocated Seeds", 
                    "Delivered / Transferred", "Distributed", 'Ongoing Distribution', 'Farmer Beneficiaries',
                    'Area Planted', 'Delivered / Transferred'
                ));
				$sheet->setHeight(1, 30);
				$sheet->cells('A1:K1', function ($cells) {
					$cells->setBackground('#92D050');
					$cells->setAlignment('center');
					$cells->setValignment('center');
				});
				$sheet->setBorder('A1:K1', 'thin');
                $sheet->freezeFirstRow();
            });
        })->download('xlsx');
    }
	
	public function export_google_schedule(Request $request){
        $schedules = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->get();
        
        $sched_array = array();
        foreach($schedules as $schedule_row){

            $actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
                ->select('*', DB::raw('SUM(total_bags) as total_bags'))
                ->where('transaction_code', $schedule_row->transaction_code)
                ->groupBy('transaction_code')
                ->first();
            if(count($actual) > 0){
                $actual_bags = $actual->total_bags;
            }else{
                $actual_bags = (string)0;
            }

            array_push($sched_array, array(
                "transaction_title" => $schedule_row->transaction_code,
                "transaction_code" => $schedule_row->transaction_code,
                "seed_type" => $schedule_row->seed_type,
                "source" => $schedule_row->source,
                "status" => $schedule_row->status,
                "from_coop" => $schedule_row->from_coop,
                "from_province" => $schedule_row->from_province,
                "from_municipality" => $schedule_row->from_municipality,
                "from_dop" => $schedule_row->from_dop,
                "from_assigned_pc" => $schedule_row->from_assigned_pc,
                "to_province" => $schedule_row->to_province,
                "to_municipality" => $schedule_row->to_municipality,
                "to_dop" => $schedule_row->to_dop,
                "to_assigned_pc" => $schedule_row->to_assigned_pc,
                "from_bags_total" => $schedule_row->from_bags_total,
                "actual_bags_total" => $actual_bags,
                "to_delivery_date" => $schedule_row->to_delivery_date == "0000-00-00" ? "N/A" : date("F j, Y g:i A", strtotime($schedule_row->to_delivery_date)),
                "to_transfer_date" => $schedule_row->to_transfer_date == "0000-00-00" ? "N/A" : date("F j, Y g:i A", strtotime($schedule_row->to_transfer_date)),
                "date_recorded" => date("F j, Y g:i A", strtotime($schedule_row->date_recorded))                 
            )); 
        }

        $excel_data = json_decode(json_encode($sched_array), true); //convert collection to associative array to be converted to excel
        return Excel::create("GOOGLE_TRANSACTIONS"."_".date("Y-m-d g:i A"), function($excel) use ($sched_array) {
            $excel->sheet("SUMMARY", function($sheet) use ($sched_array) {
                $sheet->fromArray($sched_array, null, 'A1', false, false);
                $sheet->prependRow(1, array(
                    'Transaction Title', 'Transaction Code', 'Seed Type', 'Source', 'Status', 'Seed Cooperative', 'Province (FROM)',
                    'Municipality (FROM)', 'Dropoff point (FROM)', 'Assigned PC (FROM)', 'Province (TO)',
                    'Municipality (TO)', 'Dropoff point (TO)', 'Assigned PC (TO)', 'Total Bags (SCHEDULE)', 'Total Bags (ACTUAL)', 'Delivery Date', 'Transfer Date',
                    'Date Recorded'
                ));
                $sheet->freezeFirstRow();
            });
        })->download('xlsx');
    }


    public function schedule_show_more(Request $request){
        $schedule = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->where('transaction_code', $request->transaction_id)
            ->first();

        $actual = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')
            ->select('*', DB::raw('SUM(total_bags) as total_bags'))
            ->where('transaction_code', $request->transaction_id)
            ->groupBy('transaction_code')
            ->first();

        if(count($actual) > 0){
            return array(
                "schedule_transaction_title" => $schedule->transaction_title,
                "schedule_transaction_code" => $schedule->transaction_code,
                "schedule_seed_type" => $schedule->seed_type,
                "schedule_source" => $schedule->source,
                "schedule_status" => $schedule->status,
                "schedule_from_coop" => $schedule->from_coop,
                "schedule_from_province" => $schedule->from_province,
                "schedule_from_municipality" => $schedule->from_municipality,
                "schedule_from_dop" => $schedule->from_dop,
                "schedule_from_assigned_pc" => $schedule->from_assigned_pc,
                "schedule_to_province" => $schedule->to_province,
                "schedule_to_municipality" => $schedule->to_municipality,
                "schedule_to_dop" => $schedule->to_dop,
                "schedule_to_assigned_pc" => $schedule->to_assigned_pc,
                "schedule_from_bags_total" => $schedule->from_bags_total." bag(s)",
                "schedule_to_delivery_date" => $schedule->to_delivery_date == "0000-00-00" ? "N/A" : $schedule->to_delivery_date,
                "schedule_to_transfer_date" => $schedule->to_transfer_date == "0000-00-00" ? "N/A" : $schedule->to_transfer_date,

                "actual_transaction_title" => $actual->transaction_title,
                "actual_transaction_code" => $actual->transaction_code,
                "actual_seed_type" => $actual->seed_type,
                "actual_source" => $actual->source,
                "actual_from_coop" => $actual->from_coop,
                "actual_from_province" => $actual->from_province,
                "actual_from_municipality" => $actual->from_municipality,
                "actual_from_dop" => $actual->from_dop,
                "actual_from_assigned_pc" => $actual->from_assigned_pc,
                "actual_to_province" => $actual->to_province,
                "actual_to_municipality" => $actual->to_municipality,
                "actual_to_dop" => $actual->to_dop,
                "actual_to_assigned_pc" => $actual->to_assigned_pc,
                "actual_from_bags_total" => $actual->total_bags." bag(s)",
                "actual_to_delivery_date" => $actual->to_delivery_date == "0000-00-00" ? "N/A" : $actual->to_delivery_date,
                "actual_to_transfer_date" => $actual->to_transfer_date == "0000-00-00" ? "N/A" : $actual->to_transfer_date,
            );

        }else{
            return array(
                "schedule_transaction_title" => $schedule->transaction_title,
                "schedule_transaction_code" => $schedule->transaction_code,
                "schedule_seed_type" => $schedule->seed_type,
                "schedule_source" => $schedule->source,
                "schedule_status" => $schedule->status,
                "schedule_from_coop" => $schedule->from_coop,
                "schedule_from_province" => $schedule->from_province,
                "schedule_from_municipality" => $schedule->from_municipality,
                "schedule_from_dop" => $schedule->from_dop,
                "schedule_from_assigned_pc" => $schedule->from_assigned_pc,
                "schedule_to_province" => $schedule->to_province,
                "schedule_to_municipality" => $schedule->to_municipality,
                "schedule_to_dop" => $schedule->to_dop,
                "schedule_to_assigned_pc" => $schedule->to_assigned_pc,
                "schedule_from_bags_total" => $schedule->from_bags_total." bag(s)",
                "schedule_to_delivery_date" => $schedule->to_delivery_date == "0000-00-00" ? "N/A" : $schedule->to_delivery_date,
                "schedule_to_transfer_date" => $schedule->to_transfer_date == "0000-00-00" ? "N/A" : $schedule->to_transfer_date,

                "actual_transaction_title" => "N/A",
                "actual_transaction_code" => "N/A",
                "actual_seed_type" => "N/A",
                "actual_source" => "N/A",
                "actual_status" => "N/A",
                "actual_from_coop" => "N/A",
                "actual_from_province" => "N/A",
                "actual_from_municipality" => "N/A",
                "actual_from_dop" => "N/A",
                "actual_from_assigned_pc" => "N/A",
                "actual_to_province" => "N/A",
                "actual_to_municipality" => "N/A",
                "actual_to_dop" => "N/A",
                "actual_to_assigned_pc" => "N/A",
                "actual_from_bags_total" => "N/A",
                "actual_to_delivery_date" => "N/A",
                "actual_to_transfer_date" => "N/A"
            );
        }        
    }
}

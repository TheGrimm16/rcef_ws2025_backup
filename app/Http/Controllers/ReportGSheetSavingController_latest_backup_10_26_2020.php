<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Session;

class ReportGSheetSavingController extends Controller
{
    public function get_variety_id(Request $request){
        return DB::table('seed_seed.seed_characteristics')
            ->where('variety', $request->seed_variety)
            ->groupBy('variety')
            ->value('id');
    }

    public function get_variety_details(Request $request){
        return DB::table('seed_seed.seed_characteristics')
            ->where('id', $request->variety_id)
            ->value('variety');
    }

    public function saveNewDS2021(Request $request){
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->from_seed_variety,"|");
            $variety_list = explode("|", $request->from_seed_variety);
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->from_seed_coop)->value('coopName');
            $transaction_code = Auth::user()->userId."-RGS-".time();

            //remove empty items in array
            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_title" => $request->title_transaction,
                    "transaction_code" => $transaction_code,
                    "seed_type" => $request->seed_type,
                    "source" => $request->source,
                    "status" => $request->status,
                    "from_coop" => $coop_name,
                    "from_coop_accreditation" => $request->from_seed_coop,
                    "from_bags_delivered" => $variety_details[1],
                    "from_variety" => $variety_details[0],
                    "from_assigned_pc" => "N/A",
                    "to_province" => $request->to_province,
                    "to_municipality" => $request->to_municipality,
                    "to_dop" => $request->to_dop_name,
                    "to_delivery_date" => $request->to_delivery_date,
                    "to_assigned_pc" => $request->to_assigned_pc,
                    "edit_draft_flag" => "0",
                    "edit_final_flag" => "0",
                    "recorded_by" => Auth::user()->username,

                    "from_province" => "N/A",
                    "from_municipality" => "N/A",
                    "from_dop" => "N/A"
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                $total_bags += $variety_details[1];
            }

            $balance_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->first();

            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->update(
                    [
                        "current_balance" => $balance_details->current_balance + $total_bags,
                    ]
                );
                
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $balance_details->current_balance,
                "total_bags" => $total_bags,
                "after_balance" => $balance_details->current_balance + $total_bags,
                "remarks" => "ADDED: new seeds to $request->to_province, $request->to_municipality ($total_bags) bag(s)",
                "date_recorded" => date("Y-m-d H:i:s")
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

            DB::commit();
            return "scheduled_ok";

        } catch (\Exception $ex) {
            DB::rollback();
            return "sql_error";
        }
    }


    public function saveInventory_warehouse(Request $request){
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->from_seed_variety,"|");
            $variety_list = explode("|", $request->from_seed_variety);
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->from_seed_coop)->value('coopName');
            $transaction_code = Auth::user()->userId."-RGS-".time();

            //get current balance of station based on user station id
            $balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->first();

            if($balance->current_balance >= $request->from_bags_for_delivery){
                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                    foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $request->title_transaction,
                        "transaction_code" => $transaction_code,
                        "seed_type" => $request->seed_type,
                        "source" => $request->source,
                        "status" => $request->status,
                        "from_coop" => $coop_name,
                        "from_coop_accreditation" => $request->from_seed_coop,
                        "from_bags_delivered" => $variety_details[1],
                        "from_variety" => $variety_details[0],
                        "from_assigned_pc" => "N/A",
                        "to_province" => $request->to_province,
                        "to_municipality" => $request->to_municipality,
                        "to_dop" => $request->to_dop_name,
                        "to_delivery_date" => $request->to_delivery_date,
                        "to_assigned_pc" => $request->to_assigned_pc,
                        "edit_draft_flag" => "0",
                        "edit_final_flag" => "0",
                        "recorded_by" => Auth::user()->username,

                        "from_province" => $request->from_province,
                        "from_municipality" => $request->from_municipality,
                        "from_dop" => $request->from_dop_name
                    );

                    
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);
                    $total_bags += $variety_details[1];
                }
        
                //FROM - substract stocks
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->from_province)
                    ->where('municipality', $request->from_municipality)
                    ->update(
                        [
                            "current_balance" => $balance->current_balance - $total_bags,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $balance->current_balance - $total_bags,
                    "remarks" => "DEDUCT: seeds from $request->from_province, $request->from_municipality ($total_bags) bag(s) | PHILRICE WAREHOUSE"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                //TO - add stocks
                $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->update(
                        [
                            "current_balance" => $to_balance->current_balance + $total_bags,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $to_balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $to_balance->current_balance + $total_bags,
                    "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($total_bags) bag(s) | PHILRICE WAREHOUSE"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                DB::commit();
                return "scheduled_ok";

            }else{
                return "balance_insufficient";
            }


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            return  $ex;
        }
    }


    public function saveInventory_lgu(Request $request){
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->from_seed_variety,"|");
            $variety_list = explode("|", $request->from_seed_variety);
            $transaction_code = Auth::user()->userId."-RGS-".time();

            //remove empty items in array
            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_title" => $request->title_transaction,
                    "transaction_code" => $transaction_code,
                    "seed_type" => $request->seed_type,
                    "source" => $request->source,
                    "status" => $request->status,
                    "from_coop" => "N/A",
                    "from_coop_accreditation" => "N/A",
                    "from_bags_delivered" => "0",
                    "from_variety" => $variety_details[0],
                    "from_bags_in_lgu" => $variety_details[1],
                    "from_province" => $request->from_province,
                    "from_municipality" => $request->from_municipality,
                    "from_dop" => $request->from_dop_name,
                    "from_assigned_pc" => $request->from_assigned_pc,
                    "to_province" => "N/A",
                    "to_municipality" => "N/A",
                    "to_dop" => "N/A",
                    "to_delivery_date" => "",
                    "to_assigned_pc" => "N/A",
                    "edit_draft_flag" => "0",
                    "edit_final_flag" => "0",
                    "recorded_by" => Auth::user()->username,
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                $total_bags += $variety_details[1];
            }

            /*$to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->first();

            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->update(
                    [
                        "current_balance" => $to_balance->current_balance + $total_bags,
                    ]
                );*/ 
                
            $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->first();
                
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance->current_balance,
                "total_bags" => $total_bags,
                "after_balance" => $to_balance->current_balance,
                "remarks" => "RECORDED: seeds to $request->from_province, $request->from_municipality ($total_bags) bag(s) | LGU STOCKS"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

            DB::commit();
            return "scheduled_ok";


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            return $ex;
        }
    }


    public function saveInventory_transferred(Request $request){
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->from_seed_variety,"|");
            $variety_list = explode("|", $request->from_seed_variety);
            $transaction_code = Auth::user()->userId."-RGS-".time();

            //get current balance of station based on user station id
            $from_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->first();
            
            if($from_balance->current_balance >= $request->from_bags_for_transfer){

                //remove empty items in array
                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $request->title_transaction,
                        "transaction_code" => $transaction_code,
                        "seed_type" => $request->seed_type,
                        "source" => $request->source,
                        "status" => $request->status,
                        "from_coop" => "N/A",
                        "from_coop_accreditation" => "N/A",
                        "from_bags_for_transfer" => $variety_details[1],
                        "from_variety" => $variety_details[0],
                        "from_province" => $request->from_province,
                        "from_municipality" => $request->from_municipality,
                        "from_assigned_pc" => "N/A",
                        "from_dop" => $request->from_dop_name,
                        "to_province" => $request->to_province,
                        "to_municipality" => $request->to_municipality,
                        "to_dop" => $request->to_dop_name,
                        "to_transfer_date" => $request->to_transfer_date,
                        "to_assigned_pc" => $request->to_assigned_pc,
                        "edit_draft_flag" => "0",
                        "edit_final_flag" => "0",
                        "recorded_by" => Auth::user()->username,
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                    $total_bags += $variety_details[1];
                }

                //FROM - deduct stocks
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->from_province)
                    ->where('municipality', $request->from_municipality)
                    ->update(
                        [
                            "current_balance" => $from_balance->current_balance - $total_bags,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $from_balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $from_balance->current_balance - $total_bags,
                    "remarks" => "DEDUCT: seeds from $request->from_province, $request->from_municipality ($total_bags) bag(s) | TRANSFERRED SEEDS"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);
                
                //TO - add stocks
                $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->update(
                        [
                            "current_balance" => $to_balance->current_balance + $total_bags,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $to_balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $to_balance->current_balance + $total_bags,
                    "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($total_bags) bag(s) | TRANSFERRED SEEDS"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);


                DB::commit();
                return "scheduled_ok";

            }else{
                return "balance_insufficient";
            }


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            return $ex;
        }
    }

    /**\
     * ************************************************************************************************************************
     * EDIT
     * ************************************************************************************************************************
     */

    public function editNewDS2021(Request $request){
        $this->validate($request, [
            'seed_type' => 'required',
            'source' => 'required',
            'status' => 'required',
            'from_seed_coop' => 'required|not_in:0',
            'seed_variety_str' => 'required',
            'to_province' => 'required|not_in:0',
            'to_municipality' => 'required|not_in:0',
            'to_dop_name' => 'required',
            'to_delivery_date' => 'required',
            'to_assigned_pc' => 'required',
            'title_transaction' => 'required'
        ]);

        //dd($request);

        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->from_seed_coop)->value('coopName');
            $transaction_code = $request->transaction_code;

            //delete transaction and add to balance
            $old_schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_delivered) as total_bags'), 'from_province', 'to_province',
                    'from_municipality', 'to_municipality')
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->first();

            $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $old_schedule_details->to_province)
                ->where('municipality', $old_schedule_details->to_municipality)
                ->first();
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->update(
                    [
                        "current_balance" => $to_balance->current_balance - $old_schedule_details->total_bags,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance->current_balance,
                "total_bags" => $old_schedule_details->total_bags,
                "after_balance" =>  $to_balance->current_balance - $old_schedule_details->total_bags,
                "remarks" => "ROLLBACK: seeds from $old_schedule_details->to_province, $old_schedule_details->to_municipality ($old_schedule_details->total_bags) bag(s) | NEW SEEDS | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();

            //remove empty items in array
            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_title" => $request->title_transaction,
                    "transaction_code" => $transaction_code,
                    "seed_type" => $request->seed_type,
                    "source" => $request->source,
                    "status" => $request->status,
                    "from_coop" => $coop_name,
                    "from_coop_accreditation" => $request->from_seed_coop,
                    "from_bags_delivered" => $variety_details[1],
                    "from_variety" => $variety_details[0],
                    "from_assigned_pc" => "N/A",
                    "to_province" => $request->to_province,
                    "to_municipality" => $request->to_municipality,
                    "to_dop" => $request->to_dop_name,
                    "to_delivery_date" => $request->to_delivery_date,
                    "to_assigned_pc" => $request->to_assigned_pc,
                    "edit_draft_flag" => "0",
                    "edit_final_flag" => "0",
                    "recorded_by" => Auth::user()->username,
                    "from_province" => "N/A",
                    "from_municipality" => "N/A",
                    "from_dop" => "N/A",
                    "edit_draft_flag" => $request->edit_draft_flag,
                    "edit_final_flag" => $request->edit_final_flag
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                $total_bags += $variety_details[1];
            }

            //TO - ADD STOCKS
            $to_balance_after = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->first();
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->update(
                    [
                        "current_balance" => $to_balance_after->current_balance + $total_bags,
                    ]
                );       
                
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance_after->current_balance,
                "total_bags" => $total_bags,
                "after_balance" => $to_balance_after->current_balance + $total_bags,
                "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($total_bags) bag(s) | NEW SEEDS | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

            DB::commit();
            
            Session::flash('success', 'Successfully updated the information of the selected seed schedule.');
            return redirect()->route('rcep.google_sheet.view', $transaction_code);


        } catch (\Exception $ex) {
            DB::rollback();
            //dd($ex);

            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.view', $transaction_code);
        }
    }


    public function editInventoryWarehouse(Request $request){
        $this->validate($request, [
            'seed_type' => 'required',
            'source' => 'required',
            'status' => 'required',
            'from_seed_coop' => 'required|not_in:0',
            'seed_variety_str' => 'required',
            'from_province' => 'required|not_in:0',
            'from_municipality' => 'required|not_in:0',
            'from_dop_name' => 'required',
            'to_province' => 'required|not_in:0',
            'to_municipality' => 'required|not_in:0',
            'to_dop_name' => 'required',
            'to_delivery_date' => 'required',
            'to_assigned_pc' => 'required',
            'title_transaction' => 'required'
        ]);

        //dd($request);
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $request->from_seed_coop)->value('coopName');
            $transaction_code = $request->transaction_code;

            //delete transaction and add to balance
            $old_schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_delivered) as total_bags'), 'from_province', 'to_province',
                    'from_municipality', 'to_municipality')
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->first();

            //get current balance of station based on user station id
            $from_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $old_schedule_details->from_province)
                ->where('municipality', $old_schedule_details->from_municipality)
                //->where('station_id', Auth::user()->stationId)
                ->first();

            $old_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_delivered) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $old_schedule_details->from_province)
                ->where('municipality', $old_schedule_details->from_municipality)
                ->update(
                    [
                        "current_balance" => $from_balance->current_balance + $old_balance,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $from_balance->current_balance,
                "total_bags" => $old_balance,
                "after_balance" => $from_balance->current_balance + $old_balance,
                "remarks" => "REPLENISH: seeds from $old_schedule_details->from_province, $old_schedule_details->from_municipality ($old_balance) bag(s) | PHILRICE WAREHOUSE | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

            $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $old_schedule_details->to_province)
                ->where('municipality', $old_schedule_details->to_municipality)
                //->where('station_id', Auth::user()->stationId)
                ->first();

            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $old_schedule_details->to_province)
                ->where('municipality', $old_schedule_details->to_municipality)
                ->update(
                    [
                        "current_balance" => $to_balance->current_balance - $old_balance,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance->current_balance,
                "total_bags" => $old_balance,
                "after_balance" => $to_balance->current_balance - $old_balance,
                "remarks" => "ROLLBACK: seeds from $old_schedule_details->to_province, $old_schedule_details->to_municipality ($old_balance) bag(s) | PHILRICE WAREHOUSE | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();


            $from_balance_after = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->first();

            if($from_balance_after->current_balance >= $request->from_bags_for_delivery){

                //remove empty items in array
                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $request->title_transaction,
                        "transaction_code" => $transaction_code,
                        "seed_type" => $request->seed_type,
                        "source" => $request->source,
                        "status" => $request->status,
                        "from_coop" => $coop_name,
                        "from_coop_accreditation" => $request->from_seed_coop,
                        "from_bags_delivered" => $variety_details[1],
                        "from_variety" => $variety_details[0],
                        "from_province" => $request->from_province,
                        "from_municipality" => $request->from_municipality,
                        "from_dop" => $request->from_dop_name,
                        "from_assigned_pc" => "N/A",
                        "to_province" => $request->to_province,
                        "to_municipality" => $request->to_municipality,
                        "to_dop" => $request->to_dop_name,
                        "to_delivery_date" => $request->to_delivery_date,
                        "to_assigned_pc" => $request->to_assigned_pc,
                        "edit_draft_flag" => "0",
                        "edit_final_flag" => "0",
                        "recorded_by" => Auth::user()->username,
                        "edit_draft_flag" => $request->edit_draft_flag,
                        "edit_final_flag" => $request->edit_final_flag
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                    $total_bags += $variety_details[1];
                }

                //FROM - deduct stocks
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('province', $request->from_province)
                    ->where('municipality', $request->from_municipality)
                    //->where('station_id', Auth::user()->stationId)
                    ->update(
                        [
                            "current_balance" => $from_balance_after->current_balance - $total_bags,
                        ]
                    );       
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $from_balance_after->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $from_balance_after->current_balance - $total_bags,
                    "remarks" => "DEDUCT: seeds from $request->from_province, $request->from_municipality ($total_bags) bag(s) | PHILRICE WAREHOUSE | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);


                //TO - ADD STOCKS
                $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    //->where('station_id', Auth::user()->stationId)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    //->where('station_id', Auth::user()->stationId)
                    ->update(
                        [
                            "current_balance" => $to_balance->current_balance + $total_bags,
                        ]
                    );
    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $to_balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $to_balance->current_balance + $total_bags,
                    "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($total_bags) bag(s) | PHILRICE WAREHOUSE | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                DB::commit();
                
                Session::flash('success', 'Successfully updated the information of the selected seed schedule.');
                return redirect()->route('rcep.google_sheet.view', $transaction_code);

            }else{
                Session::flash('error_msg', 'The selected balance of the "FROM" municipality is insufficient');
                return redirect()->route('rcep.google_sheet.view', $transaction_code);
            }


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            dd($ex);

            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.view', $transaction_code);
        }
    }


    public function editInventoryLgu(Request $request){
        $this->validate($request, [
            'seed_type' => 'required',
            'source' => 'required',
            'status' => 'required',
            'seed_variety_str' => 'required',
            'from_province' => 'required|not_in:0',
            'from_municipality' => 'required|not_in:0',
            'from_dop_name' => 'required',
            'title_transaction' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $transaction_code = $request->transaction_code;

            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();

            //remove empty items in array
            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_title" => $request->title_transaction,
                    "transaction_code" => $transaction_code,
                    "seed_type" => $request->seed_type,
                    "source" => $request->source,
                    "status" => $request->status,
                    "from_coop" => "N/A",
                    "from_coop_accreditation" => "N/A",
                    "from_bags_in_lgu" => $variety_details[1],
                    "from_bags_delivered" => "N/A",
                    "from_variety" => $variety_details[0],
                    "from_province" => $request->from_province,
                    "from_municipality" => $request->from_municipality,
                    "from_dop" => $request->from_dop_name,
                    "from_assigned_pc" => $request->from_assigned_pc,
                    "edit_draft_flag" => "0",
                    "edit_final_flag" => "0",
                    "recorded_by" => Auth::user()->username,
                    "edit_draft_flag" => $request->edit_draft_flag,
                    "edit_final_flag" => $request->edit_final_flag
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                $total_bags += $variety_details[1];
            }

            DB::commit();
            
            Session::flash('success', 'Successfully updated the information of the selected seed schedule.');
            return redirect()->route('rcep.google_sheet.view', $transaction_code);


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            //dd($ex);

            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.view', $transaction_code);
        }
    }


    public function editInventoryTransferred(Request $request){
        $this->validate($request, [
            'seed_type' => 'required',
            'source' => 'required',
            'status' => 'required',
            'seed_variety_str' => 'required',
            'from_province' => 'required|not_in:0',
            'from_municipality' => 'required|not_in:0',
            'from_dop_name' => 'required',
            'to_province' => 'required|not_in:0',
            'to_municipality' => 'required|not_in:0',
            'to_dop_name' => 'required',
            'to_transfer_date' => 'required',
            'to_assigned_pc' => 'required',
            'title_transaction' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $transaction_code = $request->transaction_code;

            //delete transaction and add to balance
            $old_schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_delivered) as total_bags'), 'from_province', 'to_province',
                    'from_municipality', 'to_municipality')
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->first();

            //get current balance of station based on user station id
            $from_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $old_schedule_details->from_province)
                ->where('municipality', $old_schedule_details->from_municipality)
                //->where('station_id', Auth::user()->stationId)
                ->first();

            $old_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_for_transfer) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $old_schedule_details->from_province)
                ->where('municipality', $old_schedule_details->from_municipality)
                ->update(
                    [
                        "current_balance" => $from_balance->current_balance + $old_balance,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $from_balance->current_balance,
                "total_bags" => $old_balance,
                "after_balance" => $from_balance->current_balance + $old_balance,
                "remarks" => "REPLENISH: seeds from $old_schedule_details->from_province, $old_schedule_details->from_municipality ($old_balance) bag(s) | PHILRICE WAREHOUSE | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

            $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $old_schedule_details->to_province)
                ->where('municipality', $old_schedule_details->to_municipality)
                //->where('station_id', Auth::user()->stationId)
                ->first();

            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                //->where('station_id', Auth::user()->stationId)
                ->where('province', $old_schedule_details->to_province)
                ->where('municipality', $old_schedule_details->to_municipality)
                ->update(
                    [
                        "current_balance" => $to_balance->current_balance - $old_balance,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance->current_balance,
                "total_bags" => $old_balance,
                "after_balance" => $to_balance->current_balance - $old_balance,
                "remarks" => "ROLLBACK: seeds from $old_schedule_details->to_province, $old_schedule_details->to_municipality ($old_balance) bag(s) | PHILRICE WAREHOUSE | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();

            $current_balance_after = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                //->where('station_id', Auth::user()->stationId)
                ->first();

            if($current_balance_after->current_balance >= $request->from_bags_for_transfer){

                //remove empty items in array
                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $request->title_transaction,
                        "transaction_code" => $transaction_code,
                        "seed_type" => $request->seed_type,
                        "source" => $request->source,
                        "status" => $request->status,
                        "from_coop" => "N/A",
                        "from_coop_accreditation" => "N/A",
                        "from_bags_for_transfer" => $variety_details[1],
                        "from_bags_in_lgu" => "0",
                        "from_bags_delivered" => "0",
                        "from_variety" => $variety_details[0],
                        "from_province" => $request->from_province,
                        "from_municipality" => $request->from_municipality,
                        "from_dop" => $request->from_dop_name,
                        "to_province" => $request->to_province,
                        "to_municipality" => $request->to_municipality,
                        "to_dop" => $request->to_dop_name,
                        "to_transfer_date" => $request->to_transfer_date,
                        "to_assigned_pc" => $request->to_assigned_pc,
                        "edit_draft_flag" => "0",
                        "edit_final_flag" => "0",
                        "recorded_by" => Auth::user()->username,
                        "edit_draft_flag" => $request->edit_draft_flag,
                        "edit_final_flag" => $request->edit_final_flag
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->insert($data);

                    $total_bags += $variety_details[1];
                }


                //FROM - deduct stocks
                $from_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->from_province)
                    ->where('municipality', $request->from_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->from_province)
                    ->where('municipality', $request->from_municipality)
                    ->update(
                        [
                            "current_balance" => $from_balance->current_balance - $total_bags,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $from_balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $from_balance->current_balance - $total_bags,
                    "remarks" => "DEDUCT: seeds from $request->from_province, $request->from_municipality ($total_bags) bag(s) | TRANSFERRED SEEDS | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);


                //TO - add stocks
                $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    //->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->update(
                        [
                            "current_balance" => $to_balance->current_balance + $total_bags,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $to_balance->current_balance,
                    "total_bags" => $total_bags,
                    "after_balance" => $to_balance->current_balance + $total_bags,
                    "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($total_bags) bag(s) | TRANSFERRED SEEDS | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                DB::commit();
                
                Session::flash('success', 'Successfully updated the information of the selected seed schedule.');
                return redirect()->route('rcep.google_sheet.view', $transaction_code);

            }else{
                //return "balance_insufficient";
                Session::flash('error_msg', 'The selected balance of the "FROM" municipality is insufficient');
                return redirect()->route('rcep.google_sheet.view', $transaction_code);
            }


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            //dd($ex);

            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.view', $transaction_code);
        }
    }

    /**\
     * ************************************************************************************************************************
     * ACTUAL
     * ************************************************************************************************************************
     */
    public function actualNewDS2021(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'to_dop_name' => 'required',
            'to_delivery_date' => 'required',
        ]);

        $check_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->where('transaction_code', $request->transaction_code)->first();
        if(count($check_data) == 0){
            DB::beginTransaction();
            try {
                $variety_list = rtrim($request->seed_variety_str,"|");
                $variety_list = explode("|", $request->seed_variety_str);
                $transaction_code = $request->transaction_code;

                $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->where('transaction_code', $request->transaction_code)
                    ->groupBy('transaction_code')
                    ->first();

                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $schedule_details->transaction_title,
                        "transaction_code" => $transaction_code,
                        "seed_type" => $schedule_details->seed_type,
                        "source" => $schedule_details->source,
                        "from_coop_accreditation" => $schedule_details->from_coop_accreditation,
                        "from_coop" => $schedule_details->from_coop,
                        "from_province" => $schedule_details->from_province,
                        "from_municipality" => $schedule_details->from_municipality,
                        "from_dop" => "N/A",
                        "from_assigned_pc" => $schedule_details->from_assigned_pc,
                        "to_province" => $schedule_details->to_province,
                        "to_municipality" => $schedule_details->to_municipality,
                        "to_dop" => $request->to_dop_name,
                        "to_delivery_date" => $schedule_details->to_delivery_date,
                        "to_transfer_date" => $schedule_details->to_transfer_date,
                        "to_assigned_pc" => $schedule_details->to_assigned_pc,
                        "total_bags" => $variety_details[1],
                        "variety" => $variety_details[0],
                        "submitted_by" => Auth::user()->username
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                    $total_bags += $variety_details[1];
                }

                DB::commit();
                Session::flash('success', 'Completed sending actual data for the selected seed schedule');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);

            } catch (\Exception $ex) {
                DB::rollback();
                Session::flash('error_msg', 'The system encountered an error while executing this action, please refresh & try again.');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
            }
        }else{
            Session::flash('error_msg', 'This seed schedule already has a corresponding actual data.');
            return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
        }
    } 

    public function actualInventoryWarehouse(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'from_dop_name' => 'required',
            'to_dop_name' => 'required',
            'to_delivery_date' => 'required',
        ]);

        $check_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->where('transaction_code', $request->transaction_code)->first();
        if(count($check_data) == 0){
            DB::beginTransaction();
            try {
                $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->where('transaction_code', $request->transaction_code)
                    ->groupBy('transaction_code')
                    ->first();

                $variety_list = rtrim($request->seed_variety_str,"|");
                $variety_list = explode("|", $request->seed_variety_str);
                $transaction_code = $request->transaction_code;

                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $schedule_details->transaction_title,
                        "seed_type" => $schedule_details->seed_type,
                        "source" => $schedule_details->source,
                        "from_coop_accreditation" => $schedule_details->from_coop_accreditation,
                        "from_coop" => $schedule_details->from_coop,
                        "from_province" => $schedule_details->from_province,
                        "from_municipality" => $schedule_details->from_municipality,
                        "from_assigned_pc" => $schedule_details->from_assigned_pc,
                        "to_province" => $schedule_details->to_province,
                        "to_municipality" => $schedule_details->to_municipality,
                        "to_delivery_date" => $schedule_details->to_delivery_date,
                        "to_transfer_date" => $schedule_details->to_transfer_date,
                        "to_assigned_pc" => $schedule_details->to_assigned_pc,

                        "transaction_code" => $transaction_code,
                        "total_bags" => $variety_details[1],
                        "variety" => $variety_details[0],
                        "from_dop" => $request->from_dop_name,
                        "to_dop" => $request->to_dop_name,
                        "date_recorded" => date("Y-m-d H:i:s"),
                        "submitted_by" => Auth::user()->username
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                    $total_bags += $variety_details[1];
                }

                DB::commit();

                Session::flash('success', 'Completed sending actual data for the selected seed schedule');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);

            } catch (\Exception $ex) {
                DB::rollback();
                //dd($ex);
                Session::flash('error_msg', 'The system encountered an error while executing this action, please refresh & try again.');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
            }
        }else{
            Session::flash('error_msg', 'This seed schedule already has a corresponding actual data.');
            return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
        }

        //dd($request);
        
    }

    public function actualInventoryLgu(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'from_dop_name' => 'required',
        ]);

        $check_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->where('transaction_code', $request->transaction_code)->first();
        if(count($check_data) == 0){
            DB::beginTransaction();
            try {
                $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                        ->where('transaction_code', $request->transaction_code)
                        ->groupBy('transaction_code')
                        ->first();

                $variety_list = rtrim($request->seed_variety_str,"|");
                $variety_list = explode("|", $request->seed_variety_str);
                $transaction_code = $request->transaction_code;

                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $schedule_details->transaction_title,
                        "seed_type" => $schedule_details->seed_type,
                        "source" => $schedule_details->source,
                        "from_coop_accreditation" => $schedule_details->from_coop_accreditation,
                        "from_coop" => $schedule_details->from_coop,
                        "from_province" => $schedule_details->from_province,
                        "from_municipality" => $schedule_details->from_municipality,
                        "from_assigned_pc" => $schedule_details->from_assigned_pc,
                        "to_province" => $schedule_details->to_province,
                        "to_municipality" => $schedule_details->to_municipality,
                        "to_delivery_date" => $schedule_details->to_delivery_date,
                        "to_transfer_date" => $schedule_details->to_transfer_date,
                        "to_assigned_pc" => $schedule_details->to_assigned_pc,

                        "transaction_code" => $transaction_code,
                        "total_bags" => $variety_details[1],
                        "variety" => $variety_details[0],
                        "from_dop" => $request->from_dop_name,
                        "to_dop" => "N/A",
                        "date_recorded" => date("Y-m-d H:i:s"),
                        "submitted_by" => Auth::user()->username
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                    $total_bags += $variety_details[1];
                }

                DB::commit();

                Session::flash('success', 'Completed sending actual data for the selected seed schedule');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);

            } catch (\Exception $ex) {
                DB::rollback();
                //return "sql_error";
                //dd($ex);
                Session::flash('error_msg', 'The system encountered an error while executing this action, please refresh & try again.');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
            }
        }else{
            Session::flash('error_msg', 'This seed schedule already has a corresponding actual data.');
            return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
        }
    }


    public function actualInventoryTransferred(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'from_dop_name' => 'required',
            'to_dop_name' => 'required',
        ]);

        $check_data = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->where('transaction_code', $request->transaction_code)->first();
        if(count($check_data) == 0){
            DB::beginTransaction();
            try {
                $schedule_details = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                    ->where('transaction_code', $request->transaction_code)
                    ->groupBy('transaction_code')
                    ->first();
    
                $variety_list = rtrim($request->seed_variety_str,"|");
                $variety_list = explode("|", $request->seed_variety_str);
                $transaction_code = $request->transaction_code;
    
                $variety_list = array_filter($variety_list);
                $total_bags = 0;
                foreach($variety_list as $variety){
                    $variety_details = explode("&", $variety);
                    $data = array(
                        "transaction_title" => $schedule_details->transaction_title,
                        "seed_type" => $schedule_details->seed_type,
                        "source" => $schedule_details->source,
                        "from_coop_accreditation" => $schedule_details->from_coop_accreditation,
                        "from_coop" => $schedule_details->from_coop,
                        "from_province" => $schedule_details->from_province,
                        "from_municipality" => $schedule_details->from_municipality,
                        "from_assigned_pc" => $schedule_details->from_assigned_pc,
                        "to_province" => $schedule_details->to_province,
                        "to_municipality" => $schedule_details->to_municipality,
                        "to_delivery_date" => $schedule_details->to_delivery_date,
                        "to_transfer_date" => $schedule_details->to_transfer_date,
                        "to_assigned_pc" => $schedule_details->to_assigned_pc,
    
                        "transaction_code" => $transaction_code,
                        "total_bags" => $variety_details[1],
                        "variety" => $variety_details[0],
                        "from_dop" => $request->from_dop_name,
                        "to_dop" => $request->to_dop_name,
                        "date_recorded" => date("Y-m-d H:i:s"),
                        "submitted_by" => Auth::user()->username
                    );
            
                    DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);
    
                    $total_bags += $variety_details[1];
                }
    
                DB::commit();
    
                Session::flash('success', 'Completed sending actual data for the selected seed schedule');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
    
            } catch (\Exception $ex) {
                DB::rollback();
                //return "sql_error";
                //dd($ex);
                Session::flash('error_msg', 'The system encountered an error while executing this action, please refresh & try again.');
                return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
            }
        }else{
            Session::flash('error_msg', 'This seed schedule already has a corresponding actual data.');
            return redirect()->route('rcep.google_sheet.actual', $request->transaction_code);
        }
    }

    /**\
     * ************************************************************************************************************************
     * ACTUAL - END
     * ************************************************************************************************************************
     */

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


    public function save_weekly_data(Request $request){
        $this->validate($request, [
            'date_range' => 'required',
            'bags_distributed' => 'required|not_in:0',
            'farmer_beneficiaries' => 'required|not_in:0',
            'area_planted' => 'required|not_in:0',
            'province' => 'required|not_in:0',
            'municipality' => 'required|not_in:0'
        ]);

        DB::beginTransaction();
        try {
            $date_str = explode(" - ", $request->date_range);
            $current_week = $this->get_current_week();
            
            $date_from = date("Y-m-d", strtotime($date_str[0]));
            $date_to = date("Y-m-d", strtotime($date_str[1]));
            $current_from = $current_week['start'];
            $current_to = $current_week['end'];

            if($date_from > $current_from AND $date_to > $current_to OR
               $date_from < $current_from AND $date_to < $current_to){
                
                //echo "not accepted";
                Session::flash('error_msg', 'you can only input a report for the current week.');
                return redirect()->route('rcep.google_sheet.weekly');
                

            }else{
                $lib_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $request->province)->first();
                $insert_data = array(
                    'station_id' => Auth::user()->stationId,
                    'date_from' => $date_str[0],
                    'date_to' => $date_str[1],
                    'region' => $lib_data->region,
                    'province' => $request->province,
                    'municipality' => $request->municipality,
                    'bags_distributed' => $request->bags_distributed,
                    'farmer_beneficiaries' => $request->farmer_beneficiaries,
                    'area_planted' => $request->area_planted,
                    'date_recorded' => date("Y-m-d H:i:s"),
                    'created_by' => Auth::user()->username
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')->insert($insert_data);
                DB::commit();

                Session::flash('success', 'You have successfully added your input to the report of your tagged station.');
                return redirect()->route('rcep.google_sheet.weekly');
            }

        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
        }
    }


    public function update_weekly_data(Request $request){
        $this->validate($request, [
            'date_range' => 'required',
            'bags_distributed' => 'required|not_in:0',
            'farmer_beneficiaries' => 'required|not_in:0',
            'area_planted' => 'required|not_in:0',
            'province' => 'required|not_in:0',
            'municipality' => 'required|not_in:0'
        ]);

        DB::beginTransaction();
        try {
            $date_str = explode(" - ", $request->date_range);
            $current_week = $this->get_current_week();
            
            $date_from = date("Y-m-d", strtotime($date_str[0]));
            $date_to = date("Y-m-d", strtotime($date_str[1]));
            $current_from = $current_week['start'];
            $current_to = $current_week['end'];

            if($date_from > $current_from AND $date_to > $current_to OR
               $date_from < $current_from AND $date_to < $current_to){
                
                //echo "not accepted";
                Session::flash('error_msg', 'Invalid date range, please select date(s) of the current week.');
                return redirect()->route('rcep.google_sheet.weekly.edit', $request->report_id);
                
            }else{
                $lib_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $request->province)->first();
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_report')
                    ->where('id', $request->report_id)
                    ->update(
                        [
                            'station_id' => Auth::user()->stationId,
                            'date_from' => $date_str[0],
                            'date_to' => $date_str[1],
                            'region' => $lib_data->region,
                            'province' => $request->province,
                            'municipality' => $request->municipality,
                            'bags_distributed' => $request->bags_distributed,
                            'farmer_beneficiaries' => $request->farmer_beneficiaries,
                            'area_planted' => $request->area_planted,
                            'date_recorded' => date("Y-m-d H:i:s"),
                            'created_by' => Auth::user()->username
                        ]
                    );
                DB::commit();

                Session::flash('success', 'You have successfully updated the selected report.');
                return redirect()->route('rcep.google_sheet.weekly.edit', $request->report_id);
            }

        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex);
        }
    }

    /**
     * 
     * 10-15-2020 FUNCTIONS
     */
    public function schedule_saveAsFinal($transaction_code){
        DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
            ->where('transaction_code', $transaction_code)
            ->update(
                [
                    "edit_draft_flag" => 0,
                    "edit_final_flag" => 1
                ]
            );
            
        return redirect()->route('rcep.google_sheet.schedule_home');
    }


    /**
     * 10-16-2020 FUNCTIONS
     */
    function weekOfMonth($date) {
        //Get the first day of the month.
        $firstOfMonth = strtotime(date("Y-m-01", $date));
        //Apply above formula.
        return intval(date("W", $date)) - intval(date("W", $firstOfMonth)) + 1;
    }

    public function generate_weekly_data(){
        $date_now = strtotime(date("Y-m-d"));

        $week_number = $this->weekOfMonth($date_now);
        $month_name = date("F", $date_now);

        //START: mirror report data to rcep_google sheets database (MUNICIPAL)
        $check_weekly_municipal = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')
            ->where('current_month', $month_name)
            ->where('current_week', $week_number)
            ->count();
        
        if($check_weekly_municipal > 0){
            echo "REPLICATE_DATA: data for current week is already generated... (MUNICIPAL)<br><br>";
        }else{
            $daily_report_data_municipal = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->get();
            echo "staring generating weekly reports (municipal) from daily reports: ".count($daily_report_data_municipal)." row(s)<br>";
            foreach($daily_report_data_municipal as $m_row){
                $municipal_data = array(
                    "current_month" => $month_name,
                    "current_week" => $week_number,
                    "region" => $m_row->region,
                    "province" => $m_row->province,
                    "municipality" => $m_row->municipality,
                    "total_farmers" => $m_row->total_farmers,
                    "total_bags" => $m_row->total_bags,
                    "total_dist_area" => $m_row->total_dist_area,
                    "total_actual_area" => $m_row->total_actual_area,
                    "total_male" => $m_row->total_male,
                    "total_female" => $m_row->total_female
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')->insert($municipal_data);
                echo "MYSQL_INSERT: INSERTED MUNICIPALITY ($m_row->province < $m_row->municipality)<br>";
            }
            echo "ended generating weekly reports (municipal) from daily reports...<br><br>";
        }
        //END: mirror report data to rcep_google sheets database (MUNICIPAL)


        //START: mirror report data to rcep_google sheets database (PROVINCIAL)
        $check_weekly_provincial = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_provincial')
            ->where('current_month', $month_name)
            ->where('current_week', $week_number)
            ->count();
        
        if($check_weekly_provincial > 0){
            echo "REPLICATE_DATA: data for current week is already generated... (PROVINCIAL)<br><br>";
        }else{
            $daily_report_data_provincial = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->get();
            echo "staring generating weekly reports (provincial) from daily reports: ".count($daily_report_data_provincial)." row(s)<br>";
            foreach($daily_report_data_provincial as $p_row){
                $province_data = array(
                    "current_month" => $month_name,
                    "current_week" => $week_number,
                    "region" => $p_row->region,
                    "province" => $p_row->province,
                    "total_municipalities" => $p_row->total_municipalities,
                    "total_farmers" => $p_row->total_farmers,
                    "total_bags" => $p_row->total_bags,
                    "total_dist_area" => $p_row->total_dist_area,
                    "total_actual_area" => $p_row->total_actual_area,
                    "total_male" => $p_row->total_male,
                    "total_female" => $p_row->total_female
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_provincial')->insert($province_data);
                echo "MYSQL_INSERT: INSERTED PROVINCE ($p_row->province)<br>";
            }
            echo "ended generating weekly reports (provincial) from daily reports...<br><br>";
        }
        //END: mirror report data to rcep_google sheets database (PROVINCIAL)

        //START: mirror report data to rcep_google sheets database (REGIONAL)
        $check_weekly_regional = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_regional')
            ->where('current_month', $month_name)
            ->where('current_week', $week_number)
            ->count();
        
        if($check_weekly_regional > 0){
            echo "REPLICATE_DATA: data for current week is already generated... (REGIONAL)<br><br>";
        }else{
            $daily_report_data_regional = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->get();
            echo "staring generating weekly reports (regional) from daily reports: ".count($daily_report_data_regional)." row(s)<br>";
            foreach($daily_report_data_regional as $r_row){
                $region_data = array(
                    "current_month" => $month_name,
                    "current_week" => $week_number,
                    "region" => $r_row->region,
                    "total_provinces" => $r_row->total_provinces,
                    "total_municipalities" => $r_row->total_municipalities,
                    "total_farmers" => $r_row->total_farmers,
                    "total_bags" => $r_row->total_bags,
                    "total_dist_area" => $r_row->total_dist_area,
                    "total_actual_area" => $r_row->total_actual_area,
                    "total_male" => $r_row->total_male,
                    "total_female" => $r_row->total_female
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_regional')->insert($region_data);
                echo "MYSQL_INSERT: INSERTED REGION ($r_row->region)<br>";
            }
            echo "ended generating weekly reports (regional) from daily reports...<br><br>";
        }
        //END: mirror report data to rcep_google sheets database (REGIONAL)

        //START: mirror report data to rcep_google sheets database (NATIONAL)
        $check_weekly_regional = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_national')
            ->where('current_month', $month_name)
            ->where('current_week', $week_number)
            ->count();
        
        if($check_weekly_regional > 0){
            echo "REPLICATE_DATA: data for current week is already generated... (NATIONAL)<br><br>";
        }else{
            $daily_report_data_national = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->first();
            echo "staring generating weekly reports (national) from daily reports... <br>";
            $national_data = array(
                "current_month" => $month_name,
                "current_week" => $week_number,
                "regions" => $daily_report_data_national->regions,
                "provinces" => $daily_report_data_national->provinces,
                "municipalities" => $daily_report_data_national->municipalities,
                "total_farmers" => $daily_report_data_national->total_farmers,
                "total_bags" => $daily_report_data_national->total_bags,
                "total_dist_area" => $daily_report_data_national->total_dist_area,
                "total_actual_area" => $daily_report_data_national->total_actual_area,
                "total_male" => $daily_report_data_national->total_male,
                "total_female" => $daily_report_data_national->total_female
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_national')->insert($national_data);
            echo "MYSQL_INSERT: INSERTED NATIONAL DATA<br>";
            echo "ended generating weekly reports (national) from daily reports...<br><br>";
        }
        //END: mirror report data to rcep_google sheets database (NATIONAL)
    }

}

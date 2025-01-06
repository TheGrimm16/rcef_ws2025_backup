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
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->first();

            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
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
                ->where('station_id', Auth::user()->stationId)
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
                    ->where('station_id', Auth::user()->stationId)
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
                    ->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('station_id', Auth::user()->stationId)
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
                    "remarks" => "ADDED: seeds to $request->from_province, $request->from_municipality ($total_bags) bag(s) | PHILRICE WAREHOUSE"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                DB::commit();
                return "scheduled_ok";

            }else{
                return "balance_insufficient";
            }


        } catch (\Exception $ex) {
            DB::rollback();
            return "sql_error";
            //return  $ex;
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

            $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
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
                );       
                
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance->current_balance,
                "total_bags" => $total_bags,
                "after_balance" => $to_balance->current_balance + $total_bags,
                "remarks" => "ADDED: seeds to $request->from_province, $request->from_municipality ($total_bags) bag(s) | LGU STOCKS"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

            DB::commit();
            return "scheduled_ok";


        } catch (\Exception $ex) {
            DB::rollback();
            return "sql_error";
            //return $ex;
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
                ->where('station_id', Auth::user()->stationId)
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
                    ->where('station_id', Auth::user()->stationId)
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
                    ->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('station_id', Auth::user()->stationId)
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
            return "sql_error";
            //return $ex;
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

            //get current balance of station based on user station id
            $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->first();

            //delete transaction and add to balance
            $old_transaction_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_delivered) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->update(
                    [
                        "current_balance" => $to_balance->current_balance - $old_transaction_balance,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $to_balance->current_balance,
                "total_bags" => $old_transaction_balance,
                "after_balance" => $to_balance->current_balance - $old_transaction_balance,
                "remarks" => "DEDUCT: seeds from $request->to_province, $request->to_municipality ($old_transaction_balance) bag(s) | NEW SEEDS | EDIT"
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
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->to_province)
                ->where('municipality', $request->to_municipality)
                ->first();
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
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
            //return "sql_error";
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

            //get current balance of station based on user station id
            $from_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->where('station_id', Auth::user()->stationId)
                ->first();

            //delete transaction and add to balance
            $old_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_delivered) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
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
                "remarks" => "REPLENISH: seeds from $request->from_province, $request->from_municipality ($old_balance) bag(s) | PHILRICE WAREHOUSE | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();


            $from_balance_after = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
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
                    ->where('station_id', Auth::user()->stationId)
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
                    ->where('station_id', Auth::user()->stationId)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->where('station_id', Auth::user()->stationId)
                    ->update(
                        [
                            "current_balance" => $to_balance->current_balance + ($total_bags - $old_balance),
                        ]
                    );
                $difference_value  =  $total_bags - $old_balance;       
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $to_balance->current_balance,
                    "total_bags" => $difference_value,
                    "after_balance" => $to_balance->current_balance + ($difference_value),
                    "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($difference_value) bag(s) | PHILRICE WAREHOUSE | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                DB::commit();
                
                Session::flash('success', 'Successfully updated the information of the selected seed schedule.');
                return redirect()->route('rcep.google_sheet.view', $transaction_code);

            }else{
                return "balance_insufficient";
            }


        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            //dd($ex);

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

            $old_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_in_lgu) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();

            $from_balance_after = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->where('station_id', Auth::user()->stationId)
                ->first();

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

            $value_diff = $total_bags - $old_balance;
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->update(
                    [
                        "current_balance" => $from_balance_after->current_balance + $value_diff,
                    ]
                );       
                
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $from_balance_after->current_balance,
                "total_bags" => $value_diff,
                "after_balance" => $from_balance_after->current_balance + $value_diff,
                "remarks" => "ADDED: seeds to $request->from_province, $request->from_municipality ($value_diff) bag(s) | LGU STOCKS | EDIT"
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

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

            $old_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_for_transfer) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');

            $balance_for_save =  $request->from_bags_for_transfer - $old_balance;

            //get current balance of station based on user station id
            /*$current_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')->where('station_id', Auth::user()->stationId)->first();

            //delete transaction and add to balance
            $old_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')
                ->select(DB::raw('SUM(from_bags_for_transfer) as total_bags'))
                ->where('transaction_code', $request->transaction_code)
                ->groupBy('transaction_code')
                ->value('total_bags');
            
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('station_id', Auth::user()->stationId)
                ->update(
                    [
                        "balance" => $current_balance->balance + $old_balance,
                    ]
                );
            $logs = array(
                "station_id" => Auth::user()->stationId,
                "transaction_code" => $transaction_code,
                "before_balance" => $current_balance->balance,
                "total_bags" => $old_balance,
                "after_balance" => $current_balance->balance + $old_balance
            );
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);*/
            DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_schedule')->where('transaction_code', $request->transaction_code)->delete();


            $current_balance_after = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                ->where('province', $request->from_province)
                ->where('municipality', $request->from_municipality)
                ->where('station_id', Auth::user()->stationId)
                ->first();

            if($current_balance_after->current_balance >= $balance_for_save){

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
                            "current_balance" => $from_balance->current_balance - $balance_for_save,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $from_balance->current_balance,
                    "total_bags" => $balance_for_save,
                    "after_balance" => $from_balance->current_balance - $balance_for_save,
                    "remarks" => "DEDUCT: seeds from $request->from_province, $request->from_municipality ($balance_for_save) bag(s) | TRANSFERRED SEEDS | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);


                //TO - add stocks
                $to_balance = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_balance')
                    ->where('station_id', Auth::user()->stationId)
                    ->where('province', $request->to_province)
                    ->where('municipality', $request->to_municipality)
                    ->update(
                        [
                            "current_balance" => $to_balance->current_balance + $balance_for_save,
                        ]
                    );       
                    
                $logs = array(
                    "station_id" => Auth::user()->stationId,
                    "transaction_code" => $transaction_code,
                    "before_balance" => $to_balance->current_balance,
                    "total_bags" => $balance_for_save,
                    "after_balance" => $to_balance->current_balance + $balance_for_save,
                    "remarks" => "ADDED: seeds to $request->to_province, $request->to_municipality ($balance_for_save) bag(s) | TRANSFERRED SEEDS | EDIT"
                );
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_balance_logs')->insert($logs);

                DB::commit();
                
                Session::flash('success', 'Successfully updated the information of the selected seed schedule.');
                return redirect()->route('rcep.google_sheet.view', $transaction_code);

            }else{
                return "balance_insufficient";
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

        //dd($request);
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $transaction_code = $request->transaction_code;

            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_code" => $transaction_code,
                    "total_bags" => $variety_details[1],
                    "variety" => $variety_details[0],
                    "from_dop" => "N/A",
                    "to_dop" => $request->to_dop_name,
                    "recorded_by" => Auth::user()->username
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                $total_bags += $variety_details[1];
            }

            DB::commit();

            return redirect()->route('rcep.google_sheet.actual', $transaction_code);

        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            dd($ex);
        }
    } 

    public function actualInventoryWarehouse(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'from_dop_name' => 'required',
            'to_dop_name' => 'required',
            'to_delivery_date' => 'required',
        ]);

        //dd($request);
        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $transaction_code = $request->transaction_code;

            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_code" => $transaction_code,
                    "total_bags" => $variety_details[1],
                    "variety" => $variety_details[0],
                    "from_dop" => $request->from_dop_name,
                    "to_dop" => $request->to_dop_name,
                    "date_recorded" => date("Y-m-d H:i:s"),
                    "recorded_by" => Auth::user()->username
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                $total_bags += $variety_details[1];
            }

            DB::commit();

            Session::flash('success', 'Successfully sent actual date for the selected seed schedule.');
            return redirect()->route('rcep.google_sheet.actual', $transaction_code);

        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            //dd($ex);

            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.actual', $transaction_code);
        }
    }

    public function actualInventoryLgu(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'from_dop_name' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $transaction_code = $request->transaction_code;

            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_code" => $transaction_code,
                    "total_bags" => $variety_details[1],
                    "variety" => $variety_details[0],
                    "from_dop" => $request->from_dop_name,
                    "to_dop" => "N/A",
                    "date_recorded" => date("Y-m-d H:i:s"),
                    "recorded_by" => Auth::user()->username
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                $total_bags += $variety_details[1];
            }

            DB::commit();

            Session::flash('success', 'Successfully sent actual date for the selected seed schedule.');
            return redirect()->route('rcep.google_sheet.actual', $transaction_code);

        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            //dd($ex);
            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.actual', $transaction_code);
        }
    }


    public function actualInventoryTransferred(Request $request){
        $this->validate($request, [
            'seed_variety_str' => 'required',
            'from_dop_name' => 'required',
            'to_dop_name' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $variety_list = rtrim($request->seed_variety_str,"|");
            $variety_list = explode("|", $request->seed_variety_str);
            $transaction_code = $request->transaction_code;

            $variety_list = array_filter($variety_list);
            $total_bags = 0;
            foreach($variety_list as $variety){
                $variety_details = explode("&", $variety);
                $data = array(
                    "transaction_code" => $transaction_code,
                    "total_bags" => $variety_details[1],
                    "variety" => $variety_details[0],
                    "from_dop" => $request->from_dop_name,
                    "to_dop" => $request->to_dop_name,
                    "date_recorded" => date("Y-m-d H:i:s"),
                    "recorded_by" => Auth::user()->username
                );
        
                DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_seed_actual')->insert($data);

                $total_bags += $variety_details[1];
            }

            DB::commit();

            
            Session::flash('success', 'Successfully sent actual date for the selected seed schedule.');
            return redirect()->route('rcep.google_sheet.actual', $transaction_code);

        } catch (\Exception $ex) {
            DB::rollback();
            //return "sql_error";
            //dd($ex);
            
            Session::flash('error_msg', 'An error occured while executing your transaction, please try again.');
            return redirect()->route('rcep.google_sheet.actual', $transaction_code);
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

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Session;
use Auth;
use DB;

class EditDeliveryController extends Controller
{
    public function home(){        
        $pending_deliveries = $this->load_deliveries();
        $varities = $this->load_seed_varieties();
        $dop_list = $this->load_dop_list();

        return view('delivery.edit_home')
            ->with('pending_deliveries', $pending_deliveries)
            ->with('varities', $varities)
            ->with('dop_list', $dop_list);
    }

    public function load_seed_varieties(){
        $seed_varities = DB::table('seed_seed.seed_characteristics')->groupBy('variety')->get();
        return $seed_varities;
    }

    public function load_dop_list(){
        return DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('prv_dropoff_id')->get();
    }

    public function load_deliveries(){
        $pending_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select('batchTicketNumber',DB::raw('SUM(totalBagCount) as total_bags'))
            ->where('is_cancelled', 0)
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
            ->limit(200)
            ->get();
        
        $pending = array();
        foreach($pending_deliveries as $row){
            $tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->groupBy('batchTicketNumber')
                ->get();

            if(count($tbl_actual_delivery) == 0){
                array_push($pending, array(
                    "batchTicketNumber" => $row->batchTicketNumber,
                    "total_bags" => $row->total_bags
                ));
            }
        }

        return $pending;
    }

    public function check_batch(Request $request){
        $batch_number = $request->batch_number;
        
        $inspected_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->groupBy('batchTicketNumber')
            ->get();

        if(count($inspected_data) > 0){
            return "already_inspected";
        }else{
            
            $delivery_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('batchTicketNumber', $batch_number)
                ->groupBy('batchTicketNumber')
                ->first();

            if($delivery_data->is_cancelled == 0){
                $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $delivery_data->coopAccreditation)->value('coopName');
                $seedtag_arr = array();
                $seedtags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                    ->select('seedTag', 'seedVariety', 'totalBagCount')
                    ->where('batchTicketNumber', $batch_number)
                    ->get();

                foreach($seedtags as $row){
                    array_push($seedtag_arr, "".$row->seedTag." | ".$row->seedVariety." (".$row->totalBagCount." bags)");
                }

                return array(
                    "batch_number" => $delivery_data->batchTicketNumber,
                    "seed_coop" => $coop_name,
                    "region" => $delivery_data->region,
                    "province" => $delivery_data->province,
                    "municipality" => $delivery_data->municipality,
                    "dop" => $delivery_data->dropOffPoint,
                    "seeds_list" => $seedtag_arr,
                    "prv_dropoff_id" => $delivery_data->prv_dropoff_id
                );
            }else{
                return "cancelled_delivery";
            }
            
        }
    }

    public function get_seedtag_info(Request $request){
        $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('is_cancelled', 0)
            ->where('batchTicketNumber', $request->batch_number)
            ->where('seedTag', $request->seed_tag)
            ->first();

        return array(
            "seedTag" => $delivery->seedTag,
            "seedVariety" => $delivery->seedVariety,
            "totalBagCount" => $delivery->totalBagCount,
            "deliveryId" => $delivery->deliveryId
        );
    }

    public function update_seedtag_info(Request $request){

        DB::beginTransaction();
        try {  
            //1. check seedtag volume in database
            $seedtag_data_tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('is_cancelled', 0)
                ->where('seedTag', $request->seed_tag)
                ->where('deliveryId', '!=', $request->tbl_delivery_id)
                ->get();

            $seedtag_total = 0;

            if(count($seedtag_data_tbl_delivery) > 0){
                foreach($seedtag_data_tbl_delivery as $row){
                    $seedtag_total += $row->totalBagCount;
                }
            }else{
                $seedtag_data_tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('seedTag', $request->seed_tag)
                    ->get();

                if(count($seedtag_data_tbl_actual_delivery) > 0){
                    foreach($seedtag_data_tbl_actual_delivery as $row2){
                        $seedtag_total += $row2->totalBagCount;
                    }
                }
            }


            //2. check if volume for edit and record from database exceeds 200 bags
            $volume_after_edit = $seedtag_total + $request->total_bag;
            if($volume_after_edit > 200){
                return "exceeded_max_volume_per_lot";
                DB::rollback();
            }else{

                //save-step1 - update data in tbl_delivery
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('deliveryId', $request->tbl_delivery_id)
                ->update([
                    'seedTag' => $request->seed_tag,
                    'seedVariety' => $request->seed_variety,
                    'totalBagCount' => $request->total_bag
                ]);

                //save-step2 - update data in tbl_delivery_transaction
                $delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                    ->select('batchTicketNumber',DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('is_cancelled', 0)
                    ->where('batchTicketNumber', $request->batch_number)
                    ->groupBy('batchTicketNumber')
                    ->first();

                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
                ->where('batchTicketNumber', $request->batch_number)
                ->where('status', 1)
                ->update([
                    'instructed_delivery_volume' => $delivery->total_bags,
                ]);

                DB::connection('mysql')->table('lib_logs')
                ->insert([
                    'category' => 'EDIT_SEEDTAG',
                    'description' => 'ACTION (EDIT_SEEDTAG) tiggerred and successful, batch number: '.$request->batch_number.", seed tag: ".$request->seed_tag.", seed variety: ".$request->seed_variety.", bag count: ".$request->total_bag." total bags in transaction (batch selected): ".$delivery->total_bags,
                    'author' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);
                DB::commit();

                return "edit_success";
            }
            

        } catch (\Exception $e) {
            DB::rollback();
            return "edit_sql_error";
            //dd($e);
        }
    }

    public function update_batch_dop(Request $request){
        DB::beginTransaction();
        try {
            $dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('prv_dropoff_id', $request->dop_id)->groupBy('prv_dropoff_id')->first();

            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('batchTicketNumber', $request->batch_number)
            ->update([
                'region' => $dop->region,
                'province' => $dop->province,
                'municipality' => $dop->municipality,
                'dropOffPoint' => $dop->dropOffPoint,
                'prv_dropoff_id' => $dop->prv_dropoff_id,
                'prv' => $dop->prv
            ]);

            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
            ->where('batchTicketNumber', $request->batch_number)
            ->where('status', 1)
            ->update([
                'prv_dropoff_id' => $dop->prv_dropoff_id,
            ]);

            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'EDIT_DOP',
                'description' => 'ACTION (EDIT_DOP) tiggerred and successful, batch number: '.$request->batch_number.", dropoff ID: ".$dop->prv_dropoff_id,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}

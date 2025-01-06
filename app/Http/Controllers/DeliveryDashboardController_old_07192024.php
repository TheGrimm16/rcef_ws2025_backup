<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;
use App\SeedCooperatives;
use App\SeedProducers;
use App\Transplant;
use App\Seeds;
use App\SeedGrowers;
use App\DeliveryInspect;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\DropoffPoints;

use PDFTIM;
use DB;
use Excel;

class DeliveryDashboardController extends Controller
{
    
    /*public function index()
    {
        return view('deliverydashboard.index');
    }*/

    public function get_regions() {
        $data = DB::table($GLOBALS['season_prefix']."sdms_db_dev" . '.lib_regions')
                ->select('*')
                ->where('regDesc', '!=', 'NCR')
                ->orderBy('order')
                ->get();
        return $data;
    }

    public function get_coops($id) {
        $coop = DB::connection("seed_coop_db")
            ->table("tbl_commitment_regional")
            ->select('tbl_cooperatives.coopName', 'tbl_cooperatives.regionId', DB::raw('SUM(tbl_commitment_regional.volume) as total_value'), 'tbl_cooperatives.accreditation_no', 'tbl_cooperatives.coopId', 'tbl_cooperatives.accreditation_no')
            ->join('tbl_cooperatives', 'tbl_commitment_regional.coop_Id', '=', 'tbl_cooperatives.coopId')
            ->where('regionId', $id)
            // ->where("isBuffer", 0)
            ->groupBy("coop_Id")
            ->orderBy('coopName')
            ->get();
 
            //   dd($coop);
        return $coop;
    }

    public function index()
    {
        $regions = $this->get_regions();
        return view('deliverydashboard.coop_deliveries.home')
            ->with("regions", $regions);
    }
    
    public function get_coop_name(Request $request){
        $coop_name = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('coopId', $request->coop_id)->value('coopName');
        return $coop_name;
    }

    public function getCoop_in_region(Request $request){
        $coop_list = $this->get_coops($request->region);
        $delivery = new DeliveryInspect();
        // dd($coop_list);
        $data_array = array();
        foreach($coop_list as $row){
            $coop_confirmed_deliveries = DB::connection('delivery_inspection_db')->table("tbl_delivery")
                ->select(DB::raw('SUM(tbl_delivery.totalBagCount) as total_confirmed'))
                ->where('tbl_delivery.is_cancelled', '=', '0')
                ->where('tbl_delivery.coopAccreditation', '=', $row->accreditation_no)
                ->where('tbl_delivery.isBuffer', 0)
                ->value('total_confirmed');

            $coop_delivery_batches = DB::connection('delivery_inspection_db')->table("tbl_delivery")
                ->select('batchTicketNumber', 'seedVariety')
                ->where('tbl_delivery.is_cancelled', '=', '0')
                ->where('tbl_delivery.coopAccreditation', '=', $row->accreditation_no)
                ->where('tbl_delivery.isBuffer', 0)
                ->groupBy('batchTicketNumber')
                ->get();
                



            

        
                // dd($coop_delivery_batches);
            $total_inspected = 0;
        
            $total_forwarded = 0;
            foreach($coop_delivery_batches as $batch_row){
                $total_inspected += $delivery->gad_total($batch_row->batchTicketNumber);

                $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        // ->select('batchTicketNumber')
                        ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                        ->where('is_transferred', 1)
                        ->where('transferCategory', 'T')
                        ->sum('totalBagCount');
                $total_inspected += $is_transfer_T;

                if($is_transfer_T>0){
                    $t_of_t =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        ->select('batchTicketNumber')
                        ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                        ->where('transferCategory', 'T')
                        ->groupby("batchTicketNumber")
                        ->get();

                    foreach($t_of_t as $trans){
                        $re_trans = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        // ->select('batchTicketNumber')
                        ->where('remarks','like', '%'.$trans->batchTicketNumber.'%')
                        ->where('is_transferred', 1)
                        ->where('transferCategory', 'T')
                        ->sum('totalBagCount');

                         $total_inspected += $re_trans;
                         
                    }


                       
                }

                
                
                
                
                
                
                
                
                // $nxt_season_data = DB::connection("nxt_inspection_db")->table("tbl_actual_delivery")
                //     ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                //     ->where('is_transferred', 1)
                //     ->sum("totalBagCount");
                
                $nxt_season_data = 0;
                
                $total_forwarded += $nxt_season_data;
                
            }
            // dd($total_inspected,$total_inspected2);
            


            
            $coop_delivery_replacement = DB::connection('delivery_inspection_db')->table("tbl_delivery")
                ->select('batchTicketNumber', 'seedVariety')
                ->where('tbl_delivery.is_cancelled', '=', '0')
                ->where('tbl_delivery.coopAccreditation', '=', $row->accreditation_no)
                ->where('tbl_delivery.isBuffer', 9)
                ->groupBy('batchTicketNumber')
                ->get();

        
            $total_inspected_replacement = 0;
            $total_forwarded_replacement = 0;
            foreach($coop_delivery_replacement as $batch_row_replacement){
                $total_inspected_replacement += $delivery->gad_total($batch_row_replacement->batchTicketNumber);

                $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        ->select('batchTicketNumber')
                        ->where('remarks','like', '%'.$batch_row_replacement->batchTicketNumber.'%')
                        ->where('is_transferred', 1)
                        //->where('transferCategory', 'T')
                        ->sum('totalBagCount');
                $total_inspected_replacement += $is_transfer_T;
            
                
                // $nxt_season_data = DB::connection("nxt_inspection_db")->table("tbl_actual_delivery")
                //     ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                //     ->where('is_transferred', 1)
                //     ->sum("totalBagCount");

                $nxt_season_data = 0;

                $total_forwarded += $nxt_season_data;
            
            }




            $coop_arr = array(
                "cop_id" => $row->coopId,
                "coop_name" => $row->coopName,
                "coop_accre" => $row->accreditation_no,
                "total_commit" => number_format($row->total_value),
                "total_confirmed" => number_format($coop_confirmed_deliveries),
                "total_inspected" => number_format($total_inspected),
                "total_forwarded" => number_format($total_forwarded),
                "confirmed_replacement" => number_format($total_inspected_replacement)
            );
            array_push($data_array, $coop_arr);
        }

        return $data_array;
    }

    public function get_delivery_list_old(Request $request){
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $request->coop_accre)
            ->groupBy('batchTicketNumber', 'seedVariety')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){
            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $request->coop_accre)
                ->where('seedVariety', $batch_row->seedVariety)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();

            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->sum('totalBagCount');
                
                $inspected_bags = number_format($inspected_bags)." bag(s)";

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = "N/A";
                $batch_status = "N/A";
            }
                
            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'confirmed' => number_format($confirmed_bags)." bag(s)",
                'inspected' => $inspected_bags,
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status
            );

            array_push($return_arr, $batch_data);
        }
        
        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
            ->make(true);
    }

   
       public function get_delivery_list(Request $request){
       
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $request->coop_accre)
            ->groupBy('batchTicketNumber', 'seedVariety')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){
            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $request->coop_accre)
                ->where('seedVariety', $batch_row->seedVariety)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();

            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->sum('totalBagCount');
                
                $inspected_bags = number_format($inspected_bags)." bag(s)";

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = "N/A";
                $batch_status = "N/A";
            }

            $btn = "";
            $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
               
           


            if($batch_row->isBuffer == 1){
                $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 0)
                    ->first();

                $label = "(replacement)";
            }else{
                $label = "";
            }

            



                if(count($is_transfer_W) > 0 OR count($is_transfer_T) > 0){
                    $Transfertype = '';
                    if(count($is_transfer_W) > 0)$Transfertype .= 'W';
                    if(count($is_transfer_T) > 0)$Transfertype .= 'T';


                          $btn = "<a href='#' data-dopid='' data-new_batch = ''  data-batch=". $batch_row->batchTicketNumber." data-seedVariety='".$batch_row->seedVariety."' data-toggle='modal' data-transfercategory='".$Transfertype."' data-target='#show_coop_transfer_modal' class='btn btn-success btn-xs'><i style='height:0%' class='fa fa-book'>Transfer Info ".$label."</i></a>";
                }else{
                     $btn = "<a href='#' class='btn btn-dark btn-xs' disabled><i style='height:0%' class='fa fa-book'>Transfer Info ".$label. "</i></a>";
                }
             
                $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'confirmed' => number_format($confirmed_bags)." bag(s)",
                'inspected' => $inspected_bags,
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                "action" => $btn,
            );

           array_push($return_arr, $batch_data);

        }



        //PREVIOUS SEASON 
    $prevBatch = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs as l')
                ->select('l.*')
                ->where('l.coop_accreditation', $request->coop_accre)
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as a', 'a.batchTicketNumber', '=', 'l.new_batch_number')
                ->groupBy('l.new_batch_number')
                ->get();


                //$return_arr = array();
                foreach ($prevBatch as $value) {
                   $ls_batchNumber = $value->batch_number;

                        $seed_variety_arr =explode("|", $value->seed_variety);
                        

                            foreach ($seed_variety_arr as $key => $seed_variety_arr) {
                                  
                                if($seed_variety_arr != ""){
                                     $seed_variety_arr = trim($seed_variety_arr);
                                  $inspected = 0;
                                    $cat = "P";

                                $inspected = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                    ->where('batchTicketNumber', $value->new_batch_number)
                                    ->where('seedVariety', $seed_variety_arr)
                                    ->where('transferCategory', 'P')
                                    ->where('is_transferred', 1)
                                    ->sum('totalBagCount');
                                        
                                $checkIfPartial = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from batch: '.$value->new_batch_number.'%')
                                    ->where('transferCategory', "T")
                                    ->where('is_transferred', 1)
                                    ->first();

                                $checkIfWhole = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                    ->where('batchTicketNumber', $value->new_batch_number)
                                    ->where('transferCategory', "W")
                                    ->where('is_transferred', 1)
                                    ->first();

                                if(count($checkIfWhole)>0){
                                    $cat .= "W";
                                    $inspected += DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                        ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                        ->where('batchTicketNumber', $value->new_batch_number)
                                        ->where('seedVariety', $seed_variety_arr)
                                        ->where('transferCategory', "W")
                                        ->where('is_transferred', 1)
                                        ->sum('totalBagCount');

                                }
                        
                                if(count($checkIfPartial)>0){
                                    $cat .= "T" ;

                                    $inspected += DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from batch: '.$value->new_batch_number.'%')
                                    ->where('seedVariety', $seed_variety_arr)
                                    ->where('transferCategory', "T")
                                    ->where('is_transferred', 1)
                                    ->sum('totalBagCount');
                                }

                               $btn = "<a href='#' data-dopid=".$value->destination_dop_id." data-new_batch = '".$value->new_batch_number."' data-batch=". $ls_batchNumber." data-seedvariety='".$seed_variety_arr."' data-toggle='modal' data-transfercategory='".$cat."' data-target='#show_coop_transfer_modal' class='btn btn-success btn-xs'><i style='height:0%' class='fa fa-book'>Transfer Info</i></a>";

                                $confirmed_bags = "From Previous Season";
                             
                                $inspected_bags = number_format($inspected)." bag(s)";
                                




                                $batch_status = "Transferred";

                                $dropOffPoint =DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                                    ->where('prv_dropoff_id',$value->destination_dop_id)->first();

                                    if(count($dropOffPoint)>0){
                                        $dropOffPointData = $dropOffPoint->dropOffPoint;
                                        $dropOffPointRegion = $dropOffPoint->region;
                                    }else{
                                        $dropOffPointData = "N/A";
                                        $dropOffPointRegion = "N/A";
                                    }


                                $batch_data = array(
                                                'batchTicketNumber' => $ls_batchNumber,
                                                'coopAccreditation' => $request->coop_accre,
                                                'seedVariety' => $seed_variety_arr,
                                                'dropOffPoint' => $dropOffPointData,
                                                'region' => $dropOffPointRegion,
                                                'province' => $value->destination_province,
                                                'municipality' => $value->destination_municipality,
                                                'confirmed' => $confirmed_bags,
                                                'inspected' => $inspected_bags,
                                                'deliveryDate' => date("Y-m-d", strtotime($value->date_created)),
                                                'batch_status' => $batch_status,
                                                 "action" => $btn,
                                            );

                                            array_push($return_arr, $batch_data);
                                }
                                 
                            }
                            
                }  //FOREACH        
        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
            ->make(true);
    }





     public function getPreviousData($batch, $new_batch, $dopid, $seedVariety){
         $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where('remarks', 'like', '%transferred from previous season batch: '.$batch)
                            ->where('batchTicketNumber', $new_batch)
                            ->where('is_transferred', 1)
                            ->where('transferCategory', 'P')
                            ->where('prv_dropoff_id', $dopid)
                            ->where('seedVariety', $seedVariety)
                            ->orderBy('batchTicketNumber', 'ASC')
                            ->get();



          $return_arr = array();
            foreach ($batch_deliveries as $batch_row) {
                $oldBatchTicketNumber = $batch;                    
                $batchTicketNumber = $batch_row->batchTicketNumber;

                    if($batch_row->transferCategory == 'P'){
                             $logInfo = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs as t')
                                ->select('t.batch_number', 't.origin_province', 't.origin_municipality', 'l.dropOffPoint as dopName')
                                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as l', 'l.prv_dropoff_id' ,'=','t.origin_dop_id')
                                ->where('batch_number',$oldBatchTicketNumber)
                                ->where('destination_dop_id', $batch_row->prv_dropoff_id)
                                ->where('seed_variety', 'like', '%'.$batch_row->seedVariety.'%')
                                ->first();

                                if(count($logInfo)>0){
                                    $originData = $logInfo->origin_province.', '.$logInfo->origin_municipality.' -> '.$logInfo->dopName;
                                }else{
                                    $originData = " NO INFO ON LOGS";
                                }

                                    $destData = $batch_row->province.', '.$batch_row->municipality.' -> '.$batch_row->dropOffPoint;
                               
                        $categoryTrans = "PREVIOUS SEASON TRANSFER";
                    }
                    


                /*
                if($batch_row->seedType=='I'){
                    $sdt = 'Inventory Seeds';
                } else if($batch_row->seedType == 'B'){
                    $sdt = 'Buffer Seeds';
                } else{
                    $sdt = '';
                } */

                $batch_data = array(
                'batch_num' =>$batchTicketNumber,
                'origin' => $originData,
                'destination' => $destData,
                'seedVariety' => $batch_row->seedVariety,
                'seedTag' => $batch_row->seedTag,
                'seedType' => "",
                'bags' => $batch_row->totalBagCount. ' bag(s)',
                'dateCreated' => date("Y-m-d", strtotime($batch_row->dateCreated)),
                'transferType' => $categoryTrans,
            );

            array_push($return_arr, $batch_data);
            }
            
            return $return_arr;                   
    }

    
     public function getWholeData($batch,$new_batch,$seed_variety,$seedTag){
        
        if($new_batch==""){
         $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where('batchTicketNumber', $batch)
                            ->where('seedVariety', $seed_variety)
                            ->where('transferCategory', 'W')
                            ->where('is_transferred', 1)
                            ->where('seedTag', 'like', '%'.$seedTag.'%')
                            ->orderBy('batchTicketNumber', 'ASC')
                            ->get();

           
          $return_arr = array();
            foreach ($batch_deliveries as $batch_row) {

                $batchTicketNumber = $batch_row->batchTicketNumber;

                    if($batch_row->transferCategory == 'W'){
                          $originInfo = DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs as t')
                                ->select('t.batch_number', 't.origin_province', 't.origin_municipality', 'l.dropOffPoint as dopName')
                                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as l', 'l.prv_dropoff_id' ,'=','t.origin_dop_id')
                                ->where('batch_number',$batchTicketNumber)
                                ->where('seed_variety', 'ALL_SEEDS_TRANSFER')
                                ->first();

                                if(count($originInfo)>0){
                                    $originData = $originInfo->origin_province.', '.$originInfo->origin_municipality.' -> '.$originInfo->dopName;
                                }else{
                                    $originData = " NO INFO ON LOGS";
                                }

                            $destInfo = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->select('province','municipality','dropOffPoint as dopName')
                                ->where('batchTicketNumber',$batchTicketNumber)
                                ->where('transferCategory', $batch_row->transferCategory)
                                ->first();

                                if(count($destInfo)>0){
                                    $destData = $destInfo->province.', '.$destInfo->municipality.' -> '.$destInfo->dopName;
                                }else{
                                    $destData = "";
                                }
                  
                        $categoryTrans = "WHOLE TRANSFER";
                    }
                    
                
                if($batch_row->seedType=='I'){
                    $sdt = 'Inventory Seeds';
                } else if($batch_row->seedType == 'B'){
                    $sdt = 'Buffer Seeds';
                } else{
                    $sdt = '';
                }

                $batch_data = array(
                'batch_num' =>$batchTicketNumber,
                'origin' => $originData,
                'destination' => $destData,
                'seedVariety' => $batch_row->seedVariety,
                'seedTag' => $batch_row->seedTag,
                'seedType' => $sdt,
                'bags' => $batch_row->totalBagCount. ' bag(s)',
                'dateCreated' => date("Y-m-d", strtotime($batch_row->dateCreated)),
                'transferType' => $categoryTrans,
            );

            array_push($return_arr, $batch_data);
            }
            
       
            }

            //IF HAS NEW BATCH #
            else{
                        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where('remarks', 'like', '%transferred from previous season batch: '.$batch)
                            ->where('batchTicketNumber', $new_batch)
                            ->where('is_transferred', 1)
                            ->where('transferCategory', 'W')
                            ->where('seedVariety', $seed_variety)
                            ->orderBy('batchTicketNumber', 'ASC')
                            ->get();

                  $return_arr = array();
                    foreach ($batch_deliveries as $batch_row) {

                        $batchTicketNumber = $batch_row->batchTicketNumber;

                            if($batch_row->transferCategory == 'W'){
                                  $originInfo = DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs as t')
                                        ->select('t.batch_number', 't.origin_province', 't.origin_municipality', 'l.dropOffPoint as dopName')
                                        ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as l', 'l.prv_dropoff_id' ,'=','t.origin_dop_id')
                                        ->where('batch_number',$batchTicketNumber)
                                        ->where('seed_variety', 'ALL_SEEDS_TRANSFER')
                                        ->first();

                                        if(count($originInfo)>0){
                                            $originData = $originInfo->origin_province.', '.$originInfo->origin_municipality.' -> '.$originInfo->dopName;
                                        }else{
                                            $originData = " NO INFO ON LOGS";
                                        }
                                            $destData = $batch_row->province.', '.$batch_row->municipality.' -> '.$batch_row->dropOffPoint;
                                $categoryTrans = "WHOLE TRANSFER";
                            }
                            
                        
                        if($batch_row->seedType=='I'){
                            $sdt = 'Inventory Seeds';
                        } else if($batch_row->seedType == 'B'){
                            $sdt = 'Buffer Seeds';
                        } else{
                            $sdt = '';
                        }

                        $batch_data = array(
                        'batch_num' =>$batchTicketNumber,
                        'origin' => $originData,
                        'destination' => $destData,
                        'seedVariety' => $batch_row->seedVariety,
                        'seedTag' => $batch_row->seedTag,
                        'seedType' => $sdt,
                        'bags' => $batch_row->totalBagCount. ' bag(s)',
                        'dateCreated' => date("Y-m-d", strtotime($batch_row->dateCreated)),
                        'transferType' => $categoryTrans,
                    );

                    array_push($return_arr, $batch_data);

                    }
            
            }

                  return $return_arr;


    }


    public function getNextSeasonData($batch, $seed_variety, $seedTag){



        //  $batch_deliveries = DB::connection('nxt_inspection_db')->table('tbl_actual_delivery')
        //                     ->where('remarks', 'like', '%'.$batch.'%')
        //                     ->where('seedVariety', $seed_variety)
        //                     ->where('is_transferred', 1)
        //                     ->where('transferCategory', 'P')
        //                     ->where('seedTag', 'like', '%'.$seedTag.'%')
        //                      ->orderBy('batchTicketNumber', 'ASC')
        //                     ->get();

      
        $batch_deliveries = array();

          $return_arr = array();
            foreach ($batch_deliveries as $batch_row) {

                $batchTicketNumber = $batch_row->batchTicketNumber; //NEW BATCH TICKET
                $oldBatchTicketNumber = trim(str_replace("transferred from previous season batch: ", "", $batch_row->remarks));
                     $originData = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                        ->select("province", "municipality", "dropOffPoint", "isBuffer")
                        ->where("batchTicketNumber", $batch)
                        ->first();

                    if($originData->isBuffer=='1'){
                        $sdt = 'Buffer Seeds';
                    } else{
                        $sdt = 'Inventory Seeds';
                    }


                        if(count($originData)>0){
                             $originDataLoc = $originData->province.', '.$originData->municipality.' -> '.$originData->dropOffPoint; 
                        }else{
                             $originDataLoc = " NO INFO ON LOGS";
                        } 
                            $destData = $batch_row->province.', '.$batch_row->municipality.' -> '.$batch_row->dropOffPoint;
                            $categoryTrans = "TRANSFERRED TO NEXT SEASON";
                            
                    
                
                

                $batch_data = array(
                'batch_num' =>$batchTicketNumber,
                'origin' => $originDataLoc,
                'destination' => $destData,
                'seedVariety' => $seed_variety,
                'seedTag' => $seedTag,
                'seedType' => $sdt,
                'bags' => $batch_row->totalBagCount. ' bag(s)',
                'dateCreated' => date("Y-m-d", strtotime($batch_row->dateCreated)),
                'transferType' => $categoryTrans,
            );

            array_push($return_arr, $batch_data);
            }
            
            return $return_arr;                   
    


    }


    public function getPartialData($batch,$seed_variety,$seedTag){

         $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where('remarks', 'like', '%'.$batch.'%')
                            ->where('seedVariety', $seed_variety)
                            ->where('is_transferred', 1)
                            ->where('transferCategory', 'T')
                            ->where('seedTag', 'like', '%'.$seedTag.'%')
                             ->orderBy('batchTicketNumber', 'ASC')
                            ->get();

          $return_arr = array();
            foreach ($batch_deliveries as $batch_row) {

                $batchTicketNumber = $batch_row->batchTicketNumber; //NEW BATCH TICKET
                $oldBatchTicketNumber = trim(str_replace("transferred from batch:", "", $batch_row->remarks));

                $bags = 0;
                  
                $batch_deliveries_retransfer = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where('remarks', 'like', '%'.$batchTicketNumber.'%')
                            ->where('seedVariety', $seed_variety)
                            ->where('is_transferred', 1)
                            ->where('transferCategory', 'T')
                            ->where('seedTag', 'like', '%'.$seedTag.'%')
                            ->sum('totalBagCount');
                     if($batch_row->transferCategory == 'T'){
                                  $originInfo = DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs as t')
                                        ->select('t.batch_number', 't.origin_province', 't.origin_municipality', 'l.dropOffPoint as dopName')
                                        ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as l', 'l.prv_dropoff_id' ,'=','t.origin_dop_id')
                                        ->where('batch_number',$oldBatchTicketNumber)
                                        ->where('destination_dop_id', $batch_row->prv_dropoff_id)
                                        ->where('seed_variety', 'PARTIAL_SEEDS_TRANSFER')
                                        ->first();

                                        if(count($originInfo)>0){
                                            $originData = $originInfo->origin_province.', '.$originInfo->origin_municipality.' -> '.$originInfo->dopName;
                                        }else{
                                            $originData = " NO INFO ON LOGS";
                                        }
                                            $destData = $batch_row->province.', '.$batch_row->municipality.' -> '.$batch_row->dropOffPoint;
                                $categoryTrans = "PARTIAL TRANSFER";
                            }
                    
                
                if($batch_row->seedType=='I'){
                    $sdt = 'Inventory Seeds';
                } else if($batch_row->seedType == 'B'){
                    $sdt = 'Buffer Seeds';
                } else{
                    $sdt = '';
                }

                if($batch_deliveries_retransfer)
                {
                    $bags = $batch_row->totalBagCount+$batch_deliveries_retransfer;
                }
                else
                {
                    $bags = $batch_row->totalBagCount;
                }

                $batch_data = array(
                'batch_num' =>$batchTicketNumber,
                'origin' => $originData,
                'destination' => $destData,
                'seedVariety' => $batch_row->seedVariety,
                'seedTag' => $batch_row->seedTag,
                'seedType' => $sdt,
                'bags' => $bags. ' bag(s)',
                'dateCreated' => date("Y-m-d", strtotime($batch_row->dateCreated)),
                'transferType' => $categoryTrans,
            );

            array_push($return_arr, $batch_data);
            }
            
            return $return_arr;                   
    }


     public function getTransferList(Request $request){
        $return_arr = array();
            if($request->tc == 'WT'){
                $cat = substr($request->tc,0,1);
                $cat2 = substr($request->tc,1,1);
            }elseif($request->tc == 'PT'){
                $cat = substr($request->tc,0,1);
                $cat2 = substr($request->tc,1,1);
            }elseif($request->tc == 'PWT'){
                $cat = substr($request->tc,0,1);
                $cat2 = substr($request->tc,1,1);
                $cat3 = substr($request->tc,2,1);      
            }
            else{
                $cat =$request->tc;  
            }

        if($cat == 'W'){
            $arr = $this->getWholeData($request->batch,$request->new_batch,$request->seedVariety,"%");

            foreach ($arr as $key => $value) {
                $bt = $arr[$key]['batch_num'];
                $or = $arr[$key]['origin'];
                $dt = $arr[$key]['destination'];
                $sv = $arr[$key]['seedVariety'];
                $st = $arr[$key]['seedTag'];
                $sdt = $arr[$key]['seedType'];
                $bg = $arr[$key]['bags'];
                $dc = $arr[$key]['dateCreated'];
                $tt = $arr[$key]['transferType'];
                 $batch_data = array(
                'batch_num' =>$bt,
                'origin' => $or,
                'destination' => $dt,
                'seedVariety' => $sv,
                'seedTag' => $st,
                'seedType' => $sdt,
                'bags' => $bg,
                'dateCreated' =>$dc,
                'transferType' => $tt,
            );

            array_push($return_arr, $batch_data);
            }
        
                    if(isset($cat2)){
                            $arr = $this->getPartialData($request->batch,$request->seedVariety,'%');

                            foreach ($arr as $key => $value) {
                                $bt = $arr[$key]['batch_num'];
                                $or = $arr[$key]['origin'];
                                $dt = $arr[$key]['destination'];
                                $sv = $arr[$key]['seedVariety'];
                                $st = $arr[$key]['seedTag'];
                                $sdt = $arr[$key]['seedType'];
                                $bg = $arr[$key]['bags'];
                                $dc = $arr[$key]['dateCreated'];
                                $tt = $arr[$key]['transferType'];
                                 $batch_data = array(
                                'batch_num' =>$bt,
                                'origin' => $or,
                                'destination' => $dt,
                                'seedVariety' => $sv,
                                'seedTag' => $st,
                                'seedType' => $sdt,
                                'bags' => $bg,
                                'dateCreated' =>$dc,
                                'transferType' => $tt,
                            );

                            array_push($return_arr, $batch_data);
                            } 
                    }

        } else if($cat == 'T'){
            $arr = $this->getPartialData($request->batch,$request->seedVariety,'%');

                foreach ($arr as $key => $value) {
                    $bt = $arr[$key]['batch_num'];
                    $or = $arr[$key]['origin'];
                    $dt = $arr[$key]['destination'];
                    $sv = $arr[$key]['seedVariety'];
                    $st = $arr[$key]['seedTag'];
                    $sdt = $arr[$key]['seedType'];
                    $bg = $arr[$key]['bags'];
                    $dc = $arr[$key]['dateCreated'];
                    $tt = $arr[$key]['transferType'];
                     $batch_data = array(
                    'batch_num' =>$bt,
                    'origin' => $or,
                    'destination' => $dt,
                    'seedVariety' => $sv,
                    'seedTag' => $st,
                    'seedType' => $sdt,
                    'bags' => $bg,
                    'dateCreated' =>$dc,
                    'transferType' => $tt,
                );

                array_push($return_arr, $batch_data);
                } 
        } else if($cat == 'P'){
            $arr = $this->getPreviousData($request->batch, $request->new_batch, $request->prv_dropoff_id, $request->seedVariety);

                foreach ($arr as $key => $value) {
                    $bt = $arr[$key]['batch_num'];
                    $or = $arr[$key]['origin'];
                    $dt = $arr[$key]['destination'];
                    $sv = $arr[$key]['seedVariety'];
                    $st = $arr[$key]['seedTag'];
                    $sdt = $arr[$key]['seedType'];
                    $bg = $arr[$key]['bags'];
                    $dc = $arr[$key]['dateCreated'];
                    $tt = $arr[$key]['transferType'];
                     $batch_data = array(
                    'batch_num' =>$bt,
                    'origin' => $or,
                    'destination' => $dt,
                    'seedVariety' => $sv,
                    'seedTag' => $st,
                    'seedType' => $sdt,
                    'bags' => $bg,
                    'dateCreated' =>$dc,
                    'transferType' => $tt,
                );

                array_push($return_arr, $batch_data);
            }

                if(isset($cat2)){

                        if($cat2=="T"){
                            $arr = $this->getPartialData($request->new_batch,$request->seedVariety,'%');
                            
                            foreach ($arr as $key => $value) {
                                $bt = $arr[$key]['batch_num'];
                                $or = $arr[$key]['origin'];
                                $dt = $arr[$key]['destination'];
                                $sv = $arr[$key]['seedVariety'];
                                $st = $arr[$key]['seedTag'];
                                $sdt = $arr[$key]['seedType'];
                                $bg = $arr[$key]['bags'];
                                $dc = $arr[$key]['dateCreated'];
                                $tt = $arr[$key]['transferType'];
                                 $batch_data = array(
                                'batch_num' =>$bt,
                                'origin' => $or,
                                'destination' => $dt,
                                'seedVariety' => $sv,
                                'seedTag' => $st,
                                'seedType' => $sdt,
                                'bags' => $bg,
                                'dateCreated' =>$dc,
                                'transferType' => $tt,
                            );

                            array_push($return_arr, $batch_data);
                            } 
                        }
                        elseif($cat2=="W"){
                             $arr = $this->getWholeData($request->batch,$request->new_batch,$request->seedVariety,"%");
                            
                                foreach ($arr as $key => $value) {
                                    $bt = $arr[$key]['batch_num'];
                                    $or = $arr[$key]['origin'];
                                    $dt = $arr[$key]['destination'];
                                    $sv = $arr[$key]['seedVariety'];
                                    $st = $arr[$key]['seedTag'];
                                    $sdt = $arr[$key]['seedType'];
                                    $bg = $arr[$key]['bags'];
                                    $dc = $arr[$key]['dateCreated'];
                                    $tt = $arr[$key]['transferType'];
                                     $batch_data = array(
                                    'batch_num' =>$bt,
                                    'origin' => $or,
                                    'destination' => $dt,
                                    'seedVariety' => $sv,
                                    'seedTag' => $st,
                                    'seedType' => $sdt,
                                    'bags' => $bg,
                                    'dateCreated' =>$dc,
                                    'transferType' => $tt,
                                );

                                array_push($return_arr, $batch_data);
                                }

                                //INSERT T HERE
                                if(isset($cat3)){
                                    $arr = $this->getPartialData($request->new_batch,$request->seedVariety,'%');
                                    foreach ($arr as $key => $value) {
                                        $bt = $arr[$key]['batch_num'];
                                        $or = $arr[$key]['origin'];
                                        $dt = $arr[$key]['destination'];
                                        $sv = $arr[$key]['seedVariety'];
                                        $st = $arr[$key]['seedTag'];
                                        $sdt = $arr[$key]['seedType'];
                                        $bg = $arr[$key]['bags'];
                                        $dc = $arr[$key]['dateCreated'];
                                        $tt = $arr[$key]['transferType'];
                                         $batch_data = array(
                                        'batch_num' =>$bt,
                                        'origin' => $or,
                                        'destination' => $dt,
                                        'seedVariety' => $sv,
                                        'seedTag' => $st,
                                        'seedType' => $sdt,
                                        'bags' => $bg,
                                        'dateCreated' =>$dc,
                                        'transferType' => $tt,
                                    );

                                    array_push($return_arr, $batch_data);
                                    } 
                                }
                        }
                    }
        }  
        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
            ->make(true);
    }

    public function test(){
    //   $producer = new SeedProducers();
    //  return $accreditation_no = $producer->seed_producers_accreditation(1);
    $delivery = new DeliveryInspect();
    return $delivery->delivery_accreditation("CAR-R-2/19-RcI-6555", "NSIC 2012 Rc 300");
    }

    public function cooperatives(){

        $producer = new SeedProducers();
        $delivery = new DeliveryInspect();
        $cooperatives = new SeedCooperatives();

        $cooperatives_list = $cooperatives->seed_cooperatives();

        $table_data = array();

        foreach ($cooperatives_list as $item) {
            $total_bags_commited = 0;
            $total_bags_delivered = 0;
            $total_bags_inspected = 0;
            $total_bags_distributed = 0;
            $commitColor = "#dbdbdb";
            $confirmColor = "#dbdbdb";
            $inspectColor = "#dbdbdb";
            $distributeColor = "#dbdbdb";
            $dateCommit = array();
            $dateConfirm = array();
            $dateConfirmed = array();
            $dateInspect = array();
            $dateDistribute = array();
            $dateConfirmMax = "";
            $dateConfirmedMax = "";
            $dateInspectMax = "";
            $dateDistributeMax = "";
            $confirmStatus = 0;
            $confirmStatusArray = array();
            $inspectColorTmp = "";

            // get all accreditation of cooperative
            $accreditation_no = $producer->seed_producers_accreditation($item->coopId);
            $p_code = $delivery->get_prov_code($item->provinceId);
            /*foreach ($accreditation_no as $accno) {
                $total_bags_commited = $total_bags_commited + $accno->Area_Planted_in_ha * 200;
            }*/
			foreach ($accreditation_no as $accno) {
                $total_bags_commited = $total_bags_commited + $accno->total_value;
            }
            // get delivery by accreditation number
            $delivery_val = $delivery->delivery_accreditation($item->accreditation_no);

                if( count($delivery_val) > 0 ){

                    foreach ($delivery_val as $dv){

                        $total_bags_delivered = $total_bags_delivered + $dv->totalBagCount;

                        array_push( $dateConfirm, $dv->dateCreated);

                        array_push( $dateConfirmed, $dv->deliveryDate);

                        // for inspection
                        $status = $delivery->get_delivery_status($dv->batchTicketNumber);
                        if (count($status) > 0) {
                            if ($status->status == 1) {
                                $inspectColor = "#2E8B57";
                                $total_bags_inspected = $total_bags_inspected + $dv->totalBagCount;
                                $inspection = $delivery->get_inspection($dv->batchTicketNumber);
                                foreach ($inspection as $insp) {
                                    array_push( $dateInspect, $insp->dateInspected);
                                }
                            }elseif ($status->status == 0) {
                                $inspectColorTmp = "#FF7F50";
                            }
                        }



                    }
                    $batch = $delivery->group_batch($item->accreditation_no);

                    foreach ($batch as $bch) {
                        // for distribution
                        $distribution = $delivery->get_distribution($bch->batchTicketNumber,$p_code);
                        foreach ($distribution as $dist) {
                            $total_bags_distributed = $total_bags_distributed + $dist->bags;
                            array_push( $dateDistribute, $dist->date_released);
                        }
                    }

                    if (count($dateConfirm) > 0) {
                        $dateConfirmMax = max(array_map('strtotime', $dateConfirm));
                        $dateConfirmMax = date('D, M d Y', $dateConfirmMax);

                        $dateConfirmedMax = max(array_map('strtotime', $dateConfirmed));
                        $dateConfirmedMax = date('D, M d Y', $dateConfirmedMax);
                    }
                    if (count($dateInspect) > 0) {
                        $dateInspectMax = max(array_map('strtotime', $dateInspect));
                        $dateInspectMax = date('D, M d Y', $dateInspectMax);
                    }
                    if (count($dateDistribute) > 0) {
                        $dateDistributeMax = max(array_map('strtotime', $dateDistribute));
                        $dateDistributeMax = date('D, M d Y', $dateDistributeMax);
                    }

                }
                // if($dv->inspectionId) {
                //     $total_bags_inspected = $total_bags_inspected + $dv->totalBagsDelivered;
                //     array_push( $dateInspect, $dv->dateInspected);
                // }
                // if (count($dateInspect) > 0) {
                //     $dateInspectMax = max(array_map('strtotime', $dateInspect));
                //     $dateInspectMax = date('Y-m-j H:i:s', $dateInspectMax);
                // }

                if ($total_bags_commited > 0) {

                    if ($total_bags_delivered == $total_bags_commited) {
                        $commitColor = '#2E8B57';
                        $confirmColor = '#2E8B57';
                    }else if($total_bags_delivered > 0){
                        $confirmColor = "#FF7F50";
                        $commitColor = "#FF7F50";
                    }

                    // if ($total_bags_inspected == $total_bags_delivered && $total_bags_inspected > 0) {
                    //     $inspectColor = '#2E8B57';
                    // }else if($total_bags_inspected > 0 ){
                    //     $inspectColor = '#FF7F50';
                    // }

                }else{
                    if ($total_bags_delivered > 0) {
                        // seeds delivered but not commited
                        $confirmColor = '#7F0000';
                    }
                }
               if($inspectColorTmp != ""){
                    $inspectColor = $inspectColorTmp;
               }
                if ($total_bags_distributed == $total_bags_inspected && $total_bags_distributed != 0) {
                    $distributeColor = "#2E8B57";
                }elseif($total_bags_distributed > 0){
                    $distributeColor = "#FF7F50";
                }

            $data = array(
                'coopId' => $item->coopId,
                'name' => $item->coopName,
                'commitBags' => $total_bags_commited,
                'confirmBags' => $total_bags_delivered,
                'inspectBags' => $total_bags_inspected,
                'distributeBags' => $total_bags_distributed,
                'commitColor' => $commitColor,
                'confirmColor' => $confirmColor,
                'inspectColor' => $inspectColor,
                'distributeColor' => $distributeColor,
                'confirmDate'  => $dateConfirmMax,
                'confirmedDate' => $dateConfirmedMax,
                'inspectDate' => $dateInspectMax,
                'distributeDate' => $dateDistributeMax,
                'status'   => $confirmStatusArray
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return DataTables::of($table_data)
        ->addColumn('commit', function($table_data){
            return
            "<div class='monitor-con'>
                <div class='shape-con'>
                    <div class='shape-circle' style='background-color: ".$table_data['commitColor']."'>
                      ". $table_data['commitBags'] ."
                      <div class='unit-text'>bag/s</div>
                    </div>
                    <div class='shape-line' style='background-color: ".$table_data['commitColor']."'></div>
                    <div class='date-text-top'></div>
                    <div class='date-text-bottom'></div>
                </div>
            </div>";
        })
        ->addColumn('confirm', function($table_data){
          return
          "<div class='monitor-con'>
              <div class='shape-con'>
                  <div class='shape-line-short' style='background-color: ".$table_data['commitColor']."'></div>
                  <div class='shape-circle' style='background-color: ".$table_data['confirmColor']."'>
                  ". $table_data['confirmBags'] ."
                    <div class='unit-text'>bag/s</div>
                  </div>
                  <div class='shape-line' style='background-color: ".$table_data['confirmColor']."'></div>
                  <div class='date-text-top'>".$table_data['confirmedDate']."</div>
                  <div class='date-text-bottom'>".$table_data['confirmDate']."</div>
              </div>
          </div>";
        })
        ->addColumn('inspect', function($table_data){
          return
          "<div class='monitor-con'>
              <div class='shape-con'>
                  <div class='shape-line-short' style='background-color: ".$table_data['confirmColor']."'></div>
                  <div class='shape-circle' style='background-color: ".$table_data['inspectColor']."'>
                  ". $table_data['inspectBags'] ."
                    <div class='unit-text'>bag/s</div>
                  </div>
                  <div class='shape-line' style='background-color: ".$table_data['inspectColor']."'></div>
                  <div class='date-text-top'></div>
                  <div class='date-text-bottom'>".$table_data['inspectDate']."</div>
              </div>
          </div>";
        })
        ->addColumn('distribute', function($table_data){
          return
          "<div class='monitor-con'>
              <div class='shape-con'>
                  <div class='shape-line-short' style='background-color: ".$table_data['inspectColor']."'></div>
                  <div class='shape-circle' style='background-color: ".$table_data['distributeColor']."'>
                  ". $table_data['distributeBags'] ."
                    <div class='unit-text'>bag/s</div>
                  </div>
                  <div class='date-text-top'></div>
                  <div class='date-text-bottom'>".$table_data['distributeDate']."</div>
              </div>
          </div>";
        })
        ->addColumn('actions', function($table_data) {
            return "<button class='btn btn-info view_seed_growers' id='".$table_data['coopId']."' title='View'><i class='fa fa-eye'></i> View</button>";
        })
        ->make(true);

    }
	
	
    public function seed_growers_delivery(Request $request)
    {
        $input = $request->all();
        $table_data = array();
        $delivery = new DeliveryInspect();

        $producers_data = $delivery->coop_sg_producers($input['coopId']);

        foreach( $producers_data as $p_data ){
            $delivery_data = $delivery->delivery_accreditation($p_data->Accreditation_Number, $p_data->Seed_variety);
            $status = "#dbdbdb";
            $commitedBags = $p_data->Area_Planted_in_ha * 20;
            $deliveredBags = 0;
            $inspectedBags = 0;
            $deliveredDate = "";
            $inspectedDate = "";

            foreach($delivery_data as $data){
                $deliveredDate = $data->deliveryDate;
                $inspectedDate = $data->dateInspected;
                $deliveredBags = $data->totalWeight / 20;
                $inspectedBags = ($data->totalBagsDelivered) ? $data->totalBagsDelivered : 0;
                if ( $data->status == 1 ) {
                    if ($commitedBags != $deliveredBags || $inspectedBags != $deliveredBags) {
                        $status = "#FF7F50";
                    }else{
                        $status = "#2E8B57";
                    }
                }else if($data->status == 2) {
                    $status = "#7F0000";
                }
            }
            $data_delivery = array(
                'code' => $p_data->Accreditation_Number,
                'Name' => $p_data->Name,
                'Seed_variety' => $p_data->Seed_variety,
                'commitedBags' => $commitedBags . " Bag/s",
                'deliveredBags' => $deliveredBags . " Bag/s",
                'deliveredDate' => $deliveredDate,
                'inspectedBags' => $inspectedBags. " Bag/s",
                'inspectedDate' => $inspectedDate,
                'status' => $status,

            );
            array_push($table_data, $data_delivery);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->addColumn('status', function($table_data) {
            return "<div style='width: 100px; height: 30px; background-color: ".$table_data['status']."'></div>";
        })
        ->make(true);
    }
    public function batch_delivery(Request $request)
    {
        $input = $request->all();
        $table_data = array();
        $delivery = new DeliveryInspect();

        $cooperatives = new SeedCooperatives();

        $status = "#dbdbdb";
        $commitedBags = 0;

        $producer = new SeedProducers();
        // get all accreditation of cooperative
        $accreditation_no = $producer->seed_producers_accreditation($input['coopId']);

        /*foreach ($accreditation_no as $accno) {
            $commitedBags = $commitedBags + $accno->Area_Planted_in_ha * 200;
        }*/
		
		foreach ($accreditation_no as $accno) {
			$commitedBags = $commitedBags + $accno->total_value;
		}

        $coop_data = $delivery->get_coop_accno($input['coopId']);
        // get all batch in cooperative selected
        $batch = $delivery->group_batch($coop_data->accreditation_no);
        foreach($batch as $bData){
            // get all variety in batch
            $variety = $delivery->group_variety($bData->batchTicketNumber);
            foreach ($variety as $vData) {
                $deliveredBags = 0;
                $inspectedBags = 0;
                $deliveryDate = "";
                $deliveryArray = array();
                $inspectedDate = "";
                $dropOffPoints = "";
                // fetch variety data
                $getVariety = $delivery->get_variety($bData->batchTicketNumber, $vData->seedVariety);
                foreach ($getVariety as $value) {
                    $deliveredBags = $deliveredBags + $value->totalBagCount;
                    array_push( $deliveryArray, $value->deliveryDate);
                }
                $actualDelivery = $delivery->get_actual_delivery($bData->batchTicketNumber, $vData->seedVariety);

                foreach ($actualDelivery as $ad) {
                    $inspectedBags = $inspectedBags + $ad->totalBagCount;
                    $inspectedDate = $delivery->inspection_date($bData->batchTicketNumber);
                    $inspectedDate = strtotime($inspectedDate);
                    $inspectedDate = date('D, M d Y', $inspectedDate);
                }

                // fetch drop off points
                // $dop = $delivery->get_DOP($vData->dropoffPointId);
                // $dropOffPoints = $dop->dropOffPoint;
                $dropOffPoints = $vData->province ." ". $vData->municipality.", ".$vData->dropOffPoint;
                // fetch max delivery date
                if (count($deliveryArray) > 0) {
                    $deliveryDate = max(array_map('strtotime', $deliveryArray));
                    $deliveryDate = date('D, M d Y', $deliveryDate);
                }


                if ($inspectedBags != $deliveredBags) {
                    $status = "#FF7F50";
                }elseif ($inspectedBags  > 0) {
                    $status = "#2E8B57";
                }

                $data_delivery = array(
                    'coopname' => $coop_data->coopName,
                    'code' => $bData->batchTicketNumber,
                    'Seed_variety' => $vData->seedVariety,
                    'commitedBags' => $commitedBags . " Bag/s",
                    'deliveredBags' => $deliveredBags . " Bag/s",
                    'deliveredDate' => $deliveryDate,
                    'dropOffPoint' => $dropOffPoints,
                    'inspectedBags' => $inspectedBags. " Bag/s",
                    'inspectedDate' => $inspectedDate,
                    'status' => $status,

                );
                array_push($table_data, $data_delivery);
            }
            // Variety not confirmed in delivery
            $vnc = $delivery->check_variety($bData->batchTicketNumber);
            foreach ($vnc as $vData) {
                $deliveredBags = 0;
                $inspectedBags = 0;
                $deliveryDate = "";
                $deliveryArray = array();
                $inspectedDate = "";
                $dropOffPoints = "";
                // fetch variety data

                $actualDelivery = $delivery->get_actual_delivery($bData->batchTicketNumber, $vData->seedVariety);
                foreach ($actualDelivery as $ad) {
                    $inspectedBags = $inspectedBags + $ad->totalBagCount;
                    $inspectedDate = $delivery->inspection_date($bData->batchTicketNumber);
                    $inspectedDate = strtotime($inspectedDate);
                    $inspectedDate = date('D, M d Y', $inspectedDate);
                }

                // fetch drop off points
                // $dop = $delivery->get_DOP($vData->dropoffPointId);
                // $dropOffPoints = $dop->dropOffPoint;
                $dropOffPoints = $vData->province ." ". $vData->municipality.", ".$vData->dropOffPoint;
                // fetch max delivery date

                if ($inspectedBags != $deliveredBags) {
                    $status = "#FF7F50";
                }elseif ($inspectedBags  > 0) {
                    $status = "#2E8B57";
                }

                $data_delivery = array(
                    'coopname' => $coop_data->coopName,
                    'code' => $bData->batchTicketNumber,
                    'Seed_variety' => $vData->seedVariety,
                    'commitedBags' => $commitedBags . " Bag/s",
                    'deliveredBags' => $deliveredBags . " Bag/s",
                    'deliveredDate' => $deliveryDate,
                    'dropOffPoint' => $dropOffPoints,
                    'inspectedBags' => $inspectedBags. " Bag/s",
                    'inspectedDate' => $inspectedDate,
                    'status' => $status,

                );
                array_push($table_data, $data_delivery);
            }
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->addColumn('status', function($table_data) {
            return "<div style='width: 100px; height: 30px; background-color: ".$table_data['status']."'></div>";
        })
        ->make(true);
    }

    public function coop_data(Request $request){

        $input = $request->all();
        $delivery = new DeliveryInspect();
        // $input['coopId'] = 1;
        $coop = $delivery->get_coop_accno($input['coopId']);

        $producer = new SeedProducers();
        // get all accreditation of cooperative
        $accreditation_no = $producer->seed_producers_accreditation($input['coopId']);
        $commitedBags = 0;
        /*foreach ($accreditation_no as $accno) {
            $commitedBags = $commitedBags + $accno->Area_Planted_in_ha * 200;
        }*/
		foreach ($accreditation_no as $accno) {
			$commitedBags = $commitedBags + $accno->total_value;
		}
        $delivery_val = $delivery->delivery_accreditation($coop->accreditation_no);
        $total_bags_delivered = 0;
            if( count($delivery_val) > 0 ){

                foreach ($delivery_val as $dv){

                    $total_bags_delivered = $total_bags_delivered + $dv->totalBagCount;
                }
            }
        $unConfirmed = $commitedBags - $total_bags_delivered;
        $data = array(
            'coopname' => $coop->coopName,
            'bags'  => $commitedBags,
            'confirmedBags' => $total_bags_delivered,
            'unconfirmed'   => $unConfirmed
        );

        return $data;
    }

    public function gen_iar_pdf($id){
        $delivery = new DeliveryInspect();

        $datenow = date('Y-m-d');
        $dv = $delivery->get_delivery($id);
        $coop = $delivery->get_coop($dv->coopAccreditation);
		// dd($dv);
        $coopAdd = $coop->citymunDesc . " " . $coop->provDesc;

        $dv_all = $delivery->get_delivery_batch($id);
        $date = strtotime( $dv->deliveryDate );
        $date = date('D, M d Y', $date);
        
        //$logs = $delivery->check_iar_logs2();
        //$logCount = count($logs);

         $logs = $delivery->check_iar_logs();
        //$logCount = $logs->logsId;
        //$code = date('Y'). "-" .date('m'). "-" .str_pad($logCount + 1, 4, '0', STR_PAD_LEFT);

        $code = $delivery->insert_logs($id);
    
        $data = [
            'CoopName' => $coop->coopName,
            'coopAddress' => $coopAdd,
            'region' => $dv->region,
            'province' => $dv->province,
            'municipality' => $dv->municipality,
            'drop_off_point' => $dv->dropOffPoint,
            'IAR_no' => $code,
            'Date' => $datenow,
            'MOA' => $coop->current_moa,

            'coopName' => $coop->coopName,
            'dop' => $dv->dropOffPoint,
            'delivery' => $dv_all,
            'date' => $date,
            "ticket" => $id
        ];

       // dd($data);

        $pdf = PDFTIM::loadView('DeliveryDashboard/pdf', $data);

        return $pdf->download('RCEP-SMS IAR '.$dv->municipality.' ('.$id.')'.'.pdf');
        // return $pdf->stream("sample.pdf", array("Attachment" => false));
    }
/*
     public function gen_blank_iar_pdf(){
        $id = 1;
    
        $data = [
            'CoopName' => "",
            'coopAddress' => "",
            'region' => "",
            'province' => "",
            'municipality' => "",
            'drop_off_point' => '',
            'IAR_no' => "",
            'Date' => "",
            'MOA' => "",

            'coopName' => "",
            'dop' => "",
            'delivery' => "",
            'date' => ""
        ];

        $pdf = PDFTIM::loadView('DeliveryDashboard/pdf', $data);

        return $pdf->download('RCEP-SMS IAR  ('.$id.')'.'.pdf');
        // return $pdf->stream("sample.pdf", array("Attachment" => false));
    } */

    public function delivery_schedule($id){
        $delivery = new DeliveryInspect();

        $dv = $delivery->get_delivery($id);
        $coop = $delivery->get_coop($dv->coopAccreditation);

        $dv_all = $delivery->get_delivery_batch($id);
        $date = strtotime( $dv->deliveryDate );
        $date = date('D, M d Y', $date);
        $data = [
            'coopName' => $coop->coopName,
            'province' => $dv->province,
            'municipality' => $dv->municipality,
            'dop' => $dv->dropOffPoint,
            'delivery' => $dv_all,
            'date' => $date

        ];
        $pdf = PDFTIM::loadView('DeliveryDashboard/delivery_pdf', $data);

        // return $pdf->download('delivery_schedule.pdf');
        return $pdf->stream("sample.pdf", array("Attachment" => false));
    }

    public function iar_table()
    {
        $provinces = new Provinces();
        $provinces_list = $provinces->delivery_provinces_new();

        return view('deliverydashboard.iarTable')->with(compact('provinces_list'));
    }

    public function get_municipalities(Request $request)
    {
        // Get municipalities
        $municipalities = new Municipalities();
        $municipalities_list = $municipalities->delivery_municipalities_new($request->province);

        echo $municipalities_list;
    }

    public function get_dropoff_points(Request $request)
    {
        // Get dropoff points
        $dropoff_points = new DropoffPoints();
        $dropoff_points_list = $dropoff_points->delivery_dropoff_points_new($request->province, $request->municipality);

        echo $dropoff_points_list;
    }

    public function iar_list(Request $request){
        $delivery = new DeliveryInspect();

        $input = $request->all();
        
        $deliveryData = $delivery->get_delivery_dop(@$input['province'], $input['municipality'], $input['dropoff_point']);
		//$deliveryData = $delivery->get_delivery_dop($request->province, $request->municipality, $request->dropoff_point);
        $table_data = array();
        foreach( $deliveryData as $dv ){
           $check = $delivery->check_iar_logs_batch($dv->batchTicketNumber);
           if(count($check) == 0 ){
            $data_delivery = array(
                'batchno' => $dv->batchTicketNumber,
                'date' => date("F j, Y h:i:s A", strtotime($dv->deliveryDate)),
            );
            array_push($table_data, $data_delivery);
           }   
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->addColumn('action', function($table_data) {
            return "<a type='button' class='btn btn-default' href='iar_pdf/".$table_data['batchno']."'> Download IAR </a>";
        })
        ->make(true);

    }
	
	public function accountant_iar_list(Request $request){
        $delivery = new DeliveryInspect();

        $input = $request->all();
        
        $deliveryData = $delivery->get_delivery_dop($input['province'], $input['municipality'], $input['dropoff_point']);
        $table_data = array();
        foreach( $deliveryData as $dv ){
           $check = $delivery->check_actual_delivery($dv->batchTicketNumber);
           if(count($check) > 0 ){
            $data_delivery = array(
                'batchno' => $dv->batchTicketNumber,
                'date' => date("F j, Y h:i:s A", strtotime($dv->deliveryDate)),
            );
            array_push($table_data, $data_delivery);
           }   
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->addColumn('action', function($table_data) {
            return "<a type='button' class='btn btn-default' href='accountant_iar_pdf/".$table_data['batchno']."'> Download IAR </a>";
        })
        ->make(true);

    }
	
	public function acc_iar_table()
    {
        $provinces = new Provinces();
        $provinces_list = $provinces->delivery_provinces_new();

        return view('deliverydashboard.accountant_iar')->with(compact('provinces_list'));
    }
	
	public function accountant_iar_pdf($id){
        $delivery = new DeliveryInspect();

        //$datenow = date('Y-m-d');
        $dv = $delivery->get_delivery($id);
        $coop = $delivery->get_coop($dv->coopAccreditation);
		// dd($dv);
        $coopAdd = $coop->citymunDesc . " " . $coop->provDesc;

        //$dv_all = $delivery->get_delivery_batch($id);
        $date = strtotime( $dv->deliveryDate );
        $date = date('D, M d Y', $date);
        
        $logs = $delivery->check_iar_logs();
        $logCount = count($logs);
		
		$code = "";
		$datenow = "";
		$logs = $delivery->get_iar_logs($id);
		if(count($logs) > 0){
			 $code = $logs->iarCode;
			 $datenow = $logs->dateCreated;
		}
       
	   //get delivery by variety
		$dv_all = array();
		$totalBags = 0;
		$variety = $delivery->group_variety($id);
		foreach ($variety as $vData) {
			// fetch variety data
			$getVariety = $delivery->get_variety($id, $vData->seedVariety);
			$bags = 0;
			foreach ($getVariety as $bv) {
				$bags = $bags + $bv->totalBagCount;
			}
			$totalBags = $totalBags + $bags;
			$varietydata = array(
				'variety' => $vData->seedVariety,
				'bags' => $bags,
				'date' => $vData->dateCreated,
			);
			array_push( $dv_all, $varietydata);
			
		}
		
		//get inspector data
		$inspector = $delivery->get_inspector($id);
		
		//inspection date
		$inspectedDate = strtotime($dv_all[0]['date']);
        $inspectedDate = date('D, M d Y', $inspectedDate);
		
		
    
        $data = [
            'CoopName' => $coop->coopName,
            'coopAddress' => $coopAdd,
            'region' => $dv->region,
            'province' => $dv->province,
            'municipality' => $dv->municipality,
            'drop_off_point' => $dv->dropOffPoint,
            'IAR_no' => $code,
            'Date' => $datenow,
            'MOA' => $coop->current_moa,

            'coopName' => $coop->coopName,
            'dop' => $dv->dropOffPoint,
            'delivery' => $dv_all,
			'totalBags' => $totalBags,
            'date' => $date,
			'dateInspected' => $inspectedDate,
			'inspector' => $inspector
        ];

        $pdf = PDFTIM::loadView('DeliveryDashboard/accountant_pdf', $data);

        return $pdf->download('ACCOUNTANT_IAR_'.$dv->province.'_'.$id.'.pdf');
        // return $pdf->stream("sample.pdf", array("Attachment" => false));
    }
	
	function my_ofset($text){
        preg_match('/^\D*(?=\d)/', $text, $m);
        return isset($m[0]) ? strlen($m[0]) : false;
    }



      public function export_index_of_payment(Request $request){
        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        $province_list = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('province','municipality','dropOffPoint')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->groupBy('province')
            ->orderBy('province', 'ASC')
            ->get();
        $totalBags = 0;
        $totalAmount = 0;
        $return_arr = array();
        $row =1;
            foreach ($province_list as $province) {
                $row++;
                $batch_data = array(
                    'province' => $province->province,
                    'dropOffPoint' => "",
                    'dr_no' => "",
                    'date' => "",
                    'rla' => "",
                    'variety' => "",
                    'seed_grower' => "",
                    'no_of_bags' => "",
                    'amount_bag' => "",
                    'transpo_bag' => "",
                    'transpo_cost' => "",
                    'sub_total' => "",
                    'retention' =>"",
                    'total' => ""
                );
                array_push($return_arr, $batch_data);

                $municipality_list =  DB::connection('delivery_inspection_db')->table('tbl_delivery')
                    ->where('is_cancelled', 0)
                    ->where('coopAccreditation', $coop_accreditation)
                    ->where('province', $province->province)
                    ->groupBy('municipality', 'seedTag', 'seedVariety')
                    ->orderBy('municipality', 'ASC')
                    ->get();

                    foreach ($municipality_list as $municipality) {
                        $row++;
                        $str = explode("/", $municipality->seedTag);
                        $seedtag_offset = $this->my_ofset($str[0]);

                        $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
                        $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                            ->where('coopAccreditation', $municipality->coopAccreditation)
                            ->where('labNo', $clean_seedTag)
                            ->value('sg_name');

                        $accepted_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("seedTag", $municipality->seedTag)
                                ->where("batchTicketNumber", $municipality->batchTicketNumber)
                                ->sum("totalBagCount");
                        $totalBags += $accepted_bags;
                        $amount_bag = intval($accepted_bags) * 760;
                        $totalAmount += $amount_bag;
                            $batch_data = array(
                                'province' => $municipality->municipality,
                               'dropOffPoint' => $municipality->dropOffPoint,
                                'dr_no' => "",
                                'date' => $municipality->deliveryDate,
                                'rla' => $municipality->seedTag,
                                'variety' => $municipality->seedVariety,
                                'seed_grower' => $seed_grower,
                                'no_of_bags' => $accepted_bags,
                                'amount_bag' => $amount_bag,
                                'transpo_bag' => 0,
                                'transpo_cost' => 0,
                                'sub_total' => "=SUM(I".$row.":K".$row.")",
                                'retention' =>($amount_bag*.01),
                                'total' => "=L".$row."-M".$row
                                
                            );
                            array_push($return_arr, $batch_data);

                    }





            }  
        
        $lrow =$row+1;
        $last_row = array(
             'province' => "",
            'dropOffPoint' => "",
            'dr_no' => "",
            'date' => "",
            'rla' => "",
            'variety' => "",
            'seed_grower' => "",
            'no_of_bags' => $totalBags,
            'amount_bag' => $totalAmount,
             'transpo_bag' => 0,
            'transpo_cost' => 0,
            'sub_total' => "=SUM(L1:L".$row.")",
            'retention' =>($totalAmount*.01),
            'total' => "=L".$lrow."-M".$lrow
        );
        array_push($return_arr, $last_row);

    //  dd($return_arr);

        $myFile = Excel::create('SEED_COOP_INDEX_OF_PAYMENT', function($excel) use ($return_arr,$lrow) {
            $excel->sheet("DELIVERY_LIST", function($sheet) use ($return_arr,$lrow) {
               // $sheet->fromArray($return_arr, null, 'A1', false, false);
                $sheet->prependRow(1, array(                
                               "Province/Municipality", "DOP","DR NO.", "DATE", "SEED TAG", "VARIETY", "SEED GROWER", "BAGS", "SEEDS AMOUNT @760", "TRANSPO COST/BAG", "TRANSPO COST", "TOTAL AMOUNT (SEED + TRANSPO COST)", "RETENTION FEE", "NET AMOUNT"));
                $dop = "";
                $municipality = "";
                $row = 2;
                 foreach ($return_arr as $key => $value) {           
                       if($value["variety"]=="" OR $value["rla"]==""){
                             $sheet->row($row,$value);  

                                $sheet->cells("A".$row.":N".$row, function ($cells){
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#cfcfcf');
                                }); 

                             $row++;
                       }else{
                            if($municipality == $value["province"]){
                                $value["province"] = "";
                               
                                    if($dop == $value["dropOffPoint"]){
                                        $value["dropOffPoint"] ="";
                                    }else{
                                        $dop = $value["dropOffPoint"];
                                    }


                                $sheet->row($row,$value);
                                $row++;
                            }else{
                                $municipality = $value["province"];
                                $dop = $value["dropOffPoint"];
                                
                                $sheet->cells("A".$row, function ($cells){
                                    $cells->setAlignment('right');
                                    $cells->setFontWeight('bold');
                                }); 



                                $sheet->row($row,$value);
                                $row++;
                            }


                       }  
                    }

                    $sheet->cells("A1:N1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                }); 



                $sheet->freezeFirstRow();
                $lrow++;
                $range = "I2:N".$lrow;

                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  10,
                    'D'     =>  20,
                    'E'     =>  20,
                    'F'     =>  20,
                    'G'     =>  20,
                    'H'     =>  10,
                    'I'     =>  20,
                    'J'     =>  10,
                    'K'     =>  10,
                    'L'     =>  20,
                    'M'     =>  20,
                    'N'     =>  20,
                ));
                $sheet->setColumnFormat(array(
                    $range => '#,##0.00_-'
                ));


                 


            });
        });

        $file_name = $coop_acr."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }


    public function export_coop_deliveries_FMD(Request $request){
        

        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer','sg_id')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "=", 0)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            
            $binhi_padala = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_row->batchTicketNumber)
            ->where('seedVariety', $batch_row->seedVariety)
            ->where('seedTag', $batch_row->seedTag)
            ->where("qrValStart", "!=", "")
            ->first();
            
            if($binhi_padala != null){
                    continue;
            }


            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->where('sg_id', $batch_row->sg_id)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if($check_inspected != null){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->where("qrValStart", "=", "")
                    ->sum('totalBagCount');
                

                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    

             if($batch_row->isBuffer == 1){
                $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 0)
                    ->first();

                $label = "Replacement";
            }else{
                $label = "";
            }



            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                


            //   $is_transfer_N = DB::connection('nxt_inspection_db')->table('tbl_actual_delivery')
            //     ->select('batchTicketNumber')
            //     ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
            //     ->where('transferCategory', 'p')
            //     ->first(); 

            $is_transfer_N = array();

                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);              
                $nxt_season = count($is_transfer_N);



             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'batchTicketNumber' => $bt,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => number_format($confirmed_bags),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                        );

                        array_push($return_arr, $batch_data);
                        $inspected_bags += $bg;

                      }
                    }
             }  //WHOLE COUNT 
             else{
            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'dr_no' => "",
                'dr_date' => "",
                'rla_seedtag' => $batch_row->seedTag,
                'seedVariety' => $batch_row->seedVariety,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'inspected' => number_format($inspected_bags),
                'confirmed' => number_format($confirmed_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'iar_number' => $iar_number_str,
                'remarks' => $label,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedtag' => $batch_row->seedTag,
                'seedVariety2' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
             
            );

            array_push($return_arr, $batch_data);

         }

              if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         if($batch_row->isBuffer == 1){
                            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 0)
                                ->first();

                            $label = "Replacement";
                        }else{
                            $label = "";
                        }  

                             $batch_data = array(
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => "-",
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                           
                    );

                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;

                        $retransferred = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')->where("remarks", "LIKE", "%".$bt."%")->where("seedTag", $st)->get();
                            if(count($retransferred)>0){
                                foreach ($retransferred as $in => $par) {

                                    if($par->transferCategory == "T"){
                                        $tt = "PARTIAL RE TRANSFER";
                                    }elseif($par->transferCategory == "W"){
                                        $tt = "WHOLE RE TRANSFER";
                                    }else{
                                        $tt = "";
                                    }

                                    $batch_data = array(
                                            'batchTicketNumber' => $par->batchTicketNumber,
                                            'province' => $dt_province.' => '.$par->province,
                                            'municipality' => $dt_municipality.' => '.$par->municipality,
                                            'dr_no' => "",
                                            'dr_date' => "",
                                            'rla_seedtag' => $st,
                                            'seedVariety' => $par->seedVariety,
                                            'seed_grower' => 'N/A',
                                            'inspected' => str_replace("bag(s)", "", $par->totalBagCount),
                                            'confirmed' => "-",
                                            'deliveryDate' => date("Y-m-d", strtotime($par->dateCreated)),
                                            'batch_status' => $tt,
                                            'iar_number' => '-',
                                            'remarks' => "",
                                            'coopAccreditation' => $batch_row->coopAccreditation,
                                            'seedtag' => $st,
                                            'seedVariety2' => $par->seedVariety,
                                            'dropOffPoint' => $dt_dropoff.' => '.$par->dropOffPoint,
                                            'region' => $dt_region.' => '.$par->region,
                                            
                                    );

                                    array_push($return_arr, $batch_data);   
                                    $inspected_bags += $bg;
                                }

                            }
                    
             }
         } //PARTIAL COUNT

          if($nxt_season > 0){
                    $arr = $this->getNextSeasonData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        $label = "";

                         $batch_data = array(
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => "-",
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                               
                           
                    );

                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;
             }
         } //NEXT SEASON COUNT

            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        }

        // dd($total_inspected);



















         //PREVIOUS SEASON 
    $prevBatch = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                ->where('coop_accreditation', $coop_accreditation)
                ->groupBy('new_batch_number')
                ->get();
                //$return_arr = array();
                foreach ($prevBatch as $prevBatch) {
                   $ls_batchNumber = $prevBatch->batch_number;

                    $actualList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                            ->where("batchTicketNumber", $prevBatch->new_batch_number)
                            ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                            ->get();

                            foreach ($actualList as $actualRow) {
                                $inspected = 0;        
                                $inspected = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                        ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                        ->where('batchTicketNumber', $prevBatch->new_batch_number)
                                        ->where('seedTag', $actualRow->seedTag)
                                        ->where('transferCategory', 'P')
                                        ->where('is_transferred', 1)
                                        ->sum('totalBagCount');


                               $checkIfPartial = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from batch: '.$prevBatch->new_batch_number.'%')
                                    ->where('seedTag', $actualRow->seedTag)
                                    ->where('transferCategory', "T")
                                    ->where('is_transferred', 1)
                                    ->first();

                                 $checkIfWhole = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                            ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                            ->where('batchTicketNumber', $prevBatch->new_batch_number)
                                            ->where('seedTag', $actualRow->seedTag)
                                            ->where('transferCategory', "W")
                                            ->where('is_transferred', 1)
                                            ->first();

                               if(count($checkIfWhole)>0){
                                      
                                        $inspected += DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                            ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                            ->where('batchTicketNumber', $prevBatch->new_batch_number)
                                            ->where('seedTag', $actualRow->seedTag)
                                            ->where('transferCategory', "W")
                                            ->where('is_transferred', 1)
                                            ->sum('totalBagCount');
                                    }
                                
                                if(count($checkIfPartial)>0){
                                
                                    $inspected += DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from batch: '.$prevBatch->new_batch_number.'%')
                                    ->where('seedTag', $actualRow->seedTag)
                                    ->where('transferCategory', "T")
                                    ->where('is_transferred', 1)
                                    ->sum('totalBagCount');
                                }


                                $labLot = explode("/", $actualRow->seedTag);
                                $sg =DB::connection("ls_inspection_db")->table("tbl_rla_details")
                                    ->where("labNo", $labLot[0])
                                    ->where("lotNo", $labLot[1])
                                    ->where("coopAccreditation", $coop_accreditation)
                                    ->first();
                                if(count($sg)>0){
                                    $sg = $sg->sg_name;
                                }else{
                                    $sg = "N/A";
                                }

//STOPPED HERE
                                $batch_data = array(
                                    'batchTicketNumber' => $prevBatch->new_batch_number,
                                    'province' => $actualRow->province,
                                    'municipality' => $actualRow->municipality,
                                    'dr_no' => "",
                                    'dr_date' => "",
                                    'rla_seedtag' => $actualRow->seedTag,
                                    'seedVariety' => $actualRow->seedVariety,
                                    'seed_grower' => $sg,
                                    'inspected' => strval($inspected),
                                    'confirmed' => "-",
                                    'deliveryDate' => date("Y-m-d", strtotime($prevBatch->date_created)),
                                    'batch_status' => "",
                                    'iar_number' => "Previous Season: ".$ls_batchNumber,
                                    'remarks' => 'Transferred From Previous Season',
                                    'coopAccreditation' => $coop_accreditation,
                                    'seedtag' => $actualRow->seedTag,
                                    'seedVariety2' => $actualRow->seedVariety,
                                    'dropOffPoint' => $actualRow->dropOffPoint,
                                    'region' => $actualRow->region,
                                );

                       

                                array_push($return_arr, $batch_data);

                                 if(count($checkIfWhole)>0){
                                         $arr = $this->getWholeData($ls_batchNumber,$prevBatch->new_batch_number, $actualRow->seedVariety , $actualRow->seedTag);

                                         foreach ($arr as $key => $value) {

                                            if($arr[$key]['seedTag']==$actualRow->seedTag){
                                            $bt = $arr[$key]['batch_num'];
                                            $or = $arr[$key]['origin'];
                                            $dt = $arr[$key]['destination'];
                                            $sv = $arr[$key]['seedVariety'];
                                            $st = $arr[$key]['seedTag'];
                                            $sdt = $arr[$key]['seedType'];
                                            $bg = $arr[$key]['bags'];
                                            $dc = $arr[$key]['dateCreated'];
                                            $tt = $arr[$key]['transferType'];
                                        
                                        if($dt != ""){
                                            $dt = str_replace(",", "|", $dt);
                                            $dt = str_replace("->", "|", $dt);
                                            $dt = explode("|", $dt);
                                            $dt_province = trim($dt[0]);
                                            $dt_municipality = trim($dt[1]);
                                            $dt_dropoff = trim($dt[2]);
                                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                    ->where('province', $dt_province)
                                                    ->value('regionName');
                                        }else{
                                            $dt_region = "N/A";
                                            $dt_province = "N/A";
                                            $dt_municipality = "N/A";
                                            $dt_dropoff = "N/A";
                                        }

                                        if($or != " NO INFO ON LOGS"){
                                            $or = str_replace(",", "|", $or);
                                            $or = str_replace("->", "|", $or);
                                            $or = explode("|", $or);
                                            $or_province = trim($or[0]);
                                            $or_municipality = trim($or[1]);
                                            $or_dropoff = trim($or[2]);

                                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                    ->where('province', $or_province)
                                                    ->value('regionName');
                                        }else{
                                            $or_region = "N/A";
                                            $or_province = "N/A";
                                            $or_municipality = "N/A";
                                            $or_dropoff = "N/A";
                                        }

                                        

                                        $batch_data = array(
                                            'batchTicketNumber' => $ls_batchNumber,
                                            'province' => $or_province.' => '.$dt_province,
                                            'municipality' => $or_municipality.' => '.$dt_municipality,
                                            'dr_no' => "",
                                            'dr_date' => "",
                                            'rla_seedtag' => $st,
                                            'seedVariety' => $sv,
                                            'seed_grower' => 'N/A',
                                            'inspected' => str_replace("bag(s)", "", $bg),
                                            'confirmed' => "0",
                                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                                            'batch_status' => $tt,
                                            'iar_number' => "Previous Season",
                                            'remarks' => 'Transferred From Previous Season',     
                                            'coopAccreditation' => $coop_accreditation,
                                            'seedtag' => $st,
                                            'seedVariety2' => $sv,
                                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                                            'region' => $or_region.' => '.$dt_region,
                                        );




                                        array_push($return_arr, $batch_data);
                                        $inspected += $bg;
                                      }
                                    }

                                }


                                if(count($checkIfPartial)>0){
                                       $arr = $this->getPartialData($prevBatch->new_batch_number,$actualRow->seedVariety,$actualRow->seedTag);
                                      // dd($arr);
                                        foreach ($arr as $key => $value) {
                                            $bt = $arr[$key]['batch_num'];
                                            $or = $arr[$key]['origin'];
                                            $dt = $arr[$key]['destination'];
                                            $sv = $arr[$key]['seedVariety'];
                                            $st = $arr[$key]['seedTag'];
                                            $sdt = $arr[$key]['seedType'];
                                            $bg = $arr[$key]['bags'];
                                            $dc = $arr[$key]['dateCreated'];
                                            $tt = $arr[$key]['transferType'];


                                              if($dt != ""){
                                                $dt = str_replace(",", "|", $dt);
                                                $dt = str_replace("->", "|", $dt);
                                                $dt = explode("|", $dt);
                                                $dt_province = trim($dt[0]);
                                                $dt_municipality = trim($dt[1]);
                                                $dt_dropoff = trim($dt[2]);
                                                $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                        ->where('province', $dt_province)
                                                        ->value('regionName');
                                            }else{
                                                $dt_region = "N/A";
                                                $dt_province = "N/A";
                                                $dt_municipality = "N/A";
                                                $dt_dropoff = "N/A";
                                            }

                                            if($or != " NO INFO ON LOGS"){
                                                $or = str_replace(",", "|", $or);
                                                $or = str_replace("->", "|", $or);
                                                $or = explode("|", $or);
                                                $or_province = trim($or[0]);
                                                $or_municipality = trim($or[1]);
                                                $or_dropoff = trim($or[2]);

                                                $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                        ->where('province', $or_province)
                                                        ->value('regionName');
                                            }else{
                                                $or_region = "N/A";
                                                $or_province = "N/A";
                                                $or_municipality = "N/A";
                                                $or_dropoff = "N/A";
                                            }
                                             $batch_data = array(
                                                'batchTicketNumber' => $ls_batchNumber,
                                                'province' => $or_province.' => '.$dt_province,
                                                'municipality' => $or_municipality.' => '.$dt_municipality,
                                                'dr_no' => "",
                                                'dr_date' => "",
                                                'rla_seedtag' => $st,
                                                'seedVariety' => $sv,
                                                'seed_grower' => 'N/A',
                                                'inspected' => str_replace("bag(s)", "", $bg),
                                                'confirmed' => "0",
                                                'deliveryDate' => date("Y-m-d", strtotime($dc)),
                                                'batch_status' => $tt,
                                                'iar_number' => "Previous Season",
                                                'remarks' => 'Transferred From Previous Season',
                                                'coopAccreditation' => $coop_accreditation,
                                                'seedtag' => $st,
                                                'seedVariety2' => $sv,
                                                'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                                                'region' => $or_region.' => '.$dt_region,
                                        );



                                        array_push($return_arr, $batch_data);
                                        $inspected += $bg;
                                 



                                    $retransferred = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')->where("remarks", "LIKE", "%".$bt."%")->where("seedTag", $st)->get();
                                        if(count($retransferred)>0){
                                            foreach ($retransferred as $in => $par) {

                                                if($par->transferCategory == "T"){
                                                    $tt = "PARTIAL RE TRANSFER";
                                                }elseif($par->transferCategory == "W"){
                                                    $tt = "WHOLE RE TRANSFER";
                                                }else{
                                                    $tt = "";
                                                }

                                                $batch_data = array(
                                                        'batchTicketNumber' => $par->batchTicketNumber,
                                                        'province' => $dt_province.' => '.$par->province,
                                                        'municipality' => $dt_municipality.' => '.$par->municipality,
                                                        'dr_no' => "",
                                                        'dr_date' => "",
                                                        'rla_seedtag' => $st,
                                                        'seedVariety' => $par->seedVariety,
                                                        'seed_grower' =>'N/A',
                                                        'inspected' => str_replace("bag(s)", "", $par->totalBagCount),
                                                        'confirmed' => "-",
                                                        'deliveryDate' => date("Y-m-d", strtotime($par->dateCreated)),
                                                        'batch_status' => $tt,
                                                        'iar_number' => '-',
                                                        'remarks' => "Transferred From Previous Season",
                                                        'coopAccreditation' => $batch_row->coopAccreditation,
                                                        'seedtag' => $st,
                                                        'seedVariety2' => $par->seedVariety,
                                                        'dropOffPoint' => $dt_dropoff.' => '.$par->dropOffPoint,
                                                        'region' => $dt_region.' => '.$par->region,
                                                      
                                                       
                                                );
                                                array_push($return_arr, $batch_data);   
                                                $inspected_bags += $bg;
                                            }


                                        }



                                 }
                                }

                                   $total_inspected += $inspected;

                            }
                } 

        $last_row = array(
         
            'batchTicketNumber' => '',
            'province' => '',
            'municipality' => '',
            'dr_no' => "",
            'dr_date' => "",
            'rla_seedtag' => '',
            'seedVariety' => '',
            'seed_grower' => 'TOTAL: ',
            'inspected' => number_format($total_inspected),
            'confirmed' => number_format($total_confirmed),
            'deliveryDate' => '',
            'batch_status' => '',
            'iar_number' => "",
            'remarks' => "",
            'coopAccreditation' => '',
            'seedtag' => '',
            'seedVariety2' => '',
            'dropOffPoint' => '',
            'region' => '',
           
           
        );

        array_push($return_arr, $last_row);

        $iar_checker = array();
        $iar_totals = array();
        array_push($iar_totals, array(
            "iar" => "IAR No.",
            "province" => "Province",
            "municipality" => "Municipality",
            "deliveryDate" => "DeliveryDate",
            "bags" => "Bags"
        ));

        foreach($return_arr as $dlist){
            $dList_iar = $dlist["iar_number"];
        
            if($dlist['iar_number'] == ""){
                continue;
            }
            if($dlist['iar_number'] == "N/A"){
                continue;
            }

            if($dlist['batch_status'] != "Passed"){
                continue;
            }


                if(!isset($iar_checker[$dList_iar])){
                    $get_sum = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("batchTicketNumber", $dlist["batchTicketNumber"])
                    ->sum("totalBagCount");

                    array_push($iar_totals, array(
                        "iar" => $dList_iar,
                        "province" => $dlist["province"],
                        "municipality" => $dlist["municipality"],
                        "deliveryDate" => $dlist["deliveryDate"],
                        "bags" => $get_sum
                    ));
                    $iar_checker[$dList_iar] = "done";
                    
                }else{
                    continue;
                }
        }

    //HERE PLEASE
        $replacement_arr = $this->ReplacementList_FMD($coop_accreditation);

        $iar_checker = array();
        $iar_totals_replacement = array();
        array_push($iar_totals_replacement, array(
            "iar" => "IAR No.",
            "province" => "Province",
            "municipality" => "Municipality",
            "deliveryDate" => "DeliveryDate",
            "bags" => "Bags"
        ));

        foreach($replacement_arr as $dlist){
            $dList_iar = $dlist["iar_number"];
        
            if($dlist['iar_number'] == ""){
                continue;
            }
            if($dlist['iar_number'] == "N/A"){
                continue;
            }

            if($dlist['batch_status'] != "Passed"){
                continue;
            }


                if(!isset($iar_checker[$dList_iar])){
                    $get_sum = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("batchTicketNumber", $dlist["batchTicketNumber"])
                    ->sum("totalBagCount");

                    array_push($iar_totals_replacement, array(
                        "iar" => $dList_iar,
                        "province" => $dlist["province"],
                        "municipality" => $dlist["municipality"],
                        "deliveryDate" => $dlist["deliveryDate"],
                        "bags" => $get_sum
                    ));
                    $iar_checker[$dList_iar] = "done";
                    
                }else{
                    continue;
                }
        }



        $buffer_arr = $this->bufferList_FMD($coop_accreditation);
        $iar_checker = array();
        $iar_totals_buffer = array();
        array_push($iar_totals_buffer, array(
            "iar" => "IAR No.",
            "province" => "Province",
            "municipality" => "Municipality",
            "deliveryDate" => "DeliveryDate",
            "bags" => "Bags"
        ));

        foreach($buffer_arr as $dlist){
            $dList_iar = $dlist["iar_number"];
        
            if($dlist['iar_number'] == ""){
                continue;
            }
            if($dlist['iar_number'] == "N/A"){
                continue;
            }

            if($dlist['batch_status'] != "Passed"){
                continue;
            }


                if(!isset($iar_checker[$dList_iar])){
                    $get_sum = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("batchTicketNumber", $dlist["batchTicketNumber"])
                    ->sum("totalBagCount");

                    array_push($iar_totals_buffer, array(
                        "iar" => $dList_iar,
                        "province" => $dlist["province"],
                        "municipality" => $dlist["municipality"],
                        "deliveryDate" => $dlist["deliveryDate"],
                        "bags" => $get_sum
                    ));
                    $iar_checker[$dList_iar] = "done";
                    
                }else{
                    continue;
                }
        }

        $bep_arr = $this->bep_list_FMD($coop_accreditation);
        $iar_checker = array();
        $iar_totals_bep = array();
        array_push($iar_totals_bep, array(
            "iar" => "IAR No.",
            "province" => "Province",
            "municipality" => "Municipality",
            "deliveryDate" => "DeliveryDate",
            "bags" => "Bags"
        ));

        foreach($bep_arr as $dlist){
            $dList_iar = $dlist["iar_number"];
        
            if($dlist['iar_number'] == ""){
                continue;
            }
            if($dlist['iar_number'] == "N/A"){
                continue;
            }

            if($dlist['batch_status'] != "Passed"){
                // continue;
            }


                if(!isset($iar_checker[$dList_iar])){
                    $get_sum = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("batchTicketNumber", $dlist["batchTicketNumber"])
                    ->sum("totalBagCount");

                    array_push($iar_totals_bep, array(
                        "iar" => $dList_iar,
                        "province" => $dlist["province"],
                        "municipality" => $dlist["municipality"],
                        "deliveryDate" => $dlist["deliveryDate"],
                        "bags" => $get_sum
                    ));
                    $iar_checker[$dList_iar] = "done";
                    
                }else{
                    continue;
                }
        }



        $myFile = Excel::create('SEED_COOP_DELIVERIES_FMD', function($excel) use ($return_arr, $iar_totals, $replacement_arr, $iar_totals_replacement, $buffer_arr,$iar_totals_buffer, $bep_arr,$iar_totals_bep) {
            $excel->sheet("DELIVERY_LIST", function($sheet) use ($return_arr,$iar_totals) {
                //dd($iar_totals);
                $sheet->fromArray($return_arr, null, 'A1', true);
                // $sheet->fromArray($iar_totals, null, 'W1', true);
                    $r = 1;
                 foreach($iar_totals as $iar){
                    $col = "W";
                    $sheet->cell($col.$r, function($cells) use ($iar){
                        $cells->setValue($iar["iar"]);
                    });
                    $col++;
                    $sheet->cell($col.$r, function($cells) use ($iar){
                        $cells->setValue($iar["province"]);
                    });
                    $col++;
                    $sheet->cell($col.$r, function($cells) use ($iar){
                        $cells->setValue($iar["municipality"]);
                    });
                    $col++;
                    $sheet->cell($col.$r, function($cells) use ($iar){
                        $cells->setValue($iar["deliveryDate"]);
                    });
                    $col++;
                    $sheet->cell($col.$r, function($cells) use ($iar){
                        $cells->setValue($iar["bags"]);
                    });
                    $r++;
                }


            });
            $excel->sheet("REPLACEMENT_LIST", function($sheet) use ($replacement_arr,$iar_totals_replacement) {
                $sheet->fromArray($replacement_arr);
                $r = 1;
                foreach($iar_totals_replacement as $iar){
                   $col = "W";
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["iar"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["province"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["municipality"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["deliveryDate"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["bags"]);
                   });
                   $r++;
               }
            });
            $excel->sheet("BUFFER_LIST", function($sheet) use ($buffer_arr,$iar_totals_buffer) {
                $sheet->fromArray($buffer_arr);
                $r = 1;
                foreach($iar_totals_buffer as $iar){
                   $col = "W";
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["iar"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["province"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["municipality"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["deliveryDate"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["bags"]);
                   });
                   $r++;
               }
            });
            $excel->sheet("BINHI_E_PADALA", function($sheet) use ($bep_arr,$iar_totals_bep) {
                $sheet->fromArray($bep_arr);
                $r = 1;
                foreach($iar_totals_bep as $iar){
                   $col = "W";
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["iar"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["province"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["municipality"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["deliveryDate"]);
                   });
                   $col++;
                   $sheet->cell($col.$r, function($cells) use ($iar){
                       $cells->setValue($iar["bags"]);
                   });
                   $r++;
               }
            });
        });

        $file_name = $coop_acr."_FMD_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    
    }




    public function export_coop_deliveries(Request $request){

        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        //return 'mark';
/*          //old code
            $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer','sg_id')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "=", 0)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get(); */
            return $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->join('tbl_delivery_transaction', 'tbl_delivery.coopAccreditation', '=', 'tbl_delivery_transaction.accreditation_no')
            ->select(
                'tbl_delivery.batchTicketNumber', 
                'tbl_delivery.coopAccreditation', 
                'tbl_delivery.seedVariety', 
                'tbl_delivery.deliveryDate', 
                'tbl_delivery.dropOffPoint', 
                'tbl_delivery.region', 
                'tbl_delivery.province', 
                'tbl_delivery.municipality', 
                'tbl_delivery.seedTag', 
                'tbl_delivery.isBuffer',
                'tbl_delivery.sg_id',
                'tbl_delivery_transaction.seed_distribution_mode'
            )
            ->where('tbl_delivery.is_cancelled', 0)
            ->where('tbl_delivery.coopAccreditation', $coop_accreditation)
            ->where('tbl_delivery.isBuffer', 0)
            //->where('tbl_delivery_transaction.seed_distribution_mode', 'NRP')
            ->groupBy(
                'tbl_delivery.batchTicketNumber', 'tbl_delivery.seedVariety', 'tbl_delivery.seedTag'
            )
            ->orderBy('tbl_delivery.deliveryDate', 'DESC')
            ->get();
        

        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        $return_arr_nrp = array();
        $return_arr_gqs = array();
        foreach($batch_deliveries as $batch_row){
            $binhi_padala = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $batch_row->batchTicketNumber)
            ->where('seedVariety', $batch_row->seedVariety)
            ->where('seedTag', $batch_row->seedTag)
            ->where("qrValStart", "!=", "")
            ->first();
            if($binhi_padala != null){
                    continue;
            }

            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->where('sg_id', $batch_row->sg_id)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if($check_inspected != null){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->where("qrValStart", "=", "")
                    ->sum('totalBagCount');
                

                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    

             if($batch_row->isBuffer == 1){
                $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 0)
                    ->first();

                $label = "Replacement";
            }else{
                $label = "";
            }



            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                


            //   $is_transfer_N = DB::connection('nxt_inspection_db')->table('tbl_actual_delivery')
            //     ->select('batchTicketNumber')
            //     ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
            //     ->where('transferCategory', 'p')
            //     ->first(); 

            $is_transfer_N = array();

                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);              
                $nxt_season = count($is_transfer_N);



             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label,
                            'category'=> $batch_row->seed_distribution_mode,
                           
                        );
                        if($batch_row->seed_distribution_mode == 'NRP'){
                            array_push($return_arr_nrp, $batch_data);
                        }else{
                        if($batch_row->seed_distribution_mode == 'Good Quality Seeds'){
                            array_push($return_arr_gqs, $batch_data);
                        }else{
                            array_push($return_arr, $batch_data);
                        }
                        
                        $inspected_bags += $bg;

                      }
                    }
             }  //WHOLE COUNT 
             else{
            $batch_data = array(
                'iar_number' => $iar_number_str,
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'remarks' => $label,
                'category'=> $batch_row->seed_distribution_mode,
            );


            
            array_push($return_arr, $batch_data);

         }

              if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         if($batch_row->isBuffer == 1){
                            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 0)
                                ->first();

                            $label = "Replacement";
                        }else{
                            $label = "";
                        }  

                             $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => "-",
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label,
                            'category'=> $batch_row->seed_distribution_mode,
                           
                    );

                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;

                        $retransferred = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')->where("remarks", "LIKE", "%".$bt."%")->where("seedTag", $st)->get();
                            if(count($retransferred)>0){
                                foreach ($retransferred as $in => $par) {

                                    if($par->transferCategory == "T"){
                                        $tt = "PARTIAL RE TRANSFER";
                                    }elseif($par->transferCategory == "W"){
                                        $tt = "WHOLE RE TRANSFER";
                                    }else{
                                        $tt = "";
                                    }

                                    $batch_data = array(
                                            'iar_number' => '-',
                                            'batchTicketNumber' => $par->batchTicketNumber,
                                            'coopAccreditation' => $batch_row->coopAccreditation,
                                            'seedVariety' => $par->seedVariety,
                                            'dropOffPoint' => $dt_dropoff.' => '.$par->dropOffPoint,
                                            'region' => $dt_region.' => '.$par->region,
                                            'province' => $dt_province.' => '.$par->province,
                                            'municipality' => $dt_municipality.' => '.$par->municipality,
                                            'seedtag' => $st,
                                            'seed_grower' => 'N/A',
                                            'confirmed' => "-",
                                            'inspected' => str_replace("bag(s)", "", $par->totalBagCount),
                                            'deliveryDate' => date("Y-m-d", strtotime($par->dateCreated)),
                                            'batch_status' => $tt,
                                            'remarks' => "",
                                            'category'=> $batch_row->seed_distribution_mode,
                                           
                                    );

                                    array_push($return_arr, $batch_data);   
                                    $inspected_bags += $bg;
                                }

                            }

             }
         } //PARTIAL COUNT

          if($nxt_season > 0){
                    $arr = $this->getNextSeasonData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        $label = "";

                         $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => "-",
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label,
                            'category'=> $batch_row->seed_distribution_mode,
                           
                    );

                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;
             }
         } //NEXT SEASON COUNT

            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        }

         //PREVIOUS SEASON 
    $prevBatch = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                ->where('coop_accreditation', $coop_accreditation)
                ->groupBy('new_batch_number')
                ->get();
                //$return_arr = array();
                foreach ($prevBatch as $prevBatch) {
                   $ls_batchNumber = $prevBatch->batch_number;

                    $actualList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                            ->where("batchTicketNumber", $prevBatch->new_batch_number)
                            ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                            ->get();

                            foreach ($actualList as $actualRow) {
                                $inspected = 0;        
                                $inspected = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                        ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                        ->where('batchTicketNumber', $prevBatch->new_batch_number)
                                        ->where('seedTag', $actualRow->seedTag)
                                        ->where('transferCategory', 'P')
                                        ->where('is_transferred', 1)
                                        ->sum('totalBagCount');


                               $checkIfPartial = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from batch: '.$prevBatch->new_batch_number.'%')
                                    ->where('seedTag', $actualRow->seedTag)
                                    ->where('transferCategory', "T")
                                    ->where('is_transferred', 1)
                                    ->first();

                                 $checkIfWhole = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                            ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                            ->where('batchTicketNumber', $prevBatch->new_batch_number)
                                            ->where('seedTag', $actualRow->seedTag)
                                            ->where('transferCategory', "W")
                                            ->where('is_transferred', 1)
                                            ->first();

                               if(count($checkIfWhole)>0){
                                      
                                        $inspected += DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                            ->where('remarks', 'like', '%transferred from previous season batch: '.$ls_batchNumber.'%')
                                            ->where('batchTicketNumber', $prevBatch->new_batch_number)
                                            ->where('seedTag', $actualRow->seedTag)
                                            ->where('transferCategory', "W")
                                            ->where('is_transferred', 1)
                                            ->sum('totalBagCount');
                                    }
                                
                                if(count($checkIfPartial)>0){
                                
                                    $inspected += DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where('remarks', 'like', '%transferred from batch: '.$prevBatch->new_batch_number.'%')
                                    ->where('seedTag', $actualRow->seedTag)
                                    ->where('transferCategory', "T")
                                    ->where('is_transferred', 1)
                                    ->sum('totalBagCount');
                                }


                                $labLot = explode("/", $actualRow->seedTag);
                                $sg =DB::connection("ls_inspection_db")->table("tbl_rla_details")
                                    ->where("labNo", $labLot[0])
                                    ->where("lotNo", $labLot[1])
                                    ->where("coopAccreditation", $coop_accreditation)
                                    ->first();
                                if(count($sg)>0){
                                    $sg = $sg->sg_name;
                                }else{
                                    $sg = "N/A";
                                }


                                $batch_data = array(
                                    'iar_number' => "Previous Season: ".$ls_batchNumber,
                                    'batchTicketNumber' => $prevBatch->new_batch_number,
                                    'coopAccreditation' => $coop_accreditation,
                                    'seedVariety' => $actualRow->seedVariety,
                                    'dropOffPoint' => $actualRow->dropOffPoint,
                                    'region' => $actualRow->region,
                                    'province' => $actualRow->province,
                                    'municipality' => $actualRow->municipality,
                                    'seedtag' => $actualRow->seedTag,
                                    'seed_grower' => $sg,
                                    'confirmed' => "-",
                                    'inspected' => strval($inspected),
                                    'deliveryDate' => date("Y-m-d", strtotime($prevBatch->date_created)),
                                    'batch_status' => "",
                                    'remarks' => 'Transferred From Previous Season',
                                    'category'=> $batch_row->seed_distribution_mode,
                                );

                                array_push($return_arr, $batch_data);

                                 if(count($checkIfWhole)>0){
                                         $arr = $this->getWholeData($ls_batchNumber,$prevBatch->new_batch_number, $actualRow->seedVariety , $actualRow->seedTag);

                                         foreach ($arr as $key => $value) {

                                            if($arr[$key]['seedTag']==$actualRow->seedTag){
                                            $bt = $arr[$key]['batch_num'];
                                            $or = $arr[$key]['origin'];
                                            $dt = $arr[$key]['destination'];
                                            $sv = $arr[$key]['seedVariety'];
                                            $st = $arr[$key]['seedTag'];
                                            $sdt = $arr[$key]['seedType'];
                                            $bg = $arr[$key]['bags'];
                                            $dc = $arr[$key]['dateCreated'];
                                            $tt = $arr[$key]['transferType'];
                                        
                                        if($dt != ""){
                                            $dt = str_replace(",", "|", $dt);
                                            $dt = str_replace("->", "|", $dt);
                                            $dt = explode("|", $dt);
                                            $dt_province = trim($dt[0]);
                                            $dt_municipality = trim($dt[1]);
                                            $dt_dropoff = trim($dt[2]);
                                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                    ->where('province', $dt_province)
                                                    ->value('regionName');
                                        }else{
                                            $dt_region = "N/A";
                                            $dt_province = "N/A";
                                            $dt_municipality = "N/A";
                                            $dt_dropoff = "N/A";
                                        }

                                        if($or != " NO INFO ON LOGS"){
                                            $or = str_replace(",", "|", $or);
                                            $or = str_replace("->", "|", $or);
                                            $or = explode("|", $or);
                                            $or_province = trim($or[0]);
                                            $or_municipality = trim($or[1]);
                                            $or_dropoff = trim($or[2]);

                                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                    ->where('province', $or_province)
                                                    ->value('regionName');
                                        }else{
                                            $or_region = "N/A";
                                            $or_province = "N/A";
                                            $or_municipality = "N/A";
                                            $or_dropoff = "N/A";
                                        }

                                        

                                        $batch_data = array(
                                            'iar_number' => "Previous Season",
                                            'batchTicketNumber' => $ls_batchNumber,
                                            'coopAccreditation' => $coop_accreditation,
                                            'seedVariety' => $sv,
                                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                                            'region' => $or_region.' => '.$dt_region,
                                            'province' => $or_province.' => '.$dt_province,
                                            'municipality' => $or_municipality.' => '.$dt_municipality,
                                            'seedtag' => $st,
                                            'seed_grower' => 'N/A',
                                            'confirmed' => "0",
                                            'inspected' => str_replace("bag(s)", "", $bg),
                                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                                            'batch_status' => $tt,
                                            'remarks' => 'Transferred From Previous Season',
                                            'category'=> $batch_row->seed_distribution_mode,                                           
                                        );

                                        array_push($return_arr, $batch_data);
                                        $inspected += $bg;
                                      }
                                    }

                                }


                                if(count($checkIfPartial)>0){
                                       $arr = $this->getPartialData($prevBatch->new_batch_number,$actualRow->seedVariety,$actualRow->seedTag);
                                      // dd($arr);
                                        foreach ($arr as $key => $value) {
                                            $bt = $arr[$key]['batch_num'];
                                            $or = $arr[$key]['origin'];
                                            $dt = $arr[$key]['destination'];
                                            $sv = $arr[$key]['seedVariety'];
                                            $st = $arr[$key]['seedTag'];
                                            $sdt = $arr[$key]['seedType'];
                                            $bg = $arr[$key]['bags'];
                                            $dc = $arr[$key]['dateCreated'];
                                            $tt = $arr[$key]['transferType'];


                                              if($dt != ""){
                                                $dt = str_replace(",", "|", $dt);
                                                $dt = str_replace("->", "|", $dt);
                                                $dt = explode("|", $dt);
                                                $dt_province = trim($dt[0]);
                                                $dt_municipality = trim($dt[1]);
                                                $dt_dropoff = trim($dt[2]);
                                                $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                        ->where('province', $dt_province)
                                                        ->value('regionName');
                                            }else{
                                                $dt_region = "N/A";
                                                $dt_province = "N/A";
                                                $dt_municipality = "N/A";
                                                $dt_dropoff = "N/A";
                                            }

                                            if($or != " NO INFO ON LOGS"){
                                                $or = str_replace(",", "|", $or);
                                                $or = str_replace("->", "|", $or);
                                                $or = explode("|", $or);
                                                $or_province = trim($or[0]);
                                                $or_municipality = trim($or[1]);
                                                $or_dropoff = trim($or[2]);

                                                $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                                        ->where('province', $or_province)
                                                        ->value('regionName');
                                            }else{
                                                $or_region = "N/A";
                                                $or_province = "N/A";
                                                $or_municipality = "N/A";
                                                $or_dropoff = "N/A";
                                            }
                                             $batch_data = array(
                                                'iar_number' => "Previous Season",
                                                'batchTicketNumber' => $ls_batchNumber,
                                                'coopAccreditation' => $coop_accreditation,
                                                'seedVariety' => $sv,
                                                'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                                                'region' => $or_region.' => '.$dt_region,
                                                'province' => $or_province.' => '.$dt_province,
                                                'municipality' => $or_municipality.' => '.$dt_municipality,
                                                'seedtag' => $st,
                                                'seed_grower' => 'N/A',
                                                'confirmed' => "0",
                                                'inspected' => str_replace("bag(s)", "", $bg),
                                                'deliveryDate' => date("Y-m-d", strtotime($dc)),
                                                'batch_status' => $tt,
                                                'remarks' => 'Transferred From Previous Season',
                                                'category'=> $batch_row->seed_distribution_mode,
                                        );

                                        array_push($return_arr, $batch_data);
                                        $inspected += $bg;
                                 



                                    $retransferred = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')->where("remarks", "LIKE", "%".$bt."%")->where("seedTag", $st)->get();
                                        if(count($retransferred)>0){
                                            foreach ($retransferred as $in => $par) {

                                                if($par->transferCategory == "T"){
                                                    $tt = "PARTIAL RE TRANSFER";
                                                }elseif($par->transferCategory == "W"){
                                                    $tt = "WHOLE RE TRANSFER";
                                                }else{
                                                    $tt = "";
                                                }

                                                $batch_data = array(
                                                        'iar_number' => '-',
                                                        'batchTicketNumber' => $par->batchTicketNumber,
                                                        'coopAccreditation' => $batch_row->coopAccreditation,
                                                        'seedVariety' => $par->seedVariety,
                                                        'dropOffPoint' => $dt_dropoff.' => '.$par->dropOffPoint,
                                                        'region' => $dt_region.' => '.$par->region,
                                                        'province' => $dt_province.' => '.$par->province,
                                                        'municipality' => $dt_municipality.' => '.$par->municipality,
                                                        'seedtag' => $st,
                                                        'seed_grower' =>'N/A',
                                                        'confirmed' => "-",
                                                        'inspected' => str_replace("bag(s)", "", $par->totalBagCount),
                                                        'deliveryDate' => date("Y-m-d", strtotime($par->dateCreated)),
                                                        'batch_status' => $tt,
                                                        'remarks' => "Transferred From Previous Season",
                                                        'category'=> $batch_row->seed_distribution_mode,
                                                       
                                                );

                                                array_push($return_arr, $batch_data);   
                                                $inspected_bags += $bg;
                                            }
                                        }
                                 }
                                }

                                   $total_inspected += $inspected;

                            }
                } 

        $last_row = array(
            'iar_number' => "",
            'batchTicketNumber' => '',
            'coopAccreditation' => '',
            'seedVariety' => '',
            'dropOffPoint' => '',
            'region' => '',
            'province' => '',
            'municipality' => '',
            'seedtag' => '',
            'seed_grower' => 'TOTAL: ',
            'confirmed' => number_format($total_confirmed),
            'inspected' => number_format($total_inspected),
            'deliveryDate' => '',
            'batch_status' => ''
        );

        //COMMENTED DUE TOINCONSISTENCY
        // array_push($return_arr, $last_row);


        $replacement_arr = $this->ReplacementList($coop_accreditation);
        $buffer_arr = $this->bufferList($coop_accreditation);
        $bep_arr = $this->bep_list($coop_accreditation);

        $myFile = Excel::create('SEED_COOP_DELIVERIES', function($excel) use ($return_arr, $replacement_arr, $buffer_arr, $bep_arr) {
            $excel->sheet("DELIVERY_LIST", function($sheet) use ($return_arr) {
                $sheet->fromArray($return_arr);
            });
            $excel->sheet("DELIVERY_LIST_NRP", function($sheet) use ($return_arr) {
                $sheet->fromArray($return_arr);
            });
            $excel->sheet("REPLACEMENT_LIST", function($sheet) use ($replacement_arr) {
                $sheet->fromArray($replacement_arr);
            });
            $excel->sheet("BUFFER_LIST", function($sheet) use ($buffer_arr) {
                $sheet->fromArray($buffer_arr);
            });
            $excel->sheet("BINHI_E_PADALA", function($sheet) use ($bep_arr) {
                $sheet->fromArray($bep_arr);
            });
        });

        $file_name = $coop_acr."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }
//END EXPORT
    public function ReplacementList_FMD($coop_accreditation){
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "=", 9)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 9)
                    ->first();

            if(count($is_replacement)>0){


                $label = "";


            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->sum('totalBagCount');
                

                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    



            
         
            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);              

             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'batchTicketNumber' => $bt,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => number_format($confirmed_bags),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                          
                           
                           
                        );



                        array_push($return_arr, $batch_data);
                      }
                    }
             }  //WHOLE COUNT 
             else{
                $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'dr_no' => "",
                'dr_date' => "",
                'rla_seedtag' => $batch_row->seedTag,
                'seedVariety' => $batch_row->seedVariety,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'inspected' => number_format($inspected_bags),
                'confirmed' => number_format($confirmed_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'iar_number' => $iar_number_str,
                'remarks' => $label,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedtag' => $batch_row->seedTag,
                'seedVariety2' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,

            );
            array_push($return_arr, $batch_data);
             }


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         
                            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 0)
                                ->first();

                                if(count($is_replacement)<=0){
                                    continue;
                                }

                           
                         $batch_data = array(
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => number_format($confirmed_bags),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                           
                    );
                    array_push($return_arr, $batch_data);   
             }
         } //PARTIAL COUNT
            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
            
                    


            }
        }


            $last_row = array(
            'batchTicketNumber' => '',
            'province' => '',
            'municipality' => '',
            'dr_no' => "",
            'dr_date' => "",
            'rla_seedtag' => '',
            'seedVariety' => '',
            'seed_grower' => 'TOTAL: ',
            'inspected' => number_format($total_inspected),
            'confirmed' => number_format($total_confirmed),
            'deliveryDate' => '',
            'batch_status' => '',
            'iar_number' => "",
            'remarks' => "",
            'coopAccreditation' => '',
            'seedtag' => '',
            'seedVariety2' => '',
            'dropOffPoint' => '',
            'region' => '',
           

                );
                  array_push($return_arr, $last_row);




                  


    
        return $return_arr;

    
    }



    public function ReplacementList($coop_accreditation){
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "=", 9)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 9)
                    ->first();

            if(count($is_replacement)>0){


                $label = "";


            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->sum('totalBagCount');
                

                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    


            $replacement_batch = DB::connection('delivery_inspection_db')->table('tbl_breakdown_buffer')->where('replacement_ticket',"LIKE","%".$batch_row->batchTicketNumber."%")->first();
            
            if($replacement_batch != null){
                $origin_batch = $replacement_batch->batchTicketNumber;
                $originSeedTag = $replacement_batch->seedTag;
                $origin_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery_breakdown')->where("batchTicketNumber", $origin_batch)->where("seedTag", $originSeedTag)->value("totalBagCount");
            }else{
                $origin_batch = "N/A";
                $origin_bags = "N/A";
                $originSeedTag = "N/A";
            }
            
            
            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);              

             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label,
                            'origin_batch' => $origin_batch,
                            'origin_seedTag' => $originSeedTag,
                            'origin_bags' => $origin_bags,
                           
                        );

                        array_push($return_arr, $batch_data);
                      }
                    }
             }  //WHOLE COUNT 
             else{
                $batch_data = array(
                'iar_number' => $iar_number_str,
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'remarks' => $label,
                'origin_batch' => $origin_batch,
                'origin_seedTag' => $originSeedTag,
                'origin_bags' => $origin_bags,
            );
            array_push($return_arr, $batch_data);
             }


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         
                            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 0)
                                ->first();

                                if(count($is_replacement)<=0){
                                    continue;
                                }

                           
                         $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label
                           
                    );
                    array_push($return_arr, $batch_data);   
             }
         } //PARTIAL COUNT
            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
            
                    


            }
        }


            $last_row = array(
                    'iar_number' => "",
                    'batchTicketNumber' => '',
                    'coopAccreditation' => '',
                    'seedVariety' => '',
                    'dropOffPoint' => '',
                    'region' => '',
                    'province' => '',
                    'municipality' => '',
                    'seedtag' => '',
                    'seed_grower' => 'TOTAL: ',
                    'confirmed' => number_format($total_confirmed),
                    'inspected' => number_format($total_inspected),
                    'deliveryDate' => '',
                    'batch_status' => ''
                );
                  array_push($return_arr, $last_row);


    
        return $return_arr;

    }

    public function bep_list_FMD($coop_accreditation){
        
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "!=", "1")
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;
        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            $bep_actual = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("seedTag", $batch_row->seedTag)
                    ->where("qrValStart", "!=", "")
                    ->first();

            if($bep_actual != null){
                $label = "";
            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->where("qrValStart", "!=", "")
                    ->sum('totalBagCount');
                
                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    


            
            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);              

             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'batchTicketNumber' => $bt,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => number_format($confirmed_bags),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => "From Binhi e-Padala",
                           
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                           
                            
                            
                           


                          

                        );
                       $inspected_bags += str_replace("bag(s)", "", $bg);
                        array_push($return_arr, $batch_data);
                      }
                    }
             }  //WHOLE COUNT 
             else{
                $batch_data = array( 
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'dr_no' => "",
                'dr_date' => "",
                'rla_seedtag' => $batch_row->seedTag,
                'seedVariety' => $batch_row->seedVariety,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'inspected' => number_format($inspected_bags),
                'confirmed' => number_format($confirmed_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'iar_number' => $iar_number_str,
                'remarks' => $label,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedtag' => $batch_row->seedTag,
                'seedVariety2' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                    
               


            );
            array_push($return_arr, $batch_data);
             }


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         
                            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 0)
                                ->first();

                                if(count($is_replacement)<=0){
                                    continue;
                                }

                           
                         $batch_data = array(
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => "0",
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => "From Binhi e-Padala",
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                           
                    );
                    $inspected_bags += str_replace("bag(s)", "", $bg);
                    array_push($return_arr, $batch_data);   
             }
         } //PARTIAL COUNT
            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        
            
                    


            }
        }


            $last_row = array(
                'batchTicketNumber' => '',
                'province' => '',
                'municipality' => '',
                'dr_no' => "",
                'dr_date' => "",
                'rla_seedtag' => '',
                'seedVariety' => '',
                'seed_grower' => 'TOTAL: ',
                'inspected' => number_format($total_inspected),
                'confirmed' => number_format($total_confirmed),
                'deliveryDate' => '',
                'batch_status' => '',
                'iar_number' => "",
                'remarks' => "",
                'coopAccreditation' => '',
                'seedtag' => '',
                'seedVariety2' => '',
                'dropOffPoint' => '',
                'region' => '',
                );
                  array_push($return_arr, $last_row);


    
        return $return_arr;

    
    
    }


    public function bep_list($coop_accreditation){
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "!=", "1")
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;
        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            $bep_actual = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("seedTag", $batch_row->seedTag)
                    ->where("qrValStart", "!=", "")
                    ->first();

            if($bep_actual != null){
                $label = "";
            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->where("qrValStart", "!=", "")
                    ->sum('totalBagCount');
                
                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    


            
            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);              

             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => "From Binhi e-Padala"
                           
                        );
                       $inspected_bags += str_replace("bag(s)", "", $bg);
                        array_push($return_arr, $batch_data);
                      }
                    }
             }  //WHOLE COUNT 
             else{
                $batch_data = array(
                'iar_number' => $iar_number_str,
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'remarks' => $label
            );
            array_push($return_arr, $batch_data);
             }


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         
                            $is_replacement = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 0)
                                ->first();

                                if(count($is_replacement)<=0){
                                    continue;
                                }

                           
                         $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => "0",
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => "From Binhi e-Padala"
                           
                    );
                    $inspected_bags += str_replace("bag(s)", "", $bg);
                    array_push($return_arr, $batch_data);   
             }
         } //PARTIAL COUNT
            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        
            
                    


            }
        }


            $last_row = array(
                    'iar_number' => "",
                    'batchTicketNumber' => '',
                    'coopAccreditation' => '',
                    'seedVariety' => '',
                    'dropOffPoint' => '',
                    'region' => '',
                    'province' => '',
                    'municipality' => '',
                    'seedtag' => '',
                    'seed_grower' => 'TOTAL: ',
                    'confirmed' => number_format($total_confirmed),
                    'inspected' => number_format($total_inspected),
                    'deliveryDate' => '',
                    'batch_status' => ''
                );
                  array_push($return_arr, $last_row);


    
        return $return_arr;

    
    }


    public function bufferList_FMD($coop_accreditation){
        

        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "=", 1)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            $is_buffer = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 1)
                    ->first();

            if(count($is_buffer)>0){


                $label = "";


            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->sum('totalBagCount');
                

                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    


            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'dr_no' => "",
                'dr_date' => "",
                'rla_seedtag' => $batch_row->seedTag,
                'seedVariety' => $batch_row->seedVariety,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'inspected' => number_format($inspected_bags),
                'confirmed' => number_format($confirmed_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'iar_number' => $iar_number_str,
                'remarks' => $label,

                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedtag' => $batch_row->seedTag,
                'seedVariety2' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
               
               


            

            );
            array_push($return_arr, $batch_data);
            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
            
            // $is_transfer_N = DB::connection('nxt_inspection_db')->table('tbl_actual_delivery')
            //     ->select('batchTicketNumber')
            //     ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
            //     ->where('transferCategory', 'p')
            //     ->first(); 

                $is_transfer_N = array();
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);  
                $nxt_season = count($is_transfer_N);  


             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array( 
                             'batchTicketNumber' => $bt,
                             'province' => $or_province.' => '.$dt_province,
                             'municipality' => $or_municipality.' => '.$dt_municipality,
                             'dr_no' => "",
                             'dr_date' => "",
                             'rla_seedtag' => $st,
                             'seedVariety' => $batch_row->seedVariety,
                             'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                             'inspected' => str_replace("bag(s)", "", $bg),
                             'confirmed' => number_format($confirmed_bags),
                             'deliveryDate' => date("Y-m-d", strtotime($dc)),
                             'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            
                           


            

                        );

                        array_push($return_arr, $batch_data);
                      }
                    }
             }  //WHOLE COUNT 


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         
                            $is_buffer = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 1)
                                ->first();

                                if(count($is_buffer)<=0){
                                    // continue;
                                }

                           
                         $batch_data = array(
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => "0",
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                           
                            
                          


                       


                           
                    );
                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;   
                    
             }
         } //PARTIAL COUNT


         if($nxt_season > 0){
                    $arr = $this->getNextSeasonData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        $label = "";

                         $batch_data = array(
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => $st,
                            'seedVariety' => $batch_row->seedVariety,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'confirmed' => number_format($confirmed_bags),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'iar_number' => $iar_number_str,
                            'remarks' => $label,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedtag' => $st,
                            'seedVariety2' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                           
                    );

                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;
             }
         } //NEXT SEASON COUNT










            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
            
                    


            }
        }


            $last_row = array(
                            'batchTicketNumber' => '',
                            'province' => '',
                            'municipality' => '',
                            'dr_no' => "",
                            'dr_date' => "",
                            'rla_seedtag' => '',
                            'seedVariety' => '',
                            'seed_grower' => 'TOTAL: ',
                            'inspected' => number_format($total_inspected),
                            'confirmed' => number_format($total_confirmed),
                            'deliveryDate' => '',
                            'batch_status' => '',
                            'iar_number' => "",
                            'remarks' => "",
                            'coopAccreditation' => '',
                            'seedtag' => '',
                            'seedVariety2' => '',
                            'dropOffPoint' => '',
                            'region' => '',
                );
                  array_push($return_arr, $last_row);


    
        return $return_arr;

    
    
    }



    public function bufferList($coop_accreditation){

        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag', 'isBuffer')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->where('isBuffer', "=", 1)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            $is_buffer = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $batch_row->batchTicketNumber)
                    ->where("isBuffer", 1)
                    ->first();

            if(count($is_buffer)>0){


                $label = "";


            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();


            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->sum('totalBagCount');
                

                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
            
            //get IAR number based on batch ticket number
            $iar_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')->where('batchTicketNumber',$batch_row->batchTicketNumber)->orderBy('logsId', 'DESC')->first();
            if(count($iar_number) > 0){
                $iar_number_str = $iar_number->iarCode;
            }else{
                $iar_number_str = "N/A";
            }    


            $batch_data = array(
                'iar_number' => $iar_number_str,
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status,
                'remarks' => $label
            );
            array_push($return_arr, $batch_data);
            

             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
            
            // $is_transfer_N = DB::connection('nxt_inspection_db')->table('tbl_actual_delivery')
            //     ->select('batchTicketNumber')
            //     ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
            //     ->where('transferCategory', 'p')
            //     ->first(); 

                $is_transfer_N = array();
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);  
                $nxt_season = count($is_transfer_N);  


             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber,"",$batch_row->seedVariety,$batch_row->seedTag);

                         foreach ($arr as $key => $value) {

                            if($arr[$key]['seedTag']==$batch_row->seedTag){
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                            


                        $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label
                           
                        );

                        array_push($return_arr, $batch_data);
                      }
                    }
             }  //WHOLE COUNT 


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        //transferred from batch: 519-BCH-1618556643
                         
                            $is_buffer = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("remarks", 'transferred from batch: '.$batch_row->batchTicketNumber)
                                ->where("isBuffer", 1)
                                ->first();

                                if(count($is_buffer)<=0){
                                    // continue;
                                }

                           
                         $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => "0",
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label
                           
                    );
                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;
             }
         } //PARTIAL COUNT


         if($nxt_season > 0){
                    $arr = $this->getNextSeasonData($batch_row->batchTicketNumber,$batch_row->seedVariety,$batch_row->seedTag);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];


                          if($dt != ""){
                            $dt = str_replace(",", "|", $dt);
                            $dt = str_replace("->", "|", $dt);
                            $dt = explode("|", $dt);
                            $dt_province = trim($dt[0]);
                            $dt_municipality = trim($dt[1]);
                            $dt_dropoff = trim($dt[2]);
                            $dt_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $dt_province)
                                    ->value('regionName');
                        }else{
                            $dt_region = "N/A";
                            $dt_province = "N/A";
                            $dt_municipality = "N/A";
                            $dt_dropoff = "N/A";
                        }

                        if($or != " NO INFO ON LOGS"){
                            $or = str_replace(",", "|", $or);
                            $or = str_replace("->", "|", $or);
                            $or = explode("|", $or);
                            $or_province = trim($or[0]);
                            $or_municipality = trim($or[1]);
                            $or_dropoff = trim($or[2]);

                            $or_region= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                    ->where('province', $or_province)
                                    ->value('regionName');
                        }else{
                            $or_region = "N/A";
                            $or_province = "N/A";
                            $or_municipality = "N/A";
                            $or_dropoff = "N/A";
                        }

                        $label = "";

                         $batch_data = array(
                            'iar_number' => $iar_number_str,
                            'batchTicketNumber' => $batch_row->batchTicketNumber,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => $or_dropoff.' => '.$dt_dropoff,
                            'region' => $or_region.' => '.$dt_region,
                            'province' => $or_province.' => '.$dt_province,
                            'municipality' => $or_municipality.' => '.$dt_municipality,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => "-",
                            'inspected' => str_replace("bag(s)", "", $bg),
                            'deliveryDate' => date("Y-m-d", strtotime($dc)),
                            'batch_status' => $tt,
                            'remarks' => $label
                           
                    );

                    array_push($return_arr, $batch_data);   
                    $inspected_bags += $bg;
             }
         } //NEXT SEASON COUNT










            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
            
                    


            }
        }


            $last_row = array(
                    'iar_number' => "",
                    'batchTicketNumber' => '',
                    'coopAccreditation' => '',
                    'seedVariety' => '',
                    'dropOffPoint' => '',
                    'region' => '',
                    'province' => '',
                    'municipality' => '',
                    'seedtag' => '',
                    'seed_grower' => 'TOTAL: ',
                    'confirmed' => number_format($total_confirmed),
                    'inspected' => number_format($total_inspected),
                    'deliveryDate' => '',
                    'batch_status' => ''
                );
                  array_push($return_arr, $last_row);


    
        return $return_arr;

    
    }






    public function export_coop_deliveries_old(Request $request){
        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                //->where('seedTag', $batch_row->seedTag)
                ->where('seedTag', $clean_seedTag)
				->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();

            if(count($check_inspected) > 0){
				
				//clean lab number
				//$lot_number_string = trim($clean_seedTag);
				//$lot_number_string = str_replace(' ', '', $lot_number_string);
				//$lot_number_string = preg_replace('/[^A-Za-z0-9\-]/', '', $lot_number_string);
				
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    //->where('seedTag', $batch_row->seedTag)
                    ->where('seedTag', $clean_seedTag)
					->sum('totalBagCount');
                
                $inspected_bags = $inspected_bags;
				
				$rejected_status = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    //->where('seedTag', $batch_row->seedTag)
                    ->where('seedTag', $clean_seedTag)
					->value('isRejected');

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

				if($rejected_status == 1){
					$batch_status = "Rejected";
				}else{
					if($batch_status == 0){
						$batch_status = 'Pending';
					}
					else if($batch_status == 1){
						$batch_status = 'Passed';
					}
					else if($batch_status == 2){
						$batch_status = 'Rejected';
					}else if($batch_status == 3){
						$batch_status = 'In Transit';
					}else if($batch_status == 4){
						$batch_status = 'Cancelled';
					}
				}
                
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
                
            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status
            );

            array_push($return_arr, $batch_data);
            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        }

        $last_row = array(
            'batchTicketNumber' => '',
            'coopAccreditation' => '',
            'seedVariety' => '',
            'dropOffPoint' => '',
            'region' => '',
            'province' => '',
            'municipality' => '',
            'seedtag' => '',
            'seed_grower' => 'TOTAL: ',
            'confirmed' => number_format($total_confirmed),
            'inspected' => number_format($total_inspected),
            'deliveryDate' => '',
            'batch_status' => ''
        );
        array_push($return_arr, $last_row);

        $myFile = Excel::create('SEED_COOP_DELIVERIES', function($excel) use ($return_arr) {
            $excel->sheet("DELIVERY_LIST", function($sheet) use ($return_arr) {
                $sheet->fromArray($return_arr);
            });
        });

        $file_name = $coop_acr."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

	public function export_coop_deliveries2(Request $request){
        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->groupBy('batchTicketNumber', 'seedVariety')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){
            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();

            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->sum('totalBagCount');
                
                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');
					
				$rejected_status = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
					->value('isRejected');
					
				if($rejected_status == 1){
					$batch_status = 'Rejected';
				}else{
					if($batch_status == 0){
						$batch_status = 'Pending';
					}
					else if($batch_status == 1){
						$batch_status = 'Passed';
					}
					else if($batch_status == 2){
						$batch_status = 'Rejected';
					}else if($batch_status == 3){
						$batch_status = 'In Transit';
					}else if($batch_status == 4){
						$batch_status = 'Cancelled';
					}
				}

                
            }else{
                $inspected_bags = 0;
				
				$rejected_status = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
					->value('isRejected');
					
				if($rejected_status == 1){
					$batch_status = 'Rejected';
				}else{
					$batch_status = "N/A";
				}
            }
                
            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status
            );

            array_push($return_arr, $batch_data);
            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        }

        $last_row = array(
            'batchTicketNumber' => '',
            'coopAccreditation' => '',
            'seedVariety' => '',
            'dropOffPoint' => '',
            'region' => '',
            'province' => '',
            'municipality' => 'TOTAL: ',
            'confirmed' => number_format($total_confirmed),
            'inspected' => number_format($total_inspected),
            'deliveryDate' => '',
            'batch_status' => ''
        );
        array_push($return_arr, $last_row);

        $myFile = Excel::create('SEED_COOP_DELIVERIES', function($excel) use ($return_arr) {
            $excel->sheet("DELIVERY_LIST", function($sheet) use ($return_arr) {
                $sheet->fromArray($return_arr);
            });
        });

        $file_name = $coop_acr."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }
	
	
	    public function export_coop_deliveries_032921(Request $request){
           
        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        $batch_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality', 'seedTag')
            ->where('is_cancelled', 0)
            ->where('coopAccreditation', $coop_accreditation)
            ->groupBy('batchTicketNumber', 'seedVariety', 'seedTag')
            ->orderBy('deliveryDate', 'DESC')
            ->get();


        $total_confirmed = 0;
        $total_inspected = 0;

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){

            //get seed grower profile
            //1. clean seed tag to link RLA details
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('coopAccreditation', $batch_row->coopAccreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');

            $confirmed_bags = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                //->where('seedTag', $batch_row->seedTag)
                ->where('seedTag', $clean_seedTag)
				->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();

            if(count($check_inspected) > 0){
				
				//clean lab number
				//$lot_number_string = trim($clean_seedTag);
				//$lot_number_string = str_replace(' ', '', $lot_number_string);
				//$lot_number_string = preg_replace('/[^A-Za-z0-9\-]/', '', $lot_number_string);
				
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    //->where('seedTag', $batch_row->seedTag)
                    ->where('seedTag', $clean_seedTag)
					->sum('totalBagCount');
                
                $inspected_bags = $inspected_bags;
				
				$rejected_status = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    //->where('seedTag', $batch_row->seedTag)
                    ->where('seedTag', $clean_seedTag)
					->value('isRejected');

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

				if($rejected_status == 1){
					$batch_status = "Rejected";
				}else{
					if($batch_status == 0){
						$batch_status = 'Pending';
					}
					else if($batch_status == 1){
						$batch_status = 'Passed';
					}
					else if($batch_status == 2){
						$batch_status = 'Rejected';
					}else if($batch_status == 3){
						$batch_status = 'In Transit';
					}else if($batch_status == 4){
						$batch_status = 'Cancelled';
					}
				}
                
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }
                
            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status
            );

            array_push($return_arr, $batch_data);
			
			 $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();
                
                $wholeCount = count($is_transfer_W);
                $partialCount = count($is_transfer_T);    
			
			if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber);

                         foreach ($arr as $key => $value) {
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        $batch_data = array(
  
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => 'ORIGIN',
                            'region' => $or,
                            'province' => '-',
                            'municipality' => $tt,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => $bg,
                            'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                            'batch_status' => $batch_status
                           
                        );

                        array_push($return_arr, $batch_data);
                    }
             }  //WHOLE COUNT 


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];
                         $batch_data = array(
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => 'ORIGIN',
                            'region' => $or,
                            'province' => '-',
                            'municipality' => $tt,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => $bg,
                            'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                            'batch_status' => $batch_status
                    );

                    array_push($return_arr, $batch_data);
             }

         } //PARTIAL COUNT
			
			
			

            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;
        }
		
		
        //*************** PREVIOUS SEASON TRANSFER ********************************************************
		
		$prev_batches = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('transferCategory', 'P')
                ->where('is_transferred', 1)
                ->get();

        $coop_accreditation = $request->coop_accreditation;
        $coop_acr = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accreditation)->value('coopName');
        
        foreach($prev_batches as $batch_row){
            $ls_batchNumber = trim(str_replace("transferred from previous season batch:", "", $batch_row->remarks));
            $new_batchNumber = $batch_row->batchTicketNumber;

            $ls_CoopAccre =  DB::connection('ls_inspection_db')->table('tbl_delivery')
                            ->select('coopAccreditation')
                            ->where('batchTicketNumber', $ls_batchNumber)
                            ->where('coopAccreditation', $coop_accreditation)
                            ->first();

            if(count($ls_CoopAccre)<0){
            $str = explode("/", $batch_row->seedTag);
            $seedtag_offset = $this->my_ofset($str[0]);

            $clean_seedTag = substr($str[0], $seedtag_offset, strlen($str[0]));
            $seed_grower = DB::connection('ls_inspection_db')->table('tbl_rla_details')
                ->where('coopAccreditation', $coop_accreditation)
                ->where('labNo', $clean_seedTag)
                ->value('sg_name');


            $confirmed_bags = DB::connection('ls_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $ls_batchNumber)
                ->where('coopAccreditation', $coop_accreditation)
                ->where('seedVariety', $batch_row->seedVariety)
                ->where('seedTag', $batch_row->seedTag)
                ->sum('totalBagCount');

            $check_inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $new_batchNumber)
                ->first();

            if(count($check_inspected) > 0){
                $inspected_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->where('seedTag', $batch_row->seedTag)
                    ->sum('totalBagCount');
                
                $inspected_bags = $inspected_bags;

                $batch_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->orderBy('deliveryStatusId', 'DESC')
                    ->value('status');

                if($batch_status == 0){
                    $batch_status = 'Pending';
                }
                else if($batch_status == 1){
                    $batch_status = 'Passed';
                }
                else if($batch_status == 2){
                    $batch_status = 'Rejected';
                }else if($batch_status == 3){
                    $batch_status = 'In Transit';
                }else if($batch_status == 4){
                    $batch_status = 'Cancelled';
                }
            }else{
                $inspected_bags = 0;
                $batch_status = "N/A";
            }

            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'seedtag' => $batch_row->seedTag,
                'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                'confirmed' => number_format($confirmed_bags),
                'inspected' => number_format($inspected_bags),
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status
            );
            array_push($return_arr, $batch_data);


             $is_transfer_W = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('transferCategory')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('is_transferred', 1)
                ->where('transferCategory', 'W')
                ->first();

            $is_transfer_T = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('batchTicketNumber')
                ->where('remarks','like', '%'.$batch_row->batchTicketNumber.'%')
                ->where('is_transferred', 1)
                ->where('transferCategory', 'T')
                ->first();

             $wholeCount = count($is_transfer_W);
             $partialCount = count($is_transfer_T);

             if($wholeCount > 0 ){
                     $arr = $this->getWholeData($batch_row->batchTicketNumber);

                         foreach ($arr as $key => $value) {
                            $bt = $arr[$key]['batch_num'];
                            $or = $arr[$key]['origin'];
                            $dt = $arr[$key]['destination'];
                            $sv = $arr[$key]['seedVariety'];
                            $st = $arr[$key]['seedTag'];
                            $sdt = $arr[$key]['seedType'];
                            $bg = $arr[$key]['bags'];
                            $dc = $arr[$key]['dateCreated'];
                            $tt = $arr[$key]['transferType'];
                        
                        $batch_data = array(
  
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => 'ORIGIN',
                            'region' => $or,
                            'province' => '-',
                            'municipality' => $tt,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => $bg,
                            'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                            'batch_status' => $batch_status
                           
                        );

                        array_push($return_arr, $batch_data);
                    }
             }  //WHOLE COUNT 


             if($partialCount > 0){
                    $arr = $this->getPartialData($batch_row->batchTicketNumber);

                    foreach ($arr as $key => $value) {
                        $bt = $arr[$key]['batch_num'];
                        $or = $arr[$key]['origin'];
                        $dt = $arr[$key]['destination'];
                        $sv = $arr[$key]['seedVariety'];
                        $st = $arr[$key]['seedTag'];
                        $sdt = $arr[$key]['seedType'];
                        $bg = $arr[$key]['bags'];
                        $dc = $arr[$key]['dateCreated'];
                        $tt = $arr[$key]['transferType'];
                         $batch_data = array(
                            'batchTicketNumber' => $bt,
                            'coopAccreditation' => $batch_row->coopAccreditation,
                            'seedVariety' => $batch_row->seedVariety,
                            'dropOffPoint' => 'ORIGIN',
                            'region' => $or,
                            'province' => '-',
                            'municipality' => $tt,
                            'seedtag' => $st,
                            'seed_grower' => $seed_grower == '' ? 'N/A' : $seed_grower,
                            'confirmed' => number_format($confirmed_bags),
                            'inspected' => $bg,
                            'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                            'batch_status' => $batch_status
                    );

                    array_push($return_arr, $batch_data);
             }

         } //PARTIAL COUNT

            $total_confirmed += $confirmed_bags;
            $total_inspected += $inspected_bags;


            } //IF COOP IS TRUE



        }
		
		
		
		
		

        $last_row = array(
            'batchTicketNumber' => '',
            'coopAccreditation' => '',
            'seedVariety' => '',
            'dropOffPoint' => '',
            'region' => '',
            'province' => '',
            'municipality' => '',
            'seedtag' => '',
            'seed_grower' => 'TOTAL: ',
            'confirmed' => number_format($total_confirmed),
            'inspected' => number_format($total_inspected),
            'deliveryDate' => '',
            'batch_status' => ''
        );
        array_push($return_arr, $last_row);

        $myFile = Excel::create('SEED_COOP_DELIVERIES', function($excel) use ($return_arr) {
            $excel->sheet("DELIVERY_LIST", function($sheet) use ($return_arr) {
                $sheet->fromArray($return_arr);
            });
        });

        $file_name = $coop_acr."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

	
	
	

}

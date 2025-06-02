<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Support\Facades\Schema;
use Config;
use DB;
use Excel;

use App\HistoryMonitoring;
use App\Regions;
use App\Provinces;
use App\Municipalities;

use Session;
use Auth;

class HistoryMonitoringController extends Controller
{
      public function index()
    {
        $provinces_list = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('*')
            ->where('send', '1')
            ->where('transferCategory','P')
            ->orderBy('province', 'ASC')
            ->groupBy('province')
            ->get();  

        return view('HistoryMonitoring.home')
             ->with('municipal_list', $provinces_list)
             ->with('user_level', Auth::user()->roles->first()->name)
             ->with('userName', Auth::user()->username);
    }

    public function get_province($type){
       // 0->PSTOCS 1->CSTOCS_WHOLE 2->CSTOCS_PARTIAL
        if($type == 0){
            $dataList = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('*')
            ->where('send', '1')
             ->where('transferCategory','P')
            ->orderBy('province', 'ASC')
            ->groupBy('province')
            ->get(); 
        }
        else if($type == 1){  //destination_province
             $dataList = DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->select('*')
            ->where('seed_variety','like','ALL_SEEDS_TRANSFER')
            ->orderBy('destination_province', 'ASC')
            ->groupBy('destination_province')
            ->get();
        }
        else if($type == 2){ //province

            $batch_last = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select(DB::raw("CONCAT('transferred from batch: ',batchTicketNumber) as batchTicketNumber"))
            ->where('is_transferred', '1')
             ->where('transferCategory','P')
            ->orderBy('municipality', 'ASC')
            ->groupBy('batchTicketNumber')
            ->get(); 
            $batch_last = json_decode(json_encode($batch_last), true);

             $dataList = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('*')
            ->where('is_transferred', '1')            
            ->where('transferCategory','T')
            ->whereNotIn('remarks', $batch_last)
            ->orderBy('province', 'ASC')
            ->groupBy('province')
            ->get();  
        }

        echo json_encode($dataList);
    }

    public function get_municipalities($province,$transfer)
    {  //CSTOCS

        if($transfer=='ALL_SEEDS_TRANSFER_old'){
            $transfersearch = '%'.$transfer.'%';
            $municipalities_list = DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->select('*')
            ->where('destination_province', $province)
            ->where('seed_variety','like',$transfersearch)
            ->orderBy('destination_municipality', 'ASC')
            ->groupBy('destination_municipality')
            ->get();
        }elseif($transfer=='ALL_SEEDS_TRANSFER'){
            $transfersearch = '%'.$transfer.'%';
            $municipalities_list = DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->select('*')
            ->where('destination_province', $province)
            ->where('seed_variety','like',$transfersearch)
            ->orderBy('destination_municipality', 'ASC')
            ->groupBy('destination_municipality')
            ->get();




        }elseif($transfer=='PARTIAL_SEEDS_TRANSFER'){
            $batch_last = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select(DB::raw("CONCAT('transferred from batch: ',batchTicketNumber) as batchTicketNumber"))
            ->where('province', $province)
            ->where('is_transferred', '1')
             ->where('transferCategory','P')
            ->orderBy('municipality', 'ASC')
            ->groupBy('batchTicketNumber')
            ->get(); 
            $batch_last = json_decode(json_encode($batch_last), true);

            $municipalities_list = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('*')
            ->where('province', $province)
            ->where('is_transferred', '1')
            ->where('transferCategory','T')
            ->whereNotIn('remarks', $batch_last)
            ->orderBy('municipality', 'ASC')
            ->groupBy('municipality')
            ->get();  
        }        
        echo json_encode($municipalities_list);
    }

     public function get_municipalities2($province) //PSTOCS
    {
         $municipalities_list = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('*')
            ->where('province', $province)
            ->where('is_transferred', '1')
             ->where('transferCategory','P')
            ->orderBy('municipality', 'ASC')
            ->groupBy('municipality')
            ->get();  
        echo json_encode($municipalities_list); 
    }


//HistoryMonitoring/generate/cstocs
//HistoryMonitoring/generate/pstocs

    public function generateHistoryData(Request $request){
       
        if($request->transfer_type=="ALL_SEEDS_TRANSFER"){
            return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->where('destination_province', $request->provName)
                ->where('destination_municipality', $request->muniName)
                
                ->where('seed_variety','like','%ALL_SEEDS_TRANSFER%')
                ->orderBy('date_created', 'DESC')
            )
            ->addColumn('origin', function($row){
                $dop = new HistoryMonitoring();
                $dop_details = $dop->retrieveDOP($row->origin_dop_id);
                return $dop_details;
                })
            ->addColumn('destination', function($row){  
                $dop = new HistoryMonitoring();
                $dop_details = $dop->retrieveDOP($row->destination_dop_id);
                return $dop_details;
                })
            ->addColumn('action', function($row){
                $dop = new HistoryMonitoring();
                $dest_details = $dop->retrieveDOP($row->destination_dop_id);
                $origin_details = $dop->retrieveDOP($row->origin_dop_id);
                $origin_id = $row->origin_dop_id;

                if(Auth::user()->username == $row->transferred_by ||Auth::user()->username == "v.villadon"||Auth::user()->username == "bm.delossantos" || Auth::user()->roles->first()->name == "branch-it"){
                return "<a class='btn btn-warning btn-xs' data-rowid='$row->transfer_id' data-dest='$dest_details' data-origin='$origin_details' data-batch='$row->batch_number' data-bcount='$row->bags' data-doporigin='$origin_id' data-ttype='cstocs_all' data-toggle='modal' data-target='#deleteHistoryData'><i class='fa fa-trash'></i> CANCEL</a>";
                }else{
                return $row->transferred_by;
                }

                })
                ->make(true);
        }elseif($request->transfer_type=="PARTIAL_SEEDS_TRANSFER"){

            return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('province', $request->provName)
                ->where('municipality', $request->muniName)
                ->where('is_transferred', 1)
                ->where('remarks','like','%transferred from batch:%')
                ->where('prv_dropoff_id', '<>', '""')
                ->orderBy('remarks', 'ASC')
                //->orderBy('date_created', 'DESC')
            )
            ->addColumn('batch_number', function($row){   
                return trim($row->remarks, 'transferred from batch:');
            })
            ->addColumn('origin', function($row){
                $batch_number = trim($row->remarks, 'transferred from batch:');
                 $dop = new HistoryMonitoring();
                 $origin_details = $dop->getTransferLogInfo('origin_dop_id',$batch_number,$row->prv_dropoff_id);
                   if($origin_details != 0){
                     $gathered_data =  $dop_details = $dop->retrieveDOP($origin_details);
                   }else{
                     $gathered_data = "<font color=red> <b> **No data on Transfer Logs** </b> </font>";
                   }
                
                return $gathered_data;
            })          
            ->addColumn('destination', function($row){
              $dop = new HistoryMonitoring();
              $dop_details = $dop->retrieveDOP($row->prv_dropoff_id);
              return $dop_details;
            })
            ->addColumn('seed_variety', function($row){
                $seed_variety = '<b>SEED TAG:</b> '.$row->seedTag.'<br> <b>Seed Variety:</b> '.$row->seedVariety;

                return $seed_variety;
            })
            ->addColumn('bags', function($row){
                return $row->totalBagCount;
            })
            ->addColumn('date_created', function($row){
                return $row->date_modified;
            })
            ->addColumn('action', function($row){
                


                $dop = new HistoryMonitoring();
                $dest_details = $dop->retrieveDOP($row->prv_dropoff_id);

                    $batch_number = trim($row->remarks, 'transferred from batch:');       
                    $get_origin_id = $dop->getTransferLogInfo('origin_dop_id',$batch_number,$row->prv_dropoff_id);

                        if($get_origin_id != 0){
                            $gathered_data  = $dop->retrieveDOP($get_origin_id);
                            $origin_details = $gathered_data;
                            $origin_id = $get_origin_id;
                        
                      $transferlogid = $dop->getTransferLogInfo('transfer_id',$batch_number,$row->prv_dropoff_id);
                      $transferBY = $dop->getTransferLogInfo('transferred_by',$batch_number,$row->prv_dropoff_id);
                if(Auth::user()->username == $transferBY ||Auth::user()->username == "v.villadon"||Auth::user()->username == "bm.delossantos" || Auth::user()->roles->first()->name == "branch-it"){
                return "<a class='btn btn-warning btn-xs' data-rowid='$row->actualDeliveryId' data-dest='$dest_details' data-origin='$origin_details' data-batch='$batch_number' data-bcount='$row->totalBagCount' data-doporigin='$origin_id' data-ttype='cstocs_partial' data-seedtag='$row->seedTag' data-transferlogid='$transferlogid'  data-toggle='modal' data-target='#deleteHistoryData'><i class='fa fa-trash'></i> CANCEL</a>";
                }else{
                    if(Auth::user()->roles->first()->name == "rcef-programmer" ||Auth::user()->username == "v.villadon"||Auth::user()->username == "bm.delossantos" || Auth::user()->roles->first()->name == "branch-it"){
                        return "<a class='btn btn-warning btn-xs' data-rowid='$row->actualDeliveryId' data-dest='$dest_details' data-origin='$origin_details' data-batch='$batch_number' data-bcount='$row->totalBagCount' data-doporigin='$origin_id' data-ttype='cstocs_partial' data-seedtag='$row->seedTag' data-transferlogid='$transferlogid'  data-toggle='modal' data-target='#deleteHistoryData'><i class='fa fa-trash'></i> CANCEL</a>";
                    }else{
                        return $transferBY;
                    }


                    
                }
                        }
            })
             ->make(true);
              
        } //ELSEIF(PARTIAL)                
    } //QUERY DATA TABLE


    public function ps_cancel(Request $request){
        $transferred_seeds =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery') 
        ->where("remarks", "LIKE", "%".$request->curr_season."%")
        ->where("seedTag", $request->seedtag )
        ->get();
        
        if(count($transferred_seeds)>0){
            return json_encode("Cancel First The Partial Transfer");
        }else{
           
           $cur_data =   DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')  
                ->where("batchTicketNumber", $request->curr_season)
                ->where("seedTag", $request->seedtag )
                ->first();

            if($cur_data != null){
                if($cur_data->transferCategory == "T"){
                    $bag = $cur_data->totalBagCount;
                    $orig_actual = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery') 
                            ->where("batchTicketNumber", $request->last_season)
                            ->where("seedTag", $request->seedtag )
                            ->first();
                    if($orig_actual != null){
                        $sum_bag = $orig_actual->totalBagCount+$bag;
                        //UPDATE 
                        DB::connection('delivery_inspection_db')->table('tbl_actual_delivery') 
                            ->where("actualDeliveryId", $orig_actual->actualDeliveryId)
                            ->update([
                                "totalBagCount" => $sum_bag
                            ]);

                        DB::connection('delivery_inspection_db')->table('tbl_actual_delivery') 
                            ->where("actualDeliveryId", $cur_data->actualDeliveryId)
                            ->delete();

                        DB::connection('delivery_inspection_db')->table('tbl_inspection_for_breakdown')
                            ->where("seedTag", $request->seedtag ) 
                            ->where("batchTicketNumber", $request->last_season)
                            ->delete();

                        return json_encode("Partial Transfer Reverted");

                    }else{
                        return json_encode("Can't Find Last Data");
                    }

                }elseif($cur_data->transferCategory == "P"){
                    $bag = $cur_data->totalBagCount;
                    $orig_actual = DB::connection('ls_inspection_db')->table('tbl_actual_delivery') 
                        ->where("batchTicketNumber", $request->last_season)
                        ->where("seedTag", $request->seedtag )
                        ->first();
                    if($orig_actual != null){
                            $sum_bag = $orig_actual->totalBagCount+$bag;
                            //UPDATE 
                            DB::connection('ls_inspection_db')->table('tbl_actual_delivery') 
                                ->where("actualDeliveryId", $orig_actual->actualDeliveryId)
                                ->update([
                                    "totalBagCount" => $sum_bag
                                ]);
    
                            DB::connection('delivery_inspection_db')->table('tbl_actual_delivery') 
                                ->where("actualDeliveryId", $cur_data->actualDeliveryId)
                                ->delete();

                            DB::connection('delivery_inspection_db')->table('tbl_inspection_for_breakdown')
                                ->where("seedTag", $request->seedtag ) 
                                ->where("batchTicketNumber", $request->last_season)
                                ->delete();
        
                            return json_encode("Previous Transfer Cancelled");
    
                    }else{
                            return json_encode("Can't Find Last Data");
                    }


                }else{
                    return json_encode("undefined");
                }   

            }else{
                return json_encode("Can't Find Data");
            }



        }

     

    }





    public function generateHistoryData2(Request $request){
        $data = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('province', $request->provName)
            ->where('municipality', $request->muniName) 
            ->where("transferCategory", 'P')
            ->orderBy("batchTicketNumber")
            ->get();
        $tbl_list= array();
        foreach($data as $batch){
            //orig_batch == "LAST SEASON BATCH";
            $orig_batch = str_replace("transferred from previous season batch: ","", $batch->remarks);
            $batchNumber = "Orig Batch: ".$orig_batch . "<br>";
            $batchNumber .= "New Batch: ".$batch->batchTicketNumber;

            $origin_location = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                ->select(DB::raw("CONCAT(region,', ',province,', ',municipality, '=> ',dropOffPoint) as orig_loc"))
                ->where("batchTicketNumber",$orig_batch)
                ->value("orig_loc");

          

            $dest_location = $batch->region.', '.$batch->province.', '.$batch->municipality.'=> '.$batch->dropOffPoint;
            $seed_variety = '<b>SEED TAG:</b> '.$batch->seedTag.' <br> <b>Seed Variety:</b> '.$batch->seedVariety;

            if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "branch-it"){
                $btn = "<button class='btn btn-warning' onclick='ps_cancel(".'"'.$orig_batch.'"'.",".'"'.$batch->batchTicketNumber.'"'.",".'"'.$batch->seedTag.'"'.");' > Cancel </button>";
            }else{
                $btn = "-";
            }

            array_push($tbl_list, array(
                "batch_number" => $batchNumber,
                "origin" => $origin_location,
                "destination" => $dest_location,
                "seed_variety" => $seed_variety,
                "bags" => $batch->totalBagCount,
                "date_created" => $batch->dateCreated,
                "action" => $btn,
            ));
           
            $transferred_seeds =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery') 
                    ->where("remarks", "LIKE", "%".$batch->batchTicketNumber."%")
                    ->where("seedTag", $batch->seedTag )
                    ->get();
                foreach($transferred_seeds as $trans){
                    if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "branch-it"){
                        $btn_partial = "<button class='btn btn-warning' onclick='ps_cancel(".'"'.$batch->batchTicketNumber.'"'.",".'"'.$trans->batchTicketNumber.'"'.",".'"'.$batch->seedTag.'"'.");' > Cancel Re-Transfer </button>";
                    }else{
                        $btn_partial = "-";
                    }


                    $batchNumber = "Orig Batch: ".$batch->batchTicketNumber . "<br>";
                    $batchNumber .= "New Batch: ".$trans->batchTicketNumber;

                    $orig_location = $batch->region.', '.$batch->province.', '.$batch->municipality.'=> '.$batch->dropOffPoint;
                    $dest_location = $trans->region.', '.$trans->province.', '.$trans->municipality.'=> '.$trans->dropOffPoint;
                    array_push($tbl_list, array(
                        "batch_number" => $batchNumber,
                        "origin" => $orig_location,
                        "destination" => $dest_location,
                        "seed_variety" => $seed_variety,
                        "bags" => $trans->totalBagCount,
                        "date_created" => $trans->dateCreated,
                        "action" => $btn_partial,
                    ));
                }
        }


        
            $tbl_list = collect($tbl_list);

            return Datatables::of($tbl_list)
                ->make(true);



    }


    public function generateHistoryData2_old(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('*')
                ->where('province', $request->provName)
                ->where('municipality', $request->muniName)
                ->where('send', '1')
                ->where('prv_dropoff_id', '<>', '""')
                 ->where('transferCategory','P')
                ->orderBy('municipality', 'ASC')
            )
            ->addColumn('batch_number', function($row){
               // return $row->batchTicketNumber;
                 return trim(str_replace("transferred from previous season batch:", "", $row->remarks));


            })
            ->addColumn('origin', function($row){
               
                    $orig =  DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs as l')
                        ->select('l.origin_province', 'l.origin_municipality')
                        //->select('l.origin_province', 'l.origin_municipality', 'a.dropOffPoint')
                        //->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point as a', 'l.origin_dop_id', '=', 'a.prv_dropoff_id')
                        ->where('new_batch_number', $row->batchTicketNumber)
                        ->first();
                       //dd($orig);
                    if(count($orig)>0){
                        return $orig->origin_province.' - '.$orig->origin_municipality;
                    }else{ 
                        return "From Previous Season";
                    }




            })          
            ->addColumn('destination', function($row){
              $dop = new HistoryMonitoring();
              $dop_details = $dop->retrieveDOP($row->prv_dropoff_id);
              return $dop_details;
            })
            ->addColumn('seed_variety', function($row){
                $seed_variety = '<b>SEED TAG:</b> '.$row->seedTag.' <br> <b>Seed Variety:</b> '.$row->seedVariety;

                return $seed_variety;
            })
            ->addColumn('bags', function($row){
                return $row->totalBagCount;
            })
            ->addColumn('date_created', function($row){
                return $row->dateCreated;
            })
            ->addColumn('action', function($row){
                $log =  DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                        ->select('transfer_id','transferred_by', 'coop_accreditation', 'origin_dop_id','new_batch_number')  
                        ->where('new_batch_number', $row->batchTicketNumber)
                        ->first();
                $dop = new HistoryMonitoring();
                $dest_details = $dop->retrieveDOP($row->prv_dropoff_id);

                //

                    if(count($log)>0){
                        $transferBY = $log->transferred_by;
                        $coop_accre = $log->new_batch_number;
                        $transferlogid = $log->transfer_id;
                        $origin_details = $log->origin_dop_id;
                        $origin_id = $log->origin_dop_id;
                    }else{
                            $origin_details = "PREVIOUS SEASON";
                            $origin_id = $row->prv_dropoff_id;
                            $transferBY = "No Logs";
                            $coop_accre = "TRANSFER";
                    }

                     if(Auth::user()->username == $transferBY ||Auth::user()->username == "v.villadon"||Auth::user()->username == "bm.delossantos" || Auth::user()->roles->first()->name == "branch-it"){
                        return "<a class='btn btn-warning btn-xs' data-rowid='$row->actualDeliveryId' data-dest='$dest_details' data-origin='$origin_details' data-batch='$coop_accre' data-bcount='$row->totalBagCount' data-doporigin='$origin_id' data-ttype='pstocs' data-seedtag='$transferBY' data-transferlogid='$transferlogid'  data-toggle='modal' data-target='#deleteHistoryData'><i class='fa fa-trash'></i> CANCEL </a> <br>";
                     }else{
                            return $transferBY;
                        }


            })
             ->make(true);
    }

    public function processHistory(Request $request){ //CSTOCS ALL

         //get destination region,province,municipality,dropoff etc. based on destination dop_id
            $dropoff_point_data = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->dropofforigin)
                ->groupBy('prv_dropoff_id')
                ->first();

            //update data in tbl_actual_delivery -> RETURN to ORIGINAL DATA
            DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $request->batch_number)
            ->update([
                'region' => $dropoff_point_data->region,
                'province' => $dropoff_point_data->province,
                'municipality' => $dropoff_point_data->municipality,
                'dropOffPoint' => $dropoff_point_data->dropOffPoint,
                'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                'prv' => $dropoff_point_data->prv,
                'is_transferred' => '0'
            ]);

            //UPDATE TRANSFER LOGS
            DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->where('transfer_id', $request->rowid)
            ->update([
                'seed_variety' => "ALL_SEED_CANCEL"
            ]);


            //LOGS
             DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'TRANSFER_WHOLE_CANCEL',
                'description' => 'Transfer Cancel seeds of batch ticket #: `'.$request->batch_number.'`',
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
            DB::commit();
            
          

		return "SUCCESS CANCEL";
    }

public function processHistory2(Request $request){ //CSTOCS PARTIAL

            //get seed tag details based on batch number & seed tag on tbl_actual_delivery
                $seedtag_details = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $request->batch_number)
                    ->where('seedTag', $request->seedtag)
                    ->first();


             //update total bags per seed tag in tbl_actual_delivery of original batch ticket #
                DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $request->batch_number)
                ->where('seedTag', $request->seedtag)
                ->update([
                    'totalBagCount' => intval($seedtag_details->totalBagCount) + intval($request->bcount),
                ]);

                $actual_for_del = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where('actualDeliveryId', $request->rowid)
                    ->first();
            
                if($actual_for_del != null){
                    $tran = DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction')
                        ->where('batchTicketNumber', $actual_for_del->batchTicketNumber)
                        ->first();

                        if($tran != null){
                            DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction')
                            ->where('batchTicketNumber', $actual_for_del->batchTicketNumber)
                            ->update([
                                "instructed_delivery_volume" => intval($tran->instructed_delivery_volume) - intval($request->bcount),
                            ]);

                        }
                         //DELETE ACTUAL TABLE 
                        DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                        ->where('actualDeliveryId', $request->rowid)
                        ->delete();

                }



               



      

            //UPDATE TRANSFER LOGS
            DB::table($GLOBALS['season_prefix'].'rcep_transfers.transfer_logs')
            ->where('transfer_id', $request->transferlogid)
            ->update([
                'seed_variety' => "PARTIAL_SEED_CANCEL"
            ]);


            //LOGS
             DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'TRANSFER_PARTIAL_CANCEL',
                'description' => 'Transfer Cancel seeds of batch ticket #: `'.$request->batch_number.'`'. 'Seed Tag : `'
                .$request->seedtag.'`'
                ,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
            DB::commit();
            
          

        return "SUCCESS CANCEL";
    }


public function processHistory3(Request $request){ //PSTOCS

             
            $prevInfo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('actualDeliveryId', $request->rowid)
                    ->first();
            $dropoffdetails = $prevInfo->province.' - '.$prevInfo->municipality.' -> '.$prevInfo->dropOffPoint;

            $prevBag = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', trim(str_replace("transferred from previous season batch:", "", $prevInfo->remarks)))
                ->where('seedTag', $prevInfo->seedTag)
                ->sum('totalBagCount');

                
          //UPDATE BAGS ON OLD SEASON
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', trim(str_replace("transferred from previous season batch:", "", $prevInfo->remarks)))
                ->where('seedTag', $prevInfo->seedTag)
                ->update([
                    'totalBagCount' => intval($prevBag) + intval($prevInfo->totalBagCount),
                ]);  
                    
            //DELETE ACTUAL TABLE 
            DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('actualDeliveryId', $request->rowid)
                ->delete(); 


            $logData = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                ->where('transfer_id', $request->transferlogid)
                ->first();

               // dd($logData);
                    $variety_arr = explode("|", $logData->seed_variety);
                        $indicator = 0;
                        $logSeedVariety = "";
                        foreach ($variety_arr as  $varie) {
                            if($indicator == 0){
                                if(trim($varie) == trim($prevInfo->seedVariety)){
                                    $indicator = 1;
                                }else{
                                    if($logSeedVariety!="")$logSeedVariety.="|";
                                    $logSeedVariety .= $varie;
                                }
                            }else{
                                if($logSeedVariety!="")$logSeedVariety.="|";
                                $logSeedVariety .= $varie;   
                            }
                        }
        

             //UPDATE LOG
            DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                ->where('transfer_id', $request->transferlogid)
                ->update([
                    'seed_variety'=>$logSeedVariety,
                    'bags' =>intval($logData->bags) - intval($prevInfo->totalBagCount) 
                ]);




            /*//UPDATE TRANSFER LOGS
            DB::connection('ls_rcep_transfers_db')->table('transfer_logs')
            ->where('transfer_id', $request->transferlogid)
            ->update([
                'seed_variety' => 'CANCELLED',
                'bags' => 0
            ]); */


            //LOGS
             DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'TRANSFER_PREVIOUS_SEASON_CANCEL',
                'description' => 'Transfer Previous Season Cancel of seed drop off #: `'.$dropoffdetails.'`'. 'Batch : `'
                .$prevInfo->batchTicketNumber.'` Total Bags Cancelled:'. $prevInfo->totalBagCount.'`'
                ,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);
            DB::commit();
            
          

        return "SUCCESS CANCEL";
        
    }






}

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

class StocksMonitoringController extends Controller
{











  //FOR STOCKS MONITORING
      public function index()
    {
         $provinces_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
			             ->select('province')
                         ->groupBy('province')
                         ->orderBy('province')
                         ->get();


        return view('stocksMonitoring.index')
             ->with('provinces_list', $provinces_list);

	 }

     public function getLocation(Request $request) //PSTOCS
    {
        if($request->location =='municipality'){
           $returnLocation = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                         ->select('municipality')
                         ->where('province', $request->province)
                         ->groupBy('municipality')
                         ->orderBy('municipality')
                         ->get();
        }elseif($request->location =='dropOffPoint'){
            $returnLocation = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                         ->select('dropOffPoint')
                         ->where('municipality', $request->municipality)
                         ->groupBy('dropOffPoint')
                         ->orderBy('dropOffPoint')
                         ->get();
        }
        echo json_encode($returnLocation); 
    }

     public function generateData(Request $request)
     {
        // dd($request->all());

          
            $province = $request->province;
            if($request->municipality == "0"){
                $municipality = "%";
            }else{
                $municipality = $request->municipality;
            }

            if($request->dropOffPoint == "0"){
                $dropOffPoint = "%";
            }else{
                $dropOffPoint = $request->dropOffPoint;
            }

      
                $batch_delivery_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                            ->where('province', 'like', $province)
                            ->where('municipality', 'like', $municipality.'%')
                            ->where('dropOffPoint', 'like', $dropOffPoint.'%')
                            ->groupBy('prv_dropoff_id', 'seedVariety')                            
                            ->orderBy('batchTicketNumber', 'ASC')
                            ->orderBy('province', 'ASC')
                            ->get();
            // dd($batch_delivery_list);
                $data_arr = array();
                foreach($batch_delivery_list as $row){
                    $variety_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                          //  ->where('batchTicketNumber', $row->batchTicketNumber)
                            ->where('prv_dropoff_id', $row->prv_dropoff_id)
                            ->where('seedVariety', $row->seedVariety)
                            ->get();

                    $seedTag = "";
                    foreach($variety_list as $variety_row){
                        $seedTag .= $variety_row->seedTag."<br> ";
                    }

                    $seedTag = rtrim($seedTag, "<br> ");


                    $totalBagCount = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->where('seedVariety', $row->seedVariety)
                       // ->where('batchTicketNumber', $row->batchTicketNumber)
                        ->where('prv_dropoff_id', $row->prv_dropoff_id)
                        ->where('qrStart', "<=", 0)
                        ->sum('totalBagCount');

                    $prv = substr($row->prv,0,4);

                    $total_released = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.new_released')
                        ->where('seed_variety', $row->seedVariety)
                        ->where('prv_dropoff_id', $row->prv_dropoff_id)
                        ->sum('bags_claimed');
                    
                    $stocks_downloaded = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                        ->where('seed_variety', $row->seedVariety)
                        ->where('prv_dropoff_id', $row->prv_dropoff_id)
                        ->where('is_cleared' , 0)
                        ->sum('number_of_bag');

                     if(count($stocks_downloaded)>0){
                            $stocks_down = $stocks_downloaded;
                            $bi = "Stocks Downloaded:".$stocks_downloaded.'<br>'; 
                        }else{
                            $stocks_down = 0;
                            $bi = "";
                        }

                    if(count($total_released)>0){
                        

                        $bagInfo = "Accepted:".$totalBagCount.'<br>';
                        $bagInfo .= "Distributed:".$total_released.'<br>';
                        $bagInfo .= $bi;



                        $total = $totalBagCount - ($total_released + $stocks_down); 



                        if($total <= 0){
                            $f = "<font color='red'>";
                        }else{
                            $f = "<font color='green'> ";
                        }

                        $bagInfo .= $f."<b> Total Stocks:".$total.'</b></font>';
                    }else{
                         if($totalBagCount <= 0){
                            $f = "<font color='red'>";
                        }else{
                            $f = "<font color='green'> ";
                        }



                        $bagInfo = "Accepted:".$totalBagCount.'<br>';
                        $bagInfo .= $bi;
                        
                        $total = $totalBagCount - $stocks_down; 

                         $bagInfo .= $f."<b> Total Stocks:".$total.'</b></font>';

                    }

                    


                    array_push($data_arr, array(
                        'batchTicketNumber' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'dropOffPoint' => $row->dropOffPoint,
                        'seed_variety' => $row->seedVariety,
                        'seed_tag' => $seedTag,
                        'bags' => $bagInfo,
                    ));
                }

                $data_arr = collect($data_arr);

                return Datatables::of($data_arr)
                ->make(true);
     }











}

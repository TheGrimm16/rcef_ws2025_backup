<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Yajra\Datatables\Facades\Datatables;

class DistMonitoringController extends Controller
{

 


	public function stocks_seedType(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->groupBy('province')->get();
        return view('app_monitoring.stocks.home_seedType')
            ->with('provinces', $provinces);
    }

    public function get_actual_municipalities(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
            
        $return_str= '';
        foreach($municipalities as $row){
            $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
        }
        return $return_str;
    }


    public function stocks_actual_tbl(Request $request){

            if($request->province == '0'){$province='%';}else{$province=$request->province;}
            if($request->municipality == '0'){$municipality='%';}else{$municipality=$request->municipality;}
            if($request->status == '2'){$status='%';}else{$status=$request->status;}
        $return_arr = array();
            $actualData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('province', 'like', '%'.$province)
                    ->where('municipality', 'like', '%'.$municipality)
                    ->where('isBuffer', '1')
                    //->where('is_transferred', '1')
                    ->groupBy('batchTicketNumber')
                    ->get();

                foreach ($actualData as $actualValue) {
                    /*$getUserName = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
                        ->where('batchTicketNumber', $actualValue->batchTicketNumber)
                        ->first();

                        if(count($getUserName)>0){$userName = $getUserName->user_id;}else{$username="-";}
                      */

                    $batchTicketNumber = $actualValue->batchTicketNumber;
                    $province = $actualValue->province;
                    $municipality = $actualValue->municipality;
                    $dropOffPoint = $actualValue->dropOffPoint;
                    $seed_varieties = "";
                        $seedVarieties = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                ->select('seedVariety', DB::raw('sum(totalBagCount) as sumPerVariety'))
                                ->where('batchTicketNumber', $actualValue->batchTicketNumber)
                                ->groupBy('seedVariety')
                                ->get();
                                
                                foreach ($seedVarieties as $seedData) {
                                    if($seed_varieties !="")$seed_varieties.="<br>";
                                    $seed_varieties .= "<b>".$seedData->seedVariety."</b>: ".$seedData->sumPerVariety . " bag(s)";
                                }




                    $sumBags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->where('batchTicketNumber', $actualValue->batchTicketNumber)
                        ->sum('totalBagCount');

                    if($actualValue->isBuffer =='1'){$status="Buffer Seeds";
                            $btn = "<a type='button' data-toggle='modal' data-target='#change_stocks_modal'
                         data-batchticketnumber = '".$batchTicketNumber."'
                         data-isbuffer = '".$actualValue->isBuffer."'
                         data-province = '".$province."'
                         data-municipality = '".$municipality."'
                         data-dop = '".$dropOffPoint."'
                         data-seeddata='".$seed_varieties."'  class='btn btn-success btn-xs'><i class='fa fa-exchange'> Change to For Distribution Seeds</i></a>";

                    }else{$status="For Distribution";

                        $btn = "<a type='button' data-toggle='modal' data-target='#change_stocks_modal'
                         data-batchticketnumber = '".$batchTicketNumber."'
                         data-isbuffer = '".$actualValue->isBuffer."'
                         data-province = '".$province."'
                         data-municipality = '".$municipality."'
                         data-dop = '".$dropOffPoint."'
                         data-seeddata='".$seed_varieties."'  class='btn btn-warning btn-xs'><i class='fa fa-exchange'> Change to Buffer Seeds</i></a>";
                    }
                        
                    $batch_array = array(
                    //"downloaded_by" => $username,
                    "transaction_code" =>$batchTicketNumber,
                    "province"=>$province,
                    "municipality"=>$municipality,
                    "dop_name" => $dropOffPoint,
                    "seed_varieties"=>$seed_varieties,
                    "total_bags_str"=> number_format($sumBags)." bag(s)",
                    "status_name" => $status,
                    "action" =>$btn,

                    );

                    array_push($return_arr, $batch_array);


                }

               // dd($return_arr);

        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
            ->make(true);
    }

         public function confirm_changeOfStockType(Request $request){
        DB::beginTransaction();
        try{

             //DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
              //  ->where('batchTicketNumber', '=', $request->transaction_code)
              //  ->update(['isBuffer' => $request->isbuffer]);

            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('batchTicketNumber', '=', $request->transaction_code)
                ->update(['isBuffer' => $request->isbuffer]);

              //DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_transaction')
               // ->where('batchTicketNumber', '=', $request->transaction_code)
               // ->update(['isBuffer' => $request->isbuffer]);

            //insert to logs
            DB::connection('mysql')->table('lib_logs')
                ->insert([
                    'category' => 'CHANGE_STOCKS_TYPE',
                    'description' => 'The transaction code: '.$request->transaction_code.' has been change by: '.Auth::user()->username.' | Is Buffer into '.$request->isbuffer,
                    'author' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);

            DB::commit();
            return "change_stock_type_success";

        } catch (\Exception $e) {
            DB::rollback(); 
            return $e;
        }
    }

	
	
	
	
	
	
    public function stocks_home(){
        // $user_prov = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->where('prv_code', Auth::user()->province)->groupBy('prv_code')->first();
        $station_prv = DB::table('lib_station')
            ->where('stationID', Auth::user()->stationId)
            ->get();
        $stprv = [];
        foreach($station_prv as $s){
            $stprv[] = $s->province;
        }
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')->groupBy('province')->get();
        }else{
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')->whereIn('province', $stprv)->groupBy('province')->get();
        }
        
        return view('app_monitoring.stocks.home')
            ->with('provinces', $provinces);
    }

    public function stocks_public_home(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')->groupBy('province')->get();
        return view('app_monitoring.stocks.home_public')
            ->with('provinces', $provinces);
    }

    public function get_stock_municipalities(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
            
        $return_str= '';
        foreach($municipalities as $row){
            $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
        }
        return $return_str;
    }

    public function stocks_tbl(Request $request){
        if($request->province == "no_province" && $request->municipality == "no_municipality" && $request->status == "no_status"){
            return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                ->select(DB::raw('SUM(number_of_bag) as total_bags'), 'region', 'province', 
                    'municipality', 'prv_dropoff_id', 'downloaded_by', 'transaction_code', 'is_cleared')
                ->where('app_version', 'v2.05')
                ->orwhere('app_version', 'v2.06')
                ->groupBy('transaction_code', 'downloaded_by')
            )
            ->addColumn('total_bags_str', function($row){
                return number_format($row->total_bags)." bag(s)";
            })
            ->addColumn('dop_name', function($row){
                return DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('prv_dropoff_id', $row->prv_dropoff_id)->first()->dropOffPoint; 
            })
            ->addColumn('status_name', function($row){
                if($row->is_cleared == "1"){
                    return "Stocks Released.";
                }else{
                    return "Stocks Unreleased.";
                }
            })
            ->addColumn('seed_varieties', function($row){
                $variety_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                    ->where('transaction_code', $row->transaction_code)
                    ->get();

                $variety_str = '';
                foreach($variety_list as $v_row){
                    $variety_str .= "<b>".$v_row->seed_variety."</b>: ".$v_row->number_of_bag." bag(s) <br>";
                }

                return $variety_str;
            })
            ->addColumn('release_btn', function($row){
                if($row->is_cleared == "0"){
                    return "<button class='btn btn-danger btn-xs' data-code='$row->transaction_code' data-user='$row->downloaded_by' data-toggle='modal' data-target='#release_stocks_modal'>Release Stocks</button>";
                }else{
                    return '<button class="btn btn-secondary btn-xs">Release Stocks</button>';
                }
            })
            ->make(true);
        }else{
            //dd($request->municipality);
            
            return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                ->select(DB::raw('SUM(number_of_bag) as total_bags'), 'region', 'province', 
                    'municipality', 'prv_dropoff_id', 'downloaded_by', 'transaction_code', 'is_cleared')
                //->where('app_version', 'v2.05')
                ->where('province', $request->province)
                ->where('municipality', $request->municipality)
                ->where('is_cleared', $request->status)
                //->orwhere('app_version', 'v2.06')
                //->where('province', $request->province)
                //->where('municipality', $request->municipality)
                //->where('is_cleared', $request->status)

                ->groupBy('transaction_code', 'downloaded_by')
            )
            ->addColumn('total_bags_str', function($row){
                return number_format($row->total_bags)." bag(s)";
            })
            ->addColumn('dop_name', function($row){
                    $dop_name = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('prv_dropoff_id', $row->prv_dropoff_id)->first();
                    if(count($dop_name)>0){
                        return $dop_name->dropOffPoint;
                    }else{
                        return $row->prv_dropoff_id;
                    } 


            })
            ->addColumn('status_name', function($row){
                if($row->is_cleared == "1"){
                    return "Stocks Released.";
                }else{
                    return "Stocks Unreleased.";
                }
            })
             ->addColumn('seed_varieties', function($row){
                $variety_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                    ->where('transaction_code', $row->transaction_code)
                    ->get();

                $variety_str = '';
                foreach($variety_list as $v_row){
                    $variety_str .= "<b>".$v_row->seed_variety."</b>: ".$v_row->number_of_bag." bag(s) <br>";
                }

                return $variety_str;
            })
            ->addColumn('release_btn', function($row){
                if($row->is_cleared == "0"){
                    return "<button class='btn btn-danger btn-xs' data-code='$row->transaction_code' data-user='$row->downloaded_by' data-toggle='modal' data-target='#release_stocks_modal'>Release Stocks</button>";
                }else{
                    return '<button class="btn btn-secondary btn-xs">Release Stocks</button>';
                }
            })
            ->make(true);
        }
    }

    public function stocks_tbl_public(Request $request){
        if($request->province == "no_province" && $request->municipality == "no_municipality" && $request->status == "no_status"){
            return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                ->select(DB::raw('SUM(number_of_bag) as total_bags'), 'region', 'province', 
                    'municipality', 'prv_dropoff_id', 'downloaded_by', 'transaction_code', 'is_cleared')
                ->where('app_version', 'v2.05')
                ->orwhere('app_version', 'v2.06')
                ->groupBy('transaction_code', 'downloaded_by')
            )
            ->addColumn('total_bags_str', function($row){
                return number_format($row->total_bags)." bag(s)";
            })
            ->addColumn('dop_name', function($row){
                return DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('prv_dropoff_id', $row->prv_dropoff_id)->first()->dropOffPoint; 
            })
            ->addColumn('status_name', function($row){
                if($row->is_cleared == "1"){
                    return "Stocks Released.";
                }else{
                    return "Stocks Unreleased.";
                }
            })
            ->addColumn('seed_varieties', function($row){
                $variety_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                    ->where('transaction_code', $row->transaction_code)
                    ->get();

                $variety_str = '';
                foreach($variety_list as $v_row){
                    $variety_str .= "<b>".$v_row->seed_variety."</b>: ".$v_row->number_of_bag." bag(s) <br>";
                }

                return $variety_str;
            })
            ->make(true);
        }else{
            return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                ->select(DB::raw('SUM(number_of_bag) as total_bags'), 'region', 'province', 
                    'municipality', 'prv_dropoff_id', 'downloaded_by', 'transaction_code', 'is_cleared')
                //->where('app_version', 'v2.05')
                ->where('province', $request->province)
                ->where('municipality', $request->municipality)
                ->where('is_cleared', $request->status)
                //->orwhere('app_version', 'v2.06')
                //->where('province', $request->province)
                //->where('municipality', $request->municipality)
                //->where('is_cleared', $request->status)
                ->groupBy('transaction_code', 'downloaded_by')
            )
            ->addColumn('total_bags_str', function($row){
                return number_format($row->total_bags)." bag(s)";
            })
            ->addColumn('dop_name', function($row){
                return DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('prv_dropoff_id', $row->prv_dropoff_id)->first()->dropOffPoint; 
            })
            ->addColumn('status_name', function($row){
                if($row->is_cleared == "1"){
                    return "Stocks Released.";
                }else{
                    return "Stocks Unreleased.";
                }
            })
            ->addColumn('seed_varieties', function($row){
                $variety_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                    ->where('transaction_code', $row->transaction_code)
                    ->get();

                $variety_str = '';
                foreach($variety_list as $v_row){
                    $variety_str .= "<b>".$v_row->seed_variety."</b>: ".$v_row->number_of_bag." bag(s) <br>";
                }

                return $variety_str;
            })
            ->make(true);
        }
    }


    public function confirm_releaseOfStocks(Request $request){
        DB::beginTransaction();
        try{
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                ->where('transaction_code', '=', $request->transaction_code)
                ->where('downloaded_by', '=', $request->downloaded_by)
                ->update(['is_cleared' => 1]);

            //insert to logs
            DB::connection('mysql')->table('lib_logs')
                ->insert([
                    'category' => 'STOCKS_RELEASE',
                    'description' => 'The transaction code: '.$request->transaction_code.' has been released by: '.Auth::user()->username.' | downloaded by: '.$request->downloaded_by,
                    'author' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);

            DB::commit();
            return "release_success";

        } catch (\Exception $e) {
            DB::rollback(); 
            return $e;
        }
    }
}

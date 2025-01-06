<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Session;
use DB;
use Yajra\Datatables\Facades\Datatables;

class DistributionDashboardController extends Controller
{
    public function index(){
        $dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province', 'municipality')->get();
        return view('dashboard.distribution.index');
    }

  public function pageClose(){
        $mss = "Temporary Closed";
         return view('utility.pageClosed',compact("mss"));
    }





    public function distribution_main_tbl(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province', 'municipality'))
        ->addColumn('confirmed_col', function($row){
            $total_confirmed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->where('is_cancelled', 0)
                ->sum('totalBagCount');

            return number_format($total_confirmed);
        })
        ->addColumn('inspected_col', function($row){
            $total_confirmed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->where('batchTicketNumber', '!=', 'TRANSFER')
                ->sum('totalBagCount');

            return number_format($total_confirmed);
        })
        ->addColumn('transfer_col', function($row){
            $total_transfer = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->where('batchTicketNumber', '=', 'TRANSFER')
                ->sum('totalBagCount');

            return number_format($total_transfer);
        })
        ->addColumn('distributed_col', function($row){
            $database = $GLOBALS['season_prefix']."prv_".substr($row->prv,0,4);
            $total_distributed = DB::table($database.".released")
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->where('bags', '!=', 0)
                ->sum('bags');

            return number_format($total_distributed);
        })
        ->make(true);
    }
}

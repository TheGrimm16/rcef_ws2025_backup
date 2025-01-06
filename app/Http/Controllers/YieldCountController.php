<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;

class YieldCountController extends Controller
{

	public function index(){

        $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where('region', '!=', '')
            ->groupBy('regionName')
            ->orderBy('region_sort')
            ->get();

            return view("yieldCount.home")
            ->with("regions", $regions);
            
        }


        public function regionalCount(Request $request){
            return $request;
            $top_5_varieties = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_variety_report')
                ->select('seed_variety', DB::raw('SUM(total_volume) as volume'))
                ->groupBy('seed_variety')
                ->orderBy('volume', 'DESC')
                ->limit(5)
                ->get();
    
            $return_arr = array();
            foreach($top_5_varieties as $row){
                $row_arr = array(
                    "name" => $row->seed_variety,
                    "y" => intval($row->volume),
                    "sliced" => true
                );
                array_push($return_arr, $row_arr);
            }
    
            return $return_arr;
        }

}
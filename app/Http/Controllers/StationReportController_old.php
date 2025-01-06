<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Yajra\Datatables\Facades\Datatables;

class StationReportController extends Controller
{
    public function home(){
        $covered_regions = DB::connection('mysql')->table('lib_stations_aoc')->where('station_id', Auth::user()->stationId)->get();
        return view('reports.station.home')
            ->with('covered_regions', $covered_regions);
    }

    public function load_regional_report_values(Request $request){
        $total_provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('region', $request->region)
            ->groupBy('province')
            ->get();

        $total_municipalities = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('region', $request->region)
            ->groupBy('province', 'municipality')
            ->get();

        /*$total_bags = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_accepted'))
            ->where('region', $request->region)
            ->value('total_accepted');

        $total_confirmed = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_confirmed'))
            ->where('region', $request->region)
            ->where('is_cancelled', 0)
            ->value('total_confirmed');*/

        $confirmed_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
            ->where('region', $request->region)
			->where('is_cancelled', 0)
            ->groupBy('batchTicketNumber')
            ->get();

        $total_bags = 0;
        $total_confirmed = 0;
        foreach($confirmed_deliveries as $row){
            $check_actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->first();

            if(count($check_actual_delivery) > 0){
                $total_bags += $check_actual_delivery->total_bags;
            }

            $total_confirmed += $row->total_bags; 
        }

        return array(
            'total_provinces' => number_format(count($total_provinces)),
            'total_municipalities' => number_format(count($total_municipalities)),
            'total_bags' => number_format($total_bags),
            'total_confirmed' => number_format($total_confirmed)
        );
    }

    public function load_seed_coopList(Request $request){
        $region = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->where('regionName', $request->region)
            ->first();

        $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('regionId', intval($region->regCode))
            ->get();

        $coop_list = array();
        $row_count = 1;
        foreach($cooperatives as $row){
            $row_arr = array(
                'coop_name' => $row->coopName,
                'row_count' => $row_count,
                'coop_accreditation' => $row->accreditation_no
            );
            array_push($coop_list, $row_arr);

            $row_count++;
        }

        $coop_list = collect($coop_list);
        return Datatables::of($coop_list)
        ->addColumn('coop_link', function($row){
            return '<a href="#" data-toggle="modal" data-target="#seed_coop_details" class="coop-link" data-accreditation_no="'.$row['coop_accreditation'].'">'.$row['coop_name'].'</a>';
        })
        ->make(true);
    }

    public function load_coop_details(Request $request){
        $coop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('accreditation_no', $request->accreditation_no)
            ->first();

        return array(
            "coop_name" => $coop->coopName,
            "coop_accreditation" => $coop->accreditation_no
        );
    }

    public function load_region_varieties(Request $request){
        $varieties = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('region', $request->region)
            ->groupBy('seedVariety')
            ->get();

        $variety_list = array();
        $row_count = 1;

        foreach($varieties as $row){
            $row_arr = array(
                "seed_variety" => $row->seedVariety,
                "row_count" => $row_count
            );
            array_push($variety_list, $row_arr);

            $row_count++;
        }

        $variety_list = collect($variety_list);
        return Datatables::of($variety_list)->make(true);
    }

    public function load_region_seeed_chartData(Request $request){
        $varieties = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
            ->where('region', $request->region)
            ->groupBy('seedVariety')
            ->get();

        $variety_list = array();
        foreach($varieties as $row){
            $row_arr = array(
                "name" => $row->seedVariety,
                "y" => intval($row->total_bags),
                "sliced" => true
            );
            array_push($variety_list, $row_arr);
            //$variety_list .= "{name: '".$row->seedVariety."', y: ".$row->total_bags.", sliced:true},";
        }

        return $variety_list;
    }
}

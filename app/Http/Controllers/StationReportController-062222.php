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
        //$covered_regions = DB::connection('mysql')->table('lib_stations_aoc')->where('station_id', Auth::user()->stationId)->get();
        $station_list = DB::table('geotag_db2.tbl_station')->get();
        return view('reports.station.home')
            ->with('station_list', $station_list);
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

    /**
     * NEW FUNCTIONS - REVAMPED DASHBOARD
     */

    public function load_station_areas(Request $request){
        /*$station_areas = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_station_report')->where('station_id', $request->station)->get();
        $return_str = '';
        foreach($station_areas as $row){
            if($row->province_code == ''){
                $return_str .= "<option value='$row->id'>$row->region_name</option>";
            }else{
                $return_str .= "<option value='$row->id'>$row->region_name / $row->province_name</option>";
            }                
        }

        return $return_str;*/

        $return_str = '';
        $station_areas_regions = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('station_id', $request->station)->groupBy('region')->get();
        foreach($station_areas_regions as $region_row){
            $return_str .= "<option value='REGION_$region_row->id'>$region_row->region</option>"; 
        }

        $station_areas_provinces = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('station_id', $request->station)->groupBy('province')->get();
        foreach($station_areas_provinces as $row){
            $return_str .= "<option value='REGIONPROV_$row->id'>$row->region / $row->province</option>";            
        }

        return $return_str;
    }

    public function load_regional_report_values(Request $request){
        $id_str = explode("_", $request->area_id);
        $category = $id_str[0];
        $id = $id_str[1];

        if($category == "REGION"){
            $area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('id', $id)->first();
            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->where('region', $area->region)
                ->groupBy('province')
                ->get();

            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->where('region', $area->region)
                ->groupBy('province', 'municipality')
                ->get();

            $confirmed_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $area->region)
                ->where('is_cancelled', 0)
                ->groupBy('batchTicketNumber')
                ->get();

            $transferred_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $area->region)
                ->where('batchTicketNumber', "TRANSFER")
                ->groupBy('batchTicketNumber')
                ->value('total_bags');

            $total_bags = 0;
            $total_confirmed = 0;
            foreach($confirmed_deliveries as $row){
                $check_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('batchTicketNumber', $row->batchTicketNumber)
                    ->first();

                if(count($check_actual_delivery) > 0){
                    $total_bags += $check_actual_delivery->total_bags;
                }

                $total_confirmed += $row->total_bags; 
            }

            //get total beneficiaries
            $farmer_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')->where('region', $area->region)->value('total_farmers');
        
        }else{
            $area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('id', $id)->first();
            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->groupBy('province')
                ->get();

            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->groupBy('province', 'municipality')
                ->get();

            $confirmed_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->where('is_cancelled', 0)
                ->groupBy('batchTicketNumber')
                ->get();

            $transferred_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->where('batchTicketNumber', "TRANSFER")
                ->groupBy('batchTicketNumber')
                ->value('total_bags');

            $total_bags = 0;
            $total_confirmed = 0;
            foreach($confirmed_deliveries as $row){
                $check_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('batchTicketNumber', $row->batchTicketNumber)
                    ->first();

                if(count($check_actual_delivery) > 0){
                    $total_bags += $check_actual_delivery->total_bags;
                }

                $total_confirmed += $row->total_bags; 
            }

            //get total beneficiaries
            $farmer_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->value('total_farmers');

        }

        return array(
            'total_provinces' => number_format(count($total_provinces)),
            'total_municipalities' => number_format(count($total_municipalities)),
            'total_bags' => number_format($total_bags + $transferred_bags),
            'total_confirmed' => number_format($total_confirmed),
            'farmer_beneficiaries' => number_format($farmer_beneficiaries)
        );
    } 

    public function load_regional_report_values2(Request $request){
        //check if area has tagged province
        $area = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report')->where('id', $request->area_id)->first();

        if($area->province_name == ''){
            $total_provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('region', $area->region_name)
                ->groupBy('province')
                ->get();

            $total_municipalities = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('region', $area->region_name)
                ->groupBy('province', 'municipality')
                ->get();

            $confirmed_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $area->region_name)
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

            //get total beneficiaries
            $farmer_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')->where('region', $area->region_name)->value('total_farmers');
        
        }else{
            $total_provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('region', $area->region_name)
                ->where('province', $area->province_name)
                ->groupBy('province')
                ->get();

            $total_municipalities = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->where('region', $area->region_name)
                ->where('province', $area->province_name)
                ->groupBy('province', 'municipality')
                ->get();

            $confirmed_deliveries = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $area->region_name)
                ->where('province', $area->province_name)
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

            //get total beneficiaries
            $farmer_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                ->where('region', $area->region_name)
                ->where('province', $area->province_name)
                ->value('total_farmers');
        }

        return array(
            'total_provinces' => number_format(count($total_provinces)),
            'total_municipalities' => number_format(count($total_municipalities)),
            'total_bags' => number_format($total_bags),
            'total_confirmed' => number_format($total_confirmed),
            'farmer_beneficiaries' => number_format($farmer_beneficiaries)
        );
    }

    public function load_seed_coopList(Request $request){
        $id_str = explode("_", $request->area_id);
        $category = $id_str[0];
        $id = $id_str[1];

        $area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('id', $id)->first();
        if($category == "REGION"){
            $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')
                ->select('rcep_seed_cooperatives.tbl_cooperatives.coopName', $GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives.accreditation_no')
                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives', function ($table_join) {
                    $table_join->on($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives.accreditation_no', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery.coopAccreditation');
                })
                ->where('rcep_delivery_inspection_mirror.tbl_delivery.region', $area->region)
                ->groupBy('rcep_seed_cooperatives.tbl_cooperatives.accreditation_no')
                ->get();
        }else{
            $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')
                ->select('rcep_seed_cooperatives.tbl_cooperatives.coopName', $GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives.accreditation_no')
                ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives', function ($table_join) {
                    $table_join->on($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives.accreditation_no', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery.coopAccreditation');
                })
                ->where('rcep_delivery_inspection_mirror.tbl_delivery.region', $area->region)
                ->where('rcep_delivery_inspection_mirror.tbl_delivery.province', $area->province)
                ->groupBy('rcep_seed_cooperatives.tbl_cooperatives.accreditation_no')
                ->get();
        }
       
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


    public function load_region_varieties(Request $request){
        $id_str = explode("_", $request->area_id);
        $category = $id_str[0];
        $id = $id_str[1];

        $area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('id', $id)->first();
        if($category == "REGION"){
            $varieties = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
                ->where('region', $area->region)
                ->groupBy('seedVariety')
                ->get();
        }else{
            $varieties = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->groupBy('seedVariety')
                ->get();
        }

        $variety_list = array();
        $row_count = 1;

        foreach($varieties as $row){
            $row_arr = array(
                "seed_variety" => $row->seedVariety,
                "row_count" => $row_count,
                "seed_volume" => number_format($row->total_bags)
            );
            array_push($variety_list, $row_arr);

            $row_count++;
        }

        $variety_list = collect($variety_list);
        return Datatables::of($variety_list)->make(true);
    }


    public function compute_transferred($region, $province){
        $bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
            ->where('region', $region)
            ->where('province', $province)
            ->where('batchTicketNumber', "TRANSFER")
            ->groupBy('batchTicketNumber')
            ->value('total_bags');

        return $bags;
    }

    public function load_region_seeed_chartData(Request $request){
        $id_str = explode("_", $request->area_id);
        $category = $id_str[0];
        $id = $id_str[1];

        $area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('id', $id)->first();
        if($category == "REGION"){
            $varieties = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
                ->where('region', $area->region)
                ->groupBy('seedVariety')
                ->get();
        }else{
            $varieties = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
                ->where('region', $area->region)
                ->where('province', $area->province)
                ->groupBy('seedVariety')
                ->get();
        }

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

    function compute_inspected_station_report($region_name, $province_name){
        $total_inspected = 0;

        $confirmed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')
            ->select('batchTicketNumber')
            ->where('region', $region_name)
            ->where('province', $province_name)
            ->where('is_cancelled', 0)
            ->where('region', '!=', '')
            ->groupBy('batchTicketNumber')
            ->get();

        foreach($confirmed as $confirmed_row){
            $actual = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'batchTicketNumber')
                ->where('region', $region_name)
                ->where('province', $province_name)
                ->where('batchTicketNumber', $confirmed_row->batchTicketNumber)
                //->where('batchTicketNumber',"!=","TRANSFER")
                ->groupBy('batchTicketNumber')
                ->value('total_bags');

            $total_inspected += $actual;
        }

        return $total_inspected;
    }
    
    function compute_distributed($region_name, $province_name){
        $distributed = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
            ->where('region', $region_name)
            ->where('province', $province_name)
            ->value('total_bags');

        return $distributed;
    }

    function compute_farmers($region_name, $province_name){
        $farmers = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
            ->where('region', $region_name)
            ->where('province', $province_name)
            ->value('total_farmers');

        return $farmers;
    }

    function compute_target($station_id){
        $target = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')
            ->where('station_id', $station_id)
            ->sum('total_value');

        return $target;
    }

    function compute_station_data(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report_data')->truncate();
        $station_list = DB::table('geotag_db2.tbl_station')->get();

        $station_arr_list = array();
        $station_confirmed_arr = array();
        $station_inspected_arr = array();
        $station_farmers_arr = array();
        $station_transferred_seeds = array();

        foreach($station_list as $row){
            $total_station_confirmed = 0;
            $total_station_inspected = 0;
            $total_station_distributed = 0;
            $total_transffered_bags = 0;

            $area_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('station_id', $row->stationId)->groupBy('province')->get();
            foreach($area_list as $area_row){
                $confirmed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
                    ->where('region', $area_row->region)
                    ->where('province', $area_row->province)
                    ->where('is_cancelled', 0)
                    ->where('region', '!=', '')
                    ->first();

                $inspected = $this->compute_inspected_station_report($area_row->region, $area_row->province);
                $distributed = $this->compute_distributed($area_row->region, $area_row->province);
                $transferred = $this->compute_transferred($area_row->region, $area_row->province);

                $total_station_confirmed += $confirmed->total_bags;
                $total_station_inspected += $inspected;
                $total_station_distributed += $distributed;
                $total_transffered_bags += $transferred;
            }

            if($total_station_confirmed > 0){
                $row_arr = array(
                    'station_id' => $row->stationId,
                    'station_name' => $row->stationName == 'Central Experiment Station' ? 'CES' : $row->stationName,
                    'confirmed_bags' => $total_station_confirmed,
                    'inspected_bags' => $total_station_inspected,
                    'transferred_bags' => $total_transffered_bags
                );
                DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report_data')->insert($row_arr);
            }
        }
    }

    function compute_station_data2(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report_data')->truncate();
        $station_list = DB::table('geotag_db2.tbl_station')->get();

        $station_arr_list = array();
        $station_confirmed_arr = array();
        $station_inspected_arr = array();
        $station_farmers_arr = array();

        foreach($station_list as $row){

            $total_station_confirmed = 0;
            $total_station_inspected = 0;
            $total_station_distributed = 0;

            /*$station_area_list = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_station_report')
                ->where('station_id', $row->stationId)
                ->where('province_name', '!=', '')
                ->where('province_code', '!=', '')
                ->get();*/

            $station_area_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('station_id', $row->stationId)->groupBy('province')->get();

            foreach($station_area_list as $area_row){
                $confirmed = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'seedVariety')
                    ->where('region', $area_row->region)
                    ->where('province', $area_row->province)
                    ->where('is_cancelled', 0)
                    ->where('region', '!=', '')
                    ->first();

                $inspected = $this->compute_inspected_station_report($area_row->region, $area_row->province);
                $distributed = $this->compute_distributed($area_row->region, $area_row->province);

                $total_station_confirmed += $confirmed->total_bags;
                $total_station_inspected += $inspected;
                $total_station_distributed += $distributed;
            }

            if($total_station_confirmed > 0){

                /*if($row->stationName == 'Central Experiment Station'){
                    array_push($station_arr_list, 'CES');
                }else{
                    array_push($station_arr_list, $row->stationName);
                }
               
                array_push($station_confirmed_arr, $total_station_confirmed);
                array_push($station_inspected_arr, $total_station_inspected);
                array_push($station_distributed_arr, $total_station_distributed);*/

                $row_arr = array(
                    'station_id' => $row->stationId,
                    'station_name' => $row->stationName == 'Central Experiment Station' ? 'CES' : $row->stationName,
                    'confirmed_bags' => $total_station_confirmed,
                    'inspected_bags' => $total_station_inspected
                );
                DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report_data')->insert($row_arr);
            }
        }

    }

    function load_station_data(Request $request){
        $station_name_arr = array();
        $confirmed_bags_arr = array();
        $inspected_bags_arr = array();
        $distributed_bags_arr = array();
        $target_arr = array();
        $farmers_arr = array();
        $transfer_arr = array();

        $station_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report_data')->get();
        foreach($station_data as $row){

            $total_station_distributed = 0;
            $total_station_farmers = 0;
            $total_target = 0;

            $station_area_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('station_id', $row->station_id)->groupBy('province')->get();

            foreach($station_area_list as $area_row){
                $distributed = $this->compute_distributed($area_row->region, $area_row->province);
                $farmers = $this->compute_farmers($area_row->region, $area_row->province);
                
                $total_station_distributed += $distributed;
                $total_station_farmers += $farmers;
            }

            $total_target  += $this->compute_target($row->station_id);

            array_push($station_name_arr, $row->station_name);
            array_push($confirmed_bags_arr, $row->confirmed_bags);
            array_push($inspected_bags_arr, $row->inspected_bags);
            array_push($distributed_bags_arr, $total_station_distributed);
            array_push($farmers_arr, $total_station_farmers);
            array_push($target_arr, $total_target);
            array_push($transfer_arr, $row->transferred_bags);
        }

        return array(
            'station_list' => $station_name_arr,
            'confirmed_list' => $confirmed_bags_arr,
            'inspected_list' => $inspected_bags_arr,
            'distributed_list' => $distributed_bags_arr,
            'farmer_list' => $farmers_arr,
            'target_list' => $target_arr,
            'transferred_list' => $transfer_arr
        );
    }

    function load_station_progress(Request $request){
        $regions_covered = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('station_id', $request->station)->groupBy('region')->get();

        $total_provinces = 0;
        $total_municipalities = 0;
        $total_inspected_bags = 0;
        $total_area = 0;
        $total_beneficiaries = 0;

        $target_provinces = 0;
        $target_bags = 0;

        $reported_provinces = 0;
        $reported_municipalities = 0;
        $reported_bags = 0;
        $reported_beneficiaries = 0;
		$total_transferred_bags = 0;
		$total_distributed = 0;

        foreach($regions_covered as $row){
            $lib_region_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->where('region', $row->region)
                ->first();

            //$total_provinces += $lib_region_data->total_provinces;
            //$total_municipalities += $lib_region_data->total_municipalities;

            //$total_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->where('region',$row->region)->groupBy('province')->get());
            //$total_municipalities += count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->where('region',$row->region)->groupBy('province','municipality')->get());

			$total_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
				->where('region', '!=', '')
				->where('province', '!=', '')
				->where('region',$row->region)
				->where('batchTicketNumber', '!=', 'TRANSFER')
				->groupBy('province')->get());
			$total_municipalities += count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
				->where('region', '!=', '')
				->where('province', '!=', '')
				->where('municipality', '!=', '')
				->where('region',$row->region)
				->where('batchTicketNumber', '!=', 'TRANSFER')
				->groupBy('province','municipality')->get());
			
            $total_beneficiaries += $lib_region_data->total_farmers;
            $total_area += $lib_region_data->total_dist_area;
			$total_distributed += $lib_region_data->total_bags;
			
			$total_transferred_bags += \DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber', "TRANSFER")
					->where('region', $row->region)
                    ->value('total_bag_count');
					
			$total_inspected_bags += \DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber',"!=","TRANSFER")
					->where('region', $row->region)
                    ->value('total_bag_count');

            //target data
            $target = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('region', $row->region)->get();
            foreach($target as $t_row){
                $target_bags += $t_row->total_value;
            }

            $target_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('region', $row->region)->groupBy('province')->get());
        
            //reported data
            $reported_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_reports.weekly_report_municipal')->where('region', $row->region)->groupBy('province')->get());
            $reported_municipalities += count(DB::table($GLOBALS['season_prefix'].'rcep_reports.weekly_report_municipal')->where('region', $row->region)->groupBy('municipality')->get());
            $reported_bags += DB::table($GLOBALS['season_prefix'].'rcep_reports.weekly_report_municipal')->where('region', $row->region)->sum('num_bag_confirmed_inspected');
        }

        $station_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_report_data')
                ->where('station_id', $request->station)
                ->first();
        //$total_inspected_bags = $station_data->inspected_bags + $station_data->transferred_bags;
		
		$total_report_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_farmer_data')->where('station_id', $request->station)->sum('total_farmers_reported');
		$reported_distributed = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_farmer_data')->where('station_id', $request->station)->sum('total_distributed_data');
		
        return array(
            'sms_provinces' => $total_provinces,
            'sms_municipalities' => $total_municipalities,
            'sms_inspected' => number_format($total_inspected_bags),
            'sms_area' => number_format($total_area, "2", ".", ","),
            'sms_beneficiaries' => number_format($total_beneficiaries),
			'sms_transferred' => number_format($total_transferred_bags),
			'sms_distributed' => number_format($total_distributed),

            'target_provinces' => $target_provinces,
            'target_bags' => number_format($target_bags),
            
            'reported_provinces' => $reported_provinces,
            'reported_municipalities' => $reported_municipalities,
            'reported_bags' => number_format($reported_bags),
            'report_beneficiaries' => number_format($total_report_beneficiaries),
			'report_transferred' => 0,
			'report_distributed' => number_format($reported_distributed)
        );
    }

    function load_station_progress_all(Request $request){
        $regions_covered = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->groupBy('region')->get();

        $total_provinces = 0;
        $total_municipalities = 0;
        $total_inspected_bags = 0;
        $total_area = 0;
        $total_beneficiaries = 0;

        $target_provinces = 0;
        $target_bags = 0;

        $reported_provinces = 0;
        $reported_municipalities = 0;
        $reported_bags = 0;
        $reported_beneficiaries = 0;

        foreach($regions_covered as $row){

            //rcep sms data
            $lib_region_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->where('region', $row->region)
                ->first();
			if(count($lib_region_data) > 0){
				//$total_provinces += $lib_region_data->total_provinces;
				//$total_municipalities += $lib_region_data->total_municipalities;

				/*$total_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
					->where('region',$row->region)
					->where('region', '!=', '')
					->groupBy('province')->get());*/
				//$total_municipalities += count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->where('region',$row->region)->groupBy('province','municipality')->get());
				
				$total_beneficiaries += $lib_region_data->total_farmers;
				$total_area += $lib_region_data->total_actual_area;

				//target data
				$target = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('region', $row->region)->get();
				foreach($target as $t_row){
					$target_bags += $t_row->total_value;
				}

				$target_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitmet_per_province')->where('region', $row->region)->groupBy('province')->get());

				//reported data
				//$reported_provinces += count(DB::table($GLOBALS['season_prefix'].'rcep_reports.weekly_report_municipal')->where('region', $row->region)->groupBy('province')->get());
				//$reported_municipalities += count(DB::table($GLOBALS['season_prefix'].'rcep_reports.weekly_report_municipal')->where('region', $row->region)->groupBy('municipality')->get());
				//$reported_bags += DB::table($GLOBALS['season_prefix'].'rcep_reports.weekly_report_municipal')->where('region', $row->region)->sum('num_bag_confirmed_inspected');
				
				//get latest date of weekly reports
				$latest_date = DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')->orderBy('date_generated')->first()->date_generated;
				$reported_provinces += DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')->select(DB::raw('COUNT(province) as total_provinces'))
					->where('region', $row->region)->where('total_farmers', '!=', 0)
					->where('date_generated', $latest_date)
					->groupBy('province')
					->value('total_provinces');
				$reported_municipalities += DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')->select(DB::raw('COUNT(municipality) as total_municipalities'))
					->where('region', $row->region)->where('total_farmers', '!=', 0)
					->groupBy('date_generated')
					->value('total_municipalities');
				$reported_bags += DB::table($GLOBALS['season_prefix'].'rcep_google_sheets.lib_weekly_municipal')->where('region', $row->region)->groupBy('date_generated')->sum('total_bags');
			}
        }


        $total_inspected_bags = \DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber',"!=","TRANSFER")
                    ->value('total_bag_count');
		
		$total_transferred_bags = \DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber', "TRANSFER")
                    ->value('total_bag_count');
					
		$total_distributed = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->value('total_bags');
		$reported_distributed = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_farmer_data')->sum('total_distributed_data');
		
		$total_provinces = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
				->where('region', '!=', '')
				->where('province', '!=', '')
				->where('batchTicketNumber', '!=', 'TRANSFER')
				->groupBy('province')->get());
		$total_municipalities = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
			->where('region', '!=', '')
			->where('province', '!=', '')
			->where('municipality', '!=', '')
			->where('batchTicketNumber', '!=', 'TRANSFER')
			->groupBy('province','municipality')->get());
					
		$total_report_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_station_farmer_data')->sum('total_farmers_reported');
        //$total_inspected_bags = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_station_report_data')->sum('inspected_bags');

        return array(
            'sms_provinces' => $total_provinces,
            'sms_municipalities' => number_format($total_municipalities),
            'sms_inspected' => number_format($total_inspected_bags),
            'sms_area' => number_format($total_area, "2", ".", ","),
            'sms_beneficiaries' => number_format($total_beneficiaries),
			'sms_transferred' => number_format($total_transferred_bags),
			'sms_distributed' => number_format($total_distributed),

            'target_provinces' => $target_provinces,
            'target_bags' => number_format($target_bags),
            
            'reported_provinces' => $reported_provinces,
            'reported_municipalities' => $reported_municipalities,
            'reported_bags' => number_format($reported_bags),
            'report_beneficiaries' => number_format($total_report_beneficiaries),
			'report_transferred' => 0,
			'report_distributed' => number_format($reported_distributed) 
        );
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;

use App\SeedCooperatives;
use App\SeedGrowers;

use Config;
use DB;
use Excel;

class ReportController extends Controller
{
    public function Home(){
        $d_provinces = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        return view('reports.home')->with('d_provinces', $d_provinces);
    }

    public function municipalityDropOff(Request $request){
        $municipalities = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->where('province', '=', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
        $return_str= '';
        foreach($municipalities as $municipality){
            $return_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $return_str;
    }

    public function dropOffName(Request $request){
        $dropoffs = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->orderBy('dropOffPoint', 'asc')
            ->groupBy('prv_dropoff_id')
            ->get();
        $return_str= '';
        foreach($dropoffs as $dropoff){
            $return_str .= "<option value='$dropoff->prv_dropoff_id'>$dropoff->dropOffPoint</option>";
        }
        return $return_str;
    }

    public function Home_Provincial(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('reports.home2')->with('regions', $regions);
    }

    public function Home_Regional(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('reports.home3')->with('regions', $regions);
    }

    public function SeedBeneficiariesVarieties(Request $request){
        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }

        if($table_conn == "established_connection"){
            //generate data table
            return Datatables::of(DB::connection('reports_db')->table("released")
                ->select('seed_variety', DB::raw('COUNT(released.seed_variety) as total_varieties'))
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->groupBy('released.seed_variety')
                ->orderBy('released.seed_variety')
            )->make(true);
        }
    }

    public function SeedBeneficiaries(Request $request){

        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }

        if($table_conn == "established_connection"){

            $male_count = 0;
            $female_count = 0;
            $report_res = DB::connection('reports_db')->table("released")
                ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                        DB::raw('SUM(released.bags) as total_bags'),
                        DB::raw('SUM(farmer_profile.area) as dist_area'),
                        DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                ->join('farmer_profile', function ($table_join) {
                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                    ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                })
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->first();

            if(count($report_res) > 0){
                $sex_res = DB::connection('reports_db')->table("released")
                    ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                    ->join('farmer_profile', function ($table_join) {
                        $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                        ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                    })
                    ->where('released.province', '=', $request->province)
                    ->where('released.municipality', '=', $request->municipality)
                    ->where('released.prv_dropoff_id', '=', $request->dropoff)
                    ->groupBy('sex')
                    ->orderBy('sex', 'DESC')
                    ->get();

                foreach($sex_res as $s_row){
                    if($s_row->sex == 'Male'){
                        $male_count = $s_row->sex_count;
                    }elseif($s_row->sex == 'Femal'){
                        $female_count = $s_row->sex_count;
                    }
                }
            }
            
            $data_arr = array(
                'table_name' => $table,
                'table_conn' => $table_conn,
                'total_bags' => number_format($report_res->total_bags),
                'dist_area' => number_format($report_res->dist_area, '2'),
                'actual_area' => number_format($report_res->actual_area, '2'),
                'total_farmers' => number_format($report_res->total_farmers),
                'total_male' => number_format($male_count),
                'total_female' => number_format($female_count)
            );

            return $data_arr;
        }else{
            $data_arr = array(
                'table_name' => $table,
                'table_conn' => $table_conn,
                'total_bags' => '',
                'total_area' => '',
                'total_farmers' => ''
            );

            return $data_arr;
        }

    }

    public function SeedBeneficiariesProvincial2(Request $request){
        $table = $GLOBALS['season_prefix'].'prv_'.$request->region.substr($request->province, -2);

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("released")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }

        $region_name = DB::connection('mysql')->table('lib_regions')->where('regCode', '=', $request->region)->first()->regDesc;
        $province_name = DB::connection('mysql')->table('lib_provinces')->where('provCode', '=', $request->province)->first()->provDesc;

        if($table_conn == "established_connection"){
            //get all municipalities
            $municipality_list = DB::connection('reports_db')->table("area_history")
                ->select('area_history.municipality')
                ->where('area_history.region', '=', $region_name)
                ->where('area_history.province', '=', $province_name)
                ->groupBy('area_history.province', 'area_history.municipality')
                ->get();

            //new row for each municipality
            $return_str = "";
            foreach($municipality_list as $mun){

                $total_farmers = DB::connection('reports_db')->table("released")
                    ->select(DB::raw('COUNT(farmer_id) as total_farmers'))
                    ->join('farmer_profile', 'released.farmer_id', '=', 'farmer_profile.farmerID')
                    ->where('released.province', '=', $province_name)
                    ->where('released.municipality', '=', $mun->municipality)
                    ->first()->total_farmers;

                $bags = DB::connection('reports_db')->table("released")
                    ->select(DB::raw('SUM(bags) as total_bags'))
                    ->where('province', '=', $province_name)
                    ->where('municipality', '=', $mun->municipality)
                    ->first()->total_bags;

                if(count($bags) > 0){
                    $total_bags = $bags;
                }else{
                    $total_bags = "No bags distributed.";
                }


                $area_list = DB::connection('reports_db')->table("released")
                    ->select('area_history.area')
                    ->join('area_history', 'released.farmer_id', '=', 'area_history.farmerId')
                    ->where('released.province', '=', $province_name)
                    ->where('released.municipality', '=', $mun->municipality)
                    ->get();


                $rcep_allocation = DB::connection('allocations_db')->table('allocated_volume')
                    ->where('region', '=', $region_name)
                    ->where('province', '=', $province_name)
                    ->where('municipality', '=', $mun->municipality)
                    ->first();

                if(count($rcep_allocation) > 0){
                    $total_allocations = number_format($rcep_allocation->allocation);
                }else{
                    $total_allocations = 0;
                    $total_bags = "no area allocation";
                }

                $total_area = 0;
                foreach($area_list as $area){
                    $total_area += (float) $area->area;
                }

                $return_str .= "<tr>";
                $return_str .= "<td>$mun->municipality</td>";
                $return_str .= "<td>$total_allocations</td>";
                $return_str .= "<td>$total_farmers</td>";
                $return_str .= "<td>$total_area</td>";
                $return_str .= "<td>$total_bags</td>";
                $return_str .= "</tr>";
            }

            return $return_str;
        }else{
            //get all municipalities
            $municipality_list = DB::connection('reports_db')->table("area_history")
                ->select('area_history.municipality')
                ->where('area_history.region', '=', $region_name)
                ->where('area_history.province', '=', $province_name)
                ->groupBy('area_history.province', 'area_history.municipality')
                ->get();

            $return_str = "";
            foreach($municipality_list as $mun){

                $rcep_allocation = DB::connection('allocations_db')->table('allocated_volume')
                    ->where('region', '=', $region_name)
                    ->where('province', '=', $province_name)
                    ->where('municipality', '=', $mun->municipality)
                    ->first();

                if(count($rcep_allocation) > 0){
                    $total_allocations = number_format($rcep_allocation->allocation);
                }else{
                    $total_allocations = 0;
                    $total_bags = "no area allocation";
                }

                $return_str .= "<tr>";
                $return_str .= "<td>$mun->municipality</td>";
                $return_str .= "<td>$total_allocations</td>";
                $return_str .= "<td>0</td>";
                $return_str .= "<td>0</td>";
                $return_str .= "<td>N/A</td>";
                $return_str .= "</tr>";
            }

            return $return_str;
        }


    }

    public function SeedBeneficiariesProvincial2_backup(Request $request){
        try{
            $farmer_profile_table = 'prv'.substr($request->province, -2).'_farmer_profile';
            $farmer_area_history = 'prv'.substr($request->province, -2).'_area_history';
            $farmer_performance = 'prv'.substr($request->province, -2).'_performance';

            $region_name = DB::connection('mysql')->table('lib_regions')->where('regCode', '=', $request->region)->first()->regDesc;
            $province_name = DB::connection('mysql')->table('lib_provinces')->where('provCode', '=', $request->province)->first()->provDesc;

            $municipality_list = DB::connection('distribution_db')->table($farmer_profile_table)
                                ->select('municipality')
                                ->where('region', '=', $region_name)
                                ->where('province', '=', $province_name)
                                ->groupBy('municipality')
                                ->orderBy('municipality', 'asc')
                                ->get();

            //dd($municipality_list);

            $return_str = "";
            foreach($municipality_list as $mun){

                //farmer count per municipality
                $farmers = DB::connection('distribution_db')->table($farmer_profile_table)
                    ->select(DB::raw('COUNT(farmerID) as total_farmers'))
                    ->where('region', '=', $region_name)
                    ->where('province', '=', $province_name)
                    ->where('municipality', '=', $mun->municipality)
                    ->first();
                $farmer_count = number_format($farmers->total_farmers);

                //total area per municipality
                $area = DB::connection('distribution_db')->table($farmer_area_history)
                    ->select('area')
                    ->where('region', '=', $region_name)
                    ->where('province', '=', $province_name)
                    ->where('municipality', '=', $mun->municipality)
                    ->get();

                $total_area = 0;
                foreach($area as $row){
                    $total_area += $row->area;
                }
                $total_area = number_format($total_area, 2);


                try{
                    $dist_table = 'prv'.substr($request->province, -2).'_released';
                    $bags = DB::connection('delivery_inspection_db')->table("$dist_table")
                        ->select(DB::raw('COUNT(bags) as total_bags'))
                        ->join($GLOBALS['season_prefix']."rcep_distribution2.$farmer_profile_table", $GLOBALS['season_prefix']."rcep_distribution2.$farmer_profile_table.farmerID", "=", "$dist_table.farmer_id")
                        ->where('region', '=', $region_name)
                        ->where('province', '=', $province_name)
                        ->where('municipality', '=', $mun->municipality)
                        ->first();
                    $total_bags = $bags->total_bags;
                }catch(\Illuminate\Database\QueryException $ex){
                    $total_bags = "No bags distributed.";
                }

                    $rcep_allocation = DB::connection('allocations_db')->table('allocated_volume')
                        ->where('region', '=', $region_name)
                        ->where('province', '=', $province_name)
                        ->where('municipality', '=', $mun->municipality)
                        ->first();

                if(count($rcep_allocation) > 0){
                    $total_allocations = $rcep_allocation->allocation;
                }else{
                    $total_allocations = 0;
                    $total_bags = "no area allocation";
                }

                $return_str .= "<tr>";
                $return_str .= "<td>$mun->municipality</td>";
                $return_str .= "<td>$total_allocations</td>";
                $return_str .= "<td>$farmer_count</td>";
                $return_str .= "<td>$total_area</td>";
                $return_str .= "<td>$total_bags</td>";
                $return_str .= "</tr>";
            }

            return $return_str;

        }catch(\Illuminate\Database\QueryException $ex){
            return 'rsbsa_error';
        }
    }

    public function SeedBeneficiariesRegional(Request $request){
        try{
            $farmer_profile_table = 'prv'.substr($request->province, -2).'_farmer_profile';
            $farmer_area_history = 'prv'.substr($request->province, -2).'_area_history';
            $farmer_performance = 'prv'.substr($request->province, -2).'_performance';

            $region_name = DB::connection('mysql')->table('lib_regions')->where('regCode', '=', $request->region)->first()->regDesc;

            $povince_list = DB::connection('distribution_db')->table($farmer_profile_table)
                                ->select('municipality')
                                ->where('region', '=', $region_name)
                                ->groupBy('province')
                                ->orderBy('province', 'asc')
                                ->get();

            $return_str = "";
            foreach($povince_list as $province){

                //farmer count per municipality
                $farmers = DB::connection('distribution_db')->table($farmer_profile_table)
                    ->select(DB::raw('COUNT(farmerID) as total_farmers'))
                    ->where('region', '=', $region_name)
                    ->where('province', '=', $province->municipality)
                    ->first();
                $farmer_count = number_format($farmers->total_farmers);

                //total area per municipality
                $area = DB::connection('distribution_db')->table($farmer_area_history)
                    ->select('area')
                    ->where('region', '=', $region_name)
                    ->where('province', '=', $province->municipality)
                    ->get();

                $total_area = 0;
                foreach($area as $row){
                    $total_area += $row->area;
                }
                $total_area = number_format($total_area, 2);

                $return_str .= "<tr>";
                $return_str .= "<td>$mun->municipality</td>";
                $return_str .= "<td>$farmer_count</td>";
                $return_str .= "<td>$total_area</td>";
                $return_str .= "</tr>";
            }

            return $return_str;

        }catch(\Illuminate\Database\QueryException $ex){
            return 'rsbsa_error';
        }
    }

    // input by timothy
    public function coop_summary_view(){
        return view('cooperative_summary.index');
    }

    public function coop_sg_count_total(){

        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
        }
        $data = [
            'sgTotal' => $sgCountTotal,
            'haTotal' => $haCountTotal,
            'coopTotal' => count($coop),
        ];

        return $data;
    }

    public function coop_sg_count(){

        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
            $data = [
                'name' => $value->coopName,
                'haCount' => $haCount,
                'sgCount' => $sgCount,
                'sgTotal' => $sgCountTotal,
                'haTotal' => $haCountTotal,
                'coopTotal' => count($coop),
            ];
            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->make(true);
    }

    public function coop_sg_count_excel(){
        $excel = new excel;
        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
            $data = [
                'Cooperative Name' => $value->coopName,
                'No. of Seed Growers' => $sgCount,
                'Commited Area ( ha )' => $haCount,
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Cooperative Name' => "TOTAL",
            'No. of Seed Growers' => $sgCountTotal,
            'Commited Area ( ha )' => $haCountTotal,
        ];
        array_push($table_data, $data2);

        return Excel::create('Cooperatives Seed Grower Counts', function($excel) use ($table_data) {
            $excel->sheet('Sheet1', function($sheet) use ($table_data) {
                $sheet->fromArray($table_data);
            });
        })->download('xlsx');
    }
}

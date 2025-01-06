<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;

use App\SeedCooperatives;
use App\SeedGrowers;

use Config;
use DB;
use Excel;

use Session;

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

    public function Home_scheduled(){
        $d_provinces = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        return view('reports.schedule.report')->with('d_provinces', $d_provinces);
    }

    public function set_database($database_name){
        try {
            \Config::set('database.connections.reports_db.database', $database_name);
            DB::purge('reports_db');

            DB::connection('reports_db')->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }

    function generate_national_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_national_reports')->truncate();

        //get all regions
        $region_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('region')->orderBy('region_sort', 'ASC')->get();
        
        //report variables
        $male_count = 0;
        $female_count = 0;
        $total_farmers_count = 0;
        $total_bags_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_regions = 1;

        $covered_municipalities = 0;
        $covered_provinces = 0;

        foreach($region_prv as $r_prv){
            $region_code = $r_prv->regCode;

            //count all regions
            $total_regions += 1;

            //get all provinces in region || prv databases
            $province_list = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regCode', $region_code)
                ->groupBy('province')
                ->get();
            
            //loop through all prv(s)
            foreach($province_list as $prv_list){
                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);

                //check if database exists
                //\Config::set('database.connections.reports_db.database', $database);
                //DB::purge('reports_db');
                $table_conn = $this->set_database($database);

                if($table_conn == "Connection Established!"){
                    //check if database has distribution data
                    $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                    if(count($prv_dist_data) > 0){

                        $covered_provinces += 1;

                        //get municipalities
                        $number_of_municipalities = DB::connection('reports_db')->table("released")
                            ->select('released.municipality')
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->groupBy('municipality')
                            ->get();

                        $covered_municipalities += count($number_of_municipalities);

                        /*$report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'),
                                    DB::raw('SUM(farmer_profile.area) as dist_area'),
                                    DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            //->where('released.municipality', '=', $prv_list->municipality)
                            ->first();

                        if(count($report_res) > 0){
                            $sex_res = DB::connection('reports_db')->table("released")
                                ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                                ->join('farmer_profile', function ($table_join) {
                                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                                })
                                ->where('released.province', '=', $prv_list->province)
                                //->where('released.municipality', '=', $prv_list->municipality)
                                ->groupBy('sex')
                                ->orderBy('sex', 'DESC')
                                ->get();
        
                            foreach($sex_res as $s_row){
                                if($s_row->sex == 'Male'){
                                    $male_count += $s_row->sex_count;
                                }elseif($s_row->sex == 'Femal'){
                                    $female_count += $s_row->sex_count;
                                }
                            }
                        }*/

                        //get total farmers & total bags
                        $report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'))
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->first();

                        //compute total distribution area, actual area, sex_count etc.
                        $report_list = DB::connection('reports_db')->table("released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->get();

                        foreach($report_list as $report_row){
                            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                                ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();

                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                            }else{
                                $female_count += 1;
                            }
                        }

                        $total_farmers_count += $report_res->total_farmers;
                        $total_bags_count += $report_res->total_bags;
                        //$total_dist_area_count += $report_res->dist_area;
                        //$total_actual_area_count += $report_res->actual_area;
                    }
                }
            }
        }

        //finally insert the data
        //save region data to database
        DB::connection('rcep_reports_db')->table('lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);
    }

    function generate_regional_data(){

        //empty database
        DB::connection('rcep_reports_db')->table('lib_regional_reports')->truncate();

        $region_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('region')->orderBy('region_sort')->get();
        
        foreach($region_prv as $r_prv){
            $region_code = $r_prv->regCode;

            //get all provinces in region || prv databases
            $province_list = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regCode', $region_code)
                ->groupBy('province')
                ->get();
            
            //report variables
            $male_count = 0;
            $female_count = 0;
            $total_farmers_count = 0;
            $total_bags_count = 0;
            $total_dist_area_count = 0;
            $total_actual_area_count = 0;

            $total_provinces = 0;
            $total_municipalities = 0;

            //loop through all prv(s)
            foreach($province_list as $prv_list){
                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);

                //check if database exists
                //\Config::set('database.connections.reports_db.database', $database);
                //DB::purge('reports_db');
                $table_conn = $this->set_database($database);

                if($table_conn == "Connection Established!"){

                    //check if database has distribution data
                    $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                    if(count($prv_dist_data) > 0){

                        //total provinces covered...
                        $province_count =  DB::connection('reports_db')->table("released")
                            ->groupBy('province')
                            ->get();
                        $total_provinces += count($province_count);

                        //total municipalities covered...
                        $mun_count =  DB::connection('reports_db')->table("released")
                            ->groupBy('municipality')
                            ->get();
                        $total_municipalities += count($mun_count);

                        /*$report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'),
                                    DB::raw('SUM(farmer_profile.area) as dist_area'),
                                    DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            //->where('released.municipality', '=', $prv_list->municipality)
                            ->first();

                        if(count($report_res) > 0){
                            $sex_res = DB::connection('reports_db')->table("released")
                                ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                                ->join('farmer_profile', function ($table_join) {
                                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                                })
                                ->where('released.province', '=', $prv_list->province)
                                //->where('released.municipality', '=', $prv_list->municipality)
                                ->groupBy('sex')
                                ->orderBy('sex', 'DESC')
                                ->get();
        
                            foreach($sex_res as $s_row){
                                if($s_row->sex == 'Male'){
                                    $male_count += $s_row->sex_count;
                                }elseif($s_row->sex == 'Femal'){
                                    $female_count += $s_row->sex_count;
                                }
                            }
                        }*/

                        //get total farmers & total bags
                        $report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'))
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->first();

                        //compute total distribution area, actual area, sex_count etc.
                        $report_list = DB::connection('reports_db')->table("released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->get();

                        foreach($report_list as $report_row){
                            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                                ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();

                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                            }else{
                                $female_count += 1;
                            }
                        }

                        $total_farmers_count += $report_res->total_farmers;
                        $total_bags_count += $report_res->total_bags;
                        //$total_dist_area_count += $report_res->dist_area;
                        //$total_actual_area_count += $report_res->actual_area;

                    }
                }
            }

            //save region data to database
            DB::connection('rcep_reports_db')->table('lib_regional_reports')
                ->insert([
                    'region'            => $r_prv->regionName,
                    'total_provinces'   => $total_provinces,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => $total_farmers_count,
                    'total_bags'        => $total_bags_count,
                    'total_dist_area'   => $total_dist_area_count,
                    'total_actual_area' => $total_actual_area_count,
                    'total_male'        => $male_count,
                    'total_female'      => $female_count,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            
        }
    }

    function generate_provincial_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_provincial_reports')->truncate();
    
        $province_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->groupBy('province')
            ->orderBy('regCode')
            ->orderBy('province', 'DESC')
            ->get();

        foreach($province_prv as $prv_list){
            $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){

                $male_count = 0;
                $female_count = 0;
                $total_municipalities = 0;

                $total_dist_area_count = 0;
                $total_actual_area_count = 0;

                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){

                    //total municipalities covered...
                    $mun_count =  DB::connection('reports_db')->table("released")
                        ->groupBy('municipality')
                        ->get();
                    $total_municipalities += count($mun_count);

                    /*$report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                            //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.province', '=', $prv_list->province)
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
    
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }*/

                    //get total farmers & total bags
                    $report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'))
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->first();

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::connection('reports_db')->table("released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->get();

                    foreach($report_list as $report_row){
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        $total_dist_area_count += $farmer_profile->area;
                        $total_actual_area_count += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $male_count += 1;
                        }else{
                            $female_count += 1;
                        }
                    }

                    //save province data to database
                    DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }else{
                    //save province data to database || no distribution data
                    DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

            }else{
                //save province data to database || no database
                DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->regionName,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }

        }
    }

    function generate_municipal_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_municipal_reports')->truncate();

        //list of municipalities
        $mun_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->orderBy('regCode')
            ->orderBy('provCode')
            ->orderBy('municipality', 'ASC')
            ->get();

        foreach($mun_prv as $row){
            $database = $GLOBALS['season_prefix']."prv_".substr($row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){

                $male_count = 0;
                $female_count = 0;

                $total_dist_area_count = 0;
                $total_actual_area_count = 0;

                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){
                    /*$report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                            $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.province', '=', $row->province)
                            ->where('released.municipality', '=', $row->municipality)
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
    
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }*/

                    //get total farmers & total bags
                    $report_res =  DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'))
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->first();

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::connection('reports_db')->table("released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->get();

                    foreach($report_list as $report_row){
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        $total_dist_area_count += $farmer_profile->area;
                        $total_actual_area_count += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $male_count += 1;
                        }else{
                            $female_count += 1;
                        }
                    }

                    //save municipal data to database
                    DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }else{
                    //save municipal data to database || no distribution data
                    DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

            }else{
                //save municipal data to database || no database
                DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->insert([
                    'province'          => $row->province,
                    'municipality'      => $row->municipality,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }
        }
    }

    public function generate_variety_data(){
        DB::connection('rcep_reports_db')->table('lib_variety_report')->truncate();
        $drop_off_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->get();

        //get all prv(s) in the dropoff point list and getting distinct values
        $prv_list = array();
        foreach($drop_off_list as $dop_row){
            $prv_list[] = $GLOBALS['season_prefix']."prv_".substr($dop_row->prv_dropoff_id, 0, 4);     
        }
        $prv_list = array_unique($prv_list);

        //loop through all prv databases
        foreach($prv_list as $prv_database){
            $table_conn = $this->set_database($prv_database);
            if($table_conn == "Connection Established!"){
                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){
                    $variety_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('SUM(released.bags) as total_seed_bags'),'seed_variety')
                        ->groupBy('seed_variety')
                        ->get();

                    foreach($variety_res as $v_row){
                        //save to database per total variety of each prv database
                        DB::connection('rcep_reports_db')->table('lib_variety_report')
                        ->insert([
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags
                        ]);
                    }
                }
            }
        }
    }

    //API for auto-insertion of generated report data every 12 MN
    public function scheduledReport(Request $request){
        $this->generate_national_data();
        $this->generate_regional_data();
        $this->generate_provincial_data();
        $this->generate_municipal_data();
        $this->generate_variety_data();

        DB::connection('mysql')->table('lib_logs')
        ->insert([
            'category' => 'REPORT',
            'description' => 'Pre-Scheduled report generation successfull',
            'author' => 'SYSTEM',
            'ip_address' => 'LOCAL'
        ]);
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
                ->select('seed_variety', DB::raw('SUM(released.bags) as total_varieties'))
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->groupBy('released.seed_variety')
                ->orderBy('released.seed_variety')
            )->make(true);
        }
    }

    public function SeedBeneficiariesVarietiesProvincial(Request $request){
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
                ->select('seed_variety', DB::raw('SUM(released.bags) as total_varieties'))
                ->where('released.province', '=', $request->province)
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
                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
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
                        $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                        $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
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

    /* REPORT HEADER */
    public function TotalValues(Request $request){
        $municipalities = count(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('municipality')
            ->groupBy('municipality')
            ->get()); 
            
        $provinces = count(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('province')
            ->groupBy('province')
            ->get()); 

        //to get total_beneficiaries, actual area, distribution area & bags_distributed
        $prv_databases = DB::connection('mysql')->table('lib_seasons_data')->get();

        $total_farmers = 0;
        $total_bags = 0;
        $dist_area = 0;
        $actual_area = 0;
        $male_count = 0;
        $female_count = 0;

        foreach($prv_databases as $prv_row){
            $db_name = $GLOBALS['season_prefix']."prv_".$prv_row->prv_code;
            $test = DB::select("SELECT SCHEMA_NAME
                                    FROM INFORMATION_SCHEMA.SCHEMATA
                                WHERE SCHEMA_NAME = '$db_name'");
            if(empty($test)){
                // no prv database found
            }else{
                //DB exists
                \Config::set('database.connections.reports_db.database', $db_name);
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

                if($table_conn == 'established_connection'){
                    $report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                            ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                                ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
        
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }
                    
                    $total_farmers += $report_res->total_farmers;
                    $total_bags += $report_res->total_bags;
                    $dist_area += $report_res->dist_area;
                    $actual_area += $report_res->actual_area;
                }
            }
        }

        $data_arr = array(
            "total_municipalities" => $municipalities,
            "total_provinces" => $provinces,
            "total_farmers" => number_format($total_farmers),
            "total_bags" => number_format($total_bags),
            "dist_area" => number_format($dist_area, '2'),
            "actual_area" => number_format($actual_area, '2'),
            "total_male" => $male_count,
            "total_female" => $female_count
        );
        
        return $data_arr;
    }
    /* REPORT HEADER */

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

    /** MODIFIED REPORTS */

    function load_regional_data(){        
        $regional_data = DB::connection('rcep_reports_db')
            ->table('lib_regional_reports')
            ->orderBy('region', 'ASC')
            ->get();

        return $regional_data;
    }

    function load_provincial_data(){        
        $province_data = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->orderBy('province', 'ASC')
            ->get();

        return $province_data;
    }

    function load_municipal_data(){        
        $municipal_data = DB::connection('rcep_reports_db')
            ->table('lib_municipal_reports')
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();

        return $municipal_data;
    }

    function load_national_data(){        
        $national_data = DB::connection('rcep_reports_db')
            ->table('lib_national_reports')
            ->get();

        return $national_data;
    }

    function municipal_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;

            $data = [
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Province' => '',
            'Municipality' => 'TOTAL:',
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function provincial_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;

            $data = [
                'Province' => $row->province,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Province' => 'TOTAL:',
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function regional_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        $total_provinces = 0;
        $total_municipalities = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;
            $total_provinces += $row->total_provinces;
            $total_municipalities += $row->total_municipalities;

            $data = [
                'Region' => $row->region,
                'Covered Provinces' => $row->total_provinces == '' ? '0' : (string) $row->total_provinces,
                'Covered Municipaloities' => $row->total_municipalities == '' ? '0' : (string) $row->total_municipalities,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Region' => 'TOTAL:',
            'Covered Provinces' => (string) $total_provinces,
            'Covered Municipaloities' => (string) $total_municipalities,
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function national_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $data = [
                'OVERALL SUMMARY: ' => '',
                'Covered Provinces' => (string) number_format($row->provinces),
                'Covered Municipalities' => (string) number_format($row->municipalities),
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) number_format($row->total_farmers),
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) number_format($row->total_dist_area),
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) number_format($row->total_actual_area),
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) number_format($row->total_bags),
                'Total Male' => $row->total_male == '' ? '0' : (string) number_format($row->total_male),
                'Total Female' => $row->total_female == '' ? '0' : (string) number_format($row->total_female)
            ];
            array_push($table_data, $data);
        }

       $empty_row = [
            'OVERALL SUMMARY: ' => '',
            'Covered Provinces' => '',
            'Covered Municipalities' => '',
            'Total beneficiaries' => '',
            'Distribution Area' => '',
            'Actual Area' => '',
            'Bags Distributed (20kg/bag)' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($table_data, $empty_row);

        return $table_data;
    }

    public function Home_Report2(){
        $regional_data = $this->load_regional_data();
        return view('reports.modified.home')
            ->with('regional_data', $regional_data);
    }

    public function Home_Report2_provincial(){
        //$provincial_data = $this->load_provincial_data();
        $region_list = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_provincial_reports')
            ->select('*')
            ->orderBy('region','ASC')
            ->groupBy('region')
            ->get();
        return view('reports.modified.province')
            ->with('region_list', $region_list);
    }

    public function Home_Report2_municipal(){
        //$municipal_data = $this->load_municipal_data();
        $municipal_list = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_provincial_reports')
            ->select('*')
            ->orderBy('province','ASC')
            ->groupBy('province')
            ->get();
        return view('reports.modified.municipality')
            ->with('municipal_list', $municipal_list);
    }

    public function Home_Report2_national(){
        $national_data = $this->load_national_data();
        return view('reports.modified.national')
            ->with('national_data', $national_data);
    }

    public function convert_to_excel(Request $request){

        $excel = new Excel;

        if($request->excel_type == "regional"){
            /*$data = $this->load_regional_data();
            $table_data = $this->regional_excel_content($data);
            $myFile = Excel::create('Regional Report', function($excel) use ($table_data) {
                $excel->sheet('REGIONS', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "REGIONAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";*/

        }else if($request->excel_type == "provincial"){
            $data = $this->load_provincial_data();
            $table_data = $this->provincial_excel_content($data);

            $myFile = Excel::create('Provincial Report', function($excel) use ($table_data) {
                $excel->sheet('PROVINCES', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "PROVINCIAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";

        }else if($request->excel_type == "municipal"){
            $data = $this->load_municipal_data();
            $table_data = $this->municipal_excel_content($data);

            $myFile = Excel::create('Municipal Report', function($excel) use ($table_data) {
                $excel->sheet('MUNICIPALITIES', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "MUNICIPAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";

        }else if($request->excel_type == "national"){
            //national data
            $data = $this->load_national_data();
            $table_data = $this->national_excel_content($data);

            //regional data
            $regional_data = $this->load_regional_data();
            $regional_table_data = $this->regional_excel_content($regional_data);

            $myFile = Excel::create('National Report', function($excel) use ($table_data, $regional_table_data) {
                $excel->sheet('OVERALL & REGIONAL', function($sheet) use ($table_data, $regional_table_data) {
                    $sheet->fromArray($table_data);
                    $sheet->fromArray($regional_table_data);
                });
            });

            $file_name = "NATIONAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";
        }

        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

    public function convert_to_excel_province($province){
        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->where('province', $province)->groupBy('province')->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                //array container
                $table_data = array();
                $province_sheet = array();
                $municipal_table = array();

                /** PROVINCE SUMMARY DATA */
                //get overall summary for province
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_provincial_reports')
                    ->where('province', $province)
                    ->first();

                $province_data = [
                    'Province Name' => $province,
                    'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($province_sheet, $province_data);

                $blank_row = [
                    'Province Name' => '',
                    'Covered Municipalities' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($province_sheet, $blank_row);
                /** PROVINCE SUMMARY DATA */

                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */
                $municipal_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->orderBy('municipality')
                    ->get();

                $mun_total_farmers = 0;
                $mun_total_distArea = 0;
                $mun_total_actArea = 0;
                $mun_total_male = 0;
                $mun_total_female = 0;
                $mun_total_bags = 0;

                $mun_cnt = 1;
                foreach($municipal_summary as $m_row){
                    $municipal_data = [
                        '#' => $mun_cnt,
                        'Municipality Name' => $m_row->municipality,
                        'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                        'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                        'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                        'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                        'Total Male' => (string) number_format($m_row->total_male),
                        'Total Female' => (string) number_format($m_row->total_female)
                    ];
                    array_push($municipal_table, $municipal_data);

                    ++$mun_cnt;
                    $mun_total_farmers += $m_row->total_farmers;
                    $mun_total_distArea += $m_row->total_dist_area;
                    $mun_total_actArea += $m_row->total_actual_area;
                    $mun_total_male += $m_row->total_male;
                    $mun_total_female += $m_row->total_female;
                    $mun_total_bags += $m_row->total_bags;
                }

                $total_municipal_data = [
                    '#' => '',
                    'Municipality Name' => 'TOTAL: ',
                    'Total Beneficiaries' => (string) number_format($mun_total_farmers),
                    'Total Distribution Area' => (string) number_format($mun_total_distArea),
                    'Total Actual Area' => (string) number_format($mun_total_actArea),
                    'Total Bags Distributed' => (string) number_format($mun_total_bags),
                    'Total Male' => (string) number_format($mun_total_male),
                    'Total Female' => (string) number_format($mun_total_female)
                ];
                array_push($municipal_table, $total_municipal_data);
                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */

                $province_farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                        'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                        'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->orderBy('released.province', 'ASC')
                ->get();

                $total_dist_area = 0;
                $total_actual_area = 0;
                $total_bags = 0;

                foreach ($province_farmer_list as  $row) {

                    //check other_info table
                    $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                    if(count($other_info_data) > 0){
                        $birthdate = $other_info_data->birthdate;
                        $mother_fname = $other_info_data->mother_fname;
                        $mother_mname = $other_info_data->mother_mname;
                        $mother_lname = $other_info_data->mother_lname;
                        $mother_suffix = $other_info_data->mother_suffix;
						
						if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                            $phone_number = "";
                        }else{
                            $phone_number = $other_info_data->phone;
                        }
                    }else{
                        $birthdate = '';
                        $mother_fname = '';
                        $mother_mname = '';
                        $mother_lname = '';
                        $mother_suffix = '';
						
						$phone_number = '';
                    }

                    //get farmer_profile
                    $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->orderBy('farmerID')
                        ->first();
						
					//get name of encoder using released.by in sdms_db_dev
                    $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                    if($encoder_name->middleName == ''){
                        $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                    }else{
                        $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                    }

                    //compute totals
                    $total_dist_area += $farmer_profile->area;
                    $total_actual_area += $farmer_profile->actual_area;
                    $total_bags += $row->bags;

                    $data = [
                        'RSBSA #' => $row->rsbsa_control_no,
                        'QR Code' => $farmer_profile->distributionID,
                        "Farmer's First Name" => $farmer_profile->firstName,
                        "Farmer's Middle Name" => $farmer_profile->midName,
                        "Farmer's Last Name" => $farmer_profile->lastName,
                        "Farmer's Extension Name" => $farmer_profile->extName,
                        'Sex' => $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex,
                        'Birth Date' => $birthdate,
						'Telephone Number' => $phone_number,
                        'Province' => $row->province,
                        'Municipality' => $row->municipality,
                        "Mother's First Name" => $mother_fname,
                        "Mother's Middle Name" => $mother_mname,
                        "Mother's Last Name" => $mother_lname,
                        "Mother's Suffix" => $mother_suffix,
                        'Distribution Area' => $farmer_profile->area,
                        'Actual Area' => $farmer_profile->actual_area,
                        'Bags' => $row->bags,
                        'Seed Variety' => $row->seed_variety,
                        'Date Released' => $row->date_released,
                        'Farmer ID' => $row->farmer_id,
						'Released By' => $encoder_name
                    ];
                    array_push($table_data, $data);
                }

                $data2 = [
                    'RSBSA #' => '',
                    'QR Code' => '',
                    "Farmer's First Name" => '',
                    "Farmer's Middle Name" => '',
                    "Farmer's Last Name" => '',
                    "Farmer's Extension Name" => '',
                    'Sex' => '',
                    'Birth Date' => '',
					'Telephone Number' => '',
                    'Province' => '',
                    'Municipality' => '',
                    "Mother's First Name" => '',
                    "Mother's Middle Name" => '',
                    "Mother's Last Name" => '',
                    "Mother's Suffix" => 'TOTAL: ',
                    'Distribution Area' => $total_dist_area,
                    'Actual Area' => $total_actual_area,
                    'Bags' => $total_bags,
                    'Seed Variety' => '',
                    'Date Released' => '',
                    'Farmer ID' => '',
					'Released By' => ''
                ];
                array_push($table_data, $data2);

                return Excel::create($province."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $province_sheet, $municipal_table) {
                    $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                        $sheet->fromArray($province_sheet);
                        $sheet->fromArray($municipal_table);
                    });

                    $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                        $sheet->fromArray($table_data);
                    });
                })->download('xlsx');
            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.province');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.province');
        }
    }

    public function convert_to_excel_municipality($province, $municipality){
        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->where('province', $province)
            ->where('municipality', $municipality)
            ->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                $mun_data_arr = array();
                /** MUNICIPAL SUMMARY DATA */
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->where('municipality', $municipality)
                    ->first();

                $mun_data = [
                    'Province Name' => $province,
                    'Municipality Name' => $municipality,
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($mun_data_arr, $mun_data);

                $blank_row = [
                    'Province Name' => '',
                    'Municipality Name' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($mun_data_arr, $blank_row);
                /** MUNICIPAL SUMMARY DATA */

                $farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->where('released.municipality', '=', $municipality)
                ->get();

                //check if there is data returned after inserting parameters
                if(count($farmer_list) > 0){
                     //generate array to be passed to the excel file
                    $table_data = array();
                    $total_dist_area = 0;
                    $total_actual_area = 0;
                    $total_bags = 0;

                    foreach ($farmer_list as  $row) {
                        /*
                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->birthdate;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                        }

                        //compute totals
                        $total_dist_area += $row->area;
                        $total_actual_area += $row->actual_area;
                        $total_bags += $row->bags;

                        $data = [
                            'RSBSA #' => $row->rsbsa_control_no,
                            'QR Code' => $row->distributionID,
                            "Farmer's First Name" => $row->firstName,
                            "Farmer's Middle Name" => $row->midName,
                            "Farmer's Last Name" => $row->lastName,
                            "Farmer's Extension Name" => $row->extName,
                            'Sex' => $row->sex == 'Femal' ? 'Female' : $row->sex,
                            'Birth Date' => $birthdate,
                            'Province' => $row->province,
                            'Municipality' => $row->municipality,
                            "Mother's First Name" => $mother_fname,
                            "Mother's Middle Name" => $mother_mname,
                            "Mother's Last Name" => $mother_lname,
                            "Mother's Suffix" => $mother_suffix,
                            'Distribution Area' => $row->area,
                            'Actual Area' => $row->actual_area,
                            'Bags' => $row->bags,
                            'Seed Variety' => $row->seed_variety,
                            'Date Released' => $row->date_released,
                            'Farmer ID' => $row->farmer_id
                        ];
                        array_push($table_data, $data);
                        */

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;
							
							if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
							
							$phone_number = '';
                        }
						
						//get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                        if($encoder_name->middleName == ''){
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }else{
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        //compute totals
                        $total_dist_area += $farmer_profile->area;
                        $total_actual_area += $farmer_profile->actual_area;
                        $total_bags += $row->bags;

                        $data = [
                            'RSBSA #' => $row->rsbsa_control_no,
                            'QR Code' => $farmer_profile->distributionID,
                            "Farmer's First Name" => $farmer_profile->firstName,
                            "Farmer's Middle Name" => $farmer_profile->midName,
                            "Farmer's Last Name" => $farmer_profile->lastName,
                            "Farmer's Extension Name" => $farmer_profile->extName,
                            'Sex' => $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex,
                            'Birth Date' => $birthdate,
							'Telephone Number' => $phone_number,
                            'Province' => $row->province,
                            'Municipality' => $row->municipality,
                            "Mother's First Name" => $mother_fname,
                            "Mother's Middle Name" => $mother_mname,
                            "Mother's Last Name" => $mother_lname,
                            "Mother's Suffix" => $mother_suffix,
                            'Distribution Area' => $farmer_profile->area,
                            'Actual Area' => $farmer_profile->actual_area,
                            'Bags' => $row->bags,
                            'Seed Variety' => $row->seed_variety,
                            'Date Released' => $row->date_released,
                            'Farmer ID' => $row->farmer_id,
							'Released By' => $encoder_name
                        ];
                        array_push($table_data, $data);
                    }

                    $data2 = [
                        'RSBSA #' => '',
                        'QR Code' => '',
                        "Farmer's First Name" => '',
                        "Farmer's Middle Name" => '',
                        "Farmer's Last Name" => '',
                        "Farmer's Extension Name" => '',
                        'Sex' => '',
                        'Birth Date' => '',
						'Telephone Number' => '',
                        'Province' => '',
                        'Municipality' => '',
                        "Mother's First Name" => '',
                        "Mother's Middle Name" => '',
                        "Mother's Last Name" => '',
                        "Mother's Suffix" => 'TOTAL: ',
                        'Distribution Area' => $total_dist_area,
                        'Actual Area' => $total_actual_area,
                        'Bags' => $total_bags,
                        'Seed Variety' => '',
                        'Date Released' => '',
                        'Farmer ID' => '',
						'Released By' => ''
                    ];
                    array_push($table_data, $data2);

                    return Excel::create($municipality."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $mun_data_arr) {
                        $excel->sheet('MUNICIPALITY SUMMARY', function($sheet) use ($mun_data_arr) {
                            $sheet->fromArray($mun_data_arr);
                        });

                        $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                            $sheet->fromArray($table_data);
                        });
                    })->download('xlsx');
                }else{
                    Session::flash('error_msg', "The selected municipality has no distribution data.");
                    return redirect()->route('rcep.report2.municipality');
                }

            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.municipality');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.municipality');
        }
    }

    public function convert_to_excel_region($region){
        $excel_data = array();
        $region_summary = DB::connection('rcep_reports_db')
            ->table('lib_regional_reports')
            ->where('region', $region)
            ->first();

        $region_summary_data = [
            'Region Name' => $region_summary->region,
            'Covered Provinces' => (string) number_format($region_summary->total_provinces),
            'Covered Municipalities' => (string) number_format($region_summary->total_municipalities),
            'Total Farmers' => (string) number_format($region_summary->total_farmers),
            'Total Bags Distributed (20kg/bag)' => (string) number_format($region_summary->total_bags),
            'Total Distribution Area' => (string) number_format($region_summary->total_dist_area),
            'Total Actual Area' => (string) number_format($region_summary->total_actual_area),
            'Total Male' => (string) number_format($region_summary->total_male),
            'Total Female' => (string) number_format($region_summary->total_female)
        ];
        array_push($excel_data, $region_summary_data);

        $blanK_row = [
            'Region Name' => '',
            'Covered Provinces' => '',
            'Covered Municipalities' => '',
            'Total Farmers' => '',
            'Total Bags Distributed (20kg/bag)' => '',
            'Total Distribution Area' => '',
            'Total Actual Area' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($excel_data, $blanK_row);

        $province_data = array();
        $selected_region_province_summary = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->where('region', $region)
            ->get();

        //gloabl variables for provincial data
        $total_municipalities = 0;
        $total_farmers = 0;
        $total_bags = 0;
        $total_dist_area = 0;
        $total_actual_area = 0;
        $total_male = 0;
        $total_female = 0;

        foreach($selected_region_province_summary as $p_row){
            $p_data= [
                'Region' => $p_row->region,
                'Province' => $p_row->province,
                'Covered Municipalities' => (string) number_format($p_row->total_municipalities),
                'Total Farmers' => (string) number_format($p_row->total_farmers),
                'Total Bags Distributed (20kg/bag)' => (string) number_format($p_row->total_bags),
                'Total Distribution Area' => (string) number_format($p_row->total_dist_area),
                'Total Actual Area' => (string) number_format($p_row->total_actual_area),
                'Total Male' => (string) number_format($p_row->total_male),
                'Total Female' => (string) number_format($p_row->total_female)
            ];
            array_push($province_data, $p_data);

            $total_municipalities += $p_row->total_municipalities;
            $total_farmers += $p_row->total_farmers;
            $total_bags += $p_row->total_bags;
            $total_dist_area += $p_row->total_dist_area;
            $total_actual_area +=$p_row->total_actual_area;
            $total_male += $p_row->total_male;
            $total_female += $p_row->total_female;
        }

        $total_p_data= [
            'Region' => '',
            'Province' => 'TOTAL: ',
            'Covered Municipalities' =>(string) number_format($total_municipalities),
            'Total Farmers' => (string) number_format($total_farmers),
            'Total Bags Distributed (20kg/bag)' => (string) number_format($total_bags),
            'Total Distribution Area' => (string) number_format($total_dist_area),
            'Total Actual Area' => (string) number_format($total_actual_area),
            'Total Male' => (string) number_format($total_male),
            'Total Female' => (string) number_format($total_female)
        ];
        array_push($province_data, $total_p_data);

        return Excel::create($region_summary->region."_".date("Y-m-d H:i:s"), function($excel) use ($excel_data, $province_data) {
            $excel->sheet("REGION_SUMMARY", function($sheet) use ($excel_data, $province_data) {
                $sheet->fromArray($excel_data);
                $sheet->fromArray($province_data);
            });
        })->download('xlsx');
    }

    /**NEW FUNCTIONS FOR SEED BENEFICIARY REPORTS - jpalileo */
    public function generateProvincialReportData(Request $request){
        $province_data = DB::connection('rcep_reports_db')
        ->table('lib_provincial_reports')
        ->orderBy('province', 'ASC')
        ->get();

        return Datatables::of(DB::connection('rcep_reports_db')->table('lib_provincial_reports')
            ->where('region', '=', $request->region)
            ->orderBy('province', 'ASC')
        )
        ->addColumn('action', function($row){
            //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
            $url = route('rcef.report.excel.province', $row->province);
            return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
        })
        ->make(true);
    }

    public function generateMunicipalReportData(Request $request){
        return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
            ->where('province', '=', $request->province)
            ->orderBy('municipality', 'ASC')
        )
        ->addColumn('action', function($row){
            //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
            $url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
            return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
        })
        ->make(true);
    }

    /** END-- */
}

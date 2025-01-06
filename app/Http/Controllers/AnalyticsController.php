<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use Yajra\Datatables\Facades\Datatables;
use App\utility;


class AnalyticsController extends Controller
{
    public function home_ui(){
        return view('analytics.home');
        
    }

    public function top5_data(Request $request){
        $top_5_varieties = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')
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

    public function check_table($database, $table){
        \Config::set('database.connections.reports_db.database', $database);
        DB::purge('reports_db');

        if (Schema::connection('reports_db')->hasTable($table)) {
            return "table_exists";
        }else{
            return "table_not_found";
        }
    }






    public function area_range_data(Request $request){
        $dop_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province', 'municipality')->get();
        //$dop_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->groupBy('province', 'municipality')->get();
   

        $total_beneficiaries = 0;
            $total_beneficiaries_male = 0;
            $total_beneficiaries_female = 0;
        $total_p5_farmers =0;
            $total_p5_male =0;
            $total_p5_female =0;
        $total_p5_1_farmers=0;
            $total_p5_1_male=0;
            $total_p5_1_female=0;
        $total_1_1p5_farmers=0;
            $total_1_1p5_male=0;
            $total_1_1p5_female=0;
        $total_1p5_2_farmers=0;
            $total_1p5_2_male=0;
            $total_1p5_2_female=0;
        $total_2_2p5_farmers=0;
            $total_2_2p5_male=0;
            $total_2_2p5_female=0;
        $total_2p5_3_farmers=0;
            $total_2p5_3_male=0;
            $total_2p5_3_female=0;
        $total_3_farmers=0;
            $total_3_male=0;
            $total_3_female=0;



       // dd($dop_list);
        foreach($dop_list as $row){
            // $database_name = "rpt_".substr($row->prv,0,4);

            $database_name = $GLOBALS['season_prefix']."rcep_reports_prv_view";
            $table_name    = "prv_".substr($row->prv,0,4)."_group";
            
           // if($check_table_status == "table_exists"){
                $total_beneficiaries += DB::table($database_name.".".$table_name)->count("rcef_id");


                    $total_beneficiaries_male += DB::table($database_name.".".$table_name)->where("sex", "male")->count("id");
                    $total_beneficiaries_female += DB::table($database_name.".".$table_name)->where("sex", "female")->count("id");

                $total_p5_farmers += DB::table($database_name.".".$table_name)->where('actual_area', '<=', 0.5)->count("actual_area");
                    $total_p5_male += DB::table($database_name.".".$table_name)->where('actual_area', '<=', 0.5)->where("sex", "male")->count("actual_area");
                    $total_p5_female += DB::table($database_name.".".$table_name)->where('actual_area', '<=', 0.5)->where("sex", "female")->count("actual_area");
                $total_p5_1_farmers += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 0.5)
                                    ->where('actual_area', '<=', 1)
                                    ->count("actual_area");
                    $total_p5_1_male += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 0.5)
                                    ->where('actual_area', '<=', 1)
                                    ->where("sex", "male")
                                    ->count("actual_area");
                    $total_p5_1_female += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 0.5)
                                    ->where('actual_area', '<=', 1)
                                    ->where("sex", "female")
                                    ->count("actual_area");

                $total_1_1p5_farmers += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 1)
                                    ->where('actual_area', '<=', 1.5)
                                    ->count("actual_area");
                    $total_1_1p5_male += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 1)
                                    ->where('actual_area', '<=', 1.5)
                                    ->where("sex", "male")
                                    ->count("actual_area");
                    $total_1_1p5_female += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 1)
                                    ->where('actual_area', '<=', 1.5)
                                    ->where("sex", "female")
                                    ->count("actual_area");

                $total_1p5_2_farmers += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 1.5)
                                    ->where('actual_area', '<=', 2)
                                    ->count("actual_area");
                    $total_1p5_2_male += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 1.5)
                                    ->where('actual_area', '<=', 2)
                                    ->where("sex", "male")
                                    ->count("actual_area");
                    $total_1p5_2_female += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 1.5)
                                    ->where('actual_area', '<=', 2)
                                    ->where("sex", "female")
                                    ->count("actual_area");
                $total_2_2p5_farmers += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 2)
                                    ->where('actual_area', '<=', 2.5)
                                    ->count("actual_area");
                    $total_2_2p5_male += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 2)
                                    ->where('actual_area', '<=', 2.5)
                                    ->where("sex", "male")
                                    ->count("actual_area");
                    $total_2_2p5_female += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 2)
                                    ->where('actual_area', '<=', 2.5)
                                    ->where("sex", "female")
                                    ->count("actual_area");
                $total_2p5_3_farmers += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 2.5)
                                    ->where('actual_area', '<=', 3)
                                    ->count("actual_area");
                    $total_2p5_3_male += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 2.5)
                                    ->where('actual_area', '<=', 3)
                                    ->where("sex", "male")
                                    ->count("actual_area");
                    $total_2p5_3_female += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 2.5)
                                    ->where('actual_area', '<=', 3)
                                    ->where("sex", "female")
                                    ->count("actual_area");
                $total_3_farmers += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 3)
                                    ->count("actual_area");
                    $total_3_male += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 3)
                                    ->where("sex", "male")
                                    ->count("actual_area");
                    $total_3_female += DB::table($database_name.".".$table_name)
                                    ->where('actual_area', '>', 3)
                                    ->where("sex", "female")
                                    ->count("actual_area");
   
           // }
        


        }
        if($total_beneficiaries == 0){
            $total_p5_farmers_percentage = 0;
            $total_p5_1_farmers_percentage = 0;
            $total_1_1p5_farmers_percentage = 0;
            $total_1p5_2_farmers_percentage = 0;
            $total_2_2p5_farmers_percentage = 0;
            $total_2p5_3_farmers_percentage = 0;
            $total_3_farmers_percentage = 0;
            
        }else{
            $total_p5_farmers_percentage = ($total_p5_farmers / $total_beneficiaries) *  100;
            $total_p5_1_farmers_percentage = ($total_p5_1_farmers / $total_beneficiaries) *  100;
            $total_1_1p5_farmers_percentage = ($total_1_1p5_farmers / $total_beneficiaries) *  100;
            $total_1p5_2_farmers_percentage = ($total_1p5_2_farmers / $total_beneficiaries) *  100;
            $total_2_2p5_farmers_percentage = ($total_2_2p5_farmers / $total_beneficiaries) *  100;
            $total_2p5_3_farmers_percentage = ($total_2p5_3_farmers / $total_beneficiaries) *  100;
            $total_3_farmers_percentage = ($total_3_farmers / $total_beneficiaries) *  100;

        }
    







        $return =  array(
            "total_beneficiaries" => number_format($total_beneficiaries)." (100%)",
                "total_beneficiaries_male" => "Male - ".number_format($total_beneficiaries_male),
                "total_beneficiaries_female" => "Female - ".number_format($total_beneficiaries_female),

            "total_p5_farmers" => number_format($total_p5_farmers)." (".number_format($total_p5_farmers_percentage,"2",".",",")."%)",
            "total_p5_1_farmers" => number_format($total_p5_1_farmers)." (".number_format($total_p5_1_farmers_percentage,"2",".",",")."%)",
            "total_1_1p5_farmers" => number_format($total_1_1p5_farmers)." (".number_format($total_1_1p5_farmers_percentage,"2",".",",")."%)",
            "total_1p5_2_farmers" => number_format($total_1p5_2_farmers)." (".number_format($total_1p5_2_farmers_percentage,"2",".",",")."%)",
            "total_2_2p5_farmers" => number_format($total_2_2p5_farmers)." (".number_format($total_2_2p5_farmers_percentage,"2",".",",")."%)",
            "total_2p5_3_farmers" => number_format($total_2p5_3_farmers)." (".number_format($total_2p5_3_farmers_percentage,"2",".",",")."%)",
            "total_3_farmers" => number_format($total_3_farmers)." (".number_format($total_3_farmers_percentage,"2",".",",")."%)",

             "total_p5_male" => "Male - ".number_format($total_p5_male),
            "total_p5_1_male" => "Male - ".number_format($total_p5_1_male),
            "total_1_1p5_male" => "Male - ".number_format($total_1_1p5_male),
            "total_1p5_2_male" => "Male - ".number_format($total_1p5_2_male),
            "total_2_2p5_male" => "Male - ".number_format($total_2_2p5_male),
            "total_2p5_3_male" => "Male - ".number_format($total_2p5_3_male),
            "total_3_male" => "Male - ".number_format($total_3_male),

             "total_p5_female" => "Female - ".number_format($total_p5_female),
            "total_p5_1_female" => "Female - ".number_format($total_p5_1_female),
            "total_1_1p5_female" => "Female - ".number_format($total_1_1p5_female),
            "total_1p5_2_female" => "Female - ".number_format($total_1p5_2_female),
            "total_2_2p5_female" => "Female - ".number_format($total_2_2p5_female),
            "total_2p5_3_female" => "Female - ".number_format($total_2p5_3_female),
            "total_3_female" => "Female - ".number_format($total_3_female),
        );

       // dd($return);

        return $return;
    }

    public function top_province_data(Request $request){
        $actual_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select('province', DB::raw('SUM(totalBagCount) as total_volume'))
            ->groupBy('province')
            ->orderBy('total_volume', 'DESC')
            ->limit(10)
            ->get();
       
        $provinces_arr = array();
        $volume_arr    = array();
        foreach($actual_data as $row){
            array_push($provinces_arr, $row->province);
            array_push($volume_arr, (int)$row->total_volume);
        }

        return array(
            "provinces" => $provinces_arr,
            "seeds" => $volume_arr
        );
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

    public function execute_reports_variety($prv_db){
        DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')->truncate();
        $drop_off_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->get();
        $prv_list = array();
        foreach($drop_off_list as $dop_row){
                         $prv_list[] = $GLOBALS['season_prefix']."prv_".substr($dop_row->prv_dropoff_id, 0, 4); 
                
        }
        $prv_list = array_unique($prv_list);
       //dd($prv_list);
        //loop through all prv databases
        foreach($prv_list as $prv_database){
       
           
                //check if database has distribution data
                $prv_dist_data = DB::table($prv_database.".released")->first();
                if(count($prv_dist_data) > 0){
                    $variety_res = DB::table($prv_database.".released")
                        ->select(DB::raw('SUM(released.bags) as total_seed_bags'), DB::raw('COUNT(released.release_id) as total_farmers'), 
                            DB::raw('SUM(IF((farmer_profile.sex = "male"), 1, 0)) as total_male'),
                            DB::raw('SUM(IF((farmer_profile.sex = "femal"), 1, 0)) as total_female'),
                            'seed_variety', 'released.province', 'released.municipality', 'farmer_profile.sex')
                        ->leftJoin($prv_database.'.farmer_profile', 'farmer_profile.farmerID', '=', 'released.farmer_id')
                        ->leftJoin($prv_database.'.other_info', 'other_info.farmer_id', '=', 'released.farmer_id')
                        ->groupBy('seed_variety', 'released.municipality')
                        ->get();

                   

                    foreach($variety_res as $v_row){
                       
                        //get region
                        $region_name = DB::connection('delivery_inspection_db')
                            ->table('lib_dropoff_point')
                            ->where('province', $v_row->province)
                            ->groupBy('province')
                            ->value('region');
                             /*
                        $age = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('MIN(other_info.birthdate) as max_age'), DB::raw('MAX(other_info.birthdate) as min_age'),
                                'seed_variety', 'released.province', 'released.municipality', 'other_info.birthdate')
                            ->leftJoin('other_info', 'other_info.farmer_id', '=', 'released.farmer_id')
                            ->where('seed_variety', $v_row->seed_variety)
                            ->where('released.municipality', $v_row->municipality)
                            ->whereRaw('YEAR(birthdate) > "1900" AND YEAR(birthdate) < "2021"')
                            ->groupBy('seed_variety', 'released.municipality')
                            ->get();

                        $currentDate = date("d-m-Y");
                        $min_age = date_diff(date_create($age[0]->min_age), date_create($currentDate));
                        $max_age = date_diff(date_create($age[0]->max_age), date_create($currentDate)); 
                        //save to database per total variety of each prv database
                       

                                  DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 19 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 29, 1, 0)) as  bracket_1'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 30 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 59, 1, 0)) as  bracket_2'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 60, 1, 0)) as  bracket_3')
                               

                        */
                        $min_age = 0;
                        $max_age =0;
                        $bracket_1 = 0;
                        $bracket_2 = 0;
                        $bracket_3 = 0;
                            $age_bracket = DB::table($prv_database.".released")
                            ->select("other_info.birthdate")
                           
                            ->join($prv_database.".other_info", function($join){
                                $join->on("other_info.farmer_id", "=", "released.farmer_id");
                                $join->on("other_info.rsbsa_control_no", "=", "released.rsbsa_control_no");
                            })
                            ->where('seed_variety', $v_row->seed_variety)
                            ->where('released.municipality', $v_row->municipality)
                            ->whereRaw('YEAR(birthdate) > "1900" AND YEAR(birthdate) < "2021"')
                            ->groupBy('seed_variety', 'released.municipality')
                            ->get();

                            foreach ($age_bracket as $va) {
                                $dob=date("Y-m-d", strtotime($va->birthdate)); //date of Birth
                                $condate=date("Y-m-d"); //Certain fix Date of Age 
                                $getAge = $this->getAge($dob,$condate);

                                if($getAge >= 19 || $getAge <= 29){
                                    $bracket_1++;
                                }elseif($getAge >= 30 || $getAge <= 59){
                                    $bracket_2++;
                                }elseif($getAge >= 60){
                                    $bracket_3++;
                                }


                            }



                          

                    
                        DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags,
                            'total_farmers' => $v_row->total_farmers,
                            'total_female' => $v_row->total_female,
                            'total_male' => $v_row->total_male,
                            'min_age' => $min_age,
                            'max_age' => $max_age,
                            'age_bracket_1' =>$bracket_1,
                            'age_bracket_2' =>$bracket_2,
                            'age_bracket_3' =>$bracket_3
                        ]);
                        
                    }
                }
            
        }
    
    }


    public function execute_night_reports_variety(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')->truncate();
        //DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_variety_report')->truncate();
        
        $drop_off_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->get();

        //get all prv(s) in the dropoff point list and getting distinct values
        $prv_list = array();
        foreach($drop_off_list as $dop_row){

                         $prv_list[] = $GLOBALS['season_prefix']."prv_".substr($dop_row->prv_dropoff_id, 0, 4); 
                
        }
        $prv_list = array_unique($prv_list);
       //dd($prv_list);
        //loop through all prv databases
        foreach($prv_list as $prv_database){
       
           
                //check if database has distribution data
                $prv_dist_data = DB::table($prv_database.".released")->first();
                if(count($prv_dist_data) > 0){
                    $variety_res = DB::table($prv_database.".released")
                        ->select(DB::raw('SUM(released.bags) as total_seed_bags'), DB::raw('COUNT(released.release_id) as total_farmers'), 
                            DB::raw('SUM(IF((farmer_profile.sex = "male"), 1, 0)) as total_male'),
                            DB::raw('SUM(IF((farmer_profile.sex = "femal"), 1, 0)) as total_female'),
                            'seed_variety', 'released.province', 'released.municipality', 'farmer_profile.sex')
                        ->leftJoin($prv_database.'.farmer_profile', 'farmer_profile.farmerID', '=', 'released.farmer_id')
                        ->leftJoin($prv_database.'.other_info', 'other_info.farmer_id', '=', 'released.farmer_id')
                        ->groupBy('seed_variety', 'released.municipality')
                        ->get();

                   

                    foreach($variety_res as $v_row){
                       
                        //get region
                        $region_name = DB::connection('delivery_inspection_db')
                            ->table('lib_dropoff_point')
                            ->where('province', $v_row->province)
                            ->groupBy('province')
                            ->value('region');
                             /*
                        $age = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('MIN(other_info.birthdate) as max_age'), DB::raw('MAX(other_info.birthdate) as min_age'),
                                'seed_variety', 'released.province', 'released.municipality', 'other_info.birthdate')
                            ->leftJoin('other_info', 'other_info.farmer_id', '=', 'released.farmer_id')
                            ->where('seed_variety', $v_row->seed_variety)
                            ->where('released.municipality', $v_row->municipality)
                            ->whereRaw('YEAR(birthdate) > "1900" AND YEAR(birthdate) < "2021"')
                            ->groupBy('seed_variety', 'released.municipality')
                            ->get();

                        $currentDate = date("d-m-Y");
                        $min_age = date_diff(date_create($age[0]->min_age), date_create($currentDate));
                        $max_age = date_diff(date_create($age[0]->max_age), date_create($currentDate)); 
                        //save to database per total variety of each prv database
                       

                                  DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 19 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 29, 1, 0)) as  bracket_1'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 30 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 59, 1, 0)) as  bracket_2'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 60, 1, 0)) as  bracket_3')
                               

                        */
                        $min_age = 0;
                        $max_age =0;
                        $bracket_1 = 0;
                        $bracket_2 = 0;
                        $bracket_3 = 0;
                            $age_bracket = DB::table($prv_database.".released")
                            ->select("other_info.birthdate")
                           
                            ->join($prv_database.".other_info", function($join){
                                $join->on("other_info.farmer_id", "=", "released.farmer_id");
                                $join->on("other_info.rsbsa_control_no", "=", "released.rsbsa_control_no");
                            })
                            ->where('seed_variety', $v_row->seed_variety)
                            ->where('released.municipality', $v_row->municipality)
                            ->whereRaw('YEAR(birthdate) > "1900" AND YEAR(birthdate) < "2021"')
                            ->groupBy('seed_variety', 'released.municipality')
                            ->get();

                            foreach ($age_bracket as $va) {
                                $dob=date("Y-m-d", strtotime($va->birthdate)); //date of Birth
                                $condate=date("Y-m-d"); //Certain fix Date of Age 
                                $getAge = $this->getAge($dob,$condate);

                                if($getAge >= 19 || $getAge <= 29){
                                    $bracket_1++;
                                }elseif($getAge >= 30 || $getAge <= 59){
                                    $bracket_2++;
                                }elseif($getAge >= 60){
                                    $bracket_3++;
                                }


                            }



                          

                    
                        DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags,
                            'total_farmers' => $v_row->total_farmers,
                            'total_female' => $v_row->total_female,
                            'total_male' => $v_row->total_male,
                            'min_age' => $min_age,
                            'max_age' => $max_age,
                            'age_bracket_1' =>$bracket_1,
                            'age_bracket_2' =>$bracket_2,
                            'age_bracket_3' =>$bracket_3
                        ]);
                        
                        /*
                        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags,
                            'total_farmers' => $v_row->total_farmers,
                            'total_female' => $v_row->total_female,
                            'total_male' => $v_row->total_male,
                            'min_age' => $min_age->format("%y"),
                            'max_age' => $max_age->format("%y"),
                            'age_bracket_1' =>$bracket_1,
                            'age_bracket_2' =>$bracket_2,
                            'age_bracket_3' =>$bracket_3
                        ]); */
                    }
                }
            
        }
    }

    public function summary_per_variety_data(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')
            ->select('province','municipality','seed_variety', DB::raw('SUM(total_volume) as total_bags'), DB::raw('SUM(total_farmers) as total_farmers'))
            ->groupBy('province')
            ->groupBy('municipality')
            ->groupBy('seed_variety')
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->orderBy('total_bags', 'DESC'))
        ->addColumn('total_bags_text', function($row){
            return number_format($row->total_bags);
        })
        ->addColumn('total_farmers_text', function($row){
            return number_format($row->total_farmers);
        })
        ->addColumn('bags_percentage', function($row){
            $variety_total_bags = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')->select(DB::raw('SUM(total_volume) as total_bags'))->value('total_bags');
          
             if($variety_total_bags > 0){
            	 $equivalent_percentage = ($row->total_bags / $variety_total_bags) * 100;
            }else{
            	 $equivalent_percentage = 0;
            }


            return number_format($equivalent_percentage,2,".",",")."%";
        })
        ->addColumn('area_percentage', function($row){
            $variety_total_farmers = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')->select(DB::raw('SUM(total_farmers) as total_farmers'))->value('total_area');
            if($variety_total_farmers > 0){
            	 $equivalent_percentage = ($row->total_farmers / $variety_total_farmers) * 100;
            }else{
            	 $equivalent_percentage = 0;
            }


           
            return number_format($equivalent_percentage,2,".",",")."%";
        })
        ->make(true);
    }

    public function summary_per_variety_data_chart(){
        $arr_variety = array();
        $arr_bags = array();
        $arr_farmers = array();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.lib_variety_report')
            ->select('seed_variety', DB::raw('SUM(total_volume) as total_bags'), DB::raw('SUM(total_farmers) as total_farmers'))
            ->groupBy('seed_variety')
            ->orderBy('total_bags', 'DESC')
            ->get();

        foreach($data as $row){
            array_push($arr_variety, $row->seed_variety);
            array_push($arr_bags, (int)$row->total_bags);
            array_push($arr_farmers, (int)$row->total_farmers);
        }

        return array(
            "varities" => $arr_variety,
            "bags" => $arr_bags,
            "farmers" => $arr_farmers
        );
    }
}

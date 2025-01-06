<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Config;
use DB;
use Session;
use Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class utility extends Model
{
     function reportReprocess($region,$province,$municipality,$prv){
     	//IS PROCESS = 1 
     	$prv_db = $GLOBALS['season_prefix'].'prv_'.substr($prv, 0, 4);
     	$rpt_db = 'rpt_'.substr($prv, 0, 4);
     	$rpt_tbl = 'tbl_'.$prv;
     	
     	//SET is_process = 1 on released
     	 $update = DB::table($prv_db.'.released')
					->where('municipality', 'like', '%'.$municipality.'%')
					->update(['is_processed'=>1]); 
	
    //    $this->gen_municipality_data($region,$province,$municipality,$prv);
        $this->scheduled_list($region,$province,$municipality,$prv);
		return 'Report Reprocessing for '.$municipality.' is Done';
     }


     function getRegions(){
     	$regional_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
            ->select('region')
            ->orderBy('region','ASC')
            ->groupBy('region')
            ->get();
        return $regional_list;
     }

     function getProvince($region){
     	$province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
            ->select('province')
            ->where('region', $region)
            ->orderBy('province','ASC')
            ->groupBy('province')
            ->get();
        return $province_list;
     }

	function getMunicipality($province){
     	$municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
            ->select('municipality', 'prv')
            ->where('province', $province)
            ->orderBy('municipality','ASC')
            ->groupBy('municipality')
            ->get();
          
        return $municipality_list;
     }



    function scheduled_list($region,$province,$municipality,$prv){
     //dd("execute new reports");
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
      //dd($prv);
        try{
            $province_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->where('province', $province)
            ->where('municipality', $municipality)
            ->groupBy('province')->orderBy('region_sort')->get();
            //dd($province_list);
         
          /*   \Config::set('database.connections.reports_db.host', 'localhost');
            \Config::set('database.connections.reports_db.port', '4409');
            \Config::set('database.connections.reports_db.database', null);
            \Config::set('database.connections.reports_db.username', 'rcef_web');
            \Config::set('database.connections.reports_db.password', 'SKF9wzFtKmNMfwy'); */
   /*
            \Config::set('database.connections.reports_db.host', 'localhost');
            \Config::set('database.connections.reports_db.port', '4406');
            \Config::set('database.connections.reports_db.database', null);
            \Config::set('database.connections.reports_db.username', 'root');
            \Config::set('database.connections.reports_db.password', '');
        */
    
            DB::purge('reports_db');
            
            //dd($province_list);
            foreach($province_list as $row){
                $database_name = "rpt_".substr($row->prv, 0, 4);
                //dd($database_name);
                $prv_database = $GLOBALS['season_prefix']."prv_".substr($row->prv, 0, 4);

                $query = "CREATE DATABASE IF NOT EXISTS $database_name";

                DB::connection('reports_db')->statement($query);
                

               // dd($query);

                $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                    ->where('region', $row->region)
                    ->where('province', $row->province)
                    ->where('municipality', $row->municipality)
                    ->groupBy('province', 'municipality')
                    ->orderBy('region_sort')
                    ->get();

                    //dd($municipalities);
                //loop to all municipalities and generate their respective tables
                foreach($municipalities as $m_row)  {
                    \Config::set('database.connections.reports_db.database', $database_name);
                    DB::purge('reports_db');
                    
                    //dd(DB::connection('mysql')->getPdo());

                    $table_name = "tbl_".$m_row->prv;
                    $primary_key = 'id';

                 //   dd('prv: '.$prv_database.' db: '.$database_name.' table name: '.$table_name.' municipality: '.$m_row->municipality.' province: '.$m_row->province);

                    $fields = [
                        ['name' => 'rsbsa_control_number', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'qr_code', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'farmer_fname', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'farmer_mname', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'farmer_lname', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'farmer_ext', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'sex', 'type' => 'string', 'limit' => '6'],
                        ['name' => 'birthdate', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'tel_number', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'province', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'mother_fname', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'mother_mname', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'mother_lname', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'mother_ext', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'dist_area', 'type' => 'float', 'limit' => '10'],
                        ['name' => 'actual_area', 'type' => 'float', 'limit' => '10'],
                        ['name' => 'bags', 'type' => 'integer', 'limit' => '10'],
                        ['name' => 'seed_variety', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'date_released', 'type' => 'string', 'limit' => '100'],  
                        ['name' => 'farmer_id', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'released_by', 'type' => 'text'],
                        ['name' => 'yield', 'type' => 'float', 'limit' => '10'],
                        ['name' => 'date_generated', 'type' => 'timestamp'],   
                    ];


                    $this->createTable($database_name, $table_name, $fields, $primary_key);
                    //DB::table($database_name.".".$table_name)->truncate();
                
                    //call function to save to tbl_XXXX inside the rpt_prv databases

                    //TRUNCATE REPORT TBL
                   


                    $this->process_municipalities($prv_database, $database_name, $table_name, $m_row->municipality, $m_row->province);
                }

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

    }




public function createTable($database_name, $table_name, $fields = [], $primary_key){   
        
        \Config::set('database.connections.reports_db.database', $database_name);
        DB::purge('reports_db');
        
        // echo $primary_key;die();
        $Pkey = $primary_key;
        // check if table is not already exists
        if (!Schema::connection('reports_db')->hasTable($table_name)) {
            Schema::connection('reports_db')->create($table_name, function (Blueprint $table) use ($fields, $table_name, $primary_key) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';
                $table->increments($primary_key);
                //$table->integer($primary_key)->primary();
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        if($field['type'] == 'integer'){
                            $table->{$field['type']}($field['name'])->unsigned();
                        }elseif($field['type'] == 'timestamp'){
                            $table->{$field['type']}($field['name'])->useCurrent();
                        }elseif(isset($field['limit'])){
                            $table->{$field['type']}($field['name'], $field['limit']);
                        }else{
                            $table->{$field['type']}($field['name']);
                        }
                    }
                }
            });
                 $trunc = DB::table($database_name.'.'.$table_name)->truncate();
            return response()->json(['message' => 'Given table has been successfully created!'], 200);
        }
         $trunc = DB::table($database_name.'.'.$table_name)->truncate();
        return response()->json(['message' => 'Given table is already existis.'], 400);
    }


public function process_municipalities($prv_database, $province_database, $municipality_database, $municipality_name, $province_name){
        //echo "<strong>accessing table: `".$province_database.".".$municipality_database."`  PRV Database: ".$prv_database."</strong><br>";
        $prv_municipality = DB::table($prv_database.".released")->groupBy('municipality')->get();

        //echo "Detected municipality of: ".$row->municipality.", in the province of: ".$row->province." (".$row->prv_dropoff_id.")<br>";
        $farmer_list = DB::table($prv_database.".released")
            ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by', 'released.release_id')
            ->where('released.bags', '!=', '0')
            ->where('released.province', '=', $province_name)
            ->where('released.municipality', '=', $municipality_name)
            ->where('released.is_processed', 1)
            ->orderBy('released.prv_dropoff_id')
            //->limit(1)
            ->get();
      //  dd($farmer_list);
        //echo "[".$row->prv_dropoff_id."] Province (".$row->province."): ".$row->municipality." total farmers: (".count($farmer_list).")<br>";
        /**
         * START PROCESSING
         */
        $daily_farmers = 0;
        $daily_bags = 0;
        $daily_area = 0;
        $daily_male = 0;
        $daily_female = 0;
        $yield = 0;
        foreach ($farmer_list as  $row) {

            //check other_info table
            $other_info_data = DB::table($prv_database.".other_info")
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
            $farmer_profile = DB::table($prv_database.".farmer_profile")
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
                ->where('farmerID', $row->farmer_id)
                ->where('lastName', '!=', '')
                ->where('firstName', '!=', '')
                ->where('distributionID', 'like', 'R%')
                ->orderBy('farmerID', 'DESC')
                ->first();

                if(count($farmer_profile) > 0){
                    $qr_code = $farmer_profile->distributionID;
                    $farmer_fname = $farmer_profile->firstName;
                    $farmer_mname = $farmer_profile->midName;
                    $farmer_lname = $farmer_profile->lastName;
                    $farmer_extname = $farmer_profile->extName;
                    $dist_area = $farmer_profile->area;
                    $actual_area = $farmer_profile->actual_area;
                    $sex = '';
                    $weight = $farmer_profile->weight_per_bag;
                    $no_bags = $farmer_profile->yield;
                    $area = $farmer_profile->area_harvested;

                    if($area >0){
                        $yield = (floatval($no_bags) * floatval($weight)) / floatval($area);
                    }
                    else{
                        $yield = 0;
                    }
                    $yield = $yield / 1000;

                    if($farmer_profile->sex == 'Male'){
                        $sex = 'Male';
                        $daily_male += 1;
                        // $yield = $farmer_profile->yield;					
                    }else if($farmer_profile->sex == 'Femal'){
                        $sex = 'Female';
                        $daily_female += 1; 
                        // $yield = $farmer_profile->yield;
                    }
                }else{
                    $farmer_profile = DB::table($prv_database.".farmer_profile")
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->where('lastName', '!=', '')
                        ->where('firstName', '!=', '')
                        ->orderBy('farmerID', 'DESC')
                        ->first();
                        
                    if(count($farmer_profile) > 0){
                        $qr_code = $farmer_profile->distributionID;
                        $farmer_fname = $farmer_profile->firstName;
                        $farmer_mname = $farmer_profile->midName;
                        $farmer_lname = $farmer_profile->lastName;
                        $farmer_extname = $farmer_profile->extName;
                        $dist_area = $farmer_profile->area;
                        $actual_area = $farmer_profile->actual_area;
                        $sex = '';
                        $weight = $farmer_profile->weight_per_bag;
                        $no_bags = $farmer_profile->yield;
                        $area = $farmer_profile->area_harvested;
            

                        if($area>0){
                            $yield = (floatval($no_bags) * floatval($weight)) / floatval($area);
                            }
                        else{
                            $yield = 0;
                        }
                        


                        //dd($yield);
                        $yield = $yield / 1000;
                        if($farmer_profile->sex == 'Male'){
                            $sex = 'Male';
                            $daily_male += 1;
                            // $yield = $farmer_profile->yield;					
                        }else if($farmer_profile->sex == 'Femal'){
                            $sex = 'Female';
                            $daily_female += 1; 
                            // $yield = $farmer_profile->yield;
                        }
                    }else{
                        $qr_code = "N/A";
                        $farmer_fname = "N/A";
                        $farmer_mname = "N/A";
                        $farmer_lname = "N/A";
                        $farmer_extname = "N/A";
                        $dist_area = 0;
                        $actual_area = 0;
                        $sex = "N/A";
                        
                        $daily_male += 0;
                        $daily_female += 0;
                        $yield += 0;
                    }
                }
                
            //get name of encoder using released.by in sdms_db_dev
            $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
            if(count($encoder_name) > 0){
                if($encoder_name->middleName == ''){
                    $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                }else{
                    $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                }
            }else{
                $encoder_name = '';
            }

            $daily_farmers += 1;
            $daily_bags += $row->bags;
            $daily_area += $actual_area;

            $data = [
                'rsbsa_control_number' => $row->rsbsa_control_no,
                'qr_code' => $qr_code,
                "farmer_fname" => $farmer_fname,
                "farmer_mname" => $farmer_mname,
                "farmer_lname" => $farmer_lname,
                "farmer_ext" => $farmer_extname,
                'sex' => $sex,
                'birthdate' => $birthdate,
                'tel_number' => $phone_number,
                'province' => $row->province,
                'municipality' => $row->municipality,
                "mother_fname" => $mother_fname,
                "mother_mname" => $mother_mname,
                "mother_lname" => $mother_lname,
                "mother_ext" => $mother_suffix,
                'dist_area' => $dist_area,
                'actual_area' => $actual_area,
                'bags' => $row->bags,
                'seed_variety' => $row->seed_variety,
                'date_released' => $row->date_released,
                'farmer_id' => $row->farmer_id,
                'yield' => $yield,
                'released_by' => $encoder_name
            ];
            DB::table("$province_database.$municipality_database")->insert($data);
            //echo "[".$province_database.".".$municipality_database."] Province (".$row->province."): ".$row->municipality.": MYSQL_INSERT RSBSA # - ($row->rsbsa_control_no), Name - ($farmer_fname $farmer_mname $farmer_lname)<br>";

            //after processing to seed beneficiary list DB update is_processed flag to 0
            DB::table($prv_database.'.released')->where('release_id', $row->release_id)->update([
                'is_processed' => 0
            ]);
            DB::commit();
        }
        /**
         * END PROCESSING
         */
        //echo "<strong>End of access table: `".$province_database.".".$municipality_database."`  PRV Database: ".$prv_database."</strong><br><br>";
    
        //return total bags, area, farmers, male, female'
        $region = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province_name)->groupBy('province')->value('region');
        $week_municipal_data = [
            'current_month' => "N/A",
            'current_week' => "N/A",
            "region" => $region,
            "province" => $province_name,
            "municipality" => $municipality_name,
            "total_farmers" => $daily_farmers,
            'total_bags' => $daily_bags,
            'total_dist_area' => 0,
            'total_actual_area' => $daily_area,
            'total_male' => $daily_male,
            'total_female' => $daily_female,
            'date_generated' => date("Y-m-d")
            //'date_generated' => "2020-10-23"
        ];
        DB::table($GLOBALS['season_prefix']."rcep_google_sheets.lib_weekly_municipal")->insert($week_municipal_data);
        DB::commit();
    }




















}

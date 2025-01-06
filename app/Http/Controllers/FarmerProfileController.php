<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use DB;
use Session;
use Excel;

use App\farmerProfile;


class FarmerProfileController extends Controller
{

    public function consolidate_LS($province, $municipality){
        if($municipality == 'all'){
            $municipality = "%";
        }

        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $province)
            ->where("municipality", "LIKE", $municipality)
            ->first();

            if(count($prv)>0){
                $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
            
                $rsbsa_pattern = substr($prv->prv, 0,2)."-".substr($prv->prv, 2,2)."-".substr($prv->prv, 4,2);
                if($municipality == '%'){
                    $rsbsa_pattern = substr($prv->prv, 0,2)."-".substr($prv->prv, 2,2);
                }
               // dd($rsbsa_pattern);
            }else{
                return "NO DATA FOUND";
            }



        $con = $this->changeConnection("ws2021", $prv_db);
            if($con == "success"){
                $ls_farmer_profile = DB::connection("ls_inspection_db")->table("farmer_profile")
                    ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                    ->get();

                if(count($ls_farmer_profile)>0){
                    foreach ($ls_farmer_profile as $key => $value) {
                        $checkDB = DB::table($prv_db.".farmer_profile_processed")
                            ->where("rsbsa_control_no", $value->rsbsa_control_no)
                            ->where("farmerID", $value->farmerID)
                            ->where("firstName", "LIKE", $value->firstName)
                            ->where("lastName", "LIKE", $value->lastName)
                            ->first();

                        if(count($checkDB)>0){
                            continue;
                        }else{
                            $icts_rsbsa = "0";
                            $da_area = "0";


                            $value->id = null;
                            $value->icts_rsbsa = $icts_rsbsa;
                            $value->da_area = $da_area;
                            
                             $con = $this->changeConnection("ws2021", "da_ictd_db");
                                if($con == "success"){
                                    $da_ictd_db = DB::connection("ls_inspection_db")->table("farmer_profile")
                                    ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                    ->where("farmer_first_name", "LIKE", $value->firstName)
                                    ->where("farmer_last_name", "LIKE", $value->lastName)
                                    ->first();
                                    if(count($da_ictd_db)>0){
                                        $value->icts_rsbsa = $da_ictd_db->ffrs_rsbsa_control_no;
                                        $value->da_area = $da_ictd_db->da_area;
                                        $icts_rsbsa = $da_ictd_db->ffrs_rsbsa_control_no;
                                        $da_area = $da_ictd_db->da_area;
                                    }
                                }


                            $data_ins = json_decode(json_encode($value),true);
                            DB::table($prv_db.".farmer_profile_processed")
                            ->insert([$data_ins]);
                            }



                            $con = $this->changeConnection("ws2021", $prv_db);
                            if($con == "success"){
                                $ls_other_info = DB::connection("ls_inspection_db")->table("other_info")
                                    ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                    ->where("farmer_id", $value->farmerID)
                                    ->first();

                                if(count($ls_other_info)>0){
                                        $checkDB = DB::table($prv_db.".other_info_processed")
                                            ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                            ->where("farmer_id", $value->farmerID)
                                            ->first();

                                        if(count($checkDB)>0){
                                            continue;
                                        }else{


                                            $ls_other_info->info_id = null;
                                            $ls_other_info->icts_rsbsa = $icts_rsbsa;
                                            
                                            $data_ins_oth = json_decode(json_encode($ls_other_info),true);
                                            DB::table($prv_db.".other_info_processed")
                                            ->insert([$data_ins_oth]);
                                        }
                                    
                                    
                                }
                            }
                    } //FOREACH
                    return json_encode("Success Posting Data ".$province);
                }else{
                    return json_encode("Empty Profile List");
                }
            }else{
                return json_encode("Connection Failed");
            }

    }











    private function create_farmer_profile_tbl($db){
        $raw = "CREATE TABLE ".$db.".farmer_profile_processed ( `id` int(11) NOT NULL, `farmerID` varchar(255) NOT NULL,`distributionID` varchar(255) NOT NULL,`lastName` varchar(75) NOT NULL,`firstName` varchar(75) NOT NULL,`midName` varchar(75) NOT NULL,`extName` varchar(20) NOT NULL,`fullName` varchar(255) NOT NULL,`sex` varchar(15) NOT NULL,`birthdate` varchar(20) NOT NULL,`region` varchar(75) NOT NULL,`province` varchar(100) NOT NULL, `municipality` varchar(100) NOT NULL,`barangay` varchar(100) NOT NULL,`affiliationType` varchar(75) NOT NULL, `affiliationName` varchar(255) NOT NULL,`affiliationAccreditation` varchar(20) NOT NULL, `isDaAccredited` int(11) NOT NULL, `isLGU` int(11) NOT NULL, `rsbsa_control_no` text NOT NULL, `isNew` int(1) NOT NULL, `send` int(1) NOT NULL, `area` float(5,2) NOT NULL, `area_harvested` double DEFAULT NULL,`actual_area` float(5,2) NOT NULL, `season` varchar(15) NOT NULL, `yield` decimal(8,2) NOT NULL COMMENT 'no of bags', `weight_per_bag` decimal(8,2) NOT NULL, `total_claimable` int(100) NOT NULL, `is_claimed` int(1) NOT NULL, `total_claimed` int(100) NOT NULL, `is_ebinhi` int(1) NOT NULL, `season_inserted` varchar(10) NOT NULL, `update` varchar(100) NOT NULL, `da_area` float(5,2) NOT NULL, `icts_rsbsa` text NOT NULL  )  ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            DB::select(DB::raw($raw));
            $raw = "ALTER TABLE ".$db.".farmer_profile_processed ADD PRIMARY KEY (`id`);";
            DB::select(DB::raw($raw));
            $raw = "ALTER TABLE ".$db.".farmer_profile_processed MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"; 
            DB::select(DB::raw($raw));

            return true;
    }


    private function create_other_info_tbl($db){
          $raw = "CREATE TABLE ".$db.".other_info_processed (
                                  `info_id` int(11) NOT NULL,
                                  `farmer_id` varchar(100) NOT NULL,
                                  `rsbsa_control_no` varchar(100) NOT NULL,
                                  `mother_fname` varchar(200) NOT NULL,
                                  `mother_mname` varchar(200) NOT NULL,
                                  `mother_lname` varchar(200) NOT NULL,
                                  `mother_suffix` varchar(100) NOT NULL,
                                  `birthdate` date NOT NULL,
                                  `is_representative` int(11) NOT NULL,
                                  `id_type` varchar(100) NOT NULL,
                                  `relationship` varchar(100) NOT NULL,
                                  `have_pic` int(11) NOT NULL,
                                  `phone` varchar(100) NOT NULL,
                                  `send` int(1) NOT NULL,
                                  `municipality` varchar(255) NOT NULL,
                                  `representative_name` varchar(200) NOT NULL, 
                                  `icts_rsbsa` text NOT NULL 

                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
            DB::select(DB::raw($raw));
            $raw = "ALTER TABLE ".$db.".other_info_processed ADD PRIMARY KEY (`info_id`);";
            DB::select(DB::raw($raw));
            $raw = "ALTER TABLE ".$db.".other_info_processed MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT;"; 
            DB::select(DB::raw($raw));

            return true;

    }

    



    public function insert_cross_check_xls($file){
 
            $da_path = base_path('public/excel_template/'.$file.'.csv');
            //dd($da_path);
            $data_array = array_map('str_getcsv', file($da_path));
          //  dd($data_array);
           $i = 0;
           // unset($data_array[0]);

            foreach ($data_array as $key => $data) { 
                
                if(count($data)<24){
                    continue;
                }     

                 $rsbsa =  $data[0];
                 $qr_code =  $data[1];
                 $firstName = $data[2];
                 $midName = $data[3];
                 $lastName =  $data[4];
                 $extName =  $data[5];
                 $sex = $data[6];
                 $birthdate =  $data[7];
                 $phone =  $data[8];
                 $province =  $data[9];
                 $municipality =  $data[10];
                 $mother_fname = $data[11];
                 $mother_mname =  $data[12];
                 $mother_lname =  $data[13];
                 $mother_ext = $data[14];
                 $actual_area =  $data[15];
                 $bags_claimed =  $data[16];
                 $seed_variety =  $data[17];
                 $yield =  $data[18];
                 $date_released =  $data[19];
                 $farmer_id =  $data[20];
                 $released_by =  $data[21];
                 $icts_rsbsa =  $data[23];
                 $da_area =  $data[22];


            if($da_area > 0){
                $area = $da_area;
            }else{
                $area = $actual_area;    
            }

                $actual_area = $area;

            if($area != floor($area)){
                $dec =  $area - floor($area); 

                if($dec <= 0.5 ){
                $area = floor($area) + 0.5;
                }else{
                    $area = floor($area) + 1;
                }
            }
            $total_claimable = $area * 2;


                
                 $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("province", $province)->where("municipality", $municipality)->first();
                 if(count($prv)>0){
                    $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_profile_processed")
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      $tbl_fp =  $this->create_farmer_profile_tbl($prv_db);
                    }else{
                        $tbl_fp = true;
                    }
                    

                    if($tbl_fp){
                         $farmer_profile_arr = array(
                            "farmerID" => $farmer_id,
                            "distributionID" => $qr_code,
                            "lastName"=> $lastName,
                            "firstName" => $firstName,
                            "midName" => $midName,
                            "extName" =>$extName,
                            "fullName" =>$firstName." ".$midName." ".$lastName." ".$extName, 
                            "sex" =>$sex,
                            "birthdate" =>$birthdate,
                            "region" => $prv->regionName,
                            "province" =>$province,
                            "municipality" =>$municipality,
                            "barangay" =>"",
                            "affiliationType" =>"",
                            "affiliationName" =>"",
                            "affiliationAccreditation" =>"",
                            "isDaAccredited" =>"0",
                            "isLGU" =>"0",
                            "rsbsa_control_no" => $rsbsa,
                            "isNew" => "0",
                            "area" => $actual_area,
                            "area_harvested" => "",
                            "actual_area" =>$actual_area,
                            "season" =>"",
                            "yield" =>"",
                            "weight_per_bag" => "",
                            "total_claimable" => $total_claimable,
                            "is_claimed" => "1",
                            "total_claimed" => $bags_claimed,
                            "is_ebinhi" =>"",
                            "season_inserted" =>"-",
                            "update" => "0",
                            "da_area" => $da_area,
                            "icts_rsbsa" => $icts_rsbsa,
                         );



                         $check_fp = DB::table($prv_db.".farmer_profile_processed")
                                ->where("icts_rsbsa", $icts_rsbsa)
                                ->where("da_area", $da_area)
                                ->where("rsbsa_control_no", $rsbsa)
                                ->where("farmerID", $farmer_id)
                                ->where("firstName", $firstName)
                                ->where("lastName", $lastName)
                                ->first();

                        if(count($check_fp)>0){
                            continue;
                        }else{
                            DB::table($prv_db.".farmer_profile_processed")->insert($farmer_profile_arr);
                        }

                    }
                     $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "other_info_processed")
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      $tbl_oi =  $this->create_other_info_tbl($prv_db);
                    }else{
                      $tbl_oi = true;
                    }


                    if($tbl_oi){
                          $other_info_arr = array(
                            "farmer_id" => $farmer_id,
                            "rsbsa_control_no" => $rsbsa,
                            "mother_fname" => $mother_fname,
                            "mother_mname" => $mother_mname,
                            "mother_lname" => $mother_lname,
                            "mother_suffix" => $mother_ext,
                            "birthdate" => $birthdate,
                            "is_representative" => "0",
                            "id_type" => "",
                            "relationship" => "",
                            "have_pic" => "0",
                            "phone" => $phone,
                            "send" => "0",
                            "municipality" => "",
                            "representative_name" => "",
                            "icts_rsbsa" => $icts_rsbsa,
                         );


                        $check_oth = DB::table($prv_db.".other_info_processed")
                                ->where("icts_rsbsa", $icts_rsbsa)
                                ->where("rsbsa_control_no", $rsbsa)
                                ->where("farmer_id", $farmer_id)
                                ->where("mother_fname", $mother_fname)
                                ->where("mother_lname", $mother_lname)
                                ->first();

                        if(count($check_oth)>0){
                            continue;
                        }else{
                            DB::table($prv_db.".other_info_processed")->insert($other_info_arr);
                        }

                        
                    }


                    $i++;
                 }
            }
            return json_encode($i." ROWS INSERTED");
     
    }









     private function changeConnection($season,$database_name){
            $conn_string = array();

        $conn_string['ds2020']['host'] = "localhost";
        $conn_string['ds2020']['port'] = "3306";
        $conn_string['ds2020']['user'] = "jpalileo";
        $conn_string['ds2020']['password'] = "P@ssw0rd";


        $conn_string['ws2020']['host'] = "localhost";
        $conn_string['ws2020']['port'] = "4406";
        $conn_string['ws2020']['user'] = "rcef_user";
        $conn_string['ws2020']['password'] = "SKF9wzFtKmNMfwyz";

        $conn_string['ds2021']['host'] = "192.168.10.23";
        $conn_string['ds2021']['port'] = "3306";
        $conn_string['ds2021']['user'] = "rcef_web";
        $conn_string['ds2021']['password'] = "SKF9wzFtKmNMfwy";
        
        $conn_string['ws2021']['host'] = "localhost";
        $conn_string['ws2021']['port'] = "4409";
        $conn_string['ws2021']['user'] = "rcef_web";
        $conn_string['ws2021']['password'] = "SKF9wzFtKmNMfwy";

            try{
                \Config::set('database.connections.ls_inspection_db.host', $conn_string[$season]['host']);
                \Config::set('database.connections.ls_inspection_db.port', $conn_string[$season]['port']);
                \Config::set('database.connections.ls_inspection_db.database', $database_name);
                \Config::set('database.connections.ls_inspection_db.username', $conn_string[$season]['user']);
                \Config::set('database.connections.ls_inspection_db.password', $conn_string[$season]['password']);
                DB::purge('ls_inspection_db');
                DB::connection('ls_inspection_db')->getPdo();
            
                return "success";
            } catch (\Exception $e) {
                return "failed";        
            }
    }

    private function get_da_data($rsbsa_control_no, $first_name, $last_name){
         $con = $this->changeConnection("ws2021", "da_ictd_db");
            if($con == "success"){
                $da_data = DB::connection("ls_inspection_db")->table("farmer_profile")
                    ->select("da_area", "ffrs_rsbsa_control_no")
                    ->where("rsbsa_control_no", $rsbsa_control_no)
                    ->where("farmer_last_name", $last_name)
                    ->where("farmer_first_name", $first_name)
                    ->first();

                if(count($da_data)>0){
                    return array(
                        "da_area" => $da_data->da_area,
                        "icts_rsbsa" => $da_data->ffrs_rsbsa_control_no 
                    );
                }else{
                    return array(
                        "da_area" => 0,
                        "icts_rsbsa" => 0 
                    );
                }
            }
            else{
                return array(
                        "da_area" => 0,
                        "icts_rsbsa" => 0 
                    );
            }
    }



    public function farmerProfileDataPreparation($region,$province, $municipality){
        //dd($province." ".$municipality);
            if($province == "all")$province="%";
            if($municipality== "all")$municipality="%";

            $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("province", "LIKE", $province)
                ->where("municipality", "LIKE", $municipality)
                ->where("regionName", $region)
                ->groupBy("province")
                ->get();
                $i = 0;
            foreach ($lib_prv as $key => $prv) {
                $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
               // dd($db);
                $rsbsa_reference = substr($prv->prv,0,2)."-".substr($prv->prv,2,2)."-".substr($prv->prv,4,2);
                $profile = array();

                //CREATE TABLE
                $process_tbl =  DB::table("information_schema.TABLES")
                                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                                            ->where("TABLE_SCHEMA", $db)
                                            ->where("TABLE_NAME", "farmer_profile_processed")
                                            ->groupBy("TABLE_NAME")
                                            ->first();

                if(count($process_tbl)<=0){
                        
                            $raw = "CREATE TABLE ".$db.".farmer_profile_processed ( `id` int(11) NOT NULL, `farmerID` varchar(255) NOT NULL,`distributionID` varchar(255) NOT NULL,`lastName` varchar(75) NOT NULL,`firstName` varchar(75) NOT NULL,`midName` varchar(75) NOT NULL,`extName` varchar(20) NOT NULL,`fullName` varchar(255) NOT NULL,`sex` varchar(15) NOT NULL,`birthdate` varchar(20) NOT NULL,`region` varchar(75) NOT NULL,`province` varchar(100) NOT NULL, `municipality` varchar(100) NOT NULL,`barangay` varchar(100) NOT NULL,`affiliationType` varchar(75) NOT NULL, `affiliationName` varchar(255) NOT NULL,`affiliationAccreditation` varchar(20) NOT NULL, `isDaAccredited` int(11) NOT NULL, `isLGU` int(11) NOT NULL, `rsbsa_control_no` text NOT NULL, `isNew` int(1) NOT NULL, `send` int(1) NOT NULL, `area` float(5,2) NOT NULL, `area_harvested` double DEFAULT NULL,`actual_area` float(5,2) NOT NULL, `season` varchar(15) NOT NULL, `yield` decimal(8,2) NOT NULL COMMENT 'no of bags', `weight_per_bag` decimal(8,2) NOT NULL, `total_claimable` int(100) NOT NULL, `is_claimed` int(1) NOT NULL, `total_claimed` int(100) NOT NULL, `is_ebinhi` int(1) NOT NULL, `season_inserted` varchar(10) NOT NULL, `update` varchar(100) NOT NULL, `da_area` float(5,2) NOT NULL, `icts_rsbsa` text NOT NULL  )  ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".farmer_profile_processed ADD PRIMARY KEY (`id`);";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".farmer_profile_processed MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"; 
                            DB::select(DB::raw($raw));
                        
                }else{

                    //DB::table($db.".farmer_profile_processed")->truncate();
                    DB::select(DB::raw( "DROP TABLE ".$db.".farmer_profile_processed"));

                     $raw = "CREATE TABLE ".$db.".farmer_profile_processed ( `id` int(11) NOT NULL, `farmerID` varchar(255) NOT NULL,`distributionID` varchar(255) NOT NULL,`lastName` varchar(75) NOT NULL,`firstName` varchar(75) NOT NULL,`midName` varchar(75) NOT NULL,`extName` varchar(20) NOT NULL,`fullName` varchar(255) NOT NULL,`sex` varchar(15) NOT NULL,`birthdate` varchar(20) NOT NULL,`region` varchar(75) NOT NULL,`province` varchar(100) NOT NULL, `municipality` varchar(100) NOT NULL,`barangay` varchar(100) NOT NULL,`affiliationType` varchar(75) NOT NULL, `affiliationName` varchar(255) NOT NULL,`affiliationAccreditation` varchar(20) NOT NULL, `isDaAccredited` int(11) NOT NULL, `isLGU` int(11) NOT NULL, `rsbsa_control_no` text NOT NULL, `isNew` int(1) NOT NULL, `send` int(1) NOT NULL, `area` float(5,2) NOT NULL, `area_harvested` double DEFAULT NULL,`actual_area` float(5,2) NOT NULL, `season` varchar(15) NOT NULL, `yield` decimal(8,2) NOT NULL COMMENT 'no of bags', `weight_per_bag` decimal(8,2) NOT NULL, `total_claimable` int(100) NOT NULL, `is_claimed` int(1) NOT NULL, `total_claimed` int(100) NOT NULL, `is_ebinhi` int(1) NOT NULL, `season_inserted` varchar(10) NOT NULL, `update` varchar(100) NOT NULL, `da_area` float(5,2) NOT NULL, `icts_rsbsa` text NOT NULL  )  ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".farmer_profile_processed ADD PRIMARY KEY (`id`);";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".farmer_profile_processed MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"; 
                            DB::select(DB::raw($raw));

                

              
                }




                 $process_tbl =  DB::table("information_schema.TABLES")
                                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                                            ->where("TABLE_SCHEMA", $db)
                                            ->where("TABLE_NAME", "other_info_processed")
                                            ->groupBy("TABLE_NAME")
                                            ->first();

                if(count($process_tbl)<=0){

                            $raw = "CREATE TABLE ".$db.".other_info_processed (
                                  `info_id` int(11) NOT NULL,
                                  `farmer_id` varchar(100) NOT NULL,
                                  `rsbsa_control_no` varchar(100) NOT NULL,
                                  `mother_fname` varchar(200) NOT NULL,
                                  `mother_mname` varchar(200) NOT NULL,
                                  `mother_lname` varchar(200) NOT NULL,
                                  `mother_suffix` varchar(100) NOT NULL,
                                  `birthdate` date NOT NULL,
                                  `is_representative` int(11) NOT NULL,
                                  `id_type` varchar(100) NOT NULL,
                                  `relationship` varchar(100) NOT NULL,
                                  `have_pic` int(11) NOT NULL,
                                  `phone` varchar(100) NOT NULL,
                                  `send` int(1) NOT NULL,
                                  `municipality` varchar(255) NOT NULL,
                                  `representative_name` varchar(200) NOT NULL, 
                                  `icts_rsbsa` text NOT NULL 

                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".other_info_processed ADD PRIMARY KEY (`info_id`);";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".other_info_processed MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT;"; 
                            DB::select(DB::raw($raw));


                        
                }else{
                    DB::select(DB::raw( "DROP TABLE ".$db.".other_info_processed"));
                     $raw = "CREATE TABLE ".$db.".other_info_processed (
                                  `info_id` int(11) NOT NULL,
                                  `farmer_id` varchar(100) NOT NULL,
                                  `rsbsa_control_no` varchar(100) NOT NULL,
                                  `mother_fname` varchar(200) NOT NULL,
                                  `mother_mname` varchar(200) NOT NULL,
                                  `mother_lname` varchar(200) NOT NULL,
                                  `mother_suffix` varchar(100) NOT NULL,
                                  `birthdate` date NOT NULL,
                                  `is_representative` int(11) NOT NULL,
                                  `id_type` varchar(100) NOT NULL,
                                  `relationship` varchar(100) NOT NULL,
                                  `have_pic` int(11) NOT NULL,
                                  `phone` varchar(100) NOT NULL,
                                  `send` int(1) NOT NULL,
                                  `municipality` varchar(255) NOT NULL,
                                  `representative_name` varchar(200) NOT NULL, 
                                  `icts_rsbsa` text NOT NULL 

                                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".other_info_processed ADD PRIMARY KEY (`info_id`);";
                            DB::select(DB::raw($raw));
                            $raw = "ALTER TABLE ".$db.".other_info_processed MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT;"; 
                            DB::select(DB::raw($raw));



                }



                //DS2020
                $con = $this->changeConnection("ds2020", "information_schema");
                if($con == "success"){
                    $schema = DB::connection("ls_inspection_db")->table("TABLES")
                        ->where("TABLE_SCHEMA", "ds2020_".$db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->first();
                    if(count($schema)>0){
                        $con_profile = $this->changeConnection("ds2020", "ds2020_".$db);                    
                            if($con_profile == "success"){
                                $farmer_profile = DB::connection("ls_inspection_db")->table("farmer_profile")
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->where('distributionID', 'like', 'R%')
                                    ->orderBy('id')
                                    ->get();
                                    if(count($farmer_profile)>0){
                                        foreach ($farmer_profile as $key => $value) {
                                         


                                            $value->total_claimable = 4;
                                            $con_profile = $this->changeConnection("ds2020", "ds2020_".$db);    
                                            $released = DB::connection('ls_inspection_db')->table('released')
                                                ->where("farmer_id", $value->farmerID)
                                                //->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->sum("bags");                      
                                            if(intval($released) >0){
                                                $value->is_claimed = 1;
                                                $value->total_claimed = intval($released);
                                            }else{
                                                $value->is_claimed = 0;
                                                $value->total_claimed = 0;
                                            } 


                                            $da_data = $this->get_da_data($value->rsbsa_control_no, $value->firstName, $value->lastName);

                                            
                                            $value->da_area = $da_data['da_area'];
                                            $value->icts_rsbsa = $da_data['icts_rsbsa'];

                                         
                                              $value->is_ebinhi = 0;
                                               $value->season_inserted = "ds2020";  
                                               $other_info_arr = array(
                                                "farmer_id" => $value->farmerID,
                                                "rsbsa_control_no" => $value->rsbsa_control_no,
                                                "icts_rsbsa" => $value->icts_rsbsa
                                                );
                                                 unset($value->id);
                                                $insert_value =  json_decode(json_encode($value),true);
                                            DB::table($db.".farmer_profile_processed")->insert($insert_value);
                                            DB::table($db.".other_info_processed")->insert($other_info_arr);
                                        } //LOOP
                                    } //FARMER PROFILE
                            } //CONN
                    } //SCHEMA
                } //CONN



                //WS2020
                $con = $this->changeConnection("ws2020", "information_schema");
                if($con == "success"){
                    $schema = DB::connection("ls_inspection_db")->table("TABLES")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->first();
                    if(count($schema)>0){
                        $con_profile = $this->changeConnection("ws2020", $db);                    
                            if($con_profile == "success"){
                                $farmer_profile = DB::connection("ls_inspection_db")->table("farmer_profile")
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->where('distributionID', 'like', 'R%')
                                    ->orWhere('actual_area', '>', 0)
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->orderBy('id')
                                    ->get();
                                    if(count($farmer_profile)>0){
                                        foreach ($farmer_profile as $key => $value) {
                                            $check = 0;
                                              $check = $this->checkIfFarmerExist($db, $value->rsbsa_control_no, $value->farmerID, $value->firstName);
                                              if($check == 1){
                                                $i++;
                                                continue;
                                              }

                                            $area = $value->actual_area;
                                            if($area != floor($area)){
                                                $dec =  $area - floor($area); 
                                                if($dec <= 0.5 ){
                                                $area = floor($area) + 0.5;
                                                }else{
                                                    $area = floor($area) + 1;
                                                }
                                            } 
                                            
                                            if($area = 0)$area = 3;
                                            $value->total_claimable = $area * 2;
                                            $con_profile = $this->changeConnection("ws2020", $db);
                                            $released = DB::connection('ls_inspection_db')->table('released')
                                                ->where("farmer_id", $value->farmerID)
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->sum("bags");                  

                                            if(intval($released) >0){
                                                $value->is_claimed = 1;
                                                $value->total_claimed = intval($released);
                                            }else{
                                                $value->is_claimed = 0;
                                                $value->total_claimed = 0;
                                            } 

                                            $da_data = $this->get_da_data($value->rsbsa_control_no, $value->firstName, $value->lastName);

                                                $value->da_area = $da_data['da_area'];
                                                $value->icts_rsbsa = $da_data['icts_rsbsa'];
                                            
                                               $value->is_ebinhi = 0;
                                               $value->season_inserted = "ws2020";   


                                               $con_profile = $this->changeConnection("ws2020", $db);
                                               $other_info = DB::connection('ls_inspection_db')->table('other_info')
                                                ->where("farmer_id", $value->farmerID)
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->first(); 
                                                if(count($other_info)>0){
                                                    $other_info->icts_rsbsa = $da_data['icts_rsbsa'];
                                                    unset($other_info->info_id); 
                                                    $other_info_value =  json_decode(json_encode($other_info),true);
                                                    DB::table($db.".other_info_processed")->insert($other_info_value);
                                                }



                                                   
                                                unset($value->id);  
                                                $insert_value =  json_decode(json_encode($value),true);

                                           //   dd($insert_value);
                                            DB::table($db.".farmer_profile_processed")->insert($insert_value);
                                           
                                        } //LOOP
                                    } //FARMER PROFILE
                            } //CONN
                    } //SCHEMA
                } //CONN


                //DS2021
                $con = $this->changeConnection("ds2021", "information_schema");
                if($con == "success"){
                    $schema = DB::connection("ls_inspection_db")->table("TABLES")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->first();
                    if(count($schema)>0){
                        $con_profile = $this->changeConnection("ds2021", $db);                    
                            if($con_profile == "success"){
                                $farmer_profile = DB::connection("ls_inspection_db")->table("farmer_profile")
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->where('distributionID', 'like', 'R%')
                                    ->orWhere('actual_area', '>', 0)
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->orderBy('id')
                                    ->get();
                                    if(count($farmer_profile)>0){
                                        foreach ($farmer_profile as $key => $value) {
                                            $check = 0;
                                              $check = $this->checkIfFarmerExist($db, $value->rsbsa_control_no, $value->farmerID, $value->firstName);
                                              if($check == 1){
                                                $i++;
                                                continue;
                                              }

                                            $area = $value->actual_area;
                                            if($area != floor($area)){
                                                $dec =  $area - floor($area); 
                                                if($dec <= 0.5 ){
                                                $area = floor($area) + 0.5;
                                                }else{
                                                    $area = floor($area) + 1;
                                                }
                                            } 
                                            
                                            $value->total_claimable = $area * 2;
                                             $con_profile = $this->changeConnection("ds2021", $db);
                                            $released = DB::connection('ls_inspection_db')->table('released')
                                                ->where("farmer_id", $value->farmerID)
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->sum("bags");                  

                                            if(intval($released) >0){
                                                $value->is_claimed = 1;
                                                $value->total_claimed = intval($released);
                                            }else{
                                                $value->is_claimed = 0;
                                                $value->total_claimed = 0;
                                            } 

                                            $da_data = $this->get_da_data($value->rsbsa_control_no, $value->firstName, $value->lastName);

                                            $value->da_area = $da_data['da_area'];
                                            $value->icts_rsbsa = $da_data['icts_rsbsa'];

                                            $con_profile = $this->changeConnection("ds2021", $GLOBALS['season_prefix'].'rcep_paymaya');
                                            $ebinhi = DB::connection('ls_inspection_db')->table('tbl_claim')
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->where("fullName", 'LIKE', '%'.$value->firstName.'%')
                                                ->where("fullName", 'LIKE', '%'.$value->lastName.'%')
                                                ->first();                      

                                                if(count($ebinhi)>0){
                                                    $value->is_ebinhi = 1;
                                                }else{
                                                    $value->is_ebinhi = 0;
                                                }   

                                            $con_profile = $this->changeConnection("ds2021", $db);
                                               $other_info = DB::connection('ls_inspection_db')->table('other_info')
                                                ->where("farmer_id", $value->farmerID)
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->first(); 
                                                if(count($other_info)>0){
                                                    $other_info->icts_rsbsa = $da_data['icts_rsbsa'];
                                                    unset($other_info->info_id); 
                                                    $other_info_value =  json_decode(json_encode($other_info),true);
                                                    DB::table($db.".other_info_processed")->insert($other_info_value);
                                                }

                                                unset($value->id);
                                             
                                            
                                            $value->season_inserted = "ds2021";     
                                                $insert_value =  json_decode(json_encode($value),true);

                                           //   dd($insert_value);
                                            DB::table($db.".farmer_profile_processed")->insert($insert_value);
                                        } //LOOP
                                    } //FARMER PROFILE
                            } //CONN
                    } //SCHEMA
                } //CONN





                //WS2021
                $con = $this->changeConnection("ws2021", "information_schema");
                if($con == "success"){
                    $schema = DB::connection("ls_inspection_db")->table("TABLES")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->first();
                    if(count($schema)>0){
                        $con_profile = $this->changeConnection("ws2021", $db);                    
                            if($con_profile == "success"){
                                $farmer_profile = DB::connection("ls_inspection_db")->table("farmer_profile")
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->where('distributionID', 'like', 'R%')
                                    ->orWhere('actual_area', '>', 0)
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
                                    ->orderBy('id')
                                    ->get();
                                 
                                    if(count($farmer_profile)>0){
                                        foreach ($farmer_profile as $key => $value) {
                                                $check = 0;
                                              $check = $this->checkIfFarmerExist($db, $value->rsbsa_control_no, $value->farmerID, $value->firstName);
                                              if($check == 1){
                                                $i++;
                                                continue;

                                              }

                                            $area = $value->actual_area;
                                            if($area != floor($area)){
                                                $dec =  $area - floor($area); 
                                                if($dec <= 0.5 ){
                                                $area = floor($area) + 0.5;
                                                }else{
                                                    $area = floor($area) + 1;
                                                }
                                            } 
                                            
                                            $value->total_claimable = $area * 2;
                                             $con_profile = $this->changeConnection("ws2021", $db);
                                            $released = DB::connection("ls_inspection_db")->table('released')
                                                ->where("farmer_id", $value->farmerID)
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->sum("bags");                  

                                            if(intval($released) >0){
                                                $value->is_claimed = 1;
                                                $value->total_claimed = intval($released);
                                            }else{
                                                $value->is_claimed = 0;
                                                $value->total_claimed = 0;
                                            } 


                                            $da_data = $this->get_da_data($value->rsbsa_control_no, $value->firstName, $value->lastName);
                                            $value->da_area = $da_data['da_area'];
                                            $value->icts_rsbsa = $da_data['icts_rsbsa'];

                                            $con_profile = $this->changeConnection("ws2021", $GLOBALS['season_prefix'].'rcep_paymaya');
                                            $ebinhi = DB::connection('ls_inspection_db')->table('tbl_claim')
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->where("fullName", 'LIKE', '%'.$value->firstName.'%')
                                                ->where("fullName", 'LIKE', '%'.$value->lastName.'%')
                                                ->first();                      

                                                if(count($ebinhi)>0){
                                                    $value->is_ebinhi = 1;
                                                }else{
                                                    $value->is_ebinhi = 0;
                                                }   

                                                $con_profile = $this->changeConnection("ws2021", $db);
                                               $other_info = DB::connection('ls_inspection_db')->table('other_info')
                                                ->where("farmer_id", $value->farmerID)
                                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                                ->first(); 
                                                if(count($other_info)>0){
                                                    $other_info->icts_rsbsa = $da_data['icts_rsbsa'];
                                                    unset($other_info->info_id); 
                                                    $other_info_value =  json_decode(json_encode($other_info),true);
                                                    DB::table($db.".other_info_processed")->insert($other_info_value);
                                                }





                                                unset($value->id);
                                                
                                            
                                            $value->season_inserted = "ws2021";     
                                                $insert_value =  json_decode(json_encode($value),true);

                                           //   dd($insert_value);
                                            DB::table($db.".farmer_profile_processed")->insert($insert_value);
                                        } //LOOP
                                    } //FARMER PROFILE
                            } //CONN
                    } //SCHEMA
                } //CONN
            }
            return json_encode("Processed Done");

    }





    private function checkIfFarmerExist($database, $rsbsa, $farmer_id, $first_name){
        $processed_list = DB::table($database.".farmer_profile_processed")
            ->where("rsbsa_control_no", $rsbsa)
            ->where("farmerID", $farmer_id)
            ->where("firstName", "LIKE", $first_name)
            ->first();
        if(count($processed_list)>0){
            return 1;
        }else{
            return 0;
        }
    }


















    
     public function releasedToVerified($region,$province,$municipality){
        $farmerProfile = new farmerProfile;

        if($municipality == "all"){
            $municipality = "%";
        }
        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("regionName", $region)
            ->where("province", $province)
            ->where("municipality", "like",$municipality)
            ->groupBy("municipality")
            ->orderBy("prv", "ASC")
            ->get();

        if(count($list)>0){
            foreach ($list as $list) {
                $count = $farmerProfile->add_released_data($list->prv,$list->regionName,$list->province,$list->municipality); 
            } 
        }
        return json_encode("ADDED LIST FROM RELEASED: ". $count."MUNICIPAL: ".$municipality);
    }

    public function farmerVerifiedList($region,$province){
        $farmerProfile = new farmerProfile;

        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            //->where("regionName", "CENTRAL LUZON")
            //->where("regionName","LIKE", "%SOCCSKSARGEN%")
            //->orWhere("regionName","LIKE", "%CAR%")
            //->orWhere("regionName","LIKE", "%BARMM%")
            //->orWhere("regionName","LIKE", "%CARAGA%")
            //->orWhere("regionName","LIKE", "%MIMAROPA%")
            ->where("regionName", $region)
            ->where("province", $province)
            ->groupBy("province")
            ->orderBy("prv", "ASC")
            ->get();

          // dd($list);
            $i = 0;
            foreach ($list as $province) {
                $farmerProfile->makeTable_gen_data($province->regionName,$province->province,$province->municipality,$province->prv);
                $i++;

                $ret_pro = $province->province;
            }

        return json_encode("TOTAL:".$i." Provinces ".$ret_pro);

    }


    public function nationWideStatPerRegion($region){
             DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->truncate();
            $municipalList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("regionName", $region)
                   // ->where("munCode", "LIKE", "%".$municipality."%")
                    ->orderBy("region_sort", "ASC")
                    ->groupBy("municipality")
                    ->get();
            $data_arr = array();
            foreach ($municipalList as $municipality_arr) {
                $regionCode = $municipality_arr->regCode;
                $rsbsa_pattern = $municipality_arr->regCode."-".$municipality_arr->provCode.'-'.$municipality_arr->munCode;
                $prv = $municipality_arr->regCode.$municipality_arr->provCode;

                    $checkDB = DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA", "prv_".$prv)
                    ->where("TABLE_NAME", "farmer_profile")
                    ->first();    

                    
                    if(count($checkDB)>0){

                    $total = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                            ->where("distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->count();   

                    $withContact = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                        ->select(DB::raw('COUNT(farmer_profile.distributionID) as total_farmers'), 
                            DB::raw('SUM(IF((farmer_profile.sex = "male"), 1, 0)) as total_male'),
                            DB::raw('SUM(IF((farmer_profile.sex = "femal"), 1, 0)) as total_female')
                        )
                        ->join($GLOBALS['season_prefix']."prv_".$prv.'.other_info', function($join){
                            $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                            $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                        })
                        ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","09%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","63%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->first();

                        $age = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                            ->select(DB::raw('MIN(other_info.birthdate) as max_age'), DB::raw('MAX(other_info.birthdate) as min_age'))
                            ->join($GLOBALS['season_prefix']."prv_".$prv.'.other_info', function($join){
                                $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                                $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                            })
                            ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","09%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","63%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->whereRaw('YEAR(other_info.birthdate) > "1900" AND YEAR(other_info.birthdate) < "2021"')
                            ->first();
                    
                            $currentDate = date("d-m-Y");
                            $min_age = date_diff(date_create($age->min_age), date_create($currentDate));
                            $max_age = date_diff(date_create($age->max_age), date_create($currentDate));
                    



//DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), '%Y')+0 AS age;

                        $age_bracket = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                            ->select(
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 19 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 29, 1, 0)) as  bracket_1'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 30 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 59, 1, 0)) as  bracket_2'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 60, 1, 0)) as  bracket_3')
                                )
                            ->join($GLOBALS['season_prefix']."prv_".$prv.'.other_info', function($join){
                                $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                                $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                            })
                            ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","09%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","63%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->whereRaw('YEAR(other_info.birthdate) > "1900" AND YEAR(other_info.birthdate) < "2021"')
                            ->first();




                        if($total == 0){
                            $percent = 0;
                        }else{
                            $percent = ($withContact->total_farmers/$total)*100;
                            $percent = round($percent,2);
                        } 



                        if($age_bracket->bracket_1 == null){
                            $bracket_1 = 0;
                        }else{
                            $bracket_1 = $age_bracket->bracket_1;
                        }

                        if($age_bracket->bracket_2 == null){
                            $bracket_2 = 0;
                        }else{
                            $bracket_2 = $age_bracket->bracket_2;
                        }

                        if($age_bracket->bracket_3 == null){
                            $bracket_3 = 0;
                        }else{
                            $bracket_3 = $age_bracket->bracket_3;
                        }

                         //19-29
                         //30-59
                         //60 and above
                        DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->insert(
                            [
                            "region" => $municipality_arr->regionName,
                            "province" => $municipality_arr->province,
                            "municipality" => $municipality_arr->municipality,
                            "all_farmer" => $total,
                            "withContact" => $withContact->total_farmers,
                            "Average" => $percent,
                            'total_female' => $withContact->total_female,
                            'total_male' => $withContact->total_male,
                            'min_age' => $min_age->format("%y"),
                            'max_age' => $max_age->format("%y"),
                            'age_bracket_1' =>$bracket_1,
                            'age_bracket_2' =>$bracket_2,
                            'age_bracket_3' =>$bracket_3
                            ]
                        );
                    }else{    
                    }
            }
    }


    public function nationWideStat(){
        
             DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->truncate();
            $municipalList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                   //->where("prv", "=","012804")
                   // ->where("munCode", "LIKE", "%".$municipality."%")
                    ->orderBy("region_sort", "ASC")
                    ->groupBy("municipality")
                    ->get();
            $data_arr = array();
            foreach ($municipalList as $municipality_arr) {
                $regionCode = $municipality_arr->regCode;
                $rsbsa_pattern = $municipality_arr->regCode."-".$municipality_arr->provCode.'-'.$municipality_arr->munCode;
                $prv = $municipality_arr->regCode.$municipality_arr->provCode;

                    $checkDB = DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA", "prv_".$prv)
                    ->where("TABLE_NAME", "farmer_profile")
                    ->first();    

                    
                    if(count($checkDB)>0){

                    $total = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                            ->where("distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->count();   

                    $withContact = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                        ->select(DB::raw('COUNT(farmer_profile.distributionID) as total_farmers'), 
                            DB::raw('SUM(IF((farmer_profile.sex = "male"), 1, 0)) as total_male'),
                            DB::raw('SUM(IF((farmer_profile.sex = "femal"), 1, 0)) as total_female')
                        )
                        ->join($GLOBALS['season_prefix']."prv_".$prv.'.other_info', function($join){
                            $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                            $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                        })
                        ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","09%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","63%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->first();

                        $age = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                            ->select(DB::raw('MIN(other_info.birthdate) as max_age'), DB::raw('MAX(other_info.birthdate) as min_age'))
                            ->join($GLOBALS['season_prefix']."prv_".$prv.'.other_info', function($join){
                                $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                                $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                            })
                            ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","09%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","63%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->whereRaw('YEAR(other_info.birthdate) > "1900" AND YEAR(other_info.birthdate) < "2021"')
                            ->first();
                    
                            $currentDate = date("d-m-Y");
                            $min_age = date_diff(date_create($age->min_age), date_create($currentDate));
                            $max_age = date_diff(date_create($age->max_age), date_create($currentDate));
                    



//DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), '%Y')+0 AS age;

                        $age_bracket = DB::table($GLOBALS['season_prefix']."prv_".$prv.'.farmer_profile')
                            ->select(
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 19 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 29, 1, 0)) as  bracket_1'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 30 AND DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") <= 59, 1, 0)) as  bracket_2'),
                                DB::raw('SUM(IF(DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(),other_info.birthdate)), "%Y") >= 60, 1, 0)) as  bracket_3')
                                )
                            ->join($GLOBALS['season_prefix']."prv_".$prv.'.other_info', function($join){
                                $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                                $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                            })
                            ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","09%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->where("other_info.phone","!=","")
                            ->where("other_info.phone","LIKE","63%")
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                            ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                            ->whereRaw('YEAR(other_info.birthdate) > "1900" AND YEAR(other_info.birthdate) < "2021"')
                            ->first();




                        if($total == 0){
                            $percent = 0;
                        }else{
                            $percent = ($withContact->total_farmers/$total)*100;
                            $percent = round($percent,2);
                        } 



                        if($age_bracket->bracket_1 == null){
                            $bracket_1 = 0;
                        }else{
                            $bracket_1 = $age_bracket->bracket_1;
                        }

                        if($age_bracket->bracket_2 == null){
                            $bracket_2 = 0;
                        }else{
                            $bracket_2 = $age_bracket->bracket_2;
                        }

                        if($age_bracket->bracket_3 == null){
                            $bracket_3 = 0;
                        }else{
                            $bracket_3 = $age_bracket->bracket_3;
                        }

                         //19-29
                         //30-59
                         //60 and above
                        DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->insert(
                            [
                            "region" => $municipality_arr->regionName,
                            "province" => $municipality_arr->province,
                            "municipality" => $municipality_arr->municipality,
                            "all_farmer" => $total,
                            "withContact" => $withContact->total_farmers,
                            "Average" => $percent,
                            'total_female' => $withContact->total_female,
                            'total_male' => $withContact->total_male,
                            'min_age' => $min_age->format("%y"),
                            'max_age' => $max_age->format("%y"),
                            'age_bracket_1' =>$bracket_1,
                            'age_bracket_2' =>$bracket_2,
                            'age_bracket_3' =>$bracket_3
                            ]
                        );
                  
                           
                       
                    }else{
                          
                    }
                
                    

            }
            
           
            
    
    }

    public function profileStatInfo(){
        $province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->groupBy("province")
                        ->get();

        return view("reports.farmerWithContact")
            ->with("province_list", $province_list);
    }

    public function profileStatMunicipality(Request $request){
        $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("province", $request->province)
                    ->groupBy("municipality")
                    ->get();         
        echo json_encode($municipality_list);
    }  

    public function exportToExcel(Request $request){
        $switch = 1; //1->live //2->rcep_reports
        $list = DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->count();
        if($list >= 700){
            $switch = 0;
        }else{
            $switch = 1;
        }

        

        if($switch == 1){
            if($request->municipality == "0")$municipality="%";else{$municipality=$request->municipality;}
            $municipalList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("province", $request->province)
                    ->where("munCode", "LIKE", "%".$municipality."%")
                    ->get();

            $data_arr = array();
            foreach ($municipalList as $municipality_arr) {
                $regionCode = $municipality_arr->regCode;
                $rsbsa_pattern = $municipality_arr->regCode."-".$municipality_arr->provCode.'-'.$municipality_arr->munCode;
                 $prv = $municipality_arr->regCode.$municipality_arr->provCode;
                      $checkDB = DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA", "prv_".$prv)
                    ->where("TABLE_NAME", "farmer_profile")
                    ->first();    

                    if(count($checkDB)>0){
                    $total = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.'.farmer_profile')
                            ->where("distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->count();   

                    $withContact = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.'.farmer_profile')
                        ->join($GLOBALS['season_prefix']."prv_".$request->prv.'.other_info', function($join){
                            $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                            $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                        })
                        ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","09%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","63%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->count();

                  
                        if($total == 0){
                            $percent = 0;
                        }else{
                            $percent = ($withContact/$total)*100;
                            $percent = round($percent,2);
                        } 
                        array_push($data_arr,array(
                            "Province" => $municipality_arr->province,
                            "Municipality" => $municipality_arr->municipality,
                            "Farmer Count" => $total,
                            "Farmer With Contact Infomation" => $withContact,
                            "Average" => $percent."%"));
                    }else{
                          
                    }
            }
        }else{
            $list = DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->get();
                $data_arr = array();
                foreach ($list as $key => $value) {
                     array_push($data_arr,array(
                            "Province" => $value->province,
                            "Municipality" => $value->municipality,
                            "Farmer Count" => $value->all_farmer,
                            "Farmer With Contact Infomation" => $value->withContact,
                            "Average" => $value->Average."%"));
                }
        }

         
             
            $myFile = Excel::create('Farmer_Profile_with_Contact', function($excel) use ($data_arr) {
            $excel->sheet("Profiles", function($sheet) use ($data_arr) {
                $sheet->fromArray($data_arr);
                $sheet->cells("A1:D1", function($cell){
                    $cell->setFontWeight('bold');
                    $cell->setBackground('#00db00');
                });
            });
        });

        $file_name = "EX"."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }


     public function generateTbl(Request $request)
    {


        $list = DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")->count();
        if($list >= 700){
            $switch = 0;
        }else{
            $switch = 1;
        }


       // $switch = 1; //1-live 0->rcep_reports

        if($switch == 1){
            if($request->municipality == "0")$municipality="%";else{$municipality=$request->municipality;}

      
            $municipalList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("province", $request->province)
                    ->where("munCode", "LIKE", "%".$municipality."%")
                    ->get();

            $data_arr = array();
            foreach ($municipalList as $municipality_arr) {
                $regionCode = $municipality_arr->regCode;
                $rsbsa_pattern = $municipality_arr->regCode."-".$municipality_arr->provCode.'-'.$municipality_arr->munCode;
                 $prv = $municipality_arr->regCode.$municipality_arr->provCode;
                    /*$allFarmer = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.'.farmer_profile')
                            ->where("distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->get();

                    $total = 0;
                    $withContact = 0;

                            
                            foreach ($allFarmer as $key => $value) {
                               $total++;
                                $othInfo = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.'.other_info')
                                    ->where("farmer_id", $value->farmerID)
                                    ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                    ->where("phone", "!=", "")
                                    ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                                    ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                                    ->first();

                                    if(count($othInfo)>0){
                                        $withContact++;
                                    }
                            } */
                      $checkDB = DB::table("information_schema.COLUMNS")->where("TABLE_SCHEMA", "prv_".$prv)
                    ->where("TABLE_NAME", "farmer_profile")
                    ->first();     

                    if(count($checkDB)>0){
                    $total = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.'.farmer_profile')
                            ->where("distributionID", "LIKE", "R".$regionCode."%")
                            ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                            ->count();   

                    $withContact = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.'.farmer_profile')
                        ->join($GLOBALS['season_prefix']."prv_".$request->prv.'.other_info', function($join){
                            $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID");
                            $join->on("other_info.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                        })
                        ->where("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","09%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->orWhere("farmer_profile.distributionID", "LIKE", "R".$regionCode."%")
                        ->where("farmer_profile.rsbsa_control_no","LIKE", $rsbsa_pattern.'%')
                        ->where("other_info.phone","!=","")
                        ->where("other_info.phone","LIKE","63%")
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),"<=",13)
                        ->where(DB::raw("CHAR_LENGTH(REPLACE(other_info.phone,'-',''))"),">=",10)
                        ->count();

                  


                        if($total == 0){
                            $percent = 0;
                        }else{
                            $percent = ($withContact/$total)*100;
                            $percent = round($percent,2);
                        } 
                        array_push($data_arr,array(
                            "province" => $municipality_arr->province,
                            "municipality" => $municipality_arr->municipality,
                            "total_farmer" => $total,
                            "count" => $withContact,
                            "percent" => $percent."%"));
                    }else{
                          
                    }
            }
        }else{
            if($request->municipality == "0")$municipality="%";else{$municipality=$request->municipality;}

            if($municipality != "%"){
                 $municipality = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("province", $request->province)->where("munCode", $municipality)->value("municipality");
            }

        

                $list = DB::table($GLOBALS['season_prefix']."rcep_reports.statistics_farmer_contact")
                ->where("province", $request->province)
                ->where("municipality", "like", $municipality)
                ->get();
               
                $data_arr = array();
                foreach ($list as $key => $value) {
                     array_push($data_arr,array(
                            "province" => $value->province,
                            "municipality" => $value->municipality,
                            "total_farmer" => $value->all_farmer,
                            "count" => $value->withContact,
                            "percent" => $value->Average."%"));
                }
        }



        
         
        $data_arr = collect($data_arr);
        return Datatables::of($data_arr)
        ->make(true);
    }




    
    public function index(){
        return view('farmer_profile.index');
    }

    public function ds2019_update_farmer_profile(Request $request){
        try {
           //update farmer profile ds2019
            DB::table($GLOBALS['season_prefix'].'rcep_farmers.ds_2019_list')
            ->where('id', $request->profile_id)
            ->update([
                'rsbsa_number' => $request->rsbsa_number,
                'full_name' => $request->full_name
            ]);

            return "update_profile_success";
        } catch (\Exception $e) {
            return "sql_error";
        }
    }

    public function farmer_profile_2020(Request $request){
        try {
            $farmer_profile = DB::table($request->prv_code.'.farmer_profile')->where('rsbsa_control_no', $request->rsbsa_number)->orderBy('farmerID', 'DESC')->first();

            if(count($farmer_profile) > 0){
                //check released info
                $released_data = DB::table($request->prv_code.'.released')->where('rsbsa_control_no', $request->rsbsa_number)->first();
                if(count($released_data) > 0){

                    $dop_name = DB::connection("delivery_inspection_db")->table('lib_dropoff_point')
                        ->where('prv_dropoff_id', $released_data->prv_dropoff_id)
                        ->groupBy('prv_dropoff_id')
                        ->value('dropOffPoint');

                    $bags_claimed = $released_data->bags;
                    $seed_variety = $released_data->seed_variety;
                    $dop = $dop_name;
                    $municipality = $released_data->municipality;
                    $province = $released_data->province;
                }else{
                    $bags_claimed = "N/A";
                    $seed_variety = "N/A";
                    $dop = "N/A";
                    $municipality = "N/A";
                    $province = "N/A";
                }

                return array(
                    "rsbsa_number" => $farmer_profile->rsbsa_control_no,
                    "full_name" => $farmer_profile->midName != '' ? $farmer_profile->firstName." ".$farmer_profile->midName." ".$farmer_profile->lastName." ".$farmer_profile->extName : $farmer_profile->firstName." ".$farmer_profile->lastName." ".$farmer_profile->extName,
                    "province" => $province,
                    "municipality" => $municipality,
                    "dop" => $dop,
                    "bags_claimed" => $bags_claimed,
                    "seed_variety" => $seed_variety
                );

            }else{
                return array(
                    "rsbsa_number" => "NO FARMER PROFILE",
                    "full_name" => "NO FARMER PROFILE",
                    "province" => "NO FARMER PROFILE",
                    "municipality" => "NO FARMER PROFILE",
                    "dop" => "NO FARMER PROFILE",
                    "bags_claimed" => "NO FARMER PROFILE",
                    "seed_variety" => "NO FARMER PROFILE"
                );
            }
        } catch (\Exception $e) {
            return array(
                "rsbsa_number" => "No data fetched, please check if the rsbsa number is correct",
                "full_name" => "No data fetched, please check if the rsbsa number is correct",
                "province" => "No data fetched, please check if the rsbsa number is correct",
                "municipality" => "No data fetched, please check if the rsbsa number is correct",
                "dop" => "No data fetched, please check if the rsbsa number is correct",
                "bags_claimed" => "No data fetched, please check if the rsbsa number is correct",
                "seed_variety" => "No data fetched, please check if the rsbsa number is correct"
            );
        }   
    }
    
    public function list_index(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->select('province', 'prv')->groupBy('province')->get();
        return view('farmer_profile.list_home')->with('provinces', $provinces);
    }

    public function get_municipality(Request $request){
        $str = explode('|', $request->province);
        $prv = $str[1];
        $province = $str[0];

        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('province', $province)
                ->orderBy('municipality', 'ASC')
                ->groupBy('municipality')
                ->get();

        $return_str= '';
        foreach($municipalities as $row){
                $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
        }
        return $return_str;
    }

    public function load_list(Request $request){
        $str = explode('|', $request->province);
        $prv = $str[1];
        $province = $str[0];

        //get ds 2019 farmer list
        $farmer_arr = array();
        $ds_farmer_list = DB::table($GLOBALS['season_prefix'].'rcep_farmers.ds_2019_list')
            ->where('rsbsa_number', '!=', '')
            ->where('province', $province)
            ->get();

        //get ws 2020 details
        foreach($ds_farmer_list as $row){
            $rsbsa_str = explode('-', $row->rsbsa_number);

            $rsbsa_search_str = '';
            foreach($rsbsa_str as $rsbsa_row){
                $rsbsa_search_str .= $rsbsa_row.'%';
            }
            rtrim($rsbsa_search_str,'%');

            //get data from released table and farmer profile (ws 2020)
            $database = substr($prv, 0, 4);
            $ws_released_data = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.released')
                ->select('rsbsa_control_no', 'seed_variety', 'bags')
                ->where('rsbsa_control_no', 'like', '%' . $rsbsa_search_str . '%')
                ->where('province', $province)
                ->first();

            if(count($ws_released_data) > 0){
                $ws_profile_data = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile')
                    ->select('firstName', 'midName', 'lastName', 'extName', 'sex', 'area', 'actual_area', 'rsbsa_control_no')
                    ->where('rsbsa_control_no', 'like', '%' . $rsbsa_search_str . '%')
                    ->first();

                $row_data = array(
                    'ds_2019_rsbsa' => $row->rsbsa_number,
                    'ds_2019_name' => $row->full_name,
                    'ds_2019_variety' => $row->seed_variety,
                    'ds_2019_bags_claimed' => $row->bags_claimed,
                    'ws_2020_rsbsa' => $ws_profile_data->rsbsa_control_no,
                    'ws_2020_name' => $ws_profile_data->midName != '' ? $ws_profile_data->firstName.' '.$ws_profile_data->lastName.' '.$ws_profile_data->extName : $ws_profile_data->firstName.' '.$ws_profile_data->midName.' '.$ws_profile_data->lastName.' '.$ws_profile_data->extName,
                    'ws_2020_variety' => $ws_released_data->seed_variety,
                    'ws_2020_bags_claimed' => $ws_released_data->bags
                );
                array_push($farmer_arr, $row_data);
            }else{
                $row_data = array(
                    'ds_2019_rsbsa' => $row->rsbsa_number,
                    'ds_2019_name' => $row->full_name,
                    'ds_2019_variety' => $row->seed_variety,
                    'ds_2019_bags_claimed' => $row->bags_claimed,
                    'ws_2020_rsbsa' => '',
                    'ws_2020_name' => 'no_match',
                    'ws_2020_variety' => 'no_match',
                    'ws_2020_bags_claimed' => 'no_match'
                );
                array_push($farmer_arr, $row_data);
            }
        }

        $myFile = Excel::create('DSxWS_list', function($excel) use ($farmer_arr) {
            $excel->sheet("FARMER LIST", function($sheet) use ($farmer_arr) {
                $sheet->fromArray($farmer_arr);
            });
        });

        $file_name = $province."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

    public function farmer_benificiaries_cross_check(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.ws2020_ds2021_ws2021')
        ->select('province')
        ->addSelect(DB::raw('SUBSTRING(dop,1,4) as prv'))
        ->groupBy('province')->get();
        return view('farmer_profile.cross_check')->with('provinces', $provinces);
    }

    public function cross_check_list(Request $request){
        $str = explode('|', $request->province);
        $prv = $str[1];
        $province = $str[0];

        $database = $GLOBALS['season_prefix']."prv_".substr($prv, 0, 4);
      


        $farmer_arr1 = DB::table($GLOBALS['season_prefix']."rcep_reports.ws2020_ds2021_ws2021 as a")
            ->select(DB::raw(
                "a.rsbsa_number as 'RSBSA Number', a.first_name as 'FIRST NAME', a.middle_name as 'MIDDLE NAME', a.last_name as 'LAST NAME', 
                a.ext_name as 'EXT NAME', a.sex as 'SEX', a.is_ws2020 as 'WS 2020', a.is_ds2021 as 'DS 2021'"
            ))
            ->addSelect(DB::raw("IF(IFNULL((SELECT released.rsbsa_control_no
                FROM ".$database.".released 
                LEFT JOIN ".$database.".farmer_profile ON released.rsbsa_control_no = farmer_profile.rsbsa_control_no
                WHERE 'farmer_profile.firstName' = 'a.first_name'
                    AND 'farmer_profile.midName' = 'a.middle_name'
                    AND 'farmer_profile.lastName' = 'a.last_name'
                    AND 'farmer_profile.extName' = 'a.ext_name'
                    AND 'farmer_profile.birthdate' = 'a.birthdate'
                    AND 'released.municipality' = 'a.municipality' LIMIT 1), FALSE
                ), 'YES', 'NO') as 'WS 2021'"))
            ->where([ 'a.province' => $province ])->get();
        
        $farmer_arr2 = DB::table($database.".released as a")
            ->select(DB::raw(
                "a.rsbsa_control_no as 'RSBSA Number', b.firstName as 'FIRST NAME', b.midName as 'MIDDLE NAME', b.lastName as 'LAST NAME', 
                b.extName as 'EXT NAME', b.sex as 'SEX', 'NO' as 'WS 2020', 'NO' as 'DS 2021', 'YES' as 'WS 2021'"
            ))
            ->leftJoin($database.".farmer_profile as b", "a.rsbsa_control_no", "=", "b.rsbsa_control_no")
            ->whereRaw("NOT EXISTS(
                SELECT rsbsa_number FROM rcep_reports.ws2020_ds2021_ws2021
                WHERE 'b.firstName' = 'ws2020_ds2021_ws2021.first_name'
                AND 'b.midName' = 'ws2020_ds2021_ws2021.middle_name'
                AND 'b.lastName' = 'ws2020_ds2021_ws2021.last_name'
                AND 'b.extName' = 'ws2020_ds2021_ws2021.ext_name'
                AND 'b.birthdate' = 'ws2020_ds2021_ws2021.birthdate'
                AND 'a.province' = 'ws2020_ds2021_ws2021.province'
                AND 'a.municipality' = 'ws2020_ds2021_ws2021.municipality' LIMIT 1
            )")
            ->where([ 'a.province' => $province ])->get();
            
        $farmer_arr1 = json_decode(json_encode($farmer_arr1), true);
        $farmer_arr2 = json_decode(json_encode($farmer_arr2), true);

        $farmer_arr = array_merge($farmer_arr1, $farmer_arr2);
        // dd($farmer_arr);
        $myFile = Excel::create('WS2021XDS2021xWS2021_list', function($excel) use ($farmer_arr) {
            $excel->sheet("FARMER LIST", function($sheet) use ($farmer_arr) {
                $sheet->fromArray($farmer_arr);
                $sheet->cells('A1:I1', function ($cells) {
                    $cells->setBackground('#92D050');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBorder('A1:I1', 'thin');
                   
                });
                
            });
        });
        
        $file_name = $province."_".date("Y-m-d H:i:s").".xlsx";
        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }
}

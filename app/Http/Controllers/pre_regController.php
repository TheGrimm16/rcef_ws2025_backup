<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Checking;
use Yajra\Datatables\Datatables;

use DB;
use Auth;

class pre_regController extends Controller {


    //V.2 PRE REG --------------------------------------------------
    public function prv_list(){
        $lib = DB::table($GLOBALS['season_prefix']."rcep_reports_view.rcef_nrp_provinces")
            ->orderBy("region_sort")
            ->get();

        return json_encode($lib);

    }
    
    public function muni_list($prv){
        $lib = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select('municipality', 'prv')
            ->where("prv_code", $prv)
            ->orderBy("region_sort")
            ->get();
    
       return $lib;
    }
   
    public function load_farmer_information(Request $request){
        $request = json_decode(request()->getContent(), true);

      
        if($request['api_key'] == "1q988uN743w0diRjbc3%6IBzOloUUSc"){
            $rcef_id = $request['rcef_id']; 
            $claimant = str_replace("-", "", $request['claiming_prv']);
            $prv_db = $GLOBALS['season_prefix']."prv_".substr($claimant,0,4);

                $check_farmer = DB::table($prv_db.".farmer_information_final")
                    ->where("rcef_id", $rcef_id)
                    ->where("claiming_prv", $request['claiming_prv'])
                    ->first();

                $prefix = $GLOBALS['last_season_prefix'];

                $getClaim = DB::table($prefix.'rcep_paymaya.tbl_claim')
                ->select('paymaya_code', 'rsbsa_control_no', 'seedVariety','prev_total_production','prev_ave_wt_bag','prev_area_harvested')
                ->where('paymaya_code',$rcef_id)
                ->groupBy('paymaya_code')
                ->first();

//                    dd($check_farmer);
            //dd($rcef_id);
                if($check_farmer != null){
                    return json_encode(array(
                        "status" => "1",
                        "data" => $check_farmer,
                        "yield" => $getClaim
                    ));
                }else{
                    return json_encode(array(
                        "status" => "0",
                        "data" => $check_farmer,
                        "yield" => $getClaim
                    ));
                }
        }else{
            return json_encode(array(
                "status" => "0",
                "data" => "wrong api_key provided"
            ));


        }
    }
    

    public function insert_pre_reg_farmer(Request $request){
        $request = json_decode(request()->getContent(), true);

      
        if($request['api_key'] == "1q988uN743w0diRjbc3%6IBzOloUUSc"){
             


                   try {
                    $rcef_id = $request['rcef_id']; 
                    $rsbsa = $request['rsbsa'];
                    $fname = $request['fname'];
                    $mname = $request['mname'];
                    $lname = $request['lname'];
                    $ename = $request['ename'];
                    $contact_no = $request['contact_no'];
                    $final_area = $request['final_area'];
                    $claim_area = $request['claim_area'];
                    $sex = $request['sex'];
                    $reg = $request['reg'];
                    $prv = $request['prv'];
                    $mun = $request['mun'];
                    $brgy = $request['brgy'];
                    $claiming_brgy = $request['brgy'];
                    $season = $request['prev_season'];
                    $season_year = $request['prev_season_year'];
                    $yield_seed_type = $request['yield_seed_type'];
                    $yield_seed_name = $request['yield_seed_name'];
                    $prev_area_planted = $request['prev_area_planted'];
                    $prev_production = $request['prev_production'];
                    $prev_wt_bags = $request['prev_wt_bags'];
                    $prev_yield = $request['prev_yield'];
                    $low_yield_reason = $request['low_yield_reason'];
                    $sowing_date = $request['sowing_date'];
                    $crop_establishment = $request['crop_establishment'];
                    $ecosystem = $request['ecosystem'];

                    $varietyPref = $request['varietyPref'];
                    $varietyPrefAll = $request['varietyPrefAll'];

                    $claiming_prv = substr($brgy, 0, 6);
                    $claiming_prv = substr($claiming_prv, 0, 2)."-".substr($claiming_prv, 2, 2)."-".substr($claiming_prv, 4, 2);
                    
                    $rsbsa2 = str_replace("-","",$rsbsa);

                    $parcel_address = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->select(
                            "regionName",
                            "province",
                            "municipality"
                        )
                        ->where("temp_prv", $claiming_prv)
                        ->first();
                    
        
                    $brgy = DB::table($GLOBALS["season_prefix"]."sdms_db_dev.lib_geocodes")
                        ->where("name", "LIKE",$brgy)
                        ->where("geocode_municipality", "LIKE", substr($rsbsa2,0,6)."%")
                        ->first();
        
                    if($brgy != null){
                        $brgy = $brgy->geocode_brgy;
                    }else{
                        $brgy = "000000000";
                    }
        
                    
                    $paymaya_db = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                        ->where("rcef_id", $rcef_id)
                        ->where("claiming_prv", $claiming_prv)
                        // ->where("isPrereg", "1")
                        ->first();
                   
                    if($paymaya_db != null){
                        if($paymaya_db->isPrereg == "1"){
                            return json_encode(array(
                                "status" => "0",
                                "data" => "Already PreReg"
                            ));
                        }else{
                            $paymaya_db = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                                ->where("rcef_id", $rcef_id)
                                ->update([
                                    "isPrereg" => "1"
                                ]);
                        }
                    }else{
                      
                        $sowing_arr = explode("/", $sowing_date);
                        $ins_arr = array(
                            "rcef_id" => $rcef_id,
                            "rsbsa_control_number" => $rsbsa,
                            "fname" => $fname,
                            "midname" => $mname,
                            "lname" => $lname,
                            "extename" => $ename,
                            "contact_no" => $contact_no,
                            "farm_area_ws2021" => $final_area,
                            "farm_area_ds2021" => $final_area,
                            "actual_area" => $final_area,
                            "committed_area" => $claim_area,
                            "farmer_declared_area" => $claim_area,
                            "ver_sex" => $sex,
                            "farm_addr_reg" => $parcel_address->regionName,
                            "farm_addr_prv" => $parcel_address->province,
                            "farm_addr_mun" => $parcel_address->municipality,
                            "province_name" => $prv,
                            "municipality_name" => $mun,
                            "barangay_code" => $claiming_brgy,
                            "claiming_prv" => $claiming_prv,
                            "varietyPref" => $varietyPref,
                            "varietyPrefAll" => $varietyPrefAll,
                            "season" => $season,
                            "season_year" => $season_year,
                            "yield_seed_type" => $yield_seed_type,
                            "yield_seed_name" => $yield_seed_name,
                            "yield_area" => $prev_area_planted,
                            "yield_no_bags" => $prev_production,
                            "yield_weight_bags" => $prev_wt_bags,
                            "yield" => $prev_yield,
                            "low_yield_reason" => $low_yield_reason,
                            "sowing_month" => $sowing_arr[0],
                            "sowing_week" => $sowing_arr[1],
                            "crop_establishment" => $crop_establishment,
                            "ecosystem" => $ecosystem,
                            "isPrereg" => 1,
                            "region" => substr($rsbsa2,0,2), 
                            "prv_code" => substr($rsbsa2,0,4), 
                            "muni_code" => substr($rsbsa2,0,6), 
                            "status" => 1,
                            "created_by" => "prereg",
                            "sowing_year" => "2023",
                            "isActive" => 1,
                            "isPush" => 1
                        );

                        $paymaya_db = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                        ->insert($ins_arr);
    

                    }
        
                   
                    $prv_db = $GLOBALS['season_prefix']."prv_".substr($rcef_id,0,4);    
                        // $check_farmer = DB::table($prv_db.".farmer_information")
                        //     ->where("rcef_id", $rcef_id)
                        //    ->update([
                        //     "is_ebinhi" => "1"
                        //    ]);

                        $check_farmer = DB::table($prv_db.".farmer_information_final")
                           ->where("rcef_id", $rcef_id)
                          ->update([
                           "is_ebinhi" => "1"
                          ]);


                    return json_encode(array(
                        "status" => "1",
                        "data" => "Success"
                    ));


                   } catch (\Throwable $th) {
                    
                    return json_encode(array(
                        "status" => "0",
                        "data" => "ERROR"
                    ));

                   }



        }else{
            return json_encode(array(
                "status" => "0",
                "data" => "wrong api_key provided"
            ));


        }
    }
    

    public function getCurrentSeason(){
        $raw = DB::table('rcep_season.tbl_season')
            ->where('id', 18)
            ->where('is_active', 1)
            ->value('season');
        
        return json_encode($raw);
    }

    public function getRegionalVariety($region){
        return DB::table('rcef_reports.possiblevarieties')
            ->select("variety")
            ->where(''.$region, 1)
            ->get();
    }

    public function getAllVarieties(){
        return DB::table('ds2024_seed_seed.seed_characteristics')
            ->select("variety")
            ->get();
    }

    public function getAllYieldVarieties(){
        return DB::table('ds2024_seed_seed.tbl_varieties')
            ->get();
    }





    //V.1 PRE REG ----------------------------------------------------

    public function update_farmer(Request $request){
        
        DB::beginTransaction();
        try {  

        //    DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".other_info_processed")
        //         ->where("info_id", $request->oth_id)
        //         ->update([
        //             "birthdate" => date("Y-m-d", strtotime($request->birthdate))
        //         ]);

                DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".other_info_processed")
                ->where("rsbsa_control_no", $request->rsbsa)
                ->where("farmer_id", $request->farmer_id)
                ->update([
                    "birthdate" => date("Y-m-d", strtotime($request->birthdate)),
                    "phone" => $request->contact
                ]);
           
                DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_profile_processed")
                ->where("id", $request->id)
                ->update([
                    "lastName" => $request->lname,
                    "firstName" => $request->fname,
                    "midName" => $request->mname,
                    "extName" => $request->ename,
                    "user_update" =>Auth::user()->username
                    
                ]);
           
                
            DB::commit();
           
            return json_encode(array(
                "status" => "1",
                "log" => "success"
            ));
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode(array(
                "status" => "0",
                "log" => $th->getMessage()
        
            ));
        }



       
    }

    public function view_farmer(){
        $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
             ->where("municipality", "CANDABA")
            ->groupBy("province")
            ->get();

        return view("pre_registration.index")
            ->with("provinces", $province);

    }

    public function getMunicipality($province){
        $municipality = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $province)
            // ->where("municipality", "CANDABA")
            ->groupBy("municipality")
            ->get();

        return json_encode($municipality);
    }


    public function loadFarmer(Request $request){

        $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("prv_code", $request->prv)
            ->first();

        $rsbsa_format =$lib_prv->regCode."-".$lib_prv->provCode."-".$lib_prv->munCode."%";
        // MALAKI BAGO
        $data = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information as f")
            // ->where("f.rsbsa_control_no", "LIKE", $rsbsa_format)
            //->where("f.rsbsa_control_no", "LIKE", '%03-54%')
            ->where("f.lastName", "!=", "")
            ->where("f.firstName", "!=", "")
            ->where("f.rsbsa_control_no", "LIKE", "%".$request->search_text."%")

            ->orwhere("f.lastName", "!=", "")
            ->where("f.firstName", "!=", "")
            ->where("f.fullName", "LIKE", "%".$request->search_text."%")
            
            
            ->get();

        $data = json_decode(json_encode($data), true);
        $data = collect($data);
         
        $prv = $request->prv;
       return  Datatables::of($data)   
         ->addColumn('action', function($row) use ($prv){  
            $btn = "<a class='btn btn-warning btn-sm '
                 data-toggle='modal' data-target='#pre_reg_farmerinfo'
                 data-id = ".'"'.$row['id'].'"'."
                 data-oth_id = ".'"'.$row['rcef_id'].'"'."
                 data-rsbsa  = ".'"'.$row['rsbsa_control_no'].'"'."
                 data-farmer_id  = ".'"'.$row['farmer_id'].'"'."
                 data-farmer_fname  = ".'"'.$row['firstName'].'"'."
                 data-farmer_mname  = ".'"'.$row['midName'].'"'."
                 data-farmer_ename  = ".'"'.$row['extName'].'"'."
                 data-actual_area = ".'"'.$row['actual_area'].'"'."
                 data-sex  = ".'"'.$row['sex'].'"'."
                 data-birthdate  = ".'"'.date("m/d/Y", strtotime($row['birthdate'])) .'"'."
                 data-contact  = ".'"'.$row['tel_no'].'"'."
                 data-prv  = ".'"'.$prv.'"'."
                 data-farmer_lname  = ".'"'.$row['lastName'].'"'.">Update</a>";
                
                 return $btn;
        })
         ->make(true);


    }

    public function genQRImageTrail($api_key,$prv_db,$rsbsa){

        if($api_key == "MNaslsbKrf10123j!"){
            DB::beginTransaction();
            try {  

                $cur_count = 1;
                DB::table($prv_db.".pre_reg_audit")
                    ->insert([
                        "rsbsa" => $rsbsa,
                        "count" => $cur_count,
                    ]);

                $count = DB::table($prv_db.".pre_reg_audit")
                    ->where("rsbsa", $rsbsa)
                    ->count("rsbsa");
               
    
                $return_data = array(
                    "count" => $count,
                    "date_updated" => date("Y-m-d H:i:s"),
                    "rsbsa" => $rsbsa
                );
                    
                DB::commit();
                return json_encode($return_data);
    
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode("404");
            }


        }else{
            return json_encode("404 API");
        }

        
        





    }


    public function newFarmerEntry(){
        //API
        $request = json_decode(request()->getContent(), true);
        if($request["api_key"] == "MNaslsbKrf10123j!" ){
            $rsbsa = str_replace("-", "", $request["rsbsa"]);
            $region_code = substr($rsbsa,0,2);
            $province_code = substr($rsbsa,0,4);
            $municipality_code = substr($rsbsa,0,6);
            
            $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("prv", $municipality_code)
                ->first();
               
            if(count($lib_prv)>0){
                $municipality = $lib_prv->municipality;
                $province = $lib_prv->province;
                $region = $lib_prv->regionName;

                        $tbl_check =  DB::table("information_schema.TABLES")
                            ->select("TABLE_NAME")
                            ->where("TABLE_SCHEMA", "prv_".$province_code)
                            ->where("TABLE_NAME", "LIKE", "farmer_profile_processed")
                            ->groupBy("TABLE_NAME")
                            ->first();
                          
                            if(count($tbl_check)>0){
                                $checkExist = DB::table($GLOBALS['season_prefix']."prv_".$province_code.".farmer_profile_processed")
                                    ->where("rsbsa_control_no", $request['rsbsa'])
                                    ->first();
                                    if(count($checkExist)>0){
                                        return json_encode("Already Encoded");
                                    }else{

                                        $x = 0;
                                        $farmerID = DB::table($GLOBALS['season_prefix']."prv_".$province_code.".farmer_profile_processed")
                                        ->select(DB::raw("MAX(farmerID) as farmerID"))
                                        ->first();

                                        if(count($farmerID)>0){
                                            $maxID = $farmerID->farmerID;
                                            $maxID = $maxID + 1;
                                            $newFarmerID = sprintf("%.0f",$maxID );
                                        }else{
                                            $newFarmerID = "000000000000001";
                                        }
                                            do{
                                                $recheck= DB::table($GLOBALS['season_prefix']."prv_".$province_code.".farmer_profile_processed")
                                                ->where("farmerID",$newFarmerID)
                                                ->first();
                                                    if(count($recheck)>0){
                                                        $farmerID = DB::table($GLOBALS['season_prefix']."prv_".$province_code.".farmer_profile_processed")
                                                        ->select(DB::raw("MAX(farmerID) as farmerID"))
                                                        ->first();
                
                                                        if(count($farmerID)>0){
                                                            $maxID = $farmerID->farmerID;
                                                            $maxID = $maxID + 1;
                                                            $newFarmerID = sprintf("%.0f",$maxID );
                                                        }else{
                                                            $newFarmerID = "000000000000001";
                                                        }

                                                        $x = 0;
                                                    }else{
                                                        $x = 1;
                                                    }
                                            }while ($x == 0);
                                          
                                            DB::beginTransaction();
                                                try {
                                        


                                                    $tbl_exist =  DB::table("information_schema.TABLES")
                                                            ->select("TABLE_NAME")
                                                            ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."rcep_farmer_registration")
                                                            ->where("TABLE_NAME", "LIKE", "prv_".$province_code."_farmer_profile_processed")
                                                            ->groupBy("TABLE_NAME")
                                                            ->first();

                                                    if(count($tbl_exist)==0){
                                                        
                                                        DB::connection("pre_registration_db")->select(DB::raw("CREATE TABLE prv_".$province_code."_farmer_profile_processed (`id` int(11) NOT NULL,`farmerID` varchar(255) NOT NULL,`distributionID` varchar(255) NOT NULL,`lastName` varchar(75) NOT NULL,`firstName` varchar(75) NOT NULL,`midName` varchar(75) NOT NULL,`extName` varchar(20) NOT NULL,`fullName` varchar(255) NOT NULL,`sex` varchar(15) NOT NULL,`birthdate` varchar(20) NOT NULL,`region` varchar(75) NOT NULL,`province` varchar(100) NOT NULL,`municipality` varchar(100) NOT NULL,`barangay` varchar(100) NOT NULL,`affiliationType` varchar(75) NOT NULL,`affiliationName` varchar(255) NOT NULL,`affiliationAccreditation` varchar(20) NOT NULL,`isDaAccredited` int(11) NOT NULL,`isLGU` int(11) NOT NULL,`rsbsa_control_no` text NOT NULL,`isNew` int(1) NOT NULL,`send` int(1) NOT NULL,`area` float(5,2) NOT NULL,`area_harvested` double DEFAULT NULL,`actual_area` float(5,2) NOT NULL,`season` varchar(15) NOT NULL,`yield` decimal(8,2) NOT NULL COMMENT 'no of bags',`weight_per_bag` decimal(8,2) NOT NULL,`total_claimable` int(100) NOT NULL,`is_claimed` int(1) NOT NULL,`total_claimed` int(100) NOT NULL,`is_ebinhi` int(1) NOT NULL,`season_inserted` varchar(10) NOT NULL,`update` varchar(100) NOT NULL,`da_area` float(5,2) NOT NULL,`icts_rsbsa` text NOT NULL,`oth_link` int(11) NOT NULL
                                                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"));
                                                        DB::connection("pre_registration_db")->select(DB::raw("ALTER TABLE prv_".$province_code."_farmer_profile_processed ADD PRIMARY KEY (`id`);"));
                                                        DB::connection("pre_registration_db")->select(DB::raw("ALTER TABLE prv_".$province_code."_farmer_profile_processed MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"));


                                                    }

                                                    $tbl_exist2 =  DB::table("information_schema.TABLES")
                                                    ->select("TABLE_NAME")
                                                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."rcep_farmer_registration")
                                                    ->where("TABLE_NAME", "LIKE", "prv_".$province_code."_other_info_processed")
                                                    ->groupBy("TABLE_NAME")
                                                    ->first();

                                            if(count($tbl_exist2)==0){
                                
                                                DB::connection("pre_registration_db")->select(DB::raw("CREATE TABLE prv_".$province_code."_other_info_processed (`info_id` int(11) NOT NULL,`farmer_id` varchar(100) NOT NULL,`rsbsa_control_no` varchar(100) NOT NULL,`mother_fname` varchar(200) NOT NULL,`mother_mname` varchar(200) NOT NULL,`mother_lname` varchar(200) NOT NULL,`mother_suffix` varchar(100) NOT NULL,`birthdate` date NOT NULL,`is_representative` int(11) NOT NULL,`id_type` varchar(100) NOT NULL,`relationship` varchar(100) NOT NULL,`have_pic` int(11) NOT NULL,`phone` varchar(100) NOT NULL,`send` int(1) NOT NULL,`municipality` varchar(255) NOT NULL,`representative_name` varchar(200) NOT NULL,`icts_rsbsa` text NOT NULL
                                                )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"));
                                                DB::connection("pre_registration_db")->select(DB::raw("ALTER TABLE prv_".$province_code."_other_info_processed ADD PRIMARY KEY (`info_id`);"));
                                                DB::connection("pre_registration_db")->select(DB::raw("ALTER TABLE prv_".$province_code."_other_info_processed MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT;"));
                                            }


                                            $data_reg = DB::table($GLOBALS['season_prefix']."rcep_farmer_registration.prv_".$province_code."_farmer_profile_processed")
                                                ->where("rsbsa_control_no", $request['rsbsa'])
                                                ->first();
                                            
                                            if(count($data_reg)>0){
                                                    return json_encode("Already Registered");
                                            }else{
                                                        //INSERT
                                                                
                                                        do{
                                                            $r = 0;
                                                            $recheck= DB::table($GLOBALS['season_prefix']."rcep_farmer_registration.prv_".$province_code."_farmer_profile_processed")
                                                            ->where("farmerID",$newFarmerID)
                                                            ->first();
                                                                if(count($recheck)>0){
                                                                    $farmerID = DB::table($GLOBALS['season_prefix']."rcep_farmer_registration.prv_".$province_code."_farmer_profile_processed")
                                                                    ->select(DB::raw("MAX(farmerID) as farmerID"))
                                                                    ->first();
                            
                                                                    if(count($farmerID)>0){
                                                                        $maxID = $farmerID->farmerID;
                                                                        $maxID = $maxID + 1;
                                                                        $newFarmerID = sprintf("%.0f",$maxID );
                                                                    }else{
                                                                       
                                                                    }
            
                                                                    $r = 0;
                                                                }else{
                                                                    $r = 1;
                                                                }
                                                        }while ($r == 0);





                                                        $othID = DB::table($GLOBALS['season_prefix']."rcep_farmer_registration.prv_".$province_code."_other_info_processed")
                                                        ->insertGetId([
                                                            "rsbsa_control_no" => $request['rsbsa'],
                                                            "farmer_id" => $newFarmerID,
                                                            "birthdate" => $request['birthdate'],
                                                            "phone" => $request['contact_no']
                                                        ]);


                                                        $area = $request['eArea'];
                                                        if($area != floor($area)){
                                                            $dec =  $area - floor($area); 
                      
                                                            if($dec <= 0.5 ){
                                                            $area = floor($area) + 0.5;
                                                            }else{
                                                                $area = floor($area) + 1;
                                                            }
                                                        }
                                                        $bags = $area * 2;
                                                        

                                                        DB::table($GLOBALS['season_prefix']."rcep_farmer_registration.prv_".$province_code."_farmer_profile_processed")
                                                            ->insert([
                                                                "farmerID" => $newFarmerID,
                                                                "lastName" => $request['last_name'],
                                                                "firstName" => $request['first_name'],
                                                                "midName" => $request['middle_name'],
                                                                "extName" => $request['ext_name'],
                                                                "fullName" => $request['first_name']." ".$request['middle_name']." ".$request['last_name']." ".$request['ext_name'],
                                                                "sex" => $request["sex"],
                                                                "region" => $region,
                                                                "province" => $province,
                                                                "municipality" => $municipality,
                                                                "rsbsa_control_no" => $request['rsbsa'],
                                                                "isNew" => 1,
                                                                "actual_area" => $request['eArea'],
                                                                "total_claimable" => $bags,
                                                                "is_claimed" => 0,
                                                                "total_claimed" => 0,
                                                                "birthdate" => $request["birthdate"],
                                                                "oth_link" => $othID
                                                        ]);
                                            
                                                            DB::commit();
                                                            return json_encode("SUCCESSFULY SAVED");
                                                        }


                                               

                                                  
        
                                                    
                                                           
                                        
                                                   
                                        
                                                } catch (\Throwable $th) {
                                                    DB::rollback();
                                                    return json_encode("404");
                                                }

                                      







                                    }


                            }else{
                                return json_encode("404 DB");
                            }




            }else{
                return json_encode("404 RSBSA");
            }







        }else{
            return json_encode("404 API");
        }
    }


  
    public function generate_qr_code() {
        $request = json_decode(request()->getContent(), true);
//MNaslsbKrf10123j!





       

        if($request["api_key"] == "MNaslsbKrf10123j!"){
            $prv_db = str_replace("-", "", $request["rsbsa"]);
            $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv_db,0,4);
            
            DB::beginTransaction();
            try {
             
                
                $check_qr = DB::table($prv_db.".pre_registration")
                ->where("auto_id", $request["auto_id"])
                ->where("qr_code", "!=", "")
                ->first();
                        if(count($check_qr)>0){
                            return json_encode($check_qr->qr_code);
                        }
                

                $qr = $this->generateClaim_code($request['rsbsa']);

               $id = DB::table($prv_db.".pre_registration")
                ->where("auto_id", $request["auto_id"])
                ->update([
                    "qr_code" => $qr,
                    "is_acknowledge" => 1
                ]);
    
    
                DB::commit();
                return json_encode($qr);
    
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode("404");
            }

        }else{
            return json_encode("404" );
        }

       

     

    }

    
    private function generateClaim_code($rsbsa){
        $prv_db = substr($rsbsa,0,5);
        $prv_db = str_replace("-", "", $prv_db);
        $prv_db = $GLOBALS['season_prefix']."prv_".$prv_db.".pre_registration";
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        $available = 0;
        do{
            $code = "P";
            $code .= substr(str_shuffle($str_result),0, 6);

            $check = DB::table($prv_db)
                ->where("qr_code", "LIKE", $code)
                ->get();

                if(count($check)<=0){
                    $available = 1;
                }

        }while($available == 0);
       
        return $code;



    }

    public function insert_registration() {
        $request = json_decode(request()->getContent(), true);
//MNaslsbKrf10123j!

        if($request["api_key"] == "MNaslsbKrf10123j!"){
            unset($request["api_key"]);
            $prv_db = str_replace("-", "", $request["rsbsa_control_no"]);
            $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv_db,0,4);
           
            DB::beginTransaction();
            try {


                $information_schema = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $prv_db)    
                 ->where("TABLE_NAME", "pre_registration")
                 ->first();

                 if(count($information_schema)>0){
                    $id = DB::table($prv_db.".pre_registration")
                    ->insertGetId($request);
                 }else{
                     
                     return json_encode("404 NO DB");
                 }


              
                DB::commit();
                return json_encode($id);
    
                
            } catch (\Throwable $th) {
                DB::rollback();
                return json_encode("404 ERR");
            }
        }else{
            return json_encode("404API");
        }

      

     

    }


    public function check_name($rsbsa, $fname, $lname,$api_key){
   

        try {
           

            if($api_key = "MNaslsbKrf10"){
                $prv_db = str_replace("-", "", $rsbsa);
                $prv_db = substr($prv_db,0,4);
               
                $information_schema = DB::table("information_schema.TABLES")
                   ->where("TABLE_SCHEMA", "prv_".$prv_db)    
                    ->where("TABLE_NAME", "pre_registration")
                    ->first();
                 
                    if(count($information_schema)>0){
                    
                      
            
                        
                        $already_registered = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".pre_registration")
                            ->where("first_name", "LIKE", $fname)
                            ->where("last_name", "LIKE", $lname)
                            ->where("rsbsa_control_no", $rsbsa)
                            ->first();
    
                            if(count($already_registered)>0){
                                    $already_registered->status = 1;
    
                                    return json_encode($already_registered);
    
                            }else{
    
                                $check_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_profile_processed")
                                ->where("farmer_profile_processed.rsbsa_control_no", $rsbsa)
                                ->join($GLOBALS['season_prefix']."prv_".$prv_db.".other_info_processed", function($join){
                                    $join->on("farmer_profile_processed.farmerID", "=", "other_info_processed.farmer_id");
                                    $join->on("farmer_profile_processed.rsbsa_control_no", "=", "other_info_processed.rsbsa_control_no");
                                    
                                })
                                ->where("other_info_processed.birthdate", "!=", "")
                                ->where("other_info_processed.phone", "!=", "")
                                ->where("firstName", "LIKE", $fname)
                                ->where("lastName", "LIKE", $lname)
                                ->limit(1)
                                ->get();
                                if(count($check_data)>0){
                                    $prv_release = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".released")
                                    ->where("rsbsa_control_no",$check_data[0]->rsbsa_control_no)
                                    ->where("farmer_id",$check_data[0]->farmerID)
                                    ->limit(1)
                                    ->get();
                                    if(count($prv_release)>0){
                                        $prv_dropoff_id = $prv_release->prv_dropoff_id;
                                        
                                        $claim_point = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                                            ->where("prv_dropoff_id", $prv_dropoff_id)
                                            ->limit(1)
                                            ->value("dropOffPoint");
            
                                    }else{
                                        $prv_dropoff_id = "";
                                        $claim_point = "";
                                    }
            
                                    $current_rsbsa = str_replace("-", "", $rsbsa);
                                    $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                                        ->where("regCode", substr($current_rsbsa,0,2))
                                        ->groupBy("regionName")
                                        ->value("regionName");
                                    
                                    $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                                        ->where("prv_code", substr($current_rsbsa,0,4))
                                        ->groupBy("province")
                                        ->value("province");
                                    
                                    $municipality = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                                        ->where("prv", substr($current_rsbsa,0,6))
                                        ->groupBy("municipality")
                                        ->value("municipality");
                                    
                                    $check_data[0]->phone = str_replace("-","",$check_data[0]->phone); 
            
            
                                    $check_data[0]->region = $region;
                                    $check_data[0]->province = $province;
                                    $check_data[0]->municipality = $municipality;
                                    
            
                                    $check_data[0]->prv_dropoff_id = $prv_dropoff_id;    
                                    $check_data[0]->claim_point = $claim_point;
                                    $check_data[0]->status = 2;
                                    return json_encode($check_data);
            
                                }else{
                                    return json_encode("404 FP");
                                }
    
                            }
                    }else{
                        return json_encode("404 IS");
                    }
    
            }else{
    
                return json_encode("404 API");
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }


    }


    public function get_location($type, $data){
        
        if($type == "REGION"){
            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->groupBy("regionName")
                ->orderBy("region_sort")
                ->get();
        }elseif($type == "PROVINCE"){
            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->groupBy("province")
                ->where("regionName", $data)
                ->orderBy("province")
                ->get();
        }elseif($type == "MUNICIPALITY"){
            $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->groupBy("municipality")
                ->where("province", $data)
                ->orderBy("municipality")
                ->get();

        }else{
            $data = "404";
        }
        
        return json_encode($data);
    }


    public function get_dop($province, $municipality){
        $conven_dop= DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->where("municipality", $municipality)
            ->where("province", $province)
            ->get();

        $ebinhi_dop = DB::table($GLOBALS['season_prefix']."rcep_paymaya.lib_ebinhi_dop")
            ->where("municipality", $municipality)
            ->where("province", $province)
            ->get();

        return json_encode(array(
            "conventional_dop" => $conven_dop,
            "ebinhi_dop" => $ebinhi_dop
        ));




    }

    public function getPreviousYield ($api_key,$rcef_id)
    {
        if($api_key!= "bep@2025"){
            return "Please provide api key";
        }

        $prefix = $GLOBALS['last_season_prefix'];

        $getClaim = DB::table($prefix.'rcep_paymaya.tbl_claim')
        ->select('paymaya_code', 'rsbsa_control_no', 'seedVariety','prev_total_production','prev_ave_wt_bag','prev_area_harvested')
        ->where('paymaya_code',$rcef_id)
        ->groupBy('paymaya_code')
        ->first();

        return json_encode($getClaim);

    }

}

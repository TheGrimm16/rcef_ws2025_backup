<?php

namespace App;

use DB;
use Auth;
use Illuminate\Database\Eloquent\Model;

class FarGeneration extends Model
{

    function countBeneficiaryValidatedData($province_name, $municipality_name, $brgy_code){

        
        ini_set('memory_limit', '-1');
        try{
            $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
			
			if(!isset($municipal_details)){
				return 0;
			}
            $region_name = $municipal_details->regionName;
            $database_name = "rpt_".substr($municipal_details->prv, 0, 4);
            $table_name = "tbl_".$municipal_details->prv;
            
            $prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

             $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_information")
                            
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      return 0;
                    }



            $region_code = substr($municipal_details->prv,0,2);
            $province_code = substr($municipal_details->prv,2,2);
            $municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";

            if($brgy_code != "0"){
                if($brgy_code == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy_code, 6,3)."%";
                 }
            }
                
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK   
            $rsbsa_menthod = 1;                       
             if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1

                            $list = DB::table($prv_db.".farmer_information_final")
                                ->where("rcef_bene", "=","V")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                                ->where("final_area", ">", 0)

                                ->orWhere("rcef_bene", "=","RV")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                                ->where("final_area", ">", 0)

                                ->orWhere("rcef_bene", "=","JON")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                                ->where("final_area", ">", 0)

                                ->orWhere("rcef_bene", "=","W2D")
                                ->where("data_season_entry", "=", "WS2022")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                                ->where("final_area", ">", 0)
                                ->groupBy("rsbsa_control_no","farmer_id","lastName","firstName","midName","sex","birthdate","crop_area")
                                ->get();
                          
                            $maxRow = count($list);
		

                    }
                    else{ //method 0
                       $list = DB::table($prv_db.".farmer_information")
                            ->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            ->where("status", "1")
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
                            ->get();
                    }  
                }
                else{
                   $list = DB::table($prv_db.".farmer_information")
                            ->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            ->where("status", "1")
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
                            ->get(); 
                }
				
    	
				return $maxRow;
        }catch(\Illuminate\Database\QueryException $ex){
            dd($ex);
        }
    }


    function countBeneficiaryReplacement($province_name, $municipality_name, $brgy_code){
        

        ini_set('memory_limit', '-1');
        try{
            $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
			
			if(!isset($municipal_details)){
				return 0;
			}
            $region_name = $municipal_details->regionName;
            $database_name = "rpt_".substr($municipal_details->prv, 0, 4);
            $table_name = "tbl_".$municipal_details->prv;
            
            $prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

             $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_information")
              
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      return 0;
                    }



            $region_code = substr($municipal_details->prv,0,2);
            $province_code = substr($municipal_details->prv,2,2);
            $municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";

            if($brgy_code != "0"){
                if($brgy_code == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy_code, 6,3)."%";
                 }
            }
                
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK   
            $rsbsa_menthod = 1;                       
             if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                            $list = DB::table($prv_db.".farmer_information_final")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern."%")
                                ->where("rcef_bene", "!=","V")
                                ->where("rcef_bene", "!=","RV")
                                ->where("rcef_bene", "!=","W2D")
                                ->where("rcef_bene", "!=","JON")
                                ->where("final_area", ">", 0)
                                ->where("is_replacement", 1)


                                ->orWhere("rcef_bene", "=","W2D")
                                ->where("data_season_entry", "!=", "WS2022")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern."%")
                                ->where("final_area", ">", 0)
                                ->where("is_replacement", 1)
                                // ->groupBy(DB::raw("concat(rsbsa_control_no,farmer_id,lastName,firstName,midName,sex,birthdate,crop_area)"))
                                ->groupBy("rsbsa_control_no","farmer_id","lastName","firstName","midName","sex","birthdate","crop_area")
                                ->get();
                 
                            $maxRow = count($list);
		

                    }
                }
				return $maxRow;
        }catch(\Illuminate\Database\QueryException $ex){
            dd($ex);
        }
    
    }

    function countBeneficiaryPs($province_name, $municipality_name, $brgy, $is_non){
        $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
		//->table('lib_dropoff_point')
		->where('province', $province_name)
		->where('municipality', $municipality_name)
		->first();

		if(!isset($municipal_details)){
			return 'No municipal library';
		}	
		
			$region_name = $municipal_details->regionName;
			$prv_db = $GLOBALS['season_prefix']."prv_".$municipal_details->prv_code;
				
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

            // $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
			// if($brgy != "0"){
            //     if($brgy == "all"){
            //          $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
            //      }else{
            //          $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy, 6,3)."%";
            //      }
            // }


            // $list = DB::table($prv_db.".farmer_information_final")
            // ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
           
            // ->where("final_area", ">", 0.1)
            // ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
            // ->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
            // ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
            // ->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
         
            // ->get();



           // FOR NEW FAR
            $rsba_pattern = $region_code.$province_code.$municipality_code."%";
			if($brgy != "0"){
                if($brgy == "all"){
                     $rsba_pattern = $region_code.$province_code.$municipality_code."%";
                 }elseif($brgy == "NONE"){
                    $rsba_pattern = "";
                 }
                 
                 else{
                     $rsba_pattern = $region_code.$province_code.$municipality_code.substr($brgy, 6,3)."%";
                 }
            }

        
            if($is_non == 1){
                $list = DB::table($prv_db.".farmer_information_final")
                ->where("claiming_brgy", "LIKE", $rsba_pattern)
    
                ->where("final_area", "<", 0.1)
                ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
                ->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
                ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
                ->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
             
                ->get();
            }else{
                $list = DB::table($prv_db.".farmer_information_final")
                ->where("claiming_brgy", "LIKE", $rsba_pattern)
              	
                ->where("final_area", ">=", 0.1)
                ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
                ->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
                ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
                ->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
             
                ->get();
            }

          
			

                $maxRow = count($list);
            return $maxRow;
    }



    

	
	 function countBeneficiaryPs_old($province_name, $municipality_name, $brgy_code){

        ini_set('memory_limit', '-1');
        try{
            $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
			
			if(!isset($municipal_details)){
				return 0;
			}
            $region_name = $municipal_details->regionName;
            $database_name = "rpt_".substr($municipal_details->prv, 0, 4);
            $table_name = "tbl_".$municipal_details->prv;
            
            $prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

             $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_information")
              
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      return 0;
                    }



            $region_code = substr($municipal_details->prv,0,2);
            $province_code = substr($municipal_details->prv,2,2);
            $municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";

            if($brgy_code != "0"){
                if($brgy_code == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy_code, 6,3)."%";
                 }
            }
                
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK   
            $rsbsa_menthod = 1;                       
             if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                    //      $rawsql = "SELECT rsbsa_control_no from ".$prv_db.".farmer_information_final where 
                    //      firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."' and tag_data = 0 ";
                    //         //  $rawsql .= " group by rsbsa_control_no, firstName, lastName";
                    //          $rawsql .= "  order by TRIM(LEFT(rsbsa_control_no,12)) asc, TRIM(lastName) asc, 
                    //                  TRIM(firstName) asc, TRIM(midName) asc ";
                    //    // $rawsql .= "LIMIT ".$take." OFFSET ".$skip;
                    //     // dd($rawsql);
                    //     $list = DB::select(DB::raw($rawsql));
                    
                    //         $rawsql_2 = "SELECT rsbsa_control_no from ".$prv_db.".farmer_information_unmerge where 
                    //         firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'
                    //         ";
                    //     $list_2 = DB::select(DB::raw($rawsql_2)); 

                            $list = DB::table($prv_db.".farmer_information_final")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern."%")
                                ->where("rcef_bene", "!=","V")
                                ->where("rcef_bene", "!=","RV")
                                ->where("rcef_bene", "!=","W2D")
                                ->where("rcef_bene", "!=","JON")
                                ->where("final_area", ">", 0)


                                ->orWhere("rcef_bene", "=","W2D")
                                ->where("data_season_entry", "!=", "WS2022")
                                ->where("rsbsa_control_no", "LIKE", $rsba_pattern."%")
                                ->where("final_area", ">", 0)
                                // ->groupBy(DB::raw("concat(rsbsa_control_no,farmer_id,lastName,firstName,midName,sex,birthdate,crop_area)"))
                                ->groupBy("rsbsa_control_no","farmer_id","lastName","firstName","midName","sex","birthdate","crop_area")
                                ->get();
                 
                            $maxRow = count($list);
		

                    }
                    else{ //method 0
                       $list = DB::table($prv_db.".farmer_information")
                            ->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            ->where("status", "1")
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
                            ->get();
                    }  
                }
                else{
                   $list = DB::table($prv_db.".farmer_information")
                            ->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            ->where("status", "1")
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
                            ->get(); 
                }
				
    	
				return $maxRow;
        }catch(\Illuminate\Database\QueryException $ex){
            dd($ex);
        }
    }
	
	
	


    function count_beneficiary($region, $province, $municipality,$checkbox) {

            $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province)
                ->where('municipality', "LIKE", "%".$municipality."%")
                ->first();
               // dd($municipal_details);

            $database_name = "rpt_".substr($municipal_details->prv, 0, 4);
            $table_name = "tbl_".$municipal_details->prv;
            $region_code = substr($municipal_details->prv,0,2);
            $province_code = substr($municipal_details->prv,2,2);
            $municipality_code = substr($municipal_details->prv,4,2);

              $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
              $rsbsa_menthod = 2;//1->check on prv first 0->direct to report table 2->prv
              //$rsbsa_check =1;
              $rsbsa_check = $checkbox;

                if($rsbsa_check){
                    if($rsbsa_menthod == 1){ //method 1
                         $rsbsa_query = DB::table($GLOBALS['season_prefix'].'prv_'.$region_code.$province_code.'.farmer_profile')
                                                    ->select('rsbsa_control_no as pattern', DB::raw('substr(rsbsa_control_no, 1, 12) as foo'))
                                                    ->where('rsbsa_control_no', 'like', $rsba_pattern)
                                                    ->where('firstName','!=','""')
                                                    ->where("status", "1")
                                                    ->groupBy('foo')
                                                    ->orderBy('foo', 'asc')
                                                    ->get();
                                                                
                                        $checkerArr = array();
                                        foreach ($rsbsa_query as $key => $patt) {
                                            foreach ($patt as $key => $value) {
                                              if($key=='foo'){
                                                $checkerArr[] = "'%".$value."%'";
                                                }
                                            }
                                        }


                         $rawsql = "SELECT * from ".$database_name.".".$table_name." where farmer_fname !='' ";
                        foreach ($checkerArr as $key => $val) {
                                   
                                if($key==0){
                                   $rawsql .= " AND rsbsa_control_number like ".$val;
                                }else{
                                    $rawsql .= " OR rsbsa_control_number like ".$val;
                                    
                                }
                        } //FOREACH
                        
                        $rawsql .= " and status = '1'";
                        $rawsql .= "  order by rsbsa_control_number asc, farmer_fname asc, 
                                     farmer_lname asc, farmer_mname asc";
                        //dd($rawsql);
                         $list = DB::select(DB::raw($rawsql));
                        
                    }
                    elseif($rsbsa_menthod==0){ //method 0
                        $list = DB::table($database_name.".".$table_name)
                        ->where('farmer_fname','!=','""') 
                        ->where('rsbsa_control_number','like', $rsba_pattern)
                        ->where("status", "1")
                        ->orderBy('rsbsa_control_number', 'ASC')
                        ->orderBy('farmer_fname', 'ASC')        
                        ->orderBy('farmer_lname', 'ASC')
                        ->orderBy('farmer_mname', 'ASC')    
                        ->get();
                    }elseif($rsbsa_menthod==2){ //method 2
                        $list = DB::table($GLOBALS['season_prefix'].'prv_'.$region_code.$province_code.'.farmer_profile')
                                                    ->select(DB::raw("DISTINCT (farmer_profile.rsbsa_control_no) as rsbsa_control_number"),
                                                        "farmer_profile.lastName as farmer_lname", 
                                                        "farmer_profile.firstName as farmer_fname", 
                                                        "farmer_profile.midName as farmer_mname", 
                                                        "farmer_profile.extName", 
                                                        "farmer_profile.sex",
                                                        "farmer_profile.actual_area", 
                                                        "farmer_profile.farmerID", 
                                                        "farmer_profile.distributionID", 
                                                        "other_info.birthdate", 
                                                        "other_info.mother_lname", 
                                                        "other_info.mother_fname", 
                                                        "other_info.mother_mname")



                                                    ->join($GLOBALS['season_prefix'].'prv_'.$region_code.$province_code.'.other_info', function($join){
                                                        $join->on("other_info.farmer_id", "=", "farmer_profile.farmerID")
                                                            ->on("farmer_profile.rsbsa_control_no", "=" ,"other_info.rsbsa_control_no");
                                                    })
                                                    ->where('farmer_profile.rsbsa_control_no', 'like', $rsba_pattern)
                                                    ->where('farmer_profile.firstName','!=','""')
                                                    ->where("status", "1")
                                                    ->where("farmer_profile.distributionID", "like", "R".$region_code."%")
                                                    ->orderBy(DB::raw("TRIM(LEFT(farmer_profile.rsbsa_control_no,12))"), 'ASC') 
                                                    ->orderBy(DB::raw('TRIM(farmer_profile.lastName)'), 'ASC')
                                                    ->orderBy(DB::raw('TRIM(farmer_profile.firstName)'), 'ASC')        
                                                    ->orderBy(DB::raw('TRIM(farmer_profile.midName)'), 'ASC')
                                                  
                                                    ->get();
                            //dd($list);


                    }    
                }
                else{
                        $list = DB::table($database_name.".".$table_name)
                        ->orderBy('rsbsa_control_number', 'ASC')
                        ->orderBy('farmer_fname', 'ASC')        
                        ->orderBy('farmer_lname', 'ASC')
                        ->orderBy('farmer_mname', 'ASC')    
                        ->get();  
                }


           


            $beneficiary = count($list);

        //dd($list);
        return $beneficiary; 
    }

    function changeRegionName($region){
        $data = array(
            "REGION I" => "ILOCOS",
            "REGION II" => "CAGAYAN VALLEY",
            "REGION III" => "CENTRAL LUZON",
            "REGION IV-A" => "CALABARZON",
            "REGION IV-B" => "MIMAROPA",
            "REGION V" => "BICOL",
            "REGION VI" => "WESTERN VISAYAS",
            "REGION VII" => "CENTRAL VISAYAS",
            "REGION VIII" => "EASTERN VISAYAS",
            "REGION IX" => "ZAMBOANGA PENINSULA",
            "REGION X" => "NORTHERN MINDANAO",
            "REGION XI" => "DAVAO",
            "REGION XII" => "BARMM",
            "NCR" => "NCR",
            "CAR" => "CAR",
            "BARMM" => "BARMM",
            "REGION XIII" => "CARAGA",
        );

        return $data;

    } 


}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Excel;
use Auth;
class preListController extends Controller
{

    function searchMultiArray($array, $key, $value) {
        foreach($array as $subarray) {
            if(isset($subarray[$key]) && $subarray[$key] === $value) {
                return $subarray;
            }
            if(is_array($subarray)) {
                $result = searchMultiArray($subarray, $key, $value);
                if($result !== false) {
                    return $result;
                }
            }
        }
        return false;
    }

    public function pre_list_index(){
        if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username== "e.lopez" || Auth::user()->username == "sed_access"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
            ->select('rcef_nrp_provinces.*')
            // ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
            ->groupBy("rcef_nrp_provinces.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
            

        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{

              

                $provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();

                $prv_data = json_decode(json_encode($provinces),true);
               $provinces =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->whereIn("province", $prv_data)
                    ->groupBy("province")
                    ->get();

            }
        }


                //  $mss = "No Station Tagged";
                // return view('utility.pageClosed',compact("mss"));

        return view("farmer.prelist.prelist_excel")
            ->with("province", $provinces);


    }

    private function search_to_array($array, $key, $value) {
        $results = array();
    
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }
    
            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search_to_array($subarray, $key, $value));
            }
        }
    
        return $results;
    }

    

    public function pre_list_farmer(Request $request){
        $province = $request->province;
        $municipality = $request->municipality;
        if($request->municipality == "all"){
            $request->municipality = "%";
        }

        $prv_id = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        ->where("province", $request->province)
        ->where("municipality", "LIKE", $request->municipality."%")
        ->first();
        $prv_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->get();   
       

        
        if($prv_id != null){
            $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("prv_code",$prv_id->prv_code)->get();
            $lib_prv = json_decode(json_encode($lib_prv), true);


            $lib_bgy = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
            ->where("geocode_municipality","LIKE", $prv_id->prv_code."%")
            ->get();
            $lib_bgy = json_decode(json_encode($lib_bgy), true);

            // dd($lib_prv);


            $db_table = $GLOBALS['season_prefix']."prv_".$prv_id->prv_code;
            $excel_array = array();
            $schema_check = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $db_table)
                ->where("TABLE_NAME", "farmer_information_final_may")
                ->first();
                if($schema_check != null){
                    $tbl_farmer = "farmer_information_final_may";
                }else{
                    $tbl_farmer = "farmer_information_final";
                }


                if($request->municipality == "%"){
                    $list = DB::table($db_table.".".$tbl_farmer)
                        ->select("claiming_prv","claiming_prv as claiming_muni","rcef_id", "claiming_brgy","no_of_parcels","assigned_rsbsa as rsbsa_grounds","rsbsa_control_no as rsbsa_ffrs", "lastName", "firstName", "midName", "extName", "birthdate", "tel_no", "is_ip", "final_area as claimable_area" )
                      
                        ->where("rcef_id", "!=", "")
                        ->orderBy(DB::raw("SUBSTRING(rsbsa_control_no,1,8)"))
                        ->orderBy("lastName")
                        ->orderBy("firstName")
                        ->orderBy("midName")
                        ->orderBy("extName")
                        ->get();
                }else{
                    $claim_prv = substr($prv_id->prv,0,2)."-".substr($prv_id->prv,2,2)."-".substr($prv_id->prv,4,2);
                    
                    $list = DB::table($db_table.".".$tbl_farmer)
                        ->select("claiming_prv","claiming_prv as claiming_muni","rcef_id", "claiming_brgy","no_of_parcels", "assigned_rsbsa as rsbsa_grounds","rsbsa_control_no as rsbsa_ffrs","lastName", "firstName", "midName", "extName", "birthdate", "tel_no", "is_ip", "final_area as claimable_area" )
                       
                        ->where("rcef_id", "!=", "")
                        ->where("claiming_prv", "LIKE", $claim_prv)
                        ->orderBy(DB::raw("SUBSTRING(rsbsa_control_no,1,8)"))
                        ->orderBy("lastName")
                        ->orderBy("firstName")
                        ->orderBy("midName")
                        ->orderBy("extName")
                        ->get();
                }
       
                foreach($list as $data){
                        $claiming =  $this->search_to_array($lib_prv, "prv", str_replace("-","",$data->claiming_prv));
                        $data->claiming_prv = $claiming[0]["province"];
                        $data->claiming_muni = $claiming[0]["municipality"];

                        $claiming_bgy =  $this->search_to_array($lib_bgy, "geocode_brgy", str_replace("-","",$data->claiming_brgy));
                        
                        if(count($claiming_bgy) > 0){
                            $data->claiming_brgy = $claiming_bgy[0]["name"];
                        }else{
                            $data->claiming_brgy = "EMPTY";
                        }
                }

                
            

                $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
                Excel::create("PRELIST_FARMER_LIST_".$province."-".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                   $excel->sheet("Beneficiary List", function($sheet) use ($excel_data) {
                       $sheet->fromArray($excel_data);
                   }); 
               })->download('xlsx');
       
       
            }





    }
    
    public function pre_list_farmer_2(Request $request){
        $province = $request->province;
        $municipality = $request->municipality;
        if($request->municipality == "all"){
            $request->municipality = "%";
        }
        
        $prv_id = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->where("municipality", "LIKE", $request->municipality."%")
            ->first();
        $prv_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->get();   


            if($prv_id != null){
                $db_table = $GLOBALS['season_prefix']."prv_".$prv_id->prv_code;
                
                $excel_array = array();
                $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $db_table)
                    ->where("TABLE_NAME", "farmer_information_final_2")
                    // ->where("COLUMN_NAME", "claiming_prv")
                    ->first();
                    if($schema_check != null){
                        $tbl_farmer = "farmer_information_final_2";
                        $is_parcelary = true;
                    }else{
                        $tbl_farmer = "farmer_information_final";
                        $schema_check_claiming = DB::table("information_schema.COLUMNS")
                            ->where("TABLE_SCHEMA", $db_table)
                            ->where("TABLE_NAME", $tbl_farmer)
                            ->where("COLUMN_NAME", "claiming_prv")
                            ->first();
                            if($schema_check_claiming != null){
                                $is_parcelary = true;   
                            }else{
                                $is_parcelary = false;
                            }
                    }
                 
                    if($is_parcelary){
                        if($request->municipality == "%"){
                            $list = DB::table($db_table.".".$tbl_farmer)
                                ->select("claiming_prv","rcef_id", "rsbsa_control_no as rsbsa_grounds","rsbsa_control_no as rsbsa_ffrs", "lastName", "firstName", "midName", "extName", "province as residence_province", "municipality as residence_municipality", "tel_no", "is_ip", "final_area as claimable_area", "final_claimable as claimable_bags" )
                               
                                ->where("rcef_id", "!=", "")
                                ->orderBy(DB::raw("SUBSTRING(rsbsa_control_no,1,8)"))
                                ->orderBy("lastName")
                                ->orderBy("firstName")
                                ->orderBy("midName")
                                ->orderBy("extName")
                                ->get();
                        }else{
                            $claim_prv = substr($prv_id->prv,0,2)."-".substr($prv_id->prv,2,2)."-".substr($prv_id->prv,4,2);
                            
                            $list = DB::table($db_table.".".$tbl_farmer)
                                ->select("claiming_prv","rcef_id", "rsbsa_control_no as rsbsa_grounds","rsbsa_control_no as rsbsa_ffrs","lastName", "firstName", "midName", "extName", "province as residence_province", "municipality as residence_municipality", "tel_no", "is_ip", "final_area as claimable_area", "final_claimable as claimable_bags" )
                                
                                ->where("rcef_id", "!=", "")
                                ->where("claiming_prv", "LIKE", $claim_prv)
                                ->orderBy(DB::raw("SUBSTRING(rsbsa_control_no,1,8)"))
                                ->orderBy("lastName")
                                ->orderBy("firstName")
                                ->orderBy("midName")
                                ->orderBy("extName")
                                ->get();
                        }
                    }else{
                        if($request->municipality == "%"){
                        
                            $list = DB::table($db_table.".".$tbl_farmer)
                             ->select("rcef_id", "rsbsa_control_no as rsbsa_grounds","rsbsa_control_no as rsbsa_ffrs", "lastName", "firstName", "midName", "extName", "province as residence_province", "municipality as residence_municipality", "tel_no", "is_ip", "final_area as claimable_area", "final_claimable as claimable_bags" )
                              
                                ->where("rcef_id", "!=", "")
                                ->orderBy(DB::raw("SUBSTRING(rsbsa_control_no,1,8)"))
                                ->orderBy("lastName")
                                ->orderBy("firstName")
                                ->orderBy("midName")
                                ->orderBy("extName")
                                ->get();
                                
                        }else{

                            
                            $list = DB::table($db_table.".".$tbl_farmer)
                                ->select("rcef_id", "rsbsa_control_no as rsbsa_grounds","rsbsa_control_no as rsbsa_ffrs","lastName", "firstName", "midName", "extName", "province as residence_province", "municipality as residence_municipality", "tel_no", "is_ip", "final_area as claimable_area", "final_claimable as claimable_bags" )
                                
                                ->where("rcef_id", "!=", "")
                                ->orderBy(DB::raw("SUBSTRING(rsbsa_control_no,1,8)"))
                                ->orderBy("lastName")
                                ->orderBy("firstName")
                                ->orderBy("midName")
                                ->orderBy("extName")
                                ->get();
                        }
                    }
                   

                    foreach($list as $data){
                        
                        if(isset($data->claiming_prv)){
                            $mun =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                            ->where("prv", str_replace("-","", $data->claiming_prv))
                            ->first();
                            
                             $data->claiming_prv  = $mun->province.", ".$mun->municipality;

                        }
                    
                    }

                    

                    $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
                    Excel::create("PRELIST_FARMER_LIST_".$province."-".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                       $excel->sheet("Beneficiary List", function($sheet) use ($excel_data) {
                           $sheet->fromArray($excel_data);
                       }); 
                   })->download('xlsx');


            }


    }
}

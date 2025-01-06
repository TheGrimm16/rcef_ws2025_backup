<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
class cssController extends Controller
{

    public function insertCss(Request $request){
        dd('Try again later.');
        $request = json_decode(request()->getContent(), true);

       if($request["api"] == "1q988uN743w0diRjbc3%6IBzOloUUSc1"){
        // con/bep

        unset($request['api']);
            if($request["type"] == "con"){
                $check_response = DB::table($GLOBALS['season_prefix']."rcep_css.conv_response")
                    ->where("rcef_id", $request["rcef_id"])
                    ->first();

                    if($check_response == null){
                        try {
                            unset($request['type']);
                            $save = DB::table($GLOBALS['season_prefix']."rcep_css.conv_response")
                            ->insert($request);

                            return json_encode("Success");
                        } catch (\Throwable $th) {
                            return json_encode("error on saving");
                        }
                    }else{
                        return json_encode("already encoded");
                    }
            }elseif($request["type"] == "bep"){
                  $check_response = DB::table($GLOBALS['season_prefix']."rcep_css.ebinhi_response")
                    ->where("rcef_id", $request["rcef_id"])
                    ->first();

                    if($check_response == null){
                        try {
                            unset($request['type']);
                            $save = DB::table($GLOBALS['season_prefix']."rcep_css.ebinhi_response")
                            ->insert($request);

                            return json_encode("Success");
                        } catch (\Throwable $th) {
                            return json_encode("error on saving");
                        }
                    }else{
                        return json_encode("already encoded");
                    }





            }



       }else{
        return "ERR API";
       }



    }

    public function nrpInsert(Request $request){
        $request = json_decode(request()->getContent(), true);

        if($request["XapiKey"] != "1q988uN743w0diRjbc3%6IBzOloUUSc1"){
            return null;
        }

        unset($request["XapiKey"]);
        $check_response = DB::table($GLOBALS['season_prefix']."rcep_css.nrp_response")
            ->where("rcef_id", $request["rcef_id"])
            ->first();

            if($check_response == null){
                try {
                    $save = DB::table($GLOBALS['season_prefix']."rcep_css.nrp_response")
                    ->insert($request);

                    return 0;
                } catch (\Throwable $th) {
                    return json_encode("error on saving");
                }
            }else{
                return json_encode("already encoded");
            }
    }


    public function cssLocation($province_name,$type){
        
        if($type == "province"){
            $list = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
            ->groupBy("province")
            ->orderBy("region_sort")
            ->get();
            
            return json_encode($list);
        }elseif($type == "municipality"){
            
            $list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where("province", $province_name)
            ->groupBy("municipality")
            ->orderBy("municipality")
            ->get();

            
            return json_encode($list);

        }else{
            return NULL;
        }
        
            
            



    }


    public function ebinhi_get_farmer($api_key,$search_value){
        if($api_key == "b3nh!@2023"){
            // if($search_value == "all"){
            //     $search_value = "%";
            // }
            

            $responses = DB::table($GLOBALS['season_prefix']."rcep_css.ebinhi_response")
            ->select("rcef_id")
            ->groupBy("rcef_id")
            ->get();
            $responses = json_decode(json_encode($responses),true);


            $list = DB::table($GLOBALS['season_prefix']."rcep_paymaya.released_list")
                    ->where("paymaya_code", "LIKE", "%".$search_value."%")
                    ->whereNotIn("paymaya_code", $responses)
                    ->orWhere(DB::raw("CONCAT(lastname,', ',firstname,' ',middname)"),"LIKE", "%".$search_value."%")  
                    ->whereNotIn("paymaya_code", $responses)
                    ->groupBy("paymaya_code")
                    ->limit(10)
                    ->get();

            return json_encode($list);

        }else{
            return "Wrong API";
        }


    }


    public function checkIfReleased($api_key,$rcef_id,$province, $municipality){
        if($api_key != "c0nv3@2023"){
            return "false";
        
        }
        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        ->where("province", $province)
        ->where("municipality", $municipality)
        ->first();

        if($prv != null){

            $releases =  DB::table($GLOBALS['season_prefix']."prv_".$prv->prv_code.".new_released")
            ->select("rcef_id")
            ->where("rcef_id", $rcef_id)
            ->get();

                if(count($releases)>0){
                return  "true";
                }else{
                return "false";
                }
        }else{
            return "false";
        }

       

    }

    public function conv_get_farmer($api_key,$province,$municipality,$search_value){
        if($api_key == "c0nv3@2023"){
            if($search_value == "all"){
                $search_value = "%";
            }

            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("province", $province)
                    ->where("municipality", $municipality)
                    ->first();
          
                if($prv != null){

                    $responses = DB::table($GLOBALS['season_prefix']."rcep_css.conv_response")
                    ->select("rcef_id")
                    ->where("province", $province)
                    ->where("municipality", $municipality)
                    
                    ->groupBy("rcef_id")
                    ->get();
                    $responses = json_decode(json_encode($responses),true);
                    // dd($responses);
                    $rsbsa_pattern = substr($prv->prv,0,2)."-".substr($prv->prv,2,2)."-".substr($prv->prv,4,2);

                //    $releases =  DB::table($GLOBALS['season_prefix']."prv_".$prv->prv_code.".new_released")
                //         ->select("rcef_id")
                //         ->groupBy("rcef_id")
                //         ->where("province", $province)
                //     ->where("municipality", $municipality)
                    
                //         ->get();
                //  $releases = json_decode(json_encode($releases),true);
                    // dd($releases);

                    $db_name = $GLOBALS['season_prefix']."prv_".$prv->prv_code.".farmer_information_final";
                    // dd($db_name);
                        $list = DB::table($db_name)
                            ->where("rsbsa_control_no","LIKE", $rsbsa_pattern."%")
                            ->where("rcef_id", "LIKE", "%".$search_value."%")
                            ->whereNotIn("rcef_id", $responses)
                            // ->whereIn("rcef_id", $releases)
                            ->orWhere(DB::raw("CONCAT(lastName,', ',firstName,' ',midName)"),"LIKE", "%".$search_value."%")    
                            ->where("rsbsa_control_no","LIKE", $rsbsa_pattern."%")
                            ->whereNotIn("rcef_id", $responses)
                            // ->whereIn("rcef_id", $releases)
                            ->limit(10)
                            ->get();
                    

                    
      
              
                    return json_encode($list);



                }else{
                    return "NO PRV";
                }

   

        }else{
            return "Wrong API";
        }


    }



}

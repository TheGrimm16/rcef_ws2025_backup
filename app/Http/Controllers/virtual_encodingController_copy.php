<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Datatables;
use Auth;
class virtual_encodingController extends Controller
{

    public function index_fca(){

        // if(Auth::user()->roles->first()->name != "rcef-programmer"){
        //     $mss = "Under Development";
        //         return view("utility.pageClosed")
        //     ->with("mss",$mss);
        // }

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_prv.province')
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{



                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();
           
              
                


                foreach($user_provinces as $key=> $pr){
             

                    

                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");
                  
                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
          
                    if($schema_check == null){
                        unset($user_provinces[$key]);
                    }

                  


                }
           
            }
        }
 
        $user_provinces = json_decode(json_encode($user_provinces), true);

        $provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->whereIn("province", $user_provinces)
                ->groupBy('province')->get();
           
        return view('virtual_encoding.index_fca',compact('provinces'));
    }

    public function index_lowland(){

        // if(Auth::user()->roles->first()->name != "rcef-programmer"){
        //     $mss = "Under Development";
        //         return view("utility.pageClosed")
        //     ->with("mss",$mss);
        // }

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_prv.province')
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{



                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();
           
              
                


                foreach($user_provinces as $key=> $pr){
             

                    

                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");
                  
                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
          
                    if($schema_check == null){
                        unset($user_provinces[$key]);
                    }

                  


                }
           
            }
        }
 
        $user_provinces = json_decode(json_encode($user_provinces), true);

        $provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->whereIn("province", $user_provinces)
                ->groupBy('province')->get();
           
        return view('virtual_encoding.index_lowland',compact('provinces'));
    }

    public function searchMembers_lowland(Request $request){
        $temp = str_replace('-','',$request->claiming_prv);
        $prv = substr($temp,0,4);

        $getLowlanders = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
        ->select('db_ref','rcef_id','rsbsa_control_no', DB::raw("CONCAT(lastName, ', ', firstName, ' ', midName, ' ', extName) as farmerName"), 'final_area as area', 'province', 'municipality', 'claiming_prv')
        ->where('db_ref','!=',$request->db_ref)
        ->where('claiming_prv','LIKE',$request->claiming_prv)
        ->where('is_new','!=', 3)
        ->where('final_area','<', 0.1)
        ->where('final_area','>', 0)
        ->orderBy('municipality')
        ->orderBy('rsbsa_control_no')
        // ->toSql();
        ->get();

        // dd($getLowlanders);

        foreach($getLowlanders as $low){
            $prv = str_replace('-','',$low->claiming_prv);
            $getLocation = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('prv',$prv)
            ->first();
            $low->province = $getLocation->province;
            $low->municipality = $getLocation->municipality;
        }

        $getLowlanders = collect($getLowlanders);

        return Datatables::of($getLowlanders)
        ->addColumn('action', function($row){
            return  "<input type='checkbox' class='members' data-dbref='".$row->db_ref."' data-rsbsa='".$row->rsbsa_control_no."' data-area='".$row->area."'>";            
        })
            ->make(true);
    }

    public function index(){
        // if(Auth::user()->roles->first()->name != "rcef-programmer"){
        //     $mss = "Under Development";
        //         return view("utility.pageClosed")
        //     ->with("mss",$mss);
        // }

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_prv.province')
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{



                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();
           
              
                


                foreach($user_provinces as $key=> $pr){
             

                    

                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");
                  
                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
          
                    if($schema_check == null){
                        unset($user_provinces[$key]);
                    }

                  


                }
           
            }
        }
 
        $user_provinces = json_decode(json_encode($user_provinces), true);

        $provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->whereIn("province", $user_provinces)
                ->groupBy('province')->get();
           
        return view('virtual_encoding.index',compact('provinces'));
    }

    public function index_homeAddressClaim(){
        if(Auth::user()->roles->first()->name != "rcef-programmer"){
            $mss = "Under Development";
                return view("utility.pageClosed")
            ->with("mss",$mss);
        }

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_prv.province')
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{



                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();
           
              
                


                foreach($user_provinces as $key=> $pr){
             

                    

                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");
                  
                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
          
                    if($schema_check == null){
                        unset($user_provinces[$key]);
                    }

                  


                }
           
            }
        }
 
        $user_provinces = json_decode(json_encode($user_provinces), true);

        $provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->whereIn("province", $user_provinces)
                ->groupBy('province')->get();
           
        return view('virtual_encoding.index_homeAddressClaim',compact('provinces'));
    }

    public function index_trespass(){
        if(Auth::user()->roles->first()->name != "rcef-programmer"){
            $mss = "Under Development";
                return view("utility.pageClosed")
            ->with("mss",$mss);
        }

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_prv.province')
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{



                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();
           
              
                


                foreach($user_provinces as $key=> $pr){
             

                    

                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");
                  
                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
          
                    if($schema_check == null){
                        unset($user_provinces[$key]);
                    }

                  


                }
           
            }
        }
 
        $user_provinces = json_decode(json_encode($user_provinces), true);

        $provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->whereIn("province", $user_provinces)
                ->groupBy('province')->get();
           
        return view('virtual_encoding.index_trespass',compact('provinces'));
    }

    public function search(Request $request){
        
        $lib_prv = DB::connection("delivery_inspection_db")->table("lib_prv")
            ->where("province" , $request->prv)
            ->first();

        $farmer_list = array();

        if($lib_prv != null){
            $schema_check = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code)
                ->where("TABLE_NAME", 'farmer_information_final')
                ->first();
            if($schema_check != null ){
                $farmer_list = DB::table($schema_check->TABLE_SCHEMA.".farmer_information_final")
                    ->select(DB::raw("CONCAT(rcef_id,' - ', lastName, ' ',firstName, ' ',midName, ' ',extName ) as name"))
                    ->get();
                $farmer_list = json_decode(json_encode($farmer_list), true);
                // $farmer_list = array_values($farmer_list);
                $farmer_list = array_map('current', $farmer_list);

                // print_r($flattenedArray);
            }

        }

        // dd($farmer_list);
        return json_encode($farmer_list);

    }
    function searchFarmer_fca(Request $request){
        $lib = DB::connection('delivery_inspection_db')->table('lib_prv')
            // ->where("municipality", $request->municipality)
            ->where("province", $request->province)
            ->first();

         $search_value =    $request->search_bar;
        if($search_value == ""){
            $search_value = "%";
        }
   
        $prv_db = $lib->prv_code;

        if($lib != null){
            // $rsbsa_pattern = $lib->regCode."-".$lib->provCode."-".$lib->munCode;
            $rsbsa_pattern = "%";
      
          
            //  dd($prv_db);
            $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
            ->select("rcef_id","db_ref","rsbsa_control_no as rsbsa",DB::raw("UPPER(CONCAT(lastName,', ',firstName,' ',midName,' ',extName)) as name"),DB::raw("CONCAT(province,', ',municipality,' ',brgy_name) as address"), 'final_area', DB::raw("UPPER(SUBSTR(sex,1,1)) as sex"), 'birthdate' )
            // ->where("lastName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           
            // ->orWhere("firstName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
            
            // ->orWhere("midName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
          
            // //rsbsa
            // ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           


            // ->orWhere("rcef_id", "LIKE", $search_value)
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
        
            // ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")

            ->where(function ($query) use ($search_value) {
                $query->where("lastName", "LIKE", $search_value.'%')
                    ->orWhere("firstName", "LIKE", $search_value.'%')
                    ->orWhere("midName", "LIKE", $search_value.'%')
                    ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                    ->orWhere("rcef_id", $search_value)
                    ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value.'%');
            })
            ->where('is_new', 9)
            ->orderBy("lastName")
            ->orderBy("firstName")
            ->groupBy("rsbsa_control_no")
            ->groupBy("firstName")
            ->groupBy("lastName")
            ->groupBy("midName")
            ->groupBy("birthdate")
            // ->groupBy("claiming_prv")
            
            ->get();

           
            $farmer_info = collect($farmer_info);
            return Datatables::of($farmer_info)
            ->addColumn('action', function($row) use ($prv_db) {
                $tbl = "farmer_information_final";
                $btn = "<a class='btn btn-success' style='width:100%;'><i class='fa fa-thumbs-o-up' aria-hidden='true' onclick='select_farmer(".'"'.$row->db_ref.'"'.', "'.$prv_db.'"'."); '> Select</i></a>";
                $btn .= "<a class='btn btn-success' style='width:100%;'><i class='fa fa-external-link-square' aria-hidden='true'  onclick='show_parcelary(".'"'.$prv_db.'"'.',"'.$row->db_ref.'"'.',"'.$tbl.'"'.");'> Check Parcelary</i></a>";

                 return $btn;
             })
                ->make(true);
        }        
    }

    function searchFarmer_lowland(Request $request){
        $lib = DB::connection('delivery_inspection_db')->table('lib_prv')
            // ->where("municipality", $request->municipality)
            ->where("province", $request->province)
            ->first();

         $search_value =    $request->search_bar;
        if($search_value == ""){
            $search_value = "%";
        }
   
        $prv_db = $lib->prv_code;

        if($lib != null){
            // $rsbsa_pattern = $lib->regCode."-".$lib->provCode."-".$lib->munCode;
            $rsbsa_pattern = "%";
      
          
            //  dd($prv_db);
            $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
            ->select("rcef_id","db_ref","rsbsa_control_no as rsbsa",DB::raw("UPPER(CONCAT(lastName,', ',firstName,' ',midName,' ',extName)) as name"),DB::raw("CONCAT(province,', ',municipality,' ',brgy_name) as address"), 'final_area', DB::raw("UPPER(SUBSTR(sex,1,1)) as sex"), 'birthdate' )
            // ->where("lastName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           
            // ->orWhere("firstName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
            
            // ->orWhere("midName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
          
            // //rsbsa
            // ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           


            // ->orWhere("rcef_id", "LIKE", $search_value)
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
        
            // ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")

            ->where(function ($query) use ($search_value) {
                $query->where("lastName", "LIKE", $search_value.'%')
                    ->orWhere("firstName", "LIKE", $search_value.'%')
                    ->orWhere("midName", "LIKE", $search_value.'%')
                    ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                    ->orWhere("rcef_id", $search_value)
                    ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value.'%');
            })
            ->where('is_new','!=', 3)
            ->where('final_area','<', 0.1)
            ->where('final_area','>', 0)
            ->orderBy("lastName")
            ->orderBy("firstName")
            ->groupBy("rsbsa_control_no")
            ->groupBy("firstName")
            ->groupBy("lastName")
            ->groupBy("midName")
            ->groupBy("birthdate")
            // ->groupBy("claiming_prv")
            
            ->get();

           
            $farmer_info = collect($farmer_info);
            return Datatables::of($farmer_info)
            ->addColumn('action', function($row) use ($prv_db) {
                $tbl = "farmer_information_final";
                $btn = "<a class='btn btn-success' style='width:100%;'><i class='fa fa-thumbs-o-up' aria-hidden='true' onclick='select_farmer(".'"'.$row->db_ref.'"'.', "'.$prv_db.'"'."); '> Select</i></a>";
                $btn .= "<a class='btn btn-success' style='width:100%;'><i class='fa fa-external-link-square' aria-hidden='true'  onclick='show_parcelary(".'"'.$prv_db.'"'.',"'.$row->db_ref.'"'.',"'.$tbl.'"'.");'> Check Parcelary</i></a>";

                 return $btn;
             })
                ->make(true);
        }        
    }

    function searchFarmer_homeAddressClaim(Request $request){
        $lib = DB::connection('delivery_inspection_db')->table('lib_prv')
            // ->where("municipality", $request->municipality)
            ->where("province", $request->province)
            ->first();

         $search_value =    $request->search_bar;
        if($search_value == ""){
            $search_value = "%";
        }
   
        $prv_db = $lib->prv_code;

        if($lib != null){
            // $rsbsa_pattern = $lib->regCode."-".$lib->provCode."-".$lib->munCode;
            $rsbsa_pattern = "%";
      
          
            //  dd($prv_db);
            $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
            ->select("rcef_id","db_ref","rsbsa_control_no as rsbsa",DB::raw("UPPER(CONCAT(lastName,', ',firstName,' ',midName,' ',extName)) as name"),DB::raw("CONCAT(province,', ',municipality,' ',brgy_name) as address"), 'final_area', DB::raw("UPPER(SUBSTR(sex,1,1)) as sex"), 'birthdate' )
            // ->where("lastName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           
            // ->orWhere("firstName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
            
            // ->orWhere("midName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
          
            // //rsbsa
            // ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           


            // ->orWhere("rcef_id", "LIKE", $search_value)
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
        
            // ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")

            ->where(function ($query) use ($search_value) {
                $query->where("lastName", "LIKE", $search_value.'%')
                    ->orWhere("firstName", "LIKE", $search_value.'%')
                    ->orWhere("midName", "LIKE", $search_value.'%')
                    ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                    ->orWhere("rcef_id", $search_value)
                    ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value.'%');
            })
            ->where("is_new", 7)
            ->orderBy("lastName")
            ->orderBy("firstName")
            ->groupBy("rsbsa_control_no")
            ->groupBy("firstName")
            ->groupBy("lastName")
            ->groupBy("midName")
            ->groupBy("birthdate")
            // ->limit(10)
            ->get();

           
            $farmer_info = collect($farmer_info);
            return Datatables::of($farmer_info)
            ->addColumn('action', function($row) use ($prv_db) {
                $tbl = "farmer_information_final";
                $btn = "<a class='btn btn-success' style='width:100%;'><i class='fa fa-thumbs-o-up' aria-hidden='true' onclick='select_farmer(".'"'.$row->db_ref.'"'.', "'.$prv_db.'"'."); '> Select</i></a>";
                $btn .= "<a class='btn btn-success' style='width:100%;'><i class='fa fa-external-link-square' aria-hidden='true'  onclick='show_parcelary(".'"'.$prv_db.'"'.',"'.$row->db_ref.'"'.',"'.$tbl.'"'.");'> Check Parcelary</i></a>";

                return $btn;
             })
                ->make(true);
        }        
    }

    public function checkPreviousHomeClaim(Request $request){
        $prefix = $GLOBALS['season_prefix'];
        $ffrs_data = DB::table($prefix."prv_".$request->prv.".farmer_information_final")
       ->where("db_ref", $request->db_ref)
       ->first();
        if(strlen($ffrs_data->geo_code) == 8)
        {
            $homeAddress = '0'.$ffrs_data->geo_code;
            $homeAddress = substr($homeAddress,0,6);
        }
        else
        {
            $homeAddress = $ffrs_data->geo_code;
            $homeAddress = substr($homeAddress,0,6);
        }
        $claiming_prv = str_replace('-','',$ffrs_data->claiming_prv);
        if($claiming_prv != $homeAddress){
            $checkReleased = DB::table($prefix."prv_".$request->prv.".new_released")
            ->where('prv_dropoff_id', 'LIKE', $homeAddress."%")
            ->where('db_ref','LIKE',$request->db_ref)
            ->get();
    
            if($checkReleased)
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
        else{
            return 0;
        }

    }


    function searchFarmer(Request $request){
        $lib = DB::connection('delivery_inspection_db')->table('lib_prv')
            // ->where("municipality", $request->municipality)
            ->where("province", $request->province)
            ->first();

         $search_value =    $request->search_bar;
        if($search_value == ""){
            $search_value = "%";
        }
   
        $prv_db = $lib->prv_code;

        if($lib != null){
            // $rsbsa_pattern = $lib->regCode."-".$lib->provCode."-".$lib->munCode;
            $rsbsa_pattern = "%";
      
          
            //  dd($prv_db);
            $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
            ->select("rcef_id","db_ref","rsbsa_control_no as rsbsa",DB::raw("UPPER(CONCAT(lastName,', ',firstName,' ',midName,' ',extName)) as name"),DB::raw("CONCAT(province,', ',municipality,' ',brgy_name) as address"), 'final_area', DB::raw("UPPER(SUBSTR(sex,1,1)) as sex"), 'birthdate' )
            // ->where("lastName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           
            // ->orWhere("firstName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
            
            // ->orWhere("midName", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
          
            // //rsbsa
            // ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
           


            // ->orWhere("rcef_id", "LIKE", $search_value)
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
        
            // ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
            // // ->where("claiming_prv", "LIKE", $rsbsa_pattern."%")

            ->where(function ($query) use ($search_value) {
                $query->where("lastName", "LIKE", $search_value.'%')
                    ->orWhere("firstName", "LIKE", $search_value.'%')
                    ->orWhere("midName", "LIKE", $search_value.'%')
                    ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                    ->orWhere("rcef_id", $search_value)
                    ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value.'%');
            })
            ->whereIn("is_new", [2, 8])
            ->orderBy("lastName")
            ->orderBy("firstName")
            ->groupBy("rsbsa_control_no")
            ->groupBy("firstName")
            ->groupBy("lastName")
            ->groupBy("midName")
            ->groupBy("birthdate")
            
            
            
            // ->limit(10)
            ->get();

           
            $farmer_info = collect($farmer_info);
            return Datatables::of($farmer_info)
            ->addColumn('action', function($row) use ($prv_db) {
                $tbl = "farmer_information_final";
                $btn = "<a class='btn btn-success' style='width:100%;'><i class='fa fa-thumbs-o-up' aria-hidden='true' onclick='select_farmer(".'"'.$row->db_ref.'"'.', "'.$prv_db.'"'."); '> Select</i></a>";
                $btn .= "<a class='btn btn-success' style='width:100%;'><i class='fa fa-external-link-square' aria-hidden='true'  onclick='show_parcelary(".'"'.$prv_db.'"'.',"'.$row->db_ref.'"'.',"'.$tbl.'"'.");'> Check Parcelary</i></a>";

                return $btn;
             })
                ->make(true);
        }        
    }

    function select_farmer(Request $request){
        $farmer = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information_final")
            ->where('db_ref', $request->db_ref)
            ->first();

        $farmer_data = "false";
        if($farmer != null){
           $farmer_data = $farmer;
           $home_prv = str_replace("-","", substr($farmer->rsbsa_control_no, 0,8));
           $home_add = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('prv',$home_prv )
                ->first();
            $home = "-";
            if($home_add != null){
                $home = $home_add->province.", ".$home_add->municipality;
            }
            $farmer_data->home = $home;

        }
        return json_encode($farmer_data);

    }  

    public function virtual_new_municipality(Request $request){

        $get_municipality_list = DB::connection("delivery_inspection_db")->table("lib_prv")
            ->where("province", $request->province)
            ->orderBy("municipality")
            ->get();

       
            return json_encode($get_municipality_list);
        

    }

    public function virtual_new_brgy(Request $request){
        $municipality = DB::connection("delivery_inspection_db")->table("lib_prv")
            ->where("province", $request->new_province)
            ->where("municipality", $request->new_municipality)
            ->value("prv");

        $brgy = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
            ->where("geocode_municipality", $municipality)
            ->get();

            return json_encode($brgy);
        

    }


    public function view_variety_balance(Request $request){
        $prefix = $GLOBALS['season_prefix'];
        $return_arr = array(
            "status" => 0,
            
            "variety_list" => "",
            "dop_available" => "",
        );
        $dop_available = array();
        $variety_list = array();

        $parcel_list = $request->new_parcel_list;
        $parcel_list = explode("|", $parcel_list);
            foreach($parcel_list as $parcel_data){
                $parcel = explode(";", $parcel_data);


                $inbred = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                ->select("prv_dropoff_id", "dropOffPoint", "prv", "province", "municipality", "seedVariety", DB::raw("SUM(totalBagCount) as totalBag"))
                ->where("province", $parcel[0])
                ->where("municipality", $parcel[1])
                ->groupBy("municipality", "seedVariety")
                ->get();

            foreach($inbred as $bred){
                $prv_release = substr($bred->prv,0,4);

                $release_data = DB::table($prefix."prv_".$prv_release.".new_released")
                ->where("prv_dropoff_id", "LIKE", $bred->prv."%")
                ->where("seed_variety", $bred->seedVariety)
                ->where('category', "INBRED")
                ->sum("bags_claimed");

                $remaining = $bred->totalBag - $release_data;
                $dop_available[$bred->prv_dropoff_id] = $bred->dropOffPoint . " (".$bred->province.", ".$bred->municipality.")";

                array_push($variety_list, array(
                    "prv_id" => $bred->prv,
                    "province" => $parcel[0],
                    "municipality" => $parcel[1],
                    "seedVariety" => $bred->seedVariety,
                    "totalBag" => $bred->totalBag,
                    "release" => $release_data,
                    "balance" => $remaining,
                    "category" => "INBRED"
                ));


             }


            }




        
            $return_arr["status"] = 1;
            $return_arr["variety_list"] = $variety_list;
            $return_arr["dop_available"] = $dop_available;

            return json_encode($return_arr);
    }

    

    public function insert_distribution(Request $request){
        
        DB::beginTransaction();
        try {

     
            $return_result = array(
                "status" => "0",
                "msg" => ""
            );
   

            //code...
            foreach($request->distribution as $key => $release){
                if($key == 0){
                    if($request->served == "false"){
                        $fins = array();
                        $fins["is_new"] = 1;

                        $rsbsa_control_no = $request->virtual_rsbsa_no_new ;
                        $lastName =  $request->last_name_new;
                        $firstName = $request->first_name_new;
                        $midName = $request->middle_name_new;
                        $extName = $request->ext_name_new;
                        $sex = $request->new_sex;
                        $rcef_id = $request->virtual_rcef_id_new;
                        $home_province = $request->new_province;
                        $home_municipality = $request->new_municipality;
                        $home_brgy = $request->new_brgy;
                        $final_area = $request->virtual_final_area;

                        $claiming_prv  = explode(";", $request->virtual_claiming_prv);
                        $claiming_province = $claiming_prv[0];
                        $claiming_municipality = $claiming_prv[1]; 
                        $claiming_brgy = $claiming_prv[2]; //160201001
                        $ip_name = "";
                        $ip = 0;  if($request->ip == "true"){$ip = 1; $ip_name = $request->ip_name;}
                        $pwd = 0; if($request->pwd == "true"){$pwd = 1;}

                        $brgy_name = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
                            ->where("geocode_brgy", $claiming_brgy)
                            ->value("name");

                        
                        $claiming_lib = DB::connection('delivery_inspection_db')->table('lib_prv')
                            ->where("province", $claiming_province)
                            ->where("municipality", $claiming_municipality)
                            ->first();
                        if($claiming_lib != null){
                            $prv_db = $claiming_lib->prv_code;
                            
                                if($rcef_id == ""){
                                    $checker_2=0;
                                    while ($checker_2==0) {
                                        $rcef_id = $prv_db.rand(100000,999999);
                                        $da_farmer_profile =  DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
                                                ->where('rcef_id',$rcef_id)->count(); 
                                        if($da_farmer_profile == 0){
                                            $checker_2 = 1;
                                        }                   
                                    }
                                }

                            $fins["claiming_prv"] = $claiming_lib->regCode."-".$claiming_lib->provCode."-".$claiming_lib->munCode;
                            $fins["claiming_brgy"] = $claiming_brgy;
                            $fins["no_of_parcels"] = 1;
                            $fins["parcel_brgy_info"] = '{ "parcel_id" : "'.$claiming_brgy.'", "parcel_bgy" : "'.$brgy_name.'", "croparea" : "'.$final_area.'"}';
                            $fins["rsbsa_control_no"] = $rsbsa_control_no;
                            $fins["rcef_id"] = $rcef_id;
                            $fins["assigned_rsbsa"] = $rsbsa_control_no;
                            $fins["da_intervention_card"] = $request->da_intervention_card;
                            $fins["lastName"] = $lastName;
                            $fins["firstName"] = $firstName;
                            $fins["midName"] = $midName;
                            $fins["extName"] = $extName;
                            $fins["sex"] = $sex;
                            $fins["birthdate"] = $request->birthdate;
                            $fins["province"] = $home_province;
                            $fins["municipality"] = $home_municipality;
                            $fins["brgy_name"] = $home_brgy;
                            $fins["mother_lname"] = $request->mother_last_name;
                            $fins["mother_fname"] = $request->mother_first_name;
                            $fins["mother_mname"] = $request->mother_mid_name;
                            $fins["mother_suffix"] = $request->mother_ext_name;
                            $fins["tel_no"] = $request->tel_no;
                            $fins["geo_code"] = $home_brgy;
                            $fins["is_pwd"] = $pwd;
                            $fins["is_ip"] = $ip;
                            $fins["tribe_name"] = $ip_name;
                            $fins["data_source"] = "FIMS";
                            
                           
                            $fins["final_area"] = $final_area;
                            $fins["final_claimable"] = ceil($final_area * 2);
                            $fins["is_claimed"] = 0;
                            $fins["total_claimed"] = 0;
                            $fins["total_claimed_area"] = 0;
                            $fins["is_replacement"] = 0;
                            $fins["replacement_area"] = 0;
                            $fins["replacement_bags"] = 0;
                            $fins["replacement_bags_claimed"] = 0;
                            $fins["is_ebinhi"] = 0;
                            $fins["print_count"] = 0;
                            $fins["to_prv_code"] = "";
                            
                            $new_db_ref =  DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final_pending")
                                    ->insertGetId($fins);
                            DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final_pending")
                                ->where("id", $new_db_ref)
                                ->update([
                                    "db_ref" => $new_db_ref
                                ]);
                            $request->db_ref = $new_db_ref;
                            $request->claiming_prv = $claiming_lib->regCode."-".$claiming_lib->provCode."-".$claiming_lib->munCode;
                        }else{
                            $return_result["status"] = "0";
                            $return_result["msg"] = "Claiming Location Un found";
                        
                            return json_encode($return_result);
                        }
                        
                    }  

                }else{
                    //FOR DIST
                
                    $prv_code_parcel = str_replace("-", "", substr($request->claiming_prv, 0,5));

                    $farmer = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final_pending")
                        ->where("db_ref", $request->db_ref)
                        ->first();
                    if($farmer != null){
                        $category = $request->category;
                        $variety = $release[0];
                        $claimed_area = $release[1];
                        $bags_claimed = $release[2];

                        $prv_dropoff_id = $request->dop_selected_vs;
                        $final_area = $request->virtual_final_area;
                        
                        $claiming_prv = $farmer->claiming_prv;
                        $db_ref = $farmer->db_ref;
                        $db_ref_vs = $farmer->db_ref;
                    
                        $rcef_id = $farmer->rcef_id;
                        $rcef_id_vs = $farmer->rcef_id;
                        $rsbsa_control_no = $farmer->rsbsa_control_no;
                        $yield_last_season = '[{"variety":"'.$request->yield_variety.'","area":"'.$request->yield_area.'","bags":"'.$request->yield_bags.'","weight":"'.$request->yield_weight.'","type":"'.$request->yield_type.'","class":"'.$request->yield_class.'"}]';
                        $crop_est = $request->crop_est;
                        $eco_system = $request->eco_system;
                        $water_source = $request->water_source;
                        $planting_date = $request->planting_month.'/'.$request->planting_week;
                        
                        $ip_name = "";
                        $ip = 0;  if($request->ip == "true"){$ip = 1; $ip_name = $request->ip_name;}
                        $pwd = 0; if($request->pwd == "true"){$pwd = 1;}
                        $fca_name = $request->fca_name;
                        $kp_kit = $request->kp_kit;
                        $ayuda_fertilizer = $request->ayuda_fertilizer;
                        $ayuda_incentives = $request->ayuda_incentives;
                        $ayuda_credit = $request->ayuda_credit;
                        $ayuda = "";
                            if($ayuda_fertilizer == "true"){$ayuda .= "fertilizer";}
                            if($ayuda_incentives == "true"){$ayuda .= ",cash_incentives";}
                            if($ayuda_credit == "true"){$ayuda .= ",credit";}
                        $is_representative = 0; if($request->rep){$is_representative = 1;}
                        $rep_name = $request->rep_name;
                        $rep_id = $request->rep_id;
                        $rep_relationship = $request->rep_relationship;
                   
                        $list_version = "7";
        
                        //1 -> normal; 2->insert 3-created a virtual
                        $status_vs = 2;
                        //CLAIMING_PRV
                        $prv_code_parcel = str_replace("-", "", substr($claiming_prv, 0,5));
                        $prv_code_released = substr($prv_dropoff_id,0,4);
        
                        $code_parcel = str_replace("-", "", $claiming_prv);
                        $code_released = substr($prv_dropoff_id,0,6);


                        $release_location= DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                        ->where("prv_dropoff_id", $prv_dropoff_id)
                        ->first();
                            if($release_location == null){
                                $return_result["status"] = "0";
                                $return_result["msg"] = "Release Location Undefined!";
                            
                                return json_encode($return_result);
                          
                            }
                            

                            $seed_stock= DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                ->where("prv_dropoff_id", $prv_dropoff_id)
                                ->where("seedVariety", $variety)
                                ->sum("totalBagCount");
                            if($seed_stock < $bags_claimed ){
                                $return_result["status"] = "0";
                                $return_result["msg"] = "Seed stocks exhausted";
                            
                                return json_encode($return_result);
                                }
                            
                            $dropOffPoint =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                                ->where("prv_dropoff_id", $prv_dropoff_id)
                                ->value("dropOffPoint");


                                if($prv_code_parcel != $prv_code_released){
                                    //NOT THE SAME PROVINCE            
                                    $status_vs = 3;
                                    $farmer_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final_pending")
                                        ->where("db_ref", $db_ref)
                                        ->first();
                                    if($farmer_data != null){
                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final_pending")
                                            ->where("db_ref", $db_ref)
                                            ->update([
                                                "da_intervention_card" => $da_intervention_card,
                                                "birthdate" => $birthdate,
                                                "is_claimed" => 1,
                                                "fca_name" => $fca_name,
                                                "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                "mother_lname" => $mother_last_name,
                                                "mother_fname" => $mother_first_name,
                                                "mother_mname" => $mother_mid_name,
                                                "mother_suffix" => $mother_ext_name,
                                                "tel_no" => $tel_no,
                                                "is_pwd" => $pwd,
                                                "is_ip" => $ip,
                                                "tribe_name" => $ip_name,
                                            ]);
                
                                        //GET CURRENT FARMER ON CLAIMING PRV
                                        $get_db_ref =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                ->where("rsbsa_control_no", $farmer_data->rsbsa_control_no)
                                                ->where("firstName", $farmer_data->firstName)
                                                ->where("midName", $farmer_data->midName)
                                                ->where("lastName", $farmer_data->lastName)
                                                ->where("extName", $farmer_data->extName)
                                                ->where("birthdate", $farmer_data->birthdate)
                                                ->first();
                                        
                                        $list_version = $prv_code_parcel."|".$db_ref;
                                        $final_area = $farmer_data->final_area;
                                        $sex = $farmer_data->sex;
                                        if($get_db_ref != null){
                                            $rcef_id = $get_db_ref->rcef_id;
                                            $db_ref = $get_db_ref->db_ref;
                                            $rsbsa_control_no = $get_db_ref->rsbsa_control_no;
                                        }else{
                                            $return_result["status"] = "0";
                                            $return_result["msg"] = "Cannot Find Farmer Info On tagged Released";
                                        
                                            return json_encode($return_result);

                                        }
                
                                    }else{
                                        $return_result["status"] = "0";
                                        $return_result["msg"] = "Cannot Find Farmer Info";
                                    
                                        return json_encode($return_result);
                                
                                    }
                
                                }
                                else{
    
                                            
    
                                    if($code_parcel != $code_released){
                                        //NOT THE SAME MUNICIPAL
                                        $status_vs = 3;
                                    }


                                    $farmer_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final_pending")
                                        ->where("db_ref", $db_ref)
                                        ->first();
                                    if($farmer_data != null){
                                        $sex = $farmer_data->sex;
                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final_pending")
                                            ->where("db_ref", $db_ref)
                                            ->update([
                                                "is_claimed" => 1,
                                                "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                            ]);
                                    }else{

                                        $return_result["status"] = "0";
                                        $return_result["msg"] = "Cannot Find Farmer Info";
                                    
                                        return json_encode($return_result);
                                    }
                
                
                                }

                                $birthdate= $farmer->birthdate;
                                $sex= $farmer->sex;
                                

                                $release_ref_id =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".new_released_pending")
                                ->insertGetId([
                                    "id" =>  "111111111",
                                    "rcef_id" => $rcef_id,
                                    "db_ref" => $db_ref,
                                    "prv_dropoff_id" => $prv_dropoff_id,
                                    "province" => $release_location->province,
                                    "municipality" => $release_location->municipality,
                                    "dropOffPoint" => $dropOffPoint,
                                    "transaction_code" => "web",
                                    "dataSharing" => 1,
                                    "is_representative" => $is_representative,
                                    "rep_name" => $rep_name,
                                    "rep_id" => $rep_id,
                                    "rep_relation" => $rep_relationship,
                                    "claimed_area" => $claimed_area,
                                    "bags_claimed" => $bags_claimed,
                                    "seed_variety" => $variety,
                                    "recipient_ls" => "-",
                                    "planted_rcvd_seeds_ls" => "-",
                                    "reason_not_planted_rcvd_seeds_ls" => "-",
                                    "yield_area_harvested_ls" => $request->yield_area,
                                    "yield_no_of_bags_ls" => $request->yield_bags,
                                    "yield_wt_per_bag" => $request->yield_weight,
                                    "crop_establishment_cs" => $crop_est,
                                    "seedling_age" => 0,
                                    "ecosystem_cs" => $eco_system,
                                    "ecosystem_source_cs" => $water_source,
                                    "planting_week" => $planting_date,
                                    "has_kp_kit" => $kp_kit,
                                    "other_benefits_received" => $ayuda,
                                    "date_released" => date("Y-m-d"),
                                    "released_by" => Auth::user()->username,
                                    "time_start" => "-",
                                    "time_end" => "-",
                                    "app_version" => "web",
                                    "distribution_type" => "Regular",
                                    "mode" => "search",
                                    "farmer_id_address" => $db_ref,
                                    "content_rsbsa" => $rsbsa_control_no,
                                    "yield_last_season_details" => $yield_last_season,
                                    "category" => $category,
                                    "birthdate"=> $birthdate,
                                    "final_area" => $final_area,
                                    "sex" => $sex,
                                    "list_version" => $list_version,
                                    "status" => $status_vs,
                                    "process_report_status" => "not process"
                                ]);

                                if($status_vs == 3){
    
                                    //GET THE 
                                    
                                    
                                    $prv_location = DB::connection('delivery_inspection_db')->table('lib_prv')
                                        ->where("prv", $code_parcel)
                                        ->first();
                                    $vs_lib_drop_off = "";
                                    $vs_lib_dop_name = "";
                                    
                                    
                                    if($prv_location != null){
                                        $vs_province = $prv_location->province;
                                        $vs_municipality = $prv_location->municipality;
                                        $vs_region = $prv_location->region;
                                       $vs_dop =  DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                                            ->where("municipality", $vs_municipality)
                                            ->where("province", $vs_province)
                                            ->first();
                                        if($vs_dop != null){
                                            $vs_lib_drop_off = $vs_dop->prv_dropoff_id;
                                            $vs_lib_dop_name = $vs_dop->dropOffPoint;
                                        }   
                                    }else{
                                        $return_result["status"] = "0";
                                        $return_result["msg"] = "Server Unreachable";
                                    
                                        return json_encode($return_result);
                                    }

                                    //GET STOCKS DETAILS

                                    $act_batch = "";
                                    $act_prv = "";
                                    $act_region = "";
                                    $act_province = "";
                                    $act_municipality = "";
                                    $act_dop_name = "";

                                    $seed_actual =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                        ->where("prv_dropoff_id", $prv_dropoff_id)
                                        ->where("seedVariety", $variety)
                                        ->first();
                                    if($seed_actual != null ){
                                        $act_batch = $seed_actual->batchTicketNumber;
                                        $act_prv = $seed_actual->prv;
                                        $act_region = $seed_actual->region;
                                        $act_province = $seed_actual->province;
                                        $act_municipality = $seed_actual->municipality;
                                        $act_dop_name = $seed_actual->dropOffPoint;
                                    }


                                    $vs_stock_ins = array(
                                        "batchTicketNumber_ref" => $act_batch,
                                        "prv_ref" => $act_prv,
                                        "region_ref" => $act_region,
                                        "province_ref" => $act_province,
                                        "municipality_ref" => $act_municipality,
                                        "dropOffPoint_ref" => $act_dop_name,
                                        "seedVariety_ref" => $variety,
                                        "prv" => $code_parcel,
                                        "prv_dropoff_id" =>$vs_lib_drop_off ,
                                        "dropOffPoint" =>$vs_lib_dop_name ,
                                        "region" =>$vs_region ,
                                        "province" => $vs_province,
                                        "municipality" => $vs_municipality,
                                        "totalBagCount" => $bags_claimed,
                                        "virtual_release_ref" => $release_ref_id
                                    );



                                    $vs_release_ins = array(
                                        "new_released_id_ref" => $release_ref_id,
                                        "id" =>  "111111111",
                                        "rcef_id" => $rcef_id_vs,
                                        "db_ref" => $db_ref_vs,
                                        "prv_dropoff_id" => $vs_lib_drop_off,
                                        "province" => $vs_province,
                                        "municipality" => $vs_municipality,
                                        "dropOffPoint" => $vs_lib_dop_name,
                                        "transaction_code" => "web",
                                        "dataSharing" => 1,
                                        "is_representative" => $is_representative,
                                        "rep_name" => $rep_name,
                                        "rep_id" => $rep_id,
                                        "rep_relation" => $rep_relationship,
                                        "claimed_area" => $claimed_area,
                                        "bags_claimed" => $bags_claimed,
                                        "seed_variety" => $variety,
                                        "recipient_ls" => "-",
                                        "planted_rcvd_seeds_ls" => "-",
                                        "reason_not_planted_rcvd_seeds_ls" => "-",
                                        "yield_area_harvested_ls" => $request->yield_area,
                                        "yield_no_of_bags_ls" => $request->yield_bags,
                                        "yield_wt_per_bag" => $request->yield_weight,
                                        "crop_establishment_cs" => $crop_est,
                                        "seedling_age" => 0,
                                        "ecosystem_cs" => $eco_system,
                                        "ecosystem_source_cs" => $water_source,
                                        "planting_week" => $planting_date,
                                        "has_kp_kit" => $kp_kit,
                                        "other_benefits_received" => $ayuda,
                                        "date_released" => date("Y-m-d"),
                                        "released_by" => Auth::user()->username,
                                        "time_start" => "-",
                                        "time_end" => "-",
                                        "app_version" => "web",
                                        "distribution_type" => "Regular",
                                        "mode" => "search",
                                        "farmer_id_address" => $db_ref,
                                        "content_rsbsa" => $rsbsa_control_no,
                                        "yield_last_season_details" => $yield_last_season,
                                        "category" => $category,
                                        "birthdate"=> $birthdate,
                                        "final_area" => $final_area,
                                        "sex" => $sex,
                                        "list_version" => $list_version,
                                        "prv_ref" => $prv_code_released
                                        );

                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".new_released_virtual_pending")
                                            ->insert($vs_release_ins);

                                        DB::connection('delivery_inspection_db')->table('tbl_actual_delivery_virtual_pending')
                                        ->insert($vs_stock_ins);

                                }











                    }else{
                        $return_result["status"] = "0";
                        $return_result["msg"] = "Cannot Find Farmer Info";
                    
                        return json_encode($return_result);
                
                       
                    }
                    

                }
            } //FOREACH DISTRIBUTION

           $ret_farmer =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final_pending")
                            ->where("db_ref", $db_ref_vs)
                            ->first();
            if($ret_farmer != null){
                $ret_farmer->float_id = $request->float_id;

                $ret_farmer->prv = str_replace("-","", $ret_farmer->claiming_prv);

                DB::commit();

                $return_result["status"] = "1";
                $return_result["msg"] = $ret_farmer;
            
                return json_encode($return_result);

            }else{
                $return_result["status"] = "0";
                $return_result["msg"] = "Cannot Find Final Farmer Info";
            
                return json_encode($return_result);
        
            }


            
        } catch (\Throwable $th) {

            DB::rollback();
            return json_encode($th->getMessage());
            

        }
    }







    public function save_distribution_homeClaim(Request $request){
        
        if($request->db_ref != 'new'){

            DB::beginTransaction();
            try {

            foreach($request->distribution as $key => $release){
                if($key > 0 ){
                    $category = $request->category;
                    $variety = $release[0];
                    $claimed_area = $release[1];
                    $bags_claimed = $release[2];
                    $prv_dropoff_id = $request->dop_selected_vs;
                    $final_area = $request->virtual_final_area;
                        $claiming_prv = $request->virtual_claiming_prv;
                        $db_ref = $request->db_ref;
                        $db_ref_vs = $request->db_ref;
                    
                        $da_intervention_card = $request->da_intervention_card;
                        $rcef_id = $request->rcef_id;
                        $rcef_id_vs = $request->rcef_id;
                        $rsbsa_control_no = $request->rsbsa_control_no;
                    $yield_last_season = '[{"variety":"'.$request->yield_variety.'","area":"'.$request->yield_area.'","bags":"'.$request->yield_bags.'","weight":"'.$request->yield_weight.'","type":"'.$request->yield_type.'","class":"'.$request->yield_class.'"}]';
                    $crop_est = $request->crop_est;
                    $eco_system = $request->eco_system;
                    $water_source = $request->water_source;
                    $planting_date = $request->planting_month.'/'.$request->planting_week;
                    $mother_last_name = $request->mother_last_name;
                    $mother_first_name = $request->mother_first_name;
                    $mother_mid_name = $request->mother_mid_name;
                    $mother_ext_name = $request->mother_ext_name;
                    $birthdate = $request->birthdate;
                    $tel_no = $request->tel_no;
                    $ip_name = "";
                    $ip = 0;  if($request->ip == "true"){$ip = 1; $ip_name = $request->ip_name;}
                    $pwd = 0; if($request->pwd == "true"){$pwd = 1;}
                    $fca_name = $request->fca_name;
                    $kp_kit = $request->kp_kit;
                    $ayuda_fertilizer = $request->ayuda_fertilizer;
                    $ayuda_incentives = $request->ayuda_incentives;
                    $ayuda_credit = $request->ayuda_credit;
                    $ayuda = "";
                        if($ayuda_fertilizer == "true"){$ayuda .= "fertilizer";}
                        if($ayuda_incentives == "true"){$ayuda .= ",cash_incentives";}
                        if($ayuda_credit == "true"){$ayuda .= ",credit";}
                    $is_representative = 0; if($request->rep){$is_representative = 1;}
                    $rep_name = $request->rep_name;
                    $rep_id = $request->rep_id;
                    $rep_relationship = $request->rep_relationship;
                    $sex = "";
                    $list_version = "1";
    
                    //1 -> normal; 3-created a virtual
                    $status_vs = 1;
                    //CLAIMING_PRV
                    $prv_code_parcel = str_replace("-", "", substr($claiming_prv, 0,5));
                    $prv_code_released = substr($prv_dropoff_id,0,4);
    
                    $code_parcel = str_replace("-", "", $claiming_prv);
                    $code_released = substr($prv_dropoff_id,0,6);
    
                   
              
                        $release_location= DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                                ->where("prv_dropoff_id", $prv_dropoff_id)
                                ->first();
                        if($release_location == null){
                            return json_encode("Release Location Undefined!");
                        }
                        
    
                        $seed_stock= DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where("prv_dropoff_id", $prv_dropoff_id)
                            ->where("seedVariety", $variety)
                            ->sum("totalBagCount");
                         if($seed_stock < $bags_claimed ){return json_encode("Seed stocks exhausted");}
                        
                         $dropOffPoint =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                            ->where("prv_dropoff_id", $prv_dropoff_id)
                            ->value("dropOffPoint");
    
                           
                                
                                        if($prv_code_parcel != $prv_code_released){
                                            //NOT THE SAME PROVINCE            
                                            $status_vs = 3;
                                            $farmer_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                ->where("db_ref", $db_ref)
                                                ->first();
                                            if($farmer_data != null){
                                                if($request->is_lowland){
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "is_new" => 3,
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->final_claimable,
                                                            "total_claimed_area" => $farmer_data->final_area,
                                                            "replacement_bags" => $farmer_data->final_claimable,
                                                            "replacement_area" => $farmer_data->final_area,
                                                            // "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            // "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);

                                                }
                                                else{
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);
                                                }
                        
                                                //GET CURRENT FARMER ON CLAIMING PRV
                                                $get_db_ref =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                        ->where("rsbsa_control_no", $farmer_data->rsbsa_control_no)
                                                        ->where("firstName", $farmer_data->firstName)
                                                        ->where("midName", $farmer_data->midName)
                                                        ->where("lastName", $farmer_data->lastName)
                                                        ->where("extName", $farmer_data->extName)
                                                        ->where("birthdate", $farmer_data->birthdate)
                                                        ->first();
                                                
                                                $list_version = $prv_code_parcel."|".$db_ref;
                                                $final_area = $farmer_data->final_area;
                                                $sex = $farmer_data->sex;
                                                if($get_db_ref != null){
                                                    $rcef_id = $get_db_ref->rcef_id;
                                                    $db_ref = $get_db_ref->db_ref;
                                                    $rsbsa_control_no = $get_db_ref->rsbsa_control_no;
                                                }else{
                                                    $clone_data = $farmer_data;
                                                    $clone_data = json_decode(json_encode($clone_data), true);
                                                    unset($clone_data["id"]);
                                                    unset($clone_data["db_ref"]);
                                                    $clone_data["to_prv_code"] = "clone: ".$list_version;

                                                    $new_farmer_id_clone =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                            ->insertGetId($clone_data);

                                                    //UPDATE
                                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                            ->where("id", $new_farmer_id_clone)
                                                            ->update(["db_ref" => $new_farmer_id_clone]);


                                                    $rcef_id = $farmer_data->rcef_id;
                                                    $db_ref = $new_farmer_id_clone;
                                                    $rsbsa_control_no = $farmer_data->rsbsa_control_no;

                                                            

                                                    // return json_encode("Cannot Find Farmer Info On tagged Released");
                                                }
                        
                                            }else{
                                                return json_encode("Cannot Find Farmer Info on Diff Province");
                                            }
                        
                                        }
                                        
                                        
                                        
                                        else{
    
                                            
    
                                            if($code_parcel != $code_released){
                                                //NOT THE SAME MUNICIPAL
                                                $status_vs = 3;
                                            }
    
    
                                            $farmer_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                ->where("db_ref", $db_ref)
                                                ->first();
                                            if($farmer_data != null){
                                                $sex = $farmer_data->sex;
                                                if($request->is_lowland){
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "is_new" => 3,
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->final_claimable,
                                                            "total_claimed_area" => $farmer_data->final_area,
                                                            "replacement_bags" => $farmer_data->final_claimable,
                                                            "replacement_area" => $farmer_data->final_area,
                                                            // "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            // "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);
                                                }
                                                else{
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);
                                                }
                                            }else{
                                                return json_encode("Cannot Find Farmer Info");
                                            }
                        
                        
                                        }
    
    
    
                                if($request->members){
                                    $release_ref_id =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".new_released")
                                    ->insertGetId([
                                        "id" =>  "111111111",
                                        "rcef_id" => $rcef_id,
                                        "db_ref" => $db_ref,
                                        "prv_dropoff_id" => $prv_dropoff_id,
                                        "province" => $release_location->province,
                                        "municipality" => $release_location->municipality,
                                        "dropOffPoint" => $dropOffPoint,
                                        "transaction_code" => "web",
                                        "dataSharing" => 1,
                                        "is_representative" => $is_representative,
                                        "rep_name" => $rep_name,
                                        "rep_id" => $rep_id,
                                        "rep_relation" => $rep_relationship,
                                        "claimed_area" => $claimed_area,
                                        "bags_claimed" => $bags_claimed,
                                        "seed_variety" => $variety,
                                        "recipient_ls" => "-",
                                        "planted_rcvd_seeds_ls" => "-",
                                        "reason_not_planted_rcvd_seeds_ls" => "-",
                                        "yield_area_harvested_ls" => $request->yield_area,
                                        "yield_no_of_bags_ls" => $request->yield_bags,
                                        "yield_wt_per_bag" => $request->yield_weight,
                                        "crop_establishment_cs" => $crop_est,
                                        "seedling_age" => 0,
                                        "ecosystem_cs" => $eco_system,
                                        "ecosystem_source_cs" => $water_source,
                                        "planting_week" => $planting_date,
                                        "has_kp_kit" => $kp_kit,
                                        "other_benefits_received" => $ayuda,
                                        "date_released" => date("Y-m-d"),
                                        "released_by" => Auth::user()->username,
                                        "time_start" => "-",
                                        "time_end" => "-",
                                        "app_version" => "web",
                                        "distribution_type" => "Regular",
                                        "mode" => "search",
                                        "farmer_id_address" => $db_ref,
                                        "content_rsbsa" => $rsbsa_control_no,
                                        "yield_last_season_details" => $yield_last_season,
                                        "category" => $category,
                                        "birthdate"=> $birthdate,
                                        "final_area" => $final_area,
                                        "sex" => $sex,
                                        "list_version" => $list_version,
                                        "status" => $status_vs,
                                        "process_report_status" => "not process",
                                        "low_land_members" => $request->members
                                    ]);


                                    // dd(json_decode($request->members));
                                    $lowMembers = json_decode($request->members);

                                    foreach($lowMembers as $lowHolder)
                                    {
                                        // dd($lowHolder);
                                        $get_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                        ->where("db_ref", $lowHolder->dbref)
                                        ->first();

                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $lowHolder->dbref)
                                                        ->update([
                                                            "is_new" => 3,
                                                            "is_claimed" => 1,
                                                            "total_claimed" => $get_info->final_claimable,
                                                            "total_claimed_area" => $get_info->final_area,
                                                            "replacement_bags" => $get_info->final_claimable,
                                                            "replacement_area" => $get_info->final_area,
                                                        ]);
                                    }

                                }
                                else{
                                    $release_ref_id =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".new_released")
                                    ->insertGetId([
                                        "id" =>  "111111111",
                                        "rcef_id" => $rcef_id,
                                        "db_ref" => $db_ref,
                                        "prv_dropoff_id" => $prv_dropoff_id,
                                        "province" => $release_location->province,
                                        "municipality" => $release_location->municipality,
                                        "dropOffPoint" => $dropOffPoint,
                                        "transaction_code" => "web",
                                        "dataSharing" => 1,
                                        "is_representative" => $is_representative,
                                        "rep_name" => $rep_name,
                                        "rep_id" => $rep_id,
                                        "rep_relation" => $rep_relationship,
                                        "claimed_area" => 0.00,
                                        "bags_claimed" => 0,
                                        "seed_variety" => $variety,
                                        "remarks" => $bags_claimed.' bag(s) claimed in home address with area of '.$claimed_area,
                                        "recipient_ls" => "-",
                                        "planted_rcvd_seeds_ls" => "-",
                                        "reason_not_planted_rcvd_seeds_ls" => "-",
                                        "yield_area_harvested_ls" => $request->yield_area,
                                        "yield_no_of_bags_ls" => $request->yield_bags,
                                        "yield_wt_per_bag" => $request->yield_weight,
                                        "crop_establishment_cs" => $crop_est,
                                        "seedling_age" => 0,
                                        "ecosystem_cs" => $eco_system,
                                        "ecosystem_source_cs" => $water_source,
                                        "planting_week" => $planting_date,
                                        "has_kp_kit" => $kp_kit,
                                        "other_benefits_received" => $ayuda,
                                        "date_released" => date("Y-m-d"),
                                        "released_by" => Auth::user()->username,
                                        "time_start" => "-",
                                        "time_end" => "-",
                                        "app_version" => "web",
                                        "distribution_type" => "Regular",
                                        "mode" => "search",
                                        "farmer_id_address" => $db_ref,
                                        "content_rsbsa" => $rsbsa_control_no,
                                        "yield_last_season_details" => $yield_last_season,
                                        "category" => $category,
                                        "birthdate"=> $birthdate,
                                        "final_area" => $final_area,
                                        "sex" => $sex,
                                        "list_version" => $list_version,
                                        "status" => $status_vs,
                                        "process_report_status" => "not process"
                                    ]);
                                }
                               
    
    
    
                                    if($status_vs == 3){
    
                                        //GET THE 
                                        
                                        
                                        $prv_location = DB::connection('delivery_inspection_db')->table('lib_prv')
                                            ->where("prv", $code_parcel)
                                            ->first();
                                        $vs_lib_drop_off = "";
                                        $vs_lib_dop_name = "";
                                        
                                        
                                        if($prv_location != null){
                                            $vs_province = $prv_location->province;
                                            $vs_municipality = $prv_location->municipality;
                                            $vs_region = $prv_location->region;
                                           $vs_dop =  DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                                                ->where("municipality", $vs_municipality)
                                                ->where("province", $vs_province)
                                                ->first();
                                            if($vs_dop != null){
                                                $vs_lib_drop_off = $vs_dop->prv_dropoff_id;
                                                $vs_lib_dop_name = $vs_dop->dropOffPoint;
                                            }   
                                        }else{
                                            return json_encode("Server Unreachable");
                                        }
    
                                        //GET STOCKS DETAILS
    
                                        $act_batch = "";
                                        $act_prv = "";
                                        $act_region = "";
                                        $act_province = "";
                                        $act_municipality = "";
                                        $act_dop_name = "";

                                        $seed_actual =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                            ->where("prv_dropoff_id", $prv_dropoff_id)
                                            ->where("seedVariety", $variety)
                                            ->first();
                                        if($seed_actual != null ){
                                            $act_batch = $seed_actual->batchTicketNumber;
                                            $act_prv = $seed_actual->prv;
                                            $act_region = $seed_actual->region;
                                            $act_province = $seed_actual->province;
                                            $act_municipality = $seed_actual->municipality;
                                            $act_dop_name = $seed_actual->dropOffPoint;
                                        }
    
    
                                        $vs_stock_ins = array(
                                            "batchTicketNumber_ref" => $act_batch,
                                            "prv_ref" => $act_prv,
                                            "region_ref" => $act_region,
                                            "province_ref" => $act_province,
                                            "municipality_ref" => $act_municipality,
                                            "dropOffPoint_ref" => $act_dop_name,
                                            "seedVariety_ref" => $variety,
                                            "prv" => $code_parcel,
                                            "prv_dropoff_id" =>$vs_lib_drop_off ,
                                            "dropOffPoint" =>$vs_lib_dop_name ,
                                            "region" =>$vs_region ,
                                            "province" => $vs_province,
                                            "municipality" => $vs_municipality,
                                            "totalBagCount" => $bags_claimed,
                                            "virtual_release_ref" => $release_ref_id
                                        );
    
    
    
                                        $vs_release_ins = array(
                                            "new_released_id_ref" => $release_ref_id,
                                            "id" =>  "111111111",
                                            "rcef_id" => $rcef_id_vs,
                                            "db_ref" => $db_ref_vs,
                                            "prv_dropoff_id" => $vs_lib_drop_off,
                                            "province" => $vs_province,
                                            "municipality" => $vs_municipality,
                                            "dropOffPoint" => $vs_lib_dop_name,
                                            "transaction_code" => "web",
                                            "dataSharing" => 1,
                                            "is_representative" => $is_representative,
                                            "rep_name" => $rep_name,
                                            "rep_id" => $rep_id,
                                            "rep_relation" => $rep_relationship,
                                            "claimed_area" => 0.00,
                                            "bags_claimed" => 0,
                                            "seed_variety" => $variety,
                                            "remarks" => $bags_claimed.' bags claimed in home address with area of '.$claimed_area,
                                            "recipient_ls" => "-",
                                            "planted_rcvd_seeds_ls" => "-",
                                            "reason_not_planted_rcvd_seeds_ls" => "-",
                                            "yield_area_harvested_ls" => $request->yield_area,
                                            "yield_no_of_bags_ls" => $request->yield_bags,
                                            "yield_wt_per_bag" => $request->yield_weight,
                                            "crop_establishment_cs" => $crop_est,
                                            "seedling_age" => 0,
                                            "ecosystem_cs" => $eco_system,
                                            "ecosystem_source_cs" => $water_source,
                                            "planting_week" => $planting_date,
                                            "has_kp_kit" => $kp_kit,
                                            "other_benefits_received" => $ayuda,
                                            "date_released" => date("Y-m-d"),
                                            "released_by" => Auth::user()->username,
                                            "time_start" => "-",
                                            "time_end" => "-",
                                            "app_version" => "web",
                                            "distribution_type" => "Regular",
                                            "mode" => "search",
                                            "farmer_id_address" => $db_ref,
                                            "content_rsbsa" => $rsbsa_control_no,
                                            "yield_last_season_details" => $yield_last_season,
                                            "category" => $category,
                                            "birthdate"=> $birthdate,
                                            "final_area" => $final_area,
                                            "sex" => $sex,
                                            "list_version" => $list_version,
                                            "prv_ref" => $prv_code_released
                                            );
    
                                            DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".new_released_virtual")
                                                ->insert($vs_release_ins);
    
                                            DB::connection('delivery_inspection_db')->table('tbl_actual_delivery_virtual')
                                            ->insert($vs_stock_ins);
    
                                    }
    
    
                } //KEY
    
            } //LOOP
            DB::commit();
            return json_encode("true");                
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        } 
        
    } //IF NOT NEW
    else{
        return json_encode("Server Unreachable");
    }

}

    public function save_distribution(Request $request){
        // dd($request->all());
        if($request->db_ref != 'new'){

            DB::beginTransaction();
            try {

            foreach($request->distribution as $key => $release){
                if($key > 0 ){
                    $category = $request->category;
                    $variety = $release[0];
                    $claimed_area = $release[1];
                    $bags_claimed = $release[2];
                    $prv_dropoff_id = $request->dop_selected_vs;
                    $final_area = $request->virtual_final_area;
                        $claiming_prv = $request->virtual_claiming_prv;
                        $db_ref = $request->db_ref;
                        $db_ref_vs = $request->db_ref;
                    
                        $da_intervention_card = $request->da_intervention_card;
                        $rcef_id = $request->rcef_id;
                        $rcef_id_vs = $request->rcef_id;
                        $rsbsa_control_no = $request->rsbsa_control_no;
                    $yield_last_season = '[{"variety":"'.$request->yield_variety.'","area":"'.$request->yield_area.'","bags":"'.$request->yield_bags.'","weight":"'.$request->yield_weight.'","type":"'.$request->yield_type.'","class":"'.$request->yield_class.'"}]';
                    $crop_est = $request->crop_est;
                    $eco_system = $request->eco_system;
                    $water_source = $request->water_source;
                    $planting_date = $request->planting_month.'/'.$request->planting_week;
                    $mother_last_name = $request->mother_last_name;
                    $mother_first_name = $request->mother_first_name;
                    $mother_mid_name = $request->mother_mid_name;
                    $mother_ext_name = $request->mother_ext_name;
                    $birthdate = $request->birthdate;
                    $tel_no = $request->tel_no;
                    $ip_name = "";
                    $ip = 0;  if($request->ip == "true"){$ip = 1; $ip_name = $request->ip_name;}
                    $pwd = 0; if($request->pwd == "true"){$pwd = 1;}
                    $fca_name = $request->fca_name;
                    $kp_kit = $request->kp_kit;
                    $ayuda_fertilizer = $request->ayuda_fertilizer;
                    $ayuda_incentives = $request->ayuda_incentives;
                    $ayuda_credit = $request->ayuda_credit;
                    $ayuda = "";
                        if($ayuda_fertilizer == "true"){$ayuda .= "fertilizer";}
                        if($ayuda_incentives == "true"){$ayuda .= ",cash_incentives";}
                        if($ayuda_credit == "true"){$ayuda .= ",credit";}
                    $is_representative = 0; if($request->rep){$is_representative = 1;}
                    $rep_name = $request->rep_name;
                    $rep_id = $request->rep_id;
                    $rep_relationship = $request->rep_relationship;
                    $sex = "";
                    $list_version = "1";
    
                    //1 -> normal; 3-created a virtual
                    $status_vs = 1;
                    //CLAIMING_PRV
                    $prv_code_parcel = str_replace("-", "", substr($claiming_prv, 0,5));
                    $prv_code_released = substr($prv_dropoff_id,0,4);
    
                    $code_parcel = str_replace("-", "", $claiming_prv);
                    $code_released = substr($prv_dropoff_id,0,6);
    
                   
              
                        $release_location= DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                                ->where("prv_dropoff_id", $prv_dropoff_id)
                                ->first();
                        if($release_location == null){
                            return json_encode("Release Location Undefined!");
                        }
                        
    
                        $seed_stock= DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                            ->where("prv_dropoff_id", $prv_dropoff_id)
                            ->where("seedVariety", $variety)
                            ->sum("totalBagCount");
                         if($seed_stock < $bags_claimed ){return json_encode("Seed stocks exhausted");}
                        
                         $dropOffPoint =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                            ->where("prv_dropoff_id", $prv_dropoff_id)
                            ->value("dropOffPoint");
    
                           
                                
                                        if($prv_code_parcel != $prv_code_released){
                                            //NOT THE SAME PROVINCE            
                                            $status_vs = 3;
                                            $farmer_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                ->where("db_ref", $db_ref)
                                                ->first();
                                            if($farmer_data != null){
                                                if($request->is_lowland){
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "is_new" => 3,
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->final_claimable,
                                                            "total_claimed_area" => $farmer_data->final_area,
                                                            "replacement_bags" => $farmer_data->final_claimable,
                                                            "replacement_area" => $farmer_data->final_area,
                                                            // "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            // "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);

                                                }
                                                else{
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);
                                                }
                        
                                                //GET CURRENT FARMER ON CLAIMING PRV
                                                $get_db_ref =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                        ->where("rsbsa_control_no", $farmer_data->rsbsa_control_no)
                                                        ->where("firstName", $farmer_data->firstName)
                                                        ->where("midName", $farmer_data->midName)
                                                        ->where("lastName", $farmer_data->lastName)
                                                        ->where("extName", $farmer_data->extName)
                                                        ->where("birthdate", $farmer_data->birthdate)
                                                        ->first();
                                                
                                                $list_version = $prv_code_parcel."|".$db_ref;
                                                $final_area = $farmer_data->final_area;
                                                $sex = $farmer_data->sex;
                                                if($get_db_ref != null){
                                                    $rcef_id = $get_db_ref->rcef_id;
                                                    $db_ref = $get_db_ref->db_ref;
                                                    $rsbsa_control_no = $get_db_ref->rsbsa_control_no;
                                                }else{
                                                    $clone_data = $farmer_data;
                                                    $clone_data = json_decode(json_encode($clone_data), true);
                                                    unset($clone_data["id"]);
                                                    unset($clone_data["db_ref"]);
                                                    $clone_data["to_prv_code"] = "clone: ".$list_version;

                                                    $new_farmer_id_clone =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                            ->insertGetId($clone_data);

                                                    //UPDATE
                                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                            ->where("id", $new_farmer_id_clone)
                                                            ->update(["db_ref" => $new_farmer_id_clone]);


                                                    $rcef_id = $farmer_data->rcef_id;
                                                    $db_ref = $new_farmer_id_clone;
                                                    $rsbsa_control_no = $farmer_data->rsbsa_control_no;

                                                            

                                                    // return json_encode("Cannot Find Farmer Info On tagged Released");
                                                }
                        
                                            }else{
                                                return json_encode("Cannot Find Farmer Info on Diff Province");
                                            }
                        
                                        }
                                        
                                        
                                        
                                        else{
    
                                            
    
                                            if($code_parcel != $code_released){
                                                //NOT THE SAME MUNICIPAL
                                                $status_vs = 3;
                                            }
    
    
                                            $farmer_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".farmer_information_final")
                                                ->where("db_ref", $db_ref)
                                                ->first();
                                            if($farmer_data != null){
                                                $sex = $farmer_data->sex;
                                                if($request->is_lowland){
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "is_new" => 3,
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->final_claimable,
                                                            "total_claimed_area" => $farmer_data->final_area,
                                                            "replacement_bags" => $farmer_data->final_claimable,
                                                            "replacement_area" => $farmer_data->final_area,
                                                            // "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            // "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);
                                                }
                                                else{
                                                    DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $db_ref)
                                                        ->update([
                                                            "da_intervention_card" => $da_intervention_card,
                                                            "birthdate" => $birthdate,
                                                            "is_claimed" => 1,
                                                            "fca_name" => $fca_name,
                                                            "total_claimed" => $farmer_data->total_claimed + $bags_claimed,
                                                            "total_claimed_area" => $farmer_data->total_claimed_area + $claimed_area,
                                                            "mother_lname" => $mother_last_name,
                                                            "mother_fname" => $mother_first_name,
                                                            "mother_mname" => $mother_mid_name,
                                                            "mother_suffix" => $mother_ext_name,
                                                            "tel_no" => $tel_no,
                                                            "is_pwd" => $pwd,
                                                            "is_ip" => $ip,
                                                            "tribe_name" => $ip_name,
                                                        ]);
                                                }
                                            }else{
                                                return json_encode("Cannot Find Farmer Info");
                                            }
                        
                        
                                        }
    
    
    
                                if($request->members){
                                    $release_ref_id =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".new_released")
                                    ->insertGetId([
                                        "id" =>  "111111111",
                                        "rcef_id" => $rcef_id,
                                        "db_ref" => $db_ref,
                                        "prv_dropoff_id" => $prv_dropoff_id,
                                        "province" => $release_location->province,
                                        "municipality" => $release_location->municipality,
                                        "dropOffPoint" => $dropOffPoint,
                                        "transaction_code" => "web",
                                        "dataSharing" => 1,
                                        "is_representative" => $is_representative,
                                        "rep_name" => $rep_name,
                                        "rep_id" => $rep_id,
                                        "rep_relation" => $rep_relationship,
                                        "claimed_area" => $claimed_area,
                                        "bags_claimed" => $bags_claimed,
                                        "seed_variety" => $variety,
                                        "recipient_ls" => "-",
                                        "planted_rcvd_seeds_ls" => "-",
                                        "reason_not_planted_rcvd_seeds_ls" => "-",
                                        "yield_area_harvested_ls" => $request->yield_area,
                                        "yield_no_of_bags_ls" => $request->yield_bags,
                                        "yield_wt_per_bag" => $request->yield_weight,
                                        "crop_establishment_cs" => $crop_est,
                                        "seedling_age" => 0,
                                        "ecosystem_cs" => $eco_system,
                                        "ecosystem_source_cs" => $water_source,
                                        "planting_week" => $planting_date,
                                        "has_kp_kit" => $kp_kit,
                                        "other_benefits_received" => $ayuda,
                                        "date_released" => date("Y-m-d"),
                                        "released_by" => Auth::user()->username,
                                        "time_start" => "-",
                                        "time_end" => "-",
                                        "app_version" => "web",
                                        "distribution_type" => "Regular",
                                        "mode" => "search",
                                        "farmer_id_address" => $db_ref,
                                        "content_rsbsa" => $rsbsa_control_no,
                                        "yield_last_season_details" => $yield_last_season,
                                        "category" => $category,
                                        "birthdate"=> $birthdate,
                                        "final_area" => $final_area,
                                        "sex" => $sex,
                                        "list_version" => $list_version,
                                        "status" => $status_vs,
                                        "process_report_status" => "not process",
                                        "low_land_members" => $request->members
                                    ]);


                                    // dd(json_decode($request->members));
                                    $lowMembers = json_decode($request->members);

                                    foreach($lowMembers as $lowHolder)
                                    {
                                        // dd($lowHolder);
                                        $get_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                        ->where("db_ref", $lowHolder->dbref)
                                        ->first();

                                        DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".farmer_information_final")
                                                        ->where("db_ref", $lowHolder->dbref)
                                                        ->update([
                                                            "is_new" => 3,
                                                            "is_claimed" => 1,
                                                            "total_claimed" => $get_info->final_claimable,
                                                            "total_claimed_area" => $get_info->final_area,
                                                            "replacement_bags" => $get_info->final_claimable,
                                                            "replacement_area" => $get_info->final_area,
                                                        ]);
                                    }

                                }
                                else{
                                    $release_ref_id =  DB::table($GLOBALS['season_prefix']."prv_".$prv_code_released.".new_released")
                                    ->insertGetId([
                                        "id" =>  "111111111",
                                        "rcef_id" => $rcef_id,
                                        "db_ref" => $db_ref,
                                        "prv_dropoff_id" => $prv_dropoff_id,
                                        "province" => $release_location->province,
                                        "municipality" => $release_location->municipality,
                                        "dropOffPoint" => $dropOffPoint,
                                        "transaction_code" => "web",
                                        "dataSharing" => 1,
                                        "is_representative" => $is_representative,
                                        "rep_name" => $rep_name,
                                        "rep_id" => $rep_id,
                                        "rep_relation" => $rep_relationship,
                                        "claimed_area" => $claimed_area,
                                        "bags_claimed" => $bags_claimed,
                                        "seed_variety" => $variety,
                                        "recipient_ls" => "-",
                                        "planted_rcvd_seeds_ls" => "-",
                                        "reason_not_planted_rcvd_seeds_ls" => "-",
                                        "yield_area_harvested_ls" => $request->yield_area,
                                        "yield_no_of_bags_ls" => $request->yield_bags,
                                        "yield_wt_per_bag" => $request->yield_weight,
                                        "crop_establishment_cs" => $crop_est,
                                        "seedling_age" => 0,
                                        "ecosystem_cs" => $eco_system,
                                        "ecosystem_source_cs" => $water_source,
                                        "planting_week" => $planting_date,
                                        "has_kp_kit" => $kp_kit,
                                        "other_benefits_received" => $ayuda,
                                        "date_released" => date("Y-m-d"),
                                        "released_by" => Auth::user()->username,
                                        "time_start" => "-",
                                        "time_end" => "-",
                                        "app_version" => "web",
                                        "distribution_type" => "Regular",
                                        "mode" => "search",
                                        "farmer_id_address" => $db_ref,
                                        "content_rsbsa" => $rsbsa_control_no,
                                        "yield_last_season_details" => $yield_last_season,
                                        "category" => $category,
                                        "birthdate"=> $birthdate,
                                        "final_area" => $final_area,
                                        "sex" => $sex,
                                        "list_version" => $list_version,
                                        "status" => $status_vs,
                                        "process_report_status" => "not process"
                                    ]);
                                }
                               
    
    
    
                                    if($status_vs == 3){
    
                                        //GET THE 
                                        
                                        
                                        $prv_location = DB::connection('delivery_inspection_db')->table('lib_prv')
                                            ->where("prv", $code_parcel)
                                            ->first();
                                        $vs_lib_drop_off = "";
                                        $vs_lib_dop_name = "";
                                        
                                        
                                        if($prv_location != null){
                                            $vs_province = $prv_location->province;
                                            $vs_municipality = $prv_location->municipality;
                                            $vs_region = $prv_location->region;
                                           $vs_dop =  DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                                                ->where("municipality", $vs_municipality)
                                                ->where("province", $vs_province)
                                                ->first();
                                            if($vs_dop != null){
                                                $vs_lib_drop_off = $vs_dop->prv_dropoff_id;
                                                $vs_lib_dop_name = $vs_dop->dropOffPoint;
                                            }   
                                        }else{
                                            return json_encode("Server Unreachable");
                                        }
    
                                        //GET STOCKS DETAILS
    
                                        $act_batch = "";
                                        $act_prv = "";
                                        $act_region = "";
                                        $act_province = "";
                                        $act_municipality = "";
                                        $act_dop_name = "";

                                        $seed_actual =  DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                                            ->where("prv_dropoff_id", $prv_dropoff_id)
                                            ->where("seedVariety", $variety)
                                            ->first();
                                        if($seed_actual != null ){
                                            $act_batch = $seed_actual->batchTicketNumber;
                                            $act_prv = $seed_actual->prv;
                                            $act_region = $seed_actual->region;
                                            $act_province = $seed_actual->province;
                                            $act_municipality = $seed_actual->municipality;
                                            $act_dop_name = $seed_actual->dropOffPoint;
                                        }
    
    
                                        $vs_stock_ins = array(
                                            "batchTicketNumber_ref" => $act_batch,
                                            "prv_ref" => $act_prv,
                                            "region_ref" => $act_region,
                                            "province_ref" => $act_province,
                                            "municipality_ref" => $act_municipality,
                                            "dropOffPoint_ref" => $act_dop_name,
                                            "seedVariety_ref" => $variety,
                                            "prv" => $code_parcel,
                                            "prv_dropoff_id" =>$vs_lib_drop_off ,
                                            "dropOffPoint" =>$vs_lib_dop_name ,
                                            "region" =>$vs_region ,
                                            "province" => $vs_province,
                                            "municipality" => $vs_municipality,
                                            "totalBagCount" => $bags_claimed,
                                            "virtual_release_ref" => $release_ref_id
                                        );
    
    
    
                                        $vs_release_ins = array(
                                            "new_released_id_ref" => $release_ref_id,
                                            "id" =>  "111111111",
                                            "rcef_id" => $rcef_id_vs,
                                            "db_ref" => $db_ref_vs,
                                            "prv_dropoff_id" => $vs_lib_drop_off,
                                            "province" => $vs_province,
                                            "municipality" => $vs_municipality,
                                            "dropOffPoint" => $vs_lib_dop_name,
                                            "transaction_code" => "web",
                                            "dataSharing" => 1,
                                            "is_representative" => $is_representative,
                                            "rep_name" => $rep_name,
                                            "rep_id" => $rep_id,
                                            "rep_relation" => $rep_relationship,
                                            "claimed_area" => $claimed_area,
                                            "bags_claimed" => $bags_claimed,
                                            "seed_variety" => $variety,
                                            "recipient_ls" => "-",
                                            "planted_rcvd_seeds_ls" => "-",
                                            "reason_not_planted_rcvd_seeds_ls" => "-",
                                            "yield_area_harvested_ls" => $request->yield_area,
                                            "yield_no_of_bags_ls" => $request->yield_bags,
                                            "yield_wt_per_bag" => $request->yield_weight,
                                            "crop_establishment_cs" => $crop_est,
                                            "seedling_age" => 0,
                                            "ecosystem_cs" => $eco_system,
                                            "ecosystem_source_cs" => $water_source,
                                            "planting_week" => $planting_date,
                                            "has_kp_kit" => $kp_kit,
                                            "other_benefits_received" => $ayuda,
                                            "date_released" => date("Y-m-d"),
                                            "released_by" => Auth::user()->username,
                                            "time_start" => "-",
                                            "time_end" => "-",
                                            "app_version" => "web",
                                            "distribution_type" => "Regular",
                                            "mode" => "search",
                                            "farmer_id_address" => $db_ref,
                                            "content_rsbsa" => $rsbsa_control_no,
                                            "yield_last_season_details" => $yield_last_season,
                                            "category" => $category,
                                            "birthdate"=> $birthdate,
                                            "final_area" => $final_area,
                                            "sex" => $sex,
                                            "list_version" => $list_version,
                                            "prv_ref" => $prv_code_released
                                            );
    
                                            DB::table($GLOBALS['season_prefix']."prv_".$prv_code_parcel.".new_released_virtual")
                                                ->insert($vs_release_ins);
    
                                            DB::connection('delivery_inspection_db')->table('tbl_actual_delivery_virtual')
                                            ->insert($vs_stock_ins);
    
                                    }
    
    
                } //KEY
    
            } //LOOP
            DB::commit();
            return json_encode("true");                
        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode($th->getMessage());
        } 
        
    } //IF NOT NEW
    else{
        return json_encode("Server Unreachable");
    }

}



public function get_all_parcel2(Request $request){
    

        
    $prefix = $GLOBALS['season_prefix'];
   $ffrs_data = DB::table($prefix."prv_".$request->prv.".farmer_information_final")
       ->where("db_ref", $request->db_ref)
       ->first();
   
   $return_arr = array(
       "status" => 0,
       "parcel_list" => "",
       "variety_list" => "",
       "dop_available" => "",
   );

   if($ffrs_data != null){
       $region_number = substr($ffrs_data->rsbsa_control_no,0,2);

       $home_prv_undashed = str_replace("-", "",substr($ffrs_data->rsbsa_control_no, 0 ,8));
       $add_home_dop = 1;


   /*   return   $parcel_data = DB::table("ffrs_may_2023.region_".$region_number)
           ->where("rsbsa_no", $ffrs_data->rsbsa_control_no)
           ->get(); */

      
       if($request->state==0){
           $parcel_data =DB::table($prefix."prv_".$request->prv.".farmer_information_final")
           ->where("rsbsa_control_no", $ffrs_data->rsbsa_control_no)
           ->whereIn("is_new", [2, 8])
        //    ->where(function ($query) {
        //     $query ->where("is_new", 2)
        //             ->orwhere("is_new", 8);
        // })
           ->get();
       }else{
           $parcel_data =DB::table($prefix."prv_".$request->prv.".farmer_information_final")
           ->where("db_ref", $request->db_ref)
           ->whereIn("is_new", [2, 8])
        //    ->where(function ($query) {
        //     $query ->where("is_new", 2)
        //             ->orwhere("is_new", 8);
        // })
           ->get();
       }
       $parcel_list =  array();
       foreach($parcel_data as $pd){
           $prv_claiming = str_replace("-", "",substr($pd->claiming_prv, 0 ,5));
           $claiming_prv = $pd->claiming_prv;
           $claiming_prv_undashed = str_replace("-", "",substr($pd->claiming_prv, 0 ,8));
           if($claiming_prv_undashed == $home_prv_undashed)
           {
               $add_home_dop = 0;
           }

           $may_data = DB::table($prefix."prv_".$prv_claiming.".farmer_information_final")
               ->where("claiming_prv", $claiming_prv)
               ->where("rsbsa_control_no", $pd->rsbsa_control_no)
               
               ->where("firstName", $pd->firstName)
               ->where("midName", $pd->midName)
               ->where("lastName", $pd->lastName)
               ->whereIn("is_new", [2, 8])
            //    ->where(function ($query) {
            //     $query ->where("is_new", 2)
            //             ->orwhere("is_new", 8);
            // })
               ->first();       
               $lib_prv_no =   DB::table($prefix."rcep_delivery_inspection.lib_prv")
               ->where("prv", $claiming_prv_undashed)
               ->first();                  
               if($claiming_prv_undashed=="999902"){

                    $province = "Programmer Province";
                   $municipality = "Programmer Municipality";
               }else{
                   $lib_prv_no =   DB::table($prefix."rcep_delivery_inspection.lib_prv")
                   ->where("prv", $claiming_prv_undashed)
                   ->first();
                   if($lib_prv_no != null){
                       $province = $lib_prv_no->province;
                       $municipality = $lib_prv_no->municipality;
                   }else{
                       $province = "N/A";
                       $municipality = "N/A";
                   }
               }
               $final_area = $pd->final_area;



               $prv = $prv_claiming;
               $id = "0";

               $mother_lname = "";
               $mother_fname = "";
               $mother_mname = "";
               $mother_suffix = "";
               $is_ip = "";
               $tribe_name = "";
               $is_pwd   = "";
               $birthdate  = "";
               $tel_no = "";
               $fca_name = "";

               $remaining = 0;
               $remaining_area = 0;
               if($may_data != null){
                   $id = $may_data->db_ref;

                   $mother_lname = $may_data->mother_lname;
                   $mother_fname = $may_data->mother_fname;
                   $mother_mname = $may_data->mother_mname;
                   $mother_suffix = $may_data->mother_suffix;
                   $is_ip = $may_data->is_ip;
                   $tribe_name = $may_data->tribe_name;
                   $is_pwd   = $may_data->is_pwd  ;
                   $birthdate  = $may_data->birthdate ;
                   $tel_no  = $may_data->tel_no ;
                   $fca_name = $may_data->fca_name;


                   $action =  "<button class='btn btn-success btn-sm'>Set Distribution</button>";
                   // $release = DB::table($prefix."prv_".$prv_claiming.".new_released")
                   //     ->where("db_ref", $may_data->db_ref)
                   //     ->sum("bags_claimed");

                   // $release_area = DB::table($prefix."prv_".$prv_claiming.".new_released")
                   //     ->where("db_ref", $may_data->db_ref)
                   //     ->sum("claimed_area");

                   $release = $may_data->total_claimed;
                   $release_area = $may_data->total_claimed_area;

                   $final_area = $may_data->final_area;
                   $remaining = ceil($final_area * 2) - $release;
                   $remaining_area = $final_area - $release_area;

                   if($remaining <= 0){
                       
                       $action = "<label class='badge badge-warning'>Claimed</label>";
                   }else{
                           $action = "<label class='badge badge-success'>Available</label>";

                   }

                 
                   
                   

               }else{
                   $action = "<label class='badge badge-dark'>-</label>";

               }
           
               


           array_push($parcel_list, array(
               "province" => $province,
               "municipality" => $municipality,
               "final_area" => number_format($final_area,4),
               "remaining" => $remaining,
               "remaining_area" => number_format($remaining_area,4),
               "prv" => $prv,
               "id" => $id,
               
               "claiming_prv" => $claiming_prv,
               "birthdate" => $birthdate,
               "mother_lname" =>  $mother_lname,
               "mother_fname" =>  $mother_fname,
               "mother_mname" =>  $mother_mname,
               "mother_suffix" => $mother_suffix,
               "is_ip" => $is_ip,
               "tribe_name" => $tribe_name,
               "is_pwd" => $is_pwd,
               "tel_no" => $tel_no,
               "fca_name" => $fca_name,
               "action" =>$action,
           ));

    

       }   

       
       if(count($parcel_list) <= 0){
        
           $prv_claiming = str_replace("-", "",substr($ffrs_data->claiming_prv, 0 ,5));
           $claiming_prv = $ffrs_data->claiming_prv;
           $claiming_prv_undashed = str_replace("-", "",substr($ffrs_data->claiming_prv, 0 ,8));
           if($claiming_prv_undashed == $home_prv_undashed)
           {
               $add_home_dop = 0;
           }

           $may_data = DB::table($prefix."prv_".$prv_claiming.".farmer_information_final")
               ->where("claiming_prv", $claiming_prv)
               ->where("firstName", $ffrs_data->firstName)
               ->where("midName", $ffrs_data->midName)
               ->where("lastName", $ffrs_data->lastName)
               ->first();                    
               if($claiming_prv_undashed=="999902"){
                   $province = "Programmer Province";
                   $municipality = "Programmer Municipality";
               }else{
                   $lib_prv_no =   DB::table($prefix."rcep_delivery_inspection.lib_prv")
                   ->where("prv", $claiming_prv_undashed)
                   ->first();
                   if($lib_prv_no != null){
                       $province = $lib_prv_no->province;
                       $municipality = $lib_prv_no->municipality;
                   }else{
                       $province = "N/A";
                       $municipality = "N/A";
                   }
               }
              
               
               $final_area = $ffrs_data->final_area;
               
               $prv = $prv_claiming;
               $id = "0";

               $mother_lname = "";
               $mother_fname = "";
               $mother_mname = "";
               $mother_suffix = "";
               $is_ip = "";
               $tribe_name = "";
               $is_pwd   = "";
               $birthdate  = "";
               $tel_no = "";
               $fca_name = "";

               $remaining = 0;
               $remaining_area = 0;
               if($may_data != null){
                   $id = $may_data->db_ref;

                   $mother_lname = $may_data->mother_lname;
                   $mother_fname = $may_data->mother_fname;
                   $mother_mname = $may_data->mother_mname;
                   $mother_suffix = $may_data->mother_suffix;
                   $is_ip = $may_data->is_ip;
                   $tribe_name = $may_data->tribe_name;
                   $is_pwd   = $may_data->is_pwd  ;
                   $birthdate  = $may_data->birthdate ;
                   $tel_no  = $may_data->tel_no ;
                   $fca_name = $may_data->fca_name;


                   $action =  "<button class='btn btn-success btn-sm'>Set Distribution</button>";
                   // $release = DB::table($prefix."prv_".$prv_claiming.".new_released")
                   //     ->where("db_ref", $may_data->db_ref)
                   //     ->sum("bags_claimed");

                   // $release_area = DB::table($prefix."prv_".$prv_claiming.".new_released")
                   //     ->where("db_ref", $may_data->db_ref)
                   //     ->sum("claimed_area");

                   $release = $may_data->total_claimed;
                   $release_area = $may_data->total_claimed_area;

                   $final_area = $may_data->final_area;
                   $remaining = ceil($final_area * 2) - $release;
                   $remaining_area = $final_area - $release_area;

                   if($remaining <= 0){
                       
                       $action = "<label class='badge badge-warning'>Claimed</label>";
                   }else{
                           $action = "<label class='badge badge-success'>Available</label>";

                   }

               
                   
                   

               }else{
                   $action = "<label class='badge badge-dark'>-</label>";

               }
           
               


           array_push($parcel_list, array(
               "province" => $province,
               "municipality" => $municipality,
               "final_area" => number_format($final_area,4),
               "remaining" => $remaining,
               "remaining_area" => number_format($remaining_area,4),
               "prv" => $prv,
               "id" => $id,
               
               "claiming_prv" => $claiming_prv,
               "birthdate" => $birthdate,
               "mother_lname" =>  $mother_lname,
               "mother_fname" =>  $mother_fname,
               "mother_mname" =>  $mother_mname,
               "mother_suffix" => $mother_suffix,
               "is_ip" => $is_ip,
               "tribe_name" => $tribe_name,
               "is_pwd" => $is_pwd,
               "tel_no" => $tel_no,
               "fca_name" => $fca_name,
               "action" =>$action,
           ));



       }



       $variety_list = array();
       $dop_available = array();
     
       foreach($parcel_list as $parcel){
            $hybrid = $this->getVariety($parcel["province"],$parcel["municipality"]);
      
           foreach($hybrid as $bred){
               $release_data ="soon";
               array_push($variety_list, array(
                   "prv_id" => "bred->prv",
                   "province" => $parcel["province"],
                   "municipality" => $parcel["municipality"],
                   "seedVariety" => $bred['variety'],
                   "totalBag" => '####',
                   "release" => $release_data,
                   "balance" => $bred['remaining_balance'],
                   "category" => "Inbred"
               ));

           }

           $raw_dop = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_actual_delivery")
            ->select(
                'dropOffPoint','prv_dropoff_id'
            )
            ->where("province",$parcel["province"])
            ->where("municipality", $parcel["municipality"])
            ->groupBy("prv_dropoff_id")
            ->get();
            // dd($raw_dop);
            foreach($raw_dop as $dop){
                array_push($dop_available, array(
                    'dropOffPoint' => $dop->dropOffPoint,
                    'prv_dropoff_id' => $dop->prv_dropoff_id
                ));
            }
       }
    //    dd($variety_list);
       $return_arr["status"] = 1;
       $return_arr["parcel_list"] = $parcel_list;
       $return_arr["variety_list"] = $variety_list;
       $return_arr["dop_available"] = $dop_available;

   }

   return json_encode($return_arr);
}



    public function get_all_parcel(Request $request){
    

        $prefix = $GLOBALS['season_prefix'];
   
        $ffrs_data = DB::table($prefix."prv_".$request->prv.".farmer_information_final")
            ->where("db_ref", $request->db_ref)
            ->first();
        
        $return_arr = array(
            "status" => 0,
            "parcel_list" => "",
            "variety_list" => "",
            "dop_available" => "",
        );

        if($ffrs_data != null){
            $region_number = substr($ffrs_data->rsbsa_control_no,0,2);

            $home_prv_undashed = str_replace("-", "",substr($ffrs_data->rsbsa_control_no, 0 ,8));
            $add_home_dop = 1;


            $parcel_data = DB::table("ffrs_may_2023.region_".$region_number)
                ->where("rsbsa_no", $ffrs_data->rsbsa_control_no)
                ->get();

            $parcel_list =  array();
            foreach($parcel_data as $pd){
                $prv_claiming = str_replace("-", "",substr($pd->claiming_prv, 0 ,5));
                $claiming_prv = $pd->claiming_prv;
                $claiming_prv_undashed = str_replace("-", "",substr($pd->claiming_prv, 0 ,8));
                if($claiming_prv_undashed == $home_prv_undashed)
                {
                    $add_home_dop = 0;
                }

                $may_data = DB::table($prefix."prv_".$prv_claiming.".farmer_information_final")
                    ->where("claiming_prv", $claiming_prv)
                    ->where("rsbsa_control_no", $pd->rsbsa_no)
                    
                    ->where("firstName", $pd->first_name)
                    ->where("midName", $pd->middle_name)
                    ->where("lastName", $pd->last_name)
                    ->first();


                   


                    $province = $pd->parcel_address_prv;
                    $municipality = $pd->parcel_address_mun;
                    $final_area = $pd->crop_area;
                    
                    $lib_prv_no =   DB::table($prefix."rcep_delivery_inspection.lib_prv")
                    ->where("prv", $claiming_prv_undashed)
                    ->first();
                        if($lib_prv_no != null){
                            $province = $lib_prv_no->province;
                            $municipality = $lib_prv_no->municipality;
                        }else{
                            $province = "N/A";
                            $municipality = "N/A";
                        }



                    $prv = $prv_claiming;
                    $id = "0";

                    $mother_lname = "";
                    $mother_fname = "";
                    $mother_mname = "";
                    $mother_suffix = "";
                    $is_ip = "";
                    $tribe_name = "";
                    $is_pwd   = "";
                    $birthdate  = "";
                    $tel_no = "";
                    $fca_name = "";

                    $remaining = 0;
                    $remaining_area = 0;
                    if($may_data != null){
                        $id = $may_data->db_ref;

                        $mother_lname = $may_data->mother_lname;
                        $mother_fname = $may_data->mother_fname;
                        $mother_mname = $may_data->mother_mname;
                        $mother_suffix = $may_data->mother_suffix;
                        $is_ip = $may_data->is_ip;
                        $tribe_name = $may_data->tribe_name;
                        $is_pwd   = $may_data->is_pwd  ;
                        $birthdate  = $may_data->birthdate ;
                        $tel_no  = $may_data->tel_no ;
                        $fca_name = $may_data->fca_name;


                        $action =  "<button class='btn btn-success btn-sm'>Set Distribution</button>";
                        // $release = DB::table($prefix."prv_".$prv_claiming.".new_released")
                        //     ->where("db_ref", $may_data->db_ref)
                        //     ->sum("bags_claimed");

                        // $release_area = DB::table($prefix."prv_".$prv_claiming.".new_released")
                        //     ->where("db_ref", $may_data->db_ref)
                        //     ->sum("claimed_area");

                        $release = $may_data->total_claimed;
                        $release_area = $may_data->total_claimed_area;

                        $final_area = $may_data->final_area;
                        $remaining = ceil($final_area * 2) - $release;
                        $remaining_area = $final_area - $release_area;

                        if($remaining <= 0){
                            
                            $action = "<label class='badge badge-warning'>Claimed</label>";
                        }else{
                                $action = "<label class='badge badge-success'>Available</label>";

                        }

                      
                        
                        

                    }else{
                        $action = "<label class='badge badge-dark'>-</label>";

                    }
                
                    


                array_push($parcel_list, array(
                    "province" => $province,
                    "municipality" => $municipality,
                    "final_area" => $final_area,
                    "remaining" => $remaining,
                    "remaining_area" => $remaining_area,
                    "prv" => $prv,
                    "id" => $id,
                    
                    "claiming_prv" => $claiming_prv,
                    "birthdate" => $birthdate,
                    "mother_lname" =>  $mother_lname,
                    "mother_fname" =>  $mother_fname,
                    "mother_mname" =>  $mother_mname,
                    "mother_suffix" => $mother_suffix,
                    "is_ip" => $is_ip,
                    "tribe_name" => $tribe_name,
                    "is_pwd" => $is_pwd,
                    "tel_no" => $tel_no,
                    "fca_name" => $fca_name,
                    "action" =>$action,
                ));

         

            }   


            if(count($parcel_list) <= 0){
             
                $prv_claiming = str_replace("-", "",substr($ffrs_data->claiming_prv, 0 ,5));
                $claiming_prv = $ffrs_data->claiming_prv;
                $claiming_prv_undashed = str_replace("-", "",substr($ffrs_data->claiming_prv, 0 ,8));
                if($claiming_prv_undashed == $home_prv_undashed)
                {
                    $add_home_dop = 0;
                }

                $may_data = DB::table($prefix."prv_".$prv_claiming.".farmer_information_final")
                    ->where("claiming_prv", $claiming_prv)
                    ->where("firstName", $ffrs_data->firstName)
                    ->where("midName", $ffrs_data->midName)
                    ->where("lastName", $ffrs_data->lastName)
                    ->first();

                    $lib_prv_no =   DB::table($prefix."rcep_delivery_inspection.lib_prv")
                        ->where("prv", $claiming_prv_undashed)
                        ->first();
                    if($lib_prv_no != null){
                        $province = $lib_prv_no->province;
                        $municipality = $lib_prv_no->municipality;
                    }else{
                        $province = "N/A";
                        $municipality = "N/A";
                    }
                    
                    $final_area = $ffrs_data->final_area;
                    
                    $prv = $prv_claiming;
                    $id = "0";

                    $mother_lname = "";
                    $mother_fname = "";
                    $mother_mname = "";
                    $mother_suffix = "";
                    $is_ip = "";
                    $tribe_name = "";
                    $is_pwd   = "";
                    $birthdate  = "";
                    $tel_no = "";
                    $fca_name = "";

                    $remaining = 0;
                    $remaining_area = 0;
                    if($may_data != null){
                        $id = $may_data->db_ref;

                        $mother_lname = $may_data->mother_lname;
                        $mother_fname = $may_data->mother_fname;
                        $mother_mname = $may_data->mother_mname;
                        $mother_suffix = $may_data->mother_suffix;
                        $is_ip = $may_data->is_ip;
                        $tribe_name = $may_data->tribe_name;
                        $is_pwd   = $may_data->is_pwd  ;
                        $birthdate  = $may_data->birthdate ;
                        $tel_no  = $may_data->tel_no ;
                        $fca_name = $may_data->fca_name;


                        $action =  "<button class='btn btn-success btn-sm'>Set Distribution</button>";
                        // $release = DB::table($prefix."prv_".$prv_claiming.".new_released")
                        //     ->where("db_ref", $may_data->db_ref)
                        //     ->sum("bags_claimed");

                        // $release_area = DB::table($prefix."prv_".$prv_claiming.".new_released")
                        //     ->where("db_ref", $may_data->db_ref)
                        //     ->sum("claimed_area");

                        $release = $may_data->total_claimed;
                        $release_area = $may_data->total_claimed_area;

                        $final_area = $may_data->final_area;
                        $remaining = ceil($final_area * 2) - $release;
                        $remaining_area = $final_area - $release_area;

                        if($remaining <= 0){
                            
                            $action = "<label class='badge badge-warning'>Claimed</label>";
                        }else{
                            $action = "<label class='badge badge-success'>Available</label>";

                        }

                    
                        
                        

                    }else{
                        $action = "<label class='badge badge-dark'>-</label>";

                    }
                
                    


                array_push($parcel_list, array(
                    "province" => $province,
                    "municipality" => $municipality,
                    "final_area" => $final_area,
                    "remaining" => $remaining,
                    "remaining_area" => $remaining_area,
                    "prv" => $prv,
                    "id" => $id,
                    
                    "claiming_prv" => $claiming_prv,
                    "birthdate" => $birthdate,
                    "mother_lname" =>  $mother_lname,
                    "mother_fname" =>  $mother_fname,
                    "mother_mname" =>  $mother_mname,
                    "mother_suffix" => $mother_suffix,
                    "is_ip" => $is_ip,
                    "tribe_name" => $tribe_name,
                    "is_pwd" => $is_pwd,
                    "tel_no" => $tel_no,
                    "fca_name" => $fca_name,
                    "action" =>$action,
                ));



            }



            $variety_list = array();
            $dop_available = array();
            foreach($parcel_list as $parcel){
                $inbred = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->select("prv_dropoff_id", "dropOffPoint", "prv", "province", "municipality", "seedVariety", DB::raw("SUM(totalBagCount) as totalBag"))
                    ->where("province", $parcel["province"])
                    ->where("municipality", $parcel["municipality"])
                    ->groupBy("municipality", "seedVariety")
                    ->get();

                foreach($inbred as $bred){
                    $release_data = DB::table($prefix."prv_".$request->prv.".new_released")
                    ->where("prv_dropoff_id", "LIKE", $bred->prv."%")
                    ->where("seed_variety", $bred->seedVariety)
                    ->where('category', "INBRED")
                    ->sum("bags_claimed");

                    $remaining = $bred->totalBag - $release_data;
                    $dop_available[$bred->prv_dropoff_id] = $bred->dropOffPoint . " (".$bred->province.", ".$bred->municipality.")";

                    array_push($variety_list, array(
                        "prv_id" => $bred->prv,
                        "province" => $parcel["province"],
                        "municipality" => $parcel["municipality"],
                        "seedVariety" => $bred->seedVariety,
                        "totalBag" => $bred->totalBag,
                        "release" => $release_data,
                        "balance" => $remaining,
                        "category" => "INBRED"
                    ));

                }
            }

            if($add_home_dop == 1){


                $inbred = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->select("prv_dropoff_id", "dropOffPoint", "prv", "province", "municipality", "seedVariety", DB::raw("SUM(totalBagCount) as totalBag"))
                    ->where("prv", "LIKE", $home_prv_undashed."%")
                    ->groupBy("municipality", "seedVariety")
                    ->get();

                foreach($inbred as $bred){
                    $release_data = DB::table($prefix."prv_".$request->prv.".new_released")
                    ->where("prv_dropoff_id", "LIKE", $bred->prv."%")
                    ->where("seed_variety", $bred->seedVariety)
                    ->where('category', "INBRED")
                    ->sum("bags_claimed");

                    $remaining = $bred->totalBag - $release_data;
                    $dop_available[$bred->prv_dropoff_id] = $bred->dropOffPoint . " (".$bred->province.", ".$bred->municipality.")";

                    array_push($variety_list, array(
                        "prv_id" => $bred->prv,
                        "province" => $bred->province,
                        "municipality" => $bred->municipality,
                        "seedVariety" => $bred->seedVariety,
                        "totalBag" => $bred->totalBag,
                        "release" => $release_data,
                        "balance" => $remaining,
                        "category" => "INBRED"
                    ));

                }
            }







       

            $return_arr["status"] = 1;
            $return_arr["parcel_list"] = $parcel_list;
            $return_arr["variety_list"] = $variety_list;
            $return_arr["dop_available"] = $dop_available;

        }

        return json_encode($return_arr);
    }

    public function get_dop_list(Request $request){
      $dop_data = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
            ->where("province", $request->province)
            ->where("municipality", $request->municipality)
            ->groupBy("prv_dropoff_id")
            ->get();

        return json_encode($dop_data);
            

    }

    private function getVariety($province,$municipality){
        
        try {
            $varietyData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(
                'seedVariety as seed_variety', 
                DB::raw('sum(totalBagCount) as noOfbags')
                )
            ->where('province',$province)

            ->where('municipality',$municipality)
            ->groupBy('seedVariety')
            ->get();
            if($province =="Programmer Province"){
                $lib_prv = new \stdClass;
                $lib_prv->prv_code = "9999" ;
            }else{
                $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('prv_code')
                ->where('province',$province)
                ->where('municipality',$municipality)
                ->first();
            }
            // dd($varietyData);
    
            
            $finalData = array();
            foreach ($varietyData as  $value) {
                $transferStocks =0;
                   $downloadedStocks = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
                                    ->where('province',$province)
                                    ->where('municipality',$municipality)
                                    ->where('is_cleared',0)
                                    ->where('seed_variety','like', '%'.$value->seed_variety.'%')
                                    ->sum('number_of_bag');


                 

                 $new_released = DB::table($GLOBALS['season_prefix'].'prv_'. $lib_prv->prv_code.'.new_released')
                    ->where('province',$province)
                    ->where('municipality',$municipality)
                    ->where('seed_variety','like', '%'.$value->seed_variety.'%')
                    ->where('category',"INBRED")
                    ->sum('bags_claimed');
                  

                $transferStocks= 0;
                // $transferStocks = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                //     ->where('origin_province',$province)
                //     ->where('origin_municipality',$municipality)                    
                //     ->where('seed_variety','like', '%'.$value->seed_variety.'%')
                //     ->sum('bags');
                    // $volume = $value->noOfbags - ($downloadedStocks + $new_released);
                    
                    // $transferStocks = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    //     ->where('province',$province)
                    //     ->where('municipality',$municipality)                    
                    //     ->where('seedVariety','like', '%'.$value->seed_variety.'%')
                    //     ->where('is_transferred',1)
                    //     ->sum('totalBagCount');
                    
                    // dd($province,$municipality,$value->seed_variety,$value->noOfbags,$downloadedStocks, $new_released,$volume);
                $volume = $value->noOfbags - ($downloadedStocks + $new_released + $transferStocks );
                $seed_variety_name = $value->seed_variety;
                // if($value->haswhole == 1){
                //     $seed_variety_name = $value->seed_variety." W";
                // }else{
                // }
                array_push($finalData,array(
                    'province' => $province,
                    'municipality' => $municipality,
                    'variety' =>$seed_variety_name,
                    'remaining_balance'=> $volume,
                    'package'=> (int)1,
                    'sub_package'=> (int)1,
                    'haswhole'=> (int)1,
                ));
                
            }



            return $finalData;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


}

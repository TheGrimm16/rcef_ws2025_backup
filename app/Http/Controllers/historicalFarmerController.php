<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Datatables;
class historicalFarmerController extends Controller
{
    public function farmerFinder(){
        $season =  DB::table('rcep_season.lib_season')
            ->orderBy("sort", "DESC")
            ->get();


        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('lib_prv.*')
                ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
                ->groupBy("lib_prv.province")
                ->orderBy("region_sort", 'ASC')
                ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{

                $provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();

                foreach($provinces as $key=> $pr){
                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");

                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
                    if($schema_check == null){
                        unset($provinces[$key]);
                    }

                }


            }
        }
        
      
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            return view("farmer_profile.historical_finder")
                ->with("season", $season)
                ->with("provinces", $provinces);

        }else{
            $mss ="You have no Priviledge for this page";
            return view("utility.pageClosed")
                ->with("mss",$mss);
        }
    
   
    }


    private function set_database($database_name,$host,$port, $user, $password ){
        try {
            \Config::set('database.connections.history_db.database', $database_name);
            \Config::set('database.connections.history_db.host', $host);
            \Config::set('database.connections.history_db.port', $port);
            \Config::set('database.connections.history_db.username', $user);
            \Config::set('database.connections.history_db.password', $password);
            DB::purge('history_db');

            DB::connection('history_db')->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }

    public function finderGenTable(Request $request){

        $conf = DB::table('rcep_season.lib_season')
            ->where("acronym", $request->season)
            ->first();
     
        $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where("province", $request->province)
            ->value("prv_code");
          
        $database_name = $conf->prefix."prv_".$prv;
            $this->set_database($database_name,$conf->host,$conf->port, $conf->user, $conf->password);

        $rsbsa = $request->rsbsa;
        $firstname = $request->firstname;
        $lastname = $request->lastname;
        
        if($rsbsa == ""){$rsbsa = "%";}
        if($firstname == ""){$firstname = "%";}
        if($lastname == ""){$lastname = "%";}


        $farmer_list = DB::connection("history_db")->table($conf->farmer_table_name)
            ->where("firstName", "LIKE", $firstname."%")
            ->where("lastName", "LIKE", $lastname."%")
            ->where("rsbsa_control_no", "LIKE", $rsbsa."%")
            ->get();
        

        $farmer_list = collect($farmer_list);

        return Datatables::of($farmer_list)

        ->addColumn('rsbsa', function($row){
            return $row->rsbsa_control_no;
        })
        ->addColumn('final_area', function($row){
            return $row->actual_area;
        })
        ->addColumn('name', function($row){
            return $row->lastName." ".$row->extName.", ".$row->firstName." ".$row->midName;
        })
        ->addColumn('address', function($row) {
                $prv_code = substr($row->rsbsa_control_no,0,8);
                $prv_code = str_replace("-", "",$prv_code);
              
              $address=   DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("prv", $prv_code)
                    ->first();
        
                if($address != null){
                    return $address->province.", ".$address->municipality;
                }else{
                    return "Error on RSBSA";
                }


           
        })
        ->addColumn('sex', function($row){
            return strtoupper(substr($row->sex,0,1));
        })
        ->addColumn('birthdate', function($row)  use ($conf){
                if($conf->other_info_table != "mix"){
                    $other_info = DB::connection("history_db")->table($conf->other_info_table)
                        ->where("rsbsa_control_no", $row->rsbsa_control_no)
                        ->where("farmer_id", $row->farmerID)
                        ->first();
                        if($other_info != null){
                            return $other_info->birthdate;
                        }else{
                            return "-";
                        }

                }else{
                    return date("Y-m-d", strtotime($row->birthdate));
                }

         
        })
        ->addColumn('contact_number', function($row)  use ($conf) {
          
            if($conf->other_info_table != "mix"){
                $other_info = DB::connection("history_db")->table($conf->other_info_table)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->where("farmer_id", $row->farmerID)
                    ->first();
                    if($other_info != null){
                        return $other_info->phone;
                    }else{
                        return "-";
                    }

            }else{
                return date("Y-m-d", strtotime($row->tel_no));
            }
           
           
           
           
        })
        ->addColumn('action', function($row) use ($prv,$conf){
            $btn = "";
            if($conf->prefix != $GLOBALS['season_prefix']){
                $btn = "<button class='btn btn-success btn-sm' onclick='push(".'"'.$prv.'"'.",".'"'.$conf->acronym.'"'.",".'"'.$row->id.'"'.")'>Push to Current Season</button> ";
            }


        //    $btn = "<a class ='btn btn-success btn-sm'  data-toggle='modal' data-rcef_id='".$row->rcef_id."' data-prv='".$prv."' data-target='#modal_farmer_info' ><i class='fa fa-folder-open-o' aria-hidden='true'></i>View</a>";
        //     if(Auth::user()->roles->first()->name == "rcef-programmer"){
        //         $prv_table = $GLOBALS['season_prefix']."prv_".$prv;

        //         $btn .= "<a class ='btn btn-warning btn-sm' onclick='reprint_id(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.")' ><i class='fa fa-credit-card-alt' aria-hidden='true'></i>RCEF ID</a>";
        //         // $btn .= "<a class ='btn btn-warning btn-sm' ><i class='fa fa-exchange' aria-hidden='true'></i>Change Address</a>";
                
        //     }


            return $btn;
        })
        ->make(true);
    }


    public function pushFarmer(Request $request){

        $conf = DB::table('rcep_season.lib_season')
            ->where("acronym", $request->season)
            ->first();
        $database_name = $conf->prefix."prv_".$request->prv;
            $this->set_database($database_name,$conf->host,$conf->port, $conf->user, $conf->password);

        $farmer_info = DB::connection("history_db")->table($conf->farmer_table_name)
                ->where("id", $request->id)
                ->first();
            
        if($conf->other_info_table != "mix"){
            $other_info = DB::connection("history_db")->table($conf->other_info_table)
            ->where("rsbsa_control_no",$farmer_info->rsbsa_control_no)
            ->where("farmer_id",$farmer_info->farmerID)
            ->first();

                if($other_info != null){
                    $birthdate = $other_info->birthdate;
                    $mother_fname = $other_info->mother_fname;
                    $mother_lname = $other_info->mother_lname;
                    $mother_mname = $other_info->mother_mname;
                    $mother_suffix = $other_info->mother_suffix;
                    $tel_no = $other_info->phone;
                }else{
                    $birthdate = "";
                    $mother_fname = "";
                    $mother_lname = "";
                    $mother_mname = "";
                    $mother_suffix = "";
                    $tel_no = "";
                 }
        }else{
                    $birthdate = $farmer_info->birthdate;
                    $mother_fname = $farmer_info->mother_fname;
                    $mother_lname = $farmer_info->mother_lname;
                    $mother_mname = $farmer_info->mother_mname;
                    $mother_suffix = $farmer_info->mother_suffix;
                    $tel_no = $farmer_info->tel_no;
        }
      

        $conf_curr = DB::table('rcep_season.lib_season')
            ->where("prefix", $GLOBALS['season_prefix'])
            ->first();
        
        if($conf_curr != null){

            $geo = str_replace("-","",$farmer_info->rsbsa_control_no);
            $lib_prv = DB::connection("delivery_inspection_db")->table("lib_prv")    
                    ->where("prv", substr($geo,0,6))
                    ->first();

            $brgy = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
                    ->where("geocode_brgy", substr($geo,0,9))
                    ->first();
            if($brgy != null){
                $brgy = $brgy->name;
            }else{
                $brgy = "";
            }

            try {

                $checker=0;
                $rcef_id="";
                while ($checker==0) {
                    $rcef_id = "R".$request->prv.strtoupper(substr(md5(time()), 0, 4));
                    $da_farmer_profile =  DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".".$conf_curr->farmer_table_name) 
                            ->where('db_ref',$rcef_id)->count(); 
                    if($da_farmer_profile == 0){
                            $checker = 1;
                    }                   
                }

                $checker_2=0;
                $rcef_id_int="";
                while ($checker_2==0) {
                    $rcef_id_int = $request->prv.rand(100000,999999);
                    $da_farmer_profile =  DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".".$conf_curr->farmer_table_name) 
                            ->where('rcef_id',$rcef_id_int)->count(); 
                    if($da_farmer_profile == 0){
                        $checker_2 = 1;
                    }                   
                }


                $last_id= DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".".$conf_curr->farmer_table_name) 
                ->insertGetId([
                    "db_ref" => $rcef_id,
                    "rcef_id" => $rcef_id_int,
                    "rsbsa_control_no" => $farmer_info->rsbsa_control_no,
                    "lastName" => $farmer_info->lastName,
                    "midName" => $farmer_info->midName,
                    "firstName" => $farmer_info->firstName,
                    "extName" => $farmer_info->extName,
                    "sex" => $farmer_info->sex,
                    "birthdate" =>  $birthdate,
                    "mother_fname" =>  $mother_fname,
                    "mother_lname" =>  $mother_lname,
                    "mother_mname" =>  $mother_mname,
                    "mother_suffix" =>  $mother_suffix,
                    "tel_no" =>  $tel_no,
                    "data_source" => "RSMS",
                    "province" => $lib_prv->province,
                    "municipality"=> $lib_prv->municipality,
                    "brgy_name" => $brgy,
                    "parcel_area" => $farmer_info->actual_area,
                    "crop_area" => $farmer_info->actual_area,
                    "actual_area" => $farmer_info->actual_area,
                    "rsms_id" => $farmer_info->id,
                    "data_season_entry" =>  strtoupper($request->season),
                    "final_area" => $farmer_info->actual_area,
                    "final_claimable" => ceil($farmer_info->actual_area * 2),
                ]);

                DB::table($GLOBALS['season_prefix']."log_push.trail")
                    ->insert([
                        "from_season" => strtoupper($request->season),
                        "to_season" => $GLOBALS['season_prefix'],
                        "prv_affected"=> $request->prv,
                        "old_id" => $request->id,
                        "new_id" => $last_id,
                        "rcef_id" => $rcef_id_int,
                        "user_name" => Auth::user()->username

                    ]);

                    return json_encode("success");

            } catch (\Throwable $th) {
                //throw $th;
            }


     



        }else{
            return json_encode("Failed to retrieve");
        }

            
               

      





    }

    
    function check_history_farmer($checking){
        $list = DB::table("temp_db.".$checking)
            // ->where("status", "not found")
            ->get();

        foreach($list as $data){
            $prv = str_replace("-","",substr($data->rsbsa_control_no,0,5));
                $rsbsa_pattern =    substr($data->rsbsa_control_no,0,8); 
            
             $db_prv = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
                            ->where("lastName", $data->lastName)
                            ->where("firstName", $data->firstName)
                            // ->where("midName", $data->middleName)
                            ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                            ->first();
             

                if($db_prv != null){
                  
                    $data_source = $db_prv->data_source;
                    if($db_prv->ws2022_area > 0){
                        $rsms_area = $db_prv->ws2022_area;
                   
                    }

                    if($db_prv->rsms_actual_area > 0){
                        $rsms_area = $db_prv->rsms_actual_area;
                    }else{
                        if($data_source == "RSMS"){
                            $rsms_area = $db_prv->crop_area;
                        }
                       
                    }
                    
                   
                    if($data_source == "FFRS"){
                        $ffrs_area = $db_prv->crop_area;
                    }else{
                        $ffrs_area = 0;
                    }


                    DB::table("temp_db.".$checking)
                        ->where("id", $data->id)
                        ->update([
                            "status" => "found",
                            "rsms_tagged_area" => $db_prv->final_area,
                            "rsms_area" => $rsms_area,
                            "ffrs_area" => $ffrs_area,
                        ]);




                }else{
                    DB::table("temp_db.".$checking)
                        ->where("id", $data->id)
                        ->update([
                            "status" => "not found"
                        ]);
                }



        }



    }
                    
               
                    
    

}

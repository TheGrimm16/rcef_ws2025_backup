<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Config;
use DB;
use Excel;
use PDFTIM;
use DOMPDF;

use App\HistoryMonitoring;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\utility;

use Session;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UtilityController extends Controller
{
    public function createProcessorAccount(){
        DB::beginTransaction();
          try {
  
              /*  $userData = DB::table('users')
               ->select("username")
              ->where('username',"LIKE",'pre_reg_rcef_%')
              ->get();
              $excel_data = json_decode(json_encode($userData), true); //convert collection to associative array to be converted to excel
         return Excel::create("RCEF-Pre-Reg-Account"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
             $excel->sheet("Sheet 1", function($sheet) use ($excel_data) {
                 $sheet->fromArray($excel_data);
               
                 
             });                               
              })->download('xlsx');  */
              
              for ($i=1; $i <= 6; $i++) { 
                  $token = Str::random(60);
                      $id = DB::table('users')->insertGetId(
                           [               
                            'firstName'  => 'processor_'.$i,
                            'middleName'  => 'N/A',
                            'lastName'  => 'payment processor_'.$i,
                            'extName'  => 'N/A',
                            'username'  => 'pay_processor_'.$i,
                            'email'  => 'processor_'.$i.'@philrice.gov.ph',
                            'secondaryEmail' => 'N/A',
                            'password' => '$2y$10$jh3EiHnrPTeW3x7kHkPiNu2VxII/11oBYjMCTd2hcEhpJ/wuVio6G',
                            'sex' => 'M',
                            'region' => '3',
                            'province' => '0354',
                            'municipality' => '--SELECT ASSIGNED MUNICIPALITY--',
                            'agencyId' => 0,
                            'stationId' => 0,
                            'position' => 'processor',
                            'designation' => 'processor',
                            'remember_token' => 'NULL',
                            'lastLogin' => 'NULL',
                            'api_token' => hash('sha256', $token),
                            'isDeleted' => 0,
                            'picture' => 'N/A',
                            'contact_no' => null
                           ]
                       );
        
                       $id2 = DB::table('role_user')->insertGetId(
                        [               
                            'userId' => $id,
                            'roleId' => 34,
                        ]);
                      
                }
  
              DB::commit();
              return "create users ".$i;
          } catch (\Throwable $th) {
               DB::rollback();
              return $th;
          }
          
      }

    public function createPreRegAccount(){
      DB::beginTransaction();
        try {

            /*  $userData = DB::table('users')
             ->select("username")
            ->where('username',"LIKE",'pre_reg_rcef_%')
            ->get();
            $excel_data = json_decode(json_encode($userData), true); //convert collection to associative array to be converted to excel
       return Excel::create("RCEF-Pre-Reg-Account"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
           $excel->sheet("Sheet 1", function($sheet) use ($excel_data) {
               $sheet->fromArray($excel_data);
             
               
           });                               
            })->download('xlsx');  */
            
            for ($i=21; $i <= 40; $i++) { 
                $token = Str::random(60);
                    $id = DB::table('users')->insertGetId(
                         [               
                          'firstName'  => 'pre registration_'.$i,
                          'middleName'  => 'N/A',
                          'lastName'  => 'pre reg_'.$i,
                          'extName'  => 'N/A',
                          'username'  => 'pre_reg_rcef_'.$i,
                          'email'  => 'pre_reg_rcef_'.$i.'@philrice.gov.ph',
                          'secondaryEmail' => 'N/A',
                          'password' => '$2y$10$jh3EiHnrPTeW3x7kHkPiNu2VxII/11oBYjMCTd2hcEhpJ/wuVio6G',
                          'sex' => 'M',
                          'region' => '3',
                          'province' => '0354',
                          'municipality' => '--SELECT ASSIGNED MUNICIPALITY--',
                          'agencyId' => 0,
                          'stationId' => 0,
                          'position' => 'pre_reg_rcef',
                          'designation' => 'pre_reg_rcef',
                          'remember_token' => 'NULL',
                          'lastLogin' => 'NULL',
                          'api_token' => hash('sha256', $token),
                          'isDeleted' => 0,
                          'picture' => 'N/A',
                          'contact_no' => null
                         ]
                     );
      
                     $id2 = DB::table('role_user')->insertGetId(
                      [               
                          'userId' => $id,
                          'roleId' => 36,
                      ]);
                    
              }

            DB::commit();
            return "create users ".$i;
        } catch (\Throwable $th) {
             DB::rollback();
            return $th;
        }
        
    }
    
    public function cross_match_lgu_prv($prv, $process_type){
        //0 -> nothing; 1->matched_saved_same_or_lower; 2->matched_not_saved_higher; 3->not_found

        
        if($process_type == "download_high_area"){
            if($prv == "all"){ $prv = $GLOBALS['season_prefix']."prv_%"; }

            $tbl_list =   DB::table("information_schema.TABLES")
                ->select("TABLE_NAME")
                  ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."rcep_lgu_data")
                  ->where("TABLE_NAME", "LIKE", $prv)
                  ->groupBy("TABLE_NAME")
                  ->get();

                $excel_arr = array();
              foreach ($tbl_list as $db){
                    $list = DB::table($GLOBALS['season_prefix']."rcep_lgu_data.".$db->TABLE_NAME)
                        ->where("status", 2)
                        ->orderBy("province")
                        ->orderBy("municipality")
                        ->get();

                    if(count($list)>0){
                        array_push($excel_arr, array(
                            "tbl_name" => $db->TABLE_NAME,
                            "data" => $list
                        ));
                    }

                        
       


              }
              $excel_data = json_decode(json_encode($excel_arr), true); //convert collection to associative array to be converted to excel
              return Excel::create("EXCEL_CROSS_RESULT"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
               
                  
                foreach($excel_data as $exc ){
                    $inf = $exc["data"];
                    $title = substr($exc["tbl_name"], 0, 30);

                   $excel->sheet($title, function($sheet) use ($inf) {
                    $sheet->fromArray($inf);
                }); 
                }


                 })->download('xlsx');

        }elseif($process_type=="cross_check"){

            if($prv == "all"){ $prv = $GLOBALS['season_prefix']."prv_%"; }

            $tbl_list =   DB::table("information_schema.TABLES")
                  ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."rcep_lgu_data")
                  ->where("TABLE_NAME", "LIKE", $prv)
                  ->groupBy("TABLE_NAME")
                  ->get();
              
              foreach ($tbl_list as $db){
                  $farmer_list = DB::table($GLOBALS['season_prefix']."rcep_lgu_data.".$db->TABLE_NAME)
                      ->where("status", 2)
                      ->get();

                      foreach($farmer_list as $farmer_info){
                          $lgu_id = $farmer_info->id;
                          $lgu_area = $farmer_info->actual_area;
                          $status = 0;
                          if($lgu_area <= 0){
                              continue;
                          }

                          $check_db = DB::table($db->TABLE_NAME.".farmer_profile_processed")
                              //->where("rsbsa_control_no", $farmer_info->rsbsa_control_no)
                              ->where("lastName", "LIKE",$farmer_info->last_name)
                              //->where("midName", "LIKE",$farmer_info->mid_name)
                              ->where("firstName", "LIKE",$farmer_info->first_name)
                              ->first();

                              if(count($check_db)>0){
                                  $db_id = $check_db->id;
                                  $db_area = $check_db->actual_area;
                                  
                                  if($lgu_area <= $db_area){
                                      $status = 1;
                                      
                                  }else{
                                      $status = 2;
                                  }
                              }else{
                                  $status =3;
                                  $db_area = 0;
                              }
                              
                              if($status == 1){
                                  $area = $lgu_area;
                                  if($area != floor($area)){
                                      $dec =  $area - floor($area); 

                                      if($dec <= 0.5 ){
                                      $area = floor($area) + 0.5;
                                      }else{
                                          $area = floor($area) + 1;
                                      }
                                  }
                                  $bags = $area * 2;

                                  DB::table($db->TABLE_NAME.".farmer_profile_processed")
                                      ->where("id", $db_id)
                                      ->update([
                                          "actual_area" =>  $lgu_area,
                                          "total_claimable" => $bags
                                      ]);
                              }

                              DB::table($GLOBALS['season_prefix']."rcep_lgu_data.".$db->TABLE_NAME)
                                  ->where("id", $lgu_id)
                                  ->update([
                                      "status" => $status,
                                      "server_area" => $db_area
                                  ]);
                          }
                     

                      DB::table($GLOBALS['season_prefix']."rcep_lgu_data.processed_prv")
                          ->insert([
                              "prv_name" => $db->TABLE_NAME
                          ]);


              }



        }
       




     

        
        
        

        



        










    }




    public function replicateprvs($key){

        if($key == "6ffa1a65a2db"){

            $rpt_list = DB::table("information_schema.TABLES")
                ->select("TABLE_SCHEMA as db")
                ->where("TABLE_SCHEMA", "like", "prv_%")
                ->where("TABLE_NAME", "farmer_profile_processed")
              //  ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."prv_0354")
                ->groupBy("TABLE_SCHEMA")
                ->get();
          
                foreach ($rpt_list as $rpt) {
                    $db_name = $rpt->db;
                    DB::table($db_name.".farmer_profile_processed")
                        ->update([
                            "is_claimed" => 0,
                            "total_claimed" => 0,
                            "is_ebinhi" => 0
                        ]);
               
                 
                }
        }else{
            return json_encode("ERR K");
        }

    
    }


    public function backuprv(){
        $rpt_list = DB::table("information_schema.TABLES")
                ->select("TABLE_SCHEMA as db")
                ->where("TABLE_SCHEMA", "like", "prv_%")
                ->groupBy("TABLE_SCHEMA")
                ->get();
        $date = date("m-d-Y");
        $time = date("h_i A");
        $folder = "D:\/rcep_sms_db_backup_ws2021\/DS2022_PRV_POPULATED\/".$date."_".$time;        
        $mkdir = 'mkdir "'.$folder.'"';
        exec($mkdir);
           foreach ($rpt_list as $rpt) {
            $bckup = 'mysqldump -h localhost -P 3306 --single-transaction -u jpalileo -pP@ssw0rd '.$rpt->db.' > "'.$folder.'\/'.$rpt->db.'.sql';
            exec($bckup);
                }
    }

    public function recompute_claimable($key, $prv){
        if($key == "6ffa1a65a2db"){
            $rpt_list = DB::table("information_schema.TABLES")
                ->select("TABLE_SCHEMA as db")
                ->where("TABLE_SCHEMA", "=", $prv)
                //->whereRaw("TABLE_SCHEMA NOT IN (SELECT prv from sdms_db_dev.prv_process)")
                ->groupBy("TABLE_SCHEMA")
                ->get();
              //  dd($rpt_list);
                foreach ($rpt_list as $rpt) {
                    $tbl_list = DB::table("information_schema.TABLES")
                        ->select("TABLE_NAME as tbl")
                        ->where("TABLE_SCHEMA", $rpt->db)
                        ->where("TABLE_NAME", "farmer_profile_processed")
                        ->groupBy("TABLE_NAME")
                        ->first();
                        if(count($tbl_list)) {
                            $farmer_list = DB::table($rpt->db.".farmer_profile_processed")
                                //->where("total_claimable", "<=", 0)
                                ->where("actual_area", ">", 0)
                                ->get();
                            //   dd($farmer_list);
                                foreach ($farmer_list as $key => $value) {
                                      $area = $value->actual_area;
                                    if($area != floor($area)){
                                        $dec =  $area - floor($area); 
                                        if($dec <= 0.5 ){
                                        $area = floor($area) + 0.5;
                                        }else{
                                            $area = floor($area) + 1;
                                        }
                                    }
                                    $total_claimable = $area * 2;
                                    DB::table($rpt->db.".farmer_profile_processed")
                                    ->where("id", $value->id)
                                    ->update([
                                        "total_claimable" => $total_claimable,
                                        //"icts_rsbsa" => "0"
                                    ]);
                                }
                        }
               }
        }else{
            return json_encode("ERR K");
        }

    
    }




    public function removeRptDuplicates($key){
        if($key == "6ffa1a65a2db"){


            $rpt_list = DB::table("information_schema.TABLES")
                ->select("TABLE_SCHEMA as db")
                ->where("TABLE_SCHEMA", "=", $GLOBALS['season_prefix']."prv_0129")
                //->whereRaw("TABLE_SCHEMA NOT IN (SELECT prv from sdms_db_dev.prv_process)")
                ->groupBy("TABLE_SCHEMA")
                ->get();
              //  dd($rpt_list);

                foreach ($rpt_list as $rpt) {
                    $tbl_list = DB::table("information_schema.TABLES")
                        ->select("TABLE_NAME as tbl")
                        ->where("TABLE_SCHEMA", $rpt->db)
                        ->where("TABLE_NAME", "farmer_profile_processed")
                        ->groupBy("TABLE_NAME")
                        ->first();

                        if(count($tbl_list)) {

                            $farmer_list = DB::table($rpt->db.".farmer_profile_processed")
                                ->where("total_claimable", "<=", 0)
                                ->where("actual_area", ">", 0)
                                ->get();
                            //   dd($farmer_list);

                                foreach ($farmer_list as $key => $value) {
                                    
                                      $area = $value->actual_area;

                                    if($area != floor($area)){
                                        $dec =  $area - floor($area); 

                                        if($dec <= 0.5 ){
                                        $area = floor($area) + 0.5;
                                        }else{
                                            $area = floor($area) + 1;
                                        }
                                    }
                                    $total_claimable = $area * 2;


                                    DB::table($rpt->db.".farmer_profile_processed")
                                    ->where("id", $value->id)
                                    ->update([
                                        "total_claimable" => $total_claimable,
                                        "icts_rsbsa" => "0"
                                    ]);


                                }




                        }
                    
                }
        }else{
            return json_encode("ERR K");
        }

    }



    //dbmp2.philrice.gov.ph/rcef_ws2021/forceServerSide/6ffa1a65a2db
    public function forceServerSide($key){
        if($key == "6ffa1a65a2db"){
            $cmd ="start chrome http://localhost/rcef_ds2022/analytics/execute_report/variety";
             exec($cmd); 
             $cmd ="start chrome http://localhost/rcef_ds2022/farmer/profile/contact/nationwide";
             exec($cmd); 


             return "The System Started the process for contacts and variety";
        
        
        }else{
            return "ERR on API";
        }
    }



    public function forceBatFile($key){
        

    if($key == "6ffa1a65a2db"){
        //$cmd ="start chrome http://localhost/rcef_ds2022/report_lib/";
        //  exec($cmd);
        //$cmd ="start chrome http://localhost/rcef_ds2022/station_report/compute/station_data";
         // exec($cmd);
        $cmd ="start chrome http://localhost/rcef_ws2021/report/excel/scheduled/list";
          exec($cmd);
        //$cmd ="start chrome http://localhost/rcef_ds2022/farmer/profile/contact/nationwide";
        //  exec($cmd);
        //$cmd ="start chrome http://localhost/rcef_ds2022/report/gad/farmer/process";
         // exec($cmd);

        return json_encode("Initializing Process on Server");
    }else{
       return json_encode("ERR K");
    }

   
    

    }

    public function resetdeliveryToInspection($batchTicketNumber){
            $checkStatus = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_status")
                ->where("batchTicketNumber", $batchTicketNumber)
                ->orderby("dateCreated", "DESC")
                ->where("status", 1)
                ->first();
               // dd($checkStatus);
                if(count($checkStatus)>0){
                        DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                        ->where("batchTicketNumber", $batchTicketNumber)
                        ->delete();
                      
                            //REMOVE DATA INSPECTION
                            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_inspection")
                            ->where("batchTicketNumber", $batchTicketNumber)
                            ->delete();

                            //REMOVE tbl sampling
                             DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_sampling")
                            ->where("batchTicketNumber", $batchTicketNumber)
                            ->delete();

                            //UPDATE TBL DELIVERY
                             DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->where("batchTicketNumber", $batchTicketNumber)
                            ->update(["inspectorAllocated"=>0]);

                            //UPDATE TBL DELIVERY
                             DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_status")
                            ->where("deliveryStatusId", $checkStatus->deliveryStatusId)
                            ->update(["status"=>3]); 


                    //LOGS
                     DB::connection('mysql')->table('lib_logs')
                    ->insert([
                        'category' => 'RESET_INSPECTION',
                        'description' => 'Reset inspection report of batch ticket #: `'.$batchTicketNumber.'`',
                        'author' => Auth::user()->username,
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ]);


                return json_encode("Delivery Reset to inspection success - ".$batchTicketNumber);
                }else{
                    return json_encode("failed - ".$batchTicketNumber.' =>  status not passed');
                }
    }




    public function recopyOLDaccre($pass){      
        if($pass !== "P@ssw0rd"){
            return json_encode("FAILED WRONG PASS");
        }
        $rlas = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                ->where("noOfBags", ">", 0)
                ->get();

            $i=0;
            $noRLA = array();
            foreach ($rlas as $key => $value) {
                $checkAccre = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("updated_accreditation_no", $value->coopAccreditation)
                ->first();
                if(count($checkAccre)>0){
                    DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                    ->where("rlaId",$value->rlaId)
                    ->update([
                        "coop_name" => $checkAccre->coopName,
                        "coopAccreditation" => $checkAccre->accreditation_no,
                        "moaNumber" => $checkAccre->current_moa
                        ]); 
                    $i++;
                }else{
                    $checkAccre2 = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                    ->where("accreditation_no", $value->coopAccreditation)
                    ->first();

                    if(count($checkAccre2)>0){
                         DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                            ->where("rlaId",$value->rlaId)
                            ->update([
                               "coop_name" => $checkAccre2->coopName,
                                "coopAccreditation" => $checkAccre2->accreditation_no,
                                "moaNumber" => $checkAccre2->current_moa
                                ]);
                            $i++;
                    }else{
                        array_push($noRLA,$value);
                    }
                }
            } 


        $sgs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
                ->where('is_active', 1)
                ->get();

                $x = 0;
                $noSg = array();
                foreach ($sgs as $key => $value) {
                   $checkAccre = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                        ->where("updated_accreditation_no", $value->coop_accred)
                        ->first();
                        if(count($checkAccre)>0){
                            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")
                            ->where("sg_id",$value->sg_id)
                            ->update([
                                "coop_accred" => $checkAccre->accreditation_no
                                ]); 
                            $x++;
                        }else{
                             $checkAccre2 = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                            ->where("accreditation_no", $value->coop_accred)
                            ->first();
                            if(count($checkAccre2)>0){
                                    $x++;
                            }else{
                                array_push($noSg, $value);
                            }
                        }
                }


        $return = array(
            "RLAs Updated" => $i,
            "RLA Not Found" =>$noRLA, 
            "SGs Updated" => $x,
            "SG Not Found" => $noSg
        );

        return json_encode($return);
    }

    public function farmerProfilePullIndex(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->groupBy('province')
            ->orderby('prv', 'ASC')
            ->get();


            return view('utility.farmerProfilePuller')
                ->with('provinces', $provinces);
    }

    public function released_data_index(){
        
            if(Auth::user()->roles->first()->name == "rcef-programmer"){
                // $mss = "Temporary Close";
                // return view('utility.pageClosed',compact("mss"));
        
                $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                    ->select("lib_dropoff_point.*")
                    ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.regionName", "=", "lib_dropoff_point.region")
                    ->groupBy('lib_dropoff_point.province')
                    ->orderby('lib_prv.region_sort', 'ASC')
                    ->get();
            }
            elseif(Auth::user()->roles->first()->name == "branch-it"){
     
                if(Auth::user()->stationId == ""){
                    $mss = "No Station Tagged";
                    return view("utility.pageClosed")
                        ->with("mss",$mss);
                }else{
    
                    $prov_station =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                        ->select("province")
                        ->where("stationID", Auth::user()->stationId)
                        ->groupBy("province")
                        ->get();
                    $prov_station = json_decode(json_encode($prov_station), true);
                    

                        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                        ->select("lib_dropoff_point.*")
                        ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.regionName", "=", "lib_dropoff_point.region")
                        ->whereIn("lib_prv.province", $prov_station)
                        ->groupBy('lib_dropoff_point.province')
                        ->orderby('lib_prv.region_sort', 'ASC')
                        ->get();
                }
    
    
                
                // $mss = "Temporary Close";
                // return view('utility.pageClosed',compact("mss"));
        



            }else{
                // $mss = "Temporary Close";
                // return view('utility.pageClosed',compact("mss"));
        





                 if(Auth::user()->province == ""){
                    $mss = "No Province Tagged";
                    return view("utility.pageClosed")
                        ->with("mss",$mss);

                 }else{
                        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                    ->groupBy('province')
                    ->where("prv", "LIKE", Auth::user()->province."%")
                    ->orderby('region_sort', 'ASC')
                    ->get();
                 }


             
            }



            return view('utility.released_data_index')
                ->with('provinces', $provinces);
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
    
    
    public function genReleasedTbl(Request $request){

        // dd($request->municipality);
        // $db = $GLOBALS['season_prefix']."rcep_reports_prv_view";

        if($request->municipality == "0"){
            $muni_name = "%";
        }else{
            $muni_name = $request->municipality;
        }

        if($request->municipality == "0"){
            $municipality = "%";
        }else{
            $municipality = $request->municipality;
        }
        $prv_request = $request->prv;

        
        if($request->search_name == ""){
            $search_name = "%";
        }else{
            $search_name = $request->search_name;
        }
        $categoryData = "HYBRID";
        if($request->category == "INBRED"){
            $categoryData ="INBRED";
        }
        

        $new_released_data_ref = DB::table($GLOBALS['season_prefix']."prv_".$prv_request.".new_released")
            ->select("db_ref")
            ->where("municipality", "LIKE", $muni_name)
            ->where("category", $categoryData)
            ->groupBy("db_ref")
            ->get();
        $new_released_data_ref = json_decode(json_encode($new_released_data_ref), true);

        // dd($new_released_data_ref);

        $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("prv_code", $prv_request)
            ->where("municipality", "LIKE", $municipality)
            ->first();
            // dd($lib_prv);
        if($lib_prv != null){
            $municipality = substr($lib_prv->prv,0,2)."-".substr($lib_prv->prv,2,2)."-".substr($lib_prv->prv,4,2);
        }else{
            $municipality =  substr($prv_request,0,2)."-".substr($prv_request,2,2);
        }

        // dd($municipality);

        $new_released_data = DB::table($GLOBALS['season_prefix']."prv_".$prv_request.".new_released")
            ->where("municipality", "LIKE", $muni_name)
            ->get();
        $new_released_data = json_decode(json_encode($new_released_data), true);
        // dd($new_released_data);

       

        $tbl_array = array();
        foreach (array_chunk($new_released_data_ref,500) as $index)  
        {
            $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_request.".farmer_information_final")
            ->where("rsbsa_control_no", 'like', "%".$search_name."%")
                ->whereIn("db_ref", $index)
            ->orWhere("rcef_id", 'like', "%".$search_name."%")
                ->whereIn("db_ref", $index)
            ->orWhere("lastName", 'like', "%".$search_name."%")
                ->whereIn("db_ref", $index)
            ->orWhere("firstName", 'like', "%".$search_name."%")
                ->whereIn("db_ref", $index)
            ->orWhere("midName", 'like', "%".$search_name."%")
                ->whereIn("db_ref", $index)
            ->orderBy("lastName")
            ->orderBy("firstName")
            ->orderBy("midName")
            ->get();

          
        
            foreach($farmer_info as $farmer){
                $release_result =  $this->search_to_array($new_released_data, "db_ref", $farmer->db_ref);
                foreach ($release_result as $rel){
                    if($rel['category'] != $categoryData){
                        continue;
                    }
                    array_push($tbl_array, array(
                        "rcef_id" => $farmer->rcef_id,
                        "db_ref" => $farmer->db_ref,
                        "rsbsa_control_no" => $farmer->rsbsa_control_no,
                        "lastName" => $farmer->lastName,
                        "midName" => $farmer->midName,
                        "firstName" => $farmer->firstName,
                        "extName" => $farmer->extName,
                        "actual_area" => $farmer->final_area,
                        "birthdate" => $farmer->birthdate,
                        "rel_seed_variety" => $rel["seed_variety"],
                        "rel_bags_claimed" => $rel["bags_claimed"],
                        "rel_claimed_area" => $rel["claimed_area"],
                        "rel_date_released" =>$rel["date_released"],
                        "rel_released_by" => $rel["released_by"],
                        "new_released_id" => $rel["new_released_id"],
                        "sex" => $rel["sex"],
                        "mother_name" => $farmer->mother_name,
                        "tel_no" => $farmer->tel_no,
                        "yield_no_of_bags_ls" => $rel["yield_no_of_bags_ls"],
                        "yield_wt_per_bag" => $rel["yield_wt_per_bag"],
                        "yield_area_harvested_ls" => $rel["yield_area_harvested_ls"]
                    ));
                }
            
        

            }

        }

        $tbl = collect($tbl_array);





        return Datatables::of($tbl)
        ->addColumn('action', function($row) use ($prv_request,$request){  
            // dd($row);
//administrator
            if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "branch-it" || Auth::user()->roles->first()->name == "administrator" ){
            
                $btn = "<a class='btn btn-warning btn-sm '
                data-toggle='modal' data-target='#update_farmer_info'
                data-release_id = ".'"'.$row["new_released_id"].'"'."
                data-rsbsa  = ".'"'.$row["rsbsa_control_no"].'"'."
                data-farmer_id  = ".'"'.$row["rcef_id"].'"'."
                data-farmer_fname  = ".'"'.$row["firstName"].'"'."
                data-farmer_mname  = ".'"'.$row["midName"].'"'."
                data-farmer_lname  = ".'"'.$row["lastName"].'"'."
                data-farmer_ext  = ".'"'.$row["extName"].'"'."
                
                data-sex  = ".'"'.$row["sex"].'"'."
                data-birthdate  = ".'"'.date("m/d/Y", strtotime($row["birthdate"])).'"'."
                data-tel_number  = ".'"'.$row["tel_no"].'"'."

                data-mother_name  = ".'"'.$row["mother_name"].'"';

                
                 if($request->category == "INBRED"){
                    $btn .= "
                    

                data-total_production  = ".'"'.$row["yield_no_of_bags_ls"].'"'."
                data-ave_weight_per_bag  = ".'"'.$row["yield_wt_per_bag"].'"'."
                data-area_harvested  = ".'"'.$row["yield_area_harvested_ls"].'"'."
                data-prv  = ".'"'.$prv_request.'"'."><i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit</a>
                    <a class='btn btn-danger btn-sm ' onclick='reset(".$row["new_released_id"].","
                    .'"'.$row["rsbsa_control_no"].'"'.","
                    .'"'.$row["db_ref"].'"'.","
                    .'"'.$row["firstName"].'"'.","
                    .'"'.$prv_request.'"'.
                    ")' ><i class='fa fa-trash' aria-hidden='true'></i> Delete</a>";
                }

            }else{
               
            }


           
            return $btn;

        })
        ->make(true);
      
    }

    public function farmer_distributed_update(Request $request){
$db = $GLOBALS['season_prefix'].'prv_'.$request->prv;

        if($db == ""){
            $log = "NO DB SELECTED";
            $status = 0;
        }else{


      
            $released_tbl = DB::table($db.".new_released")
                ->where("new_released_id", $request->release_id)
                ->first();

            $farmer_profile = DB::table($db.".farmer_information_final")
                ->where("rcef_id", $request->farmer_id)              
                ->first();

            if(count($released_tbl)>0){
                //update
                $status = 1;
        
                DB::table($db.".new_released")
                ->where("new_released_id", $request->release_id)
                    ->update([
                        "yield_area_harvested_ls" =>$request->area_harvested,
                        "yield_no_of_bags_ls" => $request->total_production,
                        "yield_wt_per_bag" => $request->ave_weight_per_bag
                    ]);



                    $log = "Released - OK";





            }else{
                $status = 0;
                $log = "NO RELEASE DATA";
            }


        }


        return json_encode(array(
            "status" => $status,
            "log" => $log

        ));






    }


    public function fix_claiming_brgy(){
        
        $info = DB::table("INFORMATION_SCHEMA.TABLES")
            ->where("TABLE_SCHEMA", "LIKE", "ds2024_prv_%")
            ->where("TABLE_NAME", "farmer_information_final_may")
            ->get();
          
            foreach($info as $db){
                $err_claiming = DB::table($db->TABLE_SCHEMA.".farmer_information_final")
                    ->whereRaw('REPLACE(claiming_prv, "-", "") !=  LEFT(claiming_brgy,6) ')
                    ->get();
                
                if(count($err_claiming) > 0){
                    dd($err_claiming);
                }


            }
        
        
        // 


    }



// win
    public function farmer_distri_reset(Request $request){
// dd($request->released_id);
        $released = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".new_released")
            ->where("new_released_id", $request->released_id)
            ->first();
        $logs_arr = array();
        $logs_arr["release_id"] = $request->released_id;
        $logs_arr["fid"] = $request->fid;
        $logs_arr["action"]  = "deleted";
        $logs_arr["action_by"]  = Auth::user()->username;


        if($released != null){
            $logs_arr["bags_deleted"]  = $released->bags_claimed;
            $logs_arr["dop"]  = $released->prv_dropoff_id;
        }else{
            return json_encode("FAILED");
        }
        

            $farmer_profile = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information_final")
            ->where("rsbsa_control_no", $request->rsbsa)
            ->where("firstName", "LIKE",$request->fname)
            ->where("db_ref", $request->fid)
            // ->where("lastName", "LIKE",$released->farmer_lname)
            ->first();
        
        

        if(count($farmer_profile)>0){
                $date = date("m-d-Y");
                $time = date("h_i A");
           
                    DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".new_released")
                        ->where("new_released_id", $released->new_released_id)
                        ->delete();

                    $tmps = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".new_released")
                        ->select( DB::raw("SUM(claimed_area) as claimed_area"))
                        ->where("db_ref",  $request->fid)
                        ->first();

            if($tmps->claimed_area >= 1){
                DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information_final")
                // ->where("rsbsa_control_no", $request->rsbsa)
                ->where("db_ref", $request->fid)

                    ->update([
                        "total_claimed" => ceil($tmps->claimed_area * 2),
                        "total_claimed_area" => $tmps->claimed_area,
                        "is_claimed" => 1,
                    ]);

            }else{

                DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information_final")
                ->where("db_ref", $request->fid)

                    ->update([
                        "total_claimed" => 0,
                        "total_claimed_area" => 0,
                        "is_claimed" => 0,
                        "is_replacement" => 0,
                        "replacement_area" => 0,
                        "replacement_bags" => 0,
                       
                    ]);
                
            }     


            //LOGS
                DB::table($GLOBALS['season_prefix']."sdms_db_dev.distribution_delete_log")
                    ->insert($logs_arr);


                $ret = "success";
            //}else{
             //   $ret = "err NOT THE SAME NAME";
            //}
        }else{
            $ret = "err";
        }
           
        



        return json_encode($ret);

    }


    public function farmerProfileProcess(Request $request){
            $prv = $request->prv;

            $db = $GLOBALS['season_prefix'].'prv_'.$prv;

            $regCode = 'R'.substr($prv, 0, 2);
           // dd($regCode);
            $i = 0;

            $farmerNoDistri = array();
            $note = "";
            $note2 = "";
            $farmer_profiles = DB::table($db.'.farmer_profile')
                ->where("distributionID", "LIKE", $regCode.'%')
                ->orderby('id')
                    ->get();
                foreach ($farmer_profiles as $farmer) {
                        //checkIFhasDistri
                    $distriCheck = DB::table($db.'.released')
                        ->where('farmer_id', $farmer->farmerID)
                        ->where('rsbsa_control_no', $farmer->rsbsa_control_no)
                        ->first();

                    if(count($distriCheck)<=0){

                        if($farmer->lastName != "" || $farmer->firstName != "" || $farmer->rsbsa_control_no != ""){
                           array_push($farmerNoDistri, array(
                            "id" => $farmer->id,
                            "lastname" => $farmer->lastName,
                            "firstname" => $farmer->firstName,
                            "sex" => $farmer->sex,
                            "rsbsa_control_no" => $farmer->rsbsa_control_no,
                            "farmer_id" => $farmer->farmerID,
                            "actual_area" => $farmer->actual_area,
                            "area" => $farmer->area
                            )); 
                        }

                    }else{
                       // dd($farmer->rsbsa_control_no);
                    }
                }


            if(count($farmerNoDistri)>0){
                   // dd($farmerNoDistri);
               
                $note .= "<br> <b> ALL FARMER NO DISTRI:".count($farmerNoDistri)." <br>";

                   

                    foreach ($farmerNoDistri as $farmerNoArr) {
                     //WS2020
                    \Config::set('database.connections.ls_inspection_db.host', '172.16.10.25');
                    \Config::set('database.connections.ls_inspection_db.port', '4406');
                    \Config::set('database.connections.ls_inspection_db.database', $db);
                    \Config::set('database.connections.ls_inspection_db.username', 'rcef_user');
                    \Config::set('database.connections.ls_inspection_db.password', 'SKF9wzFtKmNMfwyz');
                    DB::purge('ls_inspection_db');

                        $getArea = DB::connection('ls_inspection_db')->table("farmer_profile")
                            ->where("lastName", 'like', '%'.$farmerNoArr["lastname"].'%')
                            ->where("firstName", 'like','%'.$farmerNoArr["firstname"].'%')
                            ->where("rsbsa_control_no", $farmerNoArr["rsbsa_control_no"])
                            ->where("farmerID", $farmerNoArr["farmer_id"])
                            ->first();

                            if(count($getArea)>0){
                                if($getArea->area !== $farmerNoArr["area"] OR $getArea->actual_area !== $farmerNoArr["actual_area"])
                                {
                                         
                                    DB::table($db.'.farmer_profile')
                                        ->where('id', $farmerNoArr["id"])
                                        ->update([
                                            "area" => $getArea->area,
                                            "actual_area" => $getArea->actual_area
                                        ]);
                                    
                                    $note2 .= "ID: ".$farmerNoArr["id"].",".$farmerNoArr["area"]."=>".$getArea->actual_area." "."WS2020"." | ";
                                    $i++;
                                }


                              
                            }else{
                                //DS2021 CHECK
                                 \Config::set('database.connections.ls_inspection_db.host', '192.168.10.23');
                                \Config::set('database.connections.ls_inspection_db.port', '3306');
                                \Config::set('database.connections.ls_inspection_db.database', $db);
                                \Config::set('database.connections.ls_inspection_db.username', 'rcef_web');
                                \Config::set('database.connections.ls_inspection_db.password', 'SKF9wzFtKmNMfwy');
                                DB::purge('ls_inspection_db');

                                         $getAreaDS = DB::connection('ls_inspection_db')->table("farmer_profile")
                                        ->where("lastName", 'like', '%'.$farmerNoArr["lastname"].'%')
                                        ->where("firstName", 'like','%'.$farmerNoArr["firstname"].'%')
                                        ->where("rsbsa_control_no", $farmerNoArr["rsbsa_control_no"])
                                        ->where("farmerID", $farmerNoArr["farmer_id"])
                                        ->first();


                                            if(count($getAreaDS)>0){

                                               


                                                   if($getAreaDS->area !== $farmerNoArr["area"] OR $getAreaDS->actual_area !== $farmerNoArr["actual_area"])
                                                        {
                                                           
                                                                DB::table($db.'.farmer_profile')
                                                                    ->where('id', $farmerNoArr["id"])
                                                                    ->update([
                                                                        "area" => $getAreaDS->area,
                                                                        "actual_area" => $getAreaDS->actual_area
                                                                    ]);
                                                                
                                                                $note2 .= "ID: ".$farmerNoArr["id"].",".$farmerNoArr["area"]."=>".$getAreaDS->actual_area." "."DS2021"." |";
                                                                $i++;
                                                        }







                                            }



                            }





                    }

                    $note .= "TOTAL UPDATED:".$i."  <br>";
                    $note .= $note2;



    
                //RETURN ls_inspectionInfo
                    \Config::set('database.connections.ls_inspection_db.host', '192.168.10.23');
                    \Config::set('database.connections.ls_inspection_db.port', '3306');
                    \Config::set('database.connections.ls_inspection_db.database', $GLOBALS['season_prefix'].'rcep_delivery_inspection');
                    \Config::set('database.connections.ls_inspection_db.username', 'rcef_web');
                    \Config::set('database.connections.ls_inspection_db.password', 'SKF9wzFtKmNMfwy');
                    DB::purge('ls_inspection_db');
            }
               return json_encode($note);
    }

    public function cleanRla(){
        
        $rla_updated = 0;
        $tbl_delivery_updated = 0;
        $actual_updated = 0;
        $rlas_data = "";
        $tbl_rla = "";
        $actual_rla="";

        $rlas = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->get();

        dd($rlas);

                return "RLA UPDATED: ".$rla_updated.'<br>'.$rlas_data."<br><b> DELIVERY UPDATED: </b>".$tbl_delivery_updated.'<br>'.$tbl_rla."<br> <b> ACTUAL UPDATED: </B>".$actual_updated.'<br>'.$actual_rla;
    }




    public function viewUploadingArea(){
      $succ = "";
          return view('utility.checkArea')
         ->with("succ", $succ);
    }


    public function import_check_area(Request $request){
        dd($request->all());       
        $path = $request->file('file-upl')->getRealPath();
                $data = array_map('str_getcsv', file($path));
                // dd($data);
                $havingData = "";

         if($request->has('header')){
             unset($data[0]);       
            if(isset($data[1])){ $havingData = "T";}else{$havingData="F";}
        }else{
            if(isset($data[0])){ $havingData = "T";}else{$havingData="F";}
        }


                $i = 0;
                $editedFarmer = "";
                $unedited = "";
                $notExisting = "";
        if($havingData == "T"){
            $csv_array = array();
            foreach ($data as $key => $value) {
                    //dd($value[1]);
                if($value[1] !=0 || $value[1] != "")
                {            
                    $prv = str_replace("-", "", $value[1]);
                    $prvcode = $GLOBALS['season_prefix'].'prv_'.substr($prv, 0,4);
                   // dd($prvcode);

//                    dd($value[1]);
                    $checkDB_area = DB::table($prvcode.'.farmer_profile')
                        ->where('firstName', 'like', '%'.$value[3].'%')
                        ->where('lastName', 'like', '%'.$value[2].'%' )
                        ->where('rsbsa_control_no', $value[1])
                        ->first();
                        //dd($checkDB_area);
                        if(count($checkDB_area)>0){
                                if(trim($checkDB_area->area)!=trim($value[14]) || trim($checkDB_area->actual_area)!=trim($value[14])){
                                    DB::table($prvcode.'.farmer_profile')
                                    ->where('id', $checkDB_area->id)
                                    ->update([
                                        "area" => $value[14],
                                        "actual_area" => $value[14]
                                    ]);
                                $i++;
                                if($editedFarmer != "")$editedFarmer .=";";
                                $editedFarmer .= $checkDB_area->id.' - '.$checkDB_area->rsbsa_control_no;
                                }else{
                                    if($unedited !="")$unedited .=";";
                                    $unedited .= $value[1].' - '.$value[14];
                                }
                        }else{
                            if($notExisting !="")$notExisting .=";";
                            $notExisting .= $value[1].' - '.$value[14];
                        }





                }   
            }


                if($i>0){                
                   
            $succ = '<div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> SUCCESS </strong> '.$i.' EDITED BENEFICIARY  '. '
                <br>
                '.$editedFarmer.'
                <br> UNEDITED:
                '.$unedited.'  <br> NOT EXISTING: '.$notExisting.'
            </div>';
                }else{
                    $succ = '<div class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <strong><i class="fa fa-info-circle"></i> FAILED </strong> NO DATA ROWS 
                        <br> UNEDITED:
                        '.$unedited.' <br> NOT EXISTING: '.$notExisting.'

                    </div>';
                }

        }else{
            $succ = '<div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> FAILED </strong> INCORRECT CSV FORMAT
            </div>';
        }

            return view('utility.checkArea')
                    ->with('succ', $succ);

    }
    
    public function deleteme(){
    $brgy_arr = array('12-47-02-015%','12-47-02-016%','12-47-02-024%','12-47-02-027%','12-47-02-039%','12-47-03-004%','12-47-03-017%','12-47-03-021%','12-47-03-022%','12-47-03-023%','12-47-03-024%','12-47-09-011%','12-47-09-014%','12-47-09-015%','12-47-09-016%','12-47-09-019%','12-47-09-020%','12-47-09-025%','12-47-09-027%','12-47-09-029%','12-47-09-030%','12-47-09-033%','12-47-09-047%','12-47-09-050%','12-47-09-051%','12-47-11-003%','12-47-11-008%','12-47-11-009%','12-47-11-013%','12-47-11-014%','12-47-11-016%','12-47-11-017%','12-47-11-022%','12-47-11-029%','12-47-11-036%','12-47-12-005%','12-47-12-006%','12-47-12-007%','12-47-12-008%','12-47-12-009%','12-47-12-010%','12-47-12-012%','12-47-12-019%','12-47-12-024%','12-47-12-030%','12-47-12-034%','12-47-12-035%','12-47-12-037%','12-47-12-040%','12-47-12-041%','12-47-12-044%','12-47-12-048%','12-47-12-062%','12-47-17-017%'
    );

        $return_arr = array();
       
        foreach($brgy_arr as $pat){
            $list =DB::table("rsms_unique.farmer_profile")
            ->where("rsbsa_control_no", "LIKE", $pat)
            ->where("actual_area", ">", 0)
            ->get();
            foreach($list as $row){

                $other_info = DB::table("rsms_unique.other_info")
                    ->where("farmer_id", $row->farmerID)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->first();

                    if($other_info != null){
                        $birthdate = $other_info->birthdate;
                        $mother_name = $other_info->mother_name;
                        $tel_no = $other_info->phone;
                    }else{
                        $birthdate = "";
                        $mother_name = "";
                        $tel_no = "";
                    }
                $geo = str_replace("-","",$pat);
                $geo = str_replace("%","",$geo);
                $prv = DB::table("ds2024_rcep_delivery_inspection.lib_prv")    
                        ->where("prv", substr($geo,0,6))
                        ->first();
                
                $brgy = DB::table("ds2024_sdms_db_dev.lib_geocodes")
                    ->where("geocode_brgy", $geo)
                    ->value("name");

                DB::table("ds2024_prv_1547.farmer_information_final")
                    ->insert([
                        "rsbsa_control_no" => $row->rsbsa_control_no,
                        "lastName" => $row->lastName,
                        "midName" => $row->midName,
                        "firstName" => $row->firstName,
                        
                        "extName" => $row->extName,
                        "sex" => $row->sex,
                        "birthdate" =>  $birthdate,
                        "mother_name" =>  $mother_name,
                        "tel_no" =>  $tel_no,
                        "data_source" => "RSMS",
                        "province" => $prv->province,
                        "municipality"=> $prv->municipality,
                        "brgy_name" => $brgy,
                        "parcel_area" => $row->actual_area,
                        "crop_area" => $row->actual_area,
                        "actual_area" => $row->actual_area,
                        "rsms_id" => $row->id,
                        "data_season_entry" =>  "DS2021",
                        "final_area" => $row->actual_area,
                        "final_claimable" => ceil($row->actual_area * 2),
                        "frm_prv_code" => "1247",
                        "to_prv_code" => "1547"
                    ]);    

            }


        }

        dd($return_arr);


        // return Excel::create("ERR".date("Y-m-d g:i A"), function($excel) use ($err_excel) {
        //             $excel->sheet("BENEFICIARY LIST", function($sheet) use ($err_excel) {
        //                 $sheet->fromArray($err_excel);
        //                 $sheet->freezeFirstRow();
                        
        //             });
        //         })->download('xlsx');

    }


	public function reLogPS(){
		$batches = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
			->where('transferCategory', 'P')
			->where('is_transferred', 1)
			->where('remarks', '<>', '')
			->groupBy('batchTicketNumber')
			->get();
		//dd($batches);
				foreach ($batches as $batches) {
					$checkLog = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
						->where('new_batch_number', $batches->batchTicketNumber)
						->count();
						if($checkLog<=0){
							$seedVariety = "";
							$ls_batch_number = trim(str_replace("transferred from previous season batch:", "", $batches->remarks));
							$new_batch_number = $batches->batchTicketNumber;
							$dest_province = $batches->province;
							$dest_municipality = $batches->municipality;
							$dest_prv_id = $batches->prv_dropoff_id;

							$totalBag = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
								->where('batchTicketNumber', $new_batch_number)
								->sum('totalBagCount');

							$seed = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
								->select('seedVariety')
								->where('batchTicketNumber', $new_batch_number)
								->get();

								foreach ($seed as $seed) {
									if($seedVariety!="")$seedVariety.="|";
										$seedVariety .= $seed->seedVariety;
								}

						  $lastseasonData = DB::connection('ls_inspection_db')->table('tbl_delivery')
			       			->where('batchTicketNumber', $ls_batch_number)
			                	->first();
								//dd($lastseasonData );
			                if(count($lastseasonData)>0){
			                	$coopAccreditation = $lastseasonData->coopAccreditation;
			                	$origin_province = $lastseasonData->province;
			                	$origin_municipality = $lastseasonData->municipality;
			                	$origin_prv_id = $lastseasonData->prv_dropoff_id;
			                	
			                	DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
									->insert([
										"coop_accreditation" => $coopAccreditation,
										"batch_number" => $ls_batch_number,
										"new_batch_number" => $new_batch_number,
										"origin_province" => $origin_province,
										"origin_municipality" => $origin_municipality,
										"origin_dop_id" => $origin_prv_id,
										"destination_province" => $dest_province,
										"destination_municipality" => $dest_municipality,
										"destination_dop_id" => $dest_prv_id,
										"seed_variety" => $seedVariety,
										"bags" => $totalBag,
										"date_created" => date('Y-m-d h:m:s'),
										"transferred_by" => ""
									]); 

						

			                }
						}						
				}
	}



	public function paymaya_upload(){
		 	$succ = "";
		 return view('paymaya.utility.index')
		 ->with("succ", $succ);
	}



     public function import_parse_lib(Request $request){
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
         unset($data[0]);       
      
         $i = 0;
            foreach ($data as $key => $value) {

            if($value[1]=="" || $value[1]==0 || $value[2]=="" || $value[2]==0){
                continue;
               
            }
            DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.lib_yield_inputs")
                ->insert([
                    "category" => "weight_per_bag",
                    "province" => strtoupper($value[0]),
                    "from_value" => $value[1],
                    "to_value" => $value[2],
                    ]);            
        }
               return json_encode($i);
    
        }



        //crosscheck beneficiaries
        public function import_parse(Request $request){
             
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            //dd($data);
            

            $i = 0;
            foreach($data as $value){
                //dd($value[0]);
                $check = DB::table($GLOBALS['season_prefix']."rcep_farmer_list.tbl_farmer")
                    ->where("rsbsa_control_no", $value[0])
                    ->where("firstName", $value[2])
                    ->where("lastName", $value[4])
                    ->where("sex", $value[6])
                    ->where("birthDate", $value[7])
                    ->where("date_released", $value[18])
                    ->where("farmer_id", $value[19])
                    ->where("seed_variety", $value[17])
                    ->where("bags", $value[16])
                    ->where("season", "WS2020")
                    ->first();

                if(count($check)>0){

                }else{
                    DB::table($GLOBALS['season_prefix']."rcep_farmer_list.tbl_farmer")
                        ->insert([
                            "rsbsa_control_no" => $value[0],
                            "qr_code" =>   $value[1],  
                            "firstName" =>  $value[2],
                            "middleName" => $value[3],
                            "lastName" => $value[4],
                            "extName" => $value[5],
                            "sex" => $value[6],
                            "birthDate" => $value[7],
                            "telephone" => $value[8],
                            "province" => $value[9],
                            "municipality" => $value[10],
                            "mother_name" => $value[11],
                            "mother_middlename" => $value[12],
                            "mother_lastname" => $value[13],
                            "mother_suffix" => $value[14],
                            "actual_area" => $value[15],
                            "bags" => $value[16],
                            "seed_variety" => $value[17],
                            //"yield" => $value[18],
                            "date_released" => $value[18],
                            "farmer_id" => $value[19],
                            "released_by" => $value[20],
                            "season" => "WS2020"
                        ]);


                        $i++;
                }
            }
            return json_encode("SUCCESS _ ".$i);
         //dd($data);
        }



    public function import_parse_update(Request $request){
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
         unset($data[0]);       
        //dd($data);
         $i = 0;
         $unupdated = "";
            foreach ($data as $key => $value) {
            if($value[1]==""){
                continue;
                $unupdated .= 'key: '.$key;
            }

            $area = $value[13];
            if($area != floor($area)){
                $dec =  $area - floor($area); 

                if($dec <= 0.5 ){
                $area = floor($area) + 0.5;
                }else{
                    $area = floor($area) + 1;
                }
            }
            $bags = $area * 2;
                $check = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")            
                    ->where("paymaya_code", $value[1])
                    //->where("beneficiary_id", $value[0])
                    ->where("rsbsa_control_no", $value[8])
                    ->where("firstname", $value[9])
                    ->first();
                    if(count($check)>0){
                        $claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                        ->where("paymaya_code", $value[1])
                        ->first();
                            if(count($claim)<=0){

                            DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")            
                                    ->where("paymaya_code", $value[1])
                                    //->where("beneficiary_id", $value[0])
                                    ->where("rsbsa_control_no", $value[8])
                                    ->where("firstname", $value[9])
                                    ->update([
                                        //"drop_off_point" => $value[5],
                                        //"schedule_start" => $value[6],
                                        //"schedule_end" => $value[7],
                                        //"coop_accreditation" => $value[21]
                                        "area" => $value[13],
                                        "bags" => $bags,
                                        //"is_printed" => "R"
                                    ]);
                                    //->delete();


                                    $i++;
                            }else{
                                $unupdated .= "    already_code:".$value[1];
                            }
                         
                    }else{
                           $unupdated .= "    unexist_code:".$value[1];
                    }
                }

               return json_encode($i." <br> -- ".$unupdated);
    }


	public function import_parse_insert(Request $request){
				$path = $request->file('csv_file')->getRealPath();
	   			$data = array_map('str_getcsv', file($path));

	    if($request->has('header')){
	     unset($data[0]);	    
	    }

	    //dd(count($data[1]));
       // dd($data);

	    if(count($data[1])==22){
	    	$csv_array = array();
	    	foreach ($data as $key => $value) {
               

                
                if($value[0] !=0 || $value[0] != "")
                {            
                    $batch_array = array(
                        "beneficiary_id"=>$value[0],
                        "contact_no"=>$value[1],
                        "province"=>$value[2],
                        "municipality"=>$value[3],
                        "drop_off_point"=>$value[4],
                        "schedule_start"=>$value[5],
                        "schedule_end"=>$value[6],
                        "rsbsa_control_no"=>$value[7],
                        "firstname"=>$value[8],
                        "middname"=>$value[9],
                        "lastname"=>$value[10],
                        "extname"=>$value[11],
                        "area"=>$value[12],
                        "bags"=>$value[13],
                        "region"=>$value[14],
                        "province2"=>$value[15],
                        "municipality2"=>$value[16],
                        "barangay"=>$value[17],
                        "is_active"=>1,
                        "sex"=>$value[19],
                        "coop_accreditation"=>$value[20],
                        "is_printed" => $value[21]  
                    );
                        array_push($csv_array, $batch_array);        
                }	
            
                

            }
             //dd($csv_array);
                if(count($csv_array)>0){    
             $this->process_paymaya_beneficiary($csv_array);
            $succ = '<div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> SUCCESS </strong> '.count($csv_array).' PAYMAYA BENEFICIARY UPLOADED '. '
            </div>';
                }else{
                    $succ = '<div class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <strong><i class="fa fa-info-circle"></i> FAILED </strong> NO DATA ROWS 
                    </div>';
                }

	    }else{
	    	$succ = '<div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> FAILED </strong> INCORRECT CSV FORMAT
            </div>';
	    }

	    	return view('paymaya.utility.index')
	    			->with('succ', $succ);

	}

    public function processPaymayaCode(){

        $tbl_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                ->where('paymaya_code', '')
                ->get();
       //         dd($tbl_beneficiaries);
        $csv_array = array();
            foreach ($tbl_beneficiaries as $key => $value) {

                if($value->beneficiary_id !=0 || $value->beneficiary_id != "")
                {            
                    $batch_array = array(
                        "beneficiary_id"=>$value->beneficiary_id,
                        "contact_no"=>$value->contact_no,
                        "province"=>$value->province,
                        "municipality"=>$value->municipality,
                        "drop_off_point"=>$value->drop_off_point,
                        "schedule_start"=>$value->schedule_start,
                        "schedule_end"=>$value->schedule_end,
                        "rsbsa_control_no"=>$value->rsbsa_control_no,
                        "firstname"=>$value->firstname,
                        "middname"=>$value->middname,
                        "lastname"=>$value->lastname,
                        "extname"=>$value->extname,
                        "area"=>$value->area,
                        "bags"=>$value->bags,
                        "region"=>$value->region,
                        "province2"=>$value->province2,
                        "municipality2"=>$value->municipality2,
                        "barangay"=>$value->barangay,
                        "is_active"=>$value->is_active,
                        "sex"=>$value->sex,
                        "coop_accreditation" => $value->coop_accreditation,
                        "is_printed" => $value->is_printed
                    );
                        array_push($csv_array, $batch_array);        
                }   
            }

                if(count($csv_array)>0){                
                    $this->process_paymaya_beneficiary($csv_array);
            $succ = '<div class="alert alert-success alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <strong><i class="fa fa-info-circle"></i> SUCCESS </strong> '.count($csv_array).' PAYMAYA BENEFICIARY UPLOADED '. '
            </div>';
                }else{
                    $succ = '<div class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <strong><i class="fa fa-info-circle"></i> FAILED </strong> NO DATA ROWS 
                    </div>';
                }

                return view('paymaya.utility.index')
                    ->with('succ', $succ);
    }


	public function process_paymaya_beneficiary($arr){

        $ret = array();
        foreach ($arr as $data) {
            $area = $data["area"];

        	if($area != floor($area)){
        		$dec =  $area - floor($area); 

        		if($dec <= 0.5 ){
        		$area = floor($area) + 0.5;
	        	}else{
	        		$area = floor($area) + 1;
	        	}

        	}
        	$bags = $area * 2;
        	
           // $hashed = 1;
            $hashed = $this->hashData($data["beneficiary_id"],$data["contact_no"],$data["rsbsa_control_no"],$data["lastname"],$data["firstname"],$data["middname"],$data["area"],$bags); //id, contact; rsbsa; ln; fn; md
            //dd($hashed);
            //$hashed = "";
           
            $b = array(
                "beneficiary_id" => $data["beneficiary_id"],
                "paymaya_code" => $hashed,
                "contact_no" => $data["contact_no"],
                "province" => $data["province"],
                "municipality" => $data["municipality"],
                "drop_off_point" => $data["drop_off_point"],
                "schedule_start" => date("Y-m-d", strtotime($data["schedule_start"])),
                "schedule_end" => date("Y-m-d", strtotime($data["schedule_end"])),
                "rsbsa_control_no" => $data["rsbsa_control_no"],
                "firstname" => $data["firstname"],
                "middname" => $data["middname"],
                "lastname" => $data["lastname"],
                "extname" => $data["extname"],
                "area" => $data["area"],
                "bags" => $bags,
                "region" => $data["region"],
                "province2" => $data["province2"],
                "municipality2" => $data["municipality2"],
                "barangay" => $data["barangay"],
                "is_active" => $data["is_active"],
                "sex" => $data["sex"],
                "coop_accreditation" => $data["coop_accreditation"],
                "is_printed" => $data["is_printed"]
            ); 


            array_push($ret, $b); 
        }


        foreach ($ret as $arr_val) {

                $checkExist = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                    ->where('beneficiary_id', $arr_val["beneficiary_id"])
                    ->where('firstname', $arr_val["firstname"])
                    ->where('middname', $arr_val["middname"])
                    ->where('lastname', $arr_val["lastname"]) 
                    ->where('rsbsa_control_no', $arr_val["rsbsa_control_no"]) 
                    ->first();
                    if(count($checkExist)>0){
                      // dd($arr_val);
                    //    DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                    //    ->where('beneficiary_id', $arr_val["beneficiary_id"])
                    //    ->update($arr_val); 

                    }else{
                        DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                        ->insert($arr_val);
                    }

                    //dd($ret);
        }   


    }

	
	 public function hashData($beneficiary_id,$contact,$rsbsa,$lastname,$firstname,$middlename,$area,$bags){
       $ret = array();
        $toBeHash = $contact.$rsbsa.$lastname.$firstname.$middlename;
            do{
                $hashed =  md5(hash::make(trim($toBeHash)));
                $hashed = substr($hashed, 0, 7); //LIMIT TO 5
                $hashed = strtoupper($hashed);  //UPPER CASE
                $seeInt = intval($hashed); //PARSE TO INT 
                $seeInt = strlen($seeInt); //CHECK LEGHT
                 $codeCheck = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_paymaya_lib')
                        ->where('paymaya_code', $hashed)
                        ->first();
            }while($seeInt==7 OR count($codeCheck)>0); //RETURN IF NUMBER ONLY

                $arr = array(
                    "beneficiary_id"=>$beneficiary_id,
                    "rsbsa_control_no"=>$rsbsa,
                    "firstname" =>$firstname,
                    "middlename" =>$middlename,
                    "lastname" =>$lastname,
                    "paymaya_code" => $hashed,
                    "contact" => $contact,
                    "date_created" => date('Y-m-d'),
                    "area" => $area,
                    "bags" => $bags
                );

            array_push($ret, $arr);
    
           
                      DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_paymaya_lib')
                        ->insert($ret);
                

        return($hashed); 
    }

	public function index(){
       $utility = new utility();
       $regional_list = $utility->getRegions();

       return view('utility.pageClosed')
            ->with("mss", "This Page is temporary closed");



        return view('report_reprocess.index')
        	 ->with(compact('regional_list'));
	}

	public function getProvince($region){
		$utility = new utility();
		$province_list = $utility->getProvince($region);

		return json_encode($province_list);
	}

	public function getMunicipalities(Request $request){
        if($request->category == "INBRED"){
            $utility = new utility();
            $municipality_list = $utility->getMunicipality($request->province);
          
            return json_encode($municipality_list);
        }else{
            $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_prv')
            ->select('municipality', 'prv')
            ->where('province', $request->province)
            ->orderBy('municipality','ASC')
            ->groupBy('municipality')
            ->get();
          
        return $municipality_list;
        }
		
	}

    public function ReportReprocessProvicial_Level(Request $request){
        $utility = new utility();
        $method = 0; //1:municipality ; 0:perProvince 

        if($method ==1){
              $result = $utility->reportReprocess($request->region,$request->province,$request->municipality,$request->prv); //SET RELEASED = 1
            return $result;
        }else{
            $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("province", $request->province)->groupBy("municipality")
            //->skip(0)
            //->take(10)
            ->get();


            //dd($municipality_list);

            foreach ($municipality_list as $key => $province) {

                $result = $utility->reportReprocess($province->regionName,$province->province,$province->municipality,$province->prv);
            }  
               return "Done Processing ".$request->province;

        }
         
    }

    public function ReportReprocess(Request $request){
    	$utility = new utility();
    	$method = 1; //1:municipality ; 0:perProvince 

        if($method ==1){
              $result = $utility->reportReprocess($request->region,$request->province,$request->municipality,$request->prv); //SET RELEASED = 1
            return $result;
        }else{
            $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("province", $request->province)->groupBy("municipality")->get();
            foreach ($municipality_list as $key => $province) {

                $result = $utility->reportReprocess($province->regionName,$province->province,$province->municipality,$province->prv);
            }  
               return "Done Processing ".$request->municipality;

        }
         
	}

    
    public function ForceReportReprocess($prv_database, $province_database, $municipality_database, $municipality_name, $province_name){
        $utility = new utility();
       
         $result = $utility->process_municipalities($prv_database, $province_database, $municipality_database, $municipality_name, $province_name);
       return $result;    
    
    }    

    public function statisticReprocess(Request $request){
        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->where('region', $request->region)
            ->where('province', $request->province)
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get();

         foreach($municipalities as $municipality_row){
            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;

            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);
            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;

            //dd($database);
            if(count($prv_dist_data) > 0){
            //dd($municipality_row->province);
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    ->get();


                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->orderBy('farmerID')
                        ->first();
                        
                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        
                        $yield = 0;
                        if($farmer_profile->yield <= 5 && $farmer_profile->yield != 0){
                            $yield = $farmer_profile->yield * 20;
                        }else{
                            $yield = $farmer_profile->yield;
                        }
                        
                        if($yield > 0 && $farmer_profile->area_harvested > 0){
							// if($yield < 50 || $yield > 120){
							// 	$yield = $yield / $farmer_profile->actual_area;
							// }else{
							// 	$yield = $yield;
							// }

                            $weight = $farmer_profile->weight_per_bag;
                            $no_bags = $farmer_profile->yield;
                            $area = $farmer_profile->area_harvested;

                            $yield = (floatval($no_bags) * floatval($weight)) / floatval($area);
                            $yield = $yield / 1000;

							$farmer_dividend += 1;
							
						}else{
							$yield = $farmer_profile->yield;
						}
                        
                        $municipal_yield += $yield; 
                        
                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        $municipal_yield += 0;
                    }
                }

                if( $municipality_row->municipality == ""){
                    dd($municipal_actual_area);
                }

            }
            
            if($municipal_yield > 0 && $farmer_dividend > 0){
                $municipal_yield = $municipal_yield / $farmer_dividend;
            }


            $checkExisting = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $municipality_row->region)
                ->where('province', $municipality_row->province)
                ->where('municipality', $municipality_row->municipality)
                ->first();
                if(count($checkExisting)>0){
                    DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                        ->where('region', $municipality_row->region)
                        ->where('province', $municipality_row->province)
                        ->where('municipality', $municipality_row->municipality)
                        ->update([
                            'total_farmers'     => $municipal_farmers,
                            'total_bags'        => $municipal_bags,
                            'total_dist_area'   => $municipal_dis_area,
                            'total_actual_area' => $municipal_actual_area,
                            'total_male'        => $municipal_male,
                            'total_female'      => $municipal_female,
                            'yield'             => $municipal_yield,
                            'farmers_with_yield'=> $farmer_dividend,
                            'date_generated'    => date("Y-m-d H:i:s")
                        ]);

                        
                }else{
                    DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                        ->insert([
                            'region'            => $municipality_row->region,
                            'province'          => $municipality_row->province,
                            'municipality'      => $municipality_row->municipality,
                            'total_farmers'     => $municipal_farmers,
                            'total_bags'        => $municipal_bags,
                            'total_dist_area'   => $municipal_dis_area,
                            'total_actual_area' => $municipal_actual_area,
                            'total_male'        => $municipal_male,
                            'total_female'      => $municipal_female,
                            'yield'             => $municipal_yield,
                            'farmers_with_yield'=> $farmer_dividend,
                            'date_generated'    => date("Y-m-d H:i:s")
                        ]);
        
                }
        }  //END OF STATISTIC OF MUNICIPALITY


         //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'))
            ->where('total_bags', '!=', 0)
            ->where('region', $request->region)
            ->where('province', $request->province)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        foreach($provinces as $p_row){

            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('province', $p_row->province)
                ->where('total_bags', '!=', 0)
                ->count();
                
            $total_municipalities_with_yield= DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('province', $p_row->province)
                ->where('yield', '!=', 0)
                ->where('total_bags', '!=', 0)
                ->get();
            
            if($p_row->total_farmers_with_yield > 0){
                //$total_yield =  $p_row->total_yield / $p_row->total_farmers_with_yield;
                $total_yield =  $p_row->total_yield / count($total_municipalities_with_yield);
            }else{
                $total_yield = 0;
            }           
              //  dd($p_row->region." ".$p_row->province);

                $checkExisting = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
                    ->where('region', $p_row->region)
                    ->where('province', $p_row->province)
                    ->first();

                    if(count($checkExisting)>0){

                        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
                            ->where('region', $p_row->region)
                            ->where('province', $p_row->province)
                            ->update([
                                'total_municipalities' => $total_municipalities,
                                'total_farmers'     => $p_row->total_farmers,
                                'total_bags'        => $p_row->total_bags,
                                'total_dist_area'   => $p_row->total_dist_area,
                                'total_actual_area' => $p_row->total_actual_area,
                                'total_male'        => $p_row->total_male,
                                'total_female'      => $p_row->total_female,
                                'yield'             => $total_yield,
                                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                                'total_yield_of_municipalities' => $p_row->total_yield,
                                'date_generated'    => date("Y-m-d H:i:s")
                            ]);     
                    }else{
                         DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
                            ->insert([
                                'region'            => $p_row->region,
                                'province'          => $p_row->province,
                                'total_municipalities' => $total_municipalities,
                                'total_farmers'     => $p_row->total_farmers,
                                'total_bags'        => $p_row->total_bags,
                                'total_dist_area'   => $p_row->total_dist_area,
                                'total_actual_area' => $p_row->total_actual_area,
                                'total_male'        => $p_row->total_male,
                                'total_female'      => $p_row->total_female,
                                'yield'             => $total_yield,
                                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                                'total_yield_of_municipalities' => $p_row->total_yield,
                                'date_generated'    => date("Y-m-d H:i:s")
                            ]);
                    }
        }  //PROVINCIAL
    



        return 'Statistics Reprocessing for '.$request->province.' is Done';

    }    

 
    // E:\RCEF\Backup\Database\DS2023


    public function databaseBackup(){
        //RJ 05182021
        $date = date("m-d-Y");
        $time = date("h_i A");

        $folder = "D:\/rcep_sms_db_backup_ws_2022\ds2023/".$date."_".$time;        
        $mkdir = 'mkdir "'.$folder.'"';

        
        exec($mkdir);
        // $folder_hdd = "F:\/rcep_sms_db_backup_ws_2022\db\WS2022\/".$date."_".$time;        
        // $mkdir_hdd = 'mkdir "'.$folder_hdd.'"';
        // exec($mkdir_hdd);

       $bckup = 'C:\\UniServerZ\core\mysql\bin\mysqldump.exe -h 192.168.10.44 -P 3306 -u json -pZeijan@13 ds2024_rcep_paymaya > "'.$folder.'\rcep_paymaya.sql"';
    //    $da = 'C:\\UniServerZ\core\mysql\bin\mysqldump.exe -h 172.16.10.41 -P 3306 --single-transaction -u rcef_user4 -plciz]eYhSaUbTcpF rcep_paymaya> "D:\/rcep_sms_db_backup_ws_2022\WS2022\rcep_paymaya.sql"';

       $data = exec($bckup);

        $prvList = DB::table("information_schema.SCHEMATA")->select("SCHEMA_NAME")->where("SCHEMA_NAME", "LIKE", "ds2024_prv_%")->where("SCHEMA_NAME", "NOT LIKE", "%_bak%")->get();  
        foreach ($prvList as $prv) {
            $bckup = 'C:\\UniServerZ\core\mysql\bin\mysqldump.exe -h 192.168.10.44 -P 3306 --single-transaction -u json -pZeijan@13 '.$prv->SCHEMA_NAME.' > "'.$folder.'\/'.$prv->SCHEMA_NAME.'.sql';
            exec($bckup);
        }

        $dbArr = array("ds2032_rcep_allocations","rcep_db_logs","rcep_delivery_inspection","rcep_delivery_inspection_mirror","rcep_distribution_id","rcep_dist_updates","rcep_farmers","rcep_reports","rcep_reports_mirror","rcep_seed_cooperatives","rcep_transfers","rcep_transfers_ps","geotag_db2","sdms_db_dev","seed_growers","seed_seed","rcep_paymaya","rcep_palaysikatan", "moet_db", "extension_db");

        foreach ($dbArr as $db) {
             $bckup = 'C:\\UniServerZ\core\mysql\bin\mysqldump.exe -h 192.168.10.44 -P 3306 --single-transaction -u json -pZeijan@13 '.$db.' > "'.$folder.'\/'.$db.'.sql';
             exec($bckup);

            //  $bckup = 'mysqldump -h 172.16.10.41 -P 3306 --single-transaction -u json -pZeijan@13 '.$db.' > "'.$folder_hdd.'\/'.$db.'.sql';
            //  exec($bckup);
        }
         
         echo "<script>window.close();</script>";
         return 'done';
        
    }




    public function printedIarList(Request $request){

        if(Auth::user()->roles->first()->name == "branch-it"){
            $station_name = DB::table("geotag_db2.tbl_station")
                ->where("stationId",Auth::user()->stationId)
                ->first();

                if(count($station_name)>0){
                    $provinces = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_station")
                        ->select("province")
                        ->where("station", $station_name->stationName)
                        ->groupBy("province")
                        ->get();

                    $provinces = json_decode(json_encode($provinces),true);
                   // dd($provinces);
                        $batch_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                                ->select("batchTicketNumber")
                                ->where("is_cancelled", "!=", "1")
                                ->whereIn("province", $provinces)
                                ->groupBy("batchTicketNumber")
                                ->get();
                              
                        $batch_list = json_decode(json_encode($batch_list),true);
                        $batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                        ->whereIn("batchTicketNumber",$batch_list)
                        ->orderBy('dateCreated', 'desc')
                        ->where('is_printed','1')
                        ->get();



                }else{
                    $batch = array();
                }


          
        }else{
            $batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->orderBy('dateCreated', 'desc')
            ->where('is_printed','1')
            ->get();
        }



       

        //$pdf_name = "[".$mun_code."] FLSAR_".strtoupper($province_name)."_".strtoupper($municipality_name).".pdf";

        $return_str = ""; 
        $return_str .= "<option value='0'>.....Please Select Batch Number.....</option>";
            //$i=0;
        foreach($batch as $row){
            $return_str .= "<option value='".$row->batchTicketNumber."'>$row->batchTicketNumber</option>";
            //$return_str .= "<option value='$row->prv'>$row->province < $row->municipality</option>";
            //$i++;
        }
            //$return_str .= "<option value='".$i."'>$i</option>";
        return $return_str;
    }


    public function pullIarInfo($batchID){
       
        $batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->select(DB::raw("SUM(totalBagCount) as sumBags"),"province","municipality","deliveryDate")
        ->where('is_cancelled','0')
        ->where('batchTicketNumber', $batchID)
        ->first();
        //return $batch;

        

        echo json_encode($batch);
    }



     public function iarReprint($batchID){


        $batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->where("batchTicketNumber", $batchID)
                ->update(["is_printed" => 0]);

        //return $batch;
        echo json_encode("SUCCESS");
    }


    


    public function clear_cache(){
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return "cleared";
    }

  


    public function utilityFunction($data1, $data2, $data3){

        if($data1=="test"){
            if($data2 == "gen_db_ref"){


                $list = DB::table("ds2024_prv_".$data3.".farmer_information_final")
                        ->where("db_ref", "")
                        ->get();

                $x = 0;
                foreach($list as $data){
                 
                    $checker=0;
                    $rcef_id="";
                    while ($checker==0) {
                        $rcef_id = "W".$data3.strtoupper(substr(md5(time()), 0, 4));
                        $da_farmer_profile =  DB::table("ds2024_prv_".$data3.".farmer_information_final")
                                ->where('db_ref',$rcef_id)->count(); 
                        if($da_farmer_profile == 0){
                            DB::table("ds2024_prv_".$data3.".farmer_information_final")
                                ->where("id", $data->id)
                                ->update([
                                    "db_ref" => $rcef_id
                                ]);
                                $checker = 1;
                        }                   
                    }
    
           
                   
                    $x++;
                }

                return $x;

            }


            elseif($data2 == "gen_rcef_id"){

                $list = DB::table("ds2024_prv_".$data3.".farmer_information_final")
                        ->where("rcef_id", "")
                        ->get();

                $x = 0;
                foreach($list as $data){
                 
                    $checker=0;
                    $rcef_id="";
                    while ($checker==0) {
                        $rcef_id = "R".$data3.strtoupper(substr(md5(time()), 0, 4));
                        $da_farmer_profile =  DB::table("ds2024_prv_".$data3.".farmer_information_final")
                                ->where('db_ref',$rcef_id)->count(); 
                        if($da_farmer_profile == 0){
                            DB::table("ds2024_prv_".$data3.".farmer_information_final")
                                ->where("id", $data->id)
                                ->update([
                                    "db_ref" => $rcef_id
                                ]);
                                $checker = 1;
                        }                   
                    }
    
                    $checker_2=0;
                    $rcef_id_int="";
                    while ($checker_2==0) {
                        $rcef_id_int = $data3.rand(100000,999999);
                        $da_farmer_profile =  DB::table("ds2024_prv_".$data3.".farmer_information_final")
                                ->where('rcef_id',$rcef_id_int)->count(); 
                        if($da_farmer_profile == 0){
                            DB::table("ds2024_prv_".$data3.".farmer_information_final")
                            ->where("id", $data->id)
                            ->update([
                                "rcef_id" => $rcef_id_int
                            ]);

                            $checker_2 = 1;
                        }                   
                    }
                   
                    $x++;
                }

                return $x;

            }


        //    $seed_var =  array('NSIC Rc222','NSIC Rc216','NSIC Rc160','NSIC Rc120','NSIC Rc27','NSIC Rc218','NSIC Rc354','NSIC Rc358','NSIC Rc400','NSIC Rc402','NSIC Rc436','NSIC Rc438','NSIC Rc440','NSIC Rc442','NSIC Rc480','NSIC Rc506','NSIC Rc508','NSIC Rc510','NSIC Rc512','NSIC Rc514','NSIC Rc534','PSB Rc10','PSB Rc18','PSB Rc82');


        //     $data = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cs_commitment')
        //         ->get();
          
        //     $err_excel = array();
        //     $data = json_decode(json_encode($data), true);

        //     foreach($data as $inf){
        //         foreach($seed_var as $sv){
        //             if($inf[$sv] != "0"){
                        
        //                 if($inf["coop_name"] == "Quezon Palay Seed Growers Association  Inc."){
        //                     $inf["coop_name"] = "Quezon Palay Seed Growers Association";
        //                 }elseif($inf["coop_name"] == "Zamboanga del Norte Seed Producers  Inc."){
        //                     $inf["coop_name"] = "Zamboanga del Norte Seed Producers";
        //                 }

        //                 $coop_accre = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        //                     ->where("coopName", "LIKE", "%".trim($inf['coop_name'])."%")
        //                     ->first();
                        
                       


        //                 if($coop_accre != null){
        //                     if($coop_accre->full_address == null){
        //                         $coop_accre->full_address = "";
        //                     } 


        //                     $with_region = "0";
        //                     $explode = explode(',',$inf['prv_to_supply']);
        //                     foreach($explode as $ex){
        //                         $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        //                         ->where("province", "LIKE", "%".trim($ex)."%")
        //                         ->first();

        //                         if($region == null){
        //                         }else{
        //                             $with_region = 1;
        //                             break;
        //                         }
                       
        //                     }

        //                     if($with_region == "0"){
        //                         $explode = explode('and',$inf['prv_to_supply']);
        //                         foreach($explode as $ex){
        //                             $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        //                             ->where("province", "LIKE", "%".trim($ex)."%")
        //                             ->first();
    
        //                             if($region == null){
                                       
        //                             }else{
        //                                 $with_region = 1;
        //                                 break;
        //                             }
                           
        //                         }

        //                     }
                           
        //                     if($with_region == "0"){
        //                         $explode = explode('&',$inf['prv_to_supply']);
        //                         foreach($explode as $ex){
        //                             $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        //                             ->where("province", "LIKE", "%".trim($ex)."%")
        //                             ->first();
    
        //                             if($region == null){
                                   
        //                             }else{    
        //                                  $with_region = 1;
        //                                 break;
        //                             }
                           
        //                         }

        //                     }

        //                     if($region == null){
        //                         dd($inf['prv_to_supply']);
        //                     }

        //                     $insert_me = array(
        //                         "coop_name" => $coop_accre->coopName,
        //                         "coop_accreditation" => $coop_accre->accreditation_no,
        //                         "full_address" => $coop_accre->full_address,
        //                         "region" => $region->regionName,
        //                         "province_to_supply" =>$inf["prv_to_supply"],
        //                         "seed_variety" =>  $sv,
        //                         "bags" => $inf[$sv]
        //                     );

        //                     DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cs_requirement")
        //                     ->insert($insert_me);

        //                 }else{
        //                     array_push($err_excel, $inf);
        //                 }
        //             }
        //         }
        //     }

          



        //     return Excel::create("ERR".date("Y-m-d g:i A"), function($excel) use ($err_excel) {
        //         $excel->sheet("BENEFICIARY LIST", function($sheet) use ($err_excel) {
        //             $sheet->fromArray($err_excel);
        //             $sheet->freezeFirstRow();
                    
        //         });
        //     })->download('xlsx');

        }

        elseif($data1 == "realign_station"){
            $user_list = DB::table("users")
                    ->where("stationId", "0")
                    ->where("province", "!=", "")
                    ->get();
                    //2121
                 $i = 0;
                foreach($user_list as $user_info){
                        $user_id = $user_info->userId;
                        $prv_name = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                                ->where("prv_code", $user_info->province)
                                ->first();
                            if($prv_name == null){
                               continue;
                            }

                        $station_name = DB::table("lib_station")
                            ->where("province", $prv_name->province)
                            ->value("station");
                        $station_id = DB::table("geotag_db2.tbl_station")
                            ->where("stationName", $station_name)
                            ->value("stationId");
                       
                        DB::table("users")
                            ->where("userId", $user_id)
                            ->update([
                                "stationId" => $station_id
                            ]);

                           // dd($user_id);

                            $i++;

                }

                return "DONE_-> ".$i;

        }
        elseif($data1 == "qr_test"){
            $pdf = PDFTIM::loadView('farmer.QID', ['start_count' => 1, 'end_count' => 1, 'region_code' => "03"])->setPaper('a4', 'landscape');        
            $pdf_name = "R"."03"."_"."1"."_to_"."100".".pdf";
           
            return $pdf->stream($pdf_name);
        }
        elseif($data1 == "pre_reg_db"){

            $prv_list = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", "like", "prv_%")
                ->where("TABLE_NAME", "released")
                ->groupBY("TABLE_SCHEMA")
                ->take(1)
                ->get();
           
            foreach($prv_list as $prv_info){
                $db = $prv_info->TABLE_SCHEMA;

                \Config::set('database.connections.mysql.database', $db);
                DB::purge("mysql");
                DB::connection("mysql")->getPdo();
         
                $sql = "CREATE TABLE ".$db."pre_registration (
                    `auto_id` int(11) NOT NULL,
                    `qr_code` varchar(100) NOT NULL,
                    `philrice_id` varchar(50) NOT NULL,
                    `rsbsa_control_no` varchar(100) NOT NULL,
                    `farmer_id` varchar(100) NOT NULL,
                    `first_name` varchar(100) NOT NULL,
                    `mid_name` varchar(100) NOT NULL,
                    `last_name` varchar(100) NOT NULL,
                    `ext_name` varchar(100) NOT NULL,
                    `sex` varchar(10) NOT NULL,
                    `birthdate` varchar(50) NOT NULL,
                    `contact_num` varchar(20) NOT NULL,
                    `region` varchar(50) NOT NULL,
                    `province` varchar(100) NOT NULL,
                    `municipality` varchar(100) NOT NULL,
                    `claim_location` varchar(100) NOT NULL,
                    `verification_type` varchar(5) NOT NULL,
                    `actual_area` double(12,2) NOT NULL,
                    `claim_area` double(12,2) NOT NULL,
                    `computed_yield` double(12,2) NOT NULL,
                    `total_production` int(11) NOT NULL,
                    `ave_weight_bag` double(12,2) NOT NULL,
                    `area_harvested` double(12,2) NOT NULL,
                    `sowing_date` varchar(25) NOT NULL,
                    `crop_establishment` varchar(100) NOT NULL,
                    `eco_system` varchar(100) NOT NULL,
                    `is_acknowledge` int(1) NOT NULL DEFAULT 0
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

                DB::select(DB::raw($sql));
            
                $sql = "ALTER TABLE ".$db."pre_registration
                ADD PRIMARY KEY (`auto_id`);";
                DB::select(DB::raw($sql));

                $sql = "ALTER TABLE ".$db."pre_registration
                MODIFY `auto_id` int(11) NOT NULL AUTO_INCREMENT;";
                DB::select(DB::raw($sql));


            
            
            
            }
         


        }





        else if($data1 == $GLOBALS['season_prefix']."prv_update"){
            
            if($data2 == "all"){
                $data2 = $GLOBALS['season_prefix']."prv_%";
            }

            $list = DB::table("information_schema.COLUMNS")
                ->where("TABLE_SCHEMA", "LIKE", $data2)
                ->where("TABLE_SCHEMA", "NOT LIKE", "%_bak")
                ->where("TABLE_SCHEMA", "NOT LIKE",$GLOBALS['season_prefix']."prv_0128")
                ->where("TABLE_SCHEMA", "NOT LIKE",$GLOBALS['season_prefix']."prv_0129")
                
                ->where("TABLE_NAME", "farmer_profile_processed")
                //->where("COLUMN_NAME", "!=", "is_posted")
                ->groupBy("TABLE_SCHEMA")
                ->get();

              
            $x = 0;
            $i = 0;
            $txt = "";

            \Config::set('database.connections.ls_inspection_db.host', "localhost");
            \Config::set('database.connections.ls_inspection_db.port', "3306");
        
            \Config::set('database.connections.ls_inspection_db.username', "jpalileo");
            \Config::set('database.connections.ls_inspection_db.password', "P@ssw0rd");
            DB::purge('ls_inspection_db');
            DB::connection('ls_inspection_db')->getPdo();

                foreach($list as $db){
                    \Config::set('database.connections.ls_inspection_db.database', $db->TABLE_SCHEMA);
                    DB::purge('ls_inspection_db');
                    DB::connection('ls_inspection_db')->getPdo();

                 $prv_code = str_replace('prv_',"",$db->TABLE_SCHEMA);
               
                    $code = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->where("prv_code",  $prv_code)
                        ->groupBy("province")
                        ->first();
                    $pattern = $code->regCode."-".$code->provCode."-";

                   
                   $ls_data = DB::connection('ls_inspection_db')->table("released")
                            ->distinct()
                            ->get();
                            foreach($ls_data as $ls_info){
                                $info = json_decode(json_encode($ls_info), true);
                           
                            

                               $check_exist=  DB::table($db->TABLE_SCHEMA.".farmer_profile_processed")
                                    ->where("rsbsa_control_no", $info["rsbsa_control_no"])
                                    ->where("firstName", "LIKE", $info["farmer_fname"])
                                    ->where("lastName", "LIKE", $info["farmer_lname"])
                                    ->first();

                                    if(count($check_exist)>0){
                                        continue;
                                    }else{

                                        $farmer_profile =  DB::connection('ls_inspection_db')->table("farmer_profile_processed")
                                            ->where("rsbsa_control_no", $info["rsbsa_control_no"])
                                            ->where("firstName", "LIKE", $info["farmer_fname"])
                                            ->where("lastName", "LIKE", $info["farmer_lname"])
                                            ->first();

                                        if(count($farmer_profile)>0){
                                            $other_info = DB::connection("ls_inspection_db")->table("other_info_processed")
                                            ->where("rsbsa_control_no", $info["rsbsa_control_no"])
                                            ->where("farmer_id", $info["farmer_id"])
                                            ->first();
                                            
                                            $area = $farmer_profile->actual_area;
                                            if($area != floor($area)){
                                                $dec =  $area - floor($area); 

                                                if($dec <= 0.5 ){
                                                $area = floor($area) + 0.5;
                                                }else{
                                                    $area = floor($area) + 1;
                                                }
                                            }
                                            $bags = $area * 2;

                                            $farmer_profile->is_claimed = 0;
                                            $farmer_profile->total_claimable = $bags;
                                            $farmer_profile->total_claimed = 0;
                                            $farmer_profile->is_ebinhi = 0;
                                            //YIELD RESET
                                            $farmer_profile->season = 0;
                                            $farmer_profile->yield = 0;
                                            $farmer_profile->weight_per_bag = 0;
                                            $farmer_profile->area_harvested = 0;

                                            

                                            if(count($other_info)>0){
                                                $farmer_profile->oth_link = $other_info->info_id;
                                            }else{
                                                continue;
                                            }

                                            $check2 =  DB::table($db->TABLE_SCHEMA.".other_info_processed")
                                            ->where("rsbsa_control_no", $info["rsbsa_control_no"])
                                            ->where("farmer_id", $info["farmer_id"])
                                            ->get();

                                            if(count($check2)>0){
                                                continue;
                                            }
                                        
                                            unset($farmer_profile->id);
                                            $farmer_profile = json_decode(json_encode($farmer_profile), true);
                                            DB::table($db->TABLE_SCHEMA.".farmer_profile_processed")
                                                ->insert($farmer_profile);
                                
                                            unset($other_info->info_id);
                                            $other_info = json_decode(json_encode($other_info), true);
                                            DB::table($db->TABLE_SCHEMA.".other_info_processed")
                                                ->insert($other_info);

                                                $i++;

                                        }else{
                                            continue;
                                        }
                                    }
                                    
                                

                            }
                    $txt .= $db->TABLE_SCHEMA."-".$i."  |  ";
                }
                
              return $txt;

        }



        elseif($data1 == $GLOBALS['season_prefix']."prv_udpate"){
            if($data2 == "all"){
                $data2 = $GLOBALS['season_prefix']."prv_%";
            }

            $list = DB::table("information_schema.COLUMNS")
                ->where("TABLE_SCHEMA", "LIKE", $data2)
                ->where("TABLE_SCHEMA", "NOT LIKE", "%_bak")
                ->where("TABLE_NAME", "farmer_profile_processed")
                //->where("COLUMN_NAME", "!=", "is_posted")
                ->groupBy("TABLE_SCHEMA")
                ->get();

            $x = 0;
            $i = 0;
            $txt = "";

            \Config::set('database.connections.ls_inspection_db.host', "localhost");
            \Config::set('database.connections.ls_inspection_db.port', "4409");
        
            \Config::set('database.connections.ls_inspection_db.username', "rcef_web");
            \Config::set('database.connections.ls_inspection_db.password', "SKF9wzFtKmNMfwy");
            DB::purge('ls_inspection_db');
            DB::connection('ls_inspection_db')->getPdo();

                foreach($list as $db){
                    \Config::set('database.connections.ls_inspection_db.database', $db->TABLE_SCHEMA);
                    DB::purge('ls_inspection_db');
                    DB::connection('ls_inspection_db')->getPdo();

                 $prv_code = str_replace('prv_',"",$db->TABLE_SCHEMA);
               
                    $code = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->where("prv_code",  $prv_code)
                        ->groupBy("province")
                        ->first();
                    $pattern = $code->regCode."-".$code->provCode."-";
                   // dd($pattern);
                   
                   $ls_data = DB::connection('ls_inspection_db')->table("farmer_profile")
                            //->where("is_posted", 0)
                            ->where("distributionID", "LIKE", "R0%")
                            ->where("rsbsa_control_no", "LIKE", $pattern."%")
                            //->where("rsbsa_control_no","01-29-01-011-000289")
                            ->get();
             
                            foreach($ls_data as $ls_info){
                                $info = json_decode(json_encode($ls_info), true);
                                unset($info["is_posted"]);
                                unset($info["id"]);
                                
                                if(isset($info["date_added"])){
                                    unset($info["date_added"]);
                                }

                                    $area = $info['actual_area'];
                                    if($area != floor($area)){
                                        $dec =  $area - floor($area); 

                                        if($dec <= 0.5 ){
                                        $area = floor($area) + 0.5;
                                        }else{
                                            $area = floor($area) + 1;
                                        }
                                    }
                                    $bags = $area * 2;
                                    $info["is_claimed"] = 0;
                                    $info["total_claimable"] = $bags;
                                    $info["total_claimed"] = 0;
                                    $info["is_ebinhi"] = 0;
                                    //YIELD RESET
                                    $info["season"] = 0;
                                    $info["yield"] = 0;
                                    $info["weight_per_bag"] = 0;
                                    $info["area_harvested"] = 0;

                                    $ls_oth= DB::connection('ls_inspection_db')->table("other_info")
                                    ->where("farmer_id",  $info["farmerID"])
                                    ->where("rsbsa_control_no",  $info["rsbsa_control_no"])
                                    ->first();


                                    if(count($ls_oth)>0){
                                        
                                        $info["oth_link"] =$ls_oth->info_id;
                                        
                                    


                                        $ls_oth =  json_decode(json_encode($ls_oth), true);
                                        
                                        if(isset($ls_oth["date_added"])){
                                            unset($ls_oth["date_added"]);
                                        }


                                        $check =  DB::table($db->TABLE_SCHEMA.".farmer_profile_processed")
                                            ->where("rsbsa_control_no", $info["rsbsa_control_no"])
                                            ->where("firstName", $info["firstName"])
                                            ->where("lastName", $info["lastName"])
                                            ->get();
                                        

                                        if(count($check)>0){
                                            continue;
                                        }
                                        
                                        $check2 =  DB::table($db->TABLE_SCHEMA.".other_info_processed")
                                        ->where("info_id", $ls_oth["info_id"])
                                        ->get();

                                        if(count($check2)>0){
                                            continue;
                                        }


                                        $this_season = DB::table($db->TABLE_SCHEMA.".farmer_profile_processed")
                                            ->insert($info);
                                        $this_season = DB::table($db->TABLE_SCHEMA.".other_info_processed")
                                                ->insert($ls_oth);
                                        
                                        DB::connection('ls_inspection_db')->table("farmer_profile")
                                            ->where("id", $ls_info->id)
                                            ->update([
                                                "is_posted" => 1
                                            ]);

                                            $i++;
                                    }

                            }



                    // DB::select(DB::raw("TRUNCATE TABLE ".$db->TABLE_SCHEMA.".farmer_profile_processed"));
                    // DB::select(DB::raw("TRUNCATE TABLE ".$db->TABLE_SCHEMA.".farmer_profile"));
                    // DB::select(DB::raw("TRUNCATE TABLE ".$db->TABLE_SCHEMA.".other_info_processed"));

                    // $check = DB::table("information_schema.COLUMNS")
                    // ->where("TABLE_SCHEMA", "LIKE", $db->TABLE_SCHEMA)
                    // ->where("TABLE_NAME", "farmer_profile_processed")
                    // ->where("COLUMN_NAME", "oth_link")
                    // ->first();
                
                    //         if(count($check)>0){
                    //             $x++;
                    //             continue;
                    //         }
                    
                    // DB::select(DB::raw("ALTER TABLE ".$db->TABLE_SCHEMA.".farmer_profile_processed ADD `oth_link` INT NOT NULL;"));
                    // $i++;
                    $txt .= $db->TABLE_SCHEMA."-".$i."  |  ";
                }
                
              return $txt;

        }
        elseif($data1 == "add_view"){
            $prv_list = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", "like", "prv_%")
                ->where("TABLE_NAME", "released")
                ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."prv_0133")
                ->groupBY("TABLE_SCHEMA")
                ->take(1)
                ->get();
           
            foreach($prv_list as $prv_info){
                $db = $prv_info->TABLE_SCHEMA;

                \Config::set('database.connections.mysql.database', $db);

            DB::purge("mysql");

            DB::connection("mysql")->getPdo();
         



                DB::connection("mysql")->raw("CREATE ALGORITHM = UNDEFINED VIEW municipal_report AS SELECT municipality, SUM(actual_area) as total_area, SUM(claimed_area) as total_claimed,COUNT(release_id) as total_beneficiary, SUM(IF(UPPER(SUBSTRING(sex, 1,1)) ='M',1,0)) as total_male, SUM(IF(UPPER(SUBSTRING(sex, 1,1)) ='F',1,0)) as total_female, SUM(bags) as total_bags FROM released GROUP BY municipality");
     

          
            }

            \Config::set('database.connections.mysql.database', "sdms_db_dev");

            DB::purge("mysql");

            DB::connection("mysql")->getPdo();
            return "Connection Established!";









        }elseif($data1 == "sync_farmer_and_released"){

            if($data3 == "processed_base"){
                $i = 0;
                $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("province", $data2)
                    ->first();
                $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                
                $farmer_profile = DB::table($prv_db.".farmer_profile_processed")
                    ->where("is_claimed", 1)
                    ->where("total_claimed", ">", 0)
                    ->get();
    
                    foreach ($farmer_profile as $row){
                        $release = DB::table($prv_db.".released")
    
                            ->where("rsbsa_control_no", $row->rsbsa_control_no)
                            ->where("farmer_id", $row->farmerID)
                            ->where("farmer_fname", $row->firstName)
                            ->first();
    
                            if(count($release)<=0){
                                DB::table($prv_db.".farmer_profile_processed")
                                    ->where("id", $row->id)
                                    ->update([
                                        "is_claimed" => 0,
                                        "total_claimed" => 0
                                    ]);
                            $i++;
                                }
                    }
            }else if ($data3 == "release_base"){
                $i = 0;
                $arr = array();
                $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where("province", $data2)
                    ->first();
                $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                
                $released = DB::table($prv_db.".released")
                    ->select(DB::raw("SUM(bags) as totalBag"), "rsbsa_control_no", "farmer_id", "farmer_fname", "farmer_lname")
                    ->groupBy("rsbsa_control_no")
                    ->groupBy("farmer_id")
                    ->groupBy("farmer_fname")
                    ->groupBy("farmer_lname")
                    ->groupBy("mother_name")
                    //->where("municipality", "MORONG")
                    ->get();

                   
                    foreach ($released as $data_release){
                       $farmer =  DB::table($prv_db.".farmer_profile_processed")
                            ->where("rsbsa_control_no", $data_release->rsbsa_control_no)
                            ->where("farmerID", $data_release->farmer_id)
                            ->where("firstName", $data_release->farmer_fname)
                            ->where("lastName", $data_release->farmer_lname)
                            ->where("is_claimed", 0)
                            ->where("total_claimed", 0)
                            ->update([
                                "is_claimed" => 1,
                                "total_claimed" => $data_release->totalBag
                            ]);


                            
                            $i++;

                    }



            }



                return $i;

        }
        elseif($data1 == "fix_qr"){
            $data3 = "";
				$check_db = DB::table("information_schema.COLUMNS")
                    ->where("TABLE_SCHEMA", $data2)
                    ->where("TABLE_NAME", "released")
					->first();
				if(count($check_db)<=0){
					return $data2." DO NOT EXIST";
					dd("EXIT");
				}
					
                $check = DB::table("information_schema.COLUMNS")
                    ->where("TABLE_SCHEMA", $data2)
                    ->where("TABLE_NAME", "released")
                    ->where("COLUMN_NAME", "distributionID")
                    ->first();
                if(count($check)<=0){
                    DB::select(DB::raw("ALTER TABLE ".$data2.".released ADD `distributionID` VARCHAR(255) NOT NULL AFTER `rsbsa_control_no`;"));
                }
				
            $farmer_list = DB::table($data2.".released")
                    ->where("distributionID", "")
                    ->where("rsbsa_control_no", "!=", "")
                    ->where("farmer_fname", "!=", "")
                    ->where("farmer_lname", "!=", "")
                    ->where("farmer_id", "!=", "")
                    ->get();
                $i = 0;
                foreach ($farmer_list as $key => $value) {
                    $profile = DB::table($data2.".farmer_profile")
                            ->select("distributionID")
                            ->where("rsbsa_control_no", $value->rsbsa_control_no)
                            ->where("farmerID", $value->farmer_id)
                            ->where("firstName", $value->farmer_fname)
                            ->where("lastName", $value->farmer_lname)
                            ->first();
                    if(count($profile)<=0){
                        dd($value);

                    }

                            DB::table($data2.".released")
                                ->where("release_id", $value->release_id)
                                ->update([
                                    "distributionID" => $profile->distributionID
                                ]);
                    


                    $i++;          
                }
                return $i." processed";
        }elseif($data1=="add_distri"){
            if($data2 == "all")$data2=$GLOBALS['season_prefix']."prv_%";
            $data3 = "";
            $check = DB::table("information_schema.COLUMNS")
                    ->where("TABLE_SCHEMA", "LIKE", $data2)
                    ->where("TABLE_NAME", "released")
                    //->where("COLUMN_NAME", "distributionID")
                    ->groupBy("TABLE_SCHEMA")
                    ->get();
            foreach ($check as $key => $value) {
                $check2 = DB::table("information_schema.COLUMNS")
                    ->where("TABLE_SCHEMA", $value->TABLE_SCHEMA)
                    ->where("TABLE_NAME", "released")
                    ->where("COLUMN_NAME", "distributionID")
                    ->first();
                
                if(count($check2)<=0){
                    DB::select(DB::raw("ALTER TABLE ".$value->TABLE_SCHEMA.".released ADD `distributionID` VARCHAR(255) NOT NULL AFTER `rsbsa_control_no`;"));
                }
              //  dd($value->TABLE_SCHEMA);
            }
        }elseif($data1=="sync_paymaya"){
            if($data3 == "" || $data2 == ""){
                return "NO FILTER DATA";
            }
            $sed_verified = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                ->where("province_name", 'LIKE',$data2)
                ->where("municipality_name", 'LIKE',$data3)
                ->where("status", 1)
                ->get();
           //dd($sed_verified);
           $i = 0;
            foreach($sed_verified as $sed_value){
              //  dd($sed_value);
                $flist = DB::table($GLOBALS['season_prefix']."rcep_paymaya.kim_final_list")
                    ->where("rsbsa", 'like', $sed_value->rsbsa_control_number)
                    ->where("fname", 'like', $sed_value->fname)
                    //->where("mname", 'like', $sed_value->midname)
                    ->where("lname", 'like', $sed_value->lname)
                    //->where("ext", $sed_value->extename)
                    ->where("province", 'LIKE', $data2)
                    ->where("municipality", 'LIKE', $data3)
                    ->where("is_sync", "0")
                    ->first();
                   // dd($flist);
                if(count($flist)>0){
                      
                    
                        DB::table($GLOBALS['season_prefix']."rcep_paymaya.kim_final_list")
                        ->where("id", $flist->id)
                        ->update([
                            "is_sync" => 1
                        ]); 
                    
                    $i++;
                }else{
                    
                    $sed_verified = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                    ->where("sed_id", $sed_value->sed_id)
                    ->update([
                            "status" => 9
                        ]
                        ); 



                }
            
            }

            return $i;
        }elseif($data1=="sync_final_paymaya"){
            if($data3 == "" || $data2 == ""){
                return "NO FILTER DATA";
            }

            $tbl_beneficiaries = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
            ->where("province", 'LIKE',$data2)
            ->where("municipality", 'LIKE',$data3)
            //->where("isSent", "!=",1)
            ->get();
       // dd($tbl_beneficiaries);
       $i = 0;
       $x = 0;
        foreach($tbl_beneficiaries as $sed_value){
            $flist = DB::table($GLOBALS['season_prefix']."rcep_paymaya.kim_final_list")
                ->where("rsbsa", $sed_value->rsbsa_control_no)
               // ->where("fname", "LIKE", $sed_value->firstname)
                //->where("mname", $sed_value->middname)
                //->where("lname", $sed_value->lastname)
                //->where("ext", $sed_value->extname)
                ->where("is_sync", "1")
                ->first();
            if(count($flist)>0){
                //UPDATE
                    DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    ->where("beneficiary_id", $sed_value->beneficiary_id)
                    ->update([
                        "area" => $flist->area,
                        "bags" => $flist->bags
                    ]);
                    DB::table($GLOBALS['season_prefix']."rcep_paymaya.kim_final_list")
                    ->where("id", $flist->id)
                    ->update([
                        "is_sync" => 2,
                    ]);
                $i++;
            }else{
                $x++;
            }
        }
        return $i.'G | '. $x.'B';
        }elseif($data1 == "fix_paymaya_bag"){
            $list = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                ->where("province", $data2)
                ->where("municipality", $data3)
                ->get();
                //dd($list);
            $i =0;
                foreach($list as $key => $value){
                    $area = $value->area;
                    if($area != floor($area)){
                        $dec =  $area - floor($area); 
        
                        if($dec <= 0.5 ){
                        $area = floor($area) + 0.5;
                        }else{
                            $area = floor($area) + 1;
                        }
                    }
                    $bags = $area * 2;
                
                    DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                        ->where("beneficiary_id", $value->beneficiary_id)
                        ->update([
                            "bags" => $bags
                        ]);
                        $i++;
                }

                return $i . "fixed";


        }elseif($data1 =="fix_variety_paymaya"){
            if($data3 == "" || $data2 == ""){
                return "NO FILTER DATA";
            }

            $tbl_beneficiaries = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
            ->where("province", 'LIKE',$data2)
            ->where("municipality", 'LIKE',$data3)
            ->where("schedule_start", "2021-10-27")
            ->where("isSent" ,1)
            
            ->get();
        //dd($tbl_beneficiaries);
       $i = 0;
       $x = 0;
        foreach($tbl_beneficiaries as $sed_value){
            $flist = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_paymaya_lib")
                ->where("paymaya_code", $sed_value->paymaya_code)
                ->update([
                    "variety_1"=> "NSIC 2015 Rc 402"
                ]);
                $i++;
            }
            return $i.'G | '. $x.'B';
        }
        elseif($data1 == "sync_resched"){
            
            if($data3 == "" || $data2 == ""){
                return "NO FILTER DATA";
            }

            $tbl_beneficiaries = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
            ->where("province", 'LIKE',$data2)
            ->where("municipality", 'LIKE',$data3)
            ->where("isSent", "!=",1)
            ->get();
       // dd($tbl_beneficiaries);
       $i = 0;
       $x = 0;
        foreach($tbl_beneficiaries as $sed_value){
            $flist = DB::table($GLOBALS['season_prefix']."rcep_paymaya.kim_final_list")
                ->where("rsbsa", $sed_value->rsbsa_control_no)
               // ->where("fname", "LIKE", $sed_value->firstname)
                //->where("mname", $sed_value->middname)
                //->where("lname", $sed_value->lastname)
                //->where("ext", $sed_value->extname)
                ->where("is_sync", "2")
                ->first();
            if(count($flist)>0){
                //UPDATE
                    DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    ->where("beneficiary_id", $sed_value->beneficiary_id)
                    ->update([
                        "area" => $flist->area,
                        "bags" => $flist->bags
                    ]);
                   
                $i++;
            }else{
                $x++;
            }
        }
        return $i.'G | '. $x.'B';
        }elseif($data1 == "correct_id_resched"){
            $i = 0;
            $f= 0;
            if($data3 == "" || $data2 == ""){
                return "NO FILTER DATA";
            }
            if($data3 == "all"){
                $data3 = "%";
            }

            $tbl_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                ->whereRaw("paymaya_code not in (Select paymaya_code from rcep_paymaya.tbl_beneficiaries)")
                ->where("province", $data2)
                ->where("municipality", "LIKE", $data3)
                ->groupBy("paymaya_code")
                ->get();
              
                foreach ($tbl_claim as $key => $value) {
                    $name_arr = explode(" ", $value->fullName);
                    $fname = $name_arr[0];

                    $check = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                        ->where("firstname",  "LIKE", $fname."%")
                        ->where("rsbsa_control_no", $value->rsbsa_control_no)
                        ->first();

                    if(count($check)>0){
                   
                        DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                            ->where("paymaya_code", $value->paymaya_code)
                            ->update([
                                "paymaya_code" => $check->paymaya_code,
                                "beneficiary_id" => $check->beneficiary_id
                            ]);
                        $i++;
                    }else{
                        $f++;
                    }


                }


                return "C".$i." F".$f;

        }elseif($data1=="unclaimed_excel"){
             if($data3 == "" || $data2 == ""){
                return "NO FILTER DATA";
            }
            if($data3 == "all"){
                $data3 = "%";
            }

            $unclaimed = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                ->where("province", $data2)
                ->where("municipality", "LIKE",$data3)
              
                ->whereRaw("paymaya_code not in (Select paymaya_code from rcep_paymaya.tbl_claim)")
                ->get();


            $excel_data = json_decode(json_encode($unclaimed), true); //convert collection to associative array to be converted to excel
            return Excel::create("UNCLAIMED CODES".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                });
            })->download('xlsx');


        }
       elseif($data1 == "process_gad"){
            $cmd ="start chrome http://localhost/rcef_ws2022/report/gad/farmer/process";
             exec($cmd);
             return "GAD PROCESSING";
        }elseif($data1 == "reset_farmer"){
            if($data3 == "all"){
                $data3 = "%";
            }
            
            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("province", $data2)
                ->where("municipality", "LIKE",$data3)
                ->first();

            if(count($prv)>0){
                $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0, 4);
                $res = $db. "\n";
                $i = 0;

                $date = date("m-d-Y");
                $time = date("h_i A");
              
                    $folder = "D:\/rcep_sms_db_backup_ws2021\DS2022\RESET/".$date."_".$time;        
                    $mkdir = 'mkdir "'.$folder.'"';
                    exec($mkdir);
                    $bckup = 'mysqldump -h localhost -P 3306 --single-transaction -u jpalileo -pP@ssw0rd '.$db.' > "'.$folder.'\/'.$db.'.sql';
                    exec($bckup);
             


                    $checkList = DB::table($db.".farmer_profile_processed")
                        ->where("is_claimed", 1)
                        ->where("total_claimed", ">", 0)
                        ->get();
     
                        foreach ($checkList as $key => $value) {
                            $check_release = DB::table($db.".released")
                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                ->where("farmer_fname", $value->firstName)
                                ->where("farmer_id", $value->farmerID)
                                ->first();
                            if(count($check_release) <= 0){
                                DB::table($db.".farmer_profile_processed")
                                ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                ->where("farmer_fname", $value->firstName)
                                ->where("farmer_id", $value->farmerID)
                                    ->update([
                                        "is_claimed" => 0,
                                        "total_claimed" => 0
                                    ]);
                                    $i++;
                            }


                            }   
                    $res .= $i;
                    return $res;            
                }else{
                    return "UNFOUND";
                }

        }





    } 

     public function set_rpt_db($conName,$database_name){
        try {
            \Config::set('database.connections.'.$conName.'.database', $database_name);
                /*
            \Config::set('database.connections.'.$conName.'.host', '192.168.10.23');
                \Config::set('database.connections.'.$conName.'.port', '3306');
                \Config::set('database.connections.'.$conName.'.username', 'rcef_web');
                \Config::set('database.connections.'.$conName.'.password', 'SKF9wzFtKmNMfwy');
                */



            DB::purge($conName);

            DB::connection($conName)->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }

    public function generate_temp_codes(){
        //UPDATE 2
        //https://dbmp2.philrice.gov.ph/rcef_ws2022/utility/coops/temp_accreditation


        $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('isActive', 1)->get();
        
        $icrement_value = 1;
        foreach($coop_list as $row){
            $pattern = "MOA-DS24-08-";
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('coopId', $row->coopId)->update([
                'current_moa' => $pattern.sprintf("%02d", $icrement_value)
            ]);

            //update tbl_commitment
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')->where('coopID', $row->coopId)->update([
                'moa_number' => $pattern.sprintf("%02d", $icrement_value)
            ]);

            //update tbl_total_commitment
            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')->where('coopID', $row->coopId)->update([
                'moa_number' => $pattern.sprintf("%02d", $icrement_value)
            ]);

            //update other tbl
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                ->where("coopAccreditation", $row->accreditation_no)
                ->update([
                    "moa_number" => $pattern.sprintf("%02d", $icrement_value)
                ]);
        
            //update other tbl
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
                ->where("accreditation_no", $row->accreditation_no)
                ->update([
                    "moa_number" => $pattern.sprintf("%02d", $icrement_value)
                ]);

            //update other tbl
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.rla_requests")
                ->where("coop_accreditation", $row->accreditation_no)
                ->update([
                    "coop_moa" => $pattern.sprintf("%02d", $icrement_value)
                ]);
            
                //update other tbl
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
            ->where("coopAccreditation", $row->accreditation_no)
            ->update([
                "moaNumber" => $pattern.sprintf("%02d", $icrement_value)
            ]);
        


                

                $icrement_value++;
                echo "Updated Details for: $row->coopName<br>";
            
        
        }
    
    
        // $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
        //     ->groupBy("batchTicketNumber")
        //     ->get();


        //     foreach($list as $data){
        //         DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
        //             ->where("batchTicketNumber", $data->batchTicketNumber)
        //             ->update([
        //                 "moa_number" => $data->moa_number
        //             ]);

        //         DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_inspection")
        //         ->where("batchTicketNumber", $data->batchTicketNumber)
        //         ->update([
        //             "moa_number" => $data->moa_number
        //         ]);

        //         DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_sampling")
        //         ->where("batchTicketNumber", $data->batchTicketNumber)
        //         ->update([
        //             "moa_number" => $data->moa_number
        //         ]);

        //     }

        //tbl_actual_delivery
        //tbl_inspection
        //tbl_sampling





    
    
    
    
    }



    // 
    public function generate_key(){

        $x = 4;

      $data = DB::table('rcef_validation.tbl_user_code')
        ->where('code', null)
        ->get();

        foreach ($data as $row) {

            $randomNum = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $x);

            $randomNum_check = DB::table('rcef_validation.tbl_user_code')
            ->where("code",$randomNum)
            ->count();

            if($randomNum_check <= 0 ){

              $update = DB::table('rcef_validation.tbl_user_code')
                ->where("id",$row->id)
                ->update(['code' => $randomNum]);

            }else{
                continue;
            }

            

            
        }


    }


    public function delete_far_index()
    {
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->groupBy('province')->get();
        // dd($provinces);
        return view('utility.delete_far', compact('provinces'));
    }

    public function delete_far_muni(Request $request)
    {
        $getMunicipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('municipality')
        ->where('province',$request->prov)
        ->groupBy('municipality')
        ->get();
        return $getMunicipalities;
    }

    public function reset_far_status(Request $request)
    {
        $resetStatus = DB::table('temp_db._running_flsar')
        ->where('prv',$request->prov)
        ->where('mun',$request->muni)
        ->update(['status' => 'finished']);

        return $resetStatus;
    }
    // 
    

}

// rcep_delivery_inspection TRUNCATE
// TRUNCATE `iar_print_logs`; TRUNCATE `iar_print_logs_bak_052621`; TRUNCATE `iar_print_logs_info`; TRUNCATE `iar_upload_logs`; TRUNCATE `lib_dropoff_point`; TRUNCATE `lib_target_datasets`; TRUNCATE `logs_dropoffmaker`; TRUNCATE `rla_requests`; TRUNCATE `tbl_actual_delivery`; TRUNCATE `tbl_actual_delivery_bak2`; TRUNCATE `tbl_actual_delivery_breakdown`; TRUNCATE `tbl_breakdown_buffer`; TRUNCATE `tbl_delivery`; TRUNCATE `tbl_delivery_payments_status`; TRUNCATE `tbl_delivery_status`; TRUNCATE `tbl_delivery_transaction`; TRUNCATE `tbl_inspection`; TRUNCATE `tbl_inspection_images`; TRUNCATE `tbl_payments_attachements`; TRUNCATE `tbl_rla_details`; TRUNCATE `tbl_rla_details2`; TRUNCATE `tbl_rla_details_bak_043021_932pm`; TRUNCATE `tbl_rla_details_bak_05052021`; TRUNCATE `tbl_sampling`; TRUNCATE `tbl_schedule`; TRUNCATE `tbl_seed_grower_bak_043021_932pm`; TRUNCATE `tbl_stocks_download_transaction`; TRUNCATE `tbl_threshold`;

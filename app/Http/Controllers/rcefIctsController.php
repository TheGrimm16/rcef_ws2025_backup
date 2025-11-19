<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use PDFTIM;
use DOMPDF;
use Auth;
use Schema;
use Datatables;
use Excel;
use PclZip;
use ZipArchive;
class rcefIctsController extends Controller
{

    public function to_prv_muni(Request $request){
        $prv_code = $request->prv_code;
// dd($prv_code);
        $municipality = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where("prv_code", $prv_code)
            ->get();    

        return json_encode($municipality);
    }

    public function change_to_prv_muni(Request $request){
    
        $prv_table = $GLOBALS['season_prefix']."prv_".substr($request->rcef_id,0,4); 
         $to_prv = substr($request->municipality,0,4);
         $new_prv_table = $GLOBALS['season_prefix']."prv_".$to_prv; 
        if(substr($request->rcef_id,0,4) != $to_prv){
            $farmer_info = DB::table($prv_table.".farmer_information_final")
                    ->where("rcef_id", $request->rcef_id)
                    ->first();
                    if($farmer_info != null){
                        $ins_data = json_decode(json_encode($farmer_info),true);
                            $cur_rcef_id = $ins_data["rcef_id"];
                            $cur_rsbsa = $ins_data["rsbsa_control_no"];
                            $ins_data["to_prv_code"] = $request->municipality;


                            $chk_new = DB::table($new_prv_table.".farmer_information_final")
                                ->where("rcef_id", "T".$ins_data["rcef_id"])
                                ->first();

                            if($chk_new != null){   
                               DB::table($new_prv_table.".farmer_information_final")
                                ->where("id", $chk_new->id)
                                ->update([
                                    "rcef_id" => $ins_data["rcef_id"],
                                    "rsbsa_control_no" => $ins_data["rsbsa_control_no"],
                                    "to_prv_code" => $request->municipality
                                ]);
                            }else{
                                unset($ins_data["id"]);
                                $ins = DB::table($new_prv_table.".farmer_information_final")
                                ->insert($ins_data);

                            }
                            
                        
                                DB::table($prv_table.".farmer_information_final")
                                    ->where("id", $farmer_info->id)
                                    ->update([
                                        "rcef_id" => "T".$cur_rcef_id,
                                        "rsbsa_control_no" => "T".$cur_rsbsa,
                                        "to_prv_code" => "T".$request->municipality
                                    ]);


                    }else{
                        return json_encode("FAILED");
                    }




        }else{
            $data = DB::table($prv_table.".farmer_information_final")
            ->where("rcef_id", $request->rcef_id)
            ->update([
                "to_prv_code" => $request->municipality
            ]);
    
        }




        
        return json_encode("Updated");



    }


    public function farmerFinder(){
        

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
                ->select('lib_prv.*')
                // ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
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
    // dd($provinces);
        return view("farmer_profile.farmerFinder")
            ->with("provinces", $provinces)
            ;
    }

    public function farmerFinder2(){
        

        if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username== "e.lopez" || Auth::user()->username == "dra.jardinez" || Auth::user()->username == "rs.jandoc"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
            ->select('rcef_nrp_provinces.*')
            // ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
            ->groupBy("rcef_nrp_provinces.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
            

        }elseif(Auth::user()->roles->first()->name == "system-encoder"){
            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
                ->select('rcef_nrp_provinces.*')
                // ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
                ->groupBy("rcef_nrp_provinces.province")
                ->where("prv_code", Auth::user()->province)
                ->orderBy("region_sort", 'ASC')
                ->get();
            }
        else{

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

                // foreach($provinces as $key=> $pr){
                //     $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                //     ->where("province", $pr->province)
                //     ->value("prv_code");

                //     $schema_check = DB::table("information_schema.TABLES")
                //     ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                //     ->where("TABLE_NAME", 'farmer_information_final')
                //     ->first();
                //     if($schema_check == null){
                //         unset($provinces[$key]);
                //     }

                // }


               $provinces =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->whereIn("province", $prv_data)
                    ->groupBy("province")
                    ->get();

            }
        }
    
        return view("farmer_profile.farmerFinder2")
            ->with("provinces", $provinces)
            ;
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

    public function finderGenTable(Request $request){

   
        $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where("province", $request->province)
            ->value("prv_code");
          
        $rsbsa = $request->rsbsa;
        $firstname = $request->firstname;
        $lastname = $request->lastname;
        
        if($rsbsa == ""){$rsbsa = "%";}
        if($firstname == ""){$firstname = "%";}
        if($lastname == ""){$lastname = "%";}

        $lib_prv =  json_decode(json_encode(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->get()),true);

        $farmer_list = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')  
            ->where("firstName", "LIKE", $firstname."%")
            ->where("lastName", "LIKE", $lastname."%")
            ->where("rsbsa_control_no", "LIKE", $rsbsa."%")
            
            ->where("final_area", ">", "0")
            ->get();

        $farmer_list = collect($farmer_list);

        return Datatables::of($farmer_list)

        ->addColumn('rsbsa', function($row){
            return $row->rsbsa_control_no;
        })
        ->addColumn('name', function($row){
            return $row->lastName." ".$row->extName.", ".$row->firstName." ".$row->midName;
        })
        // ->addColumn('address', function($row){
        //     return $row->province.", ".$row->municipality." ".$row->brgy_name;
        // })

        ->addColumn('claiming_prv', function($row) use($lib_prv) {
          $claiming =  $this->search_to_array($lib_prv, "prv", str_replace("-","",$row->claiming_prv));

           return $claiming[0]["province"].", ".$claiming[0]["municipality"];

        })

        

        ->addColumn('sex', function($row){
            return strtoupper(substr($row->sex,0,1));
        })
        ->addColumn('birthdate', function($row){
            return date("Y-m-d", strtotime($row->birthdate));
        })
        ->addColumn('contact_number', function($row){
            return $row->tel_no;
        })
        ->addColumn('action', function($row) use ($prv){
           

            $btn = "<a class ='btn btn-info btn-sm'  data-toggle='modal' data-rcef_id='".$row->rcef_id."' data-db_ref='".$row->db_ref."' data-claiming-prv='".$row->claiming_prv."' data-prv='".$prv."' data-target='#modal_farmer_info' ><i class='fa fa-eye' aria-hidden='true' data-toggle='tooltip' title='View'></i> View Details</a>";
        
            if(Auth::user()->roles->first()->name == "rcef-programmer"||Auth::user()->roles->first()->name == "administrator"){
                $prv_table = $GLOBALS['season_prefix']."prv_".$prv;
                $btn .= "<a class ='btn btn-warning btn-sm' onclick='reprint_id(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.")' ><i class='fa fa-credit-card-alt' aria-hidden='true' data-toggle='tooltip' title='RCEF ID'></i></a>";

                    if($row->is_ebinhi == 1){

                        $btn .= "<a class ='btn btn-success btn-sm' onclick='transferToConventional(".$row->db_ref."".',"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.",".'"'.$row->firstName.'"'.")' ><i class='fa fa-exchange' aria-hidden='true' data-toggle='tooltip' title='Transfer to conventional'></i></a>";
             
                    }else{
                        $btn .= "<a class ='btn btn-dark btn-sm' onclick=''  disabled><i class='fa fa-exchange' aria-hidden='true' data-toggle='tooltip' title='Transfer to conventional'></i></a>";
                    }
 
                  /*   $btn .= "<a class ='btn btn-danger btn-sm' data-rcef_id='".$row->rcef_id."' data-prv='".$prv."' onclick='deleteBtn(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.")' ><i class='fa fa-folder-open-o' aria-hidden='true'></i>Delete</a>"; */
            
          
                            // $btn .= "<a class ='btn btn-info btn-sm'  data-toggle='modal' data-rcef_id='".$row->rcef_id."' data-prv='".$prv."' data-target='#modal_jump_muni' ><i class='fa fa-map-pin' aria-hidden='true'></i> Tag </a>";

                if($row->total_claimed > 0){
                    if($row->is_replacement == 0){
                        if($row->replacement_bags_claimed <= 0 ){
                            $btn .= "<a class ='btn btn-success btn-sm' onclick='tag_to_replace(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.",".'"'.$row->firstName.'"'.")' ><i class='fa fa-refresh' aria-hidden='true' data-toggle='tooltip' title='Tag to replacement seeds'></i></a>";

                        }
                    }else{
                        if($row->replacement_bags_claimed <= 0){
                      $btn .= "<a class ='btn btn-danger btn-sm' onclick='untag_to_replace(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.",".'"'.$row->firstName.'"'.")' ><i class='fa fa-refresh' aria-hidden='true' data-toggle='tooltip' title='Untag to replacement seeds'>Untag to Replace</i></a>";
                          
                        }  
                      }

                }


                }

                if(Auth::user()->username== "cdorado.cav" || Auth::user()->username== "aquino.rr" || Auth::user()->username== "ed.quezon"){
                $prv_table = $GLOBALS['season_prefix']."prv_".$prv;

                    if($row->total_claimed > 0){
                        if($row->is_replacement == 0){
                            if($row->replacement_bags_claimed <= 0 ){
                                $btn .= "<a class ='btn btn-success btn-sm' onclick='tag_to_replace(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.",".'"'.$row->firstName.'"'.")' ><i class='fa fa-exchange' aria-hidden='true'>Tag to Replace</i></a>";
    
                            }
                        }else{
                          if($row->replacement_bags_claimed <= 0){
                        $btn .= "<a class ='btn btn-danger btn-sm' onclick='untag_to_replace(".'"'.$row->rcef_id.'"'.",".'"'.$prv_table.'"'.",".'"'.$row->firstName.'"'.")' ><i class='fa fa-exchange' aria-hidden='true'>Untag to Replace</i></a>";
                            
                          }  
                        }
                    }


                   
                }


            return $btn;
        })
        ->make(true);
    }


    
    public function farmerReclose(Request $request){
        $prv_db = $request->prv;
        $rcef_id = $request->rcef_id;
        $firstName = $request->first_name;

        $farmer_info = DB::table($prv_db.".farmer_information_final")
            ->where("rcef_id", $rcef_id)
            ->where("firstName", $firstName)
            ->first();
            if($farmer_info !=null){

                DB::table($prv_db.".farmer_information_final")
                ->where("id", $farmer_info->id)
                ->update([
                    "is_replacement" => "0",
                    "replacement_area" =>"0",
                    "replacement_bags" => "0"
                ])       ;         
               
                return json_encode("Farmer Untagged");


                


            }else{
                return json_encode("Please Reload Table, Cannot find farmer");
            }
    }


    public function farmerReopen(Request $request){
        $prv_db = $request->prv;
        $rcef_id = $request->rcef_id;
        $firstName = $request->first_name;

        $farmer_info = DB::table($prv_db.".farmer_information_final")
            ->where("rcef_id", $rcef_id)
            ->where("firstName", $firstName)
            ->first();
            if($farmer_info !=null){
              
                $claimed_area = DB::table($prv_db.".new_released")
                    ->where("rcef_id", $rcef_id)
                    ->sum("claimed_area");

                $claimed_bags = DB::table($prv_db.".new_released")
                    ->where("rcef_id", $rcef_id)
                    ->sum("bags_claimed");
                   
                    


                DB::table($prv_db.".farmer_information_final")
                ->where("id", $farmer_info->id)
                ->update([
                    "is_replacement" => "1",
                    "replacement_area" =>$claimed_area,
                    "replacement_bags" => $claimed_bags
                ])       ;         
               
                return json_encode("Tagged to reopen");


                


            }else{
                return json_encode("Please Reload Table, Cannot find farmer");
            }
    }


    public function changeDistType(Request $request){
        $db_ref = $request->db_ref;
        $prv_db = $request->prv;
        $rcef_id = $request->rcef_id;
        $firstName = $request->first_name;
        $farmer_info = DB::table($prv_db.".farmer_information_final")
            ->where("rcef_id", $rcef_id)
            ->where("firstName", $firstName)
            ->where("db_ref", $db_ref)
            ->first();
            if($farmer_info !=null){
              
                $check_total_claimed = count(DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                    ->where("paymaya_code", $farmer_info->rcef_id)
                    ->get());

                $final_bags = $check_total_claimed + $farmer_info->total_claimed;

                DB::table($prv_db.".farmer_information_final")
                    ->where("id", $farmer_info->id)
                    ->update([
                        "is_ebinhi" => 0,
                        "total_claimed" => $final_bags,
                        "is_claimed" => $final_bags >= $farmer_info->final_claimable? 1 : 0
                    ]);
                    return json_encode("Farmer Transferred!");
                
                // if($farmer_info->final_claimable <= $check_total_claimed){
                //     return json_encode("Cannot Transfer Total claimable bags is already exhausted");
                // }else{
                    // DB::table($prv_db.".farmer_information_final")
                    // ->where("id", $farmer_info->id)
                    // ->update([
                    //     "is_ebinhi" => 0,
                    //     "total_claimed" => $check_total_claimed
                    // ]);
                    // return json_encode("Farmer Transferred!");
                // }


                


            }else{
                return json_encode("Please Reload Table, Cannot find farmer");
            }


    }

    public function deleteData(Request $request){
        $prv_db = $request->prv;
        $rcef_id = $request->rcef_id;
        $farmer_info = DB::table($prv_db.".farmer_information_final")
            ->where("rcef_id", $rcef_id)
            ->first();
            $firstname =  $farmer_info->firstName;
            $lastname =  $farmer_info->lastName;

           return $farmer_list_data = DB::table($prv_db.'.farmer_information_final')  
            ->where("firstName", "LIKE", $firstname."%")
            ->where("lastName", "LIKE", $lastname."%")            
            ->count();

         
            if($farmer_info !=null){
                $farmer_info_released = DB::table($prv_db.".new_released")
                ->where("rcef_id", $rcef_id)
                ->first();
                if($farmer_info_released !=null){
                    return  "released_data";
                }else{
                    return "delete farmer info";
                }

            }else{
                return json_encode("Please Reload Table, Cannot find farmer");
            }


    }
    



    public function finderChangeArea(Request $request){
        $prv_table = $GLOBALS['season_prefix']."prv_".$request->prv;
        
        $data = DB::table($prv_table.".farmer_information_final")
            ->where("id", $request->id)
            ->where("rcef_id", $request->rcef_id)
            ->first();

        if($data !=null){

            $release = DB::table($prv_table.".new_released")
                ->where("rcef_id", $request->rcef_id)
                ->first();

            if($release != null){
                return json_encode("Farmer Already has claim"); 
            }else{
                DB::table($prv_table.".farmer_information_final")
                ->where("id", $request->id)
                ->where("rcef_id", $request->rcef_id)
                ->update([
                    "final_area" => $request->area,
                    "final_claimable" => ceil($request->area *2)
                ]);

                return json_encode("Area Updated");
            }


           
        }else{
            return json_encode("Farmer Not found");
        }







    }

    public function finderInfo(Request $request){
        
        $db_ref = $request->db_ref;
        $info = DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information_final") 
        ->where("db_ref", $db_ref)
        ->where("claiming_prv", $request->claiming_prv)
            ->first();

        return json_encode($info);

    }

    public function forceChangeArea(Request $request){
        DB::table($GLOBALS['season_prefix']."prv_".$request->prv.".farmer_information_final") 
            ->where("id", $request->id)
            ->update([
                "adjusted_area" => $request->adj,
                "final_area" => $request->adj,
                "final_claimable" => ceil($request->adj*2)
            ]);

        DB::table($GLOBALS['season_prefix']."log_push.trail")
            ->insert([
                "from_season" => "area_update",
                "to_season" => $GLOBALS['season_prefix'],
                "prv_affected"=> $request->prv,
                "old_id" => $request->id,
                "new_id" => $request->adj,
                "user_name" => Auth::user()->username
            ]);
        
        return json_encode("Success");

    }



    public function areaTroubleshootChange(Request $request){
           
        
         
        ini_set('memory_limit', '-1');
            DB::beginTransaction();
  
            try {
                DB::table($GLOBALS['season_prefix']."prv_".$request->prv_code.".farmer_information_final") 
                ->where("id", $request->id)
                ->update([
                    "final_area" => $request->value,
                    "final_claimable" => ceil($request->value * 2)
                ]);
    
                    DB::commit();
              
                    return json_encode("success");
            } catch (\Throwable $th) {
                DB::rollback();
                dd($th);
            }
            



    }


    
    public function areaTroubleshootTable(Request $request){

        if($request->search == "all"){
            $search = "%";
        }else{
            $search = $request->search;
        }


        $list = DB::table($GLOBALS['season_prefix']."prv_".$request->prov_code.".farmer_information_final")
            ->select("final_claimable","id","rsbsa_control_no", "rcef_id","lastName", "firstName", "midName as middleName", "brgy_name as brgy", "crop_area as ffrs", "rsms_actual_area as rsms","final_area")
            ->where("municipality", $request->municipality)
            ->where("rsbsa_control_no", "LIKE", $search."%")
            ->where("rsms_actual_area", ">", 0)

            ->orWhere("municipality", $request->municipality)
            ->where("lastName", "LIKE", $search."%")
            ->where("rsms_actual_area", ">", 0)

            ->orWhere("municipality", $request->municipality)
            ->where("firstName", "LIKE", $search."%")
            ->where("rsms_actual_area", ">", 0)

            ->orWhere("municipality", $request->municipality)
            ->where("rcef_id", "LIKE", $search."%")
            ->where("rsms_actual_area", ">", 0)


            ->orderBy("brgy_name","lastName", "firstName")
            ->get();

        $list = collect($list);
$prv_code = $request->prov_code;

        return Datatables::of($list)
        ->addColumn('action', function($row) use ($prv_code){  

             if($row->rsms > 0 ){
                if($row->final_area == $row->ffrs){
                    $options = "<option value='".$row->ffrs."' selected> ".$row->ffrs." </option>";
                    $options .= "<option value='".$row->rsms."'> ".$row->rsms." </option>";
                     }
                else{
                    $options = "<option value='".$row->rsms."' selected> ".$row->rsms." </option>";
                    $options .= "<option value='".$row->ffrs."'> ".$row->ffrs." </option>";
                  
                }


             }else{
                $options = "<option value='".$row->final_area."'> ".$row->final_area." </option>";
             }


             

                
                $btn = "<select class='form-control' name='final_area' id='final_area' onchange='change_area(".$row->id.", ".'"'.$prv_code.'"'.",this.value);'>";
                $btn .= $options;
                $btn .= "</select>";

                return $btn;

        })
        ->make(true);

    }


    public function select_area_index(){
        
        $provinces = DB::table($GLOBALS['season_prefix']."rcep_reports_view.rcef_provinces")
            ->orderby("region_sort")
            ->get();

          return view("farmer_profile.rcef_area_troubleshoot")
                    ->with("provinces", $provinces);



    }



    public function municipality_list(Request $request){
        $prv = $GLOBALS['season_prefix']."prv_".$request->provCode;
        $municipality_list = DB::table($prv.".farmer_information_final")
            ->select("municipality")
            ->groupBy("municipality")
            ->get();

            return json_encode($municipality_list);
    }
    public function farmerExportList(){
        $province = DB::table($GLOBALS['season_prefix']."rcep_reports_view.rcef_nrp_provinces")
            ->orderBy("region_sort")
            ->get();
        return view("farmer_profile.farmer_list_export")
            ->with('province', $province);

    }
    public function farmer_list_export($province, $municipality){
            if($municipality == "N/A"){
                $municipality = "#N/A";
            }    
        

        $prv = $GLOBALS['season_prefix']."prv_".$province;
        $farmer_list = DB::table($prv.".farmer_information_final")
            ->select('rcef_id','rsbsa_control_no','lastName','firstName','midName','extName','sex','birthdate',DB::raw("final_area as crop_area"),'province','municipality','brgy_name',
            'mother_name','tel_no', DB::raw("CONCAT('PH',REPLACE(SUBSTR(rsbsa_control_no,1,8),'-',''),'000')"),'civil_status','fca_name','is_pwd','is_arb','is_ip','tribe_name','ben_4ps','data_source')
            ->where("municipality",$municipality)
            // ->where("status", "1")
            
            ->where("final_area", ">", 0)
            ->where('rcef_id', "!=", "")
            ->orderBy("lastName")
            ->orderBy("firstName")
            ->get();
        $excel_data = json_decode(json_encode($farmer_list), true); //convert collection to associative array to be converted to excel
        return Excel::create("FARMER_LIST".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
            $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
              
                // $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        "RCEF ID","RSBSA number","last name","first name","middle name","ext name","sex","birthdate","crop area","province","municipality","brgy name","mother's last name","mother's first name","mother's middle name","mother's ext name","Telephone number","Geocode / PSA Code","Civil Status","ID Type","Government ID Number","FCA Name","PWD Status (0=No, 1=Yes)","ARB (0=No, 1=Yes)","Indigenous People (0=No, 1=Yes)","Tribe Name","4PS Beneficiary (YES,NO)","Data Source"
                    ));
                $sheet->freezeFirstRow();
                $sheet->row(1, function($row) { $row->setBackground('#FFFF66');$row->setBorder('thin','thin','thin','thin');$row->setFontWeight('bold'); }); 

                //TRY
                    $cell_row = 2;
          
                    foreach($excel_data as $dat){
                        // $sheet->cell($col.$row, function($cells) use ($dat){
                        //     $cells->setValue($key);
                        //     $cells->setBackground('#62c95d');
                        //     $cells->setBorder('thin','thin','thin','thin');
                        //     });
                        
                            $sheet->row($cell_row,$dat);
                            // if($dat["data_source"] == "FFRS"){
                            //     $sheet->row($cell_row, function($row) { $row->setBackground('#B7DEE8');$row->setBorder('thin','thin','thin','thin'); });
                            // }else{
                            //     $sheet->row($cell_row, function($row) {$row->setBackground('#00FF00');$row->setBorder('thin','thin','thin','thin');});
                            // }


                            $cell_row++;
                    }


                    $sheet->setBorder('A1:AA'.$cell_row,'thin');

            });
        })->download('xlsx');


    }

    







    public function generate_rcef_id($api,$prv){
        if($api == "genmeNow@phIlRic3"){

            if($prv == "all"){
                $prv = "ds2024_prv_%";
            }else{
                $prv = $GLOBALS['season_prefix']."prv_".$prv;
            }
     
            $tables = DB::table("information_schema.TABLES")
            ->where("TABLE_SCHEMA", "LIKE", $prv)
            ->where("TABLE_NAME",  "farmer_information_final")
            ->where("TABLE_ROWS", ">", 0)
            ->get();
         
            foreach($tables as $prv_tbl){
                $province_code = str_replace($GLOBALS['season_prefix']."prv_","",$prv_tbl->TABLE_SCHEMA);
                $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
     
                    ->where("prv_code", $province_code)
                    ->value("province");
         
                $municipality_list= DB::table($prv_tbl->TABLE_SCHEMA.".".$prv_tbl->TABLE_NAME)
                    ->select("municipality")
                    ->groupBy("municipality")
                    // ->where("print_count","=",0)
                    ->where("rcef_id", "!=", "")
                    ->where("data_source", "FFRS")
                    ->get();
              
                   foreach($municipality_list as $municipality){
                        $brgy_list= DB::table($prv_tbl->TABLE_SCHEMA.".".$prv_tbl->TABLE_NAME)
                             ->select("brgy_name")
                            ->groupBy("brgy_name")
                            ->where("rcef_id", "!=", "")
                            // ->where("print_count","=",0)
                            ->where("data_source", "FFRS")
                            ->where("municipality", $municipality->municipality)
                            ->get();
                    
                        foreach($brgy_list as $brgy){
                            $data = $this->create_rcef_id($province,$municipality->municipality,$brgy->brgy_name,"procedure");
                            

                        }
                   }
            }
            

            return "DONE";

        }else{
            return "API ERR";
        }

       

    }

     public function pull_list($prv_id,$api_key)
    {   
          
        if($api_key != "rcef@pHilRic3"){
            return "FAILED LOADING";
        }

        ini_set('memory_limit', '-1');
        DB::beginTransaction();

        try {
            $list = DB::table($GLOBALS['season_prefix']."prv_".$prv_id.".farmer_information_final")
                ->where("rcef_id", "")
                ->limit(1000)
                ->get();
            $x = 0;
            foreach($list as $data){
                $checker=0;
                $rcef_id="";
                while ($checker==0) {
                    $rcef_id = "R".substr($prv_id,0,2).strtoupper(substr(md5(time()), 0, 6));
                    $da_farmer_profile = DB::table($GLOBALS['season_prefix']."prv_".$prv_id.".farmer_information")->where('rcef_id',$rcef_id)->count(); 
                    if($da_farmer_profile == 0){
                        $checker=1;
                    }                   
                }

    
                if($rcef_id != ''){
                    //UPDATE RCEF ID
                    DB::table($GLOBALS['season_prefix']."prv_".$prv_id.".farmer_information_final")
                    ->where("id", $data->id)
                    ->update([
                        "rcef_id" => $rcef_id
                    ]);
                }
                DB::commit();
               
             
            }



      
           
            return $x;
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
        }

    }


    public function push_to_prv($type, $api){
            
        if($api == "6ffa1a65a2db"){
            if($type == "exact"){
                $prv_list = DB::table("rcef_icts.rcef_result_match")
                    ->select("ffrs_farmer_address_prv")
                    ->groupBy("ffrs_farmer_address_prv")
                    ->get();

                $sum = 0;
                    foreach($prv_list as $prv){
                        $farmer_list = DB::table("rcef_icts.rcef_result_match")
                        ->where("score", "<", 1)
                        ->where("ffrs_farmer_address_prv", $prv->ffrs_farmer_address_prv)
                        ->groupBy(DB::raw("CONCAT(rsbsa_control_no,lastName,firstName,midName,extName,gender,province,municipality,barangay,mother_name,birthdate,fullname,ffrs_rsbsa_no,ffrs_first_name,ffrs_middle_name,ffrs_last_name,ffrs_ext_name,ffrs_mother_maiden_name,ffrs_gender,ffrs_birthdate,ffrs_farmer_reg_code,ffrs_farmer_address_reg,ffrs_farmer_prv_code,ffrs_farmer_address_prv,ffrs_farmer_mun_code,ffrs_farmer_address_mun,ffrs_farmer_bgy_code,ffrs_farmer_address_bgy,ffrs_deceased,ffrs_fca_member,ffrs_fca_name,ffrs_arb,ffrs_pwd,ffrs_parcel_reg_code,ffrs_parcel_address_reg,ffrs_parcel_prv_code,ffrs_parcel_address_prv,ffrs_parcel_mun_code,ffrs_parcel_address_mun,ffrs_parcel_bgy_code,ffrs_parcel_address_bgy,ffrs_ownership_type,ffrs_parcel_area,ffrs_cropname,ffrs_crop_area,ffrs_farm_type,ffrs_fullname)"))
                        ->count();
                  //$farmer_list->q = $prv->ffrs_farmer_address_prv;

                        $sum += $farmer_list;
                            //207947 -> exact
                            //65778 -> possible
                            //634053 -> diff
                            // foreach($farmer_list as $data){
                            //     dd($data->ffrs_farmer_address_prv);
                            // }
                    }
                    dd($sum);
            }
        }

        



    }

    public function genTable(Request $request){
        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        //    ->join($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces", "lib_yield_provinces.province", "=", "lib_prv.province")
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort")
            ->get();
        $table_arr = array();
           foreach($list as $prv){
            $prv_db = $GLOBALS['season_prefix']."prv_".$prv->prv_code;
            $check = DB::table("information_schema.COLUMNS")
            ->where("TABLE_SCHEMA", $prv_db)
            ->where("TABLE_NAME", "farmer_information_final")
            ->where("COLUMN_NAME", 'print_count')
            ->first();

            if($check != null){
                $muni_list = DB::table($prv_db.".farmer_information_final")
                    ->select("municipality","brgy_name", DB::raw("count(rcef_id) as total_unique"), DB::raw("SUM(IF(print_count > 0,1,0)) as total_printed"))
                    ->groupBy("municipality")
                    ->groupBy("brgy_name")
                    
                    // ->where("data_source", "FFRS")
                    ->get();

                    foreach($muni_list as $mun){
                        $percentage = ($mun->total_printed / $mun->total_unique) *100; 


                        array_push($table_arr,array(
                            "province" => $prv->province,
                            "municipality" =>  $mun->municipality,
                            "brgy_name" =>  $mun->brgy_name,
                            
                            "total_unique" => $mun->total_unique,
                            "total_printed" => $mun->total_printed,
                            "percentage" => number_format($percentage, 2)." %"
                        ));
                    }
            }else{
                continue;
            }
        }

        $tbl = collect($table_arr);

        return Datatables::of($tbl)->make(true);



    }

    public function create_new_prv($prv,$process_type){
    // $prv_db = "ds2024_prv_".$prv;
        if($prv == "all"){
            $prv = "ds2024_prv_%";
        }

        if($process_type == "create_view"){
            $prv_list = DB::table("information_schema.SCHEMATA")
            ->where("SCHEMA_NAME", "LIKE",$prv)
            ->get();
    
            foreach($prv_list as $prv_data){
                \Config::set('database.connections.pre_registration_db.database', $prv_data->SCHEMA_NAME);
                DB::purge("pre_registration_db");
                DB::connection("pre_registration_db")->getPdo();
                      //CREATE VIEW
                    $check = DB::table("information_schema.VIEWS")
                    ->where("TABLE_SCHEMA", $prv_data->SCHEMA_NAME)
                    ->where("TABLE_NAME", "municipal_report")
                    ->first();

                    if($check == null){
                        $sql = "CREATE VIEW ".$prv_data->SCHEMA_NAME.".`municipal_report`  AS SELECT `released`.`municipality` AS `municipality`, sum(`released`.`actual_area`) AS `total_area`, sum(`released`.`claimed_area`) AS `total_claimed`, count(`released`.`release_id`) AS `total_beneficiary`, sum(if(ucase(substr(`released`.`sex`,1,1)) = 'M',1,0)) AS `total_male`, sum(if(ucase(substr(`released`.`sex`,1,1)) = 'F',1,0)) AS `total_female`, sum(`released`.`bags`) AS `total_bags` FROM `released` GROUP BY `released`.`municipality``municipality` ";
                        DB::connection("pre_registration_db")->select(DB::raw($sql));
                    }
            
            }

        
            \Config::set('database.connections.pre_registration_db.database', $GLOBALS['season_prefix']."rcep_farmer_registration");
            DB::purge("pre_registration_db");
            DB::connection("pre_registration_db")->getPdo();


        }


        elseif($process_type == "create"){
            $prv_list = DB::table("information_schema.SCHEMATA")
            ->where("SCHEMA_NAME", "LIKE",$prv)
            ->get();
            $result = "";
            foreach($prv_list as $prv_data){
                $result .= $prv_data->SCHEMA_NAME;

                \Config::set('database.connections.pre_registration_db.database', $prv_data->SCHEMA_NAME);
                 DB::purge("pre_registration_db");
                 DB::connection("pre_registration_db")->getPdo();
                    //CREATE released
    
                $check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $prv_data->SCHEMA_NAME)
                    ->where("TABLE_NAME", "released")
                    ->first();
    
                if($check == null){
                    $sql = "CREATE TABLE ".$prv_data->SCHEMA_NAME.".`released` (
                        `release_id` int(11) NOT NULL,
                        `farmer_id` varchar(255) NOT NULL,
                        `rsbsa_control_no` varchar(100) NOT NULL,
                        `distributionID` varchar(255) NOT NULL,
                        `ticket_no` varchar(75) NOT NULL,
                        `batch_ticket_no` varchar(255) NOT NULL,
                        `province` varchar(100) NOT NULL,
                        `municipality` varchar(150) NOT NULL,
                        `dropOffPoint` varchar(150) NOT NULL,
                        `seed_variety` varchar(100) NOT NULL,
                        `bags` int(1) NOT NULL,
                        `date_released` varchar(100) NOT NULL,
                        `released_by` varchar(255) NOT NULL,
                        `send` int(1) NOT NULL DEFAULT 1,
                        `prv_dropoff_id` varchar(100) NOT NULL,
                        `is_processed` int(11) NOT NULL DEFAULT 1,
                        `transaction_code` varchar(255) NOT NULL,
                        `app_version` varchar(75) NOT NULL,
                        `date_synced` varchar(25) NOT NULL,
                        `brgy_name` varchar(75) NOT NULL,
                        `geocode_brgy` varchar(75) NOT NULL,
                        `indicator` varchar(5) NOT NULL,
                        `claimed_area` decimal(5,2) NOT NULL,
                        `isIndigent` int(1) NOT NULL,
                        `isPWD` int(1) NOT NULL,
                        `qr_code` varchar(100) NOT NULL,
                        `farmer_fname` varchar(255) NOT NULL,
                        `farmer_mname` varchar(255) NOT NULL,
                        `farmer_lname` varchar(255) NOT NULL,
                        `farmer_ext` varchar(255) NOT NULL,
                        `sex` varchar(6) NOT NULL,
                        `birthdate` varchar(100) NOT NULL,
                        `tel_number` varchar(100) NOT NULL,
                        `dop_name` varchar(150) NOT NULL,
                        `mother_name` varchar(255) NOT NULL,
                        `is_representative` int(11) NOT NULL,
                        `id_type` varchar(100) NOT NULL,
                        `relationship` varchar(100) NOT NULL,
                        `representative_name` varchar(200) NOT NULL,
                        `actual_area` double(10,2) NOT NULL,
                        `yield` double(10,2) NOT NULL,
                        `total_production` float(10,2) NOT NULL,
                        `ave_weight_per_bag` double(10,2) NOT NULL,
                        `area_harvested` double(10,2) NOT NULL,
                        `season` varchar(50) NOT NULL,
                        `psa_code` varchar(50) NOT NULL,
                        `claimed_calendar` int(1) NOT NULL,
                        `claimed_KP` int(1) NOT NULL,
                        `data_sharing_flag` int(1) NOT NULL COMMENT '0=no, 1=yes',
                        `distributed_kps` text NOT NULL,
                        `other_benefits_received` varchar(150) NOT NULL,
                        `crop_establishment` varchar(55) NOT NULL,
                        `seedling_age_days` int(5) NOT NULL,
                        `is_pre_registered` int(11) NOT NULL DEFAULT 0,
                        `updated_contact` varchar(50) DEFAULT NULL,
                        `sync_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
                      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

                      DB::connection("pre_registration_db")->select(DB::raw($sql));
                      $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`released` ADD PRIMARY KEY (`release_id`);";
                      DB::connection("pre_registration_db")->select(DB::raw($sql));
                      $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`released` MODIFY `release_id` int(11) NOT NULL AUTO_INCREMENT;";
                      DB::connection("pre_registration_db")->select(DB::raw($sql));
              
        
                      $result .= "| released";
                    }
            
                //DISTRIBUTION ID
                $check = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $prv_data->SCHEMA_NAME)
                ->where("TABLE_NAME", "distribution_site")
                ->first();

            if($check == null){
                $sql = "CREATE TABLE ".$prv_data->SCHEMA_NAME.".`distribution_site` (
                    `id` int(11) NOT NULL,
                    `prv_dropoff_id` varchar(50) NOT NULL,
                    `barangay` varchar(75) NOT NULL,
                    `assigned_distributor` varchar(150) NOT NULL,
                    `distribution_date` varchar(50) NOT NULL,
                    `lat` varchar(75) NOT NULL,
                    `lng` varchar(75) NOT NULL,
                    `send` int(1) NOT NULL,
                    `app_version` varchar(75) NOT NULL,
                    `sync_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

                  DB::connection("pre_registration_db")->select(DB::raw($sql));
                  $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`distribution_site` ADD PRIMARY KEY (`id`);";
                  DB::connection("pre_registration_db")->select(DB::raw($sql));
                  $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`distribution_site` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
                  DB::connection("pre_registration_db")->select(DB::raw($sql));
                    
                  
                  $result .= "| distribution";
                }

            //NRP PROFILE
            $check = DB::table("information_schema.TABLES")
            ->where("TABLE_SCHEMA", $prv_data->SCHEMA_NAME)
            ->where("TABLE_NAME", "nrp_profile")
            ->first();

        if($check == null){
            $sql = "CREATE TABLE ".$prv_data->SCHEMA_NAME.".`nrp_profile` (
                `id` int(11) NOT NULL,
                `rsbsa` varchar(150) NOT NULL,
                `fname` varchar(150) NOT NULL,
                `mname` varchar(150) NOT NULL,
                `lname` varchar(150) NOT NULL,
                `extname` varchar(150) NOT NULL,
                `sex` varchar(15) NOT NULL,
                `birthdate` varchar(15) NOT NULL,
                `phonenumber` varchar(75) NOT NULL,
                `claimed_seed` varchar(75) NOT NULL,
                `num_of_bag` int(11) NOT NULL,
                `area` decimal(5,2) NOT NULL,
                `send` int(11) NOT NULL,
                `package_weight` decimal(5,2) NOT NULL COMMENT 'total weight claimed in kg',
                `seed_variety` varchar(75) NOT NULL,
                `app_version` varchar(75) NOT NULL,
                `date_synced` varchar(25) NOT NULL,
                `brgy_name` varchar(75) NOT NULL,
                `geocode_brgy` varchar(75) NOT NULL,
                `indicator` varchar(5) NOT NULL,
                `isIndigent` int(1) NOT NULL,
                `isPWD` int(1) NOT NULL,
                `season` varchar(15) DEFAULT NULL,
                `yield` decimal(5,2) DEFAULT NULL,
                `weight_per_bag` decimal(5,2) DEFAULT NULL,
                `area_harvested` double DEFAULT NULL,
                `sync_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

              DB::connection("pre_registration_db")->select(DB::raw($sql));
              $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`nrp_profile` ADD PRIMARY KEY (`id`);";
              DB::connection("pre_registration_db")->select(DB::raw($sql));
              $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`nrp_profile` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
              DB::connection("pre_registration_db")->select(DB::raw($sql));
        
              $result .= "| nrp";
            }

        //FARMER PROFILE
        $check = DB::table("information_schema.TABLES")
        ->where("TABLE_SCHEMA", $prv_data->SCHEMA_NAME)
        ->where("TABLE_NAME", "farmer_information_final")
        ->first();

        if($check == null){
            $sql = "CREATE TABLE ".$prv_data->SCHEMA_NAME.".farmer_information_final (
                `id` int(11) NOT NULL , 
                `rsbsa_control_no` VARCHAR(150) NOT NULL , 
                `farmer_id` VARCHAR(255) NOT NULL , 
                `rcef_id` VARCHAR(100) NOT NULL , 
                `distributionID` VARCHAR(155) NOT NULL , 
                `lastName` VARCHAR(255) NOT NULL , 
                `firstName` VARCHAR(255) NOT NULL , 
                `midName` VARCHAR(255) NOT NULL , 
                `extName` VARCHAR(20) NOT NULL , 
                `fullName` VARCHAR(255) NOT NULL , 
                `sex` VARCHAR(20) NOT NULL , 
                `birthdate` VARCHAR(25) DEFAULT NULL , 
                `yield_area_harvested` float(5,2) DEFAULT NULL , 
                `yield_no_of_bags` int(11) DEFAULT NULL, 
                `yield_weight_per_bag` float(5,2) DEFAULT NULL, 
                `parcel_area` float(5,2) NOT NULL , 
                `crop_area` float(5,2) NOT NULL , 
                `actual_area` float(5,2) NOT NULL , 
                `total_claimable` int(11) NOT NULL, 
                `is_claimed` int(1) NOT NULL, 
                `is_ebinhi` int(1) NOT NULL , 
                `is_replacement` int(1) NOT NULL , 
                `replacement_area`  float(5,2) NOT NULL, 
                `replacement_bags` int(11) NOT NULL , 
                `replacement_bags_claimed` int(11) NOT NULL , 
                `province` VARCHAR(150) DEFAULT NULL , 
                `municipality` VARCHAR(150) DEFAULT NULL , 
                `mother_name` VARCHAR(150) DEFAULT NULL ,  
                `is_pwd` int(1) NOT NULL , 
                `is_ip` int(1) NOT NULL, 
                `sync_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp())
                ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                DB::connection("pre_registration_db")->select(DB::raw($sql));
                $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`farmer_information` ADD PRIMARY KEY (`id`);";
                DB::connection("pre_registration_db")->select(DB::raw($sql));
                $sql = "ALTER TABLE ".$prv_data->SCHEMA_NAME.".`farmer_information` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
                DB::connection("pre_registration_db")->select(DB::raw($sql));
            
                $result .= "| farmer_profile";     
        }



            }
    
            \Config::set('database.connections.pre_registration_db.database', $GLOBALS['season_prefix']."rcep_farmer_registration");
            DB::purge("pre_registration_db");
            DB::connection("pre_registration_db")->getPdo();
            return $result;
        } //CREATE

   

     

    }




    public function prv_db_gen(){
        $lib = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->join($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces", "lib_prv.province", "=", "lib_yield_provinces.province")
            ->groupBy("lib_prv.province")
            ->get();
        $arr = array();
        foreach($lib as $prvs){
            // $prv_db = $GLOBALS['season_prefix']."prv_".$prvs->prv_code;
            // $prv_table = "farmer_information";

            // \Config::set('database.connections.pre_registration_db.database', $prv_db);
            // DB::purge("pre_registration_db");
            // DB::connection("pre_registration_db")->getPdo();



            // $sql = "ALTER TABLE ".$prv_db.".`farmer_information` ADD `old_rsbsa` VARCHAR(200) NULL AFTER `rcef_id`;";
            // DB::connection("pre_registration_db")->select(DB::raw($sql));
            // \Config::set('database.connections.pre_registration_db.database', $GLOBALS['season_prefix']."rcep_farmer_registration");
            // DB::purge("pre_registration_db");
            // DB::connection("pre_registration_db")->getPdo();

             $data = $this->create_da_profile($prvs->prv_code);
            array_push($arr, array(
                $prvs->prv_code => "added"
            ));
        }

        return $arr;
    }



    public function create_da_profile($prv_code){
     
        //0354
    $prv_db = $GLOBALS['season_prefix']."prv_".$prv_code;
    $prv_table = "unmatched_temp";

    $check = DB::table("information_schema.TABLES")
        ->where("TABLE_SCHEMA", $prv_db)
        ->where("TABLE_NAME", $prv_table)
        ->first();

        if($check != null){
            return "exist";
        }else{
            \Config::set('database.connections.pre_registration_db.database', $prv_db);
            DB::purge("pre_registration_db");
            DB::connection("pre_registration_db")->getPdo();


  

            $sql = "CREATE TABLE ".$prv_db.".`farmer_information_temp` ( `id` INT NOT NULL AUTO_INCREMENT , `info_status` VARCHAR(100) NOT NULL,`rcef_id` VARCHAR(100) NOT NULL ,`old_rsbsa` VARCHAR(200) NOT NULL , `rsbsa_control_no` VARCHAR(150) NOT NULL ,`first_name` VARCHAR(150) NOT NULL , `middle_name` VARCHAR(150) NOT NULL , `last_name` VARCHAR(150) NOT NULL , `ext_name` VARCHAR(50) NULL , `sex` VARCHAR(10) NULL,`birthdate` VARCHAR(50) NULL,`deceased` VARCHAR(10) NULL,`fca_member` VARCHAR(10) NULL,`fca_name` VARCHAR(255) NULL,`is_arb` VARCHAR(10) NULL,`is_pwd` VARCHAR(10) NULL,`farmer_reg` VARCHAR(255) NULL,`farmer_prv` VARCHAR(255) NULL,`municipality` VARCHAR(255) NULL, `farmer_reg_code` VARCHAR(255) NULL,`farmer_prv_code` VARCHAR(255) NULL,`municipality_code` VARCHAR(255) NULL, `farmer_brgy` VARCHAR(10) NULL,`brgy_name` VARCHAR(150) NULL,`farm_reg` VARCHAR(255) NULL,`farm_prv` VARCHAR(255) NULL,`farm_muni` VARCHAR(255) NULL, `farm_reg_code` VARCHAR(255) NULL,`farm_prv_code` VARCHAR(255) NULL,`farm_muni_code` VARCHAR(255) NULL, `farm_brgy` VARCHAR(10) NULL,`farm_brgy_name` VARCHAR(150) NULL,`farm_ownership` VARCHAR(150) NULL,`parcel_area` VARCHAR(150) NULL,`crop_area` VARCHAR(150) NULL,`farm_type` VARCHAR(150) NULL,`m_fname` VARCHAR(150) NULL,`m_mname` VARCHAR(150) NULL,`m_lname` VARCHAR(150) NULL,`m_ename` VARCHAR(150) NULL,`m_fullname` VARCHAR(150) NULL,`origin` VARCHAR(150) NULL,`print_count` INT(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB;";


            // $sql = "CREATE TABLE ".$prv_db.".`unmatched_temp` ( `id` INT NOT NULL AUTO_INCREMENT , `rsbsa_control_no` VARCHAR(200) NOT NULL,`firstName` VARCHAR(200) NOT NULL,`lastName` VARCHAR(200) NOT NULL,`midName` VARCHAR(200) NOT NULL,`extname` VARCHAR(200) NOT NULL,`gender` VARCHAR(200) NOT NULL,`province` VARCHAR(200) NOT NULL,`municipality` VARCHAR(200) NOT NULL,`brgy` VARCHAR(200) NOT NULL, `mother_fname` VARCHAR(200) NOT NULL,`mother_mname` VARCHAR(200) NOT NULL,`mother_lname` VARCHAR(200) NOT NULL,`mother_ename` VARCHAR(200) NOT NULL,`birthdate` VARCHAR(200) NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB;";

            DB::connection("pre_registration_db")->select(DB::raw($sql));


            \Config::set('database.connections.pre_registration_db.database', $GLOBALS['season_prefix']."rcep_farmer_registration");
            DB::purge("pre_registration_db");
            DB::connection("pre_registration_db")->getPdo();

            return $prv_code;
        }


}



public function getGeneratedId($province, $municipality, $brgy, $season){
    if($municipality == "#N/A"){
        $municipality = "NA";
    }

    if($brgy == "#N/A"){
        $brgy = "NA";
    }
    
    $path = "rcef_id\\".$province."\\".$municipality."\\".$brgy;
    
    // $path = public_path($path);

    $path = "C:\Apache24\/vhost\/rcef_unique_checker\/public\/rcef_id_generator\/public\/rcef_id\/".$season."\/".$province."\/".$municipality."\/".$brgy;
    //dd($path);
    $return_arr = array();
   if(is_dir($path)){
        $files = scandir($path);

        foreach($files as $key => $file){
            
            if($file == "."){
                
            }elseif($file == ".."){

            }else
            {
                if(substr($file,0,4) == "RCEF"){
                    $disable = "disabled = 'disabled' ";
                    $path = "";
                }else{
                    $disable = "";
                    $path = "https://rcef-checker.philrice.gov.ph\/public\/rcef_id_generator\/public\/rcef_id";
      

                    $path .= '/'.$province.'/'.$municipality.'/'.$brgy.'/'.$file;
                }

               

             $return_arr[$key]["name"]= $file;
             $return_arr[$key]["path"]='<a class="btn btn-sm btn-success" onclick="window.open('."'".$path."'".', '."'".'_blank'."'".');" '.$disable.' >Download</a>';
            
            }




        }
        return json_encode($return_arr);
   }else{
    return json_encode("NO DOWNLOADED IDS");
   }

//

}





    public function pushUnmatched($prv){
        $unmatch= DB::table($GLOBALS['season_prefix']."prv_".$prv.".unmatched_temp")
                ->get();

         $i = 0;
        foreach($unmatch as $data){
            $arr = "";
            $profile = DB::table("julyffrs.farmer_info_".$prv)
                ->where("rsbsa_control_no", $data->rsbsa_control_no)
                ->where("lastName", $data->lastName)
                ->where("firstName", $data->firstName)
                ->where("midName", $data->midName)
                ->where("birthdate", $data->birthdate)
                ->where("mother_fname", $data->mother_fname)
                ->first();
            if($profile != null){
                $arr = array(
                    'info_status' => "U", 
                    'old_rsbsa' => $data->rsbsa_control_no, 
                    'rsbsa_control_no' =>  $data->rsbsa_control_no, 
                    'first_name' =>  $data->firstName, 
                    'middle_name' => $data->midName, 
                    'last_name' => $data->lastName, 
                    'ext_name' => $data->extname, 
                    'sex' => $data->gender, 
                    'birthdate' => $data->birthdate, 
                    'farmer_prv' => $data->province, 
                    'municipality' => $data->municipality, 
                    'farmer_reg_code' => substr($data->rsbsa_control_no,0,2), 
                    'farmer_prv_code' => substr($data->rsbsa_control_no,3,2), 
                    'municipality_code' => substr($data->rsbsa_control_no,6,2), 
                    'farmer_brgy' => substr($data->rsbsa_control_no,9,3), 
                    'brgy_name' => $data->brgy, 
                    'farm_prv' => $data->province, 
                    'farm_muni' => $data->municipality, 
                    'farm_reg_code' => substr($data->rsbsa_control_no,0,2), 
                    'farm_prv_code' => substr($data->rsbsa_control_no,3,2), 
                    'farm_muni_code' => substr($data->rsbsa_control_no,6,2), 
                    'farm_brgy' => substr($data->rsbsa_control_no,9,3), 
                    'farm_brgy_name' => $data->brgy, 
                    'parcel_area' => $profile->actual_area, 
                    'crop_area' => $profile->actual_area, 
                    'm_name' => $data->mother_name, 
                    'm_fullname' => $data->mother_name, 
                    'origin' => "U", 
                    'print_count' => 0,
                );

            }else{
                $arr= array(
                    'info_status' => "U", 
                    'old_rsbsa' => $data->rsbsa_control_no, 
                    'rsbsa_control_no' =>  $data->rsbsa_control_no, 
                    'first_name' =>  $data->firstName, 
                    'middle_name' => $data->midName, 
                    'last_name' => $data->lastName, 
                    'ext_name' => $data->extname, 
                    'sex' => $data->gender, 
                    'birthdate' => $data->birthdate, 
                    'farmer_prv' => $data->province, 
                    'municipality' => $data->municipality, 
                    'farmer_reg_code' => substr($data->rsbsa_control_no,0,2), 
                    'farmer_prv_code' => substr($data->rsbsa_control_no,3,2), 
                    'municipality_code' => substr($data->rsbsa_control_no,6,2), 
                    'farmer_brgy' => substr($data->rsbsa_control_no,9,3), 
                    'brgy_name' => $data->brgy, 
                    'farm_prv' => $data->province, 
                    'farm_muni' => $data->municipality, 
                    'farm_reg_code' => substr($data->rsbsa_control_no,0,2), 
                    'farm_prv_code' => substr($data->rsbsa_control_no,3,2), 
                    'farm_muni_code' => substr($data->rsbsa_control_no,6,2), 
                    'farm_brgy' => substr($data->rsbsa_control_no,9,3), 
                    'farm_brgy_name' => $data->brgy, 
                     
                    'm_fname' => $data->mother_name, 
                    'm_fullname' => $data->mother_name, 
                    'origin' => "U", 
                    'print_count' => 0,
                );
            }
                

           $recheck =  DB::table($GLOBALS['season_prefix']."prv_".$prv.".farmer_information")
            ->where("rsbsa_control_no", $data->rsbsa_control_no)
            ->where("last_name", $data->lastName)
            ->where("first_name", $data->firstName)
            ->where("middle_name", $data->midName)
            ->where("birthdate", $data->birthdate)
            ->where("m_fname", $data->mother_fname)
            ->first();
            if($recheck != null){

            }else{
                DB::table($GLOBALS['season_prefix']."prv_".$prv.".farmer_information")
                ->insert($arr);
                $i++;
            }

            

           
        }

        return $i;


    }


 

    public function rcefIdGenIndex(){

        $arr_station = array("Negros", "Los Banos", "Central Experiment Station", "Batac", "Agusan","Isabela", "Bicol");

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $provinces =  DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
            // ->whereIn("province", $allowed_stations)
             ->groupBy('province')
             ->orderBy('region_sort', 'ASC')
             ->get();

        }else{

             if(Auth::user()->province == ""){
                $mss = "No Province Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);

             }else{
                //     $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                //     ->select('lib_prv.*')
                //     ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
                // ->groupBy('lib_prv.province')
                // ->where("prv", "LIKE", Auth::user()->province."%")
                // ->orderby('region_sort', 'ASC')
                // ->get();
                $allowed_stations = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_station")
                ->select("province")
                ->where("stationID", Auth::user()->stationId)
                ->whereIn("station", $arr_station )
                ->groupby("province") 
                ->get();
            $allowed_stations = json_decode(json_encode($allowed_stations), true);

            $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_nrp_provinces')
               ->whereIn("province", $allowed_stations)
                ->groupBy('province')
                ->orderBy('region_sort', 'ASC')
                ->get();



             }


         
        }
    
        return view("farmer_profile.rcef_id_list")
            ->with("provinces", $provinces)
            ->with("stations", $arr_station)
            ;
    }
/* 
    public function store_ids(){
        $provinces = array(1602,1603,0604,0505,0606,0377,0308,0878,0712,0314,0619,0421,1182,1123,1124,1125,1427,0129,1432,0133,0434,1035,1536,1538,0541,1042,1043,0645,0746,0250,0354,0456,0257,0860,1280,0562,1263,0864,1668,0371,0973,0983);
        dd($provinces);


    } */

    
    public function getGeneratedId_zip($province, $municipality, $brgy){
      /*   $archive = new PclZip("archive.zip");
        $v_filename = "new_file.txt";
        $v_content = "This is the content of file one\nHello second line";
       return $list = $archive->create(array(
                                       array( PCLZIP_ATT_FILE_NAME => $v_filename,
                                              PCLZIP_ATT_FILE_CONTENT => $v_content
                                             )
                                       )
                                 );
        if ($list == 0) {
          die("ERROR : '".$archive->errorInfo(true)."'");
        } */  


        
        $public_dir = public_path().'\\files';
        //$public_dir = public_path().'/'.$province.'/'.$municipality.'/'.$brgy;

        $path = public_path('rcef_id'.'\\'.$province.'\\'.$municipality.'\\'.$brgy);
       //  $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        // $path    = './';
        $files = scandir($path);
       
        $zipFileName = 'files-'.time().'.zip';
        $zip = new ZipArchive;
        if ($zip->open($public_dir . '/' . $zipFileName, ZipArchive::CREATE) === TRUE) { 
			foreach($files as $f):               
                    if($f == "." || $f == ".."){
                        continue;
                    }
                   
				     $zip->addFile($path.'\\'.$f,$f);
			endforeach;   
            $zip->close(); 
        }     
         $headers = array(
                'Content-Type' => 'application/octet-stream',
            );
        $filetopath=$public_dir.'\\'.$zipFileName;
        if(file_exists($filetopath)){
            
            return response()->download($filetopath,$zipFileName,$headers)->deleteFileAfterSend(true);
        }
    }


    public function create_rcef_id($province,$municipality,$brgy_name,$type){
        
        if($brgy_name == "NA"){
            $brgy_name = "#N/A";
        }


        if($type == "municipal"){
            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", 'like', $province)
          //  ->where('municipality', 'like', $municipality)
            ->first();
            $prv_db = $GLOBALS['season_prefix']."prv_".$prv->prv_code;   
        
            $check = DB::table("information_schema.TABLES")
            ->where("TABLE_SCHEMA", $prv_db)
            ->where("TABLE_NAME", "farmer_information_final")
            ->first();

            if($check == null){
                return "No Farmers is uploaded on this municipality";
            }
            $data = DB::table($prv_db.".farmer_information_final")
            //   ->where("print_count", 0)
              ->where("municipality", $municipality )
              ->where("brgy_name", 'LIKE', $brgy_name )
              ->where("rcef_id", "!=", "")
            //   ->where("data_source", "FFRS") irwin
            //   ->limit(1)
                ->orderBy("municipality","last_name","first_name")
                ->get();
           


        }elseif($type == "procedure"){
            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", 'like', $province)
          //  ->where('municipality', 'like', $municipality)
            ->first();
            $prv_db = $GLOBALS['season_prefix']."prv_".$prv->prv_code;   
        
            $check = DB::table("information_schema.TABLES")
            ->where("TABLE_SCHEMA", $prv_db)
            ->where("TABLE_NAME", "farmer_information_final")
            
            ->first();

            if($check == null){
                return "No Farmers is uploaded on this municipality";
            }
            $data = DB::table($prv_db.".farmer_information_final")
            //   ->where("print_count", 0)
              ->where("municipality", $municipality )
              ->where("brgy_name", 'LIKE', $brgy_name )
              ->where("rcef_id", "!=", "")
            //   ->where("data_source", "FFRS")irwin
            //   ->limit(1)
                ->orderBy("municipality","last_name","first_name")
                ->get();

        

        }
        else{
            //province = prv_db
            //brgy_name = rcef_id
            $data = DB::table($province.".farmer_information_final")
                ->where("rcef_id", $brgy_name)
                // ->where("data_source", "FFRS") irwin
                 ->limit(1)
                ->get();

          

        }
        
        if(count($data)>0){
            
            // dd(count($data));
            foreach($data as $farmer_info){

                if($municipality == "#N/A"){
                    $municipality = "NA";
                }
                $qBrgy = $brgy_name;
                if($brgy_name == "#N/A"){
                    $brgy_name = "NA";
                }
    
    
                //CREATE PROVINCE
                $path = public_path("rcef_id\\".$province);
                if(!is_dir($path)){
                 mkdir($path);  
                }
                //CREATE MUNICIPALITY
                $path .= "\\".$municipality;
                if(!is_dir($path)){
                 mkdir($path);  
                }
                //CREATE BRGY
                $path .= "\\".$brgy_name;
                if(!is_dir($path)){
                 mkdir($path);  
                }
    
                $pdf_name = "RCEF_ID_".$farmer_info->rcef_id."_".date("Y-m-d").".pdf";
                $path .= "\\".$pdf_name;
     
                $pdf = PDFTIM::loadView('farmer_profile.rcef_id', 
                ['data' => $farmer_info])
                ->setPaper('A4', 'portrait'); 
                
    
               
    
                if($type == "procedure"){
    
                    // $data_update = DB::table($prv_db.".farmer_information")
                    // ->where("print_count", 0)
                    //  ->where("municipality", $municipality )
                    //  ->where("brgy_name", 'LIKE', $qBrgy )
                    //  ->where("rcef_id", "!=", "")
                    //  ->where("data_source", "FFRS")
                    //  // ->limit(1)
                    //  ->orderBy("municipality","last_name","first_name")
                    //  ->increment("print_count",1);
                    $save = $pdf->save($path);
                    $data_update = DB::table($prv_db.".farmer_information_final")
                    
                     ->where("rcef_id", "=", $farmer_info->rcef_id)
                     ->where("data_source", "FFRS")
                     ->increment("print_count",1);
                    

    
    
                 
                }else{
    
                    // $data_update = DB::table($prv_db.".farmer_information")
                    // ->where("print_count", 0)
                    //  ->where("municipality", $municipality )
                    //  ->where("brgy_name", 'LIKE', $qBrgy )
                    //  ->where("rcef_id", "!=", "")
                    //  ->where("data_source", "FFRS")
                    //  // ->limit(1)
                    //  ->orderBy("municipality","last_name","first_name")
                    //  ->increment("print_count",1);
                    
                    
                    $data_update = DB::table($province.".farmer_information_final")
                    ->where("rcef_id", "=", $farmer_info->rcef_id)
                    ->where("data_source", "FFRS")
                    ->increment("print_count",1);

                    // $data_update = DB::table($province.".farmer_information")
                    // ->where("rcef_id", $brgy_name)
                    // ->where("data_source", "FFRS")
                    //  ->limit(1)
                    // ->increment("print_count",1);
    
              
                }











            }


            if($type == "procedure"){
                return "done";
            }else{
                return $pdf->stream($pdf_name);
            }

           	
        }else{
            return "empty";
        }

       
    }

    

    public function reprint_rcef_id(Request $request){
        // dd($request->all());
        $rsbsa = $request->rsbsa;
        $first = $request->first;
        $last = $request->last;
        $bday = $request->bday;
        $prv_from_rsbsa = $GLOBALS['season_prefix']."prv_".substr($rsbsa, 0,2).substr($rsbsa, 3,2);
        
        $check = DB::table("information_schema.TABLES")
        ->where("TABLE_SCHEMA", $prv_from_rsbsa)
        ->where("TABLE_NAME", "farmer_information_final")
        ->first();

        if($check == null){
            return json_encode("NO TABLE");
        }else{
          
                $bday = date("Y-m-d", strtotime($bday));
            
                $data = DB::table($prv_from_rsbsa.".farmer_information_final")
                ->where("firstName", "LIKE", $first)
                ->where("lastName", "LIKE", $last)
                // ->where("birthdate", "LIKE", $bday)
                ->where("rsbsa_control_no", "LIKE",$rsbsa) 
                ->whereRaw("rcef_id != '' ")             
                ->first();
            // dd($data);
                if($data != null){
                    return json_encode(array(
                        "prv_code" => $prv_from_rsbsa,
                        "rcef_id" => $data->rcef_id 
                    ));
                }else{
                    return json_encode("NO_DB");
                }

        }




    }

    public function getMunicipality(Request $request){
        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->groupBy("municipality")
            ->orderBy("municipality")
            ->get();
        
        // $municipality = DB::table($GLOBALS['season_prefix']."prv_".$prv->prv_code.".farmer_information_final")
        //     ->select("municipality as municipality")
        //     // ->where("rsms_actual_area",">",0)
        //     // ->orWhere("data_source", "RSMS")
        //     ->groupBy("municipality")
        //     ->orderBy("municipality")
            
        //     ->get();

            return json_encode($prv);
    }

   public function getBrgy(Request $request){

        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->where("municipality", $request->municipality)
            ->first();


            
        if($prv != null){
            // $prv_db = $GLOBALS['season_prefix']."prv_".$prv->prv_code;

                // $municipality_code = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                //     ->where()
                //     ->where("municipality", $request->municipality)
                //     ->first();
                    $brgy_list = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
                        ->where("geocode_municipality", $prv->prv)
                        ->groupBy("name")
                        ->get();
                    
                    if(count($brgy_list) > 0){
                        return json_encode($brgy_list);
                    }else{
                        return json_encode("NO_DB");
                    }
              
              





                

        }else{
            return json_encode("NO_DB");
        }
            



   }



}

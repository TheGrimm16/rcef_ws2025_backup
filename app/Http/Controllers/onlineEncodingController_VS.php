<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Datatables;
class onlineEncodingController extends Controller
{

    public function get_all_parcel(Request $request){
        // dd($request->all());

        $prefix = $GLOBALS['season_prefix'];
        $main_farmer = DB::table($prefix."prv_".$request->prv.".".$request->tbl)
            ->where("id", $request->db_ref)
            ->first();
        if($request->tbl == "farmer_information_final_x"){
            $ffrs_data = DB::table($prefix."prv_".$request->prv.".farmer_information_final")
                ->where("assigned_rsbsa", $main_farmer->rsbsa_control_no)
                ->first();
        }else{
            $ffrs_data = DB::table($prefix."prv_".$request->prv.".farmer_information_final")
                ->where("rsbsa_control_no", $main_farmer->rsbsa_control_no)
                ->first();
        }

        $tbl_arr = array();
        if($ffrs_data != null){
            $region_number = substr($ffrs_data->rsbsa_control_no,0,2);


            $parcel_data = DB::table("ffrs_may_2023.region_".$region_number)
                ->where("rsbsa_no", $ffrs_data->rsbsa_control_no)
                ->get();

            foreach($parcel_data as $pd){
                $prv_claiming = str_replace("-", "",substr($pd->claiming_prv, 0 ,5));
                $claiming_prv = $pd->claiming_prv;

                $may_data = DB::table($prefix."prv_".$prv_claiming.".farmer_information_final")
                    ->where("claiming_prv", $claiming_prv)
                    ->where("firstName", $pd->first_name)
                    ->where("midName", $pd->middle_name)
                    ->where("lastName", $pd->last_name)
                    ->first();

                    $province = $pd->parcel_address_prv;
                    $municipality = $pd->parcel_address_mun;
                    $final_area = $pd->crop_area;
                    
                    $action = "<button class='btn btn-dark btn-sm' disabled>Set Distribution</button>";
                    if($may_data != null){
                        $action =  "<button class='btn btn-success btn-sm'>Set Distribution</button>";
                        $release = DB::table($prefix."prv_".$prv_claiming.".new_released")
                            ->where("db_ref", $may_data->db_ref)
                            ->first();
                        if($release != null){
                            $action =  "<button class='btn btn-success btn-sm' disabled>Distributed</button>";
                        }

                        $final_area = $may_data->final_area;
                        
                    }
                
                


                array_push($tbl_arr, array(
                    "province" => $province,
                    "municipality" => $municipality,
                    "final_area" => $final_area,
                    "action" =>$action,
                ));

            }


        }

        
        $tbl_arr = collect($tbl_arr);
        return Datatables::of($tbl_arr)
            ->make(true);
    }


    public function view_parcelary(Request $request){
        $prefix = $GLOBALS['season_prefix'];
        $main_farmer = DB::table($prefix."prv_".$request->prv_number.".".$request->tbl)
            ->where("id", $request->id)
            ->first();

            $home_prv = str_replace("-","",substr($main_farmer->rsbsa_control_no,0,8));

            // dd($home_prv);
         $home =   DB::table($prefix."rcep_delivery_inspection.lib_prv")
                ->where("prv",$home_prv )
                ->first();
         $home_info = "N/A";
        if($home != null){
            $home_info = $home->province.", ".$home->municipality;
        }


        if($request->tbl == "farmer_information_final_x"){
            $ffrs_data = DB::table($prefix."prv_".$request->prv_number.".farmer_information_final")
                ->where("assigned_rsbsa", $main_farmer->rsbsa_control_no)
                ->first();
        }else{
            $ffrs_data = DB::table($prefix."prv_".$request->prv.".farmer_information_final")
                ->where("rsbsa_control_no", $main_farmer->rsbsa_control_no)
                ->first();
        }

        $crop_size = 0;
        if($ffrs_data != null){
            $region_number = substr($ffrs_data->rsbsa_control_no,0,2);


            $crop_size = DB::table("ffrs_may_2023.region_".$region_number)
                ->where("rsbsa_no", $ffrs_data->rsbsa_control_no)
                ->sum("crop_area");
            
        }

        $main_farmer->home = $home_info;
        $main_farmer->total_size = $crop_size;

        return json_encode($main_farmer);



    }


    public function select_farmer_vs(Request $request){

        if($request->rsbsa_rcef_id == ""){   $request->rsbsa_rcef_id = "%"; }
        if($request->fname_vs == ""){   $request->fname_vs = "%"; }
        if($request->lname_vs == ""){   $request->lname_vs = "%"; }
        if($request->mname_vs == ""){   $request->mname_vs = "%"; }
        if($request->ename_vs == ""){   $request->ename_vs = "%"; }
        

        $prv_code = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where("province", $request->province_select)
            ->first();
        $prv_number = $prv_code->prv_code;
        
        $farmer_list = DB::table($GLOBALS['season_prefix'].'prv_'.$prv_code->prv_code.'.farmer_information_final_x')
            ->select("*", DB::raw("CONCAT('Dec 2022') as list"), DB::raw("CONCAT('farmer_information_final_x') as tbl"))
            ->where("rcef_id", "LIKE", $request->rsbsa_rcef_id."%")
            ->where("firstName", "LIKE", $request->fname_vs."%")
            ->where("midName", "LIKE", $request->mname_vs."%")
            ->where("lastName", "LIKE", $request->lname_vs."%")
            ->where("extName", "LIKE", $request->ename_vs."%")
            ->orWhere("rsbsa_control_no", "LIKE", $request->rsbsa_rcef_id."%")
            ->where("firstName", "LIKE", $request->fname_vs."%")
            ->where("midName", "LIKE", $request->mname_vs."%")
            ->where("lastName", "LIKE", $request->lname_vs."%")
            ->where("extName", "LIKE", $request->ename_vs."%")
            ->get();
   
        // if(count($farmer_list) == 0){
        //     $farmer_list = DB::table($GLOBALS['season_prefix'].'prv_'.$prv_code->prv_code.'.farmer_information_final_feb')
        //         ->select("*", DB::raw("CONCAT('Feb 2023')  as list"), DB::raw("CONCAT('farmer_information_final_feb') as tbl"))
        //         ->where("rcef_id", "LIKE", $request->rsbsa_rcef_id."%")
        //         ->where("firstName", "LIKE", $request->fname_vs."%")
        //         ->where("midName", "LIKE", $request->mname_vs."%")
        //         ->where("lastName", "LIKE", $request->lname_vs."%")
        //         ->where("extName", "LIKE", $request->ename_vs."%")
        //         ->orWhere("rsbsa_control_no", "LIKE", $request->rsbsa_rcef_id."%")
        //         ->where("firstName", "LIKE", $request->fname_vs."%")
        //         ->where("midName", "LIKE", $request->mname_vs."%")
        //         ->where("lastName", "LIKE", $request->lname_vs."%")
        //         ->where("extName", "LIKE", $request->ename_vs."%")
        //         ->get();
        // }

        // if(count($farmer_list) == 0){
        //     $farmer_list = DB::table($GLOBALS['season_prefix'].'prv_'.$prv_code->prv_code.'.farmer_information_final')
        //         ->select("*", DB::raw("CONCAT('May 2023') as list"), DB::raw("CONCAT('farmer_information_final') as tbl"))
        //         ->where("rcef_id", "LIKE", $request->rsbsa_rcef_id."%")
        //         ->where("firstName", "LIKE", $request->fname_vs."%")
        //         ->where("midName", "LIKE", $request->mname_vs."%")
        //         ->where("lastName", "LIKE", $request->lname_vs."%")
        //         ->where("extName", "LIKE", $request->ename_vs."%")
        //         ->orWhere("rsbsa_control_no", "LIKE", $request->rsbsa_rcef_id."%")
        //         ->where("firstName", "LIKE", $request->fname_vs."%")
        //         ->where("midName", "LIKE", $request->mname_vs."%")
        //         ->where("lastName", "LIKE", $request->lname_vs."%")
        //         ->where("extName", "LIKE", $request->ename_vs."%")
        //         ->get();
        // }


        $farmer_list = collect($farmer_list);
            return Datatables::of($farmer_list)
            ->addColumn('rsbsa_no', function($row){
               return $row->rsbsa_control_no;
            })
            // ->addColumn('home_add', function($row){
            //     return $row->rsbsa_control_no;
            // })
            ->addColumn('birthdate', function($row){
            return $row->birthdate;
            })

            ->addColumn('contact_number', function($row){
                return $row->tel_no;
                })
    
            ->addColumn('mother', function($row){
                return $row->mother_lname;
                })
            ->addColumn('list_version', function($row){
                return $row->list;
                })
        
            ->addColumn('action', function($row) use ($prv_number){
                $btn = "<a class='btn btn-success'><i class='fa fa-external-link-square' aria-hidden='true'  onclick='show_parcelary(".'"'.$prv_number.'"'.',"'.$row->id.'"'.',"'.$row->tbl.'"'.");'> Check Parcelary</i></a>";

                 return $btn;
             })
                ->make(true);   
                
             

              


    }






    public function index(){
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('lib_prv.province')
                ->join($GLOBALS['season_prefix'].'rcep_reports.lib_yield_provinces', 'lib_prv.province','=','lib_yield_provinces.province')
                ->groupBy("lib_prv.province")
                ->orderBy("region_sort", 'ASC')
                ->get();
        }else{

            $mss = "On Going Re-Development";
            return view("utility.pageClosed")
                ->with("mss",$mss);

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
   
        
        return view('onlineEncoding.index',compact('provinces'));
    }


    public function getMunicipal(Request $request){
        $municipality = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
        ->where("province", $request->province)
        ->groupBy('municipality')->get();


        return json_encode($municipality);
    }

    public function getDropOff(Request $request){
        $dop = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
        ->where("province", $request->province)
        ->where("municipality", $request->municipality)
        ->groupBy('prv_dropoff_id')->get();


        return json_encode($dop);
    }

    public function getSeedStock(Request $request){
        $seed_stock= DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select("seedVariety", DB::raw("SUM(totalBagCount) as bags"), "prv_dropoff_id", "region","province","municipality","dropOffPoint")
            ->where("province", $request->province)
            ->where("municipality", $request->municipality)
            ->where("prv_dropoff_id", $request->dop)
            ->groupBy("prv_dropoff_id")
            ->groupBy("seedVariety")
            ->get();

        $return = array();

        foreach($seed_stock as $seeds){
           $prv = substr($seeds->prv_dropoff_id,0,4);
           $released = DB::table($GLOBALS['season_prefix']."prv_".$prv.".new_released")
                ->where("prv_dropoff_id", $seeds->prv_dropoff_id)
                ->where("seed_variety", $seeds->seedVariety)
                ->sum("bags_claimed");
            
         

            $unreleased = DB::connection('delivery_inspection_db')->table('tbl_stocks_download_transaction')
                    ->where('prv_dropoff_id', $seeds->prv_dropoff_id)
                    ->where('seed_variety', $seeds->seedVariety)
                    ->where("is_cleared", 0)
                    ->sum('number_of_bag');
            $remaining = $seeds->bags - $released - $unreleased;

            array_push($return, array(
                "prv_dropoff_id" => $seeds->prv_dropoff_id,
                "seed_variety" => $seeds->seedVariety,
                "remaining" => $remaining 
            ));
        }

        return json_encode($return);
    }

    function searchFarmer(Request $request){




            $lib = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where("municipality", $request->municipality)
                ->where("province", $request->province)
                ->first();

             $search_value =    $request->search_bar;
            if($search_value == ""){
                $search_value = "%";
            }
       
            $prv_db = $lib->prv_code;

            if($lib != null){
                $rsbsa_pattern = $lib->regCode."-".$lib->provCode."-".$lib->munCode;
                 $bypasspattern = $lib->regCode.$lib->provCode.$lib->munCode;


                $prv_transform = DB::connection("delivery_inspection_db")->table('lib_prv_merged')
                    ->where("merge_prv", $prv_db)
                    ->first();

                if($prv_transform != null){
                    $prv_db = $prv_transform->main_prv;
                    $rsbsa_pattern = $lib->regCode.$lib->provCode;
                }
                //  dd($prv_db);
                $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
                ->select("rcef_id","rsbsa_control_no as rsbsa",DB::raw("UPPER(CONCAT(lastName,', ',firstName,' ',midName,' ',extName)) as name"),DB::raw("CONCAT(province,', ',municipality,' ',brgy_name) as address"), 'final_area', DB::raw("UPPER(SUBSTR(sex,1,1)) as sex"), 'birthdate' )
                ->where("lastName", "LIKE", $search_value.'%')
                ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                ->where("to_prv_code", "=", "")
                ->orWhere("firstName", "LIKE", $search_value.'%')
                ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                ->where("to_prv_code", "=", "")
                ->orWhere("midName", "LIKE", $search_value.'%')
                ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                ->where("to_prv_code", "=", "")
                //rsbsa
                ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                ->where("to_prv_code", "=", "")


                ->orWhere("rcef_id", "LIKE", $search_value)
                ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                ->where("to_prv_code", "=", "")
                ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
                ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                ->where("to_prv_code", "=", "")
                
                //FOR Province Jump 
                ->orWhere("lastName", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $rsbsa_pattern."%")
                ->orWhere("firstName", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $rsbsa_pattern."%")
                ->orWhere("midName", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $rsbsa_pattern."%")
                ->orWhere("rcef_id", "LIKE", $search_value)
                ->where("to_prv_code", "LIKE", $rsbsa_pattern."%")
                ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
                ->where("to_prv_code", "LIKE", $rsbsa_pattern."%")
                ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $rsbsa_pattern."%")
           

                //FOR MUNICIPALITY JUMP
                ->orWhere("lastName", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $bypasspattern."%")
                ->orWhere("firstName", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $bypasspattern."%")
                ->orWhere("midName", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $bypasspattern."%")
                ->orWhere("rcef_id", "LIKE", $search_value)
                ->where("to_prv_code", "LIKE", $bypasspattern."%")
                ->orWhere(DB::raw("CONCAT(lastName,' ',firstName,' ',midName,' ',extName)"), "LIKE", $search_value,"%")
                ->where("to_prv_code", "LIKE", $bypasspattern."%")
                ->orWhere("rsbsa_control_no", "LIKE", $search_value.'%')
                ->where("to_prv_code", "LIKE", $bypasspattern."%")

                ->orderBy("lastName")
                ->orderBy("firstName")
                ->limit(10)
                ->get();

               
                $farmer_info = collect($farmer_info);
                return Datatables::of($farmer_info)
                ->addColumn('action', function($row){
                    $btn = "<a class='btn btn-success'><i class='fa fa-thumbs-o-up' aria-hidden='true' onclick='select_farmer(".'"'.$row->rcef_id.'"'."); moding();'> Select</i></a>";

                     return $btn;
                })->make(true);
            }        
    }    
    
    function select_farmer(Request $request){
        $lib = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where("municipality", $request->municipality)
                ->where("province", $request->province)
                ->first();
            if($lib != null){
               $prv_db = $lib->prv_code;
               $prv_transform = DB::connection("delivery_inspection_db")->table('lib_prv_merged')
                    ->where("merge_prv", $prv_db)
                    ->first();

                if($prv_transform != null){
                    $prv_db = $prv_transform->main_prv;
                    $rsbsa_pattern = $lib->regCode.$lib->provCode;
                }

                $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
                    ->where("rcef_id", $request->rcef_id)
                    ->first();
                if($farmer_info != null){
                    $remaining = $farmer_info->final_claimable - $farmer_info->total_claimed;
                    $farmer_info = array(
                        "farmer_name" => $farmer_info->lastName.", ".$farmer_info->firstName." ".$farmer_info->midName." ".$farmer_info->extName,
                        "rsbsa_number" => $farmer_info->rsbsa_control_no,
                        "enrolled_area" => $farmer_info->final_area,
                        "claimable" => $farmer_info->total_claimed." claimed of ".$farmer_info->final_claimable." bags(s)",
                        "remaining" => $remaining,
                        "da_intervention_card" => $farmer_info->da_intervention_card,
                        "prv_code" => $lib->prv_code,
                        "fif_id" => $farmer_info->id,
                        "mother_last_name" => $farmer_info->mother_lname,
                        "mother_first_name" => $farmer_info->mother_fname,
                        "mother_mid_name" => $farmer_info->mother_mname,
                        "mother_ext_name" =>  $farmer_info->mother_suffix,
                        "birthdate" => $farmer_info->birthdate,
                        "tel_no" => $farmer_info->tel_no,
                        "ip" => $farmer_info->is_ip,
                        "ip_name" => $farmer_info->tribe_name,
                        "pwd" => $farmer_info->is_pwd,
                        "fca_name" => $farmer_info->fca_name,


                        "msg" => ""
                        );
                      
                }else{
                    $farmer_info = array(
                        "farmer_name" => "",
                        "rsbsa_number" => "",
                        "enrolled_area" => "",
                        "claimable" => "",
                        "remaining" => 0,
                        "da_intervention_card" => "",
                        "prv_code" => "",
                        "fif_id" => "",
                        "mother_last_name" => "",
                        "mother_first_name" => "",
                        "mother_mid_name" => "",
                        "mother_ext_name" => "",
                        "birthdate" => "",
                        "tel_no" => "",
                        "ip" => "",
                        "ip_name" => "",
                        "pwd" => "",
                        "fca_name" => "",
                        "msg" => "Farmer Not Found!"
                        );
                }
               
                

            }else{
                $farmer_info = array(
                    "farmer_name" => "",
                    "rsbsa_number" => "",
                    "enrolled_area" => "",
                    "claimable" => "",
                    "remaining" => 0,
                    "da_intervention_card" => "",
                    "prv_code" => "",
                    "fif_id" => "",
                    "mother_last_name" => "",
                    "mother_first_name" => "",
                    "mother_mid_name" => "",
                    "mother_ext_name" => "",
                    "birthdate" => "",
                    "tel_no" => "",
                    "ip" => "",
                    "ip_name" => "",
                    "pwd" => "",
                    "fca_name" => "",
                    "msg" => "Please check the location"
                    );
            }
            return json_encode($farmer_info);

    }

    public function saveDistribution(Request $request){
        $da_intervention_card = $request->da_intervention_card;
        $prv_code = $request->prv_code;
        $province = $request->province;
        $municipality = $request->municipality;
        $dop_id = $request->dop;
        $rcef_id = $request->rcef_id;
        $rsbsa_control_no = $request->rsbsa_control_no;
        $claimed_area = $request->claimed_area;
            $claimed_bags = ceil($claimed_area * 2);
        $variety = $request->variety;
        $yield_area = $request->yield_area;
        $yield_bags = $request->yield_bags;
        $yield_weight = $request->yield_weight;
        $crop_est = $request->crop_est;
        $eco_system = $request->eco_system;
        $water_source = $request->water_source;
        $planting_month = $request->planting_month;
        $planting_week = $request->planting_week;
            $planting_date = $planting_month.'/'.$planting_week;
        $mother_last_name = $request->mother_last_name;
        $mother_first_name = $request->mother_first_name;
        $mother_mid_name = $request->mother_mid_name;
        $mother_ext_name = $request->mother_ext_name;
        $birthdate = $request->birthdate;
        $tel_no = $request->tel_no;
        $ip = $request->ip;
        $ip_name = $request->ip_name;
        $pwd = $request->pwd;
        $fca_name = $request->fca_name;
        $kp_kit = $request->kp_kit;
        $ayuda_fertilizer = $request->ayuda_fertilizer;
        $ayuda_incentives = $request->ayuda_incentives;
        $ayuda_credit = $request->ayuda_credit;
        $ayuda = "";
            if($ayuda_fertilizer){$ayuda .= "fertilizer";}
            if($ayuda_incentives){$ayuda .= ",cash_incentives";}
            if($ayuda_credit){$ayuda .= ",credit";}
            //1602241147
        $rep = $request->rep;
        $rep_name = $request->rep_name;
        $rep_id = $request->rep_id;
        $rep_relationship = $request->rep_relationship;
        $mode = $request->mode;
        $farmer_id_address = $request->farmer_id_address;

        if($rep){$rep = 1;}else{ $rep = 0; }
        if($ip){$ip = 1;}else{ $ip = 0; }
        if($pwd){$pwd = 1;}else{ $pwd = 0; }


        $prv_transform = DB::connection("delivery_inspection_db")->table('lib_prv_merged')
        ->where("merge_prv", $prv_code)
        ->first();

        if($prv_transform != null){
            $prv_db_farmer = $prv_transform->main_prv;
        }else{
            $prv_db_farmer = $prv_code;
        }



        $farmer_info = DB::table($GLOBALS['season_prefix']."prv_".$prv_db_farmer.".farmer_information_final")
            ->where("rsbsa_control_no", $rsbsa_control_no)
            ->where("rcef_id", $rcef_id)
            ->first();
        if($farmer_info != null){
            $total_claimable = $farmer_info->final_claimable - $farmer_info->total_claimed;
            if($total_claimable < $claimed_area ){ return json_encode("Area exhausted"); }

                $seed_stock= DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->where("province", $province)
                    ->where("municipality", $municipality)
                    ->where("prv_dropoff_id", $dop_id)
                    ->where("seedVariety", $variety)
                    ->sum("totalBagCount");
            if($seed_stock < $claimed_bags ){return json_encode("Seed stocks exhausted");}

            $dropOffPoint =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                    ->where("prv_dropoff_id", $dop_id)
                    ->where("province", $province)
                    ->where("municipality", $municipality)
                    ->value("dropOffPoint");

                    DB::beginTransaction();
            try {
                DB::table($GLOBALS['season_prefix']."prv_".$prv_code.".new_released")
                ->insert([
                    "id" => "11111111111",
                    "rcef_id" => $rcef_id,
                    "prv_dropoff_id" => $dop_id,
                    "province" => $province,
                    "municipality" => $municipality,
                    "dropOffPoint" => $dropOffPoint,
                    "transaction_code" => "web",
                    "is_representative" => $rep,
                    "rep_name" => $rep_name,
                    "rep_id" => $rep_id,
                    "rep_relation" => $rep_relationship,
                    "claimed_area" => $claimed_area,
                    "bags_claimed" => $claimed_bags,
                    "seed_variety" => $variety,
                    "recipient_ls" => "-",
                    "planted_rcvd_seeds_ls" => "-",
                    "reason_not_planted_rcvd_seeds_ls" => "-",
                    "yield_area_harvested_ls" => $yield_area,
                    "yield_no_of_bags_ls" => $yield_bags,
                    "yield_wt_per_bag" => $yield_weight,
                    "crop_establishment_cs" => $crop_est,
                    "seedling_age" => "-",
                    "ecosystem_cs" => $eco_system,
                    "ecosystem_source_cs" => $water_source,
                    "planting_week" => $planting_date,
                    "has_kp_kit" => $kp_kit,
                    "kp_kit_count" => "-",
                    "other_benefits_received" => $ayuda,
                    "date_released" => date("Y-m-d"),
                    "released_by" => Auth::user()->username,
                    "time_start" => "-",
                    "time_end" => "-",
                    "app_version" => "web",
                    "distribution_type" => "Regular",
                    "mode" => $mode,
                    "farmer_id_address" => $farmer_id_address,
                    "content_rsbsa" => $rsbsa_control_no
                ]);

                $total_claimed = $farmer_info->total_claimed +  $claimed_bags;
                
             
                DB::table($GLOBALS['season_prefix']."prv_".$prv_db_farmer.".farmer_information_final")
                    ->where("id", $farmer_info->id)
                    ->update([
                        "da_intervention_card" => $da_intervention_card,
                        "birthdate" => $birthdate,
                        "yield_area_harvested" => $yield_area,
                        "yield_no_of_bags" => $yield_bags,
                        "yield_weight_per_bag" =>$yield_weight,
                        "is_claimed" => 1,
                        "fca_name" => $fca_name,
                        "total_claimed" => $total_claimed,
                        "mother_lname" => $mother_last_name,
                        "mother_fname" => $mother_first_name,
                        "mother_mname" => $mother_mid_name,
                        "mother_suffix" => $mother_ext_name,
                        "tel_no" => $tel_no,
                        "is_pwd" => $pwd,
                        "is_ip" => $ip,
                        "tribe_name" => $ip_name,
                    ]);
                DB::commit();

                    return json_encode("Distribution Success");

            } catch (\Throwable $th) {
                DB::rollback();
            }

       



        }

    }

}

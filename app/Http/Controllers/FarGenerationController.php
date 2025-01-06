<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\FarGeneration;
use Response;
use Auth;
use DB;
use PDFTIM;
use DOMPDF;
use Illuminate\Filesystem\Filesystem;
use PHPExcel;   
use PHPExcel_IOFactory;
use Excel;
use PDF;
class FarGenerationController extends Controller
{

    public function getGeneratedFars($province,$municipality){
        $season = $GLOBALS['season_prefix'];
        if($province == "0"){
            return json_encode("false");
        }
        if($municipality == "--SELECT ASSIGNED MUNICIPALITY--"){
            return json_encode("false");
        }
    
      
        
        $path = "FLSAR\\".$province."\\".$municipality;
        
        // $path = public_path($path);
    
        $path = "C:\Apache24\/vhost\/rcef_unique_checker\/public\/rcef_id_generator\/public\/FLSAR\/".$season."\/".$province."\/".$municipality;
        
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
                        $path = "https://rcef-checker.philrice.gov.ph\/public\/rcef_id_generator\/public\/FLSAR";
          
    
                        $path .= '/'.$season.'/'.$province.'/'.$municipality.'/'.$file;
                    }
    
                   
    
                 $return_arr[$key]["name"]= $file;
                 $return_arr[$key]["path"]='<a class="btn btn-sm btn-success" onclick="window.open('."'".$path."'".', '."'".'_blank'."'".');" '.$disable.' >Download</a>';
                
                }
    
    
    
    
            }
            return json_encode($return_arr);
       }else{
        return json_encode("false");
       }
    
    }


    public function pageClose(){
        $mss = "TEMPORARY CLOSE";

        return view('utility.pageClosed')
            ->with("mss", $mss);
    }


    public function ebinhiIndex()
    {

        if(Auth::user()->roles->first()->name != "rcef-programmer"){
            $mss = "Under Development";
                return view("utility.pageClosed")
            ->with("mss",$mss);
        }
           $provinces_list =  DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified') 
                                    ->orderBy('province_name','ASC')
                                    ->groupBy('province_name')
                                    ->get();
             return view('FarGeneration.ebinhi')
             ->with(compact('provinces_list')); 
    }

     public function ebinhi_get_municipalities(Request $request)
    {
        $municipalities_list =  DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                                ->where('province_name', $request->province)
                                ->orderBy('municipality_name', 'asc')
                                ->groupBy('municipality_name')
                                ->get();

        echo json_encode($municipalities_list);
    }

      public function ebinhi_get_dop(Request $request)
    {
        // dd($request->all());
        if($request->brgy == "all"){
            $brgy = "%";
        }else{
            $brgy = $request->brgy;
        }

        $brgy_list =  DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries as a')
                                ->join($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified as b','a.paymaya_code','=','b.rcef_id')
                                ->where('a.paymaya_code', '!=', "")
                                ->where('a.province', $request->province)
                                ->where('a.municipality', $request->municipality)
                                ->where('b.barangay_code', "LIKE", "%".$brgy."%")
                                ->orderBy('a.drop_off_point', 'asc')
                                ->groupBy('a.drop_off_point')
                                ->get();

        // dd($brgy_list,$request->province,$request->municipality,$brgy);
        echo json_encode($brgy_list);

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



     public function ebinhi_get_brgy(Request $request)
    {
        $lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("municipality", $request->municipality)
            ->where("province", $request->province)
            ->first();

            $prv_code = "";
            if($lib_prv != null) {
                $prv_code = $lib_prv->prv;
            }



        $dop_list =  DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
        ->select('barangay_code')
                                ->where('farm_addr_prv', $request->province)
                                ->where('farm_addr_mun', $request->municipality)
                                ->orderBy('barangay_code', 'asc')
                                ->groupBy('barangay_code')
                                ->get();
        $brgy_array = array();
        //    dd($dop_list);
            foreach($dop_list as $dp){
                
                
                $brgy_name = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_geocodes')
                    ->where("geocode_brgy", $dp->barangay_code) 
                    ->value("name");

                if($brgy_name == null){
                    $brgy_name = "N/A";
                }

                array_push($brgy_array, array(
                    "code" => $dp->barangay_code,
                    "barangay" => $brgy_name
                ));


            }
        echo json_encode($brgy_array);
    }

  
    public function makeFarPreReg($province, $municipality, $brgy){
  
       if($brgy == "all"){$brgy="%";}

        if($municipality == "RIZAL (LIWAN)")$municipality="RIZAL";

        $province = str_replace(' (HUC)','',$province);
       $prv_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where('province', $province)
            ->where("municipality", $municipality)
            ->first();


        $array_week = array(
            "1st Week" => "01",
            "2nd Week" => "02",
            "3rd Week" => "03",
            "4th Week" => "04",
        );

        

        $prv_claim_pattern = $prv_data->regCode."-".$prv_data->provCode."-".$prv_data->munCode;

        


        $list =DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select("rsbsa_control_number as rsbsa_control_no", "lname as lastName", "fname as firstName", "midname as midName", "farmer_declared_area as final_area","barangay_code",
            "farmer_declared_area as area_to_be_planted", DB::raw("CEIL(farmer_declared_area *2) as no_of_bags"), DB::raw("IF(crop_establishment='direct_seeding', 'D', 'T') as crop_est"),"sowing_month", "sowing_week",DB::raw("SUBSTRING(yield_seed_type,1,1) as yield_seed_type"),DB::raw("LEFT(`yield_seed_name`, LOCATE('(', `yield_seed_name`) - 1) as yield_seed_name"),"yield_no_bags","yield_weight_bags","yield_area", DB::raw("CONCAT('1') as is_prereg"), "varietyPref", "claiming_prv"  )
            // ->where("province_name", $province)
            // ->where("municipality_name", $municipality)
            ->where("claiming_prv", $prv_claim_pattern)
            ->where("barangay_code", "LIKE", $brgy)
            // ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_number,12))'), 'ASC') 
            ->orderBy("claiming_prv")
            ->orderBy("lname")
            ->orderBy("fname")
            ->orderBy("midname")
            ->get();
        // dd($list);
        if(count($list)>0){    

            $list = json_decode(json_encode($list), true); 
            // dd($list);
              foreach ($list as $key => $value) {
                        
                    $geo = $value["barangay_code"];
                    
                    $brgy= DB::connection('mysql')->table('lib_geocodes')
                    ->where('geocode_brgy', $geo)
                    ->value('name');
                        $list[$key]["brgy"]= $brgy;
                        $list[$key]["no_of_parcels"]= "1";
                        $list[$key]["sowing_date"] = date("m",strtotime($value["sowing_month"]))."/".$array_week[$value["sowing_week"]];



                }

                $title = "Prereg_FAR".strtoupper($municipality);
                $pdf_name = "Prereg_FAR".strtoupper($municipality)."_".date("Y-m-d").".pdf";
            
                $list = json_decode(json_encode($list), true); 
                // dd($list);
                
                    $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_ws24', 
                    ['list' => $list, 'region_code' => $prv_data->regCode, 
                    "province_code" => $prv_data->provCode, "municipality_code" => $prv_data->munCode,
                    "title" => $title, "province"=>$province, "municipality"=> $municipality, "mark" => "unmark"])
                    ->setPaper('LEGAL', 'landscape');    
                
                // dd($pdf);
                return $pdf->stream($pdf_name); 

        }else{
            return json_encode("No List Found on schedule");
        }             

    }




    public function makeEBinhiFAR($province_name, $municipality_name, $brgy, $dop,$skip, $take, $size){
      //  dd($municipality_name);
       // $customPaper = array(0,0,612,937);

            $from_ex = explode("-", $skip);
            $from_date = date($from_ex[2]."-".$from_ex[0]."-".$from_ex[1]);
            $to_ex = explode("-", $take);
            $to_date = date($to_ex[2]."-".$to_ex[0]."-".$to_ex[1]);
            
            if($brgy == "all"){$brgy="%";}



        $list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                    ->select('firstname as firstName', 'lastname as lastName', 'middname as midName', 'rsbsa_control_no', 'area as crop_area', 'bags', 'barangay', 'lastname', 'firstname', 'middname', 'sex')
                    ->where('paymaya_code' , '!=', '')
                    ->where('province', $province_name)
                    ->where('municipality', $municipality_name)
                    ->where('barangay', "LIKE", "%".$brgy."%")
                    ->where("drop_off_point", "LIKE", "%".$dop."%")
                    ->where('is_active', 1)
                    ->whereBetween(DB::raw("str_to_date(schedule_start, '%Y-%m-%d')"), [$from_date, $to_date])
                    ->whereBetween(DB::raw("str_to_date(schedule_end, '%Y-%m-%d')"), [$from_date, $to_date])
                    //->where("is_printed", "5")
                    ->orderBy('barangay')
                   // ->orderBy('rsbsa_control_no', 'ASC')
                    ->orderBy('lastname', 'ASC')
                    ->orderBy('firstname', 'ASC')
                    ->orderBy('middname', 'ASC')
                    ->groupBy('rsbsa_control_no')
                    ->groupBy('lastname')
                    ->groupBy('firstname')
                    ->groupBy('middname')
              
                    //->skip($skip)
                    //->take($take) 
                    ->get();
                   // dd($list);
        if(count($list)>0){    
                $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();

                //dd($municipal_details);

            //$region_name = $municipal_details->;
            $region_code = substr($municipal_details->prv,0,2);
            $province_code = substr($municipal_details->prv,2,2);
            $municipality_code = substr($municipal_details->prv,4,2);

            $list = json_decode(json_encode($list), true); 
            // dd($list);
              foreach ($list as $key => $value) {
                                $georeg=substr($value['rsbsa_control_no'], 0,2) ;                              
                                $geoprov=substr($value['rsbsa_control_no'], 3,2);
                                $geomuni=substr($value['rsbsa_control_no'], 6,2);
                                $geobrgy=substr($value['rsbsa_control_no'], 9,3);

                                if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                                    $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                                }else{
                                    $geo = '';
                                }
                                $brgy= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $geo)
                                ->value('name');
                                  $list[$key]["brgy"]= $brgy;
                                
                          } 
                      

             if($size == "A3"){
                $title = "eBinhi_FAR_".strtoupper($municipality_name);
                $pdf_name = "eBinhi_FAR_".strtoupper($municipality_name)."_".date("Y-m-d").".pdf";
             }elseif($size == "ext"){
                $title = "eBinhi_FAR_ext".strtoupper($municipality_name);
                $pdf_name = "eBinhi_FAR_ext".strtoupper($municipality_name)."_".date("Y-m-d").".pdf";
             }else{
                return "Size Unknown";
             }
                            
             // dd($list);

                $path = public_path('flsar\\_eBinhi_FAR\\');
            
            if(!is_dir($path)){
                mkdir($path);
            }
                 $path = public_path('flsar\\_eBinhi_FAR\\'.$pdf_name);
       


                 $list = json_decode(json_encode($list), true); 
           
                    if($size == "A3"){
                // $pdf = PDFTIM::loadView('paymaya.reports.list_home_a3', 
                //     ['list' => $list, 
                //     "region_code" => $region_code, "province_code" => $province_code, "dop" => $dop,
                //     "municipality_code" =>$municipality_code, "province" => $province_name, "municipality"=> $municipality_name
                //     ,"title" => $title])
                //     ->setPaper("A3", 'landscape');     
                    $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_v2', 
                    ['list' => $list, 'region_code' => $region_code, 
                    "province_code" => $province_code, "municipality_code" => $municipality_code,
                    "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => "unmark"])
                    ->setPaper('LEGAL', 'landscape');    
                }elseif($size == "ext"){
                    $pdf = PDFTIM::loadView('paymaya.reports.list_home_extension', 
                    ['list' => $list, 
                    "region_code" => $region_code, "province_code" => $province_code, "dop" => $dop,
                    "municipality_code" =>$municipality_code, "province_name" => $province_name, "municipality_name"=> $municipality_name
                    ,"title" => $title])
                    ->setPaper("Legal", 'landscape'); 
                }else{
                    return "Size Unknown";
                }

                  
                




                //dd($path);
                $save = $pdf->save($path);
                return $pdf->stream($pdf_name); 






        }else{
            return json_encode("No List Found on schedule");
        }             
    }
    


        public function indexPreReg(){
            if(Auth::user()->roles->first()->name != "rcef-programmer"){
                $mss = "Under Development";
                    return view("utility.pageClosed")
                ->with("mss",$mss);
            }
                    // $pre_reg_province = array("PAMPANGA");

                    $provinces_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    // ->whereIn('province',$pre_reg_province)
                    ->groupBy('province')
                    ->orderBy('prv', 'ASC')
                    ->get();
     
    
                    return view('FarGeneration.pre_registration')
                        ->with(compact('provinces_list', $provinces_list));
    
    
    
            
        }

        public function get_brgy_preReg($municipality){

            
            $prvdb = $GLOBALS['season_prefix']."prv_".substr($municipality, 0,4);

            $rsbsa_pattern = substr($municipality,0,2)."-".substr($municipality,2,2)."-".substr($municipality,4,2);
         

                $data_brgy = DB::table($prvdb.".pre_registration")
                    ->select(DB::raw('REPLACE(SUBSTRING(rsbsa_control_no, 1, 12),"-","")'))
                    ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                    ->groupBy(DB::raw("SUBSTRING(rsbsa_control_no, 1, 12)"))
                    ->get();
            
                $data_brgy = json_decode(json_encode($data_brgy),true);

            $brgy_list= DB::connection('mysql')->table('lib_geocodes')
            ->whereIn('geocode_brgy', $data_brgy)
            ->get();

        echo json_encode($brgy_list);
    
        }


        //VALIDATED DS
        public function indexValidatedData()
        {
            if(Auth::user()->roles->first()->name != "rcef-programmer"){
                $mss = "Under Development";
                    return view("utility.pageClosed")
                ->with("mss",$mss);
            }
               $provinces_list = DB::table($GLOBALS['season_prefix'].'rcep_reports_view.rcef_provinces')
                ->groupBy('province')
                ->orderBy('region_sort', 'ASC')
                ->get();
                return view('FarGeneration.vd')
                    ->with(compact('provinces_list', $provinces_list));
        }


        public function include_FAR_data($api_key, $type, $prv_code){
            

            if($api_key == "AddMeOnFARPleas3"){
                $prv_db = substr($prv_code, 0,4);

                $allowed_far = DB::table($GLOBALS['season_prefix']."db_temp.allowed_far")
                ->where("prv", $prv_db)
                ->first();
                
                if($allowed_far == null){

                    try {
                        $sql = "CREATE TABLE ".$GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final LIKE ".$GLOBALS['last_season_prefix']."prv_".$prv_db.".farmer_information_final";

                        // return $sql;
                        DB::statement($sql);
   
                        $sql = "CREATE TABLE ".$GLOBALS['season_prefix']."prv_".$prv_db.".new_released LIKE ".$GLOBALS['last_season_prefix']."prv_".$prv_db.".new_released";
                        DB::statement($sql);
   
                        DB::table($GLOBALS['season_prefix']."db_temp.allowed_far")
                           ->insert([
                               "prv" => $prv_db
                           ]);
                    } catch (\Throwable $th) {
                        return $th->getMessage();
                    }

                    

                }


                if($type == "municipal"){
                    $claiming_prv = substr($prv_code, 0,2)."-".substr($prv_code, 2,2)."-".substr($prv_code, 4,2);

                    $check_first = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
                        ->where("claiming_prv", "LIKE", $claiming_prv."%")
                        ->first();
                    if($check_first == null){
                        $sql = "INSERT INTO ".$GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final SELECT * from ".$GLOBALS['last_season_prefix']."prv_".$prv_db.".farmer_information_final where claiming_prv like '".$claiming_prv."%' ";
                        DB::statement($sql);
                    }else{
                        return "ALREADY EXISTING";
                    }


                    
  
                }elseif($type == "provincial") {
                    $claiming_prv = substr($prv_code, 0,2)."-".substr($prv_code, 2,2);

                    $check_first = DB::table($GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final")
                    ->where("claiming_prv", "LIKE", $claiming_prv."%")
                    ->first();
                    if($check_first == null){
                        $sql = "INSERT INTO ".$GLOBALS['season_prefix']."prv_".$prv_db.".farmer_information_final SELECT * from ".$GLOBALS['last_season_prefix']."prv_".$prv_db.".farmer_information_final where claiming_prv like '".$claiming_prv."%' ";
                        DB::statement($sql);
      
                    
                    }else{
                        return "ALREADY EXISTING";
                    }

                    


                }


                return "success";


            }else{
                return "addmeonfarplease_api";
            }


        }


	
	   //<< PREVIOUS SEASON FAR DATA >>
         public function indexPs()
        {

            // if(Auth::user()->roles->first()->name != "rcef-programmer"){
            //     $mss = "Under Development";
            //         return view("utility.pageClosed")
            //     ->with("mss",$mss);
            // }
                // $arr_station = array("Negros", "Los Banos", "Central Experiment Station", "Agusan", "Batac", "Midsayap", "Bicol", "Isabela");
                // $allowed_stations = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_station")
                //     ->select("province")
                //     ->whereIn("station", $arr_station )
                //     ->groupby("province") 
                //     ->get();
                // $allowed_stations = json_decode(json_encode($allowed_stations), true);
                

                // $allowed_array = DB::table($GLOBALS['season_prefix']."db_temp.allowed_far")
                //     ->select("prv")
                //     ->get();

                // $allowed_array = json_decode(json_encode($allowed_array), true);
                // // $allowed_array = array("0973", "1247", "1263", "1280", "1538");
                
                if(Auth::user()->roles->first()->name == "branch-it"){
                    $provinces_list = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->where('stationID',Auth::user()->stationId)
                    ->groupBy('province')
                    ->orderBy('province', 'ASC')
                    ->get();
                    // dd($provinces_list);
                }
                else{
                    $provinces_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                        // ->whereIn("province", $allowed_stations)
                    //    ->whereIn("prv_code", $allowed_array)
                        // ->whereIn('regCode_int',[3,6,9,12,15])
                        ->where('province','<>','Programmer Province')
                        ->groupBy('province')
                        ->orderBy('region_sort', 'ASC')
                        ->get();
                }
                

                // dd(Auth::user()->username);
                // 1686585600 1686153600
                // $launch_time = strtotime("2023-06-13");
                
                // $current  = strtotime(date("Y-m-d"));
               
                // if($current  < $launch_time){
                //     if(Auth::user()->username == "jcartienda"  )
                //     {
                        
                //     }else{
                     
                //          $mss = "Temporary Close Development on going for brgy parcelary";
                //          return view('utility.pageClosed',compact("mss"));
                //     }

                // }

                // if(Auth::user()->roles->first()->name == "rcef-programmer"){
                
                
                // }else{
                //     $mss = "Temporary Close Uploading for June 2023 List is now on going";
                //     return view('utility.pageClosed',compact("mss"));
                // }


                







                return view('FarGeneration.ps')
                    ->with(compact('provinces_list', $provinces_list))
                    // ->with("stations", $arr_station)
                    ;



        }

	    public function get_brgy($municipality)
            {


                $brgy_list= DB::connection('mysql')->table('lib_geocodes')
                    ->where('geocode_municipality', $municipality)
                    ->get();

                echo json_encode($brgy_list);
            }
		
		
        public function get_municipalitiesPreReg($province){
            $municipalities_list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->where('province_name', $province)
                    ->where('isPrereg', 1)
                    ->orderBy('municipality_name', 'ASC')
                    ->groupBy('municipality_name')
                    ->first();           
    
            
            echo json_encode($municipalities_list);
        
        }

        public function get_report_beneficiaryPreReg($province, $municipality, $brgy){
    
                $db = $GLOBALS['season_prefix']."prv_".substr($municipality, 0, 4);
              
                if($brgy != "all"){
                $rsbsa_pattern = substr($brgy, 0,2)."-".substr($brgy, 2,2)."-".substr($brgy, 4,2)."-".substr($brgy, 6,3);
              }else{
                $rsbsa_pattern = substr($municipality, 0,2)."-".substr($municipality, 2,2)."-".substr($municipality, 4,2);
              }


                $count = DB::table($db.".pre_registration")
                    ->where("rsbsa_control_no","LIKE",$rsbsa_pattern."%")
                    ->count("auto_id");
                    

            echo json_encode($count);
        }


        public function makePdfFAR_pre_reg($province_name, $municipality_name, $brgy , $rowFrom, $rowTo, $size){
            
			//save pdf to directory
            if($brgy=="all"){
                $brgy_title = "";
            }else{
                $brgy_title= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $brgy)
                                ->value('name');
            }
            dd("AAAA");

            ini_set('memory_limit', '-1');

			 $beneficiary = new FarGeneration();
                $maxRow = $beneficiary->countBeneficiaryPs($province_name, $municipality_name, $brgy);
				
			if($rowFrom<=1){
				$skip = 0;
				$totalRow = $rowTo - $skip;
			}elseif($rowFrom==$rowTo){
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip + 1;	
			}
			else{
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip;			
			}
			  $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
                
		
			if(!isset($municipal_details)){
				return 'No Listing Available';
			}
				

			$region_name = $municipal_details->region;
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

              $list =   app()->call('App\Http\Controllers\APIController@getFARDetailsPreReg',[
                        'province_name' =>$province_name,
                        'municipality_name'=>$municipality_name,
                        'brgy' => $brgy,
                        'skip'=>$skip,
                        'take'=>$totalRow,

                    ]);
				
	

              if($list=="No Listing Available"){
                return 'No Listing Available';
              }
           
                          foreach ($list as $key => $value) {
								
                                $georeg=substr($value['rsbsa_control_no'], 0,2) ;                              
                                $geoprov=substr($value['rsbsa_control_no'], 3,2);
                                $geomuni=substr($value['rsbsa_control_no'], 6,2);
                                $geobrgy=substr($value['rsbsa_control_no'], 9,3);

                                if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                                    $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                                }else{
                                    $geo = '';
                                }

									
                                $brgy= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $geo)
                                ->value('name');
                                 $list[$key]['brgy'] = $brgy;


                                 if($size == "cross" || $size == "ext"){
                                    $other_info = DB::table($GLOBALS['season_prefix']."prv_".$region_code.$province_code.".other_info_processed")
                                        ->where("farmer_id", $value['farmer_id'])
                                        ->where("rsbsa_control_no", $value['rsbsa_control_no'])
                                        ->first(); 
                                    if(count($other_info)>0){
                                        $list[$key]["tel_number"] = $other_info->phone;
                                        $list[$key]["birthdate"] = $other_info->birthdate;
                                        $list[$key]["mother_lname"] = $other_info->mother_lname;
                                        $list[$key]["mother_fname"] = $other_info->mother_fname;
                                        $list[$key]["mother_mname"] = $other_info->mother_mname;
                                    }else{
                                        $list[$key]["tel_number"] = "";
                                        $list[$key]["birthdate"] = "0000-00-00";
                                        $list[$key]["mother_lname"] = "";
                                        $list[$key]["mother_fname"] = "";
                                        $list[$key]["mother_mname"] = "";
                                    }



                                 }







                          }
        

                    
				
			$title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
			//$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
			$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";

            if($size=="A3"){
                $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".strtoupper($brgy_title)."_A3.pdf"; 
            }



            $path = public_path('flsar\\' . $pdf_name);
//SAVE PDF FILE
            if($totalRow <> $maxRow ){
               $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
                
                if($size=="A3"){
                $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".strtoupper($brgy_title)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow."_A3.pdf"; 
                }



            //    //CREATE DIRECTORY IF NOT YET EXIST
            //     $path = public_path('flsar\\_FLSAR_BATCH\\');
				
			// 	if(!is_dir($path)){
            //         mkdir($path);
            //     }

            //     $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name);
            //     if(!is_dir($path)){
            //         mkdir($path);
            //     }

            //      $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name);
            //     if(!is_dir($path)){
            //         mkdir($path);
            //     }

            //      $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name.'\\'.$municipality_name);
            //     if(!is_dir($path)){
            //         mkdir($path);
            //     }

            //    $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name.'\\'.$municipality_name.'\\'.$pdf_name);
            }


            if($size=="LEGAL"){
                 $pdf = PDFTIM::loadView('farmer.preList.list_home_updated', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title])
                ->setPaper('Legal', 'landscape');   
            }elseif($size=="A3"){
                $pdf = PDFTIM::loadView('farmer.preList.list_pre_reg_a3', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="cross"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_cross', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size=="ext"){
                $pdf = PDFTIM::loadView('farmer.preList.list_pre_reg_extension', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size == "V4"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_a3_v4', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="ext_v43"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_extension_v43', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }
               

			
               
            //dd($path);
             //$save = $pdf->save($path);
            return $pdf->stream($pdf_name);		 
	
        }





        public function get_municipalitiesPs($province)
    {

            $municipalities_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province)
                ->orderBy('municipality', 'ASC')
                ->groupBy('municipality')
                ->get();           

        echo json_encode($municipalities_list);
    }


    public function get_report_beneficiaryVd($province, $municipality, $brgy){
        $beneficiary = new FarGeneration();
        $beneficiary_count = $beneficiary->countBeneficiaryValidatedData($province, $municipality, $brgy);
        echo json_encode($beneficiary_count);
    }


      
    public function get_report_beneficiaryPs($province, $municipality, $brgy, $is_non){
                $beneficiary = new FarGeneration();
                $beneficiary_count = $beneficiary->countBeneficiaryPs($province, $municipality, $brgy, $is_non);
                echo json_encode($beneficiary_count);
        }

    public function makeExcelFAR($province_name, $municipality_name){     
    
        $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('province', $province_name)
            ->first();

        $brgy_list = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
            ->get();
   

            $region_code = substr($lib_prv->prv,0,2);
            $province_code = substr($lib_prv->prv,2,2);
            $municipality_code = substr($lib_prv->prv,4,2);
            

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
            $rsbsa_to_prv = str_replace("-","",$rsba_pattern);

			

            $list = DB::table($GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code.".farmer_information_final")
                ->select("rcef_id", "rsbsa_control_no", "lastName", "firstName", "midName", "extName", DB::raw("CONCAT('".$province_name."')"), DB::raw("CONCAT('".$municipality_name."')"))
                ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                
                ->where("final_area", ">=", 0.1)
                ->where("rcef_id", "!=", "")
                ->where("to_prv_code", "=", null)
                ->orWhere("to_prv_code", "LIKE", $rsbsa_to_prv)
					
				->where("final_area", ">=", 0.1)
				->where("rcef_id", "!=", "")
                 ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
                ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')   
                ->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
                ->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
                // ->limit(1)
                ->get();

                foreach($list as $data){
                    $brgy_code = str_replace("-","",substr($data->rsbsa_control_no,0,12));
                    $brgy_info = $this->search_to_array($brgy_list, "geocode_brgy", $brgy_code);
                        if(isset($brgy_info[0])){
                            $brgy_name = $brgy_info[0]["name"];
                        }else{
                            $brgy_name = "NA";
                        }
                        $data->brgy_name = $brgy_name;

                }
               


                $excel_data = json_decode(json_encode($list), true); //convert collection to associative array to be converted to excel
                 Excel::create("FAR_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                    $excel->sheet("LIST", function($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                    }); 
                })->download('xlsx');




    }



    public function makePdfFAR_blank($size,$page_count){
            //save pdf to directory

            ini_set('memory_limit', '-1');
              
                
            $title = "FLSAR_BLANK";
            //$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
            $pdf_name ="FLSAR_BLANK.pdf";

            if($size == "a3"){
                 $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_v2_blank', 
                ['page_count' => $page_count,'region_code' => "",
                "province_code" => "", "municipality_code" => "",
                "title" => $title, "province"=>"", "municipality"=> ""])
                ->setPaper('LEGAL', 'landscape'); 
            }elseif($size == "ext"){
                 $pdf = PDFTIM::loadView('farmer.preList.list_home_extension_blank', 
                ['page_count' => $page_count,'region_code' => "",
                "province_code" => "", "municipality_code" => "",
                "title" => $title, "province"=>"", "municipality"=> ""])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size == "v4"){

            }
               
            

            
               
            //dd($path);
            // $save = $pdf->save($path);
            return $pdf->stream($pdf_name);      
    }
    

    public function makeEbinhi_FAR($size,$province_name, $municipality_name ) {
            $maxRow = DB::table("temp_db.ebinhi_south_cotabato")
                ->where("municipality",$municipality_name)
                ->where("province",$province_name)
                ->count("id");

          $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('province', $province_name)
            ->where('municipality', $municipality_name)
            ->first();
            
    
        if(!isset($municipal_details)){
            return 'No Listing Available';
        }
        
        $region_name = $municipal_details->region;
        $region_code = substr($municipal_details->prv,0,2);
        $province_code = substr($municipal_details->prv,2,2);
        $municipality_code = substr($municipal_details->prv,4,2);

          $list =  DB::table("temp_db.ebinhi_south_cotabato")
                ->where("municipality",$municipality_name)
                ->where("province",$province_name)
                ->orderBy("brgy_name")
                ->orderBy("lastName")
                ->orderBy("firstName")
                ->orderBy("midName")
                
                
                ->get();
            
          if(count($list) <= 0){
            return 'No Listing Available';
          }
          $list = json_decode(json_encode($list),true);
            foreach ($list as $key => $value) {
          
                $georeg=substr($value['rsbsa_control_no'], 0,2) ;                              
                $geoprov=substr($value['rsbsa_control_no'], 3,2);
                $geomuni=substr($value['rsbsa_control_no'], 6,2);
                $geobrgy=substr($value['rsbsa_control_no'], 9,3);

                if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                    $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                }else{
                    $geo = '';
                }

                    
                // $brgy= DB::connection('mysql')->table('lib_geocodes')
                // ->where('geocode_brgy', $geo)
                // ->value('name');

                   $brgy = $value['brgy_name']; 
                    $list[$key]['brgy'] = $brgy;
            }
        
            
        $title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
        //$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
        $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";





    
 
      
        if($size=="A3"){
            $pdf = PDFTIM::loadView('farmer.preList.list_home_a3', 
            ['list' => $list, 'region_code' => $region_code, 
            "province_code" => $province_code, "municipality_code" => $municipality_code,
            "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => "0"])
            ->setPaper('A3', 'landscape'); 
        }elseif($size=="ext"){
            $pdf = PDFTIM::loadView('farmer.preList.list_home_extension', 
            ['list' => $list, 'region_code' => $region_code, 
            "province_code" => $province_code, "municipality_code" => $municipality_code,
            "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
            ->setPaper('Legal', 'landscape'); 
        }
        // $save = $pdf->save($path);
        return $pdf->stream($pdf_name);		 
}



    public function genExcelFar($type, $province,$municipality){
   
        $prv_code = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $province)
            ->where("municipality", $municipality)
            ->first();



            if($prv_code != null){
                $rsbsa_pattern = $prv_code->regCode."-".$prv_code->provCode."-".$prv_code->munCode;
                if($type == "identify"){
                    $list = DB::table($GLOBALS['season_prefix']."prv_".$prv_code->prv_code.".farmer_information_final")
                    ->select("rsbsa_control_no","rcef_id","lastName", "firstName", "midName", "sex", "birthdate" , "final_area", "tel_no", DB::raw("CONCAT('".$province."') as province"),DB::raw("CONCAT('".$municipality."') as municipality"),"brgy_name"
                    ,DB::raw('IF(data_source="FFRS", IF(rsms_actual_area>0,"BOTH FFRS AND RSMS", "FFRS ONLY"), "RSMS ONLY") as data_source')
                    )
                    ->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                    ->orderBy("brgy_name")
                    ->orderBy("lastName")
                    ->orderBy("firstName")
                    ->orderBy("midName")
                    ->get();

                    


                 
                }else{

                }
                $excel_data = json_decode(json_encode($list),true);

                
                return Excel::create($province."_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                    $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                        $sheet->freezeFirstRow();
                        
                        $sheet->setHeight(1, 30);
                        $sheet->cells('A1:M1', function ($cells) {
                            $cells->setBackground('#92D050');
                            $cells->setAlignment('center');
                            $cells->setValignment('center');
                        });
                        $sheet->setBorder('A1:M1', 'thin');
                    });
                })->download('xlsx');
                


            }


        


    }


    public function makePdfFARValidatedData($mark,$province_name, $municipality_name, $brgy , $rowFrom, $rowTo, $size) {
        
			//save pdf to directory
            if($brgy=="all"){
                $brgy_title = "";
            }else{
                $brgy_title= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $brgy)
                                ->value('name');
            }
            ini_set('memory_limit', '-1');

			 $beneficiary = new FarGeneration();
                $maxRow = $beneficiary->countBeneficiaryValidatedData($province_name, $municipality_name, $brgy);
			
			if($rowFrom<=1){
				$skip = 0;
				$totalRow = $rowTo - $skip;
			}elseif($rowFrom==$rowTo){
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip + 1;	
			}
			else{
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip;			
			}
			  $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
                
		
			if(!isset($municipal_details)){
				return 'No Listing Available';
			}
			
			$region_name = $municipal_details->region;
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

              $list =   app()->call('App\Http\Controllers\APIController@getFARDetailsValidatedData',[
                        'province_name' =>$province_name,
                        'municipality_name'=>$municipality_name,
                        'brgy' => $brgy,
                        'skip'=>$skip,
                        'take'=>$totalRow,

                    ]);
				
              

              if($list=="No Listing Available"){
                return 'No Listing Available';
              }
           
                          foreach ($list as $key => $value) {
								
                                $georeg=substr($value['rsbsa_control_no'], 0,2) ;                              
                                $geoprov=substr($value['rsbsa_control_no'], 3,2);
                                $geomuni=substr($value['rsbsa_control_no'], 6,2);
                                $geobrgy=substr($value['rsbsa_control_no'], 9,3);

                                if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                                    $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                                }else{
                                    $geo = '';
                                }

									
                                $brgy= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $geo)
                                ->value('name');
                                 $list[$key]['brgy'] = $brgy;
                          }
			
				
			$title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
			//$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
			$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";

            if($size=="A3"){
                $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".strtoupper($brgy_title)."_A3.pdf"; 
            }



            $path = public_path('flsar\\' . $pdf_name);
//SAVE PDF FILE
            if($totalRow <> $maxRow ){
               $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
                
                if($size=="A3"){
                $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".strtoupper($brgy_title)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow."_A3.pdf"; 
                }
            }

          
            if($size=="LEGAL"){
                 $pdf = PDFTIM::loadView('farmer.preList.list_home_updated', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title])
                ->setPaper('Legal', 'landscape');   
            }elseif($size=="A3"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_a3', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark, "tag_val" => "yes"])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="cross"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_cross', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "tag_val" => "yes"])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size=="ext"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_extension', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "tag_val" => "yes"])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size == "V4"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_a3_v4', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="ext_v43"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_extension_v43', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }    
            // $save = $pdf->save($path);
            return $pdf->stream($pdf_name);		 
    }


    public function genReplacementFAR($province_name, $municipality_name, $brgy , $rowFrom, $rowTo){
        
			//save pdf to directory
           
            if($brgy=="all"){
                $brgy_title = "";
            }else{
                $brgy_title= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $brgy)
                                ->value('name');
            }


            ini_set('memory_limit', '-1');

			 $beneficiary = new FarGeneration();
                $maxRow = $beneficiary->countBeneficiaryReplacement($province_name, $municipality_name, $brgy);
        
			if($rowFrom<=1){
				$skip = 0;
				$totalRow = $rowTo - $skip;
			}elseif($rowFrom==$rowTo){
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip + 1;	
			}
			else{
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip;			
			}
			  $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
                
		
			if(!isset($municipal_details)){
				return 'No Listing Available';
			}
				

			$region_name = $municipal_details->region;
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

              $list =   app()->call('App\Http\Controllers\APIController@getFARDetailsReplacement',[
                        'province_name' =>$province_name,
                        'municipality_name'=>$municipality_name,
                        'brgy' => $brgy,
                        'skip'=>$skip,
                        'take'=>$totalRow,

                    ]);
				


              if($list=="No Listing Available"){
                return 'No Listing Available';
              }
           
                          foreach ($list as $key => $value) {
								
                                $georeg=substr($value['rsbsa_control_no'], 0,2) ;                              
                                $geoprov=substr($value['rsbsa_control_no'], 3,2);
                                $geomuni=substr($value['rsbsa_control_no'], 6,2);
                                $geobrgy=substr($value['rsbsa_control_no'], 9,3);

                                if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                                    $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                                }else{
                                    $geo = '';
                                }

									
                                $brgy= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $geo)
                                ->value('name');
                                 $list[$key]['brgy'] = $brgy;



                          }
			$title = $municipal_details->prv."_FLSAR_REPLACEMENT_".strtoupper($municipality_name);
			//$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
			$pdf_name = $municipal_details->prv."_FLSAR_REPLACEMENT_".strtoupper($municipality_name).".pdf";

          



            $path = public_path('flsar\\' . $pdf_name);
//SAVE PDF FILE
            if($totalRow <> $maxRow ){
               $pdf_name = $municipal_details->prv."_FLSAR_REPLACEMENT_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
                
             



               //CREATE DIRECTORY IF NOT YET EXIST
                $path = public_path('flsar\\_FLSAR_BATCH\\');
				
				if(!is_dir($path)){
                    mkdir($path);
                }

                $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

                 $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

                 $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name.'\\'.$municipality_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

               $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name.'\\'.$municipality_name.'\\'.$pdf_name);
            }

          
           
            $pdf = PDFTIM::loadView('farmer.preList.list_home_replacement', 
            ['list' => $list, 'region_code' => $region_code, 
            "province_code" => $province_code, "municipality_code" => $municipality_code,
            "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => "unmark"])
            ->setPaper('A3', 'landscape'); 
               

			
               
            //dd($path);
             $save = $pdf->save($path);
            return $pdf->stream($pdf_name);		 
	
    }

    



	public function makePdfFAR($mark,$province_name, $municipality_name, $brgy , $rowFrom, $rowTo, $size,$pre_reg, $is_transfer){
			//save pdf to directory

            // if($pre_reg == 1){
            //     if($municipality_name == "ALFONSO LISTA (POTIA)"){
            //             $municipality_name = 'ALFONSO LISTA';
            //     }
            // }

            $brgy_query = $brgy;
            if($brgy=="all"){
                $brgy_title = "";
            }else{
                $brgy_title= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $brgy)
                                ->value('name');
            }
    
            ini_set('memory_limit', '-1');

			 $beneficiary = new FarGeneration();
                $maxRow = $beneficiary->countBeneficiaryPs($province_name, $municipality_name, $brgy, $pre_reg);
				
			if($rowFrom<=1){
				$skip = 0;
				$totalRow = $rowTo - $skip;
			}elseif($rowFrom==$rowTo){
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip + 1;	
			}
			else{
				$skip = $rowFrom;
				$totalRow = $rowTo - $skip;			
			}
			  $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province_name)
                ->where('municipality', $municipality_name)
                ->first();
                
		
			if(!isset($municipal_details)){
				 return 'No Listing Available';
			}
				

			$region_name = $municipal_details->region;
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

              $list =   app()->call('App\Http\Controllers\APIController@getFARDetailsPS',[
                        'province_name' =>$province_name,
                        'municipality_name'=>$municipality_name,
                        'brgy' => $brgy,
                        'skip'=>$skip,
                        'take'=>$totalRow,
                        'pre_reg' => $pre_reg,
                        'is_transfer' => $is_transfer
                    ]);
				
	

              if(count($list) == 0){
                return 'No Listing Available';
              }
           
                    if($is_transfer == 2) {
                        foreach ($list as $key => $value) {
								
                            $geo = $value["claiming_brgy"];

                            if($geo == ""){
                                $list[$key]['brgy'] = "N/A";

                            }else{
                                if($brgy_query=="all"){
                                    $brgy= DB::connection('mysql')->table('lib_geocodes')
                                    ->where('geocode_brgy', $geo)
                                    ->value('name');
                                     $list[$key]['brgy'] = $brgy;
                                }else{
                                    $list[$key]['brgy'] = $brgy_title;
                                }
                               
                            }
                            
                            

                      }

                    }
                    else{

                        foreach ($list as $key => $value) {
								
                            $georeg=substr($value['rsbsa_control_no'], 0,2) ;                              
                            $geoprov=substr($value['rsbsa_control_no'], 3,2);
                            $geomuni=substr($value['rsbsa_control_no'], 6,2);
                            $geobrgy=substr($value['rsbsa_control_no'], 9,3);

                            if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                                $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                            }else{
                                $geo = '';
                            }

                            if($brgy_query=="all"){
                                $brgy= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $geo)
                                ->value('name');
                                 $list[$key]['brgy'] = $brgy;
                            }else{
                                $list[$key]['brgy'] = $brgy_title;
                            }
                           

                      }


                    }

                         



			$title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
			//$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
			$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";

            if($size=="A3"){
                $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".strtoupper($brgy_title)."_A3.pdf"; 
            }



            $path = public_path('flsar\\' . $pdf_name);
//SAVE PDF FILE
            if($totalRow <> $maxRow ){
               $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
                
                if($size=="A3"){
                $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".strtoupper($brgy_title)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow."_A3.pdf"; 
                }



               //CREATE DIRECTORY IF NOT YET EXIST
                $path = public_path('flsar\\_FLSAR_BATCH\\');
				
				if(!is_dir($path)){
                    mkdir($path);
                }

                $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

                 $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

                 $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name.'\\'.$municipality_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

               $path = public_path('flsar\\_FLSAR_BATCH\\'.$region_name.'\\'.$province_name.'\\'.$municipality_name.'\\'.$pdf_name);
            }

          
            if($size=="LEGAL"){
                 $pdf = PDFTIM::loadView('farmer.preList.list_home_updated', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title])
                ->setPaper('Legal', 'landscape');   
            }elseif($size=="A3_OLD"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_a3', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="LEGAL_WS23"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_ws23', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                ->setPaper('LEGAL', 'landscape');  
           }elseif($size=="A3"){
                if($is_transfer == 2){
                    // $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_v2_may', 
                    // ['list' => $list, 'region_code' => $region_code, 
                    // "province_code" => $province_code, "municipality_code" => $municipality_code,
                    // "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                    // ->setPaper('LEGAL', 'landscape');

              
                    if($pre_reg == 1){
                        $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_ws24_non', 
                        ['list' => $list, 'region_code' => $region_code, 
                        "province_code" => $province_code, "municipality_code" => $municipality_code,
                        "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                        ->setPaper('LEGAL', 'landscape');
    
                    }else{
                        $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_ws24', 
                        ['list' => $list, 'region_code' => $region_code, 
                        "province_code" => $province_code, "municipality_code" => $municipality_code,
                        "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                        ->setPaper('LEGAL', 'landscape');
    
                    }


                }else{
                    // $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_v2', 
                    // ['list' => $list, 'region_code' => $region_code, 
                    // "province_code" => $province_code, "municipality_code" => $municipality_code,
                    // "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                    // ->setPaper('LEGAL', 'landscape');

                    $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_ws24', 
                    ['list' => $list, 'region_code' => $region_code, 
                    "province_code" => $province_code, "municipality_code" => $municipality_code,
                    "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                    ->setPaper('LEGAL', 'landscape');
                }


        

           }elseif($size == "DS24"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_ws24', 
                        ['list' => $list, 'region_code' => $region_code, 
                        "province_code" => $province_code, "municipality_code" => $municipality_code,
                        "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                        ->setPaper('LEGAL', 'landscape');
           }elseif($size == "DS24_NON"){
            $pdf = PDFTIM::loadView('farmer.preList.list_home_legal_ws24_non', 
                    ['list' => $list, 'region_code' => $region_code, 
                    "province_code" => $province_code, "municipality_code" => $municipality_code,
                    "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                    ->setPaper('LEGAL', 'landscape');
       }

            elseif($size=="A3_ws23"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_a3_ws23', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name, "mark" => $mark])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="cross"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_cross', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size=="ext"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_extension', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }elseif($size == "V4"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_a3_v4', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('A3', 'landscape'); 
            }elseif($size=="ext_v43"){
                $pdf = PDFTIM::loadView('farmer.preList.list_home_extension_v43', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title, "province"=>$province_name, "municipality"=> $municipality_name])
                ->setPaper('Legal', 'landscape'); 
            }
               

			
               
            //dd($path);
             $save = $pdf->save($path);
            return $pdf->stream($pdf_name);		 
	}
	
	
		//<< PREVIOUS SEASON FAR DATA >>
	
	
    public function makePdfFAR_templated($mark,$province_name, $municipality_name, $brgy , $rowFrom, $rowTo, $size,$pre_reg, $is_transfer){
        


        $pdfContent = file_get_contents(asset("public/templates/template_FAR_DS24.pdf"));
        dd($pdfContent);
        // Load PDF content into Dompdf
        $dompdf = PDFTIM::loadHTML($pdfContent);
        
        // Manipulate PDF (e.g., add a new page)
        $dompdf->addPage();
        
        // Generate new PDF content
        $newPdfContent = $dompdf->output();
        
        // Write new PDF content to a file
        file_put_contents(asset("public/templates/template_FAR_DS24_new.pdf"), $newPdfContent);

    }


	
    public function index()
    {

        if(Auth::user()->roles->first()->name != "rcef-programmer"){
            $mss = "Under Development";
                return view("utility.pageClosed")
            ->with("mss",$mss);
        }
    	
        $provinces = new Provinces();
        // Get provinces assigned
        $provinces_list = $provinces->all_provinces();
        
        
        return view('FarGeneration.index')
        	 ->with(compact('provinces_list'));
    }


      public function search_municipalities($province)
    {
        // Get municipalities
        $municipalities = new Municipalities();
        $municipalities_list = $municipalities->search_municipalities($province);

        // Get region
        $region = DB::table('lib_regions as region')
        ->leftJoin('lib_provinces as province', 'province.regCode', '=', 'region.regCode')
        ->select('region.regDesc')
        ->where('province.provDesc', $province)
        ->first();

        $data = array(
            'municipalities' => $municipalities_list,
            'region' => $region->regDesc
        );

        echo json_encode($data);
    }

    

    	 public function get_municipalities($province)
    {
        $municipalities_list =  $municipalities = DB::table('lib_municipalities')
        ->select('*')
        ->where('provCode', $province)
        ->orderBy('citymunDesc', 'asc')
        ->get();
     
   
        $municipalities_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        ->select("prv as citymunCode", "municipality as citymunDesc")
        ->where("prv", 'like', $province.'%')
        ->orderBy("municipality")
        ->groupBy("municipality")
        ->get();

        //dd($province);

           echo json_encode($municipalities_list);

    }


    	public function get_region($province)
    	{
            
   			$region_name = DB::table('lib_provinces as prov')
                        ->leftJoin('lib_regions as reg', 'prov.regCode', '=', 'reg.regCode')
                        ->select('reg.regDesc')
                        ->where('prov.provCode', $province)
                        ->first();
    		
    		echo json_encode($region_name);
    	}

  

    	public function get_report_beneficiary($region, $province, $municipality, $checkbox){
            //dd("asdasdas");
    			$beneficiary = new FarGeneration();
    			$beneficiary_count = $beneficiary->count_beneficiary($region, $province, $municipality, $checkbox);
    		
    			echo json_encode($beneficiary_count);
    	}


    	public function generate_Provincemunicipality_serverSide($region_name, $province_name, $municipality_name, $rowFrom, $rowTo, $maxRow,$checkbox){
		//$file = new Filesystem;
		//$file->cleanDirectory('public/flsar');
		ini_set('memory_limit', '-1');
	

            $regionName = new FarGeneration();
            $regionArr = $regionName->changeRegionName($region_name);

          

		if($rowFrom<=1){
			$skip = 0;
			$totalRow = $rowTo - $skip;
		}elseif($rowFrom==$rowTo){
			$skip = $rowFrom;
			$totalRow = $rowTo - $skip + 1;	
		}
		else{
			$skip = $rowFrom;
			$totalRow = $rowTo - $skip;			
		}

		try{
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();

			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);
            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
            $rsbsa_menthod = 2; //1->check PRV 0->direct to table 2->get data fromprv
            $rsbsa_check = $checkbox;



                if($rsbsa_check){
                    if($rsbsa_menthod==1){ //method 1
                         $rsbsa_query = DB::table($GLOBALS['season_prefix'].'prv_'.$region_code.$province_code.'.farmer_profile')
                                                    ->select('rsbsa_control_no as pattern', DB::raw('substr(rsbsa_control_no, 1, 12) as foo'))
                                                    ->where('rsbsa_control_no', 'like', $rsba_pattern)
                                                    ->where('firstName','!=','""')
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


                         $rawsql = "SELECT DISTINCT(rsbsa_control_number), qr_code, farmer_fname, farmer_mname, farmer_lname, farmer_ext,sex, birthdate, tel_number,province, municipality, mother_fname, mother_mname, mother_lname, mother_ext, dist_area, actual_area, bags, seed_variety, date_released, farmer_id, released_by, date_generated from ".$database_name.".".$table_name." where farmer_fname !='' ";
                        foreach ($checkerArr as $key => $val) {
                                   
                                if($key==0){
                                   $rawsql .= " AND rsbsa_control_number like ".$val;
                                }else{
                                    $rawsql .= " OR rsbsa_control_number like ".$val;
                                }
                        } //FOREACH
                        
                        $rawsql .= "  order by rsbsa_control_number asc, farmer_fname asc, 
                                     farmer_lname asc, farmer_mname asc ";
                        $rawsql .= "LIMIT ".$totalRow." OFFSET ".$skip;
                        //dd($rawsql);
                         $list = DB::select(DB::raw($rawsql));
                        
                    }
                    elseif($rsbsa_menthod==0){ //method 0
                        $list = DB::table($database_name.".".$table_name)
                        ->select(DB::raw("DISTINCT(rsbsa_control_number), qr_code, farmer_fname, farmer_mname, farmer_lname, farmer_ext,sex, birthdate, tel_number,province, municipality, mother_fname, mother_mname, mother_lname, mother_ext, dist_area, actual_area, bags, seed_variety, date_released, farmer_id, released_by, date_generated"))
                        ->where('farmer_fname','!=','""') 
                        ->where('rsbsa_control_number','like', $rsba_pattern)
                        ->orderBy('rsbsa_control_number', 'ASC')
                        ->orderBy('farmer_fname', 'ASC')        
                        ->orderBy('farmer_lname', 'ASC')
                        ->orderBy('farmer_mname', 'ASC')   
                        ->skip($skip)
                        ->take($totalRow) 
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
                                                    ->where("farmer_profile.distributionID", "like", "R".$region_code."%")
                                                    ->orderBy(DB::raw("TRIM(LEFT(farmer_profile.rsbsa_control_no,12))"), 'ASC') 
                                                    ->orderBy(DB::raw('TRIM(farmer_profile.lastName)'), 'ASC')
                                                    ->orderBy(DB::raw('TRIM(farmer_profile.firstName)'), 'ASC')        
                                                    ->orderBy(DB::raw('TRIM(farmer_profile.midName)'), 'ASC')
                                                    ->skip($skip)
                                                    ->take($totalRow)
                                                    ->get();
                           // dd($list);


                    }  
                }
                else{
                        $list = DB::table($database_name.".".$table_name)
                        ->select(DB::raw("DISTINCT(rsbsa_control_number), qr_code, farmer_fname, farmer_mname, farmer_lname, farmer_ext,sex, birthdate, tel_number,province, municipality, mother_fname, mother_mname, mother_lname, mother_ext, dist_area, actual_area, bags, seed_variety, date_released, farmer_id, released_by, date_generated"))
                        ->orderBy('rsbsa_control_number', 'ASC')
                        ->orderBy('farmer_fname', 'ASC')        
                        ->orderBy('farmer_lname', 'ASC')
                        ->orderBy('farmer_mname', 'ASC')    
                        ->skip($skip)
                        ->take($totalRow)
                        ->get();  
                }



 $list = json_decode(json_encode($list), true);
                 foreach ($list as $key => $value) {
                               // dd($value);
                                $georeg=substr($value['rsbsa_control_number'], 0,2) ;                              
                                $geoprov=substr($value['rsbsa_control_number'], 3,2);
                                $geomuni=substr($value['rsbsa_control_number'], 6,2);
                                $geobrgy=substr($value['rsbsa_control_number'], 9,3);

                                if($georeg!='' AND $geoprov!='' AND $geomuni !='' AND $geobrgy!=''){
                                    $geo = $georeg.$geoprov.$geomuni.$geobrgy;
                                }else{
                                    $geo = '';
                                }

                                    
                                $brgy= DB::connection('mysql')->table('lib_geocodes')
                                ->where('geocode_brgy', $geo)
                                ->value('name');
                                 $list[$key]['brgy'] = $brgy;
                          }








            //dd(count($list));
			//dd($list);
           

			//save pdf to directory
			$title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
			//$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
			$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";

            $path = public_path('flsar_current\\' . $pdf_name);
//SAVE PDF FILE
            if($totalRow <> $maxRow ){
               $pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name)."_".$rowFrom."_to_".$rowTo."_OF_".$maxRow.".pdf";
               
               //CREATE DIRECTORY IF NOT YET EXIST
                $path = public_path('flsar_current\\_FLSAR_BATCH\\');
                if(!is_dir($path)){
                    mkdir($path);
                }

                $path = public_path('flsar_current\\_FLSAR_BATCH\\'.$regionArr[$region_name]);
                if(!is_dir($path)){
                    mkdir($path);
                }

                 $path = public_path('flsar_current\\_FLSAR_BATCH\\'.$regionArr[$region_name].'\\'.$province_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

                 $path = public_path('flsar_current\\_FLSAR_BATCH\\'.$regionArr[$region_name].'\\'.$province_name.'\\'.$municipality_name);
                if(!is_dir($path)){
                    mkdir($path);
                }

               $path = public_path('flsar_current\\_FLSAR_BATCH\\'.$regionArr[$region_name].'\\'.$province_name.'\\'.$municipality_name.'\\'.$pdf_name);
              
            }

            $pdf = PDFTIM::loadView('farmer.preList.list_home_updated', 
                ['list' => $list, 'region_code' => $region_code, 
                "province_code" => $province_code, "municipality_code" => $municipality_code,
                "title" => $title])
                ->setPaper('Legal', 'landscape');       
            //dd($path);
             $save = $pdf->save($path);

            return $pdf->stream($pdf_name);
				

		}catch(\Illuminate\Database\QueryException $ex){
            //return 'sql_error';
			dd($ex);
		}
	}

    function getRunningFLSAR($prv, $mun){
        $status = DB::table("temp_db._running_flsar")
            ->select("status")
            ->where("prv", $prv)
            ->where("mun", $mun)
            ->where("status", "running")
            ->first();

        $st = "stopped";
        if($status){
            $st = $status->status;
        }
        return $st;
    }

}


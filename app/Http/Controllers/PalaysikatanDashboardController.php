<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Support\Facades\Schema;
use Config;
use DB;
use Excel;
use Carbon\Carbon;

use App\HistoryMonitoring;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\utility;

use Session;
use Auth;
use Illuminate\Support\Facades\Hash;


class PalaysikatanDashboardController extends Controller
{
     public function __construct()
     {
         // database connections
         $this->geotag_con = 'geotag_db';
     }     

     public function cropStages(Request $request){ 
          $end_date =  Carbon::now();;
         $data= array();
          $farmer_info= DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info as a')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production as b', 'b.farmer_id_fk','=','a.fid')
          ->where('a.add_province',$request->province)
          ->where('a.add_municipality',$request->municipality)
          ->get();
          foreach ($farmer_info as  $farmer_data) {
                $start_date= $farmer_data->date_sown;
               
               if($start_date== "0000-00-00"){
                    continue;
               }
               if($farmer_data->variety_planted == ""){
                    continue;
               }
                $varieties =  explode(",",$farmer_data->variety_planted);
                foreach ($varieties as $variety) {
                    $variety_data =$variety;
                      $dateDiff = $this->dateDifference($start_date, $end_date); 
                       $variety= explode(" ", $variety);
                       
                       // $variety[count($variety)-1];
                     $seed_characteristics= DB::table('seed_seed.seed_characteristics')                   
                    ->where('variety','LIKE' ,'%'. $variety[count($variety)-1].'%')
                    ->first();
                    if($seed_characteristics->maturity < $dateDiff){

                         if(isset($seed_characteristics)){
                              array_push($data, array(
                                   "fname" =>  $farmer_data->f_firstName.' '. $farmer_data->f_lastName,
                                   "stage_name" =>  "Harvested",
                                   "variety" => $variety_data,
                                   "municipality" => $farmer_data->add_municipality,
                                   "date_sown" => $farmer_data->date_sown,
                                   "y" => $dateDiff,
                                   "maturity" =>  $seed_characteristics->maturity,
                                   "sliced" => true,
                                   )
                               );
                               
                         }
                         continue;
                    }
                   // return json_encode($seed_characteristics->maturity);
                   return $variety[count($variety)-1];
                    $seed_characteristics= DB::table('seed_seed.seed_characteristics as a')               
                   ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_crop_stages as b', 'b.harvesting','=','a.maturity')
                   ->select('b.*')                  
                   ->where('a.variety','LIKE' ,'%'.$variety[count($variety)-1].'%')
                   ->where('b.day_from','<=',$dateDiff)
                   ->where('b.day_to','>=',$dateDiff)
                   ->first();
                  
                  if(isset($seed_characteristics)){
                       array_push($data, array(
                            "fname" =>  $farmer_data->f_firstName.' '. $farmer_data->f_lastName,
                            "stage_name" =>  $seed_characteristics->stage_name,
                            "variety" => $farmer_data->variety_planted,
                            "municipality" => $farmer_data->add_municipality,
                            "date_sown" => $farmer_data->date_sown,
                            "y" => $dateDiff,
                            "maturity" =>  $seed_characteristics->harvesting,
                            "sliced" => true,
                            )
                        );
                  }
                }
                
               
               

          }
          return json_encode($data);
     }     
     function dateDifference($start_date, $end_date)
     {
         $diff = strtotime($start_date) - strtotime($end_date);          
         // 1 day = 24 hours 
         // 24 * 60 * 60 = 86400 seconds
         return ceil(abs($diff / 86400));
     }
    public function province_list(Request $request){
         $province_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->select("farmer_info.add_province", "lib_prv.provCode")
            ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.province", "=", "farmer_info.add_province")
            ->where("add_region", $request->region)
            ->groupBy("add_province")
            ->get();
            //dd($province_list);
        return json_encode($province_list);

    }


    public function municipal_list(Request $request){
     
         $municipal_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->select("farmer_info.add_municipality", "lib_prv.munCode", DB::raw("COUNT(farmer_info.add_municipality) as f_count"))
            ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", function($join){
                $join->on("lib_prv.municipality", "=", "farmer_info.add_municipality");
                $join->on("lib_prv.province", "=", "farmer_info.add_province");
                  
            })
            ->where("add_region", $request->region)
            ->where("add_province", $request->province)
            ->groupBy("add_municipality")
            ->get();
            //dd($municipal_list);
        return json_encode($municipal_list);

    }

    public function municipal_list2(Request $request){
     
     return $municipal_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
        ->select("farmer_info.add_municipality", "lib_prv.munCode", DB::raw("COUNT(farmer_info.add_municipality) as f_count"))
        ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", function($join){
            $join->on("lib_prv.municipality", "=", "farmer_info.add_municipality");
            $join->on("lib_prv.province", "=", "farmer_info.add_province");
              
        })
       // ->where("add_region", $request->region)
        ->where("add_province", $request->province)
        ->groupBy("add_municipality")
        ->get();
        //dd($municipal_list);
    

}





    public function index(){
        $region_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->select("farmer_info.add_region", "lib_prv.regCode")
            ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.regionName", "=", "farmer_info.add_region")
            ->groupBy("add_region")
            ->orderBy("region_sort", "ASC")
            ->get();

        $total_farmer = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->count("farmer_id");

        $total_province = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->distinct()
          ->count('add_province');

        $total_municipality = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
               //->select(DB::raw("count('add_municipality') as add_municipality","add_municipality"))
               //->groupBy("add_municipality")
               ->distinct()
               ->count('add_municipality');
         $total_area =  DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
       //     ->groupBy("farmer_id_fk")
            ->sum("techno_area");

            $province_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->select("farmer_info.add_province", "lib_prv.provCode")
            ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.province", "=", "farmer_info.add_province")
            //->where("add_region", $request->region)
            ->groupBy("add_province")
            ->get();

           $stations = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->orderBy('stationName', 'asc')
        ->get();

        return view("palaysikatan.dashboard_palaysikatan")
            ->with("total_farmer", $total_farmer)   
            ->with("total_province", $total_province)   
            ->with("total_municipality", $total_municipality)  
            ->with("total_area", $total_area)
            ->with("region_list", $region_list)
            ->with("province_list", $province_list)
            ->with("stations", $stations);
    }

    public function encoderData(Request $request){
     return  $users = DB::table('users')
     ->join('role_user', 'role_user.userId', '=', 'users.userId')
     ->where('role_user.roleId',25)
     ->where('users.stationId',$request->station)
     ->get();  
  }
  public function station_status(Request $request){
     $data = "";
     $femaleData=0;
     if($request->encoder == "ALL"){
          $data=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->select('station_entries.user_encoded','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'station_entries.user_encoded as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
          ->where('station_entries.station',$request->station)
          ->groupBy('station_entries.user_encoded');
     }else{
          $data=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->select('station_entries.user_encoded','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'station_entries.user_encoded as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
          ->where('station_entries.station',$request->station)
          ->where('station_entries.user_encoded',$request->encoder)
          ->groupBy('station_entries.user_encoded');
     }
     return Datatables::of($data)      
      ->addColumn('encoder', function($row){    
        return $row->encoder;

      })
      ->addColumn('noOfEncoded', function($row){      
        return $row->encoded;
      })
      ->addColumn('area', function($row){      
          return $row->techno_area;
        })
        ->addColumn('male', function($row){ 
           // dd($row->station);   
          $male=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->where('station_entries.station',$row->station)
          ->where('station_entries.user_encoded',$row->user_encoded)
          ->where('sex','male')          
          ->groupBy('station_entries.user_encoded')
          ->count();
        //  dd($male);
          
          return $male;
        })
        ->addColumn('female', function($row){ 
          $female=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->where('station_entries.station',$row->station)
          ->where('station_entries.user_encoded',$row->user_encoded)
          ->where('sex','female')          
          ->groupBy('station_entries.user_encoded')
          ->count();
          //dd($female);
          $femaleData =$female;
          return $female;
        })
        ->addColumn('total', function($row){                
          $totalSex=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->where('station_entries.station',$row->station)
          ->where('station_entries.user_encoded',$row->user_encoded)
          ->Orwhere('sex','female')          
          ->where('sex','male')          
          ->groupBy('station_entries.user_encoded')
          ->count();
        //  dd($totalSex);
          return $totalSex;
        })
      ->make(true);
     }
  
    
    public function coveredHa(){
     

                $bar_arr = array();
                $coveredHa = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
                ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info', 'farmer_info.fid','=','crop_production.farmer_id_fk')
                ->select(DB::raw('sum(crop_production.techno_area) as area'),'farmer_info.add_region' )                
                ->groupBy("farmer_info.add_region")
                ->get();
            foreach ($coveredHa as $data) {
                array_push($bar_arr,array(
                    "name" =>  $data->add_region,
                    "y" => $data->area,
                    "sliced" => true,
                ));
            }

            return $bar_arr;

    

    }
    public function load_site_tbl(Request $request){
            $tbl_array = array();
            
            $crop_establishment_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
                ->select("crop_establishment", DB::raw("sum(techno_area) as sum_sites"))
                ->where("crop_establishment", "!=", "")
                ->groupBy("crop_establishment")
                ->get();

                foreach ($crop_establishment_list as $crop) {
                    $sites = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
                        ->select("farmer_info.add_municipality")
                        ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production", "crop_production.farmer_id_fk", "=", "farmer_info.fid")
                        ->where("crop_production.crop_establishment", $crop->crop_establishment)
                        //->groupBy("farmer_info.add_municipality")
                        //->count("farmer_info.add_municipality");
                        ->distinct()
                         ->count('add_municipality');

                    array_push($tbl_array, array(
                        "crop_establishment" =>$this->renameCropEstablishment($crop->crop_establishment),
                        "no_municipality" => $sites,
                        "area"=> $crop->sum_sites,
                    ));
                }
                $data_arr = collect($tbl_array);
                return Datatables::of($data_arr)
                ->make(true);
    }    

    public function fca_list_tbl(Request $request){
            $tbl_array = array();
            $loc = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->select("province", "municipality")->where("munCode", $request->munCode)->where("provCode", $request->provCode)->first();

            $farmer_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
                ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production", "farmer_info.fid", "=", "crop_production.farmer_id_fk")
                ->where("add_province", $loc->province)
                ->where("add_municipality", $loc->municipality)
                ->get();

                foreach ($farmer_list as $farmer) {
                    

                    array_push($tbl_array, array(
                        "fca" => $farmer->farmer_id,
                        "name" => $farmer->f_lastName.", ".$farmer->f_firstName." ".$farmer->f_middleName,
                        "crop_establishment" => $this->renameCropEstablishment($farmer->crop_establishment),
                        "area"=> $farmer->techno_area,
                        "seed_variety"=> $farmer->variety_planted
                    ));


                }

                $data_arr = collect($tbl_array);
                return Datatables::of($data_arr)
                ->make(true);
    }    

    private function renameCropEstablishment($code){
        $crop_establishment_arr = array(
               "manual_transplanting" => "Manual Transplanting",
               "mechanized_transplanting" => "Mechanical Transplanting",
               "drum_seeding" => "Manual Direct-Seeding",
               "drum_seeding_sp" => "Mechanical Direct-Seeding"
            );

        return $crop_establishment_arr[$code];
    }


    public function exportPalaysikatanTable(){
        $excel_data = array();
        $cost_array = array();
        $documentation = array();
        $cr_array = array();
        $farmer_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production", "crop_production.farmer_id_fk", "=", "farmer_info.fid")
            ->get();

            foreach ($farmer_list as $farmer_info ){
                
                $farmrsn = $farmer_info->fid;
                $user_log = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.station_entries")->where("farmer_id", $farmer_info->farmer_id)->first();

                    if(count($user_log)>0){
                        $user=$user_log->user_encoded;
                        $prristation = DB::table("geotag_db2.tbl_station")->where("stationId", $user_log->station)->value("stationName");
                        $fpid = $farmrsn.$user."_".$prristation;
                    }else{$user="";$prristation="";$fpid="";}
                $season = $farmer_info->cropping_season;
                $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("regionName", $farmer_info->add_region)->value("regCode");
                $prov = $farmer_info->add_province;
                $mun = $farmer_info->add_municipality;
                $brgy = $farmer_info->barangay;
                $fpname = $farmer_info->f_full_name;
                $varplant = $farmer_info->variety_planted;
                $cropest =  $this->renameCropEstablishment($farmer_info->crop_establishment);
                $tdarea = $farmer_info->techno_area;

                $cropcut1 = 0;
                $cropcut2 = 0;
                $cropcut3 = 0;
                $cropcut_tot = $cropcut1 + $cropcut2 + $cropcut3;
                $initialmc = 0;
               
                if($farmer_info->area_harvested == 0){
                    $actualharv = 0;
                }else{
                    $actualharv = (($farmer_info->harvest_no_bags*$farmer_info->harvest_weight_bags)/$farmer_info->area_harvested)/1000;
                }
                if($tdarea == 0){
                     $cropcutyield = 0;
                     $actualyield = 0;
                }else{
                     $cropcutyield = $cropcut_tot/$tdarea;
                     $actualyield = $actualharv / $tdarea;
                }
                $cropcutyield14mc = $cropcutyield*(100-$initialmc) /(100-14); 
                $actualinitialmc = 0;
                $actualyield14mc = $actualyield*(100-$actualinitialmc) / (100-14);
                $actualcostkg = $farmer_info->seeds_price;

                if($farmer_info->sold_as == "Fresh"){
                   $actualpricekg = $farmer_info->fresh_palay_price;
                }else{
                   $actualpricekg = $farmer_info->dry_palay_price;
                }
                $actualgrossinc = ($actualyield*1000) * $actualpricekg;

                //INSERT TO EXCEL
                array_push($excel_data, array(
                    "farmrsn" => $farmrsn,
                    "user" =>$user,
                    "prristation" => $prristation,
                    "fpid" => $fpid,
                    "season" => $season,
                    "region" => $region,
                    "prov" =>$prov,
                    "mun" => $mun,
                    "brgy" => $brgy,
                    "fpname" => $fpname,
                    "varplant" => $varplant,
                    "cropest" => $cropest,
                    "tdarea" => $tdarea,
                    "cropcut1" => $cropcut1,
                    "cropcut2" => $cropcut2,
                    "cropcut3" => $cropcut3,
                    "cropcut_tot" => $cropcut_tot,
                    "cropcutyield" => $cropcutyield,
                    "initialmc" => $initialmc,
                    "cropcutyield14mc" => $cropcutyield14mc,
                    "actualharv" => $actualharv,
                    "actualyield" => $actualyield,
                    "actualinitialmc" => $actualinitialmc,
                    "actualyield14mc" => $actualyield14mc,
                    "actualcostkg" => $actualcostkg,
                    "actualpricekg" => $actualpricekg,
                    "actualgrossinc"=> $actualgrossinc   
                ));

                $cpid = $farmer_info->cpid;
                  //activities
                    $activity_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                         ->where("ref_cp_id", $cpid)
                         ->where("farmer_id", $farmer_info->fid)
                         ->get();

                    foreach ($activity_entries as $entry){

                              if($farmer_info->crop_establishment=="manual_transplanting"){
                                   if($entry->planting_id == 250){
                                        if(isset($data[$cpid]["land_prep_labor"])){
                                             $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id >= 251 && $entry->planting_id <= 256){
                                        if(isset($data[$cpid]["land_prep_rental"])){
                                             $data[$cpid]["land_prep_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 257){
                                        if(isset($data[$cpid]["land_prep_meals"])){
                                             $data[$cpid]["land_prep_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 

                                   if($entry->planting_id == 258 || $entry->planting_id == 259){
                                        if(isset($data[$cpid]["seed_bed_rental"])){
                                             $data[$cpid]["seed_bed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 260 ){
                                        if(isset($data[$cpid]["seed_bed_labor"])){
                                             $data[$cpid]["seed_bed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 263 || $entry->planting_id == 264){
                                        if(isset($data[$cpid]["seed_tray_labor"])){
                                             $data[$cpid]["seed_tray_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 262){
                                        if(isset($data[$cpid]["seed_tray_fert"])){
                                             $data[$cpid]["seed_tray_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 261){
                                        if(isset($data[$cpid]["seed_tray_mat"])){
                                             $data[$cpid]["seed_tray_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }



                                  


                                   if($entry->planting_id == 268){
                                        if(isset($data[$cpid]["seed_mgt_labor"])){
                                             $data[$cpid]["seed_mgt_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 265){
                                        if(isset($data[$cpid]["seed_mgt_mat"])){
                                             $data[$cpid]["seed_mgt_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 266){
                                        if(isset($data[$cpid]["seed_mgt_fert"])){
                                             $data[$cpid]["seed_mgt_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 267){
                                        if(isset($data[$cpid]["seed_mgt_meals"])){
                                             $data[$cpid]["seed_mgt_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 278 || $entry->planting_id == 289 || $entry->planting_id == 280 || $entry->planting_id == 281 || $entry->planting_id == 285){
                                        if(isset($data[$cpid]["direct_seed_labor"])){
                                             $data[$cpid]["direct_seed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 282 || $entry->planting_id == 283 || $entry->planting_id == 284 ){
                                        if(isset($data[$cpid]["direct_seed_rental"])){
                                             $data[$cpid]["direct_seed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 286  ){
                                        if(isset($data[$cpid]["direct_seed_meal"])){
                                             $data[$cpid]["direct_seed_meal"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 288 || $entry->planting_id == 289 ){
                                        if(isset($data[$cpid]["trans_laborpull"])){
                                             $data[$cpid]["trans_laborpull"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_laborpull_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_laborpull"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_laborpull_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 290 ){
                                        if(isset($data[$cpid]["trans_replant"])){
                                             $data[$cpid]["trans_replant"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 287 ){
                                        if(isset($data[$cpid]["trans_mat"])){
                                             $data[$cpid]["trans_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 291 ){
                                        if(isset($data[$cpid]["trans_meal"])){
                                             $data[$cpid]["trans_meal"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 292 ){
                                        if(isset($data[$cpid]["mech_labor"])){
                                             $data[$cpid]["mech_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 293 ){
                                        if(isset($data[$cpid]["mech_rental"])){
                                             $data[$cpid]["mech_rental"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 294 ){
                                        if(isset($data[$cpid]["mech_replant"])){
                                             $data[$cpid]["mech_replant"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                    if($entry->planting_id == 295 ){
                                        if(isset($data[$cpid]["mech_meals"])){
                                             $data[$cpid]["mech_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //FERT 1

                                    if($entry->planting_id >= 297 && $entry->planting_id <= 301){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //fert 2
                                   if($entry->planting_id >= 304 && $entry->planting_id <= 309  ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 3
                                    if($entry->planting_id >= 312 && $entry->planting_id <= 316  ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 318 || $entry->planting_id == 311 || $entry->planting_id == 303 ){
                                        if(isset($data[$cpid]["fert_mgt_meals"])){
                                             $data[$cpid]["fert_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 302 || $entry->planting_id == 310 || $entry->planting_id == 317 ){
                                        if(isset($data[$cpid]["fert_mgt_labor"])){
                                             $data[$cpid]["fert_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }



                                   //WATER MGT
                                   if($entry->planting_id == 320 || $entry->planting_id == 324 || $entry->planting_id == 328 ){
                                        if(isset($data[$cpid]["wtr_mgt_irr"])){
                                             $data[$cpid]["wtr_mgt_irr"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_irr"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 321 || $entry->planting_id == 325 || $entry->planting_id == 329 ){
                                        if(isset($data[$cpid]["wtr_mgt_fuel"])){
                                             $data[$cpid]["wtr_mgt_fuel"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_fuel"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 322 || $entry->planting_id == 326 || $entry->planting_id == 330 ){
                                        if(isset($data[$cpid]["wtr_mgt_labor"])){
                                             $data[$cpid]["wtr_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 323 || $entry->planting_id == 327 || $entry->planting_id == 331 ){
                                        if(isset($data[$cpid]["wtr_mgt_meals"])){
                                             $data[$cpid]["wtr_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //PEST MGT
                                   if($entry->planting_id == 333 || $entry->planting_id == 339 || $entry->planting_id == 345 ){
                                        if(isset($data[$cpid]["pest_mgt_mollus"])){
                                             $data[$cpid]["pest_mgt_mollus"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_mollus"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   
                                   if($entry->planting_id == 334 || $entry->planting_id == 340 || $entry->planting_id == 346 ){
                                        if(isset($data[$cpid]["pest_mgt_insect"])){
                                             $data[$cpid]["pest_mgt_insect"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_insect"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 335 || $entry->planting_id == 341 || $entry->planting_id == 347 ){
                                        if(isset($data[$cpid]["pest_mgt_fungi"])){
                                             $data[$cpid]["pest_mgt_fungi"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_fungi"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 336 || $entry->planting_id == 342 || $entry->planting_id == 348 ){
                                        if(isset($data[$cpid]["pest_mgt_roden"])){
                                             $data[$cpid]["pest_mgt_roden"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_roden"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] = $entry->unit_cost;
                                        }                                   
                                   }


                                   if($entry->planting_id == 337 || $entry->planting_id == 343 || $entry->planting_id == 349 ){
                                        if(isset($data[$cpid]["pest_mgt_labor"])){
                                             $data[$cpid]["pest_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 338 || $entry->planting_id == 344 || $entry->planting_id == 350 ){
                                        if(isset($data[$cpid]["pest_mgt_meals"])){
                                             $data[$cpid]["pest_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //WEED MGT
                                   if($entry->planting_id == 352 || $entry->planting_id == 356 || $entry->planting_id == 360 ){
                                        if(isset($data[$cpid]["weed_mgt_labor"])){
                                             $data[$cpid]["weed_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 353 || $entry->planting_id == 357 || $entry->planting_id == 361 ){
                                        if(isset($data[$cpid]["weed_mgt_herbicide"])){
                                             $data[$cpid]["weed_mgt_herbicide"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_herbicide"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 354 || $entry->planting_id == 358 || $entry->planting_id == 362 ){
                                        if(isset($data[$cpid]["weed_mgt_appli"])){
                                             $data[$cpid]["weed_mgt_appli"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_appli"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 355 || $entry->planting_id == 359 || $entry->planting_id == 363 ){
                                        if(isset($data[$cpid]["weed_mgt_meals"])){
                                             $data[$cpid]["weed_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //HARVEST
                                   if($entry->planting_id == 364 ){
                                        if(isset($data[$cpid]["harvest_labor"])){
                                             $data[$cpid]["harvest_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 365 ){
                                        if(isset($data[$cpid]["harvest_tresher"])){
                                             $data[$cpid]["harvest_tresher"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_tresher"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 366 ){
                                        if(isset($data[$cpid]["harvest_harvester"])){
                                             $data[$cpid]["harvest_harvester"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_harvester"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 367 ){
                                        if(isset($data[$cpid]["harvest_hauling"])){
                                             $data[$cpid]["harvest_hauling"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_hauling"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 368 || $entry->planting_id == 369 || $entry->planting_id == 370 ){
                                        if(isset($data[$cpid]["harvest_matt"])){
                                             $data[$cpid]["harvest_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 371 ){
                                        if(isset($data[$cpid]["harvest_meals"])){
                                             $data[$cpid]["harvest_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //OTHER COST
                                   if($entry->planting_id == 500 ){
                                        if(isset($data[$cpid]["oth_labor"])){
                                             $data[$cpid]["oth_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 503 ){
                                        if(isset($data[$cpid]["oth_matt"])){
                                             $data[$cpid]["oth_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 506 ){
                                        if(isset($data[$cpid]["oth_land"])){
                                             $data[$cpid]["oth_land"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_land"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 509 ){
                                        if(isset($data[$cpid]["oth_interest"])){
                                             $data[$cpid]["oth_interest"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_interest"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] = $entry->unit_cost;
                                        }                                   
                                   }

                              } //end for manual transplanting
                              elseif($farmer_info->crop_establishment=="mechanized_transplanting"){

                                   if($entry->planting_id == 372){
                                        if(isset($data[$cpid]["land_prep_labor"])){
                                             $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id >= 373 && $entry->planting_id <= 378){
                                        if(isset($data[$cpid]["land_prep_rental"])){
                                             $data[$cpid]["land_prep_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 379){
                                        if(isset($data[$cpid]["land_prep_meals"])){
                                             $data[$cpid]["land_prep_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 

                                   if($entry->planting_id == 380 || $entry->planting_id == 381){
                                        if(isset($data[$cpid]["seed_bed_rental"])){
                                             $data[$cpid]["seed_bed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 382 ){
                                        if(isset($data[$cpid]["seed_bed_labor"])){
                                             $data[$cpid]["seed_bed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 385 || $entry->planting_id == 386){
                                        if(isset($data[$cpid]["seed_tray_labor"])){
                                             $data[$cpid]["seed_tray_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 384){
                                        if(isset($data[$cpid]["seed_tray_fert"])){
                                             $data[$cpid]["seed_tray_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 383){
                                        if(isset($data[$cpid]["seed_tray_mat"])){
                                             $data[$cpid]["seed_tray_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 390){
                                        if(isset($data[$cpid]["seed_mgt_labor"])){
                                             $data[$cpid]["seed_mgt_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 387){
                                        if(isset($data[$cpid]["seed_mgt_mat"])){
                                             $data[$cpid]["seed_mgt_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 388){
                                        if(isset($data[$cpid]["seed_mgt_fert"])){
                                             $data[$cpid]["seed_mgt_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 389){
                                        if(isset($data[$cpid]["seed_mgt_meals"])){
                                             $data[$cpid]["seed_mgt_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 400 || $entry->planting_id == 401 || $entry->planting_id == 402 || $entry->planting_id == 403 || $entry->planting_id == 407){
                                        if(isset($data[$cpid]["direct_seed_labor"])){
                                             $data[$cpid]["direct_seed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 404 || $entry->planting_id == 405 || $entry->planting_id == 406 ){
                                        if(isset($data[$cpid]["direct_seed_rental"])){
                                             $data[$cpid]["direct_seed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 408  ){
                                        if(isset($data[$cpid]["direct_seed_meal"])){
                                             $data[$cpid]["direct_seed_meal"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 410 || $entry->planting_id == 411 ){
                                        if(isset($data[$cpid]["trans_laborpull"])){
                                             $data[$cpid]["trans_laborpull"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_laborpull_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_laborpull"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_laborpull_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 412 ){
                                        if(isset($data[$cpid]["trans_replant"])){
                                             $data[$cpid]["trans_replant"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 409 ){
                                        if(isset($data[$cpid]["trans_mat"])){
                                             $data[$cpid]["trans_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 413 ){
                                        if(isset($data[$cpid]["trans_meal"])){
                                             $data[$cpid]["trans_meal"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 414 ){
                                        if(isset($data[$cpid]["mech_labor"])){
                                             $data[$cpid]["mech_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 415 ){
                                        if(isset($data[$cpid]["mech_rental"])){
                                             $data[$cpid]["mech_rental"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 416 ){
                                        if(isset($data[$cpid]["mech_replant"])){
                                             $data[$cpid]["mech_replant"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                    if($entry->planting_id == 417 ){
                                        if(isset($data[$cpid]["mech_meals"])){
                                             $data[$cpid]["mech_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //FERT 1

                                    if($entry->planting_id >= 419 && $entry->planting_id <= 423){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //fert 2
                                   if($entry->planting_id >= 426 && $entry->planting_id <= 431  ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 3
                                    if($entry->planting_id >= 434 && $entry->planting_id <= 439  ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 441 || $entry->planting_id == 433 || $entry->planting_id == 425 ){
                                        if(isset($data[$cpid]["fert_mgt_meals"])){
                                             $data[$cpid]["fert_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 424 || $entry->planting_id == 432 || $entry->planting_id == 440 ){
                                        if(isset($data[$cpid]["fert_mgt_labor"])){
                                             $data[$cpid]["fert_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }



                                   //WATER MGT
                                   if($entry->planting_id == 443 || $entry->planting_id == 447 || $entry->planting_id == 451 ){
                                        if(isset($data[$cpid]["wtr_mgt_irr"])){
                                             $data[$cpid]["wtr_mgt_irr"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_irr"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 444 || $entry->planting_id == 448 || $entry->planting_id == 452 ){
                                        if(isset($data[$cpid]["wtr_mgt_fuel"])){
                                             $data[$cpid]["wtr_mgt_fuel"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_fuel"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 445 || $entry->planting_id == 449 || $entry->planting_id == 453 ){
                                        if(isset($data[$cpid]["wtr_mgt_labor"])){
                                             $data[$cpid]["wtr_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 446 || $entry->planting_id == 450 || $entry->planting_id == 454 ){
                                        if(isset($data[$cpid]["wtr_mgt_meals"])){
                                             $data[$cpid]["wtr_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //PEST MGT
                                   if($entry->planting_id == 456 || $entry->planting_id == 462 || $entry->planting_id == 468 ){
                                        if(isset($data[$cpid]["pest_mgt_mollus"])){
                                             $data[$cpid]["pest_mgt_mollus"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_mollus"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   
                                   if($entry->planting_id == 457 || $entry->planting_id == 463 || $entry->planting_id == 469 ){
                                        if(isset($data[$cpid]["pest_mgt_insect"])){
                                             $data[$cpid]["pest_mgt_insect"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_insect"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 458 || $entry->planting_id == 464 || $entry->planting_id == 470 ){
                                        if(isset($data[$cpid]["pest_mgt_fungi"])){
                                             $data[$cpid]["pest_mgt_fungi"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_fungi"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 459 || $entry->planting_id == 465 || $entry->planting_id == 471 ){
                                        if(isset($data[$cpid]["pest_mgt_roden"])){
                                             $data[$cpid]["pest_mgt_roden"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_roden"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] = $entry->unit_cost;
                                        }                                   
                                   }


                                   if($entry->planting_id == 460 || $entry->planting_id == 466 || $entry->planting_id == 472 ){
                                        if(isset($data[$cpid]["pest_mgt_labor"])){
                                             $data[$cpid]["pest_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 461 || $entry->planting_id == 467 || $entry->planting_id == 473 ){
                                        if(isset($data[$cpid]["pest_mgt_meals"])){
                                             $data[$cpid]["pest_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //WEED MGT
                                   if($entry->planting_id == 475 || $entry->planting_id == 479 || $entry->planting_id == 483 ){
                                        if(isset($data[$cpid]["weed_mgt_labor"])){
                                             $data[$cpid]["weed_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 476 || $entry->planting_id == 480 || $entry->planting_id == 484 ){
                                        if(isset($data[$cpid]["weed_mgt_herbicide"])){
                                             $data[$cpid]["weed_mgt_herbicide"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_herbicide"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 477 || $entry->planting_id == 481 || $entry->planting_id == 485 ){
                                        if(isset($data[$cpid]["weed_mgt_appli"])){
                                             $data[$cpid]["weed_mgt_appli"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_appli"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 478 || $entry->planting_id == 482 || $entry->planting_id == 486 ){
                                        if(isset($data[$cpid]["weed_mgt_meals"])){
                                             $data[$cpid]["weed_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //HARVEST
                                   if($entry->planting_id == 487 ){
                                        if(isset($data[$cpid]["harvest_labor"])){
                                             $data[$cpid]["harvest_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 488 ){
                                        if(isset($data[$cpid]["harvest_tresher"])){
                                             $data[$cpid]["harvest_tresher"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_tresher"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 489 ){
                                        if(isset($data[$cpid]["harvest_harvester"])){
                                             $data[$cpid]["harvest_harvester"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_harvester"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 490 ){
                                        if(isset($data[$cpid]["harvest_hauling"])){
                                             $data[$cpid]["harvest_hauling"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_hauling"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 491 || $entry->planting_id == 492 || $entry->planting_id == 493 ){
                                        if(isset($data[$cpid]["harvest_matt"])){
                                             $data[$cpid]["harvest_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 494 ){
                                        if(isset($data[$cpid]["harvest_meals"])){
                                             $data[$cpid]["harvest_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //OTHER COST
                                   if($entry->planting_id == 499 ){
                                        if(isset($data[$cpid]["oth_labor"])){
                                             $data[$cpid]["oth_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 502 ){
                                        if(isset($data[$cpid]["oth_matt"])){
                                             $data[$cpid]["oth_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 505 ){
                                        if(isset($data[$cpid]["oth_land"])){
                                             $data[$cpid]["oth_land"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_land"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 508 ){
                                        if(isset($data[$cpid]["oth_interest"])){
                                             $data[$cpid]["oth_interest"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_interest"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] = $entry->unit_cost;
                                        }                                   
                                   }

                              
                              } //mechanized_transplanting
                              else if ($farmer_info->crop_establishment=="drum_seeding"){


                                   if($entry->planting_id == 128){
                                        if(isset($data[$cpid]["land_prep_labor"])){
                                             $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id >= 129 && $entry->planting_id <= 134){
                                        if(isset($data[$cpid]["land_prep_rental"])){
                                             $data[$cpid]["land_prep_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 135){
                                        if(isset($data[$cpid]["land_prep_meals"])){
                                             $data[$cpid]["land_prep_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 

                                   if($entry->planting_id == 136 || $entry->planting_id == 137){
                                        if(isset($data[$cpid]["seed_bed_rental"])){
                                             $data[$cpid]["seed_bed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 138 ){
                                        if(isset($data[$cpid]["seed_bed_labor"])){
                                             $data[$cpid]["seed_bed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 141 || $entry->planting_id == 142){
                                        if(isset($data[$cpid]["seed_tray_labor"])){
                                             $data[$cpid]["seed_tray_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 140){
                                        if(isset($data[$cpid]["seed_tray_fert"])){
                                             $data[$cpid]["seed_tray_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 139){
                                        if(isset($data[$cpid]["seed_tray_mat"])){
                                             $data[$cpid]["seed_tray_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 146){
                                        if(isset($data[$cpid]["seed_mgt_labor"])){
                                             $data[$cpid]["seed_mgt_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 143){
                                        if(isset($data[$cpid]["seed_mgt_mat"])){
                                             $data[$cpid]["seed_mgt_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 144){
                                        if(isset($data[$cpid]["seed_mgt_fert"])){
                                             $data[$cpid]["seed_mgt_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 145){
                                        if(isset($data[$cpid]["seed_mgt_meals"])){
                                             $data[$cpid]["seed_mgt_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 156 || $entry->planting_id == 157 || $entry->planting_id == 158 || $entry->planting_id == 159 || $entry->planting_id == 163){
                                        if(isset($data[$cpid]["direct_seed_labor"])){
                                             $data[$cpid]["direct_seed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 160 || $entry->planting_id == 161 || $entry->planting_id == 162 || $entry->planting_id == 495 ){
                                        if(isset($data[$cpid]["direct_seed_rental"])){
                                             $data[$cpid]["direct_seed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 164  ){
                                        if(isset($data[$cpid]["direct_seed_meal"])){
                                             $data[$cpid]["direct_seed_meal"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 166 || $entry->planting_id == 167 ){
                                        if(isset($data[$cpid]["trans_laborpull"])){
                                             $data[$cpid]["trans_laborpull"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_laborpull_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_laborpull"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_laborpull_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 168 ){
                                        if(isset($data[$cpid]["trans_replant"])){
                                             $data[$cpid]["trans_replant"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 165 ){
                                        if(isset($data[$cpid]["trans_mat"])){
                                             $data[$cpid]["trans_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 169 ){
                                        if(isset($data[$cpid]["trans_meal"])){
                                             $data[$cpid]["trans_meal"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 170 ){
                                        if(isset($data[$cpid]["mech_labor"])){
                                             $data[$cpid]["mech_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 171 ){
                                        if(isset($data[$cpid]["mech_rental"])){
                                             $data[$cpid]["mech_rental"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 172 ){
                                        if(isset($data[$cpid]["mech_replant"])){
                                             $data[$cpid]["mech_replant"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                    if($entry->planting_id == 173 ){
                                        if(isset($data[$cpid]["mech_meals"])){
                                             $data[$cpid]["mech_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //FERT 1

                                    if($entry->planting_id >= 175 && $entry->planting_id <= 179){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //fert 2
                                   if($entry->planting_id >= 182 && $entry->planting_id <= 187  ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 3
                                    if($entry->planting_id >= 190 && $entry->planting_id <= 194  ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 196 || $entry->planting_id == 189 || $entry->planting_id == 181 ){
                                        if(isset($data[$cpid]["fert_mgt_meals"])){
                                             $data[$cpid]["fert_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 180 || $entry->planting_id == 188 || $entry->planting_id == 195 ){
                                        if(isset($data[$cpid]["fert_mgt_labor"])){
                                             $data[$cpid]["fert_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }



                                   //WATER MGT
                                   if($entry->planting_id == 198 || $entry->planting_id == 202 || $entry->planting_id == 206 ){
                                        if(isset($data[$cpid]["wtr_mgt_irr"])){
                                             $data[$cpid]["wtr_mgt_irr"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_irr"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 199 || $entry->planting_id == 203 || $entry->planting_id == 207 ){
                                        if(isset($data[$cpid]["wtr_mgt_fuel"])){
                                             $data[$cpid]["wtr_mgt_fuel"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_fuel"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 200 || $entry->planting_id == 204 || $entry->planting_id == 208 ){
                                        if(isset($data[$cpid]["wtr_mgt_labor"])){
                                             $data[$cpid]["wtr_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 201 || $entry->planting_id == 205 || $entry->planting_id == 209 ){
                                        if(isset($data[$cpid]["wtr_mgt_meals"])){
                                             $data[$cpid]["wtr_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //PEST MGT
                                   if($entry->planting_id == 211 || $entry->planting_id == 217 || $entry->planting_id == 223 ){
                                        if(isset($data[$cpid]["pest_mgt_mollus"])){
                                             $data[$cpid]["pest_mgt_mollus"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_mollus"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   
                                   if($entry->planting_id == 212 || $entry->planting_id == 218 || $entry->planting_id == 224 ){
                                        if(isset($data[$cpid]["pest_mgt_insect"])){
                                             $data[$cpid]["pest_mgt_insect"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_insect"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 213 || $entry->planting_id == 219 || $entry->planting_id == 225 ){
                                        if(isset($data[$cpid]["pest_mgt_fungi"])){
                                             $data[$cpid]["pest_mgt_fungi"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_fungi"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 214 || $entry->planting_id == 220 || $entry->planting_id == 226 ){
                                        if(isset($data[$cpid]["pest_mgt_roden"])){
                                             $data[$cpid]["pest_mgt_roden"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_roden"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] = $entry->unit_cost;
                                        }                                   
                                   }


                                   if($entry->planting_id == 215 || $entry->planting_id == 221 || $entry->planting_id == 227 ){
                                        if(isset($data[$cpid]["pest_mgt_labor"])){
                                             $data[$cpid]["pest_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 216 || $entry->planting_id == 222 || $entry->planting_id == 228 ){
                                        if(isset($data[$cpid]["pest_mgt_meals"])){
                                             $data[$cpid]["pest_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //WEED MGT
                                   if($entry->planting_id == 230 || $entry->planting_id == 234 || $entry->planting_id == 238 ){
                                        if(isset($data[$cpid]["weed_mgt_labor"])){
                                             $data[$cpid]["weed_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 231 || $entry->planting_id == 235 || $entry->planting_id == 239 ){
                                        if(isset($data[$cpid]["weed_mgt_herbicide"])){
                                             $data[$cpid]["weed_mgt_herbicide"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_herbicide"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 232 || $entry->planting_id == 236 || $entry->planting_id == 240 ){
                                        if(isset($data[$cpid]["weed_mgt_appli"])){
                                             $data[$cpid]["weed_mgt_appli"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_appli"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 233 || $entry->planting_id == 237 || $entry->planting_id == 241 ){
                                        if(isset($data[$cpid]["weed_mgt_meals"])){
                                             $data[$cpid]["weed_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //HARVEST
                                   if($entry->planting_id == 242 ){
                                        if(isset($data[$cpid]["harvest_labor"])){
                                             $data[$cpid]["harvest_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 243 ){
                                        if(isset($data[$cpid]["harvest_tresher"])){
                                             $data[$cpid]["harvest_tresher"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_tresher"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 244 ){
                                        if(isset($data[$cpid]["harvest_harvester"])){
                                             $data[$cpid]["harvest_harvester"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_harvester"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 245 ){
                                        if(isset($data[$cpid]["harvest_hauling"])){
                                             $data[$cpid]["harvest_hauling"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_hauling"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 246 || $entry->planting_id == 247 || $entry->planting_id == 248 ){
                                        if(isset($data[$cpid]["harvest_matt"])){
                                             $data[$cpid]["harvest_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 249 ){
                                        if(isset($data[$cpid]["harvest_meals"])){
                                             $data[$cpid]["harvest_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //OTHER COST
                                   if($entry->planting_id == 498 ){
                                        if(isset($data[$cpid]["oth_labor"])){
                                             $data[$cpid]["oth_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 501 ){
                                        if(isset($data[$cpid]["oth_matt"])){
                                             $data[$cpid]["oth_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 504 ){
                                        if(isset($data[$cpid]["oth_land"])){
                                             $data[$cpid]["oth_land"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_land"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 507 ){
                                        if(isset($data[$cpid]["oth_interest"])){
                                             $data[$cpid]["oth_interest"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_interest"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] = $entry->unit_cost;
                                        }                                   
                                   }

                              } //drum_seeding
                              else if ($farmer_info->crop_establishment=="drum_seeding_sp"){

                                   if($entry->planting_id == 1){
                                        if(isset($data[$cpid]["land_prep_labor"])){
                                             $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id >= 2 && $entry->planting_id <= 7){
                                        if(isset($data[$cpid]["land_prep_rental"])){
                                             $data[$cpid]["land_prep_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 8){
                                        if(isset($data[$cpid]["land_prep_meals"])){
                                             $data[$cpid]["land_prep_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["land_prep_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["land_prep_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["land_prep_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 

                                   if($entry->planting_id == 9 || $entry->planting_id == 10){
                                        if(isset($data[$cpid]["seed_bed_rental"])){
                                             $data[$cpid]["seed_bed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   } 


                                   if($entry->planting_id == 11 ){
                                        if(isset($data[$cpid]["seed_bed_labor"])){
                                             $data[$cpid]["seed_bed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_bed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_bed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_bed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 14 || $entry->planting_id == 15){
                                        if(isset($data[$cpid]["seed_tray_labor"])){
                                             $data[$cpid]["seed_tray_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 13){
                                        if(isset($data[$cpid]["seed_tray_fert"])){
                                             $data[$cpid]["seed_tray_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 12){
                                        if(isset($data[$cpid]["seed_tray_mat"])){
                                             $data[$cpid]["seed_tray_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_tray_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_tray_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_tray_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 19){
                                        if(isset($data[$cpid]["seed_mgt_labor"])){
                                             $data[$cpid]["seed_mgt_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 16){
                                        if(isset($data[$cpid]["seed_mgt_mat"])){
                                             $data[$cpid]["seed_mgt_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 17){
                                        if(isset($data[$cpid]["seed_mgt_fert"])){
                                             $data[$cpid]["seed_mgt_fert"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_fert_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_fert"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_fert_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 18){
                                        if(isset($data[$cpid]["seed_mgt_meals"])){
                                             $data[$cpid]["seed_mgt_meals"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["seed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["seed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["seed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 29 || $entry->planting_id == 30 || $entry->planting_id == 31 || $entry->planting_id == 32 || $entry->planting_id == 36){
                                        if(isset($data[$cpid]["direct_seed_labor"])){
                                             $data[$cpid]["direct_seed_labor"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 33 || $entry->planting_id == 34 || $entry->planting_id == 35 || $entry->planting_id == 496 ){
                                        if(isset($data[$cpid]["direct_seed_rental"])){
                                             $data[$cpid]["direct_seed_rental"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 37  ){
                                        if(isset($data[$cpid]["direct_seed_meal"])){
                                             $data[$cpid]["direct_seed_meal"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["direct_seed_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["direct_seed_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["direct_seed_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 39 || $entry->planting_id == 40 ){
                                        if(isset($data[$cpid]["trans_laborpull"])){
                                             $data[$cpid]["trans_laborpull"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_laborpull_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_laborpull"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_laborpull_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 41 ){
                                        if(isset($data[$cpid]["trans_replant"])){
                                             $data[$cpid]["trans_replant"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 38 ){
                                        if(isset($data[$cpid]["trans_mat"])){
                                             $data[$cpid]["trans_mat"] += $entry->qty*$entry->unit_cost;

                                              $land_prep[$cpid]["trans_mat_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_mat"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_mat_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 42 ){
                                        if(isset($data[$cpid]["trans_meal"])){
                                             $data[$cpid]["trans_meal"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["trans_meal"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["trans_meal_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 43 ){
                                        if(isset($data[$cpid]["mech_labor"])){
                                             $data[$cpid]["mech_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_labor_ha"] = $entry->unit_cost;
                                        }                                  
                                   }


                                   if($entry->planting_id == 44 ){
                                        if(isset($data[$cpid]["mech_rental"])){
                                             $data[$cpid]["mech_rental"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_rental"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_rental_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   if($entry->planting_id == 45 ){
                                        if(isset($data[$cpid]["mech_replant"])){
                                             $data[$cpid]["mech_replant"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_replant"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_replant_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                    if($entry->planting_id == 46 ){
                                        if(isset($data[$cpid]["mech_meals"])){
                                             $data[$cpid]["mech_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["mech_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["mech_meals_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //FERT 1

                                    if($entry->planting_id >= 48 && $entry->planting_id <= 52){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                  
                                   }

                                   //fert 2
                                   if($entry->planting_id >= 55 && $entry->planting_id <= 60 ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 3
                                    if($entry->planting_id >= 63 && $entry->planting_id <= 68 ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 497 ){
                                        if(isset($data[$cpid]["fert_mgt_cost"])){
                                             $data[$cpid]["fert_mgt_cost"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_cost"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_cost_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 70 || $entry->planting_id == 62 || $entry->planting_id == 54 ){
                                        if(isset($data[$cpid]["fert_mgt_meals"])){
                                             $data[$cpid]["fert_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //fert 
                                    if($entry->planting_id == 53 || $entry->planting_id == 61 || $entry->planting_id == 69 ){
                                        if(isset($data[$cpid]["fert_mgt_labor"])){
                                             $data[$cpid]["fert_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["fert_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["fert_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }



                                   //WATER MGT
                                   if($entry->planting_id == 72 || $entry->planting_id == 76 || $entry->planting_id == 80 ){
                                        if(isset($data[$cpid]["wtr_mgt_irr"])){
                                             $data[$cpid]["wtr_mgt_irr"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_irr"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_irr_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 73 || $entry->planting_id == 77 || $entry->planting_id == 81 ){
                                        if(isset($data[$cpid]["wtr_mgt_fuel"])){
                                             $data[$cpid]["wtr_mgt_fuel"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_fuel"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_fuel_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 74 || $entry->planting_id == 78 || $entry->planting_id == 82 ){
                                        if(isset($data[$cpid]["wtr_mgt_labor"])){
                                             $data[$cpid]["wtr_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 75 || $entry->planting_id == 79 || $entry->planting_id == 83 ){
                                        if(isset($data[$cpid]["wtr_mgt_meals"])){
                                             $data[$cpid]["wtr_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["wtr_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["wtr_mgt_meals"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //PEST MGT
                                   if($entry->planting_id == 85 || $entry->planting_id == 91 || $entry->planting_id == 97 ){
                                        if(isset($data[$cpid]["pest_mgt_mollus"])){
                                             $data[$cpid]["pest_mgt_mollus"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_mollus"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_mollus_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   
                                   if($entry->planting_id == 86 || $entry->planting_id == 92 || $entry->planting_id == 98 ){
                                        if(isset($data[$cpid]["pest_mgt_insect"])){
                                             $data[$cpid]["pest_mgt_insect"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_insect"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_insect_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 87 || $entry->planting_id == 93 || $entry->planting_id == 99 ){
                                        if(isset($data[$cpid]["pest_mgt_fungi"])){
                                             $data[$cpid]["pest_mgt_fungi"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_fungi"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_fungi_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 88 || $entry->planting_id == 94 || $entry->planting_id == 100 ){
                                        if(isset($data[$cpid]["pest_mgt_roden"])){
                                             $data[$cpid]["pest_mgt_roden"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_roden"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_roden_ha"] = $entry->unit_cost;
                                        }                                   
                                   }


                                   if($entry->planting_id == 89 || $entry->planting_id == 95 || $entry->planting_id == 101 ){
                                        if(isset($data[$cpid]["pest_mgt_labor"])){
                                             $data[$cpid]["pest_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 90 || $entry->planting_id == 96 || $entry->planting_id == 102 ){
                                        if(isset($data[$cpid]["pest_mgt_meals"])){
                                             $data[$cpid]["pest_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["pest_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["pest_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //WEED MGT
                                   if($entry->planting_id == 104 || $entry->planting_id == 108 || $entry->planting_id == 112 ){
                                        if(isset($data[$cpid]["weed_mgt_labor"])){
                                             $data[$cpid]["weed_mgt_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 105 || $entry->planting_id == 109 || $entry->planting_id == 113 ){
                                        if(isset($data[$cpid]["weed_mgt_herbicide"])){
                                             $data[$cpid]["weed_mgt_herbicide"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_herbicide"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_herbicide_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 106 || $entry->planting_id == 110 || $entry->planting_id == 114 ){
                                        if(isset($data[$cpid]["weed_mgt_appli"])){
                                             $data[$cpid]["weed_mgt_appli"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_appli"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_appli_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 107 || $entry->planting_id == 111 || $entry->planting_id == 115 ){
                                        if(isset($data[$cpid]["weed_mgt_meals"])){
                                             $data[$cpid]["weed_mgt_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["weed_mgt_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["weed_mgt_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //HARVEST
                                   if($entry->planting_id == 116 ){
                                        if(isset($data[$cpid]["harvest_labor"])){
                                             $data[$cpid]["harvest_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }
                                   if($entry->planting_id == 117 ){
                                        if(isset($data[$cpid]["harvest_tresher"])){
                                             $data[$cpid]["harvest_tresher"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_tresher"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_tresher_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 118 ){
                                        if(isset($data[$cpid]["harvest_harvester"])){
                                             $data[$cpid]["harvest_harvester"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_harvester"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_harvester_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 119 ){
                                        if(isset($data[$cpid]["harvest_hauling"])){
                                             $data[$cpid]["harvest_hauling"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_hauling"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_hauling_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 120 || $entry->planting_id == 121 || $entry->planting_id == 122 ){
                                        if(isset($data[$cpid]["harvest_matt"])){
                                             $data[$cpid]["harvest_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 123 ){
                                        if(isset($data[$cpid]["harvest_meals"])){
                                             $data[$cpid]["harvest_meals"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["harvest_meals"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["harvest_meals_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   //OTHER COST
                                   if($entry->planting_id == 124 ){
                                        if(isset($data[$cpid]["oth_labor"])){
                                             $data[$cpid]["oth_labor"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_labor"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_labor_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 125 ){
                                        if(isset($data[$cpid]["oth_matt"])){
                                             $data[$cpid]["oth_matt"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_matt"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_matt_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 126 ){
                                        if(isset($data[$cpid]["oth_land"])){
                                             $data[$cpid]["oth_land"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_land"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_land_ha"] = $entry->unit_cost;
                                        }                                   
                                   }

                                   if($entry->planting_id == 127 ){
                                        if(isset($data[$cpid]["oth_interest"])){
                                             $data[$cpid]["oth_interest"] += $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] += $entry->unit_cost;
                                        }else{
                                             $data[$cpid]["oth_interest"] = $entry->qty*$entry->unit_cost;
                                              $land_prep[$cpid]["oth_interest"] = $entry->unit_cost;
                                        }                                   
                                   }

                              
                              } //drum_seeding_sp
                        
                    }
                         $metro_grand = 0;
                         $metro_meals =0;
                          $metro_cost =0;
                          $metro_land =0;
                          $metro_interest =0;
                    

                    foreach($data as $key_id => $value){
                        $total_labor_cost = 0;
                        $total_labor_cost_ha = 0;
                        $total_mat_cost = 0;
                        $total_mat_cost_ha =0;
                        $total_machine = 0;
                        $total_machine_ha = 0;
                        $total_fert = 0;
                        $total_fert_ha =0;
                        $total_chemical =0;
                        $total_chemical_ha =0;
                        $total_meals = 0;
                        $total_meals_ha =0;
                        $total_irr =0;
                        $total_irr_ha =0;
                        $total_cost = 0;
                        $total_cost_ha = 0;
                        $grand = 0;
                        $grand_ha = 0;
                        $land_cost = 0;
                        $total_interest = 0;
                       
                         foreach ($value as $key => $value_data) {

                               if($key == "land_prep_labor" || $key == "seed_bed_labor" || $key == "seed_tray_labor" || $key =="seed_mgt_labor" || $key == "direct_seed_labor" || $key == "trans_laborpull" || $key == "mech_labor" || $key=="fert_mgt_labor"  || $key =="pest_mgt_labor" || $key=="weed_mgt_labor" || $key =="harvest_labor" || $key == "oth_labor" || $key == "trans_replant" || $key == "mech_replant" || $key=="weed_mgt_appli"){
                                   $total_labor_cost += $value_data;
                                   $grand += $value_data;
                                       
                              }
                              
                              if($key == "land_prep_labor_ha" || $key == "seed_bed_labor_ha" || $key == "seed_tray_labor_ha" || $key =="seed_mgt_labor_ha" || $key == "direct_seed_labor_ha" || $key == "trans_laborpull_ha" || $key == "mech_labor_ha" || $key=="fert_mgt_labor_ha" || $key =="pest_mgt_labor_ha" || $key=="weed_mgt_labor_ha" || $key =="harvest_labor_ha" || $key == "oth_labor_ha" || $key == "trans_replant_ha" || $key == "mech_replant_ha" || $key =="weed_mgt_appli_ha"){
                                   $total_labor_cost_ha += $value_data;
                                   $grand_ha += $value_data;
                              }

                              if($key == "seed_tray_mat" || $key == "seed_mgt_mat" || $key == "trans_mat" || $key =="harvest_matt" ){
                                   $total_mat_cost += $value_data;
                                   $grand += $value_data;
                              }
                              if($key == "seed_tray_mat_ha" || $key == "seed_mgt_mat_ha" || $key == "trans_mat_ha" || $key =="harvest_matt_ha" || $key == "oth_matt_ha" ){
                                   $total_mat_cost_ha += $value_data;
                                   $grand_ha += $value_data;
                              }
                              if($key == "land_prep_rental" || $key == "seed_bed_rental" || $key == "direct_seed_rental" || $key =="mech_rental" || $key=="harvest_tresher" || $key =="harvest_harvester" || $key =="harvest_hauling"){
                                   $total_machine += $value_data;
                                   $grand += $value_data;
                              }
                              if($key == "land_prep_rental_ha" || $key == "seed_bed_rental_ha" || $key == "direct_seed_rental_ha" || $key =="mech_rental_ha" || $key=="harvest_tresher_ha" || $key =="harvest_harvester_ha" || $key =="harvest_hauling_ha" ){
                                   $total_machine_ha += $value_data;
                                   $grand_ha += $value_data;
                              }

                              if($key=="fert_mgt_cost" || $key == "seed_tray_fert" || $key =="seed_mgt_fert"){$total_fert += $value_data; $grand += $value_data;}
                              if($key=="fert_mgt_cost_ha" || $key == "seed_tray_fert_ha" || $key=="seed_mgt_fert_ha"){$total_fert_ha += $value_data; $grand_ha += $value_data;}
 
                              if($key=="pest_mgt_mollus" || $key == "pest_mgt_insect" || $key=="pest_mgt_fungi" || $key == "pest_mgt_roden" || $key == "weed_mgt_herbicide"){
                                   $total_chemical += $value_data; $grand += $value_data;}
                              if($key=="pest_mgt_mollus_ha" || $key == "pest_mgt_insect_ha" || $key=="pest_mgt_fungi_ha" || $key == "pest_mgt_roden_ha" || $key == "weed_mgt_herbicide_ha"){
                                   $total_chemical_ha += $value_data; $grand_ha += $value_data;}

                              if($key == "land_prep_meals" || $key == "seed_mgt_meals" || $key == "direct_seed_meal" || $key =="trans_meal" || $key == "mech_meals" || $key == "fert_mgt_meals" || $key == "wtr_mgt_meals" || $key=="pest_mgt_meals" || $key == "harvest_meals" || $key =="weed_mgt_meals"){
                                   $total_meals += $value_data;
                                   $grand += $value_data;
                              }
                               if($key == "land_prep_meals_ha" || $key == "seed_mgt_meals_ha" || $key == "direct_seed_meal_ha" || $key =="trans_meal_ha" || $key == "mech_meals_ha" || $key == "fert_mgt_meals_ha" || $key == "wtr_mgt_meals_ha" || $key=="pest_mgt_meals_ha" || $key == "harvest_meals_ha" || $key =="weed_mgt_meals_ha"){
                                   $total_meals_ha += $value_data;
                                   $grand_ha += $value_data;
                              }
                              
                              if($key == "wtr_mgt_labor" || $key == "wtr_mgt_irr" || $key =="wtr_mgt_fuel"){
                                   $total_irr += $value_data;
                                   $grand += $value_data;
                              }

                              if($key == "wtr_mgt_labor_ha" || $key == "wtr_mgt_irr_ha" || $key =="wtr_mgt_fuel_ha"){
                                   $total_irr_ha += $value_data;
                                   $grand_ha += $value_data;
                              }

                              if($key == "oth_land"){
                                   $land_cost += $value_data;
                                   $grand += $value_data;
                              }

                              if($key == "oth_interest"){
                                   $total_interest += $value_data;
                                   $grand += $value_data;
                              }

                              if($key == "oth_matt"){
                                $total_cost += $value_data;
                                $grand += $value_data;
                              }


                              if($key == "oth_land_ha" || $key == "oth_interest_ha"){
                                   $total_cost_ha += $value_data;
                                   $grand_ha += $value_data;
                              }
                         


                              $metro_grand += $grand;
                              $metro_meals += $total_meals;
                              $metro_cost += $total_cost;
                              $metro_land += $land_cost;
                              $metro_interest += $total_interest;
                    }

            
                        $act = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.lib_planting")->where("planting_id", $key_id)->first();
                        $act_head = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.lib_planting")->where("activity_code", $act->activity_head)->value("activities");


                            array_push($cost_array, array(
                            "farmrsn" => $farmrsn,
                            "user" =>$user,
                            "prristation" => $prristation,
                            "fpid" => $fpid,
                            "season" => $season,
                            "region" => $region,
                            "prov" =>$prov,
                            "mun" => $mun,
                            "brgy" => $brgy,
                            "Activity" => $act_head,
                            "Subact" => $act->activities,
                            "hired_workers" => "",
                            "hired_days" => "",
                            "hired_hrsday" => "",
                            "hired_totcost" => "",
                            "ofe_workers" => "",
                            "ofe_days" => "",
                            "ofe_hrsday" =>"" ,
                            "ofe_wagerate" => "",
                            "ofe_totcost" => "",
                            "seed_qty" => "",
                            "seed_unit" => "",
                            "seed_punit" => "",
                            "seed_totcost" => "",
                            "fert_kind" => "",
                            "fert_qty" => "",
                            "fert_unit" => "",
                            "fert_punit" => "",
                            "fert_totcost" => "",
                            "chem_kind" => "",
                            "chem_qty" => "",
                            "chem_unit" => "",
                            "chem_punit" => "",
                            "chem_totcost" => "",
                            "irrigation_power" => "",
                            "irrigation_qty" => "",
                            "irrigation_unit" => "",
                            "irrigation_punit" => "",
                            "irrigation_totcost" => "",
                            "foodcost" => $total_meals,
                            "othercost" => $total_cost,
                            "landrent" => $land_cost,
                            "intcapital" => $total_interest,
                            "totalexp" => $grand, 
                        ));
            }


            array_push($cr_array, array(
                        "fpid" =>  $fpid,
                        "tdarea" => $tdarea,
                        "actualgrossinc" => $actualgrossinc,
                        "hired_totcost" => "",
                        "ofe_totcost" => "",
                        "seed_totcost" => "",
                        "fert_totcost" => "",
                        "chem_totcost" => "",
                        "irrigation_totcost" => "",
                        "foodcost" => $metro_meals,
                        "othercost" => $metro_cost,
                        "landrent" => $metro_land,
                        "intcapital" => $metro_interest,
                        "totalexp" => $metro_grand, 

                    ));
            





            array_push($documentation, array("headers" => "farmrsn","remarks" => "",));
                array_push($documentation, array("headers" => "user","remarks" => "",));
                array_push($documentation, array("headers" => "prristation","remarks" => "",));
                array_push($documentation, array("headers" => "fpid","remarks" => "",));
                array_push($documentation, array("headers" => "season","remarks" => "",));
                array_push($documentation, array("headers" => "region","remarks" => "",));
                array_push($documentation, array("headers" => "prov","remarks" => "",));
                array_push($documentation, array("headers" => "mun","remarks" => "",));
                array_push($documentation, array("headers" => "brgy","remarks" => "",));
                array_push($documentation, array("headers" => "varplant","remarks" => "",));
                array_push($documentation, array("headers" => "cropest","remarks" => "",));
                array_push($documentation, array("headers" => "tdarea","remarks" => "ha",));
                array_push($documentation, array("headers" => "cropcut1","remarks" => "kg",));
                array_push($documentation, array("headers" => "cropcut2","remarks" => "kg",));
                array_push($documentation, array("headers" => "cropcut3","remarks" => "kg",));
                array_push($documentation, array("headers" => "cropcut_tot","remarks" => "kg",));
                array_push($documentation, array("headers" => "cropcutyield","remarks" => "kg/ha",));
                array_push($documentation, array("headers" => "initialmc","remarks" => "",));
                array_push($documentation, array("headers" => "cropcutyield14mc","remarks" => "kg/ha",));
                array_push($documentation, array("headers" => "actualharv","remarks" => "tons",));
                array_push($documentation, array("headers" => "actualyield","remarks" => "tons/ha",));
                array_push($documentation, array("headers" => "actualinitialmc","remarks" => "",));
                array_push($documentation, array("headers" => "actualyield14mc","remarks" => "",));
                array_push($documentation, array("headers" => "actualcostkg","remarks" => "cost/kg",));
                array_push($documentation, array("headers" => "actualpricekg","remarks" => "price/kg",));
                array_push($documentation, array("headers" => "actualgrossinc","remarks" => "",));
                array_push($documentation, array("headers" => "hired_workers","remarks" => "",));
                array_push($documentation, array("headers" => "hired_days","remarks" => "",));
                array_push($documentation, array("headers" => "hired_hrsday","remarks" => "",));
                array_push($documentation, array("headers" => "hired_totcost","remarks" => "",));
                array_push($documentation, array("headers" => "ofe_workers","remarks" => "",));
                array_push($documentation, array("headers" => "ofe_days","remarks" => "",));
                array_push($documentation, array("headers" => "ofe_hrsday","remarks" => "",));
                array_push($documentation, array("headers" => "ofe_wagerate","remarks" => "",));
                array_push($documentation, array("headers" => "seed_qty","remarks" => "",));
                array_push($documentation, array("headers" => "seed_unit","remarks" => "",));
                array_push($documentation, array("headers" => "seed_punit","remarks" => "",));
                array_push($documentation, array("headers" => "seed_totcost","remarks" => "",));
                array_push($documentation, array("headers" => "fert_kind","remarks" => "",));
                array_push($documentation, array("headers" => "fert_qty","remarks" => "",));
                array_push($documentation, array("headers" => "fert_unit","remarks" => "",));
                array_push($documentation, array("headers" => "fert_punit","remarks" => "",));
                array_push($documentation, array("headers" => "fert_totcost","remarks" => "",));
                array_push($documentation, array("headers" => "chem_kind","remarks" => "",));
                array_push($documentation, array("headers" => "chem_qty","remarks" => "",));
                array_push($documentation, array("headers" => "chem_unit","remarks" => "",));
                array_push($documentation, array("headers" => "chem_punit","remarks" => "",));
                array_push($documentation, array("headers" => "chem_totcost","remarks" => "",));
                array_push($documentation, array("headers" => "irrigation_power","remarks" => "",));
                array_push($documentation, array("headers" => "irrigation_qty","remarks" => "",));
                array_push($documentation, array("headers" => "irrigation_unit","remarks" => "",));
                array_push($documentation, array("headers" => "irrigation_punit","remarks" => "",));
                array_push($documentation, array("headers" => "irrigation_totcost","remarks" => "",));
                array_push($documentation, array("headers" => "foodcost","remarks" => "",));
                array_push($documentation, array("headers" => "othercost","remarks" => "",));
                array_push($documentation, array("headers" => "landrent","remarks" => "",));
                array_push($documentation, array("headers" => "intcapital","remarks" => "",));
                array_push($documentation, array("headers" => "totalexp","remarks" => "",));

    }

     $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
            $documentation = json_decode(json_encode($documentation), true); //convert collection to associative array to be converted to excel
            $cr_array = json_decode(json_encode($cr_array), true);

            return Excel::create("Palaysikatan_table"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $documentation, $cost_array, $cr_array) {
                $excel->sheet("Documentation", function($sheet) use ($documentation) {
                    $sheet->fromArray($documentation);
                }); 
                $excel->sheet("ProdYieldInc", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                }); 
                $excel->sheet("Costs", function($sheet) use ($cost_array) {
                    $sheet->fromArray($cost_array);
                }); 
                $excel->sheet("C&R", function($sheet) use ($cr_array) {
                    $sheet->fromArray($cr_array);
                }); 
                
            })->download('xlsx'); 




}



}

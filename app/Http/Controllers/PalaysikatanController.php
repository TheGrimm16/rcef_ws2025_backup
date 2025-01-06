<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use DB;
use Hash;
use Auth;
use Yajra\Datatables\Datatables;
use Validator;
use Session;
use Excel;

class PalaysikatanController extends Controller
{
    public function __construct()
    {
     $this->middleware('auth');
     $this->geotag_con = 'geotag_db';
    }
    
    public function farmers_datatable_filter(Request $request){
          
     $province = $request->province;

     if(Auth::user()->province == "" && !isset($province)){
          $province = "No Province";
     }
     if(!isset($province)){
          if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso" || Auth::user()->roles->first()->name == "techno_demo_officer"){
               $province = "%";
             
          }
     }
     

        $query = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
        ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'crop_production.farmer_id_fk', '=','farmer_info.fid')
        ->select("farmer_info.*",'crop_production.*')
        ->where('add_province', "like",$province);
        //>get();

        return Datatables::of($query)
          ->addColumn('actions', function ($query) {
               $planting = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.data_entries')->where('farmer_id', $query->fid)->exists();
               $material = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.material_entries')->where('farmer_id', $query->fid)->exists();
               $href = '<a href="'. url("palaysikatan/farmer/edit/$query->fid") .'" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-eye-open"></i> View Information</a>';



                if($material){
                    $href .= '<a href="'. url("palaysikatan/farmer/material/$query->fid") .'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-eye-open"></i> View Material Form</a>';
               }else{
                    $href .= '<a href="'. url("palaysikatan/farmer/material/$query->fid") .'" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add Material Form</a>';
               }


               if($planting){
                    $href .= '<a href="'. url("palaysikatan/form/planting/$query->fid") .'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-eye-open"></i> View Production Activities</a>';
               }else{
                    $href .= '<a href="'. url("palaysikatan/form/planting/$query->fid") .'" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add Production Activities</a>';
               }
             /*   if(Auth::user()->username == "justine.ragos"){ */
                    $href .= '<a data-id="'.$query->fid.'" href="#" class="btn btn-sm btn-danger deleteFca" onclick="deleteFca('.$query->fid.')"><i class="fa fa-trash"></i></a>';
              /*  } */
               
               
               return $href;
          })
          ->make(true);
   }
    public function get_station_province(Request $request){
          return $data = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.lib_province")->where('station',$request->station)->get();
     }

    public function tdoNtEncode_get_one(Request $request){
    $data = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.tdo_tagets")->where('username',$request->username)->first();
    return $data->target;
    }
     public function tdoNtEncode(Request $request){
     DB::beginTransaction();
     try {
          if($request->state=="add"){
               DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.tdo_tagets")
               ->insert([
                    "username" => $request->username,
                    "target" => $request->ntencode,
                    "added_by" => Auth::user()->username
               ]);
          }else{
               DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.tdo_tagets")
                    ->where("username", $request->username)
                    ->update(["target" => $request->ntencode,
                    "added_by" => Auth::user()->username
                    ]);
          }          
        
     DB::commit();
            return "true";
     }catch (\Exception $e) {
         // dd($e->getMessage());
          DB::rollback();
          return json_encode("DB ERROR MISMATCH");
          //return json_encode($e->getMessage());
     } 
     }
     public function station_status_list_encoded(Request $request){
   
          $data = "";
          $href="";
          $femaleData=0;

       
          
          $data=DB::table('users')
          ->join('role_user','role_user.userId' , '=','users.userId')
          ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries','station_entries.user_encoded', '=','users.username')
          ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->select('station_entries.user_encoded','users.province','users.firstName','station_entries.province_tag','users.lastName','users.username','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'users.username as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
          ->where('role_user.roleId', 25)   
          ->where('users.stationId',$request->station)
          ->groupBy('users.username')
          ->get();
          //dd($data);
           $lib_planting =DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_planting')->get();
          $Final_data = array();
          foreach ($data as $value) { // get user
               
                $data_entries=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.data_entries')
               ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries','station_entries.ref_id' , '=','data_entries.farmer_id')
               ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_planting','lib_planting.planting_id' , '=','data_entries.planting_id')
               ->select('data_entries.*','station_entries.*','lib_planting.activity_head')
               ->where('station_entries.user_encoded',$value->user_encoded)
               //->where('station_entries.user_encoded','juvy_ann.bataan')
               ->groupby('station_entries.ref_id','lib_planting.activity_head')
               ->get();  
             //dd($data_entries);              

             $province=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_prv')->where('prv_code', $value->province_tag)->first();

             $tdo_tagets=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.tdo_tagets')->where('username',$value->user_encoded)->first();  
               if(count($tdo_tagets)>0){
                    $noNeedToEncode = $tdo_tagets->target;
               }else{
                    $noNeedToEncode=0;
               }


               $datatmp = array();

               $datatmp =[
                    'province' =>"",
                    'username' =>"",
                    'Basis' =>"",
                    'encoded' =>"",
                    'land_preparation' => 0,
                    'seedbed_preparation' => 0,
                    'seedling_try_preparation' => 0,
                    'seedling_management' => 0,
                    'seedbed_fertilization' => 0,
                    'crop_establishment' => 0,
                    'ce_transplanting' => 0,
                    'ce_mechanized_planting' => 0,
                    'fertilized_management' => 0,
                    'water_management' => 0,
                    'pest_management' => 0,
                    'weed_management' => 0,
                    'harvest_management' => 0,
                    'other_expenses' => 0,
               ];
               $fid=0;  
               $ids=""; 
                    
               
               $encoded=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
               ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.data_entries','data_entries.farmer_id' , '=','station_entries.ref_id')
               ->select(DB::raw("count(station_entries.farmer_id) as encodedData"))
               ->where('station_entries.user_encoded',$value->user_encoded)
               ->groupBy('station_entries.farmer_id','station_entries.user_encoded')
               ->get();
            
                foreach ($data_entries as $entri) {  // get user encoded
                    $fid2 =$entri->ref_cp_id; // new farmer in loop
                    $datatmp["username"] = $value->firstName ." ".$value->lastName;
                    $datatmp["province"] = $province->province;
                    $datatmp["Basis"] = $noNeedToEncode;
                    $datatmp["encoded"] = count($encoded);
                    foreach ($lib_planting as  $lib_planting_data) { //get type of encoded
                         if($lib_planting_data->planting_id == $entri->planting_id){
                           
                              $keyData = $lib_planting_data->activity_head;
                              if($lib_planting_data->activity_head == "land_prep"){
                                   
                                   $keyData = 'land_preparation';                                 
                              }else if($lib_planting_data->activity_head == "seedling_tray_prep"){
                                   $keyData = 'seedling_try_preparation';
                              }else if($lib_planting_data->activity_head == "seedbed_ferti" || $lib_planting_data->activity_head == "seeedbed_fertilization"){
                                   $keyData = 'seedbed_fertilization';
                              }else if($lib_planting_data->activity_head == "crop_establish" || $lib_planting_data->activity_head == "crop_management"){
                                    $keyData = 'crop_establishment';
                              }else if($lib_planting_data->activity_head == "mechanized_planting"){
                                    $keyData = 'ce_mechanized_planting';
                              }else if($lib_planting_data->activity_head == "fertilizer_management"){
                                    $keyData = 'fertilized_management';
                              }  

                              if($datatmp[$keyData]>0){  

                                        if($lib_planting_data->activity_head == "land_prep"){
                                             $ids .="&&". $entri->farmer_id;
                                             $datatmp['land_preparation'] += 1;
                                        }else if($lib_planting_data->activity_head == "seedling_tray_prep"){
                                             $datatmp['seedling_try_preparation'] += 1;
                                        }else if($lib_planting_data->activity_head == "seedbed_ferti" || $lib_planting_data->activity_head == "seeedbed_fertilization"){
                                             $datatmp['seedbed_fertilization'] += 1;
                                        }else if($lib_planting_data->activity_head == "crop_establish" || $lib_planting_data->activity_head == "crop_management"){
                                             $datatmp['crop_establishment'] += 1;
                                        }else if($lib_planting_data->activity_head == "mechanized_planting"){
                                             $datatmp['ce_mechanized_planting'] += 1;
                                        }else if($lib_planting_data->activity_head == "fertilizer_management"){
                                             $datatmp['fertilized_management'] += 1;
                                        }else{
                                             $datatmp[$lib_planting_data->activity_head] += 1;
                                        }                                                                                                
                              }else{
                                  
                                   if($lib_planting_data->activity_head == "land_prep"){   
                                                                          
                                        $datatmp['land_preparation'] = 1;
                                   }else if($lib_planting_data->activity_head == "seedling_tray_prep"){
                                        $datatmp['seedling_try_preparation'] = 1;
                                   }else if($lib_planting_data->activity_head == "seedbed_ferti" || $lib_planting_data->activity_head == "seeedbed_fertilization"){
                                        $datatmp['seedbed_fertilization'] = 1;
                                   }else if($lib_planting_data->activity_head == "crop_establish" || $lib_planting_data->activity_head == "crop_management"){
                                        $datatmp['crop_establishment'] = 1;
                                   }else if($lib_planting_data->activity_head == "mechanized_planting"){
                                        $datatmp['ce_mechanized_planting'] = 1;
                                   }else if($lib_planting_data->activity_head == "fertilizer_management"){
                                        $datatmp['fertilized_management'] = 1;
                                   }else{
                                        $datatmp[$lib_planting_data->activity_head] = 1;
                                   }
                                   
                               
                              }    
                                              
                         }                        
                    }
                   
                    $fid = $entri->ref_cp_id;  
                } 

                if($datatmp["username"] != ""){
                    array_push($Final_data, $datatmp);
                }
                
          }
       //   dd($Final_data);
          $table  =collect($Final_data);
            return Datatables::of($table)
            
                ->make(true);
          }
     public function station_status_list(Request $request){
   
          $data = "";
          $href="";
          $femaleData=0;
        
     
          if($request->encoder == "ALL" &&  $request->station==0){       
               $data=DB::table('users')
               ->join('role_user','role_user.userId' , '=','users.userId')
               ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries','station_entries.user_encoded', '=','users.username')
               ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
               ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
               ->select('station_entries.user_encoded','users.username','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'users.username as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
               ->where('role_user.roleId', 25)     
               ->groupBy('users.username');
            
          }else{
               $data=DB::table('users')
               ->join('role_user','role_user.userId' , '=','users.userId')
               ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries','station_entries.user_encoded', '=','users.username')
               ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
               ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
               ->select('station_entries.user_encoded','users.username','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'users.username as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
               ->where('role_user.roleId', 25)   
               ->where('users.stationId',$request->station)
               ->groupBy('users.username');
     
               
          }
          return Datatables::of($data)      
           ->addColumn('encoder', function($row){    
             return $row->encoder;
     
           })
           ->addColumn('noOfEncoded', function($row){      
             return $row->encoded;
           })
           ->addColumn('noNeedToEncode', function($row){ 
                $noNeedToEncode = 0;
                if(isset($row->encoder)){
                    $tdo_tagets=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.tdo_tagets')->where('username',$row->encoder)->first();  
                    if(count($tdo_tagets)>0){
                         $noNeedToEncode = $tdo_tagets->target;
                    }else{
                         $noNeedToEncode=0;
                    }
                }else{
                    $noNeedToEncode=0;
                }
              
               return $noNeedToEncode;
             })
           ->addColumn('area', function($row){      
               return $row->techno_area;
             })
             ->addColumn('male', function($row){ 
                // dd($row->station);   
     
                if(isset($row->user_encoded)){
                    $male=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
                    ->where('station_entries.station',$row->station)
                    ->where('station_entries.user_encoded',$row->user_encoded)
                    ->where('sex','male')          
                    ->groupBy('station_entries.user_encoded')
                    ->count();
               }else{
                    $male=0;
               }
              
             //  dd($male);
               
               return $male;
             })
             ->addColumn('female', function($row){ 
               if(isset($row->user_encoded)){
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
               }else{
                    $female=0;
               }
              
     
               return $female;
             })
             ->addColumn('total', function($row){
                  
               if(isset($row->user_encoded)){
                    $totalSex=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
                    ->where('station_entries.station',$row->station)
                    ->where('station_entries.user_encoded',$row->user_encoded)
                    ->Orwhere('sex','female')          
                    ->where('sex','male')          
                    ->groupBy('station_entries.user_encoded')
                    ->count();
               }else{
                    $totalSex=0;
               }
               
             //  dd($totalSex);
               return $totalSex;
             })
             ->addColumn('action', function($row){  
                  
             
     
                    $data=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.tdo_tagets')->where('username', $row->encoder)->first()  ;
                    if(count($data)>0){
                       //  $href = '<button style="width:50%" onclick="tdoEncoded_update(\''.$row->user_encoded.'\',\'update\')" class="btn btn-sm btn-warning"><i class=""></i>Update</button>';
                         $href = '<button onclick="tdoEncoded_update(\''.$row->encoder.'\',\'update\')" class="btn btn-sm btn-warning"><i class=""></i>Update</button>';
          
                    } else{
                         $href = '<button onclick="tdoEncoded(\''.$row->encoder.'\',\'add\')" class="btn btn-sm btn-info"><i class=""></i>Add Target</button>';
                    } 
             
     
               return $href;
             })
           ->make(true);
          }
   /*  public function station_status_list(Request $request){
   
     $data = "";
     $href="";
     $femaleData=0;
   
     if($request->encoder == "ALL" &&  $request->station==0){
          $data=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->select('station_entries.user_encoded','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'station_entries.user_encoded as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
          ->groupBy('station_entries.user_encoded');
       
     }else{
          $data=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info','farmer_info.farmer_id' , '=','station_entries.farmer_id')
          ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production','farmer_info.fid' , '=','crop_production.farmer_id_fk')
          ->select('station_entries.user_encoded','station_entries.station',DB::raw("count(farmer_info.farmer_id) as encoded"),'station_entries.user_encoded as encoder',DB::raw("sum(crop_production.techno_area) as techno_area"))
          ->where('station_entries.station',$request->station)
          ->groupBy('station_entries.user_encoded');
     }
     return Datatables::of($data)      
      ->addColumn('encoder', function($row){    
        return $row->encoder;

      })
      ->addColumn('noOfEncoded', function($row){      
        return $row->encoded;
      })
      ->addColumn('noNeedToEncode', function($row){ 
           $noNeedToEncode = 0;
          $tdo_tagets=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.tdo_tagets')->where('username',$row->user_encoded)->first();  
          if(count($tdo_tagets)>0){
               $noNeedToEncode = $tdo_tagets->target;
          }
          return $noNeedToEncode;
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
        ->addColumn('action', function($row){     
          $data=DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.tdo_tagets')->where('username', $row->user_encoded)->first()  ;
          if(count($data)>0){
               $href = '<button style="width:50%" onclick="tdoEncoded_update(\''.$row->user_encoded.'\',\'update\')" class="btn btn-sm btn-warning"><i class=""></i>Update</button>';
          } else{
               $href = '<button onclick="tdoEncoded(\''.$row->user_encoded.'\',\'add\')" class="btn btn-sm btn-info"><i class=""></i>Add Target</button>';
          } 

          return $href;
        })
      ->make(true);
     } */
    public function tdoList(Request $request){
         
     $stations = DB::connection($this->geotag_con)
     ->table('tbl_station')
    // ->orderBy('stationName', 'asc')
     ->get();
     return view('palaysikatan.tdo')
     ->with("stations", $stations);
     }
    
     public function tdoListEncoded(Request $request){
         
          $stations = DB::connection($this->geotag_con)
          ->table('tbl_station')
         // ->orderBy('stationName', 'asc')
          ->get();
          return view('palaysikatan.tdo-encoded')
          ->with("stations", $stations);
          }
          

    public function farmers_delete(Request $request){

     DB::beginTransaction();
     try {
      
            DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')->where('fid', $request->fcaId)->delete();   
            DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.station_entries')->where('ref_id', $request->fcaId)->delete();
            
            DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
            ->insert([
                 "farmer_id" => $request->fcaId,
                 "data_ins" => "delete",
                 "module" => "all data",
                 "action" => "delete",
                 "user" => Auth::user()->username
            ]);

            DB::commit();
            return "true";
     }catch (\Exception $e) {
         // dd($e->getMessage());
          DB::rollback();
          return json_encode("DB ERROR MISMATCH");
          //return json_encode($e->getMessage());
     }

    }

    public function insert_material(Request $request){
               DB::beginTransaction();
          try {
               $farmer_id = $request->farmer_id;
               $mid_details = explode(";", $request->ins_string);

                    foreach ($mid_details as $mid) {
                         $data = explode(",", $mid);

                               $rec = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
                                        ->where("farmer_id", $farmer_id)
                                        ->where("material_id", $data[0])
                                        ->first();

                                   if(count($rec)>0){
                                        //UPDATE
                                        DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
                                        ->where("id", $rec->id)
                                        ->update([
                                             "item" => $data[1],
                                             "qty_sa" => $data[2],
                                             "unit_sa" => $data[3],
                                             "kg_lg_sa" => $data[4],
                                             "qty_fa" => $data[5],
                                             "unit_fa" => $data[6],
                                             "kg_lg_fa" => $data[7],
                                             "price" => $data[8],
                                             "notes" => $data[9]
                                        ]);
                                   }else{
                                        //INSERT
                                        $entry_id= DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
                                         ->insertGetId([
                                             "farmer_id" => $farmer_id,
                                             "material_id" => $data[0],
                                             "item" => $data[1],
                                             "qty_sa" => $data[2],
                                             "unit_sa" => $data[3],
                                             "kg_lg_sa" => $data[4],
                                             "qty_fa" => $data[5],
                                             "unit_fa" => $data[6],
                                             "kg_lg_fa" => $data[7],
                                             "price" => $data[8],
                                             "notes" => $data[9]
                                         ]);
                                        
                                   }
                    }


                     $rec = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
                                        ->where("farmer_id", $farmer_id)
                                        ->first();
                    if(count($rec)>0){
                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
                         ->insert([
                              "farmer_id" => $farmer_id,
                              "data_ins" => $request->ins_string,
                              "module" => "material",
                              "action" => "update",
                              "user" => Auth::user()->username
                         ]);
                    }else{
                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
                         ->insert([
                              "farmer_id" => $farmer_id,
                              "data_ins" => $request->ins_string,
                              "module" => "material",
                              "action" => "insert",
                              "user" => Auth::user()->username
                         ]);
                    }

              //check 
               DB::commit();
                 return json_encode("true");
          }catch (\Exception $e) {
              // dd($e->getMessage());
               DB::rollback();
               return json_encode("DB ERROR MISMATCH");
               //return json_encode($e->getMessage());
          }
    }


     public function material_form($id){
     $farmer = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
          ->select('*')
          ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'farmer_info.fid', '=', 'crop_production.farmer_id_fk')
          ->where("fid", $id)
          ->first();

     $activity_header = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_materials')
          ->select('material_desc', 'material_code')
          ->where([
               "is_active" => '1',
               "group_order" => 1,
          ])
          ->groupby('material_header')
          ->orderBy('order_all')

          ->get();


     $form_data =  DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_materials')
          ->select('*')
          ->where([
               "is_active" => '1'
          ])
          ->orderBy('order_all')
          ->orderBy('group_order')
          ->get();

     $data_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
          ->where("farmer_id", $id)
          ->get();

     $data_entry = array();
     foreach ($data_entries as $entry) {
          $data_entry[$entry->material_id]['item'] = $entry->item;

          $data_entry[$entry->material_id]['qty_sa'] = $entry->qty_sa;
          $data_entry[$entry->material_id]['unit_sa'] = $entry->unit_sa;
          $data_entry[$entry->material_id]['kg_lg_sa'] = $entry->kg_lg_sa;
          $data_entry[$entry->material_id]['qty_fa'] = $entry->qty_fa;
          $data_entry[$entry->material_id]['unit_fa'] = $entry->unit_fa;
          $data_entry[$entry->material_id]['kg_lg_fa'] = $entry->kg_lg_fa;
          $data_entry[$entry->material_id]['price'] = $entry->price;
          $data_entry[$entry->material_id]['notes'] = $entry->notes;
     }
  
               return view('palaysikatan.forms.farmer_materials')
                    ->with('farmer', $farmer)
                    ->with('form_data', $form_data)
                    ->with('id', $id)
                    ->with('activity_header',$activity_header)
                    ->with('data_entry', $data_entry);
   
   }



    public function insert_planting(Request $request){
               DB::beginTransaction();
          try {
               $farmer_id = $request->farmer_id;
                $pid_details = explode(";", $request->ins_string);

                    foreach ($pid_details as $pid) {
                            $data = explode(",", $pid);
                         if($data[0]==49){
                              //return $pid;
                         }
                               $rec = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                                        ->where("farmer_id", $farmer_id)
                                        ->where("planting_id", $data[0])
                                        ->first();

                                   if(count($rec)>0){
                                        //UPDATE
                                        DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                                        ->where("id", $rec->id)
                                        ->update([
                                             "qty" => $data[1],
                                             "unit" => $data[2],
                                             "unit_cost" => $data[3],
                                             "fertilizer_category" => $data[5],
                                             "date" => $data[6],
                                             "remarks" => $data[7]
                                        ]);
                                   }else{
                                        //INSERT
                                        $entry_id=  DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                                         ->insertGetId([
                                             "farmer_id" => $farmer_id,
                                             "planting_id" => $data[0],
                                             "qty" => $data[1],
                                             "unit" => $data[2],
                                             "unit_cost" => $data[3],
                                             "fertilizer_category" => $data[5],
                                             "date" => $data[6],
                                             "remarks" => $data[7]
                                         ]);
                                        
                                   }
                    }


                    $rec = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                                        ->where("farmer_id", $farmer_id)
                                        ->first();
                    if(count($rec)>0){
                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
                         ->insert([
                              "farmer_id" => $farmer_id,
                              "data_ins" => $request->ins_string,
                              "module" => "planting",
                              "action" => "update",
                              "user" => Auth::user()->username
                         ]);
                    }else{
                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
                         ->insert([
                              "farmer_id" => $farmer_id,
                              "data_ins" => $request->ins_string,
                              "module" => "planting",
                              "action" => "insert",
                              "user" => Auth::user()->username
                         ]);
                    }





              //check 
               DB::commit();
                 return json_encode("true");
          }catch (\Exception $e) {
              // dd($e->getMessage());
               DB::rollback();
               return json_encode("DB ERROR MISMATCH");
          }
    }

     public function farmers_view(){
          $stations = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->orderBy('stationName', 'asc')
        ->get();
        return view('palaysikatan.farmers')
          ->with("export_link", 'palaysikatan/export/report/'.Auth::user()->stationId)
          ->with("report_matrix", 'palaysikatan/export/report_matrix/'.Auth::user()->stationId)
          ->with("export_link_table", 'palaysikatan/dashboard/export')
          ->with("stations", $stations);
                  
     }


   public function farmers_datatable(){

     $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
          ->where('prv', 'like',Auth::user()->province."%")
          ->first();

     $province = $province->province;

     if(Auth::user()->province == ""){
          $province = "No Province";
     }

     if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso"){
          $province = "%";
     }



        $query = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
        ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'crop_production.farmer_id_fk', '=','farmer_info.fid')
        ->select("farmer_info.*",'crop_production.techno_area')
        ->where('add_province', "like",$province);
        //>get();

        return Datatables::of($query)
          ->addColumn('actions', function ($query) {
               $planting = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.data_entries')->where('farmer_id', $query->fid)->exists();
               $material = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.material_entries')->where('farmer_id', $query->fid)->exists();
               $href = '<a href="'. url("palaysikatan/farmer/edit/$query->fid") .'" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-eye-open"></i> View Information</a>';



                if($material){
                    $href .= '<a href="'. url("palaysikatan/farmer/material/$query->fid") .'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-eye-open"></i> View Material Form</a>';
               }else{
                    $href .= '<a href="'. url("palaysikatan/farmer/material/$query->fid") .'" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add Material Form</a>';
               }


               if($planting){
                    $href .= '<a href="'. url("palaysikatan/form/planting/$query->fid") .'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-eye-open"></i> View Production Activities</a>';
               }else{
                    $href .= '<a href="'. url("palaysikatan/form/planting/$query->fid") .'" class="btn btn-sm btn-success"><i class="glyphicon glyphicon-plus"></i> Add Production Activities</a>';
               }
              /*  if(Auth::user()->username == "justine.ragos"){ */
                    $href .= '<a data-id="'.$query->fid.'" href="#" class="btn btn-sm btn-danger deleteFca" onclick="deleteFca('.$query->fid.')"><i class="fa fa-trash"></i></a>';
            /*    } */
               
               
               return $href;
          })
          ->make(true);
   }

   public function edit_farmer($id){
      $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
          ->where('prv', 'like',Auth::user()->province."%")
          ->first();
     $region = $province->regionName;
     if(Auth::user()->province == ""){
          $region = "No region";
     }



      $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
          ->where("regionName", $region)
          ->groupBy("regionName")
          ->orderBy("region_sort")
          ->get();
     $tenure_status = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "tenure_status")
          ->get();

     $crop_establishment = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "crop_establishment")
          ->get();
     
     $seed_class = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "seed_class")
          ->get();
     
     $irrigation_source = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "irrigation_source")
          ->get();

     $seed_source = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "seed_source")
          ->get();


     $farmer_info = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production", "crop_production.farmer_id_fk", "=", "farmer_info.fid")
          ->where("farmer_info.fid", $id)
          ->first();

      $variety_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.seed_variety")
          ->select("variety as seedVariety")
          ->groupBy("variety")
          ->get();


     if($farmer_info->variety_planted!=""){
          $variety_encoded = explode(",", $farmer_info->variety_planted);
               if(isset($variety_encoded[0])){
                    $variety_1 = $variety_encoded[0];
               }else{$variety_1 = "";}

               if(isset($variety_encoded[1])){
                    $variety_2 = $variety_encoded[1];
               }else{$variety_2 = "";}
               
               if(isset($variety_encoded[2])){
                    $variety_3 = $variety_encoded[2];
               }else{$variety_3 = "";} 
     }else{
          $variety_1 = "";
          $variety_2 = "";
          $variety_3 = "";
     }



     if(count($farmer_info)>0){
          return view('palaysikatan.forms.farmer_edit')
               ->with('farmer_info', $farmer_info)
               ->with('regions', $regions)
               ->with('tenure_status', $tenure_status)
               ->with('crop_establishment', $crop_establishment)
               ->with('seed_class', $seed_class)
               ->with('irrigation_source', $irrigation_source)
               ->with('seed_source', $seed_source)
               ->with('seed_variety', $variety_list)
               ->with('variety_1', $variety_1)
               ->with('variety_2', $variety_2)
               ->with('variety_3', $variety_3);
                   
     }else{
           
          return  redirect(route('palaysikatan.farmers'));
     }

   }


   public function farmer_form(){

     $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
          ->where('prv', 'like', Auth::user()->province."%")
          ->first();
          
     $region = $province->regionName;
     if(Auth::user()->province == ""){
          $region = "No region";
     }


     $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
          ->where("regionName", $region)
          ->groupBy("regionName")
          ->orderBy("region_sort")
          ->get();

     $tenure_status = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "tenure_status")
          ->get();

     $crop_establishment = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "crop_establishment")
          ->get();
     
     $seed_class = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "seed_class")
          ->get();
     
     $irrigation_source = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "irrigation_source")
          ->get();

     $seed_source = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where("form_group", "seed_source")
          ->get();
/* 
     $variety_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
          ->select("seedVariety")
          ->where("seedVariety", "!=", "")
          ->where("seedVariety", "!=", "0")
          ->groupBy("seedVariety")
          ->get(); */

          $variety_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.seed_variety")
          ->select("variety as seedVariety")
          ->groupBy("variety")
          ->get();


     
     return view('palaysikatan.forms.farmer_info')
          ->with('regions', $regions)
          ->with('tenure_status', $tenure_status)
          ->with('crop_establishment', $crop_establishment)
          ->with('seed_class', $seed_class)
          ->with('irrigation_source', $irrigation_source)
          ->with('seed_source', $seed_source)
          ->with('seed_variety', $variety_list);

   }

   public function seedVariety(Request $request){


     

     $search = $request->search;

     if($search == ''){
        $variety_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.seed_variety")->select('variety')
        ->limit(5)->get();
     }else{
          $variety_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.seed_variety")->select('variety')
          ->where('variety', 'like', '%' .$search . '%')
          ->limit(20)->get();
     }
     $response = array();
     foreach($variety_list as $data){
        $response[] = array(
             "id"=>$data->variety,
             "text"=>$data->variety
        );
     }

     echo json_encode($response);
     exit;
  }

   public function save_date(Request $request){      
          $date_sown = date("Y-m-d", strtotime($request->date_sown));
          $date_transplanted = date("Y-m-d", strtotime($request->date_transplanted));

          $crop = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
               ->where("farmer_id_fk", $request->id)
               ->update([
                    "date_sown" => $date_sown,
                    "date_transplanted" => $date_transplanted
               ]);

          return json_encode("success");


   }


   public function planting_form($id){
     $farmer = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
          ->select('*')
          ->leftjoin($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production', 'farmer_info.fid', '=', 'crop_production.farmer_id_fk')
          ->where("fid", $id)
          ->first();
          
     
     $tenure_status = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where([
               "form_group" => "tenure_status",
               "input_code" => $farmer->tenure_status
          ])
          ->first();

     $crop_establishment = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where([
               "form_group" => "crop_establishment",
               "input_code" => $farmer->crop_establishment
          ])
          ->first();
     
     $seed_class = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where([
               "form_group" => "seed_class",
               "input_code" => $farmer->seed_class
          ])
          ->first();
     
     $irrigation_source = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where([
               "form_group" => "irrigation_source",
               "input_code" => $farmer->irrigation_source
          ])
          ->first();

     $seed_source = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_dictionary')
          ->select('*')
          ->where([
               "form_group" => "seed_source",
               "input_code" => $farmer->seed_source
          ])
          ->first();

     
     
     $address = isset($farmer->barangay) ? $farmer->barangay.", " : "";
     $address .= isset($farmer->add_municipality) ? $farmer->add_municipality.", " : "";
     $address .= isset($farmer->add_province) ? $farmer->add_province." " : "";

     /*
     $activity_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.lib_establishment")
          ->select("lib_planting.activities as activity")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.lib_planting", "lib_planting.activity_code", "=", "lib_establishment.activity_head")
          ->where("lib_establishment.crop_establishment", $farmer->crop_establishment)
          ->groupBy("lib_establishment.activity_head")
          ->orderBy("order_by", "ASC")
          ->get();

     $activity_list_detailed = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.lib_establishment")
          ->select("lib_establishment.activities as activity")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.lib_planting", "lib_planting.activity_code", "=", "lib_establishment.activity_head")
          ->where("lib_establishment.crop_establishment", $farmer->crop_establishment)
          ->groupBy("lib_establishment.activities")
          ->orderBy("order_by", "group_order")
         ->get(); 

      $activity_item = array();
     foreach ($activity_list_detailed as $act) {
          $activity_item[$act->activity] = $act->activity;
     }


         */

      $activity_list =  DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_planting as t1')
          ->select("t2.activities as activity", "t2.activity_head as activity_head_code")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.lib_planting as t2", "t2.activity_code", "=", "t1.activity_head")
          ->where([
               "t1.seeding_for" => $farmer->crop_establishment,
               "t1.is_active" => '1'
          ])
          ->groupBy('t1.activity_head')
          ->orderBy('t1.order_by')
          ->get();
        //  dd($activity_list);

    

     $data_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
          ->where("farmer_id", $id)
          ->get();

     $data_entry = array();
     foreach ($data_entries as $entry) {
          $data_entry[$entry->planting_id]['qty'] = $entry->qty;
          $data_entry[$entry->planting_id]['unit'] = $entry->unit;
          $data_entry[$entry->planting_id]['cost'] = $entry->unit_cost;
          $data_entry[$entry->planting_id]['date'] = $entry->date;
          $data_entry[$entry->planting_id]['fertilizer_category'] = $entry->fertilizer_category;
          $data_entry[$entry->planting_id]['remarks'] = $entry->remarks;        
          $data_entry[$entry->planting_id]['total_cost'] = number_format(floatval($entry->qty) * floatval($entry->unit_cost),2);
     }



          $form_data =  DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_planting')
          ->select('*')
          ->where([
               "seeding_for" => $farmer->crop_establishment,
               "is_active" => '1'
          ])
          ->orderBy('order_by')
          ->orderBy('group_order')
          ->get();

  
     return view('palaysikatan.forms.planting_info')
          ->with('farmer', $farmer)
          ->with('tenure_status', $tenure_status)
          ->with('crop_establishment', $crop_establishment)
          ->with('seed_class', $seed_class)
          ->with('irrigation_source', $irrigation_source)
          ->with('seed_source', $seed_source)
          ->with('address', $address)
          ->with('form_data', $form_data)
          ->with('id', $id)
          ->with('activity_list', $activity_list)
          //->with('activity_item', $activity_item)
          ->with('data_entry', $data_entry);
   }

   public function province(Request $request)
   {
       $region = $request->regCode;
       $province = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
       ->where('regionName', $region)
       ->where('prv', 'like',Auth::user()->province."%")
       ->groupBy("province")
       ->get();

       echo json_encode($province);
   }

   public function municipality(Request $request)
   {
       $province = $request->provCode;
       $region = $request->regCode;
       $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
       ->where('regionName', $region)
       ->where('province', $province)
       ->groupBy("municipality")
       ->get();

       echo json_encode($municipalities);
   }

    public function update_farmer(Request $request){
          $validator = Validator::make($request->all(), [
               'rsbsa_control_no' => 'required|max:100',
               'techno_area' => 'required',   
               'seeding_no_bags' => 'required|max:100',   
               'seeding_weight_bags' => 'required|max:100',   
               'variety_planted_1' => 'required',   

               'f_firstName' => 'required|max:100',
               'f_middleName' => 'max:50',
               'f_lastName' => 'required|max:100',
               'f_extName' => 'max:20',
               'r_firstName' => 'required|max:100',
               'r_middleName' => 'max:50',
               'r_lastName' => 'required|max:100',
               'r_extName' => 'max:20',
               'add_region' => 'required|max:100',
               'add_province' => 'required|max:100',
               'add_municipality' => 'required|max:100',
               'barangay' => 'required|max:100',
               'contact_no' => 'required',
               'age' => 'required|integer',
               'sex' => 'required',
               'farming_years' => 'required|integer',
               'highest_education' => 'max:100',
               'org_type' => 'required',
               //'org_membership' => 'required|max:100',               
           ]);
           
           if ($validator->fails()){
               
               return response()->json(['status' => "error", 'error'=> $validator->errors()]);
          }
           
          $input = $request->all();
           DB::beginTransaction();
           try {

               // insert user
               $farmerId = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
                    ->where('fid', $input['farmer_id'])
                    ->update([
                         'f_firstName' => $input['f_firstName'],
                         'f_middleName' => $input['f_middleName'],
                         'f_lastName' => $input['f_lastName'],
                         'f_extName' => $input['f_extName'],
                         'r_firstName' => $input['r_firstName'],
                         'r_middleName' => $input['r_middleName'],
                         'r_lastName' => $input['r_lastName'],
                         'r_extName' => $input['r_extName'],
                         'f_full_name' => $input['f_lastName'].', '.$input['f_firstName'].' '.$input['f_middleName'].' '.$input['f_extName'],
                         'r_full_name' => $input['r_lastName'].', '.$input['r_firstName'].' '.$input['r_middleName'].' '.$input['r_extName'],
                         'is_home' => $input['add_type'],
                         'add_region' => $input['add_region'],
                         'add_province' => $input['add_province'],
                         'add_municipality' => $input['add_municipality'],
                         'barangay' => $input['barangay'],
                         'contact_no' => $input['contact_no'],
                         'age' => $input['age'],
                         'sex' => $input['sex'],
                         'farming_years' => $input['farming_years'],
                         'highest_education' => $input['highest_education'],
                         'org_type' => $input['org_type'],
                         'org_membership' => $input['org_membership'],
                         'seminar_history_palaycheck' => isset($input['seminar_history_palaycheck']) ? $input['seminar_history_palaycheck'] : null ,
                         'seminar_history_ffs' => isset($input['seminar_history_ffs']) ? $input['seminar_history_ffs'] : null,
                         'seminar_history_others' => isset($input['seminar_history_others']) ? $input['seminar_history_others'] : null,
                         'rsbsa_control_no' => $input['rsbsa_control_no']
                    ]);
   

                     $variety =  $input['variety_planted_1'];
                   




               $crop_info = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production')
                    ->where('farmer_id_fk', $input['farmer_id'])
                    ->update([
                         'techno_area' => $input['techno_area'],
                         'cropping_season' => $input['season'],
                         'tenure_status' => $input['tenure_status'],
                         'area_harvested' => $input['area_harvested'],
                         'crop_establishment' => $input['crop_establishment'],
                         'crop_establishment_sub' =>$input['crop_establishment_sub'],
                         'seeding_no_bags' => $input['seeding_no_bags'],
                         'seeding_weight_bags' => $input['seeding_weight_bags'],
                         'seeds_price' => $input['seeds_price'],
                         'variety_planted' => $variety,
                         'seed_class' => $input['seed_class'],
                         'irrigation_source' => ($input['irrigation_source'] == "irr_others") ? $input['irrigation_source_input'] : $input['irrigation_source'],
                         'seed_source' => ($input['seed_source'] == "source_others") ? $input['seed_source_input'] : $input['seed_source'],
                         'harvest_no_bags' => $input['harvest_no_bags'],
                         'harvest_weight_bags' => $input['harvest_weight_bags'],
                         'crop_loss' => ($input['crop_loss'] == "No") ? $input['crop_loss'] : $input['crop_loss_input'],
                         // 'crop_loss_input' => $input['crop_loss_input'],
                         'phl_share' => $input['phl_share'],
                         'prevailing_land_rent' => $input['prevailing_land_rent'],
                         'sold_as' => $input['sold_as'],
                         'fresh_palay_price' => $input['fresh_palay_price'],
                         'dry_palay_price' => $input['dry_palay_price'],
                         'sample_1' => $input['cropt_cut_sample1'],
                         'sample_2' => $input['cropt_cut_sample2'],
                         'sample_3' => $input['cropt_cut_sample3'],
                    ]);

   

                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
                         ->insert([
                              "farmer_id" => $input['farmer_id'],
                              "data_ins" => "",
                              "module" => "farmer_list",
                              "action" => "update",
                              "user" => Auth::user()->username
                         ]);
                    


               DB::commit();
               \Session::flash('success_farmer', 'Updated Farmer successfully.');
           } catch (\Exception $e) {
               DB::rollback();
               Session::flash('error', 'Error adding Farmer.');
               return response()->json(['status' => "error_store", 'errors'=> $e->getMessage()]);
           }
   
           return redirect()->route('palaysikatan.farmers');
   }



   public function new_farmer(Request $request){
         
          $validator = Validator::make($request->all(), [
               'rsbsa_control_no' => 'required|max:100',
               'techno_area' => 'required',   
               'seeding_no_bags' => 'required|max:100',   
               'seeding_weight_bags' => 'required|max:100',   
               'variety_planted_1' => 'required',   
               
                           
               'f_firstName' => 'required|max:100',
               'f_middleName' => 'max:50',
               'f_lastName' => 'required|max:100',
               'f_extName' => 'max:20',
               'r_firstName' => 'required|max:100',
               'r_middleName' => 'max:50',
               'r_lastName' => 'required|max:100',
               'r_extName' => 'max:20',
               'add_region' => 'required|max:100',
               'add_province' => 'required|max:100',
               'add_municipality' => 'required|max:100',
               'barangay' => 'required|max:100',
               'contact_no' => 'required',
               'age' => 'required|integer',
               'sex' => 'required',
               'farming_years' => 'required|integer',
               'highest_education' => 'max:100',
               'org_type' => 'required',
              // 'org_membership' => 'required|max:100',               
           ]);
           
           if ($validator->fails()){
               
               return response()->json(['status' => "error", 'error'=> $validator->errors()]);
          }
           
          $input = $request->all();
           DB::beginTransaction();
           try {
               // insert user
               $farmerId = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
                    ->insertGetId([
                         'f_firstName' => $input['f_firstName'],
                         'f_middleName' => $input['f_middleName'],
                         'f_lastName' => $input['f_lastName'],
                         'f_extName' => $input['f_extName'],
                         'r_firstName' => $input['r_firstName'],
                         'r_middleName' => $input['r_middleName'],
                         'r_lastName' => $input['r_lastName'],
                         'r_extName' => $input['r_extName'],
                         'f_full_name' => $input['f_lastName'].', '.$input['f_firstName'].' '.$input['f_middleName'].' '.$input['f_extName'],
                         'r_full_name' => $input['r_lastName'].', '.$input['r_firstName'].' '.$input['r_middleName'].' '.$input['r_extName'],
                         'is_home' => $input['add_type'],
                         'add_region' => $input['add_region'],
                         'add_province' => $input['add_province'],
                         'add_municipality' => $input['add_municipality'],
                         'barangay' => $input['barangay'],
                         'contact_no' => $input['contact_no'],
                         'age' => $input['age'],
                         'sex' => $input['sex'],
                         'farming_years' => $input['farming_years'],
                         'highest_education' => $input['highest_education'],
                         'org_type' => $input['org_type'],
                         'org_membership' => $input['org_membership'],
                         'seminar_history_palaycheck' => isset($input['seminar_history_palaycheck']) ? $input['seminar_history_palaycheck'] : null ,
                         'seminar_history_ffs' => isset($input['seminar_history_ffs']) ? $input['seminar_history_ffs'] : null,
                         'seminar_history_others' => isset($input['seminar_history_others']) ? $input['seminar_history_others'] : null,
                         'rsbsa_control_no' => $input['rsbsa_control_no']
                    ]);
   
              $id = "FCA-". str_pad($farmerId, 4, '0', STR_PAD_LEFT);

              $farmer = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info')
                    ->where('fid', $farmerId)
                    ->update([
                         'farmer_id' => $id
                    ]);


                    $variety =  $input['variety_planted_1'];
                   



               $crop_info = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.crop_production')
                    ->insert([
                         'farmer_id_fk' => $farmerId,
                         'techno_area' => $input['techno_area'],
                         'tenure_status' => $input['tenure_status'],
                         'cropping_season' => $input['season'],
                         'area_harvested' => $input['area_harvested'],
                         'crop_establishment' => $input['crop_establishment'],
                         'crop_establishment_sub' =>$input['crop_establishment_sub'],
                         'seeding_no_bags' => $input['seeding_no_bags'],
                         'seeding_weight_bags' => $input['seeding_weight_bags'],
                         'seeds_price' => $input['seeds_price'],
                         'variety_planted' => $variety,
                         'seed_class' => $input['seed_class'],
                         'irrigation_source' => ($input['irrigation_source'] == "irr_others") ? $input['irrigation_source_input'] : $input['irrigation_source'],
                         'seed_source' => ($input['seed_source'] == "source_others") ? $input['seed_source_input'] : $input['seed_source'],
                         'harvest_no_bags' => $input['harvest_no_bags'],
                         'harvest_weight_bags' => $input['harvest_weight_bags'],
                         'crop_loss' => ($input['crop_loss'] == "No") ? $input['crop_loss'] : $input['crop_loss_input'],
                         // 'crop_loss_input' => $input['crop_loss_input'],
                         'phl_share' => $input['phl_share'],
                         'prevailing_land_rent' => $input['prevailing_land_rent'],
                         'sold_as' => $input['sold_as'],
                         'fresh_palay_price' => $input['fresh_palay_price'],
                         'dry_palay_price' => $input['dry_palay_price'],
                         'sample_1' => $input['cropt_cut_sample1'],
                         'sample_2' => $input['cropt_cut_sample2'],
                         'sample_3' => $input['cropt_cut_sample3'],
                    ]);


                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.logs")
                         ->insert([
                              "farmer_id" => $farmerId,
                              "data_ins" => "",
                              "module" => "farmer_list",
                              "action" => "insert",
                              "user" => Auth::user()->username
                         ]);


                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.station_entries")
                              ->insert([
                                   "ref_id" => $farmerId,
                                   "farmer_id" =>  $id,
                                   "user_encoded" => Auth::user()->username,
                                   "province_tag" => Auth::user()->province,
                                   "station" => Auth::user()->stationId
                              ]);



   
               DB::commit();
               \Session::flash('success_farmer', 'Added Farmer successfully.');
           } catch (\Exception $e) {
               DB::rollback();
               Session::flash('error', 'Error adding Farmer.');
               return response()->json(['status' => "error_store", 'errors'=> $e->getMessage()]);
           }
   
           return redirect()->route('palaysikatan.farmers');
   }

   public function new_planting(Request $request){
         
          
          $input = $request->all();
          $input = $input['form_data'];

          $plantingData = [];
          $data = [];
          $j = 0;
          for ($i = 0; $i < count($input); $i++){
              
              $data[$input[$i]['name']] = $input[$i]['value'];
             
              if($input[$i]['name'] == 'remarks'){
                    $plantingData[$j] = $data;
                    $data = [];
                    $j++;
              }
          }
         
          DB::beginTransaction();
          try {
               
               $planting = DB::table($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_planting')
                    ->insert($plantingData);


               DB::commit();
               \Session::flash('success_farmer', 'Added Farmer successfully.');
          } catch (\Exception $e) {
               DB::rollback();
               Session::flash('error', 'Error adding Farmer.');
               return response()->json(['status' => "error_store", 'errors'=> $e->getMessage()]);
          }

          return redirect()->route('palaysikatan.farmers');
     }

   private function join_name($fname, $lname, $mname, $extname){
          $fullname = "";

          if($fname != null && $fname != ""){
               $fullname = $fname;
          }

          if($mname != null && $mname != ""){
               $fullname .= " ".$mname;
          }

          if($lname != null && $lname != ""){
               $lname .= " ".$lname;
          }

          if($extname != null && $extname != ""){
               $extname .= " ".$extname;
          }

          return $fullname;
   }
    

   public function reconnect_cip(){
     
     $data_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
          ->where("ref_cp_id", 0)
          ->groupBy("farmer_id")
          ->get();

          foreach ($data_entries as $data) {
               $getcip = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
                    ->where("farmer_id_fk", $data->farmer_id)
                    ->first();
                    if(count($getcip)>0){
                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                         ->where("farmer_id", $data->farmer_id)
                         ->update(['ref_cp_id' => $getcip->cpid]);
                    }
                    


          }
    

           $data_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
          ->where("ref_cp_id", 0)
          ->groupBy("farmer_id")
          ->get();

          foreach ($data_entries as $data) {
               $getcip = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
                    ->where("farmer_id_fk", $data->farmer_id)
                    ->first();
                    if(count($getcip)>0){
                         DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
                         ->where("farmer_id", $data->farmer_id)
                         ->update(['ref_cp_id' => $getcip->cpid]);
                    }
                  


          }






   }


   public function matrix($station){
      
     $this->reconnect_cip();          
     if($station == "0")$station = "%";
   
     $crop_establishment_arr = array(
          "manual_transplanting" => "Manual Transplanting",
          "mechanized_transplanting" => "Mechanical Transplanting",
          "drum_seeding" => "Manual Direct-Seeding",
          "drum_seeding_sp" => "Mechanical Direct-Seeding"
     );

     $farmer_info = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info as f")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production as c", "c.farmer_id_fk", "=", "f.fid")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.station_entries as s", "s.ref_id", "=", "f.fid")
          ->where("s.station", "like", $station
          )
          ->orderBy("c.farmer_id_fk")
          ->groupBy("c.cpid")
          ->groupBy("c.variety_planted")
          ->get();
     $excel_data = array();
   
          foreach ($farmer_info as $farmer_data) {
               $data = array();
               $land_prep = array();
               $station = DB::table("geotag_db2.tbl_station")->where("stationId", $farmer_data->station)->value("stationName");
               $region = $farmer_data->add_region;
               $region_no = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("regionName", $farmer_data->add_region)->value("regCode");
               $province = $farmer_data->add_province;
               $municipality = $farmer_data->add_municipality;
               $brgy = $farmer_data->barangay;
               $farmer_name = trim($farmer_data->f_full_name);
               $lastname = $farmer_data->f_lastName;
               $firstname = $farmer_data->f_firstName;
               $middlename = $farmer_data->f_middleName;                             
               $extname = $farmer_data->f_extName;
               $contact_no = $farmer_data->contact_no;
               $area_harvested = $farmer_data->area_harvested;
               $crop_establishment = $crop_establishment_arr[$farmer_data->crop_establishment];
               $techno_area = $farmer_data->techno_area;
               $TechnoDemoStart= $farmer_data->date_sown;
               $area_harvested= $farmer_data->area_harvested;
               $cpid = $farmer_data->cpid;
               $prodn_kg_ws = $farmer_data->harvest_no_bags * $farmer_data->harvest_no_bags;
               $SEEDRATEHA_WS = $farmer_data->seeding_no_bags * $farmer_data->seeding_weight_bags;
               $seeding_no_bags=$farmer_data->seeding_no_bags;
               $seeding_weight_bags=$farmer_data->harvest_weight_bags;
               $SEEDCOHA_WS = $farmer_data->seeds_price*  $farmer_data->seeding_no_bags;
               if($prodn_kg_ws>0 && $farmer_data->area_harvested>0){
                    $YIELD_kg__ha_ws = $prodn_kg_ws / $farmer_data->area_harvested;
               }else{
                    $YIELD_kg__ha_ws=0;
               }
               
               $TotalNoSack_ws = $farmer_data->harvest_no_bags;

               $herbAI_ws = "N/A";
               $insAI_ws	 = "N/A";
               $fuAI_ws = "N/A";
               $rodAI_ws = "N/A";
               $molAI_ws = "N/A";
               $hercoha_ws = "N/A";
               $insecoha_ws = "N/A";
               $fungcoha_ws = "N/A";
               $rodentcoha_ws = "N/A";
               $molluskcoha_ws = "N/A";
               
               $priceper_kg_dr = $farmer_data->dry_palay_price;
               $priceper_kg_fr = $farmer_data->fresh_palay_price;
               $cropCutTotal= ($farmer_data->sample_1 +$farmer_data->sample_2+$farmer_data->sample_3);
               $cropCutTotalYield =(($cropCutTotal/15)*10000)/1000;
               if($farmer_data->sold_as == "Fresh"){
                    $costper_kg = $farmer_data->fresh_palay_price;
               }else{
                     $costper_kg = $farmer_data->dry_palay_price;
               }
               //CEHECK THIS
             /*   if($farmer_data->area_harvested == 0){
                    $yield =0;
               }else{
                    $yield = number_format((($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags)/$farmer_data->area_harvested)/1000,2);
               } */
               if($farmer_data->harvest_no_bags>0 && $farmer_data->harvest_weight_bags){
                     $yield = number_format(($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags),2);
               }else{
                    $yield =0;
               }
                

               if($yield>0 && $techno_area>0){
                    
                      $yield_ha =number_format(($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags)/$techno_area,2); 
               }else{
                    $yield_ha =0;
               }
               
               
                    //activities
              
               $activity_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                    ->where("ref_cp_id", $cpid)
                    ->where("farmer_id", $farmer_data->fid)
                    ->get();
                  //  dd($activity_entries);
                    foreach ($activity_entries as $entry){


                         if($entry->planting_id == 29 || $entry->planting_id == 156 || $entry->planting_id == 278 || $entry->planting_id == 400 || $entry->planting_id == 30 || $entry->planting_id == 157 || $entry->planting_id == 279 || $entry->planting_id == 401 || $entry->planting_id == 31 || $entry->planting_id == 158 || $entry->planting_id == 280 || $entry->planting_id == 402 || $entry->planting_id == 32 || $entry->planting_id == 159 || $entry->planting_id == 281 || $entry->planting_id == 403 || $entry->planting_id == 36 || $entry->planting_id == 41 || $entry->planting_id == 163 || $entry->planting_id == 285 || $entry->planting_id == 290 || $entry->planting_id == 407 || $entry->planting_id == 412 || $entry->planting_id == 40 || $entry->planting_id == 167 || $entry->planting_id == 289 || $entry->planting_id == 411 || $entry->planting_id == 43 || $entry->planting_id == 170 || $entry->planting_id == 292 || $entry->planting_id == 414){
                              if(isset($data[$cpid]["Hired_Crop_Establishment_ws"])){
                                   $data[$cpid]["Hired_Crop_Establishment_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Hired_Crop_Establishment_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }

                         
                         if($entry->planting_id == 53  || $entry->planting_id == 61  ||$entry->planting_id == 69  ||$entry->planting_id == 74  ||$entry->planting_id == 78  ||$entry->planting_id == 82  ||$entry->planting_id == 89  ||$entry->planting_id == 95  ||$entry->planting_id == 101  ||$entry->planting_id == 180  ||$entry->planting_id == 188  ||$entry->planting_id == 195  ||$entry->planting_id == 200  ||$entry->planting_id == 204  ||$entry->planting_id == 208  ||$entry->planting_id == 215  ||$entry->planting_id == 221  ||$entry->planting_id == 227  ||$entry->planting_id == 302  ||$entry->planting_id == 310  ||$entry->planting_id == 317  ||$entry->planting_id == 322  ||$entry->planting_id == 326  ||$entry->planting_id == 330  ||$entry->planting_id == 337  ||$entry->planting_id == 343  ||$entry->planting_id == 349  ||$entry->planting_id == 424  ||$entry->planting_id == 432  ||$entry->planting_id == 440  ||$entry->planting_id == 445  ||$entry->planting_id == 449  ||$entry->planting_id == 453  ||$entry->planting_id == 460  ||$entry->planting_id == 466  ||$entry->planting_id == 472 ||$entry->planting_id == 104  ||$entry->planting_id == 108  ||$entry->planting_id == 112  ||$entry->planting_id == 230  ||$entry->planting_id == 234  ||$entry->planting_id == 238 ||$entry->planting_id == 352  ||$entry->planting_id == 356  ||$entry->planting_id == 360  ||$entry->planting_id == 475  ||$entry->planting_id == 479  ||$entry->planting_id == 483  ){
                              if(isset($data[$cpid]["Hired_Crop_care_maintenance_ws"])){
                                   $data[$cpid]["Hired_Crop_care_maintenance_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Hired_Crop_care_maintenance_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }

                         if($entry->planting_id == 116  || $entry->planting_id == 242  || $entry->planting_id == 364  || $entry->planting_id == 487 || $entry->planting_id == 117  || $entry->planting_id == 243  || $entry->planting_id == 365  || $entry->planting_id == 488 || $entry->planting_id == 116|| $entry->planting_id == 242|| $entry->planting_id == 364|| $entry->planting_id == 487){
                              if(isset($data[$cpid]["Hired_Harvesting_and_Threshing_ws"])){
                                   $data[$cpid]["Hired_Harvesting_and_Threshing_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Hired_Harvesting_and_Threshing_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }
                     
                         
                         if($entry->planting_id == 118  || $entry->planting_id == 244  || $entry->planting_id == 366  || $entry->planting_id == 489){
                              if(isset($data[$cpid]["Hired_CombineHarvester_ws"])){
                                   $data[$cpid]["Hired_CombineHarvester_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Hired_CombineHarvester_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }


                         if($entry->planting_id == 124  || $entry->planting_id == 498  || $entry->planting_id == 499  || $entry->planting_id == 500){
                              if(isset($data[$cpid]["Phlcost_ws"])){
                                   $data[$cpid]["Phlcost_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Phlcost_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }

                         if($entry->planting_id == 8  || $entry->planting_id == 26  || $entry->planting_id == 37  || $entry->planting_id == 42 || $entry->planting_id == 46  ||$entry->planting_id == 54  ||$entry->planting_id == 62  ||$entry->planting_id == 70  ||$entry->planting_id == 75  ||$entry->planting_id == 79  ||$entry->planting_id == 83  ||$entry->planting_id == 90  ||$entry->planting_id == 96  ||$entry->planting_id == 102  ||$entry->planting_id == 107  ||$entry->planting_id == 111  ||$entry->planting_id == 115  ||$entry->planting_id == 123  ||$entry->planting_id == 135  ||$entry->planting_id == 153  ||$entry->planting_id == 164  ||$entry->planting_id == 169  ||$entry->planting_id == 173  ||$entry->planting_id == 181  ||$entry->planting_id == 189  ||$entry->planting_id == 196  ||$entry->planting_id == 201  ||$entry->planting_id == 205  ||$entry->planting_id == 209  ||$entry->planting_id == 216  ||$entry->planting_id == 222  ||$entry->planting_id == 228  ||$entry->planting_id == 233  ||$entry->planting_id == 237  ||$entry->planting_id == 241  ||$entry->planting_id == 249  ||$entry->planting_id == 257  ||$entry->planting_id == 275  ||$entry->planting_id == 286  ||$entry->planting_id == 291  ||$entry->planting_id == 295  ||$entry->planting_id == 303  ||$entry->planting_id == 311  ||$entry->planting_id == 318  ||$entry->planting_id == 323  ||$entry->planting_id == 327  ||$entry->planting_id == 331  ||$entry->planting_id == 338  ||$entry->planting_id == 344  ||$entry->planting_id == 350  ||$entry->planting_id == 355  ||$entry->planting_id == 359  ||$entry->planting_id == 363  ||$entry->planting_id == 371  ||$entry->planting_id == 379  ||$entry->planting_id == 397  ||$entry->planting_id == 408  ||$entry->planting_id == 413  ||$entry->planting_id == 417  ||$entry->planting_id == 425  ||$entry->planting_id == 433  ||$entry->planting_id == 441  ||$entry->planting_id == 446  ||$entry->planting_id == 450  ||$entry->planting_id == 454  ||$entry->planting_id == 461  ||$entry->planting_id == 467  ||$entry->planting_id == 473  ||$entry->planting_id == 478  ||$entry->planting_id == 482  ||$entry->planting_id == 486  ||$entry->planting_id == 494 ){
                              if(isset($data[$cpid]["foodcostha_ws"])){
                                   $data[$cpid]["foodcostha_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["foodcostha_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }
                         /* manuel */
                         if($entry->planting_id == 72  || $entry->planting_id == 76  || $entry->planting_id == 80  || $entry->planting_id == 198  ||$entry->planting_id == 202  ||$entry->planting_id == 206  ||$entry->planting_id == 320  ||$entry->planting_id == 324  ||$entry->planting_id == 328  ||$entry->planting_id == 443  ||$entry->planting_id == 447  ||$entry->planting_id == 451  ||$entry->planting_id == 73  ||$entry->planting_id == 77  ||$entry->planting_id == 81  ||$entry->planting_id == 199  ||$entry->planting_id == 203  ||$entry->planting_id == 207  ||$entry->planting_id == 321  ||$entry->planting_id == 325  ||$entry->planting_id == 329  ||$entry->planting_id == 444  ||$entry->planting_id == 448  ||$entry->planting_id == 452 ) {
                              if(isset($data[$cpid]["Irrigationcostha_ws"])){
                                   $data[$cpid]["Irrigationcostha_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Irrigationcostha_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }

                         
                         if($entry->planting_id == 126  || $entry->planting_id == 504 || $entry->planting_id == 505 || $entry->planting_id == 506) {
                              if(isset($data[$cpid]["Landrent_ha_ws"])){
                                   $data[$cpid]["Landrent_ha_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Landrent_ha_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }

                         if($entry->planting_id == 14  || $entry->planting_id == 19 || $entry->planting_id == 141 || $entry->planting_id == 146  || $entry->planting_id == 263 || $entry->planting_id == 268 || $entry->planting_id == 385 || $entry->planting_id == 390 || $entry->planting_id == 15 || $entry->planting_id == 142 || $entry->planting_id == 264 || $entry->planting_id == 386) {
                              if(isset($data[$cpid]["Hired_Seedling_prep_ws"])){
                                   $data[$cpid]["Hired_Seedling_prep_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["Hired_Seedling_prep_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }

                         
                         if($entry->planting_id == 125  || $entry->planting_id == 501 || $entry->planting_id == 502 || $entry->planting_id == 503) {
                              if(isset($data[$cpid]["otherinputcostha_ws"])){
                                   $data[$cpid]["otherinputcostha_ws"] += $entry->qty*$entry->unit_cost;                                  
                              }else{
                                   $data[$cpid]["otherinputcostha_ws"] = $entry->qty*$entry->unit_cost;
                              }  
                         }
                         
                         if($farmer_data->crop_establishment=="manual_transplanting"){
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

                              if($entry->planting_id == 289 ){
                                   if(isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"])){
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] += $entry->qty*$entry->unit_cost;

                                   }else{
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] = $entry->qty*$entry->unit_cost;
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
                              
                              // Number of Bags (14-14-14-)
                              if($entry->planting_id == 297 || $entry->planting_id == 304 || $entry->planting_id == 312){
                                   if(isset($data[$cpid]["bagsnot14"])){
                                        $data[$cpid]["bagsnot14"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnot14"] = $entry->qty;
                                   }                                  
                              }

                              // Number of Bags (16-20-0)
                              if($entry->planting_id == 298 || $entry->planting_id == 305 || $entry->planting_id == 313){
                                   if(isset($data[$cpid]["bagsnoammophos"])){
                                        $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                   }                                  
                              }
                              
                               // Number of Bags (21-0-0)
                               if($entry->planting_id == 299 || $entry->planting_id == 306 || $entry->planting_id == 314){
                                   if(isset($data[$cpid]["bagsnosul"])){
                                        $data[$cpid]["bagsnosul"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnosul"] = $entry->qty;
                                   }                                  
                              }

                               // Number of Bags (0-0-60)
                               if($entry->planting_id == 300 || $entry->planting_id == 307 || $entry->planting_id == 315){
                                   if(isset($data[$cpid]["bagsnomop"])){
                                        $data[$cpid]["bagsnomop"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnomop"] = $entry->qty;
                                   }                                  
                              }

                                // Number of Bags (46-0-0)
                                if($entry->planting_id == 301 || $entry->planting_id == 308 || $entry->planting_id == 316){
                                   if(isset($data[$cpid]["bagsnourea"])){
                                        $data[$cpid]["bagsnourea"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnourea"] = $entry->qty;
                                   }                                  
                              }

                              // Number of bags (17-0-17)
                              if($entry->planting_id == 438){
                                   if(isset($data[$cpid]["bagsno"])){
                                        $data[$cpid]["bagsno"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsno"] = $entry->qty;
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

                         } //end for manual transplanting /* joe */
                         elseif($farmer_data->crop_establishment=="mechanized_transplanting"){
                           
                              if($entry->planting_id == 372){
                                   if(isset($data[$cpid]["land_prep_labor"])){
                                        $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;

                                         $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                   }else{
                                        $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                         $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                   }                                  
                              }

                             // dd($land_prep);
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
                                   if(isset($data[$cpid]["land_prep_rental"])){
                                        $data[$cpid]["land_prep_rental"] += $entry->qty*$entry->unit_cost;
                                         $land_prep[$cpid]["land_prep_rental_ha"] += $entry->unit_cost;
                                   }else{
                                        $data[$cpid]["land_prep_rental"] = $entry->qty*$entry->unit_cost;
                                         $land_prep[$cpid]["land_prep_rental_ha"] = $entry->unit_cost;
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

                              if($entry->planting_id == 411 ){
                                   if(isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"])){
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] += $entry->qty*$entry->unit_cost;

                                   }else{
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] = $entry->qty*$entry->unit_cost;
                                   }                                  
                              }

                              if($entry->planting_id == 411 ){
                                   if(isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"])){
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] += $entry->qty*$entry->unit_cost;

                                   }else{
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] = $entry->qty*$entry->unit_cost;
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

                              // Number of Bags (14-14-14-)
                              if($entry->planting_id == 419 || $entry->planting_id == 426 || $entry->planting_id == 434){
                                   if(isset($data[$cpid]["bagsnot14"])){
                                        $data[$cpid]["bagsnot14"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnot14"] = $entry->qty;
                                   }                                  
                              }

                              // Number of Bags (16-20-0)
                              if($entry->planting_id == 420 || $entry->planting_id == 427 || $entry->planting_id == 435){
                                   if(isset($data[$cpid]["bagsnoammophos"])){
                                        $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                   }                                  
                              }

                               // Number of Bags (21-0-0)
                               if($entry->planting_id == 421 || $entry->planting_id == 428 || $entry->planting_id == 436){
                                   if(isset($data[$cpid]["bagsnosul"])){
                                        $data[$cpid]["bagsnosul"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnosul"] = $entry->qty;
                                   }                                  
                              }

                               // Number of Bags (0-0-60)
                               if($entry->planting_id == 422 || $entry->planting_id == 429 || $entry->planting_id == 437){
                                   if(isset($data[$cpid]["bagsnomop"])){
                                        $data[$cpid]["bagsnomop"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnomop"] = $entry->qty;
                                   }                                  
                              }

                                // Number of Bags (46-0-0)
                                if($entry->planting_id == 423 || $entry->planting_id == 430){
                                   if(isset($data[$cpid]["bagsnourea"])){
                                        $data[$cpid]["bagsnourea"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnourea"] = $entry->qty;
                                   }                                  
                              }

                              // Number of bags (17-0-17)
                              if($entry->planting_id == 438){
                                   if(isset($data[$cpid]["bagsno"])){
                                        $data[$cpid]["bagsno"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsno"] = $entry->qty;
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
                         else if ($farmer_data->crop_establishment=="drum_seeding"){


                              if($entry->planting_id == 128){
                                   if(isset($data[$cpid]["land_prep_labor"])){
                                        $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;
                                        if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
                                         $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                   }else{
                                        $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                         $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                          if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
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
                              if($entry->planting_id == 167 ){
                                   if(isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"])){
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] += $entry->qty*$entry->unit_cost;

                                   }else{
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] = $entry->qty*$entry->unit_cost;
                                   }                                  
                              }

                              if($entry->planting_id == 167 ){
                                   if(isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"])){
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] += $entry->qty*$entry->unit_cost;

                                   }else{
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] = $entry->qty*$entry->unit_cost;
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



                              // Number of Bags (14-14-14-)
                              if($entry->planting_id == 175 || $entry->planting_id == 182 || $entry->planting_id == 190){
                                   if(isset($data[$cpid]["bagsnot14"])){
                                        $data[$cpid]["bagsnot14"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnot14"] = $entry->qty;
                                   }                                  
                              }

                              // Number of Bags (16-20-0)
                              if($entry->planting_id == 176 || $entry->planting_id == 183 || $entry->planting_id == 191){
                                   if(isset($data[$cpid]["bagsnoammophos"])){
                                        $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                   }                                  
                              }
                              
                               // Number of Bags (21-0-0)
                               if($entry->planting_id == 177 || $entry->planting_id == 184 || $entry->planting_id == 192){
                                   if(isset($data[$cpid]["bagsnosul"])){
                                        $data[$cpid]["bagsnosul"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnosul"] = $entry->qty;
                                   }                                  
                              }

                               // Number of Bags (0-0-60)
                               if($entry->planting_id == 178 || $entry->planting_id == 185 || $entry->planting_id == 193){
                                   if(isset($data[$cpid]["bagsnomop"])){
                                        $data[$cpid]["bagsnomop"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnomop"] = $entry->qty;
                                   }                                  
                              }

                                // Number of Bags (46-0-0)
                                if($entry->planting_id == 179 || $entry->planting_id == 186 || $entry->planting_id == 194){
                                   if(isset($data[$cpid]["bagsnourea"])){
                                        $data[$cpid]["bagsnourea"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnourea"] = $entry->qty;
                                   }                                  
                              }

                              // Number of bags (17-0-17)
                              if($entry->planting_id == 438){
                                   if(isset($data[$cpid]["bagsno"])){
                                        $data[$cpid]["bagsno"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsno"] = $entry->qty;
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
                         else if ($farmer_data->crop_establishment=="drum_seeding_sp"){

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

                              if($entry->planting_id == 40 ){
                                   if(isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"])){
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] += $entry->qty*$entry->unit_cost;

                                   }else{
                                        $data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"] = $entry->qty*$entry->unit_cost;
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


                              // Number of Bags (14-14-14-)
                              if($entry->planting_id == 48 || $entry->planting_id == 55 || $entry->planting_id == 63){
                                   if(isset($data[$cpid]["bagsnot14"])){
                                        $data[$cpid]["bagsnot14"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnot14"] = $entry->qty;
                                   }                                  
                              }

                              // Number of Bags (16-20-0)
                              if($entry->planting_id == 49 || $entry->planting_id == 56 || $entry->planting_id == 64){
                                   if(isset($data[$cpid]["bagsnoammophos"])){
                                        $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                   }                                  
                              }
                              
                               // Number of Bags (21-0-0)
                               if($entry->planting_id == 50 || $entry->planting_id == 57 || $entry->planting_id == 65){
                                   if(isset($data[$cpid]["bagsnosul"])){
                                        $data[$cpid]["bagsnosul"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnosul"] = $entry->qty;
                                   }                                  
                              }

                               // Number of Bags (0-0-60)
                               if($entry->planting_id == 51 || $entry->planting_id == 58 || $entry->planting_id == 66){
                                   if(isset($data[$cpid]["bagsnomop"])){
                                        $data[$cpid]["bagsnomop"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnomop"] = $entry->qty;
                                   }                                  
                              }

                                // Number of Bags (46-0-0)
                                if($entry->planting_id == 52 || $entry->planting_id == 59 || $entry->planting_id == 497){
                                   if(isset($data[$cpid]["bagsnourea"])){
                                        $data[$cpid]["bagsnourea"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsnourea"] = $entry->qty;
                                   }                                  
                              }

                              // Number of bags (17-0-17)
                              if($entry->planting_id == 67){
                                   if(isset($data[$cpid]["bagsno"])){
                                        $data[$cpid]["bagsno"] += $entry->qty;
                                   }else{
                                        $data[$cpid]["bagsno"] = $entry->qty;
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
                    } //foreach data entries


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
                    

               foreach($data as $key_id => $value){

                    foreach ($value as $key => $value_data) {

                          if($key == "land_prep_labor" || $key == "seed_bed_labor" || $key == "seed_tray_labor" || $key =="seed_mgt_labor" || $key == "direct_seed_labor" || $key == "trans_laborpull" || $key == "mech_labor" || $key=="fert_mgt_labor"  || $key =="pest_mgt_labor" || $key=="weed_mgt_labor" || $key =="harvest_labor" || $key == "oth_labor" || $key == "trans_replant" || $key == "mech_replant" || $key=="weed_mgt_appli"){
                              $total_labor_cost += $value_data;
                              $grand += $value_data;
                                  
                         }
                         
                         if($key == "land_prep_labor_ha" || $key == "seed_bed_labor_ha" || $key == "seed_tray_labor_ha" || $key =="seed_mgt_labor_ha" || $key == "direct_seed_labor_ha" || $key == "trans_laborpull_ha" || $key == "mech_labor_ha" || $key=="fert_mgt_labor_ha" || $key =="pest_mgt_labor_ha" || $key=="weed_mgt_labor_ha" || $key =="harvest_labor_ha" || $key == "oth_labor_ha" || $key == "trans_replant_ha" || $key == "mech_replant_ha" || $key =="weed_mgt_appli_ha"){
                              $total_labor_cost_ha += $value_data;
                              $grand_ha += $value_data;
                         }

                         if($key == "seed_tray_mat" || $key == "seed_mgt_mat" || $key == "trans_mat" || $key =="harvest_matt" || $key == "oth_matt" ){
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

                         if($key == "oth_land" || $key == "oth_interest"){
                              $total_cost += $value_data;
                              $grand += $value_data;
                         }
                         if($key == "oth_land_ha" || $key == "oth_interest_ha"){
                              $total_cost_ha += $value_data;
                              $grand_ha += $value_data;
                         }
                    }
                  

                    $data[$key_id]["totlabexp_totcost"] = $total_labor_cost;
                    $data[$key_id]["totlabexp_totcost_perha"] = $total_labor_cost_ha;
                    $data[$key_id]["totmatexp_totcost"] = $total_mat_cost;
                    $data[$key_id]["totmatexp_totcost_perha"] = $total_mat_cost_ha;
                    $data[$key_id]["totmachrent_totcost"] = $total_machine;
                    $data[$key_id]["totalmachrent_totcost_perha"] = $total_machine_ha;
                    $data[$key_id]["total_fert"] = $total_fert;
                    $data[$key_id]["total_fert_ha"] = $total_fert_ha; 
                    $data[$key_id]["total_chemical"] = $total_chemical;
                    $data[$key_id]["total_chemical_ha"] = $total_chemical_ha;  
                    $data[$key_id]["total_meals"] = $total_meals;
                    $data[$key_id]["total_meals_ha"] = $total_meals_ha;
                    $data[$key_id]["totirrigexp_totcost"] = $total_irr;
                    $data[$key_id]["totirrigexp_totcost_perha"] = $total_irr_ha;
                    $data[$key_id]["tototherexp_totcost"] = $total_cost;
                    $data[$key_id]["tototherexp_totcost_totcost"] = $total_cost_ha;
                    $data[$key_id]["totprod_totcost"] = $grand;
                    $data[$key_id]["totprod_totcost_perha"] = $grand_ha;

               } 
               //dd($data[$cpid]["totlabexp_totcost"]);
               
                //dd($data[$cpid]["totlabexp_totcost"]);
                if($techno_area>0 && isset($data[$cpid]["land_prep_labor"])){
                    $data[$cpid]["land_prep_labor_ha"]=round($data[$cpid]["land_prep_labor"]/$techno_area,2);
               }
               if($techno_area>0 && isset($data[$cpid]["land_prep_rental"])){
                    $data[$cpid]["land_prep_rental_ha"]=round($data[$cpid]["land_prep_rental"]/$techno_area,2);
               }
               if($techno_area>0 && isset($data[$cpid]["land_prep_meals"])){
                    $data[$cpid]["land_prep_meals_ha"]=round($data[$cpid]["land_prep_meals"]/$techno_area,2);
               }
              /*  if($techno_area>0 && isset($data[$cpid]["seed_bed_rental"])){
                    $data[$cpid]["seed_bed_rental_ha"]=$data[$cpid]["seed_bed_rental"]/$techno_area;
               } */
               if($techno_area>0 && isset($data[$cpid]["seed_bed_labor"])){
                    $data[$cpid]["seed_bed_labor_ha"]=round($data[$cpid]["seed_bed_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_tray_labor"])){
                    $data[$cpid]["seed_tray_labor_ha"]=round($data[$cpid]["seed_tray_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_tray_fert"])){
                    $data[$cpid]["seed_tray_fert_ha"]=round($data[$cpid]["seed_tray_fert"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_tray_mat"])){
                    $data[$cpid]["seed_tray_mat_ha"]=round($data[$cpid]["seed_tray_mat"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_mgt_labor"])){
                    $data[$cpid]["seed_mgt_labor_ha"]=round($data[$cpid]["seed_mgt_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_mgt_mat"])){
                    $data[$cpid]["seed_mgt_mat_ha"]=round($data[$cpid]["seed_mgt_mat"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_mgt_fert"])){
                    $data[$cpid]["seed_mgt_fert_ha"]=round($data[$cpid]["seed_mgt_fert"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["seed_mgt_meals"])){
                    $data[$cpid]["seed_mgt_meals_ha"]=round($data[$cpid]["seed_mgt_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["direct_seed_labor"])){
                    $data[$cpid]["direct_seed_labor_ha"]=round($data[$cpid]["direct_seed_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["direct_seed_rental"])){
                    $data[$cpid]["direct_seed_rental_ha"]=round($data[$cpid]["direct_seed_rental"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["direct_seed_meal"])){
                    $data[$cpid]["direct_seed_meal_ha"]=round($data[$cpid]["direct_seed_meal"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["trans_laborpull"])){
                    $data[$cpid]["trans_laborpull_ha"]=round($data[$cpid]["trans_laborpull"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["trans_replant"])){
                    $data[$cpid]["trans_replant_ha"]=round($data[$cpid]["trans_replant"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["trans_mat"])){
                    $data[$cpid]["trans_mat_ha"]=round($data[$cpid]["trans_mat"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["trans_meal"])){
                    $data[$cpid]["trans_meal_ha"]=round($data[$cpid]["trans_meal"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["mech_labor"])){
                    $data[$cpid]["mech_labor_ha"]=round($data[$cpid]["mech_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["mech_rental"])){
                    $data[$cpid]["mech_rental_ha"]=round($data[$cpid]["mech_rental"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["mech_replant"])){
                    $data[$cpid]["mech_replant_ha"]=round($data[$cpid]["mech_replant"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["mech_meals"])){
                    $data[$cpid]["mech_meals_ha"]=round($data[$cpid]["mech_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["fert_mgt_cost"])){
                    $data[$cpid]["fert_mgt_cost_ha"]=round($data[$cpid]["fert_mgt_cost"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["fert_mgt_meals"])){
                    $data[$cpid]["fert_mgt_meals_ha"]=round($data[$cpid]["fert_mgt_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["fert_mgt_labor"])){
                    $data[$cpid]["fert_mgt_labor_ha"]=round($data[$cpid]["fert_mgt_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_irr"])){
                    $data[$cpid]["wtr_mgt_irr_ha"]=round($data[$cpid]["wtr_mgt_irr"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_fuel"])){
                    $data[$cpid]["wtr_mgt_fuel_ha"]=round($data[$cpid]["wtr_mgt_fuel"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_labor"])){
                    $data[$cpid]["wtr_mgt_labor_ha"]=round($data[$cpid]["wtr_mgt_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_meals"])){
                    $data[$cpid]["wtr_mgt_meals_ha"]=round($data[$cpid]["wtr_mgt_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["pest_mgt_mollus"])){
                    $data[$cpid]["pest_mgt_mollus_ha"]=round($data[$cpid]["pest_mgt_mollus"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["pest_mgt_insect"])){
                    $data[$cpid]["pest_mgt_insect_ha"]=round($data[$cpid]["pest_mgt_insect"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["pest_mgt_fungi"])){
                    $data[$cpid]["pest_mgt_fungi_ha"]=round($data[$cpid]["pest_mgt_fungi"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["pest_mgt_roden"])){
                    $data[$cpid]["pest_mgt_roden_ha"]=round($data[$cpid]["pest_mgt_roden"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["pest_mgt_labor"])){
                    $data[$cpid]["pest_mgt_labor_ha"]=round($data[$cpid]["pest_mgt_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["pest_mgt_meals"])){
                    $data[$cpid]["pest_mgt_meals_ha"]=round($data[$cpid]["pest_mgt_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["weed_mgt_labor"])){
                    $data[$cpid]["weed_mgt_labor_ha"]=round($data[$cpid]["weed_mgt_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["weed_mgt_herbicide"])){
                    $data[$cpid]["weed_mgt_herbicide_ha"]=round($data[$cpid]["weed_mgt_herbicide"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["weed_mgt_appli"])){
                    $data[$cpid]["weed_mgt_appli_ha"]=round($data[$cpid]["weed_mgt_appli"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["weed_mgt_meals"])){
                    $data[$cpid]["weed_mgt_meals_ha"]=round($data[$cpid]["weed_mgt_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["harvest_labor"])){
                    $data[$cpid]["harvest_labor_ha"]=round($data[$cpid]["harvest_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["harvest_tresher"])){
                    $data[$cpid]["harvest_tresher_ha"]=round($data[$cpid]["harvest_tresher"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["harvest_harvester"])){
                    $data[$cpid]["harvest_harvester_ha"]=round($data[$cpid]["harvest_harvester"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["harvest_hauling"])){
                    $data[$cpid]["harvest_hauling_ha"]=round($data[$cpid]["harvest_hauling"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["harvest_matt"])){
                    $data[$cpid]["harvest_matt_ha"]=round($data[$cpid]["harvest_matt"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["harvest_meals"])){
                    $data[$cpid]["harvest_meals_ha"]=round($data[$cpid]["harvest_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["oth_labor"])){
                    $data[$cpid]["oth_labor_ha"]=round($data[$cpid]["oth_labor"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["oth_matt"])){
                    $data[$cpid]["oth_matt_ha"]=round($data[$cpid]["oth_matt"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["oth_land"])){
                    $data[$cpid]["oth_land_ha"]=round($data[$cpid]["oth_land"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["oth_interest"])){
                    $data[$cpid]["oth_interest_ha"]=round($data[$cpid]["oth_interest"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["totlabexp_totcost"])){
                    $data[$cpid]["totlabexp_totcost_perha"]=round($data[$cpid]["totlabexp_totcost"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["totmatexp_totcost"])){
                    $data[$cpid]["totmatexp_totcost_perha"]=round($data[$cpid]["totmatexp_totcost"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["totmachrent_totcost"])){
                    $data[$cpid]["totalmachrent_totcost_perha"]=round($data[$cpid]["totmachrent_totcost"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["totmachrent_totcost"])){
                    $data[$cpid]["totalmachrent_totcost_perha"]=round($data[$cpid]["totmachrent_totcost"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["total_fert"])){
                    $data[$cpid]["total_fert_ha"]=round($data[$cpid]["total_fert"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["total_chemical"])){
                    $data[$cpid]["total_chemical_ha"]=round($data[$cpid]["total_chemical"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["total_meals"])){
                    $data[$cpid]["total_meals_ha"]=round($data[$cpid]["total_meals"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["totirrigexp_totcost"])){
                    $data[$cpid]["totirrigexp_totcost_perha"]=round($data[$cpid]["totirrigexp_totcost"]/$techno_area,2);
               }if($techno_area>0 && isset($data[$cpid]["totprod_totcost"])){
                    $data[$cpid]["totprod_totcost_perha"]=round($data[$cpid]["totprod_totcost"]/$techno_area,2);
               }
               if($techno_area>0 && isset($data[$cpid]["fert_mgt_meals"])){
                    $data[$cpid]["fert_mgt_meals_ha"]=round($data[$cpid]["fert_mgt_meals"]/$techno_area,2);
               }
               if($techno_area>0 && isset($data[$cpid]["fert_mgt_labor"])){
                    $data[$cpid]["fert_mgt_labor_ha"]=round($data[$cpid]["fert_mgt_labor"]/$techno_area,2);
               } if($techno_area>0 && isset($data[$cpid]["tototherexp_totcost"])){
                    $data[$cpid]["tototherexp_totcost_totcost"]=round($data[$cpid]["tototherexp_totcost"]/$techno_area,2);
               }
               

               if(isset($data[$cpid]["bagsnot14"]) || isset($data[$cpid]["bagsnourea"]) || isset($data[$cpid]["bagsnoammophos"]) || isset($data[$cpid]["bagsnosul"]) || isset($data[$cpid]["bagsno"]) || isset($data[$cpid]["bagsnomop"])){
                    if(!isset($data[$cpid]["bagsnot14"]) ){
                         $data[$cpid]["bagsnot14"]=0;
                    }
                    if(!isset($data[$cpid]["bagsnourea"])){
                         $data[$cpid]["bagsnourea"]=0;
                    }
                    if(!isset($data[$cpid]["bagsnoammophos"])){
                         $data[$cpid]["bagsnoammophos"]=0;
                    }
                    if(!isset($data[$cpid]["bagsnosul"])){
                         $data[$cpid]["bagsnosul"]=0;
                    }
                    if(!isset($data[$cpid]["bagsno"])){
                         $data[$cpid]["bagsno"]=0;
                    }
                    if(!isset($data[$cpid]["bagsnomop"])){
                         $data[$cpid]["bagsnomop"]=0;
                    }


                    $data[$cpid]["n_npk"] =(($data[$cpid]["bagsnot14"]*14)/2)+(($data[$cpid]["bagsnourea"]*46)/2)+(($data[$cpid]["bagsnoammophos"]*16)/2)+(($data[$cpid]["bagsnosul"]*21)/2)+(($data[$cpid]["bagsno"]*17)/2);
                    if(isset($data[$cpid]["n_npk"])){
                         if($data[$cpid]["n_npk"]>0 && $techno_area){
                              $data[$cpid]["n_npk_perha"] = number_format($data[$cpid]["n_npk"]/$techno_area,2);
                         }
                         
                    }
                    
                    

                    $data[$cpid]["p_npk"] =(($data[$cpid]["bagsnot14"]*14)/2)+(($data[$cpid]["bagsnoammophos"]*20)/2);
                    if(isset($data[$cpid]["p_npk"])){

                         if($data[$cpid]["p_npk"]>0 && $techno_area){
                              $data[$cpid]["p_npk_perha"] = number_format($data[$cpid]["p_npk"]/$techno_area,2);
                         }

                         
                    }

                    $data[$cpid]["k_npk"] =(($data[$cpid]["bagsnot14"]*14)/2)+(($data[$cpid]["bagsno"]*17)/2)+(($data[$cpid]["bagsnomop"]*60)/2);
                    if(isset($data[$cpid]["k_npk"])){
                         
                         if($data[$cpid]["k_npk"]>0 && $techno_area){
                              $data[$cpid]["k_npk_perha"] = number_format($data[$cpid]["k_npk"]/$techno_area,2);
                         }
                    }

               }

               array_push($excel_data, array(                            
                            
                              "last_name" => isset($lastname) ? $lastname : "",
                              "first_name" => isset($firstname) ? $firstname : "",
                              "middle_name" =>  isset($middlename) ? $middlename : "",
                              "ext_name" =>  isset($extname) ? $extname : "",
                              "contact_no" => isset($contact_no) ? $contact_no : "",
                              "TechnoDemoStart" =>  isset($TechnoDemoStart) ? $TechnoDemoStart : "",
                              "TechnoDemoArea" => isset($techno_area) ? number_format($techno_area,2) : "",
                              "prov" => isset($province) ? $province : "",
                              "Tenurial Status" =>  isset($area_harvested) ? $area_harvested : "",
                              "CropEstab" =>  isset($crop_establishment) ? $crop_establishment : "",
                              "area_harvested" => isset($area_harvested) ? number_format($area_harvested,2) : "",        
                              "PRODN(kg)_ws" => isset($prodn_kg_ws) ? number_format($prodn_kg_ws,2) : "",
                              "YIELD(kg/ha)_ws" =>  isset($YIELD_kg__ha_ws) ? number_format($YIELD_kg__ha_ws,2) : "",
                              "Dry Yield_ws" =>  "nawawala",
                              "TotalNoSack_ws" =>  isset($TotalNoSack_ws) ? number_format($TotalNoSack_ws,2) : "",
                              "WeightSack_ws" => isset($seeding_weight_bags) ? number_format($seeding_weight_bags,2) : "",
                              "price/kg/fresh_ws" => isset($priceper_kg_fr) ? number_format($priceper_kg_fr,2) : "",
                              "SEEDRATEHA_WS" => isset($SEEDRATEHA_WS) ? number_format($SEEDRATEHA_WS,2) : "",
                              "SEEDCOHA_WS" => isset($SEEDCOHA_WS) ? number_format($SEEDCOHA_WS,2) : "",
                              "cost/ha_fert_ws" => isset($data[$cpid]["fert_mgt_cost_ha"]) ? number_format($data[$cpid]["fert_mgt_cost_ha"],2) : "",
                              "n_npk" => isset($data[$cpid]["n_npk"]) ?$data[$cpid]["n_npk"] : "",
                              "p_npk" => isset($data[$cpid]["p_npk"]) ?$data[$cpid]["p_npk"] : "",
                              "k_npk" => isset($data[$cpid]["k_npk"]) ?$data[$cpid]["k_npk"] : "",
                              "herbAI_ws" => isset($herbAI_ws) ?$herbAI_ws : "",
                              "insAI_ws	" => isset($insAI_ws	) ?$insAI_ws	 : "",
                              "fuAI_ws" => isset($fuAI_ws) ?$fuAI_ws : "",
                              "rodAI_ws" => isset($rodAI_ws) ?$rodAI_ws : "",
                              "molAI_ws" => isset($molAI_ws) ?$molAI_ws : "",
                              "hercoha_ws" => isset($hercoha_ws) ?$hercoha_ws : "",
                              "insecoha_ws" => isset($insecoha_ws) ?$insecoha_ws : "",
                              "fungcoha_ws" => isset($fungcoha_ws) ?$fungcoha_ws : "",
                              "rodentcoha_ws" => isset($rodentcoha_ws) ?$rodentcoha_ws : "",
                              "molluskcoha_ws" => isset($molluskcoha_ws) ?$molluskcoha_ws : "",
                              "Hired_Seed preparation_ws" => isset($data[$cpid]["seed_bed_labor"]) ? number_format($data[$cpid]["seed_bed_labor"],2) : "",
                              "Hired_Seedling prep mech TPR_ws" => isset($data[$cpid]["mech_labor"]) ? number_format($data[$cpid]["mech_labor"],2) : "",
                              "Hired_Seedling prep manual TPR_ws" => isset($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"]) ? number_format($data[$cpid]["Hired_Seedling_prep_manual_TPR_ws"],2) : "",
                              "Hired_Seedling prep_ws" => isset($data[$cpid]["Hired_Seedling_prep_ws"]) ? $data[$cpid]["Hired_Seedling_prep_ws"] : "",
                              "Hired_Land Preparation_ws" =>  "",
                              "Hired_Crop Establishment_ws" => isset($data[$cpid]["Hired_Crop_Establishment_ws"]) ? number_format($data[$cpid]["Hired_Crop_Establishment_ws"],2) : "",
                              "Hired_Crop care & maintenance_ws" => isset($data[$cpid]["Hired_Crop_care_maintenance_ws"]) ? number_format($data[$cpid]["Hired_Crop_care_maintenance_ws"],2) : "",
                              "Hired_Harvesting and Threshing_ws" => isset($data[$cpid]["Hired_Harvesting_and_Threshing_ws"]) ? number_format($data[$cpid]["Hired_Harvesting_and_Threshing_ws"],2) : "",
                              "Hired_CombineHarvester_ws" => isset($data[$cpid]["Hired_CombineHarvester_ws"]) ? number_format($data[$cpid]["Hired_CombineHarvester_ws"],2) : "",
                              "Hired_Post Harvest Labor_ws" =>  "ask to maam Irish",
                              "Phlcost_ws" => isset($data[$cpid]["Phlcost_ws"]) ? number_format($data[$cpid]["Phlcost_ws"],2) : "",
                              "foodcostha_ws" => isset($data[$cpid]["foodcostha_ws"]) ? number_format($data[$cpid]["foodcostha_ws"],2) : "",
                              "Irrigationcostha_ws" => isset($data[$cpid]["Irrigationcostha_ws"]) ? number_format($data[$cpid]["Irrigationcostha_ws"],2) : "",
                              "transcostha_ws" =>  "ask to maam Irish",
                              "Landrent_ha_ws" => isset($data[$cpid]["Landrent_ha_ws"]) ? number_format($data[$cpid]["Landrent_ha_ws"],2) : "",
                              "otherinputcostha_ws" => isset($data[$cpid]["otherinputcostha_ws"]) ? number_format($data[$cpid]["otherinputcostha_ws"],2) : "",

                              
                              
                              
                             
                         ));
 


                    
                    


          } //foreach farmer data    
          
          
         
      
          $documentation = array();
         
              
           
          $documentation = array();
          array_push($documentation, array("Headers" => "LastName","Season" => "" ,"Remarks"=>"","Notes"=> ""));
          array_push($documentation, array("Headers" => "FirstName","Season" => "" ,"Remarks"=>"","Notes"=> ""));
          array_push($documentation, array("Headers" => "TechnoDemoStart","Season" => "" ,"Remarks"=>"Year and season, first techno demo established","Notes"=> ""));
          array_push($documentation, array("Headers" => "TechnoDemoArea","Season" => "" ,"Remarks"=>"Techno demo area","Notes"=> ""));
          array_push($documentation, array("Headers" => "Province","Season" => "" ,"Remarks"=>"Province","Notes"=> ""));
          array_push($documentation, array("Headers" => "Tenurial Status","Season" => "" ,"Remarks"=>"Tenurial status","Notes"=> ""));
          array_push($documentation, array("Headers" => "CropEstab","Season" => "" ,"Remarks"=>"Crop establishment","Notes"=> ""));
          array_push($documentation, array("Headers" => "AreaHarvested_ws","Season" => "WS" ,"Remarks"=>"Area harvested","Notes"=> ""));
          array_push($documentation, array("Headers" => "PRODN(kg)_ws","Season" => "WS" ,"Remarks"=>"Production in kilogram","Notes"=> ""));
          array_push($documentation, array("Headers" => "YIELD(kg/ha)_ws","Season" => "WS" ,"Remarks"=>"Yield in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "Dry Yield_ws","Season" => "WS" ,"Remarks"=>"Dry yield","Notes"=> ""));
          array_push($documentation, array("Headers" => "TotalNoSack_ws","Season" => "WS" ,"Remarks"=>"Total no of sacks","Notes"=> ""));
          array_push($documentation, array("Headers" => "WeightSack_ws","Season" => "WS" ,"Remarks"=>"Weight per sack","Notes"=> ""));
          array_push($documentation, array("Headers" => "price/kg/fresh_ws","Season" => "WS" ,"Remarks"=>"Price per kg (Fresh)","Notes"=> ""));
          array_push($documentation, array("Headers" => "SEEDRATEHA_WS","Season" => "WS" ,"Remarks"=>"Seeding rate per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "SEEDCOHA_WS","Season" => "WS" ,"Remarks"=>"Seed cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "cost/ha_fert_ws","Season" => "WS" ,"Remarks"=>"Fertilizer cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "nqha_ws","Season" => "WS" ,"Remarks"=>"N quantity in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "pqha_ws","Season" => "WS" ,"Remarks"=>"P quantity in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "kqha_ws","Season" => "WS" ,"Remarks"=>"K quantity in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "herbAI_ws","Season" => "WS" ,"Remarks"=>"Herbicide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "insAI_ws","Season" => "WS" ,"Remarks"=>"Insecticide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "fuAI_ws","Season" => "WS" ,"Remarks"=>"Fungicide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "rodAI_ws","Season" => "WS" ,"Remarks"=>"Rodenticide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "molAI_ws","Season" => "WS" ,"Remarks"=>"Molluscicide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "hercoha_ws","Season" => "WS" ,"Remarks"=>"Herbicide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "insecoha_ws","Season" => "WS" ,"Remarks"=>"Insecticide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "fungcoha_ws","Season" => "WS" ,"Remarks"=>"Fungicide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "rodentcoha_ws","Season" => "WS" ,"Remarks"=>"Rodenticide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "molluskcoha_ws","Season" => "WS" ,"Remarks"=>"Molluscicide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seed preparation_ws","Season" => "WS" ,"Remarks"=>"Seedbed preparation total labor cost ","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seedling prep mech TPR_ws","Season" => "WS" ,"Remarks"=>"Seedling preparation, Mechanical transplanter total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seedling prep manual TPR_ws","Season" => "WS" ,"Remarks"=>"Seedling preparation, Manual transplanting total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seedling prep_ws","Season" => "WS" ,"Remarks"=>"Seedling preparation, Mixed method total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Land Preparation_ws","Season" => "WS" ,"Remarks"=>"Land preparation, total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Crop Establishment_ws","Season" => "WS" ,"Remarks"=>"Crop establishment, total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Crop care & maintenance_ws","Season" => "WS" ,"Remarks"=>"Crop care and maintenance, total labor cost","Notes"=> "Irrigation, drainage, fertlizer at pesticide application, non chem mgt"));
          array_push($documentation, array("Headers" => "Hired_Harvesting and Threshing_ws","Season" => "WS" ,"Remarks"=>"Manual harvesting and threshing total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_CombineHarvester_ws","Season" => "WS" ,"Remarks"=>"Combine harvesting total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Post Harvest Labor_ws","Season" => "WS" ,"Remarks"=>"Post harvest total labor cost","Notes"=> "Hauling, cleaning"));
          array_push($documentation, array("Headers" => "Phlcost_ws","Season" => "WS" ,"Remarks"=>"Permanent hired labor, total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "foodcostha_ws","Season" => "WS" ,"Remarks"=>"Food cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "Irrigationcostha_ws","Season" => "WS" ,"Remarks"=>"Irrigation cost per hectare","Notes"=> "Fuel and oil, and/or rent sa pump"));
          array_push($documentation, array("Headers" => "transcostha_ws","Season" => "WS" ,"Remarks"=>"Transportation cost ","Notes"=> ""));
          array_push($documentation, array("Headers" => "Landrent/ha_ws","Season" => "WS" ,"Remarks"=>"Land rent per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "otherinputcostha_ws","Season" => "WS" ,"Remarks"=>"Other input total costs","Notes"=> "sum of"));
          array_push($documentation, array("Headers" => "AreaHarvested_ds","Season" => "DS" ,"Remarks"=>"Area harvested in hectares","Notes"=> ""));
          array_push($documentation, array("Headers" => "PRODN(kg)_ds","Season" => "DS" ,"Remarks"=>"Production in kilogram","Notes"=> ""));
          array_push($documentation, array("Headers" => "YIELD(kg/ha)_ds","Season" => "DS" ,"Remarks"=>"Yield in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "Dry Yield_ds","Season" => "DS" ,"Remarks"=>"Dry yield in kilogram","Notes"=> ""));
          array_push($documentation, array("Headers" => "TotalNoSack_ds","Season" => "DS" ,"Remarks"=>"Total no of sacks","Notes"=> ""));
          array_push($documentation, array("Headers" => "WeightSack_ds","Season" => "DS" ,"Remarks"=>"Weight per sack in kilogram","Notes"=> ""));
          array_push($documentation, array("Headers" => "price/kg/fresh_ds","Season" => "DS" ,"Remarks"=>"Price per kg (Fresh)","Notes"=> ""));
          array_push($documentation, array("Headers" => "SEEDRATEHA_ds","Season" => "DS" ,"Remarks"=>"Seeding rate, kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "SEEDCOHA_ds","Season" => "DS" ,"Remarks"=>"Seed cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "cost/ha_fert_ds","Season" => "DS" ,"Remarks"=>"Fertilizer cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "nqha_ds","Season" => "DS" ,"Remarks"=>"N quantity in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "pqha_ds","Season" => "DS" ,"Remarks"=>"P quantity in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "kqha_ds","Season" => "DS" ,"Remarks"=>"K quantity in kilogram per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "herbAI_ds","Season" => "DS" ,"Remarks"=>"Herbicide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "insAI_ds","Season" => "DS" ,"Remarks"=>"Insecticide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "fuAI_ds","Season" => "DS" ,"Remarks"=>"Fungicide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "rodAI_ds","Season" => "DS" ,"Remarks"=>"Rodenticide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "molAI_ds","Season" => "DS" ,"Remarks"=>"Molluscicide Active Ingredient","Notes"=> ""));
          array_push($documentation, array("Headers" => "hercoha_ds","Season" => "DS" ,"Remarks"=>"Herbicide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "insecoha_ds","Season" => "DS" ,"Remarks"=>"Insecticide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "fungcoha_ds","Season" => "DS" ,"Remarks"=>"Fungicide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "rodentcoha_ds","Season" => "DS" ,"Remarks"=>"Rodenticide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "molluskcoha_ds","Season" => "DS" ,"Remarks"=>"Molluscicide cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seed preparation_ds","Season" => "DS" ,"Remarks"=>"Seedbed preparation total labor cost ","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seedling prep mech TPR_ds","Season" => "DS" ,"Remarks"=>"Seedling preparation, Mechanical transplanter total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seedling prep manual TPR_ds","Season" => "DS" ,"Remarks"=>"Seedling preparation, Manual transplanting total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Seedling prep_ds","Season" => "DS" ,"Remarks"=>"Seedling preparation, Mixed method total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Land Preparation_ds","Season" => "DS" ,"Remarks"=>"Land preparation, total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Crop Establishment_ds","Season" => "DS" ,"Remarks"=>"Crop establishment, total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Crop care & maintenance_ds","Season" => "DS" ,"Remarks"=>"Crop care and maintenance, total labor cost","Notes"=> "Irrigation, drainage, fertlizer at pesticide application, non chem mgt"));
          array_push($documentation, array("Headers" => "Hired_Harvesting and Threshing_ds","Season" => "DS" ,"Remarks"=>"Manual harvesting and threshing total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_CombineHarvester_ds","Season" => "DS" ,"Remarks"=>"Combine harvesting total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "Hired_Post Harvest Labor_ds","Season" => "DS" ,"Remarks"=>"Post harvest total labor cost","Notes"=> "Hauling, cleaning"));
          array_push($documentation, array("Headers" => "Phlcost_ds","Season" => "DS" ,"Remarks"=>"Permanent hired labor, total labor cost","Notes"=> ""));
          array_push($documentation, array("Headers" => "foodcostha_ds","Season" => "DS" ,"Remarks"=>"Food cost per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "Irrigationcostha_ds","Season" => "DS" ,"Remarks"=>"Irrigation cost per hectare","Notes"=> "Fuel and oil, and/or rent sa pump"));
          array_push($documentation, array("Headers" => "transcostha_ds","Season" => "DS" ,"Remarks"=>"Transportation cost ","Notes"=> ""));
          array_push($documentation, array("Headers" => "Landrent/ha_ds","Season" => "DS" ,"Remarks"=>"Land rent per hectare","Notes"=> ""));
          array_push($documentation, array("Headers" => "otherinputcostha_ds","Season" => "DS" ,"Remarks"=>"Other input total costs","Notes"=> "sum of"));
          










          $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
       return Excel::create("RCEF-PalaySikatan-baseline-Input-Output-Matrix"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $documentation) {
           $excel->sheet("Sheet 1", function($sheet) use ($excel_data) {
               $sheet->fromArray($excel_data);
               $sheet->getStyle('J:EL')->getAlignment()->setHorizontal('center');
               
           });          
           $excel->sheet("documentation", function($sheet) use ($documentation) {
               $sheet->fromArray($documentation);
           });
          
       })->download('xlsx'); 



   }


   private function exportExcel_npk($station){
          
     
     $this->reconnect_cip();          
     if($station == "0")$station = "%";
   
     $crop_establishment_arr = array(
          "manual_transplanting" => "Manual Transplanting",
          "mechanized_transplanting" => "Mechanical Transplanting",
          "drum_seeding" => "Manual Direct-Seeding",
          "drum_seeding_sp" => "Mechanical Direct-Seeding"
     );

          $farmer_info = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info as f")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production as c", "c.farmer_id_fk", "=", "f.fid")
          ->join($GLOBALS['season_prefix']."rcep_palaysikatan.station_entries as s", "s.ref_id", "=", "f.fid")
          ->where("s.station", "like", $station
          )
          ->orderBy("c.farmer_id_fk")
          ->groupBy("c.cpid")
          ->groupBy("c.variety_planted")
          ->get();
          $excel_data = array();
   
          foreach ($farmer_info as $farmer_data) {
              
          
               $station = DB::table("geotag_db2.tbl_station")->where("stationId", $farmer_data->station)->value("stationName");
               $region = $farmer_data->add_region;
               $region_no = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("regionName", $farmer_data->add_region)->value("regCode");
               $province = $farmer_data->add_province;
               $municipality = $farmer_data->add_municipality;
               $brgy = $farmer_data->barangay;
               $farmer_name = trim($farmer_data->f_full_name);

               $lastname = $farmer_data->f_lastName;
               $firstname = $farmer_data->f_firstName;
               $middlename = $farmer_data->f_middleName;                             
               $extname = $farmer_data->f_extName;
               
               $variety = $farmer_data->variety_planted;
               $crop_establishment = $crop_establishment_arr[$farmer_data->crop_establishment];
               $techno_area = $farmer_data->techno_area;
               
               $cpid = $farmer_data->cpid;

               $priceper_kg_dr = $farmer_data->dry_palay_price;
               $priceper_kg_fr = $farmer_data->fresh_palay_price;
               $cropCutTotal= ($farmer_data->sample_1 +$farmer_data->sample_2+$farmer_data->sample_3);
               $cropCutTotalYield =(($cropCutTotal/15)*10000)/1000;
               if($farmer_data->sold_as == "Fresh"){
                    $costper_kg = $farmer_data->fresh_palay_price;
               }else{
                     $costper_kg = $farmer_data->dry_palay_price;
               }
               //CEHECK THIS
             /*   if($farmer_data->area_harvested == 0){
                    $yield =0;
               }else{
                    $yield = number_format((($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags)/$farmer_data->area_harvested)/1000,2);
               } */
               if($farmer_data->harvest_no_bags>0 && $farmer_data->harvest_weight_bags){
                     $yield = number_format(($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags),2);
               }else{
                    $yield =0;
               }
                

               if($yield>0 && $techno_area>0){
                      $yield_ha =number_format(($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags)/$techno_area,2);                      
               }else{
                    $yield_ha =0;
               }
               
                    //activities
            
                       $activity_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.lib_planting', 'lib_planting.planting_id','=','data_entries.planting_id')
                    ->join($GLOBALS['season_prefix'].'rcep_palaysikatan.farmer_info', 'farmer_info.fid','=','data_entries.farmer_id')
                    //->where("lib_planting.seeding_for", "mechanized_transplanting")
                  //  ->where("lib_planting.activity_code", "fm_first_application_basal")
                    ->where(function($query){
                     $query->Where("lib_planting.activity_code", "fm_first_application_basal")
                    ->orWhere("lib_planting.activity_code", "fm_second_application_top_dress")
                    ->orWhere("lib_planting.activity_code", "fm_third_application_basal");
                  })
                    ->where("data_entries.ref_cp_id", $cpid)
                    ->where("farmer_info.fid", $farmer_data->fid)
                /*     ->where("data_entries.ref_cp_id", 31)
                    ->where("farmer_info.fid", 36) */
                    ->orderBy('lib_planting.planting_id','ASC')
                    ->get();
                   // dd($activity_entries);
                        
                         $data_1st = array();
                         $data_2nd = array();
                         $data_3rd = array();
                    foreach ($activity_entries as $entry){
                         $fertilizer_type_1st="";
                         $quantity_1st="";
                         $unit_1st="";
                         $unitCost_1st="";
                         $totalCost_1st="";
                         $category_1st="";
                         $dateApplication_1st="";
                         $fertilizer_type_2nd="";
                         $quantity_2nd="";
                         $unit_2nd="";
                         $unitCost_2nd="";
                         $totalCost_2nd="";
                         $category_2nd="";
                         $dateApplication_2nd="";
                         $fertilizer_type_3rd="";
                         $quantity_3rd="";
                         $unit_3rd="";
                         $unitCost_3rd="";
                         $totalCost_3rd="";
                         $category_3rd="";
                         $dateApplication_3rd="";
                         if($farmer_data->crop_establishment=="manual_transplanting"){
                              // Number of Bags (14-14-14-)
                            
                              if($entry->planting_id >= 297 && $entry->planting_id <=316){
                                   /* 1st */
                                   if($entry->planting_id >= 297 && $entry->planting_id <=301){
                                        $fertilizer_type_1st=$entry->particulars;
                                        $quantity_1st=$entry->qty;
                                        $unit_1st=$entry->unit;
                                        $unitCost_1st=$entry->unit_cost;
                                        $totalCost_1st=$entry->unit_cost*$entry->qty;
                                        $category_1st=$entry->fertilizer_category;
                                        $dateApplication_1st=$entry->date;
                                   }
                                   /* 2nd */
                                   if($entry->planting_id >= 304 && $entry->planting_id <=309){
                                        $fertilizer_type_2nd=$entry->particulars;
                                        $quantity_2nd=$entry->qty;
                                        $unit_2nd=$entry->unit;
                                        $unitCost_2nd=$entry->unit_cost;
                                        $totalCost_2nd=$entry->unit_cost*$entry->qty;
                                        $category_2nd=$entry->fertilizer_category;
                                        $dateApplication_2nd=$entry->date;
                                   }
                                    /* 3rd */
                                   if($entry->planting_id >= 312 && $entry->planting_id <=316){
                                        $fertilizer_type_3rd=$entry->particulars;
                                        $quantity_3rd=$entry->qty;
                                        $unit_3rd=$entry->unit;
                                        $unitCost_3rd=$entry->unit_cost;
                                        $totalCost_3rd=$entry->unit_cost*$entry->qty;
                                        $category_3rd=$entry->fertilizer_category;
                                        $dateApplication_3rd=$entry->date;
                                   }
                                   
                                                            
                              }


                         } //end for manual transplanting /* joe */
                         elseif($farmer_data->crop_establishment=="mechanized_transplanting"){
                                             
                         if($entry->planting_id >= 419 && $entry->planting_id <=439){
                           /* 1st */
                                if($entry->planting_id >= 419  && $entry->planting_id <=423){
                                   $fertilizer_type_1st=$entry->particulars;
                                   $quantity_1st=$entry->qty;
                                   $unit_1st=$entry->unit;
                                   $unitCost_1st=$entry->unit_cost;
                                   $totalCost_1st=$entry->unit_cost*$entry->qty;
                                   $category_1st=$entry->fertilizer_category;
                                   $dateApplication_1st=$entry->date;
                              }
                              /* 2nd */
                              if($entry->planting_id >= 426 && $entry->planting_id <=431){
                                   $fertilizer_type_2nd=$entry->particulars;
                                   $quantity_2nd=$entry->qty;
                                   $unit_2nd=$entry->unit;
                                   $unitCost_2nd=$entry->unit_cost;
                                   $totalCost_2nd=$entry->unit_cost*$entry->qty;
                                   $category_2nd=$entry->fertilizer_category;
                                   $dateApplication_2nd=$entry->date;
                              }
                               /* 3rd */
                              if($entry->planting_id >= 434 && $entry->planting_id <=439){
                                   $fertilizer_type_3rd=$entry->particulars;
                                   $quantity_3rd=$entry->qty;
                                   $unit_3rd=$entry->unit;
                                   $unitCost_3rd=$entry->unit_cost;
                                   $totalCost_3rd=$entry->unit_cost*$entry->qty;
                                   $category_3rd=$entry->fertilizer_category;
                                   $dateApplication_3rd=$entry->date;
                              }
                          
                                   
                              
                         }

                        

                         
                         } //mechanized_transplanting
                         else if ($farmer_data->crop_establishment=="drum_seeding"){

                              if($entry->planting_id >= 175 && $entry->planting_id <=194 ){
                                   
                           /* 1st */
                                if($entry->planting_id >= 175  && $entry->planting_id <=179){
                                   $fertilizer_type_1st=$entry->particulars;
                                   $quantity_1st=$entry->qty;
                                   $unit_1st=$entry->unit;
                                   $unitCost_1st=$entry->unit_cost;
                                   $totalCost_1st=$entry->unit_cost*$entry->qty;
                                   $category_1st=$entry->fertilizer_category;
                                   $dateApplication_1st=$entry->date;
                              }
                              /* 2nd */
                              if($entry->planting_id >= 182  && $entry->planting_id <=187){
                                   $fertilizer_type_2nd=$entry->particulars;
                                   $quantity_2nd=$entry->qty;
                                   $unit_2nd=$entry->unit;
                                   $unitCost_2nd=$entry->unit_cost;
                                   $totalCost_2nd=$entry->unit_cost*$entry->qty;
                                   $category_2nd=$entry->fertilizer_category;
                                   $dateApplication_2nd=$entry->date;
                              }
                               /* 3rd */
                              if($entry->planting_id >= 190 && $entry->planting_id <=194){
                                   $fertilizer_type_3rd=$entry->particulars;
                                   $quantity_3rd=$entry->qty;
                                   $unit_3rd=$entry->unit;
                                   $unitCost_3rd=$entry->unit_cost;
                                   $totalCost_3rd=$entry->unit_cost*$entry->qty;
                                   $category_3rd=$entry->fertilizer_category;
                                   $dateApplication_3rd=$entry->date;
                              }
                          
                                   
                              }

                              

                         } //drum_seeding
                         else if ($farmer_data->crop_establishment=="drum_seeding_sp"){
                              if($entry->planting_id >= 48 && $entry->planting_id <=66 || $entry->planting_id ==497 || $entry->planting_id >= 67 && $entry->planting_id <=70){
                                  /* 1st */
                                if($entry->planting_id >= 48  && $entry->planting_id <=52){
                                   $fertilizer_type_1st=$entry->particulars;
                                   $quantity_1st=$entry->qty;
                                   $unit_1st=$entry->unit;
                                   $unitCost_1st=$entry->unit_cost;
                                   $totalCost_1st=$entry->unit_cost*$entry->unit;
                                   $category_1st=$entry->fertilizer_category;
                                   $dateApplication_1st=$entry->date;
                              }
                              /* 2nd */
                              if($entry->planting_id >= 55  && $entry->planting_id <=59){
                                   $fertilizer_type_2nd=$entry->particulars;
                                   $quantity_2nd=$entry->qty;
                                   $unit_2nd=$entry->unit;
                                   $unitCost_2nd=$entry->unit_cost;
                                   $totalCost_2nd=$entry->unit_cost*$entry->unit;
                                   $category_2nd=$entry->fertilizer_category;
                                   $dateApplication_2nd=$entry->date;
                              }
                               /* 3rd */
                              if($entry->planting_id >= 63 && $entry->planting_id <=68 || $entry->planting_id ==497){
                                   $fertilizer_type_3rd=$entry->particulars;
                                   $quantity_3rd=$entry->qty;
                                   $unit_3rd=$entry->unit;
                                   $unitCost_3rd=$entry->unit_cost;
                                   $totalCost_3rd=$entry->unit_cost*$entry->unit;
                                   $category_3rd=$entry->fertilizer_category;
                                   $dateApplication_3rd=$entry->date;
                              }
                          
                                   
                              }                         
                         } //drum_seeding_sp
                         if($fertilizer_type_1st!=""){
                              array_push($data_1st, array(
                                   "lastname" => $farmer_data->f_lastName,
                                   "firstname" => $farmer_data->f_firstName,
                                   "middlename" => $farmer_data->f_middleName,                              
                                   "extname" => $farmer_data->f_extName, 
                                   /* 1st */  
                                   "st_fertilizer_type" => $fertilizer_type_1st,  
                                   "st_quantity" => $quantity_1st,                                  
                                   "st_unit" => $unit_1st,                                  
                                   "st_unitCost" => $unitCost_1st,                                  
                                   "st_totalCost" => $totalCost_1st,                                  
                                   "st_category" => $category_1st,                                  
                                   "st_dateApplication" => $dateApplication_1st,                                   
                              ));
                         }

                         if($fertilizer_type_2nd!=""){
                              array_push($data_2nd, array( 
                                     /* 2nd */
                              "nd_fertilizer_type" => $fertilizer_type_2nd,  
                              "nd_quantity" => $quantity_2nd,                                  
                              "nd_unit" => $unit_2nd,                                  
                              "nd_unitCost" => $unitCost_2nd,                                  
                              "nd_totalCost" => $totalCost_2nd,                                  
                              "nd_category" => $category_2nd,                                  
                              "nd_dateApplication" => $dateApplication_2nd,                                 
                              ));
                         }

                         if($fertilizer_type_3rd!=""){
                              array_push($data_3rd, array( 
                                     /* 2nd */
                              "rd_fertilizer_type" => $fertilizer_type_3rd,  
                              "rd_quantity" => $quantity_3rd,                                  
                              "rd_unit" => $unit_3rd,                                  
                              "rd_unitCost" => $unitCost_3rd,                                  
                              "rd_totalCost" => $totalCost_3rd,                                  
                              "rd_category" => $category_3rd,                                  
                              "rd_dateApplication" => $dateApplication_3rd,                                    
                              ));
                         }
                          
                    } 
                    if(count($data_1st) >0 || count($data_2nd) >0 || count($data_3rd) >0){
                        
                   

                    foreach ($data_1st as $key => $value) {
                     
                         array_push($excel_data, array(
                              "lastname" => $value['lastname'],
                              "firstname" => $value['firstname'],
                              "middlename" => $value['middlename'],                              
                              "extname" => $value['extname'], 
                              /* 1st */  
                              "[1] Fertilizer_type" => isset($value['st_fertilizer_type']) ? $value['st_fertilizer_type'] : "",
                              "[1] Quantity" =>  isset($value['st_quantity']) ?$value['st_quantity'] :"",
                              "[1] Unit" =>  isset($value['st_unit']) ?$value['st_unit'] :"",
                              "[1] UnitCost" =>  isset($value['st_unitCost']) ?$value['st_unitCost'] :"",
                              "[1] TotalCost" =>  isset($value['st_totalCost']) ?$value['st_totalCost'] :"",
                              "[1] Category" =>  isset($value['st_category']) ?$value['st_category'] :"",
                              "[1] DateApplication" =>  isset($value['st_dateApplication'])?$value['st_dateApplication'] :"",
                              /* 2nd */
                              "[2] Fertilizer_type" => isset($data_2nd[$key]['nd_fertilizer_type']) ? $data_2nd[$key]['nd_fertilizer_type'] : "",
                              "[2] Quantity" => isset($data_2nd[$key]['nd_quantity']) ? $data_2nd[$key]['nd_quantity'] : "",
                              "[2] Unit" => isset($data_2nd[$key]['nd_unit']) ? $data_2nd[$key]['nd_unit'] : "",
                              "[2] UnitCost" => isset($data_2nd[$key]['nd_unitCost']) ? $data_2nd[$key]['nd_unitCost'] : "",
                              "[2] TotalCost" => isset($data_2nd[$key]['nd_totalCost']) ? $data_2nd[$key]['nd_totalCost'] : "",
                              "[2] Category" => isset($data_2nd[$key]['nd_category']) ? $data_2nd[$key]['nd_category'] : "",
                              "[2] DateApplication" => isset($data_2nd[$key]['nd_dateApplication']) ? $data_2nd[$key]['nd_dateApplication'] : "",
                              /* 3rd */
                              "[3] Fertilizer_type" => isset($data_3rd[$key]['rd_fertilizer_type']) ? $data_3rd[$key]['rd_fertilizer_type'] : "",
                              "[3] Quantity" => isset($data_3rd[$key]['rd_quantity']) ? $data_3rd[$key]['rd_quantity'] : "",
                              "[3] Unit" => isset($data_3rd[$key]['rd_unit']) ? $data_3rd[$key]['rd_unit'] : "",
                              "[3] UnitCost" => isset($data_3rd[$key]['rd_unitCost']) ? $data_3rd[$key]['rd_unitCost'] : "",
                              "[3] TotalCost" => isset($data_3rd[$key]['rd_totalCost']) ? $data_3rd[$key]['rd_totalCost'] : "",
                              "[3] Category" => isset($data_3rd[$key]['rd_category']) ? $data_3rd[$key]['rd_category'] : "",
                              "[3] DateApplication" => isset($data_3rd[$key]['rd_dateApplication']) ? $data_3rd[$key]['rd_dateApplication'] : "",
                         ));

                    }
                    }
                   

                   


          } //foreach farmer data    
          
          



         // dd($excel_data);
         return $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
         /*  return Excel::create("Palaysikatan_Info_sheet"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
           $excel->sheet("Sheet 1", function($sheet) use ($excel_data) {
               $sheet->fromArray($excel_data);
               $sheet->getStyle('J:EL')->getAlignment()->setHorizontal('center');
               
           });        
       })->download('xlsx');  */
          
}



   
   public function exportExcel($station){
     if($station == "undefined"){
          $station =   Auth::user()->stationId;
     }
    
     $npk = $this->exportExcel_npk($station);
    $this->reconnect_cip();          
    if($station == "0")$station = "%";
  
    $crop_establishment_arr = array(
         "manual_transplanting" => "Manual Transplanting",
         "mechanized_transplanting" => "Mechanical Transplanting",
         "drum_seeding" => "Manual Direct-Seeding",
         "drum_seeding_sp" => "Mechanical Direct-Seeding"
    );

     $farmer_info = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info as f")
         ->join($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production as c", "c.farmer_id_fk", "=", "f.fid")
         ->join($GLOBALS['season_prefix']."rcep_palaysikatan.station_entries as s", "s.ref_id", "=", "f.fid")
         ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv as r", "f.add_region", "=", "r.regionName")
         ->where("s.station", "like", $station)
         ->orderBy("r.region_sort")
         ->orderBy("c.farmer_id_fk")
         ->groupBy("c.cpid")
         ->groupBy("c.variety_planted")
         ->get();
    $excel_data = array();
  
         foreach ($farmer_info as $farmer_data) {
              $data = array();
              $land_prep = array();
              $station = DB::table("geotag_db2.tbl_station")->where("stationId", $farmer_data->station)->value("stationName");
              $region = $farmer_data->add_region;
              $region_no = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("regionName", $farmer_data->add_region)->value("regCode");
              $province = $farmer_data->add_province;
              $municipality = $farmer_data->add_municipality;
              $brgy = $farmer_data->barangay;
              $farmer_name = trim($farmer_data->f_full_name);
              $lastname = $farmer_data->f_lastName;
              $age = $farmer_data->age;
              $firstname = $farmer_data->f_firstName;
              $middlename = $farmer_data->f_middleName; 
              $org_type = $farmer_data->org_type;  
              $org_membership = $farmer_data->org_membership;    
              if($org_type=="" ||$org_type == null){
               $org_type="N/A";
              }
              
              if($age =="" ||$age == null){
               $age="N/A";
          }

              if($org_membership =="" ||$org_membership == null){
                    $org_membership="N/A";
               }


                                       
              $extname = $farmer_data->f_extName;
              $variety = $farmer_data->variety_planted;
              $contact_no = $farmer_data->contact_no;
              $crop_establishment = $crop_establishment_arr[trim($farmer_data->crop_establishment)];
              $crop_establishment2 = $farmer_data->crop_establishment_sub;
              if(trim($farmer_data->crop_establishment) == "drum_seeding_sp" || trim($farmer_data->crop_establishment) == "mechanized_transplanting"){
                   if($farmer_data->crop_establishment_sub !=""){
                        $crop_establishment = $crop_establishment_arr[trim($farmer_data->crop_establishment)];
                   }
                   
              }
              $techno_area = $farmer_data->techno_area;
              
              $cpid = $farmer_data->cpid;

              $priceper_kg_dr = $farmer_data->dry_palay_price;
              $priceper_kg_fr = $farmer_data->fresh_palay_price;
              $cropCutTotal= ($farmer_data->sample_1 +$farmer_data->sample_2+$farmer_data->sample_3);
              $cropCutTotalYield =(($cropCutTotal/15)*10000)/1000;
              if($farmer_data->sold_as == "Fresh"){
                   $costper_kg = $farmer_data->fresh_palay_price;
              }else{
                    $costper_kg = $farmer_data->dry_palay_price;
              }
              //CEHECK THIS
            /*   if($farmer_data->area_harvested == 0){
                   $yield =0;
              }else{
                   $yield = number_format((($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags)/$farmer_data->area_harvested)/1000,2);
              } */
              $yieldData=0;
              $techno_area2=$techno_area;
              $farmer_data_area_harvested= $farmer_data->area_harvested;
              $adjustedrArea=$farmer_data->area_harvested;
              if($farmer_data_area_harvested==0){
                 //  dd($techno_area);
                   $farmer_data_area_harvested= 1;
                   $adjustedrArea=$techno_area;
              } 
              if($techno_area2>=0 && $farmer_data->area_harvested !=0){
                   //  dd($techno_area);
                   $techno_area2 =  $farmer_data_area_harvested;
                } 
              if($farmer_data->harvest_no_bags>0 && $farmer_data->harvest_weight_bags){
                   $yieldData= $farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags;
                   $yield = number_format(($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags*$techno_area2)/$farmer_data_area_harvested,2);

              }else{
                   $yield =0;
              }
               

              if($yield>0 && $techno_area2>0){
                   
                   $yield_ha =number_format(($farmer_data->harvest_no_bags*$farmer_data->harvest_weight_bags)/$farmer_data_area_harvested,2); 

              }else{
                   $yield_ha =0;
              }
              
              
                   //activities
              $manual_harversting=0;
              $combine_harvester=0;
              $methodHarvest="";
              $seedingRate=0;
              $seedingRate_unit ="";
              $activity_entries = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.data_entries")
                   ->where("ref_cp_id", $cpid)
                   ->where("farmer_id", $farmer_data->fid)
                   ->get();
                 //  dd($activity_entries);
                   foreach ($activity_entries as $entry){
                  
                        if($entry->planting_id ==116 || $entry->planting_id ==242 || $entry->planting_id ==364 || $entry->planting_id ==487){
                             if($entry->qty>0){
                                  $manual_harversting=1;
                             }
                        }
                        
                        if($entry->planting_id ==118  || $entry->planting_id ==244 || $entry->planting_id ==366 || $entry->planting_id ==489){
                             if($entry->qty>0){
                                  $combine_harvester=1;
                             }
                        }
                        if($combine_harvester==1 && $manual_harversting==1){
                             $methodHarvest="Manual Harvesting and Combine Harvester";
                        }else{
                             if($combine_harvester==1){
                                  $methodHarvest="Combine Harvester";
                             }
                             if($manual_harversting == 1){
                                  $methodHarvest="Manual Harvesting";
                             }
                             if($combine_harvester==0 && $manual_harversting==0){
                                  $methodHarvest="No Method";

                             }
                        }
                        if($entry->planting_id ==16 || $entry->planting_id ==143 || $entry->planting_id ==265 || $entry->planting_id ==387){
                             $seedingRate = $entry->qty;                            
                              $seedingRate_unit=$entry->unit;                                                         
                        }


                        if($farmer_data->crop_establishment=="manual_transplanting"){
                             if($entry->planting_id == 250){
                                  if(isset($data[$cpid]["land_prep_labor"])){
                                       $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;
                                          if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
                                        $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                  }else{
                                       $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                        $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
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
                             /* manuel */
                             // Number of Bags (14-14-14-)
                             if($entry->planting_id == 297 || $entry->planting_id == 304 || $entry->planting_id == 312){
                                  if(isset($data[$cpid]["bagsnot14"])){
                                       $data[$cpid]["bagsnot14"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnot14"] = $entry->qty;
                                  }                                  
                             }

                             // Number of Bags (16-20-0)
                             if($entry->planting_id == 298 || $entry->planting_id == 305 || $entry->planting_id == 313){
                                  if(isset($data[$cpid]["bagsnoammophos"])){
                                       $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                  }                                  
                             }
                             
                              // Number of Bags (21-0-0)
                              if($entry->planting_id == 299 || $entry->planting_id == 306 || $entry->planting_id == 314){
                                  if(isset($data[$cpid]["bagsnosul"])){
                                       $data[$cpid]["bagsnosul"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnosul"] = $entry->qty;
                                  }                                  
                             }

                              // Number of Bags (0-0-60)
                              if($entry->planting_id == 300 || $entry->planting_id == 307 || $entry->planting_id == 315){
                                  if(isset($data[$cpid]["bagsnomop"])){
                                       $data[$cpid]["bagsnomop"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnomop"] = $entry->qty;
                                  }                                  
                             }

                               // Number of Bags (46-0-0)
                               if($entry->planting_id == 301 || $entry->planting_id == 308 || $entry->planting_id == 316){
                                  if(isset($data[$cpid]["bagsnourea"])){
                                       $data[$cpid]["bagsnourea"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnourea"] = $entry->qty;
                                  }                                  
                             }

                             // Number of bags (17-0-17)
                             if($entry->planting_id == 438){
                                  if(isset($data[$cpid]["bagsno"])){
                                       $data[$cpid]["bagsno"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsno"] = $entry->qty;
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

                        } //end for manual transplanting /* joe */
                        elseif($farmer_data->crop_establishment=="mechanized_transplanting"){
                          
                             if($entry->planting_id == 372){
                                  if(isset($data[$cpid]["land_prep_labor"])){
                                       $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;
                                        if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
                                       $data[$cpid]["land_prep_labor_unit"] += $entry->unit;
                                        $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                  }else{
                                       $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                        $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
                                  }                                  
                             }

                            // dd($land_prep);
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
                                  if(isset($data[$cpid]["land_prep_rental"])){
                                       $data[$cpid]["land_prep_rental"] += $entry->qty*$entry->unit_cost;
                                        $land_prep[$cpid]["land_prep_rental_ha"] += $entry->unit_cost;
                                  }else{
                                       $data[$cpid]["land_prep_rental"] = $entry->qty*$entry->unit_cost;
                                        $land_prep[$cpid]["land_prep_rental_ha"] = $entry->unit_cost;
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

                             // Number of Bags (14-14-14-)
                             if($entry->planting_id == 419 || $entry->planting_id == 426 || $entry->planting_id == 434){
                                  if(isset($data[$cpid]["bagsnot14"])){
                                       $data[$cpid]["bagsnot14"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnot14"] = $entry->qty;
                                  }                                  
                             }

                             // Number of Bags (16-20-0)
                             if($entry->planting_id == 420 || $entry->planting_id == 427 || $entry->planting_id == 435){
                                  if(isset($data[$cpid]["bagsnoammophos"])){
                                       $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                  }                                  
                             }

                              // Number of Bags (21-0-0)
                              if($entry->planting_id == 421 || $entry->planting_id == 428 || $entry->planting_id == 436){
                                  if(isset($data[$cpid]["bagsnosul"])){
                                       $data[$cpid]["bagsnosul"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnosul"] = $entry->qty;
                                  }                                  
                             }

                              // Number of Bags (0-0-60)
                              if($entry->planting_id == 422 || $entry->planting_id == 429 || $entry->planting_id == 437){
                                  if(isset($data[$cpid]["bagsnomop"])){
                                       $data[$cpid]["bagsnomop"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnomop"] = $entry->qty;
                                  }                                  
                             }

                               // Number of Bags (46-0-0)
                               if($entry->planting_id == 423 || $entry->planting_id == 430){
                                  if(isset($data[$cpid]["bagsnourea"])){
                                       $data[$cpid]["bagsnourea"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnourea"] = $entry->qty;
                                  }                                  
                             }

                             // Number of bags (17-0-17)
                             if($entry->planting_id == 438){
                                  if(isset($data[$cpid]["bagsno"])){
                                       $data[$cpid]["bagsno"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsno"] = $entry->qty;
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
                        else if (trim($farmer_data->crop_establishment)=="drum_seeding"){


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



                             // Number of Bags (14-14-14-)
                             if($entry->planting_id == 175 || $entry->planting_id == 182 || $entry->planting_id == 190){
                                  if(isset($data[$cpid]["bagsnot14"])){
                                       $data[$cpid]["bagsnot14"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnot14"] = $entry->qty;
                                  }                                  
                             }

                             // Number of Bags (16-20-0)
                             if($entry->planting_id == 176 || $entry->planting_id == 183 || $entry->planting_id == 191){
                                  if(isset($data[$cpid]["bagsnoammophos"])){
                                       $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                  }                                  
                             }
                             
                              // Number of Bags (21-0-0)
                              if($entry->planting_id == 177 || $entry->planting_id == 184 || $entry->planting_id == 192){
                                  if(isset($data[$cpid]["bagsnosul"])){
                                       $data[$cpid]["bagsnosul"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnosul"] = $entry->qty;
                                  }                                  
                             }

                              // Number of Bags (0-0-60)
                              if($entry->planting_id == 178 || $entry->planting_id == 185 || $entry->planting_id == 193){
                                  if(isset($data[$cpid]["bagsnomop"])){
                                       $data[$cpid]["bagsnomop"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnomop"] = $entry->qty;
                                  }                                  
                             }

                               // Number of Bags (46-0-0)
                               if($entry->planting_id == 179 || $entry->planting_id == 186 || $entry->planting_id == 194){
                                  if(isset($data[$cpid]["bagsnourea"])){
                                       $data[$cpid]["bagsnourea"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnourea"] = $entry->qty;
                                  }                                  
                             }

                             // Number of bags (17-0-17)
                             if($entry->planting_id == 438){
                                  if(isset($data[$cpid]["bagsno"])){
                                       $data[$cpid]["bagsno"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsno"] = $entry->qty;
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
                        else if ($farmer_data->crop_establishment=="drum_seeding_sp"){

                             if($entry->planting_id == 1){
                                  if(isset($data[$cpid]["land_prep_labor"])){
                                       $data[$cpid]["land_prep_labor"] += $entry->qty*$entry->unit_cost;
                                        if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
                                        $land_prep[$cpid]["land_prep_labor_ha"] += $entry->unit_cost;
                                  }else{
                                       $data[$cpid]["land_prep_labor"] = $entry->qty*$entry->unit_cost;
                                        $land_prep[$cpid]["land_prep_labor_ha"] = $entry->unit_cost;
                                        if(isset($entry->unit)){
                                             $data[$cpid]["land_prep_labor_unit"] = $entry->unit; 
                                        }
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


                             // Number of Bags (14-14-14-)
                             if($entry->planting_id == 48 || $entry->planting_id == 55 || $entry->planting_id == 63){
                                  if(isset($data[$cpid]["bagsnot14"])){
                                       $data[$cpid]["bagsnot14"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnot14"] = $entry->qty;
                                  }                                  
                             }

                             // Number of Bags (16-20-0)
                             if($entry->planting_id == 49 || $entry->planting_id == 56 || $entry->planting_id == 64){
                                  if(isset($data[$cpid]["bagsnoammophos"])){
                                       $data[$cpid]["bagsnoammophos"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnoammophos"] = $entry->qty;
                                  }                                  
                             }
                             
                              // Number of Bags (21-0-0)
                              if($entry->planting_id == 50 || $entry->planting_id == 57 || $entry->planting_id == 65){
                                  if(isset($data[$cpid]["bagsnosul"])){
                                       $data[$cpid]["bagsnosul"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnosul"] = $entry->qty;
                                  }                                  
                             }

                              // Number of Bags (0-0-60)
                              if($entry->planting_id == 51 || $entry->planting_id == 58 || $entry->planting_id == 66){
                                  if(isset($data[$cpid]["bagsnomop"])){
                                       $data[$cpid]["bagsnomop"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnomop"] = $entry->qty;
                                  }                                  
                             }

                               // Number of Bags (46-0-0)
                               if($entry->planting_id == 52 || $entry->planting_id == 59 || $entry->planting_id == 497){
                                  if(isset($data[$cpid]["bagsnourea"])){
                                       $data[$cpid]["bagsnourea"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsnourea"] = $entry->qty;
                                  }                                  
                             }

                             // Number of bags (17-0-17)
                             if($entry->planting_id == 67){
                                  if(isset($data[$cpid]["bagsno"])){
                                       $data[$cpid]["bagsno"] += $entry->qty;
                                  }else{
                                       $data[$cpid]["bagsno"] = $entry->qty;
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
                   } //foreach data entries

                   //MATERIAL
                   /* $material_entry = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.material_entries")
                   ->where("ref_cp_id", $cpid)
                   ->where("farmer_id", $farmer_data->fid)
                   ->get();
                   foreach ($material_entry as $matt_entry) {
                        if($matt_entry->material_id == 6 ){
                             if(isset($data[$cpid]["bagsnot14"])){
                                  $data[$cpid]["bagsnot14"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["bagsnot14"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 3 ){
                             if(isset($data[$cpid]["bagsnourea"])){
                                  $data[$cpid]["bagsnourea"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["bagsnourea"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 5 ){
                             if(isset($data[$cpid]["bagsnoammophos"])){
                                  $data[$cpid]["bagsnoammophos"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["bagsnoammophos"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 4 ){
                             if(isset($data[$cpid]["bagsnosul"])){
                                  $data[$cpid]["bagsnosul"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["bagsnosul"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }

                        if($matt_entry->material_id == 8 ){
                             if(isset($data[$cpid]["bagsno"])){
                                  $data[$cpid]["bagsno"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["bagsno"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 7 ){
                             if(isset($data[$cpid]["bagsnomop"])){
                                  $data[$cpid]["bagsnomop"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["bagsnomop"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 9  ){
                             if(isset($data[$cpid]["n_npk"])){
                                  $data[$cpid]["n_npk"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["n_npk"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 9 ){
                             if(isset($data[$cpid]["n_npk_perha"])){
                                  $data[$cpid]["n_npk_perha"] += ($matt_entry->qty_sa+$matt_entry->qty_fa)/$matt_entry->price;
                             }else{
                                  $data[$cpid]["n_npk_perha"] = ($matt_entry->qty_sa+$matt_entry->qty_fa)/$matt_entry->price;
                             }                                   
                        }

                        if($matt_entry->material_id == 10 ){
                             if(isset($data[$cpid]["p_npk"])){
                                  $data[$cpid]["p_npk"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["p_npk"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 10){
                             if(isset($data[$cpid]["p_npk_perha"])){
                                  $data[$cpid]["p_npk_perha"] += ($matt_entry->qty_sa+$matt_entry->qty_fa)/$matt_entry->price;
                             }else{
                                  $data[$cpid]["p_npk_perha"] = ($matt_entry->qty_sa+$matt_entry->qty_fa)/$matt_entry->price;
                             }                                   
                        }

                         if($matt_entry->material_id == 11 ){
                             if(isset($data[$cpid]["k_npk"])){
                                  $data[$cpid]["k_npk"] += $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }else{
                                  $data[$cpid]["k_npk"] = $matt_entry->qty_sa+$matt_entry->qty_fa;
                             }                                   
                        }
                        if($matt_entry->material_id == 11){
                             if(isset($data[$cpid]["k_npk_perha"])){
                                  $data[$cpid]["k_npk_perha"] += ($matt_entry->qty_sa+$matt_entry->qty_fa)/$matt_entry->price;
                             }else{
                                  $data[$cpid]["k_npk_perha"] = ($matt_entry->qty_sa+$matt_entry->qty_fa)/$matt_entry->price;
                             }                                   
                        }


                   } */


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
                   

              foreach($data as $key_id => $value){

                   foreach ($value as $key => $value_data) {

                         if($key == "land_prep_labor" || $key == "seed_bed_labor" || $key == "seed_tray_labor" || $key =="seed_mgt_labor" || $key == "direct_seed_labor" || $key == "trans_laborpull" || $key == "mech_labor" || $key=="fert_mgt_labor"  || $key =="pest_mgt_labor" || $key=="weed_mgt_labor" || $key =="harvest_labor" || $key == "oth_labor" || $key == "trans_replant" || $key == "mech_replant" || $key=="weed_mgt_appli"){
                             $total_labor_cost += $value_data;
                             $grand += $value_data;
                                 
                        }
                        
                        if($key == "land_prep_labor_ha" || $key == "seed_bed_labor_ha" || $key == "seed_tray_labor_ha" || $key =="seed_mgt_labor_ha" || $key == "direct_seed_labor_ha" || $key == "trans_laborpull_ha" || $key == "mech_labor_ha" || $key=="fert_mgt_labor_ha" || $key =="pest_mgt_labor_ha" || $key=="weed_mgt_labor_ha" || $key =="harvest_labor_ha" || $key == "oth_labor_ha" || $key == "trans_replant_ha" || $key == "mech_replant_ha" || $key =="weed_mgt_appli_ha"){
                             $total_labor_cost_ha += $value_data;
                             $grand_ha += $value_data;
                        }

                        if($key == "seed_tray_mat" || $key == "seed_mgt_mat" || $key == "trans_mat" || $key =="harvest_matt" || $key == "oth_matt" ){
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

                        if($key == "oth_land" || $key == "oth_interest"){
                             $total_cost += $value_data;
                             $grand += $value_data;
                        }
                        if($key == "oth_land_ha" || $key == "oth_interest_ha"){
                             $total_cost_ha += $value_data;
                             $grand_ha += $value_data;
                        }
                   }
                 

                   $data[$key_id]["totlabexp_totcost"] = $total_labor_cost;
                   $data[$key_id]["totlabexp_totcost_perha"] = $total_labor_cost_ha;
                   $data[$key_id]["totmatexp_totcost"] = $total_mat_cost;
                   $data[$key_id]["totmatexp_totcost_perha"] = $total_mat_cost_ha;
                   $data[$key_id]["totmachrent_totcost"] = $total_machine;
                   $data[$key_id]["totalmachrent_totcost_perha"] = $total_machine_ha;
                   $data[$key_id]["total_fert"] = $total_fert;
                   $data[$key_id]["total_fert_ha"] = $total_fert_ha; 
                   $data[$key_id]["total_chemical"] = $total_chemical;
                   $data[$key_id]["total_chemical_ha"] = $total_chemical_ha;  
                   $data[$key_id]["total_meals"] = $total_meals;
                   $data[$key_id]["total_meals_ha"] = $total_meals_ha;
                   $data[$key_id]["totirrigexp_totcost"] = $total_irr;
                   $data[$key_id]["totirrigexp_totcost_perha"] = $total_irr_ha;
                   $data[$key_id]["tototherexp_totcost"] = $total_cost;
                   $data[$key_id]["tototherexp_totcost_totcost"] = $total_cost_ha;
                   $data[$key_id]["totprod_totcost"] = $grand;
                   $data[$key_id]["totprod_totcost_perha"] = $grand_ha;

              } 
              //dd($data[$cpid]["totlabexp_totcost"]);
              
               //dd($data[$cpid]["totlabexp_totcost"]);
               if($techno_area>0 && isset($data[$cpid]["land_prep_labor"])){
                   $data[$cpid]["land_prep_labor_ha"]=round($data[$cpid]["land_prep_labor"]/$techno_area,2);
              }
              if($techno_area>0 && isset($data[$cpid]["land_prep_rental"])){
                   $data[$cpid]["land_prep_rental_ha"]=round($data[$cpid]["land_prep_rental"]/$techno_area,2);
              }
              if($techno_area>0 && isset($data[$cpid]["land_prep_meals"])){
                   $data[$cpid]["land_prep_meals_ha"]=round($data[$cpid]["land_prep_meals"]/$techno_area,2);
              }
             /*  if($techno_area>0 && isset($data[$cpid]["seed_bed_rental"])){
                   $data[$cpid]["seed_bed_rental_ha"]=$data[$cpid]["seed_bed_rental"]/$techno_area;
              } */
              if($techno_area>0 && isset($data[$cpid]["seed_bed_labor"])){
                   $data[$cpid]["seed_bed_labor_ha"]=round($data[$cpid]["seed_bed_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_tray_labor"])){
                   $data[$cpid]["seed_tray_labor_ha"]=round($data[$cpid]["seed_tray_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_tray_fert"])){
                   $data[$cpid]["seed_tray_fert_ha"]=round($data[$cpid]["seed_tray_fert"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_tray_mat"])){
                   $data[$cpid]["seed_tray_mat_ha"]=round($data[$cpid]["seed_tray_mat"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_mgt_labor"])){
                   $data[$cpid]["seed_mgt_labor_ha"]=round($data[$cpid]["seed_mgt_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_mgt_mat"])){
                   $data[$cpid]["seed_mgt_mat_ha"]=round($data[$cpid]["seed_mgt_mat"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_mgt_fert"])){
                   $data[$cpid]["seed_mgt_fert_ha"]=round($data[$cpid]["seed_mgt_fert"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["seed_mgt_meals"])){
                   $data[$cpid]["seed_mgt_meals_ha"]=round($data[$cpid]["seed_mgt_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["direct_seed_labor"])){
                   $data[$cpid]["direct_seed_labor_ha"]=round($data[$cpid]["direct_seed_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["direct_seed_rental"])){
                   $data[$cpid]["direct_seed_rental_ha"]=round($data[$cpid]["direct_seed_rental"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["direct_seed_meal"])){
                   $data[$cpid]["direct_seed_meal_ha"]=round($data[$cpid]["direct_seed_meal"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["trans_laborpull"])){
                   $data[$cpid]["trans_laborpull_ha"]=round($data[$cpid]["trans_laborpull"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["trans_replant"])){
                   $data[$cpid]["trans_replant_ha"]=round($data[$cpid]["trans_replant"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["trans_mat"])){
                   $data[$cpid]["trans_mat_ha"]=round($data[$cpid]["trans_mat"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["trans_meal"])){
                   $data[$cpid]["trans_meal_ha"]=round($data[$cpid]["trans_meal"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["mech_labor"])){
                   $data[$cpid]["mech_labor_ha"]=round($data[$cpid]["mech_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["mech_rental"])){
                   $data[$cpid]["mech_rental_ha"]=round($data[$cpid]["mech_rental"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["mech_replant"])){
                   $data[$cpid]["mech_replant_ha"]=round($data[$cpid]["mech_replant"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["mech_meals"])){
                   $data[$cpid]["mech_meals_ha"]=round($data[$cpid]["mech_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["fert_mgt_cost"])){
                   $data[$cpid]["fert_mgt_cost_ha"]=round($data[$cpid]["fert_mgt_cost"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["fert_mgt_meals"])){
                   $data[$cpid]["fert_mgt_meals_ha"]=round($data[$cpid]["fert_mgt_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["fert_mgt_labor"])){
                   $data[$cpid]["fert_mgt_labor_ha"]=round($data[$cpid]["fert_mgt_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_irr"])){
                   $data[$cpid]["wtr_mgt_irr_ha"]=round($data[$cpid]["wtr_mgt_irr"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_fuel"])){
                   $data[$cpid]["wtr_mgt_fuel_ha"]=round($data[$cpid]["wtr_mgt_fuel"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_labor"])){
                   $data[$cpid]["wtr_mgt_labor_ha"]=round($data[$cpid]["wtr_mgt_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["wtr_mgt_meals"])){
                   $data[$cpid]["wtr_mgt_meals_ha"]=round($data[$cpid]["wtr_mgt_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["pest_mgt_mollus"])){
                   $data[$cpid]["pest_mgt_mollus_ha"]=round($data[$cpid]["pest_mgt_mollus"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["pest_mgt_insect"])){
                   $data[$cpid]["pest_mgt_insect_ha"]=round($data[$cpid]["pest_mgt_insect"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["pest_mgt_fungi"])){
                   $data[$cpid]["pest_mgt_fungi_ha"]=round($data[$cpid]["pest_mgt_fungi"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["pest_mgt_roden"])){
                   $data[$cpid]["pest_mgt_roden_ha"]=round($data[$cpid]["pest_mgt_roden"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["pest_mgt_labor"])){
                   $data[$cpid]["pest_mgt_labor_ha"]=round($data[$cpid]["pest_mgt_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["pest_mgt_meals"])){
                   $data[$cpid]["pest_mgt_meals_ha"]=round($data[$cpid]["pest_mgt_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["weed_mgt_labor"])){
                   $data[$cpid]["weed_mgt_labor_ha"]=round($data[$cpid]["weed_mgt_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["weed_mgt_herbicide"])){
                   $data[$cpid]["weed_mgt_herbicide_ha"]=round($data[$cpid]["weed_mgt_herbicide"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["weed_mgt_appli"])){
                   $data[$cpid]["weed_mgt_appli_ha"]=round($data[$cpid]["weed_mgt_appli"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["weed_mgt_meals"])){
                   $data[$cpid]["weed_mgt_meals_ha"]=round($data[$cpid]["weed_mgt_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["harvest_labor"])){
                   $data[$cpid]["harvest_labor_ha"]=round($data[$cpid]["harvest_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["harvest_tresher"])){
                   $data[$cpid]["harvest_tresher_ha"]=round($data[$cpid]["harvest_tresher"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["harvest_harvester"])){
                   $data[$cpid]["harvest_harvester_ha"]=round($data[$cpid]["harvest_harvester"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["harvest_hauling"])){
                   $data[$cpid]["harvest_hauling_ha"]=round($data[$cpid]["harvest_hauling"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["harvest_matt"])){
                   $data[$cpid]["harvest_matt_ha"]=round($data[$cpid]["harvest_matt"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["harvest_meals"])){
                   $data[$cpid]["harvest_meals_ha"]=round($data[$cpid]["harvest_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["oth_labor"])){
                   $data[$cpid]["oth_labor_ha"]=round($data[$cpid]["oth_labor"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["oth_matt"])){
                   $data[$cpid]["oth_matt_ha"]=round($data[$cpid]["oth_matt"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["oth_land"])){
                   $data[$cpid]["oth_land_ha"]=round($data[$cpid]["oth_land"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["oth_interest"])){
                   $data[$cpid]["oth_interest_ha"]=round($data[$cpid]["oth_interest"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["totlabexp_totcost"])){
                   $data[$cpid]["totlabexp_totcost_perha"]=round($data[$cpid]["totlabexp_totcost"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["totmatexp_totcost"])){
                   $data[$cpid]["totmatexp_totcost_perha"]=round($data[$cpid]["totmatexp_totcost"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["totmachrent_totcost"])){
                   $data[$cpid]["totalmachrent_totcost_perha"]=round($data[$cpid]["totmachrent_totcost"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["totmachrent_totcost"])){
                   $data[$cpid]["totalmachrent_totcost_perha"]=round($data[$cpid]["totmachrent_totcost"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["total_fert"])){
                   $data[$cpid]["total_fert_ha"]=round($data[$cpid]["total_fert"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["total_chemical"])){
                   $data[$cpid]["total_chemical_ha"]=round($data[$cpid]["total_chemical"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["total_meals"])){
                   $data[$cpid]["total_meals_ha"]=round($data[$cpid]["total_meals"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["totirrigexp_totcost"])){
                   $data[$cpid]["totirrigexp_totcost_perha"]=round($data[$cpid]["totirrigexp_totcost"]/$techno_area,2);
              }if($techno_area>0 && isset($data[$cpid]["totprod_totcost"])){
                   $data[$cpid]["totprod_totcost_perha"]=round($data[$cpid]["totprod_totcost"]/$techno_area,2);
              }
              if($techno_area>0 && isset($data[$cpid]["fert_mgt_meals"])){
                   $data[$cpid]["fert_mgt_meals_ha"]=round($data[$cpid]["fert_mgt_meals"]/$techno_area,2);
              }
              if($techno_area>0 && isset($data[$cpid]["fert_mgt_labor"])){
                   $data[$cpid]["fert_mgt_labor_ha"]=round($data[$cpid]["fert_mgt_labor"]/$techno_area,2);
              } if($techno_area>0 && isset($data[$cpid]["tototherexp_totcost"])){
                   $data[$cpid]["tototherexp_totcost_totcost"]=round($data[$cpid]["tototherexp_totcost"]/$techno_area,2);
              }
              

              if(isset($data[$cpid]["bagsnot14"]) || isset($data[$cpid]["bagsnourea"]) || isset($data[$cpid]["bagsnoammophos"]) || isset($data[$cpid]["bagsnosul"]) || isset($data[$cpid]["bagsno"]) || isset($data[$cpid]["bagsnomop"])){
                   if(!isset($data[$cpid]["bagsnot14"]) ){
                        $data[$cpid]["bagsnot14"]=0;
                   }
                   if(!isset($data[$cpid]["bagsnourea"])){
                        $data[$cpid]["bagsnourea"]=0;
                   }
                   if(!isset($data[$cpid]["bagsnoammophos"])){
                        $data[$cpid]["bagsnoammophos"]=0;
                   }
                   if(!isset($data[$cpid]["bagsnosul"])){
                        $data[$cpid]["bagsnosul"]=0;
                   }
                   if(!isset($data[$cpid]["bagsno"])){
                        $data[$cpid]["bagsno"]=0;
                   }
                   if(!isset($data[$cpid]["bagsnomop"])){
                        $data[$cpid]["bagsnomop"]=0;
                   }


                   $data[$cpid]["n_npk"] =(($data[$cpid]["bagsnot14"]*14)/2)+(($data[$cpid]["bagsnourea"]*46)/2)+(($data[$cpid]["bagsnoammophos"]*16)/2)+(($data[$cpid]["bagsnosul"]*21)/2)+(($data[$cpid]["bagsno"]*17)/2);
                   if(isset($data[$cpid]["n_npk"])){
                        if($data[$cpid]["n_npk"]>0 && $techno_area){
                             $data[$cpid]["n_npk_perha"] = number_format($data[$cpid]["n_npk"]/$techno_area,2);
                        }
                        
                   }
                   
                   

                   $data[$cpid]["p_npk"] =(($data[$cpid]["bagsnot14"]*14)/2)+(($data[$cpid]["bagsnoammophos"]*20)/2);
                   if(isset($data[$cpid]["p_npk"])){

                        if($data[$cpid]["p_npk"]>0 && $techno_area){
                             $data[$cpid]["p_npk_perha"] = number_format($data[$cpid]["p_npk"]/$techno_area,2);
                        }

                        
                   }

                   $data[$cpid]["k_npk"] =(($data[$cpid]["bagsnot14"]*14)/2)+(($data[$cpid]["bagsno"]*17)/2)+(($data[$cpid]["bagsnomop"]*60)/2);
                   if(isset($data[$cpid]["k_npk"])){
                        
                        if($data[$cpid]["k_npk"]>0 && $techno_area){
                             $data[$cpid]["k_npk_perha"] = number_format($data[$cpid]["k_npk"]/$techno_area,2);
                        }
                   }

              }

              if( isset($data[$cpid]["totprod_totcost"]) && $yieldData>0){
                   $costper_kg= (floatval($data[$cpid]["totprod_totcost"])/floatval($yieldData));
                  $costper_kg = number_format( $costper_kg,2);
              
         }else{
              $costper_kg=0;
         }
      
              array_push($excel_data, array(
                             "prstation" => $station,
                             "reg" => $region,
                             "reg_no" => $region_no,
                             "prov" => $province,
                             "mun" => $municipality,
                             "brgy" => $brgy,
                             //"fp_name" => $farmer_name,
                             "last_name" => $lastname,
                             "first_name" => $firstname,
                             "middle_name" => $middlename,
                             "ext_name" => $extname,
                             "age" => $age,
                             "contact_no" => $contact_no,
                             "varplant" => $variety,
                             "metcropest" => $crop_establishment,
                             "metcropest2" => $crop_establishment2,
                             "methodHarvest" => $methodHarvest, 
                             
                             "oraganization_type" => $org_type , 
                             "Oraganization_name" => $org_membership , 

                             "tdarea" => $techno_area,
                             "Adjarea" => $adjustedrArea,                                   
                             "dikerepair_landprep" => isset($data[$cpid]["land_prep_labor"]) ? number_format($data[$cpid]["land_prep_labor"],2) : "",
                             "dikerepair_landprep_unit" => isset($data[$cpid]["land_prep_labor_unit"]) ? $data[$cpid]["land_prep_labor_unit"] : "",
                             
                             "dikerepair_landprep_perha" => isset($data[$cpid]["land_prep_labor_ha"]) ? number_format($data[$cpid]["land_prep_labor_ha"],2) : "",
                             "renttractor_landprep" => isset($data[$cpid]["land_prep_rental"]) ? number_format($data[$cpid]["land_prep_rental"],2) : "",
                             "renttractor_landprep_perha" => isset($data[$cpid]["land_prep_rental_ha"]) ? number_format($data[$cpid]["land_prep_rental_ha"],2) : "",
                             "meals_landprep" => isset($data[$cpid]["land_prep_meals"]) ? number_format($data[$cpid]["land_prep_meals"],2) : "",
                             "meals_landprep_perha" => isset($data[$cpid]["land_prep_meals_ha"]) ? number_format($data[$cpid]["land_prep_meals_ha"],2) : "",
                             "seedbedprep_landprep" => isset($data[$cpid]["seed_bed_rental"]) ? number_format($data[$cpid]["seed_bed_rental"],2) : "",
                             "seedbedprep_landprep_perha" => isset($data[$cpid]["seed_bed_rental_ha"]) ? number_format($data[$cpid]["seed_bed_rental_ha"],2) : "",
                             "seedbedprep_landprep" => isset($data[$cpid]["seed_bed_labor"]) ? number_format($data[$cpid]["seed_bed_labor"],2) : "",
                             "seedbedprep_landprep_perha" => isset($data[$cpid]["seed_bed_labor_ha"]) ? number_format($data[$cpid]["seed_bed_labor_ha"],2) : "",
                             "seedsowhaul_seedtrayprep" => isset($data[$cpid]["seed_tray_labor"]) ? number_format($data[$cpid]["seed_tray_labor"],2) : "",
                             "seedsowing_seedtrayprep_perha" => isset($data[$cpid]["seed_tray_labor_ha"]) ? number_format($data[$cpid]["seed_tray_labor_ha"],2) : "",
                             "fert_seedtrayprep" => isset($data[$cpid]["seed_tray_fert"]) ? number_format($data[$cpid]["seed_tray_fert"],2) : "",
                             "fert_seedtrayprep_perha" => isset($data[$cpid]["seed_tray_fert_ha"]) ? number_format($data[$cpid]["seed_tray_fert_ha"],2) : "",
                             "mat_seedtrayprep" => isset($data[$cpid]["seed_tray_mat"]) ? number_format($data[$cpid]["seed_tray_mat"],2) : "",
                             "mat_seedtrayprep_perha" => isset($data[$cpid]["seed_tray_mat_ha"]) ? number_format($data[$cpid]["seed_tray_mat_ha"],2) : "",
                             "seedsowfert_seedmngt" => isset($data[$cpid]["seed_mgt_labor"]) ? number_format($data[$cpid]["seed_mgt_labor"],2) : "",
                             "seedsowfert_seedmngt_perha" => isset($data[$cpid]["seed_mgt_labor_ha"]) ? number_format($data[$cpid]["seed_mgt_labor_ha"],2) : "",
                             "mat_seedmngt" => isset($data[$cpid]["seed_mgt_mat"]) ? number_format($data[$cpid]["seed_mgt_mat"],2) : "",
                             "mat_seedmngt_perha" => isset($data[$cpid]["seed_mgt_mat_ha"]) ? number_format($data[$cpid]["seed_mgt_mat_ha"],2) : "",
                             "seedingRate" => $seedingRate,
                             "seedingRate_unit" => $seedingRate_unit,
                             "fert_seedmngt" => isset($data[$cpid]["seed_mgt_fert"]) ? number_format($data[$cpid]["seed_mgt_fert"],2) : "",
                             "fert_seedmngt_perha" => isset($data[$cpid]["seed_mgt_fert_ha"]) ? number_format($data[$cpid]["seed_mgt_fert_ha"],2) : "",
                             "meals_seedmngt" => isset($data[$cpid]["seed_mgt_meals"]) ? number_format($data[$cpid]["seed_mgt_meals"],2) : "",
                             "meals_seedmngt_perha" => isset($data[$cpid]["seed_mgt_meals_ha"]) ? number_format($data[$cpid]["seed_mgt_meals_ha"],2) : "",
                             "labor_dsr_cropest" => isset($data[$cpid]["direct_seed_labor"]) ? number_format($data[$cpid]["direct_seed_labor"],2) : "",
                             "labor_dsr_cropest_perha" => isset($data[$cpid]["direct_seed_labor_ha"]) ? number_format($data[$cpid]["direct_seed_labor_ha"],2) : "",
                             "rentseeder_dsr_cropest" => isset($data[$cpid]["direct_seed_rental"]) ? number_format($data[$cpid]["direct_seed_rental"],2) : "",
                             "rentseeder_dsr_cropest_perha" => isset($data[$cpid]["direct_seed_rental_ha"]) ? number_format($data[$cpid]["direct_seed_rental_ha"],2) : "",
                             "meals_dsr_cropest" => isset($data[$cpid]["direct_seed_meal"]) ? number_format($data[$cpid]["direct_seed_meal"],2) : "",
                             "meals_dsr_cropest_perha" => isset($data[$cpid]["direct_seed_meal_ha"]) ? number_format($data[$cpid]["direct_seed_meal_ha"],2) : "",
                             "pulltrans_tpr_cropest" => isset($data[$cpid]["trans_laborpull"]) ? number_format($data[$cpid]["trans_laborpull"],2) : "",
                             "pulltrans_tpr_cropest_perha" => isset($data[$cpid]["trans_laborpull_ha"]) ? number_format($data[$cpid]["trans_laborpull_ha"],2) : "",
                             "replant_tpr_cropest" => isset($data[$cpid]["trans_replant"]) ? number_format($data[$cpid]["trans_replant"],2) : "",
                             "replant_tpr_cropest_perha" => isset($data[$cpid]["trans_replant_ha"]) ? number_format($data[$cpid]["trans_replant_ha"],2) : "",
                             "mat_tpr_cropest" => isset($data[$cpid]["trans_mat"]) ? number_format($data[$cpid]["trans_mat"],2) : "",
                             "mat_tpr_cropest_perha" => isset($data[$cpid]["trans_mat_ha"]) ? number_format($data[$cpid]["trans_mat_ha"],2) : "",
                             "meals_tpr_cropest" => isset($data[$cpid]["trans_meal"]) ? number_format($data[$cpid]["trans_meal"],2) : "",
                             "meals_tpr_cropest_perha" => isset($data[$cpid]["trans_meal_ha"]) ? number_format($data[$cpid]["trans_meal_ha"],2) : "",
                             "seedhaul_mechtpr_cropest" => isset($data[$cpid]["mech_labor"]) ? number_format($data[$cpid]["mech_labor"],2) : "",
                             "seedhaul_mechtpr_cropest_perha" => isset($data[$cpid]["mech_labor_ha"]) ? number_format($data[$cpid]["mech_labor_ha"],2) : "",
                             "mechtrans_mechtpr_cropest" => isset($data[$cpid]["mech_rental"]) ? number_format($data[$cpid]["mech_rental"],2) : "",
                             "mechtrans_mechtpr_cropest_perha" => isset($data[$cpid]["mech_rental_ha"]) ? number_format($data[$cpid]["mech_rental_ha"],2) : "",
                             "replant_mechtpr_cropest" => isset($data[$cpid]["mech_replant"]) ? number_format($data[$cpid]["mech_replant"],2) : "",
                             "replant_mechtpr_cropest_perha" => isset($data[$cpid]["mech_replant_ha"]) ? number_format($data[$cpid]["mech_replant_ha"],2) : "",
                             "meals_mechtpr_cropest" => isset($data[$cpid]["mech_meals"]) ? number_format($data[$cpid]["mech_meals"],2) : "",
                             "meals_mechtpr_cropest_perha" => isset($data[$cpid]["mech_meals_ha"]) ? number_format($data[$cpid]["mech_meals_ha"],2) : "",
                             "fert_fertmngt" => isset($data[$cpid]["fert_mgt_cost"]) ? number_format($data[$cpid]["fert_mgt_cost"],2) : "",
                             "fert_fertmngt_perha" => isset($data[$cpid]["fert_mgt_cost_ha"]) ? number_format($data[$cpid]["fert_mgt_cost_ha"],2) : "",
                             "meals_fertmngt" => isset($data[$cpid]["fert_mgt_meals"]) ? number_format($data[$cpid]["fert_mgt_meals"],2) : "",
                             "meals_fertmngt_perha" => isset($data[$cpid]["fert_mgt_meals_ha"]) ? number_format($data[$cpid]["fert_mgt_meals_ha"],2) : "",
                             "labor_fertmngt" => isset($data[$cpid]["fert_mgt_labor"]) ? number_format($data[$cpid]["fert_mgt_labor"],2) : "",
                             "labor_fertmngt_perha" => isset($data[$cpid]["fert_mgt_labor_ha"]) ? number_format($data[$cpid]["fert_mgt_labor_ha"],2) : "",
                             "irrig_watermngt" => isset($data[$cpid]["wtr_mgt_irr"]) ? number_format($data[$cpid]["wtr_mgt_irr"],2) : "",
                             "irrig_watermngt_perha" => isset($data[$cpid]["wtr_mgt_irr_ha"]) ? number_format($data[$cpid]["wtr_mgt_irr_ha"],2) : "",
                             "fuel_watermngt" => isset($data[$cpid]["wtr_mgt_fuel"]) ? number_format($data[$cpid]["wtr_mgt_fuel"],2) : "",
                             "fuel_watermngt_perha" => isset($data[$cpid]["wtr_mgt_fuel_ha"]) ? number_format($data[$cpid]["wtr_mgt_fuel_ha"],2) : "",
                             "meals_watermngt" => isset($data[$cpid]["wtr_mgt_labor"]) ? number_format($data[$cpid]["wtr_mgt_labor"],2) : "",
                             "meals_watermngt_perha" => isset($data[$cpid]["wtr_mgt_labor_ha"]) ? number_format($data[$cpid]["wtr_mgt_labor_ha"],2) : "",
                             "labor_watermngt" => isset($data[$cpid]["wtr_mgt_meals"]) ? number_format($data[$cpid]["wtr_mgt_meals"],2) : "",
                             "labor_watermngt_perha" => isset($data[$cpid]["wtr_mgt_meals_ha"]) ? number_format($data[$cpid]["wtr_mgt_meals_ha"],2) : "",
                             "mollusc_pestmngt" => isset($data[$cpid]["pest_mgt_mollus"]) ? number_format($data[$cpid]["pest_mgt_mollus"],2) : "",
                             "mollusc_pestmngt_perha" => isset($data[$cpid]["pest_mgt_mollus_ha"]) ? number_format($data[$cpid]["pest_mgt_mollus_ha"],2) : "",
                             "insect_pestmngt" =>isset($data[$cpid]["pest_mgt_insect"]) ? number_format($data[$cpid]["pest_mgt_insect"],2) : "",
                             "insect_pestmngt_perha" => isset($data[$cpid]["pest_mgt_insect_ha"]) ? number_format($data[$cpid]["pest_mgt_insect_ha"],2) : "",
                             "fungi_pestmngt" => isset($data[$cpid]["pest_mgt_fungi"]) ? number_format($data[$cpid]["pest_mgt_fungi"],2) : "",
                             "fungi_pestmngt_perha" => isset($data[$cpid]["pest_mgt_fungi_ha"]) ? number_format($data[$cpid]["pest_mgt_fungi_ha"],2) : "",
                             "rodent_pestmngt" => isset($data[$cpid]["pest_mgt_roden"]) ? number_format($data[$cpid]["pest_mgt_roden"],2) : "",
                             "rodent_pestmngt_perha" => isset($data[$cpid]["pest_mgt_roden_ha"]) ? number_format($data[$cpid]["pest_mgt_roden_ha"],2) : "",
                             "labor_pestmngt" => isset($data[$cpid]["pest_mgt_labor"]) ? number_format($data[$cpid]["pest_mgt_labor"],2) : "",
                             "labor_pestmngt_perha" => isset($data[$cpid]["pest_mgt_labor_ha"]) ? number_format($data[$cpid]["pest_mgt_labor_ha"],2) : "",
                             "meals_pestmngt" => isset($data[$cpid]["pest_mgt_meals"]) ? number_format($data[$cpid]["pest_mgt_meals"],2) : "",
                             "meals_pestmngt_perha" => isset($data[$cpid]["pest_mgt_meals_ha"]) ? number_format($data[$cpid]["pest_mgt_meals_ha"],2) : "",
                             "manweed_weedmngt" => isset($data[$cpid]["weed_mgt_labor"]) ? number_format($data[$cpid]["weed_mgt_labor"],2) : "",
                             "manweed_weedmngt_mngt" => isset($data[$cpid]["weed_mgt_labor_ha"]) ? number_format($data[$cpid]["weed_mgt_labor_ha"],2) : "",
                             "herb_weedmngt" => isset($data[$cpid]["weed_mgt_herbicide"]) ? number_format($data[$cpid]["weed_mgt_herbicide"],2) : "",
                             "herb_weedmngt_perha" => isset($data[$cpid]["weed_mgt_herbicide_ha"]) ? number_format($data[$cpid]["weed_mgt_herbicide_ha"],2) : "",
                             "herbapp_weedmngt" => isset($data[$cpid]["weed_mgt_appli"]) ? number_format($data[$cpid]["weed_mgt_appli"],2) : "",
                             "herbapp_weedmngt_perha" => isset($data[$cpid]["weed_mgt_appli_ha"]) ? number_format($data[$cpid]["weed_mgt_appli_ha"],2) : "",
                             "meals_weedmngt" => isset($data[$cpid]["weed_mgt_meals"]) ? number_format($data[$cpid]["weed_mgt_meals"],2) : "",
                             "meals_weedmngt_perha" => isset($data[$cpid]["weed_mgt_meals_ha"]) ? number_format($data[$cpid]["weed_mgt_meals_ha"],2) : "",
                             "manharvest_harvestmngt" => isset($data[$cpid]["harvest_labor"]) ? number_format($data[$cpid]["harvest_labor"],2) : "",
                             "manharvest_harvestmngt_perha" => isset($data[$cpid]["harvest_labor_ha"]) ? number_format($data[$cpid]["harvest_labor_ha"],2) : "",
                             "rentthresh_harvestmngt" => isset($data[$cpid]["harvest_tresher"]) ? number_format($data[$cpid]["harvest_tresher"],2) : "",
                             "rentthresh_harvestmngt_perha" => isset($data[$cpid]["harvest_tresher_ha"]) ? number_format($data[$cpid]["harvest_tresher_ha"],2) : "",
                             "rentcombine_harvestmngt" => isset($data[$cpid]["harvest_harvester"]) ? number_format($data[$cpid]["harvest_harvester"],2) : "",
                             "rentcombine_harvestmngt_perha" => isset($data[$cpid]["harvest_harvester_ha"]) ? number_format($data[$cpid]["harvest_harvester_ha"],2) : "",
                             "laborhaul_harvestmngt" => isset($data[$cpid]["harvest_hauling"]) ? number_format($data[$cpid]["harvest_hauling"],2) : "",
                             "laborhaul_harvestmngt_perha" => isset($data[$cpid]["harvest_hauling_ha"]) ? number_format($data[$cpid]["harvest_hauling_ha"],2) : "",
                             "mat_harvestmngt" => isset($data[$cpid]["harvest_matt"]) ? number_format($data[$cpid]["harvest_matt"],2) : "",
                             "mat_harvestmngt_perha" => isset($data[$cpid]["harvest_matt_ha"]) ? number_format($data[$cpid]["harvest_matt_ha"],2) : "",
                             "meals_harvestmngt" => isset($data[$cpid]["harvest_meals"]) ? number_format($data[$cpid]["harvest_meals"],2) : "",
                             "meals_harvestmngt_perha" => isset($data[$cpid]["harvest_meals_ha"]) ? number_format($data[$cpid]["harvest_meals_ha"],2) : "",
                             "careshare_othercost" => isset($data[$cpid]["oth_labor"]) ? number_format($data[$cpid]["oth_labor"],2) : "",
                             "careshare_othercost_perha" => isset($data[$cpid]["oth_labor_ha"]) ? number_format($data[$cpid]["oth_labor_ha"],2) : "",
                             "othermatexp_othercost" => isset($data[$cpid]["oth_matt"]) ? number_format($data[$cpid]["oth_matt"],2) : "",
                             "othermatexp_othercost_perha" => isset($data[$cpid]["oth_matt_ha"]) ? number_format($data[$cpid]["oth_matt_ha"],2) : "",
                             "landrent_othercost" => isset($data[$cpid]["oth_land"]) ? number_format($data[$cpid]["oth_land"] ,2): "",
                             "landrent_othercost_perha" => isset($data[$cpid]["oth_land_ha"]) ? number_format($data[$cpid]["oth_land_ha"],2) : "",
                             "interest_othercost" => isset($data[$cpid]["oth_interest"]) ? number_format($data[$cpid]["oth_interest"] ,2): "",
                             "interest_othercost_perha" => isset($data[$cpid]["oth_interest_ha"]) ? number_format($data[$cpid]["oth_interest_ha"] ,2): "",
                             "totlabexp_totcost" => isset($data[$cpid]["totlabexp_totcost"]) ? number_format($data[$cpid]["totlabexp_totcost"] ,2): "",
                             "totlabexp_totcost_perha" => isset($data[$cpid]["totlabexp_totcost_perha"]) ? number_format($data[$cpid]["totlabexp_totcost_perha"],2): "",
                             "totmatexp_totcost" =>  isset($data[$cpid]["totmatexp_totcost"]) ? number_format($data[$cpid]["totmatexp_totcost"],2): "",
                             "totmatexp_totcost_perha" => isset($data[$cpid]["totmatexp_totcost_perha"]) ? number_format($data[$cpid]["totmatexp_totcost_perha"],2): "",
                             "totmachrent_totcost" => isset($data[$cpid]["totmachrent_totcost"]) ? number_format($data[$cpid]["totmachrent_totcost"],2): "",
                             "totalmachrent_totcost_perha" =>isset( $data[$cpid]["totalmachrent_totcost_perha"]) ? number_format($data[$cpid]["totalmachrent_totcost_perha"],2): "",
                             "totfertexp_totcost" => isset($data[$cpid]["total_fert"]) ? number_format($data[$cpid]["total_fert"],2): "",
                             "totfertexp_totcost_perha" => isset($data[$cpid]["total_fert_ha"]) ?number_format($data[$cpid]["total_fert_ha"] ,2): "",
                             "totchemexp_totcost" => isset($data[$cpid]["total_chemical"]) ?number_format($data[$cpid]["total_chemical"] ,2): "",
                             "totchemexp_totcost_perha" => isset($data[$cpid]["total_chemical_ha"]) ?number_format($data[$cpid]["total_chemical_ha"] ,2): "",
                             "totmealsexp_totcost" => isset($data[$cpid]["total_meals"]) ?number_format($data[$cpid]["total_meals"] ,2): "",
                             "totmealsexp_totcost_perha" => isset($data[$cpid]["total_meals_ha"]) ? number_format($data[$cpid]["total_meals_ha"],2): "",
                             "totirrigexp_totcost" => isset($data[$cpid]["totirrigexp_totcost"]) ?number_format($data[$cpid]["totirrigexp_totcost"] ,2): "",
                             "totirrigexp_totcost_perha" =>isset( $data[$cpid]["totirrigexp_totcost_perha"]) ?number_format($data[$cpid]["totirrigexp_totcost_perha"] ,2): "",
                             "tototherexp_totcost" => isset($data[$cpid]["tototherexp_totcost"]) ?number_format($data[$cpid]["tototherexp_totcost"] ,2): "",
                             "tototherexp_totcost_totcost" => isset($data[$cpid]["tototherexp_totcost_totcost"]) ?number_format($data[$cpid]["tototherexp_totcost_totcost"] ,2): "",
                             "totprod_totcost" => isset($data[$cpid]["totprod_totcost"]) ?number_format($data[$cpid]["totprod_totcost"] ,2): "",
                             "totprod_totcost_perha" => isset($data[$cpid]["totprod_totcost_perha"]) ?number_format($data[$cpid]["totprod_totcost_perha"] ,2): "",
                             "priceper_kg_dr" => $priceper_kg_dr,
                             "priceper_kg_fr" => $priceper_kg_fr,                                   
                             "yield" => $yield,
                             "yield_perha" =>$yield_ha,
                             "costper_kg" => $costper_kg,
                             "bagsnot14" => isset($data[$cpid]["bagsnot14"]) ?$data[$cpid]["bagsnot14"] : "",
                             "bagsnourea" => isset($data[$cpid]["bagsnourea"]) ?$data[$cpid]["bagsnourea"] : "",
                             "bagsnoammophos" => isset($data[$cpid]["bagsnoammophos"]) ?$data[$cpid]["bagsnoammophos"] : "",
                             "bagsnosul" => isset($data[$cpid]["bagsnosul"]) ?$data[$cpid]["bagsnosul"] : "",
                             "bagsno" => isset($data[$cpid]["bagsno"]) ?$data[$cpid]["bagsno"] : "",
                             "bagsnomop" => isset($data[$cpid]["bagsnomop"]) ?$data[$cpid]["bagsnomop"] : "",
                             "n_npk" => isset($data[$cpid]["n_npk"]) ?$data[$cpid]["n_npk"] : "",
                             "n_npk_perha" => isset($data[$cpid]["n_npk_perha"]) ?$data[$cpid]["n_npk_perha"] : "",
                             "p_npk" => isset($data[$cpid]["p_npk"]) ?$data[$cpid]["p_npk"] : "",
                             "p_npk_perha" => isset($data[$cpid]["p_npk_perha"]) ?$data[$cpid]["p_npk_perha"] : "",
                             "k_npk" => isset($data[$cpid]["k_npk"]) ?$data[$cpid]["k_npk"] : "",
                             "k_npk_perha" => isset($data[$cpid]["k_npk_perha"]) ?$data[$cpid]["k_npk_perha"] : "",
                             "crop_cut_ha" => isset($cropCutTotalYield) ? number_format($cropCutTotalYield,2) : "0",
                        ));



                   
                   


         } //foreach farmer data    
         
         
        
     
         $documentation = array();
         array_push($documentation, array("Headers" => "prstation", "Remarks" => "PhilRice Station"));
         array_push($documentation, array("Headers" => "reg", "Remarks" => "Region"));
         array_push($documentation, array("Headers" => "reg_no", "Remarks" => "Region"));
         array_push($documentation, array("Headers" => "prov", "Remarks" => "Province"));
         array_push($documentation, array("Headers" => "bgry", "Remarks" => "Barangay"));
         array_push($documentation, array("Headers" => "mun", "Remarks" => "Municipality"));
         array_push($documentation, array("Headers" => "fp_name", "Remarks" => "Farmer Partner Name"));
         array_push($documentation, array("Headers" => "varplant", "Remarks" => "Variety Planted"));
         array_push($documentation, array("Headers" => "metcropest", "Remarks" => "Method of Crop Establishment"));
         array_push($documentation, array("Headers" => "metcropest2", "Remarks" => "Sub Method of Crop Establishment"));
         array_push($documentation, array("Headers" => "methodHarvest", "Remarks" => "Method of Harvesting"));               
         array_push($documentation, array("Headers" => "tdarea", "Remarks" => "Techno Demo Area  per farm"));
         array_push($documentation, array("Headers" => "Adjarea", "Remarks" => "Techno Demo Adjusted Area"));
         array_push($documentation, array("Headers" => "dikerepair_landprep", "Remarks" => "Labor for Dike Cleaning and Repair, Etc "));
         array_push($documentation, array("Headers" => "dikerepair_landprep_perha", "Remarks" => "Labor for Dike Cleaning and Repair, Etc per Hectare "));
         array_push($documentation, array("Headers" => "renttractor_landprep", "Remarks" => "Rental of Tractor/Hand tractor for Plowing, Harrowing and Levelling"));
         array_push($documentation, array("Headers" => "renttractor_landprep_perha", "Remarks" => "Rental of Tractor/Hand tractor for Plowing, Harrowing and Levelling per Hectare"));
         array_push($documentation, array("Headers" => "meals_landprep", "Remarks" => "Meals and Snacks"));
         array_push($documentation, array("Headers" => "meals_landprep_perha", "Remarks" => "Meals and Snacks per Hectare"));
         array_push($documentation, array("Headers" => "seedbedprep_landprep", "Remarks" => "Rental of Hand Tractor Seedbed Land Preparation"));
         array_push($documentation, array("Headers" => "seedbedprep_landprep_perha", "Remarks" => "Rental of Hand Tractor Seedbed Land Preparation per Hectare"));
         array_push($documentation, array("Headers" => "seedbedprep_landprep", "Remarks" => "Labor for seedbed construction"));
         array_push($documentation, array("Headers" => "seedbedprep_landprep_perha", "Remarks" => "Labor for seedbed construction per Hectare"));
         array_push($documentation, array("Headers" => "seedsowhaul_seedtrayprep", "Remarks" => "Labor for Seed Sowing/Hauling"));
         array_push($documentation, array("Headers" => "seedsowing_seedtrayprep_perha", "Remarks" => "Labor for Seed Sowing/Hauling per Hectare"));
         array_push($documentation, array("Headers" => "fert_seedtrayprep", "Remarks" => "Fertilizer Costs (Organic Fertilizer)"));
         array_push($documentation, array("Headers" => "fert_seedtrayprep_perha", "Remarks" => "Fertilizer Costs (Organic Fertilizer) per Hectare"));
         array_push($documentation, array("Headers" => "mat_seedtrayprep", "Remarks" => "Materials (Soil, Etc)"));
         array_push($documentation, array("Headers" => "mat_seedtrayprep_perha", "Remarks" => "Materials (Soil, Etc) per Hectare"));
         array_push($documentation, array("Headers" => "seedsowfert_seedmngt", "Remarks" => "Labor for seed sowing/ Fertilization"));
         array_push($documentation, array("Headers" => "seedsowfert_seedmngt_perha", "Remarks" => "Labor for seed sowing/ Fertilization per Hectare"));
         array_push($documentation, array("Headers" => "mat_seedmngt", "Remarks" => "Material Cost (Seeds, etc)"));
         array_push($documentation, array("Headers" => "mat_seedmngt_perha", "Remarks" => "Material Cost (Seeds, etc) per Hectare"));
         array_push($documentation, array("Headers" => "fert_seedmngt", "Remarks" => "Fertilizer Costs"));
         array_push($documentation, array("Headers" => "fert_seedmngt_perha", "Remarks" => "Fertilizer Costs per Hectare"));
         array_push($documentation, array("Headers" => "meals_seedmngt", "Remarks" => "Meals/Snacks"));
         array_push($documentation, array("Headers" => "meals_seedmngt_perha", "Remarks" => "Meals/Snacks per Hectare"));
         array_push($documentation, array("Headers" => "labor_dsr_cropest", "Remarks" => "Labor Cost"));
         array_push($documentation, array("Headers" => "labor_dsr_cropest_perha", "Remarks" => "Labor Cost per Hectare"));
         array_push($documentation, array("Headers" => "rentseeder_dsr_cropest", "Remarks" => "Rental of Drum Seeder/Seed Spreader/Precision Seeder/Drone"));
         array_push($documentation, array("Headers" => "rentseeder_dsr_cropest_perha", "Remarks" => "Rental of Drum Seeder/Seed Spreader/Precision Seeder/Drone per Hectare"));
         array_push($documentation, array("Headers" => "meals_dsr_cropest", "Remarks" => "Meals and snacks"));
         array_push($documentation, array("Headers" => "meals_dsr_cropest_perha", "Remarks" => "Meals and snacks per Hectare"));
         array_push($documentation, array("Headers" => "pulltrans_tpr_cropest", "Remarks" => "Labor Pulling and Transplanting"));
         array_push($documentation, array("Headers" => "pulltrans_tpr_cropest_perha", "Remarks" => "Labor Pulling and Transplanting per Hectare"));
         array_push($documentation, array("Headers" => "replant_tpr_cropest", "Remarks" => "Labor for replanting of missing hills"));
         array_push($documentation, array("Headers" => "replant_tpr_cropest_perha", "Remarks" => "Labor for Replanting of missing hills per Hectare"));
         array_push($documentation, array("Headers" => "mat_tpr_cropest", "Remarks" => "Materials Costs"));
         array_push($documentation, array("Headers" => "mat_tpr_cropest_perha", "Remarks" => "Materials Costs per Hectare"));
         array_push($documentation, array("Headers" => "meals_tpr_cropest", "Remarks" => "Meals and snacks "));
         array_push($documentation, array("Headers" => "meals_tpr_cropest_perha", "Remarks" => "Meals and snacks per Hectare"));
         array_push($documentation, array("Headers" => "seedhaul_mechtpr_cropest", "Remarks" => "Labor for hauling of seedlings"));
         array_push($documentation, array("Headers" => "seedhaul_mechtpr_cropest_perha", "Remarks" => "Labor for hauling of seedlings per Hectare"));
         array_push($documentation, array("Headers" => "mechtrans_mechtpr_cropest", "Remarks" => "Rental of mechanical transplanter"));
         array_push($documentation, array("Headers" => "mechtrans_mechtpr_cropest_perha", "Remarks" => "Rental of mechanical transplanter per Hectare"));
         array_push($documentation, array("Headers" => "replant_mechtpr_cropest", "Remarks" => "Labor for Replanting of missing hills"));
         array_push($documentation, array("Headers" => "replant_mechtpr_cropest_perha", "Remarks" => "Labor for Replanting of missing hills per Hectare"));
         array_push($documentation, array("Headers" => "meals_mechtpr_cropest", "Remarks" => "Meals and snacks"));
         array_push($documentation, array("Headers" => "meals_mechtpr_cropest_perha", "Remarks" => "Meals and snacks per Hectare"));
         array_push($documentation, array("Headers" => "fert_fertmngt", "Remarks" => "Fertilizer Cost"));
         array_push($documentation, array("Headers" => "fert_fertmngt_perha", "Remarks" => "Fertilizer Cost per Hectare"));
         array_push($documentation, array("Headers" => "meals_fertmngt", "Remarks" => "Meals and Snacks"));
         array_push($documentation, array("Headers" => "meals_fertmngt_perha", "Remarks" => "Meals and Snacks per Hectare"));
         array_push($documentation, array("Headers" => "labor_fertmngt", "Remarks" => "Labor"));
         array_push($documentation, array("Headers" => "labor_fertmngt_perha", "Remarks" => "Labor per Hectare"));
         array_push($documentation, array("Headers" => "irrig_watermngt", "Remarks" => "Irrigation fee (NIS/CIS)"));
         array_push($documentation, array("Headers" => "irrig_watermngt_perha", "Remarks" => "Irrigation fee (NIS/CIS) per Hectare"));
         array_push($documentation, array("Headers" => "fuel_watermngt", "Remarks" => "Fuel and oil cost (STW)"));
         array_push($documentation, array("Headers" => "fuel_watermngt_perha", "Remarks" => "Fuel and oil cost (STW) per Hectare"));
         array_push($documentation, array("Headers" => "meals_watermngt", "Remarks" => "Meals and Snacks"));
         array_push($documentation, array("Headers" => "meals_watermngt_perha", "Remarks" => "Meals and Snacks per Hectare"));
         array_push($documentation, array("Headers" => "labor_watermngt", "Remarks" => "Labor"));
         array_push($documentation, array("Headers" => "labor_watermngt_perha", "Remarks" => "Labor per Hectare"));
         array_push($documentation, array("Headers" => "mollusc_pestmngt", "Remarks" => "Molluscicide"));
         array_push($documentation, array("Headers" => "mollusc_pestmngt_perha", "Remarks" => "Molluscicide per Hectare"));
         array_push($documentation, array("Headers" => "insect_pestmngt", "Remarks" => "Insecticide"));
         array_push($documentation, array("Headers" => "insect_pestmngt_perha", "Remarks" => "Insecticide per Hectare"));
         array_push($documentation, array("Headers" => "fungi_pestmngt", "Remarks" => "Fungicide"));
         array_push($documentation, array("Headers" => "fungi_pestmngt_perha", "Remarks" => "Fungicide per Hectare"));
         array_push($documentation, array("Headers" => "rodent_pestmngt", "Remarks" => "Rodenticide"));
         array_push($documentation, array("Headers" => "rodent_pestmngt_perha", "Remarks" => "Rodenticide per Hectare"));
         array_push($documentation, array("Headers" => "meals_pestmngt", "Remarks" => "Meals and snacks"));
         array_push($documentation, array("Headers" => "meals_pestmngt_perha", "Remarks" => "Meals and snacks per Hectare"));
         array_push($documentation, array("Headers" => "labor_pestmngt", "Remarks" => "Labor"));
         array_push($documentation, array("Headers" => "labor_pestmngt_perha", "Remarks" => "Labor per Hectare"));
         array_push($documentation, array("Headers" => "herb_weedmngt", "Remarks" => "Herbicide"));
         array_push($documentation, array("Headers" => "herb_weedmngt_perha", "Remarks" => "Herbicide per Hectare"));
         array_push($documentation, array("Headers" => "meals_weedmngt", "Remarks" => "Meals and snacks"));
         array_push($documentation, array("Headers" => "meals_weedmngt_perha", "Remarks" => "Meals and snacks per Hectare"));
         array_push($documentation, array("Headers" => "manweed_weedmngt", "Remarks" => "Labor for manual weeding"));
         array_push($documentation, array("Headers" => "manweed_weedmngt_mngt", "Remarks" => "Labor for manual weeding per Hectare"));
         array_push($documentation, array("Headers" => "herbapp_weedmngt", "Remarks" => "Labor for Herbicide application"));
         array_push($documentation, array("Headers" => "herbapp_weedmngt_perha", "Remarks" => "Labor for Herbicide application per Hectare"));
         array_push($documentation, array("Headers" => "manharvest_harvestmngt", "Remarks" => "Labor for Manual Harvesting"));
         array_push($documentation, array("Headers" => "manharvest_harvestmngt_perha", "Remarks" => "Labor for Manual Harvesting per Hectare"));
         array_push($documentation, array("Headers" => "rentthresh_harvestmngt", "Remarks" => "Rental of Thresher"));
         array_push($documentation, array("Headers" => "rentthresh_harvestmngt_perha", "Remarks" => "Rental of Thresher per Hectare"));
         array_push($documentation, array("Headers" => "rentcombine_harvestmngt", "Remarks" => "Rental of Combine Harvester"));
         array_push($documentation, array("Headers" => "rentcombine_harvestmngt_perha", "Remarks" => "Rental of Combine Harvester per Hectare"));
         array_push($documentation, array("Headers" => "laborhaul_harvestmngt", "Remarks" => "Labor for Hauling"));
         array_push($documentation, array("Headers" => "laborhaul_harvestmngt_perha", "Remarks" => "Labor for Hauling per Hectare"));
         array_push($documentation, array("Headers" => "mat_harvestmngt", "Remarks" => "Harvest Materials (sacks,twines,etc)"));
         array_push($documentation, array("Headers" => "mat_harvestmngt_perha", "Remarks" => "Harvest Materials (sacks,twines,etc) per Hectare"));
         array_push($documentation, array("Headers" => "meals_harvestmngt", "Remarks" => "Meals and snacks"));
         array_push($documentation, array("Headers" => "meals_harvestmngt_perha", "Remarks" => "Meals and snacks per Hectare"));
         array_push($documentation, array("Headers" => "careshare_othercost", "Remarks" => "Labor Expenses (Caretaker/Maintenance Share)"));
         array_push($documentation, array("Headers" => "careshare_othercost_perha", "Remarks" => "Labor Expenses (Caretaker/Maintenance Share) per Hectare"));
         array_push($documentation, array("Headers" => "othermatexp_othercost", "Remarks" => "Other Material Expenses"));
         array_push($documentation, array("Headers" => "othermatexp_othercost_perha", "Remarks" => "Other Material Expenses per Hectare"));
         array_push($documentation, array("Headers" => "landrent_othercost", "Remarks" => "Land Rental"));
         array_push($documentation, array("Headers" => "landrent_othercost_perha", "Remarks" => "Land Rental per Hectare"));
         array_push($documentation, array("Headers" => "interest_othercost", "Remarks" => "Interest Cost"));
         array_push($documentation, array("Headers" => "interest_othercost_perha", "Remarks" => "Interest Cost per Hectare"));
         array_push($documentation, array("Headers" => "totlabexp_totcost", "Remarks" => "Total Labor Expenses"));
         array_push($documentation, array("Headers" => "totlabexp_totcost_perha", "Remarks" => "Total Labor Expenses per Hectare"));
         array_push($documentation, array("Headers" => "totmatexp_totcost", "Remarks" => "Total Material Expenses"));
         array_push($documentation, array("Headers" => "totmatexp_totcost_perha", "Remarks" => "Total Material Expenses per Hectare"));
         array_push($documentation, array("Headers" => "totmachrent_totcost", "Remarks" => "Total Machine Rental"));
         array_push($documentation, array("Headers" => "totalmachrent_totcost_perha", "Remarks" => "Total Machine Rental per Hectare"));
         array_push($documentation, array("Headers" => "totfertexp_totcost", "Remarks" => "Total Fertilizer Expenses (Inorganic, Organic, Foliar) "));
         array_push($documentation, array("Headers" => "totfertexp_totcost_perha", "Remarks" => "Total Fertilizer Expenses (Inorganic, Organic, Foliar) per Hectare"));
         array_push($documentation, array("Headers" => "totchemexp_totcost", "Remarks" => "Total Chemical Expenses ( Insecticide, Herbicide, Molluscicide, Fungicide, etc.)"));
         array_push($documentation, array("Headers" => "totchemexp_totcost_perha", "Remarks" => "Total Chemical Expenses ( Insecticide, Herbicide, Molluscicide, Fungicide, etc.) per Hectare"));
         array_push($documentation, array("Headers" => "totmealsexp_totcost", "Remarks" => "Total Meals and Snacks Expenses"));
         array_push($documentation, array("Headers" => "totmealsexp_totcost_perha", "Remarks" => "Total Meals and Snacks Expenses per Hectare"));
         array_push($documentation, array("Headers" => "totirrigexp_totcost", "Remarks" => "Total Irrigation Expenses (Fees, fuel, oil, etc.)"));
         array_push($documentation, array("Headers" => "totirrigexp_totcost_perha", "Remarks" => "Total Irrigation Expenses (Fees, fuel, oil, etc.) per Hectare"));
         array_push($documentation, array("Headers" => "tototherexp_totcost", "Remarks" => "Total Other Expenses (Land Rental & Interest Costs)"));
         array_push($documentation, array("Headers" => "tototherexp_totcost_totcost", "Remarks" => "Total Other Expenses (Land Rental & Interest Costs) per Hectare"));
         array_push($documentation, array("Headers" => "totprod_totcost", "Remarks" => "Total Production Cost"));
         array_push($documentation, array("Headers" => "totprod_totcost_perha", "Remarks" => "Total Production Cost per Hectare"));
         array_push($documentation, array("Headers" => "priceper_kg_dr", "Remarks" => "Actual Price/kg dry"));
         array_push($documentation, array("Headers" => "priceper_kg_fr", "Remarks" => "Actual Price/kg fresh"));
                   
                   
         array_push($documentation, array("Headers" => "yield", "Remarks" => "Actual Yield (Tons)"));
         array_push($documentation, array("Headers" => "yield_perha", "Remarks" => "Actual Yield per Hectare (Tons/Ha)"));
         array_push($documentation, array("Headers" => "costper_kg", "Remarks" => "Cost Per Kilo"));
         array_push($documentation, array("Headers" => "bagsnot14", "Remarks" => "Number of Bags (14-14-14-)"));
         array_push($documentation, array("Headers" => "bagsnourea", "Remarks" => "Number of Bags (46-0-0)"));
         array_push($documentation, array("Headers" => "bagsnoammophos", "Remarks" => "Number of Bags (16-20-0)"));
         array_push($documentation, array("Headers" => "bagsnosul", "Remarks" => "Number of Bags (21-0-0)"));
         array_push($documentation, array("Headers" => "bagsno", "Remarks" => "Number of bags (17-0-17)"));
         array_push($documentation, array("Headers" => "bagsnomop", "Remarks" => "Number of Bags (0-0-60)"));
         array_push($documentation, array("Headers" => "n_npk", "Remarks" => "(N) Nitrogen (kg/actual area)"));
         array_push($documentation, array("Headers" => "n_npk_perha", "Remarks" => "(N) Nitrogen (kg/ha)"));
         array_push($documentation, array("Headers" => "p_npk", "Remarks" => "(P) Phosphorous (kg/actual area"));
         array_push($documentation, array("Headers" => "p_npk_perha", "Remarks" => "(P) Phosphorous (kg/ha)"));
         array_push($documentation, array("Headers" => "k_npk", "Remarks" => "(K) Potassium (kgactual area)"));
         array_push($documentation, array("Headers" => "k_npk_perha", "Remarks" => "(K) Potassium (kg/ha)"));
         array_push($documentation, array("Headers" => "crop_cut_ha", "Remarks" => "Crop Cut Yield (tons/ha)"));







         $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
      return Excel::create("Palaysikatan_Info_sheet"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $documentation,$npk) {
          $excel->sheet("Sheet 1", function($sheet) use ($excel_data) {
              $sheet->fromArray($excel_data);
              $sheet->getStyle('J:EL')->getAlignment()->setHorizontal('center');
              
          }); 
          $excel->sheet("N-P-K", function($sheet) use ($npk) {
              $sheet->cell("A1", function($cells){
                   $cells->setValue("FCA");
               });
              $sheet->cell("F1", function($cells){
                   $cells->setValue("1st application");
               });
               $sheet->cell("L1", function($cells){
                   $cells->setValue("2nd application");
               });
               $sheet->cell("S1", function($cells){
                   $cells->setValue("3rd application");
               });
              $sheet->mergeCells("F1:K1");
              $sheet->cells("F1:K1", function ($cells){
                   $cells->setAlignment('center');
                   $cells->setFontWeight('bold');
                   $cells->setFontSize(12);
               }); 

               $sheet->mergeCells("L1:R1");
              $sheet->cells("L1:R1", function ($cells){
                   $cells->setAlignment('center');
                   $cells->setFontWeight('bold');
                   $cells->setFontSize(12);
               }); 

               $sheet->mergeCells("S1:Y1");
              $sheet->cells("S1:Y1", function ($cells){
                   $cells->setAlignment('center');
                   $cells->setFontWeight('bold');
                   $cells->setFontSize(12);
               }); 
               $sheet->mergeCells("A1:E1");
               $sheet->cells("A1:E1", function ($cells){
                    $cells->setAlignment('center');
                    $cells->setFontWeight('bold');
                    $cells->setFontSize(12);
                }); 
               $sheet->fromArray($npk,'','A2');
          });
          $excel->sheet("documentation", function($sheet) use ($documentation) {
              $sheet->fromArray($documentation);
          });
         
      })->download('xlsx'); 



               
   }




}

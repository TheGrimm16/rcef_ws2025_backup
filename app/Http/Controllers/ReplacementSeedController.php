<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Yajra\Datatables\Facades\Datatables;

class SeedReplacementController extends Controller
{

 	public function home(){
        $repalcement_reasons = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_repalcement_reason')->orderby('reason_name')->get();
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->groupBy('region')->get();
        // $muni_data = $this->municipality_tbl_current();

        return view('release_calamities.home')
            ->with('regions', $regions)
            ->with('repalcement_reasons', $repalcement_reasons);
            // ->with('muni_data', $muni_data);
    }

    public function get_provinces(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('region', '=', $request->region)
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        $return_str= '';
        foreach($provinces as $province){
            $return_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $return_str;
    }

    public function get_municipalities(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
            
        $return_str= '';
        foreach($municipalities as $row){
            $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
        }
        return $return_str;
    }


    public function municipality_tbl(Request $request){
      
        if($request->region == '0'){$region='%';}else{$region=$request->region;}
        if($request->province == '0'){$province='%';}else{$province=$request->province;}
        if($request->municipality == '0'){$municipality='%';}else{$municipality=$request->municipality;}
            
        $return_arr = array();
            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv as a')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement as b', 'b.prvId', '=', 'a.prvId')
                ->where('a.regionName', 'like', '%'.$region)
                ->where('a.province', 'like', '%'.$province)
                ->where('a.municipality', 'like', '%'.$municipality)
                ->select('a.*','b.status','b.replacement_reason')
                ->groupBy('a.municipality')
                ->get();
                

                foreach ($data as $value) {
                        
                   $data_array = array(
                    "prvId" => $value->prvId,
                    "region" => $value->regionName,
                    "province"=> $value->province,
                    "municipality"=> $value->municipality,
                    "prv"=> $value->prv,
                    "prv_code"=> $value->prv_code,
                    "status"=>$value->status,
                    "replacement_reason"=>$value->replacement_reason
                    );

                    array_push($return_arr, $data_array);


                }

        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)

        ->addColumn('action_btn', function($row){
            if($row['status'] == null){
                return "<button class='btn btn-warning btn-xs btn-block' onclick='OpenModal(".$row['prvId'].",1);'> Open for Replacement</button>";
                        
            }if($row['status'] == 1){
                if($row['replacement_reason']==0){
                    return '<button class="btn btn-success btn-xs btn-block" > Allowed for Repalcement: Typhoons</button>';
                }if($row['replacement_reason']==1){
                    return '<button class="btn btn-success btn-xs btn-block" > Allowed for Repalcement: Typhoons</button>';
                }if($row['replacement_reason']==2){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Pest Infestations</button>'; 
                }if($row['replacement_reason']==3){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Volcanic Eruptions</button>';
                }if($row['replacement_reason']==4){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Earthquake</button>';
                }if($row['replacement_reason']==5){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Storm Surge</button>';
                }if($row['replacement_reason']==6){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Prolonged Drought</button>';
                }
                
            }if($row['status'] == 2){
                if($row['replacement_reason']==0){
                    return '<button class="btn btn-secondary btn-xs btn-block "> For Approval: Typhoons</button>';
                }if($row['replacement_reason']==1){
                    return '<button class="btn btn-secondary btn-xs btn-block "> For Approval: Typhoons</button>';
                }if($row['replacement_reason']==2){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Pest Infestations</button>'; 
                }if($row['replacement_reason']==3){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Volcanic Eruptions</button>';
                }if($row['replacement_reason']==4){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Earthquake</button>';
                }if($row['replacement_reason']==5){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Storm Surge</button>';
                }if($row['replacement_reason']==6){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Prolonged Drought</button>';
                }
            }if($row['status'] == 3){
                return '<button class="btn btn-danger btn-xs btn-block"> Declined</button>';
            }
        })
            ->make(true);
    }


    public function municipality_tbl_data(){
            
        $return_arr = array();
            $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement as a')->get();

                foreach ($data as $value) {
                        
                   $data_array = array(
                    "prvId" => $value->prvId,
                    "region" => $value->region,
                    "province"=> $value->province,
                    "municipality"=> $value->municipality,
                    "prv"=> $value->prv,
                    "prv_code"=> $value->prv_code,
                    "status"=>$value->status,
                    "replacement_reason"=>$value->replacement_reason
                    );

                    array_push($return_arr, $data_array);


                }

        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)

        ->addColumn('action_btn', function($row){
            if($row['status'] == null){
                return "<button class='btn btn-warning btn-xs btn-block' onclick='OpenModal(".$row['prvId'].",1);'> Open for Replacement</button>";
                        
            }if($row['status'] == 1){
                if($row['replacement_reason']==0){
                    return '<button class="btn btn-success btn-xs btn-block" > Allowed for Repalcement: Typhoons</button>';
                }if($row['replacement_reason']==1){
                    return '<button class="btn btn-success btn-xs btn-block" > Allowed for Repalcement: Typhoons</button>';
                }if($row['replacement_reason']==2){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Pest Infestations</button>'; 
                }if($row['replacement_reason']==3){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Volcanic Eruptions</button>';
                }if($row['replacement_reason']==4){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Earthquake</button>';
                }if($row['replacement_reason']==5){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Storm Surge</button>';
                }if($row['replacement_reason']==6){
                    return '<button class="btn btn-success btn-xs btn-block"> Allowed for Repalcement: Prolonged Drought</button>';
                }
                
            }if($row['status'] == 2){
                if($row['replacement_reason']==0){
                    return '<button class="btn btn-secondary btn-xs btn-block "> For Approval: Typhoons</button>';
                }if($row['replacement_reason']==1){
                    return '<button class="btn btn-secondary btn-xs btn-block "> For Approval: Typhoons</button>';
                }if($row['replacement_reason']==2){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Pest Infestations</button>'; 
                }if($row['replacement_reason']==3){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Volcanic Eruptions</button>';
                }if($row['replacement_reason']==4){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Earthquake</button>';
                }if($row['replacement_reason']==5){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Storm Surge</button>';
                }if($row['replacement_reason']==6){
                    return '<button class="btn btn-secondary btn-xs btn-block"> For Approval: Prolonged Drought</button>';
                }
            }if($row['status'] == 3){
                return '<button class="btn btn-danger btn-xs btn-block"> Declined</button>';
            }
        })
            ->make(true);
    }



    //insert insert
    public function insert(Request $request){
  
        $return_arr = array();
          $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('prvId',$request->prvId_val)
                ->get();

                foreach ($data as $value) {           
                $data_array = array(
                     "prvId" => $value->prvId,
                     "prv"=> $value->prv,
                     "prv_code"=> $value->prv_code, 
                     "region" => $value->regionName,
                     "province"=> $value->province,
                     "municipality"=> $value->municipality,
                     "status"=>1,
                     "replacement_reason"=>$request->replacement_reason
 
                     );
 
                     array_push($return_arr, $data_array);
                 }

        DB::beginTransaction();
        try{
           $check_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                ->where('prvId',$request->prvId_val)
                ->get();

            if(count($check_data)>0){
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                ->where('prvId',$request->prvId_val)
                ->delete();

                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                ->insert($return_arr);

                $this->update_famer_profile($request);
            
              
            }else{
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                ->insert($return_arr);

                $this->update_famer_profile($request);
            } 

            DB::commit();
            return "insert success";

        } catch (\Exception $e) {
            DB::rollback(); 
            return $e;
        }
    }


    private function update_famer_profile(Request $request){

        $get_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
            ->where('prvId',$request->prvId_val)
            ->first();

        $database = $get_prv->prv_code;

        $released_data = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.released')        
            ->where('province', 'like', '%'.$get_prv->province)
            ->where('municipality', 'like', '%'.$get_prv->municipality)
            ->get();

        
        $farmer_frofile_data = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile_processed')        
            ->where('province', 'like', '%'.$get_prv->province)
            ->where('municipality', 'like', '%'.$get_prv->municipality)
            ->update([
                'is_replacement' => 1,
                'replacement_bags_claimed' =>0
            ]);  

            foreach ($released_data as $val) {

                $farmer_profile_data2 = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile_processed')
                    ->where('rsbsa_control_no', $val->rsbsa_control_no)
                    ->where('farmerID', $val->farmer_id )
                    ->update([
                        'is_replacement' => 1,
                        'replacement_area' =>$val->claimed_area,
                        'replacement_bags' =>$val->bags,
                        'replacement_bags_claimed' =>0
                    ]);      

            }    

            $farmer_frofile_not_in = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile_processed')  
                ->where('province', 'like', '%'.$get_prv->province)
                ->where('municipality', 'like', '%'.$get_prv->municipality)
                ->where('is_replacement',1)
                ->where('replacement_area',0)         
                ->get();

            foreach ($farmer_frofile_not_in as $vals) {
                DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile_processed')
                    ->where('province', 'like', '%'.$get_prv->province)
                    ->where('municipality', 'like', '%'.$get_prv->municipality)
                    ->where('is_replacement',1)
                    ->where('replacement_area',0) 
                    ->update([
                        'replacement_area' =>$vals->actual_area,
                        'replacement_bags' =>$vals->total_claimable
                    ]); 

            }              
    }
}

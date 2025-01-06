<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Yajra\Datatables\Facades\Datatables;

class ApproveReplacementController extends Controller
{

 	public function home(){
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')->groupBy('region')->get();
        $datas = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();
            
        return view('replacement_approval.home')
            ->with('datas', $datas)
            ->with('regions', $regions);
    }

    public function get_provinces(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
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


    public function decline_status(Request $request){

        DB::beginTransaction();
        try{
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
            ->where('id',$request->id_val)
            ->update([
                'status' => 3
            ]);

            DB::commit();
            return 1;

        } catch (\Exception $e) {
            DB::rollback(); 
            return $e;
        }
    }

    

    public function filter_data(Request $request){

        if($request->region == '0'){$region='%';}else{$region=$request->region;}
        if($request->province == '0'){$province='%';}else{$province=$request->province;}

     $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
            ->where('region', 'like', '%'.$region)
            ->where('province', 'like', '%'.$province)
            ->groupBy('municipality')
            ->orderBy('id','desc')
            ->get();
        
            return $municipalities = json_decode(json_encode($municipalities), true);
    }


    public function update_status1(Request $request){

        DB::beginTransaction();
        try{

         $this->update_famer_profile($request);
  
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
            ->where('id',$request->id_val)
            ->update([
                'status' => 1
            ]);

            DB::commit();
            return 1;

        } catch (\Exception $e) {
            DB::rollback(); 
            return $e;
        }
    }


    private function update_famer_profile(Request $request){

        $get_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
        ->where('id',$request->id_val)
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
                // 'replacement_area' =>$val->claimed_area,
                // 'replacement_bags' =>$val->bags,
                'replacement_bags_claimed' =>0
            ]);  

            // DB::table('user')                 
            // ->select('*')
            // ->whereNotIn('farmerID', DB::table('curses')->select('id_user')->where('id_user', '=', $id)->get()->toArray())
            // ->get();


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
   
    }


}

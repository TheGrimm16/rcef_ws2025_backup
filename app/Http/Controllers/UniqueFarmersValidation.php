<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Transfer;
use Auth;
use Yajra\Datatables\Datatables;



use DB;
use Session;
use \stdClass;

class UniqueFarmersValidation extends Controller {

    public function index(){
        $provinces = DB::table("rcef_icts.provincelist")
        ->groupBy('ffrs_parcel_address_prv')
            ->get();

        return view('validation.farmers', compact('provinces'));
    }

    
    public function approvedAllFarmer(Request $request){
        foreach ($request->ids as $key => $value) {
            $request2 = new \stdClass();
            $request2->id =$value;
            $returnData =$this->approvingAndValidating($request);
        }    
        return $returnData;
    }

    public function getFarmerData(Request $request){
        if($request->municipality=="all"){
            $request->municipality ="%";
        }  
        $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where('province', $request->province)
        ->where("municipality", 'like', $request->municipality)
        ->first();

       return $query = DB::table($GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code.'.da_farmer_list')
        ->where('id',$request->id)->get(); 
    }
    public function validating_data(Request $request){
        DB::beginTransaction();
        try {
            if($request->municipality=="all"){
                $request->municipality ="%";
            }  
            
            $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('province', $request->province)
            ->where("municipality", 'like', $request->municipality)
            ->first();

            $query = DB::table($GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code.'.da_farmer_list')
            ->where('id',$request->id)->update([
                'status' => "VALIDATED"
            ]); 

        DB::commit();
        return "ok";
        } catch (Throwable $th) {
            DB::rollback();
            return response([
                'message' => 'Error Getting Labor',
                'error' => $th,
            ], 500);
        }
    }
    private function approvingAndValidating($request){


        DB::beginTransaction();
        try {

            $usertype =  Auth::user()->roles->first()->name;   
        if($usertype=="Coordinator" || $usertype=="seed-inspector" || $usertype=="dro" || $usertype=="branch-it" || $usertype == "rcef-programmer"){
            $StatusState =2;
        }else  if($usertype == "rcef-coordinator"){
            $StatusState =0;
        }
        
        $rcef_result_match = DB::table("rcef_icts.rcef_result_match")
        ->where('index',$request->id)->where('status', $StatusState)->first();
        if(isset($rcef_result_match)){
            if($usertype=="Coordinator" || $usertype=="seed-inspector" || $usertype=="dro" || $usertype=="branch-it" || $usertype == "rcef-programmer"){
           
                if(isset($rcef_result_match)){
                    $status =1;
                }
              
            }
            if($usertype == "rcef-coordinator"){
                $status =2;
            }
        }   
  
        if($status == 2){
            $stats = DB::table("rcef_icts.rcef_result_match")
            ->where('index',$request->id)
            ->update([
                'status'=> $status,
                'validated_by' => Auth::user()->username,
               //'status'=> null,
               //'date_updated'=> null
            ]);
        }else  if($status == 1){
            $stats = DB::table("rcef_icts.rcef_result_match")
            ->where('index',$request->id)
            ->update([
                'status'=> $status,
                'approved_by' => Auth::user()->username,
               //'status'=> null,
               //'date_updated'=> null
            ]);
        }
        
        if($status==2){   
            DB::commit();         
            return "validated";
        }else if($status == 1){
            

            if(strlen($rcef_result_match->ffrs_farmer_reg_code)==1){
                $farmer_reg_code = "0".$rcef_result_match->ffrs_farmer_reg_code;
            }else{
                $farmer_reg_code = $rcef_result_match->ffrs_farmer_reg_code;
            }

            if(strlen($rcef_result_match->ffrs_farmer_prv_code)==1){
                $farmer_prv_code = "0".$rcef_result_match->ffrs_farmer_prv_code;
            }else{
                $farmer_prv_code = $rcef_result_match->ffrs_farmer_prv_code;
            }

            if(strlen($rcef_result_match->ffrs_farmer_mun_code)==1){
                $farmer_mun_code = "0".$rcef_result_match->ffrs_farmer_mun_code;
            }else{
                $farmer_mun_code = $rcef_result_match->ffrs_farmer_mun_code;
            }

            if(strlen($rcef_result_match->ffrs_parcel_reg_code)==1){
                $farm_reg_code = "0".$rcef_result_match->ffrs_parcel_reg_code;
            }else{
                $farm_reg_code = $rcef_result_match->ffrs_parcel_reg_code;
            }

            if(strlen($rcef_result_match->ffrs_parcel_prv_code)==1){
                $farm_prv_code = "0".$rcef_result_match->ffrs_parcel_prv_code;
            }else{
                $farm_prv_code = $rcef_result_match->ffrs_parcel_prv_code;
            }

            if(strlen($rcef_result_match->ffrs_parcel_mun_code)==1){
                $farm_mun_code = "0".$rcef_result_match->ffrs_parcel_mun_code;
            }else{
                $farm_mun_code = $rcef_result_match->ffrs_parcel_mun_code;
            }
            
            $mun_prv = $farm_reg_code.$farm_prv_code.$farm_mun_code;



            $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('prv', $mun_prv)
            ->first();
           
            $returnDataCreatingTBL = app()->call('App\Http\Controllers\rcefIctsController@create_da_profile',[
                'prv_code' =>  $lib_prv->prv_code,
            ]);
            if( $returnDataCreatingTBL != ""){
                $x=0;
                $rcef_id="";
                while ($x==0) {
                    $rcef_id = "R".strtoupper(substr(md5(time()), 0, 6));
                    $da_farmer_profile = DB::table($GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code.'.da_farmer_profile')->where('rcef_id',$rcef_id)->count(); 
                    if($da_farmer_profile == 0){
                        $x=1;
                    }                   
                }
                DB::table($GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code.'.da_farmer_profile')->insert([
                    'rsbsa_control_no'=>$rcef_result_match->ffrs_rsbsa_no,
                    'rcef_id' => $rcef_id,
                    'first_name' => $rcef_result_match->ffrs_first_name,
                    'middle_name' => $rcef_result_match->ffrs_middle_name,
                    'last_name' => $rcef_result_match->ffrs_last_name,
                    'ext_name' => $rcef_result_match->ffrs_ext_name,
                    'sex' => $rcef_result_match->ffrs_gender,
                    'birthdate' => $rcef_result_match->ffrs_birthdate,
                    'deceased' => $rcef_result_match->ffrs_deceased,
                    'fca_member' => $rcef_result_match->ffrs_fca_member,
                    'fca_name' => $rcef_result_match->ffrs_fca_name,
                    'is_arb' => $rcef_result_match->ffrs_arb,
                    'is_pwd' => $rcef_result_match->ffrs_pwd,
                    'farmer_reg' => $rcef_result_match->ffrs_farmer_address_reg,
                    'farmer_prv' => $rcef_result_match->ffrs_farmer_address_prv,
                    'farmer_muni' => $rcef_result_match->ffrs_farmer_address_mun,

                    'farmer_reg_code' => $farmer_reg_code,
                    'farmer_prv_code' => $farmer_prv_code,
                    'farmer_muni_code' => $farmer_mun_code,
                    
                    'farmer_brgy' => $rcef_result_match->ffrs_farmer_bgy_code,
                    'farmer_brgy_name' => $rcef_result_match->ffrs_farmer_address_bgy,
                    'farm_reg' => $rcef_result_match->ffrs_parcel_address_reg,
                    'farm_prv' => $rcef_result_match->ffrs_parcel_address_prv,
                    'farm_muni' => $rcef_result_match->ffrs_parcel_address_mun,

                    'farm_reg_code' => $farm_reg_code,
                    'farm_prv_code' => $farm_prv_code,
                    'farm_muni_code' => $farm_mun_code,

                    'farm_brgy' => $rcef_result_match->ffrs_parcel_bgy_code,
                    'farm_brgy_name' => $rcef_result_match->ffrs_parcel_address_bgy,
                    'farm_ownership' => $rcef_result_match->ffrs_ownership_type,
                    'parcel_area' => $rcef_result_match->ffrs_parcel_area,
                    'crop_area' => $rcef_result_match->ffrs_crop_area,
                    'farm_type' => $rcef_result_match->ffrs_farm_type,
                    'm_fname' => $rcef_result_match->mother_fname,
                    'm_mname' => $rcef_result_match->mother_mname,
                    'm_lname' => $rcef_result_match->mother_lname,
                    'm_ename' => $rcef_result_match->mother_ename,
                    'm_fullname' => $rcef_result_match->ffrs_mother_maiden_name,
                  
                    'origin' => "possible",
                ]);
            DB::commit();
            return "approved";
            }
        }
      
        } catch (Throwable $th) {
            DB::rollback();
            return response([
                'message' => 'Error Getting Labor',
                'error' => $th,
            ], 500);
        }

    }
    public function farmers_datatable(Request $request)
    {
       
        
            if($request->municipality=="all"){
                $request->municipality ="%";
            }  
            
            $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('province', $request->province)
            ->where("municipality", 'like', $request->municipality)
            ->first();

             $query = DB::table($GLOBALS['season_prefix'].'prv_'.$lib_prv->prv_code.'.da_farmer_list')
            ->where('farmer_muni','like',$request->municipality); 
           
        
     
        return Datatables::of($query)
        ->addColumn('action', function ($query) {

            $usertype =  Auth::user()->roles->first()->name;   
            if($query->status=="VALIDATED"){
                $action ="Already Validated";
            }else{
                $action = '<button style="width:180px" class="btn btn-success validating_btn" data-id=\''.$query->id.'\'>Validate</button><br><button class="btn btn-warning invalid_btn" data-id=\''.$query->id.'\'>Invalid Data (Revalidate)</button>'; 
            }
            
           
            

            return $action;
        })->make(true);
    }

    public function municipality(Request $request)
    {
        $provCode = $request->input('provCode');

        $municipalities = DB::table("rcef_icts.provincelist")
            ->where('ffrs_parcel_address_prv',$provCode)
            ->groupBy('ffrs_parcel_address_mun')
            ->get();

        echo json_encode($municipalities);
    }
}

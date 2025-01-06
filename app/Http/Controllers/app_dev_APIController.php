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
use Illuminate\Support\Facades\Hash;
use Config;
use DB;
use Excel;

use Session;
use Auth;

class app_dev_APIController extends Controller
{

    public function one_app_login(Request $request){
           $return_array = array();
        if($request->api_key == "de9c64b389a3916e91419896c578baf5"){ 
        try {
            $user = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
             ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'username', 'email', 'password', 'sex', 'region', 'province', 'municipality', 'agencyId', 'stationId', 'position', 'designation', 'name as uRole', 'coopAccreditation', 'users.province as tagged_province')
            ->join('users_coop', 'users.userId', '=', 'users_coop.userId', 'left')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->join('roles', 'role_user.roleId', '=', 'roles.roleId')
            ->where('username', $request->username)
//            ->where('roles.name', 'administrator')
            ->where('users.isDeleted', '0')
  //          ->where('users.province', '!=', "")
            ->get();

         

            if(count($user) > 0){

                //dd($user);

                if (Hash::check($request->password, $user[0]->password)) { 

                    /*
                    $regionProvince = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->where('prv', 'like', $user[0]->province.'%')
                        ->first();
                    if(count($regionProvince)>0){
                        $user[0]->regionName = $regionProvince->regionName;
                        $user[0]->provinceName = $regionProvince->province;
                    }else{
                        $user[0]->regionName = null;
                        $user[0]->provinceName = null;
                    } */

                                array_push($return_array, array(
                                    "status" => 1,
                                    "message" => "Success",
                                    "data" => $user[0],
                                ));
                                return json_encode($return_array);
                
                }else{
                    array_push($return_array, array(
                        "status" => 0,
                        "message" => "Invalid Credentials",
                        "data" => array(),
                    ));

                    return json_encode($return_array);
                }
            }else{
                array_push($return_array, array(
                        "status" => 0,
                        "message" => "Invalid User",
                        "data" => array(),
                    ));

                return json_encode($return_array);
            }
            
        } catch (\Illuminate\Database\QueryException $ex) {
            return json_encode($ex);
        }

    }else{
        array_push($return_array, array(
                        "status" => 0,
                        "message" => "You do not have Privilege for this API",
                        "data" => array(),
                    ));

                return json_encode($return_array);
    }


    }




    public function seedVarietyCommitments($api_key,$coopId){
        if($api_key == "de9c64b389a3916e91419896c578baf5"){
            if($coopId == "all"){
            $coopId = "%";
            }
                $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                    ->where("coopId", "like", $coopId)
                    ->groupBy("coopName")
                    ->orderBy("coopName", "ASC")
                    ->get();
                    $return_arr = array();

                    foreach ($coop as $key => $coopData) {
                        $commitments_regional = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_regional")
                                        ->select(DB::raw("CONCAT(region_name,'-',seed_variety,'|',volume) as data"))
                                        ->where("coop_Id", $coopData->coopId)
                                        ->get();

                                        array_push($return_arr, array(
                                            "coopId" => $coopData->coopId,
                                            "coopName" => $coopData->accreditation_no,
                                            "regional_commitments" => $commitments_regional
                                        ));

                    }

                    return json_encode($return_arr);
        }else{
            return json_encode("You do not have Privilege for this API");
        }
    }


    public function seedGrowerAccount($api_key,$username){
        if($api_key == "de9c64b389a3916e91419896c578baf5"){
            if($username == "all"){
            $username = "%";
            }
                $role = DB::table("role_user")
                    ->select("users.*")
                    ->join("users", "users.userId", "=", "role_user.userId")
                    ->where("role_user.roleId", 3)
                    ->where("users.username", 'like', $username)
                    ->groupBy("userId")
                    ->get();
                    $return_arr = array();

                    foreach ($role as $key => $role_data) {

                                $accreditation = DB::table("users_coop")
                                    ->where("userId", $role_data->userId)
                                    ->first();
                                    if(count($accreditation)>0){
                                        $accreditation = $accreditation->coopAccreditation;
                                    }else{
                                        $accreditation = null;
                                    }
                                 array_push($return_arr, array(
                                            "userId" => $role_data->userId,
                                            "username" => $role_data->username,
                                            "password" => $role_data->password,
                                            "accreditation_number" => $accreditation,
                                            "province" => $role_data->province
                                        ));
                             
                    }

                    return json_encode($return_arr);
        }else{
            return json_encode("You do not have Privilege for this API");
        }
    }


    public function rlaCount(Request $request){
        if($request->api_key == "de9c64b389a3916e91419896c578baf5"){
            $coopInfo = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                    ->where("accreditation_no", $request->coop_accreditation)
                    ->orwhere("updated_accreditation_no", $request->coop_accreditation)
                    ->first();
            $return_arr = array();
                if(count($coopInfo)>0){
                  $rlaData = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")
                    ->where("coopAccreditation", 'like', $coopInfo->accreditation_no) 
                    ->where("moaNumber", $coopInfo->current_moa)
                    ->orwhere("coopAccreditation", $coopInfo->updated_accreditation_no)
                    ->where("moaNumber", $coopInfo->current_moa)
                    ->groupBy("labNo")
                    ->groupBy("lotNo")
                    ->get();
                    foreach ($rlaData as $key => $rlaInfo) {
                        $delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->where("is_cancelled", 0)
                            ->where("seedTag", $rlaInfo->labNo."/".$rlaInfo->lotNo)
                            ->sum("totalBagCount");

                            array_push($return_arr, array(
                                "coopId"=> $coopInfo->coopId,
                                "coopName"=>$coopInfo->coopName,
                                "labNo" => $rlaInfo->labNo,
                                "lotNo" => $rlaInfo->lotNo,
                                "passed_bags"=> $rlaInfo->noOfBags,
                                "confirm_delivery" => $delivery
                            ));
                    }
                }
                    return json_encode($return_arr);
        }else{
            return json_encode("You do not have Privilege for this API");
        }
    }




}

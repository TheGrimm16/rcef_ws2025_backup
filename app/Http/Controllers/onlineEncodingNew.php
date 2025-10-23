<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Yajra\Datatables\Datatables;
use Auth;
use Excel;
use Hash;
use DateTime;

class onlineEncodingNew extends Controller {
    public function index(){

        // if(Auth::user()->roles->first()->name != "rcef-programmer"){
        //     $mss = "Under Development";
        //         return view("utility.pageClosed")
        //     ->with("mss",$mss);
        // }
        
        return view('onlineEncodingNew.index');
    }

    public function getProvinces(){
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select('prv_code', 'province')
            ->groupBy('province')
            ->get();
    }

    public function getMunicipalities(Request $request){
        $prv = $request["prv"];
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select('temp_prv', 'municipality')
            ->groupBy('municipality')
            ->where("prv_code", $prv)
            ->get();
    }

    public function getDropoff(Request $request){
        $mun = str_replace('-', '', $request["mun"]);
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->select('dropOffPoint', 'prv_dropoff_id')
            ->groupBy('prv_dropoff_id')
            ->where("prv", $mun)
            ->where("qrStart", 0)
            ->get();
    }

    public function getBalance(Request $request){
        $dropoff = $request["dop"];
        $totalStocks = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->select("prv_dropoff_id", "dropOffPoint", "prv", "province", "municipality", "seedVariety", DB::raw("SUM(totalBagCount) as totalBag"))
            ->where('prv_dropoff_id', $dropoff)
            ->where('qrStart', 0)
            ->groupBy('seedVariety')
            ->get();

        foreach($totalStocks as $stock)
        {
            $prv = substr($stock->prv_dropoff_id,0,4);
            $downloadedStocks = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_stocks_download_transaction')
            ->where('prv_dropoff_id', $stock->prv_dropoff_id)
            ->where('is_cleared',0)
            ->where('seed_variety','like', '%'.$stock->seedVariety.'%')
            ->sum('number_of_bag');

            $new_released = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.new_released')
                    ->where('prv_dropoff_id', $stock->prv_dropoff_id)
                    ->where('seed_variety','like', '%'.$stock->seedVariety.'%')
                    ->where('category',"INBRED")
                    ->sum('bags_claimed');

            $stock->totalBag = $stock->totalBag - ($downloadedStocks + $new_released);
        }

        $totalStocks = collect($totalStocks);
        return $totalStocks;
    }

    public function verifyFarmerFromList(Request $request){
        $rsbsa_control_no = $request->rsbsa_control_no;
        $firstName = $request->firstName;
        $midName = $request->midName;
        $lastName = $request->lastName;
        $extName = $request->extName;
        $sex = $request->sex;

        $birthdate = $request->birthdate;
        $bdate = new DateTime($birthdate);
        $birthdate_parsed = $bdate->format('m/d/Y');

        $prv = str_replace('-', '', substr($rsbsa_control_no, 0, 5));

        // dd($prv);
        $first_pass = DB::table($GLOBALS['season_prefix']."prv_".$prv.".farmer_information_final")
            ->where("rsbsa_control_no", $rsbsa_control_no)
            ->first();
        
        if(!$first_pass){
            $second_pass = DB::table($GLOBALS['season_prefix']."prv_".$prv.".farmer_information_final")
                ->where("lastName", "LIKE", "%".$lastName."%")
                ->where("firstName", "LIKE", "%".$firstName."%")
                ->where("midName", "LIKE", "%".$midName."%")
                ->where("extName", "LIKE", "%".$extName."%")
                ->where("birthdate", "LIKE", "%".$birthdate_parsed."%")
                ->where("sex", "LIKE", $sex)
                ->first();

            if(!$second_pass){
                return array(
                    "status" => "PASS",
                    "message" => "RSMS 2-stage verification passed. No exact match found."
                );
            }else{
                return array(
                    "status" => "FAIL",
                    "message" => "Profile found under a different RSBSA Control No.:",
                    "data" => array(
                        "fullName" => $second_pass->fullName,
                        "sex" => $second_pass->sex,
                        "rsbsa_control_no" => $second_pass->rsbsa_control_no
                    )
                );
            }
        }else{
            return array(
                "status" => "FAIL",
                "message" => "Profile found with the RSBSA Control No.:",
                "data" => array(
                    "fullName" => $first_pass->fullName,
                    "sex" => $first_pass->sex,
                    "rsbsa_control_no" => $first_pass->rsbsa_control_no
                )
            );
        }
    }

    public function getAddrProvinces(Request $request){
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select(
                "province",
                "prv_code"
            )
            ->whereNotIn('prv_code', ['9999']) //exclude
            ->groupBy('prv_code')
            ->get();
    }

    public function getAddrMunicipalities(Request $request){
        $prv = $request->prv;
        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select(
                "municipality",
                "prv"
            )
            ->where('prv_code', $prv)
            ->whereNotIn('prv_code', ['9999']) //exclude
            ->get();
    }

    public function getAddrBarangays(Request $request){
        $geo = $request->geo;
        return DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_geocodes")
            ->select(
                'geocode_brgy',
                'name'
            )
            ->where('geocode_municipality', $geo)
            ->get();
    }

    public function saveDistribution(Request $request){
        $profile = $request->profile;
        $release = $request->release;
        $working_prv = $profile["claiming_prv"];
        $working_prv = str_replace("-", "", $working_prv);
        $working_prv = substr($working_prv, 0, 4);
        $release["released_by"] = Auth::user()->username;
        if (empty($release["server_date_received"])) {
            unset($release["server_date_received"]);
        }

        //generate db_ref
        $max_dbref = DB::table($GLOBALS['season_prefix']."prv_".$working_prv.".farmer_information_final")
            ->select(
                DB::raw("MAX(db_ref) as maxd")
            )
            ->first();
        $release["farmer_id_address"] = $max_dbref->maxd + 1;
        $profile["db_ref"] = $max_dbref->maxd + 1;
        $release["db_ref"] = $max_dbref->maxd + 1;


        //generate rcef_id
        $existing = true;
        $generated = null;
        while($existing){
            $generated = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

            $check = DB::table($GLOBALS['season_prefix']."prv_".$working_prv.".farmer_information_final")
                ->where("rcef_id", "LIKE", $generated)
                ->first();

            if(!$check) $existing = false;
        }
        $profile["rcef_id"] = $working_prv.$generated;
        $release["rcef_id"] = $working_prv.$generated;

        // return array(
        //     "profile" => $profile,
        //     "release" => $release
        // );

        DB::beginTransaction();

        try {
            $profileId = DB::table($GLOBALS['season_prefix']."prv_".$working_prv.".farmer_information_final")
                ->insert([$profile]);

            $releaseId = DB::table($GLOBALS['season_prefix']."prv_".$working_prv.".new_released")
                ->insert([$release]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return array(
            "status" => 200,
            "message" => "SUCCESS",
            "data" => array(
                "rcef_id" => $profile["rcef_id"],
                "db_ref" => $profile["db_ref"]
            )
        );
    }

    public function getSeedVars(Request $request){
        $seed = $request["term"];
        if(!$seed){
            $seed = "";
        }
        return DB::table($GLOBALS['season_prefix']."seed_seed.tbl_varieties")
            ->select(
                'variety as seedItem',
                'variety as seedName'
            )
            ->where('variety', 'LIKE', "%".$seed."%")
            ->limit(10)
            ->get();
    }
}

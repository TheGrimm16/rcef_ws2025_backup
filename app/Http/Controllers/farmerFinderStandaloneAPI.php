<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use DB;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
class farmerFinderStandAloneApi extends Controller{
    
    public function moveToPrv(Request $request){
        if($request->api_key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return;
        }

        $user = $request->user;

        $chk_farmer = DB::table($GLOBALS["season_prefix"]."prv_".substr($request->from_prv,0,4).".farmer_information_final")
            ->where("rcef_id", $request->rcef_id)
            ->first();

            if($chk_farmer != null){
                $for_transfer = $chk_farmer;
                $for_transfer->to_prv_code = $request->to_prv;
                $for_transfer = json_decode(json_encode($for_transfer), true);

                if(substr($request->from_prv,0,4) == substr($request->to_prv, 0, 4)){
                    $data_availability = DB::table($GLOBALS["season_prefix"]."prv_".substr($request->from_prv,0,4).".farmer_information_final")
                        ->where("rcef_id",$for_transfer["rcef_id"])
                        ->update([
                            "to_prv_code" => $request->to_prv
                        ]);
                }else{
                    $data_availability = DB::table($GLOBALS["season_prefix"]."prv_".substr($request->to_prv,0,4).".farmer_information_final")
                    ->where("rcef_id", "T".$for_transfer["rcef_id"])
                    ->first();
                        if($data_availability != null){
                            DB::table($GLOBALS["season_prefix"]."prv_".substr($request->to_prv,0,4).".farmer_information_final")
                                ->where("id", $data_availability->id)
                                ->update([
                                    "rcef_id" => $for_transfer["rcef_id"],
                                    "rsbsa_control_no" => $for_transfer["rsbsa_control_no"],
                                    "to_prv_code" => $request->to_prv
                                ]);
                        }else{
                            unset($for_transfer["id"]);
                            DB::table($GLOBALS["season_prefix"]."prv_".substr($request->to_prv,0,4).".farmer_information_final")
                            ->insert($for_transfer);
                        }
                                

                            DB::table($GLOBALS["season_prefix"]."prv_".substr($request->from_prv,0,4).".farmer_information_final")
                                ->where("rcef_id", $for_transfer["rcef_id"])
                                ->update([
                                    "rcef_id" => "T".$for_transfer["rcef_id"],
                                    "rsbsa_control_no" => "T".$for_transfer["rsbsa_control_no"],
                                    "to_prv_code" =>  "T".$request->to_prv
                                ]);



                }


               
            }

            $command = "Relocating farmer ".$request->rcef_id."from PH".$request->from_prv."000 to PH".$request->to_prv."000";

            $temp = DB::table("rcef_ionic_db.ff_logs")
                ->insert(["id" => null, "username" => $user, "command" => $command, "season_db" => "ws2023"]);

            return "true";



    }

    public function getPrvs($season){
        if($season == "ds2024"){
            return DB::table("ds2024_rcep_reports_view.rcef_nrp_provinces")
                ->select("province", "prv_code", "regionName", "region_sort")
                ->groupBy("province")
                ->orderBy("region_sort", "ASC")
                ->orderBy("province", "ASC")
                ->get();
        }else if($season == "ws2023"){
            return DB::table("ws2023_rcep_reports_view.rcef_nrp_provinces")
                ->select("province", "prv_code", "regionName", "region_sort")
                ->groupBy("province")
                ->orderBy("region_sort", "ASC")
                ->orderBy("province", "ASC")
                ->get();
        }else if($season == "ds2023"){
            return DB::table("ws2023_rcep_reports_view.rcef_provinces")
                ->select("province", "prv_code", "regionName", "region_sort")
                ->groupBy("province")
                ->orderBy("region_sort", "ASC")
                ->orderBy("province", "ASC")
                ->get();
        }else if($season == "ws2022"){
            return DB::connection("ws2022")->table("rcep_reports_view.rcef_provinces")
                ->select("province", DB::raw("LEFT(prv_code, 4) as prv_code"), "regionName")
                ->groupBy("province")
                ->orderBy("province", "ASC")
                ->get();
        }else if($season == "ds2022"){
            return DB::connection("ds2022")->table("rcep_reports_view.rcef_provinces")
                ->select("provinceName as province", DB::raw("LEFT(prv_code, 4) as prv_code"), "regionName")
                ->groupBy("provinceName")
                ->orderBy("provinceName", "ASC")
                ->get();
        }else if($season == "ds2021"){
            return DB::connection("ds2021")->table("rcep_reports.rcef_provinces")
                ->select("provinceName as province", DB::raw("LEFT(prv_code, 4) as prv_code"), "regionName")
                ->groupBy("provinceName")
                ->orderBy("provinceName", "ASC")
                ->get();
        }else if($season == "ws2020"){
            return DB::connection("ws2020")->table("rcep_reports.rcef_provinces")
                ->select("provinceName as province", DB::raw("LEFT(prv_code, 4) as prv_code"), "regionName")
                ->groupBy("provinceName")
                ->orderBy("provinceName", "ASC")
                ->get();
        }else if($season == "ds2020"){
            return DB::connection("ds2022")->table("ds2020_sdms_db_.rcef_provinces")
                ->select("provinceName as province", DB::raw("LEFT(prv_code, 4) as prv_code"), "regionName")
                ->groupBy("provinceName")
                ->orderBy("provinceName", "ASC")
                ->get();
        }
    }

    public function getMunicipalities($key, $prv){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website.";
        }

        return DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select('prv', 'municipality')
            ->where("prv_code", $prv)
            ->get();
    }

    public function getFarmers($key, $prv, $season){
        
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website.";
        }
        $ws2023_tbl = "farmer_information_final";
        $raw = [];
        if($season == "ds2024"){
            $raw = DB::table("ds2024_prv_".$prv.".".$ws2023_tbl)
            ->join("ds2024_rcep_delivery_inspection.lib_prv", DB::raw("replace(".$ws2023_tbl.".claiming_prv, '-', '')"), "=", "lib_prv.prv")
            ->select(DB::raw("CONCAT(firstName, ' ', midName, ' ', lastName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", "rcef_id", "".$ws2023_tbl.".municipality", "".$ws2023_tbl.".brgy_name", "final_area", "claiming_prv", "lib_prv.province as parcel_prv", "lib_prv.municipality as parcel_mun", "db_ref as ref", DB::raw("LEFT(claiming_brgy, 6) as geo_code"), "claiming_brgy", DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
            ->get();
        }else if($season == "ws2023"){
            $raw = DB::table("ws2023_prv_".$prv.".".$ws2023_tbl)
                ->join("ws2023_rcep_delivery_inspection.lib_prv", DB::raw("replace(".$ws2023_tbl.".claiming_prv, '-', '')"), "=", "lib_prv.prv")
                ->select(DB::raw("CONCAT(firstName, ' ', midName, ' ', lastName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", "rcef_id", "".$ws2023_tbl.".municipality", "".$ws2023_tbl.".brgy_name", "final_area", "claiming_prv", "lib_prv.province as parcel_prv", "lib_prv.municipality as parcel_mun", "db_ref as ref", DB::raw("LEFT(geo_code, 6) as geo_code"), "claiming_brgy", DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
                ->get();
        }else if($season == "ds2023"){
            $raw = DB::table("ds2023_prv_".$prv.".farmer_information_final")
                ->select(DB::raw("CONCAT(lastName, ', ', firstName, ' ', midName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", "rcef_id", "municipality", "brgy_name", "final_area", DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
                ->get();
        }else if($season == "ws2022"){
            $raw = DB::connection("ws2022")->table("prv_".$prv.".farmer_profile_processed")
                ->select(DB::raw("CONCAT(lastName, ', ', firstName, ' ', midName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", DB::raw("rsbsa_control_no as rcef_id"), "municipality", DB::raw("province as brgy_name"), DB::raw("actual_area as final_area"), DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
                ->get();
        }else if($season == "ds2022"){
            $raw = DB::connection("ds2022")->table("prv_".$prv.".farmer_profile_processed")
                ->select(DB::raw("CONCAT(lastName, ', ', firstName, ' ', midName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", DB::raw("rsbsa_control_no as rcef_id"), "municipality", DB::raw("province as brgy_name"), DB::raw("actual_area as final_area"), DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
                ->get();
        }else if($season == "ds2021"){
            $raw = DB::connection("ds2021")->table("prv_".$prv.".farmer_profile")
                ->select(DB::raw("CONCAT(lastName, ', ', firstName, ' ', midName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", DB::raw("rsbsa_control_no as rcef_id"), "municipality", "barangay", DB::raw("actual_area as final_area"), DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
                ->where('rsbsa_control_no','NOT LIKE','%Loading%')
                ->where('rsbsa_control_no','NOT LIKE','')
                ->where('lastName','NOT LIKE','')
                ->where('firstName','NOT LIKE','')
                ->get();
        }else if($season == "ws2020"){
            $raw = DB::connection("ws2020")->table("prv_".$prv.".farmer_profile")
                ->select(DB::raw("CONCAT(lastName, ', ', firstName, ' ', midName, ' ', extName) as 'fullName'"), "rsbsa_control_no", "sex", DB::raw("rsbsa_control_no as rcef_id"), "municipality", "barangay", DB::raw("actual_area as final_area"), DB::raw("LEFT(REPLACE(rsbsa_control_no, '-', ''), 6) as homeGeo"))
                ->whereRaw("LENGTH(CONCAT(lastName, firstName)) > 0")
                ->get();
        }else if($season == "ds2020"){
            $raw = DB::connection("ds2022")->table("ds2020_prv_".$prv.".farmer_profile")
                ->join("ds2020_prv_".$prv.".area_history", "farmer_profile.farmerID", "=", "area_history.farmerID")
                ->select(DB::raw("CONCAT(farmer_profile.lastName, ', ', farmer_profile.firstName, ' ', farmer_profile.midName, ' ', farmer_profile.extName) as 'fullName'"), "farmer_profile.farmerID", "sex", DB::raw("farmer_profile.farmerID as rcef_id"),DB::raw("farmer_profile.farmerID as rsbsa_control_no"), "farmer_profile.municipality", "farmer_profile.barangay", DB::raw("area_history.area as final_area"), DB::raw("CONCAT(farmer_profile.municipality, ', ',farmer_profile.province,', ',farmer_profile.region) as homeAddress"))
                ->whereRaw("LENGTH(CONCAT(farmer_profile.lastName, farmer_profile.firstName)) > 0")
                ->get();
        }
        
        if(count($raw) > 0){
            return $raw;
        }else{
            return null;
        }
    }

    public function getFarmer($key, $prv, $id, $season, $claiming_prv){
        
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website.";
        }

        $raw = [];
        $raw_count = 0;
        if($season == "ds2024"){
            $raw = DB::table("ds2024_prv_".$prv.".farmer_information_final")
                ->select("*", "is_ebinhi", DB::raw("0 as 'parcel_count'"), "birthdate")
                ->where('rcef_id', $id)
                ->where('claiming_prv', $claiming_prv)
                ->get();
            $raw_count = DB::table("ds2024_prv_".$prv.".farmer_information_final")
                ->select(DB::raw("count(rcef_id) as count"))
                ->where('rcef_id', $id)
                ->groupBy('rcef_id')
                ->get();
        }else if($season == "ws2023"){
            $raw = DB::table("ws2023_prv_".$prv.".farmer_information_final")
                ->select("*", "is_ebinhi", DB::raw("0 as 'parcel_count'"), "birthdate")
                ->where('rcef_id', $id)
                ->where('claiming_prv', $claiming_prv)
                ->get();
            $raw_count = DB::table("ws2023_prv_".$prv.".farmer_information_final")
                ->select(DB::raw("count(rcef_id) as count"))
                ->where('rcef_id', $id)
                ->groupBy('rcef_id')
                ->get();
        }else if($season == "ds2023"){
            $raw = DB::table("ds2023_prv_".$prv.".farmer_information_final")
                ->select("*", "is_ebinhi")
                ->where('rcef_id', $id)
                ->get();
        }else if($season == "ws2022"){
            $raw = DB::connection("ws2022")->table("prv_".$prv.".farmer_profile_processed")
                ->select(DB::raw("*"), DB::raw("rsbsa_control_no as rcef_id"), DB::raw("actual_area as final_area"), DB::raw("total_claimable as final_claimable"))
                ->where("rsbsa_control_no", $id)
                ->get();
        }else if($season == "ds2022"){
            $raw = DB::connection("ds2022")->table("prv_".$prv.".farmer_profile_processed")
                ->select(DB::raw("*"), DB::raw("rsbsa_control_no as rcef_id"), DB::raw("actual_area as final_area"), DB::raw("total_claimable as final_claimable"))
                ->where("rsbsa_control_no", $id)
                ->get();
        }else if($season == "ds2021"){
            $raw = DB::connection("ds2021")->table("prv_".$prv.".farmer_profile")
                ->select(DB::raw("*"), DB::raw("rsbsa_control_no as rcef_id"), DB::raw("actual_area as final_area"), DB::raw("CEIL(actual_area * 2) as final_claimable"))
                ->where("rsbsa_control_no", $id)
                ->get();
        }else if($season == "ws2020"){
            $raw = DB::connection("ws2020")->table("prv_".$prv.".farmer_profile")
                ->select(DB::raw("*"), DB::raw("rsbsa_control_no as rcef_id"), DB::raw("actual_area as final_area"), DB::raw("CEIL(actual_area * 2) as final_claimable"))
                ->where("rsbsa_control_no", $id)
                ->get();
        }else if($season == "ds2020"){
            $raw = DB::connection("ds2022")->table("ds2020_prv_".$prv.".farmer_profile")
                ->join("ds2020_prv_".$prv.".area_history", "farmer_profile.farmerID", "=", "area_history.farmerId")
                ->select(DB::raw("farmer_profile.*"), DB::raw("farmer_profile.farmerID as rcef_id"), DB::raw("area_history.area as final_area"), DB::raw("CEIL(area_history.area * 2) as final_claimable"))
                ->where("farmer_profile.farmerID", $id)
                ->get();
        }

        if($season == "ds2024" || $season == "ws2023" || $season == "ds2023" || $season == "ds2022" || $season == "ws2022"){
            foreach($raw as $row){
                // $bep = DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_beneficiaries")
                //     ->where('paymaya_code', $row->rcef_id)
                //     ->get();
                
                // if(count($bep) > 0){
                //     $row->is_ebinhi = 1;
                // }else{
                //     $row->is_ebinhi = 0;
                // }
                $row->parcel_count = (int)$raw_count[0]->count;
                $row->birthdate = date('m/d/Y', strtotime($row->birthdate));
    
                return json_encode($row);
            }
        }else{
            //else if season ds22, ds21, ws20
            foreach($raw as $row){
                return json_encode($row);
            }
        }
    }

    public function getCurrentRelocation($key, $prv){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website.";
        }

        $prv = substr($prv, 0, 6);
        $data = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.lib_prv")
            ->select('regionName', 'province', 'municipality')
            ->where('prv', $prv)
            ->get();
        
            foreach($data as $row){
                return array(
                    "region" => $row->regionName,
                    "province" => $row->province,
                    "municipality" => $row->municipality
                );
            }
    }

    public function getFarmerClaims($key, $prv, $id, $season, $claiming_prv, $db_ref){

        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website.";
        }
        //DO CLAIM COUNTERS FOR PREVIOUS SEASONS NA - ON-GOING
        $rawConv = [];
        $rawBep = [];
        // return $claiming_prv;
        
        if($season == "ds2024"){
            $claiming_prv = str_replace('-', '', $claiming_prv);
            $rawConv = DB::table("ds2024_prv_".$prv.".new_released")
                ->select(DB::raw('sum(bags_claimed) as claimed'))
                ->where('db_ref', $db_ref)
                ->where('prv_dropoff_id', 'LIKE', $claiming_prv."%")
                ->where('category', "INBRED")
                ->get();
            $rawBep = DB::table("ds2024_rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('paymaya_code', $id)
                ->get();
            $rawHyb = DB::table("ds2024_prv_".$prv.".new_released")
                ->select(DB::raw('sum(bags_claimed) as kgs'))
                ->where('db_ref', $db_ref)
                ->where('prv_dropoff_id', 'LIKE', $claiming_prv."%")
                ->where('category', "HYBRID")
                ->get();
            
            $total = ($rawConv[0]->claimed? $rawConv[0]->claimed : 0) + ($rawBep[0]->claimed? $rawBep[0]->claimed : 0) + ($rawHyb[0]->kgs? $rawHyb[0]->kgs : 0);
            return array(
                "con" => $rawConv[0]->claimed? (int)$rawConv[0]->claimed : 0,
                "bep" => $rawBep[0]->claimed? (int)$rawBep[0]->claimed : 0,
                "hyb" => $rawHyb[0]->kgs? (int)$rawHyb[0]->kgs : 0,
                "total" => (int)$total,
            );
        }else if($season == "ws2023"){
            $claiming_prv = str_replace('-', '', $claiming_prv);
            $rawConv = DB::table("ws2023_prv_".$prv.".new_released")
                ->select(DB::raw('sum(bags_claimed) as claimed'))
                // ->where('rcef_id', $id)
                ->where('db_ref', $db_ref)
                ->where('prv_dropoff_id', 'LIKE', $claiming_prv."%")
                ->where('category', "INBRED")
                ->get();
            $rawBep = DB::table("ws2023_rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('paymaya_code', $id)
                ->get();
            $rawHyb = DB::table("ws2023_prv_".$prv.".new_released")
                ->select(DB::raw('sum(bags_claimed) as kgs'))
                // ->where('rcef_id', $id)
                ->where('db_ref', $db_ref)
                ->where('prv_dropoff_id', 'LIKE', $claiming_prv."%")
                ->where('category', "HYBRID")
                ->get();
            
            $total = ($rawConv[0]->claimed? $rawConv[0]->claimed : 0) + ($rawBep[0]->claimed? $rawBep[0]->claimed : 0) + ($rawHyb[0]->kgs? $rawHyb[0]->kgs : 0);
            return array(
                "con" => $rawConv[0]->claimed? (int)$rawConv[0]->claimed : 0,
                "bep" => $rawBep[0]->claimed? (int)$rawBep[0]->claimed : 0,
                "hyb" => $rawHyb[0]->kgs? (int)$rawHyb[0]->kgs : 0,
                "total" => (int)$total,
            );
        }else if($season == "ds2023"){
            $rawConv = DB::table("ds2023_prv_".$prv.".new_released")
                ->select(DB::raw('sum(bags_claimed) as claimed'))
                ->where('rcef_id', $id)
                ->get();
            $rawBep = DB::table("ds2023_rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('paymaya_code', $id)
                ->get();
        }else if($season == "ws2022"){
            $rawConv = DB::connection("ws2022")->table("prv_".$prv.".released")
                ->select(DB::raw('sum(bags) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
            $rawBep = DB::connection("ws2022")->table("rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
        }else if($season == "ds2022"){
            $rawConv = DB::connection("ds2022")->table("prv_".$prv.".released")
                ->select(DB::raw('sum(bags) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
            $rawBep = DB::connection("ds2022")->table("rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
        }else if($season == "ds2021"){
            $rawConv = DB::connection("ds2021")->table("prv_".$prv.".released")
                ->select(DB::raw('sum(bags) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
            $rawBep = DB::connection("ds2021")->table("rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
        }else if($season == "ws2020"){
            $rawConv = DB::connection("ws2020")->table("prv_".$prv.".released")
                ->select(DB::raw('sum(bags) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
            $rawBep = DB::connection("ws2020")->table("rcep_paymaya.tbl_claim")
                ->select(DB::raw('count(claimId) as claimed'))
                ->where('rsbsa_control_no', $id)
                ->get();
        }else if($season == "ds2020"){
            $rawConv = DB::connection("ds2022")->table("ds2020_prv_".$prv.".released")
                ->select(DB::raw('sum(bags) as claimed'))
                ->where('farmer_id', $id)
                ->get();
            $rawBep = json_decode('[{"claimed": 0}]');
        }

        $total = $rawConv[0]->claimed + $rawBep[0]->claimed;
        
        return array(
            "con" => (int)$rawConv[0]->claimed,
            "bep" => (int)$rawBep[0]->claimed,
            "total" => (int)$total,
        );
    }

    public function transferFarmer($key, $prv, $id, $season, $bagsClaimed, $user, $claiming_prv){
        // return null; //(UN)COMMENT for disable/enable
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website.";
        }
        $ws2023_tbl = "farmer_information_final";
        //TODO: also update total_claimed_area on the tables to prepare for Hybrid
        $result = null;
        $command = "";
        if($season == "ds2024"){
            //DS2024
            $bep_area = DB::table("ds2024_rcep_paymaya.tbl_beneficiaries")
                ->select('area')
                ->where('paymaya_code', $id)
                ->first();

            $con_area = DB::table("ds2024_prv_".$prv.".new_released")
                ->select(DB::raw('SUM(claimed_area) as claimed_area'))
                ->where('rcef_id', $id)
                // ->where('category', "INBRED")
                ->groupBy('rcef_id')
                ->get();
            
            $bep_total = (float)0.00;
            if($bep_area){
                $bep_area = !$bep_area->area? (float)0.00 : (float)$bep_area->area;
            }else{
                $bep_area = (float)0.00;
            }

            $con_total = (float)0.00;
            if($con_area){
                foreach($con_area as $areas){
                    $con_total = (float)$con_total + (float)$areas->claimed_area;
                }
            }else{
                $con_total = (float)0.00;
            }

            $overall_claimed_area = 0;
            $overall_claimed_area = $bep_total + $con_total;
            $result = DB::table("ds2024_prv_".$prv.".".$ws2023_tbl)
                ->where('rcef_id', $id)
                ->where('claiming_prv', $claiming_prv)
                ->update(['is_ebinhi' => 0, 'is_claimed' => $bagsClaimed > 0? 1 : 0, 'total_claimed' => $bagsClaimed, 'total_claimed_area' => $overall_claimed_area]);
        }else if($season == "ws2023"){
            //WS2023
            $bep_area = DB::table("ws2023_rcep_paymaya.tbl_beneficiaries")
                ->select('area')
                ->where('paymaya_code', $id)
                ->first();

            $con_area = DB::table("ws2023_prv_".$prv.".new_released")
                ->select(DB::raw('SUM(claimed_area) as claimed_area'))
                ->where('rcef_id', $id)
                // ->where('category', "INBRED")
                ->groupBy('rcef_id')
                ->get();
            
            $bep_total = (float)0.00;
            if($bep_area){
                $bep_area = !$bep_area->area? (float)0.00 : (float)$bep_area->area;
            }else{
                $bep_area = (float)0.00;
            }

            $con_total = (float)0.00;
            if($con_area){
                foreach($con_area as $areas){
                    $con_total = (float)$con_total + (float)$areas->claimed_area;
                }
            }else{
                $con_total = (float)0.00;
            }

            $overall_claimed_area = 0;
            $overall_claimed_area = $bep_total + $con_total;
            $result = DB::table("ws2023_prv_".$prv.".".$ws2023_tbl)
                ->where('rcef_id', $id)
                ->where('claiming_prv', $claiming_prv)
                ->update(['is_ebinhi' => 0, 'is_claimed' => $bagsClaimed > 0? 1 : 0, 'total_claimed' => $bagsClaimed, 'total_claimed_area' => $overall_claimed_area]);
        }else if($season == "ds2023"){
            //DS2023
            $bep_area = DB::table("ds2023_rcep_paymaya.tbl_beneficiaries")
                ->select('area')
                ->where('paymaya_code', $id)
                ->first();

            $con_area = DB::table("ds2023_prv_".$prv.".new_released")
                ->select('claimed_area')
                ->where('rcef_id', $id)
                ->where('category', "INBRED")
                ->get();
            
            $bep_total = (float)0.00;
            if($bep_area){
                $bep_area = !$bep_area->area? (float)0.00 : (float)$bep_area->area;
            }else{
                $bep_area = (float)0.00;
            }

            $con_total = (float)0.00;
            if($con_area){
                foreach($con_area as $areas){
                    $con_total = (float)$con_total + (float)$areas->claimed_area;
                }
            }else{
                $con_total = (float)0.00;
            }

            $overall_claimed_area = 0;
            $overall_claimed_area = $bep_total + $con_total;
            // return $overall_claimed_area;
            $result = DB::table("ds2023_prv_".$prv.".farmer_information_final")
                ->where('rcef_id', $id)
                ->where('claiming_prv', $claiming_prv)
                ->update(['is_ebinhi' => 0, 'is_claimed' => 1, 'total_claimed' => $bagsClaimed]);
        }else{
            return "Only for seasons DS2023 and up.";
        }

        $command = "Transferring ".$id." to conventional with ".$bagsClaimed." bags (".$overall_claimed_area."ha) and result: ".$result;

        $temp = DB::table("rcef_ionic_db.ff_logs")
            ->insert(["id" => null, "username" => $user, "command" => $command, "season_db" => $season]);

        return $result;
    }

    public function setAsReplacement($key, $prv, $id, $bagsClaimed, $user, $claiming_prv, $season){
        // return null; //(UN)COMMENT to disable/enable
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }
        $ws2023_tbl = "farmer_information_final";
        if($season == "ds2024"){
            $bep_area = DB::table("ds2024_rcep_paymaya.tbl_beneficiaries")
                ->select('area')
                ->where('paymaya_code', $id)
                ->first();

            $con_area = DB::table("ds2024_prv_".$prv.".new_released")
                ->select(DB::raw('SUM(claimed_area) as claimed_area'))
                ->where('rcef_id', $id)
                ->groupBy('rcef_id')
                ->get();

            $bep_total = (float)0.00;
            if($bep_area){
                $bep_area = !$bep_area->area? (float)0.00 : (float)$bep_area->area;
            }else{
                $bep_area = (float)0.00;
            }

            $con_total = (float)0.00;
            if($con_area){
                foreach($con_area as $areas){
                    $con_total = (float)$con_total + (float)$areas->claimed_area;
                }
            }else{
                $con_total = (float)0.00;
            }
            
            $overall_claimed_area = 0;
            $overall_claimed_area = $bep_total + $con_total;
            $result = DB::table("ds2024_prv_".$prv.".".$ws2023_tbl)
            ->where('rcef_id', $id)
            ->where('claiming_prv', $claiming_prv)
            ->update(['is_replacement' => 1, 'replacement_area' => $overall_claimed_area, 'replacement_bags' => CEIL($overall_claimed_area * 2), 'is_ebinhi' => 0]);

            
            $command = "Tagging ".$id." to replacement with ".$bagsClaimed." bags (".$overall_claimed_area."ha) and result: ".$result;

            $temp = DB::table("rcef_ionic_db.ff_logs")
                ->insert(["id" => null, "username" => $user, "command" => $command, "season_db" => "ws2023"]);
            
            return $result;
        }else if($season == "ws2023"){
            $bep_area = DB::table("ws2023_rcep_paymaya.tbl_beneficiaries")
                ->select('area')
                ->where('paymaya_code', $id)
                ->first();
    
            $con_area = DB::table("ws2023_prv_".$prv.".new_released")
                ->select(DB::raw('SUM(claimed_area) as claimed_area'))
                ->where('rcef_id', $id)
                ->groupBy('rcef_id')
                ->get();

            $bep_total = (float)0.00;
            if($bep_area){
                $bep_area = !$bep_area->area? (float)0.00 : (float)$bep_area->area;
            }else{
                $bep_area = (float)0.00;
            }

            $con_total = (float)0.00;
            if($con_area){
                foreach($con_area as $areas){
                    $con_total = (float)$con_total + (float)$areas->claimed_area;
                }
            }else{
                $con_total = (float)0.00;
            }
            
            $overall_claimed_area = 0;
            $overall_claimed_area = $bep_total + $con_total;
            $result = DB::table("ws2023_prv_".$prv.".".$ws2023_tbl)
            ->where('rcef_id', $id)
            ->where('claiming_prv', $claiming_prv)
            ->update(['is_replacement' => 1, 'replacement_area' => $overall_claimed_area, 'replacement_bags' => CEIL($overall_claimed_area * 2), 'is_ebinhi' => 0]);

            
            $command = "Tagging ".$id." to replacement with ".$bagsClaimed." bags (".$overall_claimed_area."ha) and result: ".$result;

            $temp = DB::table("rcef_ionic_db.ff_logs")
                ->insert(["id" => null, "username" => $user, "command" => $command, "season_db" => "ws2023"]);
            
            return $result;
        }
    }

    public function loginOnApp($key, $user, $password){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        $rawUser = DB::table($GLOBALS["season_prefix"]."sdms_db_dev.users")
            ->select(
                DB::raw("*"), 
                DB::raw("'' as session"), 
                DB::raw("1 as access_level"), 
                DB::raw("0 as access_reference_id"), 
                DB::raw("0 as access_final_area"), 
                DB::raw("0 as access_claims"),
                DB::raw("0 as access_transfer_prv"),
                DB::raw("0 as access_tag_replacement"),
                DB::raw("0 as access_financing"),
                DB::raw('"" as regionName'),
                DB::raw('"" as provinceName')
            )
            ->join($GLOBALS["season_prefix"]."sdms_db_dev.role_user", "users.userId", "=", "role_user.userId")
            ->join($GLOBALS["season_prefix"]."sdms_db_dev.roles", "role_user.roleId", "=", "roles.roleId")
            ->where("users.username", $user)
            ->where("users.isDeleted", 0)
            ->get();
        
        $rawLevel = DB::table("rcef_ionic_db.user_level_access")
            ->where("account_role", $rawUser[0]->name)
            ->get();
        $rawGeo = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.lib_prv")
                ->select("regionName", "province")
                ->where("regCode_int", $rawUser[0]->region)
                ->orWhere("prv_code", $rawUser[0]->province)
                ->groupBy("regCode_int")
                ->first();
        
        if(Hash::check($password, $rawUser[0]->password)){
            $encryptedSession = Crypt::encrypt(time());
            $ins = DB::table("rcef_ionic_db.ff_user_session")
                ->insert([
                    'username' => $rawUser[0]->username,
                    'sessionId' => $encryptedSession
                ]);
            $rawUser[0]->session = $encryptedSession;
            if($rawLevel){
                $rawUser[0]->access_level = $rawLevel[0]->account_level;
                $rawUser[0]->access_reference_id = $rawLevel[0]->access_reference_id;
                $rawUser[0]->access_final_area = $rawLevel[0]->access_final_area;
                $rawUser[0]->access_claims = $rawLevel[0]->access_claims;
                $rawUser[0]->access_transfer_prv = $rawLevel[0]->access_transfer_prv;
                $rawUser[0]->access_tag_replacement = $rawLevel[0]->access_tag_replacement;
                $rawUser[0]->access_financing = $rawLevel[0]->access_financing;
            }else{
                $rawUser[0]->access_level = (int)1;
                $rawUser[0]->access_reference_id = (int)0;
                $rawUser[0]->access_final_area = (int)0;
                $rawUser[0]->access_claims = (int)0;
                $rawUser[0]->access_transfer_prv = (int)0;
                $rawUser[0]->access_tag_replacement = (int)0;
                $rawUser[0]->access_financing = (int)0;
            }
            if($rawUser && $rawGeo){
                $rawUser[0]->regionName = $rawGeo->regionName? $rawGeo->regionName : null;
                $rawUser[0]->province = $rawGeo->province? $rawGeo->province : null;
            }else{
                $rawUser[0]->regionName = null;
                $rawUser[0]->province = null;
            }
            return $rawUser;
        }
        else{
            return null;
        }
    }

    public function getRegionsPerStation($key, $season, $station){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }
        if(strlen($station) < 1){
            return "No station tagged.";
        }

        if($season == "ds2024"){
            $rawTblDelivery = DB::table("ds2024_sdms_db_dev.lib_station")
                ->select("region")
                ->where("stationID", $station)
                ->groupBy("region")
                ->get();
            return $rawTblDelivery;
        }else if($season == "ws2023"){
            $rawTblDelivery = DB::table("ws2023_sdms_db_dev.lib_station")
                ->select("region")
                ->where("stationID", $station)
                ->groupBy("region")
                ->get();
            return $rawTblDelivery;
        }
    }

    public function getAvailableCoops($key, $season, $region){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        if($season == "ds2024"){
            $rawCoops = DB::table("ds2024_rcep_delivery_inspection.tbl_delivery")
                ->join("ds2024_rcep_seed_cooperatives.tbl_cooperatives", "tbl_delivery.coopAccreditation", "=", "tbl_cooperatives.accreditation_no")
                ->select("tbl_cooperatives.coopName as coopName", "tbl_delivery.coopAccreditation as coopAccreditation")
                ->where("tbl_delivery.region", $region)
                ->groupBy("tbl_delivery.coopAccreditation")
                ->get();
            return $rawCoops;
        }else if($season == "ws2023"){
            $rawCoops = DB::table("ws2023_rcep_delivery_inspection.tbl_delivery")
                ->join("ws2023_rcep_seed_cooperatives.tbl_cooperatives", "tbl_delivery.coopAccreditation", "=", "tbl_cooperatives.accreditation_no")
                ->select("tbl_cooperatives.coopName as coopName", "tbl_delivery.coopAccreditation as coopAccreditation")
                ->where("tbl_delivery.region", $region)
                ->groupBy("tbl_delivery.coopAccreditation")
                ->get();
            return $rawCoops;
        }
    }

    // public function getDeliveries($key, $season, $user, $role, $region, $station){
    //     if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
    //         return "Hello officer? There's somebody on our website's API.";
    //     }

    //     if($season == "ws2023"){
    //         $rawTblDelivery = DB::table("ws2023_rcep_delivery_inspection.tbl_delivery");
    //     }
    // }

    public function fetchDeliveries($key, $season, $region, $accred, $dateStart, $dateEnd){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        if($season == "ds2024"){
            if($accred == "All"){
                $accred = "%";
            }
            $rawDeliveries = DB::table("ds2024_rcep_delivery_inspection.tbl_delivery")
                ->select("tbl_delivery.batchTicketNumber as batch", "tbl_delivery.province as prv", "tbl_delivery.municipality as mun", "tbl_delivery.dropOffPoint as dop", "tbl_delivery.deliveryDate as date", DB::raw("SUM(totalBagCount) as expected"), DB::raw("0 as accepted"))
                ->where("tbl_delivery.region", $region)
                ->where("tbl_delivery.coopAccreditation", "LIKE", "%".$accred)
                ->where("tbl_delivery.region", "<>", "Programmer Region")
                ->where("tbl_delivery.is_cancelled", 0)
                ->whereBetween("tbl_delivery.deliveryDate", [$dateStart, $dateEnd])
                ->groupBy("tbl_delivery.batchTicketNumber")
                ->get();

                $indexing = 0;
                foreach($rawDeliveries as $row){
                    $rawDeliveries[$indexing]->expected = (int)$row->expected;
                    $entry = DB::table("ds2024_rcep_delivery_inspection.tbl_actual_delivery")
                        ->select(DB::raw("SUM(totalBagCount) as accepted"))
                        ->where("batchTicketNumber", $row->batch)
                        ->first();
                    if(count($entry) > 0){
                        $rawDeliveries[$indexing]->accepted = (int)$entry->accepted;
                    }else{
                        $rawDeliveries[$indexing]->accepted = (int)0;
                    }
                    $indexing += 1;
                }
            return $rawDeliveries;
        }else if($season == "ws2023"){
            if($accred == "All"){
                $accred = "%";
            }
            $rawDeliveries = DB::table("ws2023_rcep_delivery_inspection.tbl_delivery")
                ->select("tbl_delivery.batchTicketNumber as batch", "tbl_delivery.province as prv", "tbl_delivery.municipality as mun", "tbl_delivery.dropOffPoint as dop", "tbl_delivery.deliveryDate as date", DB::raw("SUM(totalBagCount) as expected"), DB::raw("0 as accepted"))
                ->where("tbl_delivery.region", $region)
                ->where("tbl_delivery.coopAccreditation", "LIKE", "%".$accred)
                ->where("tbl_delivery.region", "<>", "Programmer Region")
                ->where("tbl_delivery.is_cancelled", 0)
                ->whereBetween("tbl_delivery.deliveryDate", [$dateStart, $dateEnd])
                ->groupBy("tbl_delivery.batchTicketNumber")
                ->get();

                $indexing = 0;
                foreach($rawDeliveries as $row){
                    $rawDeliveries[$indexing]->expected = (int)$row->expected;
                    $entry = DB::table("ws2023_rcep_delivery_inspection.tbl_actual_delivery")
                        ->select(DB::raw("SUM(totalBagCount) as accepted"))
                        ->where("batchTicketNumber", $row->batch)
                        ->first();
                    if(count($entry) > 0){
                        $rawDeliveries[$indexing]->accepted = (int)$entry->accepted;
                    }else{
                        $rawDeliveries[$indexing]->accepted = (int)0;
                    }
                    $indexing += 1;
                }
            return $rawDeliveries;
        }
    }

    public function downloadBrgy(){
        return DB::table($GLOBALS["season_prefix"]."sdms_db_dev.lib_geocodes")
            ->get();
    }

    public function fetchInspectionPrv($key, $season){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        // $rawPrvs = DB::table('ws2023_rcep_delivery_inspection.tbl_delivery AS a')
        // ->select('a.*')
        // ->join('ws2023_rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
        //     $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
        //         ->on('a.seedTag', '=', 'b.seedTag');
        // })
        // ->where('b.batchTicketNumber', 'LIKE', '534-BCH-1682007685')
        // ->get();

        $rawPrvs = DB::table($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_delivery')
            ->select('province')
            ->where('is_cancelled', 0)
            ->where('province', '<>', 'Programmer Province')
            ->groupBy('province')
            ->get();

        return $rawPrvs;
    }

    public function fetchInspectionMuni($key, $season, $prv){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        // $rawPrvs = DB::table('ws2023_rcep_delivery_inspection.tbl_delivery AS a')
        // ->select('a.*')
        // ->join('ws2023_rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
        //     $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
        //         ->on('a.seedTag', '=', 'b.seedTag');
        // })
        // ->where('b.batchTicketNumber', 'LIKE', '534-BCH-1682007685')
        // ->get();
        // if($prv == "All"){
        //     $prv = "%";
        // }

        $rawPrvs = DB::table($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_delivery')
            ->select('municipality')
            ->where('is_cancelled', 0)
            ->where('province', '<>', 'Programmer Province')
            ->where('province', 'LIKE', $prv)
            ->groupBy('municipality')
            ->get();

        return $rawPrvs;
    }

    public function fetchInspectionDop($key, $season, $prv, $muni){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        // $rawPrvs = DB::table('ws2023_rcep_delivery_inspection.tbl_delivery AS a')
        // ->select('a.*')
        // ->join('ws2023_rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
        //     $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
        //         ->on('a.seedTag', '=', 'b.seedTag');
        // })
        // ->where('b.batchTicketNumber', 'LIKE', '534-BCH-1682007685')
        // ->get();
        // if($prv == "All"){
        //     $prv = "%";
        // }

        $rawPrvs = DB::table($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_delivery')
            ->select('dropOffPoint')
            ->where('is_cancelled', 0)
            ->where('municipality', '<>', 'Programmer Province')
            ->where('province', 'LIKE', $prv)
            ->where('municipality', 'LIKE', $muni)
            ->groupBy('dropOffPoint')
            ->get();

        return $rawPrvs;
    }

    public function fetchInspectionCoop($key, $season, $prv, $muni, $dop){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        // $rawPrvs = DB::table('ws2023_rcep_delivery_inspection.tbl_delivery AS a')
        // ->select('a.*')
        // ->join('ws2023_rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
        //     $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
        //         ->on('a.seedTag', '=', 'b.seedTag');
        // })
        // ->where('b.batchTicketNumber', 'LIKE', '534-BCH-1682007685')
        // ->get();
        // if($prv == "All"){
        //     $prv = "%";
        // }

        $rawPrvs = DB::table($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_delivery as a')
            ->join($GLOBALS["season_prefix"].'rcep_seed_cooperatives.tbl_cooperatives as b', 'a.coopAccreditation', '=', 'b.accreditation_no')
            ->select("b.accreditation_no as coop_accred", "b.coopName")
            ->where('is_cancelled', 0)
            ->where('municipality', '<>', 'Programmer Province')
            ->where('province', 'LIKE', $prv)
            ->where('municipality', 'LIKE', $muni)
            ->where('dropOffPoint', 'LIKE', $dop)
            ->groupBy('a.coopAccreditation')
            ->get();

        return $rawPrvs;
    }

    public function fetchInspectionData($key, $season, $prv, $muni, $dop, $coop){
        if($key != "d5406c6a6e17b97e4f4e7bf89c0ac18878b5d321"){
            return "Hello officer? There's somebody on our website's API.";
        }

        // $rawPrvs = DB::table('ws2023_rcep_delivery_inspection.tbl_delivery AS a')
        // ->select('a.*')
        // ->join('ws2023_rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
        //     $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
        //         ->on('a.seedTag', '=', 'b.seedTag');
        // })
        // ->where('b.batchTicketNumber', 'LIKE', '534-BCH-1682007685')
        // ->get();
        // if($prv == "All"){
        //     $prv = "%";
        // }

        if($coop == "All"){
            $coop = "%";
        }

        $rawData = DB::table($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_delivery AS a')
            ->join($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
                $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
                    ->on('a.seedTag', '=', 'b.seedTag');
                })
            ->select("a.batchTicketNumber as batch", DB::raw("SUM(a.totalBagCount) as confirmed"), DB::raw("SUM(b.totalBagCount) as inspected"), "deliveryDate", DB::raw("'' as inspectionDate"), DB::raw("'' as seedCoop"), DB::raw("'' as varieties"), DB::raw("'' as assignedInspector"), DB::raw("IF(b.totalBagCount > 0, 'Passed', 'Awaiting Inspection') as status"), "coopAccreditation as coopAccred", "b.dateCreated as dateConfirmed")
            ->where('a.is_cancelled', 0)
            ->where('b.isRejected', 0)
            ->where('a.municipality', '<>', 'Programmer Province')
            ->where('a.province', 'LIKE', $prv)
            ->where('a.municipality', 'LIKE', $muni)
            ->where('a.dropOffPoint', 'LIKE', $dop)
            ->where('a.coopAccreditation', 'LIKE', "%".$coop)
            ->groupBy('a.batchTicketNumber')
            ->get();
        
        $index = 0;
        foreach($rawData as $row){
            //getCoopName 
            $coopName = DB::table($GLOBALS["season_prefix"]."rcep_seed_cooperatives.tbl_cooperatives")
                ->select("coopName")
                ->where("accreditation_no", $row->coopAccred)
                ->first();
            $rawData[$index]->seedCoop = $coopName->coopName;

            //get Inspector Assigned
            $inspection = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_schedule")
                ->select("userId", "inspectionDate")
                ->where("batchTicketNumber", $row->batch)
                ->first();

            if($inspection){
                $inspector = DB::table($GLOBALS["season_prefix"]."sdms_db_dev.users")
                    ->select(DB::raw("CONCAT(firstName, ' ', middleName, ' ', lastName, IF(LENGTH(extName) > 0, ', ', ''), extName) as fullName"))
                    ->where("userId", $inspection->userId)
                    ->first();

                if($inspector){    
                    $rawData[$index]->inspectionDate = $inspection->inspectionDate;
                    $rawData[$index]->assignedInspector = $inspector->fullName;
                }
            }else{
                $rawData[$index]->inspectionDate = "";
                $rawData[$index]->assignedInspector = "No assigned inspector";
            }

            $seeds = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_delivery")
                ->select('seedVariety')
                ->where('is_cancelled', 0)
                ->where("batchTicketNumber", $row->batch)
                ->groupBy("seedVariety")
                ->get();
            if($seeds){
                $counter = 0;
                foreach($seeds as $row){
                    if($counter == 0){
                        $rawData[$index]->varieties = $row->seedVariety;
                    }else{
                        $rawData[$index]->varieties = $rawData[$index]->varieties.", ".$row->seedVariety;
                    }
                $counter += 1;
                }
            }else{
                $rawData[$index]->varieties = "Unknown Variety";
            }

            $index += 1;
        }

        return $rawData;
    }

    public function fetchBreakdown($batch){
        $rawBreakdown = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_sampling")
            ->select("*", DB::raw("0 as delivered"), DB::raw("0 as accepted"))
            ->where("batchTicketNumber", $batch)
            ->get();
        
            $index = 0;
        foreach($rawBreakdown as $row){
            $tbl_deliv = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_delivery")
                ->select("totalBagCount")
                // ->where("batchTicketNumber", $row->batchTicketNumber)
                ->where('is_cancelled', 0)
                ->where("seedTag", $row->seedTag)
                ->first();
                $tbl_actual = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_actual_delivery")
                ->select("totalBagCount")
                ->where('isRejected', 0)
                // ->where("batchTicketNumber", $row->batchTicketNumber)
                ->where("seedTag", $row->seedTag)
                ->first(); 
            $rawBreakdown[$index]->delivered = $tbl_deliv->totalBagCount;
            $rawBreakdown[$index]->accepted = $tbl_actual->totalBagCount;
            $index += 1;
        }

        return $rawBreakdown;
    }

    public function fetchBreakdownDeliv($batch){
        $rawData = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_delivery AS a")
            ->join($GLOBALS["season_prefix"].'rcep_delivery_inspection.tbl_actual_delivery AS b', function ($join) {
                $join->on('a.batchTicketNumber', '=', 'b.batchTicketNumber')
                    ->on('a.seedTag', '=', 'b.seedTag');
            })
            ->select("a.batchTicketNumber as batch", "a.seedTag", "a.totalBagCount as delivered","b.totalBagCount as inspected")
            ->where("a.batchTicketNumber", $batch)
            ->where('a.is_cancelled', 0)
            ->where('b.isRejected', 0)
            ->groupBy("a.seedTag")
            ->get();

        return $rawData;
    }

    public function getStatsProv($prv){
        $masterTable = DB::table($GLOBALS["season_prefix"]."prv_".$prv.".new_released AS a")
            ->join($GLOBALS["season_prefix"]."prv_".$prv.".farmer_information_final AS b", "a.db_ref", "=", "b.db_ref")
            ->select(
                "a.province as prv",
                "a.municipality as mun",
                DB::raw("0 as accepted"),
                DB::raw("0 as transferred"),
                DB::raw("SUM(a.bags_claimed) as claimedBags"),
                DB::raw("SUM(a.claimed_area) as claimedArea"),
                DB::raw("SUM(b.final_claimable) as targetBags"),
                DB::raw("SUM(b.final_area) as targetArea"),
                DB::raw("SUM(CASE WHEN LEFT(a.sex, 1) = 'M' THEN 1 ELSE 0 END) as maleCount"),
                DB::raw("SUM(CASE WHEN LEFT(a.sex, 1) = 'F' THEN 1 ELSE 0 END) as femaleCount"),
                DB::raw("COUNT(new_released_id) as totalBeneficiaries"),
                DB::raw("0 as bepTargetArea"),
                DB::raw("0 as bepClaimedArea"),
                DB::raw("0 as bepTargetBags"),
                DB::raw("0 as bepClaimedBags"),
                DB::raw("0 as bepBenefMale"),
                DB::raw("0 as bepBenefFemale")
            )
            ->where("a.category", "INBRED")
            ->groupBy("a.municipality")
            ->get();

        $counter = 0;
        foreach($masterTable as $row){
            $rawBep = DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_beneficiaries AS a")
                ->join($GLOBALS["season_prefix"]."rcep_paymaya.tbl_claim AS b", "a.paymaya_code", "=", "b.paymaya_code")
                ->select(
                    DB::raw("SUM(a.area) as targetArea"),
                    DB::raw("SUM(a.bags) as targetBags"),
                    DB::raw("SUM(a.area) as claimedArea"),
                    DB::raw("COUNT(b.claimId) as claimedBags")
                )
                ->where("a.province", "LIKE", $row->prv)
                ->where("a.municipality", "LIKE", $row->mun)
                // ->groupBy("a.municipality")
                ->first();

            $bepBenefCount = DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_beneficiaries")
                ->select(
                    DB::raw("SUM(CASE WHEN LEFT(sex, 1) = 'M' THEN 1 ELSE 0 END) as maleCount"),
                    DB::raw("SUM(CASE WHEN LEFT(sex, 1) = 'F' THEN 1 ELSE 0 END) as femaleCount")
                )
                ->where("province", "LIKE", $row->prv)
                ->where("municipality", "LIKE", $row->mun)
                ->first();

            if($rawBep){
                $masterTable[$counter]->bepTargetArea = (int)$rawBep->targetArea;
                $masterTable[$counter]->bepClaimedArea = (int)$rawBep->claimedArea;
                $masterTable[$counter]->bepTargetBags = (int)$rawBep->targetBags;
                $masterTable[$counter]->bepClaimedBags = (int)$rawBep->claimedBags;
                $masterTable[$counter]->bepBenefMale = (int)$bepBenefCount->maleCount;
                $masterTable[$counter]->bepBenefFemale = (int)$bepBenefCount->femaleCount;
            }else{
                $masterTable[$counter]->bepTargetArea = 0;
                $masterTable[$counter]->bepClaimedArea = 0;
                $masterTable[$counter]->bepTargetBags = 0;
                $masterTable[$counter]->bepClaimedBags = 0;
                $masterTable[$counter]->bepBenefMale = 0;
                $masterTable[$counter]->bepBenefFemale = 0;
            }

            $counter += 1;
        }

        return $masterTable;
    }

    public function rlaFinder($lab, $lot){
        $masterTable = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_rla_details")
            ->where("labNo", $lab)
            ->where("lotNo", $lot)
            ->get();

        if($masterTable){
            return json_encode($masterTable[0]);
        }else{
            $possibleMatches = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_rla_details")
            ->select('labNo', 'lotNo')
            ->where("labNo", "LIKE", "%$lab%")
            ->where("lotNo", "LIKE", "%$lot%")
            ->limit(10)
            ->get();

            if($possibleMatches) return $possibleMatches;
            else{
                $lab = substr($lab, 0, 2);
                $lot = substr($lot, 0, 2);
                return DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_rla_details")
                    ->select('labNo', 'lotNo')
                    ->where("labNo", "LIKE", "%$lab%")
                    ->orWhere("lotNo", "LIKE", "%$lot%")
                    ->limit(10)
                    ->get();
            }
        }
    }

    public function rlaTracker($lab, $lot){
        $delivery = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_delivery")
            ->select('batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 'deliveryDate', 'totalBagCount', 'is_cancelled', 'reason', 'dateCreated', 'date_updated')
            ->where('seedTag', $lab."/".$lot)
            ->get();


        $batchCollection = [];
        if($delivery){
            $final_array = [];
    
            // dd($delivery);
            foreach($delivery as $row){
            $actual = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_actual_delivery")
                ->select('batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 'dateCreated', 'totalBagCount', 'isRejected', 'isBuffer', 'is_transferred', 'remarks', 'date_modified', 'qrStart')
                ->where('seedTag', $lab."/".$lot)
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->get();

            array_push($final_array, array(
                "batchTicketNumber" => $row->batchTicketNumber,
                "region" => $row->region,
                "province" => $row->province,
                "municipality" => $row->municipality,
                "dropOffPoint" => $row->dropOffPoint,
                "deliveryCreatedAt" => $row->dateCreated,
                "deliveryUpdatedAt" => $row->date_updated,
                "schedule" => $row->deliveryDate,
                "isCancelled" => (int)$row->is_cancelled,
                "reason" => $row->reason,
                "toBeDelivered" => (int)$row->totalBagCount,
                "hasBeenDelivered" => $actual? (int)$actual[0]->totalBagCount : 0,
                "isRejected" => $actual? (int)$actual[0]->isRejected : 0,
                "remarks" => $actual? $actual[0]->remarks : null,
                "isBuffer" => $actual? (int)$actual[0]->isBuffer : 0,
                "isTransferred" => $actual? (int)$actual[0]->is_transferred : 0,
                "deliveredDateUpdated" => $actual? $actual[0]->date_modified : null,
                "awaiting" => ($row->is_cancelled == 0 && COUNT($actual) == 0)? 1 : 0,
                "isEbinhi" => $actual? ($actual[0]->qrStart > 0? true : false) : false
            ));
            
            array_push($batchCollection, $row->batchTicketNumber);
        }


        $checkForTransfers = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.tbl_actual_delivery")
            ->select('batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 'dateCreated', 'totalBagCount', 'isRejected', 'isBuffer', 'is_transferred', 'remarks', 'date_modified')
            ->where('seedTag', $lab."/".$lot)
            ->where('is_transferred', 1)
            ->whereNotIn('batchTicketNumber', $batchCollection)
            ->get();
        // dd($checkForTransfers);
        
        if($checkForTransfers){
            foreach($checkForTransfers as $row){
                array_push($final_array, array(
                    "batchTicketNumber" => $row->batchTicketNumber,
                    "region" => $row->region,
                    "province" => $row->province,
                    "municipality" => $row->municipality,
                    "dropOffPoint" => $row->dropOffPoint,
                    "deliveryCreatedAt" => $row->dateCreated,
                    "deliveryUpdatedAt" => $row->date_modified,
                    "schedule" => $row->dateCreated,
                    "isCancelled" => null,
                    "reason" => null,
                    "toBeDelivered" => (int)$row->totalBagCount,
                    "hasBeenDelivered" => (int)$row->totalBagCount,
                    "isRejected" => (int)$row->isRejected,
                    "remarks" => $row->remarks,
                    "isBuffer" => (int)$row->isBuffer,
                    "isTransferred" => 1,
                    "deliveredDateUpdated" => $row->date_modified,
                    "awaiting" => 0
                ));
            }
        }

        return $final_array;
        }else{
            return null;
        }

    }

    public function downloadLibPrv(){
        $css_season = "ds2024";
        return DB::table($css_season."_rcep_delivery_inspection.lib_prv")
            ->get();
    }

    public function downloadConvFarmers($prv){
        $css_season = "ds2024";
        return DB::table($css_season."_prv_".$prv.".farmer_information_final")
            ->select(
                "rcef_id",
                "rsbsa_control_no",
                "firstName",
                "midName",
                "lastName",
                "extName",
                "sex",
                "province",
                "municipality"
            )
            ->whereRaw("rcef_id NOT IN (SELECT rcef_id FROM ".$css_season."_rcep_css.conv_response)")
            ->get();
    }

    public function downloadConvFarmersAll($prv){
        $css_season = "ds2024";
        return DB::table($css_season."_prv_".$prv.".farmer_information_final")
            ->select(
                "rcef_id",
                "rsbsa_control_no",
                "firstName",
                "midName",
                "lastName",
                "extName",
                "sex",
                "province",
                "municipality"
            )
            // ->whereRaw("rcef_id NOT IN (SELECT rcef_id FROM ".$css_season."_rcep_css.conv_response)")
            ->get();
    }

    public function downloadBepFarmers(){
        $css_season = "ds2024";
        return DB::table($css_season."_rcep_paymaya.tbl_beneficiaries")
            ->select(
                "paymaya_code as rcef_id",
                "rsbsa_control_no",
                "firstname as firstName",
                "middname as midName",
                "lastname as lastName",
                "extname as extName",
                "sex",
                "province",
                "municipality"
            )
            ->whereRaw(
                "paymaya_code IN (SELECT paymaya_code FROM ".$css_season."_rcep_paymaya.tbl_claim)"
            )
            ->groupBy("paymaya_code")
            ->get();

    }

    public function scanDroIAR($iar, $season){
        $iarCheck = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->where("iar_no", $iar)
            ->get();
        
        if($iarCheck && ($iarCheck[0]->status == 'returned')){
            return array(
                "error" => "Returned by receiver.",
                "status" => "202",
                "data" => $iarCheck[0]
            );
        }else if($iarCheck && ($iarCheck[0]->status == 'accomplished' || $iarCheck[0]->status == 'received' || $iarCheck[0]->status == 'to_prp'  || $iarCheck[0]->status == 'to_prc'  || $iarCheck[0]->status == 'to_pay')){
            return array(
                "error" => "This IAR has already been received and verified. Please reach out to the system administrator if you suspect an error.",
                "status" => "201"
            );
        }else if($iarCheck && $iarCheck[0]->status == 'on_hold'){
            return array(
                "error" => "This IAR is on hold as one of the IAR within the same DV has missing documents.",
                "status" => "201"
            );
        }else if($iarCheck && ($iarCheck[0]->status == 'to_rcv')){
            return array(
                "error" => "IAR is now pending. Please wait for the receiver to verify the attachments.",
                "status" => "201"
            );
        }else{
            $iarLog = DB::table($season."_rcep_delivery_inspection.iar_print_logs")
            ->where('iarCode', $iar)
            ->get();
        
        if($iarLog){
            $batchTicketNumber = $iarLog[0]->batchTicketNumber;
            $batchInfo = DB::table($season."_rcep_delivery_inspection.tbl_actual_delivery")
                ->select("seedTag")
                ->where("batchTicketNumber", $batchTicketNumber)
                ->groupBy("seedTag")
                ->get();
            if($batchInfo){
                return $batchInfo;
            }else{
                return array(
                    "error" => "Delivery not found.",
                    "status" => "404"
                );
            }
        }else{
            return array(
                "error" => "IAR was not found. Please contact the DRO for confirmation.",
                "status" => "404"
            );
        }
        }
    }

    public function iarLogging(Request $request){
        $request = json_decode($request->getContent());
        $user_id = $request->user_id;
        
        unset($request->user_id);

        $iarCheck = DB::table($request->season."_rcep_delivery_inspection.iar_confirmation")
            ->where("iar_no", $request->iar_no)
            ->where("status", 'returned')
            ->get();
        
        DB::table($request->season."_rcep_delivery_inspection.iar_confirmation_logs")
            ->insert([
                "user_id" => $user_id,
                "iar_no" => $request->iar_no,
                "action" => "SENT_TO_RCV",
            ]);
        
        if($iarCheck){
            $new = [];
            if($iarCheck[0]->has_dr == "0" || $iarCheck[0]->has_dr == 0) array_push($new, "Delivery Receipt");
            if($iarCheck[0]->has_iar_signed_copy == "0" || $iarCheck[0]->has_iar_signed_copy == 0) array_push($new, "IAR signed copy");
            if($iarCheck[0]->has_ds == "0" || $iarCheck[0]->has_ds == 0) array_push($new, "Delivery Schedule");
            if($iarCheck[0]->has_sar == "0" || $iarCheck[0]->has_sar == 0) array_push($new, "Seed Acknowledgement Receipt");
            if($iarCheck[0]->has_soa == "0" || $iarCheck[0]->has_soa == 0) array_push($new, "Statement of Account");
            if(strpos($iarCheck[0]->attached_rla, "false")) array_push($new, "Result of Lab Analysis");

            $new = implode(", ", $new);

            $finalArray = array(
                "iar_no" => $request->iar_no,
                "has_dr" => $request->has_dr? 1: 0,
                "has_iar_signed_copy" => $request->has_iar_signed_copy? 1: 0,
                "has_ds" => $request->has_ds? 1: 0,
                "has_sar" => $request->has_sar? 1: 0,
                "has_soa" => $request->has_soa? 1: 0,
                "attached_rla" => json_encode($request->attached_rla),
                "has_form_reject" => $request->has_form_reject? 1: 0,
                "has_form_transfer" => $request->has_form_transfer? 1: 0,
                "status" => 'to_rcv',
                "remarks" => "New attachment(s): $new provided for $request->iar_no"
            );

            $updated = DB::table($request->season."_rcep_delivery_inspection.iar_confirmation")
                ->where("iar_no", $request->iar_no)
                ->update($finalArray);
            
            return json_encode($updated);
        }else{
            $iarLog = DB::table($request->season ."_rcep_delivery_inspection.iar_print_logs")
                ->select("batchTicketNumber")
                ->where("iarCode", $request->iar_no)
                ->first();

            $prv = DB::table($request->season ."_rcep_delivery_inspection.tbl_delivery")
                ->select("province")
                ->where("batchTicketNumber", $iarLog->batchTicketNumber)
                ->first();

            $stationId = DB::table($request->season ."_sdms_db_dev.lib_station")
                ->select("stationID")
                ->where("province", "LIKE", "%".$prv->province."%")
                ->first();

            $currentDateTime = date('Y-m-d H:i:s.uP');
            $finalArray = array(
                "iar_no" => $request->iar_no,
                "has_dr" => $request->has_dr? 1: 0,
                "has_iar_signed_copy" => $request->has_iar_signed_copy? 1: 0,
                "has_ds" => $request->has_ds? 1: 0,
                "has_sar" => $request->has_sar? 1: 0,
                "has_soa" => $request->has_soa? 1: 0,
                "attached_rla" => json_encode($request->attached_rla),
                "has_form_reject" => $request->has_form_reject? 1: 0,
                "has_form_transfer" => $request->has_form_transfer? 1: 0,
                "dro_id" => $stationId->stationID,
                "data_uploaded_on" => $currentDateTime
            );

            $inserted = DB::table($request->season."_rcep_delivery_inspection.iar_confirmation")
                ->insert($finalArray);
                return json_encode($inserted);
        }

        
    }

    public function scanRcvIar($iar, $season){
        $iarCheck = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->where("iar_no", $iar)
            ->get();

        if($iarCheck){
            if($iarCheck[0]->status == 'accomplished' || $iarCheck[0]->status == 'to_prp' || $iarCheck[0]->status == 'to_prc' || $iarCheck[0]->status == 'to_pay' || $iarCheck[0]->status == 'received'){
                return array(
                    "status" => "204",
                    "message" => "This IAR has already been received and verified. Please reach out to the system administrator if you suspect an error."
                );
            }else if($iarCheck[0]->status == 'returned'){
                return array(
                    "status" => "203",
                    "message" => "The scanned IAR has already been returned and is awaiting further action from the DRO."
                );
            }else if($iarCheck[0]->status == 'on_hold'){
                return array(
                    "status" => "203",
                    "message" => "This IAR is on-hold as one of the IAR within the same DV has been returned to the DRO."
                );
            }else{
                return json_encode($iarCheck[0]);
            }
        }else{
            return array(
                "status" => "404",
                "message" => "No pending IARs found, or the DRO has not yet scanned/submitted their documents. Please get in touch with your DRO for additional information."
            );
        }
    }

    public function submitReceiver(Request $request){
        $url = 'https://isd.philrice.gov.ph/ptc_v2/api/_api/send_message';
        $request = json_decode($request->getContent());
        $season = $request->season;
        $toUpdate = $request->id;
        $user_id = $request->user_id;

        unset($request->season);
        unset($request->id);
        unset($request->user_id);

        $request->has_dr = $request->has_dr? 1 : 0;
        $request->has_ds = $request->has_ds? 1 : 0;
        $request->has_form_reject = $request->has_form_reject? 1 : 0;
        $request->has_form_transfer = $request->has_form_transfer? 1 : 0;
        $request->has_iar_signed_copy = $request->has_iar_signed_copy? 1 : 0;
        $request->has_sar = $request->has_sar? 1 : 0;
        $request->has_soa = $request->has_soa? 1 : 0;
        $request->attached_rla = json_encode($request->attached_rla);

        if($request->status_before_hold){
            unset($request->remarks);
        }

        if($request->status == "returned"){
            // $notifSettings = DB::table()
            $request = json_decode(json_encode($request), true);
            $updated = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                ->where("id", $toUpdate)
                ->update($request);

                
            $tempData = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                ->select(
                    "iar_confirmation.iar_no as iar_no",
                    "lib_dro.firstName as dro_fname",
                    "lib_dro.contactNo as dro_contact",
                    "lib_dro.email as dro_email"
                    )
                    ->join($season."_rcep_delivery_inspection.lib_dro", "iar_confirmation.dro_id", "=", "lib_dro.stationId")
                    ->where("iar_confirmation.id", $toUpdate)
                    ->first();
                    
            DB::table($season."_rcep_delivery_inspection.iar_confirmation_logs")
                ->insert([
                    "user_id" => $user_id,
                    "iar_no" => $tempData->iar_no,
                    "action" => "RTND_FR_RCV"
                ]);
            $smsSettings = DB::table($season.'_rcep_delivery_inspection.iar_notifications')
                ->select("status")
                ->where("notification", "sms")
                ->first();
            
            $emailSettings = DB::table($season.'_rcep_delivery_inspection.iar_notifications')
                ->select("status")
                ->where("notification", "email")
                ->first();

            if($smsSettings->status == 1){
                $data = [
                    'token' => 'cb74aa5ba38deaa9a2211f5d1394a9a7',
                    'mobile' => $tempData->dro_contact,
                    'message' => "Hello, ".$tempData->dro_fname.". IAR: ".$tempData->iar_no." has missing/invalid attachment(s). It's on hold until provided. Thank you!"
                ];
                
                
                $ch = curl_init($url);
    
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                
                $response = curl_exec($ch);
                
                if ($response === false) {
                    echo 'cURL error: ' . curl_error($ch);
                }
                
                curl_close($ch);
            }

            if($emailSettings->status == 1){
                $recipientEmail = $tempData->dro_email;
                $message = 'Good day.
                
                This message is to inform you that the IAR: '.$tempData->iar_no.' has one (1) or more missing/invalid attachment(s) and is now on-hold until the missing/invalid attachment(s) has been provided. Thank you and have a nice day!';
                $messageData = nl2br($message, false) ;
                $datenow = date("Y-m-d H:i:s");

                $data = [
                    'messageData' => $messageData,
                    'dateNow' => $datenow,
                ];

                // Store the original mail configuration
                $originalConfig = config('mail');

                // Set temporary mail configuration for this email
                config([
                    'mail.from.address' => 'rcefseeds.mailer@gmail.com',
                    'mail.from.name' => 'RCEF Seeds',
                    'mail.username' => 'rcefseeds.mailer@gmail.com',
                    'mail.password' => 'gmco rpvn hkbg wjeu',
                ]);

                $transport = (new Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption')))
                    ->setUsername(config('mail.username'))
                    ->setPassword(config('mail.password'));


                
                // Create a Swift_Mailer instance with the custom transport
                $mailer = new Swift_Mailer($transport);

                
                // Create a Swift_Message instance
                $swiftMessage = (new Swift_Message('Reminder regarding IAR No. '.$tempData->iar_no))
                    ->setFrom([config('mail.from.address') => config('mail.from.name')])
                    ->setTo([$recipientEmail])
                    ->setBody($data['messageData'])
                    ->setContentType('text/html');
                try {
                    // Send the email using the custom Swift_Mailer
                    $mailer->send($swiftMessage);

                    $status = 'sent';
                } catch (\Exception $e) {
                    // Log the detailed error message
                    $status = 'error: ' . $e->getMessage();
                }

                // Restore the original mail configuration
                config(['mail' => $originalConfig]);
            }

            return array(
                "status" => 200,
                "message" => "Returned to DRO.",
                "adtnl_info" => "SMS set: ". $smsSettings->status ."; Email set: ". $emailSettings->status
            );
        }

        $iar_check = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->select("status_before_hold")
            ->where("id", $toUpdate)
            ->first();
    
        if($iar_check->status_before_hold != null){
            $request->status = "on_hold";
        }
        $request = json_decode(json_encode($request), true);
        $updated = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->where("id", $toUpdate)
            ->update($request);

            
        $iarUpdated = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->select("iar_no")
            ->where("id", $toUpdate)
            ->first();

        DB::table($season."_rcep_delivery_inspection.iar_confirmation_logs")
                ->insert([
                    "user_id" => $user_id,
                    "iar_no" => $iarUpdated->iar_no,
                    "action" => "RCVD_FR_DRO"
                ]);
            
        $getDVNo = DB::table($season."_rcep_delivery_inspection.iar_particulars")
            ->select("dv_control_number")
            ->where("iar_number", $iarUpdated->iar_no)
            ->first();

        // if(!$getDVNo){
        //     return array(
        //         "status" => 502,
        //         "message" => "IAR No.: $iarUpdated->iar_no has no particulars yet."
        //     );
        // }

        $DVCollection = DB::table($season."_rcep_delivery_inspection.iar_particulars")
            ->select("iar_number")
            ->where("dv_control_number", $getDVNo? $getDVNo->dv_control_number : "0000-00-000000")
            ->get();

        
        $score = 0;
        foreach($DVCollection as $row){
            $iar_checker = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                ->where("iar_no", $row->iar_number)
                ->first();

                if(!$iar_checker){
                    return array(
                        "status" => 502,
                        "message" => "IAR no.: ".$row->iar_number." was not found."
                    );
                }
                
                if($iar_checker->status == "returned") $score+= 1;
            }
        if($score < 1){
            foreach($DVCollection as $row){
                $iar_check = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                    ->select("status_before_hold")
                    ->where("iar_no", $row->iar_number)
                    ->first();

                if($iar_check->status_before_hold != null){
                    $iar_check2 = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                        ->where("iar_no", $row->iar_number)
                        ->update(["status" => $iar_check->status_before_hold, "status_before_hold" => null]);
                }else{
                    return array(
                        "status" => 200,
                        "message" => "Tagged one IAR inside DV."
                    );
                }
            }

            return array(
                "status" => 200,
                "message" => "Successfully tagged whole DV."
            );
        }else{
            if($score < 1){
                foreach($DVCollection as $row){
                    $iar_check = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                        ->select("status_before_hold")
                        ->where("iar_no", $row->iar_number)
                        ->first();
                    
                    $iar_check2 = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                        ->where("iar_no", $row->iar_number)
                        ->update(["status" => $iar_check->status_before_hold, "status_before_hold" => null]);
                }
            }

            return array(
                "status" => 200,
                "message" => "Tagged one IAR inside DV."
            );
        }

    }

    public function scanPrpIar($iar, $season){
        $iarCheck = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->where("iar_no", $iar)
            ->first();
        
        if($iarCheck){
            if($iarCheck->status == 'to_prp'){
                $dv = DB::table($season."_rcep_delivery_inspection.iar_particulars")
                    ->where("iar_number", $iar)
                    ->first();
                
                if($dv){
                    $particulars = DB::table($season."_rcep_delivery_inspection.iar_particulars")
                        ->select(
                            "*",
                            DB::raw("NULL as attachments")
                        )
                        ->where("dv_control_number", $dv->dv_control_number)
                        ->get();
                    $counter = 0;
                    foreach($particulars as $row){
                        $temp_iar_attachments = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                            ->where("iar_no", $row->iar_number)
                            ->first();
                        
                        if($temp_iar_attachments){
                            $particulars[$counter]->attachments = $temp_iar_attachments;
                        }
                        $counter++;
                    }

                    return array(
                        "status" => 200,
                        "data" => $particulars
                    );
                }else{
                    return array(
                        "status" => 404,
                        "message" => "IAR hasn't been prepared by the Preparer yet. Please contact the Preparer and/or try again later."
                    );
                }
            }else{
                $message = "";
                switch ($iarCheck->status){
                    case "on_hold":
                        $message = "This IAR is on-hold at the moment. Please make sure all the IARs in this DV are prepared correctly.";
                        break;
                    case "received":
                        $message = "This IAR has been received but has no particulars/DV yet.";
                        break;
                    case "returned":
                        $message = "This IAR has already been returned and is now awaiting further action from the DRO.";
                        break;
                    case "accomplished":
                        $message = "This IAR has already been completed. If you feel like this is an error, please contact the System Administrator.";
                        break;
                    case "to_rcv":
                        $message = "This IAR hasn't been received by the Receiver yet. Please wait for the Receiver to receive the IAR and its attachments.";
                        break;
                    case "to_prc":
                        $message = "The IAR you scanned has already been submitted to the Processor.";
                        break;
                    case "to_pay":
                        $message = "The IAR you scanned has already been submitted to the Cashier.";
                        break;
                    default: 
                        $message = "Status unknown '".$iarCheck->status."'. Please contact the System Administrator.";
                }
    
                return array(
                    "status" => 9,
                    "message" => $message
                );
            }
        }else{
            return array(
                "status" => 404,
                "message" => "IAR was not found. Please contact the DRO for confirmation."
            );
        }
    }

    public function scanPrcIar($iar, $season){
        $iarCheck = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
            ->where("iar_no", $iar)
            ->first();
        
        if($iarCheck){
            if($iarCheck->status == 'to_prc'){
                $dv = DB::table($season."_rcep_delivery_inspection.iar_particulars")
                    ->where("iar_number", $iar)
                    ->first();
                
                if($dv){
                    $particulars = DB::table($season."_rcep_delivery_inspection.iar_particulars")
                        ->select(
                            "*",
                            DB::raw("NULL as attachments")
                        )
                        ->where("dv_control_number", $dv->dv_control_number)
                        ->get();
                    $counter = 0;
                    foreach($particulars as $row){
                        $temp_iar_attachments = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                            ->where("iar_no", $row->iar_number)
                            ->first();
                        
                        if($temp_iar_attachments){
                            $particulars[$counter]->attachments = $temp_iar_attachments;
                        }
                        $counter++;
                    }

                    return array(
                        "status" => 200,
                        "data" => $particulars
                    );
                }else{
                    return array(
                        "status" => 404,
                        "message" => "IAR hasn't been prepared by the Preparer yet. Please contact the Preparer and/or try again later."
                    );
                }
            }else{
                $message = "";
                switch ($iarCheck->status){
                    case "on_hold":
                        $message = "This IAR is on-hold at the moment. Please make sure all the IARs in this DV are prepared correctly.";
                        break;
                    case "received":
                        $message = "This IAR has been received but has no particulars/DV yet.";
                        break;
                    case "returned":
                        $message = "This IAR has already been returned and is now awaiting further action from the DRO.";
                        break;
                    case "accomplished":
                        $message = "This IAR has already been completed. If you feel like this is an error, please contact the system administrator.";
                        break;
                    case "to_rcv":
                        $message = "This IAR hasn't been received by the Receiver yet. Please wait for the Receiver to receive the IAR and its attachments.";
                        break;
                    case "to_prp":
                        $message = "The IAR you scanned hasn't been prepared yet.";
                        break;
                    case "to_pay":
                        $message = "The IAR you scanned has already been submitted to the cashier.";
                        break;
                    default: 
                        $message = "Status unknown '".$iarCheck->status."'. Please contact the system administrator.";
                }
    
                return array(
                    "status" => 9,
                    "message" => $message
                );
            }
        }else{
            return array(
                "status" => 404,
                "message" => "IAR was not found. Please contact the DRO for confirmation."
            );
        }
    }

    public function submitPrcCsh(Request $request){
        try{
            $request = json_decode($request->getContent());
            $season = $request[0]->season;
            $user_id = $request[0]->user_id;

            $counter = 0;
            $url = 'https://isd.philrice.gov.ph/ptc_v2/api/_api/send_message';
            $to_send = [];

            foreach($request as $row){
                unset($request[$counter]->season);
                unset($request[$counter]->user_id);

                $logStatus = "";
                if($row->status == "on_hold") {
                    if($row->status_before_hold == "to_prp"){
                        $logStatus = "HOLD_ON_PRP";
                    }else{
                        $logStatus = "HOLD_ON_PRC";
                    }
                }else if($row->status == "returned"){
                    if($row->status_before_hold == "to_prp"){
                        $logStatus = "RTND_FR_PRP";
                    }else{
                        $logStatus = "RTND_FR_PRC";
                    }
                }else{
                    if($row->status == "to_prc"){
                        $logStatus = "SENT_TO_PRC";
                    }else{
                        $logStatus = "SENT_TO_CSH";
                    }
                }

                DB::table($season."_rcep_delivery_inspection.iar_confirmation_logs")
                ->insert([
                    "user_id" => $user_id,
                    "iar_no" => $row->iar_no,
                    "action" => $logStatus
                ]);

                DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                    ->where("id", $row->id)
                    ->where("iar_no", $row->iar_no)
                    ->update([
                        "has_dr" => $row->has_dr,
                        "has_iar_signed_copy" => $row->has_iar_signed_copy,
                        "has_ds" => $row->has_ds,
                        "has_sar" => $row->has_sar,
                        "has_soa" => $row->has_soa,
                        "attached_rla" => $row->attached_rla,
                        "has_form_reject" => $row->has_form_reject,
                        "has_form_transfer" => $row->has_form_transfer,
                        "status" => $row->status,
                        "status_before_hold" => $row->status_before_hold,
                        "remarks" => null
                    ]);
                if($row->status == "returned"){
                    $tempData = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                        ->select(
                            "iar_confirmation.iar_no as iar_no",
                            "lib_dro.firstName as dro_fname",
                            "lib_dro.contactNo as dro_contact",
                            "lib_dro.email as dro_email"
                        )
                        ->join($season."_rcep_delivery_inspection.lib_dro", "iar_confirmation.dro_id", "=", "lib_dro.stationId")
                        ->where("iar_confirmation.id", $row->id)
                        ->where("iar_confirmation.iar_no", $row->iar_no)
                        ->first();
                    
                    array_push($to_send, array(
                        "iar_no" => $tempData->iar_no,
                        "dro_fname" => $tempData->dro_fname,
                        "dro_contact" => $tempData->dro_contact,
                        "dro_email" => $tempData->dro_email
                    ));
                }

                $counter += 1;
            }
                $smsSettings = DB::table($season.'_rcep_delivery_inspection.iar_notifications')
                    ->select("status")
                    ->where("notification", "sms")
                    ->first();

                $emailSettings = DB::table($season.'_rcep_delivery_inspection.iar_notifications')
                    ->select("status")
                    ->where("notification", "email")
                    ->first();

                if($to_send && $smsSettings->status == 1){

                    $missing_iars = [];
                    foreach($to_send as $row){
                        array_push($missing_iars, $row["iar_no"]);
                    }

                    $collected_iar = implode(", ", $missing_iars);

                    $data = [
                        'token' => 'cb74aa5ba38deaa9a2211f5d1394a9a7',
                        'mobile' => $to_send[0]["dro_contact"],
                        'message' => "Good day, Mr./Mrs. ".$to_send[0]["dro_fname"].". This message is to inform you that the IAR: ".$collected_iar." has one (1) or more missing/invalid attachment(s) and is now on-hold until the missing/invalid attachment(s) has been provided. Thank you and have a nice day!"
                    ];
                    
                    
                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    
                    $response = curl_exec($ch);
                    
                    if ($response === false) {
                        echo 'cURL error: ' . curl_error($ch);
                    }
                    
                    curl_close($ch);
                }
                if($emailSettings->status == 1){
                    foreach($request as $row){
                        if($row->status == "returned"){
                            $tempData = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                                ->select(
                                    "iar_confirmation.iar_no as iar_no",
                                    "lib_dro.firstName as dro_fname",
                                    "lib_dro.contactNo as dro_contact",
                                    "lib_dro.email as dro_email"
                                )
                                ->join($season."_rcep_delivery_inspection.lib_dro", "iar_confirmation.dro_id", "=", "lib_dro.stationId")
                                ->where("iar_confirmation.id", $row->id)
                                ->where("iar_confirmation.iar_no", $row->iar_no)
                                ->first();
                            
                            array_push($to_send, array(
                                "iar_no" => $tempData->iar_no,
                                "dro_fname" => $tempData->dro_fname,
                                "dro_contact" => $tempData->dro_contact,
                                "dro_email" => $tempData->dro_email
                            ));
                        }
                    }
                    $missing_iars = [];
                    foreach($to_send as $row){
                        array_push($missing_iars, $row["iar_no"]);
                    }

                    $collected_iar = implode(", ", $missing_iars);

                    if($to_send){
                        $recipientEmail = $to_send[0]["dro_email"];
                    }else{
                        return array(
                            "status" => 200,
                            "message" => "Successfully updated tagged IARs in DV.",
                            "adtnl_info" => "SMS set: ". $smsSettings->status ."; Email set: ". $emailSettings->status
                        );
                    }
                    $message = 'Good day.
                    
                    This message is to inform you that the IAR(s): '.$collected_iar.' has one (1) or more missing/invalid attachment(s) and is now on-hold until the missing/invalid attachment(s) has been provided. Thank you and have a nice day!';
                    $messageData = nl2br($message, false) ;
                    $datenow = date("Y-m-d H:i:s");

                    $data = [
                        'messageData' => $messageData,
                        'dateNow' => $datenow,
                    ];

                    // Store the original mail configuration
                    $originalConfig = config('mail');

                    // Set temporary mail configuration for this email
                    config([
                        'mail.from.address' => 'rcefseeds.mailer@gmail.com',
                        'mail.from.name' => 'RCEF Seeds',
                        'mail.username' => 'rcefseeds.mailer@gmail.com',
                        'mail.password' => 'gmco rpvn hkbg wjeu',
                    ]);

                    $transport = (new Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption')))
                        ->setUsername(config('mail.username'))
                        ->setPassword(config('mail.password'));


                    
                    // Create a Swift_Mailer instance with the custom transport
                    $mailer = new Swift_Mailer($transport);

                    
                    // Create a Swift_Message instance
                    $swiftMessage = (new Swift_Message('Reminder regarding IAR(s). '.$collected_iar))
                        ->setFrom([config('mail.from.address') => config('mail.from.name')])
                        ->setTo([$recipientEmail])
                        ->setBody($data['messageData'])
                        ->setContentType('text/html');
                    try {
                        // Send the email using the custom Swift_Mailer
                        $mailer->send($swiftMessage);

                        $status = 'sent';
                    } catch (\Exception $e) {
                        // Log the detailed error message
                        $status = 'error: ' . $e->getMessage();
                    }

                    // Restore the original mail configuration
                    config(['mail' => $originalConfig]);
                }

            return array(
                "status" => 200,
                "message" => "Successfully updated tagged IARs in DV.",
                "adtnl_info" => "SMS set: ". $smsSettings->status ."; Email set: ". $emailSettings->status
            );
        }catch(\Exception $e){
            return array(
                "status" => 500,
                "message" => $e->getMessage()
            );
        }
    }

    public function scanCashier($iar, $season){
        try{
            $iarStatus = DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                ->where("iar_no", $iar)
                ->first();

            if($iarStatus && $iarStatus->status == "to_pay"){
                $dv = DB::table($season."_rcep_delivery_inspection.iar_particulars")
                    ->select("dv_control_number")
                    ->where("iar_number", $iar)
                    ->first();
                
                return array(
                    "status" => 200,
                    "data" => $dv->dv_control_number
                );
            }else if($iarStatus && $iarStatus->status != "to_pay"){
                $message = "";
                switch($iarStatus->status){
                    case "accomplished":
                        $message = "DV has already been tagged as paid.";
                        break;
                    case "to_rcv":
                    case "received":
                    case "to_prp":
                    case "to_prc":
                        $message = "This IAR/DV has not passed the processing (processor) stage.";
                        break;
                    case "returned":
                        $message = "This IAR has been returned to the DRO at the moment. Please try again soon.";
                        break;
                    case "on_hold":
                        $message = "This IAR is currently on-hold along with the IARs under the same DV.";
                        break;
                    default:
                        $message = "Unknown status ".$iarStatus->status;
                    }
                    return array(
                        "status" => 201,
                        "message" => $message
                    );
            }else{
                return array(
                    "status" => 201,
                    "message" => "The IAR/DV was not found."
                );
            }
        }catch(\Exception $e){
            return array(
                "status" => 500,
                "message" => $e->getMessage()
            );
        }
    }

    public function submitCashier($dvCtrl, $season, $orNo, $user_id){
        try{
            $iars_to_tag = DB::table($season."_rcep_delivery_inspection.iar_particulars")
                ->select("iar_number")
                ->where("dv_control_number", $dvCtrl)
                ->get();

            if($iars_to_tag){
                foreach ($iars_to_tag as $row) {
                    DB::table($season."_rcep_delivery_inspection.iar_confirmation")
                        ->where("iar_no", $row->iar_number)
                        ->update(["status" => "accomplished"]);
                    
                    DB::table($season."_rcep_delivery_inspection.iar_confirmation_logs")
                        ->insert([
                            "user_id" => $user_id,
                            "iar_no" => $row->iar_number,
                            "action" => "PAID_FR_CSH"
                        ]);
                }
    
                DB::table($season."_rcep_delivery_inspection.iar_particulars")
                    ->where("dv_control_number", $dvCtrl)
                    ->update(["isPaid" => "1", "or_number" => $orNo]);
                
                return array(
                    "status" => 200,
                    "message" => "Successfully updated DV."
                );
            }else{
                return array(
                    "status" => 404,
                    "message" => "There was no IAR to tag. Please contact system administrator for more information."
                );
            }

            DB::table($season."_rcep_delivery_inspection.iar_particulars")
                ->where("dv_control_number", $dvCtrl)
                ->update(["paymentStatus" => "Paid"]);

        }catch(\Exception $e){
            return array(
                "status" => 500,
                "message" => $e->getMessage()
            );
        }
    }

    public function getAlertSettings(){
        $season = "ds2024";
        $settings = DB::table($season.'_rcep_delivery_inspection.iar_notifications')
            ->get();

        return array(
            "sms" => $settings[1]->status == 1? true : false,
            "email" => $settings[0]->status == 1? true : false,
            "smsDisabled" => $settings[1]->isActive == 0? true : false,
            "emailDisabled" => $settings[0]->isActive == 0? true : false
        );
    }
}

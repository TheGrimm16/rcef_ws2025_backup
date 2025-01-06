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

class rio_custom_api extends Controller
{
    public function index(){

        $getPrvsUnc = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('province')
            ->groupBy('province')
            ->get();
        $prvArr = array();

        foreach($getPrvsUnc as $row){
            array_push($prvArr, $row->province);
        }


        // dd($getPrvsUnsched);
        return view('customExports.customExportUI', compact(
            'prvArr'
        ));
    }

    public function getMun(Request $request){
        $mun_list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('municipality')
            ->where('province', $request->prv)
            ->groupBy('municipality')
            ->get();
        $mun_arr = array();
        foreach($mun_list as $row){
            array_push($mun_arr, $row->municipality);
        }
        return $mun_arr;
    }

    public function getPrvUnsched(){
        $getPrvsUnsched = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', 'sed_verified.region', '=', 'lib_prv.regCode')
            ->select('lib_prv.regionName as region', 'lib_prv.regCode as regCode')
            ->where('sed_verified.isActive', 1)
            ->where('sed_verified.isScheduled', 1)
            ->groupBy('region')
            ->get();

        // $prvUnschArr = array();
        // foreach($getPrvsUnsched as $row){
        //     array_push($prvUnschArr, $row->region);
        // }

        return $getPrvsUnsched;
    }

    public function getMunUnsched(Request $request){
        $munUnschedRaw = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('province_name as prv')
            ->where('region', $request->prv)
            ->groupBy('province_name')
            ->get();

        $munArr = array();
        foreach($munUnschedRaw as $row){
            array_push($munArr, $row->prv);
        }

        return $munArr;
    }

    public function getMunLevelUnsched(Request $request){
        $munRaw = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('municipality_name as mun')
            ->where('region', $request->reg)
            ->where('province_name', $request->prv)
            ->groupBy('municipality_name')
            ->get();
        $muniArr = array();
        foreach($munRaw as $row){
            array_push($muniArr, $row->mun);
        }

        return $muniArr;
    }

    public function unschedExport(Request $request){

        try{
            $unschedBenefRaw = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where('region', $request->reg)
            ->where('farm_addr_prv', $request->prv)
            ->where('farm_addr_mun', $request->mun)
            ->where('isScheduled', 0)
            ->get();
        
        $unschedBen = array();
        $master_list = array();

        foreach($unschedBenefRaw as $row){
            array_push($unschedBen, array(
                "RCEF ID" => $row->rcef_id,
                "RSBSA Control No." => $row->rsbsa_control_number,
                "First Name" => $row->fname,
                "Middle Name" => $row->midname,
                "Last Name" => $row->lname,
                "Name Suffix" => $row->extename,
                "Tel. No." => $row->contact_no,
                "Province" => $row->province_name,
                "Municipality" => $row->municipality_name,
                "Committed Area" => $row->committed_area,
                "Declared Area" => $row->farmer_declared_area,
                "Previous Harvested Bags (bags)" => $row->yield_no_bags,
                "Previous Weight Per Bag (kg)" => $row->yield_weight_bags,
                "Previous Harvested Area (ha)" => $row->yield_area,
                "Previous Season Yield (T/ha)" => $row->yield,
                "Pre-registered? (0/1)" => $row->isPrereg
            ));
        }

        // dd($unschedBen);
        
        array_push($master_list, $unschedBen);

            $new_collection = collect(); 
            foreach($master_list as $list_collection_row){
                $new_collection = $new_collection->merge($list_collection_row);
            }

            $excel_data = json_decode(json_encode($new_collection), true);

            return Excel::create("UNSC_$request->prv"."_".$request->mun."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Unscheduled List", function($sheet) use ($excel_data) {          
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:P1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:P1', 'thin');
                });
            })->download('xlsx');
        }catch(\Exception $e){
            return $e;
        }
    }


    public function getUnclaimedBenef(Request $request){
        //BEGIN DOING THINGS
        try{
            $unclaimed_benef = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim', 'tbl_beneficiaries.paymaya_code', '=', 'tbl_claim.paymaya_code')
                ->select('tbl_beneficiaries.*')
                ->where('tbl_beneficiaries.province', $request->prv)
                ->where('tbl_beneficiaries.municipality', $request->mun)
                ->where(DB::raw('tbl_claim.paymaya_code'))
                // ->limit(5)
                ->groupBy('paymaya_code')
                ->get();


            $master_list = array();
            $data = array();

            foreach($unclaimed_benef as $row){
                array_push($data, array(
                    "RCEF ID" => $row->paymaya_code,
                    "Contact Number" => $row->contact_no,
                    "RSBSA Control No." => $row->rsbsa_control_no,
                    "First Name" => $row->firstname,
                    "Middle Name" => $row->middname,
                    "Last Name" => $row->lastname,
                    "Extension Name" => $row->extname,
                    "Sex" => $row->sex,
                    "Schedule Start" => "$row->schedule_start",
                    "Schedule End" => "$row->schedule_end",
                    "Province" => $row->province,
                    "Municipality" => $row->municipality,
                    "DOP" => $row->drop_off_point,
                    "Area" => $row->area,
                    "Bags" => $row->bags
                ));
            }
            array_push($master_list, $data);

            $new_collection = collect(); 
            foreach($master_list as $list_collection_row){
                $new_collection = $new_collection->merge($list_collection_row);
            }

            $excel_data = json_decode(json_encode($new_collection), true);

            return Excel::create("UNCL_$request->prv"."_".$request->mun."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Unclaimed List", function($sheet) use ($excel_data) {          
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:O1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:O1', 'thin');
                });
            })->download('xlsx');
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getScheduledProvinces(){
        $prvs = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('province')
            ->groupBy('province')
            ->get();
        
        $provinces = array();
        foreach($prvs as $row){
            array_push($provinces, $row->province);
        }

        return $provinces;
    }

    public function getScheduledMunicipalities(Request $request){
        $muns = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('municipality')
            ->where('province', $request->prv)
            ->groupBy('municipality')
            ->get();
        
        $municipalities = array();
        foreach($muns as $row){
            array_push($municipalities, $row->municipality);
        }

        return $municipalities;
    }

    public function getScheduled(Request $request){
        //BEGIN DOING THINGS
        try{
            $schedBenef = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                ->where('tbl_beneficiaries.province', $request->prv)
                ->where('tbl_beneficiaries.municipality', $request->mun)
                ->get();


            $master_list = array();
            $data = array();

            foreach($schedBenef as $row){
                array_push($data, array(
                    "RCEF ID" => $row->paymaya_code,
                    "Contact Number" => $row->contact_no,
                    "RSBSA Control No." => $row->rsbsa_control_no,
                    "First Name" => $row->firstname,
                    "Middle Name" => $row->middname,
                    "Last Name" => $row->lastname,
                    "Extension Name" => $row->extname,
                    "Sex" => $row->sex,
                    "Schedule Start" => "$row->schedule_start",
                    "Schedule End" => "$row->schedule_end",
                    "Province" => $row->province,
                    "Municipality" => $row->municipality,
                    "DOP" => $row->drop_off_point,
                    "Area" => $row->area,
                    "Bags" => $row->bags
                ));
            }
            array_push($master_list, $data);

            $new_collection = collect(); 
            foreach($master_list as $list_collection_row){
                $new_collection = $new_collection->merge($list_collection_row);
            }

            $excel_data = json_decode(json_encode($new_collection), true);

            return Excel::create("SCHED_$request->prv"."_".$request->mun."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Beneficiary List", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:O1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:O1', 'thin');
                });
            })->download('xlsx');
        }catch(\Exception $e){
            return $e;
        }
    }

    public function secureLogin($user, $pass, $season){
        // return array(
        //     "user" => $user,
        //     "pass" => $pass,
        //     "season" => $season
        // );

        //do decoding
        $decoded_user = "$user";
        $decoded_pass = "$pass";
        //end decoding

        $user_account = DB::table($season."_sdms_db_dev.users")
            ->join($season."_sdms_db_dev.role_user", "users.userId", "=", "role_user.userId")
            ->join($season."_sdms_db_dev.roles", "role_user.roleId", "=", "roles.roleId")
            ->where("username", $decoded_user)
            ->first();
        
        if(!$user_account){
            return array(
                "code" => 404,
                "message" => "Users not found."
            );
        }

        if(Hash::check($decoded_pass, $user_account->password)){
            return array(
                "code" => 200,
                "message" => "Account verified.",
                "payload" => array(
                    "username" => $user_account->username,
                    "firstName" => $user_account->firstName,
                    "middleName" => $user_account->middleName,
                    "lastName" => $user_account->lastName,
                    "extName" => $user_account->extName,
                    "stationId" => $user_account->stationId,
                    "role" => $user_account->name,
                    "role_name" => $user_account->display_name
                )
            );
        }else{
            return array(
                "code" => 201,
                "message" => "Username & password combination mismatch."
            );
        }
    }

    public function parsePrv($prv){
        $prv = str_replace("-", "", $prv);
        $prv = substr($prv, 0, 6);
        $offload = DB::table($GLOBALS["season_prefix"]."rcep_delivery_inspection.lib_prv")
            ->select(
                "region",
                "regionName",
                "province",
                "municipality",
                "psa_code"
            )
            ->where("prv", $prv)
            ->first();

        if($offload){
            return array(
                "status" => 200,
                "message" => "Address found",
                "data" => $offload
            );
        }else{
            return array(
                "status" => 404,
                "message" => "Address not found",
                "data" => null
            );
        }
    }

    public function getVarietyPerformance($season){
        try{
            $excludeTables = ["ws2023_prv_9999", "ws2023_prv_temp", "ds2023_prv_9999", "ds2023_prv_temp", "ds2024_prv_9999", "ds2024_prv_temp"];
        $tables = DB::table("information_schema.tables")
            ->select(
                "TABLE_SCHEMA"
            )
            ->where("TABLE_SCHEMA", "LIKE", $season."_prv_%")
            ->whereRaw("LENGTH(TABLE_SCHEMA) = 15")
            ->where("TABLE_NAME", "LIKE", "%release%")
            ->where("TABLE_ROWS", ">", 0)
            ->whereNotIn("TABLE_SCHEMA", $excludeTables)
            ->groupBy("TABLE_SCHEMA")
            // ->limit(2)
            ->get();
        $tables_array = [];

        foreach ($tables as $key) {
            array_push($tables_array, $key->TABLE_SCHEMA);
        }


        $provinces = [
            "01" => "ILOCOS",
            "02" => "CAGAYAN VALLEY",
            "03" => "CENTRAL LUZON",
            "04" => "CALABARZON",
            "05" => "BICOL",
            "06" => "WESTERN VISAYAS",
            "07" => "CENTRAL VISAYAS",
            "08" => "EASTERN VISAYAS",
            "09" => "ZAMBOANGA PENINSULA",
            "10" => "NORTHERN MINDANAO",
            "11" => "DAVAO",
            "12" => "SOCCSKSARGEN",
            "14" => "CAR",
            "15" => "BARMM",
            "16" => "CARAGA",
            "17" => "MIMAROPA"
        ];
        $query = [];

        foreach($tables_array as $key){
            $prv = substr($key, 11, 2);
            array_push($query, "SELECT '".$provinces[$prv]."' as 'region', province, municipality, seed_variety, SUM(claimed_area) as 'claimed_area' FROM ".$key.".new_released GROUP BY municipality, seed_variety");
        }
        $query = implode(" UNION ALL ", $query);

        $res = DB::select(DB::raw($query));
        
        $master_list = array();
        $data = array();

        foreach($res as $row){
            array_push($data, array(
                "Region" => $row->region,
                "Province" => $row->province,
                "Municipality" => $row->municipality,
                "Seed Variety" => $row->seed_variety,
                "Area Planted" => $row->claimed_area
            ));
        }

        array_push($master_list, $data);

        $new_collection = collect();
        foreach($master_list as $list_collection_row){
            $new_collection = $new_collection->merge($list_collection_row);
        }

        $excel_data = json_decode(json_encode($new_collection), true);

        return Excel::create($season."_SEED_VARIETY_PERFORMANCE_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Variety per Municipality", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:E1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:E1', 'thin');
                });
            })->download('xlsx');
        }catch(\Exception $e){
            return $e;
        }
    }
}

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

    public function province_list(Request $request){
         $province_list = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->where("add_region", $request->add_region)
            ->groupBy("add_province")
            ->get();

        return json_encode($province_list);

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
            ->groupBy("add_province")
            ->count("add_province");

        $total_municipality = DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.farmer_info")
            ->groupBy("add_municipality")
            ->count("add_municipality");
        $total_area =  DB::table($GLOBALS['season_prefix']."rcep_palaysikatan.crop_production")
            ->groupBy("farmer_id_fk")
            ->sum("techno_area");
        return view("palaysikatan.dashboard_palaysikatan")
            ->with("total_farmer", $total_farmer)   
            ->with("total_province", $total_province)   
            ->with("total_municipality", $total_municipality)  
            ->with("total_area", $total_area)
            ->with("region_list", $region_list);
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
                        ->groupBy("farmer_info.add_municipality")
                        ->count("farmer_info.add_municipality");

                    array_push($tbl_array, array(
                        "crop_establishment" => $crop->crop_establishment,
                        "no_municipality" => $sites,
                        "area"=> $crop->sum_sites,
                    ));
                }
                $data_arr = collect($tbl_array);
                return Datatables::of($data_arr)
                ->make(true);
    }    




}

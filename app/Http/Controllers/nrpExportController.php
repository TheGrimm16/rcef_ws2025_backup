<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Excel;
class nrpExportController extends Controller
{
    
    public function nrpDistriIndex(){
        $province = DB::table($GLOBALS['season_prefix']."rcep_reports_view.rcef_nrp_provinces")
            ->orderBy("region_sort")
            ->get();
            // dd($province);
        return view("nrp.export_index")
            ->with('province', $province);

    }


    public function municipality_list(Request $request){
        $municipality_list = DB::connection("delivery_inspection_db")->table("lib_prv")
                ->where("prv_code", $request->provCode)
                ->orderBy("municipality")
                ->get();
        if(count($municipality_list) <= 0){
            $municipality_list = DB::connection("delivery_inspection_db")->table("lib_prv")
            ->where("province", $request->provCode)
            ->orderBy("municipality")
            ->get();
        }


            return json_encode($municipality_list);
    }

    public function nrpExportExcel($province, $municipality){
        $municipality_check  = DB::connection("delivery_inspection_db")->table("lib_prv")
        ->where("prv", $municipality)
        ->first();

        if($municipality_check != null){
            // $rcef_id = DB::table($GLOBALS['season_prefix']."prv_".$province.".new_released")
            //     ->select("rcef_id")
            //     ->where("municipality", $municipality_check->municipality)
            //     ->get();

            $excel_data = DB::table($GLOBALS['season_prefix']."prv_".$province.".farmer_information_final as i")
                ->select("i.rcef_id", "i.rsbsa_control_no", "i.lastName", "i.firstName", "i.midName", "r.bags_claimed", "r.claimed_area", "r.rep_name as Representative", "r.rep_relation as Relation" )
                ->join($GLOBALS['season_prefix']."prv_".$province.".new_released as r", "i.rcef_id", "=", "r.rcef_id")
                ->where("r.municipality", $municipality_check->municipality)
                ->get();
            // dd($excel_data);
                $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
            
                return Excel::create("NRP_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                    $excel->sheet("NRP DISTRIBUTION REPORT", function($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                        $sheet->freezeFirstRow();
                        
                        // $sheet->setHeight(1, 30);
                        // $sheet->cells('A1:AA1', function ($cells) {
                        //     $cells->setBackground('#92D050');
                        //     $cells->setAlignment('center');
                        //     $cells->setValignment('center');
                        // });
                        // $sheet->setBorder('A1:V1', 'thin');
                    });
                })->download('xlsx');




        }else{
            return "No Municipality Selected";
        }


       


    }

}

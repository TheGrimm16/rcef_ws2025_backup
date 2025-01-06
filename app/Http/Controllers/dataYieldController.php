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

use Session;
use Auth;

class dataYieldController extends Controller
{
    public function index(){
        
        if(Auth::user()->roles->first()->name == "system-admin"){
            $province_code = Auth::user()->province;
            $region_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                //->where("prv", "like", $province_code."%")
                ->groupBy("region")
                ->get();

        }


         if(Auth::user()->username == "jpalileo" || Auth::user()->username == "r.benedicto" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga"){
            return view("yieldview.data_index")
            ->with("region", $region_list);
         }else{
            $mss = "No Access Privilege";
         return view('utility.pageClosed',compact("mss"));
         }

    }

     public function provinceList(Request $request){
        $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                ->where("region", "like", $request->region)
                ->groupBy("province")
                ->get();
        return json_encode($municipality_list);
    }


    public function load_table(Request $request){
        if($request->province == "all"){
            $province = "%";
            $group = "province";
        }else{
            $province = $request->province;
            $group = "municipality";
        }

        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->where("region", $request->region)
            ->where("province", "like", $province)
            ->groupBy($group)
            ->get();
           


            $table_array =array();
            foreach ($list as $prv) {
                $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "yield")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "weight_per_bag")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "area_harvested")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    if($group == "municipality"){
                        $yield = DB::table($db.".released")
                            ->select("farmer_profile.yield","farmer_profile.weight_per_bag","farmer_profile.area_harvested")
                            ->join($db.".farmer_profile", function($join){
                                $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                                $join->on("farmer_profile.farmerID", "=", "released.farmer_id");  
                            })
                            ->where("released.municipality", $prv->municipality)
                            ->where("farmer_profile.area_harvested", "!=",0)
                            ->where("farmer_profile.season", "DS2021")
                            ->groupBy("released.rsbsa_control_no")
                            ->groupBy("released.farmer_id")
                            ->get();
                        }
                    elseif($group == "province"){
                        $yield = DB::table($db.".released")
                            ->select("farmer_profile.yield","farmer_profile.weight_per_bag","farmer_profile.area_harvested")
                            ->join($db.".farmer_profile", function($join){
                                $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                                $join->on("farmer_profile.farmerID", "=", "released.farmer_id");  
                            })
                            ->where("farmer_profile.area_harvested", "!=",0)
                            ->where("farmer_profile.season", "DS2021")
                            ->groupBy("released.rsbsa_control_no")
                            ->groupBy("released.farmer_id")
                            ->get();
                        }
                    

                        if(count($yield)<=0){
                            continue;
                        }

                        $municipal_yield = 0;
                        foreach ($yield as $data_yield) {
                            $municipal_yield += (($data_yield->yield * $data_yield->weight_per_bag)/$data_yield->area_harvested)/1000;
                        }
                        $total_municipal_yield = $municipal_yield / count($yield);

                        if($group=="municipality"){
                            array_push($table_array, array(
                                "province" => $prv->province,
                                "municipality" => $prv->municipality,
                                "yield" => number_format($total_municipal_yield,2),
                                
                            ));}
                        elseif($group == "province"){
                            array_push($table_array, array(
                                "province" => $prv->province,
                                "municipality" => "ALL MUNICIPALITY",
                                "yield" => number_format($total_municipal_yield,2),
                                
                            ));
                        }



            }

            $table_array = collect($table_array);

             return Datatables::of($table_array)
            ->addColumn('action', function($row){
                  return "<button class='btn btn-success btn-sm' onClick='excel_download(".'"'.$row["province"].'"'.", ".'"'.$row["municipality"].'"'.")' >Export Excel</button>";  


                
            })
            ->make(true);
    }

    public function export_excel($province, $municipality){
        if($municipality=="ALL MUNICIPALITY"){
        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->where("province", $province)
            ->groupBy("municipality")
            ->get();

            $excel_data = array();

            foreach ($list as $prv) {
                $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "yield")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "weight_per_bag")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "area_harvested")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                        $yield = DB::table($db.".released")
                            ->select("farmer_profile.yield","farmer_profile.weight_per_bag","farmer_profile.area_harvested")
                            ->join($db.".farmer_profile", function($join){
                                $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                                $join->on("farmer_profile.farmerID", "=", "released.farmer_id");  
                            })
                            ->where("farmer_profile.area_harvested", "!=",0)
                            ->where("released.municipality", $prv->municipality)
                            ->where("farmer_profile.season", "DS2021")
                            ->groupBy("released.rsbsa_control_no")
                            ->groupBy("released.farmer_id")
                            ->get();
                    if(count($yield)>0){
                         $municipal_yield = 0;
                        foreach ($yield as $data_yield) {
                            $municipal_yield += (($data_yield->yield * $data_yield->weight_per_bag)/$data_yield->area_harvested)/1000;
                        }
                        $total_municipal_yield = $municipal_yield / count($yield);
                        
                        array_push($excel_data, array(
                            "province" => $prv->province,
                            "municipality"=> $prv->municipality,
                            "yield" => number_format($total_municipal_yield,2)
                        ));
                    }
            }


             $excel_data = json_decode(json_encode($excel_data), true);
            return Excel::create("Yield_report_".$province."_".date("Y-m-d g:i A"), function($excel) use ($excel_data,$province) {
                $excel->sheet($province, function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);


                    $sheet->cells("A1:C1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#19cb2f');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 

                }); 
                
            })->download('xlsx');
        }else{

        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->where("province", $province)
            ->where("municipality", $municipality)
            ->groupBy("municipality")
            ->get();

            $excel_data = array();

            foreach ($list as $prv) {
                $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "yield")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "weight_per_bag")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "area_harvested")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                        $yield = DB::table($db.".released")
                            ->select("farmer_profile.yield","farmer_profile.weight_per_bag","farmer_profile.area_harvested")
                            ->join($db.".farmer_profile", function($join){
                                $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                                $join->on("farmer_profile.farmerID", "=", "released.farmer_id");  
                            })
                            ->where("farmer_profile.area_harvested", "!=",0)
                            ->where("released.municipality", $prv->municipality)
                            ->where("farmer_profile.season", "DS2021")
                            ->groupBy("released.rsbsa_control_no")
                            ->groupBy("released.farmer_id")
                            ->get();
                    if(count($yield)>0){
                         $municipal_yield = 0;
                        foreach ($yield as $data_yield) {
                            $municipal_yield += (($data_yield->yield * $data_yield->weight_per_bag)/$data_yield->area_harvested)/1000;
                        }
                        $total_municipal_yield = $municipal_yield / count($yield);
                        
                        array_push($excel_data, array(
                            "province" => $prv->province,
                            "municipality"=> $prv->municipality,
                            "yield" => number_format($total_municipal_yield,2),
                            "date" => date("Y-m-d")
                        ));
                    }
            }

        //ADD HISTORICAL
                array_push($excel_data, array(
                            "province" => "Yield History",
                            "municipality"=> $province." (".$municipality.")",
                            "yield" => "",
                            "date" => ""
                        ));


                $history = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.municipal_yield_history")
                    ->where("province", $province)
                    ->where("municipality", $municipality)
                    ->orderBy("date_created", "DESC")
                    ->get();

                    foreach($history as $history_data){
                          array_push($excel_data, array(
                            "province" => $history_data->province,
                            "municipality"=> $history_data->municipality,
                            "yield" => $history_data->yield,
                            "date" => $history_data->date_created
                        ));
                    }

             $excel_data = json_decode(json_encode($excel_data), true);
            return Excel::create("Yield_report_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data,$municipality) {
                $excel->sheet($municipality, function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);


                    $sheet->cells("A1:C1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#19cb2f');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 

                }); 
                
            })->download('xlsx');
          
        }



    }


    public function load_chart(Request $request){
        $data_name = array();
        $data_value = array();
        $sum = 0;
        $divisor = 0;
        if($request->region == "0"){
            $regionList = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")
                ->select("lib_regional_reports.*")
                ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_regional_reports.region", "=", "lib_prv.regionName")
                ->orderBy("lib_prv.region_sort", "ASC")
                ->groupBy("lib_regional_reports.region")
                ->get();

                if(count($regionList)>0){
                  foreach ($regionList as $region) {
                    array_push($data_name, $region->region);
                    array_push($data_value, round($region->yield,2));
                    $sum += $region->yield;   
                }
                    $sum = $sum/count($regionList);  
                }
        }else{
            if($request->province=="all"){
                $province = "%";
                $group = "province";
            }else{
                $province = $request->province;
                $group = "municipality";
            }


                 $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                    ->where("region", $request->region)
                    ->where("province", "like", $province)
                    ->groupBy($group)
                    ->get();

                foreach ($list as $prv) {
                $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "yield")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "weight_per_bag")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                    $schema = DB::table("information_schema.COLUMNS")
                        ->where("TABLE_SCHEMA", $db)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->where("COLUMN_NAME", "area_harvested")
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }



                     if($group == "municipality"){
                        $yield = DB::table($db.".released")
                            ->select("farmer_profile.yield","farmer_profile.weight_per_bag","farmer_profile.area_harvested")
                            ->join($db.".farmer_profile", function($join){
                                $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                                $join->on("farmer_profile.farmerID", "=", "released.farmer_id");  
                            })
                            ->where("released.municipality", $prv->municipality)
                            ->where("farmer_profile.area_harvested", "!=",0)
                            ->where("farmer_profile.season", "DS2021")
                            ->groupBy("released.rsbsa_control_no")
                            ->groupBy("released.farmer_id")
                            ->get();
                        }
                    elseif($group == "province"){
                        $yield = DB::table($db.".released")
                            ->select("farmer_profile.yield","farmer_profile.weight_per_bag","farmer_profile.area_harvested")
                            ->join($db.".farmer_profile", function($join){
                                $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                                $join->on("farmer_profile.farmerID", "=", "released.farmer_id");  
                            })
                            ->where("farmer_profile.area_harvested", "!=",0)
                            ->where("farmer_profile.season", "DS2021")
                            ->groupBy("released.rsbsa_control_no")
                            ->groupBy("released.farmer_id")
                            ->get();
                        }


                    if(count($yield)<=0){
                        continue;
                    }
                    $divisor++;
                     $municipal_yield = 0;
                        foreach ($yield as $data_yield) {
                            $municipal_yield += (($data_yield->yield * $data_yield->weight_per_bag)/$data_yield->area_harvested)/1000;
                        }
                        $total_municipal_yield = $municipal_yield / count($yield);
                        $sum += $total_municipal_yield;
                         
                         if($group=="province"){
                         array_push($data_name, $prv->province);
                         }elseif($group=="municipality"){
                         array_push($data_name, $prv->municipality); 
                         }
                         array_push($data_value, round($total_municipal_yield,2));
                }

                if($divisor>0){
                 $sum = $sum/$divisor; 
                }else{
                 $sum = 0;
                }
           
        }


        $return = array(
            "data_name" => $data_name,
            "data_value" => $data_value,
            "total" => round($sum,2)
        );
        //dd($return);
        return json_encode($return);


    }




}

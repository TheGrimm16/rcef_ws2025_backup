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

class encoderYieldController extends Controller
{


    public function home(){
        
        if(Auth::user()->roles->first()->name == "encoder_yield"){
           $province_code = Auth::user()->province;
           $province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                ->where("prv", "like", $province_code."%")
                ->groupBy("province")
                ->get();

        }elseif(Auth::user()->roles->first()->name == "system-admin"){
            $province_code = Auth::user()->province;
            $province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                //->where("prv", "like", $province_code."%")
                ->groupBy("province")
                ->get();

        
        }elseif(Auth::user()->roles->first()->name == "rcef-programmer"){
            $province_code = Auth::user()->province;
            $province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                //->where("prv", "like", $province_code."%")
                ->groupBy("province")
                ->get();

        }


         if(Auth::user()->roles->first()->name == "encoder_yield" || Auth::user()->username == "jpalileo" || Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rd.rimandojr" || Auth::user()->username == "racariaga" || Auth::user()->username == "rm.capiroso"){
            return view("yieldview.index")
            ->with("province", $province_list);
         }else{
            $mss = "No Access Privilege";
         return view('utility.pageClosed',compact("mss"));
         }

        
    }

    public function municipalityList(Request $request){
        $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                ->where("province", "like", $request->province)
                ->groupBy("municipality")
                ->get();
        return json_encode($municipality_list);
    }


    public function getLibInputs(Request $request){

        if($request->category == "all"){
            $category = "%";
        }else{
            $category = $request->category;
        }

        $libData = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.lib_yield_inputs")
                ->where("category", "like", $category)
                ->where("province", $request->province)
                ->groupBy("category")
                ->get();
        if(count($libData)>0){
            $msg = "";
            foreach ($libData as $inputs) {
                    $category = str_replace("_", " ", $inputs->category);

                    if($msg == ""){
                        $msg .= "<strong>".$request->province."</strong>, ";
                    }else{
                        $msg .= "<br>";
                    }

                    $msg .= "Average ".$category." (Range): ";
                    $msg .= "Min: ".$inputs->from_value." | Max:".$inputs->to_value;                   
            }

            return json_encode($msg);
        }else{
            return json_encode("None");
        }
    }


    public function load_table(Request $request){
        //dd($request->all());
        if($request->municipality == "all"){
            $municipality = "%"; 
        }else{
            $municipality = $request->municipality;
        }

         if($request->contactInfo == "all"){
            $contactInfo = "%"; 
        }elseif($request->contactInfo == "with"){
            $contactInfo = "%9%";
        }elseif($request->contactInfo == "without"){
            $contactInfo = "";
        }

        //Yield Category
        //1 - No data
        //2 - Claim data is the same as yield data
        //3 - Calculated yield is <= 1
        //4 - Calculated yield is >1 and <= 2
        //5 - Calculated yield is > 13
        //6 - edited farmer

        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->select("prv")
            ->where("province", "like", $request->province)
            //->where("municipality", "like", $municipality)
            ->first();
        $table_array = array();

        if(count($prv)>0){

            $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0, 4);

             $editedFarmer = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                        ->select("farmer_tbl_id")
                        ->where("province_code", substr($prv->prv, 0, 4))
                        ->get();

            $releasedFarmerList = DB::table($db.".released")
                ->select("released.seed_variety", DB::raw("sum(released.bags) as sum"), "released.date_released", "farmer_profile.*", "other_info.phone",DB::raw("((yield * weight_per_bag / area_harvested) / 1000) as ton"))
                ->join($db.".farmer_profile", function($join){
                    $join->on("released.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                    $join->on("released.farmer_id", "=", "farmer_profile.farmerID");
                })
                ->join($db.".other_info", function($join){
                    $join->on("released.rsbsa_control_no", "=", "other_info.rsbsa_control_no");
                    $join->on("released.farmer_id", "=", "other_info.farmer_id");
                })
                ->where("released.municipality", "like", $municipality)
                ->where("released.province", $request->province)
                 ->where("other_info.phone", 'like', $contactInfo)

                ->groupBy("released.rsbsa_control_no")
                ->groupby("released.farmer_id")
                ->orderBy("other_info.phone", "DESC")
                ->orderBy("farmer_profile.lastName", "ASC")
                ->orderBy("farmer_profile.firstName", "ASC")
                ->orderBy("farmer_profile.midName", "ASC")
                ->get();

    
            foreach ($releasedFarmerList as $farmer) {
                $select = "<select class='form-control' name='season_".$farmer->id."' id='season_".$farmer->id."' onchange='updateData(this.value, ".$farmer->id.", ".'"season"'.",".'"'.$db.'"'.",".'"'.$farmer->season.'"'.")'   >"; 
                       $select .= "<option value='-' > Select Season </option>";   
                        for($x=2020; $x<=2023; $x++){
                            $valDS = "DS".$x;
                                if($valDS == $farmer->season){
                                    $sel = "selected";
                                }else{
                                    $sel ="";
                                }
                            $select .= "<option value='".$valDS."' ".$sel."> DS ".$x." </option>";
                            $valDS = "WS".$x;
                                if($valDS == $farmer->season){
                                    $sel = "selected";
                                }else{
                                    $sel ="";
                                }

                            $select .= "<option value='".$valDS."' ".$sel."> WS ".$x." </option>";      
                        }
           
                $select .= "</select>";



                array_push($table_array, array(
                    "province" => $request->province,
                    "rsbsa" => $farmer->rsbsa_control_no,
                    "name" => $farmer->lastName.", ".$farmer->firstName." ".$farmer->midName,
                    "variety" => $farmer->seed_variety,
                    "bags" => $farmer->sum,
                    "dateclaimed" => date( "Y-m-d",strtotime($farmer->date_released)),
                    "yield1" => "<input class='form-control' type='number' onchange='updateData(this.value, ".$farmer->id.", ".'"yield"'.",".'"'.$db.'"'.",".'"'.$farmer->yield.'"'.")' id='yield_".$farmer->id."' value='".$farmer->yield."' style='width: 90px; text-align: right;' >",
                    "yield2" => "<input class='form-control' type='number' onchange='updateData(this.value, ".$farmer->id.", ".'"wtperbag"'.", ".'"'.$db.'"'.",".'"'.$farmer->weight_per_bag.'"'.")' id='wtperbag_".$farmer->id."' value='".$farmer->weight_per_bag."' style='width: 90px; text-align: right;' >",
                    "yield3" => "<input class='form-control' type='number' onchange='updateData(this.value, ".$farmer->id.", ".'"area_harvest"'.",".'"'.$db.'"'.",".'"'.$farmer->area_harvested.'"'.")' id='area_harvest_".$farmer->id."' value='".$farmer->area_harvested."' style='width: 90px; text-align: right;' >",
                    "season" => $select,

                    "yield" => $farmer->yield,
                    "weight_per_bag"=> $farmer->weight_per_bag,
                    "area_harvested"=>$farmer->area_harvested,
                    "farmer" => $farmer->id,
                    "edited" => 0,
                    "contact_info" => $farmer->phone
                ));
            }
            
        }
//<input type='number' onchange='' id='season_".$farmer->id."'>
//updateData(this.value, ".$farmer->id.", ".'"area_harvest"'.",".'"'.$db.'"'.",".'"'.$farmer->area_harvested.'"'.")

        foreach($table_array as $key => $row){
             if($row["area_harvested"] >0){
                    $tons = number_format(((floatval($row["yield"])*floatval($row["weight_per_bag"]))/$row["area_harvested"]) / 1000, 2);
                }else{
                    $tons = 0;
                }

                if($row['yield'] <=0 || $row['weight_per_bag'] <=0 || $row['area_harvested'] <= 0){
                    //if($request->c1 == 0){
                        unset($table_array[$key]);
                    //}

                }elseif($row['yield'] == $row['bags'] || $row['weight_per_bag'] == $row['bags'] || $row['area_harvested'] == $row['bags']){
                    if($request->c2 == 0){
                        unset($table_array[$key]);
                    }
                }elseif($tons <= 1){
                    if($request->c3 == 0){
                        unset($table_array[$key]);
                    }
                }elseif($tons > 1 && $tons <=2){
                    if($request->c4 == 0){
                        unset($table_array[$key]);
                    }
                }elseif($tons > 13){
                    if($request->c5 == 0){
                        unset($table_array[$key]);
                    }
                }
                else{
                    $editedFarmer = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                        ->select("farmer_tbl_id")
                        ->where("farmer_tbl_id", $row["farmer"])
                        ->first();
                        if(count($editedFarmer)>0){
                            $table_array[$key]["edited"]=1;
                            if($request->c6 == 0){
                                unset($table_array[$key]);
                            }
                        }else{
                            unset($table_array[$key]);
                        }
                }
        }







        $table_array = collect($table_array);
            return Datatables::of($table_array)
            ->addColumn('bags', function($row){
                return "<label id='bags_".$row['farmer']."' >".$row["bags"]."</label>";
            })
            ->addColumn('tons', function($row){
                if($row["area_harvested"] >0){
                    $tons = number_format(((floatval($row["yield"])*floatval($row["weight_per_bag"]))/$row["area_harvested"]) / 1000, 2);
                }else{
                    $tons = 0;
                }

                return "<label id='tons_".$row['farmer']."' >".$tons."</label>";
            })
            ->addColumn('category', function($row){

                if($row["area_harvested"] >0){
                    $tons = number_format(((floatval($row["yield"])*floatval($row["weight_per_bag"]))/$row["area_harvested"]) / 1000, 2);
                }else{
                    $tons = 0;
                }

                if($row['yield'] <=0 || $row['weight_per_bag'] <=0 || $row['area_harvested'] <= 0){
                    return "<label id='category_".$row['farmer']."' >Category 1</label>";
                }elseif($row['yield'] == $row['bags'] || $row['weight_per_bag'] == $row['bags'] || $row['area_harvested'] == $row['bags']){
                    return "<label id='category_".$row['farmer']."' >Category 2</label>";
                }elseif($tons <= 1){
                    return "<label id='category_".$row['farmer']."' >Category 3</label>";
                }elseif($tons > 1 && $tons <=2){
                    return "<label id='category_".$row['farmer']."' >Category 4</label>";
                }elseif($tons > 13){
                    return "<label id='category_".$row['farmer']."' >Category 5</label>";
                }elseif($row["edited"] == 1){
                    return "<label id='category_".$row['farmer']."' >Category 6</label>";
                }




            })
            ->addColumn('action', function($row){  
                $checkHistory = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                    ->where("farmer_tbl_id", $row["farmer"])
                    ->first();
                    if(count($checkHistory)>0){
                        return ' <button class="btn btn-warning btn-sm" id="'.$row["farmer"].'" data-target="#history_modal" data-toggle="modal" data-id="'.$row["farmer"].'" >
                            <i class="fa fa-history" aria-hidden="true"></i>
                        </button>
                        ';
                    }else{
                         return ' <button class="btn btn-dark btn-sm" id="'.$row["farmer"].'" data-target="#history_modal" data-toggle="modal" data-id="'.$row["farmer"].'" disabled>
                            <i class="fa fa-history" aria-hidden="true"></i>
                        </button>
                        ';
                    }
            })

            ->make(true);
    }   
    


  


    public function historyData(Request $request){
   
        $getprv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->first();
        $return_arr = array();
            if(count($getprv)>0){
                $province_code =  substr($getprv->prv, 0,4);
                    $history = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                        ->where("province_code", $province_code)
                        ->where("farmer_tbl_id", "like", $request->farmer_id)
                        ->orderBy("date_updated", "ASC")
                        ->orderBy("field_updated", "ASC")
                        ->get();
                           
                    if(count($history)>0){
                        foreach ($history as $value) {
                            if($request->req == "table"){

                                if(strtotime(date("Y-m-d",strtotime($value->date_updated))) >= strtotime($request->date_from) AND strtotime(date("Y-m-d", strtotime($value->date_updated))) <=  strtotime($request->date_to) ){
                                
                                    if($request->user_name =="all"){
                                       array_push($return_arr, array(
                                            "rsbsa" => $value->rsbsa_control_no,
                                            "full_name" => $value->full_name,
                                            "field_updated" => $value->field_updated,
                                            "info" => $value->from_value." - ".$value->to_value,
                                            "date_updated" => $value->date_updated,
                                            "author" => $value->user_updated,
                                            "category" => $value->category
                                        )); 
                                   }else{
                                        if($request->user_name == $value->user_updated){
                                            array_push($return_arr, array(
                                                "rsbsa" => $value->rsbsa_control_no,
                                                "full_name" => $value->full_name,
                                                "field_updated" => $value->field_updated,
                                                "info" => $value->from_value." - ".$value->to_value,
                                                "date_updated" => $value->date_updated,
                                                "author" => $value->user_updated,
                                                "category" => $value->category
                                            ));
                                        }
                                   }

                                    
                                }   
                            }elseif($request->req == "button"){
                                    array_push($return_arr, array(
                                    "rsbsa" => $value->rsbsa_control_no,
                                    "full_name" => $value->full_name,
                                    "field_updated" => $value->field_updated,
                                    "info" => $value->from_value." - ".$value->to_value,
                                    "date_updated" => $value->date_updated,
                                    "author" => $value->user_updated,
                                    "category" => $value->category
                                ));
                            }


                            
                        }
                    }
            }
    

        if($request->req == "button"){
            return $return_arr;
        }elseif($request->req == "table"){
            $table_array = collect($return_arr);
            return Datatables::of($table_array)
            ->make(true);
        }
    }


      public function usernameList(Request $request)
    {
        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->first();



        $list = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
            ->where("province_code", substr($prv->prv, 0,4))
            ->where("farmer_tbl_id", "like", $request->farmer_id)
            ->groupby("user_updated")
            ->get();

            return json_encode($list);


    }



    public function exportExcelHistory($province, $municipality,$date_from,$date_to,$user_updated,$farmer_id){
        if($municipality == "all"){
            $municipality = "%"; 
        }

        if($farmer_id=="all")$farmer_id="%";


        if($user_updated=="all")$user_updated="%";

        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", $province)
            ->where("municipality", "like",$municipality)
            ->first();


        $history = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
            ->select("farmer_yield_logs.*", "users.firstName", "users.middleName", "users.lastName")
            ->join($GLOBALS['season_prefix']."sdms_db_dev.users", "users.username", "=", "farmer_yield_logs.user_updated")
            ->where("farmer_yield_logs.province_code", substr($prv->prv, 0, 4))
            ->where("farmer_yield_logs.user_updated", "like", $user_updated) 
            ->where("farmer_yield_logs.farmer_tbl_id", "like", $farmer_id) 
                      
            ->orderBy("farmer_yield_logs.user_updated", "ASC")
            ->orderBy("farmer_yield_logs.date_updated", "DESC")
            ->get();


        $history = json_decode(json_encode($history), true);
            return Excel::create("Yield_Update_Logs_".$province."_".date("Y-m-d g:i A"), function($excel) use ($history, $province,$date_from,$date_to) {
                $excel->sheet("BENEFICIARY_LIST", function($sheet) use ($history, $province,$date_from,$date_to) {
                     $sheet->mergeCells("A1:I1");
                     $sheet->cells("A1:I1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#f2f552');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                     $sheet->cell("A1", function($cells){
                            $cells->setValue("UPDATE HISTORY");
                        });

                     $row = 2;
                     $sheet->prependRow($row, array(
                        "user name", "Name", "Province","RSBSA No.", "Farmer Name", "Field Updated", "From", "To", "Date"
                    ));

                     $sheet->cells("A2:I2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#19cb2f');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 


                     $row++;

                     foreach ($history as $key => $value) {

                        if(strtotime(date("Y-m-d",strtotime($value['date_updated']))) < strtotime($date_from) || strtotime(date("Y-m-d", strtotime($value['date_updated']))) >  strtotime($date_to) ){
                            continue;
                        }





                    $col = "A";    

                        $sheet->cell($col.$row, function($cells) use ($value){
                           $cells->setValue($value["user_updated"]);                           
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                            $cells->setValue($value["firstName"]." ".$value["middleName"]." ".$value["lastName"]);
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($province){
                            $cells->setValue($province);
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                        $cells->setValue($value["rsbsa_control_no"]);                           
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                        $cells->setValue($value["full_name"]);                           
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                        $cells->setValue($value["field_updated"]);                           
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                        $cells->setValue($value["from_value"]);                           
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                        $cells->setValue($value["to_value"]);                           
                        });
                        $col++;
                        $sheet->cell($col.$row, function($cells) use ($value){
                        $cells->setValue($value["date_updated"]);                           
                        });
                        $row++;
                     }
                }); 
                
            })->download('xlsx');

    }


    public function exportExcelData(Request $request){

         if($request->municipality == "all"){
            $municipality = "%"; 
        }else{
            $municipality = $request->municipality;
        }


        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->select("prv")
            ->where("province", "like", $request->province)
            ->first();
        $contactTrue = array();
        $contactFalse = array();
        $legend = array();

        array_push($legend,array(
            "Category Number" => "Category 1",
            "Description" => "Yield is equal to  0 (zero)"
        ));

        array_push($legend,array(
            "Category Number" => "Category 2",
            "Description" => "Total Bags Claimed is equal to any of the ff: (Total Production, Ave. Weight per bag, Area Harvested)"
        ));

        array_push($legend,array(
            "Category Number" => "Category 3",
            "Description" => "Yield is less than or equal to 1 (one)"
        ));

        array_push($legend,array(
            "Category Number" => "Category 4",
            "Description" => "Yield is greater than 1 (one) but less than or equal to 2 (two)"
        ));

        array_push($legend,array(
            "Category Number" => "Category 5",
            "Description" => "Yield is greater than 13 (thirteen)"
        ));

        array_push($legend,array(
            "Category Number" => "Category 6",
            "Description" => "Updated Farmer's Yield"
        ));


        if(count($prv)>0){

            $db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0, 4);
            $releasedFarmerList = DB::table($db.".released")
                ->select("released.seed_variety", DB::raw("sum(released.bags) as sum"), "released.date_released", "farmer_profile.*", "other_info.phone")
                ->join($db.".farmer_profile", function($join){
                    $join->on("released.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                    $join->on("released.farmer_id", "=", "farmer_profile.farmerID");
                })
                ->join($db.".other_info", function($join){
                    $join->on("released.rsbsa_control_no", "=", "other_info.rsbsa_control_no");
                    $join->on("released.farmer_id", "=", "other_info.farmer_id");
                })
                
                ->where("released.province", $request->province)
                ->where("released.municipality", "like", $municipality)
                ->groupBy("released.rsbsa_control_no")
                ->groupby("released.farmer_id")
                ->orderBy("farmer_profile.lastName", "ASC")
                ->orderBy("farmer_profile.firstName", "ASC")
                ->orderBy("farmer_profile.midName", "ASC")
                ->get();

              

                foreach ($releasedFarmerList as $key => $row) {
                    $category = '';

                    if($row->phone ==""){
                         if($row->area_harvested >0){
                            $tons = number_format(((floatval($row->yield)*floatval($row->weight_per_bag))/$row->area_harvested) / 1000, 2);
                        }else{
                            $tons = 0;
                        }
                        if($row->yield <=0 || $row->weight_per_bag <=0 || $row->area_harvested <= 0){
                            $category = 1;
                        }elseif($row->yield == $row->sum || $row->weight_per_bag == $row->sum || $row->area_harvested == $row->sum){
                            $category = 2;
                        }elseif($tons <= 1){
                            $category = 3;
                        }elseif($tons > 1 && $tons <=2){
                            $category = 4;
                        }elseif($tons > 13){
                            $category = 5;
                        }
                        else{
                            $editedFarmer = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                                ->select("farmer_tbl_id")
                                ->where("farmer_tbl_id", $row->id)
                                ->first();
                                if(count($editedFarmer)>0){
                                   $category = 6;
                                }else{
                                    unset($releasedFarmerList[$key]);
                                }
                        }  
                    

                        if($category != ""){
                            array_push($contactFalse, array(
                            "province" => $request->province,
                            "Rsbsa #" => $row->rsbsa_control_no,
                            "First Name" => $row->firstName,
                            "Middle Name" => $row->midName,
                            "Last Name" => $row->lastName,
                            "Tons/ha" => $tons,
                            "Production" => $row->yield,
                            "Average Weight per Bag" => $row->weight_per_bag,
                            "Area Harvested" => $row->area_harvested,
                            "Data Category" => $category
                            ));
                        }


                    }
                    else{
                        if($row->area_harvested >0){
                            $tons = number_format(((floatval($row->yield)*floatval($row->weight_per_bag))/$row->area_harvested) / 1000, 2);
                        }else{
                            $tons = 0;
                        }
                        if($row->yield <=0 || $row->weight_per_bag <=0 || $row->area_harvested <= 0){
                            $category = 1;
                        }elseif($row->yield == $row->sum || $row->weight_per_bag == $row->sum || $row->area_harvested == $row->sum){
                            $category = 2;
                        }elseif($tons <= 1){
                            $category = 3;
                        }elseif($tons > 1 && $tons <=2){
                            $category = 4;
                        }elseif($tons > 13){
                            $category = 5;
                        }
                        else{
                            $editedFarmer = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                                ->select("farmer_tbl_id")
                                ->where("farmer_tbl_id", $row->id)
                                ->first();
                                if(count($editedFarmer)>0){
                                   $category = 6;
                                }else{
                                    unset($releasedFarmerList[$key]);
                                }
                        }

                        if($category != ""){
                            array_push($contactTrue, array(
                            "province" => $request->province,
                            "Rsbsa #" => $row->rsbsa_control_no,
                            "First Name" => $row->firstName,
                            "Middle Name" => $row->midName,
                            "Last Name" => $row->lastName,
                            "Tons/ha" => $tons,
                            "Production" => $row->yield,
                            "Average Weight per Bag" => $row->weight_per_bag,
                            "Area Harvested" => $row->area_harvested,
                            "Data Category" => $category
                            ));
                        }


                    }

                       
                }

                  
            //EXCEL DOWNLOAD
            $contactTrue = json_decode(json_encode($contactTrue), true); //convert collection to associative array to be converted to excel
            $contactFalse = json_decode(json_encode($contactFalse), true); //convert collection to associative array to be converted to excel
            $legend = json_decode(json_encode($legend), true);
            return Excel::create("Farmer_List_Invalid_Yield_Data_".$request->province."_".date("Y-m-d g:i A"), function($excel) use ($contactTrue,$contactFalse,$legend) {
                $excel->sheet("farmer_without_contact_(LGU)", function($sheet) use ($contactTrue) {
                    $sheet->fromArray($contactTrue);
                     $sheet->cells("A1:J1", function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    $cells->setFontSize(16);
                                }); 
                }); 
                $excel->sheet("farmer_with_contact_(PhilRice)", function($sheet) use ($contactFalse) {
                    $sheet->fromArray($contactFalse);
                    $sheet->cells("A1:J1", function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    $cells->setFontSize(16);
                                }); 
                }); 
                $excel->sheet("Category Legend", function($sheet) use ($legend) {
                    $sheet->fromArray($legend);
                    $sheet->cells("A1:B1", function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    $cells->setFontSize(16);
                                }); 
                }); 
            })->download('xlsx');


            }

    }




    public function updateData(Request $request){

        $check = DB::table($request->db.".farmer_profile")
                ->where("id", $request->id)
                ->first();

        if(count($check)>0){
            $field = "";
            if($request->yieldType=="yield"){
                    DB::table($request->db.".farmer_profile")
                        ->where("id", $request->id)
                        ->update(["yield"=>$request->value]);
                    $field = "yield";
            }elseif($request->yieldType=="wtperbag"){
                    DB::table($request->db.".farmer_profile")
                        ->where("id", $request->id)
                        ->update(["weight_per_bag"=>$request->value]);
                    $field = "weight_per_bag";
            }elseif($request->yieldType=="area_harvest"){
                     DB::table($request->db.".farmer_profile")
                        ->where("id", $request->id)
                        ->update(["area_harvested"=>$request->value]);
                    $field = "area_harvested";
            }elseif($request->yieldType=="season"){
                    if($request->value=="-"){
                         return json_encode("Failed_season");
                    }else{
                    DB::table($request->db.".farmer_profile")
                        ->where("id", $request->id)
                        ->update(["season"=>$request->value]);
                    $field = "season"; 
                    }


                     
            }


            else{
                return json_encode("Failed");
            }


            if($field != ""){
            
                //DELETE 
                DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                    ->where("farmer_tbl_id", $request->id)
                    ->where("category", $request->category)
                    ->where("user_updated", Auth::user()->username)
                    ->where("field_updated",  $field)
                    ->delete();

                $province_code = str_replace("prv_", "", $request->db);
                //LOGS
                DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.farmer_yield_logs")
                ->insert([
                    'farmer_tbl_id' => $request->id,
                    'farmer_id' => $check->farmerID,
                    'rsbsa_control_no' => $check->rsbsa_control_no,
                    'full_name' => $check->lastName.", ".$check->firstName." ".$check->midName,
                    'field_updated' => $field,
                    'from_value' => $request->oldvalue,
                    'to_value' => $request->value,
                    'province_code' => $province_code,
                    'user_updated' => Auth::user()->username,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'category' => $request->category
                ]);


                //INSERT YIELD HISTORY
                $this->computeYield($check->farmerID,$check->rsbsa_control_no,$request->db);



                return json_encode("Success");
            }
          
        }else{
            return json_encode("Failed");
        }
    }



    public function computeYield($farmer_id, $farmer_rsbsa, $db){

            $released_data = DB::Table($db.".released")
                ->where("farmer_id", $farmer_id)
                ->where("rsbsa_control_no", $farmer_rsbsa)
                ->first();

                if(count($released_data)>0){
                    $province = $released_data->province;
                    $municipality = $released_data->municipality;

                    $municipal_farmers= DB::table($db.".released")
                        ->select("farmer_profile.yield", "farmer_profile.weight_per_bag", "farmer_profile.area_harvested")
                        ->join($db.".farmer_profile", function($join){
                            $join->on("released.farmer_id", "=", "farmer_profile.farmerID");
                            $join->on("released.rsbsa_control_no", "=", "farmer_profile.rsbsa_control_no");
                        })
                        ->where("released.province", $province)
                        ->where("released.municipality", $municipality)
                        ->where("farmer_profile.area_harvested", "!=",0)
                        ->where("farmer_profile.season", "DS2021")
                        
                        ->groupBy("released.rsbsa_control_no")
                        ->groupBy("released.farmer_id")
                        ->get();

                        $yield = 0;
                        foreach ($municipal_farmers as $farmer) {
                        $yield += ((floatval($farmer->yield)*floatval($farmer->weight_per_bag))/$farmer->area_harvested) / 1000;
                        }
                        $total_yield = $yield / count($municipal_farmers);
                
                    $data = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.municipal_yield_history")
                        ->where("municipality", $municipality)
                        ->where("province", $province)
                        ->where("date_created", date("Y-m-d"))
                        ->first();
                        if(count($data)>0){
                            //UPDATE
                            DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.municipal_yield_history")
                                ->where("id", $data->id)
                                ->update([
                                    "yield" => $total_yield
                                ]);
                        }else{
                            //INSERT
                             DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.municipal_yield_history")
                                ->insert([
                                    "province"=>$province,
                                    "municipality" =>$municipality,
                                    "yield"=> $total_yield,
                                    "date_created" => date("Y-m-d")
                                ]);
                        }
                }
    }





}

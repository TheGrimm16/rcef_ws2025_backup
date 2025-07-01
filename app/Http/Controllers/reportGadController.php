<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\SeedCooperatives;
use App\SeedGrowers;
use Config;
use DB;
use Excel;
use Session;
use Auth;

class reportGADController extends Controller
{
    public function generateGadGraph(Request $request){

        // if($request->type=="variety_sex"){
        //     $data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")
        //     ->select("seed_variety", DB::raw("SUM(farmer_1_male + farmer_2_male + farmer_3_male) as total_male"), DB::raw("SUM(farmer_1_female + farmer_2_female + farmer_3_female) as total_female"))
        //     ->groupBy("seed_variety")
        //     ->orderBy(DB::raw("SUM(farmer_1_male + farmer_2_male + farmer_3_male + farmer_1_female + farmer_2_female + farmer_3_female)"), "DESC")
        //     ->get();

        //  return json_encode($data);

        // }elseif($request->type=="variety_group"){

        //     $data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")
        //     ->select("seed_variety", DB::raw("SUM(farmer_1_male + farmer_1_female) as cat1"), DB::raw("SUM(farmer_2_female + farmer_2_male) as cat2"), DB::raw("SUM(farmer_3_female + farmer_3_male) as cat3"))
        //     ->groupBy("seed_variety")
        //     ->orderBy(DB::raw("SUM(farmer_1_male + farmer_2_male + farmer_3_male + farmer_1_female + farmer_2_female + farmer_3_female)"), "DESC")
        //     ->get();

        //     return json_encode($data);
        // }

        // $pythonPath = 'C://Python312//python.exe';

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
        // $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

        $scriptPath = base_path('app/Http/PyScript/gad-report/gad-charts2.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];
        $type = $request->type;

        $escapedSsn = escapeshellarg($ssn);
        $escapedType = escapeshellarg($type);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedType";

        // Create a new process
        $process = new Process($command);
        
        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $result = json_decode($output, true);

            $result = json_encode($result);
            return $result;
        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }

    }


    public function generate_gad_report($type){
        $excel_arr = array();
        $sheet_1 = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data as t1")
                ->select("t2.regionName as region", "t1.province", DB::raw("CONCAT('RCEF') as origin"), DB::raw("CONCAT('PH',t2.prv_code,'00000') as psa_code"), 
                "t1.farmer_1_male", "t1.farmer_2_male", "t1.farmer_3_male",
                "t1.farmer_1_female", "t1.farmer_2_female", "t1.farmer_3_female",
                "t1.bags_1_male", "t1.bags_2_male", "t1.bags_3_male",
                "t1.bags_1_female", "t1.bags_2_female", "t1.bags_3_female",
                "t1.claimed_1_male", "t1.claimed_2_male", "t1.claimed_3_male",
                "t1.claimed_1_female", "t1.claimed_2_female", "t1.claimed_3_female"
                )
                ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv as t2", "t2.province", "=", "t1.province")
                ->groupBy("t1.province")
                ->orderBy("region_sort")
                ->get();
        array_push($excel_arr, array(
            "sheet_name" => "Table 1",
            "data" => $sheet_1
        ));
       
           
        $sheet_2 = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety as t1")
            ->select("t2.regionName as region", "t1.province", DB::raw("CONCAT('RCEF') as origin"), DB::raw("CONCAT('PH',t2.prv_code,'00000') as psa_code"), "seed_variety",
            "t1.farmer_1_male", "t1.farmer_2_male", "t1.farmer_3_male",
            "t1.farmer_1_female", "t1.farmer_2_female", "t1.farmer_3_female"
            )
            ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv as t2", "t2.province", "=", "t1.province")
           ->groupBy("province", "seed_variety")
            ->orderBy("region_sort","province","seed_variety")
            ->get();

        array_push($excel_arr, array(
            "sheet_name" => "Table 2",
            "data" => $sheet_2
        ));

        $sheet_3 = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety as t1")
            ->select( "t1.seed_variety",
            DB::raw("SUM(t1.farmer_1_male) as male_1"),DB::raw("SUM(t1.farmer_2_male) as male_2"), DB::raw("SUM(t1.farmer_3_male) as male_3"),
            DB::raw("SUM(t1.farmer_1_female) as female_1"),DB::raw("SUM(t1.farmer_2_female) as female_2"), DB::raw("SUM(t1.farmer_3_female) as female_3"),

            DB::raw("SUM(t1.bags_male_1) as bag_m1"),DB::raw("SUM(t1.bags_male_2) as bag_m2"), DB::raw("SUM(t1.bags_male_3) as bag_m3"),
            DB::raw("SUM(t1.bags_female_1) as bag_f1"),DB::raw("SUM(t1.bags_female_2) as bag_f2"), DB::raw("SUM(t1.bags_female_3) as bag_f3"),
            
            DB::raw("SUM(t1.claimed_male_1) as claimed_m1"),DB::raw("SUM(t1.claimed_male_2) as claimed_m2"), DB::raw("SUM(t1.claimed_male_3) as claimed_m3"),
            DB::raw("SUM(t1.claimed_female_1) as claimed_f1"),DB::raw("SUM(t1.claimed_female_2) as claimed_f2"), DB::raw("SUM(t1.claimed_female_3) as claimed_f3")
            )
           ->groupBy("seed_variety")
            ->orderBy("seed_variety")
            ->get();
    

        array_push($excel_arr, array(
            "sheet_name" => "Table 3",
            "data" => $sheet_3
        ));





        $data_4 = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")
        ->select( "seed_variety",
        DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 ) as total_bag"),
        DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3) as male_bag"), 
        DB::raw("SUM( bags_female_1 +bags_female_2 +bags_female_3) as female_bag"),
        DB::raw("SUM(bags_male_1 + bags_female_1) as cat1_bag"),
        DB::raw("SUM(bags_male_2 + bags_female_2) as cat2_bag"), 
        DB::raw("SUM(bags_male_3 + bags_female_3) as cat3_bag")
        )
        ->groupBy("seed_variety")
        ->orderBy(DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 )"), "DESC")
        ->get();
        
        $data_cent = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")
            ->select( 
        DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 ) as total_bag"),
        DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3) as male_bag"), 
        DB::raw("SUM( bags_female_1 +bags_female_2 +bags_female_3) as female_bag"),
        DB::raw("SUM(bags_male_1 + bags_female_1) as cat1_bag"),
        DB::raw("SUM(bags_male_2 + bags_female_2) as cat2_bag"), 
        DB::raw("SUM(bags_male_3 + bags_female_3) as cat3_bag")
        )
        ->orderBy(DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 )"), "DESC")
        ->first();
        

            $sheet_4 = array();
            foreach($data_4 as $data_info){
                array_push($sheet_4, array(
                    "seed_variety" => $data_info->seed_variety,
                    "total_bag" => number_format($data_info->total_bag),
                    "cent_bag" => number_format(($data_info->total_bag / $data_cent->total_bag)*100,2)."%" ,
                    "male_bag" => number_format($data_info->male_bag) ,
                    "male_cent" => number_format(($data_info->male_bag / $data_cent->male_bag)*100,2)."%" ,
                    "female_bag" => number_format($data_info->female_bag) ,
                    "female_cent" => number_format(($data_info->female_bag / $data_cent->female_bag)*100,2)."%" ,
                    "blank" => "" ,
                    "cat1_bag" => number_format($data_info->cat1_bag) ,
                    "cat1_cent" => number_format(($data_info->cat1_bag / $data_cent->cat1_bag)*100,2)."%" ,
                    "cat2_bag" => number_format($data_info->cat2_bag) ,
                    "cat2_cent" => number_format(($data_info->cat2_bag / $data_cent->cat2_bag)*100,2)."%" ,
                    "cat3_bag" => number_format($data_info->cat3_bag) ,
                    "cat3_cent" => number_format(($data_info->cat3_bag / $data_cent->cat3_bag)*100,2)."%" ,
                ));

            }
    


            array_push($excel_arr, array(
                "sheet_name" => "Table 4",
                "data" => $sheet_4
            ));


   










        $sheet_5 = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety as t1")
        ->select("t2.regionName as region", "t1.province", DB::raw("CONCAT('RCEF') as origin"), DB::raw("CONCAT('PH',t2.prv_code,'00000') as psa_code"), "seed_variety",
        "t1.bags_male_1", "t1.bags_male_2", "t1.bags_male_3",
        "t1.bags_female_1", "t1.bags_female_2", "t1.bags_female_3"
        )
        ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv as t2", "t2.province", "=", "t1.province")
       ->groupBy("province", "seed_variety")
        ->orderBy("region_sort","province","seed_variety")
        ->get();

        array_push($excel_arr, array(
            "sheet_name" => "Table 5",
            "data" => $sheet_5
        ));

      
                $excel_file =  Excel::create($GLOBALS['season_prefix']."as_of"."_".date("Y-m-d"), function($excel) use ($excel_arr) {
                     
                    foreach($excel_arr as $key => $data_arr){
                        $data_list = json_decode(json_encode($data_arr["data"]), true);
                        
                        if($data_arr["sheet_name"]=="Table 1"){
                            $excel->sheet($data_arr["sheet_name"], function($sheet) use ($data_list) {
                                //SET HEADER
                                $sheet->cells("A1:V1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                    $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->mergeCells("E1:J1");
                                $sheet->cells("E1:J1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("E1", function($cells){ $cells->setValue("Farmer-beneficiaries"); });
                                
                                $sheet->mergeCells("K1:P1");
                                $sheet->cells("K1:P1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("K1", function($cells){ $cells->setValue("No. of Bags"); });

                                $sheet->mergeCells("Q1:V1");
                                $sheet->cells("Q1:V1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("Q1", function($cells){ $cells->setValue("Area"); });

                                $sheet->cells("A2:D2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                    $cells->setBorder('thin','thin','thin','thin');}); 

                                $sheet->mergeCells("E2:G2");
                                $sheet->cells("E2:G2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("E2", function($cells){ $cells->setValue("Male"); });
                                $sheet->mergeCells("H2:J2");
                                $sheet->cells("H2:J2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("H2", function($cells){ $cells->setValue("Female"); });


                                $sheet->mergeCells("K2:M2");
                                $sheet->cells("K2:M2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("K2", function($cells){ $cells->setValue("Male"); });
                                $sheet->mergeCells("N2:P2");
                                $sheet->cells("N2:P2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("N2", function($cells){ $cells->setValue("Female"); });

                                $sheet->mergeCells("Q2:S2");
                                $sheet->cells("Q2:S2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("Q2", function($cells){ $cells->setValue("Male"); });
                                $sheet->mergeCells("T2:V2");
                                $sheet->cells("T2:V2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("T2", function($cells){ $cells->setValue("Female"); });

                                $sheet->prependRow(3, array(
                                    "Region", "Province", "RCEF","PSGC Code","18-29","30-59","60 & up", "18-29","30-59","60 & up","18-29","30-59","60 & up","18-29","30-59","60 & up","18-29","30-59","60 & up","18-29","30-59","60 & up" 
                                ));



                                //DATA SET
                                $sheet->fromArray($data_list, null, 'A4', false, false);


                        });
                        }elseif($data_arr["sheet_name"]=="Table 2"){
                            $excel->sheet($data_arr["sheet_name"], function($sheet) use ($data_list) {
                            $var_cell_add = array();
                            $id_cell_add = array();
                              
                                //SET HEADER
                                //  $sheet->cells("A1:D2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                //     $cells->setBorder('thin','thin','thin','thin');}); 
                                
                                $sheet->prependRow(3, array(
                                        "Region", "Province", "RCEF","PSGC Code"));


                                    $row_target = 4;
                                    $varietal_cell_target = "E";
                                    $id_start = 4;

                                    foreach($data_list as $var_data){
                                        
                                        if(isset($var_cell_add[$var_data["seed_variety"]]["col"])){
                                            $var_cell_tar = $var_cell_add[$var_data["seed_variety"]]["col"];
                                        }else{
                                            $variety = $var_data["seed_variety"];
                                            $var_cell_add[$var_data["seed_variety"]]["col"] = $varietal_cell_target;
                                            $var_cell_tar =  $var_cell_add[$var_data["seed_variety"]]["col"];
                                            
                                            $temp_var_cell_tar = $var_cell_tar;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $varietal_cell_target =  $temp_var_cell_tar;
                                            $varietal_cell_target++;

                                           $temp_merge =  $var_cell_tar."1:".$temp_var_cell_tar."1";
                                            $sheet->mergeCells($temp_merge); 
                                            $sheet->cells($temp_merge, function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF"); $cells->setBorder('thin','thin','thin','thin');});
                                            $sheet->cell($var_cell_tar."1", function($cells)use($variety){ $cells->setValue($variety); });
                                                
                                            //MALE
                                            
                                            $temp_var_cell_tar = $var_cell_tar;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            
                                            $temp_merge =  $var_cell_tar."2:".$temp_var_cell_tar."2";
                                            $sheet->mergeCells($temp_merge);
                                            $sheet->cells($temp_merge, function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");$cells->setBorder('thin','thin','thin','thin');}); 
                                            $sheet->cell($var_cell_tar."2", function($cells){ $cells->setValue("Male"); });

                                            $temp_female = $temp_var_cell_tar;
                                            $temp_female++;

                                            $temp_female_2 = $temp_female;
                                            $temp_female_2++;
                                            $temp_female_2++;
                                            
                                            $temp_merge =  $temp_female."2:".$temp_female_2."2";
                                            $sheet->mergeCells($temp_merge);
                                            $sheet->cells($temp_merge, function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");$cells->setBorder('thin','thin','thin','thin');}); 
                                            $sheet->cell($temp_female."2", function($cells){ $cells->setValue("Female"); });
                                           
                                            $sub_header_arr = array("18-29","30-59","60 & up", "18-29","30-59","60 & up");
                                            $sub_data = $var_cell_tar;
                                            foreach($sub_header_arr as $sub_header_data){
                                                $sheet->cell($sub_data."3", function($cells)use($sub_header_data){ $cells->setValue($sub_header_data); });
                                                $sub_data++;

                                            }
                                        }
                                        if(isset($id_cell_add[$var_data["province"]]["col"])){
                                            $row_cell_target = $id_cell_add[$var_data["province"]]["col"];
                                        }else{
                                            $id_cell_add[$var_data["province"]]["col"] = $row_target;
                                            $row_cell_target = $id_cell_add[$var_data["province"]]["col"];
                                            $row_target++;
                                            $sheet->prependRow($row_cell_target, array( $var_data["region"], $var_data["province"],  $var_data["origin"], $var_data["psa_code"]));
                                            
                                        }
                                        
                                         $variety_gad_data = array($var_data["farmer_1_male"],$var_data["farmer_2_male"],$var_data["farmer_3_male"], $var_data["farmer_1_female"],$var_data["farmer_2_female"],$var_data["farmer_3_female"]);
                                          
                                             foreach($variety_gad_data as $variety_data){
                                                 $sheet->cell($var_cell_tar.$row_cell_target, function($cells)use($variety_data){ $cells->setValue($variety_data); });
                                                 $var_cell_tar++;
                                             }
                                    
                                    
                                    }
                                    $sheet->cells("A1:".$varietal_cell_target."3", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                        $cells->setBorder('thin','thin','thin','thin');}); 
                        });
                        }elseif($data_arr["sheet_name"]=="Table 3"){
                            $excel->sheet($data_arr["sheet_name"], function($sheet) use ($data_list) {
                                //SET HEADER
                                $sheet->cells("A1:A2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                    $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->mergeCells("B1:G1");
                                $sheet->cells("B1:G1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("B1", function($cells){ $cells->setValue("Farmer-beneficiaries"); });
                                
                                $sheet->mergeCells("H1:M1");
                                $sheet->cells("H1:M1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("H1", function($cells){ $cells->setValue("No. of Bags"); });

                                $sheet->mergeCells("N1:S1");
                                $sheet->cells("N1:S1", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("N1", function($cells){ $cells->setValue("Area"); });

                               

                                $sheet->mergeCells("B2:D2");
                                $sheet->cells("B2:D2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("B2", function($cells){ $cells->setValue("Male"); });
                                $sheet->mergeCells("E2:G2");
                                $sheet->cells("E2:G2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("E2", function($cells){ $cells->setValue("Female"); });


                                $sheet->mergeCells("H2:J2");
                                $sheet->cells("H2:J2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("H2", function($cells){ $cells->setValue("Male"); });
                                $sheet->mergeCells("K2:M2");
                                $sheet->cells("K2:M2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("K2", function($cells){ $cells->setValue("Female"); });

                                $sheet->mergeCells("N2:P2");
                                $sheet->cells("N2:P2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("N2", function($cells){ $cells->setValue("Male"); });
                                $sheet->mergeCells("Q2:S2");
                                $sheet->cells("Q2:S2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                $cells->setBorder('thin','thin','thin','thin');}); 
                                $sheet->cell("Q2", function($cells){ $cells->setValue("Female"); });

                                $sheet->prependRow(3, array(
                                  "VARIETY","18-29","30-59","60 & up", "18-29","30-59","60 & up","18-29","30-59","60 & up","18-29","30-59","60 & up","18-29","30-59","60 & up","18-29","30-59","60 & up" 
                                ));



                                //DATA SET
                                $sheet->fromArray($data_list, null, 'A4', false, false);


                        });
                        }elseif($data_arr["sheet_name"]=="Table 4"){
                            
                            $excel->sheet($data_arr["sheet_name"], function($sheet) use ($data_list) {

                                

                                $sheet->prependRow(1, array(
                                    "", "", "", "Bags","%","Bags","%","","Bags","%","Bags","%","Bags","%"
                                  ));
                                $sheet->prependRow(2, array(
                                    "Seed Variety", "Total Bags", "%", "Male","Male","Female","Female","","18-29","18-29","30-59","30-59","60 up","60 up"
                                  ));

                                  $sheet->cells("A1:N2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                    $cells->setBorder('thin','thin','thin','thin');}); 

                                //DATA SET
                                $sheet->fromArray($data_list, null, 'A3', false, false);
                            });
                        }elseif($data_arr["sheet_name"]=="Table 5"){
                            $excel->sheet($data_arr["sheet_name"], function($sheet) use ($data_list) {
                            $var_cell_add = array();
                            $id_cell_add = array();
                              
                                //SET HEADER
                                //  $sheet->cells("A1:D2", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                //     $cells->setBorder('thin','thin','thin','thin');}); 
                                
                                $sheet->prependRow(3, array(
                                        "Region", "Province", "RCEF","PSGC Code"));


                                    $row_target = 4;
                                    $varietal_cell_target = "E";
                                    $id_start = 4;

                                    foreach($data_list as $var_data){
                                        
                                        if(isset($var_cell_add[$var_data["seed_variety"]]["col"])){
                                            $var_cell_tar = $var_cell_add[$var_data["seed_variety"]]["col"];
                                        }else{
                                            $variety = $var_data["seed_variety"];
                                            $var_cell_add[$var_data["seed_variety"]]["col"] = $varietal_cell_target;
                                            $var_cell_tar =  $var_cell_add[$var_data["seed_variety"]]["col"];
                                            
                                            $temp_var_cell_tar = $var_cell_tar;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            $varietal_cell_target =  $temp_var_cell_tar;
                                            $varietal_cell_target++;

                                           $temp_merge =  $var_cell_tar."1:".$temp_var_cell_tar."1";
                                            $sheet->mergeCells($temp_merge); 
                                            $sheet->cells($temp_merge, function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF"); $cells->setBorder('thin','thin','thin','thin');});
                                            $sheet->cell($var_cell_tar."1", function($cells)use($variety){ $cells->setValue($variety); });
                                                
                                            //MALE
                                            
                                            $temp_var_cell_tar = $var_cell_tar;
                                            $temp_var_cell_tar++;
                                            $temp_var_cell_tar++;
                                            
                                            $temp_merge =  $var_cell_tar."2:".$temp_var_cell_tar."2";
                                            $sheet->mergeCells($temp_merge);
                                            $sheet->cells($temp_merge, function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");$cells->setBorder('thin','thin','thin','thin');}); 
                                            $sheet->cell($var_cell_tar."2", function($cells){ $cells->setValue("Male"); });

                                            $temp_female = $temp_var_cell_tar;
                                            $temp_female++;

                                            $temp_female_2 = $temp_female;
                                            $temp_female_2++;
                                            $temp_female_2++;
                                            
                                            $temp_merge =  $temp_female."2:".$temp_female_2."2";
                                            $sheet->mergeCells($temp_merge);
                                            $sheet->cells($temp_merge, function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");$cells->setBorder('thin','thin','thin','thin');}); 
                                            $sheet->cell($temp_female."2", function($cells){ $cells->setValue("Female"); });
                                           
                                            $sub_header_arr = array("18-29","30-59","60 & up", "18-29","30-59","60 & up");
                                            $sub_data = $var_cell_tar;
                                            foreach($sub_header_arr as $sub_header_data){
                                                $sheet->cell($sub_data."3", function($cells)use($sub_header_data){ $cells->setValue($sub_header_data); });
                                                $sub_data++;

                                            }
                                        }
                                        if(isset($id_cell_add[$var_data["province"]]["col"])){
                                            $row_cell_target = $id_cell_add[$var_data["province"]]["col"];
                                        }else{
                                            $id_cell_add[$var_data["province"]]["col"] = $row_target;
                                            $row_cell_target = $id_cell_add[$var_data["province"]]["col"];
                                            $row_target++;
                                            $sheet->prependRow($row_cell_target, array( $var_data["region"], $var_data["province"],  $var_data["origin"], $var_data["psa_code"]));
                                            
                                        }
                                        
                                         $variety_gad_data = array($var_data["bags_male_1"],$var_data["bags_male_2"],$var_data["bags_male_3"], $var_data["bags_female_1"],$var_data["bags_female_2"],$var_data["bags_female_3"]);
                                          
                                             foreach($variety_gad_data as $variety_data){
                                                 $sheet->cell($var_cell_tar.$row_cell_target, function($cells)use($variety_data){ $cells->setValue($variety_data); });
                                                 $var_cell_tar++;
                                             }
                                    
                                    
                                    }
                                    $sheet->cells("A1:".$varietal_cell_target."3", function ($cells){$cells->setAlignment('center');$cells->setFontWeight('bold');$cells->setBackground('#00478e');$cells->setFontColor("#FFFFFF");
                                                        $cells->setBorder('thin','thin','thin','thin');}); 
                        });
                        }
                        
                    }                                
                });



        if($type == "dl"){
            return $excel_file->download('xlsx');
        }else if($type == "store"){
            $path = public_path('gad\\');
            if(!is_dir($path)){
                mkdir($path);
            }
                
            
            $totals = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data as t1")
            ->select(
            DB::raw("SUM(t1.farmer_1_male) as male_1"),DB::raw("SUM(t1.farmer_2_male) as male_2"), DB::raw("SUM(t1.farmer_3_male) as male_3"),
            DB::raw("SUM(t1.farmer_1_female) as female_1"),DB::raw("SUM(t1.farmer_2_female) as female_2"), DB::raw("SUM(t1.farmer_3_female) as female_3"),

            DB::raw("SUM(t1.bag_1_male) as bag_m1"),DB::raw("SUM(t1.bag_2_male) as bag_m2"), DB::raw("SUM(t1.bag_3_male) as bag_m3"),
            DB::raw("SUM(t1.bag_1_female) as bag_f1"),DB::raw("SUM(t1.bag_2_female) as bag_f2"), DB::raw("SUM(t1.bag_3_female) as bag_f3"),
            
            DB::raw("SUM(t1.claimed_1_male) as claimed_m1"),DB::raw("SUM(t1.claimed_2_male) as claimed_m2"), DB::raw("SUM(t1.claimed_3_male) as claimed_m3"),
            DB::raw("SUM(t1.claimed_1_female) as claimed_f1"),DB::raw("SUM(t1.claimed_2_female) as claimed_f2"), DB::raw("SUM(t1.claimed_3_female) as claimed_f3")
            )
            ->first();

            if($totals != null){
                DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_summary")
                ->where("date_generated", date("Y-m-d"))
                ->delete();
                DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_summary")
                ->insert([
                    'total_male_1' =>   $totals->male_1,
                    'total_male_2' =>   $totals->male_2,
                    'total_male_3' =>   $totals->male_3,
                    'total_female_1' => $totals->female_1,
                    'total_female_2' => $totals->female_2,
                    'total_female_3' => $totals->female_3,
                    'bags_male_1' =>   $totals->bag_m1,
                    'bags_male_2' =>   $totals->bag_m2,
                    'bags_male_3' =>   $totals->bag_m3,
                    'bags_female_1' => $totals->bag_f1,
                    'bags_female_2' => $totals->bag_f2,
                    'bags_female_3' => $totals->bag_f3,
    
                    'claimed_male_1' =>   $totals->claimed_m1,
                    'claimed_male_2' =>   $totals->claimed_m2,
                    'claimed_male_3' =>   $totals->claimed_m3,
                    'claimed_female_1' => $totals->claimed_f1,
                    'claimed_female_2' => $totals->claimed_f2,
                    'claimed_female_3' => $totals->claimed_f3,
                    'date_generated' => date("Y-m-d")
                ]);  

                $excel_file->store("xlsx", $path);
                return json_encode("store");
            }else{
                return json_encode("failed");
            }

            

           


            
        }


    }
    public function provinceCoordinates(Request $request){
        $coo_data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.province_coordinates")
            ->where("province", "LIKE", $request->province)
            ->first();

        return array("lon" => $coo_data->lon, "lat" => $coo_data->lat);

    }
    public function regionCoordinates(Request $request){
        $coo_data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.region_coordinates")
            ->where("region", "LIKE", $request->region)
            ->first();

        return array("lon" => $coo_data->lon, "lat" => $coo_data->lat);

    }
    public function regionList(Request $request){
        $region = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
            ->groupBy("region")
            ->get();

            return json_encode($region);

    }

    public function getJson($region,$province){
        //$geoJson = file_get_contents(asset("public/geoJson/Province/".$region.".json"));\
        
        if($province != "0"){
            // $base_link = "public/geoJson/ALL_JSON/geojson/municties/hires";
            $base_link = storage_path()."/geoJson/municipality/hires";
            $return_array = array();

            $region_lib =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select("lib_prv.*", "municipality_coordinates.lon", "municipality_coordinates.lat")
            ->join($GLOBALS['season_prefix']."rcep_gad_views.municipality_coordinates", function ($join){
                $join->on("municipality_coordinates.province","=","lib_prv.province");
                $join->on("municipality_coordinates.municipality","=","lib_prv.municipality");
            })

            ->where("lib_prv.regionName", "LIKE", $region)
            ->where("lib_prv.province", "LIKE", $province)
            ->get();
            $province_arr = array();
            $muni_arr = array();
            foreach($region_lib as $data){
                if(!isset($regprovince_arrion_arr[$data->province])){
                    $province_arr[$data->province] = $data->province;
                }
               
                if(!isset($muni_arr[$data->municipality])){
                    $muni_arr[$data->municipality]["name"] = $data->municipality;

                    $muni_arr[$data->municipality]["lon"] = $data->lon;
                    $muni_arr[$data->municipality]["lat"] = $data->lat;
                    // $muni_arr[$data->province]["label_position"] = $data->label_position;
                }
    
            }
        //    dd($muni_arr);
     $dump_arr =array();
     $check_arr = array();
            foreach($province_arr as $listed){
                $file = str_replace(" ","_",$listed);
                $geoJson = file_get_contents($base_link."/".$file.".json");
         
                $geoJson = json_decode($geoJson,true);
              
                foreach($geoJson["features"] as $key => $data2){
                $muni_capital = strtoupper($geoJson["features"][$key]["properties"]["ADM3_EN"])." (".$geoJson["features"][$key]["properties"]["ADM3ALT1EN"].")";
                $muni_with_loc = strtoupper($geoJson["features"][$key]["properties"]["ADM3_EN"])." (".strtoupper($geoJson["features"][$key]["properties"]["ADM3ALT1EN"]).")";
                 

                    if(isset($muni_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM3_EN"])]["name"])){
        $geoJson["features"][$key]["properties"]["lon"] = $muni_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM3_EN"])]["lon"];
        $geoJson["features"][$key]["properties"]["lat"] = $muni_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM3_EN"])]["lat"];
        // $geoJson["features"][$key]["properties"]["label_position"] = $province_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM2_EN"])]["label_position"];
        
                         array_push($return_array, $geoJson["features"][$key]);
                        //  array_push($check_arr, $muni_capital);
                 
                    }
                    else{
                        if(isset($muni_arr[$muni_capital]["name"])){

                            
                            $geoJson["features"][$key]["properties"]["lon"] = $muni_arr[$muni_capital]["lon"];
                            $geoJson["features"][$key]["properties"]["lat"] = $muni_arr[$muni_capital]["lat"];
                            array_push($return_array, $geoJson["features"][$key]);
                        //  array_push($check_arr, $muni_capital);

                        }else{
                            if(isset($muni_arr[$muni_with_loc]["name"])){

                            
                                $geoJson["features"][$key]["properties"]["lon"] = $muni_arr[$muni_with_loc]["lon"];
                                $geoJson["features"][$key]["properties"]["lat"] = $muni_arr[$muni_with_loc]["lat"];
                                array_push($return_array, $geoJson["features"][$key]);
                        //  array_push($check_arr, $muni_capital);

                            }else{
                            
                            
                                array_push($dump_arr, $geoJson["features"][$key]);
                            } 
                         
                         
                            
                        } 
                    }
                }   
            }

            if(count($dump_arr)>0){
                // dd($dump_arr);
            }

            // if(count($muni_arr) != count($check_arr)){
            //    // dd($check_arr);
            // }

            //  dd($return_array);
            return  json_encode(json_decode(json_encode(($return_array), FALSE)));

        }else{


        //DATA PREPARATION
        // $base_link = "public/geoJson/ALL_JSON/geojson/provinces/hires";
        $base_link = storage_path()."/geoJson/provinces/hires";
  
        $return_array = array();
        if($region == "0"){
          $region = "%";
        }

        if($province == "0"){
            $province = "%";
        }

        $region_lib =  DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
        ->select("lib_yield_provinces.*", $GLOBALS['season_prefix']."rcep_gad_views.province_coordinates.lon", "province_coordinates.lat")
        ->join($GLOBALS['season_prefix']."rcep_gad_views.province_coordinates", "province_coordinates.province", "=", "lib_yield_provinces.province")
        ->where("lib_yield_provinces.region", "LIKE", $region)
        ->get();
    


        $region_arr = array();
        $province_arr = array();
        foreach($region_lib as $data){

            if(!isset($region_arr[$data->region])){
                $region_arr[$data->region] = $data->region;
            }
            if(!isset($province_arr[$data->province])){
                


                $province_arr[$data->province]["name"] = $data->province;
                $province_arr[$data->province]["lon"] = $data->lon;
                $province_arr[$data->province]["lat"] = $data->lat;
                //  $province_arr[$data->province]["label_position"] = $data->label_position;
                
                
            }

        }
    

       
                    foreach($region_arr as $listed){
                        $file = str_replace(" ","_",$listed);
                        $geoJson = file_get_contents($base_link."/".$file.".json");
                    
                        $geoJson = json_decode($geoJson,true);
                   
                        foreach($geoJson["features"] as $key => $data2){
                            
                            if(isset($province_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM2_EN"])]["name"])){
                $geoJson["features"][$key]["properties"]["lon"] = $province_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM2_EN"])]["lon"];
                $geoJson["features"][$key]["properties"]["lat"] = $province_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM2_EN"])]["lat"];
                // $geoJson["features"][$key]["properties"]["label_position"] = $province_arr[strtoupper($geoJson["features"][$key]["properties"]["ADM2_EN"])]["label_position"];
                
                                 array_push($return_array, $geoJson["features"][$key]);
                         
                            }
                            
                
                        }
                 
                           //dd($geoJson["features"]);
                    }
        


            return  json_encode(json_decode(json_encode(($return_array), FALSE)));

         //dd(json_encode(json_decode(json_encode(($geoJson["features"]), FALSE))));
         

        }






    }


    public function tblStoredExcel(){
       $list =  DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_summary")
            ->select("date_generated",DB::raw("(total_male_1 + total_male_2 + total_male_3) as total_male"),DB::raw("(total_female_1 + total_female_2 + total_female_3) as total_female"))
            ->groupBy("date_generated")
            ->orderBy("date_generated", "DESC")
            ->get();

        $data = collect($list);

        return Datatables::of($data)
            ->addColumn('title', function($row){
                return $row->date_generated;
            })
            ->addColumn('total_male', function($row){
                return number_format($row->total_male);
            })
            ->addColumn('total_female', function($row){
                return number_format($row->total_female);
            })
            
            ->addColumn('action', function($row){
                $link = asset("public/gad/2022WS_as_of_".$row->date_generated.".xlsx");


                return "<button class='btn btn-success btn-xs' style='margin:1px;' onclick='window.open(".'"'.$link.'"'.")'><i class='fa fa-download' aria-hidden='true'></i> Download</button>";
            })
            ->make(true);



    }

    public function genderPercent(Request $request){
        if($request->type=="sex"){
            if($request->province == "0"){
                $request->province = "%";
            }

            $provinces = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->select("province")
                ->where("regionName", $request->region)
                ->where("province", "LIKE", $request->province."%")
                ->groupBy("province")
                ->get();

            $provinces = json_decode(json_encode($provinces),true);


            $data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data")
                ->select(DB::raw("SUM(farmer_1_male + farmer_2_male + farmer_3_male) as total_male"), DB::raw("SUM(farmer_1_female + farmer_2_female + farmer_3_female) as total_female"), DB::raw("SUM(claimed_1_male+claimed_2_male+claimed_3_male+claimed_1_female+claimed_2_female+claimed_3_female) as est_area"), DB::raw("SUM(farmer_1_male+farmer_1_female) as cat1"),DB::raw("SUM(farmer_2_male+farmer_2_female) as cat2"),DB::raw("SUM(farmer_3_male+farmer_3_female) as cat3"))
                
                ->whereIn("province", $provinces)
                ->first();
            $total = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data")
                ->select(DB::raw("SUM(farmer_1_male + farmer_2_male + farmer_3_male+farmer_1_female + farmer_2_female + farmer_3_female +total_invalid_age ) as total_farmer"))
                ->first();
                if($data != null){
                        if($data->total_male ==0 && $data->total_female == 0){
                            $ala = "0";
                            $cat1 = "0";
                            $cat2 = "0";
                            $cat3 = "0";
                        }else{
                            $ala = ($data->est_area / ($data->total_male + $data->total_female))*100;
                            $ala = number_format($ala,2); 
                         
                            $cat1 = number_format(($data->cat1/($data->total_male+$data->total_female))*100)."%";
                            $cat2 = number_format(($data->cat2/($data->total_male+$data->total_female))*100)."%";
                            $cat3 = number_format(($data->cat3/($data->total_male+$data->total_female))*100)."%";

                        }
                    
                    return json_encode(array(
                       "male" => number_format(($data->total_male / $total->total_farmer)*100)."%",
                       "female" => number_format(($data->total_female / $total->total_farmer)*100)."%",
                       "est_area" => number_format($data->est_area,2)." (ha)",
                       "male_bene" => $data->total_male,
                       "female_bene" => $data->total_female,
                       "ala" => $ala,
                       "cat1" => $cat1,
                       "cat2" => $cat2,
                       "cat3" => $cat3,
                       
                    ));
                }else{
                    return json_encode(array(
                        "male" =>"0%",
                        "female" => "0%",
                        "est_area" => "-",
                        "male_bene" => 0,
                        "female_bene" => 0,
                        "ala" => "-",
                        "cat1" => "-",
                       "cat2" => "-",
                       "cat3" => "-",
 
                     ));
                }

        }
    }

    public function gad_province($region)
    {
        $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        ->select("lib_prv.province")
        ->where("regionName", $region)
        ->join($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces","lib_prv.province","=","lib_yield_provinces.province")
        ->groupBy("lib_prv.province")
        ->orderBy("lib_prv.province")
        
        ->get();
        
        return json_encode($province);

    }


    public function index(){
       
        // $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
        //     ->groupBy("regionName")
        //     ->orderBy("region_sort")
        //     ->get();
        //     dd($region);

        // $dash_data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data")
        //         ->select( DB::raw("SUM(male)as total_male"),
        //         DB::raw("(SUM(male)/SUM(total_farmer)) * 100 as percent_male"),
        //         DB::raw("SUM(female)as total_female"),
        //         DB::raw("(SUM(female)/SUM(total_farmer)) * 100 as percent_female"),
        //         DB::raw("SUM(claimed_1_male + claimed_2_male + claimed_3_male) as claimed_male"),
        //         DB::raw("SUM(claimed_1_female + claimed_2_female + claimed_3_female) as claimed_female")
        //         // DB::raw("SUM(farmer_1_male+farmer_1_female) as overall_cat1"),
        //         // DB::raw("SUM(farmer_2_male+farmer_2_female) as overall_cat2"),
        //         // DB::raw("SUM(farmer_3_male+farmer_3_female) as overall_cat3")

        //         )
        //         ->first();
        
        // $pythonPath = 'C://Python312//python.exe';

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
        // $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

        $scriptPath = base_path('app/Http/PyScript/gad-report/gad-index.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];

        $escapedSsn = escapeshellarg($ssn);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn";
    
        // Create a new process
        $process = new Process($command);
        
        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            $region = json_decode(json_encode($result['regions']));
            $dash_data = json_decode(json_encode($result));
            unset($dash_data->regions);
            return view("dashboard.gad_dashboard", 
            compact(
                "dash_data", 
                "region"
            ));

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }
    }

    


    public function gadData(Request $request){
    // if($request->type == "stacked"){

    //     // $dash_data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_report")
    //     // ->select(
    //     // DB::raw(" ROUND((SUM(farmer_1_male) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female))*100,2) as male_1_percent"),
    //     // DB::raw(" ROUND((SUM(farmer_2_male) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female))*100,2) as male_2_percent"),
    //     // DB::raw(" ROUND((SUM(farmer_3_male) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female))*100,2) as male_3_percent"),
    //     // DB::raw(" ROUND((SUM(farmer_1_female) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female))*100,2) as female_1_percent"),
    //     // DB::raw(" ROUND((SUM(farmer_2_female) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female))*100,2) as female_2_percent"),
    //     // DB::raw(" ROUND((SUM(farmer_3_female) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female))*100,2) as female_3_percent")
    //     // )
    //     // ->first();

    //      $dash_data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data")
    //     ->select(
    //     DB::raw(" ROUND((SUM(farmer_1_male) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female + total_invalid_age))*100,2) as male_1_percent"),
    //     DB::raw(" ROUND((SUM(farmer_2_male) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female + total_invalid_age))*100,2 ) as male_2_percent"),
    //     DB::raw(" ROUND((SUM(farmer_3_male) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female + total_invalid_age))*100,2) as male_3_percent"),
    //     DB::raw(" ROUND((SUM(farmer_1_female) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female + total_invalid_age))*100,2) as female_1_percent"),
    //     DB::raw(" ROUND((SUM(farmer_2_female) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female + total_invalid_age))*100,2) as female_2_percent"),
    //     DB::raw(" ROUND((SUM(farmer_3_female) / SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female + total_invalid_age))*100,2) as female_3_percent")
    //     )
    //     ->first();


    //     return json_encode($dash_data);
    // }
    // elseif($request->type == "bar_land"){
    //     $dash_data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_data")
    //     ->select(
        
    //     DB::raw(" SUM(farmer_1_male + farmer_2_male + farmer_3_male+ farmer_1_female + farmer_2_female + farmer_3_female  + total_invalid_age) total_farmer"),
        
    //     DB::raw("SUM(farmer_1_male ) as farmer_male_1"),
    //     DB::raw("SUM(farmer_2_male ) as farmer_male_2"),
    //     DB::raw("SUM(farmer_3_male ) as farmer_male_3"),

    //     DB::raw("SUM(farmer_1_female ) as farmer_female_1"),
    //     DB::raw("SUM(farmer_2_female ) as farmer_female_2"),
    //     DB::raw("SUM(farmer_3_female ) as farmer_female_3"),
        
    //     DB::raw("SUM(farmer_1_male + farmer_1_female ) as farmer_1"),
    //     DB::raw("SUM(farmer_2_male + farmer_2_female ) as farmer_2"),
    //     DB::raw("SUM(farmer_3_male + farmer_3_female ) as farmer_3"),
        
    //     DB::raw("SUM(claimed_1_male ) as claimed_male_1"),
    //     DB::raw("SUM(claimed_2_male ) as claimed_male_2"),
    //     DB::raw("SUM(claimed_3_male ) as claimed_male_3"),

    //     DB::raw("SUM(claimed_1_female ) as claimed_female_1"),
    //     DB::raw("SUM(claimed_2_female ) as claimed_female_2"),
    //     DB::raw("SUM(claimed_3_female ) as claimed_female_3"),
        
    //     DB::raw("SUM(claimed_1_male + claimed_1_female ) as claimed_1"),
    //     DB::raw("SUM(claimed_2_male + claimed_2_female ) as claimed_2"),
    //     DB::raw("SUM(claimed_3_male + claimed_3_female ) as claimed_3")
    //     )
    //     ->first();


    //     return json_encode($dash_data);
    // }
    // $pythonPath = 'C://Python312//python.exe';

    $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
    // $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

    $scriptPath = base_path('app/Http/PyScript/gad-report/gad-charts.py');

    // Escape the arguments
    $ssn = $GLOBALS["season_prefix"];
    $type = $request->type;

    $escapedSsn = escapeshellarg($ssn);
    $escapedType = escapeshellarg($type);

    // Construct the command with arguments as a single string
    $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedType";

    // Create a new process
    $process = new Process($command);
    
    try {
        // Run the process
        $process->mustRun();

        $output = $process->getOutput();
        $result = json_decode($output, true);

        $result = json_encode($result);
        return $result;
    } catch (ProcessFailedException $exception) {
        // Handle the exception
        echo $exception->getMessage();
    }

    }
     
    public function dashboardtbl(){
        // $data = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")
        // ->select( "seed_variety",
        //     DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 ) as total_bag"),
        //     DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3) as male_bag"), 
        //     DB::raw("SUM( bags_female_1 +bags_female_2 +bags_female_3) as female_bag"),
        //     DB::raw("SUM(bags_male_1 + bags_female_1) as cat1_bag"),
        //     DB::raw("SUM(bags_male_2 + bags_female_2) as cat2_bag"), 
        //     DB::raw("SUM(bags_male_3 + bags_female_3) as cat3_bag") 
        // )
        // ->groupBy("seed_variety")
        // ->orderBy(DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 )"), "DESC")
        // ->get();

        // $data_cent = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")
        //     ->select( 
        //     DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 ) as total_bag"),
        //     DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3) as male_bag"), 
        //     DB::raw("SUM( bags_female_1 +bags_female_2 +bags_female_3) as female_bag"),
        //     DB::raw("SUM(bags_male_1 + bags_female_1) as cat1_bag"),
        //     DB::raw("SUM(bags_male_2 + bags_female_2) as cat2_bag"), 
        //     DB::raw("SUM(bags_male_3 + bags_female_3) as cat3_bag")
        // )
        // ->orderBy(DB::raw("SUM(bags_male_1 +bags_male_2 +bags_male_3 + bags_female_1 +bags_female_2 +bags_female_3 )"), "DESC")
        // ->first();
        
        //14
       $color_fam = array(
        "63BE7B","81C77D","A4D17F","B3D680","BDD881","DAE182","F4E884","FDCF7E","FCC57C","FBAF78","FBA676","FBA175","F8786D","F8696B");

        


        // $tbl_arr = array();
        // foreach($data as $data_info){
        //     array_push($tbl_arr, array(
        //         "seed_variety" => $data_info->seed_variety,
        //         "total_bag" => number_format($data_info->total_bag),
        //         "cent_bag" => number_format(($data_info->total_bag / $data_cent->total_bag)*100,2)."%" ,
        //         "male_bag" => number_format($data_info->male_bag) ,
        //         "male_cent" => number_format(($data_info->male_bag / $data_cent->male_bag)*100,2)."%" ,
        //         "female_bag" => number_format($data_info->female_bag) ,
        //         "female_cent" => number_format(($data_info->female_bag / $data_cent->female_bag)*100,2)."%" ,
        //         "blank" => "" ,
        //         "cat1_bag" => number_format($data_info->cat1_bag) ,
        //         "cat1_cent" => number_format(($data_info->cat1_bag / $data_cent->cat1_bag)*100,2)."%" ,
        //         "cat2_bag" => number_format($data_info->cat2_bag) ,
        //         "cat2_cent" => number_format(($data_info->cat2_bag / $data_cent->cat2_bag)*100,2)."%" ,
        //         "cat3_bag" => number_format($data_info->cat3_bag) ,
        //         "cat3_cent" => number_format(($data_info->cat3_bag / $data_cent->cat3_bag)*100,2)."%" ,
        //     ));
        // }

        // dd($tbl_arr);

        // $pythonPath = 'C://Python312//python.exe';

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
        // $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

        $scriptPath = base_path('app/Http/PyScript/gad-report/gad-seed-variety.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];

        $escapedSsn = escapeshellarg($ssn);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn";
    
        // Create a new process
        $process = new Process($command);
        
        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $result = json_decode($output, true);

            $tbl_arr = array();
            foreach($result as $data_info){
                array_push($tbl_arr, array(
                    "seed_variety" => $data_info["seed_variety"],
                    "total_bag" => number_format($data_info["total_bags"]),
                    "cent_bag" => number_format($data_info['cent_bag'], 2)."%",
                    "male_bag" => number_format($data_info["male_bag"]),
                    "male_cent" => number_format($data_info['male_cent'], 2)."%",
                    "female_bag" => number_format($data_info["female_bag"]),
                    "female_cent" => number_format($data_info['female_cent'], 2)."%",
                    "blank" => "",
                    "cat1_bag" => number_format($data_info["cat1_bag"]),
                    "cat1_cent" => number_format($data_info['cat1_cent'], 2)."%",
                    "cat2_bag" => number_format($data_info["cat2_bag"]),
                    "cat2_cent" => number_format($data_info['cat2_cent'], 2)."%",
                    "cat3_bag" => number_format($data_info["cat3_bag"]),
                    "cat3_cent" => number_format($data_info['cat3_cent'], 2)."%"
                ));
            }


            $data = collect($tbl_arr);
            return Datatables::of($data)->make(true);

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }
    }

  

    public function dashboardtbl_old(){
        
        $data = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")
                ->orderby("id", "ASC")
                ->orderby("variety", "ASC")
                ->orderby("group_name", "ASC")
                ->groupBy("variety")
                ->get();

                //dd($data);
                $data_arr = array();
            foreach ($data as $key => $variety) {
               
               $datalist = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")
               ->where("variety", $variety->variety)
                ->orderby("id", "ASC")
                ->orderby("variety", "ASC")
                ->orderby("group_name", "ASC")
                ->get();

                foreach ($datalist as $key => $value) {
                     if($value->group_name =="age_male" && $value->group_tag=="1"){
                    $age_male1 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="age_male" && $value->group_tag=="2"){
                        $age_male2 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="age_male" && $value->group_tag=="3"){
                        $age_male3 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="age_female" && $value->group_tag=="1"){
                        $age_female1 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="age_female" && $value->group_tag=="2"){
                        $age_female2 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="age_female" && $value->group_tag=="3"){
                        $age_female3 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="area_male" && $value->group_tag=="1"){
                    $area_male1 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="area_male" && $value->group_tag=="2"){
                        $area_male2 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="area_male" && $value->group_tag=="3"){
                        $area_male3 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="area_female" && $value->group_tag=="1"){
                        $area_female1 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="area_female" && $value->group_tag=="2"){
                        $area_female2 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }

                    if($value->group_name =="area_female" && $value->group_tag=="3"){
                        $area_female3 = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->where("variety", $value->variety)->where("group_name", $value->group_name)->where("group_tag", $value->group_tag)->sum("value");
                    }
                }


               

                    array_push($data_arr, array(
                        "seed_variety" => $variety->variety,
                        "am1" => number_format($age_male1),
                        "am2" => number_format($age_male2),
                        "am3" => number_format($age_male3),
                        "af1" => number_format($age_female1),
                        "af2" => number_format($age_female2),
                        "af3" => number_format($age_female3),
                        "rm1" => number_format($area_male1),
                        "rm2" => number_format($area_male2),
                        "rm3" => number_format($area_male3),
                        "rf1" => number_format($area_female1),
                        "rf2" => number_format($area_female2),
                        "rf3" => number_format($area_female3),  
                    ));


            }




        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->make(true);
                
    }


    public function graphLoad(Request $request){
        
        $amylose_arr = array(
            "H" => 3,
            "I" => 2,
            "L" => 1
        );
        $variety_arr = array();

        $filter = "tblgadreport.".$request->filter;
        // $variety_list = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport")
        //                 ->select("variety")
        //                 ->where("variety", "!=", "TOTAL")
        //                 ->groupBy("variety")
        //                 ->orderBy("variety")
        //                 ->get();

        $variety_list = DB::table($GLOBALS['season_prefix']."rcep_gad_views.gad_variety")  
            ->select("seed_variety as variety")
            ->groupBy("seed_variety")
            ->orderBy("seed_variety")
            ->get();
       // dd($sv_main);

        foreach ($variety_list as $variety) {
                $mf = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport")
                                ->select("variety", DB::raw("sum(age_m1) as bg1"), DB::raw("sum(age_m2) as bg2"),DB::raw("sum(age_m3) as bg3"),DB::raw("sum(age_f1) as bg4"),DB::raw("sum(age_f2) as bg5"),DB::raw("sum(age_f3) as bg6"))
                                ->where("variety", $variety->variety)
                                ->groupBy("variety")
                                ->first();

                $amylose = DB::table("seed_seed.seed_characteristics")->select("amylose")->where("variety", $variety->variety)->first();
                    if(count($amylose)>0){$amylose = substr($amylose->amylose, -1);}
                    if(!isset($amylose_arr[$amylose]))$amylose=0; else $amylose=$amylose_arr[$amylose];                    

            if(count($mf)>0){
                array_push($variety_arr,array(
                    "variety" => $variety->variety,
                    "amylose" => $amylose,
                    "male" => $mf->bg1 + $mf->bg2 + $mf->bg3,
                    "female" => $mf->bg4 + $mf->bg5 + $mf->bg6,
                ));
            }else{
                array_push($variety_arr,array(
                    "variety" => $variety->variety,
                    "amylose" => $amylose,
                    "male" => 0,
                    "female" =>0,
                ));
            }

                
        }



     
        //dd($return);
        return $variety_arr;

    }






    public function processGadData(){
        //AS_OF
        // $request_date = "2025-07-01";
         $request_date = date("Y-m-d");
         $region = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                //->where("region", "ILOCOS")
                ->groupBy("region")
                ->orderby("prv", "ASC")
                ->get();


        $gadArray = array();
        $totals_arr = array();
        $male = 0;
        $female = 0;
        foreach ($region as $region)
        {
            $province = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("region", $region->region)
                    //->where("province", "BOHOL")
                    ->groupBy("province")
                    ->orderby("prv", "ASC")
                    ->get();
                  

            foreach ($province as $province) {
                $varietyList = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                            ->where("region", $region->region)
                            ->where("province", $province->province)
                            ->groupBy("seedVariety")
                            ->orderby("seedVariety", "ASC")
                            ->get();
                    //FOR TOTALS
                    $maleGroup1_total = 0;
                    $maleGroup2_total = 0;
                    $maleGroup3_total = 0;                    
                    $femaleGroup1_total = 0;
                    $femaleGroup2_total = 0;
                    $femaleGroup3_total = 0;
                    $maleArea1_total = 0.00;
                    $maleArea2_total = 0.00;
                    $maleArea3_total = 0.00;
                    $femaleArea1_total = 0.00;
                    $femaleArea2_total = 0.00;
                    $femaleArea3_total = 0.00;
                    $maleBags1_total = 0.00;
                    $maleBags2_total = 0.00;
                    $maleBags3_total = 0.00;
                    $femaleBags1_total = 0.00;
                    $femaleBags2_total = 0.00;
                    $femaleBags3_total = 0.00;

                foreach ($varietyList as $variety) {
                   
               
                    
                    $prv = $GLOBALS['season_prefix']."prv_".substr($province->prv, 0,4);
               
                        // $released = DB::table($prv.".released")
                        //     ->select("farmer_profile.sex as sex", "released.birthdate as bday", "farmer_profile.actual_area as farmer_area", "released.bags as bags")
                        //     ->join($prv.".farmer_profile", function($join){
                        //     $join->on("farmer_profile.farmerID", "=", "released.farmer_id");
                        //     $join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
                        //     })
                    
                        //     ->where("released.seed_variety", $variety->seedVariety)
                        //     //->where("released.seed_variety", "NSIC Rc 354")
                        //     ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') <=  STR_TO_DATE(".$request_date.", '%Y-%m-%d')")
                        //     ->where("released.province", $province->province)
                        //     ->get();


                            $released = DB::table($prv.".new_released")
                            ->where("new_released.seed_variety", $variety->seedVariety)
                            //->select(DB::raw("STR_TO_DATE(date_released, '%Y-%m-%d')"))
                            // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') <=  STR_TO_DATE(".$request_date.", '%Y-%m-%d')")
                                ->get();

                            // if(count($released)>0){
                            // //    dd($released);
                            // }
                    //FOR LIST
                    // dd($released);
                    $maleGroup1 = 0;
                    $maleGroup2 = 0;
                    $maleGroup3 = 0;                    
                    $femaleGroup1 = 0;
                    $femaleGroup2 = 0;
                    $femaleGroup3 = 0;

                    $maleArea = 0.00;
                    $femaleArea = 0.00;
                    $maleArea1 = 0.00;
                    $maleArea2 = 0.00;
                    $maleArea3 = 0.00;
                    $femaleArea1 = 0.00;
                    $femaleArea2 = 0.00;
                    $femaleArea3 = 0.00;

                    $maleBags1 = 0.00;
                    $maleBags2 = 0.00;
                    $maleBags3 = 0.00;
                    $femaleBags1 = 0.00;
                    $femaleBags2 = 0.00;
                    $femaleBags3 = 0.00;



                
                   if(count($released)>0){
                    //if($variety->seedVariety=="NSIC Rc 160"){dd($released);}
                        foreach ($released as $released_data) {
                            $released_date = date("Y-m-d", strtotime($released_data->date_released));
                            
                            if(strtotime($released_date) <= strtotime($request_date)){
                                if(strtoupper(substr($released_data->sex, 0,1)) == "F"){
                               
                                    $today = date("Y-m-d");
                                    if($released_data->birthdate==""){
                                       continue; 
                                    }else{
                                        $female++;
                                        $femaleArea += $released_data->claimed_area;
                                        $bday = date("Y-m-d", strtotime($released_data->birthdate));
                                    }
                                    $age = date_diff(date_create($bday), date_create('now'))->y;
    
                                    if($age<=29){
                                        $femaleGroup1++;
                                        $femaleArea1 += $released_data->claimed_area;
                                        $femaleBags1 += intval($released_data->bags_claimed);
                                    }elseif($age>29 && $age <=59){
                                        //dd(intval($released_data->farmer_area));
    
                                        $femaleGroup2++;
                                        $femaleArea2 += $released_data->claimed_area;
                                        $femaleBags2 += intval($released_data->bags_claimed);
                                    }elseif($age>=60){
                                        $femaleGroup3++;
                                        $femaleArea3 += $released_data->claimed_area;
                                        $femaleBags3 += intval($released_data->bags_claimed);
                                    }else{
                                        dd($age);
                                    }
                                }elseif(strtoupper(substr($released_data->sex, 0,1)) == "M"){
                                    
                                    $today = date("Y-m-d");
                                    if($released_data->birthdate==""){
                                      continue;
                                    }else{
                                        $male++;
                                        $maleArea += $released_data->claimed_area;
                                        $bday = date("Y-m-d", strtotime($released_data->birthdate));
                                    }
                                    $age = date_diff(date_create($bday), date_create('now'))->y;
    
                                    if($age<=29){
                                        $maleGroup1++;
                                        $maleArea1 += $released_data->claimed_area;
                                        $maleBags1 += intval($released_data->bags_claimed);
                                    }elseif($age>29 && $age <=59){
                                        $maleGroup2++;
                                        $maleArea2 += $released_data->claimed_area;
                                        $maleBags2 += intval($released_data->bags_claimed);
                                    }elseif($age>=60){
                                        $maleGroup3++;
                                        $maleArea3 += $released_data->claimed_area;
                                        $maleBags3 += intval($released_data->bags_claimed);
                                    }else{
                                            dd($age);
                                    }
                                }else{
                                    continue;
                                }
                            }else{
                                continue;
                            }
                            


                          
                        }
                   }
                //COLECT DATA
                   array_push($gadArray, array(
                    "region" => $province->region,
                    "province" => $province->province,
                    "variety" => $variety->seedVariety,
                    "age_m1" => $maleGroup1,
                    "age_m2" => $maleGroup2,
                    "age_m3" => $maleGroup3,
                    "age_f1" => $femaleGroup1,
                    "age_f2" => $femaleGroup2,
                    "age_f3" => $femaleGroup3,
                    //"totalfarmer" => $male+$female,
                    "area_m1" => $maleArea1,
                    "area_m2" => $maleArea2,
                    "area_m3" => $maleArea3,
                    "area_f1" => $femaleArea1,
                    "area_f2" => $femaleArea2,
                    "area_f3" => $femaleArea3,

                    "bags_m1" => $maleBags1,
                    "bags_m2" => $maleBags2,
                    "bags_m3" => $maleBags3,
                    "bags_f1" => $femaleBags1,
                    "bags_f2" => $femaleBags2,
                    "bags_f3" => $femaleBags3,

                   // "totalArea" => $maleArea+$femaleArea,
                   )); 
             

                   for ($i=1; $i <=3 ; $i++) { 
                     if($i==1)$male_group=$maleGroup1;elseif($i==2)$male_group=$maleGroup2;elseif($i==3)$male_group=$maleGroup3;
                       if(isset($totals_arr[$variety->seedVariety]["age_male"][$i])){
                        $totals_arr[$variety->seedVariety]["age_male"][$i] = intval($totals_arr[$variety->seedVariety]["age_male"][$i]) + intval($male_group);
                       }else{
                        $totals_arr[$variety->seedVariety]["age_male"][$i] = $male_group;
                       }



                     if($i==1)$female_group=$femaleGroup1;elseif($i==2)$female_group=$femaleGroup2;elseif($i==3)$female_group=$femaleGroup3;
                       if(isset($totals_arr[$variety->seedVariety]["age_female"][$i])){
                        $totals_arr[$variety->seedVariety]["age_female"][$i] = intval($totals_arr[$variety->seedVariety]["age_female"][$i]) + intval($female_group);
                       }else{
                        $totals_arr[$variety->seedVariety]["age_female"][$i] = $female_group;
                       }

                       //AREA --------------
                     if($i==1)$male_group=$maleArea1;elseif($i==2)$male_group=$maleArea2;elseif($i==3)$male_group=$maleArea3;
                       if(isset($totals_arr[$variety->seedVariety]["area_male"][$i])){
                       $totals_arr[$variety->seedVariety]["area_male"][$i] = $totals_arr[$variety->seedVariety]["area_male"][$i] + $male_group;
                       }else{
                        $totals_arr[$variety->seedVariety]["area_male"][$i] = $male_group;
                       }

                     if($i==1)$female_group=$femaleArea1;elseif($i==2)$female_group=$femaleArea2;elseif($i==3)$female_group=$femaleArea3;
                       if(isset($totals_arr[$variety->seedVariety]["area_female"][$i])){
                        $totals_arr[$variety->seedVariety]["area_female"][$i] = $totals_arr[$variety->seedVariety]["area_female"][$i] + $female_group;
                       }else{
                        $totals_arr[$variety->seedVariety]["area_female"][$i] = $female_group;
                       }
                    
                       //BAGS --------------
                     if($i==1)$male_group=$maleBags1;elseif($i==2)$male_group=$maleBags2;elseif($i==3)$male_group=$maleBags3;
                       if(isset($totals_arr[$variety->seedVariety]["bags_male"][$i])){
                       $totals_arr[$variety->seedVariety]["bags_male"][$i] = intval($totals_arr[$variety->seedVariety]["bags_male"][$i]) + intval($male_group);
                       }else{
                        $totals_arr[$variety->seedVariety]["bags_male"][$i] = $male_group;
                       }

                     if($i==1)$female_group=$femaleBags1;elseif($i==2)$female_group=$femaleBags2;elseif($i==3)$female_group=$femaleBags3;
                       if(isset($totals_arr[$variety->seedVariety]["bags_female"][$i])){
                        $totals_arr[$variety->seedVariety]["bags_female"][$i] = intval($totals_arr[$variety->seedVariety]["bags_female"][$i]) + intval($female_group);
                       }else{
                        $totals_arr[$variety->seedVariety]["bags_female"][$i] = $female_group;
                       }


                   }

                    //TOTALS
                    $maleGroup1_total += $maleGroup1;
                    $maleGroup2_total += $maleGroup2;
                    $maleGroup3_total += $maleGroup3;                    
                    $femaleGroup1_total += $femaleGroup1;
                    $femaleGroup2_total += $femaleGroup2;
                    $femaleGroup3_total += $femaleGroup3;
                    $maleArea1_total += $maleArea1;
                    $maleArea2_total += $maleArea2;
                    $maleArea3_total += $maleArea3;
                    $femaleArea1_total += $femaleArea1;
                    $femaleArea2_total += $femaleArea2;
                    $femaleArea3_total += $femaleArea3;
                    $maleBags1_total += $maleBags1;
                    $maleBags2_total += $maleBags2;
                    $maleBags3_total += $maleBags3;
                    $femaleBags1_total += $femaleBags1;
                    $femaleBags2_total += $femaleBags2;
                    $femaleBags3_total += $femaleBags3;
                }





                     //COLECT DATA
                   array_push($gadArray, array(
                    "region" => $province->region,
                    "province" => $province->province,
                    "variety" => "TOTAL",
                    "age_m1" => $maleGroup1_total,
                    "age_m2" => $maleGroup2_total,
                    "age_m3" => $maleGroup3_total,
                    "age_f1" => $femaleGroup1_total,
                    "age_f2" => $femaleGroup2_total,
                    "age_f3" => $femaleGroup3_total,
                    //"totalfarmer" => $male+$female,
                    "area_m1" => $maleArea1_total,
                    "area_m2" => $maleArea2_total,
                    "area_m3" => $maleArea3_total,
                    "area_f1" => $femaleArea1_total,
                    "area_f2" => $femaleArea2_total,
                    "area_f3" => $femaleArea3_total,
                   // "totalArea" => $maleArea+$femaleArea,

                    "bags_m1" => $maleBags1_total,
                    "bags_m2" => $maleBags2_total,
                    "bags_m3" => $maleBags3_total,
                    "bags_f1" => $femaleBags1_total,
                    "bags_f2" => $femaleBags2_total,
                    "bags_f3" => $femaleBags3_total,
                   )); 

            }
        }

        //dd($gadArray);
            if(count($gadArray)>0){
                DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport")->truncate();
                DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport")->insert($gadArray);
                   
            }

            //dd($totals_arr);
            if(count($totals_arr)>0){
                    DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->truncate();
                   foreach ($totals_arr as $key => $value) {
                        $variety = $key;
                            foreach ($value as $key => $group) {
                                $group_name = $key;
                                    foreach ($group as $key => $val) {
                                           DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")->insert([
                                            "variety" => $variety,
                                            "group_name" => $group_name,
                                            "group_tag" => $key,
                                            "value" => $val 
                                           ]); 
                                    }
                            }
                   }
            }
             $this->exportGadReportExcel(0,$request_date);
    }



    public function exportGadReportExcel($process_type, $request_date){

            $gadArray = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport")
                ->select("region", "province", "variety", "age_m1", "age_m2", "age_m3", "age_f1", "age_f2", "age_f3", "area_m1", "area_m2", "area_m3", "area_f1", "area_f2", "area_f3", "bags_m1", "bags_m2", "bags_m3", "bags_f1", "bags_f2", "bags_f3")
                ->orderby("id", "ASC")
                ->orderby("region", "ASC")
                ->orderby("province", "ASC")
                ->get();
            //dd($gadArray);

            $db_totals = DB::table($GLOBALS['season_prefix']."rcep_reports.tblgadreport_totals")
                    ->orderby("id", "ASC")
                    ->orderby("variety", "ASC")
                    ->get();
                $totals_arr = array();
                $male = 0;
                $female = 0;
                $maleGroup1 = 0;
                $maleGroup2 = 0;
                $maleGroup3 = 0;                    
                $femaleGroup1 = 0;
                $femaleGroup2 = 0;
                $femaleGroup3 = 0;

                    foreach ($db_totals as $total) {
                        if(isset($totals_arr[$total->variety][$total->group_name][$total->group_tag])){
                        $totals_arr[$total->variety][$total->group_name][$total->group_tag] = intval($totals_arr[$total->variety][$total->group_name][$total->group_tag]) + intval($total->value);
                       }else{
                       $totals_arr[$total->variety][$total->group_name][$total->group_tag] = $total->value;
                       }



                        if($total->group_name == "age_male"){
                            $male += $total->value;
                            if($total->group_tag=="1"){$maleGroup1 +=$total->value;}elseif($total->group_tag=="2"){$maleGroup2 +=$total->value;}if($total->group_tag=="3"){$maleGroup3 +=$total->value;}
                        }
                        elseif($total->group_name == "age_female"){
                            $female += $total->value;
                            if($total->group_tag=="1"){$femaleGroup1 +=$total->value;}elseif($total->group_tag=="2"){$femaleGroup2 +=$total->value;}if($total->group_tag=="3"){$femaleGroup3 +=$total->value;}
                        }
                    
                        $as_of = $total->date_processed;
                    }

//                    dd($female);

            $path = public_path('gad\\');

            $excel_name = "GAD_REPORT_TOTAL_FARMER_GROUP"."_".$request_date;

            if(!is_dir($path)){
                mkdir($path);
            }


            $path = public_path('gad\\');
                

         $excel_data = json_decode(json_encode($gadArray), true); //convert collection to associative array to be converted to excel
            $excel_file =  Excel::create($excel_name, function($excel) use ($excel_data,$male,$female,$totals_arr,$maleGroup1,$maleGroup2,$maleGroup3,$femaleGroup1,$femaleGroup2,$femaleGroup3, $as_of) {
                $excel->sheet("FARMER_GROUP_PER_VARIETY", function($sheet) use ($excel_data,$male,$female,$totals_arr,$maleGroup1,$maleGroup2,$maleGroup3,$femaleGroup1,$femaleGroup2,$femaleGroup3, $as_of) {
                  
                    $sheet->prependRow(1, array(
                        "RCEF-SMS GAD DATASET") 
                    );
                    $sheet->mergeCells("A1:U1");
                    $sheet->cells("A1:U1", function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    $cells->setFontSize(16);
                                }); 


                    $sheet->mergeCells("D3:U3");
                    $sheet->cells("D3:U3", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#ddcedf');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("D3", function($cells){
                        $cells->setValue("DATASET");
                    });


                     $sheet->mergeCells("D4:F4");
                     $sheet->cells("D4:F4", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("D4", function($cells){
                        $cells->setValue("AGE GROUP (M)");
                    });
                     $sheet->mergeCells("G4:I4");
                     $sheet->cells("G4:I4", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("G4", function($cells){
                        $cells->setValue("AGE GROUP (F)");
                    });
                     $sheet->mergeCells("J4:L4");
                     $sheet->cells("J4:L4", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("J4", function($cells){
                        $cells->setValue("AREA (M)");
                    });
                     $sheet->mergeCells("M4:O4");
                     $sheet->cells("M4:O4", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                      $sheet->cell("M4", function($cells){
                        $cells->setValue("AREA (F)");
                    });


                      //BAGS
                      $sheet->mergeCells("P4:R4");
                     $sheet->cells("P4:R4", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("P4", function($cells){
                        $cells->setValue("BAGS (M)");
                    });
                     $sheet->mergeCells("S4:U4");
                     $sheet->cells("S4:U4", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                      $sheet->cell("S4", function($cells){
                        $cells->setValue("BAGS (F)");
                    });






                    $sheet->prependRow(5, array(
                        "REGION", "PROVINCE", "SEED VARIETY","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60"
                    ));

                    $sheet->cells("A5:B5", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cells("C5:C5", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#62c95d');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 


                    $sheet->setFreeze('A6');



//END OF HEADER
        //$sheet->fromArray($excel_data, null, 'A1', false, false);
                    $row = 6;
                    $region = "";
                    $province ="";
                    $variety ="";
                    foreach ($excel_data as $key => $value) {
                        if($region == $value["region"]){
                                $value["region"] = "";
                                if($province == $value["province"]){
                                    $value["province"]="";   
                                }else{
                                    $row1 = $row;
                                    $province = $value["province"];
                                    $sheet->cells("B".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#fadfb0');
                                        $cells->setBorder('thin','thin','thin','thin');
                                        }); 
                                } 
                            }else{
                                $region = $value["region"];
                                $province = $value["province"];
                                 $row1 = $row;
                                $sheet->cells("A".$row.":"."B".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                            }


                            if($value["variety"]!="TOTAL"){
                                $sheet->cells("C".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#62c95d');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 
                                $sheet->cells("D".$row.":F".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#a7dcfe');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 
                                $sheet->cells("G".$row.":I".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#fadfb0');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 
                                $sheet->cells("J".$row.":L".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#a7dcfe');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 
                                $sheet->cells("M".$row.":O".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#fadfb0');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 

                                $sheet->cells("P".$row.":R".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#a7dcfe');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 
                                $sheet->cells("S".$row.":U".$row, function ($cells){
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#fadfb0');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 

                                 $sheet->row($row,$value);
                           
                            }else{
                               

                                 $sheet->cells("C".$row, function ($cells){
                                        $cells->setValue("TOTAL");
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#f8fa67');
                                        $cells->setBorder('thin','thin','thin','thin');
                                }); 

                                 $sumColumn = "D";
                                 for ($i=0; $i < 18; $i++) { 
                                    $row2 = $row-1;

                                    $sheet->cells($sumColumn.$row, function ($cells) use ($sumColumn,$row1,$row2){
                                        $cells->setValue("=SUM(".$sumColumn.$row1.":".$sumColumn.$row2.")");
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBackground('#f8fa67');
                                        $cells->setBorder('thin','thin','thin','thin');
                                    });
                                    $sumColumn++;
                                 }



                            }


                            $row++;
                    }

                //TOTALS 
                    $sheet->cells("W8:W9", function ($cells){
                                    $cells->setAlignment('right');
                                    $cells->setFontWeight('bold');
                                }); 
                    $sheet->cells("X8:X9", function ($cells){
                        $cells->setAlignment('left');
                        $cells->setFontWeight('bold');
                    }); 
                      $sheet->cell("W8", function($cells){
                        $cells->setValue("TOTAL MALE:");
                    });
                    $sheet->cell("W9", function($cells){
                        $cells->setValue("TOTAL FEMALE:");
                    });
                    $sheet->cell("X8", function($cells) use ($male) {
                        $cells->setValue($male);
                    });
                    $sheet->cell("X9", function($cells) use ($female) {
                        $cells->setValue($female);
                    });
                      


                   

                      $headarr = array("Seed Varieties", "<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60","<29","30-59",">=60");
                      $col = "W";
                      for ($i=0; $i <= 12 ; $i++) { 
                        $sheet->cell($col."12", function($cells) use ($headarr,$i){
                        $cells->setValue($headarr[$i]);
                        if($i==0){
                            $cells->setBackground('#62c95d');
                            $cells->setBorder('thin','thin','thin','thin');
                        }
                        });
                        $col++; 
                      }


                    //DATA NATIONAL BODY
                      $row = 13;
                     
                      $variety = "";
                      foreach ($totals_arr as $key => $value) {
                        $sheet->cell("W".$row, function($cells) use ($key){
                        $cells->setValue($key);
                        $cells->setBackground('#62c95d');
                        $cells->setBorder('thin','thin','thin','thin');
                        });

                        $col = "X";
                            foreach ($value as $key => $info) {
                                foreach ($info as $key => $data) {
                                    $sheet->cell($col.$row, function($cells) use ($data){
                                    $cells->setValue($data);
                                    $cells->setBorder('thin','thin','thin','thin');
                                    });   
                                    $col++;
                                }    
                            }
                        $row++;
                      }
                     
                      $sheet->cell("W".$row, function($cells){
                        $cells->setValue("TOTAL");
                        $cells->setBackground('#f8fa67');
                        $cells->setBorder('thin','thin','thin','thin');
                        });

                      $col = "X";
                      for ($i=1; $i <= 18 ; $i++) { 
                        $sheet->cell($col.$row, function($cells) use ($row,$col){
                            $lrow= $row-1;
                        $cells->setValue("=SUM(".$col."13:".$col.$lrow.")");
                        $cells->setBackground('#f8fa67');
                        $cells->setBorder('thin','thin','thin','thin');
                        });
                        $col++; 
                      }

                       $row--;
                       //DATA NATIONAL HEADER
                     $sheet->mergeCells("X11:Z11");
                     $sheet->cells("X11:Z".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("X11", function($cells){
                        $cells->setValue("AGE GROUP (M)");
                    });
                     $sheet->mergeCells("AA11:AC11");
                     $sheet->cells("AA11:AC".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AA11", function($cells){
                        $cells->setValue("AGE GROUP (F)");
                    });
                     $sheet->mergeCells("AD11:AF11");
                     $sheet->cells("AD11:AF".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AD11", function($cells){
                        $cells->setValue("AREA (M)");
                    });
                     $sheet->mergeCells("AG11:AI11");
                     $sheet->cells("AG11:AI".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                      $sheet->cell("AG11", function($cells){
                        $cells->setValue("AREA (F)");
                    });




                      $sheet->mergeCells("AJ11:AL11");
                     $sheet->cells("AJ11:AL".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AJ11", function($cells){
                        $cells->setValue("TOTAL BAGS (M)");
                    });
                     $sheet->mergeCells("AM11:AO11");
                     $sheet->cells("AM11:AO".$row, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                      $sheet->cell("AM11", function($cells){
                        $cells->setValue("TOTAL BAGS (F)");
                    });



                      $row += 6;
                      $srow = $row;
                      $startheader = $srow - 1;
                      //TOTALS
                       $col = "W";
                      for ($i=0; $i <= 6 ; $i++) { 
                        $sheet->cell($col.$startheader, function($cells) use ($headarr,$i){
                        $cells->setValue($headarr[$i]);
                        if($i==0){
                            $cells->setBackground('#62c95d');
                            $cells->setBorder('thin','thin','thin','thin');
                        }
                        });
                        $col++; 
                      }

                      
                    $groupheader = $startheader - 1;

                    $sheet->mergeCells("X".$groupheader.":Z".$groupheader);
                     $sheet->cells("X".$groupheader.":Z".$startheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("X".$groupheader, function($cells){
                        $cells->setValue("AGE GROUP (M)");
                    });
                     $sheet->mergeCells("AA".$groupheader.":AC".$groupheader);
                     $sheet->cells("AA".$groupheader.":AC".$startheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AA".$groupheader, function($cells){
                        $cells->setValue("AGE GROUP (F)");
                    });

                    $groupheader--;
                    $sheet->mergeCells("X".$groupheader.":AC".$groupheader);
                     $sheet->cells("X".$groupheader.":AC".$groupheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#ddcedf');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("X".$groupheader, function($cells){
                        $cells->setValue("EQUIVALENT PERCENTAGE (%) PER VARIETY & AGE GROUP");
                    });



                      
                      $variety = "";
                      foreach ($totals_arr as $key => $value) {
                        $sheet->cell("W".$row, function($cells) use ($key){
                        $cells->setValue($key);
                        $cells->setBackground('#62c95d');
                        $cells->setBorder('thin','thin','thin','thin');
                        });

                        $col = "X";
                            foreach ($value as $key => $info) {
                                foreach ($info as $key2 => $data) {
                                    if($key=="age_male" || $key=="age_female"){
                                        $sheet->cell($col.$row, function($cells) use ($data,$male,$female,$key){
                                            if($key=="age_male"){
                                                if($male>0){
                                                    $ave = $data/$male*100;
                                                }else{
                                                    $ave =0;
                                                }
                                                $color = "#a7dcfe";
                                            }elseif($key=="age_female"){
                                               if($female>0){
                                                 $ave = $data/$female*100;
                                                 }else{
                                                    $ave =0;
                                                 }
                                                $color = "#fadfb0";
                                            }
                                            $cells->setValue(round($ave, 2));
                                            $cells->setBackground($color);
                                            $cells->setBorder('thin','thin','thin','thin');
                                        });   
                                        $col++;   
                                    }    
                                }    
                            }
                        $row++;
                      }

                      $sheet->cell("W".$row, function($cells){
                        $cells->setValue("TOTAL");
                        $cells->setBackground('#f8fa67');
                        $cells->setBorder('thin','thin','thin','thin');
                        });

                      $col = "X";
                      for ($i=1; $i <= 6 ; $i++) { 
                        $sheet->cell($col.$row, function($cells) use ($row,$col,$srow){
                            $lrow= $row-1;
                        $cells->setValue("=SUM(".$col.$srow.":".$col.$lrow.")");
                        $cells->setBackground('#f8fa67');
                        $cells->setBorder('thin','thin','thin','thin');
                        });
                        $col++; 
                      }


                      //MALE
                    $groupheaderM = $groupheader;
       // dd($maleGroup2);
                    $g1 = "19 - 29 y/o - ".number_format($maleGroup1);
                    $g2 = "30 - 59 y/o - ".number_format($maleGroup2);
                    $g3 = "60 - above - ".number_format($maleGroup3);

                    //dd($groupheader);
                     $sheet->mergeCells("AF".$groupheader.":AH".$groupheader);
                    $sheet->cell("AF".$groupheader, function($cells)  use ($g1){
                        $cells->setValue($g1);
                    });

                    $groupheader++;
                     $sheet->mergeCells("AF".$groupheader.":AH".$groupheader);
                    $sheet->cell("AF".$groupheader, function($cells)  use ($g2){
                        $cells->setValue($g2);
                    });
                    $groupheader++;
                     $sheet->mergeCells("AF".$groupheader.":AH".$groupheader);
                    $sheet->cell("AF".$groupheader, function($cells) use ($g3){
                        $cells->setValue($g3);
                    });

                    

                    $sheet->mergeCells("AE".$groupheaderM.":AE".$groupheader);
                    $sheet->cells("AE".$groupheaderM.":AF".$groupheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#a7dcfe');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AE".$groupheaderM, function($cells){
                        $cells->setValue("MALE");
                         $cells->setValignment("center");
                    });

                    $groupheader++;


                    //FEMALE
                    $groupheaderM = $groupheader;
    
                    $g1 = "19 - 29 y/o - ".number_format($femaleGroup1);
                    $g2 = "30 - 59 y/o - ".number_format($femaleGroup2);
                    $g3 = "60 - above - ".number_format($femaleGroup3);

                    $sheet->mergeCells("AF".$groupheader.":AH".$groupheader);
                    $sheet->cell("AF".$groupheader, function($cells)  use ($g1){
                        $cells->setValue($g1);

                    });

                    $groupheader++;
                     $sheet->mergeCells("AF".$groupheader.":AH".$groupheader);
                    $sheet->cell("AF".$groupheader, function($cells) use ($g2){
                        $cells->setValue($g2);
                    });
                    $groupheader++;
                     $sheet->mergeCells("AF".$groupheader.":AH".$groupheader);
                    $sheet->cell("AF".$groupheader, function($cells)  use ($g3){
                        $cells->setValue($g3);
                    });


                    $sheet->mergeCells("AE".$groupheaderM.":AE".$groupheader);
                    $sheet->cells("AE".$groupheaderM.":AF".$groupheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AE".$groupheaderM, function($cells){
                        $cells->setValue("FEMALE");
                        $cells->setValignment("center");
                    });
                    


                    $groupheader += 2;


                    $sheet->cells("AF".$groupheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                   // $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AF".$groupheader, function($cells){
                        $cells->setValue("19-29");
                        $cells->setValignment("center");
                    });
                    
                    $sheet->cells("AG".$groupheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AG".$groupheader, function($cells){
                        $cells->setValue("30-59");
                        $cells->setValignment("center");
                    });

                    $sheet->cells("AH".$groupheader, function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AH".$groupheader, function($cells){
                        $cells->setValue("60-above");
                        $cells->setValignment("center");
                    });

                    $groupheader++;


                    $maleGroup1 = number_format($maleGroup1);
                    $maleGroup2 = number_format($maleGroup2);
                    $maleGroup3 = number_format($maleGroup3);
                    
                    $femaleGroup1 = number_format($femaleGroup1);
                    $femaleGroup2 = number_format($femaleGroup2);
                    $femaleGroup3 = number_format($femaleGroup3);


                    $sheet->cells("AE".$groupheader, function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AE".$groupheader, function($cells){
                        $cells->setValue("MALE");
                    });

                    $sheet->cells("AF".$groupheader, function ($cells){
                                   // $cells->setAlignment('center');
                                    //$cells->setFontWeight('bold');
                                   // $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AF".$groupheader, function($cells) use ($maleGroup1){
                        $cells->setValue($maleGroup1);
                    });

                    $sheet->cells("AG".$groupheader, function ($cells){
                                    //$cells->setAlignment('center');
                                    //$cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AG".$groupheader, function($cells) use ($maleGroup2){
                        $cells->setValue($maleGroup2);
                    });

                    $sheet->cells("AH".$groupheader, function ($cells){
                                    //$cells->setAlignment('center');
                                    //$cells->setFontWeight('bold');
                                   // $cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AH".$groupheader, function($cells) use ($maleGroup3){
                        $cells->setValue($maleGroup3);
                    });

                    $groupheader++;

                    $sheet->cells("AE".$groupheader, function ($cells){
                                    $cells->setAlignment('left');
                                    $cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AE".$groupheader, function($cells){
                        $cells->setValue("FEMALE");
                    });

                    $sheet->cells("AF".$groupheader, function ($cells){
                                    //$cells->setAlignment('center');
                                    //$cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AF".$groupheader, function($cells) use ($femaleGroup1){
                        $cells->setValue($femaleGroup1);
                    });


                     $sheet->cells("AG".$groupheader, function ($cells){
                                    //$cells->setAlignment('center');
                                    //$cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AG".$groupheader, function($cells) use ($femaleGroup2){
                        $cells->setValue($femaleGroup2);
                    });


                    $sheet->cells("AH".$groupheader, function ($cells){
                                    //$cells->setAlignment('center');
                                    //$cells->setFontWeight('bold');
                                    //$cells->setBackground('#fadfb0');
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                    $sheet->cell("AH".$groupheader, function($cells) use ($femaleGroup3){
                        $cells->setValue($femaleGroup3);
                    });


                    $sheet->setWidth(array(
                    'A'     => 25,
                    'B'     => 25,
                    'C'     => 15.57,
                    'D'     => 10,
                    'E'     => 10,
                    'G'     => 10,
                    'H'     => 10,
                    'I'     => 10,
                    'J'     => 10,
                    'K'     => 10,
                    'L'     => 10,
                    'M'     => 10,
                    'N'     => 10,
                    'O'     => 10,
                    'P'     => 10,
                    'Q'     => 10,
                    'R'     => 10,
                    'S'     => 10,
                    'T'     => 10,
                    'U'     => 10,


                    'W'     => 15,
                    'X'     => 10,
                    'AA'     => 10,
                ));


                });
            });


        if($process_type == 0){
             $excel_file->store("xlsx", $path);
             return "DONE";
        }else{
            $excel_file->download("xlsx");
        }
       
    }















}

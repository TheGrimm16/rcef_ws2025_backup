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

use App\SeedCooperatives;
use App\SeedGrowers;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Config;
use DB;
use Excel;

use Session;
use Auth;

class ReportController extends Controller
{

    public function download_commitment_delivery_of_coop(){

            $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_target_datasets")
                ->select("region", DB::raw("SUM(totalBagCount) as target"))
                ->groupBy("region")
                ->get();

            $excel_array = array();
            $coop_id = array();
                foreach ($regions as $key => $value) {
                    $region = $value->region;
                    $target = $value->target;
        
                $deliveries = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                    ->where("tbl_delivery.isBuffer", 0)
                    ->where("tbl_delivery.is_cancelled", 0)
                    ->where("tbl_delivery.region", $region)
                      ->groupBy("tbl_delivery.coopAccreditation")
                    ->groupBy("tbl_delivery.region")
                  
                    ->get();


                 foreach ($deliveries as $key2 => $del) {

                        $confirm = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                            ->select("batchTicketNumber")
                            ->where("coopAccreditation", $del->coopAccreditation)
                            ->where("region", $del->region)
                             ->where("tbl_delivery.isBuffer", 0)
                            ->where("tbl_delivery.is_cancelled", 0)
                            ->groupBy("batchTicketNumber")
                            ->get();
                         $confirm = json_decode(json_encode($confirm), true);

                        $actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                            ->whereIn("batchTicketNumber", $confirm)
                            ->sum("totalBagCount");

                      
                                        $coop_list = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                                            ->where("accreditation_no", $del->coopAccreditation)
                                            ->first();
                                       if(count($coop_list)>0){
                                             if(isset($excel_array[$coop_list->coopId])){
                                                 $excel_array[$coop_list->coopId][$region] = $actual;
                                            }else{

                                                $new_seeds_batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                                                ->select("batchTicketNumber")
                                                ->where("coopAccreditation", $coop_list->accreditation_no)
                                                 ->where("tbl_delivery.isBuffer", 0)
                                                ->where("tbl_delivery.is_cancelled", 0)
                                                ->groupBy("batchTicketNumber")
                                                ->get();
                                                $new_seeds_batches = json_decode(json_encode($new_seeds_batches), true);
                                                $new_seeds_bags = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                                                    ->whereIn("batchTicketNumber", $new_seeds_batches)
                                                    ->sum("totalBagCount");

                                                //BUFFER
                                                $buffer_seedTags = DB::connection("ls_inspection_db")->table("tbl_delivery")
                                                    ->select("seedTag")
                                                     ->where("coopAccreditation", $coop_list->accreditation_no)
                                                     ->where("isBuffer", 1)
                                                    ->where("is_cancelled", 0)
                                                    ->groupBy("seedTag")
                                                    ->get();

                                             
                                                $buffer_seedTags = json_decode(json_encode($buffer_seedTags), true);
                                                $buffer_count = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                                                    ->whereIn("seedTag",$buffer_seedTags)
                                                    ->sum("totalBagCount");



                                                    /*
                                                //INVENTORY
                                                $inv_seedTags = DB::connection("ls_inspection_db")->table("tbl_delivery")
                                                    ->select("seedTag")
                                                     ->where("coopAccreditation", $coop_list->accreditation_no)
                                                     ->where("isBuffer", 0)
                                                    ->where("is_cancelled", 0)
                                                    ->groupBy("seedTag")
                                                    ->get();
                                  

                                                $inv_seedTags = json_decode(json_encode($inv_seedTags), true);
                                                $inv_count = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                                                    ->whereIn("seedTag",$inv_seedTags)
                                                    ->sum("totalBagCount");
                                                $for_distribution = $inv_count + $buffer_count; */

                                                $buffer_batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                                                    ->select("batchTicketNumber")
                                                    ->where("coopAccreditation", $coop_list->accreditation_no)
                                                    ->where("isBuffer", 1) 
                                                    ->where("is_cancelled", 0)
                                                    ->groupBy("batchTicketNumber")
                                                    ->get();
                                                $buffer_batches = json_decode(json_encode($buffer_batches), true);
                                                $current_buffer_count = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                                                    ->whereIn("batchTicketNumber",$buffer_batches)
                                                    ->sum("totalBagCount");



                                                $coop_region = DB::table("lib_regions")
                                                ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.regCode", "=", "lib_regions.regCode")
                                                ->where("lib_regions.id", $coop_list->regionId)->value("regionName");

                                                $coop_province = DB::table("lib_provinces")
                                                ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.prv_code", "=", "lib_provinces.provCode")
                                                ->where("lib_provinces.id", $coop_list->provinceId)->value("province");

                                                $commitments = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_regional")
                                                    ->where("coop_Id",$coop_list->coopId)
                                                    ->sum("volume");

                                                $excel_array[$coop_list->coopId]["coopName"] = strtoupper($coop_list->coopName);
                                                $excel_array[$coop_list->coopId]["acronym"] = $coop_list->acronym;
                                                $excel_array[$coop_list->coopId]["accreditation_no"] = $coop_list->accreditation_no;
                                                $coop_id[$coop_list->accreditation_no]  = $coop_list->coopId;

                                                $excel_array[$coop_list->coopId]["coop_region"] = $coop_region;
                                                $excel_array[$coop_list->coopId]["coop_province"] = $coop_province;
                                                $excel_array[$coop_list->coopId]["commitments"] = $commitments;
                                                $excel_array[$coop_list->coopId]["new_seeds_bags"] = $new_seeds_bags;

                                               // $excel_array[$coop_list->coopId]["inv_count"] = $inv_count;
                                           //     $excel_array[$coop_list->coopId]["buffer_count"] = $buffer_count;
                                                //$excel_array[$coop_list->coopId]["for_distribution"] = $for_distribution;
                                                $excel_array[$coop_list->coopId]["total_for_distri"] = 0;
                                                $excel_array[$coop_list->coopId]["current_buffer_count"] = $current_buffer_count;

            


                                               
                                                 
                                               
                                              
                                           
                                                $excel_array[$coop_list->coopId][$region] = $actual;
                                            }
                                       }else{
                                        dd($del->coopAccreditation);
                                       } 
                                    }                  
                                


                }

                  
                    $seedTags_trans = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                        ->select("seedTag")
                        ->where("transferCategory", "P")
                        ->groupBy("seedTag")
                        ->get();
                    $total = 0;
                    foreach ($seedTags_trans as $index => $tag) {
                                $ls_tbl_delivery = DB::connection("ls_inspection_db")->table("tbl_delivery")
                                ->where("seedTag", $tag->seedTag)
                                ->first();

                            if(count($ls_tbl_delivery)>0){
                                 
                                  if(isset($coop_id[$ls_tbl_delivery->coopAccreditation])){
                                     $cID =  $coop_id[$ls_tbl_delivery->coopAccreditation];
                                 }else{
                                     $get_coopId = explode("-", $ls_tbl_delivery->coopAccreditation);  
                                    $cID = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                                        ->where("accreditation_no", "LIKE", "%".$get_coopId[4])
                                        ->value("coopId");
                                 }

                                 $total_bags_tag = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                                    ->where("seedTag", $tag->seedTag)
                                    ->sum("totalBagCount");

                                if($ls_tbl_delivery->isBuffer == "1"){
                                    if(isset($excel_array[$cID]["buffer_count"])){
                                        $excel_array[$cID]["buffer_count"] += $total_bags_tag;       
                                    }else{
                                         $excel_array[$cID]["buffer_count"] = $total_bags_tag;  

                                    } 
                                }else{
                                    if(isset($excel_array[$cID]["inv_count"])){
                                        $excel_array[$cID]["inv_count"] += $total_bags_tag;       
                                    }else{
                                         $excel_array[$cID]["inv_count"] = $total_bags_tag;  
                                    } 
                                }

                                if(isset($excel_array[$cID]["for_distribution"])){
                                     $excel_array[$cID]["for_distribution"] += $total_bags_tag;
                                }else{
                                     $excel_array[$cID]["for_distribution"] = $total_bags_tag;
                                }
                               

                            }else{

                            }
                    }




                     $excel_data = json_decode(json_encode($excel_array), true); //convert collection to associative array to be converted to excel
              $regions = json_decode(json_encode($regions), true); //convert collection to associative array to be converted to excel
              
            return Excel::create("Local Seed Supply Analysis".date("Y-m-d g:i A"), function($excel) use ($excel_data, $regions) {
                $excel->sheet("DELIVERY LIST", function($sheet) use ($excel_data, $regions) {


                //HEADER
   
                    $sheet->cells("A1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Cooperative");
                                      $cells->setBackground('#E2EFDA');
                                }); 

                    $sheet->mergeCells("B1:C1");
                    $sheet->cells("B1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Head Quarter");
                                }); 

                    $sheet->cells("B2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Region");


                                }); 
                    $sheet->cells("C2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Province");

                                }); 



                    $sheet->mergeCells("D1:E1");
                    $sheet->cells("D1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("New Seeds");
                                }); 

                     $sheet->cells("D2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Target \n (Commitment)");
                                }); 
                     $sheet->cells("E2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Actual \n (Inspected & Accepted)");
                                }); 

                    $sheet->mergeCells("F1:H1");
                    $sheet->cells("F1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Supply from Previous Season");
                                }); 
                    $sheet->cells("F2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Inventory");
                                }); 

                    $sheet->cells("G2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Buffer");
                                }); 

                    $sheet->cells("H2", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("For Distribution");
                                });    



                     $sheet->mergeCells("I1:I2");
                    $sheet->cells("I1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("2022 DS Total \n (E+H)");
                                }); 



                $column = "J";
                     foreach ($regions as $key => $value) {
                            $row=1;
                            $column_lib[$value['region']] = $column;

                         $sheet->cells($column.$row, function ($cells) use ($value){
                                        $cells->setValue($value['region']);
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBorder('thin','thin','thin','thin');
                                        }); 
                         
                         $row++;
                           $sheet->cells($column.$row, function ($cells) use ($value){
                                        $cells->setValue("Target: ". number_format($value['target'],2));
                                        $cells->setAlignment('center');
                                        $cells->setFontWeight('bold');
                                        $cells->setBorder('thin','thin','thin','thin');
                                        }); 
                        

                        $sheet->setWidth(array($column => 25 ));         
                         $column++;



                     } 
                    $sheet->setFreeze('A3');

                  $sheet->mergeCells($column."1:".$column."2");
                    $sheet->cells($column."1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                      $cells->setValue("Forwarding Balance to \n next season");
                                }); 

                     $column_lib["current_buffer_count"] = $column;
                       $sheet->setWidth(array($column => 30 ));         
                  //END HEADER


                    //DATA INPUT
                    $row=3;
                    foreach ($excel_data as $index => $excel_info) {

                        foreach ($excel_info as $i => $details) {
                            
                            if($i == "coopName"){
                                 $sheet->cells("A".$row, function ($cells) use ($details){
                                        $cells->setValue($details);
                                        }); 
                            }elseif($i == "coop_region"){
                                 $sheet->cells("B".$row, function ($cells) use ($details){
                                        $cells->setValue($details);
                                        }); 
                            }elseif($i == "coop_province"){
                                 $sheet->cells("C".$row, function ($cells) use ($details){
                                        $cells->setValue($details);
                                        }); 
                            }
                            elseif($i == "commitments"){
                                 $sheet->cells("D".$row, function ($cells) use ($details){
                                        $cells->setValue(number_format($details));
                                        }); 
                            }
                            elseif($i == "new_seeds_bags"){
                                 $sheet->cells("E".$row, function ($cells) use ($details){
                                        $cells->setValue(number_format($details));
                                        }); 
                            }
                            elseif($i == "inv_count"){
                                 $sheet->cells("F".$row, function ($cells) use ($details){
                                        $cells->setValue($details);
                                        }); 
                            }
                            elseif($i == "buffer_count"){
                                 $sheet->cells("G".$row, function ($cells) use ($details){
                                        $cells->setValue($details);
                                        }); 
                            }
                            elseif($i == "for_distribution"){
                                 $sheet->cells("H".$row, function ($cells) use ($details){
                                        $cells->setValue($details);
                                        }); 
                            }
                            elseif($i == "total_for_distri"){
                                 $sheet->cells("I".$row, function ($cells) use ($details,$row){
                                        $cells->setValue("=E".$row."+"."H".$row);
                                        }); 
                            }



                            else{

                                if(isset($column_lib[$i])){
                                  $region_col =  $column_lib[$i];
                                $sheet->cells($region_col.$row, function ($cells) use ($details){
                                        $cells->setValue(number_format($details));
                                        });   
                            }else{

                            }


                                

                            }
                        }

                        $row++;

                    }

                    //FORMATING EXCEL
                    $row--;
                    $sheet->cells("A1:A".$row, function ($cells){
                                    $cells->setBackground('#E2EFDA');
                                }); 
                    $sheet->cells("B1:C".$row, function ($cells){
                                    $cells->setBackground('#FFF2CC');

                                }); 
                    $sheet->cells("D1:E".$row, function ($cells){
                                    $cells->setBackground('#DDEBF7');
                                }); 
                    $sheet->cells("F1:H".$row, function ($cells){
                                    $cells->setBackground('#FCE4D6');
                                }); 
                    $sheet->cells("I1:I".$row, function ($cells){
                                    $cells->setBackground('#B4C6E7');
                                }); 
                    $sheet->cells($column."1:".$column.$row, function ($cells){
                                    $cells->setBackground('#F4B084');
                                }); 
                    
                     $sheet->setWidth(array(
                    'A' => 75,
                    'B' => 20,
                    'C' => 20,
                    'D' => 20,
                    'E' => 25,
                    'F' => 12,
                    'G' => 12,
                    'H' => 18,
                    'I' => 22
                ));

                     //DECLARATION
                  
                     $first_row = 1;
                     $ls_col = $column;
                     $ls_row = $row;

                     for($first_row; $first_row <= $ls_row; $first_row++){
                               $first_col = "A";
                            for($first_col; $first_col <= $ls_col; $first_col++){
                            $sheet->cells($first_col.$first_row, function ($cells){
                                    $cells->setBorder('thin','thin','thin','thin');
                                }); 
                            }
                     }


                      

                     $sheet->getRowDimension(2)->setRowHeight(35);
    
                    
                });
            })->download('xlsx');

              
       
    }




    public function generateMinMaxYield_mi(){

          //get all municipality

        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_yield_min_max')->truncate();

        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get();

            $area_arr = array();
            $product_arr = array();
            $allocated = "";
                foreach($municipalities as $municipality_row){
                    $municipal_farmers = 0;
                    $municipal_bags = 0;
                    $municipal_dis_area = 0;
                    $municipal_actual_area = 0;
                    $municipal_male = 0;
                    $municipal_female = 0;
                    $municipal_yield = 0;
                    $municipal_area_claimed = 0;
                    $municipal_product = 0;
                    $municipal_area = 0;
                    $yield = 0;

                    $minimum_yield = 0;
                    $max_yield = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);
                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();
                if(count($dbCheck)<=0){
                    continue;
                }
            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
          //->groupBy('released.rsbsa_control_no')
                    ->get();

                $yield_check = 0;
                $computed_yield = 0;
                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('farmerID', $municipal_row->farmer_id)
                        ->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID', 'DESC')
                        ->first();
            
                        
                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;
            $weight = $farmer_profile->weight_per_bag;
            $no_bags = $farmer_profile->yield;
            $area = $farmer_profile->area_harvested;

                        if($farmer_profile->sex == 'Male'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        
            if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
              $yield_check = 1;
              
              $computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
              if($farmer_profile->weight_per_bag == 20){
                $yield_check = 0;
              }else{
                if($computed_yield <= 1){
                  $yield_check = 0;
                }else if($computed_yield > 13){
                  $yield_check = 0;
                }else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
                  $yield_check = 0;
                }else{
                  $yield_check = 1;
                }
              }
              
              
            }else{
              $yield_check = 0;
            }

            if($yield_check > 0){
              $municipal_product += (floatval($no_bags) * floatval($weight));
              $municipal_area += floatval($area);
              $farmer_dividend += 1;

              $temp_yield = floatval($no_bags) * floatval($weight);
              $temp_yield = $temp_yield / floatval($area);
              $temp_yield = $temp_yield / 1000;

                if($temp_yield > 0){
                    if($minimum_yield > 0){
                        if($minimum_yield > $temp_yield){
                            $minimum_yield = $temp_yield;
                        }
                    }else{
                        $minimum_yield = $temp_yield;
                    }

                    if($max_yield>0){
                        if($max_yield < $temp_yield){
                            $max_yield = $temp_yield;
                        }
                    }else{
                        $max_yield = $temp_yield;
                    }
                }

               

               
            }
  

                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        
                    }
                }
            }
            
      if($farmer_dividend<=0){
        $municipal_yield = 0;
      }else{
         // $municipal_yield = $municipal_yield / $farmer_dividend;
        $municipal_yield = ($municipal_product / $municipal_area) / 1000;




      }   
      
      //check if the province is allocated this ws2021
      $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $municipality_row->province)
                ->first();
      if(count($allowedLoc)>0){
        $allocated = "Y";
      }else{
        $allocated = "N";
      }


      
   


            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_yield_min_max')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'ws2021_allocated'     => $allocated,
                'mean_yield'        => $municipal_yield,
                'min_yield'   => $minimum_yield,
                'max_yield' => $max_yield,
            ]);
        
          

        }
          return json_encode("success");
    }

 

   



    public function genTableModal(Request $request){
        if($request->region=='0'){$region = '%'; dd($request->region);}else{$region=$request->region;}
        if($request->province=='0'){$province = '%';}else{$province=$request->province;}
        if($request->municipality=='0'){$municipality = '%';}else{$municipality=$request->municipality;}


        $codeDesc = array(
            "FD" => 'For Donation',
            "TD" => 'For Techno Demo',
            "DS" => 'For Damaged Seeds',
            "FR" => 'For Replacement',
            "LP" => 'For LGU/Philrice'
        );

            $breakdown = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_breakdown')
                ->where('region','like', '%'.$region)
                ->where('province','like', '%'.$province)
                ->where('municipality', 'like', '%'.$municipality)
                ->orderBy('seedAllocation', 'ASC')
                ->groupBy('seedAllocation')
                ->get();
        $return_arr = array();
          
               $totalTransfer = 0;
                foreach ($breakdown as $breakdownData) {
                    $volume = 0;
                    $volume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_breakdown')
                            ->where('region','like', '%'.$region)
                            ->where('province','like', '%'.$province)
                            ->where('municipality', 'like', '%'.$municipality)
                            ->where('seedAllocation', $breakdownData->seedAllocation)
                            ->sum('volume');
                    $totalTransfer +=$volume; 

                        $batch_array = array(
                                'code' => $breakdownData->seedAllocation,
                                'description' => $codeDesc[$breakdownData->seedAllocation],
                                'volume' => number_format($volume).' bag(s)'
                                );
                        array_push($return_arr, $batch_array);
                }


               $transferCount = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                            ->where('region','like', '%'.$region)
                            ->where('province','like', '%'.$province)
                            ->where('municipality', 'like', '%'.$municipality)
                            ->where('is_transferred', 1)
                            ->sum('totalBagCount'); 

                if($totalTransfer < $transferCount){
                         $batch_array = array(
                                'code' => '-',
                                'description' => 'Unallocated',
                                'volume' => number_format($transferCount - $totalTransfer).' bag(s)'
                                );
                        array_push($return_arr, $batch_array);
                }

            
           // dd($return_arr);
        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
        ->make(true);
    }





    public function Home(){
        $d_provinces = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        return view('reports.home')->with('d_provinces', $d_provinces);
    }

    public function Home_scheduled(){
        $d_provinces = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        return view('reports.schedule.report')->with('d_provinces', $d_provinces);
    }

    public function set_database($database_name){
        try {
            \Config::set('database.connections.reports_db.database', $database_name);
            DB::purge('reports_db');

            DB::connection('reports_db')->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }
    
    /**
     * MIRROR DATABASE AUTO-UPDATE
     */
    function lib_dropoff_point_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->get(); 
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_dropoff_point')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;

        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `LIB_DROPOFF_POINT` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "prv_dropoff_id" => $row->prv_dropoff_id,
                        "coop_accreditation" => $row->coop_accreditation,
                        "region" => $row->region,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "dropOffPoint" => $row->dropOffPoint,
                        "prv" => $row->prv,
                        "is_active" => $row->is_active,
                        "date_created" => $row->date_created,
                        "dateUpdated" => $row->dateUpdated,
                        "created_by" => $row->created_by
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_dropoff_point')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: prv_dropoff_id = $row->prv_dropoff_id, province: $row->province, municipality: $row->municipality, dropoffPoint: $row->dropOffPoint<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->prv_dropoff_id)<br>";
                }
            }

            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `LIB_DROPOFF_POINT` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";

        }else{
            echo "<strong>NO DATA IN DATABASE: `LIB_DROPOFF_POINT`</strong><br><br>";
        }
    }

    function lib_prv_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_prv')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `LIB_PRV` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "region" => $row->region,
                        "regCode" => $row->regCode,
                        "provCode" => $row->provCode,
                        "munCode" => $row->munCode,
                        "regionName" => $row->regionName,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "prv" => $row->prv,
                        "dateCreated" => $row->dateCreated,
                        "region_sort" => $row->region_sort,
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_prv')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: prv = $row->prv, region = $row->regionName, province: $row->province, municipality: $row->municipality<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->prv)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `LIB_PRV` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `LIB_PRV`</strong><br><br>";
        }
    }

    function tbl_actual_delivery_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `TBL_ACTUAL_DELIVERY` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "batchTicketNumber" => $row->batchTicketNumber,
                        "region" => $row->region,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "dropOffPoint" => $row->dropOffPoint,
                        "seedVariety" => $row->seedVariety,
                        "totalBagCount" => $row->totalBagCount,
                        "send" => $row->send,
                        "seedTag" => $row->seedTag,
                        "prv_dropoff_id" => $row->prv_dropoff_id,
                        "prv" => $row->prv,
                        "moa_number" => $row->moa_number,
                        "app_version" => $row->app_version,
                        "batchSeries" => $row->batchSeries,
                        "date_modified" => $row->date_modified,
                        "remarks" => $row->remarks,
                        "isRejected" => $row->isRejected,
                        "is_transferred" => $row->is_transferred
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_actual_delivery')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: batchTicketNumber = $row->batchTicketNumber, province = $row->province, municipality: $row->municipality, totalBagCount: $row->totalBagCount<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->batchTicketNumber)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `TBL_ACTUAL_DELIVERY` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `TBL_ACTUAL_DELIVERY`</strong><br><br>";
        }
    }

    function tbl_delivery_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `TBL_DELIVERY` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "ticketNumber" => $row->ticketNumber,
                        "batchTicketNumber" => $row->batchTicketNumber,
                        "coopAccreditation" => $row->coopAccreditation,
                        "sgAccreditation" => $row->sgAccreditation,
                        "seedTag" => $row->seedTag,
                        "seedVariety" => $row->seedVariety,
                        "seedClass" => $row->seedClass,
                        "totalWeight" => $row->totalWeight,
                        "weightPerBag" => $row->weightPerBag,
                        "totalBagCount" => $row->totalBagCount,
                        "deliveryDate" => $row->deliveryDate,
                        "deliverTo" => $row->deliverTo,
                        "coordinates" => $row->coordinates,
                        "status" => $row->status,
                        "inspectorAllocated" => $row->inspectorAllocated,
                        "userId" => $row->userId,
                        "dateCreated" => $row->dateCreated,
                        "oldTicketNumber" => $row->oldTicketNumber,
                        "region" => $row->region,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "dropOffPoint" => $row->dropOffPoint,
                        "prv_dropoff_id" => $row->prv_dropoff_id,
                        "prv" => $row->prv,
                        "moa_number" => $row->moa_number,
                        "app_version" => $row->app_version,
                        "batchSeries" => $row->batchSeries,
                        "is_cancelled" => $row->is_cancelled,
                        "cancelled_by" => $row->cancelled_by,
                        "reason" => $row->reason,
                        "date_updated" => $row->date_updated,
                        "sg_id" => $row->sg_id
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: batchTicketNumber = $row->batchTicketNumber, province = $row->province, municipality: $row->municipality, totalBagCount: $row->totalBagCount<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data ($row->batchTicketNumber)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `TBL_DELIVERY` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `TBL_DELIVERY`</strong><br><br>";
        }
    }

    function tbl_delivery_status_mirror(){
        DB::beginTransaction();
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_status')->get();
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery_status')->truncate();

        $count = number_format(count($data));
        $insert_count = 0;
        
        if(count($data) > 0){
            echo "<strong>STARTING MIRROR EXECUTION OF `TBL_DELIVERY_STATUS` DATABASE... ($count ROWS)</strong><br>";
            foreach($data as $row){
                try {  
                    $row_arr = array(
                        "batchTicketNumber" => $row->batchTicketNumber,
                        "status" => $row->status,
                        "dateCreated" => $row->dateCreated,
                        "send" => $row->send
                    );
        
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.tbl_delivery_status')->insert($row_arr);
                    DB::commit();

                    $insert_count += 1;
                    echo "MYSQL_INSERT SUCCESS: batchTicketNumber = $row->batchTicketNumber, status = $row->status, dateCreated = $row->dateCreated<br>";
                } catch (\Exception $e) {
                    DB::rollback();
                    echo "MYSQL_INSERT ERROR: Failed to insert data (batchTicketNumber = $row->batchTicketNumber, status = $row->status)<br>";
                }
            }
            
            $insert_count = number_format($insert_count);
            echo "<strong>COMPLETED MIRROR EXECUTION OF `TBL_DELIVERY_STATUS` DATABASE ($insert_count ROWS INSERTED)</strong><br><br>";
        
        }else{
            echo "<strong>NO DATA IN DATABASE: `TBL_DELIVERY_STATUS`</strong><br><br>";
        }
    }

    public function execute_mirror_db(){
        DB::connection('mysql')->table('lib_logs')
        ->insert([
            'category' => 'DELIVERY_DATA_MIRROR',
            'description' => 'Succedssful update of mirror database (rcep_delivery_inspection)',
            'author' => 'SYSTEM',
            'ip_address' => 'LOCAL'
        ]);
        
        $this->lib_dropoff_point_mirror();
        $this->lib_prv_mirror();
        $this->tbl_actual_delivery_mirror();
        $this->tbl_delivery_mirror();
        $this->tbl_delivery_status_mirror();
    }
    /**
     * MIRROR DATABASE AUTO-UPDATE
     */
     
     public function generate_all_data_filtered(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')->truncate();

        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports2.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => count($total_provinces),
                'total_municipalities' => count($total_municipalities),
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
        $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;       
        DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_national_reports')
        ->insert([
            'regions'        => count($total_regions),
            'provinces'      => count($national_provinces),
            'municipalities' => count($national_municipalities),
            'total_farmers'     => $national_data_all->total_farmers,
            'total_bags'        => $national_data_all->total_bags,
            'total_dist_area'   => $national_data_all->total_dist_area,
            'total_actual_area' => $national_data_all->total_actual_area,
            'total_male'        => $national_data_all->total_male,
            'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);
     }
     


     function generate_all_test(){

        DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')->truncate();

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->skip(8)
            ->take(20)
            ->get();
     

        $area_arr = array();
        $product_arr = array();
        $allocated = "";
        foreach($municipalities as $municipality_row){
            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;
            $municipal_area_claimed = 0;
            $municipal_product = 0;
            $municipal_area = 0;
            $yield = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    continue;
                }




            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    //->groupBy('released.rsbsa_control_no')
                    ->get();

                $yield_check = 0;
                $computed_yield = 0;
                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('farmerID', $municipal_row->farmer_id)
                        ->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID', 'DESC')
                        ->first();
                        
                    /*if(count($farmer_profile) > 1){
                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                            ->where('farmerID', $municipal_row->farmer_id)
                            ->where('season', 'like', 'DS 2021')
                            ->orderBy('farmerID')
                            ->get(1);
                    }else{
                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                            ->where('farmerID', $municipal_row->farmer_id)
                            ->where('season', 'like', 'DS 2021')
                            ->orderBy('farmerID')
                            ->first();
                    }*/
                        
                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;
                        $weight = $farmer_profile->weight_per_bag;
                        $no_bags = $farmer_profile->yield;
                        $area = $farmer_profile->area_harvested;

                        $gender = strtoupper(substr($farmer_profile->sex, 0, 1));
                        if($gender == 'M'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        
                        if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
                            $yield_check = 1;
                            
                            //check if ave weight is not 20kg
                            /*if($farmer_profile->weight_per_bag == 20){
                                $yield = 0;
                            }else{
                                $yield_check = 1;
                            }*/
                            
                            //check if ave weight is within the provincial parameters
                            /*$aveWeightLibrary = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.lib_yield_inputs")
                                ->where("province", $municipality_row->province)
                                ->where("category", "weight_per_bag")
                                ->first();

                            if(count($aveWeightLibrary)>0){
                                if($farmer_profile->weight_per_bag >= $aveWeightLibrary->from_value && $farmer_profile->weight_per_bag <= $aveWeightLibrary->to_value){   
                                    $yield_check = 1;
                                }else{
                                    $yield_check = 0;
                                }
                            }*/
                            
                            /** CHECK YIELD RANGES **/
                            /*$computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($computed_yield <= 1){
                                $yield_check = 0;
                            }else{
                                $yield_check = 1;
                            } 
                            if ($computed_yield > 13){
                                $yield_check = 0;
                            }else{
                                $yield_check = 1;
                            }*/
                            
                            $computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($farmer_profile->weight_per_bag == 20){
                                $yield_check = 0;
                            }else{
                                if($computed_yield <= 1){
                                    $yield_check = 0;
                                }else if($computed_yield > 13){
                                    $yield_check = 0;
                                }else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
                                    $yield_check = 0;
                                }else{
                                    $yield_check = 1;
                                }
                            }
                            
                            
                        }else{
                            $yield_check = 0;
                        }

                        if($yield_check > 0){
                            $municipal_product += (floatval($no_bags) * floatval($weight));
                            $municipal_area += floatval($area);
                            $farmer_dividend += 1;
                        }
    

                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        
                    }
                }
            }
            
            if($farmer_dividend<=0){
                $municipal_yield = 0;
            }else{
               // $municipal_yield = $municipal_yield / $farmer_dividend;
                $municipal_yield = ($municipal_product / $municipal_area) / 1000;
            }   
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports_test.lib_yield_provinces")
                ->where("province", $municipality_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }

            
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
                'yield_total_kg_production' => $municipal_product,
                'yield_area_harvested' => $municipal_area,
                'yield'             => $municipal_yield,
                'farmers_with_yield'=> $farmer_dividend,
                'allocated_province' =>  $allocated,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $municipal_area_claimed 
            ]);  
        }

        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports_test.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => count($total_provinces),
                'total_municipalities' => count($total_municipalities),
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            //->where('yield', '>', 0)
            ->first();
            
            if($national_data_all->yield_area_harvested == 0) {
                $national_yield = 0;
            }else{
                $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
            }

            if($province_42_data->yield_area_harvested == 0){
                $national_yield_42 = 0;
            }else{
                $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;     
            }               
        DB::table($GLOBALS['season_prefix'].'rcep_reports_test.lib_national_reports')
        ->insert([
            'regions'        => count($total_regions),
            'provinces'      => count($national_provinces),
            'municipalities' => count($national_municipalities),
            'total_farmers'     => $national_data_all->total_farmers,
            'total_bags'        => $national_data_all->total_bags,
            'total_dist_area'   => $national_data_all->total_dist_area,
            'total_actual_area' => $national_data_all->total_actual_area,
            'total_male'        => $national_data_all->total_male,
            'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);

        //after all report data is saved, save data to mirror database
        //$this->mirror_database();
    
     }


     public function generate_statistics(){


        

        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->truncate();

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get(); 

        $area_arr = array();
        $product_arr = array();
        $allocated = "";

        
        foreach($municipalities as $municipality_row){


            $processed_municipality = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_municipal_reports")
            ->where("province", $municipality_row->province)
            ->where("municipality", $municipality_row->municipality)
            ->first();

            if(count($processed_municipality)>0){
                continue;
            }



            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;
            $municipal_area_claimed = 0;
            $municipal_product = 0;
            $municipal_area = 0;
            $yield = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    continue;
                }




            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    //->groupBy('released.rsbsa_control_no')
                    ->get();

                $yield_check = 0;
                $computed_yield = 0;
                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('farmerID', $municipal_row->farmer_id)
                        //->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID', 'DESC')
                        ->first();


                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;
                        $weight = $farmer_profile->weight_per_bag;
                        $no_bags = $farmer_profile->yield;
                        $area = $farmer_profile->area_harvested;

                        $gender = strtoupper(substr($farmer_profile->sex, 0, 1));
                        //dd($gender);

                   


                        if($gender == "M"){
                            $municipal_male += 1;
                           // dd($municipal_male);
                        }else{
                            $municipal_female += 1;
                        }
                        
                        if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
                            $yield_check = 1;
                
                            $computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($farmer_profile->weight_per_bag == 20){
                                $yield_check = 0;
                            }else{
                                if($computed_yield <= 1){
                                    $yield_check = 0;
                                }else if($computed_yield > 13){
                                    $yield_check = 0;
                                }else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
                                    $yield_check = 0;
                                }else{
                                    $yield_check = 1;
                                }
                            }
                            
                            
                        }else{
                            $yield_check = 0;
                        }

                        if($yield_check > 0){
                            $municipal_product += (floatval($no_bags) * floatval($weight));
                            $municipal_area += floatval($area);
                            $farmer_dividend += 1;
                        }
    

                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        
                    }
                }
            }
            
            if($farmer_dividend<=0){
                $municipal_yield = 0;
            }else{
               // $municipal_yield = $municipal_yield / $farmer_dividend;
                $municipal_yield = ($municipal_product / $municipal_area) / 1000;
            }   
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $municipality_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }

            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
                'yield_total_kg_production' => $municipal_product,
                'yield_area_harvested' => $municipal_area,
                'yield'             => $municipal_yield,
                'farmers_with_yield'=> $farmer_dividend,
                'allocated_province' =>  $allocated,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $municipal_area_claimed 
            ]);  



            //if($municipal_male > 0){dd($municipality_row->province." ".$municipality_row->municipality." ".$municipal_male);}


        }


        


        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => count($total_provinces),
                'total_municipalities' => count($total_municipalities),
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            ->first();
            
        $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
        $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;       
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'        => count($total_regions),
            'provinces'      => count($national_provinces),
            'municipalities' => count($national_municipalities),
            'total_farmers'     => $national_data_all->total_farmers,
            'total_bags'        => $national_data_all->total_bags,
            'total_dist_area'   => $national_data_all->total_dist_area,
            'total_actual_area' => $national_data_all->total_actual_area,
            'total_male'        => $national_data_all->total_male,
            'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);

    
     }


     public function process_yield(){

        DB::table($GLOBALS['season_prefix']."rcep_reports.lib_municipal_reports")->truncate();

        DB::table($GLOBALS['season_prefix']."rcep_reports.lib_provincial_reports")->truncate();
        

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get(); 

        $area_arr = array();
        $product_arr = array();
        $allocated = "";

        
        foreach($municipalities as $municipality_row){

            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;
            $municipal_area_claimed = 0;
            $municipal_product = 0;
            $municipal_area = 0;
            $yield = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    continue;
                }




            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    //->groupBy('released.rsbsa_control_no')
                    ->get();

                $yield_check = 0;
                $computed_yield = 0;
                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('farmerID', $municipal_row->farmer_id)
                        //->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID', 'DESC')
                        ->first();
               

                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;
                        $weight = $farmer_profile->weight_per_bag;
                        $no_bags = $farmer_profile->yield;
                        $area = $farmer_profile->area_harvested;

                        $gender = strtoupper(substr($farmer_profile->sex, 0, 1));
                        if($gender == 'M'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        
                        if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
                            $yield_check = 1;
                            
                            $computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($farmer_profile->weight_per_bag == 20){
                                $yield_check = 0;
                            }else{
                                if($computed_yield <= 1){
                                    $yield_check = 0;
                                }else if($computed_yield > 13){
                                    $yield_check = 0;
                                }else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
                                    $yield_check = 0;
                                }else{
                                    $yield_check = 1;
                                }
                            }
                            
                            
                        }else{
                            $yield_check = 0;
                        }

                        if($yield_check > 0){
                            $municipal_product += (floatval($no_bags) * floatval($weight));
                            $municipal_area += floatval($area);
                            $farmer_dividend += 1;
                        }
    

                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        
                    }
                }
            }
            
            if($farmer_dividend<=0){
                $municipal_yield = 0;
            }else{
               // $municipal_yield = $municipal_yield / $farmer_dividend;
                $municipal_yield = ($municipal_product / $municipal_area) / 1000;
            }   
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $municipality_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }

            
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
                'yield_total_kg_production' => $municipal_product,
                'yield_area_harvested' => $municipal_area,
                'yield'             => $municipal_yield,
                'farmers_with_yield'=> $farmer_dividend,
                'allocated_province' =>  $allocated,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $municipal_area_claimed 
            ]);  
        }


        


        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->where("region", $r_row->region)
            ->update([
                //'region'               => $r_row->region,
                //'total_provinces'      => count($total_provinces),
                //'total_municipalities' => count($total_municipalities),
                //'total_farmers'     => $r_row->total_farmers,
                //'total_bags'        => $r_row->total_bags,
                //'total_dist_area'   => $r_row->total_dist_area,
                //'total_actual_area' => $r_row->total_actual_area,
               // 'total_male'        => $r_row->total_male,
                //'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                //'date_generated'    => date("Y-m-d H:i:s"),
                //'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
        $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;     



        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->update([
            //'regions'        => count($total_regions),
            //'provinces'      => count($national_provinces),
            //'municipalities' => count($national_municipalities),
            //'total_farmers'     => $national_data_all->total_farmers,
            //'total_bags'        => $national_data_all->total_bags,
            //'total_dist_area'   => $national_data_all->total_dist_area,
            //'total_actual_area' => $national_data_all->total_actual_area,
            //'total_male'        => $national_data_all->total_male,
            //'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            //'date_generated'    => date("Y-m-d H:i:s")
        ]);
     

     }
















     function generate_all_data(){
        //TRUNCATE
       // DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")->truncate();
       // DB::table($GLOBALS['season_prefix']."rcep_reports.lib_national_reports")->truncate();
       

        //national
        $region_count = 0;
        $ntl_province = 0;
        $ntl_municipal = 0;
        $ntl_farmers = 0;   
        $ntl_bags = 0;
        $ntl_total_dist = 0;
        $ntl_actual = 0;
        $ntl_male = 0;
        $ntl_female = 0;
        $ntl_yield = 0;
        $ntl_yield_42 = 0;
        $ntl_yield_ps = 0;
        $ntl_claimed = 0;

            $ntl_production = 0;
            $ntl_ave_weight = 0;
            $ntl_area_hardvested = 0;

            $ntl_production_42 = 0;
            $ntl_ave_weight_42 = 0;
            $ntl_area_hardvested_42 = 0;
                  $reg_count = 0;

         //Generate regional data
         $regions_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('region')
            ->orderBy('prv')
            ->get(); 

        foreach ($regions_list as $key => $value) {
            $region_prv = $GLOBALS['season_prefix']."prv_". substr($value->prv, 0, 2);
            //SELECT * FROM `COLUMNS` where TABLE_SCHEMA LIKE $GLOBALS['season_prefix']."prv_01%" and TABLE_NAME = "released" group by TABLE_SCHEMA
            $prv_list = DB::table("information_schema.COLUMNS")
                ->where("TABLE_SCHEMA", "LIKE", $region_prv."%" )
                ->where("TABLE_NAME", "released")
                ->groupby("TABLE_SCHEMA")
                ->get();

            //regional
            $region = $value->region;
            $province_count = 0;
            $municipal_count =0;
            $total_farmers = 0;
            $total_bags = 0;
            $total_dist_area = 0;
            $total_actual_area = 0;
            $total_claim_area = 0;
            $total_male = 0;
            $total_female = 0;
            $total_production = 0;
            $total_area_harvested = 0;
            $total_yield =0;
            $total_farmer_with_yield = 0;
            

            
            $regional_production = 0;
            $regional_weight_per_bags = 0;
            $regional_area = 0;
            $regional_yield = 0;

            $regional_production_42 = 0;
            $regional_weight_per_bags_42 = 0;
            $regional_area_42 = 0;
            $regional_yield_42 = 0;


      
            $prov_count = 0;
            foreach ($prv_list as $index => $prv_data) {
                $province_count += count(DB::table($prv_data->TABLE_SCHEMA.".released")
                    ->groupby("province")
                    ->get());
                
                $municipal_count += count(DB::table($prv_data->TABLE_SCHEMA.".released")
                    ->groupby("municipality")
                    ->get());

                $total_farmers += count(DB::table($prv_data->TABLE_SCHEMA.".released")
                    //->groupby("rsbsa_control_no", "farmer_id")
                    ->get());

                $total_bags += DB::table($prv_data->TABLE_SCHEMA.".released")
                    //->groupby("rsbsa_control_no", "farmer_id")
                    ->sum('bags');

                $total_actual_area += DB::table($prv_data->TABLE_SCHEMA.".released")
                    ->where("actual_area", "<=", 100)
                    //->groupby("rsbsa_control_no", "farmer_id")
                    ->sum('actual_area');

                $total_claim_area += DB::table($prv_data->TABLE_SCHEMA.".released")
                    //->groupby("rsbsa_control_no", "farmer_id")
                    ->sum('claimed_area');

                $total_male += count(DB::table($prv_data->TABLE_SCHEMA.".released")
                    ->where("sex", "LIKE", "Male")
                    //->groupby("rsbsa_control_no", "farmer_id")
                    ->get());  
                
                $total_female += count(DB::table($prv_data->TABLE_SCHEMA.".released")
                    ->where("sex", "LIKE", "Female")
                    //->groupby("rsbsa_control_no", "farmer_id")
                    ->get());  
                

               // $yield_info = DB::select(DB::raw("SELECT SUM(total_production) as total_production, SUM(area_harvested) as area_harvested, SUM(ave_weight_per_bag') as weight_per_bag, SUM('yield') as yield from ".$prv_data->TABLE_SCHEMA.".released "."WHERE ave_weight_per_bag >= 30 and ave_weight_per_bag <= 80 and ave_weight_per_bag != 0 and area_harvested != 0 and total_production != 0 and yield != 0"));





      
                   
            }

            $region_count++;
            $ntl_province += $province_count;
            $ntl_municipal += $municipal_count;
            $ntl_farmers += $total_farmers;
            $ntl_bags += $total_bags;
            $ntl_total_dist += $total_dist_area;
            $ntl_actual += $total_actual_area;
            $ntl_male += $total_male;
            $ntl_female += $total_female;
            //$ntl_yield += $total_yield;
            //$ntl_yield_ps += $total_yield;
            $ntl_claimed += $total_claim_area;
     

            //SAVE DATA
            DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")
                ->insert([
                    "region" => $region,
                    "total_provinces" => $province_count,
                    "total_municipalities" => $municipal_count,
                    "total_farmers" => $total_farmers,
                    "total_bags" => $total_bags,
                    "total_dist_area" => $total_dist_area,
                    "total_actual_area" => $total_actual_area,
                    "total_claimed_area" => $total_claim_area,
                    "total_male" => $total_male,
                    "total_female" => $total_female,
                    "yield_total_kg_production" => $regional_production,
                    "yield_area_harvested" => $regional_area,
                    "yield"=> $regional_yield,
                    "farmers_with_yield" => $total_farmer_with_yield,
                    
                ]);
        

               
        }



        //NATIONAL
        DB::table($GLOBALS['season_prefix']."rcep_reports.lib_national_reports")
                ->update([
                    "regions" => $region_count,
                    "provinces" => $ntl_province,
                    "municipalities" => $ntl_municipal,
                    "total_farmers" => $ntl_farmers,
                    "total_bags" => $ntl_bags,
                    "total_dist_area" => $ntl_total_dist,
                    "total_actual_area" => $ntl_actual,
                    "total_claimed_area" => $ntl_claimed,
                    "total_male" => $ntl_male,
                    "total_female" => $ntl_female,
                    //"yield" => $ntl_yield,
                    //"yield_ws2021" => $ntl_yield_42  
                ]);


     }

     public function national_refresh(Request $request){
            $dat = count(DB::table($GLOBALS['season_prefix']."rcep_reports.lib_national_reports")->get());

            if($dat > 0){
                $this->generate_all_data();    
                        return json_encode("Processing");
            }else{
                        return json_encode("Already Processing");
            }

            

    
     }


    function generate_all_data_ws2021(){
        

        //DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->truncate();
        //DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->truncate();
        //DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->truncate();
        //DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->truncate();

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get(); 

        $area_arr = array();
        $product_arr = array();
        $allocated = "";

        
        foreach($municipalities as $municipality_row){


            $processed_municipality = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_municipal_reports")
            ->where("province", $municipality_row->province)
            ->where("municipality", $municipality_row->municipality)
            ->first();

            if(count($processed_municipality)>0){
                continue;
            }



            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;
            $municipal_area_claimed = 0;
            $municipal_product = 0;
            $municipal_area = 0;
            $yield = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    continue;
                }




            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    //->groupBy('released.rsbsa_control_no')
                    ->get();

                $yield_check = 0;
                $computed_yield = 0;
                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('farmerID', $municipal_row->farmer_id)
                        ->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID', 'DESC')
                        ->first();
                        
                    /*if(count($farmer_profile) > 1){
                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                            ->where('farmerID', $municipal_row->farmer_id)
                            ->where('season', 'like', 'DS 2021')
                            ->orderBy('farmerID')
                            ->get(1);
                    }else{
                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                            ->where('farmerID', $municipal_row->farmer_id)
                            ->where('season', 'like', 'DS 2021')
                            ->orderBy('farmerID')
                            ->first();
                    }*/
                  


                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;
                        $weight = $farmer_profile->weight_per_bag;
                        $no_bags = $farmer_profile->yield;
                        $area = $farmer_profile->area_harvested;

                        $gender = strtoupper(substr($farmer_profile->sex, 0, 1));
                        if($gender == 'M'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        
                        if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
                            $yield_check = 1;
                



                            //check if ave weight is not 20kg
                            /*if($farmer_profile->weight_per_bag == 20){
                                $yield = 0;
                            }else{
                                $yield_check = 1;
                            }*/
                            
                            //check if ave weight is within the provincial parameters
                            /*$aveWeightLibrary = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.lib_yield_inputs")
                                ->where("province", $municipality_row->province)
                                ->where("category", "weight_per_bag")
                                ->first();

                            if(count($aveWeightLibrary)>0){
                                if($farmer_profile->weight_per_bag >= $aveWeightLibrary->from_value && $farmer_profile->weight_per_bag <= $aveWeightLibrary->to_value){   
                                    $yield_check = 1;
                                }else{
                                    $yield_check = 0;
                                }
                            }*/
                            
                            /** CHECK YIELD RANGES **/
                            /*$computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($computed_yield <= 1){
                                $yield_check = 0;
                            }else{
                                $yield_check = 1;
                            } 
                            if ($computed_yield > 13){
                                $yield_check = 0;
                            }else{
                                $yield_check = 1;
                            }*/



                            
                            $computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($farmer_profile->weight_per_bag == 20){
                                $yield_check = 0;
                            }else{
                                if($computed_yield <= 1){
                                    $yield_check = 0;
                                }else if($computed_yield > 13){
                                    $yield_check = 0;
                                }else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
                                    $yield_check = 0;
                                }else{
                                    $yield_check = 1;
                                }
                            }
                            
                            
                        }else{
                            $yield_check = 0;
                        }

                        if($yield_check > 0){
                            $municipal_product += (floatval($no_bags) * floatval($weight));
                            $municipal_area += floatval($area);
                            $farmer_dividend += 1;
                        }
    

                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        
                    }
                }
            }
            
            if($farmer_dividend<=0){
                $municipal_yield = 0;
            }else{
               // $municipal_yield = $municipal_yield / $farmer_dividend;
                $municipal_yield = ($municipal_product / $municipal_area) / 1000;
            }   
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $municipality_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }

            
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
                'yield_total_kg_production' => $municipal_product,
                'yield_area_harvested' => $municipal_area,
                'yield'             => $municipal_yield,
                'farmers_with_yield'=> $farmer_dividend,
                'allocated_province' =>  $allocated,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $municipal_area_claimed 
            ]);  
        }


        


        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => count($total_provinces),
                'total_municipalities' => count($total_municipalities),
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
        $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;       
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'        => count($total_regions),
            'provinces'      => count($national_provinces),
            'municipalities' => count($national_municipalities),
            'total_farmers'     => $national_data_all->total_farmers,
            'total_bags'        => $national_data_all->total_bags,
            'total_dist_area'   => $national_data_all->total_dist_area,
            'total_actual_area' => $national_data_all->total_actual_area,
            'total_male'        => $national_data_all->total_male,
            'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);

        //after all report data is saved, save data to mirror database
        //$this->mirror_database();
    
    


        
    }





     function generate_all_data_orig(){
       
        DB::table($GLOBALS['season_prefix'].'rcep_reports_pro.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_pro.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_pro.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_pro.lib_municipal_reports')->truncate();

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            ->get(); 
     

        $area_arr = array();
        $product_arr = array();
        $allocated = "";

        
        foreach($municipalities as $municipality_row){
            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;
            $municipal_area_claimed = 0;
            $municipal_product = 0;
            $municipal_area = 0;
            $yield = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    continue;
                }




            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    //->groupBy('released.rsbsa_control_no')
                    ->get();

                $yield_check = 0;
                $computed_yield = 0;
                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('farmerID', $municipal_row->farmer_id)
                        ->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID', 'DESC')
                        ->first();
                        
                    /*if(count($farmer_profile) > 1){
                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                            ->where('farmerID', $municipal_row->farmer_id)
                            ->where('season', 'like', 'DS 2021')
                            ->orderBy('farmerID')
                            ->get(1);
                    }else{
                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                            ->where('farmerID', $municipal_row->farmer_id)
                            ->where('season', 'like', 'DS 2021')
                            ->orderBy('farmerID')
                            ->first();
                    }*/
                  


                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;
                        $weight = $farmer_profile->weight_per_bag;
                        $no_bags = $farmer_profile->yield;
                        $area = $farmer_profile->area_harvested;

                        $gender = strtoupper(substr($farmer_profile->sex, 0, 1));
                        if($gender == 'M'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        
                        if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
                            $yield_check = 1;
                



                            //check if ave weight is not 20kg
                            /*if($farmer_profile->weight_per_bag == 20){
                                $yield = 0;
                            }else{
                                $yield_check = 1;
                            }*/
                            
                            //check if ave weight is within the provincial parameters
                            /*$aveWeightLibrary = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.lib_yield_inputs")
                                ->where("province", $municipality_row->province)
                                ->where("category", "weight_per_bag")
                                ->first();

                            if(count($aveWeightLibrary)>0){
                                if($farmer_profile->weight_per_bag >= $aveWeightLibrary->from_value && $farmer_profile->weight_per_bag <= $aveWeightLibrary->to_value){   
                                    $yield_check = 1;
                                }else{
                                    $yield_check = 0;
                                }
                            }*/
                            
                            /** CHECK YIELD RANGES **/
                            /*$computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($computed_yield <= 1){
                                $yield_check = 0;
                            }else{
                                $yield_check = 1;
                            } 
                            if ($computed_yield > 13){
                                $yield_check = 0;
                            }else{
                                $yield_check = 1;
                            }*/



                            
                            $computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
                            if($farmer_profile->weight_per_bag == 20){
                                $yield_check = 0;
                            }else{
                                if($computed_yield <= 1){
                                    $yield_check = 0;
                                }else if($computed_yield > 13){
                                    $yield_check = 0;
                                }else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
                                    $yield_check = 0;
                                }else{
                                    $yield_check = 1;
                                }
                            }
                            
                            
                        }else{
                            $yield_check = 0;
                        }

                        if($yield_check > 0){
                            $municipal_product += (floatval($no_bags) * floatval($weight));
                            $municipal_area += floatval($area);
                            $farmer_dividend += 1;
                        }
    

                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        
                    }
                }
            }
            
            if($farmer_dividend<=0){
                $municipal_yield = 0;
            }else{
               // $municipal_yield = $municipal_yield / $farmer_dividend;
                $municipal_yield = ($municipal_product / $municipal_area) / 1000;
            }   
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $municipality_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }

            
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
                'yield_total_kg_production' => $municipal_product,
                'yield_area_harvested' => $municipal_area,
                'yield'             => $municipal_yield,
                'farmers_with_yield'=> $farmer_dividend,
                'allocated_province' =>  $allocated,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $municipal_area_claimed 
            ]);  
        }


        


        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => count($total_provinces),
                'total_municipalities' => count($total_municipalities),
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
        $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;       
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'        => count($total_regions),
            'provinces'      => count($national_provinces),
            'municipalities' => count($national_municipalities),
            'total_farmers'     => $national_data_all->total_farmers,
            'total_bags'        => $national_data_all->total_bags,
            'total_dist_area'   => $national_data_all->total_dist_area,
            'total_actual_area' => $national_data_all->total_actual_area,
            'total_male'        => $national_data_all->total_male,
            'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);

        //after all report data is saved, save data to mirror database
        //$this->mirror_database();
    }

    function generate_all_data_old2(){
       //DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_national_reports')->truncate();
        //DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_regional_reports')->truncate();
        //DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')->truncate();

        //get all municipality
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->where('province', 'ILOCOS SUR')
            ->where('municipality', '!=', 'CAOAYAN')
            ->where('municipality', '!=', 'TAGUDIN')
            ->where('municipality', '!=', 'SAN ESTEBAN')
            ->where('municipality', '!=', 'SAN VICENTE')
            ->where('municipality', '!=', 'QUIRINO (ANGKAKI)')
            ->groupBy('province', 'municipality')
            ->orderBy('region_sort')
            //->limit(2)
            ->get();
     


        $area_arr = array();
        $product_arr = array();

        foreach($municipalities as $municipality_row){
            $municipal_farmers = 0;
            $municipal_bags = 0;
            $municipal_dis_area = 0;
            $municipal_actual_area = 0;
            $municipal_male = 0;
            $municipal_female = 0;
            $municipal_yield = 0;
            $municipal_area_claimed = 0;
            $municipal_product = 0;
            $municipal_area = 0;


            $database = $GLOBALS['season_prefix']."prv_".substr($municipality_row->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    continue;
                }




            $prv_dist_data = DB::table($database.".released")->first();
            $farmer_dividend = 0;
            if(count($prv_dist_data) > 0){
                $m_list = DB::table($database.".released")
                    ->where('released.bags', '!=', '0')
                    ->where('released.province', '=', $municipality_row->province)
                    ->where('released.municipality', '=', $municipality_row->municipality)
                    ->get();

                foreach($m_list as $municipal_row){
                    $municipal_farmers += 1;
                    $municipal_bags += $municipal_row->bags;
                    $municipal_area_claimed += floatval($municipal_row->claimed_area);

                    $farmer_profile = DB::table($database.".farmer_profile")
                        ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                        ->where('season', 'like', 'DS 2021')
                        ->orderBy('farmerID')
                        ->first();
                        
                    if(count($farmer_profile) > 0){
                        $municipal_dis_area += $farmer_profile->area;
                        $municipal_actual_area += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $municipal_male += 1;
                        }else{
                            $municipal_female += 1;
                        }
                        $yield = 0;
                        if($farmer_profile->yield <= 5 && $farmer_profile->yield != 0){
                            $yield = $farmer_profile->yield * 20;
                        }else{
                            $yield = $farmer_profile->yield;
                        }
                        
                        
                        if($yield > 0 && $farmer_profile->area_harvested > 0){
                            // if($yield < 50 || $yield > 120){
                            //  $yield = $yield / $farmer_profile->actual_area;
                            // }else{
                            //  $yield = $yield;
                            // }

                            $weight = $farmer_profile->weight_per_bag;
                            $no_bags = $farmer_profile->yield;
                            $area = $farmer_profile->area_harvested;

                            $yield = (floatval($no_bags) * floatval($weight)) / floatval($area);
                            $yield = $yield / 1000;


                            if($yield <= 1){
                                $yield = 0;
                            }else if ($yield > 13){
                                $yield = 0;
                            }
                        }else{
                            $yield = $farmer_profile->yield;
                        }
                        
                        
                            if($farmer_profile->weight_per_bag <=0 ||  $farmer_profile->yield <=0 || $farmer_profile->area_harvested <= 0 ){
                                $yield = 0;
                            }

                            if($farmer_profile->weight_per_bag ==20){
                                $yield = 0;
                            }

                            $aveWeightLibrary = DB::table($GLOBALS['season_prefix']."rcep_yield_encoding.lib_yield_inputs")
                                ->where("province", $municipality_row->province)
                                ->where("category", "weight_per_bag")
                                ->first();

                            if(count($aveWeightLibrary)>0){
                                if($farmer_profile->weight_per_bag >= $aveWeightLibrary->from_value && $farmer_profile->weight_per_bag <= $aveWeightLibrary->to_value){   
                                }else{
                                    $yield = 0;
                                }
                            }

                            if($yield > 0){
                                $municipal_product += (floatval($no_bags) * floatval($weight));
                                $municipal_area += floatval($area);

                                $farmer_dividend += 1;
                            }


                        $municipal_yield += $yield;
                        
                    }else{
                        $municipal_dis_area += 0;
                        $municipal_actual_area += 0;
                        $municipal_male += 0;
                        $municipal_female += 0;
                        $municipal_yield += 0;
                        
                    }
                }
            }
            
            if($municipal_yield > 0){

                if($farmer_dividend<=0){
                    $municipal_yield = 0;
                }else{
                   // $municipal_yield = $municipal_yield / $farmer_dividend;
                    $municipal_yield = ($municipal_product / $municipal_area) / 1000;
                }   
            }

            if(isset($product_arr[$municipality_row->province])){
                $product_arr[$municipality_row->province] += $municipal_product;
            }else{
                 $product_arr[$municipality_row->province] = $municipal_product;
            }

           
            if(isset($area_arr[$municipality_row->province])){
                $area_arr[$municipality_row->province] += $municipal_area;
            }else{
                 $area_arr[$municipality_row->province] = $municipal_area;
            }

            DB::table($GLOBALS['season_prefix'].'rcep_reports2.lib_municipal_reports')
            ->insert([
                'region'            => $municipality_row->region,
                'province'          => $municipality_row->province,
                'municipality'      => $municipality_row->municipality,
                'total_farmers'     => $municipal_farmers,
                'total_bags'        => $municipal_bags,
                'total_dist_area'   => $municipal_dis_area,
                'total_actual_area' => $municipal_actual_area,
                'total_male'        => $municipal_male,
                'total_female'      => $municipal_female,
                'yield'             => $municipal_yield,
                'farmers_with_yield'=> $farmer_dividend,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $municipal_area_claimed
            ]);
        }
    }

    /**
     * SAVE EXCEL FILE TO SERVER
     */

    function generate_provinceData_sheet($province){
        $province_sheet = array();
        /** PROVINCE SUMMARY DATA */

        //get overall summary for province
        $province_summary = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->where('province', $province)
            ->first();

        $province_data = [
            'Province Name' => $province,
            'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
            'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
            'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
            'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
            'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
            'Total Male' => (string) number_format($province_summary->total_male),
            'Total Female' => (string) number_format($province_summary->total_female)
        ];
        array_push($province_sheet, $province_data);

        $blank_row = [
            'Province Name' => '',
            'Covered Municipalities' => '',
            'Total Beneficiaries' => '',
            'Total Distribution Area' => '',
            'Total Actual Area' => '',
            'Total Bags Distributed' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($province_sheet, $blank_row);

        return $province_sheet;
    }

    function generate_municipalData_sheet($province){
        $municipal_table = array();
        $municipal_summary = DB::connection('rcep_reports_db')
            ->table('lib_municipal_reports')
            ->where('province', $province)
            ->orderBy('municipality')
            ->get();

        $mun_total_farmers = 0;
        $mun_total_distArea = 0;
        $mun_total_actArea = 0;
        $mun_total_male = 0;
        $mun_total_female = 0;
        $mun_total_bags = 0;

        $mun_cnt = 1;
        foreach($municipal_summary as $m_row){
            $municipal_data = [
                '#' => $mun_cnt,
                'Municipality Name' => $m_row->municipality,
                'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                'Total Male' => (string) number_format($m_row->total_male),
                'Total Female' => (string) number_format($m_row->total_female)
            ];
            array_push($municipal_table, $municipal_data);

            ++$mun_cnt;
            $mun_total_farmers += $m_row->total_farmers;
            $mun_total_distArea += $m_row->total_dist_area;
            $mun_total_actArea += $m_row->total_actual_area;
            $mun_total_male += $m_row->total_male;
            $mun_total_female += $m_row->total_female;
            $mun_total_bags += $m_row->total_bags;
        }

        $total_municipal_data = [
            '#' => '',
            'Municipality Name' => 'TOTAL: ',
            'Total Beneficiaries' => (string) number_format($mun_total_farmers),
            'Total Distribution Area' => (string) number_format($mun_total_distArea),
            'Total Actual Area' => (string) number_format($mun_total_actArea),
            'Total Bags Distributed' => (string) number_format($mun_total_bags),
            'Total Male' => (string) number_format($mun_total_male),
            'Total Female' => (string) number_format($mun_total_female)
        ];
        array_push($municipal_table, $total_municipal_data);

        return $municipal_table;
    }

    function generate_provinceList_sheet($province){
        $table_data = array();

        $province_farmer_list = DB::connection('reports_db')->table("released")
            ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by')
            ->where('released.bags', '!=', '0')
            ->where('released.province', '=', $province)
            ->orderBy('released.province', 'ASC')
            ->get();

        $total_dist_area = 0;
        $total_actual_area = 0;
        $total_bags = 0;

        foreach ($province_farmer_list as  $row) {

            //check other_info table
            $other_info_data = DB::connection('reports_db')->table("other_info")
                ->where('farmer_id', $row->farmer_id)
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
                ->first();

            if(count($other_info_data) > 0){
                $birthdate = $other_info_data->birthdate;
                $mother_fname = $other_info_data->mother_fname;
                $mother_mname = $other_info_data->mother_mname;
                $mother_lname = $other_info_data->mother_lname;
                $mother_suffix = $other_info_data->mother_suffix;

                if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                    $phone_number = "";
                }else{
                    $phone_number = $other_info_data->phone;
                }
            }else{
                $birthdate = '';
                $mother_fname = '';
                $mother_mname = '';
                $mother_lname = '';
                $mother_suffix = '';
                $phone_number = '';
            }

            //get farmer_profile
            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
                ->where('farmerID', $row->farmer_id)
                ->where('lastName', '!=', '')
                ->where('firstName', '!=', '')
                ->orderBy('farmerID')
                ->first();

            if(count($farmer_profile) > 0){
                $qr_code = $farmer_profile->distributionID;
                $farmer_fname = $farmer_profile->firstName;
                $farmer_mname = $farmer_profile->midName;
                $farmer_lname = $farmer_profile->lastName;
                $farmer_extname = $farmer_profile->extName;
                $dist_area = $farmer_profile->area;
                $actual_area = $farmer_profile->actual_area;
                $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;

                $total_dist_area += $farmer_profile->area;
                $total_actual_area += $farmer_profile->actual_area;
            }else{
                $qr_code = "N/A";
                $farmer_fname = "N/A";
                $farmer_mname = "N/A";
                $farmer_lname = "N/A";
                $farmer_extname = "N/A";
                $dist_area = 0;
                $actual_area = 0;
                $sex = "N/A";

                $total_dist_area += 0;
                $total_actual_area += 0;
            }

            //get name of encoder using released.by in sdms_db_dev
            $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
            if(count($encoder_name) > 0){
                if($encoder_name->middleName == ''){
                    $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                }else{
                    $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                }
            }else{
                $encoder_name = '';
            }

            //compute totals
            $total_bags += $row->bags;

            $data = [
                'RSBSA #' => $row->rsbsa_control_no,
                'QR Code' => $qr_code,
                "Farmer's First Name" => $farmer_fname,
                "Farmer's Middle Name" => $farmer_mname,
                "Farmer's Last Name" => $farmer_lname,
                "Farmer's Extension Name" => $farmer_extname,
                'Sex' => $sex,
                'Birth Date' => $birthdate,
                'Telephone Number' => $phone_number,
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                "Mother's First Name" => $mother_fname,
                "Mother's Middle Name" => $mother_mname,
                "Mother's Last Name" => $mother_lname,
                "Mother's Suffix" => $mother_suffix,
                'Distribution Area' => $dist_area,
                'Actual Area' => $actual_area,
                'Bags' => $row->bags,
                'Seed Variety' => $row->seed_variety,
                'Date Released' => $row->date_released,
                'Farmer ID' => $row->farmer_id,
                'Released By' => $encoder_name
            ];
            array_push($table_data, $data);
        }

        $data2 = [
            'RSBSA #' => '',
            'QR Code' => '',
            "Farmer's First Name" => '',
            "Farmer's Middle Name" => '',
            "Farmer's Last Name" => '',
            "Farmer's Extension Name" => '',
            'Sex' => '',
            'Birth Date' => '',
            'Telephone Number' => '',
            'Province' => '',
            'Municipality' => '',
            "Mother's First Name" => '',
            "Mother's Middle Name" => '',
            "Mother's Last Name" => '',
            "Mother's Suffix" => 'TOTAL: ',
            'Distribution Area' => $total_dist_area,
            'Actual Area' => $total_actual_area,
            'Bags' => $total_bags,
            'Seed Variety' => '',
            'Date Released' => '',
            'Farmer ID' => '',
            'Released By' => ''
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    public function generate_excel_server_list(){

        //clear all data in exports folder
        $file = new Filesystem;
        $file->cleanDirectory('public/excel_storage');

        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('region_sort')
            ->get();

        foreach($provinces as $p_row){
            $database = $GLOBALS['season_prefix']."prv_".substr($p_row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){
                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    $province_sheet = $this->generate_provinceData_sheet($p_row->province);
                    $municipal_table = $this->generate_municipalData_sheet($p_row->province);
                    $table_data = $this->generate_provinceList_sheet($p_row->province);
                    
                    Excel::create($p_row->province, function($excel) use ($table_data, $province_sheet, $municipal_table) {
                        $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                            $sheet->fromArray($province_sheet);
                            $sheet->fromArray($municipal_table);
                        });
    
                        $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                            $sheet->fromArray($table_data);
                        });
                    })->store('xlsx', public_path('excel_storage'));
                }
            }
        }
    }

    public function generate_excel_server_list_improvement(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('region_sort')
            ->get();

        foreach($provinces as $p_row){
            $database = $GLOBALS['season_prefix']."prv_".substr($p_row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){
                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    
                    $province_farmer_list = DB::connection('reports_db')->table("released")
                        ->select('released.province', 'released.municipality', 'released.seed_variety', 
                                'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                                'released.farmer_id', 'released.released_by', 'released.release_id')
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $p_row->province)
                        ->where('released.is_processed', 1)
                        ->orderBy('released.province', 'ASC')
                        ->get();

                    foreach ($province_farmer_list as  $row) {

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;

                            if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                            $phone_number = '';
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->where('farmerID', $row->farmer_id)
                            ->where('lastName', '!=', '')
                            ->where('firstName', '!=', '')
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $qr_code = $farmer_profile->distributionID;
                            $farmer_fname = $farmer_profile->firstName;
                            $farmer_mname = $farmer_profile->midName;
                            $farmer_lname = $farmer_profile->lastName;
                            $farmer_extname = $farmer_profile->extName;
                            $dist_area = $farmer_profile->area;
                            $actual_area = $farmer_profile->actual_area;
                            $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;
                        }else{
                            $qr_code = "N/A";
                            $farmer_fname = "N/A";
                            $farmer_mname = "N/A";
                            $farmer_lname = "N/A";
                            $farmer_extname = "N/A";
                            $dist_area = 0;
                            $actual_area = 0;
                            $sex = "N/A";
                        }

                        //get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                        if(count($encoder_name) > 0){
                            if($encoder_name->middleName == ''){
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }else{
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }
                        }else{
                            $encoder_name = '';
                        }


                        $data = [
                            'rsbsa_control_number' => $row->rsbsa_control_no,
                            'qr_code' => $qr_code,
                            "farmer_fname" => $farmer_fname,
                            "farmer_mname" => $farmer_mname,
                            "farmer_lname" => $farmer_lname,
                            "farmer_ext" => $farmer_extname,
                            'sex' => $sex,
                            'birthdate' => $birthdate,
                            'tel_number' => $phone_number,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            "mother_fname" => $mother_fname,
                            "mother_mname" => $mother_mname,
                            "mother_lname" => $mother_lname,
                            "mother_ext" => $mother_suffix,
                            'dist_area' => $dist_area,
                            'actual_area' => $actual_area,
                            'bags' => $row->bags,
                            'seed_variety' => $row->seed_variety,
                            'date_released' => $row->date_released,
                            'farmer_id' => $row->farmer_id,
                            'released_by' => $encoder_name
                        ];
                        DB::table($GLOBALS['season_prefix'].'rcep_excel.beneficiary_list')->insert($data);

                        //after processing to seed beneficiary list DB update is_processed flag to 0
                        DB::table($database.'.released')->where('release_id', $row->release_id)->update([
                            'is_processed' => 0
                        ]);
                    }

                }
            }
        }
    }

    
    function generate_all_data2(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->truncate();

        $region_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->groupBy('regionName')
            ->orderBy('region_sort', 'ASC')
            ->get();

        //national-report variables
        $male_count = 0;
        $female_count = 0;
        $total_farmers_count = 0;
        $total_bags_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_regions = 1;
        $covered_municipalities = 0;
        $covered_provinces = 0;

        foreach($region_list as $row){
            $total_regions += 1;
            //get province list
            $province_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                ->where('region', $row->regionName)
                ->groupBy('province')
                ->get();

            //regional-report variables
            $region_provinces = 0;
            $region_municipalities = 0;
            $region_farmers = 0;
            $region_bags = 0;
            $region_dis_area = 0;
            $region_actual_area = 0;
            $region_male = 0;
            $region_female = 0;

            foreach($province_list as $prv_list){

                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);
                $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                    ->where('region', $prv_list->region)
                    ->where('province', $prv_list->province)
                    ->groupBy('municipality')
                    ->get();

                foreach($municipalities as $municipality_row){
                    $municipal_farmers = 0;
                    $municipal_bags = 0;
                    $municipal_dis_area = 0;
                    $municipal_actual_area = 0;
                    $municipal_male = 0;
                    $municipal_female = 0;

                    $prv_dist_data = DB::table($database.".released")->first();
                    if(count($prv_dist_data) > 0){
                        $m_list = DB::table($database.".released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $municipality_row->province)
                            ->where('released.municipality', '=', $municipality_row->municipality)
                            ->get();

                        foreach($m_list as $municipal_row){
                            $municipal_farmers += 1;
                            $municipal_bags += $municipal_row->bags;

                            $farmer_profile = DB::table($database.".farmer_profile")
                                ->where('rsbsa_control_no', $municipal_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();
                                
                            if(count($farmer_profile) > 0){
                                $municipal_dis_area += $farmer_profile->area;
                                $municipal_actual_area += $farmer_profile->actual_area;

                                if($farmer_profile->sex == 'Male'){
                                    $municipal_male += 1;
                                }else{
                                    $municipal_female += 1;
                                }
                            }else{
                                $municipal_dis_area += 0;
                                $municipal_actual_area += 0;
                                $municipal_male += 0;
                                $municipal_female += 0;
                            }
                        }
                    }

                    DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                    ->insert([
                        'province'          => $prv_list->province,
                        'municipality'      => $municipality_row->municipality,
                        'total_farmers'     => $municipal_farmers,
                        'total_bags'        => $municipal_bags,
                        'total_dist_area'   => $municipal_dis_area,
                        'total_actual_area' => $municipal_actual_area,
                        'total_male'        => $municipal_male,
                        'total_female'      => $municipal_female,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

                //province-report variables
                $province_municipalities = 0;
                $province_farmers = 0;
                $province_bags = 0;
                $province_dis_area = 0;
                $province_actual_area = 0;
                $province_male = 0;
                $province_female = 0;

                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    
                    $covered_provinces += 1;
                    $region_provinces += 1;
                    
                    //get municipalities
                    $number_of_municipalities = DB::table($database.".released")
                        ->select('released.municipality')
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->groupBy('municipality')
                        ->get();

                    $covered_municipalities += count($number_of_municipalities);
                    $region_municipalities += count($number_of_municipalities);
                    $province_municipalities += count($number_of_municipalities);

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::table($database.".released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->get();

                    foreach($report_list as $report_row){
                        $total_farmers_count += 1;
                        $total_bags_count += $report_row->bags;

                        $region_farmers += 1;
                        $region_bags += $report_row->bags;

                        $province_farmers += 1;
                        $province_bags += $report_row->bags;

                        $farmer_profile = DB::table($database.".farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;
                            
                            $region_dis_area += $farmer_profile->area;
                            $region_actual_area += $farmer_profile->actual_area;

                            $province_dis_area += $farmer_profile->area;
                            $province_actual_area += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                                $region_male += 1;
                                $province_male += 1;
                            }else{
                                $female_count += 1;
                                $region_female += 1;
                                $province_female += 1;
                            }

                        }else{
                            $total_dist_area_count += 0;
                            $total_actual_area_count += 0;
                            $male_count += 0;
                            $female_count += 0;

                            $region_dis_area += 0;
                            $region_actual_area += 0;
                            $region_male += 0;
                            $region_female += 0;

                            $province_dis_area += 0;
                            $province_actual_area += 0;
                            $province_male += 0;
                            $province_female += 0;
                        }
                    }
                }

                DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->region,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $province_municipalities,
                    'total_farmers'     => $province_farmers,
                    'total_bags'        => $province_bags,
                    'total_dist_area'   => $province_dis_area,
                    'total_actual_area' => $province_actual_area,
                    'total_male'        => $province_male,
                    'total_female'      => $province_female,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }

            //insert the data | regional report
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'            => $prv_list->region,
                'total_provinces'   => $region_provinces,
                'total_municipalities' => $region_municipalities,
                'total_farmers'     => $region_farmers,
                'total_bags'        => $region_bags,
                'total_dist_area'   => $region_dis_area,
                'total_actual_area' => $region_actual_area,
                'total_male'        => $region_male,
                'total_female'      => $region_female,
                'date_generated'    => date("Y-m-d H:i:s")
            ]);
        }

        //finally insert the data | national report
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);


        $this->mirror_database();
    }

    public function forceNational(){

        

        //after saaving municipalities save provincial
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region','province',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('COUNT(report_id) as total_municipalities'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(yield) as total_yield'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->where('total_bags', '!=', 0)
            ->groupBy('province')
            ->orderBy('report_id')
            ->get();

        $allocated = "";
        $provincial_yield = 0;
        foreach($provinces as $p_row){    
            
            //check if the province is allocated this ws2021
            $allowedLoc = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_yield_provinces")
                ->where("province", $p_row->province)
                ->first();
            if(count($allowedLoc)>0){
                $allocated = "Y";
            }else{
                $allocated = "N";
            }
            
            //compute provincial yield
            if($p_row->yield_total_kg_production != 0 &&  $p_row->yield_area_harvested != 0){
                $provincial_total_kg_production = $p_row->yield_total_kg_production;
                $provincial_area_harvested = $p_row->yield_area_harvested;
                $provincial_yield = ($provincial_total_kg_production / $provincial_area_harvested) / 1000;
            }else{
                $provincial_total_kg_production = 0;
                $provincial_area_harvested = 0;
                $provincial_yield = 0;
            }
            //$provincial_yield = ($p_row->yield_total_kg_production / $p_row->yield_area_harvested) / 1000;

            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
            ->insert([
                'region'            => $p_row->region,
                'province'          => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers'     => $p_row->total_farmers,
                'total_bags'        => $p_row->total_bags,
                'total_dist_area'   => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male'        => $p_row->total_male,
                'total_female'      => $p_row->total_female,
                'yield_total_kg_production' => $provincial_total_kg_production,
                'yield_area_harvested' => $provincial_area_harvested,
                'yield'             => $provincial_yield,
                'farmers_with_yield'=> $p_row->total_farmers_with_yield,
                'total_claimed_area' => $p_row->claimed_area,
                'date_generated'    => date("Y-m-d H:i:s"),
                'allocated_province' => $allocated
            ]);
        }

        //after saaving province data save region
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->orderBy('report_id')
            ->get();

        $regional_yield = 0;
        foreach($regions as $r_row){
            $total_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->get();

            $total_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                ->where('region', $r_row->region)
                ->where('total_bags', '!=', 0)
                ->where('yield', '>', 0)
                ->groupBy('province')
                ->get();
                
            //compute provincial yield
            if($r_row->yield_total_kg_production != 0 &&  $r_row->yield_area_harvested != 0){
                $regional_total_kg_production = $r_row->yield_total_kg_production;
                $regional_area_harvested = $r_row->yield_area_harvested;
                $regional_yield = ($regional_total_kg_production / $regional_area_harvested) / 1000;
            }else{
                $regional_total_kg_production = 0;
                $regional_area_harvested = 0;
                $regional_yield = 0;
            }
            //$regional_yield = ($r_row->yield_total_kg_production / $r_row->yield_area_harvested) / 1000;                
            
            DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
            ->insert([
                'region'               => $r_row->region,
                'total_provinces'      => count($total_provinces),
                'total_municipalities' => count($total_municipalities),
                'total_farmers'     => $r_row->total_farmers,
                'total_bags'        => $r_row->total_bags,
                'total_dist_area'   => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male'        => $r_row->total_male,
                'total_female'      => $r_row->total_female,
                'yield_total_kg_production' => $regional_total_kg_production,
                'yield_area_harvested' => $regional_area_harvested,
                'yield'             => $regional_yield,
                'farmers_with_yield'=> $r_row->total_farmers_with_yield,
                'date_generated'    => date("Y-m-d H:i:s"),
                'total_claimed_area' => $r_row->claimed_area
            ]);
        }

        //after saving region data save national        
        $national_data_all = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('region',DB::raw('SUM(total_farmers) as total_farmers'),
                DB::raw('SUM(total_bags) as total_bags'),
                DB::raw('SUM(total_dist_area) as total_dist_area'),
                DB::raw('SUM(total_actual_area) as total_actual_area'),
                DB::raw('SUM(total_male) as total_male'),
                DB::raw('SUM(total_female) as total_female'),
                DB::raw('SUM(total_claimed_area) as claimed_area'),
                DB::raw('SUM(farmers_with_yield) as total_farmers_with_yield'), 
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = 0;
        $total_regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('region')
            ->get();

        $national_provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->groupBy('province')
            ->get();
            
        $national_municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->where('total_bags', '!=', 0)
            //->where('yield', '>', 0)
            ->get();
            
        //compute 42 provinces data
        $province_42_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select(
                DB::raw('SUM(yield_total_kg_production) as yield_total_kg_production'),
                DB::raw('SUM(yield_area_harvested) as yield_area_harvested'))
            ->where('allocated_province', 'Y')
            //->where('yield', '>', 0)
            ->first();
            
        $national_yield = ($national_data_all->yield_total_kg_production / $national_data_all->yield_area_harvested) / 1000; 
        $national_yield_42 = ($province_42_data->yield_total_kg_production / $province_42_data->yield_area_harvested) / 1000;       
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')
        ->insert([
            'regions'        => count($total_regions),
            'provinces'      => count($national_provinces),
            'municipalities' => count($national_municipalities),
            'total_farmers'     => $national_data_all->total_farmers,
            'total_bags'        => $national_data_all->total_bags,
            'total_dist_area'   => $national_data_all->total_dist_area,
            'total_actual_area' => $national_data_all->total_actual_area,
            'total_male'        => $national_data_all->total_male,
            'total_female'      => $national_data_all->total_female,
            'yield'             => $national_yield,
            'yield_ws2021'      => $national_yield_42,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);


        
    }


    function generate_national_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')->truncate();

        //get all regions
        $region_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('region')->orderBy('region_sort', 'ASC')->get();
        
        //report variables
        $male_count = 0;
        $female_count = 0;
        $total_farmers_count = 0;
        $total_bags_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_regions = 1;

        $covered_municipalities = 0;
        $covered_provinces = 0;

        foreach($region_prv as $r_prv){
            $region_code = $r_prv->regCode;

            //count all regions
            $total_regions += 1;

            //get all provinces in region || prv databases
            $province_list = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regCode', $region_code)
                ->groupBy('province')
                ->get();
            
            //loop through all prv(s)
            foreach($province_list as $prv_list){
                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);

                //check if database exists
                //\Config::set('database.connections.reports_db.database', $database);
                //DB::purge('reports_db');
                $table_conn = $this->set_database($database);

                if($table_conn == "Connection Established!"){
                    //check if database has distribution data
                    $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                    if(count($prv_dist_data) > 0){

                        $covered_provinces += 1;

                        //get municipalities
                        $number_of_municipalities = DB::connection('reports_db')->table("released")
                            ->select('released.municipality')
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->groupBy('municipality')
                            ->get();

                        $covered_municipalities += count($number_of_municipalities);

                        /*$report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'),
                                    DB::raw('SUM(farmer_profile.area) as dist_area'),
                                    DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            //->where('released.municipality', '=', $prv_list->municipality)
                            ->first();

                        if(count($report_res) > 0){
                            $sex_res = DB::connection('reports_db')->table("released")
                                ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                                ->join('farmer_profile', function ($table_join) {
                                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                                })
                                ->where('released.province', '=', $prv_list->province)
                                //->where('released.municipality', '=', $prv_list->municipality)
                                ->groupBy('sex')
                                ->orderBy('sex', 'DESC')
                                ->get();
        
                            foreach($sex_res as $s_row){
                                if($s_row->sex == 'Male'){
                                    $male_count += $s_row->sex_count;
                                }elseif($s_row->sex == 'Femal'){
                                    $female_count += $s_row->sex_count;
                                }
                            }
                        }*/

                        //get total farmers & total bags
                        $report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'))
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->first();

                        //compute total distribution area, actual area, sex_count etc.
                        $report_list = DB::connection('reports_db')->table("released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->get();

                        foreach($report_list as $report_row){
                            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                                ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();

                            if(count($farmer_profile) > 0){
                                $total_dist_area_count += $farmer_profile->area;
                                $total_actual_area_count += $farmer_profile->actual_area;

                                if($farmer_profile->sex == 'Male'){
                                    $male_count += 1;
                                }else{
                                    $female_count += 1;
                                }

                            }else{
                                $total_dist_area_count += 0;
                                $total_actual_area_count += 0;
                                $male_count += 0;
                                $female_count += 0;
                            }
                        }

                        $total_farmers_count += $report_res->total_farmers;
                        $total_bags_count += $report_res->total_bags;
                        //$total_dist_area_count += $report_res->dist_area;
                        //$total_actual_area_count += $report_res->actual_area;
                    }
                }
            }
        }

        //finally insert the data
        //save region data to database
        DB::connection('rcep_reports_db')->table('lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);
        
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')
        ->insert([
            'regions'           => $total_regions,
            'provinces'         => $covered_provinces,
            'municipalities'    => $covered_municipalities,
            'total_farmers'     => $total_farmers_count,
            'total_bags'        => $total_bags_count,
            'total_dist_area'   => $total_dist_area_count,
            'total_actual_area' => $total_actual_area_count,
            'total_male'        => $male_count,
            'total_female'      => $female_count,
            'date_generated'    => date("Y-m-d H:i:s")
        ]);
    }

    function generate_regional_data(){

        //empty database
        DB::connection('rcep_reports_db')->table('lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')->truncate();

        $region_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->groupBy('region')->orderBy('region_sort')->get();
        
        foreach($region_prv as $r_prv){
            $region_code = $r_prv->regCode;

            //get all provinces in region || prv databases
            $province_list = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regCode', $region_code)
                ->groupBy('province')
                ->get();
            
            //report variables
            $male_count = 0;
            $female_count = 0;
            $total_farmers_count = 0;
            $total_bags_count = 0;
            $total_dist_area_count = 0;
            $total_actual_area_count = 0;

            $total_provinces = 0;
            $total_municipalities = 0;

            //loop through all prv(s)
            foreach($province_list as $prv_list){
                $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);

                //check if database exists
                //\Config::set('database.connections.reports_db.database', $database);
                //DB::purge('reports_db');
                $table_conn = $this->set_database($database);

                if($table_conn == "Connection Established!"){

                    //check if database has distribution data
                    $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                    if(count($prv_dist_data) > 0){

                        //total provinces covered...
                        $province_count =  DB::connection('reports_db')->table("released")
                            ->groupBy('province')
                            ->get();
                        $total_provinces += count($province_count);

                        //total municipalities covered...
                        $mun_count =  DB::connection('reports_db')->table("released")
                            ->groupBy('municipality')
                            ->get();
                        $total_municipalities += count($mun_count);

                        /*$report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'),
                                    DB::raw('SUM(farmer_profile.area) as dist_area'),
                                    DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            //->where('released.municipality', '=', $prv_list->municipality)
                            ->first();

                        if(count($report_res) > 0){
                            $sex_res = DB::connection('reports_db')->table("released")
                                ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                                ->join('farmer_profile', function ($table_join) {
                                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                                })
                                ->where('released.province', '=', $prv_list->province)
                                //->where('released.municipality', '=', $prv_list->municipality)
                                ->groupBy('sex')
                                ->orderBy('sex', 'DESC')
                                ->get();
        
                            foreach($sex_res as $s_row){
                                if($s_row->sex == 'Male'){
                                    $male_count += $s_row->sex_count;
                                }elseif($s_row->sex == 'Femal'){
                                    $female_count += $s_row->sex_count;
                                }
                            }
                        }*/

                        //get total farmers & total bags
                        $report_res = DB::connection('reports_db')->table("released")
                            ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                    DB::raw('SUM(released.bags) as total_bags'))
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->first();

                        //compute total distribution area, actual area, sex_count etc.
                        $report_list = DB::connection('reports_db')->table("released")
                            ->where('released.bags', '!=', '0')
                            ->where('released.province', '=', $prv_list->province)
                            ->get();

                        foreach($report_list as $report_row){
                            $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                                ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                                ->orderBy('farmerID')
                                ->first();

                            if(count($farmer_profile) > 0){
                                $total_dist_area_count += $farmer_profile->area;
                                $total_actual_area_count += $farmer_profile->actual_area;

                                if($farmer_profile->sex == 'Male'){
                                    $male_count += 1;
                                }else{
                                    $female_count += 1;
                                }

                            }else{
                                $total_dist_area_count += 0;
                                $total_actual_area_count += 0;
                                $male_count += 0;
                                $female_count += 0;
                            }
                        }

                        $total_farmers_count += $report_res->total_farmers;
                        $total_bags_count += $report_res->total_bags;
                    }
                }
            }

            //save region data to database
            DB::connection('rcep_reports_db')->table('lib_regional_reports')
                ->insert([
                    'region'            => $r_prv->regionName,
                    'total_provinces'   => $total_provinces,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => $total_farmers_count,
                    'total_bags'        => $total_bags_count,
                    'total_dist_area'   => $total_dist_area_count,
                    'total_actual_area' => $total_actual_area_count,
                    'total_male'        => $male_count,
                    'total_female'      => $female_count,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
                
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->insert([
                    'region'            => $r_prv->regionName,
                    'total_provinces'   => $total_provinces,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => $total_farmers_count,
                    'total_bags'        => $total_bags_count,
                    'total_dist_area'   => $total_dist_area_count,
                    'total_actual_area' => $total_actual_area_count,
                    'total_male'        => $male_count,
                    'total_female'      => $female_count,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            
        }
    }

    function generate_provincial_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')->truncate();
    
        $province_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->groupBy('province')
            ->orderBy('regCode')
            ->orderBy('province', 'DESC')
            ->get();

        foreach($province_prv as $prv_list){
            $database = $GLOBALS['season_prefix']."prv_".substr($prv_list->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){

                $male_count = 0;
                $female_count = 0;
                $total_municipalities = 0;

                $total_dist_area_count = 0;
                $total_actual_area_count = 0;

                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){

                    //total municipalities covered...
                    $mun_count =  DB::connection('reports_db')->table("released")
                        ->groupBy('municipality')
                        ->get();
                    $total_municipalities += count($mun_count);

                    /*$report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                            //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.province', '=', $prv_list->province)
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
    
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }*/

                    //get total farmers & total bags
                    $report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'))
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->first();

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::connection('reports_db')->table("released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $prv_list->province)
                        ->get();

                    foreach($report_list as $report_row){
                        /*$farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        $total_dist_area_count += $farmer_profile->area;
                        $total_actual_area_count += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $male_count += 1;
                        }else{
                            $female_count += 1;
                        }*/

                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                            }else{
                                $female_count += 1;
                            }

                        }else{
                            $total_dist_area_count += 0;
                            $total_actual_area_count += 0;
                            $male_count += 0;
                            $female_count += 0;
                        }
                    }

                    //save province data to database
                    DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                    
                    DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }else{
                    //save province data to database || no distribution data
                    DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                    
                    DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->insert([
                        'region'            => $prv_list->regionName,
                        'province'          => $prv_list->province,
                        'total_municipalities' => $total_municipalities,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

            }else{
                //save province data to database || no database
                DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->regionName,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
                
                DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                ->insert([
                    'region'            => $prv_list->regionName,
                    'province'          => $prv_list->province,
                    'total_municipalities' => $total_municipalities,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }

        }
    }

    function generate_municipal_data(){
        //empty database
        DB::connection('rcep_reports_db')->table('lib_municipal_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')->truncate();

        //list of municipalities
        $mun_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->orderBy('regCode')
            ->orderBy('provCode')
            ->orderBy('municipality', 'ASC')
            ->get();

        foreach($mun_prv as $row){
            $database = $GLOBALS['season_prefix']."prv_".substr($row->prv,0,4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){

                $male_count = 0;
                $female_count = 0;

                $total_dist_area_count = 0;
                $total_actual_area_count = 0;

                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){
                    /*$report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                            $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                                //$table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->where('released.province', '=', $row->province)
                            ->where('released.municipality', '=', $row->municipality)
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
    
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }*/

                    //get total farmers & total bags
                    $report_res =  DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'))
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->first();

                    //compute total distribution area, actual area, sex_count etc.
                    $report_list = DB::connection('reports_db')->table("released")
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $row->province)
                        ->where('released.municipality', '=', $row->municipality)
                        ->get();

                    foreach($report_list as $report_row){
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $report_row->rsbsa_control_no)
                            ->orderBy('farmerID')
                            ->first();

                        /*$total_dist_area_count += $farmer_profile->area;
                        $total_actual_area_count += $farmer_profile->actual_area;

                        if($farmer_profile->sex == 'Male'){
                            $male_count += 1;
                        }else{
                            $female_count += 1;
                        }*/

                        if(count($farmer_profile) > 0){
                            $total_dist_area_count += $farmer_profile->area;
                            $total_actual_area_count += $farmer_profile->actual_area;

                            if($farmer_profile->sex == 'Male'){
                                $male_count += 1;
                            }else{
                                $female_count += 1;
                            }

                        }else{
                            $total_dist_area_count += 0;
                            $total_actual_area_count += 0;
                            $male_count += 0;
                            $female_count += 0;
                        }
                    }

                    //save municipal data to database
                    DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                    
                    DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => $report_res->total_farmers,
                        'total_bags'        => $report_res->total_bags,
                        'total_dist_area'   => $total_dist_area_count,
                        'total_actual_area' => $total_actual_area_count,
                        'total_male'        => $male_count,
                        'total_female'      => $female_count,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }else{
                    //save municipal data to database || no distribution data
                    DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                    
                    DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
                    ->insert([
                        'province'          => $row->province,
                        'municipality'      => $row->municipality,
                        'total_farmers'     => 0,
                        'total_bags'        => 0,
                        'total_dist_area'   => 0,
                        'total_actual_area' => 0,
                        'total_male'        => 0,
                        'total_female'      => 0,
                        'date_generated'    => date("Y-m-d H:i:s")
                    ]);
                }

            }else{
                //save municipal data to database || no database
                DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->insert([
                    'province'          => $row->province,
                    'municipality'      => $row->municipality,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
                
                DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
                ->insert([
                    'province'          => $row->province,
                    'municipality'      => $row->municipality,
                    'total_farmers'     => 0,
                    'total_bags'        => 0,
                    'total_dist_area'   => 0,
                    'total_actual_area' => 0,
                    'total_male'        => 0,
                    'total_female'      => 0,
                    'date_generated'    => date("Y-m-d H:i:s")
                ]);
            }
        }
    }


      public function generate_variety_data(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_variety_report')->truncate();
        
        
        $drop_off_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->get();
            //dd($drop_off_list);
        //get all prv(s) in the dropoff point list and getting distinct values
        $prv_list = array();
        foreach($drop_off_list as $dop_row){
            $prv_list[] = $GLOBALS['season_prefix']."prv_".substr($dop_row->prv_dropoff_id, 0, 4);   
            //$prv_list_data[]  =  "prv_".substr($dop_row->prv_dropoff_id, 0, 4);
        }


        $prv_list = array_unique($prv_list);
       // dd($prv_list);
        //loop through all prv databases
        foreach($prv_list as $prv_database){
           
                //check if database has distribution data
                $prv_dist_data = DB::table($prv_database.".new_released")->first();
                if(count($prv_dist_data) > 0){
                    $variety_res = DB::table($prv_database.".new_released")
                        ->select(DB::raw('SUM(new_released.bags_claimed) as total_seed_bags'),'seed_variety', 'province', 'municipality',DB::raw('SUM(new_released.claimed_area) as total_area'),DB::raw('COUNT(new_released.content_rsbsa) as total_farmers') )
                        ->groupBy(TRIM('seed_variety'), 'municipality')
                        ->where("category", "INBRED")
                        ->get();
                      //  dd($variety_res);
                    foreach($variety_res as $v_row){

                        //get region
                        $region_name = DB::connection('delivery_inspection_db')
                            ->table('lib_dropoff_point')
                            ->where('province', $v_row->province)
                            ->groupBy('province')
                            ->value('region');

                        //save to database per total variety of each prv database
                        DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags,
                            'total_area' => $v_row->total_area,
                            'total_farmers' => $v_row->total_farmers
                        ]);
                        
                       
                    }
                }
            
        }
    }
    



    public function generate_variety_data_old(){
        DB::connection('rcep_reports_db')->table('lib_variety_report')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_variety_report')->truncate();
        
        $drop_off_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->get();

        //get all prv(s) in the dropoff point list and getting distinct values
        $prv_list = array();
        foreach($drop_off_list as $dop_row){
            $prv_list[] = $GLOBALS['season_prefix']."prv_".substr($dop_row->prv_dropoff_id, 0, 4);     
        }
        $prv_list = array_unique($prv_list);

        //loop through all prv databases
        foreach($prv_list as $prv_database){
            $table_conn = $this->set_database($prv_database);
            if($table_conn == "Connection Established!"){
                //check if database has distribution data
                $prv_dist_data = DB::connection('reports_db')->table("released")->first();
                if(count($prv_dist_data) > 0){
                    $variety_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('SUM(released.bags) as total_seed_bags'),'seed_variety', 'province', 'municipality')
                        ->groupBy('seed_variety', 'municipality')
                        ->get();

                    foreach($variety_res as $v_row){

                        //get region
                        $region_name = DB::connection('delivery_inspection_db')
                            ->table('lib_dropoff_point')
                            ->where('province', $v_row->province)
                            ->groupBy('province')
                            ->value('region');

                        //save to database per total variety of each prv database
                        DB::connection('rcep_reports_db')->table('lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags,
                        ]);
                        
                        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_variety_report')
                        ->insert([
                            'region' => $region_name,
                            'province' => $v_row->province,
                            'municipality' => $v_row->municipality,
                            'prv_database' => $prv_database,
                            'seed_variety' => $v_row->seed_variety,
                            'total_volume' => $v_row->total_seed_bags
                        ]);
                    }
                }
            }
        }
    }
    
    function mirror_database(){
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')->truncate();
        DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')->truncate();

        $national_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_national_reports')->get();
        foreach($national_report as $n_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')
            ->insert([
                'regions' => $n_row->regions,
                'provinces' => $n_row->provinces,
                'municipalities' => $n_row->municipalities,
                'total_farmers' => $n_row->total_farmers,
                'total_bags' => $n_row->total_bags,
                'total_dist_area' => $n_row->total_dist_area,
                'total_actual_area' => $n_row->total_actual_area,
                'total_male' => $n_row->total_male,
                'total_female' => $n_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }

        $regional_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')->get();
        foreach($regional_report as $r_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
            ->insert([
                'region' => $r_row->region,
                'total_provinces' => $r_row->total_provinces,
                'total_municipalities' => $r_row->total_municipalities,
                'total_farmers' => $r_row->total_farmers,
                'total_bags' => $r_row->total_bags,
                'total_dist_area' => $r_row->total_dist_area,
                'total_actual_area' => $r_row->total_actual_area,
                'total_male' => $r_row->total_male,
                'total_female' => $r_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }

        $province_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->get();
        foreach($province_report as $p_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
            ->insert([
                'region' => $p_row->region,
                'province' => $p_row->province,
                'total_municipalities' => $p_row->total_municipalities,
                'total_farmers' => $p_row->total_farmers,
                'total_bags' => $p_row->total_bags,
                'total_dist_area' => $p_row->total_dist_area,
                'total_actual_area' => $p_row->total_actual_area,
                'total_male' => $p_row->total_male,
                'total_female' => $p_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }

        $municipal_report = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')->get();
        foreach($municipal_report as $m_row){
            DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_municipal_reports')
            ->insert([
                'province' => $m_row->province,
                'municipality' => $m_row->municipality,
                'total_farmers' => $m_row->total_farmers,
                'total_bags' => $m_row->total_bags,
                'total_dist_area' => $m_row->total_dist_area,
                'total_actual_area' => $m_row->total_actual_area,
                'total_male' => $m_row->total_male,
                'total_female' => $m_row->total_female,
                'date_generated' => date("Y-m-d H:i:s")
            ]);
        }
    }

    //API for auto-insertion of generated report data every 12 MN
    public function scheduledReport(Request $request){
        $this->generate_all_data();
        //$this->generate_national_data();
        //$this->generate_regional_data();
        //$this->generate_provincial_data();
        //$this->generate_municipal_data();
        
       // $this->generate_variety_data();

        DB::connection('mysql')->table('lib_logs')
        ->insert([
            'category' => 'REPORT',
            'description' => 'Pre-Scheduled report generation successfull',
            'author' => 'SYSTEM',
            'ip_address' => 'LOCAL'
        ]);
    }
    
    public function forceUpdateVarities(){
        $this->generate_variety_data();
    }

    public function municipalityDropOff(Request $request){
        $municipalities = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->where('province', '=', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
        $return_str= '';
        foreach($municipalities as $municipality){
            $return_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $return_str;
    }

    public function dropOffName(Request $request){
        $dropoffs = DB::connection('delivery_inspection_db')
            ->table('lib_dropoff_point')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->orderBy('dropOffPoint', 'asc')
            ->groupBy('prv_dropoff_id')
            ->get();
        $return_str= '';
        foreach($dropoffs as $dropoff){
            $return_str .= "<option value='$dropoff->prv_dropoff_id'>$dropoff->dropOffPoint</option>";
        }
        return $return_str;
    }

    public function Home_Provincial(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('reports.home2')->with('regions', $regions);
    }

    public function Home_Regional(){
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('reports.home3')->with('regions', $regions);
    }

    public function SeedBeneficiariesVarieties(Request $request){
        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }   

        if($table_conn == "established_connection"){
            //generate data table
            return Datatables::of(DB::connection('reports_db')->table("released")
                ->select('seed_variety', DB::raw('SUM(released.bags) as total_varieties'))
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->groupBy('released.seed_variety')
                ->orderBy('released.seed_variety')
            )->make(true);
        }
    }

    public function SeedBeneficiariesVarietiesProvincial(Request $request){
        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }   

        if($table_conn == "established_connection"){
            //generate data table
            return Datatables::of(DB::connection('reports_db')->table("released")
                ->select('seed_variety', DB::raw('SUM(released.bags) as total_varieties'))
                ->where('released.province', '=', $request->province)
                ->where('released.bags', '!=', '0')
                ->groupBy('released.seed_variety')
                ->orderBy('released.seed_variety')
            )->make(true);
        }
    }

    public function SeedBeneficiaries(Request $request){

        //return province table
        $prov_code = DB::connection('mysql')->table('lib_provinces')->where('provDesc', '=', $request->province)->first()->provCode;

        //append to prv fro db connection
        $table = $GLOBALS['season_prefix'].'prv_'.$prov_code;

        \Config::set('database.connections.reports_db.database', $table);
        DB::purge('reports_db');

        try{
            $tbl_check = DB::connection('reports_db')->table("pending_release")->first();

            if(count($tbl_check) > 0){
                $table_conn = "established_connection";
            }else{
                $table_conn = "no_table_found";
            }
        }catch(\Illuminate\Database\QueryException $ex){
            $table_conn = "no_table_found";
        }

        if($table_conn == "established_connection"){
            $male_count = 0;
            $female_count = 0;
            $report_res = DB::connection('reports_db')->table("released")
                ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                        DB::raw('SUM(released.bags) as total_bags'),
                        DB::raw('SUM(farmer_profile.area) as dist_area'),
                        DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                ->join('farmer_profile', function ($table_join) {
                    $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                    $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                })
                ->where('released.province', '=', $request->province)
                ->where('released.municipality', '=', $request->municipality)
                ->where('released.prv_dropoff_id', '=', $request->dropoff)
                ->where('released.bags', '!=', '0')
                ->first();

            if(count($report_res) > 0){
                $sex_res = DB::connection('reports_db')->table("released")
                    ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                    ->join('farmer_profile', function ($table_join) {
                        $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no');
                        $table_join->on('farmer_profile.farmerID', '=', 'released.farmer_id');
                    })
                    ->where('released.province', '=', $request->province)
                    ->where('released.municipality', '=', $request->municipality)
                    ->where('released.prv_dropoff_id', '=', $request->dropoff)
                    ->groupBy('sex')
                    ->orderBy('sex', 'DESC')
                    ->get();

                foreach($sex_res as $s_row){
                    if($s_row->sex == 'Male'){
                        $male_count = $s_row->sex_count;
                    }elseif($s_row->sex == 'Femal'){
                        $female_count = $s_row->sex_count;
                    }
                }
            }
            
            $data_arr = array(
                'table_name' => $table,
                'table_conn' => $table_conn,
                'total_bags' => number_format($report_res->total_bags),
                'dist_area' => number_format($report_res->dist_area, '2'),
                'actual_area' => number_format($report_res->actual_area, '2'),
                'total_farmers' => number_format($report_res->total_farmers),
                'total_male' => number_format($male_count),
                'total_female' => number_format($female_count)
            );

            return $data_arr;
        }else{
            $data_arr = array(
                'table_name' => $table,
                'table_conn' => $table_conn,
                'total_bags' => '',
                'total_area' => '',
                'total_farmers' => ''
            );

            return $data_arr;
        }

    }

    /* REPORT HEADER */
    public function TotalValues(Request $request){
        $municipalities = count(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('municipality')
            ->groupBy('municipality')
            ->get()); 
            
        $provinces = count(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select('province')
            ->groupBy('province')
            ->get()); 

        //to get total_beneficiaries, actual area, distribution area & bags_distributed
        $prv_databases = DB::connection('mysql')->table('lib_seasons_data')->get();

        $total_farmers = 0;
        $total_bags = 0;
        $dist_area = 0;
        $actual_area = 0;
        $male_count = 0;
        $female_count = 0;

        foreach($prv_databases as $prv_row){
            $db_name = $GLOBALS['season_prefix']."prv_".$prv_row->prv_code;
            $test = DB::select("SELECT SCHEMA_NAME
                                    FROM INFORMATION_SCHEMA.SCHEMATA
                                WHERE SCHEMA_NAME = '$db_name'");
            if(empty($test)){
                // no prv database found
            }else{
                //DB exists
                \Config::set('database.connections.reports_db.database', $db_name);
                DB::purge('reports_db');

                try{
                    $tbl_check = DB::connection('reports_db')->table("pending_release")->first();
        
                    if(count($tbl_check) > 0){
                        $table_conn = "established_connection";
                    }else{
                        $table_conn = "no_table_found";
                    }
                }catch(\Illuminate\Database\QueryException $ex){
                    $table_conn = "no_table_found";
                }

                if($table_conn == 'established_connection'){
                    $report_res = DB::connection('reports_db')->table("released")
                        ->select(DB::raw('COUNT(released.farmer_id) as total_farmers'),
                                DB::raw('SUM(released.bags) as total_bags'),
                                DB::raw('SUM(farmer_profile.area) as dist_area'),
                                DB::raw('SUM(farmer_profile.actual_area) as actual_area'))
                        ->join('farmer_profile', function ($table_join) {
                            $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                            ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                        })
                        ->where('released.bags', '!=', '0')
                        ->first();

                    if(count($report_res) > 0){
                        $sex_res = DB::connection('reports_db')->table("released")
                            ->select('sex', DB::raw('COUNT(farmer_profile.sex) as sex_count'))
                            ->join('farmer_profile', function ($table_join) {
                                $table_join->on('farmer_profile.rsbsa_control_no', '=', 'released.rsbsa_control_no')
                                ->orOn('farmer_profile.farmerID', '=', 'released.farmer_id');
                            })
                            ->groupBy('sex')
                            ->orderBy('sex', 'DESC')
                            ->get();
        
                        foreach($sex_res as $s_row){
                            if($s_row->sex == 'Male'){
                                $male_count += $s_row->sex_count;
                            }elseif($s_row->sex == 'Femal'){
                                $female_count += $s_row->sex_count;
                            }
                        }
                    }
                    
                    $total_farmers += $report_res->total_farmers;
                    $total_bags += $report_res->total_bags;
                    $dist_area += $report_res->dist_area;
                    $actual_area += $report_res->actual_area;
                }
            }
        }

        $data_arr = array(
            "total_municipalities" => $municipalities,
            "total_provinces" => $provinces,
            "total_farmers" => number_format($total_farmers),
            "total_bags" => number_format($total_bags),
            "dist_area" => number_format($dist_area, '2'),
            "actual_area" => number_format($actual_area, '2'),
            "total_male" => $male_count,
            "total_female" => $female_count
        );
        
        return $data_arr;
    }
    /* REPORT HEADER */

    // input by timothy
    public function coop_summary_view(){
        return view('cooperative_summary.index');
    }

    public function coop_sg_count_total(){

        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
        }
        $data = [
            'sgTotal' => $sgCountTotal,
            'haTotal' => $haCountTotal,
            'coopTotal' => count($coop),
        ];

        return $data;
    }

    public function coop_sg_count(){

        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
            $data = [
                'name' => $value->coopName,
                'haCount' => $haCount,
                'sgCount' => $sgCount,
                'sgTotal' => $sgCountTotal,
                'haTotal' => $haCountTotal,
                'coopTotal' => count($coop),
            ];
            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
        ->make(true);
    }

    public function coop_sg_count_excel(){
        $excel = new excel;
        $_coop = new SeedCooperatives;
        $_sg = new SeedGrowers;
        $table_data = array();

        $coop = $_coop->seed_cooperatives();

        $haCountTotal = 0;
        $sgCountTotal = 0;
        foreach ($coop as  $value) {
            $haCount = $_coop->seed_cooperatives_area_planted($value->coopId);
            $sgCount = $_coop->seed_growers_count($value->coopId);
            $haCountTotal = $haCountTotal + $haCount;
            $sgCountTotal = $sgCountTotal + $sgCount;
            $data = [
                'Cooperative Name' => $value->coopName,
                'No. of Seed Growers' => $sgCount,
                'Commited Area ( ha )' => $haCount,
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Cooperative Name' => "TOTAL",
            'No. of Seed Growers' => $sgCountTotal,
            'Commited Area ( ha )' => $haCountTotal,
        ];
        array_push($table_data, $data2);

        return Excel::create('Cooperatives Seed Grower Counts', function($excel) use ($table_data) {
            $excel->sheet('Sheet1', function($sheet) use ($table_data) {
                $sheet->fromArray($table_data);
            });
        })->download('xlsx');
    }

    /** MODIFIED REPORTS */

    function load_regional_data(){        
        /*if(Auth::user()->roles->first()->name == "da-icts"){
            $regional_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->where('total_farmers', '!=', 0)
                ->orderBy('region', 'ASC')
                ->get();
        }else{
            $regional_data = DB::connection('rcep_reports_db')
                ->table('lib_regional_reports')
                ->where('total_farmers', '!=', 0)
                ->orderBy('region', 'ASC')
                ->get();
        }*/
        
         if(Auth::user()->roles->first()->name == "da-icts"){
            $regional_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports')
                ->select('lib_regional_reports.region', 'lib_regional_reports.total_provinces', 'lib_regional_reports.total_municipalities',
                        'lib_regional_reports.total_farmers', 'lib_regional_reports.total_bags', 'lib_regional_reports.total_dist_area',
                        'lib_regional_reports.total_actual_area', 'lib_regional_reports.total_male', 'lib_regional_reports.total_female',
                        'lib_regional_reports.report_id')
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                    $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports_mirror.lib_regional_reports.region');
                })
                ->where('lib_regional_reports.total_farmers', '!=', 0)
                ->orderBy('lib_prv.region_sort', 'ASC')
                ->groupBy('lib_prv.region')
                ->get();
        }else{
            $regional_data = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports')
                ->select('lib_regional_reports.region', 'lib_regional_reports.total_provinces', 'lib_regional_reports.total_municipalities',
                    'lib_regional_reports.total_farmers', 'lib_regional_reports.total_bags', 'lib_regional_reports.total_dist_area',
                    'lib_regional_reports.total_actual_area', 'lib_regional_reports.total_male', 'lib_regional_reports.total_female',
                    'lib_regional_reports.report_id', 'lib_regional_reports.yield', 'lib_regional_reports.total_claimed_area')
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                    $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports.lib_regional_reports.region');
                })
                ->where('lib_regional_reports.total_farmers', '!=', 0)
                ->orderBy('lib_prv.region_sort', 'ASC')
                ->groupBy('lib_prv.region')
                ->get();
       
             


            // $get_regions =  DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            //         ->select("tbl_actual_delivery.region", "lib_prv.regCode")
            //         ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
            //             $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery.region');
            //         })
            //         ->groupBy("tbl_actual_delivery.region")
            //         ->orderBy('lib_prv.region_sort', 'ASC')
            //         ->get();


            // foreach($get_regions as $region){
            //     $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            //     ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            //     ->where('is_transferred', '!=', 1)
            //     ->where('qrStart', '<=', 0)
            //     ->where("region",$region->region)
            //     ->value('total_bags');
            
            //     $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            //         ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            //         ->where("region",$region->region)
            //         ->where('is_transferred', 1)
            //         ->value('total_bags');

            //     $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            //         ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            //         ->where('is_transferred', "!=",1)
            //         ->where("region",$region->region)
            //         ->where('qrStart', '>', 0)
            //         ->value('total_bags');

            //     if(!isset($regional_data[$region->region]["name"])){
            //         $regional_data[$region->region]["name"] = $region->region;

            //         $regional_data[$region->region]["accepted"] = $accepted;
            //         $regional_data[$region->region]["transferred"] = $transferred;
            //         $regional_data[$region->region]["ebinhi"] = $ebinhi;
                    
            //         $data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report")
            //             ->select(DB::raw('SUM(total_farmer) as total_farmer'),DB::raw('SUM(total_claimed_area) as total_claimed_area'),DB::raw('SUM(total_bags) as total_bags') )
            //             ->where("prv_id","LIKE","ds2024_prv_".$region->regCode."%")
            //             ->first();
                    
            //         if($data ==null)
            //         {
            //             $data->total_farmer = 0;
            //             $data->total_claimed_area = 0;
            //             $data->total_bags = 0;     
            //         }

            //         $data_2 = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_group")
            //         ->select(DB::raw('SUM(total_male) as total_male'),DB::raw('SUM(total_female) as total_female'),DB::raw('SUM(actual_area) as actual_area') )
            //         ->where("prv_id","LIKE","ds2024_prv_".$region->regCode."%")
            //         ->first();

            //         if($data_2 ==null)
            //         {
            //             $data_2->total_male = 0;
            //             $data_2->total_female = 0;
            //             $data_2->actual_area = 0;     
            //         }
                    

            //         $regional_data[$region->region]["distributed"] = $data->total_bags;
            //         $regional_data[$region->region]["total_farmer"] = $data->total_farmer;
            //         $regional_data[$region->region]["total_male"] = $data_2->total_male;
            //         $regional_data[$region->region]["total_female"] = $data_2->total_female;
            //         $regional_data[$region->region]["actual_area"] = $data_2->actual_area;
            //         $regional_data[$region->region]["claimed_area"] = $data->total_claimed_area;

            //         $province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            //             ->select("province")
            //             ->where("regionName", $region->region)
            //             ->groupBy("province")
            //             ->get();

                    
            //         $yield_prod = 0;
            //         $yield_area = 0;

            //         foreach($province_list as $data_lib){
            //             $yield_data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")    
            //                     ->select(DB::raw("SUM(total_production) as total_production"),DB::raw("SUM(area) as area") )
            //                     ->where("province", $data_lib->province)
            //                     ->first();
            //             if($yield_data != null){
            //                 $yield_prod += $yield_data->total_production;
            //                 $yield_area += $yield_data->area;
            //             }
            //         }

            //         if($yield_area > 0){

            //             $yield = ($yield_prod/$yield_area)/1000;
            //             $yield = number_format($yield,2);
            //         }else{
            //             $yield = 0;
            //         }


            //         $regional_data[$region->region]["yield"] = $yield;
                   

            //     }



            // }


          

                   






                         


                        
                
            } 

        return $regional_data;
    }

    function load_provincial_data(){        
        $province_data = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->where('total_farmers', '!=', 0)
            ->orderBy('province', 'ASC')
            ->get();

        return $province_data;
    }

    function load_municipal_data(){        
        $municipal_data = DB::connection('rcep_reports_db')
            ->table('lib_municipal_reports')
            ->where('total_farmers', '!=', 0)
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();

        return $municipal_data;
    }

    function load_national_data(){        
        if(Auth::user()->roles->first()->name == "da-icts"){
            $national_data = DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_national_reports')
                ->get();
        }else{
            $national_data = array();
            $municipalities = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                ->groupby("municipality")
                ->count("municipality");
            
            $totals = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report")
            ->select(DB::raw("SUM(total_farmer) as total_farmer"),DB::raw("SUM(total_claimed_area) as total_claimed_area"),DB::raw("SUM(total_bags) as total_bags"))
            ->first();
            if($totals !=null){
                $total_farmer = $totals->total_farmer;
                $total_claimed_area = $totals->total_claimed_area;
                $total_bags = $totals->total_bags;
                
            }else{
                $total_farmer = 0;
                $total_claimed_area = 0;
                $total_bags = 0;
            }


            $totals_group = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_group")
            ->select(DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(actual_area) as actual_area"))
            ->first();
                if($totals_group !=null){
                    $total_male = $totals_group->total_male;
                    $total_female = $totals_group->total_female;
                    $actual_area = $totals_group->actual_area;
                }else{
                    $total_male = 0;
                    $total_female = 0;
                    $actual_area = 0;
                }
            
                $national_data["municipalities"] = $municipalities;
                $national_data["total_farmer"] = $total_farmer;
                $national_data["total_claimed_area"] = $total_claimed_area;
                $national_data["total_bags"] = $total_bags;
                $national_data["total_male"] = $total_male;
                $national_data["total_female"] = $total_female;
                $national_data["actual_area"] = $actual_area;

                $not_in_42 = array("ILOCOS NORTE", "COTABATO (NORTH COTABATO)", "ISABELA");

                $yield_data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                    ->select(DB::raw("(SUM(total_production)/SUM(area))/1000 as yield"))
                    ->whereNotIn("province", $not_in_42 )
                    ->first();

                $yield_data_all = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                    ->select(DB::raw("(SUM(total_production)/SUM(area))/1000 as yield"))
                    ->first();

                if($yield_data == null)
                {   $yield_data->yield = 0;}

                if($yield_data_all == null)
                {   $yield_data_all->yield = 0;}

                $national_data["yield"] = $yield_data->yield;
                $national_data["yield_all"] = $yield_data_all->yield;
                

            



        }

   
        return $national_data;
    }

    function municipal_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;

            $data = [
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Province' => '',
            'Municipality' => 'TOTAL:',
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function provincial_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;

            $data = [
                'Province' => $row->province,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Province' => 'TOTAL:',
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function regional_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        $total_provinces = 0;
        $total_municipalities = 0;

        foreach ($data as  $row) {
            $total_farmers_count += $row->total_farmers;
            $total_dist_area_count += $row->total_dist_area;
            $total_actual_area_count += $row->total_actual_area;
            $total_bags_count += $row->total_bags;
            $total_male_count += $row->total_male;
            $total_female_count += $row->total_female;
            $total_provinces += $row->total_provinces;
            $total_municipalities += $row->total_municipalities;

            $data = [
                'Region' => $row->region,
                'Covered Provinces' => $row->total_provinces == '' ? '0' : (string) $row->total_provinces,
                'Covered Municipaloities' => $row->total_municipalities == '' ? '0' : (string) $row->total_municipalities,
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) $row->total_farmers,
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) $row->total_dist_area,
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) $row->total_actual_area,
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) $row->total_bags,
                'Total Male' => $row->total_male == '' ? '0' : (string) $row->total_male,
                'Total Female' => $row->total_female == '' ? '0' : (string) $row->total_female
            ];
            array_push($table_data, $data);
        }
        $data2 = [
            'Region' => 'TOTAL:',
            'Covered Provinces' => (string) $total_provinces,
            'Covered Municipaloities' => (string) $total_municipalities,
            'Total beneficiaries' => (string) $total_farmers_count,
            'Distribution Area' => (string) $total_dist_area_count,
            'Actual Area' => (string) $total_actual_area_count,
            'Bags Distributed (20kg/bag)' => (string) $total_bags_count,
            'Total Male' => (string) $total_male_count,
            'Total Female' => (string) $total_female_count
        ];
        array_push($table_data, $data2);

        return $table_data;
    }

    function national_excel_content($data){
        $table_data = array();
        $total_farmers_count = 0;
        $total_dist_area_count = 0;
        $total_actual_area_count = 0;
        $total_bags_count = 0;
        $total_male_count = 0;
        $total_female_count = 0;

        foreach ($data as  $row) {
            $data = [
                'OVERALL SUMMARY: ' => '',
                'Covered Provinces' => (string) number_format($row->provinces),
                'Covered Municipalities' => (string) number_format($row->municipalities),
                'Total beneficiaries' => $row->total_farmers == '' ? '0' : (string) number_format($row->total_farmers),
                'Distribution Area' => $row->total_dist_area == '' ? '0' : (string) number_format($row->total_dist_area),
                'Actual Area' => $row->total_actual_area == '' ? '0' : (string) number_format($row->total_actual_area),
                'Bags Distributed (20kg/bag)' => $row->total_bags == '' ? '0' : (string) number_format($row->total_bags),
                'Total Male' => $row->total_male == '' ? '0' : (string) number_format($row->total_male),
                'Total Female' => $row->total_female == '' ? '0' : (string) number_format($row->total_female)
            ];
            array_push($table_data, $data);
        }

       $empty_row = [
            'OVERALL SUMMARY: ' => '',
            'Covered Provinces' => '',
            'Covered Municipalities' => '',
            'Total beneficiaries' => '',
            'Distribution Area' => '',
            'Actual Area' => '',
            'Bags Distributed (20kg/bag)' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($table_data, $empty_row);

        return $table_data;
    }

    public function Home_Report2(){
        $regional_data = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")
            ->where("region", "!=", "Programmer Region")
            ->get();

        foreach($regional_data as $key => $region){
        
            $ebinhi  = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->where("region", $region->region)
            ->where("qrValStart", "!=", "")
            ->sum("totalBagCount");

            if($ebinhi == null){
                $ebinhi = 0;
            }

            $regional_data[$key]->ebinhi = $ebinhi;

        }

     

        $regional_data = json_decode(json_encode($regional_data), true);

        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.home')
                ->with('regional_data', $regional_data);
        }else{
            // $regional_data = json_decode(json_encode($regional_data), true);
            return view('reports.modified.home')
                ->with('regional_data', $regional_data);        
        }
    }

    public function Home_Report2_provincial(){
       
            
             $region_actual = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery") 
            ->select("region")
            ->groupBy("region")
            ->get();



        $region_actual  = json_decode(json_encode($region_actual), true);
 
        $region_list = DB::connection("delivery_inspection_db")->table("lib_prv")
            ->select("prv", "province", "regionName as region")
            ->groupBy("regionName")
            ->orderBy("region_sort", "ASC")
            ->whereIn("regionName", $region_actual)
            ->where("regionName", 'not like', 'Programmer Region')
            ->get();






        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.province')
                ->with('region_list', $region_list);
        }else{
            return view('reports.modified.province')
                ->with('region_list', $region_list)
                ->with('user_level', Auth::user()->roles->first()->name);
        }
    }


   public function Home_Report2_municipal(){
       
        // $municipal_actual = DB::table($GLOBALS['season_prefix']."rcep_reports_view.rcef_nrp_provinces") 
        //     ->select("province")
        //     ->groupBy("province")
        //     ->orderBy("province")
        //     ->get();
        // $municipal_actual  = json_decode(json_encode($municipal_actual), true);
 
        $municipal_list = DB::connection("delivery_inspection_db")->table("lib_prv")
            ->select("prv", "province")
            ->groupBy("province")
            ->orderBy("region_sort", "ASC")
            ->orderBy("prv", "ASC")
            // ->whereIn("province", $municipal_actual)
            ->get();
    

        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.municipality')
                ->with('municipal_list', $municipal_list);
        }else{
            return view('reports.modified.municipality')
                ->with('municipal_list', $municipal_list)
                ->with('user_level', Auth::user()->roles->first()->name);
        }
    }

    public function Home_Report2_municipal_ws2021(){
        //$municipal_data = $this->load_municipal_data();
        $municipal_list = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_provincial_reports')
            ->select('lib_provincial_reports.*')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports.region');
            })
            ->where('lib_provincial_reports.total_farmers', '!=', 0)
            ->orderBy('lib_prv.region_sort', 'ASC')
            ->groupBy('lib_provincial_reports.province')
            ->get();
            

        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.municipality')
                ->with('municipal_list', $municipal_list);
        }else{
            return view('reports.modified.municipality')
                ->with('municipal_list', $municipal_list)
                ->with('user_level', Auth::user()->roles->first()->name);
        }
    }


    public function Home_Report2_national(){
        $national_data = $this->load_national_data();
        $paymaya_beneficiaries = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->groupBy('beneficiary_id')->get());
        $paymaya_bags = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->get());




        if(Auth::user()->roles->first()->name == "da-icts"){
            return view('reports.modified_public.national')
                ->with('national_data', $national_data);
        }else{
            return view('reports.modified.national')
                ->with('national_data', $national_data)
                ->with('paymaya_beneficiaries', $paymaya_beneficiaries);
        }
    }

    public function convert_to_excel(Request $request){

        $excel = new Excel;

        if($request->excel_type == "regional"){
            /*$data = $this->load_regional_data();
            $table_data = $this->regional_excel_content($data);
            $myFile = Excel::create('Regional Report', function($excel) use ($table_data) {
                $excel->sheet('REGIONS', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "REGIONAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";*/

        }else if($request->excel_type == "provincial"){
            $data = $this->load_provincial_data();
            $table_data = $this->provincial_excel_content($data);

            $myFile = Excel::create('Provincial Report', function($excel) use ($table_data) {
                $excel->sheet('PROVINCES', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "PROVINCIAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";

        }else if($request->excel_type == "municipal"){
            $data = $this->load_municipal_data();
            $table_data = $this->municipal_excel_content($data);

            $myFile = Excel::create('Municipal Report', function($excel) use ($table_data) {
                $excel->sheet('MUNICIPALITIES', function($sheet) use ($table_data) {
                    $sheet->fromArray($table_data);
                });
            });

            $file_name = "MUNICIPAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";

        }else if($request->excel_type == "national"){
            //national data
            $data = $this->load_national_data();
            $table_data = $this->national_excel_content($data);

            //regional data
            $regional_data = $this->load_regional_data();
            $regional_table_data = $this->regional_excel_content($regional_data);

            $myFile = Excel::create('National Report', function($excel) use ($table_data, $regional_table_data) {
                $excel->sheet('OVERALL & REGIONAL', function($sheet) use ($table_data, $regional_table_data) {
                    $sheet->fromArray($table_data);
                    $sheet->fromArray($regional_table_data);
                });
            });

            $file_name = "NATIONAL_REPORT"."_".date("Y-m-d H:i:s").".xlsx";
        }

        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }

    public function convert_to_excel_province_Old($province){
        $passed_str = explode("___", $province);
        $province = $passed_str[0];
        $limit_options = $passed_str[1];

        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->where('province', $province)->groupBy('province')->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                //array container
                $table_data = array();
                $province_sheet = array();
                $municipal_table = array();

                /** PROVINCE SUMMARY DATA */
                //get overall summary for province
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_provincial_reports')
                    ->where('province', $province)
                    ->first();

                $province_data = [
                    'Province Name' => $province,
                    'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($province_sheet, $province_data);

                $blank_row = [
                    'Province Name' => '',
                    'Covered Municipalities' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($province_sheet, $blank_row);
                /** PROVINCE SUMMARY DATA */

                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */
                $municipal_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->orderBy('municipality')
                    ->get();

                $mun_total_farmers = 0;
                $mun_total_distArea = 0;
                $mun_total_actArea = 0;
                $mun_total_male = 0;
                $mun_total_female = 0;
                $mun_total_bags = 0;

                $mun_cnt = 1;
                foreach($municipal_summary as $m_row){
                    $municipal_data = [
                        '#' => $mun_cnt,
                        'Municipality Name' => $m_row->municipality,
                        'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                        'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                        'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                        'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                        'Total Male' => (string) number_format($m_row->total_male),
                        'Total Female' => (string) number_format($m_row->total_female)
                    ];
                    array_push($municipal_table, $municipal_data);

                    ++$mun_cnt;
                    $mun_total_farmers += $m_row->total_farmers;
                    $mun_total_distArea += $m_row->total_dist_area;
                    $mun_total_actArea += $m_row->total_actual_area;
                    $mun_total_male += $m_row->total_male;
                    $mun_total_female += $m_row->total_female;
                    $mun_total_bags += $m_row->total_bags;
                }

                $total_municipal_data = [
                    '#' => '',
                    'Municipality Name' => 'TOTAL: ',
                    'Total Beneficiaries' => (string) number_format($mun_total_farmers),
                    'Total Distribution Area' => (string) number_format($mun_total_distArea),
                    'Total Actual Area' => (string) number_format($mun_total_actArea),
                    'Total Bags Distributed' => (string) number_format($mun_total_bags),
                    'Total Male' => (string) number_format($mun_total_male),
                    'Total Female' => (string) number_format($mun_total_female)
                ];
                array_push($municipal_table, $total_municipal_data);
                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */


                $limit_options = explode("_", $limit_options);
                $offset = $limit_options[0];
                
                if($offset == 1){
                    $offset = $limit_options[0];
                }else{
                    $offset = $limit_options[0] - 1;
                }

                $limit = $limit_options[1];

                $province_farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                        'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                        'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->orderBy('released.province', 'ASC')
                //->offset($offset)
                //->limit(3000)
                ->skip($offset)
                ->take(1000)
                ->get();

                $total_dist_area = 0;
                $total_actual_area = 0;
                $total_bags = 0;

                foreach ($province_farmer_list as  $row) {

                    //check other_info table
                    $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                    if(count($other_info_data) > 0){
                        $birthdate = $other_info_data->birthdate;
                        $mother_fname = $other_info_data->mother_fname;
                        $mother_mname = $other_info_data->mother_mname;
                        $mother_lname = $other_info_data->mother_lname;
                        $mother_suffix = $other_info_data->mother_suffix;

                        if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                            $phone_number = "";
                        }else{
                            $phone_number = $other_info_data->phone;
                        }
                    }else{
                        $birthdate = '';
                        $mother_fname = '';
                        $mother_mname = '';
                        $mother_lname = '';
                        $mother_suffix = '';
                        $phone_number = '';
                    }

                    //get farmer_profile
                    $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->where('farmerID', $row->farmer_id)
                        ->where('lastName', '!=', '')
                        ->where('firstName', '!=', '')
                        ->where('distributionID', 'like', 'R%')
                        ->orderBy('farmerID', 'DESC')
                        ->first();

                    if(count($farmer_profile) > 0){
                        $qr_code = $farmer_profile->distributionID;
                        $farmer_fname = $farmer_profile->firstName;
                        $farmer_mname = $farmer_profile->midName;
                        $farmer_lname = $farmer_profile->lastName;
                        $farmer_extname = $farmer_profile->extName;
                        $dist_area = $farmer_profile->area;
                        $actual_area = $farmer_profile->actual_area;
                        $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;

                        $total_dist_area += $farmer_profile->area;
                        $total_actual_area += $farmer_profile->actual_area;
                    }else{
                        $qr_code = "N/A";
                        $farmer_fname = "N/A";
                        $farmer_mname = "N/A";
                        $farmer_lname = "N/A";
                        $farmer_extname = "N/A";
                        $dist_area = 0;
                        $actual_area = 0;
                        $sex = "N/A";

                        $total_dist_area += 0;
                        $total_actual_area += 0;
                    }

                    //get name of encoder using released.by in sdms_db_dev
                    $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                    if(count($encoder_name) > 0){
                        if($encoder_name->middleName == ''){
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }else{
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }
                    }else{
                        $encoder_name = '';
                    }

                    //compute totals
                    $total_bags += $row->bags;

                    $data = [
                        'RSBSA #' => $row->rsbsa_control_no,
                        'QR Code' => $qr_code,
                        "Farmer's First Name" => $farmer_fname,
                        "Farmer's Middle Name" => $farmer_mname,
                        "Farmer's Last Name" => $farmer_lname,
                        "Farmer's Extension Name" => $farmer_extname,
                        'Sex' => $sex,
                        'Birth Date' => $birthdate,
                        'Telephone Number' => $phone_number,
                        'Province' => $row->province,
                        'Municipality' => $row->municipality,
                        "Mother's First Name" => $mother_fname,
                        "Mother's Middle Name" => $mother_mname,
                        "Mother's Last Name" => $mother_lname,
                        "Mother's Suffix" => $mother_suffix,
                        'Distribution Area' => $dist_area,
                        'Actual Area' => $actual_area,
                        'Bags' => $row->bags,
                        'Seed Variety' => $row->seed_variety,
                        'Date Released' => $row->date_released,
                        'Farmer ID' => $row->farmer_id,
                        'Released By' => $encoder_name
                    ];
                    array_push($table_data, $data);
                }

                $data2 = [
                    'RSBSA #' => '',
                    'QR Code' => '',
                    "Farmer's First Name" => '',
                    "Farmer's Middle Name" => '',
                    "Farmer's Last Name" => '',
                    "Farmer's Extension Name" => '',
                    'Sex' => '',
                    'Birth Date' => '',
                    'Telephone Number' => '',
                    'Province' => '',
                    'Municipality' => '',
                    "Mother's First Name" => '',
                    "Mother's Middle Name" => '',
                    "Mother's Last Name" => '',
                    "Mother's Suffix" => 'TOTAL: ',
                    'Distribution Area' => $total_dist_area,
                    'Actual Area' => $total_actual_area,
                    'Bags' => $total_bags,
                    'Seed Variety' => '',
                    'Date Released' => '',
                    'Farmer ID' => '',
                    'Released By' => ''
                ];
                array_push($table_data, $data2);

                return Excel::create($province."_".$offset."_".$limit."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $province_sheet, $municipal_table) {
                    $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                        $sheet->fromArray($province_sheet);
                        $sheet->fromArray($municipal_table);
                    });

                    $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                        $sheet->fromArray($table_data);
                    });
                })->download('xlsx');
            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.province');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.province');
        }
    }

    public function convert_to_excel_province2($province){
        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')->where('province', $province)->groupBy('province')->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                //array container
                $table_data = array();
                $province_sheet = array();
                $municipal_table = array();

                /** PROVINCE SUMMARY DATA */
                //get overall summary for province
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_provincial_reports')
                    ->where('province', $province)
                    ->first();

                $province_data = [
                    'Province Name' => $province,
                    'Covered Municipalities' => (string) number_format($province_summary->total_municipalities),
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($province_sheet, $province_data);

                $blank_row = [
                    'Province Name' => '',
                    'Covered Municipalities' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($province_sheet, $blank_row);
                /** PROVINCE SUMMARY DATA */

                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */
                $municipal_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->orderBy('municipality')
                    ->get();

                $mun_total_farmers = 0;
                $mun_total_distArea = 0;
                $mun_total_actArea = 0;
                $mun_total_male = 0;
                $mun_total_female = 0;
                $mun_total_bags = 0;

                $mun_cnt = 1;
                foreach($municipal_summary as $m_row){
                    $municipal_data = [
                        '#' => $mun_cnt,
                        'Municipality Name' => $m_row->municipality,
                        'Total Beneficiaries' => (string) number_format($m_row->total_farmers),
                        'Total Distribution Area' => (string) number_format($m_row->total_dist_area),
                        'Total Actual Area' => (string) number_format($m_row->total_actual_area),
                        'Total Bags Distributed' => (string) number_format($m_row->total_bags),
                        'Total Male' => (string) number_format($m_row->total_male),
                        'Total Female' => (string) number_format($m_row->total_female)
                    ];
                    array_push($municipal_table, $municipal_data);

                    ++$mun_cnt;
                    $mun_total_farmers += $m_row->total_farmers;
                    $mun_total_distArea += $m_row->total_dist_area;
                    $mun_total_actArea += $m_row->total_actual_area;
                    $mun_total_male += $m_row->total_male;
                    $mun_total_female += $m_row->total_female;
                    $mun_total_bags += $m_row->total_bags;
                }

                $total_municipal_data = [
                    '#' => '',
                    'Municipality Name' => 'TOTAL: ',
                    'Total Beneficiaries' => (string) number_format($mun_total_farmers),
                    'Total Distribution Area' => (string) number_format($mun_total_distArea),
                    'Total Actual Area' => (string) number_format($mun_total_actArea),
                    'Total Bags Distributed' => (string) number_format($mun_total_bags),
                    'Total Male' => (string) number_format($mun_total_male),
                    'Total Female' => (string) number_format($mun_total_female)
                ];
                array_push($municipal_table, $total_municipal_data);
                /** SELECTED (PROVINCE) MUNICIPAL SUMMARY DATA */

                $province_farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                        'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                        'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->orderBy('released.province', 'ASC')
                ->get();

                $total_dist_area = 0;
                $total_actual_area = 0;
                $total_bags = 0;

                foreach ($province_farmer_list as  $row) {

                    //check other_info table
                    $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                    if(count($other_info_data) > 0){
                        $birthdate = $other_info_data->birthdate;
                        $mother_fname = $other_info_data->mother_fname;
                        $mother_mname = $other_info_data->mother_mname;
                        $mother_lname = $other_info_data->mother_lname;
                        $mother_suffix = $other_info_data->mother_suffix;

                        if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                            $phone_number = "";
                        }else{
                            $phone_number = $other_info_data->phone;
                        }
                    }else{
                        $birthdate = '';
                        $mother_fname = '';
                        $mother_mname = '';
                        $mother_lname = '';
                        $mother_suffix = '';
                        $phone_number = '';
                    }

                    //get farmer_profile
                    $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->where('farmerID', $row->farmer_id)
                        ->where('lastName', '!=', '')
                        ->where('firstName', '!=', '')
                        ->where('distributionID', 'like', 'R%')
                        ->orderBy('farmerID', 'DESC')
                        ->first();

                    if(count($farmer_profile) > 0){
                        $qr_code = $farmer_profile->distributionID;
                        $farmer_fname = $farmer_profile->firstName;
                        $farmer_mname = $farmer_profile->midName;
                        $farmer_lname = $farmer_profile->lastName;
                        $farmer_extname = $farmer_profile->extName;
                        $dist_area = $farmer_profile->area;
                        $actual_area = $farmer_profile->actual_area;
                        $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;

                        $total_dist_area += $farmer_profile->area;
                        $total_actual_area += $farmer_profile->actual_area;
                    }else{
                        $qr_code = "N/A";
                        $farmer_fname = "N/A";
                        $farmer_mname = "N/A";
                        $farmer_lname = "N/A";
                        $farmer_extname = "N/A";
                        $dist_area = 0;
                        $actual_area = 0;
                        $sex = "N/A";

                        $total_dist_area += 0;
                        $total_actual_area += 0;
                    }

                    //get name of encoder using released.by in sdms_db_dev
                    $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                    if(count($encoder_name) > 0){
                        if($encoder_name->middleName == ''){
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }else{
                            $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                        }
                    }else{
                        $encoder_name = '';
                    }

                    //compute totals
                    $total_bags += $row->bags;

                    $data = [
                        'RSBSA #' => $row->rsbsa_control_no,
                        'QR Code' => $qr_code,
                        "Farmer's First Name" => $farmer_fname,
                        "Farmer's Middle Name" => $farmer_mname,
                        "Farmer's Last Name" => $farmer_lname,
                        "Farmer's Extension Name" => $farmer_extname,
                        'Sex' => $sex,
                        'Birth Date' => $birthdate,
                        'Telephone Number' => $phone_number,
                        'Province' => $row->province,
                        'Municipality' => $row->municipality,
                        "Mother's First Name" => $mother_fname,
                        "Mother's Middle Name" => $mother_mname,
                        "Mother's Last Name" => $mother_lname,
                        "Mother's Suffix" => $mother_suffix,
                        'Distribution Area' => $dist_area,
                        'Actual Area' => $actual_area,
                        'Bags' => $row->bags,
                        'Seed Variety' => $row->seed_variety,
                        'Date Released' => $row->date_released,
                        'Farmer ID' => $row->farmer_id,
                        'Released By' => $encoder_name
                    ];
                    array_push($table_data, $data);
                }

                $data2 = [
                    'RSBSA #' => '',
                    'QR Code' => '',
                    "Farmer's First Name" => '',
                    "Farmer's Middle Name" => '',
                    "Farmer's Last Name" => '',
                    "Farmer's Extension Name" => '',
                    'Sex' => '',
                    'Birth Date' => '',
                    'Telephone Number' => '',
                    'Province' => '',
                    'Municipality' => '',
                    "Mother's First Name" => '',
                    "Mother's Middle Name" => '',
                    "Mother's Last Name" => '',
                    "Mother's Suffix" => 'TOTAL: ',
                    'Distribution Area' => $total_dist_area,
                    'Actual Area' => $total_actual_area,
                    'Bags' => $total_bags,
                    'Seed Variety' => '',
                    'Date Released' => '',
                    'Farmer ID' => '',
                    'Released By' => ''
                ];
                array_push($table_data, $data2);

                return Excel::create($province."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $province_sheet, $municipal_table) {
                    $excel->sheet('PROVINCE SUMMARY', function($sheet) use ($province_sheet, $municipal_table) {
                        $sheet->fromArray($province_sheet);
                        $sheet->fromArray($municipal_table);
                    });

                    $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                        $sheet->fromArray($table_data);
                    });
                })->download('xlsx');
            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.province');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.province');
        }
    }

    public function convert_to_excel_municipality($province, $municipality){
        $lib_prv = DB::connection('delivery_inspection_db')->table('lib_prv')
            ->where('province', $province)
            ->where('municipality', $municipality)
            ->first();
        
        $database = $GLOBALS['season_prefix']."prv_".substr($lib_prv->prv,0,4);
        $table_conn = $this->set_database($database);
        
        if($table_conn == "Connection Established!"){
            //check if database has distribution data
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){

                $mun_data_arr = array();
                /** MUNICIPAL SUMMARY DATA */
                $province_summary = DB::connection('rcep_reports_db')
                    ->table('lib_municipal_reports')
                    ->where('province', $province)
                    ->where('municipality', $municipality)
                    ->first();

                $mun_data = [
                    'Province Name' => $province,
                    'Municipality Name' => $municipality,
                    'Total Beneficiaries' => (string) number_format($province_summary->total_farmers),
                    'Total Distribution Area' => (string) number_format($province_summary->total_dist_area),
                    'Total Actual Area' => (string) number_format($province_summary->total_actual_area),
                    'Total Bags Distributed' => (string) number_format($province_summary->total_bags),
                    'Total Male' => (string) number_format($province_summary->total_male),
                    'Total Female' => (string) number_format($province_summary->total_female)
                ];
                array_push($mun_data_arr, $mun_data);

                $blank_row = [
                    'Province Name' => '',
                    'Municipality Name' => '',
                    'Total Beneficiaries' => '',
                    'Total Distribution Area' => '',
                    'Total Actual Area' => '',
                    'Total Bags Distributed' => '',
                    'Total Male' => '',
                    'Total Female' => ''
                ];
                array_push($mun_data_arr, $blank_row);
                /** MUNICIPAL SUMMARY DATA */

                $farmer_list = DB::connection('reports_db')->table("released")
                ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by')
                ->where('released.bags', '!=', '0')
                ->where('released.province', '=', $province)
                ->where('released.municipality', '=', $municipality)
                ->get();

                //check if there is data returned after inserting parameters
                if(count($farmer_list) > 0){
                     //generate array to be passed to the excel file
                    $table_data = array();
                    $total_dist_area = 0;
                    $total_actual_area = 0;
                    $total_bags = 0;

                    foreach ($farmer_list as  $row) {
                        /*
                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                        ->where('farmer_id', $row->farmer_id)
                        ->where('rsbsa_control_no', $row->rsbsa_control_no)
                        ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->birthdate;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                        }

                        //compute totals
                        $total_dist_area += $row->area;
                        $total_actual_area += $row->actual_area;
                        $total_bags += $row->bags;

                        $data = [
                            'RSBSA #' => $row->rsbsa_control_no,
                            'QR Code' => $row->distributionID,
                            "Farmer's First Name" => $row->firstName,
                            "Farmer's Middle Name" => $row->midName,
                            "Farmer's Last Name" => $row->lastName,
                            "Farmer's Extension Name" => $row->extName,
                            'Sex' => $row->sex == 'Femal' ? 'Female' : $row->sex,
                            'Birth Date' => $birthdate,
                            'Province' => $row->province,
                            'Municipality' => $row->municipality,
                            "Mother's First Name" => $mother_fname,
                            "Mother's Middle Name" => $mother_mname,
                            "Mother's Last Name" => $mother_lname,
                            "Mother's Suffix" => $mother_suffix,
                            'Distribution Area' => $row->area,
                            'Actual Area' => $row->actual_area,
                            'Bags' => $row->bags,
                            'Seed Variety' => $row->seed_variety,
                            'Date Released' => $row->date_released,
                            'Farmer ID' => $row->farmer_id
                        ];
                        array_push($table_data, $data);
                        */

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;
                            
                            if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                            $phone_number = '';
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->where('farmerID', $row->farmer_id)
                            ->where('lastName', '!=', '')
                            ->where('distributionID', 'like', 'R%')
                            ->orderBy('farmerID', 'DESC')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $qr_code = $farmer_profile->distributionID;
                            $farmer_fname = $farmer_profile->firstName;
                            $farmer_mname = $farmer_profile->midName;
                            $farmer_lname = $farmer_profile->lastName;
                            $farmer_extname = $farmer_profile->extName;
                            $dist_area = $farmer_profile->area;
                            $actual_area = $farmer_profile->actual_area;
                            $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;
    
                            $total_dist_area += $farmer_profile->area;
                            $total_actual_area += $farmer_profile->actual_area;
                        }else{
                            $qr_code = "N/A";
                            $farmer_fname = "N/A";
                            $farmer_mname = "N/A";
                            $farmer_lname = "N/A";
                            $farmer_extname = "N/A";
                            $dist_area = 0;
                            $actual_area = 0;
                            $sex = "N/A";
    
                            $total_dist_area += 0;
                            $total_actual_area += 0;
                        }

                        //get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                        if(count($encoder_name) > 0){
                            if($encoder_name->middleName == ''){
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }else{
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }
                        }else{
                            $encoder_name = '';
                        }

                        //compute totals
                        $total_bags += $row->bags;

                        $data = [
                            'RSBSA #' => $row->rsbsa_control_no,
                            'QR Code' => $qr_code,
                            "Farmer's First Name" => $farmer_fname,
                            "Farmer's Middle Name" => $farmer_mname,
                            "Farmer's Last Name" => $farmer_lname,
                            "Farmer's Extension Name" => $farmer_extname,
                            'Sex' => $sex,
                            'Birth Date' => $birthdate,
                            'Telephone Number' => $phone_number,
                            'Province' => $row->province,
                            'Municipality' => $row->municipality,
                            "Mother's First Name" => $mother_fname,
                            "Mother's Middle Name" => $mother_mname,
                            "Mother's Last Name" => $mother_lname,
                            "Mother's Suffix" => $mother_suffix,
                            'Distribution Area' => $dist_area,
                            'Actual Area' => $actual_area,
                            'Bags' => $row->bags,
                            'Seed Variety' => $row->seed_variety,
                            'Date Released' => $row->date_released,
                            'Farmer ID' => $row->farmer_id,
                            'Released By' => $encoder_name
                        ];
                        array_push($table_data, $data);
                    }

                    $data2 = [
                        'RSBSA #' => '',
                        'QR Code' => '',
                        "Farmer's First Name" => '',
                        "Farmer's Middle Name" => '',
                        "Farmer's Last Name" => '',
                        "Farmer's Extension Name" => '',
                        'Sex' => '',
                        'Birth Date' => '',
                        'Telephone Number' => '',
                        'Province' => '',
                        'Municipality' => '',
                        "Mother's First Name" => '',
                        "Mother's Middle Name" => '',
                        "Mother's Last Name" => '',
                        "Mother's Suffix" => 'TOTAL: ',
                        'Distribution Area' => $total_dist_area,
                        'Actual Area' => $total_actual_area,
                        'Bags' => $total_bags,
                        'Seed Variety' => '',
                        'Date Released' => '',
                        'Farmer ID' => '',
                        'Released By' => ''
                    ];
                    array_push($table_data, $data2);

                    return Excel::create($municipality."_".date("Y-m-d H:i:s"), function($excel) use ($table_data, $mun_data_arr) {
                        $excel->sheet('MUNICIPALITY SUMMARY', function($sheet) use ($mun_data_arr) {
                            $sheet->fromArray($mun_data_arr);
                        });

                        $excel->sheet('BENEFICIARY LIST', function($sheet) use ($table_data) {
                            $sheet->fromArray($table_data);
                        });
                    })->download('xlsx');
                }else{
                    Session::flash('error_msg', "The selected municipality has no distribution data.");
                    return redirect()->route('rcep.report2.municipality');
                }

            }else{
                Session::flash('error_msg', "The database has no distribution data.");
                return redirect()->route('rcep.report2.municipality');
            }

        }else{
            //Session::flash('error_msg', $table_conn);
            Session::flash('error_msg', "The database does not exist.");
            return redirect()->route('rcep.report2.municipality');
        }
    }

    public function convert_to_excel_region($region){
        $excel_data = array();
        $region_summary = DB::connection('rcep_reports_db')
            ->table('lib_regional_reports')
            ->where('region', $region)
            ->first();

        $region_summary_data = [
            'Region Name' => $region_summary->region,
            'Covered Provinces' => (string) number_format($region_summary->total_provinces),
            'Covered Municipalities' => (string) number_format($region_summary->total_municipalities),
            'Total Farmers' => (string) number_format($region_summary->total_farmers),
            'Total Bags Distributed (20kg/bag)' => (string) number_format($region_summary->total_bags),
            'Total Distribution Area' => (string) number_format($region_summary->total_dist_area),
            'Total Actual Area' => (string) number_format($region_summary->total_actual_area),
            'Total Male' => (string) number_format($region_summary->total_male),
            'Total Female' => (string) number_format($region_summary->total_female)
        ];
        array_push($excel_data, $region_summary_data);

        $blanK_row = [
            'Region Name' => '',
            'Covered Provinces' => '',
            'Covered Municipalities' => '',
            'Total Farmers' => '',
            'Total Bags Distributed (20kg/bag)' => '',
            'Total Distribution Area' => '',
            'Total Actual Area' => '',
            'Total Male' => '',
            'Total Female' => ''
        ];
        array_push($excel_data, $blanK_row);

        $province_data = array();
        $selected_region_province_summary = DB::connection('rcep_reports_db')
            ->table('lib_provincial_reports')
            ->where('region', $region)
            ->get();

        //gloabl variables for provincial data
        $total_municipalities = 0;
        $total_farmers = 0;
        $total_bags = 0;
        $total_dist_area = 0;
        $total_actual_area = 0;
        $total_male = 0;
        $total_female = 0;

        foreach($selected_region_province_summary as $p_row){
            $p_data= [
                'Region' => $p_row->region,
                'Province' => $p_row->province,
                'Covered Municipalities' => (string) number_format($p_row->total_municipalities),
                'Total Farmers' => (string) number_format($p_row->total_farmers),
                'Total Bags Distributed (20kg/bag)' => (string) number_format($p_row->total_bags),
                'Total Distribution Area' => (string) number_format($p_row->total_dist_area),
                'Total Actual Area' => (string) number_format($p_row->total_actual_area),
                'Total Male' => (string) number_format($p_row->total_male),
                'Total Female' => (string) number_format($p_row->total_female)
            ];
            array_push($province_data, $p_data);

            $total_municipalities += $p_row->total_municipalities;
            $total_farmers += $p_row->total_farmers;
            $total_bags += $p_row->total_bags;
            $total_dist_area += $p_row->total_dist_area;
            $total_actual_area +=$p_row->total_actual_area;
            $total_male += $p_row->total_male;
            $total_female += $p_row->total_female;
        }

        $total_p_data= [
            'Region' => '',
            'Province' => 'TOTAL: ',
            'Covered Municipalities' =>(string) number_format($total_municipalities),
            'Total Farmers' => (string) number_format($total_farmers),
            'Total Bags Distributed (20kg/bag)' => (string) number_format($total_bags),
            'Total Distribution Area' => (string) number_format($total_dist_area),
            'Total Actual Area' => (string) number_format($total_actual_area),
            'Total Male' => (string) number_format($total_male),
            'Total Female' => (string) number_format($total_female)
        ];
        array_push($province_data, $total_p_data);

        return Excel::create($region_summary->region."_".date("Y-m-d H:i:s"), function($excel) use ($excel_data, $province_data) {
            $excel->sheet("REGION_SUMMARY", function($sheet) use ($excel_data, $province_data) {
                $sheet->fromArray($excel_data);
                $sheet->fromArray($province_data);
            });
        })->download('xlsx');
    }

    /* UPDATED TO LIVE DATA RJ 09202022 */
    //FIND ME
    public function generateProvincialReportData(Request $request){

        if(Auth::user()->roles->first()->name == "da-icts"){
            return Datatables::of(
                DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->where('region', '=', $request->region)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('province', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);

        }else{

            $province_not_included = array();
            $provinces = DB::connection('delivery_inspection_db')->table('lib_prv')
                ->where('regionName', '=', $request->region)
                ->orderBy('prv', 'ASC')
                ->groupBy("province")
                ->get();
                $i=0;
                foreach($provinces as $prv){
                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $prv->province)
                    ->value('total_bags');
                
                    if($accepted <= 0){
                        $province_not_included[$i] = $prv->province;
                        $i++;
                    }

                }






            return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')
                ->where('region', '=', $request->region)
                ->whereNotIn("province", $province_not_included)
                ->orderBy('province', 'ASC')
                ->groupBy("province")
            )
                ->addColumn('action', function($row){
                    return "<a class='btn btn-success btn-xs' data-province='$row->province' data-toggle='modal' data-target='#confirm_export_pmo'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                })
                ->addColumn('total_beneficiaries', function($row){
               
                    $reg_beneficiaries = $row->total_farmers;

                    if($reg_beneficiaries != null){
                        $reg_beneficiaries = $reg_beneficiaries;
                    }else{
                        $reg_beneficiaries = 0;
                    }


                $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                $ebinhi_beneficiary = $row->total_farmers_ebinhi;
                if($ebinhi_beneficiary>0){
                    $return_distributed.= "<br> e-Binhi: ".number_format($ebinhi_beneficiary);
                    $total_beneficiaries = $ebinhi_beneficiary + $reg_beneficiaries;
                    $return_distributed.= "<br> <b> Total: ".number_format($total_beneficiaries)."</b>";

                }
                return $return_distributed;        
                })
                ->addColumn('total_registered_area', function($row){
            
                
                    $total_data = $row->total_actual_area;

                    if($total_data != null){
                        $total_data = $total_data;
                    }else{
                        $total_data = 0;
                    }



                $return_data = "Regular: ". number_format($total_data, '2', '.', ',');       

                
                     $ebinhi_count=$row->total_inspected_bags_ebinhi;
                 
                    $return_data.= "<br>e-Binhi: ".number_format($ebinhi_count,2);
                    $total_distibuted = $ebinhi_count + $total_data;
                    $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";
                

                return $return_data;
                })
               /* ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })*/
                 ->addColumn('total_bags_distributed', function($row){
        

                            $distributed = $row->total_bags;
        
                            if($distributed != null){
                                $distributed = $distributed;
                            }else{
                                $distributed = 0;
                            }
        



                 $return_distributed = "Regular distribution: ".number_format($distributed);
                 $eBinhi_claim = $row->total_bags_ebinhi;
                if($eBinhi_claim>0){
                    $return_distributed.= "<br> Binhi e-padala: ".number_format($eBinhi_claim);
                    $total_distibuted = $eBinhi_claim+ $distributed;
                    $return_distributed.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
                }


                return $return_distributed;     
                })
                ->addColumn('accepted_transferred', function($row){
                   
                    $accepted = $row->total_inspected_bags;
                    $transferred = $row->total_transferred_bags;
                     $ebinhi = $row->total_inspected_bags_ebinhi;

                    $totalBags = 0;

                    $accepted_transferred = "Accepted: ".number_format($accepted);
                    if(intval($transferred)>0){
                        $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                        $totalBags += intval($transferred);
                    }

                     if(intval($ebinhi)>0){
                        $accepted_transferred .= " <br> e-Binhi: ".number_format($ebinhi);
   
                         $totalBags += intval($ebinhi);
                    }

                        if($totalBags>0){
                            $totalBags += intval($accepted);
                            $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                        }    

                return $accepted_transferred;                  
                })
                ->addColumn('total_male_count', function($row){
   
           
                    
                            $malecount = $row->total_male;
        
                            if($malecount != null){
                                $malecount = $malecount;
                            }else{
                                $malecount = 0;
                            }
        



                
                $return_male = "Regular: ".number_format($malecount);

                 $ebinhi_male = $row->total_male_ebinhi;
                if($ebinhi_male>0){
                    $return_male.= "<br>e-Binhi: ".number_format($ebinhi_male);
                    $total_distibuted = $ebinhi_male + $malecount;
                    $return_male.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";

                }


                return $return_male;    
                })
                ->addColumn('total_female_count', function($row){
            
                            $female_count = $row->total_female;
        
                            if($female_count != null){
                                $female_count = $female_count;
                            }else{
                                $female_count = 0;
                            }
        

                 $return_female = "Regular: ".number_format($female_count);

                 $ebinhi_female = $row->total_female_ebinhi;
                 
                if($ebinhi_female>0){
                    $return_female.= "<br>e-Binhi: ".number_format($ebinhi_female);
                    $total_distibuted = $ebinhi_female + $female_count;
                    $return_female.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";

                }
                return $return_female;       
                })
                ->addColumn('total_yield', function($row){
                    $yield = $row->yield;
                    if($yield > 0){
                        return number_format($yield, 2);

                    }else{
                        return "0";
                    }
                })
                 ->addColumn('total_area_claimed', function($row){
                $total_count = $row->total_claimed_area;
                
                if($total_count != null){
                    $total_count = $total_count;
                }else{
                    $total_count = 0;
                }


                  $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 


                $ebinhi_data= $row->total_claim_area_ebinhi;

                if($ebinhi_data > 0){

                    $return_data.= "<br>e-Binhi: ".number_format($ebinhi_data,2);
                    $total_distibuted = $ebinhi_data + $total_count;
                    $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";

                }
                return $return_data;

            })
                ->make(true);
        }
    
    }


    /**NEW FUNCTIONS FOR SEED BENEFICIARY REPORTS - jpalileo */
    public function generateProvincialReportData_ws2021_md(Request $request){
        if(Auth::user()->roles->first()->name == "da-icts"){
            return Datatables::of(
                DB::table($GLOBALS['season_prefix'].'rcep_reports_mirror.lib_provincial_reports')
                    ->where('region', '=', $request->region)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('province', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);

        }else{
            return Datatables::of(
                DB::connection('rcep_reports_db')->table('lib_provincial_reports')
                    ->where('region', '=', $request->region)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('province', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.province', $row->province);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";            
                    /*if($row->total_farmers > 20000){
                        return "<a class='btn btn-success btn-sm' data-toggle='modal' data-target='#export_option_modal' data-province='".$row->province."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    }else{
                        $url = route('rcef.report.excel.province_2', $row->province);
                        return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";                        
                    }*/

                    return "<a class='btn btn-success btn-xs' data-province='$row->province' data-toggle='modal' data-target='#confirm_export_pmo'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                })
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                 ->addColumn('total_bags_distributed', function($row){
                     $distributed = "Regular distribution: ".number_format($row->total_bags);
                    $province = $row->province;
                    //$municipality = $row->municipality;
                    $eBinhi_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $province)->count("claimId");
                    if($eBinhi_claim>0){
                        $distributed.= "<br> Binhi e-padala: ".number_format($eBinhi_claim);
                    }

                    return $distributed;      
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);   

                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    //->where('municipality', $row->municipality)
                    ->where('is_transferred', '!=', 1)
                     ->where('qrStart', '<=', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
                
                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    //->where('municipality', $row->municipality)
                    ->where('is_transferred', 1)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');

                     $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    ->where('is_transferred', "!=",1)
                    ->where('qrStart', '>', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');

                    $totalBags = 0;

                    $accepted_transferred = "Accepted: ".number_format($accepted);
                    if(intval($transferred)>0){
                        $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                        $totalBags += intval($transferred);
                    }

                     if(intval($ebinhi)>0){
                        $accepted_transferred .= " <br> e-Binhi: ".number_format($ebinhi);
                        /*
                        $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                        data-region = '".$row->region."'
                        data-province = '".$row->province."'
                        data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                        */
                         $totalBags += intval($ebinhi);
                    }

                        if($totalBags>0){
                            $totalBags += intval($accepted);
                            $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                        }    

                return $accepted_transferred;                  
                })
                ->addColumn('total_male_count', function($row){
                    /*$malecount = "<div> <strong> Male: ".number_format($row->total_male)."</strong> </div>";
                    $per_province= $this->area_range_counter("province", $row->region, $row->province, "%", "Male"); 
                    if(count($per_province)>0){
                        $malecount.= "<table border=0 width='100%'><tr> <td>  <b> (<= 0.5) </b>: ".$per_province[0]["area_p5"]."</td> 
                            <td align='left'> <b> (>0.5&<= 1) </b>: ".$per_province[0]["area_p5_1"]." </td> </tr> ";
                        $malecount.= "<tr> <td>  <b>  (>1&<= 1.5) </b>: ".$per_province[0]["area_1_1p5"]."</td> 
                        <td align='left'> <b> (>1.5&<= 2) </b>: ".$per_province[0]["area_1p5_2"]." </td> </tr> ";
                        $malecount.= "<tr> <td>  <b>  (>2&<= 2.5) </b>: ".$per_province[0]["area_2_2p5"]."</td> 
                        <td align='left'> <b> (>2.5&<= 3) </b>: ".$per_province[0]["area_2p5_3"]." </td> </tr> ";
                        $malecount.= "<tr> <td colspan=2 align='left'>  <b>  ( > 3) </b>: ".$per_province[0]["area_3"]."</td> 
                        </table>";  
                    }*/
                    $malecount = number_format($row->total_male);
                    return $malecount;
                    //return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    /*$femalecount = "<div> <strong> Female: ".number_format($row->total_female)."</strong> </div>";
                    $per_province= $this->area_range_counter("province", $row->region, $row->province, "%", "Female");           
                        if(count($per_province)>0){
                            $femalecount.= "<table border=0 width='100%'><tr> <td>  <b> (<= 0.5) </b>: ".$per_province[0]["area_p5"]."</td> 
                            <td align='left'> <b> (>0.5&<= 1) </b>: ".$per_province[0]["area_p5_1"]." </td> </tr> ";
                            $femalecount.= "<tr> <td>  <b>  (>1&<= 1.5) </b>: ".$per_province[0]["area_1_1p5"]."</td> 
                            <td align='left'> <b> (>1.5&<= 2) </b>: ".$per_province[0]["area_1p5_2"]." </td> </tr> ";
                            $femalecount.= "<tr> <td>  <b>  (>2&<= 2.5) </b>: ".$per_province[0]["area_2_2p5"]."</td> 
                            <td align='left'> <b> (>2.5&<= 3) </b>: ".$per_province[0]["area_2p5_3"]." </td> </tr> ";
                            $femalecount.= "<tr> <td colspan=2 align='left'>  <b>  ( > 3) </b>: ".$per_province[0]["area_3"]."</td> 
                            </table>";     
                        }*/
                        
                    $femalecount = number_format($row->total_female);
                    return $femalecount;
                    //return number_format($row->total_female);       
                })
                ->addColumn('total_yield', function($row){
                    return number_format($row->yield, '2', '.', ',');       
                })
                 ->addColumn('total_area_claimed', function($row){
                $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->select("prv")
                        ->where('province', $row->province)
                        ->first();
                    $area_claimed = 0;
                if(count($prv)>0){
                    $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $area_claimed = DB::table($prv_db.".released")
                                ->where("province", $row->province)
                                ->sum("claimed_area");          
                }
                return number_format($area_claimed, '2', '.', ','); 

            })
                ->make(true);
        }
    }


    public function generateMunicipalReportData_b4midnight(){
        

        if($request->ebinhi == "true"){
     
            if(Auth::user()->roles->first()->name == "da-icts"){
                return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);
            
            }else{
    
                $municipality_not_included = array();
                $municipality = DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->orderBy('municipality', 'ASC')
                    ->groupBy("municipality")
                    ->get();
                    $i=0;
                    foreach($municipality as $muni){
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where("province", $muni->province)
                        ->where("municipality", $muni->municipality)
                        ->value('total_bags');
                    
                        if($accepted <= 0){
                            $municipality_not_included[$i] = $muni->municipality;
                            $i++;
                        }
    
                    }
    
    
                return Datatables::of(DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->whereNotIn("municipality", $municipality_not_included)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                  
                    $isEbinhi = 0;
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)->first();
                    if(count($ebinhi_beneficiary)>0){
                        $isEbinhi = 1;
                    }

                    return  "<a class='btn btn-success btn-xs' data-ebinhi='$isEbinhi' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                    
                    
                
                })
                ->addColumn('total_beneficiaries', function($row){
                    
                

                    $reg_beneficiaries = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                              ->where("municipality", $row->prv)
                                ->first();

                    if($reg_beneficiaries != null){
                        $reg_beneficiaries = $reg_beneficiaries->total_farmer;
                    }else{
                        $reg_beneficiaries = 0;
                    }

                    $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                 
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_beneficiaries")->where("province", $row->province)->where("municipality", $row->municipality)->value("total_beneficiaries");
    
                    if($ebinhi_beneficiary>0){
                        $return_distributed.= "<br> e-Binhi: ".number_format($ebinhi_beneficiary);
                        $total_beneficiaries = $ebinhi_beneficiary + $reg_beneficiaries;
                        $return_distributed.= "<br> <b> Total: ".number_format($total_beneficiaries)."</b>";
    
                    }
                    return $return_distributed;  
    
                })
                ->addColumn('total_registered_area', function($row){
                   
    
                    $total_data =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($total_data != null){
                        $total_data = $total_data->actual_area;
                    }else{
                        $total_data = 0;
                    }


                    $return_data = "Regular:". number_format($total_data, '2', '.', ',');        
    
    
                    //   $ebinhi_data= DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                    //  ->groupBy("paymaya_code")
                    //  ->get();
    
                    // if(count($ebinhi_data) > 0){
    
                    //      $ebinhi_count=0;
                    //  foreach ($ebinhi_data as $key => $value) {
                    //     $getArea = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    //         ->where("paymaya_code", $value->paymaya_code)
                    //         ->first();
                    //     $ebinhi_count += $getArea->area;
                    //      }
                    //     $return_data.= "<br>e-Binhi: ".number_format($ebinhi_count,2);
                    //     $total_distibuted = $ebinhi_count + $total_data;
                    //     $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";
    
                    // }
    
                    return $return_data;
                })
                /*->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
    
                     $total_data = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".released")
                                ->where("province", $row->province)
                                ->where("municipality", $row->municipality)
                                ->sum("actual_area");
                     return number_format($total_data, '2', '.', ',');  
    
    
                }) */
                ->addColumn('total_bags_distributed', function($row){
                 

                    $distributed =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($distributed != null){
                        $distributed = $distributed->total_bags;
                    }else{
                        $distributed = 0;
                    }



                        $return_distributed = "Regular distribution: ".number_format($distributed);
                       
                     $eBinhi_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_bags")->where("province", $row->province)->where("municipality", $row->municipality)->value("total_bags");
                    if($eBinhi_claim>0){
                        $return_distributed.= "<br> e-Binhi: ".number_format($eBinhi_claim);
                        $total_distibuted = $eBinhi_claim + $distributed;
                        $return_distributed.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
    
    
                    return $return_distributed;
    
    
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);  
                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    
                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', 1)
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
    
                    $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', "!=",1)
                        ->where('qrStart', '>', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');  
    
                        $totalBags = 0;
    
                        $accepted_transferred = "Accepted:   ".number_format($accepted);
                        if(intval($transferred)>0){
                            $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                            /*
                            $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                            data-region = '".$row->region."'
                            data-province = '".$row->province."'
                            data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                            */
                            $totalBags += intval($transferred);
                        }
    
                         if(intval($ebinhi)>0){
                            $accepted_transferred .= " <br> e-Binhi: ".number_format($ebinhi);
                            /*
                            $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                            data-region = '".$row->region."'
                            data-province = '".$row->province."'
                            data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                            */
                             $totalBags += intval($ebinhi);
                        }
    
                            if($totalBags>0){
                                $totalBags += intval($accepted);
                                $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                            }                        
    
                    return $accepted_transferred;
                   
                })
                ->addColumn('total_male_count', function($row){
            
                    $malecount =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($malecount != null){
                        $malecount = $malecount->total_male;
                    }else{
                        $malecount = 0;
                    }
                    


                    $return_male = "Regular: ".number_format($malecount);
    
                     $ebinhi_male = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                     ->groupBy("paymaya_code")
                     ->whereRaw("paymaya_code in (Select paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex ='male' OR sex='M' )")
                     ->get();
                    if(count($ebinhi_male)>0){
                        $return_male.= "<br>e-Binhi: ".number_format(count($ebinhi_male));
                        $total_distibuted = count($ebinhi_male) + $malecount;
                        $return_male.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
    
    
                    return $return_male;  
                })
                ->addColumn('total_female_count', function($row){
                    $femalecount =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($femalecount != null){
                        $femalecount = $femalecount->total_female;
                    }else{
                        $femalecount = 0;
                    }

                    $return_female = "Regular: ".number_format($femalecount);
    
                     $ebinhi_female = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                     ->groupBy("paymaya_code")
                     ->whereRaw("paymaya_code in (Select paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex ='female' OR sex='F' )")
                     ->get();
                    if(count($ebinhi_female)>0){
                        $return_female.= "<br>e-Binhi: ".number_format(count($ebinhi_female));
                        $total_distibuted = count($ebinhi_female) + $femalecount;
                        $return_female.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
                    return $return_female;
                })
                ->addColumn('total_yield', function($row){
                  
                    $yield = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                        ->where("municipality", $row->municipality)
                        ->first();

                        if($yield != null){
                            return number_format($yield->municipality_yield, 2);
                        }else{
                            return 0;
                        }

    
                })
                ->addColumn('total_area_claimed', function($row){
           
                    
                    $total_count =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($total_count != null){
                        $total_count = $total_count->total_claimed_area;
                    }else{
                        $total_count = 0;
                    }





                    $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 
    
    
                    $ebinhi_data= DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                     ->groupBy("paymaya_code")
                     ->get();
    
                    if(count($ebinhi_data) > 0){
    
                         $ebinhi_count=0;
                     foreach ($ebinhi_data as $key => $value) {
                        $getArea = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                            ->where("paymaya_code", $value->paymaya_code)
                            ->first();
                            if($getArea != null){
                                $ebinhi_count += $getArea->area;
                            }


                        
                         }
                        $return_data.= "<br>e-Binhi: ".number_format($ebinhi_count,2);
                        $total_distibuted = $ebinhi_count + $total_count;
                        $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";
    
                    }
                    return $return_data;
    
                })
                ->make(true);
            }
        } //EBINHI TRUE
        else{
            if(Auth::user()->roles->first()->name == "da-icts"){
                return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);
            
            }else{
    
                $municipality_not_included = array();
                $municipality = DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->orderBy('municipality', 'ASC')
                    ->groupBy("municipality")
                    ->get();
                    $i=0;
                    foreach($municipality as $muni){
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where("province", $muni->province)
                        ->where("municipality", $muni->municipality)
                        ->value('total_bags');
                    
                        if($accepted <= 0){
                            $municipality_not_included[$i] = $muni->municipality;
                            $i++;
                        }
    
                    }
    
    
                return Datatables::of(DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->whereNotIn("municipality", $municipality_not_included)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    $isEbinhi = 0;
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)->first();
                    if(count($ebinhi_beneficiary)>0){
                        $isEbinhi = 1;
                    }

                    return  "<a class='btn btn-success btn-xs' data-ebinhi='$isEbinhi' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                })
                ->addColumn('total_beneficiaries', function($row){
                    
                    $reg_beneficiaries = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                      ->first();

                    if($reg_beneficiaries != null){
                        $reg_beneficiaries = $reg_beneficiaries->total_farmer;
                    }else{
                        $reg_beneficiaries = 0;
                    }



                    $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                    return $return_distributed;  
    
                })
                ->addColumn('total_registered_area', function($row){
                   
    
                    $total_data =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($total_data != null){
                        $total_data = $total_data->actual_area;
                    }else{
                        $total_data = 0;
                    }
                     $return_data = "Regular:". number_format($total_data, '2', '.', ',');       
    
                    return $return_data;
                })
      
                ->addColumn('total_bags_distributed', function($row){
                    $distributed =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($distributed != null){
                        $distributed = $distributed->total_bags;
                    }else{
                        $distributed = 0;
                    }

                     $return_distributed = "Regular distribution: ".number_format($distributed);
                    return $return_distributed;
    
    
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);  
                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    
                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', 1)
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
    
             
    
                        $totalBags = 0;
    
                        $accepted_transferred = "Accepted:   ".number_format($accepted);
                        if(intval($transferred)>0){
                            $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                            $totalBags += intval($transferred);
                        }
    
                  
    
                            if($totalBags>0){
                                $totalBags += intval($accepted);
                                $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                            }                        
    
                    return $accepted_transferred;
                   
                })
                ->addColumn('total_male_count', function($row){
                    $malecount =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($malecount != null){
                        $malecount = $malecount->total_male;
                    }else{
                        $malecount = 0;
                    }

      
                    $return_male = "Regular: ".number_format($malecount);
                    return $return_male;  
                })
                ->addColumn('total_female_count', function($row){
                    $femalecount =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($femalecount != null){
                        $femalecount = $femalecount->total_female;
                    }else{
                        $femalecount = 0;
                    }
      
                    $return_female = "Regular: ".number_format($femalecount);
                    return $return_female;
                })
                ->addColumn('total_yield', function($row){
                    $yield = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                        ->where("municipality", $row->municipality)
                        ->first();

                        if($yield != null){
                            return number_format($yield->municipality_yield, 2);
                        }else{
                            return 0;
                        }
    
                })
                ->addColumn('total_area_claimed', function($row){
                    $total_count =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($total_count != null){
                        $total_count = $total_count->total_claimed_area;
                    }else{
                        $total_count = 0;
                    }

                    
                    $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 
                    return $return_data;
    
                })
                ->make(true);
            }
        } //EBINHI FALSE



       
    //END
    


    
    }

    public function generateMunicipalReportData_old(Request $request){

        if($request->ebinhi == "true"){
     
            if(Auth::user()->roles->first()->name == "da-icts"){
                return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);
            
            }else{
    
                $municipality_not_included = array();
                $municipality = DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->orderBy('municipality', 'ASC')
                    ->groupBy("municipality")
                    ->get();
                    $i=0;
                    foreach($municipality as $muni){
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where("province", $muni->province)
                        ->where("municipality", $muni->municipality)
                        ->value('total_bags');
                    
                        if($accepted <= 0){
                            $municipality_not_included[$i] = $muni->municipality;
                            $i++;
                        }
    
                    }
                    
    
                return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->whereNotIn("municipality", $municipality_not_included)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                  
                    $isEbinhi = 0;
                    $ebinhi_beneficiary = $row->total_farmers_ebinhi;
                    if($ebinhi_beneficiary>0){
                        $isEbinhi = 1;
                    }

                    return  "<a class='btn btn-success btn-xs' data-ebinhi='$isEbinhi' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                    
                    
                
                })
                ->addColumn('total_beneficiaries', function($row){
                    $reg_beneficiaries = $row->total_farmers;

                    if($reg_beneficiaries != null){
                        $reg_beneficiaries = $reg_beneficiaries;
                    }else{
                        $reg_beneficiaries = 0;
                    }

                    $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                 
                    $ebinhi_beneficiary = $row->total_farmers_ebinhi;
    
                    if($ebinhi_beneficiary>0){
                        $return_distributed.= "<br> e-Binhi: ".number_format($ebinhi_beneficiary);
                        $total_beneficiaries = $ebinhi_beneficiary + $reg_beneficiaries;
                        $return_distributed.= "<br> <b> Total: ".number_format($total_beneficiaries)."</b>";
    
                    }
                    return $return_distributed;  
    
                })
                ->addColumn('total_registered_area', function($row){
                   
    
                    $total_data =$row->total_actual_area;

                    if($total_data != null){
                        $total_data = $total_data;
                    }else{
                        $total_data = 0;
                    }


                    $return_data = "Regular:". number_format($total_data, '2', '.', ',');        
    
    
            
                    return $return_data;
                })
            
                ->addColumn('total_bags_distributed', function($row){
                 

                    $distributed =$row->total_bags;

                    if($distributed != null){
                        $distributed = $distributed;
                    }else{
                        $distributed = 0;
                    }



                        $return_distributed = "Regular distribution: ".number_format($distributed);
                       
                     $eBinhi_claim = $row->total_bags_ebinhi;
                    if($eBinhi_claim>0){
                        $return_distributed.= "<br> e-Binhi: ".number_format($eBinhi_claim);
                        $total_distibuted = $eBinhi_claim + $distributed;
                        $return_distributed.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
    
    
                    return $return_distributed;
    
    
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);  
                    $accepted = $row->total_actual;
                    $transferred = $row->total_transfer;
                    $ebinhi = $row->total_inspected_bags_ebinhi;
    
                    $totalBags = 0;
    
                        $accepted_transferred = "Accepted:   ".number_format($accepted);
                        if(intval($transferred)>0){
                            $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                            /*
                            $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                            data-region = '".$row->region."'
                            data-province = '".$row->province."'
                            data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                            */
                            $totalBags += intval($transferred);
                        }
    
                         if(intval($ebinhi)>0){
                            $accepted_transferred .= " <br> e-Binhi: ".number_format($ebinhi);
                            /*
                            $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                            data-region = '".$row->region."'
                            data-province = '".$row->province."'
                            data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                            */
                             $totalBags += intval($ebinhi);
                        }
    
                            if($totalBags>0){
                                $totalBags += intval($accepted);
                                $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                            }                        
    
                    return $accepted_transferred;
                   
                })
                ->addColumn('total_male_count', function($row){
            
                    $malecount =$row->total_male;

                    if($malecount != null){
                        $malecount = $malecount;
                    }else{
                        $malecount = 0;
                    }
                    


                    $return_male = "Regular: ".number_format($malecount);
    
                     $ebinhi_male = $row->total_male_ebinhi;
                    if($ebinhi_male>0){
                        $return_male.= "<br>e-Binhi: ".number_format($ebinhi_male);
                        $total_distibuted = $ebinhi_male + $malecount;
                        $return_male.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
    
    
                    return $return_male;  
                })
                ->addColumn('total_female_count', function($row){
                    $femalecount =$row->total_female;

                    if($femalecount != null){
                        $femalecount = $femalecount;
                    }else{
                        $femalecount = 0;
                    }

                    $return_female = "Regular: ".number_format($femalecount);
    
                     $ebinhi_female = $row->total_female_ebinhi;
                    if($ebinhi_female>0){
                        $return_female.= "<br>e-Binhi: ".number_format($ebinhi_female);
                        $total_distibuted = $ebinhi_female + $femalecount;
                        $return_female.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
                    return $return_female;
                })
                ->addColumn('total_yield', function($row){
                  
                    $yield = $row->yield;

                        if($yield > 0){
                            return number_format($yield, 2);
                        }else{
                            return 0;
                        }

    
                })
                ->addColumn('total_area_claimed', function($row){
           
                    
                    $total_count =$row->total_claimed_area;

                    if($total_count > 0){
                        $total_count = $total_count;
                    }else{
                        $total_count = 0;
                    }

                    $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 
    
                         $ebinhi_count=$row->total_claim_area_ebinhi;
                        $return_data.= "<br> e-Binhi: ".number_format($ebinhi_count,2);
                        $total_distibuted = $ebinhi_count + $total_count;
                        $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";
    
                    
                    return $return_data;
    
                })
                ->make(true);
            }
        } //EBINHI TRUE
        else{
            if(Auth::user()->roles->first()->name == "da-icts"){
                return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);
            
            }else{
    
                $municipality_not_included = array();
                $municipality = DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->orderBy('municipality', 'ASC')
                    ->groupBy("municipality")
                    ->get();
                    $i=0;
                    foreach($municipality as $muni){
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where("province", $muni->province)
                        ->where("municipality", $muni->municipality)
                        ->value('total_bags');
                    
                        if($accepted <= 0){
                            $municipality_not_included[$i] = $muni->municipality;
                            $i++;
                        }
    
                    }
    
    
                return Datatables::of(DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->whereNotIn("municipality", $municipality_not_included)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    $isEbinhi = 0;
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)->first();
                    if(count($ebinhi_beneficiary)>0){
                        $isEbinhi = 1;
                    }

                    return  "<a class='btn btn-success btn-xs' data-ebinhi='$isEbinhi' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                })
                ->addColumn('total_beneficiaries', function($row){
                    
                    $reg_beneficiaries = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                      ->first();

                    if($reg_beneficiaries != null){
                        $reg_beneficiaries = $reg_beneficiaries->total_farmer;
                    }else{
                        $reg_beneficiaries = 0;
                    }



                    $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                    return $return_distributed;  
    
                })
                ->addColumn('total_registered_area', function($row){
                   
    
                    $total_data =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($total_data != null){
                        $total_data = $total_data->actual_area;
                    }else{
                        $total_data = 0;
                    }
                     $return_data = "Regular:". number_format($total_data, '2', '.', ',');       
    
                    return $return_data;
                })
      
                ->addColumn('total_bags_distributed', function($row){
                    $distributed =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($distributed != null){
                        $distributed = $distributed->total_bags;
                    }else{
                        $distributed = 0;
                    }

                     $return_distributed = "Regular distribution: ".number_format($distributed);
                    return $return_distributed;
    
    
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);  
                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    
                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', 1)
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
    
             
    
                        $totalBags = 0;
    
                        $accepted_transferred = "Accepted:   ".number_format($accepted);
                        if(intval($transferred)>0){
                            $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                            $totalBags += intval($transferred);
                        }
    
                  
    
                            if($totalBags>0){
                                $totalBags += intval($accepted);
                                $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                            }                        
    
                    return $accepted_transferred;
                   
                })
                ->addColumn('total_male_count', function($row){
                    $malecount =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($malecount != null){
                        $malecount = $malecount->total_male;
                    }else{
                        $malecount = 0;
                    }

      
                    $return_male = "Regular: ".number_format($malecount);
                    return $return_male;  
                })
                ->addColumn('total_female_count', function($row){
                    $femalecount =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality_group")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($femalecount != null){
                        $femalecount = $femalecount->total_female;
                    }else{
                        $femalecount = 0;
                    }
      
                    $return_female = "Regular: ".number_format($femalecount);
                    return $return_female;
                })
                ->addColumn('total_yield', function($row){
                    $yield = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                        ->where("municipality", $row->municipality)
                        ->first();

                        if($yield != null){
                            return number_format($yield->municipality_yield, 2);
                        }else{
                            return 0;
                        }
    
                })
                ->addColumn('total_area_claimed', function($row){
                    $total_count =DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_municipality")
                    ->where("municipality", $row->prv)
                    ->first();

                    if($total_count != null){
                        $total_count = $total_count->total_claimed_area;
                    }else{
                        $total_count = 0;
                    }

                    
                    $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 
                    return $return_data;
    
                })
                ->make(true);
            }
        } //EBINHI FALSE



       
    //END
    


    }



    private function search_to_array($array, $key, $value) {
        $results = array();
    
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }
    
            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search_to_array($subarray, $key, $value));
            }
        }
    
        return $results;
    }

    public function generateMunicipalReportData(Request $request){
        
        dd($request);//removed because of load live data v2.0
        
        $processed_data = DB::table($GLOBALS["season_prefix"]."rcep_reports.lib_municipal_reports")
            ->where("province", $request->province)->get();
        $processed_data = json_decode(json_encode($processed_data), true);

        $lib_drop = DB::connection("delivery_inspection_db")->table("lib_dropoff_point")
            ->where("province", $request->province)
            ->groupBy("municipality")
            ->get();
        $tbl = array();
            $province = $request->province;
            foreach($lib_drop as $muni)
            {
                $municipality = $muni->municipality;
                $farmer_info_result = $this->search_to_array($processed_data, "municipality", $municipality);
                $ebinhi_tag = 0;
                if(count($farmer_info_result)>0){
                    $confirmed_new =  $farmer_info_result[0]["total_confirmed"];
                    $total_accept = $farmer_info_result[0]["total_actual"] + $farmer_info_result[0]["total_transfer"];
                   
                    $accepted_transfer = "<b> Total Accepted: ".number_format($total_accept)."</b><br>";
                    $accepted_transfer .= "Accepted: ".number_format($farmer_info_result[0]["total_actual"])."<br>";
                    $accepted_transfer .= "Transferred: ".number_format($farmer_info_result[0]["total_transfer"])." </i><br>";
                    
                    //EBINHI
                        $ebinhi_distri = $farmer_info_result[0]["total_bags_ebinhi"];
                        $ebinhi_bene = $farmer_info_result[0]["total_farmers_ebinhi"];
                        $ebinhi_bene_data_male =$farmer_info_result[0]["total_male_ebinhi"];
                        $ebinhi_bene_data_female =$farmer_info_result[0]["total_female_ebinhi"];
                        
                        $bep_male = $ebinhi_bene_data_male;
                        $bep_female = $ebinhi_bene_data_female;
                        $bep_area = $farmer_info_result[0]["total_claim_area_ebinhi"];
                        $regular_dist = $farmer_info_result[0]["total_bags"];
                        $claimed_area = number_format( $farmer_info_result[0]["total_claimed_area"],2)." (ha)";

                        $beneficiaries =  $farmer_info_result[0]["total_farmers"];
                        $regular_bene = $beneficiaries;
                        $farmer_info_male = $farmer_info_result[0]["total_male"];
                        $farmer_info_female = $farmer_info_result[0]["total_female"];

                        $total_male = $farmer_info_male;
                        $total_female = $farmer_info_female;
                        $registered_area = $farmer_info_result[0]["total_actual_area"];

                        $municipal_yield = DB::table($GLOBALS["season_prefix"]."rcep_reports_view.final_outpul")
                        ->where("province", $request->province)
                        ->where("municipality", $municipality)
                        ->first();
    
                        if($municipal_yield != null){
                            $yield =  $municipal_yield->municipality_yield;
                            $yield = number_format($yield,2);
                        }else{
                            $yield = "-";
                        }

                        $distributed = $ebinhi_distri + $regular_dist;
                        $beneficiaries = $regular_bene + $ebinhi_bene;

                        $ebinhi_tag = 0;
                        if($distributed > 0 ){
                            $distributed_text = "<strong> Total: ".number_format($distributed)." bag(s)"."</strong>";
                            $beneficiaries_text = "<strong> Total: ".number_format($beneficiaries)."</strong>";
                            if($regular_dist > 0){
                                $distributed_text .= "<br> Regular: ". number_format($regular_dist);
                                $beneficiaries_text .= "<br> Regular: ". number_format($regular_bene); 

                            }

                            if($ebinhi_distri > 0 ){
                                $distributed_text .= "<br> BeP: " .number_format($ebinhi_distri);

                                $ebinhi_tag = 1;
                                $beneficiaries_text .= "<br> BeP: " .number_format($ebinhi_bene);


                            }
                        }else{
                            $distributed_text = "-";
                            $beneficiaries_text = "-";
                        }


                        $male = $total_male + $bep_male;
                        $female = $total_female + $bep_female;
                        $area_registered = $registered_area + $bep_area;
                        

                        if($male > 0){
                            $male_text = "<strong> Total: ".number_format($male)."</strong>";
                            if($total_male > 0){
                                $male_text .= "<br> Regular: ".number_format($total_male);
                            }
                            if($bep_male > 0){
                                $male_text .="<br> Regular: ".number_format($bep_male);
                            }
                        }else{
                            $male_text = "-";
                        }
                        
                        if($female > 0){
                            $female_text = "<strong> Total: ".number_format($female)."</strong>";
                            if($total_female > 0){
                                $female_text .="<br> Regular: ".number_format($total_female);
                            }
                            if($bep_female > 0){
                                $female_text .="<br> Regular: ".number_format($bep_female);
                            }
                        }else{
                            $female_text = "-";
                        }
                        
                        if($area_registered > 0){
                            $area_text = "<strong> Total: ".number_format($area_registered)."</strong>";
                            if($registered_area > 0){
                                $area_text .="<br> Regular: ".number_format($registered_area);
                            }
                            if($bep_area > 0){
                                $area_text .="<br> Regular: ".number_format($bep_area);
                            }
                        }else{
                            $area_text = "-";
                        }



                        //CROSSS OVER DATA
                                    
                        $cross_arr = array();
                        // $cross_arr["NUEVA VIZCAYASOLANO"] = false;

                        //DEFAULT DATA
                        $co_bags = 0;
                        $co_claim_area = 0;
                        $co_actual_area = 0;

                        $co_total_farmer = 0;
                        $co_male = 0;
                        $co_female = 0;
                        $co_other = 0;
                        $co_yield_area = 0;
                        $co_total_production = 0;
                        $co_computed_yield = 0;


                        if(isset($cross_arr[$province.$municipality])){
                            $prv_tbl_co = $GLOBALS['season_prefix']."prv_".substr($muni->prv,0,4).".released_cross_over";
                            $co_data_sex = DB::table($prv_tbl_co)
                                ->select(DB::raw("SUM(IF(UPPER(SUBSTRING(sex,1,1))='M',1,0)) as co_male "),DB::raw("SUM(IF(UPPER(SUBSTRING(sex,1,1))='F',1,0)) as co_female "))
                                // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")          
                                ->where("municipality", $muni->municipality) 
                                ->where("province", $muni->province) 
                                ->first();

                                if($co_data_sex->co_male !=null){$co_male = $co_data_sex->co_male;}
                                if($co_data_sex->co_female !=null){$co_female = $co_data_sex->co_female;}

                            $co_total_farmer = DB::table($prv_tbl_co)
                                ->select(DB::raw("COUNT(release_id) as co_total_farmer"))
                                // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")          
                                ->where("municipality", $muni->municipality) 
                                ->where("province", $muni->province) 
                                ->first();
                                if($co_total_farmer->co_total_farmer !=null){$co_total_farmer = $co_total_farmer->co_total_farmer;}

                                $co_other = $co_total_farmer - ($co_male + $co_female);

                            $co_data = DB::table($prv_tbl_co)
                                ->select(DB::raw("SUM(bags) as co_bags"),DB::raw("SUM(claimed_area) as co_claim_area"), DB::raw("SUM(actual_area) as co_actual_area") )
                                // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")     
                                ->where("municipality", $muni->municipality) 
                                ->where("province", $muni->province)               
                                ->first();
                                
                                if($co_data->co_bags !=null){$co_bags = $co_data->co_bags;}
                                if($co_data->co_claim_area !=null){$co_claim_area = $co_data->co_claim_area;}
                                if($co_data->co_actual_area !=null){$co_actual_area = $co_data->co_actual_area;}

                            $distributed_text .= "<br> Cross-Over :" . $co_bags;
                            $beneficiaries_text .= "<br> Cross-Over :" . $co_total_farmer;
                            $male_text .= "<br> Cross-Over :" . $co_male;
                            $female_text .= "<br> Cross-Over :" . $co_female;
                            $area_text .= "<br> Cross-Over :" . $co_actual_area;
                            
                            $claimed_area .= "<br> Cross-Over :" . $co_claim_area;
                        }



                        
                        $btn = "";
                        // if(Auth::user()->roles->first()->name == "rcef-programmer"){
                        //     $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                        // }else{
                        //     $btn =  "<a class='btn btn-warning btn-xs' disabled><i class='fa fa-warning'></i> Temporarily Disabled</a>";
                        // }
                        $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                        array_push($tbl, array(
                            "municipality" => $municipality,
                            "accepted_transferred" => $accepted_transfer,
                            "total_bags_distributed" => $distributed_text,
                            "total_beneficiaries" => $beneficiaries_text,
                            "total_male_count" => $male_text,
                            "total_female_count" => $female_text,
                            "total_registered_area" => $area_text,
                            "total_yield" => $yield,
                            "total_area_claimed" => $claimed_area,
                            "action" => $btn
                        ));


                }else{
                    $regular_dist = 0;
                    $claimed_area = "-" ; 
                    $regular_bene = 0;
                    $total_male = 0;
                    $total_female = 0;
                    $registered_area = 0;
                    $yield = "-";

                    $btn = "";
                    $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                    array_push($tbl, array(
                        "municipality" => $municipality,
                        "accepted_transferred" => "Processing...",
                        "total_bags_distributed" => "Processing...",
                        "total_beneficiaries" => "Processing...",
                        "total_male_count" => "Processing...",
                        "total_female_count" => "Processing...",
                        "total_registered_area" => "Processing...",
                        "total_yield" => "Processing...",
                        "total_area_claimed" => "Processing...",
                        "action" => $btn
                    ));

                }

      
                






            }

            $tbl = collect($tbl);

            return Datatables::of($tbl)
                ->make(true);


    
    }

/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------- */
//NEW CODE

public function generateLiveMunicipalReportDataPy(Request $request){
        $prv = DB::connection("delivery_inspection_db")->table("lib_dropoff_point")
        ->select("prv","municipality")
        ->where("province", $request->province)
        ->groupBy("municipality")
        ->first();

        $pythonPath = $GLOBALS['python_path'];

        $scriptPath = base_path('app/Http/PyScript/load_live_data.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];
        $prov = $request->province;
        $prv = substr($prv->prv, 0, 4);

        $escapedSsn = escapeshellarg($ssn);
        $escapedProvince = escapeshellarg($prov);
        $escapedPrv = escapeshellarg($prv);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedProvince $escapedPrv";

    
        // Create a new process
        $process = new Process($command);

        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $return_output = json_decode($output, true);
            $tbl = array();
            foreach($return_output as $data)
            {
                $municipality = $data['t3_municipality'];
                $yield = number_format($data['t8_municipality_yield'],2);
                $claimed_area =number_format($data['claimed_area'],2)." (ha)";//total_final_area
                $accepted_transfer = "<b> Total Accepted: ".number_format($data['totalBagCount_sum'])."</b><br>";
                $accepted_transfer .= "Accepted: ".number_format($data['totalBagCount_sum_a'])."<br>";
                $accepted_transfer .= "Re-Transfer: ".number_format($data['totalBagCount_sum_t'])."<br>";
                $accepted_transfer .= "Transferred: ".number_format($data['totalBagCount_sum_p'])." </i><br>";

                $ebinhi_tag = 0;
                $distributed_text = "<strong> Total: ".number_format($data['ebinhi_distri']+$data['bags_claimed'])." bag(s)"."</strong>";//$data['distributed']
                $distributed_text .= "<br> Regular: ". number_format($data['bags_claimed']);
                $distributed_text .= "<br> BeP: " .number_format($data['ebinhi_distri']);

                if($data['home_dist']>0){
                    $distributed_text .= "<br> <strong>Note:</strong> Claimed in home DOP: ". number_format($data['home_dist']);
                }

                if($data['parcel_dist']>0){
                    $distributed_text .= "<br> <strong>Note:</strong> Claimed intended for parcel DOP: ". number_format($data['parcel_dist']);
                }

                $beneficiaries_text = "<strong> Total: ".number_format($data['regular_bene']+$data['ebinhi_bene'])."</strong>";//$data['beneficiaries']
                $beneficiaries_text .= "<br> Regular: ". number_format($data['regular_bene']);
                $beneficiaries_text .= "<br> BeP: " .number_format($data['ebinhi_bene']);
                
                $male_text = "<strong> Total: ".number_format($data['total_male']+$data['bep_male_count'])."</strong>"; //subject to change to male count
                $male_text .= "<br> Regular: ".number_format($data['total_male']);
                $male_text .="<br> BeP: ".number_format($data['bep_male_count']);

               $female_text = "<strong> Total: ".number_format($data['total_female']+$data['bep_female_count'])."</strong>";//subject to change to female count
               $female_text .="<br> Regular: ".number_format($data['total_female']);
               $female_text .="<br> BeP: ".number_format($data['bep_female_count']);
                
               $area_text = "<strong> Total: ".number_format($data['total_final_area']+$data['actual_area_bep'])."</strong>";//subject to change to something
               $area_text .="<br> Regular: ".number_format($data['total_final_area']);
               $area_text .="<br> BeP: ".number_format($data['actual_area_bep']);
                
                $btn = "";
                // if(Auth::user()->roles->first()->name == "rcef-programmer"){
                //     $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                // }else{
                //     $btn =  "<a class='btn btn-warning btn-xs' disabled><i class='fa fa-warning'></i> Temporarily Disabled</a>";
                // }
                $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
    
                array_push($tbl, array(
                    "municipality" => $data['t3_municipality'],
                    "accepted_transferred" => $accepted_transfer,
                    "total_bags_distributed" => $distributed_text,
                    "total_beneficiaries" => $beneficiaries_text,
                    "total_male_count" => $male_text,
                    "total_female_count" => $female_text,
                    "total_registered_area" => $area_text,
                    "total_yield" => $yield,
                    "total_area_claimed" => $claimed_area,//mark
                    "action" => $btn
                ));
            }
    
            $tbl = collect($tbl);
    
            return Datatables::of($tbl)
                ->make(true); 

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }

}


/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------- */


//original code of 
public function generateLiveMunicipalReportData(Request $request){
    dd($request);
        $lib_drop = DB::connection("delivery_inspection_db")->table("lib_dropoff_point")
            ->where("province", $request->province)
            ->groupBy("municipality")
            ->get();
        $tbl = array();
            $province = $request->province;
            foreach($lib_drop as $muni)
            {
                $municipality = $muni->municipality;
                $confirmed_new =  DB::connection("delivery_inspection_db")->table("tbl_delivery")
                    ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->where("isBuffer", 0)
                    ->orWhere("province", $request->province) // SUBJECT TO REMOVE
                    ->where("municipality", $municipality)
                    ->where("isBuffer", 9)
                    ->sum("totalBagCount");

                $confirmed_batches = DB::connection("delivery_inspection_db")->table("tbl_delivery")
                ->select("batchTicketNumber")
                ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->where("isBuffer", 0)
                ->orWhere("province", $request->province)
                    ->where("municipality", $municipality)
                    ->where("isBuffer", 9)
                ->groupBy("batchTicketNumber")
                ->get();
                $confirmed_batches = json_decode(json_encode($confirmed_batches),true);


                $accepted = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->whereIn("batchTicketNumber", $confirmed_batches)
                    ->where("isRejected", 0)
                    ->sum("totalBagCount");
                    

                $re_transfer = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("transferCategory", "T")
                    ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->where("isRejected", 0)
                    ->sum("totalBagCount");
                    
                $transfered_batches2 = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->select( DB::raw("CONCAT('transferred from batch: ',batchTicketNumber) as batchTicketNumber"))   
                    ->where("transferCategory", "P") 
                    ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->where("isRejected", 0)
                    ->groupBy("batchTicketNumber")
                    ->get();
                $transfered_batches2 = json_decode(json_encode($transfered_batches2),true);

                $add_transferred = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                ->orWhereIn("remarks", $transfered_batches2)
                ->where("province", $request->province)
                ->where("municipality", $municipality)
                ->where("isRejected", 0)
                ->sum("totalBagCount");

                $transfer = DB::connection("delivery_inspection_db")->table("tbl_actual_delivery")
                    ->where("transferCategory", "P") 
                    ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->where("isRejected", 0)
                    ->sum("totalBagCount");
                $transferred = $add_transferred + $transfer ;

                $total_accept = $transferred + $accepted + $re_transfer;

                if($total_accept <= 0){
                    continue;
                }

                // $accepted_transfer = "<i> Confirmed: ".number_format($confirmed_new)." <br>";
                $accepted_transfer = "<b> Total Accepted: ".number_format($total_accept)."</b><br>";
                $accepted_transfer .= "Accepted: ".number_format($accepted)."<br>";
                $accepted_transfer .= "Re-Transfer: ".number_format($re_transfer)."<br>";
                
                //where transferCategory = P
                $accepted_transfer .= "Transferred: ".number_format($transferred)." </i><br>";
        

                //EBINHI
                $ebinhi_distri = count(DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_claim")
                    ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->get());

                $ebinhi_bene =  count(DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_claim")
                ->where("province", $request->province)
                ->where("municipality", $municipality)
                ->groupBy("paymaya_code")
                ->get());

                $ebinhi_claim_code = DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_claim")
                ->select("paymaya_code")
                ->where("province", $request->province)
                ->where("municipality", $municipality)
                ->groupBy("paymaya_code")
                ->get();

                $ebinhi_claim_code = json_decode(json_encode($ebinhi_claim_code), true);

                $ebinhi_bene_data_male = count(DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_beneficiaries")
                // ->select(DB::raw("SUM(IF(,1,0)) as total_male"), DB::raw("SUM(IF(UPPER(SUBSTR(sex,1,1))='F',1,0)) as total_female"),
                // DB::raw("SUM(area) as area"))
                ->where(DB::raw("UPPER(SUBSTR(sex,1,1))"), "M")
                ->whereIn("paymaya_code", $ebinhi_claim_code)
                ->get());

                $ebinhi_bene_data_female = count(DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_beneficiaries")
                // ->select(DB::raw("SUM(IF(,1,0)) as total_male"), DB::raw("SUM(IF(UPPER(SUBSTR(sex,1,1))='F',1,0)) as total_female"),
                // DB::raw("SUM(area) as area"))
                ->where(DB::raw("UPPER(SUBSTR(sex,1,1))"), "F")
                ->whereIn("paymaya_code", $ebinhi_claim_code)
                ->get());

                $ebinhi_bene_data = DB::table($GLOBALS["season_prefix"]."rcep_paymaya.tbl_beneficiaries")
                ->select(DB::raw("SUM(area) as area"))
                ->whereIn("paymaya_code", $ebinhi_claim_code)
                ->first();
                

            $bep_male = $ebinhi_bene_data_male;
            $bep_female = $ebinhi_bene_data_female;
            $bep_area = $ebinhi_bene_data->area;
                
                $db_prv = SUBSTR($muni->prv,0,4);

                $release = DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".new_released")
                        ->select(DB::raw("SUM(bags_claimed) as bags"), DB::raw("SUM(claimed_area) as claimed_area") )
                        ->where("category", "INBRED")
                        ->where("prv_dropoff_id", "LIKE", $muni->prv."%")
                        
                        ->first();
    
                $home_dist = 0;
                $parcel_dist = 0;
                $releaseHome = DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".new_released")
                ->where("category", "INBRED")
                // ->where("prv_dropoff_id", "LIKE", "143211%")
                ->where("prv_dropoff_id", "LIKE", $muni->prv."%")
                ->where("remarks","LIKE","%claimed in home address%")
                ->get();
                
                
                foreach($releaseHome as $rel)
                {
                    $remarks = $rel->remarks;
                    $remarks = str_replace(" bags claimed in home address DOP ",',',$remarks);
                    $remarks = str_replace("(",',',$remarks);
                    $remarks = str_replace("(",',',$remarks);
                    $remarks = str_replace(") with area of ",',',$remarks);
                    $remarks = explode(",",$remarks);
                    $home_dist += ($remarks[0]);
                    // dd($home_dist);
                }

                $releaseParcel = DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".new_released")
                ->where("category", "INBRED")
                // ->where("prv_dropoff_id", "LIKE", "143211%")
                ->where("prv_dropoff_id", "LIKE", $muni->prv."%")
                ->where("remarks","LIKE","%claimed intended for Parcel%")
                ->get();

                foreach($releaseParcel as $relParcel)
                {
                    $remarksParcel = $relParcel->remarks;
                    $remarksParcel = str_replace(" bags claimed intended for Parcel DOP ",',',$remarksParcel);
                    $remarksParcel = str_replace("(",',',$remarksParcel);
                    $remarksParcel = str_replace("(",',',$remarksParcel);
                    $remarksParcel = str_replace(") with area of ",',',$remarksParcel);
                    $remarksParcel = explode(",",$remarksParcel);
                    $parcel_dist += ($remarksParcel[0]);
                    // dd($parcel_dist);
                }

                if($release->bags > 0){
                    
                    $regular_dist = $release->bags;
                    $claimed_area = number_format($release->claimed_area,2)." (ha)";

                    $beneficiaries = count(DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".new_released")
                        ->where("prv_dropoff_id", "LIKE", $muni->prv."%")
                        ->where("category", "INBRED")
                        ->groupBy("new_released_id")
                        ->get());
                     
                    $regular_bene = $beneficiaries;




                $release_rcef_id = DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".new_released")
                ->select('rcef_id')
                ->groupby("rcef_id")
                ->where("category", "INBRED")
                ->where("prv_dropoff_id", "LIKE", $muni->prv."%")
                ->get();
                $release_rcef_id = json_decode(json_encode($release_rcef_id), true);

                $farmer_info =  DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".farmer_information_final")
                ->select(DB::raw("SUM(final_area) as final_area"))
                ->whereIn("rcef_id", $release_rcef_id)
                ->first();

        $farmer_info_male = count(DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".farmer_information_final")
                    ->where(DB::raw("UPPER(SUBSTR(sex,1,1))"), "M")
                    ->whereIn("rcef_id", $release_rcef_id)
                    ->groupBy("rcef_id")
                    ->get());
        
        $farmer_info_female = count(DB::table($GLOBALS["season_prefix"]."prv_".$db_prv.".farmer_information_final")
                    ->where(DB::raw("UPPER(SUBSTR(sex,1,1))"), "F")
                    ->whereIn("rcef_id", $release_rcef_id)
                    ->groupBy("rcef_id")
                    ->get());

     
        $total_male = $farmer_info_male;
        $total_female = $farmer_info_female;
        $registered_area = $farmer_info->final_area;





                $municipal_yield = DB::table($GLOBALS["season_prefix"]."rcep_reports_view.final_outpul")
                    ->where("province", $request->province)
                    ->where("municipality", $municipality)
                    ->first();

                if($municipal_yield != null){
                    $yield =  $municipal_yield->municipality_yield;
                    $yield = number_format($yield,2);
                }else{
                    $yield = "-";
                }
                
                }else{
                    $regular_dist = 0;
                    $claimed_area = "-" ; 
                    $regular_bene = 0;
                    $total_male = 0;
                    $total_female = 0;
                    $registered_area = 0;
                    $yield = "-";
                }

                $distributed = $ebinhi_distri + $regular_dist + $parcel_dist;
                $beneficiaries = $regular_bene + $ebinhi_bene;

                $ebinhi_tag = 0;
                if($distributed > 0 ){
                    $distributed_text = "<strong> Total: ".number_format($distributed)." bag(s)"."</strong>";
                    $beneficiaries_text = "<strong> Total: ".number_format($beneficiaries)."</strong>";
                    if($regular_dist > 0){
                        $distributed_text .= "<br> Regular: ". number_format($regular_dist);
                        $beneficiaries_text .= "<br> Regular: ". number_format($regular_bene); 
                        
                    }
                    if($home_dist>0){
                        $distributed_text .= "<br> <strong>Note:</strong> Claimed in home DOP: ". number_format($home_dist);
                    }

                    if($parcel_dist>0){
                        $distributed_text .= "<br> <strong>Note:</strong> Claimed intended for parcel DOP: ". number_format($parcel_dist);
                    }

                    if($ebinhi_distri > 0 ){
                        $distributed_text .= "<br> BeP: " .number_format($ebinhi_distri);

                        $ebinhi_tag = 1;
                        $beneficiaries_text .= "<br> BeP: " .number_format($ebinhi_bene);


                    }
                }else{
                    $distributed_text = "-";
                    $beneficiaries_text = "-";
                }
                

                $male = $total_male + $bep_male;
                $female = $total_female + $bep_female;
                $area_registered = $registered_area + $bep_area;
                

                if($male > 0){
                    $male_text = "<strong> Total: ".number_format($male)."</strong>";
                    if($total_male > 0){
                        $male_text .= "<br> Regular: ".number_format($total_male);
                    }
                    if($bep_male > 0){
                        $male_text .="<br> Regular: ".number_format($bep_male);
                    }
                }else{
                    $male_text = "-";
                }
                
                if($female > 0){
                    $female_text = "<strong> Total: ".number_format($female)."</strong>";
                    if($total_female > 0){
                        $female_text .="<br> Regular: ".number_format($total_female);
                    }
                    if($bep_female > 0){
                        $female_text .="<br> Regular: ".number_format($bep_female);
                    }
                }else{
                    $female_text = "-";
                }
                
                if($area_registered > 0){
                    $area_text = "<strong> Total: ".number_format($area_registered)."</strong>";
                    if($registered_area > 0){
                        $area_text .="<br> Regular: ".number_format($registered_area);
                    }
                    if($bep_area > 0){
                        $area_text .="<br> Regular: ".number_format($bep_area);
                    }
                }else{
                    $area_text = "-";
                }
                
           
                //CROSSS OVER DATA
               
                $cross_arr = array();
                // $cross_arr["NUEVA VIZCAYASOLANO"] = false;

                //DEFAULT DATA
                $co_bags = 0;
                $co_claim_area = 0;
                $co_actual_area = 0;

                $co_total_farmer = 0;
                $co_male = 0;
                $co_female = 0;
                $co_other = 0;
                $co_yield_area = 0;
                $co_total_production = 0;
                $co_computed_yield = 0;


                if(isset($cross_arr[$province.$municipality])){
                    $prv_tbl_co = $GLOBALS['season_prefix']."prv_".substr($muni->prv,0,4).".released_cross_over";
                    $co_data_sex = DB::table($prv_tbl_co)
                        ->select(DB::raw("SUM(IF(UPPER(SUBSTRING(sex,1,1))='M',1,0)) as co_male "),DB::raw("SUM(IF(UPPER(SUBSTRING(sex,1,1))='F',1,0)) as co_female "))
                        // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")          
                        ->where("municipality", $muni->municipality) 
                        ->where("province", $muni->province) 
                        ->first();

                        if($co_data_sex->co_male !=null){$co_male = $co_data_sex->co_male;}
                        if($co_data_sex->co_female !=null){$co_female = $co_data_sex->co_female;}

                    $co_total_farmer = DB::table($prv_tbl_co)
                        ->select(DB::raw("COUNT(release_id) as co_total_farmer"))
                        // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")          
                        ->where("municipality", $muni->municipality) 
                        ->where("province", $muni->province) 
                        ->first();
                        if($co_total_farmer->co_total_farmer !=null){$co_total_farmer = $co_total_farmer->co_total_farmer;}

                        $co_other = $co_total_farmer - ($co_male + $co_female);

                    $co_data = DB::table($prv_tbl_co)
                        ->select(DB::raw("SUM(bags) as co_bags"),DB::raw("SUM(claimed_area) as co_claim_area"), DB::raw("SUM(actual_area) as co_actual_area") )
                        // ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")     
                        ->where("municipality", $muni->municipality) 
                        ->where("province", $muni->province)               
                        ->first();
                        
                        if($co_data->co_bags !=null){$co_bags = $co_data->co_bags;}
                        if($co_data->co_claim_area !=null){$co_claim_area = $co_data->co_claim_area;}
                        if($co_data->co_actual_area !=null){$co_actual_area = $co_data->co_actual_area;}

                      $distributed_text .= "<br> Cross-Over :" . $co_bags;
                      $beneficiaries_text .= "<br> Cross-Over :" . $co_total_farmer;
                      $male_text .= "<br> Cross-Over :" . $co_male;
                      $female_text .= "<br> Cross-Over :" . $co_female;
                      $area_text .= "<br> Cross-Over :" . $co_actual_area;
                      
                      $claimed_area .= "<br> Cross-Over :" . $co_claim_area;
                      
                      
                      

                }

                $btn = "";
                // if(Auth::user()->roles->first()->name == "rcef-programmer"){
                //     $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                // }else{
                //     $btn =  "<a class='btn btn-warning btn-xs' disabled><i class='fa fa-warning'></i> Temporarily Disabled</a>";
                // }
                $btn =  "<a class='btn btn-success btn-xs' data-ebinhi='$ebinhi_tag' data-province='$request->province' data-municipality='$municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                array_push($tbl, array(
                    "municipality" => $municipality,
                    "accepted_transferred" => $accepted_transfer,
                    "total_bags_distributed" => $distributed_text,
                    "total_beneficiaries" => $beneficiaries_text,
                    "total_male_count" => $male_text,
                    "total_female_count" => $female_text,
                    "total_registered_area" => $area_text,
                    "total_yield" => $yield,
                    "total_area_claimed" => $claimed_area,
                    "action" => $btn
                ));
            }

            $tbl = collect($tbl);

            return Datatables::of($tbl)
                ->make(true);


    }





    public function generateMunicipalReportData_X09202022(Request $request){

     

        if($request->ebinhi == "true"){
     
            if(Auth::user()->roles->first()->name == "da-icts"){
                return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);
            
            }else{
    
                $municipality_not_included = array();
                $municipality = DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->orderBy('municipality', 'ASC')
                    ->groupBy("municipality")
                    ->get();
                    $i=0;
                    foreach($municipality as $muni){
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where("province", $muni->province)
                        ->where("municipality", $muni->municipality)
                        ->value('total_bags');
                    
                        if($accepted <= 0){
                            $municipality_not_included[$i] = $muni->municipality;
                            $i++;
                        }
    
                    }
    
    
                return Datatables::of(DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->whereNotIn("municipality", $municipality_not_included)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                  
                    $isEbinhi = 0;
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)->first();
                    if(count($ebinhi_beneficiary)>0){
                        $isEbinhi = 1;
                    }

                    return  "<a class='btn btn-success btn-xs' data-ebinhi='$isEbinhi' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                    
                    
                
                })
                ->addColumn('total_beneficiaries', function($row){
                    
                    $reg_beneficiaries = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                                ->where("municipality", $row->municipality)
                                ->value("total_beneficiary");
                    $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                 
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_beneficiaries")->where("province", $row->province)->where("municipality", $row->municipality)->value("total_beneficiaries");
    
                    if($ebinhi_beneficiary>0){
                        $return_distributed.= "<br> e-Binhi: ".number_format($ebinhi_beneficiary);
                        $total_beneficiaries = $ebinhi_beneficiary + $reg_beneficiaries;
                        $return_distributed.= "<br> <b> Total: ".number_format($total_beneficiaries)."</b>";
    
                    }
                    return $return_distributed;  
    
                })
                ->addColumn('total_registered_area', function($row){
                   
    
                    $total_data = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                    ->where("municipality", $row->municipality)
                    ->value("total_area");
                    $return_data = "Regular:". number_format($total_data, '2', '.', ',');        
    
    
                    //   $ebinhi_data= DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                    //  ->groupBy("paymaya_code")
                    //  ->get();
    
                    // if(count($ebinhi_data) > 0){
    
                    //      $ebinhi_count=0;
                    //  foreach ($ebinhi_data as $key => $value) {
                    //     $getArea = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    //         ->where("paymaya_code", $value->paymaya_code)
                    //         ->first();
                    //     $ebinhi_count += $getArea->area;
                    //      }
                    //     $return_data.= "<br>e-Binhi: ".number_format($ebinhi_count,2);
                    //     $total_distibuted = $ebinhi_count + $total_data;
                    //     $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";
    
                    // }
    
                    return $return_data;
                })
                /*->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
    
                     $total_data = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".released")
                                ->where("province", $row->province)
                                ->where("municipality", $row->municipality)
                                ->sum("actual_area");
                     return number_format($total_data, '2', '.', ',');  
    
    
                }) */
                ->addColumn('total_bags_distributed', function($row){
                    $distributed = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                    ->where("municipality", $row->municipality)
                    ->value("total_bags");
                        $return_distributed = "Regular distribution: ".number_format($distributed);
                       
                     $eBinhi_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_bags")->where("province", $row->province)->where("municipality", $row->municipality)->value("total_bags");
                    if($eBinhi_claim>0){
                        $return_distributed.= "<br> e-Binhi: ".number_format($eBinhi_claim);
                        $total_distibuted = $eBinhi_claim + $distributed;
                        $return_distributed.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
    
    
                    return $return_distributed;
    
    
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);  
                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    
                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', 1)
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
    
                    $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', "!=",1)
                        ->where('qrStart', '>', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');  
    
                        $totalBags = 0;
    
                        $accepted_transferred = "Accepted:   ".number_format($accepted);
                        if(intval($transferred)>0){
                            $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                            /*
                            $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                            data-region = '".$row->region."'
                            data-province = '".$row->province."'
                            data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                            */
                            $totalBags += intval($transferred);
                        }
    
                         if(intval($ebinhi)>0){
                            $accepted_transferred .= " <br> e-Binhi: ".number_format($ebinhi);
                            /*
                            $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                            data-region = '".$row->region."'
                            data-province = '".$row->province."'
                            data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                            */
                             $totalBags += intval($ebinhi);
                        }
    
                            if($totalBags>0){
                                $totalBags += intval($accepted);
                                $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                            }                        
    
                    return $accepted_transferred;
                   
                })
                ->addColumn('total_male_count', function($row){
                    $malecount = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                    ->where("municipality", $row->municipality)
                    ->value("total_male");
                    $return_male = "Regular: ".number_format($malecount);
    
                     $ebinhi_male = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                     ->groupBy("paymaya_code")
                     ->whereRaw("paymaya_code in (Select paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex ='male' OR sex='M' )")
                     ->get();
                    if(count($ebinhi_male)>0){
                        $return_male.= "<br>e-Binhi: ".number_format(count($ebinhi_male));
                        $total_distibuted = count($ebinhi_male) + $malecount;
                        $return_male.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
    
    
                    return $return_male;  
                })
                ->addColumn('total_female_count', function($row){
                    $femalecount = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                    ->where("municipality", $row->municipality)
                    ->value("total_female");
                    $return_female = "Regular: ".number_format($femalecount);
    
                     $ebinhi_female = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                     ->groupBy("paymaya_code")
                     ->whereRaw("paymaya_code in (Select paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex ='female' OR sex='F' )")
                     ->get();
                    if(count($ebinhi_female)>0){
                        $return_female.= "<br>e-Binhi: ".number_format(count($ebinhi_female));
                        $total_distibuted = count($ebinhi_female) + $femalecount;
                        $return_female.= "<br> <b> Total: ".number_format($total_distibuted)."</b>";
    
                    }
                    return $return_female;
                })
                ->addColumn('total_yield', function($row){
                    //return number_format($row->yield, '2', '.', ',');  
                    $product_production_weight = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".released")
                                ->select("total_production", "ave_weight_per_bag", "area_harvested")
                                ->where("province", $row->province)
                                ->where("municipality", $row->municipality)
                                ->where("ave_weight_per_bag", ">", 30)
                                ->where("ave_weight_per_bag", "<", 80)
                                //->where('season', 'like', 'DS 2021')
                                ->where("area_harvested", "!=", 0)
                                ->where("total_production", "!=", 0)
                                ->where("ave_weight_per_bag", "!=", 0)
                                ->get();
    
                    $sum_weight_product = 0;
                    $sum_area_harvested = 0;
                    foreach ($product_production_weight as $key => $value) {
    
                            $temp_computed_yield = (($value->total_production * $value->ave_weight_per_bag)/$value->area_harvested)/1000;
    
                            if($temp_computed_yield <= 1){
    
                         //   }else if ($temp_computed_yield > 13){
    
                            }else{
                                $sum_weight_product += $value->total_production * $value->ave_weight_per_bag;
                                $sum_area_harvested += $value->area_harvested;
                            }
    
    
                            
                    }
    
                    if($sum_area_harvested <= 0){
                     return 0;   
                    } else{
    
                     $computed_yield = (floatval($sum_weight_product) / floatval($sum_area_harvested)) / 1000;
                            return number_format($computed_yield, '2', '.', ','); 
                    }
    
                })
                ->addColumn('total_area_claimed', function($row){
                    $total_count = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                                ->where("municipality", $row->municipality)
                                ->value("total_claimed");
                    
                    $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 
    
    
                    $ebinhi_data= DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)
                     ->groupBy("paymaya_code")
                     ->get();
    
                    if(count($ebinhi_data) > 0){
    
                         $ebinhi_count=0;
                     foreach ($ebinhi_data as $key => $value) {
                        $getArea = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                            ->where("paymaya_code", $value->paymaya_code)
                            ->first();
                            if($getArea != null){
                                $ebinhi_count += $getArea->area;
                            }


                        
                         }
                        $return_data.= "<br>e-Binhi: ".number_format($ebinhi_count,2);
                        $total_distibuted = $ebinhi_count + $total_count;
                        $return_data.= "<br> <b> Total: ".number_format($total_distibuted,2)."</b>";
    
                    }
                    return $return_data;
    
                })
                ->make(true);
            }
        } //EBINHI TRUE
        else{
            if(Auth::user()->roles->first()->name == "da-icts"){
                return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                    ->where('province', '=', $request->province)
                    ->where('total_farmers', '!=', 0)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('total_beneficiaries', function($row){
                    return number_format($row->total_farmers);       
                })
                ->addColumn('total_registered_area', function($row){
                    return number_format($row->total_actual_area, '2', '.', ',');       
                })
                ->addColumn('total_estimated_area', function($row){
                    return number_format($row->total_dist_area, '2', '.', ',');       
                })
                ->addColumn('total_bags_distributed', function($row){
                    return number_format($row->total_bags);       
                })
                ->addColumn('total_male_count', function($row){
                    return number_format($row->total_male);       
                })
                ->addColumn('total_female_count', function($row){
                    return number_format($row->total_female);       
                })
                ->make(true);
            
            }else{
    
                             $municipality_not_included = array();
                $municipality = DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->orderBy('municipality', 'ASC')
                    ->groupBy("municipality")
                    ->get();
                    $i=0;
                    foreach($municipality as $muni){
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where("province", $muni->province)
                        ->where("municipality", $muni->municipality)
                        ->value('total_bags');
                    
                        if($accepted <= 0){
                            $municipality_not_included[$i] = $muni->municipality;
                            $i++;
                        }
    
                    }
    
    
                return Datatables::of(DB::connection('delivery_inspection_db')->table('lib_prv')
                    ->where('province', '=', $request->province)
                    ->whereNotIn("municipality", $municipality_not_included)
                    ->orderBy('municipality', 'ASC')
                )
                ->addColumn('action', function($row){
                    //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                    //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                    $isEbinhi = 0;
                    $ebinhi_beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $row->province)->where("municipality", $row->municipality)->first();
                    if(count($ebinhi_beneficiary)>0){
                        $isEbinhi = 1;
                    }

                    return  "<a class='btn btn-success btn-xs' data-ebinhi='$isEbinhi' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";

                })
                ->addColumn('total_beneficiaries', function($row){
                    
                    $reg_beneficiaries = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                                ->where("municipality", $row->municipality)
                                ->value("total_beneficiary");
                    $return_distributed = "Regular: ".number_format($reg_beneficiaries);
                    return $return_distributed;  
    
                })
                ->addColumn('total_registered_area', function($row){
                   
    
                    $total_data = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                                ->where("municipality", $row->municipality)
                                ->value("total_area");
                     $return_data = "Regular:". number_format($total_data, '2', '.', ',');       
    
                    return $return_data;
                })
      
                ->addColumn('total_bags_distributed', function($row){
                     $distributed = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                                ->where("municipality", $row->municipality)
                                ->value("total_bags");
                     $return_distributed = "Regular distribution: ".number_format($distributed);
                    return $return_distributed;
    
    
                })
                ->addColumn('accepted_transferred', function($row){
                   // return number_format($row->total_bags);  
                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    
                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->where('is_transferred', 1)
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
    
             
    
                        $totalBags = 0;
    
                        $accepted_transferred = "Accepted:   ".number_format($accepted);
                        if(intval($transferred)>0){
                            $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                            $totalBags += intval($transferred);
                        }
    
                  
    
                            if($totalBags>0){
                                $totalBags += intval($accepted);
                                $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                            }                        
    
                    return $accepted_transferred;
                   
                })
                ->addColumn('total_male_count', function($row){
                $malecount = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                    ->where("municipality", $row->municipality)
                    ->value("total_male");

      
                    $return_male = "Regular: ".number_format($malecount);
                    return $return_male;  
                })
                ->addColumn('total_female_count', function($row){
                    $femalecount = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                    ->where("municipality", $row->municipality)
                    ->value("total_female");

      
                    $return_female = "Regular: ".number_format($femalecount);
                    return $return_female;
                })
                ->addColumn('total_yield', function($row){
                    //return number_format($row->yield, '2', '.', ',');  
                    $product_production_weight = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".released")
                                ->select("total_production", "ave_weight_per_bag", "area_harvested")
                                ->where("province", $row->province)
                                ->where("municipality", $row->municipality)
                                ->where("ave_weight_per_bag", ">", 30)
                                ->where("ave_weight_per_bag", "<", 80)
                                //->where('season', 'like', 'DS 2021')
                                ->where("area_harvested", "!=", 0)
                                ->where("total_production", "!=", 0)
                                ->where("ave_weight_per_bag", "!=", 0)
                                ->get();
    
                    $sum_weight_product = 0;
                    $sum_area_harvested = 0;
                    foreach ($product_production_weight as $key => $value) {
    
                            $temp_computed_yield = (($value->total_production * $value->ave_weight_per_bag)/$value->area_harvested)/1000;
    
                            if($temp_computed_yield <= 1){
    
                         //   }else if ($temp_computed_yield > 13){
    
                            }else{
                                $sum_weight_product += $value->total_production * $value->ave_weight_per_bag;
                                $sum_area_harvested += $value->area_harvested;
                            }
    
    
                            
                    }
    
                    if($sum_area_harvested <= 0){
                     return 0;   
                    } else{
    
                     $computed_yield = (floatval($sum_weight_product) / floatval($sum_area_harvested)) / 1000;
                            return number_format($computed_yield, '2', '.', ','); 
                    }
    
                })
                ->addColumn('total_area_claimed', function($row){
                    $total_count = DB::table($GLOBALS['season_prefix']."prv_".$row->prv_code.".municipal_report")
                                ->where("municipality", $row->municipality)
                                ->value("total_claimed");
                    
                    $return_data = "Regular: ".number_format($total_count, '2', '.', ','); 
                    return $return_data;
    
                })
                ->make(true);
            }
        } //EBINHI FALSE



       
    //END
    }



    public function generateMunicipalReportData_ws2021(Request $request){

        if(Auth::user()->roles->first()->name == "da-icts"){
            return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->where('province', '=', $request->province)
                ->where('total_farmers', '!=', 0)
                ->orderBy('municipality', 'ASC')
            )
            ->addColumn('total_beneficiaries', function($row){
                return number_format($row->total_farmers);       
            })
            ->addColumn('total_registered_area', function($row){
                return number_format($row->total_actual_area, '2', '.', ',');       
            })
            ->addColumn('total_estimated_area', function($row){
                return number_format($row->total_dist_area, '2', '.', ',');       
            })
            ->addColumn('total_bags_distributed', function($row){
                return number_format($row->total_bags);       
            })
            ->addColumn('total_male_count', function($row){
                return number_format($row->total_male);       
            })
            ->addColumn('total_female_count', function($row){
                return number_format($row->total_female);       
            })
            ->make(true);
        
        }else{
            return Datatables::of(DB::connection('rcep_reports_db')->table('lib_municipal_reports')
                ->where('province', '=', $request->province)
                ->where('total_farmers', '!=', 0)
                ->orderBy('municipality', 'ASC')
            )
            ->addColumn('action', function($row){
                //return "<a class='btn btn-success btn-sm' href='{{ route('rcef.report.excel.province', $row->province) }}'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                //$url = route('rcef.report.excel.municipality', ['province' => $row->province, 'municipality' => $row->municipality]);
                //return "<a class='btn btn-success btn-sm' href='".$url."'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
                return "<a class='btn btn-success btn-xs' data-province='$row->province' data-municipality='$row->municipality' data-toggle='modal' data-target='#confirm_export_municipality'><i class='fa fa-calendar'></i> GENERATE EXCEL</a>";
            })
            ->addColumn('total_beneficiaries', function($row){
                return number_format($row->total_farmers);       
            })
            ->addColumn('total_registered_area', function($row){
                return number_format($row->total_actual_area, '2', '.', ',');       
            })
            ->addColumn('total_estimated_area', function($row){
                return number_format($row->total_dist_area, '2', '.', ',');       
            })
            ->addColumn('total_bags_distributed', function($row){

                $distributed = "Regular distribution: ".number_format($row->total_bags);
                $province = $row->province;
                $municipality = $row->municipality;
                $eBinhi_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")->where("province", $province)->where("municipality", $municipality)->count("claimId");
                if($eBinhi_claim>0){
                    $distributed.= "<br> Binhi e-padala: ".number_format($eBinhi_claim);
                }

                return $distributed;



            })
            ->addColumn('accepted_transferred', function($row){
               // return number_format($row->total_bags);  
                $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    ->where('municipality', $row->municipality)
                    ->where('is_transferred', '!=', 1)
                    ->where('qrStart', '<=', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
                
                $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    ->where('municipality', $row->municipality)
                    ->where('is_transferred', 1)
                    //->where('qrStart', '<=', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');

                $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province', $row->province)
                    ->where('municipality', $row->municipality)
                    ->where('is_transferred', "!=",1)
                    ->where('qrStart', '>', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');  

                    $totalBags = 0;

                    $accepted_transferred = "Accepted:   ".number_format($accepted);
                    if(intval($transferred)>0){
                        $accepted_transferred .= " <br> Transfer: ".number_format($transferred);
                        /*
                        $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                        data-region = '".$row->region."'
                        data-province = '".$row->province."'
                        data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                        */
                        $totalBags += intval($transferred);
                    }

                     if(intval($ebinhi)>0){
                        $accepted_transferred .= " <br> e-Binhi: ".number_format($ebinhi);
                        /*
                        $linkBreakDown =  "<a href='#' data-toggle='modal' data-target='#show_breakdown_modal'
                        data-region = '".$row->region."'
                        data-province = '".$row->province."'
                        data-municipality = '".$row->municipality."'> ".$accepted_transferred." </a>";
                        */
                         $totalBags += intval($ebinhi);
                    }

                        if($totalBags>0){
                            $totalBags += intval($accepted);
                            $accepted_transferred .= "<br><b> Total: " . number_format($totalBags).'</b>';
                        }                        

                return $accepted_transferred;
               
            })




            ->addColumn('total_male_count', function($row){
                  //search

            $per_municipal= $this->area_range_counter("municipality", $row->region, $row->province, $row->municipality, "Male");
    
                /*$malecount = "<div> <strong> Male: ".number_format($row->total_male)."</strong> </div>";
                if(count($per_municipal)>0){

                        $malecount.= "<table border=0 width='100%'><tr> <td>  <b> (<= 0.5) </b>: ".$per_municipal[0]["area_p5"]."</td> 
                        <td align='left'> <b> (>0.5&<= 1) </b>: ".$per_municipal[0]["area_p5_1"]." </td> </tr> ";
                        $malecount.= "<tr> <td>  <b>  (>1&<= 1.5) </b>: ".$per_municipal[0]["area_1_1p5"]."</td> 
                        <td align='left'> <b> (>1.5&<= 2) </b>: ".$per_municipal[0]["area_1p5_2"]." </td> </tr> ";
                        $malecount.= "<tr> <td>  <b>  (>2&<= 2.5) </b>: ".$per_municipal[0]["area_2_2p5"]."</td> 
                        <td align='left'> <b> (>2.5&<= 3) </b>: ".$per_municipal[0]["area_2p5_3"]." </td> </tr> ";
                        $malecount.= "<tr> <td colspan=2 align='left'>  <b>  ( > 3) </b>: ".$per_municipal[0]["area_3"]."</td> 
                        </table>";       
                }*/
                
                $malecount = number_format($row->total_male);
                return $malecount;  
            })
            ->addColumn('total_female_count', function($row){

                $per_municipal= $this->area_range_counter("municipality", $row->region, $row->province, $row->municipality, "Female");
          
                /*$female_count = "<div> <strong> Female: ".number_format($row->total_female)."</strong> </div>";
                if(count($per_municipal)>0){
                        $female_count.= "<table border=0 width='100%'><tr> <td>  <b> (<= 0.5) </b>: ".$per_municipal[0]["area_p5"]."</td> 
                        <td align='left'> <b> (>0.5&<= 1) </b>: ".$per_municipal[0]["area_p5_1"]." </td> </tr> ";
                        $female_count.= "<tr> <td>  <b>  (>1&<= 1.5) </b>: ".$per_municipal[0]["area_1_1p5"]."</td> 
                        <td align='left'> <b> (>1.5&<= 2) </b>: ".$per_municipal[0]["area_1p5_2"]." </td> </tr> ";
                        $female_count.= "<tr> <td>  <b>  (>2&<= 2.5) </b>: ".$per_municipal[0]["area_2_2p5"]."</td> 
                        <td align='left'> <b> (>2.5&<= 3) </b>: ".$per_municipal[0]["area_2p5_3"]." </td> </tr> ";
                        $female_count.= "<tr> <td colspan=2 align='left'>  <b>  ( > 3) </b>: ".$per_municipal[0]["area_3"]."</td> 
                        </table>";     
                }*/
                $female_count = number_format($row->total_female);
                return $female_count;
            })
            ->addColumn('total_yield', function($row){
                return number_format($row->yield, '2', '.', ',');       
            })
            ->addColumn('total_area_claimed', function($row){
                $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->select("prv")
                        ->where('province', $row->province)
                        ->where('municipality', $row->municipality)
                        ->first();
                    $area_claimed = 0;
                if(count($prv)>0){
                    $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $area_claimed = DB::table($prv_db.".released")
                                ->where("province", $row->province)
                                ->where("municipality", $row->municipality)
                                ->sum("claimed_area");          
                }
                return number_format($area_claimed, '2', '.', ','); 

            })
            ->make(true);
        }
    }
    
    public function download_variety_report(Request $request){
        $excel = new Excel;

        $seed_data = DB::connection('rcep_reports_db')
            ->table('lib_variety_report')
            ->orderBy('province', 'municipality')
            ->get();

        $table_data = array();
        $total_seed_volume = 0;

        foreach ($seed_data as  $row) {
            $data = [
                'Region' => $row->region,
                'Province' => $row->province,
                'Municipality' => $row->municipality,
                'Seed Variety' => $row->seed_variety,
                'Total Volume (20kg/bag)' => number_format($row->total_volume)
            ];

            $total_seed_volume += $row->total_volume;
            array_push($table_data, $data);
        }
        $data2 = [
            'Region' => '',
            'Province' => '',
            'Municipality' => '',
            'Seed Variety' => 'TOTAL: ',
            'Total Volume (20kg/bag)' => number_format($total_seed_volume)
        ];
        array_push($table_data, $data2);

        $myFile = Excel::create('VARIETY REPORT', function($excel) use ($table_data) {
            $excel->sheet('SEED_VARITIES', function($sheet) use ($table_data) {
                $sheet->fromArray($table_data);
            });
        });

        $file_name = "SEED_VARIETY_REPORT"."_".date("Y-m-d H:i:s").".xlsx";


        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => $file_name,
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
    }


    public function check_prv_data_for_excel(Request $request){
        $region = $request->region;
        $province = $request->province;
        $per_part = 1000;

        $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('region', $region)->where('province', $province)->groupBy('province')->value('prv');
        $database = $GLOBALS['season_prefix']."prv_".substr($prv,0,4);

        $prv_volume = ceil(count(DB::table($database.".released")->get()) / $per_part);

        return $prv_volume;
    }



    /**
     * NEW CODE - 09/04/2020
     */

    

    

    public function convert_to_excel_province_listDependent_v1($province){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {
            $table_name = "tbl_".substr(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province)->value('prv'), 0, 4);
            \Config::set('database.connections.reports_db.database', $GLOBALS['season_prefix'].'rcep_excel');
            DB::purge('reports_db');

            $table_name = $table_name;
            $primary_key = 'id';
            $fields = [
                ['name' => 'rsbsa_control_number', 'type' => 'string', 'limit' => '100'],
                ['name' => 'qr_code', 'type' => 'string', 'limit' => '100'],
                ['name' => 'farmer_fname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'farmer_mname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'farmer_lname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'farmer_ext', 'type' => 'string', 'limit' => '255'],
                ['name' => 'sex', 'type' => 'string', 'limit' => '6'],
                ['name' => 'birthdate', 'type' => 'string', 'limit' => '100'],
                ['name' => 'tel_number', 'type' => 'string', 'limit' => '100'],
                ['name' => 'province', 'type' => 'string', 'limit' => '100'],
                ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
                ['name' => 'mother_fname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'mother_mname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'mother_lname', 'type' => 'string', 'limit' => '255'],
                ['name' => 'mother_ext', 'type' => 'string', 'limit' => '255'],
                ['name' => 'dist_area', 'type' => 'float', 'limit' => '10'],
                ['name' => 'actual_area', 'type' => 'float', 'limit' => '10'],
                ['name' => 'bags', 'type' => 'integer', 'limit' => '10'],
                ['name' => 'seed_variety', 'type' => 'string', 'limit' => '100'],
                ['name' => 'date_released', 'type' => 'string', 'limit' => '100'],  
                ['name' => 'farmer_id', 'type' => 'string', 'limit' => '100'],
                ['name' => 'released_by', 'type' => 'text'],
                ['name' => 'date_generated', 'type' => 'timestamp'],   
            ];
            $this->createTable($table_name, $fields, $primary_key);


            /**
             * PROCESS DATA - pending data (START)
             */
            $database = $GLOBALS['season_prefix']."prv_".substr(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province)->value('prv'), 0, 4);
            $table_conn = $this->set_database($database);

            if($table_conn == "Connection Established!"){
                $prv_dist_data = DB::table($database.".released")->first();
                if(count($prv_dist_data) > 0){
                    
                    $province_farmer_list = DB::connection('reports_db')->table("released")
                        ->select('released.province', 'released.municipality', 'released.seed_variety', 
                                'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                                'released.farmer_id', 'released.released_by', 'released.release_id')
                        ->where('released.bags', '!=', '0')
                        ->where('released.province', '=', $province)
                        ->where('released.is_processed', 1)
                        ->orderBy('released.province', 'ASC')
                        ->get();

                    foreach ($province_farmer_list as  $row) {

                        //check other_info table
                        $other_info_data = DB::connection('reports_db')->table("other_info")
                            ->where('farmer_id', $row->farmer_id)
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->first();

                        if(count($other_info_data) > 0){
                            $birthdate = $other_info_data->birthdate;
                            $mother_fname = $other_info_data->mother_fname;
                            $mother_mname = $other_info_data->mother_mname;
                            $mother_lname = $other_info_data->mother_lname;
                            $mother_suffix = $other_info_data->mother_suffix;

                            if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                                $phone_number = "";
                            }else{
                                $phone_number = $other_info_data->phone;
                            }
                        }else{
                            $birthdate = '';
                            $mother_fname = '';
                            $mother_mname = '';
                            $mother_lname = '';
                            $mother_suffix = '';
                            $phone_number = '';
                        }

                        //get farmer_profile
                        $farmer_profile = DB::connection('reports_db')->table("farmer_profile")
                            ->where('rsbsa_control_no', $row->rsbsa_control_no)
                            ->where('farmerID', $row->farmer_id)
                            ->where('lastName', '!=', '')
                            ->where('firstName', '!=', '')
                            ->orderBy('farmerID')
                            ->first();

                        if(count($farmer_profile) > 0){
                            $qr_code = $farmer_profile->distributionID;
                            $farmer_fname = $farmer_profile->firstName;
                            $farmer_mname = $farmer_profile->midName;
                            $farmer_lname = $farmer_profile->lastName;
                            $farmer_extname = $farmer_profile->extName;
                            $dist_area = $farmer_profile->area;
                            $actual_area = $farmer_profile->actual_area;
                            $sex = $farmer_profile->sex == 'Femal' ? 'Female' : $farmer_profile->sex;
                        }else{
                            $qr_code = "N/A";
                            $farmer_fname = "N/A";
                            $farmer_mname = "N/A";
                            $farmer_lname = "N/A";
                            $farmer_extname = "N/A";
                            $dist_area = 0;
                            $actual_area = 0;
                            $sex = "N/A";
                        }

                        //get name of encoder using released.by in sdms_db_dev
                        $encoder_name = DB::connection('mysql')->table('users')->where('username', $row->released_by)->first();
                        if(count($encoder_name) > 0){
                            if($encoder_name->middleName == ''){
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }else{
                                $encoder_name = $encoder_name->firstName." ".$encoder_name->middleName." ".$encoder_name->lastName." ".$encoder_name->extName;
                            }
                        }else{
                            $encoder_name = '';
                        }


                        $data = [
                            'rsbsa_control_number' => $row->rsbsa_control_no,
                            'qr_code' => $qr_code,
                            "farmer_fname" => $farmer_fname,
                            "farmer_mname" => $farmer_mname,
                            "farmer_lname" => $farmer_lname,
                            "farmer_ext" => $farmer_extname,
                            'sex' => $sex,
                            'birthdate' => $birthdate,
                            'tel_number' => $phone_number,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            "mother_fname" => $mother_fname,
                            "mother_mname" => $mother_mname,
                            "mother_lname" => $mother_lname,
                            "mother_ext" => $mother_suffix,
                            'dist_area' => $dist_area,
                            'actual_area' => $actual_area,
                            'bags' => $row->bags,
                            'seed_variety' => $row->seed_variety,
                            'date_released' => $row->date_released,
                            'farmer_id' => $row->farmer_id,
                            'released_by' => $encoder_name
                        ];
                        DB::table($GLOBALS['season_prefix']."rcep_excel.$table_name")->insert($data);

                        //after processing to seed beneficiary list DB update is_processed flag to 0
                        DB::table($database.'.released')->where('release_id', $row->release_id)->update([
                            'is_processed' => 0
                        ]);

                        DB::commit();
                    }

                }
                 /**
                 * PROCESS DATA - pending data (END)
                 */

                
                /**
                 * CONVERT TO EXCEL (START)
                */
                $province_data = json_decode(
                                    json_encode(
                                        DB::table($GLOBALS['season_prefix']."rcep_excel.$table_name")
                                            ->select('rsbsa_control_number', 'qr_code', 'farmer_fname', 'farmer_mname', 'farmer_lname',
                                                'farmer_ext', 'sex', 'birthdate', 'tel_number', 'province', 'municipality', 'mother_fname',
                                                'mother_mname', 'mother_lname', 'mother_ext', 'dist_area', 'actual_area', 'bags', 'seed_variety',
                                                'date_released', 'farmer_id', 'released_by')    
                                            ->get()
                                    ), 
                                true);

                return Excel::create("$province"."_".date("Y-m-d g:i A"), function($excel) use ($province_data) {
                    $excel->sheet("BENEFICIARY LIST", function($sheet) use ($province_data) {
                        
                        /*$sheet->fromArray($province_data, null, 'A1', false, false);
                        $sheet->prependRow(1, array(
                            'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
                            "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                            'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
                            "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
                            'Actual Area', 'Bags', 'Seed Variety', 'Date Released', 'Farmer ID', 'Released By'
                        ));
                        $sheet->freezeFirstRow();*/


                    });
                })->download('xlsx');
                /**
                  * CONVERT TO EXCEL (END)
                  */
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }
    }
    
    /**
     * 11-03-2020
     */
    public function nrp_provinces(Request $request){
         $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->groupBy('province')
            ->orderBy('region_sort', 'ASC')
            //->orderBy('province')
            ->get();

        $return_str= '';
        foreach($provinces as $province){
            $prv_name  = $GLOBALS['season_prefix']."prv_".substr($province->prv, 0, 4);
            $nrp_count = count(DB::table($prv_name.".nrp_profile")->get());
            if($nrp_count > 0){
                $return_str .= "<option value='$prv_name'>$province->province</option>";
            }   
        }
        return $return_str;
    }
    
    /**
     * 11-05-2020
     */
    public function areaRange_home(Request $request){
        return view('reports.area_range.home');
    }

    public function areaRange_municipalTBL(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province', 'municipality'))
        ->addColumn('total_beneficiarries', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            return count(DB::table($database.".".$table)->get());
        })
        ->addColumn('one_hectare_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_one_hectare = count(DB::table($database.".".$table)->where('actual_area', '<=', 1)->get());

            if($total_one_hectare > 0){
                $equivalent_percentage = ($total_one_hectare / $total_beneficiaries) *  100;
                return $total_one_hectare." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_one_hectare;
            }
            
        })
        ->addColumn('two_hectare_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_two_hectare = count(DB::table($database.".".$table)
                ->where('actual_area', '>', 1)
                ->where('actual_area', '<=', 2)
                ->get());

            if($total_two_hectare > 0){
                $equivalent_percentage = ($total_two_hectare / $total_beneficiaries) *  100;
                return $total_two_hectare." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_two_hectare;
            }
        })
        ->addColumn('three_hectare_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_three_hectare = count(DB::table($database.".".$table)
                ->where('actual_area', '>', 2)
                ->where('actual_area', '<=', 3)
                ->get());

            if($total_three_hectare > 0){
                $equivalent_percentage = ($total_three_hectare / $total_beneficiaries) *  100;
                return $total_three_hectare." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_three_hectare;
            }
        })
        ->addColumn('last_col', function($row){
            $database = "rpt_".substr($row->prv, 0, 4);
            $table = "tbl_".$row->prv;

            $total_beneficiaries = count(DB::table($database.".".$table)->get());
            $total_last = count(DB::table($database.".".$table)->where('actual_area', '>', 3)->get());

            if($total_last > 0){
                $equivalent_percentage = ($total_last / $total_beneficiaries) *  100;
                return $total_last." (".number_format($equivalent_percentage,"2",".",",")."%)";
            }else{
                return $total_last;
            }
        })
        ->make(true);
    }




    public function area_range_counter($groupBy, $region, $province, $municipality, $gender){

        $prvlist= DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("regionName", $region)
                ->where("province", "LIKE", "%".$province."%")
                ->where("municipality", "LIKE", "%".$municipality."%")
                ->groupBy("municipality")
                ->orderBy("prv", "ASC")
                ->get();
          //  dd($prvlist);
//(1) <=1 (2) <=2 (3) <=3 (4) >3
                
                $area_p5 = 0;
                $area_p5_1 = 0;
                $area_1_1p5=0;
                $area_1p5_2 =0;
                $area_2_2p5=0;
                $area_2p5_3 =0;
                $area_3 = 0;

                $range = array();
            foreach ($prvlist as $prv) {

              
                $rpt = "rpt_".substr($prv->prv,0,4);     
                   $schema = DB::table('information_schema.TABLES')
                        ->where("TABLE_SCHEMA", $rpt)
                        ->where("TABLE_NAME", "tbl_".$prv->prv)
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }

                if($groupBy != ""){
                $area_p5 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", "<=",.5)
                        ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");

                $area_p5_1 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", .5)
                        ->where("actual_area", "<=", 1)
                        ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");

                $area_1_1p5 += DB::table($rpt.".tbl_".$prv->prv)
                         ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 1)
                        ->where("actual_area", "<=",1.5)
                         ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");

                $area_1p5_2 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 1.5)
                        ->where("actual_area", "<=",2)
                        ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");
                
                $area_2_2p5 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 2)
                        ->where("actual_area", "<=",2.5)
                        ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");

                $area_2p5_3 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 2.5)
                        ->where("actual_area", "<=",3)
                        ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");

                $area_3 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 3)
                        ->where("sex", $gender)
                        ->groupBy($groupBy)
                        ->count("actual_area");    
                }else{
                
                $area_p5 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", "<=",.5)
                        ->where("sex", $gender)
                        ->count("actual_area");

                $area_p5_1 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", .5)
                        ->where("actual_area", "<=", 1)
                        ->where("sex", $gender)
                        ->count("actual_area");

                $area_1_1p5 += DB::table($rpt.".tbl_".$prv->prv)
                         ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 1)
                        ->where("actual_area", "<=",1.5)
                         ->where("sex", $gender)
                        ->count("actual_area");

                $area_1p5_2 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 1.5)
                        ->where("actual_area", "<=",2)
                        ->where("sex", $gender)
                        ->count("actual_area");
                
                $area_2_2p5 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 2)
                        ->where("actual_area", "<=",2.5)
                        ->where("sex", $gender)
                        ->count("actual_area");

                $area_2p5_3 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 2.5)
                        ->where("actual_area", "<=",3)
                        ->where("sex", $gender)
                        ->count("actual_area");

                $area_3 += DB::table($rpt.".tbl_".$prv->prv)
                        ->where("province", "LIKE", "%".$province."%")
                        ->where("municipality", "LIKE", "%".$municipality."%")
                        ->where("actual_area", ">", 3)
                        ->where("sex", $gender)
                        ->count("actual_area");




                }

                

                               
            }

            array_push($range,array(
                "area_p5"  => $area_p5,
                "area_p5_1" => $area_p5_1,
                "area_1_1p5" =>$area_1_1p5,
                "area_1p5_2" =>$area_1p5_2,
                "area_2_2p5" =>$area_2_2p5,
                "area_2p5_3" =>$area_2p5_3,
                "area_3" =>$area_3,
                )); 

            //return null;
            return $range;
    }   






























    /** END-- */
}

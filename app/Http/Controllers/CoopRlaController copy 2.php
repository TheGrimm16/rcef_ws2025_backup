<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;
use Redirect;
use Http;

class CoopRlaController extends Controller
{

  public function home(){
    $tbl_arr = array();
   

        $stream_opts = [
          "ssl" => [
              "verify_peer"=>false,
              "verify_peer_name"=>false,
          ]
        ];  
  
    $response = file_get_contents("https://rsis.philrice.gov.ph/api_management/rcef_lab_results",
                 false, stream_context_create($stream_opts));

    $tmp_data = json_decode($response, true);


    foreach ($tmp_data as $value) {

      array_push($tbl_arr, array(
        "Accreditation_Number" => $value['GrowerAccreNum'],
        "Name" => $value ['SeedGrower'],
        "Variety" => $value ['Variety'],
        "Lot_No" => $value ['LotNo'],
        "Lab_No" => $value ['LabNo'],
        "Date_Sampled" => $value ['DateSampled'],
        "Lab_Status" => $value ['LabStatus'],
        "Lab_Result" => $value ['LabResult'],
        "Date_Test_Completed" => $value ['DateTestCompleted'],
        "Num_of_Bags_Passed" => $value['NumOfBagsPassed'],
        "Seed_Class" => $value ['SeedClass']
        // "Affiliation" => $value ['Affiliation']
        )
      );   

    } 

        try {
          DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_nsqcs2')->truncate();
          
          DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_nsqcs2')
                            ->insert($tbl_arr);
        
        } catch (\Throwable $th) {
          DB::rollback();
        return $th;
        }


        $report = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_nsqcs2')
          ->orderby('Accreditation_Number')          
          ->get();

          // $tmp = json_decode($report, true);
        $tmp = json_decode(json_encode($report), true);
        $coopName2 =  $tmp[0]['Affiliation'];
        
        $tbl_arr_sheet = array();
        $tbl_arr_sheet_tmp = array();
        $coopName1 = "";
        $i = 0;
        foreach ($tmp as $val) {
          unset($val['id']);
          if($val["Affiliation"] == ""){
            $val["Affiliation"] = str_replace("/","&",$val["Accreditation_Number"]);
          }

          if($coopName1 != $val["Affiliation"]){

            if($coopName1 != ""){
                if(count($tbl_arr_sheet_tmp)>0){
                  $tbl_arr_sheet[$coopName1] = $tbl_arr_sheet_tmp;
                }
            }

            $coopName1 = $val["Affiliation"];
            $tbl_arr_sheet_tmp = array();
               array_push($tbl_arr_sheet_tmp,$val);
          }else{
              array_push($tbl_arr_sheet_tmp,$val);
          }
         
        }
  
      $excel_data = json_decode(json_encode($tbl_arr_sheet), true); //convert collection to associative array to be converted to excel
      return Excel::create("RLA_nsqcs_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
         foreach($excel_data as $key=> $dt){
          $excel->sheet( substr($key,0,31), function($sheet) use ($dt) {
            $sheet->fromArray($dt);
            $sheet->freezeFirstRow();
            
        });
         }
        
        
      
      })->download('xlsx');



   
  }

       
    
}

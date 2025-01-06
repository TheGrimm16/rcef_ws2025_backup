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
USE Datatables;
class CoopRlaController extends Controller
{

  public function viewCoopRLA(Request $request){
    $data = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
        ->select("*", DB::raw("CONCAT(LabStatus,' (',LabResult,')') as lab_result"))  
        ->where("CoopAccreNum", "!=", "")
          ->where("CoopAccreNum", "LIKE", '%'.$request->account)
          ->orderBy("SeedGrower")
          ->get();


    $data = collect($data);

      return Datatables::of($data)->make(true);
  }

  public function getRsisRLA(Request $request){
    $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
      ->where("regionId", $request->region)
      ->orderBy("coopName")
      ->get();
      
      $data_array = array();
      foreach($coop_list as $coop){
        //14


          $data = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
              ->select(DB::raw("SUM(BagsReceived) as total_bags"),DB::raw("SUM(NumOfBagsPassed) as total_passed"),DB::raw("SUM(NumOfBagsRejected) as total_rejected") )
              ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
              ->first();

              if($data != null){
                $passed = $data->total_passed;
                $rejected = $data->total_rejected;
                $pending =$data->total_bags - ($data->total_passed + $data->total_rejected);
                
              }else{
                $passed = 0;
                $rejected = 0;
                $pending = 0;
              }

              if($passed==null){$passed=0;}
              if($rejected==null){$rejected=0;}
              if($pending==null){$pending=0;}
            
              if($passed == 0 && $rejected == 0 && $pending ==0){

              }else{
                $coop_arr = array(
                  "account_no" => substr($coop->accreditation_no,14,20),
                  "coop_name" => $coop->coopName,
                  "coop_accre" => $coop->accreditation_no,
                  "moa_number" => $coop->current_moa,
                  "passed" => number_format($passed),
                  "rejected" => number_format($rejected),
                  "pending" => number_format($pending)
              );
        
          array_push($data_array, $coop_arr);
              }

            



      }


   
       

  return $data_array;

  }


  public function rsis_rla_dashboard(){
     $sg_list =  0;

       $synced =  DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
            ->where("CoopAccreNum", "!=", "")
            ->groupBy("CoopAccreNum")
            ->value("date_updated");
      

      $rejection_data = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
          ->select(DB::raw("SUM(BagsReceived) as total_bags"), DB::raw("SUM(NumOfBagsRejected) as total_rejected"))
          ->where("CoopAccreNum", "!=", "")
          ->first();
          if($rejection_data != null){
            try{
              $rejection_rate = ($rejection_data->total_rejected / $rejection_data->total_bags) * 100;
            }catch(\Exception $e)
            {
              $rejection_rate = 0;
            }
              $rejection_rate = number_format($rejection_rate,2);
          }
      
        $total_coops = count(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        ->where("isActive", "1")
        ->orderBy("coopName")
        ->get());


      $regions = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_regions")
        ->where("id", "!=", 14)
          ->orderby("order")
          ->get();
        foreach($regions as $key=> $reg){
          $check_data = 0;
              $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
              ->where("regionId", $reg->id)
              ->orderBy("coopName")
              ->get();
                foreach($coop_list as $data_coop){
                 $result_count =  count(DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
                      ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($data_coop->accreditation_no,14,20))
                      ->get());
                      if($result_count > 0){
                        $check_data = 1;
                        $sg_list++;
                      }
                }

            if($check_data == 1){
                $regions[$key]->with_data = "true";
               
            }else{
              $regions[$key]->with_data = "false";
              
            }
              

        }

          return view("rsis.dashboard")
            ->with("rejection_rate", $rejection_rate)
            ->with("sg_list", $sg_list)
            ->with("total_coops", $total_coops)
            ->with("rejection_data", $rejection_data)
            ->with("regions", $regions)
            ->with("synced_data", $synced);
            



  }

  public function exportRSIS($account){
    $data = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
          ->select('SeedGrower', 'GrowerAccreNum', 'CoopName', 'CoopAccreNum', 'CoopRegion', 'Variety', 'LotNo', 'LabNo', 'DateSampled', 'BagsReceived', 'BagWeight', 'LabReceivedDate', 'LabStatus', 'LabResult', 'DateTestCompleted', 'NumOfBagsPassed', 'NumOfBagsRejected', 'CauseOfReject', 'SeedClass', 'date_updated as date_synced')
          ->where("CoopAccreNum", "like", "%".$account)
          ->orderBy("SeedGrower")
          ->get();
    $excel_data = json_decode(json_encode($data), true);
        
          return Excel::create("RSIS_RLA"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
            $excel->sheet("Lab Result", function($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data);
                $sheet->freezeFirstRow();
            }); 
        })->download('xlsx');
        
  }


  public function exportSeedProduction($account){
    $sgData =  DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
    ->where("CoopAccreNum", "like", "%".$account)
    ->groupBy('SeedGrower','Variety','Area') 
    ->get();

    
    $data_array = array();
    $counter = 0;
    foreach ($sgData as $key => $value) {      
      $tbl_cooperatives =  DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where("accreditation_no", "like", "%".$account)->first();
      $counter++;  
      $cutter1 = 4;
      $cutter2 = 6;

      $expiriDataTmp = explode("/",str_replace("-","",substr($value->GrowerAccreNum,$cutter1,$cutter2)));

      $date=date_create("20".$expiriDataTmp[1]."-".$expiriDataTmp[0]."");
     $wet ="";
     $dry = "" ;
     $weekOfmonth = array(
      1 => "1st WeeK",
      2 => "2nd Week",
      3 => "3rd Week",
      4 => "4th Week"
 );
 
     
      if(substr($GLOBALS['season_prefix'],0,2) == "ws"){
        $wet =$weekOfmonth[$value->ExpectedPlantingWeek];
      }else{
        $dry = $weekOfmonth[$value->ExpectedPlantingWeek] ;
      } 
     $tbl_sg_list =  DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_sg_list')->where("GrowerName", "like", "%".$value->SeedGrower."%")->first();
      
      $coop_arr = array(
        "no" => $counter,
        "NameOfMember" => $value->SeedGrower,
        "coop_accre" => $value->GrowerAccreNum,
        "expriDate" =>  date_format($date,"Y/M"),
        "full_address" => $tbl_cooperatives->full_address,       
        "dry" => $dry,       
        "wet" => $wet,       
        "toalAreaAccred" => $tbl_sg_list->AccreditedArea,       
        "areaCommitedRcef" => $value->Area,       
        "rsPhilrice" => "#NA",       
        "rsDa" => "#NA",       
        "area" => $value->Variety,       
      );
      array_push($data_array, $coop_arr);
    }
/*   return   $data = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
          ->select('SeedGrower', 'GrowerAccreNum', 'CoopName', 'CoopAccreNum', 'CoopRegion', 'Variety', 'LotNo', 'LabNo', 'DateSampled', 'BagsReceived', 'BagWeight', 'LabReceivedDate', 'LabStatus', 'LabResult', 'DateTestCompleted', 'NumOfBagsPassed', 'NumOfBagsRejected', 'CauseOfReject', 'SeedClass', 'date_updated as date_synced')
          ->where("CoopAccreNum", "like", "%".$account)
          ->orderBy("SeedGrower")
          ->get(); */
    $excel_data = json_decode(json_encode($data_array), true);
        
          return Excel::create("RSIS_RLA"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
            $excel->sheet("Lab Result", function($sheet) use ($excel_data) {

              $sheet->getStyle('A:K')->getAlignment()->setWrapText(true);
              $sheet->mergeCells('A8:A11');
                $sheet->setWidth('A', 7);

                $sheet->setCellValue('A8', 'No.');
              $sheet->cells('A8:A11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});


              $sheet->mergeCells('B8:B11');
                $sheet->setWidth('B', 40.70);
                $sheet->setCellValue('B8', 'Name of Member');
                $sheet->cells('B8:B11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});


                $sheet->mergeCells('C8:C11');
                $sheet->setWidth('C', 38.70);
                $sheet->setCellValue('C8', 'CPI-NSQCS Accreditation Number');
                $sheet->cells('C8:C11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});

                $sheet->mergeCells('D8:D11');
                $sheet->setWidth('D', 31.70);
                $sheet->setCellValue('D8', 'Expiration Date of Accreditation');
                $sheet->cells('D8:D11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});


                $sheet->mergeCells('E8:E11');
                $sheet->setWidth('E', 33.70);
                $sheet->setCellValue('E8', 'Address of Seed Production Area');
                $sheet->cells('E8:E11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});
              

                $sheet->mergeCells('F8:G9');                              
                $sheet->setWidth('F', 20);
                $sheet->setWidth('G', 20);
                $sheet->setHeight(8, 30);
                $sheet->setHeight(9, 30);
                $sheet->setCellValue('F8', 'Schedule of Planting (week & month)');
                $sheet->cells('F8:G8', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});

                $sheet->mergeCells('F10:F11');
                $sheet->setCellValue('F10', 'Dry Season');
                $sheet->cells('F10:F11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});

                $sheet->mergeCells('G10:G11');
                $sheet->setCellValue('G10', 'Wet Season');
                $sheet->cells('G10:G11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});
                
                $sheet->mergeCells('H8:H11');
                $sheet->setWidth('H', 20);
                $sheet->setCellValue('H8', 'Total Area Accredited (ha)');
                $sheet->cells('H8:H11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});
              
                $sheet->mergeCells('I8:I11');
                $sheet->setWidth('I', 19);
                $sheet->setCellValue('I8', 'Total Area Committed to RCEF (ha)');
                $sheet->cells('I8:I11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});
              

                
                $sheet->mergeCells('J8:K8');                              
                $sheet->setWidth('J', 10);
                $sheet->setWidth('K', 11);
                $sheet->setCellValue('J8', 'Seed Source of RS');
                $sheet->cells('J8:K8', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri');$cells->setFontWeight('bold'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});

                $sheet->mergeCells('J9:J11');
                $sheet->setCellValue('J9', 'Philrice');
                $sheet->cells('J9:J11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});

                $sheet->mergeCells('K9:k11');
                $sheet->setCellValue('K9', 'DA-Accredited (DA, Seed Growers, SCUs)');
                $sheet->cells('K9:k11', function($cells) { $cells->setBorder('thin','thin','thin','thin');$cells->setBackground('#FCEEA7'); $cells->setFontFamily('Calibri'); $cells->setFontSize(12);$cells->setAlignment('center');$cells->setValignment('center');});
                

                /* $sheet->fromArray($excel_data); */
                $sheet->fromArray($excel_data, null, 'A12', false, false);
                $sheet->freezeFirstRow();
            }); 
        })->download('xlsx');
        
  }


  public function rsis_rla_puller(){
    $tbl_arr = array();
   

        $stream_opts = [
          "ssl" => [
              "verify_peer"=>false,
              "verify_peer_name"=>false,
          ]
        ];  
  
    $response = file_get_contents("https://stagingdev.philrice.gov.ph/rsis/api_management/rcef_lab_results",
    // $response = file_get_contents("https://rsis.philrice.gov.ph/api_management/rcef_lab_results",
                 false, stream_context_create($stream_opts));

    $tmp_data = json_decode($response, true);

    // dd($tmp_data);
    // https://rsis.philrice.gov.ph/api_management/rcef_sg_list
    // https://rsis.philrice.gov.ph/api_management/rcef_applied_seed_cert

    try {
      DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')->truncate();


      foreach($tmp_data as $key=> $data){
        if(count($data["serial_number"])==0) $data["serial_number"] = "";
        if(count($data["variety"])==0) $data["variety"] = "";
        if(count($data["lot_no"])==0) $data["lot_no"] = "";
        if(count($data["lab_no"])==0) $data["lab_no"] = "";
        if(count($data["date_sampled"])==0) $data["date_sampled"] = "";
        if(count($data["bags_received"])==0) $data["bags_received"] = "";
        if(count($data["bag_weight"])==0) $data["bag_weight"] = "";
        if(count($data["lab_received_date"])==0) $data["lab_received_date"] = "";
        if(count($data["lab_status"])==0) $data["lab_status"] = "";
        if(count($data["lab_result"])==0) $data["lab_result"] = "";
        if(count($data["date_test_completed"])==0) $data["date_test_completed"] = "";
        if(count($data["seed_class"])==0) $data["seed_class"] = "";
        if(count($data["num_of_bags_pass"])==0) $data["num_of_bags_pass"] = "";
        if(count($data["num_of_bags_reject"])==0) $data["num_of_bags_reject"] = "";
        if(count($data["cause_of_reject"])==0) $data["cause_of_reject"] = "";
        if(count($data["growapp_tracking_id"])==0) $data["growapp_tracking_id"] = "";

        // dd($data);
        $sgProfile = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_sg_list')
        ->where('SerialNum',$data["serial_number"])
        ->first();
        if(!$sgProfile)
        {
          continue;
        }
        else
        {
          if(strlen($sgProfile->CoopAccreNum)>10)
          {
            $accred = substr($sgProfile->CoopAccreNum,-5);
            // dd($accred);
            $checkCoopAccred = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('accreditation_no','LIKE','%'.$accred)
            ->first();
            if($checkCoopAccred)
            {
              dd($sgProfile->GrowerName,$sgProfile->CoopAccreNum,$checkCoopAccred);
              
            }
          }


        }
        

        $ins= array(
        "SeedGrower" => $sgProfile->GrowerName,
        "GrowerAccreNum" => $sgProfile->AccreNum,
        "CoopName" => $sgProfile->CoopName,
        "CoopAccreNum" => $sgProfile->CoopAccreNum,
        "CoopRegion" => $sgProfile->Region,
        "Variety" => $data["variety"],
        "LotNo" => $data["lot_no"],
        "LabNo" => $data["lab_no"],
        "DateSampled" => $data["date_sampled"],
        "BagsReceived" => $data["bags_received"],
        "BagWeight" => $data["bag_weight"],
        "LabReceivedDate" => $data["lab_received_date"],
        "LabStatus" => $data["lab_status"],
        "LabResult" => $data["lab_result"],
        "DateTestCompleted" => $data["date_test_completed"],
        "NumOfBagsPassed" => $data["num_of_bags_pass"],
        "NumOfBagsRejected" => $data["num_of_bags_reject"],
        "CauseOfReject" => $data["cause_of_reject"],
        "SeedClass" => $data["seed_class"],
        );

        DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
        ->insert($ins);
      }
      return json_encode("Synced Date : ".date("F j, Y (g:i a)"));
    } catch (\Throwable $th) {
 
    return $th;
    }


  }

  public function rsis_sg_list(){
    
    
    $tbl_arr = array();
    $stream_opts = [
      "ssl" => [
          "verify_peer"=>false,
          "verify_peer_name"=>false,
      ]
    ];  

      $response = file_get_contents("https://stagingdev.philrice.gov.ph/rsis/api_management/rcef_sg_list",
                  false, stream_context_create($stream_opts));

      $tmp_data = json_decode($response, true);

      // https://rsis.philrice.gov.ph/api_management/rcef_rs_distribution

      try {
        DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_sg_list')->truncate();

        foreach($tmp_data as $key=> $data){
          

          $batch_no = 1;
          $ins_arr = array(
            "SerialNum" => $data["SerialNum"],
            "GrowerName" => $data["GrowerName"],
            "AccreNum" => $data["AccreNum"],
            "CoopName" => $data["CoopName"],
            "CoopAccreNum" => $data["CoopAccreNum"],
            "AccreditedArea" => $data["AccreditedArea"],
            "Region" => $data["Region"],
            "Province" => $data["Province"],
            "City" => $data["City"],
          );

          DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_sg_list')
          ->insert($ins_arr);

        }
        return json_encode("Synced Date : ".date("F j, Y (g:i a)"));


      } catch (\Throwable $th) {

      return $th;
      }

  
  }



  public function sg_applied(){
    
    $tbl_arr = array();
    $stream_opts = [
      "ssl" => [
          "verify_peer"=>false,
          "verify_peer_name"=>false,
      ]
    ];  

      $response = file_get_contents("https://rsis.philrice.gov.ph/api_management/rcef_applied_seed_cert",
                  false, stream_context_create($stream_opts));

      $tmp_data = json_decode($response, true);

      // https://rsis.philrice.gov.ph/api_management/rcef_rs_distribution

      try {
        DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_seed_applied')->truncate();

        foreach($tmp_data as $key=> $data){
          

          $batch_no = 1;
          $ins_arr = array(
            "SeedGrower" => $data["SeedGrower"],
            "GrowerAccreNum" => $data["GrowerAccreNum"],
            "Variety" => $data["Variety"],
            "SeedClass" => $data["SeedClass"],
            "LabNo" => $data["LabNo"],
            "LotNo" => $data["LotNo"],
            "Sitio" => $data["Sitio"],
            "Barangay" => $data["Barangay"],
            "Municipality" => $data["Municipality"],
            "Province" => $data["Province"],
            "AreaPlanted" => $data["AreaPlanted"],
            "CroppingYear" => $data["CroppingYear"],
            "Sem" => $data["Sem"],
            "SowingDate" => $data["SowingDate"],
            "DatePlanted" => $data["DatePlanted"],
            "DateCollected" => $data["DateCollected"],
            "DateReceived" => $data["DateReceived"],
          );

          DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_seed_applied')
          ->insert($ins_arr);

        }
        return json_encode("Synced Date : ".date("F j, Y (g:i a)"));


      } catch (\Throwable $th) {

      return $th;
      }

  }     



  //--------------------

  public function sg_distri_api(){
              $tbl_arr = array();
                    $stream_opts = [
                      "ssl" => [
                          "verify_peer"=>false,
                          "verify_peer_name"=>false,
                      ]
                    ];  
              
                $response = file_get_contents("https://rsis.philrice.gov.ph/api_management/rcef_rs_distribution",
                            false, stream_context_create($stream_opts));

                $tmp_data = json_decode($response, true);
              
                // https://rsis.philrice.gov.ph/api_management/rcef_rs_distribution

                try {
                  DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')->truncate();

                  foreach($tmp_data as $key=> $data){
                    if(count($data["CoopName"])==0) $data["CoopName"] = "";
                    if(count($data["CoopAccreNum"])==0) $data["CoopAccreNum"] = "";
                    if(count($data["SeedGrower"])==0) $data["SeedGrower"] = "";
                    if(count($data["GrowerAccreNum"])==0) $data["GrowerAccreNum"] = "";
                    if(count($data["Variety"])==0) $data["Variety"] = "";
                    if(count($data["Kilograms"])==0) $data["Kilograms"] = "";
                    if(count($data["LabNo"])==0) $data["LabNo"] = "";
                    if(count($data["LotNo"])==0) $data["LotNo"] = "";
                    if(count($data["Area"])==0) $data["Area"] = "";
                    if(count($data["DateTimeReleased"])==0) $data["DateTimeReleased"] = "";
                    if(count($data["ExpectedPlantingMonth"])==0) $data["ExpectedPlantingMonth"] = "";
                    if(count($data["ExpectedPlantingWeek"])==0) $data["ExpectedPlantingWeek"] = "";

                    $batch_no = 1;
               
                    $ins= array(
                    "CoopName" => $data["CoopName"],
                    "CoopAccreNum" => $data["CoopAccreNum"],
                    "SeedGrower" => $data["SeedGrower"],
                    "GrowerAccreNum" => $data["GrowerAccreNum"],
                    "Variety" => $data["Variety"],
                    "Kilograms" => $data["Kilograms"],
                    "LabNo" => $data["LabNo"],
                    "LotNo" => $data["LotNo"],
                    "Area" => $data["Area"],
                    "DateTimeReleased" => $data["DateTimeReleased"],
                    "ExpectedPlantingMonth" => $data["ExpectedPlantingMonth"],
                    "ExpectedPlantingWeek" => $data["ExpectedPlantingWeek"],
                    "batch" => $batch_no
                    );

                    DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                    ->insert($ins);

                  }

                
                  return json_encode("Synced Date : ".date("F j, Y (g:i a)"));

                
                } catch (\Throwable $th) {
            
                return $th;
                }
  }

  public function rs_distri_dashboard(){

                $sg_list =  0;
                $week_arr = array(
                  0 => "N/A",
                  1 => "1st week of",
                  2 => "2nd week of",
                  3 => "3rd week of",
                  4 => "4th week of",
                  5 => "5th week of"
                );

                $synced =  DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                    ->where("CoopAccreNum", "!=", "")
                    ->where("batch", 1)
                    ->groupBy("CoopAccreNum")
                    
                    ->value("date_updated");
              

            

                  for($x=1; $x<13; $x++)
                  {
                    $check_month = $x;
                    if($x < 10){
                      $check_month = "0".$x;
                    }
                    $current_month = date("M", strtotime(date("Y-".$check_month."-d")));

                      $earliest_month = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                        ->where("CoopAccreNum", "!=", "")
                        ->where("ExpectedPlantingMonth",$current_month )
                        ->first();
                        if($earliest_month != null){
                       
                              for($y=1; $y <6;$y++){
                                $earliest_week = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                                  ->where("CoopAccreNum", "!=", "")
                                  ->where("ExpectedPlantingMonth", $earliest_month->ExpectedPlantingMonth )
                                  ->where("ExpectedPlantingWeek", $y)
                                  ->first();
                                  if($earliest_week != null){
                              

                                      break;
                                  }

                              }
                          break;
                        }
                  }
                  $earliest =    $week_arr[$earliest_week->ExpectedPlantingWeek]." ".date("F",strtotime($earliest_month->ExpectedPlantingMonth));
             
                  
                  for($c=12; $c>0; $c--)
                  {
                    $check_month = $c;
                    if($c < 10){
                      $check_month = "0".$c;
                    }
                    $current_month = date("M", strtotime(date("Y-".$check_month."-d")));

                      $lastest_month = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                        ->where("CoopAccreNum", "!=", "")
                        ->where("ExpectedPlantingMonth",$current_month )
                        ->first();
                        if($lastest_month != null){
                       
                              for($v=6; $v>0;$v--){
                                $lastest_week = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                                  ->where("CoopAccreNum", "!=", "")
                                  ->where("ExpectedPlantingMonth", $lastest_month->ExpectedPlantingMonth )
                                  ->where("ExpectedPlantingWeek", $v)
                                  ->first();
                                  if($lastest_week != null){
                                      break;
                                  }

                              }
                          break;
                        }
                  }
                  $lastest =  $week_arr[$lastest_week->ExpectedPlantingWeek]." ". date("F",strtotime($lastest_month->ExpectedPlantingMonth));
             




              
                $total_growers = count(DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                ->where("CoopAccreNum", "!=", "")
                ->where("GrowerAccreNum", "!=", "")
                  ->groupBy("GrowerAccreNum")
                  ->get());

                  $total_area = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                    ->where("CoopAccreNum", "!=", "")
                    ->where("GrowerAccreNum", "!=", "")
                    ->sum("Area");


              $regions = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_regions")
                ->where("id", "!=", 14)
                  ->orderby("order")
                  ->get();
                foreach($regions as $key=> $reg){
                  $check_data = 0;
                      $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                      ->where("regionId", $reg->id)
                      ->orderBy("coopName")
                      ->get();
                        foreach($coop_list as $data_coop){
                          $result_count =  count(DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                              ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($data_coop->accreditation_no,14,20))
                              ->get());
                              if($result_count > 0){
                                $check_data = 1;
                                $sg_list++;
                              }
                        }

                    if($check_data == 1){
                        $regions[$key]->with_data = "true";
                        
                    }else{
                      $regions[$key]->with_data = "false";
                      
                    }
                      

                }

          
                  return view("rsis.rsis_distri_dashboard")
                    ->with("earliest", $earliest)
                    ->with("lastest", $lastest)
                    ->with("total_growers", $total_growers)
                    ->with("total_area", $total_area)
                    ->with("sg_list", $sg_list)
                    ->with("regions", $regions)
                    ->with("synced_data", $synced);
         




  }


  public function getRsisRsDistri(Request $request){
  
    $coop_list = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
      ->where("regionId", $request->region)
      ->orderBy("coopName")
      ->get();
      $week_arr = array(
        0 => "N/A",
        1 => "1st week of",
        2 => "2nd week of",
        3 => "3rd week of",
        4 => "4th week of",
        5 => "5th week of"
      );
      $data_array = array();
      foreach($coop_list as $coop){
        //14


          $total_sg = count(DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
              ->groupby("GrowerAccreNum")
              ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
              ->get());

          if($total_sg <= 0){
            continue;
          }


           $total_rs_production =   DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
              ->groupby("GrowerAccreNum")
              ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
              ->sum("Kilograms");

            $total_area =   DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
              ->groupby("GrowerAccreNum")
              ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
              ->sum("Area");



              for($x=1; $x<13; $x++)
              {
                $check_month = $x;
                if($x < 10){
                  $check_month = "0".$x;
                }
                $current_month = date("M", strtotime(date("Y-".$check_month."-d")));

                  $earliest_month = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                  ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
                    ->where("ExpectedPlantingMonth",$current_month )
                    ->first();
                    if($earliest_month != null){
                   
                          for($y=1; $y <6;$y++){
                            $earliest_week = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                            ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
                              ->where("ExpectedPlantingMonth", $earliest_month->ExpectedPlantingMonth )
                              ->where("ExpectedPlantingWeek", $y)
                              ->first();
                              if($earliest_week != null){
                          

                                  break;
                              }

                          }
                      break;
                    }
              }

              $earliest =    $week_arr[$earliest_week->ExpectedPlantingWeek]." ".date("F",strtotime($earliest_month->ExpectedPlantingMonth));
         
              
              for($c=12; $c>0; $c--)
              {
                $check_month = $c;
                if($c < 10){
                  $check_month = "0".$c;
                }
                $current_month = date("M", strtotime(date("Y-".$check_month."-d")));

                  $lastest_month = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                  ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
                    ->where("ExpectedPlantingMonth",$current_month )
                    ->first();
                    if($lastest_month != null){
                   
                          for($v=6; $v>0;$v--){
                            $lastest_week = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rs_distribution')
                            ->where(DB::raw("SUBSTR(CoopAccreNum,15,20)"), "LIKE", substr($coop->accreditation_no,14,20))
                              ->where("ExpectedPlantingMonth", $lastest_month->ExpectedPlantingMonth )
                              ->where("ExpectedPlantingWeek", $v)
                              ->first();
                              if($lastest_week != null){
                                  break;
                              }

                          }
                      break;
                    }
              }
              $lastest =   $week_arr[$lastest_week->ExpectedPlantingWeek]." ".date("F",strtotime($lastest_month->ExpectedPlantingMonth));
         




             
                $coop_arr = array(
                  "account_no" => substr($coop->accreditation_no,14,20),
                  "coop_name" => $coop->coopName,
                  "coop_accre" => $coop->accreditation_no,
                  "moa_number" => $coop->current_moa,
                  "total_sg" => number_format($total_sg),
                  "total_rs_production" => $total_rs_production,
                  "total_area" => $total_area,
                  "earliest" => $earliest,
                  "lastest" => $lastest
              );
        
          array_push($data_array, $coop_arr);
              

            



      }


   
       

  return $data_array;

  }


  public function exportAll(Request $request)
  {   
    $getAll = DB::table($GLOBALS['season_prefix'].'rcep_rsis_rla.tbl_rla_details')
    ->get();

    // dd($getAll);
    $excel_data = json_decode(json_encode($getAll), true);

    return Excel::create("RSIS RLA Data as of ".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
        $excel->sheet("KP Kit Distribution Report", function($sheet) use ($excel_data) {
            $sheet->fromArray($excel_data);

            $border_style = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
        });

    })->setActiveSheetIndex(0)->download('xlsx');

  
  }


    
}

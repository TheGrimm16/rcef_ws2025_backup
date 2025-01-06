<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use Yajra\Datatables\Facades\Datatables;
use App\utility;

class DistributionDataController extends Controller
{
        public function home_ui(){
        $regionNames = array();
        $regionCodes = array();
        $regionsArray = array();
        $munArray = array();
        $provArray = array();
        $seasons = array();

        $regions = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
                  ->select('regCode','regionName')
                  ->where('regCode', '!=','99')
                  ->groupBy('regCode')
                  ->get();

         foreach($regions as $reg)
         {
            array_push($regionCodes, $reg->regCode);
            array_push($regionNames, $reg->regionName);
         }
        
         // dd($regions);
      return view('DistributionData.home',
         compact(
            'regionNames',
            'regionCodes'
         ));
        
    }

    public function getDistributionDataReg(){
      $regions = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
                  ->select('regCode','regionName')
                  ->where('regCode', '!=','99')
                  ->groupBy('regCode')
                  ->get();
      return $regions;
    }
    
    public function getDistributionDataPrv(Request $request){
      $provinces = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
                  ->select('prv_code','province')
                  ->where('regCode', 'LIKE',$request->reg)
                  ->groupBy('prv_code')
                  ->get();
      return $provinces;
    }

    public function getDistributionDataMun(Request $request)
    {
      $municipalities = DB::table('ds2024_rcep_delivery_inspection.lib_prv')
                  ->select('prv','municipality')
                  ->where('prv_code', 'LIKE',$request->prv_code)
                  ->groupBy('prv')
                  ->get();
      return $municipalities;
    }

    public function fetchDistData(Request $request){

      // dd($request->all());
      $region = $request->reg;
      $province = $request->prv;
      $municipality = $request->mun;
      $season = $request->ssn;
      $distType = $request->distType;

      if($distType == 'bep'){
         $season = $request->ssn.'_bep';
      }
      else if($distType == 'reg'){
         $season = $request->ssn;
      }
      
      $allData = array();


      if($region == 'default' && $province == 'default' && $municipality == 'default')
      {
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/all/all/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province == 'default' && $municipality == 'default')
      {
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$region."/".$region."/sPTfiZrgzCo3R*rWjpZnHw=";
         // dd($apiUrl);
      }
      else if($province != 'default' && $municipality == 'default')
      {
         $undashedPrv = substr($province,0,2).'-'.substr($province,2,2);
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$province."/".$undashedPrv."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality != 'default')
      {
         $undashedMun = substr($municipality,0,2).'-'.substr($municipality,2,2).'-'.substr($municipality,4,2);

         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$municipality."/".$undashedMun."/sPTfiZrgzCo3R*rWjpZnHw=";
      }

        $apiResponse = file_get_contents($apiUrl);
        
        $response = json_decode($apiResponse);

        foreach($response as $row)
        {
         $seasonalData = $row->{$season};
         if($seasonalData){

            // dd($row->{$season}[0]->dist_province_dist);
            array_push($allData, array(
               "ffrs_rsbsa" => $row->ffrs_rsbsa,
               "lastName" => $row->lastName,
               "firstName" => $row->firstName,
               "midName" => $row->midName,
               "extName" => $row->extName,
               "sex" => $row->sex,
               "birthdate" => $row->birthdate,
               "home_geocode" => $row->home_geocode,
               "is_pwd" => $row->is_pwd,
               "is_ip" => $row->is_ip,
               "tribe_name" => $row->tribe_name,
               "is_arb" => $row->is_arb,
               "mother_name" => $row->mother_name,
               "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
               "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
               "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
               "registered_area" => $row->{$season}[0]->registered_area,
               "claimed_area" => $row->{$season}[0]->claimed_area,
               "bags_claimed" => $row->{$season}[0]->bags_claimed,
               "variety" => $row->{$season}[0]->variety,
               "category" => $row->{$season}[0]->category,
               "yield_data" => $row->{$season}[0]->yield_data,
               "ls_planted" => $row->{$season}[0]->ls_planted,
               "irrigation" => $row->{$season}[0]->irrigation,
               "method" => $row->{$season}[0]->method,
               "water_source" => $row->{$season}[0]->water_source
            ));
         }
         else{
            continue;
         }
      }
      // dd(array_slice($allData, 0, 1000, true));
      // return($allData);
      $allData = collect($allData);
    
      
            return Datatables::of($allData)
            ->make(true);
      
    }

    public function exportDistData_backup($reg, $prv, $mun, $ssn, $typ){

      // dd($request->all());
      $region = $reg;
      $province = $prv;
      $municipality = $mun;
      $type = $typ == 'reg'? '' : '_bep';
      $season = $ssn;
      $allData = array();
      $season = $season.$type;

      if($province == 'default' && $municipality == 'default')
      {
         $filename = 'Distribution Data - '.$season.'_'.$region;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$region."/".$region."/sPTfiZrgzCo3R*rWjpZnHw=";
         // dd($apiUrl);
      }
      else if($province != 'default' && $municipality == 'default')
      {
         $undashedPrv = substr($province,0,2).'-'.substr($province,2,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedPrv;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$province."/".$undashedPrv."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality != 'default')
      {
         $undashedMun = substr($municipality,0,2).'-'.substr($municipality,2,2).'-'.substr($municipality,4,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedMun;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$municipality."/".$undashedMun."/sPTfiZrgzCo3R*rWjpZnHw=";
      }

        $apiResponse = file_get_contents($apiUrl);
        
        $response = json_decode($apiResponse);

        foreach($response as $row){
         if($row->{$season}){

            // dd($row->{$season}[0]->dist_province_dist);
            array_push($allData, array(
               "ffrs_rsbsa" => $row->ffrs_rsbsa,
               "lastName" => $row->lastName,
               "firstName" => $row->firstName,
               "midName" => $row->midName,
               "extName" => $row->extName,
               "sex" => $row->sex,
               "birthdate" => $row->birthdate,
               "home_geocode" => $row->home_geocode,
               "is_pwd" => $row->is_pwd,
               "is_ip" => $row->is_ip,
               "tribe_name" => $row->tribe_name,
               "is_arb" => $row->is_arb,
               "mother_name" => $row->mother_name,
               "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
               "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
               "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
               "registered_area" => $row->{$season}[0]->registered_area,
               "claimed_area" => $row->{$season}[0]->claimed_area,
               "bags_claimed" => $row->{$season}[0]->bags_claimed,
               "variety" => $row->{$season}[0]->variety,
               "category" => $row->{$season}[0]->category,
               "yield_data" => $row->{$season}[0]->yield_data,
               "ls_planted" => $row->{$season}[0]->ls_planted,
               "irrigation" => $row->{$season}[0]->irrigation,
               "method" => $row->{$season}[0]->method,
               "water_source" => $row->{$season}[0]->water_source
            ));
         }
      }
      // dd(array_slice($allData, 0, 1000, true));
      // return($allData);
      $allData = collect($allData);
    
    
      // $excel_data = json_decode(json_encode($allData), true);
        
      // return Excel::create($filename, function($excel) use ($excel_data) {
      //    $excel->sheet("Farmer Information", function($sheet) use ($excel_data) {
      //          $sheet->fromArray($excel_data);
      //          $sheet->getStyle('A1:U1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
      //          $border_style = array(
      //             'borders' => array(
      //                'allborders' => array(
      //                      'style' => \PHPExcel_Style_Border::BORDER_THIN,
      //                      'color' => array('argb' => '000000'),
      //                ),
      //             ),
      //          );
      //          $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
      //    });
      // })->setActiveSheetIndex(0)->download('xlsx');

      $excel_data = json_decode(json_encode($allData), true);

      // foreach ($excel_data as $key => $row) {
      //    $yield_data = json_decode(str_replace("'", '"', $row['yield_data']), true);
      //    if (is_array($yield_data)) {
      //       foreach ($yield_data as $index => $data) {
      //          foreach ($data as $k => $v) {
      //                $excel_data[$key]["yield_data_{$index}_{$k}"] = $v;
      //          }
      //       }
      //    }
      //    unset($excel_data[$key]['yield_data']);
      // }

      // $longestRowLength = 0;
      // foreach ($excel_data as $row) {
      //    $rowLength = count($row);
      //    if ($rowLength > $longestRowLength) {
      //       $longestRowLength = $rowLength;
      //    }
      // }

      // $defaultHeaders = ["FFRS RSBSA","Last Name","First Name","Middle Name","Ext. Name","Sex","Birthdate","Home Geocode","PWD?","IP?","Tribe Name","ARB?","Mother Name","(Dist.) Province","(Dist.) Municipality","(Dist.) Dropoff","Registered Area","Claimed Area","Bags Claimed","Variety","Category","Last Season Planted?","Ecosystem","Method of Planting","Water Source"];
      $yield_iteration = 0;
      // if($longestRowLength > count($defaultHeaders)){
      //    $yield_iteration = ($longestRowLength - count($defaultHeaders)) / 11;
      //    for($i = 0; $i < $yield_iteration; $i++){
      //       array_push($defaultHeaders, $i."_Yield Data Variety");
      //       array_push($defaultHeaders, $i."_Yield Data Area");
      //       array_push($defaultHeaders, $i."_Yield Data Bags");
      //       array_push($defaultHeaders, $i."_Yield Data Weight");
      //       array_push($defaultHeaders, $i."_Yield Data Type");
      //       array_push($defaultHeaders, $i."_Yield Data Class");
      //       array_push($defaultHeaders, $i."_Yield Data Yield");
      //       array_push($defaultHeaders, $i."_Yield Data Low Yield Cause");
      //       array_push($defaultHeaders, $i."_Yield Data Season");
      //       array_push($defaultHeaders, $i."_Yield Data Year");
      //       array_push($defaultHeaders, $i."_Yield Data Other Variety Name");
      //    }
      // }

      return Excel::create($filename, function($excel) use ($excel_data) {
         $excel->sheet("Farmer Information", function($sheet) use ($excel_data) {
            
            // if($yield_iteration == 0){
            //    $header_length = "A1:Y1";
            // }else if($yield_iteration == 1){
            //    $header_length = "A1:AJ1";
            // }else if($yield_iteration == 2){
            //    $header_length = "A1:AU1";
            // }else{
            //    $header_length = "A1:BF1";
            // }

            // $sheet->fromArray([$defaultHeaders], null, 'A1', false, false);
            $sheet->fromArray($excel_data);
            $sheet->getStyle('A1:Z1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
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

   public function exportDistData($reg, $prv, $mun, $ssn, $typ){
      $region = $reg;
      $province = $prv;
      $municipality = $mun;
      $type = $typ == 'reg'? '' : '_bep';
      $season = $ssn;
      $allData = array();
      $season = $season.$type;

      if($province == 'default' && $municipality == 'default')
      {
         $filename = 'Distribution Data - '.$season.'_'.$region;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$region."/".$region."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality == 'default')
      {
         $undashedPrv = substr($province,0,2).'-'.substr($province,2,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedPrv;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$province."/".$undashedPrv."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality != 'default')
      {
         $undashedMun = substr($municipality,0,2).'-'.substr($municipality,2,2).'-'.substr($municipality,4,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedMun;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$municipality."/".$undashedMun."/sPTfiZrgzCo3R*rWjpZnHw=";
      }

      $apiResponse = file_get_contents($apiUrl);
        
      $response = json_decode($apiResponse);

      $allData = collect($response)->map(function($row) use ($season) {
         if($row->{$season}){
            if(count($row->{$season}) == 1){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source
               ];
            }else if(count($row->{$season}) == 2){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source
               ];
            }else if(count($row->{$season}) == 3){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
               ];
            }else if(count($row->{$season}) == 4){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
                  "3dist_province_dist" => $row->{$season}[3]->dist_province_dist,
                  "3dist_municipality_dist" => $row->{$season}[3]->dist_municipality_dist,
                  "3dist_prv_dropoff_id" => $row->{$season}[3]->dist_prv_dropoff_id,
                  "3registered_area" => $row->{$season}[3]->registered_area,
                  "3claimed_area" => $row->{$season}[3]->claimed_area,
                  "3bags_claimed" => $row->{$season}[3]->bags_claimed,
                  "3variety" => $row->{$season}[3]->variety,
                  "3category" => $row->{$season}[3]->category,
                  "3yield_data" => $row->{$season}[3]->yield_data,
                  "3ls_planted" => $row->{$season}[3]->ls_planted,
                  "3irrigation" => $row->{$season}[3]->irrigation,
                  "3method" => $row->{$season}[3]->method,
                  "3water_source" => $row->{$season}[3]->water_source
               ];
            }else if(count($row->{$season}) == 5){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
                  "3dist_province_dist" => $row->{$season}[3]->dist_province_dist,
                  "3dist_municipality_dist" => $row->{$season}[3]->dist_municipality_dist,
                  "3dist_prv_dropoff_id" => $row->{$season}[3]->dist_prv_dropoff_id,
                  "3registered_area" => $row->{$season}[3]->registered_area,
                  "3claimed_area" => $row->{$season}[3]->claimed_area,
                  "3bags_claimed" => $row->{$season}[3]->bags_claimed,
                  "3variety" => $row->{$season}[3]->variety,
                  "3category" => $row->{$season}[3]->category,
                  "3yield_data" => $row->{$season}[3]->yield_data,
                  "3ls_planted" => $row->{$season}[3]->ls_planted,
                  "3irrigation" => $row->{$season}[3]->irrigation,
                  "3method" => $row->{$season}[3]->method,
                  "3water_source" => $row->{$season}[3]->water_source,
                  "4dist_province_dist" => $row->{$season}[4]->dist_province_dist,
                  "4dist_municipality_dist" => $row->{$season}[4]->dist_municipality_dist,
                  "4dist_prv_dropoff_id" => $row->{$season}[4]->dist_prv_dropoff_id,
                  "4registered_area" => $row->{$season}[4]->registered_area,
                  "4claimed_area" => $row->{$season}[4]->claimed_area,
                  "4bags_claimed" => $row->{$season}[4]->bags_claimed,
                  "4variety" => $row->{$season}[4]->variety,
                  "4category" => $row->{$season}[4]->category,
                  "4yield_data" => $row->{$season}[4]->yield_data,
                  "4ls_planted" => $row->{$season}[4]->ls_planted,
                  "4irrigation" => $row->{$season}[4]->irrigation,
                  "4method" => $row->{$season}[4]->method,
                  "4water_source" => $row->{$season}[4]->water_source
               ];
            }else if(count($row->{$season}) > 5){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
                  "3dist_province_dist" => $row->{$season}[3]->dist_province_dist,
                  "3dist_municipality_dist" => $row->{$season}[3]->dist_municipality_dist,
                  "3dist_prv_dropoff_id" => $row->{$season}[3]->dist_prv_dropoff_id,
                  "3registered_area" => $row->{$season}[3]->registered_area,
                  "3claimed_area" => $row->{$season}[3]->claimed_area,
                  "3bags_claimed" => $row->{$season}[3]->bags_claimed,
                  "3variety" => $row->{$season}[3]->variety,
                  "3category" => $row->{$season}[3]->category,
                  "3yield_data" => $row->{$season}[3]->yield_data,
                  "3ls_planted" => $row->{$season}[3]->ls_planted,
                  "3irrigation" => $row->{$season}[3]->irrigation,
                  "3method" => $row->{$season}[3]->method,
                  "3water_source" => $row->{$season}[3]->water_source,
                  "4dist_province_dist" => $row->{$season}[4]->dist_province_dist,
                  "4dist_municipality_dist" => $row->{$season}[4]->dist_municipality_dist,
                  "4dist_prv_dropoff_id" => $row->{$season}[4]->dist_prv_dropoff_id,
                  "4registered_area" => $row->{$season}[4]->registered_area,
                  "4claimed_area" => $row->{$season}[4]->claimed_area,
                  "4bags_claimed" => $row->{$season}[4]->bags_claimed,
                  "4variety" => $row->{$season}[4]->variety,
                  "4category" => $row->{$season}[4]->category,
                  "4yield_data" => $row->{$season}[4]->yield_data,
                  "4ls_planted" => $row->{$season}[4]->ls_planted,
                  "4irrigation" => $row->{$season}[4]->irrigation,
                  "4method" => $row->{$season}[4]->method,
                  "5water_source" => $row->{$season}[5]->water_source,
                  "5dist_province_dist" => $row->{$season}[5]->dist_province_dist,
                  "5dist_municipality_dist" => $row->{$season}[5]->dist_municipality_dist,
                  "5dist_prv_dropoff_id" => $row->{$season}[5]->dist_prv_dropoff_id,
                  "5registered_area" => $row->{$season}[5]->registered_area,
                  "5claimed_area" => $row->{$season}[5]->claimed_area,
                  "5bags_claimed" => $row->{$season}[5]->bags_claimed,
                  "5variety" => $row->{$season}[5]->variety,
                  "5category" => $row->{$season}[5]->category,
                  "5yield_data" => $row->{$season}[5]->yield_data,
                  "5ls_planted" => $row->{$season}[5]->ls_planted,
                  "5irrigation" => $row->{$season}[5]->irrigation,
                  "5method" => $row->{$season}[5]->method,
                  "5water_source" => $row->{$season}[5]->water_source
               ];
            }else{
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name
               ];
            }
         }
      })->filter()->all();

      return Excel::filter('chunk', 1000)->create($filename, function($excel) use ($allData) {
         $excel->sheet("Farmer Information", function($sheet) use ($allData) {
            $sheet->fromArray($allData);
            $sheet->getStyle('A1:Z1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
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

   public function arrayExcel($reg, $prv, $mun, $ssn, $typ){
      $region = $reg;
      $province = $prv;
      $municipality = $mun;
      $type = $typ == 'reg'? '' : '_bep';
      $season = $ssn;
      $allData = array();
      $season = $season.$type;

      if($province == 'default' && $municipality == 'default')
      {
         $filename = 'Distribution Data - '.$season.'_'.$region;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$region."/".$region."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality == 'default')
      {
         $undashedPrv = substr($province,0,2).'-'.substr($province,2,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedPrv;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$province."/".$undashedPrv."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality != 'default')
      {
         $undashedMun = substr($municipality,0,2).'-'.substr($municipality,2,2).'-'.substr($municipality,4,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedMun;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$municipality."/".$undashedMun."/sPTfiZrgzCo3R*rWjpZnHw=";
      }

      $apiResponse = file_get_contents($apiUrl);
        
      $response = json_decode($apiResponse);

      $allData = collect($response)->map(function($row) use ($season) {
         if($row->{$season}){
            if(count($row->{$season}) == 1){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source
               ];
            }else if(count($row->{$season}) == 2){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source
               ];
            }else if(count($row->{$season}) == 3){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
               ];
            }else if(count($row->{$season}) == 4){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
                  "3dist_province_dist" => $row->{$season}[3]->dist_province_dist,
                  "3dist_municipality_dist" => $row->{$season}[3]->dist_municipality_dist,
                  "3dist_prv_dropoff_id" => $row->{$season}[3]->dist_prv_dropoff_id,
                  "3registered_area" => $row->{$season}[3]->registered_area,
                  "3claimed_area" => $row->{$season}[3]->claimed_area,
                  "3bags_claimed" => $row->{$season}[3]->bags_claimed,
                  "3variety" => $row->{$season}[3]->variety,
                  "3category" => $row->{$season}[3]->category,
                  "3yield_data" => $row->{$season}[3]->yield_data,
                  "3ls_planted" => $row->{$season}[3]->ls_planted,
                  "3irrigation" => $row->{$season}[3]->irrigation,
                  "3method" => $row->{$season}[3]->method,
                  "3water_source" => $row->{$season}[3]->water_source
               ];
            }else if(count($row->{$season}) == 5){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
                  "3dist_province_dist" => $row->{$season}[3]->dist_province_dist,
                  "3dist_municipality_dist" => $row->{$season}[3]->dist_municipality_dist,
                  "3dist_prv_dropoff_id" => $row->{$season}[3]->dist_prv_dropoff_id,
                  "3registered_area" => $row->{$season}[3]->registered_area,
                  "3claimed_area" => $row->{$season}[3]->claimed_area,
                  "3bags_claimed" => $row->{$season}[3]->bags_claimed,
                  "3variety" => $row->{$season}[3]->variety,
                  "3category" => $row->{$season}[3]->category,
                  "3yield_data" => $row->{$season}[3]->yield_data,
                  "3ls_planted" => $row->{$season}[3]->ls_planted,
                  "3irrigation" => $row->{$season}[3]->irrigation,
                  "3method" => $row->{$season}[3]->method,
                  "3water_source" => $row->{$season}[3]->water_source,
                  "4dist_province_dist" => $row->{$season}[4]->dist_province_dist,
                  "4dist_municipality_dist" => $row->{$season}[4]->dist_municipality_dist,
                  "4dist_prv_dropoff_id" => $row->{$season}[4]->dist_prv_dropoff_id,
                  "4registered_area" => $row->{$season}[4]->registered_area,
                  "4claimed_area" => $row->{$season}[4]->claimed_area,
                  "4bags_claimed" => $row->{$season}[4]->bags_claimed,
                  "4variety" => $row->{$season}[4]->variety,
                  "4category" => $row->{$season}[4]->category,
                  "4yield_data" => $row->{$season}[4]->yield_data,
                  "4ls_planted" => $row->{$season}[4]->ls_planted,
                  "4irrigation" => $row->{$season}[4]->irrigation,
                  "4method" => $row->{$season}[4]->method,
                  "4water_source" => $row->{$season}[4]->water_source
               ];
            }else if(count($row->{$season}) > 5){
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $row->{$season}[0]->dist_province_dist,
                  "dist_municipality_dist" => $row->{$season}[0]->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $row->{$season}[0]->dist_prv_dropoff_id,
                  "registered_area" => $row->{$season}[0]->registered_area,
                  "claimed_area" => $row->{$season}[0]->claimed_area,
                  "bags_claimed" => $row->{$season}[0]->bags_claimed,
                  "variety" => $row->{$season}[0]->variety,
                  "category" => $row->{$season}[0]->category,
                  "yield_data" => $row->{$season}[0]->yield_data,
                  "ls_planted" => $row->{$season}[0]->ls_planted,
                  "irrigation" => $row->{$season}[0]->irrigation,
                  "method" => $row->{$season}[0]->method,
                  "water_source" => $row->{$season}[0]->water_source,
                  "1dist_province_dist" => $row->{$season}[1]->dist_province_dist,
                  "1dist_municipality_dist" => $row->{$season}[1]->dist_municipality_dist,
                  "1dist_prv_dropoff_id" => $row->{$season}[1]->dist_prv_dropoff_id,
                  "1registered_area" => $row->{$season}[1]->registered_area,
                  "1claimed_area" => $row->{$season}[1]->claimed_area,
                  "1bags_claimed" => $row->{$season}[1]->bags_claimed,
                  "1variety" => $row->{$season}[1]->variety,
                  "1category" => $row->{$season}[1]->category,
                  "1yield_data" => $row->{$season}[1]->yield_data,
                  "1ls_planted" => $row->{$season}[1]->ls_planted,
                  "1irrigation" => $row->{$season}[1]->irrigation,
                  "1method" => $row->{$season}[1]->method,
                  "1water_source" => $row->{$season}[1]->water_source,
                  "2dist_province_dist" => $row->{$season}[2]->dist_province_dist,
                  "2dist_municipality_dist" => $row->{$season}[2]->dist_municipality_dist,
                  "2dist_prv_dropoff_id" => $row->{$season}[2]->dist_prv_dropoff_id,
                  "2registered_area" => $row->{$season}[2]->registered_area,
                  "2claimed_area" => $row->{$season}[2]->claimed_area,
                  "2bags_claimed" => $row->{$season}[2]->bags_claimed,
                  "2variety" => $row->{$season}[2]->variety,
                  "2category" => $row->{$season}[2]->category,
                  "2yield_data" => $row->{$season}[2]->yield_data,
                  "2ls_planted" => $row->{$season}[2]->ls_planted,
                  "2irrigation" => $row->{$season}[2]->irrigation,
                  "2method" => $row->{$season}[2]->method,
                  "2water_source" => $row->{$season}[2]->water_source,
                  "3dist_province_dist" => $row->{$season}[3]->dist_province_dist,
                  "3dist_municipality_dist" => $row->{$season}[3]->dist_municipality_dist,
                  "3dist_prv_dropoff_id" => $row->{$season}[3]->dist_prv_dropoff_id,
                  "3registered_area" => $row->{$season}[3]->registered_area,
                  "3claimed_area" => $row->{$season}[3]->claimed_area,
                  "3bags_claimed" => $row->{$season}[3]->bags_claimed,
                  "3variety" => $row->{$season}[3]->variety,
                  "3category" => $row->{$season}[3]->category,
                  "3yield_data" => $row->{$season}[3]->yield_data,
                  "3ls_planted" => $row->{$season}[3]->ls_planted,
                  "3irrigation" => $row->{$season}[3]->irrigation,
                  "3method" => $row->{$season}[3]->method,
                  "3water_source" => $row->{$season}[3]->water_source,
                  "4dist_province_dist" => $row->{$season}[4]->dist_province_dist,
                  "4dist_municipality_dist" => $row->{$season}[4]->dist_municipality_dist,
                  "4dist_prv_dropoff_id" => $row->{$season}[4]->dist_prv_dropoff_id,
                  "4registered_area" => $row->{$season}[4]->registered_area,
                  "4claimed_area" => $row->{$season}[4]->claimed_area,
                  "4bags_claimed" => $row->{$season}[4]->bags_claimed,
                  "4variety" => $row->{$season}[4]->variety,
                  "4category" => $row->{$season}[4]->category,
                  "4yield_data" => $row->{$season}[4]->yield_data,
                  "4ls_planted" => $row->{$season}[4]->ls_planted,
                  "4irrigation" => $row->{$season}[4]->irrigation,
                  "4method" => $row->{$season}[4]->method,
                  "5water_source" => $row->{$season}[5]->water_source,
                  "5dist_province_dist" => $row->{$season}[5]->dist_province_dist,
                  "5dist_municipality_dist" => $row->{$season}[5]->dist_municipality_dist,
                  "5dist_prv_dropoff_id" => $row->{$season}[5]->dist_prv_dropoff_id,
                  "5registered_area" => $row->{$season}[5]->registered_area,
                  "5claimed_area" => $row->{$season}[5]->claimed_area,
                  "5bags_claimed" => $row->{$season}[5]->bags_claimed,
                  "5variety" => $row->{$season}[5]->variety,
                  "5category" => $row->{$season}[5]->category,
                  "5yield_data" => $row->{$season}[5]->yield_data,
                  "5ls_planted" => $row->{$season}[5]->ls_planted,
                  "5irrigation" => $row->{$season}[5]->irrigation,
                  "5method" => $row->{$season}[5]->method,
                  "5water_source" => $row->{$season}[5]->water_source
               ];
            }else{
               return[
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name
               ];
            }
         }
      })->filter()->all();

      return $allData;
   }
    
   public function arrayExcelTall($reg, $prv, $mun, $ssn, $typ){
      $region = $reg;
      $province = $prv;
      $municipality = $mun;
      $type = $typ == 'reg'? '' : '_bep';
      $season = $ssn;
      $allData = array();
      $season = $season.$type;

      if($province == 'default' && $municipality == 'default')
      {
         $filename = 'Distribution Data - '.$season.'_'.$region;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$region."/".$region."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality == 'default')
      {
         $undashedPrv = substr($province,0,2).'-'.substr($province,2,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedPrv;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$province."/".$undashedPrv."/sPTfiZrgzCo3R*rWjpZnHw=";
      }
      else if($province != 'default' && $municipality != 'default')
      {
         $undashedMun = substr($municipality,0,2).'-'.substr($municipality,2,2).'-'.substr($municipality,4,2);
         $filename = 'Distribution Data - '.$season.'_'.$undashedMun;
         $apiUrl = "http://192.168.10.54:3000/api/distribution/".$season."/".$municipality."/".$undashedMun."/sPTfiZrgzCo3R*rWjpZnHw=";
      }

      $apiResponse = file_get_contents($apiUrl);
        
      $response = json_decode($apiResponse);

      $allData = collect($response)->flatMap(function($row) use ($season) {
         if ($row->{$season}) {
             $entries = $row->{$season};
     
             // Initialize an array to store results
             $result = [];
     
             // Handle up to 5 entries
             for ($i = 0; $i < min(count($entries), 5); $i++) {
                 $entry = $entries[$i];
     
                 $result[] = [
                  "ffrs_rsbsa" => $row->ffrs_rsbsa,
                  "lastName" => $row->lastName,
                  "firstName" => $row->firstName,
                  "midName" => $row->midName,
                  "extName" => $row->extName,
                  "sex" => $row->sex,
                  "birthdate" => $row->birthdate,
                  "home_geocode" => $row->home_geocode,
                  "is_pwd" => $row->is_pwd,
                  "is_ip" => $row->is_ip,
                  "tribe_name" => $row->tribe_name,
                  "is_arb" => $row->is_arb,
                  "mother_name" => $row->mother_name,
                  "dist_province_dist" => $entry->dist_province_dist,
                  "dist_municipality_dist" => $entry->dist_municipality_dist,
                  "dist_prv_dropoff_id" => $entry->dist_prv_dropoff_id,
                  "registered_area" => $entry->registered_area,
                  "claimed_area" => $entry->claimed_area,
                  "bags_claimed" => $entry->bags_claimed,
                  "variety" => $entry->variety,
                  "category" => $entry->category,
                  "yield_data" => $entry->yield_data,
                  "ls_planted" => $entry->ls_planted,
                  "irrigation" => $entry->irrigation,
                  "method" => $entry->method,
                  "water_source" => $entry->water_source
               ];
             }
     
             return $result;
         }
     })->filter()->all();     

      return $allData;
   }
}

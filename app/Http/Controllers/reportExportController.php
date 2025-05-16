<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\SeedCooperatives;
use App\SeedGrowers;
use Config;
use DB;
use Excel;
use Session;
use Auth;

class reportExportController extends Controller
{   

    
        
    public function distriData($prv){
        // $prv_arr = array('0129','0133','0155','0215','0231','0250','0257','0308','0314','0354','0371','0377','0421','0434','0456','0505','0541','0562','0604','0606','0619','0645','0712','0746','0860','0864','0878','0973','0983','1035','1042','1043','1123','1124','1125','1182','1247','1263','1280','1427','1432','1536','1538','1547','1602','1603','1668');
        // $prv_arr = array("0129");

        // foreach($prv_arr as $prv){
            $excel_data = array();
             
            $prv = "ds2024_prv_".$prv;
          

            $info_schema = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $prv)
                ->where("TABLE_NAME", "new_released")
                ->get();

                $info_schema_2 = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $prv)
                ->where("TABLE_NAME", "farmer_information_final")
                ->get();
            //  dd($info_schema_2);
            if(count($info_schema)<= 0 || count($info_schema_2)<=0){
                
                
                
                return "TABLES NOT EXIST";
            }


            $released = DB::table($prv.".new_released")
                ->select("rcef_id")
                ->where("category", "INBRED")
                ->groupBy("rcef_id")
                ->get();

            $releasd_id = json_decode(json_encode($released), true);
            // dd($releasd_id);
               $farmer_info =  DB::table($prv.".farmer_information_final")
                    ->whereIn("rcef_id", $releasd_id)
                    ->groupBy("rcef_id")
                    ->get();
             

                    foreach($farmer_info as $farmer){
                        $release_data = DB::table($prv.".new_released")
                            ->select("new_released.province", "new_released.municipality", DB::raw('SUM(bags_claimed) as bags'), DB::raw("SUM(claimed_area) as area"), "ecosystem_cs", "ecosystem_source_cs", "crop_establishment_cs", "seedling_age", "yield_area_harvested_ls", "yield_no_of_bags_ls", "yield_wt_per_bag", "planting_week","lib_prv.psa_code" )
                            ->join("ds2024_rcep_delivery_inspection.lib_prv", function($join){
                                $join->on("lib_prv.province", "=", "new_released.province");
                                $join->on("lib_prv.municipality", "=", "new_released.municipality");
                            })
                            ->where("category", "INBRED")
                            ->where("rcef_id", $farmer->rcef_id)
                            ->first();

                  
                        $arr = array(
                            "rsbsa_control_no" => $farmer->rsbsa_control_no,
                            "lastName" => $farmer->lastName,
                            "firstName" => $farmer->firstName,
                            "midName" => $farmer->midName,
                            "extName" => $farmer->extName,
                            "rcef_id" => $farmer->rcef_id,
                            
                            "birthdate" => $farmer->birthdate,
                            "sex" => $farmer->sex,
                            "province" => $release_data->province,
                            "municipality" => $release_data->municipality,
                            "psa_code" => $release_data->psa_code,
                            "bags" => $release_data->bags,
                            "area" => $release_data->area,
                            "ecosystem" => $release_data->ecosystem_cs,
                            "eco_source" => $release_data->ecosystem_source_cs,
                            "establishment" => $release_data->crop_establishment_cs,
                            "seed_age" => $release_data->seedling_age,
                            "yield_area" => $release_data->yield_area_harvested_ls,
                            "yield_bags" => $release_data->yield_no_of_bags_ls,
                            "yield_wt_per_bag" => $release_data->yield_wt_per_bag,
                            "planting_week" => $release_data->planting_week
                        );


                        array_push($excel_data, $arr);
                     
                    }


        

      






                $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
                 Excel::create($prv."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $prv) {
                    $excel->sheet($prv, function($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                    }); 
                })->download('xlsx');
       

        // }

        


            



     }

    


    public function exportReplacementDeliveries(){
        $list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                    ->select("tbl_delivery.seedTag","tbl_delivery.region","tbl_delivery.province","tbl_delivery.municipality","tbl_delivery.coopAccreditation","tbl_actual_delivery.batchTicketNumber","tbl_delivery.isBuffer as main_delivery", "tbl_delivery.isBuffer as main_actual", "tbl_actual_delivery.totalBagCount as inspected")
                    ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery", function($join){
                        $join->on("tbl_actual_delivery.batchTicketNumber", "=", "tbl_delivery.batchTicketNumber");
                        $join->on("tbl_actual_delivery.seedTag", "=", "tbl_delivery.seedTag");
                          
                    })
                    ->where("tbl_delivery.isBuffer", 9)
                    //->groupBy("tbl_delivery.seedTag")
                    ->orderBy("tbl_delivery.batchTicketNumber", "ASC")
                    ->orderBy("tbl_delivery.deliveryDate", "DESC")
                    ->get();

        $export_array = array();
            foreach ($list as $list_info) {
                $coopName = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("accreditation_no",$list_info->coopAccreditation)->first();
                if(count($coopName)>0){
                    array_push($export_array, array(
                    "Cooperative Name" => $coopName->coopName, 
                    "Accreditation Number" =>$coopName->accreditation_no,
                    "Batch Ticket Number" => $list_info->batchTicketNumber,
                    "Region" => $list_info->region,
                    "Province" => $list_info->province,
                    "Municipality" => $list_info->municipality,
                    "Seed Tag" => $list_info->seedTag,
                    "Total Bags" =>$list_info->inspected
                ));
                }

                
            }


            $excel_data = json_decode(json_encode($export_array), true); //convert collection to associative array to be converted to excel
            return Excel::create("Replacement Seeds"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Municipal Data", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                }); 
            })->download('xlsx');


    }


public function exportAllCoopData(){
  $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery") 
            ->select(DB::raw("SUM(tbl_delivery.totalBagCount) as total_confirmed"), "tbl_delivery.region", "tbl_delivery.province", "coopName", "tbl_delivery.coopAccreditation" )
            ->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_cooperatives.accreditation_no","=","tbl_delivery.coopAccreditation")
            // ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery", "tbl_actual_delivery.batchTicketNumber","=","tbl_delivery.batchTicketNumber")
            
            ->groupBy("tbl_delivery.coopAccreditation")
            ->groupBy("tbl_delivery.province")
            ->where("tbl_delivery.isBuffer", "!=", "9")
            ->get();

        $excel_data = array();

        foreach($data as $coop_data){
            $batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                ->select("batchTicketNumber")
                ->where("coopAccreditation", $coop_data->coopAccreditation)
                ->where("province", $coop_data->province)
                ->groupBy("batchTicketNumber")
                ->get();

            $batches = json_decode(json_encode($batches), true);    
           
            $accepted = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->whereIn("batchTicketNumber", $batches)
                ->sum("totalBagCount");
        

            array_push($excel_data,array(
                "Cooperative_name" =>$coop_data->coopName,
                "Accreditation_no" => $coop_data->coopAccreditation,
                "Region" => $coop_data->region,
                "Province" => $coop_data->province,
                "Confirmed_deliveries" => $coop_data->total_confirmed,
                "Inspected_Accepted" =>$accepted
            ));
       

        }



 return Excel::create("Coop_data"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Coop Data", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                }); 
            })->download('xlsx');



}


public function exportRegionalUI(){

    $publicDirectory = public_path("reports//excel_export_regional//");
    $return_array = array();
    if (File::exists($publicDirectory)) {
        $files = File::allFiles($publicDirectory);
        
        // Now $files contains an array of SplFileInfo objects representing the files in the directory.
        // You can loop through them or process them as needed.

    
        foreach ($files as $file) {
            $filePath = $file->getPathname(); // Full path of the file
            $fileName = $file->getFilename(); // Name of the file
            $lastModifiedTimestamp = File::lastModified($filePath);
            array_push($return_array, array(
                "file_name" => $fileName,
             
                "date_generated" => date("Y-m-d", $lastModifiedTimestamp)
            ));

        }
    }


  
    return view("reportExport.index_regional")
        ->with("files", $return_array);
}



public function exportProvincialUI(){

    $publicDirectory = public_path("reports//excel_export_provincial//");
    $return_array = array();
    if (File::exists($publicDirectory)) {
        $files = File::allFiles($publicDirectory);
        
        // Now $files contains an array of SplFileInfo objects representing the files in the directory.
        // You can loop through them or process them as needed.

    
        foreach ($files as $file) {
            $filePath = $file->getPathname(); // Full path of the file
            $fileName = $file->getFilename(); // Name of the file
            $lastModifiedTimestamp = File::lastModified($filePath);
            array_push($return_array, array(
                "file_name" => $fileName,
             
                "date_generated" => date("Y-m-d", $lastModifiedTimestamp)
            ));

        }
    }


  
    return view("reportExport.index_provincial")
        ->with("files", $return_array);
}

public function exportMunicipalUI(){

    $publicDirectory = public_path("reports//excel_export//");
    $return_array = array();
    if (File::exists($publicDirectory)) {
        $files = File::allFiles($publicDirectory);
        
        // Now $files contains an array of SplFileInfo objects representing the files in the directory.
        // You can loop through them or process them as needed.

    
        foreach ($files as $file) {
            $filePath = $file->getPathname(); // Full path of the file
            $fileName = $file->getFilename(); // Name of the file
            $lastModifiedTimestamp = File::lastModified($filePath);
            array_push($return_array, array(
                "file_name" => $fileName,
             
                "date_generated" => date("Y-m-d", $lastModifiedTimestamp)
            ));

        }
    }


  
    return view("reportExport.index");
        // ->with("files", $return_array);
}


public function exportRegionalStatistics($date_from,$date_to,$region){
    $a =[];
    $date_1 = explode("-",$date_from);
    $date_from = $date_1[0].'-'.$date_1[1].'-'.$date_1[2];
    
    $date_2 = explode("-",$date_to);
    $date_to = $date_2[0].'-'.$date_2[1].'-'.$date_2[2];
    
    
    $date_from = date("Y-m-d", strtotime($date_from));
    $date_to = date("Y-m-d", strtotime($date_to));
    
    
    //BUILD VIRTUAL RELEASE
    $release_vs_tbl = DB::table("information_schema.TABLES")
    ->where("TABLE_SCHEMA", "LIKE",$GLOBALS['season_prefix']."prv_%")
    ->where("TABLE_NAME", "new_released_virtual")
    ->where("TABLE_ROWS", ">", 0)
    ->get();

$release_vs_data  = array();
$temp = array();
// dd($release_vs_tbl);

foreach($release_vs_tbl as $vs_tbl){
        $release_vs_db = $vs_tbl->TABLE_SCHEMA.".".$vs_tbl->TABLE_NAME;
        $rel_vs =  DB::table($release_vs_db)
            ->select(DB::raw("LEFT(prv_dropoff_id,4) as prv"),DB::raw("SUM(bags_claimed) as total_claimed_bags "), DB::raw("SUM(claimed_area) as total_claimed_area "),
            DB::raw("SUM(final_area) as total_final_area"), DB::raw("SUM(IF(LEFT(sex,1) = 'M',1,0)) as total_male"), DB::raw("SUM(IF(LEFT(sex,1) = 'F',1,0)) as total_female"),
            DB::raw("SUM(IF(db_ref >0, 1, 0)) as total_farmer")
            )
            ->groupBy(DB::raw("LEFT(prv_dropoff_id,4)"))
            ->get();
        
        foreach($rel_vs as $rvs){
            $release_prv_code = $rvs->prv;
            if($rvs->prv == ""){
                $release_prv_code = substr($vs_tbl->TABLE_SCHEMA,-4) ;
            }
            
            
            if(isset($release_vs_data[$release_prv_code])){
                $release_vs_data[$release_prv_code]["claimed_bags"] += $rvs->total_claimed_bags;
                $release_vs_data[$release_prv_code]["claimed_area"] += $rvs->total_claimed_area;
                $release_vs_data[$release_prv_code]["final_area"] += $rvs->total_final_area;
                $release_vs_data[$release_prv_code]["male"] += $rvs->total_male;
                $release_vs_data[$release_prv_code]["female"] += $rvs->total_female;
                $release_vs_data[$release_prv_code]["total_farmer"] += $rvs->total_farmer;
                $release_vs_data[$release_prv_code]["others"] = $release_vs_data[$release_prv_code]["total_farmer"] - ($release_vs_data[$release_prv_code]["male"] + $release_vs_data[$release_prv_code]["female"]) ;  
            }else{
                $release_vs_data[$release_prv_code]["claimed_bags"] = $rvs->total_claimed_bags;
                $release_vs_data[$release_prv_code]["claimed_area"] = $rvs->total_claimed_area;
                $release_vs_data[$release_prv_code]["final_area"] = $rvs->total_final_area;
                $release_vs_data[$release_prv_code]["male"] = $rvs->total_male;
                $release_vs_data[$release_prv_code]["female"] = $rvs->total_female;
                $release_vs_data[$release_prv_code]["total_farmer"] = $rvs->total_farmer;
                $release_vs_data[$release_prv_code]["others"] = $release_vs_data[$release_prv_code]["total_farmer"] - ($release_vs_data[$release_prv_code]["male"] + $release_vs_data[$release_prv_code]["female"]) ;  
            }
        
        }
}


if($region == "all"){
    $region = "%";
}

$region_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point") 
->where("region", "LIKE", $region."%")

    ->where("prv", "!=", "999999")
->groupBy("region")
->orderBy("prv")
->get();        
// dd($region_list);
$a = array();
$b = array();
foreach($region_list as $region){
    $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
    ->groupBy("province")
    ->where("region", $region->region)

    // ->skip(0)
    // ->take(2)
    ->orderBy("prv")
    ->get();

    

    foreach ($data as $key => $value) {
        $eBinhi_claim = 0;
        
    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
        ->where('province', $value->province)
        ->where('is_transferred', '!=', 1)
        ->where('qrStart', '<=', 0)
        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
        ->value('total_bags');

    
        // $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        // ->select(DB::raw('SUM(totalBagCount) as total_bags'))
        // ->where('province', $value->province)
        // ->where('is_transferred', '!=', 1)
        // ->where('qrStart', '<=', 0)
        // ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
        
        // //->where('batchSeries', '=', '')
        // ->value('total_bags');
        
    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
        ->where('province', $value->province)
        ->where('transferCategory', "P")
        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
        //->where('qrStart', '<=', 0)
        //->where('batchSeries', '=', '')
        ->value('total_bags');

    $transferred_curr = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
        ->where('province', $value->province)
        ->where('transferCategory', "!=","P")
        ->where('is_transferred', 1)
        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
        //->where('qrStart', '<=', 0)
        //->where('batchSeries', '=', '')
        ->value('total_bags');
    
    $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
        ->where('province', $value->province)
        ->where('is_transferred', "!=",1)
        ->where('qrStart', '>', 0)
        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
        
        //->where('batchSeries', '=', '')
        ->value('total_bags');

    //EBINHI ----------------------

        $binhi_male = 0;
        $binhi_female = 0;
        $binhi_farmer = 0;

        $binhi_claimed_area = 0;
        $binhi_actual_area = 0;

        $eBinhi_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
            ->select("tbl_beneficiaries.*")
            ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code", "=", "tbl_beneficiaries.paymaya_code")
            ->where('tbl_beneficiaries.province', $value->province)
            //->where('tbl_beneficiaries.municipality', "BAMBANG")
            ->whereRaw("STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            ->groupBy("tbl_beneficiaries.beneficiary_id")
            ->get();

            foreach ($eBinhi_data as $key => $binhi_val) {
                if(strtoupper(substr($binhi_val->sex, 0,1)) == "M"){
                    $binhi_male++;
                }elseif(strtoupper(substr($binhi_val->sex, 0,1)) == "F"){
                    $binhi_female++;
                }
                $binhi_claimed_area += $binhi_val->area;
                $binhi_farmer++;
            }
        $binhi_other = $binhi_farmer - $binhi_male - $binhi_female;


    $eBinhi_claim = count(DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            ->where('province', $value->province)
            ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
        
            ->get());
    
            // dd($transferred_curr);
            if($eBinhi_claim <= 0 && $transferred <= 0 && $accepted <=0 && $transferred_curr <=0){
                continue;
            }
            
        
    
    $prv_tbl = $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4).".new_released";
    

    $check_final = DB::table("information_schema.TABLES")
            ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4))
            ->where("TABLE_NAME", "farmer_information_final")
            ->first();

    
    if($check_final !=null){
        $skipper = 0;
    }else{
        $skipper = 1;
    }

    if($skipper == 0){
        
        $total_male = count(DB::table($prv_tbl)
            ->where("province", $value->province)
            ->where("category", "INBRED")
            ->whereRaw("SUBSTR(sex, 1,1) Like 'M'")
            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
            ->groupby("content_rsbsa")
            ->groupby("birthdate")
            ->groupby("sex")
            ->get());
            
        $total_female = count(DB::table($prv_tbl)
        ->where("province", $value->province)
        ->where("category", "INBRED")
        ->whereRaw("SUBSTR(sex, 1,1) Like 'F'")
        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
        ->groupby("content_rsbsa")
        ->groupby("birthdate")
        ->groupby("sex")
        ->get());

        $total_farmer = count(DB::table($prv_tbl)
        ->where("province", $value->province)
        ->where("category", "INBRED")
        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
        ->groupby("content_rsbsa")
        ->groupby("birthdate")
        ->groupby("sex")
        ->get());

    
    $total_other = $total_farmer - ($total_male + $total_female);
    
    $total_bags = DB::table($prv_tbl)
        ->where("province", $value->province)
        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")        
        ->where("category", "INBRED")      
        ->sum("bags_claimed");


        $id_released =DB::table($prv_tbl)
                            ->select("new_released_id")
                            ->where("province", $value->province)
                            ->where("category", "INBRED")
                            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                            ->groupby("content_rsbsa")
                            ->groupby("birthdate")
                            ->groupby("sex")
                            ->get();
        $id_released = json_decode(json_encode($id_released), true);
                
    $total_actual_area = DB::table($prv_tbl)
        ->whereIn("new_released_id",$id_released)
        ->sum("final_area");
    

    $total_claimed_area = DB::table($prv_tbl)
        ->where("province", $value->province)
        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")    
        ->where("category", "INBRED")               
        ->sum("claimed_area");

    }else{
        $total_male = 0;
        $total_female = 0;
        $total_farmer = 0;
        $total_other = 0;
        $total_bags = 0;
        $total_actual_area = 0;
        $total_claimed_area = 0;
    }


    if($accepted==null)$accepted=0;
    if($transferred==null)$transferred=0;
    if($ebinhi==null)$ebinhi=0;
    if($transferred_curr==null)$transferred_curr=0;

    $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")->where("province", $value->province)->value('prv');
    $prv = substr($prv,0,4);
    
        if($prv != null){$psa_code = "PH".$prv."00000";}else{$psa_code = "";}

    
    
        array_push($a, array(
            "Region" => $value->region,
            "Province" => $value->province,
            "PSA code" => '',
            "Accepted and Inspected Bags (REGULAR)" => $accepted,
            "Transferred Bags (Current Season)" => $transferred_curr,
            "Transferred Bags (Previous Season)" => $transferred,
            "eBinhi Seeds" => $ebinhi,
            "Total Bags" => $accepted+$transferred+$ebinhi,
            "Total Distributed Bags (REGULAR)" => $total_bags,
            "Total Distributed Bags (eBinhi)" => $eBinhi_claim,
            "Total Distributed Bags" => $total_bags + $eBinhi_claim,
            "Farmer Beneficiaries (REGULAR)" =>$total_farmer,
            "Farmer Beneficiaries (eBinhi)" => $binhi_farmer,
            "Total Farmer Beneficiaries" => $total_farmer+$binhi_farmer,
            "Claimed area (REGULAR)" => $total_claimed_area,
            "Claimed area (eBinhi)" => $binhi_claimed_area,
            "Total Claimed Area" => $total_claimed_area+$binhi_claimed_area,
            "Actual area (REGULAR)" => $total_actual_area,
            "Actual area (eBinhi)" => $binhi_claimed_area,    
            "Total Actual Area" => $total_actual_area + $binhi_claimed_area,
            "Total Male" =>  $total_male + $binhi_male,
            "Male (REGULAR)" => $total_male,
            "Male (eBinhi)" => $binhi_male,
            "Total Female" => $total_female + $binhi_female,
            "Female (REGULAR)" => $total_female,
            "Female (eBinhi)" => $binhi_female,
            "Total Undefined" =>  $total_other + $binhi_other,
            "Undefined (REGULAR)" => $total_other,
            "Undefined (eBinhi)" => $binhi_other,
        ));    
        
            $forQuery = substr($value->prv,0,4).'%';
    
            //FOR VIRTUAL
            try {
                // $accepted_vs_added = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_virtual')
                // ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                // ->where('prv', 'LIKE', $forQuery)
                // ->whereRaw("STR_TO_DATE(date_processed, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                // ->value('total_bags');
                
            
                // $accepted_vs_deducted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_virtual')
                //     ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                //     ->where('prv_ref', 'LIKE', $forQuery)
                //     ->whereRaw("STR_TO_DATE(date_processed, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                //     ->value('total_bags');
    
                $prv_tbl_vs = $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4).".new_released_virtual";
    
                $prv_code_vs = substr($value->prv,0,4);
    
                // if($accepted_vs_added <= 0){
                //     continue;
                // }
                
                // dd($release_vs_data,$prv_code_vs);
                if(isset($release_vs_data[$prv_code_vs])){
    
                    $total_bags_vs = $release_vs_data[$prv_code_vs]["claimed_bags"];
                    $claimed_area_vs = $release_vs_data[$prv_code_vs]["claimed_area"];
                    $final_area_vs = $release_vs_data[$prv_code_vs]["final_area"];
                    $male_vs = $release_vs_data[$prv_code_vs]["male"];
                    $female_vs = $release_vs_data[$prv_code_vs]["female"];
                    $total_farmer_vs = $release_vs_data[$prv_code_vs]["total_farmer"];
                    $others_vs = $release_vs_data[$prv_code_vs]["others"];
    
                }else{
                    $total_bags_vs = 0;
                    $claimed_area_vs = 0;
                    $final_area_vs = 0;
                    $male_vs = 0;
                    $female_vs = 0;
                    $total_farmer_vs = 0;
                    $others_vs = 0;
                }
    
            
                    array_push($b, array(
                        "Region" => $value->region,
                        "Virtual Stocks Added" => $total_bags_vs,
                        // "Virtual Stocks Deducted" => $accepted_vs_deducted,
                        "Virtual Distributed" => $total_bags_vs,
                        "Virtual Claimed Area" => $claimed_area_vs,
                        "Virtual Registered Area" => $final_area_vs,
                        "Virtual Total Farmers" => $total_farmer_vs,
                        "Virtual Total Male" => $male_vs,
                        "Virtual Total Female" => $female_vs,
                        "Virtual Undefined Sex" => $others_vs,
                        
                    ));
    
            } catch (\Throwable $th) {
                //throw $th;
        
            }

    }
}

    $regions = collect($a);
    $unique_regions = $regions->pluck("Region")->unique()->values();
    $regionalData = [];

    $regionsV = collect($b);
    $unique_regionsV = $regionsV->pluck("Region")->unique()->values();
    $regionalDataV = [];

    foreach($unique_regions as $region){
        $totalAddedV = 0;
        $totalDeductedV = 0;
        $totalDistributedV = 0;
        $totalClaimedAreaV = 0;
        $totalRegisteredAreaV = 0;
        $totalFarmersV = 0;
        $totalMaleV = 0;
        $totalFemaleV = 0;
        $totalUndefinedV = 0;

        foreach($b as $row){
            if($row['Region']==$region){
                $totalAddedV += $row["Virtual Stocks Added"];
                // $totalDeductedV += $row["Virtual Stocks Deducted"];
                $totalDistributedV += $row["Virtual Distributed"];
                $totalClaimedAreaV += $row["Virtual Claimed Area"];
                $totalRegisteredAreaV += $row["Virtual Registered Area"];
                $totalFarmersV += $row["Virtual Total Farmers"];
                $totalMaleV += $row["Virtual Total Male"];
                $totalFemaleV += $row["Virtual Total Female"];
                $totalUndefinedV += $row["Virtual Undefined Sex"];
            }
        }
        array_push($regionalDataV, array(
            "Region" => $region,
            "Virtual Stocks Added" => $totalAddedV,
            // "Virtual Stocks Deducted" => $totalDeductedV,
            "Virtual Distributed" => $totalDistributedV,
            "Virtual Claimed Area" => $totalClaimedAreaV,
            "Virtual Registered Area" => $totalRegisteredAreaV,
            "Virtual Total Farmers" => $totalFarmersV,
            "Virtual Total Male" => $totalMaleV,
            "Virtual Total Female" => $totalFemaleV,
            "Virtual Undefined Sex" => $totalUndefinedV,
        ));  
    }
    
    foreach($unique_regions as $region){
        $totalAccepted = 0;
        $totalTransferred = 0;
        $totalTransferred_prev = 0;
        $totalEbinhi = 0;
        $totalBags = 0;
        $totalDistributedReg = 0;
        $totalDistributedEbinhi = 0;
        $totalDistributed = 0;
        $totalFarmersReg = 0;
        $totalFarmersEbinhi = 0;
        $totalFarmers = 0;
        $totalClaimedAreaReg = 0;
        $totalClaimedAreaEbinhi = 0;
        $totalClaimedArea = 0;
        $totalActualAreaReg = 0;
        $totalActualAreaEbinhi = 0;
        $totalActualArea = 0;
        $totalMale = 0;
        $totalMaleReg = 0;
        $totalMaleEbinhi = 0;
        $totalFemale = 0;
        $totalFemaleReg = 0;
        $totalFemaleEbinhi = 0;
        $totalUndefined = 0;
        $totalUndefinedReg = 0;
        $totalUndefinedEbinhi = 0;
        foreach($a as $row){
            if($row['Region']==$region){
                $totalAccepted += $row["Accepted and Inspected Bags (REGULAR)"];
                $totalTransferred += $row["Transferred Bags (Current Season)"];
                $totalTransferred_prev += $row["Transferred Bags (Previous Season)"];
                $totalEbinhi += $row["eBinhi Seeds"];
                $totalBags += $row["Total Bags"];
                $totalDistributedReg += $row["Total Distributed Bags (REGULAR)"];
                $totalDistributedEbinhi += $row["Total Distributed Bags (eBinhi)"];
                $totalDistributed += $row["Total Distributed Bags"];
                $totalFarmersReg += $row["Farmer Beneficiaries (REGULAR)"];
                $totalFarmersEbinhi += $row["Farmer Beneficiaries (eBinhi)"];
                $totalFarmers += $row["Total Farmer Beneficiaries"];
                $totalClaimedAreaReg += $row["Claimed area (REGULAR)"];
                $totalClaimedAreaEbinhi += $row["Claimed area (eBinhi)"];
                $totalClaimedArea += $row["Total Claimed Area"];
                $totalActualAreaReg += $row["Actual area (REGULAR)"];
                $totalActualAreaEbinhi += $row["Actual area (eBinhi)"];
                $totalActualArea += $row["Total Actual Area"];
                $totalMale += $row["Total Male"];
                $totalMaleReg += $row["Male (REGULAR)"];
                $totalMaleEbinhi += $row["Male (eBinhi)"];
                $totalFemale += $row["Total Female"];
                $totalFemaleReg += $row["Female (REGULAR)"];
                $totalFemaleEbinhi += $row["Female (eBinhi)"];
                $totalUndefined += $row["Total Undefined"];
                $totalUndefinedReg += $row["Undefined (REGULAR)"];
                $totalUndefinedEbinhi += $row["Undefined (eBinhi)"];
            }
        }
        array_push($regionalData, array(
            "Region" => $region,
            "Accepted and Inspected Bags (REGULAR)" => $totalAccepted,
            "Transferred Bags (Current Season)" => $totalTransferred,
            "Transferred Bags (Previous Season)" => $totalTransferred_prev,
            "eBinhi Seeds" => $totalEbinhi,
            "Total Bags" => $totalBags,
            "Total Distributed Bags (REGULAR)" => $totalDistributedReg,
            "Total Distributed Bags (eBinhi)" => $totalDistributedEbinhi,
            "Total Distributed Bags" => $totalDistributed,
            "Farmer Beneficiaries (REGULAR)" =>$totalFarmersReg,
            "Farmer Beneficiaries (eBinhi)" => $totalFarmersEbinhi,
            "Total Farmer Beneficiaries" => $totalFarmers,
            "Claimed area (REGULAR)" => $totalClaimedAreaReg,
            "Claimed area (eBinhi)" => $totalClaimedAreaEbinhi,
            "Total Claimed Area" => $totalClaimedArea,
            "Actual area (REGULAR)" => $totalActualAreaReg,
            "Actual area (eBinhi)" => $totalActualAreaEbinhi,
            "Total Actual Area" => $totalActualArea,
            "Total Male" =>  $totalMale,
            "Male (REGULAR)" => $totalMaleReg,
            "Male (eBinhi)" => $totalMaleEbinhi,
            "Total Female" => $totalFemale,
            "Female (REGULAR)" => $totalFemaleReg,
            "Female (eBinhi)" => $totalFemaleEbinhi,
            "Total Undefined" =>  $totalUndefined,
            "Undefined (REGULAR)" => $totalUndefinedReg,
            "Undefined (eBinhi)" => $totalUndefinedEbinhi
        ));  

    }
    // dd($regionalData);
    

    $path = public_path("reports//excel_export_regional//");
    $excel_data = json_decode(json_encode($regionalData), true); //convert collection to associative array to be converted to excel
    $excel_data_vs = json_decode(json_encode($regionalDataV), true); //convert collection to associative array to be converted to excel
    
    //  Excel::create("rs"."_".$date_from."_".$date_to, function($excel) use ($excel_data) {
    Excel::create("rs"."_".$date_from."_".$date_to, function($excel) use ($excel_data, $excel_data_vs) {
        $excel->sheet("Regional Data", function($sheet) use ($excel_data) {
            $sheet->fromArray($excel_data);
        }); 

        $excel->sheet("Virtual Stock Data", function($sheet) use ($excel_data_vs) {
            $sheet->fromArray($excel_data_vs);
        }); 
    })
    ->save('xlsx',$path);
    // ->download('xlsx');
    return "success";
}

public function exportProvincialStatistics($date_from,$date_to,$region){
    $date_1 = explode("-",$date_from);
    $date_from = $date_1[0].'-'.$date_1[1].'-'.$date_1[2];

    $date_2 = explode("-",$date_to);
    $date_to = $date_2[0].'-'.$date_2[1].'-'.$date_2[2];


    $date_from = date("Y-m-d", strtotime($date_from));
    $date_to = date("Y-m-d", strtotime($date_to));
    

    //BUILD VIRTUAL RELEASE
    $release_vs_tbl = DB::table("information_schema.TABLES")
        ->where("TABLE_SCHEMA", "LIKE",$GLOBALS['season_prefix']."prv_%")
        ->where("TABLE_NAME", "new_released_virtual")
        ->where("TABLE_ROWS", ">", 0)
        ->get();

    $release_vs_data  = array();
    $temp = array();
    // dd($release_vs_tbl);

    foreach($release_vs_tbl as $vs_tbl){
            $release_vs_db = $vs_tbl->TABLE_SCHEMA.".".$vs_tbl->TABLE_NAME;
            $rel_vs =  DB::table($release_vs_db)
                ->select(DB::raw("LEFT(prv_dropoff_id,4) as prv"),DB::raw("SUM(bags_claimed) as total_claimed_bags "), DB::raw("SUM(claimed_area) as total_claimed_area "),
                DB::raw("SUM(final_area) as total_final_area"), DB::raw("SUM(IF(LEFT(sex,1) = 'M',1,0)) as total_male"), DB::raw("SUM(IF(LEFT(sex,1) = 'F',1,0)) as total_female"),
                DB::raw("SUM(IF(db_ref >0, 1, 0)) as total_farmer")
                 )
                ->groupBy(DB::raw("LEFT(prv_dropoff_id,4)"))
                ->get();
            
            foreach($rel_vs as $rvs){
                $release_prv_code = $rvs->prv;
                if($rvs->prv == ""){
                    $release_prv_code = substr($vs_tbl->TABLE_SCHEMA,-4) ;
                }
                
                
                if(isset($release_vs_data[$release_prv_code])){
                    $release_vs_data[$release_prv_code]["claimed_bags"] += $rvs->total_claimed_bags;
                    $release_vs_data[$release_prv_code]["claimed_area"] += $rvs->total_claimed_area;
                    $release_vs_data[$release_prv_code]["final_area"] += $rvs->total_final_area;
                    $release_vs_data[$release_prv_code]["male"] += $rvs->total_male;
                    $release_vs_data[$release_prv_code]["female"] += $rvs->total_female;
                    $release_vs_data[$release_prv_code]["total_farmer"] += $rvs->total_farmer;
                    $release_vs_data[$release_prv_code]["others"] = $release_vs_data[$release_prv_code]["total_farmer"] - ($release_vs_data[$release_prv_code]["male"] + $release_vs_data[$release_prv_code]["female"]) ;  
                }else{
                    $release_vs_data[$release_prv_code]["claimed_bags"] = $rvs->total_claimed_bags;
                    $release_vs_data[$release_prv_code]["claimed_area"] = $rvs->total_claimed_area;
                    $release_vs_data[$release_prv_code]["final_area"] = $rvs->total_final_area;
                    $release_vs_data[$release_prv_code]["male"] = $rvs->total_male;
                    $release_vs_data[$release_prv_code]["female"] = $rvs->total_female;
                    $release_vs_data[$release_prv_code]["total_farmer"] = $rvs->total_farmer;
                    $release_vs_data[$release_prv_code]["others"] = $release_vs_data[$release_prv_code]["total_farmer"] - ($release_vs_data[$release_prv_code]["male"] + $release_vs_data[$release_prv_code]["female"]) ;  
                }
               
            }
    }
    

    if($region == "all"){
        $region = "%";
    }

    $region_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point") 
    ->where("region", "LIKE", $region."%")

        ->where("prv", "!=", "999999")
    ->groupBy("region")
    ->orderBy("prv")
    ->get();        
    // dd($region_list);
    $a = array();
    $b = array();
    foreach($region_list as $region){
        $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
        ->groupBy("province")
        ->where("region", $region->region)
    
        // ->skip(0)
        // ->take(2)
        ->orderBy("prv")
        ->get();

        
   
        foreach ($data as $key => $value) {
            $eBinhi_claim = 0;
            
        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            ->where('province', $value->province)
            ->where('is_transferred', '!=', 1)
            ->where('qrStart', '<=', 0)
            ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            ->value('total_bags');
       
        
            // $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            // ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            // ->where('province', $value->province)
            // ->where('is_transferred', '!=', 1)
            // ->where('qrStart', '<=', 0)
            // ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            
            // //->where('batchSeries', '=', '')
            // ->value('total_bags');
            
        $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            ->where('province', $value->province)
            ->where('transferCategory', "P")
            ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            //->where('qrStart', '<=', 0)
            //->where('batchSeries', '=', '')
            ->value('total_bags');

        $transferred_curr = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            ->where('province', $value->province)
            ->where('transferCategory', "!=","P")
            ->where('is_transferred', 1)
            ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            //->where('qrStart', '<=', 0)
            //->where('batchSeries', '=', '')
            ->value('total_bags');
           
        $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'))
            ->where('province', $value->province)
            ->where('is_transferred', "!=",1)
            ->where('qrStart', '>', 0)
            ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            
            //->where('batchSeries', '=', '')
            ->value('total_bags');

          //EBINHI ----------------------

            $binhi_male = 0;
            $binhi_female = 0;
            $binhi_farmer = 0;

            $binhi_claimed_area = 0;
            $binhi_actual_area = 0;

            $eBinhi_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                ->select("tbl_beneficiaries.*")
                ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code", "=", "tbl_beneficiaries.paymaya_code")
                ->where('tbl_beneficiaries.province', $value->province)
                //->where('tbl_beneficiaries.municipality', "BAMBANG")
                ->whereRaw("STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                ->groupBy("tbl_beneficiaries.beneficiary_id")
                ->get();

                foreach ($eBinhi_data as $key => $binhi_val) {
                    if(strtoupper(substr($binhi_val->sex, 0,1)) == "M"){
                        $binhi_male++;
                    }elseif(strtoupper(substr($binhi_val->sex, 0,1)) == "F"){
                        $binhi_female++;
                    }
                    $binhi_claimed_area += $binhi_val->area;
                    $binhi_farmer++;
                }
            $binhi_other = $binhi_farmer - $binhi_male - $binhi_female;
  

        $eBinhi_claim = count(DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                ->where('province', $value->province)
                ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
            
                ->get());
        
                // dd($transferred_curr);
                if($eBinhi_claim <= 0 && $transferred <= 0 && $accepted <=0 && $transferred_curr <=0){
                    continue;
                }
                
            
        
        $prv_tbl = $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4).".new_released";
        

        $check_final = DB::table("information_schema.TABLES")
                ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4))
                ->where("TABLE_NAME", "farmer_information_final")
                ->first();

        
        if($check_final !=null){
            $skipper = 0;
        }else{
            $skipper = 1;
        }
     
        if($skipper == 0){
            
            $total_male = count(DB::table($prv_tbl)
                ->where("province", $value->province)
                ->where("category", "INBRED")
                ->whereRaw("SUBSTR(sex, 1,1) Like 'M'")
                ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                ->groupby("content_rsbsa")
                ->groupby("birthdate")
                ->groupby("sex")
                ->get());
                
            $total_female = count(DB::table($prv_tbl)
            ->where("province", $value->province)
            ->where("category", "INBRED")
            ->whereRaw("SUBSTR(sex, 1,1) Like 'F'")
            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
            ->groupby("content_rsbsa")
            ->groupby("birthdate")
            ->groupby("sex")
            ->get());

            $total_farmer = count(DB::table($prv_tbl)
            ->where("province", $value->province)
            ->where("category", "INBRED")
            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
            ->groupby("content_rsbsa")
            ->groupby("birthdate")
            ->groupby("sex")
            ->get());

          
        $total_other = $total_farmer - ($total_male + $total_female);
        
        $total_bags = DB::table($prv_tbl)
            ->where("province", $value->province)
            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")        
            ->where("category", "INBRED")      
            ->sum("bags_claimed");
      

            $id_released =DB::table($prv_tbl)
                                ->select("new_released_id")
                                ->where("province", $value->province)
                                ->where("category", "INBRED")
                                ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                                ->groupby("content_rsbsa")
                                ->groupby("birthdate")
                                ->groupby("sex")
                                ->get();
            $id_released = json_decode(json_encode($id_released), true);
                    
        $total_actual_area = DB::table($prv_tbl)
            ->whereIn("new_released_id",$id_released)
            ->sum("final_area");
          

        $total_claimed_area = DB::table($prv_tbl)
            ->where("province", $value->province)
            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")    
            ->where("category", "INBRED")               
            ->sum("claimed_area");

        }else{
            $total_male = 0;
            $total_female = 0;
            $total_farmer = 0;
            $total_other = 0;
            $total_bags = 0;
            $total_actual_area = 0;
            $total_claimed_area = 0;
        }
       
       
        if($accepted==null)$accepted=0;
        if($transferred==null)$transferred=0;
        if($ebinhi==null)$ebinhi=0;
        if($transferred_curr==null)$transferred_curr=0;

        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")->where("province", $value->province)->value('prv');
        $prv = substr($prv,0,4);
        
            if($prv != null){$psa_code = "PH".$prv."00000";}else{$psa_code = "";}

         
         
             array_push($a, array(
                "Region" => $value->region,
                "Province" => $value->province,
                // "PSA code" => '',
                "Accepted and Inspected Bags (REGULAR)" => $accepted,
                "Transferred Bags (Current Season)" => $transferred_curr,
                "Transferred Bags (Previous Season)" => $transferred,
                "eBinhi Seeds" => $ebinhi,
                "Total Bags" => $accepted+$transferred+$ebinhi,
                "Total Distributed Bags (REGULAR)" => $total_bags,
                "Total Distributed Bags (eBinhi)" => $eBinhi_claim,
                "Total Distributed Bags" => $total_bags + $eBinhi_claim,
                "Farmer Beneficiaries (REGULAR) " =>$total_farmer,
                "Farmer Beneficiaries (eBinhi)" => $binhi_farmer,
                "Total Farmer Beneficiaries" => $total_farmer+$binhi_farmer,
                "Claimed area (REGULAR)" => $total_claimed_area,
                "Claimed area (eBinhi)" => $binhi_claimed_area,
                "Total Claimed Area" => $total_claimed_area+$binhi_claimed_area,
                "Actual area (REGULAR)" => $total_actual_area,
                "Actual area (eBinhi)" => $binhi_claimed_area,    
                "Total Actual Area" => $total_actual_area + $binhi_claimed_area,
                "Total Male" =>  $total_male + $binhi_male,
                "Male (REGULAR)" => $total_male,
                "Male (eBinhi)" => $binhi_male,
                "Total Female" => $total_female + $binhi_female,
                "Female (REGULAR)" => $total_female,
                "Female (eBinhi)" => $binhi_female,
                "Total Undefined" =>  $total_other + $binhi_other,
                "Undefined (REGULAR)" => $total_other,
                "Undefined (eBinhi)" => $binhi_other,
             ));    
             
                $forQuery = substr($value->prv,0,4).'%';
        
                 //FOR VIRTUAL
                 try {
                    // $accepted_vs_added = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_virtual')
                    // ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    // ->where('prv', 'LIKE', $forQuery)
                    // ->whereRaw("STR_TO_DATE(date_processed, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                    // ->value('total_bags');
                    
                 
                    // $accepted_vs_deducted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_virtual')
                    //     ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    //     ->where('prv_ref', 'LIKE', $forQuery)
                    //     ->whereRaw("STR_TO_DATE(date_processed, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                    //     ->value('total_bags');
        
                    $prv_tbl_vs = $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4).".new_released_virtual";
        
                    $prv_code_vs = substr($value->prv,0,4);
        
                    // if($accepted_vs_added <= 0){
                    //     continue;
                    // }
                    
                    // dd($release_vs_data,$prv_code_vs);
                    if(isset($release_vs_data[$prv_code_vs])){
        
                        $total_bags_vs = $release_vs_data[$prv_code_vs]["claimed_bags"];
                        $claimed_area_vs = $release_vs_data[$prv_code_vs]["claimed_area"];
                        $final_area_vs = $release_vs_data[$prv_code_vs]["final_area"];
                        $male_vs = $release_vs_data[$prv_code_vs]["male"];
                        $female_vs = $release_vs_data[$prv_code_vs]["female"];
                        $total_farmer_vs = $release_vs_data[$prv_code_vs]["total_farmer"];
                        $others_vs = $release_vs_data[$prv_code_vs]["others"];
        
                    }else{
                        $total_bags_vs = 0;
                        $claimed_area_vs = 0;
                        $final_area_vs = 0;
                        $male_vs = 0;
                        $female_vs = 0;
                        $total_farmer_vs = 0;
                        $others_vs = 0;
                    }
        
                  
                        array_push($b, array(
                            "Region" => $value->region,
                            "Province" => $value->province,
                            // "PSA code" => $psa_code,
                            "Virtual Stocks Added" => $total_bags_vs,
                            // "Virtual Stocks Deducted" => $accepted_vs_deducted,
                            "Virtual Distributed" => $total_bags_vs,
                            "Virtual Claimed Area" => $claimed_area_vs,
                            "Virtual Registered Area" => $final_area_vs,
                            "Virtual Total Farmers" => $total_farmer_vs,
                            "Virtual Total Male" => $male_vs,
                            "Virtual Total Female" => $female_vs,
                            "Virtual Undefined Sex" => $others_vs,
                            
                        ));
        
                } catch (\Throwable $th) {
                    //throw $th;
               
                }
    
        }
    }
    
    // dd($release_vs_data,$b);
    $path = public_path("reports//excel_export_provincial//");
    $excel_data = json_decode(json_encode($a), true); //convert collection to associative array to be converted to excel
    $excel_data_vs = json_decode(json_encode($b), true); //convert collection to associative array to be converted to excel
    
     Excel::create("ps"."_".$date_from."_".$date_to, function($excel) use ($excel_data, $excel_data_vs) {
        $excel->sheet("Provincial Data", function($sheet) use ($excel_data) {
            $sheet->fromArray($excel_data);
        }); 

        $excel->sheet("Virtual Stock Data", function($sheet) use ($excel_data_vs) {
            $sheet->fromArray($excel_data_vs);
        }); 
    })
     ->save('xlsx',$path);
    // ->download('xlsx');
    return "success";
    
}

 public function exportMunicipalStatistics($date_from, $date_to, $region){
        
                $date_1 = explode("-",$date_from);
                $date_from = $date_1[0].'-'.$date_1[1].'-'.$date_1[2];

                $date_2 = explode("-",$date_to);
                $date_to = $date_2[0].'-'.$date_2[1].'-'.$date_2[2];


                $date_from = date("Y-m-d", strtotime($date_from));
                $date_to = date("Y-m-d", strtotime($date_to));
                

                //BUILD VIRTUAL RELEASE
                $release_vs_tbl = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", "LIKE",$GLOBALS['season_prefix']."prv_%")
                    ->where("TABLE_NAME", "new_released_virtual")
                    ->where("TABLE_ROWS", ">", 0)
                    ->get();

                $release_vs_data  = array();

                foreach($release_vs_tbl as $vs_tbl){
                        $release_vs_db = $vs_tbl->TABLE_SCHEMA.".".$vs_tbl->TABLE_NAME;
                        $rel_vs =  DB::table($release_vs_db)
                            ->select(DB::raw("LEFT(prv_dropoff_id,6) as prv"),DB::raw("SUM(bags_claimed) as total_claimed_bags "), DB::raw("SUM(claimed_area) as total_claimed_area "),
                            DB::raw("SUM(final_area) as total_final_area"), DB::raw("SUM(IF(LEFT(sex,1) = 'M',1,0)) as total_male"), DB::raw("SUM(IF(LEFT(sex,1) = 'F',1,0)) as total_female"),
                            DB::raw("SUM(IF(db_ref >0, 1, 0)) as total_farmer")
                            )
                            ->groupBy(DB::raw("LEFT(prv_dropoff_id,6)"))
                            ->get();
                        
                        foreach($rel_vs as $rvs){
                            $release_prv_code = $rvs->prv;
                            if($rvs->prv == ""){
                                $release_prv_code = "NA" ;
                            }
                            
                            if(isset($release_vs_data[$release_prv_code])){
                                $release_vs_data[$release_prv_code]["claimed_bags"] += $rvs->total_claimed_bags;
                                $release_vs_data[$release_prv_code]["claimed_area"] += $rvs->total_claimed_area;
                                $release_vs_data[$release_prv_code]["final_area"] += $rvs->total_final_area;
                                $release_vs_data[$release_prv_code]["male"] += $rvs->total_male;
                                $release_vs_data[$release_prv_code]["female"] += $rvs->total_female;
                                $release_vs_data[$release_prv_code]["total_farmer"] += $rvs->total_farmer;
                                $release_vs_data[$release_prv_code]["others"] = $release_vs_data[$release_prv_code]["total_farmer"] - ($release_vs_data[$release_prv_code]["male"] + $release_vs_data[$release_prv_code]["female"]) ;  
                            }else{
                                $release_vs_data[$release_prv_code]["claimed_bags"] = $rvs->total_claimed_bags;
                                $release_vs_data[$release_prv_code]["claimed_area"] = $rvs->total_claimed_area;
                                $release_vs_data[$release_prv_code]["final_area"] = $rvs->total_final_area;
                                $release_vs_data[$release_prv_code]["male"] = $rvs->total_male;
                                $release_vs_data[$release_prv_code]["female"] = $rvs->total_female;
                                $release_vs_data[$release_prv_code]["total_farmer"] = $rvs->total_farmer;
                                $release_vs_data[$release_prv_code]["others"] = $release_vs_data[$release_prv_code]["total_farmer"] - ($release_vs_data[$release_prv_code]["male"] + $release_vs_data[$release_prv_code]["female"]) ;  
                            }
                        }
                }


            




                if($region == "all"){
                    $region = "%";
                }

                $region_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point") 
                ->where("region", "LIKE", $region."%")

                    ->where("prv", "!=", "999999")
                ->groupBy("region")
                ->orderBy("prv")
                ->get();        
                // dd($region_list);
                $a = array();
                $b = array();
                foreach($region_list as $region){
                    $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                    ->groupBy("province")
                    ->groupBy("municipality")
                    ->where("region", $region->region)
                
                    // ->skip(0)
                    // ->take(2)
                    ->orderBy("prv")
                    ->get();

                    // dd($data);
            
                    foreach ($data as $key => $value) {
                        $eBinhi_claim = 0;
                        




                    $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $value->province)
                        ->where('municipality', $value->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                        ->value('total_bags');
                
                    
                        $accepted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $value->province)
                        ->where('municipality', $value->municipality)
                        ->where('is_transferred', '!=', 1)
                        ->where('qrStart', '<=', 0)
                        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                        
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                
                    

                    $transferred = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $value->province)
                        ->where('municipality', $value->municipality)
                        ->where('transferCategory', "P")
                        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    
                

                    $transferred_curr = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $value->province)
                        ->where('municipality', $value->municipality)
                        ->where('transferCategory', "!=","P")
                        ->where('is_transferred', 1)
                        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                        //->where('qrStart', '<=', 0)
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                    

                    $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                        ->where('province', $value->province)
                        ->where('municipality', $value->municipality)
                        ->where('is_transferred', "!=",1)
                        ->where('qrStart', '>', 0)
                        ->whereRaw("STR_TO_DATE(dateCreated, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                        
                        //->where('batchSeries', '=', '')
                        ->value('total_bags');
                
                    

                    //EBINHI ----------------------

                        $binhi_male = 0;
                        $binhi_female = 0;
                        $binhi_farmer = 0;

                        $binhi_claimed_area = 0;
                        $binhi_actual_area = 0;



                        $eBinhi_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                            // ->select("tbl_claim.*,tbl_beneficiaries.*")
                            ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries", "tbl_claim.paymaya_code", "=", "tbl_beneficiaries.paymaya_code")
                            ->where('tbl_claim.province', $value->province)
                            ->where('tbl_claim.municipality', $value->municipality)
                            //->where('tbl_beneficiaries.municipality', "BAMBANG")
                            ->whereRaw("STR_TO_DATE(tbl_claim.date_created, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                            ->groupBy("tbl_claim.beneficiary_id")
                            ->get();

                            foreach ($eBinhi_data as $key => $binhi_val) {
                                if(strtoupper(substr($binhi_val->sex, 0,1)) == "M"){
                                    $binhi_male++;
                                }elseif(strtoupper(substr($binhi_val->sex, 0,1)) == "F"){
                                    $binhi_female++;
                                }
                                $binhi_claimed_area += $binhi_val->area;
                                $binhi_farmer++;
                            }
                        $binhi_other = $binhi_farmer - $binhi_male - $binhi_female;
            

                    $eBinhi_claim = count(DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                            ->where('province', $value->province)
                            ->where('municipality', $value->municipality)
                            ->whereRaw("STR_TO_DATE(date_created, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                        
                            ->get());
                    
                    
            


                            // dd($transferred_curr);
                            if($eBinhi_claim <= 0 && $transferred <= 0 && $accepted <=0 && $transferred_curr <=0){
                                continue;
                            }
                            
                        
                    
                    $prv_tbl = $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4).".new_released";


                        
                    $check_final = DB::table("information_schema.TABLES")
                            ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4))
                            ->where("TABLE_NAME", "farmer_information_final")
                            ->first();
                    if($check_final !=null){
                        $skipper = 0;
                    }else{
                        $skipper = 1;
                    }
                
                    if($skipper == 0){
                        $total_male = count(DB::table($prv_tbl)
                            ->where("municipality", $value->municipality)
                            ->where("category", "INBRED")
                            ->whereRaw("SUBSTR(sex, 1,1) Like 'M'")
                            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                            ->groupby("content_rsbsa")
                            ->groupby("birthdate")
                            ->groupby("sex")
                            ->get());

                        $total_female = count(DB::table($prv_tbl)
                        ->where("municipality", $value->municipality)
                        ->where("category", "INBRED")
                        ->whereRaw("SUBSTR(sex, 1,1) Like 'F'")
                        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                        ->groupby("content_rsbsa")
                        ->groupby("birthdate")
                        ->groupby("sex")
                        ->get());

                        $total_farmer = count(DB::table($prv_tbl)
                        ->where("municipality", $value->municipality)
                        ->where("category", "INBRED")
                        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                        ->groupby("content_rsbsa")
                        ->groupby("birthdate")
                        ->groupby("sex")
                        ->get());

                    
                    $total_other = $total_farmer - ($total_male + $total_female);

                    
                    $total_bags = DB::table($prv_tbl)
                        ->where("municipality", $value->municipality)
                        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")        
                        ->where("category", "INBRED")      
                        ->sum("bags_claimed");
                

                        $id_released =DB::table($prv_tbl)
                                            ->select("new_released_id")
                                            ->where("municipality", $value->municipality)
                                            ->where("category", "INBRED")
                                            ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')") 
                                            ->groupby("content_rsbsa")
                                            ->groupby("birthdate")
                                            ->groupby("sex")
                                            ->get();
                        $id_released = json_decode(json_encode($id_released), true);
                                
                    $total_actual_area = DB::table($prv_tbl)
                        ->whereIn("new_released_id",$id_released)
                        ->sum("final_area");
                    

                    $total_claimed_area = DB::table($prv_tbl)
                        ->where("municipality", $value->municipality)
                        ->whereRaw("STR_TO_DATE(date_released, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")    
                        ->where("category", "INBRED")               
                        ->sum("claimed_area");

                    }else{
                        $total_male = 0;
                        $total_female = 0;
                        $total_farmer = 0;
                        $total_other = 0;
                        $total_bags = 0;
                        $total_actual_area = 0;
                        $total_claimed_area = 0;
                    }
                
                
                    if($accepted==null)$accepted=0;
                    if($transferred==null)$transferred=0;
                    if($ebinhi==null)$ebinhi=0;
                    if($transferred_curr==null)$transferred_curr=0;






                        $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")->where("province", $value->province)->where("municipality", $value->municipality)->value('prv');
                        if($prv != null){
                            $psa_code = "PH".$prv."000";}else{$psa_code = "";
                            }
                        
                        $getUpdatedPsaCode = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.;lib_prv")->where("psa_code", $psa_code)->first();
                        if($getUpdatedPsaCode == null){
                            $updated_psa_code = "";
                        }
                        else{
                            $updated_psa_code = $getUpdatedPsaCode->updated_psa_code;
                        }
                            
                    
                    
                        array_push($a, array(
                            "Region" => $value->region,
                            "Province" => $value->province,
                            "Municipality" => $value->municipality,
                            "PSA code" => $psa_code,
                            "Updated PSA Code" => $updated_psa_code,
                            "Accepted and Inspected Bags (REGULAR)" => $accepted,
                            "Transferred Bags (Current Season)" => $transferred_curr,
                            "Transferred Bags (Previous Season)" => $transferred,
                            "eBinhi Seeds" => $ebinhi,
                            "Total Bags" => $accepted+$transferred+$ebinhi,
                            "Total Distributed Bags (REGULAR)" => $total_bags,
                            "Total Distributed Bags (eBinhi)" => $eBinhi_claim,
                            "Total Distributed Bags" => $total_bags + $eBinhi_claim,
                            "Farmer Beneficiaries (REGULAR) " =>$total_farmer,
                            "Farmer Beneficiaries (eBinhi)" => $binhi_farmer,
                            "Total Farmer Beneficiaries" => $total_farmer+$binhi_farmer,
                            "Claimed area (REGULAR)" => $total_claimed_area,
                            "Claimed area (eBinhi)" => $binhi_claimed_area,
                            "Total Claimed Area" => $total_claimed_area+$binhi_claimed_area,
                            "Actual area (REGULAR)" => $total_actual_area,
                            "Actual area (eBinhi)" => $binhi_claimed_area,    
                            "Total Actual Area" => $total_actual_area + $binhi_claimed_area,
                            "Total Male" =>  $total_male + $binhi_male,
                            "Male (REGULAR)" => $total_male,
                            "Male (eBinhi)" => $binhi_male,
                            "Total Female" => $total_female + $binhi_female,
                            "Female (REGULAR)" => $total_female,
                            "Female (eBinhi)" => $binhi_female,
                            "Total Undefined" =>  $total_other + $binhi_other,
                            "Undefined (REGULAR)" => $total_other,
                            "Undefined (eBinhi)" => $binhi_other,
                        ));    
                        


                        
            


                            //FOR VIRTUAL
                            try {
                                // $accepted_vs_added = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_virtual')
                                // ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                                // ->where('prv', $value->prv)
                                // ->whereRaw("STR_TO_DATE(date_processed, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                                // ->value('total_bags');
                                
                            
                                $accepted_vs_deducted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_virtual')
                                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                                    ->where('prv_ref', $value->prv)
                                    ->whereRaw("STR_TO_DATE(date_processed, '%Y-%m-%d') BETWEEN  STR_TO_DATE('".$date_from."', '%Y-%m-%d')  AND STR_TO_DATE('".$date_to."', '%Y-%m-%d')")
                                    ->value('total_bags');
                    
                                $prv_tbl_vs = $GLOBALS['season_prefix']."prv_".substr($value->prv,0,4).".new_released_virtual";
                    
                                $prv_code_vs = $value->prv;
                    
                                // if($accepted_vs_added <= 0){
                                //     continue;
                                // }
                                
                                
                                if(isset($release_vs_data[$prv_code_vs])){
                    
                                    $total_bags_vs = $release_vs_data[$prv_code_vs]["claimed_bags"];
                                    $claimed_area_vs = $release_vs_data[$prv_code_vs]["claimed_area"];
                                    $final_area_vs = $release_vs_data[$prv_code_vs]["final_area"];
                                    $male_vs = $release_vs_data[$prv_code_vs]["male"];
                                    $female_vs = $release_vs_data[$prv_code_vs]["female"];
                                    $total_farmer_vs = $release_vs_data[$prv_code_vs]["total_farmer"];
                                    $others_vs = $release_vs_data[$prv_code_vs]["others"];
                    
                                }else{
                                    $total_bags_vs = 0;
                                    $claimed_area_vs = 0;
                                    $final_area_vs = 0;
                                    $male_vs = 0;
                                    $female_vs = 0;
                                    $total_farmer_vs = 0;
                                    $others_vs = 0;
                                }
                    
                            
                                    array_push($b, array(
                                        "Region" => $value->region,
                                        "Province" => $value->province,
                                        "Municipality" => $value->municipality,
                                        "PSA code" => $psa_code,
                                        "Virtual Stocks Added" => $total_bags_vs,
                                        "Virtual Stocks Deducted" => $accepted_vs_deducted,
                                        "Virtual Distributed" => $total_bags_vs,
                                        "Virtual Claimed Area" => $claimed_area_vs,
                                        "Virtual Registered Area" => $final_area_vs,
                                        "Virtual Total Farmers" => $total_farmer_vs,
                                        "Virtual Total Male" => $male_vs,
                                        "Virtual Total Female" => $female_vs,
                                        "Virtual Undefined Sex" => $others_vs,
                                        
                                    ));
                    
                                
                            
                    
                    
                    
                    
                            } catch (\Throwable $th) {
                                //throw $th;
                                
                                
                            }
                    













                    }








                }
                
            
                $path = public_path("reports//excel_export//");
                $excel_data = json_decode(json_encode($a), true); //convert collection to associative array to be converted to excel
                $excel_data_vs = json_decode(json_encode($b), true); //convert collection to associative array to be converted to excel
                
                Excel::create("ms"."_".$date_from."_".$date_to, function($excel) use ($excel_data, $excel_data_vs) {
                    $excel->sheet("Municipal Data", function($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                    }); 

                    $excel->sheet("Virtual Stock Data", function($sheet) use ($excel_data_vs) {
                        $sheet->fromArray($excel_data_vs);
                    }); 
                })
                ->save('xlsx',$path);
                // ->download('xlsx');
                return "success";
                    


}










     public function set_rpt_db($conName,$database_name,$host,$port,$user,$pass){
        try {
            \Config::set('database.connections.'.$conName.'.database', $database_name);
            \Config::set('database.connections.'.$conName.'.host', $host);
            \Config::set('database.connections.'.$conName.'.port', $port);
            \Config::set('database.connections.'.$conName.'.username', $user);
            \Config::set('database.connections.'.$conName.'.password', $pass);
            DB::purge($conName);

            DB::connection($conName)->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }

    public function reportExportVariety($region){
        //dd($region);
        $summary_array = array();


         $summary_array["A"] = array(
                                    "province" => $region,
                                    "variety" =>"",
                                    "accepted_count" =>"",
                                    "distbag" => "",
                                    "farmerDistributed" => ""
                                    );

         //WS2020
         $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection','localhost','4406','rcef_user','SKF9wzFtKmNMfwyz');
        $excel_array_ws2020 = array(); 
        $provinceList = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
            ->where("region", $region)
            ->where("batchSeries", "")
         //   ->where("batchTicketNumber", "!=", "TRANSFER")
            ->groupBy("province")
            ->orderBy("province")
            ->get();

           array_push($excel_array_ws2020, array(
                            "province" => $region,
                            "variety" => "",
                            "accepted_count" => "",
                            "distbag" => "",
                            "farmerDistributed" => ""
                        ));

            foreach ($provinceList as $province) {

                $variety_list = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                    ->where("region", $province->region)
                    ->where("province", $province->province)
                    ->where("batchSeries", "")
                 //   ->where("batchTicketNumber", "!=", "TRANSFER")
                    ->groupBy("seedVariety")
                    ->orderBy("province", "ASC")
                    ->orderBy("seedVariety", "ASC")
                    ->get();
                    foreach ($variety_list as $variety) {
                        $variety_count = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                            ->where("region", $province->region)
                            ->where("province", $province->province)
                            ->where("seedVariety", $variety->seedVariety)
                            ->where("batchSeries", "")
                       //     ->where("batchTicketNumber", "!=", "TRANSFER")
                            ->sum("totalBagCount");

                        if($variety_count>0){
                            $prv = substr($variety->prv, 0,4);
                            $prv_db = $GLOBALS['season_prefix']."prv_".$prv;
                           // dd($prv_db);
                            $con = $this->set_rpt_db('ls_inspection_db',$prv_db,'localhost','4406','rcef_user','SKF9wzFtKmNMfwyz');


                            
                            $distbag = DB::connection("ls_inspection_db")->table("released")
                                    ->where("seed_variety", $variety->seedVariety)
                                    ->where("province", $province->province)
                                    ->sum("bags");

                                if($distbag>0){
                                    $farmerDistributed = DB::connection("ls_inspection_db")->table("released")
                                    ->where("seed_variety", $variety->seedVariety)
                                    ->where("province", $province->province)
                                    ->count("rsbsa_control_no");

                                }else{
                                    $distbag =0;
                                    $farmerDistributed=0;
                                }
                        }else{
                            $variety_count = 0;
                            $distbag = 0;
                            $farmerDistributed = 0;
                        }

                        array_push($excel_array_ws2020, array(
                            "province" => $province->province,
                            "variety" => $variety->seedVariety,
                            "accepted_count" => $variety_count,
                            "distbag" => $distbag,
                            "farmerDistributed" => $farmerDistributed
                        ));  

                        $indexchecker = $province->province.$variety->seedVariety;
                        $indexchecker = strtoupper($indexchecker);
                        if(isset($summary_array[$indexchecker])){
                            $summary_array[$indexchecker]["accepted_count"] = intval($summary_array[$indexchecker]["accepted_count"]) + intval($variety_count); 
                            $summary_array[$indexchecker]["accepted_count"] = intval($summary_array[$indexchecker]["accepted_count"]) + intval($variety_count); 
                            $summary_array[$indexchecker]["distbag"] = intval($summary_array[$indexchecker]["distbag"]) + intval($distbag); 
                            $summary_array[$indexchecker]["farmerDistributed"] = intval($summary_array[$indexchecker]["farmerDistributed"]) + intval($farmerDistributed); 
                        }else{
                               $summary_array[$indexchecker] =  array(
                                    "province" => $province->province,
                                    "variety" => $variety->seedVariety,
                                    "accepted_count" => $variety_count,
                                    "distbag" => $distbag,
                                    "farmerDistributed" => $farmerDistributed
                                    ); 

                        }
                         
                        $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection','localhost','4406','rcef_user','SKF9wzFtKmNMfwyz');
                    }
                    $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection','localhost','4406','rcef_user','SKF9wzFtKmNMfwyz');
            }

          

        //DS2021
        $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection','192.168.10.23','3306','rcef_web','SKF9wzFtKmNMfwy');
        $excel_array_ds2021 = array(); 
        $provinceList = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
            ->where("region", $region)
            ->where("batchSeries", "")
          //  ->where("batchTicketNumber", "!=", "TRANSFER")
            ->groupBy("province")
            ->orderBy("province")
            ->get();

           array_push($excel_array_ds2021, array(
                            "province" => $region,
                            "variety" => "",
                            "accepted_count" => "",
                            "distbag" => "",
                            "farmerDistributed" => ""
                        ));
            foreach ($provinceList as $province) {

                $variety_list = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                    ->where("region", $province->region)
                    ->where("province", $province->province)
                    ->where("batchSeries", "")
                   // ->where("batchTicketNumber", "!=", "TRANSFER")
                    ->groupBy("seedVariety")
                    ->orderBy("province", "ASC")
                    ->orderBy("seedVariety", "ASC")
                    ->get();
                    foreach ($variety_list as $variety) {
                        $variety_count = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                            ->where("region", $province->region)
                            ->where("province", $province->province)
                            ->where("seedVariety", $variety->seedVariety)
                            ->where("batchSeries", "")
                           // ->where("batchTicketNumber", "!=", "TRANSFER")
                            ->sum("totalBagCount");

                           

                        if($variety_count>0){
                            $prv = substr($variety->prv, 0,4);
                            $prv_db = $GLOBALS['season_prefix']."prv_".$prv;
                           // dd($prv_db);
                             $con = $this->set_rpt_db('ls_inspection_db',$prv_db,'192.168.10.23','3306','rcef_web','SKF9wzFtKmNMfwy');

                            
                            $distbag = DB::connection("ls_inspection_db")->table("released")
                                    ->where("seed_variety", $variety->seedVariety)
                                    ->where("province", $province->province)
                                    ->sum("bags");

                                if($distbag>0){
                                    $farmerDistributed = DB::connection("ls_inspection_db")->table("released")
                                    ->where("seed_variety", $variety->seedVariety)
                                    ->where("province", $province->province)
                                    ->count("rsbsa_control_no");

                                }else{
                                    $distbag =0;
                                    $farmerDistributed=0;
                                }
                        }else{
                            $variety_count = 0;
                            $distbag = 0;
                            $farmerDistributed = 0;
                        }

                        array_push($excel_array_ds2021, array(
                            "province" => $province->province,
                            "variety" => $variety->seedVariety,
                            "accepted_count" => $variety_count,
                            "distbag" => $distbag,
                            "farmerDistributed" => $farmerDistributed
                        ));  

                        $indexchecker = $province->province.$variety->seedVariety;
                        $indexchecker = strtoupper($indexchecker);
                        if(isset($summary_array[$indexchecker])){
                            $summary_array[$indexchecker]["accepted_count"] = intval($summary_array[$indexchecker]["accepted_count"]) + intval($variety_count); 
                            $summary_array[$indexchecker]["distbag"] = intval($summary_array[$indexchecker]["distbag"]) + intval($distbag); 
                            $summary_array[$indexchecker]["farmerDistributed"] = intval($summary_array[$indexchecker]["farmerDistributed"]) + intval($farmerDistributed); 
                        }else{
                               $summary_array[$indexchecker] =  array(
                                    "province" => $province->province,
                                    "variety" => $variety->seedVariety,
                                    "accepted_count" => $variety_count,
                                    "distbag" => $distbag,
                                    "farmerDistributed" => $farmerDistributed
                                    ); 

                        }


                       $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection','192.168.10.23','3306','rcef_web','SKF9wzFtKmNMfwy');
                    }
                    $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection','192.168.10.23','3306','rcef_web','SKF9wzFtKmNMfwy');
            }

$excel_array = array();
            /*
        //WS2021
         
        $provinceList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
            ->where("region", $region)
            ->where("qrStart", "<=", 0)
          //  ->where("transferCategory", "!=", "P")
            ->groupBy("province")
            ->orderBy("province")
            ->get();
           array_push($excel_array, array(
                            "province" => $region,
                            "variety" => "",
                            "accepted_count" => "",
                            "distbag" => "",
                            "farmerDistributed" => ""
                        ));
            foreach ($provinceList as $province) {
                $variety_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->where("region", $province->region)
                    ->where("province", $province->province)
                    ->where("qrStart", "<=", 0)
                  // ->where("transferCategory", "!=", "P")
                    ->groupBy("seedVariety")
                    ->orderBy("province", "ASC")
                    ->orderBy("seedVariety", "ASC")
                    ->get();
                    foreach ($variety_list as $variety) {
                        $variety_count = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                            ->where("region", $province->region)
                            ->where("province", $province->province)
                            ->where("seedVariety", $variety->seedVariety)
                            ->where("qrStart", "<=", 0)
                         //   ->where("transferCategory", "!=", "P")
                            ->sum("totalBagCount");

                        if($variety_count>0){
                            $prv = substr($variety->prv, 0,4);
                            $distbag = DB::table($GLOBALS['season_prefix']."prv_".$prv.".released")
                                    ->where("seed_variety", $variety->seedVariety)
                                    ->where("province", $province->province)
                                    ->sum("bags");
                                if($distbag>0){
                                    $farmerDistributed = DB::table($GLOBALS['season_prefix']."prv_".$prv.".released")
                                    ->where("seed_variety", $variety->seedVariety)
                                    ->where("province", $province->province)
                                    ->count("rsbsa_control_no");
                                }else{
                                    $distbag =0;
                                    $farmerDistributed=0;
                                }
                        }else{
                            $variety_count = 0;
                            $distbag = 0;
                            $farmerDistributed = 0;
                        }

                        array_push($excel_array, array(
                            "province" => $province->province,
                            "variety" => $variety->seedVariety,
                            "accepted_count" => $variety_count,
                            "distbag" => $distbag,
                            "farmerDistributed" => $farmerDistributed
                        ));  


                        $indexchecker = $province->province.$variety->seedVariety;
                        $indexchecker = strtoupper($indexchecker);
                        if(isset($summary_array[$indexchecker])){


                            $summary_array[$indexchecker]["accepted_count"] = intval($summary_array[$indexchecker]["accepted_count"]) + intval($variety_count); 
                            $summary_array[$indexchecker]["distbag"] = intval($summary_array[$indexchecker]["distbag"]) + intval($distbag); 
                            $summary_array[$indexchecker]["farmerDistributed"] = intval($summary_array[$indexchecker]["farmerDistributed"]) + intval($farmerDistributed); 
                        }else{
                               $summary_array[$indexchecker] =  array(
                                    "province" => $province->province,
                                    "variety" => $variety->seedVariety,
                                    "accepted_count" => $variety_count,
                                    "distbag" => $distbag,
                                    "farmerDistributed" => $farmerDistributed
                                    ); 

                        }
                    }
            }
            */

            //dd($excel_array);

              $myFile = Excel::create('SEED_VARIETY_REPORT_'.date("Y-m-d H_i_s"), function($excel) use ($excel_array,$excel_array_ds2021,$excel_array_ws2020,$summary_array) {

            $excel->sheet("WS2020", function($sheet) use ($excel_array_ws2020) {
                $sheet->prependRow(1, array(                
                               "REGION/PROVINCE", "VARIETY","TOTAL ACCEPTED BAGS", "TOTAL DISTRIBUTED", "TOTAL FARMER"));
                $row = 2;
                $province = "";
                 foreach ($excel_array_ws2020 as $key => $value) {           
                       if($value["variety"]=="" OR $value["accepted_count"]==""){
                             $sheet->row($row,$value);  
                                $sheet->cells("A".$row.":E".$row, function ($cells){
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#cfcfcf');
                                }); 

                             $row++;
                       }else{
                            if($province == $value["province"]){
                                $value["province"] = "";
                                $sheet->row($row,$value);
                                $row++;
                            }else{
                                $province = $value["province"]; 
                                $sheet->cells("A".$row, function ($cells){
                                    $cells->setAlignment('right');
                                    $cells->setFontWeight('bold');
                                }); 
                                $sheet->row($row,$value);
                                $row++;
                            }
                            
                       }  
                    }

                    $sheet->cells("A1:N1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                }); 
                $sheet->freezeFirstRow();
                $lrow = $row;
                //$range = "C2:E".$lrow;

                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  30,
                    'D'     =>  20,
                    'E'     =>  20,
                ));
                //$sheet->setColumnFormat(array($range => '#,##0.00_-'));
            });


            $excel->sheet("DS2021", function($sheet) use ($excel_array_ds2021) {
                $sheet->prependRow(1, array(                
                               "REGION/PROVINCE", "VARIETY","TOTAL ACCEPTED BAGS", "TOTAL DISTRIBUTED", "TOTAL FARMER"));
                $row = 2;
                $province = "";
                 foreach ($excel_array_ds2021 as $key => $value) {           
                       if($value["variety"]=="" OR $value["accepted_count"]==""){
                             $sheet->row($row,$value);  
                                $sheet->cells("A".$row.":E".$row, function ($cells){
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#cfcfcf');
                                }); 

                             $row++;
                       }else{
                            if($province == $value["province"]){
                                $value["province"] = "";
                                $sheet->row($row,$value);
                                $row++;
                            }else{
                                $province = $value["province"]; 
                                $sheet->cells("A".$row, function ($cells){
                                    $cells->setAlignment('right');
                                    $cells->setFontWeight('bold');
                                }); 
                                $sheet->row($row,$value);
                                $row++;
                            }
                            
                       }  
                    }

                    $sheet->cells("A1:N1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                }); 
                $sheet->freezeFirstRow();
                $lrow = $row;
                //$range = "C2:E".$lrow;

                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  30,
                    'D'     =>  20,
                    'E'     =>  20,
                ));
                //$sheet->setColumnFormat(array($range => '#,##0.00_-'));
            });

            /*
            $excel->sheet("WS2021", function($sheet) use ($excel_array) {
                $sheet->prependRow(1, array(                
                               "REGION/PROVINCE", "VARIETY","TOTAL ACCEPTED BAGS", "TOTAL DISTRIBUTED", "TOTAL FARMER"));
                $row = 2;
                $province = "";
                 foreach ($excel_array as $key => $value) {           
                       if($value["variety"]=="" OR $value["accepted_count"]==""){
                             $sheet->row($row,$value);  
                                $sheet->cells("A".$row.":E".$row, function ($cells){
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#cfcfcf');
                                }); 

                             $row++;
                       }else{
                            if($province == $value["province"]){
                                $value["province"] = "";
                                $sheet->row($row,$value);
                                $row++;
                            }else{
                                $province = $value["province"]; 
                                $sheet->cells("A".$row, function ($cells){
                                    $cells->setAlignment('right');
                                    $cells->setFontWeight('bold');
                                }); 
                                $sheet->row($row,$value);
                                $row++;
                            }
                            
                       }  
                    }

                    $sheet->cells("A1:N1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                }); 
                $sheet->freezeFirstRow();
                $lrow = $row;
                //$range = "C2:E".$lrow;

                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  30,
                    'D'     =>  20,
                    'E'     =>  20,
                ));
                //$sheet->setColumnFormat(array($range => '#,##0.00_-'));
            }); */


            $excel->sheet("SUMMARY", function($sheet) use ($summary_array) {
                ksort($summary_array);
               
                $sheet->prependRow(1, array(                
                               "REGION/PROVINCE", "VARIETY","TOTAL ACCEPTED BAGS", "TOTAL DISTRIBUTED", "TOTAL FARMER"));
                $row = 2;
                $province = "";

                      foreach ($summary_array as $key => $value) {
                             if($value["variety"]=="" OR $value["accepted_count"]==""){
                             $sheet->row($row,$value);  
                                $sheet->cells("A".$row.":E".$row, function ($cells){
                                    $cells->setFontWeight('bold');
                                    $cells->setBackground('#cfcfcf');
                                }); 

                             $row++;
                       }else{
                            if($province == $value["province"]){
                                $value["province"] = "";
                                $sheet->row($row,$value);
                                $row++;
                            }else{
                                $province = $value["province"]; 
                                $sheet->cells("A".$row, function ($cells){
                                    $cells->setAlignment('right');
                                    $cells->setFontWeight('bold');
                                }); 
                                $sheet->row($row,$value);
                                $row++;
                            }
                            
                       }  
                      }
                    

                    

                    $sheet->cells("A1:N1", function ($cells){
                                    $cells->setAlignment('center');
                                    $cells->setFontWeight('bold');
                                }); 
                $sheet->freezeFirstRow();
                $lrow = $row;
                //$range = "C2:E".$lrow;

                $sheet->setWidth(array(
                    'A'     =>  20,
                    'B'     =>  20,
                    'C'     =>  30,
                    'D'     =>  20,
                    'E'     =>  20,
                ));
                //$sheet->setColumnFormat(array($range => '#,##0.00_-'));
            });






        })->download('xlsx');

        



    }


    public function createTable($database_name, $table_name, $fields = [], $primary_key){	
		
		\Config::set('database.connections.reports_db.database', $database_name);
        DB::purge('reports_db');
		
        // echo $primary_key;die();
        $Pkey = $primary_key;
        // check if table is not already exists
        if (!Schema::connection('reports_db')->hasTable($table_name)) {
            Schema::connection('reports_db')->create($table_name, function (Blueprint $table) use ($fields, $table_name, $primary_key) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';
                $table->increments($primary_key);
                //$table->integer($primary_key)->primary();
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        if($field['type'] == 'integer'){
                            $table->{$field['type']}($field['name'])->unsigned();
                        }elseif($field['type'] == 'timestamp'){
                            $table->{$field['type']}($field['name'])->useCurrent();
                        }elseif(isset($field['limit'])){
                            $table->{$field['type']}($field['name'], $field['limit']);
                        }else{
                            $table->{$field['type']}($field['name']);
                        }
                    }
                }
            });
 
            return response()->json(['message' => 'Given table has been successfully created!'], 200);
        }
 
        return response()->json(['message' => 'Given table is already existis.'], 400);
    }

    public function process_municipalities($prv_database, $province_database, $municipality_database, $municipality_name, $province_name){
        //echo "<strong>accessing table: `".$province_database.".".$municipality_database."`  PRV Database: ".$prv_database."</strong><br>";
        $prv_municipality = DB::table($prv_database.".released")->groupBy('municipality')->get();

        //echo "Detected municipality of: ".$row->municipality.", in the province of: ".$row->province." (".$row->prv_dropoff_id.")<br>";
        $farmer_list = DB::table($prv_database.".released")
            ->select('released.province', 'released.municipality', 'released.seed_variety', 
                    'released.bags', 'released.date_released', 'released.farmer_id', 'released.rsbsa_control_no',
                    'released.farmer_id', 'released.released_by', 'released.release_id', 'released.claimed_area')
            ->where('released.bags', '!=', '0')
            ->where('released.province', '=', $province_name)
            ->where('released.municipality', '=', $municipality_name)
            ->where('released.is_processed', 1)
            ->orderBy('released.prv_dropoff_id')
            //->limit(1)
            ->get();

        //echo "[".$row->prv_dropoff_id."] Province (".$row->province."): ".$row->municipality." total farmers: (".count($farmer_list).")<br>";
        /**
         * START PROCESSING
         */
		$daily_farmers = 0;
        $daily_bags = 0;
        $daily_area = 0;
        $daily_male = 0;
        $daily_female = 0;
		$yield = 0;
        foreach ($farmer_list as  $row) {

            //check other_info table
            $other_info_data = DB::table($prv_database.".other_info")
                ->where('farmer_id', $row->farmer_id)
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
                ->first();

            if(count($other_info_data) > 0){
                $birthdate = $other_info_data->birthdate;
                $mother_name = $other_info_data->mother_name;

                if($other_info_data->phone == "" || $other_info_data->phone == "0000-000-0000"){
                    $phone_number = "";
                }else{
                    $phone_number = $other_info_data->phone;
                }
            }else{
                $birthdate = '';
                $mother_name = '';
                $phone_number = '';
            }

            //get farmer_profile
            $farmer_profile = DB::table($prv_database.".farmer_profile")
                ->where('rsbsa_control_no', $row->rsbsa_control_no)
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
				$sex = '';
                $weight = $farmer_profile->weight_per_bag;
                $no_bags = $farmer_profile->yield;
                $area = $farmer_profile->area_harvested;

               
                if($area>0){
                    $yield = (floatval($no_bags) * floatval($weight)) / floatval($area);
                 }else{
                    $yield = 0;
                 }


                $yield = $yield / 1000;
                if($farmer_profile->sex == 'Male'){
                    $sex = 'Male';
                    $daily_male += 1;
					// $yield = $farmer_profile->yield;					
                }else if($farmer_profile->sex == 'Femal'){
                    $sex = 'Female';
                    $daily_female += 1; 
					// $yield = $farmer_profile->yield;
                }
            }else{
				$farmer_profile = DB::table($prv_database.".farmer_profile")
					->where('rsbsa_control_no', $row->rsbsa_control_no)
					->where('lastName', '!=', '')
					->where('firstName', '!=', '')
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
					$sex = '';
                    $weight = $farmer_profile->weight_per_bag;
                    $no_bags = $farmer_profile->yield;
                    $area = $farmer_profile->area_harvested;
    
                   
                    
                    if($area>0){
                         $yield = (floatval($no_bags) * floatval($weight)) / floatval($area);
                     }else{
                        $yield = 0;
                     }

                    $yield = $yield / 1000;



					if($farmer_profile->sex == 'Male'){
						$sex = 'Male';
						$daily_male += 1;
						// $yield = $farmer_profile->yield;					
					}else if($farmer_profile->sex == 'Femal'){
						$sex = 'Female';
						$daily_female += 1; 
						// $yield = $farmer_profile->yield;
					}
				}else{
					$qr_code = "N/A";
					$farmer_fname = "N/A";
					$farmer_mname = "N/A";
					$farmer_lname = "N/A";
					$farmer_extname = "N/A";
					$dist_area = 0;
					$actual_area = 0;
					$sex = "N/A";
					
					$daily_male += 0;
					$daily_female += 0;
					$yield += 0;
				}
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

			$daily_farmers += 1;
            $daily_bags += $row->bags;
            $daily_area += $actual_area;

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
                "mother_name" => $mother_name,
                'dist_area' => $dist_area,
                'actual_area' => $actual_area,
                'bags' => $row->bags,
                'seed_variety' => $row->seed_variety,
                'date_released' => $row->date_released,
                'farmer_id' => $row->farmer_id,
				'yield' => $yield,
                'released_by' => $encoder_name
            ];
            DB::table("$province_database.$municipality_database")->insert($data);
            //echo "[".$province_database.".".$municipality_database."] Province (".$row->province."): ".$row->municipality.": MYSQL_INSERT RSBSA # - ($row->rsbsa_control_no), Name - ($farmer_fname $farmer_mname $farmer_lname)<br>";

            //after processing to seed beneficiary list DB update is_processed flag to 0
            DB::table($prv_database.'.released')->where('release_id', $row->release_id)->update([
                'is_processed' => 0
            ]);
            DB::commit();
        }
        /**
         * END PROCESSING
         */
        //echo "<strong>End of access table: `".$province_database.".".$municipality_database."`  PRV Database: ".$prv_database."</strong><br><br>";
    
		//return total bags, area, farmers, male, female'
        $region = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province_name)->groupBy('province')->value('region');
        $week_municipal_data = [
            'current_month' => "N/A",
            'current_week' => "N/A",
            "region" => $region,
            "province" => $province_name,
            "municipality" => $municipality_name,
            "total_farmers" => $daily_farmers,
            'total_bags' => $daily_bags,
            'total_dist_area' => 0,
            'total_actual_area' => $daily_area,
            'total_male' => $daily_male,
            'total_female' => $daily_female,
            'date_generated' => date("Y-m-d")
            //'date_generated' => "2020-10-23"
        ];
        DB::table($GLOBALS['season_prefix']."rcep_google_sheets.lib_weekly_municipal")->insert($week_municipal_data);
	}


    public function scheduled_list(){
		//dd("execute new reports");
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
    
        try{
            $province_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->orderBy('region_sort')->get();
			
			 \Config::set('database.connections.reports_db.host', 'localhost');
            \Config::set('database.connections.reports_db.port', '4409');
            \Config::set('database.connections.reports_db.database', null);
            \Config::set('database.connections.reports_db.username', 'rcef_web');
            \Config::set('database.connections.reports_db.password', 'SKF9wzFtKmNMfwy');
			DB::purge('reports_db');
			
        //    dd($province_list);
			foreach($province_list as $row){
                // dd($row->prv);
                $database_name = "rpt_".substr($row->prv, 0, 4);
                $prv_database = $GLOBALS['season_prefix']."prv_".substr($row->prv, 0, 4);

                $query = "CREATE DATABASE IF NOT EXISTS $database_name";
                DB::connection('reports_db')->statement($query);
                //dd($query);
                $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                    ->where('region', $row->region)
                    ->where('province', $row->province)
                    ->groupBy('province', 'municipality')
                    ->orderBy('region_sort')
                    ->get();

                //loop to all municipalities and generate their respective tables
                foreach($municipalities as $m_row)  {
                    \Config::set('database.connections.reports_db.database', $database_name);
                    DB::purge('reports_db');
					//dd($database_name);
					//dd(DB::connection('mysql')->getPdo());

                    $table_name = "tbl_".$m_row->prv;
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
                        ['name' => 'mother_name', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'dist_area', 'type' => 'float', 'limit' => '10'],
                        ['name' => 'actual_area', 'type' => 'float', 'limit' => '10'],
                        ['name' => 'bags', 'type' => 'integer', 'limit' => '10'],
                        ['name' => 'seed_variety', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'date_released', 'type' => 'string', 'limit' => '100'],  
                        ['name' => 'farmer_id', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'released_by', 'type' => 'text'],
						['name' => 'yield', 'type' => 'float', 'limit' => '10'],
                        ['name' => 'date_generated', 'type' => 'timestamp'],   
                    ];
                    $this->createTable($database_name, $table_name, $fields, $primary_key);
                    //DB::table($database_name.".".$table_name)->truncate();
                
                    //call function to save to tbl_XXXX inside the rpt_prv databases
                    $this->process_municipalities($prv_database, $database_name, $table_name, $m_row->municipality, $m_row->province);
                }

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }
    }

     public function export_province_noUPdate($province){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

            $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province)
                // ->where('municipality', $municipality)
                ->first();
            $database_name = "rpt_".substr($prv_details->prv, 0, 4);
            $table_name    = "tbl_".$prv_details->prv;



            // $prv_database = $GLOBALS['season_prefix']."rcep_reports_prv_view."."prv_".substr($prv_details->prv, 0, 4)."_view";
            $released_db = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);
                $rcef_ids = DB::table($released_db.".new_released")
                        ->select("db_ref")
                        ->where("category", "INBRED")
                        // ->where("municipality", $municipality)
                        ->get();
        
                $rcef_ids = json_decode(json_encode($rcef_ids), true);

                $farmer_info = DB::table($released_db.".farmer_information_final")
                         ->whereIn("db_ref", $rcef_ids)
                        ->groupBy("db_ref")
                        ->get();
                    $farmer_info = json_decode(json_encode($farmer_info), true);
         
                $rcef_released = DB::table($released_db.".new_released")
                        // ->select('new_released_id', 'id', 'rcef_id', 'prv_dropoff_id', 'province', 'municipality', 'dropOffPoint', 'transaction_code', 'dataSharing', 'is_representative', 'rep_name', 'rep_id', 'rep_relation', 'claimed_area', DB::raw("SUM(bags_claimed) as bags_claimed"), 'seed_variety', 'recipient_ls', 'planted_rcvd_seeds_ls', 'reason_not_planted_rcvd_seeds_ls', 'yield_area_harvested_ls', 'yield_no_of_bags_ls', 'yield_wt_per_bag', 'crop_establishment_cs', 'seedling_age', 'ecosystem_cs', 'ecosystem_source_cs', 'planting_week', 'has_kp_kit', 'kp_kit_count', 'other_benefits_received', 'date_released', 'released_by', 'time_start', 'time_end', 'app_version', 'distribution_type', 'mode', 'with_fertilizer_voucher', 'farmer_id_address', 'content_rsbsa', 'server_date_received', 'category', 'birthdate', 'final_area', 'line_designation', 'yield_last_season_details', 'sex', 'lot_series_claims')
                        ->where("category", "INBRED")
                        // ->where("municipality", $municipality)
                        ->orderBy("municipality")
                        ->orderBy("db_ref")
                        ->get();
                
            $excel_data = array();

            foreach($rcef_released as $row)
            {

               
            
                $farmer_info_result = $this->search_to_array($farmer_info, "db_ref", $row->db_ref);
               
               
                $rcef_id = $row->rcef_id;
                $rsbsa_control_no = $row->content_rsbsa;
                $firstName = "N/A";
                $midName = "N/A";
                $lastName = "N/A";
                $extName = "N/A";
                $sex = strtoupper(substr($row->sex,0,1));
                $birthdate = $row->birthdate;
                $tel_no = "N/A";
                $mother_name = "N/A";
                $registered_area  = 0;

                foreach($farmer_info_result as $farmer){
                    $rcef_id = $farmer["rcef_id"];
                    $rsbsa_control_no = $farmer["rsbsa_control_no"];
                    $firstName = $farmer["firstName"];
                    $midName = $farmer["midName"];
                    $lastName = $farmer["lastName"];
                    $extName = $farmer["extName"];
                    $sex = $farmer["sex"];
                    
                    $tel_no = $farmer["tel_no"];
                    $mother_name = $farmer["mother_name"];
                    $registered_area  = $farmer["final_area"];


                }




                $date_synced =  $row->date_released;
                $yield_data = $row->yield_last_season_details;
                $area_claimed = $row->claimed_area;
                

                $inclu = array(
                    "db_ref" => $row->db_ref,
                    "RCEF ID" => $rcef_id,
                    "RSBSA #" => $rsbsa_control_no,
                    "Farmer's First Name" => $firstName,
                    "Farmer's Middle Name" => $midName,
                    "Farmer's Last Name" => $lastName,
                    "Farmer's Extension Name" => $extName,
                    "Sex" => $sex ,
                    "Birth Date" => $birthdate,
                    "Telephone Number" => $tel_no,
                    "Province" =>  $row->province,
                    "Municipality" =>  $row->municipality,
                    "Mother's Name" => $mother_name,
                    "Registered Area" =>number_format($registered_area,2,".",","),
                    "area_claimed" => number_format($area_claimed,2,".",","),
                    "Bags" =>  $row->bags_claimed,
                    "Seed Variety" =>  $row->seed_variety,
                    "Remarks" =>  $row->remarks,
                    "Crop Establishment" =>  $row->crop_establishment_cs,
                    "Seed age" =>  $row->seedling_age,
                    "Eco System" =>  $row->ecosystem_cs,
                    "Eco System Source" =>  $row->ecosystem_source_cs,
                    "Planting Week" =>  $row->planting_week,
                    "KP Kit Received" =>  $row->kp_kit_count,
                    "Other Benefits" =>  $row->other_benefits_received,
                    // "Yield Data" => $yield_data,
                    "Date Released" => $date_synced,
                    "Released By" => $row->released_by,
                    "Date Synced" => $date_synced,
                    "App Version" => $row->app_version,
                );

              

                $yield_data = json_decode($yield_data,false);
                $y = 1;
                foreach($yield_data as $yield){
                    $inclu["yield_variety_".$y] = $yield->variety;
                    $inclu["yield_area_".$y] = $yield->area;
                    $inclu["yield_bags_".$y] = $yield->bags;
                    $inclu["yield_weight_".$y] = $yield->weight;
                    $inclu["yield_type_".$y] = $yield->type;
                    $inclu["yield_class_".$y] = $yield->class;
                    $y++;
                }
                array_push($excel_data, $inclu);

                // $yield_data = json_decode($yield_data);
                // dd($yield_data);
            }
            
            $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
            return Excel::create("$province"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:AB1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:V1', 'thin');
                });
            })->download('xlsx');

            DB::commit();
        
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
        // ini_set('memory_limit', '-1');
        // DB::beginTransaction();
        // try {

        //     $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->where('province', $province)->first();
        //     $prv_database = $GLOBALS['season_prefix']."rcep_reports_prv_view.prv_".substr($prv_details->prv, 0, 4)."_view";
            
        //     $municipalities = DB::table($prv_database)
        //         ->where('rel_province', $province)
        //         ->groupBy('municipality')
        //         //->skip(0)
        //         //->take(2)
        //         ->get();
        //         // dd($prv_database);
        //         // dd($municipalities);

        //     $list_arr = array();  //put all the data collections to a single array
        //     foreach($municipalities as $row_muni){               
        //            $municipal_table_data = DB::table($prv_database)
        //             ->where("municipality", $row_muni->municipality)
        //             ->get();     
        //         if(count($municipal_table_data) > 0){
        //             $municipal_data = array();
        //             foreach($municipal_table_data as $row){
        //                 $yield = 0;
        //                 //$yield = $row->yield;
        //                 $date_synced = $row->rel_time_end;
        //                 $total_production = $row->rel_yield_no_of_bags_ls;
        //                 $weight_per_bag = $row->rel_yield_wt_per_bag;
        //                 $area_harvested = $row->rel_yield_area_harvested_ls;
        //                 $area_claimed = $row->rel_claimed_area;


        //                 if($area_harvested >0){
        //                     $yield = (($total_production * $weight_per_bag)/$area_harvested)/1000;
        //                 }else{
        //                     $yield = 0;
        //                 }
        //                     $actual_area = $row->final_area;
                       
        //                 array_push($municipal_data, array(
        //                     "RCEF ID" => $row->rcef_id,
        //                     "RSBSA #" => $row->rsbsa_control_no,
        //                     "Farmer's First Name" => $row->firstName,
        //                     "Farmer's Middle Name" => $row->midName,
        //                     "Farmer's Last Name" => $row->lastName,
        //                     "Farmer's Extension Name" => $row->extName,
        //                     "Sex" => $row->sex,
        //                     "Birth Date" => $row->birthdate,
        //                     "Telephone Number" => $row->tel_no,
        //                     "Province" => $row->rel_province,
        //                     "Municipality" => $row->rel_municipality,
        //                     "Mother's First Name" => $row->mother_fname,
        //                     "Mother's Middle Name" => $row->mother_mname,
        //                     "Mother's Last Name" => $row->mother_lname,
        //                     "Mother's Suffix" => $row->mother_suffix,
        //                     "Actual Area" => $actual_area,
        //                     "Claimed Area" =>  number_format($area_claimed,2,".",","),
        //                     "Bags" => $row->rel_bags_claimed,
        //                     "Seed Variety" => $row->rel_seed_variety,
        //                     "Yield(T/ha)" => number_format($yield,2,".",","),
        //                     "Date Released" => $row->rel_date_released,
        //                     "Released By" => $row->rel_released_by,
        //                     "Date Synced" => $date_synced,
        //                     "Total Production" => $total_production,
        //                     "Ave. weight per bag(kg)" => $weight_per_bag,
        //                     "Area Harvested (ha)" => $area_harvested,
        //                 ));
        //             }
        //             array_push($list_arr, $municipal_data);
        //         }
        //     }

        //     $new_collection = collect(); //loop trough all the data collections and merge it into 1 collection variable
        //     foreach($list_arr as $list_collection_row){
        //         $new_collection = $new_collection->merge($list_collection_row);
        //     }
            
  
        //     //get nrp daTa
        //     // $nrp_data = DB::table($prv_database.".nrp_profile")
        //     //     ->select('rsbsa', 'fname', 'mname', 'lname', 'extname', 'sex', 'birthdate', 'phonenumber', 'claimed_seed', 'num_of_bag', 'package_weight')
        //     //     ->get();

        //     $excel_data = json_decode(json_encode($new_collection), true); //convert collection to associative array to be converted to excel
        //     // $nrp_data = json_decode(json_encode($nrp_data), true); //convert collection to associative array to be converted to excel
        //     return Excel::create("$province"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
        //         $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
        //             /*$sheet->fromArray($excel_data, null, 'A1', false, false);
        //             $sheet->prependRow(1, array(
        //                 '#', 'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
        //                 "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
        //                 'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
        //                 "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
        //                 'Actual Area', 'Bags', 'Seed Variety', 'Yield', 'Date Released', 'Farmer ID', 'Released By'
        //             ));
        //             $sheet->freezeFirstRow();
        //             $sheet->getColumnDimension('A')->setVisible(false);*/

                  
        //             $sheet->fromArray($excel_data);
        //             $sheet->freezeFirstRow();
                    
        //             $sheet->setHeight(1, 30);
        //             $sheet->cells('A1:AA1', function ($cells) {
        //                 $cells->setBackground('#92D050');
        //                 $cells->setAlignment('center');
        //                 $cells->setValignment('center');
        //             });
        //             $sheet->setBorder('A1:Z1', 'thin');
        //         });
        //     })->download('xlsx');

        //     DB::commit();
        
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return $e;
        // }
    }





    public function export_province_noUPdate_ws2021($province){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

            $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province)->first();
            
            $prv_database = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);
            $database_name = "rpt_".substr($prv_details->prv, 0, 4);
			$prv_name      = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);

            /**  (USER_LEVTEL != PMO ACCOUNT)
             * STEPS: 1. get all municipalities within the province
             *        2. loop through all the municipalities 
             *        3. get data from `rcep_excel` for each municipality
             *        4. convert to excel file
             */

            /*$municipalities = DB::table($prv_database.".released")
                ->groupBy('municipality')
                ->get();*/
				
			 $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                ->where('province', $province)
                ->groupBy('municipality')
                //->skip()
                //->take()
                ->get();

           // dd($municipalities);


            $list_arr = array();  //put all the data collections to a single array
            foreach($municipalities as $row){

                $table_name = "tbl_".substr($row->prv_dropoff_id, 0, 6);
                 
                   $schema = DB::table('information_schema.TABLES')
                        ->where("TABLE_SCHEMA", $database_name)
                        ->where("TABLE_NAME", $table_name)
                        ->first();
                    if(count($schema)<=0){
                        continue;
                    }




               
                
                //$municipal_table_data = DB::table($database_name.".".$table_name)->get();
                
				$municipal_table_data = DB::table($database_name.".".$table_name)
                    ->select('id', 'rsbsa_control_number', 'qr_code', 'farmer_fname', 'farmer_mname', 'farmer_lname',
                             'farmer_ext', 'sex', 'birthdate', 'tel_number', 'province', 'municipality', 'mother_name', 'dist_area', 'actual_area', 'bags', 'seed_variety', 'yield',
                             'date_released', 'farmer_id', 'released_by')
                    ->get();
				
				if(count($municipal_table_data) > 0){
                    //array_push($list_arr, $municipal_table_data);
					$municipal_data = array();
                    foreach($municipal_table_data as $row){
                        $yield = 0;
                        $yield = $row->yield;
                   

                        $prv = $GLOBALS['season_prefix'].'prv_'.substr(str_replace("-", "", $row->rsbsa_control_number),0,4);
                        $date_synced = DB::table($prv_database.".released")
                            ->where("rsbsa_control_no", $row->rsbsa_control_number)
                            ->where("seed_variety", $row->seed_variety)
                            ->where("bags", $row->bags)
                            ->where("farmer_id", $row->farmer_id)
                            ->value("date_synced");
                        if(count($date_synced)<=0){
                            $date_synced = "N/A";
                        }

                        // column : yield
                        $total_production = 0;
                        // column : weight_per_bag
                        $weight_per_bag = 0;
                        // column : area_harvested
                        $area_harvested = 0;

                        $farmer_profile = DB::table($prv_database.".farmer_profile")
                            ->select("yield", "weight_per_bag", "area_harvested")
                            ->where("rsbsa_control_no", $row->rsbsa_control_number)
                            ->first();

                        if(count($farmer_profile) > 0){
                            $total_production = $farmer_profile->yield;
                            $weight_per_bag = $farmer_profile->weight_per_bag;
                            $area_harvested = $farmer_profile->area_harvested;
                        }
                        
                            $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->select("prv")
                        ->where('province', $province)
                        ->first();
                    $area_claimed = 0;
                if(count($prv)>0){
                    $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $area_claimed = DB::table($prv_db.".released")
                                ->where("province", $province)
                                ->where("rsbsa_control_no",$row->rsbsa_control_number)
                                ->where("farmer_id", $row->farmer_id)
                                ->sum("claimed_area");          
                    }






                        array_push($municipal_data, array(
                            "RSBSA #" => $row->rsbsa_control_number,
                            "QR Code" => $row->qr_code,
                            "Farmer's First Name" => $row->farmer_fname,
                            "Farmer's Middle Name" => $row->farmer_mname,
                            "Farmer's Last Name" => $row->farmer_lname,
                            "Farmer's Extension Name" => $row->farmer_ext,
                            "Sex" => $row->sex,
                            "Birth Date" => $row->birthdate,
                            "Telephone Number" => $row->tel_number,
                            "Province" => $row->province,
                            "Municipality" => $row->municipality,
                            "Mother's Maiden Name" => $row->mother_name,
                            "Actual Area" => $row->actual_area,
                            "Claimed Area" =>  number_format($area_claimed,2,".",","),
                            "Bags" => $row->bags,
                            "Seed Variety" => $row->seed_variety,
                            "Yield(T/ha)" => number_format($yield,2,".",","),
                            "Date Released" => $row->date_released,
                            "Farmer ID" => $row->farmer_id,
                            "Released By" => $row->released_by,
                            "Date Synced" => $date_synced,
                            "Total Production" => $total_production,
                            "Ave. weight per bag(kg)" => $weight_per_bag,
                            "Area Harvested (ha)" => $area_harvested,
                        ));
                    }
                    array_push($list_arr, $municipal_data);
                }
            }

            $new_collection = collect(); //loop trough all the data collections and merge it into 1 collection variable
            foreach($list_arr as $list_collection_row){
                $new_collection = $new_collection->merge($list_collection_row);
            }
			
			//get nrp daTa
            $nrp_data = DB::table($prv_name.".nrp_profile")
                ->select('rsbsa', 'fname', 'mname', 'lname', 'extname', 'sex', 'birthdate', 'phonenumber', 'claimed_seed', 'num_of_bag', 'package_weight')
                ->get();

            $excel_data = json_decode(json_encode($new_collection), true); //convert collection to associative array to be converted to excel
            $nrp_data = json_decode(json_encode($nrp_data), true); //convert collection to associative array to be converted to excel
			return Excel::create("$province"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    /*$sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        '#', 'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
                        "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                        'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
                        "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
                        'Actual Area', 'Bags', 'Seed Variety', 'Yield', 'Date Released', 'Farmer ID', 'Released By'
                    ));
                    $sheet->freezeFirstRow();
                    $sheet->getColumnDimension('A')->setVisible(false);*/
					$sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
					
					$sheet->setHeight(1, 30);
					$sheet->cells('A1:Z1', function ($cells) {
						$cells->setBackground('#92D050');
						$cells->setAlignment('center');
						$cells->setValignment('center');
					});
					$sheet->setBorder('A1:Z1', 'thin');
                });
            })->download('xlsx');

            DB::commit();
        
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function export_province_withUPdate($province){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {
            $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->where('province', $province)->first();
            $prv_database = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);
            $database_name = "rpt_".substr($prv_details->prv, 0, 4);

            $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point') //get all municipalities of the selected province
                    ->where('region', $prv_details->region)
                    ->where('province', $prv_details->province)
                    ->groupBy('municipality')
                    ->orderBy('region_sort')
                    ->get();

            //loop to all municipalities and generate their respective tables
            $new_collection = collect();
            foreach($municipalities as $m_row)  {
                //call function to save to tbl_XXXX inside the rpt_prv databases
                $table_name = "tbl_".$m_row->prv;
                $this->process_municipalities($prv_database, $database_name, $table_name, $m_row->municipality, $m_row->province);

                //$list_collection_row = DB::table($database_name.".".$table_name)->get();
                $list_collection_row = DB::table($database_name.".".$table_name)
                    ->select('id', 'rsbsa_control_number', 'qr_code', 'farmer_fname', 'farmer_mname', 'farmer_lname',
                             'farmer_ext', 'sex', 'birthdate', 'tel_number', 'province', 'municipality', 'mother_name', 'dist_area', 'actual_area', 'bags', 'seed_variety', 'yield',
                             'date_released', 'farmer_id', 'released_by')
                    ->get();
				$new_collection = $new_collection->merge($list_collection_row);
            }

            //after update is finished call function to export to excel
            $excel_data = json_decode(json_encode($new_collection), true); //convert collection to associative array to be converted to excel
            return Excel::create("$province"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        '#', 'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
                        "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                        'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
                        "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
                        'Actual Area', 'Bags', 'Seed Variety', 'Yield','Date Released', 'Farmer ID', 'Released By'
                    ));
                    $sheet->freezeFirstRow();
                    $sheet->getColumnDimension('A')->setVisible(false);
                });
            })->download('xlsx');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }

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
       public function export_muni_noUpdate_pyCsv(request $request){

        $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where('province', $request->prv)
        ->where('municipality', $request->mun)
        ->first();
        //uncomment for development
        // $pythonPath = 'C://Python312//python.exe';

        //production
        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';

        $scriptPath = base_path('app/Http/PyScript/report-exports-seed-bene.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];
        $prv = substr($prv_details->prv, 0, 4);
        $mun = $request->mun;
        $cat = 'INBRED';
        $province = $request->prv;

        $escapedSsn = escapeshellarg($ssn);
        $escapedPrv = escapeshellarg($prv);
        $escapedMun = escapeshellarg($mun);
        $escapedCat = escapeshellarg($cat);
        $escapedProvince = escapeshellarg($province);

        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedPrv $escapedMun $escapedCat $escapedProvince";

        $process = new Process($command);

        try {
            $process->mustRun();
            $output = $process->getOutput();

            $keyword = 'report/home/';
            if (strpos($output, $keyword) === 0) {
                $filename_output = substr($output, strlen($keyword));
            } else {
                $filename_output = 'Unexpected format: ' . $output;
            }
            return response()->json([
                'message' => 'Script executed successfully',
                'output' => $filename_output
            ]);
        } catch (ProcessFailedException $exception) {
            echo $exception->getMessage();
        }
        //local python path
/*         $pythonPath = 'C://Users//Admin//AppData//Local//Programs//Python//Python312//python.exe';
        $scriptPath = base_path('app/Http/PyScript/report-exports-seed-bene.py');

        //local path
        //$scriptPath = 'd://Admin//Downloads//report-exports-seed-bene.py';
    
        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];
        $prv = substr($prv_details->prv, 0, 4);
        $mun = $request->mun;
        $cat = 'INBRED';
    
        $escapedSsn = escapeshellarg($ssn);
        $escapedPrv = escapeshellarg($prv);
        $escapedMun = escapeshellarg($mun);
        $escapedCat = escapeshellarg($cat);
    
        // Construct the command with arguments
        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedPrv $escapedMun $escapedCat";
    
        // Execute the command with redirection
        $output = shell_exec($command . " 2>&1");
        $keyword = 'report/home/';
        if (strpos($output, $keyword) === 0) {
            $filename_output = substr($output, strlen($keyword));
        } else {
            $filename_output = 'Unexpected format: ' . $output;
        }

        // Return the trimmed output as a response
        return response()->json([
            'message' => 'Script executed successfully',
            'output' => $filename_output
        ]); */
        
       }
       public function py_unlinking(Request $request){
            //unlink("./report/home/sample.csv");
            $fileName = trim($request->uri);

            // Construct the full file path
            $filePath = './report/home/' . $fileName;
        
            // Debugging: Log the file path to check if it's correct
            error_log("Attempting to delete file at path: " . $filePath);
        
            if (file_exists($filePath)) {
                // Try to delete the file and handle any potential errors
                if (unlink($filePath)) {
                    return response()->json(['message' => 'File deleted successfully.']);
                } else {
                    return response()->json(['message' => 'File could not be deleted.'], 500);
                }
            } else {
                return response()->json(['message' => 'File not found.'], 404);
            }
        }
       public function export_municipality_noUPdate($province, $municipality){

/*         $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->where('province', $province)
        ->where('municipality', $municipality)
        ->first();

        //local python path
        $pythonPath = 'C://Users//Admin//AppData//Local//Programs//Python//Python312//python.exe';
        $scriptPath = base_path('app/Http/PyScript/report-exports-seed-bene.py');
        $ssn = $GLOBALS["season_prefix"];
        $prv = substr($prv_details->prv, 0, 4);
        $mun = $municipality;
        $cat = 'INBRED';
        $escapedSsn = escapeshellarg($ssn);
        $escapedPrv = escapeshellarg($prv);
        $escapedMun = escapeshellarg($mun);
        $escapedCat = escapeshellarg($cat);
        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedPrv $escapedMun $escapedCat";
        $output = shell_exec($command . " 2>&1");
    
        return $output; */

        // dd($municipality);
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

            $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('province', $province)
                ->where('municipality', $municipality)
                ->first();
            $database_name = "rpt_".substr($prv_details->prv, 0, 4);
            $table_name    = "tbl_".$prv_details->prv;



            // $prv_database = $GLOBALS['season_prefix']."rcep_reports_prv_view."."prv_".substr($prv_details->prv, 0, 4)."_view";
            $released_db = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);
                $rcef_ids = DB::table($released_db.".new_released")
                        ->select("db_ref")
                        ->where("category", "INBRED")
                        ->where("municipality", $municipality)
                        ->get();
        
                $rcef_ids = json_decode(json_encode($rcef_ids), true);

                $farmer_info = DB::table($released_db.".farmer_information_final")
                         ->whereIn("db_ref", $rcef_ids)
                        ->groupBy("db_ref")
                        ->get();
                    $farmer_info = json_decode(json_encode($farmer_info), true);
         
                $rcef_released = DB::table($released_db.".new_released")
                        // ->select('new_released_id', 'id', 'rcef_id', 'prv_dropoff_id', 'province', 'municipality', 'dropOffPoint', 'transaction_code', 'dataSharing', 'is_representative', 'rep_name', 'rep_id', 'rep_relation', 'claimed_area', DB::raw("SUM(bags_claimed) as bags_claimed"), 'seed_variety', 'recipient_ls', 'planted_rcvd_seeds_ls', 'reason_not_planted_rcvd_seeds_ls', 'yield_area_harvested_ls', 'yield_no_of_bags_ls', 'yield_wt_per_bag', 'crop_establishment_cs', 'seedling_age', 'ecosystem_cs', 'ecosystem_source_cs', 'planting_week', 'has_kp_kit', 'kp_kit_count', 'other_benefits_received', 'date_released', 'released_by', 'time_start', 'time_end', 'app_version', 'distribution_type', 'mode', 'with_fertilizer_voucher', 'farmer_id_address', 'content_rsbsa', 'server_date_received', 'category', 'birthdate', 'final_area', 'line_designation', 'yield_last_season_details', 'sex', 'lot_series_claims')
                        ->where("category", "INBRED")
                        ->where("municipality", $municipality)
                        ->orderBy("db_ref")
                        ->get();
                
            $excel_data = array();
            
            foreach($rcef_released as $row)
            {

               
            
                $farmer_info_result = $this->search_to_array($farmer_info, "db_ref", $row->db_ref);
               
               
                $rcef_id = $row->rcef_id;
                $rsbsa_control_no = $row->content_rsbsa;
                $firstName = "N/A";
                $midName = "N/A";
                $lastName = "N/A";
                $extName = "N/A";
                $sex = strtoupper(substr($row->sex,0,1));
                $birthdate = $row->birthdate;
                $tel_no = "N/A";
                $mother_name = "N/A";
                $registered_area  = 0;

                foreach($farmer_info_result as $farmer){
                    
                    $rcef_id = $farmer["rcef_id"];
                    $rsbsa_control_no = $farmer["rsbsa_control_no"];
                    $firstName = $farmer["firstName"];
                    $midName = $farmer["midName"];
                    $lastName = $farmer["lastName"];
                    $extName = $farmer["extName"];
                    $sex = $farmer["sex"];
                    
                    $tel_no = $farmer["tel_no"];
                    $mother_name = $farmer["mother_name"];
                    $registered_area  = $farmer["final_area"];


                }




                $date_synced =  $row->date_released;
                $yield_data = $row->yield_last_season_details;
                $area_claimed = $row->claimed_area;
                

                $inclu = array(
                    "db_ref" => $row->db_ref,
                    "RCEF ID" => $rcef_id,
                    "RSBSA #" => $rsbsa_control_no,
                    "Farmer's First Name" => $firstName,
                    "Farmer's Middle Name" => $midName,
                    "Farmer's Last Name" => $lastName,
                    "Farmer's Extension Name" => $extName,
                    "Sex" => $sex ,
                    "Birth Date" => $birthdate,
                    "Telephone Number" => $tel_no,
                    "Province" =>  $row->province,
                    "Municipality" =>  $row->municipality,
                    "Mother's Name" => $mother_name,
                    "Registered Area" =>number_format($registered_area,2,".",","),
                    "area_claimed" => number_format($area_claimed,2,".",","),
                    "Bags" =>  $row->bags_claimed,
                    "Seed Variety" =>  $row->seed_variety,
                    "Remarks" =>  $row->remarks,
                    "Crop Establishment" =>  $row->crop_establishment_cs,
                    "Seed age" =>  $row->seedling_age,
                    "Eco System" =>  $row->ecosystem_cs,
                    "Eco System Source" =>  $row->ecosystem_source_cs,
                    "Planting Week" =>  $row->planting_week,
                    "KP Kit Received" =>  $row->kp_kit_count,
                    "Other Benefits" =>  $row->other_benefits_received,
                    // "Yield Data" => $yield_data,
                    "Date Released" => $date_synced,
                    "Released By" => $row->released_by,
                    "Date Synced" => $date_synced,
                    "App Version" => $row->app_version,
                );

              

                $yield_data = json_decode($yield_data,false);
                $y = 1;

                
                foreach($yield_data as $yield){
                    $inclu["yield_variety_".$y] = $yield->variety;
                    $inclu["yield_area_".$y] = $yield->area;
                    $inclu["yield_bags_".$y] = $yield->bags;
                    $inclu["yield_weight_".$y] = $yield->weight;
                    $inclu["yield_type_".$y] = $yield->type;
                    $inclu["yield_class_".$y] = $yield->class;
                    $y++;
                }
                array_push($excel_data, $inclu);

                // $yield_data = json_decode($yield_data);
                // dd($yield_data);






            }


            
            $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
            return Excel::create("$province"."_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                    $sheet->setHeight(1, 30);
                    $sheet->cells('A1:AB1', function ($cells) {
                        $cells->setBackground('#92D050');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->setBorder('A1:V1', 'thin');
                });
            })->download('xlsx');

            DB::commit();
        
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }



    public function export_municipality_noUPdate_ws2021($province, $municipality){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

            $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                ->where('province', $province)
                ->where('municipality', $municipality)
                ->first();
                //dd($prv_details);
            $database_name = "rpt_".substr($prv_details->prv, 0, 4);
            $table_name    = "tbl_".$prv_details->prv;
            $prv_database = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);
            /**  (USER_LEVTEL != PMO ACCOUNT)
             * STEPS: 1. get municipality
             *        2. get data from `rpt_province` (e.g. tbl_012901)
             *        3. convert to excel file
             */

            //$municipal_table_data = DB::table($database_name.".".$table_name)->get();
            $municipal_table_data = DB::table($database_name.".".$table_name)
                ->select('id', 'rsbsa_control_number', 'qr_code', 'farmer_fname', 'farmer_mname', 'farmer_lname',
                             'farmer_ext', 'sex', 'birthdate', 'tel_number', 'province', 'municipality', 'mother_name', 'mother_ext', 'dist_area', 'actual_area','bags', 'seed_variety', 'yield',
                             'date_released', 'farmer_id', 'released_by')
                ->get();
			
			$excel_data = array();
            foreach($municipal_table_data as $row){
                $yield = 0;
                $yield = $row->yield;
                // if($row->yield <= 5 && $row->yield != 0){
                //     $yield = $row->yield * 20;
                // }else{
                //     $yield = $row->yield;
                // }
                
                // if($yield > 0 && $row->actual_area > 0){
                //     if($yield < 50 || $yield > 120){
                //         $yield = $yield / $row->actual_area;
                //     }else{
                //         $yield = $yield;
                //     }
                    
                // }else{
                //     $yield = $row->yield;
                // }

                $prv = $GLOBALS['season_prefix'].'prv_'.substr(str_replace("-", "", $prv_details->prv),0,4);
                    $date_synced = DB::table($prv.".released")
                        ->where("rsbsa_control_no", $row->rsbsa_control_number)
                        ->where("seed_variety", $row->seed_variety)
                        ->where("bags", $row->bags)
                        ->where("farmer_id", $row->farmer_id)
                        ->value("date_synced");
                    if(count($date_synced)<=0){
                        $date_synced = "N/A";
                    }

                // column : yield
                $total_production = 0;
                // column : weight_per_bag
                $weight_per_bag = 0;
                // column : area_harvested
                $area_harvested = 0;

                $farmer_profile = DB::table($prv_database.".farmer_profile")
                    ->select("yield", "weight_per_bag", "area_harvested")
                    ->where("rsbsa_control_no", $row->rsbsa_control_number)
                    ->first();

                if(count($farmer_profile) > 0){
                    $total_production = $farmer_profile->yield;
                    $weight_per_bag = $farmer_profile->weight_per_bag;
                    $area_harvested = $farmer_profile->area_harvested;
                }


                   $prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->select("prv")
                        ->where('province', $province)
                        ->where('municipality', $municipality)
                        ->first();
                    $area_claimed = 0;
                if(count($prv)>0){
                    $prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
                    $area_claimed = DB::table($prv_db.".released")
                                ->where("province", $province)
                                ->where("municipality", $municipality)
                                ->where("rsbsa_control_no",$row->rsbsa_control_number)
                                ->where("farmer_id", $row->farmer_id)
                                ->sum("claimed_area");          
                }



                array_push($excel_data, array(
                    "RSBSA #" => $row->rsbsa_control_number,
                    "QR Code" => $row->qr_code,
                    "Farmer's First Name" => $row->farmer_fname,
                    "Farmer's Middle Name" => $row->farmer_mname,
                    "Farmer's Last Name" => $row->farmer_lname,
                    "Farmer's Extension Name" => $row->farmer_ext,
                    "Sex" => $row->sex,
                    "Birth Date" => $row->birthdate,
                    "Telephone Number" => $row->tel_number,
                    "Province" => $row->province,
                    "Municipality" => $row->municipality,
                    "Mother's Maiden Name" => $row->mother_name,
                    "Actual Area" => $row->actual_area,
                    "area_claimed" => number_format($area_claimed,2,".",","),
                    "Bags" => $row->bags,
                    "Seed Variety" => $row->seed_variety,
                    "Yield(T/ha)" => number_format($yield,2,".",","),
                    "Date Released" => $row->date_released,
                    "Farmer ID" => $row->farmer_id,
                    "Released By" => $row->released_by,
                    "Date Synced" => $date_synced,
                    "Total Production" => $total_production,
                    "Ave. weight per bag(kg)" => $weight_per_bag,
                    "Area Harvested (ha)" => $area_harvested,
                ));
            }
            
            $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
			
            return Excel::create("$province"."_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    /*$sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        '#', 'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
                        "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                        'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
                        "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
                        'Actual Area', 'Bags', 'Seed Variety', 'Yield', 'Date Released', 'Farmer ID', 'Released By'
                    ));
                    $sheet->freezeFirstRow();
                    $sheet->getColumnDimension('A')->setVisible(false);*/
					$sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
					
					$sheet->setHeight(1, 30);
					$sheet->cells('A1:Z1', function ($cells) {
						$cells->setBackground('#92D050');
						$cells->setAlignment('center');
						$cells->setValignment('center');
					});
					$sheet->setBorder('A1:V1', 'thin');
                });
            })->download('xlsx');

            DB::commit();
        
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function export_municipality_withUPdate($province, $municipality){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {
            $prv_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
                ->where('province', $province)
                ->where('municipality', $municipality)
                ->first();

            $prv_database = $GLOBALS['season_prefix']."prv_".substr($prv_details->prv, 0, 4);
            $database_name = "rpt_".substr($prv_details->prv, 0, 4);

            $table_name = "tbl_".$prv_details->prv;
            $this->process_municipalities($prv_database, $database_name, $table_name, $prv_details->municipality, $prv_details->province);
            
			//$municipal_data = DB::table($database_name.".".$table_name)->get();
			$municipal_data = DB::table($database_name.".".$table_name)
                ->select('id', 'rsbsa_control_number', 'qr_code', 'farmer_fname', 'farmer_mname', 'farmer_lname',
                             'farmer_ext', 'sex', 'birthdate', 'tel_number', 'province', 'municipality', 'mother_name', 'dist_area', 'actual_area', 'bags', 'seed_variety', 'yield',
                             'date_released', 'farmer_id', 'released_by')
                ->get();

            //after update is finished call function to export to excel
            $excel_data = json_decode(json_encode($municipal_data), true); //convert collection to associative array to be converted to excel
            return Excel::create("$province"."_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $sheet->prependRow(1, array(
                        '#', 'RSBSA #', 'QR Code', "Farmer's First Name", "Farmer's Middle Name", 
                        "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                        'Telephone Number', 'Province', 'Municipality', "Mother's First Name",
                        "Mother's Middle Name", "Mother's Last Name", "Mother's Suffix", 'Distribution Area',
                        'Actual Area', 'Bags', 'Seed Variety', 'Yield', 'Date Released', 'Farmer ID', 'Released By'
                    ));
                    $sheet->freezeFirstRow();
                    $sheet->getColumnDimension('A')->setVisible(false);
                });
            })->download('xlsx');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }
	
	/**
     * NRP EXPORT
     */
    public function export_nrp_function($prv_name){
        //get nrp daTa
        $nrp_data = DB::table($prv_name.".nrp_profile")
            ->select('rsbsa', 'fname', 'mname', 'lname', 'extname', 'sex', 'birthdate', 'phonenumber', 'claimed_seed', 'num_of_bag', 'package_weight')
            ->addSelect(DB::RAW("(num_of_bag * weight_per_bag / area_harvested) / 1000 as farmer_yield"))
            ->get();

        $nrp_data = json_decode(json_encode($nrp_data), true); //convert collection to associative array to be converted to excel
        
        return Excel::create("NRP_PROFILE"."_".date("Y-m-d g:i A"), function($excel) use ($nrp_data) {
            $excel->sheet("NRP PROFILES", function($sheet) use ($nrp_data) {
                $sheet->fromArray($nrp_data, null, 'A1', false, false);
                $sheet->prependRow(1, array(
                    'RSBSA #', "Farmer's First Name", "Farmer's Middle Name", 
                    "Farmer's Last Name", "Farmer's Extension Name", 'Sex', 'Birth Date',
                    'Telephone Number', 'Claimed Seeds', 'Number of Bags', 'Package Weight', 'Yield (t/ha)'
                ));
                $sheet->freezeFirstRow();
            });
        })->download('xlsx');
    }


    public function getFiles(){

        $publicDirectory = public_path("reports//excel_export//");
        $return_array = array();
        if (File::exists($publicDirectory)) {
            $files = File::allFiles($publicDirectory);
            
            // Now $files contains an array of SplFileInfo objects representing the files in the directory.
            // You can loop through them or process them as needed.
    
        
            foreach ($files as $file) {
                $filePath = $file->getPathname(); // Full path of the file
                $fileName = $file->getFilename(); // Name of the file
                $lastModifiedTimestamp = File::lastModified($filePath);
                array_push($return_array, array(
                    "file_name" => $fileName,
                 
                    "date_generated" => date("Y-m-d", $lastModifiedTimestamp)
                ));
            // dd($filePath,$fileName,$return_array);
            }
        }

        $sortedData = collect($return_array);

        // dd($sortedData);
        return Datatables::of($sortedData)
        ->addColumn('action', function($row){
            return  '<a href="https://rcef-seed.philrice.gov.ph/rcef_'.str_replace('_','',$GLOBALS['season_prefix']).'/public/reports/excel_export/'.$row['file_name'].'" target="_blank" class="btn btn-success btn-sm download_file"> <i class="fa fa-cloud-download" aria-hidden="true"> Download File</i> </a>';            
        })
        ->make(true);

    }
}

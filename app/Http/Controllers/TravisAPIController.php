<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;




// use DB;
use Session;
use Auth;
use Excel;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;
use App\utility;

class TravisAPIController extends Controller
{

    public function login($prri_id){
        $user = DB::table('rcef_travis.tbl_employee')
            ->where('employeeNumber',$prri_id)
            ->get();
        foreach($user as $row){ 
            return json_encode($row);
        }
    }

    public function getBranches(){
        $branches = DB::table('rcef_travis.lib_stations')
            ->select('station_name')
            ->get();
            return $branches;
    }

    public function getProvinces(){
        $provinces = DB::table('rcef_travis.lib_national')
        ->select('province')
        ->groupBy('province')
        ->get();
        return $provinces;
    }
    
    public function getmunicipalities($province){
        $municipalities = DB::table('rcef_travis.lib_national')
            ->select('municipality')
            ->where('province',$province)
            ->get();
            return $municipalities;
    }

    public function getCluster($province)
    {

    }

    public function syncData(Request $request)
    {
        $activityData = json_decode($request->getContent());
        // dd($activityData);

        foreach($activityData as $row)
        {
            $validate = DB::table('rcef_travis.tbl_activities')
            ->select('time_stamp')
            ->where('employeeNumber','=',$row->employeeNumber)
            ->where('time_stamp','=', $row->time_stamp)     
            ->get();

            if(collect($validate)->isEmpty() && $row->isDeleted == false){
                DB::table('rcef_travis.tbl_activities')
                ->insert([
                    "employeeNumber" => $row->employeeNumber,
                    "origin" => $row->origin,
                    "destinationProvince" => $row->destinationProvince,
                    "destinationMunicipality" => $row->destinationMunicipality,
                    "cluster" => $row->cluster,
                    "activityDate" => $row->activityDate,
                    "activities" => $row->activities,
                    "transpoCharging" => $row->transpoCharging,
                    "accommodationCharging" => $row->accommodationCharging,
                    "mealCharging" => $row->mealCharging,
                    "meal_bfast" => $row->meal_bfast,
                    "meal_lunch" => $row->meal_lunch,
                    "meal_dinner" => $row->meal_dinner,
                    "incidentalCharging" => $row->incidentalCharging,
                    "accomodationAmount" => $row->accomodationAmount,
                    "mealAmount" => $row->mealAmount,
                    "incidentalAmount" => $row->incidentalAmount,
                    "totalClaimable" => $row->totalClaimable,
                    "actualExpenses" => $row->actualExpenses,
                    "OR_Key" => $row->OR_Key,
                    "time_stamp" => $row->time_stamp
                ]);
            }
            else{
                if($row->isUpdated == true && $row->isDeleted == false)
                {
                    DB::table('rcef_travis.tbl_activities')
                    ->where('employeeNumber','=',$row->employeeNumber)
                    ->where('time_stamp','=', $row->time_stamp)
                    ->update([
                        "employeeNumber" => $row->employeeNumber,
                        "origin" => $row->origin,
                        "destinationProvince" => $row->destinationProvince,
                        "destinationMunicipality" => $row->destinationMunicipality,
                        "cluster" => $row->cluster,
                        "activityDate" => $row->activityDate,
                        "activities" => $row->activities,
                        "transpoCharging" => $row->transpoCharging,
                        "accommodationCharging" => $row->accommodationCharging,
                        "mealCharging" => $row->mealCharging,
                        "meal_bfast" => $row->meal_bfast,
                        "meal_lunch" => $row->meal_lunch,
                        "meal_dinner" => $row->meal_dinner,
                        "incidentalCharging" => $row->incidentalCharging,
                        "accomodationAmount" => $row->accomodationAmount,
                        "mealAmount" => $row->mealAmount,
                        "incidentalAmount" => $row->incidentalAmount, 
                        "time_stamp" => $row->time_stamp,
                        "totalClaimable" => $row->totalClaimable,
                        "OR_Key" => $row->OR_Key
                    ]);
                }
                else if($row->isDeleted == true)
                {
                    DB::table('rcef_travis.tbl_activities')
                    ->where('employeeNumber','=',$row->employeeNumber)
                    ->where('time_stamp','=', $row->time_stamp)
                    ->delete();
                }

            }
        }

        return $activityData;
    }


    public function syncTranspoOR(Request $request){
        $transpoOR = json_decode($request->getContent());


        foreach($transpoOR as $row)
        {
            foreach($row as $OR)
            {
                if(count($OR)>0){
                    if($OR->type == 'transportation'){
                        DB::table('rcef_travis.tbl_receipts')
                            ->where('OR_Key','=',$OR->OR_Key) 
                            ->where('type','=',$OR->type)
                            ->delete();
                    }
                }
            }
        }

        foreach($transpoOR as $row)
        {
            foreach($row as $OR)
            {
                if(count($OR)>0){
                    if($OR->type == 'transportation'){
                        DB::table('rcef_travis.tbl_receipts')
                        ->insert([
                            "type" => $OR->type,
                            "OR_Key" => $OR->OR_Key,
                            "OR_number" => $OR->OR_number,
                            "Amount" => $OR->Amount
                        ]);
                    }
                }
            }
        }
        return 1;
    }

    public function syncAccommOR(Request $request){
        $accommOR = json_decode($request->getContent());

        foreach($accommOR as $row)
        {
            foreach($row as $OR)
            {
                if(count($OR)>0){
                    if($OR->type == 'accommodation'){
                        DB::table('rcef_travis.tbl_receipts')
                            ->where('OR_Key','=',$OR->OR_Key) 
                            ->where('type','=',$OR->type)
                            ->delete();
                    }
                }
            }
        }

        foreach($accommOR as $row)
        {
            foreach($row as $OR)
            {
                if(count($OR)>0){
                    if($OR->type == 'accommodation'){
                        DB::table('rcef_travis.tbl_receipts')
                        ->insert([
                            "type" => $OR->type,
                            "OR_Key" => $OR->OR_Key,
                            "OR_number" => $OR->OR_number,
                            "Amount" => $OR->Amount
                        ]);
                    }
                }
            }
        }
        return 1;
    }

    public function deleteOR(Request $request)
    {   
        $ORs = json_decode($request->getContent());
        // return $ORs;
        $or_collection = [];
        foreach ($ORs as $OR) {
            // return DB::table('rcef_travis.tbl_receipts')
            // ->where('OR_Key','=',$ORs)
            // ->delete() 
            // ->toSql();
            array_push($or_collection, $OR);
        }

        return DB::table('rcef_travis.tbl_receipts')
            ->whereIn('OR_key', $or_collection)
            ->delete();
            
    }

//     public function syncAttachments(Request $request)
//     {
//     try {
//         // Your code here
//     ini_set('memory_limit', -1);

//     $finalAttachment = [];
//     $attachments = json_decode($request->getContent());
//     return $attachments;

//     foreach ($attachments as $attachment) {
//         $validate = DB::table('rcef_travis.tbl_attachments')
//             ->select('key')
//             ->where('key', '=', $attachment->empKey)
//             ->get();

//         if (collect($validate)->isEmpty()) {
//             foreach ($attachment->files as $files) {
//                 // Convert base64 data to an image and get the file path
//                 $filePath = $this->convertAndSaveImage($files->base64Data);

//                 // Insert the file path into the database
//                 DB::table('rcef_travis.tbl_attachments')
//                     ->insert([
//                         "key" => $attachment->empKey,
//                         "fileName" => $files->file,
//                         "base64Data" => $filePath,
//                     ]);
//             }
//         } else {
//             // Delete existing attachments for the given key
//             DB::table('rcef_travis.tbl_attachments')
//                 ->where('key', '=', $attachment->empKey)
//                 ->delete();

//             foreach ($attachment->files as $files) {
//                 // Convert base64 data to an image and get the file path
//                 $filePath = $this->convertAndSaveImage($files->base64Data);

//                 // Insert the file path into the database
//                 DB::table('rcef_travis.tbl_attachments')
//                     ->insert([
//                         "key" => $attachment->empKey,
//                         "fileName" => $files->file,
//                         "base64Data" => $filePath,
//                     ]);
//             }
//         }
//     }

//     } catch (Exception $e) {
//         // Log or display the exception message
//         Log::error($e->getMessage());
//         return response()->json(['error' => 'Internal server error'], 500);
//     }
    
// }

// private function convertAndSaveImage($base64Data)
// {
//     // Generate a unique file name
//     $fileName = uniqid() . '.jpg';

//     // Decode base64 data
//     $imageData = base64_decode($base64Data);

//     // Specify the directory where you want to save the images
//     $directory = public_path('travis_attachments/');

//     // Create the directory if it doesn't exist
//     if (!file_exists($directory)) {
//         mkdir($directory, 0777, true);
//     }

//     // Save the image file
//     $filePath = $directory . $fileName;
//     file_put_contents($filePath, $imageData);

//     return $filePath;
// }

// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//     public function deleteAttachments(Request $request)
//     {
//         $finalAttachment =[];
//         $attachments = json_decode($request->getContent());

//         foreach ($attachments as $attachment) {
//             DB::table('rcef_travis.tbl_receipts')
//             ->where('key','=',$attachment) 
//             ->delete();
//         }

//     }
    
    
    // public function syncAttachments(Request $request)
    // {
    //     ini_set('memory_limit', -1);

    //     $finalAttachment =[];
    //     $attachments = json_decode($request->getContent());
    //     // return $attachments;
    //     // return $attachments[0];
    //     // return $attachments[0]->empKey;
    //     // return $attachments[0]->files;
    //     // return $attachments[0]->files[0];
    //     // return $attachments[0]->files[0]->file;
    //     // return $attachments[0]->files[0]->base64Data;

        
    //     foreach ($attachments as $attachment) {
    //             $validate = DB::table('rcef_travis.tbl_attachments')
    //             ->select('key')
    //             ->where('key','=',$attachment->empKey)    
    //             ->get();

    //             if(collect($validate)->isEmpty()){
    //                 foreach ($attachment->files as $files){
    //                     DB::table('rcef_travis.tbl_attachments')
    //                     ->insert([
    //                         "key" => $attachment->empKey,
    //                         "fileName" => $files->file,
    //                         "base64Data" => $files->base64Data,
    //                     ]);
    //                 }
    //             }
    //             else{
    //                 foreach ($attachment->files as $files){
    //                     DB::table('rcef_travis.tbl_attachments')
    //                     ->where('key','=',$attachment->empKey) 
    //                     ->delete();

    //                     foreach ($attachment->files as $files){
    //                         DB::table('rcef_travis.tbl_attachments')
    //                         ->insert([
        //                             "key" => $attachment->empKey,
        //                             "fileName" => $files->file,
        //                             "base64Data" => $files->base64Data,
        //                         ]);
        //                     }
    //                 }
    //             }
    //     }

    //     // return $finalAttachment;
    
    // }

    
    ////////////////////////////////////////////////////////////////////////////////////////////

    // function getMissingFarmers(Request $request){
        
    //     $decData = DB::connection("ds2024")->table("ds2024_prv_".$request->province.".farmer_information_final_x")
    //     ->get();

    //     // DB::statement("create table ds2024_prv_".$request->province.".farmer_information_final_x_2 like ds2024_prv_".$request->province.".farmer_information_final_x");

    //     foreach($decData as $dec)
    //     {
    //         $mayData = DB::connection("ds2024")->table("ds2024_prv_".$request->province.".farmer_information_final")
    //         ->where('lastName','=',$dec->lastName)
    //         ->where('firstName','=',$dec->firstName)
    //         ->where('midName','=',$dec->midName)
    //         ->where('extName','=',$dec->extName)
    //         ->where('rsbsa_control_no','=',$dec->rsbsa_control_no)
    //         ->get();

    //         if(!$mayData)
    //         {
    //             DB::connection("ds2024")->table("ds2024_prv_".$request->province.".farmer_information_final_x_2")
    //             ->insert([
    //                 "rsbsa_control_no" => $dec->rsbsa_control_no,
    //                 "db_ref" => $dec->db_ref,
    //                 "rcef_id" => $dec->rcef_id,
    //                 "farmer_id" => $dec->farmer_id,
    //                 "distributionID" => $dec->distributionID,
    //                 "da_intervention_card" => $dec->da_intervention_card,
    //                 "lastName" => $dec->lastName,
    //                 "firstName" => $dec->firstName,
    //                 "midName" => $dec->midName,
    //                 "extName" => $dec->extName,
    //                 "fullName" => $dec->fullName,
    //                 "sex" => $dec->sex,
    //                 "birthdate" => $dec->birthdate,
    //                 "province" => $dec->province,
    //                 "municipality" => $dec->municipality,
    //                 "brgy_name" => $dec->brgy_name,
    //                 "mother_lname" => $dec->mother_lname,
    //                 "mother_fname" => $dec->mother_fname,
    //                 "mother_mname" => $dec->mother_mname,
    //                 "mother_suffix" => $dec->mother_suffix,
    //                 "tel_no" => $dec->tel_no,
    //                 "geo_code" => $dec->geo_code,
    //                 "civil_status" => $dec->civil_status,
    //                 "id_type" => $dec->id_type,
    //                 "gov_id_num" => $dec->gov_id_num,
    //                 "fca_name" => $dec->fca_name,
    //                 "is_pwd" => $dec->is_pwd,
    //                 "is_arb" => $dec->is_arb,
    //                 "is_ip" => $dec->is_ip,
    //                 "tribe_name" => $dec->tribe_name,
    //                 "ben_4ps" => $dec->ben_4ps,
    //                 "data_source" => $dec->data_source,
    //                 "sync_date" => $dec->sync_date,
    //                 "crop_establishment_cs" => $dec->crop_establishment_cs,
    //                 "ecosystem_cs" => $dec->ecosystem_cs,
    //                 "ecosystem_source_cs" => $dec->ecosystem_source_cs,
    //                 "planting_week" => $dec->planting_week,
    //                 "ds2020_rsbsa" => $dec->ds2020_rsbsa,
    //                 "ws2020_rsbsa" => $dec->ws2020_rsbsa,
    //                 "ds2021_rsbsa" => $dec->ds2021_rsbsa,
    //                 "ws2021_rsbsa" => $dec->ws2021_rsbsa,
    //                 "ds2022_rsbsa" => $dec->ds2022_rsbsa,
    //                 "ws2022_rsbsa" => $dec->ws2022_rsbsa,
    //                 "ds2023_rsbsa" => $dec->ds2023_rsbsa,
    //                 "ds2020_area" => $dec->ds2020_area,
    //                 "ws2020_area" => $dec->ws2020_area,
    //                 "ds2021_area" => $dec->ds2021_area,
    //                 "ws2021_area" => $dec->ws2021_area,
    //                 "ds2022_area" => $dec->ds2022_area,
    //                 "ws2022_area" => $dec->ws2022_area,
    //                 "ds2023_area" => $dec->ds2023_area,
    //                 "ffrs_area" => $dec->ffrs_area,
    //                 "forced_area" => $dec->forced_area,

    //                 "final_area" => $dec->final_area,
    //                 "final_claimable" => $dec->final_claimable,
    //                 "is_claimed" => $dec->is_claimed,
    //                 "total_claimed" => $dec->total_claimed,
    //                 "total_claimed_area" => $dec->total_claimed_area,
    //                 "is_replacement" => $dec->is_replacement,
    //                 "replacement_area" => $dec->replacement_area,
    //                 "replacement_bags" => $dec->replacement_bags,
    //                 "replacement_bags_claimed" => $dec->replacement_bags_claimed,
    //                 "is_ebinhi" => $dec->is_ebinhi,
 
    //                 "print_count" => $dec->print_count,
    //                 "to_prv_code" => $dec->to_prv_code,
    //                 "process_data" => $dec->process_data
    //             ]);
    //         }
    //         DB::connection("ds2024")->table("ds2024_prv_".$request->province.".farmer_information_final_x")
    //             ->update([
    //                 "isChecked" => '1'
    //             ]);
    //     }

    //     return $filteredData;
    // }

    
}

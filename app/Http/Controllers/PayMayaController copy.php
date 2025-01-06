<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Session;
use Excel;

use Yajra\Datatables\Facades\Datatables;

class PayMayaController extends Controller
{

            public function paymaya_batches(){
                $batchList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->select("tbl_actual_delivery.*", "coopAccreditation")
                    ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery", "tbl_delivery.batchTicketNumber", "=" , "tbl_actual_delivery.batchTicketNumber")
                    ->where("qrValStart", "!=", "")
                    ->groupBy("batchTicketNumber","seedTag")
                    ->orderBy("coopAccreditation")
                    ->orderBy("batchTicketNumber")
                    ->get();

                    $array_list = array();
                    foreach($batchList as $batchData){

                            $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("accreditation_no", $batchData->coopAccreditation)->value("coopName");

                            $tbl_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                                ->where("seedTag", $batchData->seedTag)
                                ->count("seedTag");

                        array_push($array_list, array(
                            "Coop" => $coop_name,
                            "Batch Ticket" => $batchData->batchTicketNumber,
                            "Seed Tag" =>$batchData->seedTag,
                            "Bags Accepted" => $batchData->totalBagCount,
                            "Distributed" => $tbl_claim,
                        ));

                    }



                    $excel_data = json_decode(json_encode($array_list), true); //convert collection to associative array to be converted to excel
                    return Excel::create("EBINHI SEEDTAGS AND BATCHES"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                        $excel->sheet("EBINHI SEEDTAG AND BATCHES", function($sheet) use ($excel_data) {
                            $sheet->fromArray($excel_data);
                            $sheet->freezeFirstRow();
                            
                        });
                    })->download('xlsx');
            }



            //EBINHI SURVEY
            public function check_ph_id(){
                
                $request = json_decode(request()->getContent(), true);
                $ph_id = $request['philrice_id'];

           
                $api_key = $request['api_key'];
                
                if($api_key == "leKvg/BuUk5SeGTQEw/D1BZMKRaC3/BpnShD5UQ92gE="){
                     $survey_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_survey_answer")
                     ->select("claim_code as paymaya_code")
                     ->where("philrice_id", $ph_id)
                     ->groupBy("claim_code")
                     ->get();

                     if(count($survey_data)>0){
              
                            $survey_data =  json_decode(json_encode($survey_data),true);
                           
                            $beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                                ->whereIn("paymaya_code", $survey_data)
                                ->get();
 
                             return json_encode($beneficiary);
                     }else{
                             return json_encode("404");
                     }
                }else{
                 return json_encode("404");
                }
            }



            public function check_code_survey(){

                //$paymaya_code = $request->claim_code;
               $request = json_decode(request()->getContent(), true);
               $claim_code = $request['claimcode'];
              $api_key = $request['api_key'];
               
               if($api_key == "leKvg/BuUk5SeGTQEw/D1BZMKRaC3/BpnShD5UQ92gE="){
                    $claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                    ->where("paymaya_code", $claim_code)
                    ->first();
                    if(count($claim)>0){

                    $survey = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_survey_answer")
                        ->where("claim_code", $claim_code)
                        ->get();
                    if(count($survey)>0){
                        $beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                            ->select("tbl_beneficiaries.*", "tbl_survey_answer.*")
                            ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_survey_answer", "tbl_survey_answer.claim_code", "=", "tbl_beneficiaries.paymaya_code")
                            ->where("tbl_beneficiaries.paymaya_code", $claim_code)
                            ->limit(1)
                            ->get();
                    }else{
                        $beneficiary = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                            ->where("tbl_beneficiaries.paymaya_code", $claim_code)
                            ->limit(1)
                            ->get();
                    }

                            return json_encode($beneficiary);
                    }else{
                            return json_encode("404");
                    }
               }else{
                return json_encode("404");
               }

               

              
            }

            public function survey_answer(){
                $request = json_decode(request()->getContent(), true);
                
                if(count($request) == 19){
                    $api_key = $request[0];
                    unset($request[0]);
                    if($api_key == "f07231a2a41ddd3bf3a3c9ad18d11d7c"){
                        
                        $set1 = array(
                            "Oo" => 1,
                            "Hindi" => 0,
                            null => 9
                        );
                        $set2 = array(
                            "Sang-ayon" => 1,
                            "Walang kinikilingan" => 2,
                            "Hindi sang-ayon" =>3,
                            null => 9
                        );
                  
                            
            
                            try {
                                DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_survey_answer")
                                    ->where("claim_code",$request[2])
                                    ->delete();

                                DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_survey_answer")
                                ->insert([
                                     "philrice_id" => $request[1],
                                     "claim_code" => $request[2],
                                     "q1" => $set1[$request[3]],
                                     "q2" => $set2[$request[4]],
                                     "q3" => $set2[$request[5]],
                                     "q4" => $set2[$request[6]],
                                     "q5" => $set2[$request[7]],
                                     "q6" => $set2[$request[8]],
                                     "q7" => $set2[$request[9]],
                                     "q8" => $set2[$request[10]],
                                     "q9" => $set2[$request[11]],
                                     "q10" => $set2[$request[12]],
                                     "q11" => $set2[$request[13]],
                                     "q12" => $set2[$request[14]],
                                     "q13_a" => $request[15],
                                     "q13_b" => $request[16],
                                     "q14" => $set1[$request[17]],
                                     "q15" => $request[18]
                                ]);

                                DB::commit();
                                return json_encode("Survey Saved");
                            } catch (\Throwable $th) {
                                DB::rollback();
                                return json_encode("Saving Failed");
                                //throw $th;
                            }


    
                    }else{
                        return json_encode("404");
                    } 
                }
                
               
            }



            public function survey_question($api_key){
                if($api_key == "f07231a2a41ddd3bf3a3c9ad18d11d7c"){
                    $que = DB::table($GLOBALS['season_prefix']."rcep_paymaya.lib_survey_questions")
                        ->orderBy("rank")
                        ->get();

                        return json_encode($que);

                }else{
                    return json_encode("404");
                }


            }




        //EXCEL REPORT
        public function export_ebinhi_municipality($province, $municipality){
            $excel_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                ->select("rsbsa_control_no", "paymaya_code", "fullName", "qr_code", "seedVariety", "seedTag", "region", "province", "municipality", "barangay", "claimLocation", "phoneNumber", "released_by", "coopAccreditation")
                ->where("province", $province)
                ->where("municipality", $municipality)
                ->get();
            

                $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
                return Excel::create("EBINHI_BENEFICIARY_LIST_".$province."_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                    $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                        $sheet->fromArray($excel_data);
                        $sheet->freezeFirstRow();
                        
                    });
                })->download('xlsx');
    


        }


        public function update_area(Request $request){
           // dd($request->all());
            $paymaya_code = $request->code;
            $item = $request->item;
            $type = $request->type;

            if($type == "area"){
                $area = $item;
                if($area != floor($area)){
                    $dec =  $area - floor($area); 
    
                    if($dec <= 0.5 ){
                    $area = floor($area) + 0.5;
                    }else{
                        $area = floor($area) + 1;
                    }
                }
                $total_claimable = $area * 2;
    
                DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    ->where("paymaya_code", $paymaya_code)
                    ->update([
                        "area" => $item,
                        "bags" => $total_claimable
                    ]);

                    return json_encode("Area Changed to ".$item);
            }elseif($type=="variety"){
                DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_paymaya_lib")
                    ->where("paymaya_code", $paymaya_code)
                    ->update([
                        "variety_1" => $item
                    ]);

                    return json_encode("Variety Changed to ".$item);
            }else{
                return json_encode("Incorrect Type");
            }



        }

        public function exportUnclaimedCodes($province,$municipality,$week,$sched_start,$sched_end){
           // $province = "LAGUNA";
           // $municipality ="SINILOAN";
           // $week = "%";
           // $sched_start = "2021-05-10";
           // $sched_end = "2021-05-12";
            if($week =="-")$week="%";
            if($sched_start =="-")$sched_start="%";
            if($sched_end =="-")$sched_end="%";

            if($province =="-")$province="%";
            if($municipality =="-")$municipality="%";


                 $raw = "SELECT * from rcep_paymaya.tbl_beneficiaries where province LIKE '%".$province."%' and municipality LIKE '%".$municipality."%' and is_printed LIKE '"."%".$week."%"."'
                    and schedule_start LIKE '"."%".$sched_start."%"."'
                    and schedule_end LIKE '"."%".$sched_end."%"."'
                  and paymaya_code not in (SELECT paymaya_code from rcep_paymaya.tbl_claim) ";

                

                //$raw = "SELECT * from rcep_paymaya.tbl_claim";

                   /*$raw = "SELECT * from rcep_paymaya.tbl_beneficiaries where is_printed LIKE '"."%".$week."%"."'
                  and paymaya_code not in (SELECT paymaya_code from rcep_paymaya.tbl_claim) "; */


                $data = DB::select(DB::raw($raw));
                  $excel_data = json_decode(json_encode($data), true); //convert collection to associative array to be converted to excel
                    return Excel::create("eBinhi_Unclaimed_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                        $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                                    $row = 1;
                           $sheet->prependRow($row, array(                
                                "beneficiary_id","paymaya_code","contact_no","province","municipality","drop_off_point","schedule_start", 
                                "schedule_end","rsbsa_control_no","firstname","middname","lastname","extname","area","bags","region",
                                "province2","municipality2","barangay","is_active","sex","coop_accreditation","Week Uploaded"));  

                            foreach ($excel_data as $value) {
                                $row++;
                                //dd($value["beneficiary_id"]);
                                /*if($value["beneficiary_id"]=="3"){
                                 
                                    $cell = "A".$row.":V".$row;
                                    $sheet->cells($cell, function ($cells){
                                        $cells->setBackground('#92D050');
                                     }); 

                                    $sheet->row($row,$value);
                                */
                                       
                               // }else{
                                       $sheet->row($row,$value); 
                               // }
                                

                            }




                          //  $sheet->fromArray($excel_data, null, 'A1', false, false);
                                   
                              

                            $sheet->freezeFirstRow();
                        });
                    })->download('xlsx');
        }


          public function beneficiary_with_code(){
                $province = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    ->where("is_active", 1)
                    ->groupBy("province")
                    ->orderBy("province")
                    ->get();

                return view("paymaya.reports.beneficiary.tblBeneficiary")
                    ->with("province", $province);
        }


        public function genTable_with_code(Request $request){
            //dd($request->all());
            $list = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                ->select("rcep_paymaya.tbl_beneficiaries.*", $GLOBALS['season_prefix']."rcep_paymaya.lib_ebinhi_dop.coop_name as coopName")
                ->join($GLOBALS['season_prefix']."rcep_paymaya.lib_ebinhi_dop", "pickup_location", "=", "drop_off_point")
                ->where("is_active", 1)
                ->where("tbl_beneficiaries.province", $request->province)
                ->where("tbl_beneficiaries.municipality", "LIKE", "%".$request->municipality."%")
                ->where("tbl_beneficiaries.drop_off_point", "LIKE","%".$request->dop."%")
                ->orderBy("tbl_beneficiaries.region", "ASC")
                ->orderBy("tbl_beneficiaries.province", "ASC")
                ->orderBy("tbl_beneficiaries.municipality", "ASC")
                ->orderBy("tbl_beneficiaries.drop_off_point", "ASC")
                ->orderBy("tbl_beneficiaries.lastname", "ASC")
                ->orderBy("tbl_beneficiaries.firstname", "ASC")
                ->orderBy("tbl_beneficiaries.middname", "ASC")
                ->get();
         


                 $data_arr = array();

        foreach($list as $row){

            


            if(Auth::user()->roles->first()->name == "rcef-programmer"){
                $area_change = "<input type='number' onchange='utility_change(".'"'.$row->paymaya_code.'"'.",this.value,".'"area"'.")' style='width:80px;' id='area_input' name='area_input' value='".$row->area."'>";
            }else{
                $area_change = $row->area." (ha)";
            }


            array_push($data_arr, array(
                "rsbsa" => $row->rsbsa_control_no,
                "name" => $row->lastname.", ".$row->firstname." ".$row->middname,
                "contact"=> $row->contact_no,
                "sched_start" => $row->schedule_start,
                "sched_end" => $row->schedule_end,
                "dop" => $row->drop_off_point,
                "brgy" => $row->barangay,
                "gender" => $row->sex,
                "area" => $area_change,
                "coop" => $row->coopName,
                "bags" => $row->bags,
                "code" => "<b>".$row->paymaya_code."<b>",
            ));
        }


        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->make(true);
        }



    /**
     * INSPECTOR INTERFACE - EXCESS QR CODES
     */
        public function inspector_ui_home(){
            return view('paymaya.inspector.home');
        }

        public function search_seedtags(Request $request){
            $seed_tags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->where('labNo', 'like', $request->searchTerm.'%')
                ->orWhere('lotNo', 'like', $request->searchTerm.'%')
                ->limit(50)
                ->get();

            $data = array();
            foreach($seed_tags as $row){
                array_push($data, array(
                    "id" => $row->labNo."/".$row->lotNo,
                    "text" => $row->labNo."/".$row->lotNo
                ));
            }

            return json_encode($data);
        }

        public function flag_seedtag_unusable(Request $request){
            DB::beginTransaction();
            try {
                $seedTag = $request->tag;
                $qr_code = $request->qr_code;

                $data = array(
                    "seed_tag" => $request->tag,
                    "qr_code" => $request->qr_code,
                    "performed_by" => Auth::user()->username,
                    "date_recorded" => date("Y-m-d H:i:s")
                );
                DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_excess_tags')->insert($data);
                DB::commit();

                return "flag_success";
            } catch (\Exception $ex) {
                DB::rollback();
                //dd($ex);
                return "sql_error";
            }
        }
    /** END */

    /**
     * DIRSTRIBUTION MONITORING - START
     */
    public function seed_distribution_home(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')->groupBy('province')->get();
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->groupBy('province', 'municipality')
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();
        
        return view('paymaya.distribution.home')
            ->with('provinces', $provinces)
            ->with('municipalities', $municipalities);
    }

    public function seed_distribution_tblMunicipal(Request $request){
        return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->where('province', $request->province)
            ->where('municipality', $request->municipality)
            ->orderBy('rsbsa_control_no', 'ASC')
            ->orderBy('firstname', 'ASC')
            ->orderBy('middname', 'ASC')
            ->orderBy('lastname', 'ASC'))
        ->addColumn('extension_name', function($row){
            return  $row->extname == "" ? "N/A" : $row->extname;
        })
        ->make(true);
    }

    public function seed_distribution_municipal_totals(Request $request){
        $municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select(DB::raw('COUNT(beneficiary_id) as total_farmers'),
                DB::raw('SUM(bags) as total_bags'),
                DB::raw('SUM(area) as total_area'))
            ->where('province', $request->province)
            ->where('municipality', $request->municipality)
            ->first();

        return array(
            "total_farmers" => number_format($municipal_details->total_farmers),
            "total_bags" => number_format($municipal_details->total_bags),
            "total_area" => number_format($municipal_details->total_area,"2",".",",")
        );
    }

    public function seed_distribution_municipal_list(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();

        $municipal_arr = array();
        foreach($municipalities as $row){
            array_push($municipal_arr, $row->municipality);
        }
        $municipal_arr = array_unique($municipal_arr);

        return $municipal_arr;
    }
    /**
     * DIRSTRIBUTION MONITORING - END
     */


    /**
     * REPORT ROUTES - START
     */

    public function generate_provincial_report($province,$from,$to){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {            
            $from_ex = explode("-", $from);
            $from_date = date($from_ex[2]."-".$from_ex[0]."-".$from_ex[1]." 16:00:01");

            $to_ex = explode("-", $to);
            $to_date = date($to_ex[2]."-".$to_ex[0]."-".$to_ex[1]." 16:00:00");
//            dd(date("M-d-y",strtotime($from_date)));

            $from = date("Y-m-d H:i:s", strtotime("-1 day", strtotime($from_date))); 
            $to = date("Y-m-d H:i:s", strtotime($to_date));

            //dd($from." ".$to);
            $provincial_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->select("*", DB::raw("count(seedVariety) as bags"))
                ->where('province', $province)
                //->whereBetween("date_created", [$from, $to])
                //->where("municipality", "LALA")
                ->groupBy('seedVariety')
                ->groupBy('paymaya_code')
                ->orderBy('paymaya_code', 'ASC')
                ->orderBy("date_created", 'ASC')
                ->get();
  
            $province_arr = array();
            foreach($provincial_data as $row){
                $beneficiary_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')->where('beneficiary_id', $row->beneficiary_id)->first();
               // dd($beneficiary_details);
               
                if(count($beneficiary_details)<=0){
                    //dd($row->beneficiary_id);
                }

                $seedtag_explode = explode("/",$row->seedTag);
                $int = (int) filter_var($seedtag_explode[0], FILTER_SANITIZE_NUMBER_INT);
                $int = str_ireplace("-","",$int);
//                    dd( $beneficiary_details->coop_accreditation);
                $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("accreditation_no", $beneficiary_details->coop_accreditation)->value("coopName");
                     // dd($coop_name);
                    if($coop_name == null){
                        $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("updated_accreditation_no", $beneficiary_details->coop_accreditation)->value("coopName");
                        if($coop_name == null){
                            $coop_name = "N/A";
                        }
                    }



                /*$seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                    ->where('labNo',"LIKE", "%".$int."%")
                    ->where('lotNo',"LIKE", "%".$seedtag_explode[1]."%")
                    ->value('sg_name'); */
                    $rowDate = date("Y-m-d H:i:s", strtotime($row->date_created));

                    if(strtotime($rowDate)>= strtotime($from) AND strtotime($rowDate) <= strtotime($to)){
                        if(count($beneficiary_details) > 0){
                                array_push($province_arr, array(
                                    "coop_name" => $coop_name,
                                    "shcedule" => date("M j, Y", strtotime($beneficiary_details->schedule_start))." - ".date("M j, Y", strtotime($beneficiary_details->schedule_end)),
                                    "rsbsa_control_no" => $row->rsbsa_control_no,
                                    "firstname" => $beneficiary_details->firstname,
                                    "middname" => $beneficiary_details->middname,
                                    "lastname" => $beneficiary_details->lastname,
                                    "extname" => $beneficiary_details->extname,
                                    "paymaya_code" => $row->paymaya_code,
                                //  "qr_code" => $row->qr_code,
                                    "date_created" => $row->date_created,
                                    "province" => $row->province,
                                    "municipality" => $row->municipality,
                                    "barangay" => $row->barangay,
                                    "drop_off_point" => $beneficiary_details->drop_off_point,
                                    "phoneNumber" => $row->phoneNumber,
                                    "area" => $beneficiary_details->area,
                                    "bags" => $row->bags,
                                    "seedVariety" => $row->seedVariety
                                    
                                //  "seed_grower" => $seed_grower,
                                //  "seedTag" => $row->seedTag
                                ));
                            }else{
                                array_push($province_arr, array(
                                    "coop_name" => "N/A",
                                    "shcedule" => "N/A",
                                    "rsbsa_control_no" => $row->rsbsa_control_no,
                                    "firstname" => "N/A",
                                    "middname" => "N/A",
                                    "lastname" => "N/A",
                                    "extname" => "N/A",
                                    "paymaya_code" => $row->paymaya_code,
                                //  "qr_code" => $row->qr_code,
                                    "date_created" => $row->date_created,
                                    "province" => $row->province,
                                    "municipality" => $row->municipality,
                                    "barangay" => $row->barangay,
                                    "drop_off_point" => "N/A",
                                    "phoneNumber" => $row->phoneNumber,
                                    "area" => $beneficiary_details->area,
                                     "bags" => $row->bags,
                                    "seedVariety" => $row->seedVariety
                                   
                                //  "seed_grower" => $seed_grower,
                                //  "seedTag" => $row->seedTag
                                ));
                            }
             
                    }

            }

            $excel_data = json_decode(json_encode($province_arr), true); //convert collection to associative array to be converted to excel
            return Excel::create("eBinhi_"."$province"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                   // $sheet->fromArray($excel_data, null, 'A1', false, false);
                    /*$sheet->prependRow(1, array(
                        "Schedule", 'RSBSA #', "First Name", "Middle Name", "Last Name", "Ext Name", "Paymaya Code",
                        "QR Code", "Date Claimed", "Province", "Municipality", "Barangay", "Pickup Point", "Phone Number",
                        "Area", "Seed Variety", "Seed Grower", "Seed Tag"
                    ));*/
                    $row = 1;
                    $sheet->prependRow($row, array(
                        "Cooperative","Schedule", 'RSBSA #', "First Name", "Middle Name", "Last Name", "Ext Name", "e-Binhi Code",
                         "Date Claimed", "Province", "Municipality", "Barangay", "Pickup Point", "Phone Number",
                        "Area",  "Bags", "Seed Variety 1"
                    ));

                    $paycode = "";
                    $bags = 0;
                    $finalCol = "Q";
                    $varityCount = 1;
                    $fr =1;
                    $columnwithdata = array();
                    foreach ($excel_data as $key => $value) {
                        
                        if($paycode==$value["paymaya_code"]){
                            $bags += $value["bags"];
                            $lc++;
                            if($finalCol<$lc){
                               $finalCol = $lc;
                            }

                            $varityCount ++;
                            $sheet->cell($lc.$row, function($cell) use ($value) {
                            $cell->setValue($value['seedVariety']);
                            });
                            $columnwithdata[$row] = $lc.$row;
                            $sheet->cell("P".$row, function($cell) use ($bags) {
                            $cell->setValue($bags);
                            });
                            $sheet->cell($lc."1", function($cell) use ($value,$varityCount) {
                            $cell->setValue("Seed Variety ".$varityCount);
                            });
                            $hour = date("H:i:s", strtotime($value["date_created"]));
                            if(strtotime($hour)>strtotime(date("H:i:s",strtotime("16:00:00")))){  
                                $cell = $fc.$row.":".$lc.$row;
                                $sheet->cells($cell, function ($cells){
                                    $cells->setBackground('#FFB01F');
                                 });         
                            }else{
                                //$sheet->row($row,$value); 
                            }
                        }else{
                            $varityCount = 1;
                            $bags = $value["bags"];
                            $fc = "A";
                            $lc = "Q";
                            $paycode = $value["paymaya_code"];
                            $row++;
                            $hour = date("H:i:s", strtotime($value["date_created"]));
                            if(strtotime($hour)>strtotime(date("H:i:s",strtotime("16:00:00")))){  
                                $cell = $fc.$row.":".$lc.$row;
                                $sheet->cells($cell, function ($cells){
                                    $cells->setBackground('#FFB01F');
                                 }); 

                                $sheet->row($row,$value);           
                            }else{
                                   $sheet->row($row,$value); 
                            }  
                        }    
                    $lr = $row;
                    }

                    $row2= 1;
                    foreach ($excel_data as $key => $value) {
                        $row2++;
                        if($row2<=$lr){
                          $finalCol;
                            $lcr="Q"; 
                            if($finalCol>$lcr){
                                    if(!isset($columnwithdata[$row2])){
                                        do{
                                            $lcr++;
                                             $sheet->cell($lcr.$row2, function($cell){
                                            $cell->setValue("N/A");
                                            });
                                        }
                                        while($finalCol > $lcr);
                                    }  
                               }
                        }   
                    }
                     
                            



                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }


      public function generate_municipal_report_old($province, $municipality){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {            
        
            //dd($from." ".$to);
            $provincial_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->select("*", DB::raw("count(seedVariety) as bags"))
                ->where('province', $province)
                ->where('municipality', $municipality)
                //->whereBetween("date_created", [$from, $to])
                //->where("municipality", "LALA")
                ->groupBy('seedVariety')
                ->groupBy('paymaya_code')
                ->orderBy('paymaya_code', 'ASC')
                ->orderBy("date_created", 'ASC')
                ->get();
            
            $province_arr = array();
            
            foreach($provincial_data as $row){
                $beneficiary_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')->where('beneficiary_id', $row->beneficiary_id)->first();
               // dd($beneficiary_details);
               
                if(count($beneficiary_details)<=0){
                    //dd($row->beneficiary_id);
                }

                $seedtag_explode = explode("/",$row->seedTag);
                $int = (int) filter_var($seedtag_explode[0], FILTER_SANITIZE_NUMBER_INT);
                $int = str_ireplace("-","",$int);
//                    dd( $beneficiary_details->coop_accreditation);
                $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("accreditation_no", $beneficiary_details->coop_accreditation)->value("coopName");
                     // dd($coop_name);
                    if($coop_name == null){
                        $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")->where("updated_accreditation_no", $beneficiary_details->coop_accreditation)->value("coopName");
                        if($coop_name == null){
                            $coop_name = "N/A";
                        }
                    }



                /*$seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                    ->where('labNo',"LIKE", "%".$int."%")
                    ->where('lotNo',"LIKE", "%".$seedtag_explode[1]."%")
                    ->value('sg_name'); */
                    $rowDate = date("Y-m-d H:i:s", strtotime($row->date_created));

                   // if(strtotime($rowDate)>= strtotime($from) AND strtotime($rowDate) <= strtotime($to)){
                        if(count($beneficiary_details) > 0){
                                array_push($province_arr, array(
                                    "coop_name" => $coop_name,
                                    "shcedule" => date("M j, Y", strtotime($beneficiary_details->schedule_start))." - ".date("M j, Y", strtotime($beneficiary_details->schedule_end)),
                                    "rsbsa_control_no" => $row->rsbsa_control_no,
                                    "firstname" => $beneficiary_details->firstname,
                                    "middname" => $beneficiary_details->middname,
                                    "lastname" => $beneficiary_details->lastname,
                                    "extname" => $beneficiary_details->extname,
                                    "paymaya_code" => $row->paymaya_code,
                                //  "qr_code" => $row->qr_code,
                                    "date_created" => $row->date_created,
                                    "province" => $row->province,
                                    "municipality" => $row->municipality,
                                    "barangay" => $row->barangay,
                                    "drop_off_point" => $beneficiary_details->drop_off_point,
                                    "phoneNumber" => $row->phoneNumber,
                                    "area" => $beneficiary_details->area,
                                    "bags" => $row->bags,
                                    "seedVariety" => $row->seedVariety
                                    
                                //  "seed_grower" => $seed_grower,
                                //  "seedTag" => $row->seedTag
                                ));
                            }else{
                                array_push($province_arr, array(
                                    "coop_name" => "N/A",
                                    "shcedule" => "N/A",
                                    "rsbsa_control_no" => $row->rsbsa_control_no,
                                    "firstname" => "N/A",
                                    "middname" => "N/A",
                                    "lastname" => "N/A",
                                    "extname" => "N/A",
                                    "paymaya_code" => $row->paymaya_code,
                                //  "qr_code" => $row->qr_code,
                                    "date_created" => $row->date_created,
                                    "province" => $row->province,
                                    "municipality" => $row->municipality,
                                    "barangay" => $row->barangay,
                                    "drop_off_point" => "N/A",
                                    "phoneNumber" => $row->phoneNumber,
                                    "area" => $beneficiary_details->area,
                                     "bags" => $row->bags,
                                    "seedVariety" => $row->seedVariety
                                   
                                //  "seed_grower" => $seed_grower,
                                //  "seedTag" => $row->seedTag
                                ));
                            }
             
                   // }

            }

            $excel_data = json_decode(json_encode($province_arr), true); //convert collection to associative array to be converted to excel
            return Excel::create("eBinhi_"."$province"."_"."$municipality"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data) {
                   // $sheet->fromArray($excel_data, null, 'A1', false, false);
                    /*$sheet->prependRow(1, array(
                        "Schedule", 'RSBSA #', "First Name", "Middle Name", "Last Name", "Ext Name", "Paymaya Code",
                        "QR Code", "Date Claimed", "Province", "Municipality", "Barangay", "Pickup Point", "Phone Number",
                        "Area", "Seed Variety", "Seed Grower", "Seed Tag"
                    ));*/
                    $row = 1;
                    $sheet->prependRow($row, array(
                        "Cooperative","Schedule", 'RSBSA #', "First Name", "Middle Name", "Last Name", "Ext Name", "e-Binhi Code",
                         "Date Claimed", "Province", "Municipality", "Barangay", "Pickup Point", "Phone Number",
                        "Area",  "Bags", "Seed Variety 1"
                    ));

                    $paycode = "";
                    $bags = 0;
                    $finalCol = "Q";
                    $varityCount = 1;
                    $fr =1;
                    $columnwithdata = array();
                    foreach ($excel_data as $key => $value) {
                        
                        if($paycode==$value["paymaya_code"]){
                            $bags += $value["bags"];
                            $lc++;
                            if($finalCol<$lc){
                               $finalCol = $lc;
                            }

                            $varityCount ++;
                            $sheet->cell($lc.$row, function($cell) use ($value) {
                            $cell->setValue($value['seedVariety']);
                            });
                            $columnwithdata[$row] = $lc.$row;
                            $sheet->cell("P".$row, function($cell) use ($bags) {
                            $cell->setValue($bags);
                            });
                            $sheet->cell($lc."1", function($cell) use ($value,$varityCount) {
                            $cell->setValue("Seed Variety ".$varityCount);
                            });
                            $hour = date("H:i:s", strtotime($value["date_created"]));
                            if(strtotime($hour)>strtotime(date("H:i:s",strtotime("16:00:00")))){  
                                $cell = $fc.$row.":".$lc.$row;
                                $sheet->cells($cell, function ($cells){
                                    $cells->setBackground('#FFB01F');
                                 });         
                            }else{
                                //$sheet->row($row,$value); 
                            }
                        }else{
                            $varityCount = 1;
                            $bags = $value["bags"];
                            $fc = "A";
                            $lc = "Q";
                            $paycode = $value["paymaya_code"];
                            $row++;
                            $hour = date("H:i:s", strtotime($value["date_created"]));
                            if(strtotime($hour)>strtotime(date("H:i:s",strtotime("16:00:00")))){  
                                $cell = $fc.$row.":".$lc.$row;
                                $sheet->cells($cell, function ($cells){
                                    $cells->setBackground('#FFB01F');
                                 }); 

                                $sheet->row($row,$value);           
                            }else{
                                   $sheet->row($row,$value); 
                            }  
                        }    
                    $lr = $row;
                    }

                    $row2= 1;
                    foreach ($excel_data as $key => $value) {
                        $row2++;
                        if($row2<=$lr){
                          $finalCol;
                            $lcr="Q"; 
                            if($finalCol>$lcr){
                                    if(!isset($columnwithdata[$row2])){
                                        do{
                                            $lcr++;
                                             $sheet->cell($lcr.$row2, function($cell){
                                            $cell->setValue("N/A");
                                            });
                                        }
                                        while($finalCol > $lcr);
                                    }  
                               }
                        }   
                    }
                     
                            



                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }





    public function generate_municipal_report($province, $municipality){
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
        try {

            $provincial_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->where('province', $province)
                ->where('municipality', $municipality)
				->groupBy('beneficiary_id')
                ->get();
               
            $province_arr = array();
            $var_count = 1;
            foreach($provincial_data as $row){
             // dd($row);
                $row_data = array();
                $beneficiary_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')->where('beneficiary_id', $row->beneficiary_id)->first();
                
                if(count($beneficiary_details)<0){
                   // dd($row);
                    return "ERROR ON SYNCING CLAIM AND BENEFICIARY DETAILS => ".$row->paymaya_code;
                    
                }


                $seedtag_explode = explode("/",$row->seedTag);
				$int = (int) filter_var($seedtag_explode[0], FILTER_SANITIZE_NUMBER_INT);
				$int = str_ireplace("-","",$int);
                $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                    ->where('labNo',"LIKE", "%".$int."%")
                    ->where('lotNo',"LIKE", "%".$seedtag_explode[1]."%")
                    ->value('sg_name');

                $row_data["rsbsa_control_no"] = $row->rsbsa_control_no;
                $row_data["firstname"] = $beneficiary_details->firstname;
                $row_data["middname"] = $beneficiary_details->middname;
                $row_data["lastname"] = $beneficiary_details->lastname;
                $row_data["extname"] = $beneficiary_details->extname;
                $row_data["paymaya_code"] = $row->paymaya_code;
                $row_data["date_created"] = $row->date_created;
                $row_data["province"] = $row->province;
                $row_data["municipality"] = $row->municipality;
                $row_data["barangay"] = $row->barangay;
                $row_data["drop_off_point"] = $beneficiary_details->drop_off_point;
                $row_data["phoneNumber"] = $row->phoneNumber;
                $row_data["area"] = $beneficiary_details->area;
                $row_data["seed_grower"] = $seed_grower;
                


                $released_info = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->where('beneficiary_id', $row->beneficiary_id)
                ->get();

                $item_count = 1;
                foreach($released_info as $release){
                    $title = "QR_CODE_".$item_count;
                    $row_data[$title] = $release->qr_code;
                    $title = "SEED_TAG_".$item_count;
                    $row_data[$title] = $release->seedTag;
                    $title = "SEED VARIETY_".$item_count;
                    $row_data[$title] = $release->seedVariety;
                    $item_count++;
                   
                }
                $item_count--;
                if($item_count > $var_count){
                    $var_count = $item_count;
                }

// "qr_code" => $row->qr_code, "seedTag" => $row->seedTag, "seedVariety" => $row->seedVariety,



                array_push($province_arr, $row_data);   
          
            }
 
            $excel_data = json_decode(json_encode($province_arr), true); //convert collection to associative array to be converted to excel
            return Excel::create("PAYMAYA_"."$province"."_".$municipality."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $var_count) {
                $excel->sheet("BENEFICIARY LIST", function($sheet) use ($excel_data, $var_count) {
                    $sheet->fromArray($excel_data, null, 'A1', false, false);
                    $title_arr = array(
                        "rsbsa_control_no","firstname","middname","lastname","extname","paymaya_code","date_created","province","municipality","barangay","drop_off_point","phoneNumber","area","seed_grower"
                    );
                    for($x = 1; $x<=$var_count; $x++){
                       array_push($title_arr, "QR_CODE_".$x);
                       array_push($title_arr, "SEED_TAG_".$x);
                       array_push($title_arr, "VARIETY_".$x);
                       
                    }


                    $sheet->prependRow(1, $title_arr);


                    $sheet->freezeFirstRow();
                });
            })->download('xlsx');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function variety_report_ui(){
        $variety_totals = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->select('seedVariety',DB::raw('COUNT(seedVariety) as total_seed_variety'))
            ->groupBy('seedVariety')
            ->get();

        dd($variety_totals);
    }

    public function beneficiary_report_home(){
   
        $provincial_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.paymaya_basis')
            ->select('province', DB::raw('SUM(beg_beneficiaries) as beg_beneficiaries'),
                DB::raw('SUM(beg_total_area) as beg_total_area'), DB::raw('SUM(beg_total_bags) as beg_total_bags'))
            // ->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", $GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.province", "=", $GLOBALS['season_prefix']."rcep_paymaya.paymaya_basis.province")
                ->groupBy('province')
                // ->orderBy("region_sort")
            ->get();
     
  

        $province_arr = array();
        foreach($provincial_data as $row){

            $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province',  $row->province)
                    ->where('is_transferred', "!=",1)
                    ->where('qrStart', '>', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
            if(intval($ebinhi)>0){
                $ebinhi = $ebinhi;
            }else{ $ebinhi = 0;}

 
            $claim_total_bags = 0;
            $claimSeedsPerFarmer = 0;
            $claim_total_area = 0;

            $claim_total_bags = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_bags")->where('province', $row->province)->sum("total_bags");
            $claimSeedsPerFarmer =  DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_beneficiaries")->where('province', $row->province)->sum("total_beneficiaries");
            // $cliam_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            //     ->select("tbl_claim.paymaya_code")
            //     ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries", "tbl_beneficiaries.paymaya_code", "=", "tbl_claim.paymaya_code")
            //     ->where('tbl_claim.province', $row->province)
            //     ->groupBy("tbl_claim.paymaya_code")
            //     ->get();
        
            // foreach ($cliam_data as $key => $value) {
            //     $area_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
            //         ->select("area")
            //         ->where("paymaya_code", $value->paymaya_code)
            //         ->first();

            //     $claim_total_area += $area_data->area; 
            // }




            //$claim_total_area = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_statistics")->where('province', $row->province)->value("total_claimed");
            // $claim_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            //     ->where('province', $row->province)
            //     ->groupBy('seedVariety')
            //     ->groupBy('paymaya_code')
            //     ->get();
			
            //  $claim_total_bags = count($claim_data);

            // $claim_data_arr = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            //     ->select(DB::raw("COUNT(tbl_claim.paymaya_code) as beneficiary_count"), DB::raw("SUM(area) as area_count"))
            //     ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries", "tbl_beneficiaries.paymaya_code", "=", "tbl_claim.paymaya_code")
            //     ->where('tbl_claim.province', $row->province)
            //     ->groupBy('tbl_claim.paymaya_code')
            //     ->first();

            // $claim_total_beneficiaries = $claim_data_arr->beneficiary_count;
     
            //     $claim_total_area = $claim_data_arr->area_count;
               
        

            

            // $claimSeedsPerFarmer = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            //         ->DISTINCT('paymaya_code')
            //         ->where("province", $row->province)
            //         ->count('paymaya_code');
  
            


            array_push($province_arr, array(
                "province" => $row->province,
                "total_beneficiaries" => number_format($row->beg_beneficiaries),
                "total_area" => number_format($row->beg_total_area,2,".",","),
                "total_bags" => number_format($row->beg_total_bags),
                "claim_total_beneficiaries" => number_format($claimSeedsPerFarmer),
                "claim_total_bags" => number_format($claim_total_bags),
                // "claim_total_area" => number_format($claim_total_area,2,".",","),
                "claim_total_area" => "N/A",
                "accepted_bags" => $ebinhi,
            ));
         
        }

        //dd($province_arr);
      
        return view('paymaya.reports.beneficiary.home')
            ->with('province_arr', $province_arr);
    }


       public function beneficiary_report_provincial_tbl(Request $request){
        $municipal_data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
            ->select('province', 'municipality')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->get();

        $municipal_arr = array();
        foreach($municipal_data as $row){

             $ebinhi = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as total_bags'))
                    ->where('province',  $row->province)
                    ->where('municipality', $row->municipality)
                    ->where('is_transferred', "!=",1)
                    ->where('qrStart', '>', 0)
                    //->where('batchSeries', '=', '')
                    ->value('total_bags');
            if(intval($ebinhi)>0){
                $ebinhi = $ebinhi;
            }else{ $ebinhi = 0;}

            $municipal_tbl_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                ->select('municipality', DB::raw('COUNT(beneficiary_id) as total_beneficiaries'),
                    DB::raw('SUM(area) as total_area'), DB::raw('SUM(bags) as total_bags'))
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->first();

            $municipality = $row->municipality;
            $total_beneficiaries = number_format($municipal_tbl_beneficiaries->total_beneficiaries);
            $total_area = number_format($municipal_tbl_beneficiaries->total_area,2,".",",");
            $total_bags = number_format($municipal_tbl_beneficiaries->total_bags);


            $claim_total_beneficiaries = 0;
            $claim_total_bags = 0;
            $claim_total_area = 0;

            $claim_total_bags = DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_bags")->where('province', $row->province)->sum("total_bags");
            $claim_total_beneficiaries =  DB::table($GLOBALS['season_prefix']."rcep_paymaya.paymaya_total_beneficiaries")->where('province', $row->province)->sum("total_beneficiaries");
            

            $municipal_tbl_claim = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->get();

            $claim_total_bags = count($municipal_tbl_claim);

/* 
            $municipal_tbl_claim_arr = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->groupBy('municipality','paymaya_code')
                ->get();

            foreach($municipal_tbl_claim_arr as $claim_row){
                $claim_beneficiary_details = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                    ->where('paymaya_code', $claim_row->paymaya_code)
                    ->first();
                    $claim_total_beneficiaries += 1;
                    $claim_total_area += $claim_beneficiary_details->area;
                
            }
                 */
                $municipal_tbl_claim_arr = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                ->select('tbl_claim.paymaya_code')
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->groupBy('paymaya_code')
                ->get();
                $datatmp = json_decode(json_encode( $municipal_tbl_claim_arr),true);
                $claim_total_beneficiaries = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                ->whereIn('paymaya_code',$datatmp)
                ->where('province', $row->province)
                ->where('municipality', $row->municipality)
                ->count('paymaya_code');

            //claim_total_beneficiaries

            array_push($municipal_arr, array(
                "province" => $row->province,
                "municipality" => $municipality,
                "total_beneficiaries" => $total_beneficiaries,
                "total_area" => $total_area,
                "total_bags" => $total_bags,
               // "claim_total_beneficiaries" => number_format($claim_total_beneficiaries),
               "claim_total_beneficiaries" => number_format($claim_total_beneficiaries),
                "claim_total_bags" => number_format($claim_total_bags),
                // "claim_total_area" => number_format($claim_total_area,2,".",","),
                "claim_total_area" => "N/A",
                "accepted_bags" => $ebinhi,
                
            ));
        }

        $municipal_arr = collect($municipal_arr);
        //dd($municipal_arr);

        return Datatables::of($municipal_arr)
        ->addColumn('action', function($row){
            $link = route('paymaya.report.municipal', ['province' => $row["province"], 'municipality' => $row["municipality"]]);
            return  "<a href='$link' target='_blank' class='btn btn-success btn-sm'><i class='fa fa-download'></i> Download</a>";            
        })
        ->make(true);
    }


    
    /**
     * REPORT ROUTES - END
     */

}

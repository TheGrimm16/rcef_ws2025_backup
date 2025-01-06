<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;

use Yajra\Datatables\Datatables;
use App\rptBreakDown;

use PDFTIM;
use DB;
use Excel;
use Auth;

class rptBreakDownController extends Controller
{
	public function index(){
	   
        $province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer")
            ->groupBy('province')
            ->get();
        return view('reports.break_down.index')
        	 ->with(compact('province_list'));
	}

	public function getMunicipalities($province){
		
	  $municipalities = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer")
        ->where("province", $province)
        ->groupBy('municipality')
        ->get();
		echo json_encode($municipalities);
	}

    public function replacementInfo(Request $request) {
        $get_batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer")
                ->where("batchTicketNumber",$request->batchTicketNumber)
                ->where("seedTag", $request->seedTag)
                ->first();
        $replacement = $get_batches->replacement_ticket;
            $replacement = explode("|", $replacement);
            $batch = ""; 
            $tag = "";
            $bag = "";
            $sum = 0;
           foreach($replacement as $rep){
               if($rep != ""){
                $rep_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->where("batchTicketNumber",$rep)
                    ->where("isBuffer", 9)
                    ->first();
                if($rep_data != null) {
                    if($batch != ""){$batch .=" | ";}
                    if($tag != ""){$tag .=" | ";}
                    if($bag != ""){$bag .=" | ";}
                    
                    $batch .= $rep.' ['.$rep_data->province.', '.$rep_data->municipality .']';
                    $tag .= $rep_data->seedTag;
                    $bag .= $rep_data->totalBagCount;
                    $sum += $rep_data->totalBagCount;
                }

               }
           }

           $bag .= " (".$sum.")";

            if($sum == 0){
                return json_encode(array(
                    "batch" => "EMPTY",
                    "seedtag" => "NOT YET REPLACED",
                    "bag" => 0
                ));
            }
        
        return json_encode(array(
            "batch" => $batch,
            "seedtag" => $tag,
            "bag" => $bag
        ));

    }


	public function genTable(Request $request){
        // dd($request->all());
		if($request->municipality == 'all' ){$municipality='%';}
        else{ $municipality = $request->municipality; }
        if($request->result=='all'){$result ='%';}
        else{$result = $request->result; }

        $category_arr = array();
        $category_arr['G'] = 'For Distribution Seeds';
        $category_arr['T'] = 'For Retesting';
        $category_arr['R'] = 'Rejected Seeds';
        $category_arr['D'] = 'For Donation Seeds';
        $category_arr['P'] = 'For Replacement Seeds';



        $breakdownList = DB::connection('delivery_inspection_db')->table("tbl_breakdown_buffer")
            ->select("tbl_breakdown_buffer.*", "tbl_actual_delivery_breakdown.totalBagCount")
            ->join("tbl_actual_delivery_breakdown", function($join){
                    

                    $join->on("tbl_actual_delivery_breakdown.seedTag", "=", "tbl_breakdown_buffer.seedTag");
                    $join->on("tbl_actual_delivery_breakdown.batchTicketNumber", "=", "tbl_breakdown_buffer.batchTicketNumber");
            })  
            ->where("tbl_breakdown_buffer.province", $request->province)
            ->where("tbl_breakdown_buffer.municipality", 'LIKE', $municipality)
            ->where("tbl_breakdown_buffer.category", "LIKE", $result)
            ->get();
        $tbl_array = array();
            foreach ($breakdownList as $value) {
                array_push($tbl_array, array(
                    "batch_number" => $value->batchTicketNumber,
                    "province" => $value->province,
                    "municipality" => $value->municipality,
                    "seed_tag" => $value->seedTag,
                    "category" => $value->category,
                    "inspector" => $value->inspector_username,
                    "remarks" => $value->remarks,
                    "date_created" => $value->date_created,
                    "totalBagCount" => $value->totalBagCount
                ));
            }



		$return_arr = collect($tbl_array);
        return Datatables::of($return_arr)
        ->addColumn('result', function($row) use ($category_arr){
                    return $category_arr[$row['category']]; 
        })
        ->addColumn('action', function($row){
                if($row['category']=='R' ||$row['category']=='T' ){
                    return "<a type='button' class='btn btn-success btn-sm'  data-toggle='modal' data-target='#change_category'
                     data-batch='".$row['batch_number']."'
                     data-province='".$row['province']."'
                     data-municipality='".$row['municipality']."'
                     data-seed_tag='".$row['seed_tag']."'
                     data-category='".$row['category']."'
                     data-inspector='".$row['inspector']."'
                     data-remarks='".$row['remarks']."'
                     data-bag ='".$row['totalBagCount']."'
                     >Update Result</a>";
                }elseif($row["category"] == "P"){
                    $get_batches = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer")
                    ->where("batchTicketNumber",$row['batch_number'])
                    ->where("seedTag", $row['seed_tag'])
                    ->where("replacement_ticket","!=", "")
                    // ->orWhere("batchTicketNumber",$row['batch_number'])
                    // ->where("seedTag", $row['seed_tag'])
                    // ->where("replacement_ticket","!=", "-")
                    
                    ->get();
            
                    if($row["seed_tag"] == "18638/1")
{
  //  dd(count($get_batches));
}
                    if(count($get_batches)>0){
                        
                        return "<a type='button' class='btn btn-success btn-sm'  data-toggle='modal' data-target='#show_replacement'
                        data-batch='".$row['batch_number']."'
                        data-province='".$row['province']."'
                        data-municipality='".$row['municipality']."'
                        data-seed_tag='".$row['seed_tag']."'
                        data-category='".$row['category']."'
                        data-inspector='".$row['inspector']."'
                        data-remarks='".$row['remarks']."'
                        data-bag ='".$row['totalBagCount']."'
                        >Replacement Info</a>";
                    }


                    

                }
                else{
                    if(Auth::user()->roles->first()->name == "rcef-programmer"){

                    
                    return "<a type='button' class='btn btn-danger btn-sm'  data-toggle='modal' data-target='#change_category'
                            data-batch='".$row['batch_number']."'
                            data-province='".$row['province']."'
                            data-municipality='".$row['municipality']."'
                            data-seed_tag='".$row['seed_tag']."'
                            data-category='".$row['category']."'
                            data-inspector='".$row['inspector']."'
                            data-remarks='".$row['remarks']."'
                            data-bag ='".$row['totalBagCount']."'
                            >Update Result</a>";
                    }else{
                        return "<a type='button' class='btn btn-dark btn-sm' disabled>Update Result</a>";
                    }

                  
                }

                    
        })
            ->make(true);
	}


    public function changeResult_distribution_seeds(Request $request){
        $deduct = $request->deduct_count;
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $ls_remaining_bags = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                                ->where("batchTicketNumber",$request->ticket)
                                ->where("seedTag", $request->seedtag)
                                ->get();
            
            if(count($ls_remaining_bags)==1){
                $ls_remaining_bags = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                                ->where("batchTicketNumber",$request->ticket)
                                ->where("seedTag", $request->seedtag)
                                ->value("totalBagCount");
            }else{
                return json_encode("FAILED");
            }


   


            if($ls_remaining_bags >0){

                $remain = $ls_remaining_bags - $deduct;
                $current = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
                    ->where("batchTicketNumber",$request->ticket)
                    ->where("seedTag",$request->seedtag)
                    ->where("category", "G")
                    ->first();

                if(count($current) > 0){

                    $ls_remaining_bags = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
                    ->where("batchTicketNumber",$request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->update([
                        "totalBagCount" => $remain
                    ]);

                    $current_d = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
                    ->where("batchTicketNumber",$request->ticket)
                    ->where("seedTag",$request->seedtag)
                    ->where("category", "G")
                    ->update([
                        "category" =>$request->category_change,
                        "totalBagCount" => $deduct
                    ]);

                     $tbl_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_breakdown_buffer")
                    ->where("batchTicketNumber",$request->ticket)
                    ->where("seedTag",$request->seedtag)
                    ->where("category", "G")
                    ->update([
                        "category" =>$request->category_change,
                    ]); 
                    
                    return json_encode("SUCCESS");
                }else{
                    return json_encode("NO MATCH");  
                }

               

                





            }else{
                return json_encode("ZERO BAGS");
            }





        }else{
            return json_encode("FAILED");
        }
    }







    public function changeResult(Request $request){
  
        $ls_data = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->first();
        if(count($ls_data)>0){
            $ls_count = $ls_data->totalBagCount;
        }else{
            $ls_count = 0;
        }

        $retain_bags = $ls_count - $request->deduct_count;


        //UPDATE CURRENT SEASON
        DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
            ->where("batchTicketNumber", $request->ticket)
            ->where("seedTag", $request->seedtag)
            ->update([
                "category" => $request->category_change
            ]);
        
            DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
            ->where("batchTicketNumber", $request->ticket)
            ->where("seedTag", $request->seedtag)
            ->update([
                "totalBagCount" => $request->deduct_count,
                "category" => $request->category_change
            ]);


        //UPDATE LAST SEASON
            if($request->category_change == "G"){
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->update([
                        "is_hold" => 0
                    ]);
                
                 DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", '!=', $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->where("is_hold", 1)
                    ->update([
                        "is_hold" => 2
                    ]);    

            }elseif($request->category_change == "R"){
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where("batchTicketNumber", $request->ticket)
                ->where("seedTag", $request->seedtag)
                ->update([
                    "is_hold" => 0,
                    "totalBagCount" => $retain_bags
                ]);
            
             DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where("batchTicketNumber", '!=', $request->ticket)
                ->where("seedTag", $request->seedtag)
                ->where("is_hold", 1)
                ->update([
                    "is_hold" => 2
                ]); 
            }elseif($request->category_change == "P"){

                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->update([
                        "is_hold" => 0,
                        //"totalBagCount" => $retain_bags
                    ]);
                
                 DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", '!=', $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->where("is_hold", 1)
                    ->update([
                        "is_hold" => 2
                    ]); 
            }elseif($request->category_change == "D"){
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->update([
                        "is_hold" => 0,
                      //  "totalBagCount" => $retain_bags
                    ]);
                
                 DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where("batchTicketNumber", '!=', $request->ticket)
                    ->where("seedTag", $request->seedtag)
                    ->where("is_hold", 1)
                    ->update([
                        "is_hold" => 2
                    ]); 
            }

            return json_encode("SUCCESS");

    }

    public function getseedtag($batch_number,$code){
        if($code=="FR" || $code =="LP"){
                $seedtags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                    ->where('batchTicketNumber', $batch_number)
                    ->orderBy('seedTag', 'asc')
                    ->groupBy('seedTag')
                    ->get();
        }elseif($code=="FD"){
                $seedtags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                    ->where('batchTicketNumber', $batch_number)
                    ->where('seedType', 'I')
                    ->orderBy('seedTag', 'asc')
                    ->groupBy('seedTag')
                    ->get();
        }
          



              echo json_encode($seedtags); 
    }


    

    public function throwTemp($temp){
        //15942,1,NSIC 2016 Rc 480|15943,1,NSIC Rc 222
        $temp = explode("|", $temp);

            $return_arr = array();
            foreach ($temp as $key => $value) {
                $temp_info = explode(",", $value);
                    foreach ($temp_info as $key => $value2) {

                            $seedTag = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                    ->where('actualDeliveryId', $temp_info[0])
                                    ->value('seedTag');




                        $temp_array = array(
                            "actualId" => $temp_info[0],
                            "rejected" => $temp_info[1],
                            "variety" => $temp_info[2],
                            "seedTag" => $seedTag,
                        );

                        
                    }
                    array_push($return_arr, $temp_array);
            }


        echo json_encode($return_arr);
    }

    public function getAvailableRLA($batch_number,$seedVariety){

        $coop_accreditation = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
                ->select('coop_accreditation') 
                ->where('new_batch_number', $batch_number)
                ->first();

            if(count($coop_accreditation)>0){
                $rlas = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details as r')    
                                ->select(DB::raw('CONCAT(r.labNo,"/",r.lotNo) as st'), 'r.noOfBags')
                                ->where('r.coopAccreditation', $coop_accreditation->coop_accreditation)
                                ->where('r.seedVariety', $seedVariety)
                                ->get();
                         
                            $rlas = json_decode(json_encode($rlas), true);
                                   foreach ($rlas as $key => $rlaz) {
                                      $deliverySum = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                                            ->where('seedTag', $rlaz['st'])
                                            ->where('is_cancelled', 0)
                                            ->sum('totalBagCount');
                                            if($deliverySum==null)$deliverySum=0;
                                      $actualSum = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                            ->where('seedTag', $rlaz['st'])
                                            ->sum('totalBagCount');
                                            if($actualSum==null)$actualSum=0;

                                        $usedRla = $actualSum+$deliverySum;
                                           
                                        if($usedRla >= $rlaz['noOfBags']){
                                            unset($rlas[$key]);
                                        }else{
                                            $rla_available = $rlaz['noOfBags']  - $usedRla; 
                                            $rlas[$key]['noOfBags'] = $rla_available;
                                        }

                                    }
                            $rlas = json_decode(json_encode($rlas), true);  
                        }else{
                            $rlas = "";
                        }
                    echo json_encode($rlas);
    }



     public function replacementSave(Request $request){

         DB::beginTransaction();
    try{    
            $actualDeduct_array = array();
            $actualDesignation = array();
            $seedTagsAdded = "";
            $allActualId ="";
            $bdown = 0;
            $batch = $request->arr[0]['batchTicketNumber'];
            if($batch==""){
                echo json_encode("No Batch Number Retrieved \n Please Try Again");
                exit();
            }


            $posted = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                    ->where('batchTicketNumber', $batch)
                    ->where('is_breakdown', 0)
                    ->first();

            if(count($posted)>0){
            }else{
                 echo json_encode("Batch is already posted \n Please Refresh the page");
                exit();
            }






            //DELETE
            DB::connection('delivery_inspection_db')->table('tbl_breakdown')
                ->where('batchTicketNumber', $batch)
                ->delete();

            //INSERT DATA
            DB::connection('delivery_inspection_db')->table('tbl_breakdown')
            ->insert($request->arr);

            
            if($request->temp_data != "" || $request->temp_data_ins != ""){
                            $temp_data = $request->temp_data;
            $temp_arr = explode("|", $temp_data);

                foreach ($temp_arr as $key => $value) {

                        $arr_ins = explode(",", $value);
                        $actualId = $arr_ins[0];
                        $rejected = $arr_ins[1];
                            $checkActual = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                ->where('actualDeliveryId', $actualId)
                                ->where('is_breakdown', 0)
                                ->first();

                                if(count($checkActual)>0){
                                    if($allActualId != "")$allActualId.="|";
                                       
                                
                                        if(isset($actualDeduct_array[$actualId])){
                                        $actualDeduct_array[$actualId] =  $actualDeduct_array[$actualId] + $rejected;
                                        }else{
                                        $allActualId .= $actualId;
                                        $actualDeduct_array[$actualId] = $rejected;
                                        }
                                }else{
                                     echo json_encode("Save Failed");
                                }
                }


                //SEED TAG, REJECTED, VARIETY
                $temp_data_ins = $request->temp_data_ins;
                $temp_data_ins_arr = explode("|", $temp_data_ins);

                    $checkInfo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                            ->where('batchTicketNumber', $batch)
                            ->where('is_breakdown', 0)
                            ->first();

                        if(count($checkInfo)>0){
                            foreach ($temp_data_ins_arr as $key => $value2) {
                            $new_rla_ins_arr = explode(",", $value2);

                                $newSeedTag = $new_rla_ins_arr[0];
                                $replacementBag = $new_rla_ins_arr[1];
                                $variety = $new_rla_ins_arr[2];
                                $rmoveSeedTag = stripos($variety, "(");
                                    if($rmoveSeedTag != false){
                                        $variety = trim(substr($variety, 0,$rmoveSeedTag));
                                      }

                                if($seedTagsAdded != "")$seedTagsAdded .="|";
                                $seedTagsAdded .= $newSeedTag;

                               /* 
                              DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                        ->insert([
                                            "batchTicketNumber" => $checkInfo->batchTicketNumber,
                                            "region" => $checkInfo->region,
                                            "province" => $checkInfo->province,
                                            "municipality" => $checkInfo->municipality,
                                            "dropOffPoint" => $checkInfo->dropOffPoint,
                                            "seedVariety" => $variety,
                                            "totalBagCount" => $replacementBag,
                                            "dateCreated" => date('Y-m-d h:m:s'),
                                            "send" => 1,
                                            "seedTag" => $newSeedTag,
                                            "prv_dropoff_id" => $checkInfo->prv_dropoff_id,
                                            "prv" => $checkInfo->prv,
                                            "moa_number" => $checkInfo->moa_number,
                                            "app_version" => $checkInfo->app_version,
                                            "batchSeries" => $checkInfo->batchSeries,
                                            "remarks" => $checkInfo->remarks,
                                            "isRejected" => $checkInfo->isRejected,
                                            "is_transferred" => $checkInfo->is_transferred,
                                            "has_rla" => $checkInfo->has_rla,
                                            "transferCategory" => $checkInfo->transferCategory,
                                            "seedType" => $checkInfo->seedType,
                                            "isBuffer" => $checkInfo->isBuffer,
                                            "qrValStart" => $checkInfo->qrValStart,
                                            "qrValEnd" => $checkInfo->qrValEnd,
                                            "qrStart" => $checkInfo->qrStart,
                                            "qrEnd" => $checkInfo->qrEnd,
                                        ]);  */

                                    } //Loop


                                    //record transaction in lib_logs
                                      
                                    //DB::commit();
                //                    echo json_encode("Save Success");
                                    $bdown = 1;

                                }else{
                                      echo json_encode("Save Failed");
                                }        
                }




                //FOR DONATION
                 if($request->temp_donation != "" ){
                            $temp_data = $request->temp_donation;
                     $temp_arr = explode("|", $temp_data);

                    foreach ($temp_arr as $key => $value) {

                        $arr_ins = explode(",", $value);
                        $actualId = $arr_ins[0];
                        $rejected = $arr_ins[1];
                            $checkActual = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                ->where('actualDeliveryId', $actualId)
                                ->where('is_breakdown', 0)
                                ->first();
                                if(count($checkActual)>0){
                                    if($allActualId != "")$allActualId.="|";
                                        
                                
                                        if(isset($actualDeduct_array[$actualId])){
                                        $actualDeduct_array[$actualId] =  $actualDeduct_array[$actualId] + $rejected;
                                        }else{
                                        $allActualId .= $actualId;
                                        $actualDeduct_array[$actualId] = $rejected;
                                        }

                                         //$bdown = 1;
                                }else{
                                     echo json_encode("Save Failed");
                                }
                    }
                }


                //FOR DESIGNATION
                 if($request->temp_destination != "" ){
                    $temp_data = $request->temp_destination;
                     $temp_arr = explode("|", $temp_data);

                    foreach ($temp_arr as $key => $value) {

                        $arr_ins = explode(",", $value);
                        $actualId = $arr_ins[0];
                        $designation_info = $arr_ins[1];
                        //$designation = $ars_ins[3];
                            $checkActual = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                ->where('actualDeliveryId', $actualId)
                                ->where('is_breakdown', 0)
                                ->first();
                                if(count($checkActual)>0){
                                    if($allActualId != "")$allActualId.="|";
                                        
                                
                                       // if(isset($actualDesignation[$actualId])){
                                        //$actualDesignation[$actualId] =  $designation;
                                       // }else{
                                       // $allActualId .= $actualId;
                                        $actualDesignation[$actualId] = $designation_info;
                                       // }

                                        // $bdown = 1;
                                }else{
                                     echo json_encode("Save Failed");
                                }
                    }
                }


                if($bdown == 0){
                    //dd($bdown);
                        //IF NO REPLACEMENT
                        $transferData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                ->where('batchTicketNumber', $batch)
                                ->where('is_breakdown', 0)
                                ->get();

                                foreach ($transferData as  $transferData) {

                                        if(isset($actualDesignation[$transferData->actualDeliveryId])){
                                            $des = $actualDesignation[$transferData->actualDeliveryId];
                                        }else{
                                            $des = "0";
                                        }


                                        if(isset($actualDeduct_array[$transferData->actualDeliveryId])){
                                             $currentActualCount = $transferData->totalBagCount - $actualDeduct_array[$transferData->actualDeliveryId];
                                        }else{
                                            $currentActualCount = $transferData->totalBagCount;
                                        }











                                        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                        ->where('actualDeliveryId', $transferData->actualDeliveryId)
                                        ->update([
                                            "is_breakdown" => 1,
                                            "designation" => $des,
                                        ]);
                            
                                     //INSERT AND TRANSFER TO  ACTUAL 
                                        /*
                                      DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                        ->insert([
                                            "batchTicketNumber" => $transferData->batchTicketNumber,
                                            "region" => $transferData->region,
                                            "province" => $transferData->province,
                                            "municipality" => $transferData->municipality,
                                            "dropOffPoint" => $transferData->dropOffPoint,
                                            "seedVariety" => $transferData->seedVariety,
                                            "totalBagCount" => $currentActualCount,
                                            "dateCreated" => date('Y-m-d h:m:s'),
                                            "send" => 1,
                                            "seedTag" => $transferData->seedTag,
                                            "prv_dropoff_id" => $transferData->prv_dropoff_id,
                                            "prv" => $transferData->prv,
                                            "moa_number" => $transferData->moa_number,
                                            "app_version" => $transferData->app_version,
                                            "batchSeries" => $transferData->batchSeries,
                                            "remarks" => $transferData->remarks,
                                            "isRejected" => $transferData->isRejected,
                                            "is_transferred" => $transferData->is_transferred,
                                            "has_rla" => $transferData->has_rla,
                                            "transferCategory" => $transferData->transferCategory,
                                            "seedType" => $transferData->seedType,
                                            "isBuffer" => $transferData->isBuffer,
                                            "qrValStart" => $transferData->qrValStart,
                                            "qrValEnd" => $transferData->qrValEnd,
                                            "qrStart" => $transferData->qrStart,
                                            "qrEnd" => $transferData->qrEnd,
                                        ]);  */
                                }


























                                /*
                    //record transaction in lib_logs
                                            DB::connection('mysql')->table('lib_logs')
                                            ->insert([
                                                'category' => 'BREAKDOWN',
                                                'description' => 'BATCH NUMBER: `'.$batch,
                                                'author' => Auth::user()->username,
                                                'ip_address' => $_SERVER['REMOTE_ADDR']
                                            ]); */
                    DB::commit();
                    echo json_encode("Save Success");
                }else{
                   // dd($actualDeduct_array);

                    
                    foreach ($actualDeduct_array as $key => $dedution) {


                        $checkExis = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                            ->where('actualDeliveryId', $key)
                            ->first();
                            if(count($checkExis)>0){
                                    if(isset($actualDesignation[$checkExis->actualDeliveryId])){
                                            $des = $actualDesignation[$checkExis->actualDeliveryId];
                                        }else{
                                            $des = "0";
                                        }


                                  $currentActualCount = $checkExis->totalBagCount - $dedution;
                                        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery_for_breakdown')
                                        ->where('actualDeliveryId', $key)
                                        ->update([
                                            "is_breakdown" => 1,
                                            "designation" => $des,
                                        ]);
                            
                                //INSERT AND TRANSFER TO 
                                   /*   DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                        ->insert([
                                            "batchTicketNumber" => $checkExis->batchTicketNumber,
                                            "region" => $checkExis->region,
                                            "province" => $checkExis->province,
                                            "municipality" => $checkExis->municipality,
                                            "dropOffPoint" => $checkExis->dropOffPoint,
                                            "seedVariety" => $checkExis->seedVariety,
                                            "totalBagCount" => $currentActualCount,
                                            "dateCreated" => date('Y-m-d h:m:s'),
                                            "send" => 1,
                                            "seedTag" => $checkExis->seedTag,
                                            "prv_dropoff_id" => $checkExis->prv_dropoff_id,
                                            "prv" => $checkExis->prv,
                                            "moa_number" => $checkExis->moa_number,
                                            "app_version" => $checkExis->app_version,
                                            "batchSeries" => $checkExis->batchSeries,
                                            "remarks" => $checkExis->remarks,
                                            "isRejected" => $checkExis->isRejected,
                                            "is_transferred" => $checkExis->is_transferred,
                                            "has_rla" => $checkExis->has_rla,
                                            "transferCategory" => $checkExis->transferCategory,
                                            "seedType" => $checkExis->seedType,
                                            "isBuffer" => $checkExis->isBuffer,
                                            "qrValStart" => $checkExis->qrValStart,
                                            "qrValEnd" => $checkExis->qrValEnd,
                                            "qrStart" => $checkExis->qrStart,
                                            "qrEnd" => $checkExis->qrEnd,
                                        ]);   */
                            }
                    }



                    /*
                      DB::connection('mysql')->table('lib_logs')
                                        ->insert([
                                            'category' => 'BREAKDOWN',
                                            'description' => 'BATCH NUMBER: `'.$batch.'` Edited actualIds:'.$allActualId.', Added SeedTags:'.$seedTagsAdded,
                                            'author' => Auth::user()->username,
                                            'ip_address' => $_SERVER['REMOTE_ADDR']
                                        ]);  */
                    DB::commit();
                     echo json_encode("Save Success");
                }















        }catch(\Illuminate\Database\QueryException $ex){
                                //if atleast 1 query fails, all will not execute and the database will be rolled back - fullproof
            DB::rollback(); 
            return 'insert_error';
               
        }

    }






}

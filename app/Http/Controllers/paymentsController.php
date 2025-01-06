<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;
use Excel;
use DB;
use App\SeedCooperatives;
use App\SeedProducers;
use App\Transplant;
use App\Seeds;
use App\SeedGrowers;
use App\RegistryFarmerRole;
use Illuminate\Support\Str;


use Auth;

class paymentsController extends Controller {


    public function index() {
        return view('payment_DV_preparation.home');
    }

    public function getIARdetails(Request $request){
        $IARdata=[];
        $batch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
        ->select('batchTicketNumber')
        ->where('iarCode', $request->content)
        ->first();

        if($batch){
            $deliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as expectedBags'), 'coopAccreditation','region', 'province', 'municipality', 'dropOffPoint','deliveryDate')
            ->where('batchTicketNumber', $batch->batchTicketNumber)
            ->groupBy('batchTicketNumber')
            ->first();
        
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $deliveryData->coopAccreditation)->value('coopName');

            $delivery_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                    ->select('status')
                    ->where('batchTicketNumber', $batch->batchTicketNumber)
                    ->orderBy('deliveryStatusId', "desc")
                    ->first();
                if($delivery_status->status == 0){
                    $status_name = 'Pending';
                }
                else if($delivery_status->status == 1){
                    $status_name = 'Passed';
                }
                else if($delivery_status->status == 2){
                    $status_name = 'Rejected';
                }else if($delivery_status->status == 3){
                    $status_name = 'In Transit';
                }else if($delivery_status ->status== 4){
                    $status_name = 'Cancelled';
                }

            $actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select('dateCreated', 'qrStart','qrEnd', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actualBags'))
                ->where('batchTicketNumber', $batch->batchTicketNumber)
                ->where('region', $deliveryData->region)
            ->groupBy('batchTicketNumber')
            ->first();

            $paymentStatus = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->select('paymentStatus')
                ->where('batchTicketNumber', $batch->batchTicketNumber)
                ->first();

            $dr_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection')
            ->select('dr_number')
            ->where('batchTicketNumber', $batch->batchTicketNumber)
            ->first();

            $IAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                    ->select('iarCode')
                    ->where('batchTicketNumber', $batch->batchTicketNumber)
                    ->first();

            if(isset($dr_number))
            {
                $hasDR = 'Yes';
            }
            else{
                $hasDR = 'No';
            }

            $deliveryTypes = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->select('qrStart','qrEnd')
                    ->where('batchTicketNumber', $batch->batchTicketNumber)
                    ->first();

                    if(($deliveryTypes->qrStart ==='0' && $deliveryTypes->qrEnd === '0') || ($deliveryTypes->qrStart ===0 && $deliveryTypes->qrEnd === 0))
                    {
                        $deliveryType = 'Regular';
                    }
                    else if(($deliveryTypes->qrStart !=='0' && $deliveryTypes->qrEnd !== '0') || $deliveryTypes->qrStart !==0 && $deliveryTypes->qrEnd !== 0){
                        $deliveryType = 'Binhi e-Padala';
                    }

            array_push($IARdata,array(
                "coopName" => $coop_name,
                "province" => $deliveryData->province,
                "municipality" => $deliveryData->municipality,
                "batchTicketNumber" => $batch->batchTicketNumber,
                "dropOffPoint" => $deliveryData->dropOffPoint,
                "expectedBags" => number_format($deliveryData->expectedBags)." bag(s)",
                "acceptedBags" => number_format($actual_delivery->actualBags)." bag(s)",
                "deliveryStatus" => $status_name,
                "deliveryDate" => date("m-d-Y", strtotime($deliveryData->deliveryDate)),
                "paymentStatus" => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'For Receiving',
                "hasDR" => $hasDR,
                "iarNo" => $IAR->iarCode,
                'deliveryType' => $deliveryType,
                )); 
            return $IARdata;

        }
        
        
    }

    public function getParticulars(Request $request){
        $IARdata = [];
        $getParticularBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->select('particularsBatch')
        ->where('iar_number', $request->content)
        ->get();

        $getIARgroup = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->where('particularsBatch', $getParticularBatch[0]->particularsBatch)
        ->get();

        // dd($getIARgroup);
        foreach($getIARgroup as $row){
            array_push($IARdata,array(
                "dropOffPoint" => $row->dropOffPoint,
                "province" => $row->province,
                "municipality" => $row->municipality,
                "acceptedBags" => number_format($row->delivery_volume)." bag(s)",
                "iarNo" => $row->iar_number,
                )); 
        }

        // dd($IARdata);
        return $IARdata;


    }

    public function checkIAR(Request $request){
        $content = $request->content;

        $checkStatus = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('status')
        ->where('iar_no', $request->content)
        ->first();

        // dd($checkStatus);

        if($checkStatus){
            if($checkStatus->status == 'received'||$checkStatus->status == 'to_prp'){
            $checkIAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->select('iar_number')
            ->where('iar_number', $content)
            ->get();

                if($checkIAR){
                    return 1;
                }
                else{
                    return 0;
                }
            }
            else{
                return $checkStatus->status;
            }
        }
        
        return 9;
        
        
    }

    public function particularsPreview(Request $request){
        // dd($request->iarArray[0]['batchTicketNumber']);
        $batchTickets = [];
        $IARdetails = $request->iarArray;
        foreach($IARdetails as $iarDetail){
            array_push($batchTickets, $iarDetail["batchTicketNumber"]);
        }
        // dd($batchTickets);
        $length = 10;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $particularsBatch = str_shuffle(Str::random($length, $characters));
        // dd($request->checkedVals);
        $deliveryArray = [];
        $dateArray = [];
        $iarArray = [];
        $DRdateArray = [];
        $sumTotalBags = 0;
        $deliveryType = '';
        foreach($batchTickets as $batch){
            $tbl_delivery_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'coopAccreditation','region', 'province', 'municipality', 'dropOffPoint','deliveryDate')
            ->where('batchTicketNumber', $batch)
            ->groupBy('batchTicketNumber')
            ->first();

            // dd($tbl_delivery_data);
            if ($tbl_delivery_data) {
                array_push($deliveryArray, $tbl_delivery_data);
                $sumTotalBags += $tbl_delivery_data->total_bags;
                array_push($dateArray, date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate)));
                // array_push($dateArray, $tbl_delivery_data->deliveryDate);

                $IAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->select('iarCode')
                ->where('batchTicketNumber', $batch)
                ->first();
                array_push($iarArray, $IAR);

                $dr_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection')
                ->select('dr_number')
                ->where('batchTicketNumber', $batch)
                ->first();

                $paymentStatus = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->select('paymentStatus')
                ->where('batchTicketNumber', $batch)
                ->first();

                $deliveryTypes = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select('qrStart','qrEnd')
                ->where('batchTicketNumber', $batch)
                ->first();

                // $concatenatedDRdate = $dr_number->dr_number . ' ' . date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate));
                // array_push($DRdateArray, $concatenatedDRdate);


                if(($deliveryTypes->qrStart ==='0' && $deliveryTypes->qrEnd === '0') || ($deliveryTypes->qrStart ===0 && $deliveryTypes->qrEnd === 0))
                {
                    $deliveryType = 'Regular';
                }
                else if(($deliveryTypes->qrStart !=='0' && $deliveryTypes->qrEnd !== '0') || ($deliveryTypes->qrStart !==0 && $deliveryTypes->qrEnd !== 0)){
                    $deliveryType = 'Binhi e-Padala';
                }
                    
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->insert([
                'coopAccreditation' => $tbl_delivery_data->coopAccreditation,
                'region' => $tbl_delivery_data->region,
                'province' => $tbl_delivery_data->province,
                'municipality' => $tbl_delivery_data->municipality,
                'batchTicketNumber' => $batch,
                'dropOffPoint' => $tbl_delivery_data->dropOffPoint,
                'delivery_volume' => $tbl_delivery_data->total_bags,
                'delivery_date' => date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate)),
                'iar_number' => $IAR->iarCode,
                'paymentStatus' => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'For Receiving',
                'deliveryReceipt' => $dr_number->dr_number,
                'deliveryType' => $deliveryType,
                'particularsBatch' => $particularsBatch,
                ]);
            }
        }
        // dd($DRdateArray);
        //get unique IARs
        $jsonIAR = json_encode($iarArray);
        $IARs = json_decode($jsonIAR);
        $uniqueIAR = [];

        foreach ($IARs as $item) {
            $value = $item->iarCode;

            if (!in_array($value, $uniqueIAR)) {
                $uniqueIAR[] = $value;
            }
        }
        $outputIAR = implode(', ', $uniqueIAR);

        //get unique dates
        $jsonDates = json_encode($dateArray);
        $dates = json_decode($jsonDates);
        $uniqueDates = [];

        foreach ($dates as $item) {
            $value = $item;

            if (!in_array($value, $uniqueDates)) {
                $uniqueDates[] = $value;
            }
        }

        // Convert date strings to timestamps
        $dateTimestamps = array_map(function($date) {
            return strtotime($date);
        }, $uniqueDates);

        // Sort the timestamps in ascending order
        sort($dateTimestamps);

        // Create a sorted array of dates based on the sorted timestamps
        $sortedDates = array_map(function($timestamp) {
            return date("m/d/Y", $timestamp);
        }, $dateTimestamps);

        // dd($sortedDates);

        $DRs = [];
        foreach($sortedDates as $date){
            $dateDR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->select('deliveryReceipt', 'delivery_date')
            ->where('delivery_date', '=', $date)
            ->get();

            // dd($dateDR[0]->deliveryReceipt);
            foreach($dateDR as $dr){
                array_push($DRs,$dr->deliveryReceipt);
            }
            $outputDR = implode(', ', $DRs);
            $concatDR = $outputDR.' '.$date;
            array_push($DRdateArray, $concatDR);
        }

        $outputDates = implode(', ', $DRdateArray);

        // dd($outputDates);
        $cost = $sumTotalBags * 760;
        $particulars = "Payment for ".number_format($sumTotalBags)." bags of CS for 2024DS to ".$tbl_delivery_data->province." as per DR# ".$outputDates." less 1% ret fee | Attached IAR #: ".$outputIAR;

        foreach($batchTickets as $batch){
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('batchTicketNumber','=',$batch)
                ->update([
                'particulars' => $particulars,
                ]);
        }

        $response = [
            'particulars' => $particulars,
            'particularsBatch' => $particularsBatch,
        ];

        return ($response);
    }

    public function getGeneratedParticulars(Request $request){
        $viewParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->select('particulars')
            ->where('iar_number', $request->generatedCode)
            ->first();

            return json_encode($viewParticulars->particulars);
    }



    public function addDVnumber(Request $request){
        $checkDV = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->where('dv_control_number','=',$request->dv)
        ->get();

        if(!$checkDV){
            $updateDV = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('particularsBatch','=',$request->particularsBatch)
                ->update([
                'dv_control_number' => $request->dv,
                ]);
            $getIAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->select('iar_number')
            ->where('particularsBatch','LIKE',$request->particularsBatch)
            ->get();
            if($getIAR){
                foreach($getIAR as $IAR){
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
                    ->where('iar_no','=',$IAR->iar_number)
                    ->update([
                    'status' => 'to_prp',
                    ]);
                }
            }
            
                

        return (int)$updateDV;
        }
        else{
            return (int)9;
        }
        
    }

    public function addDVnumber2(Request $request){
        // dd($request->dv2,$request->generatedCode);
        $checkDV = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->where('dv_control_number','=',$request->dv2)
        ->get();

        $particularsBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->select('particularsBatch')
        ->where('iar_number','=',$request->generatedCode)
        ->first();


        if(!$checkDV){
            $updateDV = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('particularsBatch','=',$particularsBatch->particularsBatch)
                ->update([
                'dv_control_number' => $request->dv2,
                ]);

            // dd($updateDV);
            $getIAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->select('iar_number')
            ->where('particularsBatch','LIKE',$request->particularsBatch)
            ->get();

            // dd($request->particularsBatch,$getIAR);
            if($getIAR){
                foreach($getIAR as $IAR){
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
                    ->where('iar_no','=',$IAR->iar_number)
                    ->update([
                    'status' => 'to_prp',
                    ]);
                }
            }

        return (int)$updateDV;
        }
        else{
            return (int)9;
        }
        
    }

    public function hasDVnumber(Request $request){
        $particularsBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->select('particularsBatch')
        ->where('iar_number','=',$request->generatedCode)
        ->first();

        // dd($particularsBatch);

        if($particularsBatch){
            $hasDV = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('particularsBatch','=',$particularsBatch->particularsBatch)
                ->whereRaw('LENGTH(dv_control_number) > 0')
                ->get();
                // dd($hasDV);
                if($hasDV){
                    return $hasDV;
                }
                else if(!$hasDV){
                    return (int)9;
                }
        }
        else{
            return (int)9;
        }
        
    }

    public function getTranspoCost(Request $request){
        // dd($request->generatedCode);
        $transpoArray = [];

        $particularsBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->select('particularsBatch')
        ->where('iar_number','=',$request->generatedCode)
        ->first();

        $getBatchNo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->select('batchTicketNumber')
        ->where('particularsBatch','=',$particularsBatch->particularsBatch)
        ->get();

        foreach($getBatchNo as $batch){
            $deliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as expectedBags'), 'coopAccreditation','region', 'province', 'municipality', 'dropOffPoint','deliveryDate')
            ->where('batchTicketNumber', $batch->batchTicketNumber)
            ->groupBy('batchTicketNumber')
            ->first();

            $actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select('dateCreated', 'qrStart','qrEnd', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actualBags'))
            ->where('batchTicketNumber', $batch->batchTicketNumber)
            ->where('region', $deliveryData->region)
            ->groupBy('batchTicketNumber')
            ->first();

            $getTranspo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select('batchTicketNumber','transpo_cost_per_bag')
            ->where('batchTicketNumber', '=', $batch->batchTicketNumber)
            ->groupBy('batchTicketNumber')
            ->first();

            $getIAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->select('iar_number')
            ->where('batchTicketNumber', '=', $batch->batchTicketNumber)
            ->first();

            if($getTranspo){
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('batchTicketNumber','=',$batch->batchTicketNumber)
                ->update([
                'transpo_cost_per_bag' => $getTranspo->transpo_cost_per_bag,
                ]);
            }


            array_push($transpoArray, array(
                "iar_number" => $getIAR->iar_number,
                "batchTicketNumber" => $getTranspo->batchTicketNumber,
                "transpo_cost_per_bag" => number_format($getTranspo->transpo_cost_per_bag),
                "dropOffPoint" => $deliveryData->dropOffPoint,
                "municipality" => $deliveryData->municipality,
                "province" => $deliveryData->province,
                "acceptedBags" => $actual_delivery->actualBags
            ));
        }

        return $transpoArray;
    }

    public function saveTranspoCost(Request $request){
        // dd($request->tempdata);
        $allData = $request->tempdata;
        $totalTranspo = 0;
        foreach($allData as $data){
            // dd($data);
            $particularsTranspoCost = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('iar_number','=',$data["iar_number"])
                ->where('batchTicketNumber','=',$data["batchTicketNumber"])
                ->update([
                'transpo_cost_per_bag' => $data["transpo_cost_per_bag"],
                ]);
            
            $deliveryTranspoCost = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('batchTicketNumber','=',$data["batchTicketNumber"])
            ->update([
            'transpo_cost_per_bag' => $data["transpo_cost_per_bag"],
            ]);

            $totalTranspo += $data["transpo_cost_per_bag"];
        }

        $checkParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
        ->where('iar_number','=',$allData[0]["iar_number"])
        ->where('particulars','LIKE','%with transpo cost%')
        ->first();

        if(!$checkParticulars){
            $getParticularBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->where('iar_number','=',$allData[0]["iar_number"])
            ->first();
            
            $particulars = $getParticularBatch->particulars;

            $parts = explode("as per DR#", $particulars);

            $transpoCost = 'with transpo cost of P'.$totalTranspo.' ';
            
            $newParticulars = $parts[0].$transpoCost.'as per DR#'.$parts[1];

            if($totalTranspo != 0){
                $updateParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                    ->where('particularsBatch','LIKE',$getParticularBatch->particularsBatch)
                    ->update([
                    'particulars' => $newParticulars,
                    ]);
            }
            else if($totalTranspo == 0){
                $updateParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('particularsBatch','LIKE',$getParticularBatch->particularsBatch)
                ->update([
                'particulars' => $particulars,
                ]);
                $newParticulars = $particulars;
            }
        }
        else{
            $getParticularBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
            ->where('iar_number','=',$allData[0]["iar_number"])
            ->first();
            
            $particulars = $getParticularBatch->particulars;

            // dd($particulars);

            $parts = explode("with transpo cost of P", $particulars);

            $transpoCost = 'with transpo cost of P'.$totalTranspo.' ';

            $parts2 = explode("as per DR#", $parts[1]);

            $newParticulars = $parts[0].$transpoCost.'as per DR#'.$parts2[1];

            if($totalTranspo != 0){
                $updateParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                    ->where('particularsBatch','LIKE',$getParticularBatch->particularsBatch)
                    ->update([
                    'particulars' => $newParticulars,
                    ]);
            }
            else if($totalTranspo == 0){
                $parts3 = explode("with transpo cost of P", $newParticulars);
                $parts4 = explode("as per DR#", $parts3[1]);
                $newParticulars2 = $parts3[0].'as per DR#'.$parts4[1];
                $updateParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('particularsBatch','LIKE',$getParticularBatch->particularsBatch)
                ->update([
                'particulars' => $newParticulars2,
                ]);
                $newParticulars = $newParticulars2;
            }

        }

        // dd($newParticulars);

        $response = [
            'particulars' => $newParticulars,
            'response' => (int)$particularsTranspoCost,
        ];

        return ($response);

   
    }

    public function getAPIdata($dvCtrlNo,$token)
    {
        $year = substr($dvCtrlNo,0,4);
        $dvData = [
            'year' => $year,
            'DVControlNo' => $dvCtrlNo, 
        ];
        $getAPIdataURL = 'https://isd.philrice.gov.ph/api_center/api/fmis-rcef/dv/status?' . http_build_query($dvData);
        
        $tokenAPI = [
            'http' => [
                'header' => "Authorization: Bearer $token\r\n",
                'method' => 'GET',
            ],
        ];
        
        $apiContext = stream_context_create($tokenAPI);
        $apiResponse = file_get_contents($getAPIdataURL, false, $apiContext);
        
        $response = json_decode($apiResponse);
        $data = $response->dv;

        if($response){
            $validate = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_fmis_data')
            ->where('DVControlNo', $data->DVControlNo)
            ->where('DVNo', $data->DVNo)
            ->where('Amount', $data->Amount)
            ->where('DVStatus', $data->DVStatus)
            ->where('DVStatusMessage', $data->DVStatusMessage)
            ->get();

            if(!$validate){
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_fmis_data')
                    ->insert([
                    'DVControlNo' => $data->DVControlNo,
                    'DVNo' => $data->DVNo,
                    'Particulars' => $data->Particulars,
                    'Amount' => $data->Amount,
                    'DVStatus' => $data->DVStatus,
                    'DVStatusMessage' => $data->DVStatusMessage,
                    ]);
            return json_encode($response);
            }
        }
        else{
            return $response;
        }
    }

    public function viewAPIdata($dvCtrlNo,$token)
    {
        $year = substr($dvCtrlNo,0,4);
        $dvData = [
            'year' => $year,
            'DVControlNo' => $dvCtrlNo, 
        ];
        $getAPIdataURL = 'https://isd.philrice.gov.ph/api_center/api/fmis-rcef/dv/status?' . http_build_query($dvData);
        
        $tokenAPI = [
            'http' => [
                'header' => "Authorization: Bearer $token\r\n",
                'method' => 'GET',
            ],
        ];
        
        $apiContext = stream_context_create($tokenAPI);
        $apiResponse = file_get_contents($getAPIdataURL, false, $apiContext);
        
        $response = json_decode($apiResponse);
        $data = $response->dv;
  
        return json_encode($data);
    }

    public function getToken()
    {
        $url = 'https://isd.philrice.gov.ph/api_center/api/login';
        $data = [
            'username' => 'rcef_dev',
            'password' => 'RCEF_P@ssw0rd2023',
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            die('Failed to access the API.');
        }

        $responseData = json_decode($response);
        
        
        $token = $responseData->token;

        return $token;
    }
    
}

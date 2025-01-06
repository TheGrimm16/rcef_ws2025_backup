<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;
use Excel;
use DB;
use Mail;
use App\SeedCooperatives;
use App\SeedProducers;
use App\Transplant;
use App\Seeds;
use App\SeedGrowers;
use App\RegistryFarmerRole;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;


use Auth;

class paymentsDashboardController extends Controller {


    public function index() {
        
        //Idle Transactions
        $currentDateTime = Carbon::now();
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
        // dd($formattedDateTime);

        $getAllData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->get();

        $idleTransactions = [];
        foreach ($getAllData as $record) {
            $dataSynced = Carbon::parse($record->data_synced);
            if ($currentDateTime->isWeekend()) {
                continue;
            }
    
            $differenceInDays = $dataSynced->diffInDaysFiltered(function(Carbon $date) {
                return !$date->isWeekend();
            }, $currentDateTime);
        
            if ($differenceInDays >= 3) {
                $getLocation = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->where('iar_number', 'LIKE', $record->iar_no)
                ->first();

                $getVolume = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->select(DB::raw('SUM(delivery_volume) as totalVolume'))
                ->where('iar_number', 'LIKE', $record->iar_no)
                ->first();

                switch ($record->status) {
                    case "to_rcv":
                        $record->status = 'For Transmittal';
                        break;
                    case "returned":
                        $record->status = 'For Transmittal';
                        break;
                    case "received":
                        $record->status = 'Received';
                        break;
                    case "to_prp":
                        $record->status = 'For Preparation';
                        break;
                    case "to_prc":
                        $record->status = 'For Processing';
                        break;
                    case "to_pay":
                        $record->status = 'For Processing';
                        break;
                    case "accomplished":
                        $record->status = 'Paid';
                        break;
                    case "on_hold":
                        switch ($record->status_before_hold) {
                            case "to_rcv":
                                $record->status = 'For Transmittal';
                                break;
                            case "returned":
                                $record->status = 'For Transmittal';
                                break;
                            case "received":
                                $record->status = 'Received';
                                break;
                            case "to_prp":
                                $record->status = 'For Preparation';
                                break;
                            case "to_prc":
                                $record->status = 'For Processing';
                                break;
                            case "to_pay":
                                $record->status = 'For Processing';
                                break;
                            case "accomplished":
                                $record->status = 'Paid';
                                break;
                        }
                        break;
                }

                array_push($idleTransactions,[
                    'iar_no' => $record->iar_no,
                    'dropOffPoint' => $getLocation->dropOffPoint,
                    'volume' => $getVolume->totalVolume,
                    'status' => $record->status
                ]);
            }
        }

        // dd($idleTransactions);

        //Total Disbursed
        $totalDisbursedAgusan = 0;  
        $totalDisbursedBatac = 0;
        $totalDisbursedBicol = 0;
        $totalDisbursedCES = 0;
        $totalDisbursedIsabela = 0;
        $totalDisbursedMidsayap = 0;
        $totalDisbursedLosBanos = 0;
        $totalDisbursedNegros = 0;
        
        $getAccomplished = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('iar_no')
        ->where('status', '=', 'accomplished')
        ->get();

        $allDVctrlNo = array();
        $totalDisbursed = 0;

        if($getAccomplished)
        {
            foreach($getAccomplished as $paidIAR)
            {
                $getDVctrlNo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->select('dv_control_number')
                ->where('iar_number', 'LIKE', $paidIAR->iar_no)
                ->get();
                array_push($allDVctrlNo,$getDVctrlNo[0]->dv_control_number);
                 
            }

            $uniqueDVctrlNo = array_unique($allDVctrlNo);
            
            foreach($uniqueDVctrlNo as $DVctrlNo)
            {
                $getAmount = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_fmis_data')
                ->where('DVControlNo','LIKE',$DVctrlNo)
                ->groupBy('DVControlNo')
                ->get();
                $totalDisbursed+=$getAmount[0]->Amount;

                foreach($getAmount as $amount)
                {
                    $getIARno = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                    ->where('dv_control_number','LIKE',$DVctrlNo)
                    ->first();

                    if(!$getIARno)
                    {
                        continue;
                    }

                    $confirmAccomplishedIAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
                    ->where('iar_no','LIKE',$getIARno->iar_number)
                    ->where('status', '=', 'accomplished')
                    ->first();

                    if(!$confirmAccomplishedIAR)
                    {
                        continue;
                    }

                    $getIARbatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                    ->where('iarCode',$confirmAccomplishedIAR->iar_no)
                    ->first();

                    if(!$getIARbatch)
                    {
                        continue;
                    }

                    $getIARregion = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('batchTicketNumber',$getIARbatch->batchTicketNumber)
                    ->first();

                    if(!$getIARregion)
                    {
                        continue;
                    }

                    $getIARstation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select('station')
                    ->where('region',$getIARregion->region)
                    ->first();

                    if(!$getIARstation)
                    {
                        continue;
                    }

                    switch ($getIARstation->station) {
                        case "Agusan":
                            $totalDisbursedAgusan+=$amount->Amount;
                            break;
                        case "Batac":
                            $totalDisbursedBatac+=$amount->Amount;
                            break;
                        case "Bicol":
                            $totalDisbursedBicol+=$amount->Amount;
                            break;
                        case "Central Experiment Station":
                            $totalDisbursedCES+=$amount->Amount;
                            break;
                        case "Isabela":
                            $totalDisbursedIsabela+=$amount->Amount;
                            break;
                        case "Los Banos":
                            $totalDisbursedMidsayap+=$amount->Amount;
                            break;
                        case "Midsayap":
                            $totalDisbursedLosBanos+=$amount->Amount;
                            break;
                        case "Negros":
                            $totalDisbursedNegros+=$amount->Amount;
                            break;
                    }
                }
            }
            $totalDisbursed = number_format($totalDisbursed,2);
            $totalDisbursedAgusan = number_format($totalDisbursedAgusan,2);
            $totalDisbursedBatac = number_format($totalDisbursedBatac,2);
            $totalDisbursedBicol = number_format($totalDisbursedBicol,2);
            $totalDisbursedCES = number_format($totalDisbursedCES,2);
            $totalDisbursedIsabela = number_format($totalDisbursedIsabela,2);
            $totalDisbursedMidsayap = number_format($totalDisbursedMidsayap,2);
            $totalDisbursedLosBanos = number_format($totalDisbursedLosBanos,2);
            $totalDisbursedNegros = number_format($totalDisbursedNegros,2);
        }


        //Total Disbursed per Station
        

        //Overall Status
        $forTransmit = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->where('status', '=', 'to_rcv')
        ->orWhere('status', '=', 'returned')
        ->get());
        $receivedCES = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->where('status', '=', 'received')
        ->get());
        $forPreparation = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->where('status', '=', 'to_prp')
        ->get());
        $forProcessing = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->where('status', '=', 'to_prc')
        ->orWhere('status', '=', 'to_pay')
        ->get());
        $paidDeliveries = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->where('status', '=', 'accomplished')
        ->get());
        $onHold = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('status_before_hold')
        ->where('status', '=', 'on_hold')
        ->get();

        foreach ($onHold as $hold){
            switch ($hold->status_before_hold) {
                case "to_rcv":
                    $forTransmit++;
                    break;
                case "returned":
                    $forTransmit++;
                    break;
                case "received":
                    $receivedCES++;
                    break;
                case "to_prp":
                    $forPreparation++;
                    break;
                case "to_prc":
                    $forProcessing++;
                    break;
                case "to_pay":
                    $forProcessing++;
                    break;
                case "accomplished":
                    $paidDeliveries++;
                    break;
            }
        }

        $actualDeliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->get();
        

        $deliveriesAgusan = 0;
        $deliveriesBatac = 0;
        $deliveriesBicol = 0;
        $deliveriesCES = 0;
        $deliveriesIsabela = 0;
        $deliveriesMidsayap = 0;
        $deliveriesLosBanos = 0;
        $deliveriesNegros = 0;

        //Total Seed Deliveries
        foreach($actualDeliveries as $delivery){
           
            $station = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
            ->select('station')
            ->where('region',$delivery->region)
            ->first();
            
       
            switch ($station->station) {
                case "Agusan":
                    $deliveriesAgusan++;
                    break;
                case "Batac":
                    $deliveriesBatac++;
                    break;
                case "Bicol":
                    $deliveriesBicol++;
                    break;
                case "Central Experiment Station":
                    $deliveriesCES++;
                    break;
                case "Isabela":
                    $deliveriesIsabela++;
                    break;
                case "Los Banos":
                    $deliveriesLosBanos++;
                    break;
                case "Midsayap":
                    $deliveriesMidsayap++;
                    break;
                case "Negros":
                    $deliveriesNegros++;
                    break;
            }
            
            
        }

        $deliveriesAgusan = number_format($deliveriesAgusan);
        $deliveriesBatac = number_format($deliveriesBatac);
        $deliveriesBicol = number_format($deliveriesBicol);
        $deliveriesCES = number_format($deliveriesCES);
        $deliveriesIsabela = number_format($deliveriesIsabela);
        $deliveriesMidsayap = number_format($deliveriesMidsayap);
        $deliveriesLosBanos = number_format($deliveriesLosBanos);
        $deliveriesNegros = number_format($deliveriesNegros);

        //Total Seeds Delivered
        $totalDelivered = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->groupBy('batchTicketNumber')
        ->get());

        $getLatestMonth = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_sum')
        ->select('targetMonthTo')
        ->orderBy('targetMonthTo','DESC')
        ->first();
        if($getLatestMonth){
            $latestMonth = $getLatestMonth->targetMonthTo;
        }
        else{
            $latestMonth = '';
        }

        $totalTarget= DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_sum')
        ->selectRaw('SUM(targetVolume) as target')
        ->where('targetMonthTo', 'LIKE', $latestMonth)
        ->first();
        // $totalTarget=count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        // ->groupBy('batchTicketNumber')
        // ->get());

        //Total Paid Deliveries
        $totalPaidDeliveries = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->where('status', '=', 'accomplished')
        ->groupBy('iar_no')
        ->get());
        
        if($totalTarget->target){
            $totalDeliveredPercentage = number_format(($totalDelivered/$totalTarget->target)*100,2);
        }
        else{
            $totalDeliveredPercentage = 0;
        }

        if($totalDelivered){
            $totalPaidPercentage = number_format(($totalPaidDeliveries/$totalDelivered)*100,2);
        }
        else{
            $totalPaidPercentage = 0;
        }
        
        $totalDelivered = number_format($totalDelivered);
        $totalPaidDeliveries = number_format($totalPaidDeliveries);
        
        //Total Paid Deliveries Per Station
        $paidAgusan = 0;
        $paidBatac = 0;
        $paidBicol = 0;
        $paidCES = 0;
        $paidIsabela = 0;
        $paidMidsayap = 0;
        $paidLosBanos = 0;
        $paidNegros = 0;

        $overallPaid = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('iar_no')
        ->where('status', '=', 'accomplished')
        ->groupBy('iar_no')
        ->get();

        foreach($overallPaid as $paidIAR)
        {
            $getPaidBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('iarCode',$paidIAR->iar_no)
            ->first();

            if(!$getPaidBatch)
            {
                continue;
            }

            $paidDeliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('batchTicketNumber',$getPaidBatch->batchTicketNumber)
            ->first();

            if(!$paidDeliveryData)
            {
                continue;
            }
            
            $getPaidStation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
            ->select('station')
            ->where('region',$paidDeliveryData->region)
            ->first();

            if(!$getPaidStation)
            {
                continue;
            }

            switch ($getPaidStation->station) {
                case "Agusan":
                    $paidAgusan++;
                    break;
                case "Batac":
                    $paidBatac++;
                    break;
                case "Bicol":
                    $paidBicol++;
                    break;
                case "Central Experiment Station":
                    $paidCES++;
                    break;
                case "Isabela":
                    $paidIsabela++;
                    break;
                case "Los Banos":
                    $paidMidsayap++;
                    break;
                case "Midsayap":
                    $paidLosBanos++;
                    break;
                case "Negros":
                    $paidNegros++;
                    break;
            }

        }

        //Percentages of Paid Deliveries
        $paidPercentageAgusan = $deliveriesAgusan != 0 ? number_format(($paidAgusan / $deliveriesAgusan) * 100, 2) : 0;
        $paidPercentageBatac = $deliveriesBatac != 0 ? number_format(($paidBatac / $deliveriesBatac) * 100, 2) : 0;
        $paidPercentageBicol = $deliveriesBicol != 0 ? number_format(($paidBicol / $deliveriesBicol) * 100, 2) : 0;
        $paidPercentageCES = $deliveriesCES != 0 ? number_format(($paidCES / $deliveriesCES) * 100, 2) : 0;
        $paidPercentageIsabela = $deliveriesIsabela != 0 ? number_format(($paidIsabela / $deliveriesIsabela) * 100, 2) : 0;
        $paidPercentageMidsayap = $deliveriesMidsayap != 0 ? number_format(($paidMidsayap / $deliveriesMidsayap) * 100, 2) : 0;
        $paidPercentageLosBanos = $deliveriesLosBanos != 0 ? number_format(($paidLosBanos / $deliveriesLosBanos) * 100, 2) : 0;
        $paidPercentageNegros = $deliveriesNegros != 0 ? number_format(($paidNegros / $deliveriesNegros) * 100, 2) : 0;


        //For Transmittal Per Station
        $forTransmitAgusan = 0;
        $forTransmitBatac = 0;
        $forTransmitBicol = 0;
        $forTransmitCES = 0;
        $forTransmitIsabela = 0;
        $forTransmitMidsayap = 0;
        $forTransmitLosBanos = 0;
        $forTransmitNegros = 0;

        $overallForTransmit = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('iar_no')
        ->where('status', '=', 'to_rcv')
        ->orWhere('status', '=', 'returned')
        ->get();

        foreach($overallForTransmit as $transmitIAR)
        {
            $getBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('iarCode',$transmitIAR->iar_no)
            ->first();

             if(!$getBatch){
                continue;
             }

            $deliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('batchTicketNumber',$getBatch->batchTicketNumber)
            ->first();

            if(!$deliveryData){
                continue;
             }
            
            $getStation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
            ->select('station')
            ->where('region',$deliveryData->region)
            ->first();

            if(!$getStation){
                continue;
             }

            switch ($getStation->station) {
                case "Agusan":
                    $forTransmitAgusan++;
                    break;
                case "Batac":
                    $forTransmitBatac++;
                    break;
                case "Bicol":
                    $forTransmitBicol++;
                    break;
                case "Central Experiment Station":
                    $forTransmitCES++;
                    break;
                case "Isabela":
                    $forTransmitIsabela++;
                    break;
                case "Los Banos":
                    $forTransmitMidsayap++;
                    break;
                case "Midsayap":
                    $forTransmitLosBanos++;
                    break;
                case "Negros":
                    $forTransmitNegros++;
                    break;
            }
        }


        return view('payments.home', 
        compact(
            'forTransmit',
            'receivedCES',
            'forPreparation',
            'forProcessing',
            'paidDeliveries',
            'deliveriesAgusan',
            'deliveriesBatac',
            'deliveriesBicol',
            'deliveriesCES',
            'deliveriesIsabela',
            'deliveriesMidsayap',
            'deliveriesLosBanos',
            'deliveriesNegros',
            'forTransmitAgusan',
            'forTransmitBatac',
            'forTransmitBicol',
            'forTransmitCES',
            'forTransmitIsabela',
            'forTransmitMidsayap',
            'forTransmitLosBanos',
            'forTransmitNegros',
            'paidAgusan',
            'paidBatac',
            'paidBicol',
            'paidCES',
            'paidIsabela',
            'paidMidsayap',
            'paidLosBanos',
            'paidNegros',
            'totalDelivered',
            'totalDeliveredPercentage',
            'totalPaidDeliveries',
            'totalPaidPercentage',
            'paidPercentageAgusan',
            'paidPercentageBatac',
            'paidPercentageBicol',
            'paidPercentageCES',
            'paidPercentageIsabela',
            'paidPercentageMidsayap',
            'paidPercentageLosBanos',
            'paidPercentageNegros',
            'totalDisbursed',
            'totalDisbursedAgusan',
            'totalDisbursedBatac',
            'totalDisbursedBicol',
            'totalDisbursedCES',
            'totalDisbursedIsabela',
            'totalDisbursedMidsayap',
            'totalDisbursedLosBanos',
            'totalDisbursedNegros',
            'idleTransactions'
         ));
		
    }

    public function getDatedData(Request $request){

        $actualDeliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
        ->whereBetween(DB::raw("DATE_FORMAT(dateCreated, '%m/%d/%Y')"), [$request->startDate, $request->endDate])
        ->get();

        $deliveriesAgusan = 0;
        $deliveriesBatac = 0;
        $deliveriesBicol = 0;
        $deliveriesCES = 0;
        $deliveriesIsabela = 0;
        $deliveriesMidsayap = 0;
        $deliveriesLosBanos = 0;
        $deliveriesNegros = 0;

        //Total Seed Deliveries
        foreach($actualDeliveries as $delivery){
           
            $station = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
            ->select('station')
            ->where('region',$delivery->region)
            ->first();
            
       
            switch ($station->station) {
                case "Agusan":
                    $deliveriesAgusan++;
                    break;
                case "Batac":
                    $deliveriesBatac++;
                    break;
                case "Bicol":
                    $deliveriesBicol++;
                    break;
                case "Central Experiment Station":
                    $deliveriesCES++;
                    break;
                case "Isabela":
                    $deliveriesIsabela++;
                    break;
                case "Los Banos":
                    $deliveriesLosBanos++;
                    break;
                case "Midsayap":
                    $deliveriesMidsayap++;
                    break;
                case "Negros":
                    $deliveriesNegros++;
                    break;
            }
            
            
        }

        $deliveriesAgusan = number_format($deliveriesAgusan);
        $deliveriesBatac = number_format($deliveriesBatac);
        $deliveriesBicol = number_format($deliveriesBicol);
        $deliveriesCES = number_format($deliveriesCES);
        $deliveriesIsabela = number_format($deliveriesIsabela);
        $deliveriesMidsayap = number_format($deliveriesMidsayap);
        $deliveriesLosBanos = number_format($deliveriesLosBanos);
        $deliveriesNegros = number_format($deliveriesNegros);


        //Total Paid Deliveries Per Station
        $paidAgusan = 0;
        $paidBatac = 0;
        $paidBicol = 0;
        $paidCES = 0;
        $paidIsabela = 0;
        $paidMidsayap = 0;
        $paidLosBanos = 0;
        $paidNegros = 0;

        $overallPaid = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('iar_no')
        ->where('status', '=', 'accomplished')
        ->groupBy('iar_no')
        ->get();

        foreach($overallPaid as $paidIAR)
        {
            $getPaidBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('iarCode',$paidIAR->iar_no)
            ->first();

            if(!$getPaidBatch)
            {
                continue;
            }

            $paidDeliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('batchTicketNumber',$getPaidBatch->batchTicketNumber)
            ->whereBetween(DB::raw("DATE_FORMAT(dateCreated, '%m/%d/%Y')"), [$request->startDate, $request->endDate])
            ->first();

            if(!$paidDeliveryData)
            {
                continue;
            }
            
            $getPaidStation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
            ->select('station')
            ->where('region',$paidDeliveryData->region)
            ->first();

            if(!$getPaidStation)
            {
                continue;
            }

            switch ($getPaidStation->station) {
                case "Agusan":
                    $paidAgusan++;
                    break;
                case "Batac":
                    $paidBatac++;
                    break;
                case "Bicol":
                    $paidBicol++;
                    break;
                case "Central Experiment Station":
                    $paidCES++;
                    break;
                case "Isabela":
                    $paidIsabela++;
                    break;
                case "Los Banos":
                    $paidMidsayap++;
                    break;
                case "Midsayap":
                    $paidLosBanos++;
                    break;
                case "Negros":
                    $paidNegros++;
                    break;
            }

        }

        //Percentages of Paid Deliveries
        $paidPercentageAgusan = $deliveriesAgusan != 0 ? number_format(($paidAgusan / $deliveriesAgusan) * 100, 2) : 0;
        $paidPercentageBatac = $deliveriesBatac != 0 ? number_format(($paidBatac / $deliveriesBatac) * 100, 2) : 0;
        $paidPercentageBicol = $deliveriesBicol != 0 ? number_format(($paidBicol / $deliveriesBicol) * 100, 2) : 0;
        $paidPercentageCES = $deliveriesCES != 0 ? number_format(($paidCES / $deliveriesCES) * 100, 2) : 0;
        $paidPercentageIsabela = $deliveriesIsabela != 0 ? number_format(($paidIsabela / $deliveriesIsabela) * 100, 2) : 0;
        $paidPercentageMidsayap = $deliveriesMidsayap != 0 ? number_format(($paidMidsayap / $deliveriesMidsayap) * 100, 2) : 0;
        $paidPercentageLosBanos = $deliveriesLosBanos != 0 ? number_format(($paidLosBanos / $deliveriesLosBanos) * 100, 2) : 0;
        $paidPercentageNegros = $deliveriesNegros != 0 ? number_format(($paidNegros / $deliveriesNegros) * 100, 2) : 0;

        //For Transmittal Per Station
        $forTransmitAgusan = 0;
        $forTransmitBatac = 0;
        $forTransmitBicol = 0;
        $forTransmitCES = 0;
        $forTransmitIsabela = 0;
        $forTransmitMidsayap = 0;
        $forTransmitLosBanos = 0;
        $forTransmitNegros = 0;

        $overallForTransmit = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('iar_no')
        ->where('status', '=', 'to_rcv')
        ->orWhere('status', '=', 'returned')
        ->get();

        foreach($overallForTransmit as $transmitIAR)
        {
            $getBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->where('iarCode',$transmitIAR->iar_no)
            ->first();

             if(!$getBatch){
                continue;
             }

            $deliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('batchTicketNumber',$getBatch->batchTicketNumber)
            ->whereBetween(DB::raw("DATE_FORMAT(dateCreated, '%m/%d/%Y')"), [$request->startDate, $request->endDate])
            ->first();

            if(!$deliveryData){
                continue;
             }
            
            $getStation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
            ->select('station')
            ->where('region',$deliveryData->region)
            ->first();

            if(!$getStation){
                continue;
             }

            switch ($getStation->station) {
                case "Agusan":
                    $forTransmitAgusan++;
                    break;
                case "Batac":
                    $forTransmitBatac++;
                    break;
                case "Bicol":
                    $forTransmitBicol++;
                    break;
                case "Central Experiment Station":
                    $forTransmitCES++;
                    break;
                case "Isabela":
                    $forTransmitIsabela++;
                    break;
                case "Los Banos":
                    $forTransmitMidsayap++;
                    break;
                case "Midsayap":
                    $forTransmitLosBanos++;
                    break;
                case "Negros":
                    $forTransmitNegros++;
                    break;
            }
        }

        //Total Disbursed Per Station
        //Total Disbursed
        $totalDisbursedAgusan = 0;  
        $totalDisbursedBatac = 0;
        $totalDisbursedBicol = 0;
        $totalDisbursedCES = 0;
        $totalDisbursedIsabela = 0;
        $totalDisbursedMidsayap = 0;
        $totalDisbursedLosBanos = 0;
        $totalDisbursedNegros = 0;
        
        $getAccomplished = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->select('iar_no')
        ->where('status', '=', 'accomplished')
        ->get();

        $allDVctrlNo = array();
        $totalDisbursed = 0;

        if($getAccomplished)
        {
            foreach($getAccomplished as $paidIAR)
            {
                $getDVctrlNo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                ->select('dv_control_number')
                ->where('iar_number', 'LIKE', $paidIAR->iar_no)
                ->get();
                array_push($allDVctrlNo,$getDVctrlNo[0]->dv_control_number);
                 
            }

            $uniqueDVctrlNo = array_unique($allDVctrlNo);
            
            foreach($uniqueDVctrlNo as $DVctrlNo)
            {
                $getAmount = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_fmis_data')
                ->where('DVControlNo','LIKE',$DVctrlNo)
                ->groupBy('DVControlNo')
                ->get();
                $totalDisbursed+=$getAmount[0]->Amount;

                foreach($getAmount as $amount)
                {
                    $getIARno = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_particulars')
                    ->where('dv_control_number','LIKE',$DVctrlNo)
                    ->first();

                    if(!$getIARno)
                    {
                        continue;
                    }

                    $confirmAccomplishedIAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
                    ->where('iar_no','LIKE',$getIARno->iar_number)
                    ->where('status', '=', 'accomplished')
                    ->first();

                    if(!$confirmAccomplishedIAR)
                    {
                        continue;
                    }

                    $getIARbatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                    ->where('iarCode',$confirmAccomplishedIAR->iar_no)
                    ->first();

                    if(!$getIARbatch)
                    {
                        continue;
                    }

                    $getIARregion = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->whereBetween(DB::raw("DATE_FORMAT(dateCreated, '%m/%d/%Y')"), [$request->startDate, $request->endDate])
                    ->where('batchTicketNumber',$getIARbatch->batchTicketNumber)
                    ->first();

                    if(!$getIARregion)
                    {
                        continue;
                    }

                    $getIARstation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select('station')
                    ->where('region',$getIARregion->region)
                    ->first();

                    if(!$getIARstation)
                    {
                        continue;
                    }

                    switch ($getIARstation->station) {
                        case "Agusan":
                            $totalDisbursedAgusan+=$amount->Amount;
                            break;
                        case "Batac":
                            $totalDisbursedBatac+=$amount->Amount;
                            break;
                        case "Bicol":
                            $totalDisbursedBicol+=$amount->Amount;
                            break;
                        case "Central Experiment Station":
                            $totalDisbursedCES+=$amount->Amount;
                            break;
                        case "Isabela":
                            $totalDisbursedIsabela+=$amount->Amount;
                            break;
                        case "Los Banos":
                            $totalDisbursedMidsayap+=$amount->Amount;
                            break;
                        case "Midsayap":
                            $totalDisbursedLosBanos+=$amount->Amount;
                            break;
                        case "Negros":
                            $totalDisbursedNegros+=$amount->Amount;
                            break;
                    }
                }
            }
            $totalDisbursed = number_format($totalDisbursed,2);
            $totalDisbursedAgusan = number_format($totalDisbursedAgusan,2);
            $totalDisbursedBatac = number_format($totalDisbursedBatac,2);
            $totalDisbursedBicol = number_format($totalDisbursedBicol,2);
            $totalDisbursedCES = number_format($totalDisbursedCES,2);
            $totalDisbursedIsabela = number_format($totalDisbursedIsabela,2);
            $totalDisbursedMidsayap = number_format($totalDisbursedMidsayap,2);
            $totalDisbursedLosBanos = number_format($totalDisbursedLosBanos,2);
            $totalDisbursedNegros = number_format($totalDisbursedNegros,2);
        }
        

        return [
            'deliveriesAgusan' => $deliveriesAgusan,
            'deliveriesBatac' => $deliveriesBatac,
            'deliveriesBicol' => $deliveriesBicol,
            'deliveriesCES' => $deliveriesCES,
            'deliveriesIsabela' => $deliveriesIsabela,
            'deliveriesMidsayap' => $deliveriesMidsayap,
            'deliveriesLosBanos' => $deliveriesLosBanos,
            'deliveriesNegros' => $deliveriesNegros,
            'paidAgusan'=> $paidAgusan.' ('.$paidPercentageAgusan.'%)',
            'paidBatac'=> $paidBatac.' ('.$paidPercentageBatac.'%)',
            'paidBicol'=> $paidBicol.' ('.$paidPercentageBicol.'%)',
            'paidCES'=> $paidCES.' ('.$paidPercentageCES.'%)',
            'paidIsabela'=> $paidIsabela.' ('.$paidPercentageIsabela.'%)',
            'paidMidsayap'=> $paidMidsayap.' ('.$paidPercentageMidsayap.'%)',
            'paidLosBanos'=> $paidLosBanos.' ('.$paidPercentageLosBanos.'%)',
            'paidNegros'=> $paidNegros.' ('.$paidPercentageNegros.'%)',
            'forTransmitAgusan' => $forTransmitAgusan,
            'forTransmitBatac' => $forTransmitBatac,
            'forTransmitBicol' => $forTransmitBicol,
            'forTransmitCES' => $forTransmitCES,
            'forTransmitIsabela' => $forTransmitIsabela,
            'forTransmitMidsayap' => $forTransmitMidsayap,
            'forTransmitLosBanos' => $forTransmitLosBanos,
            'forTransmitNegros' => $forTransmitNegros,
            'totalDisbursed' => $totalDisbursed,
            'totalDisbursedAgusan' => '₱ '.$totalDisbursedAgusan,
            'totalDisbursedBatac' => '₱ '.$totalDisbursedBatac,
            'totalDisbursedBicol' => '₱ '.$totalDisbursedBicol,
            'totalDisbursedCES' => '₱ '.$totalDisbursedCES,
            'totalDisbursedIsabela' => '₱ '.$totalDisbursedIsabela,
            'totalDisbursedMidsayap' => '₱ '.$totalDisbursedMidsayap,
            'totalDisbursedLosBanos' => '₱ '.$totalDisbursedLosBanos,
            'totalDisbursedNegros' => '₱ '.$totalDisbursedNegros
            
        ];
        
    }

    public function sendAlert(){

        
        //Idle Transactions
        $currentDateTime = Carbon::now();
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
        
        $getAllData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
        ->get();
        // dd($getAllData);
        $getSmsSettings = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_notifications')
        ->where('notification','LIKE','sms')
        ->first();
        $getEmailSettings = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_notifications')
        ->where('notification','LIKE','email')
        ->first();
        $toContact =[];
        // dd($getAllData);
        foreach ($getAllData as $record) {
            $dataSynced = Carbon::parse($record->data_synced);
    
            if ($currentDateTime->isWeekend()) {
                continue;
            }
    
            $differenceInDays = $dataSynced->diffInDaysFiltered(function(Carbon $date) {
                return !$date->isWeekend();
            }, $currentDateTime);
            
    
            if ($differenceInDays >= 3) {
                array_push($toContact,$record->dro_id);
            }
        }

        // dd(count($toContact));
        $countIdle = count($toContact);
        $toContact = array_unique($toContact);

        //Finance
        if($getSmsSettings->status==1 || $getSmsSettings->status=='1'){
            $url3 = 'https://isd.philrice.gov.ph/ptc_v2/api/_api/send_message';
            $data3 = [
                'token' => 'cb74aa5ba38deaa9a2211f5d1394a9a7',
                'mobile' => '09477673490',
                'message' => "Good day ma'am/sir, this is to inform you that the RSMS have detected ($countIdle) idle trasactions or pending transactions (no movement of more than 3 days) accross all of the RCEF Branch Units, please coordinate with the RCEF Coordinators and their respective units to address this observation, for more details you may refer to the RSMS Payments Monitoring Dashboard. Thank you and have a nice day!"
            ];
            
            
            $ch3 = curl_init($url3);
    
    
            curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch3, CURLOPT_POST, 1);
            curl_setopt($ch3, CURLOPT_POSTFIELDS, $data3);
            
            $response3 = curl_exec($ch3);
            
            if ($response3 === false) {
                echo 'cURL error: ' . curl_error($ch3);
            }
            
            curl_close($ch3);
        }

        if($getEmailSettings->status==1 || $getEmailSettings->status=='1'){
            $recipientEmail = 'mitchiecruz@gmail.com';
                $IARno = $record->iar_no;
                $message = 'Good day.
                
                This is to inform you that the RSMS have detected ('.$countIdle.') idle trasactions or pending transactions (no movement of more than 3 days) accross all of the RCEF Branch Units, please coordinate with the RCEF Coordinators and their respective units to address this observation, for more details you may refer to the RSMS Payments Monitoring Dashboard. Thank you and have a nice day!
                
                This is an auto-generated e-mail. Please do not reply.';
                $messageData = nl2br($message, false) ;
                $datenow = date("Y-m-d H:i:s");
    
                $data = [
                    'messageData' => $messageData,
                    'dateNow' => $datenow,
                ];
    
                // Store the original mail configuration
                $originalConfig = config('mail');
    
                // Set temporary mail configuration for this email
                config([
                    'mail.from.address' => 'rcefseeds.mailer@gmail.com',
                    'mail.from.name' => 'RCEF Seeds',
                    'mail.username' => 'rcefseeds.mailer@gmail.com',
                    'mail.password' => 'gmco rpvn hkbg wjeu',
                ]);
    
                $transport = (new Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption')))
                    ->setUsername(config('mail.username'))
                    ->setPassword(config('mail.password'));
    
    
                
                // Create a Swift_Mailer instance with the custom transport
                $mailer = new Swift_Mailer($transport);
    
                
                // Create a Swift_Message instance
                $swiftMessage = (new Swift_Message('Reminder regarding idle IARs'))
                    ->setFrom([config('mail.from.address') => config('mail.from.name')])
                    ->setTo([$recipientEmail])
                    ->setBody($data['messageData'])
                    ->setContentType('text/html');
                try {
                    // Send the email using the custom Swift_Mailer
                    $mailer->send($swiftMessage);
    
                    $status = 'sent';
                } catch (\Exception $e) {
                    // Log the detailed error message
                    $status = 'error: ' . $e->getMessage();
                }
    
                // Restore the original mail configuration
                config(['mail' => $originalConfig]);

        }
        
        //DROs and Coordinators
        foreach($toContact as $dro){
            $idleIARs = [];
            $getIARs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
            ->where('dro_id', 'LIKE', $dro)
            ->get();

            $countIARs = count(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_confirmation')
            ->where('dro_id', 'LIKE', $dro)
            ->get());
            foreach($getIARs as $IAR){
                array_push($idleIARs,$IAR->iar_no);
            }
            $idleIARs = implode(', ',$idleIARs);


            if($getSmsSettings->status==1 || $getSmsSettings->status=='1'){
                //DRO
                $getContactNo = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dro')
                ->where('stationId', 'LIKE', $dro)
                ->first();
                
                $url = 'https://isd.philrice.gov.ph/ptc_v2/api/_api/send_message';
                $data = [
                    'token' => 'cb74aa5ba38deaa9a2211f5d1394a9a7',
                    'mobile' => $getContactNo->contactNo,
                    'message' => "Good day. This message is to inform you that the IAR(s) $idleIARs has one (1) or more missing/invalid attachment(s) and is now on-hold until the missing/invalid attachment(s) has been provided. Thank you and have a nice day!"
                ];
                
                
                $ch = curl_init($url);
    
    
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                
                $response = curl_exec($ch);
                
                if ($response === false) {
                    echo 'cURL error: ' . curl_error($ch);
                }
                
                curl_close($ch);

                //Coordinators
                $getContactNo2 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_coordinators')
                ->where('stationId', 'LIKE', $dro)
                ->first();
                
                $url2 = 'https://isd.philrice.gov.ph/ptc_v2/api/_api/send_message';
                $data2 = [
                    'token' => 'cb74aa5ba38deaa9a2211f5d1394a9a7',
                    // 'mobile' => '09912041666',
                    'mobile' => $getContactNo2->contactNo,
                    'message' => "Good day ma'am/sir, this is to inform you that we have detected ($countIARs) idle transactions due for seed deliveries handled by your station, we humbly request your immediate action on behalf of the RCEF Branch Unit. Please coordinate with your DRO & Coordinators, for more details you may refer to the RSMS Payments Monitoring Dashboard or the RSMS All-in-One App. Thank you and have a nice day!"
                ];
                
                
                $ch2 = curl_init($url2);
    
    
                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch2, CURLOPT_POST, 1);
                curl_setopt($ch2, CURLOPT_POSTFIELDS, $data2);
                
                $response2 = curl_exec($ch2);
                
                if ($response2 === false) {
                    echo 'cURL error: ' . curl_error($ch2);
                }
                
                curl_close($ch2);

                
            }
    
            if($getEmailSettings->status==1 || $getEmailSettings->status=='1'){
                //DROs
                $getEmail = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dro')
                        ->where('stationId', 'LIKE', $dro)
                        ->first();
    
                $recipientEmail = $getEmail->email;
                $IARno = $record->iar_no;
                $message = 'Good day.
                
                This message is to inform you that the IAR(s) '.$idleIARs.' has one (1) or more missing/invalid attachment(s) and is now on-hold until the missing/invalid attachment(s) has been provided. Thank you and have a nice day!
                
                This is an auto-generated e-mail. Please do not reply.';
                $messageData = nl2br($message, false) ;
                $datenow = date("Y-m-d H:i:s");
    
                $data = [
                    'messageData' => $messageData,
                    'dateNow' => $datenow,
                ];
    
                // Store the original mail configuration
                $originalConfig = config('mail');
    
                // Set temporary mail configuration for this email
                config([
                    'mail.from.address' => 'rcefseeds.mailer@gmail.com',
                    'mail.from.name' => 'RCEF Seeds',
                    'mail.username' => 'rcefseeds.mailer@gmail.com',
                    'mail.password' => 'gmco rpvn hkbg wjeu',
                ]);
    
                $transport = (new Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption')))
                    ->setUsername(config('mail.username'))
                    ->setPassword(config('mail.password'));
    
    
                
                // Create a Swift_Mailer instance with the custom transport
                $mailer = new Swift_Mailer($transport);
    
                
                // Create a Swift_Message instance
                $swiftMessage = (new Swift_Message('Reminder regarding IAR No. '.$idleIARs))
                    ->setFrom([config('mail.from.address') => config('mail.from.name')])
                    ->setTo([$recipientEmail])
                    ->setBody($data['messageData'])
                    ->setContentType('text/html');
                try {
                    // Send the email using the custom Swift_Mailer
                    $mailer->send($swiftMessage);
    
                    $status = 'sent';
                } catch (\Exception $e) {
                    // Log the detailed error message
                    $status = 'error: ' . $e->getMessage();
                }
    
                // Restore the original mail configuration
                config(['mail' => $originalConfig]);

                //Coordinators
                $getEmail2 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_coordinators')
                        ->where('stationId', 'LIKE', $dro)
                        ->first();
    
                // $recipientEmail2 = 'bryan.s.delos.santos@gmail.com';
                $recipientEmail2 = $getEmail2->email;
                $IARno = $record->iar_no;
                $message2 = 'Good day.
                
                This is to inform you that we have detected ('.$countIARs.') idle transactions due for seed deliveries handled by your station, we humbly request your immediate action on behalf of the RCEF Branch Unit. Please coordinate with your DRO & Coordinators, for more details you may refer to the RSMS Payments Monitoring Dashboard or the RSMS All-in-One App. Thank you and have a nice day!
                
                This is an auto-generated e-mail. Please do not reply.';
                $messageData2 = nl2br($message2, false) ;
                $datenow2 = date("Y-m-d H:i:s");
    
                $data2 = [
                    'messageData' => $messageData2,
                    'dateNow' => $datenow2,
                ];
    
                // Store the original mail configuration
                $originalConfig2 = config('mail');
    
                // Set temporary mail configuration for this email
                config([
                    'mail.from.address' => 'rcefseeds.mailer@gmail.com',
                    'mail.from.name' => 'RCEF Seeds',
                    'mail.username' => 'rcefseeds.mailer@gmail.com',
                    'mail.password' => 'gmco rpvn hkbg wjeu',
                ]);
    
                $transport2 = (new Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption')))
                    ->setUsername(config('mail.username'))
                    ->setPassword(config('mail.password'));
    
    
                
                // Create a Swift_Mailer instance with the custom transport
                $mailer2 = new Swift_Mailer($transport);
    
                
                // Create a Swift_Message instance
                $swiftMessage2 = (new Swift_Message('Reminder regarding IAR No. '.$idleIARs))
                    ->setFrom([config('mail.from.address') => config('mail.from.name')])
                    ->setTo([$recipientEmail2])
                    ->setBody($data2['messageData'])
                    ->setContentType('text/html');
                try {
                    // Send the email using the custom Swift_Mailer
                    $mailer2->send($swiftMessage2);
    
                    $status2 = 'sent';
                } catch (\Exception $e2) {
                    // Log the detailed error message
                    $status2 = 'error: ' . $e2->getMessage();
                }
    
                // Restore the original mail configuration
                config(['mail' => $originalConfig2]);
            }
        }

        
    }

    public function checkNotifSetting(){
        $getSettings = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_notifications')
        ->get();

        return ($getSettings);
    }

    public function updateNotifSetting(Request $request){
        $email = 0;
        $sms = 0;
        
        switch ($request->email) {
            case "true":
                $email = 1;
                break;
                case "false":
                    $email = 0;
                    break;
                }
                
        switch ($request->sms) {
            case "true":
                $sms = 1;
                break;
                case "false":
                    $sms = 0;
                    break;
                }
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_notifications')
        ->where('notification','LIKE','email')
        ->update([
            'status' => $email,
            ]);
            
        DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_notifications')
        ->where('notification','LIKE','sms')
        ->update([
            'status' => $sms,
            ]);
    }

  

    // public function sendMail(){
    //     $recipientEmail = 'bryan.s.delos.santos@gmail.com';
    //     $message = 'Good day.
        
    //     This message is to inform you that the IAR: $record->iar_no has one (1) or more missing/invalid attachment(s) and is now on-hold until the missing/invalid attachment(s) has been provided. Thank you and have a nice day!';
    //     $messageData = nl2br($message, false) ;
    //     $datenow = date("Y-m-d H:i:s");

    //     $data = [
    //         'messageData' => $messageData,
    //         'dateNow' => $datenow,
    //     ];

    //     // Store the original mail configuration
    //     $originalConfig = config('mail');

    //     // Set temporary mail configuration for this email
    //     config([
    //         'mail.from.address' => 'rcefseeds.mailer@gmail.com',
    //         'mail.from.name' => 'RCEF Seeds',
    //         'mail.username' => 'rcefseeds.mailer@gmail.com',
    //         'mail.password' => 'gmco rpvn hkbg wjeu',
    //     ]);

    //     $transport = (new Swift_SmtpTransport(config('mail.host'), config('mail.port'), config('mail.encryption')))
    //         ->setUsername(config('mail.username'))
    //         ->setPassword(config('mail.password'));


        
    //     // Create a Swift_Mailer instance with the custom transport
    //     $mailer = new Swift_Mailer($transport);

        
    //     // Create a Swift_Message instance
    //     $swiftMessage = (new Swift_Message('Reminder regarding IAR No. ######'))
    //         ->setFrom([config('mail.from.address') => config('mail.from.name')])
    //         ->setTo([$recipientEmail])
    //         ->setBody($data['messageData'])
    //         ->setContentType('text/html');
    //     try {
    //         // Send the email using the custom Swift_Mailer
    //         $mailer->send($swiftMessage);

    //         $status = 'sent';
    //     } catch (\Exception $e) {
    //         // Log the detailed error message
    //         $status = 'error: ' . $e->getMessage();
    //     }

    //     // Restore the original mail configuration
    //     config(['mail' => $originalConfig]);

    //     return $status;
    // }


}

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
use Carbon\Carbon;


use Auth;

class paymentsMonitoringController extends Controller {


    public function index() {
        
        return view('payment_monitoring.home');
    }

    public function getInitialData(){
        $tableData =[];
        $currentDate = Carbon::now()->toDateString();

        $getActive = DB::table('ds2024_rcep_delivery_inspection.iar_confirmation')
        ->where('data_uploaded_on','LIKE',$currentDate.'%')
        ->get();

        foreach($getActive as $active)
        {
            $getBatch = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
            ->select('batchTicketNumber')
            ->where('iarCode', $active->iar_no)
            ->first();

            if($getBatch){
                $deliveryData = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->select(DB::raw('SUM(totalBagCount) as expectedBags'), 'coopAccreditation','region', 'province', 'municipality', 'dropOffPoint','deliveryDate')
                ->where('batchTicketNumber', $getBatch->batchTicketNumber)
                ->groupBy('batchTicketNumber')
                ->first();
            
                $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $deliveryData->coopAccreditation)->value('coopName');

                $actual_delivery = DB::table('ds2024_rcep_delivery_inspection.tbl_actual_delivery')
                ->select('dateCreated', 'qrStart','qrEnd', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actualBags'))
                ->where('batchTicketNumber', $getBatch->batchTicketNumber)
                ->where('region', $deliveryData->region)
                ->groupBy('batchTicketNumber')
                ->first();

                $dateInspected = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection')
                ->where('batchTicketNumber', $getBatch->batchTicketNumber)
                ->first();

                switch ($active->status) {
                    case "to_rcv":
                        $active->status = 'For Transmittal';
                        break;
                    case "returned":
                        $active->status = 'For Transmittal';
                        break;
                    case "received":
                        $active->status = 'Received';
                        break;
                    case "to_prp":
                        $active->status = 'For Preparation';
                        break;
                    case "to_prc":
                        $active->status = 'For Processing';
                        break;
                    case "to_pay":
                        $active->status = 'For Processing';
                        break;
                    case "accomplished":
                        $active->status = 'Paid';
                        break;
                    case "on_hold":
                        switch ($active->status_before_hold) {
                            case "to_rcv":
                                $active->status = 'For Transmittal';
                                break;
                            case "returned":
                                $active->status = 'For Transmittal';
                                break;
                            case "received":
                                $active->status = 'Received';
                                break;
                            case "to_prp":
                                $active->status = 'For Preparation';
                                break;
                            case "to_prc":
                                $active->status = 'For Processing';
                                break;
                            case "to_pay":
                                $active->status = 'For Processing';
                                break;
                            case "accomplished":
                                $active->status = 'Paid';
                                break;
                        }
                        break;
                }
                
                array_push($tableData,array(
                    "iar_no" => $active->iar_no,
                    "batchTicketNumber" => $getBatch->batchTicketNumber,
                    "region" => $deliveryData->region,
                    "province" => $deliveryData->province,
                    "municipality" => $deliveryData->municipality,
                    "coopName" => $coop_name,
                    "bags" => $actual_delivery->actualBags,
                    "dateInspected" => $dateInspected->dateInspected,
                    "paymentStatus" => $active->status,
                    
                )); 
            }
        }

        $tableData = collect($tableData);
        return Datatables::of($tableData)
        ->make(true);
    }

    
    
}

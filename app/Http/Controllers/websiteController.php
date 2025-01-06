<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\guest;
use DB;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Hash;
use Auth;
class websiteController extends Controller
{
    public function calendarEvents($api,$type){
        if($api == "TheCodeasdjp[q1!"){
            if($type=="view_date"){
                $actual =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->select("batchTicketNumber")
                ->groupBy("batchTicketNumber")
                ->get();
                $actual = json_decode(json_encode($actual), true);

                $result_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                    ->select(DB::raw("max(deliveryDate) as max"),DB::raw("min(deliveryDate) as min"))
                    // ->groupBy("batchTicketNumber")
                    ->whereIn("batchTicketNumber", $actual)
                    // ->orderBy("deliveryDate", "ASC")
                    ->first();
            }elseif($type=="events"){

                $actual =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                ->select("batchTicketNumber")
                ->groupBy("batchTicketNumber")
                ->where("qrValStart", "")
                ->where("isBuffer", "0")
                ->get();
                //dd($actual);

                $actual = json_decode(json_encode($actual), true);

                $events = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                    ->select("region", "province", "municipality", "dropOffPoint","deliveryDate")
                    ->groupBy("batchTicketNumber")
                    ->whereIn("batchTicketNumber", $actual)
              
                    ->where("isBuffer", "0")
                    ->orderBy("deliveryDate", "ASC")
                    ->get();
                $return_data;
                    foreach($events as $key => $event)
                    {
                        $result_data[] =  array('id'=> null,
                        'season' => "ws2022",
                        'title'   => $event->dropOffPoint,
                        'distri_type' => 'REGULAR',
                        'start'   => date('Y-m-d H:i:s', strtotime($event->deliveryDate)),
                        'end'   => date('Y-m-d H:i:s', strtotime($event->deliveryDate)),
                        'description'   => "",
                        'region' => $event->region,
                        'province'   => $event->province,
                        'municipality'   =>$event->municipality,
                        'color' => "#007bff"
                        );
                    }
            
                    $paymaya = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
                    ->groupBy("province")
                    ->groupBy("municipality")
                    ->groupBy(DB::raw("CONCAT(schedule_start),'-',CONCAT(schedule_end)"))
                    ->get();
            
                    foreach($paymaya as $paymaya_event)
                    {
                        $result_data[] =  array('id'=> null,
                        'season' => "ws2022",
                        'title'   => $paymaya_event->drop_off_point,
                        'distri_type' => 'E-BINHI',
                        'start'   => date('Y-m-d H:i:s', strtotime($paymaya_event->schedule_start)),
                        'end'   => date('Y-m-d H:i:s', strtotime($paymaya_event->schedule_end)),
                        'description'   => "",
                        'region' => $paymaya_event->region,
                        'province'   => $paymaya_event->province,
                        'municipality'   =>$paymaya_event->municipality,
                        'color' => "#28a745"
                        );
                    }
            
            
            
            
                }

           

                return json_encode($result_data);
        }
    }

}

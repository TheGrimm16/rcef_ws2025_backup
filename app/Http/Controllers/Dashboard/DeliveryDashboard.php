<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use DB;
use Illuminate\Http\Request;

class DeliveryDashboard extends Controller
{
    // delivery schedule per month
    public function ds_calendar()
    {
        
        $current_month = date('F Y');
        $min_date = DB::table($GLOBALS['season_prefix'] . 'rcep_delivery_inspection.tbl_delivery_transaction')
            ->orderBy('delivery_date', 'asc')
            ->first();
        $max_date = DB::table($GLOBALS['season_prefix'] . 'rcep_delivery_inspection.tbl_delivery_transaction')
            ->orderBy('delivery_date', 'desc')
            ->first();

        $start = (new DateTime($min_date->delivery_date))->modify('first day of this month');
        $end = (new DateTime($max_date->delivery_date))->modify('first day of this month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $month = [];

        foreach ($period as $dt) {
            $months[] = [
                'label' => $dt->format('F Y'),
                'value' => $dt->format('Y-m-d'),
            ];
        }

        $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where('region', '!=', '')
            ->groupBy('regionName')
            ->orderBy('region_sort')
            ->get();
        
        $coop = DB::table($GLOBALS['season_prefix'] . 'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('isActive', '1')
            ->get();

        return view('dashboard.delivery.index', compact('current_month', 'months', 'min_date', 'max_date', 'regions', 'coop'));
    }

    public function calendar_data(Request $request)
    {
        $week = $this->getWeekRange($request->date);
        $data = [];
        foreach ($week as $key => $w) {
            $delivery_data = DB::table($GLOBALS['season_prefix'] . 'rcep_delivery_inspection.tbl_delivery_transaction')
                ->select('*', 
                    DB::RAW('SUM(IF(status = "0",instructed_delivery_volume,0)) as scheduled'),
                    DB::RAW('SUM(IF(status = "1",instructed_delivery_volume,0)) as delivered'),
                    DB::RAW('SUM(IF(status = "2",instructed_delivery_volume,0)) as pending'),
                    DB::RAW('SUM(IF(status = "3",instructed_delivery_volume,0)) as cancelled'),
                    DB::RAW('SUM(instructed_delivery_volume) as total_delivery')
                )
                ->leftJoin($GLOBALS['season_prefix'] . 'rcep_delivery_inspection.lib_dropoff_point', 
                    'lib_dropoff_point.prv_dropoff_id', '=', 'tbl_delivery_transaction.prv_dropoff_id')
                ->whereBetween('delivery_date', [$w['start'], $w['end']]);
           
            if ($request->coop != '0') {
                $delivery_data = $delivery_data->where('tbl_delivery_transaction.moa_number', $request->coop);
            }

            if ($request->region != '0') {
                $delivery_data = $delivery_data->where('lib_dropoff_point.region', $request->region);
            }

            if ($request->province != '0') {
                $delivery_data = $delivery_data->where('lib_dropoff_point.province', $request->province);
            }

            $delivery_data = $delivery_data->orderBy('delivery_date', 'asc')
                ->groupBy('moa_number')
                ->get();
            
            foreach ($delivery_data as $k => $d) {
                $scheduled_per = (($d->scheduled + $d->pending) / $d->total_delivery) * 100;
                $delivered_per = (($d->delivered) / $d->total_delivery) * 100;
                $cancelled_per = (($d->cancelled) / $d->total_delivery) * 100;
                $data[$d->moa_number][$key]['data'] = [
                    'scheduled' => $d->scheduled + $d->pending,
                    'delivered' => $d->delivered,
                    'cancelled' => $d->cancelled,
                    'scheduled_per' => $scheduled_per,
                    'delivered_per' => $delivered_per,
                    'cancelled_per' => $cancelled_per,
                    'total' => $d->total_delivery
                ];
                if (!array_key_exists("name",$data[$d->moa_number])){
                    $coop =  DB::table($GLOBALS['season_prefix'] . 'rcep_seed_cooperatives.tbl_cooperatives')
                        ->where('current_moa', $d->moa_number)
                        ->where('isActive', '1')
                        ->first();

                    if(count($coop) > 0){
                        $data[$d->moa_number]['name'] = $coop->coopName;
                    }else{
                        $data[$d->moa_number]['name'] = $d->moa_number;
                    }
                }
                
            }
        }
        $html = view('dashboard.delivery.calendar.data', compact('data', 'week'));
        return $html;
    }

    public function getWeekRange($date)
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        Carbon::setWeekEndsAt(Carbon::SUNDAY);
        //format string
        $f = 'Y-m-d';

        //if you want to record time as well, then replace today() with now()
        //and remove startOfDay()
        $today = Carbon::createFromFormat('Y-m-d', $date);
        $date = $today->copy()->firstOfMonth()->startOfDay();
        $eom = $today->copy()->endOfMonth()->startOfDay();

        $dates = [];

        for ($i = 1; $date->lte($eom); $i++) {

            //record start date
            $startDate = $date->copy();

            //loop to end of the week while not crossing the last date of month
            while ($date->dayOfWeek != Carbon::SUNDAY && $date->lte($eom)) {
                $date->addDay();
            }

            $dates['w' . $i]['start'] = $startDate->format($f);
            $dates['w' . $i]['end'] = $date->format($f);
            $date->addDay();
        }

        return $dates;
    }
}

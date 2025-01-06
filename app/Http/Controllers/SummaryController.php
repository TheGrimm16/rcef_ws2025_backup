<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;
use Excel;
use Auth;
use DB;

class SummaryController extends Controller {

    // public function index() {
    //     // Get total confirmed deliveries of dropoff point
    //     $confirmed = \DB::connection('delivery_inspection_db')
    //             ->table('tbl_delivery')
    //             ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
    //             ->where('batchTicketNumber', 'NOT LIKE', '%void%')
    //             ->where('dropOffPoint', 'NOT LIKE', '%void%')
    //             ->first();

    //     // Get total actual deliveries of dropoff point
    //     $actual = \DB::connection('delivery_inspection_db')
    //             ->table('tbl_actual_delivery')
    //             ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
    //             ->first();

    //     return view('summary.index')
    //                     ->with(compact('confirmed'))
    //                     ->with(compact('actual'));
    // }

    // public function datatable(Request $request) {
    //     // Get region, province and municipality in dropoff point library
	// 	if(Auth::user()->roles->first()->name == "da-icts"){
    //         $dropoffs = \DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_dropoff_point')
    //             ->select('region', 'province', 'municipality', 'prv_dropoff_id', 'dropOffPoint')
    //             ->where('region', $request->region)
    //             ->where('province', $request->province)
    //             ->where('municipality', $request->municipality)
    //             ->groupBy('prv_dropoff_id')
    //             ->get();

    //     }else{
    //         $dropoffs = \DB::connection('delivery_inspection_db')
    //             ->table('lib_dropoff_point')
    //             ->select('region', 'province', 'municipality', 'prv_dropoff_id', 'dropOffPoint')
    //             ->where('region', $request->region)
    //             ->where('province', $request->province)
    //             ->where('municipality', $request->municipality)
    //             ->groupBy('prv_dropoff_id')
    //             ->get();
    //     }

    //     $table_data = array();

    //     foreach ($dropoffs as $dropoff) {
    //         // Get total confirmed deliveries of dropoff point
    //         $confirmed = \DB::connection('delivery_inspection_db')
    //                 ->table('tbl_delivery')
    //                 ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
    //                 ->where('region', $dropoff->region)
    //                 ->where('province', $dropoff->province)
    //                 ->where('municipality', $dropoff->municipality)
    //                 ->where('prv_dropoff_id', $dropoff->prv_dropoff_id)
    //                 ->where('batchTicketNumber', 'NOT LIKE', '%void%')
    //                 ->where('dropOffPoint', 'NOT LIKE', '%void%')
    //                 ->first();

    //         // Get total actual deliveries of dropoff point
    //         $actual = \DB::connection('delivery_inspection_db')
    //                 ->table('tbl_actual_delivery')
    //                 ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
    //                 ->where('region', $dropoff->region)
    //                 ->where('province', $dropoff->province)
    //                 ->where('municipality', $dropoff->municipality)
    //                 ->where('prv_dropoff_id', $dropoff->prv_dropoff_id)
    //                 ->where('batchTicketNumber', "!=", "TRANSFER")
    //                 ->first();

    //         $transferred = \DB::connection('delivery_inspection_db')
    //                 ->table('tbl_actual_delivery')
    //                 ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
    //                 ->where('region', $dropoff->region)
    //                 ->where('province', $dropoff->province)
    //                 ->where('municipality', $dropoff->municipality)
    //                 ->where('prv_dropoff_id', $dropoff->prv_dropoff_id)
    //                 ->where('batchTicketNumber', "TRANSFER")
    //                 ->first();
    //         if ($confirmed->total_bag_count != 0 || $actual->total_bag_count != 0 || $transferred->total_bag_count != 0) {
    //             $data = array(
    //                 'region' => $dropoff->region,
    //                 'province' => $dropoff->province,
    //                 'municipality' => $dropoff->municipality,
    //                 'dropoff_point' => $dropoff->dropOffPoint,
    //                 'confirmed_delivery' => number_format($confirmed->total_bag_count),
    //                 'actual_delivery' => number_format($actual->total_bag_count),
    //                 'transferred' => number_format($transferred->total_bag_count)
    //             );

    //             array_push($table_data, $data);
    //         }
    //     }

    //     $table_data = collect($table_data);

    //     return Datatables::of($table_data)->make(true);
    // }

    // public function get_delivery_provinces(Request $request){
    //     $provinces = \DB::connection('delivery_inspection_db')
    //         ->table('tbl_delivery')
    //         ->where('region', $request->region)
    //         ->groupBy('province')
    //         ->orderBy('province', 'ASC')
    //         ->get();

    //     $return_str= '';
    //     foreach($provinces as $row){
    //         $return_str .= "<option value='$row->province'>$row->province</option>";
    //     }
    //     return $return_str;
    // }

    // public function get_delivery_municipalities(Request $request){
    //     $municipalities = \DB::connection('delivery_inspection_db')
    //             ->table('tbl_delivery')
    //             ->where('region', $request->region)
    //             ->where('province', $request->province)
    //             ->orderBy('municipality', 'ASC')
    //             ->groupBy('municipality')
    //             ->get();

    //     $return_str= '';
    //     foreach($municipalities as $row){
    //             $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
    //     }
    //     return $return_str;
    // }

    public function index() {
        // Get total confirmed deliveries of dropoff point
        $target = \DB::connection('delivery_inspection_db')
                ->table('tbl_delivery_sum')
                ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                // ->select('region', 'province', 'targetVolume','pro_code')
                ->where('region', $request->region)
                ->where('province', $request->province)
                ->where('targetVolume', $request->targetVolume)
                // ->where('municipality', $request->municipality)
                ->groupBy('pro_code')
                ->first();

        // Get total actual deliveries of dropoff point
        $actual = \DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                // ->select('totalBagCount')
                ->first();
            
        
        return view('summary.index')
                        ->with(compact('target'))
                        ->with(compact('actual'));
    }

    public function datatable(Request $request) {

        // dd($request->month);
        $table_data = array();
        // Get region, province and municipality in dropoff point library
		if(Auth::user()->roles->first()->name == "da-icts"){
            // $dropoffs = \DB::table('tbl_actual_delivery')
            $dropoffs = \DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection_mirror.lib_dropoff_point')
                ->select('region', 'province', 'targetVolume')
                ->where('region', $request->region)
                ->where('province', $request->province)
                // ->where('targetVolume', $request->targetVolume)
                // ->where('municipality', $request->municipality)
                // ->groupBy('pro_code')
                ->get();
                // dd($dropoffs);

                foreach($dropoffs as $dropoff)
                {
                    $percent = ($dropoff->totalBagCount / $dropoff->targetVolume) * 100;
                    $formattedPercent = number_format($percent, 2) . '%'; // Format as percentage
                    array_push($table_data, array(
                        'region' => $dropoff->region,
                        'province' => $dropoff->province,
                        'targetVolume' => $dropoff->targetVolume,
                        'totalBagCount' => $dropoff->totalBagCount,
                        'percent' => $formattedPercent
                    ));
                }
            }else{
                // dd($request->region,$request->province);
                $dropoffs = \DB::connection('delivery_inspection_db')
                ->table('tbl_delivery_sum')
                ->select('id','region', 'province','season', 'targetVolume','targetMonthFrom', 'targetMonthTo')
                ->where('region','LIKE', $request->region)
                ->where('province','LIKE', $request->province)
                ->whereRaw('concat(targetMonthFrom, " - ",targetMonthTo) LIKE "'.$request->month.'"')
                // ->where('targetVolume', $request->targetVolume)
                // ->where('municipality', $request->municipality)
                // ->groupBy('pro_code')   
                ->get();
                // dd($dropoffs);

                foreach($dropoffs as $dropoff)
                {
                    // dd($dropoff->targetMonthFrom);
                    $actual = \DB::connection('delivery_inspection_db')
                    ->table('tbl_actual_delivery')
                    ->select(DB::raw('SUM(totalBagCount) as totalBagCount'))
                    ->where('region', 'LIKE', $request->region)
                    ->where('province', 'LIKE', $request->province)
                    ->whereBetween('dateCreated', [$dropoff->targetMonthFrom, $dropoff->targetMonthTo])
                    ->first();
                    $totalBagCount = 0;
                    // dd($actual);
                    if($actual->totalBagCount){
                        $totalBagCount = $actual->totalBagCount;
                    }else{
                        $totalBagCount = (int)0;
                    }

                    $target = $dropoff->targetVolume; 

                    $percent = 0;
                    if ($target > 0) {
                        $percent = ($totalBagCount / $target) * 100;
                        if(!$percent) $percent = (int)0;
                    }
                    $formattedPercent = number_format($percent, 2) . '%'; // Format to 2 decimal places
                    
                    array_push($table_data, array(
                        'region' => $dropoff->region,
                        'province' => $dropoff->province,
                        // 'month' => substr($dropoff->targetMonthFrom,0,7),
                        'totalBagCount' => $totalBagCount,
                        'targetVolume' =>$target,
                        'percent' => $formattedPercent
                    ));
                }           
            }

            
            
            
            // foreach ($dropoffs as $dropoff) {
                //     // Get total confirmed deliveries of dropoff point
        //     $confirmed = \DB::connection('delivery_inspection_db')
        //             // ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
        //             ->select('region', 'province', 'targetVolume', 'pro_code')
        //             ->table('tbl_delivery_sum')
        //             ->where('region', $dropoff->region)
        //             ->where('province', $dropoff->province)
        //             // ->where('municipality', $dropoff->municipality)
        //             ->where('targetVolume', $dropoff->targetVolume)
        //             ->where('pro_code', $dropoff->pro_code)
        //             ->first();

        //     // Get total actual deliveries of dropoff point
        //     $actual = \DB::connection('delivery_inspection_db')
        //             ->table('tbl_delivery_sum')
        //             ->select('region', 'province', 'targetVolume', 'pro_code')
        //             // ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
        //             ->where('region', $dropoff->region)
        //             ->where('province', $dropoff->province)
        //             // ->where('municipality', $dropoff->municipality)
        //             ->where('targetVolume', $dropoff->targetVolume)
        //             ->where('pro_code', $dropoff->pro_code)
        //             ->first();

        //     $transferred = \DB::connection('delivery_inspection_db')
        //             ->table('tbl_delivery_sum') 
        //             ->select('region', 'province', 'targetVolume', 'pro_code')
        //             // ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
        //             // ->where('region', $dropoff->region)
        //             // ->where('province', $dropoff->province)
        //             // ->first();
        //             ->where('region', $dropoff->region)
        //             ->where('province', $dropoff->province)  
        //             // ->where('municipality', $dropoff->municipality)
        //             ->where('targetVolume', $dropoff->targetVolume)
        //             ->where('pro_code', $dropoff->pro_code)
        //             ->first();

        //     if ($confirmed->total_bag_count != 0 || $actual->total_bag_count != 0 || $transferred->total_bag_count != 0) {
        //         $data = array(
        //             'region' => $dropoff->region,
        //             'province' => $dropoff->province,
        //             // 'municipality' => $dropoff->municipality,
        //             'dropoff_point' => $dropoff->dropOffPoint,
        //             'pro_code' => $dropoff->pro_code,
        //             'targetVolume' => $dropoff->targetVolume,
        //             'confirmed_delivery' => number_format($confirmed->total_bag_count),
        //             'actual_delivery' => number_format($actual->total_bag_count),
        //             'transferred' => number_format($transferred->total_bag_count)
        //         );
                
        //         array_push($table_data, $data);
        //     }
            
        //         $data = array(
        //             'pro_code' => $dropoff->pro_code,
        //             'region' => $dropoff->region,
        //             'province' => $dropoff->province,
        //             // 'municipality' => $dropoff->municipality,
        //             'targetVolume' => $dropoff->targetVolume,
        //             'actual_delivery' => '0',
        //             'percent' => '0'
        //         );
                
        //         array_push($table_data, $data);
        // }


        // dd($table_data);
        $table_data = collect($table_data);
        // return $table_data;

        
        return Datatables::of($table_data)->make(true);
    }
    

    public function get_delivery_provinces(Request $request){
        $provinces = \DB::connection('delivery_inspection_db')
            ->table('tbl_delivery_sum')
            ->where('region', $request->region)
            ->groupBy('province')
            ->orderBy('province', 'ASC')
            ->get();

        $return_str= '';
        foreach($provinces as $row){
            $return_str .= "<option value='$row->province'>$row->province</option>";
        }
        return $return_str;
    }

    // public function get_delivery_municipalities(Request $request){
    //     $municipalities = \DB::connection('delivery_inspection_db')
    //             ->table('tbl_delivery_sum')
    //             ->where('region', $request->region)
    //             ->where('province', $request->province)
    //             ->where('targetVolume', $request->targetVolume)
    //             // ->orderBy('municipality', 'ASC')
    //             // ->groupBy('municipality')
    //             ->get();

    //     $return_str= '';
    //     foreach($municipalities as $row){
    //             $return_str= "<option value='$row->municipality'>$row->municipality</option>";
    //     }
    //     return $return_str;
    // }
    
    public function get_delivery_month(Request $request){
        $months = \DB::connection('delivery_inspection_db')
                ->table('tbl_delivery_sum')
                ->select('targetMonthFrom', 'targetMonthTo') // Add the fields you need
                ->where('region', "LIKE",$request->region)
                ->where('province', "LIKE",$request->province)
                ->where('targetMonthFrom','!=','0000-00-00')
                ->where('targetMonthTo','!=','0000-00-00')
                ->orderBy('targetMonthFrom', 'ASC')
                ->groupBy('targetMonthFrom','targetMonthTo')
                ->get();
        
        $return_str= '';
        foreach($months as $row){
                $return_str .= "<option value='$row->targetMonthFrom - $row->targetMonthTo'>".date("F Y", strtotime($row->targetMonthTo))."</option>";
                // $formattedFrom = date("F Y", strtotime($row->targetMonthFrom));
                // $formattedTo = date("F Y", strtotime($row->targetMonthTo));
                // $optionValue = "$row->targetMonthFrom - $row->targetMonthTo";
                // $return_str .= "<option value='$optionValue'>$formattedFrom to $formattedTo</option>";
                
        }
        return $return_str;
    }
   
    // public function get_delivery_month(Request $request){
    //     $months = \DB::connection('delivery_inspection_db')
    //             ->table('tbl_delivery_sum')
    //             ->select('targetMonthFrom', 'targetMonthTo') // Add the fields you need
    //             ->where('region', "LIKE", $request->region)
    //             ->where('province', "LIKE", $request->province)
    //             ->orderBy('targetMonthFrom', 'ASC')
    //             ->groupBy('targetMonthFrom', 'targetMonthTo')
    //             ->get();
        
    //     $return_str = '';
    //     foreach ($months as $row) {
    //         $formattedFrom = date("F Y", strtotime($row->targetMonthFrom));
    //         $formattedTo = date("F Y", strtotime($row->targetMonthTo));
            
    //         // If targetMonthFrom and targetMonthTo are different, format the option accordingly
    //         if ($formattedFrom !== $formattedTo) {
    //             $optionText = "$formattedFrom to $formattedTo";
    //         } else {
    //             $optionText = $formattedFrom;
    //         }
            
    //         $optionValue = "$row->targetMonthFrom - $row->targetMonthTo";
    //         $return_str .= "<option value='$optionValue'>$optionText</option>";
    //     }
    //     return $return_str;
    // }
    

}

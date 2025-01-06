<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use Yajra\Datatables\Facades\Datatables;
use App\utility;

class PlantingCalendarController extends Controller
{
    public function home_ui(){
        $regionNames = array();
        $regionCodes = array();
        $currentSeason = DB::connection("ds2023")->table('rcef_reports.lib_season')
        ->select('season_code', 'season','season_year', DB::raw('max(season_id) as season_id'))
        ->groupBy('season_code')
        ->orderBy('season_id', 'desc')
        ->limit(1)
        ->get();

        $tryRaw = DB::table('information_schema.tables')
            ->select(DB::raw("LEFT(RIGHT(table_schema, 4), 2) as regions"))
            ->where('TABLE_SCHEMA', "LIKE", $currentSeason[0]->season_code.'_prv_%')
            ->groupBy('regions')
            ->get();
        foreach($tryRaw as $row){
            $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select(DB::raw('regionName as reg'), DB::raw('regCode as regC'))
                ->where('regCode', $row->regions)
                ->limit(1)
                ->get();
                if(count($regions)>0){
                array_push($regionNames, $regions[0]->reg);
                array_push($regionCodes, $regions[0]->regC);
                }
            
        }
        
            $seasons=array();
            $season = DB::connection("ds2023")->table('rcef_reports.lib_season')
            ->select('season_code', 'season','season_year')
            ->groupBy('season_code')
            ->orderBy('season_id', 'desc')
            ->get();
            foreach($season as $row){
                array_push($seasons, $row);
            }
            $varieties=array();
            $variety = DB::table('seed_seed.seed_characteristics')
            ->select('variety')
            ->groupBy('variety')
            ->get();
            foreach($variety as $row){
                array_push($varieties, $row);
            }

        // return $provinces;
        return view('PlantingCalendar.home',
         compact(
            'regionNames',
            'regionCodes',
            'seasons',
            'variety'
         ));
        
    }


    public function getProvinces(Request $request){
        $returns = array();

        $raw = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province', 'prv_code')
            ->where('regCode', $request->reg)
            ->groupBy('province')
            ->get();
            
    
        foreach($raw as $row){
            $exist = DB::table($request->ssn.'_prv_'.$row->prv_code.'.new_released')
                ->select(DB::raw('count(new_released_id) as count'))
                ->get();
            if($exist[0]->count > 0){
                array_push($returns, $row);
            }
        }
        return json_encode($returns);
    }

    public function getMunicipalities(Request $request){
        $exist = DB::table($request->ssn.'_prv_'.$request->prov.'.new_released')
        ->select('municipality', DB::raw("LEFT(prv_dropoff_id, 6) as muncode"))
        ->groupBy('municipality')
        ->get();  

        return json_encode($exist);
    }

    public function getSeason(Request $request){
        $season = DB::connection("ds2023")->table('rcef_reports.lib_season')
            ->select('season_code', 'season','season_year')
            ->groupBy('season_code')
            ->orderBy('season_id', 'desc')
            ->get();
    
        return json_encode($season);
    }
    

    public function getPlantingWeek(Request $request)
    {
        //Per Region
        if($request->reg!="default"&&$request->prv=="default"&&$request->mun=="default")
        {
            $results = array();
            $checker = array();
            $prvs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('prv_code')
            ->where('regCode',"LIKE", $request->reg)
            ->groupBy('prv_code')
            ->get();
    
            foreach($prvs as $row){
                array_push($checker, $row->prv_code);
                $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$row->prv_code)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        $raw=$check;
                        return json_encode($raw);
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$row->prv_code.'.new_released')
                            ->select(DB::raw("CONCAT(
                                        SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                        '/',
                                        CONCAT(
                                            CASE
                                                WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                                WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                                WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                                ELSE '4th'
                                            END
                                        )
                                    ) as formatted_planting_week"), 
                                    DB::raw("COUNT(planting_week) as farmers"),
                                    DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->groupBy('formatted_planting_week')
                            ->orderBy('formatted_planting_week')
                            ->get();
                        foreach($result as $row2){
                            array_push($results, $row2);
                        }
                    }
                }
                
                $groupedData = collect($results)->groupBy('formatted_planting_week');
                // return($groupedData);
            

                // Transform the grouped data into the desired format
                $final_result = $groupedData->map(function ($group) {
                    return [
                        'planting_week' => $group->first()->formatted_planting_week,
                        'farmers' => $group->sum('farmers'),
                        'sort' => $group->first()->sort
                    ];
                })->values();


                $final_result = $final_result->sortBy('sort')->values();
                return json_encode($final_result);

        }

        //Per Province
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default")
        {
            $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$request->prv)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        $raw=$check;
                        return json_encode($raw);
                    }else{
                        $result =DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                            ->select(DB::raw("CONCAT(
                                        SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                        '/',
                                        CONCAT(
                                            CASE
                                                WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                                WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                                WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                                ELSE '4th'
                                            END
                                        )
                                    ) as formatted_planting_week"), 
                                    DB::raw("COUNT(planting_week) as farmers"),
                                    DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->groupBy('formatted_planting_week')
                            ->orderBy('formatted_planting_week')
                            ->get();

                    }

                    $groupedData = collect($result)->groupBy('formatted_planting_week');
                // return($groupedData);
            

                // Transform the grouped data into the desired format
                $final_result = $groupedData->map(function ($group) {
                    return [
                        'planting_week' => $group->first()->formatted_planting_week,
                        'farmers' => $group->sum('farmers'),
                        'sort' => $group->first()->sort
                    ];
                })->values();


                $final_result = $final_result->sortBy('sort')->values();
                return json_encode($final_result);
        }

        //Per Municipality
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default")
        {
            $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$request->prv)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        $raw=$check;
                        return json_encode($raw);
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                            ->select(DB::raw("CONCAT(
                                        SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                        '/',
                                        CONCAT(
                                            CASE
                                                WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                                WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                                WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                                ELSE '4th'
                                            END
                                        )
                                    ) as formatted_planting_week"), 
                                    DB::raw("COUNT(planting_week) as farmers"),
                                    DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->where('prv_dropoff_id', "LIKE", $request->mun."%")
                            ->groupBy('formatted_planting_week')
                            ->orderBy('formatted_planting_week')
                            ->get();

                            $groupedData = collect($result)->groupBy('formatted_planting_week');

                            $final_result = $groupedData->map(function ($group) {
                                return [
                                    'planting_week' => $group->first()->formatted_planting_week,
                                    'farmers' => $group->sum('farmers'),
                                    'sort' => $group->first()->sort
                                ];
                            })->values();
            
            
                            $final_result = $final_result->sortBy('sort')->values();
                            return json_encode($final_result);
                    }
        }


        //All Regions, Provinces, Municipalities
        else
        {
            $results = array();
            $checker = array();
            $prvs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('prv_code')
            ->groupBy('prv_code')
            ->get();

        
                foreach($prvs as $row){
                    array_push($checker, $row->prv_code);
                    $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$row->prv_code)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        // array_push($checker, "not exist");
                        //do nothing
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$row->prv_code.'.new_released')
                            ->select(DB::raw("CONCAT(
                                        SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                        '/',
                                        CONCAT(
                                            CASE
                                                WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                                WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                                WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                                WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                                ELSE '4th'
                                            END
                                        )
                                    ) as formatted_planting_week"), 
                                    DB::raw("COUNT(planting_week) as farmers"),
                                    DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->groupBy('formatted_planting_week')
                            ->orderBy('formatted_planting_week')
                            ->get();
              
                        foreach($result as $row2){
                            array_push($results, $row2);
                        }
                    }

                }
             
                // Group the data by planting_week
                $groupedData = collect($results)->groupBy('formatted_planting_week');

                // Transform the grouped data into the desired format
                $final_result = $groupedData->map(function ($group) {
                    return [
                        'planting_week' => $group->first()->formatted_planting_week,
                        'farmers' => $group->sum('farmers'),
                        'sort' => $group->first()->sort
                    ];
                })->values();


                $final_result = $final_result->sortBy('sort')->values();
                return json_encode($final_result);
        }
    }


    public function getVarietyYield(Request $request){
        
        //Per Region
        if($request->reg!="default"&&$request->prv=="default"&&$request->mun=="default")
        {
            $results = array();
            $checker = array();
            $prvs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('prv_code')
            ->where('regCode',"LIKE", $request->reg)
            ->groupBy('prv_code')
            ->get();
    
            foreach($prvs as $row){
                array_push($checker, $row->prv_code);
                $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$row->prv_code)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        $raw=$check;
                        return json_encode($raw);
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$row->prv_code.'.new_released')
                        ->select(DB::raw("
                            CONCAT(
                                SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                '/',
                                CONCAT(
                                CASE
                                    WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                    WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                    WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                    ELSE '4th'
                                END
                                )
                            ) as formatted_planting_week,
                            ROUND(AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000), 2) as vty_yield"),
                            DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->where('seed_variety',"LIKE", "%".$request->vty."%")
                        ->groupBy('formatted_planting_week')
                        ->havingRaw('AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000) BETWEEN 1 AND 13')
                        ->orderBy('formatted_planting_week')
                        ->get();

                        foreach($result as $row2){
                            if($row2->vty_yield >= 1 && $row2->vty_yield <= 13)
                                array_push($results, $row2);
                        }
                        
                    }
            }
                
                $groupedData = collect($results)->groupBy('formatted_planting_week');
                // return($groupedData);

                // Transform the grouped data into the desired format
                $final_result = $groupedData->map(function ($group) {
                    return [
                        'planting_week' => $group->first()->formatted_planting_week,
                        'vty_yield' => ROUND($group->sum('vty_yield') / count($group), 2),
                        'sort' => $group->first()->sort
                    ];
                })->values();

                $final_result = $final_result->sortBy('sort')->values();
                return json_encode($final_result);

        }

        //Per Province
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default")
        {
            $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$request->prv)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        $raw= $check;
                        return json_encode($raw);
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                        ->select(DB::raw("
                            CONCAT(
                                SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                '/',
                                CONCAT(
                                CASE
                                    WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                    WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                    WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                    ELSE '4th'
                                END
                                )
                            ) as formatted_planting_week,
                            ROUND(AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000), 2) as vty_yield"),
                            DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->where('seed_variety',"LIKE", "%".$request->vty."%")
                        ->groupBy('formatted_planting_week')
                        ->havingRaw('AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000) BETWEEN 1 AND 13')
                        ->orderBy('formatted_planting_week')
                        ->get();

                    }
                    
                    $groupedData = collect($result)->groupBy('formatted_planting_week');
                // return($groupedData);
            

                // Transform the grouped data into the desired format
                $final_result = $groupedData->map(function ($group) {
                    return [
                        'planting_week' => $group->first()->formatted_planting_week,
                        'vty_yield' => ROUND($group->sum('vty_yield') / count($group), 2),
                        'sort' => $group->first()->sort
                    ];
                })->values();


                $final_result = $final_result->sortBy('sort')->values();
                return json_encode($final_result);
        }

        //Per Municipality
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default")
        {
            $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$request->prv)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        $raw=$check;
                        return json_encode($raw);
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                        ->select(DB::raw("
                            CONCAT(
                                SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                '/',
                                CONCAT(
                                CASE
                                    WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                    WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                    WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                    ELSE '4th'
                                END
                                )
                            ) as formatted_planting_week,
                            ROUND(AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000), 2) as vty_yield"),
                            DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->where('prv_dropoff_id', "LIKE", $request->mun."%")
                            ->where('seed_variety',"LIKE", "%".$request->vty."%")
                        ->groupBy('formatted_planting_week')
                        ->havingRaw('AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000) BETWEEN 1 AND 13')
                        ->orderBy('formatted_planting_week')
                        ->get();

                            $groupedData = collect($result)->groupBy('formatted_planting_week');

                            $final_result = $groupedData->map(function ($group) {
                                return [
                                    'planting_week' => $group->first()->formatted_planting_week,
                                    'vty_yield' => ROUND($group->sum('vty_yield') / count($group), 2),
                                    'sort' => $group->first()->sort
                                ];
                            })->values();
            
            
                            $final_result = $final_result->sortBy('sort')->values();
                            return json_encode($final_result);
                    }
        }


        //All Regions, Provinces, Municipalities
        else
        {
            $results = array();
            $checker = array();
            $prvs = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('prv_code')
            ->groupBy('prv_code')
            ->get();

        
                foreach($prvs as $row){
                    array_push($checker, $row->prv_code);
                    $check = DB::table('information_schema.tables')
                        ->select('table_schema')
                        ->where('TABLE_SCHEMA', "LIKE",$request->ssn.'_prv_'.$row->prv_code)
                        ->groupBy('tables.TABLE_SCHEMA')
                        ->get();

                    if(!$check){
                        // array_push($checker, "not exist");
                        //do nothing
                    }else{
                        $result = DB::table($request->ssn.'_prv_'.$row->prv_code.'.new_released')
                        ->select(DB::raw("
                            CONCAT(
                                SUBSTR(DATE_FORMAT(STR_TO_DATE(planting_week, '%m/%u'), '%b'), 1, 3),
                                '/',
                                CONCAT(
                                CASE
                                    WHEN RIGHT(planting_week, 2) = '00' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '01' THEN '1st'
                                    WHEN RIGHT(planting_week, 2) = '02' THEN '2nd'
                                    WHEN RIGHT(planting_week, 2) = '03' THEN '3rd'
                                    WHEN RIGHT(planting_week, 2) = '04' THEN '4th'
                                    ELSE '4th'
                                END
                                )
                            ) as formatted_planting_week,
                            ROUND(AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000), 2) as vty_yield"),
                            DB::raw("planting_week as sort"))
                            ->where(DB::raw('planting_week NOT IN ("NULL", "", NULL, "0/0")'))
                            ->where('seed_variety',"LIKE", "%".$request->vty."%")
                        ->groupBy('formatted_planting_week')
                        ->havingRaw('AVG(((yield_no_of_bags_ls * yield_wt_per_bag) / yield_area_harvested_ls) / 1000) BETWEEN 1 AND 13')
                        ->orderBy('formatted_planting_week')
                        ->get();

                        foreach($result as $row2){
                                array_push($results, $row2);
                        }
                    }

                }
             
                // Group the data by planting_week
                $groupedData = collect($results)->groupBy('formatted_planting_week');

                // Transform the grouped data into the desired format
                $final_result = $groupedData->map(function ($group) {
                    return [
                        'planting_week' => $group->first()->formatted_planting_week,
                        'vty_yield' => ROUND($group->sum('vty_yield') / count($group), 2),
                        'sort' => $group->first()->sort
                    ];
                })->values();


                $final_result = $final_result->sortBy('sort')->values();
                return json_encode($final_result);
        }
    }
    
    
}

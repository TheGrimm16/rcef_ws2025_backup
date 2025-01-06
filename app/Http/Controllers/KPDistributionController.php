<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use Yajra\Datatables\Facades\Datatables;
use App\utility;

class KPDistributionController extends Controller
{
        public function home_ui(){
        $regionNames = array();
        $regionCodes = array();
        $regionsArray = array();
        $munArray = array();
        $provArray = array();
        $seasons = array();

        $getSeasons = DB::table("kp_distribution.kp_distribution_app")
        ->select('season')
        ->groupBy('season')
        ->get();

        $sortedData = collect($getSeasons)->sortByDesc(function ($item) {
            // Extract the year and season type (DS/WS)
            preg_match('/(\d+)$/', $item->season, $matches);
            $year = $matches[0];
            $seasonType = substr($item->season, 0, -strlen($year));
        
            // Assigning a custom sorting weight based on season type
            $weight = ($seasonType === 'DS') ? 0 : 1;
        
            // Sorting first by year in descending order and then by season type weight
            return [$year, $weight];
        })->toArray();
        
        foreach ($sortedData as $row){
            $type = '';
            $season = $row->season;
            $seasonCode = strtolower($season);
            if((substr($seasonCode, 0,2)=='ds')){
                $type = 'Dry Season';

            }else if((substr($seasonCode, 0,2)=='ws')){
                $type = 'Wet Season';
            }
            $seasonYear = substr($seasonCode, 2,4);
            $seasonDetails = [
                "season_code" => $seasonCode,
                "season" => $type,
                "season_year" => $seasonYear
            ];
            
            array_push($seasons,$seasonDetails);
        }
        // dd($seasons[0]['season_code']);
        $getLocations = DB::table("kp_distribution.kp_distribution_app")
        ->select('location')
        ->where('season','LIKE', $seasons[0]['season_code'])
        ->groupBy('location')
        ->get();

        // dd($getLocations);
        
        foreach($getLocations as $row){
            $substring = explode(', ', $row->location);
            array_push($munArray,$substring[0].', '.$substring[1]);
            array_push($provArray,$substring[1]);
            array_push($regionsArray,$substring[2]);
        }
        // dd($provArray,$munArray);
        $regionsArray = array_unique($regionsArray);
        $provArray = array_unique($provArray);
        $munArray = array_unique($munArray);
        // dd($provArray,$munArray);
        $countProvinces = count($provArray);
        $countMunicipalities = count($munArray);
        // dd($countProvinces, $countMunicipalities);
        
        foreach($regionsArray as $reg){
            $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                ->select('regCode')
                                ->where('regionName', $reg)
                                ->limit(1)
                                ->get();
                            array_push($regionNames, $reg);
                            array_push($regionCodes, $regions[0]->regCode);
        }

        // dd($regionNames,$regionCodes);

        // $currentSeason = DB::connection("ds2023")->table('rcef_reports.lib_season')
        // ->select('season_code', 'season','season_year', DB::raw('max(season_id) as season_id'))
        // ->groupBy('season_code')
        // ->orderBy('season_id', 'desc')
        // ->limit(1)
        // ->get();

        // $tryRaw = DB::table('information_schema.tables')
        //     ->select(DB::raw("LEFT(RIGHT(table_schema, 4), 4) as regions"))
        //     ->where('TABLE_SCHEMA', "LIKE", $currentSeason[0]->season_code.'_prv_%')
        //     ->groupBy('regions')
        //     ->get();
        
        //     foreach($tryRaw as $row){
        //         if(is_numeric($row->regions))
        //         {
        //             $newReleasedCount = DB::table($currentSeason[0]->season_code."_prv_".$row->regions.'.new_released')->count();

        //         $regionCode = substr($row->regions, 0, 2);
                
        //         $KPKits = DB::table($currentSeason[0]->season_code."_prv_".$row->regions.'.new_released')
        //                         ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
        //                         ->where('kp_kit_count', '!=', 0)
        //                         ->get();
        //                 $total_KPKits = $KPKits[0]->total_kp_kit_count;

        //                 if($newReleasedCount > 0 && $total_KPKits!=='null' && $total_KPKits!=0){
        //                     $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        //                     ->select(DB::raw('regionName as reg'), DB::raw('regCode as regC'))
        //                     ->where('regCode', $regionCode)
        //                     ->limit(1)
        //                     ->get();
        //                 if($regions[0]->reg != 'Programmer Region')
        //                 {
        //                 array_push($regionNames, $regions[0]->reg);
        //                 array_push($regionCodes, $regions[0]->regC);
        //                 }
                        
        //                 }      
        //     }
            
        // }

        // $seasons=array();
        // $season = DB::connection("ds2023")->table('rcef_reports.lib_season')
        // ->select('season_code', 'season','season_year')
        // ->groupBy('season_code')
        // ->orderBy('season_id', 'desc')
        // ->get();
        // foreach($season as $row){
        //     array_push($seasons, $row);
        // }
        
        // $allProvinces = DB::table('information_schema.tables')
        // ->select(DB::raw("table_schema as allProv"))
        // ->where('TABLE_SCHEMA', "LIKE", $currentSeason[0]->season_code.'_prv_%')
        // ->groupBy('allProv')
        // ->get();
        
        // $nonEmptyProvinces = array();

        // foreach($allProvinces as $province) {
        //     $databaseName = $province->allProv;
        //     $newReleasedCount = DB::table($databaseName.'.new_released')->count();
        //     $KPKits = DB::table($databaseName.'.new_released')
        //                         ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
        //                         ->where('kp_kit_count', '!=', 0)
        //                         ->get();
        //                 $total_KPKits = $KPKits[0]->total_kp_kit_count;
            
        
        //     if($newReleasedCount > 0 && $total_KPKits!='null' && $total_KPKits!=0) {
        //         $nonEmptyProvinces[] = $province->allProv;
        //     }
        // }
        // $countProvinces = count($nonEmptyProvinces);

        // $munCount = array();
        // $countMunicipalities = 0;

        // foreach($nonEmptyProvinces as $row2){
        //     $countMun = DB::table($row2.'.new_released')
        //         ->select('municipality')
        //         ->groupBy('municipality')
        //         ->get();
        //     $munCount[] = $countMun;
        //     $countMunicipalities += count($countMun);
        // }

        // dd($seasons);
        
        return view('KPDistribution.home',
         compact(
            'regionNames',
            'regionCodes',
            'seasons',
            'countProvinces',
            'countMunicipalities'
         ));
        
    }


    public function getKPRegions(Request $request)
    {
        
        $regionsArray = array();
        $regionNames = array();
        $getLocations = DB::table("kp_distribution.kp_distribution_app")
        ->select('location')
        ->where('season','LIKE', $request->ssn)
        ->groupBy('location')
        ->get();
        // dd(strlen($getLocations[0]->location));
        
        foreach($getLocations as $row){
            $substring = explode(', ', $row->location);
            array_push($regionsArray,$substring[2]);
        }
        // dd($regionsArray);

        $regionsArray = array_unique($regionsArray);
        // dd($regionsArray);
        foreach($regionsArray as $reg){
            $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                ->select('regionName','regCode')
                                ->where('regionName', $reg)
                                ->limit(1)
                                ->get();
                                // dd($regions);
                            array_push($regionNames,$regions[0]);
        }
        return $regionNames;
    }

    public function getKPRegions_old(Request $request)
    {

        $regionNames = array();
        $regionCodes = array();
        $tryRaw = DB::table('information_schema.tables')
            ->select(DB::raw("RIGHT(table_schema, 4) as regions"))
            ->where('TABLE_SCHEMA', "LIKE", $request->ssn.'_prv_%')
            ->groupBy('regions')
            ->get();
        
            foreach($tryRaw as $row)
            {
                if(is_numeric($row->regions))
            {
                $newReleasedCount = DB::table($request->ssn."_prv_".$row->regions.'.new_released')->count();
    
                    $regionCode = substr($row->regions, 0, 2);
                    
                    $KPKits = DB::table($request->ssn."_prv_".$row->regions.'.new_released')
                                    ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
                                    ->where('kp_kit_count', '!=', 0)
                                    ->get();
                            $total_KPKits = $KPKits[0]->total_kp_kit_count;
    
                            if($newReleasedCount > 0 && $total_KPKits!=='null' && $total_KPKits!=0){
                                $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                                ->select(DB::raw('regionName as reg'), DB::raw('regCode as regC'))
                                ->where('regCode', $regionCode)
                                ->where('regCode', '!=','99')
                                ->limit(1)
                                ->get();

                            
                            array_push($regionNames, array(
                                "regN" => $regions[0]->reg,
                                "regC" => $regions[0]->regC
                            ));
                            }
            }
                
            }
            // $regionCodes = array_unique($regionCodes);
            // $regionNames = array_unique($regionNames);
            $unique_array = [];
            foreach($regionNames as $element) {
                $hash = $element["regC"];
                $unique_array[$hash] = $element;
            }
            $result = array_values($unique_array);

            return $result;
    }

    public function getKPProvinces(Request $request){
        
        $locations = array();
        $provinceNames = array();
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province', 'prv_code')
            ->where('regCode', $request->reg)
            ->groupBy('province')
            ->orderBy('province')
            ->get();

        foreach($provinces as $province){
            $getLocations = DB::table("kp_distribution.kp_distribution_app")
            ->select('location')
            ->where('location','LIKE','%'.$province->province.'%')
            ->where('season','LIKE', $request->ssn)
            ->groupBy('location')
            ->get();
            if($getLocations){
                array_push($locations,$getLocations);
            }
        }

        foreach($locations as $row){
            foreach($row as $row2){
                $substring = explode(', ', $row2->location);
                array_push($provinceNames,$substring[1]);
            }
        }
        $provinceNames = array_unique($provinceNames);
        $finalProv = array();
        foreach($provinceNames as $prov){
            $raw = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('province', 'prv_code')
                ->where('province', $prov)
                ->groupBy('province')
                ->get();
            foreach($raw as $prv)
            {
                array_push($finalProv,$prv);    
            }
            
        }
        
        return json_encode($finalProv);
        
    }


    public function getKPProvinces_old(Request $request){
        $returns = array();
        $raw = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province', 'prv_code')
            ->where('regCode', $request->reg)
            ->groupBy('province')
            ->get();
        
        foreach($raw as $row){

            $exists = DB::table("information_schema.TABLES")
                ->select(DB::raw("count('table_schema') as count"))
                ->where("TABLE_SCHEMA", "LIKE", $request->ssn."_prv_".$row->prv_code)
                ->where("TABLE_NAME", "new_released")
                ->get();
            

            if ($exists[0]->count > 0) {
                $newReleasedCount = DB::table($request->ssn."_prv_".$row->prv_code.'.new_released')->count();
                $KPKits = DB::table($request->ssn."_prv_".$row->prv_code.'.new_released')
                                ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
                                ->where('kp_kit_count', '!=', 0)
                                ->get();
                        $total_KPKits = $KPKits[0]->total_kp_kit_count;
                        if($newReleasedCount > 0 && $total_KPKits!='null' && $total_KPKits!=0){
                            array_push($returns, $row);
                        }
            }
        }
        
        return json_encode($returns);
    }
    

    public function getKPMunicipalities(Request $request){
        // dd($request->prov);
        $locations = array();
        $municipalityNames = array();
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province','municipality', 'prv')
            ->where('prv_code', $request->prov)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();

        // dd($municipalities);

        foreach($municipalities as $municipality){
            $getLocations = DB::table("kp_distribution.kp_distribution_app")
            ->select('location')
            ->where('location','LIKE','%'.$municipality->municipality.'%'.$municipality->province.'%')
            ->where('season','LIKE', $request->ssn)
            ->groupBy('location')
            ->get();
            if($getLocations){
                array_push($locations,$getLocations);
            }
        }
        // dd($locations);
        foreach($locations as $row){
            foreach($row as $row2){
                $substring = explode(', ', $row2->location);
                array_push($municipalityNames,$substring[0]);
            }
        }
        $municipalityNames = array_unique($municipalityNames);
        // dd($municipalityNames);
        $finalMuni = array();
        foreach($municipalityNames as $muni){
            $raw = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select('municipality', 'prv')
                ->where('municipality', $muni)
                ->where('prv_code', $request->prov)
                ->groupBy('municipality')
                ->get();
            foreach($raw as $mun)
            {
                array_push($finalMuni,$mun);    
            }
            
        }
        // dd($finalMuni);
        return json_encode($finalMuni);
    }

    public function getKPMunicipalitie_old(sRequest $request){
        $exist = DB::table($request->ssn.'_prv_'.$request->prov.'.new_released')
        ->select('municipality', DB::raw("LEFT(prv_dropoff_id, 6) as muncode"))
        ->where('kp_kit_count', '!=', 0)
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

    

    public function getKPDistribution(Request $request)
    {
        $KPKits_distributed = array();
        $farmer_beneficiaries = array();
        $allBday = array();
        $Male18to29 = array();
        $Male30to59 = array();
        $Male60andAbove = array();
        $Female18to29 = array();
        $Female30to59 = array();
        $Female60andAbove = array();
        $MaleNoBday = array();
        $FemaleNoBday = array();
        $check_season = (substr($request->ssn, -4));
        $WSorDS_checker = (substr($request->ssn, 0,2));


        $allProvinces = DB::table('information_schema.tables')
        ->select(DB::raw("table_schema as allProv"))
        ->where('TABLE_SCHEMA', "LIKE", $request->ssn.'_prv_%')
        ->groupBy('allProv')
        ->get();
        
        $nonEmptyProvinces = array();

        foreach($allProvinces as $province) {
            $databaseName = $province->allProv;
            $newReleasedCount = DB::table($databaseName.'.new_released')->count();
            $KPKits = DB::table($databaseName.'.new_released')
                                ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
                                ->where('kp_kit_count', '!=', 0)
                                ->get();
                        $total_KPKits = $KPKits[0]->total_kp_kit_count;
            
        
            if($newReleasedCount > 0 && $total_KPKits!='null' && $total_KPKits!=0) {
                $nonEmptyProvinces[] = $province->allProv;
            }
        }
        $countProvinces = count($nonEmptyProvinces);

        $munCount = array();
        $countMunicipalities = 0;

        foreach($nonEmptyProvinces as $row2){
            $countMun = DB::table($row2.'.new_released')
                ->select('municipality')
                ->groupBy('municipality')
                ->get();
            $munCount[] = $countMun;
            $countMunicipalities += count($countMun);
        }

        
        //Per Region
        if($request->reg!="default"&&$request->prv=="default"&&$request->mun=="default"&&$check_season>=2023)
        {
            $prvs = array();
            $raw = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province', 'prv_code')
            ->where('regCode', $request->reg)
            ->groupBy('province')
            ->get();
    
        foreach($raw as $row){

            $exists = DB::table("information_schema.TABLES")
                ->select(DB::raw("count('table_schema') as count"))
                ->where("TABLE_SCHEMA", "LIKE", $request->ssn."_prv_".$row->prv_code)
                ->where("TABLE_NAME", "new_released")
                ->get();

            if ($exists[0]->count > 0) {
                $newReleasedCount = DB::table($request->ssn."_prv_".$row->prv_code.'.new_released')->count();
                $KPKits = DB::table($request->ssn."_prv_".$row->prv_code.'.new_released')
                                ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
                                ->where('kp_kit_count', '!=', 0)
                                ->get();
                        $total_KPKits = $KPKits[0]->total_kp_kit_count;
                        if($newReleasedCount > 0 && $total_KPKits!='null' && $total_KPKits!=0){
                            array_push($prvs, $row);
                        }
            }
        }

            foreach($prvs as $row2){

                        if($request->ssn=='ds2023'){
                            $KPKits = DB::table($request->ssn.'_prv_'.$row2->prv_code.'.new_released')
                            ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                            ->join($request->ssn.'_prv_'.$row2->prv_code.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                            ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                            ->get();
                        $farmers = DB::table($request->ssn.'_prv_'.$row2->prv_code.'.new_released')
                            ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                            ->join($request->ssn.'_prv_'.$row2->prv_code.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                            ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                            ->get();
                        $total_KPKits = $KPKits[0]->total_kp_kit_count;
                        array_push($KPKits_distributed, $total_KPKits);
                        $total_farmers = $farmers[0]->farmer_count;
                        array_push($farmer_beneficiaries, $total_farmers);

                        $getbday = DB::table($request->ssn.'_prv_'.$row2->prv_code.'.farmer_information_final')
                        ->select('farmer_information_final.rsbsa_control_no', 'farmer_information_final.rcef_id',
                            'farmer_information_final.lastName',
                            'farmer_information_final.firstName',
                            'farmer_information_final.midName',
                            'farmer_information_final.extName',
                            'farmer_information_final.sex', DB::raw("
                                COALESCE(
                                    case
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                                    else '00/00/0000' end,
                                '00/00/0000') as birthdate"),
                                'farmer_information_final.province',
                                'farmer_information_final.municipality',
                                DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                                'new_released.kp_kit_count')
                        ->join($request->ssn.'_prv_'.$row2->prv_code.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                        ->where('new_released.kp_kit_count', '!=', 0)
                        ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                        ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                        ->get();
                        }
                        else{
                            $KPKits = DB::table($request->ssn.'_prv_'.$row2->prv_code.'.new_released')
                                ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                                ->join($request->ssn.'_prv_'.$row2->prv_code.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                ->where('new_released.kp_kit_count', '!=', 0)
                                ->where('new_released.birthdate', '!=', '00/00/0000')
                                ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                                ->get();
                            $farmers = DB::table($request->ssn.'_prv_'.$row2->prv_code.'.new_released')
                            ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                            ->join($request->ssn.'_prv_'.$row2->prv_code.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                            ->where('new_released.birthdate', '!=', '00/00/0000')
                            ->get();
                            $total_KPKits = $KPKits[0]->total_kp_kit_count;
                            array_push($KPKits_distributed, $total_KPKits);
                            $total_farmers = $farmers[0]->farmer_count;
                            array_push($farmer_beneficiaries, $total_farmers);

                            $getbday = DB::table($request->ssn.'_prv_'.$row2->prv_code.'.farmer_information_final')
                            ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                                    'farmer_information_final.lastName',
                                    'farmer_information_final.firstName',
                                    'farmer_information_final.midName',
                                    'farmer_information_final.extName',
                                    'farmer_information_final.sex', DB::raw("
                                    COALESCE(
                                        case
                                        when new_released.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                                        when new_released.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                                        when new_released.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                                        when new_released.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                                        else '00/00/0000' end,
                                    '00/00/0000') as birthdate"),
                                    'farmer_information_final.province',
                                    'farmer_information_final.municipality',
                                    DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                                    'new_released.kp_kit_count')
                        ->join($request->ssn.'_prv_'.$row2->prv_code.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                        ->where('new_released.kp_kit_count', '!=', 0)
                        ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                        ->where('new_released.birthdate', '!=', '00/00/0000')
                        ->get();
                        }
                        
                        foreach ($getbday as $key => $value) {
                            $birthdate = $value->birthdate;
                            if ($birthdate == '00/00/0000') {
                                $age = '00';
                            } else {
                                $age = date_diff(date_create($birthdate), date_create('today'))->y;
                            }
                            $getbday[$key]->age = $age;
                        }
                        
                        $allBday = array_merge($allBday, $getbday);
                        

            }   
            
            foreach ($allBday as $element) {
                $sex = strtolower($element->sex);
                if (substr($sex, 0, 1) === 'm') {
                    if ($element->age == '00'||$element->age === null){
                        $MaleNoBday[] = $element;
                    }
                    else if ($element->age <= 29) {
                        $Male18to29[] = $element;
                    } else if ($element->age >= 30 && $element->age <= 59) {
                        $Male30to59[] = $element;
                    } else {
                        $Male60andAbove[] = $element;
                    }
                } else {
                    if ($element->age == '00'||$element->age === null){
                        $FemaleNoBday[] = $element;
                    }
                    else if ($element->age <= 29) {
                        $Female18to29[] = $element;
                    } else if ($element->age >= 30 && $element->age <= 59) {
                        $Female30to59[] = $element;
                    } else {
                        $Female60andAbove[] = $element;
                    }
                }
            }
            

            

            $male18to29Count = count($Male18to29);
            $male30to59Count = count($Male30to59);
            $male60andAboveCount = count($Male60andAbove);
            $female18to29Count = count($Female18to29);
            $female30to59Count = count($Female30to59);
            $female60andAboveCount = count($Female60andAbove);
            $malenoBdayCount = count($MaleNoBday);
            $femalenoBdayCount = count($FemaleNoBday);
            $totalmale = $male18to29Count + $male30to59Count + $male60andAboveCount;
            $totalfemale = $female18to29Count + $female30to59Count + $female60andAboveCount;
            $sum_KPKits_distributed = array_sum($KPKits_distributed);
            $sum_farmer_beneficiaries = array_sum($farmer_beneficiaries);

        
        }

        //Per Province
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default"&&$check_season>=2023)
        {
                    
                        if($request->ssn=='ds2023'){
                            $KPKits = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                            ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                            ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                            ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                            ->get();

                            $farmers = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                            ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                            ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                            ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                            ->get();
                        
                            $total_KPKits = $KPKits[0]->total_kp_kit_count;
                            array_push($KPKits_distributed, $total_KPKits);
                            $total_farmers = $farmers[0]->farmer_count;
                            array_push($farmer_beneficiaries, $total_farmers);

                            $allBday = DB::table($request->ssn.'_prv_'.$request->prv.'.farmer_information_final')
                            ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                            'farmer_information_final.lastName',
                            'farmer_information_final.firstName',
                            'farmer_information_final.midName',
                            'farmer_information_final.extName',
                            'farmer_information_final.sex', DB::raw("
                                COALESCE(
                                    case
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                                    else '00/00/0000' end,
                                '00/00/0000') as birthdate"),
                                'farmer_information_final.province',
                                'farmer_information_final.municipality',
                                DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                                'new_released.kp_kit_count')
                            ->join($request->ssn.'_prv_'.$request->prv.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                            ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                            ->get();
                            }
                            else{
                                $KPKits = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                                ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                                ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                ->where('new_released.kp_kit_count', '!=', 0)
                                ->where('new_released.birthdate', '!=', '00/00/0000')
                                ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                                ->get();
                                $farmers = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                                ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                                ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                ->where('new_released.kp_kit_count', '!=', 0)
                                ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                                ->where('new_released.birthdate', '!=', '00/00/0000')
                                ->get();


                                $total_KPKits = $KPKits[0]->total_kp_kit_count;
                                array_push($KPKits_distributed, $total_KPKits);
                                $total_farmers = $farmers[0]->farmer_count;
                                array_push($farmer_beneficiaries, $total_farmers);

                                $allBday = DB::table($request->ssn.'_prv_'.$request->prv.'.farmer_information_final')
                            ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                            'farmer_information_final.lastName',
                            'farmer_information_final.firstName',
                            'farmer_information_final.midName',
                            'farmer_information_final.extName',
                            'farmer_information_final.sex', DB::raw("
                            COALESCE(
                                case
                                when new_released.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                                when new_released.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                                when new_released.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                                when new_released.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                                else '00/00/0000' end,
                            '00/00/0000') as birthdate"),
                            'farmer_information_final.province',
                            'farmer_information_final.municipality',
                            DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                            'new_released.kp_kit_count')
                            ->join($request->ssn.'_prv_'.$request->prv.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                            ->where('new_released.birthdate', '!=', '00/00/0000')
                            ->get();
                            }
                    
                        foreach ($allBday as $key => $value) {
                            $birthdate = $value->birthdate;
                            if ($birthdate == '00/00/0000') {
                                $age = '00';
                            } else {
                                $age = date_diff(date_create($birthdate), date_create('today'))->y;
                            }
                            $allBday[$key]->age = $age;
                        }
                    

                        foreach ($allBday as $element) {
                            $sex = strtolower($element->sex);
                            if (substr($sex, 0, 1) === 'm') {
                                if ($element->age == '00'||$element->age === null){
                                    $MaleNoBday[] = $element;
                                }
                                else if ($element->age <= 29) {
                                    $Male18to29[] = $element;
                                } else if ($element->age >= 30 && $element->age <= 59) {
                                    $Male30to59[] = $element;
                                } else {
                                    $Male60andAbove[] = $element;
                                }
                            } else {
                                if ($element->age == '00'||$element->age === null){
                                    $FemaleNoBday[] = $element;
                                }
                                else if ($element->age <= 29) {
                                    $Female18to29[] = $element;
                                } else if ($element->age >= 30 && $element->age <= 59) {
                                    $Female30to59[] = $element;
                                } else {
                                    $Female60andAbove[] = $element;
                                }
                            }
                        }
                        
                        $male18to29Count = count($Male18to29);
                        $male30to59Count = count($Male30to59);
                        $male60andAboveCount = count($Male60andAbove);
                        $female18to29Count = count($Female18to29);
                        $female30to59Count = count($Female30to59);
                        $female60andAboveCount = count($Female60andAbove);
                        $malenoBdayCount = count($MaleNoBday);
                        $femalenoBdayCount = count($FemaleNoBday);
                        $totalmale = $male18to29Count + $male30to59Count + $male60andAboveCount;
                        $totalfemale = $female18to29Count + $female30to59Count + $female60andAboveCount;
                        $sum_KPKits_distributed = array_sum($KPKits_distributed);
                        $sum_farmer_beneficiaries = array_sum($farmer_beneficiaries);

                        

        }

        //Per Municipality
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default"&&$check_season>=2023)
        {
            

                        if($request->ssn=='ds2023'){
                            $KPKits = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                                        ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                                        ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                        ->where('new_released.kp_kit_count', '!=', 0)
                                        ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                                        ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                                        ->where('prv_dropoff_id',"LIKE", $request->mun."%")
                                        ->get();
                            $farmers = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                                        ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                                        ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                        ->where('new_released.kp_kit_count', '!=', 0)
                                        ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                                        ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                                        ->where('new_released.prv_dropoff_id',"LIKE", $request->mun."%")
                                        ->get();

                                $total_KPKits = $KPKits[0]->total_kp_kit_count;
                                $total_farmers = $farmers[0]->farmer_count;

                            $allBday = DB::table($request->ssn.'_prv_'.$request->prv.'.farmer_information_final')
                            ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                            'farmer_information_final.lastName',
                            'farmer_information_final.firstName',
                            'farmer_information_final.midName',
                            'farmer_information_final.extName',
                            'farmer_information_final.sex', DB::raw("
                                COALESCE(
                                    case
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                                    when farmer_information_final.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                                    else '00/00/0000' end,
                                '00/00/0000') as birthdate"),
                                'farmer_information_final.province',
                                'farmer_information_final.municipality',
                                DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                                'new_released.kp_kit_count')
                            ->join($request->ssn.'_prv_'.$request->prv.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                            ->where('new_released.prv_dropoff_id',"LIKE", $request->mun."%")
                            ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                            ->get();
                            }
                            else{
                                $KPKits = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                                        ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                                        ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                        ->where('new_released.kp_kit_count', '!=', 0)
                                        ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                                        ->where('new_released.birthdate', '!=', '00/00/0000')
                                        ->where('prv_dropoff_id',"LIKE", $request->mun."%")
                                        ->get();

                                $farmers = DB::table($request->ssn.'_prv_'.$request->prv.'.new_released')
                                        ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                                        ->join($request->ssn.'_prv_'.$request->prv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                                        ->where('new_released.kp_kit_count', '!=', 0)
                                        ->where('new_released.birthdate', '!=', '00/00/0000')
                                        ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                                        ->where('new_released.prv_dropoff_id',"LIKE", $request->mun."%")
                                        ->get();


                                $total_KPKits = $KPKits[0]->total_kp_kit_count;
                                $total_farmers = $farmers[0]->farmer_count;

                                $allBday = DB::table($request->ssn.'_prv_'.$request->prv.'.farmer_information_final')
                            ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                            'farmer_information_final.lastName',
                            'farmer_information_final.firstName',
                            'farmer_information_final.midName',
                            'farmer_information_final.extName',
                            'farmer_information_final.sex', DB::raw("
                            COALESCE(
                                case
                                when new_released.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                                when new_released.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                                when new_released.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                                when new_released.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                                else '00/00/0000' end,
                            '00/00/0000') as birthdate"),
                            'farmer_information_final.province',
                            'farmer_information_final.municipality',
                            DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                            'new_released.kp_kit_count')
                            ->join($request->ssn.'_prv_'.$request->prv.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where('new_released.prv_dropoff_id',"LIKE", $request->mun."%")
                            ->where('new_released.birthdate', '!=', '00/00/0000')
                            ->get();
                            }
                    
                        foreach ($allBday as $key => $value) {
                            $birthdate = $value->birthdate;
                            if ($birthdate == '00/00/0000') {
                                $age = '00';
                            } else {
                                $age = date_diff(date_create($birthdate), date_create('today'))->y;
                            }
                            $allBday[$key]->age = $age;
                        }

                        array_push($KPKits_distributed, $total_KPKits);
                        array_push($farmer_beneficiaries, $total_farmers);
                        

                        foreach ($allBday as $element) {
                            $sex = strtolower($element->sex);
                            if (substr($sex, 0, 1) === 'm') {
                                if ($element->age == '00'||$element->age === null){
                                    $MaleNoBday[] = $element;
                                }
                                else if ($element->age <= 29) {
                                    $Male18to29[] = $element;
                                } else if ($element->age >= 30 && $element->age <= 59) {
                                    $Male30to59[] = $element;
                                } else {
                                    $Male60andAbove[] = $element;
                                }
                            } else {
                                if ($element->age == '00'||$element->age === null){
                                    $FemaleNoBday[] = $element;
                                }
                                else if ($element->age <= 29) {
                                    $Female18to29[] = $element;
                                } else if ($element->age >= 30 && $element->age <= 59) {
                                    $Female30to59[] = $element;
                                } else {
                                    $Female60andAbove[] = $element;
                                }
                            }
                        }
                        
                        $male18to29Count = count($Male18to29);
                        $male30to59Count = count($Male30to59);
                        $male60andAboveCount = count($Male60andAbove);
                        $female18to29Count = count($Female18to29);
                        $female30to59Count = count($Female30to59);
                        $female60andAboveCount = count($Female60andAbove);
                        $malenoBdayCount = count($MaleNoBday);
                        $femalenoBdayCount = count($FemaleNoBday);
                        $totalmale = $male18to29Count + $male30to59Count + $male60andAboveCount;
                        $totalfemale = $female18to29Count + $female30to59Count + $female60andAboveCount;
                        $sum_KPKits_distributed = array_sum($KPKits_distributed);
                        $sum_farmer_beneficiaries = array_sum($farmer_beneficiaries);

        }

        //All Regions, Provinces, Municipalities
        else if($request->reg=="default"&&$request->prv=="default"&&$request->mun=="default"&&$check_season>=2023)
        {
            $nonEmptyProvinces = array();
            $allProvinces = DB::table('information_schema.tables')
            ->select(DB::raw("table_schema as allProv"))
            ->where('TABLE_SCHEMA', "LIKE", $request->ssn.'_prv_%')
            ->groupBy('allProv')
            ->get();
            
            foreach($allProvinces as $province) {
                $databaseName = $province->allProv;
                $newReleasedCount = DB::table($databaseName.'.new_released')->count();
                $KPKits = DB::table($databaseName.'.new_released')
                                    ->select(DB::raw('SUM(kp_kit_count) as total_kp_kit_count'))
                                    ->where('kp_kit_count', '!=', 0)
                                    ->get();
                            $total_KPKits = $KPKits[0]->total_kp_kit_count;
                
            
                if($newReleasedCount > 0 && $total_KPKits!='null' && $total_KPKits!=0) {
                    array_push($nonEmptyProvinces, $province);
                }
            }
            foreach($nonEmptyProvinces as $row){

                if($request->ssn=='ds2023'){
                    $KPKits = DB::table($row->allProv.'.new_released')
                    ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                    ->join($row->allProv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                    ->where('new_released.kp_kit_count', '!=', 0)
                    ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                    ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                    ->get();

                    $farmers = DB::table($row->allProv.'.new_released')
                        ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                        ->join($row->allProv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                        ->where('new_released.kp_kit_count', '!=', 0)
                        ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                        ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                        ->get();
                $total_KPKits = $KPKits[0]->total_kp_kit_count;
                array_push($KPKits_distributed, $total_KPKits);
                $total_farmers = $farmers[0]->farmer_count;
                array_push($farmer_beneficiaries, $total_farmers);

                $getbday = DB::table($row->allProv.'.farmer_information_final')
                ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                'farmer_information_final.lastName',
                'farmer_information_final.firstName',
                'farmer_information_final.midName',
                'farmer_information_final.extName',
                'farmer_information_final.sex', DB::raw("
                    COALESCE(
                        case
                        when farmer_information_final.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                        when farmer_information_final.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(farmer_information_final.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                        when farmer_information_final.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                        when farmer_information_final.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(farmer_information_final.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                        else '00/00/0000' end,
                    '00/00/0000') as birthdate"),
                    'farmer_information_final.province',
                    'farmer_information_final.municipality',
                    DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                    'new_released.kp_kit_count')
                ->join($row->allProv.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                ->where('new_released.kp_kit_count', '!=', 0)
                ->where(DB::raw('LENGTH(farmer_information_final.birthdate)'), '>', 2)
                ->where('farmer_information_final.birthdate', '!=', '00/00/0000')
                ->get();
                }
                else{   
                $KPKits = DB::table($row->allProv.'.new_released')
                            ->select(DB::raw('SUM(new_released.kp_kit_count) as total_kp_kit_count'))
                            ->join($row->allProv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                            ->where('new_released.kp_kit_count', '!=', 0)
                            ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                            ->where('new_released.birthdate', '!=', '00/00/0000')
                            ->get();
                $farmers = DB::table($row->allProv.'.new_released')
                        ->select(DB::raw('COUNT(DISTINCT(new_released.rcef_id)) as farmer_count'))
                        ->join($row->allProv.'.farmer_information_final', 'new_released.rcef_id', '=','farmer_information_final.rcef_id')
                        ->where('new_released.kp_kit_count', '!=', 0)
                        ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                        ->where('new_released.birthdate', '!=', '00/00/0000')
                        ->get();
                $total_KPKits = $KPKits[0]->total_kp_kit_count;
                array_push($KPKits_distributed, $total_KPKits);
                $total_farmers = $farmers[0]->farmer_count;
                array_push($farmer_beneficiaries, $total_farmers);

                $getbday = DB::table($row->allProv.'.farmer_information_final')
                ->select('farmer_information_final.rsbsa_control_no','farmer_information_final.rcef_id',
                'farmer_information_final.lastName',
                'farmer_information_final.firstName',
                'farmer_information_final.midName',
                'farmer_information_final.extName',
                'farmer_information_final.sex', DB::raw("
                COALESCE(
                    case
                    when new_released.birthdate REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m/%d/%Y'), '%m/%d/%Y')
                    when new_released.birthdate REGEXP '^[0-9]{2}-[0-9]{2}-[0-9]{4}$' then date_format(str_to_date(new_released.birthdate, '%m-%d-%Y'), '%m/%d/%Y')
                    when new_released.birthdate REGEXP '^[0-9]{4}/[0-9]{2}/[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y/%m/%d'), '%m/%d/%Y')
                    when new_released.birthdate REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' then date_format(str_to_date(new_released.birthdate, '%Y-%m-%d'), '%m/%d/%Y')
                    else '00/00/0000' end,
                '00/00/0000') as birthdate"),
                'farmer_information_final.province',
                'farmer_information_final.municipality',
                DB::raw("CONCAT('PH', REPLACE(LEFT(farmer_information_final.rsbsa_control_no, 8), '-', ''), '000') as PSG_code"),
                'new_released.kp_kit_count')
                ->join($row->allProv.'.new_released', 'farmer_information_final.rcef_id', '=', 'new_released.rcef_id')
                ->where('new_released.kp_kit_count', '!=', 0)
                ->where(DB::raw('LENGTH(new_released.birthdate)'), '>', 2)
                ->where('new_released.birthdate', '!=', '00/00/0000')
                ->get();
                }
                
            
                foreach ($getbday as $key => $value) {
                    $birthdate = $value->birthdate;
                    if ($birthdate == '00/00/0000') {
                        $age = '00';
                    } else {
                        $age = date_diff(date_create($birthdate), date_create('today'))->y;
                    }
                    $getbday[$key]->age = $age;
                }

                $allBday = array_merge($allBday, $getbday);
            }
            
            foreach ($allBday as $element) {
                $sex = strtolower($element->sex);
                if (substr($sex, 0, 1) === 'm') {
                    if ($element->age == '00'||$element->age === null){
                        $MaleNoBday[] = $element;
                    }
                    else if ($element->age <= 29) {
                        $Male18to29[] = $element;
                    } else if ($element->age >= 30 && $element->age <= 59) {
                        $Male30to59[] = $element;
                    } else {
                        $Male60andAbove[] = $element;
                    }
                } else {
                    if ($element->age == '00'||$element->age === null){
                        $FemaleNoBday[] = $element;
                    }
                    else if ($element->age <= 29) {
                        $Female18to29[] = $element;
                    } else if ($element->age >= 30 && $element->age <= 59) {
                        $Female30to59[] = $element;
                    } else {
                        $Female60andAbove[] = $element;
                    }
                }
            }



            $male18to29Count = count($Male18to29);
            $male30to59Count = count($Male30to59);
            $male60andAboveCount = count($Male60andAbove);
            $female18to29Count = count($Female18to29);
            $female30to59Count = count($Female30to59);
            $female60andAboveCount = count($Female60andAbove);
            $malenoBdayCount = count($MaleNoBday);
            $femalenoBdayCount = count($FemaleNoBday);
            $totalmale = $male18to29Count + $male30to59Count + $male60andAboveCount;
            $totalfemale = $female18to29Count + $female30to59Count + $female60andAboveCount;
            $sum_KPKits_distributed = array_sum($KPKits_distributed);
            $sum_farmer_beneficiaries = array_sum($farmer_beneficiaries);


        }

        else{
            $male18to29Count = 0;
            $male30to59Count = 0;
            $male60andAboveCount = 0;
            $female18to29Count = 0;
            $female30to59Count = 0;
            $female60andAboveCount = 0;
            $malenoBdayCount = 0;
            $femalenoBdayCount = 0;
            $sum_KPKits_distributed = 0;
            $sum_farmer_beneficiaries = 0;
        }



        foreach ($allBday as $row) {
            $validate = DB::table("kpdistribution_processed.processed_data")
                ->select('rsbsa_control_no','rcef_id')
                ->where('rsbsa_control_no', '=', $row->rsbsa_control_no)
                ->where('rcef_id', '=', $row->rcef_id)
                ->where('season_code', '=', $request->ssn)
                ->get();
        
            if (collect($validate)->isEmpty()&&$row->birthdate!=0) {
                DB::table("kpdistribution_processed.processed_data")
                    ->insert([
                        "rcef_id" => $row->rcef_id,
                        "rsbsa_control_no" => $row->rsbsa_control_no,
                        "lastName" => $row->lastName,
                        "firstName" => $row->firstName,
                        "midName" => $row->midName,
                        "extName" => $row->extName,
                        "sex" => $row->sex,
                        "birthdate" => $row->birthdate,
                        "province" => $row->province,
                        "municipality" => $row->municipality,
                        "PSG_code" => $row->PSG_code,
                        "kp_kit_count" => $row->kp_kit_count,
                        "age" => $row->age,
                        "season_code" => $request->ssn,
                    ]);
            }
        }
        
        

        return array(
            "KPKits_distributed"=>$sum_KPKits_distributed,
            "farmer_beneficiaries"=>$sum_farmer_beneficiaries,
            "Male1"=>$male18to29Count,
            "Male2"=>$male30to59Count,
            "Male3"=>$male60andAboveCount,
            "Female1"=>$female18to29Count, 
            "Female2"=>$female30to59Count,
            "Female3"=>$female60andAboveCount,
            "TotalMale"=>$totalmale,
            "TotalFemale"=>$totalfemale,
            "countProvinces"=>$countProvinces,
            "countMunicipalities"=>$countMunicipalities
        );
        

    }


    public function loadKPDistribution(Request $request)
    {
        $requestData = $request->all();
        // dd($requestData);
        $munArray =  array();
        $provArray =  array();
        $regionsArray =  array();

        $getLocations = DB::table("kp_distribution.kp_distribution_app")
        ->select('location')
        ->where('season','LIKE', $request->ssn)
        ->groupBy('location')
        ->get();

        // dd($getLocations);
        
        foreach($getLocations as $row){
            $substring = explode(', ', $row->location);
            $muni = $substring[0].', '.$substring[1];
            // array_push($test,$muni);
            array_push($munArray,$muni);
            array_push($provArray,$substring[1]);
            array_push($regionsArray,$substring[2]);
        }
        // dd($test);
        $regionsArray = array_unique($regionsArray);
        $provArray = array_unique($provArray);
        $munArray = array_unique($munArray);

        $countProvinces = count($provArray);
        $countMunicipalities = count($munArray);

        $sum_KPKits_distributed = 0;
        $sum_farmer_beneficiaries = 0;
        $male18to29Count = 0;
        $male30to59Count = 0;
        $male60andAboveCount = 0;
        $female18to29Count = 0;
        $female30to59Count = 0;
        $female60andAboveCount = 0;
        $totalMale = 0;
        $totalFemale = 0;
        

        //National
        if($request->reg=="default"&&$request->prv=="default"&&$request->mun=="default")
        {   
            $totalKP = 0;
            $getData = DB::table("kp_distribution.kp_distribution_app")
            ->where('season','LIKE', $request->ssn)
            ->groupBy('fullName')
            ->groupBy('rsbsa_control_no')
            ->get();   

            $getTotalKp = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            )
            ->where('season','LIKE', $request->ssn)
            ->first();
         
            $totalKP = $getTotalKp->kpKits + $getTotalKp->calendars + $getTotalKp->testimonials + $getTotalKp->services + $getTotalKp->apps + $getTotalKp->yunpalayun;

            $sum_KPKits_distributed = $totalKP;
            $sum_farmer_beneficiaries = count($getData);

            $totalMale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'M%')
            ->where('season','LIKE', $request->ssn)
            ->get());
            $totalFemale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'F%')
            ->where('season','LIKE', $request->ssn)
            ->get());
        }
        //Regional
        else if($request->reg!="default"&&$request->prv=="default"&&$request->mun=="default")
        {
            
            $getRegion = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('regionName')
            ->where('regCode', $request->reg)
            ->first();
            
            $region = $getRegion->regionName;
            $totalKP = 0;
            $getData = DB::table("kp_distribution.kp_distribution_app")
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$region.'%')
            ->groupBy('fullName')
            ->groupBy('rsbsa_control_no')
            ->get();

            $getTotalKp = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            )
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$region.'%')
            ->first();
         
            $totalKP = $getTotalKp->kpKits + $getTotalKp->calendars + $getTotalKp->testimonials + $getTotalKp->services + $getTotalKp->apps + $getTotalKp->yunpalayun;

            $sum_KPKits_distributed = $totalKP;
            $sum_farmer_beneficiaries = count($getData);

            $totalMale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'M%')
            ->where('location','LIKE','%'.$region.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
            $totalFemale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'F%')
            ->where('location','LIKE','%'.$region.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
        }
        //Provincial
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default")
        {
            
            $getProvince = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province')
            ->where('prv_code', $request->prv)
            ->first();
            
            $province = $getProvince->province;   
            $totalKP = 0;
            $getData = DB::table("kp_distribution.kp_distribution_app")
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$province.'%')
            ->groupBy('fullName')
            ->groupBy('rsbsa_control_no')
            ->get();

            $getTotalKp = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            
            )
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$province.'%')
            ->first();
         
            $totalKP = $getTotalKp->kpKits + $getTotalKp->calendars + $getTotalKp->testimonials + $getTotalKp->services + $getTotalKp->apps + $getTotalKp->yunpalayun;

            $sum_KPKits_distributed = $totalKP;
            $sum_farmer_beneficiaries = count($getData);

            $totalMale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'M%')
            ->where('location','LIKE','%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
            $totalFemale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'F%')
            ->where('location','LIKE','%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
        }
        //Municipal
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default")
        {
            $requestData = $request->all();
            // dd($requestData);

            $getMunicipality = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province','municipality')
            ->where('prv', $request->mun)
            ->first();
            
            $municipality = $getMunicipality->municipality;
            $province = $getMunicipality->province;
            // dd($province, $municipality);
            $totalKP = 0;
            $getData = DB::table("kp_distribution.kp_distribution_app")
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->groupBy('fullName')
            ->groupBy('rsbsa_control_no')
            ->get();

            // dd($getData);

            $getTotalKp = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            
            )
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->first();
         
            $totalKP = $getTotalKp->kpKits + $getTotalKp->calendars + $getTotalKp->testimonials + $getTotalKp->services + $getTotalKp->apps + $getTotalKp->yunpalayun;

            $sum_KPKits_distributed = $totalKP;
            $sum_farmer_beneficiaries = count($getData);

            $totalMale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'M%')
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
            $totalFemale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'F%')
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
        }            
        else{
            $sum_KPKits_distributed = 0;
            $sum_farmer_beneficiaries = 0;
            $male18to29Count = 0;
            $male30to59Count = 0;
            $male60andAboveCount = 0;
            $female18to29Count = 0;
            $female30to59Count = 0;
            $female60andAboveCount = 0;
            $totalMale = 0;
            $totalFemale = 0;
            $countProvinces = 0;
            $countMunicipalities = 0;
        }

        if($getData){
            foreach ($getData as $data){
                $sex = substr($data->sex,0,1);
                if($data->birthdate == '' ||$data->birthdate == 'N/A'){
                    if($sex == 'M' || $sex == 'm')
                    {
                        $male18to29Count++;
                        continue;
                    }
                    else if($sex == 'F' || $sex == 'f')
                    {
                        $female18to29Count++;
                        continue;
                    }
                }
                else{
                    $birthdateString = Carbon::parse($data->birthdate);
                    $birthdateString = $birthdateString->format('m/d/Y');
                    $currentDate = Carbon::now();
                    $dateOfBirth = Carbon::createFromFormat('m/d/Y', $birthdateString);
                    $age = $currentDate->diffInYears($dateOfBirth);
                    // dd($sex,$age);
                    // dd($birthdateString, $currentDate, $dateOfBirth,$age);
                    if($sex == 'M' || $sex == 'm')
                    {
                        if($age<30)
                        {
                            $male18to29Count++;
                        }
                        else if ($age>29 && $age<60)
                        {
                            $male30to59Count++;
                        }
                        else if($age>59)
                        {
                            $male60andAboveCount++;
                        }
                    }
                    else if($sex == 'F' || $sex == 'f')
                    {
                        if($age<30)
                        {
                            $female18to29Count++;
                        }
                        else if ($age>29 && $age<60)
                        {
                            $female30to59Count++;
                        }
                        else if($age>59)
                        {
                            $female60andAboveCount++;
                        }
                    }
                }
            }
        
        }
                                             
        return array(
            "KPKits_distributed"=>number_format($sum_KPKits_distributed),
            "farmer_beneficiaries"=>number_format($sum_farmer_beneficiaries),
            "Male1"=>number_format($male18to29Count),
            "Male2"=>number_format($male30to59Count),
            "Male3"=>number_format($male60andAboveCount),
            "Female1"=>number_format($female18to29Count),
            "Female2"=>number_format($female30to59Count),
            "Female3"=>number_format($female60andAboveCount),
            "TotalMale"=>number_format($totalMale),
            "TotalFemale"=>number_format($totalFemale),
            "countProvinces"=>number_format($countProvinces),
            "countMunicipalities"=>number_format($countMunicipalities)
        );
    }


    public function loadKPDistribution_old(Request $request)
    {
        $countProvinces = DB::table("kpdistribution_processed.processed_data")
        ->where('season_code','=',$request->ssn)
        ->groupBy(DB::raw('LEFT(PSG_code,6)'))
        ->get();

        $countProvinces = COUNT($countProvinces);

        $countMunicipalities = DB::table("kpdistribution_processed.processed_data")
        ->where('season_code','=',$request->ssn)
        ->groupBy(DB::raw('LEFT(PSG_code,8)'))
        ->get();
        $countMunicipalities = COUNT($countMunicipalities);
        

        //Per Region
        if($request->reg!="default"&&$request->prv=="default"&&$request->mun=="default")
        {
            $sum_KPKits_distributed = DB::table("kpdistribution_processed.processed_data")
                ->select(DB::raw('COALESCE(SUM(kp_kit_count), 0) as total_kpkits'))
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
                ->get();

            $sum_farmer_beneficiaries = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_farmers'))
            ->where('season_code', 'LIKE', $request->ssn)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->get();

            
            $male18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            $female18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


            $totalmale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $totalfemale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->whereRaw('SUBSTR(PSG_code, 3, 2) = '.$request->reg)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
        
        }

        //Per Province
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default")
        {
            $sum_KPKits_distributed = DB::table("kpdistribution_processed.processed_data")
                ->select(DB::raw('COALESCE(SUM(kp_kit_count), 0) as total_kpkits'))
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
                ->get();

            $sum_farmer_beneficiaries = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_farmers'))
            ->where('season_code', 'LIKE', $request->ssn)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->get();

            
            $male18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            $female18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


            $totalmale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $totalfemale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

                        

        }

        //Per Municipality
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default")
        {
            $sum_KPKits_distributed = DB::table("kpdistribution_processed.processed_data")
                ->select(DB::raw('COALESCE(SUM(kp_kit_count), 0) as total_kpkits'))
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
                ->get();

            $sum_farmer_beneficiaries = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_farmers'))
            ->where('season_code', 'LIKE', $request->ssn)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->get();

            
            $male18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            $female18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


            $totalmale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $totalfemale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


        }

        //All Regions, Provinces, Municipalities
        else if($request->reg=="default"&&$request->prv=="default"&&$request->mun=="default")
        {
    
            $sum_KPKits_distributed = DB::table("kpdistribution_processed.processed_data")
                ->select(DB::raw('COALESCE(SUM(kp_kit_count), 0) as total_kpkits'))
                ->where('season_code', 'LIKE', $request->ssn)
                ->get();


            $sum_farmer_beneficiaries = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_farmers'))
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            
            $male18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','<=',29)
            ->where('season_code','=',$request->ssn)
            ->get();
            $male30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->where('season_code','=',$request->ssn)
            ->get();
            $male60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',60)
            ->where('season_code','=',$request->ssn)
            ->get();

            $female18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','<=',29)
            ->where('season_code','=',$request->ssn)
            ->get();
            $female30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->where('season_code','=',$request->ssn)
            ->get();
            $female60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',60)
            ->where('season_code','=',$request->ssn)
            ->get();


            $totalmale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('season_code','=',$request->ssn)
            ->get();
            $totalfemale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('season_code','=',$request->ssn)
            ->get();
        }

        else{
            $male18to29Count = 0;
            $male30to59Count = 0;
            $male60andAboveCount = 0;
            $female18to29Count = 0;
            $female30to59Count = 0;
            $female60andAboveCount = 0;
            $sum_KPKits_distributed = 0;
            $sum_farmer_beneficiaries = 0;
        }                                       
        


        return array(
            "KPKits_distributed"=>$sum_KPKits_distributed[0]->total_kpkits,
            "farmer_beneficiaries"=>$sum_farmer_beneficiaries[0]->total_farmers,
            "Male1"=>$male18to29Count[0]->total_count,
            "Male2"=>$male30to59Count[0]->total_count,
            "Male3"=>$male60andAboveCount[0]->total_count,
            "Female1"=>$female18to29Count[0]->total_count, 
            "Female2"=>$female30to59Count[0]->total_count,
            "Female3"=>$female60andAboveCount[0]->total_count,
            "TotalMale"=>$totalmale[0]->total_count,
            "TotalFemale"=>$totalfemale[0]->total_count,
            "countProvinces"=>$countProvinces,
            "countMunicipalities"=>$countMunicipalities
        );
    }

    public function exportKPDistribution(Request $request)
    {   
        // dd($request->ssn);
        $allBday = array();
        $result_array = array();
        $export_array = array();
        $munArray =  array();
        $provArray =  array();
        $regionsArray =  array();

        $getLocations = DB::table("kp_distribution.kp_distribution_app")
        ->select('location')
        ->where('season','LIKE', $request->ssn)
        ->groupBy('location')
        ->get();

        // dd($getLocations);
        
        foreach($getLocations as $row){
            $substring = explode(', ', $row->location);
            $muni = $substring[0].', '.$substring[1];
            // array_push($test,$muni);
            array_push($munArray,$muni);
            array_push($provArray,$substring[1]);
            array_push($regionsArray,$substring[2]);
        }
        $regionsArray = array_unique($regionsArray);
        $provArray = array_unique($provArray);
        $munArray = array_unique($munArray);

        $countProvinces = count($provArray);
        $countMunicipalities = count($munArray);

        $sum_KPKits_distributed = 0;
        $sum_farmer_beneficiaries = 0;
        $male18to29Count = 0;
        $male30to59Count = 0;
        $male60andAboveCount = 0;
        $female18to29Count = 0;
        $female30to59Count = 0;
        $female60andAboveCount = 0;
        $totalMale = 0;
        $totalFemale = 0;
        
        //Provincial
        if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default")
        {
            
            $getProvince = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province')
            ->where('prv_code', $request->prv)
            ->first();
            
            $province = $getProvince->province;  
            $totalKP = 0;
            $getData = DB::table("kp_distribution.kp_distribution_app")
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$province.'%')
            ->groupBy('fullName')
            ->groupBy('rsbsa_control_no')
            ->orderBy('location')
            ->get();
            $filename = 'KP Distribution Report_'.$province.'_'.$request->ssn;
            $getTotalKp = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            
            )
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$province.'%')
            ->first();
         
            $totalKP = $getTotalKp->kpKits + $getTotalKp->calendars + $getTotalKp->testimonials + $getTotalKp->services + $getTotalKp->apps + $getTotalKp->yunpalayun;

            $sum_KPKits_distributed = $totalKP;
            $sum_farmer_beneficiaries = count($getData);

            $totalMale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'M%')
            ->where('location','LIKE','%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
            $totalFemale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'F%')
            ->where('location','LIKE','%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
        }
        //Municipal
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default")
        {
            $requestData = $request->all();
            // dd($requestData);

            $getMunicipality = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province','municipality')
            ->where('prv', $request->mun)
            ->first();
            
            $municipality = $getMunicipality->municipality;
            $province = $getMunicipality->province;
            // dd($province, $municipality);
            $totalKP = 0;
            $getData = DB::table("kp_distribution.kp_distribution_app")
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->groupBy('fullName')
            ->groupBy('rsbsa_control_no')
            ->orderBy('location')
            ->get();

            // dd($getData);
            $filename = 'KP Distribution Report_'.$municipality.'_'.$province.'_'.$request->ssn;
            $getTotalKp = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            )
            ->where('season','LIKE', $request->ssn)
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->first();
         
            $totalKP = $getTotalKp->kpKits + $getTotalKp->calendars + $getTotalKp->testimonials + $getTotalKp->services + $getTotalKp->apps + $getTotalKp->yunpalayun;

            $sum_KPKits_distributed = $totalKP;
            $sum_farmer_beneficiaries = count($getData);

            $totalMale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'M%')
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
            $totalFemale = count(DB::table("kp_distribution.kp_distribution_app")
            ->where('sex','LIKE', 'F%')
            ->where('location','LIKE','%'.$municipality.'%'.'%'.$province.'%')
            ->where('season','LIKE', $request->ssn)
            ->get());
        }            
        else{
            $sum_KPKits_distributed = 0;
            $sum_farmer_beneficiaries = 0;
            $male18to29Count = 0;
            $male30to59Count = 0;
            $male60andAboveCount = 0;
            $female18to29Count = 0;
            $female30to59Count = 0;
            $female60andAboveCount = 0;
            $totalMale = 0;
            $totalFemale = 0;
            $countProvinces = 0;
            $countMunicipalities = 0;
        }

        if($getData){
            foreach ($getData as $data){
                $sex = substr($data->sex,0,1);
                if($data->birthdate == '' ||$data->birthdate == 'N/A'){
                    if($sex == 'M' || $sex == 'm')
                    {
                        $male18to29Count++;
                        continue;
                    }
                    else if($sex == 'F' || $sex == 'f')
                    {
                        $female18to29Count++;
                        continue;
                    }
                }
                else{
                    $birthdateString = Carbon::parse($data->birthdate);
                    $birthdateString = $birthdateString->format('m/d/Y');
                    $currentDate = Carbon::now();
                    $dateOfBirth = Carbon::createFromFormat('m/d/Y', $birthdateString);
                    $age = $currentDate->diffInYears($dateOfBirth);
                    // dd($sex,$age);
                    // dd($birthdateString, $currentDate, $dateOfBirth,$age);
                    if($sex == 'M' || $sex == 'm')
                    {
                        if($age<30)
                        {
                            $male18to29Count++;
                        }
                        else if ($age>29 && $age<60)
                        {
                            $male30to59Count++;
                        }
                        else if($age>59)
                        {
                            $male60andAboveCount++;
                        }
                    }
                    else if($sex == 'F' || $sex == 'f')
                    {
                        if($age<30)
                        {
                            $female18to29Count++;
                        }
                        else if ($age>29 && $age<60)
                        {
                            $female30to59Count++;
                        }
                        else if($age>59)
                        {
                            $female60andAboveCount++;
                        }
                    }
                }
            }
        
        }

        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "Total KP Kits Distributed",
            "" => $sum_KPKits_distributed
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "Total Farmer Beneficiaries",
            "" => $sum_farmer_beneficiaries
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "Total Male",
            "" => $totalMale
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "- 18 to 29 years old",
            "" => $male18to29Count
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "- 30 to 59 years old",
            "" => $male30to59Count
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "- 60 years old and Above",
            "" => $male60andAboveCount
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "Total Female",
            "" => $totalFemale
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "- 18 to 29 years old",
            "" => $female18to29Count
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "- 30 to 59 years old",
            "" => $female30to59Count
        ));
        array_push($export_array, array(
            "KP Kit Distribution Report (Statistics Overview)" => "- 60 years old and Above",
            "" => $female60andAboveCount
        ));

        // dd($getData);
        $getInfo = array();

        foreach($getData as $data)
        {
            $location = explode(', ', $data->location);
            $province = $location[1];
            $municipality = $location[0];

            $getPSGcode = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('psa_code')
            ->where('province','LIKE',$province)
            ->where('municipality','LIKE',$municipality)
            ->first();

            $totalReceived = DB::table("kp_distribution.kp_distribution_app")
            ->select(DB::raw("SUM(kpKits) as kpKits"),
            DB::raw("SUM(calendars) as calendars"),
            DB::raw("SUM(testimonials) as testimonials"),
            DB::raw("SUM(services) as services"),
            DB::raw("SUM(apps) as apps"),
            DB::raw("SUM(yunpalayun) as yunpalayun")
            
            )
            ->where('id','LIKE', $data->id)
            ->first();

            
            $totalKPreceived = $totalReceived->kpKits + $totalReceived->calendars + $totalReceived->testimonials + $totalReceived->services + $totalReceived->apps + $totalReceived->yunpalayun;
            // dd($totalKPreceived);

            $age = '';

            if($data->birthdate && $data->birthdate !='N/A')
            {
            $birthdateString = Carbon::parse($data->birthdate);
            $birthdateString = $birthdateString->format('m/d/Y');
            $currentDate = Carbon::now();
            $dateOfBirth = Carbon::createFromFormat('m/d/Y', $birthdateString);
            $age = $currentDate->diffInYears($dateOfBirth);
            }

            $sex = substr($data->sex,0,1);
            // dd($sex);

            if($sex == 'M' || $sex == 'm')
            {
                $sex = 'MALE';
            }
            else if($sex == 'F' || $sex == 'f')
            {
                $sex = 'FEMALE';
            }

            array_push($getInfo, array(
                "RSBSA Control Number" => $data->rsbsa_control_no,
                "Full Name" => $data->fullName,
                "Sex" => $sex,
                "Birthdate" => $data->birthdate,
                "Province" => $province,
                "Muncipality" => $municipality,
                "PSG Code" => $getPSGcode->psa_code,
                "KP Kits Received" => $totalKPreceived,
                "Age" => $age
            ));
        }
        // dd($getInfo);

                        $excel_data = json_decode(json_encode($export_array), true);
                        $excel_data2 = json_decode(json_encode($getInfo), true);
                        // dd($excel_data2);
                 
                        // $filename = 'KP Distribution Report_'.$request->prv.'_'.$request->ssn;
                        return Excel::create($filename, function($excel) use ($excel_data,$excel_data2) {
                            $excel->sheet("KP Kit Distribution Report", function($sheet) use ($excel_data) {
                                $sheet->fromArray($excel_data);
                                $sheet->mergeCells('A1:B1');
                                $sheet->getStyle('A1:B1')->getAlignment()->setWrapText(true);
                                $sheet->getStyle('A1:B1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
                                $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                $sheet->getRowDimension(1)->setRowHeight(50);
                
                                $border_style = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                );
                                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
                            });

                            $excel->sheet("Farmer Information", function($sheet) use ($excel_data2) {
                                $sheet->fromArray($excel_data2);
                                $sheet->getStyle('A1:I1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
                                $border_style = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                );
                                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
                            });
                        })->setActiveSheetIndex(0)->download('xlsx');
 
    
    }


    public function exportKPDistribution_old(Request $request)
    {   
        // dd($request->reg);
        // $requestData = $request->all();
        // dd($requestData);

        $allBday = array();
        $result_array = array();
        $export_array = array();

            

        //Per Province
        if($request->reg!="default"&&$request->prv!="default"&&$request->mun=="default")
        {
            $sum_KPKits_distributed = DB::table("kpdistribution_processed.processed_data")
                ->select(DB::raw('COALESCE(SUM(kp_kit_count), 0) as total_kpkits'))
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
                ->get();

            $sum_farmer_beneficiaries = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_farmers'))
            ->where('season_code', 'LIKE', $request->ssn)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->get();

            
            $male18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            $female18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


            $totalmale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $totalfemale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


            $KP_kits = end($sum_KPKits_distributed);
            $KP_value = $KP_kits->total_kpkits;
            $farmers_total = end($sum_farmer_beneficiaries);
            $farmers_value = $farmers_total->total_farmers;
            $male1 = end($male18to29Count);
            $male1_value = $male1->total_count;

            $male2 = end($male30to59Count);
            $male2_value = $male2->total_count;

            $male3 = end($male60andAboveCount);
            $male3_value = $male3->total_count;

            $male_total = end($totalmale);
            $male_total_value = $male_total->total_count;

            $female1 = end($female18to29Count);
            $female1_value = $female1->total_count;

            $female2 = end($female30to59Count);
            $female2_value = $female2->total_count;

            $female3 = end($female60andAboveCount);
            $female3_value = $female3->total_count;

            $female_total = end($totalfemale);
            $female_total_value = $female_total->total_count;
            

            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total KP Kits Distributed",
                "" => $KP_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total Farmer Beneficiaries",
                "" => $farmers_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total Male",
                "" => $male_total_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 18 to 29 years old",
                "" => $male1_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 30 to 59 years old",
                "" => $male2_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 60 years old and Above",
                "" => $male3_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total Female",
                "" => $female_total_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 18 to 29 years old",
                "" => $female1_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 30 to 59 years old",
                "" => $female2_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 60 years old and Above",
                "" => $female3_value
            ));
            // dd($export_array);
            $getInfo = DB::table("kpdistribution_processed.processed_data")
                ->select('rsbsa_control_no', 'lastName', 'firstName', 'midName', 'extName', 'sex', 'birthdate', 'province', 'municipality', 'PSG_code', 'kp_kit_count', 'age')
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 4) = '.$request->prv)
                ->get();
                        $excel_data = json_decode(json_encode($export_array), true);
                        $excel_data2 = json_decode(json_encode($getInfo), true);

                        dd($excel_data,$excel_data2);
                        $filename = 'KP Distribution Report_'.$request->prv.'_'.$request->ssn;
                        return Excel::create($filename, function($excel) use ($excel_data,$excel_data2) {
                            $excel->sheet("KP Kit Distribution Report", function($sheet) use ($excel_data) {
                                $sheet->fromArray($excel_data);
                                $sheet->mergeCells('A1:B1');
                                $sheet->getStyle('A1:B1')->getAlignment()->setWrapText(true);
                                $sheet->getStyle('A1:B1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
                                $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                $sheet->getRowDimension(1)->setRowHeight(50);
                
                                $border_style = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                );
                                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
                            });

                            $excel->sheet("Farmer Information", function($sheet) use ($excel_data2) {
                                $sheet->fromArray($excel_data2);
                                $sheet->getStyle('A1:L1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
                                $border_style = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                );
                                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
                            });
                        })->setActiveSheetIndex(0)->download('xlsx');
                        

        }

        //Per Municipality
        else if($request->reg!="default"&&$request->prv!="default"&&$request->mun!="default")
        {
            $sum_KPKits_distributed = DB::table("kpdistribution_processed.processed_data")
                ->select(DB::raw('COALESCE(SUM(kp_kit_count), 0) as total_kpkits'))
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
                ->get();

            $sum_farmer_beneficiaries = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_farmers'))
            ->where('season_code', 'LIKE', $request->ssn)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->get();

            
            $male18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $male60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            $female18to29Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','<=',29)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female30to59Count = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',30)
            ->where('age','<=',59)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $female60andAboveCount = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->where('age','>=',60)
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();


            $totalmale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','M%')
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();
            $totalfemale = DB::table("kpdistribution_processed.processed_data")
            ->select(DB::raw('COUNT(DISTINCT(rcef_id)) as total_count'))
            ->where('sex','LIKE','F%')
            ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
            ->where('season_code', 'LIKE', $request->ssn)
            ->get();

            $KP_kits = end($sum_KPKits_distributed);
            $KP_value = $KP_kits->total_kpkits;
            $farmers_total = end($sum_farmer_beneficiaries);
            $farmers_value = $farmers_total->total_farmers;
            $male1 = end($male18to29Count);
            $male1_value = $male1->total_count;

            $male2 = end($male30to59Count);
            $male2_value = $male2->total_count;

            $male3 = end($male60andAboveCount);
            $male3_value = $male3->total_count;

            $male_total = end($totalmale);
            $male_total_value = $male_total->total_count;

            $female1 = end($female18to29Count);
            $female1_value = $female1->total_count;

            $female2 = end($female30to59Count);
            $female2_value = $female2->total_count;

            $female3 = end($female60andAboveCount);
            $female3_value = $female3->total_count;

            $female_total = end($totalfemale);
            $female_total_value = $female_total->total_count;
            

            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total KP Kits Distributed",
                "" => $KP_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total Farmer Beneficiaries",
                "" => $farmers_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total Male",
                "" => $male_total_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 18 to 29 years old",
                "" => $male1_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 30 to 59 years old",
                "" => $male2_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 60 years old and Above",
                "" => $male3_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "Total Female",
                "" => $female_total_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 18 to 29 years old",
                "" => $female1_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 30 to 59 years old",
                "" => $female2_value
            ));
            array_push($export_array, array(
                "KP Kit Distribution Report (Statistics Overview)" => "- 60 years old and Above",
                "" => $female3_value
            ));

            $getInfo = DB::table("kpdistribution_processed.processed_data")
                ->select('rsbsa_control_no', 'lastName', 'firstName', 'midName', 'extName', 'sex', 'birthdate', 'province', 'municipality', 'PSG_code', 'kp_kit_count', 'age')
                ->where('season_code', 'LIKE', $request->ssn)
                ->whereRaw('SUBSTR(PSG_code, 3, 6) = '.$request->mun)
                ->get();
            
                        $excel_data = json_decode(json_encode($export_array), true);
                        $excel_data2 = json_decode(json_encode($getInfo), true);
                        $filename = 'KP Distribution Report_'.$request->mun.'_'.$request->ssn;
                        return Excel::create($filename, function($excel) use ($excel_data,$excel_data2) {
                            $excel->sheet("KP Kit Distribution Report", function($sheet) use ($excel_data) {
                                $sheet->fromArray($excel_data);$sheet->mergeCells('A1:B1');
                                $sheet->getStyle('A1:B1')->getAlignment()->setWrapText(true);
                                $sheet->getStyle('A1:B1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
                                $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                $sheet->getStyle('A1:B1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                $sheet->getRowDimension(1)->setRowHeight(50);
                
                                $border_style = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                );
                                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
                            });

                            $excel->sheet("Farmer Information", function($sheet) use ($excel_data2) {
                                $sheet->fromArray($excel_data2);
                                $sheet->getStyle('A1:L1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00B53F');
                                $border_style = array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => '000000'),
                                        ),
                                    ),
                                );
                                $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())->applyFromArray($border_style);
                            });
                        })->setActiveSheetIndex(0)->download('xlsx');
            
        }
        

    }


    
}

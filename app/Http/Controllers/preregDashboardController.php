<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class preregDashboardController extends Controller
{
    public function index(){
        $taggedRegion = Auth::user()->stationId;
        $chartReg = [0];
        if(Auth::user()->stationId == "11005"){
            $chartReg = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select(
                    'farm_addr_reg as regionName', 
                    DB::raw('LEFT(claiming_prv, 2) as regCode')
                )
                ->where('sed_verified.isPrereg', 1)
                ->groupBy('sed_verified.farm_addr_reg')
                ->get();
        }else{
            $chartReg = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', DB::raw('LEFT(sed_verified.claiming_prv, 2)'), '=', 'lib_prv.regCode')
                ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'lib_prv.regionName', '=', 'lib_station.region')
                ->select('lib_prv.regionName', 'lib_prv.regCode')
                ->where('sed_verified.isPrereg', 1)
                ->where('lib_station.stationID', 'LIKE', $taggedRegion)
                ->groupBy('sed_verified.farm_addr_reg')
                ->orderBy('lib_prv.region_sort', 'ASC')
                ->get();
        }

        $lastSyncAgeRange = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.prereg_age_range_view')
        ->select("dateSync")
        ->orderBy("_id", "DESC")
        ->first();
            
        
        $regArr = array();
        foreach($chartReg as $row){
            // array_push($regArr, $row->regionName);
            array_push($regArr, 0);
        }

        $pythonPath = $GLOBALS['python_path'];

        $scriptPath = base_path('app/Http/PyScript/prereg.scripts/prereg_dashboard_index.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];
        $reg = $taggedRegion;

        $escapedSsn = escapeshellarg($ssn);
        $escapedReg = escapeshellarg($reg);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedReg";
    
        // Create a new process
        $process = new Process($command);
        
        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $result = json_decode($output, true);
            
            $total_fca = $result['total_fca_org'];
            $total_fca_reg = $result['count_fca_region'];
            $total_fca_prv = $result['count_fca_province'];
            $total_fca_muni = $result['count_fca_municipality'];

            // $total_fca_prereg = $sum_fca[0]->total; //Original
            $total_fca_prereg = $result['total_fca_members'];

            $total_fca_prereg_ave = $result['total_fca_members'] / $result['total_fca_org'];

            $total_male_percent = number_format($result['perc_male'], 2);
            $total_female_percent = number_format($result['perc_female'], 2);

            $total_bags = $result['total_distributed_bags'];
            if($lastSyncAgeRange)
                $lastSyncDate = $lastSyncAgeRange->dateSync;
            else
                $lastSyncDate = "0000-00-00 00:00:00";

            return view('prereg.dashboard', 
            compact(
                'coop_list', 
                'count_fca', 
                'total_fca_prereg',
                'total_fca_prereg_ave',
                'total_fca', 
                'total_fca_muni', 
                'total_fca_reg', 
                'total_fca_prv',
                'total_male_percent',
                'total_female_percent',
                'total_bags',
                'regArr',
                'chartReg',
                'lastSyncDate'
            ));

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }
    }

    public function loadChartDataDefault(){
        $taggedRegion = Auth::user()->stationId;
        
        $pythonPath = $GLOBALS['python_path'];

        $scriptPath = base_path('app/Http/PyScript/prereg.scripts/prereg_dashboard_regions.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];
        $reg = $taggedRegion;

        $escapedSsn = escapeshellarg($ssn);
        $escapedReg = escapeshellarg($reg);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedReg";
    
        // Create a new process
        $process = new Process($command);

        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $regions_arr = json_decode($output, true);

            return array(
                "regArr" => $regions_arr['regions'],
                "regVol" => $regions_arr['values'],
            );

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }

        
    }

    public function getMuni(Request $request){
        $taggedRegion = Auth::user()->stationId;
        if(Auth::user()->stationId == "11005"){
            $taggedRegion = "%";
        }
        $muniChart = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'sed_verified.farm_addr_prv', '=', 'lib_station.province')
            ->select('sed_verified.farm_addr_prv as province_name', DB::raw('count(sed_verified.farm_addr_prv) as members'))
            ->where('sed_verified.isPrereg', 1)
            ->where(DB::raw('LEFT(sed_verified.claiming_prv, 2)'), $request->_region)
            ->where('lib_station.stationID', 'LIKE', $taggedRegion)
            ->groupBy('sed_verified.farm_addr_prv')
            ->get();
        
        $municipalities = array();
        $members = array();
        foreach($muniChart as $row){
            array_push($municipalities, $row->province_name);
            array_push($members, $row->members);
        }

        return array(
            "muni" => $municipalities,
            "memb" => $members
        );
    }

    public function getMunicipalities(Request $request){
        $taggedRegion = Auth::user()->stationId;
        if(Auth::user()->stationId == "11005"){
            $taggedRegion = "%";
        }
        $muniChart = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'sed_verified.farm_addr_prv', '=', 'lib_station.province')
            ->select('sed_verified.farm_addr_mun as municipality_name', DB::raw('count(sed_verified.farm_addr_mun) as members'))
            ->where('sed_verified.isPrereg', 1)
            ->where('sed_verified.farm_addr_prv', $request->province)
            ->where('lib_station.stationID', 'LIKE', $taggedRegion)
            ->groupBy('sed_verified.farm_addr_mun')
            ->get();
        
        $municipalities = array();
        $muniMembers = array();

        foreach($muniChart as $row){
            array_push($municipalities, $row->municipality_name);
            array_push($muniMembers, $row->members);
        }

        return array(
            "muni_name" => $municipalities,
            "muni_memb" => $muniMembers
        );
    }

    public function getCropEstab(){
        $taggedRegion = Auth::user()->stationId;
        if(Auth::user()->stationId == "11005"){
            $taggedRegion = "%";
        }
        $cropEstab = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'sed_verified.farm_addr_prv', '=', 'lib_station.province')
            ->select(DB::raw("IF(sed_verified.crop_establishment is null, 'Not Specified', sed_verified.crop_establishment) as crop_establishment"), DB::raw('count(sed_verified.sed_id) as ce_stat'))
            // ->where(DB::raw("province_name not in ('', null)"))
            ->where('sed_verified.municipality_name', '<>', 'AGUINALDO')
            ->where('sed_verified.isPrereg', 1)
            ->where('lib_station.stationID', 'LIKE', $taggedRegion)
            // ->where('province_name', $request->province)
            ->groupBy('sed_verified.crop_establishment')
            ->get();

        $ce_title = array();
        $ce_value = array();

        foreach($cropEstab as $row){
            array_push($ce_title, ucwords(str_replace("_"," ",$row->crop_establishment)));
            array_push($ce_value, $row->ce_stat);
        }

        return array(
            "ce_title" => $ce_title,
            "ce_stat" => $ce_value
        );
    }

    public function getEcoSys(){
        $taggedRegion = Auth::user()->stationId;
        if(Auth::user()->stationId == "11005"){
            $taggedRegion = "%";
        }
        $cropEstab = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'sed_verified.farm_addr_prv', '=', 'lib_station.province')
            ->select(DB::raw("COALESCE(sed_verified.ecosystem, '') as eco_title"), DB::raw('count(sed_verified.sed_id) as eco_stat'))
            ->where('sed_verified.isPrereg', 1)
            // ->where(DB::raw("province_name not in ('', null)"))
            // ->where('sed_verified.farm_addr_mun', '<>', 'AGUINALDO')
            ->where('lib_station.stationID', 'LIKE', $taggedRegion)
            // ->where('ecosystem', '<>', '')
            // ->where('ecosystem', '<>', null)
            // ->where('province_name', $request->province)
            ->groupBy(DB::raw('COALESCE(sed_verified.ecosystem, "")'))
            ->orderBy('sed_verified.ecosystem')
            ->get();
        $eco_title = array();
        $eco_stat = array();
        
        // dd($cropEstab);

        foreach($cropEstab as $row){
            if($row->eco_title == 'irrigated_cis'){
                array_push($eco_title, "CIS (Communal)");
            }
            else if($row->eco_title == 'irrigated_nis_nia'){
                array_push($eco_title, "NIS/NIA");
            }
            else if($row->eco_title == 'irrigated_stw'){
                array_push($eco_title, "Shallow Tube Well (STW)");
            }
            else if($row->eco_title == 'irrigated_rsp'){
                array_push($eco_title, "River/Stream Pumping");
            }
            else if($row->eco_title == 'irrigated_swip'){
                array_push($eco_title, "Small water impounding pond (SWIP)");
            }
            else if($row->eco_title == 'rainfed_low'){
                array_push($eco_title, "Rainfed, Lowland");
            }
            else if($row->eco_title == 'rainfed_up'){
                array_push($eco_title, "Rainfed, Upland");
            }
            else{
                array_push($eco_title, "Not Specified");
            }
            // array_push($eco_title, $row->eco_title);
            array_push($eco_stat, $row->eco_stat);
        }

        return array(
            "eco_title" => $eco_title,
            "eco_stat" => $eco_stat
        );
    }

    public function getAveYield(){
        $taggedRegion = Auth::user()->stationId;
        if(Auth::user()->stationId == "11005"){
            $taggedRegion = "%";
        }
        $male = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'sed_verified.farm_addr_prv', '=', 'lib_station.province')
            ->select('ver_sex', DB::raw('avg(yield) as yield'))
            // ->where(DB::raw("province_name not in ('', null)"))
            // ->where('municipality_name', '<>', 'AGUINALDO')
            ->where('ver_sex', 'like', 'M%')
            ->where('isPrereg', 1)
            ->where('lib_station.stationID', 'LIKE', $taggedRegion)
            // ->where('province_name', $request->province)
            // ->groupBy('ver_sex')
            ->get();
        
        $female = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->join($GLOBALS['season_prefix'].'sdms_db_dev.lib_station', 'sed_verified.farm_addr_prv', '=', 'lib_station.province')
            ->select('ver_sex', DB::raw('avg(yield) as yield'))
            // ->where(DB::raw("province_name not in ('', null)"))
            // ->where('municipality_name', '<>', 'AGUINALDO')
            ->where('ver_sex', 'like', 'F%')
            ->where('isPrereg', 1)
            ->where('lib_station.stationID', 'LIKE', $taggedRegion)
            // ->where('province_name', $request->province)
            // ->groupBy('ecosystem')
            ->get();
    
        $genders = array();
        $gender_count = array();

        array_push($genders, ucwords($male[0]->ver_sex));
        array_push($genders, ucwords($female[0]->ver_sex));
        array_push($gender_count, $male[0]->yield);
        array_push($gender_count, $female[0]->yield);

        // dd($gender_count);

        return array(
            "genders" => $genders,
            "genders_count" => $gender_count
        );
    }

    public function getAgeRangeView(){
        $returnValue = DB::table($GLOBALS['season_prefix']."rcep_paymaya.prereg_age_range_view")
        ->orderBy("_id", "DESC")
        ->first();

        if($returnValue){
            $allYield = array(
                "age_min" => floatval($returnValue->a_min),
                "age_mid" => floatval($returnValue->a_mid),
                "age_max" => floatval($returnValue->a_max),
            );
            $femaleYield = array(
                "age_min" => floatval($returnValue->f_min),
                "age_mid" => floatval($returnValue->f_mid),
                "age_max" => floatval($returnValue->f_max),
            );
            $maleYield = array(
                "age_min" => floatval($returnValue->m_min),
                "age_mid" => floatval($returnValue->m_mid),
                "age_max" => floatval($returnValue->m_max),
            );
        }else{
            $allYield = array(
                "age_min" => 0,
                "age_mid" => 0,
                "age_max" => 0,
            );
            $femaleYield = array(
                "age_min" => 0,
                "age_mid" => 0,
                "age_max" => 0,
            );
            $maleYield = array(
                "age_min" => 0,
                "age_mid" => 0,
                "age_max" => 0,
            );
        }

        // dd($returnValue);
        return json_encode(array(
            "overAllArray" => $allYield,
            "maleArray" => $maleYield,
            "femaleArray" => $femaleYield
        ));
    }

    public function getPrv(){
        
        $pythonPath = $GLOBALS['python_path'];

        $scriptPath = base_path('app/Http/PyScript/prereg.scripts/prereg_dashboard.py');

        // Escape the arguments
        $ssn = $GLOBALS["season_prefix"];

        $escapedSsn = escapeshellarg($ssn);

        // Construct the command with arguments as a single string
        $command = "$pythonPath \"$scriptPath\" $escapedSsn";

    
        // Create a new process
        $process = new Process($command);

        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $yields_array = json_decode($output, true);
            
            $result =  DB::table($GLOBALS['season_prefix']."rcep_paymaya.prereg_age_range_view")
                ->insert(["a_min" => $yields_array['allYield']['age_min'],
                            "a_mid" => $yields_array['allYield']['age_mid'],
                            "a_max" => $yields_array['allYield']['age_max'],
                            "f_min" => $yields_array['femaleYield']['age_min'],
                            "f_mid" => $yields_array['femaleYield']['age_mid'],
                            "f_max" => $yields_array['femaleYield']['age_max'],
                            "m_min" => $yields_array['maleYield']['age_min'],
                            "m_mid" => $yields_array['maleYield']['age_mid'],
                            "m_max" => $yields_array['maleYield']['age_max'],
            ]);

            

            if(json_encode($result)=="true"){
                $syncDate = DB::table($GLOBALS['season_prefix']."rcep_paymaya.prereg_age_range_view")
                    ->Select("dateSync")
                    ->orderBy("_id", "DESC")
                    ->first();

                return $syncDate->dateSync;
            }else{
                echo "Sync Failed. Try again later.";
            }

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }
    }

    public function toCSV(Request $request){
        $reg = $request->reg;
        $prv = $request->prv;
        $mun = $request->mun;
        
        $filename_reg = $reg;
        $filename_prv = $prv;
        $filename_mun = $mun;
        

        
        //selectors
        if($request->reg == "All"){
            $reg = "%";
            $filename_reg = "ALL";
        }
        if($request->prv == "All"){
            $prv = "%";
            $filename_prv = "ALL";
        }
        if($request->mun == "All"){
            $mun = "%";
            $filename_mun = "ALL";
        }

        if($prv != "%" && $mun != "%"){
            $prv_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where('province', $prv)
                ->where("municipality", $mun)
                ->first();

                $prv_claim_pattern = $prv_data->regCode."-".$prv_data->provCode."-".$prv_data->munCode;

                $prvs = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                ->select(DB::raw("LEFT(barangay_code, 4) as prv_code"))
                ->where("claiming_prv", $prv_claim_pattern)
                ->where("isPrereg", 1)
                ->groupBy("prv_code")
                ->get();
            
        }else{
            $prvs = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select(DB::raw("LEFT(barangay_code, 4) as prv_code"))
            ->where(DB::raw("LEFT(claiming_prv, 2)"), "LIKE", $reg)
            ->where("farm_addr_prv", "LIKE", $prv)
            ->where("farm_addr_mun", "LIKE", $mun)
            ->where("isPrereg", 1)
            ->groupBy("prv_code")
            ->get();

        }




        $filename = "PR_".$filename_reg."_".$filename_prv."_".$filename_mun."_".date('Y-m-d')."_".date('h-i-sa');

       

        // dd($prvs);
        $semi_final_tbl = array();
        $final_tbl = array();
        foreach($prvs as $row){



            if($prv != "%" && $mun != "%"){
                $prv_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                    ->where('province', $prv)
                    ->where("municipality", $mun)
                    ->first();
    
                    $prv_claim_pattern = $prv_data->regCode."-".$prv_data->provCode."-".$prv_data->munCode;
                    $table_list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                        ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries', 'sed_verified.rcef_id', '=', 'tbl_beneficiaries.paymaya_code')
                        ->leftJoin($GLOBALS['season_prefix'].'prv_'.$row->prv_code.'.farmer_information_final', 'sed_verified.rcef_id', '=', 'farmer_information_final.rcef_id')
                        ->select(
                            'sed_verified.rcef_id as rcef_id',
                            'sed_verified.rsbsa_control_number as rsbsa_control_number',
                            'sed_verified.fname as fname',
                            'sed_verified.midname as midname',
                            'sed_verified.lname as lname',
                            'sed_verified.extename as extename',
                            'sed_verified.ver_sex as ver_sex',
                            'farmer_information_final.fca_name as fca_name',
                            'sed_verified.farm_addr_prv as province_name',
                            'sed_verified.farm_addr_mun as municipality_name',
                            'tbl_beneficiaries.drop_off_point as drop_off_point',
                            'sed_verified.yield as yield',
                            'sed_verified.actual_area as actual_area',
                            'sed_verified.farmer_declared_area as farmer_declared_area',
                            'sed_verified.date_updated as prereg_date',
                            'sed_verified.varietyPref as varietyPref',
                            'sed_verified.sowing_month as sowing_month',
                            'sed_verified.sowing_week as sowing_week'
                        )
                        ->where("sed_verified.claiming_prv", $prv_claim_pattern)
                        ->where('sed_verified.isPrereg', 1)
                        ->orderBy("sed_verified.farm_addr_prv", "ASC")
                        ->orderBy("sed_verified.farm_addr_mun", "ASC")
                        ->orderBy("sed_verified.lname", "ASC")
                        ->groupBy("rcef_id")
                        ->get();
                    
            }else{
                $table_list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->leftJoin($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries', 'sed_verified.rcef_id', '=', 'tbl_beneficiaries.paymaya_code')
                    ->leftJoin($GLOBALS['season_prefix'].'prv_'.$row->prv_code.'.farmer_information_final', 'sed_verified.rcef_id', '=', 'farmer_information_final.rcef_id')
                    ->select(
                        'sed_verified.rcef_id as rcef_id',
                        'sed_verified.rsbsa_control_number as rsbsa_control_number',
                        'sed_verified.fname as fname',
                        'sed_verified.midname as midname',
                        'sed_verified.lname as lname',
                        'sed_verified.extename as extename',
                        'sed_verified.ver_sex as ver_sex',
                        'farmer_information_final.fca_name as fca_name',
                        'sed_verified.farm_addr_prv as province_name',
                        'sed_verified.farm_addr_mun as municipality_name',
                        'tbl_beneficiaries.drop_off_point as drop_off_point',
                        'sed_verified.yield as yield',
                        'sed_verified.actual_area as actual_area',
                        'sed_verified.farmer_declared_area as farmer_declared_area',
                        'sed_verified.date_updated as prereg_date',
                        'sed_verified.varietyPref as varietyPref',
                        'sed_verified.sowing_month as sowing_month',
                        'sed_verified.sowing_week as sowing_week'
                    )
                    ->where("sed_verified.region", "LIKE", $reg)
                    ->where("sed_verified.farm_addr_prv", "LIKE", $prv)
                    ->where("sed_verified.farm_addr_mun", "LIKE", $mun)
                    ->where('sed_verified.isPrereg', 1)
                    ->orderBy("sed_verified.farm_addr_prv", "ASC")
                    ->orderBy("sed_verified.farm_addr_mun", "ASC")
                    ->orderBy("sed_verified.lname", "ASC")
                    ->groupBy("rcef_id")
                    ->get();
            }


            

            $final_tbl = array_merge($final_tbl, $table_list);
            $final_tbl = array_unique($final_tbl, SORT_REGULAR);
        }

        $table_arr = array();
        $computed_bags = 0;
        $table_header = array(
            ['RCEF ID', 'RSBSA Control Number', 'First Name', 'Middle Name', 'Last Name', 'Extension Name', 'Sex', 'FCA Name', 'Province', 'Municipality', 'Dropoff Point', 'Previous Harvest Yield', $GLOBALS['season_prefix'].' Actual Area', $GLOBALS['season_prefix'].' Declared Area', 'Computed Bags', 'Declared Variety (Available)', 'Sowing Date', 'Pre-registration Date']
        );

        foreach($final_tbl as $row){
            $computed_bags = ceil($row->farmer_declared_area*2);
            $tmp = ["\t".$row->rcef_id, $row->rsbsa_control_number,  $row->fname, $row->midname, $row->lname, $row->extename, $row->ver_sex, strlen($row->fca_name) > 0? $row->fca_name : "None", $row->province_name, $row->municipality_name, strlen($row->drop_off_point) > 0? $row->drop_off_point : "Not Scheduled", $row->yield, $row->actual_area, $row->farmer_declared_area, $computed_bags, $row->varietyPref, ($row->sowing_month.$row->sowing_week), $row->prereg_date];
            array_push($table_arr, $tmp);
        }
        
        // Open a file in write mode ('w')
        $baseUri = "./";
        $uri = 'public/assets/'.$filename.'.csv';
        $fp = fopen($baseUri.''.$uri, 'w');
        
        // Loop through file pointer and a line
        fputcsv($fp, $table_header[0]);

        foreach ($table_arr as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
        return $uri;
    }

    public function unlinking(Request $request){
        unlink($request->uri);
    }
}

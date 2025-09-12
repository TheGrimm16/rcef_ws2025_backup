<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use App\DeliveryInspect;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Auth;
use DB;
use Session;
use Config;

class SeedReportController extends Controller
{
    public function set_database($database_name){
        try {
            \Config::set('database.connections.reports_db.database', $database_name);
            DB::purge('reports_db');

            DB::connection('reports_db')->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            return "Could not connect to the database";
        }
    }

    public function get_provinces(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_variety_report')
            ->where('region', '=', $request->region)
            ->groupBy('province')
            ->orderBy('province')
            ->get();
        $return_str= '';
        foreach($provinces as $province){
            $return_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $return_str;
    }

    public function get_provinces_data(Request $request){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('regionName', '=', $request->region)
            ->groupBy('province')
            ->orderBy('region_sort')
            ->get();
        $return_str= '';
        foreach($provinces as $province){
            $return_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $return_str;
    }

    public function get_municipalities(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_variety_report')
            ->where('region', '=', $request->region)
            ->where('province', '=', $request->province)
            ->groupBy('province', 'municipality')
            ->get();

        $return_str= '';
        foreach($municipalities as $municipality){
            $return_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $return_str;
    }

    public function report_dop_home(){
        $dropoff_list = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
            ->groupBy('prv_dropoff_id')
            ->orderBy('region', 'ASC')
            ->orderBy('province', 'ASC')
            ->orderBy('municipality', 'ASC')
            ->get();
        return view('reports.varities.dop_home')
            ->with('dropoff_list', $dropoff_list);
    }

    public function report_dop_resultTable(Request $request){
        $dop_id = $request->dop_id;

        //get prv
        $database = $GLOBALS['season_prefix']."prv_".substr($request->dop_id,0,4);
        $table_conn = $this->set_database($database);

        if($table_conn == "Connection Established!"){
            $prv_dist_data = DB::connection('reports_db')->table("released")->first();
            if(count($prv_dist_data) > 0){
                return Datatables::of(DB::connection('reports_db')->table('released')
                    ->select(DB::raw('SUM(released.bags) as total_volume'),'seed_variety')
                    ->where('prv_dropoff_id', '=', $request->dop_id)
                    ->groupBy('seed_variety')
                )
                ->make(true);
            }else{
                $response_arr[] = array(
                    "seed_variety" => "No distribution data has been detected.",
                    "total_volume" => "N/A"
                );
                $response_data = collect($response_arr);
                return Datatables::of($response_data)
                ->make(true);
            }
        }else{
            //return json_encode("No connection established to database");
            $response_arr[] = array(
                "seed_variety" => "No connection established to the central database.",
                "total_volume" => "N/A"
            );
            $response_data = collect($response_arr);
            return Datatables::of($response_data)
            ->make(true);
        }
    }

    public function seed_report_overall(){

        $pythonPath = $GLOBALS['python_path'];

        $scriptPath = base_path('app/Http/PyScript/seed-variety-report/seed_variety_report.py');

        $ssn = $GLOBALS["season_prefix"];
        $escapedSsn = escapeshellarg($ssn);

        $command = "$pythonPath \"$scriptPath\" $escapedSsn";

        // Create a new process
        $process = new Process($command);

        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $result = json_decode($output, true);

            $regions = $result['regions'];
            $overall_seed_data = $result['overall_seed_data'];
            $total_seed_variety = $result['total_seed_variety'];//[0]['total_seed_variety'];
            $total_seed_data = $result['total_seed_data'];//[0]['total_seed_data'];

            

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }

//old code
        //ws2025_rcep_reports
/*         $overall_seed_data = DB::connection('rcep_reports_db')
            ->table('lib_variety_report')
            ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
            ->groupBy('seed_variety')
            ->orderBy('seed_total_volume', 'DESC')
            ->get();
        //ws2025_rcep_reports
        $total_seed_variety = DB::connection('rcep_reports_db')
            ->table('lib_variety_report')
            ->select('seed_variety')
            ->groupBy('seed_variety')
            ->get();
        //ws2025_rcep_reports
        $total_seed_data = DB::connection('rcep_reports_db')
            ->table('lib_variety_report')
            ->select(DB::raw('SUM(total_volume) as seed_total_volume'))
            ->value('total_volume');
        
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_variety_report')
			->select('lib_variety_report.region')
			->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($table_join) {
                $table_join->on($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv.regionName', '=', $GLOBALS['season_prefix'].'rcep_reports.lib_variety_report.region');
            })
			->orderBy('lib_prv.region_sort', 'ASC')
			->groupBy('lib_variety_report.region')
			->get(); */
			
/*         return view('reports.varities.overall')
            ->with('overall_seed_data', $overall_seed_data)
            ->with('total_seed_data', $total_seed_data)
            ->with('total_seed_variety', count($total_seed_variety))
            ->with('regions', $regions); */
            return view('reports.varities.overall',compact("regions","overall_seed_data","total_seed_variety","total_seed_data"));
    }


    public function get_seed_data(Request $request){
        $overall_seed_data = DB::connection('rcep_reports_db')
            ->table('lib_variety_report')
            ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
            ->groupBy('seed_variety')
            ->orderBy('seed_total_volume', 'DESC')
            ->get();

        $seed_variety_arr = array();
        $seed_bags_arr = array();
        foreach($overall_seed_data as $row){
            array_push($seed_variety_arr, $row->seed_variety);
            array_push($seed_bags_arr, intval($row->seed_total_volume));
        }

        return array(
            "seed_variety_list" => $seed_variety_arr,
            "seed_bag_list" => $seed_bags_arr
        );
    }

    public function filter_seed_data(Request $request){
        $region = $request->region;
        $province = $request->province;
        $municipality = $request->municipality;

        if($region != "0" && $province =="0" && $municipality == "0"){
            $overall_seed_data = DB::connection('rcep_reports_db')
                ->table('lib_variety_report')
                ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
                ->where('region', $region)
                ->groupBy('seed_variety')
                ->orderBy('seed_total_volume', 'DESC')
                ->get();
        
        }else if($region != "0" && $province !="0" && $municipality == "0"){
            $overall_seed_data = DB::connection('rcep_reports_db')
                ->table('lib_variety_report')
                ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
                ->where('region', $region)
                ->where('province', $province)
                ->groupBy('seed_variety')
                ->orderBy('seed_total_volume', 'DESC')
                ->get();
        
        }else if($region != "0" && $province !="0" && $municipality != "0"){
            $overall_seed_data = DB::connection('rcep_reports_db')
                ->table('lib_variety_report')
                ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
                ->where('region', $region)
                ->where('province', $province)
                ->where('municipality', $municipality)
                ->groupBy('seed_variety')
                ->orderBy('seed_total_volume', 'DESC')
                ->get();
        }

        $seed_variety_arr = array();
        $seed_bags_arr = array();
        foreach($overall_seed_data as $row){
            array_push($seed_variety_arr, $row->seed_variety);
            array_push($seed_bags_arr, intval($row->seed_total_volume));
        }

        return array(
            "seed_variety_list" => $seed_variety_arr,
            "seed_bag_list" => $seed_bags_arr
        );
    }

    public function filter_table(Request $request){
        $region = $request->region;
        $province = $request->province;
        $municipality = $request->municipality;

        $query = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv')
        ->where('regionName', '=', $region);

        if ($province !== '0') {
            $query->where('province', '=', $province);
        }

        $prv = $query->limit(1)->value('prv');
        
        $pythonPath = $GLOBALS['python_path'];

        $scriptPath = base_path('app/Http/PyScript/seed-variety-report/seed_variety_filter.py');

        if($province=='0'){
            //trim prv 2 only
            $prv = substr($prv, 0, 2);
        }else{
            $prv = substr($prv, 0, 4);
        }

        $ssn = $GLOBALS["season_prefix"];

        $escapedSsn = escapeshellarg($ssn);
        $escapedPrv = escapeshellarg($prv);
        $escapedProv = escapeshellarg($province);
        $escapedMuni = escapeshellarg($municipality);

        $command = "$pythonPath \"$scriptPath\" $escapedSsn $escapedPrv $escapedProv $escapedMuni";

        // Create a new process
        $process = new Process($command);

        try {
            // Run the process
            $process->mustRun();

            $output = $process->getOutput();
            $result2 = json_decode($output, true);


            $result = $result2['overall_seed_data'];
            
            $overall_seed_data = [];

            foreach ($result['seed_variety'] as $index => $variety) {
                $overall_seed_data[] = [
                    'seed_variety' => $variety,
                    'total_seed_bags' => $result['total_seed_bags'][$index]
                ];
            }
            
            return Datatables::of(collect($overall_seed_data))
            ->addColumn('volume', function($row){
                return number_format($row['total_seed_bags'])." bag(s)";
            })
            ->addIndexColumn()
            ->make(true);
            

        } catch (ProcessFailedException $exception) {
            // Handle the exception
            echo $exception->getMessage();
        }

        //return array([$region, $province, $municipality]);

        /* if($region != "0" && $province =="0" && $municipality == "0"){
            return Datatables::of(DB::connection('rcep_reports_db')
                ->table('lib_variety_report')
                ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
                ->where('region', $region)
                ->groupBy('seed_variety')
                ->orderBy('seed_total_volume', 'DESC')
            )
            ->addColumn('volume', function($row){
                return number_format($row->seed_total_volume)." bag(s)";
            })
            ->addIndexColumn()
            ->make(true);
        
        }else if($region != "0" && $province != "0" && $municipality == "0"){
            return Datatables::of(DB::connection('rcep_reports_db')
                ->table('lib_variety_report')
                ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
                ->where('region', $region)
                ->where('province', $province)
                ->groupBy('seed_variety')
                ->orderBy('seed_total_volume', 'DESC')
            )
            ->addColumn('volume', function($row){
                return number_format($row->seed_total_volume)." bag(s)";
            })
            ->addIndexColumn()
            ->make(true);

        }else if($region != "0" && $province != "0" && $municipality != "0"){
            return Datatables::of(DB::connection('rcep_reports_db')
                ->table('lib_variety_report')
                ->select(DB::raw('SUM(total_volume) as seed_total_volume'),'seed_variety')
                ->where('region', $region)
                ->where('province', $province)
                ->where('municipality', $municipality)
                ->groupBy('seed_variety')
                ->orderBy('seed_total_volume', 'DESC')
            )
            ->addColumn('volume', function($row){
                return number_format($row->seed_total_volume)." bag(s)";
            })
            ->addIndexColumn()
            ->make(true);
        } */

    }

    // Seed tag tracker
    public function seedtags(){
        $model = new DeliveryInspect();
        
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $delivery_provinces = $model->_delivery_provinces();
        }else{
            $delivery_provinces = $model->_delivery_provinces_filtered();
        }

        return view('seedtag.index')
            ->with(compact('delivery_provinces'));;
    }

    public function get_muni($province) {
        // Get municipalities
        $model = new DeliveryInspect();
        $delivery_municipalities = $model->_delivery_municipalities($province);

        echo json_encode($delivery_municipalities);
    }

    public function seed_tag_tracker(Request $request){
        return Datatables::of(
            DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('batchTicketNumber', $request->batch_number)
            ->orderBy('dateSampled', 'DESC')
        )->addColumn('seed_weight_value', function($table_data) {
          $float_seed_weight = (float) $table_data->bagWeight;
          return number_format($float_seed_weight,2);  
        })
        ->make(true);
    }
}

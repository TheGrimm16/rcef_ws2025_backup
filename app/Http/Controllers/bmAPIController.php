<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;
use Hash;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;
use App\utility;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class bmAPIController extends Controller
{
    public function unlinkExcelExport(){

        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $files = ["ms_2024-03-01_2024-05-22.xlsx","ms_2024-03-01_2024-05-26.xlsx","ms_2024-03-01_2024-05-31.xlsx"];
        foreach($files as $file)
        {
            $filePath = $documentRoot . '/rcef_ws2025/public/reports/excel_export/'.$file;
            
            if (file_exists($filePath)) {
                unlink($filePath);
            } else {
                echo "File does not exist.";
            }
        }
    }

    public function updateKPdata($season){
        $season = $season.'_';

		$pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//updateKPdata.py');

		$escapedSeason = escapeshellarg($season);
		$command = "$pythonPath \"$scriptPath\" $escapedSeason";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            return $data;
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

    }

    public function updateMoa(){
        $season = $GLOBALS['season_prefix'];

		$pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//updateMoa.py');

		$escapedSeason = escapeshellarg($season);
		$command = "$pythonPath \"$scriptPath\" $escapedSeason";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            return $data;
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

    }

    //coopApp
    public function coopAppLogin($login_id,$password){
        $getUserInfo = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
                ->where('username', $login_id)
            ->first();
        
        if($getUserInfo){
            if(Hash::check($password, $getUserInfo->password)){
                $getUserInfo = json_encode($getUserInfo);
                return $getUserInfo;
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }

    public function getCoops()
    {
        $coops = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
            ->where('isActive',1)
            ->orderBy('coopName', 'ASC')
            ->get();

        return ($coops);
    }

    public function getCommitments($accred)
    {
        $getCommitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
        ->where('coop_Id',$accred)
        ->orderBy('region_name', 'ASC')
        ->orderBy('seed_variety', 'ASC')
        ->get();
        
        if($getCommitments)
        {
            return $getCommitments;
        }
        else
        {
            return 0;
        }
    }
    public function getSeedVariety()
    {
        $variety_list = DB::table($GLOBALS['season_prefix'].'seed_seed.seed_characteristics')->groupBy('variety')->get();

        return ($variety_list);
    }

    public function getRegions()
    {
        $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')->where('regionName','NOT LIKE','Programmer Region')->orderBy('region_sort', 'ASC')->groupBy('regionName')->get();

        return ($regions);
    }

    public function getStations()
    {
        $stations = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')->orderBy('stationID', 'ASC')->groupBy('station')->get();

        return ($stations);
    }

    public function addCommitment(Request $request)
    {
        $commitmentData = json_decode($request->getContent());
        // dd($commitmentData);

        $getCoopDetails = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('coopId', $commitmentData[0]->coop_id)
        ->first();

        $coopName = $getCoopDetails->coopName;
        $coopAccred = $getCoopDetails->accreditation_no;
        $moaNumber = $getCoopDetails->current_moa;
        $dateAdded = Carbon::now()->format('Y-m-d H:i:s');

        if($commitmentData[0]->coop_id == "68" || $commitmentData[0]->coop_id == 68)
        {
            $checkIfExists = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('coop_Id', $commitmentData[0]->coop_id)
            ->where('station', $commitmentData[0]->selectedStation)
            ->where('seed_variety', $commitmentData[0]->selectedVariety)
            ->get();

            if($checkIfExists)
            {
                return json_encode('Commitment already exists for this Region and Seed Variety.');
            }
            else
            {                        
                $commitmentID = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                ->insertGetId([
                    "coopID" => $commitmentData[0]->coop_id,
                    "category" => $commitmentData[0]->selectedCategory,
                    "commitment_variety" => $commitmentData[0]->selectedVariety,
                    "commitment_value" => $commitmentData[0]->currentVolume,
                    "addedBy" => $commitmentData[0]->user,
                    "moa_number" => $moaNumber
                ]);

                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->insert([
                    "commitmentID" => $commitmentID,
                    "coop_Id" => $commitmentData[0]->coop_id,
                    "coop_name" => $coopName,
                    "accreditation_no" => $coopAccred,
                    "region_name" => 'ANY REGION',
                    "station" => $commitmentData[0]->selectedStation,
                    "category" => $commitmentData[0]->selectedCategory,
                    "seed_variety" => $commitmentData[0]->selectedVariety,
                    "volume" => $commitmentData[0]->currentVolume,
                    "date_added" => $dateAdded
                ]);
    

                
                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->where('volume', 0)
                ->orWhere('volume', 'LIKE', '')
                ->delete();

                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                ->where('commitment_value', 0)
                ->orWhere('commitment_value', 'LIKE', '')
                ->delete();
                
                $totalCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->selectRaw("SUM(volume) AS total_commitment")
                ->where('coop_Id', $commitmentData[0]->coop_id)
                ->value('total_commitment');    
        
                // dd($totalCommitment);
        
                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')
                    ->where('coopID', $commitmentData[0]->coop_id)
                    ->update([
                        "total_value" => $totalCommitment
                    ]);
                return json_encode('Commitment added successfully.');
            }
        }
        else
        {
            $checkIfExists = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->where('coop_Id', $commitmentData[0]->coop_id)
            ->where('region_name', $commitmentData[0]->selectedRegion)
            ->where('seed_variety', $commitmentData[0]->selectedVariety)
            ->get();

            if($checkIfExists)
            {
                return json_encode('Commitment already exists for this Region and Seed Variety.');
            }
            else
            {
                $commitmentID = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                ->insertGetId([
                    "coopID" => $commitmentData[0]->coop_id,
                    "category" => $commitmentData[0]->selectedCategory,
                    "commitment_variety" => $commitmentData[0]->selectedVariety,
                    "commitment_value" => $commitmentData[0]->currentVolume,
                    "addedBy" => $commitmentData[0]->user,
                    "moa_number" => $moaNumber
                ]);
                
                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->insert([
                    "commitmentID" => $commitmentID,
                    "coop_Id" => $commitmentData[0]->coop_id,
                    "coop_name" => $coopName,
                    "accreditation_no" => $coopAccred,
                    "region_name" => $commitmentData[0]->selectedRegion,
                    "station" => '',
                    "category" => $commitmentData[0]->selectedCategory,
                    "seed_variety" => $commitmentData[0]->selectedVariety,
                    "volume" => $commitmentData[0]->currentVolume,
                    "date_added" => $dateAdded,
                ]);
    

                
                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->where('volume', 0)
                ->orWhere('volume', 'LIKE', '')
                ->delete();

                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                ->where('commitment_value', 0)
                ->orWhere('commitment_value', 'LIKE', '')
                ->delete();
                
                $totalCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->selectRaw("SUM(volume) AS total_commitment")
                ->where('coop_Id', $commitmentData[0]->coop_id)
                ->value('total_commitment');    
        
                // dd($totalCommitment);
        
                DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')
                    ->where('coopID', $commitmentData[0]->coop_id)
                    ->update([
                        "total_value" => $totalCommitment
                    ]);
                return json_encode('Commitment added successfully.');
            }
        }
    }

    public function updateCommitment(Request $request)
    {
        $commitmentData = json_decode($request->getContent());
        // $jsonData = '[{"user":"bm.delossantos","coop_id":"24","region":"WESTERN VISAYAS","station":"","category":"RCEF","seed_variety":"NSIC Rc 160","volume":"239","selectedRegion":"WESTERN VISAYAS","selectedStation":"","selectedCategory":"RCEF","selectedVariety":"NSIC Rc 160","currentVolume":0}]';
        // $commitmentData = json_decode($jsonData);
    
        $tempVolume = 0;
        $dateAdded = Carbon::now()->format('Y-m-d H:i:s');

        $getCoop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('coopId',$commitmentData[0]->coop_id)
        ->pluck('accreditation_no');

        $getDelivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
        ->where('coopAccreditation',$getCoop)
        ->where('seedVariety',$commitmentData[0]->seed_variety)
        ->pluck('seedTag');

        if($commitmentData[0]->coop_id == "68" || $commitmentData[0]->coop_id == 68)
        {
            $getActualDelivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(DB::raw("SUM(totalBagCount) as bags"))
            ->whereIn('seedTag',$getDelivery)
            ->where('seedVariety',$commitmentData[0]->seed_variety)
            ->first();

            $getTotal = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->select(DB::raw("SUM(volume) as bags"))
            ->where('coop_Id', $commitmentData[0]->coop_id)
            ->value('bags');

            $tempVolume = $getTotal - $commitmentData[0]->currentVolume;

        }
        else
        {
            $getActualDelivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->select(DB::raw("SUM(totalBagCount) as bags"))
            ->whereIn('seedTag',$getDelivery)
            ->where('seedVariety',$commitmentData[0]->seed_variety)
            ->where('region',$commitmentData[0]->selectedRegion)
            ->first();

            $getTotal = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->select(DB::raw("SUM(volume) as bags"))
            ->where('coop_Id', $commitmentData[0]->coop_id)
            ->value('bags');

            $tempVolume = $getTotal - $commitmentData[0]->currentVolume;
        }
        
        if($getActualDelivery)
            {
                if($getActualDelivery->bags > $tempVolume)
                {
                    return json_encode("Volume cannot be less than ".$getActualDelivery->bags); 
                }
                else
                {
                    if($commitmentData[0]->coop_id == "68" || $commitmentData[0]->coop_id == 68)
                    {
                        $regionalCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                        ->where('coop_Id', $commitmentData[0]->coop_id)
                        ->where('region_name', 'ANY REGION')
                        ->where('station', $commitmentData[0]->station)
                        ->where('category', $commitmentData[0]->category)
                        ->where('seed_variety', $commitmentData[0]->seed_variety)
                        ->where('volume', $commitmentData[0]->volume)
                        ->get();
                        
                        foreach($regionalCommitment as $commitment)
                        {
                            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                            ->where('id', $commitment->id)
                            ->update([
                                "region_name" => $commitmentData[0]->selectedRegion,
                                "station" => $commitmentData[0]->selectedStation,
                                "category" => $commitmentData[0]->selectedCategory,
                                "seed_variety" => $commitmentData[0]->selectedVariety,
                                "volume" => $commitmentData[0]->currentVolume
                            ]);
                
                            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                            ->where('id', $commitment->commitmentID)
                            ->update([
                                "category" => $commitmentData[0]->selectedCategory,
                                "commitment_variety" => $commitmentData[0]->selectedVariety,
                                "commitment_value" => $commitmentData[0]->currentVolume
                            ]);

                        }
                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                        ->where('volume', 0)
                        ->orWhere('volume', 'LIKE', '')
                        ->delete();

                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                        ->where('commitment_value', 0)
                        ->orWhere('commitment_value', 'LIKE', '')
                        ->delete();
                
                        $totalCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                        ->selectRaw("SUM(volume) AS total_commitment")
                        ->where('coop_Id', $commitmentData[0]->coop_id)
                        ->value('total_commitment');
                
                        // dd($totalCommitment);
                
                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')
                            ->where('coopID', $commitmentData[0]->coop_id)
                            ->update([
                                "total_value" => $totalCommitment
                            ]);

                            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_adjustment_logs')
                            ->insert([
                                "coopId" => $commitment->commitmentID,
                                "region_from" => $commitmentData[0]->station,
                                "region_to" => $commitmentData[0]->selectedStation,
                                "seedvariety_from" => $commitmentData[0]->seed_variety,
                                "seedvariety_to" => $commitmentData[0]->selectedVariety,
                                "volume_from" => $commitmentData[0]->volume,
                                "volume_to" => $commitmentData[0]->currentVolume,
                                "category_from" => $commitmentData[0]->category,
                                "category_to" => $commitmentData[0]->selectedCategory,
                                "tbl_commitment_id" => $commitment->commitmentID,
                                "user_updated" => $commitmentData[0]->user,
                                "date_created" => $dateAdded,
                            ]);

                            return json_encode('Commitment Updated');
                    }
                    else
                    {
                        $regionalCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                        ->where('coop_Id', $commitmentData[0]->coop_id)
                        ->where('region_name', $commitmentData[0]->region)
                        ->where('category', $commitmentData[0]->category)
                        ->where('seed_variety', $commitmentData[0]->seed_variety)
                        ->where('volume', $commitmentData[0]->volume)
                        ->get();
                        
                        foreach($regionalCommitment as $commitment)
                        {
                            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                            ->where('id', $commitment->id)
                            ->update([
                                "region_name" => $commitmentData[0]->selectedRegion,
                                "category" => $commitmentData[0]->selectedCategory,
                                "seed_variety" => $commitmentData[0]->selectedVariety,
                                "volume" => $commitmentData[0]->currentVolume
                            ]);
                
                            DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                            ->where('id', $commitment->commitmentID)
                            ->update([
                                "category" => $commitmentData[0]->selectedCategory,
                                "commitment_variety" => $commitmentData[0]->selectedVariety,
                                "commitment_value" => $commitmentData[0]->currentVolume
                            ]);

                        }
                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                        ->where('volume', 0)
                        ->orWhere('volume', 'LIKE', '')
                        ->delete();

                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                        ->where('commitment_value', 0)
                        ->orWhere('commitment_value', 'LIKE', '')
                        ->delete();
                
                        $totalCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                        ->selectRaw("SUM(volume) AS total_commitment")
                        ->where('coop_Id', $commitmentData[0]->coop_id)
                        ->value('total_commitment');
                
                        // dd($totalCommitment);
                
                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment')
                            ->where('coopID', $commitmentData[0]->coop_id)
                            ->update([
                                "total_value" => $totalCommitment
                            ]);

                            
                        DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_adjustment_logs')
                        ->insert([
                            "coopId" => $commitment->commitmentID,
                            "region_from" => $commitmentData[0]->region,
                            "region_to" => $commitmentData[0]->selectedRegion,
                            "seedvariety_from" => $commitmentData[0]->seed_variety,
                            "seedvariety_to" => $commitmentData[0]->selectedVariety,
                            "volume_from" => $commitmentData[0]->volume,
                            "volume_to" => $commitmentData[0]->currentVolume,
                            "category_from" => $commitmentData[0]->category,
                            "category_to" => $commitmentData[0]->selectedCategory,
                            "tbl_commitment_id" => $commitment->commitmentID,
                            "user_updated" => $commitmentData[0]->user,
                            "date_created" => $dateAdded,
                        ]);
                        return json_encode('Commitment Updated');
                    }
                }
            }


    }


    public function addSeedGrower_index()
    {
        $season = $GLOBALS['season_prefix'];

        $getCoops = DB::table($season.'rcep_seed_cooperatives.tbl_cooperatives')
        ->where('isActive',1)
        ->orderBy('coopName', 'ASC')
        ->get();
        
        return view('seed_grower.index', compact('getCoops'));

    }

    public function saveSeedGrower(Request $request)
    {
        $season = $GLOBALS['season_prefix'];

        $ext = $request->extName;
        
        if(substr($ext, 0, 1) == 'J' || substr($ext, 0, 1) == 'j')
        {
            $ext = 'Jr.';
        }
        else if(substr($ext, 0, 1) == 'S' || substr($ext, 0, 1) == 's')
        {
            $ext = 'Sr.';
        }
        else if(substr($ext, 0, 1) == 'I' || substr($ext, 0, 1) == 'i' || substr($ext, 0, 1) == 'L' || substr($ext, 0, 1) == 'l')
        {
            $ext = 'I';
        }
        else if(substr($ext, 0, 1) == 'II' || substr($ext, 0, 2) == 'ii' || substr($ext, 0, 1) == 'LL' || substr($ext, 0, 1) == 'll')
        {
            $ext = 'II';
        }
        else if(substr($ext, 0, 1) == 'III' || substr($ext, 0, 3) == 'iii' || substr($ext, 0, 1) == 'LLL' || substr($ext, 0, 1) == 'LLL')
        {
            $ext = 'III';
        }
        else
        {
            $ext = '';
        }

        $name1 = $request->firstName.'%'.$request->lastName;
        $name2 = $request->lastName.'%'.$request->firstName.'%';
        $name3 = $request->firstName.'%'.$request->lastName.'%'.substr($ext, 0, 1).'%';
        $name4 = $request->lastName.'%'.$request->firstName.'%'.substr($ext, 0, 1).'%';
        $coop = $request->coop;
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');

        if($ext == '')
        {
            $checkIfExists = DB::table($season.'rcep_delivery_inspection.tbl_seed_grower')
            ->where(function ($query) use ($name1, $coop) {
                $query->where('full_name', 'LIKE', $name1)
                      ->where('coop_accred', 'LIKE', $coop);
            })
            ->orWhere(function ($query) use ($name2, $coop) {
                $query->where('full_name', 'LIKE', $name2)
                      ->where('coop_accred', 'LIKE', $coop);
            })
            ->get();
        }
        else
        {

            // dd($name3);
            $checkIfExists = DB::table($season.'rcep_delivery_inspection.tbl_seed_grower')
            ->where(function ($query) use ($name3, $coop) {
                $query->where('full_name', 'LIKE', $name3)
                      ->where('coop_accred', 'LIKE', $coop);
            })
            ->orWhere(function ($query) use ($name4, $coop) {
                $query->where('full_name', 'LIKE', $name4)
                      ->where('coop_accred', 'LIKE', $coop);
            })
            ->get();

            // dd($checkIfExists);
        }


        if($checkIfExists)
        {
            return 1;
        }
        else
        {
            $fullname = $request->firstName.' '.$request->middleName.' '.$request->lastName.' '.$ext;
            $fullname = str_replace('  ', ' ', $fullname);
            $saveSeedGrower = DB::table($season.'rcep_delivery_inspection.tbl_seed_grower')
            ->insert([
                'coop_accred' => $request->coop,
                'is_active' => 1,
                'is_block' => 0,
                'fname' => $request->firstName,
                'mname' => $request->middleName,
                'lname' => $request->lastName,
                'extension' => $ext,
                'full_name' => rtrim($fullname),
                'sync_date' => $timestamp
            ]);
            return 0;
        }
    }

    public function downloadFarmers()
    {
        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		$pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//API//downloadFarmers.py');

		$command = "$pythonPath \"$scriptPath\"";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            return $data;
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}
    }


    public function deletePreReg_index()
    {
        $season = $GLOBALS['season_prefix'];

        $provMuni = [];

        $getPrvs = DB::table($season.'rcep_paymaya.sed_verified')
        ->select(DB::raw("REPLACE(claiming_prv,'-','') as prv"))
        ->distinct()
        ->get();


        foreach($getPrvs as $prv)
        {
            $getPrvDetails = DB::table($season.'rcep_delivery_inspection.lib_prv')
            ->where('prv', $prv->prv)
            ->first();

            array_push($provMuni, [
                'regionName' => $getPrvDetails->regionName,
                'province' => $getPrvDetails->province,
                'municipality' => $getPrvDetails->municipality,
                'prv' => $getPrvDetails->prv,
                'claiming_prv' => $getPrvDetails->temp_prv,
            ]);
        }
        return view('deletePreReg.index', compact('provMuni'));

    }

    public function deletePreRegProfile(Request $request)
    {
        $season = $GLOBALS['season_prefix'];

        $claiming_prv = $request->provMuni;
        $rsbsaNo = $request->rsbsaNo;
        $lastName = $request->lastName;
        $firstName = $request->firstName;
        $middleName = $request->middleName;
        $extName = $request->extName;

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//deletePreReg.py');

		$escapedSeason = escapeshellarg($season);
        $escapedClaimingPrv = escapeshellarg($claiming_prv);
        $escapedRsbsaNo = escapeshellarg($rsbsaNo);
        $escapedLastName = escapeshellarg($lastName);
        $escapedFirstName = escapeshellarg($firstName);
        $escapedMiddleName = escapeshellarg($middleName);
        $escapedExtName = escapeshellarg($extName);
        $command = "$pythonPath \"$scriptPath\" $escapedSeason $escapedClaimingPrv $escapedRsbsaNo $escapedLastName $escapedFirstName $escapedMiddleName $escapedExtName";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            // dd($data);
            return $data;
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

    }

    public function getLibPrv($val)
    {
        if($val == 'lib')
        {
            $getLibPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->orderBy('region_sort', 'ASC')
            ->get();
            return $getLibPrv;
        }
        else if($val == 'all')
        {
            $getLibPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select("psa_code","updated_psa_code","regCode","regionName")
            ->groupBy('regCode')
            ->orderBy('region_sort', 'ASC')
            ->get();
            return $getLibPrv;
        }
        else
        {
            $checking = strlen($val);
            if($checking == 2)
            {
                $getLibPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select("psa_code","updated_psa_code","regCode","regionName","provCode","province")
                ->where('regCode', $val)
                ->groupBy('regCode','provCode')
                ->orderBy('region_sort', 'ASC')
                ->get();
                return $getLibPrv;
            }
            else if($checking == 4)
            {
                $getLibPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select("psa_code","updated_psa_code","regCode","regionName","provCode","province","munCode","municipality")
                ->where('prv_code', $val)
                ->groupBy('regCode','provCode','munCode')
                ->orderBy('region_sort', 'ASC')
                ->get();
                return $getLibPrv;
            }
            else if($checking == 6)
            {
                $getLibPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->select("psa_code","updated_psa_code","regCode","regionName","provCode","province","munCode","municipality")
                ->where('prv', $val)
                ->groupBy('regCode','provCode','munCode')
                ->orderBy('region_sort', 'ASC')
                ->get();
                return $getLibPrv;
            }
            else
            {
                return ('Invalid code.');
            }

        }
    }


    public function bepToReg_index()
    {
        $season = $GLOBALS['season_prefix'];

        $provMuni = [];

        $getPrvs = DB::table($season.'rcep_delivery_inspection.lib_prv')
        ->get();


        foreach($getPrvs as $prv)
        {

            array_push($provMuni, [
                'regionName' => $prv->regionName,
                'province' => $prv->province,
                'municipality' => $prv->municipality,
                'prv' => $prv->prv,
                'claiming_prv' => $prv->temp_prv,
            ]);
        }
        return view('bepToReg.index', compact('provMuni'));

    }


    public function bepToRegUpdate(Request $request)
    {
        $season = $GLOBALS['season_prefix'];

        $claiming_prv = $request->provMuni;
        $rsbsaNo = $request->rsbsaNo;
        $lastName = $request->lastName;
        $firstName = $request->firstName;
        $middleName = $request->middleName;
        $extName = $request->extName;
        // dd($claiming_prv, $rsbsaNo, $lastName, $firstName, $middleName, $extName);

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//bm//bepToReg.py');

		$escapedSeason = escapeshellarg($season);
        $escapedClaimingPrv = escapeshellarg($claiming_prv);
        $escapedRsbsaNo = escapeshellarg($rsbsaNo);
        $escapedLastName = escapeshellarg($lastName);
        $escapedFirstName = escapeshellarg($firstName);
        $escapedMiddleName = escapeshellarg($middleName);
        $escapedExtName = escapeshellarg($extName);
        $command = "$pythonPath \"$scriptPath\" $escapedSeason $escapedClaimingPrv $escapedRsbsaNo $escapedLastName $escapedFirstName $escapedMiddleName $escapedExtName";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
            // dd($data);
            return $data;
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}

    }

    public function get_far()
    {
        // base_path() = C:/xampp/htdocs/rcef_ws2025
        $xamppPath = dirname(dirname(base_path())); 
        dd($xamppPath);
        // $xamppPath = "C:/Apache24";

        // append your folder
        $targetPath = $xamppPath . '/rcef_unique_checker';

        $files = File::files($targetPath);   // only files
        $folders = File::directories($targetPath); // only folders

        dd([
            'files' => $files,
            'folders' => $folders
        ]);
    }



}

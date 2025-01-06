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

class KPEncoderMonitoringController extends Controller
{
    public function home_ui(){
        $allData = [];
        $overallData = [];
        $encoded = DB::table('kp_distribution.kp_distribution_app')
            ->select(DB::raw('encodedBy as Encoder, count(encodedBy) as Total_Encoded'))
            ->groupBy('encodedBy')
            ->orderBy('Total_Encoded', 'desc')
            ->get();

        foreach($encoded as $encode)
        {
            
            $getName1 = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
                ->where('username',$encode->Encoder)
                ->first();

            if(!$getName1)
            {
                continue;
            }
            $getContractDate = DB::table('kp_distribution.kp_encoders')
            ->where('userId',$encode->Encoder)
            ->first();

            if($getContractDate->status == 0)
            {
                continue;
            }

            $getQuota = DB::table('kp_distribution.kp_encoding_quota')
            ->where('month','LIKE',$getContractDate->contractStartMonth)
            ->where('year','LIKE',$getContractDate->contractStartYear)
            ->first();

            // if(!$getQuota)
            // {
            //     dd($encode);
            // }

            $getTotalQuota = DB::table('kp_distribution.kp_encoding_quota')
            ->where('sort','>=',$getQuota->sort)
            ->sum('quota');

            $maxSort = DB::table('kp_distribution.kp_encoding_quota')
                ->max('sort');

            $getTotalQuotaPrev = DB::table('kp_distribution.kp_encoding_quota')
                ->where('sort', '>=', $getQuota->sort)
                ->where('sort', '<', $maxSort)
                ->sum('quota');

            if(!$getTotalQuotaPrev)
            {
                $getTotalQuotaPrev = 0;
            }

            $contractStart = $getContractDate->contractStartMonth.' '.$getContractDate->contractStartYear;

            // dd($contractStart);
            $fullName1 = $getName1->firstName.' '.$getName1->middleName.' '.$getName1->lastName.' '.$getName1->extName;

            if($encode->Encoder == 'cq.bulan')
            {
                $getTotalQuota = $getTotalQuota - 4000;
                $getTotalQuotaPrev = $getTotalQuotaPrev - 4000;
            }

            $overallData[] = [
                "Full_Name" => $fullName1,
                "Encoder" => $encode->Encoder,
                "Total_Encoded" => $encode->Total_Encoded,
                "Quota" => $getTotalQuota,
                "QuotaPrev" => $getTotalQuotaPrev,
                "First_Contract" => $contractStart
            ];
            // dd($overallData);
        }

        

        
        // dd($allData);
        return view('KPDistribution.encoderMonitor',
        compact(
        'overallData'
        ));    
    }
    


    public function getOverallData(){

        $overallData = DB::table('kp_distribution.kp_distribution_app')
            ->select(DB::raw('Season, count(encodedBy) as Total_Encoded'))
            ->groupBy('season')
            ->orderBy('Total_Encoded', 'desc')
            ->get();

        $sortedData = collect($overallData)->sortByDesc(function ($item) {
            // Extract the year and season type (DS/WS)
            preg_match('/(\d+)$/', $item->Season, $matches);
            $year = $matches[0];
            $seasonType = substr($item->Season, 0, -strlen($year));
        
            // Assigning a custom sorting weight based on season type
            $weight = ($seasonType === 'DS') ? 0 : 1;
        
            // Sorting first by year in descending order and then by season type weight
            return [$year, $weight];
        })->toArray();

        $total = 0;
        foreach ($sortedData as $data) {
            $total += $data->Total_Encoded;
        }

        $sortedData[] = [
            'Season' => 'OVERALL TOTAL',
            'Total_Encoded' => $total
        ];
        // dd($overallData,$sortedData);

        $sortedData = collect($sortedData);

        return Datatables::of($sortedData)
        ->make(true);

    }
    public function loadKpEncoderBreakdown(){
        $getMonths = DB::table('kp_distribution.kp_distribution_app')
        ->select(DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(time_stamp, ' ', 2), ' ', -1) AS month_name"), DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(time_stamp, ' ', 4), ' ', -1) AS year"))
        ->groupBy('month_name')
        ->orderBy('month_name','DESC')
        ->get();
        
        foreach($getMonths as $month){
            $getCount = DB::table('kp_distribution.kp_distribution_app')
            ->select(DB::raw('encodedBy as Encoder, count(encodedBy) as Total_Encoded, season as Season'))
            ->where('time_stamp','LIKE','%'.$month->month_name.'%')
            ->groupBy('encodedBy')
            ->groupBy('season')
            ->get();

            switch($month->month_name){
                case 'Jan':
                    $month->month_name = 'January';
                    break;
                case 'Feb':
                    $month->month_name = 'February';
                    break;
                case 'Mar':
                    $month->month_name = 'March';
                    break;
                case 'Apr':
                    $month->month_name = 'April';
                    break;
                case 'May':
                    $month->month_name = 'May';
                    break;
                case 'Jun':
                    $month->month_name = 'June';
                    break;
                case 'Jul':
                    $month->month_name = 'July';
                    break;
                case 'Aug':
                    $month->month_name = 'August';
                    break;
                case 'Sep':
                    $month->month_name = 'September';
                    break;
                case 'Oct':
                    $month->month_name = 'October';
                    break;
                case 'Nov':
                    $month->month_name = 'November';
                    break;
                case 'Dec':
                    $month->month_name = 'December';
                    break;
            }

            foreach ($getCount as $count){
                $getName = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
                ->where('username',$count->Encoder)
                ->first();

                if(!$getName)
                {
                    continue;
                }

                $getContractDate = DB::table('kp_distribution.kp_encoders')
                ->where('userId',$count->Encoder)
                ->first();

                if($getContractDate->status == 0)
                {
                    continue;
                }
                
                $fullName = $getName->firstName.' '.$getName->middleName.' '.$getName->lastName.' '.$getName->extName;
                $allData[] = [
                    "Full_Name" => $fullName,
                    "Encoder" => $count->Encoder,
                    "Season" => $count->Season,
                    "Total_Encoded" => $count->Total_Encoded,
                    "Month_Encoded" => $month->month_name.' '.$month->year
                ];
            }
        }

        usort($allData, function($a, $b) {
            // First, sort by Encoder name
            $encoderComparison = strcmp($a['Encoder'], $b['Encoder']);
    
            // If Encoder names are equal, sort by Season
            if ($encoderComparison === 0) {
                return strcmp($b['Month_Encoded'], $a['Month_Encoded']);
            }
    
            return $encoderComparison;
        });

        $allData = collect($allData);

        return Datatables::of($allData)
        ->make(true);
    }

    public function getSeasons(Request $request)
    {
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

        return $seasons;
    }

    public function getEncoders(Request $request)
    {
        $getEncoders = DB::table("kp_distribution.kp_distribution_app")
        ->select('encodedBy')
        ->groupBy('encodedBy')
        ->get();

        foreach($getEncoders as $encoder)
        {
            $getName = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
                ->where('username',$encoder->encodedBy)
                ->first();

            $fullName = $getName->firstName.' '.$getName->middleName.' '.$getName->lastName.' '.$getName->extName;
            
            $userData[] = [
                "fullName" => $fullName,
                "userName" => $encoder->encodedBy,
            ];
        }
        return $userData;
    }

    public function exportStatistics($season,$encoder,$date1,$date2)
    {
        // $requestData = $request->all();
        // dd($requestData);
        // dd($season,$encoder,$date1,$date2);
        // $dates = explode(' - ',$request->date);
        $date1 = Carbon::createFromFormat('m-d-Y', $date1)->format('Y-m-d');
        $date2 = Carbon::createFromFormat('m-d-Y', $date2)->format('Y-m-d');
        // $date1 = $dates[0];
        // $date2 = $dates[1];
        // dd($date1,$date2);
        $getEncodedData = DB::table("kp_distribution.kp_distribution_app")
        ->select(
            '*',
            DB::raw('IF(LENGTH(sex) > 0, IF(LEFT(sex,1)="M","MALE","FEMALE"), "") as sex_1')
        )
        ->where('encodedBy', 'LIKE',$encoder)
        ->where('season', 'LIKE',$season)
        // ->whereBetween(DB::raw("STR_TO_DATE((SUBSTRING(time_stamp, 5,11)),'%M %d %Y')"), [$request->date1, $request->date2])
        ->where(DB::raw("STR_TO_DATE((SUBSTRING(time_stamp, 5,11)),'%M %d %Y')"), '>=',$date1)
        ->where(DB::raw("STR_TO_DATE((SUBSTRING(time_stamp, 5,11)),'%M %d %Y')"), '<=',$date2)
        ->get();

        if(!$getEncodedData)
        {
            return '
                <script>alert("No data available."); window.close()</script>
            ';
        }
        // dd($getEncodedData);
        $getInfo = array();

        foreach($getEncodedData as $data)
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

            // $age = '';

            // if($data->birthdate && $data->birthdate !='N/A')
            // {
            // $birthdateString = Carbon::parse($data->birthdate);
            // $birthdateString = $birthdateString->format('m/d/Y');
            // $currentDate = Carbon::now();
            // $dateOfBirth = Carbon::createFromFormat('m/d/Y', $birthdateString);
            // $age = $currentDate->diffInYears($dateOfBirth);
            // }

            // $sex = substr($data->sex,0,1);
            // // dd($sex);

            // if($sex == 'M' || $sex == 'm')
            // {
            //     $sex = 'MALE';
            // }
            // else if($sex == 'F' || $sex == 'f')
            // {
            //     $sex = 'FEMALE';
            // }

            array_push($getInfo, array(
                "RSBSA Control Number" => $data->rsbsa_control_no,
                "Full Name" => $data->fullName,
                "Sex" => $data->sex_1,
                "Birthdate" => $data->birthdate,
                "Province" => $province,
                "Muncipality" => $municipality,
                "PSG Code" => $getPSGcode->psa_code,
                "KP Kits Received" => $totalKPreceived,
                "Date Encoded" => $data->time_stamp
            ));
        }
        // dd($getInfo);
        $excel_data = json_decode(json_encode($getInfo), true);
        // dd($excel_data);
        $filename = 'KP Distribution Report_'.$encoder.'_'.$season;
        return Excel::create($filename, function($excel) use ($excel_data) {
            $excel->sheet("Farmer Information", function($sheet) use ($excel_data) {
                $sheet->fromArray($excel_data);
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
    
}

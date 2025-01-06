<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Support\Str;
class farmerFinderExtendedApi extends Controller{
    
    public function downloadLibPrv(){
        return DB::table("ds2024rcep_delivery_inspection.lib_prv")
            ->get();
    }

    public function countEncoded(){
        $allData = [];
        $encoded = DB::table('kp_distribution.kp_distribution_app')
            ->select(DB::raw('encodedBy as Encoder, count(encodedBy) as Total_Encoded'))
            ->groupBy('encodedBy')
            ->get();

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
                $allData[] = [
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

        echo "<style>
            th, td {
                padding: 10px; /* Adding padding inside table cells */
            }
            th {
                background-color: white;
            }
            tbody tr:nth-child(odd) {
                background-color: #7bd34e; /* Alternate row color for better readability */
            }
            tbody tr:nth-child(even) {
                background-color: #d3ffce; /* Alternate row color for better readability */
            }
          </style>";

        
          echo "<h2>Overall Statistics</h2>";
          echo "<table style='background-color: black;'>
          <thead style='background-color: white'>
              <tr>
                  <th>Encoder</th>
                  <th>Overall Encoded</th>
              </tr>
          </thead>
          <tbody style='background-color: gray'>";
  
          foreach ($encoded as $index => $item) {
              echo "<tr>
                      <td>".$item->Encoder."</td>
                      <td>".$item->Total_Encoded."</td>
                  </tr>";
          }
  
          echo "</tbody></table>";


          echo "<h2>Breakdown</h2>";
        echo "<table style='background-color: black; margin-top: 20px'>
        <thead style='background-color: white'>
            <tr>
                <th>Encoder</th>
                <th>Season</th>
                <th>Total Encoded</th>
                <th>Month Encoded</th>
            </tr>
        </thead>
        <tbody style='background-color: white'>";

        foreach($allData as $data){
            echo "<tr>
                    <td>".$data['Encoder']."</td>
                    <td>".$data['Season']."</td>
                    <td>".$data['Total_Encoded']."</td>
                    <td>".$data['Month_Encoded']."</td>
                </tr>";
        }

        echo "</tbody></table>";

        
    }
    

    public function getLastEncoded(Request $request){
        $lastEncoded = DB::table('kp_distribution.kp_distribution_app')
            ->where('encodedBy',$request->userName)
            ->orderBy('id','DESC')
            ->get();

        // dd($lastEncoded[0],$lastEncoded[1],$lastEncoded[2],$lastEncoded[3],$lastEncoded[4]);

        return "
        <table style='background-color: black'>
        <thead style='background-color: white'>
          <th>Full Name</th>
          <th>RSBSA Control No.</th>
          <th>Sex</th>
          <th>Birthdate</th>
          <th>Location</th>
          <th>Season</th>
          <th>KP Kits</th>
          <th>TeknoKalendaryo</th>
          <th>Gabay sa Pagpapalayan</th>
          <th>Gabay sa Pagsabog Tanim</th>
          <th>Technical Briefing</th>
          <th>YunPALAYun</th>
          <th>Time and Date Encoded</th>

        </thead>
        <tbody style='background-color: white'>
            <tr>
                <td>".$lastEncoded[0]->fullName."</td>
                <td>".$lastEncoded[0]->rsbsa_control_no."</td>
                <td>".$lastEncoded[0]->sex."</td>
                <td>".$lastEncoded[0]->birthdate."</td>
                <td>".$lastEncoded[0]->location."</td>
                <td>".$lastEncoded[0]->season."</td>
                <td>".$lastEncoded[0]->kpKits."</td>
                <td>".$lastEncoded[0]->calendars."</td>
                <td>".$lastEncoded[0]->testimonials."</td>
                <td>".$lastEncoded[0]->services."</td>
                <td>".$lastEncoded[0]->apps."</td>
                <td>".$lastEncoded[0]->yunpalayun."</td>
                <td>".$lastEncoded[0]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[1]->fullName."</td>
                <td>".$lastEncoded[1]->rsbsa_control_no."</td>
                <td>".$lastEncoded[1]->sex."</td>
                <td>".$lastEncoded[1]->birthdate."</td>
                <td>".$lastEncoded[1]->location."</td>
                <td>".$lastEncoded[1]->season."</td>
                <td>".$lastEncoded[1]->kpKits."</td>
                <td>".$lastEncoded[1]->calendars."</td>
                <td>".$lastEncoded[1]->testimonials."</td>
                <td>".$lastEncoded[1]->services."</td>
                <td>".$lastEncoded[1]->apps."</td>
                <td>".$lastEncoded[1]->yunpalayun."</td>
                <td>".$lastEncoded[1]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[2]->fullName."</td>
                <td>".$lastEncoded[2]->rsbsa_control_no."</td>
                <td>".$lastEncoded[2]->sex."</td>
                <td>".$lastEncoded[2]->birthdate."</td>
                <td>".$lastEncoded[2]->location."</td>
                <td>".$lastEncoded[2]->season."</td>
                <td>".$lastEncoded[2]->kpKits."</td>
                <td>".$lastEncoded[2]->calendars."</td>
                <td>".$lastEncoded[2]->testimonials."</td>
                <td>".$lastEncoded[2]->services."</td>
                <td>".$lastEncoded[2]->apps."</td>
                <td>".$lastEncoded[2]->yunpalayun."</td>
                <td>".$lastEncoded[2]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[3]->fullName."</td>
                <td>".$lastEncoded[3]->rsbsa_control_no."</td>
                <td>".$lastEncoded[3]->sex."</td>
                <td>".$lastEncoded[3]->birthdate."</td>
                <td>".$lastEncoded[3]->location."</td>
                <td>".$lastEncoded[3]->season."</td>
                <td>".$lastEncoded[3]->kpKits."</td>
                <td>".$lastEncoded[3]->calendars."</td>
                <td>".$lastEncoded[3]->testimonials."</td>
                <td>".$lastEncoded[3]->services."</td>
                <td>".$lastEncoded[3]->apps."</td>
                <td>".$lastEncoded[3]->yunpalayun."</td>
                <td>".$lastEncoded[3]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[4]->fullName."</td>
                <td>".$lastEncoded[4]->rsbsa_control_no."</td>
                <td>".$lastEncoded[4]->sex."</td>
                <td>".$lastEncoded[4]->birthdate."</td>
                <td>".$lastEncoded[4]->location."</td>
                <td>".$lastEncoded[4]->season."</td>
                <td>".$lastEncoded[4]->kpKits."</td>
                <td>".$lastEncoded[4]->calendars."</td>
                <td>".$lastEncoded[4]->testimonials."</td>
                <td>".$lastEncoded[4]->services."</td>
                <td>".$lastEncoded[4]->apps."</td>
                <td>".$lastEncoded[4]->yunpalayun."</td>
                <td>".$lastEncoded[4]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[5]->fullName."</td>
                <td>".$lastEncoded[5]->rsbsa_control_no."</td>
                <td>".$lastEncoded[5]->sex."</td>
                <td>".$lastEncoded[5]->birthdate."</td>
                <td>".$lastEncoded[5]->location."</td>
                <td>".$lastEncoded[5]->season."</td>
                <td>".$lastEncoded[5]->kpKits."</td>
                <td>".$lastEncoded[5]->calendars."</td>
                <td>".$lastEncoded[5]->testimonials."</td>
                <td>".$lastEncoded[5]->services."</td>
                <td>".$lastEncoded[5]->apps."</td>
                <td>".$lastEncoded[5]->yunpalayun."</td>
                <td>".$lastEncoded[5]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[6]->fullName."</td>
                <td>".$lastEncoded[6]->rsbsa_control_no."</td>
                <td>".$lastEncoded[6]->sex."</td>
                <td>".$lastEncoded[6]->birthdate."</td>
                <td>".$lastEncoded[6]->location."</td>
                <td>".$lastEncoded[6]->season."</td>
                <td>".$lastEncoded[6]->kpKits."</td>
                <td>".$lastEncoded[6]->calendars."</td>
                <td>".$lastEncoded[6]->testimonials."</td>
                <td>".$lastEncoded[6]->services."</td>
                <td>".$lastEncoded[6]->apps."</td>
                <td>".$lastEncoded[6]->yunpalayun."</td>
                <td>".$lastEncoded[6]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[7]->fullName."</td>
                <td>".$lastEncoded[7]->rsbsa_control_no."</td>
                <td>".$lastEncoded[7]->sex."</td>
                <td>".$lastEncoded[7]->birthdate."</td>
                <td>".$lastEncoded[7]->location."</td>
                <td>".$lastEncoded[7]->season."</td>
                <td>".$lastEncoded[7]->kpKits."</td>
                <td>".$lastEncoded[7]->calendars."</td>
                <td>".$lastEncoded[7]->testimonials."</td>
                <td>".$lastEncoded[7]->services."</td>
                <td>".$lastEncoded[7]->apps."</td>
                <td>".$lastEncoded[7]->yunpalayun."</td>
                <td>".$lastEncoded[7]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[8]->fullName."</td>
                <td>".$lastEncoded[8]->rsbsa_control_no."</td>
                <td>".$lastEncoded[8]->sex."</td>
                <td>".$lastEncoded[8]->birthdate."</td>
                <td>".$lastEncoded[8]->location."</td>
                <td>".$lastEncoded[8]->season."</td>
                <td>".$lastEncoded[8]->kpKits."</td>
                <td>".$lastEncoded[8]->calendars."</td>
                <td>".$lastEncoded[8]->testimonials."</td>
                <td>".$lastEncoded[8]->services."</td>
                <td>".$lastEncoded[8]->apps."</td>
                <td>".$lastEncoded[8]->yunpalayun."</td>
                <td>".$lastEncoded[8]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[9]->fullName."</td>
                <td>".$lastEncoded[9]->rsbsa_control_no."</td>
                <td>".$lastEncoded[9]->sex."</td>
                <td>".$lastEncoded[9]->birthdate."</td>
                <td>".$lastEncoded[9]->location."</td>
                <td>".$lastEncoded[9]->season."</td>
                <td>".$lastEncoded[9]->kpKits."</td>
                <td>".$lastEncoded[9]->calendars."</td>
                <td>".$lastEncoded[9]->testimonials."</td>
                <td>".$lastEncoded[9]->services."</td>
                <td>".$lastEncoded[9]->apps."</td>
                <td>".$lastEncoded[9]->yunpalayun."</td>
                <td>".$lastEncoded[9]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[10]->fullName."</td>
                <td>".$lastEncoded[10]->rsbsa_control_no."</td>
                <td>".$lastEncoded[10]->sex."</td>
                <td>".$lastEncoded[10]->birthdate."</td>
                <td>".$lastEncoded[10]->location."</td>
                <td>".$lastEncoded[10]->season."</td>
                <td>".$lastEncoded[10]->kpKits."</td>
                <td>".$lastEncoded[10]->calendars."</td>
                <td>".$lastEncoded[10]->testimonials."</td>
                <td>".$lastEncoded[10]->services."</td>
                <td>".$lastEncoded[10]->apps."</td>
                <td>".$lastEncoded[10]->yunpalayun."</td>
                <td>".$lastEncoded[10]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[11]->fullName."</td>
                <td>".$lastEncoded[11]->rsbsa_control_no."</td>
                <td>".$lastEncoded[11]->sex."</td>
                <td>".$lastEncoded[11]->birthdate."</td>
                <td>".$lastEncoded[11]->location."</td>
                <td>".$lastEncoded[11]->season."</td>
                <td>".$lastEncoded[11]->kpKits."</td>
                <td>".$lastEncoded[11]->calendars."</td>
                <td>".$lastEncoded[11]->testimonials."</td>
                <td>".$lastEncoded[11]->services."</td>
                <td>".$lastEncoded[11]->apps."</td>
                <td>".$lastEncoded[11]->yunpalayun."</td>
                <td>".$lastEncoded[11]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[12]->fullName."</td>
                <td>".$lastEncoded[12]->rsbsa_control_no."</td>
                <td>".$lastEncoded[12]->sex."</td>
                <td>".$lastEncoded[12]->birthdate."</td>
                <td>".$lastEncoded[12]->location."</td>
                <td>".$lastEncoded[12]->season."</td>
                <td>".$lastEncoded[12]->kpKits."</td>
                <td>".$lastEncoded[12]->calendars."</td>
                <td>".$lastEncoded[12]->testimonials."</td>
                <td>".$lastEncoded[12]->services."</td>
                <td>".$lastEncoded[12]->apps."</td>
                <td>".$lastEncoded[12]->yunpalayun."</td>
                <td>".$lastEncoded[12]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[13]->fullName."</td>
                <td>".$lastEncoded[13]->rsbsa_control_no."</td>
                <td>".$lastEncoded[13]->sex."</td>
                <td>".$lastEncoded[13]->birthdate."</td>
                <td>".$lastEncoded[13]->location."</td>
                <td>".$lastEncoded[13]->season."</td>
                <td>".$lastEncoded[13]->kpKits."</td>
                <td>".$lastEncoded[13]->calendars."</td>
                <td>".$lastEncoded[13]->testimonials."</td>
                <td>".$lastEncoded[13]->services."</td>
                <td>".$lastEncoded[13]->apps."</td>
                <td>".$lastEncoded[13]->yunpalayun."</td>
                <td>".$lastEncoded[13]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[14]->fullName."</td>
                <td>".$lastEncoded[14]->rsbsa_control_no."</td>
                <td>".$lastEncoded[14]->sex."</td>
                <td>".$lastEncoded[14]->birthdate."</td>
                <td>".$lastEncoded[14]->location."</td>
                <td>".$lastEncoded[14]->season."</td>
                <td>".$lastEncoded[14]->kpKits."</td>
                <td>".$lastEncoded[14]->calendars."</td>
                <td>".$lastEncoded[14]->testimonials."</td>
                <td>".$lastEncoded[14]->services."</td>
                <td>".$lastEncoded[14]->apps."</td>
                <td>".$lastEncoded[14]->yunpalayun."</td>
                <td>".$lastEncoded[14]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[15]->fullName."</td>
                <td>".$lastEncoded[15]->rsbsa_control_no."</td>
                <td>".$lastEncoded[15]->sex."</td>
                <td>".$lastEncoded[15]->birthdate."</td>
                <td>".$lastEncoded[15]->location."</td>
                <td>".$lastEncoded[15]->season."</td>
                <td>".$lastEncoded[15]->kpKits."</td>
                <td>".$lastEncoded[15]->calendars."</td>
                <td>".$lastEncoded[15]->testimonials."</td>
                <td>".$lastEncoded[15]->services."</td>
                <td>".$lastEncoded[15]->apps."</td>
                <td>".$lastEncoded[15]->yunpalayun."</td>
                <td>".$lastEncoded[15]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[16]->fullName."</td>
                <td>".$lastEncoded[16]->rsbsa_control_no."</td>
                <td>".$lastEncoded[16]->sex."</td>
                <td>".$lastEncoded[16]->birthdate."</td>
                <td>".$lastEncoded[16]->location."</td>
                <td>".$lastEncoded[16]->season."</td>
                <td>".$lastEncoded[16]->kpKits."</td>
                <td>".$lastEncoded[16]->calendars."</td>
                <td>".$lastEncoded[16]->testimonials."</td>
                <td>".$lastEncoded[16]->services."</td>
                <td>".$lastEncoded[16]->apps."</td>
                <td>".$lastEncoded[16]->yunpalayun."</td>
                <td>".$lastEncoded[16]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[17]->fullName."</td>
                <td>".$lastEncoded[17]->rsbsa_control_no."</td>
                <td>".$lastEncoded[17]->sex."</td>
                <td>".$lastEncoded[17]->birthdate."</td>
                <td>".$lastEncoded[17]->location."</td>
                <td>".$lastEncoded[17]->season."</td>
                <td>".$lastEncoded[17]->kpKits."</td>
                <td>".$lastEncoded[17]->calendars."</td>
                <td>".$lastEncoded[17]->testimonials."</td>
                <td>".$lastEncoded[17]->services."</td>
                <td>".$lastEncoded[17]->apps."</td>
                <td>".$lastEncoded[17]->yunpalayun."</td>
                <td>".$lastEncoded[17]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[18]->fullName."</td>
                <td>".$lastEncoded[18]->rsbsa_control_no."</td>
                <td>".$lastEncoded[18]->sex."</td>
                <td>".$lastEncoded[18]->birthdate."</td>
                <td>".$lastEncoded[18]->location."</td>
                <td>".$lastEncoded[18]->season."</td>
                <td>".$lastEncoded[18]->kpKits."</td>
                <td>".$lastEncoded[18]->calendars."</td>
                <td>".$lastEncoded[18]->testimonials."</td>
                <td>".$lastEncoded[18]->services."</td>
                <td>".$lastEncoded[18]->apps."</td>
                <td>".$lastEncoded[18]->yunpalayun."</td>
                <td>".$lastEncoded[18]->time_stamp."</td>
            </tr>
            <tr>
                <td>".$lastEncoded[19]->fullName."</td>
                <td>".$lastEncoded[19]->rsbsa_control_no."</td>
                <td>".$lastEncoded[19]->sex."</td>
                <td>".$lastEncoded[19]->birthdate."</td>
                <td>".$lastEncoded[19]->location."</td>
                <td>".$lastEncoded[19]->season."</td>
                <td>".$lastEncoded[19]->kpKits."</td>
                <td>".$lastEncoded[19]->calendars."</td>
                <td>".$lastEncoded[19]->testimonials."</td>
                <td>".$lastEncoded[19]->services."</td>
                <td>".$lastEncoded[19]->apps."</td>
                <td>".$lastEncoded[19]->yunpalayun."</td>
                <td>".$lastEncoded[19]->time_stamp."</td>
            </tr>
        </tbody>
      </table>

        ";
    }
    

    public function syncKPData(Request $request)
    {
       
        $allKPs = json_decode($request->getContent());
        // $row = json_decode($request->getContent());
        
        
        foreach($allKPs as $row)
        {
            if($row->encodedBy == 'dg.lanza' || $row->encodedBy == 'ja.lanza')
            {
                dd("Unavailable");
            }
            $locationlength = strlen($row->location);
            if (preg_match('/[a-zA-Z]/', $row->rsbsa_control_no))
            {
                continue;
            }
            if($locationlength<10){
                continue;
            }

            $locationString = $row->location;
            $locationString = explode(', ',$locationString);
            
            $getPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('regionName','LIKE',$locationString[2])
            ->where('province','LIKE',$locationString[1])
            ->where('municipality','LIKE',$locationString[0])
            ->first();
            
            if(!$getPrv)
            {
                $getPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('regionName','LIKE',$locationString[0])
                ->where('province','LIKE',$locationString[1])
                ->where('municipality','LIKE',$locationString[2])
                ->first();
            }

            
            $prv = $getPrv->prv_code;
            // $parsed_prv = explode('-',$row->rsbsa_control_no);
            // if(count($parsed_prv) == 1 || strlen($parsed_prv[0])>4)
            // {
            //     $prv = substr($parsed_prv[0], 0, 4);
            // }
            // else{
            //     if(strlen($parsed_prv[0])>2)
            //     {
            //         $prv = $parsed_prv[0];
            //     }
            //     else{
            //         $prv = $parsed_prv[0];
            //         if(strlen($parsed_prv[1]) == 2)
            //         {
            //             $prv2 = $parsed_prv[1];
            //         }
            //         else if (strlen($parsed_prv[1]) == 3)
            //         {
            //             $prv2 = substr($parsed_prv[1], 1);
            //         }
            //         else{
            //             $prv2 = substr($parsed_prv[1], 0, 2);
            //         }
            //         $prv = $prv.$prv2;
            //     }
            // }

            // $prv = substr(str_replace('-','',$row->rsbsa_control_no),0,4);
            $season = strtolower($row->season);
            $validate = DB::table('kp_distribution.kp_distribution_app')
            ->where('fullName','=',$row->fullName)
            ->where('rsbsa_control_no','=', $row->rsbsa_control_no) 
            ->where('season','=',$row->season)
            ->get();
            
            
            if(collect($validate)->isEmpty() && $row->isDeleted == 0){
                $getBday = DB::table($GLOBALS['season_prefix'].'prv_'.$prv.'.farmer_information_final')
                        ->select('birthdate')
                        ->where('rsbsa_control_no','=', $row->rsbsa_control_no)
                        ->orWhere('assigned_rsbsa','=', $row->rsbsa_control_no)
                        ->first();
    
                        // dd($getBday);
                    if(!$getBday){
                    
                        $getBday2 = DB::table('ds2024_prv_'.$prv.'.farmer_information_final')
                            ->select('birthdate')
                            ->where('rsbsa_control_no','=', $row->rsbsa_control_no) 
                            ->orWhere('assigned_rsbsa','=', $row->rsbsa_control_no) 
                            ->first();
                            if(!$getBday2){
                                $getBday = '';
                            }
                            else{
                                $getBday= $getBday2->birthdate;
                            }
                        }
                        else{
                            $getBday = $getBday->birthdate;
                        }
                // dd($row->location);  
                $locationlength = strlen($row->location);
                $location = $row->location;
                if($locationlength == 6){
                    $checkLibPrv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where('prv', $location)
                    ->first();
                    if($checkLibPrv){
                        $location = $checkLibPrv->municipality.', '.$checkLibPrv->province.', '.$checkLibPrv->regionName;
                    }
                    else
                    {
                        $pt1 = substr($location,0,4);
                        $pt2 = substr($location,5,2);
                        $loc = $pt1.'%'.$pt2;
                        $checkLibPrv2 = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                        ->where('prv','LIKE', $loc)
                        ->first();
                        $location = $checkLibPrv2->municipality.', '.$checkLibPrv2->province.', '.$checkLibPrv2->regionName;
                    }
                }
                else{
                    $location = $row->location; 
                }
                if($row->encodedBy == 'bryan0629')
                {
                    $row->encodedBy = 'dg.delossantos';
                }

                $fullName = str_replace(
                    ['Ñ','ñ','&lsquo;', '&Ntilde;', '&ntilde;', '&Atilde;', '&atilde;', '&Eacute;', '&eacute;', '&Iacute;', '&iacute;', '-', 'N/A', 'n/a', ' NA', ' na'], 
                    ['N', 'n',"", 'N', 'n', 'A', 'a', 'E', 'e', 'I', 'i', '', '', '', ' ', ' '],
                    $row->fullName
                );                

                $fullName = strtoupper($fullName);
                DB::table('kp_distribution.kp_distribution_app')
                ->insert([
                    "fullName" => $fullName,
                    "rsbsa_control_no" => $row->rsbsa_control_no,
                    "sex" => $row->sex,
                    "birthdate" => $getBday,
                    "location" => $location,
                    "season" => $row->season,
                    "kpKits" => !empty($row->kpKits) || $row->kpKits !== '' ? $row->kpKits : 0,
                    "calendars" => !empty($row->calendars) || $row->calendars !== '' ? $row->calendars : 0,
                    "testimonials" => !empty($row->testimonials) || $row->testimonials!== '' ? $row->testimonials : 0,
                    "services" => !empty($row->services) || $row->services !== '' ? $row->services : 0,
                    "apps" => !empty($row->apps) || $row->apps !== '' ? $row->apps : 0,
                    "yunpalayun" => !empty($row->yunpalayun) || $row->yunpalayun !== '' ? $row->yunpalayun : 0,
                    "encodedBy" => $row->encodedBy,
                    "time_stamp" => $row->time_stamp
                ]);

            }
            else
            {
                if(count($validate)>0 && $row->isDeleted==1)
                {
                    DB::table('kp_distribution.kp_distribution_app')
                    ->where('fullName','=',$row->fullName)
                    ->where('rsbsa_control_no','=', $row->rsbsa_control_no) 
                    ->where('time_stamp','=', $row->time_stamp)
                    ->delete();
                }

            }
        }
        return 1;
    }
}

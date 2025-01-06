<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class EncoderStatisticController extends Controller
{

    public function __construct()
    {
        // database connections
        $this->geotag_con = 'geotag_db';
    }

    public function index(){
         $stations = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->orderBy('stationName', 'asc')
        ->get();
       // ->pluck('stationName', 'stationId');
        return view('EncoderStatistic.index',compact('stations'));
    }

    public function encoderData(Request $request){
       return  $users = DB::table('users')
       ->join('role_user', 'role_user.userId', '=', 'users.userId')
       ->where('role_user.roleId',28)
       ->where('users.stationId',$request->station)
       ->get();  
    }

    public function getWeek(Request $request){
        $seasonActive =  $request->season;  
        $monthName =$request->month;      
        if($seasonActive == "ds2020" || $seasonActive == "ws2020"){
            $years="2020";
        }
       
        if($seasonActive == "ds2021" || $seasonActive == "ws2021"){
            $years="2021";
        }

        $Fday=date('Y-m-01',strtotime("First Day Of  $monthName $years")) ;
        $dFDday = date("D",strtotime($Fday));
        $todt=date('Y-m-d',strtotime("Last Day of $monthName $years"));
        if($dFDday=="Sun" || $dFDday =="Sat"){
            $Fday=date('Y-m-d',strtotime("First monday Of $monthName $years")) ;
            $dFDday = date("D",strtotime($Fday)); 
        }
        $Fdaytmp = $Fday;
        $dateRangeArray=[];
        $x=1;
        $y=1;
        
        while ($dFDday != "Sun") {
            
             $tmpFDday=date("D",strtotime($dFDday.'+'.$y.'day'));
             $tmpWDday=date("Y-m-d",strtotime($Fday.'+'.$y.'day'));
             if($tmpFDday == "Sun"){
                  $dateRange=$Fdaytmp."%".$tmpWDday;
                  array_push($dateRangeArray, array(
                    "Week ".$x => $dateRange));
                  $x++;
                    
                  $y++; 
                  
                  
                    $Fdaytmp =  date("Y-m-d",strtotime($Fday.'+'.$y.'day'));
                  $y--; 
             }
            
             $y++; 
             //return $todt."*".$tmpWDday;
             if($todt == $tmpWDday){
                if($tmpFDday != "Sun"){
                   $dateRange=$Fdaytmp."%".$tmpWDday;
                   array_push($dateRangeArray, array(
                     "Week ".$x => $dateRange));
                }  
                
                 break;
             }
             
        }
        return count($dateRangeArray);
         
    
        }
public function StatisticDatLoadChart(Request $request){
    $years="";
    $seasonActive= $request->seasonActive;
    if($seasonActive == "ds2020" || $seasonActive == "ws2020"){
        $years="2020";
    }
   
    if($seasonActive == "ds2021" || $seasonActive == "ws2021"){
        $years="2021";
    }
   
    
    $monthName =$request->month;
    $weekData=$request->week;

    $Fday=date('Y-m-01',strtotime("First Day Of  $monthName $years")) ;
    $dFDday = date("D",strtotime($Fday));
    $todt=date('Y-m-d',strtotime("Last Day of $monthName $years"));
    
    $Fdaytmp = $Fday;
    $dateRangeArray=[];
    $x=1;
    $y=1;
    $from="";
    $to="";
    
    while ($dFDday != "Sun") {            
         $tmpFDday=date("D",strtotime($dFDday.'+'.$y.'day'));
         $tmpWDday=date("Y-m-d",strtotime($Fday.'+'.$y.'day'));
         if($tmpFDday == "Sun"){
              $dateRange=$Fdaytmp."%".$tmpWDday;
              array_push($dateRangeArray, array(
                "Week ".$x." ".$y."" => $dateRange));
              $x++;                    
              $y++; 
                $Fdaytmp =  date("Y-m-d",strtotime($Fday.'+'.$y.'day'));
              $y--; 
         }            
         $y++; 
         if($todt == $tmpWDday){
            if($tmpFDday != "Sun"){
               $dateRange=$Fdaytmp."%".$tmpWDday;
               array_push($dateRangeArray, array(
                 "Week ".$x." ".$y."" => $dateRange));
            }  
            
             break;
         }
         
    }
    
    
    $dateRangeArray;

    foreach ($dateRangeArray as $value) {
        if(isset($value[''.$weekData.''])){
            $tmp= explode("%", $value[''.$weekData.'']);
            $from=$tmp[0];
            $to=$tmp[1];
          }
   }
        $Season = ["ds2020","ws2020","ds2021","ws2021", ];
         $users = DB::table('users')->where('stationId',$request->station)->get();  
         $Data = array();
         $Statistic = [];
         $totalData = 0;
         $username = $request->username;
         $seasonActive=$request->seasonActive;
         if($username=="ALL"){
         $tbl_arr = array();
            $users = DB::table('users')
			->join('role_user', 'role_user.userId', '=', 'users.userId')
			->where('role_user.roleId',28)
			->where('users.stationId',$request->station)
			->get();
             foreach ($users as  $UserData) {
                $username=$UserData->username;
               
                    $request = new \stdClass();
                    $request->season=$seasonActive;
                   $con = $this->connectDatabase($request);
                    if($seasonActive == "ds2020"){
                        //us_opt1
                        $database = "ds2020_rcep_extension_db";
                    }
                    if($seasonActive == "ws2020"){
                        $database = "rcep_extension_db";
                    }
                    if($seasonActive == "ds2021"){
                        $database = "rcep_extension_db";
                    }
                    if($seasonActive == "ws2021"){
                        $database = "rcep_extension_db";
                    
                    }
        
        
                        if($con=="success"){
                            $schema = DB::connection("extension_connector")->table('information_schema.TABLES')
                            ->select('TABLE_NAME')
                            ->where('TABLE_SCHEMA',"".$database."")->get();
                            $i = 0;
                          
                            foreach ($schema as  $tableName) {         
                                  $users_encodedData = count(DB::connection("extension_connector")
                                  ->table(''.$database.'.'.$tableName->TABLE_NAME)
                                  ->where('user_updated',$username)->get());
                                 // dd($users_encodedData);
                                  $Data['encoder'] = $username;
                                  $i += $users_encodedData;
                             }
                             
                             $totalData += $i;
                        }else{
                            $Data->encoder=$request->username;
                            $Data->$value="connection Failed";
                        }
                       
                      
             
             
        
				//$Data['totalData']=$totalData;
          
        
                $Data['totalData']=$totalData;
          
            array_push($tbl_arr, array(
                "encoder" => $Data['encoder'],
                "totalData" => $Data['totalData'] )
            );
             
             }
         }else{
    
            $tbl_arr = array();
       
                $request = new \stdClass();
                $request->season=$seasonActive;
               $con = $this->connectDatabase($request);
                if($seasonActive == "ds2020"){
                    //us_opt1
                    $database = "ds2020_rcep_extension_db";
                }
                if($seasonActive == "ws2020"){
                    $database = "rcep_extension_db";
                }
                if($seasonActive == "ds2021"){
                    $database = "rcep_extension_db";
                }
                if($seasonActive == "ws2021"){
                    $database = "rcep_extension_db";
                
                }
    
    
                    if($con=="success"){
                        $schema = DB::connection("extension_connector")->table('information_schema.TABLES')
                        ->select('TABLE_NAME')
                        ->where('TABLE_SCHEMA',"".$database."")->get();
                        $i = 0;
                       
                        foreach ($schema as  $tableName) {         
                              $users_encodedData = count(DB::connection("extension_connector")
                              ->table(''.$database.'.'.$tableName->TABLE_NAME)
                              ->where('user_updated',$username)->get()
                              ->whereBetween('date_created', [$from, $to])
                            );
                             // dd($users_encodedData);
                              $Data['encoder'] = $username;
                              $i += $users_encodedData;
                         }
                         
                         $totalData += $i;
                    }else{
                        $Data->encoder=$request->username;
                        $Data->$value="connection Failed";
                    }
               
          $Data['totalData']=$totalData;
          
            array_push($tbl_arr, array(
                "encoder" => $Data['encoder'],
                "totalData" => $Data['totalData'] )
            );
        
         }
      
   			return $tbl_arr;
             
        //    return $request;
    
        }

    public function StatisticDatLoad(Request $request){
        $years="";
        $seasonActive= $request->seasonActive;
        if($seasonActive == "ds2020" || $seasonActive == "ws2020"){
            $years="2020";
        }
       
        if($seasonActive == "ds2021" || $seasonActive == "ws2021"){
            $years="2021";
        }
       
        $weekData=$request->week;
        $monthName =$request->month;
        $from="";
        $to="";
        $Fday=date('Y-m-01',strtotime("First Day Of  $monthName $years")) ;
        $dFDday = date("D",strtotime($Fday));
        $todt=date('Y-m-d',strtotime("Last Day of $monthName $years"));
        
        $Fdaytmp = $Fday;
        $dateRangeArray=[];
        $x=1;
        $y=1;
        
        while ($dFDday != "Sun") {            
             $tmpFDday=date("D",strtotime($dFDday.'+'.$y.'day'));
             $tmpWDday=date("Y-m-d",strtotime($Fday.'+'.$y.'day'));
             if($tmpFDday == "Sun"){
                  $dateRange=$Fdaytmp."%".$tmpWDday;
                  array_push($dateRangeArray, array(
                    "Week ".$x." ".$y."" => $dateRange));
                  $x++;                    
                  $y++; 
                    $Fdaytmp =  date("Y-m-d",strtotime($Fday.'+'.$y.'day'));
                  $y--; 
             }            
             $y++; 
             if($todt == $tmpWDday){
                if($tmpFDday != "Sun"){
                   $dateRange=$Fdaytmp."%".$tmpWDday;
                   array_push($dateRangeArray, array(
                     "Week ".$x." ".$y."" => $dateRange));
                }  
                
                 break;
             }
             
        }
        
        
        $dateRangeArray;

        foreach ($dateRangeArray as $value) {
            if(isset($value[''.$weekData.''])){
                $tmp= explode("%", $value[''.$weekData.'']);
                $from=$tmp[0];
                $to=$tmp[1];
              }
       }

        $Season = ["ds2020","ws2020","ds2021","ws2021", ];
         $users = DB::table('users')->where('stationId',$request->station)->get();  
         $Data = array();
         $Statistic = [];
         $totalData = 0;
         $username = $request->username;
         $seasonActive=$request->seasonActive;
         if($username=="ALL"){
         $tbl_arr = array();
           $users = DB::table('users')
			->join('role_user', 'role_user.userId', '=', 'users.userId')
			->where('role_user.roleId',28)
			->where('users.stationId',$request->station)
			->get();
             foreach ($users as  $UserData) {
                $username=$UserData->username;
               
                    $request = new \stdClass();
                    $request->season=$seasonActive;
                   $con = $this->connectDatabase($request);
                    if($seasonActive == "ds2020"){
                        //us_opt1
                        $database = "ds2020_rcep_extension_db";
                    }
                    if($seasonActive == "ws2020"){
                        $database = "rcep_extension_db";
                    }
                    if($seasonActive == "ds2021"){
                        $database = "rcep_extension_db";
                    }
                    if($seasonActive == "ws2021"){
                        $database = "rcep_extension_db";
                    
                    }
        
        
                        if($con=="success"){
                            $schema = DB::connection("extension_connector")->table('information_schema.TABLES')
                            ->select('TABLE_NAME')
                            ->where('TABLE_SCHEMA',"".$database."")->get();
                            $i = 0;
                          
                            foreach ($schema as  $tableName) {         
                                  $users_encodedData = count(DB::connection("extension_connector")
                                  ->table(''.$database.'.'.$tableName->TABLE_NAME)
                                  ->where('user_updated',$username)->get()
                                  ->whereBetween('date_created', [$from, $to])
                                );
                                 // dd($users_encodedData);
                                  $Data['encoder'] = $username;
                                  $i += $users_encodedData;
                             }
                             
                             $totalData += $i;
                        }else{
                            $Data->encoder=$request->username;
                            $Data->$value="connection Failed";
                        }
                       
                      
             
             
        
             // $Data['totalData']=$totalData;
          
        
                $Data['totalData']=number_format($totalData);
          
            array_push($tbl_arr, array(
                "encoder" => $Data['encoder'],
                "totalData" => $Data['totalData'] )
            );
             
             }
         }else{
    
            $tbl_arr = array();
       
                $request = new \stdClass();
                $request->season=$seasonActive;
               $con = $this->connectDatabase($request);
                if($seasonActive == "ds2020"){
                    //us_opt1
                    $database = "ds2020_rcep_extension_db";
                }
                if($seasonActive == "ws2020"){
                    $database = "rcep_extension_db";
                }
                if($seasonActive == "ds2021"){
                    $database = "rcep_extension_db";
                }
                if($seasonActive == "ws2021"){
                    $database = "rcep_extension_db";
                
                }
    
    
                    if($con=="success"){
                        $schema = DB::connection("extension_connector")->table('information_schema.TABLES')
                        ->select('TABLE_NAME')
                        ->where('TABLE_SCHEMA',"".$database."")->get();
                        $i = 0;
                       
                        foreach ($schema as  $tableName) {         
                              $users_encodedData = count(DB::connection("extension_connector")->table(''.$database.'.'.$tableName->TABLE_NAME)->where('user_updated',$username)->get());
                             // dd($users_encodedData);
                              $Data['encoder'] = $username;
                              $i += $users_encodedData;
                         }
                         
                         $totalData += $i;
                    }else{
                        $Data->encoder=$request->username;
                        $Data->$value="connection Failed";
                    }
               
          $Data['totalData']=$totalData;
          
            array_push($tbl_arr, array(
                "encoder" => $Data['encoder'],
                "totalData" => $Data['totalData'] )
            );
        
         }
      
         $table  =collect($tbl_arr);
        return Datatables::of($table)
        
            ->make(true);
        
          
             
        //    return $request;
    
        }

        public function statisticConnectionDB(Request $request){
            $season = $request->season;		 
            $delivery_inspection_db = "rcep_delivery_inspection";
            if($season == "ds2020"){
                $delivery_inspection_db = "ds2020_".$delivery_inspection_db;
            }
    
    
                $con = $this->changeConnection($season,$delivery_inspection_db);
    
                if($con == "success"){
                   
                     
                        return array(
                            "status" => "1",
                            "message" => "success",
                            "data" => array(
                                "season" => $season,
                                "info" => ""
                            )
                        );
                }else{
                    return array(
                            "status" => "0",
                            "message" => "failed",
                            "data" => array(
                                "season" => $season,
                                "info" => ""
                            )
                        );
                }
        }
    private function connectDatabase($request){
	 	$season = $request->season;		 		
		
			return $con = $this->changeConnection($season,"information_schema");
             

			
	}

    private function changeConnection($season,$database_name){
        $conn_string = array();

        $conn_string['ws2020']['host'] = "localhost";
        $conn_string['ws2020']['port'] = "4406";
        $conn_string['ws2020']['user'] = "rcef_user";
        $conn_string['ws2020']['password'] = "SKF9wzFtKmNMfwyz";

        $conn_string['ds2021']['host'] = "192.168.10.23";
        $conn_string['ds2021']['port'] = "3306";
        $conn_string['ds2021']['user'] = "rcef_web";
        $conn_string['ds2021']['password'] = "SKF9wzFtKmNMfwy";
        
        $conn_string['ws2021']['host'] = "localhost";
        $conn_string['ws2021']['port'] = "4409";
        $conn_string['ws2021']['user'] = "rcef_web";
        $conn_string['ws2021']['password'] = "SKF9wzFtKmNMfwy";

        $conn_string['ds2020']['host'] = "localhost";
        $conn_string['ds2020']['port'] = "3306";
        $conn_string['ds2020']['user'] = "jpalileo";
        $conn_string['ds2020']['password'] = "P@ssw0rd";

            try{
                \Config::set('database.connections.extension_connector.host', $conn_string[$season]['host']);
                \Config::set('database.connections.extension_connector.port', $conn_string[$season]['port']);
                \Config::set('database.connections.extension_connector.database', $database_name);
                \Config::set('database.connections.extension_connector.username', $conn_string[$season]['user']);
                \Config::set('database.connections.extension_connector.password', $conn_string[$season]['password']);
                DB::purge('extension_connector');
                DB::connection('extension_connector')->getPdo();
            
                return "success";
            } catch (\Exception $e) {
                //dd($e);
                return "failed";		
            }
    }

}

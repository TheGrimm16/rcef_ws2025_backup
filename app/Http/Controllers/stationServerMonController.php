<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class stationServerMonController extends Controller
{
    public function index(){

        $nrp_data_release = DB::table("station_domain.lib_station_domain")/* ->where('id',6) */->get();
        $arrayData = array();
        foreach ($nrp_data_release as $key => $value) {   
               
            $host = $value->sub_domain_name;
            $handle = curl_init($host);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
                  
          
        $status = "OFFLINE";
          if($httpCode != 502) {
            // online
            $status = "ONLINE";
           
            } 

            array_push($arrayData,array(
                'StationName'=>$value->station_name,
                'ServerAddress'=>$value->sub_domain_name,
                'Status'=> $status,
                'code'=> $httpCode
            ));
    }

/*        
        $clientIP = \Request::getClientIp(true); // user Ip address
        $host = "rcef-mdsyp.philrice.gov.ph";
 */

     /*   
        if($url == NULL) return false;  
        $ch = curl_init($url);  
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        $data = curl_exec($ch);  
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
        curl_close($ch);  
     */
  
        return view('serverStation.index',compact('arrayData'));
    }
}

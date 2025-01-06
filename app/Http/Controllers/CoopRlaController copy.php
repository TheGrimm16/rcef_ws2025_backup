<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;
use Redirect;
use Http;

class CoopRlaController extends Controller
{

  public function home(){

        $stream_opts = [
          "ssl" => [
              "verify_peer"=>false,
              "verify_peer_name"=>false,
          ]
        ];  
  
    $response = file_get_contents("https://rsis.philrice.gov.ph/api_management/rcef_lab_results",
                 false, stream_context_create($stream_opts));
    return  $response;
    $url = "https://rsis.philrice.gov.ph/api_management/rcef_lab_results";
    $data = stripslashes(file_get_contents($url));

   
    $tmp = json_decode($data);
   
// return count($data2);
    foreach ($tmp as $value) {
      dd($value);
      # code...
    }
   
  }

       
    
}

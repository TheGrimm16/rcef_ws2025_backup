<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http;
use DB;
class rcefMechController extends Controller
{

    public function dashboard(){
        return view("mech.dashboard");

    }

    public function downloadAPI($page){

        $allowed_page_arr = array(
            "profileAssocs" => "tbl_profileassocs",
            "DeliveredMachines" => "tbl_deliveredmachines",
            "FcaMembers" => "tbl_fcamembers",
            "LibCategory" => "tbl_libcategory",
            "LibMachineAvaileds" => "tbl_libmachineavaileds",
            "CountFca" => "",
            "CountFcaMembers" => "",
            "CountMachine" => "",
        );

        // return $page;
        // if(isset($allowed_page_arr[$page])){
            $url = "https://rcefis.philmech.gov.ph/api/".$page;
            $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n".
                            "XApiKey: pgH7QzFHJx4w46fI~5Uzi4RvtTwlEXp"
                ),
                "ssl" => array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                )
              );
    


                $response = file_get_contents($url, false, stream_context_create($opts));
                $tmp_data = json_decode($response, true);
                    // if(count($tmp_data) > 0){
                    //     DB::table($GLOBALS['season_prefix']."mech_db.".$allowed_page_arr[$page])->truncate();
                    // }    


                    // foreach($tmp_data as $tmp){
                    //         // dd($tmp);
                
                    //         DB::table($GLOBALS['season_prefix']."mech_db.".$allowed_page_arr[$page])
                    //         ->insert($tmp);
                    // }
        
                    return json_encode($tmp_data);
        // }else{
        //     return "false";
        // }

   








    }

    public function subCategory(){
        $request = json_decode(request()->getContent(), true);

        $url = "https://rcefis.philmech.gov.ph/api/".$request["page"];
            $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n".
                            "XApiKey: pgH7QzFHJx4w46fI~5Uzi4RvtTwlEXp"
                ),
                "ssl" => array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                )
              );
    


                $response = file_get_contents($url, false, stream_context_create($opts));
                $tmp_data = json_decode($response, true);
                    // if(count($tmp_data) > 0){
                    //     DB::table($GLOBALS['season_prefix']."mech_db.".$allowed_page_arr[$page])->truncate();
                    // }    


                    // foreach($tmp_data as $tmp){
                    //         // dd($tmp);
                
                    //         DB::table($GLOBALS['season_prefix']."mech_db.".$allowed_page_arr[$page])
                    //         ->insert($tmp);
                    // }
        
                    return json_encode($tmp_data);
    }
}

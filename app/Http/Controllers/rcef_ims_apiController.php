<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Yajra\Datatables\Datatables;
use Auth;
use Hash;
use Excel;
use Storage;
class rcef_ims_apiController extends Controller{

    public function checkCredibility(){
        $request = json_decode(request()->getContent(), true);
        $apiKey = $request["apiKey"];
        $creds = $request["creds"];
        $role = $request["role"];

        if($apiKey != "rcef/ims/codec8167263"){
            return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
        }

        $rawResult = DB::table('rcef_ims.tbl_credentials_session')
            ->where('session_id', $creds)
            ->where('code_name', $role)
            ->get();

        if($rawResult){
            return null;
        }
        else{
            return 1;
        }
    }
    
    public function loginCred(){
        $request = json_decode(request()->getContent(), true);

        $encoderPre = "stopdHdvYnJva2VzaXh3aXNllookingbm90ZWxvYWR3aGforF0ZXZlcmdpYW50dGhhapasswordbmtoYXNzb2NpYWw=P@ssw0rd123dHJvdWJsZXNvbHZlY29udHJhc3RhY3RpdmVydWxlc2xhdmVncm93YnJpZ2h0bmFtZXI";
        $encoderPos = "dHJvdWJsZXNvbHZlY29udHJhc3RhY3RpdmVydWxlc2xhdmVncm93YnJpZ2h0bmFtZXI=lol";

        $apiKey = $request["apiKey"];
        $encodedpw = $request["credential"];

        if($apiKey != "rcef/ims/codec8167263"){
            return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
        }

        $encodedpw = str_replace($encoderPre, "", $encodedpw);
        $encodedpw = str_replace($encoderPos, "", $encodedpw);

        // $encodedpw = Hash::make($encodedpw);

        $account = DB::table('rcef_ims.tbl_credentials')
            // ->where('encoded_cred', 'LIKE', $encodedpw)
            ->get();
        
        foreach ($account as $key) {
            if(Hash::check($encodedpw, $key->encoded_cred)){
                $enc = encrypt($key->code_name."".date("Y-m-d H:i:s"));
                DB::table('rcef_ims.tbl_credentials_session')
                    ->insert(
                            [
                                "code_name" => $key->code_name,
                                "session_id" => $enc
                            ]
                        );
                return array(
                    "code_name" => $key->code_name,
                    "display_name" => $key->display_name,
                    "session_id" => $enc
                );
            }
        }

    }

    public function get_gad_api($season, $prv_code){
        $nationwide = 0;
       $reg_check =  DB::connection("delivery_inspection_db")->table("lib_prv")
            ->where("regionName", "LIKE",$prv_code)
            ->first();
        if($reg_check == null){
            $prv_check =  DB::connection("delivery_inspection_db")->table("lib_prv")
            ->where("province", "LIKE",$prv_code)
            ->first();
                if($prv_check == null){
                    $nationwide = 1;
                }else{
                    $code = $prv_check->prv_code;
                }

        }else{
            $code = $reg_check->regCode;
        }   

        if($nationwide == 1){
            $data  =  DB::connection("delivery_inspection_db")->table("rcef_ims.tbl_gad_seasons")
            ->select(DB::raw("SUM(male) as total_male"),DB::raw("SUM(female) as total_female"),
             DB::raw("SUM(total_farmer) as total_farmer"),
             DB::raw("SUM(farmer_1_male) as farmer_1_male"),
             DB::raw("SUM(farmer_2_male) as farmer_2_male"),
             DB::raw("SUM(farmer_3_male) as farmer_3_male"),
             
             DB::raw("SUM(farmer_1_female) as farmer_1_female"),
             DB::raw("SUM(farmer_2_female) as farmer_2_female"),
             DB::raw("SUM(farmer_3_female) as farmer_3_female"),
             
             DB::raw("SUM(total_invalid_age) as total_invalid_age")
            
            )
            // ->where("prv_code", "LIKE", $code."%")
            ->where("season", $season)
            ->first();

        }else{
            $data  =  DB::connection("delivery_inspection_db")->table("rcef_ims.tbl_gad_seasons")
            ->select(DB::raw("SUM(male) as total_male"),DB::raw("SUM(female) as total_female"),
            DB::raw("SUM(total_farmer) as total_farmer"),
            DB::raw("SUM(farmer_1_male) as farmer_1_male"),
            DB::raw("SUM(farmer_2_male) as farmer_2_male"),
            DB::raw("SUM(farmer_3_male) as farmer_3_male"),
            
            DB::raw("SUM(farmer_1_female) as farmer_1_female"),
            DB::raw("SUM(farmer_2_female) as farmer_2_female"),
            DB::raw("SUM(farmer_3_female) as farmer_3_female"),
            
            DB::raw("SUM(total_invalid_age) as total_invalid_age")

            
            )
            ->where("prv_code", "LIKE", $code."%")
            ->where("season", $season)
            ->first();

        }
      
            if($data->total_male != null){  $total_male = $data->total_male;  }else{   $total_male = 0;}
            if($data->total_female != null){    $total_female = $data->total_female;  }else{     $total_female = 0;  }
            if($data->total_farmer != null){    $total_farmer = $data->total_farmer;  }else{     $total_farmer = 0;    }

            if($total_farmer == 0){
                if($total_male > 0 || $total_female > 0){
                    $total_farmer = $total_male + $total_female;

                }
            }


            if($data->farmer_1_male != null){    $farmer_1_male = $data->farmer_1_male;  }else{     $farmer_1_male = 0;  }
            if($data->farmer_2_male != null){    $farmer_2_male = $data->farmer_2_male;  }else{     $farmer_2_male = 0;  }
            if($data->farmer_3_male != null){    $farmer_3_male = $data->farmer_3_male;  }else{     $farmer_3_male = 0;  }
            
            if($data->farmer_1_female != null){    $farmer_1_female = $data->farmer_1_female;  }else{     $farmer_1_female = 0;  }
            if($data->farmer_2_female != null){    $farmer_2_female = $data->farmer_2_female;  }else{     $farmer_2_female = 0;  }
            if($data->farmer_3_female != null){    $farmer_3_female = $data->farmer_3_female;  }else{     $farmer_3_female = 0;  }
            
            if($data->total_invalid_age != null){    $total_invalid_age = $data->total_invalid_age;  }else{     $total_invalid_age = 0;  }
            
            $total_group = $farmer_1_male + $farmer_1_female + $farmer_2_male + $farmer_2_female + $farmer_3_male + $farmer_3_female + $total_invalid_age ; 

            $group_1  =  $farmer_1_male + $farmer_1_female ;
            $group_2  =  $farmer_2_male + $farmer_2_female ;
            $group_3  =  $farmer_3_male + $farmer_3_female ;

            if($total_farmer == 0){
                return array(
                    "male" => "0%",
                    "female" => "0%",
                    "farmer_1_male" => "0%",
                    "farmer_2_male" => "0%",
                    "farmer_3_male" => "0%",
                    "farmer_1_female" => "0%",
                    "farmer_2_female" => "0%",
                    "farmer_3_female" => "0%",
                    "total_invalid_age" => "0%",
                    "group_1" => "0%",
                    "group_2" => "0%",
                    "group_3" => "0%"
                );
    
            }else{

                if($total_group == 0){
                    return array(
                        "male" => number_format(($total_male/$total_farmer)*100, 2)."%",
                        "female" => number_format(($total_female/$total_farmer)*100, 2)."%",
                        "farmer_1_male" => "0%",
                        "farmer_2_male" => "0%",
                        "farmer_3_male" => "0%",
                        "farmer_1_female" => "0%",
                        "farmer_2_female" => "0%",
                        "farmer_3_female" => "0%",
                        "total_invalid_age" => "0%",
                        "group_1" => "0%",
                        "group_2" => "0%",
                        "group_3" => "0%");
                }else{
                    return array(
                        "male" => number_format(($total_male/$total_farmer)*100, 2)."%",
                        "female" => number_format(($total_female/$total_farmer)*100, 2)."%",
                        "farmer_1_male" => number_format(($farmer_1_male/$total_group)*100, 2)."%",
                        "farmer_2_male" => number_format(($farmer_2_male/$total_group)*100, 2)."%",
                        "farmer_3_male" => number_format(($farmer_3_male/$total_group)*100, 2)."%",
                        "farmer_1_female" => number_format(($farmer_1_female/$total_group)*100, 2)."%",
                        "farmer_2_female" => number_format(($farmer_2_female/$total_group)*100, 2)."%",
                        "farmer_3_female" => number_format(($farmer_3_female/$total_group)*100, 2)."%",
                        "total_invalid_age" => number_format(($total_invalid_age/$total_group)*100, 2)."%",
                        "group_1" => number_format(($group_1/$total_group)*100, 2)."%",
                        "group_2" => number_format(($group_2/$total_group)*100, 2)."%",
                        "group_3" => number_format(($group_3/$total_group)*100, 2)."%",
                    );
                }

                
    
            }
           
           





    }

    function getSeasons(){
        return DB::table('rcep_season.lib_season')
            ->select('acronym', 'name', 'sort')
            // ->where('acronym', '!=', 'ds2023')
            // ->where('acronym', '!=', 'ws2022')
            // ->where('acronym', '!=', 'ds2022')
            ->orderBy('sort', 'ASC')
            ->get();
    }

    function getLatestSeason(){
        return DB::table('rcef_ims.tbl_dashboard_seeds')
            ->select('season')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get();
    }

    function latestSeasonData(){
        return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
    }

    function getAllRegions(){
        $regions = DB::table('rcef_ims.lib_regions')
        ->select('regName')
        ->orderBy('order', 'ASC')
        ->get();
        $allRegions = array();
        foreach ($regions as $reg) {
            array_push($allRegions, $reg->regName);
        }
        return $allRegions;
    }

    function getProvinces(){
        $request = json_decode(request()->getContent(), true);

        $apiKey = $request["apiKey"];
        $reg = $request["reg"];

        if($apiKey != "rcef/ims/codec8167263"){
            return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
        }

        $prv = DB::table('rcef_ims.lib_provinces')
            ->select('provDesc')
            ->where('regDesc', 'like', ''.$reg)
            ->groupBy('provDesc')
            ->get();

        $prvs = array();
        foreach ($prv as $pr) {
            array_push($prvs, $pr->provDesc);
        }

        return $prvs;
    }

    function getMuni(){
        $request = json_decode(request()->getContent(), true);

        $apiKey = $request["apiKey"];
        $prv = $request["prv"];

        if($apiKey != "rcef/ims/codec8167263"){
            return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
        }


        $mun = DB::table('rcef_ims.tbl_dashboard_seeds')
            ->select('municipality')
            ->where('province', 'like', ''.$prv)
            ->groupBy('municipality')
            ->get();

        $muni = array();
        foreach ($mun as $mu) {
            array_push($muni, $mu->municipality);
        }

        return $muni;
    }

    function getPastSeasonData(){
        $request = json_decode(request()->getContent(), true);

        $apiKey = $request["apiKey"];
        $reg = $request["reg"];
        $prv = $request["prv"];
        $mun = $request["mun"];
        $season = $request["season"];

        if($apiKey != "rcef/ims/codec8167263"){
            return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
        }

        return DB::table('rcef_ims.tbl_dashboard_seeds')
            ->where('season', 'like', $season)
            ->get();

        // return array(
        //     "apiKey" => $apiKey,
        //     "region" => $reg,
        //     "province" => $prv,
        //     "municipality" => $mun
        // );
    }

    function getSelectedData(){
        $request = json_decode(request()->getContent(), true);

        $apiKey = $request["apiKey"];
        $reg = $request["reg"];
        $prv = $request["prv"];
        $mun = $request["mun"];
        $season = $request["season"];

        if($apiKey != "rcef/ims/codec8167263"){
            return "<div style='font-family: Courier; font-size: 2rem; font-weight: 600; position: absolute; top: 0; bottom: 0; left: 0; right: 0; display: grid; place-items: center;'>Hi! You're not supposed to be back here!</div>";
        }

        if($reg === "0" || $reg === 0){
            $reg = "%";
        }
        if($prv === "0" || $prv === 0){
            $prv = "%";
        }
        if($mun === "0" || $mun === 0){
            $mun = "%";
        }

        return $raw = DB::table('rcef_ims.tbl_dashboard_seeds')
            ->select(DB::raw('sum(distributed) as dist'), DB::raw('sum(no_beneficiaries) as benef'))
            ->where('season', 'like', $season)
            ->where('region', 'like', $reg)
            ->where('province', 'like', $prv)
            ->where('municipality', 'like', $mun)
            ->get();
    }

    function getCoordsReg(Request $request){
        
        $rawCoords = DB::table('rcef_ims.lib_regions')
            ->select('lon', 'lan')
            ->where('regName', 'like', $request->reg)
            ->get();
        
        foreach($rawCoords as $coord){
            return array(
                "lng" => $coord->lon,
                "lat" => $coord->lan
            );
        }
        
    }

    function getCoordsPrv(Request $request){
        $rawCoords = DB::table('rcef_ims.lib_provinces')
            ->select('lon', 'lan')
            ->where('provDesc', 'like', $request->prv)
            ->get();
        
        foreach($rawCoords as $coord){
            return array(
                "lng" => $coord->lon,
                "lat" => $coord->lan
            );
        }
    }

    function delete_file_metrics(){
        $request = json_decode(request()->getContent(), true);


       $file =  DB::table("rcef_ims.tbl_file_metrics")
            ->where("id", $request['id'])
            ->first();


        if($file != null){
                
                $file_path = $file->author."/".$file->file_name.".".$file->file_type;
         
           $unlnk =  Storage::disk('ims_files')->delete($file_path);

            if($unlnk){
                DB::table("rcef_ims.tbl_file_metrics")
                ->where("id", $request['id'])
                ->delete(); 
                return "true";
            }else{
                return "false";
            }



        }else{
            return "File not found";
        }



        
    }

    function getCoordsMun(Request $request){
        $rawCoords = DB::table('rcef_ims.lib_municipalities')
            ->select('lng', 'lat')
            ->where('province', 'like', $request->prv)
            ->where('municipality', 'like', '%'.$request->mun.'%')
            ->orWhere('province', 'like', $request->prv)
            ->where('municipality_name', 'LIKE', '%'.$request->mun.'%')
            ->limit(1)
            ->get();
        
        foreach($rawCoords as $coord){
            return array(
                "lng" => $coord->lng,
                "lat" => $coord->lat
            );
        }
    }

    public function import_file_metrics(Request $request){
      

        //   dd($request->all());

        try {
            $file = $request->file('file');
        //   dd($file);
        $filenamewithextension = $file->getClientOriginalName();
        // dd($filenamewithextension);
        $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
        // dd($filename);
        $extension = $file->getClientOriginalExtension();


        $filenametostore = $request->categ.'/'.$filename.'.'.$extension;
    
        if($file->getSize() > 60000000){
            return "false";
        }else{
            $status= Storage::disk('ims_files')->put($filenametostore,  fopen($file, 'r+'));
            // return($status);
        
            if($status){
                DB::table("rcef_ims.tbl_file_metrics")
                    ->insert([
                        "file_name"=> $filename,
                        "file_type" => $extension,
                        "file_size" => $file->getSize(),
                        "file_path" =>  'public/ims/'.$filenametostore,
                        "author" => $request->categ,
                        
                    ]
                    );
    
    
                return "true";
            }else{
                return "false";
            }
        }
        } catch (\Throwable $th) {
           return $th->getMessage();
        }
       

     

    }

    public function getRecentFiles(){
        $rawFiles = DB::table("rcef_ims.tbl_file_metrics")
            ->orderBy("date_uploaded", "DESC")
            ->limit(6)
            ->get();
        
        $returnFiles = array();
        foreach($rawFiles as $file){
            array_push($returnFiles, $file);
        }

        return $returnFiles;
    }

    public function getAllFiles(){
        $rawFiles = DB::table("rcef_ims.tbl_file_metrics")
            ->get();
        
        $returnFiles = array();
        foreach($rawFiles as $file){
            array_push($returnFiles, $file);
        }

        return $returnFiles;
    }

    public function getPsgc(Request $request){
        $category = $request->cat;
        $value = $request->val;

        if($value == "0"){
            return null;
        }

        $return = "";
        $dbRaw = "";

        if($category == "reg"){
            $dbRaw = DB::table('rcef_ims.lib_regions')
                ->select('psg_code')
                ->where('regName', 'LIKE', $value)
                ->get();
        }
        if($category == "prv"){
            $dbRaw = DB::table('rcef_ims.lib_provinces')
                ->select('psg_code')
                ->where('provDesc', 'LIKE', $value)
                ->get();
        }
        if($category == "mun"){
            $prov = $request->prv;
            $dbRaw = DB::table('rcef_ims.lib_municipalities')
                ->select(DB::raw('replace(mun, "PH", "") as psg_code'))
                ->where('province', 'LIKE', $prov)
                ->where('municipality', 'LIKE', $value)
                ->get();
        }

        return array(
            "code" => $dbRaw[0]->psg_code
        );
    }

    public function getConvergence(Request $request){
        $convergenceLevel = 0;
        if($request->cat == "reg"){
            return 0;
        }else if($request->cat == "prv"){
            $tinyprv = substr($request->val, 0, 6);
            $season = $request->season;
            $rawTable = DB::table('rcef_ims.tbl_dashboard_seeds')
            ->select(DB::raw('sum(delivered+distributed+no_beneficiaries) as summary'))
            ->where('psgc', 'like', $tinyprv.'%')
            ->where('season', 'like', '%'.$season.'%')
            ->get();
            $countSeed = $rawTable[0]->summary;

            $prv = str_replace('PH', '', $request->val);
            $yr = $request->year;
            $url = "https://rcefis.philmech.gov.ph/api/CountFca?year=".$yr."&provid=".$prv;
            $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n".
                            "Cookie: foo=bar\r\n".
                            "XApiKey: pgH7QzFHJx4w46fI~5Uzi4RvtTwlEXp"
                ),
                "ssl" => array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                )
              );

            $response = file_get_contents($url, false, stream_context_create($opts));
            $countMech = json_decode($response, true);
            if($countSeed > 0){
                $convergenceLevel += 1;
            }
            if($countMech > 0){
                $convergenceLevel += 1;
            }

            return json_encode($convergenceLevel);

        }else if($request->cat == "mun"){

            $tinymun = substr($request->val, 0, 8);
            $season = $request->season;
            $rawTable = DB::table('rcef_ims.tbl_dashboard_seeds')
            ->select(DB::raw('sum(delivered+distributed+no_beneficiaries) as summary'))
            ->where('psgc', 'like', $tinymun.'%')
            ->where('season', 'like', '%'.$season.'%')
            ->get();
            $countSeed = $rawTable[0]->summary;

            $mun = str_replace('PH', '', $request->val);
            $yr = $request->year;
            $url = "https://rcefis.philmech.gov.ph/api/CountFca?year=".$yr."&munid=".$mun;
            $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n".
                            "Cookie: foo=bar\r\n".
                            "XApiKey: pgH7QzFHJx4w46fI~5Uzi4RvtTwlEXp"
                ),
                "ssl" => array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                )
              );

            $response = file_get_contents($url, false, stream_context_create($opts));
            $countMech = json_decode($response, true);
            if($countSeed > 0){
                $convergenceLevel += 1;
            }
            if($countMech > 0){
                $convergenceLevel += 1;
            }

            return json_encode($convergenceLevel);
        }
    }

    public function getCreditDisplayData(Request $request){
        $request = json_decode(request()->getContent(), true);
        $cat = $request["cat"]; // Access the value of "cat"
        $reg = $request["reg"]; // Access the value of "reg"
        $prv = $request["prv"]; // Access the value of "prv"
        $mun = $request["mun"]; // Access the value of "mun"
        $year = $request["year"]; // Access the value of "year"

        if($cat == "nat"){
            return DB::table("rcef_ims.tbl_data_credits")
                ->select(
                    DB::raw("COUNT(id) as fca_total"),
                    DB::raw("SUM(fca_member_total) as fca_member_total"),
                    DB::raw("SUM(total_released_amount) as total_released_amount"),
                    DB::raw("SUM(loan_amount) as loan_amount")
                )
                ->selectRaw("MAX(date_synced) as as_of")
                ->whereYear("date_transaction_released", '=', $year)
                ->get();
        }else if($cat == "reg"){
            $raw = DB::table("rcef_ims.tbl_data_credits")
                ->select(
                    DB::raw("COUNT(id) as fca_total"),
                    DB::raw("SUM(fca_member_total) as fca_member_total"),
                    DB::raw("SUM(total_released_amount) as total_released_amount"),
                    DB::raw("SUM(loan_amount) as loan_amount")
                )
                ->selectRaw("MAX(date_synced) as as_of")
                ->where("region_name", "like", $reg)
                ->whereYear("date_transaction_released", '=', $year)
                ->groupBy("region_name")
                ->get();
            if(count($raw) > 0){
                return $raw;
            }else{
                return [array(
                    "fca_total" => 0,
                    "fca_member_total" => 0,
                    "total_released_amount" => 0,
                    "loan_amount" => 0
                )];
            }
        }else if($cat == "prv"){
            $raw = DB::table("rcef_ims.tbl_data_credits")
                ->select(
                    DB::raw("COUNT(id) as fca_total"),
                    DB::raw("SUM(fca_member_total) as fca_member_total"),
                    DB::raw("SUM(total_released_amount) as total_released_amount"),
                    DB::raw("SUM(loan_amount) as loan_amount")
                )
                ->selectRaw("MAX(date_synced) as as_of")
                ->where("region_name", "like", $reg)
                ->where("province_name", "like", $prv)
                ->whereYear("date_transaction_released", '=', $year)
                ->groupBy("province_name")
                ->get();
            if(count($raw) > 0){
                return $raw;
            }else{
                return [array(
                    "fca_total" => 0,
                    "fca_member_total" => 0,
                    "total_released_amount" => 0,
                    "loan_amount" => 0
                )];
            }
        }else if($cat == "mun"){
            $raw = DB::table("rcef_ims.tbl_data_credits")
                ->select(
                    DB::raw("COUNT(id) as fca_total"),
                    DB::raw("SUM(fca_member_total) as fca_member_total"),
                    DB::raw("SUM(total_released_amount) as total_released_amount"),
                    DB::raw("SUM(loan_amount) as loan_amount")
                )
                ->selectRaw("MAX(date_synced) as as_of")
                ->where("region_name", "like", $reg)
                ->where("province_name", "like", $prv)
                ->where("municipality_name", "like", $mun)
                ->whereYear("date_transaction_released", '=', $year)
                ->groupBy("municipality_name")
                ->get();
            if(count($raw) > 0){
                return $raw;
            }else{
                return [array(
                    "fca_total" => 0,
                    "fca_member_total" => 0,
                    "total_released_amount" => 0,
                    "loan_amount" => 0
                )];
            }
        }
    }

    public function receiveCredsData(Request $request){
        $data = json_decode(request()->getContent(), true);

        $filteredData = array_filter($data, function ($record) {
            // Add your validation logic here
            // For example, check if any value is NULL or empty, and exclude the record if necessary
            return !in_array(null, $record, true) && !in_array('', $record, true);
        });

        $rawResult = DB::table("rcef_ims.tbl_data_credits")->insert($filteredData);
        
        if($rawResult == "1" || $rawResult == 1){
            return response()->json(['message' => 'Data inserted successfully']);
        }else{
            return response()->json(['message' => '0 inserted data']);
        }
    }

    public function receiveExtData(Request $request){
        $data = json_decode(request()->getContent(), true);

        $filteredData = array_filter($data, function ($record) {
            // Add your validation logic here
            // For example, check if any value is NULL or empty, and exclude the record if necessary
            return !in_array(null, $record, true) && !in_array('', $record, true);
        });

        $rawResult = DB::table("rcef_ims.tbl_data_ext")->insert($filteredData);
        
        if($rawResult == "1" || $rawResult == 1){
            return response()->json(['message' => 'Data inserted successfully']);
        }else{
            return response()->json(['message' => '0 inserted data']);
        }
    }

    public function _getExtData($reg, $prv, $mun, $yr){
        if($reg == "0"){
            $reg = "%";
        }
        if($prv == "0"){
            $prv = "%";
        }
        if($mun == "0"){
            $mun = "%";
        }
        if($yr == "0"){
            $yr = "%";
        }



        $raw = DB::table("rcef_ims.tbl_data_ext")
            ->select(
                DB::raw("COUNT(tesda_ptc) AS t_ptc"),
                DB::raw("SUM(rcef_ffs) AS farm_sch"),
                DB::raw("SUM(tot_dm_om + tot_hq + tot_dac + tot_pnm) as tr_trained"),
                DB::raw("SUM(operators) as ope_trained"),
                DB::raw("SUM(info_hubs) as inf_hubs"),
                DB::raw("SUM(rcef_lsa_est) as rcef_lsa"),
                DB::raw("SUM(seed_grower + seed_analyst + seed_inspector + seed_other) as fr_trained"),
                DB::raw("SUM(fca_assisted) as fca_asstd"),
                DB::raw("SUM(fca_models) as fca_model"),
                DB::raw("SUM(FITS) as fits")
            )
            ->where("region", "LIKE", "%".$reg."%")
            ->where("province", "LIKE", "%".$prv."%")
            ->where("municipality", "LIKE", "%".$mun."%")
            ->where("year", "LIKE", "%".$yr."%")
            ->first();

        return (json_encode($raw));
    }

    public function makeUserPw($pw){
        return Hash::make($pw);
    }


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Config;
use DB;
use Session;
use Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class farmerProfile extends Model
{
        


     function makeTable_gen_data($region,$province,$municipality,$prv){
     //dd("execute new reports");

   
        ini_set('memory_limit', '-1');
        DB::beginTransaction();
      //dd($prv);
        try{
    

            \Config::set('database.connections.reports_db.host', 'localhost');
            \Config::set('database.connections.reports_db.port', '4409');
            \Config::set('database.connections.reports_db.database', null);
            \Config::set('database.connections.reports_db.username', 'rcef_web');
            \Config::set('database.connections.reports_db.password', 'SKF9wzFtKmNMfwy');
   /*
            \Config::set('database.connections.reports_db.host', 'localhost');
            \Config::set('database.connections.reports_db.port', '4406');
            \Config::set('database.connections.reports_db.database', null);
            \Config::set('database.connections.reports_db.username', 'root');
            \Config::set('database.connections.reports_db.password', '');
        */
    
            DB::purge('reports_db');
            
                $prv_database = $GLOBALS['season_prefix']."prv_".substr($prv, 0, 4);

                $query = "CREATE DATABASE IF NOT EXISTS $prv_database";

                DB::connection('reports_db')->statement($query);
                    \Config::set('database.connections.reports_db.database', $prv_database);
                    DB::purge('reports_db');
                    $table_name = 'verified_farmer_list';
                    $primary_key = 'id';
                    $fields = [
                        ['name' => 'farmerID', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'distributionID', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'lastName', 'type' => 'string', 'limit' => '75'],
                        ['name' => 'firstName', 'type' => 'string', 'limit' => '75'],
                        ['name' => 'midName', 'type' => 'string', 'limit' => '75'],
                        ['name' => 'extName', 'type' => 'string', 'limit' => '20'],
                        ['name' => 'fullName', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'sex', 'type' => 'string', 'limit' => '15'],
                        ['name' => 'birthdate', 'type' => 'string', 'limit' => '20'],
                        ['name' => 'region', 'type' => 'string', 'limit' => '75'],
                        ['name' => 'province', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'municipality', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'barangay', 'type' => 'string', 'limit' => '100'],
                        ['name' => 'affiliationType', 'type' => 'string', 'limit' => '75'],
                        ['name' => 'affiliationName', 'type' => 'string', 'limit' => '255'],
                        ['name' => 'affiliationAccreditation', 'type' => 'string', 'limit' => '20'],
                        ['name' => 'isDaAccredited', 'type' => 'integer', 'limit' => '11'],
                        ['name' => 'isLGU', 'type' => 'integer', 'limit' => '11'],
                        ['name' => 'rsbsa_control_no', 'type' => 'text'],
                        ['name' => 'isNew', 'type' => 'integer', 'limit' => '1'],  
                        ['name' => 'send', 'type' => 'integer', 'limit' => '1'],
                        ['name' => 'area', 'type' => 'float', 'limit' => '5'],
                        ['name' => 'area_harvested', 'type' => 'double'],
                        ['name' => 'actual_area', 'type' => 'float', 'limit' => '5'],
                        ['name' => 'season', 'type' => 'string', 'limit' => '15'],  
                        ['name' => 'yield', 'type' => 'decimal', 'limit' => '5'],
                        ['name' => 'weight_per_bag', 'type' => 'decimal', 'limit' => '5'],   
                        ['name' => 'update', 'type' => 'integer', 'limit' => '1'],   
                          
                    ];
                    $this->createTable($prv_database, $table_name, $fields, $primary_key);
                    $this->verifyDataList($prv,$region,$province,$municipality);
                DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

    }




    public function verifyDataList($code,$region,$province,$municipality){
        try {

            //Method 1-> change area to zero ; 0->retain area
            $areaMethod = 1;
        
            $database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
            $rsbsa_reference = substr($code,0,2)."-".substr($code,2,2);

            if($region == "ILOCOS"){
                 $farmer_profile =DB::table($database.'.farmer_profile')
                    ->where('lastName', '!=', '')
                    ->where('firstName', '!=', '')
                    ->where('rsbsa_control_no', '!=', '')
                     ->where('distributionID', 'like', 'R'.substr($code,0,2).substr($code,2,2).'%')
                    ->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
                    ->orderBy('id')
                    ->get();
                 $farmer_profile = json_decode(json_encode($farmer_profile), true);
                //INSERT DATA TO Verified
               

                DB::table($database.".verified_farmer_list")->truncate();
                // dd($database);
                foreach ($farmer_profile as $key => $value) {
                   $farmer_profile[$key]["id"]=null;
                  // dd($value);
                   
                }

                foreach ($farmer_profile as $key => $value) {
                    DB::table($database.".verified_farmer_list")->insert($value);
                }

            }else{

                $con = $this->set_rpt_db('ls_inspection_db','information_schema');
                    $schema = DB::connection('ls_inspection_db')->table('TABLES')
                        ->where("TABLE_SCHEMA", $database)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->first();
                      if($schema==null){
                      }else{
                         $con = $this->set_rpt_db('ls_inspection_db',$database); 
                            if($con=='Connection Established!'){    

                                $farmer_profile = DB::connection('ls_inspection_db')
                                                ->table('farmer_profile')
                                    ->where('lastName', '!=', '')
                                    ->where('firstName', '!=', '')
                                    ->where('rsbsa_control_no', '!=', '')
                                    ->where('distributionID', 'like', 'R'.substr($code,0,2).'%')
                                    ->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
                                    ->orderBy('id')
                                    ->get();

                                if(count($farmer_profile) > 0){

                                    foreach ($farmer_profile as $key => $value) {
                                        unset($farmer_profile[$key]->id);
                                        $check_release = DB::table($database.".released")
                                            ->where("farmer_id", $value->farmerID)
                                            ->where("rsbsa_control_no", $value->rsbsa_control_no)
                                            ->first();
                                            if(count($check_release)<=0){
                                                    if($areaMethod == 1){
                                                        $farmer_profile[$key]->area = 0.00;
                                                        $farmer_profile[$key]->actual_area = 0.00;
                                                    }
                                            }else{
                                            }
                                    
                                                $farmer_profile[$key]->region = $region;
                                                $farmer_profile[$key]->province = $province;

                                    }


                                         $farmer_profile = json_decode(json_encode($farmer_profile), true);

                                        // dd($farmer_profile);
                                        //INSERT DATA TO Verified
                                        DB::table($database.".verified_farmer_list")->truncate();
                                        foreach ($farmer_profile as $key => $value) {
                                          //  dd($value);
                                            DB::table($database.".verified_farmer_list")->insert($value);
                                        }
                                }
                            }
                      }           
            }
        } catch (Exception $e) {
            dd($e);
        }
        $con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');    
    }


     public function set_rpt_db($conName,$database_name){
        try {
            \Config::set('database.connections.'.$conName.'.database', $database_name);
            DB::purge($conName);

            DB::connection($conName)->getPdo();
            return "Connection Established!";
        } catch (\Exception $e) {
            //$table_conn = "Could not connect to the database.  Please check your configuration. error:" . $e;
            //return $e."Could not connect to the database";
            return "Could not connect to the database";
            //return "error";
        }
    }
    
    



     public function add_released_data($code,$region,$province,$municipality){
        try {
            $i = 0;
            //Method 1-> change area to zero ; 0->retain area
            $areaMethod = 1;
        
            $database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
            $rsbsa_reference = substr($code,0,2)."-".substr($code,2,2);
       

                    $schema = DB::table('information_schema.TABLES')
                        ->where("TABLE_SCHEMA", $database)
                        ->where("TABLE_NAME", "farmer_profile")
                        ->first();
              if($schema==null){
              }else{   
                    $released = DB::table($database.".released")
                        ->where("municipality", $municipality)
                        ->where("app_version", "!=","v2.08")
                        ->get();

                    foreach ($released as $released_info) {
                        $checkIfAlreadyInserted = DB::table($database.".verified_farmer_list")
                            ->where("farmerID", $released_info->farmer_id)
                            ->where("rsbsa_control_no", $released_info->rsbsa_control_no)
                            ->first();

                            if(count($checkIfAlreadyInserted)<=0){
                                $farmer_profile = DB::table($database.'.farmer_profile')
                                ->where("farmerID", $released_info->farmer_id)
                                ->where("rsbsa_control_no", $released_info->rsbsa_control_no)
                                ->first();

                                if(count($farmer_profile)>0){
                                    $i++;
                                    $farmer_profile = json_decode(json_encode($farmer_profile), true);
                                    DB::table($database.".verified_farmer_list")->insert($farmer_profile);
                                }
                            }
                    }

    
                }
                   
                return $i;
        } catch (Exception $e) {
            dd($e);
        }
           
    }




















    public function createTable($database_name, $table_name, $fields = [], $primary_key){   
        \Config::set('database.connections.reports_db.database', $database_name);
        DB::purge('reports_db');
        
        // echo $primary_key;die();
        $Pkey = $primary_key;
        // check if table is not already exists
        if (!Schema::connection('reports_db')->hasTable($table_name)) {
            Schema::connection('reports_db')->create($table_name, function (Blueprint $table) use ($fields, $table_name, $primary_key) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';
                $table->increments($primary_key);
                //$table->integer($primary_key)->primary();
                if (count($fields) > 0) {
                    foreach ($fields as $field) {
                        if($field['type'] == 'integer'){
                            $table->{$field['type']}($field['name'])->unsigned();
                        }elseif($field['type'] == 'timestamp'){
                            $table->{$field['type']}($field['name'])->useCurrent();
                        }elseif(isset($field['limit'])){
                            $table->{$field['type']}($field['name'], $field['limit']);
                        }else{
                            $table->{$field['type']}($field['name']);
                        }
                    }
                }
            });
                 $trunc = DB::table($database_name.'.'.$table_name)->truncate();
            return response()->json(['message' => 'Given table has been successfully created!'], 200);
        }
         $trunc = DB::table($database_name.'.'.$table_name)->truncate();
        return response()->json(['message' => 'Given table is already existis.'], 400);
    }


}

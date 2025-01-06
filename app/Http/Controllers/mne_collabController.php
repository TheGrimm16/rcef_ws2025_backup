<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Checking;
use Yajra\Datatables\Datatables;

use DB;
use Auth;
use Excel;
class mne_collabController extends Controller {

    public function run_on_server(){

        $cmd ="start chrome http://localhost/rcef_ws2022/get_mne_excel";
             exec($cmd);

    

    }

    public function get_mne_excel(){

        $list =  DB::table("INFORMATION_SCHEMA.TABLES")
             ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."rcep_monitoring_evaluation")
                ->limit(1)
             ->groupBy("TABLE_NAME")
             ->get();
             dd($list);
         $tbl_arr = array();
             foreach($list as $tbl){
                     $data = DB::table($GLOBALS['season_prefix']."rcep_monitoring_evaluation.".$tbl->TABLE_NAME)
                         //->orderBy("date_updated", "DESC")
                         ->get();
                 if(count($data)>0){
                     $data[0]->mne_table_name = $tbl->TABLE_NAME;
                     
                 }
                 array_push($tbl_arr, array(
                     "tbl_name" => $tbl->TABLE_NAME,
                     "data" => $data
                 ));


             }
             $path = public_path('mne_db\\');
             dd($path);
             $excel_data = json_decode(json_encode($tbl_arr), true); //convert collection to associative array to be converted to excel
              Excel::create("MNE_".date("Y-m-d"), function($excel) use ($excel_data) {
                 
                 foreach($excel_data as $exc ){
                     $inf = $exc["data"];
                     $title = substr($exc["tbl_name"], 0, 30);
 
                    $excel->sheet($title, function($sheet) use ($inf) {
                     $sheet->fromArray($inf);
                 }); 
                 }
             })->store("xlsx", $path);
        //rcef_ws2022\public\mne_db\"
            //  //INSERT 

            DB::table($GLOBALS['season_prefix']."rcep_monitoring_evaluation.excel_processed")
             ->where("file","MNE_".date("Y-m-d"))
            ->delete();

             DB::table($GLOBALS['season_prefix']."rcep_monitoring_evaluation.excel_processed")
                ->insert([
                    "file" =>"MNE_".date("Y-m-d")
                ]);




     }
  

}

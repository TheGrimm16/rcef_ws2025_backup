<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Excel;
use DB;
use App\Http\Controllers\Controller;

class wishListController extends Controller
{

    public function reprocessView(){
        return view('wishlist.index');
    }

    public function download_excel_allfarmer(){
       //42
       
        $prv = DB::table("information_schema.views")
            ->where("TABLE_SCHEMA","database_prv_view")
            ->skip(0)
            ->take(5)
            ->groupBy("TABLE_NAME")
            ->orderBy("TABLE_NAME")
            ->get();

        $tbl_arr = array();

        foreach($prv as $data){
            $excel = DB::table("database_prv_view.".$data->TABLE_NAME)
                    ->groupBy("rsbsa_control_no")
                    ->groupBy("lastName")
                    ->groupBy("firstName")
                    ->where("season_entry", "ws2021")
                    ->get();
 
           $province =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->where("prv_code", substr($data->TABLE_NAME,4,4))
                ->value("province");



            array_push($tbl_arr, array(
                "sheet_name" => $province,
                "data" => json_decode(json_encode($excel),true), 
            ));  


        
        }

    

        return Excel::create("FARMER LIST".date("Y-m-d g:i A"), function($excel) use ($tbl_arr) {

            foreach($tbl_arr as $exc ){
                $data = $exc["data"];
                $excel->sheet($exc["sheet_name"], function($sheet) use ($data) {
                    $sheet->fromArray($data);
                    $sheet->freezeFirstRow();
                    
                });

            }
        })->download('xlsx');

    }


    public function reprocess(Request $request){
       
         $viewlist = DB::table('final_uniquelist.tbl_prv_list')->where('status',0)->get();
        
        foreach ($viewlist as $key => $value) {
            DB::beginTransaction();
            try {
                $tableDetails = DB::table('information_schema.TABLES')
                ->where('TABLE_SCHEMA','final_uniquelist')
                ->where('TABLE_NAME',$value->prv_name_view)->get();
                if(count($tableDetails)==0){
                    $db='final_uniquelist';
                    \Config::set('database.connections.table_creator.database', $db);
                    DB::purge("table_creator");
                    DB::connection("table_creator")->getPdo();
                   
                    $sql = "CREATE TABLE ".$value->prv_name_view." (
                        `id`	int(11),
                        `rsbsa_control_no`	varchar(150) NOT NULL,
                        `farmer_id`	varchar(200) NOT NULL,
                        `distribution_id`	varchar(200) NOT NULL,
                        `lastName`	varchar(200) NOT NULL,
                        `firstName`	varchar(200) NOT NULL,
                        `midName`	varchar(200) NOT NULL,
                        `extName`	varchar(200) NOT NULL,
                        `sex`	varchar(20) NOT NULL,
                        `actual_area`	varchar(200) NOT NULL,
                        `da_area`	varchar(200) NOT NULL,
                        `icts_rsbsa`	varchar(200) NOT NULL,
                        `province`	varchar(200) NOT NULL,
                        `municipality`	varchar(200) NOT NULL,
                        `barangay`	varchar(200) NOT NULL,
                        `mother_lname`	varchar(200) NOT NULL,
                        `mother_fname`	varchar(200) NOT NULL,
                        `mother_mname`	varchar(200) NOT NULL,
                        `mother_ename`	varchar(200) NOT NULL,
                        `birthdate`	varchar(200) NOT NULL,
                        `phone`	varchar(200) NOT NULL,
                        `season_entry`	varchar(255) NOT NULL
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                    DB::connection('table_creator')->select(DB::raw($sql));
                
                    $sql = "ALTER TABLE ".$value->prv_name_view."
                    ADD PRIMARY KEY (`id`);";
                    DB::connection('table_creator')->select(DB::raw($sql));
        
                    $sql = "ALTER TABLE ".$value->prv_name_view."
                    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
                    DB::connection('table_creator')->select(DB::raw($sql));
                }

                

                $season_arr = array("ds2022","ws2021","ds2021","ws2020");

                $data = DB::table('database_prv_view_final.'.$value->prv_name_view)->get();
           
                foreach ($data as $key => $tmpvalue) {
                    $area=0;
                    foreach($season_arr as $season){
                    
                        $tmps = DB::table('database_prv_view.'.$value->prv_name_view)
                       ->where('rsbsa_control_no', $tmpvalue->rsbsa_control_no)
                       ->where('farmer_id', $tmpvalue->farmer_id)
                       ->where('lastName', $tmpvalue->lastName)
                       ->where('firstName', $tmpvalue->firstName)
                       ->where('season_entry', $season)
                       ->first();
                        if(isset($tmps)){
                            if($tmps->actual_area >30 || $tmps->actual_area == 0 ){
                                continue;
                            }
                            $tmpvalue->actual_area =$tmps->actual_area;
                            $tmpvalue->season_entry =$tmps->season_entry;
                            break;
                        }
                    
                    }      
                $tmpvalue->id = null;
                DB::table('final_uniquelist.'.$value->prv_name_view)
                ->insert(json_decode(json_encode($tmpvalue), true) );                       
                }
                


                
                DB::table('final_uniquelist.tbl_prv_list')
                ->where('id',$value->id)
                ->update([
                    'status' => 1
                ]);
                
                DB::commit();

             
                return $value->prv_name_view;
            } catch (\Throwable $th) {
                DB::rollback();
            return response([
                'message' => 'Error Getting Labor',
                'error' => $th->getMessage(),
            ], 500);
            }
           
        }
    }
    public function wishListController_data($request){
        $excel_data = array();
        $prvViewList = DB::table('information_schema.VIEWS')->where('TABLE_SCHEMA' ,  'database_prv_view_final')->groupby('TABLE_NAME')->get();
        if($request == "unique"){
           // return$excel_data  =  $this->uniqueList($prvViewList);
        } else if($request == "gender"){
           // return    $excel_data  =  $this->genderData($prvViewList);            
            
        }else if($request == "age"){
          // return $excel_data =    $this->ageCount($prvViewList);   
        }else if($request == "count"){
          // return $excel_data = $this->noOfclaim($prvViewList);   
        }


       /*  $excel_data = json_decode(json_encode($excel_data), true); //convert collection to associative array to be converted to excel
       return Excel::create("wishlist"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data, $documentation) {
           $excel->sheet("Sheet 1", function($sheet) use ($excel_data) {
               $sheet->fromArray($excel_data);                              
           });          
          
       })->download('xlsx');  */
       
    }

    private function noOfclaim($prvViewList){
        $uniqueCountPerPrv =array();
            $total=0;
            foreach ($prvViewList as $key => $value) {
                $tmp = array();
                $x=0;
                $age ="";
            
                $ageRange ="";

                $prvData = explode("_", $value->TABLE_NAME);

                 
                    $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where('lib_prv.prv_code', $prvData[1])
                    ->first();

                      $dataPrvView = DB::table('database_prv_view_no_of_claim_final.'.$value->TABLE_NAME)
                    ->select(DB::raw('noOfclaim,COUNT(noOfclaim)as noOfclaim_cout,\''.$lib_prv->province.'\' as province'))
                    ->groupBy('noOfclaim')
                    ->get();   

                  
                   

                  //  array_push($uniqueCountPerPrv, json_decode(json_encode($dataPrvView),true) );

                    DB::beginTransaction();
                    try {
                        //code...
                        DB::table('unique_list_data.count')
                        ->insert(json_decode(json_encode($dataPrvView),true)); 
                        DB::commit();
                    } catch (\Throwable $th) {
                        //throw $th;
                          DB::rollback();
                    }
                    }
        
                   

                    return "ok";
           
    }
    private function ageCount($prvViewList){
        $uniqueCountPerPrv =array();
        $total=0;
        foreach ($prvViewList as $key => $value) {
            
         $tmp = array();
                $x=0;
                $age ="";
                $totalAge =0;
                $ageRange ="";
                $tbl_18_39 = 0;
                $tbl_40_59 = 0;
                $tbl_60 = 0;
                while($x<3){
                    $gender = "";
                    if($x==0){
                        $age = "DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),`birthdate`)), '%Y')+0 >=18 && DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),`birthdate`)), '%Y')+0 <=39";
                        $ageRange ="18-39";
                    }else if($x ==1){
                        $age = "DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),`birthdate`)), '%Y')+0 >=40 && DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),`birthdate`)), '%Y')+0 <=59";                   
                        $ageRange ="40-59";
                    }else if($x ==2){
                        $age = "DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),`birthdate`)), '%Y')+0 >=60";                   
                        $ageRange ="60 above";
                    }
                     $dataPrvView = DB::table('database_prv_view_final.'.$value->TABLE_NAME)
                    ->whereRaw("".$age)
                    ->get();
                   
                    $totalAge += count( $dataPrvView);
                    $prvData = explode("_", $value->TABLE_NAME);
    
                    $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where('lib_prv.prv_code', $prvData[1])
                    ->first();
                   
    
                    array_push($tmp, array(
                        $ageRange => count( $dataPrvView) )
                    );

                    if($x==0){
                        $tbl_18_39 = count($dataPrvView);
                    }else if($x ==1){
                        $tbl_40_59 = count($dataPrvView);
                    }else if($x ==2){
                       $tbl_60 = count($dataPrvView);
                    }

                    $x++;
                }
                $dataPrvView = DB::table('database_prv_view_final.'.$value->TABLE_NAME)              
                ->get();
               
                array_push($tmp, array(
                    "invalid" => count( $dataPrvView)-$totalAge,
                    "total" => count( $dataPrvView)
                     ) 
                    
                );

              /*   array_push($uniqueCountPerPrv, array(
                    $lib_prv->province => $tmp )
                ); */

                array_push($uniqueCountPerPrv, array(
                    'province'=>$lib_prv->province, 
                    'tbl_18_39'=> $tbl_18_39, 
                    'tbl_40_59'=> $tbl_40_59, 
                    'tbl_60_above'=> $tbl_60, 
                    'invalid'=>count( $dataPrvView)-$totalAge,
                    'total'=> count( $dataPrvView)
                    )
                );


               
            }

            DB::beginTransaction();
            try {
                //code...
                DB::table('unique_list_data.gender')
                ->insert($uniqueCountPerPrv); 
                DB::commit();
                return "ok";
            } catch (\Throwable $th) {
                //throw $th;
                  DB::rollback();
            }
           // return $uniqueCountPerPrv;
            
    }
    private function genderData($prvViewList){
        
        //SELECT * FROM `VIEWS` where TABLE_SCHEMA = 'database_prv_view_final';
        $uniqueCountPerPrv =array();
         $total=0;
        foreach ($prvViewList as $key => $value) {
            $tmp = array();
            $x=0;
            $totalGender =0;
            $maleCount =0;
            $femaleCount = 0;
            while($x<2){
                $gender = "";
                if($x==1){
                    $gender = "M";
                }else{
                    $gender = "F";
               
                }
                 $dataPrvView = DB::table('database_prv_view_final.'.$value->TABLE_NAME)
                ->whereRaw('substring(sex,1,1 ) = "'.$gender.'"')
                ->get();
               
                $totalGender += count( $dataPrvView);
                $prvData = explode("_", $value->TABLE_NAME);

                $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('lib_prv.prv_code', $prvData[1])
                ->first();
               
                if($x==1){
                    $maleCount =count($dataPrvView);
                }else{
                    $femaleCount = count($dataPrvView);
               
                }
                
                array_push($tmp, array(
                    $gender => count( $dataPrvView) )
                );
                $x++;
            }
            $otherGender = DB::table('database_prv_view_final.'.$value->TABLE_NAME)
            ->whereRaw('substring(sex,1,1) != "M"')
            ->whereRaw('substring(sex,1,1) != "F"')
            ->get();
            $totalGender += count( $otherGender);
            array_push($tmp, array(
                "others" => count($otherGender),
                "total Gender" =>  $totalGender
            ));
           
            /*            
            array_push($uniqueCountPerPrv, array(
                $lib_prv->province => $tmp 
                )
            ); */

                       
            array_push($uniqueCountPerPrv, array(
                'province' => $lib_prv->province,
                'male' => $maleCount ,
                'female' =>  $femaleCount,
                'others' =>count($otherGender),
                'total' =>  $totalGender
                )
            );

          
           
            
        }

        DB::beginTransaction();
        try {
            //code...
            DB::table('unique_list_data.unique_list_gender')
            ->insert($uniqueCountPerPrv); 
            DB::commit();
            return "ok";
        } catch (\Throwable $th) {
            //throw $th;
              DB::rollback();
        }

        //return $uniqueCountPerPrv;
    }
    private function uniqueList($prvViewList){
                
        $uniqueCountPerPrv =array();
        $total=0;
        foreach ($prvViewList as $key => $value) {
          

            $dataPrvView = DB::table('database_prv_view_final.'.$value->TABLE_NAME)
            ->get(); 
           // return count($dataPrvView);
  
            $prvData = explode("_", $value->TABLE_NAME);

            $lib_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('lib_prv.prv_code', $prvData[1])
            ->first();
           
          
            array_push($uniqueCountPerPrv, array(
                $lib_prv->province => count( $dataPrvView) )
            );
           $total = $total +count($dataPrvView);
        }
        array_push($uniqueCountPerPrv, array(
           "total" => $total )
        );
        return $uniqueCountPerPrv;
    }
}

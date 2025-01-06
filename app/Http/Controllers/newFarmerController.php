<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Datatables;



class newFarmerController extends Controller
{
    public function index(){
        

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $user_provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_prv.province')
            ->groupBy("lib_prv.province")
            ->orderBy("region_sort", 'ASC')
            ->get();
        }else{

            if(Auth::user()->stationId == ""){
                $mss = "No Station Tagged";
                return view("utility.pageClosed")
                    ->with("mss",$mss);
            }else{

                $user_provinces =  DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_station')
                    ->select("province")
                    ->where("stationID", Auth::user()->stationId)
                    ->groupBy("province")
                    ->get();
           
                foreach($user_provinces as $key=> $pr){
             

                    if(Auth::user()->roles->first()->name == "system-encoder"){
                        if($pr->province == "ZAMBALES" || $pr->province == "TARLAC"){
                            
                        }else{
                            unset($user_provinces[$key]);
                            continue;
                        }
                    }
                         
                    // if(Auth::user()->username == 'lgfigueroa_lapaz'){
                    //     $pr->province;
                    //     dd($pr->province);
                    // }



                    $prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                    ->where("province", $pr->province)
                    ->value("prv_code");
                  
                    $schema_check = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix'].'prv_'.$prv)
                    ->where("TABLE_NAME", 'farmer_information_final')
                    ->first();
          
                    if($schema_check == null){
                        unset($user_provinces[$key]);
                    }

                  


                }
           
            }
        }
 
        $user_provinces = json_decode(json_encode($user_provinces), true);

        $provinces = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->whereIn("province", $user_provinces)
                ->groupBy('province')->get();
        //    dd($provinces);
        return view('virtual_encoding.new_farmer_encoded',compact('provinces'));
    

    }


    private function search_to_array($array, $key, $value) {
        $results = array();
        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }
            foreach ($array as $subarray) {
                $results = array_merge($results, $this->search_to_array($subarray, $key, $value));
            }
        }
        return $results;
    }
    

    public function new_farmer_list(Request $request){
        if($request->rsbsa_search == ""){ $request->rsbsa_search = "%"; }
        if($request->last_search == ""){ $request->last_search = "%"; }
        if($request->first_search == ""){ $request->first_search = "%"; }
        $db_prefix = $GLOBALS['season_prefix'];

        $prv_no = DB::table($db_prefix."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->first();

        $prv_lib = DB::table($db_prefix."rcep_delivery_inspection.lib_prv")
            ->where("province", $request->province)
            ->get();
        $prv_lib = json_decode(json_encode($prv_lib), true);

            $list = DB::table($db_prefix."prv_".$prv_no->prv_code.".farmer_information_final_pending")
                ->where("lastName", "LIKE", $request->last_search."%")
                ->where("firstName", "LIKE", $request->first_search."%")
                ->where("rsbsa_control_no", "LIKE", $request->rsbsa_search."%")
                ->orderBy("lastName")
                ->orderBy("firstName")
                ->orderBy("midName")
                ->orderBy("extName") 
                ->get();

            $data = collect($list);
            
            $status_array = array(
                "0" => "Pending",
                "1" => "Approved",
                "2" => "Disapproved"
            );

            return Datatables::of($data)

 
            ->addColumn('rsbsa', function($row)  {
                 return $row->rsbsa_control_no;
             })
             ->addColumn('name', function($row)  {
                return $row->lastName." ".$row->extName.", ".$row->firstName." ".$row->midName;
            })
            ->addColumn('parcel', function($row) use ($prv_lib)  {
                
                $municipality =  $this->search_to_array($prv_lib, "prv", str_replace("-","",$row->claiming_prv));
                
                return $municipality[0]["municipality"];
            })

            ->addColumn('final_area', function($row)  {
                return number_format($row->final_area,2)." (ha)";
            })

            ->addColumn('sex', function($row)  {
                return $row->sex;
            })
            ->addColumn('birthdate', function($row)  {
                $bday = date("Y-m-d", strtotime($row->birthdate));
                if($bday == "1970-01-01"){
                    $bday = "-";
                }

                return $bday;

            })

            ->addColumn('claimed', function($row)  {
                return "Bags: ". $row->total_claimed." (".number_format($row->total_claimed_area,2)." ha)";
            })
            

            ->addColumn('status', function($row) use ($status_array) {
                $class = "";

                if($row->approval_status == "0"){
                    $class = "badge badge-info";
                }

                if($row->approval_status == "1"){
                    $class = "badge badge-success";
                }

                if($row->approval_status == "2"){
                    $class = "badge badge-warning";
                }

                return "<label id='status_".$row->id."' class='".$class."'>".$status_array[$row->approval_status]."</label>";

            })

            ->addColumn('user', function($row)  {
                return $row->approved_by;
            })
            ->addColumn('action', function($row) use ($prv_no)  {
                $btn = "";

                if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso" || Auth::user()->username == "aa.alonzo"){
                    if($row->approval_status == "0"){
                        $btn .= "<button class='btn btn-success btn-sm' id='approve_".$row->id."' style='width:100%; margin-bottom:2px;'  onclick='approve(".'"'.$row->id.'"'.",".'"'.$prv_no->prv_code.'"'." )' > Confirm </button>";
                        $btn .= "<button class='btn btn-warning btn-sm' id='disapprove_".$row->id."' style='width:100%;'  onclick='disapprove(".'"'.$row->id.'"'.",".'"'.$prv_no->prv_code.'"'." )' > Reject </button>";
                    }

                }

               

                // $tbl = "farmer_information_final";
                // $btn = "<a class='btn btn-success' style='width:100%;'><i class='fa fa-thumbs-o-up' aria-hidden='true' onclick='select_farmer(".'"'.$row->db_ref.'"'.', "'.$prv_db.'"'."); '> Select</i></a>";
                // $btn .= "<a class='btn btn-success' style='width:100%;'><i class='fa fa-external-link-square' aria-hidden='true'  onclick='show_parcelary(".'"'.$prv_db.'"'.',"'.$row->db_ref.'"'.',"'.$tbl.'"'.");'> Check Parcelary</i></a>";
                
                 return $btn;
             })
                ->make(true);




    }


    public function disapprove_new_farmer(Request $request){
    
        $db_prefix = $GLOBALS['season_prefix'];
        
        DB::beginTransaction();

        try {
            
            DB::table($db_prefix."prv_".$request->prv_code.".farmer_information_final_pending")
                ->where("id", $request->id)
                ->update([
                    "approval_status" => 2,
                    "approved_by" => Auth::user()->username
                ]);


            DB::commit();
        
            return json_encode("success");
        } catch (\Throwable $th) {
            DB::rollback();

            return json_encode("failure");
        }
        


    }

    public function approve_new_farmer(Request $request){
        $db_prefix = $GLOBALS['season_prefix'];
        DB::beginTransaction();
        try {
            
            $farmer_info = DB::table($db_prefix."prv_".$request->prv_code.".farmer_information_final_pending")
                ->where("id", $request->id)
                ->where("approval_status",  "0")
                ->first();

            if($farmer_info == null)  {    return json_encode("Server Unreachable"); }
            
            //prv_ref // new_released_id_ref // db_ref
            $release_virtual =  DB::table($db_prefix."prv_".$request->prv_code.".new_released_virtual_pending")
                ->where("db_ref", $farmer_info->db_ref)
                ->get();


            $release =  DB::table($db_prefix."prv_".$request->prv_code.".new_released_pending")
                ->where("db_ref", $farmer_info->db_ref)
                ->get();

           
            //TRANSFER DATA

            $farmer_insert = json_decode(json_encode($farmer_info), true);
            unset($farmer_insert["approval_status"]);
            unset($farmer_insert["approved_by"]);
            unset($farmer_insert["db_ref"]);
            unset($farmer_insert["id"]);
            

            $array_prv_data = array();

               $new_id =  DB::table($db_prefix."prv_".$request->prv_code.".farmer_information_final")
                            ->insertGetId($farmer_insert);

                            DB::table($db_prefix."prv_".$request->prv_code.".farmer_information_final")
                                ->where('id', $new_id)
                                ->update([
                                    "db_ref"=> $new_id
                                ]);
                
            foreach($release as $rel){

                $array_prv_data[$rel->new_released_id] = $rel->prv_dropoff_id;

                $ins_rel = json_decode(json_encode($rel), true);
                unset($ins_rel["new_released_id"]);
                $ins_rel["db_ref"] = $new_id;
                DB::table($db_prefix."prv_".$request->prv_code.".new_released")
                    ->insert($ins_rel);

            }

            foreach($release_virtual as $rel_v){
                $ref = $rel_v->new_released_id_ref;

                $virtual_release_ref = "|".$ref;
                $prv_stocks = str_replace("-","",$farmer_info->claiming_prv);
                $prv_dropoff_id_ref =  $array_prv_data[$ref];

                $ins_rel_v = json_decode(json_encode($rel_v), true);
                $ins_rel_v["db_ref"] = $new_id;
                unset($ins_rel_v["new_released_id"]);
                DB::table($db_prefix."prv_".$request->prv_code.".new_released_virtual")
                    ->insert($ins_rel_v);


                    $stocks = DB::table($db_prefix."rcep_delivery_inspection.tbl_actual_delivery_virtual_pending")
                        ->where("prv_dropoff_id_ref", $prv_dropoff_id_ref)
                        ->where("prv", $prv_stocks)
                        ->where("virtual_release_ref", "LIKE", $virtual_release_ref."%")
                        ->first();

                        $stocks = json_decode(json_encode($stocks), true);
                        DB::table($db_prefix."rcep_delivery_inspection.tbl_actual_delivery_virtual_pending")
                            ->insert($stocks);
            }

            DB::table($db_prefix."prv_".$request->prv_code.".farmer_information_final_pending")
                ->where("id", $request->id)
                ->update([
                    "is_new" => $new_id,
                    "approval_status" => 1,
                    "approved_by" => Auth::user()->username
                ]);


            DB::commit();
        
            return json_encode("success");
        } catch (\Throwable $th) {
            DB::rollback();

            return json_encode("failure");
        }
        




    }

}

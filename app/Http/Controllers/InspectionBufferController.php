<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;
use Hash;

use App\SeedInspector;
use App\SeedDelivery;
use App\InspectionSchedule;
use Yajra\Datatables\Facades\Datatables;

class InspectionBufferController extends Controller
{
    public function UpdateInspectorTagged(Request $request){
       

        DB::beginTransaction();
        try {
            $inspectors = DB::connection('mysql')->table('users')
            ->select('username')
            ->where('userId', $request->inspectorID)
            ->first();


                if(count($inspectors)>0){
                    DB::connection("ls_inspection_db")->table("tbl_inspection_for_breakdown")
                    ->where("batchTicketNumber",$request->batch_number)
                    ->update([
                        "is_tagged" => 0
                    ]);
        
                    DB::connection("ls_inspection_db")->table("tbl_inspection_for_breakdown")
                    ->insert([
                        "userId" => $request->inspectorID,
                        "remarks" => $request->reason,
                        "batchTicketNumber" => $request->batch_number,
                        "coopId" => $request->cid,
                        "username" => $inspectors->username,
                        "date_created" => date("Y-m-d H:i:s"),
                        "is_tagged" => 1
                    ]);
        
        
        
                    DB::commit();

                    return json_encode("You have successfully updated the assigned seed inspector for the batch ticket number: ".$request->batch_number);
                }else{
                    return json_encode("Saving Failed");

                }

           
            

        } catch (\Throwable $th) {
            DB::rollback();
            return json_encode("Saving Failed");
        
        }

      


    }


    public function InspectorScheduleView(){
        $inspectors = DB::connection('mysql')->table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'username')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', '29')
            ->get();

        $delivery_regions =  DB::connection('ls_inspection_db')
            ->table('tbl_actual_delivery')
            ->where('region', '!=', '')
            ->where('isBuffer', 1)
            ->where("is_hold", 2)
            ->groupBy('region')
            ->get();
  
  
        return view('inspection.buffer_schedule')
            ->with('inspectors', $inspectors)
            ->with('delivery_regions', $delivery_regions);
    }


    public function LoadTaggedInspector(Request $request){
        
        $tagged_list = DB::connection("ls_inspection_db")->table("tbl_inspection_for_breakdown") 
                    ->select("tbl_inspection_for_breakdown.*", "tbl_actual_delivery.dropOffPoint",DB::raw("SUM(totalBagCount) as totalBagCount"), "tbl_actual_delivery.dateCreated as inspection_date")
                    ->join("tbl_actual_delivery", "tbl_actual_delivery.batchTicketNumber", "=", "tbl_inspection_for_breakdown.batchTicketNumber")
                    ->where("tbl_actual_delivery.region", $request->region)
                    ->where("tbl_actual_delivery.province", $request->province)
                    ->where("tbl_actual_delivery.municipality", $request->municipality)
                    ->where("tbl_inspection_for_breakdown.is_tagged", 1)
                    ->groupBy("tbl_inspection_for_breakdown.batchTicketNumber")
                    ->get();

            $tbl_arr = array();
            foreach($tagged_list as $tagged){
                $checkPosted = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
                    ->where("batchTicketNumber", $tagged->batchTicketNumber)
                    ->get();

                    if(count($checkPosted)>0){
                        $coopName = $this->getCoopDetails($tagged->coopId);
                        array_push($tbl_arr, array(
                                "batchTicketNumber" => $tagged->batchTicketNumber,
                                "seed_coop" => $coopName,
                                "dropoff_name" => $tagged->dropOffPoint,
                                "total_dBags" => $tagged->totalBagCount,
                                "date_of_delivery" => $tagged->inspection_date,
                                "action_fld" => "<a href='' class='btn btn-primary' data-cid='".$tagged->coopId."' data-ins_date='".date("Y-m-d", strtotime($tagged->inspection_date))."' data-bags='".$tagged->totalBagCount."' data-coop='".$coopName."' data-dop='".$tagged->dropOffPoint."' data-id='".$tagged->id."' data-inspector='".$tagged->username."' data-batch='".$tagged->batchTicketNumber."' data-toggle='modal' data-target='#view_inspector_modal'> <i class='fa fa-exchange' aria-hidden='true'></i> </a>"
                        ));
                    }else{

                    }







                       
            }

            $data = collect($tbl_arr);
            return Datatables::of($data)
                ->make(true);


    }


    private function getCoopDetails($id){
        $coop =  DB::connection("ls_seed_coop")->table("tbl_cooperatives")
                ->where("coopID", $id)
                ->first();
            if(count($coop)>0){
                return $coop->coopName;
            }else{
                return "N/A";
            }
    }






    public function index(){
        $buffer_data = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                               //->where("dropOffPoint", 'LIKE', '%buffer%')
                               ->where("isBuffer", 1)
                               ->where('totalBagCount', '>', 0)
                               ->where("is_hold", 2)
                               ->groupBy("region")
                               ->get();

                            //    dd($buffer_data);
        $inspector_details = DB::connection('mysql')->table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'username')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', '29')
            ->get();

        return view('inspection.buffer')
            ->with('inspector_details', $inspector_details)
            ->with('buffer_data', $buffer_data);

       // return view('inspection.buffer');
    }

     public function getProvinceDropoffDetails(Request $request){
        if(isset($request->tagged) == 1){
            $is_hold = "%";
        }else{
            $is_hold = 2;
        }


        $provinces = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
            ->where('region', '=', $request->region)
            //->where("dropOffPoint", 'LIKE', '%buffer%')
            ->where("isBuffer", 1)
            ->where("is_hold", "LIKE", $is_hold)
           ->where('totalBagCount', '>', 0)
           ->groupBy("province")
           ->get();

        $province_str = '';
        foreach($provinces as $province){
            $province_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $province_str;
    }


      public function getMunicipalitiesDropoffDetails(Request $request){
        if(isset($request->tagged) == 1){
            $is_hold = "%";
        }else{
            $is_hold = 2;
        }

        $municipalities = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
            ->where('province', '=', $request->province)
            //->where("dropOffPoint", 'LIKE', '%buffer%')
            ->where("isBuffer", 1)
            ->where("is_hold", "LIKE", $is_hold)
           ->where('totalBagCount', '>', 0)
           ->groupBy("municipality")
           ->get();

        $municipalities_str = '';
        foreach($municipalities as $municipality){
            $municipalities_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $municipalities_str;
    }

    public function searchDropOffDelivery(Request $request){

         $raw = "SELECT batchTicketNumber from tbl_actual_delivery where batchTicketNumber NOT IN (SELECT batchTicketNumber from tbl_inspection_for_breakdown where is_tagged = 1) and region='".$request->region."' and province='".$request->province."' and municipality='".$request->municipality."' and isBuffer = 1 and is_hold = 2 group by batchTicketNumber";

        //  if($request->region == "CENTRAL LUZON"){
        //     $raw = "SELECT * from tbl_actual_delivery where  region='".$request->region."' and province='".$request->province."' and municipality='".$request->municipality."' and isBuffer = 1 and is_hold = 2 group by dropOffPoint";  
        //  }

         $batches = DB::connection("ls_inspection_db")->select(DB::raw($raw));

         $batches = json_decode(json_encode($batches), true);

         $get_coop = DB::connection("ls_inspection_db")->table("tbl_delivery")
            ->whereIn("batchTicketNumber", $batches)
            ->groupBy("coopAccreditation")
            ->get();

            $coop_names = '';
            foreach($get_coop as $coop_data){
                $coop = DB::table($GLOBALS['last_season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                    ->where("accreditation_no", $coop_data->coopAccreditation)
                    ->first();
              
                    if(count($coop)>0){
                        $coop_names .= "<option value='$coop->accreditation_no'>$coop->coopName</option>";
                    }else{
                        $coop_names .= "<option value='$coop_data->coopAccreditation'>Cannot Find this Coop</option>";
                    }
            }
        return $coop_names;
    }





public function SelectedDropOffDetails(Request $request){

    $batchTicket = DB::connection('ls_inspection_db')->table('tbl_delivery')                
    ->where('coopAccreditation', '=', $request->coop_accre)
    ->where('region', $request->region)
    ->where('province', $request->province)
    ->where('municipality', $request->municipality)
    ->groupBy("batchTicketNumber")
    ->get();
    $batch_str = '';
    foreach($batchTicket as $tbl_delivery_tickets){



        $check_tagged = DB::connection("ls_inspection_db")->table("tbl_inspection_for_breakdown")
        ->where("batchTicketNumber", $tbl_delivery_tickets->batchTicketNumber)
        ->where("is_tagged", 1)
        ->get();
    
        if(count($check_tagged)>0){
            continue;
        }

        $batches = DB::connection('ls_inspection_db')->table("tbl_actual_delivery")
            ->select("batchTicketNumber",DB::raw("SUM(totalBagCount) as total_bags"))
            ->where("batchTicketNumber", $tbl_delivery_tickets->batchTicketNumber)
            ->where("isBuffer", 1)
            ->where("is_hold", 2)
            ->groupBy("batchTicketNumber")
            ->first();

        if(count($batches)<=0){
            continue;
        }
    
            

        //append string
      
            $batch_str .= "<label><input type='checkbox' name='batch_tickets[]' value='$batches->batchTicketNumber' checked/> $batches->batchTicketNumber ($batches->total_bags bags)</label><br>";
        

        
    }


 
    $data = array(
        'delivery_date' => date("F j, Y g:i A", strtotime($tbl_delivery_tickets->dateCreated)),
        'batch_string' => $batch_str
    );







    return $data;
}










    public function SelectedDropOffDetails_old(Request $request){
        $dropoff = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')                
                ->where('tbl_actual_delivery.actualDeliveryId', '=', $request->deliveryId)
                ->where("is_hold", 2)
                ->first();
         //   dd($dropoff);
        //get all batch tickets with the same dropoff


         $raw = "SELECT batchTicketNumber, seedVariety, SUM(totalBagCount) as total_bags from tbl_actual_delivery where batchTicketNumber NOT IN (SELECT batchTicketNumber from tbl_inspection_for_breakdown where is_tagged=1) and region='".$dropoff->region."' and province='".$dropoff->province."' and municipality='".$dropoff->municipality."' and prv_dropoff_id = '".$dropoff->prv_dropoff_id."' and is_hold = 2 group by batchTicketNumber";

                  if($dropoff->region == "CENTRAL LUZON"){
                         $raw = "SELECT batchTicketNumber, seedVariety, SUM(totalBagCount) as total_bags from tbl_actual_delivery where region='".$dropoff->region."' and province='".$dropoff->province."' and municipality='".$dropoff->municipality."' and prv_dropoff_id = '".$dropoff->prv_dropoff_id."' and is_hold = 2 group by batchTicketNumber";
                  }

        $batches = DB::connection("ls_inspection_db")->select(DB::raw($raw));

        //append string
        $batch_str = '';
        foreach($batches as $batch){
            $batch_str .= "<label><input type='checkbox' name='batch_tickets[]' value='$batch->batchTicketNumber' checked/> $batch->batchTicketNumber ($batch->total_bags bags)</label><br>";
        }
        
        $data = array(
            'delivery_date' => date("F j, Y g:i A", strtotime($dropoff->dateCreated)),
            'batch_string' => $batch_str
        );

        return $data;
    }



      public function saveInspectorDetails(Request $request){
        if(count($request->batch_tickets) > 0){
            $batch_id_array = array();
             
            foreach($request->batch_tickets as $batch){
                $coop = DB::connection("ls_inspection_db")->table("tbl_delivery")  
                    ->where("batchTicketNumber", $batch)
                    ->value("coopAccreditation");
                  //  dd($batch);

                  
                    if(count($coop)>0){
                        $coopId = DB::connection("ls_seed_coop")->table("tbl_cooperatives")
                            ->where("accreditation_no", $coop)
                            ->value("coopId"); 
                            if(count($coopId)>0){
                                //INSERT
                                     $inspector_details = DB::connection('mysql')->table('users')
                                        ->where('userId', $request->inspectorID)
                                        ->value("username");

                                        if(count($inspector_details)>0){
                                            DB::connection("ls_inspection_db")->table("tbl_inspection_for_breakdown")
                                            ->insert([
                                                "userId" => $request->inspectorID,
                                                "username" => $inspector_details,
                                                "coopId" => $coopId,
                                                "batchTicketNumber"=> $batch,
                                                "remarks" => $request->pmo_remarks,
                                                "date_created" => date("Y-m-d"),
                                                "is_tagged" => 1,
                                            ]);
                                        }else{
                                             Session::flash("error_msg", "Failed To Retrieve username");
                                            return redirect()->route('rcef.inspection.buffer.designation');
                                        }                                
                            }else{
                                Session::flash("error_msg", "Failed To Retrieve Coop Id");
                                return redirect()->route('rcef.inspection.buffer.designation');
                            }
                    }else{
                                     Session::flash("error_msg", "Failed To Retrieve username");
                                      return redirect()->route('rcef.inspection.buffer.designation');
                    }



            }

            Session::flash("success", "you have successfully saved the record!");
            return redirect()->route('rcef.inspection.buffer.designation');

        }else{
            Session::flash("error_msg", "Please select a batch to assign...");
            return redirect()->route('rcef.inspection.buffer.designation');
        }

    }


    private function changeConnection($season,$database_name){
            $conn_string = array();

        $conn_string['ds2020']['host'] = "localhost";
        $conn_string['ds2020']['port'] = "3306";
        $conn_string['ds2020']['user'] = "jpalileo";
        $conn_string['ds2020']['password'] = "P@ssw0rd";


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

            try{
                \Config::set('database.connections.ls_inspection_db.host', $conn_string[$season]['host']);
                \Config::set('database.connections.ls_inspection_db.port', $conn_string[$season]['port']);
                \Config::set('database.connections.ls_inspection_db.database', $database_name);
                \Config::set('database.connections.ls_inspection_db.username', $conn_string[$season]['user']);
                \Config::set('database.connections.ls_inspection_db.password', $conn_string[$season]['password']);
                DB::purge('ls_inspection_db');
                DB::connection('ls_inspection_db')->getPdo();
            
                return "success";
            } catch (\Exception $e) {
                return "failed";        
            }
    }



















}

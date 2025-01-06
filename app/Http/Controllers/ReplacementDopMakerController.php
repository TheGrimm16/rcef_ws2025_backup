<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Input;
use Illuminate\Filesystem\Filesystem;

use Illuminate\Support\Facades\Schema;
use Config;
use DB;
use Excel;

use App\HistoryMonitoring;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\utility;
use App\DropoffPoints;

use Session;
use Auth;
use Illuminate\Support\Facades\Hash;


class ReplacementDopMakerController extends Controller
{

  public function replacementList(Request $request){
        $replacement_list =DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
                ->select("tbl_breakdown_buffer.*", "tbl_actual_delivery_breakdown.totalBagCount")
                ->join("tbl_actual_delivery_breakdown", function($join){
                    $join->on("tbl_actual_delivery_breakdown.seedTag", "=", "tbl_breakdown_buffer.seedTag");
                    $join->on("tbl_actual_delivery_breakdown.batchTicketNumber", "=", "tbl_breakdown_buffer.batchTicketNumber");       
                })
                ->where("tbl_breakdown_buffer.category", "P")
                ->get();

            foreach ($replacement_list as $key => $value) {
                //dd($replacement_list[0]->batchTicketNumber);
                //dd($value->replacement_ticket);
                $replacement_arr = array();
                if($value->replacement_ticket != "-" || $value->replacement_ticket != ""){
                    $rep_ticket = explode("|", $value->replacement_ticket);
                    foreach ($rep_ticket as $tick_key => $ticket_value) {                   
                         array_push($replacement_arr, $ticket_value);
                    }                    


                    $total_replaced = DB::connection("delivery_inspection_db")->table("tbl_delivery")
                        ->whereIn("batchTicketNumber", $replacement_arr)
                        ->where("is_cancelled", "!=", "1")
                        ->sum("totalBagCount");

                     if($total_replaced!=null){
                        if($total_replaced >= $value->totalBagCount){
                            $replacement_list[$key]->disable = "disabled";
                            $replacement_list[$key]->remaining = 0;
                        }else{
                            $replacement_list[$key]->disable = "";
                            $replacement_list[$key]->remaining = $value->totalBagCount - $total_replaced;
                        }
                      }else{
                        $replacement_list[$key]->disable = "";
                            $replacement_list[$key]->remaining = $value->totalBagCount;
                      }
                }else{
                            $replacement_list[$key]->disable = "";
                            $replacement_list[$key]->remaining = $value->totalBagCount;
                        }
            


            }

        return json_encode($replacement_list);
  }


  public function index(){
     // dd(Auth::user()->province);
    $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            // ->where("prv", "LIKE", Auth::user()->province."%")
            ->groupBy("region")
            ->orderby("region_sort", "ASC")
            ->get();

    $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
            ->where('isActive', 1)
            ->get();

    $replacement_reason = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_repalcement_reason")
            ->get();

    return view("dop_replacement_calamities.index")
            ->with("regional_list", $region)
            ->with("replacement_reason", $replacement_reason)
            ->with("coop_list", $coop);

  }

  public function provinceList(Request $request){
    $province = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("regCode", $request->region)
            ->groupBy("province")
            ->orderby("prv", "ASC")
            ->get();
    echo json_encode($province);
  }

  public function municipalList(Request $request){
    $municipality = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("regCode", $request->region)
            ->where("provCode", $request->province)
            ->groupBy("municipality")
            ->orderby("prv", "ASC")
            ->get();
    echo json_encode($municipality);
  }

    public function genTable(Request $request){

        $dopList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point as a")
            // ->join($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as b', 'b.accreditation_no', '=','a.coop_accreditation')
            ->join($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement as c', 'c.prv', '=','a.prv')
            ->select("a.*", "c.replacement_reason" )
            ->where("is_for_replacement","=",1)
            ->groupby("prv_dropoff_id")
            ->orderby("dropoffPointId","DESC")
            ->get(); 

        $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
            ->where("accreditation_no", $request->coop_accre)
            ->first();

        $moa_number = $coop->current_moa;

        $data_arr = array();
        

        foreach($dopList as $row){

            $hasRegionCommitment = $this->hasRegionCommitment($request->coop_accre, $row->region); 
            $coopRegTotalCommit = $this->getCoopRegionalCommitment($request->coop_accre, $row->region);
            $coopRegPending = $this->getCoopRegPending($request->coop_accre, $row->region);
            $coopRegConfirmed  = $this->getCoopRegConfirmed($request->coop_accre, $moa_number, $row->region);
            $coopRegInspected = $this->getCoopRegInspected($request->coop_accre, $row->region);
            $coopRegAllocated =  $coopRegPending + $coopRegConfirmed + $coopRegInspected;
            
            if(isset($coopRegTotalCommit->total)){
                $total = $coopRegTotalCommit->total;
            }else{
                $total = 0;
            }
            $remainingBalance = $total - $coopRegAllocated; 

            array_push($data_arr, array(
                'coop' => $request->coopName,
                'province' => $row->province,
                'municipality' => $row->municipality,
                'dropOffPoint' => $row->dropOffPoint,
                'reason'=>$row->replacement_reason,
                "prv_dropoff_id" => $row->prv_dropoff_id,
                "coop_accre" => $request->coop_accre,
                "region" => $row->region,
                "moa_number"=>$moa_number,
                "remaining_balance" => $remainingBalance
            ));
        }


        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->addColumn('action', function($row) {

        
        // return  "<a href='#' data-coop_accre='".$row["coop_accre"]."' data-province='".$row["province"]."' data-municipality = '".$row["municipality"]."'  data-dop=".$row["dropOffPoint"]." data-prv_id='".$row["prv_dropoff_id"]."' data-toggle='modal' data-coop_name='".$row["coop"]."' data-region='".$row["region"]."' data-moa='".$row["moa_number"]."'  data-target='#new_delivery_modal' class='btn btn-success btn-round btn-sm'><i style='height:0%' class='fa fa-ticket'></i> Select DOP</a>";
        return  "<a href='#' data-coop_accre='".$row["coop_accre"]."' data-province='".$row["province"]."' data-municipality = '".$row["municipality"]."'  data-dop=".$row["dropOffPoint"]." data-prv_id='".$row["prv_dropoff_id"]."' data-toggle='modal' data-coop_name='".$row["coop"]."' data-region='".$row["region"]."' data-moa='".$row["moa_number"]."' data-balance='".$row["remaining_balance"]."'  data-target='#new_delivery_modal' class='btn btn-success btn-round btn-sm'><i style='height:0%' class='fa fa-ticket'></i> Select DOP</a>";
        })
        ->make(true);
    }

    public function genTableList(Request $request){
        $batchList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
              ->where("prv_dropoff_id", $request->prv_dropoff_id)
              ->where("is_for_replacement", 1)
              ->get();

        $data_arr = array();

        foreach($batchList as $row){
          $status = array("For Confirmation","Already Confirmed","","Cancelled");

          $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
          ->where("accreditation_no", $row->accreditation_no)
          ->first();

            array_push($data_arr, array(
                'coop'=> $coop->coopName,
                'batchTicketNumber' => $row->batchTicketNumber,
                'delivery_date' => $row->delivery_date,
                'volume' => $row->instructed_delivery_volume,
                'status' => $status[$row->status],
            ));
        }

        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
       /* ->addColumn('action', function($row) {

        return  "<a href='#' data-coop_accre='".$row["coop_accre"]."' data-province='".$row["province"]."' data-municipality = '".$row["municipality"]."'  data-dop=".$row["dropOffPoint"]." data-prv_id='".$row["prv_dropoff_id"]."' data-toggle='modal' data-coop_name='".$row["coop"]."' data-target='#new_delivery_modal' class='btn btn-success btn-round btn-sm'><i style='height:0%' class='fa fa-ticket'></i> Select DOP</a>";
        }) */
        ->make(true);
    }


    public function insertNewDOP(Request $request){

        $checkDop_if_exist = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
          ->where("region", $request->region_name)
          ->where("province", $request->province_name)
          ->where("municipality", $request->municipality_name)
          ->where("dropOffPoint", $request->dropOffPoint)
        //   ->where("coop_accreditation", $request->coop_accre)
          ->first();

          if(count($checkDop_if_exist)>0){
             return json_encode("FAILED CREATING NEW DOP, DOP ALREADY EXIST");

          }

      $return_arr = array();
      $checkDop = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
          ->where("regionName", $request->region_name)
          ->where("province", $request->province_name)
          ->where("municipality", $request->municipality_name)
          //->where("coop_accreditation", $request->coop_accre)
          ->first();

          if(count($checkDop)>0){
             
              $last_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                  ->where("prv", $checkDop->prv)
                  ->count();
              $last_prv++;   
                do{
                  $ex = 1;
                  $prv_dropoff_id = $checkDop->prv.'-'.$last_prv;
                  //CHECK IF EXIST
                  $exist = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                      ->where("prv_dropoff_id", $prv_dropoff_id)
                      ->first();
                      if(count($exist)>0){
                        $last_prv++;
                        $ex = 1;
                      }else{
                        $ex = 0;
                      }
                }while($ex == 1);
                //INSERT NEW DOP
                DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
                ->insert([
                  "prv_dropoff_id" => $prv_dropoff_id,
                  "coop_accreditation" => 'Repalcement',
                  "region" => $request->region_name,
                  "province" => $request->province_name,
                  "municipality" =>  $request->municipality_name,
                  "dropOffPoint" => $request->dropOffPoint,
                  "prv" => $checkDop->prv,
                  "is_active" => 1,
                  "date_created" => date("Y-m-d"),
                  "created_by" => Auth::user()->username,
                  "region_sort" => $checkDop->region_sort,
                  "is_for_replacement" => 1
                ]);

                //INSERT to tbl for replacement
                $check_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                ->where('prv',$checkDop->prv)
                ->where('coop_acreditaion_no',$request->coop_accre)
                ->get();

                $data_array = array(
                    "prvId" => $checkDop->prvId,
                    "prv"=> $checkDop->prv,
                    "prv_code"=> $checkDop->prv_code, 
                    "region" => $checkDop->regionName,
                    "province"=> $checkDop->province,
                    "municipality"=> $checkDop->municipality,
                    "status"=>2,
                    "replacement_reason"=>$request->util_reason,
                    "dop_id"=>$prv_dropoff_id,
                    "coop_acreditaion_no"=>$request->coop_accre,
                    );

                    array_push($return_arr, $data_array);

                if(count($check_data)>0){
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                    ->where('prv',$checkDop->prv)
                    ->where('coop_acreditaion_no',$request->coop_accre)
                    ->delete();

                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                    ->insert($return_arr);
                
                
                }else{
                    DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
                    ->insert($return_arr);
                } 

                // update farmer_profile here

                // $this->update_farmer_profile($checkDop->prv_code);

                return json_encode("SUCCEED CREATING NEW DOP");
          }else{
            return json_encode("FAILED CREATING NEW DOP");
          }


    }

    public function insert_delivery_schedule(Request $request){

        $instructed_delivery_volume = $request->instructed_delivery_volume;
        $region = $request->region;
        $prv_dropoff_id = $request->prv_dropoff_id;
        $moa_number = $request->moa_number;
        $accreditation_no = $request->accreditation_no;
        $delivery_date = date("Y-m-d ".date("H:i:s"), strtotime($request->delivery_date));
        $date_created =  date("Y-m-d H:i:s", strtotime($request->date_created));
        // $batchTicketNumber = $request->batchTicketNumber;
        $batchTicketNumber = Auth::user()->userId."-BCH-".time();

        $checkBatch = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
            ->where("batchTicketNumber", $batchTicketNumber)
            ->first();

        if(count($checkBatch)>0){
            $batchTicket_arr = explode("-",$batchTicketNumber);
            $batchTicket_arr[2] = $batchTicket_arr[2] + 1;
            $batchTicketNumber = $batchTicket_arr[0]."-".$batchTicket_arr[1]."-".$batchTicket_arr[2]; 
        }
        
        $status = 0; 
        $confirmed_delivery = 0;
        $user_id = $request->user_id; 
        $isBuffer = $request->isBuffer;

        $hasRegionCommitment =  $this->hasRegionCommitment($accreditation_no, $region);
        $coopRegTotalCommit =  $this->getCoopRegionalCommitment($accreditation_no, $region);
        $coopRegPending =  $this->getCoopRegPending($accreditation_no, $region);
        $coopRegConfirmed  =  $this->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
        $coopRegInspected =  $this->getCoopRegInspected($accreditation_no, $region);
        $coopRegAllocated =  $coopRegPending + $coopRegConfirmed + $coopRegInspected;
        if(isset($coopRegTotalCommit->total)){
            $total = $coopRegTotalCommit->total;
        }else{
            $total = 0;
        }
        $remainingBalance = $total - $coopRegAllocated; 

        if($instructed_delivery_volume <= $remainingBalance){

            $data = [
                'instructed_delivery_volume' => $instructed_delivery_volume,
                'prv_dropoff_id' => $prv_dropoff_id,
                'moa_number' => $moa_number,
                'accreditation_no' => $accreditation_no,
                'delivery_date' => $delivery_date,
                'date_created' => $date_created,
                'status' => $status,
                'batchTicketNumber' => $batchTicketNumber,
                'confirmed_delivery' => $confirmed_delivery,
                'user_id' => $user_id,
                'region' => $region,
                'is_for_replacement' => 1,
            ];
            if($this->insert_delivery_sched($data)){
                //success
                //check balance 
                $hasRegionCommitment = $this->hasRegionCommitment($accreditation_no, $region);
                $coopRegTotalCommit = $this->getCoopRegionalCommitment($accreditation_no, $region);
                $coopRegPending = $this->getCoopRegPending($accreditation_no, $region);
                $coopRegConfirmed  = $this->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
                $coopRegInspected = $this->getCoopRegInspected($accreditation_no, $region);
                $coopRegAllocated =  $coopRegPending + $coopRegConfirmed + $coopRegInspected;
                if(isset($coopRegTotalCommit->total)){
                    $total = $coopRegTotalCommit->total;
                }else{
                    $total = 0;
                }
                $remainingBalance = $total - $coopRegAllocated; 
                
                //result
                $result["remainingBalance"] = $remainingBalance;
                $result["instructed_delivery_volume"] = $instructed_delivery_volume;
                $result["status"] = 1;
                $result["message"] = "success";
            }
            else{
                //failed
                //success
                $result["remainingBalance"] = $remainingBalance;
                $result["instructed_delivery_volume"] = $instructed_delivery_volume;
                $result["status"] = 2;
                $result["message"] = "Failed to create new batch ticket";
            }         
        }
        else{
            $result["remainingBalance"] = $remainingBalance;
            $result["instructed_delivery_volume"] = $instructed_delivery_volume;
            $result["status"] = 3;
            $result["message"] = "Insufficient balance";
        }

       
        
        return $result;
    }


    private function delivery_dropoff_points_new($province, $municipality) {
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
        ->select('dropOffPoint','prv_dropoff_id','prv')
        ->where('province', $province)
        ->where('municipality', $municipality)
        ->where("isBuffer", "!=", 9)
        ->orderBy('dropOffPoint', 'asc')
        ->groupBy("prv_dropoff_id")
        ->get();
    
    $return_str= '';
        foreach($dropoff_points as $row){
            $return_str .= "<option value='$row->prv_dropoff_id'>$row->dropOffPoint</option>";
        }
        return $return_str;
    
    }
    private function delivery_dropoff_points($province, $municipality) {
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
        ->select('dropOffPoint','prv_dropoff_id','prv')
        ->where('province', $province)
        ->where('municipality', $municipality)
        ->where('batchTicketNumber', "TRANSFER")
        ->orderBy('dropOffPoint', 'asc')
        ->groupBy("prv_dropoff_id")
        ->get();
    
        return $dropoff_points;
    }
    
    private function getCoopRegPending($accreditation_no, $region){
        $pendingBags = 0;
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
        ->select('region')
        ->addSelect(DB::raw("SUM(instructed_delivery_volume) as total"))
        ->where('accreditation_no', $accreditation_no)
        ->where('region', $region)
        ->where('isBuffer','!=',9)
        ->where('status', 0)
        ->groupBy("region")
        ->get();
    
        if(count($dropoff_points) > 0){ 
            foreach($dropoff_points as $dop){
                $pendingBags = $dop->total;
            }
    }
    
        return $pendingBags;
    }
    
    private function getCoopRegConfirmed($accreditation_no, $moa_number, $region){
    
      $confirmedBags = 0;
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
          ->select('region')
          ->addSelect(DB::raw("SUM(totalBagCount) as total"))
          ->where('coopAccreditation', $accreditation_no)
          ->where('region', $region)
          ->where('is_cancelled', 0)
          ->whereNOTIn('batchTicketNumber',function($query) use ($moa_number, $region){
            $query->select('batchTicketNumber')
                ->from($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where([
                    'moa_number' => $moa_number,
                    'region' => $region, 
                    'isBuffer' => 0
                ])
                ->groupBy("batchTicketNumber");
         })
          ->groupBy("region")
          ->get();
    
          if(count($dropoff_points) > 0){ 
              foreach($dropoff_points as $dop){
                  $confirmedBags = $dop->total;
              }
      } 
    
      return $confirmedBags;
    }
    
    private function getCoopRegInspected($accreditation_no, $region ){
    $inspectedBags = 0;
    
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
        ->select('region')
        ->addSelect(DB::raw("SUM(totalBagCount) as total"))
        ->whereIn('batchTicketNumber',function($query) use ($accreditation_no, $region){
            $query->select('batchTicketNumber')
                ->from($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where([
                    'coopAccreditation' => $accreditation_no,
                    'region' => $region, 
                    'is_cancelled' => 0
                ])
                ->groupBy("batchTicketNumber");
         })
        ->groupBy("region")
        ->get();
    
        if(count($dropoff_points) > 0){ 
            foreach($dropoff_points as $dop){
                $inspectedBags = $dop->total;
            }
    } 
    
    
    return $inspectedBags;
    }
    
    private function hasRegionCommitment($accreditation_no, $region){
    $hasData = 0;
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_regional")
        ->select('coop_name', 'region_name')
        ->addSelect(DB::raw("SUM(volume) as total"))
        ->where('accreditation_no', $accreditation_no)
        ->where('region_name', $region)
        ->groupBy("region_name")
        ->get();
    
        if(count($dropoff_points) > 0){ 
            $hasData = 1;
    }
    
        return $hasData;
    
    } 
    
    private function getCoopRegionalCommitment($accreditation_no, $region ){
        $dropoff_points = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_regional")
            ->select('coop_name', 'region_name')
            ->addSelect(DB::raw("SUM(volume) as total"))
            ->where('accreditation_no', $accreditation_no)
            ->where('region_name', $region)
            ->groupBy("region_name")
            ->first();
        
    return $dropoff_points;
    }
    
    private function insert_delivery_sched($data) {
    
        DB::beginTransaction();
        try {
            
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    //update farmer profile processed farmer raplcement area
    private function update_farmer_profile($prv_code){

        $get_prv = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_for_replacement')
        ->where('prv_code',$prv_code)
        ->first();

        $database = $prv_code;

        $rsbsa_arr = array();
        $released_data = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.released')        
            ->where('province', 'like', '%'.$get_prv->province)
            ->where('municipality', 'like', '%'.$get_prv->municipality)
            ->get();

            foreach ($released_data as $val) {

                array_push($rsbsa_arr, $val->rsbsa_control_no);

                $farmer_profile_data2 = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile_processed')
                    ->where('rsbsa_control_no', $val->rsbsa_control_no)
                    ->where('farmerID', $val->farmer_id )
                    ->update([
                        'is_replacement' => 1,
                        'replacement_area' =>$val->claimed_area,
                        'replacement_bags' =>$val->bags,
                        'replacement_bags_claimed' =>0
                    ]);      
            }    
        // $farmer_frofile_data = DB::table($GLOBALS['season_prefix'].'prv_'.$database.'.farmer_profile_processed')        
        //     ->whereIn('rsbsa_control_no', $rsbsa_arr)
        //     ->update([
        //         'is_replacement' => 1,
        //         'replacement_bags_claimed' =>0
        //     ]);     
   
    }
    //update farmer profile processed



}

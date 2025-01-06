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

use Session;
use Auth;
use Illuminate\Support\Facades\Hash;


class DopRegularMakerController extends Controller
{

  public function index(){
     // dd(Auth::user()->province);
    $region = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            // ->where("prv", "LIKE", Auth::user()->province."%")
            ->groupBy("region")
            ->orderby("region_sort", "ASC")
            ->get();

    $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
            ->where('isActive', 1)
            ->orderby('coopName','ASC')
            ->get();

    return view("drop_maker.index")
            ->with("regional_list", $region)
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

  public function get_coop(Request $request){

    $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_regional")
        ->where('region_name','LIKE',$request->region)
        ->orWhere('region_name', 'LIKE','ANY Region')
        ->groupBy('accreditation_no')
        ->get();

        $return_str= '';
        foreach($coop as $row){
            $return_str .= "<option value='$row->accreditation_no'>$row->coop_name</option>";
        }
        return $return_str;
    
    
  }

  public function genTable2(Request $request){
     
    $dopList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->where("prv", $request->region.$request->province.$request->municipality)
          //   ->where("coop_accreditation", $request->coop_accre)
            ->get();
          
    
      $data_arr = array();
     
      foreach($dopList as $row){

          array_push($data_arr, array(
            //   'coop' => $request->coop_name,
              'province' => $row->province,
              'municipality' => $row->municipality,
              'dropOffPoint' => $row->dropOffPoint,
              "prv_dropoff_id" => $row->prv_dropoff_id,
              "coop_accre" => '0',
              "region" => $row->region,
              "moa_number"=>'0',
              "remaining_balance" => '0',

          ));
          
      }


      $data_arr = collect($data_arr);

      return Datatables::of($data_arr)
      ->addColumn('action', function($row) {

      return  "<a href='#'  data-province='".$row["province"]."' data-municipality = '".$row["municipality"]."'  data-dop=".$row["dropOffPoint"]." data-prv_id='".$row["prv_dropoff_id"]."' data-toggle='modal'  data-region='".$row["region"]."' data-target='#new_delivery_modal' class='btn btn-success btn-round btn-sm'><i style='height:0%' class='fa fa-ticket'></i> Select DOP</a>";
      })
      ->make(true); 
  }

    public function genTable(Request $request){
     
      $dopList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
              ->where("prv", $request->region.$request->province.$request->municipality)
              ->where("is_for_replacement","!=", "1")
            //   ->where("coop_accreditation", $request->coop_accre)
              ->get();
            
      
          $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("accreditation_no", $request->coop_accre)
                ->first();

          $moa_number = $coop->current_moa;

        // $coop_commitment_regional = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_regional")
        //       ->select(DB::RAW('SUM(volume) as regional_commitment'))
        //       ->where("accreditation_no", $request->coop_accre)
        //       ->where("region_name",'LIKE', '%'.$request->region.'%')
        //       ->get();
      
        //   $coop_tbl_actual_delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
        //     ->select(DB::RAW('SUM(totalBagCount)'))
        //     ->where("moa_number", $moa_number)
        //     ->where("isBuffer", '0')
        //     ->where("isRejected", '0')
        //     ->get();

        //   $coop_tbl_delivery = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
        //     ->select(DB::RAW('SUM(totalBagCount)'))
        //     ->where("moa_number", $moa_number)
        //     ->where("is_cancelled", '0')
        //     ->where("isBuffer", '0')  
        //     ->get();

        //  $coop_delivery_transaction = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
        //     ->select(DB::RAW('SUM(instructed_delivery_volume)'))
        //     ->where("moa_number", $moa_number)
        //     ->where("status", '1')
        //     ->where("isBuffer", '0')  
        //     ->get();


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
                'coop' => $request->coop_name,
                'province' => $row->province,
                'municipality' => $row->municipality,
                'dropOffPoint' => $row->dropOffPoint,
                "prv_dropoff_id" => $row->prv_dropoff_id,
                "coop_accre" => $request->coop_accre,
                "region" => $row->region,
                "moa_number"=>$moa_number,
                "remaining_balance" => $remainingBalance,

            ));
            
        }

 
        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->addColumn('action', function($row) {

        return  "<a href='#' data-coop_accre='".$row["coop_accre"]."' data-province='".$row["province"]."' data-municipality = '".$row["municipality"]."'  data-dop=".$row["dropOffPoint"]." data-prv_id='".$row["prv_dropoff_id"]."' data-toggle='modal' data-coop_name='".$row["coop"]."' data-region='".$row["region"]."' data-moa='".$row["moa_number"]."' data-balance='".$row["remaining_balance"]."'  data-target='#new_delivery_modal' class='btn btn-success btn-round btn-sm'><i style='height:0%' class='fa fa-ticket'></i> Select DOP</a>";
        })
        ->make(true); 
    }

    

    public function genTableList(Request $request){
 
        $batchList = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_transaction")
              ->where("prv_dropoff_id", $request->prv_dropoff_id)
              ->where("isBuffer", 0)
              ->get();
  
        $data_arr = array();

        foreach($batchList as $row){
          $status = array("For Confirmation","Already Confirmed","","Cancelled");

          $coop = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
          ->where("accreditation_no", $row->accreditation_no)
          ->first();

            // $coop = 'coop name';

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
    //    /* ->addColumn('action', function($row) {

    //     return  "<a href='#' data-coop_accre='".$row["coop_accre"]."' data-province='".$row["province"]."' data-municipality = '".$row["municipality"]."'  data-dop=".$row["dropOffPoint"]." data-prv_id='".$row["prv_dropoff_id"]."' data-toggle='modal' data-coop_name='".$row["coop"]."' data-target='#new_delivery_modal' class='btn btn-success btn-round btn-sm'><i style='height:0%' class='fa fa-ticket'></i> Select DOP</a>";
    //     }) */
        ->make(true);
    }


    public function insertNewDOP(Request $request){

        $checkDop_name = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
            ->where("region", $request->region_name)
            ->where("province", $request->province_name)
            ->where("municipality", $request->municipality_name)
            ->where("dropOffPoint", $request->dropOffPoint)
            ->first();
          if(count($checkDop_name)>0){
            return json_encode("FAILED CREATING NEW DOP, DOP NAME ALREADY EXIST");
          }else{
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
                  "coop_accreditation" =>'Web',
                  "region" => $request->region_name,
                  "province" => $request->province_name,
                  "municipality" =>  $request->municipality_name,
                  "dropOffPoint" => $request->dropOffPoint,
                  "prv" => $checkDop->prv,
                  "is_active" => 1,
                  "date_created" => date("Y-m-d"),
                  "created_by" => Auth::user()->username,
                  "region_sort" => $checkDop->region_sort,
                  "is_for_replacement" => 0
                ]);

                return json_encode("SUCCEED CREATING NEW DOP");
          }else{
            return json_encode("FAILED CREATING NEW DOP");
          }

          }   

    }
// ..?
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
        
        // $coopRegTotalCommit =  $this->getCoopRegionalCommitment($accreditation_no, $region);
        // $coopRegPending =  $this->getCoopRegPending($accreditation_no, $region);
        // $coopRegConfirmed  =  $this->getCoopRegConfirmed($accreditation_no, $moa_number, $region);
        // $coopRegInspected =  $this->getCoopRegInspected($accreditation_no, $region);

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
                'isBuffer' => $isBuffer,
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


  // from dropoffpoints

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


}

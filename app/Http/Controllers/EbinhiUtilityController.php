<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;

use Yajra\Datatables\Facades\Datatables;

class EbinhiUtilityController extends Controller
{
 	public function home(){
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->groupBy('province')->orderBy('province')->get();

        return view('ebinhi_util.home')
            ->with('provinces', $provinces);
            
    }
    

    public function coops(Request $request){

   $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
        ->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_claim.coopAccreditation","=", "tbl_cooperatives.accreditation_no")
        ->select("tbl_claim.coopAccreditation", "tbl_cooperatives.coopName", "tbl_cooperatives.updated_accreditation_no", "tbl_cooperatives.current_moa")        
        ->where('tbl_claim.province', '=', $request->province)
        ->where('tbl_claim.municipality', '=', $request->municipality)
        ->where('tbl_claim.claimLocation', '=', $request->dop_name)
        ->groupBy('tbl_claim.coopAccreditation')
        ->orderBy('tbl_claim.coopAccreditation')
        ->get();

        $return_str= '';
        foreach($data as $row){
            $return_str .= "<option value='$row->current_moa'>$row->coopName</option>";
        }
        return $return_str;
    }

    public function get_dop(Request $request){
        $dops = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->groupBy('claimLocation')
            ->orderBy('claimLocation')
            ->get();

        $return_str= '';
        foreach($dops as $row){
            $return_str .= "<option value='$row->claimLocation'>$row->claimLocation</option>";
        }
        return $return_str;
    }

    public function load_seedtags(Request $request){
        $seedtags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->where('moa_number', '=', $request->coop)
            ->where('is_transferred', '=', 0)
            ->whereRaw("totalBagCount != '0'")    
            ->whereRaw("qrStart != '0' AND qrEnd != '0'")      
            ->orderBy('seedTag')
            ->get();

        $return_str= '';
        foreach($seedtags as $row){
            $totalBagCount = $row->totalBagCount;

            $tbl_claim = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as a')
                ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim as b", "b.coopAccreditation","=", "a.accreditation_no")
                ->select("b.*")
                ->where('a.current_moa', '=', $request->coop)
                ->where('b.seedTag', '=', $row->seedTag)
                ->count();

            $bal=$totalBagCount-$tbl_claim;

            if($bal>0){
                $return_str .= "<option value='$row->seedTag'>$row->seedTag > $row->seedVariety ($bal)</option>";
            }
        }
        return $return_str;
    }

    public function seed_variety(Request $request){
        $seed_varietys = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->where('seedTag', '=', $request->seed_tag)
            ->where('moa_number', '=', $request->coop)
            ->whereRaw("qrStart != '0' AND qrEnd != '0'")
            ->whereRaw("qrValStart != '' AND qrValEnd != ''")    
            ->where('is_transferred', '=', 0)
            ->groupBy('seedVariety')
            ->orderBy('seedVariety')
            ->get();

        $return_str= '';
        foreach($seed_varietys as $row){
            $return_str .= "<option value='$row->seedVariety'>$row->seedVariety</option>";
        }
        return $return_str;
    }

    public function qr_code(Request $request){

      $qr_codes = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
            ->where('province', '=', $request->province)
            ->where('municipality', '=', $request->municipality)
            ->where('seedTag', '=', $request->seed_tag)
            ->where('seedVariety', '=', $request->seed_variety)
            ->where('moa_number', '=', $request->coop)
            ->where('is_transferred', '=', 0)
            ->whereRaw("qrStart != '0' AND qrEnd != '0'")
            ->whereRaw("qrValStart != '' AND qrValEnd != ''")   
            ->first();

            // return $qr_codes->totalBagCount;

    //    return json_encode($qr_codes);

            $qr_codes_full = explode("-",  $qr_codes->qrValStart);

            $return_array = array();
            $start1 = $qr_codes->qrStart;
            $qr_ends = $qr_codes->qrEnd;
            $val2 = '';
            $return_str= '';
            while ($start1 <= $qr_ends){

                $bb = strlen($start1);
                $extender = "";
                for($bb; $bb<=5; $bb++){
                    $extender .= "0";
                }
                $start1 = $extender.$start1;

                $val2 = $qr_codes_full[0].'-'.$qr_codes_full[1].'-'.$start1;
    
                $check_qr = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
                     ->select('qr_code')
                     ->where('qr_code', '=', $val2)
                     ->count();

                if($check_qr <= 0){ 
                  

                    // array_push($return_array, $val2); 
                    
                    $return_str = "<option value='$val2'>$val2</option>";
                    array_push($return_array, $return_str); 
                         
                }
                $start1++;  
                    
          }

        return $return_array;
    }

    // aaaaaa
    public function add_tbl_claim(Request $request){
        $username = Auth::user()->username;

        $count = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
            ->where('paymaya_code', $request->paymayacode)
            ->first();

        $data = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->where('paymaya_code', $request->paymayacode)
            ->count();

        $temp = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->where('paymaya_code', $request->paymayacode)
            ->first();

        $check_qr_exist = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->where('qr_code', $request->qr)
            ->count();

        if($check_qr_exist >=1){
            return 2;
        } 


        if($count->bags > $data){

            DB::beginTransaction();
        try {

            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->insert([
                'beneficiary_id' => $temp->beneficiary_id,
                'paymaya_code' => $request->paymayacode,
                'rsbsa_control_no' => $temp->rsbsa_control_no,
                'qr_code' =>  $request->qr,
                'released_by' => $username,
                'date_created' =>date('Y-m-d H:i:s'),
                'app_version' => 'web',
                'seedVariety' => $request->seed_variety,
                'seedTag' => $request->seed_tag,
                'coopAccreditation' => $temp->coopAccreditation,
                'fullName' => $temp->fullName,
                'sex' =>$temp->sex,
                'region' =>$temp->region,
                'province' =>$temp->province,
                'municipality' =>$temp->municipality,
                'barangay' =>$temp->barangay,
                'claimLocation' =>$temp->claimLocation,
                'phoneNumber' =>$temp->phoneNumber,
                'distributed_kps' =>$temp->distributed_kps,
                'other_benefits_received' =>$temp->other_benefits_received,
                // 'is_paid' =>,
                // 'date_paid' =>,
                // 'sync_date1' =>
    
            ]);

            DB::commit();
            return 1;
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
         
        } else{
            return 0;
        }

        
      }

    public function get_municipalities(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')
            ->where('province', $request->province)
            ->groupBy('municipality')
            ->orderBy('municipality')
            ->get();
            
        $return_str= '';
        foreach($municipalities as $row){
            $return_str .= "<option value='$row->municipality'>$row->municipality</option>";
        }
        return $return_str;
    }

    public function select2_data(Request $request){

        $coop_acre = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
        ->where('current_moa', '=', $request->coop)
        ->first();

        $select2_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
         ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code","=", "tbl_beneficiaries.paymaya_code")
         ->select("tbl_claim.*", "tbl_beneficiaries.bags", "tbl_beneficiaries.area")
         ->where('tbl_beneficiaries.province', $request->province)
         ->where('tbl_beneficiaries.municipality', '=', $request->municipality)
         ->where('tbl_claim.claimLocation', '=', $request->dop_name)
         ->where('tbl_claim.coopAccreditation', '=', $coop_acre->accreditation_no)
         ->groupby('tbl_claim.paymaya_code')
         ->get();

            
        $return_str= '';
        $return_card='';
        $limit=0;
        foreach($select2_data as $row){

            // $count_bags_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            // ->where('tbl_claim.paymaya_code', $row->paymaya_code)
            // ->count();
         
            // if($row->bags > $count_bags_claim){
                $return_str .= "<option value='$row->paymaya_code'>$row->rsbsa_control_no > $row->fullName > $row->paymaya_code</option>";
            
            // }
            
        }

        foreach($select2_data as $row){

            $count_bags_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            ->where('tbl_claim.paymaya_code', $row->paymaya_code)
            ->count();
               
                $return_card .= '<div class="accordion">
                                <div class="card" >
                                    <div class="card-header" id="headingOne">
                                        <h5 class="mb-0" style="margin:0" >
                                            <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                <label class="pull-left">Name: '.$row->fullName.' > RSBSA:'.$row->rsbsa_control_no.'</label>
                                                    <br>
                                                <p class="pull-left">bags claimed:'.$row->bags.' > Allotted bag/s: '.$row->bags.' > Actual Area:'.$row->area.' ha </p>
                                                
                                            
                                            </button>   
                                            
                                        
                                        </h5>
                                        <button class="btn btn-success btn-sm" style="top:20%;margin-right:10px;position:absolute;right:0%;"><i class="fa fa-database  "></i> view</button>
                                    </div>  
                                </div>
                            </div>';
        if($limit==2){
           break; 
        }
        $limit++; 
         
        }
        
        return compact('return_str','return_card');
    }


    public function search_farmer(Request $request){

    $select2_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
         ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code","=", "tbl_beneficiaries.paymaya_code")
         ->select("tbl_claim.*", "tbl_beneficiaries.bags", "tbl_beneficiaries.area")
         ->where('tbl_claim.paymaya_code', $request->farmer_data)
         ->groupby('tbl_claim.paymaya_code')
         ->get();

    $per_qr = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
         ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code","=", "tbl_beneficiaries.paymaya_code")
         ->select("tbl_claim.*", "tbl_beneficiaries.bags", "tbl_beneficiaries.area")
         ->where('tbl_claim.paymaya_code', $request->farmer_data)
         ->get();
            
        $return_card_detailed= '';
        $return_card='';
        $count_bags_claim =''; 
  
        foreach($select2_data as $row){

            $count_bags_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            ->where('tbl_claim.paymaya_code',  $request->farmer_data)
            ->count();

            $return_card .= '<div class="accordion">
                                <div class="card" >
                                    <div class="card-header" id="headingOne">
                                        <h5 class="mb-0" style="margin:0" >
                                            <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                <label class="pull-left">Name: '.$row->fullName.' > RSBSA:'.$row->rsbsa_control_no.'</label>
                                                    <br>
                                                    <p class="pull-left">bags claimed: '.$count_bags_claim.' > Allotted bag/s: '.$row->bags.' > Actual Area:'.$row->area.' ha </p>
                                                
                                            
                                            </button>   
                                            
                                        
                                        </h5>
                                        
                                    </div>  
                                </div>
                            </div>';
        }

        // <button class="btn btn-success btn-sm" style="top:20%;margin-right:10px;position:absolute;right:0%;"><i class="fa fa-database  "></i> view</button>

        foreach($per_qr as $row){

            // $count_bags_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
            // ->where('tbl_claim.paymaya_code',  $request->farmer_data)
            // ->count();

            $return_card_detailed .= '<div class="accordion">
                                <div class="card" >
                                    <div class="card-header" id="headingOne">
                                        <h5 class="mb-0" style="margin:0" >
                                            <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                <label class="pull-left">Name: '.$row->fullName.' > RSBSA:'.$row->rsbsa_control_no.'</label>
                                                    <br>
                                                    <p class="pull-left">QR code:'.$row->qr_code.' > Seed Tag: '.$row->seedTag.' > Seed Variety: '.$row->seedVariety.' > date claimed: '.$row->date_created.'</p>
                                            </button>   
                                        </h5>
                                        <button class="btn btn-danger btn-sm btn_delete" style="top:20%;margin-right:10px;position:absolute;right:0%;"><i class="fa fa-times "></i></button>
                                    </div>  
                                </div>
                            </div>';
        
        }
        return compact('return_card_detailed','return_card');
    }

    public function reload(Request $request){

        $select2_data = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
             ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code","=", "tbl_beneficiaries.paymaya_code")
             ->select("tbl_claim.*", "tbl_beneficiaries.bags", "tbl_beneficiaries.area")
             ->where('tbl_claim.paymaya_code', $request->paymayacode)
             ->groupby('tbl_claim.paymaya_code')
             ->get();
    
        $per_qr = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries")
             ->join($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim", "tbl_claim.paymaya_code","=", "tbl_beneficiaries.paymaya_code")
             ->select("tbl_claim.*", "tbl_beneficiaries.bags", "tbl_beneficiaries.area")
             ->where('tbl_claim.paymaya_code', $request->paymayacode)
             ->get();
                
            $return_card_detailed= '';
            $return_card='';
            $count_bags_claim =''; 
      
            foreach($select2_data as $row){
    
                $count_bags_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                ->where('tbl_claim.paymaya_code',  $request->paymayacode)
                ->count();
    
                $return_card .= '<div class="accordion">
                                    <div class="card" >
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0" style="margin:0" >
                                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                    <label class="pull-left">Name: '.$row->fullName.' > RSBSA:'.$row->rsbsa_control_no.'</label>
                                                        <br>
                                                        <p class="pull-left">bags claimed: '.$count_bags_claim.' > Allotted bag/s: '.$row->bags.' > Actual Area:'.$row->area.' ha </p>
                                                    
                                                
                                                </button>   
                                                
                                            
                                            </h5>
                                            
                                        </div>  
                                    </div>
                                </div>';
            }
    
            // <button class="btn btn-success btn-sm" style="top:20%;margin-right:10px;position:absolute;right:0%;"><i class="fa fa-database  "></i> view</button>
    
            foreach($per_qr as $row){
    
                // $count_bags_claim = DB::table($GLOBALS['season_prefix']."rcep_paymaya.tbl_claim")
                // ->where('tbl_claim.paymaya_code',  $request->farmer_data)
                // ->count();
    
                $return_card_detailed .= '<div class="accordion">
                                    <div class="card" >
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0" style="margin:0" >
                                                <button style="color: #7387a8;text-decoration:none;" class="btn btn-link" style= "text-align: left">
                                                    <label class="pull-left">Name: '.$row->fullName.' > RSBSA:'.$row->rsbsa_control_no.'</label>
                                                        <br>
                                                        <p class="pull-left">QR code:'.$row->qr_code.' > Seed Tag: '.$row->seedTag.' > Seed Variety: '.$row->seedVariety.' > date claimed: '.$row->date_created.'</p>
                                                </button>   
                                            </h5>
                                            <button class="btn btn-danger btn-sm btn_delete" style="top:20%;margin-right:10px;position:absolute;right:0%;"><i class="fa fa-times "></i></button>
                                        </div>  
                                    </div>
                                </div>';
            
            }
            return compact('return_card_detailed','return_card');
        }
}




// B6L903F
// B6LY93G
// B6LWRTW
// B6LGNH5
// B6LY8SP
// B6L3PHF
// B6LXEBH
// BFYUVO1
// BFYEHW9
// BD7IQGR
// BD73OO3
// BD7WVIZ
// B327FC1
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB; 
class rcefXbddController extends Controller
{
    public function api_bdd_sg($api){
        
        $return_arr = array();

        $API_KEY = "NTNkMDRhODJkOTc";
      
		if($api == $API_KEY){
            $main_data = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_sg_commitment")
                ->orderBy("coop_name")
                ->get();

                foreach($main_data as $data){
                    if($data->seed_source_philrice == "1" && $data->seed_source_da == "1"){
                        $seed_source = "PhilRice and DA";
                    }elseif($data->seed_source_philrice == "1"){
                        $seed_source = "PhilRice";
                    }elseif($data->seed_source_da == "1"){
                        $seed_source = "DA";
                    }else{
                        $seed_source = "None";
                    }

                    if($data->bpi_nsq_application == "1"){
                        $bpi = "Yes";
                    }else{
                        $bpi = "No";
                    }

                    $variety_list = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_sg_commitment_variety")
                        ->select("variety","area_philrice","area_da","transplating_date")
                        ->where("sg_id", $data->id)
                        ->get();

                    $area_sum = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_sg_commitment_variety")
                        ->select(DB::raw("SUM(area_philrice) as pr"),DB::raw("SUM(area_da) as da"))
                        ->where("sg_id", $data->id)
                        ->first();
                        
                    if($area_sum != null){
                        $commited = $area_sum->pr + $area_sum->da; 
                    }
         


                    $arr = array(
                        "coop_name" => $data->coop_name,
                        "coop_accreditation_no" => $data->coop_accre,
                        "member_name" => $data->member_name,
                        "member_accre" => $data->sg_accreditation_no,
                        "accreditation_expiry_date" => $data->acc_expiry,
                        "accredited_area" =>$data->accredited_area,
                        "rcef_committed_area" => $commited ,
                        "seed_source" => $seed_source,
                        "BPI_NSQCS_Application" => $bpi,
                        "Remarks" => $data->remarks,
                        "variety" => $variety_list
                    );

                    array_push($return_arr,$arr);

                }
                return json_encode($return_arr);
        }else{
            return json_encode("No privilege");
        }




    }


    public function api_bdd_coops($season,$api){
   
		$API_KEY = "NTNkMDRhODJkOTc";
      
		if($api == $API_KEY){
            if($season == "ds2023"){
                $rs_data = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_coops_rs")
                    ->select('region','seed_Growers_Coop','address','accreditation_no',DB::raw("SUM(NSIC_Rc222) as NSIC_Rc222"),DB::raw("SUM(NSIC_Rc216) as NSIC_Rc216"),DB::raw("SUM(NSIC_Rc160) as NSIC_Rc160"),DB::raw("SUM(NSIC_Rc27) as NSIC_Rc27"),DB::raw("SUM(NSIC_Rc218) as NSIC_Rc218"),DB::raw("SUM(NSIC_Rc354) as NSIC_Rc354"),DB::raw("SUM(NSIC_Rc358) as NSIC_Rc358"),DB::raw("SUM(NSIC_Rc400) as NSIC_Rc400"),DB::raw("SUM(NSIC_Rc402) as NSIC_Rc402"),DB::raw("SUM(NSIC_Rc436) as NSIC_Rc436"),DB::raw("SUM(NSIC_Rc438) as NSIC_Rc438"),DB::raw("SUM(NSIC_Rc440) as NSIC_Rc440"),DB::raw("SUM(NSIC_Rc442) as NSIC_Rc442"),DB::raw("SUM(NSIC_Rc480) as NSIC_Rc480"),DB::raw("SUM(NSIC_Rc506) as NSIC_Rc506"),DB::raw("SUM(NSIC_Rc508) as NSIC_Rc508"),DB::raw("SUM(NSIC_Rc510) as NSIC_Rc510"),DB::raw("SUM(NSIC_Rc512) as NSIC_Rc512"),DB::raw("SUM(NSIC_Rc514) as NSIC_Rc514"),DB::raw("SUM(NSIC_Rc534) as NSIC_Rc534"),DB::raw("SUM(PSB_Rc10) as PSB_Rc10"),DB::raw("SUM(PSB_Rc18) as PSB_Rc18"),DB::raw("SUM(PSB_Rc82) as PSB_Rc82"))
                    ->where("season", $season)
                    //->where("seed_Growers_Coop","Apayao Seed Producers Multi-Purpose Cooperative")
                    ->groupBy("seed_Growers_Coop")
                    ->get();

                    $return_array = array();
                    foreach($rs_data as $data){
                        //check coop name
                        // $coop_accreditation = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                        // 	->where('accreditation_no', $data->accreditation_no)
                        // 	->first();
                        // if(count($coop_accreditation) > 0){
                        // 	$coop_accreditation = $coop_accreditation->accreditation_no;
                        // }
                            
                        $coop_array = array();
                        $coop_array['region'] = $data->region;
                        $coop_array['coop'] = $data->seed_Growers_Coop;
                        $coop_array['address'] = $data->address;
                        $coop_array['accreditation_number'] = $data->accreditation_no;
        
                        $coop_array['variety']['NSIC Rc222'] = ($data->NSIC_Rc222/(200*1))*2;
                        $coop_array['variety']['NSIC Rc216'] = ($data->NSIC_Rc216/(200*1))*2;
                        $coop_array['variety']['NSIC Rc160'] = ($data->NSIC_Rc160/(200*1))*2;
                        $coop_array['variety']['NSIC Rc27'] = ($data->NSIC_Rc27/(200*1))*2;
                        $coop_array['variety']['NSIC Rc218'] = ($data->NSIC_Rc218/(200*1))*2;
                        $coop_array['variety']['NSIC Rc354'] = ($data->NSIC_Rc354/(200*1))*2;
                        $coop_array['variety']['NSIC Rc358'] = ($data->NSIC_Rc358/(200*1))*2;
                        $coop_array['variety']['NSIC Rc400'] = ($data->NSIC_Rc400/(200*1))*2;
                        $coop_array['variety']['NSIC Rc402'] = ($data->NSIC_Rc402/(200*1))*2;
                        $coop_array['variety']['NSIC Rc436'] = ($data->NSIC_Rc436/(200*1))*2;
                        $coop_array['variety']['NSIC Rc438'] = ($data->NSIC_Rc438/(200*1))*2;
                        $coop_array['variety']['NSIC Rc440'] = ($data->NSIC_Rc440/(200*1))*2;
                        $coop_array['variety']['NSIC Rc442'] = ($data->NSIC_Rc442/(200*1))*2;
                        $coop_array['variety']['NSIC Rc480'] = ($data->NSIC_Rc480/(200*1))*2;
                        $coop_array['variety']['NSIC Rc506'] = ($data->NSIC_Rc506/(200*1))*2;
                        $coop_array['variety']['NSIC Rc508'] = ($data->NSIC_Rc508/(200*1))*2;
                        $coop_array['variety']['NSIC Rc510'] = ($data->NSIC_Rc510/(200*1))*2;
                        $coop_array['variety']['NSIC Rc512'] = ($data->NSIC_Rc512/(200*1))*2;
                        $coop_array['variety']['NSIC Rc514'] = ($data->NSIC_Rc514/(200*1))*2;
                        $coop_array['variety']['NSIC Rc534'] = ($data->NSIC_Rc534/(200*1))*2;
                        $coop_array['variety']['PSB Rc10'] = ($data->PSB_Rc10/(200*1))*2;
                        $coop_array['variety']['PSB Rc18'] = ($data->PSB_Rc18/(200*1))*2;
                        $coop_array['variety']['PSB Rc82'] = ($data->PSB_Rc82/(200*1))*2;
        
        
                            
        // (CS Commitment/(200*1))*2
        
                        array_push($return_array,$coop_array);
                    }








            }elseif($season == "ds2024"){
                $rs_data = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_coops_rs_ds2024")
                    ->select('region','seed_Growers_Coop','address','accreditation_no',DB::raw('SUM(NSIC_Rc222) as NSIC_Rc222'), DB::raw('SUM(NSIC_Rc216) as NSIC_Rc216'), DB::raw('SUM(NSIC_Rc160) as NSIC_Rc160'), DB::raw('SUM(NSIC_Rc120) as NSIC_Rc120'), DB::raw('SUM(NSIC_Rc27) as NSIC_Rc27'), DB::raw('SUM(NSIC_Rc218) as NSIC_Rc218'), DB::raw('SUM(NSIC_Rc354) as NSIC_Rc354'), DB::raw('SUM(NSIC_Rc358) as NSIC_Rc358'), DB::raw('SUM(NSIC_Rc400) as NSIC_Rc400'), DB::raw('SUM(NSIC_Rc402) as NSIC_Rc402'), DB::raw('SUM(NSIC_Rc436) as NSIC_Rc436'), DB::raw('SUM(NSIC_Rc438) as NSIC_Rc438'), DB::raw('SUM(NSIC_Rc440) as NSIC_Rc440'), DB::raw('SUM(NSIC_Rc442) as NSIC_Rc442'), DB::raw('SUM(NSIC_Rc480) as NSIC_Rc480'), DB::raw('SUM(NSIC_Rc506) as NSIC_Rc506'), DB::raw('SUM(NSIC_Rc508) as NSIC_Rc508'), DB::raw('SUM(NSIC_Rc510) as NSIC_Rc510'), DB::raw('SUM(NSIC_Rc512) as NSIC_Rc512'), DB::raw('SUM(NSIC_Rc514) as NSIC_Rc514'), DB::raw('SUM(NSIC_Rc534) as NSIC_Rc534'), DB::raw('SUM(PSB_Rc10) as PSB_Rc10'), DB::raw('SUM(PSB_Rc18) as PSB_Rc18'), DB::raw('SUM(PSB_Rc82) as PSB_Rc82')
                    )
                    ->where("season", $season)
                    //->where("seed_Growers_Coop","Apayao Seed Producers Multi-Purpose Cooperative")
                    ->groupBy("seed_Growers_Coop")
                    ->get();

                    

                    $return_array = array();
                    foreach($rs_data as $data){
                        //check coop name
                        // $coop_accreditation = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                        // 	->where('accreditation_no', $data->accreditation_no)
                        // 	->first();
                        // if(count($coop_accreditation) > 0){
                        // 	$coop_accreditation = $coop_accreditation->accreditation_no;
                        // }
                            
                        $coop_array = array();
                        $coop_array['region'] = $data->region;
                        $coop_array['coop'] = $data->seed_Growers_Coop;
                        $coop_array['address'] = $data->address;
                        $coop_array['accreditation_number'] = $data->accreditation_no;
        
                        $coop_array['variety']['NSIC Rc222'] = ($data->NSIC_Rc222/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc216'] = ($data->NSIC_Rc216/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc160'] = ($data->NSIC_Rc160/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc120'] = ($data->NSIC_Rc120/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc27'] = ($data->NSIC_Rc27/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc218'] = ($data->NSIC_Rc218/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc354'] = ($data->NSIC_Rc354/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc358'] = ($data->NSIC_Rc358/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc400'] = ($data->NSIC_Rc400/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc402'] = ($data->NSIC_Rc402/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc436'] = ($data->NSIC_Rc436/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc438'] = ($data->NSIC_Rc438/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc440'] = ($data->NSIC_Rc440/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc442'] = ($data->NSIC_Rc442/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc480'] = ($data->NSIC_Rc480/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc506'] = ($data->NSIC_Rc506/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc508'] = ($data->NSIC_Rc508/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc510'] = ($data->NSIC_Rc510/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc512'] = ($data->NSIC_Rc512/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc514'] = ($data->NSIC_Rc514/(200*1))*2; 
                        $coop_array['variety']['NSIC Rc534'] = ($data->NSIC_Rc534/(200*1))*2; 
                        $coop_array['variety']['PSB Rc10'] = ($data->PSB_Rc10/(200*1))*2; 
                        $coop_array['variety']['PSB Rc18'] = ($data->PSB_Rc18/(200*1))*2; 
                        $coop_array['variety']['PSB Rc82'] = ($data->PSB_Rc82/(200*1))*2;
        
        
                            
        // (CS Commitment/(200*1))*2
        
                        array_push($return_array,$coop_array);
                    }

            }

			
 





           // dd($rs_data);
			
			
			return json_encode($return_array);
		}else{
			return "You do not have the access privilege for this API";
		}
	
    }
}

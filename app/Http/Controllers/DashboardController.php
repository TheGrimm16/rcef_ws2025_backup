<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;
use Excel;
use DB;
use App\SeedCooperatives;
use App\SeedProducers;
use App\Transplant;
use App\Seeds;
use App\SeedGrowers;
use App\RegistryFarmerRole;
use Illuminate\Support\Str;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Auth;

class DashboardController extends Controller {
	
	function get_current_week(){
        if(date('D')!='Mon'){    
            $staticstart = date('Y-m-d',strtotime('last Monday'));
            $filter_start = date('m-d-Y',strtotime('last Monday'));
        }else{
            $staticstart = date('Y-m-d');
            $filter_start = date('m-d-Y');   
        }

        if(date('D')!='Sat'){
            $staticfinish = date('Y-m-d',strtotime('next Saturday'));
            $filter_end = date('m-d-Y',strtotime('next Saturday'));
        }else{
            $staticfinish = date('Y-m-d');
            $filter_end = date('m-d-Y'); 
        }

        return array(
            'start' => $staticstart,
            'end' => $staticfinish,
            'filter_start' => $filter_start,
            'filter_end' => $filter_end
        );
    }

    public function pageClosed(){
        $mss = "TEMPORARY CLOSED";

        return view('utility.pageClosed')
            ->with("mss", $mss);
    }

    public function index() {
        // dd($GLOBALS['season_prefix']);

        // $GLOBALS['season_prefix'] = 'ds2025_';
		// if(Auth::user()->roles->first()->name == "da-icts"){
        //     $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery")
        //         ->where('region', '!=', '')
        //         ->groupBy('region')
        //         ->orderBy('region')
        //         ->get();

        //     $confirmed = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery")
        //             ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
        //             ->where('batchTicketNumber', 'NOT LIKE', '%void%')
        //             ->where('dropOffPoint', 'NOT LIKE', '%void%')
        //             ->where('is_cancelled', 0)
        //             ->first();

        //     // Get total actual deliveries of dropoff point
        //     $actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_actual_delivery")
        //             ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
        //             ->where('batchTicketNumber',"!=","TRANSFER")
        //             ->first();

            
        //      $distributed = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_national_reports')
        //             ->select('*')
        //             ->first();

        //     $current_week = $this->get_current_week();

        //     /*$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,region  
        //             FROM rcep_delivery_inspection.tbl_delivery WHERE is_cancelled = 0 AND region != '' 
        //                 AND DATE(deliveryDate) >= :week_start AND DATE(deliveryDate) <= :week_end 
        //                 GROUP BY region ORDER BY region ASC"), array(

        //             'week_start' => date("Y-m-d", strtotime($current_week['start'])),
        //             'week_end' => date("Y-m-d", strtotime($current_week['end'])),
        //         ));*/
				
		// 	$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
        //         FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
        //             JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
        //             WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
        //             GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(
    
        //         'week_start' => date("Y-m-d", strtotime($current_week['start'])),
        //         'week_end' => date("Y-m-d", strtotime($current_week['end'])),
        //     ));

        //     if(count($confirmed_delivery_regions) > 0){ $region_list = $confirmed_delivery_regions; }else{ $region_list = "no_deliveries"; };
		// 	$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
        //     $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));
			
        //     return view('dashboard.ictd_index')
        //             ->with(compact('confirmed'))
        //             ->with(compact('actual'))
        //             ->with(compact('distributed'))
        //             ->with('regions', $regions)
        //             ->with('week_start', $current_week['start'])
        //             ->with('week_end', $current_week['end'])
        //             ->with('filter_start', $current_week['filter_start'])
        //             ->with('filter_end', $current_week['filter_end'])
        //             ->with('delivery_regions', $region_list)
		// 			->with('latest_mirror_delivery_date', $latest_mirror_delivery_date);


        // }else if(Auth::user()->roles->first()->name == "coop-operator"){
        //     $coop_accre = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->value('coopAccreditation');
        //     $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accre)->value('coopName');

            
        //     $regions = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
        //         ->select('region_name')
        //         ->where('accreditation_no', $coop_accre)
        //         ->orderBy('region_name', "asc")
        //         ->groupBy('region_name')
        //         ->get();

        //     $region_count = count($regions);

		// 	$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
        //     $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));
			
        //     return view('dashboard.coop_index')
        //         ->with('coop_accre', $coop_accre)
        //         ->with('coop_name', $coop_name)
        //         ->with('coop_regions', $regions)
		// 		->with('coop_regions_count', $region_count)
		// 		->with('latest_mirror_delivery_date', $latest_mirror_delivery_date);

        // }else if(Auth::user()->roles->first()->name == "bpi-nsqcs"){
        //     return redirect()->route('coop.rla.pmo');
        // }
		
		// else if(Auth::user()->roles->first()->name == "sed-caller"){
        //     return redirect('sed/farmers/all');
        // }else if(Auth::user()->roles->first()->name == "sed-caller-manager"){
        //     return redirect('sed/dashboard');
        // }else if(Auth::user()->roles->first()->name == "it-sra"){
        //     return redirect('sra/paymaya');
        // }else{
        //     $getRegions = $this->get_regions();
        //     $coops = array();
        //     foreach ($getRegions as $reg) {
        //         $get_coops = $this->get_coops($reg->id);
        //         $get_coops_ctr = $this->get_coops_ctr($reg->id);
        //         if ($get_coops_ctr > 0) {
        //             $data = array(
        //                 'cooperatives' => $get_coops,
        //                 'region' => $reg->regDesc,
        //                 'coop_count' => $get_coops_ctr
        //             );
        //             array_push($coops, $data);
        //         }
        //     }


        //     $national_data = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_national_reports")
        //         ->first();

        //     // if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance"){
              
        //     // }else{
        //     //         return $this->live_index();
        //     // }

        //     if($national_data == null){
        //         return $this->live_index();
        //     }

     
            
        //     try {
        //         $distributed =  $national_data;

                

        //         if(count($distributed) > 0){
        //             $distributed = $distributed;
        //         }else{
        //             $distributed = "N/A";
        //         }
        //     } catch (\Exception $e) {
        //         dd($e);
        //         $distributed = "N/A";
        //     }

        //     $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
        //         ->where('region', '!=', '')
        //         ->groupBy('region')
        //         ->orderBy('region')
        //         ->get();
            
		// 	$total_coops =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")->groupBy('coopAccreditation')->get();
        //     $total_seed_growers = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")->get();
        //     $total_seed_tags = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")->get();
            
     
            
        //     $transferred = \DB::connection('delivery_inspection_db')
        //             ->table('tbl_actual_delivery')
        //             ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
        //             //->where('batchTicketNumber',"TRANSFER")
        //             ->where('transferCategory', 'P')
        //             ->first();
			
        //     $transfer_seedTag = \DB::connection('delivery_inspection_db')
        //             ->table('tbl_actual_delivery')
        //             ->select("seedTag")
        //             ->where('transferCategory', 'P')
        //             ->groupBy("seedTag")
        //             ->get();
                  
        //     $transfer_seedTag = json_decode(json_encode($transfer_seedTag), true);


        //     $transferred_2 = DB::connection("delivery_inspection_db")
        //         ->table("tbl_actual_delivery")
        //         ->whereIn("seedTag", $transfer_seedTag)
        //         ->sum("totalBagCount");



		// 	$current_week = $this->get_current_week();
			
		// 	$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
        //         FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
        //             JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
        //             WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
        //             GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(
    
        //         'week_start' => date("Y-m-d", strtotime($current_week['start'])),
        //         'week_end' => date("Y-m-d", strtotime($current_week['end'])),
        //     ));
        //     if(count($confirmed_delivery_regions) > 0){ $region_list = $confirmed_delivery_regions; }else{ $region_list = "no_deliveries"; };
			
		// 	$paymaya_beneficiaries = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->groupBy('beneficiary_id')->get());
        //     $paymaya_bags = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->get());

        //     $raw = "SELECT * from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex !='' and substr(sex, 1, 2) like 'M' and paymaya_code in (SELECT paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_claim)";

        //     $paymaya_beneficiaries_male = count(DB::select(DB::raw($raw)));

        
            
        //     $raw = "SELECT * from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex !='' and substr(sex, 1, 2) like 'F' and paymaya_code in (SELECT paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_claim)";
        //     $paymaya_beneficiaries_female = count(DB::select(DB::raw($raw)));

		// 	$paymaya_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('qrValStart', '!=', '')->where('qrValEnd','!=','')->sum('totalBagCount');
			
		// 	//get all rrp data for all prv
        //     $nrp_dropoff = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->get();
        //     $nrp_farmers = 0;
        //     $nrp_bags = 0;
        //     $nrp_area = 0;
        //     foreach($nrp_dropoff as $nrp_row){
        //         $database = $GLOBALS['season_prefix']."prv_".substr($nrp_row->prv,0,4);
        //         $nrp_prv_details = DB::table($database.".new_released")
        //             ->select(DB::raw('SUM(bags_claimed) as total_bags'),
        //                 DB::raw('COUNT(new_released_id) as total_farmers'),
        //                 DB::raw('SUM(claimed_area) as total_area'))
        //                 ->where("category", "HYBRID")
        //             ->first();

        //         $nrp_farmers += $nrp_prv_details->total_farmers;
        //         $nrp_bags += $nrp_prv_details->total_bags;
        //         $nrp_area += $nrp_prv_details->total_area;
        //     }

        //     if($distributed == "N/A"){
        //         $process_time = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")
        //             ->groupBy("region")
        //             ->get();

        //         $load = (count($process_time)/16)*100;
        //         $load = number_format($load,2);
        //     }else{
        //         $load = 100;
        //     }

    
        //     $confirmed = json_decode(json_encode(array("total_bag_count"=> $distributed->total_coop_confirmed,"commitment" => $distributed->total_coop_commitments)),false);



        //     $actual = json_decode(json_encode(array("total_bag_count"=> $distributed->total_actual)),false);
            
        //     $yield_data = json_decode(json_encode(array("yield"=> $distributed->yield)),false);
        //     $yield_data_all = json_decode(json_encode(array("yield"=> $distributed->yield_ws2021)),false);
            
        //     $pre_registered_data = (object) array();
        //                     $pre_registered_data->total_farmers = 0;
        //                     $pre_registered_data->total_male = 0;
        //                     $pre_registered_data->total_female = 0;
        //                     $pre_registered_data->total_claimed_area = 0;
        //                     $pre_registered_data->total_actual_area = 0;
        //                     $pre_registered_data->total_bags = 0;


        //     return view('dashboard.index')
		// 		->with(compact('confirmed'))
		// 		->with(compact('actual'))
		// 		->with(compact('transferred'))
        //         ->with(compact('transferred_2'))
        //         ->with("yield_data",$yield_data)
        //         ->with("yield_data_all",$yield_data_all)
		// 		->with(compact('distributed'))
		// 		->with(compact('pre_registered_data'))
		// 		->with('coops', $coops)
		// 		->with('regions', $regions)
		// 		->with('week_start', $current_week['start'])
		// 		->with('week_end', $current_week['end'])
		// 		->with('filter_start', $current_week['filter_start'])
		// 		->with('filter_end', $current_week['filter_end'])
		// 		->with('delivery_regions', $region_list)
		// 		->with('total_coops', count($total_coops))
		// 		->with('total_seed_growers', count($total_seed_growers))
		// 		->with('total_seed_tags', count($total_seed_tags))
		// 		->with('paymaya_beneficiaries', $paymaya_beneficiaries)
		// 		->with('paymaya_bags', $paymaya_bags)
		// 		->with('paymaya_delivery', $paymaya_delivery)
		// 		->with('paymaya_male', $paymaya_beneficiaries_male)
        //         ->with('paymaya_female', $paymaya_beneficiaries_female)
        //         ->with('load', $load)
        //         ->with('nrp_farmers', $nrp_farmers)
        //         ->with('nrp_bags', $nrp_bags)
        //         ->with('nrp_area', number_format($nrp_area,2,".",","));
        // }


        if(Auth::user()->roles->first()->name == "da-icts"){
            $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                ->where('region', '!=', '')
                ->groupBy('region')
                ->orderBy('region')
                ->get();

            $confirmed = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber', 'NOT LIKE', '%void%')
                    ->where('dropOffPoint', 'NOT LIKE', '%void%')
                    ->where('is_cancelled', 0)
                    ->first();

            // Get total actual deliveries of dropoff point
            $actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber',"!=","TRANSFER")
                    ->first();

            $distributed = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_national_reports')
                    ->select('*')
                    ->first();


            $target = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery_sum")
                    ->selectRaw('SUM(targetVolume) as total_target_volume')
                    ->where('region', 'LIKE', $request->region)
                    ->where('province', 'LIKE', $request->province)
                    ->whereMonth('targetMonthFrom', 'LIKE', '2023-08-01')
                    ->whereMonth('targetMonthTo', 'LIKE', '2024-02-29')  
                    ->first();
                    
            $actualSum = $actual->total_bag_count;
            $targetSum = $target->total_target_volume;
            
            $percentage = ($targetSum !== 0) ? ($actualSum / $targetSum) * 100 : 0;

            $current_week = $this->get_current_week();

            /*$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,region  
                    FROM rcep_delivery_inspection.tbl_delivery WHERE is_cancelled = 0 AND region != '' 
                        AND DATE(deliveryDate) >= :week_start AND DATE(deliveryDate) <= :week_end 
                        GROUP BY region ORDER BY region ASC"), array(

                    'week_start' => date("Y-m-d", strtotime($current_week['start'])),
                    'week_end' => date("Y-m-d", strtotime($current_week['end'])),
                ));*/
				
			$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
                    JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
                    WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
                    GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(
    
                'week_start' => date("Y-m-d", strtotime($current_week['start'])),
                'week_end' => date("Y-m-d", strtotime($current_week['end'])),
            ));

            if(count($confirmed_delivery_regions) > 0){ $region_list = $confirmed_delivery_regions; }else{ $region_list = "no_deliveries"; };
			$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
            $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));
			
            return view('dashboard.ictd_index')
                    ->with(compact('confirmed'))
                    ->with(compact('actual'))
                    ->with(compact('distributed'))
                    ->with('regions', $regions)
                    ->with('week_start', $current_week['start'])
                    ->with('week_end', $current_week['end'])
                    ->with('filter_start', $current_week['filter_start'])
                    ->with('filter_end', $current_week['filter_end'])
                    ->with('delivery_regions', $region_list)
					->with('latest_mirror_delivery_date', $latest_mirror_delivery_date)
                    ->with('percentage', $percentage);


        }else if(Auth::user()->roles->first()->name == "coop-operator"){
            $coop_accre = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->value('coopAccreditation');
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accre)->value('coopName');

            
            $regions = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->select('region_name')
                ->where('accreditation_no', $coop_accre)
                ->orderBy('region_name', "asc")
                ->groupBy('region_name')
                ->get();

            $region_count = count($regions);

			$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
            $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));
			
            return view('dashboard.coop_index')
                ->with('coop_accre', $coop_accre)
                ->with('coop_name', $coop_name)
                ->with('coop_regions', $regions)
				->with('coop_regions_count', $region_count)
				->with('latest_mirror_delivery_date', $latest_mirror_delivery_date)
                ->with('percentage', 0);

        }else if(Auth::user()->roles->first()->name == "bpi-nsqcs"){
            return redirect()->route('coop.rla.pmo');
        }
		
		else if(Auth::user()->roles->first()->name == "sed-caller"){
            return redirect('sed/farmers/all');
        }else if(Auth::user()->roles->first()->name == "sed-caller-manager"){
            return redirect('sed/dashboard');
        }else if(Auth::user()->roles->first()->name == "it-sra"){
            return redirect('sra/paymaya');
        }else{
            
            // $actuals = DB::connection('delivery_inspection_db')
            // ->table('tbl_actual_delivery')
            // ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
            // ->where('batchTicketNumber', "!=", "TRANSFER")
            // ->get();
            $actualSum = 0; 

            // foreach($actuals as $actual)
            // {
            //     $actualSum = $actual->total_bag_count;
            // }
            
            // $targets = DB::connection('delivery_inspection_db')
            // ->table('tbl_delivery_sum')
            // ->selectRaw('SUM(targetVolume) as total_target_volume')
            // // ->where('targetMonthFrom', '>=', '2023-09-01')
            // ->where('targetMonthTo', 'LIKE', '2024-02-29')
            // ->get();
        
            // dd($targets);
            $targetSum = 0;
            
            // foreach ($targets as $target) {
            //     $targetSum = $target->total_target_volume;
            // }
            
            $percentage = 0;
            // if($targets && $targetSum > 0 && $actualSum > 0){
            //     $percentage = ($targetSum !== 0) ? ($actualSum / $targetSum) * 100 : 0;
            // }else{
            //     $percentage = 0;
            // }
            
            // $getRegions = $this->get_regions();
            // $coops = array();
            // foreach ($getRegions as $reg) {  
            //     $get_coops = $this->get_coops($reg->id);
            //     $get_coops_ctr = $this->get_coops_ctr($reg->id);
            //     if ($get_coops_ctr > 0) {
            //         $data = array(
            //             'cooperatives' => $get_coops,
            //             'region' => $reg->regDesc,
            //             'coop_count' => $get_coops_ctr
            //         );
            //         array_push($coops, $data);
            //     }
            // }

          
            $national_data = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_national_reports")
                ->first();

            // if(Auth::user()->roles->first()->name == "rcef-programmer"){
              
            // }else{
            //         return $this->live_index();
            // }

            // if($national_data == null){
            //     return $this->live_index();
            // }


            
            try {
                $distributed =  $national_data;

                

                if(count($distributed) > 0){
                    $distributed = $distributed;
                }else{
                    $distributed = "N/A";
                }
            } catch (\Exception $e) {
                dd($e);
                $distributed = "N/A";
            }

            // $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            //     ->where('region', '!=', '')
            //     ->groupBy('region')
            //     ->orderBy('region')
            //     ->get();
            
			// $total_coops =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")->groupBy('coopAccreditation')->get();
            // $total_seed_growers = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")->get();
            // $total_seed_tags = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")->get();
            
     
            
            $transferred = \DB::connection('delivery_inspection_db')
                    ->table('tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    //->where('batchTicketNumber',"TRANSFER")
                    ->where('transferCategory', 'P')
                    ->first();
            $transfer_seedTag = \DB::connection('delivery_inspection_db')
                    ->table('tbl_actual_delivery')
                    ->select("seedTag")
                    ->where('transferCategory', 'P')
                    ->groupBy("seedTag")
                    ->get();
            $transfer_seedTag = json_decode(json_encode($transfer_seedTag), true);


            $transferred_2 = DB::connection("delivery_inspection_db")
                ->table("tbl_actual_delivery")
                ->whereIn("seedTag", $transfer_seedTag)
                ->sum("totalBagCount");
            


			$current_week = $this->get_current_week();
			
			$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
                    JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
                    WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
                    GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(
    
                'week_start' => date("Y-m-d", strtotime($current_week['start'])),
                'week_end' => date("Y-m-d", strtotime($current_week['end'])),
            ));
            if(count($confirmed_delivery_regions) > 0){ $region_list = $confirmed_delivery_regions; }else{ $region_list = "no_deliveries"; };
            // $pythonPath = 'C://Python312//python.exe';
            $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
            // $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

            $scriptPath = base_path('app/Http/PyScript/home-dashboard.scripts/index.py');

            // Escape the arguments
            $ssn = $GLOBALS["season_prefix"];

            $escapedSsn = escapeshellarg($ssn);

            // Construct the command with arguments as a single string
            $command = "$pythonPath \"$scriptPath\" $escapedSsn";
        
            // Create a new process
            $process = new Process($command);

            try {
                // Run the process
                $process->mustRun();
    
                $output = $process->getOutput();
                $result = json_decode($output, true);

                  
                $paymaya_beneficiaries = $result["paymaya_beneficiaries"];
                $paymaya_bags = $result["paymaya_bags"];
                $paymaya_beneficiaries_male = $result["paymaya_beneficiaries_male"];
                $paymaya_beneficiaries_female = $result["paymaya_beneficiaries_female"];
                $paymaya_delivery = $result["paymaya_delivery"];
                
                $buffer = isset($distributed->total_buffer) ? $distributed->total_buffer : 0;

            $totalMaleFemale = (isset($distributed->total_male) ? $distributed->total_male: 0) + (isset($distributed->total_female) ? $distributed->total_female : 0);
            
            if($totalMaleFemale == 0)
            {
                $malePercentage = 0;
                $femalePercentage = 0;
            }
            else{
                $malePercentage = (isset($distributed->total_male) ? $distributed->total_male : 0) / $totalMaleFemale * 100;
                $femalePercentage = (isset($distributed->total_female) ? $distributed->total_female : 0) / $totalMaleFemale * 100;


                // dd($totalMaleFemale,$distributed->total_male,$distributed->total_female, $malePercentage,$femalePercentage);
            }

            $total_yield = 0;
            // dd($malePercentage,$femalePercentage);
            $confirmed = json_decode(json_encode(array("total_bag_count"=> isset($distributed->total_coop_confirmed) ? $distributed->total_coop_confirmed :0,"total_bag_count_RCEF"=> isset($distributed->total_coop_confirmed_RCEF) ? $distributed->total_coop_confirmed_RCEF : 0,"total_bag_count_NRP"=> isset($distributed->total_coop_confirmed_NRP) ? $distributed->total_coop_confirmed_NRP :0,"commitment" => isset($distributed->total_coop_commitments) ? $distributed->total_coop_commitments : 0)),false);

            
            $actual = json_decode(json_encode(array("total_bag_count"=> isset($distributed->total_actual) ? $distributed->total_actual : 0)),false);
            
            
            $yield_data = json_decode(json_encode(array("yield"=> isset($distributed->yield) ? $distributed->yield : 0)),false);
            $yield_data_all = json_decode(json_encode(array("yield"=> isset($distributed->yield_ws2021) ? $distributed->yield_ws2021 : 0)),false);
            
            $pre_registered_data = (object) array();
            $pre_registered_data->total_farmers = 0;
            $pre_registered_data->total_male = 0;
            $pre_registered_data->total_female = 0;
            $pre_registered_data->total_claimed_area = 0;
            $pre_registered_data->total_actual_area = 0;
            $pre_registered_data->total_bags = 0;
            $rcefDelivery = 0;
            $nrpDelivery = 0;

            $week_start = $current_week['start'];
            $week_end = $current_week['end'];
            $filter_start = $current_week['filter_start'];
            $filter_end = $current_week['filter_end'];

            // $nrp_area = number_format($nrp_area,2,".",",");
            $actualSum = number_format($actualSum,2);
            $targetSum = number_format($targetSum);
            $percentage = number_format($percentage,2);

            $delivery_regions = $region_list;
            
            return view('dashboard.index', 
                compact(
                    "confirmed",
                    "actual",
                    "buffer",
                    "transferred_2",
                    "malePercentage",
                    "femalePercentage",
                    "total_yield",
                    "yield_data",
                    "distributed",
                    "pre_registered_data",
                    "week_start",
                    "week_end",
                    "filter_start",
                    "filter_end",
                    "delivery_regions",
                    "paymaya_beneficiaries",
                    "paymaya_bags",
                    "paymaya_delivery",
                    // "paymaya_male",
                    // "paymaya_female",
                    "actualSum",
                    "targetSum",
                    "percentage"
                    )
                );

            } catch (ProcessFailedException $exception) {
                // Handle the exception
                echo $exception->getMessage();
            }
			// $paymaya_beneficiaries = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')->get());
            // $paymaya_bags = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->get());
            
            // $raw = "SELECT * from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex !='' and substr(sex, 1, 2) like 'M' and paymaya_code in (SELECT paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_claim)";

            // $paymaya_beneficiaries_male = count(DB::select(DB::raw($raw)));

        
            
            // $raw = "SELECT * from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex !='' and substr(sex, 1, 2) like 'F' and paymaya_code in (SELECT paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_claim)";
            // $paymaya_beneficiaries_female = count(DB::select(DB::raw($raw)));

			// $paymaya_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('qrValStart', '!=', '')->where('qrValEnd','!=','')->sum('totalBagCount');
			
			//get all rrp data for all prv
            // $nrp_dropoff = [];//DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->get();
            // $nrp_farmers = 0;
            // $nrp_bags = 0;
            // $nrp_area = 0;
            // foreach($nrp_dropoff as $nrp_row){
            //     $database = $GLOBALS['season_prefix']."prv_".substr($nrp_row->prv,0,4);
            //     $nrp_prv_details = DB::table($database.".new_released")
            //         ->select(DB::raw('SUM(bags_claimed) as total_bags'),
            //             DB::raw('COUNT(new_released_id) as total_farmers'),
            //             DB::raw('SUM(claimed_area) as total_area'))
            //             ->where("category", "HYBRID")
            //         ->first();

            //     $nrp_farmers += $nrp_prv_details->total_farmers;
            //     $nrp_bags += $nrp_prv_details->total_bags;
            //     $nrp_area += $nrp_prv_details->total_area;
            // }
            
            
            // if($distributed == "N/A"){
            //     $process_time = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")
            //         ->groupBy("region")
            //         ->get();

            //     $load = (count($process_time)/16)*100;
            //     $load = number_format($load,2);
            // }else{
            //     $load = 100;
            // }
            
            // $buffer = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            // ->where('isBuffer',1)
            // ->sum('totalBagCount');
            
            
        }
        //     return view('dashboard.index')
		// 		->with(compact('confirmed'))
		// 		->with(compact('actual'))
		// 		->with(compact('buffer'))
		// 		->with(compact('transferred'))
        //         ->with(compact('transferred_2'))
        //         ->with(compact('malePercentage'))
        //         ->with(compact('femalePercentage'))
        //         ->with(compact('total_yield'))
        //         ->with("yield_data",$yield_data)
        //         ->with("yield_data_all",$yield_data_all)
		// 		->with(compact('distributed'))
		// 		->with(compact('pre_registered_data'))
		// 		// ->with('regions', $regions)
		// 		->with('week_start', $current_week['start'])
		// 		->with('week_end', $current_week['end'])
		// 		->with('filter_start', $current_week['filter_start'])
		// 		->with('filter_end', $current_week['filter_end'])
		// 		->with('delivery_regions', $region_list)
		// 		// ->with('total_coops', count($total_coops))
		// 		// ->with('total_seed_growers', count($total_seed_growers))
		// 		// ->with('total_seed_tags', count($total_seed_tags))
		// 		->with('paymaya_beneficiaries', $paymaya_beneficiaries)
		// 		->with('paymaya_bags', $paymaya_bags)
		// 		->with('paymaya_delivery', $paymaya_delivery)
		// 		->with('paymaya_male', $paymaya_beneficiaries_male)
        //         ->with('paymaya_female', $paymaya_beneficiaries_female)
        //         // ->with('load', $load)
        //         ->with('nrp_farmers', $nrp_farmers)
        //         ->with('nrp_bags', $nrp_bags)
        //         ->with('nrp_area', number_format($nrp_area,2,".",","))
        //         ->with('actualSum', number_format($actualSum,2))
        //         ->with('targetSum', number_format($targetSum))
        //         ->with('percentage', number_format($percentage,2));
    }
	

    //LIVE INDEX

    public function live_index(){


		if(Auth::user()->roles->first()->name == "da-icts"){
            $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery")
                ->where('region', '!=', '')
                ->groupBy('region')
                ->orderBy('region')
                ->get();

            $confirmed = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery")
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'), DB::raw(" '0' as commitment"))
                    ->where('batchTicketNumber', 'NOT LIKE', '%void%')
                    ->where('dropOffPoint', 'NOT LIKE', '%void%')
                    ->where('is_cancelled', 0)
                    ->first();

            

            // Get total actual deliveries of dropoff point
            $actual = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_actual_delivery")
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('batchTicketNumber',"!=","TRANSFER")
                    ->first();

            
             $distributed = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_national_reports')
                    ->select('*')
                    ->first();

            $current_week = $this->get_current_week();

            /*$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,region  
                    FROM rcep_delivery_inspection.tbl_delivery WHERE is_cancelled = 0 AND region != '' 
                        AND DATE(deliveryDate) >= :week_start AND DATE(deliveryDate) <= :week_end 
                        GROUP BY region ORDER BY region ASC"), array(

                    'week_start' => date("Y-m-d", strtotime($current_week['start'])),
                    'week_end' => date("Y-m-d", strtotime($current_week['end'])),
                ));*/
				
			$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
                    JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
                    WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
                    GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(
    
                'week_start' => date("Y-m-d", strtotime($current_week['start'])),
                'week_end' => date("Y-m-d", strtotime($current_week['end'])),
            ));

            if(count($confirmed_delivery_regions) > 0){ $region_list = $confirmed_delivery_regions; }else{ $region_list = "no_deliveries"; };
			$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
            $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));
			
            return view('dashboard.ictd_index')
                    ->with(compact('confirmed'))
                    ->with(compact('actual'))
                    ->with(compact('distributed'))
                    ->with('regions', $regions)
                    ->with('week_start', $current_week['start'])
                    ->with('week_end', $current_week['end'])
                    ->with('filter_start', $current_week['filter_start'])
                    ->with('filter_end', $current_week['filter_end'])
                    ->with('delivery_regions', $region_list)
					->with('latest_mirror_delivery_date', $latest_mirror_delivery_date);


        }else if(Auth::user()->roles->first()->name == "coop-operator"){
            $coop_accre = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->value('coopAccreditation');
            $coop_name = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $coop_accre)->value('coopName');

            
            $regions = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
                ->select('region_name')
                ->where('accreditation_no', $coop_accre)
                ->orderBy('region_name', "asc")
                ->groupBy('region_name')
                ->get();

            $region_count = count($regions);

			$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
            $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));
			
            return view('dashboard.coop_index')
                ->with('coop_accre', $coop_accre)
                ->with('coop_name', $coop_name)
                ->with('coop_regions', $regions)
				->with('coop_regions_count', $region_count)
				->with('latest_mirror_delivery_date', $latest_mirror_delivery_date);

        }else if(Auth::user()->roles->first()->name == "bpi-nsqcs"){
            return redirect()->route('coop.rla.pmo');
        }
		
		else if(Auth::user()->roles->first()->name == "sed-caller"){
            return redirect('sed/farmers/all');
        }else if(Auth::user()->roles->first()->name == "sed-caller-manager"){
            return redirect('sed/dashboard');
        }else if(Auth::user()->roles->first()->name == "it-sra"){
            return redirect('sra/paymaya');
        }else{
            $getRegions = $this->get_regions();
            $coops = array();
            foreach ($getRegions as $reg) {
                $get_coops = $this->get_coops($reg->id);
                $get_coops_ctr = $this->get_coops_ctr($reg->id);
                if ($get_coops_ctr > 0) {
                    $data = array(
                        'cooperatives' => $get_coops,
                        'region' => $reg->regDesc,
                        'coop_count' => $get_coops_ctr
                    );
                    array_push($coops, $data);
                }
            }


            $confirmed = \DB::connection('delivery_inspection_db')
                    ->table('tbl_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->where('isBuffer',  0)
                    ->where('is_cancelled', 0)
                    ->first();
            
            $confirmed_tickets = \DB::connection('delivery_inspection_db')
                    ->table('tbl_delivery')
                    ->select("batchTicketNumber")
                    ->where('isBuffer',  0)
                    ->where('is_cancelled', 0)
                    ->groupBy("batchTicketNumber")
                    ->get();


                     $confirmed_tickets = json_decode(json_encode($confirmed_tickets), true);

            // Get total actual deliveries of dropoff point
            $actual = \DB::connection('delivery_inspection_db')
                    ->table('tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    ->whereIn("batchTicketNumber", $confirmed_tickets)
                    ->first();
            
            
            try {
                // $distributed = DB::table($GLOBALS['season_prefix']."rcep_reports" . '.lib_national_reports')
                //     ->select('*')
                //     ->first();
                

                $distributed = null;
                $add_on_distributed = null;

                // $distributed = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report")
                //     ->select(DB::raw("SUM(total_farmer) as total_farmers"),DB::raw("SUM(total_claimed_area) as total_claimed_area"),DB::raw("SUM(total_bags) as total_bags"))->first();

                // $add_on_distributed = DB::table($GLOBALS['season_prefix']."rcep_reports_view.distribution_report_group")
                //     ->select(DB::raw("SUM(actual_area) as actual_area"),DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female") )
                //     ->first();

                if($add_on_distributed != null){
                    $distributed->actual_area = $add_on_distributed->actual_area;
                    $distributed->total_male = $add_on_distributed->total_male;
                    $distributed->total_female = $add_on_distributed->total_female;
                    

                }



                $not_in_42 = array("ILOCOS NORTE", "COTABATO (NORTH COTABATO)", "ISABELA");

                $yield_data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                    ->select(DB::raw("(SUM(total_production)/SUM(area))/1000 as yield"))
                    ->whereNotIn("province", $not_in_42 )
                    ->first();

                $yield_data_all = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
                    ->select(DB::raw("(SUM(total_production)/SUM(area))/1000 as yield"))
                    ->first();


                $schema = DB::table("information_schema.VIEWS")
                    ->where("TABLE_SCHEMA", $GLOBALS['season_prefix']."rcep_reports_view")
                    ->where("TABLE_NAME", "pre_registered_data")
                    ->first();

                    if($schema != null){
                        $pre_registered_data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.pre_registered_data")
                        ->select(DB::raw("SUM(total_farmer) as total_farmers"),DB::raw("SUM(total_male) as total_male"),DB::raw("SUM(total_female) as total_female"),DB::raw("SUM(total_claimed_area) as total_claimed_area"),DB::raw("SUM(total_actual_area) as total_actual_area"),DB::raw("SUM(total_bags) as total_bags"))->first();    
                    }else{

                            $pre_registered_data = (object) array();
                            $pre_registered_data->total_farmers = 0;
                            $pre_registered_data->total_male = 0;
                            $pre_registered_data->total_female = 0;
                            $pre_registered_data->total_claimed_area = 0;
                            $pre_registered_data->total_actual_area = 0;
                            $pre_registered_data->total_bags = 0;
                            
                            
                    }


                if(count($distributed) > 0){
                    $distributed = $distributed;
                }else{
                    $distributed = "N/A";
                }
            } catch (\Exception $e) {
                dd($e);
                $distributed = "N/A";
            }

            $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                ->where('region', '!=', '')
                ->groupBy('region')
                ->orderBy('region')
                ->get();
            
			$total_coops =  DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")->groupBy('coopAccreditation')->get();
            $total_seed_growers = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_seed_grower")->get();
            $total_seed_tags = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_rla_details")->get();
            
     
            
            $transferred = \DB::connection('delivery_inspection_db')
                    ->table('tbl_actual_delivery')
                    ->select(\DB::RAW('SUM(totalBagCount) as total_bag_count'))
                    //->where('batchTicketNumber',"TRANSFER")
                    ->where('transferCategory', 'P')
                    ->first();
			
            $transfer_seedTag = \DB::connection('delivery_inspection_db')
                    ->table('tbl_actual_delivery')
                    ->select("seedTag")
                    ->where('transferCategory', 'P')
                    ->groupBy("seedTag")
                    ->get();
                  
            $transfer_seedTag = json_decode(json_encode($transfer_seedTag), true);


            $transferred_2 = DB::connection("delivery_inspection_db")
                ->table("tbl_actual_delivery")
                ->whereIn("seedTag", $transfer_seedTag)
                ->sum("totalBagCount");



			$current_week = $this->get_current_week();
			
			$confirmed_delivery_regions = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
                    JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
                    WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
                    GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(
    
                'week_start' => date("Y-m-d", strtotime($current_week['start'])),
                'week_end' => date("Y-m-d", strtotime($current_week['end'])),
            ));
            if(count($confirmed_delivery_regions) > 0){ $region_list = $confirmed_delivery_regions; }else{ $region_list = "no_deliveries"; };
			
			$paymaya_beneficiaries = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->groupBy('beneficiary_id')->get());
            $paymaya_bags = count(DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_claim')->get());

            $raw = "SELECT * from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex !='' and substr(sex, 1, 2) like 'M' and paymaya_code in (SELECT paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_claim)";

            $paymaya_beneficiaries_male = count(DB::select(DB::raw($raw)));

        
            
            $raw = "SELECT * from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_beneficiaries where sex !='' and substr(sex, 1, 2) like 'F' and paymaya_code in (SELECT paymaya_code from ".$GLOBALS['season_prefix']."rcep_paymaya.tbl_claim)";
            $paymaya_beneficiaries_female = count(DB::select(DB::raw($raw)));

			$paymaya_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')->where('qrValStart', '!=', '')->where('qrValEnd','!=','')->sum('totalBagCount');
			
			//get all rrp data for all prv
            $nrp_dropoff = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province')->get();
            $nrp_farmers = 0;
            $nrp_bags = 0;
            $nrp_area = 0;
            // foreach($nrp_dropoff as $nrp_row){
            //     $database = $GLOBALS['season_prefix']."prv_".substr($nrp_row->prv,0,4);
            //     $nrp_prv_details = DB::table($database.".nrp_profile")
            //         ->select(DB::raw('SUM(num_of_bag) as total_bags'),
            //             DB::raw('COUNT(id) as total_farmers'),
            //             DB::raw('SUM(area) as total_area'))
            //         ->first();

            //     $nrp_farmers += $nrp_prv_details->total_farmers;
            //     $nrp_bags += $nrp_prv_details->total_bags;
            //     $nrp_area += $nrp_prv_details->total_area;
            // }

            if($distributed == "N/A"){
                $process_time = DB::table($GLOBALS['season_prefix']."rcep_reports.lib_regional_reports")
                    ->groupBy("region")
                    ->get();

                $load = (count($process_time)/16)*100;
                $load = number_format($load,2);
            }else{
                $load = 100;
            }

    

            return view('dashboard.index')
				->with(compact('confirmed'))
				->with(compact('actual'))
				->with(compact('transferred'))
                ->with(compact('transferred_2'))
                ->with("yield_data",$yield_data)
                ->with("yield_data_all",$yield_data_all)
				->with(compact('distributed'))
				->with(compact('pre_registered_data'))
				->with('coops', $coops)
				->with('regions', $regions)
				->with('week_start', $current_week['start'])
				->with('week_end', $current_week['end'])
				->with('filter_start', $current_week['filter_start'])
				->with('filter_end', $current_week['filter_end'])
				->with('delivery_regions', $region_list)
				->with('total_coops', count($total_coops))
				->with('total_seed_growers', count($total_seed_growers))
				->with('total_seed_tags', count($total_seed_tags))
				->with('paymaya_beneficiaries', $paymaya_beneficiaries)
				->with('paymaya_bags', $paymaya_bags)
				->with('paymaya_delivery', $paymaya_delivery)
				->with('paymaya_male', $paymaya_beneficiaries_male)
                ->with('paymaya_female', $paymaya_beneficiaries_female)
                ->with('load', $load)
                ->with('nrp_farmers', $nrp_farmers)
                ->with('nrp_bags', $nrp_bags)
                ->with('nrp_area', number_format($nrp_area,2,".",","));
        }
    
    }


	public function show_delivery_summary(){
        $regions = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
                ->where('region', '!=', '')
                ->groupBy('region')
                ->orderBy('region')
                ->get();
				
		$latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
        $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));

        return view('dashboard.ictd_delivery')
                ->with('regions', $regions)
				->with('latest_mirror_delivery_date', $latest_mirror_delivery_date);
    }

    public function get_coops_ctr($id) {

        $coop = \DB::connection('seed_coop_db')
                ->table('tbl_total_commitment')
                ->select('tbl_cooperatives.coopName', 'tbl_cooperatives.regionId', 'tbl_total_commitment.total_value')
                ->join('tbl_cooperatives', 'tbl_total_commitment.coopID', '=', 'tbl_cooperatives.coopId')
                ->where('regionId', $id)
                ->count();
        return $coop;
    }

    public function get_coops($id) {

        $coop = \DB::connection('seed_coop_db')
                ->table('tbl_total_commitment')
                ->select('tbl_cooperatives.coopName', 'tbl_cooperatives.regionId', 'tbl_total_commitment.total_value')
                ->join('tbl_cooperatives', 'tbl_total_commitment.coopID', '=', 'tbl_cooperatives.coopId')
                ->where('regionId', $id)
                ->orderBy('coopName')
                ->get();
        return $coop;
    }

    public function get_regions() {
        $data = DB::table($GLOBALS['season_prefix']."sdms_db_dev" . '.lib_regions')
                ->select('*')
                ->orderBy('order')
                ->get();
        return $data;
    }

    public function upcoming_harvest_weekly() {
        // this week first day and last day
        $firstday = date('Y-m-d', strtotime("monday -1 week"));
        $lastday = date('Y-m-d', strtotime($firstday . ' + 6 days'));
        // next week first day and last day
        $firstday1 = date('Y-m-d', strtotime("monday 0 week"));
        $lastday1 = date('Y-m-d', strtotime($firstday1 . ' + 6 days'));
        // next next week first day and last day
        $firstday2 = date('Y-m-d', strtotime("monday 1 week"));
        $lastday2 = date('Y-m-d', strtotime($firstday2 . ' + 6 days'));
        // next next next week first day and last day
        $firstday3 = date('Y-m-d', strtotime("monday 2 week"));
        $lastday3 = date('Y-m-d', strtotime($firstday3 . ' + 6 days'));

        $cooperatives = new SeedCooperatives();
        $coops = $cooperatives->seed_cooperatives();

        $producers = new SeedProducers();

        $coop_array = array();
        $coop_array2 = array();
        $coop_array3 = array();
        $coop_array4 = array();

        foreach ($coops as $item) {
            $producers_planted = $producers->seed_producers_planted_filtered($item->coopId);

            foreach ($producers_planted as $item2) {
                $maturity = $item2->maturity - 14; // 21 changed to 14
                $harvest_date = date('Y-m-d', strtotime($item2->Date_planted . ' + ' . $maturity . ' days'));
                $availability_date = date('Y-m-d', strtotime($harvest_date . ' + 60 days')); // 1 month changed to 60 days

                if ($availability_date >= $firstday && $availability_date <= $lastday) {
                    $coop_array[] = array(
                        'week' => 1,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }

                if ($availability_date >= $firstday1 && $availability_date <= $lastday1) {
                    $coop_array2[] = array(
                        'week' => 2,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }

                if ($availability_date >= $firstday2 && $availability_date <= $lastday2) {
                    $coop_array3[] = array(
                        'week' => 3,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }

                if ($availability_date >= $firstday3 && $availability_date <= $lastday3) {
                    $coop_array4[] = array(
                        'week' => 4,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }
            }
        }

        $coop_group = array();
        foreach ($coop_array as $key => $item) {
            $coop_group[$item['coop']][$key] = $item;
        }

        $coop_group2 = array();
        foreach ($coop_array2 as $key => $item) {
            $coop_group2[$item['coop']][$key] = $item;
        }

        $coop_group3 = array();
        foreach ($coop_array3 as $key => $item) {
            $coop_group3[$item['coop']][$key] = $item;
        }

        $coop_group4 = array();
        foreach ($coop_array4 as $key => $item) {
            $coop_group4[$item['coop']][$key] = $item;
        }

        $coopid_array = array();

        $sumArray = array();
        foreach ($coop_group as $key2 => $subArray) {
            foreach ($subArray as $id => $value) {
                if (isset($sumArray[$value['week']][$value['coop']][$value['variety']])) {
                    $sumArray[$value['week']][$value['coop']][$value['variety']] += $value['bags'];
                } else {
                    $sumArray[$value['week']][$value['coop']][$value['variety']] = $value['bags'];
                }
                array_push($coopid_array, $value['coop']);
            }
        }

        $sumArray2 = array();
        foreach ($coop_group2 as $key2 => $subArray2) {
            foreach ($subArray2 as $id2 => $value2) {
                if (isset($sumArray2[$value2['week']][$value2['coop']][$value2['variety']])) {
                    $sumArray2[$value2['week']][$value2['coop']][$value2['variety']] += $value2['bags'];
                } else {
                    $sumArray2[$value2['week']][$value2['coop']][$value2['variety']] = $value2['bags'];
                }
                array_push($coopid_array, $value2['coop']);
            }
        }

        $sumArray3 = array();
        foreach ($coop_group3 as $key3 => $subArray3) {
            foreach ($subArray3 as $id3 => $value3) {
                if (isset($sumArray3[$value3['week']][$value3['coop']][$value3['variety']])) {
                    $sumArray3[$value3['week']][$value3['coop']][$value3['variety']] += $value3['bags'];
                } else {
                    $sumArray3[$value3['week']][$value3['coop']][$value3['variety']] = $value3['bags'];
                }
                array_push($coopid_array, $value3['coop']);
            }
        }

        $sumArray4 = array();
        foreach ($coop_group4 as $key4 => $subArray4) {
            foreach ($subArray4 as $id4 => $value4) {
                if (isset($sumArray4[$value4['week']][$value4['coop']][$value4['variety']])) {
                    $sumArray4[$value4['week']][$value4['coop']][$value4['variety']] += $value4['bags'];
                } else {
                    $sumArray4[$value4['week']][$value4['coop']][$value4['variety']] = $value4['bags'];
                }
                array_push($coopid_array, $value4['coop']);
            }
        }

        foreach ($sumArray as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $data[] = array(
                        'coop_id' => $key2,
                        'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                        'variety' => $key3,
                        'week1' => $item3,
                        'week2' => '',
                        'week3' => '',
                        'week4' => ''
                    );
                }
            }
        }

        foreach ($sumArray2 as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $exist = "false";
                    foreach ($data as $key4 => $item4) {
                        if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                            $data[$key4]['week2'] = $item3;
                            $exist = "true";
                            break;
                        }
                    }
                    if ($exist == "false") {
                        $data[] = array(
                            'coop_id' => $key2,
                            'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                            'variety' => $key3,
                            'week1' => '',
                            'week2' => $item3,
                            'week3' => '',
                            'week4' => ''
                        );
                    }
                }
            }
        }

        foreach ($sumArray3 as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $exist = "false";
                    foreach ($data as $key4 => $item4) {
                        if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                            $data[$key4]['week3'] = $item3;
                            $exist = "true";
                            break;
                        }
                    }
                    if ($exist == "false") {
                        $data[] = array(
                            'coop_id' => $key2,
                            'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                            'variety' => $key3,
                            'week1' => '',
                            'week2' => '',
                            'week3' => $item3,
                            'week4' => ''
                        );
                    }
                }
            }
        }

        foreach ($sumArray4 as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $exist = "false";
                    foreach ($data as $key4 => $item4) {
                        if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                            $data[$key4]['week4'] = $item3;
                            $exist = "true";
                            break;
                        }
                    }
                    if ($exist == "false") {
                        $data[] = array(
                            'coop_id' => $key2,
                            'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                            'variety' => $key3,
                            'week1' => '',
                            'week2' => '',
                            'week3' => '',
                            'week4' => $item3
                        );
                    }
                }
            }
        }

        $table_data = collect($data);
        return Datatables::of($table_data)
                        ->make(true);
    }

    public function upcoming_harvest_weekly_excel_all() {
        set_time_limit(1000);

        $cooperatives = new SeedCooperatives();
        $coops = $cooperatives->seed_cooperatives();

        $producers = new SeedProducers();

        $earliest_date = "";
        $last_date = "";
        $earlist_harvest = "";

        foreach ($coops as $item) {
            $producers_planted = $producers->seed_producers_planted_filtered($item->coopId);

            foreach ($producers_planted as $item2) {
                $maturity = $item2->maturity - 14; // 21 changed to 14
                $harvest_date = date('Y-m-d', strtotime($item2->Date_planted . ' + ' . $maturity . ' days'));
                $availability_date = date('Y-m-d', strtotime($harvest_date . ' + 60 days')); // 1 month changed to 60 days

                if ($earliest_date == "") {
                    $earliest_date = $availability_date;
                }
                if (strtotime($availability_date) < strtotime($earliest_date)) {
                    $earliest_date = $availability_date;
                }

                if ($last_date == "") {
                    $last_date = $availability_date;
                }
                if (strtotime($availability_date) > strtotime($last_date)) {
                    $last_date = $availability_date;
                }

                if ($earlist_harvest == "") {
                    $earlist_harvest = $harvest_date;
                    $data = $item2;
                }
                if (strtotime($harvest_date) < strtotime($earlist_harvest)) {
                    $earlist_harvest = $harvest_date;
                    $data = $item2;
                }
            }
        }

        $current_date = date('Y-m-d');

        // Get number of weeks before current date
        // $past_weeks = strtotime($current_date, 0) - strtotime($earliest_date, 0);
        $past_weeks = date_diff(date_create($earliest_date), date_create($current_date));
        $past_weeks = $past_weeks->format("%R%a days");
        $past_weeks = round($past_weeks / 7);
        // $past_weeks = floor($past_weeks / 604800);
        // Get number of weeks after current date
        // $future_weeks = strtotime($last_date, 0) - strtotime($current_date, 0);
        // $future_weeks = floor($future_weeks / 604800);
        $future_weeks = date_diff(date_create($current_date), date_create($last_date));
        $future_weeks = $future_weeks->format("%R%a days");
        $future_weeks = round($future_weeks / 7);

        $firstday = array();
        $lastday = array();
        $col = array();

        $past_weeks_count = $past_weeks;
        $future_weeks_count = $future_weeks;

        for ($x = 0; $x < $past_weeks_count; $x++) {
            $firstday[] = date('Y-m-d', strtotime("monday -" . $past_weeks . " week"));
            $first = date('Y-m-d', strtotime("monday -" . $past_weeks . " week"));
            $lastday[] = date('Y-m-d', strtotime($first . ' + 6 days'));
            $past_weeks = $past_weeks - 1;
        }

        $firstday[] = date('Y-m-d', strtotime("monday 0 week"));
        $first = date('Y-m-d', strtotime("monday 0 week"));
        $lastday[] = date('Y-m-d', strtotime($first . ' + 6 days'));
        $last = date('Y-m-d', strtotime($first . ' + 6 days'));

        for ($y = 0; $y < $future_weeks_count; $y++) {
            $firstday[] = date('Y-m-d', strtotime("monday +" . $future_weeks . " week"));
            $first = date('Y-m-d', strtotime("monday +" . $future_weeks . " week"));
            $lastday[] = date('Y-m-d', strtotime($first . ' + 6 days'));
            $future_weeks = $future_weeks - 1;
        }

        sort($firstday);
        // dd($firstday);
        sort($lastday);

        $week_count = count($firstday);

        $count = 0;
        while ($count < $week_count) {
            $col[$count] = date('F d, Y', strtotime($firstday[$count])) . ' - ' . date('F d, Y', strtotime($lastday[$count]));
            $count = $count + 1;
        }

        $coop_array = array();

        foreach ($coops as $item) {
            $producers_planted = $producers->seed_producers_planted_filtered($item->coopId);

            foreach ($producers_planted as $item2) {
                $maturity = $item2->maturity - 14; // 21 changed to 14
                $harvest_date = date('Y-m-d', strtotime($item2->Date_planted . ' + ' . $maturity . ' days'));
                $availability_date = date('Y-m-d', strtotime($harvest_date . ' + 60 days')); // 1 month changed to 60 days
                $count = 0;
                while ($count < $week_count) {
                    if ($availability_date >= $firstday[$count] && $availability_date <= $lastday[$count]) {
                        $coop_array[$count][] = array(
                            'week' => $count,
                            'coop' => $item->coopId,
                            'variety' => $item2->variety,
                            'bags' => $item2->Area_Planted_in_ha * 200,
                            'availability_date' => $availability_date,
                            'column' => $col[$count]
                        );
                        break;
                    }
                    $count = $count + 1;
                }
            }
        }

        // echo json_encode($coop_array);

        $coop_group = array();
        $count = 0;
        while ($count < $week_count) {
            if (isset($coop_array[$count])) {
                foreach ($coop_array[$count] as $key => $item) {
                    $coop_group[$count][$item['coop']][$key] = $item;
                }
            }
            $count = $count + 1;
        }

        // echo json_encode($coop_group);

        $sumArray = array();

        $count = 0;
        while ($count < $week_count) {
            if (isset($coop_group[$count])) {
                foreach ($coop_group[$count] as $key2 => $subArray) {
                    foreach ($subArray as $id => $value) {
                        if (isset($sumArray[$count][$value['week']][$value['coop']][$value['variety']])) {
                            $sumArray[$count][$value['week']][$value['coop']][$value['variety']] += $value['bags'];
                        } else {
                            $sumArray[$count][$value['week']][$value['coop']][$value['variety']] = $value['bags'];
                        }
                    }
                }
            }
            $count = $count + 1;
        }

        // echo json_encode($sumArray);

        $data = array();
        $count = 0;
        while ($count < $week_count) {
            if (isset($sumArray[$count])) {
                foreach ($sumArray[$count] as $key => $item) {
                    foreach ($item as $key2 => $item2) {
                        foreach ($item2 as $key3 => $item3) {
                            $exist = "false";
                            if (!empty($data)) {
                                foreach ($data as $key4 => $item4) {
                                    if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                                        $data[$key4][$col[$count]] = $item3;
                                        $exist = "true";
                                        break;
                                    }
                                }
                            }

                            if ($exist == "false") {
                                $data2 = array(
                                    'coop_id' => $key2,
                                    'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                                    'variety' => $key3,
                                );
                                $count2 = 0;
                                $col_count = count($col);
                                while ($count2 < $col_count) {
                                    if ($count2 == $count) {
                                        $data2[$col[$count2]] = $item3;
                                    } else {
                                        $data2[$col[$count2]] = '';
                                    }
                                    $count2 = $count2 + 1;
                                }
                                array_push($data, $data2);
                            }
                        }
                    }
                }
            }
            $count = $count + 1;
        }

        // echo json_encode($data);

        return Excel::create('Number of Bags of Certified Seeds Available Per Week', function($excel) use ($data) {
                    $excel->sheet('Sheet1', function($sheet) use ($data) {
                        $sheet->fromArray($data);
                    });
                })->download('xlsx');
    }

    public function upcoming_harvest_weekly_excel() {
        // this week first day and last day
        $firstday = date('Y-m-d', strtotime("monday -1 week"));
        $lastday = date('Y-m-d', strtotime($firstday . ' + 6 days'));
        $col = date('F d, Y', strtotime($firstday)) . ' - ' . date('F d, Y', strtotime($lastday));
        // next week first day and last day
        $firstday1 = date('Y-m-d', strtotime("monday 0 week"));
        $lastday1 = date('Y-m-d', strtotime($firstday1 . ' + 6 days'));
        $col1 = date('F d, Y', strtotime($firstday1)) . ' - ' . date('F d, Y', strtotime($lastday1));
        // next next week first day and last day
        $firstday2 = date('Y-m-d', strtotime("monday 1 week"));
        $lastday2 = date('Y-m-d', strtotime($firstday2 . ' + 6 days'));
        $col2 = date('F d, Y', strtotime($firstday2)) . ' - ' . date('F d, Y', strtotime($lastday2));
        // next next next week first day and last day
        $firstday3 = date('Y-m-d', strtotime("monday 2 week"));
        $lastday3 = date('Y-m-d', strtotime($firstday3 . ' + 6 days'));
        $col3 = date('F d, Y', strtotime($firstday3)) . ' - ' . date('F d, Y', strtotime($lastday3));

        $cooperatives = new SeedCooperatives();
        $coops = $cooperatives->seed_cooperatives();

        $producers = new SeedProducers();

        $coop_array = array();
        $coop_array2 = array();
        $coop_array3 = array();
        $coop_array4 = array();

        foreach ($coops as $item) {
            $producers_planted = $producers->seed_producers_planted_filtered($item->coopId);

            foreach ($producers_planted as $item2) {
                $maturity = $item2->maturity - 14; // 21 changed to 14
                $harvest_date = date('Y-m-d', strtotime($item2->Date_planted . ' + ' . $maturity . ' days'));
                $availability_date = date('Y-m-d', strtotime($harvest_date . ' + 60 days')); // 1 month changed to 60 days

                if ($availability_date >= $firstday && $availability_date <= $lastday) {
                    $coop_array[] = array(
                        'week' => 1,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }

                if ($availability_date >= $firstday1 && $availability_date <= $lastday1) {
                    $coop_array2[] = array(
                        'week' => 2,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }

                if ($availability_date >= $firstday2 && $availability_date <= $lastday2) {
                    $coop_array3[] = array(
                        'week' => 3,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }

                if ($availability_date >= $firstday3 && $availability_date <= $lastday3) {
                    $coop_array4[] = array(
                        'week' => 4,
                        'coop' => $item->coopId,
                        'variety' => $item2->variety,
                        'bags' => $item2->Area_Planted_in_ha * 200
                    );
                }
            }
        }

        $coop_group = array();
        foreach ($coop_array as $key => $item) {
            $coop_group[$item['coop']][$key] = $item;
        }

        $coop_group2 = array();
        foreach ($coop_array2 as $key => $item) {
            $coop_group2[$item['coop']][$key] = $item;
        }

        $coop_group3 = array();
        foreach ($coop_array3 as $key => $item) {
            $coop_group3[$item['coop']][$key] = $item;
        }

        $coop_group4 = array();
        foreach ($coop_array4 as $key => $item) {
            $coop_group4[$item['coop']][$key] = $item;
        }

        $coopid_array = array();

        $sumArray = array();
        foreach ($coop_group as $key2 => $subArray) {
            foreach ($subArray as $id => $value) {
                if (isset($sumArray[$value['week']][$value['coop']][$value['variety']])) {
                    $sumArray[$value['week']][$value['coop']][$value['variety']] += $value['bags'];
                } else {
                    $sumArray[$value['week']][$value['coop']][$value['variety']] = $value['bags'];
                }
                array_push($coopid_array, $value['coop']);
            }
        }

        $sumArray2 = array();
        foreach ($coop_group2 as $key2 => $subArray2) {
            foreach ($subArray2 as $id2 => $value2) {
                if (isset($sumArray2[$value2['week']][$value2['coop']][$value2['variety']])) {
                    $sumArray2[$value2['week']][$value2['coop']][$value2['variety']] += $value2['bags'];
                } else {
                    $sumArray2[$value2['week']][$value2['coop']][$value2['variety']] = $value2['bags'];
                }
                array_push($coopid_array, $value2['coop']);
            }
        }

        $sumArray3 = array();
        foreach ($coop_group3 as $key3 => $subArray3) {
            foreach ($subArray3 as $id3 => $value3) {
                if (isset($sumArray3[$value3['week']][$value3['coop']][$value3['variety']])) {
                    $sumArray3[$value3['week']][$value3['coop']][$value3['variety']] += $value3['bags'];
                } else {
                    $sumArray3[$value3['week']][$value3['coop']][$value3['variety']] = $value3['bags'];
                }
                array_push($coopid_array, $value3['coop']);
            }
        }

        $sumArray4 = array();
        foreach ($coop_group4 as $key4 => $subArray4) {
            foreach ($subArray4 as $id4 => $value4) {
                if (isset($sumArray4[$value4['week']][$value4['coop']][$value4['variety']])) {
                    $sumArray4[$value4['week']][$value4['coop']][$value4['variety']] += $value4['bags'];
                } else {
                    $sumArray4[$value4['week']][$value4['coop']][$value4['variety']] = $value4['bags'];
                }
                array_push($coopid_array, $value4['coop']);
            }
        }

        foreach ($sumArray as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $data[] = array(
                        'coop_id' => $key2,
                        'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                        'variety' => $key3,
                        $col => $item3,
                        $col1 => '',
                        $col2 => '',
                        $col3 => ''
                    );
                }
            }
        }

        foreach ($sumArray2 as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $exist = "false";
                    foreach ($data as $key4 => $item4) {
                        if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                            $data[$key4][$col1] = $item3;
                            $exist = "true";
                            break;
                        }
                    }
                    if ($exist == "false") {
                        $data[] = array(
                            'coop_id' => $key2,
                            'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                            'variety' => $key3,
                            $col => '',
                            $col1 => $item3,
                            $col2 => '',
                            $col3 => ''
                        );
                    }
                }
            }
        }

        foreach ($sumArray3 as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $exist = "false";
                    foreach ($data as $key4 => $item4) {
                        if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                            $data[$key4][$col2] = $item3;
                            $exist = "true";
                            break;
                        }
                    }
                    if ($exist == "false") {
                        $data[] = array(
                            'coop_id' => $key2,
                            'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                            'variety' => $key3,
                            $col => '',
                            $col1 => '',
                            $col2 => $item3,
                            $col3 => ''
                        );
                    }
                }
            }
        }

        foreach ($sumArray4 as $key => $item) {
            foreach ($item as $key2 => $item2) {
                foreach ($item2 as $key3 => $item3) {
                    $exist = "false";
                    foreach ($data as $key4 => $item4) {
                        if ($item4['coop_id'] == $key2 && $item4['variety'] == $key3) {
                            $data[$key4][$col3] = $item3;
                            $exist = "true";
                            break;
                        }
                    }
                    if ($exist == "false") {
                        $data[] = array(
                            'coop_id' => $key2,
                            'cooperative' => $cooperatives->seed_cooperative($key2)->coopName,
                            'variety' => $key3,
                            $col => '',
                            $col1 => '',
                            $col2 => '',
                            $col3 => $item3
                        );
                    }
                }
            }
        }


        return Excel::create('Number of Bags of Certified Seeds Available Per Week', function($excel) use ($data) {
                    $excel->sheet('Sheet1', function($sheet) use ($data) {
                        $sheet->fromArray($data);
                    });
                })->download('xlsx');
    }

    public function upcoming_harvest_10days() {
        $producers = new SeedProducers();
        $producers_planted = $producers->seed_producers_planted();
        $coopIds = array();

        foreach ($producers_planted as $item) {
            $maturity = $item->maturity - 14; // 21 changed to 14
            $harvest_date = date('Y-m-d', strtotime($item->Date_planted . ' + ' . $maturity . ' days'));
            $days = date('Y-m-d', strtotime(date('Y-m-d') . ' + 10 days'));

            if ($harvest_date >= date('Y-m-d') && $harvest_date <= $days) {
                if (!in_array($item->coopId, $coopIds)) {
                    array_push($coopIds, $item->coopId);
                }
            }
        }

        $cooperatives = new SeedCooperatives();
        $table_data = array();

        foreach ($coopIds as $item2) {
            $coop = $cooperatives->seed_cooperative($item2);

            $data = array(
                'coopId' => $coop->coopId,
                'cooperative' => $coop->coopName,
                'province' => $coop->provDesc
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                        ->addColumn('actions', function($table_data) {
                            return "<button class='btn btn-info view_seed_growers' id='" . $table_data['coopId'] . "' title='View'><i class='fa fa-eye'></i> View</button>";
                        })
                        ->make(true);
    }

    public function upcoming_harvest_30days() {
        $producers = new SeedProducers();
        $producers_planted = $producers->seed_producers_planted();
        $coopIds = array();

        foreach ($producers_planted as $item) {
            $maturity = $item->maturity - 14; // 21 changed to 14
            $harvest_date = date('Y-m-d', strtotime($item->Date_planted . ' + ' . $maturity . ' days'));
            $days = date('Y-m-d', strtotime(date('Y-m-d') . ' + 30 days'));

            if ($harvest_date >= date('Y-m-d') && $harvest_date <= $days) {
                if (!in_array($item->coopId, $coopIds)) {
                    array_push($coopIds, $item->coopId);
                }
            }
        }

        $cooperatives = new SeedCooperatives();
        $table_data = array();

        foreach ($coopIds as $item2) {
            $coop = $cooperatives->seed_cooperative($item2);

            $data = array(
                'coopId' => $coop->coopId,
                'cooperative' => $coop->coopName,
                'province' => $coop->provDesc
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                        ->addColumn('actions', function($table_data) {
                            return "<button class='btn btn-info view_seed_growers' id='" . $table_data['coopId'] . "' title='View'><i class='fa fa-eye'></i> View</button>";
                        })
                        ->make(true);
    }

    public function harvest_seed_growers(Request $request) {
        $input = $request->all();

        $producers = new SeedProducers();
        $producers_planted = $producers->seed_producers_planted_filtered($input['coopId']);

        $cooperatives = new SeedCooperatives();
        $table_data = array();
        foreach ($producers_planted as $item) {
            $maturity = $item->maturity - 14; // 21 changed to 14
            $harvest_date = date('Y-m-d', strtotime($item->Date_planted . ' + ' . $maturity . ' days'));
            $days = date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $input['days'] . ' days'));

            if ($harvest_date >= date('Y-m-d') && $harvest_date <= $days) {
                $harvest_estimate = $item->Area_Planted_in_ha * 200;
                $availability_date = date('Y-m-d', strtotime($harvest_date . ' + ' . ' 60 days')); // 1 month changed to 60 days
                $data = array(
                    'name' => $item->Name,
                    'variety_planted' => $item->variety,
                    'harvest_estimate' => $harvest_estimate,
                    'harvesting_date' => $harvest_date,
                    'availability_date' => $availability_date
                );

                array_push($table_data, $data);
            }
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)->make(true);
    }

    public function seed_cooperatives() {
        $cooperatives = new SeedCooperatives();
        $cooperatives_planted = $cooperatives->seed_cooperatives_planted();

        $transplanting = new Transplant();
        $seeds = new Seeds();

        $table_data = array();

        foreach ($cooperatives_planted as $item) {
            // Get seed coop
            $cooperative = $cooperatives->seed_cooperative($item->coopId);

            // Get minimum and maximum transplating date
            $min_transplanting_date = $transplanting->transplanting_date($item->coopId, "ASC");
            $max_transplanting_date = $transplanting->transplanting_date($item->coopId, "DESC");

            // Get planted seeds
            $planted_seeds = $seeds->planted_seeds($item->coopId);
            $harvest_dates = array();

            foreach ($planted_seeds as $item2) {
                $maturity = $item2->maturity - 14; // 21 changed to 14
                $harvest_date = date('Y-m-d', strtotime($item2->Date_planted . ' + ' . $maturity . ' days'));
                array_push($harvest_dates, $harvest_date);
            }

            $min_harvest_date = min($harvest_dates);
            $max_harvest_date = max($harvest_dates);
            $min_availability_date = date('Y-m-d', strtotime($min_harvest_date . ' + 60 days')); // 1 month changed to 60 days
            $max_availability_date = date('Y-m-d', strtotime($max_harvest_date . ' + 60 days')); // 1 month changed to 60 days
            // Get total area planted
            $total_area_planted = $cooperatives->seed_cooperatives_area_planted($item->coopId);

            if ($min_transplanting_date == $max_transplanting_date) {
                $transplanting_date_range = $min_transplanting_date;
            } else {
                $transplanting_date_range = $min_transplanting_date->Date_planted . ' to ' . $max_transplanting_date->Date_planted;
            }

            if ($min_harvest_date == $max_harvest_date) {
                $harvesting_date_range = $min_harvest_date;
                $availability_date_range = $min_availability_date;
            } else {
                $harvesting_date_range = $min_harvest_date . ' to ' . $max_harvest_date;
                $availability_date_range = $min_availability_date . ' to ' . $max_availability_date;
            }

            $data = array(
                'coopId' => $item->coopId,
                'name' => $cooperative->coopName,
                'province' => $cooperative->provDesc,
                'area_planted' => $total_area_planted,
                'transplanting_date' => $transplanting_date_range,
                'harvesting_date' => $harvesting_date_range,
                'availability_date' => $availability_date_range
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return DataTables::of($table_data)
                        ->addColumn('actions', function($table_data) {
                            return "<button class='btn btn-info view_municipalities' id='" . $table_data['coopId'] . "' title='View'><i class='fa fa-eye'></i> View</button>";
                        })
                        ->make(true);
    }

    public function cooperative_municipalities(Request $request) {
        // Get municipalities
        $seed_cooperatives = new SeedCooperatives();
        $municipalities = $seed_cooperatives->municipalities($request->coopId);

        $transplanting = new Transplant();
        $seeds = new Seeds();

        $table_data = array();

        foreach ($municipalities as $mun) {
            // Get minimum and maximum transplating date
            $min_transplanting_date = $transplanting->transplanting_date_mun($request->coopId, $mun->cityId, "ASC");
            $max_transplanting_date = $transplanting->transplanting_date_mun($request->coopId, $mun->cityId, "DESC");

            $min_maturity = $min_transplanting_date->maturity - 14; // 21 changed to 14
            $max_maturity = $max_transplanting_date->maturity - 14; // 21 changed to 14
            $min_harvest_date = date('Y-m-d', strtotime($min_transplanting_date->Date_planted . ' + ' . $min_maturity . ' days'));
            $max_harvest_date = date('Y-m-d', strtotime($max_transplanting_date->Date_planted . ' + ' . $max_maturity . ' days'));
            $min_availability_date = date('Y-m-d', strtotime($min_harvest_date . ' + 60 days')); // 1 month changed to 60 days
            $max_availability_date = date('Y-m-d', strtotime($max_harvest_date . ' + 60 days')); // 1 month changed to 60 days

            if ($min_transplanting_date == $max_transplanting_date) {
                $transplanting_date_range = $min_transplanting_date;
            } else {
                $transplanting_date_range = $min_transplanting_date->Date_planted . ' to ' . $max_transplanting_date->Date_planted;
            }

            if ($min_harvest_date == $max_harvest_date) {
                $harvesting_date_range = $min_harvest_date;
                $availability_date_range = $min_availability_date;
            } else {
                $harvesting_date_range = $min_harvest_date . ' to ' . $max_harvest_date;
                $availability_date_range = $min_availability_date . ' to ' . $max_availability_date;
            }

            $harvest_estimate = $mun->area_planted * 200;

            $data = array(
                'name' => $mun->municipality_name,
                'variety_planted' => $mun->Seed_variety,
                'area_planted' => $mun->area_planted,
                'harvest_estimate' => $harvest_estimate,
                'harvesting_date' => $harvesting_date_range,
                'availability_date' => $availability_date_range
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)->make(true);
    }

    public function seed_growers() {
        $producers = new SeedProducers();
        $seed_growers = $producers->seed_growers();

        $table_data = array();

        foreach ($seed_growers as $item) {
            $data = array(
                'accreditation_number' => $item->Accreditation_Number,
                'name' => $item->Name,
                'province' => $item->province_name,
                'cooperative' => $item->coopName
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                        ->addColumn('actions', function($table_data) {
                            return "<button class='btn btn-info view_seed_grower' id='" . $table_data['accreditation_number'] . "' title='View'><i class='fa fa-eye'></i> View</button>";
                        })
                        ->make(true);
    }

    public function seed_grower_details(Request $request) {
        $producers = new SeedProducers();
        $seed_grower_details = $producers->seed_grower($request->accreditation_number);

        $table_data = array();

        $seeds = new Seeds();

        foreach ($seed_grower_details as $item) {
            $seed = $seeds->seed($item->Seed_variety);
            $maturity = $seed->maturity - 14; // 21 changed to 14
            $harvest_date = date('Y-m-d', strtotime($item->Date_planted . ' + ' . $maturity . ' days'));
            $availability_date = date('Y-m-d', strtotime($harvest_date . ' + 60 days')); // 1 month changed to 60 days

            $data = array(
                'variety_planted' => $item->Seed_variety,
                'area_planted' => $item->Area_Planted_in_ha,
                'date_planted' => date('Y-m-d', strtotime($item->Date_planted)),
                'harvest_estimate' => $item->Area_Planted_in_ha * 200,
                'harvesting_date' => $harvest_date,
                'availability_date' => $availability_date
            );

            array_push($table_data, $data);
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)->make(true);
    }

    public function seed_grower_profile(Request $request) {
        $seed_growers = new SeedGrowers();
        $profile = $seed_growers->profile($request->accreditation_number);

        echo json_encode($profile);
    }
	
	public function dashboard_delivery_schedule(Request $request){
        /*$confirmed_delivery = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->select('batchTicketNumber', 'region', 'province', 'municipality', 'dropOffPoint', 
                DB::raw('SUM(totalBagCount) as expected_bags'), 'deliveryDate')
            ->where('is_cancelled', 0)
            ->where('region', '!=', '')
            ->where('deliveryDate', '>=', date("-m-d-Y", strtotime($request->week_start)))
            ->where('deliveryDate', '<=', date("-m-d-Y", strtotime($request->week_end)))
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
            ->get();*/

        $confirmed_delivery = DB::select( DB::raw("SELECT batchTicketNumber, region, province, municipality, dropOffPoint, SUM(totalBagCount) as expected_bags, deliveryDate  
            FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery WHERE region != '' 
                AND DATE(deliveryDate) >= :week_start AND DATE(deliveryDate) <= :week_end 
                GROUP BY batchTicketNumber ORDER BY deliveryDate DESC"), array(

            'week_start' => date("Y-m-d", strtotime($request->week_start)),
            'week_end' => date("Y-m-d", strtotime($request->week_end)),
        ));
        
        //check if delivery is inspected
        $inspected_arr = array();
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('dateCreated', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actual_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->where('region', $row->region)
            ->groupBy('batchTicketNumber')
            ->first();

            //get delivery status
            $delivery_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                ->select('status')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->orderBy('deliveryStatusId', "desc")
                ->first();
            if($delivery_status->status == 0){
                $status_name = 'Pending';
            }
            else if($delivery_status->status == 1){
                $status_name = 'Passed';
            }
            else if($delivery_status->status == 2){
                $status_name = 'Rejected';
            }else if($delivery_status->status == 3){
                $status_name = 'In Transit';
            }else if($delivery_status ->status== 4){
                $status_name = 'Cancelled';
            }
            

            if(count($actual_delivery) > 0){
                $row_arr = array(
                    'province' => $row->province,
                    'municipality' => $row->municipality,
                    'dropOffPoint' => $row->dropOffPoint,
                    'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                    'actual_delivery_volume' => number_format($actual_delivery->actual_bags)." bag(s)",
                    'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                    'status' => $status_name
                );
                array_push($inspected_arr, $row_arr);
            }else{
                $row_arr = array(
                    'province' => $row->province,
                    'municipality' => $row->municipality,
                    'dropOffPoint' => $row->dropOffPoint,
                    'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                    'actual_delivery_volume' => "N/A",
                    'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                    'status' => $status_name
                );
                array_push($inspected_arr, $row_arr);
            }
        }

        $inspected_arr = collect($inspected_arr);
        return Datatables::of($inspected_arr)
        ->make(true);
    }

    public function dashboard_delivery_schedule_custome(Request $request){
        $dates = explode(" - ", $request->date_duration);

        if(Auth::user()->roles->first()->name == "da-icts"){
            $confirmed_delivery = DB::select( DB::raw("SELECT coopAccreditation,batchTicketNumber, region, province, municipality, dropOffPoint, SUM(totalBagCount) as expected_bags, deliveryDate  
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery WHERE region != '' 
                    AND DATE(deliveryDate) >= :week_start AND DATE(deliveryDate) <= :week_end AND region = :region_name AND isBuffer != '9'
                    GROUP BY batchTicketNumber ORDER BY deliveryDate DESC"), array(

                'week_start' => date("Y-m-d", strtotime($dates[0])),
                'week_end' => date("Y-m-d", strtotime($dates[1])),
                'region_name' => $request->region
            ));

        }else{
            $confirmed_delivery = DB::select( DB::raw("SELECT coopAccreditation,batchTicketNumber, region, province, municipality, dropOffPoint, SUM(totalBagCount) as expected_bags, deliveryDate  
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery WHERE region != '' 
                    AND DATE(deliveryDate) >= :week_start AND DATE(deliveryDate) <= :week_end AND region = :region_name AND isBuffer != '9'
                    GROUP BY batchTicketNumber ORDER BY deliveryDate DESC"), array(

                'week_start' => date("Y-m-d", strtotime($dates[0])),
                'week_end' => date("Y-m-d", strtotime($dates[1])),
                'region_name' => $request->region
            ));
        }
        
        //check if delivery is inspected
        $inspected_arr = array();
        $action = '';
        $hasDR = '';
        $viewParticulars = '';
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('dateCreated', 'qrStart','qrEnd', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actual_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->where('region', $row->region)
            ->groupBy('batchTicketNumber')
            ->first();

            $coop_name = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives")
                ->where("accreditation_no", $row->coopAccreditation)
                ->first();

            if(count($coop_name)>0){
                $coop_name = $coop_name->coopName;
            }else{
                $coop_name = "N/A";
            }

            $dr_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection')
            ->select('dr_number')
            ->where('batchTicketNumber', $row->batchTicketNumber)
            ->first();

            if(isset($dr_number))
            {
                $hasDR = 'Yes';
            }
            else{
                $hasDR = 'No';
            }

            $paymentStatus = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
            ->select('paymentStatus')
            ->where('batchTicketNumber', $row->batchTicketNumber)
            ->first();

            //get delivery status
            $delivery_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                ->select('status')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->orderBy('deliveryStatusId', "desc")
                ->first();
            if($delivery_status->status == 0){
                $status_name = 'Pending';
            }
            else if($delivery_status->status == 1){
                $status_name = 'Passed';
            }
            else if($delivery_status->status == 2){
                $status_name = 'Rejected';
            }else if($delivery_status->status == 3){
                $status_name = 'In Transit';
            }else if($delivery_status ->status== 4){
                $status_name = 'Cancelled';
            }

            $hasParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
            ->select('particulars')
            ->where('batchTicketNumber', $row->batchTicketNumber)
            ->first();

            $deliveryType = '';

            // dd($row->batchTicketNumber);
            // dd($actual_delivery->qrStart, $actual_delivery->qrEnd);
            if(count($actual_delivery) > 0){
                if(($actual_delivery->qrStart ===0 && $actual_delivery->qrEnd === 0) || ($actual_delivery->qrStart ==='0' && $actual_delivery->qrEnd === '0'))
                {
                    $deliveryType = 'reg';
                }
                else if(($actual_delivery->qrStart !==0 && $actual_delivery->qrEnd !==0) || ($actual_delivery->qrStart !=='0' && $actual_delivery->qrEnd !=='0')){
                    $deliveryType = 'bep';
                }
            }else{
                $deliveryType = 'N/A';
            }
            

            if ($status_name === 'Passed' && isset($dr_number->dr_number) && !isset($hasParticulars->particulars)) {
                $action = '<input type="checkbox" class="radioCoop" data-deliv="'.$row->batchTicketNumber.'" data-deltype="'.$deliveryType.'" data-coop="'.$row->coopAccreditation.'" data-coop="'.$row->coopAccreditation.'">';
                $viewParticulars = '';
            }
            else if ($status_name === 'Passed' && isset($dr_number->dr_number) && isset($hasParticulars->particulars)) {
                $action = '<input type="checkbox" class="radioCoop" disabled data-deliv="'.$row->batchTicketNumber.'">';
                $viewParticulars = "<button type='button' class='viewFMIS' data-toggle='modal' data-target='#show_particulars_modal' data-batch='".$row->batchTicketNumber."'>View FMIS Particulars</button>";
            }
            else {
                $action = '<input type="checkbox" class="radioCoop" disabled data-deliv="'.$row->batchTicketNumber.'">';
                $viewParticulars = "N/A";
            }

            if(count($actual_delivery) > 0){
                if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance"){
                    $row_arr = array(
                        'action' =>$action,
                        'coop_name' =>$coop_name,
                        'region' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'batchTicketNumber' => $row->batchTicketNumber,
                        'dropOffPoint' => $row->dropOffPoint,
                        'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                        'actual_delivery_volume' => number_format($actual_delivery->actual_bags)." bag(s)",
                        'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                        'status' => $status_name,
                        'paymentStatus' => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'For Receiving',
                        'deliveryReceipt' => $hasDR,
                        'particulars' => $viewParticulars
                    );
                }else{
                    $row_arr = array(
                        'coop_name' =>$coop_name,
                        'region' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'batchTicketNumber' => $row->batchTicketNumber,
                        'dropOffPoint' => $row->dropOffPoint,
                        'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                        'actual_delivery_volume' => number_format($actual_delivery->actual_bags)." bag(s)",
                        'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                        'status' => $status_name,
                        'paymentStatus' => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'For Receiving',
                        'deliveryReceipt' => $hasDR
                    );
                }
                array_push($inspected_arr, $row_arr);
            }else{
                if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->roles->first()->name == "rcef-finance"){
                    $row_arr = array(
                        'action' =>$action,
                        'coop_name' =>$coop_name,
                        'region' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'batchTicketNumber' => $row->batchTicketNumber,
                        'dropOffPoint' => $row->dropOffPoint,
                        'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                        'actual_delivery_volume' => "N/A",
                        'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                        'status' => $status_name,
                        'paymentStatus' => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'N/A',
                        'deliveryReceipt' => $hasDR,
                        'particulars' => 'N/A',
                    );
                }else{
                    $row_arr = array(
                        'coop_name' =>$coop_name,
                        'region' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'batchTicketNumber' => $row->batchTicketNumber,
                        'dropOffPoint' => $row->dropOffPoint,
                        'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                        'actual_delivery_volume' => "N/A",
                        'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                        'status' => $status_name,
                        'paymentStatus' => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'N/A',
                        'deliveryReceipt' => $hasDR
                    );
                }

                
                array_push($inspected_arr, $row_arr);
            }
        }

        $inspected_arr = collect($inspected_arr);
        return Datatables::of($inspected_arr)
        ->make(true);
    }

    public function dashboard_delivery_schedule_searchRegions(Request $request){
        $dates = explode(" - ", $request->date_duration);

        if(Auth::user()->roles->first()->name == "da-icts"){
            $confirmed_delivery = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery
                    JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection_mirror.tbl_delivery.region = rcep_delivery_inspection_mirror.lib_prv.regionName
                    WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
                    GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(

                'week_start' => date("Y-m-d", strtotime($dates[0])),
                'week_end' => date("Y-m-d", strtotime($dates[1])),
            ));

        }else{
            $confirmed_delivery = DB::select( DB::raw("SELECT deliveryId,tbl_delivery.region
                FROM ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery
                    JOIN ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv ON ".$GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery.region = ".$GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv.regionName
                    WHERE tbl_delivery.region != '' AND DATE(tbl_delivery.deliveryDate) >= :week_start AND DATE(tbl_delivery.deliveryDate) <= :week_end 
                    GROUP BY tbl_delivery.region ORDER BY lib_prv.region_sort ASC"), array(

                'week_start' => date("Y-m-d", strtotime($dates[0])),
                'week_end' => date("Y-m-d", strtotime($dates[1])),
            ));
            
        }

        if(count($confirmed_delivery) > 0){ 
            $region_list = array();

            foreach($confirmed_delivery as $row){
                array_push($region_list, $row->region);
            }
            $region_list = array_unique($region_list);
        
        }else{ 
            $region_list = "no_deliveries"; 
        };

        return $region_list;
    }

    public function particularsPreview(Request $request){

        $length = 10;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $particularsBatch = str_shuffle(Str::random($length, $characters));
        // dd($request->checkedVals);
        $deliveryArray = [];
        $dateArray = [];
        $iarArray = [];
        $DRdateArray = [];
        $sumTotalBags = 0;
        $deliveryType = '';
        $batchTickets = $request->checkedVals;
        foreach($batchTickets as $batch){
            $tbl_delivery_data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select(DB::raw('SUM(totalBagCount) as total_bags'), 'coopAccreditation','region', 'province', 'municipality', 'dropOffPoint','deliveryDate')
            ->where('batchTicketNumber', $batch)
            ->groupBy('batchTicketNumber')
            ->first();


            if ($tbl_delivery_data) {
                array_push($deliveryArray, $tbl_delivery_data);
                $sumTotalBags += $tbl_delivery_data->total_bags;
                array_push($dateArray, date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate)));
                // array_push($dateArray, $tbl_delivery_data->deliveryDate);

                $IAR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.iar_print_logs')
                ->select('iarCode')
                ->where('batchTicketNumber', $batch)
                ->first();
                array_push($iarArray, $IAR);

                $dr_number = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_inspection')
                ->select('dr_number')
                ->where('batchTicketNumber', $batch)
                ->first();

                $paymentStatus = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
                ->select('paymentStatus')
                ->where('batchTicketNumber', $batch)
                ->first();

                $deliveryTypes = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select('qrStart','qrEnd')
                ->where('batchTicketNumber', $batch)
                ->first();

                // $concatenatedDRdate = $dr_number->dr_number . ' ' . date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate));
                // array_push($DRdateArray, $concatenatedDRdate);


                if(($deliveryTypes->qrStart ==='0' && $deliveryTypes->qrEnd === '0') || ($deliveryTypes->qrStart ===0 && $deliveryTypes->qrEnd === 0))
                {
                    $deliveryType = 'Regular';
                }
                else if(($deliveryTypes->qrStart !=='0' && $deliveryTypes->qrEnd !== '0') || ($deliveryTypes->qrStart !==0 && $deliveryTypes->qrEnd !== 0)){
                    $deliveryType = 'Binhi e-Padala';
                }
                    
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
                ->insert([
                'coopAccreditation' => $tbl_delivery_data->coopAccreditation,
                'region' => $tbl_delivery_data->region,
                'province' => $tbl_delivery_data->province,
                'municipality' => $tbl_delivery_data->municipality,
                'batchTicketNumber' => $batch,
                'dropOffPoint' => $tbl_delivery_data->dropOffPoint,
                'delivery_volume' => $tbl_delivery_data->total_bags,
                'delivery_date' => date("m/d/Y", strtotime($tbl_delivery_data->deliveryDate)),
                'iar_number' => $IAR->iarCode,
                'paymentStatus' => isset($paymentStatus->paymentStatus) ? $paymentStatus->paymentStatus : 'For Receiving',
                'deliveryReceipt' => $dr_number->dr_number,
                'deliveryType' => $deliveryType,
                'particularsBatch' => $particularsBatch,
                ]);
            }
        }
        // dd($DRdateArray);
        //get unique IARs
        $jsonIAR = json_encode($iarArray);
        $IARs = json_decode($jsonIAR);
        $uniqueIAR = [];

        foreach ($IARs as $item) {
            $value = $item->iarCode;

            if (!in_array($value, $uniqueIAR)) {
                $uniqueIAR[] = $value;
            }
        }
        $outputIAR = implode(', ', $uniqueIAR);

        //get unique dates
        $jsonDates = json_encode($dateArray);
        $dates = json_decode($jsonDates);
        $uniqueDates = [];

        foreach ($dates as $item) {
            $value = $item;

            if (!in_array($value, $uniqueDates)) {
                $uniqueDates[] = $value;
            }
        }

        // Convert date strings to timestamps
        $dateTimestamps = array_map(function($date) {
            return strtotime($date);
        }, $uniqueDates);

        // Sort the timestamps in ascending order
        sort($dateTimestamps);

        // Create a sorted array of dates based on the sorted timestamps
        $sortedDates = array_map(function($timestamp) {
            return date("m/d/Y", $timestamp);
        }, $dateTimestamps);

        // dd($sortedDates);

        foreach($sortedDates as $date){
            $DRs = [];
            $dateDR = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
            ->select('deliveryReceipt', 'delivery_date')
            ->where('delivery_date', '=', $date)
            ->get();

            // dd($dateDR[0]->deliveryReceipt);
            foreach($dateDR as $dr){
                array_push($DRs,$dr->deliveryReceipt);
            }
            $outputDR = implode(', ', $DRs);
            $concatDR = $outputDR.' '.$date;
            array_push($DRdateArray, $concatDR);
        }

        $outputDates = implode(', ', $DRdateArray);


        $cost = $sumTotalBags * 760;
        $particulars = "Payment for ".number_format($sumTotalBags)." bags of CS for 2024DS to ".$tbl_delivery_data->province." as per DR# ".$outputDates." less 1% retention fee | Attached IAR #: ".$outputIAR;

        foreach($batchTickets as $batch){
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
                ->where('batchTicketNumber','=',$batch)
                ->update([
                'particulars' => $particulars,
                ]);
        }

        return json_encode($particulars);
    }
    

    public function viewParticulars (Request $request){
        $viewParticulars = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_particulars')
            ->select('particulars')
            ->where('batchTicketNumber', $request->batch)
            ->first();

            return json_encode($viewParticulars->particulars);
    }


    public function exportData(Request $request)
    {
        $getRegions = \DB::connection('delivery_inspection_db')
            ->table('tbl_actual_delivery')
            ->distinct()
            ->pluck('region');
        // dd($getRegions);
        $exportData = [];
    
        foreach ($getRegions as $region) {
            $getActual = \DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->where('region', $region)
                // ->where('dateCreated', '>=', '2023-01-01')
                // ->where('dateCreated', '<=', '2023-08-31')
                ->selectRaw('SUM(totalBagCount) as total_bag_count')
                ->get();
            
            // dd($region,$getActual);
            $getTarget = \DB::connection('delivery_inspection_db')
                ->table('tbl_delivery_sum')
                ->where('region', $region)
                ->where('targetMonthFrom', '>=', '2023-09-01')
                ->where('targetMonthTo', '<=', '2023-12-31')
                ->selectRaw('SUM(targetVolume) as total_target_volume')
                ->get();
            // dd($region,$getTarget);
            $actualBagCount = 0;
            if (isset($getActual[0]->total_bag_count)) {
                $actualBagCount = $getActual[0]->total_bag_count;
            }
    
            $targetVolume = 0;
            if (isset($getTarget[0]->total_target_volume)) {
                $targetVolume = $getTarget[0]->total_target_volume;
            }
    
            // Avoid division by zero
            $percentage = ($targetVolume != 0) ? ($actualBagCount / $targetVolume) * 100 : 0;
    
            array_push($exportData, array(
                "Region" => $region,
                "Actual Delivery" => number_format($actualBagCount),
                "Target Delivery" => number_format($targetVolume),
                "Percentage" => number_format($percentage,2 )
            ));
        }

        // dd($exportData);
    
        // $filename = 'All Regions Data';
        // return Excel::create($filename, function($excel) use ($exportData) {
        //     $excel->sheet('All Regions', function($sheet) use ($exportData) {
        //         foreach ($exportData as &$data) {
        //             $data['Percentage'] = number_format($data['Percentage'], 2) . '%'; // Format the percentage
        //         }

        //         $sheet->fromArray($exportData);
        //     });
        // })->setActiveSheetIndex(0)->download('xlsx');

        $filename = 'All Regions Data';
        return Excel::create($filename, function($excel) use ($exportData) {
            $excel->sheet('All Regions', function($sheet) use ($exportData) {
               
                foreach ($exportData as &$data) {
                    $data['Percentage'] = number_format($data['Percentage'], 2) . '%'; // Format the percentage
                }

                $sheet->fromArray($exportData);

                
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#00b300');
                    $row->setAlignment('center');
                });
                
             
                $sheet->getStyle('A2:D' . (count($exportData) + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $sheet->setWidth([
                    'A' => 20,
                    'B' => 20,
                    'C' => 20,
                    'D' => 20,
                ]);
                $sheet->setHeight([
                    1 => 25,
                    'A' => 20,
                    'B' => 20,
                    'C' => 20,
                    'D' => 20,
                ]);
                
                $sheet->setBorder('A1:E'. (count($exportData) + 1), 'thin', 'thin', 'thin', 'thin'); 
            });
        })->setActiveSheetIndex(0)->download('xlsx');


    }

}

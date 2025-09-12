<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;
use Auth;
use App\Transfer;
use Illuminate\Support\Facades\Hash;
use Excel;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
class APIController extends Controller
{

	// public function export_api($aa){
		
	// 	$arr = array("ds2023","ws2022","ds2022","ws2021","ds2021");
	// 	$ex = array();
	// 	foreach($arr as $season){
	// 		$ws_data = file_get_contents("https://rcef-checker.philrice.gov.ph/api/sg_list/de9c64b389a3916e91419896c578baf5/".$season."/all");
		
	// 		$ws_data = json_decode($ws_data, true);
	// 		foreach($ws_data as $key => $data){
	// 			if($key == "data"){
	// 				$ex[$season] = $data;
	// 			}
	// 	}
	// }

	// return Excel::create("SG_LIST_".$season, function($excel) use ($ex) {
	// 	foreach($ex as $key=> $data){
	// 		$excel->sheet($key, function($sheet) use ($data) {
	// 			$sheet->fromArray($data);
	// 		}); 

	// 	}

		
	// })->download('xlsx');


	// }




	public function seed_grower_list($api_key, $coop_accre){

		if($api_key == "de9c64b389a3916e91419896c578baf5"){
			if($coop_accre == "all"){
				$coop_accre = "%";
			}
			
			$data_arr = array();

			$coop_list = DB::connection("seed_coop_db")->table("tbl_cooperatives")
				->where("coopName", "LIKE", "%".$coop_accre."%")
				->orderBy("regionId")
				->get();

				foreach($coop_list as $coop){
				$sg_list = DB::connection("delivery_inspection_db")->table("tbl_rla_details")
					->where("coopAccreditation", $coop->accreditation_no)
					->where("is_rejected", 0)
					->groupBy("sg_id")
					->get();

					foreach($sg_list as $sg){
						$sg_info = DB::connection("delivery_inspection_db")->table("tbl_seed_grower")
							->where("sg_id", $sg->sg_id)
							->first();
						if($sg_info != null){
							array_push($data_arr, array(
								"coop_name" => $coop->coopName,
								"coop_accre" => $coop->accreditation_no,
								"coop_moa" => $coop->current_moa,
								"cropping_year" => "2023",
								"season" => "1",
								"sg_name" => $sg_info->full_name
							));



						}
					}

				}
				return array(
					"status" => "1",
					"data" => $data_arr,
					"msg" => "success"
				);
				


		}else{
			return array(
				"status" => "0",
				"data" => "-",
				"msg" => "wrong api"
			);
		}


	}



	public function fetchFarmerInfo($api,$prv){
			if($api == "hgf+ja3lCfK"){
				$prv = $GLOBALS['season_prefix']."prv_".$prv;
				// $data = DB::table($prv)
				return "Preparation";

			}else{
				return "No Priviledge";
			}


	}


	  //API FOR CONSOLIDATED LOGIN RJ 03112022
	  public function checkCredentials(Request $request){
		$user_check = DB::table($GLOBALS['season_prefix']."sdms_db_dev.users")
			->where("username", $request->username)
			// ->where("isDeleted", 0)
			->first();
	
			if(count($user_check) > 0){
				if($user_check->isDeleted == "1"){
					return "deleted";
				}else{
					if (Hash::check($request->password, $user_check->password)) {
						return csrf_token();
					}else{
						return "false";
					}

				}
			}else{
				return "false";
			}
	}

	public function finance(){
		$data = array();
	
		 $tbl_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')->select('tbl_delivery.batchTicketNumber',DB::raw("SUM(tbl_delivery.totalBagCount) as total_bags_confirmed"))->groupBy('batchTicketNumber')->get();
		DB::beginTransaction();
        try {
		 foreach ($tbl_delivery as  $tmpdelivery) {

			$tbl_actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')		
			->select('tbl_actual_delivery.is_sync','tbl_actual_delivery.batchTicketNumber','tbl_actual_delivery.region','tbl_actual_delivery.province','tbl_actual_delivery.municipality','tbl_actual_delivery.dropOffPoint','tbl_actual_delivery.moa_number as moa_number_data',DB::raw("SUM(tbl_actual_delivery.has_rla) as onHandRLA"),DB::raw("SUM(tbl_actual_delivery.totalBagCount) as total_bags_inspected"), DB::raw("count(tbl_actual_delivery.seedTag) as totalSeedTag"))
			->where('transferCategory', '!=', 'P')
			->where('is_sync', 0)			
			->where('batchTicketNumber', $tmpdelivery->batchTicketNumber)
			->groupBy('tbl_actual_delivery.batchTicketNumber')
			->get();

		
			foreach ($tbl_actual_delivery as $value) { 				
				if($value->is_sync == 0){
					DB::table($GLOBALS['season_prefix'].'rcep_payments.seed_deliveries')->where('batch_number', $value->batchTicketNumber)->delete();
				}

				$status="";
				if($value->onHandRLA == $value->totalSeedTag){
					$status="1";
				}else if($value->onHandRLA < $value->totalSeedTag && $value->onHandRLA>0){
					$status ="2";
				}else if($value->onHandRLA==0){
					$status ="3";
				}
	

				DB::table($GLOBALS['season_prefix'].'rcep_payments.seed_deliveries')->insert(
					[
						"batch_number"=> $value->batchTicketNumber,
						"region"=> $value->region,
						"province"=> $value->province,
						"municipality"=> $value->municipality,
						"dop"=>$value->dropOffPoint,
						"total_bags_confirmed"=>$tmpdelivery->total_bags_confirmed,
						"total_bags_inspected"=> $value->total_bags_inspected,
						"moa_number"=> $value->moa_number_data,
						"total_rlaFlag_count"=> $value->onHandRLA,
						"rla_status"=>$status,
						"for_assesment_flag"=> 3,
						"for_dv_processing_flag"=> 0,
					 
					 ]
				);
	
				
				/* array_push($data, array(
					"batch_number"=> $value->batchTicketNumber,
					"region"=> $value->region,
					"province"=> $value->province,
					"municipality"=> $value->municipality,
					"dop"=>$value->dropOffPoint,
					"total_bags_confirmed"=>   $tmpdelivery->total_bags_confirmed,
					"total_bags_inspected"=> $value->total_bags_inspected,
					"moa_number"=> $value->moa_number_data,
					"totalSeedTag"=> $value->totalSeedTag,
					"total_rlaFlag_count"=> $value->onHandRLA,
					"rla_status"=>$status,
					"for_assesment_flag"=> 3,
					"for_dv_processing_flag"=> 0
					)
				); */
				
				DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery")
				->where('batchTicketNumber', $value->batchTicketNumber)
				->update(['is_sync' => 1]);
			}
		

		 }

		 DB::commit();
		 return/* $data */ "success";
          } catch (Exception $e) {
            DB::rollback();
             return response()->json([
                'message'=>$e->getMessage()
            ],500);
        }
		
	
	}


	public function receive_moet_data(Request $request){
		$farmer_tbl_array = $request["farmer_tbl_array"];
		echo print_r($farmer_tbl_array);
	}

	public function commitment_member($api){
		if($api =="NTNkMDRhODJkOTZ"){
			$return_array = array();
			$memberList = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_member")
				->select("tbl_commitment_member.member_id","tbl_commitment_member.first_name","tbl_commitment_member.middle_name","tbl_commitment_member.last_name","tbl_commitment_member.accreditation_number as Member_Accreditation","tbl_commitment_member.allocated_area","tbl_commitment_member.commitment_variety", "tbl_cooperatives.coopName", "tbl_commitment_member.coop_accreditation_number")
				->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_commitment_member.coop_accreditation_number", "=", "tbl_cooperatives.accreditation_no")
				->get();



				foreach ($memberList as $key => $value) {
				
					$area = $value->allocated_area;
                    if($value->allocated_area != floor($value->allocated_area)) {
                                    $dec =  $value->allocated_area - floor($value->allocated_area); 
                                    if($dec <= 0.5 ){
                                        $area = floor($value->allocated_area) + 0.5;
                                    }else{
                                        $area = floor($value->allocated_area) + 1;
                                    }
                                     // dd($area);
                                }
                        $bags = $area * 200;
                        $kgs = $area * 40;

                       array_push($return_array,array(
                       	"member_id" => $value->member_id,
                       	"first_name" => $value->first_name,
                       	"middle_name" => $value->middle_name,
                       	"last_name" => $value->last_name,
                       	"Member_Accreditation" => $value->Member_Accreditation,
                       	"allocated_area" => $value->allocated_area,
                       	//"allocated_bags" => $bags,
                       	"allocated_quantity" => $kgs ,
                       	"commitment_variety"=> $value->commitment_variety,
                       	"coopName" => $value->coopName,
                       	"coop_accreditation_number" => $value->coop_accreditation_number,
                       ));
				}




			return json_encode($return_array);


		}else{
			return json_encode("You do not have the access privilege for this API");
		}
	}

	public function api_buffer_login(Request $request){
        
        try {
            $user = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
             ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'username', 'email', 'password', 'sex', 'region', 'province', 'municipality', 'agencyId', 'stationId', 'position', 'designation', 'name as uRole', 'coopAccreditation')
            ->join('users_coop', 'users.userId', '=', 'users_coop.userId', 'left')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->join('roles', 'role_user.roleId', '=', 'roles.roleId')
            ->where('username', $request->username)
            ->where('roles.name', 'buffer-inspector')
            ->where('users.isDeleted', '0')
            ->where('users.province', '!=', "")
           // ->orWhere('username', $request->username)
           // ->where('roles.name', 'data-officer')
           // ->where('users.isDeleted', '0')
           // ->where('users.province', '!=', "")
            ->get();

            $return_array = array();

            if(count($user) > 0){

            	//dd($user);

                if (Hash::check($request->password, $user[0]->password)) {

                	$regionProvince = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                		->where('prv', 'like', $user[0]->province.'%')
                		->first();
                	if(count($regionProvince)>0){
                		$user[0]->regionName = $regionProvince->regionName;
                		$user[0]->provinceName = $regionProvince->province;
                	}else{
                		$user[0]->regionName = null;
                		$user[0]->provinceName = null;
                	}

                				array_push($return_array, array(
			                		"status" => 1,
			                		"message" => "Success",
			                		"data" => $user[0],
			                	));
			                    return json_encode($return_array);
                
                }else{
                	array_push($return_array, array(
                		"status" => 0,
                		"message" => "Invalid Credentials",
                		"data" => array(),
                	));

                    return json_encode($return_array);
                }
            }else{
                array_push($return_array, array(
                		"status" => 0,
                		"message" => "Invalid User",
                		"data" => array(),
                	));

                return json_encode($return_array);
            }
            
        } catch (\Illuminate\Database\QueryException $ex) {
            return json_encode($ex);
        }

    }



    public function getBufferCoopBatchList($api_key,$username){
		// dd($GLOBALS["next_season_prefix"]);
		//dd($api_key);
		//dd(Auth::user()->province);
		$main_return = array();
		if($api_key == "NTNkMDRhODJkOTc"){
		
                	$checkIfTaggedAsInspector = DB::connection('ls_inspection_db')->table('tbl_inspection_for_breakdown as i')
				
                			->select("i.username","i.batchTicketNumber","c.*")
                			->join($GLOBALS["last_season_prefix"]."rcep_seed_cooperatives.tbl_cooperatives as c", "i.coopId", "=", "c.coopId")
                			->where("i.username", $username)
                			->where("is_tagged", 1)
                			->get();

                			// dd( $checkIfTaggedAsInspector);
                	if(count($checkIfTaggedAsInspector)>0){
                			$return_array = array();
                			foreach ($checkIfTaggedAsInspector as $batchTicket) {
          						$return_array_data = array();
								$dopWithData = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
								->where('batchTicketNumber', $batchTicket->batchTicketNumber)
								->where('totalBagCount', '>', 0)
								->groupBy("seedTag")
								->get();


									if(count($dopWithData)>0){
										foreach ($dopWithData as $actualData) {
											$isDlOther = DB::connection('ls_inspection_db')->table('tbl_breakdown_download')
												->where('batchTicketNumber', $actualData->batchTicketNumber)
												->where('seedTag', $actualData->seedTag)
												->where('downloaded_by', '!=', $username)
												->where('is_cleared', '==', 0)
												->first();

											if(count($isDlOther)<=0){
												$checkIfAlreadyBreak =   DB::connection('delivery_inspection_db')->table('tbl_actual_delivery_breakdown')
															->where('batchTicketNumber', $actualData->batchTicketNumber)
															->where('seedTag', $actualData->seedTag)
															->where('is_breakdown', 1)
															->first();
												if(count($checkIfAlreadyBreak)<=0){
														$totalBagCount=DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
															->where("batchTicketNumber", $actualData->batchTicketNumber)
															->where("seedTag", $actualData->seedTag)
															->sum("totalBagCount");

														$lablot =  explode("/", $actualData->seedTag);
														$rlaCert = DB::connection('ls_inspection_db')->table("tbl_rla_details")
															->where("labNo", $lablot[0])
															->where("lotNo", $lablot[1])
															->first();
															if(count($rlaCert)>0){
																$certificationDate = $rlaCert->certificationDate;	
															}else{
																$certificationDate = null;	
															}
														array_push($return_array_data, array(
															"seedVariety" => $actualData->seedVariety,
															"seedTag" => $actualData->seedTag,
															"totalBagCount" => $totalBagCount,
															//"prv_dropoff_id" => $actualData->prv_dropoff_id,
															"certificationDate" => $certificationDate
														));
													}	
												}
											
																$region = $actualData->region;
																$province = $actualData->province;
																$municipality = $actualData->municipality;
																$dropOffPoint = $actualData->dropOffPoint;
																$prv_dropoff_id = $actualData->prv_dropoff_id;
											}


												if(count($return_array_data)>0){
													array_push($return_array, array(
														"coop_name" => $batchTicket->coopName,
														"batchTicketNumber" => $batchTicket->batchTicketNumber,
														"region" => $region,
														"province"=> $province,
														"municipality" => $municipality,
														"dropOffPoint" => $dropOffPoint,
														"prv_dropoff_id" => $prv_dropoff_id,
														"data_batch" => $return_array_data
														)
													);		
												}

																
										}	

          							}
								
										if(count($return_array)>0){
											
												array_push($main_return, array(
											 		"status" => 1,
											 		"message" => "Success",
											 		"data" => $return_array
													 	));
											 	return json_encode($main_return);
										}else{
											array_push($main_return, array(
											 		"status" => 0,
											 		"message" => "No Buffer List",
											 		"data" => array()
											 	));

										 	return json_encode($main_return);
										}
                	}
					else{
						array_push($main_return, array(
				 		"status" => 0,
				 		"message" => "No Tagged Inspection",
				 		"data" => array()
				 	));
				 	return json_encode($main_return);
					}
		}else{
				array_push($main_return, array(
			 		"status" => 0,
			 		"message" => "You do not have the access privilege for this API",
			 		"data" => array()
			 	));
			 	return json_encode($main_return);
		}
	}

	public function InsertBreakdown(Request $request){
		//dd($request->all()); 
		// NTNkMDRhODJkOTc
		//  dd($request->all());
		// NTNkMDRhODJkOTc
		if($request->api_key == "NTNkMDRhODJkOTc"){
			$batchTicketNumber = $request->batchTicketNumber;
			$username = $request->username;
			$data = $request->data;
		
			DB::beginTransaction();
			try {	
				foreach ($data as $breakdownList) {
					$tbl_delivery = DB::connection("ls_inspection_db")->table("tbl_delivery")
							->select("coopAccreditation")
							->where("batchTicketNumber", $batchTicketNumber)
							->where("seedTag", $breakdownList["seedTag"])
							->first();

					if(count($tbl_delivery)>0){
						$coop_accre = $tbl_delivery->coopAccreditation;
					}else{


						if($breakdownList["category"]=="R" || $breakdownList["category"]=="G" ){

							$coop_accre = "--";

						}else{

							$tbl_actual_delivery_checker = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
						
							->where("batchTicketNumber", $batchTicketNumber)
							->where("seedTag", $breakdownList["seedTag"])
							->first();

							if(count($tbl_actual_delivery_checker)>0){
								$coop_accre = "--";
							}else{
								DB::rollback();
								$main_return = array(
									   "status" => 0,
									   "message" => "Cannot Find Previous Season Info",
									   "data" => ""
									   );
								return json_encode($main_return);
							}

						

						}

					
					}

					if($breakdownList["category"]=="G"){
						//INSERT TO current tbl_actual_breakdown -> Copy for Original Data
						$origData = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
							->where("batchTicketNumber", $batchTicketNumber)
							->where("seedTag", $breakdownList["seedTag"])
							->first();
						if(count($origData)>0){
							$checkClone = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
								->where("actualDeliveryId", $origData->actualDeliveryId)
								->first();

								if(count($checkClone)<=0){
									//CLONE ACTUAL FOR FUTURE REF
									DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
									->insert([
										"actualDeliveryId" => $origData->actualDeliveryId,
										"batchTicketNumber" =>$origData->batchTicketNumber,
										"region" =>$origData->region,
										"province" =>$origData->province,
										"municipality" =>$origData->municipality,
										"dropOffPoint" =>$origData->dropOffPoint,
										"seedVariety" =>$origData->seedVariety,
										"totalBagCount" =>$origData->totalBagCount,
										"dateCreated" =>$origData->dateCreated,
										"send" =>$origData->send,
										"seedTag" =>$origData->seedTag,
										"prv_dropoff_id" =>$origData->prv_dropoff_id,
										"prv" =>$origData->prv,
										"moa_number" =>$origData->moa_number,
										"app_version" =>$origData->app_version,
										"batchSeries" =>$origData->batchSeries,
										"remarks" =>$origData->remarks,
										"isRejected" =>$origData->isRejected,
										"is_transferred" =>$origData->is_transferred,
										"has_rla" =>$origData->has_rla,
										"is_breakdown" =>1,
										"category" =>$breakdownList["category"]
									]);
									//INSERT MAIN BREAKDOWN
									$is_palleted = $breakdownList["palleted"];
									$is_good_stocking = $breakdownList["stockings"];
									$is_good_wh = $breakdownList["warehouse"];
									$wh_pest = $breakdownList["pest"];
									$wh_temperature = $breakdownList["temp"];
									$wh_roofing = $breakdownList["roofing"];
									$remarks = $breakdownList["remarks"];
									
									if($is_palleted == "")$is_palleted=0;
									if($is_good_stocking == "")$is_good_stocking=0;
									if($is_good_wh == "")$is_good_wh=0;
									if($wh_pest == "")$wh_pest=0;
									if($wh_temperature == "")$wh_temperature=0;
									if($wh_roofing == "")$wh_roofing=0;
									if($remarks =="")$remarks="";
									
									DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
									->insert([
										"batchTicketNumber" => $batchTicketNumber,
										"region" => $origData->region,
										"province" => $origData->province,
										"municipality" => $origData->municipality,
										"seedTag" => $breakdownList["seedTag"],
										"category" => $breakdownList["category"],
										"seedsAge" => $breakdownList["seedsAge"],
										"is_palleted" => $is_palleted,
										"is_good_stocking" => $is_good_stocking,
										"is_good_wh" => $is_good_wh,
										"wh_pest" => $wh_pest,
										"wh_temperature" => $wh_temperature,
										"wh_roofing" => $wh_roofing,
										"remarks" => $remarks,
										"inspector_username" => $username,
										"date_created" => $breakdownList["date_created"]
									]);

									
									DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
										->where('actualDeliveryId', $origData->actualDeliveryId)
										->update(['is_hold' => 0]);

								}else{
									/*
									DB::rollback();
									$main_return = array(
											"status" => 0,
											"message" => "Batch and SeedTag Already Exist",
											"data" => ""
											);
									 return json_encode($main_return); */
								}
						}else{
							DB::rollback();
							$main_return = array(
									"status" => 0,
									"message" => "No Data on Last Season",
									"data" => ""
									);
							 return json_encode($main_return);
						}
					}elseif($breakdownList["category"]=="T"){
						//INSERT TO current tbl_actual_breakdown -> Copy for Original Data
						$origData = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
							->where("batchTicketNumber", $batchTicketNumber)
							->where("seedTag", $breakdownList["seedTag"])
							->first();
						if(count($origData)>0){
							$checkClone = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
								->where("actualDeliveryId", $origData->actualDeliveryId)
								->first();

								if(count($checkClone)<=0){
									//CLONE ACTUAL FOR FUTURE REF
									DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
									->insert([
										"actualDeliveryId" => $origData->actualDeliveryId,
										"batchTicketNumber" =>$origData->batchTicketNumber,
										"region" =>$origData->region,
										"province" =>$origData->province,
										"municipality" =>$origData->municipality,
										"dropOffPoint" =>$origData->dropOffPoint,
										"seedVariety" =>$origData->seedVariety,
										"totalBagCount" =>$origData->totalBagCount,
										"dateCreated" =>$origData->dateCreated,
										"send" =>$origData->send,
										"seedTag" =>$origData->seedTag,
										"prv_dropoff_id" =>$origData->prv_dropoff_id,
										"prv" =>$origData->prv,
										"moa_number" =>$origData->moa_number,
										"app_version" =>$origData->app_version,
										"batchSeries" =>$origData->batchSeries,
										"remarks" =>$origData->remarks,
										"isRejected" =>$origData->isRejected,
										"is_transferred" =>$origData->is_transferred,
										"has_rla" =>$origData->has_rla,
										"is_breakdown" =>1,
										"category" =>$breakdownList["category"]
									]);
									//INSERT MAIN BREAKDOWN
									$is_palleted = $breakdownList["palleted"];
									$is_good_stocking = $breakdownList["stockings"];
									$is_good_wh = $breakdownList["warehouse"];
									$wh_pest = $breakdownList["pest"];
									$wh_temperature = $breakdownList["temp"];
									$wh_roofing = $breakdownList["roofing"];
									$remarks = $breakdownList["remarks"];
									
									if($is_palleted == "")$is_palleted=0;
									if($is_good_stocking == "")$is_good_stocking=0;
									if($is_good_wh == "")$is_good_wh=0;
									if($wh_pest == "")$wh_pest=0;
									if($wh_temperature == "")$wh_temperature=0;
									if($wh_roofing == "")$wh_roofing=0;
									if($remarks =="")$remarks="";
									
									DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
									->insert([
										"batchTicketNumber" => $batchTicketNumber,
										"region" => $origData->region,
										"province" => $origData->province,
										"municipality" => $origData->municipality,
										"seedTag" => $breakdownList["seedTag"],
										"category" => $breakdownList["category"],
										"seedsAge" => $breakdownList["seedsAge"],
										"is_palleted" => $is_palleted,
										"is_good_stocking" => $is_good_stocking,
										"is_good_wh" => $is_good_wh,
										"wh_pest" => $wh_pest,
										"wh_temperature" => $wh_temperature,
										"wh_roofing" => $wh_roofing,
										"remarks" => $remarks,
										"inspector_username" => $username,
										"date_created" => $breakdownList["date_created"]
									]);
									
									//HOLD SEEDTAG
									DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
										->where('actualDeliveryId', $origData->actualDeliveryId)
										->update(['is_hold' => 1]);

									//WITH THE SAME SEEDTAG
									$theSameSeedTag = DB::connection("ls_inspection_db")->table("tbl_delivery")
										->where("coopAccreditation", $coop_accre)
										->where("seedTag", $breakdownList["seedTag"])
										->where("batchTicketNumber", "!=", $batchTicketNumber)
										->get();

									if(count($theSameSeedTag)>0){
										foreach ($theSameSeedTag as $sameSeedTagdelivery) {
											//CLONE ACTUAL FOR FUTURE REF
										$sameSeedTag = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
											->where("batchTicketNumber", $sameSeedTagdelivery->batchTicketNumber)
											->where("seedTag", $sameSeedTagdelivery->seedTag)
											->first();
										if(count($sameSeedTag)>0){
										/*
											DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
											->insert([
												"actualDeliveryId" => $sameSeedTag->actualDeliveryId,
												"batchTicketNumber" =>$sameSeedTag->batchTicketNumber,
												"region" =>$sameSeedTag->region,
												"province" =>$sameSeedTag->province,
												"municipality" =>$sameSeedTag->municipality,
												"dropOffPoint" =>$sameSeedTag->dropOffPoint,
												"seedVariety" =>$sameSeedTag->seedVariety,
												"totalBagCount" =>$sameSeedTag->totalBagCount,
												"dateCreated" =>$sameSeedTag->dateCreated,
												"send" =>$sameSeedTag->send,
												"seedTag" =>$sameSeedTag->seedTag,
												"prv_dropoff_id" =>$sameSeedTag->prv_dropoff_id,
												"prv" =>$sameSeedTag->prv,
												"moa_number" =>$sameSeedTag->moa_number,
												"app_version" =>$sameSeedTag->app_version,
												"batchSeries" =>$sameSeedTag->batchSeries,
												"remarks" =>$sameSeedTag->remarks,
												"isRejected" =>$sameSeedTag->isRejected,
												"is_transferred" =>$sameSeedTag->is_transferred,
												"has_rla" =>$sameSeedTag->has_rla,
												"is_breakdown" =>1,
												"category" =>$breakdownList["category"]
											]);
											//ADD TBL BREAKDOWN MAIN
											DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
											->insert([
												"batchTicketNumber" => $sameSeedTag->batchTicketNumber,
												"region" => $sameSeedTag->region,
												"province" => $sameSeedTag->province,
												"municipality" => $sameSeedTag->municipality,
												"seedTag" => $breakdownList["seedTag"],
												"category" => $breakdownList["category"],
												"seedsAge" => $breakdownList["seedsAge"],
												"is_palleted" => $is_palleted,
												"is_good_stocking" => $is_good_stocking,
												"is_good_wh" => $is_good_wh,
												"wh_pest" => $wh_pest,
												"wh_temperature" => $wh_temperature,
												"wh_roofing" => $wh_roofing,
												"remarks" => "DUE TO BATCH :".$batchTicketNumber,
												"inspector_username" => $username,
												"date_created" => $breakdownList["date_created"]
											]);
											*/


											//HOLD SEEDTAG
											DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
												->where('actualDeliveryId', $sameSeedTag->actualDeliveryId)
												->update(['is_hold' => 1]);
										}
									}




									}	
								}else{
									/*
									DB::rollback();
									$main_return = array(
											"status" => 0,
											"message" => "Batch and SeedTag Already Exist",
											"data" => ""
											);
									 return json_encode($main_return); */
								}
						}else{
							DB::rollback();
							$main_return = array(
									"status" => 0,
									"message" => "No Data on Last Season",
									"data" => ""
									);
							 return json_encode($main_return);
						}
					}elseif($breakdownList["category"] =="D"){
						//INSERT TO current tbl_actual_breakdown -> Copy for Original Data
						$origData = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
							->where("batchTicketNumber", $batchTicketNumber)
							->where("seedTag", $breakdownList["seedTag"])
							->first();
						if(count($origData)>0){
							$rejected_bags = $breakdownList["num_bags_rejected"];
							$remaining_bags = $origData->totalBagCount - $rejected_bags;
							$checkClone = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
								->where("actualDeliveryId", $origData->actualDeliveryId)
								->first();

								if(count($checkClone)<=0){


									//CLONE ACTUAL FOR FUTURE REF
									DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
									->insert([
										"actualDeliveryId" => $origData->actualDeliveryId,
										"batchTicketNumber" =>$origData->batchTicketNumber,
										"region" =>$origData->region,
										"province" =>$origData->province,
										"municipality" =>$origData->municipality,
										"dropOffPoint" =>$origData->dropOffPoint,
										"seedVariety" =>$origData->seedVariety,
										"totalBagCount" =>$rejected_bags,
										"dateCreated" =>$origData->dateCreated,
										"send" =>$origData->send,
										"seedTag" =>$origData->seedTag,
										"prv_dropoff_id" =>$origData->prv_dropoff_id,
										"prv" =>$origData->prv,
										"moa_number" =>$origData->moa_number,
										"app_version" =>$origData->app_version,
										"batchSeries" =>$origData->batchSeries,
										"remarks" =>$origData->remarks,
										"isRejected" =>$origData->isRejected,
										"is_transferred" =>$origData->is_transferred,
										"has_rla" =>$origData->has_rla,
										"is_breakdown" =>1,
										"category" =>$breakdownList["category"]
									]);
									//INSERT MAIN BREAKDOWN
									$is_palleted = $breakdownList["palleted"];
									$is_good_stocking = $breakdownList["stockings"];
									$is_good_wh = $breakdownList["warehouse"];
									$wh_pest = $breakdownList["pest"];
									$wh_temperature = $breakdownList["temp"];
									$wh_roofing = $breakdownList["roofing"];
									$remarks = $breakdownList["remarks"];
									
									if($is_palleted == "")$is_palleted=0;
									if($is_good_stocking == "")$is_good_stocking=0;
									if($is_good_wh == "")$is_good_wh=0;
									if($wh_pest == "")$wh_pest=0;
									if($wh_temperature == "")$wh_temperature=0;
									if($wh_roofing == "")$wh_roofing=0;
									if($remarks =="")$remarks="";
									
									DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
									->insert([
										"batchTicketNumber" => $batchTicketNumber,
										"region" => $origData->region,
										"province" => $origData->province,
										"municipality" => $origData->municipality,
										"seedTag" => $breakdownList["seedTag"],
										"category" => $breakdownList["category"],
										"seedsAge" => $breakdownList["seedsAge"],
										"is_palleted" => $is_palleted,
										"is_good_stocking" => $is_good_stocking,
										"is_good_wh" => $is_good_wh,
										"wh_pest" => $wh_pest,
										"wh_temperature" => $wh_temperature,
										"wh_roofing" => $wh_roofing,
										"remarks" => $remarks,
										"inspector_username" => $username,
										"date_created" => $breakdownList["date_created"]
									]);
									
								
										
									//UPDATE LS_DATA
									DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
									->where('actualDeliveryId',$origData->actualDeliveryId)
									->update([
										"totalBagCount" => $remaining_bags,
										"is_hold" => 0
									]);




									// //WITH THE SAME SEEDTAG
									// $theSameSeedTag = DB::connection("ls_inspection_db")->table("tbl_delivery")
									// 	->where("coopAccreditation", $coop_accre)
									// 	->where("seedTag", $breakdownList["seedTag"])
									// 	->where("batchTicketNumber", "!=", $batchTicketNumber)
									// 	->get();

									// if(count($theSameSeedTag)>0){
									// 	foreach ($theSameSeedTag as $sameSeedTagdelivery) {
									// 		$sameSeedTag = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
									// 		->where("batchTicketNumber", $sameSeedTagdelivery->batchTicketNumber)
									// 		->where("seedTag", $sameSeedTagdelivery->seedTag)
									// 		->first();

									// 		if(count($sameSeedTag)>0){
									// 		/*
									// 		//CLONE ACTUAL FOR FUTURE REF
									// 		DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
									// 		->insert([
									// 			"actualDeliveryId" => $sameSeedTag->actualDeliveryId,
									// 			"batchTicketNumber" =>$sameSeedTag->batchTicketNumber,
									// 			"region" =>$sameSeedTag->region,
									// 			"province" =>$sameSeedTag->province,
									// 			"municipality" =>$sameSeedTag->municipality,
									// 			"dropOffPoint" =>$sameSeedTag->dropOffPoint,
									// 			"seedVariety" =>$sameSeedTag->seedVariety,
									// 			"totalBagCount" =>$sameSeedTag->totalBagCount,
									// 			"dateCreated" =>$sameSeedTag->dateCreated,
									// 			"send" =>$sameSeedTag->send,
									// 			"seedTag" =>$sameSeedTag->seedTag,
									// 			"prv_dropoff_id" =>$sameSeedTag->prv_dropoff_id,
									// 			"prv" =>$sameSeedTag->prv,
									// 			"moa_number" =>$sameSeedTag->moa_number,
									// 			"app_version" =>$sameSeedTag->app_version,
									// 			"batchSeries" =>$sameSeedTag->batchSeries,
									// 			"remarks" =>$sameSeedTag->remarks,
									// 			"isRejected" =>$sameSeedTag->isRejected,
									// 			"is_transferred" =>$sameSeedTag->is_transferred,
									// 			"has_rla" =>$sameSeedTag->has_rla,
									// 			"is_breakdown" =>1,
									// 			"category" =>$breakdownList["category"]
									// 		]);
									// 		//ADD TBL BREAKDOWN MAIN
									// 		DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
									// 		->insert([
									// 			"batchTicketNumber" => $sameSeedTag->batchTicketNumber,
									// 			"region" => $sameSeedTag->region,
									// 			"province" => $sameSeedTag->province,
									// 			"municipality" => $sameSeedTag->municipality,
									// 			"seedTag" => $breakdownList["seedTag"],
									// 			"category" => $breakdownList["category"],
									// 			"seedsAge" => $breakdownList["seedsAge"],
									// 			"is_palleted" => $is_palleted,
									// 			"is_good_stocking" => $is_good_stocking,
									// 			"is_good_wh" => $is_good_wh,
									// 			"wh_pest" => $wh_pest,
									// 			"wh_temperature" => $wh_temperature,
									// 			"wh_roofing" => $wh_roofing,
									// 			"remarks" => "DUE TO BATCH :".$batchTicketNumber,
									// 			"inspector_username" => $username,
									// 			"date_created" => $breakdownList["date_created"]
									// 		]);
									// 		*/
									// 		// //HOLD SEEDTAG
									// 		// DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
									// 		// 	->where('actualDeliveryId', $sameSeedTag->actualDeliveryId)
									// 		// 	->update(['is_hold' => 2]);
									// 		}
									// 	}
									// }	
								}else{
									/*
									DB::rollback();
									$main_return = array(
											"status" => 0,
											"message" => "Batch and SeedTag Already Exist",
											"data" => ""
											);
									 return json_encode($main_return); */
								}
						}else{
							DB::rollback();
							$main_return = array(
									"status" => 0,
									"message" => "No Data on Last Season",
									"data" => ""
									);
							 return json_encode($main_return);
						}
					
					}
					
					
					elseif($breakdownList["category"]=="R"){
						
				

						//INSERT TO current tbl_actual_breakdown -> Copy for Original Data
						$origData = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
							->where("batchTicketNumber", $batchTicketNumber)
							->where("seedTag", $breakdownList["seedTag"])
							->first();
						if(count($origData)>0){
							$rejected_bags = $breakdownList["num_bags_rejected"];
							$remaining_bags = $origData->totalBagCount - $rejected_bags;
							$checkClone = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_actual_delivery_breakdown")
								->where("actualDeliveryId", $origData->actualDeliveryId)
								->first();

								if(count($checkClone)<=0){


									//CLONE ACTUAL FOR FUTURE REF
									DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
									->insert([
										"actualDeliveryId" => $origData->actualDeliveryId,
										"batchTicketNumber" =>$origData->batchTicketNumber,
										"region" =>$origData->region,
										"province" =>$origData->province,
										"municipality" =>$origData->municipality,
										"dropOffPoint" =>$origData->dropOffPoint,
										"seedVariety" =>$origData->seedVariety,
										"totalBagCount" =>$rejected_bags,
										"dateCreated" =>$origData->dateCreated,
										"send" =>$origData->send,
										"seedTag" =>$origData->seedTag,
										"prv_dropoff_id" =>$origData->prv_dropoff_id,
										"prv" =>$origData->prv,
										"moa_number" =>$origData->moa_number,
										"app_version" =>$origData->app_version,
										"batchSeries" =>$origData->batchSeries,
										"remarks" =>$origData->remarks,
										"isRejected" =>$origData->isRejected,
										"is_transferred" =>$origData->is_transferred,
										"has_rla" =>$origData->has_rla,
										"is_breakdown" =>1,
										"category" =>$breakdownList["category"]
									]);
									//INSERT MAIN BREAKDOWN
									$is_palleted = $breakdownList["palleted"];
									$is_good_stocking = $breakdownList["stockings"];
									$is_good_wh = $breakdownList["warehouse"];
									$wh_pest = $breakdownList["pest"];
									$wh_temperature = $breakdownList["temp"];
									$wh_roofing = $breakdownList["roofing"];
									$remarks = $breakdownList["remarks"];
									
									if($is_palleted == "")$is_palleted=0;
									if($is_good_stocking == "")$is_good_stocking=0;
									if($is_good_wh == "")$is_good_wh=0;
									if($wh_pest == "")$wh_pest=0;
									if($wh_temperature == "")$wh_temperature=0;
									if($wh_roofing == "")$wh_roofing=0;
									if($remarks =="")$remarks="";
									
									DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
									->insert([
										"batchTicketNumber" => $batchTicketNumber,
										"region" => $origData->region,
										"province" => $origData->province,
										"municipality" => $origData->municipality,
										"seedTag" => $breakdownList["seedTag"],
										"category" => $breakdownList["category"],
										"seedsAge" => $breakdownList["seedsAge"],
										"is_palleted" => $is_palleted,
										"is_good_stocking" => $is_good_stocking,
										"is_good_wh" => $is_good_wh,
										"wh_pest" => $wh_pest,
										"wh_temperature" => $wh_temperature,
										"wh_roofing" => $wh_roofing,
										"remarks" => $remarks,
										"inspector_username" => $username,
										"date_created" => $breakdownList["date_created"]
									]);
									
									//HOLD SEEDTAG
									DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
										->where('actualDeliveryId', $origData->actualDeliveryId)
										->update(['is_hold' => 1]);


										
									//UPDATE LS_DATA
									DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
									->where("actualDeliveryId", $origData->actualDeliveryId)
									->update([
										"totalBagCount" => $remaining_bags,
										"is_hold" => 0
									]);




									// //WITH THE SAME SEEDTAG
									// $theSameSeedTag = DB::connection("ls_inspection_db")->table("tbl_delivery")
									// 	->where("coopAccreditation", $coop_accre)
									// 	->where("seedTag", $breakdownList["seedTag"])
									// 	->where("batchTicketNumber", "!=", $batchTicketNumber)
									// 	->get();

									// if(count($theSameSeedTag)>0){
									// 	foreach ($theSameSeedTag as $sameSeedTagdelivery) {
									// 		$sameSeedTag = DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
									// 		->where("batchTicketNumber", $sameSeedTagdelivery->batchTicketNumber)
									// 		->where("seedTag", $sameSeedTagdelivery->seedTag)
									// 		->first();

									// 		if(count($sameSeedTag)>0){
									// 		/*
									// 		//CLONE ACTUAL FOR FUTURE REF
									// 		DB::connection("delivery_inspection_db")->table("tbl_actual_delivery_breakdown")
									// 		->insert([
									// 			"actualDeliveryId" => $sameSeedTag->actualDeliveryId,
									// 			"batchTicketNumber" =>$sameSeedTag->batchTicketNumber,
									// 			"region" =>$sameSeedTag->region,
									// 			"province" =>$sameSeedTag->province,
									// 			"municipality" =>$sameSeedTag->municipality,
									// 			"dropOffPoint" =>$sameSeedTag->dropOffPoint,
									// 			"seedVariety" =>$sameSeedTag->seedVariety,
									// 			"totalBagCount" =>$sameSeedTag->totalBagCount,
									// 			"dateCreated" =>$sameSeedTag->dateCreated,
									// 			"send" =>$sameSeedTag->send,
									// 			"seedTag" =>$sameSeedTag->seedTag,
									// 			"prv_dropoff_id" =>$sameSeedTag->prv_dropoff_id,
									// 			"prv" =>$sameSeedTag->prv,
									// 			"moa_number" =>$sameSeedTag->moa_number,
									// 			"app_version" =>$sameSeedTag->app_version,
									// 			"batchSeries" =>$sameSeedTag->batchSeries,
									// 			"remarks" =>$sameSeedTag->remarks,
									// 			"isRejected" =>$sameSeedTag->isRejected,
									// 			"is_transferred" =>$sameSeedTag->is_transferred,
									// 			"has_rla" =>$sameSeedTag->has_rla,
									// 			"is_breakdown" =>1,
									// 			"category" =>$breakdownList["category"]
									// 		]);
									// 		//ADD TBL BREAKDOWN MAIN
									// 		DB::connection("delivery_inspection_db")->table("tbl_breakdown_buffer")
									// 		->insert([
									// 			"batchTicketNumber" => $sameSeedTag->batchTicketNumber,
									// 			"region" => $sameSeedTag->region,
									// 			"province" => $sameSeedTag->province,
									// 			"municipality" => $sameSeedTag->municipality,
									// 			"seedTag" => $breakdownList["seedTag"],
									// 			"category" => $breakdownList["category"],
									// 			"seedsAge" => $breakdownList["seedsAge"],
									// 			"is_palleted" => $is_palleted,
									// 			"is_good_stocking" => $is_good_stocking,
									// 			"is_good_wh" => $is_good_wh,
									// 			"wh_pest" => $wh_pest,
									// 			"wh_temperature" => $wh_temperature,
									// 			"wh_roofing" => $wh_roofing,
									// 			"remarks" => "DUE TO BATCH :".$batchTicketNumber,
									// 			"inspector_username" => $username,
									// 			"date_created" => $breakdownList["date_created"]
									// 		]);
									// 		*/
									// 		//HOLD SEEDTAG
									// 		DB::connection("ls_inspection_db")->table("tbl_actual_delivery")
									// 			->where('actualDeliveryId', $sameSeedTag->actualDeliveryId)
									// 			->update(['is_hold' => 1]);
									// 		}
									// 	}
									// }	
								}else{
									/*
									DB::rollback();
									$main_return = array(
											"status" => 0,
											"message" => "Batch and SeedTag Already Exist",
											"data" => ""
											);
									 return json_encode($main_return); */
								}
						}else{
							DB::rollback();
							$main_return = array(
									"status" => 0,
									"message" => "No Data on Last Season",
									"data" => ""
									);
							 return json_encode($main_return);
						}
					}else{
						DB::rollback();
						$main_return = array(
						"status" => 0,
						"message" => "Incorrect Category",
						"data" => ""
						);
						return json_encode($main_return);
					}
				}
				 $main_return = array(
						"status" => 1,
						"message" => "Success",
						"data" => ""
						);
				 	return json_encode($main_return);


			}catch(\Illuminate\Database\QueryException $ex){
				 DB::rollback();
				 dd($ex);
				 $main_return = array(
						"status" => 0,
						"message" => "Insert Error",
						"data" => ""
						);
				 return json_encode($main_return);
			}

		
			//UNHOLD BATCH ON INSPECTOR
			DB::connection("ls_inspection_db")->table("tbl_inspection_for_breakdown")
				->where("batchTicketNumber", $batchTicketNumber)
				->update([
					"is_tagged" => 0
				]);

		}else{
			$main_return= array(
		 		"status" => 0,
		 		"message" => "You do not have the access privilege for this API",
		 		"data" => array()
		 	);
		 	return json_encode($main_return);
		}
	}







	public function InsertTransferDataNewProcedure(Request $request){
		
		$transfer = new Transfer();

        $transfer_str = rtrim($request->transfer_str,"*|*");
        $transfer_list = explode("*|*", $transfer_str);
        
        DB::beginTransaction();
        try{
			//if($request->api_key === $ICTS_API_KEY){
             $dropoff_point_data_oldseason = DB::connection('ls_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();
			
				$cprv = $transfer->_check_dropoff($request->destination_dropoff);
            //ADD LIBRARY PRV
                if($cprv==0){
		   
				 $prvID =  DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
					->insertGetId([
						'prv_dropoff_id' => $dropoff_point_data_oldseason->prv_dropoff_id,
                        'coop_accreditation' => $dropoff_point_data_oldseason->coop_accreditation,
                        'region' => $dropoff_point_data_oldseason->region,
                        'province' =>$dropoff_point_data_oldseason->province,
                        'municipality' => $dropoff_point_data_oldseason->municipality,
                        'dropOffPoint' => $dropoff_point_data_oldseason->dropOffPoint,
                        'prv' => $dropoff_point_data_oldseason->prv,
                        'is_active' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
						'created_by' => Auth::user()->username,
                       
					]);
					
                }

            //get destination region,province,municipality,dropoff etc. based on destination dop_id
            $dropoff_point_data = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();

            $new_batch_number = Auth::user()->userId."-BCH-".time();
            $log_str = "";
            $log_count = 1;
            $bag_count = 0;
            $seed_variety_log = "";
            //add data to tbl_actual_delivery for each seed tag

            foreach($transfer_list as $str_row){
                $transfer_details = explode("&", $str_row);
                $seed_tag = $transfer_details[0];
                $seedTag_bags = $transfer_details[1];
				$seedType = $transfer_details[2];
				
                //get seed tag details based on batch number & seed tag on tbl_actual_delivery
                //OLD SEASON
                $seedtag_details = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $request->batch_number)
                    ->where('seedTag', $seed_tag)
                    ->first();

                 $coop_details = DB::connection('ls_seed_coop')->table('tbl_cooperatives')
                    ->where('accreditation_no', $request->coop_acre)
                    ->first();

                
				
				//UPDATE BAGS ON OLD SEASON
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $request->batch_number)
                ->where('seedTag', $seed_tag)
                ->update([
                    'totalBagCount' => intval($seedtag_details->totalBagCount) - intval($seedTag_bags),
                ]);  
                    
                    DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->insert([
                        'batchTicketNumber' => $new_batch_number,
                        'region' => $dropoff_point_data->region,
                        'province' => $dropoff_point_data->province,
                        'municipality' => $dropoff_point_data->municipality,
                        'dropOffPoint' => $dropoff_point_data->dropOffPoint,
                        'seedVariety' => $seedtag_details->seedVariety,
                        'totalBagCount' => $seedTag_bags,
                        'prv' => $dropoff_point_data->prv,
                        'seedTag' => $seed_tag,
                        'send' => 1,
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                        'app_version' => "v1.3.1",
                        'isRejected' => '0',
                        'is_transferred' => '1',
                        'remarks' => 'transferred from previous season batch: '.$request->batch_number,
                        'moa_number' => $coop_details->current_moa,
						'transferCategory' => 'P',
						'seedType' => $seedType,
                    ]);

                    $seed_variety_log = $seed_variety_log.' | '.$seedtag_details->seedVariety;
                //generate string to store to lib_logs to store step by step transfer
                $log_str .= "($log_count) seedTag: $seed_tag, seedVariety: $seedtag_details->seedVariety, amounting to $seedTag_bags bag(s) : ";
                $log_count += 1;
                $bag_count += intval($seedTag_bags);
            }

			
			   //record transaction in transder logs table last season //
			   DB::connection('ls_rcep_transfers_db')->table('transfer_logs')
			  // $d = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ws.transfer_logs')
				->insert([
					'coop_accreditation' => $request->coop_acre,
					'seed_variety' => $request->batch_number,
					'bags' => $bag_count,
					'date_created' => date("Y-m-d H:i:s"),
					'created_by' => Auth::user()->username,
					'prv_dropoff_id' => $request->destination_dropoff,
				]); 

				DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
          // $d = DB::table($GLOBALS['season_prefix'].'rcep_transfers_ws.transfer_logs')
            ->insert([
                'coop_accreditation' => $request->coop_acre,
                'batch_number' => $request->batch_number,
                'new_batch_number' => $new_batch_number,
                'origin_province' => $request->original_province,
                'origin_municipality' => $request->original_municipality,
                'origin_dop_id' =>  $request->origin_dop_id,
                'destination_province' => $request->destination_province,
                'destination_municipality' => $request->destination_municipality,
                'destination_dop_id' => $request->destination_dropoff,
                'seed_variety' => $seed_variety_log,
                'bags' => $bag_count,
                'transferred_by' => Auth::user()->username,
            ]); 


 
				//record transaction in lib_logs
				DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')
				->insert([
					'category' => 'TRANSFER_FROM_LAST_SEASON',
					'description' => 'Transferred seeds of batch ticket #: `'.$request->batch_number.'` from (prv_dropoff_id) from:'.$request->origin_dop_id.', to:'.$request->destination_dropoff.', amounting to a total of: '.$bag_count.' || summary of transfer: '.$log_str,
					'author' => Auth::user()->username,
					'ip_address' => $_SERVER['REMOTE_ADDR']
				]);
			
			
			
			
            DB::commit();
			
            return route('rcef.transfers');
			
			/*}else{
				return json_encode("You do not have the access privilege for this API");
			}*/
        }catch(\Illuminate\Database\QueryException $ex){
            //if atleast 1 query fails, all will not execute and the database will be rolled back - fullproof
			DB::rollback();
			// dd($ex->getMessage());
            return 'sql_error';
           
        }
	}



	public function InsertTransferData(Request $request){
	//	dd($request->all());
		$transfer = new Transfer();

        $transfer_str = rtrim($request->transfer_str,"*|*");
        $transfer_list = explode("*|*", $transfer_str);
        
        DB::beginTransaction();
        try{
			//if($request->api_key === $ICTS_API_KEY){
             $dropoff_point_data_oldseason = DB::connection('ls_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();
			
				$cprv = $transfer->_check_dropoff($request->destination_dropoff);
            //ADD LIBRARY PRV
                if($cprv==0){
		   
				 $prvID =  DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
					->insertGetId([
						'prv_dropoff_id' => $dropoff_point_data_oldseason->prv_dropoff_id,
                        'coop_accreditation' => $dropoff_point_data_oldseason->coop_accreditation,
                        'region' => $dropoff_point_data_oldseason->region,
                        'province' =>$dropoff_point_data_oldseason->province,
                        'municipality' => $dropoff_point_data_oldseason->municipality,
                        'dropOffPoint' => $dropoff_point_data_oldseason->dropOffPoint,
                        'prv' => $dropoff_point_data_oldseason->prv,
                        'is_active' => 1,
                        'date_created' => date("Y-m-d H:i:s"),
						'created_by' => Auth::user()->username,
                       
					]);
					
                }

            //get destination region,province,municipality,dropoff etc. based on destination dop_id
            $dropoff_point_data = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
                ->where('prv_dropoff_id', $request->destination_dropoff)
                ->groupBy('prv_dropoff_id')
                ->first();

            $new_batch_number = Auth::user()->userId."-BCH-".time();
            $log_str = "";
            $log_count = 1;
            $bag_count = 0;
            $seed_variety_log = "";
            //add data to tbl_actual_delivery for each seed tag

            foreach($transfer_list as $str_row){
                $transfer_details = explode("&", $str_row);
                $seed_tag = $transfer_details[0];
                $seedTag_bags = $transfer_details[1];
				$seedType = $transfer_details[2];
				
                //get seed tag details based on batch number & seed tag on tbl_actual_delivery
                //OLD SEASON
                $seedtag_details = DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                    ->where('batchTicketNumber', $request->batch_number)
                    ->where('seedTag', $seed_tag)
                    ->first();

                 $coop_details = DB::connection('ls_seed_coop')->table('tbl_cooperatives')
                    ->where('accreditation_no', $request->coop_acre)
                    ->first();


				$iar_number = DB::connection('ls_inspection_db')->table('iar_print_logs')
					->where("batchTicketNumber", $request->batch_number)
					->value("iarCode");

				//UPDATE BAGS ON OLD SEASON
                DB::connection('ls_inspection_db')->table('tbl_actual_delivery')
                ->where('batchTicketNumber', $request->batch_number)
                ->where('seedTag', $seed_tag)
                ->update([
                    'totalBagCount' => intval($seedtag_details->totalBagCount) - intval($seedTag_bags),
                ]);  
                    
                    DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                    ->insert([
                        'batchTicketNumber' => $new_batch_number,
                        'region' => $dropoff_point_data->region,
                        'province' => $dropoff_point_data->province,
                        'municipality' => $dropoff_point_data->municipality,
                        'dropOffPoint' => $dropoff_point_data->dropOffPoint,
                        'seedVariety' => $seedtag_details->seedVariety,
                        'totalBagCount' => $seedTag_bags,
                        'prv' => $dropoff_point_data->prv,
                        'seedTag' => $seed_tag,
                        'send' => 1,
                        'dateCreated' => date("Y-m-d H:i:s"),
                        'prv_dropoff_id' => $dropoff_point_data->prv_dropoff_id,
                        'app_version' => "v1.3.1",
                        'isRejected' => '0',
                        'is_transferred' => '1',
                        'remarks' => 'transferred from previous season batch: '.$request->batch_number,
                        'moa_number' => $coop_details->current_moa,
						'transferCategory' => 'P',
						'seedType' => $seedType,
                    ]);


					if($iar_number != null){
						DB::connection('delivery_inspection_db')->table('iar_print_logs')
							->insert([
								"iarCode" => $iar_number,
								"batchTicketNumber" => $new_batch_number,
								"dateCreated" => date("Y-m-d"),
								"is_printed" => 1
							]);
					}
					


                    $seed_variety_log = $seed_variety_log.' | '.$seedtag_details->seedVariety;
                //generate string to store to lib_logs to store step by step transfer
                $log_str .= "($log_count) seedTag: $seed_tag, seedVariety: $seedtag_details->seedVariety, amounting to $seedTag_bags bag(s) : ";
                $log_count += 1;
                $bag_count += intval($seedTag_bags);
            }

			
			   //record transaction in transder logs table last season //
			   DB::connection('ls_rcep_transfers_db')->table('transfer_logs')
				->insert([
					'coop_accreditation' => $request->coop_acre,
					'seed_variety' => $request->batch_number,
					'bags' => $bag_count,
					'date_created' => date("Y-m-d H:i:s"),
					'created_by' => Auth::user()->username,
					'prv_dropoff_id' => $request->destination_dropoff,
				]); 

				DB::table($GLOBALS['season_prefix'].'rcep_transfers_ps.transfer_logs')
            ->insert([
                'coop_accreditation' => $request->coop_acre,
                'batch_number' => $request->batch_number,
                'new_batch_number' => $new_batch_number,
                'origin_province' => $request->original_province,
                'origin_municipality' => $request->original_municipality,
                'origin_dop_id' =>  $request->origin_dop_id,
                'destination_province' => $request->destination_province,
                'destination_municipality' => $request->destination_municipality,
                'destination_dop_id' => $request->destination_dropoff,
                'seed_variety' => $seed_variety_log,
                'bags' => $bag_count,
                'transferred_by' => Auth::user()->username,
            ]); 


 
				//record transaction in lib_logs
				DB::connection('mysql')->table('lib_logs')
				->insert([
					'category' => 'TRANSFER_FROM_LAST_SEASON',
					'description' => 'Transferred seeds of batch ticket #: `'.$request->batch_number.'` from (prv_dropoff_id) from:'.$request->origin_dop_id.', to:'.$request->destination_dropoff.', amounting to a total of: '.$bag_count.' || summary of transfer: '.$log_str,
					'author' => Auth::user()->username,
					'ip_address' => $_SERVER['REMOTE_ADDR']
				]);
			
			
			
			
            DB::commit();
			
            return route('rcef.transfers');
			
			/*}else{
				return json_encode("You do not have the access privilege for this API");
			}*/
        }catch(\Illuminate\Database\QueryException $ex){
            //if atleast 1 query fails, all will not execute and the database will be rolled back - fullproof
			
            DB::rollback();
			// return $ex->getMessage();
			return 'sql_error';
        }
	}


























	public function api_bdd_sgList($api_key){
		$API_KEY = "NTNkMDRhODJkOTc";
		if($api_key === $API_KEY){
			$return_array = array();
			$memberList = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_commitment_member")
				->select("tbl_commitment_member.member_id","tbl_commitment_member.first_name","tbl_commitment_member.middle_name","tbl_commitment_member.last_name","tbl_commitment_member.accreditation_number as Member_Accreditation","tbl_commitment_member.allocated_area","tbl_commitment_member.commitment_variety", "tbl_cooperatives.coopName", "tbl_commitment_member.coop_accreditation_number")
				->join($GLOBALS['season_prefix']."rcep_seed_cooperatives.tbl_cooperatives", "tbl_commitment_member.coop_accreditation_number", "=", "tbl_cooperatives.accreditation_no")
				->get();

			//dd($memberList);

				foreach ($memberList as $key => $value) {
				
					$area = $value->allocated_area;
                    if($value->allocated_area != floor($value->allocated_area)) {
                                    $dec =  $value->allocated_area - floor($value->allocated_area); 
                                    if($dec <= 0.5 ){
                                        $area = floor($value->allocated_area) + 0.5;
                                    }else{
                                        $area = floor($value->allocated_area) + 1;
                                    }
                                     // dd($area);
                                }
                        $bags = $area * 200;
                        $kgs = $area * 40;

                       array_push($return_array,array(
                       	"member_id" => $value->member_id,
                       	"first_name" => $value->first_name,
                       	"middle_name" => $value->middle_name,
                       	"last_name" => $value->last_name,
                       	"Member_Accreditation" => $value->Member_Accreditation,
                       	"allocated_area" => $value->allocated_area,
                       	//"allocated_bags" => $bags,
                       	"allocated_quantity" => $kgs ,
                       	"commitment_variety"=> $value->commitment_variety,
                       	"coopName" => $value->coopName,
                       	"coop_accreditation_number" => $value->coop_accreditation_number,
                       ));
				}




			return json_encode($return_array);
		}else{
			return "You do not have the access privilege for this API";
		}
		
	}

    public function __construct(UrlGenerator $url){
        $this->url = $url;
    }
	
	public function load_rcef_users($api_key){
		$API_KEY = "NTNkMDRhODJkOTc";
		
		try{
			if($api_key === $API_KEY){
				$rcef_users = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')->where('isDeleted', 0)->get();
				
				$return_array = array();
				foreach($rcef_users as $user){
					//role ID & tagged coop
					$user_role = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.role_user')->where('userId', $user->userId)->first();
					if(count($user_role) > 0){
						$user_role = $user_role->roleId;
					}else{
						$user_role = NULL;
					}
					
					$tagged_coop = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', $user->userId)->first();
					if(count($tagged_coop) > 0){
						$tagged_coop = $tagged_coop->coopAccreditation;
					}else{
						$tagged_coop = NULL;
					}
					
					array_push($return_array, array(
						"first_name" => $user->firstName,
						"middle_name" => $user->middleName,
						"last_name" => $user->lastName,
						"username" => $user->username,
						"email" => $user->email,
						"assigned_province" => $user->province,
						"assigned_municipality" => $user->municipality == "--SELECT ASSIGNED MUNICIPALITY--" ? NULL : $user->municipality,
						"role_id" => $user_role,
						"tagged_coop" => $tagged_coop
					));
				}
				
				return json_encode($return_array);
			}else{
				return json_encode("You do not have the access privilege to use this API!");
			}
			
		}catch(\Illuminate\Database\QueryException $ex){
            //return json_encode('sql_error');
			return json_encode($ex);
		}
	}
	
	//FAR API
	public function getFARDetails($province_name, $municipality_name, $skip, $take){
			
		ini_set('memory_limit', '-1');
		try{
			
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();

			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

			$list = DB::table($database_name.".".$table_name)
			->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
				->orderBy('farmer_fname', 'ASC')		
				->orderBy('farmer_lname', 'ASC')
				->orderBy('farmer_mname', 'ASC')
				//->skip(0)
				//->take(1000)
				->skip($skip)
				->take($take)
				->get();
			$list = json_decode(json_encode($list), true);

			//save pdf to directory
			/*$title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
			$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";
			$path = public_path('flsar\\' . $pdf_name);


			$pdf = PDFTIM::loadView('farmer.preList.list_home', 
				['list' => $list, 'region_code' => $region_code, 
				"province_code" => $province_code, "municipality_code" => $municipality_code,
				"title" => $title])
				->setPaper('Legal', 'landscape');
			return $pdf->stream($pdf_name);*/
				

		}catch(\Illuminate\Database\QueryException $ex){
            //return 'sql_error';
			dd($ex);
		}
	}
	
		//FARFORPREREG
		public function getFARDetailsPreReg($province_name, $municipality_name, $brgy, $skip, $take){
			
		$ICTS_API_KEY = "MTc0MGE3MjUzOTB";
		ini_set('memory_limit', '-1');
		try{
		//if($request->api_key === $ICTS_API_KEY){
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
				//->table('lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();
	
			if(!isset($municipal_details)){
				return 'No Listing Available';
			}	
				


			$region_name = $municipal_details->regionName;
			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

			 $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "pre_registration")
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      return 'No Listing Available';
                    }


			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";


            if($brgy != "0"){
                if($brgy == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy, 6,3)."%";
                 }
            }


            
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK
            $rsbsa_menthod = 1;
		
							
			 if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                    	//PRV FIRST
							$list = DB::table($prv_db.".pre_registration")
								->where("rsbsa_control_no", "LIKE", $rsba_pattern)
								->where("qr_code", "!=", "")
								->orderBy("last_name")
								->orderBy("first_name")
								->orderBy("mid_name")
								->skip($skip)
								->take($take)
								->get();

                    }
                 
                }
              
				$maxRow = count($list);
          
			
			if($maxRow>0){
				return $list = json_decode(json_encode($list), true);
			}else{
				return 'No Listing Available';
			}
			
		
		}catch(\Illuminate\Database\QueryException $ex){
			dd($ex);
		}


	}

	//FOR VALIDATED DATA FAR
	public function getFARDetailsValidatedData($province_name, $municipality_name, $brgy, $skip, $take){
		$ICTS_API_KEY = "MTc0MGE3MjUzOTB";
		ini_set('memory_limit', '-1');
		try{
		//if($request->api_key === $ICTS_API_KEY){
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
				//->table('lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();
		
			if(!isset($municipal_details)){
				return 'No Municipal Library';
			}	
				


			$region_name = $municipal_details->regionName;
			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

			 $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_information_final")
                            ->groupBy("TABLE_NAME")
                            ->first();
							
                    if(count($process_tbl)<=0){
                      return 'No Listing Available';
                    }


			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";


            if($brgy != "0"){
                if($brgy == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy, 6,3)."%";
                 }
            }


            
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK
            $rsbsa_menthod = 1;

							
			 if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                         $rawsql = "SELECT id, rsbsa_control_no, farmer_id, db_ref, rcef_id, distributionID, da_intervention_card, lastName, firstName, midName, extName, fullName, sex, birthdate, yield_area_harvested, yield_no_of_bags, yield_weight_per_bag, parcel_area, final_area as crop_area, actual_area, rsms_actual_area, rsms_id, data_season_entry, final_claimable as total_claimable, is_claimed, total_claimed, is_ebinhi, is_replacement, replacement_area, replacement_bags, replacement_bags_claimed, province, municipality, brgy_name, farm_province, farm_municipality, farm_brgy_name, mother_name, tel_no, geo_code, civil_status, id_type, gov_id_num, fca_name, is_pwd, is_arb, is_ip, tribe_name, ben_4ps, print_count, data_source, sync_date, status, rcef_bene, orig_area, re_tagging, tag_data from ".$prv_db.".farmer_information_final where 
						 firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  and rcef_id != '' and rcef_bene = 'V' and final_area > 0  
						 or  firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  and rcef_id != '' and rcef_bene = 'RV' and  final_area > 0 
						 or  firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  and rcef_id != '' and rcef_bene = 'JON' and  final_area > 0 
						 or  firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  and rcef_id != '' and rcef_bene = 'W2D' and data_season_entry = 'WS2022' and final_area > 0 ";
						   	// $rawsql .= "OR status NOT LIKE 'FOR_DELETE%' and firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  ";

							
							 $rawsql .= " group by rsbsa_control_no,farmer_id,lastName,firstName,midName,sex,birthdate,crop_area";
                             $rawsql .= "  order by TRIM(LEFT(rsbsa_control_no,12)) asc, TRIM(lastName) asc, 
                                     TRIM(firstName) asc, TRIM(midName) asc ";
                        $rawsql .= "LIMIT ".$take." OFFSET ".$skip;


                        $list = DB::select(DB::raw($rawsql));
					
					


                    }
                    else{ //method 0
                        $list = DB::table($prv_db.".farmer_information_final")
                            //->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            ->where('status', '1') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')     
	                        ->skip($skip)
                        	->take($take) 
                            ->get();
                    }  
                }
                else{
                	$list = DB::table($prv_db.".farmer_information_final")
                            //->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
							->where('status','1') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')      
	                        ->skip($skip)
                        	->take($take) 
                            ->get();
                }
				$maxRow = count($list);
          
			
			if($maxRow>0){
				return $list = json_decode(json_encode($list), true);
			}else{
				
			
				return 'No Listing Available';
			}
			
		
		}catch(\Illuminate\Database\QueryException $ex){
			dd($ex);
		}
	}


	public function getFARDetailsReplacement($province_name, $municipality_name, $brgy, $skip, $take){
		
		$ICTS_API_KEY = "MTc0MGE3MjUzOTB";
		ini_set('memory_limit', '-1');
		try{
		//if($request->api_key === $ICTS_API_KEY){
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
				//->table('lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();
	
			if(!isset($municipal_details)){
				return 'No municipal library';
			}	
				


			$region_name = $municipal_details->regionName;
			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

			 $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_information_final")
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      return 'No Listing Available';
                    }


			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";


            if($brgy != "0"){
                if($brgy == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy, 6,3)."%";
                 }
            }


            
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK
            $rsbsa_menthod = 1;

							
			 if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                  
                         $rawsql = "SELECT id, rsbsa_control_no, farmer_id, db_ref, rcef_id, distributionID, da_intervention_card, lastName, firstName, midName, extName, fullName, sex, birthdate, yield_area_harvested, yield_no_of_bags, yield_weight_per_bag, parcel_area, replacement_area as crop_area, actual_area, rsms_actual_area, rsms_id, data_season_entry, final_claimable as total_claimable, is_claimed, total_claimed, is_ebinhi, is_replacement, replacement_area, replacement_bags, replacement_bags_claimed, province, municipality, brgy_name, farm_province, farm_municipality, farm_brgy_name, mother_name, tel_no, geo_code, civil_status, id_type, gov_id_num, fca_name, is_pwd, is_arb, is_ip, tribe_name, ben_4ps, print_count, data_source, sync_date, status, rcef_bene, orig_area, re_tagging, tag_data from ".$prv_db.".farmer_information_final where 
						 firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  and rcef_id != '' and rcef_bene != 'V' and rcef_bene != 'RV' and rcef_bene != 'JON' and rcef_bene != 'W2D' and final_area > 0 and is_replacement=1
						  or rcef_bene = 'W2D' and  firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."' and data_season_entry !='WS2022'  and final_area > 0 and is_replacement=1 ";
						   	// $rawsql .= "OR status NOT LIKE 'FOR_DELETE%' and firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  ";
                          
							   $rawsql .= " group by rsbsa_control_no,farmer_id,lastName,firstName,midName,sex,birthdate,crop_area";
							
							// $rawsql .= " group by rsbsa_control_no, firstName, lastName";
                             $rawsql .= "  order by TRIM(LEFT(rsbsa_control_no,12)) asc, TRIM(lastName) asc, 
                                     TRIM(firstName) asc, TRIM(midName) asc ";
                        $rawsql .= "LIMIT ".$take." OFFSET ".$skip;

                        $list = DB::select(DB::raw($rawsql));

				
						
					



                    }
                    else{ //method 0
                        $list = DB::table($prv_db.".farmer_information_final")
                            //->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            ->where('status', '1') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')     
	                        ->skip($skip)
                        	->take($take) 
                            ->get();
                    }  
                }
                else{
                	$list = DB::table($prv_db.".farmer_information_final")
                            //->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
							->where('status','1') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')      
	                        ->skip($skip)
                        	->take($take) 
                            ->get();
                }
				$maxRow = count($list);
          
			
			if($maxRow>0){
				return $list = json_decode(json_encode($list), true);
			}else{
				return 'No Listing Available';
			}
			
		
		}catch(\Illuminate\Database\QueryException $ex){
			dd($ex);
		}

	}



	public function getFARDetailsPS($province_name, $municipality_name, $brgy, $skip, $take, $pre_reg,$is_transfer){

		if($pre_reg == 1){
			// $check = array('0349878911','0349899191','0349306047','0349920290','0349239896','0349036047','0349328420','0349044399','0349667167','0349499218','0349201050','0349218095','0349243285','0349712678','0349379415','0349900152','0349887413','0349993717','0349393601','0349572411','0349455847','0349432376','0349276603','0349819605','0349289486','0349712358','0349321395','0349387588','0349844573','0349124789','0349915502','0349033109','0349838565','0349480636','0349389351','0349537251','0349263572','0349790306','0349386670','0349811059','0349033865','0349998734','0349674545','0349796392','0349409284','0349415710','0349687106','0349632862','0349928336');

			// if($municipality_name == "ALFONSO LISTA"){
			// 	$municipality_name = 'ALFONSO LISTA (POTIA)';
			// }

			//  $list = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
			//  	->select("lname as lastName", "fname as firstName", "midname as midName", "rsbsa_control_number as rsbsa_control_no", "actual_area as crop_area", DB::raw("concat('1') as is_ebinhi")
			// 	, "crop_establishment", "ecosystem", "sowing_month", "sowing_week", "yield_no_bags", "yield_weight_bags", "yield_area", "farmer_declared_area", DB::raw("CEIL(farmer_declared_area * 2) as bags")
			// 	, DB::raw("CONCAT('1') as is_prereg")
			// 	)
			//  	->where("province_name", "LIKE","%".$province_name."%")
			// 	->where("municipality_name", "LIKE","%".$municipality_name."%")
			// 	// ->whereIn("rcef_id", $check)
			// 	->where("isPrereg", 1)
			// 	->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
			// 	->orderBy("lname")
			// 	->orderBy("fname")
			// 	->orderBy("midname")
			// 	// ->limit(1)
			// 	->get();
			// 	//  dd($list);
			// 	return $list = json_decode(json_encode($list), true);



	//PRV_DATA
	$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
	//->table('lib_dropoff_point')
	->where('province', $province_name)
	->where('municipality', $municipality_name)
	->first();

	if(!isset($municipal_details)){
		return 'No municipal library';
	}	
	
		$region_name = $municipal_details->regionName;
		$prv_db = $GLOBALS['season_prefix']."prv_".$municipal_details->prv_code;
			
		$region_code = substr($municipal_details->prv,0,2);
		$province_code = substr($municipal_details->prv,2,2);
		$municipality_code = substr($municipal_details->prv,4,2);
		$claiming_prv = $region_code.'-'.$province_code.'-'.$municipality_code."%";

		$rsba_pattern = $region_code.$province_code.$municipality_code."%";
		if($brgy != "0"){
			if($brgy == "all"){
				 $rsba_pattern = $region_code.$province_code.$municipality_code."%";
			 }
			 elseif($brgy == "NONE"){
				$rsba_pattern = "";
			 }
			 else{
				 $rsba_pattern = $region_code.$province_code.$municipality_code.substr($brgy, 6,3)."%";
			 }
		}

		
		$list = DB::table($prv_db.".farmer_information_final")
		// ->select('id', 'claiming_prv', 'claiming_brgy','no_of_parcels','rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate',    'final_area as origin_crop_area', "final_area as crop_area",'final_claimable as origin_total_claimable', "final_claimable as total_claimable", 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'data_source')
			// ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
			->where("claiming_brgy", "LIKE", $rsba_pattern)
			->where("final_area", "<", 0.1)
			->where("rcef_id", "!=", "")
			->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
			->orderBy(DB::raw('TRIM(lastName)'), 'ASC')   
			->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
			->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
			->skip($skip)
			->take($take) 
			->get();

		


		}
		else{

		if($is_transfer == 0){
			//PRV_DATA
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
			//->table('lib_dropoff_point')
			->where('province', $province_name)
			->where('municipality', $municipality_name)
			->first();
	
			if(!isset($municipal_details)){
				return 'No municipal library';
			}	
			
				$region_name = $municipal_details->regionName;
				$prv_db = $GLOBALS['season_prefix']."prv_".$municipal_details->prv_code;
					
				$region_code = substr($municipal_details->prv,0,2);
				$province_code = substr($municipal_details->prv,2,2);
				$municipality_code = substr($municipal_details->prv,4,2);
				$claiming_prv = $region_code.'-'.$province_code.'-'.$municipality_code."%";

				$rsba_pattern = $region_code.$province_code.$municipality_code;
				if($brgy != "0"){
					if($brgy == "all"){
						 $rsba_pattern = $region_code.$province_code.$municipality_code."%";
					 }else{
						 $rsba_pattern = $region_code.$province_code.$municipality_code.substr($brgy, 6,3)."%";
					 }
				}
	
				
				$list = DB::table($prv_db.".farmer_information_final")
				// ->select('id', 'rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate',    'final_area as origin_crop_area', DB::raw("IF(final_area >5, 5, final_area) as crop_area"),'final_claimable as origin_total_claimable', DB::raw("IF(final_claimable > 10, 10, final_claimable) as total_claimable"), 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'data_source')
					// ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
					->where("claiming_brgy", "LIKE", $rsba_pattern)
					->where("final_area", ">=", 0.1)
					->where("rcef_id", "!=", "")
				
					
				
					 
					
					->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
					->orderBy(DB::raw('TRIM(lastName)'), 'ASC')   
					->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
					->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
					->skip($skip)
					->take($take) 
					->get();
	
				

		}elseif($is_transfer == 2){
			//PRV_DATA
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
			//->table('lib_dropoff_point')
			->where('province', $province_name)
			->where('municipality', $municipality_name)
			->first();
	
			if(!isset($municipal_details)){
				return 'No municipal library';
			}	
			
				$region_name = $municipal_details->regionName;
				$prv_db = $GLOBALS['season_prefix']."prv_".$municipal_details->prv_code;
					
				$region_code = substr($municipal_details->prv,0,2);
				$province_code = substr($municipal_details->prv,2,2);
				$municipality_code = substr($municipal_details->prv,4,2);
				$claiming_prv = $region_code.'-'.$province_code.'-'.$municipality_code."%";

				$rsba_pattern = $region_code.$province_code.$municipality_code."%";
				if($brgy != "0"){
					if($brgy == "all"){
						 $rsba_pattern = $region_code.$province_code.$municipality_code."%";
					 }
					 elseif($brgy == "NONE"){
						$rsba_pattern = "";
					 }
					 else{
						 $rsba_pattern = $region_code.$province_code.$municipality_code.substr($brgy, 6,3)."%";
					 }
				}
	
				
				$list = DB::table($prv_db.".farmer_information_final")
				// ->select('id', 'claiming_prv', 'claiming_brgy','no_of_parcels','rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate',    'final_area as origin_crop_area', "final_area as crop_area",'final_claimable as origin_total_claimable', "final_claimable as total_claimable", 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'data_source')
					// ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
					->where("claiming_brgy", "LIKE", $rsba_pattern)
					
					->where("final_area", ">=", 0.1)
					->where("rcef_id", "!=", "")
					
					->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
					->orderBy(DB::raw('TRIM(lastName)'), 'ASC')   
					->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
					->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
					->skip($skip)
					->take($take) 
					->get();
	
				

		}
		
		
		
		
		else{
			
		$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
		//->table('lib_dropoff_point')
		->where('province', $province_name)
		->where('municipality', $municipality_name)
		->first();

		if(!isset($municipal_details)){
			return 'No municipal library';
		}	
		
			$region_name = $municipal_details->regionName;
			$prv_db = $GLOBALS['season_prefix']."prv_".$municipal_details->prv_code;
				
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.$province_code.$municipality_code;
			// dd($rsba_pattern);
			// $rsba_pattern = str_replace("-","",$rsba_pattern);
// dd($rsba_pattern);
			$list = DB::table($prv_db.".farmer_information_final")



			// ->select('id', 'rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate',    'final_area as origin_crop_area', "final_area as crop_area",'final_claimable as origin_total_claimable',  "final_claimable as total_claimable", 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'data_source')
				// ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
				
				->where("claiming_brgy", "LIKE", $rsba_pattern)
				
				->where("final_area", ">=", 0.1)
				->where("rcef_id", "!=", "")


				->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
				->orderBy(DB::raw('TRIM(lastName)'), 'ASC')   
				->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
				->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
				// ->skip($skip)
				// ->take($take) 
				->get();
		// dd($list);

		}


	}



		// dd($list);
			return $list = json_decode(json_encode($list), true);
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
    

	public function lib_prv($provincial_code){
		
		$database_name = $GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv";
		if($provincial_code == "all"){
			$provincial_code = "%";
		}

		$lib_prv = DB::table($database_name)
			->where("prv_code", "like", $provincial_code)
			->groupBy("municipality")
			->get();

		return json_encode($lib_prv);

	}


	public function API_fetch_farmerProfile($code, $start_index){
		// $GLOBALS['season_prefix'] = "ws2025_";
		// dd($GLOBALS['season_prefix']);
		$lib_prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
			->where("prv", $code)
			->first();
		if($lib_prv != null){
			$province = $lib_prv->province;
			$municipality = $lib_prv->municipality;
			
		}else{
			$province = "-";
			$municipality = "-";
		}

		$database_name = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
		$rsbsa_pattern = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
		$to_prv_pattern = substr($code,0,2).substr($code,2,2).substr($code,4,2);

		// 'id', 'claiming_prv','rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate',    'final_area as origin_crop_area','final_area as crop_area','final_claimable as origin_total_claimable', 'final_claimable as total_claimable', 'is_claimed', 'total_claimed', DB::raw("IF(is_replacement = 1, replacement_area_claimed , total_claimed_area) as total_claimed_area"), DB::raw("(final_claimable - total_claimed) as inbred_balance"), DB::raw("(final_area - IF(is_replacement = 1, replacement_area_claimed , total_claimed_area)) as hybrid_balance") , 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed' , 'replacement_area_claimed', DB::raw("CONCAT('".$province."') as province"), DB::raw("CONCAT('".$municipality."') as municipality"), 'brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'data_source', DB::raw("CONCAT('4') as version_list")
		// dd($database_name,$rsbsa_pattern,$start_index);


			// 	$farmer_profile_final = DB::table($database_name.".farmer_information_final")	
			// 	->select( "*",
			// 	  DB::raw("IF(is_new = 9, 1, 0) as is_fca"),
			//       DB::raw("(final_claimable - total_claimed) as inbred_balance"), 
			//       DB::raw("(ROUND(final_area,2) - IF(is_replacement = 1, ROUND(replacement_area_claimed,2) , ROUND(total_claimed_area,2))) as hybrid_balance"), 
			//       DB::raw("CONCAT('4') as version_list"), 
			//       DB::raw('ROUND(final_area,2) as origin_crop_area'),
			//       DB::raw('ROUND(final_area,2) as crop_area'),
			//       'final_claimable as origin_total_claimable', 
			//       'final_claimable as total_claimable' 
			// 	  )
			// 				->orderBy("municipality")
			// 				->orderBy("lastName")
			// 				->orderBy("firstName")
			// 				->orderBy("midName")
			// 				->where("claiming_prv", "LIKE", $rsbsa_pattern."%")
			// 				->where("rcef_id", "!=", "")
			// 				->where('is_new',"!=", 2)						
			// 				// ->where("final_area", ">=", 0.1)
			// 				->where("id", ">", $start_index)
			// 				->get();
				
			// foreach($farmer_profile_final as $profile)
			// {
			// 	$inbredArea = DB::table($database_name.".area_history")
			// 	->where('db_ref', $profile->db_ref)
			// 	->first();

			// 	$hybridArea = DB::table($database_name.".area_history")
			// 	->where('db_ref', $profile->db_ref)
			// 	->orderBy('version', 'DESC')
			// 	->first();
			// 	$profile->inbredArea = $inbredArea->area;
			// 	$profile->hybridArea = $hybridArea->area;
			// 	// dd($inbredArea,$hybridArea);
			// }
			
			// return json_encode($farmer_profile_final);
		
	
			$pythonPath = $GLOBALS['python_path'];

			$scriptPath = base_path('app//Http//PyScript//API//API_fetch_farmerProfile.py');

			$escapedDb = escapeshellarg($database_name);
			$escapedRsbsa = escapeshellarg($rsbsa_pattern);
			$escapedIndex = escapeshellarg($start_index);
	
			$command = "$pythonPath \"$scriptPath\" $escapedDb $escapedRsbsa $escapedIndex";
			
			$process = new Process($command);
	
			try {
				$process->mustRun();
				$farmer_profile_final = $process->getOutput();
				// dd($farmer_profile_final);
				return $farmer_profile_final;
			} catch (ProcessFailedException $exception) {
				echo $exception->getMessage();
			}

		
		

	}


	public function API_fetch_farmerProfile2($code){
		$database_name = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
		$rsbsa_pattern = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
		  $to_prv_pattern = substr($code,0,2).substr($code,2,2).substr($code,4,2);

			$farmer_profile_final = DB::table($database_name.".farmer_information_final")	
					->select('id', 'claiming_prv','assigned_rsbsa','rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate',    'final_area as origin_crop_area','final_area as crop_area','final_claimable as origin_total_claimable', 'final_claimable as total_claimable', 'is_claimed', 'total_claimed', 'total_claimed_area', DB::raw("(final_claimable - total_claimed) as inbred_balance"), DB::raw("(final_area - total_claimed_area) as hybrid_balance") , 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'data_source',DB::raw("CONCAT('3') as version_list"))
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->orderBy("midName")
						->where("claiming_prv", "LIKE", $rsbsa_pattern."%")

						->where("rcef_id", "!=", "")
						
						->where("final_area", ">=", 0.1)
						->where("to_prv_code", "=", null)
						// ->where("rsbsa_control_no","03-49-17-034-000166")
						->orWhere("to_prv_code", "LIKE", $to_prv_pattern."%")
						->where("rcef_id", "!=", "")
							
						->where("final_area", ">=", 0.1)
						

						//  ->where("rsbsa_control_no","17-52-05-018-000015")
						// ->where("rcef_id", "0349704546")
						//17-52-05-018-000015
						->get();
		 $farmer_new_released = DB::table($database_name.".new_released")
		 ->select('*',DB::raw("sum(claimed_area) as claimed_area_sum"))
		 ->where('prv_dropoff_id','like',  $to_prv_pattern.'%')
		 ->groupby('content_rsbsa','final_area')
		 ->get();
		$farmer_new_released = json_decode(json_encode($farmer_new_released), true);
		
		$pre_reg_data_list = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
				->where("rsbsa_control_number", "LIKE",  $rsbsa_pattern."%")
				->where("isPrereg", 1)
				->get();
		$pre_reg_data_list = json_decode(json_encode($pre_reg_data_list), true);

	
			foreach($farmer_profile_final as $row){
				if(count($pre_reg_data_list)>0){
				$pre_reg_data =  $this->search_to_array($pre_reg_data_list, "rcef_id", $row->rcef_id);
				$planting_week = "";
				$ecosystem_source_cs = "";
				$crop_establishment_cs = "";
				$ecosystem_cs = "";
				$yield_last_season_details = "";
				$pre_reg = 0;
				
				if(isset($pre_reg_data[0])){
					// dd($pre_reg_data);
					$pre_reg  = 1;
					// dd($row->rcef_id);
					if($pre_reg_data[0]["sowing_week"] == "1st Week" ){
						$week = "01";
					}elseif($pre_reg_data[0]["sowing_week"] == "2nd Week"){
						$week = "02";
					} elseif($pre_reg_data[0]["sowing_week"] == "3rd Week"){
						$week = "03"; 
					}else{
						$week = "04"; 
					}
                   
					$planting_week = date("m",strtotime($pre_reg_data[0]["sowing_month"]))."/".$week;
                    
					if($pre_reg_data[0]["ecosystem"] == "irrigated_cis"){
						$ecosystem_source_cs = "CIS(Communal)";
						$ecosystem_cs = "Irrigated";
					}elseif($pre_reg_data[0]["ecosystem"] == "irrigated_nis_nia"){
						$ecosystem_source_cs = "NIS/NIA";
						$ecosystem_cs = "Irrigated";

					}elseif($pre_reg_data[0]["ecosystem"] == "irrigated_stw"){
						$ecosystem_source_cs = "STW(Shallow Tube Well)";
						$ecosystem_cs = "Irrigated";

					}elseif($pre_reg_data[0]["ecosystem"] == "irrigated_swis"){
						$ecosystem_source_cs = "SWIP(Small water impounding pond)";
						$ecosystem_cs = "Irrigated";

					}elseif($pre_reg_data[0]["ecosystem"] == "rainfed_low"){
						$ecosystem_source_cs = "Lowland";
						$ecosystem_cs = "Rainfed";

					}elseif($pre_reg_data[0]["ecosystem"] == "rainfed_up"){
						$ecosystem_source_cs = "Upland";
						$ecosystem_cs = "Rainfed";

					}


					if($pre_reg_data[0]["crop_establishment"] == "transplanting"){
						$crop_establishment_cs = "Transplanted";
					}else{
						$crop_establishment_cs = "Direct Seeding";
					}
					
					// $yield_last_season_details = '[{"variety":"","area":'.$pre_reg_data[0]["yield_area"].',"bags":'.$pre_reg_data[0]["yield_no_bags"].',"weight":'.$pre_reg_data[0]["yield_area"].',"type":"","class":""}]';
					
					$data_yield = array(array(
						"variety" => "",
						"area" => $pre_reg_data[0]["yield_area"],
						"bags" => $pre_reg_data[0]["yield_no_bags"],
						"weight" => $pre_reg_data[0]["yield_weight_bags"],
						"type" => "",
						"class" => ""
					));
			
					
					$yield_last_season_details =  json_encode($data_yield);

				}
			
				$row->planting_week = $planting_week;
				$row->ecosystem_cs = $ecosystem_cs;
				$row->ecosystem_source_cs = $ecosystem_source_cs;
				$row->crop_establishment_cs = $crop_establishment_cs;
				$row->yield_last_season_details = $yield_last_season_details;
				$row->pre_reg = $pre_reg;

			


			}
			$row->inbred_balance =  $row->total_claimable;
			$row->hybrid_balance =  $row->crop_area;
			$row->total_claimed =0;//BAGS
			$row->total_claimed_area =0; //AREA
			$releaseData_data_u_rcefId =  $this->search_to_array($farmer_new_released, "db_ref", $row->db_ref);
			if(count($releaseData_data_u_rcefId) >0){
				$row->inbred_balance = $row->total_claimable - ceil($releaseData_data_u_rcefId[0]['claimed_area_sum']*2);
				$row->hybrid_balance = $row->crop_area - $releaseData_data_u_rcefId[0]['claimed_area_sum'];	
		
				$row->total_claimed = ceil($releaseData_data_u_rcefId[0]['claimed_area_sum']*2);
				$row->total_claimed_area = $releaseData_data_u_rcefId[0]['claimed_area_sum'];									
			}
			
		}
		
		

		// $releaseData_data_u_rsba =  $this->search_to_array($releaseData_data_u_rcefId, "content_rsbsa", $row->assigned_rsbsa);	
		// 		if(count($releaseData_data_u_rsba) >0){	
		// 			$releaseData_data_u_sex =  $this->search_to_array($releaseData_data_u_rsba, "sex", $row->sex);		
		// 			if(count($releaseData_data_u_sex)){																												
		// 						$row->inbred_balance = $row->total_claimable - ceil($releaseData_data_u_sex[0]['claimed_area_sum']*2);
		// 						$row->hybrid_balance = $row->crop_area - $releaseData_data_u_sex[0]['claimed_area_sum'];	
								
		// 						$row->total_claimed = ceil($releaseData_data_u_sex[0]['claimed_area_sum']*2);
		// 						$row->total_claimed_area = $releaseData_data_u_sex[0]['claimed_area_sum'];				 
		// 			}									
		// 		}else{
		// 			$releaseData_data_u_rsba =  $this->search_to_array($releaseData_data_u_rcefId, "content_rsbsa", $row->rsbsa_control_no);	
		// 			if(count($releaseData_data_u_rsba) >0){	
		// 				$releaseData_data_u_sex =  $this->search_to_array($releaseData_data_u_rsba, "sex", $row->sex);		
		// 				if(count($releaseData_data_u_sex)){													
		// 						$row->inbred_balance = $row->total_claimable - ceil($releaseData_data_u_sex[0]['claimed_area_sum']*2);
		// 						$row->hybrid_balance = $row->crop_area - $releaseData_data_u_sex[0]['claimed_area_sum'];	
		// 						$row->total_claimed = ceil($releaseData_data_u_sex[0]['claimed_area_sum']*2);
		// 						$row->total_claimed_area = $releaseData_data_u_sex[0]['claimed_area_sum'];						
		// 				}									
		// 			}
		// 		}

		return json_encode($farmer_profile_final);

	}


		//FARAPIPREVIOUS
	public function getFARDetailsPS_old($province_name, $municipality_name, $brgy, $skip, $take){
		$ICTS_API_KEY = "MTc0MGE3MjUzOTB";
		ini_set('memory_limit', '-1');
		try{
		//if($request->api_key === $ICTS_API_KEY){
			$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
				//->table('lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();
	
			if(!isset($municipal_details)){
				return 'No municipal library';
			}	
				


			$region_name = $municipal_details->regionName;
			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($municipal_details->prv, 0, 4);

			 $process_tbl =  DB::table("information_schema.TABLES")
                            ->select("TABLE_SCHEMA", "TABLE_NAME")
                            ->where("TABLE_SCHEMA", $prv_db)
                            ->where("TABLE_NAME", "farmer_information_final")
                            ->groupBy("TABLE_NAME")
                            ->first();
                    if(count($process_tbl)<=0){
                      return 'No Listing Available';
                    }


			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);

            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";


            if($brgy != "0"){
                if($brgy == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy, 6,3)."%";
                 }
            }


            
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK
            $rsbsa_menthod = 1;

							
			 if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                    	//PRV FIRST
                    	/*
                    	$list = DB::table($prv_db.".farmer_information")
                            ->select(DB::raw("DISTINCT(rsbsa_control_no), farmerID, id distributionID, lastName, firstName, midName, extName, fullName, sex, birthdate, region, province, municipality, barangay, area, area_harvested, actual_area, season, yield, weight_per_bag, total_claimable, da_area, icts_rsbsa"))
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')  
	                        ->skip($skip)
                        	->take($take) 
                            ->get(); 
  						dd($list); */

                         $rawsql = "SELECT id, rsbsa_control_no, farmer_id, db_ref, rcef_id, distributionID, da_intervention_card, lastName, firstName, midName, extName, fullName, sex, birthdate, yield_area_harvested, yield_no_of_bags, yield_weight_per_bag, parcel_area, final_area as crop_area, actual_area, rsms_actual_area, rsms_id, data_season_entry, final_claimable as total_claimable, is_claimed, total_claimed, is_ebinhi, is_replacement, replacement_area, replacement_bags, replacement_bags_claimed, province, municipality, brgy_name, farm_province, farm_municipality, farm_brgy_name, mother_name, tel_no, geo_code, civil_status, id_type, gov_id_num, fca_name, is_pwd, is_arb, is_ip, tribe_name, ben_4ps, print_count, data_source, sync_date, status, rcef_bene, orig_area, re_tagging, tag_data from ".$prv_db.".farmer_information_final where 
						 firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  and rcef_id != '' and rcef_bene != 'V' and rcef_bene != 'RV' and rcef_bene != 'JON' and rcef_bene != 'W2D' and final_area > 0
						  or rcef_bene = 'W2D' and  firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."' and data_season_entry !='WS2022'  and final_area > 0";
						   	// $rawsql .= "OR status NOT LIKE 'FOR_DELETE%' and firstName !='' and rsbsa_control_no LIKE '".$rsba_pattern."'  ";
                          
							   $rawsql .= " group by rsbsa_control_no,farmer_id,lastName,firstName,midName,sex,birthdate,crop_area";
							
							// $rawsql .= " group by rsbsa_control_no, firstName, lastName";
                             $rawsql .= "  order by TRIM(LEFT(rsbsa_control_no,12)) asc, TRIM(lastName) asc, 
                                     TRIM(firstName) asc, TRIM(midName) asc ";
                        $rawsql .= "LIMIT ".$take." OFFSET ".$skip;

                        $list = DB::select(DB::raw($rawsql));

				
						
					



                    }
                    else{ //method 0
                        $list = DB::table($prv_db.".farmer_information_final")
                            //->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            ->where('status', '1') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')     
	                        ->skip($skip)
                        	->take($take) 
                            ->get();
                    }  
                }
                else{
                	$list = DB::table($prv_db.".farmer_information_final")
                            //->select("DISTINCT('rsbsa_control_no')")
                            ->where("rsbsa_control_no", "LIKE", $rsba_pattern)
                            ->where('firstName','!=','""') 
                            // ->orWhere("icts_rsbsa", "LIKE", $rsba_pattern)
                            // ->where('firstName','!=','""') 
							->where('status','1') 
                            ->orderBy(DB::raw('TRIM(LEFT(rsbsa_control_no,12))'), 'ASC') 
	                  		->orderBy(DB::raw('TRIM(firstName)'), 'ASC')
	                        ->orderBy(DB::raw('TRIM(lastName)'), 'ASC')        
	                        ->orderBy(DB::raw('TRIM(midName)'), 'ASC')      
	                        ->skip($skip)
                        	->take($take) 
                            ->get();
                }
				$maxRow = count($list);
          
			
			if($maxRow>0){
				return $list = json_decode(json_encode($list), true);
			}else{
				return 'No Listing Available';
			}
			
		
		}catch(\Illuminate\Database\QueryException $ex){
			dd($ex);
		}



	}
		//FARAPIPREVIOUS
	public function getFARDetailsPS_for_LAST_SEASON($province_name, $municipality_name, $brgy, $skip, $take){
		$ICTS_API_KEY = "MTc0MGE3MjUzOTB";
		ini_set('memory_limit', '-1');
		try{
		//if($request->api_key === $ICTS_API_KEY){
			$municipal_details = DB::connection('ls_inspection_db')
				->table('lib_dropoff_point')
				->where('province', $province_name)
				->where('municipality', $municipality_name)
				->first();

			if(!isset($municipal_details)){
				return 'No Listing Available';
			}	
				
			$region_name = $municipal_details->region;
			$database_name = "rpt_".substr($municipal_details->prv, 0, 4);
			$table_name = "tbl_".$municipal_details->prv;
			
			$region_code = substr($municipal_details->prv,0,2);
			$province_code = substr($municipal_details->prv,2,2);
			$municipality_code = substr($municipal_details->prv,4,2);


			//if($province_name =="ILOCOS SUR" and $municipality_name ==)




            $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";


            if($brgy != "0"){
                if($brgy == "all"){
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code."%";
                 }else{
                     $rsba_pattern = $region_code.'-'.$province_code.'-'.$municipality_code.'-'.substr($brgy, 6,3)."%";
                 }
            }


            
            $rsbsa_check = 1; //ALWAYS TRUE RSBSA CHECK

            //set ls_seed_coop to rpt db , //set ls_rcep_transfers_db to prv
				$con = $this->set_rpt_db('ls_seed_coop',$database_name);
				$con2 = $this->set_rpt_db('ls_rcep_transfers_db','prv_'.$region_code.$province_code);

				if($con2=='Connection Established!'){$rsbsa_menthod = 1; }else{ $rsbsa_menthod = 0; } //RSBSA METHOD
		
		if($con=='Connection Established!'){							
			 if($rsbsa_check){
                    if($rsbsa_menthod){ //method 1
                    	//PRV FIRST
                         $rsbsa_query = DB::connection('ls_rcep_transfers_db')->table('farmer_profile')
	                                        ->select('rsbsa_control_no as pattern', DB::raw('substr(rsbsa_control_no, 1, 12) as foo'))
	                                        ->where('rsbsa_control_no', 'like', $rsba_pattern)
	                                        ->where('firstName','!=','""')
	                                        ->groupBy('foo')
	                                        ->orderBy('foo', 'asc')
	                                        ->get();

                                        $checkerArr = array();
                                        foreach ($rsbsa_query as $key => $patt) {
                                            foreach ($patt as $key => $value) {
                                              if($key=='foo'){
                                                $checkerArr[] = "'%".$value."%'";
                                                }
                                            }
                                        }

                        // $rawsql = " from ".$database_name.".".$table_name." where farmer_fname !='' ";


                         $rawsql = "SELECT DISTINCT rsbsa_control_number, qr_code, farmer_fname, farmer_mname, farmer_lname, farmer_ext,sex, birthdate, tel_number,province, municipality, mother_fname, mother_mname, mother_lname, mother_ext, dist_area, actual_area, bags, seed_variety, date_released, farmer_id, released_by from ".$table_name." where farmer_fname !='' ";
				                        foreach ($checkerArr as $key => $val) {
				                                   
				                                if($key==0){
				                                   $rawsql .= " AND rsbsa_control_number like ".$val;
				                                }else{
				                                   $rawsql .= " OR rsbsa_control_number like ".$val;
				                                }
				                        } //FOREACH
                        $rawsql .= "  order by TRIM(LEFT(rsbsa_control_number,12)) asc, TRIM(farmer_lname) asc, 
                                     TRIM(farmer_fname) asc, TRIM(farmer_mname) asc ";
                        $rawsql .= "LIMIT ".$take." OFFSET ".$skip;
                        //dd($rawsql);
                        $list = DB::connection('ls_seed_coop')
                        		->select(DB::raw($rawsql));
						//dd($list)		;
                    }
                    else{ //method 0
                        $list = DB::connection('ls_seed_coop')
						->table($table_name)
						 ->select(DB::raw("DISTINCT(rsbsa_control_number), qr_code, farmer_fname, farmer_mname, farmer_lname, farmer_ext,sex, birthdate, tel_number,province, municipality, mother_fname, mother_mname, mother_lname, mother_ext, dist_area, actual_area, bags, seed_variety, date_released, farmer_id, released_by"))
                        ->where('farmer_fname','!=','""') 
                        ->where('rsbsa_control_number','like', $rsba_pattern)
                  		->orderBy('TRIM(LEFT(rsbsa_control_number,12))', 'ASC') 
                  		->orderBy('TRIM(farmer_lname)', 'ASC')
                        ->orderBy('TRIM(farmer_fname)', 'ASC')        
                        ->orderBy('TRIM(farmer_mname)', 'ASC')  
                        
                        ->skip($skip)
                        ->take($take) 
                        ->get();
                    }  
                }
                else{
                	$list = DB::connection('ls_seed_coop')
					->table($table_name)
					 ->select(DB::raw("DISTINCT(rsbsa_control_number), qr_code, farmer_fname, farmer_mname, farmer_lname, farmer_ext,sex, birthdate, tel_number,province, municipality, mother_fname, mother_mname, mother_lname, mother_ext, dist_area, actual_area, bags, seed_variety, date_released, farmer_id, released_by"))
					->orderBy('TRIM(LEFT(rsbsa_control_number,12))', 'ASC')
					->orderBy('TRIM(farmer_lname)', 'ASC')
					->orderBy('TRIM(farmer_fname)', 'ASC')		
					->orderBy('TRIM(farmer_mname)', 'ASC')
					
					->skip($skip)
                    ->take($take)
					->get(); 
                }
				$maxRow = count($list);
            }  //IF CONNECTION TRUE
            else{
            	$maxRow = 0;
            }
			
			if($maxRow>0){
				return $list = json_decode(json_encode($list), true);
			}else{
				return 'No Listing Available';
			}
			
			//RETURN ORIG DB	
			$con = $this->set_rpt_db('ls_seed_coop','rcep_seed_cooperatives');			
            $con2 = $this->set_rpt_db('ls_rcep_transfers_db','rcep_transfers_ws');

		/*}else{
				return json_encode("You do not have the access privilege for this API");
		}*/
		
		}catch(\Illuminate\Database\QueryException $ex){
            //return 'sql_error';
            //RETURN ORIG DB
            $con = $this->set_rpt_db('ls_seed_coop','rcep_seed_cooperatives');
            $con2 = $this->set_rpt_db('ls_rcep_transfers_db','rcep_transfers_ws');
			dd($ex);
		}
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
		

	public function API_fetch_variety($seed_class){
		if($seed_class == "A"){
			$seed_class = "%";
		}
		$database = $GLOBALS['season_prefix']."seed_seed.tbl_varieties";
			$variety_data = DB::table($database)
				->where("seed_class", "LIKE", $seed_class."%")
				->get();

		return json_encode($variety_data);
	}

	public function API_fetch_otherInfo_newAPI($code){
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$ws_data = file_get_contents("https://rcef-seed.philrice.gov.ph/rcef_ws2020/api/fetch/other_info/".$code);
            return $ws_data;
			
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
	}

	public function API_fetch_otherInfoWs2021($code){
		try{

			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$con = $this->set_rpt_db('ls_inspection_db',$database);	
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
		if($con=='Connection Established!'){
			$other_info = DB::connection('ls_inspection_db')
					->table('other_info')
				->select('info_id', 'farmer_id', 'rsbsa_control_no', 'mother_fname', 'mother_lname', "mother_mname",
						 'mother_suffix', 'birthdate', 'is_representative', 'id_type', 'relationship', 
						 'have_pic', 'phone', 'send')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->get();
				
			if(count($other_info) > 0){
				$other_info_arr = array();
				foreach($other_info as $row){
					$profile_check = DB::connection('ls_inspection_db')
						->table('farmer_profile')
						->select('rsbsa_control_no')
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->where('distributionID', 'like', 'R%')
						->orWhere('actual_area', '>', 0)
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->where('distributionID', 'like', 'R%')
						->orderBy('id')
						->first();
						
					if(count($profile_check) > 0){
						array_push($other_info_arr, array(
							"info_id" => $row->info_id,
							"farmer_id" => $row->farmer_id,
							"rsbsa_control_no" => $row->rsbsa_control_no,
							"mother_fname" => $row->mother_fname,
							"mother_mname" => $row->mother_mname,
							"mother_lname" => $row->mother_lname,
							"mother_suffix" => $row->mother_suffix,
							"birthdate" => $row->birthdate,
							"is_representative" => $row->is_representative,
							"id_type" => $row->id_type,
							"relationship" => $row->relationship,
							"have_pic" => $row->have_pic,
							"phone" => $row->phone,
							"send" => $row->send
						));
					}
				}
					return json_encode($other_info_arr);
			
			}else{
				$other_info_arr = array();
				array_push($other_info_arr, array(
							"info_id" => "1",
							"farmer_id" => '63'.substr($code,0,4).'000000000',
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
							"mother_fname" => "Juan",
							"mother_mname" => "",
							"mother_lname" => "Dela Cruz",
							"mother_suffix" => "",
							"birthdate" => "",
							"is_representative" => "0",
							"id_type" => "",
							"relationship" => "",
							"have_pic" => "0",
							"phone" => "",
							"send" => "0"
						));
						
				return json_encode($other_info_arr);
			}
		}else{
			$other_info_arr = array();
				array_push($other_info_arr, array(
							"info_id" => "1",
							"farmer_id" => '63'.substr($code,0,4).'000000000',
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
							"mother_fname" => "Juan",
							"mother_mname" => "",
							"mother_lname" => "Dela Cruz",
							"mother_suffix" => "",
							"birthdate" => "",
							"is_representative" => "0",
							"id_type" => "",
							"relationship" => "",
							"have_pic" => "0",
							"phone" => "",
							"send" => "0"
						));
						
				return json_encode($other_info_arr);
		}		
			
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
     	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
	}

	public function API_fetch_farmerProfileNew($code){
		$database_name = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
		$municipal = substr($code,4,2);
	
		$farmer_profile = (object)[];
		$a=array();	

		if($municipal == false){
			$farmer_profile_unique = DB::table($database_name.".farmer_information_final")
						->select("*",DB::raw('CONCAT("") as content'))
						->where("status", 1)
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  0)
						// ->limit(5)
						->get();


			$farmer_profile_dupli = DB::table($database_name.".farmer_information_final")
						->select("*")
						->where("status", 1)
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  1)
						//->limit(5)
						->get();
			$farmer_profile_unique =json_decode(json_encode($farmer_profile_unique),true);	
			foreach($farmer_profile_dupli as $dupli)
			{
				$content = DB::table($database_name.".farmer_information_unmerge")
					->where("rcef_id", $dupli->rcef_id)
					->get();
				$dupli->content = $content;
				array_push($farmer_profile_unique,json_decode(json_encode($dupli),true));

			}
								
			
		}else{
			$rsbsa_pattern = substr($code, 0,2)."-".substr($code, 2, 2)."-".substr($code, 4,2);
			$farmer_profile_unique = DB::table($database_name.".farmer_information_final")
						->select("*",DB::raw('CONCAT("") as content'))
						->where("status", 1)
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  0)
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
						  //->limit(5)
						->get();


			$farmer_profile_dupli = DB::table($database_name.".farmer_information_final")
						->select("*")
						->where("status", 1)
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  1)
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
						//->limit(5)
						->get();
			$farmer_profile_unique =json_decode(json_encode($farmer_profile_unique),true);
			foreach($farmer_profile_dupli as $dupli)
			{
				$content = DB::table($database_name.".farmer_information_unmerge")
					->where("rcef_id", $dupli->rcef_id)
					->get();
				$dupli->content = $content;
				array_push($farmer_profile_unique,json_decode(json_encode($dupli),true));
			}

		}

		
		 return json_encode($farmer_profile_unique);

	}

	public function API_fetch_farmerProfile_with_lib($code){
		$database_name = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
		$municipal = substr($code,4,2);
	
		$farmer_profile = (object)[];
			

		if($municipal == false){
			$farmer_profile_unique = DB::table($database_name.".farmer_information_final")
						->select('id', 'rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate', 'yield_area_harvested', 'yield_no_of_bags', 'yield_weight_per_bag', 'parcel_area', 'final_area as crop_area', 'actual_area', 'rsms_actual_area', 'rsms_id', 'data_season_entry', 'final_claimable as total_claimable', 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'farm_province', 'farm_municipality', 'farm_brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'print_count', 'data_source', 'sync_date', 'status', 'rcef_bene', 'orig_area', 're_tagging', 'tag_data',DB::raw('CONCAT("") as content'))
						
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  0)
						->where("final_area", ">", 0)
						


						// ->limit(5)
						->get();


			$farmer_profile_dupli = DB::table($database_name.".farmer_information_final")
						->select('id', 'rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate', 'yield_area_harvested', 'yield_no_of_bags', 'yield_weight_per_bag', 'parcel_area', 'final_area as crop_area', 'actual_area', 'rsms_actual_area', 'rsms_id', 'data_season_entry',  'final_claimable as total_claimable', 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'farm_province', 'farm_municipality', 'farm_brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'print_count', 'data_source', 'sync_date', 'status', 'rcef_bene', 'orig_area', 're_tagging', 'tag_data')
						
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  1)
						->where("final_area", ">", 0)
						->get();
			foreach($farmer_profile_dupli as $dupli)
			{
				$content = DB::table($database_name.".farmer_information_unmerge")
					->where("rcef_id", $dupli->rcef_id)
					->get();
				$dupli->content = $content;

			}
			$farmer_profile->no_merge = $farmer_profile_unique;
			$farmer_profile->with_merge = $farmer_profile_dupli;
		}else{
			$rsbsa_pattern = substr($code, 0,2)."-".substr($code, 2, 2)."-".substr($code, 4,2);
			$bypass_pattern = substr($code, 0,2).substr($code, 2, 2).substr($code, 4,2);
			
			$farmer_profile_unique = DB::table($database_name.".farmer_information_final")
						->select('id', 'rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate', 'yield_area_harvested', 'yield_no_of_bags', 'yield_weight_per_bag', 'parcel_area', 'final_area as crop_area', 'actual_area', 'rsms_actual_area', 'rsms_id', 'data_season_entry', 'final_claimable as total_claimable', 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'farm_province', 'farm_municipality', 'farm_brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'print_count', 'data_source', 'sync_date', 'status', 'rcef_bene', 'orig_area', 're_tagging', 'tag_data',DB::raw('CONCAT("") as content'))
						

						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  0)
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
						->orWhere("tag_data", "1")
						->where("rsms_actual_area",">",0)
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
                		->orWhere("to_prv_code", "LIKE", $bypass_pattern)
						//  ->limit(5)
						->get();


			$farmer_profile_dupli = DB::table($database_name.".farmer_information_final")
						->select('id', 'rsbsa_control_no', 'farmer_id', 'db_ref', 'rcef_id', 'distributionID', 'da_intervention_card', 'lastName', 'firstName', 'midName', 'extName', 'fullName', 'sex', 'birthdate', 'yield_area_harvested', 'yield_no_of_bags', 'yield_weight_per_bag', 'parcel_area', 'final_area as crop_area', 'actual_area', 'rsms_actual_area', 'rsms_id', 'data_season_entry', 'final_claimable as total_claimable', 'is_claimed', 'total_claimed', 'is_ebinhi', 'is_replacement', 'replacement_area', 'replacement_bags', 'replacement_bags_claimed', 'province', 'municipality', 'brgy_name', 'farm_province', 'farm_municipality', 'farm_brgy_name', 'mother_lname', 'mother_fname', 'mother_mname', 'mother_suffix', 'tel_no', 'geo_code', 'civil_status', 'id_type', 'gov_id_num', 'fca_name', 'is_pwd', 'is_arb', 'is_ip', 'tribe_name', 'ben_4ps', 'print_count', 'data_source', 'sync_date', 'status', 'rcef_bene', 'orig_area', 're_tagging', 'tag_data')
					
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("tag_data",  1)
						->where("rsms_actual_area", "<=", 0)
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
						->get();
			foreach($farmer_profile_dupli as $dupli)
			{
			
					$content = DB::table($database_name.".farmer_information_unmerge")
					->where("rcef_id", $dupli->rcef_id)
					->get();
					$dupli->content = $content;
				

				

			}

			// $aa = $farmer_profile_dupli->merge($farmer_profile_unique);
			// dd($aa);

			$farmer_profile->no_merge = $farmer_profile_unique;
			$farmer_profile->with_merge = $farmer_profile_dupli;
			
		}

		
		return json_encode($farmer_profile);

	}

	

	public function API_fetch_farmerProfile_unclean($code){
		try {
			$database_name = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$municipal = substr($code,4,2);
				if($municipal == false){
					$farmer_profile = DB::table($database_name.".farmer_information")
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->get();

						return json_encode($farmer_profile);
				}else{
					$rsbsa_pattern = substr($code, 0,2)."-".substr($code, 2, 2)."-".substr($code, 4,2);
					
					$check_by_name = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
						->where("prv", $code)
						->first();
					if($check_by_name != null){
						$farmer_profile = DB::table($database_name.".farmer_information")
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
						->where("status", 1)
						->orWhere("municipality", "LIKE", $check_by_name->municipality)
						->where("status", 1)
						->orderBy("municipality")
						->orderBy("lastName")
						
						->orderBy("firstName")
						
						->get();
					}else{
						$farmer_profile = DB::table($database_name.".farmer_information")
						->where("status", 1)
						->orderBy("municipality")
						->orderBy("lastName")
						->orderBy("firstName")
						->where("rsbsa_control_no", "LIKE", $rsbsa_pattern."%")
						->get();
					}
					

					
					return json_encode($farmer_profile);
				}
			

								


		} catch (\Throwable $th) {
			//throw $th;
			return 'sql_error';
		}


	}

	public function API_fetch_otherInfo_WS22($code){
		try{

			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
	
			$other_info = DB::table($database.'.other_info_processed')
				->select('info_id', 'farmer_id', 'rsbsa_control_no', 'mother_fname', 'mother_lname', "mother_mname",
						 'mother_suffix', 'birthdate', 'is_representative', 'id_type', 'relationship', 
						 'have_pic', 'phone', 'send', 'icts_rsbsa')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->orWhere("rsbsa_control_no", 'like', $rsbsa_reference,"%")
				->get();
				
			if(count($other_info) > 0){
				$other_info_arr = array();
					$profile_check = DB::table($database.'.farmer_information')
						->select('rsbsa_control_no')
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->where('distributionID', 'like', 'R%')
						->orWhere('actual_area', '>', 0)
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->where('distributionID', 'like', 'R%')
						->orderBy('id')
						->first();
					if(count($profile_check)>0){
						foreach($other_info as $row){
								array_push($other_info_arr, array(
									"info_id" => $row->info_id,
									"farmer_id" => $row->farmer_id,
									"rsbsa_control_no" => $row->rsbsa_control_no,
									"mother_fname" => $row->mother_fname,
									"mother_mname" => $row->mother_mname,
									"mother_lname" => $row->mother_lname,
									"mother_suffix" => $row->mother_suffix,
									"birthdate" => $row->birthdate,
									"is_representative" => $row->is_representative,
									"id_type" => $row->id_type,
									"relationship" => $row->relationship,
									"have_pic" => $row->have_pic,
									"phone" => $row->phone,
									"send" => $row->send,
									"icts_rsbsa" => $row->icts_rsbsa
								));
						}
					}else{
						array_push($other_info_arr, array(
									"info_id" => "1",
									"farmer_id" => '63'.substr($code,0,4).'000000000',
		                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
									"mother_fname" => "Juan",
									"mother_mname" => "",
									"mother_lname" => "Dela Cruz",
									"mother_suffix" => "",
									"birthdate" => "",
									"is_representative" => "0",
									"id_type" => "",
									"relationship" => "",
									"have_pic" => "0",
									"phone" => "",
									"send" => "0"
								));
					}
					return json_encode($other_info_arr);
			
			}else{
				$other_info_arr = array();
				array_push($other_info_arr, array(
							"info_id" => "1",
							"farmer_id" => '63'.substr($code,0,4).'000000000',
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
							"mother_fname" => "Juan",
							"mother_mname" => "",
							"mother_lname" => "Dela Cruz",
							"mother_suffix" => "",
							"birthdate" => "",
							"is_representative" => "0",
							"id_type" => "",
							"relationship" => "",
							"have_pic" => "0",
							"phone" => "",
							"send" => "0"
						));
						
				return json_encode($other_info_arr);
			}
	
			
		}catch(\Illuminate\Database\QueryException $ex){
            return "sql error";
            //return $ex;
        }   	
	}

	public function API_fetch_otherInfo_WS2021($code){
		try{

			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$con = $this->set_rpt_db('ls_inspection_db',$database);	
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
		if($con=='Connection Established!'){
			$other_info = DB::connection('ls_inspection_db')
					->table('other_info')
				->select('info_id', 'farmer_id', 'rsbsa_control_no', 'mother_fname', 'mother_lname', "mother_mname",
						 'mother_suffix', 'birthdate', 'is_representative', 'id_type', 'relationship', 
						 'have_pic', 'phone', 'send')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->get();
				
			if(count($other_info) > 0){
				$other_info_arr = array();
				foreach($other_info as $row){
					$profile_check = DB::connection('ls_inspection_db')
						->table('farmer_profile')
						->select('rsbsa_control_no')
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->where('distributionID', 'like', 'R%')
						->orWhere('actual_area', '>', 0)
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->where('distributionID', 'like', 'R%')
						->orderBy('id')
						->first();
						
					if(count($profile_check) > 0){
						array_push($other_info_arr, array(
							"info_id" => $row->info_id,
							"farmer_id" => $row->farmer_id,
							"rsbsa_control_no" => $row->rsbsa_control_no,
							"mother_fname" => $row->mother_fname,
							"mother_mname" => $row->mother_mname,
							"mother_lname" => $row->mother_lname,
							"mother_suffix" => $row->mother_suffix,
							"birthdate" => $row->birthdate,
							"is_representative" => $row->is_representative,
							"id_type" => $row->id_type,
							"relationship" => $row->relationship,
							"have_pic" => $row->have_pic,
							"phone" => $row->phone,
							"send" => $row->send
						));
					}
				}
					return json_encode($other_info_arr);
			
			}else{
				$other_info_arr = array();
				array_push($other_info_arr, array(
							"info_id" => "1",
							"farmer_id" => '63'.substr($code,0,4).'000000000',
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
							"mother_fname" => "Juan",
							"mother_mname" => "",
							"mother_lname" => "Dela Cruz",
							"mother_suffix" => "",
							"birthdate" => "",
							"is_representative" => "0",
							"id_type" => "",
							"relationship" => "",
							"have_pic" => "0",
							"phone" => "",
							"send" => "0"
						));
						
				return json_encode($other_info_arr);
			}
		}else{
			$other_info_arr = array();
				array_push($other_info_arr, array(
							"info_id" => "1",
							"farmer_id" => '63'.substr($code,0,4).'000000000',
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
							"mother_fname" => "Juan",
							"mother_mname" => "",
							"mother_lname" => "Dela Cruz",
							"mother_suffix" => "",
							"birthdate" => "",
							"is_representative" => "0",
							"id_type" => "",
							"relationship" => "",
							"have_pic" => "0",
							"phone" => "",
							"send" => "0"
						));
						
				return json_encode($other_info_arr);
		}		
			
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
     	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
	}

	public function API_fetch_otherInfo_old_DS2021($code){
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$other_info = DB::table($database.'.other_info')
				->select('info_id', 'farmer_id', 'rsbsa_control_no', 'mother_fname', 'mother_lname', "mother_mname",
						 'mother_suffix', 'birthdate', 'is_representative', 'id_type', 'relationship', 
						 'have_pic', 'phone', 'send')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->get();
				
			if(count($other_info) > 0){
				$other_info_arr = array();
				foreach($other_info as $row){
					$profile_check = DB::table($database.'.farmer_profile')
						->select('rsbsa_control_no')
						->where('lastName', '!=', '')
						->where('firstName', '!=', '')
						->where('rsbsa_control_no', '!=', '')
						->orderBy('id')
						->first();
						
					if(count($profile_check) > 0){
						array_push($other_info_arr, array(
							"info_id" => $row->info_id,
							"farmer_id" => $row->farmer_id,
							"rsbsa_control_no" => $row->rsbsa_control_no,
							"mother_fname" => $row->mother_fname,
							"mother_mname" => $row->mother_mname,
							"mother_lname" => $row->mother_lname,
							"mother_suffix" => $row->mother_suffix,
							"birthdate" => $row->birthdate,
							"is_representative" => $row->is_representative,
							"id_type" => $row->id_type,
							"relationship" => $row->relationship,
							"have_pic" => $row->have_pic,
							"phone" => $row->phone,
							"send" => $row->send
						));
					}
				}
					return json_encode($other_info_arr);
			
			}else{
				$other_info_arr = array();
				array_push($other_info_arr, array(
							"info_id" => "1",
							"farmer_id" => '63'.substr($code,0,4).'000000000',
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
							"mother_fname" => "Juan",
							"mother_mname" => "",
							"mother_lname" => "Dela Cruz",
							"mother_suffix" => "",
							"birthdate" => "",
							"is_representative" => "0",
							"id_type" => "",
							"relationship" => "",
							"have_pic" => "0",
							"phone" => "",
							"send" => "0"
						));
						
				return json_encode($other_info_arr);
			}
				
			
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
	}

	public function API_fetch_farmerProfile_newAPI($code){
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$ws_data = file_get_contents("https://rcef-seed.philrice.gov.ph/rcef_ws2020/api/fetch/farmer_profile/".$code);
            return $ws_data;
			
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
	}
	
	public function API_fetch_farmerProfileZeroArea($code){
		

		try{


			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			//$con = $this->set_rpt_db('ls_inspection_db',$database);	
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$farmer_profile = DB::table($database.".verified_farmer_list")
				->where('lastName', '!=', '')
				->where('firstName', '!=', '')
				->where('rsbsa_control_no', '!=', '')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->where('distributionID', 'like', 'R%')
				->orderBy('id')
				->get();
			
			//dd($farmer_profile);


			if(count($farmer_profile) > 0){
					
					return json_encode($farmer_profile);

			}else{
				
				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0"
						));
						
						return json_encode($farmer_profile_arr);
					}
				
			

				}catch(\Illuminate\Database\QueryException $ex){
		            return 'sql_error';
		        }   
	    	
     		//$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
		   
	}

	public function API_fetch_farmerProfile_used_on_ds2021($code){

		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$con = $this->set_rpt_db('ls_inspection_db',$database);	
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
		if($con=='Connection Established!'){	
			$farmer_profile = DB::connection('ls_inspection_db')->table('farmer_profile')
				->where('lastName', '!=', '')
				->where('firstName', '!=', '')
				->where('rsbsa_control_no', '!=', '')
				->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
				->where('distributionID', 'like', 'R%')
				->orWhere('actual_area', '>', 0)
				->where('lastName', '!=', '')
				->where('firstName', '!=', '')
				->where('rsbsa_control_no', '!=', '')
				->where('rsbsa_control_no', 'like', $rsbsa_reference . '%')
				->orderBy('id')
				->get();

			if(count($farmer_profile) > 0){			
				$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');
				return json_encode($farmer_profile);		
			}else{
				
				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0"
						));
						$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
						return json_encode($farmer_profile_arr);
					}
				}
				else{

				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0"
						));
						$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
						return json_encode($farmer_profile_arr);
					

				}

				}catch(\Illuminate\Database\QueryException $ex){
		            return 'sql_error';
		        }   
	}

	public function API_fetch_farmerProfile_ws2022($code){
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			//$con = $this->set_rpt_db('ls_inspection_db',$database);	
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			//$con = $this->set_rpt_db('ls_seed_coop', $GLOBALS['season_prefix'].'rcep_paymaya');
			$con ="Connection Established!";
		if($con=='Connection Established!'){	
			$farmer_profile = DB::table($database.'.farmer_information')
			->where("rsbsa_control_no", 'LIKE', $rsbsa_reference."%")
			->orwhere("icts_rsbsa", "LIKE", $rsbsa_reference."%")
			->get();
			if(count($farmer_profile) > 0){			
			//	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');
				return json_encode($farmer_profile);		
			}else{
				
				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0",
                            "weight_per_bag" => "0",
                            "total_claimable" => "0",
                            "is_claimed" => "0",
                            "total_claimed" => "0",
                            "is_ebinhi" => "0",
                            "da_area" => "0",
                            "icts_rsbsa" => "0",
							"is_replacement" => "0",
							"replacement_area" => "0",
							"replacement_bags" => "0",
							"replacement_bags_claimed" => "0"
						));
					//	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
						return json_encode($farmer_profile_arr);
					}
				}
				else{

				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0",
                            "total_claimable" => "0",
                            "is_claimed" => "0",
                            "total_claimed" => "0",
                            "is_ebinhi" => "0",
                            "da_area" => "0",
                            "icts_rsbsa" => "0",
							"is_replacement" => "0",
							"replacement_area" => "0",
							"replacement_bags" => "0",
							"replacement_bags_claimed" => "0"
						));
					//	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
						return json_encode($farmer_profile_arr);
				}

				}catch(\Illuminate\Database\QueryException $ex){
		            return 'sql_error';
		        }   
	    	
     		
		   
	}

	public function API_fetch_farmerProfile_with_PreReg($code){
	
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			//$con = $this->set_rpt_db('ls_inspection_db',$database);	
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			//$con = $this->set_rpt_db('ls_seed_coop', $GLOBALS['season_prefix'].'rcep_paymaya');
			$con ="Connection Established!";
		if($con=='Connection Established!'){	
			
			$tbl_info = DB::table("information_schema.TABLES")
				->where("TABLE_SCHEMA", $database)
				->where("TABLE_NAME", "pre_registration")
				->groupBy("TABLE_NAME")
				->get();

				if(count($tbl_info)>0){
					$farmer_profile = DB::table($database.'.farmer_information')
					->select("pre_registration.qr_code", "pre_registration.contact_num as updated_contact","pre_registration.total_production","pre_registration.ave_weight_bag","pre_registration.area_harvested","pre_registration.sowing_date","pre_registration.crop_establishment","pre_registration.eco_system","pre_registration.claim_area","farmer_information.*")
					->leftJoin($database.".pre_registration", function($join){
						$join->on("pre_registration.rsbsa_control_no", "=", "farmer_information.rsbsa_control_no");
						$join->on("pre_registration.farmer_id", "=", "farmer_information.farmerID");
					})
					->where("farmer_information.rsbsa_control_no", 'LIKE', $rsbsa_reference."%")
					->orwhere("farmer_information.icts_rsbsa", "LIKE", $rsbsa_reference."%")
					->get();
					
				}else{
					$farmer_profile = DB::table($database.'.farmer_information')
					->select(DB::raw("CONCAT(null) as qr_code"),DB::raw("CONCAT(null) as updated_contact"),DB::raw("CONCAT(null) as total_production"),DB::raw("CONCAT(null) as ave_weight_bag"),DB::raw("CONCAT(null) as area_harvested"),DB::raw("CONCAT(null) as sowing_date"),DB::raw("CONCAT(null) as crop_establishment"),DB::raw("CONCAT(null) as eco_system"),DB::raw("CONCAT(null) as claim_area"), "farmer_information.*" )
					->where("rsbsa_control_no", 'LIKE', $rsbsa_reference."%")
					->orwhere("icts_rsbsa", "LIKE", $rsbsa_reference."%")
					->get();
					
				}

			
	
			
			
			if(count($farmer_profile) > 0){			
			//	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');
				
				return json_encode($farmer_profile);
				
				
			}else{
				
				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"qr_code"=> null,
							"updated_contact"=> null,
							"total_production"=> null,
							"ave_weight_bag"=> null,
							"area_harvested"=> "0",
							"sowing_date"=> null,
							"crop_establishment"=> null,
							"eco_system"=> null,
							"claim_area"=> null,
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0",
                            "weight_per_bag" => "0",
                            "total_claimable" => "0",
                            "is_claimed" => "0",
                            "total_claimed" => "0",
                            "is_ebinhi" => "0",
                            "da_area" => "0",
                            "icts_rsbsa" => "0"
							
						));
					//	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
						return json_encode($farmer_profile_arr);
					}
				}
				else{

				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"qr_code"=> null,
							"updated_contact"=> null,
							"total_production"=> null,
							"ave_weight_bag"=> null,
							"area_harvested"=> "0",
							"sowing_date"=> null,
							"crop_establishment"=> null,
							"eco_system"=> null,
							"claim_area"=> null,
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0",
                            "total_claimable" => "0",
                            "is_claimed" => "0",
                            "total_claimed" => "0",
                            "is_ebinhi" => "0",
                            "da_area" => "0",
                            "icts_rsbsa" => "0"
							
						));
					//	$con = $this->set_rpt_db('ls_inspection_db','rcep_delivery_inspection');	
						return json_encode($farmer_profile_arr);
				}

				}catch(\Illuminate\Database\QueryException $ex){
		            return 'sql_error';
		        }   
	    	
     		
		   
	}

	public function API_fetch_farmerProfile_oldDS2021($code){
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$farmer_profile = DB::table($database.'.farmer_profile')
				->where('lastName', '!=', '')
				->where('firstName', '!=', '')
				->where('rsbsa_control_no', '!=', '')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->orderBy('id')
				->get();
				
			if(count($farmer_profile) > 0){
				return json_encode($farmer_profile);
			}else{
				
				$farmer_profile_arr = array();
				array_push($farmer_profile_arr, array(
							"id" => '01',
							"farmerID" => '63'.substr($code,0,4).'000000001',
							"distributionID" => 'P63'.substr($code,0,4).'000006036',
							"lastName" => "Dela Cruz",
							"firstName" => "Juan",
							"midName" => "",
                            "extName" => "",
                            "fullName" => "Juan Dela Cruz",
                            "sex" => "Male",
                            "birthdate" => "",
                            "region" => "",
                            "province" => "",
                            "municipality" => "",
                            "barangay" => "",
                            "affiliationType" => "",
                            "affiliationName" => "",
                            "affiliationAccreditation" => "",
                            "isDaAccredited" => "0",
                            "isLGU" => "0",
                            "rsbsa_control_no" => ''.substr($code,0,2).'-'.substr($code,2,2).'-'.substr($code,4,2).'-001-000000',
                            "isNew" => "1",
                            "send" => "0",
                            "update" => "0",
                            "area" => "0",
                            "actual_area" => "0",
                            "season" => "WS",
                            "yield" => "0"
						));
						
				return json_encode($farmer_profile_arr);
			}
				
			
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
	}
	
	public function API_fetch_preList($code){
		try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$other_info = DB::table($database.'.other_info')
				->select('info_id', 'farmer_id', 'rsbsa_control_no', 'mother_fname', 'mother_lname', "mother_mname",
						 'mother_suffix', 'birthdate', 'is_representative', 'id_type', 'relationship', 
						 'have_pic', 'phone', 'send')
				->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
				->get();
				
				
			$other_info_arr = array();
			foreach($other_info as $row){
				$profile_check = DB::table($database.'.farmer_profile')
					//->select('rsbsa_control_no')
					->where('lastName', '!=', '')
					->where('firstName', '!=', '')
					->where('rsbsa_control_no', '!=', '')
					->where('rsbsa_control_no', $row->rsbsa_control_no)
					->orderBy('id')
					->first();
					
				if(count($profile_check) > 0){
					array_push($other_info_arr, array(
						"oi_info_id" => $row->info_id,
						"oi_farmer_id" => $row->farmer_id,
						"oi_rsbsa_control_no" => $row->rsbsa_control_no,
						"oi_mother_fname" => $row->mother_fname,
						"oi_mother_mname" => $row->mother_mname,
						"oi_mother_lname" => $row->mother_lname,
						"oi_mother_suffix" => $row->mother_suffix,
						"oi_birthdate" => $row->birthdate,
						"oi_is_representative" => $row->is_representative,
						"oi_id_type" => $row->id_type,
						"oi_relationship" => $row->relationship,
						"oi_have_pic" => $row->have_pic,
						"oi_phone" => $row->phone,
						"oi_send" => $row->send,
						
						"fp_farmerID" => $profile_check->farmerID,
						"fp_distributionID" => $profile_check->distributionID,
						"fp_lastName" => $profile_check->lastName,
						"fp_firstName" => $profile_check->firstName,
						"fp_midName" => $profile_check->midName,
						"fp_extName" => $profile_check->extName,
						"fp_fullName" => $profile_check->fullName,
						"fp_sex" => $profile_check->sex,
						"fp_birthdate" => $profile_check->birthdate,
						"fp_region" => $profile_check->region,
						"fp_province" => $profile_check->province,
						"fp_municipality" => $profile_check->municipality,
						"fp_barangay" => $profile_check->barangay,
						"fp_affiliationType" => $profile_check->affiliationType,
						"fp_affiliationName" => $profile_check->affiliationName,
						"fp_affiliationAccreditation" => $profile_check->affiliationAccreditation,
						"fp_isDaAccredited" => $profile_check->isDaAccredited,
						"fp_isLGU" => $profile_check->isLGU,
						"fp_rsbsa_control_no" => $profile_check->rsbsa_control_no,
						"fp_isNew" => $profile_check->isNew,
						"fp_send" => $profile_check->send,
						"fp_update" => $profile_check->update,
						"fp_actual_area" => $profile_check->actual_area,
						"fp_area" => $profile_check->area	
					));
				}
			}
			return json_encode($other_info_arr);
		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }   
	}
	
	public function get_farmer_photo(Request $request){
		try {
            $farmer_profile = DB::table($request->prv_code.".farmer_profile")
			->where('rsbsa_control_no', $request->rsbsa_number)
			->first();
			return $farmer_profile->distributionID;
        } catch (\Exception $e) {
            return "sql_error";
        }
	}

    public function provinceList(Request $request){
        $provinces = DB::connection('mysql')
            ->table('lib_provinces')
            ->select('provDesc', 'provCode')
            ->where('regCode', '=', $request->region)
            ->orderBy('provDesc', 'asc')
            ->get();
        $return_str= '';
        foreach($provinces as $province){
            $return_str .= "<option value='$province->provCode'>$province->provDesc</option>";
        }
        return $return_str;
    }

    public function MunicipalityList(Request $request){
        $municipalities = DB::connection('mysql')
            ->table('lib_municipalities')
            ->select('citymunDesc', 'citymunCode')
            ->where('provCode', '=', $request->province)
            ->orderBy('citymunDesc', 'asc')
            ->get();
        $return_str= '';
        foreach($municipalities as $municipality){
            $return_str .= "<option value='$municipality->citymunCode'>$municipality->citymunDesc</option>";
        }
        return $return_str;
    }

    public function SelectedEmployeeProfile(Request $request){
        $profile = DB::connection('hris_db')->table('employees')
                ->select('emp_station','emp_division','emp_office','emp_unit')
                ->where('emp_idno', '=', $request->id_number)
                ->first();
        
        $branch =  $profile->emp_station == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_stations')->where('id_station', '=', $profile->emp_station)->first()->station_abbr.' - '.DB::connection('hris_db')->table('lib_stations')->where('id_station', '=', $profile->emp_station)->first()->station_name;               
        $division =  $profile->emp_division == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_divisions')->where('id_division', '=', $profile->emp_division)->first()->division_abbr.' - '.DB::connection('hris_db')->table('lib_divisions')->where('id_division', '=', $profile->emp_division)->first()->division_name;
        $office =  $profile->emp_office == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_offices')->where('id_office', '=', $profile->emp_office)->first()->office_abbr.' - '.DB::connection('hris_db')->table('lib_offices')->where('id_office', '=', $profile->emp_office)->first()->office_name;
        $unit =  $profile->emp_unit == 0 ? 'Not Available.' : DB::connection('hris_db')->table('lib_units')->where('id_unit', '=', $profile->emp_unit)->first()->unit_abbr.' - '.DB::connection('hris_db')->table('lib_units')->where('id_unit', '=', $profile->emp_unit)->first()->unit_name;
    
        $data = array(
            'division' => $division,
            'office' => $office,
            'branch' => $branch,
            'unit' => $unit
        );

        return $data;
    }
	
	/**NEW */
    public function getProvinceUpdateInspector(Request $request){
        $provinces = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('region', '=', $request->region)
            ->groupBy('province')
            ->get();
        $province_str = '';
        foreach($provinces as $province){
            $province_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $province_str;
    }
    public function getMunicipalityUpdateInspector(Request $request){
        $municipalities = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('region', '=', $request->region)
            ->where('province', '=', $request->province)
            ->groupBy('municipality')->get();
        $municipalities_str = '';
        foreach($municipalities as $municipality){
            $municipalities_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $municipalities_str;
    }
    /**NEW */

    public function getProvinceDropoffDetails(Request $request){
        $provinces = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('region', '=', $request->region)
			->where('inspectorAllocated', '=', 0)
            ->groupBy('province')
            ->get();
        $province_str = '';
        foreach($provinces as $province){
            $province_str .= "<option value='$province->province'>$province->province</option>";
        }
        return $province_str;
    }

    public function get_coop_name($number){

        $coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')
			->where("accreditation_no","LIKE",$number)
            ->first();
			
        $ctr = DB::connection('seed_coop_db')->table('tbl_cooperatives')
			->where("accreditation_no",$number)
            ->count();
        return ($ctr>0) ? $coop->coopName: "N/A";
    }

    public function get_seedtags($token){
		
		if($token=="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9"){
			
            $seedTags = DB::connection('delivery_inspection_db')->table('tbl_rla_details')
            ->groupBy('coop_name')
			->groupBy('labNo')
			->groupBy('lotNo')
			->get();

			$count = 1;
			foreach($seedTags as $result){
			
				$data[] = array(
					'number' => $count,
					'coopAccreditation' => $result->coopAccreditation,
					'seedTag' => $result->labNo.'/'.$result->lotNo,
					'coop_name' =>$result->coop_name,
					'seed_variety' =>$result->seedVariety,
					'seed_grower' =>$result->sg_name
				);
				$data[] .="<br>";
				$count++;
			}
			return json_encode($data);
		}
		else{
			return json_encode("Access denied!");
		}
    }
    public function get_seedtags_api($season, $year, $token){
		
		if($token=="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9"){
			
        $search = DB::table($GLOBALS['season_prefix']."rcep_api" . '.tbl_api')
                ->select('*')
                ->where('season', "LIKE",  $season)
                ->where('year',  $year . '%')
				->first();
			
        $ctr = DB::table($GLOBALS['season_prefix']."rcep_api" . '.tbl_api')
                ->select('*')
                ->where('season', "LIKE",  $season)
                ->where('year',  $year . '%')
				->count();
				if($ctr>0){
					return redirect($search->url);
				}
				else{
					return json_encode("Access denied!");
				}
			//return json_encode($data);
		}
		else{
			return json_encode("Access denied!");
		}
    }
    public function getMunicipalitiesDropoffDetails(Request $request){
        $municipalities = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('province', '=', $request->province)
			->where('inspectorAllocated', '=', 0)
            ->groupBy('municipality')->get();
        $municipalities_str = '';
        foreach($municipalities as $municipality){
            $municipalities_str .= "<option value='$municipality->municipality'>$municipality->municipality</option>";
        }
        return $municipalities_str;
    }

    public function searchDropOffDelivery(Request $request){
        $dropoff = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('region', '=', $request->region)
                ->where('province', '=', $request->province)
                ->where('municipality', '=', $request->municipality)
				->where('inspectorAllocated', '=', 0)
				->where('is_cancelled', '!=', 1)
                ->groupBy('dropOffPoint')->get();
        $dropoff_str = '';
        foreach($dropoff as $drop_off){
            $dropoff_str .= "<option value='$drop_off->deliveryId'>$drop_off->dropOffPoint</option>";
        }
        return $dropoff_str;
    }

    public function SelectedDropOffDetails(Request $request){
        $dropoff = DB::connection('delivery_inspection_db')->table('tbl_delivery')                
                ->where('tbl_delivery.deliveryId', '=', $request->deliveryId)
                ->first();
        //dd($dropoff);
        //get all batch tickets with the same dropoff
        $bacthes = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select(DB::raw('batchTicketNumber, seedVariety, SUM(totalBagCount) as total_bags'), "deliveryDate")                
            ->where('tbl_delivery.region', '=', $dropoff->region)
            ->where('tbl_delivery.province', '=', $dropoff->province)
            ->where('tbl_delivery.municipality', '=', $dropoff->municipality)
            ->where('tbl_delivery.prv_dropoff_id', '=', $dropoff->prv_dropoff_id)
			->where('tbl_delivery.inspectorAllocated', '=', 0)
			->where('tbl_delivery.is_cancelled', '=', 0)
            ->groupBy('batchTicketNumber')
            ->get();

            $deliveryDate = $dropoff->deliveryDate;
        //append string
        $batch_str = '';
        foreach($bacthes as $batch){
            $batch_str .= "<label><input type='checkbox' name='batch_tickets[]' value='$batch->batchTicketNumber' checked/> $batch->batchTicketNumber ($batch->total_bags bags)</label><br>";
            $deliveryDate = $batch->deliveryDate;
        }
        
        $data = array(
            'delivery_date' => date("F j, Y g:i A", strtotime($deliveryDate)),
            'batch_string' => $batch_str
        );
        return $data;
    }

    public function getTicketDuration(Request $request){
        $ticket = DB::connection('delivery_inspection_db')->table('tbl_schedule')                
                ->where('tbl_schedule.scheduleId', '=', $request->scheduleId)
                ->first();
        
        $data = array(
            'duration_from' => date("m/d/Y", strtotime($ticket->duration_from)),
            'duration_to' => date("m/d/Y", strtotime($ticket->duration_to))
        );

        return $data;
    }

    public function RegisteredFarmerDetails(Request $request){
        $profile = DB::connection('registry_db')->table('farmer_profiles')->where('id', '=', $request->profileID)->first();
        $details = DB::connection('registry_db')->table('farm_details')->where('id', '=', $profile->farmer_details_id)->first();
        $affiliations = DB::connection('registry_db')->table('farmer_affiliations')->where('id', '=', $profile->farmer_affiliation_id)->first();

        if($profile->farmer_suffix_name != ''){
            $full_name = $profile->farmer_first_name.' '.$profile->farmer_middle_name.' '.$profile->farmer_last_name.' '.$profile->farmer_suffix_name;
        }else{
            $full_name = $profile->farmer_first_name.' '.$profile->farmer_middle_name.' '.$profile->farmer_last_name;
        }

        switch($affiliations->farm_accreditation){
            case 1:
                $farm_accreditation = "None";
                break;
            case 2:
                $farm_accreditation = "SEC";
                break;
            case 3:
                $farm_accreditation = "CDA";
                break;
            case 4:
                $farm_accreditation = "DOLE";
                break;
            case 5:
                $farm_accreditation = $affiliations->farm_accreditation_others;
                break;
        }

        switch($affiliations->affiliation_type){
            case 1:
                $affiliation_type = "IA - Irrigators Assocation";
                break;
            case 2:
                $affiliation_type = "COOP - Farmer's Cooperative";
                break;
            case 3:
                $affiliation_type = "SWISA - Small Water Irrigation Ssystem Association";
                break;
            case 4:
                $affiliation_type = $affiliations->farm_accreditation_others;
                break;
        }
        
        $data = array(
            'full_name' => $full_name,
            'sex' => $profile->farmer_gender == 1 ? 'Male' : 'Female',
            'contact_number' => $profile->farmer_contact_number == '' ? 'Not Available' : $profile->farmer_contact_number,
            'birth_date' => date("F j, Y", strtotime($profile->farmer_birth_date)),
            'farm_area' => $details->farm_area.' ha',
            'rice_area' => $details->rice_area.' ha',
            'tenurial_status' => $details->tenurial_status == 1 ? 'Owner' : 'Rent / Lease',
            'tenurial_type' => DB::connection('registry_db')->table('farmer_roles')->where('id', '=', $details->tenurial_type)->first()->farmer_role_description,
            'affiliation_type' => $affiliation_type,
            'affiliation_name' => $affiliations->affiliation_name,
            'farm_accreditation' => $farm_accreditation
        );

        return $data;
    }

    public function APIRegisteredFarmers(Request $request){
        return Datatables::of(DB::connection('registry_db')->table('farmer_profiles')
                    ->select('farmer_profiles.farmerID',
                             'farmer_profiles.farmer_first_name',
                             'farmer_profiles.farmer_middle_name',
                             'farmer_profiles.farmer_last_name',
                             'farmer_profiles.farmer_suffix_name',
                             'farm_details.farm_brgy',
                             'farm_details.farm_municipality',
                             'farm_details.farm_province')
                    ->join('farm_details', 'farmer_profiles.farmerID', '=','farm_details.farmerID')
            ->orderBy('farmer_profiles.created_at', 'desc')
        )
        ->addColumn('full_name', function($row){
            $full_name = $row->farmer_first_name.' '.$row->farmer_last_name;
            return $full_name; 
        })
        ->addColumn('address', function($row){
            $province = DB::connection('mysql')->table('lib_provinces')->where("provCode", "=", "$row->farm_province")->value('provDesc');
            $municipality = DB::connection('mysql')->table('lib_municipalities')->where("citymunCode", "=", "$row->farm_municipality")->value('citymunDesc');
        
            $address='';
            if($row->farm_brgy != ''){
                $address = $province.', '.$municipality.', '.$row->farm_brgy;
            }else{
                $address = $province.', '.$municipality;
            }

            return $address;
            //return $province;
        })
        ->addColumn('action', function($row){
            return '<a href="" data-toggle="modal" data-target="#farm_details" data-id="'.$row->farmerID.'" class="btn btn-round btn-success" style="margin-right:0"> <i class="fa fa-map"></i> </a>
            <a href="" class="btn btn-round btn-primary" style="margin-right:0"> <i class="fa fa-bar-chart"></i> </a>'; 
        })
        ->make(true);
    }

    public function ConfirmedDeliveries(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('status', '=', 0)
            ->orderBy('deliveryDate', 'desc')
        )
        ->addColumn('seed_grower_fld', function($row){
            $seed_grower = DB::connection('seed_grower_db')->table('seed_growers_all')->where('Code_Number', '=', $row->sgAccreditation)->first();
            return "<b>$seed_grower->Name</b><hr style='margin-top: 5px;margin-bottom: 5px;border: 0;border-top: 1px solid #cdcdd4;'>
                    <b>Seed Variety: </b>$row->seedVariety ($row->seedClass)<br>
                    <b>Seed Tag: </b>$row->seedTag<br>
                    <b>Weight: </b>$row->totalWeight / $row->weightPerBag per bag";
        })
        ->addColumn('action', function($row){
            return '<a href="" data-toggle="modal" data-target="#verify_allocation_modal" data-id="'.$row->ticketNumber.'" class="btn btn-success allocate-open-modal" style="margin-right:0"> <i class="fa fa-eye"></i> Designate Inspector </a><br>
                    <a href="" data-id="'.$row->ticketNumber.'" class="btn btn-danger" style="margin-right:0"> <i class="fa fa-thumbs-down"></i> Cancel Delivery </a>'; 
        })
        ->make(true);
    }

    public function getInspectorTickets(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')
            ->table('tbl_schedule')
            ->select('tbl_schedule.userId','tbl_schedule.ticketNumber',
                     'tbl_schedule.duration_from', 'tbl_schedule.duration_to',
                     'tbl_schedule.pmo_remarks', 'tbl_delivery.coopAccreditation', 
                     'tbl_delivery.seedTag', 'tbl_delivery.seedVariety',
                     'tbl_delivery.totalWeight', 'tbl_delivery.weightPerBag',
                     'tbl_delivery.deliveryDate', 'tbl_delivery.deliverTo',
                     'tbl_delivery.deliverTo', 'tbl_delivery.status',
                     'tbl_schedule.scheduleId')
            ->join('tbl_delivery', 'tbl_schedule.ticketNumber', '=', 'tbl_delivery.ticketNumber')
            ->where('tbl_schedule.userId', '=', $request->userId)
        )
        ->addColumn('coopName_fld', function($row){
            $coopName = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('coopId', '=', $row->coopAccreditation)->first()->coopName;
            $weight = $row->totalWeight.' / '.$row->weightPerBag.' per bag';
            return "<b><u>$coopName</u></b><br>
                    Seed Variety: $row->seedVariety<br>
                    Seed Tag: $row->seedTag<br>
                    Weight: $weight<br>
                    Deliver To: $row->deliverTo";
        })
        ->addColumn('duration_fld', function($row){
            return "From: ".date("F j, Y", strtotime($row->duration_from)).'<br>'.
                   "To: ".date("F j, Y", strtotime($row->duration_to));
        })
        ->addColumn('status_fld', function($row){
            $delivery_status = "";
            if($row->status == "0"){
                $delivery_status = 'Pending for Inspection';
            }elseif($row->status == "1"){
                $delivery_status = 'Inspection Completed';
            }elseif($row->status == "2"){
                $delivery_status = 'Rejected';
            }
            return $delivery_status;
        })
        ->addColumn('action', function($row){
            return '<a href="" data-toggle="modal" data-target="#schedule_duration_modal" data-id="'.$row->scheduleId.'" class="btn btn-round btn-warning" style="margin-right:0"> <i class="fa fa-calendar"></i></a>
            <a href="" class="btn btn-round btn-primary" style="margin-right:0"> <i class="fa fa-bar-chart"></i> </a>'; 
        })
        ->make(true);
    }

    public function getInspectorProfiles(Request $request){
        return Datatables::of(DB::connection('mysql')->table('users')
            ->select('users.firstName', 'users.middleName', 'users.lastName','users.extName','users.userId')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', 14)
            ->orderBy('firstName', 'asc')
            ->orderBy('middleName', 'asc')
            ->orderBy('lastName', 'asc')
        )
        ->addColumn('action', function($row){
            $url = $this->url->to('inspection/profile/'.$row->userId);
            return "<a href='$url' class='btn btn-warning btn-sm'><i class='fa fa-folder-open'></i></a>
                    <a href='$url' class='btn btn-danger btn-sm' style='margin-left: -5px;'><i class='fa fa-undo'></i></a>";
        })
        ->make(true);
    }

    public function RegionSummary(Request $request){
        $provinces = DB::connection('logs_db')
            ->table('imported_tbl_logs')->get();
        
        $total_area = 0;
        $total_farmer_count = 0;
        foreach($provinces as $province){
            //search for the database
            \Config::set('database.connections.reports_db.database', $province->db_name);
            DB::purge('reports_db');

            try {
                $tbl_check = DB::connection('reports_db')->table("farmer_profile")->first();
                if(count($tbl_check) > 0){
                    $table_conn = "established_connection";
                }else{
                    $table_conn = "no_table_found";
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                $table_conn = "no_table_found";
            }


            /*if($table_conn == "established_connection"){
                try{
                    $area_category1_result = DB::connection('reports_db')->table("farmer_profile")
                            ->select(DB::raw('COUNT(farmer_profile.farmerID) as total_farmers'))
                            ->join('area_history', 'farmer_profile.farmerID', '=', 'area_history.farmerId')
                            ->where('area_history.area', '<=', 2)
                            ->where('area_history.region', '>', 1.5)
                            ->first()->total_farmers;

                    $total_farmer_count += (float) $area_category1_result->total_farmers;
    
                }catch(\Illuminate\Database\QueryException $ex){
                    $table_conn = "no_table_found";
                }
            }*/
        }

        $data_arr = array(
            'area_cat1_res' => 'sadasdsad'
        );

        return $data_arr;
    }
	
	//BPI API FUNCTION
    public function bpiApiFetch(){

    }


	public function bpiApiFetchStop(){      
		
		$con_result = 0;
        	for ($x = 0; $x <=3; $x++){
        		$connected = @fsockopen("bpinsqcs.da.gov.ph", 443);
        		if($connected){
        			$con_result=1;
        			fclose($connected);
        			break;
        		}
        	}

		 if($con_result==1){
        $url = "https://bpinsqcs.da.gov.ph/nsqcs-api/test-results-api.php";
        $bpiApi = stripslashes(file_get_contents($url));
        
        DB::table($GLOBALS['season_prefix'].'rcep_bpi_api.tbl_rla_api')->truncate();

        $bpiApi = str_replace("\n","",$bpiApi);
        $bpiApi = str_replace("[]","",$bpiApi);
        $rlaArray =explode("{", $bpiApi); //WHOLE DATA
    
            unset($rlaArray[0]);
            $bpi = array();
           // dd($rlaArray);
            $i = 0;
        foreach ($rlaArray as $key  => $value) {
            $w = explode(",", $value);
                foreach ($w as $value2) {
                $d = explode(":", $value2);
                    if(isset($d[0])){
                         $title = str_replace('"', "", $d[0]);
                    }else{
                         $title = "";
                    }


                    if(isset($d[1])){
                        $val =  str_replace('"', "", $d[1]);
                        $val = str_replace('}', "", $val);
                    }else{
                        $val = "";
                    }

                    if($title != "" AND $val != ""){
                        $title = trim($title);
                        $val = trim($val);
                         $bpi[$key][$title] = $val;
                    }
                }
        
                if(isset($bpi[$key]['CoopAccreNum'])){

                    if(isset($bpi[$key]['SeedProg'])){$SeedProg=$bpi[$key]['SeedProg'];}else{$SeedProg='';}
                    if(isset($bpi[$key]['RegSrcID'])){$RegSrcID=$bpi[$key]['RegSrcID'];}else{$RegSrcID='';}
                    if(isset($bpi[$key]['LabSrcID'])){$LabSrcID=$bpi[$key]['LabSrcID'];}else{$LabSrcID='';}
                    if(isset($bpi[$key]['LotNum'])){$LotNum=$bpi[$key]['LotNum'];}else{$LotNum='';}
                    if(isset($bpi[$key]['CoopSrcID'])){$CoopSrcID=$bpi[$key]['CoopSrcID'];}else{$CoopSrcID='';}
	                    if(isset($bpi[$key]['CoopAccreNum'])){
	                    	$CoopAccreNum=$bpi[$key]['CoopAccreNum'];
       						$CoopAccreNum = str_replace('RcI', 'Rcl', $CoopAccreNum);

		                }else{$CoopAccreNum='';}

                    if(isset($bpi[$key]['CoopExpDate'])){$CoopExpDate=$bpi[$key]['CoopExpDate'];}else{$CoopExpDate='';}
                    if(isset($bpi[$key]['SGSrcID'])){$SGSrcID=$bpi[$key]['SGSrcID'];}else{$SGSrcID='';}
                    if(isset($bpi[$key]['VarCode'])){$VarCode=$bpi[$key]['VarCode'];}else{$VarCode='';}
                    if(isset($bpi[$key]['VarSrcID'])){$VarSrcID=$bpi[$key]['VarSrcID'];}else{$VarSrcID='';}
                    if(isset($bpi[$key]['BagsRepresented'])){$BagsRepresented=$bpi[$key]['BagsRepresented'];}else{$BagsRepresented='';}
                    if(isset($bpi[$key]['Status'])){$Status=$bpi[$key]['Status'];}else{$Status='';}
                    if(isset($bpi[$key]['NumBagPass'])){$NumBagPass=$bpi[$key]['NumBagPass'];}else{$NumBagPass='';}
                    if(isset($bpi[$key]['NumBagReject'])){$NumBagReject=$bpi[$key]['NumBagReject'];}else{$NumBagReject='';}
                    if(isset($bpi[$key]['PercentGermination'])){$PercentGermination=$bpi[$key]['PercentGermination'];}else{$PercentGermination='';}
                    if(isset($bpi[$key]['PercentFresh'])){$PercentFresh=$bpi[$key]['PercentFresh'];}else{$PercentFresh='';}
                    if(isset($bpi[$key]['PercentMoisture'])){$PercentMoisture=$bpi[$key]['PercentMoisture'];}else{$PercentMoisture='';}
                    if(isset($bpi[$key]['CauseofReject'])){$CauseofReject=$bpi[$key]['CauseofReject'];}else{$CauseofReject='';}
                  
                  					/*
                                       DB::table($GLOBALS['season_prefix'].'rcep_bpi_api.tbl_rla_api')
                                        ->insert([
                                            'SeedProg' => $SeedProg,
                                            'Region' => $RegSrcID,
                                            'lab_no' => $LabSrcID,
                                            'lot_no' => $LotNum,
                                            'coop_name' => $CoopSrcID,
                                            'coop_accreditation' => $CoopAccreNum,
                                            'CoopExpDate' => $CoopExpDate,
                                            'seed_grower' => $SGSrcID,
                                            'variety' => $VarCode,
                                            'VarSrcID' => $VarSrcID,
                                            'bags_presented' => $BagsRepresented,
                                            'status' => $Status,
                                            'bags_passed' => $NumBagPass,
                                            'bags_reject' => $NumBagReject,
                                            'PercentGermination' => $PercentGermination,
                                            'PercentFresh' => $PercentFresh,
                                            'PercentMoisture' => $PercentMoisture,
                                            'CauseofReject' => $CauseofReject,
                                        ]); */ 
                                
                            $i++;}
        }
    
        if(isset(Auth::user()->username)){$user=Auth::user()->username;}else{$user='Scheduled Process';}

      DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'BPI_API_DOWNLOAD',
                'description' => $i.' DATA DOWNLOADED',
                'author' =>$user,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);  
    
       		
       	 $bpiData = DB::table($GLOBALS['season_prefix'].'rcep_bpi_api.tbl_rla_api')
       			->where('coop_accreditation', '!=', 'null')
       			->get(); 
    	foreach ($bpiData as $value) {
				 $sg = $this->split_name(trim($value->seed_grower));
					if(isset($sg)){
						$fn = $sg['first_name'];
						$mn = $sg['middle_name'];
						$ln = $sg['last_name'];
					}else{
						$fn ="-";
						$mn ="-";
						$ln ="-";
					}

					//echo $fn. ' '.$ln.'<br>';
       			// $this->updateRLA($value->coop_accreditation,$value->lab_no,$value->lot_no,$fn,$mn,$ln,$value->variety,$value->bags_passed,$value->CoopExpDate);
       		}      
		 }
    }

    public function updateRLA($coopAccreditation,$labNo,$lotNo,$sg_fn,$sg_mn,$sg_ln,$seedVariety,$bags,$CoopExpDate){

    	$getSG = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower') 
    		->where('full_name', 'like', '%'.$sg_fn.'%')
			->where('full_name', 'like', '%'.$sg_ln.'%')
			->where('coop_accred', 'like', '%'.$coopAccreditation.'%')
    		->where('is_active', 1)
    		->where('is_block', 0)
    		->first();
	
			if($getSG != null){
				$sgid = $getSG->sg_id;
				$sgName = $getSG->full_name;
			}else{
				$fullname = $sg_fn . ' '. substr(trim($sg_mn),0,1) . ' ' .$sg_ln;
				$insSG = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_seed_grower')
						->insertGetId([
							'coop_accred' => $coopAccreditation,
							'is_active' => 1,
							'is_block' => 0,
							'fname' => $sg_fn,
							'mname' => $sg_mn,
							'lname' => $sg_ln,
							'full_name' => $fullname,
						]);
				$sgid = $insSG;
				$sgName = $sg_fn . ' '. substr(trim($sg_mn), 0,1) . ' ' .$sg_ln; 
			}


			$getCoop = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives') 
			->where('accreditation_no', 'like', '%'.$coopAccreditation.'%')
    		->first();

			if($getCoop != null){
				$coopName = $getCoop->coopName;
				$moa = $getCoop->current_moa;
			}else{
				$coopName = '';
				$moa = '';
			}

        $checkCurrent = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
            ->where('coopAccreditation', 'like','%'.$coopAccreditation.'%')    
            ->where('labNo', 'like', '%'.$labNo.'%')
            ->where('lotNo', 'like', '%'.$lotNo.'%')
            ->where('sg_id', $sgid)
            ->where('seedVariety', 'like', '%'.$seedVariety.'%')
            ->first();
      
        if($checkCurrent != null){
            if($bags != $checkCurrent->noOfBags){
                /*
                //UPDATE RLA
                DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
				->where('coopAccreditation', 'like','%'.$coopAccreditation.'%')    
				->where('labNo', 'like', '%'.$labNo.'%')
				->where('lotNo', 'like', '%'.$lotNo.'%')
				->where('sg_id', $sgid)
				->where('seedVariety', 'like', '%'.$seedVariety.'%')
                ->update([
                    'noOfBags' => $bags
                ]); */
            }                   
        }else{      
        	/*
	        	//INSERT
	        	DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
                ->insert([
                    'coop_name' => $coopName,
                    'coopAccreditation' => $coopAccreditation,
                    'sg_id' => $sgid,
                    'sg_name' => $sgName,
                    'certificationDate' => $CoopExpDate,
                    'labNo' => $labNo,
                    'lotNo' => $lotNo,
                    'noOfBags' => $bags,
                    'seedVariety' => $seedVariety,
                    'moaNumber' => $moa,
                    'is_rejected' => 0
                ]); */	
        }
    }

    public function split_name($name) {
	    $parts = array();
	    while ( strlen( trim($name)) > 0 ) {
	        $name = trim($name);
	        $string = preg_replace('#.*\s([\w-]*)$#', '$1', $name);
	        $parts[] = $string;
	        $name = trim( preg_replace('#'.preg_quote($string,'#').'#', '', $name ) );
	    }
	    if (empty($parts)) {
	        return false;
	    }
	    $parts = array_reverse($parts);
	    $name = array();
	    $name['first_name'] = $parts[0];
	    $name['middle_name'] = (isset($parts[2])) ? $parts[1] : '';
	    $name['last_name'] = (isset($parts[2])) ? $parts[2] : ( isset($parts[1]) ? $parts[1] : '');
	    return $name;
	}



}

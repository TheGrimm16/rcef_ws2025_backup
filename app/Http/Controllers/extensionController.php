<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;
use Auth;


class extensionController extends Controller
{
	public function home_page(){
		return view('extension.home');
	}


	public function connectDatabase(Request $request){
		$season = $request->season;		 
		$delivery_inspection_db = "rcep_delivery_inspection";
		if($season == "ds2020"){
			$delivery_inspection_db = "ds2020_".$delivery_inspection_db;
		}


			$con = $this->changeConnection($season,$delivery_inspection_db);

			if($con == "success"){
				if($season == "ds2020"){
					$provinces = DB::connection("extension_connector")->table("tbl_actual_delivery")->groupBy("province")->get();
				}else{
					$provinces = DB::connection("extension_connector")->table("tbl_actual_delivery")->groupBy("province")->orderBy("prv")->get();	
				}
				 
			        return array(
			        	"status" => "1",
			        	"message" => "success",
			        	"data" => array(
			        		"season" => $season,
			        		"info" => $provinces
			        	)
			        );
			}else{
				return array(
			        	"status" => "0",
			        	"message" => "failed",
			        	"data" => array(
			        		"season" => $season,
			        		"info" => ""
			        	)
			        );
			}
	}


	public function municipality_list(Request $request){
		$season = $request->season;
		$delivery_inspection_db = "rcep_delivery_inspection";
		if($season == "ds2020"){
			$delivery_inspection_db = "ds2020_".$delivery_inspection_db;
		}

		$con = $this->changeConnection($season,$delivery_inspection_db);
		if($con == "success"){
			if($season == "ds2020"){
				$municipalities = DB::connection("extension_connector")->table("tbl_actual_delivery")->groupBy("municipality")->where("province", $request->province)->get();
			}else{
				$municipalities = DB::connection("extension_connector")->table("tbl_actual_delivery")->groupBy("municipality")->where("province", $request->province)->orderby("prv")->get();	
			}
			return json_encode($municipalities);
		}else{
			return "";
		}
	}

	 

	private function changeConnection($season,$database_name){
			$conn_string = array();

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

		$conn_string['ds2020']['host'] = "localhost";
		$conn_string['ds2020']['port'] = "3306";
		$conn_string['ds2020']['user'] = "jpalileo";
		$conn_string['ds2020']['password'] = "P@ssw0rd";

			try{
				\Config::set('database.connections.extension_connector.host', $conn_string[$season]['host']);
		        \Config::set('database.connections.extension_connector.port', $conn_string[$season]['port']);
		        \Config::set('database.connections.extension_connector.database', $database_name);
		        \Config::set('database.connections.extension_connector.username', $conn_string[$season]['user']);
		        \Config::set('database.connections.extension_connector.password', $conn_string[$season]['password']);
		        DB::purge('extension_connector');
		        DB::connection('extension_connector')->getPdo();
			
		        return "success";
			} catch (\Exception $e) {
				//dd($e);
        		return "failed";		
        	}
	}


	public function load_search(Request $request){

		$season = $request->season;
		$province = $request->province;
		$municipality = $request->municipality;
		$filter = $request->text_filter;
		$table_status = "failed";


		$delivery_inspection_db = "rcep_delivery_inspection";
		if($season == "ds2020"){
			$delivery_inspection_db = "ds2020_".$delivery_inspection_db;
		}
		$con = $this->changeConnection($season,$delivery_inspection_db);
		if($con == "success"){
			$prv = DB::connection("extension_connector")->table("lib_prv")->where("province", $province)->where("municipality", 'LIKE', "%".$municipality."%")->first();
			$tbl_array = array();
			$prv_id = $prv->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);

			//$rsbsa_ref = substr($prv_id, 0,2)."-".substr($prv_id, 2,2)."-".substr($prv_id, 4,2);


					//CHECK IF TABLE EXIST
					$check = $this->checkTable($season, $prv_db);
					//dd($check);
						if($check == "0"){
							$table_status = $this->createTable($season, $prv_db);
							//dd($table_status);
						}elseif($check == "failed"){
							return json_encode("failed error: check");
						}elseif($check == "1"){
							$table_status = "success";
						}

						if($table_status == "success"){
							//POST DB TO TABLE
								if($season == "ds2020"){
									$prv_con = $this->changeConnection($season,"ds2020_".$prv_db);	
									$profile = DB::connection('extension_connector')->table("farmer_profile")
										->select("farmer_profile.*", "released.date_released")
										->join("released", "farmerID", "=", "farmer_id")
										->where("rsbsa_control_no", 'LIKE', '%'.$filter.'%')
										->orWhere("firstName", 'LIKE', '%'.$filter.'%')
										->orWhere("midName", 'LIKE', '%'.$filter.'%')
										->orWhere("lastName", 'LIKE', '%'.$filter.'%')
										//->where(DB::raw("CHAR_LENGTH(rsbsa_control_no) <= 21"))
										->orderBy("farmer_profile.rsbsa_control_no", "ASC")
										->take(3)
										->get();
								}elseif($season == "ds2021" || $season == "ws2020"){
									$prv_con = $this->changeConnection($season,$prv_db);
									$profile = DB::connection('extension_connector')->table("farmer_profile")
										->select("farmer_profile.*", "released.date_released")
										->join("released", function($join){
											$join->on("farmerID", "=", "farmer_id");
											$join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
										})
										->where("farmer_profile.rsbsa_control_no", 'LIKE', '%'.$filter.'%')
										->orWhere("farmer_profile.firstName", 'LIKE', '%'.$filter.'%')
										->orWhere("farmer_profile.midName", 'LIKE', '%'.$filter.'%')
										->orWhere("farmer_profile.lastName", 'LIKE', '%'.$filter.'%')
										//->where(DB::raw("CHAR_LENGTH(rsbsa_control_no) <= 21"))
										->orderBy("farmer_profile.rsbsa_control_no", "ASC")										
										->take(3)
										->get();

										//dd($profile);
								}
								else{
									$prv_con = $this->changeConnection($season,$prv_db);
									$profile = DB::connection('extension_connector')->table("farmer_profile")
										->select("farmer_profile.*", "released.date_synced")
										->join("released", function($join){
											$join->on("farmerID", "=", "farmer_id");
											$join->on("farmer_profile.rsbsa_control_no", "=", "released.rsbsa_control_no");
										})
										->where("farmer_profile.rsbsa_control_no", 'LIKE', '%'.$filter.'%')
										->orWhere("farmer_profile.firstName", 'LIKE', '%'.$filter.'%')
										->orWhere("farmer_profile.midName", 'LIKE', '%'.$filter.'%')
										->orWhere("farmer_profile.lastName", 'LIKE', '%'.$filter.'%')
										//->where(DB::raw("CHAR_LENGTH(rsbsa_control_no) <= 21"))
										->orderBy("farmer_profile.rsbsa_control_no", "ASC")										
										->take(3)
										->get();

										//dd($profile);
								}
//DS2021 - date_released
//WS2021 - date_synced
//DS2020 - date_released

								//dd($profile);
									$previous_rsbsa = "";
									foreach ($profile as $farmer_profile){
										if($season == "ds2020"){
											$clean_rsbsa = str_replace("-", "", $farmer_profile->rsbsa_control_no);
											if($previous_rsbsa == $clean_rsbsa){
												continue;
											}else{
												$previous_rsbsa = $clean_rsbsa;
											}
											//$save_this_rsbsa = $farmer_profile->rsbsa_control_no;
										}else{
											$clean_rsbsa = str_replace("-", "", $farmer_profile->rsbsa_control_no);
											if($previous_rsbsa == substr($clean_rsbsa, 0,15)){
												continue;
											}else{
												$previous_rsbsa = substr($clean_rsbsa, 0,15);
											}	
											//$save_this_rsbsa = substr($clean_rsbsa, 0, 2)."-".substr($clean_rsbsa, 2, 2)."-".substr($clean_rsbsa, 4, 2)."-".substr($clean_rsbsa, 6, 3)."-".substr($clean_rsbsa, 9, 8);
									

										}
								


							            $id = 0;
							        	$kp1 = 0;
										$kp2 = 0;
							        	$kp3 = 0;
							        	$calendar = 0;
							        	$ksl = 0;
							        	$phone = "";
										$birthdate ="";
						        		
						        		$ext_db = "rcep_extension_db";
						        		if($season == "ds2020"){
											$ext_db = "ds2020_".$ext_db;
										}

						        	$prv_con = $this->changeConnection($season,$ext_db);
						        	if($prv_con == "success"){
						        		$get_extension_data = DB::connection("extension_connector")->table($prv_db)
						        		->where("rsbsa_control_no", $farmer_profile->rsbsa_control_no)
						        		->where("farmer_id", $farmer_profile->farmerID)
						        		->where("first_name", $farmer_profile->firstName)
										->first();						        		
										if(count($get_extension_data)>0){
											$id = $get_extension_data->id;
											$kp1 = $get_extension_data->kp1;
											$kp2 = $get_extension_data->kp2;
											$kp3 = $get_extension_data->kp3;
											$calendar = $get_extension_data->calendar;
											$ksl = $get_extension_data->ksl;
										}
						        	}

						        if($season != "ds2020"){
						        	$con_oth = $this->changeConnection($season,$prv_db);
							        if($con_oth == "success"){
							        	$contact_info = DB::connection('extension_connector')->table("other_info")
											->where("rsbsa_control_no", $farmer_profile->rsbsa_control_no)
											->where("farmer_id", $farmer_profile->farmerID)
											->first();
										if(count($contact_info)>0){
											$phone = $contact_info->phone;
											$birthdate = $contact_info->birthdate;
											$mothers_name = $contact_info->mother_lname.", ".$contact_info->mother_fname;
										}
							        }
						        }else{
						        	$mothers_name = ", ";
						        }
						
						        if($farmer_profile->sex != ""){
						        	if(strtoupper(substr($farmer_profile->sex, 0,1))=="M"){
						        	$gender = "Male";
							        }else{
							        	$gender = "Female";
							        }
						        }else{
						        	$gender = "";
						        }
						        
						        
						        if($phone == ""){$phone = "N/A";}
						        if($birthdate == ""){$birthdate = "N/A";$age = "N/A";}
						        else{
						        	$today = date("Y-m-d");
						        	$bday = date("Y-m-d", strtotime($birthdate));
						        	$age = date_diff(date_create($bday), date_create('now'))->y;
						        }
						        if($mothers_name == ", "){$mothers_name = "N/A";}
								
						        if($season == "ws2021"){
						        	$date_synced = $farmer_profile->date_synced;
						        }else{
						        	$date_synced = $farmer_profile->date_released;
						        }

								array_push($tbl_array, array(
									"id" => $id,
									"rsbsa_control_no" => $farmer_profile->rsbsa_control_no,
									"farmerID" => $farmer_profile->farmerID,
									"firstName" => $farmer_profile->firstName,
									"midName" => $farmer_profile->midName,
									"lastName" => $farmer_profile->lastName,
									"province" => $province,
									"municipality" => $municipality,
									"prv_id" => $prv_id,
									"contact" => $phone,
									"birthdate" => $birthdate,
									"age" => $age,
									"mother_name" => $mothers_name,
									"gender" => $gender,
									"kp1_label" => "Gabay sa <br> &emsp; &nbsp;  Makabagong <br> &emsp; &nbsp; Pagpapalayan",
									"kp1" => $kp1,
									"kp2_label" => "yunPALAYun <br> &emsp; &nbsp; Handout",
									"kp2" => $kp2,
									"kp3_label" => "Gabay Sa <br> &emsp; &nbsp;  Pagsasabog-tanim",
									"kp3" => $kp3,
									"cal_label" => "TeknoKalendaryo",
									"calendar" => $calendar,
									"ksl_label" => "Technical Briefing",
									"ksl" => $ksl,
									"date_synced" => date("Y-m-d", strtotime($date_synced))

								));
								}
							if(count($tbl_array)>0){
								return json_encode($tbl_array);
							}else{
								array_push($tbl_array, array(
									"id" => 0,
									"rsbsa_control_no" => "NO_DATA",
									"farmerID" => "",
									"firstName" => "",
									"midName" => "",
									"lastName" => "",
									"province" => "",
									"municipality" => "",
									"prv_id" => 0,
									"kp1" => 0,
									"kp2" => 0,
									"kp3" => 0,
									"calendar" => 0,
									"ksl" => 0

								));

								return json_encode($tbl_array);
							}
					




						}else{
							return json_encode("failed error: created tbl");
						}
			}else{
				return json_encode("failed error: connect");
			}
				
	}



	public function load_card(Request $request){
		$season = $request->season;
		$province = $request->province;
		$municipality = $request->municipality;
		$card_count = $request->card_count;

		$table_status = "failed";
		$con = $this->changeConnection($season,'rcep_delivery_inspection');
		if($con == "success"){
			$prv = DB::connection("extension_connector")->table("lib_prv")->where("province", $province)->where("municipality", $municipality)->first();
			//PRV DATA
			$prv_id = $prv->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
					//CHECK IF TABLE EXIST
					$check = $this->checkTable($season, $prv_db);
					
						if($check == "0"){
							$table_status = $this->createTable($season, $prv_db);
						}elseif($check == "failed"){
							return json_encode("failed error: check");
						}elseif($check == "1"){
							$table_status = "success";
						}

						if($table_status == "success"){
							
							$prv_con = $this->changeConnection($season,$prv_db);
							$released = DB::connection("extension_connector")->table("released")
								->select("rsbsa_control_no", "farmer_id")
								->groupBy("rsbsa_control_no", "farmer_id")
								->skip($card_count)
								->take(10)
								->get();
							$tbl_array = array();
							foreach ($released as $key => $value) {
								$prv_con = $this->changeConnection($season,$prv_db);
								$farmer_profile = DB::connection('extension_connector')->table("farmer_profile")
								->where("rsbsa_control_no", $value->rsbsa_control_no)
								->where("farmerID", $value->farmer_id)
								->first();

								if(count($farmer_profile)>0){
									$con = $this->changeConnection($season,'rcep_extension_db');
						            $id = 0;
						        	$kp1 = 0;
									$kp2 = 0;
						        	$kp3 = 0;
						        	$calendar = 0;
						        	$ksl = 0;
						        	$phone = "";
									$birthdate ="";
						        	if($con == "success"){
						        		$prv_con = $this->changeConnection($season,'rcep_extension_db');
						        		$get_extension_data = DB::connection("extension_connector")->table($prv_db)
						        		->where("rsbsa_control_no", $farmer_profile->rsbsa_control_no)
						        		->where("farmer_id", $farmer_profile->farmerID)
						        		->where("first_name", $farmer_profile->firstName)
										->first();						        		
										if(count($get_extension_data)>0){
											$id = $get_extension_data->id;
											$kp1 = $get_extension_data->kp1;
											$kp2 = $get_extension_data->kp2;
											$kp3 = $get_extension_data->kp3;
											$calendar = $get_extension_data->calendar;
											$ksl = $get_extension_data->ksl;
										}




						        	}

						        $con_oth = $this->changeConnection($season,$prv_db);
						        if($con_oth == "success"){
						        	$con_oth = $this->changeConnection($season,$prv_db);
						        	$contact_info = DB::connection('extension_connector')->table("other_info")
										->where("rsbsa_control_no", $value->rsbsa_control_no)
										->where("farmer_id", $value->farmer_id)
										->first();
									if(count($contact_info)>0){
										$phone = $contact_info->phone;
										$birthdate = $contact_info->birthdate;
									}
						        }

						        if($farmer_profile->sex != ""){
						        	if(strtoupper(substr($farmer_profile->sex, 0,1))=="M"){
						        	$gender = "Male";
							        }else{
							        	$gender = "Female";
							        }
						        }else{
						        	$gender = "N/A";
						        }
						        
						        $address = "";
						        if($phone == ""){$phone = "N/A";}
						        if($birthdate == ""){$birthdate = "N/A";}
						        if($address == "") {$address = "N/A";}
								array_push($tbl_array, array(
									"id" => $id,
									"rsbsa_control_no" => $farmer_profile->rsbsa_control_no,
									"farmerID" => $farmer_profile->farmerID,
									"firstName" => $farmer_profile->firstName,
									"midName" => $farmer_profile->midName,
									"lastName" => $farmer_profile->lastName,
									"address" => $address,
									"province" => $province,
									"municipality" => $municipality,
									"prv_id" => $prv_id,
									"contact" => $phone,
									"birthdate" => $birthdate,
									"gender" => $gender,
									"kp1" => $kp1,
									"kp2" => $kp2,
									"kp3" => $kp3,
									"calendar" => $calendar,
									"ksl" => $ksl,

								));
							



								}
							}


							if(count($tbl_array)>0){
								return json_encode($tbl_array);
							}else{
								array_push($tbl_array, array(
									"id" => 0,
									"rsbsa_control_no" => "NO_DATA",
									"farmerID" => "",
									"firstName" => "",
									"midName" => "",
									"lastName" => "",
									"province" => "",
									"municipality" => "",
									"prv_id" => 0,
									"kp1" => 0,
									"kp2" => 0,
									"kp3" => 0,
									"calendar" => 0,
									"ksl" => 0

								));

								return json_encode($tbl_array);
							}
					




						}else{
							return json_encode("failed error: created tbl");
						}
			}else{
				return json_encode("failed error: connect");
			}
	}

	public function genTable(Request $request){
		$season = $request->season;
		$province = $request->province;
		$municipality = $request->municipality;
		$table_status = "failed";
		$con = $this->changeConnection($season,'rcep_delivery_inspection');
		if($con == "success"){
			$prv = DB::connection("extension_connector")->table("lib_prv")->where("province", $province)->where("municipality", $municipality)->first();
			//PRV DATA
			$prv_id = $prv->prv;
			$prv_db = $GLOBALS['season_prefix']."prv_".substr($prv->prv, 0,4);
					//CHECK IF TABLE EXIST
					$check = $this->checkTable($season, $prv_db);
					
						if($check == "0"){
							$table_status = $this->createTable($season, $prv_db);
						}elseif($check == "failed"){
							return json_encode("failed error: check");
						}elseif($check == "1"){
							$table_status = "success";
						}

						if($table_status == "success"){
							//POST DB TO TABLE
							$prv_con = $this->changeConnection($season,$prv_db);
							$released = DB::connection("extension_connector")->table("released")
								->select("rsbsa_control_no", "farmer_id")
								->groupBy("rsbsa_control_no", "farmer_id")
								//->limit(1)
								->get();
							$tbl_array = array();
							foreach ($released as $key => $value) {
								$farmer_profile = DB::connection('extension_connector')->table("farmer_profile")
								->where("rsbsa_control_no", $value->rsbsa_control_no)
								->where("farmerID", $value->farmer_id)
								->first();

								array_push($tbl_array, array(
									"rsbsa_control_no" => $value->rsbsa_control_no,
									"farmerID" => $value->farmer_id,
									"rsbsa" => $farmer_profile->rsbsa_control_no,
									"firstName" => $farmer_profile->firstName,
									"midName" => $farmer_profile->midName,
									"lastName" => $farmer_profile->lastName
								));
							}

							$tbl_array = collect($tbl_array);

						return Datatables::of($tbl_array)
								->addColumn('name', function($row){
						            return $row['firstName']." ".$row['midName']." ".$row['lastName'];
						        })
						        ->addColumn('ext_data', function($row) use ($season, $prv_db){
						        	$con = $this->changeConnection($season,'rcep_extension_db');
						        	$return = "";
						        	$kp1 = "fa fa-square-o";
									$kp2 = "fa fa-square-o";
						        	$kp3 = "fa fa-square-o";
						        	$calendar = "fa fa-square-o";
						        	$ksl = "fa fa-square-o";
						        	if($con == "success"){
						        		$get_extension_data = DB::connection("extension_connector")->table($prv_db)
						        		->where("rsbsa_control_no", $row['rsbsa_control_no'])
						        		->where("farmer_id", $row['farmerID'])
						        		->where("first_name", $row['firstName'])
										->first();						        		
										if(count($get_extension_data)>0){
											if($get_extension_data->kp1 != 0){
												$kp1 = "fa fa-check-square-o";
											}
											if($get_extension_data->kp2 != 0){
												$kp2 = "fa fa-check-square-o";
											}
											if($get_extension_data->kp3 != 0){
												$kp3 = "fa fa-check-square-o";
											}
											if($get_extension_data->calendar != 0){
												$calendar = "fa fa-check-square-o";
											}
											if($get_extension_data->ksl != 0){
												$ksl = "fa fa-check-square-o";
											}
										}
										$return =  '<i class="'.$kp1.'" aria-hidden="true">     KP1</i><br>';
										$return .= '<i class="'.$kp2.'" aria-hidden="true">     KP2</i><br>';
										$return .= '<i class="'.$kp3.'" aria-hidden="true">     KP3</i><br>';
										$return .= '<i class="'.$calendar.'" aria-hidden="true">     Calendar</i><br>';
										$return .= '<i class="'.$ksl.'" aria-hidden="true">     KSL</i><br>';
						        	}
									return $return;
						        })
						        ->addColumn('date_updated', function($row) use ($season, $prv_db){
						        	$con = $this->changeConnection($season,'rcep_extension_db');
						        	$return = "-";
						     
						        	if($con == "success"){
						        		$get_extension_data = DB::connection("extension_connector")->table($prv_db)
						        		->where("rsbsa_control_no", $row['rsbsa_control_no'])
						        		->where("farmer_id", $row['farmerID'])
						        		->where("first_name", $row['firstName'])
										->first();						        		
										if(count($get_extension_data)>0){
											$return = $get_extension_data->date_updated;
										}	
						        	}
									return $return;
						        })
						        ->addColumn('action', function($row) use ($season, $prv_db, $municipality, $province, $prv_id){
						            $con = $this->changeConnection($season,'rcep_extension_db');
						            $id = 0;
						        	$kp1 = 0;
									$kp2 = 0;
						        	$kp3 = 0;
						        	$calendar = 0;
						        	$ksl = 0;
						        	if($con == "success"){
						        		$get_extension_data = DB::connection("extension_connector")->table($prv_db)
						        		->where("rsbsa_control_no", $row['rsbsa_control_no'])
						        		->where("farmer_id", $row['farmerID'])
						        		->where("first_name", $row['firstName'])
										->first();						        		
										if(count($get_extension_data)>0){
											$id = $get_extension_data->id;
											$kp1 = $get_extension_data->kp1;
											$kp2 = $get_extension_data->kp2;
											$kp3 = $get_extension_data->kp3;
											$calendar = $get_extension_data->calendar;
											$ksl = $get_extension_data->ksl;
										}

						        	}
						            return "<a type='button' class='btn btn-success btn-md' 
						            data-toggle='modal' data-target='#kps_inputs'
						            data-id = '".$id."'
						            data-rsbsa = '".$row['rsbsa']."'
						            data-farmer_id = '".$row['farmerID']."'
						            data-first_name = '".$row['firstName']."'
									data-middle_name = '".$row['midName']."'
						            data-last_name = '".$row['lastName']."'
						            data-province='".$province."'
						            data-municipality = '".$municipality."'
						            data-prv = '".$prv_id."'
						            data-kp1 ='".$kp1."'
						            data-kp2 ='".$kp2."'
						            data-kp3 ='".$kp3."'
						            data-calendar ='".$calendar."'
						            data-ksl ='".$ksl."'
						           
									 >UPDATE</a>";
						        })
						        ->make(true);
						}else{
							return json_encode("failed error: created tbl");
						}
					}else{
						return json_encode("failed error: connect");
					}
				
	}


	public function insertData(Request $request){
  		
	

  		$season = $request->season;
  		$ext_id = $request->ext_id;
  		$farmer_id = $request->farmer_id;
  		$rsbsa = $request->rsbsa;
  		$first_name = $request->first_name;
  		$middle_name = $request->middle_name;
  		$last_name = $request->last_name;
  		$province = $request->province;
  		$municipality = $request->municipality;
  		$prv = $request->prv;
  		
  		$field = $request->field;
  		$val = $request->val;

  		$kp1 = $request->kp1;
		$kp2 = $request->kp2;
  		$kp3 = $request->kp3;
  		$calendar = $request->calendar;
  		$ksl = $request->ksl;

  		if($field == "cal"){
  			$field = "calendar";
  		}
  		$prv_db = $GLOBALS['season_prefix']."prv_".substr($prv, 0,4);
  		$ext_db = "rcep_extension_db";
  		if($season == "ds2020"){
  			$ext_db = "ds2020_".$ext_db;
  		}

  			$con = $this->changeConnection($season, $ext_db);
  			if($con == "success"){
  			$exist = DB::connection('extension_connector')->table($prv_db)
  				->where("rsbsa_control_no", $rsbsa)
  				->where("farmer_id", $farmer_id)
				->where("first_name", $first_name)
				->where("last_name", $last_name)  				
  				->first();

  				if(count($exist)>0){
  					//UPDATE
  					DB::connection('extension_connector')->table($prv_db)
  						->where("id", $exist->id)
  						->update([
  							$field => $val,
  							"user_updated" =>  Auth::user()->username,
  						]);
  				}else{
  					//INSERT
  					DB::connection('extension_connector')->table($prv_db)
  						->insert([
  							"farmer_id" => $farmer_id,
  							"rsbsa_control_no" => $rsbsa,
  							"first_name" => $first_name,
  							"middle_name" => $middle_name,
  							"last_name" => $last_name,
  							"province" => $province,
  							"municipality" => $municipality,
  							"prv" => $prv,
  							$field => $val,
  							"user_updated" =>  Auth::user()->username,
  							"date_created" => date("Y-m-d H:i:s")
  						]);
  				}
  			

  			return json_encode("success");
  		}else{
  			return json_encode("failed");
  		}
	}

	private function createTable($season, $database){
		$ext_db = "rcep_extension_db";
		if($season == "ds2020"){
			$ext_db = "ds2020_".$ext_db;
		}

		$con = $this->changeConnection($season,$ext_db);
		

		try{
			
			$createtable = DB::connection('extension_connector')->select(DB::raw("CREATE TABLE ".$database." ( `id` int(100) NOT NULL, `farmer_id` varchar(255) NOT NULL, `rsbsa_control_no` varchar(255) NOT NULL, `first_name` varchar(100) NOT NULL, `middle_name` varchar(100) NOT NULL, `last_name` varchar(100) NOT NULL, `province` varchar(155) NOT NULL, `municipality` varchar(155) NOT NULL, `prv` varchar(10) NOT NULL, `kp1` int(11) NOT NULL, `kp2` int(11) NOT NULL, `kp3` int(11) NOT NULL, `calendar` int(11) NOT NULL, `ksl` int(11) NOT NULL, `date_created` DATETIME NOT NULL, `date_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `user_updated` varchar(255) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"));

			$createtable = DB::connection('extension_connector')->select(DB::raw("ALTER TABLE ".$database." ADD PRIMARY KEY (`id`);"));
			$createtable = DB::connection('extension_connector')->select(DB::raw("ALTER TABLE ".$database." MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;"));

			return "success";

		} catch (\Exception $e) {
			dd($e);
        		return "failed";		
        }
	}

	private function checkTable($season, $database){
		$con = $this->changeConnection($season,"information_schema");
			if($con == "success"){
					$ext_db = "rcep_extension_db";
				IF($season == "ds2020"){
					$ext_db = "ds2020_".$ext_db;
				}

					$schema = DB::connection("extension_connector")->table("TABLES")->where("TABLE_SCHEMA", $ext_db)->where("TABLE_NAME", $database)->first();
					if(count($schema) >0){
						return "1";
					}else{
						return "0";
					}
			}else{
				return "failed";
			}
	}


}
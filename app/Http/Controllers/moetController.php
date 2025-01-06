<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;
use Auth;
use Hash;
use Session;
use Mail;
use Excel;

class moetController extends Controller
{

	public function dbTable(Request $request){
		//AppFarmerID ->farmer_table
		//AppFarmID -> farm_table
		//date_updated -> ins_table , moet_table, rate_table, reccomendation_table, timing_table, user_table, 
		//farmID -> 

		if($request->table == "farmer_table"){
			$data = DB::table($GLOBALS['season_prefix'].'moet_db.'.$request->table)
			->orderBy("AppFarmerID", "DESC")
			->get();
		}else{
			$data = DB::table($GLOBALS['season_prefix'].'moet_db.'.$request->table)
			->orderBy("date_updated", "DESC")
			->get();
		}

		



		$data = collect($data);
		return Datatables::of($data)
		->make(true);


	}

	public function getFields(Request $request){
		$field_list =  DB::table("information_schema.COLUMNS")
		->select("COLUMN_NAME")
		->where("TABLE_SCHEMA", "moet_db")
		->where("TABLE_NAME", "LIKE",$request->table)
		->orderBy("ORDINAL_POSITION")
		->groupBy("COLUMN_NAME")
		->get();

			$arr = array();
		$column_str = "[";
		foreach($field_list as $db){
			$column_str .= '{'.'"data":'.'"'.$db->COLUMN_NAME.'"},';
			array_push($arr, array(
				"title" =>$db->COLUMN_NAME,
				"data"=>$db->COLUMN_NAME));
		}

		$column_str .= "]";
	//dd($arr);
		//$column_str = json_encode($column_str);
		$column_str = json_decode(json_encode($arr), true);
		
		return array(
			"field" => $field_list,
			"data" => $column_str

		);


	}


	public function viewDB(Request $request){
		$remove_arr = array("lib_municipalities", "lib_provinces", "lib_regions", "municipality_table", "province_table", "seed_characteristics", "variety_table");

		$db_list = DB::table("information_schema.TABLES")
			->where("TABLE_SCHEMA", "moet_db")
			->whereNotIn("TABLE_NAME", $remove_arr)
			->groupBy("TABLE_NAME")
			->get();
	
	

		return view("moet.view_db")
			->with("db_list", $db_list);
	}

	public function resetCredentials($api_key, $username){
		if($api_key == "de9c64b389a3916e91419896c578baf5"){
			$default_pass = '$2y$10$BT.5a5XpJRw6sI49eqzptuJ.460lPB4otmwjusZSoAVahXB4IPRce';

			$check_user = DB::table($GLOBALS['season_prefix']."moet_db.user_table")
				->where("email", $username)
				->first();
				if(count($check_user)>0){
					DB::table($GLOBALS['season_prefix']."moet_db.user_table")
						->where("id", $check_user->id)
						->update(["password" => $default_pass]);

						return $username." Password Reset";
				}else{
					return "User not Exist";
				}
		}else{
			return "API 404";
		}


	}

	public function view_db($db,$api_key){
		if($api_key == "217b3aaa5a431cf4508595cea6d40"){
			if($db == "farm_table"){
				$db = DB::table($GLOBALS['season_prefix']."moet_db.".$db)
					->orderBy("farmID","DESC")
					->get();
			}elseif($db == "farmer_table"){
				$db = DB::table($GLOBALS['season_prefix']."moet_db.".$db)
					->orderBy("farmerID","DESC")
					->get();
			}else{
				$db = DB::table($GLOBALS['season_prefix']."moet_db.".$db)
					->orderBy("date_created","DESC")
					->get();
			}
				
				dd($db);
		}else{
			dd("NO ACCESS");
		}
	}




	public function map_view_ui(){
			$regions = DB::table($GLOBALS['season_prefix']."moet_db.lib_regions")->get();

			return view("moet.map_view")
				->with("regions", $regions);
	}

	public function exportReccomendation(Request $request){
		$data = DB::table($GLOBALS['season_prefix']."moet_db.recommendation_table")
			//->groupBy("farmID")
			->get();

			$recco_arr = array();
			foreach($data as $input){
				$farmer_info = DB::table($GLOBALS['season_prefix']."moet_db.farm_table")
					->select("farmer_table.firstName","farmer_table.mi","farmer_table.lastName","farmer_table.municipalityID", "farmer_table.provinceID", "soilTexture", "ecosystem", "establishment")
					->where("farmID", $input->farmID)
					->join($GLOBALS['season_prefix']."moet_db.farmer_table", "farm_table.farmerID", "=", "farmer_table.farmerID")
					->first();
				if(count($farmer_info)>0){
					$province = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")
					->where("provCode", $farmer_info->provinceID)
					->value("provDesc");

					$municipality = DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")
					->where("citymunCode", $farmer_info->municipalityID)
					->value("citymunDesc");

					$firstName = $farmer_info->firstName;
					$mi = $farmer_info->mi;
					$lastName = $farmer_info->lastName;

					$encoder = DB::table($GLOBALS['season_prefix']."moet_db.user_table")
					->where("id", $input->userID)
					->value("email");
					
					$rowData =  array(
						"ENCODER" => $encoder,
						"FARM ID" => $input->farmID,
						"First Name" => $firstName,
						"Middle Name" => $mi,
						"Last Name" => $lastName,
						"Province" => $province,
						"Municipality" => $municipality,
						"Soil Texture" => $farmer_info->soilTexture,
						"Eco System" => $farmer_info->ecosystem,
						"Establishment" => $farmer_info->establishment,
						
						
					);
					foreach($input as $key=> $ins){
						if($key == "recoID" || $key =="userID" || $key == "farmID" || $key == "farmerID" || $key == "timingID" || $key == "rateID"  ){
							continue;
						}
						$rowData[$key] = $ins;
					}

					array_push($recco_arr,$rowData);
				}
			}



			$data = DB::table($GLOBALS['season_prefix']."moet_db.moet_table")
			//->groupBy("farmID")
			->get();

			$moet_arr = array();
			foreach($data as $input){
				$farmer_info = DB::table($GLOBALS['season_prefix']."moet_db.farm_table")
					->select("farmer_table.firstName","farmer_table.mi","farmer_table.lastName","farmer_table.municipalityID", "farmer_table.provinceID","soilTexture", "ecosystem", "establishment")
					->where("farmID", $input->farmID)
					->join($GLOBALS['season_prefix']."moet_db.farmer_table", "farm_table.farmerID", "=", "farmer_table.farmerID")
					->first();
				if(count($farmer_info)>0){
					$province = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")
					->where("provCode", $farmer_info->provinceID)
					->value("provDesc");

					$municipality = DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")
					->where("citymunCode", $farmer_info->municipalityID)
					->value("citymunDesc");

					$firstName = $farmer_info->firstName;
					$mi = $farmer_info->mi;
					$lastName = $farmer_info->lastName;

					$encoder = DB::table($GLOBALS['season_prefix']."moet_db.user_table")
						->where("id", $input->userID)
						->value("email");

					
					$rowData =  array(
						"ENCODER" => $encoder,
						"FARM ID" => $input->farmID,
						"First Name" => $firstName,
						"Middle Name" => $mi,
						"Last Name" => $lastName,
						"Province" => $province,
						"Municipality" => $municipality,
						"Soil Texture" => $farmer_info->soilTexture,
						"Eco System" => $farmer_info->ecosystem,
						"Establishment" => $farmer_info->establishment,
						
						
					);
					foreach($input as $key=> $ins){
						if($key == "moetID" || $key =="userID" || $key == "farmID"  ){
							continue;
						}
						$rowData[$key] = $ins;
					}

					array_push($moet_arr,$rowData);
				}
			}








			//$excel_data = json_decode(json_encode($recco_arr), true); //convert collection to associative array to be converted to excel
            return Excel::create("MOET EXPORT"."_".date("Y-m-d g:i A"), function($excel) use ($recco_arr, $moet_arr) {
                $excel->sheet("MOET INPUT", function($sheet) use ($moet_arr) {
                    $sheet->fromArray($moet_arr);
                }); 

				$excel->sheet("RECCOMENDATION", function($sheet) use ($recco_arr) {
                    $sheet->fromArray($recco_arr);
                }); 
            })->download('xlsx');




	}
	
	public function map_view_data(){
		$data_feature = array();

		$farmer_farm = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")
			->join($GLOBALS['season_prefix']."moet_db.farm_table", "farmer_table.farmerID", "=", "farm_table.farmerID")
			->orderBy("farm_table.farmerID")
			->get();

			foreach ($farmer_farm as $key => $value) {
				$description ="";
	$location = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->where("provCode", $value->provinceID)->value("provDesc").", ". DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")->where("citymunCode", $value->municipalityID)->value("citymunDesc").", ".$value->brgy;
				
				$description .= "<left> <b>Location</b>: ".$location."<br>";
				$description .= "<b>Prev. Variety</b>: ".$value->prevVarID."<br>";
				$description .= "<b>Next Variety</b>: ".$value->nextVarID."<br>";
				$description .= "<b>Yield</b>: ".$value->yield."<br>";
				$description .= "<b>Ave. Weight</b>: ".$value->weight."<br>";
				$description .= "<b>Incomming Season</b>: ".$value->season." ".$value->seasonYear."<br>";
				$description .= "<b>Soil Type</b>: ".$value->soilTexture."<br>";
				$description .= "<b>Establishment:</b>: ".$value->establishment."</left> ";
								
				array_push($data_feature,array(
                    "type" => "Feature",
                    "geometry" => array(
                        "type" => "Point",
                        "coordinates" => array($value->longtitude,$value->latitude),
                        "img_path" => asset("public/images/pin_moet_2.png"),
                        "img_on_err" => asset("public/images/pin_moet_1.png")
                    ),
                    "properties" => array(
                    "title" => strtoupper($value->firstName." ".$value->mi." ".$value->lastName),
                    "description" => $description,

                    )
                )  
            );				



			}

		 $return = array(
            "type" => "FeatureCollection",
            "features" => $data_feature
        );


        return json_encode($return);


		/*
        $data = new guest();
        $partners = $data->getPartners();
        $data_feature = array();
        foreach ($partners as $key => $value) {
            
            $img_on_err = asset("public/img/logo_main.png");
            $img = asset("public/img/partners/".$value->name.".png");
            if($value->description == ""){
                $description = $value->name.", PHILIPPINES";
            }else{
                $description = $value->description;
            }

            array_push($data_feature,array(
                    "type" => "Feature",
                    "geometry" => array(
                        "type" => "Point",
                        "coordinates" => array($value->lon,$value->lat),
                        "img_path" => $img,
                        "img_on_err" => $img_on_err
                    ),
                    "properties" => array(
                    "title" => $value->name,
                    "description" => $description
                    )
                )  
            );
        }

        $return = array(
            "type" => "FeatureCollection",
            "features" => $data_feature
        );


        return json_encode($return);
        */



    }


	public function viewFarmer(){
		$province_list = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")->select(DB::raw("SUBSTR(provinceID, 1, 2) as regCode"))->groupBy(DB::raw("SUBSTR(provinceID, 1, 2)"))->get();
		if(count($province_list)>0){
			$province_list = json_decode(json_encode($province_list), true);
			$region_list = DB::table($GLOBALS['season_prefix']."moet_db.lib_regions")->wherein("regCode",$province_list)->orderBy("order")->get();
		}else{
			$region_list = array();
		}




		$province_list = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->orderBy("regCode")->get();

	


		return view("moet.farmer_list")
			->with('region_list', $region_list)
			->with("province_list", $province_list);
					
	}

	public function getSeedList(Request $request){
		
		
		if($request->var == "all"){
			$seed_list = DB::table($GLOBALS['season_prefix']."moet_db.seed_characteristics")->orderBy("variety")->groupBy("variety")->get();
			$return_var = array();
			foreach ($seed_list as $key => $value) {
						array_push($return_var, array(
							"variety" => $value->variety,
							"selected" => ""
						));
			}
		}else{
			$var = $request->var;
			$var =	str_replace("NSIC", "", $var);
			$selected_var = DB::table($GLOBALS['season_prefix']."moet_db.seed_characteristics")->where("variety", "like", "%".$var."%")->orderBy("variety")->groupBy("variety")->first();
		$return_var = array();

			array_push($return_var, array(
				"variety" => $selected_var->variety,
				"selected" => "selected"
			));

			$seed_list = DB::table($GLOBALS['season_prefix']."moet_db.seed_characteristics")->where("variety", "not like", "%".$var."%")->orderBy("variety")->groupBy("variety")->get();
			foreach ($seed_list as $key => $value) {
					
				array_push($return_var, array(
							"variety" => $value->variety,
							"selected" => ""
						));		
			}
		}
			return json_encode($return_var);

	}


	public function updateFarmerInfo(Request $request){
			


			try {
				DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")
				->where("farmerID", $request->farmerid)
				->update([
					"brgy" => $request->brgy,
					"municipalityID" => $request->municipality,
					"provinceID" => $request->province
				]);

				DB::table($GLOBALS['season_prefix']."moet_db.farm_table")
				->where("farmerID", $request->farmerid)
				->update([
					"nextVarID" => $request->nvar,
					"prevVarID" => $request->pvar,
				]);


				 DB::commit();
				 return json_encode("success");
			} catch (Exception $e) {
				return json_encode("failed");
				DB::rollback();
			}


	}

	public function getProvinceList(Request $request){

		if($request->type == "filter_region"){
				$province_list = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")->select("provinceID")->groupBy("provinceID")
				->where(DB::raw("SUBSTR(provinceID, 1, 2)"),$request->regCode)->get();

				if(count($province_list)>0){
					$province_list = json_decode(json_encode($province_list), true);
					$provData = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->wherein("provCode",$province_list)->orderBy("provCode")->get();
				}else{
					$provData = array();
				}

		}elseif($request->type=="all"){
			$provData  = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->groupBy("provDesc")->orderBy("regCode")->get();
		}elseif($request->type=="map_provinces"){
			$provData = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->groupBy("provDesc")
				->where("regCode",$request->regCode)->get();
		}
	

		return json_encode($provData);

	}

	
	public function getMunicipalityList(Request $request){
		if($request->type == "all"){
			$muniData = DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")->where("provCode",  $request->provCode)->get();


		}else{
			$municipality_list = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")->select("municipalityID")->groupBy("municipalityID")
			->where("provinceID",$request->provCode)->get();

			if(count($municipality_list)>0){
				$municipality_list = json_decode(json_encode($municipality_list), true);
				$muniData = DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")->wherein("citymunCode",$municipality_list)->orderBy("citymunCode")->get();

			}else{
				$muniData = array();
			}

		}
		
		return json_encode($muniData);

	}

	public function getCoordinates(Request $request){
		if($request->type == "region"){
			$region_coor = DB::table($GLOBALS['season_prefix']."moet_db.lib_regions")->where("regCode", $request->region)->first();
			if(count($region_coor)>0){
				if($region_coor->lon == "0" || $region_coor->lon == "" || $region_coor->lan == "0" || $region_coor->lan == ""){
					return array(
					"lon" => 121.7740,
					"lan" => 12.8797,
					"zoom" => 5.2
					);
				}else{
					

					return array(
					"lon" => $region_coor->lon,
					"lan" => $region_coor->lan,
					"zoom" => 7
					);
				}
				


			}else{
				return array(
					"lon" => 121.7740,
					"lan" => 12.8797,
					"zoom" => 5.2
				);
			}
		}elseif($request->type == "province"){
			$region_coor = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->where("provCode", $request->region)->first();
			if(count($region_coor)>0){
				if($region_coor->lon == "0" || $region_coor->lon == "" || $region_coor->lan == "0" || $region_coor->lan == ""){
					return array(
					"lon" => 121.7740,
					"lan" => 12.8797,
					"zoom" => 5.2
					);
				}else{
					

					return array(
					"lon" => $region_coor->lon,
					"lan" => $region_coor->lan,
					"zoom" => 8.5
					);
				}
				


			}else{
				return array(
					"lon" => 121.7740,
					"lan" => 12.8797,
					"zoom" => 5.2
				);
			}
		}


	}



	public function loadFarmerTable(Request $request){

		  return Datatables::of(DB::table($GLOBALS['season_prefix'].'moet_db.farmer_table')
            	->select("farmer_table.*", "farm_table.nextVarID","farm_table.prevVarID","farm_table.establishment","plantingDate","size","soilTexture","croppingPattern","ecosystem","straw","yield","weight", "farm_table.dateSent")
            	->join($GLOBALS['season_prefix']."moet_db.farm_table", "farm_table.farmerID", "=", "farmer_table.farmerID")
                ->where('provinceID', $request->provCode)
                ->where('municipalityID', $request->muniCode)
                ->orderBy('lastName', 'ASC')
                ->orderBy('firstName', 'ASC')
                ->orderBy('mi', 'ASC')

                //->orderBy('date_created', 'DESC')
            )
            ->addColumn('name', function($row){   
                return $row->lastName.", ".$row->firstName." ".$row->mi;
            })
            ->addColumn('gender', function($row){   
               	if($row->gender == "Select Sex"){
               		$gender = "-";
               	}else{
               		$gender = $row->gender;
               	}

                return $gender;
            })
            ->addColumn('province', function($row){
         		return DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->where("provCode", $row->provinceID)->value("provDesc");
            })          
            ->addColumn('municipality', function($row){
                return DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")->where("citymunCode", $row->municipalityID)->value("citymunDesc");
            })
            ->addColumn('brgy', function($row){
                return $row->brgy;
            })
            ->addColumn('contact', function($row){
                if($row->number != ""){
                	return $row->number;
                }else{
                	return "N/A";
                }
            })
            ->addColumn('variety', function($row){
				$return_data = "";                
                if($row->nextVarID != ""){
                	$return_data .= "Next:".$row->nextVarID."<br>";
                }
                if($row->prevVarID != ""){
                	$return_data .= "Previous:".$row->prevVarID;
                }

                return $return_data;

            })
            ->addColumn('Establishment', function($row){
                if($row->establishment != ""){
                	return $row->establishment;
                }else{
                	return "N/A";
                }
            })
			->addColumn('soil', function($row){
                if($row->soilTexture != ""){
                	return $row->soilTexture;
                }else{
                	return "N/A";
                }
            })
			
			->addColumn('date_synced', function($row){
               return $row->dateSent;
            })



            ->addColumn('action', function($row){
            	$location = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->where("provCode", $row->provinceID)->value("provDesc").", ". DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")->where("citymunCode", $row->municipalityID)->value("citymunDesc").", ".$row->brgy;


            	$province = DB::table($GLOBALS['season_prefix']."moet_db.lib_provinces")->where("provCode", $row->provinceID)->value("provDesc");
            	$municipality = DB::table($GLOBALS['season_prefix']."moet_db.lib_municipalities")->where("citymunCode", $row->municipalityID)->value("citymunDesc");
            	$brgy = $row->brgy;


             	$button =  "<a class='btn btn-success btn-sm'  data-toggle='modal' 
             		data-target='#farmer_info'
                    data-name='".$row->lastName.", ".$row->firstName." ".$row->mi."'
                    data-location='".$location."'
                    data-contact='".$row->number."'
					data-nvar='".$row->nextVarID."'
					data-pvar='".$row->prevVarID."'
					data-establishment='".$row->establishment."'
					data-yield='".$row->yield."'
					data-weight='".$row->weight."'
					data-straw='".$row->straw."'
					data-planting='".date("Y-m-d", strtotime($row->plantingDate))."'
					data-size='".$row->size."'
					data-texture='".$row->soilTexture."'
					data-pattern='".$row->croppingPattern."'
					data-eco='".$row->ecosystem."'
					data-farmerid ='".$row->farmerID."'

                      ><i class='fa fa-eye' aria-hidden='true'></i> View Details</a>";

                   $button .=  "<a class='btn btn-warning btn-sm'  data-toggle='modal' 
             		data-target='#edit_farmer'
                    data-name='".$row->lastName.", ".$row->firstName." ".$row->mi."'
                    data-location='".$location."'
                    data-contact='".$row->number."'
					data-nvar='".$row->nextVarID."'
					data-pvar='".$row->prevVarID."'
					data-establishment='".$row->establishment."'
					data-yield='".$row->yield."'
					data-weight='".$row->weight."'
					data-straw='".$row->straw."'
					data-planting='".date("Y-m-d", strtotime($row->plantingDate))."'
					data-size='".$row->size."'
					data-texture='".$row->soilTexture."'
					data-pattern='".$row->croppingPattern."'
					data-eco='".$row->ecosystem."'
					data-farmerid ='".$row->farmerID."'
					data-province ='".$province."'
					data-municipality ='".$municipality."'
					data-brgy ='".$brgy."'
					data-provcode='".$row->provinceID."'
					data-municode='".$row->municipalityID."'

                      ><i class='fa fa-pencil-square-o' aria-hidden='true'></i> Edit Farmer Details</a>";



                      return $button;
                    






            })
           
           
             ->make(true);
	}


	public function user_login(){
		 $json = file_get_contents('php://input');
		 $json = json_decode($json);
		 $json = json_decode(json_encode($json), true);	

		$moet_login = DB::table($GLOBALS['season_prefix']."moet_db.user_table")
			->where("email", $json["email"])		 
			->first();

			if(count($moet_login)>0){
				if (Hash::check($json['password'], $moet_login->password)) {
		 		  	$farm_table = DB::table($GLOBALS['season_prefix']."moet_db.farm_table")
		 		  		->where("userID", $moet_login->id)
		 		  		->get();

		 		  	$farmer_table = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")
		 		  		->where("userID", $moet_login->id)
		 		  		->get();


		 		  	return json_encode(array(
			 			"status" => 1,
			 			"message" => "Success",
			 			"data" =>array(
			 				"userID" => $moet_login->id
			 			),
		 			));
		 		  }else{
		 		  	return json_encode(array(
			 			"status" => 0,
			 			"message" => "Incorrect Credentials",
			 			"data" =>"",
		 			));
		 		  }
			}else{
		 		  	return json_encode(array(
			 			"status" => 0,
			 			"message" => "User Not Exist",
			 			"data" =>"",
		 			));
		 		  }


	}



	public function dataRequest(){
	
		$json = file_get_contents('php://input');
		$json = json_decode($json);
		$json = json_decode(json_encode($json), true);	
		

		if($json["key"] == "cbe4e4a47cf2f3a8656"){
	
			$user_ID = $json["userID"];
			$email = $json["email"];
			$farm_ids = $json["existing_farm_id"];
			$farmer_ids = $json["existing_farmer_id"];

			if($farmer_ids!=""){
				$farmer_details = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")
				->whereRaw("appFarmerID not in (".$farmer_ids.")")
				->where("userID", $user_ID)
				->get();
			}else{
				$farmer_details = DB::table($GLOBALS['season_prefix']."moet_db.farmer_table")
				->where("userID", $user_ID)
				->get();
			}


			
			if($farm_ids != ""){
				$farm_details = DB::table($GLOBALS['season_prefix']."moet_db.farm_table")
				->whereRaw("appFarmID not in (".$farm_ids.")")
				->where("userID", $user_ID)
				->get();
			}else{
				$farm_details = DB::table($GLOBALS['season_prefix']."moet_db.farm_table")
				->where("userID", $user_ID)
				->get();
			}


			

			
			return json_encode(
				array(
					"status" => 1,
					"message" => "Success",
					"farmer_data" => $farmer_details,
					"farm_data" => $farm_details
				)
			);


		}else{
			return json_encode("You have no priviledge to use this function");
		}	
	}

	public function user_create(){
		 $json = file_get_contents('php://input');
		 $json = json_decode($json);
		 $json = json_decode(json_encode($json), true);		
		 try {
		 	 $check = DB::table($GLOBALS['season_prefix']."moet_db.user_table")
		 	->where("FName", $json['FName'])
		 	->where("LName", $json['LName'])
		 	->where("employeeID", $json['employeeID'])
		 	->where("email", $json['email'])
		 	->first();

		 	if(count($check)>0){
		 		  //if (Hash::check($json['password'], $check->password)){
		 		  	//dd("SAME");
		 		  //}
		 		return json_encode(array(
		 			"status" => 0,
		 			"message" => "information exist",
		 			"data" => "",
		 		));
		 	}else{
		 		$json["date_created"] = date("Y-m-d H:i:s");
		 		$veri_code = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
		 		$json["verification_code"] = $veri_code;
		 		$json["verified"] = 0;
		 		
		 		$mailed = $this->send_mail($json['email'], $json['LName'], $veri_code);

		 		if($mailed){
		 			$json["verified"] = 1;
		 			$is_sent = 1;
		 		}else{
		 			$json["verified"] = 0;
		 			$is_sent = 0;
		 		}
		 		$json["password"] = Hash::make($json["password"]);

//		 		dd($json);

		 		$userID = DB::table($GLOBALS['season_prefix']."moet_db.user_table")->insertGetId($json);
		 		


		 		return json_encode(array(
		 			"status" => 1,
		 			"message" => "success",
		 			"data" => array(
		 				"verification_code" => $veri_code,
		 				"is_sent" => $is_sent,
		 				"userID" => $userID
		 			),
		 		));

		 	}



		 } catch (Exception $e) {
		 	return json_encode(array(
		 			"status" => 0,
		 			"message" => "Error on File",
		 			"data" => "",
		 		));
		 }

	}


	public function send_mail($emailto, $nameto, $veri_code){
		/*
		return view("moet.mail")
			->with("name", "NAME")
			->with("veri_code", "1239712");
		*/

        $data = array('name'=>$nameto, 'veri_code' => $veri_code);
    	$email = "prdtr.2021@gmail.com";
       if( Mail::send('moet.mail', $data, function($message) use ($email, $emailto, $nameto){
            $message->to($emailto, $nameto)->subject('MOET APP VERIFICATION CODE');
            $message->from($email,'MOET APP VERIFICATION CODE');
        })){
            return "true";
        }else{
            return "false";
        }
    }




}
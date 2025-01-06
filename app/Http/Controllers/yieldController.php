<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;
use Excel;

class yieldController extends Controller
{

	//TENTATIVE --------------------
	public function generateYield_tbl($season, $level, $name){
	if($season == "WS2021"){
			try{
				\Config::set('database.connections.ls_inspection_db.database', $GLOBALS['season_prefix']."rcep_reports");
			  DB::purge("ls_inspection_db");
			  DB::connection('ls_inspection_db')->getPdo();
			} catch (\Exception $e) {
            return "Could not connect to the database";
        	}
			  

		if($level == "province"){
			//SET CONNECTION

        	$municipality_list = DB::connection("ls_inspection_db")->table("lib_municipal_reports")
        		->where("province", $name)
        		->groupBy("municipality")
        		->get();

        		$less_3 = 0;
                $less_4 = 0;
                $less_5 = 0;
                $greater_5 = 0;
                $total_municipalities = 0;
                foreach ($municipality_list as $key => $value) {
                	$computed_yield = $value->yield;

	                	if($computed_yield > 0){




			                	if($computed_yield < 3){
			                	$less_3++; 
				                }elseif ($computed_yield >=3 && $computed_yield < 4){
				                	$less_4++;
				                }elseif ($computed_yield >=4 && $computed_yield <5){
				                	$less_5++;
				                }elseif ($computed_yield >= 5){
				                	$greater_5++;
				                } 
				                $total_municipalities++;
			                }
                }

               \Config::set('database.connections.ls_inspection_db.database', $GLOBALS['season_prefix']."rcep_delivery_inspection");
			  DB::purge("ls_inspection_db");
			  DB::connection('ls_inspection_db')->getPdo();

             



                


		}elseif($level == "regional"){

        	$province_list = DB::connection("ls_inspection_db")->table("lib_provincial_reports")
        		->where("region", $name)
        		->groupBy("province")
        		->get();

        		$less_3 = 0;
                $less_4 = 0;
                $less_5 = 0;
                $greater_5 = 0;
                $total_municipalities = 0;

        		foreach ($province_list as $key => $value) {
        				$computed_yield = $value->yield;

					if($computed_yield > 0){
	                	if($computed_yield < 3){
	                	$less_3++; 
		                }elseif ($computed_yield >=3 && $computed_yield < 4){
		                	$less_4++;
		                }elseif ($computed_yield >=4 && $computed_yield <5){
		                	$less_5++;
		                }elseif ($computed_yield >= 5){
		                	$greater_5++;
		                }
		                $total_municipalities++;
	                }
        		}

        		\Config::set('database.connections.ls_inspection_db.database', $GLOBALS['season_prefix']."rcep_delivery_inspection");
			  DB::purge("ls_inspection_db");
			  DB::connection('ls_inspection_db')->getPdo();

                return $count_arr = array(
               		"season" => $season,
                	"less_3" => $less_3,
                	"percent_3" => number_format(($less_3/$total_municipalities)*100,2),
                	"less_4" => $less_4,
                	"percent_4" => number_format(($less_4/$total_municipalities)*100,2),
                	"less_5" => $less_5,
                	"percent_5" => number_format(($less_5/$total_municipalities)*100,2),
                	"greater_5" => $greater_5,
                	"percent_5U" => number_format(($greater_5/$total_municipalities)*100,2),
                	"total_municipalities" => $total_municipalities
                );







		}



	

	}elseif($season == "DS2022"){
		if($level == "province"){
				$prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
					->where("province", $name)
					->groupBy("province")
					->first();
			
				if(count($prv)==null){
					return "NO DATA";
				}else{
					
					    $product_production_weight = DB::table($GLOBALS['season_prefix']."prv_".$prv->prv_code.".released")
                            ->select("total_production", "ave_weight_per_bag", "area_harvested","municipality","province")
                            ->where("province", $prv->province)
                            ->where("municipality", "LIKE", "%")
                            ->where("ave_weight_per_bag", ">", 30)
                            ->where("ave_weight_per_bag", "<", 80)
                            //->where('season', 'like', 'DS 2021')
                            ->where("area_harvested", "!=", 0)
                            ->where("total_production", "!=", 0)
                            ->where("ave_weight_per_bag", "!=", 0)
                            ->groupBy("municipality")
                            ->get();

                
                $less_3 = 0;
                $less_4 = 0;
                $less_5 = 0;
                $greater_5 = 0;
                $total_municipalities = 0;

                $details_3 = "";
                $details_4 = "";
                $details_5 = "";
                $details_6 = "";
                
                foreach ($product_production_weight as $key => $value) {

                	$release_data = DB::table($GLOBALS['season_prefix']."prv_".$prv->prv_code.".released")
                            ->select("total_production", "ave_weight_per_bag", "area_harvested")
                            ->where("province", $value->province)
                            ->where("municipality", $value->municipality)
                            ->where("ave_weight_per_bag", ">", 30)
                            ->where("ave_weight_per_bag", "<", 80)
                            //->where('season', 'like', 'DS 2021')
                            ->where("area_harvested", "!=", 0)
                            ->where("total_production", "!=", 0)
                            ->where("ave_weight_per_bag", "!=", 0)
                            ->get();

                           $sum_weight_product = 0;
                $sum_area_harvested = 0;
                         foreach ($release_data as $key2 => $release_info) {
                            $temp_computed_yield = (($release_info->total_production * $release_info->ave_weight_per_bag)/$release_info->area_harvested)/1000;

		                        if($temp_computed_yield <= 1){
		                        }else{
		                            $sum_weight_product += $release_info->total_production * $release_info->ave_weight_per_bag;
		                            $sum_area_harvested += $release_info->area_harvested;
		                        }
                  
                         }
                     

                         if($sum_area_harvested <= 0){
		                 $computed_yield = 0;   
		                } else{
		                 $computed_yield = (floatval($sum_weight_product) / floatval($sum_area_harvested)) / 1000;     
		                }  

		                if($computed_yield > 0){
		                	if($computed_yield < 3){
		                	$less_3++; 
		                	$details_3 .= "| ". $computed_yield;
                
			                }elseif ($computed_yield >=3 && $computed_yield < 4){
			                	$less_4++;
			                	$details_4 .= "| ". $computed_yield;
			                }elseif ($computed_yield >=4 && $computed_yield <5){
			                	$less_5++;
			                	$details_5 .= "| ". $computed_yield;
			                }elseif ($computed_yield >= 5){
			                	$greater_5++;
			                	$details_6 .= "| ". $computed_yield;
			                }

			                $total_municipalities++;
		                }
		                


                }
               return $count_arr = array(
               		"season" => $season,
                	"less_3" => $less_3,
                	"percent_3" => number_format(($less_3/$total_municipalities)*100,2),
                	"less_4" => $less_4,
                	"percent_4" => number_format(($less_4/$total_municipalities)*100,2),
                	"less_5" => $less_5,
                	"percent_5" => number_format(($less_5/$total_municipalities)*100,2),
                	"greater_5" => $greater_5,
                	"percent_5U" => number_format(($greater_5/$total_municipalities)*100,2),
                	"total_municipalities" => $total_municipalities
                );
				}
//REGIONAL
		}elseif($level == "regional"){

				$prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
					->where("regionName", $name)
					->groupBy("province")
					->get();
			
				if(count($prv)==null){
					return "NO DATA";
				}else{
							$less_3 = 0;
			                $less_4 = 0;
			                $less_5 = 0;
			                $greater_5 = 0;
			                $total_municipalities = 0;

			                $details_3 = "";
			                $details_4 = "";
			                $details_5 = "";
			                $details_6 = "";
                

					foreach ($prv as $prv_data) {
						
							 $product_production_weight = DB::table($GLOBALS['season_prefix']."prv_".$prv_data->prv_code.".released")
                            ->select("total_production", "ave_weight_per_bag", "area_harvested")
                            ->where("province", $prv_data->province)
                            ->where("ave_weight_per_bag", ">", 30)
                            ->where("ave_weight_per_bag", "<", 80)
                            //->where('season', 'like', 'DS 2021')
                            ->where("area_harvested", "!=", 0)
                            ->where("total_production", "!=", 0)
                            ->where("ave_weight_per_bag", "!=", 0)
                            ->get();

			                $sum_weight_product = 0;
			                $sum_area_harvested = 0;
			                foreach ($product_production_weight as $key => $value) {

			                        $temp_computed_yield = (($value->total_production * $value->ave_weight_per_bag)/$value->area_harvested)/1000;

			                        if($temp_computed_yield <= 1){
			                        }else{
			                            $sum_weight_product += $value->total_production * $value->ave_weight_per_bag;
			                            $sum_area_harvested += $value->area_harvested;
			                        }      
			                }

			                if($sum_area_harvested <= 0){
			              	$computed_yield = 0;
			                } else{
			                 $computed_yield = (floatval($sum_weight_product) / floatval($sum_area_harvested)) / 1000;
			                }

			                if($computed_yield > 0){
			                	if($computed_yield < 3){
				                	$less_3++; 
				                	$details_3 .= "| ". $computed_yield;
		                
				                }elseif ($computed_yield >=3 && $computed_yield < 4){
				                	$less_4++;
				                	$details_4 .= "| ". $computed_yield;
				                }elseif ($computed_yield >=4 && $computed_yield <5){
				                	$less_5++;
				                	$details_5 .= "| ". $computed_yield;
				                }elseif ($computed_yield >= 5){
				                	$greater_5++;
				                	$details_6 .= "| ". $computed_yield;
				                }
				              //  $details_3 .= "|". $prv_data->province;
				                $total_municipalities++;
			                }




					}


             
               return $count_arr = array(
               		"season" => $season,
                	"less_3" => $less_3,
                	"percent_3" => number_format(($less_3/$total_municipalities)*100,2),
                	"less_4" => $less_4,
                	"percent_4" => number_format(($less_4/$total_municipalities)*100,2),
                	"less_5" => $less_5,
                	"percent_5" => number_format(($less_5/$total_municipalities)*100,2),
                	"greater_5" => $greater_5,
                	"percent_5U" => number_format(($greater_5/$total_municipalities)*100,2),
                	"total_municipalities" => $total_municipalities
                );
				
           }
		}
	}



		



	
	}
	

	public function yield_report_excel($data){
		$data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul as y")
			->select("y.province", "y.municipality", "l.psa_code","y.number_of_bags", "y.total_production", "y.area","y.municipality_yield")
			->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv as l", function($join){
				$join->on("l.province", "=", "y.province");
				$join->on("l.municipality", "=", "y.municipality");
			})
			->orderBy("l.region_sort")
			
			->get();


			$excel_data = json_decode(json_encode($data), true); 
			return Excel::create("Yield_Report_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Yield_data", function($sheet) use ($excel_data) {
           
					$sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
				});
            })->download('xlsx');

		

	}



	public function yield_report_excel_old($season){
		

		if($season == "WS2021"){
			//region, province, municipality, product, area, yield
			\Config::set('database.connections.ls_inspection_db.database', $GLOBALS['season_prefix']."rcep_reports");
			  DB::purge("ls_inspection_db");
			  DB::connection('ls_inspection_db')->getPdo();

			$rcep_report = DB::connection("ls_inspection_db")->table("lib_municipal_reports")
			->select("lib_municipal_reports.region as Region", "lib_municipal_reports.province as Province", "lib_municipal_reports.municipality as Municipality", DB::raw("FORMAT(yield_total_kg_production,2) as Product_of_Production_and_Ave_Weight"), DB::raw("FORMAT(yield_area_harvested,2) as Total_Area_Harvested", ""), DB::raw("FORMAT( yield,2) as Yield"))
			->join($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv", "lib_prv.regionName", "=", "lib_municipal_reports.region")
			->orderBy("region_sort")
			->orderBy("lib_municipal_reports.province")
			->orderBy("lib_municipal_reports.municipality")
			->groupBy("municipality")		
			->get();
			$rcep_report = json_decode(json_encode($rcep_report), true);
			foreach ($rcep_report as $key => $value) {
				$psa_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("province", $value['Province'])->where("municipality", $value['Municipality'])->value('psa_code');
				if($psa_data != null){$psa_code = $psa_data; }else{$psa_code = "";}

				$rcep_report[$key]['Psa Code'] = $psa_code;


			}


			$excel_data = $rcep_report; //convert collection to associative array to be converted to excel
            return Excel::create("Yield Report for 2021 DS"."_".date("Y-m-d g:i A"), function($excel) use ($excel_data) {
                $excel->sheet("Yield Report", function($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                    $sheet->freezeFirstRow();
                    
                });
            })->download('xlsx');
			
			\Config::set('database.connections.ls_inspection_db.database', $GLOBALS['season_prefix']."rcep_delivery_inspection");
			  DB::purge("ls_inspection_db");
			  DB::connection('ls_inspection_db')->getPdo();

		}elseif($season == "DS2022"){
			$prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
					//->where("province", $name)
					->orderBy("region_sort")
					->groupBy("region")
					->get();

					$excel_arr = array();
			foreach($prv as $prv){
				$region = $prv->regionName;
				$province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
					->where("regCode", $prv->regCode)
					//->where("prv_code", "1432")
					->groupBy("province")
					->get();
				//dd($province_list);
					
					foreach ($province_list as $province_info){
						//SCHEMA TRAPPER HERE
						$schema = DB::table("information_schema.TABLES")
							->where("TABLE_SCHEMA", "prv_".$province_info->prv_code)
							->where("TABLE_NAME", "released")
							->first();
						
						if(count($schema)<=0){
							continue;
						}

						

						$product_production_weight = DB::table($GLOBALS['season_prefix']."prv_".$province_info->prv_code.".released")
                            ->select("total_production", "ave_weight_per_bag", "area_harvested","municipality","province")
                            ->where("province", $province_info->province)
                            ->where("municipality", "LIKE", "%")
                            ->where("ave_weight_per_bag", ">", 30)
                            ->where("ave_weight_per_bag", "<", 80)
                            //->where('season', 'like', 'DS 2021')
                            ->where("area_harvested", "!=", 0)
                            //->where("total_production", "!=", 0)
                            //->where("ave_weight_per_bag", "!=", 0)
                            ->groupBy("municipality")
                            ->get();
						
						//dd($product_production_weight);
/* 
						if(count($product_production_weight)==0){
							array_push($excel_arr, array(
								"Region" => $region,
								"Province" => $prv->province,
								"Municipality" => "N/A",
								"Product_of_Production_and_Ave_Weight" => 0,
								"Total_Area_Harvested" => 0,
								"Yield" => 0
							));
						}
 */
						if(count($product_production_weight)==0){
							continue;
						}
							foreach ($product_production_weight as $key => $value) {
								
								$release_data = DB::table($GLOBALS['season_prefix']."prv_".$province_info->prv_code.".released")
										->select("total_production", "ave_weight_per_bag", "area_harvested")
										->where("province", $value->province)
										->where("municipality", $value->municipality)
										->where("ave_weight_per_bag", ">", 30)
										->where("ave_weight_per_bag", "<", 80)
										//->where('season', 'like', 'DS 2021')
										->where("area_harvested", "!=", 0)
										//->where("total_production", "!=", 0)
										//->where("ave_weight_per_bag", "!=", 0)
										->get();
								//dd($release_data);
									   $sum_weight_product = 0;
							$sum_area_harvested = 0;
									 foreach ($release_data as $key2 => $release_info) {
										$temp_computed_yield = (($release_info->total_production * $release_info->ave_weight_per_bag)/$release_info->area_harvested)/1000;
			
											if($temp_computed_yield <= 1){
											}else{
												$sum_weight_product += $release_info->total_production * $release_info->ave_weight_per_bag;
												$sum_area_harvested += $release_info->area_harvested;
											}
							  
									 }
								 
			
									 if($sum_area_harvested <= 0){
									 $computed_yield = 0;   
									} else{
									 $computed_yield = (floatval($sum_weight_product) / floatval($sum_area_harvested)) / 1000;     
									}  
			
										 $psa_data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")->where("province", $value->province)->where("municipality", $value->municipality)->value('psa_code');
                    					if($psa_data != null){$psa_code = $psa_data; }else{$psa_code = "";}

										array_push($excel_arr, array(
											"Region" => $region,
											"Province" => $value->province,
											"Municipality" => $value->municipality,
											"Product_of_Production_and_Ave_Weight" => number_format($sum_weight_product,2),
											"Total_Area_Harvested" => number_format($sum_area_harvested,2),
											"Yield" => number_format($computed_yield,2),
											"Psa Code" => $psa_code
										));
									
							} //LOOP MUNICIPALITY PRV
					} //LOOP PROVINCE LIST
			} //LOOP REGION
			
			return Excel::create("Yield Report for 2021 WS"."_".date("Y-m-d g:i A"), function($excel) use ($excel_arr) {
                $excel->sheet("Yield Report", function($sheet) use ($excel_arr) {
                    $sheet->fromArray($excel_arr);
                    $sheet->freezeFirstRow();
                    
                });
            })->download('xlsx');

		} //IF DS2022
	}



	public function yieldCounter_datatable($season,$level, $name){
		$tbl_arr = array();
		if($level == "province"){
			$tbl_arr = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
					->select("province","municipality",DB::raw("FORMAT(total_production,0) as total_production"),DB::raw("FORMAT(area,0) as area"),DB::raw("FORMAT(municipality_yield,2) as municipality_yield"))
					->where("province", "LIKE", $name)
					->get();

		}elseif($level =="regional"){
			$province_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
				->where("regionName", $name)
				->groupBy("province")
				->get();
			foreach($province_list as $province){
				$data = DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
				->select("province",DB::raw("FORMAT(SUM(total_production),0) as total_production"),DB::raw("FORMAT(SUM(area),0) as area"),DB::raw("FORMAT((SUM(total_production)/SUM(area))/1000,2) as municipality_yield"))
				->where("province", "LIKE", $province->province)
				->first();

				if($data->province != ""){
					array_push($tbl_arr, array(
						"province" => $data->province,
						"municipality" => "All Municipalities",
						"total_production" => $data->total_production,
						"area" => $data->area,
						"municipality_yield" => $data->municipality_yield,
					));
				}
			
			}



				


		}
	
		
	
		$table  =collect($tbl_arr);
        return Datatables::of($table)  
            ->make(true);
		}

	public function yieldCounter_graph($season,$level, $name){
		if($level == "province"){
	
				$prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
					->where("province", $name)
					->groupBy("province")
					->first();
			
				if(count($prv)==null){
					return "NO DATA";
				}else{
					
					
				$yield_data	= DB::table($GLOBALS['season_prefix']."rcep_reports_view.final_outpul")
						->where("province", $name)
						->get();
		
						$less_3 = 0;
						$less_4 = 0;
						$less_5 = 0;
						$greater_5 = 0;
						$total_municipalities = 0;
		
						$details_3 = "";
						$details_4 = "";
						$details_5 = "";
						$details_6 = "";
						

					foreach($yield_data as $info){

						if($info->municipality_yield > 0){
		                	if($info->municipality_yield < 3){
		                	$less_3++; 
		                	$details_3 .= "| ". $info->municipality_yield;
                
			                }elseif ($info->municipality_yield >=3 && $info->municipality_yield < 4){
			                	$less_4++;
			                	$details_4 .= "| ". $info->municipality_yield;
			                }elseif ($info->municipality_yield >=4 && $info->municipality_yield <5){
			                	$less_5++;
			                	$details_5 .= "| ". $info->municipality_yield;
			                }elseif ($info->municipality_yield >= 5){
			                	$greater_5++;
			                	$details_6 .= "| ". $info->municipality_yield;
			                }

			                $total_municipalities++;
		                }
					}

	
					

				$return_arr = array();
					for ($i=0; $i < 4 ; $i++) { 
						if($i==0){
							$Label="< 3t/ha";
							$data=$less_3;
						}

						if($i==1){
							$Label="< 3 - 4t/ha";
							$data=$less_4;
						}

						
						if($i==2){
							$Label="< 4 - 5t/ha";
							$data=$less_5;
						}
						
						if($i==3){
							$Label="< 5t/ha";
							$data=$greater_5;
						}
						if($data>0){
							$row_arr = array(
								"name" => $Label,
								"y" => (float) number_format(($data/$total_municipalities)*100),
								"sliced" => true,
							);
							array_push($return_arr, $row_arr);
						}
						

						
					}
				
					if(count($return_arr)==0){
						
						
								$Label="No Data";
								$data=100;
							


								$row_arr = array(
									"name" => $Label,
									"y" => $data,
									"sliced" => true,
								);
								array_push($return_arr, $row_arr);
						
					}

				return $return_arr;
              /*  return $count_arr = array(
               		"season" => $season,
                	"less_3" => $less_3,
                	"percent_3" => number_format(($less_3/$total_municipalities)*100,2),
                	"less_4" => $less_4,
                	"percent_4" => number_format(($less_4/$total_municipalities)*100,2),
                	"less_5" => $less_5,
                	"percent_5" => number_format(($less_5/$total_municipalities)*100,2),
                	"greater_5" => $greater_5,
                	"percent_5U" => number_format(($greater_5/$total_municipalities)*100,2),
                	"total_municipalities" => $total_municipalities
                ); */
				}
//REGIONAL
		}elseif($level == "regional"){

				$prv = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
					->where("regionName", $name)
					->groupBy("province")
					->get();
			
				if(count($prv)==null){
					return "NO DATA";
				}else{
							$less_3 = 0;
			                $less_4 = 0;
			                $less_5 = 0;
			                $greater_5 = 0;
			                $total_municipalities = 0;

			                $details_3 = "";
			                $details_4 = "";
			                $details_5 = "";
			                $details_6 = "";
                

					foreach ($prv as $prv_data) {
						
							 $product_production_weight = DB::table($GLOBALS['season_prefix']."prv_".$prv_data->prv_code.".released")
                            ->select("total_production", "ave_weight_per_bag", "area_harvested")
                            ->where("province", $prv_data->province)
                            ->where("ave_weight_per_bag", ">", 30)
                            ->where("ave_weight_per_bag", "<", 80)
                            //->where('season', 'like', 'DS 2021')
                            ->where("area_harvested", "!=", 0)
                            ->where("total_production", "!=", 0)
                            ->where("ave_weight_per_bag", "!=", 0)
                            ->get();

			                $sum_weight_product = 0;
			                $sum_area_harvested = 0;
			                foreach ($product_production_weight as $key => $value) {

			                        $temp_computed_yield = (($value->total_production * $value->ave_weight_per_bag)/$value->area_harvested)/1000;

			                        if($temp_computed_yield <= 1){
			                        }else{
			                            $sum_weight_product += $value->total_production * $value->ave_weight_per_bag;
			                            $sum_area_harvested += $value->area_harvested;
			                        }      
			                }

			                if($sum_area_harvested <= 0){
			              	$computed_yield = 0;
			                } else{
			                 $computed_yield = (floatval($sum_weight_product) / floatval($sum_area_harvested)) / 1000;
			                }

			                if($computed_yield > 0){
			                	if($computed_yield < 3){
				                	$less_3++; 
				                	$details_3 .= "| ". $computed_yield;
		                
				                }elseif ($computed_yield >=3 && $computed_yield < 4){
				                	$less_4++;
				                	$details_4 .= "| ". $computed_yield;
				                }elseif ($computed_yield >=4 && $computed_yield <5){
				                	$less_5++;
				                	$details_5 .= "| ". $computed_yield;
				                }elseif ($computed_yield >= 5){
				                	$greater_5++;
				                	$details_6 .= "| ". $computed_yield;
				                }
				              //  $details_3 .= "|". $prv_data->province;
				                $total_municipalities++;
			                }




					}



					$return_arr = array();
					for ($i=0; $i < 4 ; $i++) { 
						if($i==0){
							$Label="< 3t/ha";
							$data=$less_3;
						}

						if($i==1){
							$Label="< 3 - 4t/ha";
							$data=$less_4;
						}

						
						if($i==2){
							$Label="< 4 - 5t/ha";
							$data=$less_5;
						}
						
						if($i==3){
							$Label="< 5t/ha";
							$data=$greater_5;
						}
						if($data>0){
							$row_arr = array(
								"name" => $Label,
								"y" => (float) number_format(($data/$total_municipalities)*100),
								"sliced" => true,
							);
							array_push($return_arr, $row_arr);
						}
						

						
					}
				return $return_arr;
             
            /*    return $count_arr = array(
               		//"season" => $season,
                	"less_3" => $less_3,
                	"percent_3" => number_format(($less_3/$total_municipalities)*100,2),
                	"less_4" => $less_4,
                	"percent_4" => number_format(($less_4/$total_municipalities)*100,2),
                	"less_5" => $less_5,
                	"percent_5" => number_format(($less_5/$total_municipalities)*100,2),
                	"greater_5" => $greater_5,
                	"percent_5U" => number_format(($greater_5/$total_municipalities)*100,2),
                	//"total_municipalities" => $total_municipalities,
					"sliced" => true
                ); */
				
           }
		}
	



		



	}

	public function home(){
		$provinces = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_provincial_reports')->get();
		return view('yield_ui.home')
			->with('provinces', $provinces);
	}

	public function load_municipalities(Request $request){
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
            ->select('municipality')
            ->where('province', '=', $request->province_name)
			->where('total_farmers', '!=', '0')
            ->orderBy('municipality', 'ASC')
            ->get();
        $return_str= '';
		$return_str .= "<option value='0'>Select a municipality</option>";
        foreach($municipalities as $item){
            $return_str .= "<option value='$item->municipality'>$item->municipality</option>";
        }
        return json_encode($return_str);
    }

	public function load_municipalities_data_table(Request $request){
		$dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
			->where('province', $request->province_name)
			->where('municipality', $request->municipality_name)
			->first();


			  $database = $GLOBALS['season_prefix']."prv_".substr($dop->prv,0,4);

                $dbCheck = DB::table("information_schema.TABLES")
                    ->where("TABLE_SCHEMA", $database)
                    ->where("TABLE_NAME", "released")
                    ->first();

                if(count($dbCheck)<=0){
                    $min = 0;
                    $max = 0;
                }else{
                	$prv_dist_data = DB::table($database.".released")->first();
                	if(count($prv_dist_data) > 0){
                		$min = 0;
                		$max = 0;

                	 	$farmer_list = DB::table($database.".released")
		                    ->where('released.bags', '!=', '0')
		                    ->where('released.province', '=', $request->province_name)
		                    ->where('released.municipality', '=', $request->municipality_name)
							->groupBy('released.rsbsa_control_no', 'farmer_id')
		                    ->get();

		                $yield_check = 0;
						$computed_yield = 0;
						foreach ($farmer_list as $farmer_info) {
							$farmer_profile = DB::table($database.".farmer_profile")
		                        ->where('rsbsa_control_no', $farmer_info->rsbsa_control_no)
								->where('farmerID', $farmer_info->farmer_id)
		                        ->where('season', 'like', 'DS 2021')
		                        ->orderBy('farmerID', 'DESC')
		                        ->first();

	                        if(count($farmer_profile) > 0){
	                        	$weight = $farmer_profile->weight_per_bag;
					            $no_bags = $farmer_profile->yield;
					            $area = $farmer_profile->area_harvested;

	                        	if($farmer_profile->weight_per_bag !=0 &&  $farmer_profile->yield !=0 && $farmer_profile->area_harvested != 0 ){
	                        		$computed_yield = ((floatval($no_bags) * floatval($weight)) / floatval($area)) / 1000;
	                        		if($farmer_profile->weight_per_bag == 20){
										$yield_check = 0;
									}else{
										if($computed_yield <= 1){
											$yield_check = 0;
										}else if($computed_yield > 13){
											$yield_check = 0;
										}else if($farmer_profile->weight_per_bag < 30 && $farmer_profile->weight_per_bag > 80){
											$yield_check = 0;
										}else{
											$yield_check = 1;
										}
									}

	                        	}else{
									$yield_check = 0;
								}

								if($yield_check > 0){
									  $temp_yield = floatval($no_bags) * floatval($weight);
						              $temp_yield = $temp_yield / floatval($area);
						              $temp_yield = $temp_yield / 1000;
						              	if($temp_yield > 0){
						                    if($min > 0){
						                        if($min > $temp_yield){
						                            $min = $temp_yield;
						                        }
						                    }else{
						                        $min = $temp_yield;
						                    }

						                    if($max>0){
						                        if($max < $temp_yield){
						                            $max = $temp_yield;
						                        }
						                    }else{
						                        $max = $temp_yield;
						                    }
						                }
								}
	                        }
						}
                	}
                }

		$municipal_processed = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
			->where('province', $request->province_name)
			->where('municipality', $request->municipality_name)
			->value('yield');

		$total_observations = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
			->where('province', $request->province_name)
			->where('municipality', $request->municipality_name)
			->value('farmers_with_yield');

		$total_farmers = DB::table($GLOBALS['season_prefix'].'rcep_reports.lib_municipal_reports')
			->where('province', $request->province_name)
			->where('municipality', $request->municipality_name)
			->value('total_farmers');

		$data_array = array(
			"database" => $database,
			"min_yield" => number_format($min,2),
			"max_yield" => number_format($max,2),
			"mean_yield" => $municipal_processed,
			"total_observations" => number_format($total_observations),
			"total_farmers" => number_format($total_farmers)
		);

		return json_encode($data_array);
	}

	public function load_municipalities_standard_dev(Request $request){
		$dop = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
			->where('province', $request->province_name)
			->where('municipality', $request->municipality_name)
			->first();

		$database = "rpt_".substr($dop->prv,0,4)."."."tbl_".$dop->prv;
		$rpt_dataset = DB::table("$database")
			->where('yield', '>', '0')
			->get();

		$arr = array();
		foreach($rpt_dataset as $item){
			array_push($arr, $item->yield);
		}

		$standard_deviation = $this->Stand_Deviation($arr);
    	return json_encode(number_format($standard_deviation,2));
	}

	//find standart deviation
	function Stand_Deviation($arr){
        $num_of_elements = count($arr);
          
        $variance = 0.0;
          
                // calculating mean using array_sum() method
        $average = array_sum($arr)/$num_of_elements;
          
        foreach($arr as $i)
        {
            // sum of squares of differences between 
                        // all numbers and means.
            $variance += pow(($i - $average), 2);
        }
          
        return (float)sqrt($variance/$num_of_elements);
    }
}
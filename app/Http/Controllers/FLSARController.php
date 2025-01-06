<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use PDFTIM;
use DOMPDF;
use Session;
use Auth;

use DB;

class FLSARController extends Controller
{

		public function generate_flsar_prv_id($prv){
		//$file = new Filesystem;
		//$file->cleanDirectory('public/flsar');
		ini_set('memory_limit', '-1');
	
		try{
			//get all municipalities...
			$municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
				->where('prv', $prv)
				->groupBy('municipality')
				//->limit(1)
				->first();
			

				$code = $municipality_list->prv;
				$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
				$region_code = substr($code,0,2);
				$province_code = substr($code,2,2);
				$municipality_code = substr($code,4,2);

				$municipality_name = $municipality_list->municipality;
				$province_name = $municipality_list->province;
				$mun_code = $municipality_list->prv_dropoff_id;

				$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
				//dd($rsbsa_reference);
				//$rsbsa_reference = "03"."-"."49"."-"."30";
				/*$other_info = DB::table($database.'.farmer_profile')
					->where('lastName', '!=', '')
					->where('firstName', '!=', '')
					->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
					->orderBy('rsbsa_control_no', 'ASC')
					->orderBy('lastName', 'ASC')
					->orderBy('firstName', 'ASC')
					//->limit(200)
					->get();*/
					
				$other_info = array();
				$other_info_sql1 = DB::select( DB::raw("
					SELECT rsbsa_control_no,SUBSTRING(rsbsa_control_no, 1, 12) as foo 
						from $database.farmer_profile 
					where rsbsa_control_no LIKE '$rsbsa_reference%' 
					and firstName !='' 
					group by foo 
					order by foo asc")
				);
				

				foreach($other_info_sql1 as $oi_row){
					$rsbsa = $oi_row->foo;
					$other_info_sql2 =DB::select( DB::raw("SELECT farmerID, distributionID, lastName, firstName, midName, extName, fullName, sex, birthdate, region, province,municipality,barangay, affiliationType, affiliationName, affiliationAccreditation,isDaAccredited, isLGU, rsbsa_control_no, isNew, `send`, `update`,actual_area, area
							from $database.farmer_profile 
						where rsbsa_control_no LIKE '$rsbsa%' 
						order by replace(lastName,' ','') asc, replace(firstName,' ','')")
					);
					
					//dd($other_info_sql2);
				
					foreach($other_info_sql2 as $oi_row2){
						array_push($other_info,array(
							"farmerID" => $oi_row2->farmerID,
							"distributionID" => $oi_row2->distributionID,
							"lastName" => $oi_row2->lastName,
							"firstName" => $oi_row2->firstName,
							"midName" => $oi_row2->midName,
							"extName" => $oi_row2->extName,
							"fullName" => $oi_row2->fullName,
							"sex" => $oi_row2->sex,
							"birthdate" => $oi_row2->birthdate,
							"region" => $oi_row2->region,
							"province" => $oi_row2->province,
							"municipality" => $oi_row2->municipality,
							"barangay" => $oi_row2->barangay,
							"affiliationType" => $oi_row2->affiliationType,
							"affiliationName" => $oi_row2->affiliationName,
							"affiliationAccreditation" => $oi_row2->affiliationAccreditation,
							"isDaAccredited" => $oi_row2->isDaAccredited,
							"isLGU" => $oi_row2->isLGU,
							"rsbsa_control_no" => $oi_row2->rsbsa_control_no,
							"isNew" => $oi_row2->isNew,
							"send" => $oi_row2->send,
							"update" => $oi_row2->update,
							"actual_area" => $oi_row2->actual_area,
							"area" => $oi_row2->area,
							"asdas" => "<br>"
						));
					}

				}
				
				$profile_arr = array();
				foreach($other_info as $row){
					$profile_check = DB::table($database.'.other_info')
						->where('rsbsa_control_no', $row["rsbsa_control_no"])
						->orderBy('info_id')
						->first();
						
					if(count($profile_check) > 0){
						array_push($profile_arr, array(
							"oi_info_id" => $profile_check->info_id,
							"oi_farmer_id" => $profile_check->farmer_id,
							"oi_rsbsa_control_no" => $profile_check->rsbsa_control_no,
							"oi_mother_fname" => $profile_check->mother_fname,
							"oi_mother_mname" => $profile_check->mother_mname,
							"oi_mother_lname" => $profile_check->mother_lname,
							"oi_mother_suffix" => $profile_check->mother_suffix,
							"oi_birthdate" => $profile_check->birthdate,
							"oi_is_representative" => $profile_check->is_representative,
							"oi_id_type" => $profile_check->id_type,
							"oi_relationship" => $profile_check->relationship,
							"oi_have_pic" => $profile_check->have_pic,
							"oi_phone" => $profile_check->phone,
							"oi_send" => $profile_check->send,
							
							"fp_farmerID" => $row["farmerID"],
							"fp_distributionID" => $row["distributionID"],
							"fp_lastName" => $row["lastName"],
							"fp_firstName" => $row["firstName"],
							"fp_midName" => $row["midName"],
							"fp_extName" => $row["extName"],
							"fp_fullName" => $row["fullName"],
							"fp_sex" => $row["sex"],
							"fp_birthdate" => $row["birthdate"],
							"fp_region" => $row["region"],
							"fp_province" => $row["province"],
							"fp_municipality" => $row["municipality"],
							"fp_barangay" => $row["barangay"],
							"fp_affiliationType" => $row["affiliationType"],
							"fp_affiliationName" => $row["affiliationName"],
							"fp_affiliationAccreditation" => $row["affiliationAccreditation"],
							"fp_isDaAccredited" => $row["isDaAccredited"],
							"fp_isLGU" => $row["isLGU"],
							"fp_rsbsa_control_no" => $row["rsbsa_control_no"],
							"fp_isNew" => $row["isNew"],
							"fp_send" => $row["send"],
							"fp_update" => $row["update"],
							"fp_actual_area" => $row["actual_area"],
							"fp_area" => $row["area"]
						));
					}
				}
				//dd($profile_arr);

				//save pdf to directory
				$pdf_name = $code."_FLSAR_".strtoupper($municipality_name).".pdf";
				$path = public_path('flsar\\' . $pdf_name);

				$list = $profile_arr;
				//$list = json_decode(json_encode($list), true);
				//$list = $this->array_orderby($list_for_sort, 'fp_rsbsa_control_no', SORT_ASC,'fp_lastName', SORT_ASC, 'fp_firstName', SORT_ASC);


				$pdf = PDFTIM::loadView('farmer.preList.list_home', 
					['list' => $list, 'region_code' => $region_code, 
					"province_code" => $province_code, "municipality_code" => $municipality_code])
				->setPaper('Legal', 'landscape');
				return $pdf->stream($pdf_name);        
				

		}catch(\Illuminate\Database\QueryException $ex){
            //return 'sql_error';
			dd($ex);
		}
	}




		public function generate_Provincemunicipality_serverSide($province_name, $municipality_name, $skip, $take){
		//$file = new Filesystem;
		//$file->cleanDirectory('public/flsar');
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
				->orderBy('rsbsa_control_number', 'ASC')
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
			$title = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name);
			$pdf_name = $municipal_details->prv."_FLSAR_".strtoupper($municipality_name).".pdf";
			$path = public_path('flsar\\' . $pdf_name);


			$pdf = PDFTIM::loadView('farmer.preList.list_home', 
				['list' => $list, 'region_code' => $region_code, 
				"province_code" => $province_code, "municipality_code" => $municipality_code,
				"title" => $title])
				->setPaper('Legal', 'landscape');
			return $pdf->stream($pdf_name);
				

		}catch(\Illuminate\Database\QueryException $ex){
            //return 'sql_error';
			dd($ex);
		}
	}





		public function generate_municipality_serverSide($province_name){
		//$file = new Filesystem;
		//$file->cleanDirectory('public/flsar');
		ini_set('memory_limit', '-1');
	
		try{
			//get all municipalities...
			$municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")
				->where('province', "like" ,$province_name)
				->groupBy('municipality')
				->limit(1)
				->get();
			
				foreach($municipality_list as $m_row){
				$code = $m_row->prv;
			
				$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
				$region_code = substr($code,0,2);
				$province_code = substr($code,2,2);
				$municipality_code = substr($code,4,2);

				$municipality_name = $m_row->municipality;
				$province_name = $m_row->province;
				$mun_code = $m_row->prv_dropoff_id;

				$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
				//$rsbsa_reference = "03"."-"."49"."-"."30";
				/*$other_info = DB::table($database.'.farmer_profile')
					->where('lastName', '!=', '')
					->where('firstName', '!=', '')
					->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
					->orderBy('rsbsa_control_no', 'ASC')
					->orderBy('lastName', 'ASC')
					->orderBy('firstName', 'ASC')
					//->limit(200)
					->get();*/
					
				$other_info = array();
				$other_info_sql1 = DB::select( DB::raw("
					SELECT rsbsa_control_no,SUBSTRING(rsbsa_control_no, 1, 16) as foo 
						from $database.farmer_profile 
					where rsbsa_control_no LIKE '$rsbsa_reference%' 
					and firstName !='' 
					group by foo 
					order by foo asc")
				);

				foreach($other_info_sql1 as $oi_row){
					$rsbsa = $oi_row->foo;
					$other_info_sql2 =DB::select( DB::raw("SELECT farmerID, distributionID, lastName, firstName, midName, extName, fullName, sex, birthdate, region, province,municipality,barangay, affiliationType, affiliationName, affiliationAccreditation,isDaAccredited, isLGU, rsbsa_control_no, isNew, `send`, `update`,actual_area, area
							from $database.farmer_profile 
						where rsbsa_control_no LIKE '$rsbsa%' 
						order by replace(lastName,' ','') asc, replace(firstName,' ','') asc ")
					);

					foreach($other_info_sql2 as $oi_row2){
						array_push($other_info,array(
							"farmerID" => $oi_row2->farmerID,
							"distributionID" => $oi_row2->distributionID,
							"lastName" => $oi_row2->lastName,
							"firstName" => $oi_row2->firstName,
							"midName" => $oi_row2->midName,
							"extName" => $oi_row2->extName,
							"fullName" => $oi_row2->fullName,
							"sex" => $oi_row2->sex,
							"birthdate" => $oi_row2->birthdate,
							"region" => $oi_row2->region,
							"province" => $oi_row2->province,
							"municipality" => $oi_row2->municipality,
							"barangay" => $oi_row2->barangay,
							"affiliationType" => $oi_row2->affiliationType,
							"affiliationName" => $oi_row2->affiliationName,
							"affiliationAccreditation" => $oi_row2->affiliationAccreditation,
							"isDaAccredited" => $oi_row2->isDaAccredited,
							"isLGU" => $oi_row2->isLGU,
							"rsbsa_control_no" => $oi_row2->rsbsa_control_no,
							"isNew" => $oi_row2->isNew,
							"send" => $oi_row2->send,
							"update" => $oi_row2->update,
							"actual_area" => $oi_row2->actual_area,
							"area" => $oi_row2->area	
						));
					}
				}

				$profile_arr = array();
				foreach($other_info as $row){
					$profile_check = DB::table($database.'.other_info')
						->where('rsbsa_control_no', $row["rsbsa_control_no"])
						->orderBy('info_id')
						->first();
						
					if(count($profile_check) > 0){
						array_push($profile_arr, array(
							"oi_info_id" => $profile_check->info_id,
							"oi_farmer_id" => $profile_check->farmer_id,
							"oi_rsbsa_control_no" => $profile_check->rsbsa_control_no,
							"oi_mother_fname" => $profile_check->mother_fname,
							"oi_mother_mname" => $profile_check->mother_mname,
							"oi_mother_lname" => $profile_check->mother_lname,
							"oi_mother_suffix" => $profile_check->mother_suffix,
							"oi_birthdate" => $profile_check->birthdate,
							"oi_is_representative" => $profile_check->is_representative,
							"oi_id_type" => $profile_check->id_type,
							"oi_relationship" => $profile_check->relationship,
							"oi_have_pic" => $profile_check->have_pic,
							"oi_phone" => $profile_check->phone,
							"oi_send" => $profile_check->send,
							
							"fp_farmerID" => $row["farmerID"],
							"fp_distributionID" => $row["distributionID"],
							"fp_lastName" => $row["lastName"],
							"fp_firstName" => $row["firstName"],
							"fp_midName" => $row["midName"],
							"fp_extName" => $row["extName"],
							"fp_fullName" => $row["fullName"],
							"fp_sex" => $row["sex"],
							"fp_birthdate" => $row["birthdate"],
							"fp_region" => $row["region"],
							"fp_province" => $row["province"],
							"fp_municipality" => $row["municipality"],
							"fp_barangay" => $row["barangay"],
							"fp_affiliationType" => $row["affiliationType"],
							"fp_affiliationName" => $row["affiliationName"],
							"fp_affiliationAccreditation" => $row["affiliationAccreditation"],
							"fp_isDaAccredited" => $row["isDaAccredited"],
							"fp_isLGU" => $row["isLGU"],
							"fp_rsbsa_control_no" => $row["rsbsa_control_no"],
							"fp_isNew" => $row["isNew"],
							"fp_send" => $row["send"],
							"fp_update" => $row["update"],
							"fp_actual_area" => $row["actual_area"],
							"fp_area" => $row["area"]
						));
					}
				}

				//save pdf to directory
				$pdf_name = $code."_FLSAR_".strtoupper($municipality_name).".pdf";
				$path = public_path('flsar\\' . $pdf_name);

				$list = $profile_arr;
				//$list = json_decode(json_encode($list), true);
				//$list = $this->array_orderby($list_for_sort, 'fp_rsbsa_control_no', SORT_ASC,'fp_lastName', SORT_ASC, 'fp_firstName', SORT_ASC);


				$pdf = PDFTIM::loadView('farmer.preList.list_home', 
					['list' => $list, 'region_code' => $region_code, 
					"province_code" => $province_code, "municipality_code" => $municipality_code])
				->setPaper('Legal', 'landscape')
				->save($path);        
			}

		}catch(\Illuminate\Database\QueryException $ex){
            //return 'sql_error';
			dd($ex);
		}
	}





		public function generate_municipality_excel($prv){
		$rpt_database = "rpt_".substr($prv, 0, 4);
		$tbl_database = "tbl_".$prv;

		$municipal_details = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
			->where('prv_dropoff_id', 'like', $prv.'%')
			->first();

		$municipal_data = DB::table($rpt_database.'.'.$tbl_database)
			->select('rsbsa_control_number', 'farmer_fname', 'farmer_mname', 'farmer_lname', 'farmer_ext', 'sex', 'birthdate',
					'mother_fname', 'mother_mname', 'mother_lname', 'mother_ext', 'actual_area')
			->orderBy('rsbsa_control_number', 'ASC')
			->orderBy('farmer_fname', 'ASC')
			->orderBy('farmer_mname', 'ASC')
			->orderBy('farmer_lname', 'ASC')
			->get();

		$excel_data = array();
		$row_cnt = 1;

		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());

		array_push($excel_data, array(
			'#' => 'FARMER NAME (Last Name, First Name, Middle Name, Extension)',
			'Farmer\'s first name' => '',
			'Farmer\'s middle name' => '',
			'Farmer\'s last name' => '',
			'Farmer\'s extension name' => '',
			'RSBSA control #' => '',
			'Sex' => '',
			'birthdate' => '',
			'Mother\'s first name' => 'Mother\'s Maiden Name (Last Name, First Name, Middle Name, Extension)',
			'Mother\'s middle name' => '',
			'Mother\'s last name' => '',
			'Mother\'s extension name' => '',
			'Contact #' => '',
			'Area Planted' => '',
			'Variety' => '',
			'No. of bags' => '',
			'No. of leaflets' => '',
			'No. of calendars' => '',
			'QR Code' => '',
			'Name of Authorized Representative' => '',
			'Signature of Claimant' => ''	
		));

		array_push($excel_data, array(
			'#' => '',
			'Farmer\'s first name' => 'Farmer\'s first name',
			'Farmer\'s middle name' => 'Farmer\'s middle name',
			'Farmer\'s last name' => 'Farmer\'s last name',
			'Farmer\'s extension name' => 'Farmer\'s extension name',
			'RSBSA control #' => 'RSBSA control #',
			'Sex' => 'Sex',
			'birthdate' => 'birthdate',
			'Mother\'s first name' => 'Mother\'s first name',
			'Mother\'s middle name' => 'Mother\'s middle name',
			'Mother\'s last name' => 'Mother\'s last name',
			'Mother\'s extension name' => 'Mother\'s extension name',
			'Contact #' => 'Contact #',
			'Area Planted' => 'Area Planted',
			'Variety' => 'Variety',
			'No. of bags' => 'No. of bags',
			'No. of leaflets' => 'No. of leaflets',
			'No. of calendars' => 'No. of calendars',
			'QR Code' => 'QR Code',
			'Name of Authorized Representative' => 'Name of Authorized Representative',
			'Signature of Claimant' => 'Signature of Claimant'	
		));

		foreach($municipal_data as $row){
			array_push($excel_data, array(
				'#' => $row_cnt,
				'Farmer\'s first name' => $row->farmer_fname,
				'Farmer\'s middle name' => $row->farmer_mname,
				'Farmer\'s last name' => $row->farmer_lname,
				'Farmer\'s extension name' => $row->farmer_ext,
				'RSBSA control #' => $row->rsbsa_control_number,
				'Sex' => $row->sex,
				'birthdate' => $row->birthdate,
				'Mother\'s first name' => $row->mother_fname,
				'Mother\'s middle name' => $row->mother_mname,
				'Mother\'s last name' => $row->mother_lname,
				'Mother\'s extension name' => $row->mother_ext,
				'Contact #' => '',
				'Area Planted' => $row->actual_area,
				'Variety' => '',
				'No. of bags' => '',
				'No. of leaflets' => '',
				'No. of calendars' => '',
				'QR Code' => '',
				'Name of Authorized Representative' => '',
				'Signature of Claimant' => ''	
			));

			$row_cnt++;
		}

		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());

		array_push($excel_data, array(
			'#' => '',
			'Farmer\'s first name' => 'Issued By:',
			'Farmer\'s middle name' => '',
			'Farmer\'s last name' => '',
			'Farmer\'s extension name' => '',
			'RSBSA control #' => '',
			'Sex' => '',
			'birthdate' => '',
			'Mother\'s first name' => 'Certified By:',
			'Mother\'s middle name' => '',
			'Mother\'s last name' => '',
			'Mother\'s extension name' => '',
			'Contact #' => '',
			'Area Planted' => '',
			'Variety' => '',
			'No. of bags' => '',
			'No. of leaflets' => '',
			'No. of calendars' => '',
			'QR Code' => '',
			'Name of Authorized Representative' => '',
			'Signature of Claimant' => ''	
		));

		array_push($excel_data, array(
			'#' => '',
			'Farmer\'s first name' => '',
			'Farmer\'s middle name' => '___________________________________',
			'Farmer\'s last name' => '',
			'Farmer\'s extension name' => '',
			'RSBSA control #' => '',
			'Sex' => '',
			'birthdate' => '',
			'Mother\'s first name' => '',
			'Mother\'s middle name' => '___________________________________',
			'Mother\'s last name' => '',
			'Mother\'s extension name' => '',
			'Contact #' => '',
			'Area Planted' => '',
			'Variety' => '',
			'No. of bags' => '',
			'No. of leaflets' => '',
			'No. of calendars' => '',
			'QR Code' => '',
			'Name of Authorized Representative' => '',
			'Signature of Claimant' => ''	
		));

		array_push($excel_data, array(
			'#' => '',
			'Farmer\'s first name' => '',
			'Farmer\'s middle name' => '  Signature above printed name of PRC/RC  ',
			'Farmer\'s last name' => '',
			'Farmer\'s extension name' => '',
			'RSBSA control #' => '',
			'Sex' => '',
			'birthdate' => '',
			'Mother\'s first name' => '',
			'Mother\'s middle name' => '  Signature above printed name of PDO  ',
			'Mother\'s last name' => '',
			'Mother\'s extension name' => '',
			'Contact #' => '',
			'Area Planted' => '',
			'Variety' => '',
			'No. of bags' => '',
			'No. of leaflets' => '',
			'No. of calendars' => '',
			'QR Code' => '',
			'Name of Authorized Representative' => '',
			'Signature of Claimant' => ''	
		));

		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());
		array_push($excel_data, $this->blank_row_flsar());

		array_push($excel_data, array(
			'#' => '',
			'Farmer\'s first name' => 'FORM 4',
			'Farmer\'s middle name' => '',
			'Farmer\'s last name' => '',
			'Farmer\'s extension name' => '',
			'RSBSA control #' => '',
			'Sex' => '',
			'birthdate' => '',
			'Mother\'s first name' => 'PHILRICE RCEF Seed FLSAR Rev 00 Effectivity Date: 23 March 2020',
			'Mother\'s middle name' => '',
			'Mother\'s last name' => '',
			'Mother\'s extension name' => '',
			'Contact #' => '',
			'Area Planted' => '',
			'Variety' => '',
			'No. of bags' => '',
			'No. of leaflets' => '',
			'No. of calendars' => '',
			'QR Code' => '',
			'Name of Authorized Representative' => '',
			'Signature of Claimant' => ''	
		));

		$region = substr($prv, 0, 2);
		$province = substr($prv, 2, 2);
		$municipality = substr($prv, 4, 2);

		//$excel_data = json_decode(json_encode($municipal_data), true); //convert collection to associative array to be converted to excel
		return Excel::create("FLSAR"."_".$prv."_".$municipal_details->province.'_'.$municipal_details->municipality.date("Y-m-d g:i A"), function($excel) use ($excel_data, $region, $province, $municipality) {
			
			$excel->sheet("FARMER LIST", function($sheet) use ($excel_data, $region, $province, $municipality) {
				$sheet->cell('B2', function($cell) {
					$cell->setValue('Season Year: ____DRY SEASON 2021___');
				});

				$sheet->cell('B3', function($cell) {
					$cell->setValue('');
				});

				$sheet->setCellValue('B3', 'Drop-off Point (Municipality, Province): ________________');
				$sheet->setCellValue('B4', 'RSBSA Code: Region: _'.$region.'_, Province: _'.$province.'_, Municipality: _'.$municipality.'_');

				$sheet->mergeCells('B2:E2', 'left');
				$sheet->mergeCells('B3:E3', 'left');
				$sheet->mergeCells('B4:E4', 'left');
				
				$sheet->setSize('A1', 0, 90);
				$sheet->cell('H1', function($cell) {
					$cell->setValue('Farmer Acknowledgement Receipt (Seeds, Leaflet, Calendar) RCEF Seed Program');
					$cell->setValignment('center');
					$cell->setFont(array(
						'family'     => 'Calibri',
						'size'       => '16',
						'bold'       =>  true
					));
				});

				$sheet->cell('A6', function($cell) {
					$cell->setValignment('center');
				});
				$sheet->cell('I6', function($cell) {
					$cell->setValignment('center');
				});

				$sheet->mergeCells('H1:O1', 'center');
				$sheet->mergeCells('A6:E6', 'center');
				$sheet->mergeCells('I6:L6', 'center');

				$sheet->setSize('A6', 170, 30);
				$sheet->setSize('A7', 5, 10);
				$sheet->setSize('B7', 25, 20);
				$sheet->setSize('C7', 25, 20);
				$sheet->setSize('D7', 25, 20);
				$sheet->setSize('E7', 25, 20);

				$sheet->setSize('F7', 20, 20);
				$sheet->setSize('H7', 20, 20);

				$sheet->setSize('I7', 25, 20);
				$sheet->setSize('J7', 25, 20);
				$sheet->setSize('K7', 25, 20);
				$sheet->setSize('L7', 25, 20);

				$sheet->setSize('M7', 20, 20);
				$sheet->setSize('N7', 20, 15);

				$sheet->setSize('O7', 15, 15);
				$sheet->setSize('P7', 15, 15);
				$sheet->setSize('Q7', 15, 15);
				$sheet->setSize('R7', 15, 15);
				$sheet->setSize('S7', 15, 15);

				$sheet->setSize('T7', 35, 15);
				$sheet->setSize('U7', 35, 15);
				
				$sheet->fromArray($excel_data, null, 'A1', false, false);
				
				//philrice logo
				$objDrawing = new PHPExcel_Worksheet_Drawing;
				$objDrawing->setPath(public_path('images/philrice_logo_box.jpg')); //your image path
				$objDrawing->setCoordinates('A1');
				$objDrawing->setHeight(100);
				$objDrawing->setWorksheet($sheet);
				
				//socotec logo
				$objDrawing2 = new PHPExcel_Worksheet_Drawing;
				$objDrawing2->setPath(public_path('images/Socotec-Logo.jpg')); //your image path
				$objDrawing2->setCoordinates('S1');
				$objDrawing2->setHeight(100);
				$objDrawing2->setWorksheet($sheet);
				

				/*$sheet->prependRow(1, array(
					'RSBSA control #', 'Farmer\'s first name', 'Farmer\'s middle name', 'Farmer\'s last name', 'Farmer\'s extension name',
					'Sex', 'Birthday', 'Mother\'s first name', 'Mother\'s middle name', 'Mother\'s last name', 'Mother\'s extension name',
					'Area Planted' 
				));*/
				
				
				//$sheet->freezeFirstRow();
				//$sheet->freezeSecondRow();
			});
		})->download('xlsx');
	}



	public function get_municipalities(Request $request){
		$regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')->groupBy('province', 'municipality')->orderBy('region_sort')->get();

		$return_str = ""; 
		$return_str .= "<option value='0'>Please select a Municipality</option>";
		foreach($regions as $row){
			$return_str .= "<option value='".$row->prv."_FLSAR_".strtoupper($row->municipality).".pdf'>$row->province < $row->municipality</option>";
		}

		return $return_str;
	}

	public function generate_blank($page_count){
		$pdf = PDFTIM::loadView('farmer.preList.list_blank', 
				['page_count' => $page_count])
			->setPaper('legal', 'landscape');        
            $pdf_name = "FLSAR_BLANK".".pdf";
        return $pdf->stream($pdf_name);
	}

    public function generate_municipality($code){

        try{
			$database = $GLOBALS['season_prefix']."prv_".substr($code,0,4);
			$region_code = substr($code,0,2);
			$province_code = substr($code,2,2);
			$municipality_code = substr($code,4,2);

			$municipality_name = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_dropoff_point")->where('prv', $code)->value("municipality");

			$rsbsa_reference = substr($code,0,2)."-".substr($code,2,2)."-".substr($code,4,2);
			$other_info = DB::table($database.'.other_info')
				->select('info_id', 'farmer_id', 'rsbsa_control_no', 'mother_fname', 'mother_lname', "mother_mname",
						 'mother_suffix', 'birthdate', 'is_representative', 'id_type', 'relationship', 
						 'have_pic', 'phone', 'send')
                ->where('rsbsa_control_no', 'like', '%' . $rsbsa_reference . '%')
                //->limit(15)
				->get();
				
			$profile_arr = array();
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
					array_push($profile_arr, array(
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
            
            //$list = json_encode($profile_arr);
            $list = $profile_arr;
			$pdf = PDFTIM::loadView('farmer.preList.list_home', 
				['list' => $list, 'region_code' => $region_code, 
				"province_code" => $province_code, "municipality_code" => $municipality_code])
			->setPaper('legal', 'landscape');        
            $pdf_name = "FLSAR_".strtoupper($municipality_name).".pdf";
            return $pdf->stream($pdf_name);

		}catch(\Illuminate\Database\QueryException $ex){
            return 'sql_error';
        }
    }
}

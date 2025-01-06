<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Config;
use Auth;

use Yajra\Datatables\Facades\Datatables;

class SubServerController extends Controller
{
    /*public function sync_area_history(Request $request){
		DB::beginTransaction();
		
		$database = $request->database;
		$farmerId = $request->farmerId;
		$region = $request->region;
		$province = $request->province;
		$municipality = $request->municipality;
		$barangay = $request->barangay;
		$area = $request->area;
		$dateCreated = $request->dateCreated;
		$rsbsa_control_no = $request->rsbsa_control_no;

		try {
            $check_row = DB::table($database.'.area_history')
                ->where('farmerId', $farmerId)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->count();

            if($check_row == 0){
                DB::table($database.'.area_history')
                    ->insert([
                        'farmerId' => $farmerId,
                        'region' => $region,
                        'province' => $province,
                        'municipality' => $municipality,
                        'barangay' => $barangay,
                        'area' => $area,
                        'dateCreated' => $dateCreated,
                        'rsbsa_control_no' => $rsbsa_control_no,		
                        'send' => 0
                    ]);
                DB::commit();
                
                return 'insert_success';
            }else{
                return 'row_exists';
            }

		
		}catch (\Exception $e) {
			DB::rollback();
			return "sql_error";
			//return $e;
		}
	}
	
	public function sync_farmer_profile(Request $request){
		DB::beginTransaction();
		
        $database = $request->database;
        $farmerID = $request->farmerID;
        $distributionID = $request->distributionID;
        $lastName = $request->lastName;
        $firstName = $request->firstName;
        $midName = $request->midName;
        $extName = $request->extName;
        $fullName = $request->fullName;
        $sex = $request->sex;
        $birthdate = $request->birthdate;
        $region = $request->region;
        $province = $request->province;
        $municipality = $request->municipality;
        $barangay = $request->barangay;
        $affiliationType = $request->affiliationType;
        $affiliationName = $request->affiliationName;
        $affiliationAccreditation = $request->affiliationAccreditation;
        $isDaAccredited = $request->isDaAccredited;
        $isLGU = $request->isLGU;
        $rsbsa_control_no = $request->rsbsa_control_no;
        $isNew = $request->isNew;
        $update = $request->update;
        $area = $request->area;
        $actual_area = $request->actual_area;
        $send = 0;
		
		try {
            $check_row = DB::table($database.'.farmer_profile')
                ->where('farmerID', $farmerID)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->count();

            if($check_row == 0){
                DB::table($database.'.farmer_profile')
				->insert([
                    'farmerID' => $farmerID,
                    'distributionID' => $distributionID,
                    'lastName' => $lastName,
                    'firstName' => $firstName,
                    'midName' => $midName,
                    'extName' => $extName,
                    'fullName' => $fullName,
                    'sex' => $sex,
                    'birthdate' => $birthdate,
                    'region' => $region,
                    'province' => $province,
                    'municipality' => $municipality,
                    'barangay' => $barangay,
                    'affiliationType' => $affiliationType,
                    'affiliationName' => $affiliationName,
                    'affiliationAccreditation'=> $affiliationAccreditation,
                    'isDaAccredited' => $isDaAccredited,
                    'isLGU' => $isLGU,
                    'rsbsa_control_no' => $rsbsa_control_no,
                    'isNew' => $isNew,
                    'update' => $update,
                    'area' => $area,
                    'actual_area' => $actual_area,
                    'send' => 0
				]);
                DB::commit();
                
                return 'insert_success';
            }else{
                return 'row_exists';
            }

			
		}catch (\Exception $e) {
			DB::rollback();
			//return "sql_error";
			return $e;
        }
    }
	
	public function sync_other_info(Request $request){
		DB::beginTransaction();

		$database = $request->database;
		$farmer_id = $request->farmer_id;
		$rsbsa_control_no = $request->rsbsa_control_no;
		$mother_fname = $request->mother_fname;
		$mother_mname = $request->mother_mname;
		$mother_lname = $request->mother_lname;
		$mother_suffix = $request->mother_suffix;
		$birthdate = $request->birthdate;
		$is_representative = $request->is_representative;
		$id_type = $request->id_type;
		$relationship = $request->relationship;
		$have_pic = $request->have_pic;
		$send = 0;
		
		\Config::set('database.connections.reports_db.database', $request->database);
		DB::purge('reports_db');
		
		try {
            $check_row = DB::table($database.'.other_info')
                ->where('farmer_id', $farmer_id)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->count();

            if($check_row == 0){
                DB::connection('reports_db')->table('other_info')
                    ->insert([
                        'farmer_id' => $farmer_id,
                        'rsbsa_control_no' => $rsbsa_control_no,
                        'mother_fname' => $mother_fname,
                        'mother_mname' => $mother_mname,
                        'mother_lname' => $mother_lname,
                        'mother_suffix' => $mother_suffix,
                        'birthdate' => $birthdate,
                        'is_representative' => $is_representative,
                        'id_type' => $id_type,
                        'relationship' => $relationship,
                        'have_pic' => $have_pic,
                        'send' => 0
                    ]);
                DB::commit();
                return 'insert_success';
            }else{
                return 'row_exists';
            }
			
		}catch (\Exception $e) {
			DB::rollback();
		//	return "sql_error";
			return $e;
		}
	}
	
	public function sync_pending_release(Request $request){
        DB::beginTransaction();

        $database = $request->database;
        $farmer_id = $request->farmer_id;
        $rsbsa_control_no = $request->rsbsa_control_no;
        $ticket_no = $request->ticket_no;
        $batch_ticket_no = $request->batch_ticket_no;
        $province = $request->province;
        $municipality = $request->municipality;
        $dropOffPoint = $request->dropOffPoint;
        $seed_variety = $request->seed_variety;
        $bags = $request->bags;
        $date_created = $request->date_created;
        $is_released = $request->is_released;
        $created_by = $request->created_by;
        $send = 0;
        $prv_dropoff_id = $request->prv_dropoff_id;
        
        try {

            $check_row = DB::table($database.'.pending_release')
                ->where('farmer_id', $farmer_id)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->count();

            if($check_row == 0){
                DB::table($database.'.pending_release')
                ->insert([
                    'farmer_id' => $farmer_id,
                    'rsbsa_control_no' => $rsbsa_control_no,
                    'ticket_no' => $ticket_no,
                    'batch_ticket_no' => $batch_ticket_no,
                    'province' => $province,
                    'municipality' => $municipality,
                    'dropOffPoint' => $dropOffPoint,
                    'seed_variety' => $seed_variety,
                    'bags' => $bags,
                    'date_created' => $date_created,
                    'is_released' => $is_released,
                    'created_by' => $created_by,
                    'send' => 0,
                    'prv_dropoff_id' => $prv_dropoff_id
                ]);
                DB::commit();
                return 'insert_success';
            }else{
                return 'row_exists';
            }

               
        }catch (\Exception $e) {
            DB::rollback();
            //return "sql_error";
            return $e;
        }
    }
	
	public function sync_performance(Request $request){
        DB::beginTransaction();
        $database = $request->database;
        $farmerID = $request->farmerID;
        $rsbsa_control_no = $request->rsbsa_control_no;
        $variety_used = $request->variety_used;
        $seed_usage = $request->seed_usage;
        $yield = $request->yield;
        $preferred_variety = $request->preferred_variety;
        $area_planted = $request->area_planted;
        $send = 0;
        
        try {
            $check_row = DB::table($database.'.performance')
                ->where('farmerID', $farmerID)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->count();

            if($check_row == 0){
                DB::table($database.'.performance')
                    ->insert([
                        'farmerID' => $farmerID,
                        'rsbsa_control_no' => $rsbsa_control_no,
                        'variety_used' => $variety_used,
                        'seed_usage' => $seed_usage,
                        'yield' => $yield,
                        'preferred_variety' => $preferred_variety,
                        'area_planted' => $area_planted,
                        'send' => 0
                    ]);
                DB::commit();

                return 'insert_success';
            }else{
                return 'row_exists';
            }
            
        }catch (\Exception $e) {
            DB::rollback();
            //return "sql_error";
            return $e;
        }
    }
	
	public function sync_released(Request $request){
        DB::beginTransaction();
        $database = $request->database;
        $farmer_id = $request->farmer_id;
        $rsbsa_control_no = $request->rsbsa_control_no;
        $ticket_no = $request->ticket_no;
        $batch_ticket_no = $request->batch_ticket_no;
        $province = $request->province;
        $municipality = $request->municipality;
        $dropOffPoint = $request->dropOffPoint;
        $seed_variety = $request->seed_variety;
        $bags = $request->bags;
        $date_released = $request->date_released;
        $released_by = $request->released_by;
        $send = $request->send;
        $prv_dropoff_id = $request->prv_dropoff_id;
        
        \Config::set('database.connections.reports_db.database', $request->database);
        DB::purge('reports_db');
        
        try {
            $check_row = DB::table($database.'.released')
                ->where('farmer_id', $farmer_id)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->count();
            
            if($check_row == 0){
                DB::table($database.'.released')
                ->insert([
                    'farmer_id' => $farmer_id,
                    'rsbsa_control_no' => $rsbsa_control_no,
                    'ticket_no' => $ticket_no,
                    'batch_ticket_no' => $batch_ticket_no,
                    'province' => $province,
                    'municipality' => $municipality,
                    'dropOffPoint' => $dropOffPoint,
                    'seed_variety' => $seed_variety,
                    'bags' => $bags,
                    'date_released' => $date_released,
                    'released_by' => $released_by,
                    'send' => 0,
                    'prv_dropoff_id' => $prv_dropoff_id
                ]);
            DB::commit();
                return 'insert_success';
            }else{
                return 'row_exists';
            } 
           
        }catch (\Exception $e) {
            DB::rollback();
            //return "sql_error";
            return $e;
        }
    }*/
	
	public function sync_area_history(Request $request){
        DB::beginTransaction();
        $database = $request->database;
		$data = json_decode($request->area_history_data);
		
		//process data - insert to database
		try {
            $ah_success_arr = array();
            foreach($data as $row){
                $check_row = DB::table($database.'.area_history')
                    ->where("farmerId", $row->farmerId)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->count();

                if($check_row == 0){
                    DB::table($database.'.area_history')
                        ->insert([
                            'farmerId' => $row->farmerId,
                            'region' => $row->region,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            'barangay' => $row->barangay,
                            'area' => $row->area,
                            'dateCreated' => $row->dateCreated,
                            'rsbsa_control_no' => $row->rsbsa_control_no,
                            'send' => 0
                        ]);
                    DB::commit();
                }
                
                $row_data = [
                    'areaHistoryId' => $row->areaHistoryId,
                    'farmerId' => $row->farmerId,
                    'rsbsa_control_no' => $row->rsbsa_control_no,
                ];
                array_push($ah_success_arr, $row_data);
            }

            return array(
                "process_status" => "insert_success",
                "process_arr" => json_encode($ah_success_arr)
            );

        }catch (\Exception $e) {
            DB::rollback();
            return array( "process_status" => "sql_error" );
        }
	}
	
	public function sync_farmer_profile(Request $request){
        DB::beginTransaction();
        $database = $request->database;
		$data = json_decode($request->farmer_profile_data);
		
		//process data - insert to database
		try {
            $success_arr = array();
            foreach($data as $row){
                $check_row = DB::table($database.'.farmer_profile')
                    ->where("farmerID", $row->farmerID)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->count();

                if($check_row == 0){
                    DB::table($database.'.farmer_profile')
                        ->insert([
                            'farmerID' => $row->farmerID,
                            'distributionID' => $row->distributionID,
                            'lastName' => $row->lastName,
                            'firstName' => $row->firstName,
                            'midName' => $row->midName,
                            'extName' => $row->extName,
                            'fullName' => $row->fullName,
                            'sex' => $row->sex,
                            'birthdate' => $row->birthdate,
                            'region' => $row->region,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            'barangay' => $row->barangay,
                            'affiliationType' => $row->affiliationType,
                            'affiliationName' => $row->affiliationName,
                            'affiliationAccreditation' => $row->affiliationAccreditation,
                            'isDaAccredited' => $row->isDaAccredited,
                            'isLGU' => $row->isLGU,
                            'rsbsa_control_no' => $row->rsbsa_control_no,
                            'isNew' => $row->isNew,
                            'send' => 0,
                            'update' => $row->update,
                            'area' => $row->area,
                            'actual_area' => $row->actual_area
                        ]);
                    DB::commit();
                }

                $row_data = [
                    'id' => $row->id,
                    'farmerID' => $row->farmerID,
                    'rsbsa_control_no' => $row->rsbsa_control_no,
                ];
                array_push($success_arr, $row_data);
            }

            return array(
                "process_status" => "insert_success",
                "process_arr" => json_encode($success_arr)
            );

        }catch (\Exception $e) {
            DB::rollback();
            return array( "process_status" => "sql_error" );
        }
    }
	
	public function sync_other_info(Request $request){
        DB::beginTransaction();
        $database = $request->database;
		$data = json_decode($request->other_info_data);
		
		//process data - insert to database
		try {
            $success_arr = array();
            foreach($data as $row){
                $check_row = DB::table($database.'.other_info')
                    ->where("farmer_id", $row->farmer_id)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->count();

                if($check_row == 0){
                    DB::table($database.'.other_info')
                        ->insert([
                            'farmer_id' => $row->farmer_id,
                            'rsbsa_control_no' => $row->rsbsa_control_no,
                            'mother_fname' => $row->mother_fname,
                            'mother_mname' => $row->mother_mname,
                            'mother_lname' => $row->mother_lname,
                            'mother_suffix' => $row->mother_suffix,
                            'birthdate' => $row->birthdate,
                            'is_representative' => $row->is_representative,
                            'id_type' => $row->id_type,
                            'relationship' => $row->relationship,
                            'have_pic' => $row->have_pic,
							'phone' => $row->phone,
                            'send' => 0
                        ]);
                    DB::commit();
                }

                $row_data = [
                    'info_id' => $row->info_id,
                    'farmer_id' => $row->farmer_id,
                    'rsbsa_control_no' => $row->rsbsa_control_no,
                ];
                array_push($success_arr, $row_data);
            }

            return array(
                "process_status" => "insert_success",
                "process_arr" => json_encode($success_arr)
            );

        }catch (\Exception $e) {
            DB::rollback();
            return array( "process_status" => "sql_error" );
        }
    }
	
	public function sync_pending_release(Request $request){
        DB::beginTransaction();
        $database = $request->database;
		$data = json_decode($request->pending_release_data);
		
		//process data - insert to database
		try {
            $success_arr = array();
            foreach($data as $row){
                $check_row = DB::table($database.'.pending_release')
                    ->where("farmer_id", $row->farmer_id)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->count();

                if($check_row == 0){
                    DB::table($database.'.pending_release')
                        ->insert([
                            'farmer_id' => $row->farmer_id,
                            'rsbsa_control_no' => $row->rsbsa_control_no,
                            'ticket_no' => $row->ticket_no,
                            'batch_ticket_no' => $row->batch_ticket_no,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            'dropOffPoint' => $row->dropOffPoint,
                            'seed_variety' => $row->seed_variety,
                            'bags' => $row->bags,
                            'date_created' => $row->date_created,
                            'is_released' => $row->is_released,
                            'created_by' => $row->created_by,
                            'send' => 0,
                            'prv_dropoff_id' => $row->prv_dropoff_id
                        ]);
                    DB::commit();
                }

                $row_data = [
                    'pending_id' => $row->pending_id,
                    'farmer_id' => $row->farmer_id,
                    'rsbsa_control_no' => $row->rsbsa_control_no,
                ];
                array_push($success_arr, $row_data);
            }

            return array(
                "process_status" => "insert_success",
                "process_arr" => json_encode($success_arr)
            );

        }catch (\Exception $e) {
            DB::rollback();
            return array( "process_status" => "sql_error" );
        }
    }
	
	public function sync_performance(Request $request){
        DB::beginTransaction();
        $database = $request->database;
		$data = json_decode($request->performance_data);
		
		//process data - insert to database
		try {
            $success_arr = array();
            foreach($data as $row){
                $check_row = DB::table($database.'.performance')
                    ->where("farmerID", $row->farmerID)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->count();

                if($check_row == 0){
                    DB::table($database.'.performance')
                        ->insert([
                            'farmerID' => $row->farmerID,
                            'rsbsa_control_no' => $row->rsbsa_control_no,
                            'variety_used' => $row->variety_used,
                            'seed_usage' => $row->seed_usage,
                            'yield' => $row->yield,
                            'preferred_variety' => $row->preferred_variety,
                            'area_planted' => $row->area_planted,
                            'send' => 0
                        ]);
                    DB::commit();
                }

                $row_data = [
                    'farmerID' => $row->farmerID,
                    'rsbsa_control_no' => $row->rsbsa_control_no,
                ];
                array_push($success_arr, $row_data);
            }

            return array(
                "process_status" => "insert_success",
                "process_arr" => json_encode($success_arr)
            );

        }catch (\Exception $e) {
            DB::rollback();
            return array( "process_status" => "sql_error" );
        }
    }
	
	public function sync_released(Request $request){
        DB::beginTransaction();
        $database = $request->database;
		$data = json_decode($request->released_data);
		
		//process data - insert to database
		try {
            $success_arr = array();
            foreach($data as $row){
                $check_row = DB::table($database.'.released')
                    ->where("farmer_id", $row->farmer_id)
                    ->where("rsbsa_control_no", $row->rsbsa_control_no)
                    ->count();

                if($check_row == 0){
                    DB::table($database.'.released')
                        ->insert([
                            'farmer_id' => $row->farmer_id,
                            'rsbsa_control_no' => $row->rsbsa_control_no,
                            'ticket_no' => $row->ticket_no,
                            'batch_ticket_no' => $row->batch_ticket_no,
                            'province' => $row->province,
                            'municipality' => $row->municipality,
                            'dropOffPoint' => $row->dropOffPoint,
                            'seed_variety' => $row->seed_variety,
                            'bags' => $row->bags,
                            'date_released' => $row->date_released,
                            'released_by' => $row->released_by,
                            'send' => 0,
                            'prv_dropoff_id' => $row->prv_dropoff_id
                        ]);
                    DB::commit();
                }

                $row_data = [
                    'farmer_id' => $row->farmer_id,
                    'rsbsa_control_no' => $row->rsbsa_control_no,
                ];
                array_push($success_arr, $row_data);
            }

            return array(
                "process_status" => "insert_success",
                "process_arr" => json_encode($success_arr)
            );

        }catch (\Exception $e) {
            DB::rollback();
            return array( "process_status" => "sql_error" );
        }
    }
}

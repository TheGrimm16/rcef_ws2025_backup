<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Checking;
use Yajra\Datatables\Datatables;

use DB;
use Auth;

class CheckingController extends Controller {

    public function index() {
        $checking = new Checking();
        $dropoffpoints = $checking->_drop_offpoints();

        $dropoff = array();

        foreach ($dropoffpoints as $item) {
            $data = array(
                'prv_dropoff_id' => $item->prv_dropoff_id,
                'dropOffPoint' => $item->municipality . ' - ' . $item->dropOffPoint
            );
            array_push($dropoff, $data);
        }
        return view('checking.index')
                        ->with(compact('dropoff'));
    }

    public function delete_farmer_data(Request $request) {
        $profile = new Checking();
        $input = $request->all();
        $profile->_delete_farmer_data($input['rsbsa']);

        //insert saving of logs here...
        DB::table($GLOBALS['season_prefix'].'rcep_dist_updates.tbl_rsbsa_updates')
        ->insert([
            'original_rsbsa' => $input['rsbsa'],
            'update_description' => "RSBSA_DELETED || FARMER_DATA_DELETED",
            'performed_by' => Auth::user()->username
        ]);
        
        return json_encode("success");
    }

    public function deleteFarmer(Request $request) {
        $profile = new Checking();
        $input = $request->all();
        $profile->_delete_farmer($input['farmer_id'], $input['qr_code'], $input['rsbsa_control_no']);
    }

    public function table(Request $request) {
        $input = $request->all();
        return view('checking.table');
    }

    public function showUnreleased(Request $request) {
        $profile = new Checking();
        $input = $request->all();

        $searchdata = $profile->_get_pendingreleases($input['drop_id']);
        $table_data = array();
        $i = 1;
        foreach ($searchdata as $dv) {
            $get_profile = $profile->_get_farmer_details($dv->farmer_id, $dv->rsbsa_control_no);
            $pending_data = $profile->_get_release_data($dv->farmer_id, $dv->rsbsa_control_no, "pending_release", 2);
            $release_count = $profile->_get_release_data($dv->farmer_id, $dv->rsbsa_control_no, "released", 1);

            $data_search = array(
                'number' => $i,
                'rsbsa' => $dv->rsbsa_control_no,
                'rsbsa_id' => $dv->rsbsa_control_no,
                'qr' => ($get_profile != "" ? $get_profile->distributionID : ""),
                'full_name' => ($get_profile != "" ? $get_profile->firstName . ' ' . $get_profile->midName . ' ' . $get_profile->lastName : ""),
                'variety' => ($pending_data != "" ? $pending_data->seed_variety : ""),
                'bags' => ($pending_data != "" ? $pending_data->bags : ""),
                'actual_area' => ($get_profile != "" ? $get_profile->actual_area : ""),
                'area' => ($get_profile != "" ? $get_profile->area : ""),
                'date' => ($pending_data != "" ? $pending_data->date_created : ""),
                'id' => ($get_profile != "" ? $get_profile->id : "")
            );
            array_push($table_data, $data_search);
            $i++;
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                ->addColumn('action', function($table_data) {
                    if(Auth::user()->roles->first()->name == "system-encoder"){
                        return '<a href="#" data-rsbsa="'.$table_data['rsbsa'].'" data-location="unrelease_tbl" data-toggle="modal" data-target="#edit_check_modal" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Edit Details</a>';
                    }else{
                        return '<a for="' . $table_data['rsbsa_id'] . '" class="btn btn-danger btn-xs deleteDatacheck"><i class="fa fa-trash-o"></i> Delete </a><br>
                            <a href="#" data-rsbsa="'.$table_data['rsbsa'].'" data-location="unrelease_tbl" data-toggle="modal" data-target="#edit_check_modal" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Edit Details</a>';
                    }                    
                })
                ->make(true);
    }

    public function search(Request $request) {
        $profile = new Checking();
        $input = $request->all();

        $searchdata = $profile->_get_farmer_profile($input['search_data']);
        $table_data = array();
        $i = 1;
        foreach ($searchdata as $dv) {
            $pending_data = $profile->_get_release_data($dv->farmerID, $dv->rsbsa_control_no, "pending_release", 2);
            $release_count = $profile->_get_release_data($dv->farmerID, $dv->rsbsa_control_no, "released", 1);
            $data_search = array(
                'number' => $i,
                'rsbsa' => $profile->_highlight($input['search_data'], $dv->rsbsa_control_no),
                'rsbsa_id' => $dv->rsbsa_control_no,
                'qr' => $profile->_highlight($input['search_data'], $dv->distributionID),
                'full_name' => $profile->_highlight($input['search_data'], $dv->firstName . ' ' . $dv->midName . ' ' . $dv->lastName),
                'variety' => ($pending_data != "" ? $pending_data->seed_variety : ""),
                'bags' => ($pending_data != "" ? $pending_data->bags : ""),
                'actual_area' => $dv->actual_area,
                'area' => $dv->area,
                'date' => ($pending_data != "" ? $pending_data->date_created : ""),
                'id' => $dv->id
            );
            array_push($table_data, $data_search);
            $i++;
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                ->addColumn('action', function($table_data) {
                    if(Auth::user()->roles->first()->name == "system-encoder"){
                        return '<a href="#" data-rsbsa="'.$table_data['rsbsa_id'].'" data-location="search_tbl" data-toggle="modal" data-target="#edit_check_modal" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Edit Details</a>';
                    }else{
                        return '<a for="' . $table_data['rsbsa_id'] . '" class="btn btn-danger btn-xs deleteDatacheck"><i class="fa fa-trash-o"></i> Delete </a><br>
                        <a href="#" data-rsbsa="'.$table_data['rsbsa_id'].'" data-location="search_tbl" data-toggle="modal" data-target="#edit_check_modal" class="btn btn-warning btn-xs"><i class="fa fa-edit"></i> Edit Details</a>';
                    }
                })
                ->make(true);
    }

    public function get_farmer_data_forEdit(Request $request){
        $current_rsbsa = $request->current_rsbsa;
        $prv_str = explode("-",$current_rsbsa);
        //$prv_code = $GLOBALS['season_prefix']."prv_".$prv_str[0].$prv_str[1];
        $prv_code = $GLOBALS['season_prefix']."prv_".Auth::user()->province;

        $pending_data  = DB::table($prv_code.".pending_release")->where('rsbsa_control_no', $request->current_rsbsa)->first();
        $profile_data  = DB::table($prv_code.".farmer_profile")->where('rsbsa_control_no', $request->current_rsbsa)->first();

        if(count($pending_data) > 0){
            $birth_date = DB::table($prv_code.".other_info")->where('rsbsa_control_no', $request->current_rsbsa)->value('birthdate');

            return array(
                "rsbsa_number" => $pending_data->rsbsa_control_no,
                "first_name" => $profile_data->firstName,
                "middle_name" => $profile_data->midName,
                "last_name" => $profile_data->lastName,
                "ext_name" => $profile_data->extName == "" ? "N/A" : $profile_data->extName,
                "actual_area" => $profile_data->actual_area,
                "dist_area" => $profile_data->area,
                "seed_variety" => $pending_data->seed_variety,
                "seed_bags" => $pending_data->bags,
                "birth_date" => $birth_date,
                "sex" => $profile_data->sex
            );
        }else{
            return array(
                "rsbsa_number" => $profile_data->rsbsa_control_no,
                "first_name" => $profile_data->firstName == "" ? "N/A" : $profile_data->firstName,
                "middle_name" => $profile_data->midName == "" ? "N/A" : $profile_data->midName,
                "last_name" => $profile_data->lastName == "" ? "N/A" : $profile_data->lastName,
                "ext_name" => $profile_data->extName == "" ? "N/A" : $profile_data->extName,
                "actual_area" => $profile_data->actual_area,
                "dist_area" => $profile_data->area,
                "seed_variety" => "",
                "seed_bags" => "",
                "sex" => $profile_data->sex
            );
        }
    }

    function delete_previous_record($old_rsbsa, $new_rsbsa, $database){
        

        DB::table($database . '.' . 'area_history')
            ->where('rsbsa_control_no', $new_rsbsa)
            ->delete();
    }

    public function update_farmer_data_forEdit_old(Request $request){
        DB::beginTransaction();
        try {
            $prv_str = explode("-",$request->old_rsbsa);
            //$prv_code = $GLOBALS['season_prefix']."prv_".$prv_str[0].$prv_str[1];
            $prv_code = $GLOBALS['season_prefix']."prv_".Auth::user()->province;

            if( $request->new_rsbsa != $request->old_rsbsa){
                /**DELETE DATA */
                DB::table($prv_code . '.' . 'farmer_profile')
                    ->where('rsbsa_control_no', $request->new_rsbsa)
                    ->delete();
                DB::table($prv_code . '.' . 'area_history')
                    ->where('rsbsa_control_no', $request->new_rsbsa)
                    ->delete();
                DB::table($prv_code . '.' . 'other_info')
                    ->where('rsbsa_control_no', $request->new_rsbsa)
                    ->delete();
                DB::table($prv_code . '.' . 'performance')
                    ->where('rsbsa_control_no', $request->new_rsbsa)
                    ->delete();
                /**DELETE DATA */
            }

            DB::table($prv_code.".area_history")
                ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                ->update(
                    [
                        'rsbsa_control_no' => $request->new_rsbsa, 
                    ]
                );

            DB::table($prv_code.".farmer_profile")
                ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                ->update(
                    [
                        'rsbsa_control_no' => $request->new_rsbsa,
                        'firstName' => $request->first_name,
                        'midName' => $request->middle_name,
                        'lastName' => $request->last_name,
                        'extName' => $request->ext_name,
                        'sex' => $request->sex 
                    ]
                );

            DB::table($prv_code.".other_info")
                ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                ->update(
                    [
                        'rsbsa_control_no' => $request->new_rsbsa,
                        'birthdate' => $request->birth_date
                    ]
                );
            
            DB::table($prv_code.".pending_release")
                ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                ->update(
                    [
                        'rsbsa_control_no' => $request->new_rsbsa, 
                    ]
                );
				

            DB::table($prv_code.".performance")
                ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                ->update(
                    [
                        'rsbsa_control_no' => $request->new_rsbsa
                    ]
                );
				
			DB::table($prv_code.".released")
				->where('rsbsa_control_no', '=', $request->old_rsbsa)
				->update(
					[
						'rsbsa_control_no' => $request->new_rsbsa
					]
				);
           
            DB::table($GLOBALS['season_prefix'].'rcep_dist_updates.tbl_rsbsa_updates')
            ->insert([
                'original_rsbsa' => $request->old_rsbsa,
                'update_description' => "updated RSBSA # to: `".$request->new_rsbsa."` [DS 2019 DATA]"."` & Middle Name: ".$request->middle_name.", First Name: ".$request->first_name.", Last Name: ".$request->last_name.", Ext Name: ".$request->ext_name.", Birth Date: ".$request->birth_date.", Sex: ".$request->sex,
                'performed_by' => Auth::user()->username
            ]);

            DB::commit();

        } catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();
            return "sql error";
        }
        
    }

    public function update_farmer_data_forEdit(Request $request){
        DB::beginTransaction();
        try {
            $prv_str = explode("-",$request->old_rsbsa);
            //$prv_code = $GLOBALS['season_prefix']."prv_".$prv_str[0].$prv_str[1];
            $prv_code = $GLOBALS['season_prefix']."prv_".Auth::user()->province;
            $pending_data  = DB::table($prv_code.".pending_release")->where('rsbsa_control_no', $request->old_rsbsa)->first();

            if(count($pending_data) > 0){
                if($pending_data->created_by == Auth::user()->username){
                    //update code... {area_history}
                    DB::table($prv_code.".area_history")
                        ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                        ->update(
                            [
                                'rsbsa_control_no' => $request->new_rsbsa, 
                            ]
                        );
    
                    //update code... {farmer_profile}
                    DB::table($prv_code.".farmer_profile")
                        ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                        ->update(
                            [
                                'rsbsa_control_no' => $request->new_rsbsa,
                                'firstName' => $request->first_name,
                                'midName' => $request->middle_name,
                                'lastName' => $request->last_name,
                                'extName' => $request->ext_name,
                                'sex' => $request->sex
                            ]
                        );
    
                    //update code... {other_info}
                    DB::table($prv_code.".other_info")
                        ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                        ->update(
                            [
                                'rsbsa_control_no' => $request->new_rsbsa,
                                'birthdate' => $request->birth_date
                            ]
                        );
    
                    //update code... {pending_release}
                    DB::table($prv_code.".pending_release")
                        ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                        ->update(
                            [
                                'rsbsa_control_no' => $request->new_rsbsa
                            ]
                        );
    
                    //update code... {performance}
                    DB::table($prv_code.".performance")
                        ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                        ->update(
                            [
                                'rsbsa_control_no' => $request->new_rsbsa
                            ]
                        );
					
					//update code... {released}
					DB::table($prv_code.".released")
                        ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                        ->update(
                            [
                                'rsbsa_control_no' => $request->new_rsbsa
                            ]
                        );
    
                    //insert saving of logs here...
                    DB::table($GLOBALS['season_prefix'].'rcep_dist_updates.tbl_rsbsa_updates')
                    ->insert([
                        'original_rsbsa' => $request->old_rsbsa,
                        'update_description' => "updated RSBSA # to: `".$request->new_rsbsa."` & Middle Name: ".$request->middle_name.", First Name: ".$request->first_name.", Last Name: ".$request->last_name.", Ext Name: ".$request->ext_name.", Birth Date: ".$request->birth_date.", Sex: ".$request->sex,
                        'performed_by' => Auth::user()->username
                    ]);
                
                }else{
                    return "user_no_permission";
                }
            
            }else{

                //update code... {area_history}
                DB::table($prv_code.".area_history")
                    ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                    ->update(
                        [
                            'rsbsa_control_no' => $request->new_rsbsa, 
                        ]
                    );

                //update code... {farmer_profile}
                DB::table($prv_code.".farmer_profile")
                    ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                    ->update(
                        [
                            'rsbsa_control_no' => $request->new_rsbsa,
                            'firstName' => $request->first_name,
                            'midName' => $request->middle_name,
                            'lastName' => $request->last_name,
                            'extName' => $request->ext_name,
                            'sex' => $request->sex 
                        ]
                    );

                //update code... {other_info}
                DB::table($prv_code.".other_info")
                    ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                    ->update(
                        [
                            'rsbsa_control_no' => $request->new_rsbsa,
                            'birthdate' => $request->birth_date
                        ]
                    );

                //update code... {pending_release}
                DB::table($prv_code.".pending_release")
                    ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                    ->update(
                        [
                            'rsbsa_control_no' => $request->new_rsbsa
                        ]
                    );

                //update code... {performance}
                DB::table($prv_code.".performance")
                    ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                    ->update(
                        [
                            'rsbsa_control_no' => $request->new_rsbsa
                        ]
                    );

                //update code... {released}
                DB::table($prv_code.".released")
                ->where('rsbsa_control_no', '=', $request->old_rsbsa)
                ->update(
                    [
                        'rsbsa_control_no' => $request->new_rsbsa
                    ]
                );

                //insert saving of logs here...
                DB::table($GLOBALS['season_prefix'].'rcep_dist_updates.tbl_rsbsa_updates')
                ->insert([
                    'original_rsbsa' => $request->old_rsbsa,
                    'update_description' => "updated RSBSA # to: `".$request->new_rsbsa."` [DS 2019 DATA]"."` & Middle Name: ".$request->middle_name.", First Name: ".$request->first_name.", Last Name: ".$request->last_name.", Ext Name: ".$request->ext_name.", Birth Date: ".$request->birth_date.", Sex: ".$request->sex,
                    'performed_by' => Auth::user()->username
                ]);

            }

            DB::commit();
            return "insert_success";

        } catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();
            return "sql error";
        }
    }

    public function check_rsbsa_if_exist(Request $request){
        $prv_str = explode("-",$request->old_rsbsa);
        //$prv_code = $GLOBALS['season_prefix']."prv_".$prv_str[0].$prv_str[1];
        $prv_code = $GLOBALS['season_prefix']."prv_".Auth::user()->province;
        $return_str = "";
        $farmer_name = "";

        //step 1 - check if there is pending_release data...
        $pending_data = DB::table($prv_code.".pending_release")->where('rsbsa_control_no', $request->new_rsbsa)->first();
        if(count($pending_data) > 0){
            $return_str = "pending_data_exists";

            // if-pending data exists check if rsbsa si already used...
            $check_rsbsa = DB::table($prv_code.".farmer_profile")
                ->where('rsbsa_control_no', $request->new_rsbsa)
                ->first();

            if(count($check_rsbsa) > 0){
                if($check_rsbsa->rsbsa_control_no == $request->old_rsbsa){
                    $return_str = "new_data_same_rsbsa";
                }else{
                    $return_str = "new_data_rsbsa_exists";
                    $farmer_name = $check_rsbsa->midName == '' ? $check_rsbsa->firstName." ".$check_rsbsa->lastName." ".$check_rsbsa->extName : $check_rsbsa->firstName." ".$check_rsbsa->midName." ".$check_rsbsa->lastName." ".$check_rsbsa->extName;
                }
                            
            }else{
                $return_str = "new_data_new_rsbsa";
            }
        

        }else{
            $farmer_profile = DB::table($prv_code.".farmer_profile")->where('rsbsa_control_no', $request->new_rsbsa)->first();
            if(count($farmer_profile) > 0){
                $return_str = "no_pending_data_old_data";
                $farmer_name = $farmer_profile->midName == '' ? $farmer_profile->firstName." ".$farmer_profile->lastName." ".$farmer_profile->extName : $farmer_profile->firstName." ".$farmer_profile->midName." ".$farmer_profile->lastName." ".$farmer_profile->extName;
            }else{
                $return_str = "new_data_new_rsbsa";
            }
        }

        return array(
            'return_msg' => $return_str,
            'farmer_name' => $farmer_name
        );
    }

}

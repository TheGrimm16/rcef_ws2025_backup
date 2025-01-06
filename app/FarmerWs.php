<?php

namespace App;

use DB;
use Auth;

class FarmerWs {

    function names($term) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $names = DB::table($database . '.farmer_profile')
                ->select('*')
                ->where('firstName', 'LIKE', $term . '%')
                ->orWhere('lastName', 'LIKE', $term . '%')
                ->orWhere('fullName', 'LIKE', $term . '%')
                ->orderBy('fullName', 'ASC')
                ->take(25)
                ->get();

        return $names;
    }

    function _get_geonames($prv) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $names = DB::connection('delivery_inspection_db')
                ->table('lib_prv')
                ->select('*')
                ->where('prv', $prv)
                ->first();

        return $names;
    }

    function _insert_dropoff($data) {
        // Pending release table based on province posted by app
        /* $table = 'prv' . $prv . '_released'; */

        // $province = Auth::user()->province;

        DB::beginTransaction();
        try {
            /* DB::connection('delivery_inspection_db')
              ->table($table)
              ->insert($data); */
            DB::connection('delivery_inspection_db')
                    ->table('lib_dropoff_point')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    function _check_dropoff($prv, $dropoffpoint, $flag) {
        if ($flag == 1) {
            $data = DB::connection('delivery_inspection_db')
                    ->table('lib_dropoff_point')
                    ->select('*')
                    ->where('prv', 'LIKE', $prv)
                    ->where('dropOffPoint', 'LIKE', $dropoffpoint)
                    ->count();
        } else {
            $data = DB::connection('delivery_inspection_db')
                    ->table('lib_dropoff_point')
                    ->select('*')
                    ->where('prv', 'LIKE', $prv)
                    ->where('dropOffPoint', 'LIKE', $dropoffpoint)
                    ->first();
        }
        return $data;
    }

    function _check_other_dropoff($prv, $dropoffpoint) {
	
        $ctr = DB::connection('delivery_inspection_db')
                ->table('lib_dropoff_point')
                ->select('*')
                ->where('prv', 'LIKE', $prv)
                ->where('dropOffPoint', '!=', $dropoffpoint)
                ->count();

        return $ctr;
    }

    function _check_dropoff_coop($prv, $dropoffpoint, $coop) {
        $ctr = DB::connection('delivery_inspection_db')
                ->table('lib_dropoff_point')
                ->select('*')
                ->where('prv', 'LIKE', $prv)
                ->where('dropOffPoint', 'LIKE', $dropoffpoint)
                ->where('coop_accreditation', $coop)
                ->count();

        return $ctr;
    }

    function _other_info($rsbsa, $farmerID) {

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $data = DB::table($database . '.other_info')
                ->select('*')
                ->where('rsbsa_control_no', 'LIKE', $rsbsa)
                ->where('farmer_id', $farmerID)
                ->orderBy('info_id', 'DESC')
                ->take(1)
                ->get();

        $ctr = DB::table($database . '.other_info')
                ->select('*')
                ->where('rsbsa_control_no', 'LIKE', $rsbsa)
                ->where('farmer_id', $farmerID)
                ->count();
        if ($ctr > 0) {
            foreach ($data as $result):
                $data = array(
                    'mother_fname' => $result->mother_fname,
                    'mother_lname' => $result->mother_lname,
                    'mother_mname' => $result->mother_mname,
                    'mother_suffix' => $result->mother_suffix,
                    'birthdate' => $result->birthdate,
                    'phone' => $result->phone
                );
            endforeach;
        } else {
            $data = false;
        }
        return $data;
    }

    function _search_rsbsa_no($rsbsa, $flag) {

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $replace_rsbsa = str_ireplace("-", "", $rsbsa);
        if ($flag == 1) {
            $data = DB::table($database . '.farmer_profile')
                    ->where('rsbsa_control_no', 'LIKE', $replace_rsbsa)
                    ->orWhere('rsbsa_control_no', 'LIKE', $rsbsa)
                    ->orderBy('id', 'DESC')
                    ->count();
        } else {
            $data = DB::table($database . '.farmer_profile')
                    ->Select('*')
                    ->where('rsbsa_control_no', 'LIKE', $replace_rsbsa)
                    ->orWhere('rsbsa_control_no', 'LIKE', $rsbsa)
                    ->orderBy('id', 'DESC')
                    ->take(1)
                    ->get();
        }
        return $data;
    }

    function farmer_area($farmer_id) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_area_history'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $area = DB::table($database . '.area_history')
                ->select('*')
                ->where('farmerID', $farmer_id)
                ->where('dateCreated', '!=', date('Y-m-d'))
                ->orderBy('dateCreated', 'desc')
                ->first();

        return $area;
    }

    // REPLACED BY FARM PERFORMANCE
    /* function insert_farm_address($address) {
      DB::beginTransaction();
      try {
      DB::connection('rcep_farmers_db')
      ->table('tbl_area_history')
      ->insert($address);
      DB::commit();
      return "success";
      } catch (\Exception $e) {
      DB::rollback();
      return $e->getMessage();
      }
      } */

    function insert_farm_performance($farm_performance) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            DB::table($database . '.performance')
                    ->insert($farm_performance);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        }
    }

    function insert_distribution_id($farmer_id, $distribution_id) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            DB::table($database . '.farmer_profile')
                    ->where('farmerID', $farmer_id)
                    ->update([
                        'distributionID' => $distribution_id,
                        'update' => 1
            ]);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        }
    }

    function farmer_profile($prv, $distribution_id) {
        // Farmer profile table based on province posted by app
        // $table = 'prv' . $prv . '_farmer_profile';
        $database = $GLOBALS['season_prefix'].'prv_' . $prv;

        $farmer = DB::table($database . '.farmer_profile')
                ->select('*')
                ->where('distributionID', $distribution_id)
                ->orderBy('id', "DESC")
                ->first();

        return $farmer;
    }

    function farmer_rsbsa($farmer_id) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $farmer = DB::table($database . '.farmer_profile')
                ->select('*')
                ->where('farmerID', $farmer_id)
                ->first();

        return $farmer;
    }

    function update_farmer_rsbsa($farmer_id, $rsbsa_control_no) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            DB::table($database . '.farmer_profile')
                    ->where('farmerID', $farmer_id)
                    ->update([
                        'rsbsa_control_no' => $rsbsa_control_no,
                        'update' => 1
            ]);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    function add_performance($rsbsa_control_no, $farmerID, $variety_used, $seed_usage, $preferred_variety, $area_planted,$yields) {

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            $id = DB::table($database . '.performance')
                    ->insertGetId([
                'farmerID' => $farmerID,
                'rsbsa_control_no' => $rsbsa_control_no,
                'variety_used' => $variety_used,
                'seed_usage' => $seed_usage,
                'yield' => $yields,
                'preferred_variety' => $preferred_variety,
                'area_planted' => $area_planted,
                'send' => 0
            ]);

            DB::commit();
            $data = array(
                'result' => 'success',
                'farmerID' => $farmerID
            );
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            return $data = array('result' => 'failed');
        }
    }

    function add_other_info($phone, $rsbsa, $farmerId, $birthdate, $mfname, $mmname, $mlname, $mextname, $valid_id, $relationship, $is_representative, $have_pic) {

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            $id = DB::table($database . '.other_info')
                    ->insertGetId([
                'farmer_id' => $farmerId,
                'rsbsa_control_no' => $rsbsa,
                'mother_fname' => $mfname,
                'mother_mname' => $mmname,
                'mother_lname' => $mlname,
                'mother_suffix' => $mextname,
                'phone' => $phone,
                'birthdate' => $birthdate,
                'send' => 0,
                'is_representative' => $is_representative,
                'id_type' => $valid_id,
                'relationship' => $relationship,
                'have_pic' => $have_pic
            ]);

            DB::commit();
            $data = array(
                'result' => 'success',
                'farmerID' => $farmerId
            );
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            return $data = array('result' => 'failed');
        }
    }

    function add_farmer_rsbsa($rsbsa_control_no, $area, $distributionID, $sex, $lastName, $firstName, $midName, $extName, $actual_area) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table1 = 'prv' . $province . '_farmer_profile';
          $table2 = 'prv' . $province . '_area_history'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            // Get last farmerID in table

            $replace_rsbsa = str_ireplace("-", "", $rsbsa_control_no);
            $delete = DB::table($database . '.' . 'farmer_profile')
                    ->where('rsbsa_control_no', $rsbsa_control_no)
                    ->orWhere('rsbsa_control_no', $replace_rsbsa)
                    ->delete();
            $delete = DB::table($database . '.' . 'other_info')
                    ->where('rsbsa_control_no', $rsbsa_control_no)
                    ->orWhere('rsbsa_control_no', $replace_rsbsa)
                    ->delete();

            $farmer = DB::table($database . '.farmer_profile')
                    ->select('farmerID')
                    ->count();
            if ($farmer > 0) {
                $lastID = DB::table($database . '.farmer_profile')
                        ->select('farmerID')
                        ->orderBy('id', 'DESC')
                        ->first();
                $f_id = $lastID->farmerID;
            } else {
                $lastID = '63' . $province . '000000001';
                $f_id = $lastID;
            }
            // Insert rsbsa control number in farmer profile table
            $id = DB::table($database . '.farmer_profile')
                    ->insertGetId([
                'distributionID' => $distributionID,
                'lastName' => $lastName,
                'firstName' => $firstName,
                'midName' => $midName,
                'extName' => $extName,
                'sex' => $sex,
                'area' => $area,
                'actual_area' => $actual_area,
                'rsbsa_control_no' => $rsbsa_control_no,
                'isNew' => 1,
                'send' => 0
            ]);

            // $farmerID = (int)$lastID->farmerID + 1;

            $prefix = substr($f_id, 0, 9);
            $farmerID = $prefix . '' . str_pad($id, 6, 0, STR_PAD_LEFT);

            // Update farmer profile insert farmerID
            DB::table($database . '.farmer_profile')
                    ->where('id', $id)
                    ->update(['farmerID' => $farmerID]);

            // Insert farmer area in area history table
            DB::table($database . '.area_history')
                    ->insert([
                        'farmerId' => $farmerID,
                        'area' => $area,
                        'rsbsa_control_no' => $rsbsa_control_no,
						'send' => 0
            ]);

            DB::commit();
            $data = array(
                'result' => 'success',
                'farmerID' => $farmerID
            );
            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            return $data = array('result' => 'failed');
        }
    }

    function rsbsa_codes($term) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $rsbsa_control_no = DB::table($database . '.farmer_profile')
                ->select('*')
                ->where('rsbsa_control_no', 'LIKE', $term . '%')
                ->where('distributionID', '=', '')
                ->where('lastName', '=', '')
                ->take(5)
                ->get();

        return $rsbsa_control_no;
    }

    function update_farmer($farmerID, $profile, $area, $performance) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table1 = 'prv' . $province . '_farmer_profile';
          $table2 = 'prv' . $province . '_area_history';
          $table3 = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            DB::table($database . '.farmer_profile')
                    ->where('farmerID', $farmerID)
                    ->update($profile);

            DB::table($database . '.area_history')
                    ->where('farmerID', $farmerID)
                    ->update($area);

            DB::table($database . '.performance')
                    ->insert($performance);

            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    function _get_lgu_max($dropoff_id) {
        $ctr = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_dropoff_settings')
                ->select('lgu_limit')
                ->where('dropoffID', $dropoff_id)
                ->orderBy('id', "desc")
                ->count();
        if ($ctr > 0) {
            $data = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_dropoff_settings')
                    ->select('lgu_limit')
                    ->where('dropoffID', $dropoff_id)
                    ->orderBy('id', "desc")
                    ->first();
            return $data->lgu_limit;
        } else {
            return 0;
        }
    }

    function _get_max_pmo($code) {
        $data = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_settings')
                ->select('setting_value')
                ->where('setting_code', $code)
                ->first();

        return $data->setting_value;
    }

    function farmer_area_address($farmerID) {
        // Assigned province to logged in user
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $address = DB::table($database . '.area_history')
                ->select('region', 'province', 'municipality', 'barangay')
                ->where('farmerID', $farmerID)
                ->orderBy('areaHistoryId', 'desc')
                ->first();

        return $address;
    }

    function update_area($data) {
        // Assigned province to logged in user
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            DB::table($database . '.area_history')
                    ->insert($data);

            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    function check_qr($distributionID, $rsbsa_control_no) {
        // Assigned province to logged in user
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $rsbsa = DB::table($database . '.farmer_profile')
                ->where('distributionID', $distributionID)
                ->where('rsbsa_control_no', "!=", $rsbsa_control_no)
                ->count();

        return $rsbsa;
    }

    function get_farmer_rsbsa($rsbsa_control_no) {
        // Assigned province to logged in user
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $replace_rsbsa = str_ireplace("-", "", $rsbsa_control_no);
        $pending = DB::table($database . '.pending_release')
                ->select(DB::RAW('SUM(bags) as bags'))
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->orWhere('rsbsa_control_no', $replace_rsbsa)
                ->first();

        return ($pending->bags == '' ? 0 : $pending->bags);
    }

}

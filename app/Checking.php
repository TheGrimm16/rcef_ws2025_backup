<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class Checking extends Model {

    function _drop_offpoints() {
        $province = Auth::user()->province;

        $search = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                ->select('*')
                ->where('prv_dropoff_id', 'like', $province . '%')
                ->orderBy('dropOffPoint', 'asc')
                ->get();

        return $search;
    }

    function _get_farmer_profile($search) {
        $province = Auth::user()->province;

        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $search = DB::table($GLOBALS['season_prefix'].$database . '.farmer_profile')
                ->select('*')
                ->where('rsbsa_control_no', 'like', '%' . $search . '%')
                ->orWhere('firstName', 'like', $search . '%')
                ->orWhere('lastName', 'like', $search . '%')
                ->orWhere(DB::raw("CONCAT(firstName, ' ', lastName)"), 'like', '%' . $search . '%')
                ->orWhere('midName', 'like', $search . '%')
                ->orWhere('distributionId', 'like', '%' . $search . '%')
                ->orderBy('id', 'desc')
                ->take(100)
                ->get();

        return $search;
    }

    function _get_farmer_details($farmerID, $rsbsa) {
        $province = Auth::user()->province;

        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $search = DB::table($GLOBALS['season_prefix'].$database . '.farmer_profile')
                ->select('*')
                ->where('rsbsa_control_no', 'like', $rsbsa)
                ->where('farmerID', $farmerID)
                ->orderBy('id', 'desc')
                ->first();

        return $search;
    }

    function _get_pendingreleases($drop_id) {
        $province = Auth::user()->province;

        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $search = DB::table($GLOBALS['season_prefix'].$database . '.pending_release')
                ->select('*')
                ->where('is_released', 0)
                ->where('prv_dropoff_id', $drop_id)
                ->orderBy('pending_id', "desc")
                ->take(100)
                ->get();

        return $search;
    }

    function _delete_farmer_data($rsbsa_control_no) {
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $delete = DB::table($GLOBALS['season_prefix'].$database . '.' . 'farmer_profile')
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->delete();
        $delete = DB::table($GLOBALS['season_prefix'].$database . '.' . 'area_history')
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->delete();
        $delete = DB::table($GLOBALS['season_prefix'].$database . '.' . 'pending_release')
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->delete();
        
        $delete = DB::table($GLOBALS['season_prefix'].$database . '.' . 'released')
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->delete();
        return json_encode("success");
    }

    function _delete_farmer($farmer_id, $qr_code, $rsbsa_control_no) {
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $delete = DB::table($GLOBALS['season_prefix'].$database . '.' . 'farmer_profile')
                ->where('farmerID', $farmer_id)
                ->where('distributionID', $qr_code)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->delete();
        $delete = DB::table($GLOBALS['season_prefix'].$database . '.' . 'area_history')
                ->where('farmerId', $farmer_id)
                ->where('rsbsa_control_no', $rsbsa_control_no)
                ->delete();
    }

    function _get_release_data($farmer_id, $rsbsa, $table, $flag) {
        $province = Auth::user()->province;

        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $ctr = DB::table($GLOBALS['season_prefix'].$database . '.' . $table)
                ->where('farmer_id', $farmer_id)
                ->where('rsbsa_control_no', $rsbsa)
                ->count();
        if ($ctr > 0) {
            $data = DB::table($GLOBALS['season_prefix'].$database . '.' . $table)
                    ->where('farmer_id', $farmer_id)
                    ->where('rsbsa_control_no', $rsbsa)
                    ->orderBy('farmer_id', "desc")
                    ->first();
        } else {
            $data = "";
        }
        return ($flag == 1 ? $ctr : $data);
    }

    function _highlight($text, $words) {
        return str_ireplace($text, '<strong style="background:yellow;">' . $text . '</strong>', $words);
    }

}

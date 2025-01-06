<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class Transfer extends Model {

    function _drop_offpoints() {
        $province = Auth::user()->province;

        $search = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                ->select('*')
                ->orderBy('municipality', 'asc')
                ->get();

        return $search;
    }

    function _add_details_transfer($data) {
	
        DB::beginTransaction();
        try {
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.tbl_actual_delivery')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        }
    }
	
    function _province_name() {
        $province = Auth::user()->province;

        $data = DB::table($GLOBALS['season_prefix']."sdms_db_dev" . '.lib_provinces')
                ->select('provDesc')
                ->where('provCode', $province)
                ->first();

        return $data;
    }
    function _add_details($data) {

        DB::beginTransaction();
        try {
            DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        }
    }

    function _get_prv_details() {
        $province = Auth::user()->province;

        $search = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_prv')
                ->select('*')
                ->where('prv', 'LIKE', $province . '%')
                ->first();

        return $search;
    }

    function _check_dropoff($id) {

        $data = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                ->select('*')
                ->where('prv_dropoff_id', $id)
                ->count();

        return $data;
    }

    function _transfer() {
        $province = Auth::user()->province;

        $search = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                ->select('*')
                ->orderBy('municipality', 'asc')
                ->get();

        return $search;
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;

class Connect extends Model {

    function _drop_offpoints($province) {

        $query = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                ->select('*')
                ->orderBy('province', 'asc')
                ->where('prv', 'LIKE', $province . '%')
                ->get();

        return $query;
    }
    function _drop_offpoints_admin($province) {

        $query = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection" . '.lib_dropoff_point')
                ->select('*')
                ->orderBy('province', 'asc')
                ->get();

        return $query;
    }
    function _cooperatives() {

        $query = DB::table($GLOBALS['season_prefix']."rcep_seed_cooperatives" . '.tbl_cooperatives')
                ->select('*')
                ->orderBy('coopName', 'asc')
                ->get();

        return $query;
    }

}

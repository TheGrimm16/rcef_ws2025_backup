<?php

namespace App;

use DB;

class ProvincesWs {

    function all_provinces() {
        $provinces = DB::table('lib_provinces')
                ->select('*')
                ->orderBy('provDesc', 'asc')
                ->get();

        return $provinces;
    }

    function delivery_provinces() {
        $provinces = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select('province', 'prv', 'prv_dropoff_id')
                ->orderBy('province', 'asc')
                ->groupBy("province")
                ->get();

        return $provinces;
    }
	
	function delivery_provinces_new() {
        $provinces = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('province', 'prv', 'prv_dropoff_id')
                ->orderBy('province', 'asc')
                ->groupBy("province")
                ->get();

        return $provinces;
    }

    function assigned_region($province) {
        $region = DB::table('lib_provinces')
                ->select('regCode')
                ->where('provCode', $province)
                ->first();

        return $region;
    }

    function provinces_assigned($region) {
        $provinces = DB::table('lib_provinces')
                ->select('provDesc', 'provCode')
                ->where('regCode', $region)
                ->get();

        return $provinces;
    }

}

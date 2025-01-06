<?php

namespace App;

use DB;

class Municipalities
{
    function search_municipalities($province) {
        $municipalities = DB::table('lib_municipalities as mun')
        ->leftJoin('lib_provinces as prov', 'prov.provCode', '=', 'mun.provCode')
        ->select('mun.*')
        ->where('prov.provDesc', $province)
        ->orderBy('mun.citymunDesc', 'asc')
        ->get();

        return $municipalities;
    }

    function delivery_municipalities($province) {
        $municipalities = DB::connection('delivery_inspection_db')
        ->table('tbl_actual_delivery')
        ->select('municipality','prv','prv_dropoff_id')
        ->where('province', $province)
        ->where('batchTicketNumber', "TRANSFER")
        ->orderBy('municipality', 'asc')
        ->groupBy("municipality")
        ->get();

        return $municipalities;
    }
	
	function delivery_municipalities_new($province) {
        $municipalities = DB::connection('delivery_inspection_db')
        ->table('tbl_delivery')
        ->select('municipality','prv','prv_dropoff_id')
        ->where('province', $province)
        ->where("isBuffer", "!=", 9)
        ->orderBy('municipality', 'asc')
        ->groupBy("municipality")
        ->get();

        $return_str= '';
        foreach($municipalities as $mun){
            $return_str .= "<option value='$mun->municipality'>$mun->municipality</option>";
        }
        return $return_str;

        //return $municipalities;
    }
}

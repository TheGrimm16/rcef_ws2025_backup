<?php

namespace App;

use DB;

class DropoffPointsWs
{
    function delivery_dropoff_points_new($province, $municipality) {
        $dropoff_points = DB::connection('delivery_inspection_db')
        ->table('tbl_delivery')
        ->select('dropOffPoint','prv_dropoff_id','prv')
        ->where('province', $province)
        ->where('municipality', $municipality)
        ->orderBy('dropOffPoint', 'asc')
        ->groupBy("prv_dropoff_id")
        ->get();
		
		$return_str= '';
        foreach($dropoff_points as $row){
            $return_str .= "<option value='$row->prv_dropoff_id'>$row->dropOffPoint</option>";
        }
        return $return_str;

        //return $dropoff_points;
    }
	function delivery_dropoff_points($province, $municipality) {
        $dropoff_points = DB::connection('delivery_inspection_db')
        ->table('tbl_actual_delivery')
        ->select('dropOffPoint','prv_dropoff_id','prv')
        ->where('province', $province)
        ->where('municipality', $municipality)
        ->orderBy('dropOffPoint', 'asc')
        ->groupBy("prv_dropoff_id")
        ->get();

        return $dropoff_points;
    }
}

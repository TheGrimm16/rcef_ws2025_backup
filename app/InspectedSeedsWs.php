<?php

namespace App;

use DB;
use Auth;

class InspectedSeedsWs {

    function available_seeds($distribution_province, $distribution_municipality, $dropoff_point) {
        // Assigned province to logged in user
        $province = substr(Auth::user()->province, 2);
        $table = 'prv' . $province . '_pending_release';

        $seeds = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select('seedVariety', DB::RAW('SUM(totalBagCount) as totalBagCount'))
                ->where('province', $distribution_province)
                ->where('municipality', $distribution_municipality)
                ->where('prv_dropoff_id', $dropoff_point)
                ->groupBy('seedVariety')
                ->get();

        return $seeds;
    }

    function variety($distribution_province, $distribution_municipality, $dropoff_point, $variety) {
        $varieties = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select('seedVariety', DB::RAW('SUM(totalBagCount) as totalBagCount'))
                ->where('province', $distribution_province)
                ->where('municipality', $distribution_municipality)
                ->where('prv_dropoff_id', $dropoff_point)
                ->where('seedVariety', $variety)
                ->first();

        // dd($varieties);

        return $varieties;
    }

    function pending_variety($distribution_province, $distribution_municipality, $dropoff_point, $variety) {
        // Assigned province to logged in user
        // $province = substr(Auth::user()->province, 2);
        // $database = 'prv' . $province . '_pending_release';

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $varieties = DB::table($database . '.pending_release')
                ->select(DB::RAW('SUM(bags) as bags'))
                ->where('seed_variety', $variety)
                ->where('province', $distribution_province)
                ->where('municipality', $distribution_municipality)
                ->where('send', 0)
                ->where('prv_dropoff_id', $dropoff_point)
                // ->where('batch_ticket_no', $batch_ticket_no)
                ->first();

        return $varieties;
    }

    /* function variety($variety) {
      // Assigned province to logged in user
      $province = substr(Auth::user()->province, 2);
      $table = 'prv' . $province . '_pending_release';

      $varieties = DB::connection('delivery_inspection_db')
      ->table('tbl_inspection as inspection')
      ->leftJoin('tbl_delivery as delivery', 'delivery.batchTicketNumber', '=', 'inspection.batchTicketNumber')
      ->leftJoin('tbl_delivery_status as delivery_status', 'delivery_status.batchTicketNumber', '=', 'inspection.batchTicketNumber')
      ->leftJoin($table . ' as pending', 'pending.batch_ticket_no', '=', 'inspection.batchTicketNumber')
      ->select('inspection.seedVariety', 'inspection.batchTicketNumber', 'inspection.totalBagsDelivered', DB::RAW('SUM(pending.bags) as bags'))
      ->where('inspection.seedVariety', $variety)
      ->where('pending.seed_variety', $variety)
      ->where('delivery_status.status', 1)
      ->groupBy('inspection.seedVariety')
      ->get();

      return $varieties;
      } */

    function confirm_variety($distribution_province, $distribution_municipality, $dropoff_point, $variety) {
        $variety = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select('seedVariety', DB::RAW('SUM(totalBagCount) as totalBagCount'))
                ->where('province', $distribution_province)
                ->where('municipality', $distribution_municipality)
                ->where('prv_dropoff_id', $dropoff_point)
                ->where('seedVariety', $variety)
                ->first();

        return $variety;
    }

    // Get allocated variety for farmer
    // function variety_allocated($batch_ticket_no) {
    //     $variety = DB::connection('delivery_inspection_db')
    //     ->table('tbl_delivery')
    //     ->
    // }
}

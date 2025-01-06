<?php

namespace App;

use DB;
use Auth;

class ReleasingWs {

    function released($farmer_id) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_pending_release';

          $released = DB::connection('delivery_inspection_db')
          ->table($table)
          ->select(DB::RAW('SUM(bags) as bags'))
          ->where('farmer_id', $farmer_id)
          ->first(); */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $released = DB::table($database . '.pending_release')
                ->select(DB::RAW('SUM(bags) as bags'))
                ->where('farmer_id', $farmer_id)
                ->first();

        return $released;
    }

    function check_direct_seeded() {
        $province = Auth::user()->province;

        $ctr = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_provinces")
                ->where('provCode', $province)
                ->where('is_direct_seeded', 1)
                ->count();

        return $ctr;
    }

    function add_released($data) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_pending_release'; */
	
        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
	
        DB::beginTransaction();
        try {
            /* DB::connection('delivery_inspection_db')
              ->table($table)
              ->insert($data);
              DB::commit(); */
            DB::table($database . '.released')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        }
    }
    function add_pending($data) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_pending_release'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            /* DB::connection('delivery_inspection_db')
              ->table($table)
              ->insert($data);
              DB::commit(); */
            DB::table($database . '.pending_release')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return "failed";
        }
    }

    function allocated_seeds($prv, $rsbsa, $farmer_id) {
        // Pending release table based on province posted by app
        /* $table = 'prv' . $prv . '_pending_release'; */

        // $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $prv;

        $seeds = DB::table($database . '.pending_release')
                ->select('*')
                ->where('farmer_id', $farmer_id)
                ->where('rsbsa_control_no', $rsbsa)
                ->get();

        return $seeds;
    }

    function release($prv, $data) {
        // Pending release table based on province posted by app
        /* $table = 'prv' . $prv . '_pending_release'; */

        // $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $prv;

        DB::beginTransaction();
        try {
            /* DB::connection('delivery_inspection_db')
              ->table($table)
              ->where('farmer_id', $data['farmer_id'])
              ->update([
              'is_released' => 1
              ]); */
            DB::table($database . '.pending_release')
                    ->where('farmer_id', $data['farmer_id'])
                    ->where('rsbsa_control_no', $data['rsbsa_control_no'])
                    ->update([
                        'is_released' => 1
            ]);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    function insert_released($prv, $data) {
        // Pending release table based on province posted by app
        /* $table = 'prv' . $prv . '_released'; */

        // $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $prv;

        DB::beginTransaction();
        try {
            /* DB::connection('delivery_inspection_db')
              ->table($table)
              ->insert($data); */
            DB::table($database . '.released')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    function pending_releases() {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_pending_release'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        /* $pending_releases = DB::connection('delivery_inspection_db')
          ->table($table)
          ->select('*')
          ->where('send', 1)
          ->get(); */

        $pending_releases = DB::table($database . '.pending_release')
                ->select('*')
                ->where('send', 1)
                ->get();

        return $pending_releases;
    }

    function insert_pending_to_server($data, $pending_id) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_pending_release'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $table = $GLOBALS['season_prefix'].'prv_' . $province . '.pending_release';

        if ($this->isConnected()) {
            DB::beginTransaction();
            try {
                // Insert to server
                DB::connection('central_delivery_inspection_db')
                        ->table($table)
                        ->insert($data);

                // Update local server
                DB::table($database . '.pending_release')
                        ->where('pending_id', $pending_id)
                        ->update(['send' => 0]);

                DB::commit();
                return "success";
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        } else {
            return "no connection";
        }
    }

    function releases() {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_released'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $releases = DB::table($database . '.released')
                ->select('*')
                ->where('send', 1)
                ->get();

        return $releases;
    }

    function insert_released_to_server($data, $release_id) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_released'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $table = $GLOBALS['season_prefix'].'prv_' . $province . '.released';

        if ($this->isConnected()) {
            DB::beginTransaction();
            try {
                // Insert to server
                DB::connection('central_delivery_inspection_db')
                        ->table($table)
                        ->insert($data);

                // Update local server
                DB::table($database . '.released')
                        ->where('release_id', $release_id)
                        ->update(['send' => 0]);

                DB::commit();
                return "success";
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        } else {
            return "no connection";
        }
    }

    // Check dbmp2 database connection
    // Change to rcep database connection later
    function isConnected() {
        $connected = @fsockopen("dbmp2.philrice.gov.ph", 80);

        if ($connected) {
            $connectedDB = DB::connection('central_delivery_inspection_db')->getPdo();
            if ($connectedDB) {
                $isConnected = true;
            } else {
                $isConnected = false;
            }
        } else {
            $isConnected = false;
        }

        return $isConnected;
    }

    function farmer_profile_list() {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $profile_list = DB::table($database . '.farmer_profile')
                ->select('*')
                ->where('send', 1)
                ->get();

        return $profile_list;
    }

    function farmer_area_list($farmerId) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_area_history'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $area_list = DB::table($database . '.area_history')
                ->select('*')
                ->where('farmerId', $farmerId)
                ->get();

        return $area_list;
    }

    function farmer_performance_list($farmerID) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $performance = DB::table($database . '.performance')
                ->select('*')
                ->where('farmerID', $farmerID)
                ->get();

        return $performance;
    }

    function insert_farmer_profile_to_server($farmerID, $farmer_profile, $farmer_area, $farmer_performance) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table1 = 'prv' . $province . '_farmer_profile';
          $table2 = 'prv' . $province . '_area_history';
          $table3 = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $table1 = $GLOBALS['season_prefix'].'prv_' . $province . '.farmer_profile';
        $table2 = $GLOBALS['season_prefix'].'prv_' . $province . '.area_history';
        $table3 = $GLOBALS['season_prefix'].'prv_' . $province . '.performance';

        if ($this->isConnected()) {
            DB::beginTransaction();
            try {
                // Insert farmer profile to server
                DB::connection('central_distribution_db')
                        ->table($table1)
                        ->insert($farmer_profile);

                // Insert farmer area to server
                DB::connection('central_distribution_db')
                        ->table($table2)
                        ->insert($farmer_area);

                // Insert farmer performance to server
                DB::connection('central_distribution_db')
                        ->table($table3)
                        ->insert($farmer_performance);

                // Update local server
                DB::table($database . '.farmer_profile')
                        ->where('farmerID', $farmerID)
                        ->update(['send' => 0]);

                DB::commit();
                return "success";
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        } else {
            return "no connection";
        }
    }

    function farmer_profile_list_update() {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        $profile_list = DB::table($database . '.farmer_profile')
                ->select('*')
                ->where('update', 1)
                ->get();

        return $profile_list;
    }

    function update_farmer_profile_to_server($farmerID, $farmer_profile) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;
        $table = $GLOBALS['season_prefix'].'prv_' . $province . '.farmer_profile';

        if ($this->isConnected()) {
            DB::beginTransaction();
            try {
                // Update farmer profile to server
                DB::connection('central_distribution_db')
                        ->table($table)
                        ->where('farmerID', $farmerID)
                        ->update($farmer_profile);

                // Update local server
                DB::table($database . '.farmer_profile')
                        ->where('farmerID', $farmerID)
                        ->update(['update' => 0]);

                DB::commit();
                return "success";
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        } else {
            return "no connection";
        }
    }

    function farmer_profile_list_server() {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_farmer_profile'; */

        $province = Auth::user()->province;
        $table = $GLOBALS['season_prefix'].'prv_' . $province . '.farmer_profile';

        $profile_list = DB::connection('central_distribution_db')
                ->table($table)
                ->select('*')
                ->get();

        return $profile_list;
    }

    function farmer_area_list_server($farmerId) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_area_history'; */

        $province = Auth::user()->province;
        $table = $GLOBALS['season_prefix'].'prv_' . $province . '.area_history';

        $area_list = DB::connection('central_distribution_db')
                ->table($table)
                ->select('*')
                ->where('farmerId', $farmerId)
                ->get();

        return $area_list;
    }

    function farmer_performance_list_server($farmerID) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $table = $GLOBALS['season_prefix'].'prv_' . $province . '.performance';

        $performance = DB::connection('central_distribution_db')
                ->table($table)
                ->select('*')
                ->where('farmerID', $farmerID)
                ->get();

        return $performance;
    }

    // function farmer_profile_list_check() {
    //     // Assigned province to logged in user
    //     $province = substr(Auth::user()->province, 2);
    //     $table = 'prv' . $province . '_farmer_profile';
    //
    //     $profile_count = DB::connection('distribution_db')
    //     ->table($table)
    //     ->count();
    //
    //     return $profile_count;
    // }

    function truncate_distribution_tables() {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table1 = 'prv' . $province . '_farmer_profile';
          $table2 = 'prv' . $province . '_area_history';
          $table3 = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        DB::beginTransaction();
        try {
            // Truncate farmer profile, area history, and performance tables
            DB::table($database . '.farmer_profile')
                    ->truncate();

            DB::table($database . '.area_history')
                    ->truncate();

            DB::table($database . '.performance')
                    ->truncate();

            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage();
        }
    }

    function insert_farmer_profile_to_local($farmerID, $farmer_profile, $farmer_area, $farmer_performance) {
        // Assigned province to logged in user
        /* $province = substr(Auth::user()->province, 2);
          $table1 = 'prv' . $province . '_farmer_profile';
          $table2 = 'prv' . $province . '_area_history';
          $table3 = 'prv' . $province . '_performance'; */

        $province = Auth::user()->province;
        $database = $GLOBALS['season_prefix'].'prv_' . $province;

        if ($this->isConnected()) {
            DB::beginTransaction();
            try {
                // Insert farmer profile to local
                DB::table($database . '.farmer_profile')
                        ->insert($farmer_profile);

                // Insert farmer area to local
                DB::table($database . '.area_history')
                        ->insert($farmer_area);

                // Insert farmer performance to local
                DB::table($database . '.performance')
                        ->insert($farmer_performance);

                DB::commit();
                return "success";
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        } else {
            return "no connection";
        }
    }

    function actual_delivery_data() {
        $delivery_data = DB::connection('delivery_inspection_db')
                ->table('tbl_actual_delivery')
                ->select('*')
                ->where('send', 1)
                ->get();

        return $delivery_data;
    }

    function send_delivery_data_to_server($actualDeliveryId, $data) {
        if ($this->isConnected()) {
            DB::beginTransaction();
            try {
                // Insert delivery data to server
                DB::connection('central_distribution_db')
                        ->table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                        ->insert($data);

                // Update local server
                DB::connection('delivery_inspection_db')
                        ->table('tbl_actual_delivery')
                        ->where('actualDeliveryId', $actualDeliveryId)
                        ->update(['send' => 0]);

                DB::commit();
                return "success";
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        } else {
            return "no connection";
        }
    }

}

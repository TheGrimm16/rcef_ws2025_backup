<?php

namespace App;

use DB;

class DropoffPoints
{
    function delivery_dropoff_points_new($province, $municipality) {
        $dropoff_points = DB::connection('delivery_inspection_db')
        ->table('tbl_delivery')
        ->select('dropOffPoint','prv_dropoff_id','prv')
        ->where('province', $province)
        ->where('municipality', $municipality)
        ->where("isBuffer", "!=", 9)
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
        ->where('batchTicketNumber', "TRANSFER")
        ->orderBy('dropOffPoint', 'asc')
        ->groupBy("prv_dropoff_id")
        ->get();

        return $dropoff_points;
    }

    function getCoopRegPending($accreditation_no, $region){
        $pendingBags = 0;
        $dropoff_points = DB::connection('delivery_inspection_db')
        ->table('tbl_delivery_transaction')
        ->select('region')
        ->addSelect(DB::raw("SUM(instructed_delivery_volume) as total"))
        ->where('accreditation_no', $accreditation_no)
        ->where('region', $region)
        ->where('isBuffer','!=',9)
        ->where('status', 0)
        ->groupBy("region")
        ->get();

        if(count($dropoff_points) > 0){ 
            foreach($dropoff_points as $dop){
                $pendingBags = $dop->total;
            }
		}

        return $pendingBags;
    }

    function getCoopRegConfirmed($accreditation_no, $moa_number, $region){
		$confirmedBags = 0;

        $dropoff_points = DB::connection('delivery_inspection_db')
        ->table('tbl_delivery')
        ->select('region')
        ->addSelect(DB::raw("SUM(totalBagCount) as total"))
        ->where('coopAccreditation', $accreditation_no)
        ->where('region', $region)
        ->where('is_cancelled', 0)
        ->whereNOTIn('batchTicketNumber',function($query) use ($moa_number, $region){
            $query->select('batchTicketNumber')
                ->from('tbl_actual_delivery')
                ->where([
                    'moa_number' => $moa_number,
                    'region' => $region, 
                    'isBuffer' => 0
                ])
                ->groupBy("batchTicketNumber");
         })
        ->groupBy("region")
        ->get();

        if(count($dropoff_points) > 0){ 
            foreach($dropoff_points as $dop){
                $confirmedBags = $dop->total;
            }
		} 
		
		return $confirmedBags;
	}

    function getCoopRegInspected($accreditation_no, $region ){
		$inspectedBags = 0;

        $dropoff_points = DB::connection('delivery_inspection_db')
        ->table('tbl_actual_delivery')
        ->select('region')
        ->addSelect(DB::raw("SUM(totalBagCount) as total"))
        ->whereIn('batchTicketNumber',function($query) use ($accreditation_no, $region){
            $query->select('batchTicketNumber')
                ->from('tbl_delivery')
                ->where([
                    'moa_number' => $accreditation_no,
                    'region' => $region, 
                    'is_cancelled' => 0
                ])
                ->groupBy("batchTicketNumber");
         })
        ->groupBy("region")
        ->get();

        if(count($dropoff_points) > 0){ 
            foreach($dropoff_points as $dop){
                $inspectedBags = $dop->total;
            }
		} 

		
		return $inspectedBags;
	}

    function hasRegionCommitment($accreditation_no, $region){
		$hasData = 0;

        $dropoff_points = DB::connection('mysql')
        ->table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
        ->select('coop_name', 'region_name')
        ->addSelect(DB::raw("SUM(volume) as total"))
        ->where('accreditation_no', $accreditation_no)
        ->where('region_name', $region)
        ->groupBy("region_name")
        ->get();

        if(count($dropoff_points) > 0){ 
            $hasData = 1;
		}

        return $hasData;
		
	} 

    function getCoopRegionalCommitment($accreditation_no, $region ){

        $dropoff_points = DB::connection('mysql')
            ->table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->select('coop_name', 'region_name')
            ->addSelect(DB::raw("SUM(volume) as total"))
            ->where('accreditation_no', $accreditation_no)
            ->where('region_name', $region)
            ->groupBy("region_name")
            ->first();
        
		return $dropoff_points;
	}

    function insert_delivery_sched($data) {
        // Pending release table based on province posted by app
        /* $table = 'prv' . $prv . '_released'; */

        // $province = Auth::user()->province;

        DB::beginTransaction();
        try {
            /* DB::connection('delivery_inspection_db')
              ->table($table)
              ->insert($data); */
            DB::connection('delivery_inspection_db')
                    ->table('tbl_delivery_transaction')
                    ->insert($data);
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }
}

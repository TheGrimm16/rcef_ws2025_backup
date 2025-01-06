<?php

namespace App;

use DB;

class SeedCooperatives
{
    function seed_cooperatives() {
        $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
        ->select('*')
        ->orderBy('coopName')
        ->get();

        return $cooperatives;
    }

    function seed_cooperative($coopId) {
        $cooperative = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as coop')
        ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces as province', 'province.id', '=', 'coop.provinceId')
        ->select('*')
        ->where('coop.coopId', $coopId)
        ->first();

        return $cooperative;
    }

    function seed_cooperatives_planted() {
        // $cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        // ->select('coopId')
        // ->distinct()
        // ->get();

        // return $cooperatives;
        return "";
    }

    function seed_cooperatives_area_planted($coopId) {
        // $area_planted = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values')
        // ->where('coopId', $coopId)
        // ->sum('Area_Planted_in_ha');

        // return $area_planted;
        return "";
    }

    function seed_growers_count($coopId) {
        // $area_planted = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values')
        // ->where('coopId', $coopId)
        // ->get();

        // return count($area_planted);
        return "";
    }

    function municipalities($coopId) {
        $municipalities = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        ->leftJoin('seed_growers.seed_growers_all as seed_growers', 'seed_growers.Code_Number', '=', 'producers.Accreditation_Number')
        ->leftJoin('seed_growers.municipalities as mun', 'mun.cityId', '=', 'seed_growers.cityId')
        ->select('mun.cityId', 'mun.municipality_name', DB::RAW('SUM(Area_Planted_in_ha) as area_planted'), 'producers.Seed_variety')
        ->where('producers.coopId', $coopId)
        ->groupBy('producers.Seed_variety')
        ->groupBy('mun.cityId')
        ->get();

        return $municipalities;
    }
}

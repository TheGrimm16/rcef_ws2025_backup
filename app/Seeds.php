<?php

namespace App;

use DB;

class Seeds
{
    function planted_seeds($coopId) {
        $seeds = DB::table('seed_seed.seed_characteristics as seeds')
        ->leftjoin($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers', 'producers.Seed_variety', '=', 'seeds.variety')
        ->select('seeds.variety', 'seeds.ave_yld', 'seeds.maturity', 'producers.Date_planted', 'producers.Area_Planted_in_ha')
        ->where('producers.coopId', $coopId)
        ->where('seeds.variety_name', 'NOT LIKE', '%DWSR%')
        ->get();

        return $seeds;
    }

    function planted_seeds_mun($coopId, $cityId) {
        $seeds = DB::table('seed_seed.seed_characteristics as seeds')
        ->leftjoin($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers', 'producers.Seed_variety', '=', 'seeds.variety')
        ->leftJoin('seed_growers.seed_growers_all as seed_growers', 'seed_growers.Code_Number', '=', 'values.Accreditation_Number')
        ->leftJoin('seed_growers.municipalities as mun', 'mun.cityId', '=', 'seed_growers.cityId')
        ->select('seeds.variety', 'seeds.ave_yld', 'seeds.maturity', 'producers.Date_planted', 'producers.Area_Planted_in_ha')
        ->where('producers.coopId', $coopId)
        ->where('seed_growers.cityId', $cityId)
        ->where('seeds.variety_name', 'NOT LIKE', '%DWSR%')
        ->get();

        return $seeds;
    }

    function seed($variety) {
        $seed = DB::table('seed_seed.seed_characteristics')
        ->select('*')
        ->where('variety', $variety)
        ->where('variety_name', 'NOT LIKE', '%DWSR%')
        ->first();

        return $seed;
    }
    function seed_all() {
        $seed = DB::table('seed_seed.seed_characteristics')
        ->select('*')
        ->where('variety_name', 'NOT LIKE', '%DWSR%')
        ->get();

        return $seed;
    }
}

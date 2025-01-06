<?php

namespace App;

use DB;

class Transplant
{
    function transplanting_date($coopId, $order) {
        // $transplanting_date = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        // ->select('Date_planted')
        // ->where('coopId', $coopId)
        // ->orderBy('Date_planted', ''.$order.'')
        // ->first();

        // return $transplanting_date;
        return "";
    }

    function transplanting_date_mun($coopId, $cityId, $order) {
        // $transplanting_date = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        // ->leftJoin('seed_seed.seed_characteristics as seeds', 'seeds.variety', '=', 'producers.Seed_variety')
        // ->leftJoin('seed_growers.seed_growers_all as seed_growers', 'seed_growers.Code_Number', '=', 'producers.Accreditation_Number')
        // ->leftJoin('seed_growers.municipalities as mun', 'mun.cityId', '=', 'seed_growers.cityId')
        // ->select('producers.Date_planted', 'seeds.maturity')
        // ->where('producers.coopId', $coopId)
        // ->where('seed_growers.cityId', $cityId)
        // ->where('seeds.variety_name', 'NOT LIKE', '%DWSR%')
        // ->orderBy('producers.Date_planted', ''.$order.'')
        // ->first();

        // return $transplanting_date;
        return "";
    }
}

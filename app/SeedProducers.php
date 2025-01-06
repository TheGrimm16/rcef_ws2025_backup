<?php

namespace App;

use DB;

class SeedProducers
{
    function seed_producers_planted() {
        $producers_planted = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        ->leftJoin('seed_seed.seed_characteristics as seeds', 'seeds.variety', '=', 'producers.Seed_Variety')
        ->select('*')
        ->where('seeds.variety_name', 'NOT LIKE', '%DWSR%')
        ->get();

        return $producers_planted;
    }

    function seed_producers_planted_filtered($coopId) {
        $producers_planted = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        ->leftJoin('seed_seed.seed_characteristics as seeds', 'seeds.variety', '=', 'producers.Seed_Variety')
        ->leftJoin('seed_growers.seed_growers_all as seed_growers', 'seed_growers.Code_Number', '=', 'producers.Accreditation_Number')
        ->select('*')
        ->where('producers.coopId', $coopId)
        ->where('seeds.variety_name', 'NOT LIKE', '%DWSR%')
        ->get();

        return $producers_planted;
    }

    function seed_growers() {
        $seed_growers = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        ->leftJoin('seed_growers.seed_growers_all as seed_growers', 'seed_growers.Code_Number', '=', 'producers.Accreditation_Number')
        ->leftJoin('seed_growers.provinces as provinces', 'provinces.provinceId', '=', 'seed_growers.provinceId')
        ->leftjoin($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives as coop', 'coop.coopId', '=', 'producers.coopId')
        ->select('seed_growers.Name', 'producers.Accreditation_Number', 'provinces.province_name', 'coop.coopName')
        ->distinct()
        ->get();

        return $seed_growers;
    }

    function seed_grower($accreditation_no) {
        $seed_grower = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values')
        ->select('*')
        ->where('Accreditation_Number', $accreditation_no)
        ->get();

        return $seed_grower;
    }

    function seed_producers_accreditation($coopId) {
        /*$accreditation = DB::table($GLOBALS['season_prefix'].'rcep_producers.tbl_values as producers')
        ->select('*')
        ->where('producers.coopId', $coopId)
        ->get();

        return $accreditation;*/

        $accreditation = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_total_commitment as producers')
        ->select('*')
        ->where('producers.coopID', $coopId)
        ->get();

        return $accreditation;
    }
}

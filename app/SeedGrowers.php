<?php

namespace App;

use DB;

class SeedGrowers
{
    function profile($accreditation_no) {
        $profile = DB::table('seed_growers.seed_growers_all')
        ->select('*')
        ->where('Code_Number', $accreditation_no)
        ->first();

        return $profile;
    }
}

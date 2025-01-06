<?php

namespace App;

use DB;

class Regions
{
    function assigned_region($province) {
        $region = DB::table('lib_provinces')
        ->select('regCode')
        ->where('provCode', $province)
        ->first();

        return $region;
    }
}

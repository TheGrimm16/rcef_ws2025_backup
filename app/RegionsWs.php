<?php

namespace App;

use DB;

class RegionsWs
{
    function assigned_region($province) {
        $region = DB::table('lib_provinces')
        ->select('regCode')
        ->where('provCode', $province)
        ->first();

        return $region;
    }
}

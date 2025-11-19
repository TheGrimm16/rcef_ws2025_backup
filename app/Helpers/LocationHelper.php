<?php

namespace App\Helpers;

use DB;

class LocationHelper
{
    public static function getProvinces()
    {
        return DB::connection('local')
            ->table('lib_provinces')
            ->select('provCode as code', 'provDesc as name')
            ->orderBy('name')
            ->get();
    }

    public static function getMunicipalities($provinceCode)
    {
        return DB::connection('local')
            ->table('lib_municipalities')
            ->select('citymunCode as code', 'citymunDesc as name')
            ->where('provCode', $provinceCode)
            ->orderBy('name')
            ->get();
    }
}

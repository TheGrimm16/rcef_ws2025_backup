<?php

namespace App\Http\Controllers\Select2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    protected $tbl;

    public function __construct()
    {
        $this->tbl = $GLOBALS['season_prefix'] . "rcep_delivery_inspection" . ".lib_prv";
    }

    /**
     * Fetch Regions
     */
    public function getRegions()
    {
        $rows = DB::table($this->tbl)
            ->select('regCode as id', 'regionName as text')
            ->groupBy('regCode', 'regionName')
            ->orderBy('region_sort')
            ->get();

        return response()->json($rows);
    }

    /**
     * Fetch Provinces (include region code)
     */
    public function getProvinces($regionCode = null)
    {
        $query = DB::table($this->tbl)
            ->select('prv_code as id', 'province as text', 'regCode');

        if ($regionCode) {
            $query->where('regCode', $regionCode);
        }

        $rows = $query->groupBy('prv_code', 'province', 'regCode')
            ->orderBy('province')
            ->get();

        return response()->json($rows);
    }

    /**
     * Fetch Municipalities (include province code)
     */
    public function getMunicipalities($provinceCode = null)
    {
        $query = DB::table($this->tbl)
            ->select('prv as id', 'municipality as text', 'prv_code AS provCode');

        if ($provinceCode) {
            $query->where('prv_code', $provinceCode);
        }

        $rows = $query->groupBy('prv', 'municipality', 'prv_code')
            ->orderBy('municipality')
            ->get();

        return response()->json($rows);
    }
}

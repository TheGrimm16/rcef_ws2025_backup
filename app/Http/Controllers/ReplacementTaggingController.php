<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use DB;
use Session;
use Auth;
use Excel;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use Yajra\Datatables\Facades\Datatables;
use App\utility;

class ReplacementTaggingController extends Controller
{
    public function home_ui(){
    $regions = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
    ->groupBy('regionName')
    ->get();

    
    return view('replacement_tagging.home',
        compact('regions'));
        
    }

    public function getProvinces(Request $request){
        $season = ($GLOBALS['season_prefix']);
        $prvCodes = DB::table('information_schema.tables')
        ->select(DB::raw("REPLACE(REPLACE(table_schema,'$season',''),'prv_','') as provCode"))
        ->where('TABLE_SCHEMA', "LIKE", $season.'prv_'.$request->reg.'%')
        ->groupBy('provCode')
        ->get();

        $provCodesArray = collect($prvCodes)->pluck('provCode');

        $getProvinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
        ->select('prv_code', 'province')
        ->whereIn('prv_code', $provCodesArray)
        ->groupBy('prv_code')
        ->orderBy('prv_code', 'asc')
        ->get();

        return response()->json($getProvinces);
    }


   
}

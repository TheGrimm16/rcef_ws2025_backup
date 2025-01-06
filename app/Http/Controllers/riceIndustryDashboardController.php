<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Datatables;
use Auth;
class riceIndustryDashboardController extends Controller
{
    public function index(){

        
    }

    public function getProvincialData(Request $request)
    {   
        $allProvData = [];
        $allProvinces = DB::table('information_schema.tables')
        ->select(DB::raw("table_schema as allProv"))
        ->where('TABLE_SCHEMA', "LIKE", $GLOBALS["season_prefix"].'prv_%')
        ->groupBy('allProv')
        ->get();

        foreach($allProvinces as $province) {
            $databaseName = $province->allProv;
            $prv = substr($province->allProv,11,4);
            
            $newReleasedCount = DB::table($databaseName.'.new_released')->count();

            if($newReleasedCount>1){
                continue;
            }
            else{
                $getProvData = DB::table('ds2024_prv_0128.new_released')
                ->select(DB::raw('LEFT(prv_dropoff_id,6) as prv_no'),'seed_variety', DB::raw('SUM(bags_claimed) as bags'), DB::raw('SUM(claimed_area) as area'), DB::raw('(SUM(bags_claimed)*20) as kgs'))
                ->where('category', 'LIKE', 'INBRED')
                ->groupBy('seed_variety')
                ->get();

                dd($getProvData->prv_no);

                array_push($allProvData,$getProvData);
                // array_push($allProvData,array(
                //     "psa_code" => 
                // ));
            }
        }
        dd($allProvData);
    }

}

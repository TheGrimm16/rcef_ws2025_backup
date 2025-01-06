<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use DB;
use Hash;
use Auth;
use Yajra\Datatables\Datatables;
use Validator;
use Session;
use Illuminate\Support\Collection;


class OfflineFixController extends Controller
{
    public function __construct()
    {
        
    }

    public function fix_area(){
          $data = DB::connection('mysql')
          ->table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
          ->select('*')
          ->where('created_by', '!=', null)
          ->where(function ($query) {
               $query->where('status', "=", 1);
                    // ->orWhere('status', "=", 2)
                    // ->orWhere('status', "=", 3);
          })
          ->where(function ($query) {
               $query->where('committed_area', "=", 0);
          })
          // ->where('enableEdit', 0)
          ->get();

          // dd($data);
          foreach($data as $d){
               if($d->farm_area_ws2021 != 0){
                    DB::connection('mysql')
                    ->table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->where('sed_id', $d->sed_id)
                    ->where('muni_code', $d->muni_code)
                    ->update([
                         'committed_area' => $d->farm_area_ws2021
                    ]);
               }else if($d->farm_area_ds2021 != 0){
                    DB::connection('mysql')
                    ->table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->where('sed_id', $d->sed_id)
                    ->where('muni_code', $d->muni_code)
                    ->update([
                         'committed_area' => $d->farm_area_ds2021
                    ]);
               }
          }
          dd($data);
    }
     
     
}

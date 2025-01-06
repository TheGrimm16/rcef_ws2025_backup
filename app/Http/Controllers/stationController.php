<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class stationController extends Controller
{
    public function loginData(Request $request){
    

        $data = DB::table('users')->where('username',$request->username)->first();
        $station = DB::table('station_domain.lib_station_domain')->where('station_code',$data->stationId)->first();
        $token = $request->_token;
        return compact('station','token');

    }
}

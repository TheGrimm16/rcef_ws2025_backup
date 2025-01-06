<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
class optimizationController extends Controller
{
    public function a(){
        DB::table("_rcef_connect.tbl_routes_logs")
            ->insert([
                "routes" => "FUNCTION A"
            ]);
    }


}

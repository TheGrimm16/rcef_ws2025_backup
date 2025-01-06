<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Style\Fill;




// use DB;
use Session;
use Auth;
use Excel;
use Hash;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;
use App\utility;


class epaalalayController extends Controller
{
    public function login($login_id,$password, $login){
        $getUserInfo = DB::table('ds2024_sdms_db_dev.users')
                ->where('username', $login_id)
            ->first();
        
        if($getUserInfo){
            if(Hash::check($password, $getUserInfo->password)){
                $getUserInfo = json_encode($getUserInfo);
                return $getUserInfo;
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }

    public function advisory(){
        $getFarmerInfo = DB::table('ds2024_epaalalay.ds2024_0128')
        ->where('category','LIKE','INBRED')
        ->first();

        $month = substr($getFarmerInfo->sowing_date,0,2);
        $week = substr($getFarmerInfo->sowing_date,3,2);
        dd($month,$week);
    }

    
}

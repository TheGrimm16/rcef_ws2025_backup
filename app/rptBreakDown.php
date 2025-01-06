<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Config;
use DB;
use Session;
use Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Requests;

class rptBreakDown extends Model
{
   function getLocationList($field,$where,$comparison, $table){
   		$data = DB::table($table)
   			->select($field)
   			->where($where, 'like', '%'.$comparison)
   			->groupBy($field)
   			->orderBy($field, 'ASC')
   			->get();

   		return $data;

   }














}

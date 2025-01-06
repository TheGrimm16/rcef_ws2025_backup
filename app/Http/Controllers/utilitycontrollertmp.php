<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use App\Http\Controllers\Controller;

class utilitycontrollertmp extends Controller
{
    public function generatorView(){
        return view('generator.index');
    }

}

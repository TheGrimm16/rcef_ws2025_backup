<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;
use App\SeedCooperatives;
use App\SeedProducers;
use App\Transplant;
use App\Seeds;
use App\SeedGrowers;
use App\DeliveryInspect;
use App\Regions;
use App\Provinces;
use App\Municipalities;
use App\DropoffPoints;

use PDFTIM;
use DB;
use Excel;
use Auth;

class daDashboardController extends Controller
{
    /*public function index()
    {
        return view('deliverydashboard.index');
    }*/


    public function index()
    {
        $regions = $this->get_regions();

        $regionName = $this->get_region_name();
        return view('dashboard.da_dashboard.home')
            ->with("regions", $regions)
            ->with("regionName", $regionName);
    }

    public function get_regions() {
        $data = DB::table($GLOBALS['season_prefix']."sdms_db_dev" . '.lib_regions')
                ->select('*')
                ->where('regDesc', '!=', 'NCR')
                ->orderBy('order')
                ->get();

        return $data;
    }

    public function get_region_name(){
        $data = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->groupBy("regionName")
            ->get();
            $regionNameArr = array();
            foreach ($data as $key => $value) {
                $regionNameArr[$value->regCode] = $value->regionName; 
            }
            return $regionNameArr;
    }


    public function loadProvince(Request $request){

           // dd($request->region_id);
        $region_id = $request->region_id;
       if(!isset($region_id[1])){
        $region_id = "0".$region_id;
       }
      // dd($region_id);
       $data = DB::table($GLOBALS['season_prefix']."sdms_db_dev" . '.lib_provinces')
                ->select('*')
                ->where('regCode',  $region_id)
                ->orderBy('provDesc', 'ASC')
                ->get();

                //dd($region_id);
        return $data;
    }



    public function loadMunicipality(Request $request){
        $return_arr = array();
       $municipality_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->where("province", 'LIKE', '%'.$request->province.'%')
            ->groupBy("municipality")
            ->orderBy("municipality")
            ->get();


            foreach ($municipality_list as $municipality) {
                $db = 'rpt_'.$municipality->regCode.$municipality->provCode;
                $tbl = 'tbl_'.$municipality->regCode.$municipality->provCode.$municipality->munCode;
                    $DBExistsql = "SELECT * FROM information_schema.tables WHERE table_schema = '".$db."' AND table_name = '".$tbl."' LIMIT 1";
                         $checkDB = DB::select(DB::raw($DBExistsql));
                        if(count($checkDB)>0){
                             $dataCheck = DB::table($db.'.'.$tbl)
                                    ->first();
                                if(count($dataCheck)>0){
                                     $btn = "<button class='form-control btn-success'> <i class='fa fa-file-pdf-o' aria-hidden='true'>  Download PDF</i>  </button> ";
                                }else{
                                     $btn = "<button class='form-control btn btn-dark' title='No Data' style = 'cursor: not-allowed; pointer-events:none;'> <i class='fa fa-file-pdf-o' aria-hidden='true'>  Download PDF</i>  </button> ";
                                } 
                        }else{
                                     $btn = "<button class='form-control btn btn-dark' title='No Data' style = 'cursor: not-allowed; pointer-events:none;' > <i class='fa fa-file-pdf-o' aria-hidden='true'>  Download PDF</i>  </button> ";
                        }

                    $batch_data = array(
                    'region' => $municipality->regionName,
                    'province' => $municipality->province,
                    'municipality' => $municipality->municipality,
                    'action' => $btn
                    );
                    array_push($return_arr, $batch_data);
            }
               
        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
            ->make(true);

    }









    
  



}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Routing\UrlGenerator;
use App\Connect;
use Auth;

class ConnectController extends Controller {

    public function receive_moet_data(Request $request){
		//$farmer_tbl_array = json_decode($request);
		dd($request[0]);
	}


    public function get_dropoffpoints(Request $request) {

        $input = $request->all();
        $connect = new Connect();
		
		$drop_offpoints = $connect->_drop_offpoints($input['province']);
		
        $data = array();

        foreach ($drop_offpoints as $item) {
            $data[] = array(
                'prv_dropoff_id' => $item->prv_dropoff_id,
                'dropOffPoint' => $item->municipality . ' - ' . $item->dropOffPoint
            );
        }
        echo json_encode($data);
    }

    public function get_coops(Request $request) {

        $input = $request->all();
        $connect = new Connect();
      //  $drop_offpoints = $connect->_cooperatives();
        

         $drop_offpoints = DB::connection('ls_seed_coop')->table('tbl_cooperatives')
                ->select('*')
                ->orderBy('coopName', 'asc')
                ->get();




        $data = array();

        foreach ($drop_offpoints as $item) {
            $data[] = array(
                'accreditation_no' => $item->accreditation_no,
                'coopName' => $item->coopName
            );
        }
        echo json_encode($data);
    }
}

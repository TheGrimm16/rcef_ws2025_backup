<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Checking;
use Yajra\Datatables\Datatables;
use Auth;
class CheckingController extends Controller {

    public function index() {
        $checking = new Checking();
        $dropoffpoints = $checking->_drop_offpoints();

        $dropoff = array();

        foreach ($dropoffpoints as $item) {
            $data = array(
                'prv_dropoff_id' => $item->prv_dropoff_id,
                'dropOffPoint' => $item->municipality . ' - ' . $item->dropOffPoint
            );
            array_push($dropoff, $data);
        }
        return view('checking.index')
                        ->with(compact('dropoff'));
    }

    public function delete_farmer_data(Request $request) {
        $profile = new Checking();
        $input = $request->all();
        $profile->_delete_farmer_data($input['rsbsa']);
        
        return json_encode("success");
    }

    public function deleteFarmer(Request $request) {
        $profile = new Checking();
        $input = $request->all();
        $profile->_delete_farmer($input['farmer_id'], $input['qr_code'], $input['rsbsa_control_no']);
    }

    public function table(Request $request) {
        $input = $request->all();
        return view('checking.table');
    }

    public function showUnreleased(Request $request) {
        $profile = new Checking();
        $input = $request->all();

        $searchdata = $profile->_get_pendingreleases($input['drop_id']);
        $table_data = array();
        $i = 1;
        foreach ($searchdata as $dv) {
            $get_profile = $profile->_get_farmer_details($dv->farmer_id, $dv->rsbsa_control_no);
            $pending_data = $profile->_get_release_data($dv->farmer_id, $dv->rsbsa_control_no, "pending_release", 2);
            $release_count = $profile->_get_release_data($dv->farmer_id, $dv->rsbsa_control_no, "released", 1);

            $data_search = array(
                'number' => $i,
                'rsbsa' => $dv->rsbsa_control_no,
                'rsbsa_id' => $dv->rsbsa_control_no,
                'qr' => ($get_profile != "" ? $get_profile->distributionID : ""),
                'full_name' => ($get_profile != "" ? $get_profile->firstName . ' ' . $get_profile->midName . ' ' . $get_profile->lastName : ""),
                'variety' => ($pending_data != "" ? $pending_data->seed_variety : ""),
                'bags' => ($pending_data != "" ? $pending_data->bags : ""),
                'actual_area' => ($get_profile != "" ? $get_profile->actual_area : ""),
                'area' => ($get_profile != "" ? $get_profile->area : ""),
                'date' => ($pending_data != "" ? $pending_data->date_created : ""),
                'id' => ($get_profile != "" ? $get_profile->id : "")
            );
            array_push($table_data, $data_search);
            $i++;
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                        ->addColumn('action', function($table_data) {
                            return ((Auth::user()->roles->first()->name == "system-encoder") ?  "" :'<a for="' . $table_data['rsbsa_id'] . '" class="btn btn-danger btn-xs deleteDatacheck"><i class="fa fa-trash-o"></i> Delete </a>');
                        })
                        ->make(true);
    }

    public function search(Request $request) {
        $profile = new Checking();
        $input = $request->all();

        $searchdata = $profile->_get_farmer_profile($input['search_data']);
        $table_data = array();
        $i = 1;
        foreach ($searchdata as $dv) {
            $pending_data = $profile->_get_release_data($dv->farmerID, $dv->rsbsa_control_no, "pending_release", 2);
            $release_count = $profile->_get_release_data($dv->farmerID, $dv->rsbsa_control_no, "released", 1);
            $data_search = array(
                'number' => $i,
                'rsbsa' => $profile->_highlight($input['search_data'], $dv->rsbsa_control_no),
                'rsbsa_id' => $dv->rsbsa_control_no,
                'qr' => $profile->_highlight($input['search_data'], $dv->distributionID),
                'full_name' => $profile->_highlight($input['search_data'], $dv->firstName . ' ' . $dv->midName . ' ' . $dv->lastName),
                'variety' => ($pending_data != "" ? $pending_data->seed_variety : ""),
                'bags' => ($pending_data != "" ? $pending_data->bags : ""),
                'actual_area' => $dv->actual_area,
                'area' => $dv->area,
                'date' => ($pending_data != "" ? $pending_data->date_created : ""),
                'id' => $dv->id
            );
            array_push($table_data, $data_search);
            $i++;
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
                        ->addColumn('action', function($table_data) {
							
                            return (Auth::user()->roles->first()->name != "system-encoder" ?'<a for="' . $table_data['rsbsa_id'] . '" class="btn btn-danger btn-xs deleteDatacheck"><i class="fa fa-trash-o"></i> Delete </a>' : "");
                        })
                        ->make(true);
    }

}

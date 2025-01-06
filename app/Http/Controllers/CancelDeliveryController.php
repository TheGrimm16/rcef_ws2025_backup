<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;

use DB;
use Session;
use Auth;

class CancelDeliveryController extends Controller
{
    public function cancel_home(){
        return view('delivery.cancel_home');
    }
    
    public function cancel_batch_details(Request $request){
        $station_prv = DB::table('lib_station')
                ->where('stationID', Auth::user()->stationId)
                ->get();
        $stprv = [];
        foreach($station_prv as $s){
            $stprv[] = $s->province;
        }
        
        $batch_number = $request->batch_number;
        $batch_details = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber',DB::raw('SUM(totalBagCount) as total_bags'), 'coopAccreditation', 'region', 'province', 'municipality', 'dropOffPoint', "deliveryDate", "prv")
            ->where('batchTicketNumber', 'like', "%".$batch_number)
            ->where('is_cancelled', 0)
            ->groupBy('batchTicketNumber')
            ->first();
        if(count($batch_details) > 0){
            $coop_name = DB::connection('seed_coop_db')->table('tbl_cooperatives')
                ->where('accreditation_no', $batch_details->coopAccreditation)
                ->value('coopName');
            
            $variety_list = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_number)
                ->where('is_cancelled', 0)
                ->groupBy('seedVariety')
                ->get();
            $variety_str = '';
            foreach($variety_list as $row){
                $variety_str .= $row->seedVariety.',<br> ';
            }

            $variety_str = rtrim($variety_str, ',<br> ');

            $seed_lot_list = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('batchTicketNumber', $batch_number)
                ->where('is_cancelled', 0)
                ->get();
            $seed_lot_str = '';
            foreach($seed_lot_list as $seed_row){
                $seed_lot_str .= $seed_row->seedTag.", ";
            }
            $seed_lot_str = rtrim($seed_lot_str,', ');
        }

        $data_arr[] = array(
            "batchTicketNumber" => $batch_details->batchTicketNumber,
            "total_bags" => number_format($batch_details->total_bags),
            "region" => $batch_details->region,
            "province" => $batch_details->province,
            "municipality" => $batch_details->municipality,
            "dropOffPoint" => $batch_details->dropOffPoint,
            "coop_name" => $coop_name,
            "variety_str" => $variety_str,
            "deliveryDate" => $batch_details->deliveryDate,
            "seed_lot_str" => $seed_lot_str,
            "prv" => $batch_details->prv
        );

        $data_arr = collect($data_arr);

        return Datatables::of($data_arr)
        ->addColumn('action', function($row) use ($stprv) {
            if(in_array($row['province'], $stprv) ||  Auth::user()->roles->first()->name == "rcef-programmer"){
                return "<a type='button' class='btn btn-danger btn-sm' href='#' data-toggle='modal' data-target='#cancel_verification_modal' data-batch='".$row["batchTicketNumber"]."'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }else{
                return "<span class='text-warning'>You do not have permission to cancel this delivery!</span>";
            }
            
        })
        ->addColumn('delivery_address', function($row) use ($stprv) {
            // $user_prov = Auth::user()->province;
            // $prov = substr($row['prv'], 0, -2);
            // if($user_prov != $prov){
            //     return "";
            // }
            return "<b>REGION:</b> ".$row["region"]."<br>
                    <b>PROVINCE:</b> ".$row["province"]."<br>
                    <b>MUNICIPALITY:</b> ".$row["municipality"]."<br>
                    <b>DROPOFF POINT:</b> ".$row["dropOffPoint"]."<br>";
        })
        ->addColumn('delivery_details', function($row) use ($stprv) {
            if(in_array($row['province'], $stprv) ||  Auth::user()->roles->first()->name == "rcef-programmer"){
                return "<b>VARITIES:</b><br>".$row["variety_str"]."<br>"."<br><b>SEED TAGS:</b><br>".$row["seed_lot_str"];
            }
            return "";
        })
        ->addColumn('total_bag_count', function($row) use ($stprv) {
            if(in_array($row['province'], $stprv) ||  Auth::user()->roles->first()->name == "rcef-programmer"){
                return $row["total_bags"]." bag(s)";
            }
           return "";
        })
        ->make(true);
    }

    public function cancel_batch_update_flags(Request $request){
        $this->validate($request, array(
            'batch_number_update' => 'required',
            'reason' => 'required'
        ));

        DB::beginTransaction();
        try {
            $batch_number = $request->batch_number_update;
            $reason = $request->reason;
    
            //update data in tbl_delivery || is_cancelled = 1
            DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->where('batchTicketNumber', $batch_number)
            ->update([
                'is_cancelled' => 1,
                'cancelled_by' => Auth::user()->username,
                'reason' => $reason
            ]);
    
            DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
            ->insert([
                'batchTicketNumber' => $batch_number,
                'status' => 4,
                'dateCreated' => date("Y-m-d H:i:s"),
                'send' => 0
            ]);
			
			//update delivery transaction
			DB::connection('delivery_inspection_db')->table('tbl_delivery_transaction')
            ->where('batchTicketNumber', $batch_number)
            ->update([
                'status' => 3
            ]);
    
            Session::flash('success', 'You have successfully cancelled the seed delivery ('.$batch_number.')');
            return redirect()->route('delivery_web.cancel.home');

        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'There was an error while cancelling a seed delivery, the database has been rolled back. please try again.');
        }
    }
	
	public function check_batch_details(Request $request){
       
        $batch_delivery = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select('batchTicketNumber',DB::raw('SUM(totalBagCount) as total_bags'), 'coopAccreditation', 'region', 'province', 'municipality', 'dropOffPoint', "deliveryDate")
            ->where('batchTicketNumber', 'like', '%'.$request->batch_number)
            ->where('is_cancelled', 0)
            ->groupBy('batchTicketNumber')
            ->first();

        if(count($batch_delivery) > 0){
            //check if inspected
            $inspected = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')                
                ->where('batchTicketNumber', $batch_delivery->batchTicketNumber)
                ->groupBy('batchTicketNumber')
                ->first();

            if(count($inspected) > 0){
                return 'already_inspected';
            }else{
                return 'ok_for_cancel';
            }

        }else{
            return 'no_batch_return';
        }
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;
use Excel;

use Yajra\Datatables\Datatables;

class CoopOperatorController extends Controller
{
    public function coop_delivery_home(){
        $coop_accre = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->value('coopAccreditation');
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->where('region', '!=', '')
            ->where('coopAccreditation', $coop_accre)
            ->groupBy('province')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        $latest_mirror_delivery_date = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_logs')->where('category', 'DELIVERY_DATA_MIRROR')->orderBy('id', 'DESC')->value('date_recorded');
        $latest_mirror_delivery_date = date("F j, Y g:i A", strtotime($latest_mirror_delivery_date));

        return view('dashboard.coop_delivery')->with('coop_accre', $coop_accre)->with('provinces', $provinces)->with('latest_mirror_delivery_date', $latest_mirror_delivery_date);
    }

    public function coop_delivery_list(Request $request){
        $batch_deliveries = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
            ->select('batchTicketNumber', 'coopAccreditation', 'seedVariety', 'deliveryDate', 'dropOffPoint', 'region', 'province', 'municipality')
            ->where('coopAccreditation', $request->coop_accre)
            ->groupBy('batchTicketNumber', 'seedVariety')
            ->orderBy('deliveryDate', 'DESC')
            ->get();

        $return_arr = array();
        foreach($batch_deliveries as $batch_row){
            $confirmed_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->where('coopAccreditation', $request->coop_accre)
                ->where('seedVariety', $batch_row->seedVariety)
                ->sum('totalBagCount');

            $check_inspected = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->first();

            if(count($check_inspected) > 0){
                $inspected_bags = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                    ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                    ->where('seedVariety', $batch_row->seedVariety)
                    ->sum('totalBagCount');
                
                $inspected_bags = number_format($inspected_bags)." bag(s)";
            }else{
                $inspected_bags = "N/A";
                $batch_status = "N/A";
            }

            $batch_status = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_status')
                ->where('batchTicketNumber', $batch_row->batchTicketNumber)
                ->orderBy('deliveryStatusId', 'DESC')
                ->value('status');

            if($batch_status == 0){
                $batch_status = 'Pending';
            }
            else if($batch_status == 1){
                $batch_status = 'Passed';
            }
            else if($batch_status == 2){
                $batch_status = 'Rejected';
            }else if($batch_status == 3){
                $batch_status = 'In Transit';
            }else if($batch_status == 4){
                $batch_status = 'Cancelled';
            }
                
            $batch_data = array(
                'batchTicketNumber' => $batch_row->batchTicketNumber,
                'coopAccreditation' => $batch_row->coopAccreditation,
                'seedVariety' => $batch_row->seedVariety,
                'dropOffPoint' => $batch_row->dropOffPoint,
                'region' => $batch_row->region,
                'province' => $batch_row->province,
                'municipality' => $batch_row->municipality,
                'confirmed' => number_format($confirmed_bags)." bag(s)",
                'inspected' => $inspected_bags,
                'deliveryDate' => date("Y-m-d", strtotime($batch_row->deliveryDate)),
                'batch_status' => $batch_status
            );

            array_push($return_arr, $batch_data);
        }
        
        $return_arr = collect($return_arr);
        return Datatables::of($return_arr)
        ->addColumn('action', function($row) {
            if($row['batch_status'] == 'Cancelled' ||  $row['batch_status'] == 'Passed' || $row['batch_status'] == 'Rejected'){
                return "<a type='button' style='color: #fff;background-color: #f79f9d;border-color: #f1b3b1;' class='btn btn-danger btn-sm' href='#'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }else{
                return "<a type='button' class='btn btn-danger btn-sm' href='#' data-toggle='modal' data-target='#cancel_verification_modal' data-batch='".$row["batchTicketNumber"]."'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }
        })
        ->make(true);
    }

    public function cancel_delivery(Request $request){
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
    
            Session::flash('success', 'You have successfully cancelled the seed delivery ('.$batch_number.')');
            return redirect()->route('coop_operator.deliveries');

        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'There was an error while cancelling a seed delivery, the database has been rolled back. please try again.');
            return redirect()->route('coop_operator.deliveries');
        }
    }

    public function compute_variety_total($deliveries, $seed_variety){
        $total_variety = 0;
        foreach($deliveries as $row){
            $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
                ->where('ad.batchTicketNumber', "=", $row->batchTicketNumber)
                ->where('ad.seedVariety', "=", $seed_variety)
                ->sum('totalBagCount');
            
            $total_variety += $variety_count;
        }

        return $total_variety;
    }

    public function compute_confirmed_total($seed_variety, $accreditation){
        $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as cd')
            ->where('cd.seedVariety', "=", $seed_variety)
            ->where('cd.coopAccreditation', "=", $accreditation)
            ->sum('totalBagCount');

        return $variety_count;
    }



    public function compute_variety_total_province($deliveries, $seed_variety, $region, $province){
        $total_variety = 0;
        foreach($deliveries as $row){
            $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery as ad')
                //->where('ad.is_cancelled', "=", 0)
                ->where('ad.batchTicketNumber', "=", $row->batchTicketNumber)
                ->where('ad.seedVariety', "=", $seed_variety)
                ->where('ad.region', "=", $region)
                ->where('ad.province', "=", $province)
                ->sum('totalBagCount');
            
            $total_variety += $variety_count;
        }

        return $total_variety;
    }

    public function compute_confirmed_total_province($seed_variety, $accreditation, $region, $province){
        $variety_count = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery as cd')
            ->where('cd.seedVariety', "=", $seed_variety)
            ->where('cd.coopAccreditation', "=", $accreditation)
            ->where('cd.region', "=", $region)
            ->where('cd.province', "=", $province)
            ->where('cd.is_cancelled', "=", '0')
            ->sum('totalBagCount');

        return $variety_count;
    }

    public function coop_total_values(Request $request){
        $coop_id = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('accreditation_no', $request->coop_accre)->value('coopId');
        $commitments = DB::connection('seed_coop_db')->table('tbl_commitment')->where('coopID', $coop_id)->get();

        $confirmed_delivery_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->select('batchTicketNumber', DB::raw("SUM(totalBagCount) as total_bags"))
            ->where('tbl_delivery.is_cancelled', '=', '0')
            ->where('tbl_delivery.coopAccreditation', '=', $request->coop_accre)
            ->groupBy('batchTicketNumber')
            ->get();

        $variety_list = array();
        $commitment_list = array();
        $delivered_list = array();
        $confirmed_list   = array();

        $total_commitment = 0;
        $total_delivered = 0;
        $total_confirmed = 0;

        foreach($confirmed_delivery_list as $batch){
            $total_confirmed += $batch->total_bags;
        }

        foreach($commitments as $row){
            $variety_total = $this->compute_variety_total($confirmed_delivery_list, $row->commitment_variety);
            $confirmed_total = $this->compute_confirmed_total($row->commitment_variety, $request->coop_accre);

            array_push($variety_list, $row->commitment_variety);
            array_push($commitment_list, intval($row->commitment_value));
            array_push($delivered_list, intval($variety_total));
            array_push($confirmed_list, intval($confirmed_total));

            $total_commitment += $row->commitment_value;
            $total_delivered += $variety_total;
        }
        
        return array(
            'variety_list' => $variety_list,
            'commitment_list' => $commitment_list,
            'delivered_list' => $delivered_list,
            'total_commitment' => number_format($total_commitment),
            'total_delivered' => number_format($total_delivered),
            'total_confirmed' => number_format($total_confirmed),
            'confirmed_list' => $confirmed_list
        );
    }

    public function coop_total_values_province(Request $request){
        //$coop_id = DB::connection('seed_coop_db')->table('tbl_cooperatives')->where('accreditation_no', $request->coop_accre)->value('coopId');
        $commitments = DB::connection('seed_coop_db')->table('tbl_commitmet_per_province')
            ->where('accreditation_no', $request->coop_accre)
            ->where('region', $request->region)
            ->where('province', $request->province)
            ->get();

        $total_commitment = 0;

        $commitment_array = array();
        foreach($commitments as $c_row){
            if($c_row->nsic_rc_222 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC Rc 222",  
                    "commitment_value" => $c_row->nsic_rc_222
                ));
                $total_commitment += $c_row->nsic_rc_222;
            }
            if($c_row->nsic_rc_216 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC Rc 216",  
                    "commitment_value" => $c_row->nsic_rc_216
                ));
                $total_commitment += $c_row->nsic_rc_216;
            }
            if($c_row->nsic_rc_160 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC Rc 160",  
                    "commitment_value" => $c_row->nsic_rc_160
                ));
                $total_commitment += $c_row->nsic_rc_160;
            }
            if($c_row->nsic_rc_158 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC Rc 158",  
                    "commitment_value" => $c_row->nsic_rc_158
                ));
                $total_commitment += $c_row->nsic_rc_158;
            }
            if($c_row->nsic_rc_218 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC Rc 218",  
                    "commitment_value" => $c_row->nsic_rc_218
                ));
                $total_commitment += $c_row->nsic_rc_218;
            }
            if($c_row->nsic_rc_358 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC 2014 Rc 358",  
                    "commitment_value" => $c_row->nsic_rc_358
                ));
                $total_commitment += $c_row->nsic_rc_358;
            }
            if($c_row->nsic_rc_400 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC 2015 Rc 400",  
                    "commitment_value" => $c_row->nsic_rc_400
                ));
                $total_commitment += $c_row->nsic_rc_400;
            }
            if($c_row->nsic_rc_402 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC 2015 Rc 402",  
                    "commitment_value" => $c_row->nsic_rc_402
                ));
                $total_commitment += $c_row->nsic_rc_402;
            }
            if($c_row->nsic_rc_440 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC 2016 Rc 440",  
                    "commitment_value" => $c_row->nsic_rc_440
                ));
                $total_commitment += $c_row->nsic_rc_440;
            }
            if($c_row->nsic_rc_442 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC Rc 442",  
                    "commitment_value" => $c_row->nsic_rc_442
                ));
                $total_commitment += $c_row->nsic_rc_442;
            }
            if($c_row->nsic_rc_480 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "NSIC 2016 Rc 480",  
                    "commitment_value" => $c_row->nsic_rc_480
                ));
                $total_commitment += $c_row->nsic_rc_480;
            }
            if($c_row->psb_rc_10 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "PSB Rc 10",  
                    "commitment_value" => $c_row->psb_rc_10
                ));
                $total_commitment += $c_row->psb_rc_10;
            }
            if($c_row->psb_rc_18 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "PSB Rc 18",  
                    "commitment_value" => $c_row->psb_rc_18
                ));
                $total_commitment += $c_row->psb_rc_18;
            }
            if($c_row->psb_rc_82 != 0){
                array_push($commitment_array, array(
                    "commitment_variety" => "PSB Rc 82",  
                    "commitment_value" => $c_row->psb_rc_82
                ));
                $total_commitment += $c_row->psb_rc_82;
            }
        }

        $confirmed_delivery_list = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.tbl_delivery")
            ->select('batchTicketNumber', DB::raw("SUM(totalBagCount) as total_bags"))
            ->where('tbl_delivery.is_cancelled', '=', '0')
            ->where('tbl_delivery.coopAccreditation', '=', $request->coop_accre)
            ->where('tbl_delivery.region', '=', $request->region)
            ->where('tbl_delivery.province', '=', $request->province)
            ->groupBy('batchTicketNumber')
            ->get();

        $variety_list = array();
        $commitment_list = array();
        $delivered_list = array();
        $confirmed_list   = array();

        $total_commitment = 0;
        $total_delivered = 0;
        $total_confirmed = 0;

        foreach($commitment_array as $row){
            $variety_total = $this->compute_variety_total_province($confirmed_delivery_list, $row["commitment_variety"], $request->region, $request->province);
            $confirmed_total = $this->compute_confirmed_total_province($row["commitment_variety"], $request->coop_accre, $request->region, $request->province);

            array_push($variety_list, $row["commitment_variety"]);
            array_push($commitment_list, intval($row["commitment_value"]));
            array_push($delivered_list, intval($variety_total));
            array_push($confirmed_list, intval($confirmed_total));

            $total_commitment += $row["commitment_value"];
            $total_delivered += $variety_total;
        }


        foreach($confirmed_delivery_list as $batch){
            $total_confirmed += $batch->total_bags;
        }

        
        return array(
            'variety_list' => $variety_list,
            'commitment_list' => $commitment_list,
            'delivered_list' => $delivered_list,
            'total_commitment' => number_format($total_commitment),
            'total_delivered' => number_format($total_delivered),
            'total_confirmed' => number_format($total_confirmed),
            'confirmed_list' => $confirmed_list
        );
    }
    
    public function coop_delivery_list2(Request $request){
        $confirmed_delivery = DB::select( DB::raw("SELECT batchTicketNumber, region, province, municipality, dropOffPoint, SUM(totalBagCount) as expected_bags, deliveryDate  
            FROM rcep_delivery_inspection.tbl_delivery WHERE region != '' AND province = :province_name AND coopAccreditation = :coop_accreditaion
                GROUP BY batchTicketNumber ORDER BY deliveryDate DESC"), array(

            'province_name' => $request->province,
            'coop_accreditaion' => $request->coop_accre
        ));
        
        //check if delivery is inspected
        $inspected_arr = array();
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
                ->select('dateCreated', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actual_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->where('province', $row->province)
            ->groupBy('batchTicketNumber')
            ->first();

            //get delivery status
            $delivery_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                ->select('status')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->orderBy('deliveryStatusId', "desc")
                ->first();
            if($delivery_status->status == 0){
                $status_name = 'Pending';
            }
            else if($delivery_status->status == 1){
                $status_name = 'Passed';
            }
            else if($delivery_status->status == 2){
                $status_name = 'Rejected';
            }else if($delivery_status->status == 3){
                $status_name = 'In Transit';
            }else if($delivery_status ->status== 4){
                $status_name = 'Cancelled';
            }
            
            if(count($actual_delivery) > 0){
                $row_arr = array(
                    'region' => $row->region,
                    'province' => $row->province,
                    'municipality' => $row->municipality,
                    'dropOffPoint' => $row->dropOffPoint,
                    'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                    'actual_delivery_volume' => number_format($actual_delivery->actual_bags)." bag(s)",
                    'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                    'status' => $status_name,
                    'batchTicketNumber' => $row->batchTicketNumber
                );
                array_push($inspected_arr, $row_arr);
            }else{
                $row_arr = array(
                    'region' => $row->region,
                    'province' => $row->province,
                    'municipality' => $row->municipality,
                    'dropOffPoint' => $row->dropOffPoint,
                    'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                    'actual_delivery_volume' => "N/A",
                    'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                    'status' => $status_name,
                    'batchTicketNumber' => $row->batchTicketNumber
                );
                array_push($inspected_arr, $row_arr);
            }
        }

        $inspected_arr = collect($inspected_arr);
        return Datatables::of($inspected_arr)
        ->addColumn('action', function($row) {
            if($row['status'] == 'Cancelled' ||  $row['status'] == 'Passed' || $row['status'] == 'Rejected'){
                $msg = 'This delivery is no longer available for cancellation.';
                return "<a type='button' style='color: #fff;background-color: #9e9e9e;border-color: #969696;' class='btn btn-primary btn-sm' href='#'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }else{
                return "<a type='button' class='btn btn-danger btn-sm' href='#' data-toggle='modal' data-target='#cancel_verification_modal' data-batch='".$row["batchTicketNumber"]."'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }
        })
        ->make(true);
    }


    public function coop_delivery_list_province(Request $request){
        $confirmed_delivery = DB::select( DB::raw("SELECT batchTicketNumber, region, province, municipality, dropOffPoint, SUM(totalBagCount) as expected_bags, deliveryDate  
            FROM rcep_delivery_inspection.tbl_delivery WHERE region != '' AND province = :province_name AND region = :region_name AND coopAccreditation = :coop_accreditation
                GROUP BY batchTicketNumber ORDER BY deliveryDate DESC"), array(

            'province_name' => $request->province,
            'region_name' => $request->region,
            'coop_accreditation' => $request->coop_accre
        ));
        
        //check if delivery is inspected
        $inspected_arr = array();
        foreach($confirmed_delivery as $row){
            $actual_delivery = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                ->select('dateCreated', DB::raw('SUM(tbl_actual_delivery.totalBagCount) as actual_bags'))
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->where('province', $row->province)
            ->groupBy('batchTicketNumber')
            ->first();

            //get delivery status
            $delivery_status = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_delivery_status')
                ->select('status')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->orderBy('deliveryStatusId', "desc")
                ->first();

            /*if($delivery_status->status == 0){
                $status_name = 'Pending';
            }
            else if($delivery_status->status == 1){
                $status_name = 'Passed';
            }
            else if($delivery_status->status == 2){
                $status_name = 'Rejected';
            }else if($delivery_status->status == 3){
                $status_name = 'In Transit';
            }else if($delivery_status ->status== 4){
                $status_name = 'Cancelled';
            }*/

            if($delivery_status->status == $request->delivery_status){

                if($request->delivery_status == 0){
                    $status_name = 'Pending';
                }
                else if($request->delivery_status == 1){
                    $status_name = 'Passed';
                }
                else if($request->delivery_status == 2){
                    $status_name = 'Rejected';
                }else if($request->delivery_status == 3){
                    $status_name = 'In Transit';
                }else if($request->delivery_status== 4){
                    $status_name = 'Cancelled';
                }

                if(count($actual_delivery) > 0){
                    $row_arr = array(
                        'region' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'dropOffPoint' => $row->dropOffPoint,
                        'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                        'actual_delivery_volume' => number_format($actual_delivery->actual_bags)." bag(s)",
                        'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                        'status' => $status_name,
                        'batchTicketNumber' => $row->batchTicketNumber
                    );
                    array_push($inspected_arr, $row_arr);
                }else{
                    $row_arr = array(
                        'region' => $row->region,
                        'province' => $row->province,
                        'municipality' => $row->municipality,
                        'dropOffPoint' => $row->dropOffPoint,
                        'expected_delivery_volume' => number_format($row->expected_bags)." bag(s)",
                        'actual_delivery_volume' => "N/A",
                        'delivery_date' => date("m-d-Y", strtotime($row->deliveryDate)),
                        'status' => $status_name,
                        'batchTicketNumber' => $row->batchTicketNumber
                    );
                    array_push($inspected_arr, $row_arr);
                }
            }
        }

        $inspected_arr = collect($inspected_arr);
        return Datatables::of($inspected_arr)
        ->addColumn('action', function($row) {
            if($row['status'] == 'Cancelled' ||  $row['status'] == 'Passed' || $row['status'] == 'Rejected' || $row['status'] == 'In Transit'){
                return "<a type='button' style='color: #fff;background-color: #9e9e9e;border-color: #969696;' class='btn btn-primary btn-sm' href='#'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }else{
                return "<a type='button' class='btn btn-danger btn-sm' href='#' data-toggle='modal' data-target='#cancel_verification_modal' data-batch='".$row["batchTicketNumber"]."'><i class='fa fa-trash'></i> Cancel Delivery</a>";
            }
        })
        ->make(true);
    }
	
	/**
	*  02-09-2021
	**/
	public function coop_report_home(){
		$user_tag = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->first();
		$coop_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $user_tag->coopAccreditation)->first();
		return view('dashboard.coop_operator.report')
			->with('coop_details', $coop_details);
	}
	
	public function coop_report_sgList(Request $request){
		return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')
			->select(DB::raw('COUNT(sg_id) as total_tags'), 'sg_name', 'sg_id')
			->where('coopAccreditation', $request->coop_accreditation)
			->where('moaNumber', $request->moa_number)
            ->groupBy('sg_id')
        )
		->addColumn('variety_list', function($row){
            $v_list = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_rla_details')->where('sg_id', $row->sg_id)->groupBy('seedVariety')->get();
			
			$v_str = "";
			foreach($v_list as $v_row){
				$v_str .= $v_row->seedVariety.", ";
			}
			
			return rtrim($v_str,", ")." [".$row->total_tags." seedtags(s)]";
        })->make(true);
	}
	
	//02-19-2021
	public function SGEnrollment(){
		$tagged_accreditation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->value('coopAccreditation');
		$total_sg_enrolled = count(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('is_active', 0)->get());
		$total_sg_area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('is_active', 0)->sum('area');
		
		return view('dashboard.coop_operator.enroll')
			->with('tagged_accreditation', $tagged_accreditation)
			->with('total_sg_enrolled', $total_sg_enrolled)
			->with('total_sg_area', $total_sg_area);
	}
	
	public function SGEnrollmentConfirm(Request $request){
		$sg_accreditation = substr_replace($request->coop_accreditation,"",-5).$request->sg_accreditation;
		
		//check if accreditation number is already saved
		$check_accreditation = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('accreditation_number', $sg_accreditation)->get();
		if(count($check_accreditation) > 0){
			return json_encode("accreditation_no_exists");
		}else{
			DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
			->insert([
				'first_name' => $request->first_name,
				'middle_name' => $request->middle_name,
				'last_name' => $request->last_name,
				'extension_name' => $request->extension_name,
				'accreditation_number' => $sg_accreditation,
				'coop_accreditation_number' => $request->coop_accreditation,
				'area' => $request->area
			]);
			
			return json_encode("insert_success");
		}
	}
	
	public function SGEnrollmentTable(Request $request){
		return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
			->where('coop_accreditation_number', $request->coop_accreditation)
			->orderBy('date_recorded', 'DESC')
        )
		->addColumn('action', function($row){
            $edit_btn = "<a href='#' data-id='$row->id' data-toggle='modal' data-target='#edit_member_modal' class='btn btn-warning btn-xs'><i class='fa fa-edit'></i></a>";
			$delete_btn = "<button class='btn btn-danger btn-xs' onclick='deleteSG($row->id)'><i class='fa fa-trash'></i></button>";
			return $edit_btn.$delete_btn;
        })->make(true);
	}
	
	public function SGEnrollmentSUmmary(Request $request){
		$total_sg_enrolled = count(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('is_active', 0)->get());
		$total_sg_area = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('is_active', 0)->sum('area');
		
		return array(
			"total_sg_enrolled" => $total_sg_enrolled,
			"total_sg_area" => $total_sg_area
		);
	}
	
	public function SGEnrollmentDelete(Request $request){
		DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('id', $request->coop_member_id)->delete();
	}
	
	public function SGMatrix(Request $request){
		$tagged_accreditation = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users_coop')->where('userId', Auth::user()->userId)->value('coopAccreditation');
		$coop_id = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')->where('accreditation_no', $tagged_accreditation)->value('coopId');
		$required_commitments = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
			->select(DB::raw('SUM(commitment_value) as total_value'), 'commitment_variety')
			->where('coopID', $coop_id)
			->where('variety_status', '!=', 0)
			->groupBy('commitment_variety')
			->get();
			
		return view('dashboard.coop_operator.matrix')
			->with('required_commitments', $required_commitments)
			->with('tagged_accreditation', $tagged_accreditation);
	}
	
	public function SGEnrollmentEditDetails(Request $request){
		$member_details = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('id', $request->coop_member_id)->first();
		return array(
			"first_name" => $member_details->first_name,
			"middle_name" => $member_details->middle_name,
			"last_name" => $member_details->last_name,
			"extension_name" => $member_details->extension_name,
			"accreditation_number" => substr($member_details->accreditation_number, -5),
			"area" => $member_details->area
		);
	}
	
	public function SGEnrollmentEditConfirm(Request $request){
		DB::beginTransaction();
        try {
			$coop_accreditation = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')->where('id', $request->member_id)->value('coop_accreditation_number');
			$sg_accreditation = substr_replace($coop_accreditation,"",-5).$request->sg_accreditation;
			
			DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
			->where('id', $request->member_id)
			->update([
				'first_name' => $request->first_name,
				'middle_name' => $request->middle_name,
				'last_name' => $request->last_name,
				'extension_name' => $request->extension_name,
				'accreditation_number' => $sg_accreditation,
				'area' => $request->area
			]);
			DB::commit();
			return json_encode("update_success");

        } catch (\Exception $e) {
            DB::rollback();
			return json_encode("update_failed");
        }
	}
	
	public function SGMatrixTable(Request $request){
		return Datatables::of(DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
			->where('coop_accreditation_number', $request->coop_accreditation)
			->orderBy('date_recorded', 'DESC')
        )
		->addColumn('action', function($row){
			
            $view_btn = "<a href='#' data-id='$row->id'
				data-fname='$row->first_name'
				data-mname='$row->middle_name'
				data-lname='$row->last_name'
				data-accre='$row->accreditation_number'
				data-coopaccre='$row->coop_accreditation_number'
				data-area='$row->area'
			data-toggle='modal' data-target='#show_allocation_modal' class='btn btn-warning btn-xs'><i class='fa fa-edit'></i></a>";
			
			
			
			//$delete_btn = "<button class='btn btn-danger btn-xs' onclick='deleteSG($row->id)'><i class='fa fa-trash'></i></button>";
			$delete_btn = "";
			return $view_btn.$delete_btn;
        })->make(true);
	}
	
	public function SGGetVariety(Request $request){
			$tbl_cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
						->select('coopId')
						->where('accreditation_no', $request->coopaccre)
						->first();
						
			$tbl_commitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
						->select('id', 'commitment_variety', DB::raw('sum(commitment_value) as commitment_value'))
						->where('coopID', $tbl_cooperatives->coopId)
						->groupBy('commitment_variety')
						->get();

				$ret = array();
				foreach ($tbl_commitment as $commitment){
                
                    $checkAllMemberCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
                        ->where("commitment_variety", $commitment->commitment_variety)
                        ->where("coop_accreditation_number", $request->coopaccre)
                        ->sum('allocated_area');

                    if(count($checkAllMemberCommitment)>0){
                        $allMemberCommitment = $checkAllMemberCommitment * 200;
                        $availableBags =  $commitment->commitment_value - $allMemberCommitment;
                    }else{
                        $availableBags = $commitment->commitment_value;
                    }


                    $checkMemberArea = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
                        ->select(DB::raw('sum(allocated_area) as AllocatedArea'))
                        ->where("accreditation_number", $request->accre)
                        ->first();
                        //dd($checkMemberArea);

                    if(count($checkMemberArea)>0){
                        $availableArea = $request->area - round($checkMemberArea->AllocatedArea,2);
                    }else{
                        $availableArea = $request->area;
                    }
                  //  dd(round($checkMemberArea->AllocatedArea,2));


					$checkMemberCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
						->where("commitment_variety", $commitment->commitment_variety)
                        ->where("accreditation_number", $request->accre)
                        ->where("coop_accreditation_number", $request->coopaccre)
						->first();

						if(count($checkMemberCommitment)>0){
                            $memberCommit = round($checkMemberCommitment->allocated_area,2).' (ha)';
                            $allocationId = $checkMemberCommitment->id;
                            $withData = true;
						}else{
	                        $withData = false;
                            $memberCommit = 0;
                            $allocationId = 0;
                		}
				
					$btn_array = array(
                    "seed_variety" => $commitment->commitment_variety,
                    "withData" => $withData,
                    "availableBags" => $availableBags,
                    "availableArea" => $availableArea,
                    "commitment_id" => $commitment->id,
                    "memberCommit" => $memberCommit,
                    "allocationId" => $allocationId
					);
				
					array_push($ret,$btn_array);
				
				}
				
			echo json_encode($ret);	
	}


    public function SGconfirm(Request $request){
        $tbl_cooperatives = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives')
                        ->select('coopId')
                        ->where('accreditation_no', $request->coopaccre)
                        ->first();
                        
        $sum_commitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment')
                        ->where('coopID', $tbl_cooperatives->coopId)
                        ->where('commitment_variety', $request->variety)
                        ->sum('commitment_value');


        $checkAllMemberCommitment = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
                        ->where("commitment_variety", $request->variety)
                        ->where("coop_accreditation_number", $request->coopaccre)
                        ->sum('allocated_area');

                    if(count($checkAllMemberCommitment)>0){
                        $allMemberCommitment = $checkAllMemberCommitment * 200;
                        $availableBags =  $sum_commitment - $allMemberCommitment;
                    }else{
                        $availableBags = $sum_commitment;
                    }


        $sumArea =DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_cooperatives_members')
                    ->select('area')
                    ->where('id', $request->memberid)
                    ->first();

        $checkMemberArea = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
                    ->where("accreditation_number", $request->memberaccre)
                    ->sum('allocated_area');
                        //dd($checkMemberArea);
                    if(count($checkMemberArea)>0){
                        $availableArea = $sumArea->area - round($checkMemberArea,2);
                    }else{
                        $availableArea = $sumArea->area;
                    }
                    //dd(round($checkMemberArea,2));


                    $ins = array();
            if($availableBags>0 OR $availableArea>0){
                //SAVE
                    DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
                        ->insert([
                            "member_id" => $request->memberid,
                            "first_name"  => $request->fname,
                            "middle_name"  => $request->mname,
                            "last_name"  => $request->lname,
                            "accreditation_number"  => $request->memberaccre,
                            "allocated_area"  => $request->inputArea,
                            "commitment_variety"  => $request->variety,
                            "coop_accreditation_number"  => $request->coopaccre,
                            "ref_id"  => $request->commitmentid,                               
                            ]);
                    echo  json_encode("success");                   
            }else{

                echo json_encode("exceeds");


            }
    }


     public function SGremove(Request $request){
     
            if((isset($request->id))) {
                //SAVE
                    DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_member')
                        ->where('id', $request->id)
                        ->delete();
                    echo  json_encode("success");                   
            }else{

                echo json_encode("exceeds");


            }
	
	
	
	}	

}


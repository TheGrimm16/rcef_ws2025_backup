<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Inspmonitoring;
use Auth;
use Yajra\Datatables\Datatables;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use DB;
use Session;
use Excel;

class InspmonitoringController extends Controller {

    public function index() {

        $model = new Inspmonitoring();
        
        if(Auth::user()->roles->first()->name == "rcef-programmer" || Auth::user()->username == "rm.capiroso" || Auth::user()->username == "kruz" || Auth::user()->username == "jc.tizon"|| Auth::user()->username == "ddc.espiritu" || Auth::user()->username == "renaida_pascual" || Auth::user()->username == "arfulgencio"){
            $inspected_provinces = $model->_inspected_provinces();
        }else{
            $inspected_provinces = $model->_inspected_provinces_filtered();
        }

        return view('inspmonitoring.index')
                        ->with(compact('inspected_provinces'));
    }

    public function get_muni($province) {
        // Get municipalities
        $model = new Inspmonitoring();
        $inspected_municipalities = $model->_inspected_municipalities($province);

        echo json_encode($inspected_municipalities);
    }

    public function get_dropoff_points($province, $municipality) {
        // Get dropoff points
        $model = new Inspmonitoring();
        $inspected_dropoff = $model->_inspected_dropoff($province, $municipality);

        echo json_encode($inspected_dropoff);
    }

    public function get_batch_details_forMonitoring(Request $request){
        $batch_number = $request->batch_number;

        $actual_delivery_data = DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->select(DB::raw("*, SUM(totalBagCount) as total_deliveryBags"))
            ->where('batchTicketNumber', $request->batch_number)
            ->groupBy("batchTicketNumber")
            ->first();

        if(count($actual_delivery_data) > 0){
            $actual_delivery_status = $actual_delivery_data->total_deliveryBags;
        }else{
            $actual_delivery_status = "no_actual_delivery";
        }
        
        return array(
            "batch_number" => $batch_number,
            "actual_delivery_status" => $actual_delivery_status
        );
    }

    public function get_actual_delivery_forMonitoring(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_actual_delivery')
            ->where('batchTicketNumber', $request->batch_number)
        )
        ->make(true);
    }

    public function get_sample_details_forMonitoring(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_sampling')
            ->where('batchTicketNumber', $request->batch_number)
            ->orderBy('dateSampled', 'DESC')
        )->addColumn('seed_weight_value', function($table_data) {
          $float_seed_weight = (float) $table_data->bagWeight;
          return number_format($float_seed_weight,2);  
        })
        ->make(true);
    }

    public function upload_signed_iar(Request $request){
        $this->validate($request, [
            'input_img' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf',
        ]);
    
        if ($request->hasFile('input_img')) {
            $image = $request->file('input_img');
            
			$filename = $_FILES["input_img"]["name"];
            $ext = end((explode(".", $filename)));

            //$name = time().'.'.$image->getClientOriginalExtension();
            //$name = "SIGNED_IAR_".$request->batch_iar_number."_".time().'.'.$image->getClientOriginalExtension();
            $name = "SIGNED_IAR_".$request->batch_iar_number."_".time().'.'.$ext;
            $destinationPath = public_path("iar");
            $image->move($destinationPath, $name);

            //check if the file is uploaded successfully...
            DB::connection('delivery_inspection_db')->table('iar_upload_logs')
            ->insert([
                'original_file_name' => $filename,
                //'original_file_name' => $image->getClientOriginalName(),
                'new_file_name' => $name,
                'batch_number' => $request->batch_iar_number,
                //'file_extension' => $image->getClientOriginalExtension(),
                'file_extension' => $ext,
                'uploader' => Auth::user()->username
            ]);
			
			//insert to system logs
             DB::connection('mysql')->table('lib_logs')
             ->insert([
                 'category' => 'UPLOAD_SIGNED_IAR',
                 'description' => 'Uploaded a signed IAR (scanned copy) for batch ticket #: '.$request->batch_iar_number,
                 'author' => Auth::user()->username,
                 'ip_address' => $_SERVER['REMOTE_ADDR']
             ]);

            Session::flash("success", "you have successfully uploaded an IAR for (".$request->batch_iar_number.")");
            return redirect()->route('monitoring_insp.index');
            
        }else{
            echo "error";
        }
    }

    public function reupload_signed_iar(Request $request){
        $this->validate($request, [
            're_input_img' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf',
        ]);
    
        if ($request->hasFile('re_input_img')) {
            $image = $request->file('re_input_img');
            //$name = time().'.'.$image->getClientOriginalExtension();
            $name = "SIGNED_IAR_".$request->re_batch_iar_number."_".time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path("iar");
            $image->move($destinationPath, $name);

            //check if the file is uploaded successfully...
            DB::connection('delivery_inspection_db')->table('iar_upload_logs')
            ->where('batch_number', $request->re_batch_iar_number)
            ->update([
                'original_file_name' => $image->getClientOriginalName(),
                'new_file_name' => $name,
                'file_extension' => $image->getClientOriginalExtension(),
                'uploader' => Auth::user()->username
            ]);
			
			//insert to system logs
            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'RE-UPLOAD_SIGNED_IAR',
                'description' => 'Re-uploaded a signed IAR (scanned copy) for batch ticket #: '.$request->re_batch_iar_number,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            Session::flash("success", "you have successfully re-uploaded an IAR for (".$request->re_batch_iar_number.")");
            return redirect()->route('monitoring_insp.index');
            
        }else{
            echo "error";
        }
    }

    public function table_data(Request $request) {
        $model = new Inspmonitoring();
        $input = $request->all();
        
        $batches = $model->_get_batches($input['drop_id'],$input["prv_name"]);
        $table_data = array();
        $i = 1;

        $status_chk = '';
        foreach ($batches as $dv) {
            $is_replacement = 0;
            $is_partial_replacement =0;
            if($dv->isBuffer == 9){
                $is_replacement = $model->_isReplacement($dv->batchTicketNumber);     
            }
            

            $check_reject = $model->_get_reject_status($dv->batchTicketNumber);

            if($check_reject == 3){
                $get_status = $model->_get_status($dv->batchTicketNumber);
                $badge_status = $get_status;    
            }else{
                $badge_status = $check_reject;
            }
            
            $coop_details = $model->_coop_details($dv->coopAccreditation);
            $inspector_details = $model->_inspector_details($dv->batchTicketNumber, 1);
            $inspector_count = $model->_inspector_details($dv->batchTicketNumber, 2);
            $inspection_details = $model->_inspection_details($dv->batchTicketNumber,1);
            $inspection_details_count = $model->_inspection_details($dv->batchTicketNumber,2);
            $varieties = $model->_batch_varieties($dv->batchTicketNumber);

             //NEW MODEL
            $partialTransfer = $model->_get_partial_list($dv->batchTicketNumber);
            //$prevTransfer = $model->_get_pushed_data($dv->batchTicketNumber);
            $prevTransfer = null;
            //$release_count = $model->_get_release_data($dv->farmer_id, $dv->rsbsa_control_no, "released", 1);
            if($badge_status == 0){
                $status = '<span class="badge badge-primary" style="color:#fff;">Pending</span>';
                $status_chk = '';
            }
            else if($badge_status == 1){
                $status = '<span class="badge badge-success" style="color:#fff;">Passed</span>';
                $status_chk = 'Passed';
            }
            else if($badge_status == 2){
                $status = '<span class="badge badge-danger" style="color:#fff;">Rejected</span>';
                $status_chk = 'Rejected';
            }else if($badge_status == 3){
                $status = '<span class="badge badge-warning" style="color:#fff;">In transit</span>';
                $status_chk = '';
            }else if($badge_status == 4){
                $status = '<span class="badge badge-dark" style="color:#fff;">Cancelled</span>';
                $status_chk = '';
            }



             $insp_data =  number_format($inspection_details->inspected);

             if($inspection_details->qrValStart != ""){
                $status .= '<span class="badge badge-warning" style="color:#fff;">Binhi e-Padala</span>';
             }


             $g = 0;
             $g += intval($inspection_details->inspected);
             if(count($partialTransfer)>0){
                $prv_arr = array();
                $prv_muni = array();
                if($dv->isBuffer == 1){
                 $is_partial_replacement = $model->_isPartialReplacement($dv->batchTicketNumber);     
                }



                   // dd($partialTransfer);
                    foreach ($partialTransfer as $partialData) {
                         
                            $partialBagCount = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.tbl_actual_delivery')
                                    ->where("batchTicketNumber", $partialData->batchTicketNumber)
                                    ->where("prv_dropoff_id", $partialData->prv_dropoff_id)
                                    ->sum("totalBagCount");
                                if($partialBagCount >0){}else{$partialBagCount=0;}
                                
                                if(!isset($prv_arr[$partialData->prv_dropoff_id])){
                                    $prv_arr[$partialData->prv_dropoff_id] = $partialBagCount;
                                    $prv_muni[$partialData->prv_dropoff_id] = $partialData->municipality;
                                }else{
                                     $prv_arr[$partialData->prv_dropoff_id] =  $prv_arr[$partialData->prv_dropoff_id] + $partialBagCount;
                                }                               
                                
                                $g += intval($partialBagCount);
                    }       

           
                foreach ($prv_arr as $key => $value) {
                         $status_partial = '<span class="badge badge-info" style="color:#fff;">Transferred</span>';
                         $status_chk_partial = 'transfered';
                 $insp_data .="<br>".number_format($value)."     ".$status_partial." <font size=2> (".$prv_muni[$key].") </font>";
            }



            }



            

           

            if(count($prevTransfer)>0){
                  //  dd(count($prevTransfer));
                    foreach ($prevTransfer as $prevData) {
                        $totalPrev = 0;
                         $status_partial = '<span class="badge badge-warning" style="color:#fff;">WS2021</span>';
                         $status_chk_partial = 'transfered';
                            //WS2021
                            $con = $model->set_rpt_db("ls_rcep_transfers_db","rcep_delivery_inspection","172.16.10.25","4409","rcef_web","SKF9wzFtKmNMfwy");
                            if($con=="Connected"){                            
                                $PrevBagCount = DB::connection("ls_rcep_transfers_db")->table("tbl_actual_delivery")
                                        ->where("remarks", $prevData->remarks)
                                        //->where("prv_dropoff_id", $prevData->prv_dropoff_id)
                                        ->sum("totalBagCount");
                               // dd($PrevBagCount);
                                $prevPartialCount = DB::connection("ls_rcep_transfers_db")->table("tbl_actual_delivery")
                                         ->where('remarks', "LIKE", '%transferred from batch: '.$prevData->batchTicketNumber.'%')
                                         ->sum("totalBagCount");
                                if($prevPartialCount>0){$totalPrev = $prevPartialCount;}else{$totalPrev=0;}
                                    if($PrevBagCount >0){$totalPrev += $PrevBagCount;}
                                    $insp_data .= "<br>".number_format($totalPrev)."     ".$status_partial;
                                      $g += intval($totalPrev);
                                $con = $model->set_rpt_db("ls_rcep_transfers_db","rcep_transfers_ws","172.16.10.25","4406","rcef_user","SKF9wzFtKmNMfwyz");
                            }
                    }    
                }

                if($g>intval($inspection_details->inspected)){
                    $insp_data .= "<br> <b> Total:  ".number_format($g)."</b>";
                }


                if($is_replacement == 1 || $is_partial_replacement == 1){
                    $status .= '<span class="badge badge-info" style="color:#fff;">Replacement</span>';
                }

          

            $data_search = array(
                'number' => $i,
                'batch' => $dv->batchTicketNumber,
                'coop' => $coop_details->coopName,
                'inspector' => ($inspector_count > 0 ? $inspector_details->firstName . ' ' . $inspector_details->lastName : "Not yet assigned"),
                'variety' => $varieties,
                'confirmed' => number_format($dv->confirmed),
                'status' => $status,
                'status_chk' => $status_chk,
                'inspected' => ($badge_status == 4 ? '<p class="badge badge-danger" style="color:#fff;">Cancelled</p>' : ($inspection_details_count > 0 ? $insp_data : '<p class="badge badge-warning" style="color:#000;">Waiting for inspection data</p>')),
                'date_confirmed' => $dv->deliveryDate,
                'date_inspected' => ($inspection_details_count > 0 ? $inspection_details->dateCreated : "Not yet inspected")
            );
            array_push($table_data, $data_search);
            $i++;
        }

        $table_data = collect($table_data);

        return Datatables::of($table_data)
            ->addColumn('action', function($table_data) {
                
                if($table_data["status_chk"] == "Passed" || $table_data["status_chk"] == "Rejected"){
                    //check if batch has an uploaded IAR file in server
                    $upload_logs = DB::connection("delivery_inspection_db")->table("iar_upload_logs")
                        ->where("batch_number", $table_data['batch'])
                        ->first();

                    if(count($upload_logs) > 0){
                        $url = asset('public/iar/'.$upload_logs->new_file_name);
						if(Auth::user()->roles->first()->name == "accountant"){
							return '
								<a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a><br>
								<a href="'.$url.'" target="_blank" for="' . $table_data['batch'] . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> View IAR</a><br>
								<a for="' . $table_data['batch'] . '" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#inspection_ReUploadIAR_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-refresh"></i> Replace Uploaded IAR</a>
							';
						}else{
							return '
								<a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a><br>
								<a href="'.$url.'" target="_blank" for="' . $table_data['batch'] . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> View IAR</a>
							';
						}
                        
                        /*return '
                            <a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a>
                        ';*/ 
                    }else{
						if(Auth::user()->roles->first()->name == "accountant"){
							return '
								<a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a><br>
								<a for="' . $table_data['batch'] . '" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#inspection_uploadIAR_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-upload"></i> Upload Signed IAR </a>
							';
						}elseif(Auth::user()->roles->first()->name == "rcef-programmer" ||Auth::user()->roles->first()->name == "branch-it"){
                            $btn =  '<a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a>';
                            $btn .= '<a class="btn btn-danger btn-xs " onclick="resetDelivery('."'".$table_data['batch']."'".');" ><i class="fa fa-window-close"></i> Revert Inspection Report </a>';
                            
                            return $btn;   
                        }
                        else{
							return '
								<a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a>
							';
						}
                        
                        /*return '
                            <a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a>
                        ';*/ 
                    }
                                      
                }else{
                    return '<a for="' . $table_data['batch'] . '" class="btn btn-primary btn-xs view_batchdetails" data-toggle="modal" data-target="#inspection_details_modal" data-id="' . $table_data['batch'] . '"><i class="fa fa-eye"></i> View Details </a>';
                }
                
            })
            ->make(true);
    }



    function inspection_data_transferred_sheet($batches){
        $model = new Inspmonitoring();

        $table_data = array();
        $total_bags = 0;

        foreach ($batches as $dv) {
            $varieties = $model->_batch_varieties_inspectionDataTransferred($dv->prv_dropoff_id);
            //compute values
            $total_bags += $dv->total_bags;

            $data_search = array(
                'Region' => $dv->region,
                'Province' => $dv->province,
                'Municipality' => $dv->municipality,
                'Dropoff Point' => $dv->dropOffPoint,
                'Seed Variety' => $varieties,
                'TOTAL Bags' => number_format($dv->total_bags),
                //'Date Transferred' => date("F j, Y g:i A", strtotime($dv->dateCreated))
            );
            array_push($table_data, $data_search);
        }

        $total_data = array(
            'Region' => '',
            'Province' => '',
            'Municipality' => '',
            'Dropoff Point' => '',
            'Seed Variety' => 'TOTAL: ',
            'TOTAL Bags' => number_format($total_bags),
            //'Date Transferred' => ''
        );
        array_push($table_data, $total_data);

        return $table_data;
    }

    function inspection_data_actual_delivery_sheet($batches){
        $model = new Inspmonitoring();

        $table_data = array();
        $total_confirmed = 0;
        $total_inspected = 0;

        foreach ($batches as $dv) {
            $inspector_details = $model->_inspector_details($dv->batchTicketNumber, 1);
            $inspector_count = $model->_inspector_details($dv->batchTicketNumber, 2);
            $inspection_details = $model->_inspection_details($dv->batchTicketNumber,1);
            $inspection_details_count = $model->_inspection_details($dv->batchTicketNumber,2);
            $varieties = $model->_batch_varieties_inspectionData($dv->batchTicketNumber);
            
            $tbl_delivery_base = DB::connection('delivery_inspection_db')
                ->table('tbl_delivery')
                ->select('batchTicketNumber', 'coopAccreditation', 'deliveryDate')
                ->where('batchTicketNumber', $dv->batchTicketNumber)
                ->groupBy('batchTicketNumber')
                ->first();

            if(count($tbl_delivery_base) > 0){
                $coop_name = DB::connection('seed_coop_db')->table('tbl_cooperatives')->select('coopName')
                    ->where('accreditation_no', $tbl_delivery_base->coopAccreditation)
                    ->value('coopName');  
                $delivery_date = date("F j, Y g:i A", strtotime($tbl_delivery_base->deliveryDate));
            }else{
                $coop_name = 'TBL_DELIVERY Data does not exist.';
                $delivery_date = 'TBL_DELIVERY Data does not exist.';
            }
            
            //compute values
            $total_confirmed += $dv->confirmed;
            $total_inspected += $inspection_details->inspected;

            $data_search = array(
                'Region' => $dv->region,
                'Province' => $dv->province,
                'Municipality' => $dv->municipality,
                'Dropoff Point' => $dv->dropOffPoint,
                'Batch Ticket #' => $dv->batchTicketNumber,
                'Seed Cooperative' => $coop_name,
                'Assigned Inspector' => ($inspector_count > 0 ? $inspector_details->firstName . ' ' . $inspector_details->lastName : "Not yet assigned"),
                'Seed Variety' => $varieties,
                'TOTAL Bags' => number_format($dv->confirmed),
                'Date Confirmed' => $delivery_date,
                'Date Inspected' => ($inspection_details_count > 0 ? date("F j, Y g:i A", strtotime($inspection_details->dateCreated)) : "Not yet inspected")
            );
            array_push($table_data, $data_search);
        }

        $total_data = array(
            'Region' => '',
            'Province' => '',
            'Municipality' => '',
            'Dropoff Point' => '',
            'Batch Ticket #' => '',
            'Seed Cooperative' => '',
            'Assigned Inspector' => '',
            'Seed Variety' => 'TOTAL:',
            'Confirmed' => number_format($total_confirmed),
            'Date Confirmed' => '',
            'Date Inspected' => ''
        );
        array_push($table_data, $total_data);

        return $table_data;
    }


    public function inspection_data(Request $request) {
        $model = new Inspmonitoring();
        $input = $request->all();

        $batches = $model->_get_inspection_data();
        $transferred_batches = $model->_get_transferred_inspection_data();

        $table_data = $this->inspection_data_actual_delivery_sheet($batches);
        $transferred_data = $this->inspection_data_transferred_sheet($transferred_batches);

        //$table_data = collect($table_data);
        $myFile = Excel::create('INSPECTION_DATA', function($excel) use ($table_data, $transferred_data) {
            $excel->sheet('INSPECTION DATA', function($sheet) use ($table_data) {
                $sheet->fromArray($table_data);
            });

            $excel->sheet('TRANFERRED DATA', function($sheet) use ($transferred_data) {
                $sheet->fromArray($transferred_data);
            });
        });

        $myFile = $myFile->string('xlsx');
        $response = array(
            'name' => "GENERATED_INSPECTION_DATA"."_".date("Y-m-d H:i:s").".xlsx",
            'file' => "data:application/vmd.openxmlformats-officedocument.spreadsheet.spreadsheetml.sheet;base64,".base64_encode($myFile)
        );

        return response()->json($response);
        
    }


    public function coop_data(Request $request)
    {
        $prv_dropoff_id = $request->myData['drop_id'];
        $season = $GLOBALS['season_prefix'];

        $pythonPath = 'C://Users//Administrator//AppData//Local//Programs//Python//Python312//python.exe';
		// $pythonPath = 'C://Users//bmsdelossantos//AppData//Local//Programs//Python//Python311//python.exe';

		$scriptPath = base_path('app//Http//PyScript//inspMonitoring//getCoopData.py');

		$escapedPrv_dropoff_id = escapeshellarg($prv_dropoff_id);
		$escapedSeason = escapeshellarg($season);

		$command = "$pythonPath \"$scriptPath\" $escapedSeason $escapedPrv_dropoff_id";
		
		$process = new Process($command);

		try {
			$process->mustRun();
			$data = $process->getOutput();
			if (strlen($data) > 5)
			{
                $data = json_decode($data);
                $finalData = collect($data);
                
                // dd($finalData);
                return Datatables::of($finalData)
                ->make(true);
			}
			else
			{
                $data = [];
                $finalData = collect($data);
                
                // dd($finalData);
                return Datatables::of($finalData)
                ->make(true);
			}
		} catch (ProcessFailedException $exception) {
			echo $exception->getMessage();
		}
    }

}

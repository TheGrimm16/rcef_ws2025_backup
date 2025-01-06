<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Session;
use Auth;
use Hash;

use App\SeedInspector;
use App\SeedDelivery;
use App\InspectionSchedule;
use Yajra\Datatables\Facades\Datatables;

class InspectionController extends Controller
{
    public function index(){
        return view('inspection.index');
    }

    public function InspectorProfile(){
        return view('inspection.profile');
    }

    public function InspectorDetails(Request $request){
        $hris_data = DB::connection('hris_db')->table('employees')
            ->select('emp_idno', 'emp_lname', 'emp_fname', 'emp_mname', 
                     'emp_extname', 'emp_fullname', 'emp_status', 'emp_email_official',
                     'emp_position', 'emp_station', 'emp_division', 
                     'emp_office', 'emp_unit', 'emp_is_active')
            ->where('emp_idno', '=', $request->id_number)
            ->where('emp_is_active', '=',   1)
            ->first();
        
        if($hris_data->emp_status == '11001'){
            $employment_status = "PERMANENT";
        }else{
            $employment_status = "SC";
        }

        if($hris_data->emp_position == 0 OR $hris_data->emp_position == ""){
            $position = "N/A";
        }else{
            $position = DB::connection('hris_db')->table('lib_positions')
                ->select('id_position', 'position_name', 'position_abbr')
                ->where('id_position', '=', $hris_data->emp_position)
                ->first()->position_name;
        }

        if($hris_data->emp_station == 0 OR $hris_data->emp_station == ""){
            $station = "N/A";
        }else{
            $station = DB::connection('hris_db')->table('lib_stations')
                ->select('id_station', 'station_name', 'station_abbr')
                ->where('id_station', '=', $hris_data->emp_station)
                ->first()->station_name;
        }

        if($hris_data->emp_office == 0 OR $hris_data->emp_office == ""){
            $office = "N/A";
        }else{
            $office = DB::connection('hris_db')->table('lib_offices')
                ->select('id_office', 'office_name', 'office_abbr')
                ->where('id_office', '=', $hris_data->emp_office)
                ->first()->office_name;
        }

        if($hris_data->emp_division == 0 OR $hris_data->emp_division == ""){
            $division = "N/A";
        }else{
            $division = DB::connection('hris_db')->table('lib_divisions')
                ->select('id_division', 'division_name', 'division_abbr')
                ->where('id_division', '=', $hris_data->emp_division)
                ->first()->division_name;
        }

        if($hris_data->emp_unit == 0 OR $hris_data->emp_unit == ""){
            $unit = "N/A";
        }else{
            $unit = DB::connection('hris_db')->table('lib_units')
                ->select('id_unit', 'unit_name', 'unit_abbr')
                ->where('id_unit', '=', $hris_data->emp_unit)
                ->first()->unit_name;
        }

        //get sex
        $sex = DB::connection('hris_db')->table('employees_personal_info')
            ->select('emp_idno', 'emp_idno', 'emp_gender')
            ->where('emp_idno', '=', $request->id_number)
            ->first()->emp_gender;
        
        $data_arr = array(
            "firstName" => $hris_data->emp_fname,
            "middleName" => $hris_data->emp_mname,
            "lastName" => $hris_data->emp_lname,
            "extName" => $hris_data->emp_extname,
            "email" => $hris_data->emp_email_official,
            "position" => $position,
            "station" => $station,
            "office" => $office,
            "division" => $division,
            "unit" => $unit,
            "sex" => $sex,
            "employment_status" => $employment_status
        );

        return $data_arr;
    }

    public function DesignateInspector($ticketNumber){        
        $delivery_details = DB::connection('delivery_inspection_db')->table('tbl_delivery')->where('ticketNumber', '=', $ticketNumber)->first();
        $seed_grower_details = DB::connection('seed_grower_db')->table('seed_growers_all')->where('Code_Number', '=', $delivery_details->sgAccreditation)->first();
        $inspector_details = DB::connection('mysql')->table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', '8')
            ->get();

        return view('inspection.form')
            ->with('delivery_details', $delivery_details)
            ->with('seed_grower_details', $seed_grower_details)
            ->with('inspector_details', $inspector_details);
    }

    public function getInspectorDetails($idNumber){
        $inspector = DB::connection('mysql')->table('users')->where('userId', '=', $idNumber)->first();
        $batch_tickets = DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->select('tbl_delivery.batchTicketNumber', 
                     'tbl_delivery.coopAccreditation', 
                     'tbl_delivery.deliveryDate', 
                     'tbl_delivery.dropOffPoint', 
                     'tbl_schedule.userId',
                     'tbl_delivery.region',
                     'tbl_delivery.province',
                     'tbl_delivery.municipality')
            ->join('tbl_delivery', 'tbl_delivery.batchTicketNumber', '=', 'tbl_schedule.batchTicketNumber')
            ->where('tbl_schedule.userId', '=', $idNumber)      
            ->groupBy('tbl_delivery.batchTicketNumber')
            ->get();              

        return view('inspection.details')
            ->with('inspector', $inspector)
            ->with('batch_tickets', $batch_tickets);
    }

    public function DesignateInspector2(){        
        //$delivery_details = DB::connection('delivery_inspection_db')->table('tbl_delivery')->where('ticketNumber', '=', $ticketNumber)->first();
        //$seed_grower_details = DB::connection('seed_grower_db')->table('seed_growers_all')->where('Code_Number', '=', $delivery_details->sgAccreditation)->first();
        $delivery_details = DB::connection('delivery_inspection_db')->table('tbl_delivery')
                                ->where('tbl_delivery.status', '=', '0')
                                ->where('tbl_delivery.inspectorAllocated', '=', '0')
								->where('tbl_delivery.region', '!=', '')
                                ->where('tbl_delivery.is_cancelled', '!=', '1')
                                ->groupBy('tbl_delivery.region')
                                ->get();
        $inspector_details = DB::connection('mysql')->table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'username')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', '8')
            ->get();

        return view('inspection.form2')
            ->with('inspector_details', $inspector_details)
            ->with('delivery_details', $delivery_details);
    }

    public function updateTicketDuration(Request $request){
        //inspectoor duration
        $duration_arr = explode(' - ',$request->inspector_duration);
        $duration_from = date("Y-m-d", strtotime($duration_arr[0]));
        $duration_to = date("Y-m-d", strtotime($duration_arr[1]));

        DB::connection('delivery_inspection_db')->table('tbl_schedule')
                        ->where('scheduleId', $request->scheduleId)
                        ->limit(1) 
                        ->update(array('duration_from' => $duration_from, 
                                       'duration_to' => $duration_to));

        $userId = DB::connection('delivery_inspection_db')
                    ->table('tbl_schedule')
                    ->where('scheduleId', '=', $request->scheduleId)
                    ->first()->userId;
        
        Session::flash("success", "you have successfully performed an update...");
        return redirect()->route('inspector.details', $userId);
    }

    public function saveInspectorDetails(Request $request){
        $this->validate($request, array(
            'inspectorID' => 'required',
            'date_of_inspection' => 'required',
            'pmo_remarks' => 'required',
            'dropOffID' => 'required'
        ));
		
		if(count($request->batch_tickets) > 0){
            $batch_id_array = array();
            foreach($request->batch_tickets as $batch){
                DB::connection('delivery_inspection_db')->table('tbl_delivery')
                    ->where('batchTicketNumber', $batch)
                    ->update(array('inspectorAllocated' => 1));

                DB::connection('delivery_inspection_db')->table('tbl_schedule')
                ->insertGetId([
                    'userId' => $request->inspectorID,
                    'batchTicketNumber' => $batch,
                    'inspectionDate' => date("Y-m-d", strtotime($request->date_of_inspection)),
                    'pmo_remarks' => $request->pmo_remarks,
                    'assignedBy' => Auth::user()->userId
                ]);
            }

            Session::flash("success", "you have successfully saved the record!");
            return redirect()->route('rcef.inspection.designation2');

        }else{
            Session::flash("error_msg", "Please select a batch to assign...");
            return redirect()->route('rcef.inspection.designation2');
        }

        //get all batchID in a dropOffPoint
        /*$delivery_details = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('deliveryId', '=', $request->dropOffID)
            ->first();

        $drop_off_point = $delivery_details->dropOffPoint;

        //get all records based on the retrieved drop_off_point
        $delivery_drops = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('dropOffPoint', '=', $drop_off_point)
            ->get();

        //updated inspectorAllocated status on tbl_delivery
        foreach($delivery_drops as $d_drop){
            DB::connection('delivery_inspection_db')->table('tbl_delivery')
                ->where('deliveryId', $d_drop->deliveryId)
                ->update(array('inspectorAllocated' => 1));
        }

        //get all batches to save to tbl_schedule
        $delivery_batches = DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('dropOffPoint', '=', $drop_off_point)
            ->groupBy('batchTicketNumber')
            ->get();

        foreach($delivery_batches as $batch){
            DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->insertGetId([
                'userId' => $request->inspectorID,
                'batchTicketNumber' => $batch->batchTicketNumber,
                'inspectionDate' => date("Y-m-d", strtotime($request->date_of_inspection)),
                'pmo_remarks' => $request->pmo_remarks,
                'assignedBy' => Auth::user()->userId
            ]);
        }
       
        
        Session::flash("success", "you have successfully saved the record!");
        return redirect()->route('rcef.inspection.designation2');*/
    }

    public function getBatchDetails($userId, $batchID){
        $inspector = DB::connection('mysql')->table('users')->where('userId', '=', $userId)->first();
        $tickets = DB::connection('delivery_inspection_db')->table('tbl_delivery')->where('batchTicketNumber', '=', $batchID)->get();
        $batchTicketNumber = $batchID;

        return view('inspection.tickets')
            ->with('inspector', $inspector)
            ->with('tickets', $tickets)
            ->with('batchTicketNumber', $batchTicketNumber);
    }

    public function InspectorRegistrationForm(){
        return view('inspection.registration2');
    }

    public function InspectorRegistrationSave(Request $request){
        $this->validate($request, [
            'id_number' => 'required|max:7',
            'first_name' => 'required|max:100',
            'middle_name' => 'max:50',
            'last_name' => 'required|max:100',
            'suffix_name' => 'max:20',
            'email_address' => 'required|email|max:150',
        ]);

        //check if id number exists in inspectors table
        $inspectors = DB::connection('mysql')->table('users')
            ->where('email', '=', $request->email_address)
            ->first();

        if(count($inspectors) > 0){
            return "inspector_exists";
        }else{

            $password = Hash::make("P@ssw0rd");
            $api_token = str_random(60);

            // insert user
            $userId = DB::connection('mysql')->table('users')
            ->insertGetId([
                'firstName' => $request->first_name,
                'middleName' => $request->middle_name,
                'lastName' => $request->last_name,
                'extName' => $request->suffix_name,
                'username' => $request->id_number,
                'email' => $request->email_address,
                'secondaryEmail' => $request->email_address,
                'password' => $password,
                'sex' => $request->sex,
                'region' => 0,
                'province' => 0,
                'municipality' => 0,
                'agencyId' => 0,
                'stationId' => 0,
                'position' => "",
                'designation' => "",
                'api_token' => $api_token,
            ]);

            // add user roles
            DB::connection('mysql')->table('role_user')
            ->insert([
                'userId' => $userId,
                'roleId' => 8
            ]);

            return route('rcef.inspection.registration');
        }
    }

    public function InspectorVerification(){
        return view('inspection.verification');
    }

    public function InspectorScheduleView(){
        $inspectors = DB::connection('mysql')->table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName')
            ->join('role_user', 'users.userId', '=', 'role_user.userId')
            ->where('role_user.roleId', '=', '8')
            ->get();

        $delivery_regions =  DB::connection('delivery_inspection_db')
            ->table('tbl_delivery')
            ->where('region', '!=', '')
            ->groupBy('region')
            ->get();

        return view('inspection.schedule')
            ->with('inspectors', $inspectors)
            ->with('delivery_regions', $delivery_regions);
    }

    public function replaceInspector(Request $request){
        $this->validate($request, array(
            'inspectorID' => 'required',
            'scheduleID' => 'required'
        ));

        DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->where('scheduleId', $request->scheduleID)
            ->update(array(
                'userId' => $request->inspectorID,
                'date_assigned' => date("Y-m-d H:i:s")
            ));

        Session::flash("success", "You have successfully replaced a seed inspector.");
        return redirect()->route('rcef.inspector.schedule');
    }

    public function InspectorScheduleTable(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->select('tbl_schedule.userId', 'tbl_schedule.batchTicketNumber', 'tbl_schedule.inspectionDate',
                'tbl_delivery.region', 'tbl_delivery.province', 'tbl_delivery.municipality', 'tbl_delivery.dropOffPoint',
                DB::raw("SUM(totalBagCount) as bags"), 'tbl_schedule.scheduleId')
            ->join('tbl_delivery', 'tbl_delivery.batchTicketNumber', '=', 'tbl_schedule.batchTicketNumber')
            ->groupBy('batchTicketNumber')
            ->orderBy('inspectionDate', 'DESC')
        )
        ->addColumn('full_name', function($row){
            $user = DB::connection('mysql')->table('users')->where('userId', '=', $row->userId)->first();
            $full_name = $user->firstName." ".$user->lastName;
            $anchor = "<a href='' data-id='".$row->scheduleId."' data-toggle='modal' data-target='#inspectorModal' data-name='".$full_name."' data-batch='".$row->batchTicketNumber."' data-bags='".$row->bags."'>".$full_name." <li class='fa fa-exchange'></li></a>";
            return $anchor; 
        })
        ->addColumn('inspection_date', function($row){
            return date("Y-m-d", strtotime($row->inspectionDate)); 
        })
        ->make(true);
    }
	
	public function InspectorDeliveryTable(Request $request){
        return Datatables::of(DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select(DB::raw("*, SUM(totalBagCount) as total_deliveryBags"))
            ->where('region', $request->region)
            ->where('province', $request->province)
            ->where('municipality', $request->municipality)
            ->groupBy('batchTicketNumber')
            ->orderBy('deliveryDate', 'DESC')
        )
        ->addColumn('seed_coop', function($row){
            $coop_name = DB::connection('seed_coop_db')->table('tbl_cooperatives')
                ->where('accreditation_no', $row->coopAccreditation)
                ->value('coopName');
            return $coop_name != "" ? $coop_name : "N/A";
        })
        ->addColumn('date_of_delivery', function($row){
            return date("Y-m-d", strtotime($row->deliveryDate)); 
        })
        ->addColumn('total_dBags', function($row){
            return $row->total_deliveryBags." bag(s)"; 
        })
        ->addColumn('delivery_status', function($row){
            $delivery_status = DB::connection('delivery_inspection_db')->table('tbl_delivery_status')
                ->where('batchTicketNumber', $row->batchTicketNumber)
                ->get();

            $status_str = "";
            foreach($delivery_status as $s_row){
                if($s_row->status == 0){
                    //pending
                    $status_str .= '<span class="label label-primary">Pending</span><br>';
                }else if($s_row->status == 1){
                    //inspected passed
                    $status_str .= '<span class="label label-success">Inspected (Passed)</span><br>';
                }else if($s_row->status == 2){
                    //inspected rejected
                    $status_str .= '<span class="label label-danger">Inspected (Rejected)</span><br>';
                }else if($s_row->status == 3){
                    //in transit
                    $status_str .= '<span class="label label-warning">In Transit</span><br>';
                }else if($s_row->status == 4){
                    //cancelled
                    $status_str .= '<span class="label label-danger">Cancelled</span><br>';
                }  
            }

            return $status_str;
        })
        ->addColumn('action_fld', function($row){
            return '<a href="#" class="btn btn-info btn-sm" data-toggle="modal" data-target="#view_inspector_modal" data-batch="'.$row->batchTicketNumber.'"><i class="fa fa-eye"></i></a>'; 
        })
		->addColumn('dropoff_name', function($row){
            return DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
				->where('prv_dropoff_id', $row->prv_dropoff_id)
				->value('dropOffPoint');
        })
        ->make(true);
    }
	
	public function getaBatchDelivery_InspectorDetails(Request $request){
        $batch_number = $request->batch_number;

        //get batch_ticket number details
        $batch_details = DB::connection('delivery_inspection_db')->table('tbl_delivery')
            ->select(DB::raw("*, SUM(totalBagCount) as total_deliveryBags"))
            ->where('batchTicketNumber', $batch_number)
            ->groupBy('batchTicketNumber')
            ->first();

        //get seed coop_name
        $seed_coop = DB::connection('seed_coop_db')->table('tbl_cooperatives')
            ->where('accreditation_no', $batch_details->coopAccreditation)
            ->value('coopName');
			
		//get dropoff point name
		$dropoff_name = DB::connection('delivery_inspection_db')->table('lib_dropoff_point')
			->where('prv_dropoff_id', $batch_details->prv_dropoff_id)
			->value('dropOffPoint');

        //get assigned inspector
        $inspector_sched = DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->where("batchTicketNumber", $batch_number)
            ->first();
        if(count($inspector_sched) > 0){
            $inspector_name = DB::connection('mysql')->table('users')
                ->where('userId', $inspector_sched->userId)
                ->first();

            $current_inspector = $inspector_name->firstName." ".$inspector_name->lastName;

            //get inspector list
            $inspector_list = DB::connection('mysql')->table('users')
                ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'username')
                ->join('role_user', 'users.userId', '=', 'role_user.userId')
                ->where('role_user.roleId', '=', '8')
                ->where('users.userId', '!=', $inspector_name->userId)
                ->get();

            $inspector_list_str = '';
            foreach($inspector_list as $inspector_row){
                $inspector_name = $inspector_row->firstName." ".$inspector_row->lastName;
                $inspector_list_str .= "<option value='$inspector_row->userId'>$inspector_name ($inspector_row->username)</option>";
            }
        
        }else{
            $current_inspector = "No inspector allocated";
            $inspector_list_str = "no_inspector";
        }

        
        return array(
            "region" => $batch_details->region,
            "province" => $batch_details->province,
            "municipality" => $batch_details->municipality,
            //"dropoff" => $batch_details->dropOffPoint,
			"dropoff" => $dropoff_name,
            "total_bags" => $batch_details->total_deliveryBags,
            "date_of_delivery" => $batch_details->deliveryDate,
            "seed_coop" => $seed_coop,
            "current_inspector" => $current_inspector,
            "inspector_list" => $inspector_list_str
        );
    }
	
	public function updateAssignedInspector(Request $request){
        $new_inspector_id = $request->inspectorID;
        $reason = $request->reason;
        $batch_number = $request->batch_number;

        $schedule = DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->where('batchTicketNumber', $batch_number)
            ->first();

        $new_inspector = DB::connection('mysql')->table('users')->where('userId', $new_inspector_id)->first();
        $original_inspector = DB::connection('mysql')->table('users')->where('userId', $schedule->userId)->first();

        //names
        $new_inspector_name = $new_inspector->firstName." ".$new_inspector->lastName;
        $original_inspector_name = $original_inspector->firstName." ".$original_inspector->lastName;

        try{
            DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->where('batchTicketNumber', $batch_number)
            ->update(array(
                'userId' => $new_inspector_id,
                'pmo_remarks' => $schedule->pmo_remarks.", REPLACEMENT OF SEED INSPECTOR DETAILS: original seed inspector: ".$original_inspector_name.", new seed inspector: ".$new_inspector_name." | REASON: ".$reason
            ));

            //insert to logs
            DB::connection('mysql')->table('lib_logs')
            ->insert([
                'category' => 'INSPECTOR_REPLACEMENT',
                'description' => 'Replaced seed inspector for the batch ticket number: '.$batch_number.' | DETAILS: original seed inspector: '.$original_inspector_name.', new seed inspector: '.$new_inspector_name." | REASON: ".$reason,
                'author' => Auth::user()->username,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            return route('rcef.inspector.schedule');

        }catch(\Illuminate\Database\QueryException $ex){
            //error in updating seed inspector
        }        
    }

    public function SubmittedInspectors(Request $request){
        return Datatables::of(DB::connection('mysql')->table('inspectors')
            ->orderBy('dateCreated', 'desc')
        )
        ->addColumn('full_name', function($row){
            if($row->extName == ''){
                $full_name = $row->firstName.' '.$row->middleName.' '.$row->lastName;
            }else{
                $full_name = $row->firstName.' '.$row->middleName.' '.$row->lastName.' '.$row->extName;
            }
            return $full_name; 
        })
        ->addColumn('ins_status', function($row){
           if($row->inspectorStatus == 0){
                $ins_status = 'Pending';
           }elseif($row->inspectorStatus == 1){
                $ins_status = 'Approved';
           }elseif($row->inspectorStatus == 2){
                $ins_status = 'Rejected';
           }
            return $ins_status; 
        })
        ->addColumn('action', function($row){
            if($row->inspectorStatus == 1 || $row->inspectorStatus == 2){
                return 'N/A';
            }else{
                return '<a href="" data-toggle="modal" data-target="#verify_approve_inspector_modal" data-id="'.$row->philriceID.'" class="btn btn-success btn-sm verify-approve-open-modal"> <i class="fa fa-thumbs-up"></i></a>
                    <a href="" data-toggle="modal" data-target="#verify_reject_inspector_modal" data-id="'.$row->philriceID.'" class="btn btn-danger btn-sm verify-reject-open-modal"> <i class="fa fa-thumbs-down"></i> </a>'; 
            }
        })
        ->make(true);
    }

    public function ApproveInspectorProfile(Request $request){
        $this->validate($request, array(
            'id_number_approve' => 'required'
        ));

        //check if user already exist
        $inspector_data = DB::connection('mysql')->table('inspectors')->where('philriceID', '=', $request->id_number_approve)->first();
        $user_data = DB::connection('mysql')->table('users')
            ->where('firstName', '=', $inspector_data->firstName)
            ->where('middleName', '=', $inspector_data->middleName)
            ->where('lastName', '=', $inspector_data->lastName)
            ->where('extName', '=', $inspector_data->extName)
            ->first();
       
        if(count($user_data) > 0){
            Session::flash("error_msg", "Existing user!");
            return redirect()->route('rcef.inspection.verification');
        }else{
            DB::connection('mysql')->table('inspectors')
                     ->where('philriceID', $request->id_number_approve)
                     ->update(array('inspectorStatus' => 1));

            $userID = DB::connection('mysql')->table('users')
            ->insertGetId([
                'firstName' => $inspector_data->firstName,
                'middleName' => $inspector_data->middleName,
                'lastName' => $inspector_data->lastName,
                'extName' => $inspector_data->extName,
                'username' => $inspector_data->philriceID,
                'email' => $inspector_data->email,
                'password' => Hash::make("P@ssw0rd"),
                'sex' => $inspector_data->sex,
                'position' => $inspector_data->position,
                'designation' => $inspector_data->designation,
                'api_token' => str_random(60),
                'created_at' => date("Y-m-d H:i:s")
            ]);

            DB::table('role_user')->insert([
                'userId' => $userID,
                'roleId' => 8
            ]);

            Session::flash("success", "Successfully designated the selected profile as `Delivery Inspector`");
            return redirect()->route('rcef.inspection.verification');
        }
    }

    public function RejectInspectorProfile(Request $request){
        $this->validate($request, array(
            'id_number_reject' => 'required'
        ));

        DB::connection('mysql')->table('inspectors')
            ->where('philriceID', $request->id_number_reject)
            ->update(array('inspectorStatus' => 2));

        Session::flash("error_msg", "Successfully rejected the request for designation as `Delivery Inspector`");
        return redirect()->route('rcef.inspection.verification');
    }

}

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
                                ->groupBy('tbl_delivery.region')
                                ->get();
        $inspector_details = DB::connection('mysql')->table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName')
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

        //dd($request->batch_tickets);

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

        /*//get all batchID in a dropOffPoint
        $delivery_details = DB::connection('delivery_inspection_db')
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
        */
        //update all batch id : allocate inspector
        
        /*foreach($delivery_batches as $batch){
            DB::connection('delivery_inspection_db')->table('tbl_schedule')
            ->insertGetId([
                'userId' => $request->inspectorID,
                'batchTicketNumber' => $batch->batchTicketNumber,
                'inspectionDate' => date("Y-m-d", strtotime($request->date_of_inspection)),
                'pmo_remarks' => $request->pmo_remarks,
                'assignedBy' => Auth::user()->userId
            ]);
        }*/
       
        
        
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
        $regions = DB::connection('mysql')->table('lib_regions')->get();
        return view('inspection.registration')->with('regions', $regions);
    }

    public function InspectorRegistrationSave(Request $request){
        $this->validate($request, array(
            'id_number' => 'required',
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'sex' => 'required',
            'email_address' => 'required',
            'position' => 'required'
        ));

        //check if id number exists in inspectors table
        $inspectors = DB::connection('mysql')->table('inspectors')
            ->where('philriceID', '=', $request->id_number)
            ->where('inspectorStatus', '=', '0')
            ->orWhere('philriceID', '=', $request->id_number)
            ->where('inspectorStatus', '=', '1')
            ->first();

        if(count($inspectors) > 0){
            Session::flash("error_msg", "This inspector profile has already been registered in the database!");
            return redirect()->route('rcef.inspection.registration');
        }else{
            //check data on hris - get employment status
            $hris_data = DB::connection('hris_db')->table('employees')->select('emp_status')->where('emp_idno', '=', $request->id_number)->first();
            
            if($hris_data->emp_status == '11038'){
                $employment_status = 'SC';
            }elseif($hris_data->emp_status == '11001' || $hris_data->emp_status == '11006'){
                $employment_status = 'P';
            }

            DB::connection('mysql')->table('inspectors')
            ->insertGetId([
                'philriceID' => $request->id_number,
                'firstName' => $request->first_name,
                'middleName' => $request->middle_name,
                'lastName' => $request->last_name,
                'extName' => $request->suffix_name,
                'sex' => $request->sex,
                'email' => $request->email_address,
                'position' => $request->position,
                'designation' => $request->designation,
                'employmentStatus' => $employment_status,
                'inspectorStatus' => 0,
                'dateCreated' => date("Y-m-d H:i:s")
            ]);

            Session::flash("success", "you have successfully submitted an inspector Profile!");
            return redirect()->route('rcef.inspection.registration');
        }
    }

    public function InspectorVerification(){
        return view('inspection.verification');
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

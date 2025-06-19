<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use DB;
use Hash;
use Auth;
use Yajra\Datatables\Datatables;

use Session;
use App\User;

class UserController extends Controller
{
    public function __construct()
    {
        // database connections
        $this->geotag_con = 'geotag_db';
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    // public function index(Request $request)
    public function index()
    {
     
        $data['api_token'] = Auth::user()->api_token;

    $userManagement = array();
    
    $roles_filtered = [
        "branch-it", "buffer-inspector", "dro", "delivery-manager", "ebinhi-implementor", "rcef-pmo", "system-encoder", "techno_demo_officer", "seed-grower", "administrator"
    ];

     if(Auth::user()->roles->first()->name == "rcef-programmer"){
        $roles = DB::table('roles')
        // ->whereIn("name", $roles_filtered)
        ->pluck('display_name', 'roleId');
    }else{
        $roles = DB::table('roles')
        ->whereIn("name", $roles_filtered)
        ->pluck('display_name', 'roleId');
    }

    

        if(isset($userManagement[Auth::user()->username]) || Auth::user()->roles->first()->name == "rcef-programmer"){
            return view('users.index', compact('data', 'roles'));  
        }elseif(Auth::user()->roles->first()->name == "branch-it"){
            if(Auth::user()->stationId != "0"){
                return view('users.index', compact('data', 'roles'));  
            }else{
                $mss = "No Station Tagged";
                return view('utility.pageClosed',compact("mss"));
            }
        }
        else{
                $mss = "No Access Privilege";
                return view('utility.pageClosed',compact("mss"));
             
        }






        
    }







    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $roles = DB::table('roles')
            ->pluck('display_name', 'roleId');
        }else{
            
            $mss = "No Access Privilege";
            return view('utility.pageClosed',compact("mss"));

            $roles = DB::table('roles')
            ->whereNotIn("name", array("system-admin","rcef-programmer","moet_dev"))
            ->pluck('display_name', 'roleId');
        }

     //   dd($roles);

        $agencies = DB::table('lib_agencies')
        ->orderBy('name', 'asc')
        ->pluck('name', 'agencyId');

        $stations = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->orderBy('stationName', 'asc')
        ->pluck('stationName', 'stationId');

        $provinces = DB::table('lib_provinces')
        ->orderBy('provDesc', 'asc')
        ->pluck('provDesc', 'provCode');

        $data['api_token'] = Auth::user()->api_token;

        return view('users.create',
        compact('roles', 'agencies', 'stations', 'provinces', 'data'));
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function request_approval(){
        
     
        $data['api_token'] = Auth::user()->api_token;

    $userManagement = array();

        if(isset($userManagement[Auth::user()->username]) || Auth::user()->roles->first()->name == "rcef-programmer"){
            return view('users.request_index', compact('data'));  
        }
        else{
                $mss = "No Access Privilege";
                return view('utility.pageClosed',compact("mss"));
             
        }
    }


    public function create_request()
    {
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $roles = DB::table('roles')
            ->pluck('display_name', 'roleId');
        }else{
             $roles = DB::table('roles')
            ->whereNotIn("name", array("system-admin","rcef-programmer","moet_dev","sdms-data-manager","bdd-manager","bdd-head","isd-head","rcef-pmo","accountant","sed-caller","sed-caller-manager","coa","ces_payment_processor","payment_accountant","moet_dev","rcef-coordinator","preReg-officer","kiosk-manager","techno_demo_encoder","it-sra","extension_encoder","encoder_yield","techno_demo_officer","dro","da-icts","farm-manager","warehouse-manager"/* ,"delivery-manager" */))
            ->pluck('display_name', 'roleId');
        }

     //   dd($roles);
        $agencies = DB::table('lib_agencies')
        ->orderBy('name', 'asc')
        ->pluck('name', 'agencyId');

//stationId
        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $filter_station = "%";
            $provinces = DB::table('lib_provinces')
            ->orderBy('provDesc', 'asc')
            ->pluck('provDesc', 'provCode');

        }
        else{
            if(Auth::user()->stationId != "0"){
                $filter_station = Auth::user()->stationId;
                $filter_province = DB::table("lib_station")
                    ->select("province")
                    ->where("stationID", $filter_station)
                    ->get();

                $filter_province = json_decode(json_encode($filter_province), true);
                
                $provinces = DB::table('lib_provinces')
                ->whereIn("provDesc", $filter_province)
                ->orderBy('provDesc', 'asc')
                ->pluck('provDesc', 'provCode');


            }else{
                return "Your User is not assigned on a particular station";
            }
        }
        
        $stations = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->orderBy('stationName', 'asc')
        ->where("stationId", "LIKE", $filter_station)
        ->pluck('stationName', 'stationId');

        
        $data['api_token'] = Auth::user()->api_token;

        return view('users.create_request',
        compact('roles', 'agencies', 'stations', 'provinces', 'data'));
    }


    public function store_request(Request $request)
    {
       

        $this->validate($request, [
            'firstName' => 'required|max:100',
            'middleName' => 'max:50',
            'lastName' => 'required|max:100',
            'extName' => 'max:20',
            'username' => 'required|unique:users,username|max:255',
            'password' => 'required|same:password2|min:6',
            'roles' => 'required',
            'stationId' => 'required',
        ], [
            'password.same' => 'Password does not match the confirm password',
            'stationId.required' => 'The station field is required'
        ]);

        $input = $request->all();
        $password = Hash::make($input['password']);
        $api_token = str_random(60);

        DB::beginTransaction();
        try {
          
            // insert user
            $email = strtolower(substr($input['firstName'],0,1)).".".strtolower($input['lastName']);
            $available_email = 0;
                do { 
                    $check_mail = DB::table("users")
                        ->where("email", "LIKE", $email."@philrice.gov.ph")
                        ->get();
                    if(count($check_mail)>0){
                            $email .= "_1";
                    }else{
                        $available_email = 1;
                    }

                } while ($available_email == 0);
                

                $email .= "@philrice.gov.ph";
              

            $userId = DB::table('users')
            ->insertGetId([
                'firstName' => $input['firstName'],
                'middleName' => $input['middleName'],
                'lastName' => $input['lastName'],
                'extName' => $input['extName'],
                'username' => $input['username'],
                'email' => $email,
                'secondaryEmail' => $input['secondaryEmail'],
                'password' => $password,
                'sex' => $input['sex'],
                'region' => $input['region'],
                'province' => $input['province'],
                'municipality' => $input['municipality'],
                'agencyId' => "0",
                'stationId' => $input['stationId'],
                'position' => "-",
                'designation' => "-",
                'api_token' => $api_token,
                // 'requested_by' => Auth::user()->username,
                // 'aprroved_date' => "00-00-00 00:00:00",
            ]);

            // add user roles
            foreach ($input['roles'] as $key => $value) {
                DB::table('role_user')
                ->insert([
                    'userId' => $userId,
                    'roleId' => $value
                ]);

                if($value == 3){
                    DB::connection('mysql')->table('request_users_coop')
                    ->insert([
                        'userId' => $userId,
                        'coopAccreditation' => $request->accreditation_number,
                        'assignedBy' => Auth::user()->username,
                    ]);
                }
            }

            DB::commit();
            Session::flash('success', 'Added user successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Error adding user.');
        }

        return redirect()->route('users.index');
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'firstName' => 'required|max:100',
            'middleName' => 'max:50',
            'lastName' => 'required|max:100',
            'extName' => 'max:20',
            'username' => 'required|unique:users,username|max:255',
            'email' => 'required|email|unique:users,email|max:150',
            'secondaryEmail' => 'email|unique:users,email|max:150',
            'password' => 'required|same:password2|min:6',
            'roles' => 'required',
            'stationId' => 'required_if:agencyId,1',
        ], [
            'password.same' => 'Password does not match the confirm password',
            'stationId.required_if' => 'The station field is required'
        ]);

        $input = $request->all();
        $password = Hash::make($input['password']);
        $api_token = str_random(60);

        DB::beginTransaction();
        try {
            // insert user
            $userId = DB::table('users')
            ->insertGetId([
                'firstName' => $input['firstName'],
                'middleName' => $input['middleName'],
                'lastName' => $input['lastName'],
                'extName' => $input['extName'],
                'username' => $input['username'],
                'email' => $input['email'],
                'secondaryEmail' => $input['secondaryEmail'],
                'password' => $password,
                'sex' => $input['sex'],
                'region' => $input['region'],
                'province' => $input['province'],
                'municipality' => $input['municipality'],
                'agencyId' => $input['agencyId'],
                'stationId' => $input['stationId'],
                'position' => $input['position'],
                'designation' => $input['designation'],
                'api_token' => $api_token,
            ]);

            // add user roles
            foreach ($input['roles'] as $key => $value) {
                DB::table('role_user')
                ->insert([
                    'userId' => $userId,
                    'roleId' => $value
                ]);

                if($value == 3){
                    DB::connection('mysql')->table('users_coop')
                    ->insert([
                        'userId' => $userId,
                        'coopAccreditation' => $request->accreditation_number,
                        'assignedBy' => Auth::user()->username,
                    ]);
                }
            }

            DB::commit();
            Session::flash('success', 'Added user successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('error', 'Error adding user.');
        }

        return redirect()->route('users.index');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $userId = $id;

        $user = DB::table('users')
        ->select('*')
        ->where('userId', $userId)
        ->first();

        $name = $this->full_name($user->firstName, $user->middleName, $user->lastName, $user->extName);

        $agency = DB::table('lib_agencies')
        ->select('*')
        ->where('agencyId', $user->agencyId)
        ->first();

        $station = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->select('*')
        ->where('stationId', $user->stationId)
        ->first();

        $region = DB::table('lib_regions')
        ->select('*')
        ->where('regCode', $user->region)
        ->first();

        $province = DB::table('lib_provinces')
        ->select('*')
        ->where('provCode', $user->province)
        ->first();

        $municipality = DB::table('lib_municipalities')
        ->select('*')
        ->where('citymunCode', $user->municipality)
        ->first();

        $roles = DB::table('roles')
        ->select('roles.display_name')
        ->leftJoin('role_user', 'role_user.roleId', '=', 'roles.roleId')
        ->where('role_user.userId', $userId)
        ->get();

        return view('users.show',
        compact('user', 'name', 'agency', 'station', 'region', 'province', 'municipality', 'roles'));
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $userId = $id;

        $user = DB::table('users')
        ->select('*')
        ->where('userId', $userId)
        ->first();

        if(Auth::user()->roles->first()->name == "rcef-programmer"){
            $roles = DB::table('roles')
            ->pluck('display_name', 'roleId');
        }else{
            $roles = DB::table('roles')
            ->whereNotIn("name", array("system-admin","rcef-programmer","moet_dev"))
            ->pluck('display_name', 'roleId');
        }

        $userRoles = DB::table('role_user')
        ->where('userId', $userId)
        ->lists('roleId');

        $agencies = DB::table('lib_agencies')
        ->pluck('name', 'agencyId');

        $stations = DB::connection($this->geotag_con)
        ->table('tbl_station')
        ->pluck('stationName', 'stationId');

        $regions = DB::table('lib_regions')
        ->pluck('regDesc', 'regCode');

        $provinces = DB::table('lib_provinces')
        ->pluck('provDesc', 'provCode');

        $municipalities = DB::table('lib_municipalities')
        ->where('provCode', $user->province)
        ->pluck('citymunDesc', 'citymunCode');

        return view('users.edit',
        compact('user','roles','userRoles', 'agencies', 'stations', 'regions', 'provinces', 'municipalities'));
    }

    public function assignCoopID(Request $request){
        $this->validate($request, [
            'userID' => 'required',
            'seed_coop' => 'required'
        ]);

        DB::connection('mysql')->table('users_coop')
            ->insertGetId([
                'userId' => $request->userID,
                'coopAccreditation' => $request->seed_coop,
                'assignedBy' => Auth::user()->username
            ]
        );

        Session::flash('success', 'Successfully added an accreditation number for a seed grower account.');
        return redirect()->route('users.index');
    }

    public function updateCoopID(Request $request){
        $this->validate($request, [
            'userID_update' => 'required',
            'seed_coop_update' => 'required'
        ]);

        DB::connection('mysql')->table('users_coop')
        ->where('userId', $request->userID_update)
        ->update([
            'coopAccreditation' => $request->seed_coop_update,
            'dateUpdated' => date("Y-m-d H:i:s")
        ]);

        Session::flash('success', 'Successfully updated an accreditation number for a seed grower account.');
        return redirect()->route('users.index');
    }


    public function approve_request(Request $request){
    
            $user_request = DB::table("request_users")
                ->where("userId", $request->prv_userID)
                ->first();
          
            if($user_request != null){

                $role = DB::table("request_role_user")
                ->where("userId", $request->prv_userID)
                ->get();

                // DB::beginTransaction();
                // try {
                    $user_request = json_decode(json_encode($user_request), true);
                
                    // insert user
                    $approved_userId = DB::table('users')
                    ->insertGetId([
                        'firstName' => $user_request['firstName'],
                        'middleName' => $user_request['middleName'],
                        'lastName' => $user_request['lastName'],
                        'extName' => $user_request['extName'],
                        'username' => $user_request['username'],
                        'email' => $user_request['email'],
                        'secondaryEmail' => $user_request['secondaryEmail'],
                        'password' => $user_request["password"],
                        'sex' => $user_request['sex'],
                        'region' => $user_request['region'],
                        'province' => $user_request['province'],
                        'municipality' => $user_request['municipality'],
                        'agencyId' => $user_request['agencyId'],
                        'stationId' => $user_request['stationId'],
                        'position' => $user_request['position'],
                        'designation' => $user_request['designation'],
                        'api_token' => $user_request["api_token"],
                    ]);
        
                    // add user roles
                    foreach ($role as $key => $value) {
                        DB::table('role_user')
                        ->insert([
                            'userId' => $approved_userId,
                            'roleId' => $value->roleId
                        ]);
        
                        if($value->roleId == 3){
                            $coop = DB::table("request_users_coop")  
                            ->where("userId", $request->prv_userID)
                            ->first();
                            DB::connection('mysql')->table('users_coop')
                            ->insert([
                                'userId' => $approved_userId,
                                'coopAccreditation' => $coop->coopAccreditation,
                                'assignedBy' => $coop->assignedBy,
                            ]);
                        }
                    }
        
                   
                   
                    DB::table("request_users")
                    ->where("userId", $request->prv_userID)
                    ->update([
                        "aprroved_date" => date("Y-m-d H:i:s"),
                        "approved_by" => Auth::user()->username
                    ]);


                    DB::commit();
                    Session::flash('success', 'Approved user successfully.');
                // } catch (\Exception $e) {
                //     DB::rollback();
                //     Session::flash('error', 'Error on Approving user.');
                // }

                return redirect()->route('users.approval');

            }else{
                Session::flash('error', 'Error on adding user.');
                return redirect()->route('users.approval');
            }



    }

    public function datatable_request(){
        $data = DB::table("request_users")
            ->select('firstName','middleName','lastName','username', 'stationID', 'requested_by as requested', 'userId') 
            ->where("aprroved_date", "0000-00-00 00:00:00")
            ->get();
        $dtbl = array();
        foreach($data as $data){
                $station = DB::table("lib_station")
                ->where("stationID", $data->stationID)
                ->value("station");

                $role = DB::table("request_role_user")
                    ->where("userId", $data->userId)
                    ->value("roleId");
                $role_name = DB::table("roles")
                ->where("roleId", $role)
                ->value("display_name");

                $button = '<a href="#" data-username="'.$data->username.'"  data-name="'.$data->lastName.", ".$data->firstName." ".$data->middleName.'" data-id="'.$data->userId.'" class="btn btn-success actionBtn open-assignProvince" data-toggle="modal" data-target="#assignProvince" title="Assign accreditation #"><i class="fa fa-check-circle-o"></i> Approved</a>&nbsp;';


                array_push($dtbl, array(
                        "name" => $data->lastName.", ".$data->firstName." ".$data->middleName,
                        "username" => $data->username,
                        "station"  => $station,
                        "role" => $role_name,
                        "requested" => $data->requested,
                        "action" => $button
                ));

        }

        $dtbl = collect($dtbl);
        return Datatables::of($dtbl)
            ->make(true);

    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        $userId = $id;
        $this->validate($request, [
            'firstName' => 'required|max:100',
            'middleName' => 'max:50',
            'lastName' => 'required|max:100',
            'extName' => 'max:20',
            'username' => 'required|unique:users,username,' . $userId . ',userId|max:255',
            'email' => 'required|email|unique:users,email,' . $userId . ',userId|max:150',
            'secondaryEmail' => 'email|unique:users,email,' . $userId . ',userId|max:150',
            // 'password' => 'sometimes|same:password2|min:6',
            'sex' => 'required',
            'roles' => 'required',
            'stationId' => 'required_if:agencyId,1',
            'region' => 'required'
        ], [
            // 'password.same' => 'Password does not match the confirm password',
            'stationId.required_if' => 'The station field is required'
        ]);


        $input = $request->all();
        // if(!empty($input['password'])){
        //     $input['password'] = Hash::make($input['password']);
        // }else{
        //     $input = array_except($input,array('password'));
        // }

        DB::beginTransaction();
        try {
            // update user details
            DB::table('users')
            ->where('userId', $userId)
            ->update([
                'firstName' => $input['firstName'],
                'middleName' => $input['middleName'],
                'lastName' => $input['lastName'],
                'extName' => $input['extName'],
                'username' => $input['username'],
                'email' => $input['email'],
                'secondaryEmail' => $input['secondaryEmail'],
                'sex' => $input['sex'],
                'region' => $input['region'],
                'province' => $input['province'],
                'municipality' => $input['municipality'],
                'agencyId' => $input['agencyId'],
                'stationId' => $input['stationId'],
                'position' => $input['position'],
                'designation' => $input['designation'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // delete user roles
            DB::table('role_user')
            ->where('userId', $userId)
            ->delete();

            // add user roles
            foreach ($input['roles'] as $key => $value) {
                DB::table('role_user')
                ->insert([
                    'userId' => $userId,
                    'roleId' => $value
                ]);
            }

            DB::commit();
            $request->session()->flash('success', 'Updated user successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Error updating user.');
        }

        return redirect()->route('users.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        // User::find($id)->delete();
        // return redirect()->route('users.index')
        // ->with('success','User deleted successfully');
    }

    private function full_name($firstName, $middleName, $lastName, $extName)
    {
        $name = "";
        $middleInitial = "";

        if (!empty($middleName)) {
            $middleName = explode(" ", $middleName);

            if ($middleName > 1) {
                foreach ($middleName as $i) {
                    $middleInitial .= $i[0] . '.';
                    $name = $firstName . ' ' . $middleInitial . ' ' . $lastName;
                }
            } else {
                foreach ($middleName as $i) {
                    $middleInitial .= $i[0];
                    $name = $firstName . ' ' . $lastName;
                }
            }
        } else {
            $name = $firstName . ' ' . $lastName;
        }

        if (!empty($extName)) {
            $name .=  ' ' . $extName;
        }

        return $name;
    }

    public function province(Request $request)
    {
        $provCode = $request->input('provCode');

        $municipalities = DB::table('lib_municipalities')
        ->select('citymunDesc', 'citymunCode')
        ->where('provCode', $provCode)
        ->get();

        echo json_encode($municipalities);
    }

    public function region(Request $request)
    {
        $provCode = $request->input('provCode');

        $region = DB::table('lib_provinces')
        ->select('regCode')
        ->where('provCode', $provCode)
        ->first();

        echo json_encode($region);
    }

    public function datatable()
    {
      
        if(Auth::user()->roles->first()->name == "branch-it" && Auth::user()->username != "rs.jandoc-ces"){

      
                $users = DB::table('users')
                ->select('userId', 'firstName', 'middleName', 'lastName', 'extName', 'email', 'username', 'municipality', 'isDeleted')
                ->where('isDeleted', 0)
                ->where("stationId",Auth::user()->stationId)
                ->get();
            
         
        }else{
            $users = DB::table('users')
            ->select('userId', 'firstName', 'middleName', 'lastName', 'extName', 'email', 'username', 'municipality', 'isDeleted')
            ->where('isDeleted', 0)
            ->get();
         
        }
       
        $data = array();


        foreach ($users as $item) {
    
            $roles = DB::table('roles')
            ->leftJoin('role_user', 'role_user.roleId', '=', 'roles.roleId')
            ->select('roles.display_name')
            ->where('role_user.userId', $item->userId)
            ->get();

            if ($item->extName == '') {
                $name = $item->firstName . ' ' . $item->lastName;
            } else {
                $name = $item->firstName . ' ' . $item->lastName . ', ' . $item->extName;
            }

            $getPrvMun = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('province', 'municipality')
            ->where('prv', 'LIKE', $item->municipality)
            ->first();

            if($getPrvMun)
            {
                $province = $getPrvMun->province;
                $municipality = $getPrvMun->municipality;
            }
            else
            {
                $province = 'N/A';
                $municipality = 'N/A';
            }

            $data[] = array(
                'userId' => $item->userId,
                'name' => $name,
                'email' => $item->email,
                'province' => $province,
                'municipality' => $municipality,
                'username' => $item->username,
                'lastName' => $item->lastName,
                'firstName' => $item->firstName,
                'middleName' => $item->middleName,
                'extName' => $item->extName,
                
                'isDeleted' => $item->isDeleted,
                'roles' => $roles
            );
        }

        $data = collect($data);


        return Datatables::of($data)
        ->addColumn('roles', function($data) {
            $roles = '';
            foreach ($data['roles'] as $item) {
                $roles .= '<span class="label label-primary">'.$item->display_name.'</span>&nbsp;';
            }
            return $roles;
        })
        ->addColumn('status', function($data) {
            if ($data['isDeleted'] == 0) {
                return '<button class="btn btn-success">Active</button>';
            } else {
                return '<button class="btn btn-danger">Inactive</button>';
            }
        })
        ->addColumn('actions', function($data) {
            $button = '<a href="'.route('users.show', $data['userId']).'" class="btn btn-info actionBtn" title="View"><i class="fa fa-eye"></i> View</a>&nbsp;';

            if (Auth::user()->can('user-edit')) {
                $button .= '<a href="'.route('users.edit', $data['userId']).'" class="btn btn-warning actionBtn" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }

            if (Auth::user()->can('user-delete')) {
                if ($data['isDeleted'] == 0) {
                    $button .= '<a href="#" class="btn btn-danger actionBtn" title="Deactivate"><i class="fa fa-times"></i> Deactivate</a>';
                } else {
                    $button .= '<a href="#" class="btn btn-success actionBtn" title="Activate"><i class="fa fa-check"></i> Activate</a>';
                }
            }

            foreach ($data['roles'] as $item) {
                if($item->display_name == 'Seed Grower' || $item->display_name == 'Delivery Manager'){
                    $coop_details = DB::connection('mysql')->table('users_coop')->where('userId', '=', $data['userId'])->first();
                    if(count($coop_details) > 0){
                        $button .= '<a href="#" data-id="'.$data['userId'].'" data-name="'.$data['name'].'" data-coop="'.$coop_details->coopAccreditation.'" class="btn btn-default actionBtn open-updateAcxreditation" data-toggle="modal" data-target="#update_accre_modal" title="'.$coop_details->coopAccreditation.'">'.$coop_details->coopAccreditation.'</a>&nbsp;';
                    }else{
                        $button .= '<a href="#" data-id="'.$data['userId'].'" data-name="'.$data['name'].'" class="btn btn-success actionBtn open-assignModal" data-toggle="modal" data-target="#assignModal" title="Assign accreditation #"><i class="fa fa-tag"></i> Tag to seed coop</a>&nbsp;';
                    }
                    
                }
            }

            $button .= '<a href="#" data-id="'.$data['userId'].'" class="btn btn-danger actionBtn open-assignProvince" data-toggle="modal" data-target="#assignProvince" title="Assign accreditation #"><i class="fa fa-map-marker"></i> Change Tagged Address</a>&nbsp;';
            $button .= '<a href="#" data-id="'.$data['userId'].'" data-last_name="'.$data['lastName'].'" data-first_name="'.$data['firstName'].'" data-mid_name="'.$data['middleName'].'" data-ext_name="'.$data['extName'].'" class="btn btn-warning actionBtn open-changeInfo" data-toggle="modal" data-target="#changeInfo"><i class="fa fa-user"></i> Update Information</a>&nbsp;';
            $button .= '<a href="#" data-id="'.$data['userId'].'" class="btn btn-warning actionBtn open-changeRole" data-toggle="modal" data-target="#changeRole"><i class="fa fa-user-plus"></i> Change Role</a>&nbsp;';


            $button .= '<a href="#" data-id="'.$data['userId'].'" class="btn btn-warning actionBtn open-resetPassword" data-toggle="modal" data-target="#reset_password_modal"><i class="fa fa-unlock-alt"></i> RESET PASSWORD</a>&nbsp;';

            return $button;
        })
        ->make(true);
    }

    public function updateProvince(Request $request){
     

        DB::table($GLOBALS['season_prefix']."sdms_db_dev.users")
        ->where("userId", $request->prv_userID)
        ->update([
            "province" => $request->changeProvince,
            "municipality" => $request->changeMunicipality
        ]);
        

        return redirect('users');


    }


    public function updateInfo(Request $request){
        
        // dd($request->all());

        $userID = $request->info_userID;
        $lastName = $request->lastName;
        $firstName = $request->firstName;
        $midName = $request->midName;
        $extName = $request->extName;

        DB::beginTransaction();
        try {
           
            DB::table("users")
                ->where("userId", $userID)
                ->update([
                    "lastName" => $lastName,
                    "firstName" => $firstName,
                    "middleName" => $midName,
                    "extName" => $extName
                ]);

                DB::commit();


            $request->session()->flash('success', 'Updated user successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'There is an error while updating the user.');
              return $e;
        }

     
             return redirect()->route('users.index');
        
        

    }


    public function updateRole(Request $request){
        $roleID = $request->changeRoleSelect;
        $userID = $request->role_userID;
        DB::beginTransaction();
        try {
           
             // delete user roles
            DB::table('role_user')
            ->where('userId', $userID)
            ->delete();

            // add user roles
            DB::table('role_user')
            ->insert([
                'userId' => $userID,
                'roleId' => $roleID
            ]);

            DB::commit();
            $request->session()->flash('success', 'Updated user successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'There is an error while updating the user.');
            // return $e;
        }

        return redirect()->route('users.index');
        

    }

    public function provinceData(Request $request){

        $user_prv = DB::table("users")
            ->where("userId", $request->userID)
            ->value("province");

        if(Auth::user()->roles->first()->name == "branch-it"){
            $station_name = DB::table("geotag_db2.tbl_station")
                ->where("stationId",Auth::user()->stationId)
                ->first();
         
                if(count($station_name)>0){
                    $provinces = DB::table($GLOBALS['season_prefix']."sdms_db_dev.lib_station")
                        ->select("province")
                        ->where("station", $station_name->stationName)
                        ->groupBy("province")
                        ->get();

                    $provinces = json_decode(json_encode($provinces),true);
           
                    $provinces = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                        ->select("province","prv_code")
                        ->whereIn("province", $provinces)
                        ->orderBy("region_sort")
                        ->groupBy("province")
                        ->get();
                    $provinces = json_decode(json_encode($provinces),true);

                }else{
                    $provinces = array();
                }
         }else{
            $provinces = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->select("province","prv_code")
                ->orderBy("region_sort")
                ->groupBy("province")
                ->get();
            $provinces = json_decode(json_encode($provinces),true);
        }

        $select = "";
        foreach($provinces as $data){
     
            $selected = "";
            if($data["prv_code"] == $user_prv){
                $selected = "selected";
            }

            $select .= "<option value='".$data["prv_code"]."' ";
            $select .= $selected.">";
            $select .= $data['province']."</option>";

          //  $select .= "<option value='".$data["prv_code"]."' $selected>".$data["province"]."</option>";

        }

        return $select;
    }

    public function municipalityData(Request $request){

        $user_prv = DB::table("users")
            ->where("userId", $request->userID)
            ->value("municipality");

            $municipality = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
                ->select("municipality","prv")
                ->where("prv_code", $request->province)
                ->orderBy("region_sort")
                ->groupBy("municipality")
                ->get();
            $municipality = json_decode(json_encode($municipality),true);
        

        $select = "";
        foreach($municipality as $data){
     
            $selected = "";
            if($data["prv"] == $user_prv){
                $selected = "selected";
            }

            $select .= "<option value='".$data["prv"]."' ";
            $select .= $selected.">";
            $select .= $data['municipality']."</option>";

          //  $select .= "<option value='".$data["prv_code"]."' $selected>".$data["province"]."</option>";

        }

        return $select;
    }

    public function resetPassword(Request $request){
        $this->validate($request, [
            'userID_reset' => 'required',
            'reset_pass' => 'required'
        ]);

        $new_password = Hash::make($request->reset_pass);
        $user_name = DB::connection('mysql')->table('users')->where('userId', $request->userID_reset)->value('username');

        DB::connection('mysql')->table('users')
        ->where('userId', $request->userID_reset)
        ->update([
            'password' => $new_password
        ]);

        Session::flash('success', 'Successfully updated password of user (`'.$user_name.'`)');
        return redirect()->route('users.index');
    }
	
	public function user_changePassword(Request $request){
        $this->validate($request, [
            'new_pass' => 'required',
            'confirm_pass' => 'required'
        ]);

       
        DB::beginTransaction();
        try {
            $new_pass = $request->new_pass;
            $confirm_pass = $request->confirm_pass;
            
            DB::connection('mysql')->table('users')
            ->where('userId', Auth::user()->userId)
            ->update([
                'password' => Hash::make($new_pass),
                'updated_at' => date("Y-m-d H:i:s")
            ]);

            DB::commit();
            return "password_updated";
        } catch (\Exception $e) {
            DB::rollback();
            return "sql_error";
        }       
    }
}

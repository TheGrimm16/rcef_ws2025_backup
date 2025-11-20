<?php

namespace App\Http\Controllers;




use Illuminate\Support\Collection;
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
        $data = [];
        $data['api_token'] = Auth::user()->api_token;

        $roles_filtered = [
            "branch-it", "buffer-inspector", "dro", "delivery-manager",
            "ebinhi-implementor","rcef-pmo","system-encoder",
            "techno_demo_officer","seed-grower","administrator"
        ];

        // Get all user roles as array safely
        $authRoles = Auth::user()->roles;

        $userRoles = [];
        if (is_array($authRoles)) {
            foreach ($authRoles as $r) {
                $userRoles[] = is_array($r) ? $r['name'] : $r->name;
            }
        } elseif ($authRoles instanceof \Illuminate\Support\Collection) {
            $userRoles = $authRoles->pluck('name')->toArray();
        }

        // Determine first role safely
        $firstRole = '';
        if (!empty($authRoles)) {
            $first = is_array($authRoles) ? reset($authRoles) : $authRoles->first();
            $firstRole = is_array($first) ? $first['name'] : $first->name;
        }

        // Prepare roles list for dropdown
        if ($firstRole === 'rcef-programmer') {
            $rolesCollection = DB::table('roles')->get();
        } else {
            $rolesCollection = DB::table('roles')
                ->whereIn('name', $roles_filtered)
                ->get();
        }

        // Convert collection to simple array for Blade
        $roles = [];
        foreach ($rolesCollection as $r) {
            $roles[$r->roleId] = $r->display_name;
        }

        // Access control
        if (in_array('rcef-programmer', $userRoles) || in_array('branch-it', $userRoles)) {
            return view('users.index', compact('data', 'roles', 'firstRole'));
        } elseif (in_array('branch-it', $userRoles) && Auth::user()->stationId != "0") {
            return view('users.index', compact('data', 'roles', 'firstRole'));
        } else {
            $mss = "No Access Privilege";
            return view('utility.pageClosed', compact('mss'));
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

            // $getPrvMun = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            // ->select('province', 'municipality')
            // ->where('prv', 'LIKE', $item->municipality)
            // ->first();
public function datatable()
{
    $currentUser = Auth::user();
    
    // Base query
    // Ensure the connection uses utf8_unicode_ci for this session
    DB::statement("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");

    $usersQuery = DB::table('users as u')
        ->leftJoin('role_user as ru', 'ru.userId', '=', 'u.userId')
        ->leftJoin('roles as r', function($join) {
            $join->on('r.roleId', '=', 'ru.roleId')
                ->where('r.isDeleted', '=', 0);
        })
        ->leftJoin('users_coop as uc', 'uc.userId', '=', 'u.userId')
        ->leftJoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv as p', function($join) {
            $join->on(DB::raw("CONVERT(p.prv USING utf8) COLLATE utf8_general_ci"), '=', 'u.municipality');
        })
        ->select(
            'u.userId',
            DB::raw("CONCAT_WS(' ', u.firstName, u.middleName, u.lastName, u.extName) AS name"),
            'u.firstName',
            'u.middleName',
            'u.lastName',
            'u.extName',
            'u.username',
            'u.email',
            'p.province',
            'p.municipality',
            'u.isDeleted',
            DB::raw("GROUP_CONCAT(r.display_name SEPARATOR '|') AS role_list"),
            'uc.coopAccreditation'
        )
        ->where('u.isDeleted', 0)
        ->groupBy('u.userId');


    // Branch-IT restriction
    $userRoles = collect($currentUser->roles)->pluck('name')->toArray();
    if (in_array('branch-it', $userRoles) && $currentUser->username != "rs.jandoc-ces") {
        $usersQuery->where('u.stationId', $currentUser->stationId);
    }

    return Datatables::of($usersQuery)
        ->filter(function($query) {
            if (request()->has('search') && $search = request('search')['value']) {
                $search = strtolower($search);
                $query->where(function($q) use ($search) {
                    // Name search
                    $q->whereRaw("LOWER(CONCAT_WS(' ', u.firstName, u.middleName, u.lastName, u.extName)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(CONCAT_WS(' ', u.firstName, u.lastName)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(CONCAT_WS(' ', u.lastName, u.firstName)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(u.firstName) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(u.lastName) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(u.username) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(u.email) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(u.province) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(u.municipality) LIKE ?", ["%{$search}%"]);
                });
            }
        })
        ->addColumn('roles', function($user) {
            if (empty($user->role_list)) return '<span class="label label-default">No roles</span>';
            $roles = explode('|', $user->role_list);
            $html = '';
            foreach ($roles as $r) {
                $html .= '<span class="label label-primary">'.$r.'</span> ';
            }
            return $html;
        })
        ->addColumn('status', function($user) {
            return $user->isDeleted ? '<button class="btn btn-danger">Inactive</button>' 
                                    : '<button class="btn btn-success">Active</button>';
        })
        ->addColumn('actions', function($user) {
            $fullName = trim($user->firstName.' '.$user->middleName.' '.$user->lastName.' '.$user->extName);

            $button = '<a href="'.route('users.show', $user->userId).'" class="btn btn-info actionBtn" title="View"><i class="fa fa-eye"></i> View</a>&nbsp;';

            if (Auth::user()->can('user-edit')) {
                $button .= '<a href="'.route('users.edit', $user->userId).'" class="btn btn-warning actionBtn" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }

            if (Auth::user()->can('user-delete')) {
                $button .= $user->isDeleted == 0
                    ? '<a href="#" class="btn btn-danger actionBtn" title="Deactivate"><i class="fa fa-times"></i> Deactivate</a>'
                    : '<a href="#" class="btn btn-success actionBtn" title="Activate"><i class="fa fa-check"></i> Activate</a>';
            }

            if (!empty($user->role_list) && preg_match('/Seed Grower|Delivery Manager/', $user->role_list)) {
                if ($user->coopAccreditation) {
                    $button .= '<a href="#" data-id="'.$user->userId.'" data-name="'.$fullName.'" data-coop="'.$user->coopAccreditation.'" class="btn btn-default actionBtn open-updateAcxreditation" data-toggle="modal" data-target="#update_accre_modal" title="'.$user->coopAccreditation.'">'.$user->coopAccreditation.'</a>&nbsp;';
                } else {
                    $button .= '<a href="#" data-id="'.$user->userId.'" data-name="'.$fullName.'" class="btn btn-success actionBtn open-assignModal" data-toggle="modal" data-target="#assignModal" title="Assign accreditation #"><i class="fa fa-tag"></i> Tag to seed coop</a>&nbsp;';
                }
            }

            return $button;
        })
        ->escapeColumns([])
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

        if(Auth::user()->roles->first()->name == "branch-it" && Auth::user()->username != "rs.jandoc-ces"){
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

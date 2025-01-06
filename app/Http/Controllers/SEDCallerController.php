<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use DB;
use Excel;
use Hash;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class SEDCallerController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $prov = Auth::user()->province;

        if ($prov != "" && $prov != null) {
            // $db = "rpt_".$prov;
            // $query = DB::table('INFORMATION_SCHEMA.SCHEMATA')
            // ->where('SCHEMA_NAME', $db)
            // ->exists();

            $query = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
                ->select('*')
                ->where('created_by', Auth::user()->userId)
                ->groupBy('muni_code')
                ->get();

            // if(count($query) > 0){
            //      $return['status'] = 1;
            //      $return['message'] = "Data process";
            // }else{
            //      $return['status'] = 0;
            //      $return['message'] = "Sorry, data for this municipality is not yet process";
            // }
        } else {
            $return['status'] = 0;
            $return['message'] = "Please assign municipality for this user";
        }

        // $db = "rpt_".Auth::user()->province;
        // $tbl = "tbl_".Auth::user()->municipality;

        return view('sed.farmers', compact('return', 'query'));
    }

    public function callers_dashboard(Request $request)
    {
        return view('sed.include.farmers_ver_datatable', compact('request'));
    }

    public function enumarators()
    {
        return view('sed.enumarators');
    }

    public function callers_management()
    {
        $data['api_token'] = Auth::user()->api_token;
        return view('sed.callers', compact('data'));
    }

    public function manage_farmer()
    {
        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('isEbinhi', 1)
            ->orderBy('province', 'asc')
            ->pluck('province', 'prv_code');

        $users = DB::table('users')
            ->select("*")
            ->addSelect(DB::raw("CONCAT(firstName,' ',lastName) as fullname"))
            ->leftJoin("role_user", "role_user.userId", "=", "users.userId")
            ->leftJoin("roles", "roles.roleId", "=", "role_user.roleId")
            ->where("roles.name", "sed-caller")
            ->where("users.isDeleted", 0)
            ->get();

        $week_start = strtotime('monday this week');
        $week_end = strtotime('sunday 23:59:59');

        $datedefault = date('m/d/Y H:i:s', $week_start) . " - " . date('m/d/Y H:i:s', $week_end);

        return view('sed.farmers_manage', compact('provinces', 'users', 'datedefault'));
    }

    public function user_form()
    {
        $users = [];

        // $provinces = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
        //      ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'sed_verified.prv_code', '=', 'lib_provinces.provCode')
        //      ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function($join){
        //           $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
        //      })
        //      ->where('lib_prv.isEbinhi', 1)
        //      ->whereNotNull('prv_code')
        //      ->orderBy('provDesc', 'asc')
        //      ->groupBy('lib_provinces.provCode')
        //      ->pluck('lib_provinces.provDesc', 'lib_provinces.provCode');

        $provinces = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->where('lib_prv.isEbinhi', 1)
            ->where('lib_prv.localLoad', 1)
            ->orderBy('province', 'asc')
            ->groupBy('lib_prv.provCode')
            ->get();

        // dd($provinces);
        return view('sed.include.sed_caller_form', compact('users', 'provinces'));
    }

    public function assign_municipality(Request $request)
    {
        $input = $request->all();
        $userId = $input['userID'];

        $users = DB::table('users')
            ->select('userId', 'firstName', 'middleName', 'lastName', 'extName', 'email', 'username', 'province', 'municipality')
            ->where('userId', $userId)
            ->first();

        $provinces = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'sed_verified.prv_code', '=', 'lib_provinces.provCode')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->whereNotNull('sed_verified.prv_code')
            ->orderBy('provDesc', 'asc')
            ->groupBy('lib_provinces.provCode')
            ->pluck('lib_provinces.provDesc', 'lib_provinces.provCode');

        $municipalities = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'sed_verified.muni_code', '=', 'lib_municipalities.citymunCode')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('lib_municipalities.provCode', $users->province)
            ->whereNotNull('muni_code')
            ->orderBy('citymunDesc', 'asc')
            ->groupBy('citymunCode')
            ->pluck('citymunDesc', 'citymunCode');

        return view('sed.include.assign_municipality', compact('users', 'provinces', 'municipalities'));
    }

    public function assign_allocation_details(Request $request)
    {
        $data['min'] = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where('muni_code', $request->muni_code)
            ->whereNull('created_by')
            ->min('farmer_id');

        $data['max'] = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where('muni_code', $request->muni_code)
            ->max('farmer_id');

        $data['unallocated'] = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where('muni_code', $request->muni_code)
            ->whereNull('created_by')
            ->count();

        return $data;
    }

    public function save_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|max:100',
            'middleName' => 'max:50',
            'lastName' => 'required|max:100',
            'province' => 'required|max:100',
            'municipality' => 'required|max:100',
            'extName' => 'max:20',
            'username' => 'required|unique:users,username|max:255',
            'email' => 'required|email|unique:users,email|max:150',
            'secondaryEmail' => 'email|unique:users,email|max:150',
            'password' => 'required|same:confirmPassword|min:6',
            'contact_no' => 'regex:/^(09)[0-9]{9}$/',
            // 'stationId' => 'required_if:agencyId,1',
        ], [
            'password.same' => 'Password does not match the confirm password',
            'contact_no.regex' => 'Invalid contact nnumber',
            // 'stationId.required_if' => 'The station field is required'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return ['status' => 1, 'message' => $errors->all()];
        }

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
                    'region' => '',
                    'province' => $input['province'],
                    'municipality' => $input['municipality'],
                    'agencyId' => '',
                    'stationId' => '',
                    'position' => '',
                    'designation' => '',
                    'api_token' => $api_token,
                    'contact_no' => $input['contact_no'],
                ]);

            // add user roles
            $roles = DB::table('roles')
                ->select('*')
                ->where('name', 'sed-caller')
                ->first();
            DB::table('role_user')
                ->insert([
                    'userId' => $userId,
                    'roleId' => $roles->roleId,
                ]);

            DB::commit();
            return ['status' => 0, 'message' => 'Added user successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            // Session::flash('error', 'Error adding user.');
            return ['message' => 'Error adding user.'];
        }

    }

    public function edit_municipality(Request $request)
    {
        Validator::extend('lessthanorequal', function ($attribute, $value, $parameters, $validator) {
            if ($value <= $parameters[0]) {
                return true;
            }
            return false;
        });
        Validator::extend('greaterthanorequal', function ($attribute, $value, $parameters, $validator) {
            if ($value >= $parameters[0]) {
                return true;
            }
            return false;
        });

        $validator = Validator::make($request->all(), [
            'municipality' => 'required',
            'province' => 'required',
            'max' => 'required|lessthanorequal:' . $request->max_id . '|greaterthanorequal:' . $request->min_id,
        ],
            [
                'lessthanorequal' => "max id should be less than or equal to total no of farmers.",
                'greaterthanorequal' => "max id should be greater than or equal to min id.",
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorArray = [];

            if ($errors->has('municipality')) {
                $errorArray[] = ['key' => 'municipality', 'value' => $errors->first('municipality')];
            }

            if ($errors->has('province')) {
                $errorArray[] = ['key' => 'province', 'value' => $errors->first('province')];
            }

            if ($errors->has('max')) {
                $errorArray[] = ['key' => 'max', 'value' => $errors->first('max')];
            }

            return ['status' => 4, 'message' => $errorArray, 'errors' => $errors->all()];
        }

        $input = $request->all();
        $userId = $input['userID'];
        DB::beginTransaction();
        try {
            // update user details
            DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
                ->where('userId', $userId)
                ->update([
                    'province' => $input['province'],
                    'municipality' => $input['municipality'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('muni_code', $input['municipality'])
                ->whereBetween('farmer_id', [$input['min_id'], $input['max']])
                ->update([
                    'created_by' => $userId,
                ]);

            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_assignment')
                ->insert([
                    'assigned_to' => $userId,
                    'muni_code' => $input['municipality'],
                    'min_id' => $input['min_id'],
                    'max_id' => $input['max'],
                    'assigned_by' => Auth::user()->userId,
                ]);

            DB::commit();
            return ['message' => 'Updated user successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['message' => 'Error updating user.'];
        }

    }

    public function farmers_datatable(Request $request)
    {
        $status = $request->status;
        // $db = "rpt_".Auth::user()->province;
        // $tbl = "tbl_".Auth::user()->municipality;

        // need to fetch in previous season
        $query = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',extename,' ',lname) as fullname"))
            ->where("muni_code", $request->municode)
            ->where(function ($q) use ($request) {
                $q->where('created_by', '=', Auth::user()->userId);
                // ->orWhere('created_by', '=', Auth::user()->userId);
            })
            ->where(function ($q) {
                $q->where('contact_no', 'LIKE', '%09%')
                    ->orWhere('contact_no', 'LIKE', '%9%')
                    ->orWhere('contact_no', 'LIKE', '%63%');
            });

        // if($status == 'verified'){
        //      $query = $query->where("status", "!=", 0);
        // }else if($status == 'unverified'){
        //      $query = $query->where("status", "=", 0);
        // }
        $query = $query->where(function ($q) {
            $q->where('status', '=', 0)
                ->orWhere('status', '=', 4);
        });
        $query = $query->orderBy('enableEdit', 'desc');

        // dd($query);
        return Datatables::of($query)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(fname,' ',extename,' ',lname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('actions', function ($query) {
                $href = "";
                if ($query->isPush == 0) {
                    $editClass = "";
                    $editName = "";
                    if ($query->enableEdit == 1) {
                        $editClass = "verifyFarmer";
                        $editName = "- please edit";
                    }
                    if ($query->status === 0) {
                        $href = '<button  class="btn btn-xs btn-warning verifyFarmer" data-id="' . $query->sed_id . '">VERIFY FARMER' . $editName . '</button>';
                    } else if ($query->status == 4) {
                        if ($query->created_by == Auth::user()->userId || $query->created_by == null) {
                            $href = '<button  class="btn btn-xs btn-success verifyFarmer" data-id="' . $query->sed_id . '"><i>pending...</i></button>';
                        } else {
                            $href = '<button  class="btn btn-xs btn-danger verifyFarmer" data-id="' . $query->sed_id . '"><i>pending - other user...</i></button>';
                        }

                    } else if ($query->status == 1 && $query->status == 5) {
                        if ($query->created_by == Auth::user()->userId || $query->created_by == null) {
                            $href = '<button  class="btn btn-xs btn-info ' . $editClass . '" data-id="' . $query->sed_id . '">ANSWERED YES' . $editName . '</button>';
                        }
                    } else if ($query->status == 3) {
                        if ($query->created_by == Auth::user()->userId || $query->created_by == null) {
                            $href = '<button  class="btn btn-xs btn-danger ' . $editClass . '" data-id="' . $query->sed_id . '">Failed Call' . $editName . '</button>';
                        }

                    } else if ($query->status == 2) {
                        if ($query->created_by == Auth::user()->userId || $query->created_by == null) {
                            $href = '<button  class="btn btn-xs btn-danger ' . $editClass . '" data-id="' . $query->sed_id . '">ANSWERED NO' . $editName . '</button>';
                        }
                    }
                } else {
                    $href = '<button  class="btn btn-xs btn-success" data-id="' . $query->sed_id . '">SYNC</button>';
                }

                return $href;
            })
            ->addColumn('has_claim', function ($query) {
                $has_claim = "";
                if ($query->has_claim == 1) {
                    $has_claim = '<span  class="btn btn-xs btn-warning" >Yes</span>';
                } else if ($query->has_claim == 0) {
                    $has_claim = '<span  class="btn btn-xs btn-success" >No</span>';
                }

                return $has_claim;
            })

            ->make(true);
    }

    public function verifyModal(Request $request)
    {
        // $db = "rpt_".Auth::user()->province;
        // $tbl = "tbl_".Auth::user()->municipality;
        $season = \Config::get('constants.season');
        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $ver_data = [];

        $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where([
                'sed_id' => $request->farmerid,
            ])
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->first();

        // $variety = DB::table('seed_seed.seed_characteristics')
        //      ->select('*')
        //      ->get();

        $region = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_dropoff_point')
            ->select('*')
            ->where('prv', $farmer->citymunCode)
            ->first();

        $variety = DB::table($GLOBALS['season_prefix'].'rcep_seed_cooperatives.tbl_commitment_regional')
            ->select("seed_variety")
            ->where('region_name', $region->region)
            ->groupBy('seed_variety')
            ->get();

        foreach ($variety as $v) {
            $seed_variety = preg_replace("/(19|20)[0-9][0-9]/", '', $v->seed_variety);
            $seed_variety = preg_replace("/\s+/", ' ', $seed_variety);
            $varieties[] = ['seed_variety' => $seed_variety];
        }
        $variety = collect($varieties)
            ->groupBy('seed_variety')
            ->toArray();

        $sowing_month = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks')
            ->select('*')
            ->where('season_code', $season_code)
            ->groupBy('season_month')
            ->orderBy('sw_id')
            ->get();

        $sowing_week = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks')
            ->select('*')
            ->where('season_code', $season_code)
            ->where('season_month', $farmer->sowing_month)
            ->orderBy('sw_id')
            ->get();

        return view('sed.include.verification_form', compact('variety', 'farmer', 'sed_verified', 'sowing_month', 'sowing_week'));
    }

    public function first_form_modal(Request $request)
    {

        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $ver_data = [];

        $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where([
                'sed_id' => $request->farmerid,
            ])
            ->first();

        if ($farmer->created_by != Auth::user()->userId && $farmer->created_by != null) {
            return ["error" => true, "message" => "This farmer is under verification by other user."];
        } else {
            if ($farmer->status == 0) {
                $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->where('sed_id', $request->farmerid)
                    ->update([
                        'created_by' => Auth::user()->userId,
                        'status' => 4,
                        'time_fetch' => date("Y-m-d H:i:s"),
                    ]);
            }

        }
        // forEdit value: 1 = edit for yes, 2 = edit for no
        return view('sed.include.first_form', compact('farmer'));
    }

    public function save_verified_data(Request $request)
    {
        $season = \Config::get('constants.season');
        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');

        Validator::extend('lessthanorequal', function ($attribute, $value, $parameters, $validator) {
            if ($value <= $parameters[0]) {
                return true;
            }
            return false;
        });

        Validator::extend('notequalzero', function ($attribute, $value, $parameters, $validator) {
            if ($value != 0) {
                return true;
            }
            return false;
        });

        Validator::extend('weightofbags', function ($attribute, $value, $parameters, $validator) {
            if ($value == null || $value == 0 || ($value >= 30 && $value <= 70)) {
                return true;
            }
            return false;
        });

        // Validator::extend('week_rule', function($attribute, $value, $parameters, $validator) {
        //      if($parameters[0] != 0){
        //           return true;
        //       }
        //           return false;
        // });

        $validator = Validator::make($request->all(), [
            'contactno' => 'required|max:12|min:10',
            // 'farm_area' => 'required|notequalzero|lessthanorequal:'.$request->actual_area,
            'farm_area' => 'required|notequalzero|lessthanorequal:999',
            'sowing_month' => 'required',
            'sowing_week' => 'required_unless:sowing_month,0',
            // 'areaharvested' => 'required_with:noofbags|required_with:weightofbags|notequalzero|lessthanorequal:'.$request->actual_area,
            // 'noofbags' => 'required_with:areaharvested|required_with:weightofbags|notequalzero|lessthanorequal:300',
            // 'weightofbags' => 'weightofbags',
            'contactno' => 'regex:/^(09)[0-9]{9}$/',
            'variety1' => 'required',
        ],
            [
                'lessthanorequal' => "The Farm Area must be less than or equal to Actual Area.",
                'areaharvested.notequalzero' => "Area harvested must be greater than 0",
                'noofbags.notequalzero' => "no. of bags must be greater than 0",
                'noofbags.lessthanorequal' => "no. of bags must be less than 300",
                'weightofbags.notequalzero' => "Weight per bag must be greater than 0",
                'farm_area.notequalzero' => "Farm area must be greater than 0",
                'sowing_week.required_unless' => "The sowing week field is required",
                'weightofbags.weightofbags' => "Not valid weight. (min. 30, max. 70)",
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorArray = [];

            if ($errors->has('contactno')) {
                $errorArray[] = ['key' => 'contactno', 'value' => $errors->first('contactno')];
            }

            if ($errors->has('farm_area')) {
                $errorArray[] = ['key' => 'farm_area', 'value' => $errors->first('farm_area')];
            }

            if ($errors->has('sowing_month')) {
                $errorArray[] = ['key' => 'sowing_month', 'value' => $errors->first('sowing_month')];
            }

            if ($errors->has('sowing_week')) {
                $errorArray[] = ['key' => 'sowing_week', 'value' => $errors->first('sowing_week')];
            }

            if ($errors->has('areaharvested')) {
                $errorArray[] = ['key' => 'areaharvested', 'value' => $errors->first('areaharvested')];
            }

            if ($errors->has('noofbags')) {
                $errorArray[] = ['key' => 'noofbags', 'value' => $errors->first('noofbags')];
            }

            if ($errors->has('weightofbags')) {
                $errorArray[] = ['key' => 'weightofbags', 'value' => $errors->first('weightofbags')];
            }

            if ($errors->has('variety1')) {
                $errorArray[] = ['key' => 'variety1', 'value' => $errors->first('variety1')];
            }

            return ['status' => 4, 'message' => $errorArray, 'errors' => $errors->all()];
        }

        DB::beginTransaction();
        try {
            // if($request->contactno != $request->contact_number){
            //      $contactno = $request->contactno;
            // }else{
            //     $contactno = "";
            // }
            // $prov = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
            // ->select('*')
            // ->where('provCode', $request->prv_code)
            // ->first();

            // $yield_ave = DB::table($GLOBALS['season_prefix'].'rcep_yield_encoding.lib_yield_inputs')
            //      ->select('*')
            //      ->where('province', $prov->provDesc)
            //      ->where('category', 'weight_per_bag')
            //      ->first();

            // if(count($yield_ave) > 0){
            //      $weight_ave = (intval($yield_ave->from_value) + intval($yield_ave->to_value)) / 2;
            // }else{
            //      $weight_ave = 50;
            // }

            // $yield = 0;
            // if($request->areaharvested > 0){
            //      if($request->weightofbags > 0){
            //           $yield = (floatval($request->noofbags) * floatval($request->weightofbags)) / floatval($request->areaharvested);
            //      }else{
            //           $yield = (floatval($request->noofbags) * floatval($weight_ave)) / floatval($request->areaharvested);
            //      }
            // }
            // dd($yield);
            $contactno = $request->contactno;
            $year = "";
            if ($request->sowing_month != "0") {
                $sowing_year = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks')
                    ->select('*')
                    ->where('season_month', $request->sowing_month)
                    ->where('season_code', $season_code)
                    ->groupBy('season_month')
                    ->first();

                $year = $sowing_year->season_year;
            }
            if (Auth::user()->roles->first()->name == "sed-caller-manager") {
                $update_data = [
                    'secondary_contact_no' => $contactno,
                    'committed_area' => $request->farm_area,
                    'ver_sex' => $request->sex,
                    'preffered_variety1' => $request->variety1,
                    'preffered_variety2' => $request->variety2,
                    // 'yield_no_bags' => $request->noofbags,
                    // 'yield_weight_bags' => $request->weightofbags,
                    // 'yield_area' => $request->areaharvested,
                    // 'yield' => $yield,
                    'sowing_year' => $year,
                    'sowing_month' => $request->sowing_month,
                    'sowing_week' => $request->sowing_week,
                    'isParticipating' => $request->participate,
                    'planted_variety' => $request->variety_planted,
                    'status' => 1,
                    'updated_by' => Auth::user()->userId,
                    'date_updated' => date("Y-m-d H:i:s"),
                    'enableEdit' => 0,
                ];
            } else {
                $update_data = [
                    'secondary_contact_no' => $contactno,
                    'committed_area' => $request->farm_area,
                    'ver_sex' => $request->sex,
                    'preffered_variety1' => $request->variety1,
                    'preffered_variety2' => $request->variety2,
                    // 'yield_no_bags' => $request->noofbags,
                    // 'yield_weight_bags' => $request->weightofbags,
                    // 'yield_area' => $request->areaharvested,
                    // 'yield' => $yield,
                    'sowing_year' => $year,
                    'sowing_month' => $request->sowing_month,
                    'sowing_week' => $request->sowing_week,
                    'isParticipating' => $request->participate,
                    'planted_variety' => $request->variety_planted,
                    'status' => 1,
                    // 'created_by' => Auth::user()->userId,
                    'time_save' => date("Y-m-d H:i:s"),
                    'enableEdit' => 0,
                ];
            }

            $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where([
                    'sed_id' => $request->farmerid,
                    'prv_code' => $request->prv_code,
                    'muni_code' => $request->muni_code,
                ])
                ->update($update_data);
            DB::commit();
            $return = ["status" => 1, "message" => "Farmer data updated successfuly"];
        } catch (\Exception $e) {
            DB::rollback();
            $return = ["status" => 0, "message" => $e];
        }
        return $return;
    }

    public function save_verified_data_no(Request $request)
    {

        Validator::extend('lessthanorequal', function ($attribute, $value, $parameters, $validator) {
            if ($value <= $parameters[0]) {
                return true;
            }
            return false;
        });
        Validator::extend('notequalzero', function ($attribute, $value, $parameters, $validator) {
            if ($value != 0) {
                return true;
            }
            return false;
        });

        $validator = Validator::make($request->all(), [
            'areaharvested' => 'required_with:noofbags|required_with:weightofbags|notequalzero|lessthanorequal:' . $request->actual_area,
            'noofbags' => 'required_with:areaharvested|required_with:weightofbags|notequalzero|lessthanorequal:300',
            'weightofbags' => 'weightofbags',
        ], [
            'lessthanorequal' => "The Farm Area must be less than or equal to Actual Area.",
            'areaharvested.notequalzero' => "Area harvested must be greater than 0",
            'noofbags.notequalzero' => "no. of bags must be greater than 0",
            'noofbags.lessthanorequal' => "no. of bags must be less than 300",
            'weightofbags.notequalzero' => "Weight per bag must be greater than 0",
            'farm_area.notequalzero' => "Farm area must be greater than 0",
            'weightofbags.weightofbags' => "Not valid weight. (min. 30, max. 70)",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorArray = [];

            if ($errors->has('areaharvested')) {
                $errorArray[] = ['key' => 'areaharvested', 'value' => $errors->first('areaharvested')];
            }

            if ($errors->has('noofbags')) {
                $errorArray[] = ['key' => 'noofbags', 'value' => $errors->first('noofbags')];
            }

            if ($errors->has('weightofbags')) {
                $errorArray[] = ['key' => 'weightofbags', 'value' => $errors->first('weightofbags')];
            }

            return ['status' => 4, 'message' => $errorArray, 'errors' => $errors->all()];
        }

        DB::beginTransaction();
        try {
            // if($request->contactno != $request->contact_number){
            //      $contactno = $request->contactno;
            // }else{
            //     $contactno = "";
            // }
            $prov = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
                ->select('*')
                ->where('provCode', $request->prv_code)
                ->first();

            $yield_ave = DB::table($GLOBALS['season_prefix'].'rcep_yield_encoding.lib_yield_inputs')
                ->select('*')
                ->where('province', $prov->provDesc)
                ->where('category', 'weight_per_bag')
                ->first();

            if (count($yield_ave) > 0) {
                $weight_ave = (intval($yield_ave->from_value) + intval($yield_ave->to_value)) / 2;
            } else {
                $weight_ave = 50;
            }

            $yield = 0;
            if ($request->areaharvested > 0) {
                if ($request->weightofbags > 0) {
                    $yield = (floatval($request->noofbags) * floatval($request->weightofbags)) / floatval($request->areaharvested);
                } else {
                    $yield = (floatval($request->noofbags) * floatval($weight_ave)) / floatval($request->areaharvested);
                }
            }

            if (Auth::user()->roles->first()->name == "sed-caller-manager") {
                $update_data = [
                    'yield_no_bags' => $request->noofbags,
                    'yield_weight_bags' => $request->weightofbags,
                    'yield_area' => $request->areaharvested,
                    'yield' => $yield,
                    'planted_variety' => $request->variety_planted,
                    'status' => 2,
                    'updated_by' => Auth::user()->userId,
                    'date_updated' => date("Y-m-d H:i:s"),
                    'enableEdit' => 0,
                ];
            } else {
                $update_data = [
                    'yield_no_bags' => $request->noofbags,
                    'yield_weight_bags' => $request->weightofbags,
                    'yield_area' => $request->areaharvested,
                    'planted_variety' => $request->variety_planted,
                    'status' => 2,
                    'yield' => $yield,
                    'created_by' => Auth::user()->userId,
                    'time_save' => date("Y-m-d H:i:s"),
                    'enableEdit' => 0,
                ];
            }
            $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where([
                    'sed_id' => $request->farmerid,
                    'prv_code' => $request->prv_code,
                    'muni_code' => $request->muni_code,
                ])
                ->update($update_data);
            DB::commit();
            $return = ["status" => 1, "message" => "Farmer data updated successfuly"];
        } catch (\Exception $e) {
            DB::rollback();
            $return = ["status" => 0, "message" => $e];
        }
        return $return;
    }

    public function verifyModalNo(Request $request)
    {
        // $db = "rpt_".Auth::user()->province;
        // $tbl = "tbl_".Auth::user()->municipality;
        $season = \Config::get('constants.season');
        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $ver_data = [];

        $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where([
                'sed_id' => $request->farmerid,
            ])
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->first();

        // $variety = DB::table('seed_seed.seed_characteristics')
        //      ->select('*')
        //      ->get();

        return view('sed.include.verification_form_no', compact('farmer'));
    }

    public function cancel_verified_data(Request $request)
    {

        DB::beginTransaction();
        try {
            $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where([
                    'sed_id' => $request->verified_id,
                ])
                ->first();

            if ($farmer->status == 4) {
                $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->where('sed_id', $request->verified_id)
                    ->update([
                        // 'created_by' => NULL,
                        'status' => 0,
                    ]);
            }

            DB::commit();

            $return = ["status" => 1, "message" => "Transaction cancelled"];
        } catch (\Exception $e) {
            DB::rollback();
            $return = ["status" => 0, "message" => $e];
        }
        return $return;
    }

    public function failedCall(Request $request)
    {

        DB::beginTransaction();
        try {
            if (Auth::user()->roles->first()->name == "sed-caller-manager") {
                $update_data = [
                    'status' => 3,
                    'updated_by' => Auth::user()->userId,
                ];
            } else {
                $update_data = [
                    'status' => 3,
                    'created_by' => Auth::user()->userId,
                ];
            }
            $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_id', $request->verified_id)
                ->update($update_data);
            DB::commit();

            $return = ["status" => 1, "message" => "Call attempt failed."];
        } catch (\Exception $e) {
            DB::rollback();
            $return = ["status" => 0, "message" => $e];
        }
        return $return;
    }

    public function province(Request $request)
    {
        $regCode = $request->input('regCode');

        $province = DB::table('lib_provinces')
            ->select('*')
            ->where('regCode', $regCode)
            ->get();

        echo json_encode($province);
    }

    public function municipality(Request $request)
    {
        $provCode = $request->input('provCode');

        $municipalities = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities')
        //->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified', 'sed_verified.muni_code', '=', 'lib_municipalities.citymunCode')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "lib_municipalities.citymunCode");
            })
            ->select('citymunDesc', 'citymunCode')
            ->where('lib_prv.isEbinhi', "LIKE", 1)
        //->whereNotNull('muni_code')
            ->where('lib_municipalities.provCode', $provCode)
            ->groupBy('citymunCode')
            ->get();

        echo json_encode($municipalities);
    }

    private function join_name($fname, $lname, $mname, $extname)
    {
        $fullname = "";

        if ($fname != null && $fname != "") {
            $fullname = $fname;
        }

        if ($mname != null && $mname != "") {
            $fullname .= " " . $mname;
        }

        if ($lname != null && $lname != "") {
            $lname .= " " . $lname;
        }

        if ($extname != null && $extname != "") {
            $extname .= " " . $extname;
        }

        return $fullname;
    }

    public function datatable()
    {
        $users = DB::table('users')
            ->select('users.userId', 'firstName', 'middleName', 'lastName', 'extName', 'email', 'username', 'users.isDeleted', 'roles.name', 'roles.display_name', 'province', 'municipality',
                DB::raw("(select count(sed_id) from ".$GLOBALS['season_prefix']."rcep_paymaya.sed_verified where sed_verified.isActive != 3 and sed_verified.created_by = users.userId) as total_count"))
            ->leftJoin('role_user', 'role_user.userId', '=', 'users.userId')
            ->leftJoin('roles', 'role_user.roleId', '=', 'roles.roleId')
            ->where('roles.name', 'sed-caller')
            ->where('users.isDeleted', 0)
            ->get();

        $data = array();

        foreach ($users as $item) {

            if ($item->extName == '') {
                $name = $item->firstName . ' ' . $item->lastName;
            } else {
                $name = $item->firstName . ' ' . $item->lastName . ', ' . $item->extName;
            }

            $data[] = array(
                'userId' => $item->userId,
                'name' => $name,
                'email' => $item->email,
                'username' => $item->username,
                'province' => $item->username,
                'municipality' => $item->municipality,
                'isDeleted' => $item->isDeleted,
                'roles' => $item->display_name,
                'total_count' => $item->total_count,
            );
        }

        $data = collect($data);

        return Datatables::of($data)
            ->addColumn('roles', function ($data) {
                $roles = '<span class="label label-primary">' . $data['roles'] . '</span>&nbsp;';

                return $roles;
            })
            ->addColumn('status', function ($data) {
                if ($data['isDeleted'] == 0) {
                    return '<button class="btn btn-success">Active</button>';
                } else {
                    return '<button class="btn btn-danger">Inactive</button>';
                }
            })
            ->addColumn('actions', function ($data) {
                $button = '<button data-id="' . $data['userId'] . '" class="btn btn-info actionBtn assignMunicipality" data-toggle="modal" data-target="#assignMunicipality"><i class="fa fa-edit"></i> Re-assign municipality</button>&nbsp;';
                $button .= '<button data-id="' . $data['userId'] . '" class="btn btn-danger actionBtn deleteUser"><i class="fa fa-trash"></i></button>&nbsp;';

                //   $button .= '<a href="#" data-id="'.$data['userId'].'" class="btn btn-warning actionBtn open-resetPassword" data-toggle="modal" data-target="#reset_password_modal"><i class="fa fa-unlock-alt"></i> RESET PASSWORD</a>&nbsp;';

                return $button;
            })
            ->make(true);
    }

    public function delete_user(Request $request)
    {
        $delete = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.users')
            ->where([
                'userId' => $request->id,
            ])
            ->update([
                'isDeleted' => 1,
            ]);

        if ($delete) {
            return ["message" => "User Deleted Successfully"];
        } else {
            return ["message" => "Failed to delete"];
        }
    }

    public function manage_farmers_datatable(Request $request)
    {

        $daterange = explode(" - ", $request->daterange);
        $date[] = date("Y-m-d", strtotime($daterange[0]));
        $date[] = date("Y-m-d", strtotime($daterange[1]));
        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) as sowing"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->whereBetween('date_sync', $date);

        if ($request->province != "") {
            $query = $query->where("prv_code", $request->province);
        }

        if ($request->municipality != "") {
            $query = $query->where("muni_code", $request->municipality);
        }

        if ($request->status != "") {
            if ($request->status == 0) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 0)
                        ->orWhere('status', "=", 4);
                });
            } else if ($request->status == 1) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 1);
                    $q->orWhere('status', "=", 5);
                })
                    ->where('sowing_month', "!=", '0');
            } else if ($request->status == 2) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 2);
                });

            } else if ($request->status == 3) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 3);
                });

            } else if ($request->status == 1) {
                $query = $query->where(function ($q) {
                    $q->where('status', '1')
                    ->orWhere('status', '5');
                })
                    ->where('sowing_month', "=", '0');
            }

        }

        if ($request->users != "") {
            $query = $query->where("created_by", $request->users);
        }

        return Datatables::of($query)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(fname,' ',midname,' ',lname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->filterColumn('sowing', function ($query, $keyword) {
                $sql = "CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })->addColumn('status', function ($query) {
            $href = "";
            // if($query->isPush == 0){
            //      if($query->status == 1){
            //           if($query->sowing_month === '0'){
            //                 $href = '<button  class="btn btn-xs btn-warning " data-id="'.$query->sed_id.'">Next Season</button>';
            //           }else{
            //                 $href = '<button  class="btn btn-xs btn-info " data-id="'.$query->sed_id.'">Edit</button>';
            //           }
            //       }else if($query->status == 3){
            //            $href = '<button  class="btn btn-xs btn-danger " data-id="'.$query->sed_id.'">Failed Call</button>';
            //       }else if($query->status == 2){
            //            $href = '<button  class="btn btn-xs btn-danger " data-id="'.$query->sed_id.'">Not Participating</button>';
            //       }
            // }else{
            //      $href = '<button  class="btn btn-xs btn-success" data-id="'.$query->sed_id.'">Completed</button>';
            // }
            if ($query->status == 1) {
                if ($query->sowing_month === '0') {
                    $href = '<button  class="btn btn-xs btn-warning " data-id="' . $query->sed_id . '">Next Season</button>';
                } else {
                    $href = '<button  class="btn btn-xs btn-info " data-id="' . $query->sed_id . '">Answered Yes</button>';
                }
            } else if ($query->status == 3) {
                $href = '<button  class="btn btn-xs btn-danger " data-id="' . $query->sed_id . '">Failed Call</button>';
            } else if ($query->status == 2) {
                if ($query->isActive == 2) {
                    $href = '<button  class="btn btn-xs btn-danger " data-id="' . $query->sed_id . '">Answered No - (Deceased Farmer)</button>';
                } else {
                    $href = '<button  class="btn btn-xs btn-danger " data-id="' . $query->sed_id . '">Answered No</button>';
                }
            }
            return $href;
        })->addColumn('sowing', function ($query) {
            if ($query->sowing_year == 0) {
                $sow = "";
            } else {
                $sow = $query->sowing;
            }

            return $sow;
        })
            ->make(true);
    }

//     public function manage_farmers_summary(Request $request)
//     {
//         $daterange = explode(" - ", $request->daterange);
//         $date[] = date("Y-m-d", strtotime($daterange[0]));
//         $date[] = date("Y-m-d", strtotime($daterange[1]));
//         $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
//             ->select('status')
//             ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
//             ->addSelect(DB::raw("CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) as sowing"));

//         $query = $query->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
//             ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
//                 $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
//             })
//             ->where('lib_prv.isEbinhi', 1)
//             ->where('isActive', '!=', 3);
//         $query = $query->whereBetween('date_sync', $date);

//         if ($request->province != "") {
//             $query = $query->where("sed_verified.prv_code", $request->province);
//         }

//         if ($request->municipality != "") {
//             $query = $query->where("muni_code", $request->municipality);
//         }

//         if ($request->status != "") {
//             if ($request->status == 0) {
//                 $query = $query->where(function ($q) {
//                     $q->where('status', "=", 0)
//                         ->orWhere('status', "=", 4);
//                 });
//             } else if ($request->status == 1) {
//                 $query = $query->where(function ($q) {
//                     $q->where('status', "=", 1);
//                 });
//                 // ->where('sowing_month', "!=", '0');
//             } else if ($request->status == 2) {
//                 $query = $query->where(function ($q) {
//                     $q->where('status', "=", 2);
//                 });

//             } else if ($request->status == 3) {
//                 $query = $query->where(function ($q) {
//                     $q->where('status', "=", 3);
//                 });

//             }
//             // else if($request->status == 1){
//             //      $query = $query->where(function($q) {
//             //           $q->where('status', "=", 1);
//             //      })
//             //      ->where('sowing_month', "=", '0');
//             // }

//         }

//         if ($request->users != "") {
//             $query = $query->where("created_by", $request->users);
//         }
//         $sql = $query->toSql();
//         $query = $query->get();

//         $collection = collect($query);
//      //    dd($collection);
//         $yes = $collection->where('status', 1)->count();
//         $yes_calls = $yes;
//         $no = $collection->where('status', 2)->count();
//         $failed = $collection->where('status', 3)->count();
//         $pending = $collection->where('status', 0)->count();

//         if ($request->province == "" || $request->province == "") {
//             $yes_calls = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
//                 ->select('*')
//                 ->where('status', 1)
//                //  ->where('isActive','!=', 3)
//                 ->whereBetween('date_sync', $date);
//             if ($request->users != "") {
//                 $yes_calls = $yes_calls->where("created_by", $request->users);
//             }

//             $yes_calls = $yes_calls->count();

//         }
//         dd($sql);
//         return view('sed.include.monitoring_summary', compact('yes', 'no', 'failed', 'pending', 'yes_calls'));
//     }

    public function manage_farmers_summary(Request $request)
    {
        $daterange = explode(" - ", $request->daterange);
        $date[] = date("Y-m-d", strtotime($daterange[0]));
        $date[] = date("Y-m-d", strtotime($daterange[1]));
        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('status')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->where('isActive', '!=', 3);
            
        $query = $query->whereBetween('date_sync', $date);
        if ($request->province != "") {
            $query = $query->where("sed_verified.prv_code", $request->province);
        }

        if ($request->municipality != "") {
            $query = $query->where("muni_code", $request->municipality);
        }

        if ($request->status != "") {
            if ($request->status == 0) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 0)
                        ->orWhere('status', "=", 4);
                });
            } else if ($request->status == 1) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 1);
                    $q->orWhere('status', "=", 5);
                });
                // ->where('sowing_month', "!=", '0');
            } else if ($request->status == 2) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 2);
                });

            } else if ($request->status == 3) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 3);
                });

            }

        }

        if ($request->users != "") {
            $query = $query->where("created_by", $request->users);
        }
        $sql = $query->toSql();
        $query = $query->get();

        $collection = collect($query);
     //    dd($collection);
        $yes = $collection->count();
     //    $yes_calls = $yes;
        $yes_calls = $collection->whereIn('status', ['1', '5'])->count();
        $no = $collection->where('status', '2')->count();
        $failed = $collection->where('status', '3')->count();
     //    $pending = $collection->where('status', '0')->count();
     //    dd($yes);
        if ($request->province == "" || $request->province == "") {
            $yes_calls = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('*')
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
               //  ->where('isActive','!=', 3)
                ->whereBetween('date_sync', $date);
            if ($request->users != "") {
                $yes_calls = $yes_calls->where("created_by", $request->users);
            }

            $yes_calls = $yes_calls->count();

        }

        $pending = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('*')
                ->where('status', 0);
             
            if ($request->users != "") {
                $pending = $pending->where("created_by", $request->users);
            }
          //   dd($pending);
            $pending = $pending->count();
       
        return view('sed.include.monitoring_summary', compact('yes', 'no', 'failed', 'pending', 'yes_calls'));
    }

    public function season_weeks(Request $request)
    {
        $month = $request->input('month');

        $weeks = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks')
            ->select('*')
            ->where('season_month', $month)
            ->get();

        echo json_encode($weeks);
    }

    // SED controller Dashboard
    public function dashboard()
    {
        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $season = \Config::get('constants.season');

        if ($season == "Dry Season") {
            $prev_season = (intval($season_year) - 1);
            $prev_season = 'DS' . $prev_season;
        } else {
            $prev_season = (intval($season_year) - 1);
            $prev_season = 'WS' . $prev_season;
        }

        // $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
        //      ->select('lib_provinces.*','lib_municipalities.*','lib_inspection_dates.*', 'sed_verified.muni_code')
        //      ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
        //      ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
        //      ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_inspection_dates', function($join) use ($prev_season){
        //           $join->on('lib_inspection_dates.prv', '=', "sed_verified.muni_code");
        //           $join->where('lib_inspection_dates.crop_season', '=', "'".$prev_season."'");
        //      })
        //      ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function($join){
        //           $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
        //      })
        //      ->where('lib_prv.isEbinhi', 1)
        //      ->groupBy('sed_verified.muni_code')
        //      ->orderBy('date_min')
        //      ->get();

        $query = DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
            ->select('lib_inspection_dates.*', 'lib_prv.*')
        // ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
        // ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_inspection_dates', function ($join) use ($prev_season) {
                $join->on('lib_inspection_dates.prv', '=', "lib_prv.prv");
                $join->where('lib_inspection_dates.crop_season', '=', $prev_season);
            })
        // ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function($join){
        //      $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
        // })
            ->where('lib_prv.isEbinhi', 1)
            ->where('lib_prv.localLoad', 1)
            ->groupBy('lib_prv.prv')
            ->orderBy('date_min')
            ->get();
        // dd($query);
        $data = [];
        $prv_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('sed_verified.prv_code')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->groupBy('sed_verified.prv_code')
            ->get();
        $summary['prov_count'] = count($prv_count);
        $summary['muni_count'] = count($query);
        $summary['res_count'] = 0;
        $summary['male_count'] = 0;
        $summary['fem_count'] = 0;

        foreach ($query as $q) {

            $male = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where('muni_code', $q->prv)
                ->whereRaw('LOWER(`ver_sex`) = "male"')
                ->count();

            $female = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where('muni_code', $q->prv)
                ->whereRaw('LOWER(`ver_sex`) = "female"')
                ->count();

            $farmer_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where('muni_code', $q->prv)
                ->count();

            $sched_min = "";
            $sched_max = "";
            if ($q->date_min != null) {
                $sched_min = date("M d, Y", strtotime($q->date_min));
            }

            if ($q->date_min != null) {
                $sched_max = date("M d, Y", strtotime($q->date_max));
            }

            $data[] = (object) [
                'id' => "prv_" . $q->prv,
                'muni_code' => $q->prv,
                'province' => $q->province,
                'municipality' => $q->municipality,
                'min_delivery_sched' => $sched_min,
                'max_delivery_sched' => $sched_max,
                'respondents' => $farmer_count,
                'male_count' => $male,
                'female_count' => $female,
            ];

            $summary['res_count'] = intval($summary['res_count']) + intval($farmer_count);
            $summary['male_count'] = intval($summary['male_count']) + intval($male);
            $summary['fem_count'] = intval($summary['fem_count']) + intval($female);

        }

        $summary['prov_count'] = number_format($summary['prov_count']);
        $summary['muni_count'] = number_format($summary['muni_count']);
        $summary['res_count'] = number_format($summary['res_count']);
        $summary['male_count'] = number_format($summary['male_count']);
        $summary['fem_count'] = number_format($summary['fem_count']);
        $data = (object) $data;
        return view('sed.dashboard', compact('data', 'summary'));
    }

    public function dashboard_datatable(Request $request)
    {
        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(sowing_week,' of ',sowing_month,', ',sowing_year) as sowing"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->addSelect(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName) as createdBy"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.users', 'users.userId', '=', "sed_verified.created_by")
            ->where('sed_verified.muni_code', $request->municode);

        return Datatables::of($query)
            ->filterColumn('createdBy', function ($query, $keyword) {
                $sql = "CONCAT(firstName,' ',middleName,' ',lastName) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('has_claim', function ($query) {
                $has_claim = "";
                if ($query->has_claim == 1) {
                    $has_claim = '<span  class="btn btn-xs btn-warning" >YES</span>';
                } else if ($query->has_claim == 0) {
                    $has_claim = '<span  class="btn btn-xs btn-success" >NO</span>';
                }

                return $has_claim;
            })
            ->addColumn('farm_area_ws2021', function ($query) {
                $farm_area_ws2021 = $query->farm_area_ws2021;
                if ($query->farm_area_ws2021 == 0) {
                    $farm_area_ws2021 = 'N/A';
                }
                return $farm_area_ws2021;
            })
            ->addColumn('farm_area_ds2021', function ($query) {
                $farm_area_ds2021 = $query->farm_area_ds2021;
                if ($query->farm_area_ds2021 == 0) {
                    $farm_area_ds2021 = 'N/A';
                }
                return $farm_area_ds2021;
            })
            ->make(true);
    }

    public function load_data_municipalities(Request $request)
    {
        $municode = $request->municode;
        return view('sed.include.dashboard_datatable', compact('municode'));
    }

    public function edit_farmer()
    {
        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $season = \Config::get('constants.season');

        if ($season == "Dry Season") {
            $prev_season = (intval($season_year) - 1);
            $prev_season = 'DS' . $prev_season;
        } else {
            $prev_season = (intval($season_year) - 1);
            $prev_season = 'WS' . $prev_season;
        }

        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('lib_provinces.*', 'sed_verified.muni_code', 'sed_verified.yield_no_bags', 'sed_verified.yield_weight_bags', 'sed_verified.yield_area', 'sed_verified.prv_code')
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('sed_verified.prv_code', '=', "lib_prv.prv_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where(function ($q) {
                $q->where('status', "!=", 0)
                    ->where('status', "!=", 4);
            })
            ->groupBy('sed_verified.prv_code')
            ->orderBy('provDesc')
            ->get();

        $data = [];

        $total_area_all = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('yield_area', '>', '0')
            ->sum('yield_area');

        $total_yield_all = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('yield_area', '>', '0')
            ->sum('yield');

        $with_yield_all = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('yield_area', '>', '0')
            ->count();

        $answered_yes = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where(function ($q) {
                $q->where('sed_verified.status', "=", 1);
                $q->orWhere('sed_verified.status', "=", 5);
            })
            ->where('sowing_month', "!=", 0)
            ->count();

        $answered_next = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.status', 1)
            ->where('sowing_month', "=", 0)
            ->count();

        $answered_no = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.status', 2)
            ->count();

        $failed_calls = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.status', 3)
            ->count();

        if ($total_yield_all > 0) {
            $total_yield_all = $total_yield_all / $with_yield_all;
        }

        $summary['with_yield_all'] = $with_yield_all;
        $summary['total_yield_all'] = $total_yield_all;
        $summary['total_area_all'] = $total_area_all;
        $summary['answered_yes'] = $answered_yes;
        $summary['failed_calls'] = $failed_calls;
        $summary['answered_no'] = $answered_no;
        $summary['answered_next'] = $answered_next;

        foreach ($query as $q) {

            $total_area = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where('yield_area', '>', '0')
                ->sum('yield_area');

            $total_yield = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where('yield_area', '>', '0')
                ->sum('yield');

            $with_yield = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where('yield_area', '>', '0')
                ->count();

            if ($total_yield > 0) {
                $total_yield = $total_yield / $with_yield;
            }

            $male = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->whereRaw('LOWER(`ver_sex`) = "male"')
                ->count();

            $female = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->whereRaw('LOWER(`ver_sex`) = "female"')
                ->count();

            $farmer_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->count();

            $respondents = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->where('sed_verified.prv_code', $q->prv_code)
                ->count();

            $yes_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', "!=", 0)
                ->count();

            $no_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where('status', 2)
                ->count();

            $next_season = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->where('sowing_month', 0)
                ->count();

            $failed_call = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where('status', 3)
                ->count();

            $expected_sowing_min = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('sowing_month', 'season_year')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks', 'lib_season_weeks.season_month', '=', "sed_verified.sowing_month")
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('lib_season_weeks.season_code', $season_code)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', "!=", 0)
                ->orderby('lib_season_weeks.sw_id')
                ->first();

            $expected_sowing_max = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('sowing_month', 'season_year')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks', 'lib_season_weeks.season_month', '=', "sed_verified.sowing_month")
                ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                    $join->on('sed_verified.muni_code', '=', "lib_prv.prv");
                })
                // ->where('lib_prv.isEbinhi', 1)
                ->where('lib_season_weeks.season_code', $season_code)
                ->where('sed_verified.prv_code', $q->prv_code)
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', "!=", 0)
                ->orderby('lib_season_weeks.sw_id', "desc")
                ->first();
            $sowing_min = "";
            $sowing_max = "";
            if (count($expected_sowing_min) > 0) {
                $sowing_min = $expected_sowing_min->sowing_month . " " . $expected_sowing_min->season_year;
            }
            if (count($expected_sowing_max) > 0) {
                $sowing_max = $expected_sowing_max->sowing_month . " " . $expected_sowing_max->season_year;
            }

            if($farmer_count == 0){
                $percentage = 0;
            }else{
                $percentage = ($respondents / $farmer_count) * 100;
            }

            
            $data[] = (object) [
                'id' => "prv_" . $q->prv_code,
                'muni_code' => $q->muni_code,
                'province' => $q->provDesc,
                'prv_code' => $q->prv_code,
                'farmer_count' => $farmer_count,
                'respondents' => $respondents,
                'percentage' => number_format($percentage, 2),
                'male' => $male,
                'female' => $female,
                'yes_count' => $yes_count,
                'no_count' => $no_count,
                'sowing_min' => $sowing_min,
                'sowing_max' => $sowing_max,
                'next_season' => $next_season,
                'failed_call' => $failed_call,
                'total_area' => $total_area,
                'total_yield' => number_format($total_yield, 2),
                'with_yield' => $with_yield,
            ];
        }
        $data = (object) $data;

        $summary['with_yield_all'] = number_format($summary['with_yield_all']);
        $summary['total_yield_all'] = number_format($summary['total_yield_all'], 2);
        $summary['total_area_all'] = number_format($summary['total_area_all']);
        $summary['answered_yes'] = number_format($summary['answered_yes']);
        $summary['failed_calls'] = number_format($summary['failed_calls']);
        $summary['answered_no'] = number_format($summary['answered_no']);
        $summary['answered_next'] = number_format($summary['answered_next']);

        return view('sed.verified_farmers', compact('data', 'summary'));
    }

    public function load_verified_municipalities_list(Request $request)
    {

        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $season = \Config::get('constants.season');

        if ($season == "Dry Season") {
            $prev_season = (intval($season_year) - 1);
            $prev_season = 'DS' . $prev_season;
        } else {
            $prev_season = (intval($season_year) - 1);
            $prev_season = 'WS' . $prev_season;
        }

        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('lib_provinces.*', 'lib_municipalities.*', 'lib_inspection_dates.*', 'sed_verified.muni_code', 'sed_verified.yield_no_bags', 'sed_verified.yield_weight_bags', 'sed_verified.yield_area')
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_inspection_dates', function ($join) use ($prev_season) {
                $join->on('lib_inspection_dates.prv', '=', "sed_verified.muni_code")
                    ->where('lib_inspection_dates.crop_season', '=', $prev_season);
            })
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            // ->where('lib_prv.isEbinhi', 1)
            ->where(function ($q) {
                $q->where('status', "!=", 0)
                    ->where('status', "!=", 4);
            })
            ->where('sed_verified.prv_code', $request->prv_code)
            ->groupBy('sed_verified.muni_code')
            ->orderBy('provDesc', 'citymunDesc')
            ->get();

        $data = [];

        $total_area_all = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('yield_area', '>', '0')
            ->sum('yield_area');

        $total_yield_all = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('yield_area', '>', '0')
            ->sum('yield');

        $with_yield_all = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('yield_area', '>', '0')
            ->count();

        $answered_yes = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('sed_verified.status', 1)
            ->where('sowing_month', "!=", 0)
            ->count();

        $answered_next = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('sed_verified.status', 1)
            ->where('sowing_month', "=", 0)
            ->count();

        $answered_no = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('sed_verified.status', 2)
            ->count();

        $failed_calls = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->leftjoin($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv', function ($join) {
                $join->on('lib_prv.prv', '=', "sed_verified.muni_code");
            })
            ->where('lib_prv.isEbinhi', 1)
            ->where('sed_verified.prv_code', $request->prv_code)
            ->where('sed_verified.status', 3)
            ->count();

        if ($total_yield_all > 0) {
            $total_yield_all = $total_yield_all / $with_yield_all;
        }

        $summary['with_yield_all'] = $with_yield_all;
        $summary['total_yield_all'] = $total_yield_all;
        $summary['total_area_all'] = $total_area_all;
        $summary['answered_yes'] = $answered_yes;
        $summary['failed_calls'] = $failed_calls;
        $summary['answered_no'] = $answered_no;
        $summary['answered_next'] = $answered_next;
        foreach ($query as $q) {

            // $yield_ave = DB::table($GLOBALS['season_prefix'].'rcep_yield_encoding.lib_yield_inputs')
            //      ->select('*')
            //      ->where('province', $q->provDesc)
            //      ->where('category', 'weight_per_bag')
            //      ->first();

            // if(count($yield_ave) > 0){
            //      $weight_ave = (intval($yield_ave->from_value) + intval($yield_ave->to_value)) / 2;
            // }else{
            //      $weight_ave = 50;
            // }
            // $yield = 0;
            // if($q->yield_area > 0){
            //      $yield = (floatval($q->yield_no_bags) * floatval($q->yield_weight_bags)) / floatval($q->yield_area);
            // }
            $total_area = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('muni_code', $q->muni_code)
                ->where('yield_area', '>', '0')
                ->sum('yield_area');

            $total_yield = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('muni_code', $q->muni_code)
                ->where('yield_area', '>', '0')
                ->sum('yield');

            $with_yield = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('muni_code', $q->muni_code)
                ->where('yield_area', '>', '0')
                ->count();

            if ($total_yield > 0) {
                $total_yield = $total_yield / $with_yield;
            }

            $male = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where('muni_code', $q->muni_code)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->whereRaw('LOWER(`ver_sex`) = "male"')
                ->count();

            $female = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where('muni_code', $q->muni_code)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->whereRaw('LOWER(`ver_sex`) = "female"')
                ->count();

            $farmer_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where('muni_code', $q->muni_code)
                ->count();

            $respondents = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('ver_sex')
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->where('muni_code', $q->muni_code)
                ->count();

            $yes_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->where('muni_code', $q->muni_code)
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', "!=", 0)
                ->count();

            $no_count = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->where('muni_code', $q->muni_code)
                ->where('status', 2)
                ->count();

            $next_season = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->where('muni_code', $q->muni_code)
                ->where(function ($q) {
                    $q->where('status', "!=", 0)
                        ->where('status', "!=", 4);
                })
                ->where('sowing_month', 0)
                ->count();

            $failed_call = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('status')
                ->where('muni_code', $q->muni_code)
                ->where('status', 3)
                ->count();

            $expected_sowing_min = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('sowing_month', 'season_year')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks', 'lib_season_weeks.season_month', '=', "sed_verified.sowing_month")
                ->where('lib_season_weeks.season_code', $season_code)
                ->where('muni_code', $q->muni_code)
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', "!=", 0)
                ->orderby('lib_season_weeks.sw_id')
                ->first();

            $expected_sowing_max = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->select('sowing_month', 'season_year')
                ->leftjoin($GLOBALS['season_prefix'].'rcep_paymaya.lib_season_weeks', 'lib_season_weeks.season_month', '=', "sed_verified.sowing_month")
                ->where('lib_season_weeks.season_code', $season_code)
                ->where('muni_code', $q->muni_code)
                ->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', "!=", 0)
                ->orderby('lib_season_weeks.sw_id', "desc")
                ->first();
            $sowing_min = "";
            $sowing_max = "";
            if (count($expected_sowing_min) > 0) {
                $sowing_min = $expected_sowing_min->sowing_month . " " . $expected_sowing_min->season_year;
            }
            if (count($expected_sowing_max) > 0) {
                $sowing_max = $expected_sowing_max->sowing_month . " " . $expected_sowing_max->season_year;
            }

            $percentage = ($respondents / $farmer_count) * 100;
            $data[] = (object) [
                'id' => "prv_mun_" . $q->muni_code,
                'muni_code' => $q->muni_code,
                'province' => $q->provDesc,
                'municipality' => $q->citymunDesc,
                'min_delivery_sched' => date("M d, Y", strtotime($q->date_min)),
                'max_delivery_sched' => date("M d, Y", strtotime($q->date_max)),
                'farmer_count' => $farmer_count,
                'respondents' => $respondents,
                'percentage' => number_format($percentage, 2),
                'male' => $male,
                'female' => $female,
                'yes_count' => $yes_count,
                'no_count' => $no_count,
                'sowing_min' => $sowing_min,
                'sowing_max' => $sowing_max,
                'next_season' => $next_season,
                'failed_call' => $failed_call,
                'total_area' => $total_area,
                'total_yield' => number_format($total_yield, 2),
                'with_yield' => $with_yield,
            ];
        }
        $data = (object) $data;

        $summary['with_yield_all'] = number_format($summary['with_yield_all']);
        $summary['total_yield_all'] = number_format($summary['total_yield_all'], 2);
        $summary['total_area_all'] = number_format($summary['total_area_all']);
        $summary['answered_yes'] = number_format($summary['answered_yes']);
        $summary['failed_calls'] = number_format($summary['failed_calls']);
        $summary['answered_no'] = number_format($summary['answered_no']);
        $summary['answered_next'] = number_format($summary['answered_next']);

        return view('sed.include.municipality_list', compact('data', 'summary'));

    }

    public function verified_farmers_datatable(Request $request)
    {
        $status = $request->status;
        // $db = "rpt_".Auth::user()->province;
        // $tbl = "tbl_".Auth::user()->municipality;

        // need to fetch in previous season
        // $prov_code = str_split($request->municode, 4);
        // $prov = DB::table($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces')
        //      ->select('*')
        //      ->where('provCode', $prov_code[0])
        //      ->first();

        // $yield_ave = DB::table($GLOBALS['season_prefix'].'rcep_yield_encoding.lib_yield_inputs')
        //      ->select('*')
        //      ->where('province', $prov->provDesc)
        //      ->where('category', 'weight_per_bag')
        //      ->first();

        // if(count($yield_ave) > 0){
        //      $weight_ave = (intval($yield_ave->from_value) + intval($yield_ave->to_value)) / 2;
        // }else{
        //      $weight_ave = 50;
        // }

        $query = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName) as createdBy"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.users', 'users.userId', '=', "sed_verified.created_by")
            ->where('muni_code', $request->municode)
            ->where(function ($q) {
                $q->where('created_by', '!=', null);
            })
            ->where(function ($q) {
                $q->where('status', "!=", 4);
                // ->where('status', "!=", 4);
            });

        if ($request->filter == 'yes') {
            $query = $query->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', '!=', 0)
                ->where('isPush', 0);
        }

        if ($request->filter == 'no') {
            $query = $query->where('status', 2)
                ->where('isPush', 0);
        }

        if ($request->filter == 'failed') {
            $query = $query->where('status', 3)
                ->where('isPush', 0);
        }

        if ($request->filter == 'next') {
            $query = $query->where(function ($q) {
                    $q->where('status', '1');
                    $q->orWhere('status', '5');
                })
                ->where('sowing_month', 0)
                ->where('isPush', 0);
        }

        if ($request->filter == 'pending') {
            $query = $query->where('status', 0)
                ->where('isPush', 0);
        }

        return Datatables::of($query)
            ->filterColumn('fullname', function ($query, $keyword) {
                $sql = "CONCAT(fname,' ',midname,' ',lname) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->filterColumn('createdBy', function ($query, $keyword) {
                $sql = "CONCAT(firstName,' ',middleName,' ',lastName) like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('actions', function ($query) {
                $href = "";
                if ($query->isPush == 0) {
                    if ($query->status == 1) {
                        if ($query->sowing_month === '0') {
                            $href .= '<button  class="btn btn-xs btn-warning verifyFarme" data-id="' . $query->sed_id . '">Next Season</button>';
                        } else {
                            $href .= '<button  class="btn btn-xs btn-info verifyFarme" data-id="' . $query->sed_id . '">Yes</button>';
                        }
                    } else if ($query->status == 3) {
                        $href .= '<button  class="btn btn-xs btn-danger verifyFarme" data-id="' . $query->sed_id . '">Call Failed</button>';
                    } else if ($query->status == 2) {
                        $href .= '<button  class="btn btn-xs btn-danger verifyFarme" data-id="' . $query->sed_id . '">No</button>';
                    }

                    //  if($query->status != 3){
                    if ($query->enableEdit == 0) {
                        $href .= '<button  class="btn btn-xs btn-success enableEdit" data-id="' . $query->sed_id . '" data-value="1">Enable Edit</button>';
                    } else {
                        $href .= '<button  class="btn btn-xs btn-danger enableEdit" data-id="' . $query->sed_id . '" data-value="0">Disable Edit</button>';
                    }
                    // }
                } else {
                    $href = '<button  class="btn btn-xs btn-success" data-id="' . $query->sed_id . '">Sync</button>';
                }

                return $href;
            })
            ->addColumn('has_claim', function ($query) {
                $has_claim = "";
                if ($query->has_claim == 1) {
                    $has_claim = '<span  class="btn btn-xs btn-warning" >YES</span>';
                } else if ($query->has_claim == 0) {
                    $has_claim = '<span  class="btn btn-xs btn-success" >NO</span>';
                }

                return $has_claim;
            })
            ->addColumn('committed_area', function ($query) {
                $committed_area = $query->committed_area;
                if ($query->committed_area == 0) {
                    $committed_area = 'N/A';
                }
                return $committed_area;
            })
        // ->addColumn('yield', function($q) use($weight_ave){

        //      $yield = "";
        //      if($q->yield_area > 0){

        //           if($q->yield_weight_bags > 0){
        //                $yield .= "<small>(".$q->yield_no_bags." * ".$q->yield_weight_bags.") / ".$q->yield_area." = </small>";
        //                $yield .= (floatval($q->yield_no_bags) * floatval($q->yield_weight_bags)) / floatval($q->yield_area);
        //           }else{
        //                $yield .= "<small>(".$q->yield_no_bags." * ".$weight_ave.") / ".$q->yield_area." = </small>";
        //                $yield .= (floatval($q->yield_no_bags) * floatval($weight_ave)) / floatval($q->yield_area);
        //           }
        //      }
        //      return $yield;
        // })
            ->make(true);
    }
    public function load_verified_municipalities(Request $request)
    {
        $municode = $request->municode;
        return view('sed.include.verified_table', compact('municode'));
    }

    public function verified_first_form_modal(Request $request)
    {

        $season_year = \Config::get('constants.season_year');
        $season_code = \Config::get('constants.season_code');
        $ver_data = [];

        $farmer = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->where([
                'sed_id' => $request->farmerid,
            ])
            ->first();

        return view('sed.include.first_form', compact('farmer'));
    }

    public function push_verified_data(Request $request)
    {
        $farmers = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',extename,' ',lname) as fullname"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_provinces', 'lib_provinces.provCode', '=', "sed_verified.prv_code")
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.lib_municipalities', 'lib_municipalities.citymunCode', '=', "sed_verified.muni_code")
            ->where('muni_code', $request->municode)
            ->where(function ($q) {
                $q->where('created_by', '!=', null);
            })
        // ->where(function($q) {
        //      $q->where('status', "=", 1);
        // })
        // ->where('sowing_month', '!=', 0)
            ->where('enableEdit', "!=", 1)
            ->where('isPush', 0)
            ->get();
        // dd($farmers);
        foreach ($farmers as $f) {
            DB::beginTransaction();
            try {
                if ($f->status == 1 && $f->sowing_month != 0) {
                    $bags = round(intval($f->committed_area)) * 2;
                    if ($f->farm_area_ws2021 != 0) {
                        if ($f->committed_area <= $f->farm_area_ws2021) {
                            $area = $f->committed_area;
                        } else {
                            $area = $f->farm_area_ws2021;
                        }
                    } else if ($f->farm_area_ds2021 != 0) {
                        if ($f->committed_area <= $f->farm_area_ds2021) {
                            $area = $f->committed_area;
                        } else {
                            $area = $f->farm_area_ds2021;
                        }
                    } else {
                        $area = $f->committed_area;
                    }
                    $data = [
                        'sed_id_fk' => $f->sed_id,
                        'contact_no' => $f->contact_no,
                        'paymaya_code' => '',
                        'province' => $f->provDesc,
                        'municipality' => $f->citymunDesc,
                        'drop_off_point' => '',
                        'schedule_start' => '',
                        'schedule_end' => '',
                        'rsbsa_control_no' => '',
                        'firstname' => $f->fname,
                        'middname' => $f->midname,
                        'lastname' => $f->lname,
                        'extname' => $f->extename,
                        'area' => $area,
                        'bags' => $bags,
                        'region' => '',
                        'province2' => '',
                        'municipality2' => '',
                        'barangay' => '',
                        'is_active' => 1,
                        'sex' => $f->ver_sex,
                        'coop_accreditation' => $f->coop_accred,
                        'is_printed' => 0,
                    ];
                    $insert = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.tbl_beneficiaries')
                        ->insert($data);
                }

                $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                    ->where([
                        'sed_id' => $f->sed_id,
                    ])
                    ->update([
                        'isPush' => 1,
                        'pushBy' => Auth::user()->userId,
                    ]);
                DB::commit();
                $return = ["status" => 1, "message" => "Farmer data updated successfuly"];
            } catch (\Exception $e) {
                DB::rollback();
                $return = ["status" => 0, "message" => $e];
            }
        }

        return $return;
    }

    public function ebinhi_municipalities()
    {
        return view('sed.municipality');
    }

    public function municipality_datatable()
    {

        $query = DB::table($GLOBALS['season_prefix']."rcep_delivery_inspection.lib_prv")
            ->select('*')
            ->where('localLoad', 1)
        // ->whereExists(function ($query) {
        //      $query->select(DB::raw('muni_code'))
        //            ->from('rcep_paymaya.sed_verified')
        //            ->whereRaw('sed_verified.muni_code = lib_prv.prv');
        //  })
            ->orderBy('isEbinhi', 'desc');

        return Datatables::of($query)
            ->addColumn('isEbinhi', function ($query) {
                $isEbinhi = "";
                if ($query->isEbinhi == 1) {
                    $isEbinhi = '<button  class="btn btn-xs btn-danger editFarmer" data-id="' . $query->prv . '" data-val="0">NO</button>';
                } else if ($query->isEbinhi == 0) {
                    $isEbinhi = '<button  class="btn btn-xs btn-success editFarmer" data-id="' . $query->prv . '" data-val="1">YES</button>';
                }

                return $isEbinhi;
            })
            ->make(true);
    }

    public function municipality_edit(Request $request)
    {
        DB::beginTransaction();
        try {
            // update user details
            DB::table($GLOBALS['season_prefix'].'rcep_delivery_inspection.lib_prv')
                ->where('prv', $request->prv)
                ->update([
                    'isEbinhi' => $request->value,
                ]);

            DB::commit();
            return ['message' => 'Updated municipality successfully.'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['message' => 'Error updating municipality.'];
        }
    }

    public function enable_edit_view(Request $request)
    {
        $data['id'] = $request->id;
        $data['value'] = $request->value;
        return view('sed.include.enableEdit', compact('data'));
    }

    public function enable_edit(Request $request)
    {
        if ($request->value == 1) {
            $validator = Validator::make($request->all(), [
                'remarks' => 'required',
            ], []);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorArray = [];

                if ($errors->has('remarks')) {
                    $errorArray[] = ['key' => 'remarks', 'value' => $errors->first('remarks')];
                }

                return ['status' => 4, 'message' => $errorArray, 'errors' => $errors->all()];
            }
        }

        DB::beginTransaction();
        try {
            // update user details
            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_id', $request->id)
                ->update([
                    'enableEdit' => $request->value,
                    'edit_remarks' => $request->remarks,
                ]);

            DB::commit();
            return ['message' => 'Successfully updated'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['message' => 'Error updating farmer details.'];
        }
    }

    public function increment_farmerid()
    {
        $farmers = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->get();

        foreach ($farmers as $f) {
            $maxid = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('muni_code', $f->muni_code)
                ->max('farmer_id');

            $maxid++;

            DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
                ->where('sed_id', $f->sed_id)
                ->update([
                    'farmer_id' => $maxid,
                ]);
        }
    }

    public function excel_verified_farmers($prv)
    {
        $farmers = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            ->addSelect(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName) as createdBy"))
            ->addSelect(DB::raw("sed_verified.contact_no as farmers_mobile_no"))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.users', 'users.userId', '=', "sed_verified.created_by")
            ->where('muni_code', $prv)
            ->where(function ($q) {
                $q->where('created_by', '!=', null);
            });
        // ->where(function($q) {
        //      $q->where('status', "!=", 0)
        //      ->where('status', "!=", 4);
        // });

        // if($request->filter == 'yes'){
        $farmers = $farmers->where(function ($q) {
                $q->where('status', '1');
                $q->orWhere('status', '5');
            })
        // ->where('sowing_month', '!=', 0)
            // ->where('isPush', 0)
            ->get();
        // }

        // if($request->filter == 'no'){
        //      $query = $query->where('status', 2)
        //                ->where('isPush', 0);
        // }

        // if($request->filter == 'failed'){
        //      $query = $query->where('status', 3)
        //                ->where('isPush', 0);
        // }

        // if($request->filter == 'next'){
        //      $query = $query->where('status', 1)
        //                ->where('sowing_month', 0)
        //                ->where('isPush', 0);
        // }
        $location = $farmers[0]->province_name . " " . $farmers[0]->municipality_name;
        // $farmers = json_decode(json_encode($farmers), true); //convert collection to associative array to be converted to excel
        $data = [];

        foreach ($farmers as $d) {
            if ($d->farm_area_ws2021 == 0 || $d->farm_area_ws2021 == "") {
                $d->farm_area_ws2021 = "N/A";
            }
            if ($d->farm_area_ds2021 == 0 || $d->farm_area_ds2021 == "") {
                $d->farm_area_ds2021 = "N/A";
            }

            if ($d->isParticipatingAfter == 1) {
                $d->isParticipatingAfter = "YES";
            } else {
                $d->isParticipatingAfter = "NO";
            }

            if ($d->hasDecreasedYield == 1) {
                $d->hasDecreasedYield = "YES";
            } else {
                $d->hasDecreasedYield = "NO";
            }

            if ($d->hasDecreasedFarmArea == 1) {
                $d->hasDecreasedFarmArea = "YES";
            } else {
                $d->hasDecreasedFarmArea = "NO";
            }

            $data[] = [
                (strpos($d->created_by, 'sms')) ? "SMS Respondent" : $d->firstName . " " . $d->lastName, $d->rsbsa_control_number, $d->fname, $d->midname, $d->lname, $d->extename, $d->ver_sex,
                $d->farmers_mobile_no, $d->secondary_contact_no, $d->farm_area_ws2021, $d->farm_area_ds2021,
                $d->committed_area, $d->sowing_year, $d->sowing_month, $d->sowing_week, $d->preffered_variety1, $d->preffered_variety2,
                $d->isParticipatingAfter, $d->hasDecreasedYield, $d->hasDecreasedFarmArea,
            ];
        }

        return Excel::create($location . " VERIFIED_FARMERS" . "_" . date("Y-m-d g:i A"), function ($excel) use ($data) {
            $excel->sheet("FARMERS", function ($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->prependRow(1, array(
                    'Enumerator', 'RSBSA #', "Farmer's First Name", "Farmer's Middle Name",
                    "Farmer's Last Name", "Farmer's Extension Name", 'Sex',
                    'Contact no', 'Verified Contact Number', 'WS 2021 Area (ha)', 'DS 2022 Area (ha)',
                    'Verified Area (ha)', 'Sowing Year', 'Sowing Month', 'Sowing Week', 'Preferred Variety', 'Second Preferred Variety',
                    'Participating next season?', 'Yield decreased', 'Farm area decrease',
                ));
                $sheet->freezeFirstRow();
            });
        })->download('xlsx');
    }

    public function excel_verified_farmers_sra($prv)
    {
        $farmers = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->addSelect(DB::raw("CONCAT(fname,' ',midname,' ',lname) as fullname"))
            // ->addSelect(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName) as createdBy"))
            ->addSelect(DB::raw("sed_verified.contact_no as farmers_mobile_no"))
            // ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.users', 'users.userId', '=', "sed_verified.created_by")
            ->where(function ($q) {
                $q->where('status', '1')
                ->orWhere('status', '5');
            })
            ->where('muni_code', $prv)
            ->where(function ($q) {
                $q->where('created_by', '!=', null);
            })
            ->where('isPush', 1)
            ->get();

        if (count($farmers) == 0) {
            dd("No data found");
        }

        $location = $farmers[0]->province_name . " " . $farmers[0]->municipality_name;

        $data = [];

        foreach ($farmers as $d) {
            if ($d->farm_area_ws2021 == 0 || $d->farm_area_ws2021 == "") {
                $d->farm_area_ws2021 = "N/A";
            }
            if ($d->farm_area_ds2021 == 0 || $d->farm_area_ds2021 == "") {
                $d->farm_area_ds2021 = "N/A";
            }

            if ($d->isParticipatingAfter == 1) {
                $d->isParticipatingAfter = "YES";
            } else {
                $d->isParticipatingAfter = "NO";
            }

            if ($d->hasDecreasedYield == 1) {
                $d->hasDecreasedYield = "YES";
            } else {
                $d->hasDecreasedYield = "NO";
            }

            if ($d->hasDecreasedFarmArea == 1) {
                $d->hasDecreasedFarmArea = "YES";
            } else {
                $d->hasDecreasedFarmArea = "NO";
            }

            $data[] = [
                $d->rsbsa_control_number, $d->fname, $d->midname, $d->lname, $d->extename, $d->ver_sex,
                $d->farmers_mobile_no, $d->secondary_contact_no, $d->farm_area_ws2021, $d->farm_area_ds2021,
                $d->committed_area, $d->sowing_year, $d->sowing_month, $d->sowing_week, $d->preffered_variety1, $d->preffered_variety2,
                $d->isParticipatingAfter, $d->hasDecreasedYield, $d->hasDecreasedFarmArea,
            ];

        }

        return Excel::create($location . " VERIFIED_FARMERS" . "_" . date("Y-m-d g:i A"), function ($excel) use ($data) {
            $excel->sheet("FARMERS", function ($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->prependRow(1, array(
                    'RSBSA #', "Farmer's First Name", "Farmer's Middle Name",
                    "Farmer's Last Name", "Farmer's Extension Name", 'Sex',
                    'Contact Number', 'Secondary Number', 'Prev Season Area (ha)', rtrim($GLOBALS['season_prefix'], '_').' Area (ha)',
                    'Verified Area (ha)', 'Sowing Year', 'Sowing Month', 'Sowing Week', 'Preferred Variety', 'Second Preferred Variety',
                    'Participating next season?', 'Yield decreased', 'Farm area decrease',
                ));
                $sheet->freezeFirstRow();
            });
        })->download('xlsx');
    }

    public function excel_detaailed_summary($prv, $muni, $status, $user, $datefrom, $dateto)
    {

        $date[] = date("Y-m-d", strtotime(str_replace('-', '/', $datefrom)));
        $date[] = date("Y-m-d", strtotime(str_replace('-', '/', $dateto)));

        $query = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            ->select('province_name', 'municipality_name')
            ->addSelect(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName) as fullname"))
            ->addSelect(DB::raw('COUNT(IF(status = 1 OR status = 5,1,NULL)) as yes_count'))
            ->addSelect(DB::raw('COUNT(IF(status = 2,1,NULL)) as no_count'))
            ->addSelect(DB::raw('COUNT(IF(status = 3,1,NULL)) as failed_count'))
            ->leftjoin($GLOBALS['season_prefix'].'sdms_db_dev.users', function ($join) {
                $join->on('users.userId', '=', "sed_verified.created_by");
            });
        // ->where('lib_prv.isEbinhi', 1);
        $query = $query->whereBetween('date_sync', $date);

        if ($prv != "null") {
            $query = $query->where("sed_verified.prv_code", $prv);
        }

        if ($muni != "null") {
            $query = $query->where("muni_code", $muni);
        }

        if ($status != "null") {
            if ($status == 0) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 0)
                        ->orWhere('status', "=", 4);
                });
            } else if ($status == 1) {
                $query = $query->where(function ($q) {
                    $q->where('status', '1')
                    ->orWhere('status', '5');
                })
                    ->where('sowing_month', "!=", '0');
            } else if ($status == 2) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 2);
                });

            } else if ($status == 3) {
                $query = $query->where(function ($q) {
                    $q->where('status', "=", 3);
                });

            } else if ($status == 1) {
                $query = $query->where(function ($q) {
                    $q->where('status', '1')
                    ->orWhere('status', '5');
                })
                    ->where('sowing_month', "=", '0');
            }

        }

        if ($user != "null") {
            $query = $query->where("created_by", $user);
        }

        $query = $query->groupBy('created_by', 'muni_code')
            ->orderBy('created_by', 'province_name')
            ->get();

        // $collection = collect($query);
        // $yes = $collection->where('status', 1)->count();
        // $yes_calls = $yes;
        // $no = $collection->where('status', 2)->count();
        // $failed = $collection->where('status', 3)->count();
        // $pending  = $collection->where('status', 0)->count();

        $data = [];

        foreach ($query as $d) {

            $data[] = [
                $d->province_name,
                $d->municipality_name,
                $d->fullname,
                $d->yes_count,
                $d->no_count,
                $d->failed_count,
            ];
        }

        return Excel::create("Detailed Summary" . "_" . date("Y-m-d g:i A"), function ($excel) use ($data) {
            $excel->sheet("FARMERS", function ($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                $sheet->prependRow(1, array(
                    'Province Name', 'Municipality Name', 'Enumerators', 'Answered Yes', 'Answered No', 'Failed Calls',
                ));
                $sheet->freezeFirstRow();
            });
        })->download('xlsx');
    }

    public function fix_farmer_id()
    {
        $farmers = DB::table($GLOBALS['season_prefix']."rcep_paymaya.sed_verified")
            ->select('*')
            ->where('muni_code', '025701')
            ->orderBy("sed_id")
            ->get();

        $id = 1;
        $res = [];
        foreach ($farmers as $f) {
            // $update = DB::table($GLOBALS['season_prefix'].'rcep_paymaya.sed_verified')
            //      ->where('sed_id', $f->sed_id)
            //      ->update([
            //           'farmer_id' => $id,
            //      ]);
            $update = "Update ".$GLOBALS['season_prefix']."rcep_paymaya.sed_verified SET farmer_id = " . $id . " WHERE sed_id = " . $f->sed_id;
            $res[] = $update;
            $id++;
        }

        return $res;
    }

}

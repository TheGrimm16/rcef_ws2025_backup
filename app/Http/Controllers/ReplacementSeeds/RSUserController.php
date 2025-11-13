<?php

namespace App\Http\Controllers\ReplacementSeeds;

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

class RSUserController extends Controller
{

    public function index() {
     
        $data['api_token'] = Auth::guard('replacement_seeds')->user()->api_token;

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
                return view('replacement_seeds.users.index', compact('data', 'roles'));  
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

}
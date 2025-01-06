<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
// use App\Role;
// use App\Permission;
use DB;
use Auth;
use Yajra\Datatables\Datatables;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['api_token'] = Auth::user()->api_token;
        return view('roles.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = DB::table('permissions')
        ->select('*')
        ->get();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'display_name' => 'required',
            'description' => 'required',
            'permission' => 'required',
        ]);

        $input = $request->all();

        DB::beginTransaction();
        try {
            // insert role
            $roleId = DB::table('roles')
            ->insertGetId([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
                'description' => $input['description']
            ]);

            // add role permissions
            foreach ($input['permission'] as $key => $value) {
                DB::table('permission_role')
                ->insert([
                    'permissionId' => $value,
                    'roleId' => $roleId
                ]);
            }

            DB::commit();
            $request->session()->flash('success', 'Created role successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Error creating role.');
        }

        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $roleId = $id;

        $role = DB::table('roles')
        ->select('*')
        ->where('roles.roleId', $roleId)
        ->first();

        $rolePermissions = DB::table('permissions')
        ->leftJoin('permission_role', 'permission_role.permissionId', '=', 'permissions.permissionId')
        ->select('*')
        ->where('permission_role.roleId', $roleId)
        ->get();

        return view('roles.show', compact('role', 'rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $roleId = $id;

        $role = DB::table('roles')
        ->select('*')
        ->where('roles.roleId', $roleId)
        ->first();

        $permissions = DB::table('permissions')
        ->select('*')
        ->get();

        $rolePermissions = DB::table('permission_role')
        ->where('roleId', $roleId)
        ->lists('permissionId');

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
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
        $roleId = $id;

        $this->validate($request, [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required',
            'permission' => 'required',
        ]);

        $input = $request->all();

        DB::beginTransaction();
        try {
            // update role
            DB::table('roles')
            ->where('roleId', $roleId)
            ->update([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
                'description' => $input['description'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // delete role permissions
            DB::table('permission_role')
            ->where('roleId', $roleId)
            ->delete();

            // add role permissions
            foreach ($input['permission'] as $key => $value) {
                DB::table('permission_role')
                ->insert([
                    'permissionId' => $value,
                    'roleId' => $roleId
                ]);
            }

            DB::commit();
            $request->session()->flash('success', 'Updated role successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Error updating role.');
        }

        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("roles")->where('roleId',$id)->delete();
        return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
    }

    public function datatable()
    {
        $roles = DB::table('roles')
        ->select('roleId', 'display_name', 'description', 'isDeleted')
        ->get();

        $data = array();

        foreach ($roles as $item) {
            $data[] = array(
                'roleId' => $item->roleId,
                'role' => $item->display_name,
                'description' => $item->description,
                'isDeleted' => $item->isDeleted
            );
        }

        $data = collect($data);

        return Datatables::of($data)
        ->addColumn('status', function($data) {
            if ($data['isDeleted'] == 0) {
                return '<span class="badge badge-success" style="font-family: Noto; font-size: .7vw;">Active</span>';
            } else {
                return '<span class="badge badge-danger" style="font-family: Noto; font-size: .7vw;">Inactive</span>';
            }
        })
        ->addColumn('actions', function($data) {
            $button = '<a href="'.route('roles.show', $data['roleId']).'" class="btn btn-pill btn-info actionBtn" title="View">View</a>&nbsp;';

            if (Auth::user()->can('role-edit')) {
                $button .= '<a href="'.route('roles.edit', $data['roleId']).'" class="btn btn-pill btn-warning actionBtn" title="Edit">Edit</a>&nbsp;';
            }

            if (Auth::user()->can('role-delete')) {
                if ($data['isDeleted'] == 0) {
                    $button .= '<a href="#" class="btn btn-pill btn-danger actionBtn" title="Deactivate">Deactivate</a>';
                } else {
                    $button .= '<a href="#" class="btn btn-pill btn-success actionBtn" title="Activate">Activate</a>';
                }
            }

            return $button;
        })
        ->make(true);
    }
}

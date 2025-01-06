<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
// use App\Permission;
use DB;
use Auth;
use Yajra\Datatables\Datatables;

class PermissionController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $data['api_token'] = Auth::user()->api_token;
        return view('permissions.index', compact('data'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        return view('permissions.create');
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
            'name' => 'required|unique:permissions,name',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        $input = $request->all();

        DB::beginTransaction();
        try {
            // insert permission
            DB::table('permissions')
            ->insert([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
                'description' => $input['description']
            ]);

            DB::commit();
            $request->session()->flash('success', 'Created permission successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Error creating permission.');
        }

        return redirect()->route('permissions.index');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $permissionId = $id;

        $permission = DB::table('permissions')
        ->select('*')
        ->where('permissionId', $permissionId)
        ->first();

        return view('permissions.show', compact('permission'));
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $permissionId = $id;

        $permission = DB::table('permissions')
        ->select('*')
        ->where('permissionId', $permissionId)
        ->first();

        return view('permissions.edit', compact('permission'));
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
        $permissionId = $id;

        $this->validate($request, [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        $input = $request->all();

        DB::beginTransaction();
        try {
            // update permission
            DB::table('permissions')
            ->where('permissionId', $permissionId)
            ->update([
                'name' => $input['name'],
                'display_name' => $input['display_name'],
                'description' => $input['description'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            DB::commit();
            $request->session()->flash('success', 'Updated permission successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Error updating permission.');
        }

        return redirect()->route('permissions.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        //
    }

    public function datatable()
    {
        $permissions = DB::table('permissions')
        ->select('*')
        ->get();

        $data = array();

        foreach ($permissions as $item) {
            $data[] = array(
                'permissionId' => $item->permissionId,
                'permission' => $item->display_name,
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
            $button = '<a href="'.route('permissions.show', $data['permissionId']).'" class="btn btn-pill btn-info actionBtn" title="View">View</a>&nbsp;';

            if (Auth::user()->can('permission-edit')) {
                $button .= '<a href="'.route('permissions.edit', $data['permissionId']).'" class="btn btn-pill btn-warning actionBtn" title="Edit">Edit</a>&nbsp;';
            }

            if (Auth::user()->can('permission-delete')) {
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

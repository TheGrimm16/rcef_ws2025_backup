<?php

namespace App\Http\Controllers\SeedReplacement;

use Illuminate\Http\Request;
use DB;
use Yajra\Datatables\Datatables;
use App\Models\SeedReplacementRoles;
use App\Models\SeedReplacementUser;

class SRUserController extends SRBaseController
{
    /**
     * Return the actual user model
     */
    protected function getCurrentUser()
    {
        return $this->userObject(); // returns Eloquent model
    }

    /**
     * Check if user has at least one role from the array
     */
    protected function userHasAnyRole($user, $roles)
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function index()
    {
        $user = $this->getCurrentUser();

        $roles = SeedReplacementRoles::getAllRoles(); // key => value collection

        $roles_filtered = [
            "branch-it", "buffer-inspector", "dro", "delivery-manager",
            "ebinhi-implementor", "rcef-pmo", "system-encoder",
            "techno_demo_officer", "seed-grower", "administrator",
            "rcef-programmer"
        ];

        if (!$this->userHasAnyRole($user, $roles_filtered)) {
            $mss = "No Access Privilege";
            return view('utility.pageClosed', compact('mss'));
        }

        return view('seed_replacement.users.index', [
            'currentUser' => $user,
            'currentUserRoles' => $user->roles->pluck('name')->toArray(), // convert Collection to array
            'roles' => $roles,
            'apiToken' => $user->api_token,
        ]);
    }

    public function datatable()
    {
        $usersQuery = SeedReplacementUser::select(
                'users.userId',
                \DB::raw("CONCAT_WS(' ', firstName, middleName, lastName, extName) AS name"),
                'users.firstName',
                'users.middleName',
                'users.lastName',
                'users.extName',
                'users.username',
                'users.email',
                'users.province',
                'users.municipality',
                'users.isDeleted',
                \DB::raw("GROUP_CONCAT(roles.display_name SEPARATOR '|') AS role_list")
            )
            ->leftJoin('role_user', 'users.userId', '=', 'role_user.userId')
            ->leftJoin('roles', function ($join) {
                $join->on('role_user.roleId', '=', 'roles.roleId')
                     ->where('roles.isDeleted', '=', 0);
            })
            ->where('users.isDeleted', 0)
            ->groupBy('users.userId');

        return Datatables::of($usersQuery)
            ->filter(function($query) {
                if (request()->has('search') && $search = request('search')['value']) {
                    $search = strtolower($search);
                    $query->where(function($q) use ($search) {

                        // Name search: multiple combinations
                        $q->where(function ($x) use ($search) {
                            $x->whereRaw("LOWER(CONCAT_WS(' ', firstName, middleName, lastName, extName)) LIKE ?", ["%{$search}%"])
                              ->orWhereRaw("LOWER(CONCAT_WS(' ', firstName, lastName)) LIKE ?", ["%{$search}%"])
                              ->orWhereRaw("LOWER(CONCAT_WS(' ', lastName, firstName)) LIKE ?", ["%{$search}%"])
                              ->orWhereRaw("LOWER(firstName) LIKE ?", ["%{$search}%"])
                              ->orWhereRaw("LOWER(lastName) LIKE ?", ["%{$search}%"]);
                        });

                        // Other fields
                        $q->orWhereRaw("LOWER(username) LIKE ?", ["%{$search}%"])
                          ->orWhereRaw("LOWER(email) LIKE ?", ["%{$search}%"])
                          ->orWhereRaw("LOWER(province) LIKE ?", ["%{$search}%"])
                          ->orWhereRaw("LOWER(municipality) LIKE ?", ["%{$search}%"]);
                    });
                }
            })
            ->addColumn('name', function($user) {
                $fullName = $user->firstName;
                if (!empty($user->middleName)) $fullName .= ' ' . $user->middleName;
                if (!empty($user->lastName))   $fullName .= ' ' . $user->lastName;
                if (!empty($user->extName))    $fullName .= ' ' . $user->extName;
                return $fullName;
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
                return $user->isDeleted ? 'Inactive' : 'Active';
            })
            ->addColumn('actions', function($user) {
                return view('seed_replacement.users.partials.actions', compact('user'))->render();
            })
            ->escapeColumns([])
            ->make(true);
    }
}

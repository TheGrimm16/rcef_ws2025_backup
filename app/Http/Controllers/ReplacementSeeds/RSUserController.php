<?php

namespace App\Http\Controllers\ReplacementSeeds;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Yajra\Datatables\Datatables;
use App\Models\ReplacementSeedsRoles;
use App\Models\ReplacementSeedsUser;

class RSUserController extends Controller
{
    protected function getCurrentUser()
    {
        $user = Auth::guard('replacement_seeds')->user();

        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        return [
            'userId' => $user->userId,
            'firstName' => $user->firstName,
            'middleName' => $user->middleName,
            'lastName' => $user->lastName,
            'extName' => $user->extName,
            'email' => $user->email,
            'username' => $user->username,
            'stationId' => $user->stationId,
            'api_token' => $user->api_token,
            'roles' => $user->roles->pluck('name')->toArray(),
            'can' => function($permission) use ($user) {
                return $user->can($permission);
            },
            'hasRole' => function($roles) use ($user) {
                $roles = is_array($roles) ? $roles : [$roles];
                foreach ($roles as $r) {
                    if ($user->hasRole($r)) return true;
                }
                return false;
            }
        ];
    }

    public function getFullNameAttribute()
    {
        $parts = [$this->firstName];
        if ($this->middleName) $parts[] = $this->middleName;
        if ($this->lastName) $parts[] = $this->lastName;
        if ($this->extName) $parts[] = $this->extName;
        return implode(' ', $parts);
    }

    public function index()
    {
        $user = $this->getCurrentUser();

        // Use the new model helper to get all active roles
        $roles = ReplacementSeedsRoles::getAllRoles(); // key => value collection

        $roles_filtered = [
            "branch-it", "buffer-inspector", "dro", "delivery-manager",
            "ebinhi-implementor", "rcef-pmo", "system-encoder",
            "techno_demo_officer", "seed-grower", "administrator",
            "rcef-programmer"
        ];

        if (!$user['hasRole']($roles_filtered)) {
            $mss = "No Access Privilege";
            return view('utility.pageClosed', compact('mss'));
        }

        return view('replacement_seeds.users.index', [
            'currentUser' => $user,
            'currentUserRoles' => $user['roles'],
            'roles' => $roles,
            'apiToken' => $user['api_token'],
        ]);
    }

public function datatable()
{
    $usersQuery = ReplacementSeedsUser::select(
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
                ->where('roles.isDeleted', '=', 0);   // FIXED
        })
        ->where('users.isDeleted', 0)
        ->groupBy('users.userId');

    return \Datatables::of($usersQuery)
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
            // concatenate name for the datatable column
            $fullName = $user->firstName;
            if (!empty($user->name)) $fullName .= ' ' . $user->middleName;
            if (!empty($user->lastName)) $fullName .= ' ' . $user->lastName;
            if (!empty($user->extName)) $fullName .= ' ' . $user->extName;
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
            // put your buttons HTML here
            return view('replacement_seeds.users.partials.actions', compact('user'))->render();
        })
        ->escapeColumns([])
        ->make(true);
}





}

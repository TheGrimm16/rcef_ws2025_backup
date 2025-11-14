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
    $usersQuery = \DB::table('users')
        ->select(
            'userId',
            \DB::raw("CONCAT_WS(' ', firstName, middleName, lastName, extName) AS name"),
            'firstName',
            'middleName',
            'lastName',
            'extName',
            'username',
            'email',
            'province',
            'municipality',
            'isDeleted'
        )
        ->where('isDeleted', 0);

    return \Datatables::of($usersQuery)
        ->filter(function($query) {
            if (request()->has('search') && $search = request('search')['value']) {
                $search = strtolower($search);

                $query->where(function($q) use ($search) {
                    $q->whereRaw("LOWER(CONCAT(firstName, ' ', middleName, ' ', lastName, ' ', extName)) LIKE ?", ["%{$search}%"])
                      ->orWhereRaw("LOWER(username) LIKE ?", ["%{$search}%"])
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
            // return roles HTML if needed
            return ''; 
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

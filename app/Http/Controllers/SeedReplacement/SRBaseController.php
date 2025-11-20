<?php

namespace App\Http\Controllers\SeedReplacement;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SRBaseController extends Controller
{
    /**
     * Return the actual logged-in user model, preferring seed_replacement guard
     */
    protected function currentUserModel()
    {
        if (Auth::guard('seed_replacement')->check()) {
            return Auth::guard('seed_replacement')->user()->load('roles');
        }

        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user()->load('roles');
        }

        abort(403, 'Unauthorized access');
    }

    /**
     * Return standardized array for API / Blade purposes
     */
    protected function userArray($user = null)
    {
        if (!isset($user)) {
            $user = $this->currentUserModel();
        }

        $stationId = isset($user->stationId) ? $user->stationId : null;
        $apiToken  = isset($user->api_token) ? $user->api_token : null;

        $roles = array();
        foreach ($user->roles as $role) {
            $roles[] = (object) array(
                'name' => $role->name,
                'display_name' => isset($role->display_name) ? $role->display_name : $role->name,
            );
        }

        return array(
            'userId' => $user->userId,
            'firstName' => $user->firstName,
            'middleName' => $user->middleName,
            'lastName' => $user->lastName,
            'extName' => $user->extName,
            'email' => $user->email,
            'username' => $user->username,
            'stationId' => $stationId,
            'api_token' => $apiToken,
            'roles' => $roles,
        );
    }

    /**
     * Return the actual user model (no array conversion)
     */
    protected function userObject($user = null)
    {
        if (isset($user)) {
            return $user;
        }

        return $this->currentUserModel();
    }
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ReplacementSeedsUser extends Authenticatable
{
    /**
     * Use the local database connection instead of default.
     *
     * @var string
     */
    protected $connection = 'local';

    /**
     * Table name in the local database.
     *
     * @var string
     */
    protected $table = 'users';

    protected $primaryKey = 'userId';

    /**
     * Fillable fields (adjust as needed).
     *
     * @var array
     */
    protected $fillable = [
        'firstName', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * A user may have many roles.
     */
    public function roles()
    {
        return $this->belongsToMany(
            'App\Models\ReplacementSeedsRoles',
            'role_user',
            'userId',
            'roleId'
        )->where('roles.isDeleted', 0); // only active roles
    }

    /**
     * Quick role checker (by internal name column).
     */
    public function hasRole($roleName)
    {
        return $this->roles()->where('roles.name', $roleName)->exists();
    }

}

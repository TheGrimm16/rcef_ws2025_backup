<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ReplacementSeedsUser extends Authenticatable
{
    protected $connection = 'local';
    protected $table = 'users';
    protected $primaryKey = 'userId';

    protected $fillable = [
        'firstName', 'middleName', 'lastName', 'extName', 'username',
        'email', 'province', 'municipality', 'stationId'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Roles relationship
    public function roles()
    {
        return $this->belongsToMany(
            'App\Models\ReplacementSeedsRoles',
            'role_user',
            'userId',
            'roleId'
        )->where('roles.isDeleted', 0);
    }

    // Quick role checker
    public function hasRole($roleNames)
    {
        $query = $this->roles();

        if (is_array($roleNames)) {
            return $query->whereIn('roles.name', $roleNames)->exists();
        }

        return $query->where('roles.name', $roleNames)->exists();
    }

    // Full name accessor
    public function getFullNameAttribute()
    {
        return trim("{$this->firstName} {$this->middleName} {$this->lastName} {$this->extName}");
    }

    // Search scope (optimized for indexed columns)
    public function scopeSearch($query, $term)
    {
        $term = "%{$term}%";

        return $query->where(function ($q) use ($term) {
            $q->whereRaw("CONCAT_WS(' ', firstName, middleName, lastName, extName) LIKE ?", [$term])
              ->orWhere('username', 'LIKE', $term)
              ->orWhere('email', 'LIKE', $term)
              ->orWhere('province', 'LIKE', $term)
              ->orWhere('municipality', 'LIKE', $term);
        });
    }
}

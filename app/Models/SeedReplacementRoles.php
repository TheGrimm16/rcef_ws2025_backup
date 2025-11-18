<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedReplacementRoles extends Model
{
    protected $connection = 'local';
    protected $table = 'roles';
    protected $primaryKey = 'roleId';
    public $timestamps = true;

    protected $fillable = [
        'name', 'display_name', 'description', 'isDeleted'
    ];

    /**
     * A role belongs to many users.
     */
    public function users()
    {
        return $this->belongsToMany(
            'App\Models\SeedReplacementUser',
            'role_user',
            'roleId',
            'userId'
        );
    }

    /**
     * Get all active roles as key-value (name => display_name)
     */
    public static function getAllRoles()
    {
        return self::where('isDeleted', 0)
                   ->pluck('display_name', 'name');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacementSeedsRoles extends Model
{
    protected $connection = 'local';
    protected $table = 'roles';
    protected $primaryKey = 'roleId';
    public $timestamps = true; // because created_at/updated_at exist

    protected $fillable = [
        'name', 'display_name', 'description', 'isDeleted'
    ];

    /**
     * A role belongs to many users.
     */
    public function users()
    {
        return $this->belongsToMany(
            'App\Models\ReplacementSeedsUser', // related model
            'role_user',                       // pivot table
            'roleId',                          // pivot column for this model
            'userId'                           // pivot column for related model
        );
    }
}

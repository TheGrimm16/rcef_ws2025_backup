<?php

// namespace App;

// use Illuminate\Foundation\Auth\User as Authenticatable;

// class User extends Authenticatable
// {
//     /**
//      * The attributes that are mass assignable.
//      *
//      * @var array
//      */
//     protected $fillable = [
//         'name', 'email', 'password',
//     ];

//     /**
//      * The attributes that should be hidden for arrays.
//      *
//      * @var array
//      */
//     protected $hidden = [
//         'password', 'remember_token',
//     ];
// }

namespace App;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
// use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    // use EntrustUserTrait {
    //     EntrustUserTrait::restore insteadof SoftDeletes;
    // }
        
    // use SoftDeletes;

    use EntrustUserTrait;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $primaryKey = 'userId';

    protected $fillable = [
        'firstName', 'middleName', 'lastName', 'extName', 'username', 'email', 'secondaryEmail', 'password', 'sex', 'agencyId', 'stationId', 'position', 'designation', 'api_token', 'region', 'province', 'municipality'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // protected $dates = ['deleted_at'];

    public function isAdmin()
    {
        return $this->admin ? true : false;
    }
}
<?php

namespace App;

use Zizaco\Entrust\EntrustRole;


class Role extends EntrustRole
{
  protected $primaryKey = 'roleId';

  protected $fillable = [
    'name', 'display_name', 'description'
  ];
}
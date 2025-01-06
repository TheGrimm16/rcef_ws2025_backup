<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistryFarmerRole extends Model
{
    protected $connection = 'registry_db';
    protected $table = 'farmer_roles';

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistryFarmerProfile extends Model
{
    protected $connection = 'registry_db';
    protected $table = 'farmer_profiles';

}

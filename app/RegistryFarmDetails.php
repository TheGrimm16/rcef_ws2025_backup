<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistryFarmDetails extends Model
{
    protected $connection = 'registry_db';
    protected $table = 'farm_details';
}

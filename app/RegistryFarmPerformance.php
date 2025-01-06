<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistryFarmPerformance extends Model
{
    protected $connection = 'registry_db';
    protected $table = 'farm_performances';
}

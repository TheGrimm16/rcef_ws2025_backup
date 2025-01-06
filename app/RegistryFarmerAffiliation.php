<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistryFarmerAffiliation extends Model
{
    protected $connection = 'registry_db';
    protected $table = 'farmer_affiliations';
}

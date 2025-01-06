<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeedInspector extends Model
{
    protected $connection = 'inspector_db';
    protected $table = 'inspector_schedule';
}

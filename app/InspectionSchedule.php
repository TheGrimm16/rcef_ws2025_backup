<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InspectionSchedule extends Model
{
    protected $connection = 'delivery_inspection_db';
    protected $table = 'tbl_schedule';
}

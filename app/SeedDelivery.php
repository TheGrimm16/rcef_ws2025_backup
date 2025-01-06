<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeedDelivery extends Model
{
    protected $connection = 'delivery_inspection_db';
    protected $table = 'tbl_delivery';
    protected  $primaryKey = 'deliveryId';
}

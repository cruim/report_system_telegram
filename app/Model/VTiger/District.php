<?php

namespace App\Model\VTiger;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'moscow_district';
    protected $fillable = ['id', 'name', 'mkad'];
}

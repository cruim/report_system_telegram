<?php

namespace App\Model\VtigerGo;

use Illuminate\Database\Eloquent\Model;

class VtigerBalance extends Model
{
    protected $connection = 'vtiger_go2';
    protected $table = 'vtiger_balance';
    protected $fillable = ['id',
        'city_id',
        'price',
        'minbalance',
        'limit_city'];

}

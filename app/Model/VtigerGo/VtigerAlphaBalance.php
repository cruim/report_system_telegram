<?php

namespace App\Model\VtigerGo;

use Illuminate\Database\Eloquent\Model;

class VtigerAlphaBalance extends Model
{
    protected $connection = 'vtiger_go';
    protected $table = 'balances';
    protected $fillable = ['subject',
        'balance',
        'price',
        'lid_sum',
        'limit_city'];

}

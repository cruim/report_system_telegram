<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
   protected $table = 'balance.balance';
   protected $fillable = ['ID', 'url', 'balance', 'Date', 'balance_prew', 'manual'];
}

<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class TverlangSalesOrder extends Model
{
    protected $connection = 'tver_lang';
    protected $table = 'crm-tverlang.vtiger_salesorder';
    protected $fillable =
        [
            'salesorderid',
            'sostatus'
        ];

}

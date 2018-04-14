<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class CallCenterCurrentMonthData extends Model
{
    protected $table = 'telegram.call_center_current_month_head_data';
    protected $fillable =
        [
            'id',
            'geo',
            'leads',
            'apruv_persent',
            'conversion',
            'delivery',
            'avg_check',
            'avg_jars'
        ];
}
<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'telegram.reports';
    protected $fillable =
        [
            'id',
            'telegram_name',
            'report_active',
            'sms_name',
            'controller',
            'parameters'
        ];
}
<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReportParameters extends Model
{
    protected $table = 'telegram.report_parameters';
    protected $fillable =
        [
            'id',
            'report_id',
            'parameters',
            'controller',
            'method'
        ];
}
<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReportToAbonent extends Model
{
    protected $table = 'telegram.report_to_abonent';
    protected $fillable =
        [
            'id',
            'report_id',
            'abonent_id',
            'active'
        ];
}
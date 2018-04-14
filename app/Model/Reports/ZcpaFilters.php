<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class ZcpaFilters extends Model
{
    protected $table = 'telegram.zcpa_filters';
    protected $fillable =
        [
            'id',
            'abonent_id',
            'filter_name',
            'filter_val'
        ];
}
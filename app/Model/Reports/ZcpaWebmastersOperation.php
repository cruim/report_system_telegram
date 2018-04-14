<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class ZcpaWebmastersOperation extends Model
{
    protected $table = 'webmasters.operation';
    protected $fillable =
        [
            'id',
            'operation',
            'agent',
            'contragent',
            'transaction',
            'created_at',
            'updated_at',
            'author',
            'operation_date',
            'geo',
            'offer',
            'deleted'
        ];
}
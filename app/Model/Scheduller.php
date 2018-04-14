<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Scheduller extends Model
{
    protected $table = 'telegram.scheduller';
    protected $fillable =
        [
            'id',
            'report_id',
            'abonent_id',
            'sending_time',
            'active'
        ];
}
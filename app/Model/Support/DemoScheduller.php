<?php

namespace App\Model\Support;

use Illuminate\Database\Eloquent\Model;

class DemoScheduller extends Model
{
    protected $table = 'telegram.demo_test_scheduller';
    protected $fillable =
        [
            'id',
            'telegram_id',
            'sending_time'
        ];
}
<?php

namespace App\Model\Support;

use Illuminate\Database\Eloquent\Model;

class DemoAbonents extends Model
{
    protected $table = 'telegram.demo_test_abonents';
    protected $fillable =
        [
            'id',
            'telegram_id',
            'label'
        ];
}
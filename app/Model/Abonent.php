<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Abonent extends Model
{
    protected $table = 'telegram.abonents';
    protected $fillable =
        [
            'id',
            'telegram_id',
            'abonent',
            'group_id',
            'active',
            'tiger_2_id',
            'first_name'
        ];
}
<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $table = 'telegram.bots';
    protected $fillable =
        [
            'id',
            'name',
            'active',
            'telegram_token'
        ];
}
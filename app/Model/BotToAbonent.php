<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BotToAbonent extends Model
{
    protected $table = 'telegram.bot_to_abonent';
    protected $fillable =
        [
            'id',
            'abonent_id',
            'bot_id',
            'active'
        ];
}
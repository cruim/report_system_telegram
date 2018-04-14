<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BotToReport extends Model
{
    protected $table = 'telegram.bot_to_report';
    protected $fillable =
        [
            'id',
            'report_id',
            'bot_id',
            'active'
        ];
}
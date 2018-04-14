<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class ZcpaInnerWebmasters extends Model
{
    protected $table = 'webmasters.webmasters';
    protected $fillable =
        [
            'id',
            'webmaster',
            'user_id',
            'telegram_id',
            'bot_notification'
        ];
}
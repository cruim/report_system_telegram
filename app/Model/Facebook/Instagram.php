<?php

namespace App\Model\Facebook;

use Illuminate\Database\Eloquent\Model;

class Instagram extends Model
{
    protected $table = 'telegram.instagram_check_data';
    protected $fillable = [
        'id',
        'comment_count',
        'post_label',
        'url',
        'telegram_id',
        'is_active'
    ];
}

<?php

namespace App\Model\Facebook;

use Illuminate\Database\Eloquent\Model;

class Facebook extends Model
{
    protected $table = 'telegram.fb_check_data';
    protected $fillable = [
        'id',
        'post_id',
        'comment_count',
        'is_active',
        'telegram_id',
        'user_access_token',
        'page_access_token'
    ];
}
<?php

namespace App\Model\CPA;

use Illuminate\Database\Eloquent\Model;

class AwardedWebs extends Model
{
    protected $table = 'webmasterdb.awarded_webs';
    protected $fillable =
        [
            'id',
            'apruv_count',
            'web_id'
        ];
}
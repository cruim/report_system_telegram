<?php

namespace App\Model\CPA;

use Illuminate\Database\Eloquent\Model;

class WebMaster extends Model
{
    protected $table = 'webmasterdb.webmaster_base';
    protected $fillable =
        [
            'id',
            'web_start_competion',
            'source_of_web'
        ];
}
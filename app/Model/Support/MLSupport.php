<?php

namespace App\Model\Support;

use Illuminate\Database\Eloquent\Model;

class MLSupport extends Model
{
    protected $table = 'telegram.ml_support';
    protected $fillable =
        [
            'id',
            'category',
            'url',
            'question',
            'answer',
            'keywords'
        ];
}
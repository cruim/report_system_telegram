<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'telegram.groups';
    protected $fillable =
        [
            'group_id',
            'group_name'
        ];
}
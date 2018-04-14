<?php

namespace App\administration;

use Illuminate\Database\Eloquent\Model;

class visit_log extends Model
{
    protected $table = 'visit_log';
    protected $fillable = ['id', 'user_id', 'created_at', 'updated_at'];
}

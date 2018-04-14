<?php

namespace App\Model\CPA;

use Illuminate\Database\Eloquent\Model;

class EventStandings extends Model
{
    protected $table = 'webmasterdb.event_standings';
    protected $fillable =
        [
            'id',
            'web_id',
            'apruv_count'
        ];
}
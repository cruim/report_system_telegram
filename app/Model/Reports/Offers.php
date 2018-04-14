<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    protected $table = 'reference.offers';
    protected $fillable =
        [
            'offer_name'
        ];
}
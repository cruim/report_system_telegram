<?php

namespace App\Model\VTiger;

use Illuminate\Database\Eloquent\Model;

class CrmData extends Model
{
    protected $table = 'crm_data';
    protected $fillable = [ 'order_id',
        'order_num',
        'order_custom_num',
        'createdtime',
        'modifiedtime',
        'order_status',
        'curency',
        'offer',
        'total_sum',
        'country',
        'delivery_service',
        'sp_delivery_date',
        'sp_delivery_time',
        'area',
        'ship_city',
        'user_id',
        'last_name',
        'first_name',
        'stager',
        'position',
        'department',
        'subdivision',
        'group',
        'utm_source',
        'sp_utm_content',
        'landing',
        'landing_type',
        'language',
        'sp_lead_cost_db',
        'sp_lead_cost_pp',
        'payment_currency',
        'telegram_id',
        'validation_status'
    ];
}
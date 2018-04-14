<?php

namespace App\Model\VTiger;

use Illuminate\Database\Eloquent\Model;

class ReportDesigner extends Model
{
    protected $table = 'analytics.report_designer';
    protected $fillable = [
        'order_id',
        'order_num',
        'order_custom_num',
        'user_id',
        'manager',
        'position',
        'department',
        'subdivision',
        'group',
        'stager',
        'createdweek',
        'createdmonth',
        'createdtime',
        'createdtime_spec',
        'createdhour',
        'modifiedtime',
        'modifiedtime_spec',
        'order_status',
        'curency',
        'offer',
        'delivery_service',
        'total_sum',
        'total_sum_eur',
        'utm_source',
        'sp_utm_content',
        'country',
        'landing',
        'landing_type',
        'language',
        'apruv',
        'denial',
        'handing',
        'injob',
        'brak',
        'paid',
        'main_category',
        'category',
        'price_category',
        'offer_name',
        'country_name',
        'location',
        'motivate',
        'motivate_test',
        'currency_zone',
        'sp_lead_cost_db',
        'sp_lead_cost_pp',
        'zone_subordinate',
        'payment_currency',
        'ship_city',
        'first_status_change',
        'sp_cross_sale',
        'variforts',
        'sp_receiving_money_date',
        'preapproved',
        'sp_net_so_number'
        ];
}

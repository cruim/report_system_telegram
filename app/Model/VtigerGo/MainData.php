<?php

namespace App\Model\VtigerGo;

use Illuminate\Database\Eloquent\Model;

class MainData extends Model
{
    protected $connection = 'vtiger_go';
    protected $table = 'main_data';
    protected $fillable = ['salesorderid',
        'salesorder_no',
        'createdtime',
        'subject',
        'sp_offer',
        'sp_method',
        'sostatus',
        'sp_utm_medium',
        'Apruv',
        'New',
        'sp_utm_content'];
    
}

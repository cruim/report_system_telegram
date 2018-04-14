<?php

namespace App\Model\VtigerGo;

use Illuminate\Database\Eloquent\Model;

class VtigerSMSNow extends Model
{
    protected $connection = 'vtiger_go';
    protected $table = 'vtiger_sms_now';
    protected $fillable = [
        'subject',
        'leads',
        'limit',
        'perc',
        'sp_offer'];

}

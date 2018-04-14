<?php

namespace App\Model\VtigerGo;

use Illuminate\Database\Eloquent\Model;

class VtigerSMS extends Model
{
    protected $connection = 'vtiger_go';
    protected $table = 'vtiger_sms';
    protected $fillable = [
        'subject',
        'leads',
        'limit',
        'perc',
        'sp_offer'];

}

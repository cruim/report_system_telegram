<?php

namespace App\Model\Reports;

use Illuminate\Database\Eloquent\Model;

class TverlangCrmEntity extends Model
{
    protected $connection = 'tver_lang';
    protected $table = 'crm-tverlang.vtiger_crmentity';
    protected $fillable =
        [
            'crmid',
            'createdtime'
        ];

}

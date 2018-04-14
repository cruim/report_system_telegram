<?php

namespace App\Model\VtigerGo;

use Illuminate\Database\Eloquent\Model;

class VtigerSubject extends Model
{
    protected $connection = 'vtiger_go2';
    protected $table = 'vtiger_subject';
    protected $fillable = ['subjectid',
        'subject',
        'presence',
        'picklist_valueid',
        'sortorderid'];

}

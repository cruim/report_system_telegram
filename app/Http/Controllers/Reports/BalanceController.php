<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Http\Request;
use App\Model\Reports\Balance;
use App\Http\Requests;
use DB;
use App\Http\Controllers\Controller;

class BalanceController extends Controller
{
    public function Index($chatid)
    {
        $result = Balance::select(DB::raw("names.name_to_show as `name`,
                                  balance.balance"))
                          ->join('balance.names','balance.url','=','names.name_orig')
                          ->where('names.hidden','<>','1')
                          ->where('Date','=',date('Y-m-d'))
                          ->get();
        $text = '[Wiki](http://wiki.finereports.info/index.php?title=Баланс)' . chr(10);
        $text .= date('d.m.') . chr(10);
        foreach ($result as $row) {
            $text .= $row->name . ': ' . $row->balance . chr(10);
        }
        return $text;
    }
}

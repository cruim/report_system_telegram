<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\Reports\TverlangSalesOrder;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TverLangController extends Controller
{
    public function getTodayLeadsInfo()
    {
        $result = TverlangSalesOrder::select(DB::raw("sostatus,count(*) as lead_count"))
            ->join("vtiger_crmentity","vtiger_salesorder.salesorderid","=","vtiger_crmentity.crmid")
            ->whereRaw("date(createdtime) = CURDATE()")
            ->groupBy(DB::raw("sostatus with rollup"))
            ->get();

        $text = date('Y-m-d') . chr(10) .chr(10);

        if(count($result) != 0)
        {
            foreach ($result as $value)
            {
                if(is_null($value->sostatus))
                {
                    $text .= 'Итого: ' . $value->lead_count . chr(10);
                }
                else
                {
                    $text .= $value->sostatus . ': ' . $value->lead_count .chr(10);
                }
            }
        }
        else
        {
            $text .= 'Сегодня не было заявок';
        }


        return $text;
    }

    function checkNewOrder()
    {
        $send_message = new MessageController();
//        $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'),'В TverLang поступила новая заявка','common');
        $send_message->sendMessage('153470584','В TverLang поступила новая заявка','common'); // соловьев
    }
}

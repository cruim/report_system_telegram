<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UntakenOrdersController extends Controller
{
    public function BuildReportUntakenOrders($chatid)
    {
        $result = ReportDesigner::select(DB::raw("ifnull(
		`report_designer`.`country_name`,'не указан') AS `source`,count(0) AS `count`"))
            ->where("order_status","=","Новый")
            ->where("manager","=","заказ Нераспределенный")
            ->whereRaw("`createdtime_spec` < (now() - INTERVAL 1 HOUR)")
            ->whereRaw("`createdtime` > '2017-04-16'")
            ->groupBy("country_name")
            ->orderBy("count","desc")
            ->get();

        if(count($result) >0)
        {
            $text = '[Wiki ](http://wiki.finereports.info/index.php?title=Клиент_ждет_звонка)' . chr(10);
            $text .= 'КЛИЕНТ ЖДЕТ ЗВОНКА БОЛЕЕ ЧАСА:'.chr(10).chr(10);

            foreach ($result as $value)
            {
                $text .= $value->source.' - не распределено '.$value->count.' шт.'.chr(10).'Из них: '.chr(10);

                $inner_result = ReportDesigner::select(DB::raw("IFNULL(`report_designer`.`utm_source`,
            'не указан') AS `source`,COUNT(0) AS `count`"))
                    ->where("order_status","=","Новый")
                    ->where("manager","=","заказ Нераспределенный")
                    ->whereRaw("`createdtime_spec` < (now() - INTERVAL 1 HOUR)")
                    ->whereRaw("`createdtime` > '2017-04-16'")
                    ->where("country_name","=",$value->source)
                    ->groupBy("country_name")
                    ->orderBy("count","desc")
                    ->get();
                foreach ($inner_result as $value)
                {
                    $text .= $value->source.' - '.$value->count.' шт.'.chr(10);
                }
            }
            return $text;
        }
        else{$text = 'Заявок, когда клиент ждет звонка более часа, нет';}
        return $text;
    }
}

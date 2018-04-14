<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaGeoWebController extends Controller
{
    function getStafferWebs()
    {
        $apiLink = 'https://a.zcpa.ru//affiliate/staff-list?api_key=645j6nbuhb4234v2y123ggh123123jjbghrw';
        $res = file_get_contents($apiLink);
        $obj_res = json_decode($res);
        $webs = implode(",",$obj_res->items);

        return $webs;
    }

    public function getData($chatid)
    {
        $manual = new LogController();
        $manual->setManualInputReport($chatid,'zcpageo');

        try{
            $keyboard = [['Geo_Zcpa - Сегодня 🦋', 'Geo_Zcpa - Вчера 🐛'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid,'Выберите временной промежуток','zcpa_dir',$reply_markup);
        }catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return 'Введите дату в формате 2017-02-29';
    }

    function getTodayData()
    {

        $text = date("Y-m-d") . chr(10) . chr(10);

        $stuffer_webs = $this->getStafferWebs();
        $stuff_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE()
and utm_source = 'zcpa'
and sp_utm_content in ($stuffer_webs)
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE()
and sp_utm_content in ($stuffer_webs)
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $result= DB::select(DB::raw($stuff_query));

        $text .= 'Zcpa внутренние:' . chr(10);

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $outstuff_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE()
and utm_source = 'zcpa'
and sp_utm_content not in ($stuffer_webs)
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE()
and sp_utm_content not in ($stuffer_webs)
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $text .= chr(10) . 'Zcpa внешние:' . chr(10);

        $result= DB::select(DB::raw($outstuff_query));

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $overal_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE()
and utm_source = 'zcpa'
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE()
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $text .= chr(10) . 'Zcpa всего:' . chr(10);

        $result= DB::select(DB::raw($overal_query));

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        return $text;
    }

    function getYesterdayData()
    {

        $text = $date = date("Y-m-d", strtotime( '-1 days' )) . chr(10) . chr(10);

        $stuffer_webs = $this->getStafferWebs();
        $stuff_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE() - interval 1 day
and utm_source = 'zcpa'
and sp_utm_content in ($stuffer_webs)
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE() - interval 1 day
and sp_utm_content in ($stuffer_webs)
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $result= DB::select(DB::raw($stuff_query));

        $text .= 'Zcpa внутренние:' . chr(10);

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $outstuff_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE() - interval 1 day
and utm_source = 'zcpa'
and sp_utm_content not in ($stuffer_webs)
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE() - interval 1 day
and sp_utm_content not in ($stuffer_webs)
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $text .= chr(10) . 'Zcpa внешние:' . chr(10);

        $result= DB::select(DB::raw($outstuff_query));

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $overal_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE() - interval 1 day
and utm_source = 'zcpa'
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = CURDATE() - interval 1 day
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $text .= chr(10) . 'Zcpa всего:' . chr(10);

        $result= DB::select(DB::raw($overal_query));

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        return $text;
    }

    function getCustomData($createdtime,$chatid)
    {

        $text = date("$createdtime") . chr(10) . chr(10);

        $stuffer_webs = $this->getStafferWebs();
        $stuff_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = '$createdtime'
and utm_source = 'zcpa'
and sp_utm_content in ($stuffer_webs)
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = '$createdtime'
and sp_utm_content in ($stuffer_webs)
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $result= DB::select(DB::raw($stuff_query));

        $text .= 'Zcpa внутренние:' . chr(10);

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $outstuff_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = '$createdtime'
and utm_source = 'zcpa'
and sp_utm_content not in ($stuffer_webs)
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = '$createdtime'
and sp_utm_content not in ($stuffer_webs)
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $text .= chr(10) . 'Zcpa внешние:' . chr(10);

        $result= DB::select(DB::raw($outstuff_query));

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $overal_query = "select 'Итого' as c_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = '$createdtime'
and utm_source = 'zcpa'
union all
select country_name, count(*) as total_leads, round(sum(apruv)/count(*)*100) as apruv_persent
from analytics.report_designer
where date(createdtime) = '$createdtime'
and utm_source = 'zcpa'
group by country
order by total_leads desc";

        $text .= chr(10) . 'Zcpa всего:' . chr(10);

        $result= DB::select(DB::raw($overal_query));

        foreach ($result as $value)
        {
            $text .= $value->c_name . ' - ' . $value->total_leads . '/' . $value->apruv_persent . '%' . chr(10);
        }

        $message = new MessageController();
        $message->sendMessage($chatid,$text,'zcpa_dir');

        $log = new LogController();
        $log->setTelegramLog($chatid,'Geo_Zcpa - Custom',$text);
    }
}

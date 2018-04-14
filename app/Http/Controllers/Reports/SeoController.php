<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SeoController extends Controller
{
    public function getSeoDataLastMonthRange($telegram_id)
    {
        $result = ReportDesigner::select(DB::raw("concat(createdmonth,' Итого - ',count(*),'/',
        round((sum(apruv)/count(*))*100),'%')COLLATE utf8_general_ci as seo_data"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH))")
            ->whereRaw("landing in ('zdorovface.com','cream-artraid.com','artraidcream.com','zdorov-varicream.com',
                'varikoz.zdorov-story.ru','deparazit.com','deparazit.ru','eliksir-deparazit.com','zdorov-slim.com',
                'cream-cleanfoot.com','creamfoot.com','riseons.com','head-and-hair.com','head-hair.com','store-zdorov.ru')")
            ->whereRaw("order_status not in ('тест','брак','дубль','фейк')");


        $total_result = ReportDesigner::select(DB::raw("concat(createdmonth,' ',landing,' - ',count(*),'/',
        round((sum(apruv)/count(*))*100),'%') as seo_data"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH))")
            ->whereRaw("landing in ('zdorovface.com','cream-artraid.com','artraidcream.com','zdorov-varicream.com',
                'varikoz.zdorov-story.ru','deparazit.com','deparazit.ru','eliksir-deparazit.com','zdorov-slim.com',
                'cream-cleanfoot.com','creamfoot.com','riseons.com','head-and-hair.com','head-hair.com','store-zdorov.ru')")
            ->whereRaw("order_status not in ('тест','брак','дубль','фейк')")
            ->groupBy("landing")
            ->union($result)
            ->get();
        $to_string = '';
        foreach ($total_result as $item)
        {
            $to_string .= $item->seo_data . chr(10);
        }

        if($to_string == '')
        {
            $to_string = 'Нет лидов за выбранный период!';
        }

        return ($to_string);
    }

    public function getSeoDataMonthRange($telegram_id)
    {
        $total_result = ReportDesigner::select(DB::raw("concat(DATE_FORMAT(CURDATE() - INTERVAL 1 month,'%d.%m.'),
        '-',DATE_FORMAT(CURDATE(),'%d.%m.'),' ',landing,' - ',count(*),'/',round((sum(apruv)/count(*))*100),'%') as seo_data"))
            ->whereRaw("createdtime >= CURDATE() - INTERVAL 1 month and createdtime <= CURDATE()")
            ->whereRaw("landing in ('zdorovface.com','cream-artraid.com','artraidcream.com','zdorov-varicream.com',
                'varikoz.zdorov-story.ru','deparazit.com','deparazit.ru','eliksir-deparazit.com','zdorov-slim.com',
                'cream-cleanfoot.com','creamfoot.com','riseons.com','head-and-hair.com','head-hair.com','store-zdorov.ru')")
            ->whereRaw("order_status not in ('тест','брак','дубль','фейк')")
            ->groupBy("landing")
            ->get();

        $to_string = '';
        foreach ($total_result as $item)
        {
            $to_string .= $item->seo_data . chr(10);
        }

        if($to_string == '')
        {
            $to_string = 'Нет лидов за выбранный период!';
        }

        return ($to_string);
    }

    public function getSeoDataWeekRange($telegram_id)
    {
        $total_result = ReportDesigner::select(DB::raw("concat(DATE_FORMAT(CURDATE() - INTERVAL 1 week,'%d.%m.'),'-',DATE_FORMAT(CURDATE(),'%d.%m.'),' ',landing,' - ',count(*),'/',round((sum(apruv)/count(*))*100),'%') as seo_data"))
            ->whereRaw("createdtime >= CURDATE() - INTERVAL 1 week and createdtime <= CURDATE()")
            ->whereRaw("landing in ('zdorovface.com','cream-artraid.com','artraidcream.com','zdorov-varicream.com',
                'varikoz.zdorov-story.ru','deparazit.com','deparazit.ru','eliksir-deparazit.com','zdorov-slim.com',
                'cream-cleanfoot.com','creamfoot.com','riseons.com','head-and-hair.com','head-hair.com','store-zdorov.ru')")
            ->whereRaw("order_status not in ('тест','брак','дубль','фейк')")
            ->groupBy("landing")
            ->get();

        $to_string = '';
        foreach ($total_result as $item)
        {
            $to_string .= $item->seo_data . chr(10);
        }

        if($to_string == '')
        {
            $to_string = 'Нет лидов за выбранный период!';
        }

        return ($to_string);
    }

    public function getSeoDataYesterdayRange($telegram_id)
    {
        $total_result = ReportDesigner::select(DB::raw("concat(DATE_FORMAT(CURDATE() - INTERVAL 1 day  ,'%d.%m.'),' ',landing,' - ',count(*),'/',round((sum(apruv)/count(*))*100),'%') as seo_data"))
            ->whereRaw("createdtime = CURDATE() - INTERVAL 1 day")
            ->whereRaw("landing in ('zdorovface.com','cream-artraid.com','artraidcream.com','zdorov-varicream.com',
                'varikoz.zdorov-story.ru','deparazit.com','deparazit.ru','eliksir-deparazit.com','zdorov-slim.com',
                'cream-cleanfoot.com','creamfoot.com','riseons.com','head-and-hair.com','head-hair.com','store-zdorov.ru')")
            ->whereRaw("order_status not in ('тест','брак','дубль','фейк')")
            ->groupBy("landing")
            ->get();

        $to_string = '';
        foreach ($total_result as $item)
        {
            $to_string .= $item->seo_data . chr(10);
        }

        if($to_string == '')
        {
            $to_string = 'Нет лидов за выбранный период!';
        }

        return ($to_string);
    }

    public function getSeoDataTodayRange($telegram_id)
    {
        $total_result = ReportDesigner::select(DB::raw("concat(DATE_FORMAT(CURDATE(),'%d.%m.'),' ',landing,' - ',count(*),'/',round((sum(apruv)/count(*))*100),'%') as seo_data"))
            ->whereRaw("createdtime = CURDATE()")
            ->whereRaw("landing in ('zdorovface.com','cream-artraid.com','artraidcream.com','zdorov-varicream.com',
                'varikoz.zdorov-story.ru','deparazit.com','deparazit.ru','eliksir-deparazit.com','zdorov-slim.com',
                'cream-cleanfoot.com','creamfoot.com','riseons.com','head-and-hair.com','head-hair.com','store-zdorov.ru')")
            ->whereRaw("order_status not in ('тест','брак','дубль','фейк')")
            ->groupBy("landing")
            ->get();

        $to_string = '';
        foreach ($total_result as $item)
        {
            $to_string .= $item->seo_data . chr(10);
        }

        if($to_string == '')
        {
            $to_string = 'Нет лидов за выбранный период!';
        }

        return ($to_string);
    }
}

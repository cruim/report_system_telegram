<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaGetWebmasterDataController extends Controller
{
    public function getData($chatid)
    {
        $manual = new LogController();
        $manual->setManualInputReport($chatid,'getwebdata');

        return 'Введите номер вебмастера.';
    }

    public function buildDateKeyboard($chatid)
    {
        try{
            $keyboard = [['Вебмастер - Сегодня', 'Вебмастер - вчера',],
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

    }

    public function getWebmasterTodayData($chatid)
    {
        $date = date("Y-m-d");

        $web_id = Abonent::select("manual_input_report")
            ->where("telegram_id", "=", "$chatid")
            ->get();

        $web_id = $web_id[0]->manual_input_report;

        $text = $date . chr(10) . chr(10);

        $result = ReportDesigner::select(DB::raw("createdtime,offer_name,country, count(*) as total_leads, 
        round(sum(apruv)/count(*)*100) as apruv_persent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("utm_source","=","zcpa")
            ->where("sp_utm_content","=",$web_id)
            ->whereRaw("date(createdtime) = CURDATE()")
            ->groupBy("country","offer_name","createdtime")
            ->orderBy("createdtime")
            ->orderBy("offer_name")
            ->orderBy("country")
            ->get();

        $past_result = ReportDesigner::select(DB::raw("createdtime,offer_name,country, count(*) as total_leads, 
        round(sum(apruv)/count(*)*100) as apruv_persent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("sp_utm_content","=",$web_id)
            ->whereRaw("date(createdtime) = CURDATE() - interval 1 day")
            ->groupBy("country","offer_name","createdtime")
            ->orderBy("createdtime")
            ->orderBy("offer_name")
            ->orderBy("country")
            ->get();
        $offer = '';
        $difference = 0;
        if(count($result) != 0)
        {
            foreach ($result as $value)
            {
                if($offer != $value->offer_name)
                {
                    $text .= chr(10) . $value->offer_name . chr(10) . chr(10);
                    $offer = $value->offer_name;
                }

                if(count($past_result) != 0)
                {
                    foreach ($past_result as $past_value)
                    {
                        if($value->offer_name == $past_value->offer_name and $value->country == $past_value->country)
                            $difference = round(($value->total_leads - $past_value->total_leads) * 100 / $past_value->total_leads);
                    }
                }

                $text .= '(' . $value->country . ') ' . $value->total_leads . ' (' . $difference . '%) ' .
                    $value->apruv_persent . '% - ' . $value->avg_check . chr(10);
            }
        }
        else{$text .= 'Нет данных за выбранный период';}


        return $text;
    }

    public function getWebmasterYesterdayData($chatid)
    {
        $date = date("Y-m-d", strtotime( '-1 days' ));

        $web_id = Abonent::select("manual_input_report")
            ->where("telegram_id", "=", "$chatid")
            ->get();

        $web_id = $web_id[0]->manual_input_report;

        $text = $date . chr(10) . chr(10);

        $result = ReportDesigner::select(DB::raw("createdtime,offer_name,country, count(*) as total_leads, 
        round(sum(apruv)/count(*)*100) as apruv_persent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("utm_source","=","zcpa")
            ->where("sp_utm_content","=",$web_id)
            ->whereRaw("date(createdtime) = CURDATE() - interval 1 day")
            ->groupBy("country","offer_name","createdtime")
            ->orderBy("createdtime")
            ->orderBy("offer_name")
            ->orderBy("country")
            ->get();

        $past_result = ReportDesigner::select(DB::raw("createdtime,offer_name,country, count(*) as total_leads, 
        round(sum(apruv)/count(*)*100) as apruv_persent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("sp_utm_content","=",$web_id)
            ->whereRaw("date(createdtime) = CURDATE() - interval 2 day")
            ->groupBy("country","offer_name","createdtime")
            ->orderBy("createdtime")
            ->orderBy("offer_name")
            ->orderBy("country")
            ->get();
        $offer = '';
        $difference = 0;
        if(count($result) != 0)
        {
            foreach ($result as $value)
            {
                if($offer != $value->offer_name)
                {
                    $text .= chr(10) . $value->offer_name . chr(10) . chr(10);
                    $offer = $value->offer_name;
                }

                if(count($past_result) != 0)
                {
                    foreach ($past_result as $past_value)
                    {
                        if($value->offer_name == $past_value->offer_name and $value->country == $past_value->country)
                            $difference = round(($value->total_leads - $past_value->total_leads) * 100 / $past_value->total_leads);
                    }
                }

                $text .= '(' . $value->country . ') ' . $value->total_leads . ' (' . $difference . '%) ' .
                    $value->apruv_persent . '% - ' . $value->avg_check . chr(10);
            }
        }
        else{$text .= 'Нет данных за выбранный период';}

        return $text;
    }

    public function getTodayData()
    {
        $date = date("Y-m-d");

        $text = date("Y-m-d") . chr(10);

        $result = ReportDesigner::select(DB::raw("createdtime,offer_name,country, count(*) as total_leads, 
        round(sum(apruv)/count(*)*100) as apruv_persent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("sp_utm_content","=",42)
            ->whereRaw("date(createdtime) = CURDATE()")
            ->groupBy("country","offer_name","createdtime")
            ->orderBy("createdtime")
            ->orderBy("offer_name")
            ->orderBy("country")
            ->get();

        $past_result = ReportDesigner::select(DB::raw("createdtime,offer_name,country, count(*) as total_leads, 
        round(sum(apruv)/count(*)*100) as apruv_persent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("sp_utm_content","=",42)
            ->whereRaw("date(createdtime) = CURDATE() + interval 1 day")
            ->groupBy("country","offer_name","createdtime")
            ->orderBy("createdtime")
            ->orderBy("offer_name")
            ->orderBy("country")
            ->get();
        $offer = '';
        $difference = 0;
        if(count($result) != 0)
        {
            foreach ($result as $value)
            {
                if($offer != $value->offer_name)
                {
                    $text .= $value->offer_name . chr(10);
                    $offer = $value->offer_name;
                }

                if(count($past_result) != 0)
                {
                    foreach ($past_result as $past_value)
                    {
                        if($value->offer_name == $past_value->offer_name and $value->country == $past_value->country)
                            $difference = round(($value->total_leads - $past_value->total_leads) * 100 / $past_value->total_leads);
                    }
                }

                $text .= '(' . $value->country . ') ' . $difference . '%' . '/' . $value->total_leads . '/' .
                    $value->apruv_persent . ' - ' . $value->avg_check ;
            }
        }

        return $text;
    }
}

<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\CurlController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\Reports\Offers;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class WebOffersController extends Controller
{
    public function getWebOffersTodayData($telegram_id)
    {
        $offer = Abonent::select("manual_input_report")
            ->where("telegram_id","=",$telegram_id)
            ->get();
        $offer = $offer[0]->manual_input_report;

        $zcpa_total = ReportDesigner::select(DB::raw("count(DISTINCT sp_utm_content) as web_count, count(*) as leads"))
            ->where("offer_name","=","$offer")
            ->where("utm_source","=","zcpa")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE()")
            ->get();

        $outstaff_total = ReportDesigner::select(DB::raw("count(DISTINCT sp_utm_content) as web_count, count(*) as leads"))
            ->where("offer_name","=","$offer")
            ->where("utm_source","<>","zcpa")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE()")
            ->get();

        $total = ReportDesigner::select(DB::raw("count(DISTINCT sp_utm_content) as web_count, count(*) as leads"))
            ->where("offer_name","=","$offer")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE()")
            ->get();

        $result = ReportDesigner::select(DB::raw("offer_name,utm_source,sp_utm_content,count(*) as `leads`"))
            ->where("offer_name","=","$offer")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE()")
            ->groupBy("offer_name","sp_utm_content")
            ->orderBy("leads","desc")
            ->get();

        $text = $offer . chr(10) . chr(10);

        foreach ($zcpa_total as $value)
        {
            $text .= 'Внутрение: ' .$value->web_count . '/' . $value->leads . chr(10);
        }

        foreach ($outstaff_total as $value)
        {
            $text .= 'Внешние: ' .$value->web_count . '/' . $value->leads . chr(10);
        }

        foreach ($total as $value)
        {
            $text .= 'Всего: ' .$value->web_count . '/' . $value->leads . chr(10) . chr(10);
        }

        foreach ($result as $value)
        {
            $sp_utm_content = $value->sp_utm_content;
            $sp_utm_content = str_replace('_',' ',$sp_utm_content);
            $text .= $value->utm_source . ' - ' . $sp_utm_content . ': ' . $value->leads . chr(10);
        }

        return $text;
    }

    public function getWebOffersYesterdayData($telegram_id)
    {
        $offer = Abonent::select("manual_input_report")
            ->where("telegram_id","=",$telegram_id)
            ->get();
        $offer = $offer[0]->manual_input_report;

        $zcpa_total = ReportDesigner::select(DB::raw("count(DISTINCT sp_utm_content) as web_count, count(*) as leads"))
            ->where("offer_name","=","$offer")
            ->where("utm_source","=","zcpa")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->get();

        $outstaff_total = ReportDesigner::select(DB::raw("count(DISTINCT sp_utm_content) as web_count, count(*) as leads"))
            ->where("offer_name","=","$offer")
            ->where("utm_source","<>","zcpa")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->get();

        $total = ReportDesigner::select(DB::raw("count(DISTINCT sp_utm_content) as web_count, count(*) as leads"))
            ->where("offer_name","=","$offer")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->get();

        $result = ReportDesigner::select(DB::raw("offer_name,utm_source,sp_utm_content,count(*) as `leads`"))
            ->where("offer_name","=","$offer")
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupBy("offer_name","sp_utm_content")
            ->orderBy("leads","desc")
            ->get();

        $text = $offer . chr(10) . chr(10);

        foreach ($zcpa_total as $value)
        {
            $text .= 'Внутрение: ' .$value->web_count . '/' . $value->leads . chr(10);
        }

        foreach ($outstaff_total as $value)
        {
            $text .= 'Внешние: ' .$value->web_count . '/' . $value->leads . chr(10);
        }

        foreach ($total as $value)
        {
            $text .= 'Всего: ' .$value->web_count . '/' . $value->leads . chr(10) . chr(10);
        }

        foreach ($result as $value)
        {
            $sp_utm_content = $value->sp_utm_content;
            $sp_utm_content = str_replace('_',' ',$sp_utm_content);
            $text .= $value->utm_source . ' - ' . $sp_utm_content . ': ' . $value->leads . chr(10);
        }

        return $text;
    }

    public function buildCustomKeyboardWebOffers($telegram_id,$text,$bot_name)
    {
        $key = Offers::select("offer_name")
            ->get();

        foreach ($key as $value)
        {
            $keyboard[] = array((string)$value->offer_name);
        }

        $keyboard[] = array('Назад');

        $reply_markup = \Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);

        $message = '[Wiki](http://wiki.finereports.info/index.php?title=Офферы/Веб-мастера)';
        $send_message = new MessageController();
        $send_message->sendMessage($telegram_id,$message,$bot_name,$reply_markup);

        return 'Формирую список.';
    }

    public function buildTodayYesterdayKeyboard($telegram_id,$text,$bot_name)
    {
        $keyboard[] = array('Оффер/Веб - Сегодня');
        $keyboard[] = array('Оффер/Веб - Вчера');
        $keyboard[] = array('Назад');

        $reply_markup = \Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);

        $send_message = new MessageController();
        $send_message->sendMessage($telegram_id,'Формирую список.',$bot_name,$reply_markup);
    }

    function webOfferArray()
    {
        $key = Offers::select("offer_name")
            ->get();
        $keyboard = [];

        foreach ($key as $value)
        {
            $keyboard[] = $value->offer_name;
        }

        return $keyboard;
    }
}

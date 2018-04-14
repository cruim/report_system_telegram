<?php

namespace App\Http\Controllers\Reports;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use DB;
use Illuminate\Http\Request;
use App\Model\VTiger\ReportDesigner;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class GeoController extends Controller
{
    function buildGeoKeyboard($chatid,$text,$bot_name)
    {
        try{
            $keyboard = [['ГЕО.Общий отчет', 'ГЕО.Европа(ру)'],
                ['ГЕО.Бурж','ГЕО.СНГ'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);

            $message = '[Wiki](http://wiki.finereports.info/index.php?title=ГЕО)';
            $send_message = new MessageController();
            $send_message->sendMessage($chatid,$message,$bot_name,$reply_markup);
        }catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return chr(10) . 'Выберите Гео';
    }

    function buildDateRangeKeyboard($chatid,$text,$bot_name)
    {
        $manual_input_report = new LogController();
        $manual_input_report->setManualInputReport($chatid,$text);

        try{
            $keyboard = [['ГЕО.Сегодня', 'ГЕО.Вчера'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid,'Выберите',$bot_name,$reply_markup);
        }catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return 'Временной промежуток';
    }

    function getTodayData($chatid)
    {
        $manual_input_report = Abonent::select("manual_input_report")
            ->where("telegram_id","=",$chatid)
            ->get();
        $manual_input_report = $manual_input_report[0]->manual_input_report;

        if($manual_input_report == 'ГЕО.Общий отчет')
        {
            return $this->totalTodatData($chatid);
        }
        elseif($manual_input_report == 'ГЕО.Европа(ру)')
        {
            return $this->euroRuTodayData($chatid);
        }
        elseif($manual_input_report == 'ГЕО.Бурж')
        {
            return $this->euroTodayData($chatid);
        }
        elseif($manual_input_report == 'ГЕО.СНГ')
        {
            return $this->sngTodayData($chatid);
        }
    }

    function getYesterdayData($chatid)
    {
        $manual_input_report = Abonent::select("manual_input_report")
            ->where("telegram_id","=",$chatid)
            ->get();
        $manual_input_report = $manual_input_report[0]->manual_input_report;

        if($manual_input_report == 'ГЕО.Общий отчет')
        {
            return $this->totalYesterdayData($chatid);
        }
        elseif($manual_input_report == 'ГЕО.Европа(ру)')
        {
            return $this->euroRuYesterdayData($chatid);
        }
        elseif($manual_input_report == 'ГЕО.Бурж')
        {
            return $this->euroYesterdayData($chatid);
        }
        elseif($manual_input_report == 'ГЕО.СНГ')
        {
            return $this->sngYesterdayData($chatid);
        }
    }

    public function totalTodatData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("country_name as `param`,
                    count(*) as `leads`, sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->apruv_count . '/' . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }

        return $text;
    }

    public function totalYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("country_name as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }
        return $text;
    }

    public function euroRuTodayData($chatid)
    {

        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("country_name as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }
        return $text;
    }

    public function euroRuYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("country_name as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }
        return $text;
    }

    public function euroTodayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("`language` IN ('FR','DE','EN','LV','IT','RO')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("language as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("`language` IN ('FR','DE','EN','LV','IT','RO')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }
        return $text;
    }

    public function euroYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("`language` IN ('FR','DE','EN','LV','IT','RO')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("language as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("`language` IN ('FR','DE','EN','LV','IT','RO')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }
        return $text;
    }

    public function sngTodayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'RUB'")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("country_name as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'RUB'")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }
        return $text;
    }

    public function sngYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'RUB'")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("country_name as `param`,
                    count(*) as `leads`,sum(apruv) as apruv_count,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`,
                    ifnull(round(sum(total_sum) / sum(apruv)),0) as avg_check"))
            ->whereRaw("order_status not in ('Брак','Дубль','Не целевой','Фейк','Фейк вернулся')")
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'RUB'")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/'  . $value->apruv_count . '/'  . $value->aproove .
                '%(' . $value->avg_check . ')' . chr(10);
        }

        return $text;
    }
}

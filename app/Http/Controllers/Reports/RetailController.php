<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\AdminControllers\ConfigController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Telegram\Bot\BotsManager;

class RetailController extends Controller
{
    function buildKeyboard($chatid)
    {
        $manual_inport = new LogController();
        $manual_inport->setManualInputReport($chatid, 'Розница');
        $message = 'Выберите временной промежуток' . chr(10) . 'Или введите дату в формате 2017-02-29' . chr(10) .
            '[Wiki](http://wiki.finereports.info/index.php?title=Розница)';
        try
        {
            $keyboard = [['Розница Сегодня', 'Розница Вчера'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, $message, 'common', $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function getCustomData($telegram_id, $createtime)
    {
        $send_message = new MessageController();
        $send_message->sendMessage($telegram_id, 'Считаю', 'common');

        try
        {
            $to_string = $this->getDataFromApi($createtime,$createtime);
            $send_message = new MessageController();
            $send_message->sendMessage($telegram_id, $to_string, 'common');
            $log = new LogController();
            $log->setTelegramLog($telegram_id, 'Розница', $to_string);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function getTodayData()
    {
        $date = date("Y-m-d");
        return $this->getDataFromApi($date,$date);
    }

    public function getYesterdayData()
    {
        $date = date("Y-m-d", strtotime('-1 days'));
        return $this->getDataFromApi($date,$date);
    }

    public function getMonthData()
    {
        $sng = ReportDesigner::select(DB::raw("'СНГ',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->where("location", "=", "СНГ")
            ->where("price_category", "=", "Стандарт")
            ->whereRaw("country not in('BY','GE')");

        $euro_native = ReportDesigner::select(DB::raw("'Евро-натив',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("location in('Европа','Азия','Северная Америка')")
            ->whereRaw("`language` in('FR','DE','EN','LV')")
            ->whereRaw("price_category = 'Стандарт'");

        $deutsch_kc = ReportDesigner::select(DB::raw("'в т.ч. Нем. КЦ',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("department = 'Нем'");

        $india = ReportDesigner::select(DB::raw("'Индия',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("location = 'Азия'");

        $euro_ru = ReportDesigner::select(DB::raw("'Евро-ру',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("location in('Европа','Азия','Северная Америка')")
            ->whereRaw("`language` in('RU')")
            ->whereRaw("price_category = 'Стандарт'");

        $sng_99 = ReportDesigner::select(DB::raw("'СНГ за 99',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->where("location", "=", "СНГ")
            ->where("price_category", "=", "Оффер за 99")
            ->whereRaw("country not in('BY','GE')");

        $euro_1 = ReportDesigner::select(DB::raw("'Евро за 1',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("location in('Европа','Азия','Северная Америка')")
            ->whereRaw("price_category = 'Оффер за 1'");

        $ge_by = ReportDesigner::select(DB::raw("'GE и BY',
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("country in ('GE','BY')");

        $total = ReportDesigner::select(DB::raw("'Итого' as total,
            COUNT(*) AS `leads`,
            ROUND((SUM(apruv)/ COUNT(*)) * 100) AS `apruv`,
            ROUND(SUM(total_sum) / SUM(apruv)) AS `average`,
            SUM(total_sum) AS `total_sum`"))
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW())")
            ->whereRaw("country not in('BY','GE')")
            ->union($sng)
            ->union($euro_native)
            ->union($deutsch_kc)
            ->union($india)
            ->union($euro_ru)
            ->union($sng_99)
            ->union($euro_1)
            ->union($ge_by)
            ->get();

        $to_string = '01.' . date('m') . ' - ' . date('d.m') . chr(10);
        foreach ($total as $item)
        {
            if ($item->leads > 0)
            {
                $to_string .= $item->total . ':' . $item->leads . '/' . $item->apruv . '/' . $item->average . '/' . $item->total_sum . chr(10);
            }

        }

        return $to_string;
    }

    function getDataFromApi($start_date, $end_date)
    {
        try
        {
            $token = $this->apiAuthorization();

            $params = [
                'action' => 'report',
                'params' => [
                    ['field' => 'date(createdtime)', 'key' => 'between', 'value' => [$start_date, $end_date]]
                ],

            ];
            $string = http_build_query($params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://api.finereports.info/retail?" . $string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt( $ch, CURLOPT_POST, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $token->token
            ));
            $result = json_decode(curl_exec($ch));
            curl_close($ch);
            $text = $start_date .chr(10);

            foreach ($result as $item)
            {
                $text .= $item->total . ':' . $item->leads . '/' . $item->approve . '/' . $item->average . '/' . $item->total_sum . chr(10);
            }

            return $text;
        }catch (\Exception $e)
        {
            $message = new MessageController();
            $message->sendMessage(348169607,$e->getMessage(),'common');
        }
    }

    function apiAuthorization()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.finereports.info/auth/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt( $ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=kraken36@list.ru&password=12038936147fish&client=web');
        $result = curl_exec($ch);
        curl_close($ch);
        $token = json_decode($result);

        return $token;
    }
}

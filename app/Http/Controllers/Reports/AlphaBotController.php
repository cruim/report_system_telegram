<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\VtigerGo\MainData;
use App\Model\VtigerGo\VtigerAlphaBalance;
use App\Model\VtigerGo\VtigerBalance;
use App\Model\VtigerGo\VtigerSMS;
use App\Model\VtigerGo\VtigerSMSNow;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AlphaBotController extends Controller
{
    function getBalances()
    {
        $result = VtigerAlphaBalance::select("subject", "balance", "limit_city")
            ->whereRaw("subject not like '%перелив%' and subject not like '%Фомин%' and subject <> 'novgorod/?vkextra'")
            ->get();

        $text = '';
        if (count($result) > 0)
        {
            foreach ($result as $value)
            {
                $text .= $value->subject . ': ' . $value->balance . '/' . $value->limit_city . chr(10);
            }
        }

        return $text;
    }

    function buildAlphaKeyboard($chatid)
    {
        $message = 'Выберите отчет';
        try
        {
            $keyboard = [['Балансы'],
                ['Альфа - Сегодня', 'Альфа - Вчера'],
                ['Альфа - Неделя', 'Альфа - Месяц'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, $message, 'alpha', $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }

    function getTodayData()
    {
        $result = VtigerSMSNow::select(DB::raw("*"))
            ->whereRaw("subject not like '%Перелив%'  and subject <> 'novgorod/?vkextra'")
            ->whereRaw("`limit` is not null")
            ->whereRaw("sp_offer in ('Варикоз','Кровь','Спина','Геморрой','Инфаркт')")
            ->orderByRaw('FIELD(sp_offer, "Варикоз", "Кровь", "Спина","Геморрой","Инфаркт")')
            ->get();

        $text = date('Y-m-d') . chr(10) . chr(10);

        $varicoz_leads = 0;
        $blood_leads = 0;
        $back_leads = 0;
        $gemor_leads = 0;
        $gemor_limit = 0;
        $infarct_leads = 0;
        $infarct_limit = 0;
        $varicoz_limit = 0;
        $blood_limit = 0;
        $back_limit = 0;
        foreach ($result as $value)
        {
            if($value->sp_offer == 'Варикоз'){$varicoz_leads += $value->leads;$varicoz_limit += $value->limit;}
            if($value->sp_offer == 'Кровь'){$blood_leads += $value->leads;$blood_limit += $value->limit;}
            if($value->sp_offer == 'Спина'){$back_leads += $value->leads;$back_limit += $value->limit;}
            if($value->sp_offer == 'Геморрой'){$gemor_leads += $value->leads;$gemor_limit += $value->limit;}
            if($value->sp_offer == 'Инфаркт'){$infarct_leads += $value->leads;$infarct_limit += $value->limit;}
        }

        $text .= $result[0]->sp_offer . '(' . $varicoz_limit . ')' . $varicoz_leads . chr(10);
        $sp_offer = $result[0]->sp_offer;
        foreach ($result as $value)
        {
            $limit = $value->limit;
            if($limit == null){$limit = 0;}
            $leads = $value->leads;
            if($leads == null){$leads = 0;}
            if ($value->sp_offer == 'Кровь' and $sp_offer != 'Кровь')
            {
                $text .= chr(10) . 'Кровь' . '(' . $blood_limit . ')' . $blood_leads . chr(10);
            }
            if ($value->sp_offer != $sp_offer and $value->sp_offer != 'Кровь' and $value->sp_offer != 'Геморрой')
            {
                $text .= chr(10) . 'Спина' . '(' . $back_limit . ')' . $back_leads . chr(10);
            }
            if ($value->sp_offer == 'Геморрой')
            {
                $text .= chr(10) . 'Геморрой' . '(' . $gemor_limit . ')' . $gemor_leads . chr(10);
            }
            if ($value->sp_offer == 'Инфаркт')
            {
                $text .= chr(10) . 'Инфаркт' . '(' . $infarct_limit . ')' . $infarct_leads . chr(10);
            }

            $text .= $value->subject  . " (" . $limit . ")" . $leads .chr(10);
            $sp_offer = $value->sp_offer;
        }
        if($gemor_limit == 0 and $gemor_leads == 0)
        {
            $text .= chr(10) . 'Геморрой' . '(' . $gemor_limit . ')' . $gemor_leads . chr(10);
        }

        return $text;
    }

    function getYesterdayData()
    {
        $result = VtigerSMS::select(DB::raw("*"))
            ->whereRaw("subject not like '%Перелив%'  and subject <> 'novgorod/?vkextra'")
            ->whereRaw("`limit` is not null")
            ->whereRaw("sp_offer in ('Варикоз','Кровь','Спина','Геморрой','Инфаркт')")
            ->orderByRaw('FIELD(sp_offer, "Варикоз", "Кровь", "Спина","Геморрой","Инфаркт")')
            ->get();

        $text = date('Y-m-d') . chr(10) . chr(10);

        $varicoz_leads = 0;
        $blood_leads = 0;
        $back_leads = 0;
        $gemor_leads = 0;
        $gemor_limit = 0;
        $infarct_leads = 0;
        $infarct_limit = 0;
        $varicoz_limit = 0;
        $blood_limit = 0;
        $back_limit = 0;
        foreach ($result as $value)
        {
            if($value->sp_offer == 'Варикоз'){$varicoz_leads += $value->leads;$varicoz_limit += $value->limit;}
            if($value->sp_offer == 'Кровь'){$blood_leads += $value->leads;$blood_limit += $value->limit;}
            if($value->sp_offer == 'Спина'){$back_leads += $value->leads;$back_limit += $value->limit;}
            if($value->sp_offer == 'Геморрой'){$gemor_leads += $value->leads;$gemor_limit += $value->limit;}
            if($value->sp_offer == 'Инфаркт'){$infarct_leads += $value->leads;$infarct_limit += $value->limit;}
        }

        $text .= $result[0]->sp_offer . '(' . $varicoz_limit . ')' . $varicoz_leads . chr(10);
        $sp_offer = $result[0]->sp_offer;
        foreach ($result as $value)
        {
            $limit = $value->limit;
            if($limit == null){$limit = 0;}
            $leads = $value->leads;
            if($leads == null){$leads = 0;}
            if ($value->sp_offer == 'Кровь' and $sp_offer != 'Кровь')
            {
                $text .= chr(10) . 'Кровь' . '(' . $blood_limit . ')' . $blood_leads . chr(10);
            }
            if ($value->sp_offer != $sp_offer and $value->sp_offer != 'Кровь' and $value->sp_offer != 'Геморрой')
            {
                $text .= chr(10) . 'Спина' . '(' . $back_limit . ')' . $back_leads . chr(10);
            }
            if ($value->sp_offer == 'Геморрой')
            {
                $text .= chr(10) . 'Геморрой' . '(' . $gemor_limit . ')' . $gemor_leads . chr(10);
            }
            if ($value->sp_offer == 'Инфаркт')
            {
                $text .= chr(10) . 'Инфаркт' . '(' . $infarct_limit . ')' . $infarct_leads . chr(10);
            }

            $text .= $value->subject  . " (" . $limit . ")" . $leads .chr(10);
            $sp_offer = $value->sp_offer;
        }
        if($gemor_limit == 0 and $gemor_leads == 0)
        {
            $text .= chr(10) . 'Геморрой' . '(' . $gemor_limit . ')' . $gemor_leads . chr(10);
        }

        return $text;
    }

    function getWeekData()
    {
        $text = date("Y-m-d", strtotime('-7 days')) . ' - ' . date("Y-m-d") . chr(10) . chr(10);
        $result = MainData::select(DB::raw("`subject`, COUNT(*) AS `leads`, SUM(Apruv) AS `aproove`"))
            ->whereRaw("`main_data`.`createdtime` BETWEEN DATE(NOW()) - INTERVAL 1 week AND DATE(NOW())")
            ->whereRaw("`main_data`.`subject` <> 'Брак'")
            ->groupBy("subject")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $perc = 0;
            if($value->leads !== 0){
                $perc = round(($value->aproove / $value->leads)*100);
            }
            $text .= $value->subject . ": " . $value->leads . "/" . $perc. "/" . $value->aproove . chr(10);
        }

        return $text;
    }

    function getMonthData()
    {
        $text = date("Y-m-d", strtotime('-30 days')) . ' - ' . date("Y-m-d") . chr(10) . chr(10);
        $result = MainData::select(DB::raw("`subject`, COUNT(*) AS `leads`, SUM(Apruv) AS `aproove`"))
            ->whereRaw("`main_data`.`createdtime` BETWEEN DATE(NOW()) - INTERVAL 1 month AND DATE(NOW())")
            ->whereRaw("`main_data`.`subject` <> 'Брак'")
            ->groupBy("subject")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $perc = 0;
            if($value->leads !== 0){
                $perc = round(($value->aproove / $value->leads)*100);
            }
            $text .= $value->subject . ": " . $value->leads . "/" . $perc. "/" . $value->aproove . chr(10);
        }

        return $text;
    }
}

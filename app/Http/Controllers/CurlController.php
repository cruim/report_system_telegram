<?php

namespace App\Http\Controllers;

use App\Model\Abonent;
use App\Model\BotToReport;
use App\Model\ReportParameters;
use App\Model\ReportToAbonent;
use Illuminate\Http\Request;

use App\Http\Requests;

class CurlController extends Controller
{
    public function sendMessageByCURL($chatid,$text,$bot_token,$replyMarkup = false)
    {
        $url = env('TELEGRAM_API') . $bot_token . '/sendmessage?chat_id=' . $chatid . '&text=' . $text;

        if($replyMarkup){
            $replyMarkup = json_encode($replyMarkup);
            $url .= '&reply_markup='.$replyMarkup;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        curl_close($curl);

        return response()->json(['success' => true]);
    }

    public function sendMessageByCURLZcpa($chatid,$text,$replyMarkup = false)
    {
        $url = env('TELEGRAM_API') . env('TELEGRAM_ZCPA_BOT_TOKEN') . '/sendmessage?chat_id=' . $chatid . '&text=' . $text;

        if($replyMarkup){
            $replyMarkup = json_encode($replyMarkup);
            $url .= '&reply_markup='.$replyMarkup;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        curl_close($curl);

        return response()->json(['success' => true]);
    }

    public function buildParametersKeyboardForCurl($chatid, $text)
    {
        $available_buttons = ReportParameters::select("report_parameters.parameters")
            ->join("telegram.reports", "report_parameters.report_id", "=", "reports.id")
            ->where("telegram_name", "=", "$text")
            ->get();

        foreach ($available_buttons as $value)
        {
            $keyboard[] = array((string)$value->parameters);
        }
        $keyboard[] = array('Назад');

        $mainKeyboard = array('keyboard'=>$keyboard, 'resize_keyboard'=>true);

        $this->sendMessageByCURLZcpa($chatid, $text,$mainKeyboard);
        return response()->json(['success' => true]);
    }

    public function buildCustomKeyboardForCurl($chatid, $abonent, $text)
    {
        $available_buttons = BotToReport::select("telegram_name")
            ->join("telegram.reports", "bot_to_report.report_id", "=", "reports.id")
            ->join("telegram.report_to_abonent","reports.id","=","report_to_abonent.report_id")
            ->where("bot_to_report.bot_id","=",6)
            ->where("abonent_id", "=", "$abonent")
            ->where("report_to_abonent.active", "=", 1)
            ->where("bot_to_report.active","=",1)
            ->groupBy("telegram_name")
            ->get();

        $keyboard = [];
        foreach ($available_buttons as $value)
        {
            $keyboard[] = array((string)$value->telegram_name);
        }

        $mainKeyboard = array('keyboard'=>$keyboard, 'resize_keyboard'=>true, 'one_time_keyboard' => true);

        $this->sendMessageByCURLZcpa($chatid, 'Список доступных отчетов находится ниже.',$mainKeyboard);
        return response()->json(['success' => true]);
    }

    public function checkAbonentAccessCurl($chatid, $text)
    {
        $abonent = Abonent::select("abonents.id")
            ->join("telegram.bot_to_abonent","abonents.id","=","bot_to_abonent.abonent_id")
            ->where("telegram_id", "=", $chatid)
            ->where("abonents.active", "=", 1)
            ->where("bot_to_abonent.active", "=", 1)
            ->where("bot_to_abonent.bot_id","=",6)
            ->get();

        if (count($abonent) == 0)
        {
            $response_text = "Я вас незнаю. Обратитесь к администратору, приложив Ваш ID $chatid.";
            $this->sendMessageByCURLZcpa($chatid,$response_text);

            exit();
        }

        return $abonent[0]->id;
    }
}

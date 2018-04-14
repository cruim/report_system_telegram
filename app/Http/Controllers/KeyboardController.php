<?php

namespace App\Http\Controllers;

use App\Model\BotToReport;
use App\Model\Report;
use App\Model\ReportParameters;
use Illuminate\Http\Request;

use App\Http\Requests;

class KeyboardController extends Controller
{
    function buildCustomKeyboard($chatid, $abonent, $text, $bot_id, $bot_name)
    {
        $available_buttons = BotToReport::select("telegram_name")
            ->join("telegram.reports", "bot_to_report.report_id", "=", "reports.id")
            ->join("telegram.report_to_abonent", "reports.id", "=", "report_to_abonent.report_id")
            ->where("bot_to_report.bot_id", "=", $bot_id)
            ->where("abonent_id", "=", "$abonent")
            ->where("report_to_abonent.active", "=", 1)
            ->where("bot_to_report.active", "=", 1)
            ->groupBy("telegram_name")
            ->get();
        $keyboard = array();
        foreach ($available_buttons as $value)
        {
            $keyboard[] = array((string)$value->telegram_name);
        }

        $reply_markup = \Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $send_message = new MessageController();
        $send_message->sendMessage($chatid, 'Список доступных отчетов находится ниже.', $bot_name, $reply_markup);
        return response()->json(['success' => true]);
    }

    function buildParametersKeyboard($chatid, $text, $bot_name)
    {
        $available_buttons = ReportParameters::select("report_parameters.parameters", "reports.wiki_link")
            ->join("telegram.reports", "report_parameters.report_id", "=", "reports.id")
            ->where("telegram_name", "=", "$text")
            ->get();

        foreach ($available_buttons as $value)
        {
            $keyboard[] = array((string)$value->parameters);
        }
        $keyboard[] = array('Назад');

        $reply_markup = \Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $message = 'Выбери временной интервал.';
        if (isset($available_buttons[0]->wiki_link))
        {
            $wiki_link = $available_buttons[0]->wiki_link;
            $message .= chr(10) . "[Wiki]($wiki_link)";
        }
        $send_message = new MessageController();
        $send_message->sendMessage($chatid, $message, $bot_name, $reply_markup);

        return response()->json(['success' => true]);
    }

    function getReportsWithCustomKeyboard()
    {
        try
        {
            $key = Report::select("telegram_name")
                ->where("parameters", "=", 1)
                ->get();
            $keyboard = [];
            foreach ($key as $value)
            {
                $keyboard[] = $value->telegram_name;
            }

            return $keyboard;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }
}

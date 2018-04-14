<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\AccessController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CPA\EventStandingsController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\Facebook\InstagramController;
use App\Http\Controllers\KeyboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Reports\AlphaBotController;
use App\Http\Controllers\Reports\CallCenterController;
use App\Http\Controllers\Reports\ChinilovDataController;
use App\Http\Controllers\Reports\RetailController;
use App\Http\Controllers\Reports\WebOffersController;
use App\Http\Controllers\Reports\ZcpaArmeniaController;
use App\Http\Controllers\Reports\ZcpaController;
use App\Model\BotToReport;
use App\Model\CPA\EventStandings;
use App\Model\ReportParameters;
use App\Model\VTiger\ReportDesigner;
use App\Model\VtigerGo\VtigerSMSNow;
use Illuminate\Http\Request;
use DB;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class ApiController extends Controller
{
    public function me()
    {
        $message = new RetailController();
        return $message->getYesterdayData();

//        return 1;
    }

    //head_bot
    public function setWebHook(Request $request)
    {
        try
        {
            $bot_id = 3;

            $bot_name = 'common';
            $chatid = $request['message']['chat']['id'];
            if ($chatid == null or $chatid == '' or !isset($chatid))
            {
                return response()->json(['success' => true]);
            }

            try
            {
                $text = (string)$request['message']['text'];
            } catch (\Exception $e)
            {
                $text = 'errorr';
            }
            $log = new LogController();
            $log->writeTextMessageFromAbonent($chatid, $text);
            $manual_input_report = $log->getManualInportReport($chatid);
            $access = new AccessController();
            $access->checkAbonentAccess($chatid, $text, $bot_id, $bot_name);


            $keyboard = new KeyboardController();
            $parameters_keyboard = $keyboard->getReportsWithCustomKeyboard();
            $offer_web = new WebOffersController();
            $offer_web = $offer_web->webOfferArray();

            if ($text == '/start' or $text == 'Назад')
            {
                $distribution = new DistributionController();
                $distribution->distribution($chatid, $text, $bot_id, $bot_name);
            } elseif (in_array($text, $offer_web))
            {
                $log->setManualInputReport($chatid, $text);
                $today_yesterday = new WebOffersController();
                $today_yesterday->buildTodayYesterdayKeyboard($chatid, $text, $bot_name);
            } elseif ($text == 'Розница')
            {
                $retail_custom = new RetailController();
                $retail_custom->buildKeyboard($chatid);
            } elseif ($manual_input_report == 'Розница' and \DateTime::createFromFormat('Y-m-d', (string)$text) !== FALSE)
            {
                $custom_retail = new RetailController();
                $custom_retail->getCustomData($chatid, $text);
            } elseif (count(ReportParameters::select("parameters")
                    ->whereRaw("parameters like '%$text%'")
                    ->get()) > 0
            )
            {
                if (in_array($text, $parameters_keyboard))
                {
                    $keyboard->buildParametersKeyboard($chatid, $text, $bot_name);
                } else
                {
                    try
                    {
                        $send_message = new MessageController();
                        $send_message->sendMessage($chatid, 'Считаю...', $bot_name);

                        $entity = new EntityController();
                        $controller = $entity->getReportControllerName($text);
                        $method = $entity->getReportMethodName($text);
                        if (is_string($method))
                        {
                            $report_data = new $controller();
                            $report_data = $report_data->$method($chatid, $text, $bot_name);

                            $send_message->sendMessage($chatid, $report_data, $bot_name);

                            $log->setTelegramLog($chatid, $text, $report_data);
                            return response()->json(['success' => true]);
                        } else
                        {
                            $send_message->sendMessage($chatid, 'Произошла ошибка. Сообщите о ней администратору.', $bot_name);
                            return response()->json(['success' => true]);
                        }

                    } catch (\Exception $e)
                    {
                        $message = $e->getMessage();
                        $code = $e->getCode();

                        $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . '), в отчете ' . $text;
                        $send_message = new MessageController();
                        $send_message->sendMessage($chatid, 'Произошла ошибка. Сообщите пожайлуста о ней администратору!', $bot_name);
                        $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"), $error_message, $bot_name);

                        $log->setTelegramLog($chatid, $text, 'Произошла ошибка. Сообщите пожайлуста о ней администратору!');

                        return response()->json(['success' => true]);
                    }
                }
            } else
            {
                $send_message = new MessageController();
                $send_message->sendMessage($chatid, 'Я Вас не понимаю. Чтобы посмотреть список доступных отчетов, введите команду /start', $bot_name);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            $log = new LogController();
            $log->setErrorLog('666', $e->getMessage());
            return response()->json(['success' => true]);
        }
//        return response()->json(['success' => true]);
    }
}

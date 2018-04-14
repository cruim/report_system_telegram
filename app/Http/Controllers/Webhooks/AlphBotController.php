<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\AccessController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\KeyboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Reports\AlphaBotController;
use App\Model\ReportParameters;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AlphBotController extends Controller
{
    function setWebHookAlphaBot(Request $request)
    {
        try
        {
            $bot_id = 9;

            $bot_name = 'alpha';

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

            $send_message = new MessageController();
            $log = new LogController();
            $log->writeTextMessageFromAbonent($chatid, $text);

            $access = new AccessController();
            $access->checkAbonentAccess($chatid, $text, $bot_id, $bot_name);;

            $keyboard = new KeyboardController();
            $parameters_keyboard = $keyboard->getReportsWithCustomKeyboard();

            if ($text == '/start' or $text == 'Назад')
            {
                $distribution = new DistributionController();
                $distribution->distribution($chatid, $text, $bot_id, $bot_name);
            } elseif ($text == 'Альфа')
            {
                $alpha_keyboard = new AlphaBotController();
                $alpha_keyboard->buildAlphaKeyboard($chatid);
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
                        $send_message->sendMessage($chatid, 'Считаю...', $bot_name);

                        $entity = new EntityController();
                        $controller = $entity->getReportControllerName($text);
                        $method = $entity->getReportMethodName($text);
                        if (is_string($method))
                        {
                            $report_data = new $controller();
                            $report_data = $report_data->$method($chatid);

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
                        $send_message->sendMessage($chatid, 'Произошла ошибка. Сообщите пожайлуста о ней администратору!', $bot_name);
                        $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"), $error_message, $bot_name);

                        $log->setTelegramLog($chatid, $text, 'Произошла ошибка. Сообщите пожайлуста о ней администратору!');

                        return response()->json(['success' => true]);
                    }
                }
            } else
            {
                $send_message->sendMessage($chatid, 'Я Вас не понимаю. Чтобы посмотреть список доступных отчетов, введите команду /start', $bot_name);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            $log = new LogController();
            $log->setErrorLog('666', $e->getMessage());;
            return response()->json(['success' => true]);
        }

    }
}

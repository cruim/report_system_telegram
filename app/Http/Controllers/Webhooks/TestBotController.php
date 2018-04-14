<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\AdminControllers\ConfigController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Reports\ZcpaController;
use App\Http\Controllers\Reports\ZcpaMyDataController;
use App\Model\ReportParameters;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Telegram\Bot\Actions;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TestBotController extends Controller
{
    function setWebHook(Request $request)
    {
        $bot_name = 'test';
        $chatid = $request['message']['chat']['id'];

//        if (isset($request['message']['contact']['phone_number']))
//        {
//            $log = new LogController();
//            $log->setErrorLog($chatid, $request['message']['contact']['phone_number']);
//        }
        try
        {
            $text = (string)$request['message']['text'];
        } catch (\Exception $e)
        {
            $text = 'errorr';
        }
//        if ($text == '/start')
//        {
//
//            try
//            {
//                $btn[] = [[
//                    'text' => "SHOW PHONE",
//                    'request_contact' => true
//                ]];
//                $reply_markup = \Telegram::replyKeyboardMarkup([
//                    'keyboard' => $btn,
//                    'resize_keyboard' => true,
//                    'one_time_keyboard' => true,
//                ]);
//                $send_message = new MessageController();
//                $send_message = $send_message->sendMessage($chatid, 'phone', $bot_name, $reply_markup);
//                $log = new LogController();
//                $log->setErrorLog($chatid, $send_message);
//            } catch (\Exception $e)
//            {
//                $send_message = new MessageController();
//                $send_message->sendMessage($chatid, $e->getMessage(), $bot_name);
//                return response()->json(['success' => true]);
//            }
//            return response()->json(['success' => true]);
//
//        }
        $send_message = new MessageController();
        $log = new LogController();
        $report = ReportParameters::select("parameters")
            ->whereRaw("parameters like '%$text%'")
            ->get();

        $check_access = new ZcpaController();
        $check_access = $check_access->checkZcpaUserAccess($chatid);

        if ($check_access[0] == 1 and $text == '/start')
        {
            $build_keyboard = new ZcpaMyDataController();
            $build_keyboard->buildWebmasterKeyboard($chatid, $bot_name);
        } elseif ($check_access[0] == 0 and filter_var($text, FILTER_VALIDATE_EMAIL))
        {
            $check_access = new ZcpaController();
            $check_access->sendVerificationEmail($chatid, $text);
            $send_message->sendMessage($chatid, 'На Ваш email отправлено письмо с подтверждением', $bot_name);
        } elseif ($check_access[0] == 0)
        {
            $message = 'Чтобы привязать Ваш telegram аккаунт к zcpa.ru, введите свой email';
            $send_message->sendMessage($chatid, $message, $bot_name);
        }
        elseif (count($report) > 0)

        {
                try
                {
                    $entity = new EntityController();
                    $controller = $entity->getReportControllerName($text);
                    $method = $entity->getReportMethodName($text);
                    if (is_string($method))
                    {
                        $report_data = new $controller();
                        $report_data = $report_data->$method($chatid,$check_access[1]);

                        $send_message->sendMessage($chatid, $report_data, $bot_name);

                        $log->setTelegramLog($chatid, $text, $report_data);
                        return response()->json(['success' => true]);
                    } else
                    {
                        $admin_message = 'У пользователя ' . $chatid . ' произошла ошибка ' . $text;
                        $send_message->sendMessage($chatid, 'Произошла ошибка. Сообщите о ней администратору.', $bot_name);
                        $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $admin_message, $bot_name);
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

        else
        {
            $send_message->sendMessage($chatid, 'заглушка', $bot_name);
        }
    }
}

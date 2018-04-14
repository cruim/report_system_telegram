<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\AccessController;
use App\Http\Controllers\CPA\EventStandingsController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\KeyboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Reports\ZcpaArmeniaController;
use App\Http\Controllers\Reports\ZcpaController;
use App\Http\Controllers\Reports\ZcpaGeoWebController;
use App\Http\Controllers\Reports\ZcpaGetWebmasterDataController;
use App\Http\Controllers\Reports\ZcpaMyDataController;
use App\Model\ReportParameters;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaBotController extends Controller
{
    function setWebHookZcpaBot(Request $request)
    {
        $bot_id = 6;

        $bot_name = 'zcpa';
        $keyboard = ['💸 Баланс','📈 Сегодня','📈 Вчера','7⃣ Посл. 7 дней', '🗓 Этот месяц'];
        $chatid = $request['message']['chat']['id'];

        try
        {
            $text = (string)$request['message']['text'];
        } catch (\Exception $e)
        {
            $text = 'errorr';
        }

        $send_message = new MessageController();
        $log = new LogController();

        $check_access = new ZcpaController();
        $check_access = $check_access->checkZcpaUserAccess($chatid);

        if ($check_access[0] == 1 and $text == '/start')
        {
            $build_keyboard = new ZcpaMyDataController();
            $build_keyboard->buildWebmasterKeyboard($chatid, $bot_name);
        } elseif ($check_access[0] == 0)
        {
            $zcpa_url = 'https://zcpa.ru/telegram/assign/' . $chatid;
            $message = 'Чтобы привязать Ваш telegram аккаунт к zcpa.ru,' . chr(10) . 'перейдите по ссылке ' . chr(10) . $zcpa_url;
            $send_message->sendMessage($chatid, $message, $bot_name);
        } elseif (in_array($text,$keyboard))

        {
            try
            {
                $entity = new EntityController();
                $controller = $entity->getReportControllerName($text);
                $method = $entity->getReportMethodName($text);
                if (is_string($method))
                {
                    $report_data = new $controller();
                    $report_data = $report_data->$method($chatid, $check_access[1]);

                    $send_message->sendMessage($chatid, $report_data, $bot_name);

                    $log->setZcpaLog($chatid, $text, $report_data);
                    return response()->json(['success' => true]);
                }

            } catch (\Exception $e)
            {
                $message = $e->getMessage();
                $code = $e->getCode();

                $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . '), в отчете ' . $text;

                $send_message->sendMessage($chatid, 'Произошла ошибка. Сообщите пожайлуста о ней администратору!', $bot_name);
                $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"), $error_message, $bot_name);

                $log->setZcpaLog($chatid, $text, 'Произошла ошибка. Сообщите пожайлуста о ней администратору!');

                return response()->json(['success' => true]);
            }
        }
        return response()->json(['success' => true]);
    }
}

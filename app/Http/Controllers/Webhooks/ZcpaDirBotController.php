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
use App\Http\Controllers\Reports\ZcpaGeoWebController;
use App\Http\Controllers\Reports\ZcpaGetWebmasterDataController;
use App\Http\Controllers\Reports\ZcpaMyDataController;
use App\Model\ReportParameters;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Telegram\Bot\Api;

class ZcpaDirBotController extends Controller
{
    function setWebHook(Request $request)
    {
        $bot_name = 'zcpa_dir';
        $chatid = $request['message']['chat']['id'];
        try
        {
            $text = (string)$request['message']['text'];
        } catch (\Exception $e)
        {
            $text = 'errorr';
        }
        try
        {
            $manual_array = ['zcpa_armenia', 'zcpageo', 'AM', 'GE', 'KZ'];
            $geo_array = ['🇷🇺 RU', '🇪🇺 EU', '🇦🇲 AM', '🇰🇿 KZ', '🇬🇪 GE', '🇰🇬 KG', '🇺🇿 UZ'];
            $manual_geo_array_eu = ['RU', 'EU', 'AM', 'KZ', 'GE', 'KG', 'UZ'];
            $manual_geo_array = ['RU', 'AM', 'KZ', 'GE', 'KG', 'UZ'];
            $bot_id = 11;
            $send_message = new MessageController();

            $log = new LogController();
            $log->writeTextMessageFromAbonent($chatid, $text);
            if (in_array($text, $manual_array))
            {
                $log->setManualInputReport($chatid, $text);
            }

            if (in_array($text, $geo_array))
            {
                $text = substr($text, -2);
                $log->setManualInputReport($chatid, $text);
            }

            $manual_input_report = $log->getManualInportReport($chatid);

            $access = new AccessController();
            $access->checkAbonentAccess($chatid, $text, $bot_id, $bot_name);

            $keyboard = new KeyboardController();
            $parameters_keyboard = $keyboard->getReportsWithCustomKeyboard();

            if (($text == '/start' or $text == 'Назад'))
            {
                $distribution = new DistributionController();
                $distribution->distribution($chatid, $text, $bot_id, $bot_name);
            } elseif ($manual_input_report == 'getwebdata' and is_numeric($text))
            {
                $custom_zcpa_keyboard = new ZcpaGetWebmasterDataController();
                $custom_zcpa_keyboard->buildDateKeyboard($chatid);
                $log->setManualInputReport($chatid, $text);
            } elseif ($manual_input_report == 'zcpageo' and \DateTime::createFromFormat('Y-m-d', (string)$text) !== FALSE)
            {
                try
                {
                    $send_message->sendMessage($chatid, 'Считаю...', $bot_name);
                    $geo_web = new ZcpaGeoWebController();
                    $geo_web->getCustomData($text, $chatid);
                } catch (\Exception $e)
                {
                    return response()->json(['success' => true]);
                }
            } elseif (in_array($text, $manual_geo_array_eu))
            {
                $range_keyboard = new ZcpaArmeniaController();
                $range_keyboard->getData($chatid);
            }
            elseif (in_array($manual_input_report, $manual_geo_array)
                and (\DateTime::createFromFormat('d-m-y', (string)$text) !== FALSE || $text == 'Сегодня' || $text == 'Вчера')
            )
            {
                try
                {
                    $send_message->sendMessage($chatid, 'Считаю...', $bot_name);
                    $geo_web = new ZcpaArmeniaController();
                    $geo_web->getCustomData($text, $chatid);
                } catch (\Exception $e)
                {
                    return response()->json(['success' => true]);
                }
            } elseif ($manual_input_report == 'EU' and
                (\DateTime::createFromFormat('d-m-y', (string)$text) !== FALSE || $text == 'Сегодня' || $text == 'Вчера'))
            {
                try
                {
                    $send_message->sendMessage($chatid, 'Считаю...', $bot_name);
                    $geo_web = new ZcpaArmeniaController();
                    $geo_web->getCustomEuroZone($text, $chatid);
                } catch (\Exception $e)
                {
                    return response()->json(['success' => true]);
                }
            }
            elseif (count(ReportParameters::select("parameters")
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
            } elseif (substr($manual_input_report, 0, 5) == 'event' and $text == 'Забрать приз')
            {
                $web_id = substr($manual_input_report, 5);
                $get_prize = new EventStandingsController();
                $get_prize->insertIntoPrizePool($web_id);
                $send_message->sendMessage($chatid, $web_id . ' Прекращает борьбу и забирает приз!', $bot_name);
            } elseif (substr($manual_input_report, 0, 5) == 'event' and $text == 'Продолжить участие')
            {
                $web_id = substr($manual_input_report, 5);
                $get_prize = new EventStandingsController();
                $get_prize->updateWebStatus($web_id);
                $send_message->sendMessage($chatid, $web_id . ' Продолжает борьбу!', $bot_name);
            } else
            {
                $send_message->sendMessage($chatid, 'Я Вас не понимаю. Чтобы посмотреть список доступных отчетов, введите команду /start', $bot_name);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            $log = new LogController();
            $log->setErrorLog('666', $e->getMessage());
            return response()->json(['success' => true]);
        }
    }

}

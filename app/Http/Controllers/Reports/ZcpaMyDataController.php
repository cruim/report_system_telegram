<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\AccessController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CurlController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\BotToReport;
use App\Model\Reports\ZcpaFilters;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaMyDataController extends Controller
{
    public function getTodayData($chatid,$web_id = null)
    {
        try
        {
            $date = date("Y-m-d");

            $url = 'https://a.zcpa.ru/telegram/stat?api_key=' . env('ZCPA_API_KEY') . '&telegramCode=' . $chatid .
                '&dateStart=' .  $date . '&dateEnd=' . $date;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($curl);
            curl_close($curl);

            $obj_res = json_decode($res);
            $text = '📈 Сводка за сегодня' . chr(10);
            if($obj_res->result == false)
            {
                $text = 'Пользователь с указанным telegram_id не найден';
            }
            else
            {
                foreach ($obj_res->data as $key => $value)
                {
                    $text .= 'Клики: ' . $value->click_count . chr(10)
                        . 'Уникальные клики: ' . $value->click_unique_count . chr(10)
                        . 'Конверсии: ' . $value->conversion_approved_count . chr(10)
                        . 'В ожидании: ' . $value->conversion_pending_count . chr(10)
                        . 'Отклоненные: ' . $value->conversion_rejected_count . chr(10)
                        . 'Треш: ' . $value->conversion_trash_count;
                }
            }

        }catch (\Exception $e)
        {
            $message = $e->getMessage();
            $code = $e->getCode();
            $send_message = new MessageController();
            $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
            $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
            return response()->json(['success' => true]);
        }

        return $text;
    }

    public function getYesterdayData($chatid,$web_id = null)
    {
        try
        {
            $date = date("Y-m-d", strtotime("yesterday"));

            $url = 'https://a.zcpa.ru/telegram/stat?api_key=' . env('ZCPA_API_KEY') . '&telegramCode=' . $chatid .
                '&dateStart=' .  $date . '&dateEnd=' . $date;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($curl);
            curl_close($curl);

            $obj_res = json_decode($res);
            $text = '📈 Сводка за Вчера' . chr(10);
            if($obj_res->result == false)
            {
                $text = 'Пользователь с указанным telegram_id не найден';
            }
            else
            {
                foreach ($obj_res->data as $key => $value)
                {
                    $text .= 'Клики: ' . $value->click_count . chr(10)
                        . 'Уникальные клики: ' . $value->click_unique_count . chr(10)
                        . 'Конверсии: ' . $value->conversion_approved_count . chr(10)
                        . 'В ожидании: ' . $value->conversion_pending_count . chr(10)
                        . 'Отклоненные: ' . $value->conversion_rejected_count . chr(10)
                        . 'Треш: ' . $value->conversion_trash_count;
                }
            }

        }catch (\Exception $e)
        {
            $message = $e->getMessage();
            $code = $e->getCode();
            $send_message = new MessageController();
            $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
            $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
            return response()->json(['success' => true]);
        }

        return $text;
    }

    public function getCurMonthData($chatid,$web_id = null)
    {
        try
        {
            $today = date("Y-m-d");
            $month_start = date("Y-m") . "-01";

            $url = 'https://a.zcpa.ru/telegram/stat?api_key=' . env('ZCPA_API_KEY') . '&telegramCode=' . $chatid .
                '&dateStart=' .  $month_start . '&dateEnd=' . $today;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($curl);
            curl_close($curl);

            $obj_res = json_decode($res);
            $text = '🗓 Сводка за текущий месяц' . chr(10);
            if($obj_res->result == false)
            {
                $text = 'Пользователь с указанным telegram_id не найден';
            }
            else
            {
                foreach ($obj_res->data as $key => $value)
                {
                    $text .= 'Клики: ' . $value->click_count . chr(10)
                        . 'Уникальные клики: ' . $value->click_unique_count . chr(10)
                        . 'Конверсии: ' . $value->conversion_approved_count . chr(10)
                        . 'В ожидании: ' . $value->conversion_pending_count . chr(10)
                        . 'Отклоненные: ' . $value->conversion_rejected_count . chr(10)
                        . 'Треш: ' . $value->conversion_trash_count;
                }
            }

        }catch (\Exception $e)
        {
            $message = $e->getMessage();
            $code = $e->getCode();
            $send_message = new MessageController();
            $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
            $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
            return response()->json(['success' => true]);
        }

        return $text;
    }

    function getLastSevenDaysData($chatid,$web_id = null)
    {
        try
        {
            $today = date("Y-m-d");
            $seven_days_ago = strtotime($today);
            $seven_days_ago = date("Y-m-d", strtotime("-7 day", $seven_days_ago));

            $url = 'https://a.zcpa.ru/telegram/stat?api_key=' . env('ZCPA_API_KEY') . '&telegramCode=' . $chatid .
                '&dateStart=' .  $today . '&dateEnd=' . $seven_days_ago;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($curl);
            curl_close($curl);

            $obj_res = json_decode($res);
            $text = '7⃣ Сводка за последнюю неделю' . chr(10);
            if($obj_res->result == false)
            {
                $text = 'Пользователь с указанным telegram_id не найден';
            }
            else
            {
                foreach ($obj_res->data as $key => $value)
                {
                    $text .= 'Клики: ' . $value->click_count . chr(10)
                        . 'Уникальные клики: ' . $value->click_unique_count . chr(10)
                        . 'Конверсии: ' . $value->conversion_approved_count . chr(10)
                        . 'В ожидании: ' . $value->conversion_pending_count . chr(10)
                        . 'Отклоненные: ' . $value->conversion_rejected_count . chr(10)
                        . 'Треш: ' . $value->conversion_trash_count;
                }
            }

        }catch (\Exception $e)
        {
            $message = $e->getMessage();
            $code = $e->getCode();
            $send_message = new MessageController();
            $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
            $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
            return response()->json(['success' => true]);
        }

        return $text;
    }

    function getWebmasterBalance($chatid,$web_id = null)
    {
        try
        {
            $send_message = new MessageController();
            if ($chatid == '153470584')
            {
                $web_id = '42';
            }
            if (is_numeric($web_id))
            {
                $url = 'https://a.zcpa.ru/balance/affiliate?api_key=' . env('ZCPA_API_KEY') . '&id=' . $web_id;
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $res = curl_exec($curl);
                curl_close($curl);

                $obj_res = json_decode($res);
                $text = '';


                if (count($obj_res->balances) == 0)
                {
                    $text = '0.00';
                    return $text;
                }

                else
                {
                    foreach ($obj_res->balances as $key => $value)
                    {
                        $text .= $value->iso . ': ' . round($value->balance, 2) . chr(10);
                    }
                    return $text;
                }

            } else
            {
                $send_message->sendMessage($chatid, 'Произошла ошибка, сообщите о ней администратору.', 'zcpa');
            }
        } catch (\Exception $e)
        {
            $message = $e->getMessage();
            $code = $e->getCode();
            $send_message = new MessageController();
            $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
            $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
            return response()->json(['success' => true]);
        }
    }

    public function GetGroupFilter($web_id)
    {
        try
        {
            $result = ZcpaFilters::select("filter_val")
                ->where("filter_name", "=", "my_stats")
                ->where("abonent_id", "=", $web_id)
                ->get();

            if (count($result) == 0)
            {
                DB::table('telegram.zcpa_filters')->insert(
                    ['abonent_id' => $web_id, 'filter_name' => 'my_stats', 'filter_val' => 'offer']
                );

                $result = ZcpaFilters::select("filter_val")
                    ->where("filter_name", "=", "my_stats")
                    ->where("abonent_id", "=", $web_id)
                    ->get();
            }
            return $result[0]->filter_val;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function buildWebmasterKeyboard($chatid,$bot_name)
    {
        try
        {
            $message = 'Стартовое меню';
            try
            {

                $keyboard = [['💸 Баланс'],
                    ['📈 Сегодня', '📈 Вчера'],
                    ['7⃣ Посл. 7 дней', '🗓 Этот месяц']];

                $reply_markup = \Telegram::replyKeyboardMarkup([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]);

                $send_message = new MessageController();
                $send_message->sendMessage($chatid, $message, $bot_name, $reply_markup);
                $log = new LogController();
                $log->setTelegramLog($chatid, 'ZCPA-WEB', $message);
            } catch (\Exception $e)
            {
                $message = $e->getMessage();
                $code = $e->getCode();
                $send_message = new MessageController();
                $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
                $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
                return response()->json(['success' => true]);
            }
        } catch (\Exception $e)
        {
            $message = $e->getMessage();
            $code = $e->getCode();
            $send_message = new MessageController();
            $error_message = 'У пользователя ' . $chatid . ' произошла ошибка - ' . $message . '(' . $code . ')';
            $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$error_message,'zcpa');
            return response()->json(['success' => true]);
        }
    }
}

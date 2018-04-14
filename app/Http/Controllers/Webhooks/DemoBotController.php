<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\CurlController;
use App\Http\Controllers\KeyboardController;
use App\Http\Controllers\MessageController;
use App\Model\Support\DemoAbonents;
use App\Model\Support\DemoScheduller;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class DemoBotController extends Controller
{
    function setWebHook(Request $request)
    {
//        $telegram = new Api(env('TELEGRAM_DEMO_BOT_TOKEN'));
//
//        $response = $telegram->setWebhook(['url' => 'https://bots.finereports.info/517974705:AAGSsAaAaNNAFW4oWcUEhK0KWJMN7nH8sS4/webhook']);
//
//        return $response;
        try
        {
            $bot_id = 11;

            $bot_name = 'demo';
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

            if ($text == '/start' or $text == 'Назад')
            {
                $this->buildStartKeyboard($chatid, $bot_name);
                $this->updateAbonent($chatid, $text);
            } elseif (mb_stripos($text, 'баланс') !== false)
            {
                $send_message = new MessageController();
                $send_message->sendMessage($chatid, $this->getTestBalance(), $bot_name);
                $this->updateAbonent($chatid, $text);
            } elseif (mb_stripos($text, 'переход') !== false)
            {
                $send_message = new MessageController();
                $send_message->sendMessage($chatid, 'https://www.google.ru/', $bot_name);
                $this->updateAbonent($chatid, $text);
            } elseif (mb_stripos($text, 'сегодня') !== false)
            {
                $this->updateAbonent($chatid, $text);
                return $this->getTestTodayData($chatid, $bot_name);
            } elseif (mb_stripos($text, 'вчера') !== false)
            {
                $this->updateAbonent($chatid, $text);
                return $this->getTestYesterdayData($chatid, $bot_name);
            } elseif (mb_stripos($text, 'настроить') !== false)
            {
                $this->updateAbonent($chatid,$text);
                $text = 'Для создания рассылки' . chr(10) .
                    'Введите время в формате ЧЧ:ММ:СС' . chr(10) .
                    'Для удаления всех рассылок введите 0';
                $message = new MessageController();
                $message->sendMessage($chatid, $text, $bot_name);
            } elseif (\DateTime::createFromFormat('G:i:s', $text) !== false and mb_stripos($this->getLabel($chatid), 'настроить') !== false)
            {
                return $this->setScheduller($chatid, $text);
            } elseif ($text == 0 and mb_stripos($this->getLabel($chatid), 'настроить') !== false)
            {
                $this->updateAbonent($chatid,$text);
                return $this->deleteScheduller($chatid);
            } elseif ($text != '🤖 Заказать бота' and mb_stripos($this->getLabel($chatid), 'заказать бота') !== false)
            {
                return $this->messageFromClient(env('TELEGRAM_ADMIN_ID'), 'Сообщение от клиента: ' . $chatid . ' ' .
                    $text, env('TELEGRAM_DEMO_BOT_TOKEN'));
            } elseif (mb_stripos($text, 'заказать бота') !== false)
            {
                $this->updateAbonent($chatid,$text);
                $send_message = new MessageController();
                $send_message->sendMessage($chatid, 'Оставьте свои контактные данные' . chr(10) . 'Мы обязательно свяжемся с Вами!', $bot_name);
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function buildStartKeyboard($chatid, $botname)
    {
        $message = 'Стартовое меню';
        try
        {
            $keyboard = [['💸 Баланс'],
                ['📈 Статистика Сегодня', '📈 Статистика Вчера'],
                ['✉ Настроить Рассылку', '🔗 Переход на Сайт'],
                ['🤖 Заказать бота']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, $message, $botname, $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function getTestBalance()
    {
        $text = 'Текущий баланс:' . chr(10) .
            '150000 ₽' . chr(10) .
            '99000 $' . chr(10) .
            '88000 €' . chr(10) .
            '150 btc';

        return $text;
    }

    function getTestTodayData($chatid, $botname)
    {

        try
        {
            $message = date("Y-m-d") . chr(10) . chr(10) .
                'Россия: 115/63%' . chr(10) .
                'Казахстан: 223/59%' . chr(10) .
                'Украина: 48/89%' . chr(10) .
                'Грузия: 400/61%';
            $path = dirname(__FILE__) . '/1.png';

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, $message, $botname);
            $send_message->sendPhoto($chatid, $botname, $path, 'Статистика');
        } catch (\Exception $e)
        {
            $send_message = new MessageController();
            $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $e->getMessage(), 'demo');
        }

        return response()->json(['success' => true]);
    }

    function getTestYesterdayData($chatid, $botname)
    {
        try
        {
            $message = date("Y-m-d", time() - 86400) . chr(10) . chr(10) .
                'Россия: 66/63%' . chr(10) .
                'Казахстан: 73/59%' . chr(10) .
                'Украина: 83/89%' . chr(10) .
                'Грузия: 55/61%';
            $path = dirname(__FILE__) . '/2.png';

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, $message, $botname);
            $send_message->sendPhoto($chatid, $botname, $path, 'Статистика');
        } catch (\Exception $e)
        {
            $send_message = new MessageController();
            $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $e->getMessage(), 'demo');
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }

    function updateAbonent($chatid, $text)
    {
        DemoAbonents::updateOrCreate(
            ['telegram_id' => $chatid], ['label' => $text]
        );
    }

    function getLabel($telegram_id)
    {
        $label = DemoAbonents::select("label")
            ->where("telegram_id", "=", $telegram_id)
            ->get();

        return $label[0]->label;

    }

    function setScheduller($chatid, $time)
    {
        DB::table('demo_test_scheduller')->insert(
            ['telegram_id' => $chatid,
                'sending_time' => $time]
        );
    }

    function deleteScheduller($chatid)
    {
        DB::table('demo_test_scheduller')->where('telegram_id', '=', $chatid)->delete();
    }

    function getSchedullerTasks()
    {
        try
        {
            $cur_time = date('H:i');
            $result = DemoScheduller::select("telegram_id", "sending_time")
                ->whereRaw("sending_time = TIME('$cur_time')")
                ->get();

            if (count($result) > 0)
            {
                foreach ($result as $value)
                {
                    return $this->getTestTodayData($value->telegram_id, 'demo');
                }
            }
        } catch (\Exception $e)
        {
            $send_message = new MessageController();
            $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $e->getMessage(), 'demo');
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => true]);
    }

    function messageFromClient($chatid, $text, $bot_token)
    {
        $send_message = new CurlController();
        $send_message->sendMessageByCURL($chatid, $text, $bot_token);
    }
}

<?php

namespace App\Http\Controllers;

use App\Model\Abonent;
use Illuminate\Http\Request;

use App\Http\Requests;

class AccessController extends Controller
{
    function checkAbonentAccess($chatid, $text, $bot_id, $bot_name)
    {
        try
        {
            $abonent = Abonent::select("abonents.id")
                ->join("telegram.bot_to_abonent", "abonents.id", "=", "bot_to_abonent.abonent_id")
                ->where("telegram_id", "=", $chatid)
                ->where("abonents.active", "=", 1)
                ->where("bot_to_abonent.active", "=", 1)
                ->where("bot_to_abonent.bot_id", "=", $bot_id)
                ->get();

            if (count($abonent) == 0)
            {
                $response_text = "Я вас незнаю. Обратитесь к администратору, приложив Ваш ID " . $chatid;
                $message_for_admin = 'Пользователь ' . $chatid . ' пытался зайти в ' . $bot_name;
                $send_message = new MessageController();
                $send_message->sendMessage($chatid, $response_text, $bot_name);
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $message_for_admin, $bot_name);

                exit();
            }

            return $abonent[0]->id;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function checkIsUserWebmaster($chatid)
    {
        $group_id = Abonent::select("group_id")
            ->where("telegram_id", "=", $chatid)
            ->get();
        $group_id = $group_id[0]->group_id;

        return $group_id;
    }
}

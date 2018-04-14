<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\MessageController;
use App\Model\MLSupport;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Telegram\Bot\Api;

class SupportBotController extends Controller
{
    function setWebHook(Request $request)
    {
        try
        {
            $bot_id = 10;

            $bot_name = 'support';
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

            $check_question = \App\Model\Support\MLSupport::select("answer")
                ->where("question","=",$text)
                ->get();
            if(count($check_question) > 0)
            {
                $send_message = new MessageController();
                $send_message->sendMessage($chatid,$check_question[0]->answer,$bot_name);
            }
            else
            {
                $send_message = new MessageController();
                $send_message->sendMessage($chatid,"Я Вас не понимаю.",$bot_name);
            }
            return response()->json(['success' => true]);
        }
        catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }
}

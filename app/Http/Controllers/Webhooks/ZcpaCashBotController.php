<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaCashBotController extends Controller
{
    function setWebHookZcpaCashBot(Request $request)
    {
        try
        {
            $bot_id = 7;

            $bot_name = 'zcpa_cash_bot';

            $chatid = $request['message']['chat']['id'];

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, 'test', $bot_name);

            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            $log = new LogController();
            $log->setErrorLog('666', $e->getMessage());
            return response()->json(['success' => true]);
        }

    }
}

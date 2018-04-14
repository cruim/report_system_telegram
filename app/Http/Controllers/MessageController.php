<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminControllers\ConfigController;
use Illuminate\Http\Request;

use App\Http\Requests;
use Telegram\Bot\BotsManager;

class MessageController extends Controller
{
    function sendMessage($chatid, $response_text, $bot_name, $reply_markup = null)
    {
        $config = new ConfigController();
        $config = $config->getConfig();
        $bot_manager = new BotsManager((array)$config);
        $response_text = str_replace('_',' ',$response_text);
        for ($start = 0, $length = 2500; $subtext = mb_substr($response_text, $start, $length); $start = $start + 2500)
        {
            $bot_manager->bot($bot_name)->sendMessage([
                'chat_id' => $chatid,
                'text' => $subtext,
                'reply_markup' => $reply_markup,
                'parse_mode' => 'Markdown'
            ]);
        }

        return response()->json(['success' => true]);
    }

    function sendPhoto($chatid, $bot_name, $path, $caption = null)
    {
        $config = new ConfigController();
        $config = $config->getConfig();
        $bot_manager = new BotsManager((array)$config);
        $bot_manager->bot($bot_name)->sendPhoto([
            'chat_id' => $chatid,
            'photo' => $path,
            'caption' => $caption
        ]);
    }
}

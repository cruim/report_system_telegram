<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class DistributionController extends Controller
{
    function distribution($chatid, $text, $bot_id, $bot_name)
    {
        $abonent = new AccessController();
        $abonent = $abonent->checkAbonentAccess($chatid, $text, $bot_id, $bot_name);

        $keyboard = new KeyboardController();
        $keyboard->buildCustomKeyboard($chatid, $abonent, $text, $bot_id, $bot_name);
    }
}

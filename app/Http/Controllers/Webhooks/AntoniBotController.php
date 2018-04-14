<?php

namespace App\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AntoniBotController extends Controller
{
    function setWebHookAntoniZDR(Request $request)
    {
        try
        {
            $chatid = $request['message']['from']['id'];
            $title = $request['message']['chat']['title'];
            $type = $request['message']['chat']['type'];
            $text = $request['message']['text'];


            DB::table('antoni_log')->insert(
                ['telegram_id' => $chatid,
                    'message' => $text,
                    'group_title' => $title,
                    'type' => $type
                ]
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            DB::table('antoni_log')->insert(
                ['telegram_id' => 666,
                    'message' => $e->getMessage()]
            );
            return response()->json(['success' => true]);
        }

    }

    function setWebHookAntoniZD(Request $request)
    {
        try
        {
            $chatid = $request['message']['from']['id'];
            $text = 'test';


            DB::table('antoni_log')->insert(
                ['telegram_id' => $chatid,
                    'message' => $text,
                    'group_title' => 'cruim'
                ]
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            DB::table('antoni_log')->insert(
                ['telegram_id' => 666,
                    'message' => $e->getMessage()]
            );
        }
    }
}

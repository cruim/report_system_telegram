<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\Bot;
use App\Model\Department;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class DispatchController extends Controller
{
    function index(Request $request)
    {

        $department = Department::select("group_name")
            ->get();

        $abonent = Abonent::select(Db::raw("id, abonent"))
            ->join('telegram.groups', 'abonents.group_id', '=', 'groups.group_id')
            ->where("abonents.active", "=", 1)
            ->orderBy('group_name')
            ->get();

        $bot = Bot::select("name")
            ->whereRaw("name in ('common','manager','marketing','zcpa')")
            ->get();

        return view('dispatch', [
            'department' => $department,
            'abonent' => $abonent,
            'bot' => $bot
        ]);
    }

    function activateDispatch(Request $request)
    {
        try
        {
            $data = $request['request'];
            $department = $data['department'];
            $abonent = $data['abonent'];
            $bot = $data['bot'];
            $text = $data['text_for_abonents'];

            $abonent_list = Abonent::select("telegram_id")
                ->join("telegram.groups", "abonents.group_id", "=", "groups.group_id")
                ->whereIn("abonent", $abonent)
                ->orWhereIn("groups.group_name", $department)
                ->where("active", "<>", 0)
                ->get();
        } catch (\Exception $e)
        {
            $send_message = new MessageController();
            $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), 'В модуле рассылке сообщений произошла ошибка: ' .
                $e->getMessage(), 'common');
            return response()->json(['success' => true]);
        }
        $send_message = new MessageController();
        foreach ($abonent_list as $value)
        {
            try
            {
                $send_message->sendMessage($value->telegram_id, $text, $bot);
            } catch (\Exception $e)
            {
                $send_message = new MessageController();
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), 'В модуле рассылке сообщений произошла ошибка: ' .
                    $e->getMessage(), 'common');
            }
        }
    }
}

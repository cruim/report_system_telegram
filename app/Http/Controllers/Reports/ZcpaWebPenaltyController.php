<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\Reports\ZcpaInnerWebmasters;
use App\Model\Reports\ZcpaWebmastersOperation;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaWebPenaltyController extends Controller
{
    function getPenaltyData()
    {
        $all_webs = ZcpaInnerWebmasters::select(DB::raw("user_id,concat(users.first_name,' ' ,sur_name) as web_name,direction"))
            ->join("analytics.users","webmasters.user_id","=","users.id")
            ->where("bot_notification","=",1)
            ->get();

        $good_webs = ZcpaWebmastersOperation::select("author")
            ->whereRaw("date(operation.created_at) = CURDATE()")
            ->groupBy("author")
            ->get();

        $good_webs_array = array();

        foreach ($good_webs as $value)
        {
            $good_webs_array[] = $value->author;
        }

        $text = 'Не заполнили ежедневный отчет(Лидогенерация):' . chr(10);
        $vanya_text  = 'Не заполнили ежедневный отчет(Лидогенерация):' . chr(10);
        foreach ($all_webs as $value)
        {
            if(!in_array($value->user_id,$good_webs_array))
            {
                $text .= $value->web_name . chr(10);
                DB::table('webmasters.webmaster_penalty')->insert(
                    ['web_id' => $value->user_id]
                );
                if($value->direction == 'Приглашения')
                {
                    $vanya_text .= $value->web_name . chr(10);
                }
            }
        }
        $send_message = new MessageController();
        $send_message->sendMessage(env("TELEGRAM_ADMIN_ID"),$text, "common");
        $send_message->sendMessage('221416106',$text, "common"); //бугров
        $send_message->sendMessage('119223267',$text, "zcpa"); //булдаков
        $send_message->sendMessage('442437356',$vanya_text, "zcpa"); //гулый
    }

    function sendMessageToWebmaster()
    {
        $all_webs = ZcpaInnerWebmasters::select(DB::raw("user_id,concat(users.first_name,' ' ,sur_name) as web_name, telegram_id"))
            ->join("analytics.users","webmasters.user_id","=","users.id")
            ->where("bot_notification","=",1)
            ->get();

        $good_webs = ZcpaWebmastersOperation::select("author")
            ->whereRaw("date(operation.created_at) = CURDATE()")
            ->groupBy("author")
            ->get();

        $good_webs_array = array();

        foreach ($good_webs as $value)
        {
            $good_webs_array[] = $value->author;
        }

        $send_message = new MessageController();
        $admin_text = '';
        foreach ($all_webs as $value)
        {
            if(!in_array($value->user_id,$good_webs_array) and $value->telegram_id != 0)
            {
                $admin_text .= $value->web_name . chr(10);
                $text = $value->web_name . ' ,Вы не заполнили ежедневный отчет(Лидогенерация).';
                $chatid = $value->telegram_id;
                $send_message->sendMessage($chatid,$text,'zcpa');
            }
        }
    }
}

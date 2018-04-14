<?php

namespace App\Http\Controllers\CPA;


use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\CPA\AwardedWebs;
use App\Model\CPA\EventStandings;
use App\Model\CPA\WebMaster;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EventStandingsController extends Controller
{
    function buildEventKeyboard($chatid)
    {
        $keyboard = [['Акция.Текущее положение', 'Акция.Призовые места'], ['Назад']];
        $reply_markup = \Telegram::replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ]);

        $send_message = new MessageController();
        $send_message->sendMessage($chatid, 'Выберите кнопку', 'zcpa_dir', $reply_markup);
    }

    function getActualStandings()
    {
        try
        {
            $result = EventStandings::select("web_id", "apruv_count")
                ->where("web_id", "<>", 0)
                ->orderBy("apruv_count", "desc")
                ->get();

            $text = 'Текущее положение:' . chr(10);

            foreach ($result as $item)
            {
                $text .= $item->web_id . ': ' . $item->apruv_count . chr(10);
            }

            return $text;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function getPrizePool()
    {
        try
        {
            $result = AwardedWebs::select("web_id", "apruv_count")->get();

            $text = 'Призовые места:' . chr(10);

            foreach ($result as $item)
            {
                $text .= $item->web_id . ': ' . $item->apruv_count . chr(10);
            }
            return $text;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    function updateEventData()
    {
        try
        {
            $geo = "'Германия', 'Латвия', 'Литва', 'Великобритания', 'Эстония', 'Нидерланды', 'Польша', 'Франция', 'Венгрия', 
        'Словакия', 'Чехия', 'Кипр', 'Испания', 'Италия', 'Португалия', 'Румыния', 'Греция', 'Австрия', 'Бельгия', 'Болгария', 
        'Дания', 'Ирландия', 'Люксембург', 'Словения', 'Финляндия'";

            EventStandings::truncate();

            $active_webmaster = \App\Model\CPA\WebMaster::select("webmaster_id", "web_start_competion")
                ->join("webmasterdb.webmaster_base_source", "webmaster_base.id", "=", "webmaster_base_source.forsage_id")
                ->join("webmasterdb.sources_invites", "webmaster_base.source_of_web", "=", "sources_invites.id")
                ->where("source_of_web", "=", 7)
                ->get();

            $iterator = 1;
            $result = "insert into webmasterdb.event_standings(web_id,apruv_count) ";
            foreach ($active_webmaster as $value)
            {
                if ($iterator > 1)
                {
                    $result .= " UNION ALL ";
                }
                $result .= $this->buildCalculateQuery($value->webmaster_id, $value->web_start_competion, $geo);
                $iterator++;
            }
            $result .= " order by aproove desc";

            DB::select(DB::raw($result));
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function buildCalculateQuery($web_id, $web_start_competion, $geo)
    {
        return $query =
            " SELECT ifnull(webmaster_id,0), ifnull(SUM(apruv),0) AS 'aproove'
        FROM webmasterdb.webmaster_base
        INNER JOIN webmasterdb.webmaster_base_source ON webmaster_base_source.forsage_id = webmaster_base.id
        INNER JOIN analytics.report_designer ON report_designer.sp_utm_content = webmaster_base_source.webmaster_id
        where utm_source = 'zcpa'
        and webmaster_id = '$web_id'
        and createdtime_spec >= '$web_start_competion'
        and country_name in ($geo) ";
    }

    function checkWinners()
    {
        try
        {
            $awarded_webs = AwardedWebs::select("web_id")
                ->get();
            $awarded_web = '';

            if (count($awarded_webs) == 0)
            {
                $awarded_web = 0;
            } else
            {
                foreach ($awarded_webs as $web)
                {
                    $awarded_web .= $web->web_id . ',';
                }
                $awarded_web = substr($awarded_web, 0, -1);
            }

            $first_prize = EventStandings::select("event_standings.web_id", "event_standings.apruv_count")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 1)
                ->whereRaw("event_standings.web_id not in(0)")
                ->whereRaw("apruv_count > 1499");

            $second_prize = EventStandings::select("event_standings.web_id", "event_standings.apruv_count")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 2)
                ->whereRaw("event_standings.web_id not in(0)")
                ->whereRaw("apruv_count > 4999");

            $third_prize = EventStandings::select("event_standings.web_id", "event_standings.apruv_count")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 3)
                ->whereRaw("event_standings.web_id not in(0)")
                ->whereRaw("apruv_count > 9999");

            $result = EventStandings::select("event_standings.web_id", "event_standings.apruv_count")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 0)
                ->whereRaw("event_standings.web_id not in($awarded_web)")
                ->whereRaw("apruv_count > 99")
                ->union($first_prize)
                ->union($second_prize)
                ->union($third_prize)
                ->get();

            if (count($result) == 0)
            {
                return;
            }
            $text = 'Достиг призовой отметки' . chr(10);

            $text .= $result[0]->web_id . ': ' . $result[0]->apruv_count;

            $manual_import = new LogController();
            $manual_import->setManualInputReport(env('TELEGRAM_ADMIN_ID'), 'event' . $result[0]->web_id);
            $manual_import->setManualInputReport(376625562, 'event' . $result[0]->web_id);

            $keyboard = [['Забрать приз', 'Продолжить участие'], ['Назад']];
            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $text, 'zcpa_dir', $reply_markup);
            $send_message->sendMessage(376625562, $text, 'zcpa_dir', $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    function insertIntoPrizePool($web_id)
    {
        try
        {
            $web_data = EventStandings::select("web_id", "apruv_count")
                ->where("web_id", "=", $web_id)
                ->get();

            $webid = $web_data[0]->web_id;
            $apruv_count = $web_data[0]->apruv_count;
            $result =
                "insert into webmasterdb.awarded_webs(web_id,apruv_count)
                select * from(select $webid,$apruv_count) as tmp
                where not exists(select web_id from webmasterdb.awarded_webs where web_id = $webid)";

            DB::select(DB::raw($result));
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    function insertIntoWebStatus()
    {
        try
        {
            $active_webs = EventStandings::select("web_id")->get();
            foreach ($active_webs as $value)
            {
                $result =
                    "insert into webmasterdb.event_web_status(web_id)
                select * from(select $value->web_id) as tmp
                where not exists(select web_id from webmasterdb.event_web_status where web_id = $value->web_id)";

                DB::select(DB::raw($result));
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function updateWebStatus($web_id)
    {
        try
        {
            $result =
                "update webmasterdb.event_web_status
            set status = status + 1
            where web_id = $web_id";

            DB::select(DB::raw($result));
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function apiDataPretender()
    {
        try
        {
            $awarded_webs = AwardedWebs::select("web_id")
                ->get();
            $awarded_web = '';

            if (count($awarded_webs) == 0)
            {
                $awarded_web = 0;
            } else
            {
                foreach ($awarded_webs as $web)
                {
                    $awarded_web .= $web->web_id . ',';
                }
                $awarded_web = substr($awarded_web, 0, -1);
            }
            $zero_prize = EventStandings::select("event_standings.web_id")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 0)
                ->whereRaw("event_standings.web_id not in($awarded_web)")
                ->orderBy("apruv_count",'desc')
                ->limit(4)
                ->get();

            $first_prize = EventStandings::select("event_standings.web_id")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 1)
                ->whereRaw("event_standings.web_id not in($awarded_web)")
                ->orderBy("apruv_count",'desc')
                ->limit(4)
                ->get();

            $second_prize = EventStandings::select("event_standings.web_id")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 2)
                ->whereRaw("event_standings.web_id not in($awarded_web)")
                ->orderBy("apruv_count",'desc')
                ->limit(4)
                ->get();

            $third_prize = EventStandings::select("event_standings.web_id")
                ->join("webmasterdb.event_web_status", "event_standings.web_id", "=", "event_web_status.web_id")
                ->where("event_web_status.status", "=", 3)
                ->whereRaw("event_standings.web_id not in($awarded_web)")
                ->orderBy("apruv_count",'desc')
                ->limit(4)
                ->get();

            $api_array = array();
            $zero_prize_array = array();
            $first_prize_array = array();
            $secont_prize_array = array();
            $third_prize_array = array();

            foreach ($zero_prize as $value)
            {
                $zero_prize_array[] = [$value->web_id];
            }
            foreach ($first_prize as $value)
            {
                $first_prize_array[] = [$value->web_id];
            }
            foreach ($second_prize as $value)
            {
                $secont_prize_array[] = [$value->web_id];
            }
            foreach ($third_prize as $value)
            {
                $third_prize_array[] = [$value->web_id];
            }
            $api_array[] = $zero_prize_array;
            $api_array[] = $first_prize_array;
            $api_array[] = $secont_prize_array;
            $api_array[] = $third_prize_array;

            return $api_array;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function apiDataEventWinner()
    {
        try
        {
            $result = AwardedWebs::select("web_id", "apruv_count")
                ->orderBy("apruv_count")
                ->get();

            $api_array = array();

            foreach ($result as $value)
            {
                $api_array[] = [$value->web_id => $value->apruv_count];
            }

            return $api_array;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }
}

<?php

namespace App\Http\Controllers\AdminControllers;

use App\Model\Bot;
use App\Model\BotToAbonent;
use App\Model\BotToReport;
use Illuminate\Http\Request;
use App\Model\Abonent;
use App\Model\Department;
use App\Model\Report;
use App\Model\ReportToAbonent;
use App\Http\Controllers\Controller;
use Auth;
use DB;

class BotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $result = Bot::select("id","name","telegram_token","active")
            ->get();

        return view('bot',[
            'result' => $result,
            'is_user_active' => $is_user_active
        ]);
    }

    public function updateBotData(Request $request)
    {
        $data = $request['request'];
        $column = $data['column'];

        Bot::where("id","=",$data['id'])
            ->update(["$column" => $data['value']]);
    }

    public function createBot(Request $request)
    {
        $data = $request['request'];
        $name = $data['name'];
        $telegram_token = $data['telegram_token'];

        Db::insert("insert into telegram.bots(`name`, telegram_token) 
        values('$name','$telegram_token')");

        $bot_id = Bot::select("id")
            ->where("name","=","$name")
            ->get();
        foreach ($bot_id as $value)
        {
            $id = $value;
        }

        Db::insert("insert into telegram.bot_to_report(bot_id,report_id)
        select bots.id, reports.id
        from bots cross join reports
        where bots.id = $id->id");

        Db::insert("insert into telegram.bot_to_abonent(bot_id,abonent_id)
        select bots.id, abonents.id
        from bots cross join abonents
        where bots.id = $id->id");
    }

    public function getDetailAboutBot($id)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $bot_reports = BotToReport::select("bot_id", "bots.name", "sms_name", "bot_to_report.active")
            ->join("telegram.reports", "bot_to_report.report_id", "=", "reports.id")
            ->join("telegram.bots", "bot_to_report.bot_id", "=", "bots.id")
            ->where("bot_to_report.bot_id", "=", "$id")
            ->get();

        $bot_name = Bot::select("name")
            ->where("id", "=", "$id")
            ->get();

        return view('bot_detail_info',
            [
                'bot' => $bot_reports,
                'bot_name' => $bot_name,
                'is_user_active' => $is_user_active
            ]);
    }

    public function updateBotToReport(Request $request)
    {
        $data = $request['request'];
        $bot_id = $data['bot_id'];
        $sms_name = $data['sms_name'];
        $report_id = Report::select("id")
            ->where("sms_name","=",$sms_name)
            ->get();

        BotToReport::where("bot_id","=",$bot_id)
            ->where("report_id","=",$report_id[0]->id)
            ->update(['active' => $data['value']]);
    }

    public function getDetailAboutBotToAbonent($id)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $bot_abonents = BotToAbonent::select("bot_id", "bots.name", "abonent", "bot_to_abonent.active")
            ->join("telegram.abonents", "bot_to_abonent.abonent_id", "=", "abonents.id")
            ->join("telegram.bots", "bot_to_abonent.bot_id", "=", "bots.id")
            ->where("bot_to_abonent.bot_id", "=", "$id")
            ->get();

        $bot_name = Bot::select("name")
            ->where("id", "=", "$id")
            ->get();

        return view('bot2abonent_detail_info',
            [
                'bot' => $bot_abonents,
                'bot_name' => $bot_name,
                'is_user_active' => $is_user_active
            ]);
    }

    public function updateBotToAbonent(Request $request)
    {
        $data = $request['request'];
        $bot_id = $data['bot_id'];
        $abonent = $data['abonent'];
        $abonent_id = Abonent::select("id")
            ->where("abonent","=",$abonent)
            ->get();

        BotToAbonent::where("bot_id","=",$bot_id)
            ->where("abonent_id","=",$abonent_id[0]->id)
            ->update(['active' => $data['value']]);
    }
}

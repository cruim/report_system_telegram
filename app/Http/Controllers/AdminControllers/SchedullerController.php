<?php

namespace App\Http\Controllers\AdminControllers;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\Bot;
use App\Model\Report;
use App\Model\ReportParameters;
use App\Model\Scheduller;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use DB;

class SchedullerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        $result = Report::select(Db::raw("reports.id, telegram_name"))
            ->join("telegram.report_parameters","reports.id","=","report_parameters.report_id")
            ->where("dispatch_method","=",1)
            ->orderBy('reports.id', 'desc')
            ->get();

        return view('scheduller', [
            'scheduller' => $result
        ]);
    }

    public function getSchedullerTime($id)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $bots_id = Bot::select("id")
            ->where("active","=",1)
            ->get();

        $scheduller = Scheduller::select("scheduller.id", "abonents.abonent", "sending_time", "scheduller.active", "scheduller.bot_id")
            ->join("telegram.abonents", "scheduller.abonent_id", "=", "abonents.id")
            ->join("telegram.reports", "scheduller.report_id", "=", "reports.id")
            ->where("report_id", "=", "$id")
            ->get();

        $report_name = Report::select("telegram_name")
            ->where("id", "=", "$id")
            ->get();

        $abonent = Abonent::select("abonent")
            ->orderBy("abonent")
            ->get();

        return view('scheduller_detail_info',
            [
                'scheduller' => $scheduller,
                'report_name' => $report_name,
                'abonent' => $abonent,
                'is_user_active' => $is_user_active,
                'bots_id' => $bots_id
            ]);
    }

    public function updateSchedullerData(Request $request)
    {
        $data = $request['request'];
        $id = $data['id'];
        $column = $data['column'];

        Scheduller::where("id", "=", $id)
            ->update(["$column" => $data['value']]);
    }

    public function createSchedullerTask(Request $request)
    {
        $data = $request['request'];
        $abonent = $data['abonent'];
        $report = $data['report_name'];

        $abonent_id = Abonent::select("id")
            ->where("abonent", "=", $abonent)
            ->get();

        $report_id = Report::select("id")
            ->where("sms_name", "=", "$report")
            ->get();
        $abonent_id = $abonent_id[0]->id;
        $report_id = $report_id[0]->id;

        DB::insert("insert into telegram.scheduller(abonent_id, report_id) 
        values($abonent_id, $report_id)");
    }

    public function checkSchedullerTask()
    {
        $cur_time = date('H:i');
        $result = Scheduller::select("report_id", "abonent_id", "bot_id")
            ->whereRaw("sending_time = TIME('$cur_time')")
            ->whereRaw("active = 1")
            ->get();

        if (count($result) > 0)
        {
            foreach ($result as $value)
            {
                try
                {
                    $report_id = $value->report_id;
                    $abonent_id = $value->abonent_id;
                    $bot_id = $value->bot_id;

                    $bot_name = Bot::select("name")
                        ->where("id", "=", $bot_id)
                        ->get();

                    $bot_name = $bot_name[0]->name;

                    $dispatch = ReportParameters::select("report_parameters.controller", "method", "telegram_name")
                        ->join("telegram.reports", "report_parameters.report_id", "=", "reports.id")
                        ->where("report_id", "=", "$report_id")
                        ->where("dispatch_method", "=", "1")
                        ->get();

                    $controller = $dispatch[0]->controller;
                    $method = $dispatch[0]->method;

                    $abonent = Abonent::select("telegram_id")
                        ->where("id", "=", "$abonent_id")
                        ->get();

                    $chatid = $abonent[0]->telegram_id;

                    $text = "Отчет - " . $dispatch[0]->telegram_name . chr(10) . chr(10);

                    $report_data = new $controller();
                    $report_data = $report_data->$method($chatid);
                    $text .= $report_data;

                    $send_message = new MessageController();
                    $send_message->sendMessage($chatid, $text, $bot_name);
                } catch (\Exception $e)
                {
                    \Telegram::sendMessage([
                        'chat_id' => '348169607',
                        'text' => 'cron_error у пользователя ' . $chatid,
                    ]);

                }
            }
            return response()->json(['success' => true]);
        }
    }
}

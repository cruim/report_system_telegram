<?php

namespace App\Http\Controllers;

use App\Model\Abonent;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;

class LogController extends Controller
{
    function setErrorLog($chatid, $text)
    {
        try
        {
            DB::table('error_log')->insert(
                ['telegram_id' => $chatid,
                    'message' => $text]
            );
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    function getManualInportReport($chatid)
    {
        $result = Abonent::select("manual_input_report")
            ->where("telegram_id", "=", $chatid)
            ->get();

        if (count($result) != 0)
        {
            $manual_input_report = $result[0]->manual_input_report;
        } else
        {
            $manual_input_report = 1;
        }
        return $manual_input_report;
    }

    function setManualInputReport($chatid, $text)
    {
        DB::table('abonents')
            ->where('telegram_id', $chatid)
            ->update(['manual_input_report' => $text]);
    }

    function writeTextMessageFromAbonent($chatid, $text)
    {
        DB::table('incoming_message')->insert(
            ['telegram_id' => $chatid,
                'message' => $text]
        );
    }

    function setTelegramLog($chatid, $text, $report_data)
    {
        DB::table('telegram_log')->insert(
            ['telegram_id' => $chatid,
                'report' => $text,
                'message' => $report_data]
        );
    }

    function setZcpaLog($chatid, $text, $report_data)
    {
        DB::table('zcpa_web_log')->insert(
            ['telegram_id' => $chatid,
                'report' => $text,
                'message' => $report_data]
        );
    }
}

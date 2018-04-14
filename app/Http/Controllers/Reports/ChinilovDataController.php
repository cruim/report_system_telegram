<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ChinilovDataController extends Controller
{
    function yesterdayData()
    {
        try
        {
            $text = date("d.m", strtotime("yesterday")) . chr(10);

            $query = ReportDesigner::select(DB::raw
            ("concat(sp_utm_content,' - ',count(*),' / ',sum(apruv),' (',round(sum(apruv)/count(*)*100,1),'%)') as web"))
                ->where("utm_source", "=", 'zcpa')
                ->whereRaw("createdtime = CURDATE() - interval 1 day")
                ->whereRaw("sp_utm_content in (114,612,136,551,1528,500,1914)")
                ->groupBy("sp_utm_content");

            $total_result = ReportDesigner::select(DB::raw
            ("concat('Итого: ',count(*),' / ',sum(apruv),' (',round(sum(apruv)/count(*)*100,1),'%)') as web"))
                ->where("utm_source", "=", 'zcpa')
                ->whereRaw("createdtime = CURDATE() - interval 1 day")
                ->whereRaw("sp_utm_content in (114,612,136,551,1528,500,1914)")
                ->union($query)
                ->get();

            foreach ($total_result as $today)
            {
                $text .= $today->web . chr(10);
            }

            $send_message = new MessageController();
            $send_message->sendMessage(348169607, $text, 'zcpa');
            $send_message->sendMessage(264647841, $text, 'zcpa');
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    function todayData()
    {
        try
        {
            $text = date("d.m") . chr(10);

            $query = ReportDesigner::select(DB::raw
            ("concat(sp_utm_content,' - ',count(*),' / ',sum(apruv),' (',round(sum(apruv)/count(*)*100,1),'%)') as web"))
                ->where("utm_source", "=", 'zcpa')
                ->whereRaw("createdtime = CURDATE()")
                ->whereRaw("sp_utm_content in (114,612,136,551,1528,500,1914)")
                ->groupBy("sp_utm_content");

            $total_result = ReportDesigner::select(DB::raw
            ("concat('Итого: ',count(*),' / ',sum(apruv),' (',round(sum(apruv)/count(*)*100,1),'%)') as web"))
                ->where("utm_source", "=", 'zcpa')
                ->whereRaw("createdtime = CURDATE()")
                ->whereRaw("sp_utm_content in (114,612,136,551,1528,500,1914)")
                ->union($query)
                ->get();

            foreach ($total_result as $today)
            {
                $text .= $today->web . chr(10);
            }

            $send_message = new MessageController();
            $send_message->sendMessage(348169607, $text, 'zcpa');
            $send_message->sendMessage(264647841, $text, 'zcpa');
            return response()->json(['success' => true]);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }
}
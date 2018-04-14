<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class OffersController extends Controller
{
    public function getTotalDataToday($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        return $text;
    }

    public function getYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        return $text;
    }

    public function euroRuTodayData($chatid)
    {

        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }
        return $text;
    }

    public function euroRuYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'EUR'")
            ->whereRaw("language not in('FR','DE','EN','LV')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }
        return $text;
    }

    public function euroTodayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("((currency_zone = 'EUR' AND `language` IN ('FR' , 'DE', 'EN','LV')) OR country = 'IN')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("((currency_zone = 'EUR' AND `language` IN ('FR' , 'DE', 'EN','LV')) OR country = 'IN')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }
        return $text;
    }

    public function euroYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("((currency_zone = 'EUR' AND `language` IN ('FR' , 'DE', 'EN','LV')) OR country = 'IN')")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("((currency_zone = 'EUR' AND `language` IN ('FR' , 'DE', 'EN','LV')) OR country = 'IN')")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }
        return $text;
    }

    public function sngTodayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'RUB'")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }


        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("currency_zone = 'RUB'")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }
        return $text;
    }

    public function sngYesterdayData($chatid)
    {
        $total_result  = ReportDesigner::select(DB::raw("'ИТОГО' as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'RUB'")
            ->get();

        $text = '';

        foreach ($total_result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        $result = ReportDesigner::select(DB::raw("offer_name as `param`,
                    count(*) as `leads`,
                    ROUND((sum(apruv)/count(*))*100) as `aproove`"))
            ->whereRaw("createdtime = DATE(NOW()) - interval 1 day")
            ->whereRaw("currency_zone = 'RUB'")
            ->groupBy("param")
            ->orderBy("leads","desc")
            ->get();

        foreach ($result as $value)
        {
            $text .= $value->param . ':  ' . $value->leads . '/' . $value->aproove . '%' . chr(10);
        }

        return $text;
    }
}

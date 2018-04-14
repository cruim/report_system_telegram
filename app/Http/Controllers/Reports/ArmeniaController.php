<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ArmeniaController extends Controller
{
    public  function getTodayData($telegram_id)
    {
        $result = ReportDesigner::select(DB::raw("sp_utm_content,
                        COUNT(*) AS `leads`,
                        SUM(apruv) AS `aproove`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country","=","AM")
            ->whereRaw("createdtime = CURDATE()")
            ->groupBy(DB::raw('sp_utm_content WITH ROLLUP'))
            ->get();

        $text = date('Y-m-d').chr(10);

        foreach ($result as $value)
        {
            if(is_null($value->sp_utm_content))
            {
                $text .= 'ИТОГО:  '.$value->leads.'/'.$value->aproove.'/'.$value->perc.chr(10).chr(10);
            }
            else{
                $text .= $value->sp_utm_content.':  '.$value->leads.'/'.$value->aproove.'/'.$value->perc.chr(10);
            }

        }


        return $text;
    }

    public  function getYesterdayData()
    {
        $result = ReportDesigner::select(DB::raw("sp_utm_content,
                        COUNT(*) AS `leads`,
                        SUM(apruv) AS `aproove`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country","=","AM")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupBy(DB::raw('sp_utm_content WITH ROLLUP'))
            ->get();

        $text = date('Y-m-d').chr(10);

        foreach ($result as $value)
        {
            if(is_null($value->sp_utm_content))
            {
                $text .= 'ИТОГО:  '.$value->leads.'/'.$value->aproove.'/'.$value->perc.chr(10).chr(10);
            }
            else{
                $text .= $value->sp_utm_content.':  '.$value->leads.'/'.$value->aproove.'/'.$value->perc.chr(10);
            }

        }

        return $text;
    }
}

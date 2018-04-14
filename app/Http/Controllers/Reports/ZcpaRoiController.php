<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaRoiController extends Controller
{
    function getYesterdayRoiData()
    {
        $result = ReportDesigner::select(DB::raw("sp_utm_content,count(*) as leads_count,
        ifnull(round(sum(total_sum)/sum(sp_lead_cost_pp),2),0) as roi,
        round(sum(apruv)/count(*)*100) as apruv_percent,ifnull(round(sum(total_sum)/SUM(apruv)),0) as avg_check"))
            ->where("utm_source","=","zcpa")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupBy("sp_utm_content")
            ->having("roi","<",4)
            ->orderBy("leads_count","desc")
            ->get();

        $text = '';

        if(count($result) != 0)
        {
            foreach ($result as $value)
            {
                $text .= $value->sp_utm_content . '/' . $value->leads_count . '/' . $value->roi . '/' .
                    $value->apruv_percent . '/' . $value->avg_check . chr(10);
            }
        }
        else{$text .= 'Нет данных';}

        return $text;

    }
}

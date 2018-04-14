<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReconciliationController extends Controller
{
    public function BuildReportReconciliation()
    {
        $result = ReportDesigner::select(DB::raw("utm_source, sum(apruv) as apruv"))
            ->where("sp_lead_cost_db","<>","sp_lead_cost_pp")
            ->whereRaw("createdtime = date(now())")
            ->groupBy("utm_source")
            ->orderBy("apruv","desc")
            ->get();

        $to_string = '[Wiki](http://wiki.finereports.info/index.php?title=Сверка)' . chr(10);
        foreach ($result as $item)
        {
            $to_string .= $item->utm_source." - ". $item->apruv." з., ".chr(10);
        }

        if($to_string == '')
        {
            $to_string = 'Сегодня несовпадений нет.';
        }

        return ($to_string);
    }
}

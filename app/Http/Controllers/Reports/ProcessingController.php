<?php

namespace App\Http\Controllers\Reports;

use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProcessingController extends Controller
{
    public function buildReportProcessing($chatid)
    {
        $status_new = ReportDesigner::select(DB::raw("count(*) as count"))
            ->where("order_status","=","Новый")
            ->get();

        $in_queue = ReportDesigner::select(DB::raw("count(*) as count"))
            ->where("order_status","=","Новый")
            ->where("manager","=","Заказ нераспределенный")
            ->get();

        $in_handling = ReportDesigner::select(DB::raw("count(*) as count"))
            ->where("order_status","=","В обработке")
            ->get();

        $text = '[Wiki](http://wiki.finereports.info/index.php?title=Обработка)' . chr(10);
        $text .= $in_queue[0]->count." в очереди на обработку, ".$status_new[0]->count."  в статусе Новый, ".
            $in_handling[0]->count." в обработке.";

        return $text;
    }
}

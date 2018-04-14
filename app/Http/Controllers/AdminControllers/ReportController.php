<?php

namespace App\Http\Controllers\AdminControllers;

use App\Model\Abonent;
use App\Model\Report;
use App\Model\ReportToAbonent;
use App\Model\Scheduller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $result = Report::select(Db::raw("id, telegram_name, report_active"))
            ->orderBy('id', 'desc')
            ->get();

        return view('report', [
            'user_info' => $result,
            'is_user_active' => $is_user_active
        ]);
    }

    public function store(Request $request)
    {
        $result = Report::select('id','sms_name','report_active')
            ->orderBy('id','desc')
            ->get();

        return json_encode($result);
    }

    public function updateReportData(Request $request)
    {
        $data = $request['request'];
        $column = $data['column'];

        Report::where("id","=",$data['id'])
            ->update(["$column" => $data['value']]);
    }

    public function createReportData(Request $request)
    {
        $data = $request['request'];
        $telegram_name = $data['telegram_name'];

        Db::insert("insert into telegram.reports(sms_name,telegram_name) 
        values('$telegram_name','$telegram_name')");

        $report_id = Report::select("id")
            ->where("sms_name","=","$telegram_name")
            ->get();

        foreach ($report_id as $item)
        {
            $id = $item;
        }

        Db::insert("insert into telegram.report_to_abonent(report_id, abonent_id)
        SELECT reports.id, abonents.id 
        FROM `reports`
        cross join abonents 
        where reports.id = $id->id");

        Db::insert("insert into telegram.bot_to_report(bot_id,report_id)
        select bots.id, reports.id
        from bots cross join reports
        where reports.id = $id->id");
    }

    public function getDetailAboutReport($id)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $user_reports = ReportToAbonent::select("report_id", "abonent", "telegram_name", "report_to_abonent.active")
            ->join("telegram.reports", "report_to_abonent.report_id", "=", "reports.id")
            ->join("telegram.abonents", "report_to_abonent.abonent_id", "=", "abonents.id")
            ->where("report_to_abonent.report_id", "=", "$id")
            ->get();

        $report_name = Report::select("telegram_name")
            ->where("id", "=", "$id")
            ->get();
        return view('report_detail_info',
            [
                'reports' => $user_reports,
                'report_name' => $report_name,
                'is_user_active' => $is_user_active
            ]);
    }

    public function updateAbonentToReport(Request $request)
    {
        $data = $request['request'];
        $report_id = $data['report_id'];
        $abonent = $data['abonent'];
        $abonent_id = Abonent::select("id")
            ->where("abonent","=","$abonent")
            ->get();
        foreach ($abonent_id as $value)
        {
            $id = $value;
        }

        ReportToAbonent::where("abonent_id","=",$id->id)
            ->where("report_id","=",$report_id)
            ->update(['active' => $data['value']]);

        $check_variable = Scheduller::select("*")
            ->where("abonent_id","=",$id->id)
            ->where("report_id","=",$report_id)
            ->get();

        if(count($check_variable) == 0)
        {
            Db::insert("insert into telegram.scheduller(abonent_id,report_id)
        select abonent_id, report_id
        from report_to_abonent
        where abonent_id = $id->id
        and report_id = $report_id");
        }
    }
}



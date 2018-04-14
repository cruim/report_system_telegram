<?php

namespace App\Http\Controllers\AdminControllers;

use App\Model\Abonent;
use App\Model\Department;
use App\Model\Report;
use App\Model\ReportToAbonent;
use App\Model\Scheduller;
use App\Model\VTiger\VTUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;

use App\Http\Requests;

class AbonentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $department = Department::select("group_name")
            ->get();

        $result = Abonent::select(Db::raw("id, abonent, first_name, telegram_id, group_name, active"))
            ->join('telegram.groups', 'abonents.group_id', '=', 'groups.group_id')
            ->orderBy('id', 'desc')
            ->get();

        return view('abonent', [
            'department' => $department,
            'user_info' => $result,
            'is_user_active' => $is_user_active
        ]);
    }

    public function store(Request $request)
    {
        $result = Abonent::select(Db::raw("id, abonent, telegram_id, group_name, active"))
            ->join('telegram.groups', 'abonents.group_id', '=', 'groups.group_id')
            ->orderBy('id', 'desc')
            ->get();

        $report = Report::select("sms_name")
            ->get();

        return json_encode($result);
    }

    public function updateAbonentData(Request $request)
    {
        $data = $request['request'];
        $column = $data['column'];

        Abonent::where("id","=",$data['id'])
            ->update(["$column" => $data['value']]);
    }

    public function createAbonentData(Request $request)
    {
        $data = $request['request'];
        $abonent = $data['abonent'];
        $telegram_id = $data['telegram_id'];
        $tiger_id = $data['tiger_id'];
        $group_name = $data['group_name'];
        $first_name = $data['first_name'];

        $group_id = Department::select("group_id")
            ->where("group_name","=","$group_name")
            ->get();

        foreach ($group_id as $loc_group_id)
        {
            $group = $loc_group_id;
        }

        Db::insert("insert into telegram.abonents(telegram_id, abonent, group_id, tiger_2_id,first_name) 
        values('$telegram_id', '$abonent', '$group->group_id', '$tiger_id','$first_name')");

        $abonent_id = Abonent::select("id")
            ->where("abonent","=","$abonent")
            ->get();
        foreach ($abonent_id as $item)
        {
            $id = $item;
        }

        Db::insert("insert into telegram.report_to_abonent(report_id, abonent_id)
        SELECT reports.id, abonents.id 
        FROM `reports`
        cross join abonents 
        where abonents.id = $id->id");

        Db::insert("insert into telegram.bot_to_abonent(bot_id,abonent_id)
        select bots.id, abonents.id
        from bots cross join abonents
        where abonents.id = $id->id");
    }

    public function getDetailAboutAbonent($id)
    {
        $is_user_active = Abonent::select(DB::raw("DISTINCT active"))
            ->get();

        $user_reports = ReportToAbonent::select("abonent_id", "report_id", "telegram_name","active")
            ->join("telegram.reports", "report_to_abonent.report_id", "=", "reports.id")
            ->where("abonent_id", "=", "$id")
            ->get();

        $user_name = Abonent::select("abonent")
            ->where("id", "=", "$id")
            ->get();

        $user_department = Department::select("group_name")
            ->join("telegram.abonents", "groups.group_id", "=", "abonents.group_id")
            ->where("abonents.id", "=", "$id")
            ->get();

        $user_telegram_id = Abonent::select("telegram_id")
            ->where("id", "=", "$id")
            ->get();

        return view('abonent_detail_info',
            [
                'reports' => $user_reports,
                'department' => $user_department,
                'user_name' => $user_name,
                'telegram_id' => $user_telegram_id,
                'is_user_active' => $is_user_active
            ]);
    }

    public function updateReportToAbonent(Request $request)
    {
        $data = $request['request'];
        $report_id = $data['report_id'];
        $abonent_id = $data['abonent_id'];

        ReportToAbonent::where("abonent_id","=",$data['abonent_id'])
            ->where("report_id","=",$report_id)
            ->update(['active' => $data['value']]);

        $check_variable = Scheduller::select("*")
            ->where("abonent_id","=",$abonent_id)
            ->where("report_id","=",$report_id)
            ->get();

        if(count($check_variable) == 0)
        {
            Db::insert("insert into telegram.scheduller(abonent_id,report_id)
        select abonent_id, report_id
        from report_to_abonent
        where abonent_id = $abonent_id
        and report_id = $report_id");
        }
    }

    function getListVtigerUsers(Request $request)
    {
        $result = VTUser::select(DB::raw("id, concat(first_name,' ',last_name) as full_name"))
            ->orderBy("id","desc")
            ->get();

        return view('vt_users', [
            'users' => $result
        ]);
    }
}

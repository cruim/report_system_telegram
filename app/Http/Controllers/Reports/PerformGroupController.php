<?php

namespace App\Http\Controllers\Reports;

use App\Model\Abonent;
use App\Model\VTiger\ReportDesigner;
use App\Model\VTiger\VTUser;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class PerformGroupController extends Controller
{
    public function buildGroupsPerfomance($telegram_id)
    {
        $tiger_id = Abonent::select("tiger_2_id")
            ->where("telegram_id","=",$telegram_id)
            ->get();

        if($tiger_id[0]->tiger_2_id != 0 and $tiger_id[0]->tiger_2_id != null)
        {
            $tiger_user_info = VTUser::select("first_name","last_name","title","department","user_department_group")
                ->where("id","=",$tiger_id[0]->tiger_2_id)
                ->get();

            $department = $tiger_user_info[0]->department;
            $group = $tiger_user_info[0]->user_department_group;
            $title = $tiger_user_info[0]->title;

            if($title == 'Старший группы')
            {
                $result = ReportDesigner::select(DB::raw("manager,
                            department,
                            `group`,
                            count(*) as `leads`,
                            sum(apruv) as `approve`,
                            ROUND((SUM(apruv)/(count(*)-SUM(brak)))*100) as `konversion`,
                            round((sum(motivate)/sum(apruv)),2) as `avg`"))
                    ->whereRaw("createdtime = date(now())")
                    ->where("department","=","$department")
                    ->where("group","=",$group)
                    ->get();

                $text = '[Wiki](http://wiki.finereports.info/index.php?title=Показатели_групп)' . chr(10);
                $text .= 'Показатели '. chr(10) ;

                if($result[0]->manager != null)
                {
                    foreach ($result as $value)
                    {
                        $text .= $value->manager . ':   ' . $value->leads . '/' . $value->aproove . '/' . $value->konversion . '/' .
                            $value->avg . chr(10);
                    }
                }
                else{$text = 'Нет данных';}
            }

            else
            {
                $result = ReportDesigner::select(DB::raw(" manager,
                            department,
                            `group`,
                            count(*) as `leads`,
                            sum(apruv) as `approve`,
                            ROUND((SUM(apruv)/(count(*)-SUM(brak)))*100) as `konversion`,
                            round((sum(motivate)/sum(apruv)),2) as `avg`"))
                    ->whereRaw("createdtime = date(now())")
                    ->groupBy("manager")
                    ->orderBy("department","group")
                    ->limit(80)
                    ->get();

                $text = '[Wiki](http://wiki.finereports.info/index.php?title=Показатели_групп)' . chr(10);
                $text .= 'Показатели Менеджеров'. chr(10) . chr(10) ;
                foreach ($result as $value)
                {
                    $text .= "   - " . $value->manager . ':   ' . $value->leads . '/' . $value->approve .
                        '/' . $value->konversion . '/' . $value->avg . chr(10) ;
                }

            }
        }

        else{$text = 'Ваш tiger_id не найден. Обратитесь к администратору';}

        return $text;
    }
}

<?php

namespace App\Http\Controllers\Reports;

use App\Model\VtigerGo\MainData;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TigerLeadsController extends Controller
{
    function todayData()
    {
        $result = MainData::select(DB::raw("main_data.subject,sp_offer,ifnull(CONCAT(COUNT(*),' (',
        ROUND(COUNT(*) / `vtiger_balance`.`limit_city` * 100), '%)'),0) AS `count`,
                        (`vtiger_balance`.`limit_city` - COUNT(*)) AS `leads_diff`"))
            ->leftJoin("vtiger-go.vtiger_subject", "vtiger_analyse.main_data.subject", "=", "vtiger-go.vtiger_subject.subject")
            ->leftJoin("vtiger-go.vtiger_balance", "vtiger-go.vtiger_subject.subjectid", "=", "vtiger-go.vtiger_balance.city_id")
            ->whereRaw("createdtime = DATE(NOW())")
            ->whereRaw("main_data.subject IN ('Москва' , 'Москва 2',
                            'Нижний Новгород',
                            'Санкт-Петербург',
                            'Санкт-Петербург 4',
                            'Ростов-на-Дону',
                            'Воронеж',
                            'Краснодар 2',
                            'Сочи 2',
                            'Якутск',
                            'Москва Спина',
                            'Москва Спина 2',
                            'Москва Спина 3',
                            'Нижний Новгород Спина',
                            'Нижний Новгород Спина 2',
                            'Санкт-Петербург Спина',
                            'Санкт-Петербург Спина 2',
                            'Сочи Спина',
                            'Ростов-на-Дону Спина',
                            'Санкт-Петербург Спина 3',
                            'Самара 2',
'Москва ЕМС','Москва Кровь','Москва Кровь 2', 'Москва Геморрой')")
            ->groupBy("main_data.subject", "sp_offer")
            ->havingRaw("sp_offer is not null")
            ->orderByRaw("sp_offer , FIELD(main_data.subject,
                            'Москва' , 'Москва 2',
                            'Нижний Новгород',
                            'Санкт-Петербург',
                            'Санкт-Петербург 4',
                            'Ростов-на-Дону',
                            'Воронеж',
                            'Краснодар 2',
                            'Сочи 2',
                            'Якутск',
                            'Москва Спина',
                            'Москва Спина 2',
                            'Москва Спина 3',
                            'Нижний Новгород Спина',
                            'Нижний Новгород Спина 2',
                            'Санкт-Петербург Спина',
                            'Санкт-Петербург Спина 2',
                            'Сочи Спина',
                            'Самара 2',
                            'Ростов-на-Дону Спина',
                            'Санкт-Петербург Спина 3',
'Москва ЕМС','Москва Кровь','Москва Кровь 2','Москва Геморрой')")
            ->get();
        $text = $result[0]->sp_offer . chr(10);
        $sp_offer = $result[0]->sp_offer;

        foreach ($result as $value)
        {
            if ($value->sp_offer != $sp_offer)
            {
                $text .= chr(10) . $value->sp_offer . chr(10);
            }

            $text .= $value->subject . ': ' . $value->count . chr(10);
            $sp_offer = $value->sp_offer;
        }

        return $text;
    }
}

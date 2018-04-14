<?php

namespace App\Http\Controllers\Reports;

use App\Model\Report;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\VTiger\ReportDesigner;

class GeoWebController extends Controller
{
    public function Index()
    {
        $offers = ['not like', 'like'];
        $text = '[Wiki](http://wiki.finereports.info/index.php?title=Апрув_ниже_40)' . chr(10);
        foreach ($offers as $value)
        {
            if ($value == 'not like')
            {
                $label = "АПРУВ НИЖЕ 45%: " . chr(10);
                $percent = 45;
            } else
            {
                $label = "АПРУВ за 1 НИЖЕ 30%: " . chr(10);
                $percent = 30;
            }
            $countryResult = ReportDesigner::select(DB::raw("country_name,
                                                            sp_utm_content,
                                                            count(*) as `leads`,
            ROUND((SUM(`apruv`)/SUM(IF((`order_status` NOT IN ('Брак' , 'Дубль','Не целевой','Фейк','Фейк вернулся','Недозвон','Консультация')),1,0)))*100) as `apruv`,
            SUM(if((order_status = 'Новый' AND manager = 'заказ Нераспределенный'),1,0)) as `untaken`"))
                ->where('createdtime', '=', date('Y-m-d'))
                ->whereRaw("category $value '%за 1%'")
                ->groupBy('country_name')
                ->groupBy('sp_utm_content')
                ->having('apruv', '<', $percent)
                ->having('leads', '>', 5)
                ->get();

            $zoneResult = ReportDesigner::select(DB::raw("zone_subordinate,country_name,
                                                            sp_utm_content,
                                                            count(*) as `leads`,
            ROUND((SUM(`apruv`)/SUM(IF((`order_status` NOT IN ('Брак' , 'Дубль','Не целевой','Фейк','Фейк вернулся','Недозвон','Консультация')),1,0)))*100) as `apruv`,
            SUM(if((order_status = 'Новый' AND manager = 'заказ Нераспределенный'),1,0)) as `untaken`"))
                ->where('createdtime', '=', date('Y-m-d'))
                ->whereRaw("category $value '%за 1%'")
                ->groupBy('country_name')
                ->having('apruv', '<', $percent)
                ->having('leads', '>', 5)
                ->orderBy('zone_subordinate')
                ->get();
//            if (empty($zoneResult))
//            {
                $text .= chr(10) . $label;
//            }
            $zone_sub = false;
            foreach ($zoneResult as $country)
            {
                if ($country->zone_subordinate !== $zone_sub)
                {
                    $text .= $country->zone_subordinate . chr(10);
                }
                $text .= $country->country_name . ' - ' . $country->apruv . '%' . chr(10);

                foreach ($countryResult as $value)
                {
                    if ($country->country_name == $value->country_name)
                    {
                        $sp_utm_content = $value->sp_utm_content;
                        $sp_utm_content = str_replace('_',' ',$sp_utm_content);
                        $text .= '   - ' . $sp_utm_content . ': ' . $value->leads . ' (' . $value->apruv . '%)';
                        $text .= ($value->untaken > 0) ? ' не распределено ' . $value->untaken . ' шт.' . chr(10) : chr(10);
                    }
                }
                $zone_sub = $country->zone_subordinate;
            }

        }
        return $text;
    }

}

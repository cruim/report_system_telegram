<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\VTiger\ReportDesigner;
use DateTime;
class DeviationController extends Controller
{
    public function Index($dateRange)
    {
     $dateArray = $this->getRangeDates($dateRange[0], $dateRange[1]);
     $period = count($dateArray);
     $text = '';
     if ($period > 1) {
        $text = $this->DayInfo();
     } else {
        $text = $this->WeekInfo(); 
     }
     return $text;
    }
    
    public function DayInfo()
    {
        $SourceYesterday = ReportDesigner::select(DB::raw('utm_source, count(*) as `count`'))
                                ->whereRaw("createdtime >= (CURDATE()- INTERVAL 1 day) and createdtime <= concat(CURDATE() - INTERVAL 1 day, ' ', CURTIME())")
                                ->groupby('utm_source')
                                ->having('count','>',20)
                                ->get();
        $SourceToday = ReportDesigner::select(DB::raw('utm_source, count(*) as `count`'))
                                ->whereRaw('createdtime = CURDATE()')
                                ->groupby('utm_source')
                                ->get();
        $ContentYesterday = ReportDesigner::select(DB::raw('sp_utm_content,utm_source, count(*) as `count`'))
                                ->whereRaw("createdtime >= (CURDATE()- INTERVAL 1 day) and createdtime <= concat(CURDATE() - INTERVAL 1 day, ' ', CURTIME())")
                                ->groupby('sp_utm_content')
                                ->having('count','>',20)
                                ->get();
        $ContentToday = ReportDesigner::select(DB::raw('sp_utm_content,utm_source, count(*) as `count`'))
                                ->whereRaw('createdtime = CURDATE()')
                                ->groupby('sp_utm_content')
                                ->get();
        $resultSource = [];
        foreach ($SourceYesterday as $yesterday) {
            foreach ($SourceToday as $today) {
                if($today->utm_source == $yesterday->utm_source) {
                  $resultSource[] = [
                                    'utm_source' => $today->utm_source,
                                    'difference' => 100 - round(($today->count / $yesterday->count) * 100),
                                    'count' => $today->count - $yesterday->count
                  ];
                }
            }
        }
        $resultContent = [];
        foreach ($ContentYesterday as $yesterday) {
            foreach ($ContentToday as $today) {
                if($today->sp_utm_content == $yesterday->sp_utm_content) {
                  $resultContent[] = [
                                    'sp_utm_content' => $today->sp_utm_content,
                                    'utm_source' => $today->utm_source,
                                    'difference' => (100-round($today->count / $yesterday->count,2)*100),
                                    'count' => $today->count - $yesterday->count
                  ];
                }
            }
        }
        $text = '';
        foreach ($resultSource as $source) {
            $text .= chr(10) . $source['utm_source'] . ' ' . $source['difference'] . '%(' . $source['count'] . ')' . chr(10);
            foreach ($resultContent as $content) {
                $sp_utm_content = $content['sp_utm_content'];
                $sp_utm_content = str_replace('_',' ',$sp_utm_content);
                if ($source['utm_source'] == $content['utm_source'] && $content['difference'] > 30) {
                    $text .= $sp_utm_content . ' ' . $content['difference'] . '%(' . $content['count'] . ')' . chr(10);
                }
            }
        }
        return $text;
    }
    public function WeekInfo()
    {
            $date = New DateTime();
            
            $weeks = [];
            for ($i = 0; $i < 2; $i++) {
            $end = $date->format('Y-m-d');
            $begin = $date->modify('-1 week')->format('Y-m-d');
            $weeks[$i] = ReportDesigner::select(DB::raw("utm_source as `source`,
                                                        sp_utm_content as `web`,
                                                        count(*) as `count`"))
                                                        ->whereBetween('createdtime',[$begin,$end])
                                                        ->groupby('utm_source')
                                                        ->groupby('sp_utm_content')
                                                        ->get();    
            }
            
            $text = '';
            foreach ($weeks[0] as $row) {
                foreach ($weeks[1] as $twoRow) {
                    if($row->web == $twoRow->web && $row->source == $twoRow->source) {
                        $delta = round($row->count / ($twoRow->count / 100));
                        $delta = 100 - $delta;
                        if($delta > 30) {
                        $text .= $row->web . ' ' . $row->source . ' / ' . $twoRow->count . ' / ' . $row->count . ' / ' . $delta . '%' . chr(10);
                        }
                    }
                }
            }
            if ($text == '') {
                $text = "Не найдено совпадающих веб-мастеров";
            }
            return $text;
    }
}

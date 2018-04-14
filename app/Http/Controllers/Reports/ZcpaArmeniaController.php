<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaArmeniaController extends Controller
{
    function getStafferWebs()
    {
        $apiLink = 'https://a.zcpa.ru//affiliate/staff-list?api_key=645j6nbuhb4234v2y123ggh123123jjbghrw';
        $res = file_get_contents($apiLink);
        $obj_res = json_decode($res);
        $webs = implode(",", $obj_res->items);

        return $webs;
    }

    function getData($chatid)
    {

        try
        {
            $keyboard = [['Сегодня', 'Вчера'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, 'Выберите временной промежуток' . chr(10) . 'Или введите дату в формате (31-12-17)',
                'zcpa_dir', $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return 'Введите дату в формате 2017-02-29';
    }

    function getGeoKeyboard($chatid)
    {
        try
        {
            if ($chatid == 348169607 || $chatid == 153470584 || $chatid == 119223267) //admin, соловьев, булдаков
            {
                $keyboard = [
                    ['🇷🇺 RU', '🇪🇺 EU', '🇦🇲 AM', '🇰🇿 KZ', '🇬🇪 GE', '🇰🇬 KG', '🇺🇿 UZ'],
                    ['Назад']];
            } elseif ($chatid == 254248313) //воронин
            {
                $keyboard = [
                    ['🇷🇺 RU'],
                    ['Назад']];
            } elseif ($chatid == 308871210) //якобсон
            {
                $keyboard = [
                    ['🇺🇿 UZ'],
                    ['Назад']];
            } elseif ($chatid == 95508999) //жигульский
            {
                $keyboard = [
                    ['🇰🇬 KG'],
                    ['Назад']];
            } elseif ($chatid == 85932780) //чуприков
            {
                $keyboard = [
                    ['🇰🇿 KZ'],
                    ['Назад']];
            } elseif ($chatid == 340126802) //беляев
            {
                $keyboard = [
                    ['🇦🇲 AM'],
                    ['Назад']];
            } elseif ($chatid == 262743353) //казенова
            {
                $keyboard = [
                    ['🇬🇪 GE'],
                    ['Назад']];
            } elseif ($chatid == 264647841) //чинилов
            {
                $keyboard = [
                    ['🇪🇺 EU'],
                    ['Назад']];
            } else
            {
                return response()->json(['success' => true]);
            }


            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, 'Формирую список', 'zcpa_dir', $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
        return 'Выберите гео';
    }

    function getCustomData($createdtime, $chatid)
    {
        if ($createdtime == 'Вчера')
        {
            $createdtime = date("Y-m-d", strtotime('-1 days'));
        } elseif ($createdtime == 'Сегодня')
        {
            $createdtime = date('Y-m-d');
        } else
        {
            $createdtime = date('d-m-y', strtotime($createdtime));
            $createdtime = date("Y-m-d", strtotime($createdtime));
        }

        $date = date("$createdtime");

        $country = new LogController();
        $country = $country->getManualInportReport($chatid);

        $stuff_webs = $this->getStafferWebs();
        $flag = '';
//        ['🇷🇺 RU', '🇪🇺 EU', '🇦🇲 AM', '🇰🇿 KZ', '🇬🇪 GE', '🇰🇬 KG', '🇺🇿 UZ'],
        if($country == 'RU'){$flag = '🇷🇺 Россия';}
        elseif ($country == 'AM'){$flag = '🇦🇲 Армения';}
        elseif ($country == 'KZ'){$flag = '🇰🇿 Казахстан';}
        elseif ($country == 'GE'){$flag = '🇬🇪 Грузия';}
        elseif ($country == 'KG'){$flag = '🇰🇬 Киргизия';}
        elseif ($country == 'UZ'){$flag = '🇺🇿 Узбекинстан';}

        $text = $date . ' - ' . $flag . chr(10) . chr(10);

        $result_am = ReportDesigner::select(DB::raw("
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("language", "=", "$country")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->get();

        $result_ru = ReportDesigner::select(DB::raw("
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("language", "=", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->get();

        $result_total = ReportDesigner::select(DB::raw("
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->get();

        $result_am_stuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("language", "=", "$country")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $result_ru_stuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("language", "=", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $result_am_outstuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("language", "=", "$country")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content not in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $result_ru_outstuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("country", "=", "$country")
            ->where("language", "=", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content not in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $text .= 'Общее: ' . $result_total[0]->leads . '/' . $result_total[0]->apruv . '/' . $result_total[0]->perc . '%' . chr(10);
        $text .= 'Нативная: ' . $result_am[0]->leads . '/' . $result_am[0]->apruv . '/' . $result_am[0]->perc . '%' . chr(10);
        $text .= 'Русскоговорящие: ' . $result_ru[0]->leads . '/' . $result_ru[0]->apruv . '/' . $result_ru[0]->perc . '%' . chr(10) . chr(10);
        $text .= 'Zcpa внутренние:' . chr(10) . 'Нативная:' . chr(10);

        if (count($result_am_stuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_am_stuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->apruv . '/' . $value->perc . '%' . chr(10);
            }
        }

        $text .= chr(10) . 'Русскоговорящие:' . chr(10);

        if (count($result_ru_stuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_ru_stuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->apruv . '/' . $value->perc . '%' . chr(10);
            }
        }

        $text .= chr(10) . 'Zcpa внешние:' . chr(10) . 'Нативная:' . chr(10);

        if (count($result_am_outstuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_am_outstuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->apruv . '/' . $value->perc . '%' . chr(10);
            }
        }

        $text .= chr(10) . 'Русскоговорящие:' . chr(10);

        if (count($result_ru_outstuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_ru_outstuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->leads . '/' . $value->perc . '%' . chr(10);
            }
        }

        $message = new MessageController();
        $message->sendMessage($chatid, $text, 'zcpa_dir');

        $log = new LogController();
        $log->setTelegramLog($chatid, 'Zcpa(am,kz,ge)', $text);
    }

    function getGeo()
    {
        $geo_collection = ['AM', 'GE', 'AZ'];
        return $geo_collection;
    }

    function getCustomEuroZone($createdtime, $chatid)
    {
        if ($createdtime == 'Вчера')
        {
            $createdtime = date("Y-m-d", strtotime('-1 days'));
        } elseif ($createdtime == 'Сегодня')
        {
            $createdtime = date('Y-m-d');
        } else
        {
            $createdtime = date('d-m-y', strtotime($createdtime));
            $createdtime = date("Y-m-d", strtotime($createdtime));
        }


        $date = date("$createdtime");

//        $country = new LogController();
//        $country = $country->getManualInportReport($chatid);

        $stuff_webs = $this->getStafferWebs();

        $text = $date . ' - ' . '🇪🇺 Европа' . chr(10) . chr(10);

        $result_am = ReportDesigner::select(DB::raw("
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("language", "<>", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->get();

        $result_ru = ReportDesigner::select(DB::raw("
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("language", "=", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->get();

        $result_total = ReportDesigner::select(DB::raw("
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->get();

        $result_am_stuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("language", "<>", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $result_ru_stuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("language", "=", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $result_am_outstuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("language", "<>", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content not in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $result_ru_outstuff = ReportDesigner::select(DB::raw("ifnull(sp_utm_content,'Итого') as sp_utm_content,
                        COUNT(*) AS `leads`, SUM(apruv) as `apruv`,
                        ROUND(((SUM(apruv) / COUNT(*)) * 100)) AS `perc`"))
            ->where("location", "=", "Европа")
            ->where("language", "=", "RU")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = '$createdtime'")
            ->whereRaw("sp_utm_content not in ($stuff_webs)")
            ->groupBy(DB::raw('sp_utm_content with rollup'))
            ->get();

        $text .= 'Общее: ' . $result_total[0]->leads . '/' . $result_total[0]->apruv . '/' . $result_total[0]->perc . '%' . chr(10);
        $text .= 'Нативная: ' . $result_am[0]->leads . '/' . $result_am[0]->apruv . '/' . $result_am[0]->perc . '%' . chr(10);
        $text .= 'Русскоговорящие: ' . $result_ru[0]->leads . '/' . $result_ru[0]->apruv . '/' . $result_ru[0]->perc . '%' . chr(10) . chr(10);
        $text .= 'Zcpa внутренние:' . chr(10) . 'Нативная:' . chr(10);

        if (count($result_am_stuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_am_stuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->apruv . '/' . $value->perc . '%' . chr(10);
            }
        }

        $text .= chr(10) . 'Русскоговорящие:' . chr(10);

        if (count($result_ru_stuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_ru_stuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->apruv . '/' . $value->perc . '%' . chr(10);
            }
        }

        $text .= chr(10) . 'Zcpa внешние:' . chr(10) . 'Нативная:' . chr(10);

        if (count($result_am_outstuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_am_outstuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->apruv . '/' . $value->perc . '%' . chr(10);
            }
        }

        $text .= chr(10) . 'Русскоговорящие:' . chr(10);

        if (count($result_ru_outstuff) == 0)
        {
            $text .= 'Нет лидов!' . chr(10) . chr(10);
        } else
        {
            foreach ($result_ru_outstuff as $value)
            {
                $text .= $value->sp_utm_content . ': ' . $value->leads . '/' . $value->leads . '/' . $value->perc . '%' . chr(10);
            }
        }

        $message = new MessageController();
        $message->sendMessage($chatid, $text, 'zcpa_dir');

        $log = new LogController();
        $log->setTelegramLog($chatid, 'Zcpa(am,kz,ge)', $text);
    }

    function inputTimeRange($chatid)
    {
        try
        {
            $send_message = new MessageController();
            $send_message->sendMessage($chatid, 'Выберите временной промежуток', 'zcpa_dir');
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return 'Введите дату в формате 2017-02-29';
    }
}

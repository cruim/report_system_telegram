<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\Reports\CallCenterCurrentMonthData;
use App\Model\Reports\CallCenterLastMonthData;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\VTiger\VTUser;
use App\Model\VTiger\ReportDesigner;

class CallCenterController extends Controller
{
    function buildKeyboard($chatid)
    {
        $message = 'Выберите временной промежуток' . chr(10) . 'Или введите дату в формате 2017-02-29' . chr(10) .
            '[Wiki](http://wiki.finereports.info/index.php?title=КЦ_статистика)';
        try
        {
            $keyboard = [['КЦ статистика - Сегодня', 'КЦ статистика - Вчера'],
                ['КЦ статистика - Текущий месяц', 'КЦ статистика - Прошлый месяц'],
                ['Назад']];

            $reply_markup = \Telegram::replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]);

            $send_message = new MessageController();
            $send_message->sendMessage($chatid, $message, 'manager', $reply_markup);
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function getCustomData($telegram_id, $createdtime)
    {
        try
        {
            $tiger_id = Abonent::select("tiger_2_id")
                ->where("telegram_id", "=", $telegram_id)
                ->get();
            $tiger_id = $tiger_id[0]->tiger_2_id;

            $title = VTUser::select("title")
                ->where('id', '=', $tiger_id)
                ->get();

            if ($telegram_id == env('TELEGRAM_ADMIN_ID'))
            {
                $tiger_id = 291;
            }
            $title = $title[0]->title;

            if ($title == 'Директор' || $title == 'Руководитель отдела')
            {
                return $this->getHeadInfoCustom($telegram_id, $tiger_id, $createdtime);
            } elseif ($title == 'Старший группы' || $telegram_id == env('TELEGRAM_ADMIN_ID'))
            {
                return $this->customSeniorManagerInfo($telegram_id, $tiger_id, $createdtime);
            } elseif ($title == 'Менеджер' || $title == 'Стажер')
            {
                return $this->customManagerInfo($telegram_id, $tiger_id, $createdtime);
            } else
            {
                return $this->ErrorMessage;
            }
        } catch (\Exception $e)
        {
            $text = $e->getCode() . ' ' . $e->getMessage();
            $error_message = new LogController();
            $error_message->setErrorLog($telegram_id,$text);
            return response()->json(['success' => true]);
        }
    }

    public function getTodayData($telegram_id)
    {
        try{
            $tiger_id = Abonent::select("tiger_2_id")
                ->where("telegram_id", "=", $telegram_id)
                ->get();
            $tiger_id = $tiger_id[0]->tiger_2_id;

            $title = VTUser::select("title")
                ->where('id', '=', $tiger_id)
                ->get();

            $date = date('Y-m-d');

            if ($telegram_id == env('TELEGRAM_ADMIN_ID'))
            {
                $tiger_id = 8;
            }
            $title = $title[0]->title;

            return $this->getDataFromApi($tiger_id,$title,$date,$date,$telegram_id);

        }catch (\Exception $e)
        {
            $text = $e->getCode() . ' ' . $e->getMessage();
            $error_message = new LogController();
            $error_message->setErrorLog($telegram_id,$text);
            return response()->json(['success' => true]);
        }

    }

    public function getYesterdayData($telegram_id)
    {
        try{
            $tiger_id = Abonent::select("tiger_2_id")
                ->where("telegram_id", "=", $telegram_id)
                ->get();
            $tiger_id = $tiger_id[0]->tiger_2_id;
            if ($telegram_id == env('TELEGRAM_ADMIN_ID'))
            {
                $tiger_id = 8;
            }

            $title = VTUser::select("title")
                ->where('id', '=', $tiger_id)
                ->get();

            $title = $title[0]->title;

            $date = date("Y-m-d", strtotime('-1 days'));

            return $this->getDataFromApi($tiger_id,$title,$date,$date,$telegram_id);

        }catch (\Exception $e)
        {
            $text = $e->getCode() . ' ' . $e->getMessage();
            $error_message = new LogController();
            $error_message->setErrorLog($telegram_id,$text);
            return response()->json(['success' => true]);
        }

    }

    public function getCurMonthData($telegram_id)
    {
        try{
            $tiger_id = Abonent::select("tiger_2_id")
                ->where("telegram_id", "=", $telegram_id)
                ->get();
            $tiger_id = $tiger_id[0]->tiger_2_id;
            if ($telegram_id == env('TELEGRAM_ADMIN_ID'))
            {
                $tiger_id = 8;
            }

            $title = VTUser::select("title")
                ->where('id', '=', $tiger_id)
                ->get();

            $title = $title[0]->title;
            $end_date = date("Y-m-d");
            $start_date = date("Y-m") . '-01';

            return $this->getDataFromApi($tiger_id,$title,$start_date,$end_date,$telegram_id);

        }catch (\Exception $e)
        {
            $text = $e->getCode() . ' ' . $e->getMessage();
            $error_message = new LogController();
            $error_message->setErrorLog($telegram_id,$text);
            return response()->json(['success' => true]);
        }

    }

    public function getLastMonthData($telegram_id)
    {
        try{
            $tiger_id = Abonent::select("tiger_2_id")
                ->where("telegram_id", "=", $telegram_id)
                ->get();
            $tiger_id = $tiger_id[0]->tiger_2_id;
            if ($telegram_id == env('TELEGRAM_ADMIN_ID'))
            {
                $tiger_id = 8;
            }
            $title = VTUser::select("title")
                ->where('id', '=', $tiger_id)
                ->get();

            $title = $title[0]->title;

            $month_ini = new \DateTime("first day of last month");
            $month_end = new \DateTime("last day of last month");
            $start_date = $month_ini->format('Y-m-d');
            $end_date = $month_end->format('Y-m-d');

            return $this->getDataFromApi($tiger_id,$title,$start_date,$end_date,$telegram_id);

        }catch (\Exception $e)
        {
            $text = $e->getCode() . ' ' . $e->getMessage();
            $error_message = new LogController();
            $error_message->setErrorLog($telegram_id,$text);
            return response()->json(['success' => true]);
        }

    }

    public function customManagerInfo($telegram_id, $tigerId, $createdtime)
    {
        $send_message = new MessageController();
        $send_message->sendMessage($telegram_id, 'Считаю', 'manager');

        $url = 'http://analytics.finereports.info/api/managersstatistic?id=' . $tigerId . '&begin=' . $createdtime . '&end=' . $createdtime;
        $result = $this->getCurl($url);
        if($result == false){return response()->json(['success' => true]);}
        $text = $createdtime;
        if (count($result) > 0)
        {
            foreach ($result as $value)
            {
                if ($value->leads > 0)
                {
                    $text .= chr(10) . $value->category
                        . ' - '
                        . $value->currency_zone
                        . ': '
                        . chr(10)
                        . 'Заказы - '
                        . $value->leads
                        . '; Апрув - '
                        . $value->apruv
                        . '('
                        . round($value->apruv / $value->leads * 100)
                        . '%); Отказы - '
                        . $value->denial
                        . 'шт. Конверсия: '
                        . $value->konv
                        . '%; Доставляемость: '
                        . $value->deliv
                        . '%; Среднее банок: '
                        . $value->bank_avg
                        . '; Сумма продаж:'
                        . $value->total_sum
                        . ' руб;'
                        . chr(10)
                        . ' Зарплата: '
                        . $value->salary
                        . ' руб.'
                        . chr(10)
                        . chr(10);
                } else
                {
                    $text .= 'Нет данных за указынный период.';
                }
            }
        } else
        {
            $text .= 'Нет данных за указынный период.';
        }

        $send_message->sendMessage($telegram_id, $text, 'manager');
    }

    public function todayManagerInfo($tigerId)
    {
        try
        {
            $date = date('Y-m-d');
            $url = 'http://analytics.finereports.info/api/managersstatistic?id=' . $tigerId . '&begin=' . $date . '&end=' . $date;
            $result = $this->getCurl($url);
            if($result == false){return response()->json(['success' => true]);}
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->category
                            . ' - '
                            . $value->currency_zone
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round($value->apruv / $value->leads * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_avg
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
            } else
            {
                $text .= 'Нет данных за указынный период.';
            }

            return $text;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    public function yesterdayManagerInfo($tigerId)
    {
        try
        {
            $date = date("Y-m-d", strtotime('-1 days'));
            $url = 'http://analytics.finereports.info/api/managersstatistic?id=' . $tigerId . '&begin=' . $date . '&end=' . $date;
            $result = $this->getCurl($url);
            if($result == false){return response()->json(['success' => true]);}
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->category
                            . ' - '
                            . $value->currency_zone
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round($value->apruv / $value->leads * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_avg
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
            } else
            {
                $text .= 'Нет данных за указынный период.';
            }
            return $text;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    public function curMonthManagerInfo($tigerId)
    {
        try
        {
            $date = date("Y-m-d");
            $start_date = date("Y-m") . '-01';
            $url = 'http://analytics.finereports.info/api/managersstatistic?id=' . $tigerId . '&begin=' . $start_date . '&end=' . $date;
            $result = $this->getCurl($url);
            if($result == false){return response()->json(['success' => true]);}
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->category
                            . ' - '
                            . $value->currency_zone
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round($value->apruv / $value->leads * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_avg
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
            } else
            {
                $text .= 'Нет данных за указынный период.';
            }
            return $text;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    public function lastMonthManagerInfo($tigerId)
    {
        try
        {
            $month_ini = new \DateTime("first day of last month");
            $month_end = new \DateTime("last day of last month");

            $start_date = $month_ini->format('Y-m-d');
            $date = $month_end->format('Y-m-d');

            $url = 'http://analytics.finereports.info/api/managersstatistic?id=' . $tigerId . '&begin=' . $start_date . '&end=' . $date;
            $result = $this->getCurl($url);
            if($result == false){return response()->json(['success' => true]);}
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->category
                            . ' - '
                            . $value->currency_zone
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round($value->apruv / $value->leads * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_avg
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
            } else
            {
                $text .= 'Нет данных за указынный период.';
            }
            return $text;
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    public function customSeniorManagerInfo($telegram_id, $tigerId, $createdtime)
    {
        try
        {
            $send_message = new MessageController();
            $send_message->sendMessage($telegram_id, 'Считаю', 'manager');
            $url = 'http://analytics.finereports.info/api/seniorstatistic?id=' . $tigerId . '&begin=' . $createdtime . '&end=' . $createdtime;
            $result = $this->getCurl($url);
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $outerKey => $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->manager . ' '
                            . $value->category
                            . ' - '
                            . $value->region
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round(($value->apruv / $value->leads) * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_average
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
                $text = \GuzzleHttp\json_encode($text);
                $text = \GuzzleHttp\json_decode($text);
                $send_message->sendMessage($telegram_id,(str_replace('_',' ',$text)),'manager');
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    public function todaySeniorManagerInfo($tigerId, $telegram_id)
    {
        try{
            $date = date('Y-m-d');
            $url = 'http://analytics.finereports.info/api/seniorstatistic?id=' . $tigerId . '&begin=' . $date . '&end=' . $date;
            $result = $this->getCurl($url);
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $outerKey => $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->manager . ' '
                            . $value->category
                            . ' - '
                            . $value->region
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round(($value->apruv / $value->leads) * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_average
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
                return (str_replace('_',' ',$text));
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    public function yesterdaySeniorManagerInfo($tigerId, $telegram_id)
    {
        try{
            $date = date("Y-m-d", strtotime('-1 days'));
            $url = 'http://analytics.finereports.info/api/seniorstatistic?id=' . $tigerId . '&begin=' . $date . '&end=' . $date;
            $result = $this->getCurl($url);
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $outerKey => $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->manager . ' '
                            . $value->category
                            . ' - '
                            . $value->region
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round(($value->apruv / $value->leads) * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_average
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
                return (str_replace('_',' ',$text));
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    public function curMonthSeniorManagerInfo($tigerId, $telegram_id)
    {
        try
        {
            $date = date("Y-m-d");
            $start_date = date("Y-m") . '-01';
            $url = 'http://analytics.finereports.info/api/seniorstatistic?id=' . $tigerId . '&begin=' . $start_date . '&end=' . $date;
            $result = $this->getCurl($url);
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $outerKey => $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->manager . ' '
                            . $value->category
                            . ' - '
                            . $value->region
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round(($value->apruv / $value->leads) * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_average
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
                return (str_replace('_',' ',$text));
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }

    }

    public function lastMonthSeniorManagerInfo($tigerId, $telegram_id)
    {
        try{
            $month_ini = new \DateTime("first day of last month");
            $month_end = new \DateTime("last day of last month");

            $start_date = $month_ini->format('Y-m-d');
            $date = $month_end->format('Y-m-d');
            $url = 'http://analytics.finereports.info/api/seniorstatistic?id=' . $tigerId . '&begin=' . $start_date . '&end=' . $date;
            $result = $this->getCurl($url);
            $text = '';
            if (count($result) > 0)
            {
                foreach ($result as $outerKey => $value)
                {
                    if ($value->leads > 0)
                    {
                        $text .= $value->manager . ' '
                            . $value->category
                            . ' - '
                            . $value->region
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->apruv
                            . '('
                            . round(($value->apruv / $value->leads) * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . $value->konv
                            . '%; Доставляемость: '
                            . $value->deliv
                            . '%; Среднее банок: '
                            . $value->bank_average
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    } else
                    {
                        $text .= 'Нет данных за указынный период.';
                    }
                }
                return (str_replace('_',' ',$text));
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
    }

    public function getHeadInfoToday()
    {
        $result = ReportDesigner::select(DB::raw("  CASE 
                                                    WHEN currency_zone = 'EUR' AND price_category = 'стандарт' 
                                                    THEN 'Европа'
                                                    WHEN currency_zone = 'RUB' AND price_category = 'стандарт'
                                                    THEN 'СНГ'
                                                    ELSE 'Офферы за 1'
                                                    END as `parametr`,
                                                    COUNT(*) as `leads`,
                                                    ROUND((SUM(`apruv`) / COUNT(*))*100) as `perc_apruv`,
                                                    ROUND(((SUM(apruv)/(SUM(apruv)+SUM(denial)+SUM(handing)))*100),1) as `konv`,
                                                    ROUND(((SUM(`paid`) / SUM(`apruv`))*100)) as `deliv`,
                                                    ROUND(SUM(`total_sum`) / SUM(`apruv`)) as `average`,
                                                    ROUND(SUM(`motivate`) / SUM(`apruv`),2) as `average_bank`"))
            ->whereRaw("createdtime = CURDATE()")
            ->groupby('parametr')
            ->get();
        $text = '';
        if (count($result) > 0)
        {
            foreach ($result as $row)
            {
                $text .= $row->parametr
                    . ': Лиды - ' . $row->leads
                    . ', %Апрув - ' . $row->perc_apruv
                    . ', %Конверсии - ' . $row->konv
                    . ', %Доставляемости - ' . $row->deliv
                    . ', Ср.чек - ' . $row->average
                    . ', Ср.чек(б) - ' . $row->average_bank
                    . chr(10) . chr(10);
            }
        } else
        {
            $text .= 'Нет данных за указанный период';
        }

        return $text;
    }

    public function getHeadInfoCustom($telegram_id, $tiger_id, $createdtime)
    {
        $send_message = new MessageController();
        $send_message->sendMessage($telegram_id, 'Считаю', 'manager');

        $result = ReportDesigner::select(DB::raw("  CASE 
                                                    WHEN currency_zone = 'EUR' AND price_category = 'стандарт' 
                                                    THEN 'Европа'
                                                    WHEN currency_zone = 'RUB' AND price_category = 'стандарт'
                                                    THEN 'СНГ'
                                                    ELSE 'Офферы за 1'
                                                    END as `parametr`,
                                                    COUNT(*) as `leads`,
                                                    ROUND((SUM(`apruv`) / COUNT(*))*100) as `perc_apruv`,
                                                    ROUND(((SUM(apruv)/(SUM(apruv)+SUM(denial)+SUM(handing)))*100),1) as `konv`,
                                                    ROUND(((SUM(`paid`) / SUM(`apruv`))*100)) as `deliv`,
                                                    ROUND(SUM(`total_sum`) / SUM(`apruv`)) as `average`,
                                                    ROUND(SUM(`motivate`) / SUM(`apruv`),2) as `average_bank`"))
            ->whereRaw("createdtime = '$createdtime'")
            ->groupby('parametr')
            ->get();
        $text = '';
        if (count($result) > 0)
        {
            foreach ($result as $row)
            {
                $text .= $row->parametr
                    . ': Лиды - ' . $row->leads
                    . ', %Апрув - ' . $row->perc_apruv
                    . ', %Конверсии - ' . $row->konv
                    . ', %Доставляемости - ' . $row->deliv
                    . ', Ср.чек - ' . $row->average
                    . ', Ср.чек(б) - ' . $row->average_bank
                    . chr(10) . chr(10);
            }
        } else
        {
            $text .= 'Нет данных за указанный период';
        }
        $send_message->sendMessage($telegram_id, $text, 'manager');
    }

    public function getHeadInfoYesterday()
    {
        $result = ReportDesigner::select(DB::raw("  CASE 
                                                    WHEN currency_zone = 'EUR' AND price_category = 'стандарт' 
                                                    THEN 'Европа'
                                                    WHEN currency_zone = 'RUB' AND price_category = 'стандарт'
                                                    THEN 'СНГ'
                                                    ELSE 'Офферы за 1'
                                                    END as `parametr`,
                                                    COUNT(*) as `leads`,
                                                    ROUND((SUM(`apruv`) / COUNT(*))*100) as `perc_apruv`,
                                                    ROUND(((SUM(apruv)/(SUM(apruv)+SUM(denial)+SUM(handing)))*100),1) as `konv`,
                                                    ROUND(((SUM(`paid`) / SUM(`apruv`))*100)) as `deliv`,
                                                    ROUND(SUM(`total_sum`) / SUM(`apruv`)) as `average`,
                                                    ROUND(SUM(`motivate`) / SUM(`apruv`),2) as `average_bank`"))
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupby('parametr')
            ->get();
        $text = '';
        if (count($result) > 0)
        {
            foreach ($result as $row)
            {
                $text .= $row->parametr
                    . ': Лиды - ' . $row->leads
                    . ', %Апрув - ' . $row->perc_apruv
                    . ', %Конверсии - ' . $row->konv
                    . ', %Доставляемости - ' . $row->deliv
                    . ', Ср.чек - ' . $row->average
                    . ', Ср.чек(б) - ' . $row->average_bank
                    . chr(10) . chr(10);
            }
        } else
        {
            $text .= 'Нет данных за указанный период';
        }
        return $text;
    }

    public function getHeadInfoCurMonth()
    {
        $result = CallCenterCurrentMonthData::get();
        $text = '';
        foreach ($result as $value)
        {
            $text .= $value->geo . ': Лиды - ' . $value->leads . ', %Апрув - ' . $value->apruv_persent . ', %Конверсии - ' .
                $value->conversion . ', %Доставляемости - ' . $value->delivery . ', Ср.чек - ' . $value->avg_check .
                ', Ср.чек(б) - ' . $value->avg_jars . chr(10) . chr(10);
        }
        return $text;
    }

    public function getHeadInfoLastMonth()
    {
        $result = CallCenterLastMonthData::get();
        $text = '';
        foreach ($result as $value)
        {
            $text .= $value->geo . ': Лиды - ' . $value->leads . ', %Апрув - ' . $value->apruv_persent . ', %Конверсии - ' .
                $value->conversion . ', %Доставляемости - ' . $value->delivery . ', Ср.чек - ' . $value->avg_check .
                ', Ср.чек(б) - ' . $value->avg_jars . chr(10) . chr(10);
        }
        return $text;
    }

    function dailyInsertHeadInfoLastMonth()
    {
        DB::statement("truncate telegram.call_center_last_month_head_data");

        DB::insert("insert into telegram.call_center_last_month_head_data(SELECT null,
	 CASE
WHEN currency_zone = 'EUR' AND price_category = 'стандарт'
THEN 'Европа'
WHEN currency_zone = 'RUB' AND price_category = 'стандарт'
THEN 'СНГ'
ELSE 'Офферы за 1'
END as `parametr`,
COUNT(*) as `leads`,
ROUND((SUM(`apruv`) / COUNT(*))*100) as `perc_apruv`,
ROUND(((SUM(apruv)/(SUM(apruv)+SUM(denial)+SUM(handing)))*100),1) as `konv`,
ROUND(((SUM(`paid`) / SUM(`apruv`))*100)) as `deliv`,
ROUND(SUM(`total_sum`) / SUM(`apruv`)) as `average`,
ROUND(SUM(`motivate`) / SUM(`apruv`),2) as `average_bank` from `analytics`.`report_designer` where MONTH(`createdtime`) = MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) AND YEAR(`createdtime`) = YEAR(NOW()) group by `parametr`)");
    }

    function dailyInsertHeadInfoCurrentMonth()
    {
        DB::statement("truncate telegram.call_center_current_month_head_data");

        DB::insert("insert into telegram.call_center_current_month_head_data(select null,
CASE
WHEN currency_zone = 'EUR' AND price_category = 'стандарт'
THEN 'Европа'
WHEN currency_zone = 'RUB' AND price_category = 'стандарт'
THEN 'СНГ'
ELSE 'Офферы за 1'
END as `parametr`,
COUNT(*) as `leads`,
ROUND((SUM(`apruv`) / COUNT(*))*100) as `perc_apruv`,
ROUND(((SUM(apruv)/(SUM(apruv)+SUM(denial)+SUM(handing)))*100),1) as `konv`,
ROUND(((SUM(`paid`) / SUM(`apruv`))*100)) as `deliv`,
ROUND(SUM(`total_sum`) / SUM(`apruv`)) as `average`,
ROUND(SUM(`motivate`) / SUM(`apruv`),2) as `average_bank` from `analytics`.`report_designer` where MONTH(`createdtime`) = MONTH(NOW()) AND YEAR(`createdtime`) = YEAR(NOW()) group by `parametr`)");
    }

    function getDataFromApi($tiger_id, $title, $start_date, $end_date, $telegram_id)
    {
        try
        {
            $token = $this->apiAuthorization();

            $params = [
                'motivate' => 'motivate',
                'action' => 'report',
                'params' => [
                    ['field' => 'date(createdtime)', 'key' => 'between', 'value' => [$start_date, $end_date]],
                    ['field' => 'user_id', 'key' => '=', 'value' => $tiger_id],
                ],

            ];
            $string = http_build_query($params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://api.finereports.info/ccstat?" . $string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt( $ch, CURLOPT_POST, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $token->token
            ));
            $result = json_decode(curl_exec($ch));
            curl_close($ch);
            $text = '';
            if (count($result) > 0)
            {
                if($title == 'Директор' || $title == 'Руководитель отдела' || $telegram_id == env('TELEGRAM_ADMIN_ID'))
                {
                    foreach ($result as $row)
                    {
                        $text .= $row->parametr
                            . ': Лиды - ' . $row->leads
                            . ', %Апрув - ' . $row->perc_apruv
                            . ', %Конверсии - ' . $row->konv
                            . ', %Доставляемости - ' . $row->deliv
                            . ', Ср.чек - ' . $row->average
                            . ', Ср.чек(б) - ' . $row->average_bank
                            . chr(10) . chr(10);
                    }
                }
                elseif ($title == 'Старший группы')
                {
                    foreach ($result->original as $value)
                    {
                        $text .= $value->manager . ' '
                            . $value->category
                            . ' - '
                            . $value->currency_zone
                            . ': '
                            . chr(10)
                            . 'Заказы - '
                            . $value->leads
                            . '; Апрув - '
                            . $value->approve
                            . '('
                            . round(($value->approve / $value->leads) * 100)
                            . '%); Отказы - '
                            . $value->denial
                            . 'шт. Конверсия: '
                            . round($value->konv,2)
                            . '%; Доставляемость: '
                            . round($value->deliv,2)
                            . '%; Среднее банок: '
                            . round($value->bank_average,1)
                            . '; Сумма продаж:'
                            . $value->total_sum
                            . ' руб;'
                            . chr(10)
                            . ' Зарплата: '
                            . $value->salary_senior
                            . ' руб.'
                            . chr(10)
                            . chr(10);
                    }
                }
                elseif ($title == 'Менеджер' || $title == 'Стажер')
                {
                    foreach ($result->original as $value)
                    {
                        if ($value->leads > 0)
                        {
                            $category = $value->category;
                            $currency_zone = $value->currency_zone;
                            if($category == 'all' || $currency_zone == 'all'){$category = 'Все регионы'; $currency_zone = 'Все офферы';}
                            elseif($category == 'overall' || $currency_zone == 'overall'){$category = 'Итог';$currency_zone = 'Итог';}
                            $text .= $category
                                . ' - '
                                . $currency_zone
                                . ': '
                                . chr(10)
                                . 'Заказы - '
                                . $value->leads
                                . '; Апрув - '
                                . $value->approve
                                . '('
                                . round($value->approve / $value->leads * 100)
                                . '%); Отказы - '
                                . $value->denial
                                . 'шт. Конверсия: '
                                . round($value->konv,2)
                                . '%; Доставляемость: '
                                . round($value->deliv,2)
                                . '%; Среднее банок: '
                                . round($value->average_bank,1)
                                . '; Сумма продаж:'
                                . $value->total_sum
                                . ' руб;'
                                . chr(10)
                                . ' Зарплата: '
                                . $value->salary
                                . ' руб.'
                                . chr(10)
                                . chr(10);
                        }
                    }
                }

            } else
            {
                $text .= 'Нет данных за указанный период';
            }

            return $text;
        }catch (\Exception $e)
        {
            $send_message = new MessageController();
            $send_message->sendMessage(348169607,'КЦ: ' . $e->getMessage(),'common');
            return response()->json(['success' => true]);
        }

    }

    function apiAuthorization()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.finereports.info/auth/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt( $ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=kraken36@list.ru&password=12038936147fish&client=web');
        $result = curl_exec($ch);
        curl_close($ch);
        $token = json_decode($result);

        return $token;
    }
}

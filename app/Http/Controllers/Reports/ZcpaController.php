<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use App\Model\VTiger\ReportDesigner;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ZcpaController extends Controller
{
    public function getZcpaDataToday()
    {
        $apiLink = 'https://a.zcpa.ru//affiliate/staff-list?api_key=645j6nbuhb4234v2y123ggh123123jjbghrw';
        $res = file_get_contents($apiLink);
        $obj_res = json_decode($res);
        $webs = implode(",", $obj_res->items);

        $inner_webs = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->whereRaw("sp_utm_content in($webs)")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = CURDATE()")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $outer_webs = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->whereRaw("sp_utm_content not in($webs)")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = CURDATE()")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $total_result = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = CURDATE()")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $text = 'ZCPA - Сегодня' . chr(10);
        $total_count = 0;
        $total_apruv = 0;

        foreach ($total_result as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }

        $text .= 'ZCPA Итог: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        $total_count = 0;
        $total_apruv = 0;

        foreach ($inner_webs as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }
        $text .= 'ZCPA Штатные: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        foreach ($inner_webs as $value)
        {
            $text .= 'Веб: ' . $value->sp_utm_content . ': ' . $value->count . '/' . $value->apruv . '%' . chr(10);
        }

        $total_count = 0;
        $total_apruv = 0;

        foreach ($outer_webs as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }
        $text .= 'ZCPA Внешние: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        foreach ($outer_webs as $value)
        {
            $text .= 'Веб: ' . $value->sp_utm_content . ': ' . $value->count . '/' . $value->apruv . '%' . chr(10);
        }
        return ($text);
    }

    public function getZcpaDataYesterday()
    {
        $apiLink = 'https://a.zcpa.ru//affiliate/staff-list?api_key=645j6nbuhb4234v2y123ggh123123jjbghrw';
        $res = file_get_contents($apiLink);
        $obj_res = json_decode($res);
        $webs = implode(",", $obj_res->items);

        $inner_webs = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->whereRaw("sp_utm_content in($webs)")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $outer_webs = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->whereRaw("sp_utm_content not in($webs)")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $total_result = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("createdtime = CURDATE() - interval 1 day")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $text = 'ZCPA - Вчера' . chr(10);
        $total_count = 0;
        $total_apruv = 0;

        foreach ($total_result as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }

        $text .= 'ZCPA Итог: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        $total_count = 0;
        $total_apruv = 0;

        foreach ($inner_webs as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }
        $text .= 'ZCPA Штатные: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        foreach ($inner_webs as $value)
        {
            $text .= 'Веб: ' . $value->sp_utm_content . ': ' . $value->count . '/' . $value->apruv . '%' . chr(10);
        }

        $total_count = 0;
        $total_apruv = 0;

        foreach ($outer_webs as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }
        $text .= 'ZCPA Внешние: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        foreach ($outer_webs as $value)
        {
            $text .= 'Веб: ' . $value->sp_utm_content . ': ' . $value->count . '/' . $value->apruv . '%' . chr(10);
        }
        return ($text);
    }

    public function getZcpaDataCurMonth()
    {
        $apiLink = 'https://a.zcpa.ru//affiliate/staff-list?api_key=645j6nbuhb4234v2y123ggh123123jjbghrw';
        $res = file_get_contents($apiLink);
        $obj_res = json_decode($res);
        $webs = implode(",", $obj_res->items);

        $inner_webs = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->whereRaw("sp_utm_content in($webs)")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW()) AND YEAR(`createdtime`) = YEAR(NOW())")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $outer_webs = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->whereRaw("sp_utm_content not in($webs)")
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW()) AND YEAR(`createdtime`) = YEAR(NOW())")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $total_result = ReportDesigner::select(DB::Raw("sp_utm_content, COUNT(*) AS `count`, SUM(apruv) AS `apruv`"))
            ->where("utm_source", "=", "zcpa")
            ->whereRaw("MONTH(`createdtime`) = MONTH(NOW()) AND YEAR(`createdtime`) = YEAR(NOW())")
            ->groupBy("sp_utm_content")
            ->orderBy("count", "desc")
            ->get();

        $text = 'ZCPA - Текущий месяц' . chr(10);
        $total_count = 0;
        $total_apruv = 0;

        foreach ($total_result as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }

        $text .= 'ZCPA Итог: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        $total_count = 0;
        $total_apruv = 0;

        foreach ($inner_webs as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }
        $text .= 'ZCPA Штатные: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        foreach ($inner_webs as $value)
        {
            $text .= 'Веб: ' . $value->sp_utm_content . ': ' . $value->count . '/' . $value->apruv . '%' . chr(10);
        }

        $total_count = 0;
        $total_apruv = 0;

        foreach ($outer_webs as $value)
        {
            $total_count += $value->count;
            $total_apruv += $value->apruv;
        }
        $text .= 'ZCPA Внешние: ' . $total_count . '/' . round($total_apruv / $total_count * 100) . '%' . chr(10);

        foreach ($outer_webs as $value)
        {
            $text .= 'Веб: ' . $value->sp_utm_content . ': ' . $value->count . '/' . $value->apruv . '%' . chr(10);
        }
        return ($text);
    }

    function getMessageFromZcpa(Request $request)
    {
        $input = ['id' => $request->id, 'message' => $request->message];
        $validation = \Validator::make($input,
            [
                'id' => 'required|array',
                'message' => 'required'
            ]);

        if ($validation->fails())
        {
            return $validation->errors;
        }
        $send_message = new MessageController();
        foreach ($request->id as $value)
        {
            try
            {
                $send_message->sendMessage($value, (str_replace('_',' ',$input['message'])), 'zcpa');
                DB::table('zcpa_message_log')->insert(
                    ['telegram_id' => $value,
                        'message' => $input['message']]
                );
            } catch (\Exception $e)
            {
                DB::table('error_log')->insert(
                    ['telegram_id' => $value,
                        'message' => $input['message'] . ' ' . $e->getMessage()]);

                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $value . ' Ошибка ' . $e->getMessage(), 'zcpa');
            }
        }

        return response()->json(['success' => true]);
    }

    function getMessageFromGrigoriev(Request $request)
    {
        $input = ['id' => $request->id, 'message' => $request->message];
        $validation = \Validator::make($input,
            [
                'id' => 'required|array',
                'message' => 'required'
            ]);

        if ($validation->fails())
        {
            return $validation->errors;
        }
        $send_message = new MessageController();
        foreach ($request->id as $value)
        {
            try
            {
                $send_message->sendMessage($value, (str_replace('_',' ',$input['message'])), 'zcpa');
            } catch (\Exception $e)
            {
                DB::table('error_log')->insert(
                    ['telegram_id' => $value,
                        'message' => $input['message'] . ' ' . $e->getMessage()]);

                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $value . ' Ошибка ' . $e->getMessage(), 'zcpa');
                $send_message->sendMessage(186873547, $value . ' Ошибка ' . $e->getMessage(), 'zcpa'); //григорьев
            }
        }

        return response()->json(['success' => true]);
    }

    function requestDispatchStatus($telegram_id)
    {
        $url = 'https://a.zcpa.ru/telegram/check?api_key=645j6nbuhb4234v2y123ggh123123jjbghrw&telegramCode=' . $telegram_id;
        $result = $this->getCurl($url);
    }

    function dailyBalanceMessage()
    {
        $abonents = Abonent::select("abonent", "telegram_id")
            ->where("group_id", "=", 6)
            ->where("active", "=", 1)
            ->whereRaw("abonent REGEXP '^[0-9]+$'")
            ->get();

        $send_message = new MessageController();
        foreach ($abonents as $value)
        {
            try
            {
                $link = 'https://a.zcpa.ru/balance/affiliate?api_key=' . env('ZCPA_API_KEY') . '&id=' . $value->abonent;
                $res = file_get_contents($link);

                $obj_res = json_decode($res);
                $text = 'Ваш баланс составляет: ';
                foreach ($obj_res->balances as $key => $item)
                {
                    $text .= round($item->balance, 2) . ' ' . $item->iso . chr(10);
                }
                $send_message->sendMessage($value->telegram_id, $text, 'zcpa');
            } catch (\Exception $e)
            {
                DB::table('error_log')->insert(
                    ['telegram_id' => $value->abonent,
                        'message' => $e->getMessage()]);

                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $value->abonent . ' Ошибка ' . $e->getMessage(), 'zcpa');
            }

        }
        return response()->json(['success' => true]);
    }

    function checkZcpaUserAccess($telegram_id)
    {
        $url = "https://a.zcpa.ru/telegram/check?api_key=" . env("ZCPA_API_KEY") . "&telegramCode=" . $telegram_id;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = json_decode(curl_exec($curl));
        curl_close($curl);
        $webmaster_array = array();
        $is_access = $content->isset;
        $webmaster_id = '';
        $webmaster_email = '';
        if($is_access != 0)
        {
            $webmaster_id = $content->data->id;
            $webmaster_email = $content->data->email;
        }

        if($is_access == 1)
        {
            $webmaster_array[] =   $is_access;
            $webmaster_array[] =  $webmaster_id;
            $webmaster_array[] =  $webmaster_email;
        }
        else
        {
            $webmaster_array[] =   $is_access;
        }
        return $webmaster_array;
    }

    function sendVerificationEmail($telegram_id, $mailToSend)
    {
        $isMail = filter_var($mailToSend, FILTER_VALIDATE_EMAIL);
        if(!$isMail)
        {
            return $text = 'Некорректная почта.';
        }
        $url = "https://a.zcpa.ru/telegram/login?api_key=" . env("ZCPA_API_KEY") . "&identity=" . $mailToSend .
            "&telegramCode=" . $telegram_id;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = json_decode(curl_exec($curl));
        curl_close($curl);
        if($content->code === 0)
        {
            $text = 'Не удалось найти пользователя с указанным email.';
        }
        else
        {
            $text = 'На ваш email, указанный в профиле zcpa.ru, выслано письмо для подтверждения.';
        }

        return $text;
    }
}

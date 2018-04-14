<?php

namespace App\Http\Controllers\Reports;

use App\Model\VtigerGo\VtigerSMS;
use App\Model\VtigerGo\VtigerSMSNow;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class InviteController extends Controller
{
    public function todayData($telegram_id)
    {
        $result_back = VtigerSMSNow::select(DB::raw("*"))
            ->where("sp_offer","=","Спина")
            ->get();

        $result_blood = VtigerSMSNow::select(DB::raw("*"))
            ->where("sp_offer","=","Кровь")
            ->get();

        $result_varicoz = VtigerSMSNow::select(DB::raw("*"))
            ->where("sp_offer","=","Варикоз")
            ->get();

        $text = 'Варикоз' . chr(10);

        foreach ($result_varicoz as $value)
        {
            $text .= $value->subject . '(' . $value->limit . ')' . $value->leads . chr(10);
        }

        $text .= chr(10) . 'Спина' . chr(10);

        foreach ($result_back as $value)
        {
            $text .= $value->subject . '(' . $value->limit . ')' . $value->leads . chr(10);
        }

        $text .= chr(10) . 'Кровь' . chr(10);

        foreach ($result_blood as $value)
        {
            $text .= $value->subject . '(' . $value->limit . ')' . $value->leads . chr(10);
        }

        return $text;
    }

    public function yesterdayData($telegram_id)
    {
        $result_back = VtigerSMS::select(DB::raw("*"))
            ->where("sp_offer","=","Спина")
            ->get();

        $result_blood = VtigerSMS::select(DB::raw("*"))
            ->where("sp_offer","=","Кровь")
            ->get();

        $result_varicoz = VtigerSMS::select(DB::raw("*"))
            ->where("sp_offer","=","Варикоз")
            ->get();

        $text = 'Варикоз' . chr(10);

        foreach ($result_varicoz as $value)
        {
            $text .= $value->subject . '(' . $value->limit . ')' . $value->leads . chr(10);
        }

        $text .= chr(10) . 'Спина' . chr(10);

        foreach ($result_back as $value)
        {
            $text .= $value->subject . '(' . $value->limit . ')' . $value->leads . chr(10);
        }

        $text .= chr(10) . 'Кровь' . chr(10);

        foreach ($result_blood as $value)
        {
            $text .= $value->subject . '(' . $value->limit . ')' . $value->leads . chr(10);
        }

        return $text;
    }
}

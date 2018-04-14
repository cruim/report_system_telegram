<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\Abonent;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class ZcpaCashController extends Controller
{
    function getCashMessageFromZcpa(Request $request)
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
                $send_message->sendMessage($value, $input['message'], 'zcpa_cash_bot');
                DB::table('zcpa_message_log')->insert(
                    ['telegram_id' => $value,
                        'message' => $input['message']]
                );
            } catch (\Exception $e)
            {
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $value . ' Не авторизован ' . $e->getMessage(), 'zcpa_cash_bot');
            }
        }
        return response()->json(['success' => true]);
    }
}

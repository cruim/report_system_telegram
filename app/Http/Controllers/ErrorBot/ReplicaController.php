<?php

namespace App\Http\Controllers\ErrorBot;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ReplicaController extends Controller
{
    function getMessageAboutRepication(Request $request)
    {
        $input = ['telegram_id'=> $request->telegram_id, 'message' => $request->text];
        $validation = \Validator::make($input,
            [
                'telegram_id' => 'required|array',
                'message' => 'required'
            ]);

        if ($validation->fails())
        {
            return $validation->errors;
        }


        $send_message = new MessageController();
        foreach ($request->telegram_id as $value)
        {
            try
            {
                $send_message->sendMessage($value, (str_replace('_', ' ', $input['message'])), 'common');
            } catch (\Exception $e)
            {
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $e->getMessage(), 'common');
            }
        }
        return response()->json(['success' => true]);
    }
}

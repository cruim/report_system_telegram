<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Model\Facebook\Facebook;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class FacebookController extends Controller
{
    function getPostsForCheck(Request $request)
    {
        try
        {
            $text = (string)$request['message']['text'];
            $chatid = $request['message']['chat']['id'];
            if ($text == '/start' || $text == 'start')
            {
                $keyboard[] = array('My telegram id');

                $reply_markup = \Telegram::replyKeyboardMarkup([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]);
                $send_message = new MessageController();
                $send_message->sendMessage($chatid, 'start', 'facebook', $reply_markup);
            } elseif ($text == 'My telegram id')
            {

                $send_message = new MessageController();
                $send_message->sendMessage($chatid, 'Your telegram id: ' . $chatid, 'facebook');
            }
        } catch (\Exception $e)
        {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => true]);

    }

    function checkNewComments()
    {
        $get_actual_post = Facebook::select("post_id", "comment_count", "telegram_id", "title")
            ->where("is_active", "=", 1)
            ->get();

        foreach ($get_actual_post as $value)
        {
            try
            {
                $last_post_comment_count = $value->comment_count;
                $post_id = $value->post_id;
                $title = $value->title;

                $url = "https://graph.facebook.com/" . $post_id . "/comments?summary=1&order=reverse_chronological&access_token=" . env("FACEBOOK_PERMANENT_TOKEN");
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
                $curl_result = curl_exec($curl);
                $answer = $curl_result;
                curl_close($curl);
                $response = json_decode($answer);
                $post_comments_count = $response->summary->total_count;
                $comment_text = $response->data[0]->message;
                $message = $title . ' Новое сообщение: ' . $comment_text;

                if ($post_comments_count > $last_post_comment_count)
                {
                    $send_message = new MessageController();
                    $send_message->sendMessage($value->telegram_id, $message, 'facebook');
                    Facebook::where("post_id", "=", $post_id)
                        ->update(["comment_count" => $post_comments_count]);
                }
            } catch (\Exception $e)
            {
                $send_message = new MessageController();
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $e->getMessage(), 'facebook');
                return response()->json(['success' => true]);
            }
        }
        return response()->json(['success' => true]);
    }

    function deleteComments()
    {

        $post_data = Facebook::select("post_id", "user_access_token", "page_access_token")
            ->where("is_active", "=", 1)
            ->where("id", "<>", 1)
            ->get();

        foreach ($post_data as $value)
        {
            try
            {
                $post_id = $value->post_id;
                $user_access_token = $value->user_access_token;
                $page_access_token = $value->page_access_token;
                $url = "https://graph.facebook.com/" . $post_id . "/comments?summary=1&order=reverse_chronological&access_token="
                    . $user_access_token;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
                $curl_result = curl_exec($curl);
                $answer = $curl_result;
                curl_close($curl);
                $response = json_decode($answer);
                if (isset($response->error))
                {
                    Facebook::where("post_id", "=", $value->post_id)
                        ->update(['is_active' => 0]);
                }
                $total_count = $response->summary->total_count;
            } catch (\Exception $e)
            {
                return response()->json(['success' => true]);
            }
            try
            {
                if ($total_count > 0)
                {
                    $comment_id = $response->data[0]->id;
                    $delete_comment_url = "https://graph.facebook.com/" . $comment_id . "?method=delete&access_token=" . $page_access_token;
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $delete_comment_url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
                    curl_exec($curl);
                    curl_close($curl);
                }
            } catch (\Exception $e)
            {
                return response()->json(['success' => true]);
            }

        }
        return response()->json(['success' => true]);
    }
}

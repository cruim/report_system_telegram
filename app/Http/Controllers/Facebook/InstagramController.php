<?php

namespace App\Http\Controllers\Facebook;

use App\Http\Controllers\MessageController;
use App\Model\Facebook\Instagram;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class InstagramController extends Controller
{
    function checkNewMessage()
    {
        $check_data = Instagram::select("comment_count", "post_label", "url", "telegram_id", "id")
            ->where("is_active", "=", 1)
            ->get();

        foreach ($check_data as $value)
        {
            try
            {

                $url = $value->url . "?__a=1";
                $current_comment_count = $this->getCurlData($url);

                if ($current_comment_count > $value->comment_count)
                {
                    Instagram::where("id", "=", $value->id)
                        ->update(["comment_count" => $current_comment_count]);

                    $text = 'К посту ' . $value->post_label . ' оставлен(ы) новые комментарии';
                    $send_message = new MessageController();
                    $send_message->sendMessage($value->telegram_id, $text, 'facebook');
                }
            } catch (\Exception $e)
            {
//                Instagram::where("id", "=", $value->id)
//                    ->update(["is_active" => 0]);
                $send_message = new MessageController();
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), 'insta: ' . $e->getMessage(), 'common');
            }
        }

    }

    function getCurlData($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        $result = \Response::json($result);
        $data = $result->getData();
        $data = json_decode($data);
        return $data->graphql->shortcode_media->edge_media_to_comment->count;
    }
}

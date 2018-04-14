<?php

namespace App\Http\Controllers\AdminControllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ConfigController extends Controller
{
    public function getConfig()
    {
        return [

            'default' => 'common',


            'bots' => [
                'common' => [
                    'username' => 'MyTelegramBot',
                    'token' => env('TELEGRAM_BOT_TOKEN'),
                    'commands' => [
//                Acme\Project\Commands\MyTelegramBot\BotCommand::class
                    ],
                ],
                'manager' => [
                    'username' => 'MySecondBot',
                    'token' => env('TELEGRAM_MANAGER_BOT_TOKEN'),
                ],
                'zcpa' => [
                    'username' => 'MyThirdBot',
                    'token' => env('TELEGRAM_ZCPA_BOT_TOKEN'),
                ],
                'marketing' => [
                    'username' => 'MyForthBot',
                    'token' => env('TELEGRAM_MARKETING_BOT_TOKEN')
                ],
                'zcpa_cash_bot' => [
                    'username' => 'MyFifthBot',
                    'token' => env('TELEGRAM_CASH_BOT_TOKEN')
                ],
                'alpha' => [
                    'username' => 'MySixthBot',
                    'token' => env('TELEGRAM_ALPHA_BOT_TOKEN')
                ],
                'facebook' => [
                    'username' => 'Facebook',
                    'token' => env('TELEGRAM_FACEBOOK_TOKEN')
                ],
                'support' => [
                    'username' => 'Support',
                    'token' => env('TELEGRAM_SUPPORT_BOT_TOKEN')
                ],
                'demo' => [
                    'username' => 'Demo',
                    'token' => env('TELEGRAM_DEMO_BOT_TOKEN')
                ],
                'test' =>[
                    'username' => 'TestBot',
                    'token' => env('TELEGRAM_TEST_BOT_TOKEN')
                ],
                'zcpa_dir' =>[
                    'username'=> 'ZcpaDir',
                    'token' => env('TELEGRAM_ZCPA_DIR_BOT_TOKEN')
                ]
            ],];
    }
}

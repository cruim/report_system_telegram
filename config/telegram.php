<?php

return [

    'default' => 'common',


    'bots' => [
        'common' => [
            'username'  => 'MyTelegramBot',
            'token' => env('TELEGRAM_BOT_TOKEN'),
            'commands' => [
//                Acme\Project\Commands\MyTelegramBot\BotCommand::class
            ],
        ],
        'second' => [
            'username'  => 'MySecondBot',
            'token' => env('TELEGRAM_MANAGER_BOT_TOKEN'),
        ],
    ],
//    'bot_tokens' =>
//        [
//            '437198743:AAEr3Jrs_tYGfM74VOqM7ALNvwe574p1hNY', //zdorov_head_bot
//            '351137725:AAEPfgSWa_WKe86bC_NtlRJ0FktFXisvb4Q'  //zdorov_managers_bot
//        ],

    /*
    |--------------------------------------------------------------------------
    | Asynchronous Requests [Optional]
    |--------------------------------------------------------------------------
    |
    | When set to True, All the requests would be made non-blocking (Async).
    |
    | Default: false
    | Possible Values: (Boolean) "true" OR "false"
    |
    */
    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Handler [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use a custom HTTP Client Handler.
    | Should be an instance of \Telegram\Bot\HttpClients\HttpClientInterface
    |
    | Default: GuzzlePHP
    |
    */
    'http_client_handler' => null,

    /*
    |--------------------------------------------------------------------------
    | Register Telegram Commands [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use the SDK's built in command handler system,
    | You can register all the commands here.
    |
    | The command class should extend the \Telegram\Bot\Commands\Command class.
    |
    | Default: The SDK registers, a help command which when a user sends /help
    | will respond with a list of available commands and description.
    |
    */
    'commands' => [
        Telegram\Bot\Commands\HelpCommand::class,
    ],
];

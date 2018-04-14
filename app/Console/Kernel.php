<?php

namespace App\Console;

use App\Http\Controllers\AdminControllers\SchedullerController;
use App\Http\Controllers\CPA\EventStandingsController;
use App\Http\Controllers\ErrorBot\SdekErrorController;
use App\Http\Controllers\Facebook\FacebookController;
use App\Http\Controllers\Facebook\InstagramController;
use App\Http\Controllers\Reports\CallCenterController;
use App\Http\Controllers\Reports\ChinilovDataController;
use App\Http\Controllers\Reports\ZcpaController;
use App\Http\Controllers\Reports\ZcpaWebPenaltyController;
use App\Http\Controllers\Webhooks\DemoBotController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function ()
        {
            $telegram = new SchedullerController();
            $telegram->checkSchedullerTask();
        })->everyMinute();
        $schedule->call(function ()
        {
            $demo = new DemoBotController();
            $demo->getSchedullerTasks();
        })->everyMinute();
        $schedule->call(function ()
        {
            $call_center_head_info_last_month = new CallCenterController();
            $call_center_head_info_last_month->dailyInsertHeadInfoLastMonth();
        })->dailyAt('04:00');
        $schedule->call(function ()
        {
            $call_center_head_info_last_month = new CallCenterController();
            $call_center_head_info_last_month->dailyInsertHeadInfoCurrentMonth();
        })->dailyAt('04:05');
//        $schedule->call(function ()
//        {
//            $hour = date('H');
//            if ($hour <= 23 && $hour >= 8)
//            {
//                $error_bot = new SdekErrorController();
//                $error_bot->Index();
//            }
//        })->everyThirtyMinutes();
        $schedule->call(function ()
        {
            $penalty_webs = new ZcpaWebPenaltyController();
            $penalty_webs->getPenaltyData();
        })->weekdays()->at('21:00');
        $schedule->call(function ()
        {
            $penalty_webs = new ZcpaWebPenaltyController();
            $penalty_webs->sendMessageToWebmaster();
        })->weekdays()->at('18:00');
//        $schedule->call(function ()
//        {
//            $dailyBalanceMessage = new ZcpaController();
//            $dailyBalanceMessage->dailyBalanceMessage();
//        })->dailyAt('23:00');
        $schedule->call(function ()
        {
            $facebook = new FacebookController();
            return $facebook->deleteComments();
        })->everyMinute();
        $schedule->call(function ()
        {
            $instagram = new InstagramController();
            return $instagram->checkNewMessage();
        })->everyMinute();
        $schedule->call(function ()
        {
            $chinilov_yesterday = new ChinilovDataController();
            $chinilov_yesterday->yesterdayData();
        })->dailyAt('10:00');
        $schedule->call(function ()
        {
            $chinilov_today = new ChinilovDataController();
            $chinilov_today->todayData();
        })->dailyAt('21:00');
    }
}

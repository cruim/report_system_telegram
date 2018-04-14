<?php

namespace App\Http\Controllers\ErrorBot;


use App\Http\Controllers\ApiController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Reports\TigerApiController;
use App\Model\VTiger;
use App\Model\VTiger\VTUser;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DateTime;

class SdekErrorController extends Controller
{
    public function Index()
    {
        $send_message = new MessageController();
        $messages = $this->CheckDeliveryService();
        if (count($messages) == 0)
        {
            return;
        }

        foreach ($messages as $value)
        {
            try
            {
                $send_message->sendMessage($value['abonent'], (string)$value['message'], 'manager');
            } catch (\Exception $e)
            {
                $warning_message = $value['name'] . ' не авторизован(а) в боте!';
                $send_message->sendMessage(env('TELEGRAM_ADMIN_ID'), $warning_message, 'manager');
                $send_message->sendMessage('304074342', $warning_message, 'manager'); //осипов
            }
        }
        $this->CheckDistictTown();

        return response()->json(['success' => true]);
    }

    public function CheckDeliveryService()
    {
        $data = VTiger\CrmData::select('order_id',
            'order_custom_num',
            'first_name',
            'last_name',
            'stager',
            'telegram_id',
            'department',
            'subdivision',
            'group',
            'area',
            'ship_city',
            'modifiedtime',
            'sp_delivery_date',
            'sp_delivery_time',
            'validation_status')
            ->where('delivery_service', '=', 'СДЭК Москва')
            ->where('order_status', '=', 'Отправлять')
            ->whereIn('validation_status', [0, 1, 2])
            ->get();

        $districts = VTiger\District::where('mkad', '=', '1')->lists('name');
        $messages = [];
        $orders = [];
        foreach ($data as $row)
        {
            $text = '';
            $inc = 0;
            $validation = '';
            foreach ($districts as $district)
            {
                if (strcasecmp($district, $row->area) >= -1 && strcasecmp($district, $row->area) <= 1)
                {
                    $inc = 1;
                }
            }

            if ($inc == 1)
            {
                $date = new DateTime($row->modifiedtime);
                $date = $date->format('Y-m-d');
                $weekDayDelivery = strftime("%w", strtotime($row->sp_delivery_date));
                $datetime1 = new DateTime($date);
                $datetime2 = new DateTime($row->sp_delivery_date);
                $inter = $datetime1->diff($datetime2);
                $interval = $inter->format('%a');
                if ($interval >= 2 && $interval <= 7)
                {
                    if ($weekDayDelivery == 0 || ($weekDayDelivery == 6 && $row->sp_delivery_time == 'с 18:00 до 21:00'))
                    {
                        $text .= 'Некорректная дата и время доставки!(Указано воскресенье или суббота после 18:00)' . chr(10);
                    }
                } else
                {
                    $text .= 'Некорректная дата и время доставки!(Доставку можно назначать не ранее чем через 1 день после даты оформления)' . chr(10);
                }

            } else
            {
                if ($row->area == '')
                {
                    $text .= 'Не заполнено поле район!' . chr(10);
                }
                if ($row->sp_delivery_time !== '' || $row->sp_delivery_date !== '')
                {
                    $text .= 'Поля дата и время доставки заполнены не верно! Или в поле район допущена ошибка!' . chr(10);
                }
            }
            if ($text !== '')
            {
                $senior = VTUser::where('user_department_group', '=', $row->group)
                    ->where('department', '=', $row->department)
                    ->where('title', '=', 'Старший группы')
                    ->lists('telegram_id');
                if (count($senior) < 1)
                {
                    $senior[0] = '304074342'; //осипов
                }
                if ($row->telegram_id == '')
                {
                    $row->telegram_id = $senior[0];
                }

                if ($row->validation_status < 2)
                {
                    $messages[] = [
                        'message' => 'Вы допустили ошибку в заказе ' . $row->order_custom_num
                            . ' по следующим пунктам:' . $text,
                        'abonent' => $row->telegram_id,
                        'name' => $row->first_name . ' ' . $row->last_name
                    ];
                    $messages[] = [
                        'message' => $row->first_name . ' ' . $row->last_name . ' допустил ошибку в заказе ' . $row->order_custom_num
                            . ' по следующим пунктам:' . $text,
                        'abonent' => $senior[0],
                        'name' => $row->first_name . ' ' . $row->last_name
                    ];
                    if ($row->department !== 'Аутсорс' and $row->department !== 'УКР')
                    {
                        $messages[] = [
                            'message' => $row->first_name . ' ' . $row->last_name . ' допустил ошибку в заказе ' . $row->order_custom_num
                                . ' по следующим пунктам:' . $text,
                            'abonent' => '304074342', //осипов
                            'name' => $row->first_name . ' ' . $row->last_name
                        ];
                        $messages[] = [
                            'message' => $row->first_name . ' ' . $row->last_name . ' допустил ошибку в заказе ' . $row->order_custom_num
                                . ' по следующим пунктам:' . $text,
                            'abonent' => '348169607', //admin
                            'name' => $row->first_name . ' ' . $row->last_name
                        ];
                    }
                    elseif ($row->department == 'Аутсорс' or $row->department == 'УКР')
                    {
                        $messages[] = [
                            'message' => $row->first_name . ' ' . $row->last_name . ' допустил ошибку в заказе ' . $row->order_custom_num
                                . ' по следующим пунктам:' . $text,
                            'abonent' => '315139134', //запутряев
                            'name' => $row->first_name . ' ' . $row->last_name
                        ];
                        $messages[] = [
                            'message' => $row->first_name . ' ' . $row->last_name . ' допустил ошибку в заказе ' . $row->order_custom_num
                                . ' по следующим пунктам:' . $text,
                            'abonent' => '348169607', //admin
                            'name' => $row->first_name . ' ' . $row->last_name
                        ];
                    }
                } else
                {
                    $messages[] = [
                        'message' => 'Заказ ' . $row->order_custom_num
                            . ' передан менеджеру Казино',
                        'abonent' => $senior[0],
                        'name' => $row->first_name . ' ' . $row->last_name
                    ];
                    $messages[] = [
                        'message' => 'Заказ ' . $row->order_custom_num
                            . ' передан менеджеру Казино',
                        'abonent' => '304074342', //осипов
                        'name' => $row->first_name . ' ' . $row->last_name
                    ];
                }

                if ($row->stager != 1 && $row->department != 'Аутсорс')
                {
                    $validation = (int)$row->validation_status + 1;
                }
                else
                {
                    $validation = 4;
                }
            }

            $messages[] = ['order' => $row->order_id,
                'status' => $validation];
        }

        $TigerApiController = new TigerApiController();
        $TigerApiController->SendValidationStatus($orders);

        return $orders;
    }


    public function CheckDistictTown()
    {
        $districts = VTiger\District::where('mkad', '=', '0')->lists('name');
        $data = VTiger\CrmData::select('order_id',
            'area',
            'ship_city')
            ->where('delivery_service', '=', 'СДЭК Москва')
            ->where('order_status', '=', 'Отправлять')
            ->whereIn('area', $districts)
            ->get();
        if (count($data) < 1)
        {
            return;
        }

        $TigerApiController = new TigerApiController;
        $TigerApiController->SendChipCity($data);
        return;
    }
}

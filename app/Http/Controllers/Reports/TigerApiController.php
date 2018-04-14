<?php

namespace App\Http\Controllers\Reports;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\vtwsclib\Vtiger\Vtiger_WSClient;


class TigerApiController extends Controller
{
    public function Index()
    {
        $orders[] = ['order' => '1599128', 'status' => 1];
        $orders[] = ['order' => '1599133', 'status' => 1];
        $orders[] = ['order' => '1599134', 'status' => 1];
        $orders[] = ['order' => '1599136', 'status' => 1];

        $this->SendValidationStatus($orders);
    }

    public function SendValidationStatus($orders)
    {
        $login = "controller@crm.zdorov.top";
        $passw = "nioFn5UnkFBKL7y3";
        $vtigerConnector = new Vtiger_WSClient('http://crm.zdorov.top/webservice.php');
        $vtigerConnector->doLogin($login, $passw);

        foreach ($orders as $order)
        {
            $parametr = [];
            $parametr['salesorderid'] = $order['order'];
            $parametr['validation_status'] = $order['status'];
            if ($order['status'] == 3)
            {
                $parametr['assigned_user_id'] = "19x137";
            }
            file_put_contents("update1.txt", print_r($parametr, 1), FILE_APPEND);
            $request = array(
                "elementType" => "SalesOrder",
                "element" => $vtigerConnector->toJSONString($parametr),
            );
            $response_update = $vtigerConnector->doInvoke('updateOrder', $request);
            file_put_contents("update1.txt", print_r($response_update, 1), FILE_APPEND);
        }
        $res = $vtigerConnector->doInvoke('logout');
    }


    public function SendChipCity($orders)
    {
        $login = "controller@crm.zdorov.top";
        $passw = "nioFn5UnkFBKL7y3";
        $vtigerConnector = new Vtiger_WSClient('http://crm.zdorov.top/webservice.php');
        $vtigerConnector->doLogin($login, $passw);
        foreach ($orders as $order)
        {
            $parametr = [];
            $parametr['salesorderid'] = $order['order_id'];
            $parametr['ship_city'] = $order['area'];
            $request = array(
                "elementType" => "SalesOrder",
                "element" => $vtigerConnector->toJSONString($parametr),
            );
            $response_update = $vtigerConnector->doInvoke('updateOrder', $request);
        }
        $res = $vtigerConnector->doInvoke('logout');
    }
}

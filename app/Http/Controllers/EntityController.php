<?php

namespace App\Http\Controllers;

use App\Model\ReportParameters;
use Illuminate\Http\Request;

use App\Http\Requests;

class EntityController extends Controller
{
    function getReportControllerName($text)
    {
        $controller_name = ReportParameters::select("controller")
            ->where("parameters", "=", "$text")
            ->get();
        if (count($controller_name) == 0)
        {
            return response()->json(['success' => true]);
        } else
        {
            $controller_name = $controller_name[0]->controller;
        }

        return $controller_name;
    }

    function getReportMethodName($text)
    {
        $method_name = ReportParameters::select("method")
            ->where("parameters", "=", "$text")
            ->get();
        if (count($method_name) == 0)
        {
            return response()->json(['success' => true]);
        } else
        {
            $method_name = $method_name[0]->method;
        }

        return $method_name;
    }
}

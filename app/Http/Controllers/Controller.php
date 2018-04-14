<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $ErrorMessage = 'Oops! Something went wrong!';
    
    public function getCurl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        $curl_result = curl_exec($curl);
        $answer = $curl_result;
        if($answer != false){$response = json_decode($answer);}
        else{$response = ($answer);}
        curl_close($curl);
        return ($response);
    }
    
    public function getRangeDates($date_from, $date_to)
    {
        $from = new DateTime($date_from);
        $to   = new DateTime($date_to);
        $to->modify("1 day");

        $period = new DatePeriod($from, new DateInterval('P1D'), $to);

        $arrayOfDates = array_map(
            function($item){return $item->format('Y-m-d');},
            iterator_to_array($period)
        );
        
        return $arrayOfDates;
    }
}


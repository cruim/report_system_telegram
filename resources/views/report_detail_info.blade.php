@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')
        {{--<div class="container-fluid col-xs-12 col-md-10 col-md-offset-1 gray-div">--}}
        <a href="/reports/"><button class="btn btn-warning" id='back'>Назад <i class="glyphicon glyphicon-chevron-left"></i></button></a>
        <form method="get" action="/detail_report">
            <h3>{{$report_name[0]->telegram_name}}</h3>

            <div class="overflow">
                <div class="abonent-to-report">
                    <table id='abonent_table' class="table table-bordered table-hover table-responsive tables">

                        <thead>
                        <th>Report ID</th>
                        <th>Отчет</th>
                        <th>Абонент</th>
                        <th>Активность</th>
                        </thead>
                        <tbody>

                        @foreach($reports as $value)


                            <tr>
                                <td class="report_id">{{$value->report_id}}</td>
                                <td class="sms_name">{{$value->telegram_name}}</td>
                                <td class="abonent">{{$value->abonent}}</td>
                                <td class="active">
                                    <select class='form-control' id='active'>
                                        @foreach($is_user_active as $val)
                                            @if ($val->active == $value->active)
                                                <option selected  value='{{$val->active}}'>{{$val->active}}</option>
                                            @else
                                                <option value='{{$val->active}}'>{{$val->active}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
</div>
{{--</div>--}}
<link rel="stylesheet" href="{{asset('css/abonent.css')}}">
<script type='text/javascript' src="{{asset('js/report.js')}}"></script>
@endsection
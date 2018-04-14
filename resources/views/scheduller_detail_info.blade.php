@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')
        <a href="/scheduller/"><button class="btn btn-warning" id='back'>Назад <i class="glyphicon glyphicon-chevron-left"></i></button></a>
        <form method="get" action="/detail_report">
            <h3 id="report_name" >{{$report_name[0]->telegram_name}}</h3>
            <div class="menu-el">
                <select class='form-control' id='abonent'>
                    @foreach($abonent as $value)
                        <option value='{{$value->abonent}}'>{{$value->abonent}}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" id='add_scheduller_task'>Добавить <i class=" glyphicon glyphicon-calendar"></i></button>
            </div>
            <div class="overflow">
                <div class="scheduller">
                    <table id='scheduller_table' class="table table-bordered table-hover table-responsive tables">

                        <thead>
                        <th>Scheduller ID</th>
                        <th>Абонент</th>
                        <th>Время рассылки</th>
                        <th>Bot ID</th>
                        <th>Активность</th>
                        </thead>
                        <tbody>

                        @foreach($scheduller as $value)
                            <tr>
                                <td class="id">{{$value->id}}</td>
                                <td class="abonent">{{$value->abonent}}</td>
                                <td class="sending_time"><input type="text" class="form-control"id="sending_time"
                                                                value="{{($value->sending_time)}}"></td>
                                <td class="bot_id">
                                    <select class='form-control' id='active'>
                                        @foreach($bots_id as $val)
                                            @if ($val->id == $value->bot_id)
                                                <option selected  value='{{$val->id}}'>{{$val->id}}</option>
                                            @else
                                                <option value='{{$val->id}}'>{{$val->id}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
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
<link rel="stylesheet" href="{{asset('css/scheduller.css')}}">
<script type='text/javascript' src="{{asset('js/scheduller.js')}}"></script>
@endsection
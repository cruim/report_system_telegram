@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')
        <a href="/bots/"><button class="btn btn-warning" id='back'>Назад <i class="glyphicon glyphicon-chevron-left"></i></button></a>
        <form method="get" action="/detail_report">
            <h3>{{$bot_name[0]->name}}</h3>

            <div class="overflow">
                <div class="bot-to-abonent">
                    <table id='abonent_table' class="table table-bordered table-hover table-responsive tables">

                        <thead>
                        <th>Bot ID</th>
                        <th>Бот</th>
                        <th>Абонент</th>
                        <th>Активность</th>
                        </thead>
                        <tbody>

                        @foreach($bot as $value)


                            <tr>
                                <td class="bot_id">{{$value->bot_id}}</td>
                                <td class="name">{{$value->name}}</td>
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
<link rel="stylesheet" href="{{asset('css/abonent.css')}}">
<script type='text/javascript' src="{{asset('js/bot.js')}}"></script>
@endsection
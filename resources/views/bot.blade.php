@extends('layouts.app')
<div class='menu form-inline'>
@section('content')
        <div class="menu-el">
            <input type="text" id="name" class="form-control" placeholder="Имя">
            <input type="text" id="telegram_token" class="form-control" placeholder="Телеграм Токен">
            <button class="btn btn-primary" id='create_bot'>Создать <i class=" glyphicon glyphicon-tower"></i></button>
        </div>
        <div class="overflow">
            <div class="bot">
                <table id='bot_table' class="table table-bordered table-hover table-responsive tables">

                    <thead>
                    <th>ID</th>
                    <th>Бот</th>
                    <th>Токен</th>
                    <th>Активен</th>
                    <th>Бот <span class="glyphicon glyphicon-arrow-right"></span> Отчет</th>
                    <th>Бот <span class="glyphicon glyphicon-arrow-right"></span> Абонент</th>
                    </thead>
                    <tbody>

                    @foreach($result as $value)


                        <tr>
                            <td class="id">{{$value->id}}</td>
                            <td class="name">{{$value->name}}</td>
                            <td class="telegram_token">{{$value->telegram_token}}</td>
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
                            <td>
                                <form method="GET" action="detail_bot/{{$value->id}}">
                                    <input type="submit" class="btn btn-success form-control" value="Изменить">
                                </form>
                            </td>
                            <td>
                                <form method="GET" action="detail_bot2abonent/{{$value->id}}">
                                    <input type="submit" class="btn btn-info form-control" value="Изменить">
                                </form>
                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<link rel="stylesheet" href="{{asset('css/bot.css')}}">
<script type='text/javascript' src="{{asset('js/bot.js')}}"></script>
@endsection
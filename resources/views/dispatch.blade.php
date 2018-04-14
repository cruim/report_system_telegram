@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')


        <div class="overflow">
            <div class="main">
                <div class="menu-el  form-inline">
                    <p>
                        Рассылка сообщений
                    </p>
                    <select multiple id="department" class="form-control" title="Отдел">
                        @foreach($department as $value)
                            <option>{{$value->group_name}}</option>
                        @endforeach
                    </select>
                    <select multiple id="abonent" class="form-control" title="Абоненты">
                        @foreach($abonent as $value)
                            <option>{{$value->abonent}}</option>
                        @endforeach
                    </select>
                    <select id="bot" class="form-control" title="Bot">
                        @foreach($bot as $value)
                            <option>{{$value->name}}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-info" id='sender'>Отправить <i class="glyphicon glyphicon-send"></i></button>
                </div>
                </div>

        </div>
        <div class="form-group">
            <textarea class="form-control" rows="5" id="text_for_abonents" placeholder="Сообщение..." style="min-width: 400px"></textarea>
        </div>
        </div>

{{--<link rel="stylesheet" href="{{asset('css/abonent.css')}}">--}}
<script type='text/javascript' src="{{asset('js/dispatch.js')}}"></script>
@endsection

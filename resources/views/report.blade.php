@extends('layouts.app')
<div class='menu form-inline'>
@section('content')
    <div class='menu form-inline'>
        <div class="menu-el">
            <input type="text" id="search" class="form-control" placeholder="Поиск">
        </div>
        <div class="menu-el">
            <input type="text" id="telegram_name" class="form-control" placeholder="Название">
            <button class="btn btn-primary" id="create_report">Создать <i class=" glyphicon glyphicon-user"></i></button>
        </div>
    </div>


    <div class="overflow">
        <div class="main">
            <table id='report_table' class="table table-bordered table-hover table-responsive tables">

                <thead>
                <th>ID</th>
                <th>Название отчета</th>
                <th>Активен</th>
                <th class="update-th">Отчет <span class="glyphicon glyphicon-arrow-right"></span> Абонент</th>
                </thead>
                <tbody>

                @foreach($user_info as $value)


                    <tr>
                        <td class="id">{{$value->id}}</td>
                        <td class="abonent">{{$value->telegram_name}}</td>
                        <td class="report_active">
                            <select class='form-control' id='active'>
                                @foreach($is_user_active as $val)
                                    @if ($val->active == $value->report_active)
                                        <option selected  value='{{$val->active}}'>{{$val->active}}</option>
                                    @else
                                        <option value='{{$val->active}}'>{{$val->active}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <form method="GET" action="detail_report/{{$value->id}}">
                                <input type="submit" class="btn btn-success form-control" value="Изменить">
                            </form>

                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>


    <script type='text/javascript' src="{{asset('js/report.js')}}"></script>
    <link rel="stylesheet" href="{{asset('css/report.css')}}">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.19.1/vis.min.js"></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/vis/4.19.1/vis.min.css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>

    <script>
        $(document).ready(function(){
            $("#search").keyup(function(){
                _this = this;

                $.each($(".table tbody tr"), function() {
                    if($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }});
            });
        });
    </script>
@endsection

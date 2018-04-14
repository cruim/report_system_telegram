@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')
        <div class="menu-el">
            <input type="text" id="search" class="form-control" placeholder="Поиск">
        </div>
        <div class="menu-el">
            <input type="text" id="abonent" class="form-control" placeholder="Фамилия">
            <input type="text" id="first_name" class="form-control" placeholder="Имя">
            <input type="text" id="telegram_id" class="form-control" placeholder="Телеграм ID">
            <input type="text" id="tiger_id" class="form-control" placeholder="Tiger ID">
            <select class='form-control' id='department'>
                @foreach($department as $value)
                    <option value='{{$value->group_name}}'>{{$value->group_name}}</option>
                @endforeach
            </select>
            <button class="btn btn-primary" id='create_abonent'>Создать <i class=" glyphicon glyphicon-user"></i>
            </button>
        </div>
        <div class="overflow">
            <div class="main">
                <table id='abonent_table' class="table table-bordered table-hover table-responsive tables">

                    <thead>
                    <th>ID</th>
                    <th>Абонент</th>
                    <th>Имя</th>
                    <th>Телеграм ID</th>
                    <th>Подразделение</th>
                    <th>Активен</th>
                    <th>Абонент <span class="glyphicon glyphicon-arrow-right"></span> Отчет</th>
                    </thead>
                    <tbody>

                    @foreach($user_info as $value)


                        <tr id="user-id" data-user="{{$value->first_name}}">
                            <td class="id">{{$value->id}}</td>
                            <td class="abonent">{{$value->abonent}}</td>
                            <td class="first_name">{{$value->first_name}}</td>
                            <td class="telegram_id">{{$value->telegram_id}}</td>
                            <td class="group-name">{{$value->group_name}}</td>
                            <td class="active">
                                <select class='form-control' id='active'>
                                    @foreach($is_user_active as $val)
                                        @if ($val->active == $value->active)
                                            <option selected value='{{$val->active}}'>{{$val->active}}</option>
                                        @else
                                            <option value='{{$val->active}}'>{{$val->active}}</option>
                                        @endif
                                    @endforeach
                                </select></td>
                            <td>
                                <form method="GET" action="detail_abonent/{{$value->id}}">
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
<link rel="stylesheet" href="{{asset('css/abonent.css')}}">
<script type='text/javascript' src="{{asset('js/abonent.js')}}"></script>
<script type='text/javascript'>
    $('#maintable').DataTable({
        info: false,
        scrollY: 800,
        scrollCollapse: true,
        paging: false,
    });
</script>
<script>
    $(document).ready(function () {
        $("#search").keyup(function () {
            _this = this;

            $.each($(".table tbody tr"), function () {
                if ($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });
    });
</script>
@endsection

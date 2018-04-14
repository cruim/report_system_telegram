@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')
        <div class="menu-el">
            <input type="text" id="search" class="form-control" placeholder="Поиск">
        </div>
        <div class="overflow">
            <div class="vt_users">
                <table id='vt_table' class="table table-bordered table-hover table-responsive tables">

                    <thead>
                    <th>ID</th>
                    <th>Пользователь</th>
                    </thead>
                    <tbody>

                    @foreach($users as $value)
                        <tr>
                            <td class="id">{{$value->id}}</td>
                            <td class="name">{{$value->full_name}}</td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</div>
<link rel="stylesheet" href="{{asset('css/abonent.css')}}">
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
@extends('layouts.app')
<div class='menu form-inline'>
    @section('content')


        <div class="overflow">
            <div class="main">
                <table id='report_table' class="table table-bordered table-hover table-responsive tables">

                    <thead>
                    <th>ID</th>
                    <th>Название отчета</th>
                    <th class="update-th">Расписание <span class="glyphicon glyphicon-dashboard"></span></th>
                    </thead>
                    <tbody>

                    @foreach($scheduller as $value)


                        <tr>
                            <td class="id">{{$value->id}}</td>
                            <td class="abonent">{{$value->telegram_name}}</td>
                            <td>
                                <form method="GET" action="detail_scheduller_report/{{$value->id}}">
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
@endsection

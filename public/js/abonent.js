$(document).ready(function () {
    // getAbonent();

    $(document).on('change','.main td select',function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateData(element);
    })

    $(document).on('click','#create_abonent',function (e)
    {
        e.preventDefault();
        var element = $(this);
        createAbonent(element);
    })

    $(document).on('click','#update_abonent', function(e)
    {
        e.preventDefault();
        var element = $(this);
        getDetailAboutAbonent(element);
    })

    $(document).on('change','.report-to-abonent td select', function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateReportToAbonent(element);
    })
});



function getAbonent()
{
    $.ajax({
        url: '/abonent_data',
        type: "GET",
        dataType: "json",
        data:{},
        success: function (response) {
            setAbonent(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        }
    });
}

function setAbonent(data)
{
    $('.main').empty();
    $('.main').append('<table class="table table-bordered table-hover"><thead></thead><tbody></tbody></table>');
    var Thead = '.main table thead';
    var Tbody = '.main table tbody';
    var inc = 0;
    $(Thead).last().append('<tr></tr>');
    data.forEach(function (item, i, data) {
        $(Tbody).last().append('<tr></tr>');
        for (var key in item) {
            if (inc == 0) {
                $(Thead + ' >tr').last().append('<th class="' + key + '">' + key + '</th>');
            }
            if(key == 'active')
            {
                $(Tbody + ' >tr').last().append('<td class="' + key + '"><input value="' + item[key] + '" class="form-control"></td>');
            }
            else
            {
                $(Tbody + ' >tr').last().append('<td class="' + key + '">' + item[key] + '</td>');
            }


        }
            $(Tbody + ' >tr').last().append('<td id="btn-event"><button id="update_abonent" class="event-b btn btn-info"><i class=" glyphicon glyphicon-edit"></i></button></td>');
        inc++;
    }
    );
    $(Thead + ' >tr').last().append('<th class="active">Update</th>');
    formatTable();
}

function formatTable() {
    var labels = {
        'abonent': 'Абонент',
        'telegram_id': 'Телеграм ID',
        'group_name': 'Подразделение',
        'active': 'Активен',
        'Update': 'Редактировать',
        'id': 'ID'
    };
    $('.main table th').each(function () {
        $(this).text($(this).text().split($(this).text()).join(labels[$(this).text()])); // Замена лейблов на таблице.
    });
}

function updateData(element){
    // var el = document.getElementById('user-id');
    // user = el.dataset.user;
    var request =
    {

        'user':$(element).parent().parent().data('user'),
        'telegram_id':$(element).parent().parent().children('.telegram_id').text(),
        'id':$(element).parent().parent().children('.id').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_abonent',
        type: "GET",
        dataType: "json",
        data:{
            request: request,
            token: $('#token').val()

        },
        success: function (response) {
            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        }
    });
    console.log(request);
}

function createAbonent(element)
{
    var abonent = $('#abonent').val();
    var telegram_id = $('#telegram_id').val();
    var tiger_id = $('#tiger_id').val();
    var group_name = $('#department').val();
    var first_name = $('#first_name').val();
    if(tiger_id == '' || abonent == '' || telegram_id == ''){alert('Не заполнено одно из обязательных полей!');}
    else {
    var request =
    {
        'abonent':abonent,
        'telegram_id':telegram_id,
        'tiger_id':tiger_id,
        'group_name':group_name,
        'first_name':first_name


    };
    $.ajax({
        url: '/create_abonent',
        type: "GET",
        dataType: "json",
        data:{
            request: request,
            token: $('#token').val()

        },
        success: function (response) {
            console.log(response);
            $('#fountainG').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            $('#fountainG').hide();
        }
    });}
}

function getDetailAboutAbonent(element)
{
    var request =
    {
        'id':$(element).parent().parent().children('.id').text(),
    };
    $.ajax({
        url: '/detail_abonent',
        type: "GET",
        dataType: "json",
        data:{
            request: request,
            token: $('#token').val()

        },
        success: function (response) {
            console.log(response);
            $('#fountainG').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            $('#fountainG').hide();
        }
    });
}

function updateReportToAbonent(element){
    var request =
    {
        'abonent_id':$(element).parent().parent().children('.abonent_id').text(),
        'sms_name':$(element).parent().parent().children('.sms_name').text(),
        'report_id':$(element).parent().parent().children('.report_id').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_report2abonent',
        type: "GET",
        dataType: "json",
        data:{
            request: request,
        },
        success: function (response) {
            console.log(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        }
    });
}

$('.input-daterange input').each(function() {
    $(this).datepicker({
        format: 'yyyy-mm-dd',
        language: 'ru'
    });
});

$('.clockpicker').clockpicker({
    donetext: 'click',
    autoclose: true
    // placement: 'bottom',
    // align: 'left',
    // autoclose: true,
    // 'default': 'now'
});

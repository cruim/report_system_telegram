$(document).ready(function () {
    // getReport();

    $(document).on('change','.main td select',function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateData(element);
    })

    $(document).on('click','#create_report',function (e)
    {
        e.preventDefault();
        var element = $(this);
        createReport(element);
    })

    $(document).on('change','.abonent-to-report td select', function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateAbonentToReport(element);
    })
});

function getReport()
{
    $.ajax({
        url: '/report_data',
        type: "GET",
        dataType: "json",
        data:{},
        success: function (response) {
            setReport(response);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        }
    });
}

function setReport(data)
{
    $('.report').empty();
    $('.report').append('<table class="table table-bordered table-hover"><thead></thead><tbody></tbody></table>');
    var Thead = '.report table thead';
    var Tbody = '.report table tbody';
    var inc = 0;
    $(Thead).last().append('<tr></tr>');
    data.forEach(function (item, i, data) {
        $(Tbody).last().append('<tr></tr>');
        for (var key in item) {
            if (inc == 0) {
                $(Thead + ' >tr').last().append('<th class="' + key + '">' + key + '</th>');
            }
            if(key == 'report_active')
            {
                $(Tbody + ' >tr').last().append('<td class="' + key + '"><input value="' + item[key] + '" class="form-control"></td>');
            }
            else
            {
                $(Tbody + ' >tr').last().append('<td class="' + key + '">' + item[key] + '</td>');
            }


        }
        inc++;
    });
    formatTable();
}

function formatTable() {
    var labels = {
        'sms_name': 'Название отчета',
        'report_active': 'Активен',
        'id': 'id'
    };
    $('.report table th').each(function () {
        $(this).text($(this).text().split($(this).text()).join(labels[$(this).text()])); // Замена лейблов на таблице.
    });
}

function updateData(element){
    var request =
    {
        'id':$(element).parent().parent().children('.id').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_report',
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
}

function createReport(element)
{
    var telegram_name = $('#telegram_name').val();
    var request =
    {
        'telegram_name':telegram_name,
    };
    $.ajax({
        url: '/create_report',
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
}

function getDetailAboutReport(element)
{
    var request =
    {
        'id':$(element).parent().parent().children('.id').text(),
    };
    $.ajax({
        url: '/detail_report',
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

function updateAbonentToReport(element)
{
    var request =
    {
        'abonent':$(element).parent().parent().children('.abonent').text(),
        'sms_name':$(element).parent().parent().children('.sms_name').text(),
        'report_id':$(element).parent().parent().children('.report_id').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_abonent2report',
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
$(document).ready(function () {

    $(document).on('change','.bot td select',function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateData(element);
    })

    $(document).on('click','#create_bot',function (e)
    {
        e.preventDefault();
        var element = $(this);
        createBot(element);
    })

    $(document).on('change','.bot-to-report td select', function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateBotToReport(element);
    })

    $(document).on('change','.bot-to-abonent td select', function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateBotToAbonent(element);
    })
});

function updateData(element){
    var request =
    {
        'id':$(element).parent().parent().children('.id').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_bot',
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

function createBot(element)
{
        var name = $('#name').val();
        var telegram_token = $('#telegram_token').val();
        var request =
        {
            'name':name,
            'telegram_token':telegram_token,
        };
        $.ajax({
            url: '/create_bot',
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

function getDetailAboutBot(element)
{
    var request =
    {
        'id':$(element).parent().parent().children('.id').text(),
    };
    $.ajax({
        url: '/detail_bot',
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

function updateBotToReport(element)
{
    var request =
    {
        'bot_id':$(element).parent().parent().children('.bot_id').text(),
        'name':$(element).parent().parent().children('.name').text(),
        'sms_name':$(element).parent().parent().children('.sms_name').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_bot2reports',
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

function updateBotToAbonent(element)
{
    var request =
    {
        'bot_id':$(element).parent().parent().children('.bot_id').text(),
        'name':$(element).parent().parent().children('.name').text(),
        'abonent':$(element).parent().parent().children('.abonent').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_bot2abonent',
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
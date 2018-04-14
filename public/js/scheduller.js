$(document).ready(function () {

    $(document).on('change','.scheduller td select', function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateScheduller(element);
    })
    $(document).on('change','.scheduller td input', function (e)
    {
        e.preventDefault();
        var element = $(this);
        updateScheduller(element);
    })
    $(document).on('click','#add_scheduller_task',function (e)
    {
        e.preventDefault();
        var element = $(this);
        addSchedullerTask(element);
    })

  });

function updateScheduller(element)
{
    var request =
    {
        'id':$(element).parent().parent().children('.id').text(),
        'abonent':$(element).parent().parent().children('.abonent').text(),
        'sending_time':$(element).parent().parent().children('.sending_time').text(),
        'column':$(element).parent().attr('class'),
        'value':$(element).val(),
    };
    $.ajax({
        url: '/update_scheduller',
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
    // console.log(request);
    snd_time = {};
}

function addSchedullerTask(element)
{
    var abonent = $('#abonent').val();
    var report_name = $('#report_name').text();

    var request =
    {
        'abonent':abonent,
        'report_name':report_name
    };
    $.ajax({
        url: '/create_scheduller_task',
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

$('.sending_time').clockpicker({
    donetext: 'click',
    autoclose: true,
    afterDone: function() {
        updateScheduller(snd_time);
    }
});
var snd_time = {};
$('.sending_time').click(function(e){
    e.preventDefault();
    snd_time = $(this).children();
});
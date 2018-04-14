$(document).ready(function () {
    $('select').each(function () {
        $(this).multiselect({
            enableFiltering: true,
            includeSelectAllOption: true,
            maxHeight: '300',
            nonSelectedText: $(this).attr('title'),
            enableCaseInsensitiveFiltering: true
        });
    });
    $(document).on('click', '#sender', function (e) {
        e.preventDefault();
        getAbonentsList();
    });
});

function getAbonentsList() {
    var department = $('#department').val();
    var abonent = $('#abonent').val();
    var bot = $('#bot').val();
    var text_for_abonents = $('#text_for_abonents').val();
    if (text_for_abonents == '') {
        alert("Не сформировано сообщение!");
        return;
    }

    if (!abonent.length) {
        abonent = ['none']
    }
    if (!department.length) {
        department = ['none']
    }
    if(department == 'none' && abonent == 'none'){
        alert("Не выбраны получатели!");
        return;
    }
    var request = {
        'department': department,
        'abonent': abonent,
        'bot': bot,
        'text_for_abonents': text_for_abonents
    };
    console.log(request);
    $.ajax({
        url: '/activate_dispatch',
        type: "get",
        dataType: "json",
        data: {
            request: request
        },
        success: function (response) {
            console.log(response);
            $('#fountainG').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        }
    });
}
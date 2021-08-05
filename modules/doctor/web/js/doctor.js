/* === Подтягиваем значения таблицы в поля === */
$('.found .table_blur_search tr').on('click', function () {
    $('.result_table_call').empty();
    // удаляем у всех tr элементов таблицы класс active
    $('.lead').css({"display": "inline"});
    $('.table_blur_search tr').removeClass('active');
    // выбранной строке таблицы присваиваем класс active
    // в нашем случае в this лежит ссылка на обрабатываемый по клику элемент TR
    $(this).addClass('active');

    // Получаем значения полей в переменную
    var client_id = $(this).find('td:first').text(); // Идентификатор client
    var name = $(this).find('.fullname').text() // Имя пациента
    // Ложим переменную в форму
    $("#formevent-client_id").hide().fadeIn(500).val(client_id); // Идентификатор клиента

    // Изменяем ссылку в кнопке на форму создания обращения
    var href = document.getElementById('setForm').href;
    var str = href.split('?')
    var url = window.location.href
    var strurl = url.split('?')
    if (strurl[1]){
        href = str[0] + '?client_id=' + client_id + '&' + strurl[1];
    }else {
        href = str[0] + '?client_id=' + client_id;
    }
    document.getElementById('setForm').href = href

    // Изменяем ссылку в кнопке на форму cоздания ЭЛН
    // href = document.getElementById('eln').href;
    // str = href.split('?')
    // href = str[0] + '?client_id=' + client_id;
    // document.getElementById('eln').href = href

    // Изменяем ссылку в кнопке на вид больничных
    // var href = document.getElementById('elnlist').href;
    // href = href + '?client_id=' + client_id;
    // document.getElementById('elnlist').href = href

    // Изменяем ссылку в кнопке на форму запись на вакцинацию
    href = document.getElementById('set-form-DoctorList').href;
    str = href.split('?')
    href = str[0] + '?client_id=' + client_id;
    document.getElementById('set-form-DoctorList').href = href
    getPortalDoctorUrl(client_id,name);



});

/* === Получаем МКБ динамически === */
$("#formevent-mkb").autocomplete({
    source: '/doctor/monitor/get-mkb'
    // minLength: 1
});
/* === Получаем МКБ динамически в форму ЭЛН === */
$("#formeln-diagnosis").autocomplete({
    source: '/doctor/monitor/get-mkb'
    // minLength: 1
});



function getPortalDoctorUrl(client_id,name) {
    $.ajax({
        type: "POST",
        url: '/doctor/portal-doctor/get-url', // куда шлем запрос
        cache: false,
        dataType: 'json',
        data: {
            client_id: client_id,          // Идентификатор ЛПУ
        },
        success: function (res) {
            if (res == null){
                document.getElementById('portaldoctor').setAttribute("disabled", "disabled");
                document.getElementById('portaldoctor').href = res
                document.getElementById('portaldoctor').text = 'Портал для этого пациента недоступен';
            }else {
                document.getElementById('portaldoctor').removeAttribute("disabled");
                document.getElementById('portaldoctor').href = res
                document.getElementById('portaldoctor').text = 'Портал врача для ' + name;
            }
        },
        // Пока идет выполнение скрипта
        beforeSend: function (res) {
            // Делаем анимацию видимой
            $("#loading").css({"display": "inline"});
            $("#portaldoctor").css({"display": "none"});
        },
        // Полное завершение скрипта
        complete: function () {
            // скрываем анимацию
            $("#loading").css({"display": "none"});
            $("#portaldoctor").css({"display": "block"});
        },
        error: function(request, status, error, html) {
            console.log(JSON.stringify(request));
            // var statusCode = request.status; // вот он код ответа
        }
    });
} // получить ссылку на портал врача




$(function() {
    //при нажатии на кнопку с id="save"
    $('#gen-eln-num').click(function(event) {
        event.preventDefault()
        //переменная formValid
        var formValid = true;
        //перебрать все элементы управления input
        $('input,textarea,select').each(function() {
            //найти предков, которые имеют класс .form-group, для установления success/error
            var formGroup = $(this).parents('.form-group');
            //найти glyphicon, который предназначен для показа иконки успеха или ошибки
            var glyphicon = formGroup.find('.form-control-feedback');
            //для валидации данных используем HTML5 функцию checkValidity
            if (this.checkValidity()) {
                //добавить к formGroup класс .has-success, удалить has-error
                formGroup.addClass('has-success').removeClass('has-error');
                //добавить к glyphicon класс glyphicon-ok, удалить glyphicon-remove
                glyphicon.addClass('glyphicon-ok').removeClass('glyphicon-remove');
            } else {
                //добавить к formGroup класс .has-error, удалить .has-success
                formGroup.addClass('has-error').removeClass('has-success');
                //добавить к glyphicon класс glyphicon-remove, удалить glyphicon-ok
                glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
                //отметить форму как невалидную
                formValid = false;
            }
        });
        //если форма валидна, то
        if (formValid) {
            var alias = $('#alias').val()
            var password = $('#password').val()

            $.ajax({
                type: "POST",
                url: '/doctor/eln/get-eln-num', // куда шлем запрос
                cache: false,
                dataType: 'json',
                data: {
                    alias: alias,          // Идентификатор ЛПУ
                    password: password,    // Идентификатор пациента
                },
                success: function (res) {
                    // // успешно выполнено
                    console.log(JSON.stringify(res));
                    var mess = '';
                    if (res['status'] == 0){
                        mess = '<div class="alert alert-danger" role="alert">\n' +
                            '                        <p><strong>Ошибка!</strong></p>\n' +
                            '                        <p>'+ res['message'] +'</p>\n' +
                            '                    </div>'
                        $('.alerts').html(mess)
                        return false
                    }else {
                        $('.alerts').html(mess)
                        $('#formeln-elnnum').hide().fadeIn(500).val(res);
                    }


                    //сркыть модальное окно
                    $('#myModal').modal('hide');
                    //отобразить сообщение об успехе
                    $('#success-alert').removeClass('hidden');
                },
                // Пока идет выполнение скрипта
                beforeSend: function (res) {
                    // Делаем анимацию видимой
                    $("#loading").css({"display": "inline"});
                    $("#gen-eln-num").css({"display": "none"});
                },
                // Полное завершение скрипта
                complete: function () {
                    // скрываем анимацию
                    $("#loading").css({"display": "none"});
                    $("#gen-eln-num").css({"display": "inline"});
                },
                error: function(request, status, error, html) {
                    console.log(JSON.stringify(request));
                    var statusCode = request.status; // вот он код ответа
                    $("error").html("Error: " + statusCode);
                }
            });

        }
    });
}); // валидация формы получения ЭЦП в модальном окне

// добавить период


$('.period .add').click(function(event){
    event.preventDefault()
    $('.set-eln-period').removeClass    ('hidden');
    $('.edit-eln-period').addClass('hidden');
    var countPeriod = $('.period table tr').length // посчитать количество периодов
    if (countPeriod > 3){
        var mess =
            '<div class="alert alert-warning alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<p><strong>Нельзя добавить более трех периодов!</strong></p>' +
            '<p>Удалите лишние или измените существующие</p>' +
            '</div>';
        $('.periodAlert').hide().fadeIn(500).html(mess);
        return false;
    }
    $('#myModalPeriod').modal('toggle');
}) // открыть модльное окно на добавление периода

$('.period .edit').click(function(event){
    event.preventDefault()
    $('.set-eln-period').addClass('hidden');
    $('.edit-eln-period').removeClass('hidden');
    $('#myModalPeriod').modal('toggle');
    var date_period_one = $('tr.active .date_period_one').text()
    var date_period_two = $('tr.active .date_period_two').text()
    $('#date_period_one').val(date_period_one)
    $('#date_period_two').val(date_period_two)


}) // открыть модльное окно на добавление периода

$('.period .remove').click(function(event){
    event.preventDefault()
    $('.table-period tr.active').addClass('animate__animated animate__bounceOutRight');
    setTimeout(function () {
        $('.table-period tr.active').remove();
        $('.period > .remove,.period > .edit').addClass('hidden');
    },500)
}) // удалить элемент

$('.set-eln-period').click(function(event) {
    event.preventDefault()
    var date_period_one = $('#date_period_one').val()
    var date_period_two = $('#date_period_two').val()
    var countDays = calcDate(date_period_one,date_period_two); // разница между датами
    var period = '';
    period = '<tr>' +
        '<td class="date_period_one">' + date_period_one + '</td>' +
        '<td class="date_period_two">' + date_period_two + '</td>' +
        '<td class="countDays">' + countDays + '</td>' +
        '<td></td>' +
        '<td></td>' +
        '</tr>'
    $('.table-period').append(period)
    $('#myModalPeriod').modal('hide');
}) // добавить период из модального окна

$('.edit-eln-period').click(function(event){
    event.preventDefault()
    var date_period_one = $('#date_period_one').val()
    var date_period_two = $('#date_period_two').val()
    var countDays = calcDate(date_period_one,date_period_two); // разница между датами

    $('.table-period tr.active .date_period_one').text(date_period_one)
    $('.table-period tr.active .date_period_two').text(date_period_two)
    $('.table-period tr.active .countDays').text(countDays)
    $('#myModalPeriod').modal('hide');

}) // изменить период

$(document).on("click", ".table-period tr:not(:first)", function(){
    $('.table-period tr').removeClass('active');
    $(this).addClass('active');
    $('.period > .btn').removeClass('hidden');
}) // действия с периодом общее

function calcDate(date1,date2) {
    var period_one = date1.split('.')
    var period_two = date2.split('.')
    period_one = new Date(period_one[2] + '-' + period_one[1] + '-' + period_one[0]);
    period_two = new Date(period_two[2] + '-' + period_two[1] + '-' + period_two[0]);
    return Math.ceil(Math.abs(period_two.getTime() - period_one.getTime()) / (1000 * 3600 * 24));
} // вычислить разничу между датами

$('.subscribe').click(function(event){
    event.preventDefault()
    var countPeriod = $('.period table tr').length // посчитать количество периодов

    if (countPeriod <= 1){
        document.getElementById('save').setAttribute("disabled", "disabled");
        var mess =
            '<div class="alert alert-warning alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<p><strong>Нужен хотя бы один период!</strong></p>'
            '</div>';
        $('.periodAlert').hide().fadeIn(500).html(mess);
        return false;
    }


    var eln = $('#formeln-elnnum').val()
    if (eln == ''){
        var mess =
            '<div class="alert alert-warning alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<p><strong>Сначала нужно получить номер ЭЛН!</strong></p>'
        '</div>';
        $('.periodAlert').hide().fadeIn(500).html(mess);
        return false;
    }
    var mkb = $('#formeln-diagnosis').val()
    if (mkb == ''){
        var mess =
            '<div class="alert alert-warning alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<p><strong>Сначала нужно добавить диагноз!</strong></p>'
        '</div>';
        $('.periodAlert').hide().fadeIn(500).html(mess);
        return false;
    }

    document.getElementById('save').removeAttribute("disabled");
    $('#myModalPodpisDoctor').modal('toggle');
})


$(function() {
    //при нажатии на кнопку с id="save"
    $('#gen-eln-num-doctor').click(function(event) {
        event.preventDefault()
        //переменная formValid
        var formValid = true;
        //перебрать все элементы управления input
        $('input,textarea,select').each(function() {
            //найти предков, которые имеют класс .form-group, для установления success/error
            var formGroup = $(this).parents('.form-group');
            //найти glyphicon, который предназначен для показа иконки успеха или ошибки
            var glyphicon = formGroup.find('.form-control-feedback');
            //для валидации данных используем HTML5 функцию checkValidity
            if (this.checkValidity()) {
                //добавить к formGroup класс .has-success, удалить has-error
                formGroup.addClass('has-success').removeClass('has-error');
                //добавить к glyphicon класс glyphicon-ok, удалить glyphicon-remove
                glyphicon.addClass('glyphicon-ok').removeClass('glyphicon-remove');
            } else {
                //добавить к formGroup класс .has-error, удалить .has-success
                formGroup.addClass('has-error').removeClass('has-success');
                //добавить к glyphicon класс glyphicon-remove, удалить glyphicon-ok
                glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
                //отметить форму как невалидную
                formValid = false;
            }
        });
        //если форма валидна, то
        if (formValid) {
            var alias = $('#alias_podpis_doctor').val()
            var password = $('#password_podpis_doctor').val()
            var eln = $('#formeln-elnnum').val()
            var mkb = $('#formeln-diagnosis').val()
            var reason_incapacity_work  = $('#formeln-cause').val() // причина трудоспособности
            var url_string = window.location.href
            var url = new URL(url_string);
            var client_id  = url.searchParams.get("client_id");
            var period
            var obj = {}
            var table = document.querySelector('.table-period').rows;
            for(var i=0;i<table.length;i++)
            {
                if(i !== 0){
                    var date_period_one = table[i].querySelector('.date_period_one').textContent
                    var date_period_two = table[i].querySelector('.date_period_two').textContent
                    var countDays = table[i].querySelector('.countDays').textContent
                    obj[i-1] = {
                        date_period_one : date_period_one,
                        date_period_two : date_period_two,
                        countDays : countDays,
                    }
                    period = JSON.stringify(obj)
                }
            }
            $.ajax({
                type: "POST",
                url: '/doctor/eln/get-podpis-doctor', // куда шлем запрос
                cache: false,
                dataType: 'json',
                data: {
                    period: period,
                    alias: alias,
                    password: password,
                    eln: eln,
                    mkb: mkb,
                    reason_incapacity_work: reason_incapacity_work,
                    client_id: client_id,
                },
                success: function (res) {
                    // // успешно выполнено
                    console.log(JSON.stringify(res));
                    var mess = '';
                    if (res['status'] == 0){
                        mess = '<div class="alert alert-danger" role="alert">\n' +
                            '                        <p><strong>Ошибка!</strong></p>\n' +
                            '                        <p>'+ res['message'] +'</p>\n' +
                            '                    </div>'
                        $('.alerts').html(mess)
                        return false
                    }else {
                        mess = '<div class="alert alert-success" role="alert">\n' +
                            '                        <p><strong>Успех!</strong></p>\n' +
                            '                        <p> ЭЛН успешно сохранён! </p>\n' +
                            '                    </div>'
                        $('.periodAlert').html(mess)
                    }
                },
                error: function(request, status, error, html) {
                    console.log(JSON.stringify(request));
                    var statusCode = request.status; // вот он код ответа
                    $("error").html("Error: " + statusCode);
                }
            });
        }
    });
}); // валидация формы получения ЭЦП в модальном окне

function toObject(arr) {
    var rv = {};
    for (var i = 0; i < arr.length; ++i)
        rv[i] = arr[i];
    return rv;
} // преобразовать объект в массив



$('.save').click(function(event){
    event.preventDefault()
    var alias = $('#alias_podpis_doctor').val()
    var password = $('#password_podpis_doctor').val()
    var eln = $('#formeln-elnnum').val()
    var mkb = $('#formeln-diagnosis').val()
    var letswork = $('#formeln-letswork').val()
    if (letswork == ''){
        letswork = null
    }
    console.log('letswork - ' + letswork)
    var reason_incapacity_work  = $('#formeln-cause').val() // причина трудоспособности
    var url_string = window.location.href
    var url = new URL(url_string);
    var client_id  = url.searchParams.get("client_id");
    var period
    var obj = {}
    var table = document.querySelector('.table-period').rows;
    for(var i=0;i<table.length;i++)
    {
        if(i !== 0){
            var date_period_one = table[i].querySelector('.date_period_one').textContent
            var date_period_two = table[i].querySelector('.date_period_two').textContent
            var countDays = table[i].querySelector('.countDays').textContent
            obj[i-1] = {
                date_period_one : date_period_one,
                date_period_two : date_period_two,
                countDays : countDays,
            }
            period = JSON.stringify(obj)
        }
    }
    $.ajax({
        type: "POST",
        url: '/doctor/eln/save-eln', // куда шлем запрос
        cache: false,
        dataType: 'json',
        data: {
            period: period,
            alias: alias,
            password: password,
            eln: eln,
            mkb: mkb,
            reason_incapacity_work: reason_incapacity_work,
            client_id: client_id,
            letswork: letswork,
        },
        success: function (res) {
            // // успешно выполнено
            console.log('res');
            console.log(JSON.stringify(res));
            var mess = '';
            if (res['status'] == 0){
                mess = '<div class="alert alert-danger" role="alert">\n' +
                    '                        <p><strong>Ошибка!</strong></p>\n' +
                    '                        <p>'+ res['message'] +'</p>\n' +
                    '                    </div>'
                $('.alerts').html(mess)
                return false
            }else {
                var key = res.message;
                console.log(key)
                mess = '<div class="alert alert-success" role="alert">\n' +
                    '                        <p><strong>Успех!</strong></p>\n' +
                    '                        <p> ЭЛН успешно подписан</p>\n' +
                    '                    </div>'
                $('.alerts').html(mess)
            }
        },
        // Пока идет выполнение скрипта
        beforeSend: function (res) {
            // Делаем анимацию видимой
            $("#loading").css({"display": "inline"});
            $("#gen-eln-num").css({"display": "none"});
        },
        // Полное завершение скрипта
        complete: function () {
            // скрываем анимацию
            $("#loading").css({"display": "none"});
            $("#gen-eln-num").css({"display": "inline"});
        },
        error: function(request, status, error, html) {
            console.log(JSON.stringify(request));
            var statusCode = request.status; // вот он код ответа
            $("error").html("Error: " + statusCode);
        }
    });

}) // сохранить подпись в БД

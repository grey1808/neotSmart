    /**
 * Created by Belya on 11.07.2019.
 */


jQuery(document).ready(function( $ )  {

    // получаем подробности о вызове
    $('.found .table_ssmp tr').on('click', function (){
        var eventId = $(this).find('.eventId').text(); // Идентификатор события
        var callNumberId = $(this).find('.callNumberId').text(); // Номер вызова что ли
        $('.addEventSsmp').val(eventId); // для кнопки принять вызов
        $('.addEventSsmpLocal').val(callNumberId); // для кнопки принять событие
        console.log(eventId)

        $.ajax({
            type: "POST",
            url: '/ssmp/numberssmp', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                callNumberId: callNumberId

            },
            success: function (res) {
                // console.log(JSON.stringify(res));
                $('.result_table_call').hide().fadeIn(500).html(res);
                },
            beforeSend: function (res) {
                // Делаем анимацию видимой
                $(".loading").css({"display": "inline"});
            },
            // Полное завершение скрипта
            complete: function () {
                // скрываем анимацию
                $(".loading").css({"display": "none"});
            },
            error: function(request, status, error, html) {
                console.log(JSON.stringify(request));
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });

    }); // numberssmp

    // Принять вызов через интеграционную платформу
    $('.addEventSsmp').on('click', function () {
        event.preventDefault();
        var eventId = $(this).val(); // Получить идентификатор события из кнопки

        if(eventId == ''){
            var mess =
                '<div class="alert alert-warning alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<strong>Внимание!</strong> Сначала выберите вызов!' +
                '</div>';
            $('.ssmpalert').hide().fadeIn(500).html(mess);
            return false;
        }
        console.log(eventId)
        $.ajax({
            type: "POST",
            url: '/ssmp/upd-event', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                eventId: eventId
            },
            success: function (res) {
                console.log(JSON.stringify(res));
                var mess =
                    '<div class="alert alert-success" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<strong>Успешно!</strong> Вызов <strong>' + eventId + '</strong> принят!' +
                    '</div>';
                $('.ssmpalert').hide().fadeIn(500).html(mess);
                // $('.ssmpalert').hide().fadeIn(500).html(res);
                location.reload(); // обновляю страницу для того чтобы обновить список, и убрать обработанные вызовы
            },

            error: function(request, status, error, html) {
                console.log(JSON.stringify(request));
                var mess =
                    '<div class="alert alert-success" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<strong>Ошибка!</strong> Вызов <strong>' + eventId + '</strong> не принят! Смотрите подромности в логе' +
                    '</div>';
                $('.ssmpalert').hide().fadeIn(500).html(mess);
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });
    }); // Получаем вызов через интеграционную платформу upd-event

    $('.addEventSsmpLocal').on('click', function () {
        var callNumberId = $(this).val(); // Получить идентификатор из кнопки
        var ssmpresoult = $('.ssmpresoult').val(); // Получить идентификатор собыития
        var ssmpresoult_text = $('.ssmpresoult option:selected').text(); // Получить  текст опиции события
        var note = $('.ssmpnote').val();
        var mess;

        var status = $('.table_ssmp tr.active td.status').text();
        console.log('status1 = "' + status + '"')
        if(callNumberId == ''){
            // event.preventDefault();
            mess =
                '<div class="alert alert-warning alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<strong>Внимание!</strong> Сначала выберите вызов!' +
                '</div>';
            $('.ssmpalert').hide().fadeIn(500).html(mess);
            return false;
        }
        if (+status == 0){
            mess =
                '<div class="alert alert-warning alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<strong>Внимание!</strong> Сначала вы должны принять вызов!' +
                '</div>';
            $('.ssmpalert').hide().fadeIn(500).html(mess);
            return false;
        }

        if(ssmpresoult == ''){
            // event.preventDefault();
            mess =
                '<div class="alert alert-warning alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<strong>Внимание!</strong> Выберите результат вызова!' +
                '</div>';
            $('.ssmpalert').hide().fadeIn(500).html(mess);
            return false;
        }
        console.log(' callNumberId ' + callNumberId)
        console.log(' ssmpresoult ' + ssmpresoult)
        console.log(' ssmpresoult_text ' + ssmpresoult_text)
        $.ajax({
            type: "POST",
            // url: '/ssmp/add-event-local', // куда шлем запрос
            url: '/ssmp/add-event', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                callNumberId: callNumberId,
                ssmpresoult: ssmpresoult,
                ssmpresoult_text: ssmpresoult_text,
                note: note
            },
            success: function (res) {
                console.log(JSON.stringify(res));
                var mess =
                    '<div class="alert alert-success" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<strong>Успешно!</strong> Результат вызова <strong>' + callNumberId + '</strong> добавлен, статус <strong>' +ssmpresoult_text + '</strong>"!' +
                    '</div>';
                $('.ssmpalert').hide().fadeIn(500).html(mess);
                location.reload(); // обновляю страницу для того чтобы обновить список, и убрать обработанные вызовы
            },

            error: function(request, status, error, html) {
                console.error(JSON.stringify(request));
                var mess =
                    '<div class="alert alert-danger" role="alert">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                    '<strong>Ошибка!</strong> Результат вызова <strong>' + callNumberId + '</strong> не принят!' +
                    '</div>';
                $('.ssmpalert').hide().fadeIn(500).html(mess);
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });
    }); // запись вызова в БД add-event

    CheckSsmp(); // вызывается высплывающее окно
    setInterval(CheckSsmp, 60000 );// вызывается высплывающее окно через каждую минуту
    function CheckSsmp() {
        $.ajax({
            type: "POST",
            url: '/ssmp/check-ssmp', // куда шлем запрос
            cache: false,
            dataType: 'json',
            success: function (res) {
                console.log(JSON.stringify(res));
                if(!res == false){
                    var mess =
                        '<div class="alert alert-danger alert-dismissible" role="alert">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                        res + '</div>';
                    $("#box_info").html(mess).fadeIn(500).delay(35000).fadeOut(500);// показываем всплывающее окно

                }

            },

            error: function(request, status, error, html) {
                console.log('Произошла ошибка! ' + JSON.stringify(request));
                // alert()

                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });
    } // функция проверки новых людей в базе



    });

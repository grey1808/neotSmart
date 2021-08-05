// далее идут функции

jQuery(document).ready(function( $ )  {



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
        var id = $(this).find('td:first').text(); // Идентификатор
        var famely = $(this).find('td:nth-child(2)').text(); // Фамилия
        var res_name = $(this).find('td:nth-child(3)').text(); // Имя
        var res_otchestvo = $(this).find('td:nth-child(4)').text(); // Отчество
        var birthdateAll = $(this).find('td:nth-child(5)').text(); // Дата рождения
        var birthdate = getAge($(this).find('td:nth-child(5)').text()); // Дата рождения
        function getAge(dateString) { // Вычислить возраст по дате рождения
            /**
             * Возвращает возраст по дате рождения
             *
             * @param dateString - дата рождения в формате '22.05.1990'
             * @return int сколько лет
             */
            var day = parseInt(dateString.substring(0,2));
            var month = parseInt(dateString.substring(3,5));
            var year = parseInt(dateString.substring(6,10));

            var today = new Date();
            var birthDate = new Date(year, month - 1, day); // 'month - 1' т.к. нумерация месяцев начинается с 0
            var age = today.getFullYear() - birthDate.getFullYear();
            var m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }// Вычислить возраст по дате рождения
        var res_pol = $(this).find('td:nth-child(6)').text(); // Пол
        var address = $(this).find('td:nth-child(7)').text(); // Адрес
        function formatDate(date) { // Функция преобразования даты и времени
            var dd = date.getDate();
            if (dd < 10) dd = '0' + dd;
            var mm = date.getMonth() + 1;
            if (mm < 10) mm = '0' + mm;
            var yy = date.getFullYear();
            var hh = date.getHours();
            var MM = date.getMinutes();
            return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + MM;
        }// Функция преобразования даты и времениы
        var d = new Date(); // Дата
        var telefon = $(this).find('td:nth-child(8)').text(); // Входящий номер

        // Ложим переменную в форму
        $("#id_client").hide().fadeIn(500).val(id); // Идентификатор клиента
        $("#famely").hide().fadeIn(500).val(famely); // Фамилия
        $("#name_men").hide().fadeIn(500).val(res_name); // имя
        $("#otch").hide().fadeIn(500).val(res_otchestvo); // Отчество
        $("#birthdate").hide().fadeIn(500).val(birthdate); // Дата рождения
        $("#pol").hide().fadeIn(500).val(res_pol); // Пол
        $("#address").hide().fadeIn(500).val(address); // Адрес
        $("#date_monitor").hide().fadeIn(500).val(formatDate(d)); // текущая дата
        $("#telefon").hide().fadeIn(500).val(telefon); // Контактный телефон


        $("#famely_appointment").hide().fadeIn(500).text(famely + ' ' + res_name + ' ' + res_otchestvo + ' ' + birthdateAll + ' г.р.'); // Фамилия имя отчество

        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                // subdivision: id,
                client_id_table:id
            },
            success: function (res) {
                // // успешно выполнено
                // alert(id);
                // alert(res);
                // // alert(res);
                // console.log(JSON.stringify(res));
                // успешно выполнено

                if(res == ''){
                    var mess = '<p class="alert alert-info">У выбранного пациента пока нет истории звонков...</p>'
                    $('.result_table_all').hide().fadeIn(500).html(mess);
                }else{
                    var table = '<h4 class="text-primary">НАЙДЕННЫЕ ОБРАЩЕНИЯ ПАЦИЕНТА</h4>';
                    table += '<div class="found_result">';
                    table += '<table class="call table_blur_search">\
                                <tr>\
                                    <th class="hidden">Номер обращения</th>\
                                    <th>Номер обращения</th>\
                                    <th>Дата обращения</th>\
                                    <th>Время обращения</th>\
                                    <th>Входящий вызов</th>\
                                    <th>Телефон пациента</th>\
                                    <th>Вид обращения</th>\
                                    <th>Подкатегория</th>\
                                    <th>Тема обращения</th>\
                                    <th>Комментарий</th>\
                                </tr>';

                    $.each(res, function (key, value) {
                        table += '<tr>';
                        table += '<td class="hidden">' + value.date_tratmentAll + '</td>';
                        table += '<td>' + value.id + '</td>';
                        table += '<td>' + value.date_tratment + '</td>';
                        table += '<td>' + value.time_tratment + '</td>';
                        table += '<td>' + value.contact + '</td>';
                        table += '<td>' + value.number + '</td>';
                        table += '<td>' + value.type + '</td>';
                        table += '<td>' + value.subcategory + '</td>';
                        table += '<td>' + value.topic + '</td>';
                        table += '<td>' + value.comment + '</td>';
                        table += '</tr>';
                    });
                    table += '</table>';
                    table += '</div>';
                    $('.getDistrictListAll').css({'background-color':'#ececec'});
                    $('.result_table_all').hide().fadeIn(500).html(table);
                }




            },
            // error: function (res) {
            //     // какая-то ошибка
            //     alert("Ошибка получения запроса specialties")
            // }
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });

    });

    /* === Запись значений вкладки монитор в бвзу === */
    $('#form_monitor #insert_table').bind('click', function () {
        var client_id = $("#id_client").val(); // Идентификатор клиента
        var eventtype = $("#eventtype").val(); // Вид обращения
        var subcategory = $("#subcategory").val(); // Подкатегория
        var topicevent = $("#topicevent").val(); // Тема обращения
        var number = $("#number").val(); // Номер пациента из базы
        var comment = $("#exampleFormControlTextarea1").val(); // Коментарии
        var telefon = $("#telefon").val(); // Номер вызова
        // alert(client_id + ' ' + eventtype + ' ' + subcategory + ' ' + topicevent + ' ' + telefon + ' ' + comment + ' ' + number)
        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            //dataType: 'json',
            data: {
                monitor:'есть',
                client_id: client_id,
                eventtype:eventtype,
                subcategory:subcategory,
                topicevent:topicevent,
                number:number,
                comment:comment,
                telefon:telefon
            },// отправляем $_POST['insert_monitor'] скрипту с содержимым переменной id
            success: function (res) {
                // успешно выполнено
                // alert('Успешно!');
                $('#form_monitor')[0].reset();
            },
            error: function (res) {
                // какая-то ошибка
                alert("Ошибка получения запроса javascript insert_monitor");
            }
        });
        $("#myModal").modal('hide'); // Закрываем модальное окно
    });

    /* === Подключаем календарь=== */
    $( function() {
        $( "#datepicker_entry" ).datepicker({
            // dateFormat:"dd.mm.yy"});
            dateFormat:"yy-mm-dd"});

        $( "#date-history-one" ).datepicker({
            dateFormat:"yy-mm-dd"});

        // Вкладка история
        $( "#date-history-two" ).datepicker({
            dateFormat:"yy-mm-dd"});
        $( "#date-jose-one" ).datepicker({
            dateFormat:"yy-mm-dd"});

        // ЖОЗ на вкладке монитор в модальном окне запись на ЗОЖ
        $( "#date-jose-two" ).datepicker({
            dateFormat:"yy-mm-dd"});

        // Вкладка мониторинг
        $( "#date-monitoring-one" ).datepicker({
            dateFormat:"yy-mm-dd"});
        $( "#date-monitoring-two" ).datepicker({
            dateFormat:"yy-mm-dd"});

    } );


    // Получаем список регионов
    $('#myModal_entry').on("show.bs.modal",function(){

        // Очищаем блоки
        $('.getDistrictListHeader').empty();
        $('.GetLPUListHeader').empty();
        $('.GetLPUListAll').empty();
        $('.GetSpesialityListHeader').empty();
        $('.GetDoctorListHeader').empty();
        $('.GetAvaibleAppointmentsHeader').empty();
        $('.GetSpesialityListAll').empty();
        $('.GetDoctorListAll').empty();
        $('.SetAppointmentAll').empty();
        $('.GetAvaibleAppointmentsAll').empty();
        $('.previewAll').empty();
        $('.joseHeader').empty();
        $('.jose').empty();

        // скрываем блоки
        $('.preview').css({'display':'none'});
        $('.joseAll').css({'display':'none'});
        $('.jose').css({'display':'none'});
        $('.joseContent').css({'display':'none'});
        $('.GetAvaibleAppointments').css({'display':'none'});
        $('.joseContentBody').css({'display':'none'});
        $('.GetDoctorListAll').css({'display':'none'});
        $('.GetSpesialityListAll').css({'display':'none'});

        // Очищаем поля
        $("#josePhone").val('');
        $("#date-jose-one").val('');
        $("#date-jose-two").val('');
        $(".joseInfo").val('');

        // Оформляем шапуку блока
        $('.getDistrictListAll').css({
            'display':'inline-block'
            // 'background-color':'#ececec'
        });
        $('.getDistrictListHeader').css({'background-color':'#ececec'});


        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                // subdivision: id,
                datepicker_entry_one:1
            },
            success: function (res) {
                // успешно выполнено
                // console.log(JSON.stringify(res));
                var getDistrictList = '<p class="bg-warning headerP">Выберите муниципальное образование, где доступна электронная запись</p>' +
                    '<ul>';
                $.each(res, function (key, value) {
                    getDistrictList += '<div class="col-md-6 col-lg-4">' +
                        '<li class="getDistrictListId" value="'+ value.IdDistrict +'">' + value.DistrictName + '</li>' +
                        '</div>';
                });
                getDistrictList += '</ul>';
                $('.getDistrictListAll').hide().fadeIn(500).html(getDistrictList);
            },
            // Пока идет выполнение скрипта
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
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });

    });

    // Получаем список подразделений ЛПУ
    $(document).on("click", ".getDistrictListId",function(){
        var id = $(this).val();
        var text = $(this).text();
        $('.getDistrictListAll').css({'display':'none'});
        $('.getDistrictList').css({'background-color':'#5499C9'});

        // $('.getDistrictList').css({'padding':'0'});
        //$('.getDistrictList').append('<div class="getDistrictListHeader"><h1 class="col-md-6">' + text + '</h1><span class="col-md-6 getDistrictListSpan">Выбрать другой регион</span></div>');

        var getDistrictList = '<h1 class="col-md-6">' + text + '</h1><span class="col-md-6 getDistrictListSpan">Выбрать другой регион</span>';
        $('.getDistrictListHeader').hide().fadeIn(500).html(getDistrictList);

        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                subdivision: id
            },
            success: function (res) {
                // // успешно выполнено
                // alert(id);
                // alert(res);
                // // alert(res);
                // console.log(JSON.stringify(res));
                $('.GetLPUListAll').css({'background-color':'#ececec'});
                if(res == ''){
                    var mess = '<p class="bg-danger error_p">На эту дату у выбранного подразделения ничего нет..</p>';
                    $('.GetLPUListAll').hide().fadeIn(500).html(mess);
                }else{
                    // успешно выполнено
                    var GetLPUList = '<p class="bg-warning headerP">Выберите медицинское учреждение</p>';
                    $.each(res, function (key, value) {
                        GetLPUList +='<div class="GetLPUListDiv" value"' + value.IdLPU + '">';
                        GetLPUList += '<span class="hidden">' + value.IdLPU + '</span>';
                        GetLPUList += '<p><b>' + value.LPUFullName + '</b></p>';
                        GetLPUList += '<p>' + value.Address + '</p>';
                        GetLPUList += '</div>';
                    });

                    $('.GetLPUListAll').hide().fadeIn(500).html(GetLPUList);
                }


            },
            // error: function (res) {
            //     // какая-то ошибка
            //     alert("Ошибка получения запроса specialties")
            // },
            // Пока идет выполнение скрипта
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
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error:1 " + statusCode);
            }
        });
    });
    $(document).on("click", ".getDistrictListSpan", function(){
        $('.getDistrictListHeader').empty();
        $('.GetLPUListHeader').empty();
        $('.GetSpesialityListHeader').empty();
        $('.GetDoctorListHeader').empty();
        $('.GetAvaibleAppointmentsHeader').empty();
        $('.GetSpesialityListAll').empty();
        $('.GetLPUListAll').empty();
        $('.GetDoctorListAll').empty();
        $('.SetAppointmentAll').empty();
        $('.GetAvaibleAppointmentsAll').empty();
        $('.previewAll').empty();
        $(".jose").empty()

        // скрываем блоки
        $('.joseContent').css({'display':'none'});
        $('.GetAvaibleAppointments').css({'display':'none'});
        $('.joseContentBody').css({'display':'none'});

        $('.getDistrictListAll').css({'display':'inline-block'});
        $('.getDistrictList').css({'background-color':'#ececec'});
        // $('.getDistrictListAll').css({'background-color':'#ececec'});
    });

    // Получаем идентификатор пациента и специальности из МИС ЛПУ
    $(document).on("click", ".GetLPUListDiv", function(){
        $('.GetLPUListAll').css({'display':'none'});
        $('.GetLPUList').css({'background-color':'#5499C9'});
        var id_LPU = $(this).find('span').text();
        $('.getLpuListId_LPU').hide().fadeIn(500).html(id_LPU);
        var LPUFullName = $(this).find('b').text();
        var lastname = $('.found .table_blur_search .active').find('td:nth-child(2)').text(); // Фамилия
        $('.lastname').hide().fadeIn(500).html(lastname);
        var firstname = $('.found .table_blur_search .active').find('td:nth-child(3)').text(); // Имя
        $('.firstname').hide().fadeIn(500).html(firstname);
        var patrname = $('.found .table_blur_search .active').find('td:nth-child(4)').text();  // Отчество
        $('.patrname').hide().fadeIn(500).html(patrname);
        var birthdate = $('.found .table_blur_search .active').find('td:nth-child(5)').text(); // Дата рождения
        $('.birthdate').hide().fadeIn(500).html(birthdate);


        var GetLPUList = '<h1 class="col-md-6">' + LPUFullName + '</h1><span class="col-md-6 GetLPUListSpan">Выбрать другое учреждение</span>';
        $('.GetLPUListHeader').hide().fadeIn(500).html(GetLPUList);
        var Address = $(this).find('p:last').text();
        $('.Address').hide().fadeIn(500).html(Address);

        var CallCancelVar =$('.CallCancelVar').text();

        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                CheckPatient: 1,        // CheckPatient
                lastname: lastname,     // Фамилия
                firstname: firstname,   // Имя
                patrname: patrname,     // Отчество
                birthdate: birthdate,   // Дата рождения
                id_LPU: id_LPU          // Идентификатор ЛПУ

            },
            success: function (res) {
                // успешно выполнено
                // console.log(JSON.stringify(res));
                $(".loading").css({"display": "inline"});
                if(res['ErrorDescription']){
                    if(res['IdError'] == 20){ // Если нет пациента выполняем этом метод
                        $.ajax({
                            type: "POST",
                            url: '', // куда шлем запрос
                            cache: false,
                            dataType: 'json',
                            data: {
                                AddNewPatient: 1,        // CheckPatient
                                lastname: lastname,     // Фамилия
                                firstname: firstname,   // Имя
                                patrname: patrname,     // Отчество
                                birthdate: birthdate,   // Дата рождения
                                id_LPU: id_LPU          // Идентификатор ЛПУ

                            },
                            success: function (res) {
                                // успешно выполнено
                                console.log(JSON.stringify(res));
                                var IdPat = res.IdPat;
                                $('.CheckPatient').hide().fadeIn(500).html(IdPat);
                                $.ajax({
                                    type: "POST",
                                    url: '', // куда шлем запрос
                                    cache: false,
                                    dataType: 'json',
                                    data: {
                                        GetSpesialityList: id_LPU,          // Идентификатор ЛПУ
                                        IdPat:IdPat                         // Полученный идентификатор пациента

                                    },
                                    success: function (res) {
                                        // // успешно выполнено
                                        // console.log(JSON.stringify(res));
                                        $('.GetSpesialityListAll').css({'background-color':'#ececec'});
                                        if(res['ErrorDescription']){
                                            var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>';
                                            $('.GetSpesialityListAll').hide().fadeIn(500).html(mess);

                                        }else{


                                            var GetSpesialityAll = '<p class="bg-warning headerP">Выберите специализацию врача</p>';
                                            GetSpesialityAll += '<ul class="GetSpesialityListTwo">';
                                            $.each(res, function (key, value) {
                                                GetSpesialityAll += '<div class="col-lg-6"><li class="GetSpesialityListId" value="'+ value.IdSpesiality +'">' + value.NameSpesiality  + ' (' + 'доступно номерков ' + value.CountFreeParticipantIE + ')' + '<span class="hidden">' +value.FerIdSpesiality +  '</span></li></div>';
                                            });
                                            GetSpesialityAll += '</ul>';

                                            $('.GetSpesialityListAll').hide().fadeIn(500).html(GetSpesialityAll);
                                        }
                                    },
                                    // Пока идет выполнение скрипта
                                    beforeSend: function (res) {
                                        // Делаем анимацию видимой
                                        $(".loading").css({"display": "inline"});
                                        $("body").prop("disabled", true);
                                    },
                                    // Полное завершение скрипта
                                    complete: function () {
                                        // скрываем анимацию
                                        $(".loading").css({"display": "none"});
                                        $("body").prop("disabled", false);
                                    },
                                    error: function(request, status, error, html) {
                                        var statusCode = request.status; // вот он код ответа
                                        $("error").html("Error: " + statusCode);
                                    }
                                });

                            },
                            error: function(request, status, error, html) {
                                var statusCode = request.status; // вот он код ответа
                                $("#error").html("Error: " + statusCode);
                            }
                        });
                    } // если не найден в базе

                    else {
                        var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>';
                        $('.GetSpesialityListAll').css({'background-color':'#ececec'});
                        $('.GetSpesialityListAll').hide().fadeIn(500).html(mess);
                    } // если найден в базе
                    // $(".loading").css({"display": "none"});

                } // если ошибка
                else {
                    var IdPat = res.IdPat;
                    $('.CheckPatient').hide().fadeIn(500).html(IdPat);
                    if(CallCancelVar == 1){


                        var idPatient = $('.found .table_blur_search .active').find('td:nth-child(1)').text(); // id пацента
                        var CheckPatient = $('.CheckPatient').text();


                        // Получить историю записей на прием
                        $.ajax({
                            type: "POST",
                            url: '', // куда шлем запрос
                            cache: false,
                            dataType: 'json',
                            data: {
                                GetPatientHistory: 1,
                                id_LPU: id_LPU,
                                IdPat: CheckPatient

                            },
                            success: function (res) {
                                // // успешно выполнено
                                // console.log(JSON.stringify(res));
                                // var GetSpesialityAll = '<p class="bg-warning headerP">Внимание! Нажимая кнопку "Отменить", Вы отправляете запрос на отмену записи в медицинскую информационную систему учреждения.</p>';
                                $('.GetSpesialityListAll').css({'background-color':'#ececec'});


                                if(res['ErrorDescription']){
                                    var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>';
                                    $('.GetSpesialityListAll').hide().fadeIn(500).html(mess);
                                }
                                else{
                                    var GetSpesialityAll = '<div class="previewCancel">' +
                                        '<p class="bg-warning headerP">Внимание! Нажимая кнопку "Отменить", Вы отправляете запрос на отмену записи в медицинскую информационную систему учреждения.</p>' +
                                        '';
                                    var count = 1;
                                    $.each(res, function (key, value) {
                                        // Преобразуем формат вывода даты
                                        var date = res[key]['VisitStart'].split('T');
                                        var date1 = date[0].split('-');
                                        date1 = date1[2] + '.' + date1[1] + '.' + date1[0];
                                        var time = date[1].slice(0, -3);
                                        var datetime = date1 + ' ' +  time;


                                        // GetAvaibleAppointmentsAll += '<li class="col-md-2 GetAvaibleAppointmentsDate" value="' + value.IdAppointment + '">' + '<i class="hidden">' + value.IdAppointment + '</i><span>' + date[2] + '.' + date[1] + '.' + date[0] + ' ' + dateformat[2].slice(0, -3) + '</span></li>';

                                        GetSpesialityAll += '' +
                                            '<div class="row previewCancelDiv">' +

                                            '<div class="col-sm-6">' +
                                            '<p><b>' + count + '. ' + res[key]['DoctorRendingConsultation']['Name'] + '</b></p>' +
                                            '<p><span>' + res[key]['SpecialityRendingConsultation']['NameSpesiality'] + '</span></p>' +
                                            // '<p><span>' + res[key]['VisitStart'] + '</span></p>' +
                                            '<p><span>' + datetime + '</span></p>' +
                                            '</div>' +
                                            '<div  class="col-sm-2">' +
                                            '<button name="' + count + '" type="button" class="btn btn-primary CallCancelBtn" value="' + res[key]['DoctorRendingConsultation']['IdDoc'] + '">Отменить</button>' +
                                            '</div>' +
                                            '<div  class="col-sm-4">' +
                                            '<p class="successCancel' + count + '"></p>' +
                                            '</div>' +
                                            '</div>';
                                        count++;

                                    });
                                    GetSpesialityAll += '</div>';
                                    $('.GetSpesialityListAll').hide().fadeIn(500).html(GetSpesialityAll);
                                }

                            },
                            // Пока идет выполнение скрипта
                            beforeSend: function (res) {
                                // Делаем анимацию видимой
                                $(".loading").css({"display": "inline"});
                            },
                            // // Полное завершение скрипта
                            complete: function () {
                                // скрываем анимацию
                                $(".loading").css({"display": "none"});
                            },
                            error: function(request, status, error, html) {
                                var statusCode = request.status; // вот он код ответа
                                $("error").html("Error: " + statusCode);
                            }
                        });

                        $(".loading").css({"display": "none"});
                    }
                    else {
                        $.ajax({
                            type: "POST",
                            url: '', // куда шлем запрос
                            cache: false,
                            dataType: 'json',
                            data: {
                                GetSpesialityList: id_LPU,          // Идентификатор ЛПУ
                                IdPat:IdPat                         // Полученный идентификатор пациента

                            },
                            success: function (res) {
                                // // успешно выполнено
                                // console.log(JSON.stringify(res));
                                $('.GetSpesialityListAll').css({'background-color':'#ececec'});
                                if(res['ErrorDescription']){
                                    var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>';
                                    $('.GetSpesialityListAll').hide().fadeIn(500).html(mess);

                                }else{


                                    var GetSpesialityAll = '<p class="bg-warning headerP">Выберите специализацию врача</p>';
                                    GetSpesialityAll += '<ul class="GetSpesialityListTwo">';
                                    $.each(res, function (key, value) {
                                        GetSpesialityAll += '<div class="col-lg-6"><li class="GetSpesialityListId" value="'+ value.IdSpesiality +'">' + value.NameSpesiality  + ' (' + 'доступно номерков ' + value.CountFreeParticipantIE + ')' + '<span class="hidden">' +value.FerIdSpesiality +  '</span></li></div>';
                                    });
                                    GetSpesialityAll += '</ul>';

                                    $('.GetSpesialityListAll').hide().fadeIn(500).html(GetSpesialityAll);
                                }
                            },
                            // Пока идет выполнение скрипта
                            beforeSend: function (res) {
                                // Делаем анимацию видимой
                                $(".loading").css({"display": "inline"});
                                $("body").prop("disabled", true);
                            },
                            // Полное завершение скрипта
                            complete: function () {
                                // скрываем анимацию
                                $(".loading").css({"display": "none"});
                                $("body").prop("disabled", false);
                            },
                            error: function(request, status, error, html) {
                                var statusCode = request.status; // вот он код ответа
                                $("error").html("Error: " + statusCode);
                            }
                        });
                    }
                } // если успешно
            },
            // Пока идет выполнение скрипта
            beforeSend: function (res) {
                // Делаем анимацию видимой
                $(".loading").css({"display": "inline"});
            },
            // Полное завершение скрипта
            // complete: function () {
            //     // скрываем анимацию
            //     $(".loading").css({"display": "none"});
            // },
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });


    });
    $(document).on("click", ".GetLPUListSpan", function(){


        $('.GetLPUListHeader').empty();
        $('.GetSpesialityListHeader').empty();
        $('.GetDoctorListHeader').empty();
        $('.GetAvaibleAppointmentsHeader').empty();
        $('.GetSpesialityListAll').empty();
        $('.GetDoctorListAll').empty();
        $('.SetAppointmentAll').empty();
        $('.GetAvaibleAppointmentsAll').empty();
        $('.previewAll').empty();
        $(".jose").empty();

        $('.previewAll').css({'background-color':'#ffffff'});

        // скрываем блоки
        $('.joseContent').css({'display':'none'});
        $('.GetAvaibleAppointments').css({'display':'none'});
        $('.joseContentBody').css({'display':'none'});

        $('.GetLPUListAll').css({'display':'inline-block'});
        $('.GetLPUListHeader').css({'background-color':'#ececec'});
    });



    // Получаем список врачей указанной специальности
    $(document).on("click", ".GetSpesialityListId", function(){


        $('.GetSpesialityListAll').css({'display':'none'});
        $('.GetSpesialityList').css({'background-color':'#5499C9'});
        var id = $(this).val();
        $('.IdSpesiality').hide().fadeIn(500).html(id);
        var NameSpesiality = $(this).text();
        $('.NameSpeciality').hide().fadeIn(500).html(NameSpesiality);
        var id_LPU = $('.getLpuListId_LPU').text();
        var IdPat = $('.CheckPatient').text();
        NameSpesiality = NameSpesiality.substr(0,NameSpesiality.indexOf(")")+1);
        var GetDoctorList = '<h1 class="col-md-6">' + NameSpesiality + '</h1><span class="col-md-6 GetSpesialityListSpan">Выбрать другую специальность</span>';
        $('.GetSpesialityListHeader').hide().fadeIn(500).html(GetDoctorList);



        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                id_LPU: id_LPU,
                IdPat: IdPat,
                idSpesiality: id

            },
            success: function (res) {
                // успешно выполнено
                // console.log(JSON.stringify(res));

                $('.GetDoctorListAll').css({'background-color':'#ececec'});
                if(res['ErrorDescription']){
                    var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>';
                    $('.GetAvaibleAppointments').css({'display':'inline-table'});
                    $('.GetDoctorListAll').hide().fadeIn(500).html(mess);
                }else{
                    // успешно выполнено

                    // var GetDoctorListAll = '<div class="GetDoctorListAll">';
                    var GetDoctorListAll = '<p class="bg-warning headerP">Выберите врача</p>';
                    GetDoctorListAll += '<ul>';
                    $.each(res, function (key, value) {
                        GetDoctorListAll += '<li class="GetDoctorListAllId" value="' +
                            // value.IdDoc + ',' + value.NearestDate + ',' + value.LastDate + '">' +
                            value.IdDoc + '">' +
                            '<b class="GetDoctorListAllNearestDate hidden">' + value.NearestDate + '</b>' +
                            '<i class="GetDoctorListAllLastDate hidden">' + value.LastDate + '</i>' +
                            '<span class="GetDoctorListAllLastName">' + value.Name  + '</span>' +
                            ' <span class="CountFreeParticipantIE">(Доступно номерков ' + value.CountFreeParticipantIE + ') ' +
                            value.AriaNumber +
                            '</span></li>';
                    });
                    // GetDoctorListAll += '</ul></div>';
                    GetDoctorListAll += '</ul>';
                    $('.GetAvaibleAppointments').css({'display':'inline-table'});
                    $('.GetDoctorListAll').hide().fadeIn(500).html(GetDoctorListAll);


                }


            },

            // Пока идет выполнение скрипта
            beforeSend: function (res) {
                // Делаем анимацию видимой
                $(".loading").css({"display": "inline"});
            },
            // Полное завершение скрипта
            complete: function () {
                // скрываем анимацию
                $(".loading").css({"display": "none"});
            },
            // error: function (res) {
            //     // какая-то ошибка
            //     alert("Ошибка получения запроса specialties")
            // }
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });
    });
    $(document).on("click", ".GetSpesialityListSpan", function(){


        $('.GetSpesialityListHeader').empty();
        $('.GetDoctorListHeader').empty();
        $('.GetAvaibleAppointmentsHeader').empty();
        $('.GetDoctorListAll').empty();
        $('.SetAppointmentAll').empty();
        $('.GetAvaibleAppointmentsAll').empty();
        $('.previewAll').empty();
        $(".joseHeader").empty();
        $(".jose").empty();


        $('.joseContentBody').css({'display':'none'});
        $('.jose').css({'background-color':'#ffffff'});
        $('.previewAll').css({'background-color':'#ffffff'});
        $('.GetSpesialityListAll').css({'display':'inline-block'});
        $('.GetSpesialityList').css({'background-color':'#ececec'});
    });

    // Получаем список доступных дат с временем указанных врачей
    $(document).on("click", ".GetDoctorListAllId", function(){

        var idSpesiality = $('.idSpesiality option:selected').val();
        var id_LPU = $('.getLpuListId_LPU').text();
        var IdPat = $('.CheckPatient').text();
        var GetAvailabledates = $(this).find(".GetDoctorListAllNearestDate").text();
        var IdDoc =  $(this).val();
        var visitStart = $(this).find(".GetDoctorListAllNearestDate").text();
        var LastDate = $(this).find(".GetDoctorListAllLastDate").text();
        var Name = $(this).find(".GetDoctorListAllLastName").text();


        $('.GetDoctorListAll').css({'display':'none'});
        $('.GetDoctorList').css({'background-color':'#5499C9'});
        var GetDoctorList = '<h1 class="col-md-6">' + Name + '</h1><span class="col-md-6 GetDoctorListSpan">Выбрать другого врача</span>';
        $('.GetDoctorListHeader').hide().fadeIn(500).html(GetDoctorList);

        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                GetAvailabledates:1,
                IdDoc: IdDoc,   // id доктора
                id_LPU: id_LPU, // id ЛПУ
                IdPat: IdPat,   // id пациента
                visitStart: visitStart, // ближайшая дата
                LastDate: LastDate      // конечная дата

            },
            success: function (res) {
                // успешно выполнено

                // console.log(JSON.stringify(res));

                $('.GetAvaibleAppointmentsAll').css({'background-color':'#ececec'});

                if(res == false){
                    var mess = '<p class="bg-danger error_p">Не найдено свободных талонов </p>';
                    $('.GetAvaibleAppointmentsAll').hide().fadeIn(500).html(mess);
                    // Добавляем блок ЖОЗ
                    $(".jose").hide().fadeIn(500).html(
                        "<p class='col-md-9'>При отсутствии номерков или отсутствии удобных номерков, Вы можете подать заявку в журнал отложенной записи.</p> " +
                        "<button class='btn btn-primary col-md-3 requestJose'>Подать заявку</button>"
                    );
                    $('.jose').css({'display':'inline-table'});
                }else if(res['ErrorDescription']){
                    var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>';
                    $('.GetAvaibleAppointmentsAll').css({"display":"inline-table"});
                    $('.GetAvaibleAppointmentsAll').hide().fadeIn(500).html(mess);
                    // Добавляем блок ЖОЗ
                    $(".myModal_entry").hide().fadeIn(500).append(
                        "<div class='jose'>" +
                        "<p class='col-md-9'>При отсутствии номерков или отсутствии удобных номерков, Вы можете подать заявку в журнал отложенной записи.</p> " +
                        "<button class='btn btn-primary col-md-3 requestJose'>Подать заявку</button>" +
                        "</div>"
                    );
                }else {
                    // успешно выполнено
                    var GetAvaibleAppointmentsAll = '<p class="bg-warning headerP">Выберите дату и время приема</p>';
                    GetAvaibleAppointmentsAll += '<ul>';
                    $.each(res, function (key, value) {
                        var dateformat = value.IdAppointment.split('_');
                        var date = dateformat[1].split('-');
                        GetAvaibleAppointmentsAll += '<li class="col-md-2 GetAvaibleAppointmentsDate" value="' + value.IdAppointment + '">' + '<i class="hidden">' + value.IdAppointment + '</i><span>' + date[2] + '.' + date[1] + '.' + date[0] + ' ' + dateformat[2].slice(0, -3) + '</span></li>';
                    });
                    GetAvaibleAppointmentsAll += '</ul>';
                    $('.GetAvaibleAppointmentsAll').hide().fadeIn(500).html(GetAvaibleAppointmentsAll);
                    // Добавляем блок ЖОЗ
                    $(".jose").hide().fadeIn(500).html(
                        "<p class='col-md-9'>При отсутствии номерков или отсутствии удобных номерков, Вы можете подать заявку в журнал отложенной записи.</p> " +
                        "<button class='btn btn-primary col-md-3 requestJose'>Подать заявку</button>"
                    );
                    $('.GetAvaibleAppointments').css({'display':'inline-table'});
                    $('.jose').css({'display':'inline-table'});
                }


            },
            // Пока идет выполнение скрипта
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
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });
    });
    $(document).on("click", ".GetDoctorListSpan", function(){

        // Очищаем блоки
        $('.GetDoctorListHeader').empty();
        $('.GetAvaibleAppointmentsHeader').empty();
        $('.SetAppointmentAll').empty();
        $('.previewAll').empty();
        $(".jose").empty();

        // Скрываем блоки
        $('.joseContent').css({'display':'none'});
        $('.GetAvaibleAppointmentsAll').css({'display':'none'});
        $('.joseContentBody').css({'display':'none'});
        $('.preview').css({"display":"none"});

        // показываем блоки
        $('.GetAvaibleAppointments').css({"display":"inline-table"});
        $('.GetDoctorListAll').css({"display":"inline-table"});
        $('.GetDoctorList').css({'background-color':'#ececec'});
        $('.GetDoctorListAll').css({'background-color':'#ececec'});

    });

    // Показываем предпросмотр
    $(document).on("click", ".GetAvaibleAppointmentsDate", function(){

        $(".jose").empty();
        $('.GetAvaibleAppointmentsAll').css({'display':'none'});
        $('.GetAvaibleAppointments').css({'background-color':'#5499C9'});
        var IdAppointment =  $(this).find("i").text();
        $('.IdAppointment').hide().fadeIn(500).html(IdAppointment);
        var GetAvaibleAppointmentsDate =  $(this).find("span").text();
        var GetAvaibleAppointments = '<h1 class="col-md-6">' + GetAvaibleAppointmentsDate + '</h1><span class="col-md-6 GetAvaibleAppointmentsSpan">Выберите время приёма</span>';
        $('.GetAvaibleAppointmentsHeader').hide().fadeIn(500).html(GetAvaibleAppointments);
        var Error = '<p class="previewAllError"></p>';
        var Name = '<p><span>' + $('#famely_appointment').text() + ' </span></p>';
        var Date = '<p>Запись на приём ' + $('.GetAvaibleAppointmentsHeader h1').text() + '</p>';
        var Spesiality = '<p>' + $('.GetSpesialityListHeader h1').text() + ' <span>' + $('.GetDoctorListHeader h1').text() + '</span>' + '</p>';
        var Address = '<p>' + $('.Address').text() + '</p>';
        var Button = '<button type="button" class="btn btn-primary btn-lg SetAppointmentSuccess">Записать</button>' + ' <button type="button" data-dismiss="modal" class="btn btn-secondary btn-lg SetAppointmentClose">Отменить</button>';

        $('.preview').css({'display':'inline-block'});
        $('.previewAll').css({'background-color':'#ececec'});
        $('.previewAll').hide().fadeIn(500).html(Error + Name + Date + Spesiality + Address + Button);

    });
    $(document).on("click", ".GetAvaibleAppointmentsSpan", function(){

        // очищаем блоки
        $('.GetAvaibleAppointmentsHeader').empty();
        $('.SetAppointmentAll').empty();
        $('.previewAll').empty();
        $('.previewAll').css({'background-color':'#ffffff'});
        $('.jose').remove();

        // выравниваем шапку
        $('.GetAvaibleAppointmentsAll').css({'display':'inline-block'});
        $('.GetAvaibleAppointments').css({'background-color':'#ececec'});

        // Добавляем блок ЖОЗ
        $(".myModal_entry").hide().fadeIn(500).append(
            "<div class='jose'>" +
            "<p class='col-md-9'>При отсутствии номерков или отсутствии удобных номерков, Вы можете подать заявку в журнал отложенной записи.</p> " +
            "<button class='btn btn-primary col-md-3 requestJose'>Подать заявку</button>" +
            "</div>"
        );
    });

    // Подтверждение записи
    $(document).on("click", ".SetAppointmentSuccess", function(){
        // $('.SetAppointment').empty();
        // $('.error_list').empty();
        // $('#error').empty();
        // $('select').on('change',function() {
        // });
        var id_LPU = $('.getLpuListId_LPU').text(); // id ЛПУ
        var IdPat = $('.CheckPatient').text();  // id пациента
        var IdAppointment = $('.IdAppointment').text();


        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                IdAppointment:IdAppointment,
                id_LPU: id_LPU, // id ЛПУ
                IdPat: IdPat   // id пациента

            },
            success: function (res) {
                // // успешно выполнено
                // alert(id);
                // alert(res);
                // // alert(res);
                // console.log(JSON.stringify(res));
                $('.SetAppointmentAll').css({'background-color':'#ececec'});
                if(res['ErrorDescription']){
                    var mess = '<p class="bg-danger error_p">' + res['ErrorDescription'] + '</p>'
                    $('.SetAppointmentAll').hide().fadeIn(500).html(mess);
                }else{
                    // успешно выполнено
                    var mess = '<p class="bg-success error_p">' + ' Успешно!' + '</p>'
                    $('.SetAppointmentAll').hide().fadeIn(500).html(mess);
                }


            },

            // Пока идет выполнение скрипта
            beforeSend: function (res) {
                // Делаем анимацию видимой
                $(".loading").css({"display": "inline"});
            },
            // Полное завершение скрипта
            complete: function () {
                // скрываем анимацию
                $(".loading").css({"display": "none"});
            },
            // error: function (res) {
            //     // какая-то ошибка
            //     alert("Ошибка получения запроса specialties")
            // }
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });
    });


    // Получаем список доступных звонков в виде списка wav файлов на вкладке монитор
    $(document).on("click", ".found_result .table_blur_search tr", function(){


        var date = $(this).find('td:first').text();
        // var date = $(this).find('td:first').text().replace(/-/g, '_'); // заменяем символы в строке
        // date = date.replace(/ /g, '_');  // заменяем символы в строке
        // date = date.replace(/:/g, '_'); // заменяем символы в строке
        // date = date.slice(0,-3);  // удаляем последние символы в строке


        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                getCallListAnalog:date // дата звонка

            },
            success: function (res) {
                // // успешно выполнено
                // alert(id);
                // alert(res);
                // // alert(res);
                console.log(JSON.stringify(res));

                if(res.length == 0){
                    var mess = '<p class="alert alert-info">У выбранного обращения пациента нет записанных звонков...</p>'
                    $('.result_table_call').hide().fadeIn(500).html(mess);
                }else {
                    $('.result_table_call').hide().fadeIn(500).html(res);
                }

            },
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });
    });

    // Получаем список доступных звонков в виде списка wav файлов на вкладке история
    $(document).on("click", ".found_history .table_blur_search tr", function(){


        var date = $(this).find('td:last').text();
        // var date = $(this).find('td:first').text().replace(/-/g, '_'); // заменяем символы в строке
        // date = date.replace(/ /g, '_');  // заменяем символы в строке
        // date = date.replace(/:/g, '_'); // заменяем символы в строке
        // date = date.slice(0,-3);  // удаляем последние символы в строке


        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                getCallListAnalog:date // дата звонка

            },
            success: function (res) {
                // // успешно выполнено
                // alert(id);
                // alert(res);
                // // alert(res);
                console.log(JSON.stringify(res));

                if(res.length == 0){
                    var mess = '<p class="alert alert-info">У выбранного обращения пациента нет записанных звонков...</p>'
                    $('.result_table_call').hide().fadeIn(500).html(mess);
                }else {
                    $('.result_table_call').hide().fadeIn(500).html(res);
                }

            },
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });
    });

    // Получить уникальный номер ЖОЗ
    $(document).on("click", ".requestJose", function(){
        // Убираем лишние блоки
        $('.jose').empty();

        // Обработка header
        var joseHeader = '<h1 class="col-md-6">Журнал отложенной записи</h1><span class="col-md-6 joseHeaderSpan">Выбрать другое время</span>';
        $('.joseHeader').hide().fadeIn(500).html(joseHeader); // Показываем заголовок
        $('.GetAvaibleAppointmentsAll').css({'display':'none'}); // Скрываем контента
        $('.joseContent').css({'background-color':'#5499C9'}); // Обработка заголовка

        // Скрываем лишние блоки
        $('.GetAvaibleAppointments').css({'display':'none'});
        $('.preview').css({'display':'none'});
        $('.SetAppointment').css({'display':'none'});

        // Показывем контент
        $('.joseContent').css({'display':'inline-block'});
        $('.joseContentBody').css({'display':'inline-block'});
        $('.joseAll').css({'display':'inline-block'});
    });

    // Получить уникальный номер ЖОЗ
    $(document).on("click", ".joseBtn", function(){


        // Вытаскиваем данные
        var IdLpu = $('.IdLpu').text(); // id ЛПУ
        var IdSpeciality = $('.IdSpesiality').text();
        var NameSpeciality = $('.NameSpeciality').text().split(' ')[0]; // Наименование врачебной специальности в справочнике МИС
        var FerIdSpeciality = $('.NameSpeciality').text().split(')')[1]; // Номенклатура специальностей специалистов с высшим и послевузовским медицинским и фармацевтическим образованием в сфере здравоохранения (OID 1.2.643.5.1.13.2.1.1.181)
        var IdDoc = $('.NameSpeciality').text(); // Идентификатор врача в соответствующем справочнике МИС
        var Claim = $('.Claim').val(); // Причина постановки в лист ожидания
        var IdPatient = $('.CheckPatient').text(); // Идентификатор пациента из соответствующего справочника МИС
        var LastName = $('.lastname').text(); // Фамилия пациента
        var FirstName = $('.firstname').text(); // Имя пациента
        var MiddleName = $('.patrname').text(); // Отчество пациента
        var BirthDate = $('.birthdate').text(); // Дата рождения пациента
        BirthDate = BirthDate.split('.');
        BirthDate = BirthDate[2] + '-' + BirthDate[1] + '-' + BirthDate[0];
        var Phone = $('#josePhone').val().replace('(', '').replace(')', '').replace('-', '').replace('-', '').replace('-', ''); // Номер телефона
        var Info = $('.joseInfo').val(); // Причина посещения врача, краткое описание симптомов и диагноза если известны
        var EndDate = $('#date-jose-two').val(); // Окончание интервала
        var StartDate = $('#date-jose-one').val(); // Начало интервала


        // проверка на заполненность
        if(!Phone){ // нужно сделать более изящно..
            $('#josePhone').css({"border":"1px solid red"});
            return;
        }$('#josePhone').css({"border":"1px solid #ccc"});

        if(!StartDate){
            $('#date-jose-one').css({"border":"1px solid red"});
            return;
        }$('#date-jose-one').css({"border":"1px solid #ccc"});

        if(!EndDate){
            $('#date-jose-two').css({"border":"1px solid red"});
            return;
        }$('#date-jose-two').css({"border":"1px solid #ccc"});

        if(!Info){
            $('.joseInfo').css({"border":"1px solid red"});
            return;
        }$('.joseInfo').css({"border":"1px solid #ccc"});




        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                FerIdSpeciality: FerIdSpeciality,
                IdLpu:IdLpu,
                IdSpeciality:IdSpeciality,
                NameSpeciality:NameSpeciality,
                Claim:Claim,
                BirthDate:BirthDate,
                FirstName:FirstName,
                IdPatient:IdPatient,
                LastName:LastName,
                MiddleName:MiddleName,
                Phone:Phone,
                Info:Info,
                EndDate:EndDate,
                StartDate:StartDate

            },
            success: function (res) {
                // успешно выполнено
                console.log(JSON.stringify(res));

                if(res['ErrorDescription']){
                    var mess = '<p class="bg-danger error_p"><code>' + res['ErrorDescription'] + '</code></p>';
                    $('.preview').css({'display':'inline-block'});
                    $('.previewAll').hide().fadeIn(500).html(mess);
                }
                else {
                    // успешно выполнено
                    $('.preview').css({'display':'inline-block'});
                    $('.previewAll').css({'background-color': '#ececec'});
                    $('.previewAll').hide().fadeIn(500).html('<p>Уважаемый пользователь! Ваша заявка зарегистрирована под номером <b>' + res.IdPar + '</b></p>');


                }

            },
            // Пока идет выполнение скрипта
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
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });
    });
    $(document).on("click", ".joseHeaderSpan", function(){
        // Открываем блок
        $('.GetAvaibleAppointments').css({'display':'inline-table'});
        $('.GetAvaibleAppointmentsAll').css({'display':'inline-table'});
        // Скрываем блок
        $('.joseContent').css({'display':'none'});
        $('.joseContentBody').css({'display':'none'});
        $('.preview').css({"display":"none"});
        // Ощищаем блок
        $('.previewAll').empty();


        // Добавляем блок ЖОЗ
        $(".jose").hide().fadeIn(500).html(
            "<p class='col-md-9'>При отсутствии номерков или отсутствии удобных номерков, Вы можете подать заявку в журнал отложенной записи.</p> " +
            "<button class='btn btn-primary col-md-3 requestJose'>Подать заявку</button>"
        );
        $('.jose').css({'display':'inline-table'});
    });

    /*=== Работа с классом найденных обращений на вкладке монитор  ===*/
    $(document).on("click", ".found_result .table_blur_search tr", function() {
        // $('.found_result .table_blur_search tr').on('click', function () {
        // удаляем у всех tr элементов таблицы класс active
        $('.found_result .table_blur_search tr').removeClass('active');
        // выбранной строке таблицы присваиваем класс active
        // в нашем случае в this лежит ссылка на обрабатываемый по клику элемент TR
        $(this).addClass('active');



    });

    /*=== Работа с классом найденных обращений на вкладке монитор  ===*/
    $(document).on("click", ".found_history .table_blur_search tr", function() {
        // $('.found_result .table_blur_search tr').on('click', function () {
        // удаляем у всех tr элементов таблицы класс active
        $('.found_history .table_blur_search tr').removeClass('active');
        // выбранной строке таблицы присваиваем класс active
        // в нашем случае в this лежит ссылка на обрабатываемый по клику элемент TR
        $(this).addClass('active');
        // var print = '<button class="btn pull-right print"><i class="glyphicon glyphicon-print"></i></button>';

        // $('.print_div').hide().fadeIn(500).html(print);

        // Получаем значения полей в переменную
        var id = $(this).find('td:first').text(); // Идентификатор
        var id_tratment = $(this).find('td:nth-child(2)').text(); // Номер обращения
        var date_tratment = $(this).find('td:nth-child(3)').text(); // Дата обращения
        var time_tratment = $(this).find('td:nth-child(4)').text(); // Время обращения
        var famely = $(this).find('td:nth-child(5)').text(); // Фамилия
        var res_name = $(this).find('td:nth-child(6)').text(); // Имя
        var res_otchestvo = $(this).find('td:nth-child(7)').text(); // Отчество
        var birthdate = getAge($(this).find('td:nth-child(8)').text()); // Дата рождения
        function getAge(dateString) { // Вычислить возраст по дате рождения
            /**
             * Возвращает возраст по дате рождения
             *
             * @param dateString - дата рождения в формате '22.05.1990'
             * @return int сколько лет
             */
            var day = parseInt(dateString.substring(0,2));
            var month = parseInt(dateString.substring(3,5));
            var year = parseInt(dateString.substring(6,10));

            var today = new Date();
            var birthDate = new Date(year, month - 1, day); // 'month - 1' т.к. нумерация месяцев начинается с 0
            var age = today.getFullYear() - birthDate.getFullYear();
            var m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        }// Вычислить возраст по дате рождения
        var res_pol = $(this).find('td:nth-child(9)').text(); // Пол
        var set_number = $(this).find('td:nth-child(10)').text(); // Входящий вызов
        var save_number = $(this).find('td:nth-child(11)').text(); // Телефон пациента вызов
        var type = $(this).find('td:nth-child(12)').text(); // Вид обращения
        var topic = $(this).find('td:nth-child(14)').text(); // Тема обращения
        var comment = $(this).find('td:nth-child(15)').text(); // Комментарий



        // var address = $(this).find('td:nth-child(10)').text(); // Адрес
        function formatDate(date) { // Функция преобразования даты и времени
            var dd = date.getDate();
            if (dd < 10) dd = '0' + dd;
            var mm = date.getMonth() + 1;
            if (mm < 10) mm = '0' + mm;
            var yy = date.getFullYear();
            var hh = date.getHours();
            var mm = date.getMinutes();
            return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + mm;
        }// Функция преобразования даты и времениы
        var d = new Date(); // Дата
        var telefon = $(this).find('td:nth-child(8)').text(); // Входящий номер

        // Ложим переменную в форму
        $("#id_client").hide().fadeIn(500).text(id); // Идентификатор клиента
        $("#id_tratment").hide().fadeIn(500).text(id_tratment); // Номер обращения
        $("#type").hide().fadeIn(500).text(type); // Вид обращения
        $("#topic").hide().fadeIn(500).text(topic); // Тема обращения
        $("#date_tratment").hide().fadeIn(500).text(date_tratment + ' ' + time_tratment); // Дата обращения
        $("#famely").hide().fadeIn(500).text(famely); // Фамилия
        $("#name_men").hide().fadeIn(500).text(res_name); // имя
        $("#otch").hide().fadeIn(500).text(res_otchestvo); // Отчество
        $("#pol").hide().fadeIn(500).text(res_pol); // Пол
        $("#birthdate").hide().fadeIn(500).text(birthdate); // Дата рождения
        $("#set_number").hide().fadeIn(500).text(set_number); // Входящий номер
        $("#save_number").hide().fadeIn(500).text(save_number); // Контактный телефон
        $("#famely_appointment").hide().fadeIn(500).text(famely + ' ' + res_name + ' ' + res_otchestvo); // Фамилия имя отчество
        $("#comment").hide().fadeIn(500).text(comment); // Комментарий



    });

    // получить отчет
    $(document).on("click", ".reportsBtn", function(){


        // Очищаем блок
        $('.resoultReports').empty();
        $('.count').empty();

        // Вытаскиваем данные
        var input = $('.reports input:checked').val();
        var dateOne = $('#date-monitoring-one').val();
        var dateTwo = $('#date-monitoring-two').val();
        var name = $('.reports input:checked').text();

        if(input == 1 || input == 2){
            $.ajax({
                type: "POST",
                url: '', // куда шлем запрос
                cache: false,
                dataType: 'json',
                data: {
                    getReports: input,
                    dateOne: dateOne,
                    dateTwo: dateTwo
                },
                success: function (res) {
                    // успешно выполнено
                    console.log(JSON.stringify(res));

                    $('.modal-dialog').css({'margin':'50px 10%'});
                    $('.resoultReports').hide().fadeIn(500).html(res[0]);
                    $('.count').hide().fadeIn(500).html(res[1]);
                    $('#myModalLabel').text(name);

                },
                // Пока идет выполнение скрипта
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
                    var statusCode = request.status; // вот он код ответа
                    $("#error_found_result").html("Error: " + statusCode);
                }
            });
        }


    });


    // получить диспансеризация 1 этап, диспансеризация 2 этап, профосмотры, неотложная помощь год
    $(document).on("click", ".reportsDiagram", function(){


        // Очищаем блок
        $('.resoultReports').empty();
        $('.count').empty();

        // Вытаскиваем данные
        var input = $('.reports input:checked').val();
        if(input == 3){
            var POST = $('.disp1').text();
            var name = 'Диспансеризация взрослого населения I этап';
        }
        else if(input == 4){
            var POST = $('.disp2').text();
            var name = 'Диспансеризация взрослого населения II этап';
        }
        else if(input == 5){
            var POST = $('.prof').text();
            var name = 'Профилактические осмотры взрослого населения';
        }
        else if(input == 6){
            var POST = $('.neot').text();
            var name = 'Неотложная помощь';
        }
        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input
            },
            success: function (res) {
                // успешно выполнено
                console.log(JSON.stringify(res));
                var mess = '' +
                    '<div  class="col-sm-6"><h2 class="text-center">План на год</h2><canvas id="myChart" width="400" height="200"></canvas></div>' +
                    '<div  class="col-sm-6"><h2 class="text-center">План на месяц</h2><canvas id="myChart1" width="400" height="200"></canvas></div>' +
                    '';
                // $('.modal-dialog').css({'margin':'50px 3%'});
                $('.resoultReports').hide().fadeIn(500).html(mess);

                // var POST = '<?php echo json_encode($_POST[\'disp_year\']); ?>';
                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
//                                labels: [res['twelveHeader'], "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
                        labels: [
                            "План",
                            res['year']
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                POST,
                                res['count']
                            ],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.9)',
                                'rgba(51, 139, 46, 0.8)'
                            ],
                            borderColor: [
                                'rgba(255,99,132,1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero:true
                                }
                            }]
                        }
                    }
                });

                $.ajax({
                    type: "POST",
                    url: '', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        // успешно выполнено
                        console.log(JSON.stringify(res));
                        var plan = Math.round(POST/12);
                        var bad = plan - res['count'];

                        var ctx = "myChart1"; // id блока
                        var chart = new Chart(ctx, {
                            // The type of chart we want to create
                            type: 'pie',
//                            type: 'doughnut',

                            // The data for our dataset
                            data: {
                                labels: ["Выполнено на текущий месяц", "Не выполнено"],
                                datasets: [{
                                    label: "Диспансеризация 1 этап - текущий месяц",
                                    backgroundColor: [
                                        'rgba(51, 139, 46, 0.8)',
                                        'rgba(255, 99, 132, 0.9)',
                                    ],
                                    data: [ res['count'], bad],
                                }]
                            },

                            // Configuration options go here
                            options: {}
                        });

                    },
                    // Пока идет выполнение скрипта
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
                        var statusCode = request.status; // вот он код ответа
                        $("#error_found_result").html("Error: " + statusCode);
                    }
                });
                $('#myModalLabel').text(name);
            },
            // Пока идет выполнение скрипта
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
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });



    });


    // отмена записи на прием
    $('.CallCancel').on('click', function (){
        $('.CallCancelVar').text('1');
    });
    $("#myModal_entry").on("hidden.bs.modal", function () {
        $('.CallCancelVar').text('');
    });

    // Отмена записи
    $(document).on("click",".CallCancelBtn", function (){
        //
        // var CallCancelPolisSerial = $('.CallCancelPolisSerial').text(); // Серия полиса
        // var CallCancelPolisNumber = $('.CallCancelPolisNumber').text(); // Номер полиса
        var idPat = $('.CheckPatient').text(); //  Идентификатор пациента из соответствующего справочника МИС
        var idAppointment = $(this).val(); // Идентификатор талона на запись
        var idLpu = $('.IdLpu').text(); // Идентификатор ЛПУ
        var countName = $(this).attr('name');
        // alert('CheckPatient: ' + idPat + ' ' + ' DoctorRendingConsultation=>idDoc] : ' + idAppointment + ' idLpu: ' + idLpu + ' countName = ' + countName);

        $.ajax({
            type: "POST",
            url: '', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                CreateClaimForRefusal: 1,        // Идентификатор пациента из соответствующего справочника МИС
                idPat: idPat,        // Идентификатор пациента из соответствующего справочника МИС
                idAppointment: idAppointment,        // Идентификатор талона на запись
                idLpu: idLpu

            },
            success: function (res) {
                // успешно выполнено
                // alert('idPat ' + idPat + ' idAppointment ' + idAppointment + ' idLpu ' + idLpu);

                // console.log(JSON.stringify(res));


                if(res['ErrorDescription']){
                    var mess = '<p class="bg-danger error_p"><code>' + res['ErrorDescription'] + 'IdError: ' + res['IdError'] + '</code></p>';
                    $('p.successCancel'  + countName).hide().fadeIn(500).html(mess);
                }
                else if(res['Success'] == 1) {
                    // успешно выполнено
                    var mess = '<p class="bg-success error_p">' + ' Успешно!' + '</p>';
                    $('p.successCancel'  + countName).hide().fadeIn(500).html(mess);

                }
                else if(res['Success'] == false) {
                    // успешно выполнено
                    var mess = '<p class="bg-danger error_p"><code>Такой записи нет ' + res['Success'] + '</code></p>';
                    $('p.successCancel'  + countName).hide().fadeIn(500).html(mess);

                }else{
                    var mess = '<p class="bg-danger error_p"><code>Такой записи нет ' + res['Success'] + '</code></p>';
                    $('p.successCancel'  + countName).hide().fadeIn(500).html(mess);
                }

            },
            // Пока идет выполнение скрипта
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
                var statusCode = request.status; // вот он код ответа
                $("#error").html("Error: " + statusCode);
            }
        });

    });

//     // получить диспансеризация 1 этап
//     $(document).on("click", ".disp1", function(){
//
//
//         // Очищаем блок
//         $('.resoultReports').empty();
//         $('.count').empty();
//
//         var POST = $('.disp_year').text();
//         $.ajax({
//             type: "POST",
//             url: '', // куда шлем запрос
//             cache: false,
//             dataType: 'json',
//             data: {
//                 disp1year: 1
//             },
//             success: function (res) {
//                 // успешно выполнено
//                 console.log(JSON.stringify(res));
//                 var mess = '' +
//                     '<div  class="col-sm-6"><canvas id="myChart" width="400" height="200"></canvas></div>' +
//                     '<div  class="col-sm-6"><canvas id="myChart1" width="400" height="200"></canvas></div>';
//                 $('.resoultReports').hide().fadeIn(500).html(mess);
//
//                 // var POST = '<?php echo json_encode($_POST[\'disp_year\']); ?>';
//                 var ctx = "myChart"; // id блока
//                 var myChart = new Chart(ctx, {
//                     type: 'bar',
//                     data: {
// //                                labels: [res['twelveHeader'], "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
//                         labels: [
//                             "План",
//                             res['year']
//                         ],
//                         datasets: [{
//                             label: 'Диспансеризация 1 этап - текущий год',
//                             data: [
//                                 POST,
//                                 res['count']
//                             ],
//                             backgroundColor: [
//                                 'rgba(255, 99, 132, 0.9)',
//                                 'rgba(51, 139, 46, 0.8)'
//                             ],
//                             borderColor: [
//                                 'rgba(255,99,132,1)',
//                                 'rgba(255, 159, 64, 1)'
//                             ],
//                             borderWidth: 0
//                         }]
//                     },
//                     options: {
//                         scales: {
//                             yAxes: [{
//                                 ticks: {
//                                     beginAtZero:true
//                                 }
//                             }]
//                         }
//                     }
//                 });
//                 $.ajax({
//                     type: "POST",
//                     url: '', // куда шлем запрос
//                     cache: false,
//                     dataType: 'json',
//                     data: {
//                         disp1mount: 1
//                     },
//                     success: function (res) {
//                         // успешно выполнено
//                         console.log(JSON.stringify(res));
//                         var plan = POST/12;
//                         var bad = plan - res['count'];
// //                        alert('План ' + plan + ' число месяца  ' + res['count'] + 'не выполнено' + bad);
//
//                         var ctx = "myChart1"; // id блока
//                         var chart = new Chart(ctx, {
//                             // The type of chart we want to create
//                             type: 'pie',
// //                            type: 'doughnut',
//
//                             // The data for our dataset
//                             data: {
//                                 labels: ["Выполнено на текущий месяц", "Не выполнено"],
//                                 datasets: [{
//                                     label: "Диспансеризация 1 этап - текущий месяц",
//                                     backgroundColor: [
//                                         'rgba(51, 139, 46, 0.8)',
//                                         'rgba(255, 99, 132, 0.9)',
//                                     ],
//                                     data: [ res['count'], bad],
//                                 }]
//                             },
//
//                             // Configuration options go here
//                             options: {}
//                         });
//
//                     },
//                     // Пока идет выполнение скрипта
//                     beforeSend: function (res) {
//                         // Делаем анимацию видимой
//                         $(".loading").css({"display": "inline"});
//                     },
//                     // Полное завершение скрипта
//                     complete: function () {
//                         // скрываем анимацию
//                         $(".loading").css({"display": "none"});
//                     },
//                     error: function(request, status, error, html) {
//                         var statusCode = request.status; // вот он код ответа
//                         $("#error_found_result").html("Error: " + statusCode);
//                     }
//                 });
//
//             },
//             // Пока идет выполнение скрипта
//             beforeSend: function (res) {
//                 // Делаем анимацию видимой
//                 $(".loading").css({"display": "inline"});
//             },
//             // Полное завершение скрипта
//             complete: function () {
//                 // скрываем анимацию
//                 $(".loading").css({"display": "none"});
//             },
//             error: function(request, status, error, html) {
//                 var statusCode = request.status; // вот он код ответа
//                 $("#error_found_result").html("Error: " + statusCode);
//             }
//         });
//
//
//
//     });
//
//     // получить диспансеризация 2 этап
//     $(document).on("click", ".disp2", function(){
//
//
//         // Очищаем блок
//         $('.resoultReports').empty();
//         $('.count').empty();
//
//         var POST = $('.disp_year').text();
//         $.ajax({
//             type: "POST",
//             url: '', // куда шлем запрос
//             cache: false,
//             dataType: 'json',
//             data: {
//                 disp2year: 1
//             },
//             success: function (res) {
//                 // успешно выполнено
//                 console.log(JSON.stringify(res));
//                 var mess = '' +
//                     '<div  class="col-sm-6"><canvas id="myChart" width="400" height="200"></canvas></div>' +
//                     '<div  class="col-sm-6"><canvas id="myChart1" width="400" height="200"></canvas></div>';
//                 $('.resoultReports').hide().fadeIn(500).html(mess);
//
//                 // var POST = '<?php echo json_encode($_POST[\'disp_year\']); ?>';
//                 var ctx = "myChart"; // id блока
//                 var myChart = new Chart(ctx, {
//                     type: 'bar',
//                     data: {
// //                                labels: [res['twelveHeader'], "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
//                         labels: [
//                             "План",
//                             res['year']
//                         ],
//                         datasets: [{
//                             label: 'Диспансеризация 1 этап - текущий год',
//                             data: [
//                                 POST,
//                                 res['count']
//                             ],
//                             backgroundColor: [
//                                 'rgba(255, 99, 132, 0.9)',
//                                 'rgba(51, 139, 46, 0.8)'
//                             ],
//                             borderColor: [
//                                 'rgba(255,99,132,1)',
//                                 'rgba(255, 159, 64, 1)'
//                             ],
//                             borderWidth: 0
//                         }]
//                     },
//                     options: {
//                         scales: {
//                             yAxes: [{
//                                 ticks: {
//                                     beginAtZero:true
//                                 }
//                             }]
//                         }
//                     }
//                 });
//                 $.ajax({
//                     type: "POST",
//                     url: '', // куда шлем запрос
//                     cache: false,
//                     dataType: 'json',
//                     data: {
//                         disp2mount: 1
//                     },
//                     success: function (res) {
//                         // успешно выполнено
//                         console.log(JSON.stringify(res));
//                         var plan = POST/12;
//                         var bad = plan - res['count'];
// //                        alert('План ' + plan + ' число месяца  ' + res['count'] + 'не выполнено' + bad);
//
//                         var ctx = "myChart1"; // id блока
//                         var chart = new Chart(ctx, {
//                             // The type of chart we want to create
//                             type: 'pie',
// //                            type: 'doughnut',
//
//                             // The data for our dataset
//                             data: {
//                                 labels: ["Выполнено на текущий месяц", "Не выполнено"],
//                                 datasets: [{
//                                     label: "Диспансеризация 1 этап - текущий месяц",
//                                     backgroundColor: [
//                                         'rgba(51, 139, 46, 0.8)',
//                                         'rgba(255, 99, 132, 0.9)',
//                                     ],
//                                     data: [ res['count'], bad],
//                                 }]
//                             },
//
//                             // Configuration options go here
//                             options: {}
//                         });
//
//                     },
//                     // Пока идет выполнение скрипта
//                     beforeSend: function (res) {
//                         // Делаем анимацию видимой
//                         $(".loading").css({"display": "inline"});
//                     },
//                     // Полное завершение скрипта
//                     complete: function () {
//                         // скрываем анимацию
//                         $(".loading").css({"display": "none"});
//                     },
//                     error: function(request, status, error, html) {
//                         var statusCode = request.status; // вот он код ответа
//                         $("#error_found_result").html("Error: " + statusCode);
//                     }
//                 });
//
//             },
//             // Пока идет выполнение скрипта
//             beforeSend: function (res) {
//                 // Делаем анимацию видимой
//                 $(".loading").css({"display": "inline"});
//             },
//             // Полное завершение скрипта
//             complete: function () {
//                 // скрываем анимацию
//                 $(".loading").css({"display": "none"});
//             },
//             error: function(request, status, error, html) {
//                 var statusCode = request.status; // вот он код ответа
//                 $("#error_found_result").html("Error: " + statusCode);
//             }
//         });
//
//
//
//     });
//
//     // получить Профилактические осмотры
//     $(document).on("click", ".prof", function(){
//
//
//         // Очищаем блок
//         $('.resoultReports').empty();
//         $('.count').empty();
//
//         var POST = $('.disp_year').text();
//         $.ajax({
//             type: "POST",
//             url: '', // куда шлем запрос
//             cache: false,
//             dataType: 'json',
//             data: {
//                 profyear: 1
//             },
//             success: function (res) {
//                 // успешно выполнено
//                 console.log(JSON.stringify(res));
//                 var mess = '' +
//                     '<div  class="col-sm-6"><canvas id="myChart" width="400" height="200"></canvas></div>' +
//                     '<div  class="col-sm-6"><canvas id="myChart1" width="400" height="200"></canvas></div>';
//                 $('.resoultReports').hide().fadeIn(500).html(mess);
//
//                 // var POST = '<?php echo json_encode($_POST[\'disp_year\']); ?>';
//                 var ctx = "myChart"; // id блока
//                 var myChart = new Chart(ctx, {
//                     type: 'bar',
//                     data: {
// //                                labels: [res['twelveHeader'], "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
//                         labels: [
//                             "План",
//                             res['year']
//                         ],
//                         datasets: [{
//                             label: 'Диспансеризация 1 этап - текущий год',
//                             data: [
//                                 POST,
//                                 res['count']
//                             ],
//                             backgroundColor: [
//                                 'rgba(255, 99, 132, 0.9)',
//                                 'rgba(51, 139, 46, 0.8)'
//                             ],
//                             borderColor: [
//                                 'rgba(255,99,132,1)',
//                                 'rgba(255, 159, 64, 1)'
//                             ],
//                             borderWidth: 0
//                         }]
//                     },
//                     options: {
//                         scales: {
//                             yAxes: [{
//                                 ticks: {
//                                     beginAtZero:true
//                                 }
//                             }]
//                         }
//                     }
//                 });
//                 $.ajax({
//                     type: "POST",
//                     url: '', // куда шлем запрос
//                     cache: false,
//                     dataType: 'json',
//                     data: {
//                         profmount: 1
//                     },
//                     success: function (res) {
//                         // успешно выполнено
//                         console.log(JSON.stringify(res));
//                         var plan = POST/12;
//                         var bad = plan - res['count'];
// //                        alert('План ' + plan + ' число месяца  ' + res['count'] + 'не выполнено' + bad);
//
//                         var ctx = "myChart1"; // id блока
//                         var chart = new Chart(ctx, {
//                             // The type of chart we want to create
//                             type: 'pie',
// //                            type: 'doughnut',
//
//                             // The data for our dataset
//                             data: {
//                                 labels: ["Выполнено на текущий месяц", "Не выполнено"],
//                                 datasets: [{
//                                     label: "Диспансеризация 1 этап - текущий месяц",
//                                     backgroundColor: [
//                                         'rgba(51, 139, 46, 0.8)',
//                                         'rgba(255, 99, 132, 0.9)',
//                                     ],
//                                     data: [ res['count'], bad],
//                                 }]
//                             },
//
//                             // Configuration options go here
//                             options: {}
//                         });
//
//                     },
//                     // Пока идет выполнение скрипта
//                     beforeSend: function (res) {
//                         // Делаем анимацию видимой
//                         $(".loading").css({"display": "inline"});
//                     },
//                     // Полное завершение скрипта
//                     complete: function () {
//                         // скрываем анимацию
//                         $(".loading").css({"display": "none"});
//                     },
//                     error: function(request, status, error, html) {
//                         var statusCode = request.status; // вот он код ответа
//                         $("#error_found_result").html("Error: " + statusCode);
//                     }
//                 });
//
//             },
//             // Пока идет выполнение скрипта
//             beforeSend: function (res) {
//                 // Делаем анимацию видимой
//                 $(".loading").css({"display": "inline"});
//             },
//             // Полное завершение скрипта
//             complete: function () {
//                 // скрываем анимацию
//                 $(".loading").css({"display": "none"});
//             },
//             error: function(request, status, error, html) {
//                 var statusCode = request.status; // вот он код ответа
//                 $("#error_found_result").html("Error: " + statusCode);
//             }
//         });
//
//
//
//     });
























}); // конец jQwery

/*Получение Результата обращения на основании результата осмотра*/
/*Получение Результата обращения на основании результата осмотра при клике*/
//
// function clickSelectTratment() {
//     var resosmotr = jQuery(".resosmotr option:selected").val();
//     $.ajax({
//         type: "POST",
//         url: '', // куда шлем запрос
//         cache: false, // не кэшируем браузером
//         dataType: 'json',
//         data: {resulTratment: resosmotr},
//         success: function (res) {
//             // успешно выполнено
//             var new_options = '';
//             $.each(res, function (key, value) {
//                 new_options += '<option>' + value.name + '</option>';
//             });
//             //$('.selectTratment').JSON.stringify(html(new_options));
//             $('.selectTratment').hide().fadeIn(500).html(new_options);
//
//
//             //$("#event").hide().fadeIn(500).html(res[0]);
//         },
//         error: function (res) {
//             // какая-то ошибка
//             alert("Ошибка получения запроса clickSelectTratment")
//         }
//     });
// };
// /*Получение Результата обращения на основании результата осмотра при клике*/
// function CallPrint(strid) {
//     var prtContent = document.getElementById(strid);
//     var prtCSS = '<link rel="stylesheet" href="/views/clinicdoctor/css/print.css" type="text/css" />';
//     var WinPrint = window.open('', '', 'left=50,top=50,width=800,height=640,toolbar=0,scrollbars=1,status=0');
//     WinPrint.document.write('<div id="print" class="contentpane">');
//     WinPrint.document.write(prtCSS);
//     WinPrint.document.write(prtContent.innerHTML);
//     WinPrint.document.write('</div>');
//     WinPrint.document.close();
//     WinPrint.focus();
//     WinPrint.print();
//     WinPrint.close();
//     prtContent.innerHTML = strOldOne;
// }


// Печать
function PrintElem(elem)
{
    Popup($(elem).html());
}

function Popup(data)
{
    var mywindow = window.open('', 'my div', 'height=400,width=600');
    mywindow.document.write('<html><head><title>my div</title>');
    /*optional stylesheet*/ //mywindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
    mywindow.document.write('</head><body >');
    mywindow.document.write(data);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    mywindow.print();
    mywindow.close();

    return true;
}


// Диаграммы



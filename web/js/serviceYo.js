$('document').ready(function() {
    var strGET = window.location.search.replace( '?', '');
    $('.btn-menu').each(function() {
        if (strGET == 'netrica_Code=' + $(this).data('val'))
        {
            $(this).addClass('active');
        }
    });

}); // выбираем активный пункт профиля помощи

$(document).on("click", ".getnumber", function(e){
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/get-number',
        cache: false,
        dataType: 'json',
        success: function (res) {
            // успешно выполнено
            $('.number').val(res);
            $('.dateregister').removeClass('hidden')
            $('.dateregister-label').removeClass('hidden')
            $('.register').removeClass('hidden')

        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}); // сформировать номер 32007_23654 и показать календарь

// $(document).on("click", ".dateregister", function(e){
//     $('.register').removeClass('hidden')
// }); // выбрать дату и открыть кнопку сформировать направление


$(document).on("click", ".TargetLpuCode", function(e){
    e.preventDefault();
    var TargetLpuCode = $(this).val()
    var lpuId = $(this).data('id')
    // alert(lpuId)
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/get-lpu-code',
        cache: false,
        dataType: 'json',
        data: {
            TargetLpuCode: TargetLpuCode,
            lpuId: lpuId,
        },
        success: function (res) {
            // успешно выполнено
            console.log(res)

        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}) // Отправить направление65

$(document).on("click", ".register", function(e){
    e.preventDefault();
    var dateregister = $('.dateregister').val();
    if(!dateregister.replace(/^\s+|\s+$/g, '')){
        alert('Заполните поле с датой!')
        return false;
    }
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/register',
        cache: false,
        dataType: 'json',
        data: {
            dateregister: dateregister,
        },
        success: function (res) {
            // успешно выполнено
            console.log(res)
            $('.speciality-list').hide().fadeIn(500).html(res);
            // alert(res)
        },
        beforeSend: function (res) {
            // Делаем анимацию видимой
            $(".loading").css({"display": "inline"});
        },
        complete: function () {
            // скрываем анимацию
            $(".loading").css({"display": "none"});
        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}) // сформировать направление и получить список специальностей

$(document).on("click", ".FerIdSpeciality", function(e){
    e.preventDefault();
    var idspeciality = $(this).data('idspeciality')
    console.log(' idspeciality ' + idspeciality)
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/doctors-referral2',
        cache: false,
        dataType: 'json',
        data: {
            idspeciality: idspeciality,
        },
        success: function (res) {
            // успешно выполнено
            console.log(res)
            $('.doctor-list').hide().fadeIn(500).html(res);
            // alert(res)
        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}) // получаем список доступных врачей по выбранной специальности

$(document).on("click", ".doctorid", function(e){
    e.preventDefault();
    var idDoc = $(this).data('iddoc')
    var doctorname = $(this).text()
    console.log(' idDoc ' + idDoc)
    console.log(' doctorname ' + doctorname)
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/time-referral2',
        cache: false,
        dataType: 'json',
        data: {
            idDoc: idDoc,
            doctorname: doctorname,
        },
        success: function (res) {
            // успешно выполнено
            console.log(res)
            $('.time-list').hide().fadeIn(500).html(res);
            // alert(res)
        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}) // получаем список доступного времени по выбранному врачу


$(document).on("click", ".doctor-time", function(e){
    e.preventDefault();
    var idAppointment = $(this).data('idappointment') // idAppointment врача
    var visitstart = $(this).data('visitstart') // дата начала приема
    console.log(' idAppointment ' + idAppointment)
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/set-appointment',
        cache: false,
        dataType: 'json',
        data: {
            idAppointment: idAppointment,
            visitstart: visitstart,
        },
        success: function (res) {
            // успешно выполнено
            console.log(res)
            $('.result-SetAppointment').hide().fadeIn(500).html(res);
            // alert(res)
        },
        beforeSend: function (res) {
            // Делаем анимацию видимой
            $(".loading").css({"display": "inline"});
            $(".time-list").css({"display": "none"});
        },
        complete: function () {
            // скрываем анимацию
            $(".loading").css({"display": "none"});
            $(".time-list").css({"display": "inline"});
        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}) // получаем список доступного времени по выбранному врачу




$(document).on("click", ".cancelreg", function(e){
    var idmq = $(this).val() // IdMq
    $('.cancellation').data('idmq',idmq); // присовить значение причине
    console.log(' idmq ' + idmq)
}) // передаем данные для отмены отмены записи в модальное окно

$(document).on("click", ".cancellation", function(e){
    var idmq = $(this).data('idmq');
    var cancellation = $(this).data('cancellation');
    $.ajax({
        type: "POST",
        url: '/serviceyo/service/idAppointment-reg',
        cache: false,
        dataType: 'json',
        data: {
            idmq: idmq,
            cancellation: cancellation,
        },
        success: function (res) {
            // успешно выполнено
            console.log(res)
            $('.cancelRegResult').hide().fadeIn(500).html(res);
            // alert(res)
        },
        beforeSend: function (res) {
            // Делаем анимацию видимой
            $(".loading").css({"display": "inline"});
        },
        complete: function () {
            // скрываем анимацию
            $(".loading").css({"display": "none"});
        },
        error: function(res, status, error, html) {
            console.log(JSON.stringify(res));
        }
    });
}) // Отмена записи


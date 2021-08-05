


jQuery(document).ready(function( $ )  {
    // (window.location.pathname == '/monitoring') ? getSetting = getsettingReportsDate() : false; // получаем настройку на выбор дыты у всех отчетов
    // if(getSetting == 0){
    //
    // }

    $(document).on("click", ".reports .btn", function(){
        $('.reports .btn').removeClass("active");
        $(this).addClass("active");
        var input = $('.reports .btn.active').val();
        if(input == 1 || input == 2 || input == 7)
            $('.info').hide().fadeIn(500).html('');
            $('.info').hide().fadeIn(500).html('<span class="text-danger "><strong>Выберите дату и нажмите кнопку "Просмотр"</strong></span>');
            $('.info').addClass('animated wobble');
        // alert($('.reports .btn.active').val());
    }); // Сообщение о выборе даты анимация
    // получить отчет
    $(document).on("click", ".reportsBtn", function(){

        // Очищаем блок
        $('.resoultReports').empty();
        $('.count').empty();

        // Вытаскиваем данные
        // var input = $('.reports input:checked').val();
        var input = $('.reports .btn.active').val();
        if(!input){
            $('.reportsModal').modal('toggle');
            $('.info').hide().fadeIn(500).html('');
            $('.info').hide().fadeIn(500).html('<span class="text-danger "><strong>Выберите дату и нажмите кнопку "Просмотр"</strong></span>');
            $('.info').addClass('animated wobble');
        } // если не нажата кнопка


        var dateOne = $('#date-monitoring-one').val();
        var dateTwo = $('#date-monitoring-two').val();
        var name = $('.reports .btn.active').text();
        // if(!input) {input = $('.reports .btn:first').val()};
        // if(!name) {name = $('.reports .btn:first').text()};


        switch (+input){
            case 1:
                getOne(input,dateOne,dateTwo,name);
                break;
            case 2:
                getOne(input,dateOne,dateTwo,name);
                break;
            case 3:
                // var POST = $('.disp1').text();
                name = 'Диспансеризация взрослого населения I этап';
                getReportProfDate(input,name,dateOne,dateTwo)
                break;
            case 4:
                // var POST = $('.disp2').text();
                name = 'Диспансеризация взрослого населения II этап';
                getReportDate(input,name,dateOne,dateTwo)
                break;
            case 5:
                // var POST = $('.prof').text();
                name = 'Профилактические осмотры взрослого населения';
                getReportProfDate(input,name,dateOne,dateTwo)
                break;
            case 6:
                // var POST = $('.neot').text();
                name = 'Неотложная помощь';
                getReportNeotDate(input,name)
                break;
            case 7:
                name = 'Гериатрия стационар';
                getGeriatriaStac(input,name,dateOne,dateTwo);
                break;
            case 8:
                name = 'Гериатрия поликлиника';
                getGeriatriaPol(input,name,dateOne,dateTwo);
                break;
        }

    }); // Получить отчет по нажатию на кнопку просмотр

    // получить диспансеризация 1 этап, диспансеризация 2 этап, профосмотры, неотложная помощь год
    $(document).on("click", ".reportsDiagram", function(){

        $('.info').hide().fadeIn(500).html('');
        // Очищаем блок
        $('.resoultReports').empty();
        $('.count').empty();

        // Вытаскиваем данные
        var input = $(this).val();
        switch (+input){
            case 3:
                // var POST = $('.disp1').text();
                var name = 'Диспансеризация взрослого населения I этап';
                getReportProf(input,name)
                break;
            case 4:
                var POST = $('.disp2').text();
                var name = 'Диспансеризация взрослого населения II этап';
                getReport(input,name)
                break;
            case 5:
                // var POST = $('.prof').text();
                var name = 'Профилактические осмотры взрослого населения';
                getReportProf(input,name)
                break;
            case 6:
                // var POST = $('.neot').text();
                var name = 'Неотложная помощь';
                getReportNeot(input,name)
                break;
            case 9:
                // var POST = $('.neot').text();
                var name = 'Диспансеризация раз в три года';
                getReportDispThreeYear(input,name)
                break;
            case 10:
                // var POST = $('.neot').text();
                var name = 'Диспансеризация раз в год';
                getReportDispOneYear(input,name)
                break;
        }
    });


    function PrintElem(elem){
        Popup($(elem).html());
    }// Печать

    function Popup(data){
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
    } // Печать модальное окно

    function getOne(input,dateOne,dateTwo,name) {
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                getReports: input,
                dateOne: dateOne,
                dateTwo: dateTwo
            },
            success: function (res) {
                // успешно выполнено
                // console.log(JSON.stringify(res));

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

                console.log(JSON.stringify(request));
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });
    }

    function getReport(input,name) {
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports-diagram-year',
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
                $('.resoultReports').hide().fadeIn(500).html(mess);

                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "План",
                            res['year']
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                res['plan-year'],
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
                    url: '/monitoring-old/reports-diagram-mount', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        console.log(JSON.stringify(res));
                        var plan = res['plan-mount'];
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
                    beforeSend: function (res) {
                        // Делаем анимацию видимой
                        $(".loading").css({"display": "inline"});
                    },
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
            beforeSend: function (res) {
                // Делаем анимацию видимой
                $(".loading").css({"display": "inline"});
            },
            complete: function () {
                // скрываем анимацию
                $(".loading").css({"display": "none"});
            },
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });
    } // получить отчет если это не неотложная помощь

    function getReportNeot(input,name) { // получить отчет только по неотложно помощи

        var mess = '' +
            '<div  class="col-sm-6"><h2 class="text-center">План на год</h2><canvas id="myChart" width="400" height="200"></canvas></div>' +
            '<div  class="col-sm-6"><h2 class="text-center">План на месяц</h2><canvas id="myChart1" width="400" height="200"></canvas></div>'
        // $('.modal-dialog').css({'margin':'50px 3%'});
        $('.resoultReports').hide().fadeIn(500).html(mess);
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports-diagram-year', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input
            },
            success: function (res) {
                // успешно выполнено
                console.log(JSON.stringify(res));
                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "План на " + res['year'],
                            "Выполнено " +"\n" + "год",
                            "Взрослые",
                            "Дети",
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                res['plan-year'],
                                res['count'],
                                res['plan-year-vzr'],
                                res['plan-year-det'],
                            ],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.9)',
                                'rgba(51, 139, 46, 0.8)',
                                'rgba(34, 150, 240, 0.8)',
                                'rgba(240, 226, 34, 0.8)',
                            ],
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
                    url: '/monitoring-old/reports-diagram-mount', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        // успешно выполнено
                        console.log(JSON.stringify(res));
                        // var plan = Math.round(res['plan_year']/12);
                        var plan = res['plan-mount'];

                        plan = plan - res['count'];

                        var ctx = "myChart1"; // id блока
                        var chart = new Chart(ctx, {
                            // The type of chart we want to create
                            type: 'pie',
//                            type: 'doughnut',

                            // The data for our dataset
                            data: {
                                labels: [
                                    "Не выполнено",
                                    "Взрослые",
                                    "Дети",
                                ],
                                datasets: [{
                                    label: name,
                                    backgroundColor: [
                                        // 'rgba(51, 139, 46, 0.8)',
                                        'rgba(255, 99, 132, 0.9)',
                                        'rgba(34, 150, 240, 0.8)',
                                        'rgba(240, 226, 34, 0.8)',
                                    ],
                                    data: [
                                        // res['count'],
                                        plan,
                                        res['plan-mount-vzr'],
                                        res['plan-mount-det'],
                                    ],
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
    } // получить неотложку

    function getReportProf(input,name) { // Диспансеризация взрослого населения I этап, Профилактические осмотры взрослого населения

        var mess = '' +
            '<div  class="col-sm-6"><h2 class="text-center">План на год</h2><canvas id="myChart" width="400" height="200"></canvas></div>' +
            '<div  class="col-sm-6"><h2 class="text-center">План на месяц</h2><canvas id="myChart1" width="400" height="200"></canvas></div>' +
            '<div  class="col-sm-12"><h2 class="text-center">Исход осмотров</h2><div id="myChart3"></div></div>'
        // $('.modal-dialog').css({'margin':'50px 3%'});
        $('.resoultReports').hide().fadeIn(500).html(mess);
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports-diagram-year', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input
            },
            success: function (res) {
                // успешно выполнено


                console.log(JSON.stringify(res));

                var table = '<table class="table table-hover">' +
                    '<th>Год</th>' +
                    '<th>Количество</th>' +
                    '<th>Наименование</th>';
                $.each(res['plan-year-table'], function (key, value) {
                    table += '<tr>'+
                                '<td>'+ value.year +'</td>'+
                                '<td>'+ value.count +'</td>'+
                                '<td>'+ value.result +'</td>'+
                            '</tr>';
                });
                table += '</table>';
                $('#myChart3').hide().fadeIn(500).html(table);

                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "План на " + res['plan-year-table'][0]['year'],
                            "Выполнено" +"\n" + "год",
                            "До 40 лет",
                            "После 40 лет",
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                res['plan-year'],
                                +res['plan-year-before'] + +res['plan-year-after'],
                                res['plan-year-before'],
                                res['plan-year-after'],
                            ],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.9)',
                                'rgba(51, 139, 46, 0.8)',
                                'rgba(34, 150, 240, 0.8)',
                                'rgba(240, 226, 34, 0.8)',
                            ],
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
                    url: '/monitoring-old/reports-diagram-mount', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        // успешно выполнено
                        console.log(JSON.stringify(res));
                        // var plan = Math.round(res['plan_year']/12);
                        var plan = res['plan-mount'];

                        plan = plan - res['count'];

                        var ctx = "myChart1"; // id блока
                        var chart = new Chart(ctx, {
                            // The type of chart we want to create
                            type: 'pie',
//                            type: 'doughnut',

                            // The data for our dataset
                            data: {
                                labels: [
                                    "План на месяц",
                                    "Выполнено до 40 лет",
                                    "Выполнено после 40 лет",
                                ],
                                datasets: [{
                                    label: name,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.9)',
                                        'rgba(34, 150, 240, 0.8)',
                                        'rgba(240, 226, 34, 0.8)',
                                    ],
                                    data: [
                                        res['plan-mount'],
                                        res['plan-mount-before'],
                                        res['plan-mount-after'],
                                    ],
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
    } // получить профосмотры

    function getReportDate(input,name,dateOne,dateTwo) {
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports-diagram-year-date',
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input,
                dateOne:dateOne,
                dateTwo:dateTwo
            },
            success: function (res) {
                // успешно выполнено
                console.log(JSON.stringify(res));
                var mess = '' +
                    '<div  class="col-sm-6"><h2 class="text-center">План на год</h2><canvas id="myChart" width="400" height="200"></canvas></div>' +
                    '<div  class="col-sm-6"><h2 class="text-center">План на месяц</h2><canvas id="myChart1" width="400" height="200"></canvas></div>' +
                    '';
                $('.resoultReports').hide().fadeIn(500).html(mess);

                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "План",
                            res['year']
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                res['plan-year'],
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
                    url: '/monitoring-old/reports-diagram-mount-date', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        console.log(JSON.stringify(res));
                        var plan = res['plan-mount'];
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
                    beforeSend: function (res) {
                        // Делаем анимацию видимой
                        $(".loading").css({"display": "inline"});
                    },
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
            beforeSend: function (res) {
                // Делаем анимацию видимой
                $(".loading").css({"display": "inline"});
            },
            complete: function () {
                // скрываем анимацию
                $(".loading").css({"display": "none"});
            },
            error: function(request, status, error, html) {
                var statusCode = request.status; // вот он код ответа
                $("#error_found_result").html("Error: " + statusCode);
            }
        });
    } // получить отчет если это не неотложная помощь

    function getReportNeotDate(input,name,dateOne,dateTwo) { // получить отчет только по неотложно помощи

        var mess = '' +
            '<div  class="col-sm-6"><h2 class="text-center">План на год</h2><canvas id="myChart" width="400" height="200"></canvas></div>' +
            '<div  class="col-sm-6"><h2 class="text-center">План на месяц</h2><canvas id="myChart1" width="400" height="200"></canvas></div>'
        // $('.modal-dialog').css({'margin':'50px 3%'});
        $('.resoultReports').hide().fadeIn(500).html(mess);
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports-diagram-year-date', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input,
                dateOne:dateOne,
                dateTwo:dateTwo
            },
            success: function (res) {
                // успешно выполнено
                console.log(JSON.stringify(res));
                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "План на " + res['year'],
                            "Выполнено" +"\n" + "год",
                            "Взрослые",
                            "Дети",
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                res['plan-year'],
                                res['count'],
                                res['plan-year-vzr'],
                                res['plan-year-det'],
                            ],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.9)',
                                'rgba(51, 139, 46, 0.8)',
                                'rgba(34, 150, 240, 0.8)',
                                'rgba(240, 226, 34, 0.8)',
                            ],
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
                    url: '/monitoring-old/reports-diagram-mount-date', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        // успешно выполнено
                        console.log(JSON.stringify(res));
                        // var plan = Math.round(res['plan_year']/12);
                        var plan = res['plan-mount'];

                        plan = plan - res['count'];

                        var ctx = "myChart1"; // id блока
                        var chart = new Chart(ctx, {
                            // The type of chart we want to create
                            type: 'pie',
//                            type: 'doughnut',

                            // The data for our dataset
                            data: {
                                labels: [
                                    "Не выполнено",
                                    "Взрослые",
                                    "Дети",
                                ],
                                datasets: [{
                                    label: name,
                                    backgroundColor: [
                                        // 'rgba(51, 139, 46, 0.8)',
                                        'rgba(255, 99, 132, 0.9)',
                                        'rgba(34, 150, 240, 0.8)',
                                        'rgba(240, 226, 34, 0.8)',
                                    ],
                                    data: [
                                        // res['count'],
                                        plan,
                                        res['plan-mount-vzr'],
                                        res['plan-mount-det'],
                                    ],
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
    } // получить неотложку

    function getReportProfDate(input,name,dateOne,dateTwo) { // получить отчет только по неотложно помощи

        var mess = '' +
            '<div  class="col-sm-6"><h2 class="text-center">План на год</h2><canvas id="myChart" width="400" height="200"></canvas></div>' +
            '<div  class="col-sm-6"><h2 class="text-center">План на месяц</h2><canvas id="myChart1" width="400" height="200"></canvas></div>' +
            '<div  class="col-sm-12"><h2 class="text-center">Исход осмотров</h2><div id="myChart3"></div></div>'
        // $('.modal-dialog').css({'margin':'50px 3%'});
        $('.resoultReports').hide().fadeIn(500).html(mess);
        $.ajax({
            type: "POST",
            url: '/monitoring-old/reports-diagram-year-date', // куда шлем запрос
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input,
                dateOne:dateOne,
                dateTwo:dateTwo
            },
            success: function (res) {
                // успешно выполнено

                console.log(JSON.stringify(res));
                var table = '<table class="table table-hover">' +
                    '<th>Год</th>' +
                    '<th>Количество</th>' +
                    '<th>Наименование</th>';

                $.each(res['plan-year-table'], function (key, value) {
                    table += '<tr>'+
                        '<td>'+ value.year +'</td>'+
                        '<td>'+ value.count +'</td>'+
                        '<td>'+ value.result +'</td>'+
                        '</tr>';
                });
                table += '</table>';
                $('#myChart3').hide().fadeIn(500).html(table);

                var ctx = "myChart"; // id блока
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: [
                            "План на " + res['plan-year-table'][0]['year'],
                            "Выполнено" +"\n" + "год",
                            "До 40 лет",
                            "После 40 лет",
                        ],
                        datasets: [{
                            label: name,
                            data: [
                                res['plan-year'],
                                +res['plan-year-before'] + +res['plan-year-after'],
                                res['plan-year-before'],
                                res['plan-year-after'],
                            ],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.9)',
                                'rgba(51, 139, 46, 0.8)',
                                'rgba(34, 150, 240, 0.8)',
                                'rgba(240, 226, 34, 0.8)',
                            ],
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
                    url: '/monitoring-old/reports-diagram-mount-date', // куда шлем запрос
                    cache: false,
                    dataType: 'json',
                    data: {
                        reportsDiagramMount: input
                    },
                    success: function (res) {
                        // успешно выполнено
                        console.log(JSON.stringify(res));
                        // var plan = Math.round(res['plan_year']/12);
                        var plan = res['plan-mount'];

                        plan = plan - res['count'];

                        var ctx = "myChart1"; // id блока
                        var chart = new Chart(ctx, {
                            // The type of chart we want to create
                            type: 'pie',
//                            type: 'doughnut',

                            // The data for our dataset
                            data: {
                                labels: [
                                    "План на месяц",
                                    "Выполнено до 40 лет",
                                    "Выполнено после 40 лет",
                                ],
                                datasets: [{
                                    label: name,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.9)',
                                        'rgba(34, 150, 240, 0.8)',
                                        'rgba(240, 226, 34, 0.8)',
                                    ],
                                    data: [
                                        res['plan-mount'],
                                        res['plan-mount-before'],
                                        res['plan-mount-after'],
                                    ],
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
    } // получить профосмотры

    function getGeriatriaStac(input,name,dateOne,dateTwo) {
        $.ajax({
            type: "POST",
            url: '/monitoring-old/get-geriatria-stac',
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input,
                dateOne:dateOne,
                dateTwo:dateTwo
            },
            success: function (res) {
                // успешно выполнено
                // console.log(JSON.stringify(res));
                $('#myModalLabel').text(name);
                $('.resoultReports').hide().fadeIn(500).html(res);

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
                console.log('Error:',statusCode)
            }
        })
    } // Получить отчет по Гериатрии стационар

    function getGeriatriaPol(input,name,dateOne,dateTwo) {
        $.ajax({
            type: "POST",
            url: '/monitoring-old/get-geriatria-pol',
            cache: false,
            dataType: 'json',
            data: {
                reportsDiagramYear: input,
                dateOne:dateOne,
                dateTwo:dateTwo
            },
            success: function (res) {
                // успешно выполнено
                console.log(JSON.stringify(res));
                $('#myModalLabel').text(name);
                $('.resoultReports').hide().fadeIn(500).html(res);

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
                console.log('Error:',statusCode)
            }
        })
    } // Получить отчет по Гериатрии поликлиника



    function getsettingReportsDate(){
        var url = '/monitoring/get-setting-reports-date'
        fetch(url)
            .then(function (responce) {
                return responce.json()
            })
            .then(function (value) {
                console.log(value)
            })
    } // получаем настройку на выбор дыты у всех отчетов (Недоделанный модуль, после того как начал делать выяснилось что он не нужен, так и зависло)

});
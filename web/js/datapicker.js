/* === Подключаем календарь=== */
var joseOne = document.getElementById('date-jose-one');
var joseTwo = document.getElementById('date-jose-two');
var monitoringOne = document.getElementById('date-monitoring-one');
var monitoringTwo = document.getElementById('date-monitoring-two');
var dateregister = document.getElementById('dateregister');
$( function() {



    if(joseOne || joseTwo || monitoringOne || monitoringTwo || dateregister){
        $( "#date-jose-one" ).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat:"yy-mm-dd"});

        // ЖОЗ на вкладке монитор в модальном окне запись на ЗОЖ
        $( "#date-jose-two" ).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat:"yy-mm-dd"});

        // Вкладка мониторинг
        $( "#date-monitoring-one" ).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat:"yy-mm-dd"});
        $( "#date-monitoring-two" ).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat:"yy-mm-dd"});

        $( "#dateregister" ).datepicker({
            showOtherMonths: true,
            selectOtherMonths: true,
            dateFormat:"yy-mm-dd"});


    }



} );

/**
 * Created by Belyakov on 11.10.2019.
 */

// Выделяем меню если оно активное
$('.nav-pills li a').each(function() {

    if($(this).attr('href') == window.location.pathname){
        $(this).parent().addClass('active');
    }
    if(window.location.pathname == '/' ){
        $('.nav-pills li:first-child').addClass('active');
    } // если стартовая страница
});

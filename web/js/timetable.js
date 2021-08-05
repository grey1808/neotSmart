// перемещаем выбранный параетр в ссылку
var href = $('.timelistid').attr('href');
var params = href.split('?');
params[1] = '?id=' + $('.category-time select').val()+ '';
$('.timelistid').attr('href',params[0]+params[1])
$('.category-time select').change(function(){
    var href = $('.timelistid').attr('href');
    var params = href.split('?');
    params[1] = '?id=' + $(this).val()+ '';
    $('.timelistid').attr('href',params[0]+params[1])

})// перемещаем выбранный параетр в ссылку при клике

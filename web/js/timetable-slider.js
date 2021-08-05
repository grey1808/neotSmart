$('.carousel').carousel({
    interval: 10000
}) // Интервал между слайдами

//
// $('.active tr:not(:first)').each(function(i){
//     var row = $(this);
//     setTimeout(function() {
//         row.toggleClass('animated flipInX');
//     }, 500*i);
//     row.removeClass("animated flipInX");
// })
//
// // $('.carousel').on('slide.bs.carousel', function () {
// $('.carousel').on('slid.bs.carousel', function () {
//     $('.active tr:not(:first)').each(function(i){
//         var row = $(this);
//         setTimeout(function() {
//             row.toggleClass('animated flipInX');
//         }, 500*i);
//         row.removeClass("animated flipInX");
//     })
// }) // Это событие срабатывает во время вызова метода







setInterval(function() {window.location.reload();}, 36000);
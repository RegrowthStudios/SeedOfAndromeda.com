$(document).ready(function () {
    var elem;
    var elems = $('.video');

    elem = $(elems[0]);
    initSrc = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";
    $('.enlarged-iframe').attr('src', initSrc);
    $('.temp-iframe').attr('src', initSrc);

    elems.click(function () {
        elem = $(this);
        var src = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";
        $('.temp-iframe').attr('src', src);
        $('.enlarged-iframe').fadeOut(0, function () {
            $('.enlarged-iframe').attr('src', src).fadeIn(0);
        });
    })

    $('.vid-next').click(function () {
        var ind = elems.index(elem);
        if (ind == (elems.length - 1)) {
            elem = $(elems.get(0));
            var src = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";
            $('.temp-iframe').attr('src', src);
            $('.enlarged-iframe').fadeOut(0, function () {
                $('.enlarged-iframe').attr('src', src).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind + 1));
            var src = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";
            $('.temp-iframe').attr('src', src);
            $('.enlarged-iframe').fadeOut(0, function () {
                $('.enlarged-iframe').attr('src', src).fadeIn(0);
            });
        }
    });


    $('.vid-prev').click(function () {
        var ind = elems.index(elem);

        if (ind == 0) {
            elem = $(elems.get(elems.length - 1));
            index = elems.index(elem);
            var src = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";
            $('.temp-iframe').attr('src', src);
            $('.enlarged-iframe').fadeOut(0, function () {
                $('.enlarged-iframe').attr('src', src).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind - 1));
            index = elems.index(elem);
            var src = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";
            $('.temp-iframe').attr('src', src);
            $('.enlarged-iframe').fadeOut(0, function () {
                $('.enlarged-iframe').attr('src', src).fadeIn(0);
            });
        }
    })

});
$(document).ready(function () {
    var elem, index;
    var elems = $('.image');

    elem = $(elems[0]);
    $('.enlarged-image').attr('src', $(elems[0]).attr('src'));
    $('.temp-image').attr('src', $(elems[0]).attr('src'));

    elems.click(function () {
        elem = $(this)
        var src = elem.attr('src');
        $('.temp-image').attr('src', src);
        $('.enlarged-image').fadeOut(0, function () {
            $('.enlarged-image').attr('src', src).fadeIn(0);
        });
    })

    $('.img-next').click(function () {
        var ind = elems.index(elem);
        if (ind == (elems.length - 1)) {
            elem = $(elems.get(0));
            index = 0;
            var src = elem.attr('src');
            $('.temp-image').attr('src', src).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', src).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind + 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            $('.temp-image').attr('src', src).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', src).fadeIn(0);
            });
        }
    });


    $('.img-prev').click(function () {
        var ind = elems.index(elem);

        if (ind == 0) {
            elem = $(elems.get(elems.length - 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            $('.temp-image').attr('src', src).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', src).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind - 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            $('.temp-image').attr('src', src).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', src).fadeIn(0);
            });
        }
    })

});
$(document).ready(function () {
    var elem, index;
    var elems = $('.image');

    elem = $(elems[0]);
    var thumbpath = $(elems[0]).attr('src');
    var fullimg = thumbpath.substring(0, thumbpath.indexOf('_thumb_')) +'_thumb_700x480' + thumbpath.substring(thumbpath.length-4);
    
    $('.enlarged-image').attr('src', fullimg);
    $('.temp-image').attr('src', fullimg);

    elems.click(function () {
        elem = $(this)
        var src = elem.attr('src');
        var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
        $('.temp-image').attr('src', fullimg);
        $('html, body').animate({
            scrollTop: $("#image-frame-inner").offset().top - 100
        }, 1000);
        $('.enlarged-image').fadeOut(0, function () {
            $('.enlarged-image').attr('src', fullimg).fadeIn(0);
        });
    })

    $('.img-next').click(function () {
        var ind = elems.index(elem);
        if (ind == (elems.length - 1)) {
            elem = $(elems.get(0));
            index = 0;
            var src = elem.attr('src');
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind + 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        }
    });


    $('.img-prev').click(function () {
        var ind = elems.index(elem);

        if (ind == 0) {
            elem = $(elems.get(elems.length - 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind - 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        }
    })

});
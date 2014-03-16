$(document).ready(function () {
    var elem, index;
    var elems = $('.image');
    var ctrlsLocked = false;

    elem = $(elems[0]);
    var thumbpath = $(elems[0]).attr('src');
    $('.enlarged-image').attr('src', thumbpath);
    $('.temp-image').attr('src', thumbpath);
    var fullimg = thumbpath.substring(0, thumbpath.indexOf('_thumb_')) +'_thumb_700x480' + thumbpath.substring(thumbpath.length-4);
    $('#screenshotlink').attr('href', (fullimg
			.substring(0, fullimg.indexOf('_thumb_')) + fullimg
			.substring(fullimg.length - 4)));
    $('.enlarged-image').attr('src', fullimg);
    $('.temp-image').attr('src', fullimg);

    elems.click(function () {
        elem = $(this);
        var src = elem.attr('src');
        $('.temp-image').attr('src', src);
        var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
        $('.temp-image').attr('src', fullimg);
        $('#screenshotlink').attr('href', (fullimg
				.substring(0, fullimg.indexOf('_thumb_')) + fullimg
				.substring(fullimg.length - 4)));
        $('html, body').animate({
            scrollTop: $("#image-frame-inner").offset().top - 150
        }, 1000);
        $('.enlarged-image').fadeOut(0, function () {
            $('.enlarged-image').attr('src', fullimg).fadeIn(0);
        });
        
        index = elems.index(elem);
        preload(index - 1);
        preload(index + 1);
    });

    $('.img-next').click(function () {
    	if(ctrlsLocked) { return; }
        var ind = elems.index(elem);
        if (ind == (elems.length - 1)) {
            elem = $(elems.get(0));
            index = 0;
            var src = elem.attr('src');
            $('.temp-image').attr('src', src);
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('#screenshotlink').attr('href', (fullimg
    				.substring(0, fullimg.indexOf('_thumb_')) + fullimg
    				.substring(fullimg.length - 4)));
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind + 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            $('.temp-image').attr('src', src);
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('#screenshotlink').attr('href', (fullimg
    				.substring(0, fullimg.indexOf('_thumb_')) + fullimg
    				.substring(fullimg.length - 4)));
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        }
        
        index = elems.index(elem);
        preload(index - 1);
        preload(index + 1);     
        lockCtrls();
    });


    $('.img-prev').click(function () {
    	if(ctrlsLocked) { return; }
        var ind = elems.index(elem);
        if (ind == 0) {
            elem = $(elems.get(elems.length - 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            $('.temp-image').attr('src', src);
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('#screenshotlink').attr('href', (fullimg
    				.substring(0, fullimg.indexOf('_thumb_')) + fullimg
    				.substring(fullimg.length - 4)));
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        } else {
            elem = $(elems.get(ind - 1));
            index = elems.index(elem);
            var src = elem.attr('src');
            $('.temp-image').attr('src', src);
            var fullimg = src.substring(0, src.indexOf('_thumb_')) +'_thumb_700x480' +  src.substring(src.length-4);
            $('#screenshotlink').attr('href', (fullimg
    				.substring(0, fullimg.indexOf('_thumb_')) + fullimg
    				.substring(fullimg.length - 4)));
            $('.temp-image').attr('src', fullimg).css('display', 'none').show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
            $('.enlarged-image').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlarged-image').attr('src', fullimg).fadeIn(0);
            });
        }
        
        index = elems.index(elem);
        preload(index - 1);
        preload(index + 1);
        lockCtrls();
    });
    
    function preload(imgIndex) {
    	    	var image = $(elems.get(imgIndex));
    	    	var src = image.prop('src').replace('_thumb_202x162', '_thumb_700x480');
    	    	(new Image()).src = src;
    }
    
    function lockCtrls() {
    	ctrlsLocked = true;
    	setTimeout(function() {
    		ctrlsLocked = false;
    	}, 500);
    }

});
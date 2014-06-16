$(document).ready(function () {
    $.getJSON('https://gdata.youtube.com/feeds/api/videos?author=UCMlW2qG20hcFYo06rcit4CQ&max-results=48&v=2&alt=jsonc&orderby=published', function (data) {
        for (var i = 0; i < data.data.items.length; i++) {
            //console.log(data.data.items[i].title); // title
            //console.log(data.data.items[i].description); // description
            //console.log(data.data.items[i]);
            var check = (i % 3) + 1;
            var html = '<div class="video-col tri-col-' + check + ' empty"><img src="' + data.data.items[i].thumbnail.hqDefault + '" data-id="' + data.data.items[i].id + '" title="' + data.data.items[i].title + '" class="video" /><div class="video-play" title="' + data.data.items[i].title + '"></div></div>';
            $('.final_content_border').append(html);
        }
        var elem;

        var elems = $('.video');



        elem = $(elems[0]);

        initSrc = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";

        $('.enlarged-iframe').attr('src', initSrc);

        $('.temp-iframe').attr('src', initSrc);

        $('.video-play').click(function () {
            $(this).parent().find('.video').click();
        });


        elems.click(function () {

            elem = $(this);

            var src = "https://www.youtube.com/embed/" + elem.attr('data-id') + "?wmode=transparent";

            $('.temp-iframe').attr('src', src);
            $('html, body').animate({
                scrollTop: $("#video-frame-inner").offset().top - 150
            }, 1000);

            $('.enlarged-iframe').fadeOut(0, function () {

                $('.enlarged-iframe').attr('src', src).fadeIn(0);

            });

        });



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

        });
    });

});
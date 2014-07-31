$(document).ready(function () {
    var images = $(".img");
    if (images.length > 0) {
        var html = '';
        $.each(images, function (i, v) {
            var src = $(v).attr('src');
            html += '<div class="media-wrapper card-wrapper" style="display: none;"><a href="' + src + '" id="screenshotlink" data-lightbox="screenshot" title="screenshot"><div class="card-background" style="background-image: url(\'' + src.substring(0, (src.length - 4)) + '_thumb_781x398.jpg\');"></div></a></div>';
        });
        $("#blog-image-slider .media-slider-frame").append(html);

        var elems = $("#blog-image-slider .media-wrapper");
        var sliderFrame = $("#blog-image-slider .media-slider-frame");
        var slider = new MediaSlider(elems, sliderFrame, 3000);
        $("#blog-image-slider").show();
    }
});
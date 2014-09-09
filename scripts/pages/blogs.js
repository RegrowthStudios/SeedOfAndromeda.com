$(document).ready(function () {
    var images = $(".img");
    if (images.length > 0) {
        var html = '';
        $.each(images, function (i, v) {
            var src = $(v).attr('src');
            html += '<div class="media-wrapper card-wrapper" style="display: none;"><a href="' + src + '" id="screenshotlink" data-lightbox="screenshot" title="screenshot"><div class="card-background" style="background-image: url(\'' + src.substring(0, (src.length - 4)) + '_thumb_781x398.jpg\');"></div></a></div>';
        });
        var frameWrapper = $("#blog-image-slider");
        frameWrapper.find(".media-slider-frame").append(html);

        var slider = new MediaSlider(frameWrapper, 3000);
        frameWrapper.fadeIn();
    }
});
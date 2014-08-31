//Populate Screenshots and Initiate Screenshots Slider
var slider = null;
$(document).ready(function () {
    loadMoreScreenshots(1, false);
    var elems = $("#screenshot-viewer .media-wrapper");
    var sliderFrame = $("#screenshot-viewer .media-slider-frame");
    slider = new MediaSlider(elems, sliderFrame, 3000);
});

function loadMoreScreenshots(pid, async) {
    var images = new Array();
    $.ajax({
        url: "../image-loader.php?pid=" + pid,
        type: "POST",
        success: function (msg) {
            images = JSON.parse(msg);

            var frame = $(".media-slider-frame");
            var screenshotColumn = $("#screenshots");
            $.each(images, function (i, v) {
                var htmlWrapper = '<div class="media-wrapper card-wrapper" style="display: none;"><a href="' + v["url"] + '" id="screenshotlink" data-lightbox="screenshot" title="screenshot"><div class="card-background" style="background-image: url(\'' + v["url"].substring(0, v["url"].lastIndexOf(".")) + '_thumb_781x398.jpg\');"></div></a></div>';
                var htmlColumn = '<div class="col quad-col-1"><img class="img small-wide screenshot" src="' + v["url"].substring(0, v["url"].lastIndexOf(".")) + '_thumb_213x128.jpg" /></div>';

                frame.append(htmlWrapper);
                screenshotColumn.append(htmlColumn);
            });

            var screenshots = $(".screenshot");
            $.each(screenshots, function (i, v) {
                $(v).click(function () {
                    if (slider.ctrlsLocked == false) {
                        slider.lockCtrls();
                        slider.setItem(i);
                    }
                });
            });

            if (slider != null) {
                slider.updateElems("#screenshot-viewer .media-wrapper");
            }
        },
        async: async
    });
}

var nextPID = 2;
$(window).on('scroll', function () {
    var docHeightWindowHeightDiff = $(document).height() - $(window).height();
    var yScrollPos = window.pageYOffset;
    var yScrollPosReq = 50; // Pixels from bottom of document

    if ((docHeightWindowHeightDiff - yScrollPos) <= yScrollPosReq) {
        loadMoreScreenshots(nextPID, true);
        nextPID++;
    }
});
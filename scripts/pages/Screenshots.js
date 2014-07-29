// Initiate Screenshots Slider
var slider;
$(document).ready(function () {
    var elems = $("#screenshot-viewer .media-wrapper");
    var sliderFrame = $("#screenshot-viewer .media-slider-frame");
    slider = new MediaSlider(elems, sliderFrame, 3000);
});

// Handle Specific Screenshot Selection
$(document).ready(function () {
    var screenshots = $(".screenshot");
    $.each(screenshots, function (i, v) {
        $(v).click(function () {
            if (slider.ctrlsLocked == false) {
                slider.lockCtrls();
                slider.setItem(i);
            }
        });
    });
});
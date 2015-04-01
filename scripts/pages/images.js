var frameWrapper;
var frame;
var slider;
$(document).ready(function () {
    frameWrapper = $('#image-viewer');
    frame = frameWrapper.find(".media-slider-frame");

    slider = new MediaSlider(frameWrapper, 3000);

    var spinnerWrapper = new SpinnerWrapper();
    spinnerWrapper.spinner.spin(frame[0]);

    var gameplayImagesWrapper = $("#gameplay-images .images-outer-wrapper");
    pagifyGameplay = new Pagify(gameplayImagesWrapper, "#gameplay-images .images", "loaders/image-loader.php", 1, function (pgData) {
        if (pgData == 0) {
            gameplayImagesWrapper.parent().hide();
            return -1;
        }
        return prepareImages(pgData);
    }, function () { refreshSlider(); }, { "category": "GAMEPLAY" });
    var conceptImagesWrapper = $("#concept-images .images-outer-wrapper");
    pagifyConcept = new Pagify(conceptImagesWrapper, "#concept-images .images", "loaders/image-loader.php", 1, function (pgData) {
        if (pgData == 0) {
            conceptImagesWrapper.parent().hide();
            return -1;
        }
        return prepareImages(pgData);
    }, function () { refreshSlider(); }, { "category": "CONCEPT" });
});

function prepareImages(pgData) {
    var totalHtmlColumn = '<div class="images" style="display:none;">';
    $.each(pgData, function (i, v) {
        totalHtmlColumn += '<div class="col quad-col-1"><img class="img small-wide image" src="' + v["url"].substring(0, v["url"].lastIndexOf(".")) + '_thumb_213x128.jpg" data-url="' + v["url"] + '" data-title="' + v["title"] + '" data-desc="' + v["description"] + '" /></div>';
    });
    totalHtmlColumn += '</div>';
    return totalHtmlColumn;
};

function refreshSlider() {
    var totalHtmlWrapper = '';
    var imgs = $('.image');
    $.each(imgs, function (i, v) {
        var _v = $(v);
        totalHtmlWrapper += '<div class="media-wrapper card-wrapper" style="display: none;"><a href="' + _v.data("url") + '" id="screenshotlink" data-lightbox="screenshot" title="' + _v.data("title") + ' - ' + _v.data("desc") + '"><div class="card-background" style="background-image: url(\'' + _v.data("url").substring(0, _v.data("url").lastIndexOf(".")) + '_thumb_781x398.jpg\');"></div></a></div>';
    });
    frameWrapper.find(".media-wrapper").remove();
    frame.append(totalHtmlWrapper);
    if (slider != null) {
        slider.updateElems();
        slider.setItem(0);
        slider.bindItemsToSlider($(".image"));
    }
};
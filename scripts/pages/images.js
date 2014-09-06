var pagifyGameplay = null;
var pagifyConcept = null;
var slider = null;

var frame = null;
var gameplayImagesWrapper = null;
var conceptImagesWrapper = null;
$(document).ready(function () {
    frame = $("#image-viewer .media-slider-frame");
    gameplayImagesWrapper = $("#gameplay-images .images-outer-wrapper");
    conceptImagesWrapper = $("#concept-images .images-outer-wrapper");

    pagifyGameplay = new Pagify(gameplayImagesWrapper, "#gameplay-images .images", "loaders/image-loader.php", 1, function (pgData) {
        if (pgData == 0) {
            gameplayImagesWrapper.parent().hide();
            return -1;
        }
        return prepareImages(pgData);
    }, function () { refreshSlider(); }, { "category": "GAMEPLAY" });
    pagifyConcept = new Pagify(conceptImagesWrapper, "#concept-images .images", "loaders/image-loader.php", 1, function (pgData) {
        if (pgData == 0) {
            conceptImagesWrapper.parent().hide();
            return -1;
        }
        return prepareImages(pgData);
    }, function () { refreshSlider(); }, { "category": "CONCEPT" });

    var elems = $("#image-viewer .media-wrapper");
    var sliderFrame = $("#image-viewer .media-slider-frame");
    slider = new MediaSlider(elems, sliderFrame, 3000);
    slider.bindItemsToSlider(".image");
});

function prepareImages(pgData) {
    var totalHtmlColumn = '';
    $.each(pgData, function (i, v) {
        totalHtmlColumn += '<div class="col quad-col-1"><img class="img small-wide image" src="' + v["url"].substring(0, v["url"].lastIndexOf(".")) + '_thumb_213x128.jpg" data-url="' + v["url"] + '" data-title="' + v["title"] + '" data-desc="' + v["description"] + '" /></div>';
    });
    return totalHtmlColumn;
};

function refreshSlider() {
    var totalHtmlWrapper = '';
    var imgs = $('.image');
    console.log(imgs);
    $.each(imgs, function (i, v) {
        var _v = $(v);
        console.log(_v.data("title"));
        totalHtmlWrapper += '<div class="media-wrapper card-wrapper" style="display: none;"><a href="' + _v.data("url") + '" id="screenshotlink" data-lightbox="screenshot" title="' + _v.data("title") + ' - ' + _v.data("description") + '"><div class="card-background" style="background-image: url(\'' + _v.data("url").substring(0, _v.data("url").lastIndexOf(".")) + '_thumb_781x398.jpg\');"></div></a></div>';
    });
    $(".media-wrapper").remove();
    frame.append(totalHtmlWrapper);
    if (slider != null) {
        slider.updateElems("#image-viewer .media-wrapper");
        slider.setItem(0);
        slider.bindItemsToSlider(".image");
    }
};
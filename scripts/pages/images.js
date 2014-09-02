var pagifyGameplay = null;
var pagifyConcept = null;
var slider = null;

var frame = null;
var gameplayImageRow = null;
var conceptImageRow = null;
$(document).ready(function () {
    frame = $("#image-viewer .media-slider-frame");
    gameplayImageRow = $("#gameplay-images");
    conceptImageRow = $("#concept-images");

    pagifyGameplay = new Pagify("html", "body", "loaders/image-loader.php");
    pagifyConcept = new Pagify("html", "body", "loaders/image-loader.php");
    loadImages(1, "GAMEPLAY", pagifyGameplay);
    loadImages(1, "CONCEPT", pagifyConcept);

    var elems = $("#image-viewer .media-wrapper");
    var sliderFrame = $("#image-viewer .media-slider-frame");
    slider = new MediaSlider(elems, sliderFrame, 3000);
});

function loadImages(pid, category, pagify) {
    var images = pagify.getPage(pid, { "category": category });
    var noneReturned = true;
    console.log(images);
    $.each(images, function (i, v) {
        var htmlWrapper = '<div class="media-wrapper card-wrapper" style="display: none;"><a href="' + v["url"] + ' - ' + v["description"] + '" id="screenshotlink" data-lightbox="screenshot" title="' + v["title"] + '"><div class="card-background" style="background-image: url(\'' + v["url"].substring(0, v["url"].lastIndexOf(".")) + '_thumb_781x398.jpg\');"></div></a></div>';
        var htmlColumn = '<div class="col quad-col-1"><img class="img small-wide image" src="' + v["url"].substring(0, v["url"].lastIndexOf(".")) + '_thumb_213x128.jpg" /></div>';

        frame.append(htmlWrapper);
        if (category == "GAMEPLAY") {
            gameplayImageRow.append(htmlColumn);
        } else if (category == "CONCEPT") {
            conceptImageRow.append(htmlColumn);
        }
        noneReturned = false;
    });
    console.log(noneReturned);

    if (noneReturned && pid == 1) {
        if (category == "GAMEPLAY") {
            gameplayImageRow.hide();
        } else if (category == "CONCEPT") {
            conceptImageRow.hide();
        }
    } else if (noneReturned) {
        return -1;
    } else {
        var imgs = $(".image");
        $.each(imgs, function (i, v) {
            $(v).click(function () {
                slider.lockCtrls();
                slider.setItem(i);
            });
        });

        if (slider != null) {
            slider.updateElems("#image-viewer .media-wrapper");
        }
    }
};
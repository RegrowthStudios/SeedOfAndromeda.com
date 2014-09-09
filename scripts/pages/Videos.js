var frameWrapper;
var frame;
var slider
$(document).ready(function () {
    frameWrapper = $('#video-viewer');
    frame = frameWrapper.find(".media-slider-frame");

    slider = new MediaSlider(frameWrapper, 3000);

    var spinnerWrapper = new SpinnerWrapper();
    spinnerWrapper.spinner.spin(frame[0]);

    var featuredVideosWrapper = $("#featured-videos .videos-outer-wrapper");
    pagifyFeatured = new Pagify(featuredVideosWrapper, "#featured-videos .videos", "loaders/video-loader.php", 1, function (pgData) {
        if (pgData <= 0) {
            featuredVideosWrapper.parent().hide();
            return -1;
        }
        return prepareVideos(pgData);
    }, function () { refreshSlider(); }, { "category": "FEATURED" });
    var streamVideosWrapper = $("#stream-videos .videos-outer-wrapper");
    pagifyStream = new Pagify(streamVideosWrapper, "#stream-videos .videos", "loaders/video-loader.php", 1, function (pgData) {
        if (pgData <= 0) {
            streamVideosWrapper.parent().hide();
            return -1;
        }
        return prepareVideos(pgData);
    }, function () { refreshSlider(); }, { "category": "STREAM" });
    var allVideosWrapper = $("#all-videos .videos-outer-wrapper");
    pagifyAll = new Pagify(allVideosWrapper, "#all-videos .videos", "loaders/video-loader.php", 1, function (pgData) {
        return prepareVideos(pgData);
    }, function () { refreshSlider(); });
});

function prepareVideos(pgData) {
    var totalHtmlColumn = '<div class="videos" style="display:none;">';
    $.each(pgData, function (i, v) {
        totalHtmlColumn += '<div class="col tri-col-1"><img src="' + v["thumb_url"] + '" data-id="' + v["vid_id"] + '" title="' + v["title"] + '" class="img medium-wide video" /></div>';
    });
    totalHtmlColumn += '</div>';
    return totalHtmlColumn;
};

function refreshSlider() {
    var totalHtmlWrapper = '';
    var vids = $(".video");
    $.each(vids, function (i, v) {
        var _v = $(v);
        totalHtmlWrapper += '<div class="media-wrapper card-wrapper" style="display: none;"><div class="video-title"><h2 class="indent-large">' + _v.attr("title") + '</h2></div><div class="card-background" style="background-image: url(\'' + _v.attr("src") + '\')" data-id="' + _v.data("id") + '"></div><div class="video-play"></div></div>';
    });
    frameWrapper.find(".media-wrapper").remove();
    frame.append(totalHtmlWrapper);
    if (slider != null) {
        slider.updateElems();
        slider.setItem(0);
        slider.bindItemsToSlider($(".video"));
    }
    // Correct Video Play Positioning
    var videoPlays = frameWrapper.find(".video-play");
    {
        var frameWidth = frame.width();
        var videoPlayWidth = videoPlays.first().width();
        var leftVal = ((frameWidth - videoPlayWidth) / 2);
        $.each(videoPlays, function (i, v) {
            $(v).css("left", leftVal + "px");
        });
    }
    // Handle Video Play Interaction
    {
        var cover = $("#video-cover");
        $.each(videoPlays, function (i, v) {
            $(v).click(function () {
                var vidID = $(v).siblings().last().data("id");
                var vidURL = "https://www.youtube.com/embed/" + vidID + "?wmode=transparent";
                var video = "<div class='text' style='width:" + ($(window).width() * 0.6) + "px;margin-top:" + (($(window).height() - ($(window).width() * 0.6 * 0.5)) / 2) + "px;display:inline-block;padding:0;'><iframe width='" + ($(window).width() * 0.6) + "' height='" + ($(window).width() * 0.6 * 0.5) + "' style='margin-bottom:-5px;' src='" + vidURL + "' frameborder='0' allowfullscreen></iframe></div>";
                cover.children().remove();
                cover.append(video).fadeIn();
                setTimeout(function () {
                    slider.pauseSlideshow();
                }, 10);
            });
        });
        cover.click(function (e) {
            if (($(e.target).parents('#video-cover').length) * (-1)) {
                return;
            }
            cover.children().remove();
            slider.playSlideshow();
        });
    }
};
// Prepare and Display Video Thumbnails
$(document).ready(function () {
    $.getJSON('https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=48&playlistId=UUMlW2qG20hcFYo06rcit4CQ&key=AIzaSyDDvpdu4_LQ0T07p8siXC2-pCUQXmi6tLA', function (data) {
        for (var i = 0; i < data.pageInfo.resultsPerPage; ++i) {
            var htmlWrapper = '<div class="media-wrapper card-wrapper" style="display: none;"><div class="video-title"><h2 class="indent-large">' + data.items[i].snippet.title + '</h2></div><div class="card-background" style="background-image: url(\'' + ((data.items[i].snippet.thumbnails.maxres == 1) ? data.items[i].snippet.thumbnails.maxres.url : data.items[i].snippet.thumbnails.high.url) + '\')" data-id="' + data.items[i].snippet.resourceId.videoId + '"></div><div class="video-play"></div></div>';
            var htmlColumn = '<div class="col tri-col-1"><img src="' + data.items[i].snippet.thumbnails.high.url + '" data-id="' + data.items[i].snippet.resourceId.videoId + '" title="' + data.items[i].snippet.title + '" class="img medium-wide video" /></div>';
            $('#video-viewer .media-slider-frame').append(htmlWrapper);
            $('#videos').append(htmlColumn);
        }

        // Initiate Videos Slider
        var slider;
        {
            var elems = $("#video-viewer .media-wrapper");
            var sliderFrame = $("#video-viewer .media-slider-frame");
            slider = new MediaSlider(elems, sliderFrame, 3000);
        }

        //Correct Video Play Positioning
        var videoPlays = $("#video-viewer .video-play");
        {
            var frameWidth = $("#video-viewer .media-slider-frame").width();
            var videoPlayWidth = videoPlays.last().width();
            var leftVal = ((frameWidth - videoPlayWidth) / 2);
            $.each(videoPlays, function (i, v) {
                $(v).css("left", leftVal + "px");
            });
        }
        console.log(videoPlays);
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

        // Handle Specific Video Selection
        {
            var videos = $(".video");
            $.each(videos, function (i, v) {
                $(v).click(function () {
                    if (slider.ctrlsLocked == false) {
                        slider.lockCtrls();
                        slider.setItem(i);
                    }
                });
            });
        }
    });
});
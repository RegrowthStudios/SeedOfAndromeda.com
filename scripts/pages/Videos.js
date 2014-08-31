// Prepare and Display Video Thumbnails
$(document).ready(function () {
    $.getJSON('https://gdata.youtube.com/feeds/api/videos?author=UCMlW2qG20hcFYo06rcit4CQ&max-results=48&v=2&alt=jsonc&orderby=published', function (data) {
        console.log(data);
        if (data.data.totalItems > 0) {
            for (var i = 0; i < data.data.items.length; i++) {
                var htmlWrapper = '<div class="media-wrapper card-wrapper" style="display: none;"><div class="video-title"><h2 class="indent-large">' + data.data.items[i].title + '</h2></div><div class="card-background" style="background-image: url(\'https://i.ytimg.com/vi/' + data.data.items[i].id + '/maxresdefault.jpg\');" data-id="' + data.data.items[i].id + '"></div><div class="video-play"></div></div>';
                var htmlColumn = '<div class="col tri-col-1"><img src="' + data.data.items[i].thumbnail.hqDefault + '" data-id="' + data.data.items[i].id + '" title="' + data.data.items[i].title + '" class="img medium-wide video" /></div>';
                $('#video-viewer .media-slider-frame').append(htmlWrapper);
                $('#all-videos').append(htmlColumn);
            }
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
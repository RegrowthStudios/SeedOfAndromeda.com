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

    if ($("#single-blog").length === 0) {
        var blogListWrapperOuter = $("#blog-list .blogs-outer-wrapper");
        pagifyBlogs = new Pagify(blogListWrapperOuter, "#blog-list .blogs", "loaders/blog-loader.php", 1, function (pgData) {
            if (pgData == 0) {
                blogListWrapperOuter.parent().hide();
                return -1;
            }
            return prepareBlogs(pgData);
        }, function () {
            refreshDisqus();
            return 1;
        });
    }
});

function refreshDisqus() {
    // Undefine disquswidgets to force a refresh also on ajax reload
    window.DISQUSWIDGETS = undefined;
    $.getScript("https://" + disqus_shortname + ".disqus.com/count.js");
}

function prepareBlogs(pgData) {
    var totalHtmlBlog = '<div class="blogs" style="display:none;">';
    $.each(pgData, function (i, v) {
        var postlink = genPostLink(v);
        totalHtmlBlog += '<div class="blog-item row ng clearfix">';
            totalHtmlBlog += '<div class="header"><h2>';
                totalHtmlBlog += '<a href="/blogs/' + postlink + '">' + trimWholeWord(strip_tags(v["title"]), 60) + '</a>';
            totalHtmlBlog += '</h2></div>';
            totalHtmlBlog += '<div class="double-col-2"><div class="text"><div id="blog-post" class="clearfix"><div>';
                totalHtmlBlog += trimWholeWord(strip_tags(v["post_brief"]), 1400);
                totalHtmlBlog += '<span id="blog-post-footer">';
                    totalHtmlBlog += '<a href="/blogs/' + postlink + '">Read More...</a>';
                    if (v["disablecomments"] == 0) {
                        totalHtmlBlog += '<small> - (<a href="/blogs/' + postlink + '#disqus_thread" data-disqus-identifier="blogs-' + v["id"] + '">Comments</a>)</small>';
                    }
                totalHtmlBlog += '</span>'
            totalHtmlBlog += '</div></div></div></div>';
        totalHtmlBlog += '</div>';
    });
    totalHtmlBlog += '</div>';
    return totalHtmlBlog;
}
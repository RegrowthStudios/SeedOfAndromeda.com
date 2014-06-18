$(document).ready(function () {
    var children = $("#blog-post-body > *");
    var blogPostMaxHeight = parseInt($("#blog-post-body").css('maxHeight'), 10);
    var heightCount = 0;
    children.each(function () {
        var height = parseInt($(this).height(), 10);
        var marginBottom = parseInt($(this).css('marginBottom'), 10);
        var totalHeight = height + marginBottom;
        heightCount += totalHeight;

        if (heightCount >= blogPostMaxHeight) {
            $(this).hide();
        }
    });
});
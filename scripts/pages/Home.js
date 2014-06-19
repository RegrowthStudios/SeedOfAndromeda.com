//Hide JavaScript warning.
$(document).ready(function () {
    $('#dev-news-js-warning').hide();
});

$(document).ready(function () {
    var elem, index, automateID;
    var elems = $('.dev-news-wrapper');
    var ctrlsLocked = false;
    var slideShowPaused = false;
    var slideShowDelay = 4000;
    index = 0;

    //Initially show latest Dev News
    elem = $(elems[index]);
    elem.show();
    if (elems.length > 1) {
        //Bind handlers on hover event for each Dev News wrapper div.
        //First function handles mouse in, second mouse out.
        $.each(elems, function (i, v) {
            $(v).hover(function () {
                pauseSlideshow();
            }, function () {
                playSlideshow();
            });
        });

        $('.dev-news-control-left').click(function () {
            pauseSlideshowDelay(5000);
            if (ctrlsLocked == false) {
                previousItem();
            }
            lockCtrls();
        });

        $('.dev-news-control-right').click(function () {
            pauseSlideshowDelay(5000);
            if (ctrlsLocked == false) {
                nextItem();
            }
            lockCtrls();
        });

        function nextItem() {
            if (index != (elems.length - 1)) {
                var nextElem = $(elems[index + 1]);
                nextElem.prop("right", "-100%");
                elem.hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
                nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
                elem = nextElem;
                index++;
            } else {
                var nextElem = $(elems[0]);
                nextElem.prop("right", "-100%");
                elem.hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
                nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
                elem = nextElem;
                index = 0;
            }
        }

        function previousItem() {
            if (index != 0) {
                var nextElem = $(elems[index - 1]);
                nextElem.prop("left", "-100%");
                elem.hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
                nextElem.show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
                elem = nextElem;
                index--;
            } else {
                var nextElem = $(elems[(elems.length - 1)]);
                nextElem.prop("left", "-100%");
                elem.hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
                nextElem.show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
                elem = nextElem;
                index = (elems.length - 1);
            }
        }

        function automateSlideshow() {
            if (slideShowPaused == false) {
                nextItem();
                automateID = setTimeout(function () {
                    automateSlideshow();
                }, slideShowDelay);
            }
        }

        function playSlideshow() {
            slideShowPaused = false;
            setTimeout(function () {
                automateSlideshow();
            }, slideShowDelay);
        }

        function pauseSlideshow() {
            slideShowPaused = true;
            clearTimeout(automateID);
        }

        function pauseSlideshowDelay(timeout) {
            slideShowPaused = true;
            clearTimeout(automateID);
            setTimeout(function () {
                playSlideshow();
            }, timeout);
        }

        function lockCtrls() {
            ctrlsLocked = true;
            setTimeout(function () {
                ctrlsLocked = false;
            }, 500);
        }

        //Initiate slideshow
        (function () {
            setTimeout(function () {
                automateSlideshow();
            }, slideShowDelay);
        })();
    } else {
        $('.dev-news-control').hide();
    }
});
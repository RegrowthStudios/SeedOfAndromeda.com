//Hide JavaScript warning.
$(document).ready(function () {
    $('#dev-news-js-warning').hide();
});

$(document).ready(function () {
    var elem;
    var elems = $('.dev-news-wrapper');
    var ctrlsLocked = false;
    var slideShowPaused = false;
    var slideShowDelay = 6000;
    var slideShowPauseDelay = 7000;
    var index = 0;
    var automateID = [];

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
            pauseSlideshowDelay();
            if (ctrlsLocked == false) {
                previousItem();
            }
            lockCtrls();
        });

        $('.dev-news-control-right').click(function () {
            pauseSlideshowDelay();
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
                clearTimeouts();
                automateID[automateID.length] = setTimeout(function () {
                    automateSlideshow();
                }, slideShowDelay);
            }
        }

        function playSlideshow() {
            clearTimeouts();
            slideShowPaused = false;
            automateID[automateID.length] = setTimeout(function () {
                automateSlideshow();
            }, slideShowDelay);
        }

        function pauseSlideshow() {
            slideShowPaused = true;
        }

        function pauseSlideshowDelay() {
            slideShowPaused = true;
            setTimeout(function () {
                playSlideshow();
            }, slideShowPauseDelay);
        }

        function lockCtrls() {
            ctrlsLocked = true;
            setTimeout(function () {
                ctrlsLocked = false;
            }, 500);
        }

        function clearTimeouts() {
            for (var i = 0; i < automateID.length; ++i) {
                clearTimeout(automateID[i]);
                automateID.splice(i, 1);
                --i;
            }
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
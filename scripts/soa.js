/**
 * jQuery.browser.mobile (http://detectmobilebrowser.com/)
 *
 * jQuery.browser.mobile will be true if the browser is a mobile device
 *
 **/
(function (a) {
    (jQuery.browser = jQuery.browser || {}).mobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))
})(navigator.userAgent || navigator.vendor || window.opera);

function clear_textbox(textbox_id, textbox_value) {
    var text = $("#" + textbox_id);
    if (text.val() == textbox_value) {
        text.val("");
    }
}

function restore_textbox(textbox_id, textbox_value) {
    var text = $("#" + textbox_id);
    if (text.val() === "") {
        text.val(textbox_value);
    }
}

function confirmAction(message) {
    var ask = confirm(message);
    if (ask == true) {
        return true;
    }
    return false;
}

//Get URL parameters
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// User Name Shortener
$(document).ready(function () {
    if ($('#account-bar .account-name').text().length > 16) {
        var name = $('#account-bar .account-name').text()
                .substring(0, 12)
                + "...";
        $('#account-bar .account-name').text(name);
    }
});

// Close FullScreen Objects (Using Cover)
$(document).ready(function () {

    $('.close').click(function () {
        popdown();
    });
    $('.cover').click(function (e) {
        if (($(e.target).parents('.cover').length) * (-1)) {
            return;
        }
        popdown();
    });
    function popdown() {
        $('.cover').fadeOut();
    }

});

// This or similar could be used in the future to load pages on the fly
function LoadPage(pageName) {
    $.get('/' + pageName + '?notemplate=true', function (data) {
        $('#content-outer').html(data);
    });
}

//---------------------\\
// Media Slider Script \\
//---------------------\\

//Hide JavaScript warning.
$(document).ready(function () {
    $('.media-slider-js-warning').hide();
});

function MediaSlider(elements, sliderFrame, slideShowPauseDelay, slideShowDelay, animationDur) {
    var _this = this;
    _this.slideShowDelay = typeof slideShowDelay !== 'undefined' ? slideShowDelay : 6000;
    _this.slideShowPauseDelay = typeof slideShowPauseDelay !== 'undefined' ? slideShowPauseDelay : 7000;
    _this.animationDur = typeof animationDur !== 'undefined' ? animationDur : 500;
    _this.elems = elements;
    _this.ctrlsLocked = false;
    _this.slideShowPaused = false;
    _this.ignoreMouseOut = false;
    _this.index = 0;
    _this.automateID = new Array();
    _this.elem = _this.elems[_this.index];
    $(_this.elem).show();
    _this.leftControl = sliderFrame.children(".media-slider-control-left");
    _this.rightControl = sliderFrame.children(".media-slider-control-right");
    if(_this.elems.length > 1) {

        $.each(_this.elems, function (i, v) {
            $(v).hover(function () {
                _this.pauseSlideshow();
            }, function () {
                _this.playSlideshow();
            });
        });        

        _this.leftControl.click(function () {
            if (_this.ctrlsLocked == false) {
                _this.pauseSlideshowDelay();
                _this.previousItem();
            }
        });

        _this.rightControl.click(function () {
            if (_this.ctrlsLocked == false) {
                _this.pauseSlideshowDelay();
                _this.nextItem();
            }
        });

        (function () {
            setTimeout(function () {
                _this.automateSlideshow();
            }, _this.slideShowDelay);
        })();

    } else {
        _this.leftControl.hide();
        _this.rightControl.hide();
    }
}

MediaSlider.prototype.nextItem = function () {
    var _this = this;
    _this.lockCtrls();
    if (_this.index != (_this.elems.length - 1)) {
        var nextElem = $(_this.elems[_this.index + 1]);
        nextElem.prop("right", "-100%");
        $(_this.elem).hide("slide", { direction: "left", easing: "easeInOutCirc" }, _this.animationDur);
        nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, _this.animationDur, function () {
            _this.unlockCtrls();
        });
        _this.elem = nextElem;
        _this.index++;
    } else {
        var nextElem = $(_this.elems[0]);
        nextElem.prop("right", "-100%");
        $(_this.elem).hide("slide", { direction: "left", easing: "easeInOutCirc" }, _this.animationDur);
        nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, _this.animationDur, function () {
            _this.unlockCtrls();
        });
        _this.elem = nextElem;
        _this.index = 0;
    }
};

MediaSlider.prototype.previousItem = function () {
    var _this = this;
    _this.lockCtrls();
    if (_this.index != 0) {
        var nextElem = $(_this.elems[_this.index - 1]);
        nextElem.prop("left", "-100%");
        $(_this.elem).hide("slide", { direction: "right", easing: "easeInOutCirc" }, _this.animationDur);
        nextElem.show("slide", { direction: "left", easing: "easeInOutCirc" }, _this.animationDur, function () {
            _this.unlockCtrls();
        });
        _this.elem = nextElem;
        _this.index--;
    } else {
        var nextElem = $(_this.elems[(_this.elems.length - 1)]);
        nextElem.prop("left", "-100%");
        $(_this.elem).hide("slide", { direction: "right", easing: "easeInOutCirc" }, _this.animationDur);
        nextElem.show("slide", { direction: "left", easing: "easeInOutCirc" }, _this.animationDur, function () {
            _this.unlockCtrls();
        });
        _this.elem = nextElem;
        _this.index = (_this.elems.length - 1);
    }
};

MediaSlider.prototype.setItem = function (index) {
    var _this = this;
    _this.pauseSlideshowDelay();
    _this.lockCtrls();
    var nextElem = $(_this.elems[index]);
    nextElem.prop("left", "-100%");
    $(_this.elem).hide("slide", { direction: "left", easing: "easeInOutCirc" }, _this.animationDur);
    nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, _this.animationDur, function () {
        _this.unlockCtrls();
    });
    $('html, body').animate({
        scrollTop: $(".media-slider-frame").offset().top - 200
    }, 1000);
    _this.elem = nextElem;
    _this.index = index;
};

MediaSlider.prototype.automateSlideshow = function () {
    var _this = this;
    if (_this.slideShowPaused == false) {
        _this.nextItem();
        _this.clearTimeouts();
    }
    _this.automateID[_this.automateID.length] = setTimeout(function () {
        _this.automateSlideshow();
    }, _this.slideShowDelay);
};

MediaSlider.prototype.playSlideshow = function () {
    var _this = this;
    if (!_this.ignoreMouseOut && (_this.elems.length > 1)) {
        _this.clearTimeouts();
        _this.slideShowPaused = false;
        _this.automateID[_this.automateID.length] = setTimeout(function () {
            _this.automateSlideshow();
        }, _this.slideShowDelay);
    }
};

MediaSlider.prototype.pauseSlideshow = function () {
    this.slideShowPaused = true;
    console.log(this.slideShowPaused);
};

MediaSlider.prototype.pauseSlideshowDelay = function () {
    var _this = this;
    _this.slideShowPaused = true;
    _this.ignoreMouseOut = true;
    setTimeout(function () {
        _this.ignoreMouseOut = false;
        _this.playSlideshow();
    }, _this.slideShowPauseDelay);
};

MediaSlider.prototype.lockCtrls = function () {
    this.ctrlsLocked = true;
};

MediaSlider.prototype.unlockCtrls = function () {
    this.ctrlsLocked = false;
};

MediaSlider.prototype.lockCtrlsTemp = function (duration) {
    var _this = this;
    var dur = typeof duration !== 'undefined' ? duration : _this.animationDur;
    _this.ctrlsLocked = true;
    setTimeout(function () {
        _this.ctrlsLocked = false;
    }, dur);
}

MediaSlider.prototype.clearTimeouts = function () {
    var _this = this;
    while (_this.automateID.length > 0) {
        clearTimeout(_this.automateID[0]);
        _this.automateID.splice(0, 1);
    }
};

MediaSlider.prototype.updateElems = function (selector) {
    var _this = this
    _this.elems = $(selector);

    $.each(_this.elems, function (i, v) {
        $(v).hover(function () {
            _this.pauseSlideshow();
        }, function () {
            _this.playSlideshow();
        });
    });
}

//Fix nav controls to hover state when on mobile devices for greater visibility
$(document).ready(function () {
    if (jQuery.browser.mobile) {
        $(".media-slider-control-img").css("background", "rgba(255,255,255,0.15)");
    }
});
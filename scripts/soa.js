/**
 * jQuery.browser.mobile (http://detectmobilebrowser.com/)
 *
 * jQuery.browser.mobile will be true if the browser is a mobile device
 *
 **/
(function (a) {
    (jQuery.browser = jQuery.browser || {}).mobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4));
})(navigator.userAgent || navigator.vendor || window.opera);

function strip_tags(input, allowed) {
    //  discuss at: http://phpjs.org/functions/strip_tags/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Luke Godfrey
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Pul
    //    input by: Alex
    //    input by: Marc Palau
    //    input by: Brett Zamir (http://brett-zamir.me)
    //    input by: Bobby Drake
    //    input by: Evertjan Garretsen
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Onno Marsman
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Eric Nagel
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Tomasz Wesolowski
    //  revised by: Rafa? Kukawski (http://blog.kukawski.pl/)
    //   example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
    //   returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
    //   example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
    //   returns 2: '<p>Kevin van Zonneveld</p>'
    //   example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
    //   returns 3: "<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>"
    //   example 4: strip_tags('1 < 5 5 > 1');
    //   returns 4: '1 < 5 5 > 1'
    //   example 5: strip_tags('1 <br/> 1');
    //   returns 5: '1  1'
    //   example 6: strip_tags('1 <br/> 1', '<br>');
    //   returns 6: '1 <br/> 1'
    //   example 7: strip_tags('1 <br/> 1', '<br><br/>');
    //   returns 7: '1 <br/> 1'

    allowed = (((allowed || '') + '')
      .toLowerCase()
      .match(/<[a-z][a-z0-9]*>/g) || [])
      .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
      commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '')
      .replace(tags, function ($0, $1) {
          return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
      });
}

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


function cleanPageId(pageId) {
    var temp = pageId.toLowerCase();
    temp = pageId.replace(".php","");
    return temp.replace("/[^\/A-Za-z0-9_\-]/","");
}
function genPostLink(row) {
    return row["id"] + '-' + cleanPageId(row["title"].replace(" ","-"));
}

function trimWholeWord(string, length) {
    var short = string.substr(0, length);
    if (/^\S/.test(string.substr(length)))
        return short.replace(/\s+\S*$/, "");
    return short;
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

function MediaSlider(sliderWrapper, slideShowPauseDelay, slideShowDelay, animationDur) {
    if (typeof sliderWrapper !== 'object') {
        return -1;
    }
    var slideShowDelay = typeof slideShowDelay !== 'undefined' ? slideShowDelay : 6000;
    var slideShowPauseDelay = typeof slideShowPauseDelay !== 'undefined' ? slideShowPauseDelay : 7000;
    var animationDur = typeof animationDur !== 'undefined' ? animationDur : 500;
    var elems = sliderWrapper.find(".media-wrapper");
    var sliderFrame = sliderWrapper.find(".media-slider-frame");
    var ctrlsLocked = false;
    var slideShowPaused = false;
    var ignoreMouseOut = false;
    var index = 0;
    var automateID = new Array();
    var elem = elems[index];
    $(elem).show();
    var leftControl = sliderFrame.children(".media-slider-control-left");
    var rightControl = sliderFrame.children(".media-slider-control-right");
    
    var _this = this;
    _this.nextItem = function () {
        if (elems.length > 1) {
            _this.lockCtrls();
            if (index != (elems.length - 1)) {
                var nextElem = $(elems[index + 1]);
                nextElem.prop("right", "-100%");
                $(elem).hide("slide", { direction: "left", easing: "easeInOutCirc" }, animationDur);
                nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, animationDur, function () {
                    _this.unlockCtrls();
                });
                elem = nextElem;
                index++;
            } else {
                var nextElem = $(elems[0]);
                nextElem.prop("right", "-100%");
                $(elem).hide("slide", { direction: "left", easing: "easeInOutCirc" }, animationDur);
                nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, animationDur, function () {
                    _this.unlockCtrls();
                });
                elem = nextElem;
                index = 0;
            }
        }
    };
    _this.previousItem = function () {
        if (elems.length > 1) {
            _this.lockCtrls();
            if (index != 0) {
                var nextElem = $(elems[index - 1]);
                nextElem.prop("left", "-100%");
                $(elem).hide("slide", { direction: "right", easing: "easeInOutCirc" }, animationDur);
                nextElem.show("slide", { direction: "left", easing: "easeInOutCirc" }, animationDur, function () {
                    _this.unlockCtrls();
                });
                elem = nextElem;
                index--;
            } else {
                var nextElem = $(elems[(elems.length - 1)]);
                nextElem.prop("left", "-100%");
                $(elem).hide("slide", { direction: "right", easing: "easeInOutCirc" }, animationDur);
                nextElem.show("slide", { direction: "left", easing: "easeInOutCirc" }, animationDur, function () {
                    _this.unlockCtrls();
                });
                elem = nextElem;
                index = (elems.length - 1);
            }
        }
    };
    _this.setItem = function (ind, scroll) {
        if (ind < 0 || ind > elems.length) {
            return -1;
        }
        var scr = typeof scroll !== 'undefined' ? scroll : false;
        _this.pauseSlideshowDelay();
        _this.lockCtrls();
        var nextElem = $(elems[ind]);
        nextElem.prop("left", "-100%");
        $(elem).hide("slide", { direction: "left", easing: "easeInOutCirc" }, animationDur);
        nextElem.show("slide", { direction: "right", easing: "easeInOutCirc" }, animationDur, function () {
            _this.unlockCtrls();
        });
        if (scr) {
            $('html, body').animate({
                scrollTop: $(".media-slider-frame").offset().top - 200
            }, 1000);
        }
        elem = nextElem;
        index = ind;
    };
    _this.automateSlideshow = function () {
        if (slideShowPaused == false) {
            _this.nextItem();
            _this.clearTimeouts();
        }
        automateID[automateID.length] = setTimeout(function () {
            _this.automateSlideshow();
        }, slideShowDelay);
    };
    _this.playSlideshow = function () {
        if (!ignoreMouseOut && (elems.length > 1)) {
            _this.clearTimeouts();
            slideShowPaused = false;
            automateID[automateID.length] = setTimeout(function () {
                _this.automateSlideshow();
            }, slideShowDelay);
        }
    };
    _this.pauseSlideshow = function () {
        slideShowPaused = true;
    };
    _this.pauseSlideshowDelay = function () {
        slideShowPaused = true;
        ignoreMouseOut = true;
        setTimeout(function () {
            ignoreMouseOut = false;
            _this.playSlideshow();
        }, slideShowPauseDelay);
    };
    _this.lockCtrls = function () {
        ctrlsLocked = true;
    };
    _this.unlockCtrls = function () {
        ctrlsLocked = false;
    };
    _this.lockCtrlsTemp = function (duration) {
        var dur = typeof duration !== 'undefined' ? duration : animationDur;
        ctrlsLocked = true;
        setTimeout(function () {
            ctrlsLocked = false;
        }, dur);
    };
    _this.clearTimeouts = function () {
        while (automateID.length > 0) {
            clearTimeout(automateID[0]);
            automateID.splice(0, 1);
        }
    };
    _this.updateElems = function () {
        elems = sliderWrapper.find(".media-wrapper");
        if (elems.length > 1) {
            leftControl.fadeIn();
            rightControl.fadeIn();
        } else {
            leftControl.fadeOut();
            rightControl.fadeOut();
        }
        $.each(elems, function (i, v) {
            $(v).hover(function () {
                _this.pauseSlideshow();
            }, function () {
                _this.playSlideshow();
            });
        });
    };
    _this.bindItemsToSlider = function (items) {
        $.each(items, function (i, v) {
            $(v).unbind();
            $(v).click(function () {
                _this.lockCtrls();
                _this.setItem(i, true);
            });
        });
    };

    sliderFrame.hover(function () {
        _this.pauseSlideshow();
    }, function () {
        _this.playSlideshow();
    });

    leftControl.click(function () {
        if (ctrlsLocked == false) {
            _this.pauseSlideshowDelay();
            _this.previousItem();
        }
    });
    rightControl.click(function () {
        if (elems.length > 1 && ctrlsLocked == false) {
            _this.pauseSlideshowDelay();
            _this.nextItem();
        }
    });

    if (elems.length < 2) {
        leftControl.fadeOut();
        rightControl.fadeOut();
    }

    (function () {
        setTimeout(function () {
            _this.automateSlideshow();
        }, slideShowDelay);
    })();
}

//Fix nav controls to hover state when on mobile devices for greater visibility
$(document).ready(function () {
    if (jQuery.browser.mobile) {
        $(".media-slider-control-img").css("background", "rgba(255,255,255,0.15)");
    }
});

//------------------------\\
// Spinner Wrapper Script \\
//------------------------\\

function SpinnerWrapper(spinnerOpts) {
    var sliderSpinnerOpts = {
        lines: 13,
        length: 32,
        width: 10,
        radius: 30,
        corners: 1,
        rotate: 0,
        direction: 1,
        color: '#10beef',
        speed: 1,
        trail: 57,
        shadow: false,
        hwaccel: true,
        className: 'spinner',
        zIndex: 1,
        top: '50%',
        left: '50%'
    };
    if (typeof spinnerOpts === 'object') {
        var keys = Object.keys(spinnerOpts);
        $.each(keys, function (i, v) {
            sliderSpinnerOpts[v] = spinnerOpts[v];
        });
    }
    this.spinner = new Spinner(sliderSpinnerOpts);
}

//---------------\\
// Pagify Script \\
//---------------\\

function Pagify(outerWrapper, innerWrapper, loader, startPage, callbackOnTransition, callbackOnSuccess, constArgsForLoader, spinnerOpts) {
    var _this = this;
    var constArgsExist = typeof constArgsForLoader !== 'undefined' ? true : false;
    var startPid = typeof startPage !== 'undefined' ? startPage : 1;
    var currPid = 0;
    var url = "";
    var totalPages = 0;
    var dur = 400;

    var transitionSpinnerOpts = {
        color: '#075d74'
    };
    if (typeof spinnerOpts === 'object') {
        var keys = Object.keys(spinnerOpts);
        $.each(keys, function (i, v) {
            transitionSpinnerOpts[v] = spinnerOpts[v];
        });
    }
    var spinnerWrapper = new SpinnerWrapper(transitionSpinnerOpts);

    function checkWrappersExist() {
        if (outerWrapper.length <= 0 || $(innerWrapper).length <= 0) {
            return false;
        }
        return true;
    };
    function checkLoaderExists() {
        var r = false;
        $.ajax( {
            url: "../" + loader + "?check=true",
            type: "GET",
            success: function (msg) {
                r = true;
            },
            async: false
        } );
        return r;
    };
    function checkCallbackExists() {
        if (typeof callbackOnTransition !== 'function') {
            return false;
        }
        return true;
    };
    function createUrl() {
        var l = "../" + loader;
        l += "?";
        if (constArgsExist) {
            var keys = Object.keys(constArgsForLoader);
            for (var i = 0; i < keys.length; ++i) {
                l += keys[i];
                l += "=";
                l += constArgsForLoader[keys[i]];
                l += "&";
            }
        }
        return l;
    };
    function getTotalPages() {
        return getPageData({ "getTotalPages": true });
    };
    function addControls(hidden) {
        if (totalPages < 2) {
            return;
        }

        var h = typeof hidden !== 'undefined' ? hidden : false;
        function echoPgfyCtrl(pid, isDisabled) {
            var iD = typeof isDisabled !== 'undefined' ? isDisabled : false;
            return '<div class="pagify-control ' + (iD ? "disabled" : "") + '" data-id="' + pid + '">' + pid + '</div>';
        }
        var htmlControls = '<div class="col double-col-2 pagify-control-wrapper"' + (h ? 'style="opacity:0;"' : "") + '>';
        var htmlEllipsis = '<div class="pagify-control pagify-control-ellipsis">. . .</div>';

        htmlControls += '<div class="pagify-control ' + ((currPid > 1) ? "" : "disabled") + '" data-id="prev">&lt;</div>';
        
        if (currPid == 1) {
            htmlControls += echoPgfyCtrl(1, true);
        } else {
            htmlControls += echoPgfyCtrl(1);
        }

        if (currPid == 1) {
            if (totalPages > 3) {
                htmlControls += echoPgfyCtrl(2);
                htmlControls += htmlEllipsis;
            } else if (totalPages == 3) {
                htmlControls += echoPgfyCtrl(2);
            }
        } else if (currPid == 2) {
            if (totalPages > 2) {
                htmlControls += echoPgfyCtrl(2, true);
            }
            if (totalPages > 4) {
                htmlControls += echoPgfyCtrl(3);
                htmlControls += htmlEllipsis;
            } else if (totalPages == 4) {
                htmlControls += echoPgfyCtrl(3);
            }
        } else if (currPid == 3) {
            htmlControls += echoPgfyCtrl(2);
            if (totalPages > 3) {
                htmlControls += echoPgfyCtrl(3, true);
            }
            if (totalPages > 5) {
                htmlControls += echoPgfyCtrl(4);
                htmlControls += htmlEllipsis;
            } else if (totalPages == 5) {
                htmlControls += echoPgfyCtrl(4);
            }
        } else if ((totalPages - 2) > 3 && currPid == (totalPages - 2)) {
            htmlControls += htmlEllipsis;
            htmlControls += echoPgfyCtrl(totalPages - 3);
            htmlControls += echoPgfyCtrl((totalPages - 2), true);
            htmlControls += echoPgfyCtrl(totalPages - 1);
        } else if ((totalPages - 1) > 3 && currPid == (totalPages - 1)) {
            htmlControls += htmlEllipsis;
            htmlControls += echoPgfyCtrl(totalPages - 2);
            htmlControls += echoPgfyCtrl((totalPages - 1), true);
        } else if (totalPages > 3 && currPid == totalPages) {
            htmlControls += htmlEllipsis;
            htmlControls += echoPgfyCtrl(totalPages - 1);
        } else {
            htmlControls += htmlEllipsis;
            htmlControls += echoPgfyCtrl(currPid - 1);
            htmlControls += echoPgfyCtrl(currPid, true);
            htmlControls += echoPgfyCtrl(currPid + 1);
            htmlControls += htmlEllipsis;
        }

        if (totalPages > 1) {
            if (totalPages == currPid) {
                htmlControls += echoPgfyCtrl(totalPages, true);
            } else {
                htmlControls += echoPgfyCtrl(totalPages);
            }
        }

        htmlControls += '<div class="pagify-control ' + ((currPid != totalPages) ? "" : "disabled") + '" data-id="next">&gt;</div>';

        htmlControls += '</div>';

        outerWrapper.append(htmlControls);
    };
    function createClickEventListeners() {
        var controls = outerWrapper.find(".pagify-control:not('.disabled')");
        $.each(controls, function (i, v) {
            var id = $(v).data("id");
            $(v).click(function () {
                if (id != "prev" && id != "next") {
                    _this.setPage(id);
                } else if (id == "next") {
                    _this.setPage(currPid + 1);
                } else if (id == "prev") {
                    _this.setPage(currPid - 1);
                }
            });
        });
    };
    function refreshControls() {
        outerWrapper.children(".pagify-control-wrapper").remove();
        addControls();
        createClickEventListeners();
    };
    function transitionFromPage(direction) {
        var dir = typeof direction !== 'undefined' ? direction : "left";

        outerWrapper.append('<div class="loading" style="display:none;"></div>');
        var target = outerWrapper.find(".loading")[0];
        spinnerWrapper.spinner.spin(target);

        var _innerWrapperList = outerWrapper.children(":not(.pagify-control-wrapper)");
        var _innerWrapperFirst = _innerWrapperList.first();
        var _innerWrapperLast = _innerWrapperList.last();

        _innerWrapperFirst.hide("slide", { direction: dir, easing: "easeInOutCirc" }, dur, function () {
            _innerWrapperList.filter(":not(.loading)").remove();
        });
        _innerWrapperLast.fadeIn(dur, "easeInOutCirc");
        return 1;
    }
    function transitionToPage(pageData, direction, delay) {
        if (typeof pageData === 'undefined') {
            return -1;
        } else if (pageData.length == 0) {
            callbackOnTransition(pageData);
            return -1;
        }
        var reverseDir = ((typeof direction !== 'undefined' ? direction : "left") === "left" ? "right" : "left");
        var del = typeof delay !== 'undefined' ? delay : true;

        var htmlPage = callbackOnTransition(pageData);
        outerWrapper.append(htmlPage);

        function transTo() {
            var _innerWrapperList = outerWrapper.children(":not(.pagify-control-wrapper)").filter(":not(.ui-effects-wrapper)");
            var _innerWrapperFirst = _innerWrapperList.first();
            var _innerWrapperLast = _innerWrapperList.last();

            _innerWrapperFirst.fadeOut(dur, "easeInOutCirc", function () {
                _innerWrapperLast.show("slide", { direction: reverseDir, easing: "easeInOutCirc" }, dur, function () {
                    spinnerWrapper.spinner.stop();
                    //Removes all loading divs in case of double pressing controls.
                    _innerWrapperList.filter(".loading").remove();
                    if (typeof callbackOnSuccess === 'function') {
                        callbackOnSuccess();
                    }
                });
            });

            outerWrapper.animate({
                height: (_innerWrapperLast.outerHeight() + 40 + "px")
            }, dur, "easeInOutCirc");
            refreshControls();
            return 1;
        }

        if (del) {
            setTimeout(function () {
                transTo();
            }, dur);
        } else {
            transTo();
        }
    };
    // Get page data as specified by args and data provided.
    // Returns:
    //     1 on AJAX call made and callback provided.
    //     JSON Parsed data on successful AJAX call with no callback.
    function getPageData(argsForLoader, callbackOnCompletion) {
        var argsExist = typeof argsForLoader !== 'undefined' ? true : false;
        var callbackExists = typeof callbackOnCompletion === 'function' ? true : false;
        var l = url;
        if (argsExist) {
            var keys = Object.keys(argsForLoader);
            for (var i = 0; i < keys.length; ++i) {
                l += keys[i];
                l += "=";
                l += argsForLoader[keys[i]];
                l += "&";
            }
        }
        var r = 1;
        $.ajax({
            url: l,
            type: "POST",
            success: function (msg) {
                if (callbackExists) {
                    callbackOnCompletion(JSON.parse(msg));
                } else {
                    r = JSON.parse(msg);
                }
            },
            async: callbackExists
        });
        return r;
    };
    // Gets page identified by the given page id.
    // If no page id is given, it gets the page of the current pid.
    // Returns:
    //     1 on AJAX call made.
    //     -1 on if callback is not provided.
    function getPage(pid, callbackOnCompletion, extraArgsForLoader) {
        var argsExist = typeof extraArgsForLoader !== 'undefined' ? true : false;
        if (typeof callbackOnCompletion !== 'function') {
            return -1;
        }
        if (argsExist) {
            extraArgsForLoader["pid"] = pid;
            getPageData(extraArgsForLoader, callbackOnCompletion);
        } else {
            getPageData({ "pid": pid }, callbackOnCompletion);
        }
        return 1;
    };

    //Sets the page displayed to the page of the given page id.
    //If no page id is given, it does not change the page displayed.
    //Returns:
    //    1 on AJAX call made.
    //    0 if pid is equal to the current id.
    //    -1 on failed AJAX call.
    //    -2 if pid lies outside range of pages accessible.
    _this.setPage = function (pid, argsForLoader) {
        var _pid = typeof pid !== 'undefined' ? pid : currPid;
        if (_pid == currPid) {
            return 0;
        } else if (_pid <= 0 || _pid > totalPages) {
            callbackOnTransition(-1);
            return -2;
        }
        if (currPid > pid) {
            currPid = pid;
            transitionFromPage("right");
            return getPage(pid, function (msg) {
                transitionToPage(msg, "right");
            }, argsForLoader);
        } else {
            currPid = pid;
            transitionFromPage();
            return getPage(pid, function (msg) {
                transitionToPage(msg);
            }, argsForLoader);
        }
    };
    
    if (!checkWrappersExist() || !checkLoaderExists() || !checkCallbackExists()) {
        return -1;
    }
    url = createUrl();
    totalPages = getTotalPages();
    currPid = startPid;
    getPage(currPid, function (msg) {
        transitionToPage(msg);
    });
}
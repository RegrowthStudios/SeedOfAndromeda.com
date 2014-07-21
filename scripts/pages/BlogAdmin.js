//Disable Dev News option unless Publish is first checked.
$(document).ready(function () {
    var devnews = $("input.devnews");
    var publish = $("input.publish");
    publish.click(function () {
        if ($(this).is(':checked')) {
            devnews.removeAttr("disabled");
        } else {
            devnews.prop("disabled", true);
        }
    });
    $(document).ready(function () {
        if (!(publish.is(':checked'))) {
            devnews.prop("disabled", true);
        }
    });
});

//Disable No Sign Off unless anonymous is first checked.
$(document).ready(function () {
    var noSignOff = $("input.no-sign-off");
    var anonymous = $("input.anonymous");
    anonymous.click(function () {
        if ($(this).is(':checked')) {
            noSignOff.removeAttr("disabled");
        } else {
            noSignOff.prop("disabled", true);
        }
    });
    $(document).ready(function () {
        if (!(anonymous.is(':checked'))) {
            noSignOff.prop("disabled", true);
        }
    });
});


//Display Dev News summary textbox if Dev News is checked.
$(document).ready(function () {
    var devnews = $("input.devnews");
    devnews.click(function () {
        if ($(this).is(':checked')) {
            $("#dev-news-summary-content-cover").show();
        } else {
            $("#dev-news-summary-content-cover").hide();
        }
    });
});
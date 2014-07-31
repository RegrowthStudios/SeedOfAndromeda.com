//Disable Dev News option unless Publish is first checked.
$(document).ready(function () {
    var devnews = $("input#devnews");
    var publish = $("input#publish");
    publish.click(function () {
        if ($(this).is(':checked')) {
            devnews.removeAttr("disabled");
        } else {
            devnews.prop("disabled", true);
        }
    });
    if (!(publish.is(':checked'))) {
        devnews.prop("disabled", true);
    }
});

//Disable no sign off option unless anonymous is first checked.
$(document).ready(function () {
    var nosignoff = $("input#no-sign-off");
    var anonymous = $("input#anonymous");
    anonymous.click(function () {
        if ($(this).is(':checked')) {
            nosignoff.removeAttr("disabled");
        } else {
            nosignoff.prop("disabled", true);
        }
    });
    if (!(anonymous.is(':checked'))) {
        nosignoff.prop("disabled", true);
    }
});

//Disable hide screenshots option when prioritise screenshots is checked, and vice versa.
$(document).ready(function () {
    var prioritise = $("input#prioritisescreenshots");
    var hide = $("input#hidescreenshots");
    prioritise.click(function () {
        if ($(this).is(':checked')) {
            hide.prop("disabled", true);
        } else {
            hide.removeAttr("disabled");
        }
    });
    hide.click(function () {
        if ($(this).is(':checked')) {
            prioritise.prop("disabled", true);
        } else {
            prioritise.removeAttr("disabled");
        }
    });
    if (prioritise.is(':checked')) {
        hide.prop("disabled", true);
    } else if (hide.is(':checked')) {
        prioritise.prop("disabled", true);
    }
});

//Display Dev News summary textbox if Dev News is checked.
$(document).ready(function () {
    var devnews = $("input#devnews");
    devnews.click(function () {
        if ($(this).is(':checked')) {
            $("#dev-news-summary-content-cover").show();
        } else {
            $("#dev-news-summary-content-cover").hide();
        }
    });
});
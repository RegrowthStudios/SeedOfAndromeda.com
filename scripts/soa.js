function show_menu() {

    $("#game_menu").removeClass("game_menu_h").addClass("game_menu_s");

}

function hide_menu() {

    $("#game_menu").removeClass("game_menu_s").addClass("game_menu_h");

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

$(document).ready(function () {

    if (document.images) {

        img1 = new Image();

        img1.src = "/Assets/images/closeImage_Hover.png";

    }

});

var contHeight = null;

// User Name Shortener

$(document).ready(
		function () {

		    if ($('#accountBar > div.accountsName').text().length > 16) {

		        var name = $('#accountBar > div.accountsName').text()
						.substring(0, 12)
						+ "...";

		        $('#accountBar > div.accountsName').text(name);

		    }

		});

// Close FullScreen Objects (Using Cover)

$(document).ready(function () {

    $('.close').click(function () {

        popdown();

    })

    $('.cover').click(function (e) {

        if (($(e.target).parents('.cover').length) * (-1)) {

            return;

        }

        popdown();

    })

    function popdown() {

        $('.cover').fadeOut();

    }

});

/*
 * 
 * //Nav Bar Dropdown Slider
 * 
 * $(document).ready(function() {
 * 
 * var list = new Array();
 * 
 * list = $('#navigation li');
 * 
 * var drops = $('.dropdown');
 * 
 * var heights = new Array();
 * 
 * 
 * 
 * var dropdown = function() {
 * 
 * var i = list.indexOf($(this));
 * 
 * var drop = drops[i];
 * 
 * var height = heights[i];
 * 
 * 
 * 
 * drop.animate({
 * 
 * height: height
 * 
 * }); }
 * 
 * for(var i = 0; i < list.length; i++) {
 * 
 * list[i].hover(dropdown());
 * 
 * var drop = drops[i];
 * 
 * heights[i] = drop.innerHeight();
 * 
 * drop.css('height', '0')
 * 
 * .css('-moz-transform', 'scaleY(1)')
 * 
 * .css('-ms-transform', 'scaleY(1)')
 * 
 * .css('-o-transform', 'scaleY(1)')
 * 
 * .css('-webkit-transform', 'scaleY(1)')
 * 
 * .css('transform', 'scaleY(1)'); }
 * 
 * });
 * 
 */
/* This or similar could be used in the future to load pages on the fly */
function LoadPage(pageName) {
    $.get('/' + pageName + '?notemplate=true', function (data) {
        $('#content-outer').html(data);
    });
}
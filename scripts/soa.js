function show_menu()

{

	$("#game_menu").removeClass("game_menu_h").addClass("game_menu_s");

}

function hide_menu()

{

	$("#game_menu").removeClass("game_menu_s").addClass("game_menu_h");

}

function clear_textbox(textbox_id, textbox_value)

{

	var text = $("#" + textbox_id);

	if (text.val() == textbox_value)

	{

		text.val("");

	}

}

function restore_textbox(textbox_id, textbox_value)

{

	var text = $("#" + textbox_id);

	if (text.val() === "")

	{

		text.val(textbox_value);

	}

}

function validate_newsletter_email()

{

	var error_message = "";

	if ($('#first_name').val() === "" || $('#first_name').val() == "First Name") {
		error_message += "Please enter your first name\n";
	}

	if ($('#last_name').val() === "" || $('#last_name').val() == "Last Name") {
		error_message += "Please enter your last name\n";
	}

	if (/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/
			.test($('#email').val()) === false) {
		error_message += "Please enter a valid email address\n";
	}

	if (error_message === "") {
		document.forms.newsletter.submit();
	} else {
		alert(error_message);
		return false;
	}

}

function validate_create()

{

	var error_message = "";

	if ($('#reg_first_name').val() === "") {
		error_message += "Please enter your first name\n";
	}

	if ($('#reg_last_name').val() === "") {
		error_message += "Please enter your last name\n";
	}

	if (/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/.test($('#reg_email')
			.val()) === false) {
		error_message += "Please enter a valid email address\n";
	}

	if ($('#reg_username').val() === "") {
		error_message += "Please enter a username\n";
	}

	if ($('#reg_password').val() === "") {
		error_message += "Please enter a password\n";
	}

	if ($('#reg_password').val().length < 8) {
		error_message += "Your password must be at least 8 characters long!\n";
	} else

	if ($('#reg_password').val() != $('#reg_confirm_password').val()) {
		error_message += "Passwords do not match\n";
	}

	if (error_message === "") {
		document.forms.create.submit();
	} else {
		alert(error_message);
		return false;
	}

}

function prepare_login()

{

	var currURL = document.URL;

	if (currURL.length < 31) {

		return true;

	}

	var shortenedURL = currURL.substring(31);

	if (shortenedURL == "?loginerror=invalid"
			|| shortenedURL == "inerror=invalid") {
		return true;
	}

	$(".accountLog form").get(0).setAttribute('action',
			("/Login_Function.php?prev=" + shortenedURL));

	return true;

}

$(document).ready(function() {

	if (document.images) {

		img1 = new Image();

		img1.src = "/Assets/images/closeImage_Hover.png";

	}

});

$(document).ready(function() {

	var currURL = document.URL;

	if (currURL.length < 31) {

		return;

	}

	var shortenedURL = currURL.substring(31);

	$(".logout").attr("href", "/Logout.php?prev=" + shortenedURL);

});

var contHeight = null;

// User Name Shortener

$(document).ready(
		function() {

			if ($('#accountBar > div.accountsName').text().length > 16) {

				var name = $('#accountBar > div.accountsName').text()
						.substring(0, 12)
						+ "...";

				$('#accountBar > div.accountsName').text(name);

			}

		});

// Nav Bar Fixer

$(document).ready(
		function() {

			var nav = $('#nav-bar');

			var top = nav.offset().top
					- parseFloat(nav.css('marginTop').replace(/auto/, 0));

			var topimg = $('.topimg');

			$(window).scroll(function(event) {

				// what the y position of the scroll is

				var y = $(this).scrollTop();

				// whether that's below the form

				if (y >= top) {

					nav.addClass('navigation-fixed');

					topimg.css("margin-top", "106px");

				} else {

					// otherwise remove it

					nav.removeClass('navigation-fixed');

					topimg.css("margin-top", "60px");

				}

			});

		});

// Sticky Footer (Cheating - do the damned CSS)

$(document).ready(function() {

	var footer = $('#footer');

	var stickyFooter = function() {

		if (contHeight === null) {

			setTimeout(stickyFooter, 1000);

		} else {

			var margin = $(window).height() - 568 - contHeight; // 568 is the
			// pixel height
			// of elements
			// exluding
			// content-outer.

			if (margin > 40) {

				footer.css('margin-top', margin);

			} else {

				footer.css('margin-top', 40);

			}

		}

	}

	$(window).resize(function() {

		stickyFooter();

	})

	stickyFooter();

});

// Close FullScreen Objects (Using Cover)

$(document).ready(function() {

	$('.close').click(function() {

		popdown();

	})

	$('.cover').click(function(e) {

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
	$.get('/' + pageName + '?notemplate=true', function(data) {
		$('#content-outer').html(data);
	});
}
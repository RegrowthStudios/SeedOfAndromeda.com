function show_menu()
{
	document.getElementById("game_menu").className = "game_menu_s";	
}

function hide_menu()
{
	document.getElementById("game_menu").className = "game_menu_h";	
}

function clear_textbox(textbox_id,textbox_value)
{
	if(document.getElementById(textbox_id).value == textbox_value)
	{
	document.getElementById(textbox_id).value = "";
	}
}

function restore_textbox(textbox_id,textbox_value)
{
	if(document.getElementById(textbox_id).value === "")
	{
	document.getElementById(textbox_id).value = textbox_value;
	}
}

function validate_newsletter_email()
{
	var error_message = "";
	if(document.getElementById('first_name').value == "" || document.getElementById('first_name').value == "First Name"){ error_message += "Please enter your first name\n" }
	if(document.getElementById('last_name').value == "" || document.getElementById('last_name').value == "Last Name"){ error_message += "Please enter your last name\n" }
	if(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/.test(document.getElementById('email').value) == false) { error_message += "Please enter a valid email address\n"; }
	if(error_message == ""){ document.forms.newsletter.submit(); } else { alert(error_message); return false; }
}

//User Name Shortener
$(document).ready(function () {
    if ($('#accountBar > div.accountsName').text().length > 16) {
        var name = $('#accountBar > div.accountsName').text().substring(0, 12) + "...";
        $('#accountBar > div.accountsName').text(name);
    }
});

//Nav Bar Fixer
$(document).ready(function () {

    var top = $('#nav-bar').offset().top - parseFloat($('#nav-bar').css('marginTop').replace(/auto/, 0));

    $(window).scroll(function (event) {
        // what the y position of the scroll is
        var y = $(this).scrollTop();

        // whether that's below the form
        if (y >= top) {
            // if so, add the fixed class
            $('#nav-bar').addClass('navigation-fixed');
        } else {
            // otherwise remove it
            $('#nav-bar').removeClass('navigation-fixed');
        }

    });

});

//Full Screen Media Slider
$(document).ready(function () {
    var img, index;
    var imgs = $('.image');

    function popup_Img(img) {
        var src = img.attr('src').toString();
        $('.enlargedImage').attr('src', src);
        $('.cover.imgSlider').fadeIn();
    }

    imgs.click(function () {
        img = $(this);
        $('.imgPos').text((imgs.index(img) + 1).toString() + " / " + (imgs.length).toString());
        popup_Img(img);
    })

    $('.imgNext').click(function () {
        var ind = imgs.index(img);
        if (ind == (imgs.length - 1)) {
            img = $(imgs.get(0));
            index = 0;
            var src = img.attr('src');
            $('.tempImage').attr('src', src).css('display', 'none').show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
            $('.enlargedImage').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlargedImage').attr('src', src).fadeIn(0);
            });
        } else {
            img = $(imgs.get(ind + 1));
            index = imgs.index(img);
            var src = img.attr('src');
            $('.tempImage').attr('src', src).css('display', 'none').show("slide", { direction: "left", easing: "easeInOutCirc" }, "slow");
            $('.enlargedImage').hide("slide", { direction: "right", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlargedImage').attr('src', src).fadeIn(0);
            });
        }

        $('.imgPos').text((index + 1).toString() + " / " + (imgs.length).toString());
    });


    $('.imgPrev').click(function () {
        var ind = imgs.index(img);

        if (ind == 0) {
            img = $(imgs.get(imgs.length - 1));
            index = imgs.index(img);
            var src = img.attr('src');
            $('.tempImage').attr('src', src).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlargedImage').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlargedImage').attr('src', src).fadeIn(0);
            });
        } else {
            img = $(imgs.get(ind - 1));
            index = imgs.index(img);
            var src = img.attr('src');
            $('.tempImage').attr('src', src).css('display', 'none').show("slide", { direction: "right", easing: "easeInOutCirc" }, "slow");
            $('.enlargedImage').hide("slide", { direction: "left", easing: "easeInOutCirc" }, "slow", function () {
                $('.enlargedImage').attr('src', src).fadeIn(0);
            });
        }
        $('.imgPos').text((index + 1).toString() + " / " + (imgs.length).toString());
    })

});

//Empty Content Box Width Fix
$(document).ready(function () {
    var empties = $('div.empty');
    for (var i = 0; i < empties.length; i++) {
        empty = $(empties[i]);
        var width = (empty.innerWidth() + 22).toString();
        empty.css('width', (width + 'px'));
    }
});

//Content Discreet Repeat
$(document).ready(function () {

    var contBoxes = $('#content-outer');

    for (var i = 0; i < contBoxes.length; i++) {
        var contBox = $(contBoxes[i]);
        var innerHeight = contBox.innerHeight();
        var height = (Math.ceil((innerHeight) / 72) * 72);
        contBox.css('height', (height.toString() + 'px'));

        console.log(innerHeight - height)
    }

});

//Close FullScreen Objects (Using Cover)
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
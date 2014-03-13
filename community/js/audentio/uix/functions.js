function viewport() {
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
}

function checkLogoVisibility() {
	if ( $('#header').hasClass('activeHeaderSticky') ) {

		var logoTopOffset		 = $('#logo').offset().top + ($('#logo').outerHeight(true) / 2),
			navigationBottomOffset 	 = $('#navigation').offset().top + $('#navigation').outerHeight(true);

		if (navigationBottomOffset < logoTopOffset) {
			$('#logo_small').css('display', 'none');
			$('#header').removeClass('hasLogoSmall');
		} else {
			$('#logo_small').css('display', 'block');
			$('#header').addClass('hasLogoSmall');
		}
	} else {
		$('#logo_small').css('display', 'none');
		$('#header').removeClass('hasLogoSmall');
	}
}

function unFixHeaderIfLandscape(windowHeight) {
	if (windowHeight <= 400) { //is landscape, essentially
		$('html').addClass('isLandscape');
		if ( !$('html').hasClass('hasInitialHeaderFixed')) {
			$('#header').removeClass('activeHeaderStickyNotTouching').removeClass('activeHeaderSticky');
			$('#loginBar').removeClass('activeLoginBarSticky');
			$('#moderatorBar').removeClass('activeModeratorBarSticky');
		}
		return true;
	} else {
		$('html').removeClass('isLandscape');
		if ( !$('html').hasClass('hasInitialHeaderFixed')) {
			$('#header').addClass('activeHeaderStickyNotTouching').addClass('activeHeaderSticky');
			$('#loginBar').addClass('activeLoginBarSticky');
			$('#moderatorBar').addClass('activeModeratorBarSticky');
		}
		return false;
	}
}

function checkResponsiveNavAgain() {
	if (!$('html').hasClass('NoResponsive'))
	{
		XenForo.updateVisibleNavigationTabs();
		XenForo.updateVisibleNavigationLinks();
	}
}

function checkFixedHeader(navigationTop) {

	var windowTop = $(window).scrollTop();
	var windowHeight = viewport().height;

	
	if (windowTop > 2) {
		$('#header').addClass('activeHeaderStickyNotTouching');
	} else {
		$('#header').removeClass('activeHeaderStickyNotTouching');
	}

	if (navigationTop <= windowTop) {
		if (unFixHeaderIfLandscape(windowHeight)) {
			return;
		}
		if ($('#moderatorBar').length) {
			$('#moderatorBar').addClass('activeModeratorBarSticky');
		}
		if ($('#loginBar').length) {
			$('#loginBar').addClass('activeLoginBarSticky');
		}
		
		$('#header').addClass('activeHeaderSticky');
	}
	else if (navigationTop > windowTop) {
		if ( !$('html').hasClass('hasInitialHeaderFixed') ) {
			if ($('#moderatorBar').length) {
				$('#moderatorBar').removeClass('activeModeratorBarSticky');
			}
			if ($('#loginBar').length) {
				$('#loginBar').removeClass('activeLoginBarSticky');
			}
			$('#header').removeClass('activeHeaderSticky');
		}
	}
}
 

$(document).ready(function() {

	var uix_windowWidth = viewport().width;
	$(window).on('resize orientationchange', function(){ 
		uix_windowWidth = viewport().width;
	});
	
	$('#header').css('height', $('#header').height() );
	
 	$('.topLink a').click(function () {
	 	$('body,html').animate({
			scrollTop: 0
 		}, 800);
    	return false;
	});
	

	if ( $('html').hasClass('hasHeaderFixed') ) {
		var navigationTop = 0;
		
		var heightOfFixedHeader = $('#navigation').outerHeight();
		if ( $('#moderatorBar').length )
			heightOfFixedHeader += $('#moderatorBar').outerHeight();
		if ( $('#loginBar').length )
			heightOfFixedHeader += $('#loginBar').outerHeight();
		
		if ( !$('html').hasClass('hasInitialHeaderFixed') ) {
			navigationTop = $('#navigation').offset().top;
		}
		
		checkFixedHeader(navigationTop);
		checkResponsiveNavAgain();
		$(window).on('scroll resize orientationchange load', function(e) { 
			checkFixedHeader(navigationTop);
			checkResponsiveNavAgain();
			if ( $('#logo_small').length ) {
				checkLogoVisibility();
			}
		});
	}
	
	var target = '';
	setTimeout(function(){
		if (document.location.hash) {
			var 
			target = document.location.hash;
			$target = $(target);
			if ($target.length) {
				$('html, body').scrollTop($target.offset().top - 110);
			}
		}
		
	}, 10);
	
	$('a[href*="#"]').on('click',function (e) {
		var target = this.hash.replace(/\./g, '\\\\.');
		var $target = $(target);
		if ( $(target).length ) {
			$('html, body').animate({
				'scrollTop': $target.offset().top - 110
			}, 800, 'swing', function () {
				window.location.hash = target;
			});
		}
	    
	});
	
	if ( $('.uix_mainSidebar').length && $('html').hasClass('hasSidebarToggle') ) {
				
		var documentWidthWhenSidebarResponsive = 800;
		var $sidebar = $('.sidebar');
		var sidebarLocation, sidebarMargin, origSidebarMargin = 0;
		if ( $('.uix_mainSidebar').hasClass('uix_mainSidebar_left') ) {
			sidebarLocation = 'left';
		}
		else {
			sidebarLocation = 'right';
		}
		
		if (uix_mainContainerMargin.length)
			origSidebarMargin = uix_mainContainerMargin;

		$(window).on('resize orientationchange', function(){ 
			
			if (uix_windowWidth <= documentWidthWhenSidebarResponsive) {
				$('.mainContainer .mainContent').css('marginRight', 0);
				$('.mainContainer .mainContent').css('marginLeft', 0);
			}
			else {
				if ( $('.uix_mainSidebar').is(":visible") ) {
					if ( sidebarLocation == 'left') {
						$('.mainContainer .mainContent').css('marginLeft', origSidebarMargin);
					}
					else {
						$('.mainContainer .mainContent').css('marginRight', origSidebarMargin);
					}
				}
			}
		});

		if ( $.cookie('collapsedSidebar') == 1 ) {
			$('.uix_sidebar_collapse').addClass('uix_sidebar_collapsed');
			$('.uix_mainSidebar').hide();
			if (sidebarLocation == 'left') {
				$('.mainContainer .mainContent').css('marginLeft', 0);
				$('.sidebar').css('marginLeft', parseInt($sidebar.css('marginLeft'),10) == 0 ? $sidebar.outerWidth() * (-1) : 0 );
			}
			else {
				$('.mainContainer .mainContent').css('marginRight', 0);
				$('.sidebar').css('marginRight', parseInt($sidebar.css('marginRight'),10) == 0 ? $sidebar.outerWidth() * (-1) : 0 );
			}
		}

		$('.uix_sidebar_collapse a').on('click', function(e) {

			e.preventDefault();
			
			if ( $('.uix_mainSidebar').is(":visible") ) {
			
				$.cookie("collapsedSidebar", 1);
				
				$('.uix_sidebar_collapse').addClass('uix_sidebar_collapsed');
				
				if (sidebarLocation == 'left') {
					if (uix_windowWidth > documentWidthWhenSidebarResponsive) {
						$('.sidebar').stop().animate({
							marginLeft: parseInt($sidebar.css('marginLeft'),10) == 0 ? $sidebar.outerWidth() * (-1) : 0
						}, function() {
							$('.uix_mainSidebar').hide();
						});
						$('.mainContainer .mainContent').stop().animate({
							marginLeft: 0
						});
					}
					else {
						$('.uix_mainSidebar').hide();
						$('.mainContainer .mainContent').css('marginLeft', 0);
					}
				}
				else {
					if (uix_windowWidth > documentWidthWhenSidebarResponsive) {
						$('.sidebar').stop().animate({
							marginRight: parseInt($sidebar.css('marginRight'),10) == 0 ? $sidebar.outerWidth() * (-1) : 0
						}, function() {
							$('.uix_mainSidebar').hide();
						});
						$('.mainContainer .mainContent').stop().animate({
							marginRight: 0
						});
					}
					else {
						$('.uix_mainSidebar').hide();
						$('.mainContainer .mainContent').css('marginRight', 0);
					}
				}
				
			}
			else {
			
				$.cookie("collapsedSidebar", 0);
			
				$('.uix_sidebar_collapse').removeClass('uix_sidebar_collapsed');
				
				if (sidebarLocation == 'left') {
					$('.uix_mainSidebar').show();
					if (uix_windowWidth > documentWidthWhenSidebarResponsive) {
						$('.sidebar').stop().animate({
							marginLeft: 0
						});
						$('.mainContainer .mainContent').animate({
							marginLeft: origSidebarMargin
						});
					}
					else {
						$('.sidebar').css('marginLeft', 0);
						$('.mainContainer .mainContent').css('marginLeft', 0);
					}
				}
				else {
					$('.uix_mainSidebar').show();
					if (uix_windowWidth > documentWidthWhenSidebarResponsive) {
						$('.sidebar').stop().animate({
							marginRight: 0
						});
						$('.mainContainer .mainContent').stop().animate({
							marginRight: origSidebarMargin
						});
					}
					else {
						$('.sidebar').css('marginRight', 0);
						$('.mainContainer .mainContent').css('marginRight', 0);
					}
				}
			
			}
		});
	}

	if ( $('html').hasClass('hasCollapseNodes') ) {
		// go through each cookie, and hide nodes that are stored
		if ( $.cookie('collapsedNodes') ) {
			var collapsedNodes = $.cookie("collapsedNodes");
			var collapsedNodes_array = collapsedNodes.split('.');
			$.each(collapsedNodes_array, function(index, value) {
				if (value) {
					$('.node_' + value + '.category > .nodeList').hide();
					$('.node_' + value).addClass("collapsed");
				}
			});
		}
	
		$('.uix_collapseNodes').click(function(e) {
		
			e.preventDefault();
			
			// this nodelist
			var thisNodeList = $(this).parents('.node.category').children('.nodeList');
			// get the id of the clicked node
			var nodeId = $(this).parents('.node.category').attr('id').split('.')[1];
			
			// get the contents of the cookie, the collapsed nodes
			var collapseNodes_content = '';
			if ( $.cookie('collapsedNodes') ) {
				collapseNodes_content = $.cookie('collapsedNodes');
			} 
			
			
			// if the id of the node is already in the cookie, remove it's cookie otherwise create it
			if ( collapseNodes_content.indexOf(nodeId + '.') >= 0) {
				collapseNodes_content = collapseNodes_content.replace( nodeId + '.' , '');
			} 
			else { // add it in
				collapseNodes_content = collapseNodes_content + nodeId + '.';
			}
			$.cookie("collapsedNodes", collapseNodes_content);
			
			// the animation
			$(this).parents('.node.category').toggleClass("collapsed").children('.nodeList').slideToggle();
		
		});
	}

	if ( $('#searchBar.hasSearchButton').length) {
		$("#QuickSearch .primaryControls span").click(function(e) {
			e.preventDefault();
			$("#QuickSearch > .formPopup").submit();
		});
	}
	if ( $("#content.register_form").length ) {
		$("#loginBarHandle").hide();
	}

});
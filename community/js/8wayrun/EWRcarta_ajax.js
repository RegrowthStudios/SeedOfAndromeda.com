/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.ToggleContents = function($toc)
	{
		var hideText = 'hide';
		var showText = 'show';
		var isVisible = true;

		$toc.ready(function() {
			$toc.find(':first').append('<span class="toggle">(<a href="#">'+hideText+'</a>)</span>');

			$toc.find('.toggle a').click(function(e)
			{
				e.preventDefault();

				if (isVisible = !isVisible)
				{
					$toc.find('.toggle a').html(hideText);
					$toc.find('.contents').slideDown();
				}
				else
				{
					$toc.find('.toggle a').html(showText);
					$toc.find('.contents').slideUp();
				}
			});
		});
	}

	// *********************************************************************

	XenForo.SlugText = function($text)
	{
		$text.slugIt({
			output: '.SlugOut'
		});
	}

	// *********************************************************************

	XenForo.SlugEdit = function($text)
	{
		$text.slugIt({
			events: 'focus blur',
			output: '.SlugOut'
		});
	}

	// *********************************************************************

	XenForo.register('.WikiToggle .ToggleContents', 'XenForo.ToggleContents');
	XenForo.register('.SlugIn', 'XenForo.SlugText');
	XenForo.register('.SlugEdit', 'XenForo.SlugEdit');
}
(jQuery, this, document);
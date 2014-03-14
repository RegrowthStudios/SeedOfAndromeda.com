/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.SortColumn = function($column)
	{
		$column.sortable({
			connectWith: ".sortColumn",
			cursor: "move",
			distance: 10,
			items: "div.portlet:not(.locked)",
			placeholder: "portlet-placeholder",
			revert: "true",
			tolerance: "pointer",
			update : function () { 
				$('input.position').each(function() {           
					var parentID = $(this).parent().parent().attr('ID');
					$(this).val( parentID );
				});
			}
		});

		$column.disableSelection();
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

	XenForo.CategoryText = function($text)
	{
		$text.slugIt({
			events: 'focus blur',
			output: '.CategoryEdit',
			type: 'keys'
		});
	}

	// *********************************************************************

	XenForo.register('.sortColumn', 'XenForo.SortColumn');
	XenForo.register('.SlugIn', 'XenForo.SlugText');
	XenForo.register('.SlugEdit', 'XenForo.SlugEdit');
	XenForo.register('.CategoryEdit', 'XenForo.CategoryText');
}
(jQuery, this, document);
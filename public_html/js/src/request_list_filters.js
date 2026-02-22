/**
 *  Run a function each time a filter checkbox is clicked
 *
 */
$(document).ready(function()
{
	$('.request_filter_check').on('click', function()
	{
		townhall_list_filter();
	});
	// Click the "reset filters" button
	$('.townhall_btn_filters_reset').on('click', function()
	{
		townhall_list_reset();
	});

});

/**
 *  Filter the member list
 *
 *  @ active => array containing all items that should be shown
 *  if null, show all markers
 */
function townhall_list_filter(clearAll = false)
{
	// Instantiate filter array
	let checkedItems = [];

	// Loop through all checkboxes and populate filter array
	$(".request_filter_check").each(function(index, element)
	{
		if ($(element).is(':checked'))
		{
			checkedItems.push($(element).attr("value"));
		}
	});
	// Show the "reset all filters" button
	$('.townhall_btn_filters_reset').removeClass('d-none');

	// If array is empty, show all markers
	if (checkedItems.length === 0 || clearAll == true)
	{
		// Remove all filters and hide classes
		$('.townhall_request_row').removeClass('d-none');
		$('.townhall_request_row').removeClass('filter-show');
		$('.townhall_request_row').removeClass('filter-hide');

		// Uncheck the checkboxes
		$('.request_filter_check').prop('checked', false);

		// Hide the "reset all filters" button
		$('.townhall_btn_filters_reset').addClass('d-none');

		// If there's a term in the search field, run it back through
		// ONLY if clearAll flag is not set
		if (!clearAll && $('#requests_search').val())
		{
			townhall_member_search($('#requests_search').val());
		}
	}
	// If not empty, hide all markers that are not in the array
	else
	{
		// Hide all first
		$('.townhall_request_row').addClass('d-none');
		$('.townhall_request_row').addClass('filter-hide');
		$('.townhall_request_row').removeClass('filter-show');

		// Show the remaining rows
		let counter = 0;
		checkedItems.forEach(function(status)
		{
			$('.townhall_request_row[data-status="' + status +'"]').removeClass('d-none');
			$('.townhall_request_row[data-status="' + status +'"]').addClass('filter-show');
			$('.townhall_request_row[data-status="' + status +'"]').removeClass('filter-hide');
			counter++;
		});

		if (counter > 0)
		{
			// Hide the "search results empty" notification
			$('.search-results-empty').addClass('d-none');
		}

		// If there's a term in the search field, run it back through
		if ($('#requests_search').val())
		{
			townhall_member_search($('#requests_search').val());
		}
	}
	// Update the count
	townhall_update_visible_count();
}

/**
 *  Show the element during the filter/search
 */
function townhall_element_show(element)
{
	element.classList.remove('d-none');
	element.classList.remove('filter-hide');
	element.classList.add('filter-show');
}

/**
 *  Hide the element during the filter/search
 */
function townhall_element_hide(element)
{
	element.classList.add('d-none');
	element.classList.add('filter-hide');
	element.classList.remove('filter-show');
}


/**
 *  Sum the visible rows and adjust the count
 */
function townhall_update_visible_count()
{
	let count = $('.townhall_request_row:not(.d-none)').length;
	$('.requests_count').text(count);
	// Set the proper singular/plural
	let term = (count == 1) ? "request" : "requests";
	$('.noun_plural').text(term);
}


/**
 *  Reset all filters and search
 */
function townhall_list_reset()
{
	townhall_list_filter(true);
	// Clear the search field
	$('#requests_search').val('');
	// Hide the "search results empty" notification
	$('.search-results-empty').addClass('d-none');
	// Update the count
	townhall_update_visible_count();
}

/**
 *  Run a function each time a filter checkbox is clicked
 *
 */
$(document).ready(function()
{
	const searchInput = document.getElementById('requests_search');
	// Event listener for the 'input' event
	if (searchInput)
	{
		searchInput.addEventListener('input', (event) =>
		{
			// Check if the current value of the input is an empty string
			if (event.currentTarget.value === "")
			{
				townhall_request_search_clear();
			}
			else
			{
				townhall_request_search(event.currentTarget.value);
				townhall_request_search_check_no_results();
			}
		});
	}
});

/**
 *  Search
 *
 *  @ term => string representing the current search term
 */
function townhall_request_search(term)
{
	let containers = document.querySelectorAll('.townhall_request_row');
	let searchTerm = term.toLowerCase();
	// show the "reset filters" button
	$('.townhall_btn_filters_reset').removeClass('d-none');

	containers.forEach(e =>
	{
		//if (e.textContent.toLowerCase().includes(searchTerm))
		if (
			!e.classList.contains('filter-hide') &&
			(
			e.querySelector('.townhall_request_row_request_name').textContent.toLowerCase().includes(searchTerm) ||
			e.querySelector('.townhall_request_row_contact').textContent.toLowerCase().includes(searchTerm) ||
			e.querySelector('.townhall_request_row_session_name').textContent.toLowerCase().includes(searchTerm)
			))
		{
			// Ensure previously hidden items are shown
			e.closest('.townhall_request_row').classList.remove('d-none');
		}
		else
		{
			// Hide the list item
			e.closest('.townhall_request_row').classList.add('d-none');
		}
	});
	// Update the count
	townhall_update_visible_count();
}

/**
 *  Clear the search results
 */
function townhall_request_search_clear(leaveFilters = true)
{
	if (leaveFilters == true)
	{
		$('.townhall_request_row:not(.filter-hide)').removeClass('d-none');
		// Show the "reset all filters" button
		$('.townhall_btn_filters_reset').removeClass('d-none');
	}
	else
	{
		$('.townhall_request_row').removeClass('d-none');
		// Hide the "reset all filters" button
		$('.townhall_btn_filters_reset').addClass('d-none');
	}
	// Hide the "search results empty" notification
	$('.search-results-empty').addClass('d-none');
	// Update the count
	townhall_update_visible_count();
}

/**
 *  Show the "no results" content if applicable
 */
function townhall_request_search_check_no_results()
{
	if ($(".townhall_request_row").length === $(".townhall_request_row.d-none").length)
	{
		// No results found
		$('.search-results-empty').removeClass('d-none');
		$('.townhall_request_table_header').addClass('d-none');
	}
	else
	{
		// Results found
		$('.search-results-empty').addClass('d-none');
		$('.townhall_request_table_header').removeClass('d-none');
	}
}

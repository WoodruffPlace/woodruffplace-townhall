// Document ready functions
$(document).ready(function()
{
	if (document.getElementsByClassName("form_event_date_select"))
	{
		// Limit date fields to future dates only
		// Get today's date
		var today = new Date().toISOString().split('T')[0];
		// Set the minimum selectable date to today
		var date_felds = document.getElementsByClassName("form_event_date_select");
		for (var i = 0; i < date_felds.length; i++)
		{
			date_felds[i].setAttribute("min", today);
		}

		// Set the end date based on the start date if null
		$('[name="session_start_date"]').change(function()
		{
			// Set min value to match start
			$('[name="session_end_date"]').prop('min', ($('[name="session_start_date"]').val()));
			// Automatically set the value of the end date based on the start
			if ($('[name="session_end_date"]').val() == '')
			{
				$('[name="session_end_date"]').val($('[name="session_start_date"]').val());
			}
		});

		// Set the max on the start date to match the end date
		// Set the end date based on the start date if null
		$('[name="session_end_date"]').change(function()
		{
			$('[name="session_start_date"]').prop('max', ($('[name="session_end_date"]').val()));
		});
	}
	else
	{
		console.log("testing");
	}
});

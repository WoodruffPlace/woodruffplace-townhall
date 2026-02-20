// Document ready functions
$(document).ready(function()
{
	// Listen for changes to the main/billing contact form
	$('#form-request-checklist input').change(function()
	{
		validate();
	}
	);
});

// Disable the submit button unless all items are checked
function validate()
{
	var button_disable = false;
	$("#form-request-checklist .form-check-input[required]").each(function(e)
	{
		if ($(this).prop('checked') != true)
		{
			button_disable = true;
		}
		$("button#checklist_submit").prop('disabled', button_disable);
	});
}

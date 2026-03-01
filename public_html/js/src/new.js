// Document ready functions
$(document).ready(function()
{
	// Listen for changes to the main/billing contact form
	form_requestcontact_check_validity();
	$('.form_request_contact').change(function()
	{
		form_requestcontact_check_validity();
	}
	);
	// Listen for changes to the add/edit session form
	$('.form_block_session input, .form_block_session textarea, .form_block_session select').change(function()
	{
		form_addsession_check_validity();
	});
});


/**
 *  Sponsor block
 */

// User clicks "yes"
$('#form_wpcl_member_yes').on('click', function()
{
	if (!$(".form_block_sponsor").hasClass("d-none"))
	{
		$(".form_block_sponsor").addClass("d-none");
	}
});
// User clicks "no"
$('#form_wpcl_member_no').on('click', function()
{
	$(".form_block_sponsor").removeClass("d-none");
});


// Remove modal
$('.form_block_session_remove').on('click', function()
{
	$("#session_to_remove").val($(this).data("session-to-remove"));
});

// Change form back to "add"
$("#form_block_session_add").on('click', function ()
{
	// Update modal title
	const modal_title = $("#modal_session_add_edit").find(".modal-title");
	modal_title.text("Add a session");
	// Update submit button
	$(".btn_form_submit_addedit").text("Add session");
	$(".btn_form_submit_addedit").attr("value", "session_add");
	$(".btn_form_submit_addedit").prop('disabled', true);

	// Clear out any form fields
	$(".form_block_session").find(".form-control").each(function(index, element)
	{
		$(element).val('');
	});
	$(".form_block_session").find(".form-select").each(function(index, element)
	{
		$(element).val('');
	});
});

// Edit modal
$('.form_block_session_edit').on('click', function()
{
	const button_action = $(this).data("bs-action");
	const item_to_edit = $(this).data("bs-itemtoedit");
	const url = "/api/session-get-session/" + item_to_edit;
	// Get data and populate the form
	$.getJSON(url, function(data)
	{
		// Update form fields
		$("#session_to_edit").val(item_to_edit);
		// Session info
		if (data.session_name){ $("input[name='session_name']").val(data.session_name); }
		if (data.session_start_date){ $("input[name='session_start_date']").val(data.session_start_date); }
		if (data.session_start_time){ $("input[name='session_start_time']").val(data.session_start_time); }
		if (data.session_end_date){ $("input[name='session_end_date']").val(data.session_end_date); }
		if (data.session_end_time){ $("input[name='session_end_time']").val(data.session_end_time); }
		if (data.session_attendance){ $("select[name='session_attendance']").val(data.session_attendance); }
		if (data.session_alcohol){ $("select[name='session_alcohol']").val(data.session_alcohol); }
		// Update modal title
		const modal_title = $("#modal_session_add_edit").find(".modal-title");
		modal_title.text("Editing session");
		// Update submit button text
		$(".btn_form_submit_addedit").text("Save");
		$(".btn_form_submit_addedit").prop('disabled', false);
		$(".btn_form_submit_addedit").attr("value", "session_edit");
	});
});

// Clear the form fields on modal close
$('#modal_session_add_edit').on('hidden.bs.modal', function ()
{
	$('#modal_session_add_edit .form-check-input').prop('checked', false);
	$('#modal_session_add_edit .btn-check').prop('checked', false);
	$(".btn_form_submit_addedit").prop('disabled', true);
});


// Main form - billing contact info
function form_requestcontact_check_validity()
{
	var button_disable = false;
	var is_wpcl_member = false;
	// Swap required attribute on sponsor fields, depending on whether
	// the current user is a WPCL member
	if ($('input[name="wpcl_member"]:checked').val() !== "y")
	{
		form_wpcl_sponsor_fields_required(true);
	}
	else
	{
		form_wpcl_sponsor_fields_required(false);
		is_wpcl_member = true;
	}
	// Iterate over your required fields
	$(".form_request_contact[required]").each(function(e)
	{
		// Check if the field is empty after trimming whitespace
		//   + Will not check an entire radio group
		//   + Will skip the WPCL sponsor fields if is_wpcl_member
		if ($(this).attr('name').includes('wpcl_sponsor') && is_wpcl_member)
		{
			return false;
		}
		else if ($(this).attr('name') == "agreement" && $(this).prop('checked') == false)
		{
			button_disable = true;
		}
		else
		{
			if ($(this).val().trim() === '')
			{
				button_disable = true;
			}
			// Check for any radio groups
			if ($(this).is(':radio') && form_radio_group_is_empty($(this).attr('name')))
			{
				button_disable = true;
			}
		}
	});

	// Enable or disable the submit button based on the validation result
	$("button#route_review").prop('disabled', button_disable);
}

// Set a required flag on the WPCL sponsor fields
function form_wpcl_sponsor_fields_required(flag)
{
	$("input[name='wpcl_sponsor_name_first']").attr('required', flag);
	$("input[name='wpcl_sponsor_name_last']").attr('required', flag);
	$("input[name='wpcl_sponsor_email']").attr('required', flag);
	$("input[name='wpcl_sponsor_phone']").attr('required', flag);
}

// Take an input element by the name attribute and see if it's
// part of a radio group that's empty
function form_radio_group_is_empty(name)
{
	if ($('[name=' + name + ']').is(':radio') && !$('[name=' + name + ']').is(':checked'))
	{
		return true;
	}
	else
	{
		return false;
	}
}


// New/edit session form
function form_addsession_check_validity()
{
	var fields_complete_input = true;
	var fields_complete_radio = false;
	var button_disable = true;
	// Iterate over your required fields
	$(".form_block_session [required]").each(function()
	{
		// Check if the field is empty after trimming whitespace
		if ($(this).val() == null)
		{
			fields_complete_input = false;
		}
		else if ($(this).val().trim() === '')
		{
			fields_complete_input = false;
		}
		// Check both and return
		if (fields_complete_input == true)
		{
			button_disable = false;
		}
		else
		{
			button_disable = true;
		}
	});

	// Enable or disable the submit button based on the validation result
	$(".btn_form_submit_addedit").prop('disabled', button_disable);
}

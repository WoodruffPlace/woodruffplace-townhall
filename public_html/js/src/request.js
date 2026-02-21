// Document ready functions
$(document).ready(function()
{

});


/**
 *  Request screen
 */
// Edit modal
$('.form_block_event_session_edit').on('click', function()
{
	const button_action = $(this).data("bs-action");
	const item_to_edit = $(this).data("bs-itemtoedit");
	const url = "/api/event-get-session/" + item_to_edit;
	// Get data and populate the form
	$.getJSON(url, function(data)
	{
		if (data.session_name){ $("input[name='session_to_edit']").val(data.session_eventID); }
		if (data.session_name){ $("input[name='session_name']").val(data.session_name); }
		if (data.session_start_date){ $("input[name='session_start_date']").val(data.session_start_date); }
		if (data.session_start_time){ $("input[name='session_start_time']").val(data.session_start_time); }
		if (data.session_end_date){ $("input[name='session_end_date']").val(data.session_end_date); }
		if (data.session_end_time){ $("input[name='session_end_time']").val(data.session_end_time); }
		if (data.session_fee_rental){ $("select[name='session_attendance']").val(data.session_fee_rental); }
		if (data.session_fee_alcohol){ $("select[name='session_alcohol']").val(data.session_fee_alcohol); }
		if (data.session_fee_waiver_rental && data.session_fee_waiver_rental == "y"){ $("input[name='fee_waiver_rental']").prop('checked', true); }
		if (data.session_fee_waiver_alcohol && data.session_fee_waiver_alcohol == "y"){ $("input[name='fee_waiver_alcohol']").prop('checked', true); }

		// Update modal info
		const modal_title = $("#modal_session_add_edit").find(".modal-title");
		modal_title.text("Editing session");
		// Update submit button text
		$(".btn_form_submit_addedit").text("Save");
		$(".btn_form_submit_addedit").prop('disabled', false);
		$(".btn_form_submit_addedit").attr("value", "session_edit");

		// Disable the alcohol waive switch if alcohol = no
		if (data.session_fee_alcohol != "y")
		{
			$('#fee_waiver_alcohol').prop('disabled', true);
		}
	});
});

// Alcohol fee toggle
$('.form_event_alcohol').change(function()
{
	$('#fee_waiver_alcohol').prop('disabled', true);
	$('#fee_waiver_alcohol').prop('checked', false);
	if ($(this).val() == 'y')
	{
		$('#fee_waiver_alcohol').prop('disabled', false);
	}
});

// Cleaning fee
$('#form_fee_cleaning_waiver').change(function()
{
	if ($(this).is(':checked'))
	{
		$('.fee_waived_cleaning_hide').addClass('d-none');
		$('.fee_waived_cleaning_show').removeClass('d-none');
	}
	else
	{
		$('.fee_waived_cleaning_hide').removeClass('d-none');
		$('.fee_waived_cleaning_show').addClass('d-none');
	}
	total_recalculate();
});

// Security deposit
$('#form_fee_security_waiver').change(function()
{
	if ($(this).is(':checked'))
	{
		$('.fee_waived_security_hide').addClass('d-none');
		$('.fee_waived_security_show').removeClass('d-none');
	}
	else
	{
		$('.fee_waived_security_hide').removeClass('d-none');
		$('.fee_waived_security_show').addClass('d-none');
	}
	total_recalculate();
});

// Recalculate request total
function total_recalculate()
{
	// Get total
	let fee_total = get_frontend_number('#form_display_cost_total_unwaived');

	// Store new total
	let fee_total_new = fee_total;

	// Get cleaning fee
	let fee_cleaning = get_frontend_number('#form_display_cost_cleaning');

	// Get security fee
	let fee_security = get_frontend_number('#form_display_cost_security');

	// Check cleaning fee, remove from total if checked
	if ($('#form_fee_cleaning_waiver').is(':checked'))
	{
		fee_total_new = Number(fee_total_new) - Number(fee_cleaning);
	}

	// Check security fee, remove from total if checked
	if ($('#form_fee_security_waiver').is(':checked'))
	{
		fee_total_new = Number(fee_total_new) - Number(fee_security);
	}
	// else
	// {
	// 	fee_total_new = Number(fee_total_new) + Number(fee_security);
	// }

	// Replace front-end HTML
	$("#form_display_cost_total").html(fee_total_new);
}


function get_frontend_number(element)
{
	let temp = $(element).html();
	temp.trim();
	let numbers = temp.replace('$', '');
	numbers.trim();
	return numbers;
}

// Notes editing

function notes_toggle(toggle)
{
	switch (toggle)
	{
		case 'edit':
			$('.admin_notes_view').addClass('d-none');
			$('.admin_notes_edit').removeClass('d-none');
			$('.btn-request-notes-edit').addClass('d-none');
		break;
		case 'view':
			$('.admin_notes_view').removeClass('d-none');
			$('.admin_notes_edit').addClass('d-none');
			$('.btn-request-notes-edit').removeClass('d-none');
		break;
	}
}
// Edit activator
$('.btn-request-notes-edit').on('click', function()
{
	notes_toggle('edit');
});
// Cancel (view activator w/o save)
$('.btn-request-notes-view').on('click', function()
{
	notes_toggle('view');
});

// Swap the "change status" dropdown
// Security deposit
$('#request_status_switch').change(function(e)
{
	// Default actions
	$('.alert_transition').addClass('d-none');
	$('#request_status_transition').removeClass('btn-primary');
	$('#request_status_transition').removeClass('btn-success');
	$('#request_status_transition').removeClass('btn-danger');

	switch ($(this).val())
	{
		case 'denied':
			$('#alert_transition_denied').removeClass('d-none');
			// Button
			$('#request_status_transition').addClass('btn-danger');
			$('#request_status_transition').html('Deny request');
		break;
		case 'approved':
			$('#alert_transition_approved').removeClass('d-none');
			// button
			$('#request_status_transition').addClass('btn-success');
			$('#request_status_transition').html('Approve request');

		break;
		default:
			$('#request_status_transition').addClass('btn-primary');
			$('#request_status_transition').html('Save request');
	}
});

<div class="text-secondary">
	<span class="d-block fs-7">Request status:</span><span class="bi bi-circle-fill <?php echo Request::status_display($request->request_get('status'))['color-text']; ?> me-1"></span> <span class="fw-medium fs-7 text-secondary-emphasis"><?php echo Request::status_display($request->request_get('status'))['status']; ?>
	<?php
	if ($request->request_get('is_wp_event') != '1')
	{
		switch ($request->request_get('status'))
		{
			case 'approved':
				echo '<span class="badge rounded-pill bg-warning text-bg-warning ms-1">Awaiting payment</span>';
			break;
			case 'scheduled':
				echo '<span class="badge rounded-pill bg-success text-bg-success ms-1">Paid</span>';
			break;
		}
	}
	?>
</div>

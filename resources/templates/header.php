<?php $page_header = TRUE; ?>
<!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo (!empty($page->title)) ? $page->title : "Business Memberships | Mass Ave Indy"; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Funnel+Sans:ital,wght@0,300..800;1,300..800&family=Kameron:wght@400..700&display=swap" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="<?php echo $config['path']['css'];?>/styles.css">
	</head>
	<body>
		<?php if ($page->page_omit_content_header() !== TRUE): ?>
		<header class="header">
			<div class="row mx-0 g-0">
				<div class="col-12">
					<div class="container-xl">
						<div class="row g-0">
							<div class="col-12 py-3 py-md-4">
								<h1 class="text-white text-heading">Woodruff Place Town Hall <span class="d-block fs-3 text-white-50">Rental Requests</span></h1>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>
		<?php endif; ?>

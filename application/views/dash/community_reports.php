<?php defined('BASEPATH') OR exit('No direct script access allowed');
$portal_url = isset($portal_url) ? $portal_url : site_url('portal');
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
$reports = isset($reports) && is_array($reports) ? $reports : array();
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
$report_statuses = isset($report_statuses) && is_array($report_statuses) ? $report_statuses : array();
$form_values = isset($report_form_values) && is_array($report_form_values) ? $report_form_values : array();
$form_value = function ($key) use ($form_values) {
	return isset($form_values[$key]) ? html_escape($form_values[$key]) : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224"><meta name="robots" content="noindex,nofollow">
	<meta name="description" content="Submit and track a local flooding or hazard report for MDRRMO review.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20260924d">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261006j">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="portal-page resident-portal community-reports-page">
	<a class="skip-link" href="#main">Skip to content</a>
	<div class="gov-bar"><div class="gov-bar__inner"><span>Republic of the Philippines · Municipality of Dulag, Leyte</span><span>Municipal Disaster Risk Reduction and Management Office</span></div></div>
	<header class="topbar"><div class="topbar__inner"><a class="brand" href="<?php echo html_escape($portal_url); ?>"><span class="brand__mark"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span><span class="brand__text"><span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span><span class="brand__name brand__name--mobile">Daguitan Monitor</span></span></a><div class="topbar__actions"><span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span><a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a><button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false"><span></span><span></span><span></span></button></div></div></header>
	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation">
			<div class="resident-sidebar__profile"><img class="resident-sidebar__avatar resident-sidebar__avatar--logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt=""><div><strong><?php echo html_escape($auth_name); ?></strong><span>Resident account</span></div></div>
			<nav class="resident-sidebar__nav" aria-label="Resident portal">
				<a href="<?php echo html_escape($portal_url); ?>">Dashboard</a>
				<a href="<?php echo site_url('portal/go-bag'); ?>">Go Bag</a>
				<a href="<?php echo site_url('portal/evacuation-centers'); ?>">Evacuation Centers</a>
				<a href="<?php echo site_url('portal/announcements'); ?>">Announcements</a>
				<a class="is-active" href="<?php echo site_url('portal/reports'); ?>" aria-current="page">Flood / Hazard Reports</a>
				<a href="<?php echo site_url('portal/profile'); ?>">My Profile</a>
				<a href="<?php echo site_url('portal/help'); ?>">Help / How to Use</a>
			</nav>
			<a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			<div class="resident-sidebar__government"><span>Republic of the Philippines</span><strong>Municipality of Dulag, Leyte</strong></div>
		</aside>
		<div class="resident-layout__content">
			<section class="hero community-reports-hero">
				<div class="hero__copy">
					<p class="eyebrow">Community safety · Dulag, Leyte</p>
					<h1>Flood / Hazard Reports</h1>
					<p class="lede">Report flooding, rising water, blocked drainage, or another local hazard that the bridge monitor may not detect.</p>
				</div>
			</section>
			<div class="community-report-emergency" role="note">
				<strong>Not an emergency service</strong>
				<span>Reports are reviewed by MDRRMO and may not receive an immediate response. If anyone is in immediate danger, do not wait for this form—follow official emergency instructions and contact local responders directly.</span>
			</div>
			<?php if ( ! empty($report_notice)): ?><p class="community-report-message community-report-message--success" role="status"><?php echo html_escape($report_notice); ?></p><?php endif; ?>
			<?php if ( ! empty($report_error)): ?><p class="community-report-message community-report-message--error" role="alert"><?php echo html_escape($report_error); ?></p><?php endif; ?>
			<div class="community-reports-grid">
				<section class="community-report-panel" aria-labelledby="reportFormTitle">
					<header><p class="card-kicker">Submit a report</p><h2 id="reportFormTitle">What did you observe?</h2><p>Share a specific location and what you saw. Photo is optional.</p></header>
					<form class="community-report-form" method="post" enctype="multipart/form-data" action="<?php echo site_url('portal/reports'); ?>">
						<input type="hidden" name="report_token" value="<?php echo html_escape($report_token); ?>">
						<div class="community-report-form__fields">
							<label for="reportType">Report type
								<select id="reportType" name="report_type" required>
									<option value="">Select type</option>
									<option value="flooding"<?php echo $form_value('report_type') === 'flooding' ? ' selected' : ''; ?>>Flooding</option>
									<option value="blocked_drainage"<?php echo $form_value('report_type') === 'blocked_drainage' ? ' selected' : ''; ?>>Blocked drainage</option>
									<option value="rising_water"<?php echo $form_value('report_type') === 'rising_water' ? ' selected' : ''; ?>>Rising water</option>
									<option value="other_hazard"<?php echo $form_value('report_type') === 'other_hazard' ? ' selected' : ''; ?>>Other hazard</option>
								</select>
							</label>
							<label for="reportBarangay">Barangay
								<select id="reportBarangay" name="barangay" required>
									<option value="">Select barangay</option>
									<?php foreach ($barangays as $key => $label): ?>
										<option value="<?php echo html_escape($key); ?>"<?php echo $form_value('barangay') === html_escape($key) ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<label class="community-report-form__wide" for="reportLandmark">Street, landmark, or location
								<input id="reportLandmark" name="landmark" type="text" maxlength="160" required value="<?php echo $form_value('landmark'); ?>" placeholder="Nearby road, bridge, school, or recognizable place">
							</label>
							<label class="community-report-form__wide" for="reportDescription">Description
								<textarea id="reportDescription" name="description" rows="5" maxlength="2000" required placeholder="Describe what you observed and when. Do not include sensitive personal information."><?php echo $form_value('description'); ?></textarea>
							</label>
							<label class="community-report-form__wide" for="reportPhoto">Photo (optional)
								<input id="reportPhoto" name="report_photo" type="file" accept="image/jpeg,image/png,image/webp">
								<small>JPEG, PNG, or WebP; maximum 3 MB. Avoid including identifiable people if possible.</small>
							</label>
						</div>
						<button class="btn btn--primary" type="submit">Submit report</button>
						<p class="community-report-form__privacy">Your report and optional photo are visible only to you and authorized MDRRMO admins. Reports are stored in this site's local database.</p>
					</form>
				</section>
				<section class="community-report-history" aria-labelledby="reportHistoryTitle">
					<header><p class="card-kicker">Your submissions</p><h2 id="reportHistoryTitle">Track your reports <span><?php echo count($reports); ?></span></h2><p>Status and MDRRMO follow-up updates appear here.</p></header>
					<?php if (empty($reports)): ?>
						<div class="community-report-empty">
							<strong>No reports yet</strong>
							<span>Once you submit a report, its current status and any MDRRMO follow-up will appear here.</span>
						</div>
					<?php else: ?>
						<div class="community-report-list">
							<?php foreach ($reports as $report): ?>
								<article class="community-report-card">
									<div class="community-report-card__head">
										<div><span class="community-report-type"><?php echo html_escape($report['type_label']); ?></span><h3><?php echo html_escape($report['landmark']); ?></h3></div>
										<span class="community-report-status community-report-status--<?php echo html_escape($report['status']); ?>"><?php echo html_escape($report['status_label']); ?></span>
									</div>
									<p class="community-report-card__area">Barangay <?php echo html_escape($report['barangay']); ?> · <?php echo html_escape(date('M j, Y · g:i A', strtotime($report['created_at']))); ?></p>
									<p class="community-report-card__description"><?php echo nl2br(html_escape($report['description'])); ?></p>
									<?php if (!empty($report['has_photo'])): ?><a class="community-report-photo-link" href="<?php echo html_escape($report_photo_base . '/' . (int) $report['id']); ?>" target="_blank" rel="noopener">View submitted photo</a><?php endif; ?>
									<?php if (!empty($report['admin_note'])): ?><div class="community-report-followup"><strong>MDRRMO follow-up</strong><p><?php echo nl2br(html_escape($report['admin_note'])); ?></p><?php if (!empty($report['reviewed_by'])): ?><small>Updated by <?php echo html_escape($report['reviewed_by']); ?></small><?php endif; ?></div><?php endif; ?>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			</div>
		</div>
	</main>
	<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
	<footer class="footer portal-footer"><div class="portal-footer__inner"><p class="footer__note">In an emergency, follow MDRRMO and barangay instructions. This report form is not monitored as an emergency hotline.</p></div></footer>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=1"></script>
</body>
</html>

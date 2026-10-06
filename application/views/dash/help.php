<?php defined('BASEPATH') OR exit('No direct script access allowed');
$portal_url = isset($portal_url) ? $portal_url : site_url('portal');
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
$monitor = isset($monitor) && is_array($monitor) ? $monitor : array();
$warning_level = isset($monitor['warning_level']) && in_array($monitor['warning_level'], array('green', 'yellow', 'red'), TRUE)
	? $monitor['warning_level']
	: '';
$selected_level = $warning_level !== '' ? $warning_level : 'green';
$warning_labels = array('green' => 'Safe', 'yellow' => 'Monitor', 'red' => 'Critical');
$warning_label = $warning_level !== '' ? $warning_labels[$warning_level] : 'Unavailable';
$sensor_label = isset($monitor['sensor_label']) ? $monitor['sensor_label'] : 'Status unavailable';
$last_updated = ! empty($monitor['last_updated']) ? $monitor['last_updated'] : 'Not available';
$status_url = isset($status_url) ? $status_url : site_url('api/status');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224"><title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20260924d">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261006e">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="portal-page resident-portal help-page">
	<a class="skip-link" href="#main">Skip to content</a>
	<div class="gov-bar"><div class="gov-bar__inner"><span>Republic of the Philippines · Municipality of Dulag, Leyte</span><span>Municipal Disaster Risk Reduction and Management Office</span></div></div>
	<header class="topbar"><div class="topbar__inner"><a class="brand" href="<?php echo html_escape($portal_url); ?>"><span class="brand__mark"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span><span class="brand__text"><span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span><span class="brand__name brand__name--mobile">Daguitan Monitor</span></span></a><div class="topbar__actions"><span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span><a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a><button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false"><span></span><span></span><span></span></button></div></div></header>
	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation"><div class="resident-sidebar__profile"><img class="resident-sidebar__avatar resident-sidebar__avatar--logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt=""><div><strong><?php echo html_escape($auth_name); ?></strong><span>Resident account</span></div></div><nav class="resident-sidebar__nav" aria-label="Resident portal"><a href="<?php echo html_escape($portal_url); ?>">Dashboard</a><a href="<?php echo site_url('portal/go-bag'); ?>">Go Bag</a><a href="<?php echo site_url('portal/evacuation-centers'); ?>">Evacuation Centers</a><a href="<?php echo site_url('portal/announcements'); ?>">Announcements</a><a href="<?php echo site_url('portal/reports'); ?>">Flood / Hazard Reports</a><a href="<?php echo site_url('portal/profile'); ?>">My Profile</a><a class="is-active" href="<?php echo site_url('portal/help'); ?>" aria-current="page">Help / How to Use</a></nav><a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a><div class="resident-sidebar__government"><span>Republic of the Philippines</span><strong>Municipality of Dulag, Leyte</strong></div></aside>
		<div class="resident-layout__content">
			<section class="hero help-page__hero">
				<div class="hero__copy">
					<p class="eyebrow">Resident guide</p>
					<h1>Help / How to Use</h1>
					<p class="lede">A practical guide to reading flood updates and getting your household ready. In an emergency, follow official instructions from MDRRMO and barangay officials.</p>
					<div class="help-page__hero-actions">
						<a class="btn btn--primary" href="<?php echo html_escape($portal_url); ?>">View live status</a>
						<a class="btn btn--ghost" href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>">Find evacuation centers</a>
					</div>
				</div>
			</section>

			<section class="help-steps" aria-label="How to use the resident portal">
				<header class="help-section-head">
					<div><p class="card-kicker">Portal basics</p><h2>Know what to do</h2></div>
					<p>Use these tools to stay informed and prepare before conditions change.</p>
				</header>
				<div class="help-grid">
					<article class="glass-card help-card">
						<span class="help-card__number" aria-hidden="true">01</span>
						<div><h3>Read the water level</h3><p>Check the current level, trend, and last update on the dashboard. A rising trend means conditions may be changing.</p></div>
						<a href="<?php echo html_escape($portal_url); ?>">Open dashboard <span aria-hidden="true">→</span></a>
					</article>
					<article class="glass-card help-card">
						<span class="help-card__number" aria-hidden="true">02</span>
						<div><h3>Know where to go</h3><p>View listed evacuation locations and directions. Follow the center assigned by barangay officials if it differs.</p></div>
						<a href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>">View evacuation centers <span aria-hidden="true">→</span></a>
					</article>
					<article class="glass-card help-card">
						<span class="help-card__number" aria-hidden="true">03</span>
						<div><h3>Prepare your Go Bag</h3><p>Use the checklist to track essential supplies, documents, medicines, water, and other household needs.</p></div>
						<a href="<?php echo html_escape(site_url('portal/go-bag')); ?>">Open Go Bag checklist <span aria-hidden="true">→</span></a>
					</article>
					<article class="glass-card help-card">
						<span class="help-card__number" aria-hidden="true">04</span>
						<div><h3>Follow official advisories</h3><p>Use announcements for official notices. Do not rely on forwarded or unverified emergency information.</p></div>
						<a href="<?php echo html_escape(site_url('portal/announcements')); ?>">Read announcements <span aria-hidden="true">→</span></a>
					</article>
				</div>
			</section>

			<section class="help-action-guide" aria-labelledby="helpActionTitle" data-status-url="<?php echo html_escape($status_url); ?>" data-current-level="<?php echo html_escape($warning_level); ?>">
				<header class="help-section-head help-action-guide__head">
					<div><p class="card-kicker">Flood response guide</p><h2 id="helpActionTitle">What to do at each warning level</h2></div>
					<p>Start with the latest monitor status, then follow instructions from MDRRMO and your barangay.</p>
				</header>
				<div class="help-current-status" data-level="<?php echo html_escape($warning_level !== '' ? $warning_level : 'unavailable'); ?>">
					<div class="help-current-status__copy">
						<span class="help-current-status__label">Most recent monitor level</span>
						<strong id="helpCurrentLevel"><?php echo html_escape($warning_label); ?></strong>
						<span id="helpSensorStatus">Sensor: <?php echo html_escape($sensor_label); ?></span>
					</div>
					<div class="help-current-status__meta">
						<span>Last update</span>
						<strong id="helpLastUpdated"><?php echo html_escape($last_updated); ?></strong>
					</div>
					<button class="btn btn--ghost" id="helpRefreshStatus" type="button">Refresh status</button>
					<p class="help-current-status__notice" id="helpStatusMessage" role="status" aria-live="polite"><?php echo $warning_level !== '' ? 'This is the most recent status available when this page loaded.' : 'The latest warning level is unavailable. Refresh the status or check official announcements.'; ?></p>
				</div>
				<p class="help-action-guide__disclaimer">Monitor readings are for awareness and do not replace official evacuation orders. If readings are unavailable or delayed, use official announcements and contact local officials for guidance.</p>
				<div class="help-level-tabs" role="tablist" aria-label="Select a flood warning level">
					<button class="help-level-tab" id="helpTabGreen" type="button" role="tab" aria-controls="helpPanelGreen" aria-selected="<?php echo $selected_level === 'green' ? 'true' : 'false'; ?>" tabindex="<?php echo $selected_level === 'green' ? '0' : '-1'; ?>" data-level="green">Safe</button>
					<button class="help-level-tab" id="helpTabYellow" type="button" role="tab" aria-controls="helpPanelYellow" aria-selected="<?php echo $selected_level === 'yellow' ? 'true' : 'false'; ?>" tabindex="<?php echo $selected_level === 'yellow' ? '0' : '-1'; ?>" data-level="yellow">Monitor</button>
					<button class="help-level-tab" id="helpTabRed" type="button" role="tab" aria-controls="helpPanelRed" aria-selected="<?php echo $selected_level === 'red' ? 'true' : 'false'; ?>" tabindex="<?php echo $selected_level === 'red' ? '0' : '-1'; ?>" data-level="red">Critical</button>
				</div>
				<div class="help-level-panel" id="helpPanelGreen" role="tabpanel" aria-labelledby="helpTabGreen" data-level-panel="green" <?php echo $selected_level === 'green' ? '' : 'hidden'; ?>>
					<p class="help-level-panel__eyebrow">Safe · Stay prepared</p>
					<h3>Keep informed before conditions change</h3>
					<ul><li>Check the dashboard and official announcements regularly.</li><li>Keep your Go Bag, essential medicines, and important documents ready.</li><li>Know your barangay-assigned evacuation location and a safe route.</li></ul>
				</div>
				<div class="help-level-panel" id="helpPanelYellow" role="tabpanel" aria-labelledby="helpTabYellow" data-level-panel="yellow" <?php echo $selected_level === 'yellow' ? '' : 'hidden'; ?>>
					<p class="help-level-panel__eyebrow">Monitor · Prepare to act</p>
					<h3>Pay close attention and get ready</h3>
					<ul><li>Watch for updated readings and instructions from MDRRMO and barangay officials.</li><li>Move essential items, medicines, and documents where they can be collected quickly.</li><li>Avoid riverbanks and other flood-prone areas; be ready to leave if instructed.</li></ul>
				</div>
				<div class="help-level-panel" id="helpPanelRed" role="tabpanel" aria-labelledby="helpTabRed" data-level-panel="red" <?php echo $selected_level === 'red' ? '' : 'hidden'; ?>>
					<p class="help-level-panel__eyebrow">Critical · Prioritize safety</p>
					<h3>Follow official instructions immediately</h3>
					<ul><li>If evacuation is ordered, leave promptly for the location directed by local officials.</li><li>Do not walk, drive, or ride through floodwater; avoid bridges and fast-moving water.</li><li>Help children, older adults, persons with disabilities, and neighbors who may need assistance, if safe to do so.</li></ul>
				</div>
				<div class="help-guide-links">
					<a href="<?php echo html_escape(site_url('portal/announcements')); ?>">Official announcements <span aria-hidden="true">→</span></a>
					<a href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>">Evacuation centers <span aria-hidden="true">→</span></a>
					<a href="<?php echo html_escape(site_url('portal/go-bag')); ?>">Go Bag checklist <span aria-hidden="true">→</span></a>
					<button class="help-guide-links__print" id="helpPrint" type="button">Print this guide</button>
				</div>
			</section>
		</div>
	</main>
	<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
	<footer class="footer portal-footer"><div class="portal-footer__inner"><p class="footer__note">In an emergency, follow MDRRMO and barangay instructions.</p></div></footer>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/help-guide.js?v=20261006a"></script>
</body>
</html>

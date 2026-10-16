<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$a = $announcement;
$w = $weather;
$resident_announcements = (isset($announcements) && is_array($announcements)) ? $announcements : array();
$resident_barangay = isset($resident_barangay) ? trim((string) $resident_barangay) : '';
$trend_arrow = ($m['trend'] === 'falling') ? '↓' : (($m['trend'] === 'steady') ? '→' : '↑');
$warning_key = $m['warning_level'];
$gauge_pct = isset($m['gauge_pct']) ? (int) $m['gauge_pct'] : 8;
$ett_label = isset($m['ett_label']) ? $m['ett_label'] : '—';
$rate_prefix = ($m['rate_cm_min'] > 0) ? '+' : '';
$has_reading = isset($m['source']) && $m['source'] !== 'none';
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
$reading_is_current = isset($m['sensor_status']) && $m['sensor_status'] === 'online';
$initial_action = $reading_is_current && isset($actions[0]) && is_string($actions[0])
	? $actions[0]
	: 'The last reading may be out of date. Do not use it to decide whether it is safe; follow current MDRRMO and barangay instructions.';
$emergency_contact = NULL;
foreach ((isset($evacuation_centers) && is_array($evacuation_centers)) ? $evacuation_centers : array() as $center)
{
	$phone = isset($center['phone']) ? trim((string) $center['phone']) : '';
	$phone_link = preg_replace('/[^0-9+]/', '', $phone);
	if ($phone_link !== '')
	{
		$emergency_contact = array(
			'name' => isset($center['name']) ? (string) $center['name'] : 'Listed evacuation center',
			'phone_link' => 'tel:' . $phone_link,
		);
		break;
	}
}
$yellow_threshold = isset($m['threshold_yellow_m']) ? (float) $m['threshold_yellow_m'] : 0.1;
$red_threshold = isset($m['threshold_red_m']) ? (float) $m['threshold_red_m'] : 0.2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="Daguitan Monitor">
	<meta name="description" content="Resident flood watch for Daguitan Bridge, Dulag, Leyte.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="manifest" href="<?php echo html_escape($base_url); ?>manifest.webmanifest">
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="apple-touch-icon" href="<?php echo html_escape($asset_url); ?>icons/pwa-icon-192.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20261016-alert-panel3">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing-weather.css?v=1">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261014-hazard-popup1">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/resident-portal.css?v=20261014-announcement-images1">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="portal-page resident-portal resident-dashboard">
	<a class="skip-link" href="#main">Skip to content</a>

	<div class="gov-bar">
		<div class="gov-bar__inner">
			<span>Republic of the Philippines · Municipality of Dulag, Leyte</span>
			<span>Municipal Disaster Risk Reduction and Management Office</span>
		</div>
	</div>

	<div class="ambient" aria-hidden="true">
		<div class="ambient__blob ambient__blob--one"></div>
		<div class="ambient__blob ambient__blob--two"></div>
		<div class="ambient__blob ambient__blob--three"></div>
		<div class="ambient__particles" id="particles"></div>
	</div>

	<header class="topbar" id="topbar">
		<div class="topbar__inner">
			<a class="brand" href="<?php echo site_url('/'); ?>" aria-label="Daguitan Flood Monitor home">
				<span class="brand__mark" aria-hidden="true">
					<img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40">
				</span>
				<span class="brand__text">
					<span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span>
					<span class="brand__name brand__name--mobile">Daguitan Monitor</span>
				</span>
			</a>

			<div class="topbar__actions">
				<?php $this->load->view('partials/notifications_bell'); ?>
				<a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
				<button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>

	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation">
			<button class="resident-sidebar__close" type="button" id="residentSidebarClose" aria-label="Close resident navigation">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
			</button>
			<div class="resident-sidebar__profile">
				<button class="resident-sidebar__avatar-button" type="button" id="profileImageButton" aria-label="Choose profile image" title="Choose profile image">
					<img class="resident-sidebar__avatar resident-sidebar__avatar--logo" id="profileImage" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="">
				</button>
				<input class="resident-sidebar__file" type="file" id="profileImageInput" accept="image/*" hidden>
				<div>
					<strong><?php echo html_escape($auth_name); ?></strong>
					<span>Resident account</span>
				</div>
			</div>
			<nav class="resident-sidebar__nav" aria-label="Resident portal">
				<a class="is-active" href="#home" aria-current="page">Dashboard</a>
				<a href="<?php echo site_url('portal/go-bag'); ?>">Go Bag</a>
				<a href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>">Evacuation Centers</a>
				<a href="<?php echo html_escape(site_url('portal/announcements')); ?>">Announcements</a>
				<a href="<?php echo html_escape(site_url('portal/reports')); ?>">Flood / Hazard Reports</a>
				<a href="<?php echo site_url('portal/profile'); ?>">My Profile</a>
				<a href="<?php echo site_url('portal/help'); ?>">Help / How to Use</a>
			</nav>
			<a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			<div class="resident-sidebar__government">
				<span>Republic of the Philippines</span>
				<strong>Municipality of Dulag, Leyte</strong>
			</div>
		</aside>
		<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
		<div class="resident-layout__content">
		<section class="hero" id="home">
			<div class="hero__copy reveal">
				<p class="eyebrow">Resident portal · Daguitan Bridge</p>
				<h1>Welcome, <?php echo html_escape($auth_name); ?></h1>
				<p class="lede">Your flood watch for Daguitan Bridge. Live water level, warning status, and MDRRMO guidance — this does not replace official evacuation orders.</p>
			</div>

			<article class="status-panel reveal" aria-labelledby="statusTitle">
				<div class="status-panel__wave" aria-hidden="true"></div>
				<p class="status-panel__kicker" id="statusTitle">Flood Status Monitor</p>
				<button class="status-badge status-badge--<?php echo $reading_is_current ? html_escape($warning_key) : 'unavailable'; ?>" id="heroStatusBadge" type="button" data-warning-details="<?php echo html_escape($warning_key); ?>" aria-expanded="false" aria-controls="warningDetails" title="<?php echo $reading_is_current ? 'View impacts and safety guidance for this level' : 'A current reading is unavailable'; ?>"<?php echo $reading_is_current ? '' : ' disabled'; ?>>
					<span class="status-dot" aria-hidden="true"></span>
					<span id="heroStatusLabel"><?php echo $reading_is_current ? strtoupper(html_escape($m['warning_label'])) : 'STATUS UNAVAILABLE'; ?></span>
					<span class="sr-only" id="heroStatusSr"><?php echo $reading_is_current ? 'Warning level: ' . html_escape($m['warning_label']) : 'Current warning level unavailable because the station is not reporting live data.'; ?></span>
					<span class="portal-status-badge__hint" aria-hidden="true">Details</span>
				</button>
				<p class="portal-status-guidance" id="heroGuidance"><?php echo html_escape($initial_action); ?></p>
				<p class="portal-connection" id="portalConnection" role="status" aria-live="polite">
					<span class="portal-connection__dot" aria-hidden="true"></span>
					<span id="portalConnectionText"><?php echo html_escape($m['sensor_status'] === 'online' ? 'Live station · updates every few seconds' : ($m['sensor_status'] === 'waiting' ? 'Waiting for the first sensor reading' : 'Monitoring station offline · showing the last saved reading')); ?></span>
				</p>
				<p class="portal-threshold-note" id="portalThresholdNote">Advisory threshold: <?php echo number_format($yellow_threshold, 2); ?> m · Critical threshold: <?php echo number_format($red_threshold, 2); ?> m. These readings do not replace official evacuation orders.</p>
				<div class="status-panel__grid">
					<div>
						<span class="meta" id="heroWaterLabel"><?php echo $has_reading && ! $reading_is_current ? 'Last Water Level' : 'Water Level'; ?></span>
						<strong class="metric" id="heroWaterLevel"><?php echo $has_reading ? number_format($m['water_level_m'], 2) . ' m' : 'No reading'; ?></strong>
					</div>
					<div>
						<span class="meta" id="heroTrendLabel"><?php echo $has_reading && ! $reading_is_current ? 'Last Trend' : 'Trend'; ?></span>
						<strong id="heroTrend"><?php echo $has_reading ? $trend_arrow . ' ' . html_escape($m['trend_label']) : '—'; ?></strong>
					</div>
					<div>
						<span class="meta">Last Reading</span>
						<time id="heroUpdated" datetime="<?php echo html_escape($m['last_updated_iso']); ?>"><?php echo html_escape($m['last_updated']); ?></time>
					</div>
				</div>
				<div class="gauge gauge--<?php echo $reading_is_current ? html_escape($warning_key) : 'unavailable'; ?>" id="heroGauge" aria-hidden="true">
					<div class="gauge__fill" id="heroGaugeFill" style="--level: <?php echo (int) $gauge_pct; ?>%"></div>
				</div>
			</article>
		</section>

		<nav class="resident-quick-actions" aria-label="Emergency and preparedness actions">
			<a href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>"><strong>Find evacuation centers</strong><span>Locations and directions</span></a>
			<a href="<?php echo html_escape(site_url('portal/go-bag')); ?>"><strong>Prepare your Go Bag</strong><span>Open your checklist</span></a>
			<a href="<?php echo html_escape($base_url); ?>offline-guide.html"><strong>Offline safety guide</strong><span>Cached for offline access after first load</span></a>
			<?php if ($emergency_contact !== NULL): ?>
			<a href="<?php echo html_escape($emergency_contact['phone_link']); ?>" aria-label="Call listed contact for <?php echo html_escape($emergency_contact['name']); ?>"><strong>Call listed contact</strong><span><?php echo html_escape($emergency_contact['name']); ?></span></a>
			<?php endif; ?>
		</nav>

		<?php $this->load->view('partials/warning_details_dialog'); ?>

		<section class="section section--tight resident-barangay-alerts" aria-labelledby="barangayAlertsTitle">
			<header class="section__head">
				<div class="resident-barangay-alerts__intro">
					<h2 id="barangayAlertsTitle">Alerts for you</h2>
					<p><?php echo $resident_barangay !== '' ? 'Showing notices for Barangay ' . html_escape($resident_barangay) . ' and municipality-wide announcements.' : 'These notices are municipality-wide. <a href="' . html_escape(site_url('portal/profile')) . '">Add your barangay in My Profile</a> to receive notices targeted to your area.'; ?></p>
				</div>
				<a class="resident-barangay-alerts__all" href="<?php echo html_escape(site_url('portal/announcements')); ?>">All announcements <span aria-hidden="true">→</span></a>
			</header>
			<?php if ( ! empty($resident_announcements)): ?>
			<div class="resident-barangay-alerts__list<?php echo count($resident_announcements) === 1 ? ' resident-barangay-alerts__list--single' : ''; ?>">
				<?php foreach (array_slice($resident_announcements, 0, 4) as $resident_announcement):
					$notice_level = isset($resident_announcement['level']) ? $resident_announcement['level'] : 'info';
					$notice_target = isset($resident_announcement['barangay']) ? trim((string) $resident_announcement['barangay']) : '';
					$notice_label = ($notice_level === 'red') ? 'Emergency' : (($notice_level === 'yellow') ? 'Advisory' : 'Information');
					$notice_body = isset($resident_announcement['body']) ? (string) $resident_announcement['body'] : '';
				?>
				<article class="resident-barangay-alert resident-barangay-alert--<?php echo html_escape($notice_level); ?>">
					<div class="resident-barangay-alert__meta">
						<span><?php echo html_escape($notice_label); ?></span>
						<span><?php echo $notice_target !== '' ? 'Barangay ' . html_escape($notice_target) : 'Municipality-wide'; ?></span>
					</div>
					<h3><?php echo html_escape($resident_announcement['title']); ?></h3>
					<details class="resident-barangay-alert__details">
						<summary>Read full announcement</summary>
						<p><?php echo nl2br(html_escape($notice_body)); ?></p>
						<?php if ( ! empty($resident_announcement['images'])): ?>
							<div class="resident-barangay-alert__images" aria-label="Announcement photos">
								<?php foreach ($resident_announcement['images'] as $image_index => $image_name):
									if ( ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $image_name)) continue;
								?>
									<img src="<?php echo html_escape(site_url('announcement-image/' . $image_name)); ?>" alt="Photo <?php echo (int) $image_index + 1; ?> attached to <?php echo html_escape($resident_announcement['title']); ?>" loading="lazy">
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</details>
				</article>
				<?php endforeach; ?>
			</div>
			<?php else: ?>
			<p class="resident-barangay-alerts__empty"><?php echo $resident_barangay !== '' ? 'There are no current announcements for your barangay. Check all announcements for the latest municipality-wide updates.' : 'There are no current municipality-wide announcements.'; ?></p>
			<?php endif; ?>
		</section>

		<section class="section" id="monitor">
			<header class="section__head reveal">
				<h2>Live Flood Monitoring</h2>
				<p>Readings are refreshed every few seconds while the station is online. Check the status and last-reading time before relying on a value.</p>
			</header>
			<div class="metric-grid">
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3s6 7 6 11a6 6 0 1 1-12 0c0-4 6-11 6-11z"/></svg>
					</div>
					<h3 id="cardWaterLabel"><?php echo $has_reading && ! $reading_is_current ? 'Last Water Level' : 'Water Level'; ?></h3>
					<p class="metric" id="cardWater"><?php echo $has_reading ? number_format($m['water_level_m'], 2) . ' m' : 'No reading'; ?></p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 16l6-6 4 4 6-8"/><path d="M16 6h4v4"/></svg>
					</div>
					<h3 id="cardTrendLabel"><?php echo $has_reading && ! $reading_is_current ? 'Last Water Trend' : 'Water Trend'; ?></h3>
					<p class="metric" id="cardTrend"><?php echo $has_reading ? $trend_arrow . ' ' . html_escape($m['trend_label']) : '—'; ?></p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg>
					</div>
					<h3 id="cardRateLabel"><?php echo $has_reading && ! $reading_is_current ? 'Last Rate of Rise' : 'Rate of Rise'; ?></h3>
					<p class="metric" id="cardRate"><?php echo $has_reading ? $rate_prefix . number_format($m['rate_cm_min'], 2) . ' cm/min' : '—'; ?></p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg>
					</div>
					<h3>Est. time-to-threshold</h3>
					<p class="metric" id="cardEtt"><?php echo html_escape($ett_label); ?></p>
				</article>
			</div>
		</section>

		<section class="section section--tight" aria-labelledby="portalEvacuationMapTitle">
			<div class="glass-card evacuation-map">
				<div class="evacuation-map__head">
					<h2 id="portalEvacuationMapTitle">Hazards &amp; evacuation centers</h2>
					<p>Colored hazard pins show known risk locations; evacuation pins show official centers. Choose Street map, Satellite, or Terrain from the map selector.</p>
				</div>
				<div class="hazard-legend" aria-label="Hazard map color legend">
					<?php foreach ((isset($hazard_types) && is_array($hazard_types)) ? $hazard_types : array() as $type): ?>
					<span><i style="--hazard-color: <?php echo html_escape($type['color']); ?>"></i><?php echo html_escape($type['label']); ?></span>
					<?php endforeach; ?>
					<span class="hazard-legend__evacuation"><i></i>Evacuation center</span>
				</div>
				<div class="map-style-switcher" id="portalMapStylePicker"></div>
				<div id="portalEvacuationMap" class="evacuation-map__canvas" aria-label="Map of hazards and evacuation centers"></div>
			</div>
		</section>

		<section class="section resident-dashboard__weather" aria-labelledby="weatherSectionTitle">
			<header class="section__head">
				<h2 id="weatherSectionTitle">Weather in Dulag</h2>
				<p>Current local conditions and the coming days.</p>
			</header>
			<article class="wx-now glass-card reveal" aria-labelledby="weatherTitle">
				<div class="wx-now__now">
					<div class="weather-card__icon" aria-hidden="true" data-theme="<?php echo html_escape(isset($w['theme']) ? $w['theme'] : 'cloudy'); ?>"></div>
					<div class="wx-now__tempwrap">
						<p class="wx-now__temp"><?php echo (int) $w['temp_c']; ?>°</p>
						<p class="wx-now__feels">Feels like <?php echo (int) (isset($w['feels_like_c']) ? $w['feels_like_c'] : $w['temp_c']); ?>°</p>
					</div>
					<div class="wx-now__meta">
						<h2 id="weatherTitle">Dulag weather</h2>
						<p class="wx-now__cond"><?php echo html_escape($w['condition']); ?></p>
						<p class="wx-now__source"><?php echo html_escape(isset($w['source']) ? $w['source'] : ''); ?></p>
					</div>
				</div>
				<ul class="wx-now__stats">
					<li><span>Humidity</span><strong><?php echo (int) $w['humidity']; ?>%</strong></li>
					<li><span>Rain</span><strong><?php echo number_format($w['rainfall_mm'], 1); ?> mm</strong></li>
					<li><span>Wind</span><strong><?php echo (int) $w['wind_kmh']; ?> km/h <?php echo html_escape(isset($w['wind_dir']) ? $w['wind_dir'] : ''); ?></strong></li>
					<li><span>Rain chance</span><strong><?php echo (int) (isset($w['rain_chance']) ? $w['rain_chance'] : 0); ?>%</strong></li>
					<li><span>Clouds</span><strong><?php echo (int) (isset($w['cloud_pct']) ? $w['cloud_pct'] : 0); ?>%</strong></li>
					<li><span>Pressure</span><strong><?php echo (int) (isset($w['pressure_hpa']) ? $w['pressure_hpa'] : 1013); ?> hPa</strong></li>
				</ul>
				<?php $forecast = (isset($w['forecast']) && is_array($w['forecast'])) ? $w['forecast'] : array(); ?>
				<?php if ( ! empty($forecast)): ?>
				<div class="wx-now__week">
					<div class="wx-now__days">
						<?php foreach ($forecast as $i => $day): ?>
							<article class="wx-now__day<?php echo $i === 0 ? ' is-today' : ''; ?>" title="<?php echo html_escape($day['condition']); ?>">
								<p class="wx-now__dlabel"><?php echo html_escape($day['label']); ?></p>
								<p class="wx-now__dcond"><?php echo html_escape($day['condition']); ?></p>
								<p class="wx-now__dhi"><?php echo (int) $day['temp_max']; ?>°</p>
								<p class="wx-now__dlo"><?php echo (int) $day['temp_min']; ?>°</p>
								<p class="wx-now__drain"><?php echo (int) $day['rain_chance']; ?>%</p>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</article>
		</section>

		</div>
	</main>

	<footer class="footer portal-footer">
		<div class="portal-footer__inner">
		<div class="footer__grid">
			<div>
				<p class="footer__brand">
					<img class="footer__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="36" height="36">
					<span>DAGUITAN FLOOD MONITOR</span>
				</p>
				<p>Resident portal for IoT flood monitoring at Daguitan Bridge, Dulag, Leyte.</p>
			</div>
			<nav aria-label="Footer">
				<a href="#home">Status</a>
				<a href="#monitor">Live data</a>
				<a href="<?php echo site_url('/'); ?>">Public site</a>
			</nav>
		</div>
		<p class="footer__note">Developed for community flood awareness and early warning.</p>
		</div>
	</footer>

	<script>
		window.DAGUITAN = <?php echo json_encode(array(
			'monitor' => $m,
			'thresholds' => array(
				'yellow' => (float) $m['threshold_yellow_m'],
				'red' => (float) $m['threshold_red_m'],
			),
			'statusUrl' => $status_url,
			'pollMs' => 5000,
			'actions' => $actions_map,
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/monitor-alert-sound.js?v=20261016-alert-panel3"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js?v=20261016-alert-state2"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=20261007-mobile-plus"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-profile.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-mobile.js?v=20261007-mobile-plus2"></script>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-map-layers.js?v=20261012-map-layout2"></script>
	<script>
		(function () {
			var centers = <?php echo json_encode(isset($evacuation_centers) ? $evacuation_centers : array(), JSON_UNESCAPED_SLASHES); ?>;
			var hazards = <?php echo json_encode(isset($hazards) ? $hazards : array(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			var hazardImageBase = <?php echo json_encode(site_url('hazard-image/'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			var mapElement = document.getElementById('portalEvacuationMap');
			if (!mapElement || !window.L) return;
			var map = L.map(mapElement).setView([10.9525, 125.0322], 13);
			window.DaguitanMapLayers.add(map, document.getElementById('portalMapStylePicker'));
			var bounds = [];
			centers.forEach(function (center) {
				var point = [Number(center.latitude), Number(center.longitude)];
				bounds.push(point);
				L.marker(point).addTo(map).bindPopup('<strong>' + center.name + '</strong><br>Barangay ' + center.barangay + '<br>' + center.address);
			});
			hazards.forEach(function (hazard) {
				var point = [Number(hazard.latitude), Number(hazard.longitude)];
				bounds.push(point);
				var popup = document.createElement('div');
				popup.className = 'hazard-pin-popup';
				if (typeof hazard.image === 'string' && /^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/.test(hazard.image)) {
					var photo = document.createElement('img');
					photo.className = 'hazard-pin-popup__image';
					photo.src = hazardImageBase + hazard.image;
					photo.alt = 'Photo of ' + hazard.name;
					photo.loading = 'lazy';
					popup.appendChild(photo);
				}
				var name = document.createElement('strong');
				name.textContent = hazard.name;
				var detail = document.createElement('div');
				detail.textContent = hazard.type_label + ' · Barangay ' + hazard.barangay + (hazard.description ? ' · ' + hazard.description : '');
				popup.appendChild(name);
				popup.appendChild(detail);
				L.circleMarker(point, { radius: 9, color: '#fff', weight: 2, fillColor: hazard.color, fillOpacity: 0.95 }).addTo(map).bindPopup(popup);
			});
			if (bounds.length > 1) map.fitBounds(bounds, { padding: [28, 28] });
		})();
	</script>
	<?php if ( ! empty($notify_config)): ?>
	<script>
		window.DAGUITAN_NOTIFY = <?php echo json_encode($notify_config, JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/notifications.js?v=20261016-timely1"></script>
	<?php endif; ?>
</body>
</html>

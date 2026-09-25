<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$a = $announcement;
$w = $weather;
$trend_arrow = ($m['trend'] === 'falling') ? '↓' : (($m['trend'] === 'steady') ? '→' : '↑');
$warning_key = $m['warning_level'];
$gauge_pct = isset($m['gauge_pct']) ? (int) $m['gauge_pct'] : 8;
$ett_label = isset($m['ett_label']) ? $m['ett_label'] : '—';
$rate_prefix = ($m['rate_cm_min'] > 0) ? '+' : '';
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
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
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20260924d">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing-weather.css?v=1">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20260924m">
</head>
<body class="portal-page resident-portal">
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
				<button class="icon-btn hamburger" type="button" id="menuBtn" aria-label="Open menu" aria-controls="mobileMenu" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>

	<div class="drawer" id="mobileMenu" hidden>
		<div class="drawer__panel" role="dialog" aria-modal="true" aria-labelledby="menuTitle">
			<div class="drawer__head">
				<p id="menuTitle">Resident menu</p>
				<button class="icon-btn" type="button" id="menuClose" aria-label="Close menu">
					<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
			</div>
			<nav class="drawer__nav" aria-label="Mobile">
				<a href="#home">Status</a>
				<a href="#monitor">Live data</a>
				<a href="#alerts">Alerts</a>
				<a href="#safety">Safety</a>
				<a href="<?php echo site_url('/'); ?>">Public site</a>
				<a href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			</nav>
		</div>
	</div>

	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" aria-label="Resident portal navigation">
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
				<a href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>">Evacuation Centers</a>
				<a href="<?php echo html_escape(site_url('portal/announcements')); ?>">Announcements</a>
				<a href="#alerts">Flood Alerts</a>
				<a href="#safety">Emergency Contacts</a>
			</nav>
			<a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
		</aside>
		<div class="resident-layout__content">
		<section class="hero" id="home">
			<div class="hero__copy reveal">
				<p class="eyebrow">Resident portal · Daguitan Bridge</p>
				<h1>Welcome, <?php echo html_escape($auth_name); ?></h1>
				<p class="lede">Your flood watch for Daguitan Bridge. Live water level, warning status, and MDRRMO guidance — this does not replace official evacuation orders.</p>
			</div>

			<article class="status-panel reveal" aria-labelledby="statusTitle">
				<div class="status-panel__wave" aria-hidden="true"></div>
				<p class="status-panel__kicker" id="statusTitle">Current Flood Status</p>
				<p class="status-badge status-badge--<?php echo html_escape($warning_key); ?>" id="heroStatusBadge">
					<span class="status-dot" aria-hidden="true"></span>
					<span id="heroStatusLabel"><?php echo strtoupper(html_escape($m['warning_label'])); ?></span>
					<span class="sr-only" id="heroStatusSr">Warning level: <?php echo html_escape($m['warning_label']); ?></span>
				</p>
				<div class="status-panel__grid">
					<div>
						<span class="meta">Water Level</span>
						<strong class="metric" id="heroWaterLevel"><?php echo number_format($m['water_level_m'], 2); ?> m</strong>
					</div>
					<div>
						<span class="meta">Trend</span>
						<strong id="heroTrend"><?php echo $trend_arrow; ?> <?php echo html_escape($m['trend_label']); ?></strong>
					</div>
					<div>
						<span class="meta">Last Updated</span>
						<time id="heroUpdated" datetime="<?php echo html_escape($m['last_updated_iso']); ?>"><?php echo html_escape($m['last_updated']); ?></time>
					</div>
				</div>
				<div class="gauge gauge--<?php echo html_escape($warning_key); ?>" id="heroGauge" aria-hidden="true">
					<div class="gauge__fill" id="heroGaugeFill" style="--level: <?php echo (int) $gauge_pct; ?>%"></div>
				</div>
			</article>
		</section>

		<section class="section section--tight" id="alerts">
			<article class="glass-card warn-board reveal" aria-labelledby="warningBoardTitle">
				<div class="warn-board__head">
					<p class="card-kicker" id="warningBoardTitle">Flood warning level</p>
					<p class="card-kicker">Status</p>
				</div>
				<div class="warn-board__list" role="list" aria-label="Flood warning levels">
					<div class="warning-track__item<?php echo $warning_key === 'green' ? ' is-active' : ''; ?>" role="listitem" data-level="green">
						<div class="warn-board__spec">
							<span class="warning-chip warning-chip--green"><span aria-hidden="true">●</span> GREEN</span>
							<strong>Safe</strong>
						</div>
						<p class="warn-board__status">Below advisory threshold.</p>
					</div>
					<div class="warning-track__item<?php echo $warning_key === 'yellow' ? ' is-active' : ''; ?>" role="listitem" data-level="yellow">
						<div class="warn-board__spec">
							<span class="warning-chip warning-chip--yellow"><span aria-hidden="true">●</span> YELLOW</span>
							<strong>Monitor</strong>
						</div>
						<p class="warn-board__status">Approaching caution. Stay alert.</p>
					</div>
					<div class="warning-track__item warning-track__item--critical<?php echo $warning_key === 'red' ? ' is-active' : ''; ?>" role="listitem" data-level="red">
						<div class="warn-board__spec">
							<span class="warning-chip warning-chip--red"><span aria-hidden="true">●</span> RED</span>
							<strong>Critical</strong>
						</div>
						<p class="warn-board__status">Critical threshold reached.</p>
					</div>
				</div>
			</article>
		</section>

		<section class="section" id="monitor">
			<header class="section__head reveal">
				<h2>Live Flood Monitoring</h2>
				<p>Readings from the ultrasonic station at Daguitan Bridge, refreshed every few seconds.</p>
			</header>
			<div class="metric-grid">
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3s6 7 6 11a6 6 0 1 1-12 0c0-4 6-11 6-11z"/></svg>
					</div>
					<h3>Water Level</h3>
					<p class="metric" id="cardWater"><?php echo number_format($m['water_level_m'], 2); ?> m</p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 16l6-6 4 4 6-8"/><path d="M16 6h4v4"/></svg>
					</div>
					<h3>Water Trend</h3>
					<p class="metric" id="cardTrend"><?php echo $trend_arrow; ?> <?php echo html_escape($m['trend_label']); ?></p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg>
					</div>
					<h3>Rate of Rise</h3>
					<p class="metric" id="cardRate"><?php echo $rate_prefix . number_format($m['rate_cm_min'], 2); ?> cm/min</p>
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

		<section class="section">
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

		<section class="section" id="safety">
			<header class="section__head reveal">
				<h2>Emergency desk</h2>
				<p>Tap a number to call. Keep these contacts saved on your phone before heavy rain.</p>
			</header>
			<div class="hotline-grid">
				<a class="glass-card portal-hotline reveal" href="tel:09171234567">
					<h3>MDRRMO Dulag</h3>
					<p class="metric portal-hotline__num">0917 123 4567</p>
					<p class="card-copy">Disaster risk reduction and response</p>
				</a>
				<a class="glass-card portal-hotline reveal" href="tel:09985991111">
					<h3>PNP Dulag</h3>
					<p class="metric portal-hotline__num">0998 599 1111</p>
					<p class="card-copy">Police assistance</p>
				</a>
				<a class="glass-card portal-hotline reveal" href="tel:0533250000">
					<h3>BFP Dulag</h3>
					<p class="metric portal-hotline__num">(053) 325-0000</p>
					<p class="card-copy">Fire and rescue</p>
				</a>
				<a class="glass-card portal-hotline reveal" href="tel:911">
					<h3>Nationwide</h3>
					<p class="metric portal-hotline__num">911</p>
					<p class="card-copy">Emergency hotline</p>
				</a>
			</div>
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
				<a href="#alerts">Alerts</a>
				<a href="<?php echo site_url('/'); ?>">Public site</a>
			</nav>
		</div>
		<p class="footer__note">Developed for community flood awareness and early warning.</p>
		</div>
	</footer>

	<nav class="bottom-nav" aria-label="Portal">
		<a href="#home" class="is-active"><span aria-hidden="true">⌂</span> Status</a>
		<a href="#monitor"><span aria-hidden="true">🌊</span> Live</a>
		<a href="#alerts"><span aria-hidden="true">🔔</span> Alerts</a>
	</nav>

	<script>
		window.DAGUITAN = <?php echo json_encode(array(
			'statusUrl' => $status_url,
			'pollMs' => 5000,
			'actions' => $actions_map,
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js?v=20260924a"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-profile.js?v=1"></script>
	<?php if ( ! empty($notify_config)): ?>
	<script>
		window.DAGUITAN_NOTIFY = <?php echo json_encode($notify_config, JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/notifications.js?v=2"></script>
	<?php endif; ?>
</body>
</html>

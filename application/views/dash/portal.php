<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$a = $announcement;
$w = $weather;
$trend_arrow = ($m['trend'] === 'falling') ? '↓' : (($m['trend'] === 'steady') ? '→' : '↑');
$warning_key = $m['warning_level'];
$gauge_pct = isset($m['gauge_pct']) ? (int) $m['gauge_pct'] : 8;
$sensor_class = ($m['sensor_status'] === 'online') ? 'metric--online' : 'metric--offline';
$sensor_label = isset($m['sensor_label']) ? $m['sensor_label'] : ucfirst($m['sensor_status']);
$rate_prefix = ($m['rate_cm_min'] > 0) ? '+' : '';
$guidance_titles = array('Do this first', 'Then', 'Keep in mind');
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
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css">
</head>
<body class="portal-page">
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
		<svg class="ambient__waves" viewBox="0 0 1440 320" preserveAspectRatio="none">
			<path class="wave wave--a" d="M0,224L48,208C96,192,192,160,288,154.7C384,149,480,171,576,186.7C672,203,768,213,864,197.3C960,181,1056,139,1152,133.3C1248,128,1344,160,1392,176L1440,192L1440,320L0,320Z"></path>
			<path class="wave wave--b" d="M0,256L60,245.3C120,235,240,213,360,208C480,203,600,213,720,229.3C840,245,960,267,1080,256C1200,245,1320,203,1380,181.3L1440,160L1440,320L0,320Z"></path>
		</svg>
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

			<nav class="nav-desktop" aria-label="Portal">
				<a href="#home">Status</a>
				<a href="#monitor">Live data</a>
				<a href="#guidance">Guidance</a>
				<a href="#alerts">Alerts</a>
				<a href="#safety">Safety</a>
			</nav>

			<div class="topbar__actions">
				<?php $this->load->view('partials/notifications_bell'); ?>
				<span class="btn btn--ghost btn--compact portal-user" title="<?php echo html_escape($auth_name . ( ! empty($auth_phone) ? ' · ' . $auth_phone : '')); ?>"><?php echo html_escape($auth_name); ?></span>
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
				<a href="#guidance">Guidance</a>
				<a href="#alerts">Alerts</a>
				<a href="#safety">Safety</a>
				<a href="<?php echo site_url('/'); ?>">Public site</a>
				<a href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			</nav>
			<a class="btn btn--primary btn--block" href="#guidance">What to do now</a>
		</div>
	</div>

	<main id="main">
		<section class="hero" id="home">
			<div class="hero__copy reveal">
				<p class="eyebrow">Resident portal · Daguitan Bridge</p>
				<h1>Welcome, <?php echo html_escape($auth_name); ?></h1>
				<p class="lede">Your flood watch for Daguitan Bridge. Live water level, warning status, and MDRRMO guidance — this does not replace official evacuation orders.</p>
				<div class="hero__actions">
					<a class="btn btn--primary" href="#guidance">What to do now</a>
					<a class="btn btn--ghost" href="#alerts">Emergency desk</a>
				</div>
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
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/><circle cx="12" cy="12" r="4"/></svg>
					</div>
					<h3>Sensor Status</h3>
					<p class="metric <?php echo $sensor_class; ?>" id="cardSensor"><span class="live-dot" aria-hidden="true"></span> <span id="cardSensorLabel"><?php echo html_escape($sensor_label); ?></span></p>
				</article>
			</div>
		</section>

		<section class="section" id="guidance">
			<header class="section__head reveal">
				<p class="eyebrow" id="portalGuidanceKicker"><?php echo strtoupper(html_escape($m['warning_label'])); ?> · What to do now</p>
				<h2>Your next steps</h2>
				<p>These actions change with the live warning. Follow barangay and MDRRMO instructions if they differ.</p>
			</header>
			<ol class="process" id="portalActions">
				<?php foreach ($actions as $i => $item): ?>
					<li class="process__step reveal">
						<span class="process__num"><?php echo str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); ?></span>
						<h3><?php echo html_escape(isset($guidance_titles[$i]) ? $guidance_titles[$i] : 'Next'); ?></h3>
						<p><?php echo html_escape($item); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<section class="section" id="alerts">
			<header class="section__head reveal">
				<h2>Flood Warning Status</h2>
				<p>Classification follows the station thresholds used on the public Daguitan monitor.</p>
			</header>
			<div class="warning-track reveal" role="list" aria-label="Flood warning levels">
				<div class="warning-track__item<?php echo $warning_key === 'green' ? ' is-active' : ''; ?>" role="listitem" data-level="green">
					<span class="warning-chip warning-chip--green"><span aria-hidden="true">●</span> GREEN</span>
					<strong>Safe</strong>
					<p>Water remains below the advisory threshold.</p>
				</div>
				<div class="warning-track__item<?php echo $warning_key === 'yellow' ? ' is-active' : ''; ?>" role="listitem" data-level="yellow">
					<span class="warning-chip warning-chip--yellow"><span aria-hidden="true">●</span> YELLOW</span>
					<strong>Monitor</strong>
					<p>Water is approaching caution levels. Stay alert.</p>
				</div>
				<div class="warning-track__item warning-track__item--critical<?php echo $warning_key === 'red' ? ' is-active' : ''; ?>" role="listitem" data-level="red">
					<span class="warning-chip warning-chip--red"><span aria-hidden="true">●</span> RED</span>
					<strong>Critical</strong>
					<p>Water has reached the critical threshold.</p>
				</div>
			</div>
		</section>

		<section class="section section--split">
			<article class="weather-card glass-card reveal" aria-labelledby="weatherTitle">
				<p class="card-kicker">Supplementary Weather Information</p>
				<div class="weather-card__row">
					<div class="weather-card__icon" aria-hidden="true">
						<svg viewBox="0 0 64 64" width="72" height="72">
							<circle class="sun-soft" cx="22" cy="22" r="10" fill="#ffb4b8"/>
							<path d="M18 40h28a10 10 0 0 0 1-20 14 14 0 0 0-26 4 9 9 0 0 0-3 16z" fill="rgba(155,18,36,.12)" stroke="#9b1224" stroke-width="1.6"/>
						</svg>
					</div>
					<div>
						<h2 id="weatherTitle">Current Weather</h2>
						<p class="metric weather-card__temp"><?php echo (int) $w['temp_c']; ?>°C</p>
						<p class="weather-card__cond"><?php echo html_escape($w['condition']); ?></p>
					</div>
				</div>
				<dl class="weather-stats">
					<div><dt>Humidity</dt><dd><?php echo (int) $w['humidity']; ?>%</dd></div>
					<div><dt>Rainfall</dt><dd><?php echo number_format($w['rainfall_mm'], 1); ?> mm</dd></div>
					<div><dt>Wind</dt><dd><?php echo (int) $w['wind_kmh']; ?> km/h</dd></div>
				</dl>
			</article>

			<article class="announce-card glass-card reveal" aria-labelledby="announceTitle">
				<div class="announce-card__head">
					<h2 id="announceTitle">Emergency Announcements</h2>
					<span class="pill" id="announcePill"><?php echo $a['active'] ? 'Active' : 'Quiet'; ?></span>
				</div>
				<div id="announceBody">
				<?php if ($a['active']): ?>
					<p class="status-badge status-badge--<?php echo html_escape($a['level']); ?>"><?php echo html_escape($a['title']); ?></p>
					<p><?php echo html_escape($a['body']); ?></p>
				<?php else: ?>
					<p class="announce-card__empty"><?php echo html_escape($a['title']); ?></p>
					<p><?php echo html_escape($a['body']); ?></p>
				<?php endif; ?>
				</div>
				<p class="issuer">Issued by <?php echo html_escape($a['issuer']); ?></p>
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

		<section class="emergency-cta" id="about">
			<div class="emergency-cta__waves" aria-hidden="true"></div>
			<div class="emergency-cta__inner reveal">
				<h2>When the warning rises, be ready to act.</h2>
				<p>Stay with this page during rainfall. Move to higher ground only when MDRRMO or barangay officials instruct you — or immediately at red alert.</p>
				<a class="btn btn--light" href="<?php echo site_url('/'); ?>">Open public site</a>
			</div>
		</section>
	</main>

	<footer class="footer">
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
				<a href="#guidance">Guidance</a>
				<a href="#alerts">Alerts</a>
				<a href="<?php echo site_url('/'); ?>">Public site</a>
			</nav>
		</div>
		<p class="footer__note">Developed for community flood awareness and early warning.</p>
	</footer>

	<nav class="bottom-nav" aria-label="Portal">
		<a href="#home" class="is-active"><span aria-hidden="true">⌂</span> Status</a>
		<a href="#monitor"><span aria-hidden="true">🌊</span> Live</a>
		<a href="#guidance"><span aria-hidden="true">✓</span> Guide</a>
		<a href="#alerts"><span aria-hidden="true">🔔</span> Alerts</a>
	</nav>

	<script>
		window.DAGUITAN = <?php echo json_encode(array(
			'statusUrl' => $status_url,
			'pollMs' => 5000,
			'actions' => $actions_map,
			'guidanceTitles' => $guidance_titles,
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js"></script>
	<?php if ( ! empty($notify_config)): ?>
	<script>
		window.DAGUITAN_NOTIFY = <?php echo json_encode($notify_config, JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/notifications.js?v=2"></script>
	<?php endif; ?>
</body>
</html>

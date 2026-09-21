<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$w = $weather;
$a = $announcement;
$trend_arrow = ($m['trend'] === 'falling') ? '↓' : (($m['trend'] === 'steady') ? '→' : '↑');
$warning_key = $m['warning_level'];
$gauge_pct = isset($m['gauge_pct']) ? (int) $m['gauge_pct'] : 8;
$sensor_class = ($m['sensor_status'] === 'online') ? 'metric--online' : 'metric--offline';
$sensor_label = isset($m['sensor_label']) ? $m['sensor_label'] : ucfirst($m['sensor_status']);
$rate_prefix = ($m['rate_cm_min'] > 0) ? '+' : '';
$wx_theme = isset($w['theme']) ? (string) $w['theme'] : 'cloudy';
if ( ! preg_match('/^[a-z]+(-[a-z]+)?$/', $wx_theme))
{
	$wx_theme = 'cloudy';
}
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
	<meta name="description" content="Real-time flood monitoring and early warning information for Daguitan Bridge, Dulag, Leyte.">
	<title><?php echo html_escape($page_title); ?> · Dulag, Leyte</title>
	<link rel="manifest" href="<?php echo html_escape($base_url); ?>manifest.webmanifest">
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="apple-touch-icon" href="<?php echo html_escape($asset_url); ?>icons/pwa-icon-192.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing-weather.css?v=1">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css">
</head>
<body>
	<a class="skip-link" href="#main">Skip to content</a>

	<div class="gov-bar">
		<div class="gov-bar__inner">
			<span>Republic of the Philippines · Municipality of Dulag, Leyte</span>
			<span>Municipal Disaster Risk Reduction and Management Office</span>
		</div>
	</div>

	<div class="wx-scene" id="wxScene" data-theme="<?php echo html_escape($wx_theme); ?>" aria-hidden="true">
		<div class="wx-sky"></div>
		<div class="wx-sun">
			<div class="wx-sun__glow"></div>
			<div class="wx-sun__disc"></div>
		</div>
		<div class="wx-clouds">
			<div class="wx-cloud wx-cloud--1"></div>
			<div class="wx-cloud wx-cloud--2"></div>
			<div class="wx-cloud wx-cloud--3"></div>
			<div class="wx-cloud wx-cloud--4"></div>
			<div class="wx-cloud wx-cloud--5"></div>
		</div>
		<div class="wx-rain wx-rain--back" id="wxRainBack"></div>
		<div class="wx-rain wx-rain--front" id="wxRainFront"></div>
		<div class="wx-lightning" id="wxLightning"></div>
		<div class="wx-fog"></div>
		<div class="wx-ground"></div>
		<svg class="wx-river" viewBox="0 0 1440 200" preserveAspectRatio="none" aria-hidden="true">
			<path class="wx-river__wave wx-river__wave--1" d="M0,120 C240,90 480,150 720,110 S1200,80 1440,120 L1440,200 L0,200 Z"/>
			<path class="wx-river__wave wx-river__wave--2" d="M0,140 C360,160 720,100 1080,130 S1320,150 1440,135 L1440,200 L0,200 Z"/>
		</svg>
	</div>

	<header class="topbar" id="topbar">
		<div class="topbar__inner">
			<a class="brand" href="#home" aria-label="Daguitan Flood Monitor home">
				<span class="brand__mark" aria-hidden="true">
					<img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40">
				</span>
				<span class="brand__text">
					<span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span>
					<span class="brand__name brand__name--mobile">Daguitan Monitor</span>
				</span>
			</a>

			<nav class="nav-desktop" aria-label="Primary">
				<a href="#home">Home</a>
				<a href="#monitor">Monitor</a>
				<a href="#alerts">Alerts</a>
				<a href="#safety">Safety</a>
				<a href="#about">About</a>
			</nav>

			<div class="topbar__actions">
				<?php if ( ! empty($auth_role)): ?>
					<a class="btn btn--ghost btn--compact" href="<?php echo html_escape($auth_role === 'admin' ? site_url('admin') : site_url('portal')); ?>"><?php echo html_escape($auth_name); ?></a>
					<a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
				<?php else: ?>
				<div class="access-btns" role="group" aria-label="Resident access">
					<a class="btn btn--ghost btn--compact" href="<?php echo html_escape($login_user); ?>">Sign in</a>
					<a class="btn btn--primary btn--compact" href="<?php echo html_escape($signup_url); ?>">Sign up</a>
				</div>
				<?php endif; ?>
				<button class="icon-btn" type="button" id="notifyBtn" aria-label="Notifications, no unread alerts">
					<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/>
						<path d="M10 19a2 2 0 0 0 4 0"/>
					</svg>
				</button>
				<button class="icon-btn hamburger" type="button" id="menuBtn" aria-label="Open menu" aria-controls="mobileMenu" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>

	<div class="drawer" id="mobileMenu" hidden>
		<div class="drawer__panel" role="dialog" aria-modal="true" aria-labelledby="menuTitle">
			<div class="drawer__head">
				<p id="menuTitle">Menu</p>
				<button class="icon-btn" type="button" id="menuClose" aria-label="Close menu">
					<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
			</div>
			<nav class="drawer__nav" aria-label="Mobile">
				<a href="#home">Home</a>
				<a href="#monitor">Monitor</a>
				<a href="#alerts">Alerts</a>
				<a href="#safety">Safety</a>
				<a href="#about">About</a>
				<?php if ( ! empty($auth_role)): ?>
					<a href="<?php echo html_escape($auth_role === 'admin' ? site_url('admin') : site_url('portal')); ?>">Dashboard</a>
					<a href="<?php echo html_escape($logout_url); ?>">Sign out</a>
				<?php else: ?>
					<a href="<?php echo html_escape($login_user); ?>">Sign in</a>
					<a href="<?php echo html_escape($signup_url); ?>">Sign up</a>
				<?php endif; ?>
			</nav>
			<a class="btn btn--primary btn--block" href="#monitor">View Live Status</a>
		</div>
	</div>

	<main id="main">
		<section class="hero" id="home">
			<div class="hero__copy reveal">
				<p class="eyebrow">Daguitan Bridge · Dulag, Leyte</p>
				<h1>Stay Informed. Stay Prepared. Stay Safe.</h1>
				<p class="lede">Real-time flood monitoring and early warning information for Daguitan Bridge, Dulag, Leyte.</p>
				<div class="hero__actions">
					<a class="btn btn--primary" href="#monitor">View Live Status</a>
					<a class="btn btn--ghost" href="#how">How the system works</a>
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
						<strong class="metric" id="heroWaterLevel" data-count="<?php echo html_escape($m['water_level_m']); ?>" data-suffix=" m">0.00 m</strong>
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

		<div class="trust-strip reveal">
			<article><strong>IoT station</strong><span>Ultrasonic sensor at Daguitan Bridge</span></article>
			<article><strong>5-second refresh</strong><span>Public dashboard polls live packets</span></article>
			<article><strong>3-level advisory</strong><span>Green, yellow, and red thresholds</span></article>
			<article><strong>MDRRMO-led</strong><span>Community early warning for Dulag</span></article>
		</div>

		<section class="section" id="monitor">
			<header class="section__head reveal">
				<h2>Live Flood Monitoring</h2>
				<p>Real-time information from the Daguitan Bridge monitoring station.</p>
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

		<section class="section" id="alerts">
			<header class="section__head reveal">
				<h2>Flood Warning Status</h2>
				<p>Flood warning classification is based on predefined water-level thresholds established for the monitoring station.</p>
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

		<section class="section" id="safety">
			<header class="section__head reveal">
				<h2>Community Safety Reminders</h2>
				<p>Use live readings together with these standing MDRRMO precautions for households near Daguitan Bridge.</p>
			</header>
			<div class="metric-grid safety-grid">
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3l8 4v6c0 5-3.4 8.2-8 9-4.6-.8-8-4-8-9V7l8-4z"/></svg>
					</div>
					<h3>Before flooding</h3>
					<p class="card-copy">Keep a go-bag, store drinking water, and agree on a meeting point with your household.</p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 4.3L2.8 18a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0z"/></svg>
					</div>
					<h3>Yellow advisory</h3>
					<p class="card-copy">Stay alert, avoid the riverbank, and prepare to move if water continues to rise.</p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4M4.9 19.1l2.8-2.8M16.3 7.7l2.8-2.8"/></svg>
					</div>
					<h3>Red / critical</h3>
					<p class="card-copy">Follow MDRRMO instructions immediately. Do not cross flowing water or wait on the bridge.</p>
				</article>
				<article class="glass-card reveal">
					<div class="glass-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6.4 6.4l1.2-1.2a2 2 0 0 1 2.1-.4c.8.2 1.7.4 2.6.6A2 2 0 0 1 22 16.9z"/></svg>
					</div>
					<h3>Hotlines</h3>
					<p class="card-copy">Call MDRRMO Dulag or barangay responders if someone is trapped or if the sensor page cannot load.</p>
				</article>
			</div>
		</section>

		<section class="section section--split">
			<article class="weather-card glass-card reveal" aria-labelledby="weatherTitle">
				<p class="card-kicker">Supplementary Weather Information</p>
				<div class="weather-card__row">
					<div class="weather-card__icon" id="wxCardIcon" aria-hidden="true" data-theme="<?php echo html_escape($wx_theme); ?>"></div>
					<div>
						<h2 id="weatherTitle">Current Weather</h2>
						<p class="metric weather-card__temp" id="wxCardTemp"><?php echo (int) $w['temp_c']; ?>°C</p>
						<p class="weather-card__cond" id="wxCardCond"><?php echo html_escape($w['condition']); ?></p>
						<p class="weather-card__source" id="wxCardSource"><?php echo html_escape($w['source']); ?></p>
					</div>
				</div>
				<dl class="weather-stats">
					<div><dt>Humidity</dt><dd id="wxCardHumidity"><?php echo (int) $w['humidity']; ?>%</dd></div>
					<div><dt>Rainfall</dt><dd id="wxCardRain"><?php echo number_format($w['rainfall_mm'], 1); ?> mm</dd></div>
					<div><dt>Wind</dt><dd id="wxCardWind"><?php echo (int) $w['wind_kmh']; ?> km/h</dd></div>
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
				<a class="btn btn--ghost" href="#alerts">View All Announcements</a>
			</article>
		</section>

		<section class="section" id="how">
			<header class="section__head reveal">
				<h2>How It Works</h2>
				<p>From river sensor to community warning — a four-step monitoring path.</p>
			</header>
			<ol class="process">
				<li class="process__step reveal">
					<span class="process__num">01</span>
					<h3>Sense</h3>
					<p>Water-level sensor measures the river.</p>
				</li>
				<li class="process__step reveal">
					<span class="process__num">02</span>
					<h3>Process</h3>
					<p>ESP32 processes and transmits monitoring data.</p>
				</li>
				<li class="process__step reveal">
					<span class="process__num">03</span>
					<h3>Analyze</h3>
					<p>The system determines water-level trend, rate of rise, and warning status.</p>
				</li>
				<li class="process__step reveal">
					<span class="process__num">04</span>
					<h3>Warn</h3>
					<p>Residents receive flood information and emergency notifications.</p>
				</li>
			</ol>
		</section>

		<section class="pwa-cta reveal" id="install">
			<div class="pwa-cta__copy">
				<h2>Take Flood Monitoring With You</h2>
				<p>Install Daguitan Flood Monitor on your mobile device for quick access to real-time flood information and emergency notifications.</p>
				<button class="btn btn--primary" type="button" id="installBtn">Install App</button>
				<p class="hint" id="installHint" hidden>Use your browser menu and choose Add to Home Screen.</p>
			</div>
			<div class="phone" aria-hidden="true">
				<div class="phone__bezel">
					<div class="phone__screen">
						<div class="phone__bar">🌊 Daguitan Monitor</div>
						<div class="phone__status">
							<small>CURRENT FLOOD STATUS</small>
							<strong id="phoneStatus"><?php echo strtoupper(html_escape($m['warning_label'])); ?></strong>
							<span id="phoneMeta"><?php echo number_format($m['water_level_m'], 2); ?> m · <?php echo html_escape($m['trend_label']); ?></span>
						</div>
						<div class="phone__tiles">
							<span id="phoneWater"><?php echo number_format($m['water_level_m'], 2); ?> m</span><span id="phoneTrend"><?php echo $trend_arrow; ?> <?php echo html_escape($m['trend_label']); ?></span>
							<span id="phoneRate"><?php echo $rate_prefix . number_format($m['rate_cm_min'], 2); ?></span><span id="phoneSensor"><?php echo html_escape($sensor_label); ?></span>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="emergency-cta" id="about">
			<div class="emergency-cta__waves" aria-hidden="true"></div>
			<div class="emergency-cta__inner reveal">
				<h2>When the warning rises, be ready to act.</h2>
				<p>Stay updated with real-time flood information from Daguitan Bridge.</p>
				<a class="btn btn--light" href="#monitor">View Current Status</a>
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
				<p>IoT-Based Flood Real-Time Monitoring and Early Warning System</p>
				<p>Daguitan Bridge, Dulag, Leyte</p>
			</div>
			<nav aria-label="Footer">
				<a href="#home">Home</a>
				<a href="#monitor">Live Monitoring</a>
				<a href="#alerts">Alerts</a>
				<a href="#safety">Safety</a>
				<a href="#about">About</a>
			</nav>
		</div>
		<p class="footer__note">Developed for community flood awareness and early warning.</p>
	</footer>

	<nav class="bottom-nav" aria-label="App">
		<a href="#home" class="is-active"><span aria-hidden="true">⌂</span> Home</a>
		<a href="#monitor"><span aria-hidden="true">🌊</span> Monitor</a>
		<a href="#alerts"><span aria-hidden="true">🔔</span> Alerts</a>
		<a href="#about"><span aria-hidden="true">ℹ</span> About</a>
	</nav>

	<script>
		window.DAGUITAN = <?php echo json_encode(array(
			'monitor' => $m,
			'weather' => $w,
			'announcement' => $a,
			'statusUrl' => $status_url,
			'pollMs' => 5000,
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing-weather.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js?v=20260320wx"></script>
</body>
</html>

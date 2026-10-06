<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="description" content="Learn how the Daguitan Flood Monitor supports river-level awareness and early-warning information for Daguitan Bridge, Dulag, Leyte.">
	<title><?php echo html_escape($page_title); ?> · Dulag, Leyte</title>
	<link rel="manifest" href="<?php echo html_escape($base_url); ?>manifest.webmanifest">
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="apple-touch-icon" href="<?php echo html_escape($asset_url); ?>icons/pwa-icon-192.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20261006f">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20260925z">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="landing-page about-page">
	<a class="skip-link" href="#main">Skip to content</a>

	<div class="gov-bar">
		<div class="gov-bar__inner">
			<span>Republic of the Philippines · Municipality of Dulag, Leyte</span>
			<span>Municipal Disaster Risk Reduction and Management Office</span>
		</div>
	</div>

	<header class="topbar" id="topbar">
		<div class="topbar__inner">
			<a class="brand" href="<?php echo html_escape($home_url); ?>" aria-label="Daguitan Flood Monitor home">
				<span class="brand__mark" aria-hidden="true"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span>
				<span class="brand__text">
					<span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span>
					<span class="brand__name brand__name--mobile">Daguitan Monitor</span>
				</span>
			</a>
			<nav class="nav-desktop" aria-label="Primary">
				<?php $this->load->view('partials/public_nav'); ?>
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
				<?php $this->load->view('partials/public_nav'); ?>
				<?php if ( ! empty($auth_role)): ?>
					<a href="<?php echo html_escape($auth_role === 'admin' ? site_url('admin') : site_url('portal')); ?>">Dashboard</a>
					<a href="<?php echo html_escape($logout_url); ?>">Sign out</a>
				<?php else: ?>
					<a href="<?php echo html_escape($login_user); ?>">Sign in</a>
					<a href="<?php echo html_escape($signup_url); ?>">Sign up</a>
				<?php endif; ?>
			</nav>
			<a class="btn btn--primary btn--block" href="<?php echo html_escape($home_url); ?>#monitor">View live status</a>
		</div>
	</div>

	<main id="main" class="about-main">
		<section class="about-hero" aria-labelledby="aboutTitle">
			<img class="about-hero__image" src="<?php echo html_escape($asset_url); ?>img/DULAG.jpg" alt="Dulag, Leyte, the community served by the Daguitan Flood Monitor" fetchpriority="high">
			<div class="about-hero__copy">
				<p class="eyebrow">MDRRMO Dulag · System overview</p>
				<h1 id="aboutTitle">Daguitan Flood Monitor</h1>
				<p class="lede">A localized IoT-based river water-level monitoring and early-warning prototype for Daguitan Bridge, Dulag, Leyte.</p>
				<div class="about-hero__actions">
					<a class="btn btn--primary" href="<?php echo html_escape($home_url); ?>#monitor">View live status</a>
					<a class="btn btn--ghost" href="<?php echo html_escape($announcements_url); ?>">Flood advisories</a>
				</div>
			</div>
		</section>

		<section class="about-intro" aria-labelledby="purposeTitle">
			<div>
				<p class="eyebrow">Purpose</p>
				<h2 id="purposeTitle">A clearer view of river conditions</h2>
			</div>
			<div class="about-intro__copy">
				<p>The system is designed to help MDRRMO personnel and residents follow changing water levels near Daguitan Bridge and find timely official guidance. A field sensor sends measurements to a monitoring platform, where current status and supporting context can be viewed.</p>
				<p>It is a localized working prototype for community flood awareness, not a replacement for MDRRMO direction or on-the-ground emergency response.</p>
			</div>
		</section>

		<div class="about-facts" aria-label="System summary">
			<div class="about-fact"><span>Monitoring site</span><strong>Daguitan Bridge</strong></div>
			<div class="about-fact"><span>Primary measurement</span><strong>River water level</strong></div>
			<div class="about-fact"><span>Lead stakeholder</span><strong>MDRRMO Dulag</strong></div>
			<div class="about-fact"><span>Project stage</span><strong>Working prototype</strong></div>
		</div>

		<section class="about-organization" aria-labelledby="organizationTitle">
			<header class="about-organization__heading">
				<p class="eyebrow">MDRRMO Dulag</p>
				<h2 id="organizationTitle">Office organization</h2>
				<p class="about-section-lede">Leadership, divisions, and functional teams supporting disaster risk reduction and emergency response in Dulag.</p>
			</header>

			<div class="about-org-chart" aria-label="MDRRMO Dulag organizational chart">
				<div class="about-org-chart__leadership">
					<article class="about-org-person about-org-person--mayor">
						<img class="about-org-person__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/mayor.png" alt="">
						<p class="about-org-person__label">Local Chief Executive</p>
						<h3>Hon. Jade A. Agullo</h3>
						<p>Municipal Mayor</p>
					</article>
					<span class="about-org-chart__connector" aria-hidden="true"></span>
					<article class="about-org-person about-org-person--director">
						<img class="about-org-person__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/office-head.png" alt="">
						<p class="about-org-person__label">Office Head</p>
						<h3>Atty. Leah C. Caminong</h3>
						<p>Municipal Government Department Head I</p>
					</article>
					<span class="about-org-chart__connector about-org-chart__connector--branches" aria-hidden="true"></span>
				</div>

				<div class="about-org-chart__branches">
					<section class="about-org-branch" aria-labelledby="adminTrainingTitle">
						<header class="about-org-branch__lead">
							<img class="about-org-branch__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/admin-training-lead.png" alt="">
							<p class="about-org-person__label">Division Lead</p>
							<h3 id="adminTrainingTitle">Jeffrey M. Pabro</h3>
							<p>Administrative &amp; Training</p>
						</header>
						<ul class="about-org-branch__team">
							<li>
								<article class="about-org-member">
									<div class="about-org-member__identity">
										<img class="about-org-member__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/admin-section.png" alt="">
										<div><h4>Krissa Joy L. Abanes</h4>
										<p class="about-org-member__role">Administrative Section</p></div>
									</div>
									<p>Provides administrative support and maintains office records and property.</p>
								</article>
							</li>
							<li>
								<article class="about-org-member">
									<div class="about-org-member__identity">
										<img class="about-org-member__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/information-training.png" alt="">
										<div><h4>Geoffrey L. Baldo</h4>
										<p class="about-org-member__role">Information, Education &amp; Training Section</p></div>
									</div>
									<p>Conducts training and information and education campaigns.</p>
								</article>
							</li>
						</ul>
					</section>

					<section class="about-org-branch" aria-labelledby="researchPlanningTitle">
						<header class="about-org-branch__lead">
							<img class="about-org-branch__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/research-planning-lead.png" alt="">
							<p class="about-org-person__label">Division Lead</p>
							<h3 id="researchPlanningTitle">Rolando M. Lagunzad Jr.</h3>
							<p>Research &amp; Planning</p>
						</header>
						<ul class="about-org-branch__team">
							<li>
								<article class="about-org-member">
									<div class="about-org-member__identity">
										<img class="about-org-member__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/hydromet.png" alt="">
										<div><h4>Wenward Alicando</h4>
										<p class="about-org-member__role">Hydromet Section</p></div>
									</div>
									<p>Installs and maintains hydrometeorological stations and warning systems.</p>
								</article>
							</li>
							<li>
								<article class="about-org-member">
									<div class="about-org-member__identity">
										<img class="about-org-member__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/planning.png" alt="">
										<div><h4>Jan Gabriel Abrenio</h4>
										<p class="about-org-member__role">Planning Section</p></div>
									</div>
									<p>Prepares and supports implementation of local disaster risk reduction plans.</p>
								</article>
							</li>
						</ul>
					</section>

					<section class="about-org-branch" aria-labelledby="operationsWarningTitle">
						<header class="about-org-branch__lead">
							<img class="about-org-branch__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/operations-warning-lead.png" alt="">
							<p class="about-org-person__label">Division Lead</p>
							<h3 id="operationsWarningTitle">Jason I. Tupaz</h3>
							<p>Operations &amp; Warning</p>
						</header>
						<ul class="about-org-branch__team">
							<li>
								<article class="about-org-member">
									<div class="about-org-member__identity">
										<img class="about-org-member__photo" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/flood-warning.png" alt="">
										<div><h4>Judith Petilla</h4>
										<p class="about-org-member__role">Flood &amp; Early Warning Section</p></div>
									</div>
									<p>Communicates warnings to barangays.</p>
								</article>
							</li>
							<li>
								<article class="about-org-member">
									<div class="about-org-member__identity">
										<img class="about-org-member__photo about-org-member__photo--emblem" src="<?php echo html_escape($asset_url); ?>img/mdrrmo-org/rescue-unit.png" alt="">
										<div><h4>Dulag Emergency Response &amp; Rescue Unit</h4>
									<p class="about-org-member__role">Alpha, Bravo &amp; Charlie Teams</p>
										</div>
									</div>
									<p>Conducts search, rescue, and retrieval operations.</p>
								</article>
							</li>
						</ul>
					</section>
				</div>
			</div>
		</section>

		<section class="about-flow" aria-labelledby="flowTitle">
			<p class="eyebrow">Monitoring process</p>
			<h2 id="flowTitle">From river reading to public notice</h2>
			<p class="about-section-lede">Each stage helps turn a field measurement into information people can use.</p>
			<ol class="about-steps">
				<li class="about-step"><span class="about-step__number">01</span><h3>Measure</h3><p>A solar-powered ESP32 station with rechargeable battery backup uses a waterproof ultrasonic sensor to measure distance to the river surface.</p></li>
				<li class="about-step"><span class="about-step__number">02</span><h3>Validate</h3><p>Readings are checked and converted into a river water-level measurement before display.</p></li>
				<li class="about-step"><span class="about-step__number">03</span><h3>Classify</h3><p>Validated water level is compared with configured thresholds to determine the Green, Yellow, or Red status.</p></li>
				<li class="about-step"><span class="about-step__number">04</span><h3>Inform</h3><p>The dashboard and resident-facing channels present current status and official advisory information.</p></li>
			</ol>
		</section>

		<section class="about-channels" aria-labelledby="channelsTitle">
			<p class="eyebrow">What it provides</p>
			<h2 id="channelsTitle">Monitoring, context, and communication</h2>
			<div class="about-channel-grid">
				<article class="about-channel">
					<h3>Live monitoring</h3>
					<p>The public monitor and MDRRMO dashboard show water level and warning status. Trend, rate of rise, and estimated time-to-threshold are supporting indicators.</p>
				</article>
				<article class="about-channel">
					<h3>Resident information</h3>
					<p>The resident web app provides access to monitoring information and published advisories. The wider warning design includes push and SMS notification options, plus a community warning speaker when configured.</p>
				</article>
				<article class="about-channel">
					<h3>Local and cloud records</h3>
					<p>Local storage supports continued recording during connectivity interruptions, with cloud synchronization when a connection is available.</p>
				</article>
			</div>
		</section>

		<aside class="about-scope" aria-labelledby="scopeTitle">
			<h2 id="scopeTitle">How to interpret the information</h2>
			<p>Warning classification is based on validated river water level and configured thresholds. Weather information is supplementary; weather, trend, rate of rise, and estimated time-to-threshold do not set the Green, Yellow, or Red classification.</p>
			<p>This prototype does not guarantee flood prediction. Thresholds require confirmation with MDRRMO, and residents should always follow official instructions and local emergency guidance.</p>
		</aside>

		<section class="about-next" aria-label="Explore the monitoring system">
			<div>
				<h2>Follow current conditions</h2>
				<p>View the latest monitor status or check published advisories.</p>
			</div>
			<div class="about-next__actions">
				<a href="<?php echo html_escape($home_url); ?>#monitor">Live monitor</a>
				<a href="<?php echo html_escape($announcements_url); ?>">Announcements</a>
			</div>
		</section>
	</main>

	<footer class="footer">
		<div class="footer__inner">
			<a class="brand" href="<?php echo html_escape($home_url); ?>" aria-label="Daguitan Flood Monitor home">
				<span class="brand__mark" aria-hidden="true"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span>
				<span class="brand__text">
					<span class="brand__name">DAGUITAN FLOOD MONITOR</span>
					<span class="footer__place">Municipality of Dulag, Leyte · MDRRMO</span>
				</span>
			</a>
			<nav class="footer__nav" aria-label="Footer">
				<?php $this->load->view('partials/public_nav'); ?>
			</nav>
		</div>
		<div class="footer__bar">
			<div class="footer__bar-inner">
				<p>© <?php echo date('Y'); ?> Municipality of Dulag, Leyte. Developed for community flood awareness.</p>
				<p class="footer__credit">Designed and developed by J. Abina, E. Amor, and J. Lagunzad</p>
			</div>
		</div>
	</footer>

	<nav class="bottom-nav" aria-label="App">
		<a href="<?php echo html_escape($home_url); ?>">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="m3 10 9-7 9 7"/><path d="M5.5 9v11h13V9M9.5 20v-6h5v6"/></svg>
			Home
		</a>
		<a href="<?php echo html_escape($home_url); ?>#monitor">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 12h4l2.5-4 4.5 8 2.5-4H21"/></svg>
			Monitor
		</a>
		<a href="<?php echo html_escape($announcements_url); ?>">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="M5 3.5h14a2 2 0 0 1 2 2v15H7a2 2 0 0 1-2-2z"/><path d="M5 18.5a2 2 0 0 0 2 2M9 8h8M9 12h8M9 16h5"/></svg>
			News
		</a>
		<a href="<?php echo html_escape($about_url); ?>" class="is-active" aria-current="page">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>
			About
		</a>
	</nav>

	<script>
		window.DAGUITAN = <?php echo json_encode(array('homeUrl' => $home_url, 'serviceWorkerUrl' => $base_url . 'sw.js'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js?v=20260929about"></script>
</body>
</html>
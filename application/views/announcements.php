<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$w = $weather;
$a = $announcement;
$list = (isset($announcements) && is_array($announcements)) ? $announcements : array();
$warning_key = $m['warning_level'];
$wx_theme = isset($w['theme']) ? (string) $w['theme'] : 'cloudy';
if ( ! preg_match('/^[a-z]+(-[a-z]+)?$/', $wx_theme))
{
	$wx_theme = 'cloudy';
}

$level_label = function ($level) {
	if ($level === 'red')
	{
		return 'Emergency';
	}
	if ($level === 'yellow')
	{
		return 'Advisory';
	}
	return 'Information';
};

$featured_level = isset($a['level']) ? $a['level'] : 'info';
if ($featured_level !== 'red' && $featured_level !== 'yellow')
{
	$featured_level = 'info';
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
	<meta name="description" content="MDRRMO Dulag announcements, flood advisories, and emergency notices for Daguitan Bridge.">
	<title><?php echo html_escape($page_title); ?> · Dulag, Leyte</title>
	<link rel="manifest" href="<?php echo html_escape($base_url); ?>manifest.webmanifest">
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="apple-touch-icon" href="<?php echo html_escape($asset_url); ?>icons/pwa-icon-192.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20261006g">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing-weather.css?v=1">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261006n">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="landing-page announce-page<?php echo ! empty($resident_portal) ? ' portal-page resident-portal' : ''; ?>">
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
			<a class="brand" href="<?php echo html_escape(isset($home_url) ? $home_url : site_url()); ?>" aria-label="Daguitan Flood Monitor home">
				<span class="brand__mark" aria-hidden="true">
					<img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40">
				</span>
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
				<button class="icon-btn" type="button" id="notifyBtn" aria-label="Notifications, no unread alerts">
					<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/>
						<path d="M10 19a2 2 0 0 0 4 0"/>
					</svg>
				</button>
				<?php if ( ! empty($resident_portal)): ?>
				<button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
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
			<a class="btn btn--primary btn--block" href="<?php echo html_escape(isset($home_url) ? $home_url : site_url()); ?>#monitor">View live status</a>
		</div>
	</div>

	<main id="main"<?php echo ! empty($resident_portal) ? ' class="resident-layout"' : ''; ?>>
		<?php if ( ! empty($resident_portal)): ?>
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation">
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
				<a href="<?php echo html_escape($home_url); ?>">Dashboard</a>
				<a href="<?php echo html_escape(site_url('portal/go-bag')); ?>">Go Bag</a>
				<a href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>">Evacuation Centers</a>
				<a class="is-active" href="<?php echo html_escape($announcements_url); ?>" aria-current="page">Announcements</a>
				<a href="<?php echo html_escape(site_url('portal/reports')); ?>">Flood / Hazard Reports</a>
				<a href="<?php echo html_escape(site_url('portal/profile')); ?>">My Profile</a>
				<a href="<?php echo html_escape(site_url('portal/help')); ?>">Help / How to Use</a>
			</nav>
			<a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			<div class="resident-sidebar__government">
				<span>Republic of the Philippines</span>
				<strong>Municipality of Dulag, Leyte</strong>
			</div>
		</aside>
		<div class="resident-layout__content">
		<?php endif; ?>
		<section class="hero announce-hero" id="home">
			<div class="hero__copy reveal">
				<p class="eyebrow">MDRRMO Dulag · Public notices</p>
				<h1>Announcements and flood advisories</h1>
				<p class="lede">Official notices for residents near Daguitan Bridge. Check live river status, then follow MDRRMO guidance when an advisory is active.</p>
				<div class="hero__actions">
					<a class="btn btn--primary" href="<?php echo html_escape(isset($home_url) ? $home_url : site_url()); ?>#monitor">View live status</a>
					<a class="btn btn--ghost" href="#notices">Browse notices</a>
					<a class="btn btn--ghost" href="https://www.facebook.com/profile.php?id=100063639583165" target="_blank" rel="noopener noreferrer">Follow on Facebook</a>
				</div>
			</div>
		</section>

		<?php if ( ! empty($resident_portal)): ?>
		<section class="section section--tight" id="live-advisory">
			<article class="glass-card announce-card reveal announce-card--<?php echo html_escape($featured_level); ?><?php echo ! empty($a['active']) ? ' is-active' : ''; ?>" aria-labelledby="announceTitle">
				<div class="announce-card__head">
					<div>
						<p class="card-kicker">Live advisory</p>
						<h2 id="announceTitle">Current notice</h2>
					</div>
					<span class="pill" id="announcePill"><?php echo ! empty($a['active']) ? 'Active' : 'Quiet'; ?></span>
				</div>
				<div id="announceBody" class="live-advisory__body">
					<?php if ( ! empty($a['active'])): ?>
						<p class="status-badge status-badge--<?php echo html_escape($featured_level === 'info' ? 'yellow' : $featured_level); ?>"><?php echo html_escape($a['title']); ?></p>
					<?php else: ?>
						<p class="announce-card__empty"><?php echo html_escape($a['title']); ?></p>
					<?php endif; ?>
					<p class="announce-live-scope" id="announceScope"><?php echo ! empty($a['barangay']) ? 'For Barangay ' . html_escape($a['barangay']) : 'Municipality-wide notice'; ?></p>
					<p class="live-advisory__copy" id="announceCopy"><?php echo nl2br(html_escape($a['body'])); ?></p>
					<button class="live-advisory__toggle" type="button" data-toggle-announcement aria-controls="announceCopy" aria-expanded="false" hidden>Read full advisory</button>
				</div>
				<p class="issuer"><?php echo html_escape(isset($a['issuer']) ? $a['issuer'] : 'MDRRMO Dulag'); ?></p>
			</article>
		</section>
		<?php endif; ?>

		<section class="section section--tight" id="safety">
			<header class="section__head reveal">
				<h2>If an advisory is active</h2>
			</header>
			<div class="metric-grid safety-grid">
				<article class="glass-card reveal">
					<h3>Stay informed</h3>
					<p class="card-copy">Check live water level and trend on the monitor before traveling near the bridge.</p>
				</article>
				<article class="glass-card reveal">
					<h3>Yellow advisory</h3>
					<p class="card-copy">Stay alert, avoid the riverbank, and prepare to move if water keeps rising.</p>
				</article>
				<article class="glass-card reveal">
					<h3>Emergency / red</h3>
					<p class="card-copy">Follow MDRRMO instructions. Do not cross flowing water or wait on the bridge.</p>
				</article>
				<article class="glass-card reveal">
					<h3>Hotlines</h3>
					<p class="card-copy">Call MDRRMO Dulag or barangay responders if someone is trapped.</p>
				</article>
			</div>
		</section>

		<section class="section" id="notices">
			<header class="section__head reveal">
				<h2>Published announcements</h2>
				<p><?php echo ! empty($resident_portal) ? 'Barangay-specific notices for your area appear first, along with every municipality-wide advisory.' : 'Municipality-wide advisories posted by MDRRMO Dulag for residents.'; ?></p>
			</header>

			<?php if (empty($list)): ?>
				<article class="glass-card reveal announce-empty">
					<p class="announce-card__empty">No published announcements yet</p>
					<p><?php echo ! empty($resident_portal) && empty($resident_barangay) ? 'No municipality-wide notices are published yet. Add your barangay in My Profile to receive notices targeted to your area.' : 'When MDRRMO posts an official notice for your area, it will appear here. Continue to watch live water levels during heavy rainfall.'; ?></p>
				</article>
			<?php else: ?>
				<?php $announcement_index = 0; ?>
				<ul class="announce-list">
					<?php foreach ($list as $ann):
						$announcement_index++;
						$ann_level = isset($ann['level']) ? $ann['level'] : 'info';
						if ($ann_level !== 'red' && $ann_level !== 'yellow')
						{
							$ann_level = 'info';
						}
						$updated = isset($ann['updated_at']) ? $ann['updated_at'] : '';
						$updated_label = $updated !== '' ? date('M j, Y · g:i A', strtotime($updated)) : '';
						$ann_barangay = isset($ann['barangay']) ? trim((string) $ann['barangay']) : '';
					?>
						<li class="glass-card reveal announce-item announce-item--<?php echo html_escape($ann_level); ?>">
							<div class="announce-item__meta">
								<span class="warning-chip warning-chip--<?php echo $ann_level === 'info' ? 'green' : html_escape($ann_level); ?>">
									<span aria-hidden="true">●</span> <?php echo html_escape(strtoupper($level_label($ann_level))); ?>
								</span>
								<span class="announce-item__scope"><?php echo $ann_barangay !== '' ? 'Barangay ' . html_escape($ann_barangay) : 'Municipality-wide'; ?></span>
								<?php if ($updated_label !== ''): ?>
									<time datetime="<?php echo html_escape($updated); ?>"><?php echo html_escape($updated_label); ?></time>
								<?php endif; ?>
							</div>
							<h3><?php echo html_escape($ann['title']); ?></h3>
							<p class="announce-item__copy" id="publishedAnnouncementBody<?php echo $announcement_index; ?>"><?php echo nl2br(html_escape($ann['body'])); ?></p>
							<button class="announce-item__toggle" type="button" data-toggle-announcement aria-controls="publishedAnnouncementBody<?php echo $announcement_index; ?>" aria-expanded="false" hidden>Read full announcement</button>
							<p class="issuer">MDRRMO Dulag</p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>

		<section class="section section--tight fb-page-shell reveal" aria-label="MDRRMO Dulag Facebook timeline">
			<div class="fb-page-shell__heading">
				<h2>MDRRMO Dulag on Facebook</h2>
				<a href="https://www.facebook.com/profile.php?id=100063639583165" target="_blank" rel="noopener noreferrer">Open Facebook page</a>
			</div>
			<div class="fb-page-shell__embed">
				<iframe
					title="MDRRMO Dulag Facebook page and timeline"
					src="https://www.facebook.com/plugins/page.php?href=<?php echo rawurlencode('https://www.facebook.com/profile.php?id=100063639583165'); ?>&tabs=timeline&width=500&height=680&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true"
					width="500"
					height="680"
					style="border:none;overflow:hidden"
					scrolling="yes"
					frameborder="0"
					allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
					loading="lazy">
				</iframe>
			</div>
		</section>
		<?php if ( ! empty($resident_portal)): ?></div><?php endif; ?>
	</main>
	<?php if ( ! empty($resident_portal)): ?>
	<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
	<?php endif; ?>

	<footer class="footer">
		<div class="footer__inner">
			<a class="brand" href="<?php echo html_escape(isset($home_url) ? $home_url : site_url()); ?>" aria-label="Daguitan Flood Monitor home">
				<span class="brand__mark" aria-hidden="true">
					<img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40">
				</span>
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
		<a href="<?php echo html_escape(isset($home_url) ? $home_url : site_url()); ?>">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="m3 10 9-7 9 7"/><path d="M5.5 9v11h13V9M9.5 20v-6h5v6"/></svg>
			Home
		</a>
		<a href="<?php echo html_escape(isset($home_url) ? $home_url : site_url()); ?>#monitor">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 12h4l2.5-4 4.5 8 2.5-4H21"/></svg>
			Monitor
		</a>
		<a href="<?php echo html_escape(isset($announcements_url) ? $announcements_url : site_url('announcements')); ?>" class="is-active" aria-current="page">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><path d="M5 3.5h14a2 2 0 0 1 2 2v15H7a2 2 0 0 1-2-2z"/><path d="M5 18.5a2 2 0 0 0 2 2M9 8h8M9 12h8M9 16h5"/></svg>
			News
		</a>
		<a href="<?php echo html_escape(isset($about_url) ? $about_url : site_url('about')); ?>">
			<svg class="bottom-nav__icon" viewBox="0 0 24 24" aria-hidden="true" fill="none"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg>
			About
		</a>
	</nav>

	<script>
		window.DAGUITAN = <?php echo json_encode(array(
			'monitor' => $m,
			'weather' => $w,
			'announcement' => $a,
			'statusUrl' => $status_url,
			'homeUrl' => isset($home_url) ? $home_url : site_url(),
			'pollMs' => 5000,
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing-weather.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js?v=20261006c"></script>
	<?php if ( ! empty($resident_portal)): ?>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-profile.js?v=1"></script>
	<?php endif; ?>
</body>
</html>

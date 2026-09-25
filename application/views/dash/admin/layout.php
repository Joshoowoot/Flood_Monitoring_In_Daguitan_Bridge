<?php defined('BASEPATH') OR exit('No direct script access allowed');
$section = isset($admin_section) ? $admin_section : 'dashboard';
$nav_groups = array(
	'Overview' => array('dashboard', 'live', 'history', 'analytics'),
	'Operations' => array('alerts', 'announcements', 'sms', 'sensors'),
	'Administration' => array('residents', 'reports', 'settings'),
);
function admin_nav_icon($key) {
	$icons = array(
		'dashboard' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg>',
		'live' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12.5a7 7 0 0 1 14 0"/><path d="M12 16v3"/><circle cx="12" cy="12.5" r="1.2" fill="currentColor" stroke="none"/></svg>',
		'history' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/></svg>',
		'alerts' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>',
		'analytics' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5"/><path d="M8 19v-6"/><path d="M12 19V9"/><path d="M16 19v-9"/><path d="M20 19V7"/></svg>',
		'sensors' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v3"/><path d="M6 8a6 6 0 1 0 12 0"/><path d="M4 20h16"/></svg>',
		'announcements' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10v4"/><path d="M7 8v8"/><path d="M10 6v12"/><path d="M13 9v6"/><path d="M16 7v10"/><path d="M19 10v4"/></svg>',
		'residents' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.4-3.2 3.8-4.8 7-4.8s5.6 1.6 7 4.8"/></svg>',
		'reports' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h9l3 3v13H6z"/><path d="M9 13h6M9 17h4"/></svg>',
		'settings' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>',
	);
	return isset($icons[$key]) ? $icons[$key] : $icons['dashboard'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#6e0d1a">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo html_escape($page_title); ?> · MDRRMO Admin Portal</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/admin-portal.css?v=6">
</head>
<body class="admin-portal">
	<div class="admin-layout">
		<aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation">
			<div class="admin-sidebar__head">
				<a class="admin-sidebar__brand" href="<?php echo site_url('admin'); ?>">
					<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="44" height="44">
					<div>
						<strong>Daguitan Monitor</strong>
						<span>MDRRMO Admin Portal</span>
					</div>
				</a>
			</div>

			<div class="admin-sidebar__scroll">
				<?php foreach ($nav_groups as $group_label => $keys): ?>
					<p class="admin-nav__group"><?php echo html_escape($group_label); ?></p>
					<nav class="admin-nav" aria-label="<?php echo html_escape($group_label); ?>">
						<?php foreach ($keys as $key): ?>
							<?php if ( ! isset($admin_nav[$key])) continue;
							$item = $admin_nav[$key]; ?>
							<a class="admin-nav__link<?php echo $section === $key ? ' is-active' : ''; ?>" href="<?php echo site_url($item['href']); ?>">
								<span class="admin-nav__icon"><?php echo admin_nav_icon($key); ?></span>
								<span class="admin-nav__text"><?php echo html_escape($item['label']); ?></span>
								<?php if ($key === 'alerts' && ! empty($portal_stats['active_alerts'])): ?>
									<span class="admin-nav__badge"><?php echo (int) $portal_stats['active_alerts']; ?></span>
								<?php elseif ($key === 'sensors' && isset($portal_stats['sensor_online']) && ! $portal_stats['sensor_online']): ?>
									<span class="admin-nav__badge admin-nav__badge--warn" title="Sensor offline">!</span>
								<?php elseif ($key === 'residents' && ! empty($portal_stats['residents'])): ?>
									<span class="admin-nav__badge admin-nav__badge--muted"><?php echo (int) $portal_stats['residents']; ?></span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</nav>
				<?php endforeach; ?>
			</div>

			<div class="admin-sidebar__foot">
				<a class="admin-nav__link admin-nav__link--logout" href="<?php echo html_escape($logout_url); ?>">
					<span class="admin-nav__icon"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5"/><path d="M15 12H4"/><path d="M19 4v16"/></svg></span>
					<span class="admin-nav__text">Logout</span>
				</a>
				<p class="admin-sidebar__gov">Republic of the Philippines · Dulag, Leyte</p>
			</div>
		</aside>

		<div class="admin-backdrop" id="adminBackdrop" hidden></div>

		<div class="admin-stage">
			<header class="admin-header">
				<div class="admin-header__left">
					<button class="admin-header__menu" type="button" id="adminNavToggle" aria-expanded="false" aria-controls="adminSidebar" aria-label="Open menu">
						<span></span><span></span><span></span>
					</button>
					<div>
						<p class="admin-kicker">Daguitan Bridge · MDRRMO Dulag</p>
						<h1><?php echo html_escape($page_heading); ?></h1>
					</div>
				</div>
				<div class="admin-header__actions">
					<?php $this->load->view('partials/notifications_bell'); ?>
					<a class="btn btn--ghost btn--compact" href="<?php echo html_escape($public_url); ?>">Public site</a>
					<form method="post" action="<?php echo site_url('admin/sync'); ?>">
						<input type="hidden" name="return_to" value="<?php echo html_escape(current_url()); ?>">
						<button class="btn btn--primary btn--compact" type="submit">Sync to cloud<?php if (! empty($portal_stats['pending_sync'])): ?> (<?php echo (int) $portal_stats['pending_sync']; ?>)<?php endif; ?></button>
					</form>
				</div>
			</header>

			<div class="admin-main">
				<?php if ( ! empty($page_lede)): ?>
					<p class="admin-lede"><?php echo html_escape($page_lede); ?></p>
				<?php endif; ?>

				<?php if ( ! empty($sync_notice)): ?>
					<p class="admin-notice" role="status"><?php echo html_escape($sync_notice); ?></p>
				<?php endif; ?>

				<div class="admin-content">
					<?php $this->load->view('dash/admin/pages/' . $section); ?>
				</div>
			</div>
		</div>
	</div>

	<script>
		window.DAGUITAN_ADMIN = <?php
		$admin_js = array(
			'statusUrl'  => isset($status_url) ? $status_url : '',
			'pollMs'     => 5000,
			'thresholds' => isset($thresholds) ? $thresholds : array('yellow' => 1.5, 'red' => 2.5),
		);
		if (isset($chart))
		{
			$admin_js['chart'] = $chart;
		}
		echo json_encode($admin_js, JSON_UNESCAPED_SLASHES);
		?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/admin-portal.js?v=6"></script>
	<?php if ( ! empty($notify_config)): ?>
	<script>
		window.DAGUITAN_NOTIFY = <?php echo json_encode($notify_config, JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/notifications.js?v=2"></script>
	<?php endif; ?>
</body>
</html>

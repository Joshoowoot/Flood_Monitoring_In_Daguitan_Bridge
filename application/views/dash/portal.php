<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$a = $announcement;
$w = $weather;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css">
</head>
<body class="dash-body">
	<header class="dash-top">
		<div class="dash-top__inner">
			<a class="brand" href="<?php echo site_url('/'); ?>">
				<span class="brand__mark" aria-hidden="true">
					<img class="brand__logo brand__logo--dash" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="36" height="36">
				</span>
				<span class="brand__text"><span class="brand__name">Resident Portal</span></span>
			</a>
			<nav class="dash-nav">
				<a href="<?php echo site_url('/'); ?>">Public site</a>
				<a href="<?php echo site_url('auth/logout'); ?>">Sign out</a>
			</nav>
		</div>
	</header>

	<main class="dash-main">
		<p class="dash-hello">Welcome, <?php echo html_escape($auth_name); ?></p>
		<h1>Your flood watch</h1>
		<p class="lede">Personalized guidance based on the live Daguitan Bridge reading. This is not a substitute for official evacuation orders.</p>

		<article class="status-panel portal-hero">
			<p class="status-panel__kicker">Current flood status</p>
			<p class="status-badge status-badge--<?php echo html_escape($m['warning_level']); ?>" id="heroStatusBadge">
				<span class="status-dot"></span>
				<span id="heroStatusLabel"><?php echo strtoupper(html_escape($m['warning_label'])); ?></span>
			</p>
			<div class="status-panel__grid">
				<div>
					<span class="meta">Water level</span>
					<strong class="metric" id="heroWaterLevel"><?php echo number_format($m['water_level_m'], 2); ?> m</strong>
				</div>
				<div>
					<span class="meta">Trend</span>
					<strong id="heroTrend"><?php echo html_escape($m['trend_label']); ?></strong>
				</div>
				<div>
					<span class="meta">Updated</span>
					<time id="heroUpdated"><?php echo html_escape($m['last_updated']); ?></time>
				</div>
			</div>
		</article>

		<div class="dash-grid">
			<section class="glass-card">
				<h2>What to do now</h2>
				<ol class="action-list">
					<?php foreach ($actions as $item): ?>
						<li><?php echo html_escape($item); ?></li>
					<?php endforeach; ?>
				</ol>
			</section>
			<section class="glass-card">
				<h2>Emergency desk</h2>
				<ul class="hotline-list">
					<li><strong>MDRRMO Dulag</strong> <a href="tel:09171234567">0917 123 4567</a></li>
					<li><strong>PNP Dulag</strong> <a href="tel:09985991111">0998 599 1111</a></li>
					<li><strong>BFP Dulag</strong> <a href="tel:0533250000">(053) 325-0000</a></li>
					<li><strong>Nationwide emergency</strong> <a href="tel:911">911</a></li>
				</ul>
				<p class="issuer"><?php echo html_escape($a['title']); ?></p>
				<p><?php echo html_escape($a['body']); ?></p>
			</section>
		</div>
	</main>
	<script>
		window.DAGUITAN = <?php echo json_encode(array('statusUrl' => $status_url, 'pollMs' => 5000), JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js"></script>
</body>
</html>

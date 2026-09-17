<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$a = $announcement;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo html_escape($page_title); ?> · MDRRMO Dulag</title>
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
				<span class="brand__text"><span class="brand__name">Operations Console</span></span>
			</a>
			<nav class="dash-nav">
				<a href="<?php echo site_url('/'); ?>">Public site</a>
				<a href="<?php echo site_url('auth/logout'); ?>">Sign out</a>
			</nav>
		</div>
	</header>

	<main class="dash-main">
		<p class="dash-hello">Signed in as <?php echo html_escape($auth_name); ?></p>
		<h1>Daguitan Bridge station</h1>
		<p class="lede">Live telemetry from the ESP32 ultrasonic sensor. Readings are always saved in local SQL, then copied to the cloud database when the internet is available.</p>

		<?php if ( ! empty($sync_notice)): ?>
			<p class="auth-error" role="status" style="max-width:52rem;"><?php echo html_escape($sync_notice); ?></p>
		<?php endif; ?>

		<div class="dash-kpis">
			<article class="glass-card">
				<h3>Water level</h3>
				<p class="metric" id="cardWater"><?php echo number_format($m['water_level_m'], 2); ?> m</p>
			</article>
			<article class="glass-card">
				<h3>Warning</h3>
				<p class="metric" id="heroStatusLabel"><?php echo html_escape($m['warning_label']); ?></p>
			</article>
			<article class="glass-card">
				<h3>Sensor</h3>
				<p class="metric <?php echo $m['sensor_status'] === 'online' ? 'metric--online' : 'metric--offline'; ?>" id="cardSensor">
					<span class="live-dot"></span> <span id="cardSensorLabel"><?php echo html_escape($m['sensor_label']); ?></span>
				</p>
			</article>
			<article class="glass-card">
				<h3>Last packet</h3>
				<p class="metric" id="heroUpdated"><?php echo html_escape($m['last_updated']); ?></p>
			</article>
		</div>

		<div class="dash-grid">
			<section class="glass-card">
				<h2>Hybrid storage</h2>
				<dl class="dash-dl">
					<div><dt>Internet</dt><dd><?php echo html_escape($sync['internet_label']); ?></dd></div>
					<div><dt>Local database</dt><dd>MDRRMO_DULAG</dd></div>
					<div><dt>Cloud copy</dt><dd><?php echo html_escape($sync['cloud_database']); ?></dd></div>
					<div><dt>Waiting to copy</dt><dd><?php echo (int) $sync['pending_total']; ?> row(s) (<?php echo (int) $sync['pending_readings']; ?> readings, <?php echo (int) $sync['pending_users']; ?> users)</dd></div>
					<div><dt>Readings copied</dt><dd><?php echo (int) $sync['synced_readings']; ?></dd></div>
					<div><dt>Last cloud copy</dt><dd><?php echo $sync['last_synced_at'] ? html_escape($sync['last_synced_at']) : 'Not yet'; ?></dd></div>
				</dl>
				<form method="post" action="<?php echo site_url('admin/sync'); ?>">
					<button class="btn btn--primary" type="submit">Copy pending to cloud</button>
				</form>
			</section>
			<section class="glass-card">
				<h2>Station configuration</h2>
				<dl class="dash-dl">
					<div><dt>Yellow advisory</dt><dd><?php echo number_format((float) $thresholds['yellow'], 2); ?> m</dd></div>
					<div><dt>Red / critical</dt><dd><?php echo number_format((float) $thresholds['red'], 2); ?> m</dd></div>
					<div><dt>Sensor height</dt><dd><?php echo (int) $thresholds['height']; ?> cm</dd></div>
					<div><dt>Public ingest</dt><dd class="mono"><?php echo html_escape($ingest_url); ?></dd></div>
					<div><dt>ESP32 proxy URL</dt><dd class="mono"><?php echo html_escape($proxy_url); ?></dd></div>
				</dl>
			</section>
			<section class="glass-card">
				<h2>Current advisory</h2>
				<p class="status-badge status-badge--<?php echo html_escape($a['level'] ? $a['level'] : $m['warning_level']); ?>"><?php echo html_escape($a['title']); ?></p>
				<p><?php echo html_escape($a['body']); ?></p>
			</section>
		</div>

		<section class="glass-card dash-table-wrap">
			<h2>Recent readings</h2>
			<?php if (empty($history)): ?>
				<p>No Arduino packets stored yet. Power the ESP32 and confirm the ingest URL.</p>
			<?php else: ?>
			<table class="dash-table">
				<thead>
					<tr><th>Time</th><th>Water level</th><th>Storage</th></tr>
				</thead>
				<tbody>
					<?php foreach (array_slice($history, 0, 18) as $row): ?>
						<tr>
							<td><?php echo html_escape(date('M j, g:i:s A', (int) $row['ts'])); ?></td>
							<td><?php echo number_format((float) $row['water_level_m'], 3); ?> m</td>
							<td>
								<?php
								$st = isset($row['sync_status']) ? $row['sync_status'] : 'pending';
								$label = ($st === 'synced') ? 'Local + cloud' : (($st === 'failed') ? 'Local (cloud failed)' : 'Local only');
								?>
								<span class="sync-pill sync-pill--<?php echo html_escape($st); ?>"><?php echo html_escape($label); ?></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</section>
	</main>
	<script>
		window.DAGUITAN = <?php echo json_encode(array('statusUrl' => $status_url, 'pollMs' => 5000), JSON_UNESCAPED_SLASHES); ?>;
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/landing.js"></script>
</body>
</html>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$i = $infra;
$s = isset($snapshot) ? $snapshot : array();
$wl = html_escape($m['warning_level']);
?>
<div class="admin-quick-links glass-card">
	<h2>Quick actions</h2>
	<div class="admin-quick-links__grid">
		<a class="btn btn--ghost" href="<?php echo site_url('admin/live'); ?>">Live monitoring</a>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/alerts'); ?>">Flood alerts <?php if (!empty($s['active_alerts'])): ?>(<?php echo (int) $s['active_alerts']; ?>)<?php endif; ?></a>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/announcements'); ?>">Announcements</a>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/residents'); ?>">Residents (<?php echo (int) (isset($s['residents']) ? $s['residents'] : 0); ?>)</a>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/history'); ?>">Full history</a>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/settings'); ?>">Threshold settings</a>
	</div>
</div>

<div class="admin-kpi-grid">
	<article class="glass-card admin-kpi">
		<h3>Water level</h3>
		<p class="metric" id="admWater"><?php echo number_format($m['water_level_m'], 2); ?> m</p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Warning level</h3>
		<p class="metric"><span class="status-badge status-badge--<?php echo $wl; ?>" id="admWarning"><?php echo html_escape($m['warning_label']); ?></span></p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Rate of rise</h3>
		<p class="metric" id="admRate"><?php echo ($m['rate_cm_min'] > 0 ? '+' : '') . number_format($m['rate_cm_min'], 2); ?> cm/min</p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Est. time-to-threshold</h3>
		<p class="metric" id="admEtt"><?php echo html_escape($m['ett_label']); ?></p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Sensor</h3>
		<p class="metric <?php echo $m['sensor_status'] === 'online' ? 'metric--online' : 'metric--offline'; ?>" id="admSensor">
			<span class="live-dot"></span> <span id="admSensorLabel"><?php echo html_escape($m['sensor_label']); ?></span>
		</p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Internet</h3>
		<p class="metric" id="admInternet"><?php echo html_escape($i['internet']['detail']); ?></p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Active alerts</h3>
		<p class="metric"><a href="<?php echo site_url('admin/alerts'); ?>"><?php echo (int) (isset($s['active_alerts']) ? $s['active_alerts'] : 0); ?></a></p>
	</article>
	<article class="glass-card admin-kpi">
		<h3>Pending cloud sync</h3>
		<p class="metric"><span id="admPendingSync"><?php echo (int) (isset($s['pending_sync']) ? $s['pending_sync'] : 0); ?></span> rows</p>
	</article>
</div>

<section class="glass-card admin-chart-card">
	<div class="admin-chart-card__head">
		<h2>Real-time water level</h2>
		<p>Threshold bands: green below <?php echo number_format($thresholds['yellow'], 2); ?> m · yellow to <?php echo number_format($thresholds['red'], 2); ?> m · red at critical. Updates every 5 seconds.</p>
	</div>
	<div class="admin-chart-wrap">
		<canvas id="adminWaterChart" height="280" aria-label="Water level chart"></canvas>
	</div>
	<div class="warning-track admin-threshold-legend">
		<div class="warning-track__item" data-level="green"><strong>Safe</strong><span>&lt; <?php echo number_format($thresholds['yellow'], 2); ?> m</span></div>
		<div class="warning-track__item" data-level="yellow"><strong>Monitor</strong><span><?php echo number_format($thresholds['yellow'], 2); ?>–<?php echo number_format($thresholds['red'], 2); ?> m</span></div>
		<div class="warning-track__item" data-level="red"><strong>Critical</strong><span>&ge; <?php echo number_format($thresholds['red'], 2); ?> m</span></div>
	</div>
</section>

<div class="admin-split">
	<section class="glass-card">
		<h2>Hybrid storage</h2>
		<dl class="dash-dl">
			<div><dt>Local database</dt><dd>MDRRMO_DULAG</dd></div>
			<div><dt>Cloud copy</dt><dd><?php echo html_escape($sync['cloud_database']); ?></dd></div>
			<div><dt>Waiting to copy</dt><dd><?php echo (int) $sync['pending_total']; ?> row(s)</dd></div>
			<div><dt>Last cloud copy</dt><dd><?php echo $sync['last_synced_at'] ? html_escape($sync['last_synced_at']) : 'Not yet'; ?></dd></div>
			<div><dt>Published advisories</dt><dd><?php echo (int) (isset($s['published_announcements']) ? $s['published_announcements'] : 0); ?></dd></div>
		</dl>
	</section>
	<section class="glass-card dash-table-wrap">
		<h2>Latest readings</h2>
		<table class="dash-table">
			<thead><tr><th>Time</th><th>Level</th><th>Warning</th><th>Trend</th></tr></thead>
			<tbody id="admRecentBody">
				<?php foreach ($history as $row): ?>
					<tr>
						<td><?php echo html_escape(date('M j, g:i A', (int) $row['ts'])); ?></td>
						<td><?php echo number_format($row['water_level_m'], 3); ?> m</td>
						<td><span class="status-badge status-badge--<?php echo html_escape($row['warning_level']); ?>"><?php echo html_escape($row['warning_label']); ?></span></td>
						<td><?php echo html_escape($row['trend_label']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="admin-muted"><a href="<?php echo site_url('admin/history'); ?>">View full history →</a></p>
	</section>
</div>

<?php if (! empty($recent_logins)): ?>
<section class="glass-card dash-table-wrap">
	<h2>Recent resident sign-ins</h2>
	<table class="dash-table">
		<thead><tr><th>When</th><th>Name</th><th>Mobile</th><th>Role</th></tr></thead>
		<tbody>
			<?php foreach ($recent_logins as $log): ?>
				<tr>
					<td><?php echo html_escape(isset($log['logged_in_at']) ? $log['logged_in_at'] : '—'); ?></td>
					<td><?php echo html_escape(isset($log['name']) ? $log['name'] : '—'); ?></td>
					<td><?php echo ! empty($log['phone']) ? html_escape($log['phone']) : '—'; ?></td>
					<td><?php echo html_escape(isset($log['role']) ? $log['role'] : 'user'); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p class="admin-muted"><a href="<?php echo site_url('admin/residents'); ?>">Manage residents →</a></p>
</section>
<?php endif; ?>

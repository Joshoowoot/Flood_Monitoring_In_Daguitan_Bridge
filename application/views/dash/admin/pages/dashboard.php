<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$i = $infra;
$s = isset($snapshot) ? $snapshot : array();
$wl = html_escape($m['warning_level']);
$water_level = (float) $m['water_level_m'];
$yellow_threshold = (float) $thresholds['yellow'];
$red_threshold = (float) $thresholds['red'];
$threshold_distance = '—';
$threshold_target = 'to advisory threshold';
if ($m['sensor_status'] === 'waiting')
{
	$threshold_target = 'Waiting for telemetry';
}
elseif ($water_level >= $red_threshold)
{
	$threshold_target = 'Critical threshold reached';
}
else
{
	$next_threshold = ($water_level >= $yellow_threshold) ? $red_threshold : $yellow_threshold;
	$threshold_distance = number_format(max(0, $next_threshold - $water_level), 2) . ' m';
	$threshold_target = ($water_level >= $yellow_threshold) ? 'to critical threshold' : 'to advisory threshold';
}
?>
<div class="admin-dashboard">
<section class="admin-dashboard-group" aria-labelledby="adminWaterGroupTitle">
	<header class="admin-dashboard-group__head">
		<div>
			<p class="admin-kicker">Flood conditions</p>
			<h2 id="adminWaterGroupTitle">Water status</h2>
		</div>
		<p>Current readings and proximity to warning levels</p>
	</header>
	<div class="admin-kpi-grid admin-kpi-grid--water">
	<article class="glass-card admin-kpi admin-kpi--water">
		<h3>Water level</h3>
		<p class="metric" id="admWater"><?php echo number_format($m['water_level_m'], 2); ?> m</p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--warning admin-kpi--warning-<?php echo $wl; ?>">
		<h3>Warning level</h3>
		<p class="metric"><span class="status-badge status-badge--<?php echo $wl; ?>" id="admWarning"><?php echo html_escape($m['warning_label']); ?></span></p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--rate">
		<h3>Rate of rise</h3>
		<p class="metric" id="admRate"><?php echo ($m['rate_cm_min'] > 0 ? '+' : '') . number_format($m['rate_cm_min'], 2); ?> cm/min</p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--trend">
		<h3>Water trend</h3>
		<p class="metric" id="admTrend"><?php echo html_escape($m['trend_label']); ?></p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--ett">
		<h3>Est. time-to-threshold</h3>
		<p class="metric" id="admEtt"><?php echo html_escape($m['ett_label']); ?></p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--distance">
		<h3>Distance to next warning threshold</h3>
		<p class="metric" id="admThresholdDistance"><?php echo html_escape($threshold_distance); ?></p>
		<small class="admin-kpi__context" id="admThresholdTarget"><?php echo html_escape($threshold_target); ?></small>
	</article>
	</div>
</section>

<section class="admin-dashboard-group" aria-labelledby="adminInfrastructureGroupTitle">
	<header class="admin-dashboard-group__head">
		<div>
			<p class="admin-kicker">System connectivity</p>
			<h2 id="adminInfrastructureGroupTitle">Station health</h2>
		</div>
		<p>Sensor and internet connection status</p>
	</header>
	<div class="admin-kpi-grid admin-kpi-grid--support">
	<article class="glass-card admin-kpi admin-kpi--sensor">
		<h3>Sensor</h3>
		<p class="metric <?php echo $m['sensor_status'] === 'online' ? 'metric--online' : 'metric--offline'; ?>" id="admSensor">
			<span class="live-dot"></span> <span id="admSensorLabel"><?php echo html_escape($m['sensor_label']); ?></span>
		</p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--internet">
		<h3>Internet</h3>
		<p class="metric" id="admInternet"><?php echo html_escape($i['internet']['detail']); ?></p>
	</article>
	</div>
</section>

<section class="admin-dashboard-group" aria-labelledby="adminResponseGroupTitle">
	<header class="admin-dashboard-group__head">
		<div>
			<p class="admin-kicker">Operations</p>
			<h2 id="adminResponseGroupTitle">Response &amp; data sync</h2>
		</div>
		<p>Alerts that need attention and queued cloud updates</p>
	</header>
	<div class="admin-kpi-grid admin-kpi-grid--support">
	<article class="glass-card admin-kpi admin-kpi--alerts<?php echo ! empty($s['active_alerts']) ? ' has-active' : ''; ?>">
		<h3>Active alerts</h3>
		<p class="metric"><a href="<?php echo site_url('admin/alerts'); ?>"><?php echo (int) (isset($s['active_alerts']) ? $s['active_alerts'] : 0); ?></a></p>
	</article>
	<article class="glass-card admin-kpi admin-kpi--sync<?php echo ! empty($s['pending_sync']) ? ' has-pending' : ''; ?>">
		<h3>Pending cloud sync</h3>
		<p class="metric"><span id="admPendingSync"><?php echo (int) (isset($s['pending_sync']) ? $s['pending_sync'] : 0); ?></span> rows</p>
	</article>
	</div>
</section>

<section class="glass-card admin-chart-card">
	<div class="admin-chart-card__head">
		<div>
			<p class="admin-kicker">Live telemetry</p>
			<h2>Real-time water level</h2>
		</div>
		<p class="admin-chart-refresh"><span aria-hidden="true"></span> Updates every 5 seconds</p>
	</div>
	<div class="admin-chart-layout">
		<aside class="admin-threshold-meter" aria-label="Flood threshold levels">
			<p class="admin-kicker">Current warning</p>
			<strong class="admin-threshold-meter__current"><?php echo html_escape($m['warning_label']); ?></strong>
			<span class="admin-threshold-meter__reading"><?php echo number_format($m['water_level_m'], 2); ?> m water level</span>
			<div class="admin-threshold-meter__scale">
				<div class="admin-threshold-meter__level<?php echo $wl === 'red' ? ' is-active' : ''; ?> admin-threshold-meter__level--red"><span>RED <b>Critical</b></span><small>&ge; <?php echo number_format($thresholds['red'], 2); ?> m</small></div>
				<div class="admin-threshold-meter__level<?php echo $wl === 'yellow' ? ' is-active' : ''; ?> admin-threshold-meter__level--yellow"><span>YELLOW <b>Monitor</b></span><small><?php echo number_format($thresholds['yellow'], 2); ?>–<?php echo number_format($thresholds['red'], 2); ?> m</small></div>
				<div class="admin-threshold-meter__level<?php echo $wl === 'green' ? ' is-active' : ''; ?> admin-threshold-meter__level--green"><span>GREEN <b>Safe</b></span><small>&lt; <?php echo number_format($thresholds['yellow'], 2); ?> m</small></div>
			</div>
		</aside>
		<div class="admin-chart-wrap">
			<canvas id="adminWaterChart" height="190" aria-label="Water level chart"></canvas>
			<p class="admin-chart-empty" id="admChartEmpty" hidden>No recent telemetry is available to plot.</p>
		</div>
	</div>
</section>

<section class="glass-card dash-table-wrap">
		<header class="admin-dashboard-panel-head">
			<p class="admin-kicker">Monitoring data</p>
			<h2>Latest readings</h2>
		</header>
		<div class="admin-table-scroll">
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
		</div>
		<p class="admin-muted"><a href="<?php echo site_url('admin/history'); ?>">View full history →</a></p>
	</section>

<?php if (! empty($recent_logins)): ?>
<section class="glass-card admin-recent-logins">
	<div class="admin-recent-logins__head">
		<div>
			<p class="admin-kicker">Access activity</p>
			<h2>Recent resident sign-ins</h2>
		</div>
		<a class="admin-recent-logins__link" href="<?php echo site_url('admin/residents'); ?>">Manage residents <span aria-hidden="true">→</span></a>
	</div>
	<div class="admin-recent-logins__table-wrap">
		<table class="admin-recent-logins__table">
			<thead><tr><th scope="col">When</th><th scope="col">Name</th><th scope="col">Mobile</th><th scope="col">Role</th></tr></thead>
			<tbody>
				<?php foreach ($recent_logins as $log): ?>
					<?php
						$login_role = isset($log['role']) ? strtolower(trim((string) $log['role'])) : 'user';
						$role_class = in_array($login_role, array('admin', 'user'), TRUE) ? $login_role : 'other';
					?>
					<tr>
						<td data-label="When"><time><?php echo html_escape(isset($log['logged_in_at']) ? $log['logged_in_at'] : '—'); ?></time></td>
						<td data-label="Name" class="admin-recent-logins__name"><?php echo html_escape(isset($log['name']) ? $log['name'] : '—'); ?></td>
						<td data-label="Mobile"><?php if (! empty($log['phone'])): ?><?php echo html_escape($log['phone']); ?><?php else: ?><span class="admin-recent-logins__empty">Not provided</span><?php endif; ?></td>
						<td data-label="Role"><span class="admin-recent-logins__role admin-recent-logins__role--<?php echo $role_class; ?>"><?php echo html_escape(ucfirst($login_role)); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
<?php endif; ?>
</div>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$i = $infra;
$yellow = isset($thresholds['yellow']) ? (float) $thresholds['yellow'] : 1.5;
$red = isset($thresholds['red']) ? (float) $thresholds['red'] : 2.5;
$level = (float) $m['water_level_m'];
$is_live = isset($m['sensor_status']) && $m['sensor_status'] === 'online';
$age = isset($m['age_seconds']) && is_numeric($m['age_seconds']) ? max(0, (int) $m['age_seconds']) : NULL;
$age_label = $age === NULL ? 'No telemetry received' : ($age < 60 ? $age . ' sec ago' : floor($age / 60) . ' min ago');
$freshness_label = $is_live ? 'Live telemetry · packet received ' . $age_label : (($m['sensor_status'] === 'waiting') ? 'Waiting for first telemetry' : 'Station offline · last packet ' . $age_label);
$threshold_context = ($level >= $red)
	? 'Critical threshold reached'
	: (($level >= $yellow) ? number_format($red - $level, 2) . ' m to critical threshold' : number_format($yellow - $level, 2) . ' m to monitor threshold');
$threshold_max = max(0.01, $red);
$level_percent = min(100, max(0, ($level / $threshold_max) * 100));
$yellow_percent = min(100, max(0, ($yellow / $threshold_max) * 100));
$has_chart_points = isset($chart['points']) && ! empty($chart['points']);
?>
<div class="admin-live-page">
<div class="admin-live-toolbar">
	<div class="admin-live-toolbar__copy">
		<p class="admin-live-intro">Current river level, trend, and field hardware status at Daguitan Bridge.</p>
		<p class="admin-muted"><span class="live-dot"></span> Checking for new station data every 5 seconds.</p>
	</div>
	<div class="admin-live-actions">
		<button class="btn btn--ghost btn--compact" type="button" id="admRefreshNow">Refresh now</button>
	<?php if ($m['warning_level'] !== 'green'): ?>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/alerts'); ?>">Review flood alerts</a>
		<a class="btn btn--primary btn--compact" href="<?php echo site_url('admin/announcements'); ?>">Publish advisory</a>
	<?php endif; ?>
	</div>
</div>
<div class="admin-live-grid">
	<section class="glass-card admin-live-hero<?php echo $is_live ? '' : ' is-stale'; ?>" aria-label="Current river reading">
		<div class="admin-live-reading-head">
			<p class="admin-kicker" id="admReadingLabel"><?php echo $is_live ? 'Current reading' : 'Last known reading'; ?></p>
			<p class="admin-freshness admin-freshness--<?php echo $is_live ? 'live' : (($m['sensor_status'] === 'waiting') ? 'waiting' : 'stale'); ?>" id="admFreshness" role="status"><?php echo html_escape($freshness_label); ?></p>
		</div>
		<p class="admin-live-level" id="admWater"><?php echo number_format($m['water_level_m'], 2); ?> <span>m</span></p>
		<p class="status-badge status-badge--<?php echo html_escape($m['warning_level']); ?>" id="admWarning"><?php echo html_escape($m['warning_label']); ?></p>
		<div class="admin-live-threshold">
			<div class="admin-live-threshold__head"><span>Next threshold</span><strong id="admThresholdContext"><?php echo html_escape($threshold_context); ?></strong></div>
			<div class="admin-threshold-track" id="admThresholdMeter" role="meter" aria-label="Water level relative to critical threshold" aria-valuemin="0" aria-valuemax="<?php echo number_format($red, 2, '.', ''); ?>" aria-valuenow="<?php echo number_format($level, 2, '.', ''); ?>">
				<span class="admin-threshold-track__fill admin-threshold-track__fill--<?php echo html_escape($m['warning_level']); ?>" id="admThresholdFill" style="width: <?php echo number_format($level_percent, 2, '.', ''); ?>%"></span>
				<i class="admin-threshold-track__marker" style="left: <?php echo number_format($yellow_percent, 2, '.', ''); ?>%" aria-hidden="true"></i>
				<i class="admin-threshold-track__marker admin-threshold-track__marker--red" style="left: 100%" aria-hidden="true"></i>
			</div>
			<div class="admin-live-threshold__scale"><span>0 m</span><span>Monitor <?php echo number_format($yellow, 2); ?> m</span><span>Critical <?php echo number_format($red, 2); ?> m</span></div>
		</div>
		<ul class="admin-stat-list admin-stat-list--live">
			<li><span>Trend</span><strong id="admTrend"><?php echo html_escape($m['trend_label']); ?></strong></li>
			<li><span>Rate of rise</span><strong id="admRate"><?php echo ($m['rate_cm_min'] > 0 ? '+' : '') . number_format($m['rate_cm_min'], 2); ?> cm/min</strong></li>
			<li><span>Est. time-to-threshold</span><strong id="admEtt"><?php echo html_escape($m['ett_label']); ?></strong></li>
			<li><span>Last sensor update</span><strong id="admUpdated"><?php echo html_escape($m['last_updated']); ?></strong></li>
		</ul>
	</section>
	<section class="glass-card admin-live-hardware">
		<h2>Field hardware</h2>
		<ul class="admin-device-list">
			<?php foreach (array('esp32', 'ultrasonic', 'internet', 'power', 'solar', 'battery') as $key): ?>
				<?php $d = $i[$key]; ?>
				<li>
					<div>
						<strong><?php echo html_escape($d['label']); ?></strong>
						<span<?php echo $key === 'internet' ? ' id="admInternetDetail"' : ''; ?>><?php echo html_escape($d['detail']); ?></span>
					</div>
					<span class="admin-pill admin-pill--<?php echo html_escape($d['status']); ?>"<?php echo ($key === 'esp32' || $key === 'ultrasonic') ? ' data-adm-sensor-pill="' . html_escape($key) . '"' : (($key === 'internet') ? ' id="admInternetPill"' : ''); ?>><?php echo html_escape(ucfirst($d['status'])); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<section class="glass-card admin-live-weather">
		<h2>Supplementary weather</h2>
		<p class="admin-muted">Weather is supplementary; flood warning thresholds use water level only.</p>
		<dl class="dash-dl admin-weather-grid">
			<div><dt>Condition</dt><dd><?php echo html_escape($weather['condition']); ?></dd></div>
			<div><dt>Temperature</dt><dd><?php echo (int) $weather['temp_c']; ?> °C</dd></div>
			<div><dt>Humidity</dt><dd><?php echo (int) $weather['humidity']; ?>%</dd></div>
			<div><dt>Rainfall</dt><dd><?php echo number_format($weather['rainfall_mm'], 1); ?> mm</dd></div>
		</dl>
	</section>
</div>

<section class="glass-card admin-live-chart">
	<div class="admin-live-chart__head">
		<div><h2>Water level history</h2><p class="admin-muted">Recorded station readings from the past 60 minutes, with warning thresholds shown.</p></div>
		<span class="admin-chart-period">Past hour</span>
	</div>
	<canvas id="adminWaterChart" data-window-seconds="3600" height="210" aria-label="Water level history for the past 60 minutes"<?php echo $has_chart_points ? '' : ' hidden'; ?>></canvas>
	<p class="admin-live-chart__empty admin-muted" id="admChartEmpty"<?php echo $has_chart_points ? ' hidden' : ''; ?>>No station readings are available for the past hour.</p>
</section>
</div>

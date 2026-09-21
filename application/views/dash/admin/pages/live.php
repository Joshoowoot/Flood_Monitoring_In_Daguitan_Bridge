<?php defined('BASEPATH') OR exit('No direct script access allowed');
$m = $monitor;
$i = $infra;
?>
<p class="admin-muted">Live values refresh every 5 seconds from the station API. <button class="btn btn--ghost btn--compact" type="button" id="admRefreshNow">Refresh now</button>
	<?php if ($m['warning_level'] !== 'green'): ?>
		· <a href="<?php echo site_url('admin/alerts'); ?>">Review flood alerts</a>
		· <a href="<?php echo site_url('admin/announcements'); ?>">Publish advisory</a>
	<?php endif; ?>
</p>
<div class="admin-live-grid">
	<section class="glass-card admin-live-hero">
		<p class="admin-kicker">Current reading</p>
		<p class="admin-live-level" id="admWater"><?php echo number_format($m['water_level_m'], 2); ?> <span>m</span></p>
		<p class="status-badge status-badge--<?php echo html_escape($m['warning_level']); ?>" id="admWarning"><?php echo html_escape($m['warning_label']); ?></p>
		<ul class="admin-stat-list">
			<li><span>Trend</span><strong id="admTrend"><?php echo html_escape($m['trend_label']); ?></strong></li>
			<li><span>Rate of rise</span><strong id="admRate"><?php echo ($m['rate_cm_min'] > 0 ? '+' : '') . number_format($m['rate_cm_min'], 2); ?> cm/min</strong></li>
			<li><span>Est. time-to-threshold</span><strong id="admEtt"><?php echo html_escape($m['ett_label']); ?></strong></li>
			<li><span>Last sensor update</span><strong id="admUpdated"><?php echo html_escape($m['last_updated']); ?></strong></li>
		</ul>
	</section>
	<section class="glass-card">
		<h2>Field hardware</h2>
		<ul class="admin-device-list">
			<?php foreach (array('esp32', 'ultrasonic', 'internet', 'power', 'solar', 'battery') as $key): ?>
				<?php $d = $i[$key]; ?>
				<li>
					<div>
						<strong><?php echo html_escape($d['label']); ?></strong>
						<span><?php echo html_escape($d['detail']); ?></span>
					</div>
					<span class="admin-pill admin-pill--<?php echo html_escape($d['status']); ?>"<?php echo ($key === 'esp32' || $key === 'ultrasonic') ? ' data-adm-sensor-pill="' . html_escape($key) . '"' : ''; ?>><?php echo html_escape(ucfirst($d['status'])); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<section class="glass-card">
		<h2>Supplementary weather</h2>
		<p class="admin-muted">Weather does not set the flood warning level. Threshold classification uses water level only.</p>
		<dl class="dash-dl">
			<div><dt>Condition</dt><dd><?php echo html_escape($weather['condition']); ?></dd></div>
			<div><dt>Temperature</dt><dd><?php echo (int) $weather['temp_c']; ?> °C</dd></div>
			<div><dt>Humidity</dt><dd><?php echo (int) $weather['humidity']; ?>%</dd></div>
			<div><dt>Rainfall</dt><dd><?php echo number_format($weather['rainfall_mm'], 1); ?> mm</dd></div>
		</dl>
	</section>
</div>

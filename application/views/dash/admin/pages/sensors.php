<?php defined('BASEPATH') OR exit('No direct script access allowed');
$i = $infra;
$m = $monitor;
$sensor_groups = array(
	'Bridge station' => array('esp32' => 'ESP32', 'ultrasonic' => 'JSN-SR04T'),
	'Connectivity and power' => array('internet' => 'Internet', 'power' => 'Power supply', 'solar' => 'Solar panel', 'battery' => 'Battery'),
);
$sensor_count = 0;
$online_count = 0;
foreach ($sensor_groups as $group_sensors)
{
	foreach ($group_sensors as $key => $title)
	{
		$sensor_count++;
		if (isset($i[$key]['status']) && $i[$key]['status'] === 'online')
		{
			$online_count++;
		}
	}
}
?>
<div class="admin-sensors-page">
<section class="glass-card admin-sensor-overview">
	<div class="admin-sensor-overview__copy">
		<p class="admin-kicker">Monitoring station</p>
		<h2>Station health</h2>
		<p class="admin-muted">Last successful reading: <strong><?php echo html_escape($i['last_reading']); ?></strong><?php if ($i['last_error']): ?> · Last issue: <?php echo html_escape($i['last_error']); ?><?php endif; ?></p>
	</div>
	<div class="admin-sensor-overview__totals" aria-label="Hardware status summary">
		<span><strong id="admSensorOnlineCount"><?php echo $online_count; ?></strong> online</span>
		<span><strong id="admSensorAttentionCount"><?php echo $sensor_count - $online_count; ?></strong> need attention</span>
	</div>
</section>

<div class="admin-sensor-groups">
	<?php foreach ($sensor_groups as $group_title => $group_sensors): ?>
	<section class="admin-sensor-group" aria-label="<?php echo html_escape($group_title); ?>">
		<header><h2><?php echo html_escape($group_title); ?></h2><span><?php echo count($group_sensors); ?> components</span></header>
		<div class="admin-sensor-grid">
		<?php foreach ($group_sensors as $key => $title): ?>
			<?php $d = $i[$key]; ?>
			<article class="glass-card admin-sensor-card admin-sensor-card--<?php echo html_escape($d['status']); ?>" data-infra-key="<?php echo html_escape($key); ?>">
				<h3><?php echo html_escape($title); ?></h3>
				<p class="admin-sensor-status admin-sensor-status--<?php echo html_escape($d['status']); ?>"><?php echo html_escape(ucfirst($d['status'])); ?></p>
				<p><?php echo html_escape($d['detail']); ?></p>
				<?php if ($key === 'battery' && isset($d['pct'])): ?>
					<div class="gauge gauge--green"><div class="gauge__fill" style="width:<?php echo (int) $d['pct']; ?>%"></div></div>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
		</div>
	</section>
	<?php endforeach; ?>
</div>

<section class="glass-card admin-sensor-summary">
	<div class="admin-sensor-summary__head">
		<p class="admin-kicker">Data pipeline</p>
		<h2>Connection summary</h2>
	</div>
	<div class="admin-sensor-summary__content">
	<dl class="dash-dl">
		<div><dt>Telemetry source</dt><dd><?php echo html_escape($m['source']); ?></dd></div>
		<div><dt>Packet age</dt><dd id="admPacketAge"><?php echo $m['age_seconds'] !== NULL ? (int) $m['age_seconds'] . ' s' : '—'; ?></dd></div>
		<div><dt>Cloud sync</dt><dd id="admCloudSync"><?php echo html_escape($sync['internet_label']); ?></dd></div>
		<div><dt>Pending upload</dt><dd><?php echo (int) $sync['pending_total']; ?> row(s)</dd></div>
	</dl>
	<div class="admin-sensor-summary__actions">
		<form method="post" action="<?php echo site_url('admin/sync'); ?>">
			<input type="hidden" name="return_to" value="<?php echo html_escape(current_url()); ?>">
			<button class="btn btn--primary" type="submit">Force cloud sync now</button>
		</form>
		<p class="admin-muted"><a href="<?php echo site_url('admin/live'); ?>">Open live monitoring</a><a href="<?php echo site_url('admin/settings'); ?>">Offline window settings</a></p>
	</div>
	</div>
</section>
</div>

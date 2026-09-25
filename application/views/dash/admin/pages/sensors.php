<?php defined('BASEPATH') OR exit('No direct script access allowed');
$i = $infra;
$m = $monitor;
?>
<section class="glass-card">
	<h2>Station health</h2>
	<p class="admin-muted">Last successful reading: <strong><?php echo html_escape($i['last_reading']); ?></strong>
		<?php if ($i['last_error']): ?> · Last issue: <?php echo html_escape($i['last_error']); ?><?php endif; ?>
	</p>
</section>

<div class="admin-sensor-grid">
	<?php foreach (array('esp32' => 'ESP32', 'ultrasonic' => 'JSN-SR04T', 'internet' => 'Internet', 'power' => 'Power supply', 'solar' => 'Solar panel', 'battery' => 'Battery') as $key => $title): ?>
		<?php $d = $i[$key]; ?>
		<article class="glass-card admin-sensor-card" data-infra-key="<?php echo html_escape($key); ?>">
			<h3><?php echo html_escape($title); ?></h3>
			<p class="admin-sensor-status admin-sensor-status--<?php echo html_escape($d['status']); ?>"><?php echo html_escape(ucfirst($d['status'])); ?></p>
			<p><?php echo html_escape($d['detail']); ?></p>
			<?php if ($key === 'battery' && isset($d['pct'])): ?>
				<div class="gauge gauge--green"><div class="gauge__fill" style="width:<?php echo (int) $d['pct']; ?>%"></div></div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>

<section class="glass-card">
	<h2>Connection summary</h2>
	<dl class="dash-dl">
		<div><dt>Telemetry source</dt><dd><?php echo html_escape($m['source']); ?></dd></div>
		<div><dt>Packet age</dt><dd id="admPacketAge"><?php echo $m['age_seconds'] !== NULL ? (int) $m['age_seconds'] . ' s' : '—'; ?></dd></div>
		<div><dt>Cloud sync</dt><dd id="admCloudSync"><?php echo html_escape($sync['internet_label']); ?></dd></div>
		<div><dt>Pending upload</dt><dd><?php echo (int) $sync['pending_total']; ?> row(s)</dd></div>
	</dl>
	<form method="post" action="<?php echo site_url('admin/sync'); ?>">
		<input type="hidden" name="return_to" value="<?php echo html_escape(current_url()); ?>">
		<button class="btn btn--primary" type="submit">Force cloud sync now</button>
	</form>
	<p class="admin-muted"><a href="<?php echo site_url('admin/live'); ?>">Open live monitoring</a> · <a href="<?php echo site_url('admin/settings'); ?>">Offline window settings</a></p>
</section>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
$s = $settings;
?>
<form method="post" action="<?php echo site_url('admin/settings'); ?>" class="admin-settings-grid">
	<section class="glass-card">
		<h2>Flood warning thresholds</h2>
		<p class="admin-muted">Warning levels are derived from these water-level values only.</p>
		<label>Yellow advisory (m) <input type="number" step="0.01" min="0" name="monitor_threshold_yellow_m" value="<?php echo html_escape($s['monitor_threshold_yellow_m']); ?>" required></label>
		<label>Red critical (m) <input type="number" step="0.01" min="0" name="monitor_threshold_red_m" value="<?php echo html_escape($s['monitor_threshold_red_m']); ?>" required></label>
	</section>
	<section class="glass-card">
		<h2>Monitoring configuration</h2>
		<label>Sensor height (cm) <input type="number" min="1" name="monitor_sensor_height_cm" value="<?php echo (int) $s['monitor_sensor_height_cm']; ?>" required></label>
		<label>Offline after (seconds) <input type="number" min="30" name="monitor_offline_after" value="<?php echo (int) $s['monitor_offline_after']; ?>" required></label>
		<p class="admin-muted mono">Ingest URL: <?php echo html_escape($ingest_url); ?></p>
	</section>
	<section class="glass-card">
		<h2>Notifications</h2>
		<label class="admin-check"><input type="checkbox" name="notify_residents_push" value="1"<?php echo ! empty($s['notify_residents_push']) ? ' checked' : ''; ?>> Resident push notifications</label>
		<label class="admin-check"><input type="checkbox" name="notify_residents_email" value="1"<?php echo ! empty($s['notify_residents_email']) ? ' checked' : ''; ?>> Resident email (when configured)</label>
		<label class="admin-check"><input type="checkbox" name="notify_admin_on_red" value="1"<?php echo ! empty($s['notify_admin_on_red']) ? ' checked' : ''; ?>> Alert admins on critical threshold</label>
		<label class="admin-check"><input type="checkbox" name="notify_admin_on_offline" value="1"<?php echo ! empty($s['notify_admin_on_offline']) ? ' checked' : ''; ?>> Alert admins when sensor offline</label>
	</section>
	<section class="glass-card">
		<h2>Admin account</h2>
		<dl class="dash-dl">
			<div><dt>Signed in as</dt><dd><?php echo html_escape($auth_name); ?> (<?php echo html_escape($auth_user); ?>)</dd></div>
		</dl>
		<p class="admin-muted">Password changes are managed through the database or seed accounts.</p>
	</section>
	<div class="admin-settings-actions">
		<button class="btn btn--primary" type="submit">Save settings</button>
	</div>
</form>

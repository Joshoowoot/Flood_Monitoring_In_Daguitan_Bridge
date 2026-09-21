<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="admin-filter-bar glass-card">
	<form method="post" action="<?php echo site_url('admin/alert_acknowledge_all'); ?>" onsubmit="return confirm('Acknowledge all active alerts?');">
		<button class="btn btn--primary" type="submit"<?php echo empty($alerts['active']) ? ' disabled' : ''; ?>>Acknowledge all active</button>
	</form>
	<p class="admin-muted">Alerts are created from threshold levels and sensor offline status—not AI predictions. <a href="<?php echo site_url('admin/live'); ?>">Live monitoring</a> · <a href="<?php echo site_url('admin/history?warning=red'); ?>">Critical history</a></p>
</div>

<div class="admin-split">
	<section class="glass-card">
		<h2>Active alerts (<?php echo count($alerts['active']); ?>)</h2>
		<?php if (empty($alerts['active'])): ?>
			<p>No active threshold alerts.</p>
		<?php else: ?>
			<ul class="admin-alert-list">
				<?php foreach ($alerts['active'] as $alert): ?>
					<li class="admin-alert admin-alert--<?php echo html_escape($alert['warning_level']); ?>">
						<div>
							<strong><?php echo html_escape($alert['title']); ?></strong>
							<p><?php echo html_escape($alert['body']); ?></p>
							<p class="admin-muted"><?php echo html_escape(date('M j, Y g:i A', strtotime($alert['triggered_at']))); ?> · <?php echo number_format($alert['water_level_m'], 2); ?> m · <?php echo html_escape(ucfirst($alert['warning_level'])); ?></p>
						</div>
						<a class="btn btn--primary btn--compact" href="<?php echo site_url('admin/alert_acknowledge/' . (int) $alert['id']); ?>">Acknowledge</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<section class="glass-card dash-table-wrap">
		<h2>Alert history</h2>
		<?php if (empty($alerts['all'])): ?>
			<p>No alerts recorded yet. They appear when water crosses yellow/red thresholds or the sensor goes offline.</p>
		<?php else: ?>
		<table class="dash-table">
			<thead><tr><th>When</th><th>Title</th><th>Level</th><th>Water</th><th>Status</th><th>By</th></tr></thead>
			<tbody>
				<?php foreach ($alerts['all'] as $alert): ?>
					<tr>
						<td><?php echo html_escape(date('M j, g:i A', strtotime($alert['triggered_at']))); ?></td>
						<td><?php echo html_escape($alert['title']); ?></td>
						<td><span class="status-badge status-badge--<?php echo html_escape($alert['warning_level']); ?>"><?php echo html_escape(ucfirst($alert['warning_level'])); ?></span></td>
						<td><?php echo number_format($alert['water_level_m'], 2); ?> m</td>
						<td><?php echo html_escape($alert['status']); ?></td>
						<td><?php echo $alert['acknowledged_by'] ? html_escape($alert['acknowledged_by']) : '—'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</section>
</div>

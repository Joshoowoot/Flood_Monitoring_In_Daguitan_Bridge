<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="admin-alerts-page">
<section class="glass-card admin-alerts-toolbar">
	<div class="admin-alerts-toolbar__copy">
		<p class="admin-kicker">MDRRMO response actions</p>
		<h2>Review and manage flood alerts</h2>
		<p class="admin-muted">Review severity, water-level readings, and acknowledgement status in one place.</p>
	</div>
	<div class="admin-alerts-toolbar__actions">
	<form method="post" action="<?php echo site_url('admin/alert_acknowledge_all'); ?>" onsubmit="return confirm('Acknowledge all active alerts?');">
		<button class="btn btn--primary" type="submit"<?php echo empty($alerts['active']) ? ' disabled' : ''; ?>>Acknowledge all active</button>
	</form>
	<a class="btn btn--ghost" href="<?php echo site_url('admin/live'); ?>">Live monitoring</a>
	<a class="btn btn--ghost" href="<?php echo site_url('admin/history?warning=red'); ?>">Critical history</a>
	</div>
</section>

<div class="admin-split">
	<section class="glass-card admin-alerts-active-panel">
		<div class="admin-alerts-panel-head">
			<div class="admin-alerts-panel-title">
				<p class="admin-kicker">Needs review</p>
				<h2>Active alerts</h2>
			</div>
			<span class="admin-alert-count"><?php echo number_format(count($alerts['active'])); ?> <span>active</span></span>
			<label class="admin-alert-sort">Sort
				<select data-alert-sort="activeAlertsList">
					<option value="newest">Newest</option>
					<option value="oldest">Oldest</option>
					<option value="severity">Severity</option>
				</select>
			</label>
		</div>
		<?php if (empty($alerts['active'])): ?>
			<div class="admin-alerts-empty"><strong>All clear</strong><p>There are no active alerts that need review.</p></div>
		<?php else: ?>
			<ul class="admin-alert-list" id="activeAlertsList">
				<?php foreach ($alerts['active'] as $alert): ?>
					<li class="admin-alert admin-alert--<?php echo html_escape($alert['warning_level']); ?>" data-alert-time="<?php echo (int) strtotime($alert['triggered_at']); ?>" data-alert-level="<?php echo html_escape($alert['warning_level']); ?>">
						<div>
							<div class="admin-alert__title"><strong><?php echo html_escape($alert['title']); ?></strong><span class="status-badge status-badge--<?php echo html_escape($alert['warning_level']); ?>"><?php echo html_escape(ucfirst($alert['warning_level'])); ?></span></div>
							<p><?php echo html_escape($alert['body']); ?></p>
							<p class="admin-muted"><time datetime="<?php echo html_escape(date(DATE_ATOM, strtotime($alert['triggered_at']))); ?>"><?php echo html_escape(date('M j, Y g:i A', strtotime($alert['triggered_at']))); ?></time><span><?php echo number_format($alert['water_level_m'], 2); ?> m water level</span></p>
						</div>
						<a class="btn btn--primary btn--compact" href="<?php echo site_url('admin/alert_acknowledge/' . (int) $alert['id']); ?>" aria-label="Acknowledge <?php echo html_escape($alert['title']); ?> alert">Acknowledge</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
	<section class="glass-card dash-table-wrap admin-alert-history-panel">
		<div class="admin-alerts-panel-head">
			<div class="admin-alerts-panel-title">
				<p class="admin-kicker">Recorded events</p>
				<h2>Alert history</h2>
			</div>
			<span class="admin-alert-count"><?php echo number_format(count($alerts['all'])); ?> <span>records</span></span>
			<label class="admin-alert-sort">Sort
				<select data-alert-sort="alertHistoryRows">
					<option value="newest">Newest</option>
					<option value="oldest">Oldest</option>
					<option value="severity">Severity</option>
				</select>
			</label>
		</div>
		<?php if (empty($alerts['all'])): ?>
			<div class="admin-alerts-empty"><strong>No alert history yet</strong><p>Alerts will appear here when water crosses a warning threshold or the sensor goes offline.</p></div>
		<?php else: ?>
		<div class="admin-alert-history-scroll">
		<table class="dash-table">
			<thead><tr><th>When</th><th>Title</th><th>Level</th><th>Water</th><th>Status</th><th>By</th></tr></thead>
			<tbody id="alertHistoryRows">
				<?php foreach ($alerts['all'] as $alert): ?>
					<tr data-alert-time="<?php echo (int) strtotime($alert['triggered_at']); ?>" data-alert-level="<?php echo html_escape($alert['warning_level']); ?>">
						<td><time datetime="<?php echo html_escape(date(DATE_ATOM, strtotime($alert['triggered_at']))); ?>"><?php echo html_escape(date('M j, g:i A', strtotime($alert['triggered_at']))); ?></time></td>
						<td><?php echo html_escape($alert['title']); ?></td>
						<td><span class="status-badge status-badge--<?php echo html_escape($alert['warning_level']); ?>"><?php echo html_escape(ucfirst($alert['warning_level'])); ?></span></td>
						<td><?php echo number_format($alert['water_level_m'], 2); ?> m</td>
						<td><span class="admin-alert-status admin-alert-status--<?php echo html_escape($alert['status']); ?>"><?php echo html_escape(ucfirst($alert['status'])); ?></span></td>
						<td><?php echo $alert['acknowledged_by'] ? html_escape($alert['acknowledged_by']) : '—'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		</div>
		<?php endif; ?>
	</section>
</div>
</div>
<script>
(function () {
	var severityRank = { red: 0, yellow: 1, green: 2 };
	document.querySelectorAll('[data-alert-sort]').forEach(function (control) {
		control.addEventListener('change', function () {
			var list = document.getElementById(control.getAttribute('data-alert-sort'));
			if (!list) return;

			var items = Array.prototype.slice.call(list.children);
			items.sort(function (a, b) {
				var timeA = Number(a.getAttribute('data-alert-time')) || 0;
				var timeB = Number(b.getAttribute('data-alert-time')) || 0;
				if (control.value === 'severity') {
					var levelA = severityRank[a.getAttribute('data-alert-level')];
					var levelB = severityRank[b.getAttribute('data-alert-level')];
					var severityDifference = (typeof levelA === 'number' ? levelA : 3) - (typeof levelB === 'number' ? levelB : 3);
					if (severityDifference) return severityDifference;
				}
				return control.value === 'oldest' ? timeA - timeB : timeB - timeA;
			});
			items.forEach(function (item) { list.appendChild(item); });
		});
	});
})();
</script>

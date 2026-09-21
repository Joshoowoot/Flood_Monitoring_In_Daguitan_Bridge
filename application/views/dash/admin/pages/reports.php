<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="admin-filter-bar glass-card">
	<a class="btn btn--primary" href="<?php echo site_url('admin/reports_export'); ?>">Download CSV report</a>
	<button class="btn btn--ghost" type="button" onclick="window.print();">Print this page</button>
</div>

<section class="glass-card">
	<h2>Operations snapshot</h2>
	<p>Generated <?php echo html_escape(date('F j, Y g:i A')); ?> · Daguitan Bridge · <?php echo html_escape(isset($summary_label) ? $summary_label : 'Stored readings'); ?> · Threshold-based classification only.</p>
	<div class="admin-kpi-grid admin-kpi-grid--4">
		<article class="glass-card admin-kpi"><h3>Current level</h3><p class="metric"><?php echo number_format($monitor['water_level_m'], 2); ?> m</p></article>
		<article class="glass-card admin-kpi"><h3>Warning</h3><p class="metric status-badge status-badge--<?php echo html_escape($monitor['warning_level']); ?>"><?php echo html_escape($monitor['warning_label']); ?></p></article>
		<article class="glass-card admin-kpi"><h3>Stored readings</h3><p class="metric"><?php echo (int) $summary['readings']; ?></p></article>
		<article class="glass-card admin-kpi"><h3>Active alerts</h3><p class="metric"><?php echo count($alerts['active']); ?></p></article>
	</div>
</section>

<section class="glass-card dash-table-wrap">
	<h2>Recent readings (report extract)</h2>
	<table class="dash-table">
		<thead><tr><th>Time</th><th>Level</th><th>Warning</th><th>Rate</th><th>Est. TT</th></tr></thead>
		<tbody>
			<?php foreach ($history_rows as $row): ?>
				<tr>
					<td><?php echo html_escape(date('M j, g:i A', (int) $row['ts'])); ?></td>
					<td><?php echo number_format($row['water_level_m'], 3); ?> m</td>
					<td><?php echo html_escape($row['warning_label']); ?></td>
					<td><?php echo number_format($row['rate_cm_min'], 2); ?> cm/min</td>
					<td><?php echo html_escape($row['ett_label']); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>

<section class="glass-card dash-table-wrap">
	<h2>Recent alerts</h2>
	<table class="dash-table">
		<thead><tr><th>When</th><th>Title</th><th>Status</th></tr></thead>
		<tbody>
			<?php foreach ($alerts['all'] as $alert): ?>
				<tr>
					<td><?php echo html_escape(date('M j, g:i A', strtotime($alert['triggered_at']))); ?></td>
					<td><?php echo html_escape($alert['title']); ?></td>
					<td><?php echo html_escape($alert['status']); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>

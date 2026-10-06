<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="admin-reports-page">
<section class="glass-card admin-reports-actions">
	<div>
		<p class="admin-kicker">Report tools</p>
		<p class="admin-reports-actions__description">Export this snapshot or prepare a print-friendly copy.</p>
	</div>
	<div class="admin-reports-actions__buttons">
		<a class="btn btn--primary" href="<?php echo site_url('admin/reports_export'); ?>">Download CSV report</a>
		<button class="btn btn--ghost" type="button" onclick="window.print();">Print this page</button>
	</div>
</section>

<section class="glass-card admin-reports-snapshot">
	<header class="admin-reports-section-head">
		<div>
			<p class="admin-kicker">At a glance</p>
			<h2>Operations snapshot</h2>
		</div>
		<div class="admin-reports-meta">
			<span>Generated <?php echo html_escape(date('F j, Y g:i A')); ?></span>
			<span><?php echo html_escape(isset($summary_label) ? $summary_label : 'Stored readings'); ?></span>
			<span>Daguitan Bridge</span>
		</div>
	</header>
	<p class="admin-reports-note"><span aria-hidden="true"></span> Threshold-based classification only</p>
	<div class="admin-kpi-grid admin-kpi-grid--4">
		<article class="glass-card admin-kpi admin-reports-kpi--level">
			<h3>Current level</h3>
			<p class="metric"><?php echo number_format($monitor['water_level_m'], 2); ?> <small>m</small></p>
			<p class="admin-reports-kpi-detail">Latest station reading</p>
		</article>
		<article class="glass-card admin-kpi admin-reports-kpi--warning">
			<h3>Warning status</h3>
			<p class="metric"><span class="status-badge status-badge--<?php echo html_escape($monitor['warning_level']); ?>"><?php echo html_escape($monitor['warning_label']); ?></span></p>
			<p class="admin-reports-kpi-detail">Threshold classification</p>
		</article>
		<article class="glass-card admin-kpi admin-reports-kpi--readings">
			<h3>Stored readings</h3>
			<p class="metric"><?php echo (int) $summary['readings']; ?></p>
			<p class="admin-reports-kpi-detail"><?php echo html_escape(isset($summary_label) ? $summary_label : 'Stored readings'); ?></p>
		</article>
		<article class="glass-card admin-kpi admin-reports-kpi--alerts<?php echo ! empty($alerts['active']) ? ' has-active' : ''; ?>">
			<h3>Active alerts</h3>
			<p class="metric"><?php echo count($alerts['active']); ?></p>
			<p class="admin-reports-kpi-detail"><?php echo ! empty($alerts['active']) ? 'Require attention' : 'No alerts require attention'; ?></p>
		</article>
	</div>
</section>

<section class="glass-card dash-table-wrap">
	<header class="admin-reports-section-head">
		<div>
			<p class="admin-kicker">Monitoring data</p>
			<h2>Recent readings <span>(report extract)</span></h2>
		</div>
		<div class="admin-reports-table-tools">
			<label class="admin-reports-sort-label" for="reportReadingSort">Sort by</label>
			<select class="admin-reports-sort" id="reportReadingSort" aria-label="Sort report readings">
				<option value="">Default</option>
				<option value="0:desc:date">Newest</option>
				<option value="0:asc:date">Oldest</option>
				<option value="1:desc:number">Highest level</option>
				<option value="1:asc:number">Lowest level</option>
				<option value="3:desc:number">Fastest</option>
				<option value="3:asc:number">Slowest</option>
			</select>
			<span class="admin-reports-count"><?php echo count($history_rows); ?> <?php echo count($history_rows) === 1 ? 'reading' : 'readings'; ?></span>
		</div>
	</header>
	<?php if (empty($history_rows)): ?>
		<p class="admin-reports-empty">No readings are available for this report period.</p>
	<?php else: ?>
	<div class="admin-table-scroll">
		<table class="dash-table">
			<thead><tr><th>Time</th><th>Level</th><th>Warning</th><th>Rate</th><th>Est. TT</th></tr></thead>
			<tbody>
				<?php foreach ($history_rows as $row): ?>
					<tr>
						<td data-sort-value="<?php echo (int) $row['ts']; ?>"><?php echo html_escape(date('M j, g:i A', (int) $row['ts'])); ?></td>
						<td data-sort-value="<?php echo (float) $row['water_level_m']; ?>"><?php echo number_format($row['water_level_m'], 3); ?> m</td>
						<td><span class="status-badge status-badge--<?php echo in_array($row['warning_level'], array('green', 'yellow', 'red'), TRUE) ? html_escape($row['warning_level']) : 'yellow'; ?>"><?php echo html_escape($row['warning_label']); ?></span></td>
						<td data-sort-value="<?php echo (float) $row['rate_cm_min']; ?>"><?php echo number_format($row['rate_cm_min'], 2); ?> cm/min</td>
						<td><?php echo html_escape($row['ett_label']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</section>

<section class="glass-card dash-table-wrap">
	<header class="admin-reports-section-head">
		<div>
			<p class="admin-kicker">Alert log</p>
			<h2>Recent alerts</h2>
		</div>
		<div class="admin-reports-table-tools">
			<label class="admin-reports-sort-label" for="reportAlertSort">Sort by</label>
			<select class="admin-reports-sort" id="reportAlertSort" aria-label="Sort report alerts">
				<option value="">Default</option>
				<option value="0:desc:date">Newest</option>
				<option value="0:asc:date">Oldest</option>
			</select>
			<span class="admin-reports-count"><?php echo count($alerts['active']); ?> active <span aria-hidden="true">·</span> <?php echo count($alerts['all']); ?> total</span>
		</div>
	</header>
	<?php if (empty($alerts['all'])): ?>
		<p class="admin-reports-empty">No alerts are available to display.</p>
	<?php else: ?>
	<div class="admin-table-scroll">
		<table class="dash-table" data-sort-visible-limit="5">
			<thead><tr><th>When</th><th>Title</th><th>Status</th></tr></thead>
			<tbody id="reportAlertRows">
				<?php foreach ($alerts['all'] as $alert): ?>
					<tr>
						<td data-sort-value="<?php echo (int) strtotime($alert['triggered_at']); ?>"><?php echo html_escape(date('M j, g:i A', strtotime($alert['triggered_at']))); ?></td>
						<td><?php echo html_escape($alert['title']); ?></td>
						<td><span class="admin-reports-alert-status admin-reports-alert-status--<?php echo $alert['status'] === 'active' ? 'active' : 'acknowledged'; ?>"><?php echo html_escape(ucfirst($alert['status'])); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<button class="admin-reports-show-more" type="button" data-sort-toggle aria-controls="reportAlertRows" aria-expanded="false" hidden></button>
	<?php endif; ?>
</section>
</div>

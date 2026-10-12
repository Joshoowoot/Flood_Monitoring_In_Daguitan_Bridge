<?php defined('BASEPATH') OR exit('No direct script access allowed');
$q = http_build_query(array_filter(array(
	'warning'   => isset($filters['warning']) ? $filters['warning'] : '',
	'date_from' => isset($filter_date_from) ? $filter_date_from : '',
	'date_to'   => isset($filter_date_to) ? $filter_date_to : '',
)));
?>
<div class="admin-history-page">
<section class="glass-card admin-filter-bar">
	<div class="admin-history-filter-head">
		<div>
			<p class="admin-kicker">Find station readings</p>
			<h2>Filter history</h2>
		</div>
		<p class="admin-muted">Choose a date range and warning level, or start with a quick filter.</p>
	</div>
	<div class="admin-quick-links__grid admin-quick-links__grid--tight">
		<span class="admin-history-presets-label">Quick ranges</span>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/history?date_from=' . date('Y-m-d') . '&date_to=' . date('Y-m-d')); ?>">Today</a>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/history?date_from=' . date('Y-m-d', strtotime('-7 days')) . '&date_to=' . date('Y-m-d')); ?>">Last 7 days</a>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/history?warning=red'); ?>">Critical only</a>
	</div>
	<form method="get" action="<?php echo site_url('admin/history'); ?>" class="admin-filters" aria-label="Filter monitoring history">
		<label>From <input type="date" name="date_from" value="<?php echo ! empty($filters['date_from']) ? html_escape(date('Y-m-d', $filters['date_from'])) : ''; ?>"></label>
		<label>To <input type="date" name="date_to" value="<?php echo ! empty($filters['date_to']) ? html_escape(date('Y-m-d', $filters['date_to'])) : ''; ?>"></label>
		<label>Warning
			<select name="warning">
				<option value="">All</option>
				<option value="green"<?php echo (isset($filters['warning']) && $filters['warning'] === 'green') ? ' selected' : ''; ?>>Safe</option>
				<option value="yellow"<?php echo (isset($filters['warning']) && $filters['warning'] === 'yellow') ? ' selected' : ''; ?>>Monitor</option>
				<option value="red"<?php echo (isset($filters['warning']) && $filters['warning'] === 'red') ? ' selected' : ''; ?>>Critical</option>
			</select>
		</label>
		<div class="admin-history-actions">
			<button class="btn btn--primary" type="submit">Apply filters</button>
			<a class="btn btn--ghost" href="<?php echo site_url('admin/history'); ?>">Clear</a>
			<a class="btn btn--ghost" href="<?php echo site_url('admin/history_export' . ($q ? '?' . $q : '')); ?>">Download CSV</a>
		</div>
	</form>
</section>

<section class="glass-card admin-history-chart" aria-label="Water level reading trends">
	<div class="admin-history-chart__head">
		<div>
			<p class="admin-kicker">Warning-level trends</p>
			<h2>Water level over time</h2>
			<p class="admin-muted">Compare average station readings by warning category.</p>
		</div>
		<div class="admin-history-periods" role="group" aria-label="Chart time period">
			<button class="is-active" type="button" data-history-period="week" aria-pressed="true">Week</button>
			<button type="button" data-history-period="month" aria-pressed="false">Month</button>
			<button type="button" data-history-period="year" aria-pressed="false">Year</button>
		</div>
	</div>
	<ul class="admin-history-chart-legend" aria-label="Warning categories">
		<li><span class="admin-history-chart-key admin-history-chart-key--green"></span>Safe</li>
		<li><span class="admin-history-chart-key admin-history-chart-key--yellow"></span>Monitor</li>
		<li><span class="admin-history-chart-key admin-history-chart-key--red"></span>Critical</li>
	</ul>
	<div class="admin-history-chart__plot">
		<canvas id="adminHistoryChart" height="280" aria-label="Average water level by warning category for the past 7 days"></canvas>
		<p class="admin-history-chart__empty admin-muted" id="adminHistoryChartEmpty" hidden>No readings are available for this period.</p>
	</div>
	<p class="admin-history-chart__note">Each line shows the average water level for readings in that warning category. Thresholds: Monitor <?php echo number_format($history_chart['yellow'], 2); ?> m · Critical <?php echo number_format($history_chart['red'], 2); ?> m.</p>
</section>

<section class="glass-card dash-table-wrap admin-history-table">
	<div class="admin-history-table__head">
		<div>
			<p class="admin-kicker">Station records</p>
			<h2>Reading history</h2>
		</div>
		<span class="admin-history-result-count"><?php echo (int) $history_count; ?> <span><?php echo (int) $history_count === 1 ? 'reading shown' : 'readings shown'; ?></span></span>
	</div>
	<?php if (empty($history_rows)): ?>
		<div class="admin-history-empty">
			<strong>No readings found</strong>
			<p>No station readings match these filters. Try a wider date range or <a href="<?php echo site_url('admin/history'); ?>">clear all filters</a>.</p>
		</div>
	<?php else: ?>
	<div class="admin-table-scroll">
		<table class="dash-table">
			<thead>
				<tr>
					<th>Date &amp; time</th>
					<th>Water level</th>
					<th>Trend</th>
					<th>Rate</th>
					<th>Est. TT</th>
					<th>Warning</th>
					<th>Sensor</th>
					<th>Sync</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($history_rows as $row): ?>
					<tr>
						<td><?php echo html_escape(date('M j, Y g:i A', (int) $row['ts'])); ?></td>
						<td><?php echo number_format($row['water_level_m'], 3); ?> m</td>
						<td><?php echo html_escape($row['trend_label']); ?></td>
						<td><?php echo number_format($row['rate_cm_min'], 2); ?> cm/min</td>
						<td><?php echo html_escape($row['ett_label']); ?></td>
						<td><span class="status-badge status-badge--<?php echo html_escape($row['warning_level']); ?>"><?php echo html_escape($row['warning_label']); ?></span></td>
						<td><span class="admin-pill admin-pill--<?php echo html_escape($row['sensor_status']); ?>"><?php echo html_escape($row['sensor_label']); ?></span></td>
						<td><span class="sync-pill sync-pill--<?php echo html_escape($row['sync_status']); ?>"><?php echo html_escape($row['sync_status']); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</section>
</div>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
$q = http_build_query(array_filter(array(
	'warning'   => isset($filters['warning']) ? $filters['warning'] : '',
	'date_from' => isset($filter_date_from) ? $filter_date_from : '',
	'date_to'   => isset($filter_date_to) ? $filter_date_to : '',
)));
?>
<section class="glass-card admin-filter-bar">
	<div class="admin-quick-links__grid admin-quick-links__grid--tight">
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/history?date_from=' . date('Y-m-d') . '&date_to=' . date('Y-m-d')); ?>">Today</a>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/history?date_from=' . date('Y-m-d', strtotime('-7 days')) . '&date_to=' . date('Y-m-d')); ?>">Last 7 days</a>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/history?warning=red'); ?>">Critical only</a>
	</div>
	<form method="get" action="<?php echo site_url('admin/history'); ?>" class="admin-filters">
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
		<button class="btn btn--primary" type="submit">Apply filters</button>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/history'); ?>">Clear</a>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/history_export' . ($q ? '?' . $q : '')); ?>">Download CSV</a>
	</form>
	<p class="admin-muted"><?php echo (int) $history_count; ?> reading(s) shown (max 500 per load).</p>
</section>

<section class="glass-card dash-table-wrap">
	<h2>Reading history</h2>
	<?php if (empty($history_rows)): ?>
		<p>No readings match your filters. Adjust dates or <a href="<?php echo site_url('admin/history'); ?>">clear filters</a>.</p>
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

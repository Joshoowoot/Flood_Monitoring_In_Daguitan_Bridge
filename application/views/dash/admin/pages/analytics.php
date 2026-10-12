<?php defined('BASEPATH') OR exit('No direct script access allowed');
$days = isset($analytics_days) ? (int) $analytics_days : 30;
$chart_points = isset($chart['points']) && is_array($chart['points']) ? $chart['points'] : array();
$chart_start = $days > 0 ? time() - ($days * 86400) : (! empty($chart_points) ? (int) $chart_points[0]['ts'] : time() - 3600);
$chart_window = max(3600, time() - $chart_start);
$range_label = $days > 0 ? 'Last ' . $days . ' days' : 'All stored data';
?>
<div class="admin-analytics-page">
<section class="glass-card admin-filter-bar admin-analytics-range">
	<div class="admin-analytics-range__copy">
		<div><p class="admin-kicker">Reporting period</p><h2><?php echo html_escape($range_label); ?></h2></div>
		<p class="admin-muted">Choose a time range to update the summary, chart, and warning counts.</p>
	</div>
	<div class="admin-quick-links__grid">
		<a class="btn btn--ghost<?php echo $days === 7 ? ' is-active-tab' : ''; ?>"<?php echo $days === 7 ? ' aria-current="page"' : ''; ?> href="<?php echo site_url('admin/analytics?days=7'); ?>">Last 7 days</a>
		<a class="btn btn--ghost<?php echo $days === 30 ? ' is-active-tab' : ''; ?>"<?php echo $days === 30 ? ' aria-current="page"' : ''; ?> href="<?php echo site_url('admin/analytics?days=30'); ?>">Last 30 days</a>
		<a class="btn btn--ghost<?php echo $days === 90 ? ' is-active-tab' : ''; ?>"<?php echo $days === 90 ? ' aria-current="page"' : ''; ?> href="<?php echo site_url('admin/analytics?days=90'); ?>">Last 90 days</a>
		<a class="btn btn--ghost<?php echo $days === 0 ? ' is-active-tab' : ''; ?>"<?php echo $days === 0 ? ' aria-current="page"' : ''; ?> href="<?php echo site_url('admin/analytics?days=0'); ?>">All stored data</a>
	</div>
</section>

<div class="admin-kpi-grid admin-kpi-grid--4">
	<article class="glass-card admin-kpi admin-analytics-kpi"><h3>Readings in range</h3><p class="metric"><?php echo number_format((int) $summary['readings']); ?></p><span class="admin-analytics-kpi__detail">stored station records</span></article>
	<article class="glass-card admin-kpi admin-analytics-kpi admin-analytics-kpi--max"><h3>Maximum level</h3><p class="metric"><?php echo number_format($summary['max_level'], 2); ?> <span>m</span></p><span class="admin-analytics-kpi__detail">highest reading in range</span></article>
	<article class="glass-card admin-kpi admin-analytics-kpi admin-analytics-kpi--min"><h3>Minimum level</h3><p class="metric"><?php echo number_format($summary['min_level'], 2); ?> <span>m</span></p><span class="admin-analytics-kpi__detail">lowest reading in range</span></article>
	<article class="glass-card admin-kpi admin-analytics-kpi admin-analytics-kpi--average"><h3>Average level</h3><p class="metric"><?php echo number_format($summary['avg_level'], 2); ?> <span>m</span></p><span class="admin-analytics-kpi__detail">mean water level</span></article>
</div>

<section class="glass-card admin-chart-card">
	<div class="admin-analytics-section-head">
		<div><p class="admin-kicker">Reading trend</p><h2>Water-level history</h2><p class="admin-muted">Recorded station readings for <?php echo html_escape(strtolower($range_label)); ?>. Threshold bands are shown for context.</p></div>
		<span class="admin-analytics-period"><?php echo html_escape($range_label); ?></span>
	</div>
	<div class="admin-chart-wrap">
		<canvas id="adminWaterChart" data-window-seconds="<?php echo (int) $chart_window; ?>" data-window-labels="analytics" height="260" aria-label="Water level history for <?php echo html_escape(strtolower($range_label)); ?>"<?php echo empty($chart_points) ? ' hidden' : ''; ?>></canvas>
		<p class="admin-chart-empty admin-muted" id="adminAnalyticsChartEmpty"<?php echo empty($chart_points) ? '' : ' hidden'; ?>>No readings are available for this reporting period.</p>
	</div>
</section>

<div class="admin-split">
	<section class="glass-card admin-analytics-warnings">
		<div class="admin-analytics-section-head"><div><p class="admin-kicker">By severity</p><h2>Warning-level counts</h2><p class="admin-muted">Readings grouped by the configured water-level thresholds.</p></div></div>
		<ul class="threshold-list">
			<li class="admin-analytics-warning--green"><span><i aria-hidden="true"></i>Safe</span><strong><?php echo number_format((int) $summary['warning_counts']['green']); ?></strong></li>
			<li class="admin-analytics-warning--yellow"><span><i aria-hidden="true"></i>Monitor</span><strong><?php echo number_format((int) $summary['warning_counts']['yellow']); ?></strong></li>
			<li class="admin-analytics-warning--red"><span><i aria-hidden="true"></i>Critical</span><strong><?php echo number_format((int) $summary['warning_counts']['red']); ?></strong></li>
		</ul>
	</section>
	<section class="glass-card dash-table-wrap admin-analytics-recent">
		<div class="admin-analytics-section-head"><div><p class="admin-kicker">Latest records</p><h2>Recent trend sample</h2><p class="admin-muted">Most recent station readings in this range.</p></div></div>
		<?php if (empty($history_rows)): ?>
			<p class="admin-analytics-empty">No readings are available for this reporting period.</p>
		<?php else: ?>
			<div class="admin-analytics-table-scroll">
				<table class="dash-table">
					<thead><tr><th>Time</th><th>Level</th><th>Rate</th><th>Est. TT</th><th>Warning</th></tr></thead>
					<tbody>
						<?php foreach ($history_rows as $row): ?>
							<tr>
								<td><?php echo html_escape(date('M j, g:i A', (int) $row['ts'])); ?></td>
								<td><?php echo number_format($row['water_level_m'], 3); ?> m</td>
								<td><?php echo number_format($row['rate_cm_min'], 2); ?> cm/min</td>
								<td><?php echo html_escape($row['ett_label']); ?></td>
								<td><span class="status-badge status-badge--<?php echo html_escape($row['warning_level']); ?>"><?php echo html_escape($row['warning_label']); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>
</div>
</div>

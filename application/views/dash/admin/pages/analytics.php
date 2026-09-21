<?php defined('BASEPATH') OR exit('No direct script access allowed');
$days = isset($analytics_days) ? (int) $analytics_days : 30;
?>
<div class="admin-filter-bar glass-card">
	<div class="admin-quick-links__grid">
		<a class="btn btn--ghost<?php echo $days === 7 ? ' is-active-tab' : ''; ?>" href="<?php echo site_url('admin/analytics?days=7'); ?>">Last 7 days</a>
		<a class="btn btn--ghost<?php echo $days === 30 ? ' is-active-tab' : ''; ?>" href="<?php echo site_url('admin/analytics?days=30'); ?>">Last 30 days</a>
		<a class="btn btn--ghost<?php echo $days === 90 ? ' is-active-tab' : ''; ?>" href="<?php echo site_url('admin/analytics?days=90'); ?>">Last 90 days</a>
		<a class="btn btn--ghost<?php echo $days === 0 ? ' is-active-tab' : ''; ?>" href="<?php echo site_url('admin/analytics?days=0'); ?>">All stored data</a>
	</div>
</div>

<div class="admin-kpi-grid admin-kpi-grid--4">
	<article class="glass-card admin-kpi"><h3>Readings in range</h3><p class="metric"><?php echo (int) $summary['readings']; ?></p></article>
	<article class="glass-card admin-kpi"><h3>Max level</h3><p class="metric"><?php echo number_format($summary['max_level'], 2); ?> m</p></article>
	<article class="glass-card admin-kpi"><h3>Min level</h3><p class="metric"><?php echo number_format($summary['min_level'], 2); ?> m</p></article>
	<article class="glass-card admin-kpi"><h3>Average</h3><p class="metric"><?php echo number_format($summary['avg_level'], 2); ?> m</p></article>
</div>

<section class="glass-card admin-chart-card">
	<h2>Water-level history <?php echo $days > 0 ? '(filtered)' : ''; ?></h2>
	<div class="admin-chart-wrap"><canvas id="adminWaterChart" height="260"></canvas></div>
</section>

<div class="admin-split">
	<section class="glass-card">
		<h2>Warning-level counts</h2>
		<ul class="threshold-list">
			<li><span>Safe (green)</span><strong><?php echo (int) $summary['warning_counts']['green']; ?></strong></li>
			<li><span>Monitor (yellow)</span><strong><?php echo (int) $summary['warning_counts']['yellow']; ?></strong></li>
			<li><span>Critical (red)</span><strong><?php echo (int) $summary['warning_counts']['red']; ?></strong></li>
		</ul>
	</section>
	<section class="glass-card dash-table-wrap">
		<h2>Recent trend sample</h2>
		<table class="dash-table">
			<thead><tr><th>Time</th><th>Level</th><th>Rate</th><th>Est. TT</th><th>Warning</th></tr></thead>
			<tbody>
				<?php foreach ($history_rows as $row): ?>
					<tr>
						<td><?php echo html_escape(date('M j, g:i A', (int) $row['ts'])); ?></td>
						<td><?php echo number_format($row['water_level_m'], 3); ?> m</td>
						<td><?php echo number_format($row['rate_cm_min'], 2); ?> cm/min</td>
						<td><?php echo html_escape($row['ett_label']); ?></td>
						<td><?php echo html_escape($row['warning_label']); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</div>

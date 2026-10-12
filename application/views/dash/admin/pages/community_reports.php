<?php defined('BASEPATH') OR exit('No direct script access allowed');
$reports = isset($reports) && is_array($reports) ? $reports : array();
$report_statuses = isset($report_statuses) && is_array($report_statuses) ? $report_statuses : array();
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
$status_counts = array_fill_keys(array_keys($report_statuses), 0);
foreach ($reports as $report)
{
	if (isset($report['status']) && isset($status_counts[$report['status']])) $status_counts[$report['status']]++;
}
?>
<div class="admin-community-reports">
	<div class="admin-community-reports__notice">
		<span class="admin-kicker">MDRRMO guidance</span>
		<strong>Community reports are not emergency calls.</strong>
		<span>Review submissions and share a concise follow-up residents can safely act on. For immediate danger, direct residents to the appropriate emergency service. Do not promise a response time unless confirmed.</span>
	</div>
	<?php if ( ! empty($report_error)): ?><p class="admin-community-reports__message admin-community-reports__message--error" role="alert"><?php echo html_escape($report_error); ?></p><?php endif; ?>
	<section class="admin-community-reports__overview" aria-label="Reports in current view">
		<div class="admin-community-reports__overview-head">
			<div>
				<p class="admin-kicker">Review queue</p>
				<h2>Reports in this view</h2>
			</div>
			<span class="admin-community-reports__total"><?php echo number_format(count($reports)); ?> <span><?php echo count($reports) === 1 ? 'report' : 'reports'; ?></span></span>
		</div>
		<div class="admin-community-reports__summary">
			<?php foreach ($report_statuses as $key => $label): ?>
				<div class="admin-community-reports__summary-item admin-community-reports__summary-item--<?php echo html_escape($key); ?>">
					<strong><?php echo number_format($status_counts[$key]); ?></strong>
					<span><?php echo html_escape($label); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<section class="admin-community-reports__filter-panel" aria-labelledby="communityReportFiltersTitle">
		<div class="admin-community-reports__filter-head">
			<div>
				<h2 id="communityReportFiltersTitle">Find reports</h2>
				<p>Filter the queue by review status or barangay.</p>
			</div>
		</div>
		<form class="admin-community-reports__filters" method="get" action="<?php echo site_url('admin/community-reports'); ?>">
			<label for="communityReportStatus">Status
				<select id="communityReportStatus" name="status">
					<option value="">All statuses</option>
					<?php foreach ($report_statuses as $key => $label): ?>
						<option value="<?php echo html_escape($key); ?>"<?php echo $status_filter === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label for="communityReportBarangay">Barangay
				<select id="communityReportBarangay" name="barangay">
					<option value="">All barangays</option>
					<?php foreach ($barangays as $key => $label): ?>
						<option value="<?php echo html_escape($key); ?>"<?php echo $barangay_filter === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div class="admin-community-reports__filter-actions">
				<button class="btn btn--primary btn--compact" type="submit">Apply filters</button>
				<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/community-reports'); ?>">Clear filters</a>
			</div>
		</form>
	</section>
	<?php if (empty($reports)): ?>
		<div class="admin-community-reports__empty">
			<strong>No reports found</strong>
			<p>No community reports match the selected filters. Try a different status or barangay.</p>
		</div>
	<?php else: ?>
		<div class="admin-community-reports__list">
			<?php foreach ($reports as $report): ?>
				<article class="admin-community-report admin-community-report--<?php echo html_escape($report['status']); ?>">
					<div class="admin-community-report__top">
						<div class="admin-community-report__heading">
							<div class="admin-community-report__eyebrow">
								<span class="admin-community-report__type"><?php echo html_escape($report['type_label']); ?></span>
								<span>Barangay <?php echo html_escape($report['barangay']); ?></span>
							</div>
							<h2><?php echo html_escape($report['landmark']); ?></h2>
						</div>
						<span class="admin-community-report__status admin-community-report__status--<?php echo html_escape($report['status']); ?>"><?php echo html_escape($report['status_label']); ?></span>
					</div>
					<div class="admin-community-report__details">
						<p class="admin-community-report__by"><span>Submitted by</span> <?php echo html_escape(! empty($report['reporter_name']) ? $report['reporter_name'] : $report['reporter_username']); ?> <span aria-hidden="true">·</span> <time datetime="<?php echo html_escape(date(DATE_ATOM, strtotime($report['created_at']))); ?>"><?php echo html_escape(date('M j, Y · g:i A', strtotime($report['created_at']))); ?></time></p>
						<p class="admin-community-report__description"><?php echo nl2br(html_escape($report['description'])); ?></p>
						<?php if (!empty($report['has_photo'])): ?><a class="admin-community-report__photo" href="<?php echo html_escape($report_photo_base . '/' . (int) $report['id']); ?>" target="_blank" rel="noopener">View attached photo <span class="admin-community-report__photo-note">(opens in new tab)</span></a><?php endif; ?>
					</div>
					<form class="admin-community-report__review" method="post" action="<?php echo site_url('admin/community-reports'); ?>">
						<input type="hidden" name="report_admin_token" value="<?php echo html_escape($report_token); ?>">
						<input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
						<div class="admin-community-report__review-heading">
							<h3>Administrator review</h3>
							<p>Update the report status and share a safe, useful follow-up with the resident.</p>
						</div>
						<label for="communityReportReviewStatus<?php echo (int) $report['id']; ?>">Update status
							<select id="communityReportReviewStatus<?php echo (int) $report['id']; ?>" name="status" required>
								<?php foreach ($report_statuses as $key => $label): ?>
									<option value="<?php echo html_escape($key); ?>"<?php echo $report['status'] === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label for="communityReportNote<?php echo (int) $report['id']; ?>">Follow-up note for resident
							<textarea id="communityReportNote<?php echo (int) $report['id']; ?>" name="admin_note" rows="3" maxlength="1000" placeholder="Optional: share a confirmed update or practical guidance."><?php echo html_escape(isset($report['admin_note']) ? $report['admin_note'] : ''); ?></textarea>
							<small>Up to 1,000 characters. Do not include private staff information or unconfirmed response times.</small>
						</label>
						<div class="admin-community-report__review-actions">
							<button class="btn btn--primary btn--compact" type="submit">Save review</button>
							<?php if (!empty($report['reviewed_by'])): ?><span class="admin-community-report__updated">Last updated by <?php echo html_escape($report['reviewed_by']); ?> · <?php echo html_escape(date('M j, Y · g:i A', strtotime($report['updated_at']))); ?></span><?php endif; ?>
						</div>
					</form>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

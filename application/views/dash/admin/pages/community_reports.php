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
		<div class="admin-community-reports__list" role="region" aria-label="Community reports. Scroll to see more reports." tabindex="0">
			<?php foreach ($reports as $report): ?>
				<article class="admin-community-report admin-community-report--<?php echo html_escape($report['status']); ?>">
					<button class="admin-community-report__open" type="button" aria-haspopup="dialog" aria-controls="communityReportDialog<?php echo (int) $report['id']; ?>">
						<span class="admin-community-report__top">
							<span class="admin-community-report__heading">
								<span class="admin-community-report__eyebrow">
									<span class="admin-community-report__type"><?php echo html_escape($report['type_label']); ?></span>
									<span>Barangay <?php echo html_escape($report['barangay']); ?></span>
								</span>
								<strong><?php echo html_escape($report['landmark']); ?></strong>
							</span>
							<span class="admin-community-report__status admin-community-report__status--<?php echo html_escape($report['status']); ?>"><?php echo html_escape($report['status_label']); ?></span>
						</span>
						<span class="admin-community-report__summary">
							<span><?php echo html_escape(! empty($report['reporter_name']) ? $report['reporter_name'] : $report['reporter_username']); ?></span>
							<span aria-hidden="true">·</span>
							<time datetime="<?php echo html_escape(date(DATE_ATOM, strtotime($report['created_at']))); ?>"><?php echo html_escape(date('M j, Y · g:i A', strtotime($report['created_at']))); ?></time>
						</span>
						<span class="admin-community-report__preview"><?php echo html_escape($report['description']); ?></span>
						<span class="admin-community-report__open-label">View report and review <span aria-hidden="true">→</span></span>
					</button>
					<dialog class="admin-community-report__dialog" id="communityReportDialog<?php echo (int) $report['id']; ?>" aria-labelledby="communityReportDialogTitle<?php echo (int) $report['id']; ?>">
						<div class="admin-community-report__dialog-head">
							<div>
								<p class="admin-kicker">Community report</p>
								<h2 id="communityReportDialogTitle<?php echo (int) $report['id']; ?>"><?php echo html_escape($report['landmark']); ?></h2>
							</div>
							<button class="admin-community-report__close" type="button" aria-label="Close report details">&times;</button>
						</div>
						<div class="admin-community-report__dialog-content">
							<div class="admin-community-report__top">
								<div class="admin-community-report__heading">
									<div class="admin-community-report__eyebrow">
										<span class="admin-community-report__type"><?php echo html_escape($report['type_label']); ?></span>
										<span>Barangay <?php echo html_escape($report['barangay']); ?></span>
									</div>
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
									<h3>Review and respond</h3>
									<p>Set the report’s current status. You can also send the resident a helpful update.</p>
								</div>
								<label class="admin-community-report__review-status" for="communityReportReviewStatus<?php echo (int) $report['id']; ?>">
									<span>Report status</span>
									<select id="communityReportReviewStatus<?php echo (int) $report['id']; ?>" name="status" required>
										<?php foreach ($report_statuses as $key => $label): ?>
											<option value="<?php echo html_escape($key); ?>"<?php echo $report['status'] === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
										<?php endforeach; ?>
									</select>
									<small class="admin-community-report__status-help" data-status-help>Choose the stage that best describes the report right now.</small>
								</label>
								<label class="admin-community-report__review-note" for="communityReportNote<?php echo (int) $report['id']; ?>">
									<span>Message to the resident <span class="admin-community-report__optional">(optional)</span></span>
									<textarea id="communityReportNote<?php echo (int) $report['id']; ?>" name="admin_note" rows="4" maxlength="1000" aria-describedby="communityReportNoteHelp<?php echo (int) $report['id']; ?> communityReportNoteCount<?php echo (int) $report['id']; ?>" placeholder="Example: Thank you for reporting this. Please continue to follow your barangay’s water advisories."><?php echo html_escape(isset($report['admin_note']) ? $report['admin_note'] : ''); ?></textarea>
									<span class="admin-community-report__note-foot">
										<small id="communityReportNoteHelp<?php echo (int) $report['id']; ?>">Share confirmed information or practical guidance only. This message is visible to the resident.</small>
										<small id="communityReportNoteCount<?php echo (int) $report['id']; ?>" class="admin-community-report__note-count" data-note-count><?php echo isset($report['admin_note']) ? strlen($report['admin_note']) : 0; ?>/1,000 characters</small>
									</span>
								</label>
								<div class="admin-community-report__review-actions">
									<button class="btn btn--primary" type="submit">Save status and message</button>
									<?php if (!empty($report['reviewed_by'])): ?><span class="admin-community-report__updated">Last saved by <?php echo html_escape($report['reviewed_by']); ?> · <?php echo html_escape(date('M j, Y · g:i A', strtotime($report['updated_at']))); ?></span><?php endif; ?>
								</div>
							</form>
						</div>
					</dialog>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
<?php if ( ! empty($reports)): ?>
<script>
(function () {
	var cards = document.querySelectorAll('.admin-community-report');
	cards.forEach(function (card) {
		var trigger = card.querySelector('.admin-community-report__open');
		var dialog = card.querySelector('.admin-community-report__dialog');
		var review = card.querySelector('.admin-community-report__review');
		if (review) {
			var status = review.querySelector('select[name="status"]');
			var statusHelp = review.querySelector('[data-status-help]');
			var note = review.querySelector('textarea[name="admin_note"]');
			var noteCount = review.querySelector('[data-note-count]');
			var statusGuidance = {
				received: 'Received: the report has been recorded and is waiting for review.',
				reviewing: 'Under review: staff are checking the report and available information.',
				action_taken: 'Action taken: a response or follow-up has started.',
				resolved: 'Resolved: the reported issue has been addressed or no further action is needed.'
			};
			if (status && statusHelp) {
				var updateStatusHelp = function () {
					statusHelp.textContent = statusGuidance[status.value] || 'Choose the stage that best describes the report right now.';
				};
				status.addEventListener('change', updateStatusHelp);
				updateStatusHelp();
			}
			if (note && noteCount) {
				var updateNoteCount = function () {
					noteCount.textContent = note.value.length.toLocaleString() + '/1,000 characters';
				};
				note.addEventListener('input', updateNoteCount);
				updateNoteCount();
			}
		}
		if (!trigger || !dialog || typeof dialog.showModal !== 'function') return;

		var close = dialog.querySelector('.admin-community-report__close');
		trigger.addEventListener('click', function () {
			dialog.showModal();
			if (close) close.focus();
		});
		if (close) close.addEventListener('click', function () { dialog.close(); });
		dialog.addEventListener('cancel', function (event) {
			event.preventDefault();
			dialog.close();
		});
		dialog.addEventListener('click', function (event) {
			if (event.target === dialog) dialog.close();
		});
	});
	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') return;
		cards.forEach(function (card) {
			var dialog = card.querySelector('.admin-community-report__dialog');
			if (dialog && dialog.open) {
				event.preventDefault();
				dialog.close();
			}
		});
	}, true);
})();
</script>
<?php endif; ?>

<?php defined('BASEPATH') OR exit('No direct script access allowed');
$reports = isset($reports) && is_array($reports) ? $reports : array();
$report_statuses = isset($report_statuses) && is_array($report_statuses) ? $report_statuses : array();
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
?>
<div class="admin-community-reports">
	<div class="admin-community-reports__notice">
		<strong>Resident reports are not emergency calls.</strong>
		<span>Review submissions and share a concise follow-up residents can safely act on. Do not promise a response time unless one has been confirmed.</span>
	</div>
	<?php if ( ! empty($report_error)): ?><p class="admin-community-reports__message admin-community-reports__message--error" role="alert"><?php echo html_escape($report_error); ?></p><?php endif; ?>
	<form class="admin-community-reports__filters" method="get" action="<?php echo site_url('admin/community-reports'); ?>">
		<label>Status
			<select name="status">
				<option value="">All statuses</option>
				<?php foreach ($report_statuses as $key => $label): ?>
					<option value="<?php echo html_escape($key); ?>"<?php echo $status_filter === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label>Barangay
			<select name="barangay">
				<option value="">All barangays</option>
				<?php foreach ($barangays as $key => $label): ?>
					<option value="<?php echo html_escape($key); ?>"<?php echo $barangay_filter === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<button class="btn btn--primary btn--compact" type="submit">Filter reports</button>
		<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/community-reports'); ?>">Clear</a>
	</form>
	<?php if (empty($reports)): ?>
		<p class="admin-community-reports__empty">No community reports match these filters.</p>
	<?php else: ?>
		<div class="admin-community-reports__list">
			<?php foreach ($reports as $report): ?>
				<article class="admin-community-report">
					<div class="admin-community-report__top">
						<div><span class="admin-community-report__type"><?php echo html_escape($report['type_label']); ?> · Barangay <?php echo html_escape($report['barangay']); ?></span><h2><?php echo html_escape($report['landmark']); ?></h2></div>
						<span class="admin-community-report__status admin-community-report__status--<?php echo html_escape($report['status']); ?>"><?php echo html_escape($report['status_label']); ?></span>
					</div>
					<p class="admin-community-report__by">Submitted by <?php echo html_escape(! empty($report['reporter_name']) ? $report['reporter_name'] : $report['reporter_username']); ?> · <?php echo html_escape(date('M j, Y · g:i A', strtotime($report['created_at']))); ?></p>
					<p class="admin-community-report__description"><?php echo nl2br(html_escape($report['description'])); ?></p>
					<?php if (!empty($report['has_photo'])): ?><a class="admin-community-report__photo" href="<?php echo html_escape($report_photo_base . '/' . (int) $report['id']); ?>" target="_blank" rel="noopener">View attached photo</a><?php endif; ?>
					<form class="admin-community-report__review" method="post" action="<?php echo site_url('admin/community-reports'); ?>">
						<input type="hidden" name="report_admin_token" value="<?php echo html_escape($report_token); ?>">
						<input type="hidden" name="report_id" value="<?php echo (int) $report['id']; ?>">
						<label>Status
							<select name="status" required>
								<?php foreach ($report_statuses as $key => $label): ?>
									<option value="<?php echo html_escape($key); ?>"<?php echo $report['status'] === $key ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label>Follow-up note for resident
							<textarea name="admin_note" rows="3" maxlength="1000" placeholder="Optional update for the resident. Avoid sharing private staff details."><?php echo html_escape(isset($report['admin_note']) ? $report['admin_note'] : ''); ?></textarea>
						</label>
						<button class="btn btn--primary btn--compact" type="submit">Save review</button>
						<?php if (!empty($report['reviewed_by'])): ?><small>Last updated by <?php echo html_escape($report['reviewed_by']); ?> · <?php echo html_escape(date('M j, Y · g:i A', strtotime($report['updated_at']))); ?></small><?php endif; ?>
					</form>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

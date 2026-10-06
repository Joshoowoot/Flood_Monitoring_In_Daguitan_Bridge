<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($edit_row) && is_array($edit_row) ? $edit_row : NULL;
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
?>
<div class="admin-announcements-page">
<div class="admin-split admin-split--wide">
	<section class="glass-card">
		<h2><?php echo $edit ? 'Edit announcement' : 'Create announcement'; ?></h2>
		<form method="post" action="<?php echo site_url('admin/announcements'); ?>" class="admin-form">
			<input type="hidden" name="id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
			<label>Title <input type="text" name="title" required maxlength="160" value="<?php echo $edit ? html_escape($edit['title']) : ''; ?>"></label>
			<label>Message <textarea name="body" rows="5" required><?php echo $edit ? html_escape($edit['body']) : ''; ?></textarea></label>
			<label>Level
				<select name="level">
					<option value="info"<?php echo ($edit && $edit['level'] === 'info') ? ' selected' : ''; ?>>Information</option>
					<option value="yellow"<?php echo ($edit && $edit['level'] === 'yellow') ? ' selected' : ''; ?>>Advisory (yellow)</option>
					<option value="red"<?php echo ($edit && $edit['level'] === 'red') ? ' selected' : ''; ?>>Emergency (red)</option>
				</select>
			</label>
			<label>Target
				<select name="barangay">
					<option value=""<?php echo ($edit && empty($edit['barangay'])) ? ' selected' : ''; ?>>Municipality-wide · all residents</option>
					<?php foreach ($barangays as $key => $label): ?>
						<option value="<?php echo html_escape($key); ?>"<?php echo ($edit && isset($edit['barangay']) && $edit['barangay'] === $key) ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="admin-check"><input type="checkbox" name="is_published" value="1"<?php echo ($edit && ! empty($edit['is_published'])) ? ' checked' : ''; ?>> Published (visible to selected residents; municipality-wide reaches everyone)</label>
			<button class="btn btn--primary" type="submit"><?php echo $edit ? 'Update announcement' : 'Save announcement'; ?></button>
			<?php if ($edit): ?>
				<a class="btn btn--ghost" href="<?php echo site_url('admin/announcements'); ?>">Cancel edit</a>
			<?php endif; ?>
		</form>
	</section>
	<section class="glass-card">
		<div class="admin-announcements-list-head">
			<h2>Published &amp; drafts (<?php echo count($announcements); ?>)</h2>
			<?php if ( ! empty($announcements)): ?>
				<form class="admin-announcements-search" id="adminAnnouncementSearchForm" role="search">
					<label class="admin-sr-only" for="adminAnnouncementSearch">Search announcements</label>
					<input type="search" id="adminAnnouncementSearch" placeholder="Search by title or message" autocomplete="off">
					<button class="btn btn--ghost btn--compact" type="submit">Search</button>
				</form>
			<?php endif; ?>
		</div>
		<?php if (empty($announcements)): ?>
			<p>No announcements yet. Create one to display on the public monitor.</p>
		<?php else: ?>
			<ul class="admin-announce-list" id="adminAnnouncementList">
				<?php foreach ($announcements as $ann): ?>
					<li class="glass-card admin-announce-item">
						<div>
							<strong><?php echo html_escape($ann['title']); ?></strong>
							<p class="admin-announcement-body is-collapsed"><?php echo nl2br(html_escape($ann['body'])); ?></p>
							<button class="admin-announcement-toggle" type="button" aria-expanded="false">See more</button>
							<p class="admin-muted"><?php echo html_escape($ann['level']); ?> · <?php echo empty($ann['barangay']) ? 'Municipality-wide' : 'Barangay ' . html_escape($ann['barangay']); ?> · <?php echo $ann['is_published'] ? 'Published' : 'Draft'; ?><?php echo $ann['push_sent'] ? ' · Push sent' : ''; ?> · Updated <?php echo html_escape($ann['updated_at']); ?></p>
						</div>
						<div class="admin-announce-actions">
							<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/announcements?edit=' . (int) $ann['id']); ?>">Edit</a>
							<?php if ( ! $ann['is_published']): ?>
								<form method="post" action="<?php echo site_url('admin/announcements'); ?>">
									<input type="hidden" name="action" value="publish">
									<input type="hidden" name="id" value="<?php echo (int) $ann['id']; ?>">
									<label class="admin-check"><input type="checkbox" name="send_push" value="1"> Send push</label>
									<button class="btn btn--primary btn--compact" type="submit">Publish</button>
								</form>
							<?php else: ?>
								<form method="post" action="<?php echo site_url('admin/announcements'); ?>">
									<input type="hidden" name="action" value="unpublish">
									<input type="hidden" name="id" value="<?php echo (int) $ann['id']; ?>">
									<button class="btn btn--ghost btn--compact" type="submit">Unpublish</button>
								</form>
							<?php endif; ?>
							<form method="post" action="<?php echo site_url('admin/announcements'); ?>" onsubmit="return confirm('Delete this announcement?');">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int) $ann['id']; ?>">
								<button class="btn btn--ghost btn--compact admin-announcement-delete" type="submit">Delete</button>
							</form>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="admin-announcements-search-empty admin-muted" id="adminAnnouncementSearchEmpty" role="status" hidden>No announcements match your search.</p>
		<?php endif; ?>
	</section>
</div>
</div>
<?php if ( ! empty($announcements)): ?>
<script>
(function () {
	var form = document.getElementById('adminAnnouncementSearchForm');
	var search = document.getElementById('adminAnnouncementSearch');
	var list = document.getElementById('adminAnnouncementList');
	var empty = document.getElementById('adminAnnouncementSearchEmpty');
	if (!form || !search || !list || !empty) return;

	Array.prototype.forEach.call(list.querySelectorAll('.admin-announcement-body'), function (body) {
		var toggle = body.nextElementSibling;
		if (!toggle || !toggle.classList.contains('admin-announcement-toggle')) return;
		if (body.scrollHeight <= body.clientHeight + 1) {
			toggle.hidden = true;
			body.classList.remove('is-collapsed');
			return;
		}

		toggle.addEventListener('click', function () {
			var expanded = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
			body.classList.toggle('is-collapsed', expanded);
			toggle.textContent = expanded ? 'See more' : 'See less';
		});
	});

	function filterAnnouncements() {
		var query = search.value.trim().toLocaleLowerCase();
		var visibleCount = 0;
		Array.prototype.forEach.call(list.children, function (item) {
			var content = item.querySelector('div:first-child');
			var matches = !query || (content && content.textContent.toLocaleLowerCase().indexOf(query) !== -1);
			item.hidden = !matches;
			if (matches) visibleCount += 1;
		});
		empty.hidden = visibleCount !== 0;
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();
		filterAnnouncements();
	});
	search.addEventListener('input', filterAnnouncements);
})();
</script>
<?php endif; ?>

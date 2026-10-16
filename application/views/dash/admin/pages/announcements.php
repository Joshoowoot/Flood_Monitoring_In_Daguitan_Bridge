<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($edit_row) && is_array($edit_row) ? $edit_row : NULL;
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
$published_count = 0;
foreach ($announcements as $announcement)
{
	if ( ! empty($announcement['is_published'])) $published_count++;
}
$draft_count = count($announcements) - $published_count;
$level_labels = array('info' => 'Information', 'yellow' => 'Advisory', 'red' => 'Emergency');
?>
<div class="admin-announcements-page">
	<section class="glass-card admin-announcement-composer">
		<div class="admin-announcement-section-head">
			<div>
				<p class="admin-kicker"><?php echo $edit ? 'Update public information' : 'Message composer'; ?></p>
				<h2><?php echo $edit ? 'Edit announcement' : 'Create announcement'; ?></h2>
				<p class="admin-muted">Write a clear advisory, choose who should receive it, and decide whether to publish now or save it as a draft.</p>
			</div>
		</div>
		<form method="post" action="<?php echo site_url('admin/announcements'); ?>" class="admin-form" enctype="multipart/form-data">
			<input type="hidden" name="id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
			<div class="admin-announcement-field admin-announcement-field--title">
				<label for="announcementTitle">Title</label>
				<input id="announcementTitle" type="text" name="title" required maxlength="160" value="<?php echo $edit ? html_escape($edit['title']) : ''; ?>" placeholder="e.g. Flood advisory for coastal communities">
				<p class="admin-announcement-help">Use a short, specific heading residents can understand at a glance.</p>
			</div>
			<div class="admin-announcement-field admin-announcement-field--message">
				<label for="announcementBody">Message</label>
				<textarea id="announcementBody" name="body" rows="6" required placeholder="Explain what is happening, who is affected, and what residents should do."><?php echo $edit ? html_escape($edit['body']) : ''; ?></textarea>
				<p class="admin-announcement-help">Include the affected area, safety guidance, and any useful timing information.</p>
			</div>
			<div class="admin-announcement-field admin-announcement-field--images">
				<label for="announcementImages">Photos</label>
				<input id="announcementImages" type="file" name="announcement_images[]" accept="image/jpeg,image/png,image/webp" multiple data-existing-count="<?php echo $edit && isset($edit['images']) ? count($edit['images']) : 0; ?>">
				<p class="admin-announcement-help" id="announcementImageHelp">Add up to 5 photos. JPG, PNG, or WebP; max 3 MB per image.</p>
				<?php if ($edit && ! empty($edit['images'])): ?>
					<div class="admin-announcement-image-grid" aria-label="Photos already attached">
						<?php foreach ($edit['images'] as $index => $image_name):
							if ( ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $image_name)) continue;
						?>
							<img src="<?php echo html_escape(site_url('announcement-image/' . $image_name)); ?>" alt="Attached photo <?php echo (int) $index + 1; ?>">
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<div class="admin-announcement-image-grid" id="announcementImagePreview" aria-live="polite"></div>
				<p class="admin-announcement-image-count" id="announcementImageCount" role="status"></p>
			</div>
			<div class="admin-announcement-settings">
				<div class="admin-announcement-field">
					<label for="announcementLevel">Advisory level</label>
					<select id="announcementLevel" name="level">
						<option value="info"<?php echo ($edit && $edit['level'] === 'info') ? ' selected' : ''; ?>>Information</option>
						<option value="yellow"<?php echo ($edit && $edit['level'] === 'yellow') ? ' selected' : ''; ?>>Advisory · Yellow</option>
						<option value="red"<?php echo ($edit && $edit['level'] === 'red') ? ' selected' : ''; ?>>Emergency · Red</option>
					</select>
					<p class="admin-announcement-help">Choose the level that best reflects urgency.</p>
				</div>
				<div class="admin-announcement-field">
					<label for="announcementTarget">Audience</label>
					<select id="announcementTarget" name="barangay">
						<option value=""<?php echo ($edit && empty($edit['barangay'])) ? ' selected' : ''; ?>>Municipality-wide · all residents</option>
						<?php foreach ($barangays as $key => $label): ?>
							<option value="<?php echo html_escape($key); ?>"<?php echo ($edit && isset($edit['barangay']) && $edit['barangay'] === $key) ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="admin-announcement-help">Municipality-wide reaches all residents.</p>
				</div>
				<label class="admin-announcement-publish">
					<input type="checkbox" name="is_published" value="1"<?php echo ($edit && ! empty($edit['is_published'])) ? ' checked' : ''; ?>>
					<span><strong>Publish immediately</strong><small>Leave unchecked to save as a draft.</small></span>
				</label>
			</div>
			<div class="admin-announcement-form-actions">
				<button class="btn btn--primary" type="submit"><?php echo $edit ? 'Update announcement' : 'Save announcement'; ?></button>
				<?php if ($edit): ?>
					<a class="btn btn--ghost" href="<?php echo site_url('admin/announcements'); ?>">Cancel editing</a>
				<?php endif; ?>
			</div>
		</form>
	</section>

	<section class="glass-card admin-announcement-library">
		<div class="admin-announcements-list-head">
			<div class="admin-announcement-list-title">
				<p class="admin-kicker">Announcement library</p>
				<h2>Published &amp; drafts</h2>
				<div class="admin-announcement-counts" aria-label="Announcement totals">
					<span><?php echo number_format($published_count); ?> published</span>
					<span><?php echo number_format($draft_count); ?> drafts</span>
				</div>
			</div>
			<?php if ( ! empty($announcements)): ?>
				<form class="admin-announcements-search" id="adminAnnouncementSearchForm" role="search">
					<label class="admin-sr-only" for="adminAnnouncementSearch">Search announcements</label>
					<input type="search" id="adminAnnouncementSearch" placeholder="Search by title or message" autocomplete="off" aria-controls="adminAnnouncementList">
					<button class="btn btn--ghost btn--compact" type="submit">Search</button>
				</form>
			<?php endif; ?>
		</div>
		<?php if (empty($announcements)): ?>
			<div class="admin-announcements-empty">
				<strong>No announcements yet</strong>
				<p>Create an announcement to share official updates with residents.</p>
			</div>
		<?php else: ?>
			<ul class="admin-announce-list" id="adminAnnouncementList" tabindex="0" aria-label="Announcement list">
				<?php foreach ($announcements as $ann): ?>
					<li class="glass-card admin-announce-item">
						<?php
						$level = isset($ann['level']) ? strtolower((string) $ann['level']) : 'info';
						if ( ! isset($level_labels[$level])) $level = 'info';
						?>
						<div class="admin-announce-main">
							<div class="admin-announce-badges">
								<span class="admin-announcement-level admin-announcement-level--<?php echo html_escape($level); ?>"><?php echo html_escape($level_labels[$level]); ?></span>
								<span class="admin-announcement-status<?php echo $ann['is_published'] ? ' is-published' : ''; ?>"><?php echo $ann['is_published'] ? 'Published' : 'Draft'; ?></span>
								<?php if ( ! empty($ann['push_sent'])): ?><span class="admin-announcement-push">Push sent</span><?php endif; ?>
							</div>
							<h3><?php echo html_escape($ann['title']); ?></h3>
							<p class="admin-announcement-body is-collapsed"><?php echo nl2br(html_escape($ann['body'])); ?></p>
							<button class="admin-announcement-toggle" type="button" aria-expanded="false">See more</button>
							<?php if ( ! empty($ann['images'])): ?>
								<div class="admin-announcement-image-grid admin-announcement-image-grid--library" aria-label="Announcement photos">
									<?php foreach ($ann['images'] as $index => $image_name):
										if ( ! preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/', $image_name)) continue;
									?>
										<img src="<?php echo html_escape(site_url('announcement-image/' . $image_name)); ?>" alt="Photo <?php echo (int) $index + 1; ?> attached to <?php echo html_escape($ann['title']); ?>" loading="lazy">
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<div class="admin-announcement-meta">
								<span><?php echo empty($ann['barangay']) ? 'Municipality-wide audience' : 'Barangay ' . html_escape($ann['barangay']); ?></span>
								<span>Updated <?php echo html_escape($ann['updated_at']); ?></span>
							</div>
						</div>
						<div class="admin-announce-actions">
							<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/announcements?edit=' . (int) $ann['id']); ?>">Edit</a>
							<?php if ( ! $ann['is_published']): ?>
								<form method="post" action="<?php echo site_url('admin/announcements'); ?>">
									<input type="hidden" name="action" value="publish">
									<input type="hidden" name="id" value="<?php echo (int) $ann['id']; ?>">
									<label class="admin-check"><input type="checkbox" name="send_push" value="1"> Notify subscribed devices</label>
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
			<p class="admin-announcements-search-empty admin-muted" id="adminAnnouncementSearchEmpty" role="status" hidden>No announcements match your search. Try another title or message.</p>
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
	var imageInput = document.getElementById('announcementImages');
	var imagePreview = document.getElementById('announcementImagePreview');
	var imageCount = document.getElementById('announcementImageCount');
	if (imageInput && imagePreview && imageCount) {
		var previewUrls = [];
		var existingCount = Number(imageInput.getAttribute('data-existing-count')) || 0;
		if (existingCount) imageCount.textContent = existingCount + ' of 5 photos already attached';
		imageInput.addEventListener('change', function () {
			previewUrls.forEach(function (url) { URL.revokeObjectURL(url); });
			previewUrls = [];
			imagePreview.textContent = '';
			existingCount = Number(imageInput.getAttribute('data-existing-count')) || 0;
			var total = existingCount + imageInput.files.length;
			imageCount.textContent = total > 5
				? 'You can attach up to 5 images in total. Remove some selected photos and try again.'
				: total + ' of 5 photos selected' + (existingCount ? ' (' + existingCount + ' already attached)' : '');
			imageCount.classList.toggle('is-error', total > 5);
			Array.prototype.forEach.call(imageInput.files, function (file) {
				if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return;
				var url = URL.createObjectURL(file);
				previewUrls.push(url);
				var image = document.createElement('img');
				image.src = url;
				image.alt = 'Selected photo preview';
				imagePreview.appendChild(image);
			});
		});
	}
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

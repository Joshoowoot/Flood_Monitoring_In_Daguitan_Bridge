<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($edit_row) && is_array($edit_row) ? $edit_row : NULL;
?>
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
			<label class="admin-check"><input type="checkbox" name="is_published" value="1"<?php echo ($edit && ! empty($edit['is_published'])) ? ' checked' : ''; ?>> Published (shows on public site when checked)</label>
			<button class="btn btn--primary" type="submit"><?php echo $edit ? 'Update announcement' : 'Save announcement'; ?></button>
			<?php if ($edit): ?>
				<a class="btn btn--ghost" href="<?php echo site_url('admin/announcements'); ?>">Cancel edit</a>
			<?php endif; ?>
		</form>
	</section>
	<section class="glass-card">
		<h2>Published &amp; drafts (<?php echo count($announcements); ?>)</h2>
		<?php if (empty($announcements)): ?>
			<p>No announcements yet. Create one to display on the public monitor.</p>
		<?php else: ?>
			<ul class="admin-announce-list">
				<?php foreach ($announcements as $ann): ?>
					<li class="glass-card admin-announce-item">
						<div>
							<strong><?php echo html_escape($ann['title']); ?></strong>
							<p><?php echo nl2br(html_escape($ann['body'])); ?></p>
							<p class="admin-muted"><?php echo html_escape($ann['level']); ?> · <?php echo $ann['is_published'] ? 'Published' : 'Draft'; ?><?php echo $ann['push_sent'] ? ' · Push sent' : ''; ?> · Updated <?php echo html_escape($ann['updated_at']); ?></p>
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
								<button class="btn btn--ghost btn--compact" type="submit">Delete</button>
							</form>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
</div>

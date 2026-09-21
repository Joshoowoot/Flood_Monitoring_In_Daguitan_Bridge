<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<section class="glass-card admin-filter-bar">
	<form method="get" action="<?php echo site_url('admin/residents'); ?>" class="admin-filters">
		<label>Search <input type="search" name="q" value="<?php echo html_escape($search); ?>" placeholder="Name, username, phone"></label>
		<label>Filter
			<select name="status">
				<option value="">All residents</option>
				<option value="active"<?php echo (isset($status_filter) && $status_filter === 'active') ? ' selected' : ''; ?>>Signed in at least once</option>
				<option value="no_phone"<?php echo (isset($status_filter) && $status_filter === 'no_phone') ? ' selected' : ''; ?>>Missing mobile number</option>
			</select>
		</label>
		<button class="btn btn--primary" type="submit">Apply</button>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/residents'); ?>">Clear</a>
		<?php
		$export_q = http_build_query(array_filter(array(
			'q'      => $search,
			'status' => isset($status_filter) ? $status_filter : '',
		)));
		?>
		<a class="btn btn--ghost" href="<?php echo site_url('admin/residents_export' . ($export_q ? '?' . $export_q : '')); ?>">Download CSV</a>
	</form>
	<p class="admin-muted"><?php echo (int) $resident_count; ?> resident account(s) listed.</p>
</section>

<section class="glass-card dash-table-wrap">
	<h2>Registered residents</h2>
	<?php if (empty($residents)): ?>
		<p>No residents match your search. Residents appear after they <a href="<?php echo site_url('signup'); ?>">sign up</a> on the public site.</p>
	<?php else: ?>
	<div class="admin-table-scroll">
		<table class="dash-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Mobile</th>
					<th>Username</th>
					<th>Registered</th>
					<th>Last sign-in</th>
					<th>Sign-ins</th>
					<th>Notifications</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($residents as $u): ?>
					<tr>
						<td><?php echo html_escape($u['name']); ?></td>
						<td><?php echo ! empty($u['phone']) ? html_escape($u['phone']) : '—'; ?></td>
						<td><?php echo html_escape($u['username']); ?></td>
						<td><?php echo html_escape(isset($u['created_at']) ? $u['created_at'] : '—'); ?></td>
						<td><?php echo html_escape($u['last_login_at_display']); ?></td>
						<td><?php echo (int) $u['login_count']; ?></td>
						<td><span class="admin-pill admin-pill--<?php echo ! empty($u['phone']) ? 'online' : 'warning'; ?>"><?php echo html_escape($u['notify_status']); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</section>

<?php if (! empty($recent_logins)): ?>
<section class="glass-card dash-table-wrap">
	<h2>Recent sign-in activity</h2>
	<table class="dash-table">
		<thead><tr><th>When</th><th>Name</th><th>Username</th><th>Mobile</th><th>IP</th></tr></thead>
		<tbody>
			<?php foreach ($recent_logins as $log): ?>
				<tr>
					<td><?php echo html_escape(isset($log['logged_in_at']) ? $log['logged_in_at'] : '—'); ?></td>
					<td><?php echo html_escape(isset($log['name']) ? $log['name'] : '—'); ?></td>
					<td><?php echo html_escape(isset($log['username']) ? $log['username'] : '—'); ?></td>
					<td><?php echo ! empty($log['phone']) ? html_escape($log['phone']) : '—'; ?></td>
					<td><?php echo html_escape(isset($log['ip_address']) ? $log['ip_address'] : '—'); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
<?php endif; ?>

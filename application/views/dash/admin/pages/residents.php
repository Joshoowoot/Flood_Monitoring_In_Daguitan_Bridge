<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="admin-residents-page">
<section class="glass-card admin-filter-bar">
	<form method="get" action="<?php echo site_url('admin/residents'); ?>" class="admin-filters">
		<label>Search <input type="search" name="q" value="<?php echo html_escape($search); ?>" placeholder="Name, username, phone"></label>
		<label>Barangay
			<select name="barangay">
				<option value="">All barangays</option>
				<option value="__none__"<?php echo isset($barangay_filter) && $barangay_filter === '__none__' ? ' selected' : ''; ?>>Not specified</option>
				<?php foreach ($barangays as $value => $label): ?>
					<option value="<?php echo html_escape($value); ?>"<?php echo (isset($barangay_filter) && $barangay_filter === $value) ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label>Filter
			<select name="status">
				<option value="">All residents</option>
				<option value="active"<?php echo (isset($status_filter) && $status_filter === 'active') ? ' selected' : ''; ?>>Signed in at least once</option>
				<option value="no_phone"<?php echo (isset($status_filter) && $status_filter === 'no_phone') ? ' selected' : ''; ?>>Missing mobile number</option>
			</select>
		</label>
		<?php
		$export_q = http_build_query(array_filter(array(
			'q'      => $search,
			'status' => isset($status_filter) ? $status_filter : '',
			'barangay' => isset($barangay_filter) ? $barangay_filter : '',
		)));
		?>
		<div class="admin-residents-actions">
			<button class="btn btn--primary" type="submit">Apply filters</button>
			<a class="btn btn--ghost" href="<?php echo site_url('admin/residents'); ?>">Clear</a>
			<a class="btn btn--ghost" href="<?php echo site_url('admin/residents_export' . ($export_q ? '?' . $export_q : '')); ?>">Download CSV</a>
		</div>
	</form>
</section>

<section class="admin-resident-barangays" aria-label="Resident counts by barangay">
	<header class="admin-resident-barangays__head">
		<div>
			<p class="card-kicker">Community distribution</p>
			<h2>Residents by barangay</h2>
		</div>
		<p>Counts use each resident’s latest saved barangay.</p>
	</header>
	<?php if (empty($barangay_counts)): ?>
		<p class="admin-resident-barangays__empty">No barangay data yet. Residents can add their barangay when they register or update their profile.</p>
	<?php else: ?>
		<div class="admin-resident-barangays__grid">
			<?php foreach ($barangay_counts as $count): ?>
				<?php $count_filter = $count['barangay'] !== '' ? $count['barangay'] : '__none__'; ?>
				<a class="admin-resident-barangay<?php echo isset($barangay_filter) && $barangay_filter === $count_filter ? ' is-active' : ''; ?>" href="<?php echo site_url('admin/residents?' . http_build_query(array('barangay' => $count_filter))); ?>">
					<span><?php echo html_escape($count['barangay_label']); ?></span>
					<strong><?php echo (int) $count['resident_count']; ?></strong>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<section class="glass-card dash-table-wrap">
	<header class="admin-residents-table-head">
		<h2>Registered residents</h2>
		<div class="admin-residents-table-tools">
			<label class="admin-residents-sort-label" for="residentSort">Sort by</label>
			<select class="admin-residents-sort" id="residentSort" aria-label="Sort registered residents">
				<option value="">Default order</option>
				<option value="4:desc:date">Newest registered</option>
				<option value="4:asc:date">Oldest registered</option>
				<option value="5:desc:date">Most recent sign-in</option>
				<option value="5:asc:date">Least recent sign-in</option>
				<option value="6:desc:number">Most sign-ins</option>
				<option value="6:asc:number">Fewest sign-ins</option>
			</select>
			<span><?php echo (int) $resident_count; ?> total</span>
		</div>
	</header>
	<?php if (empty($residents)): ?>
		<p>No residents match your search. Residents appear after they <a href="<?php echo site_url('signup'); ?>">sign up</a> on the public site.</p>
	<?php else: ?>
	<div class="admin-table-scroll">
		<table class="dash-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Barangay</th>
					<th>Mobile</th>
					<th>Username</th>
					<th>Registered</th>
					<th>Last sign-in</th>
					<th>Sign-ins</th>
					<th>Notifications</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($residents as $u):
					$resident_barangay = isset($u['barangay']) ? trim((string) $u['barangay']) : '';
					$resident_barangay_label = $resident_barangay === ''
						? 'Not specified'
						: (isset($barangays[$resident_barangay]) ? $barangays[$resident_barangay] : $resident_barangay);
				?>
					<tr>
						<td><?php echo html_escape($u['name']); ?></td>
						<td><?php echo html_escape($resident_barangay_label); ?></td>
						<td><?php echo ! empty($u['phone']) ? html_escape($u['phone']) : '—'; ?></td>
						<td><?php echo html_escape($u['username']); ?></td>
						<td data-sort-value="<?php echo html_escape(isset($u['created_at']) ? $u['created_at'] : ''); ?>"><?php echo html_escape(isset($u['created_at']) ? $u['created_at'] : '—'); ?></td>
						<td data-sort-value="<?php echo html_escape($u['last_login_at_display'] === '—' ? '' : $u['last_login_at_display']); ?>"><?php echo html_escape($u['last_login_at_display']); ?></td>
						<td data-sort-value="<?php echo (int) $u['login_count']; ?>"><?php echo (int) $u['login_count']; ?></td>
						<td><span class="admin-pill admin-pill--<?php echo ! empty($u['phone']) ? 'online' : 'warning'; ?>"><?php echo html_escape($u['notify_status']); ?></span></td>
						<td>
							<form method="post" action="<?php echo site_url('admin/residents' . ($search !== '' || ! empty($status_filter) || ! empty($barangay_filter) ? '?' . http_build_query(array_filter(array('q' => $search, 'status' => isset($status_filter) ? $status_filter : '', 'barangay' => isset($barangay_filter) ? $barangay_filter : ''))) : '')); ?>" onsubmit="return confirm('Permanently delete this resident account and its sign-in history from the local database? Cloud deletion will be queued for the next sync.');">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
								<input type="hidden" name="resident_delete_token" value="<?php echo html_escape($resident_delete_token); ?>">
								<button class="btn btn--ghost btn--compact admin-resident-delete" type="submit">Delete</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</section>

<?php if (! empty($recent_logins)): ?>
<section class="glass-card dash-table-wrap">
	<header class="admin-residents-table-head">
		<h2>Recent sign-in activity</h2>
		<div class="admin-residents-table-tools">
			<label class="admin-residents-sort-label" for="loginSort">Sort by</label>
			<select class="admin-residents-sort" id="loginSort" aria-label="Sort sign-in activity">
				<option value="">Default order</option>
				<option value="0:desc:date">Newest first</option>
				<option value="0:asc:date">Oldest first</option>
			</select>
		</div>
	</header>
	<div class="admin-table-scroll">
		<table class="dash-table">
			<thead>
				<tr>
					<th>When</th>
					<th>Name</th>
					<th>Username</th>
					<th>Mobile</th>
					<th>IP</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($recent_logins as $log): ?>
					<tr>
						<td data-sort-value="<?php echo html_escape(isset($log['logged_in_at']) ? $log['logged_in_at'] : ''); ?>"><?php echo html_escape(isset($log['logged_in_at']) ? $log['logged_in_at'] : '—'); ?></td>
						<td><?php echo html_escape(isset($log['name']) ? $log['name'] : '—'); ?></td>
						<td><?php echo html_escape(isset($log['username']) ? $log['username'] : '—'); ?></td>
						<td><?php echo ! empty($log['phone']) ? html_escape($log['phone']) : '—'; ?></td>
						<td><?php echo html_escape(isset($log['ip_address']) ? $log['ip_address'] : '—'); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
<?php endif; ?>
</div>

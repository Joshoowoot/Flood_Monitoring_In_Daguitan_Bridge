<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($edit_row) && is_array($edit_row) ? $edit_row : NULL;
$centers = isset($centers) && is_array($centers) ? $centers : array();
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="admin-split admin-split--wide">
	<section class="glass-card">
		<h2><?php echo $edit ? 'Edit evacuation pin' : 'Add evacuation pin'; ?></h2>
		<p class="admin-muted">Add the official location for a barangay in Dulag, Leyte. Use the exact latitude and longitude from the MDRRMO-approved location.</p>
		<form method="post" action="<?php echo site_url('admin/evacuation'); ?>" class="admin-form">
			<input type="hidden" name="id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
			<label>Center name <input type="text" name="name" required maxlength="160" value="<?php echo $edit ? html_escape($edit['name']) : ''; ?>" placeholder="Barangay evacuation center"></label>
			<label>Barangay <input type="text" name="barangay" required maxlength="80" value="<?php echo $edit ? html_escape($edit['barangay']) : ''; ?>" placeholder="Barangay name"></label>
			<label>Address <input type="text" name="address" required maxlength="220" value="<?php echo $edit ? html_escape($edit['address']) : ''; ?>" placeholder="Street or landmark, Dulag, Leyte"></label>
			<div class="admin-form-row">
				<label>Latitude <input type="number" id="evacuationLatitude" name="latitude" required step="0.000001" min="9" max="12" value="<?php echo $edit ? html_escape($edit['latitude']) : ''; ?>" placeholder="10.952500"></label>
				<label>Longitude <input type="number" id="evacuationLongitude" name="longitude" required step="0.000001" min="123" max="127" value="<?php echo $edit ? html_escape($edit['longitude']) : ''; ?>" placeholder="125.032200"></label>
			</div>
			<p class="admin-muted">Click the map to place or move the evacuation pin.</p>
			<div id="evacuationAdminMap" class="evacuation-admin-map" aria-label="Click to select evacuation center location"></div>
			<label>Capacity note <input type="text" name="capacity" maxlength="120" value="<?php echo $edit ? html_escape($edit['capacity']) : 'Confirm current capacity with MDRRMO'; ?>"></label>
			<label>Contact number <input type="text" name="phone" maxlength="30" value="<?php echo $edit ? html_escape($edit['phone']) : '053 325 0000'; ?>"></label>
			<label class="admin-check"><input type="checkbox" name="active" value="1"<?php echo (!$edit || ! empty($edit['active'])) ? ' checked' : ''; ?>> Visible to residents</label>
			<button class="btn btn--primary" type="submit"><?php echo $edit ? 'Update pin' : 'Save pin'; ?></button>
			<?php if ($edit): ?><a class="btn btn--ghost" href="<?php echo site_url('admin/evacuation'); ?>">Cancel edit</a><?php endif; ?>
		</form>
	</section>
	<section class="glass-card">
		<h2>Published pins (<?php echo count($centers); ?>)</h2>
		<?php if (empty($centers)): ?>
			<p>No evacuation pins have been added.</p>
		<?php else: ?>
			<ul class="admin-announce-list">
				<?php foreach ($centers as $center): ?>
					<li class="glass-card admin-announce-item">
						<div>
							<strong><?php echo html_escape($center['name']); ?></strong>
							<p>Barangay <?php echo html_escape($center['barangay']); ?> · <?php echo html_escape($center['address']); ?></p>
							<p class="admin-muted"><?php echo html_escape($center['latitude']); ?>, <?php echo html_escape($center['longitude']); ?> · <?php echo ! empty($center['active']) ? 'Visible' : 'Hidden'; ?></p>
						</div>
						<div class="admin-announce-actions">
							<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/evacuation?edit=' . (int) $center['id']); ?>">Edit</a>
							<form method="post" action="<?php echo site_url('admin/evacuation'); ?>" onsubmit="return confirm('Delete this evacuation pin?');">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int) $center['id']; ?>">
								<button class="btn btn--ghost btn--compact" type="submit">Delete</button>
							</form>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
	var mapElement = document.getElementById('evacuationAdminMap');
	var latitude = document.getElementById('evacuationLatitude');
	var longitude = document.getElementById('evacuationLongitude');
	if (!mapElement || !window.L || !latitude || !longitude) return;
	var initial = [Number(latitude.value) || 10.9525, Number(longitude.value) || 125.0322];
	var map = L.map(mapElement).setView(initial, latitude.value ? 16 : 13);
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
	var marker = latitude.value && longitude.value ? L.marker(initial).addTo(map) : null;
	map.on('click', function (event) {
		var point = event.latlng;
		latitude.value = point.lat.toFixed(6);
		longitude.value = point.lng.toFixed(6);
		if (marker) marker.setLatLng(point);
		else marker = L.marker(point).addTo(map);
	});
})();
</script>

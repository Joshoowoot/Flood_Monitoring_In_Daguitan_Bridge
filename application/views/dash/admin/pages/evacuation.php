<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($edit_row) && is_array($edit_row) ? $edit_row : NULL;
$centers = isset($centers) && is_array($centers) ? $centers : array();
$visible_count = 0;
foreach ($centers as $center)
{
	if ( ! empty($center['active'])) $visible_count++;
}
$hidden_count = count($centers) - $visible_count;
?>
<div class="admin-evacuation-page">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="admin-split admin-split--wide">
	<section class="glass-card admin-evacuation-card">
		<header class="admin-evacuation-card__head">
			<p class="admin-kicker"><?php echo $edit ? 'Update directory listing' : 'Directory management'; ?></p>
			<h2><?php echo $edit ? 'Edit evacuation center' : 'Add evacuation center'; ?></h2>
			<p class="admin-muted">Enter verified center details, then mark its location on the map so residents can find it.</p>
		</header>
		<form method="post" action="<?php echo site_url('admin/evacuation'); ?>" class="admin-form">
			<input type="hidden" name="id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
			<label class="admin-evacuation-field--name" for="evacuationCenterName">Center name <input id="evacuationCenterName" type="text" name="name" required maxlength="160" value="<?php echo $edit ? html_escape($edit['name']) : ''; ?>" placeholder="Barangay evacuation center"></label>
			<label class="admin-evacuation-field--barangay" for="evacuationBarangay">Barangay <input id="evacuationBarangay" type="text" name="barangay" required maxlength="80" value="<?php echo $edit ? html_escape($edit['barangay']) : ''; ?>" placeholder="Barangay name"></label>
			<label class="admin-evacuation-field--address" for="evacuationAddress">Street address or landmark <input id="evacuationAddress" type="text" name="address" required maxlength="220" value="<?php echo $edit ? html_escape($edit['address']) : ''; ?>" placeholder="Street or landmark, Dulag, Leyte"></label>
			<div class="admin-form-row admin-evacuation-coordinates">
				<label for="evacuationLatitude">Latitude <input type="number" id="evacuationLatitude" name="latitude" required step="0.000001" min="9" max="12" value="<?php echo $edit ? html_escape($edit['latitude']) : ''; ?>" placeholder="10.952500"></label>
				<label for="evacuationLongitude">Longitude <input type="number" id="evacuationLongitude" name="longitude" required step="0.000001" min="123" max="127" value="<?php echo $edit ? html_escape($edit['longitude']) : ''; ?>" placeholder="125.032200"></label>
			</div>
			<p class="admin-muted admin-evacuation-map-note" id="evacuationMapHelp">Select the exact location on the map, or enter the coordinates above. The marker shows the location residents will see.</p>
			<div id="evacuationAdminMap" class="evacuation-admin-map" aria-label="Map for selecting evacuation center coordinates" aria-describedby="evacuationMapHelp"></div>
			<label class="admin-evacuation-field--capacity" for="evacuationCapacity">Capacity note <input id="evacuationCapacity" type="text" name="capacity" maxlength="120" value="<?php echo $edit ? html_escape($edit['capacity']) : 'Confirm current capacity with MDRRMO'; ?>" placeholder="Confirm current capacity with MDRRMO"></label>
			<label class="admin-evacuation-field--phone" for="evacuationPhone">Public contact number <input id="evacuationPhone" type="text" name="phone" maxlength="30" value="<?php echo $edit ? html_escape($edit['phone']) : '053 325 0000'; ?>" placeholder="053 325 0000"></label>
			<label class="admin-check admin-evacuation-field--active"><input type="checkbox" name="active" value="1"<?php echo (!$edit || ! empty($edit['active'])) ? ' checked' : ''; ?>> <span><strong>Visible in resident directory</strong><small>Turn this off to keep the center saved but hide it from residents.</small></span></label>
			<div class="admin-evacuation-form-actions">
				<button class="btn btn--primary" type="submit"><?php echo $edit ? 'Save center changes' : 'Add evacuation center'; ?></button>
				<?php if ($edit): ?><a class="btn btn--ghost" href="<?php echo site_url('admin/evacuation'); ?>">Cancel editing</a><?php endif; ?>
			</div>
		</form>
	</section>
	<section class="glass-card admin-evacuation-card">
		<header class="admin-evacuation-card__head">
			<p class="admin-kicker">Location directory</p>
			<h2>Managed evacuation centers <span class="admin-evacuation-count"><?php echo count($centers); ?></span></h2>
			<p class="admin-muted">Manage the locations residents can use for directions and emergency planning.</p>
		</header>
		<div class="admin-evacuation-summary" aria-label="Evacuation center visibility">
			<span><strong><?php echo number_format($visible_count); ?></strong> visible to residents</span>
			<span><strong><?php echo number_format($hidden_count); ?></strong> hidden</span>
		</div>
		<?php if (empty($centers)): ?>
			<div class="admin-evacuation-empty"><strong>No centers added yet</strong><p>Add an evacuation center to make its verified location available in the resident directory.</p></div>
		<?php else: ?>
			<div class="admin-evacuation-directory-tools">
				<label class="admin-evacuation-search">
					<span class="admin-evacuation-visually-hidden">Search evacuation centers</span>
					<input type="search" id="evacuationCenterSearch" placeholder="Search by center, barangay, or address" autocomplete="off" aria-controls="evacuationCenterList">
				</label>
				<p id="evacuationCenterSearchStatus" class="admin-evacuation-search-status" role="status">Showing all <?php echo count($centers); ?> centers</p>
			</div>
			<ul class="admin-announce-list admin-evacuation-list" id="evacuationCenterList">
				<?php foreach ($centers as $center): ?>
					<li class="glass-card admin-announce-item" data-evacuation-center>
						<div class="admin-evacuation-center__details">
							<div class="admin-evacuation-center__top">
								<span class="admin-evacuation-center__visibility<?php echo ! empty($center['active']) ? ' is-visible' : ''; ?>"><?php echo ! empty($center['active']) ? 'Visible to residents' : 'Hidden from residents'; ?></span>
								<span class="admin-evacuation-center__barangay"><?php echo html_escape($center['barangay']); ?></span>
							</div>
							<strong><?php echo html_escape($center['name']); ?></strong>
							<p><?php echo html_escape($center['address']); ?></p>
							<p class="admin-evacuation-coordinates-label"><span>Map coordinates</span> <code><?php echo html_escape($center['latitude']); ?>, <?php echo html_escape($center['longitude']); ?></code></p>
							<?php if ( ! empty($center['capacity']) || ! empty($center['phone'])): ?>
								<div class="admin-evacuation-center__contact">
									<?php if ( ! empty($center['capacity'])): ?><span><strong>Capacity:</strong> <?php echo html_escape($center['capacity']); ?></span><?php endif; ?>
									<?php if ( ! empty($center['phone'])): ?><span><strong>Contact:</strong> <?php echo html_escape($center['phone']); ?></span><?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="admin-announce-actions">
							<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/evacuation?edit=' . (int) $center['id']); ?>">Edit center</a>
							<form method="post" action="<?php echo site_url('admin/evacuation'); ?>" onsubmit="return confirm('Delete this evacuation pin?');">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?php echo (int) $center['id']; ?>">
								<button class="btn btn--ghost btn--compact" type="submit">Delete</button>
							</form>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="admin-evacuation-search-empty" id="evacuationCenterSearchEmpty" role="status" hidden>No centers match your search. Try a different name, barangay, or address.</p>
		<?php endif; ?>
	</section>
</div>
</div>
<script>
(function () {
	var search = document.getElementById('evacuationCenterSearch');
	var status = document.getElementById('evacuationCenterSearchStatus');
	var empty = document.getElementById('evacuationCenterSearchEmpty');
	var directory = document.querySelector('.admin-evacuation-list');
	if (!search || !directory || !status || !empty) return;

	var centers = Array.prototype.slice.call(directory.querySelectorAll('[data-evacuation-center]'));
	function filterCenters() {
		var query = search.value.trim().toLocaleLowerCase();
		var visibleCount = 0;
		centers.forEach(function (center) {
			var matches = !query || center.textContent.toLocaleLowerCase().indexOf(query) !== -1;
			center.hidden = !matches;
			if (matches) visibleCount++;
		});
		status.textContent = query
			? 'Showing ' + visibleCount + ' of ' + centers.length + ' centers'
			: 'Showing all ' + centers.length + ' centers';
		empty.hidden = visibleCount !== 0;
	}
	search.addEventListener('input', filterCenters);
})();
</script>
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
	function updateMarkerFromCoordinates() {
		var lat = Number(latitude.value);
		var lng = Number(longitude.value);
		if (latitude.value === '' || longitude.value === '' || !isFinite(lat) || !isFinite(lng) || lat < 9 || lat > 12 || lng < 123 || lng > 127) {
			if (marker) {
				map.removeLayer(marker);
				marker = null;
			}
			return;
		}
		var point = L.latLng(lat, lng);
		if (marker) marker.setLatLng(point);
		else marker = L.marker(point).addTo(map);
		map.panTo(point);
	}
	latitude.addEventListener('change', updateMarkerFromCoordinates);
	longitude.addEventListener('change', updateMarkerFromCoordinates);
})();
</script>

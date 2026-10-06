<?php defined('BASEPATH') OR exit('No direct script access allowed');
$edit = isset($edit_row) && is_array($edit_row) ? $edit_row : NULL;
$hazards = isset($hazards) && is_array($hazards) ? $hazards : array();
$hazard_types = isset($hazard_types) && is_array($hazard_types) ? $hazard_types : array();
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
$map_config = array();
foreach ($hazard_types as $key => $type)
{
	$map_config[$key] = array('label' => $type['label'], 'color' => $type['color']);
}
?>
<div class="admin-hazards-page">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="admin-split admin-split--wide">
	<section class="glass-card hazard-management-list">
		<div class="hazard-list-heading">
			<div>
				<h2>Hazard pins</h2>
				<p class="admin-muted"><span id="hazardResultCount"><?php echo count($hazards); ?></span> of <?php echo count($hazards); ?> locations</p>
			</div>
		</div>
		<div class="hazard-legend" aria-label="Hazard map color legend">
			<?php foreach ($hazard_types as $type): ?>
			<span><i style="--hazard-color: <?php echo html_escape($type['color']); ?>"></i><?php echo html_escape($type['label']); ?></span>
			<?php endforeach; ?>
		</div>
		<div class="hazard-list-tools">
			<label class="hazard-search">
				<span class="hazard-search__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.8" cy="10.8" r="6.8"></circle><path d="m16 16 5 5"></path></svg>
				</span>
				<span class="sr-only">Search hazard pins</span>
				<input type="search" id="hazardSearch" placeholder="Search name, barangay, or description" autocomplete="off"<?php if ( ! empty($hazards)): ?> aria-controls="hazardPinGroups"<?php endif; ?>>
				<kbd>/</kbd>
			</label>
			<label class="hazard-classification">
				<span>Classification</span>
				<select id="hazardClassification" aria-label="Filter by hazard classification">
					<option value="all">All classifications</option>
					<?php foreach ($hazard_types as $key => $type): ?>
					<option value="<?php echo html_escape($key); ?>"><?php echo html_escape($type['label']); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<?php if (empty($hazards)): ?>
			<p class="hazard-list-empty">No hazard pins have been added yet.</p>
		<?php else: ?>
			<div class="hazard-pin-groups" id="hazardPinGroups" aria-live="polite">
				<?php foreach ($hazard_types as $type_key => $type): ?>
					<?php
					$group_hazards = array_values(array_filter($hazards, function ($hazard) use ($type_key) {
						return isset($hazard['type']) && $hazard['type'] === $type_key;
					}));
					if (empty($group_hazards)) continue;
					usort($group_hazards, function ($left, $right) {
						return strcasecmp($left['name'], $right['name']);
					});
					?>
					<section class="hazard-pin-group" data-hazard-group data-classification="<?php echo html_escape($type_key); ?>">
						<h3 class="hazard-pin-group__heading">
							<i style="--hazard-color: <?php echo html_escape($type['color']); ?>"></i>
							<span><?php echo html_escape($type['label']); ?></span>
							<span class="hazard-pin-group__count" data-hazard-group-count><?php echo count($group_hazards); ?></span>
						</h3>
						<ul class="admin-announce-list">
							<?php foreach ($group_hazards as $hazard): ?>
								<li class="glass-card admin-announce-item" data-hazard-pin data-name="<?php echo html_escape($hazard['name']); ?>" data-barangay="<?php echo html_escape($hazard['barangay']); ?>" data-visible="<?php echo ! empty($hazard['active']) ? '1' : '0'; ?>">
									<div>
										<strong><i class="hazard-list-dot" style="--hazard-color: <?php echo html_escape($hazard['color']); ?>"></i><?php echo html_escape($hazard['name']); ?></strong>
										<p class="hazard-pin-location"><?php echo html_escape($hazard['type_label']); ?> <span aria-hidden="true">·</span> Barangay <?php echo html_escape($hazard['barangay']); ?></p>
										<?php if ($hazard['description'] !== ''): ?><p class="hazard-pin-description"><?php echo html_escape($hazard['description']); ?></p><?php endif; ?>
										<div class="hazard-pin-facts">
											<span class="hazard-pin-coordinates"><?php echo html_escape($hazard['latitude']); ?>, <?php echo html_escape($hazard['longitude']); ?></span>
											<span class="hazard-pin-status<?php echo ! empty($hazard['active']) ? ' is-visible' : ''; ?>"><?php echo ! empty($hazard['active']) ? 'Visible' : 'Hidden'; ?></span>
										</div>
									</div>
									<div class="admin-announce-actions">
										<a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/hazards?edit=' . (int) $hazard['id']); ?>">Edit</a>
										<form method="post" action="<?php echo site_url('admin/hazards'); ?>" onsubmit="return confirm('Delete this hazard pin?');">
											<input type="hidden" name="action" value="delete">
											<input type="hidden" name="id" value="<?php echo (int) $hazard['id']; ?>">
											<button class="btn btn--ghost btn--compact" type="submit">Delete</button>
										</form>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endforeach; ?>
			</div>
			<p class="hazard-search-empty" id="hazardSearchEmpty" hidden>No hazard pins match your search.</p>
		<?php endif; ?>
	</section>
	<section class="glass-card hazard-management-form">
		<div class="hazard-form-heading">
			<p class="hazard-form-step"><?php echo $edit ? 'Update location' : 'New location'; ?></p>
			<h2><?php echo $edit ? 'Edit hazard pin' : 'Add a hazard pin'; ?></h2>
			<p class="admin-muted">Mark a known hazard location. A pin marks one point, not the surveyed boundary of a hazard area.</p>
		</div>
		<form method="post" action="<?php echo site_url('admin/hazards'); ?>" class="admin-form hazard-form">
			<input type="hidden" name="id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
			<fieldset class="hazard-form-section">
				<legend>Location details</legend>
				<div class="hazard-form-grid">
					<label class="hazard-form-field--wide">Location name
						<input type="text" name="name" required maxlength="160" value="<?php echo $edit ? html_escape($edit['name']) : ''; ?>" placeholder="Low-lying riverside area">
					</label>
					<label>Barangay
						<select name="barangay" required>
							<option value="">Select a barangay</option>
							<?php foreach ($barangays as $value => $label): ?>
							<option value="<?php echo html_escape($value); ?>"<?php echo ($edit && $edit['barangay'] === $value) ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label>Hazard type
						<select name="type" id="hazardType" required>
							<?php foreach ($hazard_types as $key => $type): ?>
							<option value="<?php echo html_escape($key); ?>"<?php echo ($edit && $edit['type'] === $key) ? ' selected' : ''; ?>><?php echo html_escape($type['label']); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="hazard-form-field--wide">Description <span class="hazard-form-optional">Optional</span>
						<input type="text" name="description" maxlength="220" value="<?php echo $edit ? html_escape($edit['description']) : ''; ?>" placeholder="Nearby landmark or safety note">
					</label>
				</div>
			</fieldset>
			<fieldset class="hazard-form-section">
				<legend>Pin location</legend>
				<p class="hazard-form-help">Click the map to place the pin. You can also type coordinates directly.</p>
				<div class="hazard-form-grid">
					<label>Latitude
						<input type="number" id="hazardLatitude" name="latitude" required step="0.000001" min="9" max="12" value="<?php echo $edit ? html_escape($edit['latitude']) : ''; ?>" placeholder="10.952500">
					</label>
					<label>Longitude
						<input type="number" id="hazardLongitude" name="longitude" required step="0.000001" min="123" max="127" value="<?php echo $edit ? html_escape($edit['longitude']) : ''; ?>" placeholder="125.032200">
					</label>
				</div>
				<div id="hazardAdminMap" class="evacuation-admin-map" aria-label="Click to select hazard location"></div>
				<p class="hazard-color-preview" id="hazardColorPreview" aria-live="polite"><i></i><span></span></p>
			</fieldset>
			<div class="hazard-form-footer">
				<label class="admin-check"><input type="checkbox" name="active" value="1"<?php echo (!$edit || ! empty($edit['active'])) ? ' checked' : ''; ?>> Show on resident maps</label>
				<div class="hazard-form-actions">
					<button class="btn btn--primary" type="submit"><?php echo $edit ? 'Save changes' : 'Save hazard pin'; ?></button>
					<?php if ($edit): ?><a class="btn btn--ghost" href="<?php echo site_url('admin/hazards'); ?>">Cancel</a><?php endif; ?>
				</div>
			</div>
		</form>
	</section>
</div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
	var search = document.getElementById('hazardSearch');
	var classification = document.getElementById('hazardClassification');
	var groupContainer = document.getElementById('hazardPinGroups');
	var resultCount = document.getElementById('hazardResultCount');
	var searchEmpty = document.getElementById('hazardSearchEmpty');
	if (search && classification && groupContainer && resultCount && searchEmpty) {
		var groups = Array.prototype.slice.call(groupContainer.querySelectorAll('[data-hazard-group]'));
		var updatePins = function () {
			var query = search.value.trim().toLocaleLowerCase();
			var selectedClassification = classification.value;
			var visibleCount = 0;
			groups.forEach(function (group) {
				var visibleInGroup = 0;
				group.querySelectorAll('[data-hazard-pin]').forEach(function (pin) {
					var matches = (selectedClassification === 'all' || group.dataset.classification === selectedClassification)
						&& pin.textContent.toLocaleLowerCase().indexOf(query) !== -1;
					pin.hidden = !matches;
					if (matches) visibleInGroup += 1;
				});
				group.hidden = visibleInGroup === 0;
				group.querySelector('[data-hazard-group-count]').textContent = visibleInGroup;
				visibleCount += visibleInGroup;
			});
			resultCount.textContent = visibleCount;
			searchEmpty.hidden = visibleCount !== 0;
		};
		search.addEventListener('input', updatePins);
		classification.addEventListener('change', updatePins);
		document.addEventListener('keydown', function (event) {
			if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey
				&& !/INPUT|TEXTAREA|SELECT/.test(event.target.tagName) && !event.target.isContentEditable) {
				event.preventDefault();
				search.focus();
			}
			if (event.key === 'Escape' && document.activeElement === search && search.value !== '') {
				search.value = '';
				updatePins();
			}
		});
	}

	var mapElement = document.getElementById('hazardAdminMap');
	var latitude = document.getElementById('hazardLatitude');
	var longitude = document.getElementById('hazardLongitude');
	var typeSelect = document.getElementById('hazardType');
	var preview = document.getElementById('hazardColorPreview');
	var types = <?php echo json_encode($map_config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
	if (!mapElement || !window.L || !latitude || !longitude || !typeSelect || !preview) return;
	var hasSavedPoint = latitude.value !== '' && longitude.value !== '';
	var initial = [Number(latitude.value) || 10.9525, Number(longitude.value) || 125.0322];
	var map = L.map(mapElement).setView(initial, latitude.value ? 16 : 13);
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
	var marker = null;
	function selectedType() {
		return types[typeSelect.value] || types.flood;
	}
	function paintMarker(point) {
		var type = selectedType();
		if (marker) marker.setLatLng(point).setStyle({ color: '#fff', fillColor: type.color });
		else marker = L.circleMarker(point, { radius: 9, color: '#fff', weight: 2, fillColor: type.color, fillOpacity: 0.95 }).addTo(map);
	}
	function syncManualCoordinates() {
		var lat = Number(latitude.value);
		var lng = Number(longitude.value);
		if (latitude.value === '' || longitude.value === '' || !isFinite(lat) || !isFinite(lng)
			|| lat < 9 || lat > 12 || lng < 123 || lng > 127) return;
		initial = [lat, lng];
		paintMarker(initial);
	}
	function updatePreview() {
		var type = selectedType();
		var dot = preview.querySelector('i');
		var label = preview.querySelector('span');
		if (dot) dot.style.setProperty('--hazard-color', type.color);
		if (label) label.textContent = type.label + ' marker color';
		syncManualCoordinates();
	}
	if (hasSavedPoint) paintMarker(initial);
	map.on('click', function (event) {
		latitude.value = event.latlng.lat.toFixed(6);
		longitude.value = event.latlng.lng.toFixed(6);
		initial = [event.latlng.lat, event.latlng.lng];
		paintMarker(initial);
	});
	latitude.addEventListener('input', syncManualCoordinates);
	longitude.addEventListener('input', syncManualCoordinates);
	typeSelect.addEventListener('change', updatePreview);
	updatePreview();
})();
</script>

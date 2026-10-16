<?php defined('BASEPATH') OR exit('No direct script access allowed');
$centers = (isset($centers) && is_array($centers)) ? $centers : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="description" content="Resident evacuation centers for Dulag, Leyte.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20261007-responsive4">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261014-hazard-popup1">
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="portal-page resident-portal evacuation-page">
	<a class="skip-link" href="#main">Skip to content</a>

	<div class="gov-bar">
		<div class="gov-bar__inner">
			<span>Republic of the Philippines · Municipality of Dulag, Leyte</span>
			<span>Municipal Disaster Risk Reduction and Management Office</span>
		</div>
	</div>

	<header class="topbar" id="topbar">
		<div class="topbar__inner">
			<a class="brand" href="<?php echo html_escape($portal_url); ?>" aria-label="Back to Daguitan resident portal">
				<span class="brand__mark" aria-hidden="true">
					<img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40">
				</span>
				<span class="brand__text">
					<span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span>
					<span class="brand__name brand__name--mobile">Daguitan Monitor</span>
				</span>
			</a>
			<div class="topbar__actions">
				<?php $this->load->view('partials/notifications_bell'); ?>
				<span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span>
				<a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
				<button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>

	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation">
			<div class="resident-sidebar__profile">
				<button class="resident-sidebar__avatar-button" type="button" id="profileImageButton" aria-label="Choose profile image" title="Choose profile image">
					<img class="resident-sidebar__avatar resident-sidebar__avatar--logo" id="profileImage" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="">
				</button>
				<input class="resident-sidebar__file" type="file" id="profileImageInput" accept="image/*" hidden>
				<div>
					<strong><?php echo html_escape($auth_name); ?></strong>
					<span>Resident account</span>
				</div>
			</div>
			<nav class="resident-sidebar__nav" aria-label="Resident portal">
				<a href="<?php echo html_escape($portal_url); ?>">Dashboard</a>
				<a href="<?php echo html_escape(site_url('portal/go-bag')); ?>">Go Bag</a>
				<a class="is-active" href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>" aria-current="page">Evacuation Centers</a>
				<a href="<?php echo html_escape(site_url('portal/announcements')); ?>">Announcements</a>
				<a href="<?php echo html_escape(site_url('portal/reports')); ?>">Flood / Hazard Reports</a>
				<a href="<?php echo html_escape(site_url('portal/profile')); ?>">My Profile</a>
				<a href="<?php echo html_escape(site_url('portal/help')); ?>">Help / How to Use</a>
			</nav>
			<a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			<div class="resident-sidebar__government">
				<span>Republic of the Philippines</span>
				<strong>Municipality of Dulag, Leyte</strong>
			</div>
		</aside>
		<div class="resident-layout__content">
		<section class="hero evacuation-page__hero">
			<div class="hero__copy reveal">
				<p class="eyebrow">Resident safety · Dulag, Leyte</p>
				<h1>Evacuation Centers</h1>
				<p class="lede">Browse listed centers, get directions, and call the listed contact before traveling. Follow the evacuation center assigned by barangay officials.</p>
			</div>
			<?php if ( ! empty($centers)): $featured_center = $centers[0]; ?>
			<article class="glass-card announce-card announce-card--yellow reveal" aria-labelledby="featuredCenterTitle">
				<div class="announce-card__head">
					<div>
						<p class="card-kicker">Featured listed center</p>
						<h2 id="featuredCenterTitle"><?php echo html_escape($featured_center['name']); ?></h2>
						<p class="issuer">Barangay <?php echo html_escape($featured_center['barangay']); ?></p>
					</div>
					<span class="pill">Availability not live</span>
				</div>
				<p><?php echo html_escape($featured_center['address']); ?></p>
				<p class="issuer">Availability is not updated live. Call the listed contact and follow the center assigned by barangay officials.</p>
			</article>
			<?php endif; ?>
		</section>

		<section class="section section--tight evacuation-page__map-section" id="locationMap" aria-labelledby="evacuationMapTitle">
			<div class="glass-card evacuation-map">
				<div class="evacuation-map__head">
					<h2 id="evacuationMapTitle">Hazards &amp; evacuation centers</h2>
					<p>Use the map to see official evacuation centers and reported hazards across Dulag. Choose Street map, Satellite, or Terrain from the map selector.</p>
				</div>
				<div class="hazard-legend" aria-label="Hazard map color legend">
					<?php foreach ((isset($hazard_types) && is_array($hazard_types)) ? $hazard_types : array() as $type): ?>
					<span><i style="--hazard-color: <?php echo html_escape($type['color']); ?>"></i><?php echo html_escape($type['label']); ?></span>
					<?php endforeach; ?>
					<span class="hazard-legend__evacuation"><i></i>Evacuation center</span>
				</div>
				<p class="evacuation-map__status" id="evacuationMapStatus" role="status" aria-live="polite">Map is optional. Search and choose a location from the list below if map tiles are slow or unavailable.</p>
				<div class="map-style-switcher" id="evacuationMapStylePicker"></div>
				<div id="evacuationMap" class="evacuation-map__canvas" role="region" tabindex="0" aria-label="Map of hazards and evacuation centers"></div>
			</div>
		</section>

		<section class="section section--tight evacuation-page__centers" id="centerLocations" aria-labelledby="centerListTitle">
			<div class="section__head">
				<div>
					<p class="card-kicker">MDRRMO directory</p>
					<h2 id="centerListTitle">Evacuation center locations</h2>
				</div>
				<p>Availability and capacity are not updated live. Confirm with the listed contact and follow your barangay’s assigned center.</p>
			</div>
			<label class="evacuation-search" for="centerSearch"><span>Search locations</span>
				<input id="centerSearch" type="search" inputmode="search" autocomplete="off" placeholder="Search by center, barangay, or address">
			</label>
			<div class="evacuation-distance-tools">
				<button class="btn btn--ghost btn--compact" id="showCenterDistances" type="button">Show distances from my location</button>
				<p id="centerDistanceStatus" role="status" aria-live="polite">Location is requested only if you choose the button. Distances are approximate and straight-line, not travel routes.</p>
			</div>
			<p class="evacuation-search__status" id="centerSearchStatus" role="status" aria-live="polite"><?php echo count($centers); ?> <?php echo count($centers) === 1 ? 'location' : 'locations'; ?> listed</p>
			<div class="evacuation-grid" id="evacuationCenterList">
				<?php foreach ($centers as $index => $center): ?>
				<article class="glass-card evacuation-card<?php echo $index === 0 ? ' evacuation-card--featured' : ''; ?>" data-center-lat="<?php echo html_escape((string) (float) $center['latitude']); ?>" data-center-lng="<?php echo html_escape((string) (float) $center['longitude']); ?>" data-center-search="<?php echo html_escape($center['name'] . ' ' . $center['barangay'] . ' ' . $center['address']); ?>">
					<div class="evacuation-card__heading">
						<div>
							<p class="card-kicker evacuation-card__rank"<?php echo $index === 0 ? '' : ' hidden'; ?>><?php echo $index === 0 ? 'Featured listed center' : ''; ?></p>
							<h3><?php echo html_escape($center['name']); ?></h3>
							<p class="evacuation-card__barangay">Barangay <?php echo html_escape($center['barangay']); ?></p>
						</div>
						<span class="evacuation-card__marker" aria-hidden="true"><?php echo sprintf('%02d', $index + 1); ?></span>
					</div>
					<dl class="evacuation-card__details">
						<div><dt>Address</dt><dd><?php echo html_escape($center['address']); ?></dd></div>
						<div class="evacuation-card__distance" hidden><dt>Approx. straight-line distance</dt><dd></dd></div>
						<div><dt>Capacity</dt><dd><?php echo html_escape($center['capacity']); ?></dd></div>
					</dl>
					<div class="evacuation-card__actions">
						<a class="btn btn--primary btn--compact" href="<?php echo html_escape($center['maps']); ?>" target="_blank" rel="noopener" data-current-location-directions>Directions</a>
						<?php if ( ! empty($center['phone_link'])): ?><a class="btn btn--ghost btn--compact" href="<?php echo html_escape($center['phone_link']); ?>">Call listed contact</a><?php endif; ?>
					</div>
					<p class="evacuation-card__directions-status" hidden role="status" aria-live="polite"></p>
				</article>
			<?php endforeach; ?>
			</div>
			<p class="evacuation-empty" id="evacuationSearchEmpty" hidden>No evacuation centers match that search. Try another center name, barangay, or address.</p>
		</section>
		</div>
	</main>
	<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
	<footer class="footer portal-footer">
		<div class="portal-footer__inner">
			<div class="footer__grid">
				<div>
					<p class="footer__brand"><img class="footer__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="36" height="36"><span>DAGUITAN FLOOD MONITOR</span></p>
					<p>Resident evacuation information for Dulag, Leyte.</p>
				</div>
				<nav aria-label="Footer">
					<a href="<?php echo html_escape($portal_url); ?>">Resident portal</a>
					<a href="<?php echo html_escape($logout_url); ?>">Sign out</a>
				</nav>
			</div>
			<p class="footer__note">In an emergency, follow MDRRMO and barangay instructions.</p>
		</div>
	</footer>
	<script>window.DAGUITAN_NOTIFY = <?php echo json_encode($notify_config, JSON_UNESCAPED_SLASHES); ?>;</script>
	<script src="<?php echo html_escape($asset_url); ?>js/notifications.js?v=20261016-timely1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=20261007-mobile-plus"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-profile.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-mobile.js?v=20261012-evacuation-nav-removed1"></script>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-map-layers.js?v=20261012-map-layout2"></script>
	<script>
		(function () {
			var centers = <?php echo json_encode($centers, JSON_UNESCAPED_SLASHES); ?>;
			var hazards = <?php echo json_encode(isset($hazards) ? $hazards : array(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			var hazardImageBase = <?php echo json_encode(site_url('hazard-image/'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
			var directionLinks = document.querySelectorAll('[data-current-location-directions]');
			Array.prototype.forEach.call(directionLinks, function (link) {
				var card = link.closest('.evacuation-card');
				var status = card ? card.querySelector('.evacuation-card__directions-status') : null;
				var setStatus = function (message) {
					if (!status) return;
					status.textContent = message;
					status.hidden = false;
				};

				link.addEventListener('click', function (event) {
					event.preventDefault();
					setStatus('Requesting your location to create directions...');
					var directionsTab = window.open('about:blank', '_blank');
					if (!directionsTab) {
						setStatus('A new tab could not be opened. Opening directions to the center.');
						window.location.assign(link.href);
						return;
					}
					directionsTab.opener = null;
					directionsTab.document.title = 'Getting your location';
					directionsTab.document.body.textContent = 'Allow location access to get directions from your current position.';

					if (!navigator.geolocation) {
						setStatus('Location access is unavailable. Opening directions to the center.');
						directionsTab.location.replace(link.href);
						return;
					}

					navigator.geolocation.getCurrentPosition(function (position) {
						var directionsUrl = new URL(link.href);
						directionsUrl.searchParams.set('origin', position.coords.latitude + ',' + position.coords.longitude);
						directionsUrl.searchParams.set('dir_action', 'navigate');
						setStatus('Directions are starting from your current location.');
						directionsTab.location.replace(directionsUrl.toString());
					}, function () {
						setStatus('Location permission was not granted. Opening directions without a confirmed starting point.');
						directionsTab.location.replace(link.href);
					}, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
				});
			});

			var distanceButton = document.getElementById('showCenterDistances');
			var distanceStatus = document.getElementById('centerDistanceStatus');
			var centerList = document.getElementById('evacuationCenterList');
			if (distanceButton && distanceStatus && centerList) {
				distanceButton.addEventListener('click', function () {
					if (!navigator.geolocation) {
						distanceStatus.textContent = 'Location access is unavailable in this browser. You can still browse and search the center list.';
						return;
					}
					distanceButton.disabled = true;
					distanceStatus.textContent = 'Waiting for your location permission to estimate distances...';
					navigator.geolocation.getCurrentPosition(function (position) {
						var latitude = position.coords.latitude;
						var longitude = position.coords.longitude;
						var cards = Array.prototype.slice.call(centerList.querySelectorAll('.evacuation-card'));
						var locatedCards = cards.map(function (card) {
							var centerLatitude = Number(card.getAttribute('data-center-lat'));
							var centerLongitude = Number(card.getAttribute('data-center-lng'));
							if (!Number.isFinite(centerLatitude) || !Number.isFinite(centerLongitude)) return null;
							var radians = Math.PI / 180;
							var latDiff = (centerLatitude - latitude) * radians;
							var lngDiff = (centerLongitude - longitude) * radians;
							var a = Math.sin(latDiff / 2) * Math.sin(latDiff / 2)
								+ Math.cos(latitude * radians) * Math.cos(centerLatitude * radians)
								* Math.sin(lngDiff / 2) * Math.sin(lngDiff / 2);
							var distance = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
							return { card: card, distance: distance };
						});
						var missingCoordinates = locatedCards.filter(function (item) { return item === null; }).length;
						locatedCards = locatedCards.filter(function (item) { return item !== null; });
						locatedCards.sort(function (a, b) { return a.distance - b.distance; });
						locatedCards.forEach(function (item, index) {
							var distanceRow = item.card.querySelector('.evacuation-card__distance');
							var distanceValue = distanceRow ? distanceRow.querySelector('dd') : null;
							if (distanceValue) {
								distanceValue.textContent = item.distance < 1
									? Math.round(item.distance * 1000 / 50) * 50 + ' m'
									: item.distance.toFixed(1) + ' km';
								distanceRow.hidden = false;
							}
							var rank = item.card.querySelector('.evacuation-card__rank');
							if (rank) {
								rank.textContent = index === 0 ? 'Nearest by straight-line distance' : '';
								rank.hidden = index !== 0;
							}
							item.card.classList.toggle('evacuation-card--featured', index === 0);
							var marker = item.card.querySelector('.evacuation-card__marker');
							if (marker) marker.textContent = String(index + 1).padStart(2, '0');
							centerList.appendChild(item.card);
						});
						distanceStatus.textContent = locatedCards.length
							? 'Centers are sorted nearest to farthest by approximate straight-line distance. Your location is used only in this browser and is not saved. Follow the center assigned by barangay officials.'
							: 'No evacuation centers have usable coordinates for distance estimates.';
						if (missingCoordinates) {
							distanceStatus.textContent += ' ' + missingCoordinates + ' center(s) without coordinates remain unsorted.';
						}
						distanceButton.textContent = 'Update distances from my location';
						distanceButton.disabled = false;
					}, function (error) {
						var message = error.code === error.PERMISSION_DENIED
							? 'Location permission was denied. You can still browse and search the center list.'
							: (error.code === error.TIMEOUT
								? 'The location request timed out. Try again or browse the center list.'
								: 'Your location could not be determined. Try again or browse the center list.');
						distanceStatus.textContent = message;
						distanceButton.disabled = false;
					}, { enableHighAccuracy: false, timeout: 12000, maximumAge: 60000 });
				});
			}

			var mapElement = document.getElementById('evacuationMap');
			if (!mapElement || !window.L) return;
			var map = L.map(mapElement).setView([10.9525, 125.0322], 13);
			var mapLayers = window.DaguitanMapLayers.add(map, document.getElementById('evacuationMapStylePicker'));
			mapLayers.forEach(function (layer) {
				layer.on('tileerror', function () {
				var status = document.getElementById('evacuationMapStatus');
				if (status) status.textContent = 'Map tiles could not be loaded. Use the searchable evacuation-center list below for directions and contact details.';
				});
			});
			var bounds = [];
			centers.forEach(function (center) {
				var point = [Number(center.latitude), Number(center.longitude)];
				bounds.push(point);
				L.marker(point).addTo(map).bindPopup('<strong>' + center.name + '</strong><br>Barangay ' + center.barangay + '<br>' + center.address);
			});
			hazards.forEach(function (hazard) {
				var point = [Number(hazard.latitude), Number(hazard.longitude)];
				bounds.push(point);
				var popup = document.createElement('div');
				popup.className = 'hazard-pin-popup';
				if (typeof hazard.image === 'string' && /^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/.test(hazard.image)) {
					var photo = document.createElement('img');
					photo.className = 'hazard-pin-popup__image';
					photo.src = hazardImageBase + hazard.image;
					photo.alt = 'Photo of ' + hazard.name;
					photo.loading = 'lazy';
					popup.appendChild(photo);
				}
				var name = document.createElement('strong');
				name.textContent = hazard.name;
				var detail = document.createElement('div');
				detail.textContent = hazard.type_label + ' · Barangay ' + hazard.barangay + (hazard.description ? ' · ' + hazard.description : '');
				popup.appendChild(name);
				popup.appendChild(detail);
				L.circleMarker(point, { radius: 9, color: '#fff', weight: 2, fillColor: hazard.color, fillOpacity: 0.95 }).addTo(map).bindPopup(popup);
			});
			if (bounds.length > 1) map.fitBounds(bounds, { padding: [28, 28] });
		})();
	</script>
</body>
</html>

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
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20260924d">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261006c">
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
				<p class="lede">Find the nearest listed center, get directions, and call MDRRMO before traveling. Follow the evacuation center assigned by barangay officials if it differs.</p>
				<div class="hero__actions">
					<a class="btn btn--primary" href="<?php echo html_escape($portal_url); ?>">Back to resident portal</a>
				</div>
			</div>
			<?php if ( ! empty($centers)): $nearest = $centers[0]; ?>
			<article class="glass-card announce-card announce-card--yellow reveal" aria-labelledby="nearestCenterTitle">
				<div class="announce-card__head">
					<div>
						<p class="card-kicker">Nearest listed center</p>
						<h2 id="nearestCenterTitle"><?php echo html_escape($nearest['name']); ?></h2>
						<p class="issuer">Barangay <?php echo html_escape($nearest['barangay']); ?></p>
					</div>
					<span class="pill">Ready</span>
				</div>
				<p class="announce-card__empty"><?php echo html_escape($nearest['distance']); ?></p>
				<p><?php echo html_escape($nearest['address']); ?></p>
				<p class="issuer">Confirm capacity with MDRRMO before traveling.</p>
			</article>
			<?php endif; ?>
		</section>

		<section class="section section--tight" aria-labelledby="evacuationMapTitle">
			<div class="glass-card evacuation-map">
				<div class="evacuation-map__head">
					<h2 id="evacuationMapTitle">Hazards &amp; evacuation centers</h2>
					<p>Official evacuation pins and colored known-hazard locations maintained by MDRRMO Dulag.</p>
				</div>
				<div class="hazard-legend" aria-label="Hazard map color legend">
					<?php foreach ((isset($hazard_types) && is_array($hazard_types)) ? $hazard_types : array() as $type): ?>
					<span><i style="--hazard-color: <?php echo html_escape($type['color']); ?>"></i><?php echo html_escape($type['label']); ?></span>
					<?php endforeach; ?>
					<span class="hazard-legend__evacuation"><i></i>Evacuation center</span>
				</div>
				<div id="evacuationMap" class="evacuation-map__canvas" aria-label="Map of hazards and evacuation centers"></div>
			</div>
		</section>

		<section class="section section--tight evacuation-page__centers" aria-labelledby="centerListTitle">
			<div class="section__head">
				<h2 id="centerListTitle">Available locations</h2>
				<p>Center capacity can change during an active emergency. Confirm availability before you leave.</p>
			</div>
			<div class="evacuation-grid">
				<?php foreach ($centers as $index => $center): ?>
				<article class="glass-card evacuation-card<?php echo $index === 0 ? ' evacuation-card--nearest' : ''; ?>">
					<?php if ($index === 0): ?><p class="card-kicker">Nearest listed center</p><?php endif; ?>
					<h3><?php echo html_escape($center['name']); ?></h3>
					<p class="card-kicker">Barangay <?php echo html_escape($center['barangay']); ?></p>
					<dl class="evacuation-card__details">
						<div><dt>Address</dt><dd><?php echo html_escape($center['address']); ?></dd></div>
						<div><dt>Distance</dt><dd><?php echo html_escape($center['distance']); ?></dd></div>
						<div><dt>Capacity</dt><dd><?php echo html_escape($center['capacity']); ?></dd></div>
					</dl>
					<div class="evacuation-card__actions">
						<a class="btn btn--primary btn--compact" href="<?php echo html_escape($center['maps']); ?>" target="_blank" rel="noopener">Directions</a>
						<a class="btn btn--ghost btn--compact" href="<?php echo html_escape($center['phone_link']); ?>">Call MDRRMO</a>
					</div>
				</article>
			<?php endforeach; ?>
			</div>
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
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=1"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-profile.js?v=1"></script>
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
	<script>
		(function () {
			var centers = <?php echo json_encode($centers, JSON_UNESCAPED_SLASHES); ?>;
			var hazards = <?php echo json_encode(isset($hazards) ? $hazards : array(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
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

			var mapElement = document.getElementById('evacuationMap');
			if (!mapElement || !window.L) return;
			var map = L.map(mapElement).setView([10.9525, 125.0322], 13);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; OpenStreetMap contributors',
				maxZoom: 19
			}).addTo(map);
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

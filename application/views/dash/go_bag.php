<?php defined('BASEPATH') OR exit('No direct script access allowed');
$portal_url = isset($portal_url) ? $portal_url : site_url('portal');
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
$base_url = isset($base_url) ? $base_url : base_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="description" content="Household Go Bag checklist for flood readiness in Dulag, Leyte.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20260924d">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261006f">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="portal-page resident-portal go-bag-page">
	<a class="skip-link" href="#main">Skip to content</a>
	<div class="gov-bar"><div class="gov-bar__inner"><span>Republic of the Philippines · Municipality of Dulag, Leyte</span><span>Municipal Disaster Risk Reduction and Management Office</span></div></div>
	<header class="topbar" id="topbar">
		<div class="topbar__inner">
			<a class="brand" href="<?php echo html_escape($portal_url); ?>" aria-label="Back to Daguitan resident portal">
				<span class="brand__mark" aria-hidden="true"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span>
				<span class="brand__text"><span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span><span class="brand__name brand__name--mobile">Daguitan Monitor</span></span>
			</a>
			<div class="topbar__actions"><span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span><a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a><button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false"><span></span><span></span><span></span></button></div>
		</div>
	</header>
	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation">
			<div class="resident-sidebar__profile"><img class="resident-sidebar__avatar resident-sidebar__avatar--logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt=""><div><strong><?php echo html_escape($auth_name); ?></strong><span>Resident account</span></div></div>
			<nav class="resident-sidebar__nav" aria-label="Resident portal">
				<a href="<?php echo html_escape($portal_url); ?>">Dashboard</a>
				<a class="is-active" href="<?php echo site_url('portal/go-bag'); ?>" aria-current="page">Go Bag</a>
				<a href="<?php echo site_url('portal/evacuation-centers'); ?>">Evacuation Centers</a>
				<a href="<?php echo site_url('portal/announcements'); ?>">Announcements</a>
				<a href="<?php echo site_url('portal/reports'); ?>">Flood / Hazard Reports</a>
				<a href="<?php echo site_url('portal/profile'); ?>">My Profile</a>
				<a href="<?php echo site_url('portal/help'); ?>">Help / How to Use</a>
			</nav>
			<a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a>
			<div class="resident-sidebar__government">
				<span>Republic of the Philippines</span>
				<strong>Municipality of Dulag, Leyte</strong>
			</div>
		</aside>
		<div class="resident-layout__content">
			<section class="hero go-bag-page__hero">
				<div class="hero__copy">
					<p class="eyebrow">Resident readiness · Dulag, Leyte</p>
					<h1>Prepare your Go Bag</h1>
					<p class="lede">Keep essential supplies together so your household can move quickly when MDRRMO or barangay officials issue an evacuation instruction.</p>
				</div>
				<article class="glass-card announce-card">
					<p class="card-kicker">Before heavy rain</p>
					<h2>Keep it ready near an exit</h2>
					<p>Check medicines, batteries, drinking water, and important documents regularly.</p>
				</article>
			</section>
			<section class="go-bag-offline-callout" aria-labelledby="offlineGuideTitle">
				<div><p class="card-kicker">Works without a connection</p><h2 id="offlineGuideTitle">Keep emergency steps available offline</h2><p>Open the guide once while online so this device can keep a copy for when service is unavailable.</p></div>
				<a class="btn btn--primary" href="<?php echo html_escape(rtrim($base_url, '/') . '/offline-guide.html'); ?>">Open offline emergency guide <span aria-hidden="true">→</span></a>
				<p id="offlineGuideStatus" class="go-bag-offline-callout__status" role="status" aria-live="polite">Offline guide availability will be checked on this device.</p>
			</section>
			<section class="section section--tight go-bag-page__checklist" aria-labelledby="goBagChecklistTitle">
				<header class="section__head">
					<h2 id="goBagChecklistTitle">Household readiness checklist</h2>
					<p>Open a category and check off the supplies you have ready.</p>
				</header>
				<div class="go-bag-progress" role="status" aria-live="polite">
					<strong id="goBagReadyPercent">0%</strong>
					<span>of your checklist ready</span>
					<div class="go-bag-progress__track" role="progressbar" aria-label="Go Bag checklist completion" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
						<span id="goBagReadyBar"></span>
					</div>
				</div>
				<div class="go-bag-grid go-bag-grid--page">
					<article class="glass-card go-bag-card" data-go-bag-card>
						<button class="go-bag-card__toggle" type="button" aria-expanded="false" aria-controls="goBagDetailsDocuments">
							<span class="go-bag-card__number">01</span><span><strong>Documents</strong><small>IDs, certificates, contacts, and cash.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span>
						</button>
						<div class="go-bag-card__details" id="goBagDetailsDocuments" hidden>
							<label><input type="checkbox" data-go-bag-item> Valid IDs and certificates</label>
							<label><input type="checkbox" data-go-bag-item> Medicines list and emergency contacts</label>
							<label><input type="checkbox" data-go-bag-item> Cash in a waterproof pouch</label>
						</div>
					</article>
					<article class="glass-card go-bag-card" data-go-bag-card>
						<button class="go-bag-card__toggle" type="button" aria-expanded="false" aria-controls="goBagDetailsFood">
							<span class="go-bag-card__number">02</span><span><strong>Water and food</strong><small>Water, food, and basic utensils.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span>
						</button>
						<div class="go-bag-card__details" id="goBagDetailsFood" hidden>
							<label><input type="checkbox" data-go-bag-item> Drinking water</label>
							<label><input type="checkbox" data-go-bag-item> Ready-to-eat food</label>
							<label><input type="checkbox" data-go-bag-item> Manual can opener and utensils</label>
						</div>
					</article>
					<article class="glass-card go-bag-card" data-go-bag-card>
						<button class="go-bag-card__toggle" type="button" aria-expanded="false" aria-controls="goBagDetailsPower">
							<span class="go-bag-card__number">03</span><span><strong>Light and power</strong><small>Light, charging, and signaling items.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span>
						</button>
						<div class="go-bag-card__details" id="goBagDetailsPower" hidden>
							<label><input type="checkbox" data-go-bag-item> Flashlight and spare batteries</label>
							<label><input type="checkbox" data-go-bag-item> Power bank and charging cable</label>
							<label><input type="checkbox" data-go-bag-item> Whistle</label>
						</div>
					</article>
					<article class="glass-card go-bag-card" data-go-bag-card>
						<button class="go-bag-card__toggle" type="button" aria-expanded="false" aria-controls="goBagDetailsHealth">
							<span class="go-bag-card__number">04</span><span><strong>Health and clothing</strong><small>Medicines, hygiene, and warm clothing.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span>
						</button>
						<div class="go-bag-card__details" id="goBagDetailsHealth" hidden>
							<label><input type="checkbox" data-go-bag-item> Prescription medicines and first aid</label>
							<label><input type="checkbox" data-go-bag-item> Hygiene supplies</label>
							<label><input type="checkbox" data-go-bag-item> Change of clothes and blanket</label>
						</div>
					</article>
					<article class="glass-card go-bag-card" data-go-bag-card>
						<button class="go-bag-card__toggle" type="button" aria-expanded="false" aria-controls="goBagDetailsCommunication">
							<span class="go-bag-card__number">05</span><span><strong>Communication</strong><small>Stay connected during evacuation.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span>
						</button>
						<div class="go-bag-card__details" id="goBagDetailsCommunication" hidden>
							<label><input type="checkbox" data-go-bag-item> Charged phone</label>
							<label><input type="checkbox" data-go-bag-item> Emergency contact list</label>
							<label><input type="checkbox" data-go-bag-item> Small radio if available</label>
						</div>
					</article>
					<article class="glass-card go-bag-card" data-go-bag-card>
						<button class="go-bag-card__toggle" type="button" aria-expanded="false" aria-controls="goBagDetailsFamily">
							<span class="go-bag-card__number">06</span><span><strong>Family needs</strong><small>Supplies for each household member.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span>
						</button>
						<div class="go-bag-card__details" id="goBagDetailsFamily" hidden>
							<label><input type="checkbox" data-go-bag-item> Baby or senior-care supplies</label>
							<label><input type="checkbox" data-go-bag-item> Disability support items</label>
							<label><input type="checkbox" data-go-bag-item> Pet supplies if needed</label>
						</div>
					</article>
				</div>
			</section>
			<section class="section go-bag-family-plan" aria-labelledby="familyPlanTitle">
				<header class="family-plan__head">
					<div><p class="card-kicker">Plan together</p><h2 id="familyPlanTitle">Family evacuation plan</h2></div>
					<p>Agree on the details before an alert, so everyone knows where to go and how to reconnect.</p>
				</header>
				<div class="family-plan__notice">
					<strong>Saved only in this browser</strong>
					<span>This plan is not sent to MDRRMO or your barangay. Avoid entering details you do not want stored on this device. Downloaded or printed copies may be seen by others; clear saved data on shared devices.</span>
				</div>
				<form id="familyPlanForm" class="family-plan__form">
					<div class="family-plan__fields">
						<label class="family-plan__field" for="familyMeetingPoint">
							<span>Family meeting point</span>
							<small>Where should everyone meet if separated?</small>
							<input id="familyMeetingPoint" name="meetingPoint" type="text" maxlength="120" autocomplete="off" placeholder="Example: a safe, familiar place away from floodwater">
						</label>
						<label class="family-plan__field" for="familyEvacuationPlace">
							<span>Evacuation destination</span>
							<small>Use the location assigned by barangay officials.</small>
							<input id="familyEvacuationPlace" name="evacuationPlace" type="text" maxlength="120" autocomplete="off" placeholder="Enter your assigned evacuation location">
						</label>
						<label class="family-plan__field" for="familyContactName">
							<span>Out-of-area contact <em>(optional)</em></span>
							<small>A relative or trusted person your household can check in with.</small>
							<input id="familyContactName" name="contactName" type="text" maxlength="80" autocomplete="off" placeholder="Contact name">
						</label>
						<label class="family-plan__field" for="familyContactPhone">
							<span>Contact phone <em>(optional)</em></span>
							<small>Share the number with household members.</small>
							<input id="familyContactPhone" name="contactPhone" type="tel" maxlength="32" autocomplete="off" placeholder="Phone number">
						</label>
					</div>
					<fieldset class="family-plan__support">
						<legend>Does anyone need extra help evacuating?</legend>
						<p>Select only the support types you want to include in this private plan.</p>
						<div class="family-plan__support-options">
							<label><input type="checkbox" name="supportNeeds" value="Young children or infants"> Young children or infants</label>
							<label><input type="checkbox" name="supportNeeds" value="Older adults"> Older adults</label>
							<label><input type="checkbox" name="supportNeeds" value="Mobility or accessibility support"> Mobility or accessibility support</label>
							<label><input type="checkbox" name="supportNeeds" value="Medication or health supplies"> Medication or health supplies</label>
							<label><input type="checkbox" name="supportNeeds" value="Pets or service animals"> Pets or service animals</label>
						</div>
					</fieldset>
					<div class="family-plan__actions">
						<button class="btn btn--primary" type="submit">Save &amp; mark reviewed</button>
						<button class="btn btn--ghost" id="familyPlanDownload" type="button">Download a copy</button>
						<button class="btn btn--ghost" id="familyPlanPrint" type="button">Print plan</button>
						<button class="family-plan__clear" id="familyPlanClear" type="button">Clear saved plan</button>
					</div>
					<p class="family-plan__review" id="familyPlanReview" role="status" aria-live="polite">Save your plan after reviewing it with your household to start the 90-day review reminder. Update it sooner whenever contacts, destinations, or support needs change.</p>
					<p class="family-plan__status" id="familyPlanStatus" role="status" aria-live="polite">Plan details have not been saved on this device.</p>
				</form>
			</section>
			<section class="section go-bag-page__note">
				<div class="glass-card">
					<h2>Before you leave</h2>
					<p>Follow MDRRMO and barangay instructions. Bring your Go Bag, lock your home, turn off electricity only if safe, and never cross moving floodwater.</p>
				</div>
			</section>
		</div>
	</main>
	<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
	<footer class="footer portal-footer"><div class="portal-footer__inner"><div class="footer__grid"><div><p class="footer__brand"><img class="footer__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="36" height="36"><span>DAGUITAN FLOOD MONITOR</span></p><p>Resident readiness information for Dulag, Leyte.</p></div><nav aria-label="Footer"><a href="<?php echo html_escape($portal_url); ?>">Resident portal</a><a href="<?php echo html_escape($logout_url); ?>">Sign out</a></nav></div><p class="footer__note">In an emergency, follow MDRRMO and barangay instructions.</p></div></footer>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=1"></script>
	<script>
		(function () {
			if (!('serviceWorker' in navigator)) {
				document.getElementById('offlineGuideStatus').textContent = 'This browser does not support offline guide storage. Open or print the guide while connected.';
				return;
			}
			var guideStatus = document.getElementById('offlineGuideStatus');
			var workerUrl = <?php echo json_encode(rtrim($base_url, '/') . '/sw.js', JSON_UNESCAPED_SLASHES); ?>;
			navigator.serviceWorker.register(workerUrl).then(function (registration) {
				if (registration.active) {
					guideStatus.textContent = 'Offline support is active for this site. Open the guide while online and wait for it to load before relying on it offline.';
				} else {
					guideStatus.textContent = 'Preparing offline guide storage. Keep this page open briefly while connected.';
				}
			}).catch(function () {
				guideStatus.textContent = 'Could not prepare offline guide storage. Open or print the guide while connected.';
			});
		})();
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/go-bag-plan.js?v=20261006b"></script>
	<script>
		(function () {
			var cards = document.querySelectorAll('[data-go-bag-card]');
			var items = document.querySelectorAll('[data-go-bag-item]');
			var percent = document.getElementById('goBagReadyPercent');
			var bar = document.getElementById('goBagReadyBar');
			function updateProgress() {
				var checked = document.querySelectorAll('[data-go-bag-item]:checked').length;
				var value = items.length ? Math.round((checked / items.length) * 100) : 0;
				percent.textContent = value + '%';
				bar.style.width = value + '%';
				bar.parentElement.setAttribute('aria-valuenow', value);
			}
			cards.forEach(function (card) {
				var toggle = card.querySelector('.go-bag-card__toggle');
				var details = card.querySelector('.go-bag-card__details');
				var chevron = card.querySelector('.go-bag-card__chevron');
				toggle.addEventListener('click', function () {
					var open = details.hidden;
					details.hidden = !open;
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
					chevron.textContent = open ? '-' : '+';
				});
			});
			items.forEach(function (item) { item.addEventListener('change', updateProgress); });
			updateProgress();
		})();
	</script>
</body>
</html>

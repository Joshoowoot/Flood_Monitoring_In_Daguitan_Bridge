<?php defined('BASEPATH') OR exit('No direct script access allowed');
$portal_url = isset($portal_url) ? $portal_url : site_url('portal');
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
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
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20260925z">
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
			<div class="topbar__actions"><span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span><a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a></div>
		</div>
	</header>
	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" aria-label="Resident portal navigation">
			<div class="resident-sidebar__profile"><img class="resident-sidebar__avatar resident-sidebar__avatar--logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt=""><div><strong><?php echo html_escape($auth_name); ?></strong><span>Resident account</span></div></div>
			<nav class="resident-sidebar__nav" aria-label="Resident portal">
				<a href="<?php echo html_escape($portal_url); ?>">Dashboard</a>
				<a class="is-active" href="<?php echo site_url('portal/go-bag'); ?>" aria-current="page">Go Bag</a>
				<a href="<?php echo site_url('portal/evacuation-centers'); ?>">Evacuation Centers</a>
				<a href="<?php echo site_url('portal/announcements'); ?>">Announcements</a>
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
				<div class="hero__copy"><p class="eyebrow">Resident readiness · Dulag, Leyte</p><h1>Prepare your Go Bag</h1><p class="lede">Keep essential supplies together so your household can move quickly when MDRRMO or barangay officials issue an evacuation instruction.</p></div>
				<article class="glass-card announce-card"><p class="card-kicker">Before heavy rain</p><h2>Keep it ready near an exit</h2><p>Check medicines, batteries, drinking water, and important documents regularly.</p></article>
			</section>
			<section class="section section--tight" aria-labelledby="goBagChecklistTitle">
				<header class="section__head"><h2 id="goBagChecklistTitle">Checklist</h2><p>Click a category to check what is ready.</p></header>
				<div class="go-bag-progress" role="status" aria-live="polite"><strong id="goBagReadyPercent">0%</strong><span>ready</span><div class="go-bag-progress__track"><span id="goBagReadyBar"></span></div></div>
				<div class="go-bag-grid go-bag-grid--page">
					<article class="glass-card go-bag-card" data-go-bag-card><button class="go-bag-card__toggle" type="button" aria-expanded="false"><span class="go-bag-card__number">01</span><span><strong>Documents</strong><small>IDs, certificates, contacts, and cash.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span></button><div class="go-bag-card__details" hidden><label><input type="checkbox" data-go-bag-item> Valid IDs and certificates</label><label><input type="checkbox" data-go-bag-item> Medicines list and emergency contacts</label><label><input type="checkbox" data-go-bag-item> Cash in a waterproof pouch</label></div></article>
					<article class="glass-card go-bag-card" data-go-bag-card><button class="go-bag-card__toggle" type="button" aria-expanded="false"><span class="go-bag-card__number">02</span><span><strong>Water and food</strong><small>Water, food, and basic utensils.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span></button><div class="go-bag-card__details" hidden><label><input type="checkbox" data-go-bag-item> Drinking water</label><label><input type="checkbox" data-go-bag-item> Ready-to-eat food</label><label><input type="checkbox" data-go-bag-item> Manual can opener and utensils</label></div></article>
					<article class="glass-card go-bag-card" data-go-bag-card><button class="go-bag-card__toggle" type="button" aria-expanded="false"><span class="go-bag-card__number">03</span><span><strong>Light and power</strong><small>Light, charging, and signaling items.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span></button><div class="go-bag-card__details" hidden><label><input type="checkbox" data-go-bag-item> Flashlight and spare batteries</label><label><input type="checkbox" data-go-bag-item> Power bank and charging cable</label><label><input type="checkbox" data-go-bag-item> Whistle</label></div></article>
					<article class="glass-card go-bag-card" data-go-bag-card><button class="go-bag-card__toggle" type="button" aria-expanded="false"><span class="go-bag-card__number">04</span><span><strong>Health and clothing</strong><small>Medicines, hygiene, and warm clothing.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span></button><div class="go-bag-card__details" hidden><label><input type="checkbox" data-go-bag-item> Prescription medicines and first aid</label><label><input type="checkbox" data-go-bag-item> Hygiene supplies</label><label><input type="checkbox" data-go-bag-item> Change of clothes and blanket</label></div></article>
					<article class="glass-card go-bag-card" data-go-bag-card><button class="go-bag-card__toggle" type="button" aria-expanded="false"><span class="go-bag-card__number">05</span><span><strong>Communication</strong><small>Stay connected during evacuation.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span></button><div class="go-bag-card__details" hidden><label><input type="checkbox" data-go-bag-item> Charged phone</label><label><input type="checkbox" data-go-bag-item> Emergency contact list</label><label><input type="checkbox" data-go-bag-item> Small radio if available</label></div></article>
					<article class="glass-card go-bag-card" data-go-bag-card><button class="go-bag-card__toggle" type="button" aria-expanded="false"><span class="go-bag-card__number">06</span><span><strong>Family needs</strong><small>Supplies for each household member.</small></span><span class="go-bag-card__chevron" aria-hidden="true">+</span></button><div class="go-bag-card__details" hidden><label><input type="checkbox" data-go-bag-item> Baby or senior-care supplies</label><label><input type="checkbox" data-go-bag-item> Disability support items</label><label><input type="checkbox" data-go-bag-item> Pet supplies if needed</label></div></article>
				</div>
			</section>
			<section class="section go-bag-page__note">
				<div class="glass-card">
					<h2>Before you leave</h2>
					<p>Follow MDRRMO and barangay instructions. Bring your Go Bag, lock your home, turn off electricity only if safe, and never cross moving floodwater.</p>
				</div>
			</section>
		</div>
	</main>
	<footer class="footer portal-footer"><div class="portal-footer__inner"><div class="footer__grid"><div><p class="footer__brand"><img class="footer__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="36" height="36"><span>DAGUITAN FLOOD MONITOR</span></p><p>Resident readiness information for Dulag, Leyte.</p></div><nav aria-label="Footer"><a href="<?php echo html_escape($portal_url); ?>">Resident portal</a><a href="<?php echo html_escape($logout_url); ?>">Sign out</a></nav></div><p class="footer__note">In an emergency, follow MDRRMO and barangay instructions.</p></div></footer>
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

<?php defined('BASEPATH') OR exit('No direct script access allowed');
$portal_url = isset($portal_url) ? $portal_url : site_url('portal');
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
$auth_phone = trim((string) $auth_phone);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224"><title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20260924d">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20260925z">
</head>
<body class="portal-page resident-portal profile-page">
	<a class="skip-link" href="#main">Skip to content</a>
	<div class="gov-bar"><div class="gov-bar__inner"><span>Republic of the Philippines · Municipality of Dulag, Leyte</span><span>Municipal Disaster Risk Reduction and Management Office</span></div></div>
	<header class="topbar"><div class="topbar__inner"><a class="brand" href="<?php echo html_escape($portal_url); ?>"><span class="brand__mark"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span><span class="brand__text"><span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span><span class="brand__name brand__name--mobile">Daguitan Monitor</span></span></a><div class="topbar__actions"><span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span><a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a></div></div></header>
	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" aria-label="Resident portal navigation"><div class="resident-sidebar__profile"><img class="resident-sidebar__avatar resident-sidebar__avatar--logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt=""><div><strong><?php echo html_escape($auth_name); ?></strong><span>Resident account</span></div></div><nav class="resident-sidebar__nav" aria-label="Resident portal"><a href="<?php echo html_escape($portal_url); ?>">Dashboard</a><a href="<?php echo site_url('portal/go-bag'); ?>">Go Bag</a><a href="<?php echo site_url('portal/evacuation-centers'); ?>">Evacuation Centers</a><a href="<?php echo site_url('portal/announcements'); ?>">Announcements</a><a class="is-active" href="<?php echo site_url('portal/profile'); ?>" aria-current="page">My Profile</a><a href="<?php echo site_url('portal/help'); ?>">Help / How to Use</a></nav><a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a><div class="resident-sidebar__government"><span>Republic of the Philippines</span><strong>Municipality of Dulag, Leyte</strong></div></aside>
		<div class="resident-layout__content"><section class="hero profile-page__hero"><div class="hero__copy"><p class="eyebrow">Resident account</p><h1>My Profile</h1><p class="lede">Review your account details and notification preferences for the Daguitan Flood Monitor.</p></div></section><section class="section section--tight"><div class="profile-grid"><article class="glass-card profile-card"><p class="card-kicker">Account details</p><h2><?php echo html_escape($auth_name); ?></h2><dl class="profile-details"><div><dt>Username</dt><dd><?php echo html_escape($auth_user); ?></dd></div><div><dt>Mobile number</dt><dd><?php echo $auth_phone !== '' ? html_escape($auth_phone) : 'Not added'; ?></dd></div><div><dt>Account type</dt><dd>Resident</dd></div></dl></article><article class="glass-card profile-card"><p class="card-kicker">Notification preferences</p><h2>Flood updates</h2><label class="profile-preference"><input type="checkbox" checked disabled><span><strong>Flood status updates</strong><small>Receive important monitoring and advisory updates.</small></span></label><label class="profile-preference"><input type="checkbox" checked disabled><span><strong>Announcements</strong><small>Stay informed about official MDRRMO notices.</small></span></label><p class="profile-note">Preferences are managed by the resident portal account settings.</p></article></div></section></div>
	</main>
	<footer class="footer portal-footer"><div class="portal-footer__inner"><p class="footer__note">In an emergency, follow MDRRMO and barangay instructions.</p></div></footer>
</body>
</html>

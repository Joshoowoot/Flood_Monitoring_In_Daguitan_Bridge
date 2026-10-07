<?php defined('BASEPATH') OR exit('No direct script access allowed');
$portal_url = isset($portal_url) ? $portal_url : site_url('portal');
$logout_url = isset($logout_url) ? $logout_url : site_url('auth/logout');
$auth_phone = trim((string) $auth_phone);
$profile_form_values = isset($profile_form_values) && is_array($profile_form_values) ? $profile_form_values : array();
$profile_name_value = isset($profile_form_values['name']) ? $profile_form_values['name'] : $auth_name;
$profile_phone_value = isset($profile_form_values['phone']) ? $profile_form_values['phone'] : $auth_phone;
$profile_barangay_value = isset($profile_form_values['barangay']) ? $profile_form_values['barangay'] : (isset($auth_barangay) ? $auth_barangay : '');
$barangays = isset($barangays) && is_array($barangays) ? $barangays : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224"><title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css?v=20261007-responsive4">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/app.css?v=20261007-resident-mobile-plus2">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="portal-page resident-portal profile-page">
	<a class="skip-link" href="#main">Skip to content</a>
	<div class="gov-bar"><div class="gov-bar__inner"><span>Republic of the Philippines · Municipality of Dulag, Leyte</span><span>Municipal Disaster Risk Reduction and Management Office</span></div></div>
	<header class="topbar"><div class="topbar__inner"><a class="brand" href="<?php echo html_escape($portal_url); ?>"><span class="brand__mark"><img class="brand__logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40"></span><span class="brand__text"><span class="brand__name brand__name--desktop">DAGUITAN FLOOD MONITOR</span><span class="brand__name brand__name--mobile">Daguitan Monitor</span></span></a><div class="topbar__actions"><?php $this->load->view('partials/notifications_bell'); ?><span class="btn btn--ghost btn--compact portal-user"><?php echo html_escape($auth_name); ?></span><a class="btn btn--primary btn--compact" href="<?php echo html_escape($logout_url); ?>">Sign out</a><button class="resident-sidebar-toggle" type="button" id="residentSidebarToggle" aria-label="Open resident navigation" aria-controls="residentSidebar" aria-expanded="false"><span></span><span></span><span></span></button></div></div></header>
	<main id="main" class="resident-layout">
		<aside class="resident-sidebar" id="residentSidebar" aria-label="Resident portal navigation"><div class="resident-sidebar__profile"><img class="resident-sidebar__avatar resident-sidebar__avatar--logo" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt=""><div><strong><?php echo html_escape($auth_name); ?></strong><span>Resident account</span></div></div><nav class="resident-sidebar__nav" aria-label="Resident portal"><a href="<?php echo html_escape($portal_url); ?>">Dashboard</a><a href="<?php echo site_url('portal/go-bag'); ?>">Go Bag</a><a href="<?php echo site_url('portal/evacuation-centers'); ?>">Evacuation Centers</a><a href="<?php echo site_url('portal/announcements'); ?>">Announcements</a><a href="<?php echo site_url('portal/reports'); ?>">Flood / Hazard Reports</a><a class="is-active" href="<?php echo site_url('portal/profile'); ?>" aria-current="page">My Profile</a><a href="<?php echo site_url('portal/help'); ?>">Help / How to Use</a></nav><a class="resident-sidebar__signout" href="<?php echo html_escape($logout_url); ?>">Sign out</a><div class="resident-sidebar__government"><span>Republic of the Philippines</span><strong>Municipality of Dulag, Leyte</strong></div></aside>
		<div class="resident-layout__content">
			<section class="hero profile-page__hero">
				<div class="hero__copy">
					<p class="eyebrow">Resident account</p>
					<h1>My Profile</h1>
					<p class="lede">Keep your contact details current and protect access to your account.</p>
				</div>
			</section>
			<?php if ( ! empty($profile_notice)): ?>
				<div class="profile-message profile-message--success" role="status"><?php echo html_escape($profile_notice); ?></div>
			<?php endif; ?>
			<?php if ( ! empty($profile_error)): ?>
				<div class="profile-message profile-message--error" role="alert"><?php echo html_escape($profile_error); ?></div>
			<?php endif; ?>
			<section class="section section--tight">
				<div class="profile-grid">
					<article class="glass-card profile-card">
						<p class="card-kicker">Personal information</p>
						<h2>Contact details</h2>
						<form class="profile-form" method="post" action="<?php echo html_escape(site_url('portal/profile')); ?>">
							<input type="hidden" name="profile_form_token" value="<?php echo html_escape($profile_form_token); ?>">
							<input type="hidden" name="action" value="profile">
							<label class="profile-form__field" for="profileName">
								<span>Full name</span>
								<input id="profileName" name="name" type="text" value="<?php echo html_escape($profile_name_value); ?>" minlength="2" maxlength="120" autocomplete="name" required>
							</label>
							<label class="profile-form__field" for="profilePhone">
								<span>Mobile number <small>Used for important emergency contact.</small></span>
								<input id="profilePhone" name="phone" type="tel" value="<?php echo html_escape($profile_phone_value); ?>" inputmode="tel" autocomplete="tel" pattern="09[0-9]{9}" maxlength="11" placeholder="09XXXXXXXXX" aria-describedby="profilePhoneHint">
								<small id="profilePhoneHint">Use 09 followed by 9 digits. You may leave this blank.</small>
							</label>
							<label class="profile-form__field" for="profileBarangay">
								<span>Barangay</span>
								<select id="profileBarangay" name="barangay" autocomplete="address-level3" required>
									<option value="">Select your barangay</option>
									<?php foreach ($barangays as $value => $label): ?>
										<option value="<?php echo html_escape($value); ?>"<?php echo $profile_barangay_value === $value ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
									<?php endforeach; ?>
								</select>
								<small>Keep your barangay current for local emergency coordination.</small>
							</label>
							<div class="profile-form__actions">
								<button class="btn btn--primary" type="submit">Save contact details</button>
							</div>
						</form>
						<dl class="profile-details profile-details--compact">
							<div><dt>Username</dt><dd><?php echo html_escape($auth_user); ?><small>Username changes are not available.</small></dd></div>
							<div><dt>Account type</dt><dd>Resident</dd></div>
						</dl>
					</article>
					<article class="glass-card profile-card profile-card--security">
						<p class="card-kicker">Account security</p>
						<h2>Change password</h2>
						<p class="profile-card__intro">Confirm your current password before choosing a new one.</p>
						<form class="profile-form" method="post" action="<?php echo html_escape(site_url('portal/profile')); ?>">
							<input type="hidden" name="profile_form_token" value="<?php echo html_escape($profile_form_token); ?>">
							<input type="hidden" name="action" value="password">
							<label class="profile-form__field" for="currentPassword">
								<span>Current password</span>
								<input id="currentPassword" name="current_password" type="password" autocomplete="current-password" required>
							</label>
							<label class="profile-form__field" for="newPassword">
								<span>New password</span>
								<input id="newPassword" name="new_password" type="password" minlength="8" maxlength="4096" autocomplete="new-password" aria-describedby="passwordHint" required>
								<small id="passwordHint">Use at least 8 characters.</small>
							</label>
							<label class="profile-form__field" for="confirmPassword">
								<span>Confirm new password</span>
								<input id="confirmPassword" name="confirm_password" type="password" minlength="8" maxlength="4096" autocomplete="new-password" required>
							</label>
							<div class="profile-form__actions">
								<button class="btn btn--primary" type="submit">Update password</button>
							</div>
						</form>
					</article>
					<article class="glass-card profile-card profile-card--guidance">
						<p class="card-kicker">Resident tools</p>
						<h2>Stay prepared</h2>
						<p class="profile-card__intro">Keep your emergency plan ready and follow official MDRRMO updates.</p>
						<div class="profile-quick-links">
							<a class="profile-quick-link" href="<?php echo html_escape(site_url('portal/go-bag')); ?>"><span><strong>Go Bag checklist</strong><small>Review essential supplies and readiness.</small></span><span aria-hidden="true">→</span></a>
							<a class="profile-quick-link" href="<?php echo html_escape(site_url('portal/evacuation-centers')); ?>"><span><strong>Evacuation centers</strong><small>Find listed centers and directions.</small></span><span aria-hidden="true">→</span></a>
							<a class="profile-quick-link" href="<?php echo html_escape(site_url('portal/announcements')); ?>"><span><strong>Official announcements</strong><small>Read current advisories from MDRRMO.</small></span><span aria-hidden="true">→</span></a>
						</div>
					</article>
				</div>
			</section>
		</div>
	</main>
	<div class="resident-sidebar-backdrop" id="residentSidebarBackdrop" hidden></div>
	<footer class="footer portal-footer"><div class="portal-footer__inner"><p class="footer__note">In an emergency, follow MDRRMO and barangay instructions.</p></div></footer>
	<script>window.DAGUITAN_NOTIFY = <?php echo json_encode($notify_config, JSON_UNESCAPED_SLASHES); ?>;</script>
	<script src="<?php echo html_escape($asset_url); ?>js/notifications.js?v=20261007-resident-mobile-plus2"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-sidebar.js?v=20261007-mobile-plus"></script>
	<script src="<?php echo html_escape($asset_url); ?>js/resident-mobile.js?v=20261007-mobile-plus2"></script>
</body>
</html>

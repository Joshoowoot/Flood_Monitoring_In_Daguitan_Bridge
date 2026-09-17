<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="description" content="Create a resident account for the Daguitan Flood Monitor.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/auth.css">
</head>
<body class="auth-body auth-body--resident">
	<div class="auth-bg" aria-hidden="true"></div>
	<div class="auth-corner auth-corner--tl" aria-hidden="true"></div>
	<div class="auth-corner auth-corner--br" aria-hidden="true"></div>

	<header class="auth-top">
		<div class="auth-top__brand">
			<img class="auth-ph-seal" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="Official seal of Dulag, Leyte" width="58" height="58">
			<div class="auth-top__titles">
				<p>Republic of the Philippines</p>
				<strong>Municipality of Dulag, Leyte</strong>
			</div>
		</div>
		<p class="auth-top__office">Municipal Disaster Risk Reduction and<br>Management Office</p>
		<p class="auth-top__motto">
			<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 3l7 3v5c0 4.5-3 7.5-7 8.7C8 18.5 5 15.5 5 11V6l7-3z"/>
			</svg>
			Sa Ligtas na Pamayanan,<br>Handa ang Dulag.
		</p>
	</header>

	<main class="auth-panel" id="main">
		<section class="auth-intro">
			<div class="auth-intro__head">
				<span class="auth-mark" aria-hidden="true">
					<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="58" height="58">
				</span>
				<div>
					<h1>Daguitan Flood <span>Monitor</span></h1>
					<p class="auth-kicker">Official IoT Early-Warning Portal</p>
				</div>
			</div>
			<p class="auth-intro__copy">Create a resident account to save your session and receive flood status for Daguitan Bridge from MDRRMO Dulag.</p>
			<div class="auth-features">
				<article>
					<span class="auth-feature-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
						</svg>
					</span>
					<h2>For Residents</h2>
					<p>Sign up is for community members. Staff access is separate and is not listed on this page.</p>
				</article>
				<article>
					<span class="auth-feature-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 19a2 2 0 0 0 4 0"/>
						</svg>
					</span>
					<h2>Early Warning</h2>
					<p>Get timely alerts and advisories for safer decision-making.</p>
				</article>
				<article>
					<span class="auth-feature-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12 3l7 3v5c0 4.5-3 7.5-7 8.7C8 18.5 5 15.5 5 11V6l7-3z"/>
						</svg>
					</span>
					<h2>Safer Community</h2>
					<p>Your account is stored in the MDRRMO Dulag database.</p>
				</article>
			</div>
		</section>

		<section class="auth-card">
			<div class="auth-welcome">
				<span class="auth-welcome__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="8" r="3.2"/>
						<path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
					</svg>
				</span>
				<div>
					<h2>Create resident account</h2>
					<p class="auth-lead">Sign up to open the Daguitan Flood Monitor resident portal.</p>
				</div>
			</div>

			<?php if ($error): ?>
				<p class="auth-error" role="alert"><?php echo html_escape($error); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo site_url('signup'); ?>" class="auth-form" autocomplete="username">
				<label class="auth-field">
					<span>Full name</span>
					<span class="auth-field__control">
						<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="8" r="3.2"/>
							<path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
						</svg>
						<input type="text" name="name" required value="<?php echo html_escape($name); ?>" placeholder="Juan Dela Cruz" autocomplete="name">
					</span>
				</label>
				<label class="auth-field">
					<span>Username</span>
					<span class="auth-field__control">
						<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<path d="M4 20V10l8-5 8 5v10"/><path d="M9 20v-6h6v6"/>
						</svg>
						<input type="text" name="username" required value="<?php echo html_escape($username); ?>" placeholder="Choose a username" autocomplete="username">
					</span>
				</label>
				<label class="auth-field">
					<span>Password</span>
					<span class="auth-field__control">
						<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<rect x="4" y="11" width="16" height="10" rx="2"/>
							<path d="M8 11V8a4 4 0 0 1 8 0v3"/>
						</svg>
						<input id="password" type="password" name="password" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password">
					</span>
				</label>
				<label class="auth-field">
					<span>Confirm password</span>
					<span class="auth-field__control">
						<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<rect x="4" y="11" width="16" height="10" rx="2"/>
							<path d="M8 11V8a4 4 0 0 1 8 0v3"/>
						</svg>
						<input type="password" name="password_confirm" required minlength="8" placeholder="Re-enter your password" autocomplete="new-password">
					</span>
				</label>
				<button class="btn btn--primary btn--block auth-submit" type="submit">
					Sign up
				</button>
			</form>

			<p class="auth-switch">Already have an account? <a href="<?php echo site_url('login'); ?>">Sign in</a></p>
			<p class="auth-foot">
				<a href="<?php echo site_url('/'); ?>">
					<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M15 18l-6-6 6-6"/>
					</svg>
					Return to public monitor
				</a>
			</p>
		</section>
	</main>
</body>
</html>

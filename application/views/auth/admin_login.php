<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="robots" content="noindex,nofollow">
	<meta name="description" content="MDRRMO administrator sign in.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/auth.css?v=20260925e">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/typography.css?v=20261006-inter">
</head>
<body class="auth-body auth-body--admin">
	<div class="auth-bg" aria-hidden="true"></div>
	<div class="auth-corner auth-corner--tl" aria-hidden="true"></div>
	<div class="auth-corner auth-corner--br" aria-hidden="true"></div>

	<header class="auth-top">
		<a class="auth-top__brand" href="<?php echo site_url('/'); ?>">
			<img class="auth-ph-seal" src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="44" height="44">
			<div class="auth-top__titles">
				<p>Republic of the Philippines</p>
				<strong>Municipality of Dulag, Leyte</strong>
			</div>
		</a>
		<p class="auth-top__office">MDRRMO Dulag</p>
		<p class="auth-top__motto">Authorized Operations Access</p>
	</header>

	<main class="auth-shell" id="main">
		<section class="auth-panel" aria-label="Administrator sign in">
			<aside class="auth-intro">
				<div class="auth-intro__brand">
					<span class="auth-mark" aria-hidden="true">
						<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="64" height="64">
					</span>
					<p class="auth-kicker">Administrator Access</p>
					<h1>MDRRMO <span>Console</span></h1>
					<p class="auth-intro__copy">This page is strictly for authorized MDRRMO personnel only. It is not publicly available on the website and is intended solely for official administrative use. Unauthorized access is prohibited.</p>
				</div>
				<p class="auth-intro__back">
					<a href="<?php echo site_url('/'); ?>">
						<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
						Back to public monitor
					</a>
				</p>
			</aside>

			<section class="auth-card">
				<div class="auth-card__inner">
					<header class="auth-welcome">
						<p class="auth-admin-badge">Restricted Access</p>
						<h2>Administrator sign in</h2>
						<p class="auth-lead">Enter staff credentials to open the operations console.</p>
					</header>
					<div class="auth-secure-note auth-secure-note--<?php echo html_escape($station_health['status']); ?>" role="status">
						<span class="auth-secure-note__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><circle cx="12" cy="15" r="1" fill="currentColor" stroke="none"/></svg>
						</span>
						<span><strong>Secure operations channel</strong><small><?php echo html_escape($station_health['label']); ?> · <?php echo html_escape($station_health['detail']); ?></small></span>
					</div>

					<?php if ($error): ?>
						<p class="auth-error" role="alert"><?php echo html_escape($error); ?></p>
					<?php endif; ?>

					<form method="post" action="<?php echo site_url('admin'); ?>" class="auth-form" id="adminLoginForm" autocomplete="on">
						<label class="auth-field">
							<span class="auth-field__label">Username</span>
							<span class="auth-field__control">
								<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
									<circle cx="12" cy="8" r="3.2"/>
									<path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
								</svg>
								<input type="text" name="username" required value="<?php echo html_escape($username); ?>" placeholder="Staff username" autocomplete="username">
							</span>
						</label>
						<label class="auth-field">
							<span class="auth-field__label">Password</span>
							<span class="auth-field__control auth-field__control--eye">
								<input id="password" type="password" name="password" required placeholder="Staff password" autocomplete="current-password">
								<button class="auth-eye" type="button" id="togglePassword" aria-label="Show password" aria-pressed="false">
									<svg class="auth-eye__show" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden>
										<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
										<circle cx="12" cy="12" r="3"/>
									</svg>
									<svg class="auth-eye__hide" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/><path d="M9.9 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.2 3.8"/><path d="M6.1 6.1A18 18 0 0 0 2 12s3.5 7 10 7c1.3 0 2.5-.2 3.6-.6"/>
									</svg>
								</button>
							</span>
						</label>
						<button class="btn btn--primary btn--block auth-submit" type="submit" data-default-label="Sign in">Sign in</button>
					</form>
					<p class="auth-access-help">Need account assistance? <a href="<?php echo site_url('about'); ?>">Contact MDRRMO support</a></p>

				</div>
			</section>
		</section>
	</main>

	<script>
		(function () {
			var btn = document.getElementById('togglePassword');
			var input = document.getElementById('password');
			if (!btn || !input) return;
			btn.addEventListener('click', function () {
				var show = input.type === 'password';
				input.type = show ? 'text' : 'password';
				btn.setAttribute('aria-pressed', show ? 'true' : 'false');
				btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
				btn.classList.toggle('is-visible', show);
				btn.querySelector('.auth-eye__show').hidden = !show;
				btn.querySelector('.auth-eye__hide').hidden = show;
			});
		})();
		(function () {
			var form = document.getElementById('adminLoginForm');
			if (!form) return;
			form.addEventListener('submit', function () {
				var submit = form.querySelector('button[type="submit"]');
				if (!submit || submit.disabled) return;
				submit.disabled = true;
				submit.textContent = 'Signing in...';
			});
		})();
	</script>
</body>
</html>

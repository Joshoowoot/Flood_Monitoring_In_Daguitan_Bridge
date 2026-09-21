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
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/auth.css?v=20260320c">
</head>
<body class="auth-body auth-body--admin">
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
			Operations access only
		</p>
	</header>

	<main class="auth-panel" id="main">
		<section class="auth-intro">
			<div class="auth-intro__head">
				<span class="auth-mark" aria-hidden="true">
					<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="58" height="58">
				</span>
				<div>
					<h1>MDRRMO <span>Console</span></h1>
					<p class="auth-kicker">Administrator access</p>
				</div>
			</div>
			<p class="auth-intro__copy">This page is not linked from the public website. Only authorized MDRRMO staff may sign in here.</p>
		</section>

		<section class="auth-card">
			<div class="auth-welcome">
				<span class="auth-welcome__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<rect x="4" y="11" width="16" height="10" rx="2"/>
						<path d="M8 11V8a4 4 0 0 1 8 0v3"/>
					</svg>
				</span>
				<div>
					<h2>Administrator sign in</h2>
					<p class="auth-lead">Enter staff credentials to open the operations console.</p>
				</div>
			</div>

			<?php if ($error): ?>
				<p class="auth-error" role="alert"><?php echo html_escape($error); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo site_url('admin'); ?>" class="auth-form" autocomplete="username">
				<label class="auth-field">
					<span>Username</span>
					<span class="auth-field__control">
						<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="8" r="3.2"/>
							<path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
						</svg>
						<input type="text" name="username" required value="<?php echo html_escape($username); ?>" placeholder="Staff username" autocomplete="username">
					</span>
				</label>
				<label class="auth-field">
					<span>Password</span>
					<span class="auth-field__control">
						<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<rect x="4" y="11" width="16" height="10" rx="2"/>
							<path d="M8 11V8a4 4 0 0 1 8 0v3"/>
						</svg>
						<input id="password" type="password" name="password" required placeholder="Staff password" autocomplete="current-password">
						<button class="auth-eye" type="button" id="togglePassword" aria-label="Show password" aria-pressed="false">
							<svg class="auth-eye__show" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
								<circle cx="12" cy="12" r="3"/>
							</svg>
							<svg class="auth-eye__hide" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden>
								<path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/><path d="M9.9 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.2 3.8"/><path d="M6.1 6.1A18 18 0 0 0 2 12s3.5 7 10 7c1.3 0 2.5-.2 3.6-.6"/>
							</svg>
						</button>
					</span>
				</label>
				<button class="btn btn--primary btn--block auth-submit" type="submit">Sign in</button>
			</form>

			<p class="auth-foot">
				<a href="<?php echo site_url('/'); ?>">Return to public monitor</a>
			</p>
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
				btn.querySelector('.auth-eye__show').hidden = show;
				btn.querySelector('.auth-eye__hide').hidden = !show;
			});
		})();
	</script>
</body>
</html>

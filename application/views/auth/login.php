<?php defined('BASEPATH') OR exit('No direct script access allowed');
$username = isset($username) ? $username : '';
$error = isset($error) ? $error : '';
$asset_v = '20260320c';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="description" content="Sign in to the Daguitan Flood Monitor resident portal.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/auth.css?v=<?php echo $asset_v; ?>">
</head>
<body class="auth-body auth-body--resident auth-body--signin">
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
		<section class="auth-intro" aria-label="About the portal">
			<div class="auth-intro__head">
				<span class="auth-mark" aria-hidden="true">
					<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="58" height="58">
				</span>
				<div>
					<h1>Daguitan Flood <span>Monitor</span></h1>
					<p class="auth-kicker">Official IoT Early-Warning Portal</p>
				</div>
			</div>
			<p class="auth-intro__copy">Real-time flood monitoring and early warning system for the residents of Dulag, Leyte. This portal is operated by the MDRRMO to ensure a safer and more resilient community.</p>
			<div class="auth-features">
				<article>
					<span class="auth-feature-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<path d="M5 12.5a7 7 0 0 1 14 0"/><path d="M8.2 12.5a3.8 3.8 0 0 1 7.6 0"/><circle cx="12" cy="13" r="1.2" fill="currentColor" stroke="none"/>
						</svg>
					</span>
					<h2>Real-Time Monitoring</h2>
					<p>IoT sensors track water levels and provide up-to-date flood information.</p>
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
							<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
						</svg>
					</span>
					<h2>For a Safer Community</h2>
					<p>Together we can reduce risks and build a more resilient Dulag.</p>
				</article>
			</div>
			<div class="auth-intro__foot">
				<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<path d="M4 20V10l8-5 8 5v10"/><path d="M9 20v-6h6v6"/>
				</svg>
				<div>
					<strong>Municipality of Dulag, Leyte</strong>
					<span>MDRRMO · Disaster Risk Reduction and Management</span>
				</div>
			</div>
		</section>

		<section class="auth-card" aria-labelledby="signin-heading">
			<div class="auth-card__inner">
				<nav class="auth-tabs" aria-label="Account type">
					<a class="auth-tab is-active" href="<?php echo site_url('login'); ?>" aria-current="page">Sign in</a>
					<a class="auth-tab" href="<?php echo site_url('signup'); ?>">Sign up</a>
				</nav>

				<p class="auth-mobile-tagline">Access live Daguitan Bridge flood status and MDRRMO guidance for Dulag residents.</p>

				<div class="auth-welcome">
					<span class="auth-welcome__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="8" r="3.2"/>
							<path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
						</svg>
					</span>
					<div>
						<h2 id="signin-heading">Welcome back</h2>
						<p class="auth-lead">Sign in to open your resident portal and view the latest flood monitor data.</p>
					</div>
				</div>

				<?php if ($error): ?>
					<p class="auth-error" role="alert"><?php echo html_escape($error); ?></p>
				<?php endif; ?>

				<form method="post" action="<?php echo site_url('login'); ?>" class="auth-form">
					<label class="auth-field">
						<span class="auth-field__label">Username or mobile</span>
						<span class="auth-field__control">
							<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<circle cx="12" cy="8" r="3.2"/>
								<path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
							</svg>
							<input type="text" name="username" required value="<?php echo html_escape($username); ?>" placeholder="Username or 09171234567" autocomplete="username" autocapitalize="none">
						</span>
						<span class="auth-hint">Use the username you registered, or your 11-digit mobile number.</span>
					</label>

					<label class="auth-field">
						<span class="auth-field__label">Password</span>
						<span class="auth-field__control auth-field__control--eye">
							<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<rect x="4" y="11" width="16" height="10" rx="2"/>
								<path d="M8 11V8a4 4 0 0 1 8 0v3"/>
							</svg>
							<input id="login-password" type="password" name="password" required placeholder="Your password" autocomplete="current-password">
							<button class="auth-eye" type="button" data-auth-toggle="login-password" aria-label="Show password" aria-pressed="false">
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

					<button class="btn btn--primary btn--block auth-submit" type="submit">
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
							<path d="M10 17l5-5-5-5"/>
							<path d="M4 12h11"/>
						</svg>
						Sign in
					</button>
				</form>

				<div class="auth-card__actions">
					<p class="auth-switch">New resident? <a href="<?php echo site_url('signup'); ?>">Create a free account</a></p>
					<p class="auth-foot">
						<a href="<?php echo site_url('/'); ?>">
							<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="M15 18l-6-6 6-6"/>
							</svg>
							Return to public monitor
						</a>
					</p>
				</div>

				<p class="auth-trust">
					<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<rect x="4" y="11" width="16" height="10" rx="2"/>
						<path d="M8 11V8a4 4 0 0 1 8 0v3"/>
					</svg>
					<span>Sessions are encrypted. Sign-in activity may be recorded for security and audit purposes.</span>
				</p>
			</div>
		</section>
	</main>

	<footer class="auth-status">
		<p>
			<span class="auth-dot" aria-hidden="true"></span> System Online
		</p>
		<p>
			<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 3l7 3v5c0 4.5-3 7.5-7 8.7C8 18.5 5 15.5 5 11V6l7-3z"/>
			</svg>
			Secure Connection
		</p>
		<p>
			<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 3v4M8 7h8"/><path d="M6 20V10l6-4 6 4v10"/>
			</svg>
			Flood Monitoring Active
		</p>
		<p class="auth-status__end">Municipality of Dulag, Leyte <span>MDRRMO</span></p>
	</footer>

	<script src="<?php echo html_escape($asset_url); ?>js/auth.js?v=<?php echo $asset_v; ?>"></script>
</body>
</html>

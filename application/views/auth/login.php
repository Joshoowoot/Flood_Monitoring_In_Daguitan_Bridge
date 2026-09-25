<?php defined('BASEPATH') OR exit('No direct script access allowed');
$username = isset($username) ? $username : '';
$name = isset($name) ? $name : '';
$phone = isset($phone) ? $phone : '';
$error = isset($error) ? $error : '';
$auth_mode = (isset($auth_mode) && $auth_mode === 'signup') ? 'signup' : 'signin';
$is_signup = ($auth_mode === 'signup');
$asset_v = '20260924g';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#9b1224">
	<meta name="description" content="Sign in or create a resident account for the Daguitan Flood Monitor.">
	<title><?php echo html_escape($page_title); ?> · Daguitan Flood Monitor</title>
	<link rel="icon" type="image/png" href="<?php echo html_escape($asset_url); ?>img/dulag-logo.png">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/landing.css">
	<link rel="stylesheet" href="<?php echo html_escape($asset_url); ?>css/auth.css?v=<?php echo $asset_v; ?>">
</head>
<body class="auth-body auth-body--resident auth-body--<?php echo $is_signup ? 'signup' : 'signin'; ?>" data-auth-mode="<?php echo $auth_mode; ?>">
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
		<p class="auth-top__motto">Sa Ligtas na Pamayanan, Handa ang Dulag.</p>
	</header>

	<main class="auth-shell" id="main">
		<section class="auth-panel" aria-label="Resident account">
			<aside class="auth-intro">
				<div class="auth-intro__brand">
					<span class="auth-mark" aria-hidden="true">
						<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="64" height="64">
					</span>
					<p class="auth-kicker" data-auth-kicker-signin="Resident access" data-auth-kicker-signup="New resident">
						<?php echo $is_signup ? 'New resident' : 'Resident access'; ?>
					</p>
					<h1>Daguitan Flood <span>Monitor</span></h1>
					<p class="auth-intro__copy" data-auth-copy-signin="Sign in to follow live Daguitan Bridge levels and MDRRMO flood advisories." data-auth-copy-signup="Create a free account in about a minute. You can sign in later with your username or mobile number.">
						<?php echo $is_signup
							? 'Create a free account in about a minute. You can sign in later with your username or mobile number.'
							: 'Sign in to follow live Daguitan Bridge levels and MDRRMO flood advisories.'; ?>
					</p>
				</div>

				<ul class="auth-benefits">
					<li>
						<span class="auth-benefits__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3s6 7 6 11a6 6 0 1 1-12 0c0-4 6-11 6-11z"/></svg>
						</span>
						<div>
							<strong>Live water levels</strong>
							<span>Track Daguitan Bridge in real time.</span>
						</div>
					</li>
					<li>
						<span class="auth-benefits__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>
						</span>
						<div>
							<strong>Flood advisories</strong>
							<span>Stay ready when warnings change.</span>
						</div>
					</li>
					<li>
						<span class="auth-benefits__icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 8.7C8 18.5 5 15.5 5 11V6l7-3z"/></svg>
						</span>
						<div>
							<strong>Trusted by MDRRMO</strong>
							<span>Official Dulag early-warning portal.</span>
						</div>
					</li>
				</ul>
			</aside>

			<section class="auth-card">
				<div class="auth-card__inner">
					<div class="auth-card__brand">
						<img src="<?php echo html_escape($asset_url); ?>img/dulag-logo.png" alt="" width="40" height="40">
						<div>
							<p class="auth-kicker">MDRRMO Dulag</p>
							<strong>Daguitan Flood Monitor</strong>
						</div>
					</div>

					<nav class="auth-tabs" role="tablist" aria-label="Sign in or sign up">
						<button class="auth-tab<?php echo $is_signup ? '' : ' is-active'; ?>" type="button" role="tab" data-auth-mode="signin" aria-controls="panel-signin" id="tab-signin"<?php echo $is_signup ? ' aria-selected="false"' : ' aria-selected="true" aria-current="page"'; ?>>Sign in</button>
						<button class="auth-tab<?php echo $is_signup ? ' is-active' : ''; ?>" type="button" role="tab" data-auth-mode="signup" aria-controls="panel-signup" id="tab-signup"<?php echo $is_signup ? ' aria-selected="true" aria-current="page"' : ' aria-selected="false"'; ?>>Sign up</button>
					</nav>

					<?php if ($error): ?>
						<p class="auth-error" role="alert"><?php echo html_escape($error); ?></p>
					<?php endif; ?>

					<div class="auth-modes" id="authModes">
						<div class="auth-mode auth-mode--signin<?php echo $is_signup ? '' : ' is-active'; ?>" data-panel="signin" role="tabpanel" id="panel-signin" aria-labelledby="tab-signin" <?php echo $is_signup ? 'hidden' : ''; ?>>
							<header class="auth-welcome">
								<h2>Welcome back</h2>
								<p class="auth-lead">Use your username or 11-digit mobile number.</p>
							</header>
							<form method="post" action="<?php echo site_url('login'); ?>" class="auth-form" id="signinForm">
								<label class="auth-field">
									<span class="auth-field__label">Username or mobile</span>
									<span class="auth-field__control">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
										</svg>
										<input type="text" name="username" required value="<?php echo $is_signup ? '' : html_escape($username); ?>" placeholder="juan or 09171234567" autocomplete="username" autocapitalize="none" spellcheck="false">
									</span>
								</label>
								<label class="auth-field">
									<span class="auth-field__label">Password</span>
									<span class="auth-field__control auth-field__control--eye">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
										</svg>
										<input id="login-password" type="password" name="password" required placeholder="Your password" autocomplete="current-password">
										<button class="auth-eye" type="button" data-auth-toggle="login-password" aria-label="Show password" aria-pressed="false">
											<svg class="auth-eye__show" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
											<svg class="auth-eye__hide" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden><path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/><path d="M9.9 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.2 3.8"/><path d="M6.1 6.1A18 18 0 0 0 2 12s3.5 7 10 7c1.3 0 2.5-.2 3.6-.6"/></svg>
										</button>
									</span>
								</label>
								<button class="btn btn--primary btn--block auth-submit" type="submit">Sign in</button>
							</form>
							<p class="auth-switch">New here? <button type="button" class="auth-switch__btn" data-auth-mode="signup">Create an account</button></p>
						</div>

						<div class="auth-mode auth-mode--signup<?php echo $is_signup ? ' is-active' : ''; ?>" data-panel="signup" role="tabpanel" id="panel-signup" aria-labelledby="tab-signup" <?php echo $is_signup ? '' : 'hidden'; ?>>
							<header class="auth-welcome">
								<h2>Create your account</h2>
								<p class="auth-lead">Free resident registration. You can sign in with mobile later.</p>
							</header>
							<form method="post" action="<?php echo site_url('signup'); ?>" class="auth-form" id="signupForm" novalidate>
								<label class="auth-field">
									<span class="auth-field__label">Full name</span>
									<span class="auth-field__control">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<circle cx="12" cy="8" r="3.2"/><path d="M5 19c1.4-3.2 3.8-4.8 7-4.8S17.6 15.8 19 19"/>
										</svg>
										<input type="text" name="name" required value="<?php echo html_escape($name); ?>" placeholder="Juan Dela Cruz" autocomplete="name">
									</span>
								</label>
								<label class="auth-field">
									<span class="auth-field__label">Mobile number</span>
									<span class="auth-field__control">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>
										</svg>
										<input id="signup-phone" type="tel" name="phone" required value="<?php echo html_escape($phone); ?>" placeholder="09171234567" autocomplete="tel" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09">
									</span>
									<span class="auth-hint">11 digits starting with 09</span>
								</label>
								<label class="auth-field">
									<span class="auth-field__label">Username</span>
									<span class="auth-field__control">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<path d="M4 20V10l8-5 8 5v10"/><path d="M9 20v-6h6v6"/>
										</svg>
										<input type="text" name="username" required value="<?php echo html_escape($username); ?>" placeholder="juan_delacruz" autocomplete="username" autocapitalize="none" spellcheck="false" pattern="[a-z0-9_]{3,32}" title="3–32 characters: letters, numbers, underscore">
									</span>
								</label>
								<label class="auth-field">
									<span class="auth-field__label">Password</span>
									<span class="auth-field__control auth-field__control--eye">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
										</svg>
										<input id="signup-password" type="password" name="password" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password">
										<button class="auth-eye" type="button" data-auth-toggle="signup-password" aria-label="Show password" aria-pressed="false">
											<svg class="auth-eye__show" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
											<svg class="auth-eye__hide" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden><path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/><path d="M9.9 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.2 3.8"/><path d="M6.1 6.1A18 18 0 0 0 2 12s3.5 7 10 7c1.3 0 2.5-.2 3.6-.6"/></svg>
										</button>
									</span>
								</label>
								<label class="auth-field">
									<span class="auth-field__label">Confirm password</span>
									<span class="auth-field__control auth-field__control--eye">
										<svg class="auth-icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>
										</svg>
										<input id="signup-password-confirm" type="password" name="password_confirm" required minlength="8" placeholder="Repeat password" autocomplete="new-password">
										<button class="auth-eye" type="button" data-auth-toggle="signup-password-confirm" aria-label="Show password" aria-pressed="false">
											<svg class="auth-eye__show" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
											<svg class="auth-eye__hide" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" hidden><path d="M3 3l18 18"/><path d="M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.4-4.4"/><path d="M9.9 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3.2 3.8"/><path d="M6.1 6.1A18 18 0 0 0 2 12s3.5 7 10 7c1.3 0 2.5-.2 3.6-.6"/></svg>
										</button>
									</span>
								</label>
								<p class="auth-hint auth-hint--error" id="signupMatchHint" hidden>Passwords must match.</p>
								<button class="btn btn--primary btn--block auth-submit" type="submit">Create account</button>
							</form>
							<p class="auth-switch">Already registered? <button type="button" class="auth-switch__btn" data-auth-mode="signin">Sign in instead</button></p>
						</div>
					</div>

					<footer class="auth-card__actions">
						<p class="auth-foot">
							<a href="<?php echo site_url('/'); ?>">
								<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
								Back to public monitor
							</a>
						</p>
						<p class="auth-trust">
							<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 8.7C8 18.5 5 15.5 5 11V6l7-3z"/></svg>
							<span>Accounts are stored by MDRRMO Dulag for flood advisories and recovery.</span>
						</p>
					</footer>
				</div>
			</section>
		</section>
	</main>

	<footer class="auth-status">
		<p><span class="auth-dot" aria-hidden="true"></span> System online</p>
		<p>Secure connection</p>
		<p class="auth-status__end">Municipality of Dulag, Leyte · MDRRMO</p>
	</footer>

	<script>
		window.DAGUITAN_AUTH = {
			loginUrl: <?php echo json_encode(site_url('login')); ?>,
			signupUrl: <?php echo json_encode(site_url('signup')); ?>
		};
	</script>
	<script src="<?php echo html_escape($asset_url); ?>js/auth.js?v=<?php echo $asset_v; ?>"></script>
</body>
</html>

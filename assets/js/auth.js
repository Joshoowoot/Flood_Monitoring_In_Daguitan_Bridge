(function () {
	function syncEyeIcons(btn, showPlain) {
		var showIcon = btn.querySelector('.auth-eye__show');
		var hideIcon = btn.querySelector('.auth-eye__hide');
		if (showIcon && hideIcon) {
			showIcon.hidden = !showPlain;
			hideIcon.hidden = showPlain;
			return;
		}
		btn.textContent = showPlain ? 'Show' : 'Hide';
	}

	function syncFieldState(input) {
		if (!input) return;
		var control = input.closest('.auth-field__control');
		if (!control) return;
		control.classList.toggle('has-value', String(input.value || '').trim().length > 0);
	}

	function bindToggle(btn) {
		var targetId = btn.getAttribute('data-auth-toggle');
		if (!targetId) return;
		var input = document.getElementById(targetId);
		if (!input) return;

		btn.addEventListener('click', function () {
			var revealing = input.type === 'password';
			input.type = revealing ? 'text' : 'password';
			btn.setAttribute('aria-pressed', revealing ? 'true' : 'false');
			btn.setAttribute('aria-label', revealing ? 'Hide password' : 'Show password');
			syncEyeIcons(btn, !revealing);
		});
	}

	document.querySelectorAll('[data-auth-toggle]').forEach(bindToggle);
	document.querySelectorAll('.auth-form input').forEach(function (input) {
		syncFieldState(input);
		input.addEventListener('input', function () {
			syncFieldState(input);
		});
		input.addEventListener('blur', function () {
			syncFieldState(input);
		});
	});

	var phone = document.getElementById('signup-phone');
	if (phone) {
		phone.addEventListener('input', function () {
			var digits = phone.value.replace(/\D+/g, '');
			if (digits.length > 11) {
				digits = digits.slice(0, 11);
			}
			if (phone.value !== digits) {
				phone.value = digits;
			}
		});
	}

	var body = document.body;
	var urls = window.DAGUITAN_AUTH || {};
	var signinForm = document.getElementById('signinForm');
	var signupForm = document.getElementById('signupForm');
	var pass = document.getElementById('signup-password');
	var confirm = document.getElementById('signup-password-confirm');
	var matchHint = document.getElementById('signupMatchHint');

	function setDisabled(form, disabled) {
		if (!form) return;
		form.querySelectorAll('input, button[type="submit"]').forEach(function (el) {
			if (el.classList.contains('auth-eye')) return;
			el.disabled = disabled;
		});
		form.querySelectorAll('.auth-eye').forEach(function (el) {
			el.disabled = disabled;
		});
	}

	function syncIntro(signup) {
		var kicker = document.querySelector('.auth-intro [data-auth-kicker-signin]');
		var copy = document.querySelector('.auth-intro [data-auth-copy-signin]');
		if (kicker) {
			kicker.textContent = signup
				? (kicker.getAttribute('data-auth-kicker-signup') || kicker.textContent)
				: (kicker.getAttribute('data-auth-kicker-signin') || kicker.textContent);
		}
		if (copy) {
			copy.textContent = signup
				? (copy.getAttribute('data-auth-copy-signup') || copy.textContent)
				: (copy.getAttribute('data-auth-copy-signin') || copy.textContent);
		}
	}

	function setMode(mode, push) {
		var signup = mode === 'signup';
		body.classList.toggle('auth-body--signup', signup);
		body.classList.toggle('auth-body--signin', !signup);
		body.dataset.authMode = signup ? 'signup' : 'signin';

		document.querySelectorAll('.auth-tab[data-auth-mode]').forEach(function (tab) {
			var on = tab.getAttribute('data-auth-mode') === mode;
			tab.classList.toggle('is-active', on);
			tab.setAttribute('aria-selected', on ? 'true' : 'false');
			if (on) tab.setAttribute('aria-current', 'page');
			else tab.removeAttribute('aria-current');
		});

		document.querySelectorAll('.auth-mode').forEach(function (panel) {
			var on = panel.getAttribute('data-panel') === mode;
			panel.classList.toggle('is-active', on);
			panel.hidden = !on;
		});

		setDisabled(signinForm, signup);
		setDisabled(signupForm, !signup);
		syncIntro(signup);

		var nextUrl = signup ? urls.signupUrl : urls.loginUrl;
		if (push && nextUrl && window.history && history.pushState) {
			history.pushState({ authMode: mode }, '', nextUrl);
			document.title = (signup ? 'Sign up' : 'Sign in') + ' · Daguitan Flood Monitor';
		}

		var focusForm = signup ? signupForm : signinForm;
		var first = focusForm && focusForm.querySelector('input:not([disabled])');
		if (first && push) first.focus();
	}

	document.querySelectorAll('button[data-auth-mode]').forEach(function (el) {
		el.addEventListener('click', function () {
			setMode(el.getAttribute('data-auth-mode'), true);
		});
	});

	window.addEventListener('popstate', function () {
		var path = (window.location.pathname || '').toLowerCase();
		setMode(path.indexOf('signup') !== -1 || path.indexOf('register') !== -1 ? 'signup' : 'signin', false);
	});

	function checkMatch() {
		if (!pass || !confirm || !matchHint) return;
		var mismatch = confirm.value.length > 0 && pass.value !== confirm.value;
		matchHint.hidden = !mismatch;
		confirm.setAttribute('aria-invalid', mismatch ? 'true' : 'false');
	}

	if (pass) pass.addEventListener('input', checkMatch);
	if (confirm) confirm.addEventListener('input', checkMatch);

	if (signupForm) {
		signupForm.addEventListener('submit', function (event) {
			if (pass && confirm && pass.value !== confirm.value) {
				event.preventDefault();
				checkMatch();
				confirm.focus();
			}
		});
	}

	setMode(body.dataset.authMode === 'signup' ? 'signup' : 'signin', false);
})();

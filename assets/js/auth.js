(function () {
	function bindToggle(btn) {
		var targetId = btn.getAttribute('data-auth-toggle');
		if (!targetId) return;
		var input = document.getElementById(targetId);
		if (!input) return;

		btn.addEventListener('click', function () {
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			btn.setAttribute('aria-pressed', show ? 'true' : 'false');
			btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
			var showIcon = btn.querySelector('.auth-eye__show');
			var hideIcon = btn.querySelector('.auth-eye__hide');
			if (showIcon) showIcon.hidden = show;
			if (hideIcon) hideIcon.hidden = !show;
		});
	}

	document.querySelectorAll('[data-auth-toggle]').forEach(bindToggle);

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
})();

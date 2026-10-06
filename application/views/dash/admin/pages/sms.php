<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="admin-sms-page">
<section class="glass-card admin-sms-card">
	<div class="admin-sms-card__head">
		<div>
			<p class="admin-kicker">Emergency communication</p>
			<h2>Send SMS to residents</h2>
			<p>Broadcast a short warning to every resident with a registered mobile number.</p>
		</div>
		<strong class="admin-sms-card__count"><?php echo count($recipients); ?><span> recipients</span></strong>
	</div>
	<?php if (! empty($sms_error)): ?><p class="admin-notice admin-notice--error" role="alert"><?php echo html_escape($sms_error); ?></p><?php endif; ?>
	<?php if (! empty($sms_success)): ?><p class="admin-notice" role="status"><?php echo html_escape($sms_success); ?></p><?php endif; ?>
	<form method="post" action="<?php echo site_url('admin/sms'); ?>" class="admin-sms-form" data-recipient-count="<?php echo (int) count($recipients); ?>">
		<label for="smsMessage">Message</label>
		<div class="admin-sms-template-actions">
			<button type="button" class="btn btn--ghost btn--compact sms-template" data-template="🚨 MDRRMO DULAG ALERT
Bridge: Daguitan Bridge
Status: YELLOW
Action: Avoid the riverbank and follow MDRRMO instructions.
Time: [TIME]
Info: Stay informed and monitor official updates.">Use emergency template</button>
			<button type="button" class="btn btn--ghost btn--compact sms-template" data-template="⚠️ FLOOD WATCH
Bridge: Daguitan Bridge
Status: MONITOR
Action: Be alert and avoid unnecessary travel near the river.
Time: [TIME]
Source: MDRRMO Dulag">Use watch template</button>
		</div>
		<textarea id="smsMessage" name="message" rows="6" maxlength="320" required placeholder="Example: 🚨 MDRRMO DULAG ALERT
Bridge: Daguitan Bridge
Status: YELLOW
Action: Avoid the riverbank and follow MDRRMO instructions.
Time: 2:11 PM"></textarea>
		<div class="admin-sms-form__metrics" role="status" aria-live="polite">
			<span id="smsCharacterCount">0 / 320 characters</span>
			<span id="smsSegmentEstimate">Estimated: 0 SMS segments</span>
		</div>
		<div class="admin-sms-form__foot">
			<span>Maximum 320 characters. Review carefully before sending.</span>
			<button class="btn btn--primary" type="submit"<?php echo empty($recipients) ? ' disabled' : ''; ?>>Send to <?php echo count($recipients); ?> residents</button>
		</div>
	</form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.admin-sms-form');
    if (!form) return;
    const textarea = form.querySelector('#smsMessage');
    if (!textarea) return;
	const characterCount = form.querySelector('#smsCharacterCount');
	const segmentEstimate = form.querySelector('#smsSegmentEstimate');
	const gsmBasic = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\u001bÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
	const gsmExtended = '^{}\\[~]|€\f';

	function updateMessageMetrics() {
		const message = textarea.value;
		if (characterCount) characterCount.textContent = Array.from(message).length + ' / ' + textarea.maxLength + ' characters';
		if (!segmentEstimate) return;

		let isGsm = true;
		let septets = 0;
		Array.from(message).forEach(function (character) {
			if (gsmBasic.indexOf(character) !== -1) septets += 1;
			else if (gsmExtended.indexOf(character) !== -1) septets += 2;
			else isGsm = false;
		});

		const units = isGsm ? septets : message.length;
		const singleLimit = isGsm ? 160 : 70;
		const multipartLimit = isGsm ? 153 : 67;
		const segments = units === 0 ? 0 : (units <= singleLimit ? 1 : Math.ceil(units / multipartLimit));
		segmentEstimate.textContent = 'Estimated: ' + segments + (segments === 1 ? ' SMS segment' : ' SMS segments') + (isGsm ? ' · GSM-7' : ' · Unicode');
	}

    form.querySelectorAll('.sms-template').forEach(function (button) {
        button.addEventListener('click', function () {
            const template = button.dataset.template || '';
            textarea.value = template.replace(/\[TIME\]/g, new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }));
			updateMessageMetrics();
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        });
    });
	textarea.addEventListener('input', updateMessageMetrics);
	form.addEventListener('submit', function (event) {
		const count = Number(form.dataset.recipientCount) || 0;
		if (!window.confirm('Send this SMS to ' + count + ' residents? Review the message before confirming.')) event.preventDefault();
	});
	updateMessageMetrics();
});
</script>

<section class="glass-card admin-sms-recipients">
	<h2>Recipients with mobile numbers</h2>
	<?php if (empty($recipients)): ?>
		<p>No residents have a mobile number on file.</p>
	<?php else: ?>
		<div class="admin-sms-table-scroll">
		<table class="dash-table">
			<thead><tr><th>Name</th><th>Mobile</th><th>Status</th></tr></thead>
			<tbody>
			<?php foreach ($recipients as $recipient): ?>
				<tr><td><?php echo html_escape($recipient['name']); ?></td><td><?php echo html_escape($recipient['phone']); ?></td><td><span class="sync-pill sync-pill--synced">Ready</span></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	<?php endif; ?>
</section>
</div>
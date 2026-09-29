<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
	<form method="post" action="<?php echo site_url('admin/sms'); ?>" class="admin-sms-form">
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
    form.querySelectorAll('.sms-template').forEach(function (button) {
        button.addEventListener('click', function () {
            const template = button.dataset.template || '';
            textarea.value = template.replace(/\[TIME\]/g, new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }));
            textarea.focus();
            textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        });
    });
});
</script>

<section class="glass-card admin-sms-recipients">
	<h2>Recipients with mobile numbers</h2>
	<?php if (empty($recipients)): ?>
		<p>No residents have a mobile number on file.</p>
	<?php else: ?>
		<table class="dash-table">
			<thead><tr><th>Name</th><th>Mobile</th><th>Status</th></tr></thead>
			<tbody>
			<?php foreach ($recipients as $recipient): ?>
				<tr><td><?php echo html_escape($recipient['name']); ?></td><td><?php echo html_escape($recipient['phone']); ?></td><td><span class="sync-pill sync-pill--synced">Ready</span></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</section>
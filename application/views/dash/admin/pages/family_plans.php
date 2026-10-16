<?php defined('BASEPATH') OR exit('No direct script access allowed');
$family_plans = isset($family_plans) && is_array($family_plans) ? $family_plans : array();
?>
<div class="admin-family-plans-page">
	<section class="glass-card admin-family-plans__notice">
		<strong>Voluntarily shared plans only</strong>
		<p>Residents' plans remain private unless they actively opt in. A resident can revoke sharing from their Family evacuation plan page; revoked plans no longer appear here. Use this information for coordination alongside official barangay assignments, not as a substitute for them.</p>
	</section>
	<?php if ( ! empty($family_plans_error)): ?>
		<p class="admin-family-plans__error" role="alert"><?php echo html_escape($family_plans_error); ?></p>
	<?php endif; ?>
	<section class="glass-card admin-family-plans__directory" aria-labelledby="familyPlansHeading">
		<header class="admin-family-plans__header">
			<div>
				<p class="admin-kicker">Resident coordination</p>
				<h2 id="familyPlansHeading">Shared family plans</h2>
				<p><?php echo number_format(count($family_plans)); ?> shared <?php echo count($family_plans) === 1 ? 'plan' : 'plans'; ?><?php echo ! empty($family_plans_search) ? ' matching your search' : ''; ?>.</p>
			</div>
			<form method="get" action="<?php echo site_url('admin/family-plans'); ?>" class="admin-family-plans__search">
				<label for="familyPlansSearch">Search names, barangays, or destinations</label>
				<div>
					<input id="familyPlansSearch" type="search" name="q" value="<?php echo html_escape($family_plans_search); ?>" maxlength="120" placeholder="Name, barangay, destination">
					<button class="btn btn--primary btn--compact" type="submit">Search</button>
					<?php if ( ! empty($family_plans_search)): ?><a class="btn btn--ghost btn--compact" href="<?php echo site_url('admin/family-plans'); ?>">Clear</a><?php endif; ?>
				</div>
			</form>
		</header>
		<?php if ($family_plans): ?>
			<div class="admin-family-plans__list">
				<?php foreach ($family_plans as $plan): ?>
					<article class="admin-family-plan-card">
						<header>
							<div>
								<p class="admin-kicker"><?php echo html_escape($plan['barangay'] !== '' ? $plan['barangay'] : 'Barangay not specified'); ?></p>
								<h3><?php echo html_escape($plan['name']); ?></h3>
							</div>
							<span class="admin-family-plan-card__shared">Shared <?php echo html_escape(date('M j, Y g:i A', strtotime($plan['shared_at']))); ?></span>
						</header>
						<dl>
							<div><dt>Evacuation destination</dt><dd><?php echo html_escape($plan['evacuation_place']); ?></dd></div>
							<div><dt>Family meeting point</dt><dd><?php echo $plan['meeting_point'] !== '' ? html_escape($plan['meeting_point']) : 'Not provided'; ?></dd></div>
							<div><dt>Out-of-area contact</dt><dd><?php echo $plan['contact_name'] !== '' ? html_escape($plan['contact_name']) : 'Not provided'; ?><?php if ($plan['contact_phone'] !== ''): ?> · <?php echo html_escape($plan['contact_phone']); ?><?php endif; ?></dd></div>
							<div><dt>Last reviewed</dt><dd><?php echo ! empty($plan['last_reviewed_at']) ? html_escape(date('M j, Y', strtotime($plan['last_reviewed_at']))) : 'Not recorded'; ?></dd></div>
						</dl>
						<div class="admin-family-plan-card__support">
							<strong>Support needs</strong>
							<p><?php echo $plan['support_needs'] ? html_escape(implode(', ', $plan['support_needs'])) : 'None selected'; ?></p>
						</div>
						<p class="admin-family-plan-card__consent">Resident consent recorded <?php echo html_escape(date('M j, Y g:i A', strtotime($plan['consented_at']))); ?>.</p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php elseif (empty($family_plans_error)): ?>
			<p class="admin-family-plans__empty"><?php echo ! empty($family_plans_search) ? 'No shared plans match your search.' : 'No residents have shared a family evacuation plan yet.'; ?></p>
		<?php endif; ?>
	</section>
</div>

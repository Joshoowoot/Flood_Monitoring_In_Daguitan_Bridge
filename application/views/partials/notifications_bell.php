<?php defined('BASEPATH') OR exit('No direct script access allowed');
$notify_audience = isset($notify_audience) ? $notify_audience : 'user';
$notify_unread = isset($notify_unread) ? (int) $notify_unread : 0;
?>
<div class="notify-wrap" id="notifyWrap" data-audience="<?php echo html_escape($notify_audience); ?>">
	<button class="icon-btn notify-bell" type="button" id="notifyBellBtn" aria-expanded="false" aria-controls="notifyPanel" aria-haspopup="dialog" aria-label="<?php echo $notify_unread > 0 ? $notify_unread . ' unread notifications' : 'Notifications, none unread'; ?>">
		<svg class="notify-bell__icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8">
			<path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/>
			<path d="M10 19a2 2 0 0 0 4 0"/>
		</svg>
		<span class="notify-bell__badge" id="notifyBadge"<?php echo $notify_unread < 1 ? ' hidden' : ''; ?>><?php echo $notify_unread > 99 ? '99+' : $notify_unread; ?></span>
	</button>

	<div class="notify-backdrop" id="notifyBackdrop" hidden aria-hidden="true"></div>

	<div class="notify-panel" id="notifyPanel" hidden role="dialog" aria-modal="true" aria-labelledby="notifyPanelTitle">
		<div class="notify-panel__head">
			<div class="notify-panel__title-wrap">
				<span class="notify-panel__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>
				</span>
				<div>
					<h2 class="notify-panel__title" id="notifyPanelTitle">Notifications</h2>
					<p class="notify-panel__subtitle" id="notifySubtitle"><?php echo $notify_unread > 0 ? $notify_unread . ' unread' : 'You\'re all caught up'; ?></p>
				</div>
			</div>
			<button class="notify-panel__close" type="button" id="notifyCloseBtn" aria-label="Close notifications">
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>

		<div class="notify-panel__toolbar">
			<div class="notify-tabs" role="tablist" aria-label="Filter notifications">
				<button class="notify-tabs__btn is-active" type="button" role="tab" aria-selected="true" data-filter="all" id="notifyTabAll">All</button>
				<button class="notify-tabs__btn" type="button" role="tab" aria-selected="false" data-filter="unread" id="notifyTabUnread">Unread</button>
			</div>
			<button class="notify-panel__mark" type="button" id="notifyMarkAll">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12l4 4L19 6"/></svg>
				Mark all read
			</button>
		</div>

		<div class="notify-panel__body">
			<ul class="notify-list" id="notifyList" aria-live="polite">
				<li class="notify-skeleton" aria-hidden="true"><span></span><span></span><span></span></li>
				<li class="notify-skeleton" aria-hidden="true"><span></span><span></span><span></span></li>
				<li class="notify-skeleton" aria-hidden="true"><span></span><span></span><span></span></li>
			</ul>
		</div>

		<div class="notify-panel__foot">
			<span>MDRRMO Dulag · Daguitan Bridge</span>
		</div>
	</div>
</div>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<dialog class="warning-details" id="warningDetails" aria-labelledby="warningDetailsTitle">
	<div class="warning-details__head">
		<div>
			<p class="card-kicker" id="warningDetailsLevel">Flood warning level</p>
			<h3 id="warningDetailsTitle">Water-level details</h3>
		</div>
		<button class="warning-details__close" type="button" aria-label="Close water-level details" autofocus>&times;</button>
	</div>
	<p class="warning-details__range" id="warningDetailsRange"></p>
	<p class="warning-details__reading">Current water level <strong id="warningDetailsCurrent">—</strong></p>
	<div class="warning-details__meter" id="warningDetailsMeter" role="meter" aria-label="Current water level" aria-valuemin="0" aria-valuenow="0" aria-valuemax="3">
		<div class="warning-details__track">
			<span class="warning-details__zones" aria-hidden="true"></span>
			<span class="warning-details__marker" id="warningDetailsMarker" aria-hidden="true"></span>
		</div>
		<div class="warning-details__labels" aria-hidden="true">
			<span>0 m</span>
			<span>Advisory <strong id="warningDetailsYellow">—</strong></span>
			<span>Critical <strong id="warningDetailsRed">—</strong></span>
		</div>
	</div>
	<section class="warning-details__community" aria-labelledby="warningDetailsCommunityTitle">
		<h4 id="warningDetailsCommunityTitle">What this may mean for your community</h4>
		<p id="warningDetailsCommunityIntro"></p>
		<div class="warning-details__guidance">
			<div class="warning-details__guidance-card">
				<h5>Possible effects</h5>
				<ul id="warningDetailsEffects"></ul>
			</div>
			<div class="warning-details__guidance-card warning-details__guidance-card--action">
				<h5>What you can do</h5>
				<ul id="warningDetailsActions"></ul>
			</div>
		</div>
	</section>
	<p class="warning-details__note">These are general possibilities based on the river-level threshold. Local conditions can vary; follow official MDRRMO and barangay advisories.</p>
</dialog>

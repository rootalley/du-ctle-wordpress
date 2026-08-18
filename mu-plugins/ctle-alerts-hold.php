<?php
/**
 * Plugin Name: CTLE Admin Alerts — recipient hold
 * Description: Temporarily narrows administrator alert recipients to the infrastructure owner.
 * Version:     1.0.0
 * Author:      Steven Endres (sendres@dom.edu)
 * License:     GPL-2.0-or-later
 *
 * WHY THIS EXISTS
 *
 * ctle-admin-alerts.php emails every Administrator login and every role change to the
 * CTLE admin list, which includes the CTLE Director. While single sign-on is being
 * tested and accounts are being pre-created, those alerts would arrive before the
 * conversation that is meant to introduce this work — and an unexplained security
 * alert is a poor way to open it. One such alert has already gone out unexplained.
 *
 * The alerts themselves are still wanted. Only the audience is temporarily wrong, so
 * this narrows the recipient list rather than unhooking anything: a genuine security
 * event during the hold is still delivered, just to one person.
 *
 * DELETE THIS FILE AT HANDOVER.
 *
 * Left in place, it silently removes the Director from a security control she is
 * supposed to hold, and nothing about the running site would reveal that — the alerts
 * keep arriving for whoever remains on the list, so they look healthy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Priority 100, so this wins over anything else filtering the same list.
 */
add_filter(
	'ctle_alert_recipients',
	function () {
		return array( 'sendres@dom.edu' );
	},
	100
);

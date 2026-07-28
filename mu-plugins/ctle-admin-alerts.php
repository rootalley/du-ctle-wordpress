<?php
/**
 * Plugin Name: CTLE Admin Alerts
 * Description: Emails the designated CTLE admin(s) on (a) any login by a user holding
 *              the Administrator role and (b) any user role change. Provides these
 *              alerts using free WordPress core hooks, in place of WP Activity Log
 *              Premium's paid notifications.
 * Author:      Steven Endres (sendres@dom.edu)
 * Version:     1.0.0
 *
 * DEPLOY TO:   wp-content/mu-plugins/ctle-admin-alerts.php
 *              A must-use plugin: always active, no activation step, and it cannot
 *              be deactivated from the WP admin UI.
 *
 * DEPENDENCY:  Email delivery depends on WP Mail SMTP.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Who receives the alerts.
 *
 * Set the $recipients array below with the CTLE admin email addresses. If the list is
 * left empty, it falls back to the site Administration Email (Settings → General).
 *
 * @return string[]
 */
function ctle_alert_recipients() {
	$recipients = array(
		'sendres@dom.edu',
	);

	$recipients = array_values( array_filter( array_map( 'trim', $recipients ) ) );

	if ( empty( $recipients ) ) {
		$recipients = array( get_option( 'admin_email' ) );
	}

	/** Filter the CTLE alert recipient list. */
	return (array) apply_filters( 'ctle_alert_recipients', $recipients );
}

/**
 * Best-effort real client IP. Kinsta sits behind Cloudflare, so REMOTE_ADDR is an
 * edge IP; the real visitor IP is forwarded in these headers. This value is used only
 * to enrich an informational email — never for an access-control decision — so
 * reading it straight from the header is acceptable here.
 *
 * @return string
 */
function ctle_alert_client_ip() {
	foreach ( array( 'HTTP_TRUE_CLIENT_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$parts = explode( ',', wp_unslash( $_SERVER[ $key ] ) );
			return sanitize_text_field( trim( $parts[0] ) );
		}
	}
	return 'unknown';
}

/**
 * Shared trailer appended to every alert body.
 *
 * @return string
 */
function ctle_alert_context() {
	return implode( "\n", array(
		'Time:      ' . current_time( 'mysql' ) . ' (' . wp_timezone_string() . ')',
		'IP:        ' . ctle_alert_client_ip(),
		'Site:      ' . home_url(),
	) );
}

/**
 * (a) Alert on any login by a user holding the Administrator role.
 *
 * Administrator logins should be rare and deliberate once faculty are on SSO, so
 * alerting on all of them stays low-noise.
 */
add_action( 'wp_login', function ( $user_login, $user ) {
	if ( ! ( $user instanceof WP_User ) || ! in_array( 'administrator', (array) $user->roles, true ) ) {
		return;
	}

	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = sprintf( '[%s] Administrator login: %s', $site, $user->user_login );
	$body    = implode( "\n", array(
		'An account holding the Administrator role signed in.',
		'',
		'User:      ' . $user->user_login . ' (' . $user->user_email . ')',
		'User ID:   ' . $user->ID,
		'Roles:     ' . implode( ', ', (array) $user->roles ),
		ctle_alert_context(),
		'',
		'If this was not you or an expected CTLE admin, treat it as a security event.',
	) );

	wp_mail( ctle_alert_recipients(), $subject, $body );
}, 10, 2 );

/**
 * (b) Alert on any user role change.
 *
 * Fires on the WP admin "Change role to…" action and any programmatic set_role().
 *
 * NOTE ON NOISE LEVEL: Once SSO is live, every newly provisioned Faculty account
 * triggers a role set. If this is too noisy, you can suppress routine new-Faculty
 * provisioning by returning false from the 'ctle_alert_should_notify_role_change' filter
 * for that case, e.g.:
 *
 *   add_filter( 'ctle_alert_should_notify_role_change', function ( $notify, $user_id, $new_role, $old_roles ) {
 *       if ( empty( $old_roles ) && 'faculty' === $new_role ) { return false; } // new SSO faculty
 *       return $notify;
 *   }, 10, 4 );
 */
add_action( 'set_user_role', function ( $user_id, $new_role, $old_roles ) {
	$notify = apply_filters( 'ctle_alert_should_notify_role_change', true, $user_id, $new_role, $old_roles );
	if ( ! $notify ) {
		return;
	}

	$user = get_userdata( $user_id );
	$who  = $user ? $user->user_login . ' (' . $user->user_email . ')' : ( 'user #' . $user_id );

	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = sprintf( '[%s] Role change: %s', $site, $user ? $user->user_login : ( '#' . $user_id ) );
	$body    = implode( "\n", array(
		'A WordPress user role was changed.',
		'',
		'User:      ' . $who,
		'User ID:   ' . $user_id,
		'Old roles: ' . ( $old_roles ? implode( ', ', (array) $old_roles ) : '(none)' ),
		'New role:  ' . $new_role,
		ctle_alert_context(),
		'',
		'If this elevation or change was not expected, treat it as a security event.',
	) );

	wp_mail( ctle_alert_recipients(), $subject, $body );
}, 10, 3 );

<?php
/**
 * Plugin Name: CTLE Hardening
 * Description: Small defense-in-depth hardening for the CTLE site. Disables XML-RPC,
 *              stops advertising the pingback endpoint, and removes password
 *              authentication entirely. Kinsta also blocks XML-RPC attacks at the Nginx
 *              layer; this adds an application-layer stop.
 * Author:      Steven Endres (sendres@dom.edu)
 * Version:     1.1.0
 *
 * DEPLOY TO:   wp-content/mu-plugins/ctle-hardening.php
 *              A must-use plugin: always active, no activation step, and it cannot
 *              be deactivated from the WP admin UI.
 *
 * SAFE TO DISABLE XML-RPC: nothing CTLE runs needs it — SSO and WP Mail SMTP
 * (Microsoft Graph) both work without XML-RPC. It is a well-worn brute-force and
 * pingback-amplification surface, so it is turned off outright.
 *
 * ⚠️ RECOVERY PROCEDURE CHANGED IN 1.1.0. The standard recovery procedure — reset a
 * password over WP-CLI and log in with it — no longer works while this file is in place,
 * because password authentication is removed. The recovery sequence is now:
 *
 *     ssh <user>@<host> -p <port>
 *     mv ~/public/wp-content/mu-plugins/ctle-hardening.php ~/ctle-hardening.php.off
 *     cd public && wp user update <user> --user_pass="$(openssl rand -base64 24)"
 *     # ... log in, fix the problem, then put the file back:
 *     mv ~/ctle-hardening.php.off ~/public/wp-content/mu-plugins/ctle-hardening.php
 *
 */

defined( 'ABSPATH' ) || exit;

// Disable XML-RPC entirely (authenticated methods return the "disabled" fault).
add_filter( 'xmlrpc_enabled', '__return_false' );

// Stop advertising the pingback endpoint via the X-Pingback response header.
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

// Belt-and-suspenders: drop the pingback XML-RPC methods too. Pingbacks/trackbacks
// are already disabled in Settings → Discussion.
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

/**
 * Remove password authentication entirely (added 1.1.0).
 *
 * WHY: Kinsta confirmed 2026-07-29 that its automatic brute-force IP ban watches
 * `/wp-login.php` specifically. Since WPS Hide Login moved the form to a custom path,
 * that protection no longer covers the endpoint that actually accepts logins — the
 * custom path takes unlimited password attempts with no rate limit or ban.
 *
 * Rather than bolt a rate-limiting plugin onto a login nobody is supposed to use, this
 * removes the thing being attacked. Given the stated goal that "no account on the site
 * can be logged into with a password": faculty authenticate through Entra, and
 * administrators through MyKinsta auto-login, which issues an auth cookie directly and
 * does not run the username/password authenticators below. Brute force against a form
 * that cannot authenticate anyone is noise, not risk.
 *
 * Core registers these at priority 20 in `wp-includes/default-filters.php`, and
 * `wp-settings.php` loads that file before mu-plugins — so removing them here works.
 * `wp_authenticate_cookie` is deliberately left alone: it is what keeps existing
 * sessions valid.
 *
 */
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );

// Application passwords are a password-shaped credential for REST/XML-RPC. Nothing
// CTLE runs uses them, and they would reintroduce exactly what is removed above.
remove_filter( 'authenticate', 'wp_authenticate_application_password', 20 );
add_filter( 'wp_is_application_passwords_available', '__return_false' );

// With password login gone, a reset link only produces a credential that cannot be
// used — a dead end for the user and a mail-generating endpoint for everyone else.
add_filter( 'allow_password_reset', '__return_false' );

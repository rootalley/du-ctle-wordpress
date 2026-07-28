<?php
/**
 * Plugin Name: CTLE Hardening
 * Description: Small defense-in-depth hardening for the CTLE site. Disables XML-RPC and
 *              stops advertising the pingback endpoint. Kinsta also blocks XML-RPC
 *              attacks at the Nginx layer; this adds an application-layer stop.
 * Author:      Steven Endres (sendres@dom.edu)
 * Version:     1.0.0
 *
 * DEPLOY TO:   wp-content/mu-plugins/ctle-hardening.php
 *              A must-use plugin: always active, no activation step, and it cannot
 *              be deactivated from the WP admin UI.
 *
 * SAFE TO DISABLE XML-RPC: nothing CTLE runs needs it — SSO, LTI Tool, and WP Mail SMTP
 * (Microsoft Graph) all work without XML-RPC. It is a well-worn brute-force and
 * pingback-amplification surface, so it is turned off outright.
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
// were already disabled in Settings → Discussion (§4).
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );

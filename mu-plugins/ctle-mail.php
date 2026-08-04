<?php
/**
 * Plugin Name: CTLE Mail (Microsoft Graph)
 * Description: Routes all wp_mail() through Microsoft Graph sendMail using an Entra
 *              app registration and the client-credentials flow, sending as the
 *              ctle-noreply@dom.edu shared mailbox. Replaces WP Mail SMTP, whose
 *              Microsoft 365 mailer is Pro-only and delegated-only — a shared mailbox
 *              with sign-in blocked cannot complete a delegated flow.
 * Author:      Steven Endres (sendres@dom.edu)
 * Version:     1.0.0
 *
 * DEPLOY TO:   wp-content/mu-plugins/ctle-mail.php
 *              A must-use plugin: always active, no activation step, and it cannot
 *              be deactivated from the WP admin UI.
 *
 * REQUIRES in wp-config.php — never the database, never version control:
 *
 *     define( 'CTLE_MAIL_TENANT_ID',     '...' );
 *     define( 'CTLE_MAIL_CLIENT_ID',     '...' );
 *     define( 'CTLE_MAIL_CLIENT_SECRET', '...' );
 *     define( 'CTLE_MAIL_FROM',          'ctle-noreply@dom.edu' );  // optional
 *
 * The registration needs the Graph **application** permission Mail.Send (not delegated),
 * admin-consented, and constrained by an application access policy to that one mailbox.
 * Without the policy the app can send as any mailbox in the tenant.
 *
 * The client secret expires. Diary the date the day it is issued; when it lapses,
 * every send fails at the token step and the failure is visible only in the PHP
 * error log, because the thing that would email you about it is this file.
 */

defined( 'ABSPATH' ) || exit;

const CTLE_MAIL_TOKEN_TRANSIENT = 'ctle_mail_graph_token';
const CTLE_MAIL_GRAPH_BASE      = 'https://graph.microsoft.com/v1.0';
const CTLE_MAIL_HTTP_TIMEOUT    = 15;

/**
 * The mailbox to send as. Graph derives the sender from the sendMail URL, so this is
 * both the API path segment and the visible From address.
 *
 * @return string
 */
function ctle_mail_from() {
	$from = defined( 'CTLE_MAIL_FROM' ) ? CTLE_MAIL_FROM : 'ctle-noreply@dom.edu';
	return (string) apply_filters( 'ctle_mail_from', $from );
}

/**
 * Whether the three secrets are present. Checked before every send so a half-configured
 * site fails loudly at the first message rather than silently at the hundredth.
 *
 * @return bool
 */
function ctle_mail_is_configured() {
	foreach ( array( 'CTLE_MAIL_TENANT_ID', 'CTLE_MAIL_CLIENT_ID', 'CTLE_MAIL_CLIENT_SECRET' ) as $constant ) {
		if ( ! defined( $constant ) || '' === (string) constant( $constant ) ) {
			return false;
		}
	}
	return true;
}

/**
 * An application access token for Graph, cached in a transient.
 *
 * Entra issues these with a one-hour life. The cache expires five minutes early so a
 * token cannot be spent on a request that outlives it. On any failure the transient is
 * left unset, so the next send retries rather than inheriting a poisoned cache.
 *
 * @param bool $force Skip the cache — used to retry once after a 401.
 * @return string|WP_Error
 */
function ctle_mail_access_token( $force = false ) {
	if ( ! $force ) {
		$cached = get_transient( CTLE_MAIL_TOKEN_TRANSIENT );
		if ( is_string( $cached ) && '' !== $cached ) {
			return $cached;
		}
	}

	$response = wp_remote_post(
		'https://login.microsoftonline.com/' . rawurlencode( CTLE_MAIL_TENANT_ID ) . '/oauth2/v2.0/token',
		array(
			'timeout' => CTLE_MAIL_HTTP_TIMEOUT,
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'scope'         => 'https://graph.microsoft.com/.default',
				'client_id'     => CTLE_MAIL_CLIENT_ID,
				'client_secret' => CTLE_MAIL_CLIENT_SECRET,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'ctle_mail_token_http', 'Token request failed: ' . $response->get_error_message() );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || empty( $body['access_token'] ) ) {
		// Entra's error_description names the cause — expired secret, missing consent,
		// wrong tenant. Carry it through; it is the whole diagnosis.
		$detail = isset( $body['error_description'] ) ? $body['error_description'] : 'HTTP ' . $code;
		return new WP_Error( 'ctle_mail_token_rejected', 'Token request rejected: ' . $detail );
	}

	$lifetime = isset( $body['expires_in'] ) ? (int) $body['expires_in'] - 300 : 300;
	set_transient( CTLE_MAIL_TOKEN_TRANSIENT, $body['access_token'], max( 60, $lifetime ) );

	return $body['access_token'];
}

/**
 * Turn wp_mail()'s loose recipient formats into Graph recipient objects.
 *
 * Accepts an array or a comma-separated string, with or without a display name in
 * "Name <address>" form. Anything that is not a valid address is dropped rather than
 * passed to Graph, which rejects the whole message on one bad recipient.
 *
 * @param string|string[] $recipients
 * @return array[]
 */
function ctle_mail_recipients( $recipients ) {
	if ( ! is_array( $recipients ) ) {
		$recipients = explode( ',', (string) $recipients );
	}

	$out = array();

	foreach ( $recipients as $recipient ) {
		$recipient = trim( (string) $recipient );
		$name      = '';

		if ( preg_match( '/^(.*?)<(.+)>$/', $recipient, $matches ) ) {
			$name      = trim( $matches[1], " \t\"'" );
			$recipient = trim( $matches[2] );
		}

		if ( ! is_email( $recipient ) ) {
			continue;
		}

		$address = array( 'address' => $recipient );
		if ( '' !== $name ) {
			$address['name'] = $name;
		}

		$out[] = array( 'emailAddress' => $address );
	}

	return $out;
}

/**
 * Split wp_mail()'s headers into the pieces Graph wants.
 *
 * Only the headers that map onto a sendMail field are honoured — Cc, Bcc, Reply-To and
 * Content-Type. From is deliberately ignored: the sending mailbox is fixed by the app
 * access policy, and a From we cannot honour is better dropped than sent as a lie.
 *
 * @param string|string[] $headers
 * @return array{cc: array[], bcc: array[], reply_to: array[], content_type: string}
 */
function ctle_mail_parse_headers( $headers ) {
	$parsed = array(
		'cc'           => array(),
		'bcc'          => array(),
		'reply_to'     => array(),
		'content_type' => '',
	);

	if ( ! is_array( $headers ) ) {
		$headers = explode( "\n", str_replace( "\r\n", "\n", (string) $headers ) );
	}

	foreach ( $headers as $header ) {
		if ( false === strpos( (string) $header, ':' ) ) {
			continue;
		}

		list( $name, $value ) = explode( ':', (string) $header, 2 );

		switch ( strtolower( trim( $name ) ) ) {
			case 'cc':
				$parsed['cc'] = array_merge( $parsed['cc'], ctle_mail_recipients( $value ) );
				break;
			case 'bcc':
				$parsed['bcc'] = array_merge( $parsed['bcc'], ctle_mail_recipients( $value ) );
				break;
			case 'reply-to':
				$parsed['reply_to'] = array_merge( $parsed['reply_to'], ctle_mail_recipients( $value ) );
				break;
			case 'content-type':
				$type = strtolower( trim( explode( ';', $value )[0] ) );
				$parsed['content_type'] = $type;
				break;
		}
	}

	return $parsed;
}

/**
 * Base64 the attachments into Graph fileAttachment objects.
 *
 * Graph's simple sendMail carries attachments inline in the request body, which is
 * capped at 4 MB — and base64 adds a third. Anything over the ceiling below has to go
 * through the upload-session API instead, which nothing here needs; the send fails
 * rather than being silently truncated.
 *
 * @param string|string[] $attachments
 * @return array[]|WP_Error
 */
function ctle_mail_attachments( $attachments ) {
	if ( ! is_array( $attachments ) ) {
		$attachments = '' === trim( (string) $attachments ) ? array() : explode( "\n", str_replace( "\r\n", "\n", (string) $attachments ) );
	}

	$out   = array();
	$bytes = 0;

	foreach ( $attachments as $name => $path ) {
		$path = trim( (string) $path );
		if ( '' === $path ) {
			continue;
		}

		if ( ! is_readable( $path ) ) {
			return new WP_Error( 'ctle_mail_attachment_unreadable', 'Attachment not readable: ' . $path );
		}

		$contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( false === $contents ) {
			return new WP_Error( 'ctle_mail_attachment_unreadable', 'Attachment could not be read: ' . $path );
		}

		$encoded = base64_encode( $contents ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$bytes  += strlen( $encoded );

		if ( $bytes > 3 * 1024 * 1024 ) {
			return new WP_Error( 'ctle_mail_attachments_too_large', 'Attachments exceed the 3 MB inline limit.' );
		}

		$out[] = array(
			'@odata.type'  => '#microsoft.graph.fileAttachment',
			'name'         => is_string( $name ) ? $name : basename( $path ),
			'contentType'  => 'application/octet-stream',
			'contentBytes' => $encoded,
		);
	}

	return $out;
}

/**
 * POST a built message to Graph, retrying once on 401 with a fresh token.
 *
 * A 401 here usually means the cached token was revoked mid-life rather than that the
 * credentials are wrong, so one retry with a forced token distinguishes the two.
 *
 * @param array $message
 * @return true|WP_Error
 */
function ctle_mail_send_message( array $message ) {
	$url = CTLE_MAIL_GRAPH_BASE . '/users/' . rawurlencode( ctle_mail_from() ) . '/sendMail';

	$payload = wp_json_encode(
		array(
			'message'         => $message,
			'saveToSentItems' => (bool) apply_filters( 'ctle_mail_save_to_sent_items', false ),
		)
	);

	if ( false === $payload ) {
		return new WP_Error( 'ctle_mail_encode_failed', 'Message could not be encoded as JSON.' );
	}

	for ( $attempt = 0; $attempt < 2; $attempt++ ) {
		$token = ctle_mail_access_token( $attempt > 0 );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => CTLE_MAIL_HTTP_TIMEOUT,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'    => $payload,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ctle_mail_http', 'Graph request failed: ' . $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );

		// 202 Accepted with an empty body is the documented success response.
		if ( 202 === $code ) {
			return true;
		}

		if ( 401 === $code && 0 === $attempt ) {
			delete_transient( CTLE_MAIL_TOKEN_TRANSIENT );
			continue;
		}

		$body   = json_decode( wp_remote_retrieve_body( $response ), true );
		$detail = isset( $body['error']['message'] ) ? $body['error']['message'] : wp_remote_retrieve_body( $response );

		return new WP_Error( 'ctle_mail_rejected', sprintf( 'Graph sendMail returned %d: %s', $code, $detail ) );
	}

	return new WP_Error( 'ctle_mail_unauthorized', 'Graph sendMail returned 401 twice; check Mail.Send consent and the application access policy.' );
}

/**
 * Take over wp_mail() entirely.
 *
 * Returning a non-null value short-circuits core's PHPMailer path, so every message the
 * site produces — admin alerts, comment notifications, whatever SSO adds later — goes
 * through Graph or fails visibly. An unconfigured site returns false rather than null:
 * falling back to PHP mail() on this host would produce mail that appears to send,
 * arrives from the wrong domain, and lands in spam. A refusal is easier to diagnose.
 *
 * Failures fire wp_mail_failed with a WP_Error, matching core's contract, and are
 * written to the PHP error log — the one channel that does not depend on mail working.
 */
add_filter( 'pre_wp_mail', function ( $short_circuit, $atts ) {
	$fail = function ( WP_Error $error ) {
		error_log( 'ctle-mail: ' . $error->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		do_action( 'wp_mail_failed', $error );
		return false;
	};

	if ( ! ctle_mail_is_configured() ) {
		return $fail( new WP_Error( 'ctle_mail_unconfigured', 'CTLE_MAIL_TENANT_ID, CTLE_MAIL_CLIENT_ID and CTLE_MAIL_CLIENT_SECRET must be defined in wp-config.php.' ) );
	}

	$to = ctle_mail_recipients( isset( $atts['to'] ) ? $atts['to'] : array() );
	if ( empty( $to ) ) {
		return $fail( new WP_Error( 'ctle_mail_no_recipients', 'No valid recipient addresses.' ) );
	}

	$headers     = ctle_mail_parse_headers( isset( $atts['headers'] ) ? $atts['headers'] : '' );
	$attachments = ctle_mail_attachments( isset( $atts['attachments'] ) ? $atts['attachments'] : array() );

	if ( is_wp_error( $attachments ) ) {
		return $fail( $attachments );
	}

	// Core applies wp_mail_content_type inside the function we are replacing, so honour
	// it here for anything that sets HTML through the filter instead of a header.
	$content_type = $headers['content_type'];
	if ( '' === $content_type ) {
		$content_type = apply_filters( 'wp_mail_content_type', 'text/plain' );
	}

	$message = array(
		'subject'      => isset( $atts['subject'] ) ? (string) $atts['subject'] : '',
		'body'         => array(
			'contentType' => 'text/html' === $content_type ? 'HTML' : 'Text',
			'content'     => isset( $atts['message'] ) ? (string) $atts['message'] : '',
		),
		'toRecipients' => $to,
	);

	foreach ( array( 'cc' => 'ccRecipients', 'bcc' => 'bccRecipients', 'reply_to' => 'replyTo' ) as $key => $field ) {
		if ( ! empty( $headers[ $key ] ) ) {
			$message[ $field ] = $headers[ $key ];
		}
	}

	if ( ! empty( $attachments ) ) {
		$message['attachments'] = $attachments;
	}

	$sent = ctle_mail_send_message( apply_filters( 'ctle_mail_message', $message, $atts ) );

	return is_wp_error( $sent ) ? $fail( $sent ) : true;
}, 10, 2 );

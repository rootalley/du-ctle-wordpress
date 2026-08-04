# mu-plugins

Version-controlled **must-use plugins** for the CTLE WordPress site.

Files here deploy to `wp-content/mu-plugins/` on the Live environment. Must-use
plugins load automatically, need no activation, and **cannot be deactivated from the
WP admin UI** — the right home for security controls that should never be switched off.

## Files

| File | Purpose | Ref |
|---|---|---|
| `ctle-admin-alerts.php` | Emails the CTLE admin list on any Administrator login and any role change. Replaces WP Activity Log Premium's paid notifications with free core hooks. | §5, §7 |
| `ctle-hardening.php` | **v1.1.0** — disables XML-RPC, stops advertising the pingback endpoint, and **removes password authentication entirely** (username/password, email/password, and application-password authenticators; application passwords hidden; password reset off). | §6 |
| `ctle-mail.php` | Routes every `wp_mail()` through Microsoft Graph `sendMail` as `ctle-noreply@dom.edu`, using an Entra app registration and the client-credentials flow. Replaces WP Mail SMTP. | §15 |

> ⚠️ **`ctle-hardening.php` v1.1.0 changed the recovery procedure.** A WP-CLI password reset no longer grants a login while the file is in place — move it aside first:
>
> ```bash
> mv ~/public/wp-content/mu-plugins/ctle-hardening.php ~/ctle-hardening.php.off
> # ... reset, log in, resolve, then restore it
> mv ~/ctle-hardening.php.off ~/public/wp-content/mu-plugins/ctle-hardening.php
> ```
>
> Full context in `docs/kinsta_onboarding.md` §6 and §7. Verify MyKinsta auto-login on Staging after any change to this file, since it is the only remaining interactive path into WP Admin.

## Deploy

Upload the file into `wp-content/mu-plugins/` (create the directory if it does not
exist). Either:

- **SFTP** (MyKinsta → Sites → DU-CTLE → Info → SFTP/SSH), or
- **SSH:** `scp -P <port> mu-plugins/ctle-admin-alerts.php <user>@<host>:~/public/wp-content/mu-plugins/`

No activation step. Confirm it loaded:

```bash
wp plugin list --status=must-use
```

## Mail configuration

`ctle-mail.php` reads three secrets from `wp-config.php`. They belong there and in the
CTLE vault — **never** the database, never this repository:

```php
define( 'CTLE_MAIL_TENANT_ID',     '...' );
define( 'CTLE_MAIL_CLIENT_ID',     '...' );
define( 'CTLE_MAIL_CLIENT_SECRET', '...' );
define( 'CTLE_MAIL_FROM',          'ctle-noreply@dom.edu' );  // optional; this is the default
```

The Entra registration is **separate from the SSO one** and needs the Graph
**application** permission `Mail.Send` — not delegated — admin-consented, and constrained
by an application access policy to that single mailbox. Without the policy the app can
send as any mailbox in the tenant.

Until the credentials land, every send fails fast with `ctle_mail_unconfigured` rather
than falling back to PHP `mail()`. That is deliberate: a fallback would produce mail that
looks sent, arrives from the wrong domain, and lands in spam.

Test once the constants are in place:

```bash
wp eval 'var_dump( wp_mail( "sendres@dom.edu", "CTLE mail test", "body" ) );'
```

`true` means Graph accepted it (HTTP 202). `false` means it did not — the reason is in the
PHP error log, prefixed `ctle-mail:`. Failures also fire `wp_mail_failed`, so anything
listening for that hook still sees them.

**The client secret expires.** Diary the date the day it is issued. When it lapses every
send fails at the token step, and the only signal is the error log — because the thing
that would email you about it is this plugin.

## Notes

- **Recipients:** edit `ctle_alert_recipients()` in `ctle-admin-alerts.php` with the real
  CTLE admin addresses. Left empty, it falls back to the site Administration Email (§4).
- **WP Mail SMTP is to be deleted.** Its Microsoft 365 mailer is Pro-only *and*
  delegated-only, so it cannot send as a sign-in-blocked shared mailbox. `ctle-mail.php`
  takes over `wp_mail()` through `pre_wp_mail` regardless, so leaving it installed only
  adds an unused attack surface and two orphan database tables.

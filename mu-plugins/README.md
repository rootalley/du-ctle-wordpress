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
| `ctle-alerts-hold.php` | ⏳ **Temporary — delete at handover.** Narrows `ctle-admin-alerts.php`'s recipient list to `sendres@dom.edu` while single sign-on is tested and accounts are pre-created. | — |

> ⚠️ **`ctle-hardening.php` v1.1.0 changed the recovery procedure.** A WP-CLI password reset no longer grants a login while the file is in place — move it aside first:
>
> ```bash
> mv ~/public/wp-content/mu-plugins/ctle-hardening.php ~/ctle-hardening.php.off
> # ... reset, log in, resolve, then restore it
> mv ~/ctle-hardening.php.off ~/public/wp-content/mu-plugins/ctle-hardening.php
> ```
>
> Full context in `docs/kinsta_onboarding.md` §6 and §7. Verify MyKinsta auto-login on Staging after any change to this file, since it is the only remaining interactive path into WP Admin.

> ⏳ **`ctle-alerts-hold.php` is a debt with an expiry date.** `ctle-admin-alerts.php` emails the
> CTLE Director on every Administrator login and role change; during SSO testing those alerts
> would arrive before the conversation meant to introduce the work. The hold narrows the audience
> through the plugin's own `ctle_alert_recipients` filter and **suppresses nothing**, so a real
> security event still reaches someone.
>
> **Delete it at handover, and verify:**
>
> ```bash
> rm ~/public/wp-content/mu-plugins/ctle-alerts-hold.php
> cd public && wp eval 'print_r( ctle_alert_recipients() );'   # both addresses back
> ```
>
> Left in place it removes the Director from a security control she is supposed to hold, and
> nothing about the running site would show it — the alerts keep arriving for whoever is left.

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

**Status: working since 2026-08-05.** The Exchange `RestrictAccess` policy scoping the app to
that one mailbox took **over 24 hours** to propagate after DU IT created it — during which every
send returned `403 [RAOP] Access to OData is disabled`, indistinguishable from a
misconfiguration. If you ever rebuild this, wait a full day before concluding the policy is
wrong.

Without credentials, every send fails fast with `ctle_mail_unconfigured` rather than falling
back to PHP `mail()`. That is deliberate: a fallback would produce mail that looks sent,
arrives from the wrong domain, and lands in spam.

Test:

```bash
wp eval 'var_dump( wp_mail( "sendres@dom.edu", "CTLE mail test", "body" ) );'
```

`true` means Graph accepted it (HTTP 202). `false` means it did not — the reason is in the
PHP error log, prefixed `ctle-mail:`. Failures also fire `wp_mail_failed`, so anything
listening for that hook still sees them.

**The client secret expires 2028-08-02.** A renewal reminder sits in `PLAN.md` for 2028-07-02.
When it lapses every send fails at the token step, and the only signal is the error log —
because the thing that would email you about it is this plugin.

### Verifying the scope is a live control

The app must be able to send as `ctle-noreply@dom.edu` and **nothing else**. Configured is not
the same as enforced, so check it rather than assume — re-run this whenever the app
registration changes:

```bash
wp eval 'add_filter("ctle_mail_from", function(){ return "sendres@dom.edu"; });
         var_dump( wp_mail( "sendres@dom.edu", "should be denied", "x" ) );'
```

`bool(false)` with `403` in the error log is the **correct** result. `bool(true)` means the
restriction is gone and the app can send as any mailbox in the tenant — treat that as a
security incident.

## Notes

- **Recipients:** edit `ctle_alert_recipients()` in `ctle-admin-alerts.php` with the real
  CTLE admin addresses. Left empty, it falls back to the site Administration Email (§4).
- **WP Mail SMTP was deleted 2026-08-04.** Its Microsoft 365 mailer is Pro-only *and*
  delegated-only, so it could never send as a sign-in-blocked shared mailbox. Its two tables
  (`wp_wpmailsmtp_debug_events`, `wp_wpmailsmtp_tasks_meta`) survived the uninstall and are
  left for the pre-launch cleanup.
- **The site timezone is unset**, so `ctle-admin-alerts.php` renders its timestamps in UTC
  rather than Central. Fix in Settings → General before launch.

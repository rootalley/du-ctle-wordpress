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

## Notes

- **Recipients:** edit `ctle_alert_recipients()` in `ctle-admin-alerts.php` with the real
  CTLE admin addresses. Left empty, it falls back to the site Administration Email (§4).
- **Email delivery** depends on WP Mail SMTP wired to Microsoft Graph (§15 / IT-2).
  Until that is live, alerts are generated but not delivered — expected; verify once IT-2 lands.

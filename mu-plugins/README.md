# mu-plugins

Version-controlled **must-use plugins** for the CTLE WordPress site.

Files here deploy to `wp-content/mu-plugins/` on the Live environment. Must-use
plugins load automatically, need no activation, and **cannot be deactivated from the
WP admin UI** — the right home for security controls that should never be switched off.

## Files

| File | Purpose | Ref |
|---|---|---|
| `ctle-admin-alerts.php` | Emails the CTLE admin list on any Administrator login and any role change. Replaces WP Activity Log Premium's paid notifications with free core hooks. | §5, §7 |
| `ctle-hardening.php` | Defense-in-depth: disables XML-RPC and stops advertising the pingback endpoint. | §6 |

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

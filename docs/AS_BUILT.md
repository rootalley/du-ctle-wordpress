# CTLE WordPress — As-Built Configuration

**What this is:** the recorded state of the CTLE WordPress infrastructure — what *is*, not what to do. For what to do, see `PLAN.md`.

**Last full capture:** 2026-08-06 · **Partially re-verified:** 2026-08-18 (DNS, TLS, users, plugins, OIDC constants) and 2026-08-14 (Live options, themes, OIDC plugin source) · **Maintainer:** Steven Endres

> ⚠ **A full recapture is owed.** The 08-14 update was targeted, not a `scripts/audit-env.sh` run. Re-run the script against both environments once Job 4 lands and regenerate this file from it.

> **Regenerate this.** Everything below came from `scripts/audit-env.sh`, run against both environments:
>
> ```bash
> ssh -i ~/.ssh/id_ed25519_ctle_sendres_kinsta -p <port> ductle@163.192.209.112 'sh -s' \
>   < scripts/audit-env.sh > docs/audit/<env>-$(date +%F).txt
> ```
>
> Raw output lands in `docs/audit/`, which is **gitignored** — it contains staff email addresses. Re-run after any significant change and update this file from it. Do not hand-edit values here without re-capturing; a document that drifts from the machine is worse than none.

**No secrets in this file.** No passwords, no client secrets. Those live in the CTLE vault and DU SecureTransfer. (There is no custom login path to protect as of 2026-08-18.)

---

## ✅ Both 2026-08-06 warnings are withdrawn

**1. `ctle.dom.edu` resolves and serves.** Re-verified 2026-08-14:

```
dig +short ctle.dom.edu        → 162.159.135.42
dig +short www.ctle.dom.edu    → ctle.dom.edu. → 162.159.135.42
curl -I https://ctle.dom.edu/  → HTTP/2 200
```

This is exactly the A record and `www` CNAME that DU IT's ticket 26363781 recorded, so the original delivery stands and no DNS ticket is needed.

**What the 08-06 NXDOMAIN was remains unresolved.** It came from `ns1.dom.edu` carrying the authoritative-answer flag. As of 2026-08-14 `ns1.dom.edu` does not resolve at all — the `dom.edu` NS set is `dc1`, `dc2`, `dc3`, `az-dc1`, `dc2012r2-2` and `prioryserv`, none of which answer from off-campus. So either the record was genuinely absent and has since been restored, or `ns1.dom.edu` was a decommissioned server still answering for a zone it no longer held. Both fit the evidence. **The durable lesson is procedural: query the NS set the zone actually publishes, not a nameserver hostname you remember.**

**2. Staging's database instability no longer threatens anything.** MariaDB was observed down on 2026-08-04 and 2026-08-06 with `Can't connect to local server through socket '/run/mysqld/mysqld.sock'`, restarting on its own each time. That was recorded as urgent on the belief that Staging held the only copy of the site build.

**It did not.** Amanda Norris confirmed on 2026-08-14 that she used Staging only for basic testing and never developed the site there. What is on it is `educational-university` demo content imported by `ansar-import`. **No manual backup is needed, no Kinsta support ticket is warranted, and the defensive dump at `~/staging-safety-2026-08-08-0202.sql` is now only a curiosity.**

---

## Environments

Both were provisioned at account setup and were present at first MyKinsta login. **They have no common ancestor after the original 2026-05-28 WordPress install** — they were never cloned from one another, and have diverged independently ever since. This is the single most important fact about this deployment.

| | Live | Staging |
|---|---|---|
| Host | `pjl-ductle-live-prod.incus` | `knh-ductle-staging-staging.incus` |
| Configured URL | `https://ctle.dom.edu` | `https://stg-ductle-staging.kinsta.cloud` |
| Actually reachable at | `https://ctle.dom.edu` → 200, and `https://ductle.kinsta.cloud` → 200 | its Kinsta hostname → 401 (Basic Auth) |
| SSH port | 26769 | 50378 |
| SSH user / IP | `ductle@163.192.209.112` | same |
| Webroot | `/www/ductle_136/public` | same |
| PHP (web + CLI) | 8.4.23 | 8.4.23 |
| WP-CLI | 2.12.0 | 2.12.0 |
| WordPress core | **7.0.4** — was 7.0.2 on 08-06, so core auto-updates are running | 7.0.2 as of 08-06, not re-checked |
| Disk used | 108 MB | 93 MB |
| Database | Stable | Intermittently down — see above, now harmless |
| Password protection | **Disabled** — deliberately; turns on at handover | **Enabled** (HTTP Basic Auth at Nginx). **It intercepts MyKinsta auto-login**: the 401 consumes the auto-login link and drops you on the front page, so click *Log in to WP Admin* a second time once the browser holds the credentials |
| Backups | Daily + manual baselines | Daily only, no manual baseline — **no longer a risk** |
| Role | Production, **and the build environment**; holds all infrastructure and security work | **Disposable.** Theme demo content only |

> ### The login journey — no WordPress login form is ever shown
>
> `OIDC_LOGIN_TYPE` is `auto`, so `/wp-login.php` builds a fresh authorization request server-side
> and redirects to Entra. Verified 2026-08-18: consecutive requests return `302` to
> `login.microsoftonline.com` with **different `state` values** and `x-kinsta-cache: BYPASS`.
> Both journeys route through it — a deep link into a restricted area via `auth_redirect()`, and
> the Canvas global-navigation button, which targets `https://ctle.dom.edu/wp-login.php`.
>
> **WPS Hide Login was removed on 2026-08-18 and must not be reinstalled.** Its custom slug was
> outside Kinsta's cache exclusions, so the edge cached the login page at `s-maxage=86400` and
> froze the one-time `state` (TTL 180 s) into it — every sign-in returned `invalid-state`. The same
> blind spot had already cost Kinsta's edge brute-force ban, which watches `/wp-login.php` alone.
> Recorded as a dated deviation in `REQUIREMENTS.md` §5.
>
> **Never store a copied authorization URL anywhere.** It embeds a single-use 180-second nonce;
> linking to it from Canvas, a bookmark or documentation fails for every user, permanently.
>
> **DU IT signed off this model on 2026-08-18** without the obfuscated login path, agreeing it adds
> nothing once users are directed to Entra. The orphaned `whl_page` option has been deleted.
>
> ### MyKinsta auto-login matches on email
>
> Established by test on Staging 2026-08-18: a hand-created account carrying the right address is
> adopted rather than duplicated. MyKinsta mints a fresh random username per environment, so
> usernames are not an identifier — the same person is `pdriveru8gf` on Live and `pdriverdebl` on
> Staging. **Pre-creating an admin account is therefore safe and absorbs both provisioning paths.**

**One SSH key pair per person, both environments** — `id_ed25519_ctle_sendres_kinsta`. Kinsta permits one SSH user per environment; additional MyKinsta members authorise their own keys against that same user. Kinsta "additional users" are SFTP-only and are **not** a WP-CLI recovery path.

---

## Live — production

### WordPress

| Setting | Value |
|---|---|
| `siteurl` / `home` | `https://ctle.dom.edu` — resolving and serving since at least 2026-08-14 |
| `blog_public` | `0` — search engines discouraged (**launch gate: must flip to 1**) |
| Active theme | `twentytwentyfive` 1.5 |
| `show_on_front` | `posts` (no static front page set) |
| `users_can_register` | `0` |
| `default_role` | `subscriber` |
| `admin_email` | `ctle@dom.edu` |
| Privacy policy page | ID 3 (draft), registered as `wp_page_for_privacy_policy` |
| `DISABLE_WP_CRON` | `1` — Kinsta system cron calls `wp-cron.php` |
| Timezone | ***not set*** — so all timestamps, including those in the admin alert emails, render as UTC rather than Central. Worth fixing before launch. |

### Plugins

| Plugin | Version | Status |
|---|---|---|
| ~~WPS Hide Login~~ | ~~1.9.18~~ | **Deleted 2026-08-18.** It forfeited Kinsta's edge brute-force ban and broke SSO by putting the login page outside the host's cache exclusions. Do not reinstall — see the login-journey note above. |
| WP Activity Log (`wp-security-audit-log`) | 5.6.5 | Active |
| Query Monitor | 4.0.7 | Active |
| OpenID Connect Generic (`daggerhart-openid-connect-generic`) | 3.11.3 | **Active since 2026-08-14. Sign-in proven working 2026-08-18.** Configured against tenant `e363050e-…-7db1230b452a` via `OIDC_*` constants in `wp-config.php`. **No userinfo endpoint set**, deliberately. JWKS and issuer verification both active. MyKinsta auto-login re-confirmed in a fresh private session after activation. **Client ID and secret corrected 2026-08-18** — they had held the mail app's values. Tenant admin consent granted 2026-08-18; first successful sign-in matched user ID 3 on email and stamped `openid-connect-generic-subject-identity`. **`employeeId` did not arrive in the ID token** — see `PLAN.md` `4b`. **`OIDC_ENABLE_LOGGING` is still on** and the log holds decoded claims; Job 6 turns it off. |
| Relevanssi | 4.27.2 | Inactive — staged for the search build |
| wpForo | 3.1.4 | Inactive — staged for forums |

**WP Mail SMTP was deleted 2026-08-04.** Its Microsoft 365 mailer is Pro-only *and* delegated-only, so it could never send as a sign-in-blocked shared mailbox. Its two tables survived the uninstall and remain (see Database tables).

Themes present: `twentytwentyfive` (active), `twentytwentyfour`, `twentytwentythree`. Deleted 2026-07-29: Akismet, Hello Dolly, LTI Tool, ceLTIc LTI Library.

### Must-use plugins

Source of truth is `mu-plugins/` in this repo. Deployed to `wp-content/mu-plugins/`.

| File | Version | Size on Live | Purpose |
|---|---|---|---|
| `ctle-admin-alerts.php` | 1.0.0 | 5394 B | Emails on any Administrator login or role change. Recipients: `sendres@dom.edu`, `pdriver@dom.edu`. **Delivery verified 2026-08-05.** |
| `ctle-hardening.php` | 1.1.0 | 3983 B | XML-RPC off, `X-Pingback` removed, **password authentication removed entirely** |
| `ctle-mail.php` | 1.0.0 | 13479 B | **Takes over `wp_mail()` via `pre_wp_mail`** and posts to Microsoft Graph `sendMail` as `ctle-noreply@dom.edu`. Deployed 2026-08-04, delivering since 2026-08-05. |
| `kinsta-mu-plugins` | 3.6.1 | — | Vendor-supplied, do not modify |

> ⏳ **`ctle-alerts-hold.php` exists in the repo but is NOT yet deployed** as of 2026-08-18. It narrows `ctle-admin-alerts.php`'s recipients to `sendres@dom.edu` for the duration of the communications hold. **It must be deployed before the next Administrator sign-in or account creation**, because both alert the CTLE Director — see `PLAN.md` `2a`. When it is deployed, this table gains a fourth row and the Security controls table below changes. **It is a debt to delete at handover.**

### Users

| ID | Login | Email | Role | `sis_user_id` |
|---|---|---|---|---|
| 2 | `pdriveru8gf` | pdriver@dom.edu | Administrator | 542588 |
| 3 | `sendresiq78` | sendres@dom.edu | Administrator | 904238 |

Both are MyKinsta auto-login accounts with **no password**.

> **`sis_user_id` is not what SSO matches on.** It was stamped on the belief that OpenID Connect Generic could match an `employeeId` claim against it. Reading the plugin source on 2026-08-14 disproved that: identity is `id_token.sub` against the plugin's own `openid-connect-generic-subject-identity` meta, with an optional fallback to `email_exists()`. See `PLAN.md` Job 4. The stamps are retained because Job 5's Canvas and SIS work wants the Jenzabar ID on the account — they are simply not load-bearing for authentication.

**Amanda (`anorris@dom.edu`) has no account on Live** as of 2026-08-18. An account carrying that address must exist before her first SSO sign-in or the plugin creates a duplicate. **This is no longer waiting on her:** `PLAN.md` `2b` creates it by WP-CLI, since who creates the account is irrelevant to email matching. Update this table once it exists.

### Content

Bare install: 1 post (`Hello world!`), 2 pages (`Sample Page`, `Privacy Policy` draft), 0 media. Sample content removal is CTLE's (**launch gate**).

### Database tables

Core WordPress, plus `wp_actionscheduler_*` (4), `wp_wsal_*` (2), and `wp_wpmailsmtp_*` (2) — the last **orphaned**, left behind when the plugin was deleted. Dropping them is a pre-launch cleanup item, not urgent. `wp_options` is 2.1 MB.

---

## Staging — the Developer's build

> Captured 2026-08-06 while MariaDB was down, so the database-derived figures below were re-taken on a subsequent successful connection. Filesystem figures are from the failed run and are unaffected.

### WordPress

| Setting | Value |
|---|---|
| `siteurl` / `home` | `https://stg-ductle-staging.kinsta.cloud` |
| `blog_public` | `0` |
| Active theme | **`educational-university` 0.3.5** (update available) |
| `show_on_front` | `page` → front page ID 18, posts page ID 26 |
| `DISABLE_WP_CRON` | *not set* — WP-Cron runs on page loads here |

### Plugins

Only `ansar-import` 2.1.2 is active — the theme's demo-content importer. Akismet and Hello Dolly remain installed but inactive; both were deleted on Live.

Themes: `educational-university` (active), `newsgoal`, `newsup`, and the three core themes. **None of Live's security or infrastructure plugins exist here.**

### Must-use plugins

`ctle-admin-alerts.php` (5394 B) and `ctle-hardening.php` (**4338 B**) are present. Live's hardening file is 3983 B and the repo's is 3975 B — same declared version, different build, so **Staging carries a divergent copy**. Live matches the repo. **`ctle-mail.php` is not deployed here**, which is correct: mail belongs to production.

### Users

| ID | Login | Email | Role |
|---|---|---|---|
| 1 | `topsecretuser` | ctle@dom.edu | Administrator |
| 2 | `anorrisieuv` | anorris@dom.edu | Administrator |
| 3 | `pdriverdebl` | pdriver@dom.edu | Administrator |
| 5 | `sendres19xb` | sendres@dom.edu | Administrator |

**`topsecretuser` still exists here.** It was deleted from Live on 2026-07-27. No account here carries `sis_user_id`.

**User IDs do not correspond across environments.** `wp_posts.post_author` stores these integers, so any content transfer must remap authorship:

| Person | Live ID | Staging ID |
|---|---|---|
| pdriver | 2 | 3 |
| sendres | 3 | 5 |
| anorris | — | 2 |

One `wp_navigation` row carries `post_author = 0`, which the remap in `PLAN.md` handles explicitly.

### Content — theme demo material, not a site build

> **Superseded 2026-08-14.** This section was headed "the work to preserve" and drove a 3-hour merge plan. **Amanda confirms she only did basic testing here and never developed the site on Staging.** The pages below are `educational-university`'s demo content, imported by `ansar-import`; the uploads are that demo's images. Nothing here is being transferred to Live. The inventory is retained only so that a future reader recognises this content for what it is if they find it.

73 `wp_posts` rows in total: 10 published pages plus 1 draft, 27 navigation menu items, 12 attachments, 15 revisions, 3 global-styles records and 1 navigation record. 13 MB of uploads across 60 files. Imported 2026-07-21.

| ID | Page |
|---|---|
| 18 | Dominican University CTLE *(front page)* |
| 21 | Consultations |
| 26 | Opportunities for Growth *(posts page)* |
| 36 | Teaching Best Practices |
| 38 | Instructional Technology & AI |
| 40 | New Faculty |
| 42 | Adjunct Faculty |
| 44 | Training & Certifications |
| 46 | ADA Resources |

Plus the inherited `Sample Page` (2) and `Privacy Policy` (3) — same IDs as Live, so no collision.

### Database tables

**Core WordPress only.** No custom tables — no page builder, no form plugin, nothing writing outside standard WordPress storage. This is what makes a content-level transfer viable.

---

## Divergence summary

| | Live | Staging |
|---|---|---|
| Security plugins | 4 active | none |
| Deleted plugins | removed | Akismet + Hello Dolly still present |
| Theme | `twentytwentyfive` | `educational-university` |
| Pages | 2 | 11 |
| Uploads | 0 | 60 files / 13 MB |
| `topsecretuser` | deleted | **present** |
| `sis_user_id` | stamped | absent |
| Custom tables | 8 | 0 |
| Password protection | disabled | enabled |
| Mail transport | `ctle-mail.php`, working | none |
| Database stability | stable | flapping, and now inconsequential |

**Staging → Live is still forbidden.** It would overwrite production's security configuration, and Kinsta carries environment settings — redirects, PHP, Nginx — *unconditionally*, even on a files-only push. "Push files only" is not the safeguard it appears to be. See `kinsta_onboarding.md` §24.

**Live → Staging is now safe**, because Staging holds nothing worth keeping. That is Kinsta's supported direction, and it is how Staging should be re-established as a mirror once Live has a real build.

---

## Infrastructure

### Domains, TLS, routing

- **`ctle.dom.edu` → `162.159.135.42`**, with `www.ctle.dom.edu` as a CNAME to it. Verified 2026-08-14; serves HTTP/2 200.
- `https://ductle.kinsta.cloud` also serves 200. With DNS working it reverts to what it always was — an accepted gap slated for post-launch removal, not the production entrance.
- TLS issued by Google Trust Services through Kinsta's Cloudflare layer: `CN=ctle.dom.edu`, issuer `WE1`, valid `2026-06-02` → **`2026-08-31`**. **The 2026-08-24 renewal check is live and no longer moot** — DNS resolves, so validation can complete.
- No CAA records on `dom.edu`, so issuance is unconstrained.
- HTTP/2 active.

### Performance

Kinsta CDN enabled; Cloudflare Polish in **Lossless** mode (replaces a server-side image plugin); page cache active with authenticated requests correctly `BYPASS`; bandwidth alerts on.

### PHP limits (Live, web SAPI)

`memory_limit` 256M · `max_execution_time` 300s · `upload_max_filesize` and `post_max_size` 128M · `display_errors` off · `log_errors` on.

> Read these from **Site Health → Info → Server** or Query Monitor. `wp eval` reports the CLI SAPI's limits (`-1` / `2M` / `8M`), not the site's.

### Backups

Kinsta daily automatic, 14-day retention, plus point-in-time restore. Manual baselines on Live: `pre-cleanup-2026-07-29`, `pre-build-2026-07-28`. **Staging has no manual baseline**, which is now a live risk given its database instability — a defensive SQL dump sits on the Staging container but is not a substitute. A CTLE-operated 30-day off-site backup remains a requirement, deferred post-launch.

---

## Security controls

| Control | State |
|---|---|
| Password authentication | **Removed site-wide** via `ctle-hardening.php` — core username/password, email/password, and application-password authenticators unhooked; application passwords hidden; password reset disabled |
| Administrator access | MyKinsta WP Admin auto-login only. No privileged account has a password. |
| Second factor | MyKinsta 2FA. Codes for the Company Owner go to `ctle@dom.edu`, **making that mailbox's access list a security control** |
| Login URL | **`/wp-login.php`, no longer obfuscated** — WPS Hide Login deleted 2026-08-18. It is not a form: `OIDC_LOGIN_TYPE=auto` redirects it straight to Entra, and `ctle-hardening.php` has removed password authentication site-wide, so there is nothing behind it to guess at. Restoring the default path also restores Kinsta's edge brute-force ban, which watches that path alone. Deviation recorded in `REQUIREMENTS.md` §5. |
| Brute-force | Kinsta's automatic IP ban watches `/wp-login.php` **only** — it does not follow a custom login path. Measured: `POST /wp-login.php` → 403 at the edge; `POST` to the custom path → 200, processed. This is why password authentication was removed rather than rate-limited, and **why the custom path was abandoned on 2026-08-18** — the ban applies again now. |
| Graph mail scope | The mail app can send as `ctle-noreply@dom.edu` **and no other mailbox** — verified live 2026-08-05, not merely configured. Re-run that check if the app registration changes. |
| XML-RPC | Disabled at both Nginx (403) and application layers |
| Open registration | Off |
| Audit logging | WP Activity Log (free tier — logs only; its email notifications are Premium, hence `ctle-admin-alerts.php`) |
| Administrator alerts | `ctle-admin-alerts.php` emails **`sendres@dom.edu` and `pdriver@dom.edu`** on every Administrator login and role change. ⏳ **To be narrowed to `sendres@dom.edu` alone** for the duration of the communications hold, once `ctle-alerts-hold.php` is deployed. That narrowing is reversible, suppresses nothing, and **must be undone at handover** — the Director is meant to hold this control. |
| Malware scanning | **Kinsta does not scan proactively.** Its service is reactive: free vendor-assisted cleanup after a confirmed compromise. Disclosed to DU IT. |

### Recovery

MyKinsta account standing is a single point of failure for Administrator access. SSH + WP-CLI is the fallback.

> **`ctle-hardening.php` must be moved aside before a WP-CLI password reset grants a login.**
>
> ```bash
> ssh ductle@163.192.209.112 -p <port>
> mv ~/public/wp-content/mu-plugins/ctle-hardening.php ~/ctle-hardening.php.off
> cd public && wp user create <username> <email> --role=administrator
> # resolve the incident, then restore the control:
> mv ~/ctle-hardening.php.off ~/public/wp-content/mu-plugins/ctle-hardening.php
> ```

Only Steven currently holds an SSH key. **Two-person recovery is not yet satisfied** — Amanda's key is outstanding.

---

## Integrations

| Integration | State |
|---|---|
| **Mail** | ✅ **Working since 2026-08-05.** `ctle-mail.php` routes every `wp_mail()` through Microsoft Graph `sendMail` as `ctle-noreply@dom.edu`, using a dedicated Entra app registration with the **application** `Mail.Send` permission, admin-consented and constrained by an Exchange `RestrictAccess` policy scoped to `CTLE-NoReplyGroup`. Credentials live in `wp-config.php` constants. **Client secret expires 2028-08-02.** |
| **Entra SSO** | Plugin **active since 2026-08-14**; configuration is the active job. Two app registrations exist in tenant `e363050e-…`: the **mail** app (`ddf…`) and the **SSO** app (`7b8…`, *CTLE WordPress Redirect*). **On 2026-08-14 the SSO constants were written with the mail app's client ID and secret**, which produced `AADSTS500113` and was misread as a missing redirect URI; Aidan diagnosed it from the tenant sign-in logs on 08-17 and it was **corrected 2026-08-18**. The redirect URI `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize` and the `email` claim have been present on the SSO registration since 08-14. Requested scope is `openid email profile` — no Graph or mailbox access. The allowlist group **`CTLE WordPress`** holds **Persis, Amanda, Ellen and Steven**; `employeeId` carries the **J1 (Jenzabar)** value for normally-onboarded accounts, including Ellen's. Manually created accounts — NAPs, `sw_` student workers — may have it empty, which will matter at launch if any `DOMFaculty` member was onboarded by hand. Entra ID P1 confirmed. **Built on Live only**, the config being hostname-bound. Matching is on **email**, not `employeeId` — see the note under *Live → Users*. **Outstanding: tenant admin consent for the app** (requested 2026-08-18), and confirmation of whether *Assignment required* is on with the group assigned to the app. |
| **Canvas** | LTI dropped. Faculty launch from the existing CTLE global-nav button retargeted to the SSO-initiation URL, with visibility gated on `declared_user_type=teacher` read from `/api/v1/users/self/logins`. Script built at `canvas/ctle-global-nav.js`, `enabled: false`, not yet uploaded. Pete owns adding `declared_user_type` to the nightly Jenzabar→Canvas import, after SSO works. |
| **Events calendar** | Not started. Events Calendar Pro licence unpurchased. |

---

## Credentials — pointers only

| Credential | Where it lives |
|---|---|
| MyKinsta Company Owner | Persis's own; self-recoverable via reset to `ctle@dom.edu` |
| ~~Custom login path~~ | **No longer exists.** Removed 2026-08-18; `/wp-login.php` is the login URL and is not a secret. |
| Staging Basic Auth | DU SecureTransfer, individually. Regenerated 2026-07-29. |
| SSH private key | `~/.ssh/id_ed25519_ctle_sendres_kinsta`, Steven's machine only |
| Entra client secrets | CTLE vault, and `wp-config.php` constants — **not** the database. Two now: the mail app (expires 2028-08-02) and the SSO app (expiry supplied 2026-08-14, diarise a month ahead) |

Two plaintext passwords were removed from `kinsta_onboarding.md` before its first commit; git history confirms neither was ever committed.

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 1.3.1 | 2026-08-18 | sendres | Recorded the **communications hold** and its two machine-side consequences: `ctle-admin-alerts.php` currently emails the CTLE Director on every Administrator login and role change, so it is now listed explicitly as a security control rather than left implicit in the mu-plugins table; and `ctle-alerts-hold.php` exists in the repo **undeployed**, flagged as a debt to delete at handover. Noted that Amanda's missing Live account is no longer a dependency on her. No environment state changed in this entry — it records intent and pending changes, not a capture. |
| 1.3.0 | 2026-08-18 | sendres | **Corrected the SSO credentials.** `OIDC_CLIENT_ID` and `OIDC_CLIENT_SECRET` held the mail app's values rather than the SSO app's; the resulting `AADSTS500113` was misattributed to a missing redirect URI on Entra's side, and Aidan found the true cause in the tenant sign-in logs. The redirect URI and `email` claim had been in place since 08-14. Recorded both registrations by GUID prefix so the two can never again be confused by inspection, and the requested scope. Sign-in now reaches the SSO app; blocked on tenant admin consent. Re-verified live: two users only, four active plugins, DNS/TLS unchanged with the certificate expiring 08-31. Targeted re-verification, not a full capture — **`scripts/audit-env.sh` is still owed.** |
| 1.2.0 | 2026-08-14 | sendres | **Both 08-06 warnings withdrawn.** `ctle.dom.edu` resolves to `162.159.135.42` with the `www` CNAME, serves 200 and holds a valid certificate to 08-31 — the record ticket 26363781 described is in place. The 08-06 NXDOMAIN came from `ns1.dom.edu`, which no longer resolves at all, so whether the record was ever truly absent cannot be determined; recorded as such rather than guessed. Amanda confirmed Staging was only basic testing, never the site build, so its database instability threatens nothing and its content is theme demo material — Live is now both production and the build environment, and Live → Staging pushes become safe. **Corrected the identity model:** reading OpenID Connect Generic 3.11.3's source on Live shows matching is `id_token.sub` against the plugin's own meta with an email fallback, never an arbitrary claim against `sis_user_id`; the stamps are retained for SIS purposes only. Aidan delivered SSO credentials and confirmed `employeeId` carries the J1 value. Targeted re-verification, not a full capture. |
| 1.1.0 | 2026-08-06 | sendres | Recaptured from both environments. **Two new findings dominate:** `ctle.dom.edu` returns authoritative NXDOMAIN, contradicting the previous record of DNS delivered under ticket 26363781; and Staging's MariaDB has been observed down twice, on an environment holding the only copy of the site build with no manual backup. Otherwise: mail now working through `ctle-mail.php`, WP Mail SMTP deleted with two tables orphaned, OpenID Connect Generic installed inactive, and the Graph send scope verified as a live control. |
| 1.0.0 | 2026-08-03 | sendres | Initial as-built, captured directly from both environments via `scripts/audit-env.sh`. Replaces the scattered "verified state" prose in `HANDOFF.md` and the checkbox state in `kinsta_onboarding.md`. Confirmed by capture rather than inference: `topsecretuser` present on Staging; user IDs misaligned across environments; `ctle-hardening.php` divergent on Staging; PHP already 8.4 on both; **no custom database tables on Staging**, which is what makes a content-level transfer viable instead of a Kinsta push. |

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

# CTLE WordPress — As-Built Configuration

**What this is:** the recorded state of the CTLE WordPress infrastructure — what *is*, not what to do. For what to do, see `PLAN.md`.

**Last verified:** 2026-08-03 by direct capture · **Maintainer:** Steven Endres

> **Regenerate this.** Everything below came from `scripts/audit-env.sh`, run against both environments:
>
> ```bash
> ssh -i ~/.ssh/id_ed25519_ctle_sendres_kinsta -p <port> ductle@163.192.209.112 'sh -s' \
>   < scripts/audit-env.sh > docs/audit/<env>-$(date +%F).txt
> ```
>
> Raw output lands in `docs/audit/`, which is **gitignored** — it contains staff email addresses. Re-run after any significant change and update this file from it. Do not hand-edit values here without re-capturing; a document that drifts from the machine is worse than none.

**No secrets in this file.** No passwords, no client secrets, no custom login path. Those live in the CTLE vault and DU SecureTransfer.

---

## Environments

Both were provisioned at account setup and were present at first MyKinsta login. **They have no common ancestor after the original 2026-05-28 WordPress install** — they were never cloned from one another, and have diverged independently ever since. This is the single most important fact about this deployment.

| | Live | Staging |
|---|---|---|
| Host | `pjl-ductle-live-prod.incus` | `knh-ductle-staging-staging.incus` |
| URL | `https://ctle.dom.edu` | `https://stg-ductle-staging.kinsta.cloud` |
| SSH port | 26769 | 50378 |
| SSH user / IP | `ductle@163.192.209.112` | same |
| Webroot | `/www/ductle_136/public` | same |
| PHP (web + CLI) | 8.4.23 | 8.4.23 |
| WP-CLI | 2.12.0 | 2.12.0 |
| WordPress core | 7.0.2 | 7.0.2 |
| Disk used | 116 MB | 93 MB |
| Password protection | **Disabled** | **Enabled** (HTTP Basic Auth at Nginx) |
| Backups | Daily + manual baselines | Daily only, no manual baselines |
| Role | Production; holds all infrastructure and security work | Holds the Developer's site build |

**One SSH key pair per person, both environments** — `id_ed25519_ctle_sendres_kinsta`. Kinsta permits one SSH user per environment; additional MyKinsta members authorise their own keys against that same user. Kinsta "additional users" are SFTP-only and are **not** a WP-CLI recovery path.

---

## Live — production

### WordPress

| Setting | Value |
|---|---|
| `siteurl` / `home` | `https://ctle.dom.edu` |
| `blog_public` | `0` — search engines discouraged (**launch gate: must flip to 1**) |
| Active theme | `twentytwentyfive` 1.5 |
| `show_on_front` | `posts` (no static front page set) |
| `users_can_register` | `0` |
| `default_role` | `subscriber` |
| `admin_email` | `ctle@dom.edu` |
| Privacy policy page | ID 3 (draft) |
| `DISABLE_WP_CRON` | `1` — Kinsta system cron calls `wp-cron.php` every 15 min |
| Timezone | *not set* |

### Plugins

| Plugin | Version | Status |
|---|---|---|
| WPS Hide Login | 1.9.18 | Active |
| WP Activity Log (`wp-security-audit-log`) | 5.6.5 | Active |
| Query Monitor | 4.0.7 | Active |
| WP Mail SMTP | 4.9.0 | Active — **unconfigured, and will not be used** (see Mail below) |
| Relevanssi | 4.27.2 | Inactive — staged for the search build |
| wpForo | 3.1.4 | Inactive — staged for forums |

Themes present: `twentytwentyfive` (active), `twentytwentyfour`, `twentytwentythree`. Deleted 2026-07-29: Akismet, Hello Dolly, LTI Tool, ceLTIc LTI Library.

### Must-use plugins

Source of truth is `mu-plugins/` in this repo. Deployed to `wp-content/mu-plugins/`.

| File | Version | Size on Live | Purpose |
|---|---|---|---|
| `ctle-admin-alerts.php` | 1.0.0 | 5394 B | Emails on any Administrator login or role change. Recipients: `sendres@dom.edu`, `pdriver@dom.edu`. **Cannot deliver until mail is configured.** |
| `ctle-hardening.php` | 1.1.0 | 3983 B | XML-RPC off, `X-Pingback` removed, **password authentication removed entirely** |
| `kinsta-mu-plugins` | 3.6.1 | — | Vendor-supplied, do not modify |

### Users

| ID | Login | Email | Role | `sis_user_id` |
|---|---|---|---|---|
| 2 | `pdriveru8gf` | pdriver@dom.edu | Administrator | 542588 |
| 3 | `sendresiq78` | sendres@dom.edu | Administrator | 904238 |

Both are MyKinsta auto-login accounts with **no password**. `sis_user_id` is stamped so SSO resolves to the existing account rather than creating a duplicate. **Amanda (`anorris@dom.edu`) has no account on Live** — she must auto-login once and be stamped before her first SSO sign-in.

### Content

Bare install: 1 post (`Hello world!`), 2 pages (`Sample Page`, `Privacy Policy` draft), 0 media. Sample content removal is CTLE's (**launch gate**).

### Database tables

Core WordPress, plus: `wp_actionscheduler_*` (4), `wp_wpmailsmtp_*` (2), `wp_wsal_*` (2). `wp_options` is 2.1 MB.

---

## Staging — the Developer's build

### WordPress

| Setting | Value |
|---|---|
| `siteurl` / `home` | `https://stg-ductle-staging.kinsta.cloud` |
| `blog_public` | `0` |
| Active theme | **`educational-university` 0.3.5** (update available) |
| `show_on_front` | `page` → front page ID 18, posts page ID 26 |
| `DISABLE_WP_CRON` | *not set* |

### Plugins

| Plugin | Version | Status |
|---|---|---|
| `ansar-import` | 2.1.2 | Active — theme demo-content importer |
| Akismet | 5.7 | Inactive — deleted on Live, still present here |
| Hello Dolly | 1.7.2 | Inactive — deleted on Live, still present here |

Themes: `educational-university` (active), `newsgoal`, `newsup`, and the three core themes. **None of Live's security or infrastructure plugins exist here.**

### Must-use plugins

Both present, but `ctle-hardening.php` is **4338 B** against Live's 3983 B and the repo's 3975 B. Same declared version, different build — **Staging carries a divergent copy.** Live matches the repo.

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

### Content — the work to preserve

11 pages, 27 navigation menu items, 12 attachments, 13 MB of uploads across 60 files, 3 global-styles records. Built 2026-07-21.

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

**No Kinsta push is safe in either direction.** See `PLAN.md` for the export/import method used instead, and `kinsta_onboarding.md` §24 for why the push mechanism itself is the hazard.

---

## Infrastructure

### Domains, TLS, routing

- Primary domain `ctle.dom.edu`. DNS by DU IT under ticket 26363781: A record → `162.159.135.42`, plus `www` CNAME.
- `http://ctle.dom.edu`, `http://www.ctle.dom.edu`, `https://www.ctle.dom.edu`, `http://ductle.kinsta.cloud` all redirect to `https://ctle.dom.edu/`.
- `https://ductle.kinsta.cloud` serves 200 without redirecting — **accepted gap**, Kinsta `noindex`es it and it is the DNS fallback route. Post-launch removal.
- TLS by Google Trust Services via Kinsta's Cloudflare layer. **Expires 2026-08-31** — inside the launch window. Auto-renews; verify 2026-08-24.
- No CAA records on `dom.edu`, so issuance is unconstrained.
- HTTP/2 active.

### Performance

Kinsta CDN enabled; Cloudflare Polish in **Lossless** mode (replaces a server-side image plugin); page cache active with authenticated requests correctly `BYPASS`; bandwidth alerts on.

### PHP limits (Live, web SAPI)

`memory_limit` 256M · `max_execution_time` 300s · `upload_max_filesize` and `post_max_size` 128M · `display_errors` off · `log_errors` on.

> Read these from **Site Health → Info → Server** or Query Monitor. `wp eval` reports the CLI SAPI's limits (`-1` / `2M` / `8M`), not the site's.

### Backups

Kinsta daily automatic, 14-day retention, plus point-in-time restore. Manual baselines on Live: `pre-cleanup-2026-07-29`, `pre-build-2026-07-28`. **Staging has no manual baseline.** A CTLE-operated 30-day off-site backup remains a requirement, deferred post-launch.

---

## Security controls

| Control | State |
|---|---|
| Password authentication | **Removed site-wide** via `ctle-hardening.php` — core username/password, email/password, and application-password authenticators unhooked; application passwords hidden; password reset disabled |
| Administrator access | MyKinsta WP Admin auto-login only. No privileged account has a password. |
| Second factor | MyKinsta 2FA. Codes for the Company Owner go to `ctle@dom.edu`, **making that mailbox's access list a security control** |
| Login URL | Obfuscated via WPS Hide Login; rotated 2026-07-29 after exposure. Path is **not recorded in this repo**. Treat as a speed bump, not a control. |
| Brute-force | Kinsta's automatic IP ban watches `/wp-login.php` **only** — it does not follow a custom login path. Measured: `POST /wp-login.php` → 403 at the edge; `POST` to the custom path → 200, processed. This is why password authentication was removed rather than rate-limited. |
| XML-RPC | Disabled at both Nginx (403) and application layers |
| Open registration | Off |
| Audit logging | WP Activity Log (free tier — logs only; its email notifications are Premium, hence `ctle-admin-alerts.php`) |
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
| **Entra SSO** | Mailbox and Entra work delivered by DU IT. Configuration pending a joint IT + LT working session. Option 1: DU IT refreshes an Entra group from the SIS faculty list; the app is gated on that group; WordPress JIT-provisions on first sign-in. Entra ID P1 confirmed. **Built on Live only** — the config is hostname-bound. |
| **Mail** | `ctle-noreply@dom.edu` shared mailbox created. Requires a **separate** Entra app registration with Graph `Mail.Send` **application** permission, admin-consented and scoped to that one mailbox via application access policy. **WP Mail SMTP cannot do this** — its Microsoft 365 mailer is Pro-only *and* delegated-only, and a sign-in-blocked shared mailbox cannot complete a delegated flow. Transport will be a custom mu-plugin using client credentials. |
| **Canvas** | LTI dropped. Faculty launch from the existing CTLE global-nav button retargeted to the SSO-initiation URL, with visibility gated on `declared_user_type=teacher` read from `/api/v1/users/self/logins`. Script built at `canvas/ctle-global-nav.js`, `enabled: false`, not yet uploaded. |
| **Events calendar** | Not started. Events Calendar Pro licence unpurchased. |

---

## Credentials — pointers only

| Credential | Where it lives |
|---|---|
| MyKinsta Company Owner | Persis's own; self-recoverable via reset to `ctle@dom.edu` |
| Custom login path | DU SecureTransfer, individually. **Never in this repo.** |
| Staging Basic Auth | DU SecureTransfer, individually. Regenerated 2026-07-29. |
| SSH private key | `~/.ssh/id_ed25519_ctle_sendres_kinsta`, Steven's machine only |
| Entra client secrets | CTLE vault, and `wp-config.php` constants — **not** the database |

Two plaintext passwords were removed from `kinsta_onboarding.md` before its first commit; git history confirms neither was ever committed.

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 1.0.0 | 2026-08-03 | sendres | Initial as-built, captured directly from both environments via `scripts/audit-env.sh`. Replaces the scattered "verified state" prose in `HANDOFF.md` and the checkbox state in `kinsta_onboarding.md`. Confirmed by capture rather than inference: `topsecretuser` present on Staging; user IDs misaligned across environments; `ctle-hardening.php` divergent on Staging; PHP already 8.4 on both; **no custom database tables on Staging**, which is what makes a content-level transfer viable instead of a Kinsta push. |

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

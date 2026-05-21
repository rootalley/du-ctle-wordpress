# CTLE WordPress Hosting — Kinsta Vetting Checklist

**Purpose:** Working checklist for Kinsta vendor evaluation. Use during vendor calls and email exchanges to confirm every requirement before contracting.

**Key:** `[DOC]` = confirmed from public documentation · `[CONFIRM]` = must verify with vendor · `[UNKNOWN]` = not found in research

**Budget ceiling:** ≤ $70/month · **Contract deadline:** May 30, 2026 · **IT briefing deadline:** May 22, 2026

---

**Plan / Tier:** Single 20GB  
**Monthly Cost:** $30/month (annual billing, $350/year)  
**Vendor contact:** [kinsta.com/contact/](https://kinsta.com/contact/)  
**Evaluator:** ___________________________  
**Date:** ___________________________  
**Vendor rep name / email:** ___________________________

---

## Phase 1 Plugin Stack — Verify Against Banned List (Do This First)

Kinsta maintains a published banned/incompatible plugins list. The primary restrictions are caching plugins (conflict with Kinsta's built-in caching), backup solution plugins, and server-based image optimization tools. Security scanner plugins are also discouraged (Wordfence is supported with specific configuration). Confirm every Phase 1 plugin.

| Plugin | Category | Status |
|---|---|---|
| miniOrange SAML SSO (or OpenID Connect Generic) | SSO / Authentication | [DOC] ✓ Still required for WordPress site SSO (faculty login via Entra) — Kinsta native SAML ([kinsta.com/docs/company-settings/wordpress-saml-sso/microsoft-entra-saml-sso/](https://kinsta.com/docs/company-settings/wordpress-saml-sso/microsoft-entra-saml-sso/)) covers MyKinsta dashboard login only. miniOrange confirmed compatible and recommended for enterprise IdP (Azure AD, Entra) use on Kinsta — [kinsta.com/blog/wordpress-security-workflows/](https://kinsta.com/blog/wordpress-security-workflows/) |
| Two Factor / WP 2FA (Melapress) | 2FA for break-glass account | [DOC] ✓ Both confirmed compatible and recommended by Kinsta — [kinsta.com/blog/wordpress-security-workflows/](https://kinsta.com/blog/wordpress-security-workflows/) |
| WP Activity Log | Audit logging | [DOC] ✓ Recommended by Kinsta as "one of the best plugins on the market" for activity logging — [kinsta.com/blog/wordpress-activity-log/](https://kinsta.com/blog/wordpress-activity-log/) |
| WPS Hide Login | Login URL obfuscation | [DOC] ✓ Recommended by Kinsta as "the most common and probably easiest way" to change the login URL. Note: Kinsta blog also suggests Perfmatters (premium, by a Kinsta team member) as a Kinsta-specific alternative — [kinsta.com/blog/wordpress-login-url/](https://kinsta.com/blog/wordpress-login-url/) |
| The Events Calendar Pro + Event Tickets | Events & registration | [DOC] ✓ Featured in Kinsta's WordPress events plugin roundup with no compatibility caveats — [kinsta.com/blog/wordpress-events/](https://kinsta.com/blog/wordpress-events/) |
| wpForo (free core) | Discussion forums | [DOC] ✓ Featured in Kinsta's WordPress forum plugins roundup with no compatibility caveats — [kinsta.com/blog/wordpress-forum-plugins/](https://kinsta.com/blog/wordpress-forum-plugins/) |
| LTI Platform for WordPress | LTI 1.3 / Canvas integration | [DOC] ✓ Verified — not on Kinsta's banned/incompatible plugins list ([kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/](https://kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/)). Kinsta permits any plugin not on that list; the banned categories are caching, backup, server-based image/video optimization, and certain performance/security tools — none describe an LTI 1.3 plugin (pure-PHP OIDC/JWKS handshake, no server binaries). Confirmed compatible. |
| WP Mail SMTP | Outbound email via M365 | [DOC] ✓ Recommended by Kinsta for configuring WordPress email delivery — [kinsta.com/blog/send-test-email/](https://kinsta.com/blog/send-test-email/) |
| Beaver Builder (or Gutenberg blocks) | Page builder | [DOC] ✓ Featured in Kinsta's WordPress page builders roundup with no compatibility caveats — [kinsta.com/blog/wordpress-page-builders/](https://kinsta.com/blog/wordpress-page-builders/) |
| Relevanssi (free) | Search | [DOC] ✓ Verified — not on Kinsta's banned/incompatible plugins list ([kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/](https://kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/)). Relevanssi is pure PHP/MySQL FULLTEXT indexing with no server-side binaries or caching conflicts, so it falls in none of the banned categories. Confirmed compatible. |
| ShortPixel / Smush / Imagify | Image optimization | [DOC] ✓ Plugin may not be needed — Kinsta CDN includes native image optimization via Cloudflare Polish: automatic WebP conversion (lossless and lossy modes) for GIF, PNG, and JPEG. Delivered at the CDN edge; does not alter stored files. If a plugin is still desired for bulk optimization or lazy loading, API-based plugins are allowed; server-side binaries are not — [kinsta.com/docs/wordpress-hosting/wordpress-cdn/kinsta-cdn/](https://kinsta.com/docs/wordpress-hosting/wordpress-cdn/kinsta-cdn/) |
| Forum privacy consent plugin | Custom / consent | [DOC] ✓ Verified — consent/privacy plugins (e.g., Complianz, CookieYes) do not appear on Kinsta's banned/incompatible plugins list, and the list has no consent-plugin category ([kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/](https://kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/)). Confirmed allowed. |
| GamiPress (Phase 2) | Achievements | [DOC] ✓ Recommended by Kinsta for gamification — [kinsta.com/blog/online-school-assignments/](https://kinsta.com/blog/online-school-assignments/) |
| PublishPress Permissions (Phase 2) | Access control | [DOC] ✓ PublishPress Capabilities (free tier of the same plugin) recommended by Kinsta for user role management — [kinsta.com/blog/optimize-wordpress-b2b-saas/](https://kinsta.com/blog/optimize-wordpress-b2b-saas/) |

**Vendor verification — resolved by research:** All 14 Phase 1 + Phase 2 plugins were checked against Kinsta's published banned/incompatible plugins list — none appear on it. Recommended contract-time step: ask Kinsta to confirm the banned/incompatible plugins list ([kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/](https://kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/)) is unchanged as of contracting.

**Outcome:** ✅ All confirmed &ensp; ⬜ One or more flagged

**Flagged plugins / caveats:** None. All 14 Phase 1 + Phase 2 plugins verified against Kinsta's published banned/incompatible plugins list; none are banned. Related note: Kinsta provides native CDN-edge image optimization (Cloudflare Polish), so a server-side image optimization plugin is unnecessary — if one is used it must be API-based, not a server-binary tool.

---

## H — Hosting Infrastructure

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| H-1 | Managed WordPress hosting (core updates, patching) | [DOC] ✓ | Included all plans — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-2 | PHP 8.1+ | [DOC] ✓ | Current PHP versions supported; selectable per site — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-3 | MySQL 8.0+ or MariaDB 10.6+ — must allow custom tables (wpForo creates 20+) | [DOC] ✓ | wpForo confirmed compatible on Kinsta — custom table creation is permitted — [kinsta.com/blog/wordpress-forum-plugins/](https://kinsta.com/blog/wordpress-forum-plugins/) |
| H-4 | Free auto-renewing SSL with HTTPS enforced | [DOC] ✓ | Included all plans — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-5 | Custom domain mapping — `ctle.dom.edu` | [DOC] ✓ | External subdomain mapping supported — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-6 | Staging environment — built-in, one-click push | [DOC] ✓ | Staging included all plans; selective push to production available — [kinsta.com/help/staging-environment/](https://kinsta.com/help/staging-environment/) |
| H-7 | SSH and/or SFTP access for developer | [DOC] ✓ | SSH and SFTP both included — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-8 | WP-CLI access | [DOC] ✓ | WP-CLI included via SSH — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-9 | US-based server location | [DOC] ✓ | Multiple US Google Cloud regions available — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| H-10 | Auto-scaling or burst capacity | [DOC] ⚠ | Partial — Preferred req. Kinsta's automatic-scaling feature applies only to its Application Hosting product (Kubernetes horizontal pod autoscaling), **not** Managed WordPress Hosting — [kinsta.com/changelog/automatic-scaling/](https://kinsta.com/changelog/automatic-scaling/). WordPress plans scale **vertically** via a manual plan upgrade (more CPU/RAM/PHP workers). Surges of anonymous/cached traffic are absorbed automatically by Cloudflare edge caching + isolated LXD containers, and bandwidth overages are billed (site stays online; alerts at 80%/100% — see S-2) rather than causing downtime. A sustained surge of authenticated/dynamic load beyond the plan's PHP workers would need a manual plan upgrade. Acceptable for CTLE's scale (~200–400 faculty, mostly cached public traffic). |

---

## S — Storage & Transfer

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| S-1 | Disk storage ≥ 10 GB | [DOC] ✓ 10 GB | Single 20GB plan includes 10 GB storage — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| S-2 | Bandwidth — specify cap and overage | [DOC] ✓ | Single 20GB plan: 20 GB server bandwidth + 125 GB CDN bandwidth/month. No visit cap. Overage rates: server bandwidth $0.50/GB; CDN bandwidth $0.05/GB. Alerts sent at 80% and 100% of plan usage. — [kinsta.com/pricing/?plan=single-20gb&interval=year](https://kinsta.com/pricing/?plan=single-20gb&interval=year) · [kinsta.com/docs/billing/wordpress-hosting-plans/overages/](https://kinsta.com/docs/billing/wordpress-hosting-plans/overages/) |
| S-3 | File upload limit configurable to ≥ 64 MB | [DOC] ✓ 128 MB | `upload_max_filesize` is set to 128 MB — [kinsta.com/docs/wordpress-hosting/site-management/](https://kinsta.com/docs/wordpress-hosting/site-management/) |

---

## C — Security & Compliance

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| C-1 | SOC 2 Type II + ISO 27001 — provide current attestation documents for DU IT | [DOC] ✓ Both confirmed | **Action:** Request current SOC 2 Type II attestation letter and ISO 27001 certificate — [trust.kinsta.com](https://trust.kinsta.com) |
| C-2 | Daily automated backups, ≥ 30-day retention | [DOC] ⚠ | **Vendor:** daily automated backups confirmed, but the **Single 20GB plan retains only 14 days**; 30-day retention is available only on a WP 20+ tier (not as an add-on on this plan), and that tier exceeds the $70/month budget. **Compensating control (DU-operated):** a scheduled job on a CTLE server performs a self-managed daily off-site backup — it SSHes into the site (SSH access per H-7; WP-CLI v2 preinstalled per H-8), runs `wp db export` plus an `rsync` of `wp-content`, and pulls a dated copy to CTLE storage retained 30+ days. This meets the 30-day-retention intent off the platform; Kinsta's 14-day backups remain the fast one-click restore tier. Operational notes: the job needs failure alerting, and server-side config (redirects, custom Nginx rules, IP Deny list, PHP/MySQL settings) is not in any file+database backup — document it separately. Verify with vendor: (1) whether SFTP/SSH egress counts toward metered server bandwidth (rsync deltas keep transfers small regardless); (2) that key-based non-interactive SSH auth is supported for an unattended scheduled job. — [kinsta.com/docs/wordpress-hosting/wordpress-backups/](https://kinsta.com/docs/wordpress-hosting/wordpress-backups/) |
| C-3 | Point-in-time restore | [DOC] ✓ | Confirmed. Any retained backup — automatic daily, manual, or system-generated — can be restored via the "Restore to" button in MyKinsta, not just the most recent. The Single 20GB plan retains 14 days of daily backups, each a full restorable snapshot of files, database, and configuration — [kinsta.com/docs/wordpress-hosting/wordpress-backups/](https://kinsta.com/docs/wordpress-hosting/wordpress-backups/) |
| C-4 | Off-site backup storage (preferred) | [DOC] ⚠ | **Vendor:** standard daily and manual backups are stored **on the same host machine as the live site** — not a separate region — [kinsta.com/docs/wordpress-hosting/wordpress-backups/disaster-recovery/](https://kinsta.com/docs/wordpress-hosting/wordpress-backups/disaster-recovery/). Vendor off-site option: the paid **External Backups add-on** ($2/month/site + $1/GB egress) pushes to Amazon S3 or Google Cloud Storage, but on a **weekly or monthly** schedule only — [kinsta.com/add-ons/](https://kinsta.com/add-ons/). **Compensating control (CTLE-operated):** the self-managed daily SSH/WP-CLI backup described in C-2 lands on a CTLE server — genuinely off-site and daily — fully satisfying this requirement's intent through a CTLE-side process. The External Backups add-on (~$2/month, well under budget) remains an optional hands-off complement for a weekly/monthly S3/GCS archive. |
| C-5 | On-demand manual backup | [DOC] ✓ | Confirmed. Sites → site → Backups → Manual → "Back up now" triggers an on-demand backup at any time (e.g., before a major update). Up to 5 manual backups retained for a minimum of 14 days — [kinsta.com/docs/wordpress-hosting/wordpress-backups/](https://kinsta.com/docs/wordpress-hosting/wordpress-backups/) |
| C-6 | Web Application Firewall (WAF) | [DOC] ✓ | Cloudflare WAF integrated — [kinsta.com/knowledgebase/wordpress-cdn/](https://kinsta.com/knowledgebase/wordpress-cdn/) |
| C-7 | DDoS mitigation | [DOC] ✓ | Cloudflare DDoS protection included — [kinsta.com/knowledgebase/wordpress-cdn/](https://kinsta.com/knowledgebase/wordpress-cdn/) |
| C-8 | Malware scanning and removal | [DOC] ⚠ | Partial — Required req. **Removal: ✓** — Kinsta's Security Pledge provides free, vendor-assisted malware cleanup if a hosted site is compromised (deep file scan, WP core reinstall, infected plugin/theme removal) — [kinsta.com/docs/service-information/malware-removal/](https://kinsta.com/docs/service-information/malware-removal/). **Proactive scanning: ✗** — Kinsta does not run automated/scheduled malware scanning of site files with customer alerting; the service is reactive (triggered by evidence of compromise). To meet the "automated malware scanning with alerting" half of the requirement, CTLE would add a plugin — Kinsta supports Wordfence with specific configuration. Pledge caveats: excludes sites with nulled plugins/themes; $100 fee if the customer does not complete required post-removal steps within one business day. |
| C-9 | Brute-force protection — must not interfere with obfuscated login URL (WPS Hide Login) | [DOC] ⚠ | Met, with one detail to confirm at contract. Kinsta provides brute-force protection: automatic IP ban after more than 6 failed login attempts per minute, auto-added Nginx rules blocking detected XML-RPC attacks (403), and network-level Cloudflare WAF + DDoS that is URL-path-independent (see C-6, C-7) — [kinsta.com/blog/wordpress-login-url/](https://kinsta.com/blog/wordpress-login-url/). Kinsta does not interfere with an obfuscated login URL: WPS Hide Login is not on the banned list and Kinsta's own blog discusses changing the login URL favorably — so the requirement's "or not interfere" clause is satisfied. To confirm: Kinsta does not publicly document whether its native failed-login IP-ban keys on the default `wp-login.php` path; the Cloudflare-layer protection remains effective regardless. Ask the vendor to confirm native login protection still applies once the URL is renamed. |
| C-10 | 2FA plugin support — must not block Two Factor or WP 2FA | [DOC] ✓ | Confirmed. Kinsta does not block WordPress 2FA plugins; Two Factor and WP 2FA are both verified compatible and recommended in Kinsta's security workflows guide — [kinsta.com/blog/wordpress-security-workflows/](https://kinsta.com/blog/wordpress-security-workflows/) (see plugin checklist above). TOTP-based 2FA on the break-glass recovery account is fully supported. |
| C-11 | Access and error logs available to developer | [DOC] ✓ | Log files available in Kinsta dashboard (My Kinsta → Logs) |
| C-12 | Data return/destruction policy on termination | [DOC] ✓ | Documented in Kinsta's DPA: upon termination, Kinsta makes reasonable efforts to delete all Customer Personal Data within 45 days and — at the customer's written direction — will return data and copies instead of deleting (subject to legal retention obligations) — [kinsta.com/legal/data-processing-addendum/](https://kinsta.com/legal/data-processing-addendum/) · [kinsta.com/legal/privacy-policy/](https://kinsta.com/legal/privacy-policy/). Export formats are covered by standard self-service tooling available throughout the contract: downloadable full-site backup archives (see C-5), SFTP/SSH file access (H-7), and phpMyAdmin SQL export (W-9). |
| C-13 | Breach notification — "without undue delay" (72 hours) | [DOC] ✓ | DPA uses "without undue delay" (GDPR Art. 33 standard) — [kinsta.com/legal/data-processing-addendum/](https://kinsta.com/legal/data-processing-addendum/) |
| C-14 | Willing to execute DU's data processing agreement or equivalent | [DOC] ✓ DPA available | Action: DU IT / legal must review and execute the Kinsta DPA before contracting — [kinsta.com/legal/data-processing-addendum/](https://kinsta.com/legal/data-processing-addendum/) |

---

## W — WordPress Platform Capabilities

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| W-1 | No restrictions on plugin installation (see plugin checklist above) | [DOC] ⚠ Banned list exists | Kinsta maintains a published banned/incompatible plugins list (caching, backup, server-based image/video optimization, and certain performance/security tools) — a real but narrow restriction. Plugin checklist now complete: all 14 CTLE Phase 1 + Phase 2 plugins verified against the list — none are banned. The ⚠ reflects that a restriction mechanism exists, not any impact on this project — [kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/](https://kinsta.com/docs/wordpress-hosting/wordpress-plugins-themes/wordpress-banned-incompatible-plugins/) |
| W-2 | Configurable PHP settings (memory limit, execution time, upload sizes) | [DOC] ✓ | PHP thread count and memory pool are self-service via MyKinsta dashboard. Other php.ini customizations (e.g., custom directives) require contacting Kinsta support — [community.kinsta.com/t/manage-your-site-s-php-threads-and-memory-use-for-optimal-performance/4558](https://community.kinsta.com/t/manage-your-site-s-php-threads-and-memory-use-for-optimal-performance/4558) |
| W-3 | Server-level cron support | [DOC] ✓ | Every site has its own crontab; configure via SSH (`crontab -e`) or ask support to add it. Available on all plans. Minimum interval: 5 minutes. — [kinsta.com/docs/wordpress-hosting/site-management/cron-jobs/](https://kinsta.com/docs/wordpress-hosting/site-management/cron-jobs/) |
| W-4 | Outbound SMTP on ports 587 and 465 | [DOC] ✓ | Ports 587, 465, and 2525 confirmed open. Port 25 blocked for spam prevention. — [kinsta.com/blog/smtp-port/](https://kinsta.com/blog/smtp-port/) |
| W-5 | No restrictions on outbound HTTP/HTTPS from PHP | [DOC] ✓ | No restrictions on outbound HTTP/HTTPS API calls from PHP. Kinsta documents outbound external API behavior as normal — a site connects out via its external IP, which third-party services can allowlist — [kinsta.com/blog/wordpress-performance-partial-failures/](https://kinsta.com/blog/wordpress-performance-partial-failures/). Outbound calls to Microsoft Entra token endpoints, LTI 1.3 OIDC/JWKS endpoints, and Panopto embeds are standard HTTPS (port 443) and are not blocked. Only port 25 (SMTP) is blocked for spam prevention — irrelevant here; mail uses ports 587/465 (see W-4). |
| W-6 | Multisite support or upgrade path (preferred) | [DOC] ✓ | Met via upgrade path. Kinsta supports WordPress Multisite on the WP 2 plan and higher, but not on the Single 35k / Single 20GB entry plans — [kinsta.com/knowledgebase/multisite/](https://kinsta.com/knowledgebase/multisite/). The CTLE plan (Single 20GB) does not itself support Multisite, but W-6 requires "Multisite support **or** an upgrade path" — a clear upgrade path exists (move to WP 2 or higher). Multisite is not a current requirement. |
| W-7 | PHP memory limit ≥ 256 MB | [DOC] ✓ 256 MB | "Kinsta's default PHP memory limit is 256MB, which is more than enough for most WordPress plugins and sites" — [kinsta.com/blog/php-threads-vs-php-memory-limit/](https://kinsta.com/blog/php-threads-vs-php-memory-limit/) |
| W-8 | PHP max_execution_time ≥ 120 seconds | [DOC] ✓ 300 s | Default max_execution_time is 300 seconds — [kinsta.com/blog/php-threads-vs-php-memory-limit/](https://kinsta.com/blog/php-threads-vs-php-memory-limit/) |
| W-9 | Database management tool; must allow custom tables | [DOC] ✓ | phpMyAdmin available; Kinsta database management in dashboard — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |

---

## P — Performance & Reliability

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| P-1 | Uptime SLA ≥ 99.9% (contractual) | [DOC] ✓ 99.9% | Contractual 99.9% SLA with credit structure — [kinsta.com/legal/service-level-agreement/](https://kinsta.com/legal/service-level-agreement/) |
| P-2 | CDN for static asset delivery | [DOC] ✓ | Cloudflare CDN included all plans — [kinsta.com/knowledgebase/wordpress-cdn/](https://kinsta.com/knowledgebase/wordpress-cdn/) |
| P-3 | Server-side page caching with authenticated-user bypass | [DOC] ✓ | Edge caching + Redis object cache; logged-in users and wp-admin bypass cache — [kinsta.com/help/edge-caching/](https://kinsta.com/help/edge-caching/) |
| P-4 | HTTP/2 or HTTP/3 | [DOC] ✓ | Confirmed. Kinsta supports HTTP/3 (with QUIC) across its Cloudflare-fronted infrastructure and the Kinsta CDN, plus HTTP/2; no customer configuration required — [kinsta.com/blog/http3/](https://kinsta.com/blog/http3/) · [kinsta.com/cloudflare-integration/](https://kinsta.com/cloudflare-integration/) |
| P-5 | Page load targets — < 3s uncached, < 2s cached | [DOC] ✓ | Google Cloud infrastructure + Cloudflare edge caching designed for these targets — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| P-6 | Resource isolation | [DOC] ✓ | Container-based architecture on Google Cloud; no noisy-neighbor sharing — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| P-7 | Image optimization support | [DOC] ✓ | Native CDN image optimization via Cloudflare Polish (WebP conversion, lossless/lossy compression) included — no plugin required. API-based plugins also allowed if needed. — [kinsta.com/docs/wordpress-hosting/wordpress-cdn/kinsta-cdn/](https://kinsta.com/docs/wordpress-hosting/wordpress-cdn/kinsta-cdn/) |

---

## T — Support

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| T-1 | Business-hours support (M–F, US time zones) | [DOC] ✓ | 24/7 support on all plans — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| T-2 | Ticket/email support; live chat preferred | [DOC] ✓ | 24/7 live chat and ticket support — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| T-3 | Published response time targets by severity | [DOC] ⚠ | Partial — Required req. Kinsta provides 24/7/365 support with a very fast median first response (1m 02s median across 120,000+ conversations in 2024) and monitors every site every 3 minutes, auto-notifying engineers on downtime — [kinsta.com/kinsta-support/](https://kinsta.com/kinsta-support/). However, Kinsta publishes **no severity-tiered response-time commitments** (e.g., site-down < 1 hr, degraded < 4 hr) — it runs a flat model with uniformly fast response. Its contractual SLA covers **uptime** credits (issued after > 43 min monthly downtime on the 99.9% tier), not support-response targets — [kinsta.com/legal/service-level-agreement/](https://kinsta.com/legal/service-level-agreement/). Intent met (uniformly fast); no formal severity SLA. |
| T-4 | WordPress-knowledgeable support staff | [DOC] ✓ | Kinsta engineers specialize in WordPress; no general hosting staff — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| T-5 | Defined escalation process | [DOC] ⚠ | Partial — Required req. Kinsta runs a deliberately **flat** support model — no tiers, no handoffs, no junior reps; every contact connects directly to a WordPress engineer (40+ experts, including WordPress core contributors and dedicated Linux engineers) — [kinsta.com/blog/kinsta-support-for-wordpress-developers/](https://kinsta.com/blog/kinsta-support-for-wordpress-developers/). There is no traditional frontline → escalation chain; complex infrastructure issues are routed internally to specialist Linux/SysOps engineers. The intent of T-5 (issues frontline cannot resolve reach someone who can) is satisfied by design, but Kinsta publishes no "defined escalation process" in the literal sense the requirement describes. |
| T-6 | Public status page | [DOC] ✓ | [https://kinsta.com/status/](https://kinsta.com/status/) |
| T-7 | Proactive monitoring and alerting | [DOC] ✓ | Uptime monitoring with automatic alerting — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |

---

## M — Migration & Onboarding

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| M-1 | Free migration assistance (preferred) | [DOC] ✓ | Free migrations available — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |
| M-2 | Onboarding documentation (DNS, SSL, staging, SFTP, SMTP) | [DOC] ✓ | Extensive documentation — [kinsta.com/docs/](https://kinsta.com/docs/) |
| M-3 | DNS configuration guidance — A/CNAME for `ctle.dom.edu` | [DOC] ✓ | Standard documentation available — [kinsta.com/docs/](https://kinsta.com/docs/) |
| M-4 | Environment provisioned within 24 hours | [DOC] ✓ | Typically instant upon account creation — [kinsta.com/wordpress-hosting/](https://kinsta.com/wordpress-hosting/) |

---

## K — Contractual & Commercial

| # | Requirement | Status | Vendor Response / Notes |
|---|---|---|---|
| K-1 | All-inclusive pricing ≤ $70/month; itemize overages | [DOC] ✓ $30/month | Single 20GB: $30/month ($350/year). No visit cap. Overage rates: server bandwidth $0.50/GB; CDN bandwidth $0.05/GB; disk space $2/GB/month. Extreme overages (≥ plan cost or $500) may trigger immediate billing. — [kinsta.com/pricing/?plan=single-20gb&interval=year](https://kinsta.com/pricing/?plan=single-20gb&interval=year) · [kinsta.com/docs/billing/wordpress-hosting-plans/overages/](https://kinsta.com/docs/billing/wordpress-hosting-plans/overages/) |
| K-2 | Willing to execute DU's DPA | [DOC] ✓ | DPA available — [kinsta.com/legal/data-processing-addendum/](https://kinsta.com/legal/data-processing-addendum/) |

---

## Summary

| Category | Items | [DOC] ✓ | [DOC] ⚠ | [CONFIRM] / [UNKNOWN] |
|---|---|---|---|---|
| Plugin stack (Phase 1+2) | 14 | 14 | 0 | 0 |
| Hosting Infrastructure (H) | 10 | 9 | 1 | 0 |
| Storage & Transfer (S) | 3 | 3 | 0 | 0 |
| Security & Compliance (C) | 14 | 10 | 4 | 0 |
| WordPress Platform (W) | 9 | 8 | 1 | 0 |
| Performance & Reliability (P) | 7 | 7 | 0 | 0 |
| Support (T) | 7 | 5 | 2 | 0 |
| Migration & Onboarding (M) | 4 | 4 | 0 | 0 |
| Contractual & Commercial (K) | 2 | 2 | 0 | 0 |
| **Total** | **70** | **62** | **8** | **0** |

**Monthly Cost:** $30/month  
**Overall Assessment:** ⬜ Recommended &ensp; ✅ Acceptable with caveats &ensp; ⬜ Not recommended

Kinsta fully meets 62 of 70 requirements according to their public documentation, with the other 8 requirements partially met. The partially met requirements are mostly preferred items or model differences; the backup-retention gap (C-2) can be mitigated by a DU-operated off-site backup process, leaving requirement C-8 ( proactive malware scanning) as the main concern. There were no failed or unresolved requirements. Additionally, Kinsta offers a strong path to add computing resources to the existing CTLE site if that should be necessary in the future. In summary, Kinsta appears to be a strong fit and is very affordable at $30/month, contingent on accepting the caveats below.

**Key Strengths:**

1. Strong platform and infrastructure fit — 62 of 70 line items fully met from public documentation, including all 14 plugins in the Phase 1+2 stack, container-isolated Google Cloud architecture, Cloudflare WAF/DDoS/CDN, HTTP/3, server-level cron, configurable PHP, and 24/7 WordPress-specialist support with a ~1-minute median first response.
2. Well under budget — $30/month against the $70 ceiling, leaving headroom for add-ons (e.g., External Backups at ~$2/month) and a future plan upgrade if scale or backup retention requires it.
3. Security and compliance posture well suited to DU IT review — SOC 2 Type II and ISO 27001, a signable Data Processing Addendum, Cloudflare WAF and DDoS mitigation, and a free vendor-assisted malware-removal pledge.

**Key Gaps or Risks:**

1. Backup retention shortfall (C-2, Required) — the Single 20GB plan retains only 14 days of daily backups versus the 30-day requirement (30-day retention needs a WP 20+ tier, likely over budget). **Mitigated** by a CTLE-operated self-managed daily off-site backup (see C-2): this avoids upsizing the plan, but adds an operational responsibility CTLE must commit to and monitor.
2. No proactive malware scanning (C-8, Required) — Kinsta's malware service is reactive (free hack-fix if compromised); it does not perform automated malware scanning with alerting. CTLE would add a plugin (Kinsta supports Wordfence with configuration) to fully meet the requirement.
3. Off-site backups and compute auto-scaling are not standard from the vendor (C-4, H-10 — both Preferred) — Kinsta's standard backups are same-host (off-site is a paid weekly/monthly add-on) and WordPress plans scale vertically via manual plan upgrades. C-4 is addressed by the same CTLE-operated off-site backup process noted above; H-10 is acceptable at CTLE's scale. Neither blocks selection.

**Evaluator Signature:** ___________________________  **Date:** ___________________________

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 0.1.0 | 2026-05-21 | sendres | Initial version. |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

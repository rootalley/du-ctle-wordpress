# CTLE WordPress — Implementation Phases

## Purpose

This document maps every requirement in `REQUIREMENTS.md` to one of three implementation phases to support hosting vendor selection and project planning.

The chosen hosting vendor must fully support all Phase 1 requirements at or under $70/month and must offer scalable upgrade paths for the **same single site** — more CPU, RAM, and storage on the same plan — not the ability to add more low-performance sites.

---

## Phase Definitions

| Phase | Label | Target | Hosting implication |
|---|---|---|---|
| **1** | Launch | August 2026 | The initial hosting plan must fully support all Phase 1 items. This phase drives vendor selection and the sub-$70/month budget ceiling. |
| **2** | Enhancement | 6–12 months post-launch | Plugin additions and integrations that improve the site after it is running. The vendor must offer a performance upgrade path for the same site without requiring a platform migration. |
| **3** | Future | No committed date | Aspirational, pending external decisions (IT, OPC, Learning Technologies), or dependent on usage patterns. Not a factor in vendor selection. |

---

## Requirements Map

### §3 — Hosting & Infrastructure

All hosting and infrastructure requirements land in Phase 1 — they are the vendor evaluation criteria themselves.

| Requirement | Phase | Notes |
|---|---|---|
| Managed WordPress hosting (auto-updates, vendor support) | 1 | Core selection criterion |
| SSL / HTTPS enforcement | 1 | Standard on all managed WP hosts |
| Daily automated backups with point-in-time restore | 1 | Required at launch |
| Staging / dev environment | 1 | Required to test plugin updates before pushing to production |
| PHP 8.1+, MySQL 8.0+ / MariaDB 10.6+ | 1 | Current WordPress requirements |
| SOC 2 Type II or equivalent security certification | 1 | Required by DU IT |
| Uptime SLA ≥ 99.9% | 1 | Required |
| Breach notification SLA — vendor to DU IT within 72 hours | 1 | Market-standard DPA language from managed WP hosts (WP Engine, Kinsta) is "without undue delay," aligned with the GDPR Article 33 standard of 72 hours. Illinois PIPA specifies no fixed number of hours. A 72-hour contractual commitment is market-achievable and gives DU IT adequate headroom for its own notification obligations. |
| SSH / SFTP access for developer | 1 | Required |
| Unrestricted custom plugin and PHP configuration support | 1 | Budget shared hosts often block this; it is a hard disqualifier |
| Server-side page caching with authenticated-user bypass | 1 | Required for mixed anon/auth traffic (forums, events) |
| CDN for static assets | 1 | Included in most managed WP plans; verify at vendor evaluation |
| Page load ≤ 3 s uncached / ≤ 2 s cached | 1 | Validate against chosen plan |
| Image auto-optimization (WebP, compression) | 1 | **Cloudflare Polish** at the Kinsta CDN edge (enabled 2026-07-28, Lossless) — no server-side plugin (Kinsta bans them). |
| Panopto iframe lazy-loading | 1 | Developer implementation task at launch |
| Per-page plugin asset audit (CSS/JS loading only on needed pages) | 1 | Developer task at launch |

---

### §4 — User Roles & Access Control

| Role | Phase | Notes |
|---|---|---|
| CTLE Admin | 1 | |
| Developer Admin | 1 | |
| Faculty (authenticated) | 1 | |
| Public (anonymous) | 1 | |
| Contributor | 2 | Role exists in WP but is not actively used or configured until Phase 2; no Contributors at launch |

---

### §5 — Authentication & SSO

| Requirement | Phase | Notes |
|---|---|---|
| Entra ID (OIDC / SAML) SSO | 1 | Core auth mechanism |
| User auto-provisioning on first SSO/LTI login | 1 | Required |
| Account linking by DU employee identifier (not email) | 1 | Required for record continuity |
| Profile field sync on every login — display name, email | 1 | Needed for forum post display names and email notifications |
| DU employee identifier stored on first login | 1 | Required as the account primary key; needed for Phase 2 Canvas completion record matching |
| Role preservation on login (SSO must not modify WP roles) | 1 | Required |
| 24-hour session lifetime | 1 | Required |
| Administrator access via MyKinsta WP Admin auto-login (no password assigned) | 1 | Supersedes the break-glass recovery account, withdrawn 2026-07-24. Must be verified before any content or users are live. |
| Administrator recovery path via SSH + WP-CLI, held by at least two people | 1 | The fallback if MyKinsta is unavailable. Must be tested before launch, not discovered during an outage. |
| Audit-log alerting on all Administrator logins and role changes; obfuscated login URL | 1 | Carries over from the break-glass design; only the alert trigger changed. Alerting is implemented via the custom `ctle-admin-alerts.php` mu-plugin — WP Activity Log's notifications are Premium-only. |
| Single logout (SLO) | 3 | Preferred but explicitly not required |

**Faculty Profiles — self-view dashboard and avatar:**

| Requirement | Phase | Notes |
|---|---|---|
| Self-view dashboard (achievements, completion records, forum reputation) | 2 | Nothing to show in a WP dashboard until completion records are imported and achievements are configured |
| Avatar (source TBD Phase 2 — was the LTI payload, superseded 2026-07-28 §6); SSO-only placeholder at launch | 2 | Avatar display on forum posts deferred; a generic placeholder suffices at launch |
| Profile persistence on departure (formal policy and CTLE Admin workflow) | 2 | The practice of retaining WP accounts is Phase 1; the formal OPC-aligned policy is Phase 2 pending OPC consultation |

---

### §6 — Canvas LMS Integration

| Requirement | Phase | Notes |
|---|---|---|
| Canvas global-nav CTLE button → Entra SSO-initiation URL (primary launch) | 1 | **Primary as of 2026-07-28.** Faculty launch from the Canvas global-nav button; the click completes Entra SSO (Canvas is on the same Entra tenant) and lands them logged in. No hosting dependency beyond the SSO URL. |
| Button visibility gated on faculty (`declared_user_type=teacher` via SIS `users.csv`) | 1 | Validated 2026-07-28; read client-side from `users/self/logins`. The Entra group is the real access gate; button visibility is UX. |
| ~~LTI 1.3 launch from Canvas (SSO passthrough)~~ | — | **Withdrawn 2026-07-28** — superseded by the nav-link + Entra SSO above; CTLE needs none of LTI Advantage's services. See `REQUIREMENTS.md` §6 / HANDOFF decision 10. |

---

### §7 — Course Catalog & Completion Records

Professional development courses are hosted in Canvas. WordPress hosts only a public-facing course catalog.

| Requirement | Phase | Notes |
|---|---|---|
| Course catalog — public WP pages listing available Canvas courses with title, description, and enrollment link | 1 | Simple pages or a custom post type (see §18 open question A) |
| Canvas completion records imported to WordPress | 2 | Import mechanism TBD (see §18 open question B). Records become the source of truth for WP-side achievements and Interfolio export. |
| Interfolio CSV export from WordPress records | 2 | Manual export once completion records are imported |
| Interfolio API integration (automated push) | 3 | Mechanism TBD (§18 open question #9); CSV covers Phase 1 and Phase 2 |

---

### §8 — Achievements & Engagement

All achievement and engagement features are Phase 2. At launch, course completion recognition and certificates are handled entirely by Canvas.

| Requirement | Phase | Notes |
|---|---|---|
| wpForo reputation levels (points, levels displayed on posts) | 2 | Built into wpForo free; enable and configure alongside other engagement features |
| GamiPress course completion achievements (from imported Canvas records) | 2 | Requires Canvas completion records to be imported first |
| GamiPress cross-platform achievements (spanning completions + events + forums) | 2 | Requires Canvas completion records to be imported first |
| GamiPress points system | 2 | Bundled with GamiPress rollout |
| Open Badges / external credential sharing | 3 | Explicitly deferred (§8, §18 open question #16) |

---

### §9 — Events Calendar & Registration

| Requirement | Phase | Notes |
|---|---|---|
| Full event data model (title, description, presenters, date/time, location, Zoom, series, capacity, Panopto link, pinned flag) | 1 | Core events |
| Event series taxonomy (admin-managed; series landing pages) | 1 | Core events |
| Registration with capacity limits | 1 | Core events |
| .ics attachment in registration confirmation email | 1 | Handled by WP Mail SMTP |
| Zoom link visibility toggle (public / private per-event) | 1 | Core events |
| Panopto recording link display (post-event) | 1 | Core events |
| Event display logic (upcoming / past+recording / past+no recording) | 1 | Core events |
| Pinned event on home page | 1 | Core events |
| Waitlist with automatic promotion when a spot opens | 2 | Manual waitlist management acceptable at launch |
| Post-event attendance tracking by CTLE Admin | 2 | Feeds GamiPress event-attendance achievement triggers |
| Nested / parent-child events (multi-session workshop days) | 2 | Complex data model; not needed for initial event types |
| Microsoft Graph API — direct Outlook calendar write | 3 | Requires IT approval of `Calendars.ReadWrite` scope (§18 open question #2); .ics covers Phase 1 |

---

### §10 — Discussion Forums

| Requirement | Phase | Notes |
|---|---|---|
| Category-based forums (CTLE-managed topics) | 1 | Core forums |
| Course-specific discussion boards (organized by Canvas course topic) | 1 | Core forums; CTLE Admin creates and names these manually |
| Full moderation tools (edit, delete, pin, lock) | 1 | Core forums |
| SSO/LTI display name shown on posts | 1 | Name sync is Phase 1 (part of SSO) |
| Generic avatar placeholder on forum posts | 1 | Full avatar handling is Phase 2 (source TBD — was the LTI payload, dropped 2026-07-28 §6) |
| wpForo reputation level display on posts | 2 | Part of the Phase 2 engagement feature set |
| GamiPress integration for forum activity triggers | 2 | Tied to GamiPress Phase 2 rollout |

---

### §11 — Content Management & Workflow

| Requirement | Phase | Notes |
|---|---|---|
| All content types (pages, blog/news, course catalog pages, events, forums, resources) | 1 | Core CMS |
| CTLE Admin create-and-publish workflow | 1 | Native WordPress |
| Contributor pending-review workflow | 2 | No Contributors at launch; configure when the first Contributor is onboarded |
| Per-assignment Contributor scoping (PublishPress Permissions) | 2 | Plugin not needed until Phase 2 |

---

### §12 — Search

| Requirement | Phase | Notes |
|---|---|---|
| Relevanssi free (replaces WP core search) | 1 | |
| Indexed content: blog, pages, events, course catalog, resources, forum topics | 1 | |
| Access-aware results (anon visitors vs. authenticated faculty) | 1 | |
| English stemming | 1 | Relevanssi free |
| Relevance ranking (title matches weighted above body) | 1 | Relevanssi free |
| Content-type filters on results page | 1 | |
| Zero-results fallback with helpful links | 1 | |
| Search query logging (including zero-result queries) | 1 | Low effort; enable at launch |
| PDF full-text indexing | 3 | Relevanssi Premium feature; upgrade triggers defined in §12 |
| Live-as-you-type search | 3 | Explicitly out of scope for Phase 1 |
| Relevanssi Premium upgrade | 3 | Upgrade triggers defined in §12; no committed date |

---

### §13 — Notifications & Microsoft 365 Integration

| Requirement | Phase | Notes |
|---|---|---|
| Event registration confirmation email with .ics attachment | 1 | |
| Event reminder 24 hours before | 1 | |
| Waitlist promotion notification | 1 | Sent manually by CTLE Admin at Phase 1 |
| Forum reply notification to thread subscribers | 1 | |
| Content submitted for review — Admin notification | 2 | Relevant only when Contributor workflow is active |
| WP Mail SMTP via DU Microsoft 365 shared application mailbox | 1 | IT provisioning required before launch (§18 open question #15) |
| Microsoft Graph API — direct Outlook calendar write | 3 | Tied to IT decision on `Calendars.ReadWrite` scope; .ics covers Phase 1 |

---

### §14 — Privacy & Data Handling

| Requirement | Phase | Notes |
|---|---|---|
| Published privacy policy page (pre-launch) | 1 | Required before any users are onboarded |
| Forum first-visit acknowledgment modal | 1 | Required before forums open |
| Forum posting-time privacy reminder | 1 | Minor template customization |
| Forum footer link to privacy policy on every forum page | 1 | Minor template customization |
| WordPress built-in Export Personal Data tool | 1 | No additional plugin; enable at launch |
| WordPress built-in Erase Personal Data tool | 1 | No additional plugin; enable at launch |
| Moderation audit trail (WP Activity Log) | 1 | Logging only; Administrator login and role-change **alerting** is via the custom `ctle-admin-alerts.php` mu-plugin (WP Activity Log notifications are Premium). |
| Data retention and departure-handling policy (OPC consultation) | 2 | Needs OPC input (§18 open question #11) before completion records are imported in Phase 2 |
| Panopto identified-user tracking review | 3 | Needs Learning Technologies input (§18 open question #14) |

---

### §15 — Accessibility

All accessibility requirements (WCAG 2.1 Level AA, accessible theme, accessible plugins, pre-launch audit) are Phase 1. Non-negotiable launch gate.

---

### §16 — Home Page

All home page requirements (upcoming events, pinned event, course highlights, news, quick links) are Phase 1.

---

### §17 — Plugin Stack

| Plugin | Phase | Notes |
|---|---|---|
| SSO (miniOrange SAML / OpenID Connect Generic) | 1 | |
| WP Activity Log | 1 | Audit logging (logins, role changes, moderation). Alerting is separate — see custom mu-plugins below. |
| Custom must-use plugins (`mu-plugins/`) | 1 | `ctle-admin-alerts.php` (admin-login + role-change email alerts, free core hooks) and `ctle-hardening.php` (XML-RPC off, `X-Pingback` removed). Source version-controlled in the repo. |
| WPS Hide Login | 1 | |
| The Events Calendar (Pro) + Event Tickets | 1 | |
| wpForo (free core) | 1 | Forums; reputation features enabled in Phase 2 |
| ~~LTI Tool (ceLTIc) + ceLTIc LTI Library~~ | — | **Not required as of 2026-07-28** — LTI superseded by the Canvas nav-link + Entra SSO (§6). Installed then deactivated on Live (kept for optionality). |
| WP Mail SMTP | 1 | Via DU Microsoft 365 shared mailbox |
| Page builder (Beaver Builder or Gutenberg blocks) | 1 | |
| Relevanssi (free) | 1 | |
| ~~Image optimization plugin~~ → **Cloudflare Polish** (CDN edge) | 1 | No plugin — Polish enabled 2026-07-28 (Lossless). Kinsta bans server-side image-optimization plugins. |
| Forum privacy consent (custom dev or plugin) | 1 | First-visit acknowledgment + posting reminder |
| GamiPress (free core + add-ons) | 2 | Requires Canvas completion records to be imported first |
| PublishPress Permissions | 2 | Not needed until Contributors are onboarded |
| WordPress LMS plugin | — | **Not required at any phase.** Professional development courses remain in Canvas. |

---

## Hosting Criteria Summary

The following criteria must be confirmed with any prospective vendor before contracting. All are Phase 1 requirements.

| Criterion | Requirement |
|---|---|
| Hosting type | Managed WordPress; no restrictions on plugins or PHP configuration |
| Security certification | SOC 2 Type II or equivalent |
| Uptime SLA | ≥ 99.9% |
| Breach notification | Contractual commitment to notify DU IT **within 72 hours** of confirming a security incident affecting CTLE data |
| Backups | Daily automated; point-in-time restore available |
| Staging environment | Included (not an add-on) |
| Developer access | SSH and SFTP |
| Caching | Server-side page cache with authenticated-user bypass |
| CDN | Included |
| Scalability model | Upgrade to a higher-resource plan for the **same single site**; not adding additional sites |
| Budget | ≤ $70/month at Phase 1 |

---

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 0.2.0 | 2026-07-24 | sendres | §5: replaced the break-glass recovery account with MyKinsta WP Admin auto-login and an SSH/WP-CLI recovery path; audit-log alerting retargeted to all Administrator logins and role changes. §14, §17: removed the Two Factor / WP 2FA plugin, no longer required. See `REQUIREMENTS.md` §5 for the full rationale. |
| 0.2.1 | 2026-07-24 | sendres | Corrected the LTI plugin name in the plugin summary: WordPress is the LTI **tool** launched from Canvas, so the software is the **LTI Tool** plugin (ceLTIc project) with its **ceLTIc LTI Library** dependency — not "LTI Platform for WordPress." Matches `IT_REQUESTS.md` Request 3. |
| 0.2.2 | 2026-07-24 | sendres | Plugin summary: flagged the similarly named ceLTIc **LTI Platform** plugin as the wrong-direction plugin, not to be confused with **LTI Tool**. |
| 0.2.3 | 2026-07-28 | sendres | §6 reversed: Canvas global-nav link + Entra SSO is now the **primary** launch; **LTI 1.3 withdrawn** (LTI Tool + ceLTIc row marked not-required; plugins deactivated on Live). Button visibility gated on `declared_user_type` via SIS `users.csv` (validated). Phase-2 avatar-from-LTI references superseded (source to be re-chosen in Phase 2). See REQUIREMENTS.md §6 / HANDOFF decision 10. |
| 0.2.4 | 2026-07-28 | sendres | Cross-doc audit sync to match what was built: §3/§17 image optimization → **Cloudflare Polish** (no server-side plugin — Kinsta bans them); §5/§10/§17 alerting attributed to the custom `ctle-admin-alerts.php` mu-plugin (WP Activity Log = logging only; its notifications are Premium); added a custom-mu-plugins row (`ctle-admin-alerts` + `ctle-hardening`) to the §17 stack. |

*This document reflects finalized phase assignments. See `REQUIREMENTS.md` for full requirement details and open questions.*

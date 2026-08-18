# CTLE WordPress Platform — Technical Requirements

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Timeline & Milestones](#2-timeline--milestones)
3. [Hosting & Infrastructure](#3-hosting--infrastructure)
4. [User Roles & Access Control](#4-user-roles--access-control)
5. [Authentication & Single Sign-On](#5-authentication--single-sign-on)
6. [Canvas LMS Integration](#6-canvas-lms-integration)
7. [Course Catalog & Completion Records](#7-course-catalog--completion-records)
8. [Achievements & Engagement](#8-achievements--engagement)
9. [Events Calendar & Registration](#9-events-calendar--registration)
10. [Discussion Forums](#10-discussion-forums)
11. [Content Management & Workflow](#11-content-management--workflow)
12. [Search](#12-search)
13. [Notifications & Microsoft 365 Integration](#13-notifications--microsoft-365-integration)
14. [Privacy & Data Handling](#14-privacy--data-handling)
15. [Accessibility](#15-accessibility)
16. [Home Page Requirements](#16-home-page-requirements)
17. [Recommended Plugin Stack](#17-recommended-plugin-stack)
18. [Open Questions & Future Considerations](#18-open-questions--future-considerations)
19. [Changelog](#19-changelog)

---

## 1. Project Overview

The Center for Teaching & Learning Excellence (CTLE) at Dominican University (DU) is building a standalone, publicly accessible WordPress website for faculty professional development. The site provides an events calendar with registration, faculty discussion forums, a public course catalog, and downloadable resource collections. Professional development courses themselves are hosted in Canvas and are not managed within WordPress; the CTLE WordPress site links faculty to those Canvas courses and, in Phase 2, imports completion records for achievement tracking and Interfolio export.

This project is motivated by the need for the CTLE to manage its web presence in its entirety without depending on Information Technology (IT), Learning Technologies, the Office of Marketing and Communications, or other stakeholders.

- **Audience:** The primary audience is DU faculty — full-time and adjunct. Most content is publicly visible to support faculty recruitment and promote effective teaching across higher education.
- **Public vs. Protected Content:** CTLE resources are public with selected exceptions. Discussion forums and certain event features (event registration and Add-to-Calendar functionality) require DU authentication. Zoom links default to private (DU sign-in required) but can be set to public on a per-event basis by CTLE Admin (see §9). The course catalog is public; enrollment in Canvas courses requires Canvas access.
- **URL:** Subdomain of university website (e.g., `ctle.dom.edu`).

---

## 2. Timeline & Milestones

| Milestone | Target Date |
|---|---|
| Requirements defined & hosting vendor selected | February 2026 |
| WordPress platform up and running | March 2026 |
| Content development & migration from Canvas | March–July 2026 |
| User acceptance testing & soft launch | July 2026 |
| Official launch | August 2026 |

---

## 3. Hosting & Infrastructure

| Requirement | Detail |
|---|---|
| **Hosting Type** | Managed WordPress hosting (third-party vendor) |
| **Budget** | ≤ $70/month at Phase 1 launch |
| **IT involvement** | IT will vet the vendor for security compliance, configure the `ctle.dom.edu` subdomain, and set up Entra SSO. (Canvas LTI was dropped 2026-07-28 — see §6; DU LT retargets the Canvas nav-link instead.) Day-to-day tech support handled by the hosting vendor. |
| **SSL** | Required (HTTPS enforced) |
| **Backups** | Daily automated backups with point-in-time restore |
| **Staging environment** | Required — a staging/dev copy for testing updates before production |
| **PHP version** | 8.1+ (current WordPress recommendation) |
| **Database** | MySQL 8.0+ or MariaDB 10.6+ (current WordPress requirements) |

### Hosting Vendor Evaluation Criteria

- Managed WordPress specialization (auto-updates, caching, content delivery network)
- SOC 2 Type II or equivalent security certification (required by DU IT)
- Uptime SLA ≥ 99.9%
- Breach notification SLA: contractual commitment to notify DU IT within 72 hours of any confirmed or suspected security incident affecting CTLE data (see Breach Notification under §14)
- Built-in staging environment (required, not an add-on)
- SSH/SFTP access for the developer
- Support for custom plugins and PHP configurations (many budget hosts restrict this and are disqualified)
- Scalable upgrade path for a single site — more CPU/RAM/storage on the existing plan, not adding additional low-performance sites

### Performance & Caching

| Requirement | Detail |
|---|---|
| **Page load target (uncached)** | Under 3 seconds for a full page load on a standard broadband connection. Applies to the home page, course catalog, event calendar, and other content-heavy pages. |
| **Page load target (cached)** | Under 2 seconds for subsequent page loads with browser caching and server-side page caching active. |
| **Server-side page caching** | Provided by the managed hosting vendor. Static and public pages are served from cache. Logged-in (authenticated) users bypass page cache so that personalized content (forum access, event registration state) renders correctly. The developer must verify that forum access controls and event registration state function correctly when page caching is active for anonymous visitors and bypassed for authenticated users. |
| **CDN** | Provided by the managed hosting vendor (static assets served from edge servers). |
| **Image optimization** | All uploaded images are automatically compressed and converted to modern formats (e.g., WebP). This is handled by **Cloudflare Polish** at the Kinsta CDN edge (enabled 2026-07-28, Lossless mode) rather than a server-side plugin (Kinsta bans those; see §17), so CTLE staff do not need to manually resize or compress images before uploading. |
| **Lazy loading of Panopto embeds** | Panopto video embeds on past-event pages must be lazy-loaded — the embed iframe and player script should not load until the user scrolls the video into view. This prevents external Panopto resources from blocking initial page render. |
| **Front-end asset management** | The developer should audit CSS and JavaScript loading across all plugins to ensure that plugin assets are loaded only on pages where they are actually used. This is particularly important given the number of plugins in the stack (SSO, events, forums, search, page builder, activity log). |

---

## 4. User Roles & Access Control

| Role | Phase | Capabilities |
|---|---|---|
| **CTLE Admin** | 1 | Full site administration: create/edit/publish all content, manage the course catalog, manage events, moderate forums, manage faculty users. Phase 2 additions: configure achievements and engagement settings, approve contributed content. |
| **Developer Admin** | 1 | WordPress admin access for SSO configuration, plugin management, security settings, and hosting-level operations. Also includes content authoring responsibilities. |
| **Faculty (authenticated)** | 1 | View protected content, browse the course catalog (enrollment occurs in Canvas), participate in forums, RSVP/register for events. Phase 2 addition: earn achievements. |
| **Public (anonymous)** | 1 | View public pages, event listings (with restricted fields), blog posts, course catalog, and resource descriptions. Cannot post in forums or register for events. |
| **Contributor** | 2 | A faculty member (or other subject-matter expert) invited by a CTLE Admin to author a specific page. Contributor status is granted manually per assignment — a CTLE Admin initializes the target page in WordPress, then assigns one or more contributors to it. Contributors can edit only the content they've been explicitly assigned to and cannot see other contributors' unpublished work unless co-assigned. All contributed content enters a pending review state and must be approved by a CTLE Admin before publication. |

---

## 5. Authentication & Single Sign-On

| Requirement | Detail |
|---|---|
| **Identity provider** | Microsoft Entra ID (Azure AD) via OIDC/SAML |
| **Scope** | DU IT gates the Entra app on a group refreshed from the SIS current-faculty list (assignment required). CTLE admins, director, and developer reach WordPress via the hosting provider's auto-login (see *Administrator access & recovery*), not this group, so the faculty group is the entire SSO scope. *(Option 1, chosen 2026-07-27; Entra ID P1 confirmed for group-based assignment.)* |
| **Plugin** | A WordPress SSO plugin compatible with Entra ID (e.g., miniOrange SAML/OIDC, OAuth Single Sign On). |
| **Primary access method** | Faculty perform all regular operation via Entra SSO or LTI launch from Canvas. CTLE Admin and Developer Admin access WordPress via the hosting provider's WP Admin auto-login (see *Administrator access & recovery*), not the faculty SSO group. No password-based WordPress login is used in normal operation. |
| **User provisioning** | On first successful SSO or LTI login, a WordPress user account is auto-created with the Faculty role. |
| **Default role** | All users provisioned via Entra SSO or LTI launch receive the Faculty role by default. Elevation to Contributor (Phase 2), CTLE Admin, or Developer Admin is performed manually within WordPress and is not driven by Entra claims or Canvas context. |
| **Account linking** | The SSO and LTI plugins must be configured to link authenticating users to existing WordPress accounts by DU employee identifier — the stable, claim-provided ID from Entra and Canvas (see §6), stored in the WordPress user-meta field `sis_user_id` — not by email. This protects continuity of completion records and forum history across name or email changes. The small number of local admin accounts created at initial setup (see Administrator access & recovery below) are typically also faculty, so they arrive via MyKinsta auto-login, SSO, *and* LTI; to keep each person to a single account, `sis_user_id` is stamped onto their admin account (matching the person's `employeeId`) **before their first SSO login**, with email-matching as the fallback if a stamp is missing. Thereafter the employee identifier is the primary key. |
| **Profile field sync on login** | On every successful SSO or LTI login, WordPress overwrites the user's display name and email from the incoming Entra claim or LTI launch payload. This ensures that name and email changes propagate automatically without manual intervention. Avatar sync from the LTI launch payload is Phase 2 (see Faculty Profiles below). |
| **Role preservation on login** | SSO/LTI login must never modify a user's WordPress role. Roles are assigned on first login (default: Faculty) and thereafter are managed exclusively within WordPress by CTLE Admin or Developer Admin. Re-sync of profile fields on login must not cascade into role changes. |
| **Session management** | WordPress session lifetime is 24 hours. After session expiry, re-authentication via SSO or LTI is required. This short lifetime is the primary mechanism for revoking access to departed users: once DU IT disables a user's Entra account and Canvas enrollment, the user's next WP re-authentication will fail, typically within 24 hours of the external revocation. Single logout (SLO) preferred but not required. |
| **Administrator access & recovery** | Privileged WordPress access is obtained through the hosting provider's WP Admin auto-login (MyKinsta → Sites → [site] → Info → Log in to WP Admin), which provisions a WordPress Administrator account bound to the operator's MyKinsta identity with **no WordPress password assigned**. No password-authenticated privileged account exists on the site. This mechanism is also used for the one-time elevation of the first CTLE Admin and Developer Admin from their SSO-default Faculty roles. |
| **Recovery path** | If MyKinsta is unavailable — dashboard outage, account suspension, or billing lapse — administrator access is restored via SSH and WP-CLI (`wp user create`). This path must be verified working before launch and must be held by **at least two people**, so recovery never depends on one person's device. (Kinsta allows only one SSH user per environment; "two people" is satisfied by two MyKinsta company members each authorizing their own key — Kinsta's additional SFTP users have no shell/WP-CLI and are not a recovery path.) Because MyKinsta account standing gates administrator access, the hosting billing contact and annual renewal are themselves availability controls. |
| **Administrator access protection** | Protection derives from: (1) MyKinsta account security, including two-factor authentication on all MyKinsta company users; (2) an obfuscated login URL (the default `wp-login.php` path is changed) as a layer against automated scanning; (3) real-time audit-log alerting — implemented as a custom must-use plugin (`mu-plugins/ctle-admin-alerts.php`; WP Activity Log's own email notifications are Premium-only) that emails all CTLE Admins on any successful login by an Administrator-role user and on any role change. Because Company Owner two-factor codes are delivered to the shared `ctle@dom.edu` mailbox, **access to that mailbox is itself a security control** and must be kept minimal and documented. |
| **Rationale — supersedes the break-glass design** | An earlier version of this requirement specified a break-glass local administrator account with a 20-character vaulted password and TOTP, held by DU IT. **That design was withdrawn on 2026-07-24.** Auto-login provides equivalent guaranteed access while eliminating the password entirely — removing a permanent, high-value, password-authenticated credential rather than investing in protecting one. The net result is that no privileged account on the site has a password: faculty authenticate through Entra (which enforces DU's own MFA) and administrators through MyKinsta (which enforces MyKinsta 2FA). A WordPress 2FA plugin is consequently no longer required, as no local password login remains for it to protect. |

> ### ⚠ Implementation deviation — *Account linking*, recorded 2026-08-14
>
> **The Account linking row above cannot be implemented as written, and what was built on 2026-08-14 differs from it.** This note records the deviation rather than quietly amending an agreed requirement; the row itself is left as the stakeholders approved it.
>
> **What the requirement specifies:** link authenticating users to existing accounts by DU employee identifier stored in `sis_user_id`, *"not by email"*, with email-matching only as a fallback where a stamp is missing.
>
> **Why it can't be done:** the selected plugin — OpenID Connect Generic 3.11.3 — has no mechanism for matching an arbitrary claim against an arbitrary user-meta key. Verified by reading its source on Live: `get_subject_identity()` returns the `sub` claim and nothing else, users are found by matching `sub` against the plugin's own `openid-connect-generic-subject-identity` meta, and the only fallback is `email_exists()`. The plugin's "Identity Key" setting, which reads as though it selects the matching key, only chooses which claim becomes a *new* account's username. **No configuration produces the specified behaviour.**
>
> **What was built instead:** first sign-in matches on the email claim; the plugin then records the `sub` value on the account, and every subsequent sign-in matches on that.
>
> **Whether the requirement's intent survives — mostly yes.** The stated purpose is continuity of completion records and forum history *"across name or email changes"*. Entra's `sub` is immutable for the lifetime of the account-plus-application pairing, so from the second login onward continuity is at least as strong as the employee identifier would have given. **The gap is the first login only**, which depends on the WordPress account's email matching Entra's.
>
> **Consequence: the second IT Responsibility below is now load-bearing rather than hygiene.** If a local admin's WordPress email does not match their Entra email at first sign-in, they get a duplicate account rather than a match, and nothing reports the error. Verify each of the pre-existing accounts before its owner's first sign-in.
>
> **`sis_user_id` is retained and still stamped.** It remains the key for linking Canvas completion records to Interfolio (§7), which is unaffected — it is simply no longer the authentication key.
>
> **Open for CTLE:** whether to accept this deviation, or to fund the alternative (a custom plugin extension, or a different SSO plugin). The recommendation is to accept it — `sub` matching is the more robust mechanism, and the residual risk is confined to a handful of known accounts on their first login.
>
> **⛔ Do not raise this with CTLE in writing.** A communications hold has been in force since 2026-08-18 (`PLAN.md`); this belongs in the face-to-face, with working single sign-on as the evidence. Raising a specification deviation by email ahead of that conversation is the worst available order.
>
> **The first-login exposure is closed for all three known accounts.** Live holds `pdriver@dom.edu` (ID 2) and `sendres@dom.edu` (ID 3), both matching their Entra addresses, and Amanda's account is created with `anorris@dom.edu` before her first sign-in rather than left to be created by it. Verified 2026-08-18.
>
> **Also verified 2026-08-18: MyKinsta auto-login matches an existing account by email**, so a pre-created account absorbs both provisioning paths rather than being duplicated by the second one. This was established by test on the Staging environment — an account was deleted and recreated by hand with the same address, and auto-login adopted it. It matters because the multi-path reconciliation described in the row above assumed this and had never confirmed it: MyKinsta mints a *fresh random username per environment* (the same person is `pdriveru8gf` on Live and `pdriverdebl` on Staging), so username is not an identifier, and its provisioning is server-side with no WordPress-visible hook to read.

> ### ⚠ Implementation deviation — *Administrator access protection*, item (2), recorded 2026-08-18
>
> **The obfuscated login URL has been removed, and the requirement row above is left as the stakeholders approved it.** WPS Hide Login was deactivated and deleted from Live on 2026-08-18. Protections (1) MyKinsta account security and (3) audit-log alerting are unaffected and remain in force.
>
> **It was removed because it was subtracting security and breaking authentication, not adding a layer.**
>
> **First, it removed the host's brute-force protection.** Kinsta's edge ban watches `/wp-login.php` and only that. Measured directly: `POST /wp-login.php` → 403 at the edge; `POST` to the custom path → 200, processed by WordPress. Moving the login form out from under the host's awareness of it forfeited the ban entirely, so item (2) was costing more protection than it provided.
>
> **Second, it broke single sign-on outright.** Kinsta excludes `/wp-login.php` and `/wp-admin/` from its page cache; an arbitrary slug is an ordinary public page and was cached with `s-maxage=86400`. The sign-in link carries a single-use `state` nonce with a 180-second lifetime, so the edge froze one nonce into the cached response and served it to every visitor for a day. Sign-in failed with `invalid-state` from fresh private sessions — indistinguishable from a genuine misconfiguration. Measured: identical `state` across independent requests with `x-kinsta-cache: HIT`, versus a distinct `state` per request on a cache-busted URL. **A redirect would not have escaped this**: 302 responses on that path were also served from cache.
>
> **What replaces it.** The OIDC plugin now runs in `auto` login mode, so `/wp-login.php` builds a fresh authorization request server-side and redirects straight to Entra. Verified 2026-08-18: two consecutive requests return `HTTP 302` to `login.microsoftonline.com` with **different `state` values** and `x-kinsta-cache: BYPASS`. **No WordPress login form is presented to any user**, which is the outcome item (2) was reaching for — and it is achieved by removing the form from the journey rather than by hiding its address. The Canvas global-navigation button targets the same URL.
>
> **The residual risk is small and was already accepted elsewhere in this document.** No password-authenticated account exists on this site — `mu-plugins/ctle-hardening.php` unhooks core's authenticators — so `/wp-login.php` being discoverable exposes no form to guess against. Automated scanners reaching it are met by a redirect to Entra and, now, by Kinsta's edge ban.
>
> **Open for DU IT:** item (2) is named in the §*IT Responsibilities* sign-off request as part of the agreed substitute for broader 2FA or IP-allowlist requirements. **That sign-off request must be re-stated without it**, describing the protection model as MyKinsta 2FA + audit-log alerting + Entra-enforced MFA on the only interactive path. This is a DU IT conversation, reached by ticket, and is **not** covered by the communications hold.
>
> **One cleanup outstanding:** the orphaned `whl_page` option still holds the old slug in the database. Harmless, and no longer confidential, but it should be deleted so a future reader does not mistake it for live configuration.

### IT Responsibilities

- Register the WordPress site as an application in Entra ID.
- Configure claims to pass: display name, email, and a DU employee identifier (e.g., `employeeId` or `netID`) suitable for matching Canvas completion records stored in WordPress to Interfolio entries (see §7).
- Confirm that the Entra email claim matches the email addresses used when local CTLE Admin / Developer Admin accounts are created, to ensure clean email-based account linking on first SSO login.
- Gate the app on an Entra group refreshed from the SIS current-faculty list (assignment required). CTLE admins/director/developer access WordPress via the hosting console, not this group. *(Option 1, 2026-07-27.)*
- ~~Hold the break-glass recovery credential (password + TOTP seed) in IT's credential vault. Rotate the password after any use of the account.~~ **Withdrawn 2026-07-24** — there is no longer a credential to vault. See "Rationale — supersedes the break-glass design" above.
- Advise on constraining access to the shared `ctle@dom.edu` mailbox. That mailbox receives the MyKinsta two-factor codes that gate WordPress Administrator access, so its access list functions as a security control.
- Confirm IT security sign-off on the administrator access protection model as the agreed substitute for broader 2FA or IP-allowlist requirements on privileged access. **Restate this without the obfuscated login URL, which was removed on 2026-08-18** — see the deviation note in §5. The model to sign off is: MyKinsta 2FA on all company users, audit-log alerting on every Administrator login and role change, Entra-enforced MFA on the only interactive sign-in path, and no password-authenticated account on the site at all.

---

## Faculty Profiles

Each authenticated user has a WordPress account record storing basic identity data. Profiles are not public — there is no browsable member directory, and no faculty member can view another's profile page.

### Phase 1: Account Record

| Field | Source | Visibility | Editable by user |
|---|---|---|---|
| **Display name** | Entra claim / LTI launch payload, refreshed on every login | Shown on forum posts | No |
| **Email** | Entra claim / LTI launch payload, refreshed on every login | Internal only (not displayed to other users) | No |
| **DU employee identifier** (WP user-meta `sis_user_id`) | Entra claim / LTI launch payload, set on first login as the account's primary key | Internal only; used for linking Canvas completion records to Interfolio (see §7) | No |
| **Role** | Assigned in WordPress (default Faculty on first login); never modified by subsequent SSO/LTI login | N/A | No |

### Phase 2: Self-View Dashboard and Extended Profile

In Phase 2, once completion records are imported from Canvas (see §7) and the achievements system is configured (see §8), each user's account is extended with:

| Field | Source | Visibility | Editable by user |
|---|---|---|---|
| **Avatar** | ~~LTI launch claim from Canvas~~ — **source superseded 2026-07-28 (LTI dropped, §6); to be re-chosen in Phase 2** (e.g., Canvas API or a placeholder) | Shown on forum posts and on the user's own dashboard | No |
| **Forum reputation level** | Generated by wpForo from forum activity (posts, replies, likes) | Displayed on forum posts (visible to all forum participants) and on the user's own dashboard | N/A |
| **Cross-platform achievements** | Generated by GamiPress from Canvas course completions imported to WordPress, event attendance, and forum milestones (see §8) | Self-view only | N/A |
| **Completion records** | Imported from Canvas by CTLE Admin | Self-view (own records) + CTLE Admin view (all records); exportable as CSV per §7 | N/A |

**Self-View Dashboard (Phase 2):** Displays the user's earned GamiPress achievements, imported course completion records, and wpForo forum reputation level. Achievements are displayed in reverse chronological order (most recently earned first). Faculty cannot reorder, hide, or delete earned achievements.

**Avatar Handling (Phase 2):**

> **Source superseded 2026-07-28 (decision 10 — LTI dropped).** The bullets below assume an LTI launch payload, which no longer exists. In Phase 2 the avatar source must be re-chosen (e.g., the Canvas API, Gravatar, or a placeholder). Retained as the original design for reference.

- When a user launches the site via LTI from Canvas, the LTI payload includes a URL to the user's Canvas avatar (128×128 pixels). Canvas provides a generic "no picture" icon by default for users who have not uploaded their own photo, so every LTI-launched user has an avatar URL available.
- WordPress stores or references the avatar URL from the LTI launch claim. Implementation choice (direct reference vs. local copy on the WP media library) is left to the developer.
- Users who authenticate only via Entra SSO — principally CTLE Admin and Developer Admin users — will not have a Canvas-provided avatar. For these users, WordPress displays a locally-hosted copy of the Canvas "no picture provided" icon as a default placeholder. Learning Technologies will provide this icon to the developer.

**Forum Display:**
- Phase 1: Forum posts display the author's display name (synced from Entra/Canvas on login). A generic avatar placeholder is shown.
- Phase 2: The author's Canvas avatar is shown alongside their posts, and their wpForo reputation level is displayed.

**Profile Persistence on Departure:**
- When a faculty member leaves DU, DU IT disables their Entra account and Canvas enrollment externally. Their WordPress account is not deleted — it remains to preserve the integrity of forum history and any completion records or audit trail CTLE may need.
- The departed user's next re-authentication attempt (after their 24-hour WP session expires) will fail because SSO/LTI external access is revoked, effectively preventing further login without any WordPress-side action.
- CTLE Admins may optionally terminate a departed user's active WordPress session manually if immediate revocation is required before natural session expiry.

---

## 6. Canvas LMS Integration

### Canvas Global-Nav Link + Entra SSO (Primary — 2026-07-28)

Faculty reach the CTLE site from a **CTLE button in the Canvas global navigation** that links to the site's Entra **SSO-initiation URL**. Because DU's Canvas authenticates through the same Microsoft Entra tenant as the CTLE site, a faculty member already signed into Canvas has a live Entra session, so the click completes SSO without re-prompting and lands them in WordPress authenticated. No LTI plugin, Developer Key, or platform registration is involved.

- **Access control is Entra, not the button.** The CTLE Entra app is gated on the SIS-faculty group (assignment required — §5, Option 1), and that gate applies no matter how the site is reached (button, bookmark, or direct URL). The button's visibility is therefore UX only; an unauthorized click is denied by Entra at sign-in.
- **Button visibility** is driven by the existing Canvas global-nav JavaScript, gated on a per-user signal readable client-side. Validated mechanism (2026-07-28): the nightly SIS `users.csv` import sets `declared_user_type=teacher` for faculty; the global-nav JS reads the current user's `declared_user_type` from `GET /api/v1/users/self/logins` (confirmed readable by non-admin users) and shows the button for `teacher`. Alternative signals (CTLE-course enrollment, `ENV.current_user_roles`) are equally viable; the choice is DU LT's. De-provisioning of the button is cosmetic — Entra remains the real gate.
- **Integration level:** launch/SSO only. The CTLE site does not pass grades or completions back to Canvas (unchanged from the LTI plan).

### Rationale — supersedes the LTI 1.3 launch design

An earlier version of this section specified an **LTI 1.3 launch** (the ceLTIc **LTI Tool** plugin + **ceLTIc LTI Library**), with the navigation link as a fallback. **On 2026-07-28 that was reversed: the navigation link + Entra SSO is now the primary and only launch mechanism, and LTI is withdrawn.** CTLE is a standalone site that requires none of LTI Advantage's services — no grade passback, no per-course roster (Names & Roles), no deep-linking, and no iframe embedding. LTI would have added a Developer Key, platform registration, a JWKS handshake, and a second identity path (LTI `lis_person_sourcedid` vs Entra `employeeId`) to reconcile — all to achieve "launch the site, logged in," which the nav link plus a single Entra identity already deliver. It also removes an entire pre-launch integration workstream and its timeline risk. The LTI Tool + ceLTIc LTI Library plugins were installed, **deactivated 2026-07-28**, and **deleted 2026-07-29** — the "kept installed for optionality" position was reversed on the grounds that a withdrawn integration's code sitting on disk is attack surface with no offsetting benefit, and both plugins are free to reinstall from wordpress.org in about a minute. This supersedes the LTI-launch language throughout the document: every "SSO or LTI" provisioning/identity reference (§5, Faculty Profiles) now resolves through **Entra SSO only**. The **Phase 2 avatar** source, previously the LTI launch payload, will need a different mechanism (e.g., the Canvas API, or a placeholder) — to be resolved in Phase 2.

### Learning Technologies Responsibilities

- Update the existing Canvas global-nav CTLE button to point at the CTLE site's Entra SSO-initiation URL (available once SSO is configured — §5 / Request 1).
- Gate the button's visibility on the faculty population (validated approach: `declared_user_type=teacher` via SIS `users.csv`, read client-side from `users/self/logins`).
- ~~Register the WordPress site as an LTI 1.3 tool in Canvas; provide OIDC/JWKS endpoints, claims, and avatar URL.~~ **Withdrawn 2026-07-28** — no LTI registration is needed. See "Rationale — supersedes the LTI 1.3 launch design" above.

---

## 7. Course Catalog & Completion Records

### Phase 1: Course Catalog

Professional development courses are hosted and managed in Canvas. The CTLE WordPress site provides a public-facing course catalog — informational pages that faculty can browse to discover available courses, with enrollment occurring in Canvas.

| Element | Description |
|---|---|
| **Catalog entries** | Each entry includes: course title, description, thumbnail, and a link to enroll in Canvas. |
| **Visibility** | The catalog is public — no authentication required to browse. |
| **Implementation** | Simple pages or a custom post type (developer decision; see §18 open question A). A custom post type is more maintainable as the catalog grows. |
| **Management** | CTLE Admin creates and maintains catalog entries in WordPress. |

### Phase 2: Completion Records

Canvas completion records are imported to WordPress to enable achievement tracking (see §8) and Interfolio export.

| Element | Description |
|---|---|
| **Import mechanism** | TBD: manual CSV upload by CTLE Admin, or automated Canvas API pull (see §18 open question B). |
| **Record fields** | DU employee identifier, course title, completion date. Additional fields TBD based on Canvas export format. |
| **Interfolio export** | CTLE Admin can export completion records from WordPress as CSV for submission to Interfolio, DU's faculty credentials system. |

### Phase 3: Interfolio API Integration

Automated push of completion records from WordPress to Interfolio via API, replacing manual CSV submission. Mechanism TBD (see §18 open question #9).

---

## 8. Achievements & Engagement

> **All features in this section are Phase 2.** At launch, course completion recognition and certificates are handled entirely by Canvas.

The goal of the achievements system is to drive faculty engagement — encouraging event attendance and forum participation through visible recognition. In Phase 2, when Canvas completion records are imported to WordPress (see §7), course completions also become achievement triggers.

### Engagement Model

| Layer | What it rewards | Plugin | How it's visible |
|---|---|---|---|
| **Forum reputation** | Posting, replying, and receiving likes in discussion forums | wpForo (built-in reputation system) | Reputation level displayed on every forum post and on the self-view dashboard |
| **Course completion recognition** | Canvas course completions imported to WordPress | GamiPress | Achievement display on self-view dashboard |
| **Cross-platform achievements** | Milestones spanning course completions, event attendance, and forum participation | GamiPress | Achievement notifications on completion; achievements on self-view dashboard |

### Forum Reputation (wpForo)

- wpForo's built-in reputation system assigns points and levels based on forum activity (posts, replies, likes received).
- Faculty progress through reputation levels (e.g., Newbie → Member → Expert) automatically as they participate.
- Reputation level and title are displayed on every forum post and on the user's self-view dashboard.
- CTLE Admin can customize level names and point thresholds within wpForo settings.

### Course Completion Recognition (GamiPress + Canvas Import)

- When Canvas completion records are imported to WordPress (see §7), GamiPress achievement triggers are configured to fire on completion milestones (e.g., "completed first course," "completed 5 courses").
- PDF certificates are generated and held in Canvas; the WP achievement display shows that a user has completed a course without duplicating Canvas's certificate.
- Developer should verify that GamiPress correctly processes imported completion records as achievement triggers (see §18 open question #1).

### Cross-Platform Achievements (GamiPress)

| Requirement | Detail |
|---|---|
| **Plugin** | GamiPress (free core). |
| **Cost** | Free core with optional paid add-ons. The free version covers all Phase 2 requirements. |
| **Triggers** | Event attendance milestones (e.g., "attended 5 events"), course completion milestones (from imported Canvas records), cross-system achievements (e.g., "completed a course AND posted in a forum AND attended an event"), and custom achievements defined by CTLE Admin. |
| **Integration** | GamiPress has explicit, maintained integrations with wpForo and The Events Calendar. Canvas completion records serve as triggers via the import mechanism (see §7). The developer should verify these integrations are functional with the specific plugin versions selected (see §18 open question #1). |
| **Points system (optional)** | GamiPress supports an accumulated points system across all activities. CTLE Admin can decide whether to enable visible point totals or use points only as internal achievement thresholds. |
| **On-site display** | Earned achievements are displayed on the faculty member's self-view dashboard alongside completion records and forum reputation (see Faculty Profiles under §5). |

### Phase 3: Open Badges

If CTLE later decides to make achievements shareable externally (LinkedIn, email, personal portfolio sites), GamiPress offers an Open Badges add-on that can be enabled without restructuring the existing achievement system. This is deferred to post-Phase 2 evaluation (see §18 open question #16).

---

## 9. Events Calendar & Registration

### Event Data Model

| Field | Type | Phase | Notes |
|---|---|---|---|
| **Title** | Text | 1 | Required |
| **Abstract / Description** | Rich text | 1 | Required |
| **Presenter(s)** | Repeater field | 1 | Each entry: Name, Title/Role. Multiple presenters per event. |
| **Date** | Date | 1 | Required |
| **Time** | Time (start – end) | 1 | Required |
| **Location** | Text | 1 | Physical location for in-person events |
| **Series** | Taxonomy (select) | 1 | Admin-defined list (e.g., Conversation Series, Faculty Seminar Series). CTLE creates/manages series names. |
| **Parent Event** | Reference | 2 | For nested events (sessions under a workshop). See Nested Events below. |
| **Zoom Link** | URL | 1 | For webinars/virtual events |
| **Zoom Visibility** | Toggle | 1 | Default: Private (DU sign-in required). CTLE Admin can set to Public on a per-event basis. See §1. |
| **Panopto Recording Link** | URL | 1 | Added post-event when recording is available |
| **Capacity** | Number (nullable) | 1 | Max registrations allowed. Blank/null = unlimited (the default). A positive number enforces a cap; when reached, new registrants are added to a waitlist. |
| **Pinned** | Boolean | 1 | Pin event to the home page (see §16) |

### Registration & RSVP

| Feature | Phase | Description |
|---|---|---|
| **Registration** | 1 | Authenticated (DU) users can register for upcoming events via a "Register" button on the event page. Registration creates a registration record, counts against capacity, and triggers a confirmation email (see §13). |
| **Calendar add (.ics)** | 1 | The registration confirmation email includes an `.ics` attachment the faculty member can open to add the event to their Outlook calendar. |
| **Capacity limits** | 1 | When capacity is reached, new registrants are added to a waitlist. CTLE Admin manages the waitlist manually at Phase 1. |
| **Waitlist auto-promotion** | 2 | Waitlisted registrants are automatically promoted (and notified) when a spot opens. |
| **Attendance tracking** | 2 | CTLE Admin can mark who actually attended post-event. In Phase 2, attendance feeds GamiPress achievement triggers. |
| **Outlook calendar (Graph API)** | 3 | Direct write to the faculty member's Outlook calendar via Microsoft Graph API with `Calendars.ReadWrite` permission. Requires IT approval (see §18 open question #2). .ics covers Phase 1. |

### Event Display Logic

| State | Media Area | Link/Info Area |
|---|---|---|
| **Upcoming event** | Placeholder or event image | Zoom link (respecting visibility toggle) and/or physical location |
| **Past event (recording available)** | Panopto embedded thumbnail player | Link to full Panopto recording |
| **Past event (no recording)** | Event image or placeholder | "Recording not available" |

### Nested / Parent-Child Events (Phase 2)

- A parent event (e.g., "Fall Faculty Workshop Day") can contain multiple child sessions (e.g., "Session 1: Active Learning — 9:00 AM, Room 101").
- Each child session is a full event record (with its own time, location, presenters, Zoom link, recording, etc.).
- The parent event page displays an agenda/schedule of all child sessions.
- A workshop or multi-session event may be treated as its own "series" for organizational purposes.

### Event Series

- Series are a custom taxonomy managed by CTLE Admin (create, rename, archive).
- Examples: *Conversation Series*, *Faculty Seminar Series*, *New Faculty Orientation*, *Workshop Day 2026*.
- Events are filtered/browsable by series on the front end.
- Each series may have its own landing page showing all past and upcoming events in that series.

---

## 10. Discussion Forums

### Structure

| Forum Type | Description |
|---|---|
| **Category-based forums** | General discussion areas organized by topic (e.g., "Teaching with AI," "Assessment Strategies," "New Faculty"). CTLE Admin creates and manages categories. |
| **Course-specific forums** | Discussion boards organized by Canvas course topic, for faculty to continue conversations related to specific courses. CTLE Admin creates and names these forums to match available Canvas courses. |

### Access & Moderation

- All forums are restricted to authenticated (DU) faculty.
- Forum content is not visible to the public.
- CTLE Admin has full moderation capabilities (edit, delete, pin, lock threads).
- Faculty can post new topics and reply to existing threads.
- Phase 1: Forum posts display the author's profile display name (synced from Entra/LTI on login). A generic avatar placeholder is shown. There is no forum-specific pseudonym — forum identity is always the faculty member's institutional identity.
- Phase 2: The author's Canvas avatar is shown alongside posts, and their wpForo reputation level is displayed. Forum participation feeds achievement triggers via wpForo's reputation system and GamiPress cross-platform achievements (see §8).

### Recommended Plugin Approach

- **wpForo** (free core, actively maintained). wpForo uses its own database tables for better performance than WordPress's native `wp_posts` storage, provides modern forum layouts, and built-in moderation tools.
- Must respect the SSO-provisioned user accounts and display the Entra/LTI-synced display name on posts.
- Phase 2: wpForo's reputation system is enabled and configured (CTLE Admin customizes level names and point thresholds). GamiPress integration is configured for cross-platform achievement triggers (e.g., "posted in 10 forum topics").

---

## 11. Content Management & Workflow

### Content Types

| Content Type | Phase | Description |
|---|---|---|
| **Pages** | 1 | Static informational pages (About CTLE, Contact, Resources, etc.) |
| **Blog / News posts** | 1 | CTLE announcements, teaching tips, faculty spotlights |
| **Course catalog entries** | 1 | Public-facing pages listing available Canvas courses with enrollment links (see §7) |
| **Events** | 1 | Custom post type (see §9) |
| **Forum topics** | 1 | Managed via forum plugin (see §10) |
| **Resources** | 1 | Downloadable guides, templates, toolkits (custom post type or media library category) |

### Editorial Workflow

| Author | Phase | Workflow |
|---|---|---|
| **CTLE Admin** | 1 | Create → Publish (immediate) |
| **Contributor** | 2 | Assigned by CTLE Admin to a specific page → Create/Edit within assignment → Submit for Review → CTLE Admin Approves/Rejects → Publish. Contributors cannot create new top-level content on their own initiative. |

Phase 2 notes:
- Per-assignment authoring scope (ensuring contributors see only their own assigned content) requires a permissions plugin (e.g., PublishPress Permissions). See §17.
- CTLE Admin will be notified when a Contributor submits content for review (see §13).

---

## 12. Search

Site-wide search is a first-class feature. Faculty need to reliably find course catalog entries, events, blog posts, resources, and (when authenticated) forum discussions from a single search interface.

### Requirements

| Requirement | Detail |
|---|---|
| **Plugin** | Relevanssi (free version) replaces WordPress's default search with a relevance-ranked index. CTLE may upgrade to Relevanssi Premium in the future if usage patterns warrant (see Upgrade triggers below). |
| **Indexed content** | Blog posts, static pages, events (title, abstract, presenters, series, location), course catalog entries (titles and descriptions), downloadable resources (titles, descriptions, and filenames), and forum topics/replies (with access filtering — see below). |
| **PDF content** | At launch, only PDF filenames and media-library titles/descriptions are indexed. Indexing the full text inside PDF files is a Relevanssi Premium feature and is not required for Phase 1. |
| **Stemming** | English stemming enabled (Relevanssi free handles this). Searches for `assess` should match `assessing`, `assessment`, `assessments`, etc. |
| **Relevance ranking** | Results ordered by relevance, not date. Title matches should weight more heavily than body matches. |

### Access-Aware Results

Search results must respect content visibility rules so that no user sees a result they cannot access:

| Viewer | Sees in results |
|---|---|
| **Anonymous visitor** | Public blog posts, public pages, public event listings (with restricted fields per §9), course catalog entries, public resource descriptions. Not forum threads or protected event fields. |
| **Authenticated faculty** | All of the above, plus forum topics and replies from forums they have access to. |

Relevanssi supports per-user access filtering via standard WordPress capability checks; the developer should verify this works correctly with the chosen forum plugin (§10).

### Search UI

- A search box is present in the site header on every page, visible to both anonymous visitors and authenticated users.
- Submitting a search takes the user to a dedicated results page showing matched items with title, short excerpt, content type label, and a link to the item.
- The results page includes content-type filters allowing the user to narrow results by category: Courses, Events, Blog/News, Resources, Pages, and (for authenticated users) Forums.
- Search is submit-and-show-results, not live-as-you-type. Live search is out of scope for Phase 1.
- When a search returns zero results, the page displays a helpful "no results" message with links to browse the course catalog, event calendar, and resource library, and a contact link for CTLE.

### Search Analytics

- Relevanssi free logs user queries, including zero-result queries, to the WordPress database. The developer should enable this logging at launch.
- Actively reviewing search analytics is not a priority for Phase 1 and no dashboard or reporting surface needs to be built. The logged data will simply accumulate and be available if CTLE later wants to review it.

### Upgrade Triggers to Relevanssi Premium

CTLE should consider upgrading to the paid version if any of the following occur post-launch:

1. Search analytics (or direct faculty feedback) reveal a pattern of zero-result queries that correspond to content that actually exists on the site — particularly content inside PDFs.
2. The CTLE resource library grows to include substantial PDF content where filename-only indexing is insufficient.
3. CTLE adds non-English content (see open question on multilingual support).
4. CTLE wants formal search reporting, "did you mean" suggestions, or search redirects.

The upgrade decision is deferrable and does not affect launch scope.

---

## 13. Notifications & Microsoft 365 Integration

### Email Notifications

| Trigger | Phase | Recipient | Content |
|---|---|---|---|
| Event registration confirmation | 1 | Registrant | Event details + .ics attachment |
| Event reminder (24 hrs before) | 1 | All registrants | Reminder with Zoom link / location |
| Waitlist promotion | 1 | Promoted registrant | "A spot has opened" + event details (managed manually by CTLE Admin at Phase 1) |
| Forum reply | 1 | Thread subscribers | Excerpt of reply + link to thread |
| Content submitted for review | 2 | CTLE Admin | Link to pending content (active when Contributor workflow is in use) |

### Microsoft 365 Integration

| Integration | Phase | Description |
|---|---|---|
| **Outlook Calendar (.ics)** | 1 | Registration confirmation email includes an `.ics` attachment the faculty member can open to add the event to their Outlook calendar. |
| **Outlook Calendar (Graph API)** | 3 | Direct write to the faculty member's Outlook calendar via Microsoft Graph API. Requires IT approval of `Calendars.ReadWrite` scope (see §18 open question #2). |

### IT Responsibilities

- Provision the dedicated `ctle-noreply@dom.edu` shared application mailbox in Microsoft 365 for WordPress to use as the sender for all system-generated email — kept separate from the human `ctle@dom.edu`. Confirm SPF/DKIM/DMARC alignment for `dom.edu` covers it. *(Sender decided 2026-07-27.)*
- Confirm that automated email volume from this mailbox (estimated 50–200 messages per day at peak, with occasional bursts for event reminders) is acceptable under DU's Exchange Online sending limits and acceptable-use policies.
- Send via the Microsoft Graph API (`Mail.Send`), configured in WP Mail SMTP's Microsoft 365 / Outlook mailer — **not** SMTP AUTH, whose basic authentication Microsoft disables by default for existing tenants at the end of December 2026 (see `IT_REQUESTS.md` Request 2).
- Phase 3: If Outlook calendar Graph API integration is approved, this will require an Entra app registration with `Calendars.ReadWrite` delegated permissions (see §18 open question #2).

---

## 14. Privacy & Data Handling

The CTLE site collects personal data about DU faculty: identity (name, email, employee ID), forum activity, event registrations, and search query history. In Phase 2, the site will also hold Canvas completion records imported for Interfolio purposes and GamiPress achievement records. This section establishes the privacy obligations that apply to that data.

### Applicable Regimes

| Regime | Applicability |
|---|---|
| **FERPA** | Not applicable. CTLE training records are employee records, not student education records. Students cannot create accounts on or access protected content in the CTLE site. If this scope ever expands to include students (e.g., graduate TAs, peer mentors), FERPA applicability must be re-evaluated. |
| **Illinois PIPA** | Applicable to breach notification and reasonable security obligations for personal information. |
| **DU OPC (Office of People and Culture) policy** | Applicable to employee records. CTLE will consult OPC on retention, disclosure, correction, and departure-handling policies (see §18). |
| **GDPR** | Not a primary design driver. The site primarily serves U.S. faculty. If an EU data subject (e.g., a visiting scholar) asserts GDPR rights, CTLE Admin must be able to fulfill access, correction, and erasure requests via WordPress's built-in privacy tools. |

### Privacy Policy

| Requirement | Detail |
|---|---|
| **Published privacy policy page** | The CTLE site must have a publicly accessible privacy policy page (linked from the site footer on every page) before launch. |
| **Required content** | The policy must name: (1) what personal data is collected, (2) how it is used, (3) all third-party recipients of that data (hosting provider, Panopto, Interfolio, Microsoft Graph if used, and any analytics service if used), (4) retention periods, (5) the process by which faculty can request access to, correction of, or deletion of their data, and (6) a contact point for privacy concerns. |
| **Authoring** | CTLE drafts the policy, using the WordPress built-in privacy policy template as a starting point. OPC and/or DU legal review the draft before publication. |

### Faculty Data Rights

| Capability | Phase | Detail |
|---|---|---|
| **Data export** | 1 | CTLE Admin can export a specific faculty member's personal data on request, using WordPress's built-in Export Personal Data tool. |
| **Data correction** | 1 | CTLE Admin can correct inaccurate records on request. The exact process is to be defined in consultation with OPC (see §18). |
| **Data erasure** | 1 | CTLE Admin can redact or erase a faculty member's personal data on request, using WordPress's built-in Erase Personal Data tool. Completion records retained for institutional purposes may be anonymized rather than deleted; the exact boundary is to be defined in consultation with OPC (see §18). |
| **Self-view** | 2 | Faculty can view their own completion records, earned achievements, and forum reputation via the self-view dashboard (see Faculty Profiles under §5). |

### Forum Privacy Disclosure

Because forum posts are linked to institutional identity (see §10) and visible to all authenticated DU faculty, users must be clearly informed of the visibility and moderation rules before and during forum use.

| Disclosure mechanism | When it appears |
|---|---|
| **First-visit acknowledgment** | On a user's first visit to any forum page, a modal or inline acknowledgment displays the forum privacy notice and requires the user to click to confirm before proceeding. The acknowledgment reappears whenever the forum privacy policy is materially updated. |
| **Persistent footer link** | Every forum category, topic, and thread page displays a footer link to the full forum privacy policy. |
| **Posting-time reminder** | When a user is composing a post or reply, a short note near the submit button reminds them that forum content is visible to all DU faculty and is moderated by CTLE, with a link to the full policy. |

The exact language of these disclosures is to be drafted by CTLE in consultation with OPC (see §18).

### Moderation Audit Trail

- All CTLE Admin actions affecting forum content (edits, deletions, pinning, locking) are logged using WP Activity Log (see §17).
- The audit trail records the admin identity, timestamp, action, and affected post.
- This protects faculty (transparency about what happens to their posts) and CTLE (defensibility of moderation decisions).

### Third-Party Data Flows

The following third parties will receive CTLE faculty data in the course of normal operation. Each requires a data processing agreement or equivalent arrangement, handled by DU IT at vendor selection:

| Vendor / Service | Data received |
|---|---|
| **Managed WordPress hosting provider** | Full database and file access (all site data) |
| **Microsoft 365 (email)** | Recipient addresses and message content for all system-generated email, sent via a DU shared application mailbox |
| **Panopto** | Video viewing activity, potentially including identified-user analytics (see §18 open question #14) |
| **Interfolio** | Training completion records (Phase 2/3 — mechanism TBD per §18 open question #9) |
| **Microsoft Graph API** (Phase 3, if approved) | Event data and calendar writes |
| **Analytics service** (if used — currently TBD per §18) | Page view and session data |

### Breach Notification

- The managed hosting vendor contract must require the vendor to notify DU IT within 72 hours of any confirmed or suspected security incident affecting CTLE data.
- Market-standard Data Processing Addenda from managed WordPress hosts (including WP Engine and Kinsta) use "without undue delay" language aligned with the GDPR Article 33 standard of 72 hours. Illinois PIPA, the applicable state law, requires notification "in the most expedient time possible and without unreasonable delay" but does not specify a fixed number of hours. A contractual 72-hour commitment satisfies both market realities and DU's regulatory obligations.
- In the event of a breach affecting personal information, DU follows its standard notification procedures under Illinois PIPA and any applicable OPC policies.

---

## 15. Accessibility

| Requirement | Standard |
|---|---|
| **Compliance level** | WCAG 2.1 Level AA or greater |
| **Theme** | The WordPress theme must be accessibility-ready (tested against WCAG criteria). |
| **Plugins** | All plugins (forums, events, and other installed plugins) must produce accessible front-end markup. |
| **Media** | Panopto videos should include captions. The site should support alt text for all images. |
| **Testing** | Accessibility audit recommended before launch (automated + manual testing). |
| **DU branding** | Follow Dominican University's brand guidelines for colors, typography, and logo usage. CTLE and the developer should coordinate with DU Marketing/Communications. |

---

## 16. Home Page Requirements

| Element | Description |
|---|---|
| **Upcoming events** | Display the next several upcoming events (configurable count, e.g., 3–5) with title, date, series, and a registration CTA. |
| **Pinned event** | CTLE Admin can "pin" a specific event to a prominent home-page position — either to drive registrations for an upcoming event or to highlight the recording of a signature past event. |
| **Course highlights** | Optional: featured or new courses from the course catalog. |
| **Announcements / News** | Latest blog post or CTLE announcement. |
| **Quick links** | Links to the course catalog, event calendar, forums, and resources. |

---

## 17. Recommended Plugin Stack

The following is a starting-point recommendation for the developer to evaluate. Final selections should consider compatibility, licensing costs, and long-term maintenance.

| Function | Phase | Recommended Plugin(s) | Notes |
|---|---|---|---|
| **SSO / Authentication** | 1 | miniOrange SAML SSO, or OpenID Connect Generic | Must support Entra ID; must support account linking by employee identifier (not email) and preserve existing roles on re-login (see §5) |
| **Audit logging** | 1 | WP Activity Log | Logs logins, role changes, content edits, plugin activations. **Alerting is separate** (below) — WP Activity Log's email notifications are Premium-only. See §5. |
| **Admin-login / role-change alerting** | 1 | Custom must-use plugin (`mu-plugins/ctle-admin-alerts.php`) | Emails CTLE Admins on any Administrator login and any role change, via free core hooks. Delivery depends on WP Mail SMTP (§13). See §5. |
| **Login URL obfuscation** | 1 | WPS Hide Login (or equivalent) | Changes the default `wp-login.php` path. See §5. |
| **Hardening (XML-RPC off)** | 1 | Custom must-use plugin (`mu-plugins/ctle-hardening.php`) | Disables XML-RPC (defense-in-depth; Kinsta also blocks it at Nginx) and removes the `X-Pingback` header. See §6. |
| **Events** | 1 | The Events Calendar (Pro) + Event Tickets | Custom post type with series taxonomy, registration, capacity |
| **Forums** | 1 | wpForo (free core) | Actively maintained; uses own database tables for better performance. Replaces bbPress, which has been in maintenance mode since 2020. Reputation features enabled in Phase 2. |
| ~~**LTI**~~ | — | ~~LTI Tool (ceLTIc) + ceLTIc LTI Library~~ | **Withdrawn 2026-07-28 (§6):** LTI superseded by the Canvas nav-link + Entra SSO. Plugins were installed, deactivated 07-28, and **deleted 2026-07-29** — "kept for optionality" reversed; both are free to reinstall if ever needed. |
| **Email** | 1 | WP Mail SMTP | Sends via the Microsoft Graph API (`Mail.Send`) from the `ctle-noreply@dom.edu` shared mailbox (see §13); not SMTP AUTH. |
| **Page builder** | 1 | Beaver Builder or Gutenberg blocks | Developer preference; must produce WCAG 2.1 Level AA compliant markup (see §15). Elementor is not recommended due to documented accessibility issues with keyboard navigation, focus management, and ARIA markup in its generated output. |
| **Search** | 1 | Relevanssi (free) | Replaces WP core search with relevance-ranked indexing of custom post types and custom fields; access-aware results. See §12. Upgrade to Relevanssi Premium deferrable post-launch. |
| **Forum privacy disclosure** | 1 | Custom development or a terms/consent plugin (e.g., Complianz, CookieYes) | Implements first-visit acknowledgment modal (with re-display on policy change), posting-time reminder, and persistent footer link per §14. Footer link and posting-time note are minor template customizations; the first-visit acknowledgment with tracked consent and re-display logic is more involved. Developer to evaluate build-vs-buy. |
| **Image optimization** | 1 | **Cloudflare Polish** (Kinsta CDN edge) — no plugin | Enabled 2026-07-28 (Lossless). Kinsta bans server-side image-optimization plugins; Polish covers WebP conversion + compression at the edge. If bulk pre-upload optimization is ever needed, use an API-based plugin only. See §3 and `kinsta_onboarding.md` §11. |
| **Calendar integration** | 1 | .ics export via WP Mail SMTP | .ics attachment in event registration confirmation emails. Microsoft Graph API for direct calendar write is Phase 3 (see §18 open question #2). |
| **Cross-platform achievements** | 2 | GamiPress (free core) | Points/achievements/ranks spanning Canvas completions (imported to WP), events, and forums. Integrations with wpForo and The Events Calendar. Requires Canvas completion records to be imported first (see §7). See §8. |
| **Access control / per-post permissions** | 2 | PublishPress Permissions (or equivalent) | Needed to scope Contributor authoring to specific assigned pages (see §4, §11). |
| **WordPress LMS plugin** | — | Not required | Professional development courses remain in Canvas. No WordPress LMS plugin is needed at any phase. |

---

## 18. Open Questions & Future Considerations

| # | Question / Consideration | Status |
|---|---|---|
| A | **Course catalog implementation:** Is a lightweight custom post type (with consistent fields: title, description, thumbnail, Canvas enrollment URL) preferable to static pages for the course catalog? A CPT is more maintainable as the catalog grows but adds a small amount of initial setup. | Open — developer recommendation |
| B | **Canvas completion records import mechanism:** For Phase 2, should completion records be imported via (1) manual CSV upload by CTLE Admin, or (2) automated Canvas API pull on a schedule? Option 1 has no external dependencies; Option 2 requires Canvas API credentials and developer work. | Phase 2 — needs CTLE / IT input |
| 1 | **GamiPress achievement trigger verification:** Developer to verify that GamiPress correctly processes imported Canvas completion records as achievement triggers, and that the GamiPress–wpForo integration correctly detects forum activity (posts, replies, likes) with the specific wpForo version deployed. See §8. | Phase 2 — verify before enabling GamiPress |
| 2 | **Microsoft Graph API scope:** IT to confirm willingness to grant `Calendars.ReadWrite` for direct Outlook calendar integration. .ics covers Phase 1; Graph API is Phase 3 if approved. | Phase 3 — needs IT input before Phase 3 planning |
| 3 | **Content migration:** Inventory non-course content in the current Canvas-based CTLE site — blog posts, resource documents, event history, and static pages — that should be migrated to WordPress. Professional development courses remain in Canvas and are not migrated. Determine formats (HTML, PDF, video links) and migration effort. | Future work |
| 4 | **Analytics:** Does CTLE need reporting dashboards (event attendance trends, popular content, forum activity)? If so, Google Analytics or a WP analytics plugin. | Phase 2/3 — determine post-launch based on needs |
| 5 | **Multilingual support:** Any need for content in languages other than English? If yes, this would also be a trigger for upgrading to Relevanssi Premium for multilingual stemming (see §12). | To be determined |
| 6 | **Data retention:** Canvas completion records imported to WordPress in Phase 2 will be retained indefinitely by default. Align with DU OPC policy on retention of employee training records, including what happens to records after a faculty member separates from DU. Rolled into the broader OPC consultation (#11). | Phase 2 — needs OPC input before records are imported |
| 7 | **Custom theme vs. commercial theme:** Developer to recommend based on DU brand requirements and budget. | Open |
| 8 | **Disaster recovery:** Confirm RPO/RTO requirements with IT. | Needs IT input |
| 9 | **Interfolio ingestion mechanism:** Canvas completion records will be imported to WordPress in Phase 2 and will be exportable as CSV (see §7). The mechanism for getting records into Interfolio — manual CSV upload, scheduled export, or direct API integration — is a separate decision and does not block Phase 2. **Associate Provost** to confirm how Interfolio ingests records. | Phase 2/3 — needs Associate Provost input |
| 11 | **OPC (Office of People and Culture) consultation:** CTLE to consult OPC on all HR-adjacent aspects of the project, including: (a) retention policy for completion records imported from Canvas; (b) handling of forum posts and other authored content when a faculty member separates from DU; (c) process and authority for correcting inaccurate records; (d) process and authority for handling faculty data access, export, and erasure requests; (e) any other employee-records requirements OPC wishes to impose. Findings feed back into §7, §14, and any other affected sections. | Needs OPC input |
| 12 | **Privacy policy authoring:** CTLE to draft the site's privacy policy page (using WordPress's built-in template as a starting point) before launch. The policy must identify (a) what data is collected, (b) how it is used, (c) all third-party recipients of data, (d) retention periods, (e) the process for faculty data access and correction, and (f) a privacy contact. OPC and/or DU legal review required before publication. See §14. | CTLE task, pre-launch |
| 13 | **Forum privacy disclosure language:** Draft the exact language for the three forum privacy disclosures (first-visit acknowledgment, persistent footer link text, posting-time reminder — see §14). To be drafted by CTLE in consultation with OPC. | Needs CTLE / OPC input |
| 14 | **Panopto tracking review:** Learning Technologies to review whether DU's Panopto instance is configured to collect identified-user viewing analytics, and confirm how that data flow should be disclosed in the site's privacy policy (see §14). | Needs Learning Technologies input |
| 15 | **Microsoft 365 email for WordPress:** IT to confirm: (a) willingness to provision a shared application mailbox (e.g., `ctle@dom.edu`) for automated WordPress email; (b) that estimated volume (50–200 messages/day, occasional bursts for event reminders) is acceptable under Exchange Online sending limits; (c) preferred SMTP relay or Graph API configuration for WP Mail SMTP. See §13. | ✅ Resolved 2026-07-27 — sender is the dedicated `ctle-noreply@dom.edu` mailbox, sending via Graph `Mail.Send` (not SMTP AUTH); volume/limits to confirm via Request 2. |
| 16 | **Open Badges for external sharing:** If CTLE later wants faculty to share achievements externally (LinkedIn, personal portfolios), GamiPress offers an Open Badges add-on. Evaluate post-Phase 2 based on faculty interest. See §8. | Phase 3 consideration |

---

## 19. Changelog

| Version | Date | Author | Changes |
|---|---|---|---|
| 0.1.0 | 2026-02-11 | sendres | Initial version for CTLE and IT review |
| 0.1.1 | 2026-03-09 | sendres | Minor revisions after pdriver and kodell review |
| 0.1.2 | 2026-03-09 | sendres | Add Interfolio export requirement for completed faculty training records |
| 0.1.3 | 2026-03-10 | sendres | Fix document repository link |
| 0.1.4 | 2026-04-13 | sendres | Clarify Contributor role |
| 0.1.5 | 2026-04-13 | sendres | Revise authentication process |
| 0.1.6 | 2026-04-13 | sendres | Update Interfolio requirements; add DU ID to Entra and LTI login requirements |
| 0.1.7 | 2026-04-13 | sendres | Clarify requirements for faculty profiles |
| 0.1.8 | 2026-04-13 | sendres | Remove numbering and ToC entry from Faculty Profiles subsection |
| 0.1.9 | 2026-04-13 | sendres | Add search requirements |
| 0.1.10 | 2026-04-16 | sendres | Add Privacy & Data Handling section |
| 0.1.11 | 2026-04-16 | sendres | Minor copy edits and formatting |
| 0.1.12 | 2026-04-16 | sendres | Audit plugin stack; clarify recovery account password strength is manually enforced |
| 0.1.13 | 2026-04-16 | sendres | Clarify transactional email and IT support requirements |
| 0.1.14 | 2026-04-16 | sendres | Add Performance & Caching subsection |
| 0.1.15 | 2026-04-16 | sendres | Clarify event registration and private/public availability of Zoom links |
| 0.1.16 | 2026-04-16 | sendres | Rework achievements and engagement approach; downgrade Open Badges from requirement to future work |
| 0.1.17 | 2026-04-16 | sendres | Remove Elementor from page builder consideration due to WCAG issues |
| 0.2.0 | 2026-05-16 | sendres | Comprehensive scope refinement: (1) Professional development courses remain in Canvas; WordPress hosts course catalog pages only; no WordPress LMS plugin required at any phase. §7 renamed "Course Catalog & Completion Records." (2) Canvas completion records imported to WordPress in Phase 2 for achievement tracking and Interfolio export. (3) All achievements and engagement features (GamiPress, wpForo reputation) deferred to Phase 2. (4) Faculty self-view dashboard and avatar handling deferred to Phase 2. (5) Contributor workflow and PublishPress Permissions deferred to Phase 2. (6) Breach notification SLA revised from 24 hours to 72 hours, aligned with market-standard DPA language and Illinois PIPA. (7) All requirements organized into implementation phases (Phase 1: August 2026 launch; Phase 2: 6–12 months post-launch; Phase 3: future). (8) Open question #10 removed (WP LMS course-contributor mechanism, now moot). New open questions A and B added for course catalog implementation and completion records import mechanism. |

| 0.2.1 | 2026-07-24 | sendres | §5: withdrew the break-glass recovery account and its TOTP/vault protection model, replacing them with administrator access via MyKinsta WP Admin auto-login (no password assigned) and an SSH/WP-CLI recovery path held by at least two people. Added the `ctle@dom.edu` mailbox access list as a named security control, since it receives the MyKinsta two-factor codes gating Administrator access. Removed the corresponding DU IT credential-vault responsibility. §17: removed the Two Factor / WP 2FA plugin, no longer required as no local password login remains; retargeted WP Activity Log alerting to all Administrator logins and role changes. |

| 0.2.2 | 2026-07-24 | sendres | Corrected the LTI plugin naming in §6 and §17: WordPress is the LTI **tool** launched from Canvas, so the correct software is the **LTI Tool** plugin (ceLTIc project) with its **ceLTIc LTI Library** dependency — not "LTI Platform for WordPress," which is the reverse integration (WordPress as the platform, embedding external tools). Matches the correction recorded in `IT_REQUESTS.md` Request 3. |
| 0.2.3 | 2026-07-24 | sendres | §6 and §17: flagged the similarly named ceLTIc **LTI Platform** plugin as the wrong-direction plugin that must not be installed in place of **LTI Tool**. |
| 0.2.4 | 2026-07-27 | sendres | Post-IT-meeting decisions. §5: SSO access is gated on an Entra group refreshed from the SIS faculty list (Option 1), with CTLE admins/director/developer reaching WordPress via the hosting console rather than the group; noted the Kinsta one-SSH-user constraint on the two-person recovery path. §13/§17: sender is now the dedicated `ctle-noreply@dom.edu` mailbox, sending via Microsoft Graph `Mail.Send` only (SMTP AUTH dropped); resolved open question #15. |
| 0.2.5 | 2026-07-27 | sendres | §5: named the SSO/LTI account-linking WordPress user-meta key `sis_user_id`, and documented the multi-path admin reconciliation — admins who are also faculty get `sis_user_id` stamped (matching Entra's `employeeId`) before first SSO so MyKinsta/SSO/LTI resolve to one account, with role preservation verified afterward. |
| 0.2.6 | 2026-07-28 | sendres | §6 reversed: the **Canvas global-nav link + Entra SSO** is now the primary and only launch mechanism; the **LTI 1.3 launch is withdrawn** (LTI Tool + ceLTIc LTI Library plugins deactivated, kept installed). Rationale added (CTLE needs none of LTI Advantage's services; nav-link + single Entra identity delivers the launch with far less complexity and removes a pre-launch workstream). Validated the Canvas-side button gating via `declared_user_type` read from `users/self/logins`. LTI-launch/provisioning references elsewhere (§5, Faculty Profiles) now resolve through Entra SSO only; the Phase-2 avatar source (was the LTI payload) is to be re-chosen in Phase 2. |
| 0.2.8 | 2026-07-29 | sendres | §6 and §17: the LTI Tool + ceLTIc plugins are now **deleted**, not merely deactivated — "kept installed for optionality" reversed on the grounds that a withdrawn integration's code on disk is attack surface with no offsetting benefit, and both are free to reinstall from wordpress.org. No requirement changes; this records the executed state. |
| 0.2.7 | 2026-07-28 | sendres | Cross-doc audit sync (no new decisions — aligning the spec to what was built): §3 + §17 image optimization changed from a server-side plugin (ShortPixel/Smush/Imagify) to **Cloudflare Polish** at the CDN edge (Kinsta bans those plugins; Polish enabled 2026-07-28). §5 + §17 alerting split from WP Activity Log (Premium-only notifications) to the custom `ctle-admin-alerts.php` mu-plugin, and added a `ctle-hardening.php` (XML-RPC) row. §17 LTI plugin row marked withdrawn (§6). §Setup "IT involvement" no longer lists Canvas LTI. Phase-2 avatar source marked superseded. |

| 0.3.1 | 2026-08-18 | sendres | §5: recorded an **implementation deviation on *Administrator access protection*, item (2)** — the obfuscated login URL was removed on 2026-08-18. It was subtracting protection rather than adding it: Kinsta's edge brute-force ban watches `/wp-login.php` alone, so moving the login form forfeited it (measured 403 at the edge versus 200 on the custom path), and the same blind spot in the host's cache policy froze the SSO nonce into a cached page and broke sign-in for everyone. Replaced by `auto` login mode, which redirects `/wp-login.php` straight to Entra with a freshly minted nonce and presents no WordPress login form to any user — verified as distinct `state` values across consecutive requests with the cache bypassed. Residual risk is low because no password-authenticated account exists. **The §*IT Responsibilities* sign-off request is amended to drop item (2)** from the protection model. Also recorded, under *Account linking*, that **MyKinsta auto-login matches an existing account by email** — established by test rather than assumed, which is what makes the multi-path admin reconciliation safe. The requirement rows themselves are left as approved. |
| 0.3.0 | 2026-08-18 | sendres | §5: recorded that the *Account linking* deviation **must not be raised with CTLE in writing** while the communications hold is in force — it belongs in the face-to-face with working SSO as evidence. Also recorded that the deviation's first-login exposure is **closed for all three known accounts**: both existing Live accounts carry their real `dom.edu` addresses, and Amanda's is created ahead of her first sign-in rather than by it. No requirement changed. |
| 0.2.9 | 2026-08-14 | sendres | §5: recorded an **implementation deviation on *Account linking***, which specifies matching by `sis_user_id` and explicitly "not by email". OpenID Connect Generic 3.11.3 cannot match an arbitrary claim against an arbitrary user-meta key — verified in its source — so what was built matches on email at first sign-in and on the Entra `sub` claim thereafter. The requirement's intent (continuity across name and email changes) is preserved from the second login onward, since `sub` is immutable; the exposure is the first login only. The requirement row is left as approved and the deviation noted beneath it, **pending CTLE's decision to accept or fund an alternative.** Consequence flagged: the IT responsibility to confirm Entra email claims match local admin account emails is now load-bearing rather than hygiene. No other requirement changed. |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

---

**End of Specification**

# CTLE WordPress — Kinsta Onboarding & Setup Checklist

**Purpose:** Step-by-step setup guide for the CTLE WordPress site on Kinsta, from account creation through Phase 1 launch readiness.

**Plan:** Single 20GB · **Billing:** $0 for the first month , then $350 for months 2–12 (first year annual)

**Target launch:** August 2026

**Coordination required:** Steps marked **[DU IT]**, **[DU LT]**, or **[CTLE]** require action from those parties before or during the step.

---

## Table of Contents

**Initial Setup (Persis, Steven, Amanda)**
1. [Account & Billing](#1-account--billing)
2. [MyKinsta Admin Setup](#2-mykinsta-admin-setup)
3. [WordPress Site Provisioning](#3-wordpress-site-provisioning)

**WordPress Configuration (Steven, Amanda, Persis)**
4. [WordPress Core Setup](#4-wordpress-core-setup)
5. [Plugin Installation](#5-plugin-installation)
6. [Security Baseline](#6-security-baseline)
7. [Administrator Access & Recovery](#7-administrator-access--recovery)
8. [Developer Access](#8-developer-access)

**Infrastructure (Steven)**
9. [DNS & SSL](#9-dns--ssl)
10. [Server & PHP Configuration](#10-server--php-configuration)
11. [CDN & Caching](#11-cdn--caching)
12. [Backup Configuration](#12-backup-configuration)

**Backend Configuration and IT Integrations (Steven)**
13. [SSO Configuration](#13-sso-configuration)
14. [First Admin User Elevation](#14-first-admin-user-elevation)
15. [Email Configuration](#15-email-configuration)
16. [LTI / Canvas Integration](#16-lti--canvas-integration)

**Content & Features (Amanda, Persis, Steven)**
17. [Events Calendar Setup](#17-events-calendar-setup)
18. [Discussion Forums Setup](#18-discussion-forums-setup)
19. [Search Setup](#19-search-setup)
20. [Course Catalog Setup](#20-course-catalog-setup)
21. [Home Page Setup](#21-home-page-setup)
22. [Privacy & Compliance](#22-privacy--compliance)
23. [Pre-Launch Verification](#23-pre-launch-verification)

---

## Initial Setup

## 1. Account & Billing

- [x] Sign up for a Kinsta account at [kinsta.com](https://kinsta.com)
> Company Owner account: CTLE Director, `ctle@dom.edu`. **Password is held in the CTLE credential vault — never record it in this repository.**
- [x] Select the **Single 20GB** plan with **annual billing** ($0 month 1, $350 for months 2–12) — [WordPress Hosting Plans](https://kinsta.com/docs/billing/wordpress-hosting-plans/)
- [x] Enter payment method (credit card or invoice) — [Payment Methods](https://kinsta.com/docs/billing/payments/)
- [x] Set the billing contact to the appropriate DU administrative contact — [Billing Details](https://kinsta.com/docs/billing/update-billing-address/) · [Invoices](https://kinsta.com/docs/billing/invoices/)
- [x] Request Kinsta's current **SOC 2 Type II attestation letter** and **ISO 27001 certificate** from [trust.kinsta.com](https://trust.kinsta.com) — provide to DU IT for vendor security sign-off


---

## 2. MyKinsta Admin Setup

MyKinsta is Kinsta's hosting control panel. Access is separate from WordPress admin.

- [x] Verify that your MyKinsta login uses a strong, unique password — [User Settings](https://kinsta.com/docs/user-settings/)
- [x] Enable two-factor authentication on the MyKinsta account (account settings → Security) — [Two-Factor Authentication](https://kinsta.com/docs/user-settings/logging-in/)
> For the Company Owner (CTLE Director, `ctle@dom.edu`), two-factor authentication codes get sent to that address so anyone with CTLE email account access can verify the login. For Company Admin and Company Developer users, the codes get sent to the individual users' email addresses.
- ~~[ ] **Optional:** Configure [MyKinsta SAML SSO with Microsoft Entra](https://kinsta.com/docs/company-settings/wordpress-saml-sso/microsoft-entra-saml-sso/) for MyKinsta dashboard access — note this covers dashboard login only, not WordPress site login (that is handled separately in §13)~~
> **Not done** since the previous MyKinsta MFA-to-email process is adequate.
- [x] Invite the CTLE admins as MyKinsta company users with the appropriate role (Company Developer or Company Administrator, depending on whether CTLE Admin needs billing access) — [User Management](https://kinsta.com/docs/company-settings/user-management/)
- [x] Confirm both the CTLE admin (Persis) and developer (Amanda) can log in to MyKinsta successfully
> **Elevated importance as of 2026-07-24.** With the break-glass account withdrawn (§7), MyKinsta account security *is* WordPress Administrator security — anyone who can log in to MyKinsta can reach WP Admin via auto-login. Treat MyKinsta user management with the same care originally planned for the break-glass credential vault. See §7 for the specific controls.


---

## 3. WordPress Site Provisioning

- [x] In MyKinsta, create a new WordPress site: Sites → Add site → Install WordPress — [Add a New Site](https://kinsta.com/docs/wordpress-hosting/wordpress-getting-started/new-site/)
- [x] Site name: `DU-CTLE` (or your convention)
- [x] Select the `Chicago (US)` datacenter — [Data Center Locations](https://kinsta.com/docs/service-information/data-center-locations/)
- [x] Record the temporary WordPress admin credentials generated by Kinsta — store securely; this account is superseded by MyKinsta auto-login (§7) and **must be deleted** once auto-login (§7) and SSH/WP-CLI recovery (§8) are both verified — see §6
> Username `topsecretuser`. **Password is held in the CTLE credential vault — never record it in this repository.** This account is scheduled for deletion (§6) once auto-login and SSH recovery are verified, after which the credential becomes moot.
- [x] Verify the staging environment is present: Sites → [site] → Staging — it should be available without any add-on — [Staging Environments](https://kinsta.com/docs/wordpress-hosting/staging-environment/)
- [x] Confirm the site is accessible at the Kinsta-provided temporary URL (e.g., `ductle.kinsta.cloud`)

---

## WordPress Configuration (Steven, Amanda, Persis)

## 4. WordPress Core Setup

- [x] Update WordPress core to the latest stable version (Kinsta may have installed an earlier version at provisioning)
- [x] Update the default theme(s) — **updated 2026-07-29; none deleted.** Theme deletion waits on the CD-1 theme decision, since "keep the active theme and one fallback" cannot be applied before knowing which theme is active.
- [x] Delete the unused plugins: Hello Dolly, Akismet, and — as of 2026-07-29 — **LTI Tool + ceLTIc LTI Library** (LTI withdrawn, decision 10; this reverses the earlier "kept installed for optionality"). **Keep Relevanssi**, staged inactive for §19. ✅ Done 2026-07-29: `wp plugin delete akismet hello lti-tool celtic-lti`
- [ ] **[CTLE]** Delete or replace the default sample content: "Hello World" post, "Sample Page" page, and the default comment
> **Re-assigned 2026-07-29 (CD-14).** Posts and pages are CTLE's domain, so the Director and Developer own this rather than infrastructure. **Nobody is otherwise scheduled to do it** — §23 therefore carries a matching hard launch gate.
- [x] Settings → General:
  - [x] Site Title: `Dominican University CTLE`
  - [x] Tagline: leave blank for now
  - [x] WordPress Address and Site Address: `https://ctle.dom.edu`
  > This was marked complete prematurely. As of 2026-07-24 it is genuinely done — performed by the primary-domain search and replace in §9, and verified. Kinsta serving the site at a custom domain and WordPress *knowing* its own address are two independent settings; only the first had happened.
  - [x] Administration Email: the DU shared mailbox (e.g., `ctle@dom.edu` — see §15) or a CTLE Admin DU email
  - [x] Timezone: `America/Chicago`
  - [x] Date and time format: Selected `F j, Y` date (e.g., `May 29, 2026`) and `g:i a` time (`8:00 pm`) as initial settings; this can be changed later
- [x] Settings → Reading → check **Discourage search engines from indexing this site**
> Set 2026-07-24. *(Checkbox corrected 2026-07-29 — it had been left unticked while the register recorded ME-1d as done.)* As of the DNS cutover (§9) the site is publicly reachable and fully indexable at `ctle.dom.edu` — verified: no `x-robots-tag` header, no meta robots tag, and `robots.txt` advertises the sitemap. Without this, a bare install with placeholder content can be indexed under the DU brand.
>
> ⚠️ **This creates a launch-blocking failure mode: if it is not unchecked at launch, the site is invisible to search engines.** The corresponding un-check is a hard gate in §23. Do not remove one without the other.
>
> Note this is a WordPress-level setting and is separate from Kinsta's automatic `noindex` on the temporary `*.kinsta.cloud` hostname, which applies regardless.
- [x] Settings → Permalinks: select `Post name` format (e.g., `https://ductle.kinsta.cloud/sample-post/`)
- [x] Settings → Discussion:
  - [x] Uncheck Attempt to notify any blogs linked to from the post
  - [x] Uncheck Allow link notifications from other blogs (pingbacks and trackbacks) on new posts
  - [x] Confirm Allow people to submit comments on new posts is unchecked

---

## 5. Plugin Installation

Install plugins in the order listed. Activate and do a basic sanity check after each one before proceeding.

> ⚠️ **Install on Live, not staging.** This section originally said to install on staging and [push to production](https://kinsta.com/docs/wordpress-hosting/wordpress-push-environments/) afterwards. That is reversed as of 2026-07-29 and the original instruction was wrong for this site: Live is the build environment (CD-2), so Live holds the current plugin set, the activation states, and the hardening mu-plugins. A staging→live push would overwrite all three. See §24 for the push protocol that governs every environment sync on this site.

### Security & Admin Plugins (install first)

- [x] **WPS Hide Login** (`wps-hide-login` v1.9.18) — changes the default `wp-login.php` path. Done + verified 2026-07-27 (old `wp-login.php` → 404; new path serves 200; MyKinsta auto-login compatible).
  - After activation, set a custom login path in Settings → WPS Hide Login
  - Record the new login path; it is also the URL the SSO sign-in button must point to (§13)
  - Verify: navigate to `/wp-login.php` and confirm it returns 404 or redirects; navigate to the new custom path and confirm the login form appears
  - **Compatible with MyKinsta auto-login.** Kinsta detects a customized login URL automatically; allow up to one minute after changing it before the auto-login button works again — [WP Admin](https://kinsta.com/docs/wordpress-hosting/site-management/wordpress-wp-admin/)
- [x] **WP Activity Log** (`wp-security-audit-log` v5.6.5) — audit logging. Installed + active on Live 2026-07-28.
  - **Email alerting is handled by a custom must-use plugin, not this one.** WP Activity Log's custom email notifications are a Premium (paid) feature; the free tier only logs. Rather than license it for two rules, the alerts (any Administrator login; any role change) live in `mu-plugins/ctle-admin-alerts.php` (deployed to Live 2026-07-28), using free core hooks. See §7.
  - Verify alert delivery once WP Mail SMTP → Graph is live (§15 / IT-2) — WordPress has no working mail transport until then.
- [x] **Query Monitor** (`query-monitor` v4.0.7) — developer diagnostics; output renders to logged-in admins only (safe on Live). Installed + active on Live 2026-07-28. May be deactivated when not actively debugging.
- ~~[ ] **Two Factor** (or **WP 2FA**) — TOTP 2FA for the break-glass account (see §7 for configuration)~~
> **Not needed** as of 2026-07-24. This plugin existed solely to protect the break-glass account's password login (§7, withdrawn). Under the current model no privileged account has a password at all: faculty authenticate through Entra (which enforces DU's own MFA), and administrators enter through MyKinsta auto-login (which enforces MyKinsta 2FA per §2). There is no local password login left for a WordPress 2FA plugin to protect. Note that `REQUIREMENTS.md` §5 and `IMPLEMENTATION_PHASES.md` §17 still list this plugin as a Phase 1 requirement — both need the same amendment.

### Communication Plugin

- [x] **WP Mail SMTP** (`wp-mail-smtp` v4.9.0) — installed + active on Live 2026-07-28. **Not yet configured** — enter the Microsoft Graph credentials in §15 once IT-2 delivers the `ctle-noreply@dom.edu` mailbox + `Mail.Send` app registration. Until then WordPress falls back to default (non-working) mail.

### Events Plugin

- [ ] **The Events Calendar Pro** + **Event Tickets**
  - Enter license key
  - Run setup wizard; configure default currency, date/time formats, and map provider if needed
  - Verify a test event can be created and displayed

### Forum Plugin

- [x] **wpForo** (free core from WordPress.org) — **installed 2026-07-29, left inactive.** Configuration waits on SSO (§13) and the CD-8 forum privacy language; pre-staged so nothing is left to discover during the SSO push.
  - Run setup wizard
  - Leave reputation features disabled for now (Phase 2)
  - Forum access configuration in §18

### LTI Plugin

- [x] ~~**LTI Tool** + **ceLTIc LTI Library**~~ — **superseded 2026-07-28 (decision 10): LTI dropped for a Canvas nav-link + Entra SSO.** Both were installed + active on Live 2026-07-28 (`lti-tool` 3.2.6, `celtic-lti` 5.3.2; correct plugin confirmed — title "LTI Tool", not "LTI Platform"), then **deactivated** the same day, and **deleted 2026-07-29** (§4). The "kept installed for optionality" position was reversed deliberately: the integration is withdrawn, both plugins are free and reinstallable from wordpress.org in about a minute, and unused plugin code on disk is attack surface with no offsetting benefit. `wp plugin delete` runs each plugin's uninstall routine — desirable here, since neither was ever configured and there was no LTI state to preserve. See §16 and `REQUIREMENTS.md` §6.
  - ⚠️ **Install "LTI Tool" — not "LTI Platform."** The ceLTIc project publishes two near-identically named plugins by the same author that sit side by side in the wordpress.org search results. **LTI Platform** (which the original checklist named as "LTI Platform for WordPress") is the *reverse* integration — it makes WordPress a platform that embeds external tools, so faculty could never launch *into* CTLE from Canvas. See `IT_REQUESTS.md` Request 3.

### SSO Plugin

- [ ] **miniOrange SAML SSO** (or **OpenID Connect Generic** — choose one based on what DU IT's Entra app registration supports: SAML or OIDC)
  - Install and activate; full configuration in §13

### Search Plugin

- [ ] **Relevanssi** (free version from WordPress.org)
  - Install and activate; full configuration in §19

### Page Builder (if using Beaver Builder)

- [ ] **Beaver Builder** (Lite or Pro)
  - Install and activate
  - If using Gutenberg blocks instead, skip this step

### Forum Privacy Consent

- [ ] Implement the forum privacy disclosure (choose one approach):
  - **Build:** Custom code — a first-visit acknowledgment modal with consent flag stored in user meta, a posting-time reminder in the wpForo reply form template, and a footer link on all forum pages
  - **Buy:** Evaluate a consent plugin (e.g., Complianz or CookieYes) for the modal/consent tracking, and handle the posting-time reminder and footer link as template customizations
  - Full configuration in §18

> **Note on image optimization:** Do not install a server-side image optimization plugin (e.g., Imagify with server binary mode, ShortPixel with local binary). Kinsta CDN provides [Cloudflare Polish](https://kinsta.com/docs/image-optimization-for-wordpress/) (automatic WebP conversion) at the edge — this covers the requirement without a plugin. If a plugin is desired for bulk pre-upload optimization, choose an API-based plugin only.

---

## 6. Security Baseline

- [x] **Do not** use `admin` as the WordPress admin username — delete or rename the default admin user if it was created with that username. ✅ Satisfied — the only accounts are the two random-username MyKinsta auto-login admins; `topsecretuser` deleted 2026-07-27.
- [x] Disable XML-RPC — implemented in `mu-plugins/ctle-hardening.php` (deployed to Live 2026-07-28). **Verified 2026-07-28:** `xmlrpc.php` returns **403 at the Nginx layer** (Kinsta blocks it before WordPress runs), the app-layer `xmlrpc_enabled` filter is belt-and-suspenders behind that, and the `X-Pingback` header is gone. Reference snippet:
  ```php
  add_filter('xmlrpc_enabled', '__return_false');
  ```
  Note: Kinsta also blocks XML-RPC attacks at the Nginx level, but disabling it in WordPress adds defense in depth. — [Kinsta Infrastructure & Security](https://kinsta.com/docs/wordpress-hosting/wordpress-getting-started/wordpress-infrastructure/)
- [x] Verify Kinsta's built-in brute-force protection is active (automatic IP ban after > 6 failed logins/minute) and confirm whether it follows the custom login URL from §5 — [Bot Protection](https://kinsta.com/docs/wordpress-hosting/mykinsta-tools/wordpress-tools-bot-protection/)
> **Answered by Kinsta support 2026-07-29 — and the answer was no.** In their words: the protection "specifically monitors requests to **/wp-login.php**… Requests to the **custom login URL** are not the endpoint the system watches for this protection."
>
> **Consequence:** WPS Hide Login moved the form to a custom path, so the endpoint that actually accepts credentials has **no rate limiting and no IP ban** — unlimited password attempts. Kinsta's protection now guards a path that returns 404. The obfuscated URL still raises the cost of *finding* the form, but it is not a rate limit, and obscurity was never meant to be the whole control.
>
> This had been assumed to be fine because Kinsta auto-detects a customized login URL for *auto-login* purposes. It does — but that is a different subsystem from the brute-force ban. **Do not generalize "Kinsta knows about the custom URL" across features.**
>
> **Compensating control — password authentication removed outright** (`mu-plugins/ctle-hardening.php` v1.1.0, 2026-07-29). See the subsection below. Rejected alternative: a rate-limiting plugin such as Limit Login Attempts Reloaded, which is the right answer for a site that *needs* password login. This one does not — so the stronger move is to delete the capability rather than throttle it. Keep the plugin in mind as the fallback if the removal ever has to be reverted.
>
> Severity was low at the time of discovery (no password-authenticated account existed) but would **not** have stayed low: SSO JIT provisioning gives every faculty account a WordPress password hash at go-live.
>
> **Measured 2026-07-29, not merely inferred from the support answer.** Two `curl` probes established the asymmetry directly:
>
> | Request | Result |
> |---|---|
> | `GET /wp-login.php` (staging, past Basic Auth) | **200** — page serves |
> | `POST /wp-login.php` with credentials (staging, past Basic Auth) | **403** — refused at Kinsta's edge before WordPress runs |
> | `POST <custom login path>` with credentials (Live) | **200** — form processed normally, no block, no throttle |
>
> So it is sharper than "the IP ban doesn't follow the custom URL." Kinsta *actively blocks automated login POSTs* on `wp-login.php` at the edge — real, working protection — and the custom path has **none of it**. Moving the login form traded a protected endpoint for an unprotected one. Obscuring a login URL and protecting it are different things, and only one of them was in place.
- [ ] In MyKinsta, navigate to Sites → [site] → Security → IP Deny — add any known malicious IP ranges if applicable — [Block IP Addresses](https://kinsta.com/docs/wordpress-hosting/site-management/block-ip-address/) *(N/A 2026-07-28 — none known to add.)*

### Eliminate password-based login paths

With the break-glass account withdrawn (§7), the goal is that **no account on the site can be logged into with a password**. Faculty enter via Entra SSO, administrators via MyKinsta auto-login. Every remaining password login is an attack surface with no legitimate user behind it.

- [x] **Delete the temporary provisioning account from §3** (`topsecretuser`) — ✅ done 2026-07-27 (reassigned to ID 3). This was a password-authenticated Administrator created by Kinsta at install and was the only such account on the site. **Gate the deletion on two things, both of which can be done the same day: MyKinsta auto-login verified working (§7), and SSH + WP-CLI recovery verified working (§8).** Do *not* wait for SSO (§13) — that is weeks out, and once the site is publicly reachable at `ctle.dom.edu` (§9) this account is a password-authenticated Administrator sitting on a public URL with no legitimate user behind it. Notify the Director and Developer before deleting, in case either has been using it.
  ```bash
  wp user list --role=administrator --fields=ID,user_login,user_email
  wp user delete <id> --reassign=<keep-user-id>
  ```
- [x] Disable open user registration: Settings → General → uncheck **Anyone can register**. All provisioning happens through SSO (§13) and LTI (§16). ✅ Verified 2026-07-28 — `users_can_register` = 0.
- [x] **Remove password authentication entirely** — `mu-plugins/ctle-hardening.php` v1.1.0 (2026-07-29) drops core's username/password and email/password authenticators (priority 20 on the `authenticate` filter), removes the application-password authenticator, hides application passwords, and disables password reset. `wp_authenticate_cookie` is deliberately left in place — it is what keeps existing sessions valid.
> **This is the compensating control for the brute-force gap above**, and it turns §6's stated goal from an operational assumption into an enforced property: no account on this site can be logged into with a password, whether or not one exists. Faculty authenticate through Entra; administrators through MyKinsta auto-login, which issues an auth cookie directly and never reaches those authenticators.
>
> ⚠️ **Verify MyKinsta auto-login on staging before deploying to Live.** If it were to break, the site's only remaining entry is SSH.
>
> ⚠️ **This changes the §7/§8 recovery procedure** — resetting a password over WP-CLI no longer grants a login while this file is in place. See §7 "Recovery path."
- [ ] After SSO is live, audit for any remaining password-capable accounts: `wp user list --fields=ID,user_login,user_email,roles` — every account should trace to either an Entra SSO-provisioned user or a MyKinsta auto-login user
- [x] Password-protect the staging environment: MyKinsta → Sites → [site] → staging → Tools → Password Protection. Staging carries the same code and often the same data as production but gets none of the attention — do not leave it publicly reachable. ✅ **Enabled 2026-07-29** (HTTP Basic Auth at the Nginx layer, so it challenges before WordPress runs). Credentials go to the Director and Developer via DU SecureTransfer, never this repo.
- [x] Confirm the custom login path from §5 is not leaked in any published page, sitemap, or the repository — never committed to this repo; keep it that way.
> **Rotated 2026-07-29.** The then-current path was pasted into a working session outside the repo during the §6 brute-force diagnostics, so it was rotated the same day and the new path used for the CD-N3 notice — which had not yet been sent, making rotation free. The repo rule held: no login path has ever been committed. Treat the path as a speed bump, not a control; removing password authentication is what makes an exposed path uninteresting.

---

## 7. Administrator Access & Recovery

> **Decision — 2026-07-24: no break-glass account will be created.** MyKinsta's **WP Admin auto-login** provides the same guaranteed privileged access with a materially better security property — the account it provisions has **no password at all**, so it cannot be brute-forced, phished, or credential-stuffed at the WordPress layer. A vaulted break-glass password would have been a permanent, high-value, password-authenticated target; this is not. All DU LT coordination previously required by this section is withdrawn.

~~All steps in the original break-glass procedure — LT-agreed username, vaulted 20-character password, TOTP enrollment, vaulted TOTP seed, LT rotation procedure — are superseded by the sections below.~~

### How to use MyKinsta auto-login

1. Log into [https://my.kinsta.com](https://my.kinsta.com).
2. On the Dashboard, click the **DU-CTLE** site.
3. Click the **Create admin and log in** button.
4. A "Confirm WP admin user creation" dialog will appear. Click **Create admin and log in**.

Notes:
- On first use, MyKinsta creates a WordPress Administrator account matched to the MyKinsta user's email address, with a randomly generated username and no password assigned.
- If a WordPress user with that email already exists, MyKinsta logs into that existing account instead of creating a new one.
- The WordPress password is never collected or stored by MyKinsta.
- Auto-login is enabled by default; it is managed at Sites → DU-CTLE → User management → WP Admin auto-login. Only Company Owners, Company Administrators, and Company Developers can enable or disable it.
- ~~If a user ever needs a conventional password login, they must obtain one through the standard "Lost your password?" reset process since auto-login does not create one.~~
> **No longer possible as of 2026-07-29, by design.** `ctle-hardening.php` v1.1.0 removes password authentication and disables password reset, so there is no route to a conventional password login on this site — the "Lost your password?" flow would only mint a credential that authenticates nothing. Anyone who needs in uses MyKinsta auto-login, Entra SSO, or the SSH recovery sequence below.

### What this changes about the security model

Administrator access to WordPress is now gated entirely by MyKinsta account security. Two consequences must be actively managed rather than assumed.

- [ ] **The shared mailbox is now effectively an admin credential.** Per §2, two-factor codes for the Company Owner (`ctle@dom.edu`) are delivered to that shared mailbox — so anyone who can read CTLE mail can reach WordPress Administrator. Decide and document who has access to `ctle@dom.edu`, and keep that list at least as short as the break-glass vault list would have been.
- [x] Prefer per-person **Company Developer** accounts for routine work — their 2FA codes go to individual DU addresses (§2) rather than the shared mailbox. Reserve the Company Owner account for billing and ownership tasks. ✅ Confirmed 2026-07-29
- [x] Review MyKinsta company users quarterly and remove anyone no longer on the project — removing a MyKinsta user removes their path into WP Admin ✅ First review done 2026-07-29 — **recurring**, next due 2026-10-29
- [ ] After each new person's first auto-login, check Users in WP Admin and confirm the auto-provisioned account is expected and that no unexpected Administrator accounts exist

### Recovery path if MyKinsta is unavailable

MyKinsta account standing is now a single point of failure for administrator access: a billing lapse, account suspension, or dashboard outage removes the auto-login path. SSH is the fallback, and it is the real replacement for the break-glass account.

- [ ] Confirm the developer's SSH key is on file and working **before launch** (§8). With SSH, administrator access can always be restored through WP-CLI regardless of the MyKinsta dashboard's state.

  ⚠️ **Amended 2026-07-29.** `ctle-hardening.php` v1.1.0 removed password authentication (§6), so creating or resetting an account is no longer enough on its own — nothing can log in with the resulting password until the hardening file is moved aside. The recovery sequence is now:
  ```bash
  ssh <user>@<host> -p <port>
  mv ~/public/wp-content/mu-plugins/ctle-hardening.php ~/ctle-hardening.php.off

  cd public
  wp user create <username> <email> --role=administrator
  # or reset an existing account
  wp user update <user> --user_pass="$(openssl rand -base64 24)"

  # ... log in, resolve the incident, then restore the control:
  mv ~/ctle-hardening.php.off ~/public/wp-content/mu-plugins/ctle-hardening.php
  ```
  One extra step in a path that already requires SSH. The failure mode to avoid is discovering it mid-incident — which is why it is written here, in the file header, and in §6.
- [ ] Ensure **at least two people** hold working SSH keys, so recovery never depends on one person's laptop being available
- [x] Confirm the billing contact (§1) is a monitored DU address and that the annual renewal cannot lapse silently — a suspended Kinsta account takes the dashboard *and* SSH with it, which is the one scenario neither path covers ✅ Confirmed 2026-07-29

### Administrator login alerting

The audit requirement from the original break-glass design still applies; only the trigger changes. Administrator logins should be rare and deliberate once faculty are on SSO, so alerting on all of them stays low-noise.

Implemented via the **`ctle-admin-alerts.php` must-use plugin** (source in the repo at `mu-plugins/`; deployed to Live 2026-07-28), **not** WP Activity Log — whose custom notifications are Premium-only (§5). A must-use plugin cannot be deactivated from the WP admin UI, which is the right property for a security alert.

- [x] Email the CTLE admin list on **any successful login by any user holding the Administrator role** — `wp_login` hook
- [x] Email on **any user role change** — `set_user_role` hook (with a documented filter to suppress routine new-Faculty provisioning once SSO is live)
- [ ] Verify delivery once WP Mail SMTP → Graph is live (§15 / IT-2); today WordPress has no working mail transport. Recipients **widened 2026-07-29 to Steven + the Director** and redeployed (**ME-12** closed). Add the Director and any other named CTLE admin; **deliberately not `ctle@dom.edu`**, since that mailbox receives the MyKinsta 2FA codes gating Administrator access and would put the alert and the second factor in one inbox. Revisit once IT-4 settles the access list. The recipient list drops any entry that is not a valid address, so a half-edited placeholder cannot become a live recipient.

---

## 8. Developer Access

> **Steven's access verified 2026-07-27 (SSH, SFTP, WP-CLI on both Staging and Live); checkboxes below ticked 2026-07-29 to match.** The outstanding item is **Amanda's** public key, which is what the two-person recovery requirement in §7 actually depends on (ME-6).
>
> ⚠️ **Recovery has a prerequisite as of 2026-07-29.** SSH alone no longer restores a login: `ctle-hardening.php` removes password authentication, so a WP-CLI password reset produces a credential that authenticates nothing until that file is moved aside. The full sequence is in §7 → "Recovery path if MyKinsta is unavailable." Read it *before* an incident, not during one.

- [x] In MyKinsta, navigate to Sites → [site] → Info → SFTP/SSH to find connection details — [Connect via SSH](https://kinsta.com/docs/wordpress-hosting/connect-to-ssh/) · [Connect via SFTP](https://kinsta.com/docs/wordpress-hosting/connecting-with-sftp/)
- [x] Generate a personal SSH key pair if you do not already have one: `ssh-keygen -t ed25519 -C "dev@example.com"`
- [x] Add your public key to MyKinsta: User Settings → SSH Keys → Add SSH Key — *Steven's key on file; **Amanda's still outstanding** (ME-6)* — [SSH Key Authentication](https://kinsta.com/docs/wordpress-hosting/connect-to-ssh/)
  - **One SSH user per environment.** Kinsta does not allow additional SSH users — but each MyKinsta company member adds their **own** key here, and all authorized keys connect as that single environment user. The two-person recovery requirement (§7) is therefore satisfied by two MyKinsta members each holding a key, not by two SSH accounts. Kinsta "additional users" are SFTP-only (no shell, no WP-CLI) and are **not** a recovery path. `ssh-keygen -t ed25519` works even though Kinsta's docs show `-t rsa`. (Confirmed on Staging + Live, 2026-07-27.)
- [x] Test SSH access: `ssh [user]@[host] -p [port]` — verified on Staging + Live 2026-07-27
- [x] Test SFTP access using your preferred client (e.g., Transmit, FileZilla, Cyberduck) with key-based authentication
- [x] Test WP-CLI via SSH: `wp --info` — confirm WP-CLI v2 is available — re-confirmed 2026-07-29 — [WP-CLI](https://kinsta.com/docs/wordpress-hosting/site-management/wordpress-wp-cli/)
- [ ] Provide SFTP/SSH credentials (or instruct on MyKinsta user creation) to CTLE Admin if they need direct file access — use a separate MyKinsta user account, not the developer's credentials — [User Management](https://kinsta.com/docs/company-settings/user-management/)

---

## Infrastructure (Steven)

## 9. DNS & SSL

**Prerequisite — [DU IT]:** DU IT must configure the `ctle.dom.edu` subdomain. Provide them with the Kinsta-assigned IP address (found in MyKinsta → Sites → [site] → Info).

- [x] In MyKinsta, navigate to Sites → [site] → Domains → Add domain → enter `ctle.dom.edu` — [Domains and DNS](https://kinsta.com/docs/wordpress-hosting/wordpress-domains/)
- [x] Provide DU IT with the required DNS record:
  - For a subdomain: **CNAME** `ctle` → `[site].kinsta.cloud` (preferred), or **A record** pointing to the Kinsta IP
  - Kinsta documentation: [Point Your Domain to Kinsta](https://kinsta.com/docs/wordpress-hosting/wordpress-domains/dns/)
- [x] **[DU IT]** Create the `ctle.dom.edu` DNS record in DU's DNS management system
> Delivered under IT Ticket 26363781. IT created an **A record** to `162.159.135.42` (Cloudflare edge), plus a CNAME `www.ctle` → `ctle.dom.edu`. Verified 2026-07-24.
- [x] After DNS propagation (allow up to 24 hours), verify `ctle.dom.edu` resolves to the Kinsta server
- [x] **Check for a CAA record before expecting SSL to provision:** `dig ctle.dom.edu CAA +short` and `dig dom.edu CAA +short`. CAA records declare which certificate authorities may issue for a domain and are inherited up the DNS tree — a restrictive record at `dom.edu` would silently block Kinsta's automatic certificate issuance.
> **No CAA records exist** at either name (verified 2026-07-24), so any CA may issue. No action needed, and none was ever required — but the failure mode is quiet enough to be worth the five-second check.
- [x] In MyKinsta, confirm that Kinsta has automatically provisioned a free SSL certificate for `ctle.dom.edu` — [SSL Certificates](https://kinsta.com/docs/wordpress-hosting/wordpress-domains/wordpress-ssl-certificates/)
> Separate certificates issued for `ctle.dom.edu` and `www.ctle.dom.edu` by Google Trust Services (via Kinsta's Cloudflare layer), valid 2026-06-02 → **2026-08-31**. They auto-renew, but the expiry falls inside the launch window — confirm renewal has occurred in late August rather than assuming it.
- [x] Set the **primary domain** to `ctle.dom.edu`: MyKinsta → Sites → [site] → Domains → Set as primary. Leave the "Run search and replace after change" checkbox **enabled** — this performs the WordPress URL cutover in the same operation.
> **Take a manual backup first** (§12). This rewrites every table and the MyKinsta dialog offers no dry-run; the backup is the only undo. Expect to be logged out afterward — session cookies are scoped to the previous hostname.
- [x] Set the WordPress Site URL and Home URL to `https://ctle.dom.edu`
> Completed automatically by the primary-domain search and replace, above. Verified 2026-07-24: the REST link header reports `https://ctle.dom.edu/wp-json/`, and the homepage HTML contains zero `kinsta.cloud` references (previously 30).
- [x] Enable **Force HTTPS**: MyKinsta → Sites → [site] → Tools → Force HTTPS
> Two options are offered. Select **"Force HTTPS on all your live domains"** *only while the primary domain is still the temporary `*.kinsta.cloud` hostname* — the other option would redirect the real domain to the temporary one. Once `ctle.dom.edu` is primary, switch to **"Force HTTPS and redirect all traffic … to the primary domain"** to collapse `www` and the Kinsta hostname into one canonical identity.
>
> **Order matters:** never enable redirect-to-primary while WordPress still believes it lives at the old hostname. WordPress's canonical redirect sends the visitor back to the old host, Kinsta redirects to the new one, and the result is an infinite loop — most visibly in `wp-admin`.
- [ ] Verify HTTPS enforced and the canonical hostname is singular:
  ```bash
  curl -sS -o /dev/null -L -w 'final=%{url_effective} redirects=%{num_redirects}\n' http://ctle.dom.edu
  curl -sS -o /dev/null -L -w 'final=%{url_effective} redirects=%{num_redirects}\n' http://www.ctle.dom.edu
  curl -sS -o /dev/null -L -w 'final=%{url_effective} redirects=%{num_redirects}\n' https://[site].kinsta.cloud
  curl -sS https://ctle.dom.edu | grep -c 'kinsta\.cloud'   # expect 0
  ```
> **Verified complete 2026-07-24.** `http://ctle.dom.edu`, `http://www.ctle.dom.edu`, `https://www.ctle.dom.edu`, and `http://ductle.kinsta.cloud` all resolve to `https://ctle.dom.edu/`. The homepage contains zero `kinsta.cloud` references (previously 30).
>
> **One accepted gap:** `https://ductle.kinsta.cloud` still returns 200 without redirecting — Kinsta's redirect-to-primary covers HTTP but not HTTPS on its own temporary hostname. This is acceptable and is being left in place deliberately: Kinsta serves `x-robots-tag: noindex, nofollow, nosnippet, noarchive` on that hostname, so it cannot be indexed; WPS Hide Login (§5) operates at the WordPress level and so applies to both hostnames identically, meaning it is not a bypass of the obfuscated login path; and the hostname is the fallback route into the site if `ctle.dom.edu` DNS breaks. Scheduled for removal post-launch.
- [ ] **Do not enable HSTS yet.** It is tempting to turn on alongside Force HTTPS, but browsers cache the policy and it is hard to walk back. Add it after launch, once the hostname is settled, starting with a short `max-age`.

---

## 10. Server & PHP Configuration

Kinsta's defaults meet all CTLE requirements. Verify each value; contact Kinsta support if any differ.

- [x] In MyKinsta, navigate to Sites → [site] → Tools → PHP engine — [PHP Configuration](https://kinsta.com/docs/wordpress-hosting/php/)
  - **Set to PHP 8.4** on Live 2026-07-28 (decision 4 resolved: 8.4 over 8.3/8.5 for the runway-vs-maturity balance — security support to ~end of 2028; was 8.2, EOL Dec 2026). Verified `fpm-fcgi` reports **8.4.23**; `wp plugin list` clean, no fatals; Query Monitor clean after creating the optional `wp-content/plugins/lti/` sub-plugin dir that LTI Tool scans for.
- [x] Confirm PHP memory limit is **256 MB** — ✅ verified 2026-07-28 (`fpm-fcgi`: `memory_limit=256M`). **Read the web values from Site Health → Info → Server or Query Monitor's PHP panel — not `wp eval`/WP-CLI**, which reports the CLI SAPI's own defaults (`memory_limit=-1`, `upload=2M`, `post=8M`), not the site's php-fpm values. — [PHP Performance](https://kinsta.com/docs/wordpress-hosting/php/wordpress-php-performance/)
- [x] Confirm `max_execution_time` is **300 seconds** — ✅ verified 2026-07-28 (web: 300).
- [x] Confirm `upload_max_filesize` is **128 MB** — ✅ verified 2026-07-28 (web: 128M; `post_max_size` also 128M). `display_errors` off + `log_errors` on, as wanted for production.
- [x] Verify server-level cron is available — ✅ verified 2026-07-28: Kinsta system cron calls `wp-cron.php` every 15 min (`crontab -l`), and `wp cron event list` shows events queued. Set `DISABLE_WP_CRON=true` in wp-config 2026-07-28 (was undefined) so WP's pseudo-cron doesn't also fire on page loads. — [Cron Jobs](https://kinsta.com/docs/wordpress-hosting/site-management/cron-jobs/)
- [ ] Note: any custom `php.ini` directives beyond the self-service dashboard options require a Kinsta support request — document any such customizations separately (see backup note in §12) — [Configuration Changes](https://kinsta.com/docs/wordpress-hosting/site-management/configuration-changes/)

---

## 11. CDN & Caching

- [x] In MyKinsta, navigate to Sites → [site] → Kinsta CDN → Enable — ✅ enabled (default on all Kinsta sites; confirmed 2026-07-27). — [Kinsta CDN](https://kinsta.com/docs/wordpress-hosting/wordpress-cdn/kinsta-cdn/)
- [x] Enable **Cloudflare Polish** (WebP image optimization) in the CDN settings — this replaces the need for a server-side image optimization plugin — [Image Optimization](https://kinsta.com/docs/image-optimization-for-wordpress/)
  - ✅ Enabled 2026-07-28 in **Lossless** mode (CTLE chose image fidelity over max compression). Still does WebP conversion; easily switchable to Lossy later if bandwidth on the Single 20GB plan becomes a concern.
- [x] Verify edge caching is active for public pages: open an incognito window, load the site home page, and confirm the `X-Kinsta-Cache: HIT` response header on second load (use browser dev tools → Network tab) — [Edge Caching](https://kinsta.com/docs/wordpress-hosting/caching/edge-caching/)
> Confirmed 2026-07-24 for anonymous requests (`x-kinsta-cache: HIT` over HTTP/2), incidentally during the §9 verification. The authenticated-bypass half of this check is still outstanding, below.
- [x] Verify authenticated-user cache bypass: log in to WordPress, load the home page, and confirm the `X-Kinsta-Cache: BYPASS` response header — ✅ confirmed 2026-07-28 (logged-in request returned `x-kinsta-cache: BYPASS`). Ensures logged-in users (forum access, event registration state) do not receive cached pages.
- [x] Configure Kinsta's bandwidth usage alerts: MyKinsta → Company → Notifications — ✅ usage alerts enabled 2026-07-28 (were on by default). — [Notifications](https://kinsta.com/docs/user-settings/notifications/)

---

## 12. Backup Configuration

### Kinsta-Side Backups (14-day retention, fast restore)

- [x] Verify that daily automated backups are running: Sites → [site] → Backups → Automatic — ✅ confirmed 2026-07-28 (daily backups listed with timestamps; 14-day rolling retention).
- [x] Create a manual backup now as a baseline: Backups → Manual → Back up now
> Taken 2026-07-24 immediately before the primary-domain cutover in §9; a second baseline `pre-build-2026-07-28` taken before the 2026-07-28 build session. Both confirmed in Backups → Manual.
- [x] Confirm point-in-time restore is available: verify that any listed backup has a "Restore to" button — ✅ confirmed 2026-07-28.

### CTLE-Operated Off-Site Backup (30-day retention)

The Single 20GB plan retains 14 days of backups; the requirement is 30 days. A CTLE-operated daily backup fills this gap.

> **Deferred to post-launch (decided 2026-07-28, tracked as ME-11).** Needs a CTLE-controlled off-Kinsta storage destination (server / NAS / cloud bucket), which does not exist yet, plus an unattended backup job. Kinsta's 14-day retention + point-in-time restore covers the near term, so this is **not a launch-day blocker** — but it remains a requirement to close post-launch. Original setup steps below.

- [ ] Provision a CTLE-controlled server or storage location (e.g., a university file server, NAS, or cloud storage bucket) to receive daily backup archives — must be physically separate from Kinsta's infrastructure
- [ ] Create an SSH key pair for the unattended backup job: `ssh-keygen -t ed25519 -C "ctle-backup-job"` — add the public key to MyKinsta (see §8)
- [ ] Write and test a backup script that:
  1. SSHes into the Kinsta site using the backup job key
  2. Runs `wp db export /tmp/ctle-$(date +%Y%m%d).sql`
  3. `rsync`s `wp-content/` and the database export to the CTLE backup destination
  4. Retains at least 30 days of dated archives (prune older files in the same job)
  5. Sends a failure alert (email or monitoring webhook) if any step exits non-zero
- [ ] Schedule the script as a daily cron job on the CTLE server
- [ ] Verify the first backup run produces a complete, dated archive at the off-site destination
- [ ] **Document separately** any server-side configurations that are not captured in files or the database: Kinsta Nginx rules, IP Deny list entries, custom redirect rules, PHP/MySQL settings — these are not backed up by WP-CLI/rsync and must be recreated manually after a full server restore

---

## Backend Configuration and IT Integrations

## 13. SSO Configuration

> **Configure SSO on Live/production, not staging (CD-2, decided 2026-07-27).** The redirect/reply URLs and the Entra app registration are hostname-bound, and a staging→live push would overwrite Live and carry staging URLs. Building on Live means DU IT registers one redirect URI, not two. Reserve staging for post-launch update testing. Nothing is live to break yet (site is `noindex`ed and unannounced).

**Prerequisites — [DU IT]:**
- Register the WordPress site as an application in Microsoft Entra ID
- Configure Entra claims to pass: display name, email, and DU employee identifier (e.g., `employeeId` or `netID`)
- Restrict the app via Entra group assignment (assignment required) to an Entra group DU IT refreshes from the SIS current-faculty list. WordPress auto-provisions a Faculty account on first sign-in. CTLE admins/director/developer reach WordPress via MyKinsta auto-login (§7, §14), not this group — so the faculty group is the entire SSO scope. *(Option 1, decided 2026-07-27; Entra P1 confirmed.)*
- Confirm that the Entra email claim matches the email addresses used when local CTLE Admin / Developer Admin accounts were created (for email-based account linking on first SSO login)
- Provide the developer with the Entra tenant ID, client ID, client secret (OIDC) or metadata URL (SAML), and claim field names

### miniOrange SAML SSO Configuration (adjust if using OIDC plugin)

- [ ] Navigate to the miniOrange plugin settings in WP Admin
- [ ] Configure the Identity Provider (IdP) using DU IT's Entra metadata URL or manual entry of endpoints
- [ ] Map Entra claims to WordPress user fields:
  - Display name → `display_name`
  - Email → `user_email`
  - DU employee identifier → the `sis_user_id` custom user meta field — this becomes the account primary key
- [ ] Set **account linking method**: link by DU employee identifier (not email) — for users without an existing employee ID on file (i.e., initial local admin accounts), fall back to email-matching once, then the employee ID becomes primary
- [ ] **Reconcile the multi-path admin accounts *before* their first SSO login.** Each CTLE admin (the director, Steven, and the developer) has a MyKinsta auto-login Administrator account that already carries their DU email — and, being faculty, they will also arrive via SSO and LTI. Stamp the institutional ID onto each so all three paths resolve to the *same* WordPress user rather than creating duplicates:
  ```bash
  wp user meta update <id> sis_user_id <institutional_id>   # must equal Entra's employeeId claim, byte-for-byte
  wp user meta get <id> sis_user_id                          # verify
  ```
  Stamp while the account still has only the Administrator role, and confirm the exact `employeeId` format via IT-1. After the person's first SSO login, **verify the role was preserved** — the default-Faculty rule must apply to new accounts only and must never downgrade a matched admin (see §5). *(Steven and the director stamped 2026-07-27; the developer pending her first Live auto-login.)*
- [ ] Set **default role** for new SSO-provisioned users: **Faculty**
- [ ] Confirm **role preservation on login**: the SSO plugin must not modify the WordPress role of existing users on re-login — verify in plugin settings or by testing: log in as an elevated user (CTLE Admin), log out, log back in via SSO, and confirm the CTLE Admin role is unchanged
- [ ] Set **WordPress session lifetime**: add to `wp-config.php` or configure via plugin:
  ```php
  // 24-hour session lifetime
  define('AUTH_COOKIE_EXPIRATION', 86400);
  define('LOGGED_IN_COOKIE_EXPIRATION', 86400);
  ```
- [ ] Configure **profile field sync on every login**: on each SSO login, overwrite `display_name` and `user_email` from the incoming Entra claims — verify this is enabled in plugin settings
- [ ] Set the SSO login button URL to the custom login path set in WPS Hide Login
- [ ] Test end-to-end SSO login with a DU test account:
  - Navigate to the custom login URL
  - Click SSO sign-in
  - Authenticate with Entra
  - Confirm redirect to WordPress dashboard with Faculty role
  - Confirm `sis_user_id` user meta is populated
  - Confirm display name and email match the Entra claims

---

## 14. First Admin User Elevation

This is the one-time process to elevate the CTLE Admin and Developer Admin from their default Faculty roles. It must occur after SSO is configured and tested. Elevation is performed from a MyKinsta auto-login session (§7) rather than a break-glass account.

**Check first — the email-collision case.** If a person's MyKinsta email is the same as their DU SSO email, the Administrator account MyKinsta auto-provisioned for them (§7) will be matched by §13's one-time email fallback on their first SSO login, and they will simply keep Administrator — no elevation step needed. This is likely to apply to the Developer Admin. Confirm which case each person is in before running the steps below, and confirm §13's role-preservation setting is active so the SSO login does not downgrade them to Faculty.

- [ ] Have the CTLE Admin person log in via SSO — this creates their WordPress account with the Faculty role (or links to an existing account per the note above)
- [ ] Have the Developer Admin person log in via SSO — same
- [ ] Confirm which accounts, if any, still need elevating: `wp user list --fields=ID,user_login,user_email,roles`
- [ ] Log in to WP Admin via **MyKinsta auto-login** (Sites → [site] → Info → Log in to WP Admin)
- [ ] Elevate the CTLE Admin's account to the **CTLE Admin** role (or **Administrator** with appropriate capability restrictions — developer decision)
- [ ] Elevate the Developer Admin's account to **Administrator**
- [ ] Confirm WP Activity Log captured the Administrator login and the role changes (§5)
- [ ] Confirm the alert email was sent to CTLE Admin addresses
- [ ] Log out
- [ ] From now on, CTLE Admin and Developer Admin work is performed via SSO. MyKinsta auto-login is reserved for recovery and for work that cannot be done as an SSO user — its use should be infrequent enough that every alert email is worth reading.

---

## 15. Email Configuration

**Prerequisites — [DU IT]:**
- Provision the dedicated `ctle-noreply@dom.edu` shared mailbox in Microsoft 365 (separate from the human `ctle@dom.edu`; sender decided 2026-07-27)
- Confirm that the estimated volume (50–200 messages/day, occasional bursts for event reminders) is acceptable under Exchange Online sending limits
- Provide a Microsoft Graph API app registration (client ID + secret, `Mail.Send` application permission) for WP Mail SMTP. We are specifically **not** requesting SMTP AUTH: Microsoft disables SMTP AUTH basic authentication by default for existing tenants at the end of December 2026 — about four months post-launch (see `IT_REQUESTS.md` Request 2).
- Confirm SPF/DKIM/DMARC alignment for `dom.edu` covers the `ctle-noreply@dom.edu` mailbox

- [ ] In WP Admin, navigate to WP Mail SMTP → Settings
- [ ] Select mailer: **Microsoft 365 / Outlook** (sends via the Microsoft Graph API)
  - Enter the Entra app registration client ID and secret provided by DU IT; authorize the connection
  - Do **not** use the "Other SMTP" mailer / SMTP AUTH — basic auth for SMTP client submission is disabled by default for existing tenants at the end of December 2026 (see `IT_REQUESTS.md` Request 2)
- [ ] Set From Email: `ctle-noreply@dom.edu`
- [ ] Set From Name: `CTLE — Dominican University` (or DU brand-compliant name)
- [ ] Send a test email: WP Mail SMTP → Tools → Email Test → send to a DU test address and confirm delivery
- [ ] Verify the email shows the correct from address and passes spam checks (no SPF/DKIM failures)

---

## 16. Canvas Integration — Global-Nav Link + Entra SSO

> **LTI superseded 2026-07-28 (decision 10).** CTLE does **not** use LTI. Faculty launch from the existing **CTLE button in the Canvas global navigation**, retargeted to the site's **Entra SSO-initiation URL** (§13). Because Canvas is on the same Entra tenant, the click completes SSO silently and lands the user logged in. Access is gated by the Entra faculty group (§13, Option 1); the button's visibility is gated client-side on `declared_user_type=teacher` (set via the nightly SIS `users.csv`, read from `GET /api/v1/users/self/logins` — validated non-admin-readable 2026-07-28), which is cosmetic since Entra is the real gate. This is Canvas/DU-LT-side work; the WordPress side owes only the SSO-initiation URL (§13). LTI Tool + ceLTIc were installed, deactivated 07-28, and **deleted 2026-07-29** (§4, §5). **The original LTI 1.3 procedure is retained below, struck through, for the record only.**
>
> **Built 2026-07-29 — see `canvas/`.** `ctle-global-nav.js` implements both halves (faculty gate + retarget); `canvas/README.md` carries the install path, the rollout order, and the SIS `users.csv` `declared_user_type` specification. The SSO URL is one config constant and the `enabled` switch defaults to `false`, so the script can be staged in Canvas before IT-1 lands without altering today's button. Remaining: beta test against a teacher and a student account, the SIS column, then the real URL.

### ~~LTI 1.3 (withdrawn — historical)~~

**Prerequisites — [DU LT]:**
- Register the WordPress site as an LTI 1.3 tool in Canvas
- Provide: Canvas OIDC endpoint URL, Canvas JWKS endpoint URL, Canvas platform issuer
- Confirm the LTI launch payload includes: email, DU employee identifier (e.g., `lis_person_sourcedid`), and avatar URL (for Phase 2 — configure now to avoid later reconfiguration)
- Update the existing Canvas global-nav CTLE button URL to `https://ctle.dom.edu`

- [ ] In WP Admin, navigate to the **LTI Tool** plugin settings
- [ ] Enter Canvas platform details: OIDC endpoint, JWKS endpoint, platform issuer — provided by Learning Technologies
- [ ] Configure account linking: map the LTI `lis_person_sourcedid` (or the agreed DU employee identifier claim) to the same `sis_user_id` user meta field used by SSO (§13) — the linking key must be consistent between SSO and LTI
- [ ] Set default role for LTI-provisioned users: **Faculty**
- [ ] Test LTI launch:
  - In Canvas (using a test account with faculty role), navigate to the CTLE tool
  - Confirm successful LTI launch and WordPress login
  - Confirm the WordPress account created (or linked) has Faculty role
  - Confirm `sis_user_id` user meta is populated
  - Confirm display name and email are synced from the LTI payload
- [ ] Confirm that LTI login does not modify an already-elevated user's role (same role-preservation requirement as SSO — test with a previously elevated CTLE Admin account)

---

## Content & Features

## 17. Events Calendar Setup

- [ ] In WP Admin, navigate to Events → Settings
- [ ] General settings: set default timezone, currency, and time format to match DU preferences
- [ ] Configure the **Series** taxonomy: add initial series names provided by CTLE (e.g., Conversation Series, Faculty Seminar Series, New Faculty Orientation)
- [ ] Configure **Zoom link visibility toggle** on event edit screen — default: Private (DU sign-in required). Confirm this field appears per-event and that anonymous visitors cannot see the Zoom link unless the event is explicitly set to Public.
- [ ] Configure **capacity limits**: enable the capacity field on events; confirm that registration closes and a waitlist message appears when capacity is reached (waitlist promotion is manual at Phase 1 — note this for CTLE Admin documentation)
- [ ] Configure **event registration confirmation email**:
  - Trigger: user registers for an event
  - Content: event title, date/time, location, Zoom link (if public or user is authenticated), plus an `.ics` calendar attachment
  - From: `ctle@dom.edu` via WP Mail SMTP (§15)
- [ ] Configure **event reminder email**:
  - Trigger: 24 hours before event start time
  - Recipients: all registered attendees
  - Content: event reminder with Zoom link / location
- [ ] Configure **Panopto recording link** field on the event post type — this is a URL field added post-event when recording is available
- [ ] Implement **event display logic** (developer task):
  - Upcoming event: show Zoom link (respecting visibility toggle) and/or physical location
  - Past event with recording: embed Panopto thumbnail player; link to full recording
  - Past event without recording: show "Recording not available"
- [ ] Implement **Panopto iframe lazy loading**: Panopto embed iframes must not load until scrolled into view — use the `loading="lazy"` attribute or an Intersection Observer implementation. Verify that Panopto resources do not appear in the network waterfall on initial page load.
- [ ] Configure **Pinned event** field on events — CTLE Admin can mark one event as pinned for prominent home-page display (see §21)
- [ ] Create one test event; verify it appears on the events calendar, registration works, confirmation email with `.ics` is received, and the `.ics` opens correctly in Outlook

---

## 18. Discussion Forums Setup

- [ ] In WP Admin, navigate to wpForo → Settings → General
  - Set forum access: require login to view and post — anonymous visitors must not see forum content
  - Confirm display name source: wpForo must use the WordPress `display_name` field (synced from SSO/LTI on every login per §13) — not a separate forum-only profile name
- [ ] Create initial forum categories as defined by CTLE:
  - Category-based forums (general discussion topics): e.g., Teaching with AI, Assessment Strategies, New Faculty
  - Course-specific forums (organized by Canvas course topic): CTLE Admin creates and names these to match available Canvas courses
- [ ] Configure generic avatar placeholder for all users (Phase 1 — Canvas avatar sync is Phase 2)
- [ ] Leave wpForo reputation levels disabled (Phase 2 feature — configure when GamiPress is added)

### Forum Privacy Disclosure Implementation

All three disclosure mechanisms must be live before any faculty user accesses the forums.

- [ ] **First-visit acknowledgment modal:**
  - On a user's first visit to any forum page, a modal displays the forum privacy notice (CTLE-drafted language from §18 open question #13)
  - User must click to confirm before proceeding
  - Consent is stored in user meta; the modal reappears if the forum privacy policy is materially updated (use a version key in user meta to detect policy changes)
  - **[CTLE]** Provide the exact modal language (to be drafted in consultation with OPC)
- [ ] **Posting-time privacy reminder:**
  - A short note near the submit button on every reply/new-post form: "Forum posts are visible to all DU faculty and moderated by CTLE. [Forum Privacy Policy](link)"
  - Implemented as a wpForo template customization or hook
  - **[CTLE]** Provide the reminder text
- [ ] **Persistent footer link:**
  - Every forum category, topic, and thread page displays a footer link to the full forum privacy policy page
  - Implemented as a wpForo template customization
- [ ] Test the full disclosure flow: log in as a test Faculty user, navigate to the forums, confirm the modal appears, accept it, navigate to the reply form, confirm the posting-time reminder appears, and confirm the footer link is present on all forum pages
- [ ] Confirm that the modal does not reappear on subsequent visits unless the policy version key changes

---

## 19. Search Setup

- [ ] In WP Admin, navigate to Relevanssi → Searching
  - Enable Relevanssi (replaces WordPress core search)
- [ ] Configure indexed content:
  - Enable: posts (blog/news), pages, Events (The Events Calendar custom post type), course catalog entries, resources (custom post type or media), and wpForo forum topics/replies
  - Disable indexing of any private or internal post types not intended for search
- [ ] Enable **English stemming** in Relevanssi settings
- [ ] Configure **relevance weighting**: set title field weight higher than body (Relevanssi default favors titles — confirm this is active)
- [ ] Enable **search query logging** (including zero-result queries): Relevanssi → Logging → enable logging
- [ ] Configure **access-aware results**:
  - Anonymous visitors: only public content appears (no forum results)
  - Authenticated faculty: public content plus forum topics from forums they have access to
  - Verify: log out and search for a forum term — confirm no forum results appear. Log in as Faculty — confirm forum results appear.
- [ ] Build the **search results page**:
  - Shows: title, short excerpt, content-type label, link to item
  - Content-type filters: Courses, Events, Blog/News, Resources, Pages, Forums (authenticated users only)
  - Zero-results fallback: "No results found" message with links to course catalog, event calendar, resource library, and a CTLE contact link
- [ ] Confirm search box is present in the site header on every page (both logged-in and anonymous views)
- [ ] Run Relevanssi index rebuild: Relevanssi → Indexing → Build the index — confirm completion

---

## 20. Course Catalog Setup

**Decision required — [CTLE + Developer]:** Resolve REQUIREMENTS.md §18 open question A: custom post type (CPT) vs. static pages. A CPT is strongly recommended for maintainability as the catalog grows.

- [ ] If using a **Custom Post Type**:
  - Register a `ctle_course` CPT (via a custom plugin, not `functions.php` in a child theme)
  - Fields per entry: course title, description, thumbnail, Canvas enrollment URL
  - Set the CPT to public (visible on front end)
  - Configure Relevanssi to index this CPT (§19)
- [ ] If using **static pages**: create a parent "Course Catalog" page; each course is a child page — simpler initially but harder to manage at scale
- [ ] Create 2–3 initial test course catalog entries using sample content from CTLE's current Canvas courses
- [ ] Verify the course catalog is publicly accessible without login
- [ ] Verify each entry links correctly to the Canvas enrollment URL
- [ ] **[CTLE]** Migrate or recreate all existing CTLE course catalog entries (content migration from the current Canvas-based CTLE site — see §18 open question #3)

---

## 21. Home Page Setup

- [ ] Build or configure the home page with the following sections (use the chosen page builder — Beaver Builder or Gutenberg blocks):
  - **Upcoming events:** the next 3–5 upcoming events (count configurable by CTLE Admin), each showing: title, date, series, and registration CTA
  - **Pinned event:** a prominent featured slot for the one event marked "Pinned" by CTLE Admin — displays even if the event is past (to highlight a notable recording)
  - **Course highlights:** optional — a selection of featured or new courses from the course catalog (can be a static curated list or dynamic query)
  - **Announcements / News:** the most recent blog post or CTLE announcement
  - **Quick links:** styled link buttons to Course Catalog, Event Calendar, Discussion Forums, and Resources
- [ ] Confirm all home page sections are visible and function correctly for anonymous visitors
- [ ] Confirm that the Zoom link on pinned or listed events respects the per-event visibility toggle (private Zoom links hidden from anonymous visitors)

---

## 22. Privacy & Compliance

- [x] Enable **WordPress Export Personal Data** tool: Settings → Privacy — confirm the tool is accessible to CTLE Admin ✅ 2026-07-29
- [x] Enable **WordPress Erase Personal Data** tool: same location — confirm the tool is accessible to CTLE Admin ✅ 2026-07-29
- [x] Set the Privacy Policy page in Settings → Privacy → select or create the privacy policy page ✅ 2026-07-29 — the WordPress-generated draft is designated; **CTLE writes the content** (see the `[CTLE]` item below)
- [ ] **[CTLE]** Draft the site privacy policy page using the WordPress built-in template as a starting point. Required content (per REQUIREMENTS.md §14):
  1. What personal data is collected
  2. How it is used
  3. All third-party recipients (Kinsta, Microsoft 365, Panopto, and any analytics service)
  4. Retention periods
  5. Process for faculty data access, correction, and erasure requests
  6. Privacy contact point
- [ ] **[CTLE + DU Legal/OPC]** OPC and/or DU legal review and approve the privacy policy draft before publication
- [ ] Publish the privacy policy page; confirm it is linked from the site footer on every page
- [ ] Confirm WP Activity Log is capturing all CTLE Admin moderation actions (edits, deletions, pinning, locking of forum content): log a test moderation action and verify it appears in the WP Activity Log
- [ ] Confirm Kinsta DPA has been executed (§1 — prerequisite) — verify with DU IT that this is complete before any faculty user data is collected

---

## 23. Pre-Launch Verification

Complete all items in this section **on Live**, and re-verify on Live.

> ⚠️ **Do not stage-then-push this section.** It previously read "complete on staging first, then push to production." That is reversed as of 2026-07-29: Live is the build environment (CD-2), so pre-launch verification must exercise the environment that will actually serve faculty — the hardened one. Verifying on staging proves nothing about Live, and pushing staging's result *to* Live would undo the hardening being verified. See §24.

### Security

- [ ] Administrator access model is in place per §7: MyKinsta auto-login verified working, admin-login / role-change alerts confirmed (via the `ctle-admin-alerts.php` mu-plugin — requires WP Mail SMTP live to deliver), obfuscated login URL in use
- [ ] Recovery path verified: at least two people hold working SSH keys (**Amanda's is the outstanding one** — ME-6), and the **full** procedure has been walked on staging — including the step added 2026-07-29: `ctle-hardening.php` must be moved aside before a WP-CLI password reset grants a login, because password authentication is removed. Testing only `wp user create` no longer exercises the real path.
- [ ] The temporary provisioning account from §3 has been deleted, and `wp user list` shows no password-authenticated Administrator accounts remaining (§6)
- [ ] MyKinsta company user list reviewed; access to the `ctle@dom.edu` shared mailbox is documented and minimal (§7)
- [ ] SSO is the primary login path; local password login is not promoted or accessible at the standard `/wp-login.php` URL
- [ ] WP Activity Log is capturing logins, role changes, content edits, and plugin activations
- [ ] Kinsta CDN, WAF, and DDoS protection are active

### Backup

- [ ] Kinsta daily automated backups are confirmed active and a manual backup exists
- [ ] CTLE off-site backup script has run successfully at least once and the archive is confirmed complete
- [ ] Backup failure alerting has been tested (manually trigger a failure and confirm the alert fires)
- [ ] Server-side Kinsta configuration is documented separately (Nginx rules, redirect rules, IP Deny list, PHP settings)

### Performance

- [ ] Uncached page load time < 3 seconds: test the home page, course catalog, and event calendar from an external connection with browser cache cleared — use WebPageTest or Chrome Lighthouse
- [ ] Cached page load time < 2 seconds: reload each of the above pages and confirm `X-Kinsta-Cache: HIT` header
- [ ] Authenticated users bypass page cache: confirm `X-Kinsta-Cache: BYPASS` when logged in as Faculty
- [ ] Panopto embeds are lazy-loaded: verify Panopto iframe and player scripts do not appear in the network waterfall on initial page load for past-event pages with recordings
- [ ] Plugin asset audit: use a tool (e.g., Query Monitor) to confirm that plugin CSS and JS files are loaded only on pages where they are used — flag any plugins loading assets globally that should be conditional

### Accessibility

- [ ] Run automated accessibility scan (Axe DevTools, WAVE, or Lighthouse accessibility audit) on: home page, event listing page, individual event page, course catalog page, forum page, search results page, and blog post page
- [ ] Manually test keyboard navigation on the same pages (Tab, Shift+Tab, Enter, Space, arrow keys)
- [ ] Confirm all images have alt text (or empty alt for decorative images)
- [ ] Confirm color contrast ratios meet WCAG 2.1 Level AA (minimum 4.5:1 for normal text, 3:1 for large text)
- [ ] **[DU Marketing]** Confirm theme colors and typography comply with DU brand guidelines

### Functionality

- [ ] **SSO login:** test with at least one DU faculty account and one CTLE Admin account
- [ ] **LTI launch:** test launch from Canvas with a faculty test account
- [ ] **Event registration:** register for a test event; confirm confirmation email with `.ics` arrives; open `.ics` in Outlook and confirm event details
- [ ] **Event reminder:** manually trigger or simulate the 24-hour reminder email; confirm delivery
- [ ] **Forum access:** confirm anonymous visitors cannot see forum content; confirm logged-in Faculty can post; confirm CTLE Admin can moderate
- [ ] **Forum privacy disclosures:** first-visit modal, posting-time reminder, and footer link all appear correctly
- [ ] **Search:** search for terms that match course catalog, event, blog, and resource content; confirm relevant results appear; confirm forum results appear only when logged in; confirm zero-results fallback displays correctly
- [ ] **Course catalog:** confirm all entries are publicly accessible and Canvas enrollment links work
- [ ] **Email sending:** send a test email via WP Mail SMTP; confirm delivery and correct from address
- [ ] **Backup restore test:** restore from a Kinsta backup to the staging environment to confirm the restore process works

### Content & Compliance

- [ ] 🚩 **Settings → Reading → UNCHECK "Discourage search engines from indexing this site."** This was deliberately enabled during the build (§4). Leaving it on means the launched site is invisible to search engines. Verify from outside afterward — `curl -sSI https://ctle.dom.edu | grep -i x-robots-tag` should return nothing, and the page source should contain no `<meta name="robots" ... noindex>`.
- [ ] 🚩 **[CTLE — CD-14]** No WordPress sample content remains: the "Hello World" post, "Sample Page," and the default comment are deleted or replaced. This is a hard gate precisely because it is nobody's day job — it was re-assigned from infrastructure to CTLE on 2026-07-29 on the principle that posts and pages are CTLE's domain, which means it can only be caught here.
  ```bash
  wp post list --post_type=any --post_status=any --fields=ID,post_type,post_title,post_status
  wp comment list --fields=comment_ID,comment_author,comment_approved
  ```
- [ ] Privacy policy page is published and linked from the site footer
- [ ] Terms of service / accessibility statement pages are present if required by DU policy
- [ ] Kinsta DPA is executed
- [ ] SOC 2 Type II attestation letter is on file with DU IT
- [ ] **[CTLE + OPC]** Forum privacy disclosure language is finalized and approved
- [ ] CTLE Admin has been trained on: creating and publishing events, managing the course catalog, moderating forums, managing the waitlist, and handling data export/erasure requests

---

## 24. Environment Push Protocol

**Live is the source of truth for this site.** CD-2 put the build on Live because SSO is hostname-bound; the consequence, which was never written down until now, is that **Live accumulated state that exists nowhere else** — the hardening mu-plugins, the rotated login path, the deleted `topsecretuser`, the `sis_user_id` stamping, PHP 8.4, and the redirect-to-primary rule. Staging is a stale fork. A staging→live push is therefore a **destructive** operation on this site, not a routine deployment, and the ordinary "build on staging, push to live" habit is exactly backwards here.

### What a Kinsta push actually moves

Verified against Kinsta's [Push Environments](https://kinsta.com/docs/wordpress-hosting/wordpress-push-environments/) documentation, 2026-07-29.

| Category | Selectable? | Behavior |
|---|---|---|
| **Files** | Yes — all, specific files/folders, or none | Source overwrites destination |
| **Database** | Yes — all tables, specific tables, or none | Source overwrites destination; optional Search & Replace rewrites the domain to the destination's |
| **Environment settings** — redirects, geolocation, PHP version, Nginx configuration | **No — always pushed** | Source **overwrites** destination, *even on a files-only or database-only push* |

> ⚠️ **The third row is the trap.** There is no push configuration that leaves Live's environment settings alone. "Push files only" is the standard safety advice and it does **not** protect §9's redirect-to-primary or §10's PHP 8.4 — both are environment settings, and both get overwritten by staging's values on every push regardless of what you select.
>
> The fix is not to avoid pushing. It is to **keep staging's environment settings identical to Live's**, so the unavoidable overwrite writes the same values it replaces. Treat any divergence in PHP version or redirect rules between the two environments as a defect to be corrected on staging immediately.

**Not transferred** (these survive a push, and some must be re-applied manually):

- **.htpasswd password protection** — does not transfer in either direction. Staging's Basic Auth will not land on Live; equally, re-enable it on staging after any push *to* staging (§6).
- **Bot protection level** — not transferred; set per environment.
- **Custom Nginx configuration** — the *destination's* custom config is retained. (Distinct from the standard Nginx configuration in the table above, which is pushed. Do not conflate them.)
- **Domains and SSL certificates** — bound to the environment, not the content.
- **SSH keys** — but the **host key fingerprint is regenerated** by a push, which will trip a `known_hosts` mismatch on next connect. Expected, not a compromise.

**Kinsta takes an automatic backup of the target before every push.** That is the undo. It is not a substitute for the checklist below, because a rollback also discards anything created on Live between the push and the rollback.

### Before any push to Live

- [ ] **Confirm the direction is deliberate.** Content and theme work flows staging→live. Everything in §4–§16 was built on Live and flows live→staging. If a single push would carry both, it is two pushes.
- [ ] Take a **manual** backup of Live (§12) in addition to Kinsta's automatic one, named for the push
- [ ] Confirm staging's **PHP version matches Live's** (8.4) — it is pushed whether or not you select it
- [ ] Confirm staging's **redirect rules match Live's** (redirect all traffic to the primary domain, §9) — same reason
- [ ] If pushing the database, **tick Run Search & Replace**. Without it, Live's `siteurl`/`home` are overwritten with staging's hostname and the site breaks.
- [ ] If pushing files, confirm both mu-plugins are present **on staging** — see the asymmetry warning below

### After any push to Live — re-verify, do not assume

The push is not finished until these pass. Each corresponds to state that lives only on Live and that a push can silently revert.

- [ ] PHP is **8.4** and limits are intact (§10) — read from Site Health → Info → Server, **not** `wp eval`
- [ ] All hostnames still fold to `https://ctle.dom.edu` (§9 verification block)
- [ ] `wp plugin list --status=must-use` shows **both** `ctle-admin-alerts.php` and `ctle-hardening.php`
- [ ] Password authentication is still refused — positive control per §6, not merely "the file is present"
- [ ] MyKinsta auto-login still works. **This is the gate**; it is the only interactive path into WP Admin
- [ ] The custom login path is still the rotated one, and `/wp-login.php` still 404s (§6)
- [ ] Users list contains **only** the expected admins — specifically, confirm `topsecretuser` has **not** returned. A database push from a staging copy predating 2026-07-27 will resurrect it, and nothing else in this checklist would catch that.
- [ ] `sis_user_id` is still stamped on each admin account (ME-10) — if lost, first SSO login creates duplicate accounts
- [ ] Search engines still discouraged pre-launch (`blog_public=0`, §4)
- [ ] The four deleted plugins (§4) have not reappeared from staging's filesystem
- [ ] Once SSO is live: the entire §13 configuration, which exists only on Live by design

> ⚠️ **mu-plugin asymmetry — unresolved.** As of 2026-07-29, `ctle-hardening.php` was copied to staging for testing but **`ctle-admin-alerts.php` was deployed only to Live.** Whether a Kinsta file push *deletes* destination files absent from the source is **not documented** — confirm with Kinsta support before the first file push. Until that answer is in hand, the safe move is to keep both mu-plugins present and identical on both environments, which makes the question moot.

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 0.8.0 | 2026-07-29 | sendres | **Added §24 Environment Push Protocol** and reversed two stale build-on-staging instructions that contradicted CD-2 and would have destroyed the Live build if followed: §5 ("use the staging environment for initial installation, then push to production") and §23 ("complete all items on the staging environment first, then push to production"). Both now direct the work to Live. §24 records the finding that drove this: Kinsta pushes **environment settings — redirects, geolocation, PHP version, Nginx configuration — unconditionally, even on a files-only or database-only push**, so "push files only" does *not* protect §9's redirect-to-primary or §10's PHP 8.4. Mitigation is to hold staging's environment settings identical to Live's so the forced overwrite is a no-op. Added pre-push and post-push checklists keyed to the state that exists only on Live (mu-plugins, rotated login path, deleted `topsecretuser`, `sis_user_id` stamping), and flagged one unresolved question — whether a Kinsta file push deletes destination files absent from the source, which determines whether a push would silently remove `ctle-admin-alerts.php` from Live. |
| 0.1.0 | 2026-05-27 | sendres | Initial version. |
| 0.2.0 | 2026-05-28 | sendres | Reordered sections; updated all cross-references; changed break-glass [DU IT] tags to [DU LT]. |
| 0.3.0 | 2026-05-29 | sendres | Reordered §4–§12 to prioritize WordPress Configuration; added Infrastructure group; updated all cross-references. |
| 0.4.0 | 2026-07-24 | sendres | Withdrew the break-glass recovery account in favor of MyKinsta WP Admin auto-login. Rewrote §7 as Administrator Access & Recovery; struck the Two Factor / WP 2FA plugin (§5); retargeted WP Activity Log alerting to all Administrator logins; added a password-login elimination subsection to §6; rewrote §14 to elevate from an auto-login session; expanded §23 security verification. Withdrew all DU LT break-glass coordination. |
| 0.4.1 | 2026-07-24 | sendres | Corrected the `topsecretuser` deletion gate in §3 and §6: auto-login plus SSH/WP-CLI recovery verified, rather than waiting on SSO. |
| 0.5.1 | 2026-07-24 | sendres | Recorded the §9 redirect verification and the accepted `https://*.kinsta.cloud` gap. Added the build-time search-engine discouragement to §4, paired with a matching launch gate in §23. |
| 0.5.0 | 2026-07-24 | sendres | §9 executed and rewritten against verified state: added the CAA pre-check, primary-domain cutover, Force HTTPS option guidance and its ordering trap, and a verification block. Corrected the premature site-URL checkbox in §4. Marked the §12 baseline backup and the §11 anonymous edge-cache check complete. |
| 0.6.0 | 2026-07-24 | sendres | Corrected the LTI plugin naming in §5 and §16: WordPress is the LTI **tool** launched from Canvas, so the software is the **LTI Tool** plugin (ceLTIc project) plus its **ceLTIc LTI Library** dependency — not "LTI Platform for WordPress," which is the reverse integration. Amended §15 to send mail via the Microsoft Graph API only, dropping SMTP AUTH as a co-equal option ahead of Microsoft's end-of-December-2026 basic-auth retirement. Both align with `IT_REQUESTS.md` Requests 3 and 2. |
| 0.6.1 | 2026-07-24 | sendres | §5: added an explicit warning to install the **LTI Tool** plugin and *not* the near-identically named ceLTIc **LTI Platform** plugin (the reverse integration), which sits beside it in wordpress.org search results. |
| 0.6.2 | 2026-07-27 | sendres | Post-IT-meeting: §8 documented Kinsta's one-SSH-user-per-environment model (multiple keys via MyKinsta members; SFTP users are not a WP-CLI path; ed25519 works); §13 recorded SSO Option 1 (SIS-faculty group gates the app; admins via console) and a build-on-Live note (CD-2); §15 set the sender to the dedicated `ctle-noreply@dom.edu` mailbox. |
| 0.6.3 | 2026-07-27 | sendres | Renamed the SSO/LTI account-linking user-meta key `du_employee_id` → `sis_user_id` (§13, §16). Added a §13 step to reconcile the multi-path admin accounts before first SSO by stamping `sis_user_id` (matching Entra's `employeeId`) and verifying role preservation. |
| 0.6.4 | 2026-07-27 | sendres | §6: marked the `topsecretuser` deletion done — the last password-authenticated admin is gone; only passwordless MyKinsta auto-login admins remain. |
| 0.7.0 | 2026-07-28 | sendres | Build session marked complete on Live: §5 plugin installs (WP Activity Log, Query Monitor active; WP Mail SMTP + Relevanssi staged) + admin-alert mu-plugin note (WP Activity Log notifications are Premium-only, so alerts live in `mu-plugins/ctle-admin-alerts.php`); §6 open-registration off + XML-RPC disabled via `mu-plugins/ctle-hardening.php` (verified 403 at Nginx, X-Pingback removed); §10 PHP 8.4 + limits/cron verified + `DISABLE_WP_CRON`; §11 CDN Polish (Lossless) + bandwidth alerts + authenticated BYPASS; §12 daily backups + point-in-time restore confirmed (off-site 30-day deferred to ME-11). |
| 0.7.1 | 2026-07-28 | sendres | **LTI superseded (decision 10):** §16 rewritten to the Canvas global-nav link + Entra SSO (original LTI 1.3 steps retained struck-through as history); §5 LTI Tool + ceLTIc marked deactivated/superseded. Faculty launch via the retargeted Canvas nav button → SSO-initiation URL; button visibility gated on `declared_user_type=teacher` (SIS `users.csv`, read from `users/self/logins`); the Entra faculty group is the access gate. Cross-doc: REQUIREMENTS §6 (0.2.6), IT_REQUESTS Request 3 withdrawn, IMPLEMENTATION_PHASES §6 (0.2.3), STATUS 0.1.12. |
| 0.7.6 | 2026-07-29 | sendres | Part A executed — checkbox and consistency sweep. Ticked what was actually done: §4 plugin deletions + theme updates (none deleted, CD-1) and the stale Discourage-indexing box (done 07-24, never ticked); §5 wpForo installed-inactive; §6 staging password protection, plus a note that the login path was **rotated** after exposure in a diagnostic session; §7 MyKinsta hygiene ×3 and the widened alert recipients; §8 Steven's SSH/SFTP/WP-CLI (Amanda's key still outstanding) with a recovery-prerequisite warning added at the top; §22 privacy tooling. **Corrected two contradictions the hardening change created:** §7 no longer tells users to obtain a password via "Lost your password?" (reset is disabled, so that would mint a credential authenticating nothing), and §23's recovery gate now requires walking the *full* sequence including moving the hardening file aside — testing `wp user create` alone no longer exercises the real path. |
| 0.7.5 | 2026-07-29 | sendres | **§6 brute-force finding.** Kinsta support confirmed the automatic IP ban watches `/wp-login.php` only, so the WPS Hide Login custom path had **no rate limiting at all** — the assumption that Kinsta's auto-detection covered it was wrong (auto-login detection ≠ brute-force ban; different subsystems). Compensating control: `ctle-hardening.php` v1.1.0 removes password authentication outright (username/password, email/password, and application-password authenticators; application passwords hidden; password reset off), making §6's "no password login" goal an enforced property rather than an assumption. Rejected alternative recorded (Limit Login Attempts Reloaded — right answer for a site that needs password login; this one does not). §7 recovery procedure amended: a WP-CLI password reset no longer grants login until the hardening file is moved aside — one extra SSH step, documented in three places. §8 unchanged. |
| 0.7.4 | 2026-07-29 | sendres | §4 cleanup split by ownership. Plugins: added LTI Tool + ceLTIc to the deletion list (reversing decision 10's "kept installed for optionality" — withdrawn integration, free to reinstall, no upside to unused code on disk), Relevanssi explicitly retained; §5 LTI entry updated to deleted-not-just-deactivated with the uninstall-routine note. Content: the sample post/page/comment re-assigned to **[CTLE]** as CD-14, with a new 🚩 hard launch gate in §23 — it is nobody's day job now, so §23 is the only place it can be caught. |
| 0.7.3 | 2026-07-29 | sendres | §16: recorded the built Canvas artifacts (`canvas/ctle-global-nav.js` + `canvas/README.md` — faculty gate, retarget behind one constant, `enabled: false` master switch, SIS `declared_user_type` spec). §7: alert-recipient widening scoped as ME-12 and explicitly excluding `ctle@dom.edu` (it carries the MyKinsta 2FA codes), with an `is_email` guard on the list. Execution runbook for the remaining §4/§6/§7/§22 self-serve items: `SELF_SERVE_CHECKLIST.md`. |
| 0.7.2 | 2026-07-28 | sendres | Audit sync: §23 verification names the actual alert mechanism (the `ctle-admin-alerts.php` mu-plugin, needs WP Mail SMTP live) rather than WP Activity Log, whose notifications are Premium. |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

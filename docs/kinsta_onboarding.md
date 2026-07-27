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
- [ ] Update the default theme(s) and delete any unused themes (keep only the active theme and one fallback)
- [ ] Delete Hello Dolly and Akismet plugins (will be replaced by the Phase 1 stack in §5)
- [ ] Delete the default sample content: "Hello World" post, "Sample Page" page
- [x] Settings → General:
  - [x] Site Title: `Dominican University CTLE`
  - [x] Tagline: leave blank for now
  - [x] WordPress Address and Site Address: `https://ctle.dom.edu`
  > This was marked complete prematurely. As of 2026-07-24 it is genuinely done — performed by the primary-domain search and replace in §9, and verified. Kinsta serving the site at a custom domain and WordPress *knowing* its own address are two independent settings; only the first had happened.
  - [x] Administration Email: the DU shared mailbox (e.g., `ctle@dom.edu` — see §15) or a CTLE Admin DU email
  - [x] Timezone: `America/Chicago`
  - [x] Date and time format: Selected `F j, Y` date (e.g., `May 29, 2026`) and `g:i a` time (`8:00 pm`) as initial settings; this can be changed later
- [ ] Settings → Reading → check **Discourage search engines from indexing this site**
> Set 2026-07-24. As of the DNS cutover (§9) the site is publicly reachable and fully indexable at `ctle.dom.edu` — verified: no `x-robots-tag` header, no meta robots tag, and `robots.txt` advertises the sitemap. Without this, a bare install with placeholder content can be indexed under the DU brand.
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

Install plugins in the order listed. Activate and do a basic sanity check after each one before proceeding. Use the [staging environment](https://kinsta.com/docs/wordpress-hosting/staging-environment/) for initial installation, then [push to production](https://kinsta.com/docs/wordpress-hosting/wordpress-push-environments/) after all plugins are validated.

### Security & Admin Plugins (install first)

- [ ] **WPS Hide Login** — changes the default `wp-login.php` path
  - After activation, set a custom login path in Settings → WPS Hide Login
  - Record the new login path; it is also the URL the SSO sign-in button must point to (§13)
  - Verify: navigate to `/wp-login.php` and confirm it returns 404 or redirects; navigate to the new custom path and confirm the login form appears
  - **Compatible with MyKinsta auto-login.** Kinsta detects a customized login URL automatically; allow up to one minute after changing it before the auto-login button works again — [WP Admin](https://kinsta.com/docs/wordpress-hosting/site-management/wordpress-wp-admin/)
- [ ] **WP Activity Log** — audit logging and Administrator login alerting
  - After activation, run the setup wizard
  - Configure notifications: WP Activity Log → Notifications → New Notification → trigger on any successful login by any user holding the **Administrator** role → send email to all CTLE Admin addresses
  - Add a second notification triggered on **any user role change**
  - Verify: log in via MyKinsta auto-login and confirm an alert email is received
- ~~[ ] **Two Factor** (or **WP 2FA**) — TOTP 2FA for the break-glass account (see §7 for configuration)~~
> **Not needed** as of 2026-07-24. This plugin existed solely to protect the break-glass account's password login (§7, withdrawn). Under the current model no privileged account has a password at all: faculty authenticate through Entra (which enforces DU's own MFA), and administrators enter through MyKinsta auto-login (which enforces MyKinsta 2FA per §2). There is no local password login left for a WordPress 2FA plugin to protect. Note that `REQUIREMENTS.md` §5 and `IMPLEMENTATION_PHASES.md` §17 still list this plugin as a Phase 1 requirement — both need the same amendment.

### Communication Plugin

- [ ] **WP Mail SMTP** — configure after DU IT provisions the M365 mailbox (see §15)
  - Install now; configure SMTP settings in §15

### Events Plugin

- [ ] **The Events Calendar Pro** + **Event Tickets**
  - Enter license key
  - Run setup wizard; configure default currency, date/time formats, and map provider if needed
  - Verify a test event can be created and displayed

### Forum Plugin

- [ ] **wpForo** (free core from WordPress.org)
  - Run setup wizard
  - Leave reputation features disabled for now (Phase 2)
  - Forum access configuration in §18

### LTI Plugin

- [ ] **LTI Tool** (ceLTIc project) — plus its required **ceLTIc LTI Library** dependency
  - WordPress is the LTI *tool*, launched from Canvas (the platform). Install and activate both; full configuration in §16.
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

- [ ] **Do not** use `admin` as the WordPress admin username — delete or rename the default admin user if it was created with that username (Users → create a new admin user with a non-obvious username, log in as that user, then delete the `admin` user)
- [ ] Disable XML-RPC if no plugin requires it — add to a custom plugin or `functions.php`:
  ```php
  add_filter('xmlrpc_enabled', '__return_false');
  ```
  Note: Kinsta also blocks XML-RPC attacks at the Nginx level, but disabling it in WordPress adds defense in depth. — [Kinsta Infrastructure & Security](https://kinsta.com/docs/wordpress-hosting/wordpress-getting-started/wordpress-infrastructure/)
- [ ] Verify Kinsta's built-in brute-force protection is active (automatic IP ban after > 6 failed logins/minute) — no configuration needed, but confirm with Kinsta support that this protection still applies after the login URL is changed in §5 (WPS Hide Login) — [Bot Protection](https://kinsta.com/docs/wordpress-hosting/mykinsta-tools/wordpress-tools-bot-protection/)
- [ ] In MyKinsta, navigate to Sites → [site] → Security → IP Deny — add any known malicious IP ranges if applicable — [Block IP Addresses](https://kinsta.com/docs/wordpress-hosting/site-management/block-ip-address/)

### Eliminate password-based login paths

With the break-glass account withdrawn (§7), the goal is that **no account on the site can be logged into with a password**. Faculty enter via Entra SSO, administrators via MyKinsta auto-login. Every remaining password login is an attack surface with no legitimate user behind it.

- [ ] **Delete the temporary provisioning account from §3** (`topsecretuser`). This is a password-authenticated Administrator created by Kinsta at install and is now the only such account on the site. **Gate the deletion on two things, both of which can be done the same day: MyKinsta auto-login verified working (§7), and SSH + WP-CLI recovery verified working (§8).** Do *not* wait for SSO (§13) — that is weeks out, and once the site is publicly reachable at `ctle.dom.edu` (§9) this account is a password-authenticated Administrator sitting on a public URL with no legitimate user behind it. Notify the Director and Developer before deleting, in case either has been using it.
  ```bash
  wp user list --role=administrator --fields=ID,user_login,user_email
  wp user delete <id> --reassign=<keep-user-id>
  ```
- [ ] Disable open user registration: Settings → General → uncheck **Anyone can register**. All provisioning happens through SSO (§13) and LTI (§16).
- [ ] After SSO is live, audit for any remaining password-capable accounts: `wp user list --fields=ID,user_login,user_email,roles` — every account should trace to either an SSO/LTI-provisioned user or a MyKinsta auto-login user
- [ ] Password-protect the staging environment: MyKinsta → Sites → [site] → staging → Tools → Password Protection. Staging carries the same code and often the same data as production but gets none of the attention — do not leave it publicly reachable.
- [ ] Confirm the custom login path from §5 is not leaked in any published page, sitemap, or the repository

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
- If a user ever needs a conventional password login, they must obtain one through the standard "Lost your password?" reset process since auto-login does not create one.

### What this changes about the security model

Administrator access to WordPress is now gated entirely by MyKinsta account security. Two consequences must be actively managed rather than assumed.

- [ ] **The shared mailbox is now effectively an admin credential.** Per §2, two-factor codes for the Company Owner (`ctle@dom.edu`) are delivered to that shared mailbox — so anyone who can read CTLE mail can reach WordPress Administrator. Decide and document who has access to `ctle@dom.edu`, and keep that list at least as short as the break-glass vault list would have been.
- [ ] Prefer per-person **Company Developer** accounts for routine work — their 2FA codes go to individual DU addresses (§2) rather than the shared mailbox. Reserve the Company Owner account for billing and ownership tasks.
- [ ] Review MyKinsta company users quarterly and remove anyone no longer on the project — removing a MyKinsta user removes their path into WP Admin
- [ ] After each new person's first auto-login, check Users in WP Admin and confirm the auto-provisioned account is expected and that no unexpected Administrator accounts exist

### Recovery path if MyKinsta is unavailable

MyKinsta account standing is now a single point of failure for administrator access: a billing lapse, account suspension, or dashboard outage removes the auto-login path. SSH is the fallback, and it is the real replacement for the break-glass account.

- [ ] Confirm the developer's SSH key is on file and working **before launch** (§8). With SSH, administrator access can always be restored through WP-CLI regardless of the MyKinsta dashboard's state:
  ```bash
  wp user create <username> <email> --role=administrator
  # or reset an existing account
  wp user update <user> --user_pass="$(openssl rand -base64 24)"
  ```
- [ ] Ensure **at least two people** hold working SSH keys, so recovery never depends on one person's laptop being available
- [ ] Confirm the billing contact (§1) is a monitored DU address and that the annual renewal cannot lapse silently — a suspended Kinsta account takes the dashboard *and* SSH with it, which is the one scenario neither path covers

### Administrator login alerting

The audit requirement from the original break-glass design still applies; only the trigger changes. Administrator logins should be rare and deliberate once faculty are on SSO, so alerting on all of them stays low-noise.

- [ ] Configure WP Activity Log (§5) to email all CTLE Admins on **any successful login by any user holding the Administrator role**
- [ ] Configure a second alert on **any user role change**
- [ ] Verify: use MyKinsta auto-login and confirm an alert email is received

---

## 8. Developer Access

- [ ] In MyKinsta, navigate to Sites → [site] → Info → SFTP/SSH to find connection details — [Connect via SSH](https://kinsta.com/docs/wordpress-hosting/connect-to-ssh/) · [Connect via SFTP](https://kinsta.com/docs/wordpress-hosting/connecting-with-sftp/)
- [ ] Generate a personal SSH key pair if you do not already have one: `ssh-keygen -t ed25519 -C "dev@example.com"`
- [ ] Add your public key to MyKinsta: User Settings → SSH Keys → Add SSH Key — [SSH Key Authentication](https://kinsta.com/docs/wordpress-hosting/connect-to-ssh/)
  - **One SSH user per environment.** Kinsta does not allow additional SSH users — but each MyKinsta company member adds their **own** key here, and all authorized keys connect as that single environment user. The two-person recovery requirement (§7) is therefore satisfied by two MyKinsta members each holding a key, not by two SSH accounts. Kinsta "additional users" are SFTP-only (no shell, no WP-CLI) and are **not** a recovery path. `ssh-keygen -t ed25519` works even though Kinsta's docs show `-t rsa`. (Confirmed on Staging + Live, 2026-07-27.)
- [ ] Test SSH access: `ssh [user]@[host] -p [port]`
- [ ] Test SFTP access using your preferred client (e.g., Transmit, FileZilla, Cyberduck) with key-based authentication
- [ ] Test WP-CLI via SSH: `wp --info` — confirm WP-CLI v2 is available — [WP-CLI](https://kinsta.com/docs/wordpress-hosting/site-management/wordpress-wp-cli/)
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

- [ ] In MyKinsta, navigate to Sites → [site] → Info → PHP engine — [PHP Configuration](https://kinsta.com/docs/wordpress-hosting/php/)
  - Set PHP version to **8.2** (or the current WordPress-recommended version ≥ 8.1)
- [ ] Confirm PHP memory limit is **256 MB** (Kinsta default — meets the ≥ 256 MB requirement) — [PHP Performance](https://kinsta.com/docs/wordpress-hosting/php/wordpress-php-performance/)
- [ ] Confirm `max_execution_time` is **300 seconds** (Kinsta default — meets the ≥ 120 s requirement)
- [ ] Confirm `upload_max_filesize` is **128 MB** (Kinsta default — meets the ≥ 64 MB requirement)
- [ ] Verify server-level cron is available: connect via SSH and run `crontab -l` — if it returns empty or your entries, cron access is confirmed. Minimum interval is 5 minutes per Kinsta's documentation. — [Cron Jobs](https://kinsta.com/docs/wordpress-hosting/site-management/cron-jobs/)
- [ ] Note: any custom `php.ini` directives beyond the self-service dashboard options require a Kinsta support request — document any such customizations separately (see backup note in §12) — [Configuration Changes](https://kinsta.com/docs/wordpress-hosting/site-management/configuration-changes/)

---

## 11. CDN & Caching

- [ ] In MyKinsta, navigate to Sites → [site] → Kinsta CDN → Enable — [Kinsta CDN](https://kinsta.com/docs/wordpress-hosting/wordpress-cdn/kinsta-cdn/)
- [ ] Enable **Cloudflare Polish** (WebP image optimization) in the CDN settings — this replaces the need for a server-side image optimization plugin — [Image Optimization](https://kinsta.com/docs/image-optimization-for-wordpress/)
  - Confirm lossless or lossy mode based on CTLE's preference for image quality vs. file size
- [x] Verify edge caching is active for public pages: open an incognito window, load the site home page, and confirm the `X-Kinsta-Cache: HIT` response header on second load (use browser dev tools → Network tab) — [Edge Caching](https://kinsta.com/docs/wordpress-hosting/caching/edge-caching/)
> Confirmed 2026-07-24 for anonymous requests (`x-kinsta-cache: HIT` over HTTP/2), incidentally during the §9 verification. The authenticated-bypass half of this check is still outstanding, below.
- [ ] Verify authenticated-user cache bypass: log in to WordPress, load the home page, and confirm the `X-Kinsta-Cache: BYPASS` response header — this ensures logged-in users (forum access, event registration state) do not receive cached pages
- [ ] Configure Kinsta's bandwidth usage alerts: MyKinsta → Company → Notifications — enable alerts at 80% and 100% of plan bandwidth — [Notifications](https://kinsta.com/docs/user-settings/notifications/)

---

## 12. Backup Configuration

### Kinsta-Side Backups (14-day retention, fast restore)

- [ ] Verify that daily automated backups are running: Sites → [site] → Backups → Automatic — backups should appear within 24 hours of site creation
- [x] Create a manual backup now as a baseline: Backups → Manual → Back up now
> Taken 2026-07-24 immediately before the primary-domain cutover in §9.
- [ ] Confirm point-in-time restore is available: verify that any listed backup has a "Restore to" button

### CTLE-Operated Off-Site Backup (30-day retention)

The Single 20GB plan retains 14 days of backups; the requirement is 30 days. A CTLE-operated daily backup fills this gap. Set this up before any content is added.

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
  - DU employee identifier → a custom user meta field (e.g., `du_employee_id`) — this becomes the account primary key
- [ ] Set **account linking method**: link by DU employee identifier (not email) — for users without an existing employee ID on file (i.e., initial local admin accounts), fall back to email-matching once, then the employee ID becomes primary
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
  - Confirm `du_employee_id` user meta is populated
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

## 16. LTI / Canvas Integration

**Prerequisites — [DU LT]:**
- Register the WordPress site as an LTI 1.3 tool in Canvas
- Provide: Canvas OIDC endpoint URL, Canvas JWKS endpoint URL, Canvas platform issuer
- Confirm the LTI launch payload includes: email, DU employee identifier (e.g., `lis_person_sourcedid`), and avatar URL (for Phase 2 — configure now to avoid later reconfiguration)
- Update the existing Canvas global-nav CTLE button URL to `https://ctle.dom.edu`

- [ ] In WP Admin, navigate to the **LTI Tool** plugin settings
- [ ] Enter Canvas platform details: OIDC endpoint, JWKS endpoint, platform issuer — provided by Learning Technologies
- [ ] Configure account linking: map the LTI `lis_person_sourcedid` (or the agreed DU employee identifier claim) to the same `du_employee_id` user meta field used by SSO (§13) — the linking key must be consistent between SSO and LTI
- [ ] Set default role for LTI-provisioned users: **Faculty**
- [ ] Test LTI launch:
  - In Canvas (using a test account with faculty role), navigate to the CTLE tool
  - Confirm successful LTI launch and WordPress login
  - Confirm the WordPress account created (or linked) has Faculty role
  - Confirm `du_employee_id` user meta is populated
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

- [ ] Enable **WordPress Export Personal Data** tool: Settings → Privacy — confirm the tool is accessible to CTLE Admin
- [ ] Enable **WordPress Erase Personal Data** tool: same location — confirm the tool is accessible to CTLE Admin
- [ ] Set the Privacy Policy page in Settings → Privacy → select or create the privacy policy page
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

Complete all items in this section on the staging environment first, then push to production and re-verify.

### Security

- [ ] Administrator access model is in place per §7: MyKinsta auto-login verified working, WP Activity Log alerts on Administrator logins and role changes confirmed, obfuscated login URL in use
- [ ] Recovery path verified: at least two people hold working SSH keys, and `wp user create` has been tested on staging so the procedure is known to work before it is needed
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
- [ ] Privacy policy page is published and linked from the site footer
- [ ] Terms of service / accessibility statement pages are present if required by DU policy
- [ ] Kinsta DPA is executed
- [ ] SOC 2 Type II attestation letter is on file with DU IT
- [ ] **[CTLE + OPC]** Forum privacy disclosure language is finalized and approved
- [ ] CTLE Admin has been trained on: creating and publishing events, managing the course catalog, moderating forums, managing the waitlist, and handling data export/erasure requests

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
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

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

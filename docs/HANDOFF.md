# CTLE WordPress — Session Handoff

**Written:** 2026-07-24 · **Last updated:** 2026-07-29 · **For:** the next working session.

> ## Where this stands, in one paragraph
>
> 🔴 **Read this first — the paragraph below is out of date within the same day.** A 2026-07-29 review found that **Live and Staging have diverged in both directions**: CD-2 put the whole security and infrastructure build on Live, while the Developer has since started building the theme and content in Staging. **No Kinsta push is safe in either direction** — staging→live destroys the security build, live→staging destroys her work — and Kinsta pushes **environment settings unconditionally even on a files-only push**, so the usual safeguard does not cover PHP 8.4 or the redirect rule. The urgent item is **CD-N7: tell the Developer today not to push Staging to Live**, because the button is the normal deployment step everywhere else and nothing warns her this site is different. Then ME-16, ME-17, ME-18. Mechanics: `kinsta_onboarding.md` **§24**. Nothing already built was lost — no push has happened.
>
> **Nothing is waiting on us.** As of 2026-07-29 the entire self-serve workstream is finished — WordPress infrastructure, security, backups, and the Canvas launch mechanism are all built and verified. What remains is (a) two provisioning requests sitting with DU IT, (b) four decisions sitting with the CTLE Director and Developer, and (c) **three things a new session should do in its first ten minutes** — send the two drafted emails and the SecureTransfer. See "Start here" immediately below.

Two build sessions got it here: **2026-07-28** completed the infra/plugin stack (§4–§12) and dropped LTI for a Canvas nav-link + Entra SSO (decision 10); **2026-07-29** closed every remaining self-serve item, removed password authentication site-wide after a Kinsta brute-force finding (decision 11), and built the Canvas gating script. The register in `STATUS_AND_ACTIONS.md` is authoritative.

This file exists so a new session can pick up without re-deriving context. It is a pointer document — the authoritative detail lives in the files it points to.

---

## Start here

**0. 🔴 Tell Amanda not to push Staging to Live (CD-N7).** Ahead of everything below, and not by batching it into the CTLE email — this one is time-sensitive in a way the others are not. She is building in Staging; a push would undo the whole security build. Assume she has no idea, because on every other host and on Kinsta generally, pushing staging to live *is* the correct deployment step. Then work ME-16 (align Staging's PHP + redirects to Live's), ME-17 (one question to Kinsta support), and ME-18 (the merge) in that order.

The three items below were the whole queue before that finding; they are still valid and still ready to send.

1. **Send the DU IT email** — `docs/outbound/2026-07-29-it.md`. Confirm Ellen Alamilla's address, then send as-is. Its one critical ask is a **turnaround estimate for the Entra app registration**, because that estimate is the missing input for the Director's launch-scope decision (CD-6). Everything else in the project is downstream of this.
2. **Send the CTLE email** — `docs/outbound/2026-07-29-ctle.md`. Needs Amanda's address added. Every factual claim in it is already true, so it can go immediately.
3. **Send the SecureTransfer** — the rotated custom login path (CD-N3) and the regenerated staging password, individually to Persis and Amanda. Never in the shared email, never in this repo.

Then the register (`STATUS_AND_ACTIONS.md`) becomes purely a chase list. `SELF_SERVE_CHECKLIST.md` Part B (Canvas beta test + the SIS `declared_user_type` column) is DU LT work that can proceed in parallel whenever there is time — it is not blocking anything.

---

## What this project is

Dominican University's Center for Teaching and Learning Excellence is standing up a WordPress site at `https://ctle.dom.edu`, hosted on Kinsta (Single 20GB plan, $350/year). Faculty authenticate through Microsoft Entra SSO and launch into the site from a link in the Canvas global navigation, arriving already authenticated (LTI was evaluated and dropped — decision 10). Target launch is August 2026 — **see the timeline risk below, this is the project's central problem.**

**Team:** Steven Endres (infrastructure, this repo; also head of DU Learning Technologies), Persis (CTLE Director), Amanda (developer). DU IT owns identity and mail; DU Learning Technologies (Steven's team) owns Canvas.

---

## Read these, in this order

| File | What it is |
|---|---|
| `STATUS_AND_ACTIONS.md` | **Start here.** Status by audience, plus the action register with owners. Most current. |
| `SELF_SERVE_CHECKLIST.md` | Runbook for everything that depends on nobody else, with exact commands. **Disposable** — retires into the register when done. |
| `../canvas/README.md` | The Canvas launch mechanism: the global-nav gating script and the SIS `declared_user_type` specification. |
| `kinsta_onboarding.md` | The 23-section build checklist. The operational spine of the project. |
| `IT_REQUESTS.md` | Specifications for DU IT and DU LT. Requests 1 & 2 submitted 2026-07-27 (Ellen email); **Request 3 (LTI) withdrawn 2026-07-28 — superseded by the Canvas nav-link + SSO (decision 10).** |
| `REQUIREMENTS.md` | Full requirements. Reviewed by stakeholders — treat changes as consequential. |
| `IMPLEMENTATION_PHASES.md` | Phase 1/2/3 assignments per requirement. |
| `Kinsta_Checklist.md`, `VENDOR_REQS.md` | Record of the completed vendor evaluation. **Historical — do not retroactively edit.** |

Every doc carries a changelog table at the bottom. Keep that convention; bump the version when you edit.

**Repo state at handoff:** as of 2026-07-29, working tree clean and pushed to `origin/main`. The `.md` files are the source of truth, plus two directories of real artifacts: `mu-plugins/` (deployed to Live) and `canvas/` (not yet uploaded to Canvas).

**Never commit credentials to this repo.** Two plaintext passwords were removed from `kinsta_onboarding.md` immediately before its first commit and confirmed absent from history. Use vault pointers.

---

## Verified state of the live site (2026-07-24, re-verified 07-27 and 07-29)

Confirmed by direct inspection, not assumed:

- Site is **live and publicly reachable** at `https://ctle.dom.edu`
- `http://ctle.dom.edu`, `http://www.ctle.dom.edu`, `https://www.ctle.dom.edu`, and `http://ductle.kinsta.cloud` all redirect to `https://ctle.dom.edu/`
- `https://ductle.kinsta.cloud` still serves 200 without redirecting — **known, accepted, documented** in §9. Kinsta `noindex`es it; it is the DNS fallback route. Scheduled for post-launch removal (ME-1c).
- WordPress `siteurl`/`home` cut over; zero `kinsta.cloud` references in page output
- TLS via Google Trust Services, **expires 2026-08-31** — inside the launch window, auto-renews, verify in late August
- No CAA records on `dom.edu`, so certificate issuance is unconstrained
- HTTP/2 active; Kinsta page cache active; **Kinsta CDN enabled** (default on all Kinsta sites; confirmed 2026-07-27)
- Search engines discouraged (`blog_public=0`) — set deliberately during the build, with a matching 🚩 launch gate in §23 to undo it
- WordPress core **updated to 7.0.2** (confirmed 2026-07-27 via generator meta); default theme cleanup deferred pending the theme decision
- PHP **now 8.4.23** (set on Live 2026-07-28; was 8.2) — decision 4 resolved; clear of the Dec 2026 EOL that applied to 8.2. Limits verified (memory 256M, exec 300s, upload/post 128M); `DISABLE_WP_CRON=true` set

**Site content is still largely a bare WordPress install** — no theme chosen, sample content (Hello World / Sample Page) still present and, as of 2026-07-29, **CTLE's to remove (CD-14)** rather than infrastructure's, with a hard §23 launch gate as the backstop. Security hardening done 2026-07-27: WPS Hide Login active (login path obfuscated; old `wp-login.php` → 404, new URL serves 200 anonymously), and `topsecretuser` deleted — only the two passwordless MyKinsta auto-login admins (Persis ID 2, Steven ID 3) remain. As of 2026-07-29 that is no longer merely true but **enforced**: `ctle-hardening.php` v1.1.0 removes password authentication outright, so no account *can* be logged into with a password whether or not one exists (decision 11). The custom login path was **rotated** 2026-07-29 after being exposed in a working session.

**Plugin/infra stack as of 2026-07-29 (all on Live):**
- **Active:** WPS Hide Login; WP Activity Log (audit *logging*); Query Monitor; WP Mail SMTP (installed, **config pending IT-2**).
- **Custom must-use plugins** (source version-controlled in `mu-plugins/`): `ctle-admin-alerts.php` (emails on any Administrator login / role change; recipients Steven + the Director; **delivery pending IT-2**) and `ctle-hardening.php` **v1.1.0** (XML-RPC off — verified 403 at Nginx — `X-Pingback` removed, and **password authentication removed entirely**).
- **Installed, inactive:** Relevanssi (config pending content/theme); wpForo (pre-staged 07-29; config pending SSO + CD-8).
- **Deleted 2026-07-29:** Hello Dolly, Akismet, LTI Tool, ceLTIc. On the LTI pair, "kept for optionality" was reversed deliberately — unused plugin code on disk is surface area with no upside, and both reinstall in a minute.
- **Config:** open registration off; PHP 8.4; CDN + Cloudflare Polish (Lossless) + bandwidth alerts; authenticated cache `BYPASS`; daily backups + point-in-time restore; **staging password-protected**; privacy export/erase tooling confirmed and the privacy page designated.
- **Deliberately not ours:** the sample content (Hello World, Sample Page, default comment) is **CD-14**, re-assigned to CTLE on the principle that posts and pages are their domain — with a hard 🚩 §23 launch gate, because nobody is otherwise scheduled to remove it.

---

## Decisions made this session — do not re-litigate without cause

**1. No break-glass recovery account.** Withdrawn in favor of MyKinsta's WP Admin auto-login, which provisions an Administrator account with *no password at all*. Net security improvement: no privileged account on the site has a password. Full reasoning in `kinsta_onboarding.md` §7.

Two consequences that must stay managed: the `ctle@dom.edu` shared mailbox now receives the MyKinsta 2FA codes that gate Administrator access, making that mailbox's access list a security control; and MyKinsta account standing is now a single point of failure, with SSH + WP-CLI as the documented fallback.

**Amended 2026-07-24.** `REQUIREMENTS.md` (v0.2.1) and `IMPLEMENTATION_PHASES.md` (v0.2.0) now carry the change: the break-glass rows are replaced by the auto-login and SSH recovery model, DU IT's credential-vault responsibility is struck, and the Two Factor / WP 2FA plugin is removed from both plugin stacks. The withdrawal and its reasoning are preserved in `REQUIREMENTS.md` §5 rather than deleted, so the decision remains auditable. Monday's IT agenda item 4 presents this as a formal requirements change and asks for fresh security sign-off (IT-6).

**2. The LTI plugin in the original plan was backwards.** The docs named "LTI Platform for WordPress," which makes WordPress act as the LMS. CTLE needs the reverse — WordPress as the **tool**, launched from Canvas. Correct software is [LTI Tool](https://wordpress.org/plugins/lti-tool/) plus the [ceLTIc LTI Library](https://wordpress.org/plugins/celtic-lti/) dependency. Corrected in `IT_REQUESTS.md` Request 3, and (2026-07-24) in `kinsta_onboarding.md` §5 and §16 and `REQUIREMENTS.md` §6 and §17. **Superseded 2026-07-28 (decision 10): LTI is withdrawn entirely in favor of a nav-link + SSO; this plugin-direction correction is retained only as history.**

**3. Mail goes through Microsoft Graph, not SMTP AUTH.** Microsoft disables SMTP AUTH basic authentication by default for existing tenants at the end of December 2026 — four months post-launch. `kinsta_onboarding.md` §15 amended 2026-07-24 to Graph-only; SMTP AUTH dropped as a co-equal option.

**4. PHP target — resolved to 8.4, applied 2026-07-28.** Server was on 8.2 (security-support end Dec 2026). Chose **8.4** over 8.3 (shorter runway, ~end 2027) and 8.5 (newest, plugin-lag risk): 8.4 is mature by launch and supported to ~end 2028. Applied on Live 2026-07-28; verified `fpm-fcgi` 8.4.23, all plugins load clean, PHP limits + cron confirmed, `DISABLE_WP_CRON=true` set (§10). CD-N6 heads-up to the developer still to send (no visible effect, reversible).

**5. Deferred deliberately:** HSTS (hard to walk back; add post-launch); disabling the `ductle.kinsta.cloud` hostname (fallback route during the build).

**6. SSO uses Option 1 — Entra group-gated access with JIT provisioning (2026-07-27 IT meeting).** DU IT refreshes an Entra group from the SIS faculty list; the CTLE enterprise app is gated on that group (assignment required); WordPress auto-provisions a Faculty account on first successful sign-in. Access control lives entirely in Entra — no roster feed or cron on our side. CTLE admins, director, and developer reach WordPress via MyKinsta auto-login, so they need not be in the group, which makes the faculty group the whole SSO scope. Entra ID P1 is confirmed (group-based assignment available). The rejected Option 2 would have opened SSO to all university users and made WordPress enforce its own allow-list.

**7. Automated mail sends from `ctle-noreply@dom.edu` (2026-07-27).** A dedicated shared mailbox on the established `dom.edu` domain, separate from the human `ctle@dom.edu` (which also receives MyKinsta 2FA codes), so WordPress mail inherits existing SPF/DKIM/DMARC with no new DNS. This is the send-as identity for the Graph `Mail.Send` app in Request 2.

**8. Build SSO on Live, not staging (CD-2, decided 2026-07-27).** SSO config is hostname-bound and a staging→live push overwrites Live, so building on Live means IT registers one Entra redirect URI, not two. Staging is reserved for post-launch update testing. Nothing is live to break yet (noindex, unannounced). *(Originally "SSO/LTI"; LTI dropped 2026-07-28 — decision 10.)*

**9. Graph is split — calendar deferred, mail in scope (2026-07-27).** Calendar-write Graph (`Calendars.ReadWrite`) stays Phase 3; launch uses an .ics "add to calendar" download. Mail-send Graph (`Mail.Send`) is still needed for launch — it's how WordPress sends notifications. Two different app-registration permissions; don't conflate them.

**10. LTI dropped — Canvas nav-link + Entra SSO is the launch mechanism (2026-07-28).** CTLE is a standalone site needing none of LTI Advantage's services (grades, roster, deep-linking, embedding), and the Entra faculty group (Option 1) already gates access no matter how the site is reached. So faculty launch from the existing **CTLE button in Canvas global nav**, retargeted to the site's **SSO-initiation URL**; since Canvas is on the same Entra tenant, the click completes SSO silently and lands them logged in. This drops the whole LTI workstream (Developer Key, platform registration, JWKS exchange, LTI-vs-Entra identity reconciliation) and de-risks the timeline — **SSO (IT-1) is now the single launch integration.** Button visibility is gated client-side on `declared_user_type=teacher`, set via the nightly SIS `users.csv` and read from `/api/v1/users/self/logins` (validated 2026-07-28 as non-admin-readable); this is cosmetic since Entra is the real gate. LTI Tool + ceLTIc deactivated 07-28 and **deleted 2026-07-29** — the "kept installed for optionality" clause was reversed deliberately (withdrawn integration; both free to reinstall; unused code on disk is surface with no upside). Recorded across `REQUIREMENTS.md` §6, `IT_REQUESTS.md` Request 3 (withdrawn), `IMPLEMENTATION_PHASES.md` §6, and `STATUS_AND_ACTIONS.md` (LT-1/LT-3 re-scoped). Supersedes decision 2.

**11. Password authentication removed site-wide (2026-07-29).** Kinsta support confirmed their automatic brute-force IP ban watches `/wp-login.php` **specifically**, so WPS Hide Login had quietly moved the login form to an endpoint with *no* rate limiting — measured directly: `POST /wp-login.php` → **403 at Kinsta's edge**, `POST` to the custom path → **200, processed**. Moving the login had removed protection rather than added it. Rather than bolt on a rate limiter, `ctle-hardening.php` v1.1.0 drops core's username/password, email/password, and application-password authenticators, hides application passwords, and disables password reset — turning §6's "no password login" goal from an operational assumption into an enforced property. Verified on Staging before Live with a positive control (a user whose password `wp_check_password` accepts but `wp_authenticate` refuses), and MyKinsta auto-login confirmed still working on both. **Rejected alternative:** Limit Login Attempts Reloaded — the right answer for a site that needs password login; this one does not. **Cost:** the §7 recovery procedure gains one step (move the hardening file aside before a WP-CLI password reset grants a login) — documented in §7, §8, §23, the file header, and `mu-plugins/README.md`. The custom login path was rotated the same day after exposure in a diagnostic session; treat the path as a speed bump, not a control.

**12. Live is the source of truth; a staging→live push is destructive here (2026-07-29).** This is decision 8's missing corollary, not a new direction. Putting the build on Live was right — SSO is hostname-bound, and it saves IT registering two redirect URIs — but it was recorded as a narrow decision *about SSO*, and its general consequence went unwritten: **everything** now lives on Live first, so sync flows live→staging and the ordinary "build on staging, push to live" habit is inverted for this site. Two days of docs disagreed with the decision as a result — `kinsta_onboarding.md` §5 and §23 still said to build on staging and push to production, either of which would have destroyed the build. Both reversed 07-29 and the mechanics written up in the new **§24**. Because the Developer has meanwhile started building in Staging, the fix is a **selective merge (ME-18)**, not a refresh in either direction: theme files and content tables move up, `wp_options`/`wp_users`/`wp_usermeta` never do, and theme mods get re-applied by hand. **Rejected alternative:** refresh Staging from Live — the clean answer right up until Staging contained work worth keeping.

**Confirmed at the 2026-07-27 meeting (and after):** vendor security review approved; Kinsta DPA executed; **IT-6 admin-protection sign-off received**; `topsecretuser` deleted (never logged in; CD-N1 waived). Requests 1 & 2 submitted via the Ellen email; LT-2 (Canvas↔Entra ID match) confirmed. Still open: IT-4/CD-7 (`ctle@dom.edu` access-list decision).

---

## Hard-won knowledge

**Kinsta serves stale HTML after settings changes.** Anything altering rendered output stays cached at the edge for anonymous visitors. Diagnostic pattern: request the URL with a `?v=random` query string. If the buster shows the change and the plain URL doesn't, it is cache, not configuration. Check `x-kinsta-cache` (page cache) and `ki-cf-cache-status` (Cloudflare layer) separately — they purge independently, and a second layer appears once the CDN is enabled.

**Update 2026-07-27 — resolved:** the homepage stale-`noindex` cache instance has cleared. Re-verified 07-27: the `noindex, nofollow` robots meta is present in *both* the cached (`x-kinsta-cache: HIT`) and cache-busted (`x-kinsta-cache: BYPASS`) renders, so anonymous visitors now receive the discouraged-indexing copy. No Kinsta support ticket needed.

**The redirect-loop trap.** Never enable Kinsta's redirect-all-to-primary while WordPress still believes it lives at the old hostname: WordPress canonical-redirects back to the old host, Kinsta redirects forward, and `wp-admin` becomes unreachable. Always cut `siteurl` over first. Documented in §9.

**Kinsta allows only one SSH user per environment.** You cannot create additional SSH users — but each MyKinsta company member authorizes their own key (User Settings → SSH Keys), and all authorized keys connect as that single environment user. So "recovery held by two people" (decision 1) means two MyKinsta members each with a key on file, not two SSH accounts. Kinsta's "additional users" are SFTP-only — no shell, no WP-CLI — so they are **not** a recovery path. ed25519 keys work even though Kinsta's docs only show `ssh-keygen -t rsa`. Confirmed by direct connection on both Staging and Live, 2026-07-27.

**"Kinsta serves the domain" and "WordPress knows its address" are independent settings.** Only the first had been done, while the checklist claimed both. Verify externally rather than trusting checkboxes — several were inaccurate.

**Credentials must never enter this repo.** Two plaintext passwords were removed from `kinsta_onboarding.md` on 2026-07-24 before the first commit. Confirmed never committed. Use vault pointers instead.

**`wp eval` / WP-CLI reports the CLI SAPI's PHP limits, not the site's.** Checking `memory_limit`/`upload_max_filesize` via `wp eval` returned CLI defaults (`-1`/`2M`/`8M`), *not* the live php-fpm values. Read PHP limits from **Site Health → Info → Server** or Query Monitor's PHP panel (SAPI shows `fpm-fcgi`). Confirmed 2026-07-28 (web values: 256M / 300s / 128M).

**WP Activity Log's email alerts are Premium-only.** The free plugin logs but does not send custom notifications. Rather than license it for two rules, admin-login / role-change alerting lives in the free `mu-plugins/ctle-admin-alerts.php` (core `wp_login` + `set_user_role` hooks). Delivery still needs WP Mail SMTP (IT-2).

**Kinsta's login protection watches `/wp-login.php` — and only that. WPS Hide Login therefore *removed* protection rather than adding it.** Confirmed by Kinsta support and then measured directly, 2026-07-29: `POST /wp-login.php` is refused **403 at Kinsta's edge** before WordPress runs, while a `POST` to the custom login path returns **200** and is processed normally — no block, no throttle. So the site traded a genuinely protected endpoint for an unprotected one, and the protection was left guarding a URL that returns 404. Obscuring a login URL and protecting it are not the same thing. The trap: Kinsta *does* auto-detect a customized login URL for its auto-login feature, which made "Kinsta knows about the custom URL" feel like it covered everything. It does not — **auto-login detection and the brute-force ban are different subsystems.** Do not generalize a vendor's awareness of a setting across its features. Fixed by removing password authentication outright (`ctle-hardening.php` v1.1.0, §6) rather than adding a rate limiter to a login nobody should use.

**A Kinsta push carries environment settings unconditionally — "files only" is not the safeguard everyone assumes.** Redirects, geolocation, **PHP version**, and Nginx configuration transfer from source to destination *even when you select files-only or database-only*. So the standard advice for protecting a live database ("just push files") would still have reverted §10's PHP 8.4 and §9's redirect-to-primary. There is no push configuration that leaves them alone. The fix is not to avoid pushing but to **keep the two environments' settings identical**, so the forced overwrite writes back the same values (ME-16). Confirmed excluded from a push: `.htpasswd` protection, bot-protection level, the *destination's* custom Nginx config (retained), domains, and SSL. The destination is auto-backed-up first. One thing that is still **undocumented and worth a support ticket**: whether a file push *deletes* destination files absent from the source (ME-17) — it decides whether a push would silently remove `ctle-admin-alerts.php`, which was deployed to Live only. **The generalizable lesson:** a hosting feature named for the common workflow ("Push to Live") assumes staging is downstream of production. Invert that assumption — as CD-2 did, for good reasons — and the vendor's safety defaults quietly stop protecting you, while the button keeps looking routine to anyone who hasn't been told.

**Kinsta blocks XML-RPC at the Nginx layer (403).** `POST /xmlrpc.php` returns 403 before WordPress runs, so the app-layer `xmlrpc_enabled` filter in `ctle-hardening.php` is belt-and-suspenders. Confirmed 2026-07-28.

**Canvas `declared_user_type` is readable by non-admin users via `/api/v1/users/self/logins`.** This is the validated hook for gating the CTLE global-nav button (decision 10): set `declared_user_type=teacher` (an enum value) via the nightly SIS `users.csv`, then the global-nav JS reads it from `self/logins` and shows the button. It is *not* in `window.ENV` or `users/self`, and the response for this endpoint has no `while(1);` prefix. Gating is cosmetic — Entra is the real gate. Confirmed 2026-07-28.

---

## Immediate next actions

`STATUS_AND_ACTIONS.md` is the authoritative register. **Our queue is no longer empty** — the 07-29 environment-divergence finding reopened it with CD-N7 (urgent), ME-16, ME-17, and ME-18. See "Start here" at the top of this file. What follows is what we are waiting on *others* for, and who owns it.

### Waiting on DU IT — chase, don't wait
- **IT-1 — Entra app registration + test account.** The gate. It unblocks the SSO plugin (§13), admin elevation (§14), forums (§18), the Canvas launch link, and roughly half of §23. The **turnaround estimate matters more than the artifact right now**, because it is the missing input for CD-6.
- **IT-2 — `ctle-noreply@dom.edu` + Graph `Mail.Send`.** Unblocks every email the site sends, including the admin-login alerting that is already running but cannot deliver.
- **IT-4 / CD-7** — who holds `ctle@dom.edu`. It carries the MyKinsta 2FA codes, so the access list is a security control.

### Waiting on the Director & Developer
- **CD-1 theme** — the biggest gap. Gates WCAG 2.1 AA and DU brand review, neither of which can start before a theme exists, plus all content work.
- **CD-6 launch scope** — take it to the Director the moment IT's estimate lands. Proposal is already written: launch the public layer in August, hold forums.
- **CD-4** Events Calendar Pro license · **CD-3** page builder · **CD-5** catalog structure (CPT recommended) · **CD-8** start OPC on forum privacy now, long lead time · **CD-14** delete the sample content before launch.
- **ME-6 / ME-10 — Amanda specifically.** Her MyKinsta auto-login (which provisions her account so her `sis_user_id` can be stamped **before** her first SSO, or she ends up with duplicates) and her SSH key (so recovery is held by two people, not one). Neither needs IT. Both are asks in the CTLE email.

### DU LT (our team, Canvas-side) — buildable now, blocking nothing
- `SELF_SERVE_CHECKLIST.md` Part B: beta-test `canvas/ctle-global-nav.js` against a teacher and a student account, add the `declared_user_type` column to the nightly SIS `users.csv`, and settle LT-4 (retract the break-glass request if it was ever sent). The production upload waits for the real SSO URL and a launch decision.

### Parked (post-launch)
- **ME-11** off-site 30-day backup · **ME-1c** disable `ductle.kinsta.cloud` · HSTS · full Relevanssi + forum config · §14 admin elevation.
- **ME-13** — verify TLS auto-renewal on **2026-08-24**; the certs expire 08-31, inside the launch window.

## The timeline problem

**This is the thing to keep in view.** The launch target is August 2026. Entra SSO and the M365 mailbox are specified and requested (07-27) but not yet provisioned by IT. **Canvas LTI was dropped 2026-07-28 (decision 10)** in favor of the nav-link + SSO, so SSO is now the single launch integration — it gates admin elevation (§14), forums (§18), the Canvas launch link, and roughly half of pre-launch verification (§23). Forums specifically cannot launch, since the requirement is that anonymous visitors see no forum content.

The proposal on the table, needing the Director's decision (CD-6): **launch the public layer in August** — home page, course catalog, events calendar, blog, search, none of which need Entra — and hold forums until SSO lands. (The navigation link that `IMPLEMENTATION_PHASES.md` §6 once listed as the LTI *fallback* is now the *primary* launch mechanism — decision 10 — so there is no separate LTI wait.)

**Sharper as of 2026-07-29: there is no longer any work of ours between here and launch.** Every task that did not depend on IT or CTLE is done, so the schedule is now purely a function of IT's turnaround and the Director's scope call. That is worth stating plainly, because it changes what pressure is useful — writing more code will not recover a single day. Chasing the estimate will.

> **Amended later the same day.** The environment-divergence finding puts a small amount of work back on our side (CD-N7, ME-16, ME-17, ME-18), so "no work of ours" is no longer strictly true. The conclusion above survives anyway, for a reason worth being precise about: none of the four items is on the **critical path**. They are a correction to how the two environments are kept in sync, and the merge (ME-18) runs in parallel with the wait on IT rather than extending it. The schedule is still a function of IT's turnaround. What *has* changed is the downside risk — an unwitting push would cost days of rebuild plus a security regression, which is exactly the kind of self-inflicted delay an August target cannot absorb. Hence CD-N7 today rather than in the next batch.

The estimate was requested at the 07-27 meeting and again in the 07-29 email. Take the scope question to the Director the moment it lands; if it has not landed by roughly **2026-08-07**, escalate rather than wait, because a decision made in mid-August is a decision made too late to act on.

---

## Housekeeping still open

- **Nothing is open here.** Both former items closed 2026-07-29:
- ~~**ME-8** confirm the redacted §1/§3 passwords are in the vault~~ — **closed as moot.** Neither credential still matters: the `topsecretuser` password died with the account (deleted 07-27), and the §1 MyKinsta owner password is the Director's own, self-recoverable via Kinsta's reset, and correctly not redistributed. Git history re-scanned — neither plaintext was ever committed.
- ~~**Widen `ctle-admin-alerts.php` recipients**~~ — **done.** Steven + the Director, with an `is_email` guard on the list. `ctle@dom.edu` deliberately excluded: it carries the MyKinsta 2FA codes, and alert plus second factor should not share an inbox.
- ~~Second cross-document consistency audit~~ — **done 2026-07-29.** Found four inconsistencies beyond ordinary checkbox lag: §7 still directed users to "Lost your password?" (disabled by decision 11); §23's recovery gate still treated `wp user create` alone as sufficient; `IT_REQUESTS.md` still claimed "Requests 1–5 outstanding" with Request 3 live; and Request 1's OIDC preference rested half on the withdrawn LTI integration. All corrected. **Lesson worth keeping: a security change ripples into procedure docs, not just status docs — grep for the *procedure* it invalidates, not only the setting it changed.**
- ~~Cross-document consistency audit~~ — **done 2026-07-28.** Aligned every doc to what was actually built: image optimization → **Cloudflare Polish** (no plugin) in `REQUIREMENTS.md` §3/§17 and `IMPLEMENTATION_PHASES.md`; alerting → the **`ctle-admin-alerts.php` mu-plugin** (WP Activity Log notifications are Premium); LTI withdrawn everywhere (decision 10); the two custom mu-plugins added to both §17 plugin stacks. All docs changelog-bumped. `Kinsta_Checklist.md` and `VENDOR_REQS.md` left untouched (historical evaluation records).
- ~~Amend `REQUIREMENTS.md` and `IMPLEMENTATION_PHASES.md` for the break-glass withdrawal~~ — **done 2026-07-24**
- ~~Correct the LTI plugin naming (decision 2)~~ — **done 2026-07-24**; then the whole LTI approach was **withdrawn 2026-07-28 (decision 10)**.
- ~~Amend `kinsta_onboarding.md` §15 to drop SMTP AUTH as an option (decision 3)~~ — **done 2026-07-24**

---

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

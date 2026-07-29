# CTLE WordPress — Session Handoff

**Written:** 2026-07-24 · **Last updated:** 2026-07-29 · **For:** the next working session.

> **2026-07-29:** every task that depends on nobody else is now enumerated with exact commands in `SELF_SERVE_CHECKLIST.md`, the Canvas launch mechanism is built (`canvas/`), and the IT and CTLE chase emails are drafted in `docs/outbound/`. Once that runbook is executed, **nothing is waiting on us.**
 The 2026-07-28 build session completed the WordPress infra/plugin stack (§4–§12: security plugins + custom mu-plugins, PHP 8.4, CDN/Polish, backups verified) and **dropped LTI in favor of a Canvas nav-link + Entra SSO (decision 10).** The critical path is now SSO alone (IT-1). Remaining self-serve work and external waits are in `STATUS_AND_ACTIONS.md`.

This file exists so a new session can pick up without re-deriving context. It is a pointer document — the authoritative detail lives in the files it points to.

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

**Repo state at handoff:** as of 2026-07-27, working tree clean and pushed to `origin/main`. The `.md` files are the source of truth.

**Never commit credentials to this repo.** Two plaintext passwords were removed from `kinsta_onboarding.md` immediately before its first commit and confirmed absent from history. Use vault pointers.

---

## Verified state of the live site as of 2026-07-24 (re-verified 2026-07-27)

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

**Site content is still largely a bare WordPress install** — no theme chosen, sample content (Hello World / Sample Page) still present. Security hardening done 2026-07-27: WPS Hide Login active (login path obfuscated; old `wp-login.php` → 404, new URL serves 200 anonymously), and `topsecretuser` deleted — only the two passwordless MyKinsta auto-login admins (Persis ID 2, Steven ID 3) remain, so **no password-authenticated login exists on the site**.

**Plugin/infra stack built 2026-07-28 (all on Live):**
- **Active:** WPS Hide Login; WP Activity Log (audit *logging*); Query Monitor.
- **Custom must-use plugins** (source version-controlled in `mu-plugins/`): `ctle-admin-alerts.php` (emails admins on any Administrator login / role change — **delivery pending WP Mail SMTP/IT-2**; recipients currently just `sendres@dom.edu`) and `ctle-hardening.php` (XML-RPC off — verified 403 at Nginx — and `X-Pingback` removed).
- **Staged, inactive:** WP Mail SMTP (config pending IT-2); Relevanssi (config pending content/theme).
- **Deactivated:** LTI Tool + ceLTIc (LTI dropped — decision 10; kept installed for optionality).
- **Config:** open registration off; PHP 8.4; CDN + Cloudflare Polish (Lossless) + bandwidth alerts on; authenticated cache `BYPASS` verified; daily backups + point-in-time restore confirmed.
- **Still to clean (ME-3, needs CD-N5):** Hello Dolly + Akismet (inactive) and the sample content are still present.

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

**10. LTI dropped — Canvas nav-link + Entra SSO is the launch mechanism (2026-07-28).** CTLE is a standalone site needing none of LTI Advantage's services (grades, roster, deep-linking, embedding), and the Entra faculty group (Option 1) already gates access no matter how the site is reached. So faculty launch from the existing **CTLE button in Canvas global nav**, retargeted to the site's **SSO-initiation URL**; since Canvas is on the same Entra tenant, the click completes SSO silently and lands them logged in. This drops the whole LTI workstream (Developer Key, platform registration, JWKS exchange, LTI-vs-Entra identity reconciliation) and de-risks the timeline — **SSO (IT-1) is now the single launch integration.** Button visibility is gated client-side on `declared_user_type=teacher`, set via the nightly SIS `users.csv` and read from `/api/v1/users/self/logins` (validated 2026-07-28 as non-admin-readable); this is cosmetic since Entra is the real gate. LTI Tool + ceLTIc deactivated (kept installed for optionality). Recorded across `REQUIREMENTS.md` §6, `IT_REQUESTS.md` Request 3 (withdrawn), `IMPLEMENTATION_PHASES.md` §6, and `STATUS_AND_ACTIONS.md` (LT-1/LT-3 re-scoped). Supersedes decision 2.

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

**Kinsta blocks XML-RPC at the Nginx layer (403).** `POST /xmlrpc.php` returns 403 before WordPress runs, so the app-layer `xmlrpc_enabled` filter in `ctle-hardening.php` is belt-and-suspenders. Confirmed 2026-07-28.

**Canvas `declared_user_type` is readable by non-admin users via `/api/v1/users/self/logins`.** This is the validated hook for gating the CTLE global-nav button (decision 10): set `declared_user_type=teacher` (an enum value) via the nightly SIS `users.csv`, then the global-nav JS reads it from `self/logins` and shows the button. It is *not* in `window.ENV` or `users/self`, and the response for this endpoint has no `while(1);` prefix. Gating is cosmetic — Entra is the real gate. Confirmed 2026-07-28.

---

## Immediate next actions

`STATUS_AND_ACTIONS.md` is the authoritative register; this is the prioritized short version. **The infra/plugin build (§4–§12) is done. SSO (IT-1) is the single external blocker** — it gates the Canvas launch link, forums, admin elevation, and (with IT-2) mail. Everything below is ordered so the top items move *without* waiting on SSO or email.

### Do now — Steven, self-serve (no SSO/email dependency)

**→ `SELF_SERVE_CHECKLIST.md` Part A.** It supersedes the list that used to sit here: ME-3 §4 cleanup, the §6 brute-force confirmation, alert-recipient widening (ME-12), ME-8 vault check, staging password protection (CD-N4), MyKinsta hygiene, §22 privacy tooling, the TLS renewal reminder (ME-13), and optional wpForo pre-staging — each with the exact command and verification. Ordered so the destructive step is backed up first and the CTLE email goes out only after the cleanup is real.

Chasing **IT-1** (and IT-2) is not on that list because it is not a build — it is the drafted email at `outbound/2026-07-29-it.md`, and it remains the single highest-leverage action in the project.

### Do now — needs Amanda (developer)
8. **ME-10** — have Amanda launch into Live via MyKinsta auto-login (provisions her admin account), then stamp her `sis_user_id` (= her Entra `employeeId`) **before her first SSO**. Does not require SSO to be live.
9. **ME-6** — add Amanda's SSH public key in MyKinsta so recovery is held by two people (§8).

### Do now — DU LT (Steven's team, Canvas-side — the launch mechanism, minus the final URL)

**→ `SELF_SERVE_CHECKLIST.md` Part B**, against the built artifacts in `canvas/`. The gating script is written (`ctle-global-nav.js`); what remains is the Canvas **beta** test with a teacher and a student account, the SIS `declared_user_type` column, and LT-4's break-glass retraction check. The SSO URL is one config constant, and the `enabled` switch defaults to `false` — so the script can ship to Canvas before IT-1 lands without touching today's button.

### External — chase, don't wait
- **IT-1** (Entra app + estimate) — the gate. **IT-2** (`ctle-noreply@dom.edu` mailbox + Graph `Mail.Send`) — unblocks all mail (alerts + event/registration notices).
- **IT-4 / CD-7** — decide who holds `ctle@dom.edu` (it carries MyKinsta 2FA codes, so it's a security control).

### Blocked on the Director & Developer
- **CD-1 theme** (the biggest gap — gates WCAG + brand review, and content), **CD-3** page builder, **CD-4** Events Calendar Pro license, **CD-6** launch scope (take it once the Entra estimate lands), **CD-8** start the OPC forum-privacy conversation now (long lead time).

### Parked (post-launch)
- **ME-11** off-site 30-day backup; **ME-1c** disable `ductle.kinsta.cloud`; HSTS; full Relevanssi + forum config; §14 admin elevation.

---

## The timeline problem

**This is the thing to keep in view.** The launch target is August 2026. Entra SSO and the M365 mailbox are specified and requested (07-27) but not yet provisioned by IT. **Canvas LTI was dropped 2026-07-28 (decision 10)** in favor of the nav-link + SSO, so SSO is now the single launch integration — it gates admin elevation (§14), forums (§18), the Canvas launch link, and roughly half of pre-launch verification (§23). Forums specifically cannot launch, since the requirement is that anonymous visitors see no forum content.

The proposal on the table, needing the Director's decision (CD-6): **launch the public layer in August** — home page, course catalog, events calendar, blog, search, none of which need Entra — and hold forums until SSO lands. (The navigation link that `IMPLEMENTATION_PHASES.md` §6 once listed as the LTI *fallback* is now the *primary* launch mechanism — decision 10 — so there is no separate LTI wait.)

The Monday meeting is done and the Entra turnaround estimate was requested in the follow-up email; it's the missing input for the CD-6 scope decision. Take the scope question to the Director once that estimate lands.

---

## Housekeeping still open

- **ME-8** — the credential redaction is done, but confirm the passwords are actually recorded in the CTLE vault before anyone needs them.
- **Widen `ctle-admin-alerts.php` recipients** beyond `sendres@dom.edu` before launch.
- ~~Cross-document consistency audit~~ — **done 2026-07-28.** Aligned every doc to what was actually built: image optimization → **Cloudflare Polish** (no plugin) in `REQUIREMENTS.md` §3/§17 and `IMPLEMENTATION_PHASES.md`; alerting → the **`ctle-admin-alerts.php` mu-plugin** (WP Activity Log notifications are Premium); LTI withdrawn everywhere (decision 10); the two custom mu-plugins added to both §17 plugin stacks. All docs changelog-bumped. `Kinsta_Checklist.md` and `VENDOR_REQS.md` left untouched (historical evaluation records).
- ~~Amend `REQUIREMENTS.md` and `IMPLEMENTATION_PHASES.md` for the break-glass withdrawal~~ — **done 2026-07-24**
- ~~Correct the LTI plugin naming (decision 2)~~ — **done 2026-07-24**; then the whole LTI approach was **withdrawn 2026-07-28 (decision 10)**.
- ~~Amend `kinsta_onboarding.md` §15 to drop SMTP AUTH as an option (decision 3)~~ — **done 2026-07-24**

---

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

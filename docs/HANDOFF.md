# CTLE WordPress — Session Handoff

**Written:** 2026-07-24 · **Last updated:** 2026-07-27 · **For:** the next working session (expected 2026-07-28). The 07-27 IT meeting is done and its decisions are captured below; today also closed out the security hardening — `topsecretuser` deleted, login path obfuscated. Tomorrow is the remaining self-serve cleanup while IT provisions Entra and the mailbox.

This file exists so a new session can pick up without re-deriving context. It is a pointer document — the authoritative detail lives in the files it points to.

---

## What this project is

Dominican University's Center for Teaching and Learning Excellence is standing up a WordPress site at `https://ctle.dom.edu`, hosted on Kinsta (Single 20GB plan, $350/year). Faculty authenticate through Microsoft Entra SSO and launch into the site from Canvas via LTI 1.3. Target launch is August 2026 — **see the timeline risk below, this is the project's central problem.**

**Team:** Steven Endres (infrastructure, this repo; also head of DU Learning Technologies), Persis (CTLE Director), Amanda (developer). DU IT owns identity and mail; DU Learning Technologies (Steven's team) owns Canvas.

---

## Read these, in this order

| File | What it is |
|---|---|
| `STATUS_AND_ACTIONS.md` | **Start here.** Status by audience, plus the action register with owners. Most current. |
| `kinsta_onboarding.md` | The 23-section build checklist. The operational spine of the project. |
| `IT_REQUESTS.md` | Specifications for DU IT and DU LT. Requests 1 & 2 submitted 2026-07-27 (Ellen email); Request 3 (LTI — Steven's own team) pending. |
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
- PHP **confirmed 8.2** (2026-07-27) — must move off it before its Dec 2026 security-support end; target now under review (decision 4)

**Site content is still largely a bare WordPress install** — no theme chosen, sample content (Hello World / Sample Page) still present. Security hardening done 2026-07-27: WPS Hide Login active (login path obfuscated; old `wp-login.php` → 404, new URL serves 200 anonymously), and `topsecretuser` deleted — only the two passwordless MyKinsta auto-login admins (Persis ID 2, Steven ID 3) remain, so **no password-authenticated login exists on the site**.

---

## Decisions made this session — do not re-litigate without cause

**1. No break-glass recovery account.** Withdrawn in favor of MyKinsta's WP Admin auto-login, which provisions an Administrator account with *no password at all*. Net security improvement: no privileged account on the site has a password. Full reasoning in `kinsta_onboarding.md` §7.

Two consequences that must stay managed: the `ctle@dom.edu` shared mailbox now receives the MyKinsta 2FA codes that gate Administrator access, making that mailbox's access list a security control; and MyKinsta account standing is now a single point of failure, with SSH + WP-CLI as the documented fallback.

**Amended 2026-07-24.** `REQUIREMENTS.md` (v0.2.1) and `IMPLEMENTATION_PHASES.md` (v0.2.0) now carry the change: the break-glass rows are replaced by the auto-login and SSH recovery model, DU IT's credential-vault responsibility is struck, and the Two Factor / WP 2FA plugin is removed from both plugin stacks. The withdrawal and its reasoning are preserved in `REQUIREMENTS.md` §5 rather than deleted, so the decision remains auditable. Monday's IT agenda item 4 presents this as a formal requirements change and asks for fresh security sign-off (IT-6).

**2. The LTI plugin in the original plan was backwards.** The docs named "LTI Platform for WordPress," which makes WordPress act as the LMS. CTLE needs the reverse — WordPress as the **tool**, launched from Canvas. Correct software is [LTI Tool](https://wordpress.org/plugins/lti-tool/) plus the [ceLTIc LTI Library](https://wordpress.org/plugins/celtic-lti/) dependency. Corrected in `IT_REQUESTS.md` Request 3, and (2026-07-24) in `kinsta_onboarding.md` §5 and §16 and `REQUIREMENTS.md` §6 and §17.

**3. Mail goes through Microsoft Graph, not SMTP AUTH.** Microsoft disables SMTP AUTH basic authentication by default for existing tenants at the end of December 2026 — four months post-launch. `kinsta_onboarding.md` §15 amended 2026-07-24 to Graph-only; SMTP AUTH dropped as a co-equal option.

**4. PHP target moved from 8.2 to 8.3 — now worth revisiting.** Server **confirmed still on 8.2** (07-27), which loses security support in December 2026. PHP **8.5 is now available** on Kinsta, so the real choice is 8.3 (the documented target, supported to ~Dec 2027) versus 8.4/8.5 for more runway — gated on WordPress + plugin compatibility. Not yet applied to the server.

**5. Deferred deliberately:** HSTS (hard to walk back; add post-launch); disabling the `ductle.kinsta.cloud` hostname (fallback route during the build).

**6. SSO uses Option 1 — Entra group-gated access with JIT provisioning (2026-07-27 IT meeting).** DU IT refreshes an Entra group from the SIS faculty list; the CTLE enterprise app is gated on that group (assignment required); WordPress auto-provisions a Faculty account on first successful sign-in. Access control lives entirely in Entra — no roster feed or cron on our side. CTLE admins, director, and developer reach WordPress via MyKinsta auto-login, so they need not be in the group, which makes the faculty group the whole SSO scope. Entra ID P1 is confirmed (group-based assignment available). The rejected Option 2 would have opened SSO to all university users and made WordPress enforce its own allow-list.

**7. Automated mail sends from `ctle-noreply@dom.edu` (2026-07-27).** A dedicated shared mailbox on the established `dom.edu` domain, separate from the human `ctle@dom.edu` (which also receives MyKinsta 2FA codes), so WordPress mail inherits existing SPF/DKIM/DMARC with no new DNS. This is the send-as identity for the Graph `Mail.Send` app in Request 2.

**8. Build SSO/LTI on Live, not staging (CD-2, decided 2026-07-27).** SSO/LTI config is hostname-bound and a staging→live push overwrites Live, so building on Live means IT registers one Entra redirect URI, not two. Staging is reserved for post-launch update testing. Nothing is live to break yet (noindex, unannounced).

**9. Graph is split — calendar deferred, mail in scope (2026-07-27).** Calendar-write Graph (`Calendars.ReadWrite`) stays Phase 3; launch uses an .ics "add to calendar" download. Mail-send Graph (`Mail.Send`) is still needed for launch — it's how WordPress sends notifications. Two different app-registration permissions; don't conflate them.

**Confirmed at the 2026-07-27 meeting (and after):** vendor security review approved; Kinsta DPA executed; **IT-6 admin-protection sign-off received**; `topsecretuser` deleted (never logged in; CD-N1 waived). Requests 1 & 2 submitted via the Ellen email; LT-2 (Canvas↔Entra ID match) confirmed. Still open: IT-4/CD-7 (`ctle@dom.edu` access-list decision).

---

## Hard-won knowledge

**Kinsta serves stale HTML after settings changes.** Anything altering rendered output stays cached at the edge for anonymous visitors. Diagnostic pattern: request the URL with a `?v=random` query string. If the buster shows the change and the plain URL doesn't, it is cache, not configuration. Check `x-kinsta-cache` (page cache) and `ki-cf-cache-status` (Cloudflare layer) separately — they purge independently, and a second layer appears once the CDN is enabled.

**Update 2026-07-27 — resolved:** the homepage stale-`noindex` cache instance has cleared. Re-verified 07-27: the `noindex, nofollow` robots meta is present in *both* the cached (`x-kinsta-cache: HIT`) and cache-busted (`x-kinsta-cache: BYPASS`) renders, so anonymous visitors now receive the discouraged-indexing copy. No Kinsta support ticket needed.

**The redirect-loop trap.** Never enable Kinsta's redirect-all-to-primary while WordPress still believes it lives at the old hostname: WordPress canonical-redirects back to the old host, Kinsta redirects forward, and `wp-admin` becomes unreachable. Always cut `siteurl` over first. Documented in §9.

**Kinsta allows only one SSH user per environment.** You cannot create additional SSH users — but each MyKinsta company member authorizes their own key (User Settings → SSH Keys), and all authorized keys connect as that single environment user. So "recovery held by two people" (decision 1) means two MyKinsta members each with a key on file, not two SSH accounts. Kinsta's "additional users" are SFTP-only — no shell, no WP-CLI — so they are **not** a recovery path. ed25519 keys work even though Kinsta's docs only show `ssh-keygen -t rsa`. Confirmed by direct connection on both Staging and Live, 2026-07-27.

**"Kinsta serves the domain" and "WordPress knows its address" are independent settings.** Only the first had been done, while the checklist claimed both. Verify externally rather than trusting checkboxes — several were inaccurate.

**Credentials must never enter this repo.** Two plaintext passwords were removed from `kinsta_onboarding.md` on 2026-07-24 before the first commit. Confirmed never committed. Use vault pointers instead.

---

## Immediate next actions

From `STATUS_AND_ACTIONS.md` — that register is authoritative, this is the short version.

The security-hardening arc is done; what remains is lighter cleanup plus external waits.

**Unblocked, do anytime (Steven):**
1. **ME-4** — install WP Activity Log and Query Monitor (WPS Hide Login is done and verified).
2. **ME-3** — §4 cleanup: remove Hello Dolly + Akismet, delete sample content (Hello World, Sample Page) and the default comment. **Send CD-N5 first.** Leave themes alone (CD-1); do not delete the draft Privacy Policy page (§22 needs it). Core is already on 7.0.2.
3. **ME-7** — decide the PHP target (8.3 vs 8.4/8.5 — decision 4), apply it, verify PHP limits, enable Cloudflare Polish, set bandwidth alerts. (CDN is already on.)
4. **ME-10** — have Amanda launch into Live (provisions her auto-login admin), then stamp her `sis_user_id` before her first SSO.
5. **ME-8** — confirm the redacted credentials are actually recorded in the CTLE vault.
6. Add the developer's SSH key so recovery is held by two people (ME-6 redundancy).

**External clocks (submitted 2026-07-27, awaiting IT):** IT-1 (Entra app + test account) and IT-2 (`ctle-noreply@dom.edu` mailbox + Graph `Mail.Send`). Chase the **Entra turnaround estimate** — it's the missing input for CD-6. LT-1 (Canvas LTI registration) is Steven's own team to schedule.

**Blocked on the Director & Developer:** theme selection (CD-1, the biggest gap), page builder (CD-3), Events Calendar Pro license (CD-4), course catalog structure (CD-5), launch scope (CD-6), forum privacy language (CD-8).

---

## The timeline problem

**This is the thing to keep in view.** As of 2026-07-27 the launch target is August 2026. Entra SSO, the M365 mailbox, and Canvas LTI are now specified and requested (submitted 07-27) but not yet provisioned by IT. SSO gates admin elevation (§14), forums (§18), and roughly half of pre-launch verification (§23). Forums specifically cannot launch, since the requirement is that anonymous visitors see no forum content.

The proposal on the table, needing the Director's decision (CD-6): **launch the public layer in August** — home page, course catalog, events calendar, blog, search, none of which need Entra — and hold forums and LTI until SSO lands, taking the navigation-link fallback that `IMPLEMENTATION_PHASES.md` §6 already approves.

The Monday meeting is done and the Entra turnaround estimate was requested in the follow-up email; it's the missing input for the CD-6 scope decision. Take the scope question to the Director once that estimate lands.

---

## Housekeeping still open

- **ME-8** — the credential redaction is done, but confirm the passwords are actually recorded in the CTLE vault before anyone needs them
- ~~Amend `REQUIREMENTS.md` and `IMPLEMENTATION_PHASES.md` for the break-glass withdrawal~~ — **done 2026-07-24**
- ~~Correct the LTI plugin naming in `kinsta_onboarding.md` §5 and §16, **and `REQUIREMENTS.md` §17** (decision 2)~~ — **done 2026-07-24** (also corrected the same reference in `REQUIREMENTS.md` §6, which had the wrong name too)
- ~~Amend `kinsta_onboarding.md` §15 to drop SMTP AUTH as an option (decision 3)~~ — **done 2026-07-24**

---

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

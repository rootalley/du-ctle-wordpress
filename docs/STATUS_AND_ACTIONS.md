# CTLE WordPress — Status & Action Items

**Purpose:** Track project status and action items by audience. Each audience section is written to be read aloud or pasted into an email without editing.

**Maintainer:** Steven Endres · **Last updated:** 2026-07-24

---

## How to use this document

**Notify before acting.** Anything that changes a URL, removes an account, deletes content, or alters how someone logs in gets announced to the affected audience *before* it happens — not reported afterward. Pending notifications live in each audience section and move to the action register once sent.

**Action IDs.** `IT-n` (DU IT), `LT-n` (DU Learning Technologies), `CD-n` (CTLE Director + Developer), `ME-n` (Steven). IDs are permanent; closed items stay in the register with their status changed.

**Section references** (`§4`, `§13`) point to `kinsta_onboarding.md`. Request numbers point to `IT_REQUESTS.md`.

---

## Current Status Snapshot

The Kinsta account, billing, and site provisioning are complete. DNS for `ctle.dom.edu` has been delivered by DU IT. WordPress core configuration is partially done. **The critical path is now entirely external:** Entra SSO, the M365 mailbox, and Canvas LTI registration are all unstarted, and they gate roughly a third of the remaining build.

| Group | Sections | Status |
|---|---|---|
| Initial Setup | §1–§3 | ✅ Complete |
| WordPress Configuration | §4–§8 | 🟡 §4 partial; §5–§8 in progress 2026-07-24 |
| Infrastructure | §9–§12 | 🟢 **§9 complete** — site live at `https://ctle.dom.edu`, HTTPS enforced, URLs cut over, verified 07-24. §10–§12 in progress. |
| Backend & IT Integrations | §13–§16 | 🔴 Blocked on DU IT (Requests 1–2) and DU LT (Request 3) |
| Content & Features | §17–§21 | 🔴 Blocked on theme and page-builder decisions; Events Calendar Pro license |
| Privacy & Launch | §22–§23 | 🔴 Not started; forum privacy language needs OPC lead time |

**Open risk:** target launch is August 2026. See CD-6.

---

## Audience: DU IT

### Meeting — Monday 2026-07-27, 11:00, IT Project Manager

Suggested running order. Items 1–3 are the reason for the meeting; items 4–5 are disclosures that need to be said out loud rather than left in a document.

**1. Close out — DNS delivered (2 min)**

Ticket 26363781 is complete. IT created an A record to `162.159.135.42` plus a `www` CNAME; both verified resolving 2026-07-24. Certificates issued, HTTPS enforced, and the site is live at `https://ctle.dom.edu`. **Confirm the ticket can be closed — no follow-up needed.**

*(The CAA concern raised in ME-2 is resolved: no CAA records exist on `dom.edu`, so certificate issuance was never at risk. Nothing to ask IT for. Do not raise it.)*

**2. Submit two new requests (10 min)**

Hand over `IT_REQUESTS.md` Requests 1 and 2. These route to different teams; ask for them to be opened as **separate tickets the same day** so the slower one doesn't gate the faster one.

- **Request 1 — Entra ID app registration (SSO).** The single longest pole. Nothing about faculty authentication, admin elevation, or forums can proceed without it.
- **Request 2 — M365 shared mailbox `ctle@dom.edu` + Graph app registration.** Note we are specifically *not* asking for SMTP AUTH: Microsoft disables basic auth for SMTP client submission by default at the end of December 2026, about four months post-launch.

**Ask for:** a named technical contact for each, and a realistic turnaround estimate. The estimate is what determines whether the August launch scope has to change — see item 5.

**3. Status check — Kinsta DPA and vendor security file (5 min)**

`IT_REQUESTS.md` Request 5. The DPA must be executed **before any faculty data is collected**, which means before SSO goes live — not before launch day. Confirm status, and confirm the SOC 2 Type II letter and ISO 27001 certificate are on file.

**4. Disclosure — the break-glass account is withdrawn, which removes an IT duty (5 min)**

`REQUIREMENTS.md` §5 assigned DU IT the job of holding a break-glass WordPress credential (password + TOTP seed) in IT's credential vault and rotating it after use. **That requirement is withdrawn.** MyKinsta's WP Admin auto-login provisions an administrator account with *no password at all*, so there is no credential to vault, and no password-authenticated admin account left on the site.

This is a net security improvement and it removes work from IT — but it changes a requirement IT may have reviewed, so IT should hear it directly rather than discover it. Full rationale in `kinsta_onboarding.md` §7.

**One consequence IT needs to weigh in on:** MyKinsta two-factor codes for the account owner are delivered to the shared `ctle@dom.edu` mailbox. That mailbox is now effectively an administrator credential — anyone who can read CTLE mail can reach WordPress Administrator. Ask IT whether they want that access list constrained, and who currently has it.

**5. Disclosure — proactive malware scanning gap (3 min)**

If it wasn't surfaced during vendor review: Kinsta does not perform proactive automated malware scanning. Its malware service is reactive — free vendor-assisted cleanup after a confirmed compromise. The compensating control is a scanning plugin on the site. Documented at `Kinsta_Checklist.md` C-8. Whoever signs the vendor security review should see this.

**6. Timeline (5 min)**

Raise it here first, before taking it to the Director. With Entra, the mailbox, and LTI all unstarted as of July 24, the question to put to the PM is plain: *is a working Entra app registration realistic before the end of August?* The answer determines the launch scope. See CD-6 for the proposal.

### What I need back from IT

| ID | Item | Blocks |
|---|---|---|
| IT-1 | Entra app registration + test account (Request 1) | §13, §14, §18, §23 |
| IT-2 | M365 mailbox + Graph credentials (Request 2) | §15, §17 |
| IT-3 | Kinsta DPA execution confirmation (Request 5) | §22, §23 launch gate |
| IT-4 | Decision on who holds `ctle@dom.edu` mailbox access | §7 |
| IT-5 | CAA record fix for `dom.edu`, **only if** ME-2 shows one blocking Let's Encrypt | §9 SSL |

---

## Audience: CTLE Director & Developer

*To be sent after the Monday IT meeting, so it includes IT's turnaround estimates.*

### Notifications — send before acting

None of these are reversible-by-accident, and two of them will change how the Director and Developer reach the site. **Send this list before touching any of it.**

| ID | Change | Why they care | Status |
|---|---|---|---|
| CD-N1 | Delete the `topsecretuser` account | It's the temporary admin Kinsta created at install. It is the only password-authenticated Administrator left on the site. If either of them has been using it, they need to switch to MyKinsta auto-login first. | ⬜ Not sent |
| CD-N2 | Change the site URL to `https://ctle.dom.edu` | The `kinsta.cloud` address stops being canonical. Any bookmark or link they've shared changes. | ⬜ Not sent |
| CD-N3 | Change the WP login URL (WPS Hide Login) | **They cannot log in without the new path.** Send the new URL directly to each person, not to a shared channel. | ⬜ Not sent |
| CD-N4 | Password-protect the staging environment | They'll need the credentials to reach staging afterward. | ⬜ Not sent |
| CD-N5 | Delete sample content and default plugins | Hello World post, Sample Page, default comment, Hello Dolly, Akismet. Low impact, but it's site content changing without their involvement. | ⬜ Not sent |
| CD-N6 | Set PHP to 8.3 | No visible effect expected; worth stating in case the Developer has version-specific code in flight. | ⬜ Not sent |

### Decisions I need — in priority order

| ID | Decision | Why it's urgent |
|---|---|---|
| CD-1 | **Theme selection** | The largest gap in the plan. WCAG 2.1 AA compliance and DU brand approval are both launch gates, and neither can even begin until a theme exists. Remediating an inaccessible theme late is the classic way this slips. Ask the Developer whether one is already chosen. |
| CD-2 | **Build on production or staging?** | Changes §5's stated approach. Recommendation: build directly on production — nothing is live to break, and configuring SSO/LTI on a staging hostname means DU IT registers two hostnames instead of one. Staging then gets its real job: testing updates after launch. |
| CD-3 | **Page builder — Beaver Builder or Gutenberg** | Couples to CD-1; the theme choice may decide it. Blocks §21. |
| CD-4 | **Events Calendar Pro license — who purchases, against what budget line?** | Costs money, and the project budget is currently recorded as TBD. Blocks §17 entirely, which is most of the launch content. |
| CD-5 | **Course catalog: custom post type or static pages?** | `REQUIREMENTS.md` §18 open question A. Recommendation: CPT, for maintainability as the catalog grows. Blocks §20. |
| CD-6 | **August launch scope** | See below. Needs the Director's decision, not just awareness. |
| CD-7 | **Who has access to the `ctle@dom.edu` mailbox?** | That mailbox now receives the MyKinsta 2FA codes that gate WordPress Administrator access. The access list should be as short as a credential vault's would be. |
| CD-8 | **Forum privacy disclosure language** | Needs OPC consultation, which has its own lead time. Nothing about §18 can be finished without it. Start the OPC conversation now even if forums slip past launch. |

### CD-6 — the August launch conversation

State it plainly and bring a proposal, not just a problem.

With Entra, the mailbox, and LTI unstarted on July 24, SSO will not realistically be configured, tested, and validated in five weeks at a university. SSO gates admin elevation (§14), forums (§18), and about half of pre-launch verification (§23). Forums specifically cannot launch, since the requirement is that anonymous visitors see no forum content at all.

**Proposal — launch the public layer in August:** home page, course catalog, events calendar, blog, and search. None of it requires Entra. Hold forums and LTI until SSO lands, take the navigation-link fallback that `IMPLEMENTATION_PHASES.md` §6 already approves in place of LTI, and set launch events with public Zoom links so registration isn't gated behind a login that doesn't exist yet.

That is a real August launch rather than a missed one. The Director's call.

### Status I need from them

| ID | Question | For |
|---|---|---|
| CD-9 | Course catalog content — migration from the current Canvas-based site started? | §20 |
| CD-10 | Privacy policy draft — started? Has OPC or DU Legal been engaged? | §22, launch gate |
| CD-11 | Series names for the events taxonomy (Conversation Series, Faculty Seminar, etc.) | §17 |
| CD-12 | Initial forum categories, and which Canvas courses get course-specific boards | §18 |
| CD-13 | CTLE Admin training availability — needs scheduling before launch | §23 |

---

## Audience: DU Learning Technologies

| ID | Item | Note |
|---|---|---|
| LT-1 | Submit Request 3 — Canvas LTI 1.3 tool registration | Includes a correction: our earlier plan named the wrong plugin direction. WordPress is the **tool**, Canvas is the platform. The Canvas-side artifact is an LTI Developer Key. |
| LT-2 | Confirm the LTI identifier matches Entra's `employeeId` | If Canvas and Entra key on different values, faculty end up with duplicate WordPress accounts. Must be confirmed before either integration is configured. |
| LT-3 | Update the Canvas global-nav CTLE button to `https://ctle.dom.edu` | Can happen any time before launch. |
| LT-4 | **Retract the break-glass request, if it was already sent** | `IT_REQUESTS.md` Request 4 is withdrawn. Otherwise LT may provision and vault a credential nobody will use. |
| LT-5 | Ask for an LTI turnaround estimate | If it exceeds three weeks, take the navigation-link fallback deliberately rather than waiting. |

---

## Action Item Register

| ID | Owner | Item | Due | Status |
|---|---|---|---|---|
| ME-1 | Steven | Complete §9 — add domain in MyKinsta, set primary, verify SSL, force HTTPS, cut over site URL | 2026-07-24 | ✅ Done 07-24 — verified: HTTP and `www` both redirect to `https://ctle.dom.edu`, `siteurl` cut over, zero `kinsta.cloud` refs remaining |
| ME-1b | Steven | Switch Force HTTPS to **redirect-all-to-primary** | 2026-07-24 | ✅ Done 07-24 — all hostnames fold to `https://ctle.dom.edu` except `https://ductle.kinsta.cloud`, which Kinsta does not redirect. Gap accepted and documented in §9: it is `noindex`ed by Kinsta, is not a login-obfuscation bypass, and is the DNS fallback. |
| ME-1d | Steven | Set Settings → Reading → **Discourage search engines from indexing this site** | 2026-07-24 | 🟡 Set 07-24 and confirmed in origin HTML, but the **edge cache is still serving the pre-change copy** to anonymous visitors. Purge the Kinsta cache, then re-verify. |
| ME-1c | Steven | **Post-launch:** disable the `ductle.kinsta.cloud` hostname entirely. Deliberately deferred — during the build it is the fallback route into the site if `ctle.dom.edu` DNS (controlled by DU IT) breaks, and a 301 keeps the Director's and Developer's existing bookmarks working. Redirecting already closes the duplicate-content and second-login-door concerns. Revisit once DNS has been stable for several weeks and Kinsta analytics show no traffic on the old hostname. | Post-launch | ⏸ Deferred |
| ME-2 | Steven | Check `dig dom.edu CAA` before the URL cutover; escalate to IT Monday if it blocks Let's Encrypt | 2026-07-24 | ✅ Closed 07-24 — no CAA records at `ctle.dom.edu` or `dom.edu`; certificates issued and valid. Nothing for IT. |
| ME-3 | Steven | Finish §4 — remove default plugins, sample content, default comment (themes deferred to CD-1) | 2026-07-24 | ⬜ Open |
| ME-4 | Steven | §5 partial — install WPS Hide Login, WP Activity Log, Query Monitor | 2026-07-24 | ⬜ Open |
| ME-5 | Steven | Verify MyKinsta auto-login works, then re-verify after the login URL changes | 2026-07-24 | 🟡 Confirmed working 07-24 after the domain cutover. Still to do: re-verify once WPS Hide Login changes the login path (ME-4), and audit the Users list per §7. |
| ME-6 | Steven | §8 — SSH keys on file for two people; test WP-CLI recovery procedure | 2026-07-24 | ⬜ Open |
| ME-7 | Steven | §10–§12 — PHP 8.3, verify PHP limits, enable CDN + Polish, baseline manual backup | 2026-07-24 | 🟡 Partial — baseline backup taken 07-24; anonymous edge-cache HIT verified. PHP version and CDN/Polish still open. |
| ME-8 | Steven | Move the plaintext credentials in `kinsta_onboarding.md` §1/§3 into a vault | 2026-07-24 | ⬜ Open |
| ME-9 | Steven | Send CD-N1 through CD-N6 notifications before acting on any of them | Before acting | ⬜ Open |
| IT-1 | DU IT | Entra app registration + test account | Est. TBD Monday | ⬜ Open |
| IT-2 | DU IT | M365 mailbox + Graph credentials | Est. TBD Monday | ⬜ Open |
| IT-3 | DU IT | Kinsta DPA execution confirmation | Before SSO goes live | ⬜ Open |
| IT-4 | DU IT | Decision on `ctle@dom.edu` mailbox access list | 2026-07-31 | ⬜ Open |
| IT-5 | DU IT | CAA record fix, if ME-2 shows one is needed | Conditional | ✅ Closed 07-24 — not needed; no CAA records exist. Do not raise Monday. |
| LT-1 | DU LT | Canvas LTI 1.3 tool registration | Est. TBD | ⬜ Open |
| LT-4 | DU LT | Retract break-glass request if sent | 2026-07-27 | ⬜ Open |
| CD-1 | Developer | Theme selection | 2026-07-31 | ⬜ Open |
| CD-2 | Developer | Build environment decision | 2026-07-28 | ⬜ Open |
| CD-4 | Director | Events Calendar Pro license purchase | 2026-07-31 | ⬜ Open |
| CD-6 | Director | August launch scope decision | 2026-07-31 | ⬜ Open |
| CD-8 | Director | Start OPC conversation on forum privacy language | 2026-07-31 | ⬜ Open |

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 0.1.0 | 2026-07-24 | sendres | Initial version. |
| 0.1.1 | 2026-07-24 | sendres | §9 complete — closed ME-1, ME-2, IT-5; added ME-1b (redirect-to-primary still outstanding); ME-7 partial. Updated the Monday agenda: DNS ticket can be closed, CAA item withdrawn. |
| 0.1.2 | 2026-07-24 | sendres | Closed ME-1b with a documented accepted gap on the HTTPS Kinsta hostname. Added ME-1c (post-launch hostname removal) and ME-1d (discourage indexing during build, with a matching launch gate in §23). |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

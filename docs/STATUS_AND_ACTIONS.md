# CTLE WordPress — Status & Action Items

**Purpose:** Track project status and action items by audience. Each audience section is written to be read aloud or pasted into an email without editing.

**Maintainer:** Steven Endres · **Last updated:** 2026-07-29

---

## How to use this document

**Notify before acting.** Anything that changes a URL, removes an account, deletes content, or alters how someone logs in gets announced to the affected audience *before* it happens — not reported afterward. Pending notifications live in each audience section and move to the action register once sent.

**Action IDs.** `IT-n` (DU IT), `LT-n` (DU Learning Technologies), `CD-n` (CTLE Director + Developer), `ME-n` (Steven). IDs are permanent; closed items stay in the register with their status changed.

**Section references** (`§4`, `§13`) point to `kinsta_onboarding.md`. Request numbers point to `IT_REQUESTS.md`.

**Companion working files (2026-07-29).** `SELF_SERVE_CHECKLIST.md` is a disposable runbook for the tasks that depend on nobody else — it retires into this register once complete. `outbound/` holds what was actually sent to each audience, dated.

---

## Current Status Snapshot

**As of 2026-07-29, nothing is waiting on us.** Every task that depended on neither DU IT nor CTLE is complete (`SELF_SERVE_CHECKLIST.md` Part A, closed out this session). The Kinsta account, billing, provisioning, DNS, and the whole WordPress infrastructure and security layer are done. **The critical path is entirely external:** Entra SSO and the M365 mailbox were specified and requested at the 2026-07-27 IT meeting (SSO Option 1; `ctle-noreply@dom.edu` sender; Graph `Mail.Send`) and chased again 2026-07-29, but are not yet provisioned. **Canvas LTI was dropped 2026-07-28** (decision 10) in favor of a Canvas global-nav link + Entra SSO, so **SSO (IT-1) is the single remaining launch integration** — it gates forums, admin elevation, and the Canvas launch link alike.

| Group | Sections | Status |
|---|---|---|
| Initial Setup | §1–§3 | ✅ Complete |
| WordPress Configuration | §4–§8 | 🟢 **Complete on our side 2026-07-29.** §4 core 7.0.2, unused plugins deleted, themes updated (none deleted — CD-1); *sample content deliberately left to CTLE — **CD-14**, with a §23 launch gate.* §5 active: Hide Login, WP Activity Log, Query Monitor, WP Mail SMTP (config pending IT-2); inactive/pre-staged: Relevanssi, wpForo; **LTI Tool + ceLTIc deleted.** §6 XML-RPC off, open registration off, staging password-protected, and **password authentication removed outright** (`ctle-hardening.php` v1.1.0) after Kinsta confirmed its brute-force ban only covers `wp-login.php`. §7 alerting live to Steven + the Director (delivery pending IT-2), MyKinsta hygiene reviewed. §8 Steven's SSH/WP-CLI verified — **Amanda's key outstanding (ME-6)**. |
| Infrastructure | §9–§12 | 🟢 **Complete** — site live at `https://ctle.dom.edu`, HTTPS enforced, URLs cut over (07-24); PHP 8.4 + limits + cron, CDN + Polish + alerts + authenticated BYPASS, daily backups + point-in-time restore (07-28). Off-site 30-day backup deferred post-launch (ME-11). |
| Backend & IT Integrations | §13–§16 | 🔴 **Blocked on DU IT — Requests 1 and 2 only.** Request 3 (LTI) withdrawn; the Canvas side (§16) is built and awaiting one URL. |
| Content & Features | §17–§21 | 🔴 Blocked on the Director and Developer: theme (CD-1), page builder (CD-3), Events Calendar Pro license (CD-4), catalog structure (CD-5). |
| Privacy & Launch | §22–§23 | 🟡 §22 tooling done (export/erase confirmed, privacy page designated); **content and OPC review are CTLE's** (CD-8, CD-10). §23 not started — about half of it needs SSO. |

**Open risk:** target launch is August 2026, and the only lever left is IT's turnaround. See CD-6.

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

**4. Requirements change — the break-glass account is withdrawn, which removes an IT duty (5–8 min)**

**This is a formal amendment to reviewed requirements, not just a status note.** `REQUIREMENTS.md` (now v0.2.1) and `IMPLEMENTATION_PHASES.md` (now v0.2.0) were both amended on 2026-07-24. Lead with that framing so it is understood as a documented change rather than something discovered later.

*What changed:*

- **Withdrawn:** the break-glass local administrator account — a vaulted 20-character password plus TOTP seed, held by DU IT and rotated after each use.
- **Replaces it:** MyKinsta's WP Admin auto-login, which provisions a WordPress Administrator account bound to the operator's MyKinsta identity with **no WordPress password assigned**, plus an SSH/WP-CLI recovery path held by at least two people.
- **Also removed:** the Two Factor / WP 2FA plugin requirement. With faculty on Entra and administrators on MyKinsta, no local password login remains for it to protect.

*Why it is a net improvement:* the old design created a permanent, high-value, password-authenticated credential and then invested in protecting it. The new design eliminates the credential. **No privileged account on the site has a password.**

*What DU IT loses:* the credential-vault responsibility and its rotation procedure. That line is struck in `REQUIREMENTS.md` §5 → IT Responsibilities.

*Two asks for IT:*

1. **Security sign-off on the replacement protection model** — MyKinsta 2FA + obfuscated login URL + audit-log alerting on all Administrator logins and role changes — as the substitute for the previously agreed TOTP-on-break-glass model. The old sign-off no longer describes what is being built.
2. **Guidance on constraining access to the shared `ctle@dom.edu` mailbox.** It receives the MyKinsta two-factor codes that gate WordPress Administrator access, so its access list is now a security control. Ask who currently has it and whether IT wants that narrowed.

Full rationale: `REQUIREMENTS.md` §5, "Rationale — supersedes the break-glass design," and `kinsta_onboarding.md` §7.

**5. Disclosure — proactive malware scanning gap (3 min)**

If it wasn't surfaced during vendor review: Kinsta does not perform proactive automated malware scanning. Its malware service is reactive — free vendor-assisted cleanup after a confirmed compromise. The compensating control is a scanning plugin on the site. Documented at `Kinsta_Checklist.md` C-8. Whoever signs the vendor security review should see this.

**6. Timeline (5 min)**

Raise it here first, before taking it to the Director. With Entra, the mailbox, and LTI all unstarted as of July 24, the question to put to the PM is plain: *is a working Entra app registration realistic before the end of August?* The answer determines the launch scope. See CD-6 for the proposal.

### Outcomes — meeting held 2026-07-27 (with Ellen, IT)

- **SSO — Option 1 chosen.** DU IT refreshes an Entra group from the SIS faculty list; the CTLE enterprise app is gated on that group (assignment required); WordPress JIT-provisions an account on first successful sign-in. CTLE admins, director, and developer reach WordPress via **MyKinsta auto-login**, so they need not be in the group — the faculty group is the entire SSO scope. **Entra ID P1 confirmed** (group-based app assignment available). Turnaround estimate on the Entra build requested — it drives the CD-6 launch-scope call.
- **Email sender — `ctle-noreply@dom.edu`.** Dedicated shared mailbox for automated WordPress mail, kept separate from `ctle@dom.edu` (human correspondence + MyKinsta 2FA codes). On the `dom.edu` domain so it inherits existing SPF/DKIM/DMARC with no new DNS.
- **Graph — split confirmed.** Calendar Graph (`Calendars.ReadWrite`) deferred to Phase 3 (.ics / "add to calendar" download covers launch). **Mail-send Graph (`Mail.Send`) remains in scope for launch** — it's how WordPress sends the notifications above. Open: whether Steven files the mailbox + Graph ticket or IT routes it.
- **Vendor security review — approved.**
- **Kinsta DPA — executed** (closes IT-3).
- **`topsecretuser` — deleted 2026-07-27.** IT approved; never logged in (CD-N1 waived); both recovery gates verified. The site now has **no password-authenticated login** — only the two MyKinsta auto-login admins (IDs 2, 3) remain.
- **Still open from the meeting:** IT-4 / CD-7 (`ctle@dom.edu` access list). *(IT-6 signed off 2026-07-27; IT-1/IT-2 requests submitted via the Ellen email.)*

### What I need back from IT

| ID | Item | Blocks |
|---|---|---|
| IT-1 | Entra app registration + test account (Request 1) | §13, §14, §18, §23 |
| IT-2 | M365 mailbox + Graph credentials (Request 2) | §15, §17 |
| IT-3 | Kinsta DPA execution confirmation (Request 5) | §22, §23 launch gate |
| IT-4 | Decision on who holds `ctle@dom.edu` mailbox access | §7 |
| IT-6 | Security sign-off on the replacement administrator protection model | §5 requirements amendment |
| IT-5 | CAA record fix for `dom.edu`, **only if** ME-2 shows one blocking Let's Encrypt | §9 SSL |

---

## Audience: CTLE Director & Developer

*To be sent after the Monday IT meeting, so it includes IT's turnaround estimates.*

### Notifications — send before acting

None of these are reversible-by-accident, and two of them will change how the Director and Developer reach the site. **Send this list before touching any of it.**

| ID | Change | Why they care | Status |
|---|---|---|---|
| CD-N1 | Delete the `topsecretuser` account | It's the temporary admin Kinsta created at install. It is the only password-authenticated Administrator left on the site. If either of them has been using it, they need to switch to MyKinsta auto-login first. | ✅ **Done 2026-07-27 — `topsecretuser` deleted** (ID 1, reassigned to ID 3). Notification waived (account never logged in). Only two passwordless auto-login admins (IDs 2, 3) remain; §6 no-password-login goal met. |
| CD-N2 | Change the site URL to `https://ctle.dom.edu` | The `kinsta.cloud` address stops being canonical. Any bookmark or link they've shared changes. | ⬜ Not sent |
| CD-N3 | Change the WP login URL (WPS Hide Login) | **They cannot log in without the new path.** Send the new URL directly to each person, not to a shared channel. | ⬜ Not sent |
| CD-N4 | Password-protect the staging environment | They'll need the credentials to reach staging afterward. | ⬜ Not sent |
| CD-N5 | ~~Delete sample content and default plugins~~ → **Delete the four unused plugins** | Hello Dolly, Akismet, LTI Tool, ceLTIc LTI Library. **Narrowed 2026-07-29:** the sample content is no longer ours to delete — posts and pages are CTLE's domain, so it moved to CD-14. What remains is plugin removal, none of it authored by them. | ⬜ Not sent — goes out in `outbound/2026-07-29-ctle.md` as an **after-the-fact** notice (deliberate departure from notify-before-acting; do not relabel) |
| CD-N6 | Set PHP to 8.4 | No visible effect expected; worth stating in case the Developer has version-specific code in flight. | ⬜ **Not sent — change already applied 2026-07-28.** Low stakes (reversible, no visible effect, developer not yet active), but send the heads-up so it's on record. |

### Decisions I need — in priority order

| ID | Decision | Why it's urgent |
|---|---|---|
| CD-1 | **Theme selection** | The largest gap in the plan. WCAG 2.1 AA compliance and DU brand approval are both launch gates, and neither can even begin until a theme exists. Remediating an inaccessible theme late is the classic way this slips. Ask the Developer whether one is already chosen. |
| CD-2 | **Build on production or staging?** | ✅ **Decided 2026-07-27 — build SSO/LTI on Live.** Nothing is live to break, SSO/LTI are hostname-bound (so building on staging would make IT register two redirect URIs), and a staging→live push overwrites Live. Staging keeps its real job: testing updates after launch. |
| CD-3 | **Page builder — Beaver Builder or Gutenberg** | Couples to CD-1; the theme choice may decide it. Blocks §21. |
| CD-4 | **Events Calendar Pro license — who purchases, against what budget line?** | Costs money, and the project budget is currently recorded as TBD. Blocks §17 entirely, which is most of the launch content. |
| CD-5 | **Course catalog: custom post type or static pages?** | `REQUIREMENTS.md` §18 open question A. Recommendation: CPT, for maintainability as the catalog grows. Blocks §20. |
| CD-6 | **August launch scope** | See below. Needs the Director's decision, not just awareness. |
| CD-7 | **Who has access to the `ctle@dom.edu` mailbox?** | That mailbox now receives the MyKinsta 2FA codes that gate WordPress Administrator access. The access list should be as short as a credential vault's would be. |
| CD-8 | **Forum privacy disclosure language** | Needs OPC consultation, which has its own lead time. Nothing about §18 can be finished without it. Start the OPC conversation now even if forums slip past launch. |

### CD-6 — the August launch conversation

State it plainly and bring a proposal, not just a problem.

With Entra and the mailbox unstarted on July 24, SSO will not realistically be configured, tested, and validated in five weeks at a university. (Canvas LTI, also unstarted, was dropped 2026-07-28 — decision 10 — so it is no longer a factor.) SSO gates admin elevation (§14), forums (§18), and about half of pre-launch verification (§23). Forums specifically cannot launch, since the requirement is that anonymous visitors see no forum content at all.

**Proposal — launch the public layer in August:** home page, course catalog, events calendar, blog, and search. None of it requires Entra. Hold forums until SSO lands. **(LTI is no longer a factor — dropped 2026-07-28 for the Canvas nav-link + Entra SSO, decision 10 — so the launch *mechanism* is settled; only SSO provisioning gates forums.)** Set launch events with public Zoom links so registration isn't gated behind a login that doesn't exist yet.

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

*Steven — this project's infrastructure lead — also heads DU Learning Technologies, so the items below are internally owned rather than an external dependency.*

| ID | Item | Note |
|---|---|---|
| LT-1 | ~~Submit Request 3 — Canvas LTI 1.3 tool registration~~ → **Retarget the Canvas global-nav button to the CTLE SSO-initiation URL + gate its visibility on faculty** | **Re-scoped 2026-07-28 — LTI withdrawn (decision 10).** No Developer Key/registration. Remaining work: point the existing global-nav button at the SSO-initiation URL (available once SSO is configured, IT-1) and gate visibility via `declared_user_type=teacher` in the SIS `users.csv` (validated: readable client-side from `users/self/logins`). Canvas-side; DU LT owns. Merges with LT-3. |
| LT-2 | Confirm the LTI identifier matches Entra's `employeeId` | ✅ **Confirmed 2026-07-27.** **Now largely moot (LTI dropped 2026-07-28):** with no LTI launch, only the Entra identity provisions accounts. Remaining dependency unchanged — IT-1: the value stamped into `sis_user_id` must equal what Entra emits, verified at first SSO login. |
| LT-3 | Update the Canvas global-nav CTLE button to the CTLE **SSO-initiation URL** | **Now the primary launch mechanism (decision 10);** merges with the re-scoped LT-1. Target URL available once SSO is configured (IT-1). |
| LT-4 | **Retract the break-glass request, if it was already sent** | `IT_REQUESTS.md` Request 4 is withdrawn. Otherwise LT may provision and vault a credential nobody will use. |
| LT-5 | ~~Ask for an LTI turnaround estimate~~ | **Retired 2026-07-28** — LTI dropped; the nav-link + Entra SSO is the chosen mechanism, so there is no LTI timeline to estimate. |

---

## Action Item Register

| ID | Owner | Item | Due | Status |
|---|---|---|---|---|
| ME-1 | Steven | Complete §9 — add domain in MyKinsta, set primary, verify SSL, force HTTPS, cut over site URL | 2026-07-24 | ✅ Done 07-24 — verified: HTTP and `www` both redirect to `https://ctle.dom.edu`, `siteurl` cut over, zero `kinsta.cloud` refs remaining |
| ME-1b | Steven | Switch Force HTTPS to **redirect-all-to-primary** | 2026-07-24 | ✅ Done 07-24 — all hostnames fold to `https://ctle.dom.edu` except `https://ductle.kinsta.cloud`, which Kinsta does not redirect. Gap accepted and documented in §9: it is `noindex`ed by Kinsta, is not a login-obfuscation bypass, and is the DNS fallback. |
| ME-1d | Steven | Set Settings → Reading → **Discourage search engines from indexing this site** | 2026-07-24 | ✅ Done — set 07-24; edge cache re-verified 07-27: `noindex, nofollow` present in both cached (`x-kinsta-cache: HIT`) and cache-busted (`BYPASS`) renders. Stale-cache instance cleared; no Kinsta ticket needed. |
| ME-1c | Steven | **Post-launch:** disable the `ductle.kinsta.cloud` hostname entirely. Deliberately deferred — during the build it is the fallback route into the site if `ctle.dom.edu` DNS (controlled by DU IT) breaks, and a 301 keeps the Director's and Developer's existing bookmarks working. Redirecting already closes the duplicate-content and second-login-door concerns. Revisit once DNS has been stable for several weeks and Kinsta analytics show no traffic on the old hostname. | Post-launch | ⏸ Deferred |
| ME-2 | Steven | Check `dig dom.edu CAA` before the URL cutover; escalate to IT Monday if it blocks Let's Encrypt | 2026-07-24 | ✅ Closed 07-24 — no CAA records at `ctle.dom.edu` or `dom.edu`; certificates issued and valid. Nothing for IT. |
| ME-3 | Steven | Finish §4 — remove the unused plugins (themes deferred to CD-1; **sample content re-assigned to CD-14 on 2026-07-29**) | 2026-07-24 | ✅ **Done 2026-07-29** — deleted `akismet`, `hello`, `lti-tool`, `celtic-lti`; `relevanssi` retained (staged for §19); themes updated but none deleted (CD-1). Deleting the LTI pair reverses decision 10's "kept installed for optionality" — deliberate: LTI is withdrawn, both are free to reinstall, and unused plugin code on disk is surface with no upside. |
| ME-4 | Steven | §5 — install WPS Hide Login, WP Activity Log, Query Monitor | 2026-07-24 | ✅ Done 2026-07-28 — all three installed + active on Live (WPS Hide Login 1.9.18, WP Activity Log 5.6.5, Query Monitor 4.0.7). Admin-login / role-change **alerting** implemented via the free `mu-plugins/ctle-admin-alerts.php` must-use plugin (WP Activity Log's custom notifications are Premium-only); email **delivery** pending IT-2 (Graph). Also pre-staged on Live: WP Mail SMTP 4.9.0 (config pending §15/IT-2). LTI Tool 3.2.6 + ceLTIc 5.3.2 were staged then **deactivated 2026-07-28 — LTI superseded (decision 10)**; Relevanssi staged inactive. |
| ME-5 | Steven | Verify MyKinsta auto-login works, then re-verify after the login URL changes | 2026-07-24 | ✅ Done 2026-07-27 — MyKinsta auto-login re-verified on the new login path for **both Staging and Live**. Users list audited: after the `topsecretuser` deletion, only the two auto-login admins (IDs 2, 3) remain. |
| ME-6 | Steven | §8 — SSH keys on file for two people; test WP-CLI recovery procedure | 2026-07-24 | ✅ Recovery verified 2026-07-27 — SSH on Staging + Live (ed25519) and a `wp user create`/delete round-trip on staging both confirmed. Gate cleared; **`topsecretuser` deleted 2026-07-27**. Still open for full redundancy: add the developer's key so recovery is held by two people (one SSH user per env; SFTP extra users are not a WP-CLI path). |
| ME-7 | Steven | §10–§12 — PHP, verify PHP limits, enable CDN + Polish, backups | 2026-07-24 | ✅ Done 2026-07-28 — PHP 8.4 + limits + cron + `DISABLE_WP_CRON` (§10); CDN + Cloudflare Polish (Lossless) + bandwidth alerts + authenticated BYPASS (§11); daily automatic backups + point-in-time restore confirmed, manual baselines present (§12). Off-site 30-day backup split out to **ME-11** (post-launch). |
| ME-11 | Steven | §12 off-site 30-day backup — provision CTLE-controlled off-Kinsta storage + nightly job (SSH → `wp db export` → rsync off-site → prune >30d → failure alert) | Post-launch | ⏸ Deferred 2026-07-28 — no off-Kinsta storage destination yet; Kinsta 14-day + point-in-time covers the near term. Not a launch blocker; close post-launch. |
| ME-9 | Steven | Send CD-N1 through CD-N6 notifications before acting on any of them | Before acting | ⬜ Open |
| ME-10 | Steven | Admin account identity reconciliation — stamp `sis_user_id` (= Entra `employeeId`) on each auto-login admin account **before its first SSO login**, so MyKinsta and SSO resolve to one account (LTI path dropped 2026-07-28); verify DU email and role preservation | Before SSO go-live | 🟡 Steven (ID 3) and Persis (ID 2) stamped 2026-07-27; their auto-login accounts already carry DU emails. **Developer (Amanda) pending — she must first launch into Live via MyKinsta auto-login to provision her account, then be stamped + verified.** |
| ME-12 | Steven | Widen `ctle-admin-alerts.php` recipients beyond `sendres@dom.edu` — add the Director and redeploy. Deliberately **excludes** `ctle@dom.edu`: that mailbox carries the MyKinsta 2FA codes gating Administrator access, so alert and second factor would share an inbox. Revisit once IT-4 closes. | Before launch | ✅ **Done 2026-07-29** — recipients are `sendres@dom.edu` + `pdriver@dom.edu`, with an `is_email` guard so a half-edited placeholder cannot become a live recipient. Deployed to Live and confirmed loading as a must-use plugin. Delivery itself stays unverifiable until IT-2. |
| ME-14 | Steven | **Deploy `ctle-hardening.php` v1.1.0 — removes password authentication.** Compensating control for the §6 brute-force gap (Kinsta's IP ban watches `/wp-login.php` only, so the custom login path had no rate limiting). | 2026-07-30 | ✅ **Done 2026-07-29.** Staging-verified first: MyKinsta auto-login still works, and a positive-control test confirmed `wp_check_password` accepts the test user's password while `wp_authenticate` refuses the same credentials. Deployed to Live, auto-login re-verified. Amends the §7 recovery procedure — a WP-CLI password reset no longer grants login until the file is moved aside (§7, §8, §23, and `mu-plugins/README.md` all updated). |
| ME-8 | Steven | ~~Move the plaintext §1/§3 credentials into a vault~~ | 2026-07-24 | ✅ **Closed 2026-07-29 — nothing to do.** Both credentials are moot: the `topsecretuser` password died with the account (deleted 07-27), and the §1 MyKinsta owner password is the Director's own, self-recoverable via Kinsta reset to `ctle@dom.edu`, and correctly *not* redistributed (Amanda has her own company user). Git history re-scanned: neither plaintext was ever committed. |
| ME-15 | Steven | **Rotate the WPS Hide Login custom path**, then use the new one in the CD-N3 SecureTransfer. The current path was exposed outside the repo during the 2026-07-29 diagnostics, and CD-N3 has not been sent — so rotating now costs nothing and stops being free once it has. Sequence after ME-14; the path is a speed bump, not the control. | Before CD-N3 | ✅ **Done 2026-07-29** — new path set, old path confirmed 404, new path 200 anonymously, MyKinsta auto-login re-verified. The staging Basic Auth credentials were regenerated at the same time and for the same reason. |
| ME-13 | Steven | Verify the `ctle.dom.edu` TLS certificates auto-renewed (expire **2026-08-31**, inside the launch window) | 2026-08-24 | ⬜ Open — reminder set |
| IT-1 | DU IT | Entra app registration + test account | Est. TBD | 🟡 **Request submitted 2026-07-27** (Ellen email). Option 1 (SIS-faculty group gates the app; JIT provisioning); Entra ID P1 confirmed. Awaiting the app registration, test account, and turnaround estimate. |
| IT-2 | DU IT | M365 mailbox + Graph credentials | Est. TBD | 🟡 Request submitted 2026-07-27 (Ellen email) — `ctle-noreply@dom.edu` mailbox + Graph `Mail.Send`. Awaiting provisioning + credentials. |
| IT-3 | DU IT | Kinsta DPA execution confirmation | Before SSO goes live | ✅ Executed — confirmed 2026-07-27. |
| IT-4 | DU IT | Decision on `ctle@dom.edu` mailbox access list | 2026-07-31 | ⬜ Open |
| IT-6 | DU IT | **Security sign-off on the replacement administrator protection model** (MyKinsta 2FA + obfuscated login URL + Administrator login alerting), superseding the withdrawn TOTP-on-break-glass sign-off | 2026-07-31 | ✅ **Signed off 2026-07-27.** |
| IT-5 | DU IT | CAA record fix, if ME-2 shows one is needed | Conditional | ✅ Closed 07-24 — not needed; no CAA records exist. Do not raise Monday. |
| LT-1 | DU LT | ~~Canvas LTI 1.3 tool registration~~ → Retarget global-nav button to the SSO-initiation URL + faculty-gate its visibility | After SSO config (IT-1) | 🟡 **Built 2026-07-29** — `canvas/ctle-global-nav.js` implements the faculty gate (`declared_user_type` from `users/self/logins`) and the retarget, with the SSO URL isolated in one constant and an `enabled` master switch defaulting to `false` (early upload is a no-op). Remaining: beta test, the SIS `declared_user_type` column, and the real URL from IT-1. |
| LT-4 | DU LT | Retract break-glass request if sent | 2026-07-27 | ⬜ Open |
| CD-1 | Developer | Theme selection | 2026-07-31 | ⬜ Open |
| CD-2 | Developer | Build environment decision | 2026-07-28 | ✅ Decided 2026-07-27 — build SSO/LTI on Live/production; staging reserved for post-launch update testing. SSO is hostname-bound and a staging→live push overwrites Live, so building on Live means IT registers one Entra redirect URI, not two. |
| CD-4 | Director | Events Calendar Pro license purchase | 2026-07-31 | ⬜ Open |
| CD-6 | Director | August launch scope decision | 2026-07-31 | ⬜ Open |
| CD-8 | Director | Start OPC conversation on forum privacy language | 2026-07-31 | ⬜ Open |
| CD-14 | Director + Developer | **Delete or replace the WordPress sample content** — Hello World post, Sample Page, default comment | Before launch | ⬜ Open — assigned 2026-07-29. Posts and pages are CTLE's domain, so this moved off ME-3. **Nobody is scheduled to do it**, which is why §23 now carries a matching hard launch gate: a live university site must not ship with "Hello World" on it. |

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 0.1.0 | 2026-07-24 | sendres | Initial version. |
| 0.1.1 | 2026-07-24 | sendres | §9 complete — closed ME-1, ME-2, IT-5; added ME-1b (redirect-to-primary still outstanding); ME-7 partial. Updated the Monday agenda: DNS ticket can be closed, CAA item withdrawn. |
| 0.1.2 | 2026-07-24 | sendres | Closed ME-1b with a documented accepted gap on the HTTPS Kinsta hostname. Added ME-1c (post-launch hostname removal) and ME-1d (discourage indexing during build, with a matching launch gate in §23). |
| 0.1.3 | 2026-07-24 | sendres | `REQUIREMENTS.md` and `IMPLEMENTATION_PHASES.md` formally amended for the break-glass withdrawal. Rewrote Monday agenda item 4 as a requirements-change disclosure with two IT asks; added IT-6 (security sign-off on the replacement protection model). |
| 0.1.4 | 2026-07-27 | sendres | Monday live-site re-verification: closed ME-1d (stale-`noindex` cache cleared); ME-3 → partial (core now 7.0.2; sample content still present); ME-7 → Kinsta CDN confirmed enabled (default) and PHP confirmed 8.2 (target decision pending). |
| 0.1.5 | 2026-07-27 | sendres | Captured the 07-27 IT meeting: added a meeting-outcomes block; SSO Option 1 chosen (SIS-faculty group gates the app, JIT provisioning, admins via MyKinsta auto-login, Entra P1 confirmed); email sender `ctle-noreply@dom.edu`; Graph split (calendar → Phase 3, `Mail.Send` in scope); vendor security approved; IT-3 DPA executed; CD-2 decided (build on Live); CD-N1 waived and ME-6 advanced (SSH confirmed both environments); IT-6 noted still open. |
| 0.1.6 | 2026-07-27 | sendres | Added ME-10 (multi-path admin identity reconciliation). Linking meta key named `sis_user_id`; Steven (ID 3) and Persis (ID 2) stamped; developer to launch into Live and be stamped before first SSO. |
| 0.1.7 | 2026-07-27 | sendres | LT-2 confirmed — Canvas and Entra key on the same identifier, so SSO and LTI will not create duplicate accounts. |
| 0.1.8 | 2026-07-27 | sendres | IT-1/IT-2 requests submitted (Ellen email sent); IT-6 signed off; ME-6 recovery verified (clears the `topsecretuser` deletion gate); ME-4 WPS Hide Login done (old login path edge-verified 404, `/wp-admin/`→`/404/`); ME-5 re-verify auto-login on the new path still pending. Noted Steven also heads DU Learning Technologies. |
| 0.1.9 | 2026-07-27 | sendres | `topsecretuser` deleted (reassigned to ID 3) — no password-authenticated login remains (§6 goal met). ME-5 closed (auto-login re-verified on the new path, both environments; Users list audited: only IDs 2, 3 remain). ME-4 new login URL confirmed 200 anonymously. |
| 0.1.10 | 2026-07-28 | sendres | Build session (all on Live): closed ME-4 — WP Activity Log 5.6.5 + Query Monitor 4.0.7 installed/active; pre-staged WP Mail SMTP 4.9.0, LTI Tool 3.2.6, ceLTIc LTI Library 5.3.2 (config deferred to §15/§16). Admin-login / role-change alerting implemented as the free `mu-plugins/ctle-admin-alerts.php` must-use plugin (WP Activity Log notifications are Premium-only); email delivery pending IT-2. Live re-verified externally (site 200, redirects, noindex, WP 7.0.2, TLS→2026-08-31). |
| 0.1.11 | 2026-07-28 | sendres | Build session cont'd: §6 baseline (open registration off; XML-RPC disabled via `ctle-hardening.php` mu-plugin — verified 403 at Nginx + X-Pingback removed); §10 PHP 8.4 + limits/cron verified + `DISABLE_WP_CRON`; §11 CDN Polish (Lossless) + bandwidth alerts + authenticated BYPASS; §12 daily backups + point-in-time restore confirmed. Closed ME-7; added ME-11 (off-site 30-day backup, post-launch). Decision 4 resolved (PHP 8.4). Relevanssi staged (inactive) pending content phase. |
| 0.1.12 | 2026-07-28 | sendres | **LTI withdrawn (decision 10):** the Canvas global-nav link + Entra SSO replaces LTI 1.3 as the launch mechanism — CTLE needs none of LTI Advantage's services, and Entra already gates access URL-agnostically. LTI Tool + ceLTIc deactivated (kept installed). Validated the Canvas button-gating via `declared_user_type` read from `users/self/logins` (non-admin readable). Re-scoped LT-1/LT-3 (retarget the global-nav button to the SSO-initiation URL + faculty-gate visibility), retired LT-5, noted LT-2 moot; updated the critical-path + CD-6 narrative to drop LTI. Cross-doc: REQUIREMENTS §6 (0.2.6), IT_REQUESTS Request 3 withdrawn (0.2.1), IMPLEMENTATION_PHASES §6 (0.2.3). |
| 0.1.18 | 2026-07-29 | sendres | **Part A closed out.** Register: ME-3, ME-12, ME-14, ME-15 all ✅ (ME-8 already closed as moot). Status snapshot rewritten — §4–§8 and §9–§12 are now green, §13–§16 names Requests 1–2 only, §22 split into our-tooling-done vs CTLE-content-pending. Cross-doc sweep found and fixed four real inconsistencies beyond the expected checkbox lag: (1) §7 still told users to obtain a password via "Lost your password?", which the hardening change disabled; (2) §23's recovery gate still described `wp user create` alone as sufficient; (3) `IT_REQUESTS.md` still claimed "Requests 1–5 outstanding" and listed Request 3 as a live ask with an LTI fallback; (4) Request 1's OIDC preference rested half on the withdrawn LTI integration. Also ticked §4's Discourage-indexing box, done 07-24 but never marked. `mu-plugins/README.md` updated for v1.1.0 + the recovery prerequisite. |
| 0.1.17 | 2026-07-29 | sendres | §6 brute-force finding upgraded from support statement to **direct measurement**: `POST /wp-login.php` → **403 at Kinsta's edge**; `POST` to the Live custom login path → **200**, processed. So WPS Hide Login *removed* working protection rather than adding it — sharper than "the ban doesn't follow the custom URL." A4b verified via positive control (correct password accepted by `wp_check_password`, refused by `wp_authenticate`); noted WPS Hide Login is active on Live only, not Staging. Added **ME-15** (rotate the login path before CD-N3 — it was exposed during diagnostics and rotation is free until that notice is sent). |
| 0.1.16 | 2026-07-29 | sendres | Part A execution. **§6 brute-force gap found and closed:** Kinsta support confirmed the automatic IP ban watches `/wp-login.php` only, leaving the WPS Hide Login custom path with no rate limiting; the fix removes password authentication outright (`ctle-hardening.php` v1.1.0 → **ME-14**, staging-gated on MyKinsta auto-login still working) rather than throttling a login nobody should use. §7 recovery amended accordingly. **ME-8 closed as moot** — both redacted credentials are dead or self-recoverable, and git history re-confirmed neither plaintext was ever committed. A5 credential channel recorded as DU SecureTransfer. Cross-doc: kinsta_onboarding 0.7.5 (§6, §7), HANDOFF (hard-won knowledge). |
| 0.1.15 | 2026-07-29 | sendres | §4 cleanup re-scoped mid-session. **Plugins:** ME-3 now deletes `akismet`, `hello`, `lti-tool`, and `celtic-lti`, keeping `relevanssi` — removing the LTI pair reverses decision 10's "kept installed for optionality" (deliberate: withdrawn integration, free to reinstall, no upside to unused code on disk). **Content:** the sample post/page/comment is no longer ours — posts and pages are CTLE's domain, so it became **CD-14** with a matching hard launch gate in §23, since nobody is otherwise scheduled to remove it. CD-N5 narrowed to a plugin notice. Cross-doc: kinsta_onboarding 0.7.4 (§4, §5, §23), REQUIREMENTS 0.2.8 (§6, §17), IMPLEMENTATION_PHASES 0.2.5 (§17), HANDOFF decision 10. |
| 0.1.14 | 2026-07-29 | sendres | Self-serve session. Added `SELF_SERVE_CHECKLIST.md` (disposable runbook covering every task that depends on nobody else) and `outbound/` (dated record of what was sent to each audience). LT-1 advanced — `canvas/ctle-global-nav.js` + `canvas/README.md` built: faculty gate via `declared_user_type`, retarget behind one config constant, `enabled: false` master switch, plus the SIS `users.csv` specification. Added ME-12 (widen alert recipients; excludes `ctle@dom.edu` on 2FA-separation grounds) and ME-13 (TLS renewal check 2026-08-24). Drafted the DU IT and CTLE emails. **Note:** CD-N5 is being sent as an after-the-fact notice, a deliberate departure from notify-before-acting. |
| 0.1.13 | 2026-07-28 | sendres | Cross-doc audit sync: aligned the CD-6 narrative and ME-4 register entry to the LTI drop (plugins deactivated, not "config pending"); the image-optimization → Cloudflare Polish and audit-alerting → mu-plugin corrections landed in REQUIREMENTS (0.2.7) and IMPLEMENTATION_PHASES (0.2.4). |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*



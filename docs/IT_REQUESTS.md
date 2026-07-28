# CTLE WordPress — External Dependency Requests

**Purpose:** Ready-to-send specifications for every task owned by DU IT, DU Learning Technologies, or DU procurement. Each request is written to be complete on first send so it does not bounce back for clarification.

**Status as of 2026-07-24:** DNS delivered (ticket 26363781). Requests 1–5 outstanding.

**Why these are the critical path:** Requests 1 and 2 gate §13–§18 and most of §23 in `kinsta_onboarding.md`. At typical university turnaround these are the only items that cannot be compressed by working harder on the WordPress side.

---

## Priority & Lead Time

| # | Request | Owner | Gates | Fallback if late |
|---|---|---|---|---|
| 1 | Entra ID app registration (SSO) | DU IT — Identity | §13 → §14 → §18, most of §23 | **None.** No SSO means no authenticated users, which means no forums at launch. |
| 2 | M365 shared mailbox + Graph app | DU IT — Messaging | §15 → all of §17's email | **None.** No email means no event registration confirmations or reminders. |
| 3 | Canvas LTI 1.3 tool registration | DU LT | §16 | Yes — navigation-link redirect, already blessed in `IMPLEMENTATION_PHASES.md` §6. |
| 4 | ~~Break-glass account coordination~~ | ~~DU LT~~ | — | **Withdrawn 2026-07-24.** Do not send. |
| 5 | Kinsta DPA execution + SOC 2 on file | DU IT / Procurement | §22, §23 launch gate | **None.** Contractual gate before any faculty data is collected. |

Requests 1 and 2 usually route to different teams inside IT. Send them as **separate tickets on the same day** — do not bundle them, or the slower one holds the faster one hostage.

---

## Request 1 — Microsoft Entra ID Application Registration (SSO)

**To:** DU IT — Identity / Directory Services
**Priority:** Highest. This is the single longest pole in an August launch.

### Context

CTLE is standing up a WordPress site at `https://ctle.dom.edu` (hosted by Kinsta, SOC 2 Type II / ISO 27001). Faculty will sign in with their DU credentials. No local passwords will be issued to faculty; Entra is the sole faculty authentication path.

### Protocol preference

**We prefer OIDC over SAML**, but will implement whichever DU IT supports as standard — please confirm which. Our reasoning for OIDC: the LTI 1.3 Canvas integration (Request 3) and the Graph mail integration (Request 2) are both OAuth/OIDC-based, so a single protocol keeps one troubleshooting model across all three integrations. If DU IT standardizes on SAML for third-party apps, that is fine and we will adjust.

### What we need registered

- **Application name:** CTLE WordPress (ctle.dom.edu)
- **Redirect URI (OIDC):** `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize`
  We will confirm this string exactly once the plugin is installed. **Please confirm whether adding or amending a redirect URI later requires a new ticket** — if so, we will hold this request until the plugin is in place and the URI is verified.
- **Reply/ACS URL (SAML, if SAML is chosen):** provided by us on request once the plugin is selected. Entity ID would be `https://ctle.dom.edu`.
- **User assignment required:** Yes — restrict to the assigned group only.
- **Assigned group:** an Entra group that DU IT refreshes from the SIS current-faculty list. Access is gated on membership, and WordPress auto-provisions a Faculty account on a user's first successful sign-in (no roster feed needed on our side). CTLE admins, director, and developer reach WordPress via the hosting console (MyKinsta auto-login), so they need **not** be in this group — the faculty group is the entire SSO scope. (This is the "Option 1" model chosen at the 2026-07-27 meeting; we have confirmed Entra ID P1, so group-based app assignment is available.)

### Claims we need in the token

| Claim | Purpose | Note |
|---|---|---|
| Display name (`name`) | Shown on forum posts; re-synced on every login | Standard claim |
| Email (`email` or `preferred_username`) | Notifications; one-time account linking at setup | Standard claim |
| **DU employee identifier (`employeeId`)** | **Account primary key** — permanent record continuity across name and email changes | **Not present by default.** Must be added as an optional claim, and requires the attribute to be populated in the directory. |
| Object ID (`oid`) | Immutable fallback key | Always present; we want it regardless |

**Please flag if `employeeId` is not reliably populated** for all users in the assigned groups. If coverage is partial, we will key accounts on `oid` instead and treat `employeeId` as secondary. It is much cheaper to know this now than after accounts exist.

### What we need back

1. Tenant ID
2. Client ID
3. Client secret **and its expiry date** — plus who owns rotation and how we will be notified before it lapses. (A secret expiring silently takes faculty SSO down with no warning.)
4. OIDC discovery document URL — `https://login.microsoftonline.com/{tenant}/v2.0/.well-known/openid-configuration` — or, for SAML, the federation metadata URL and the signing certificate with its expiry.
5. Exact claim names as they will appear in the token
6. **A test account** in one of the assigned groups that CTLE can authenticate with end-to-end. We cannot validate §13 or §14 without one.
7. A named technical contact for follow-up during configuration.

---

## Request 2 — Microsoft 365 Shared Mailbox and Sending Method

**To:** DU IT — Messaging / Exchange
**Priority:** Highest, parallel with Request 1.

### What we need

A dedicated shared application mailbox **`ctle-noreply@dom.edu`** for transactional mail from the CTLE WordPress site: event registration confirmations (with `.ics` attachments), 24-hour event reminders, waitlist notifications, and forum reply notifications. This is kept separate from the human `ctle@dom.edu` mailbox (which also receives our MyKinsta 2FA codes); using an address on the already-authenticated `dom.edu` domain means no new DNS. (Sender address decided 2026-07-27.)

**Estimated volume:** 50–200 messages/day, with bursts around event reminder sends. Well inside Exchange Online's standard limits (10,000 recipients/day, 30 messages/minute), but please confirm no tenant-specific throttle applies.

### Sending method — please provision Graph, not SMTP AUTH

We are requesting **Microsoft Graph API** with an app registration granted the `Mail.Send` application permission.

We are specifically **not** requesting SMTP AUTH with basic authentication. Microsoft's revised timeline (announced January 2026) disables SMTP AUTH basic auth by default for existing tenants **at the end of December 2026** — roughly four months after our launch. Provisioning it now would mean an emergency migration during CTLE's first semester on the platform.

**Security scoping:** we understand `Mail.Send` as an application permission is tenant-wide by default. We are explicitly requesting that it be constrained with an **ApplicationAccessPolicy scoped to `ctle-noreply@dom.edu` only**, so the credential cannot send as any other mailbox in the tenant. Please confirm this scoping is applied.

This request covers **mail sending only**. The separate Outlook *calendar* Graph integration (`Calendars.ReadWrite`) is deferred to Phase 3 (see `REQUIREMENTS.md` §18 open question #2) and is **not** part of this request — at launch, event registrants get an `.ics` "add to calendar" download instead.

### What we need back

1. Confirmation the shared mailbox `ctle-noreply@dom.edu` exists
2. Tenant ID, client ID, and client secret for the mail app registration — **with the secret's expiry date and rotation owner**
3. Confirmation that `Mail.Send` is scoped by ApplicationAccessPolicy to this mailbox alone
4. Confirmation that SPF, DKIM, and DMARC alignment for `dom.edu` covers mail sent from this mailbox via Graph
5. Whether a display name of "CTLE — Dominican University" on the From address is acceptable under DU brand/messaging policy

---

## ~~Request 3 — Canvas LTI 1.3 Tool Registration~~ (Withdrawn — superseded by Canvas nav-link + Entra SSO, 2026-07-28)

**To:** DU Learning Technologies

> **Withdrawn 2026-07-28.** CTLE no longer uses LTI. Faculty reach the site from the Canvas global-nav button linked to the Entra **SSO-initiation URL**; access is gated by the Entra faculty group (Request 1), and the button's visibility by `declared_user_type` in the SIS `users.csv` import. LTI Advantage's services (grades, roster, deep-linking, embedding) are not needed, so the Developer Key, platform registration, and JWKS exchange below are all unnecessary. The **one surviving item** is retargeting the existing global-nav button to the CTLE SSO-initiation URL. See `REQUIREMENTS.md` §6 and HANDOFF decision 10. Original request preserved below for the record.

### Correction to our earlier plan

Our internal checklist named the plugin "LTI Platform for WordPress." **That is the wrong direction of integration** and we are correcting it here. That plugin makes WordPress act as the *platform* (the LMS side, embedding external tools into WordPress pages). What CTLE needs is the reverse: faculty launch *into* WordPress *from* Canvas, which makes WordPress the **tool** and Canvas the platform.

The correct software is the **LTI Tool** plugin (ceLTIc project, `wordpress.org/plugins/lti-tool/`), which depends on the **ceLTIc LTI Library** plugin. Both are free. This changes nothing about what we need from Learning Technologies, but it does mean the Canvas-side registration is a **Developer Key (LTI)** registering CTLE WordPress as an external tool.

### What we need

Register `https://ctle.dom.edu` as an LTI 1.3 tool in Canvas and return:

1. Platform issuer (typically `https://canvas.instructure.com`)
2. OIDC authentication endpoint URL
3. JWKS (public keyset) endpoint URL
4. Access token / OAuth2 endpoint URL
5. Client ID (the Developer Key ID)
6. Deployment ID

### Launch payload claims we need

- Email
- **DU employee identifier** — `lis_person_sourcedid`, or whichever claim Learning Technologies confirms carries it. **This must be the same identifier Entra passes as `employeeId` in Request 1.** If the two systems key on different values, faculty will end up with duplicate WordPress accounts. Please confirm the two match before we configure either.
- Avatar URL — not used at launch (Phase 2), but requesting it now avoids re-registering later.
- Roles claim, so we can confirm faculty map to the Faculty role

### Also needed

Update the existing Canvas global-nav CTLE button to point at `https://ctle.dom.edu`.

**If LTI registration will take more than three weeks:** tell us early. `IMPLEMENTATION_PHASES.md` §6 already designates a plain navigation-link redirect as an acceptable Phase 1 fallback, and we would rather take the fallback deliberately than miss the launch waiting.

---

## ~~Request 4 — Break-Glass Recovery Account~~ (Withdrawn)

> **Withdrawn 2026-07-24 — do not send.** MyKinsta's WP Admin auto-login replaces the break-glass account, so DU LT has nothing to hold, vault, or rotate. See `kinsta_onboarding.md` §7 for the replacement model.
>
> **If this request was already sent to DU LT, send a short retraction** — otherwise LT may spend time provisioning a credential and vault entry that will never be used.

---

## Request 5 — Kinsta DPA Execution and Compliance Documents

**To:** DU IT — Vendor Security / Procurement

This is a launch gate (`kinsta_onboarding.md` §22 and §23) and is easy to lose track of because it sits outside the technical build.

**Status check needed:**

1. Has the Kinsta Data Processing Addendum been executed? (`kinsta.com/legal/data-processing-addendum/`) — this must be complete **before any faculty user data is collected**, which means before SSO goes live, not before launch day.
2. Are the SOC 2 Type II attestation letter and ISO 27001 certificate on file with DU IT? CTLE requested these from `trust.kinsta.com` (§1, complete) — confirm receipt and sign-off.
3. Is there any remaining DU vendor security review step CTLE has not been told about?

**One known gap to disclose in the security review, if it has not already been:** Kinsta does not perform proactive automated malware scanning — its malware service is reactive (free vendor-assisted cleanup after a confirmed compromise). CTLE's compensating control is a scanning plugin on the site. This is documented in `Kinsta_Checklist.md` C-8 and should be visible to whoever signs off.

---

## Changelog

| Version | Date | Author | Notes |
|---|---|---|---|
| 0.1.0 | 2026-07-24 | sendres | Initial version. Corrected LTI plugin direction (tool, not platform); specified Graph over SMTP AUTH given the December 2026 basic-auth retirement. |
| 0.1.1 | 2026-07-24 | sendres | Withdrew Request 4 (break-glass account) — superseded by MyKinsta WP Admin auto-login. |
| 0.2.0 | 2026-07-27 | sendres | Post-IT-meeting updates. Request 1: recorded the Option 1 provisioning model (Entra group refreshed from the SIS faculty list gates the app; JIT provisioning; admins/director/developer via MyKinsta console, not the group; Entra P1 confirmed). Request 2: send-as identity is now the dedicated `ctle-noreply@dom.edu` mailbox (separate from human `ctle@dom.edu`), with the ApplicationAccessPolicy scoped to it; clarified this covers mail-send only, calendar Graph deferred to Phase 3. |
| 0.2.1 | 2026-07-28 | sendres | Withdrew Request 3 (Canvas LTI 1.3 registration) — superseded by the Canvas global-nav link + Entra SSO (no LTI). Surviving item: retarget the existing global-nav button to the SSO-initiation URL. See REQUIREMENTS.md §6 / HANDOFF decision 10. |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

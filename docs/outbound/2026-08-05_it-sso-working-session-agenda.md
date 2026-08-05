# CTLE WordPress — Entra SSO working session

**Date:** 2026-08-05
**Attending:** Steven Endres (Learning Technologies), DU IT — identity
**Goal:** leave with the Entra app registered, the group question resolved, and a date for credentials.
**Length:** 45 minutes.

---

## What is already true — no discussion needed

- Site is `https://ctle.dom.edu`, hosted on Kinsta. **Built on Live only** — the config is
  hostname-bound, so there is exactly one redirect URI to register, not one per environment.
- Entra ID P1 is confirmed available.
- Faculty reach the site from a link in the Canvas global navigation, already signed in.
- WordPress plugin: **OpenID Connect Generic** (free). Standard authorization-code flow.
- **Password authentication is removed site-wide.** Entra is not an additional login option;
  it is the only one faculty will have.
- Mail is a **separate** app registration, already issued and unrelated to this. Don't conflate.

---

## 1. The group question — 15 min, the real blocker

**`DOMFaculty` is a mailing list. Entra app assignment needs a security group.** These are
different object types, and the app cannot be gated on a distribution list.

Decide:

- [ ] Create a new security group, or mirror `DOMFaculty` into one?
- [ ] Who owns its membership, and how does it stay current — manual, SIS-driven, or dynamic
      membership rule?
- [ ] If dynamic: what attribute identifies faculty reliably in Entra?
- [ ] **Nested groups are not supported** for this. Flat membership only.

> Persis Driver (CTLE Director) owns *who may sign in* as a policy question. `DOMFaculty` is
> the proposal, not yet a decision. I need the mechanism from IT; she supplies the list.

---

## 2. App registration — 10 min

- [ ] Single tenant
- [ ] Redirect URI, exactly:
      `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize`
- [ ] **Assignment required = Yes**, gated on the group from item 1
- [ ] Confirm the OIDC discovery document URL for the tenant

---

## 3. Claims — 10 min, the part that quietly breaks things

Required in the ID token:

| Claim | Used for |
|---|---|
| `email` | account email, profile sync |
| `given_name` | display name |
| `family_name` | display name |
| **`employeeId`** | **identity matching — the important one** |

**Why `employeeId` matters.** Two accounts already exist on the site with `sis_user_id`
stamped on them (mine and Persis's). SSO matches on that value. If `employeeId` is missing or
formatted differently from what the SIS sends, the first sign-in creates a *duplicate* account
instead of landing on the existing one.

- [ ] Confirm `employeeId` is populated in Entra for faculty, and emitted as a claim
- [ ] Confirm its format matches the SIS `sis_user_id` — mine is `904238`, six digits, no
      padding or prefix

---

## 4. Handover and testing — 5 min

- [ ] Tenant ID, client ID, client secret — **individually via DU SecureTransfer**, not email
- [ ] Record the **client secret expiry date** at issue time
- [ ] **Can IT provide a test account** in the faculty group? I want to test in this order:
      IT's test account → me → Persis → Amanda Norris. A duplicate account at any step means
      `employeeId` didn't match, and I stop before anyone else signs in.
- [ ] Note: the site currently sits behind HTTP Basic Auth during the build. Browser sign-in
      carries cached credentials and works; if IT does any back-channel check against the site
      it will get a 401. That's expected, not a fault.

---

## 5. Dates — 5 min

- [ ] When can the group exist?
- [ ] When can credentials be delivered?
- [ ] Anything IT needs from me or from CTLE to start?

---

## Explicitly not asking for

- **LTI / LTI Advantage** — dropped. A Canvas global-nav link plus Entra SSO does the job.
- **Any change to password policy or MFA** — there are no passwords on this site.
- **Guest or external access** — faculty only, gated on the group.

---

## If this is also the Exchange person — 2 min, otherwise skip

The separate mail app registration is blocked. `sendMail` returns
`403 [RAOP] Access to OData is disabled`, and I've shown it is *not* the
`ApplicationAccessPolicy` — a mailbox the policy denies and one it grants fail identically, so
the app is refused before per-mailbox scope is evaluated. Suspects are the EWS/OData
application gate (`Get-OrganizationConfig | fl EwsApplicationAccessPolicy, EwsAllowList`) or a
tenant on RBAC for Applications. Details are on the existing ticket — no need to solve it here.

---

## My follow-ups after the session

1. Install and configure OpenID Connect Generic; endpoints from the discovery document
2. Credentials into `wp-config.php` constants — never the database
3. Identity key `employeeId` → `sis_user_id`; create user if none, role Subscriber; profile
   sync on every login
4. Test in the order above
5. Then Learning Technologies: real SSO URL into the Canvas global-nav script, `enabled: true`,
   beta-test against a teacher and a student, add `declared_user_type` to the nightly SIS
   `users.csv`

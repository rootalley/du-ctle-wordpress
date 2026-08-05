# DRAFT — to Aidan: mail resolved, SSO next steps

**Status:** draft, not sent. Supersedes the earlier mail-only closing reply.
**To:** Aidan (Entra admin) — the mail ticket, then the SSO work Pete handed over.

---

**Subject:** CTLE WordPress — mail is working; next steps for Entra SSO

Hi Aidan,

Two things: mail is finished, and SSO is the next piece. Pete asked me to work through the SSO
setup with you since you'd already made a start on it.

## Mail — working, nothing further needed

`sendMail` now returns **HTTP 202 Accepted** and messages are arriving. Your configuration was
correct from the start; it simply hadn't taken effect yet.

The `RestrictAccess` policy created on 8/4 took **more than 24 hours** to become effective. It
was inert when I tested two hours after it was built, and inert again six hours after that —
which is what sent me looking for a second cause that didn't exist. Microsoft documents this
propagation as "up to 30 minutes." That was not our experience, and it's worth remembering the
next time one of these looks broken.

**Please disregard the changes I proposed yesterday.** The EWS gate and RBAC for Applications
were both dead ends, and your output ruled them out correctly — there was nothing to find. I
sent you down two blind alleys on the strength of a test that couldn't mean what I thought it
did, and I'm sorry for the time that cost.

Your policy is confirmed working as intended, which I'm glad to have verified rather than
assumed:

| App sends as | Result |
|---|---|
| `ctle-noreply@dom.edu` | **202 Accepted** — delivered |
| `sendres@dom.edu` | **403 Denied** |

So the application can send as that one mailbox and nothing else. For your records, the client
secret expires **2028-08-02**; I have a reminder set a month ahead.

## SSO — where things stand after today's session with Pete

Pete and I met earlier today. He confirmed everything we're asking for is doable: a CTLE
allowlist security group populated from `DOMFaculty`, OIDC sign-in from Entra into WordPress,
and `DOMFaculty` also driving `declared_user_type` in the nightly Canvas SIS import.

He leaned toward SAML from familiarity but confirmed OIDC works fine. **We're going with
OIDC.** The WordPress plugin — OpenID Connect Generic, free and actively maintained — is
OIDC-only and is already installed on the site. SAML would mean a different plugin, a licence
cost CTLE hasn't budgeted, and a different set of URLs.

Pete mentioned you'd already started some SSO work a few days ago, so he asked me to complete
that part with you and come back to him afterwards for the Canvas SIS file.

## What I need from you

**0. First — what's already in place?** Rather than duplicate your earlier work, tell me what
exists and I'll work from that. One thing to confirm: this needs to be a **separate app
registration from the mail one**, since the two have different permissions and lifecycles.

**1. The allowlist security group.** Please check whether it already exists and create it if
not.

- It must be a **security group**. `DOMFaculty` is a mailing list, and Entra can't gate app
  assignment on a distribution list — this is the one hard blocker.
- **Flat membership.** Nested groups aren't supported for app assignment.
- Pete owns populating it from `DOMFaculty` at launch; I just need the object to exist and the
  name.

**On membership while we build.** Pete and I agreed to start the group with **Ellen as the only
member** — she's ideal for verification, being both IT and adjunct faculty — and swap to the
`DOMFaculty`-populated membership at launch.

I'd like to extend that slightly: **please also add me, Persis Driver and Amanda Norris**
during the build phase. Ellen on her own can only prove that sign-in works; she has no existing
WordPress account, so her first sign-in creates a fresh one. That exercises provisioning, not
matching. The failure I'm actually worried about — an existing account being duplicated instead
of matched — can only show up for Persis and me, because we're the two with accounts already
stamped with our Jenzabar IDs. If we can't sign in during testing, that risk goes untested
until launch day, which is precisely when I don't want to find it.

So: Ellen, Persis, Amanda and me during build; swap to `DOMFaculty` at launch.

Two related questions:

- **Is Ellen's `employeeId` populated?** Adjunct records sometimes differ from full-time ones in
  the SIS, and if hers is empty, she'd be the one person whose test result misleads us.
- **Will Pete's population from `DOMFaculty` be a one-time copy or an ongoing sync?** If it's
  one-time, faculty hired later won't be able to sign in and nobody will notice for months.

**2. App registration** — or confirmation of the existing one.

- Single tenant
- Platform: **Web** (confidential client using a client secret, not SPA or public client)
- Redirect URI, exactly:
  `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize`
- **Assignment required = Yes**, assigned to the group from step 1
- The OIDC discovery document URL for the tenant

**3. Claims — the part I'd flag as fiddly.** Needed in the ID token: `email`, `given_name`,
`family_name`, and **`employeeId`**.

`employeeId` is the one to watch. It isn't in the standard optional-claims list in the portal —
emitting it needs a **custom claims policy** on the application's service principal. Microsoft's
own documentation uses `employeeId` as its worked example for exactly this, so it's a known
path rather than an exotic one.

**Can you confirm what Entra's `employeeId` is populated with?** My understanding from past
work is that it carries the Jenzabar ID, but I'd rather have that confirmed than assume it.

Here's why it matters more than it looks. Two accounts already exist on the site with their
Jenzabar IDs stamped on them — mine is `904238`, six digits, no prefix or padding. WordPress
matches sign-ins against that value. If `employeeId` is absent from the token, or formatted
differently, the first sign-in **silently creates a duplicate account** instead of landing on
the existing one. It fails quietly rather than loudly, which is the worst way for it to fail.

If emitting `employeeId` turns out to be awkward, say so early. There are fallbacks, but they
mean re-stamping the existing accounts, and I'd much rather know beforehand.

**4. Handover.**

- Tenant ID, client ID and client secret — **individually via SecureTransfer**, not email
- The client secret's expiry date at issue, so I can diary it as I did for mail

Given the group membership above, no separate test account is needed. I'll test in this order:

1. **Ellen** — proves the end-to-end flow works with a real faculty identity
2. **Me**, then **Persis** — proves matching works, since we both have existing accounts
3. **Amanda Norris** — she'll have an account by then from the site build

**A duplicate account at any step means `employeeId` didn't match, and I stop there** rather
than let anyone else sign in and compound it.

**5. One thing that will look like a fault and isn't.** The site sits behind HTTP Basic Auth
while it's being built. Browser sign-in carries cached credentials and works normally, but any
back-channel check against the site will get a 401. That's expected, not a misconfiguration.

## Timing

I'm travelling **Thursday through Monday, back Tuesday**, with limited availability in between.
I'll pick up anything you send, just slowly. Please don't hold work for me — if you can get the
group and the registration in place while I'm out, I'll test as soon as I'm back.

Thanks again for digging into the mail side. That ruled out a real possibility even though the
answer turned out to be elsewhere.

Steven

---

## Notes for us, not for the email

- Supersedes `2026-08-05_it-ticket-mail-resolved.md`, which was mail-only and never sent.
- Three things Aidan has to answer before Job 4 can move: whether the group exists, what he
  already built, and what `employeeId` actually contains.
- **The launch-day membership swap is a cutover event, not a checkbox.** Don't do it on launch
  morning. Make the change a couple of days ahead and confirm a real `DOMFaculty` member can
  sign in — Exchange policy propagation on the mail job took over 24 hours, and there's no
  reason to assume group and assignment changes are instant either.
- The `employeeId` custom claims policy is the likeliest place this stalls. If he pushes back,
  the fallback is matching on `upn` or `oid` — but both mean re-stamping Steven's and Persis's
  accounts, so decide before credentials are issued, not after.
- Pete's remaining piece is the Canvas SIS `declared_user_type` work, which comes after SSO.

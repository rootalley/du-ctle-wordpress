# DRAFT — to Aidan: he was right, wrong app ID our end; now needs admin consent

**Status:** draft, not sent.
**To:** Aidan Acosta (DU IT, Entra admin).
**Context:** replies to his 2026-08-17 message, in which he checked the sign-in logs and found
Steven's attempts landing on the **mail** app registration (`ddf…`) rather than the SSO one
(`7b8…`), and asked whether the right app ID was in use.
**He was right.** `OIDC_CLIENT_ID` and `OIDC_CLIENT_SECRET` in `wp-config.php` both held the mail
app's values. Corrected 2026-08-18; the next sign-in reached the SSO app and its registered
redirect URI, and now stops at a consent prompt.

---

**Subject:** RE: CTLE WordPress SSO — you found it, and one more click needed

Hi Aidan,

**You were right, and thank you for digging through the sign-in logs — that was the problem.** Our
WordPress config had the mail app's client ID and secret in the single sign-on settings. Both
registrations came across on the same day and I pasted the wrong pair. Nothing was wrong on your
side: the redirect URI and the email claim were both in place exactly as you'd set them.

I've corrected it. The sign-in now reaches the right app — **CTLE WordPress Redirect** — and the
redirect URI matches, so that error is gone.

**It now stops here:**

```
Need admin approval
CTLE WordPress Redirect needs permission to access resources in your organization
that only an admin can grant.
```

**Could you grant admin consent for the app?** In Entra: *App registrations → CTLE WordPress
Redirect → API permissions → Grant admin consent for Dominican University*. It's also available
under *Enterprise applications → CTLE WordPress Redirect → Permissions*.

The app requests only `openid`, `email` and `profile` — no Graph access, no mailbox access,
nothing that reads data. Those three are ordinarily user-consentable, so I suspect this is just
the tenant's user-consent setting rather than anything specific to this registration.

**One thing to check while you're in there, so we don't need another round trip.** If
*Enterprise applications → CTLE WordPress Redirect → Properties → Assignment required* is set to
**Yes**, then the `CTLE WordPress` group needs to be assigned to the app under *Users and groups* —
which is a separate thing from the group existing and having the right members. Could you confirm
either that assignment is in place, or that assignment isn't required?

Once consent is granted I'll test again the same day and let you know.

Thanks again for finding this,
Steven

---

## Notes for us, not for the email

- **The credit is genuine and should stay in.** He diagnosed it correctly from his side with no
  access to our config, after we had told him the problem was on his end. He also spent time
  chasing it before that.
- **Own the error plainly, once, and move on.** No extended apology — he needs the next action,
  not contrition.
- The assignment question is folded in deliberately. If *Assignment required* is Yes and the group
  isn't assigned, the next failure is `AADSTS50105`, and that would be a third round trip on a
  thread already four days old.
- **Do not raise the identity-matching correction with him.** `employeeId` stays in the token;
  matching is our side's business. Same reasoning as the 08-14 note.
- Still not his: whether `DOMFaculty` population is a one-time copy or an ongoing sync (Pete and
  Persis), and the launch-day group swap (raise a week before launch, and ask then for Steven to
  stay in the group as a permanent manual addition).

## The lesson worth keeping

The 08-14 configuration was verified as *"client ID is a valid UUID; secret is 40 characters."*
Both statements were true, and both were true of the **wrong** credentials. **A shape check is not
an identity check.** Where two registrations exist in one tenant, verify which app a value belongs
to — the cheapest form of it is comparing against the other app's constants, which takes one
command and would have caught this on 08-14:

```bash
wp eval 'echo (OIDC_CLIENT_ID === CTLE_MAIL_CLIENT_ID ? "STILL MAIL" : "ok"), PHP_EOL;'
```

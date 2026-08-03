# DU IT — Entra app registration for WordPress outbound mail

**To:** Ellen Alamilla (DU IT) · **From:** Steven Endres
**Date:** 2026-08-03
**Status:** ⬜ Not sent
**Covers:** IT-2 (second half — the mailbox itself is done)

---

## Subject: CTLE WordPress — Entra app registration for outbound mail (separate from SSO)

Hi Ellen,

Thanks for getting `ctle-noreply@dom.edu` created. To finish connecting the CTLE WordPress site to it, the site needs its own app registration so it can send through Graph.

One thing up front: this should be **separate from the SSO app registration**. Different permissions, and I'd rather a problem with one not reach the other.

What the site needs:

1. A new app registration for CTLE WordPress outbound mail.
2. Microsoft Graph **`Mail.Send`**, **application** permission — app-only, not delegated. The mailbox is sign-in-blocked, so there's no user account for a delegated flow to authenticate as.
3. Admin consent granted.
4. The app **scoped to `ctle-noreply@dom.edu` only**, via an application access policy or whatever your current equivalent is. My understanding is that `Mail.Send` at the application level is otherwise tenant-wide, and we specifically don't want this site able to send as any other mailbox at DU.
5. Sent back to me: **tenant ID**, **client ID**, **client secret**, and the **secret's expiry date** so I can diary the rotation before it lapses.

Happy to do this as a working session alongside the SSO one if that's easier — I can have the WordPress side ready to test the moment the credentials exist.

Thanks,
Steven

---

## If they push back — prepared answers

**"Just use SMTP AUTH, it's simpler."**
> Microsoft disables basic authentication for SMTP client submission by default at the end of December 2026 — about four months after we launch. We'd be rebuilding it almost immediately.

**"Use a regular user account with delegated permissions instead."**
> That needs an interactive sign-in to authorise, and a shared mailbox has no password and blocked sign-in. Making it sign-in-capable would mean licensing it and creating a credential nobody should hold.

**"Can you just use the SSO app registration?"**
> Technically yes, but I'd rather not — it widens what a compromise of either one reaches. Happy to defer to you if you'd prefer one app.

**"Tenant-wide `Mail.Send` is a problem."**
> Agreed, which is why item 4 is in the list. Scope it to the one mailbox and I'm satisfied.

**"What's the plugin you're using?"**
> None for this — the common WordPress SMTP plugins only support user-delegated OAuth, which a sign-in-blocked mailbox can't do. It's about 100 lines of custom code using standard client-credentials against Graph.

---

## Notes for the sender — not part of the email

- Confirm Ellen is still the right contact for this, or whether the mailbox ticket went to a different team.
- **Do not** send this in the same message as the SSO working-session scheduling — the two app registrations should stay visibly separate so nobody merges them for convenience.
- Store the returned secret in the CTLE vault, **never** in this repo. It goes into `wp-config.php` as a constant, not the database.
- Add the secret expiry to the calendar the day it arrives (Entra caps secrets at 24 months; mail fails silently when one lapses).
- Closes the specification half of **IT-2**. The build half is the mu-plugin, which can be written before the credentials arrive.

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

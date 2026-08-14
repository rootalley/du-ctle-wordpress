# DRAFT — to Aidan: redirect URI needed, discovery question answered

**Status:** ✅ **Sent 2026-08-14.**
**To:** Aidan Acosta (DU IT, Entra admin).
**Context:** replies to his 2026-08-14 message about the SSO app registration and the OIDC discovery document. Credentials arrived by SecureTransfer the same day.
**Outcome so far:** he confirmed the `CTLE WordPress` group holds Persis, Amanda, Ellen and Steven, and that **Ellen's `employeeId` is populated**. The redirect URI itself is still awaited.

---

**Subject:** RE: CTLE WordPress SSO — one field missing, and your discovery question

Hi Aidan,

I got the CTLE SSO settings via SecureTransfer and configured things on the WordPress side. It
looks like we're nearly there — I tried to sign in, and it returned:

```
AADSTS500113: No reply address is registered for the application.
```

**Could you add this redirect URI to the registration?** That looks like the missing piece:

```
https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize
```

It'll need to be under platform **Web** rather than Single-page application, since we're using
the client secret.

**Can you also confirm `email` is in the ID token?** WordPress uses the address both for
notifications and for matching people to their existing accounts when they sign in, so that one's
load-bearing. Worth keeping `employeeId` in there as well — I need it for the Canvas piece later.

Regarding your discovery document question, there's nothing for you to do. It's published
automatically for every tenant, so there's no field for it in Entra. The URL you quoted was
exactly right, and it's mine to enter rather than yours to supply.

Once the redirect URI is in I'll test again and let you know how it goes.

Thanks,
Steven

---

## Notes for us, not for the email

- **Ready to send.** Discovery document pulled and all five endpoints confirmed; WordPress
  configured, plugin activated, redirect URI verified identical to the value quoted above.
- The `email` claim is the one thing that can still block after the redirect URI lands. With no
  userinfo endpoint set, the plugin has no second request to fall back on, so a missing `email`
  fails sign-in outright. Loud failure, which is what we want, but worth pre-empting.
- Deliberately **not** explaining the identity-matching correction to him. `employeeId` stays in
  the token; the change is entirely on our side and doesn't affect what he builds. **Don't tell
  him the ID number does the matching** — it doesn't, and that misconception is what nearly cost
  us duplicate accounts.
- **Both cut questions were answered anyway**, verbally on 2026-08-14: the group holds Persis,
  Amanda, Ellen and Steven, and Ellen's `employeeId` is populated. Nothing further to ask.
- Not re-raised here because it's Pete's and Persis's, not Aidan's: whether the `DOMFaculty`
  population is a one-time copy or an ongoing sync.
- The launch-day group swap is a cutover. Raise it a week before launch, not now — and when we do,
  ask for Steven to stay in the group as a permanent manual addition.

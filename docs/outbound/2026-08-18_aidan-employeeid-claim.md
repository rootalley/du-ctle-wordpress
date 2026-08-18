# SENT 2026-08-18 — to Aidan: SSO works; `employeeId` missing from the ID token

**Status:** **sent 2026-08-18.** Awaiting reply.
**To:** Aidan Acosta (DU IT, Entra admin).
**Context:** he granted admin consent on 2026-08-18 and sign-in was proven working the same day —
Steven's account matched on email, no duplicate created. The one thing missing is the `employeeId`
claim, which is not in the ID token. **Not blocking authentication**; it is the key for the Canvas
SIS linkage in Job 5.
**Not covered by the communications hold** — DU IT work continues normally.

---

**Subject:** RE: CTLE WordPress SSO — it works, one claim missing

Hi Aidan,

**Single sign-on is working.** Consent came through and I signed in successfully the same day — it
matched my existing WordPress account rather than creating a second one, which was the thing I was
most worried about. Thank you, and thanks again for finding the wrong client ID from your side.

**One thing didn't come through: `employeeId` isn't in the ID token.** Here's everything the token
carried:

```
email               sendres@dom.edu
preferred_username  sendres@dom.edu
given_name          Steven
family_name         Endres
name                Endres, Steven
sub, oid, tid, aud, iss, iat, nbf, exp, uti, rh, sid, ver
```

Everything sign-in needs is there, so **nothing is broken and this isn't urgent.** But `employeeId`
is the value that ties a WordPress account to the same person in Canvas and Jenzabar, so I need it
before that integration can go in.

**Could you check whether the claims policy emitting `employeeId` is applied to this registration?**
You confirmed on 08-14 that Entra holds the J1 value and that it's populated for our test users, so
I think the directory side is fine — it's whether the policy is attached to **CTLE WordPress
Redirect** specifically, and emitting into the **ID token** rather than only the access token.

**One constraint worth flagging:** it has to arrive in the ID token. Our plugin reads claims from
there, and Graph's `/oidc/userinfo` endpoint only returns a fixed set of standard claims that will
never include `employeeId` — so pointing us at userinfo would lose claims rather than add them.

**Also, no problem at all:** the granted scope came back as `openid email profile User.Read`. I
assume `User.Read` was added at the consent step. We don't use it and it does no harm — just
flagging it so you know it wasn't us.

Once the claim is arriving I'll confirm it's being stamped onto accounts correctly, then have Ellen
run a sign-in as the final check.

Thanks,
Steven

---

## Notes for the sender

**Don't accept a userinfo endpoint as the fix.** If the answer comes back as "point it at
`/oidc/userinfo`", that makes things worse: the plugin uses the userinfo response *instead of* the
ID token claims when the endpoint is set (`openid-connect-generic-client-wrapper.php:543`), and
Graph's userinfo returns a small fixed set. `OIDC_ENDPOINT_USERINFO_URL` is deliberately unset.

**What to check when he says it's fixed.** Sign in again, then read the stored claim rather than
trusting the sign-in succeeding:

```bash
wp eval '$c = get_user_option("openid-connect-generic-last-id-token-claim", 3);
echo isset($c["employeeId"]) ? $c["employeeId"] : "ABSENT", PHP_EOL;'
```

Expect `904238` for Steven — the value already stamped by hand in `sis_user_id`, which is what
makes his account the one worth testing against.

**Amanda's account (ID 4) is unstamped** and needs `sis_user_id` set once her `employeeId` is
known. That is already recorded in Job 6.

**Ellen's test comes after this**, not before — she is the only remaining unproven path (an account
provisioned rather than matched), and it is worth running once rather than twice.

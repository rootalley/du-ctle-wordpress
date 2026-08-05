# DRAFT — closing reply to DU IT: mail is working

**Status:** draft, not sent. Closes the mailbox/Graph ticket.

---

Mail is working. Your configuration was correct from the start — it just hadn't taken effect yet.

I retested this afternoon and `sendMail` returned **HTTP 202 Accepted**. Messages are arriving.
Nothing in your tenant needed changing, and please don't act on either of the theories I sent
you: the EWS gate and RBAC for Applications were both dead ends, and your output ruled them
out correctly.

**What actually happened.** The `RestrictAccess` policy you created on 8/4 took **more than 24
hours** to become effective. It was inert when I tested two hours after you built it, and inert
again when I tested six hours later — which is what sent me looking for a second cause that
did not exist. Microsoft documents this propagation as "up to 30 minutes"; other guidance says
several hours. Neither matched what we saw. Worth remembering the next time one of these looks
broken: with this particular policy, a full day of patience is a legitimate diagnostic step.

**Where I went wrong, so the record is straight.** I told you the block sat *above* your access
policy, on the reasoning that a mailbox the policy denied and one it granted were failing
identically. That inference was sound but the conclusion was wrong. While the policy was still
propagating, nothing was being evaluated, so of course both looked the same. That test only
means something once propagation is known to be complete. I sent you down two blind alleys on
the strength of it, and I'm sorry for the time that cost.

**Your policy is confirmed working as intended**, which I'm glad to have verified rather than
assumed:

| App sends as | Result |
|---|---|
| `ctle-noreply@dom.edu` | **202 Accepted** — delivered |
| `sendres@dom.edu` | **403 Denied** |

So the application can send as that one mailbox and nothing else. That scoping is exactly the
control we wanted, and it is demonstrably live rather than merely configured.

**One thing for your records:** the client secret expires **2028-08-02**. I have a reminder set
a month ahead. When it lapses, mail stops silently, so I'd rather renew early than discover it
from a user.

Thanks for the patience and for digging into the RBAC side — that ruled out a real possibility
even though the answer turned out to be elsewhere. Nothing further needed on this ticket.

---

## Notes for us, not for the ticket

- Job 3 is closed. Mail is verified end to end, including the HTML and Reply-To header paths.
- The scoping check (`sendres@dom.edu` → 403) is worth re-running any time the app registration
  changes. It is the only proof the restriction is live rather than merely present.
- Persis received one test Administrator-login alert as a side effect of verifying
  `ctle-admin-alerts.php`. Give her a one-liner so it doesn't read as a security event.

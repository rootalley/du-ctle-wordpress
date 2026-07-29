# CD-N7 — Do not push Staging to Live (urgent)

**To:** Amanda (CTLE Developer) · **Cc:** Persis (CTLE Director)
**From:** Steven Endres
**Date:** 2026-07-29
**Status:** ⬜ Not sent — **send today, do not batch into the CTLE email**

> **Why this one goes out on its own.** Every other notification in `STATUS_AND_ACTIONS.md` can wait for the next batch. This one guards against a single click that would undo roughly three weeks of security and infrastructure work, and the click looks completely routine. Amanda has done nothing wrong and there is nothing to correct — she simply has not been told that this site is configured backwards from the norm, because until today nobody had written it down.

---

## Subject: Quick heads-up before you go further in Staging — please don't use "Push to Live"

Hi Amanda,

Short but important, and it needs to reach you before you do anything else in Staging.

**Please don't use the "Push to Live" button in MyKinsta on the CTLE site.** Not on a full push, and not on a selective one either. If something you've built in Staging needs to reach the live site, send it to me and I'll move it across.

This is not a comment on your work, and it isn't a process I'm imposing for its own sake. It's that our setup is genuinely unusual, and the button behaves in a way that would surprise anyone:

**Why this site is backwards from normal.** On virtually every WordPress host — and on Kinsta generally — you build in staging and push to live, and that's exactly right. On this site we did the reverse, deliberately. Microsoft Entra SSO is tied to a specific hostname, so building it in Staging would have meant asking DU IT to register two separate redirect URLs instead of one. We decided on 27 July to do the infrastructure work directly on Live.

The consequence is that **Live now holds a large amount of work that Staging has never seen** — the security hardening, the login configuration, the PHP version, the redirect rules, the user accounts, the mail alerting. Staging was copied from Live *before* all of that existed. So a push from Staging to Live doesn't just add your work to the site; it replaces the live site with an older copy and takes the security build with it. Among other things, it would restore an administrator account we deliberately deleted.

**One extra wrinkle, since it's genuinely counterintuitive.** Kinsta lets you push selectively — files only, or specific database tables — and you'd reasonably expect "files only" to be the safe option. For most things it is. But Kinsta always carries a handful of environment settings across regardless of what you select, including the **PHP version** and the **redirect rules**. So even a careful, narrow push would still change those two on Live. There's no setting that turns that off. I only found this in the fine print of Kinsta's documentation today.

**What happens next, so this isn't just a "don't."** I'm going to:

1. Bring Staging's PHP version and redirect settings in line with Live's. Worth knowing: **Staging may currently be on an older PHP version than Live**, which means you could be building against 8.2 while the real site runs 8.4. I'd rather you find that out from me than from a bug later. I'll confirm and fix it.
2. Confirm one open question with Kinsta support about how their file push handles deletions.
3. Work out with you how to move what you've built in Staging over to Live properly — selectively, keeping your theme and content and leaving the security configuration intact.

For step 3, whenever you have a few minutes, it would help to know: **which theme you're using, whether you're using a page builder, and roughly what you've built so far** (theme files, pages and posts, uploaded media, plugin settings). That tells me exactly what needs to move and what has to be re-applied by hand.

Nothing has been lost and nothing is broken — I caught this while reviewing the setup, not because anything went wrong. I'd just rather get the heads-up to you now than after a push.

Happy to talk it through if it's easier than email.

Thanks,
Steven

---

## Notes for the sender — not part of the email

- **Tone is deliberate.** The risk here is a correct habit meeting an unusual configuration. Anything that reads as a warning about carelessness is both unfair and likelier to get skimmed.
- **Confirm Amanda's address** before sending.
- **Do not include** the custom login path or the staging password. Those go via DU SecureTransfer (CD-N3, A5), separately and individually.
- **The PHP-version disclosure is the part most likely to earn goodwill** — it's a real problem for her that she'd otherwise hit blind, and it makes the email useful rather than merely restrictive.
- Consider following up by phone or chat if there's any chance she is mid-build today. Email latency is the weak point in this control.
- Once sent, mark CD-N7 sent in `STATUS_AND_ACTIONS.md` and record her answers against **ME-18**, which cannot be scoped without them.

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

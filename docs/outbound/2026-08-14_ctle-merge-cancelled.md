# DRAFT — to Persis and Amanda: the merge is off, Live is the build environment

**Status:** draft, not sent.
**To:** Persis Driver, Amanda Norris.
**Why this exists:** the merge sequence was committed to in writing on 2026-08-04
(`2026-08-04_ctle.md`), including a request for a maintenance window. Withdrawing it should be in
writing too, so nobody holds a window open for an operation that isn't happening.

---

**Subject:** CTLE WordPress — good news, the merge is cancelled

Hi both,

Short version: **Amanda's answer about Staging saved us a job.** No maintenance window is needed,
and there's nothing left to schedule.

## What changed

I'd planned a careful transfer of everything on Staging into the production site — a couple of
hours, done together, with a rollback plan, because I'd assumed Staging held the site build.

Amanda confirmed it doesn't: she'd used it for basic testing rather than development, and the
pages on it are the theme's demo content. **So there's nothing to move.** The whole operation goes
away, along with its risk.

**Please disregard the maintenance window request from 8/4.** Nothing to book.

## What happens instead

**Production is now the build environment.** That was always the plan for after the merge; we just
arrive there without the merge.

**Amanda — three things when you get a chance, none urgent:**

1. **Create your profile on the production site** — in MyKinsta, choose the Live environment and
   *Create admin and log in*. Takes a minute. I'll finish the setup on my side afterwards.
   You'll need this before single sign-on testing anyway.
2. **Your SSH public key**, so you're not dependent on me for recovery access.
3. **A question about the theme, and it's genuinely open.** You picked `educational-university`.
   Nobody has assessed it against WCAG 2.1 AA, and I'd parked that question earlier specifically
   because changing theme would have meant throwing away your work. **That's no longer true —
   you're starting fresh either way, so this is the one moment when changing course is free.**

   If you're happy with it, that's a fine answer and we'll handle accessibility as a review later.
   I'm not steering you off it. I just didn't want to raise it in a month when it would cost you
   real work, having stayed quiet now.

**One thing to know about the site's address.** Production is at `https://ctle.dom.edu` and
working. The password protection currently isn't on, because it interferes with the single sign-on
testing I'm doing this week. There's nothing on the site but placeholder content, so that's fine
for now — **I'll turn it on before you start putting real content up**, and send you both the
credentials then.

## Where the rest of it is

**Single sign-on is the active piece and it's moving well.** IT delivered everything on their side
today, faster than I'd budgeted for. I'm configuring and testing this week. When it's ready I'll
ask each of you to sign in once so we can confirm you land on your existing account rather than a
new one.

**Persis — one question still sitting with you**, no rush: once we launch, faculty access is
driven by a list. Who maintains it? If it syncs automatically from the faculty directory, nothing
to do. If it's maintained by hand, then faculty hired later won't be able to sign in and nobody
will notice for months. Worth deciding before launch rather than after.

And the alert email you got on 8/5 about an Administrator login — **that was me testing.** Sorry,
I should have said at the time.

Steven

---

## Notes for us, not for the email

- **Send after the Aidan reply**, not before. If his registration turns out to be incomplete the
  SSO paragraph needs softening.
- The theme question is asked **once**, framed as genuinely open, and then dropped whatever the
  answer is. If she stays with `educational-university`, WCAG goes back to being a Job 6 audit
  item and doesn't get re-raised in the meantime.
- Amanda's Live account matters more than it sounds now that matching is on email — she needs an
  account carrying `anorris@dom.edu` *before* her first SSO sign-in, or she gets a duplicate.
- Deliberately not explaining the identity-matching correction to them. It's our internal
  mechanics and doesn't change anything they do.
- Basic Auth timing is a reversal of what the 08-04 email implied. Stated plainly rather than
  quietly changed.

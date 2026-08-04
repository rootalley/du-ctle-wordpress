# Session handoff — start here

**Read `PLAN.md` first.** It has one job at the top. Do that job.

Everything else in this file is for a new Claude session picking up cold.

---

## Prompt to start a session

```
Read PLAN.md and docs/AS_BUILT.md in this repo, then tell me what Job I'm on
and what the next single action is. Don't summarise the whole plan back to me.
```

ADHD mode is always-on via `~/.claude/.i-have-adhd-always`, so it needs no invoking. If a session starts producing walls of prose or lists of parallel options, say **"adhd mode"** to pull it back.

---

## The project in one paragraph

Dominican University's Center for Teaching and Learning Excellence is building a WordPress site at `https://ctle.dom.edu`, hosted on Kinsta (Single 20GB, $350/yr). Faculty authenticate through Microsoft Entra SSO and arrive from a link in the Canvas global navigation, already signed in.

**Steven Endres** (`sendres@dom.edu`, he/him) — Director of Learning Technologies, Office of the Provost. Owns infrastructure here, and also heads DU Learning Technologies, so Canvas-side work is internal rather than an external dependency. **Persis Driver** (`pdriver@dom.edu`) — CTLE Director; owns content, policy, and who may sign in. **Amanda Norris** (`anorris@dom.edu`) — developer; owns the theme and site build. DU IT owns identity and mail.

**How each audience is reached.** DU IT via **tickets** — one for the mailbox, one for SSO — not email. CTLE via email to Persis and Amanda together; the record of what was actually sent lives in `docs/outbound/`. Credentials never travel by either route: login path, Basic Auth pairs, and client secrets go individually via DU SecureTransfer.

---

## Which file is what

**Live documents — keep these current:**

| File | Purpose |
|---|---|
| `PLAN.md` | **The only to-do list.** One job at a time. If anything contradicts it, fix this first. |
| `docs/AS_BUILT.md` | What the infrastructure *is*. Regenerate from `scripts/audit-env.sh`, don't hand-edit. |
| `docs/REQUIREMENTS.md` | The agreed specification. Stakeholder-reviewed — treat changes as consequential. |
| `docs/HANDOFF.md` | This file. |

**Historical — do not edit, do not treat as current:**

`kinsta_onboarding.md` (the 24-section build record; §24's push protocol is still worth reading before any environment sync), `IT_REQUESTS.md`, `IMPLEMENTATION_PHASES.md`, `Kinsta_Checklist.md`, `VENDOR_REQS.md`. These record how decisions were reached. Several contain statements that were true when written and are not now.

**Working directories:** `mu-plugins/` (source of truth for deployed must-use plugins), `canvas/` (global-nav script, not yet uploaded), `docs/outbound/` (what was sent to whom, dated), `docs/audit/` (**gitignored** — raw captures contain staff emails), `scripts/`.

---

## Things that will bite you

**Live and Staging have no common ancestor.** Both were provisioned at account setup and diverged independently. Live holds all infrastructure and security; Staging holds Amanda's site build. **No Kinsta push is safe in either direction** — and Kinsta carries environment settings (redirects, PHP, Nginx) *unconditionally*, even on a files-only push, so "push files only" is not the safeguard it appears to be.

**Password authentication is removed site-wide.** `ctle-hardening.php` unhooks core's authenticators. A WP-CLI password reset grants nothing until that file is moved aside — see the recovery sequence in `AS_BUILT.md`. MyKinsta auto-login is the only interactive way in.

**Kinsta's brute-force ban watches `/wp-login.php` and only that.** WPS Hide Login therefore *removed* protection rather than adding it. Measured directly: `POST /wp-login.php` → 403 at the edge; `POST` to the custom path → 200, processed. Don't assume a vendor's awareness of one setting extends across its features.

**`wp eval` reports the CLI SAPI's PHP limits, not the site's.** Read web values from Site Health → Info → Server.

**Kinsta serves stale HTML after settings changes.** Diagnose with a `?v=random` cache-buster; check `x-kinsta-cache` and `ki-cf-cache-status` separately.

**Never commit credentials.** Not the login path, not Basic Auth pairs, not client secrets. Vault or SecureTransfer only.

---

## Decisions not to relitigate

1. **No break-glass account.** MyKinsta auto-login provisions administrators with no password at all. Consequence: `ctle@dom.edu` receives the 2FA codes, so that mailbox's access list is a security control.
2. **Build on Live, not Staging.** SSO is hostname-bound, so this means IT registers one redirect URI. Corollary, learned the hard way: **Live is the source of truth.**
3. **LTI dropped.** A Canvas global-nav link plus Entra SSO does the job; CTLE needs none of LTI Advantage's services.
4. **Password authentication removed** rather than rate-limited. Rejected: Limit Login Attempts Reloaded — right for a site that needs password login; this one doesn't.
5. **Mail via a custom Graph mu-plugin.** WP Mail SMTP's Microsoft 365 mailer is Pro-only *and* delegated-only; a sign-in-blocked shared mailbox cannot complete a delegated flow.
6. **Merge by WP-CLI export/import, not Kinsta push.** The audit found no custom tables on Staging, so a content-level transfer is sufficient and carries none of a push's blast radius.

---

## Deliberately deferred — don't re-raise

Decided 2026-08-04. Both are real; the call is to deliver the platform first and volunteer help afterwards. Flagging them again before Jobs 2–4 close is noise, not diligence.

- **Theme accessibility (WCAG 2.1 AA) and DU brand review** — `educational-university` 0.3.5 is unassessed. A genuine schedule risk, but it belongs to content rather than platform.
- **Launch date** — not restated since DU IT delivered.

---

## How Steven works

ADHD. One action at a time with a time estimate — never a menu of parallel options. Batch the *commands within a job*; don't batch jobs. Terse by default; he'll ask for depth. Technically fluent — no beginner explanations of WP-CLI, SSH, or the Canvas API. Draft outbound email, never send it. Name explicitly what is *not* his, because unbounded background worry is its own load.

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

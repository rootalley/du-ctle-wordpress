# Session handoff — start here

**Read `PLAN.md` first.** It has the current job at the top. Do that job.

Everything else in this file is for a new Claude session picking up cold.

**Last archive check:** 2026-08-06 — both environments recaptured. **Partially re-verified 2026-08-14**, when both of that check's blockers turned out to be closed and SSO was configured. **A full `scripts/audit-env.sh` recapture is owed** — run it once SSO signs in successfully, and regenerate `AS_BUILT.md` from it.

---

## Prompt to start a session

```
Read PLAN.md and docs/AS_BUILT.md in this repo, then tell me what Job I'm on
and what the next single action is. Don't summarise the whole plan back to me.
```

ADHD mode is always-on via `~/.claude/.i-have-adhd-always`, so it needs no invoking. If a session starts producing walls of prose or lists of parallel options, say **"adhd mode"** to pull it back.

---

## Where things stand — end of 2026-08-14

**Done:** communications (Job 1), **mail (Job 3)**, and as of today **the entire WordPress side of SSO (Job 4)**.

**Both 08-06 blockers turned out to be closed**, one by re-testing and one by an answer from Amanda:

- **B1 — DNS is fine.** `ctle.dom.edu` → `162.159.135.42`, `www` CNAME present, HTTPS 200, certificate valid to 2026-08-31. The 08-06 NXDOMAIN came from `ns1.dom.edu`, a hostname that no longer resolves at all, so whether the record was ever truly missing can't be established. Don't spend time on it.
- **B2 — Staging holds nothing.** Amanda used it for basic testing only; she never developed the site there. What's on it is `educational-university` demo content. No backup needed, no Kinsta ticket, **and the merge that Job 2 used to be is cancelled outright.**

**SSO is configured, activated and verified on Live.** Constants written, plugin active, JWKS and issuer verification on, redirect URI confirmed identical to what Entra needs, MyKinsta auto-login re-confirmed afterwards in a fresh session. Aidan has confirmed the `CTLE WordPress` group holds the right four people and that Ellen's `employeeId` is populated.

**One field stands between here and a working sign-in:** the Entra registration has no redirect URI (`AADSTS500113`). Asked for in writing on 08-14. **When he confirms, the next action is a 15-minute sign-in test** — the commands are at the top of `PLAN.md`.

**Two things owed by us**, both in `docs/outbound/`:

1. The CTLE note (`2026-08-14_ctle-merge-cancelled.md`) is **unsent**, and it's what asks Amanda for the Live account her SSO test depends on.
2. Persis needs telling that the 08-05 Administrator-login alert was a test.

**Waiting on other people:** Aidan for the redirect URI; Amanda for a Live profile, an SSH key and a theme answer; Persis for the post-launch group-ownership question and the account-linking deviation.

---

## The project in one paragraph

Dominican University's Center for Teaching and Learning Excellence is building a WordPress site at `https://ctle.dom.edu`, hosted on Kinsta (Single 20GB, $350/yr). Faculty authenticate through Microsoft Entra SSO and arrive from a link in the Canvas global navigation, already signed in.

---

## The people

| Person | Role | Owns |
|---|---|---|
| **Steven Endres** (`sendres@dom.edu`, he/him) | Director of Learning Technologies, Office of the Provost. **Not faculty.** | Infrastructure. Also heads DU Learning Technologies, so Canvas-side work is internal, not an external dependency. |
| **Persis Driver** (`pdriver@dom.edu`) | CTLE Director, **also faculty** | Content, policy, who may sign in, and who maintains that list after launch |
| **Amanda Norris** (`anorris@dom.edu`) | Developer, **also faculty** | The theme and the site build — **which now happens on Live** |
| **Aidan Acosta** | DU IT — Entra admin | The mail app registration and **the SSO registration, group and claims — both delivered** |
| **Pete** | DU IT — runs Jenzabar and the Canvas SIS import | `declared_user_type` in the nightly import, **after SSO works** |
| **Ellen Alamilla** | IT project-manager role, **adjunct faculty** | Nothing yet — she is the first SSO test subject |

**How each audience is reached.** DU IT via **tickets**, not email — though the Aidan thread has run as email. CTLE via email to Persis and Amanda together. The record of what was sent lives in `docs/outbound/`. Credentials never travel by either route: login path, Basic Auth pairs, and client secrets go individually via DU SecureTransfer.

**Steven is not faculty.** When the SSO allowlist group swaps to mirror `DOMFaculty` at launch, he drops out of it. Admin access survives through MyKinsta auto-login, so it is not a lockout — but his SSO sign-in stops working. That is a decision to make deliberately, recorded in Job 4.

---

## Which file is what

**Live documents — keep these current:**

| File | Purpose |
|---|---|
| `PLAN.md` | **The only to-do list.** One job at a time, blockers at the top. If anything contradicts it, fix this first. |
| `docs/AS_BUILT.md` | What the infrastructure *is*. Regenerate from `scripts/audit-env.sh`, don't hand-edit. |
| `docs/REQUIREMENTS.md` | The agreed specification. Stakeholder-reviewed — treat changes as consequential. **Where reality diverges, add a dated deviation note rather than editing the requirement**; §5 *Account linking* is the worked example. |
| `mu-plugins/README.md` | How the three must-use plugins are configured and verified. |
| `docs/HANDOFF.md` | This file. |

**Historical — do not edit, do not treat as current:**

`kinsta_onboarding.md` (the 24-section build record; §24's push protocol is still worth reading before any environment sync), `IT_REQUESTS.md`, `IMPLEMENTATION_PHASES.md`, `Kinsta_Checklist.md`, `VENDOR_REQS.md`. These record how decisions were reached. Several contain statements that were true when written and are not now.

**Working directories:** `mu-plugins/` (source of truth for deployed must-use plugins), `canvas/` (global-nav script, not yet uploaded), `docs/outbound/` (what was sent to whom, dated), `docs/audit/` (**gitignored** — raw captures contain staff emails), `scripts/`.

---

## Things that will bite you

**A push from Staging to Live would destroy production.** Both environments were provisioned at account setup and diverged independently, with no common ancestor. Live holds all infrastructure and security; Staging holds theme demo content and nothing else. Kinsta carries environment settings (redirects, PHP, Nginx) *unconditionally*, even on a files-only push, so "push files only" is not the safeguard it appears to be.

The **Live → Staging** direction is safe now that Staging is known to be disposable, and that is Kinsta's supported direction anyway.

**A plugin's "identity key" setting may not be what identifies anyone.** OpenID Connect Generic's Identity Key only names the claim that becomes a *new* account's username. Matching is hardcoded to `id_token.sub` against the plugin's own user meta, falling back to email. This plan spent five versions describing an `employeeId` → `sis_user_id` match that the plugin has never been able to perform, and it read as plausible throughout. **Read the source of anything an authentication decision rests on.** The consequence reaches `REQUIREMENTS.md`, whose §5 *Account linking* row specifies the impossible behaviour — recorded there as a dated deviation note rather than amended, because that document is stakeholder-reviewed.

**PHP reports unterminated-string errors at the end of the file, not at the fault.** A stray backtick typed into the start of a `define(` line produced *"unexpected end of file, expecting `` ` ``"* pointing seventeen lines away. When you see that error, look for the first unbalanced quote or backtick, not at the line named.

**Don't paste a shell block containing `read` into a terminal.** The lines that follow get consumed as the answers. Write the script to a file first, then run it. Relatedly, `set -e` turns a `grep -q ... && { ... }` guard into a silent exit when grep finds nothing — which looks exactly like the command doing nothing at all.

**Password authentication is removed site-wide.** `ctle-hardening.php` unhooks core's authenticators. A WP-CLI password reset grants nothing until that file is moved aside — see the recovery sequence in `AS_BUILT.md`. MyKinsta auto-login is the only interactive way in.

This is why OpenID Connect Generic was held inactive until 2026-08-14, and activated only once credentials existed and auto-login could be re-confirmed immediately afterwards. **It is active now.** If you ever touch an authentication plugin here, verify auto-login in a *fresh private session* rather than a reloaded tab — a reloaded tab only proves an existing cookie still works, which is not the thing at risk.

**Cloud policy changes can take far longer than documented to propagate.** The Exchange mail policy took **over 24 hours** against a documented "up to 30 minutes." While it was inert, a mailbox the policy granted and one it denied failed *identically* — which looked like proof the policy wasn't being consulted, and sent DU IT down two dead ends. **A discriminating test means nothing until propagation is known to be complete.** Wait a full day before concluding a correct-looking configuration is wrong.

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
5. **Mail via a custom Graph mu-plugin.** WP Mail SMTP's Microsoft 365 mailer is Pro-only *and* delegated-only; a sign-in-blocked shared mailbox cannot complete a delegated flow. Built, deployed, working; WP Mail SMTP deleted.
6. **No merge at all.** ~~Merge by WP-CLI export/import, not Kinsta push.~~ **Void as of 2026-08-14** — Amanda confirmed Staging holds nothing worth moving, so there is nothing to transfer by any method. Live is simply the build environment.
7. **OIDC, not SAML.** Pete leaned SAML from familiarity but agreed OIDC works. The installed plugin is OIDC-only, free and maintained; SAML would mean a different plugin and an unbudgeted licence.
8. **SSO matches on email.** Not on `employeeId`, because the plugin cannot. First sign-in matches the address, then stamps the subject identity; every later sign-in matches that. `employeeId` is kept as the SIS key for Canvas work.

---

## Deliberately deferred — don't re-raise

Decided 2026-08-04. The call is to deliver the platform first and volunteer help afterwards.

- **DU brand review** — belongs to content.
- **Launch date** — not restated since DU IT delivered.

**Theme accessibility has come off this list.** It was deferred because re-theming meant discarding Amanda's build; there is no build to discard. `educational-university` 0.3.5 is still unassessed for WCAG 2.1 AA, and the one cheap moment to change course is before she starts building on Live. Ask her once — see `PLAN.md` 2c — then take the answer and stop raising it.

---

## How Steven works

ADHD. One action at a time with a time estimate — never a menu of parallel options. Batch the *commands within a job*; don't batch jobs. Terse by default; he'll ask for depth. Technically fluent — no beginner explanations of WP-CLI, SSH, or the Canvas API. Draft outbound email, never send it. Name explicitly what is *not* his, because unbounded background worry is its own load.

**Verify claims against the machine rather than against this repo.** The DNS blocker existed for days while `AS_BUILT.md` recorded it as delivered. A document is evidence of what someone believed, not of what is true.

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

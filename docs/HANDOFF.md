# Session handoff — start here

**Read `PLAN.md` first.** It has the current job at the top. Do that job.

Everything else in this file is for a new Claude session picking up cold.

**Last archive check:** 2026-08-06 — both environments recaptured, every live document reconciled against them.

---

## Prompt to start a session

```
Read PLAN.md and docs/AS_BUILT.md in this repo, then tell me what Job I'm on
and what the next single action is. Don't summarise the whole plan back to me.
```

ADHD mode is always-on via `~/.claude/.i-have-adhd-always`, so it needs no invoking. If a session starts producing walls of prose or lists of parallel options, say **"adhd mode"** to pull it back.

---

## Where things stand — 2026-08-06

**Done:** communications (Job 1), and **mail (Job 3)** — `wp_mail()` delivers through Microsoft Graph as `ctle-noreply@dom.edu`, scoped by an Exchange policy to that one mailbox and verified as a live restriction rather than a configured one.

**Two blockers found by the last archive check**, both recorded as B1 and B2 at the top of `PLAN.md`:

- **B1 — `ctle.dom.edu` does not exist in DNS.** Authoritative NXDOMAIN from `ns1.dom.edu`. `AS_BUILT.md` had recorded DNS as delivered by DU IT under ticket 26363781; it is not there now. Live is reachable only at `ductle.kinsta.cloud`. **Blocks SSO testing and launch.**
- **B2 — Staging's MariaDB keeps going down.** Observed 08-04 and 08-06. Staging holds the only copy of the site build and has never had a manual backup. A verified defensive dump sits on the container; a Kinsta manual backup is still needed.

**In flight:** SSO (Job 4) is with Aidan. Whether the handover email actually went out before Steven travelled is **unconfirmed** — check sent items before assuming Job 4 has started.

**Waiting on other people:** the merge (Job 2) needs Amanda and a window; content (Job 5) needs Amanda and Persis.

---

## The project in one paragraph

Dominican University's Center for Teaching and Learning Excellence is building a WordPress site at `https://ctle.dom.edu`, hosted on Kinsta (Single 20GB, $350/yr). Faculty authenticate through Microsoft Entra SSO and arrive from a link in the Canvas global navigation, already signed in.

---

## The people

| Person | Role | Owns |
|---|---|---|
| **Steven Endres** (`sendres@dom.edu`, he/him) | Director of Learning Technologies, Office of the Provost. **Not faculty.** | Infrastructure. Also heads DU Learning Technologies, so Canvas-side work is internal, not an external dependency. |
| **Persis Driver** (`pdriver@dom.edu`) | CTLE Director, **also faculty** | Content, policy, who may sign in, and who maintains that list after launch |
| **Amanda Norris** (`anorris@dom.edu`) | Developer, **also faculty** | The theme and the site build |
| **Aidan** | DU IT — Entra admin | The mail app registration (done) and **the SSO registration, group and claims** |
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
| `docs/REQUIREMENTS.md` | The agreed specification. Stakeholder-reviewed — treat changes as consequential. |
| `mu-plugins/README.md` | How the three must-use plugins are configured and verified. |
| `docs/HANDOFF.md` | This file. |

**Historical — do not edit, do not treat as current:**

`kinsta_onboarding.md` (the 24-section build record; §24's push protocol is still worth reading before any environment sync), `IT_REQUESTS.md`, `IMPLEMENTATION_PHASES.md`, `Kinsta_Checklist.md`, `VENDOR_REQS.md`. These record how decisions were reached. Several contain statements that were true when written and are not now.

**Working directories:** `mu-plugins/` (source of truth for deployed must-use plugins), `canvas/` (global-nav script, not yet uploaded), `docs/outbound/` (what was sent to whom, dated), `docs/audit/` (**gitignored** — raw captures contain staff emails), `scripts/`.

---

## Things that will bite you

**Live and Staging have no common ancestor.** Both were provisioned at account setup and diverged independently. Live holds all infrastructure and security; Staging holds Amanda's site build. **No Kinsta push is safe in either direction** — and Kinsta carries environment settings (redirects, PHP, Nginx) *unconditionally*, even on a files-only push, so "push files only" is not the safeguard it appears to be.

**Password authentication is removed site-wide.** `ctle-hardening.php` unhooks core's authenticators. A WP-CLI password reset grants nothing until that file is moved aside — see the recovery sequence in `AS_BUILT.md`. MyKinsta auto-login is the only interactive way in. **This is why OpenID Connect Generic is installed but deliberately inactive**: activating an auth plugin with no configuration risks the one door that works.

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
6. **Merge by WP-CLI export/import, not Kinsta push.** The audit found no custom tables on Staging, so a content-level transfer is sufficient and carries none of a push's blast radius.
7. **OIDC, not SAML.** Pete leaned SAML from familiarity but agreed OIDC works. The installed plugin is OIDC-only, free and maintained; SAML would mean a different plugin and an unbudgeted licence.

---

## Deliberately deferred — don't re-raise

Decided 2026-08-04. Both are real; the call is to deliver the platform first and volunteer help afterwards. Flagging them again before Jobs 2 and 4 close is noise, not diligence.

- **Theme accessibility (WCAG 2.1 AA) and DU brand review** — `educational-university` 0.3.5 is unassessed. A genuine schedule risk, but it belongs to content rather than platform.
- **Launch date** — not restated since DU IT delivered.

---

## How Steven works

ADHD. One action at a time with a time estimate — never a menu of parallel options. Batch the *commands within a job*; don't batch jobs. Terse by default; he'll ask for depth. Technically fluent — no beginner explanations of WP-CLI, SSH, or the Canvas API. Draft outbound email, never send it. Name explicitly what is *not* his, because unbounded background worry is its own load.

**Verify claims against the machine rather than against this repo.** The DNS blocker existed for days while `AS_BUILT.md` recorded it as delivered. A document is evidence of what someone believed, not of what is true.

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

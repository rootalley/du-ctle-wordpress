# CTLE WordPress — Self-Serve Runbook

**Written:** 2026-07-29 · **Owner:** Steven Endres · **Status:** working document

> 🔴 **Status as of 2026-07-29 (later the same day): still usable, but no longer the whole list.** The environment-divergence finding reopened work on our side — **CD-N7, ME-16, ME-17, ME-18** — none of which is in this runbook. Three consequences:
>
> - **Part A is done and stays accurate.** Part B (Canvas) is unaffected — keep working it.
> - **Part C2 must not be sent as written.** The CTLE email states the foundation is complete and hands over staging access without mentioning pushes. To someone building in Staging that combination reads as an invitation to push. **Send CD-N7 first** (`outbound/2026-07-29-amanda-do-not-push.md`), and send the amended C2 after.
> - **Part D is now partly wrong** — it would re-introduce the "our queue is empty" claim that `HANDOFF.md` has since corrected. Corrected in place below.
>
> **`STATUS_AND_ACTIONS.md` is the authoritative list, not this file.** That was always true; it matters more now that the two have diverged.

**Purpose.** Close every remaining task that does **not** depend on DU IT (SSO, email) or on the CTLE Director and Developer, then send the two status/chase emails. ~~When this runbook is finished, the project's entire open-item list is external — nothing is waiting on us.~~ **That closing premise no longer holds** — see the banner above.

**Disposable.** This is a session runbook, not a reference document. When every box is checked, fold the outcomes into `STATUS_AND_ACTIONS.md` (Part D) and delete this file. Do not let it become a seventh document to keep in sync.

**Scope boundaries — deliberately excluded:**

| Excluded | Why |
|---|---|
| §13 SSO config, §14 admin elevation, §15 mail config | Blocked on IT-1 / IT-2. |
| Retargeting the Canvas button to the real SSO URL | The URL does not exist until IT-1 lands. Everything *else* about the button is in scope (Part B). |
| CD-1 theme, CD-3 builder, CD-4 license, CD-5 CPT, §17–§21 content | Director / Developer decisions. |
| **CD-14 — the WordPress sample content** (Hello World, Sample Page, default comment) | Posts and pages are CTLE's domain (decided 2026-07-29). Now a CTLE-owned item with a §23 launch gate. |
| ME-6 (Amanda's SSH key), ME-10 (Amanda's account stamp) | Require Amanda. Both are asks in the CTLE email. |
| ME-11 off-site backup, ME-1c hostname removal, HSTS | Deliberately deferred post-launch. |
| IT-4 / CD-7 `ctle@dom.edu` access list | Decision belongs to IT and the Director. Chased in both emails. |

**Order matters in two places.** Part A2 runs *before* the CTLE email in Part C2, because that email states the plugin removals are done. And Part C3 (the login URL) never goes in a shared email.

---

## Part A — Live site

All commands run over SSH on **Live**. Connection details: MyKinsta → Sites → DU-CTLE → Info → SFTP/SSH.

```bash
ssh <user>@<host> -p <port>
cd public
wp --info
```

- [X] # Confirm WP-CLI v2 and the expected PHP 8.4 CLI binary

### A1 — Take a manual backup first

- [X] MyKinsta → Sites → DU-CTLE → **Backups → Manual** → create one, label it `pre-cleanup-2026-07-29`

Everything in A2 is destructive and irreversible from the WP UI. Kinsta keeps 14 days of dailies plus point-in-time, so this is belt-and-suspenders — but a labelled restore point costs 30 seconds and makes the rest of this section safe to run without hesitation.

### A2 — ME-3: remove the unused plugins

Inactive on Live: `akismet`, `hello`, `lti-tool`, `celtic-lti`, `relevanssi`. **Delete the first four. Keep Relevanssi** — it is staged for the §19 search build.

Deleting the two LTI plugins reverses decision 10's "kept installed for optionality" clause. That is the better call: LTI is withdrawn, both plugins are free and reinstallable from wordpress.org in about a minute, and unused plugin code sitting on disk is surface area with no offsetting benefit. Note that `wp plugin delete` runs each plugin's uninstall routine — which is what we want here, since neither was ever configured and there is no LTI state to preserve.

**Themes must survive** — CD-1 has not been decided. Do not add `--all` to anything.

```bash
# 1. Inspect. Confirm the inactive set matches expectations.
wp plugin list --fields=name,status,version

# 2. Delete the four. Relevanssi is deliberately absent from this list.
wp plugin delete akismet hello lti-tool celtic-lti

# 3. Confirm: relevanssi should be the only inactive plugin left, and the
#    active set should be wps-hide-login, wp-security-audit-log,
#    query-monitor, and wp-mail-smtp.
wp plugin list --fields=name,status,version
wp plugin list --status=must-use --fields=name,status
```

- [X] Akismet, Hello Dolly, LTI Tool, and ceLTIc LTI Library deleted
- [X] Relevanssi still present and inactive
- [X] Themes untouched
- [X] Must-use plugins still loading (`ctle-admin-alerts`, `ctle-hardening`)

> Closes **ME-3**'s plugin half.

**Sample content stays — decided 2026-07-29.** Posts and pages are CTLE's domain, so the Hello World post, Sample Page, and default comment now belong to the Director and Developer to remove or replace as content gets built. Tracked as **CD-14**, with a matching §23 launch gate so the site cannot ship with "Hello World" on it. Two consequences:
>
> - **CD-N5 shrinks to a plugin notice.** The after-the-fact departure from notify-before-acting now covers only plugin removals — nothing either of them authored. Still record it as after-the-fact rather than relabeling it.
> - **Nobody is currently scheduled to delete it.** That is the risk this hands off, and it is why CD-14 needs to be a launch gate rather than an assumption.

### A3 — Widen the admin-alert recipients

The repo copy of `mu-plugins/ctle-admin-alerts.php` now lists `sendres@dom.edu` and `pdriver@dom.edu`, and drops any entry that is not a valid address so a half-edited placeholder can never become a live recipient. **It deliberately does not include `ctle@dom.edu`:** that mailbox holds the MyKinsta 2FA codes gating Administrator access, so routing Administrator-login alerts to it would put the alert and the second factor in the same inbox. Revisit once IT-4 closes.

- [X] Deploy and confirm it loaded:

```bash
scp -P <port> mu-plugins/ctle-admin-alerts.php <user>@<host>:~/public/wp-content/mu-plugins/
ssh <user>@<host> -p <port> 'cd public && wp plugin list --status=must-use'
```

- [X] `ctle-admin-alerts.php` shows in the must-use list

Delivery stays unverifiable until IT-2 lands — expected, and already tracked in §7.

### A4 — §6 residual: brute-force protection on the custom login URL

MyKinsta → live chat. Ask verbatim:

> Our site uses WPS Hide Login, so `wp-login.php` returns 404 and the login form lives at a custom path. Does Kinsta's automatic brute-force protection (IP ban after 6 failed logins per minute) follow the custom login URL, or does it only watch `wp-login.php`?

- [X] Answer received 2026-07-29 and recorded in `kinsta_onboarding.md` §6

**The answer was no, and the assumption behind this step was wrong.** Kinsta support confirmed the automatic IP ban watches `/wp-login.php` specifically. WPS Hide Login moved the form to a custom path, so the endpoint that actually accepts logins has **no rate limiting and no IP ban at all** — unlimited password attempts. Kinsta's protection now guards a path that returns 404, which is worth exactly nothing. Getting it on record was the right instinct.

Severity today is low and it does not stay low: with no password-authenticated account on the site, there is currently nothing to guess. That changes at SSO go-live, when JIT provisioning gives every faculty account a WordPress password hash.

### A4b — Close the gap: remove password authentication

Rather than bolt a rate-limiting plugin onto a login nobody is supposed to use, remove the thing being attacked. `mu-plugins/ctle-hardening.php` v1.1.0 drops the username/password and email/password authenticators, disables application passwords, and turns off password reset. This is §6's stated goal — "no account on the site can be logged into with a password" — enforced in code rather than assumed. Faculty use Entra; administrators use MyKinsta auto-login, which issues an auth cookie directly and never runs those authenticators.

**Deploy to staging first.** The one thing that must be verified is that MyKinsta auto-login still works.

```bash
# 1. Staging.
scp -P <staging-port> mu-plugins/ctle-hardening.php <user>@<staging-host>:~/public/wp-content/mu-plugins/

# 2. In MyKinsta, open the STAGING site → "Create admin and log in".
#    Confirm it lands you in wp-admin. This is the gate.

# 3. Confirm the login form can no longer authenticate: visit the custom login
#    path on staging and submit any username/password. Expect failure.

# 4. Only then, Live.
scp -P <port> mu-plugins/ctle-hardening.php <user>@<host>:~/public/wp-content/mu-plugins/
```

- [X] Staging: MyKinsta auto-login still works with v1.1.0 in place — **verified 2026-07-29.** (The username/password prompts seen during this test were HTTP Basic Auth from the A5 staging password protection, enforced at Nginx before WordPress runs — not WordPress login, and not related to this plugin. They will not appear on Live.)
- [X] Staging: password authentication refused **against a positive control** — verified 2026-07-29: `wp_check_password` returned true for the test user's correct password while `wp_authenticate` returned a `WP_Error` for the same credentials.

> ⚠️ **A bare "the login failed" proves nothing here.** No account on this site has a password, so submitting bad credentials fails identically whether or not the hardening is in place. The test needs a user who genuinely *has* a valid password and still cannot use it.
>
> ```bash
> cd public
>
> # 1. Confirm v1.1.0 actually landed — a stale v1.0.0 on disk invalidates everything below.
> wp eval 'var_dump(
>   has_filter("authenticate","wp_authenticate_username_password"),
>   has_filter("authenticate","wp_authenticate_email_password"),
>   has_filter("authenticate","wp_authenticate_application_password")
> );'
> # → bool(false) bool(false) bool(false).   Any int(20) = still running the old file.
>
> # 2. The control.
> wp user create hardening-test hardening-test@example.com --role=subscriber \
>   --user_pass='Correct-Horse-Battery-Staple-9'
>
> # 3. The password is genuinely correct...
> wp eval 'var_dump( wp_check_password( "Correct-Horse-Battery-Staple-9", get_user_by("login","hardening-test")->user_pass ) );'
> # → bool(true)
>
> # 4. ...and authentication refuses it anyway.
> wp eval 'var_dump( is_wp_error( wp_authenticate( "hardening-test", "Correct-Horse-Battery-Staple-9" ) ) );'
> # → bool(true)
>
 > # 5. End-to-end through both auth layers and the real form (OPTIONAL — steps
> #    3 and 4 already settle it; this only re-confirms the HTTP endpoint).
> #    Derive the URL on the box instead of typing it: the login path is
> #    deliberately unrecorded in this repo and should stay out of shell history.
> #    Ask WordPress, not the plugin's storage — WPS Hide Login rewrites through
> #    the site_url filter, so this is correct regardless of its option names
> #    (`whl_page` does not exist in 1.9.18).
> LOGIN_URL="$(wp eval 'echo wp_login_url();')"
> echo "$LOGIN_URL"      # sanity-check: staging, not Live
> #    If this prints .../wp-login.php, WPS Hide Login is not active in THIS
> #    environment (it was configured on Live; staging is separate). Fine — the
> #    test is about authentication, not the path. Use whatever URL is printed.
>
> # Pass -u with the USERNAME ONLY — curl then prompts for the password rather
> # than taking it from the command line. Credentials are the A5 staging
> # password-protection pair (MyKinsta → Staging → Tools → Password Protection).
> curl -sSi -u '<staging-basic-auth-user>' \
>   --cookie 'wordpress_test_cookie=WP Cookie check' \
>   -d 'log=hardening-test&pwd=Correct-Horse-Battery-Staple-9&wp-submit=Log+In' \
>   "$LOGIN_URL" \
>   | grep -iE '^HTTP/|^set-cookie: wordpress_logged_in|Invalid username'
> # → 200, no wordpress_logged_in cookie, and the invalid-credentials string.
> #   Unhardened this would be 302 + the cookie. The wordpress_test_cookie is
> #   required, or WordPress fails the cookie check instead of the auth check —
> #   which looks like a pass but tests nothing.
>
> # 6. Clean up (after step 5).
> wp user delete $(wp user get hardening-test --field=ID) --yes
> ```
>
> The create/delete fires `set_user_role`, so `ctle-admin-alerts` generates two alerts — inert on staging, which has no mail transport.

**Resolved 2026-07-29.** Steps 1–4 passed: `wp_check_password` returned true and `wp_authenticate` returned a `WP_Error` for the same correct credentials. The optional HTTP probe turned into the more useful measurement recorded in §6 — `POST /wp-login.php` is refused **403 at Kinsta's edge**, while a `POST` to the Live custom login path returns **200** and is processed. Note WPS Hide Login is **not** active on Staging, only Live; the two environments differ here.

### A4c — Rotate the custom login path before CD-N3 goes out

The Live login path was pasted outside the repo during the diagnostics above, so its obscurity value is spent. Rotating it is **almost free right now** and stops being free the moment CD-N3 is sent.

- [X] MyKinsta/WP Admin → Settings → WPS Hide Login → set a new path
- [X] Re-verify: old path → 404, new path → 200 anonymously, MyKinsta auto-login still works (allow ~1 minute for Kinsta to re-detect)
- [ ] Use the **new** path in the SecureTransfer (Part C3) — still pending, since C3 has not been sent. CD-N3 never went out with the old path, so nobody has anything to unlearn.

Do this *after* ME-14 deploys to Live, not instead of it. The path is a speed bump; removing password authentication is the actual control, and it is what makes a leaked path uninteresting.
- [X] Live: deployed, auto-login re-verified 2026-07-29
- [X] Recovery procedures updated — **resetting a password over WP-CLI no longer grants login while this file is in place.** The sequence gains one step: move `ctle-hardening.php` aside over SSH first. Documented in the file header, `mu-plugins/README.md`, §7 (the sequence), §8 (a prerequisite warning), and §23 (the launch gate now requires walking the *full* path, since testing `wp user create` alone no longer exercises it). §8 needed no procedural change of its own — it covers access setup, not recovery.

> If auto-login *does* break on staging, do not deploy to Live — fall back to installing **Limit Login Attempts Reloaded** (free, app-layer, so it follows the custom URL) and re-open this as an open item.

### A5 — CD-N4: password-protect staging

- [X] MyKinsta → Sites → DU-CTLE → **Staging** → Tools → **Password Protection** → enable
- [X] Record the credentials in the CTLE vault (not this repo)
- [X] Confirm staging prompts for credentials from a logged-out browser

Staging carries the same code as production and gets none of the attention. The credentials go to the Director and Developer **via DU SecureTransfer** (chosen 2026-07-29), alongside the custom login URL — see Part C3. Never in the shared email, never in this repo.

### A6 — §7 MyKinsta hygiene

- [X] Review the MyKinsta company user list; remove anyone no longer on the project (each MyKinsta user is a path into WP Admin)
- [X] Confirm each active person uses a per-person **Company Developer** account for routine work, so their 2FA codes go to individual DU addresses rather than the shared mailbox
- [X] Confirm the §1 billing contact is a monitored DU address and the annual renewal cannot lapse silently — a suspended Kinsta account takes the dashboard *and* SSH, the one scenario neither recovery path covers

### A7 — Update what is safe to update

Themes are updated but **not deleted** — deletion waits on CD-1.

```bash
wp core check-update
wp plugin update --all --dry-run
wp theme update --all --dry-run
# then re-run without --dry-run if the listings look as expected
wp theme list --fields=name,status,version,update
```

- [X] Core, plugins, and themes reported and updated as appropriate; no theme deleted

### A8 — ME-8: the vault entries — closed on inspection, 2026-07-29

**Neither redacted credential still matters, so there is nothing to retrieve and nothing to transfer.** Checked against §1, §3, and the full git history:

- **§3 — the `topsecretuser` WordPress password.** That account was **deleted 2026-07-27**. §3's own note anticipated this: "after which the credential becomes moot." It authenticates nothing.
- **§1 — the MyKinsta Company Owner password** for `ctle@dom.edu`. This is the Director's own account credential. It should **not** be redistributed: Persis holds it, and it is self-recoverable through Kinsta's password reset, which delivers to `ctle@dom.edu`. Amanda does not need it — §2 confirms she already has her own MyKinsta company user, which is the correct access model and the one §7 asks us to prefer.

Confirmed by scanning every commit that touched the file: neither plaintext value was ever committed. The first commit already carries the redacted "held in the CTLE credential vault" wording, exactly as `HANDOFF.md` claims.

- [X] ME-8 closed — no vault retrieval needed; nothing outstanding to record

**What the SecureTransfer to Persis and Amanda actually needs** is the custom login URL and the staging password (Part C3) — both of which are current, both of which you hold.

### A9 — §22 groundwork that needs no CTLE content

```bash
wp option get wp_page_for_privacy_policy    # should be the draft Privacy Policy page ID
```

- [X] Privacy Policy page is designated in Settings → Privacy (the draft is fine — CTLE writes the content)
- [X] Confirm Tools → **Export Personal Data** and **Erase Personal Data** are present and reachable by an Administrator

### A10 — Certificate renewal reminder

The TLS certificates for `ctle.dom.edu` and `www.ctle.dom.edu` expire **2026-08-31**, inside the launch window. They auto-renew.

- [X] Put a calendar reminder on **2026-08-24** to verify renewal:

```bash
echo | openssl s_client -servername ctle.dom.edu -connect ctle.dom.edu:443 2>/dev/null \
  | openssl x509 -noout -dates
```

### A11 — *(optional)* pre-stage wpForo

Install only — configuration waits on SSO and the CD-8 privacy language.

```bash
wp plugin install wpforo          # do not activate
wp plugin list --fields=name,status,version
```

- [X] wpForo installed, left inactive

Rounds out the §5 plugin roster so nothing is left to discover during the SSO push. Skip if you would rather not carry an unconfigured forum plugin on Live.

---

## Part B — Canvas (DU LT)

New in the repo: [`canvas/ctle-global-nav.js`](../canvas/ctle-global-nav.js) and [`canvas/README.md`](../canvas/README.md). The script faculty-gates the global-nav CTLE button and retargets it, with the SSO URL isolated in a single config constant and an `enabled` master switch defaulting to `false` — so uploading it early is a guaranteed no-op.

### B1 — Test the gating JS in Canvas beta

- [ ] Append the IIFE to the existing DU theme JS bundle (Canvas allows one JS file per account theme — **append, do not replace**)
- [ ] Upload to `beta.instructure.com` with `enabled: true`, `debug: true`
- [ ] Verify with a **teacher** account: button visible, `[ctle-nav]` console output shows `declared_user_type lookup → true`
- [ ] Verify with a **student** account: button absent
- [ ] Verify the failure path: block `/api/v1/users/self/logins` in devtools, reload, confirm the button stays hidden (`failOpen: false`)

Fully testable today — none of this needs SSO. Production upload waits for launch.

### B2 — SIS `declared_user_type`

Full specification in [`canvas/README.md`](../canvas/README.md#sis-specification--declared_user_type).

- [ ] Add the `declared_user_type` column to the nightly SIS `users.csv` export, populated `teacher` for faculty
- [ ] Run a single-file test import first and confirm batch-mode row selection is unaffected
- [ ] Spot-check via `GET /api/v1/users/self/logins` as a faculty user
- [ ] Note the size of the manually-provisioned faculty population that the SIS export misses — if it is material, tell CTLE so the site carries a findable direct link

### B3 — LT-4: retract the break-glass request

- [ ] Determine whether `IT_REQUESTS.md` Request 4 was ever actually sent to DU LT
- [ ] If it was, send a two-line retraction so nobody provisions and vaults a credential that will never be used
- [ ] Close LT-4 in the register either way, with which case applied

### B4 — Hold the production upload

- [ ] Do **not** upload to production Canvas until the SSO URL is real and launch is agreed (CD-6)

The site is `noindex`ed and unannounced. A live button pointing faculty at an unfinished site is the one way this Canvas work can do harm.

---

## Part C — Communications

### C1 — DU IT

- [ ] Send [`docs/outbound/2026-07-29-it.md`](outbound/2026-07-29-it.md)

Chases IT-1 (the estimate above all), IT-2, and IT-4; confirms what has landed; and tells them the LTI withdrawal removed work from their plate.

### C2 — CTLE Director & Developer

- [ ] **First: send [`docs/outbound/2026-07-29-amanda-do-not-push.md`](outbound/2026-07-29-amanda-do-not-push.md)** (CD-N7) — urgent, on its own, not batched into the email below
- [ ] Then send [`docs/outbound/2026-07-29-ctle.md`](outbound/2026-07-29-ctle.md) — **after Part A2 is actually done**, so its "this is complete" statements are true, and **after CD-N7**, since it hands over staging access and describes the Live build as finished

Carries CD-N2, CD-N4, CD-N5, CD-N6 as notices; asks Persis for CD-1/4/6/7/8 and the CD-9–13 status questions; asks Amanda for ME-10, ME-6, and the theme decision.

### C3 — The login URL, separately

- [ ] Send the custom login path (CD-N3) and the staging password (A5) to Persis and Amanda **individually via DU SecureTransfer** — not in the shared email, not in a channel, never in this repo

Nothing from §1 or §3 goes in this transfer — see A8. The `topsecretuser` password is dead with its account, and the MyKinsta owner password is Persis's own and self-recoverable.

---

## Part D — Close-out

Once Parts A–C are done, these register updates apply. Hand the results back and they can be applied in one pass.

- [ ] `STATUS_AND_ACTIONS.md`: close **ME-3**, **ME-8**, **ME-9** (CD-N2/N4/N5/N6 sent; CD-N3 sent individually), **LT-4**; advance **LT-1/LT-3** to "gating JS built and beta-tested, retarget pending IT-1"; close **ME-12** once A3 has deployed (noting delivery stays unverifiable until IT-2)
- [ ] `kinsta_onboarding.md`: check off §4 cleanup, §6 brute-force confirmation and staging password protection, §7 hygiene items, §22 privacy tooling
- [ ] ~~`HANDOFF.md`: rewrite "Immediate next actions" — the self-serve column should be empty, leaving IT-1/IT-2 and the CD decisions~~ — **do not do this.** Superseded 2026-07-29: `HANDOFF.md` has since been corrected to say the opposite, because CD-N7/ME-16/ME-17/ME-18 are ours. Following this line would re-introduce the error. Instead: confirm HANDOFF's "Start here" still leads with CD-N7.
- [ ] `STATUS_AND_ACTIONS.md`: also close **CD-N7** once sent, and record Amanda's answers (theme, page builder, what she has built) against **ME-18** — it cannot be scoped without them
- [ ] **Do not delete this file yet.** Part B is still open, and the close-out above no longer covers everything. Retire it only when Parts B and C are both complete *and* their outcomes are in the register.
- [ ] Commit

---

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

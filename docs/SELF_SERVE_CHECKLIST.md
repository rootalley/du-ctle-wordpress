# CTLE WordPress — Self-Serve Runbook

**Written:** 2026-07-29 · **Owner:** Steven Endres · **Status:** working document

**Purpose.** Close every remaining task that does **not** depend on DU IT (SSO, email) or on the CTLE Director and Developer, then send the two status/chase emails. When this runbook is finished, the project's entire open-item list is external — nothing is waiting on us.

**Disposable.** This is a session runbook, not a reference document. When every box is checked, fold the outcomes into `STATUS_AND_ACTIONS.md` (Part D) and delete this file. Do not let it become a seventh document to keep in sync.

**Scope boundaries — deliberately excluded:**

| Excluded | Why |
|---|---|
| §13 SSO config, §14 admin elevation, §15 mail config | Blocked on IT-1 / IT-2. |
| Retargeting the Canvas button to the real SSO URL | The URL does not exist until IT-1 lands. Everything *else* about the button is in scope (Part B). |
| CD-1 theme, CD-3 builder, CD-4 license, CD-5 CPT, §17–§21 content | Director / Developer decisions. |
| ME-6 (Amanda's SSH key), ME-10 (Amanda's account stamp) | Require Amanda. Both are asks in the CTLE email. |
| ME-11 off-site backup, ME-1c hostname removal, HSTS | Deliberately deferred post-launch. |
| IT-4 / CD-7 `ctle@dom.edu` access list | Decision belongs to IT and the Director. Chased in both emails. |

**Order matters in two places.** Part A2 runs *before* the CTLE email in Part C2, because that email states the cleanup is done. And Part C3 (the login URL) never goes in a shared email.

---

## Part A — Live site

All commands run over SSH on **Live**. Connection details: MyKinsta → Sites → DU-CTLE → Info → SFTP/SSH.

```bash
ssh <user>@<host> -p <port>
cd public
wp --info          # confirm WP-CLI v2 and the expected PHP 8.4 CLI binary
```

### A1 — Take a manual backup first

- [ ] MyKinsta → Sites → DU-CTLE → **Backups → Manual** → create one, label it `pre-cleanup-2026-07-29`

Everything in A2 is destructive and irreversible from the WP UI. Kinsta keeps 14 days of dailies plus point-in-time, so this is belt-and-suspenders — but a labelled restore point costs 30 seconds and makes the rest of this section safe to run without hesitation.

### A2 — ME-3: finish §4 cleanup

**Read the listings before deleting anything.** Two things must survive: the **draft Privacy Policy page** (§22 needs it) and **all themes** (CD-1 has not been decided).

```bash
# 1. Inspect. Confirm hello and akismet are the only inactive plugins.
wp plugin list --fields=name,status,version

# 2. Delete the two default plugins.
wp plugin delete hello akismet

# 3. Inspect posts and pages. Note the IDs — and note that the Privacy Policy
#    page will appear here as a draft. Do NOT delete it.
wp post list --post_type=post --post_status=any --fields=ID,post_title,post_status
wp post list --post_type=page --post_status=any --fields=ID,post_title,post_status

# 4. Delete the two sample items by ID (--force skips the trash).
wp post delete <hello-world-id> --force
wp post delete <sample-page-id> --force

# 5. The default "A WordPress Commenter" comment.
wp comment list --fields=comment_ID,comment_author,comment_post_ID,comment_approved
wp comment delete <comment-id> --force

# 6. Confirm the end state.
wp plugin list --fields=name,status
wp post list --post_type=any --post_status=any --fields=ID,post_type,post_title,post_status
```

Verify externally — remember Kinsta serves stale HTML, so cache-bust:

```bash
curl -sS -o /dev/null -w 'hello-world: %{http_code}\n' "https://ctle.dom.edu/hello-world/?v=$RANDOM"
curl -sS -o /dev/null -w 'sample-page: %{http_code}\n' "https://ctle.dom.edu/sample-page/?v=$RANDOM"
# both → 404
```

- [ ] Hello Dolly and Akismet deleted
- [ ] Hello World post, Sample Page, and the default comment deleted
- [ ] Privacy Policy draft still present; all themes untouched
- [ ] Both URLs return 404 from outside

> Closes **ME-3**. Note the ordering choice: CD-N5 is being sent as an after-the-fact notice in the CTLE email (Part C2) rather than in advance. That is a deliberate departure from the notify-before-acting rule in `STATUS_AND_ACTIONS.md`, justified by the content being WordPress's own sample data, the change being restorable from the A1 backup, and neither the Director nor the Developer having begun site work. Record it that way in the register — do not quietly re-label it as notified.

### A3 — Widen the admin-alert recipients

The repo copy of `mu-plugins/ctle-admin-alerts.php` now lists `sendres@dom.edu` and `pdriver@dom.edu`, and drops any entry that is not a valid address so a half-edited placeholder can never become a live recipient. **It deliberately does not include `ctle@dom.edu`:** that mailbox holds the MyKinsta 2FA codes gating Administrator access, so routing Administrator-login alerts to it would put the alert and the second factor in the same inbox. Revisit once IT-4 closes.

- [ ] Deploy and confirm it loaded:

```bash
scp -P <port> mu-plugins/ctle-admin-alerts.php <user>@<host>:~/public/wp-content/mu-plugins/
ssh <user>@<host> -p <port> 'cd public && wp plugin list --status=must-use'
```

- [ ] `ctle-admin-alerts.php` shows in the must-use list

Delivery stays unverifiable until IT-2 lands — expected, and already tracked in §7.

### A4 — §6 residual: brute-force protection on the custom login URL

MyKinsta → live chat. Ask verbatim:

> Our site uses WPS Hide Login, so `wp-login.php` returns 404 and the login form lives at a custom path. Does Kinsta's automatic brute-force protection (IP ban after 6 failed logins per minute) follow the custom login URL, or does it only watch `wp-login.php`?

- [ ] Answer received and pasted into `kinsta_onboarding.md` §6 with the date

Expected to be yes — Kinsta auto-detects a customized login URL. This is about getting it on record, not about doubting it.

### A5 — CD-N4: password-protect staging

- [ ] MyKinsta → Sites → DU-CTLE → **Staging** → Tools → **Password Protection** → enable
- [ ] Record the credentials in the CTLE vault (not this repo)
- [ ] Confirm staging prompts for credentials from a logged-out browser

Staging carries the same code as production and gets none of the attention. The credentials go to the Director and Developer via the same channel as the login URL (Part C3), not in the shared email.

### A6 — §7 MyKinsta hygiene

- [ ] Review the MyKinsta company user list; remove anyone no longer on the project (each MyKinsta user is a path into WP Admin)
- [ ] Confirm each active person uses a per-person **Company Developer** account for routine work, so their 2FA codes go to individual DU addresses rather than the shared mailbox
- [ ] Confirm the §1 billing contact is a monitored DU address and the annual renewal cannot lapse silently — a suspended Kinsta account takes the dashboard *and* SSH, the one scenario neither recovery path covers

### A7 — Update what is safe to update

Themes are updated but **not deleted** — deletion waits on CD-1.

```bash
wp core check-update
wp plugin update --all --dry-run
wp theme update --all --dry-run
# then re-run without --dry-run if the listings look as expected
wp theme list --fields=name,status,version,update
```

- [ ] Core, plugins, and themes reported and updated as appropriate; no theme deleted

### A8 — ME-8: confirm the vault entries

- [ ] Confirm the credentials redacted from `kinsta_onboarding.md` §1 and §3 are actually recorded in the CTLE vault, reachable by someone other than Steven

The redaction is done; what is unverified is whether the values survived it. This is a five-minute check that prevents a bad day later.

### A9 — §22 groundwork that needs no CTLE content

```bash
wp option get wp_page_for_privacy_policy    # should be the draft Privacy Policy page ID
```

- [ ] Privacy Policy page is designated in Settings → Privacy (the draft is fine — CTLE writes the content)
- [ ] Confirm Tools → **Export Personal Data** and **Erase Personal Data** are present and reachable by an Administrator

### A10 — Certificate renewal reminder

The TLS certificates for `ctle.dom.edu` and `www.ctle.dom.edu` expire **2026-08-31**, inside the launch window. They auto-renew.

- [ ] Put a calendar reminder on **2026-08-24** to verify renewal:

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

- [ ] wpForo installed, left inactive

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

- [ ] Send [`docs/outbound/2026-07-29-ctle.md`](outbound/2026-07-29-ctle.md) — **after Part A2 is actually done**, so its "this is complete" statements are true

Carries CD-N2, CD-N4, CD-N5, CD-N6 as notices; asks Persis for CD-1/4/6/7/8 and the CD-9–13 status questions; asks Amanda for ME-10, ME-6, and the theme decision.

### C3 — The login URL, separately

- [ ] Send the custom login path (CD-N3) and the staging password (A5) to Persis and Amanda **individually** — not in the shared email, not in a channel, never in this repo

---

## Part D — Close-out

Once Parts A–C are done, these register updates apply. Hand the results back and they can be applied in one pass.

- [ ] `STATUS_AND_ACTIONS.md`: close **ME-3**, **ME-8**, **ME-9** (CD-N2/N4/N5/N6 sent; CD-N3 sent individually), **LT-4**; advance **LT-1/LT-3** to "gating JS built and beta-tested, retarget pending IT-1"; close **ME-12** once A3 has deployed (noting delivery stays unverifiable until IT-2)
- [ ] `kinsta_onboarding.md`: check off §4 cleanup, §6 brute-force confirmation and staging password protection, §7 hygiene items, §22 privacy tooling
- [ ] `HANDOFF.md`: rewrite "Immediate next actions" — the self-serve column should be empty, leaving IT-1/IT-2 and the CD decisions
- [ ] Delete this file
- [ ] Commit

---

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

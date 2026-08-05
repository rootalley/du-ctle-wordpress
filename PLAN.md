# PLAN — current state to production

**This file is the only to-do list.** If it disagrees with any other document, fix this one first.

For what the site *currently is*, see `docs/AS_BUILT.md`. Don't read it to find out what to do.

---

# → RIGHT NOW: send Aidan the mail-and-SSO email before you travel

`docs/outbound/2026-08-05_aidan-mail-resolved-sso-next.md`. It closes the mail ticket and hands him the three SSO questions — group, what he already built, and what `employeeId` contains. Sending it today means he can work while you're away.

Also tell Persis the Administrator-login alert she received was a test.

⏱ **5 minutes.** Job 3 is done. Everything else waits on Amanda, Persis or Aidan.

> **Travelling Thu 2026-08-06 → Mon 08-10, back Tue 08-11**, limited availability. Flagged to Aidan with an explicit "don't hold work for me."

---

## The jobs

| # | Job | Needs | Time | State |
|---|---|---|---|---|
| 1 | Communications | — | — | ✅ Done 2026-08-04 |
| 2 | Merge Staging into Live | Amanda + a window | 2–3 hrs | ⏸ Waiting on them |
| 3 | Mail | — | — | ✅ Done 2026-08-05 |
| 4 | SSO | Aidan, then Pete | 3 hrs | ⏸ Handed to Aidan 08-05 |
| 5 | Content and features | Amanda + Persis | weeks | Not started |
| 6 | Pre-launch | everyone | 1 day | Not started |

---

## Deliberately deferred — do not re-raise

Decided 2026-08-04. Both are real and neither is forgotten; the call is to **deliver the platform first**, then volunteer help.

- **Theme accessibility (WCAG 2.1 AA) and DU brand review.** Nobody has assessed `educational-university` 0.3.5. Genuine schedule risk, and it belongs to content rather than platform.
- **Launch date.** Not restated since IT delivered.

Raise both once Jobs 2, 3 and 4 are closed. Not before.

---

## Job 1 — Communications ✅

**Done 2026-08-04.** CTLE email sent to Persis and Amanda (`docs/outbound/2026-08-04_ctle.md`): the push warning, the merge proposal with its sequence, and the SSO access-model question. DU IT is now handled through **two tickets** — one for the mailbox, one for SSO — not email. Older outbound drafts deleted; they described a communication model no longer in use.

---

## Job 2 — Merge Staging into Live

**Waiting on:** Amanda creating her Live profile, and a maintenance window from CTLE. Both were asked for on 2026-08-04.

The sequence below is what was **committed to in writing**. Don't reorder it.

### 2a. Amanda creates her Live profile — her action

MyKinsta → Live → *Create admin and log in*. Then find and stamp her:

```bash
cd public
wp user list --fields=ID,user_login,user_email --format=csv
wp user meta update <her-ID> sis_user_id <her-employeeId>
```

**Write her Live user ID down. Step 2e needs it.** While you have her, get her SSH public key — two-person recovery still isn't satisfied.

### 2b. Lock down Live — 20 min

MyKinsta → Live → Tools → Password Protection → Enable. Record in the CTLE vault, **not this repo**. Confirm it challenges from a logged-out browser, then SecureTransfer the credentials to both of them alongside the custom login path.

> Basic Auth sits in front of WordPress, so it also sits in front of the Entra redirect. Browser sign-in carries cached credentials and works; back-channel checks get a 401. Job 4 tests this.

### 2c. Back up both — 5 min

MyKinsta → Backups → Manual, **both environments**, labelled `pre-merge`. Staging has never had one.

### 2d. Pull from Staging — 25 min

```bash
KEY=~/.ssh/id_ed25519_ctle_sendres_kinsta
IP=163.192.209.112

ssh -i $KEY -p 50378 ductle@$IP 'cd public && wp db export /tmp/ctle-content.sql \
  --tables=wp_posts,wp_postmeta,wp_terms,wp_termmeta,wp_term_taxonomy,wp_term_relationships'
scp -i $KEY -P 50378 ductle@$IP:/tmp/ctle-content.sql /tmp/

ssh -i $KEY -p 50378 ductle@$IP \
  'cd public && wp option get theme_mods_educational-university --format=json' > /tmp/theme-mods.json

rsync -avz -e "ssh -i $KEY -p 50378" ductle@$IP:~/public/wp-content/uploads/ /tmp/ctle-uploads/
rsync -avz -e "ssh -i $KEY -p 50378" \
  ductle@$IP:~/public/wp-content/themes/educational-university/ /tmp/ctle-theme/
```

### 2e. Push to Live and remap — 30 min

> **The import replaces `wp_posts` wholesale, so Live's draft Privacy Policy page does not survive it.** Staging has no equivalent. Recreate it after the merge — Job 6 assumes one exists to publish. Live's other page is `Sample Page`, which is expendable.

```bash
rsync -avz -e "ssh -i $KEY -p 26769" /tmp/ctle-uploads/ ductle@$IP:~/public/wp-content/uploads/
rsync -avz -e "ssh -i $KEY -p 26769" \
  /tmp/ctle-theme/ ductle@$IP:~/public/wp-content/themes/educational-university/
scp -i $KEY -P 26769 /tmp/ctle-content.sql /tmp/theme-mods.json ductle@$IP:/tmp/

ssh -i $KEY -p 26769 ductle@$IP
cd public
wp db export /tmp/live-pre-merge-$(date +%F).sql     # local undo, on top of Kinsta's
wp db import /tmp/ctle-content.sql
```

Staging and Live user IDs differ. Offset first so no mapping collides mid-flight.

**Verified against both environments 2026-08-04.** Staging: `1` topsecretuser, `2` Amanda, `3` Persis, `5` Steven. Live: `2` Persis, `3` Steven. The three mappings below are correct as written.

```bash
wp db query "UPDATE wp_posts SET post_author = post_author + 1000;"
wp db query "UPDATE wp_posts SET post_author = 2 WHERE post_author = 1003;"   # pdriver
wp db query "UPDATE wp_posts SET post_author = 3 WHERE post_author = 1005;"   # sendres
wp db query "UPDATE wp_posts SET post_author = <AMANDA_LIVE_ID> WHERE post_author = 1002;"
wp db query "UPDATE wp_posts SET post_author = 3 WHERE post_author >= 1000;"  # topsecretuser and the orphan
wp db query "SELECT post_author, COUNT(*) FROM wp_posts GROUP BY post_author;"
```

> **The catch-all is `>= 1000`, not `> 1000`.** One `wp_navigation` row on Staging has `post_author = 0`, which the offset turns into exactly `1000`. A `>` comparison leaves it stranded there and the merge finishes with an author ID pointing at no user.

Last query must show only IDs 2, 3 and Amanda's. Anything ≥1000 means a step was skipped.

### 2f. Fix URLs and settings — 15 min

```bash
wp search-replace 'https://stg-ductle-staging.kinsta.cloud' 'https://ctle.dom.edu' \
  --all-tables-with-prefix --precise --skip-columns=guid --report-changed-only

wp theme activate educational-university
wp option update show_on_front page
wp option update page_on_front 18
wp option update page_for_posts 26
wp option update theme_mods_educational-university "$(cat /tmp/theme-mods.json)" --format=json
```

### 2g. Verify — 15 min

```bash
wp post list --post_type=page --fields=ID,post_title --format=csv   # expect 11
wp plugin list --status=must-use --format=csv                      # both ours present
wp user list --fields=ID,user_login --format=csv                   # no topsecretuser
wp option get blog_public                                          # 0
wp eval 'var_dump(has_filter("authenticate","wp_authenticate_username_password"));'  # false
```

Then in a browser, past Basic Auth: front page renders, menu works, images load, MyKinsta auto-login still works.

**Done when:** all five checks pass and Live looks like Staging did.

**If it goes wrong:** `wp db import /tmp/live-pre-merge-<date>.sql`, or restore the Kinsta manual backup.

### 2h. Hand Live to Amanda — 5 min

Confirm Live is now the build environment and Staging is frozen.

---

## Job 3 — Mail ✅

**Done 2026-08-05.** `wp_mail()` reaches `ctle-noreply@dom.edu` through Microsoft Graph, scoped by an Exchange access policy to that mailbox alone. Remaining: confirm the test messages actually landed in your inbox, and refresh `AS_BUILT.md`.

**IT ticket covers:** separate app registration; Graph `Mail.Send` **application** permission; admin consent; scoped to `ctle-noreply@dom.edu` via application access policy.

1. ✅ **`mu-plugins/ctle-mail.php` written** 2026-08-04. Client-credentials token cached in a transient, refreshed once on a 401; `pre_wp_mail` posting to `/v1.0/users/ctle-noreply@dom.edu/sendMail`. Unconfigured, it fails fast and logs rather than falling back to PHP `mail()`.
2. ✅ **`mu-plugins/README.md` updated** — third file, the `wp-config.php` constants, and the test command.
3. ✅ **Deployed to Live** 2026-08-04. `wp plugin list --status=must-use` shows three CTLE files; `pre_wp_mail` confirmed hooked.
4. ✅ **WP Mail SMTP deleted** 2026-08-04. Its only settings were `mailer: mail` and a from-address — nothing worth keeping. Its two tables (`wp_wpmailsmtp_debug_events`, `wp_wpmailsmtp_tasks_meta`) survive the uninstall; leave them for the pre-launch cleanup rather than dropping tables now.
5. ✅ **Constants in `wp-config.php`** 2026-08-04. Token issues cleanly — decoded claims show `roles: Mail.Send`, `idtyp: app`. **The app registration and consent are correct; nothing more is needed there.**
6. ✅ **Mail delivers. 2026-08-05.** `wp_mail()` returns `true`; Graph accepts with HTTP 202. Plain text and HTML-with-headers both verified.

   **It was propagation the whole time.** IT's `RestrictAccess` policy was correct when they built it on 2026-08-04; it simply had not taken effect. It began working somewhere between 18:32 UTC on 08-04 and 19:00 UTC on 08-05 — **over 24 hours**, far beyond the "up to 30 minutes" in Microsoft's documentation and beyond the "several hours" the second ticket quoted.

   **The discriminator test misled us.** Yesterday, sending as a granted mailbox and a denied one failed identically, which was read as the policy not being reached. The real cause was that nothing had propagated, so everything was denied. Today the same test separates cleanly — see step 7. When a policy is inert, "granted" and "denied" are indistinguishable; that test only carries meaning once propagation is known to be complete.

7. ✅ **Scoping verified as a live security control.** Sending as `ctle-noreply@dom.edu` succeeds; sending as `sendres@dom.edu` is refused with `403 [RAOP]`. The app can send as that one mailbox and nothing else, which is the control we asked for. **Re-run this check if the app registration is ever modified.**
8. ✅ **`ctle-admin-alerts.php` fires cleanly** — the `wp_login` hook was triggered for real against the live recipient list (`sendres@dom.edu`, `pdriver@dom.edu`). **Persis received a test alert as a side effect; tell her it was a test.**
9. ✅ **Client secret expiry: 2028-08-02.** Recorded under *Not yours* below with a renewal reminder a month ahead. When it lapses, every send fails at the token step and the only signal is the error log — nothing emails you, because the thing that would is this plugin.
10. **Re-run `scripts/audit-env.sh` and update `AS_BUILT.md`** — the plugin list and the mail row are both stale.

> **Don't let IT "fix" this by widening the app's access.** A `RestrictAccess` policy scoped to the one mailbox is the control we asked for. Unrestricted app-only mailbox access would let a leaked secret send as anyone at DU.

**Done when:** an Administrator login produces an email.

---

## Job 4 — SSO

**Waiting on:** Aidan. Email sent 2026-08-05 (`docs/outbound/2026-08-05_aidan-mail-resolved-sso-next.md`).

### Who does what in IT

| Person | Owns |
|---|---|
| **Aidan** | Entra admin. Did the mail app registration; **owns the SSO registration, the group and the claims**. Primary contact for this job. |
| **Pete** | Runs the SIS (Jenzabar) and builds the Canvas SIS import files. Some SSO involvement, less than Aidan. **His piece — `declared_user_type` in the nightly import — comes after SSO works.** |
| **Ellen** | IT project-manager role, and adjunct faculty. Not currently in the loop on either piece. |

### Settled with Pete, 2026-08-05

All three asks confirmed doable: a CTLE allowlist security group populated from `DOMFaculty`; OIDC sign-in to WordPress; `DOMFaculty` also driving `declared_user_type` for Canvas. **Pete leaned SAML from familiarity but agreed OIDC works — decision stands at OIDC**, since the installed plugin is OIDC-only and SAML would mean a different plugin and an unbudgeted licence. Pete found earlier SSO work already started by Aidan, so he handed the SSO piece to Aidan and asked to be picked up again for the Canvas file afterwards.

### Three answers needed from Aidan before this can move

1. **Does the allowlist security group exist?** He checks, and creates it if not. Must be a security group — `DOMFaculty` is a mailing list and can't gate app assignment. Flat membership; nested groups unsupported.
2. **What did he already build?** Unknown. Must be a **separate registration from the mail one**.
3. **What does Entra's `employeeId` actually contain?** Believed to be the Jenzabar ID. Unconfirmed.

**IT's side, in Entra:**

1. App registration, single tenant, platform **Web** (confidential client, client secret)
2. Redirect URI: `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize`
3. Gate on the allowlist group — *Assignment required* = Yes
4. Claims: `email`, `given_name`, `family_name`, `employeeId` in the ID token
5. Hand back tenant ID, client ID, client secret via SecureTransfer, plus the secret's expiry

> **`employeeId` is where this will stall.** It is *not* in the portal's standard optional-claims list — emitting it needs a **custom claims policy** on the app's service principal. Microsoft's own docs use `employeeId` as the worked example, so it's a known path, not an exotic one.
>
> If Aidan can't emit it, the fallback is matching on `upn` or `oid` — but that means re-stamping the `sis_user_id` meta on Steven's and Persis's accounts. **Decide before credentials are issued, not after.**

**Our side, in WordPress:**

1. ✅ **OpenID Connect Generic 3.11.3 installed on Live 2026-08-05 — deliberately left inactive.** Actively maintained (last release 2026-02), PHP 7.4+, 200k+ installs.
2. **Activate only when you can immediately test MyKinsta auto-login.** With password authentication removed, auto-login is the *only* interactive way into wp-admin. An unconfigured auth plugin has no upside until credentials exist, and a locked admin has a real downside. One command, then confirm you can still get in.
3. Endpoints from the tenant's OIDC discovery document
4. Client ID and secret in `wp-config.php` constants
5. **Confirm JWT signature verification against the tenant JWKS is enabled.** 3.11.0 was a security release adding it, to stop token forgery. Don't leave it off.
6. Identity key: `employeeId` → the `sis_user_id` meta already stamped on your and Persis's accounts
7. *Create user if none exists*, role Subscriber
8. Profile sync on every login (display name, email)

> **Version gap to watch:** Live runs WordPress 7.0.2; the plugin declares "tested up to 6.9.0". Not a known break, but if activation misbehaves, that gap is the first thing to suspect.
>
> **Also check on activation:** WPS Hide Login is active. The redirect URI targets `admin-ajax.php`, which it does not intercept, so the sign-in flow should be unaffected — but any plugin redirect that lands on `wp-login.php` will 404. Verify the full round trip, not just the callback.

### Group membership: Ellen first, `DOMFaculty` at launch

Agreed with Pete 2026-08-05. The group starts with **Ellen** as its only member — she is both IT and adjunct faculty, so she can verify against a real faculty identity — and switches to `DOMFaculty`-populated membership at launch.

**Steven, Persis and Amanda must be added during the build phase too.** Ellen alone cannot test what matters. She has no existing WordPress account, so her sign-in is a JIT provision — that exercises *provisioning*, not *matching*. The duplicate-account failure can only surface for Steven and Persis, the two with accounts already stamped with `sis_user_id`. Testing with Ellen alone defers that risk to launch day.

**Test in this order:** Ellen → you → Persis → Amanda. Each of the last three must land on their **existing** account. A duplicate means `employeeId` didn't match — stop before anyone else signs in.

Two questions raised with Aidan:

- **Is Ellen's `employeeId` populated?** Adjunct records can differ from full-time in the SIS. If hers is empty, she is the one person whose passing test would mislead us.
- **Is Pete's `DOMFaculty` population one-time or an ongoing sync?** One-time means faculty hired later silently cannot sign in.

> **The launch-day membership swap is a cutover, not a checkbox.** Make the change a couple of days before launch and confirm a real `DOMFaculty` member can sign in. Exchange policy propagation on Job 3 took over 24 hours; assume nothing about how fast group and assignment changes take effect.

Then LT: real SSO URL into `canvas/ctle-global-nav.js`, `enabled: true`, beta-test against a teacher and a student, add `declared_user_type` to the nightly SIS `users.csv`.

**Done when:** Amanda signs in from Canvas and lands logged into Live on her existing account.

---

## Job 5 — Content and features

Amanda and Persis lead. Yours is the plumbing.

1. **Course catalog** — custom post type recommended over static pages
2. **Events calendar** — needs the Events Calendar Pro licence decision and a budget line
3. **Search** — activate Relevanssi, index, tune
4. **Forums** — activate wpForo; anonymous visitors must see no forum content. Needs the confidentiality language proposed in the 08-04 email, which Persis owns
5. **Real home page** — replacing the imported demo front page

---

## Job 6 — Pre-launch

1. **WCAG 2.1 AA audit** and **DU brand review** — deferred above; raise once Jobs 2–4 close
2. **Delete the sample content** — `Hello world!`, `Sample Page`, the default comment
3. **Update the theme** — 0.3.5 has an update pending
4. **Recreate the privacy policy page** — the merge replaces `wp_posts`, so Live's draft does not survive it
5. **Flip the switches:** `blog_public=1`, remove Live password protection, publish the privacy policy, upload the Canvas button to production
6. **Drop the two orphan WP Mail SMTP tables** — `wp_wpmailsmtp_debug_events`, `wp_wpmailsmtp_tasks_meta`, left behind by the uninstall

**Done when:** a logged-out stranger reaches `https://ctle.dom.edu` and sees a finished site.

---

## Not yours

Nothing here needs you this week.

- **Amanda** — Live profile, merge window, SSH key, then content build on Live
- **Persis** — who may sign in (DOMFaculty proposed), Events Calendar licence, catalog structure, confidentiality language, forum categories, admin training
- **DU IT** — the two tickets
- **Post-launch** — off-site 30-day backup, HSTS, disabling `ductle.kinsta.cloud`
- **2026-08-24** — verify TLS auto-renewed; certificates expire 08-31
- **2028-07-02** — ask IT to reissue the Graph mail client secret. **It expires 2028-08-02** and its lapse is silent: mail simply stops, and the only trace is `ctle-mail:` in the PHP error log. Put this in a calendar, not only here

---

## Changelog

| Version | Date | Notes |
|---|---|---|
| 1.5.1 | 2026-08-05 | Group membership plan recorded: Ellen alone during build, `DOMFaculty` at launch. Added the requirement that Steven, Persis and Amanda join during build too — Ellen has no existing WordPress account, so her sign-in tests provisioning rather than matching, and the duplicate-account risk would otherwise go untested until launch day. Flagged Ellen's adjunct record as a possible `employeeId` gap, the one-time-vs-ongoing question on Pete's population, and the launch swap as a cutover to rehearse early. |
| 1.5.0 | 2026-08-05 | Job 4 handed to **Aidan** after the session with Pete. Recorded who owns what in IT — Aidan for Entra, Pete for the SIS and Canvas import, Ellen as project manager. OIDC confirmed over SAML. Three unknowns block progress: whether the allowlist group exists, what Aidan already built, and what Entra's `employeeId` contains. Flagged that `employeeId` needs a custom claims policy rather than a portal checkbox, and that the fallback costs a re-stamp of existing accounts. |
| 1.4.0 | 2026-08-05 | **Job 3 closed — mail delivers.** IT's access policy was correct when built on 08-04 but took over 24 hours to propagate, well beyond any documented window. Recorded that the discriminator test which suggested a second cause is meaningless while a policy is still inert. Scoping verified live: the app sends as `ctle-noreply@dom.edu` and is refused for any other mailbox. OpenID Connect Generic 3.11.3 installed for Job 4, left inactive. |
| 1.3.0 | 2026-08-04 | Job 2's merge sequence verified read-only against both environments. The user-ID mappings, the page IDs (18, 26), the search-replace hostname and the expected page count of 11 are all correct as written. Two defects fixed: the author catch-all was `> 1000`, which stranded a `wp_navigation` row whose `post_author` is 0, and nothing recorded that the import discards Live's draft privacy policy. Client secret expiry recorded (2028-08-02). |
| 1.2.2 | 2026-08-04 | Mail constants added; token verified to carry `roles: Mail.Send` as an app identity, so the Entra side is confirmed correct. `sendMail` blocked by Exchange's app-only access policy (`403 [RAOP]`) — drafted the ticket update and made explicit that the fix is to scope the `RestrictAccess` policy, not to widen the app's reach. |
| 1.2.1 | 2026-08-04 | `ctle-mail.php` deployed to Live and WP Mail SMTP deleted. Job 3 is down to the `wp-config.php` constants and the delivery test. Added a step to refresh `AS_BUILT.md` from the audit script once mail is proven — the plugin list and the mail row are both stale. |
| 1.2.0 | 2026-08-04 | Job 3 code written — `mu-plugins/ctle-mail.php` takes over `wp_mail()` through `pre_wp_mail` and posts to Graph `sendMail`. README documents the constants and the test. Stale WP Mail SMTP references removed from the other two mu-plugins. Job 3 resequenced: deploy and remove WP Mail SMTP now, constants and testing when IT delivers. |
| 1.1.0 | 2026-08-04 | Job 1 closed — CTLE email sent; DU IT moved to two tickets, so the outbound email drafts were deleted. Jobs renumbered. Merge sequence reordered to match what was committed to in writing: Amanda's profile first, then the window, then lockdown, then backups. Mail promoted to the active job since it is the only substantial unblocked work. Recorded the deliberate deferral of theme accessibility and the launch date so neither gets re-raised before the platform is delivered. |
| 1.0.0 | 2026-08-03 | Written from the audit of both environments. Replaced `NOW.md`, the `STATUS_AND_ACTIONS.md` register and `SELF_SERVE_CHECKLIST.md`. Merge method changed from Kinsta push to WP-CLI table export/import — no custom tables on Staging, so a content-level transfer is sufficient and far safer. Mail settled as a custom Graph mu-plugin. |

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

# PLAN — current state to production

**This file is the only to-do list.** If it disagrees with any other document, fix this one first.

For what the site *currently is*, see `docs/AS_BUILT.md`. Don't read it to find out what to do.

---

# → RIGHT NOW: send the IT ticket update

**Job 3 is built, deployed and configured.** Graph is refusing the send at Exchange's app-only access policy — an IT-side setting, not ours. The draft is at `docs/outbound/2026-08-04_it-ticket-mail-accesspolicy.md`; append it to the existing mailbox ticket.

⏱ **5 minutes.** Then Job 3 waits on IT, and nothing else on your list is unblocked this week.

---

## The jobs

| # | Job | Needs | Time | State |
|---|---|---|---|---|
| 1 | Communications | — | — | ✅ Done 2026-08-04 |
| 2 | Merge Staging into Live | Amanda + a window | 2–3 hrs | ⏸ Waiting on them |
| **3** | **Mail** | **IT — access policy** | **5 min + wait** | **⏸ built and deployed; send the ticket** |
| 4 | SSO | IT + LT session | 3 hrs | ⏸ Ticket filed |
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

Staging and Live user IDs differ. Offset first so no mapping collides mid-flight:

```bash
wp db query "UPDATE wp_posts SET post_author = post_author + 1000;"
wp db query "UPDATE wp_posts SET post_author = 2 WHERE post_author = 1003;"   # pdriver
wp db query "UPDATE wp_posts SET post_author = 3 WHERE post_author = 1005;"   # sendres
wp db query "UPDATE wp_posts SET post_author = <AMANDA_LIVE_ID> WHERE post_author = 1002;"
wp db query "UPDATE wp_posts SET post_author = 3 WHERE post_author > 1000;"   # topsecretuser → you
wp db query "SELECT post_author, COUNT(*) FROM wp_posts GROUP BY post_author;"
```

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

## Job 3 — Mail ← start here

**Code is written.** Everything remaining is a deploy step or waits on IT's credentials.

**IT ticket covers:** separate app registration; Graph `Mail.Send` **application** permission; admin consent; scoped to `ctle-noreply@dom.edu` via application access policy.

1. ✅ **`mu-plugins/ctle-mail.php` written** 2026-08-04. Client-credentials token cached in a transient, refreshed once on a 401; `pre_wp_mail` posting to `/v1.0/users/ctle-noreply@dom.edu/sendMail`. Unconfigured, it fails fast and logs rather than falling back to PHP `mail()`.
2. ✅ **`mu-plugins/README.md` updated** — third file, the `wp-config.php` constants, and the test command.
3. ✅ **Deployed to Live** 2026-08-04. `wp plugin list --status=must-use` shows three CTLE files; `pre_wp_mail` confirmed hooked.
4. ✅ **WP Mail SMTP deleted** 2026-08-04. Its only settings were `mailer: mail` and a from-address — nothing worth keeping. Its two tables (`wp_wpmailsmtp_debug_events`, `wp_wpmailsmtp_tasks_meta`) survive the uninstall; leave them for the pre-launch cleanup rather than dropping tables now.
5. ✅ **Constants in `wp-config.php`** 2026-08-04. Token issues cleanly — decoded claims show `roles: Mail.Send`, `idtyp: app`. **The app registration and consent are correct; nothing more is needed there.**
6. ⏸ **Blocked on IT — Exchange app-only access policy.** `sendMail` returns `403 [RAOP] Blocked by tenant configured AppOnly AccessPolicy settings`. That is Exchange's application access policy layer, not Entra: either no `RestrictAccess` policy exists for the app, it is scoped to a group not containing `ctle-noreply@dom.edu`, or a `DenyAccess` policy overrides it. Ticket update drafted at `docs/outbound/2026-08-04_it-ticket-mail-accesspolicy.md` — **send it.** Policy changes take up to 30 min to propagate, so re-test after a wait, not immediately.
7. **Re-test** once IT confirms: `wp eval 'var_dump( wp_mail( "sendres@dom.edu", "test", "body" ) );'` — `true` is HTTP 202 accepted; `false` puts the reason in the PHP error log prefixed `ctle-mail:`.
8. **Confirm `ctle-admin-alerts.php` delivers** — log in as an admin, check the alert arrives.
9. **Diary the client secret's expiry.** When it lapses, every send fails at the token step and the only signal is the error log.
10. **Re-run `scripts/audit-env.sh` and update `AS_BUILT.md`** — the plugin list and the mail row are both stale.

> **Don't let IT "fix" this by widening the app's access.** A `RestrictAccess` policy scoped to the one mailbox is the control we asked for. Unrestricted app-only mailbox access would let a leaked secret send as anyone at DU.

**Done when:** an Administrator login produces an email.

---

## Job 4 — SSO

**Waiting on:** the IT + LT working session. Ticket filed.

Confirm the plugin before the session — **OpenID Connect Generic** (free) is the assumption below.

**IT's side, in Entra:**

1. App registration, single tenant
2. Redirect URI: `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize`
3. Gate on the faculty group — *Assignment required* = Yes
4. Claims: `email`, `given_name`, `family_name`, `employeeId` in the token
5. Hand back tenant ID, client ID, client secret

> **Snag to raise in the ticket before the session:** a *mailing list* and a *security group* are different objects. App assignment needs a security group, so DOMFaculty can't be used directly — IT will need to create or mirror one.

**Our side, in WordPress:**

1. Install and activate OpenID Connect Generic
2. Endpoints from the tenant's OIDC discovery document
3. Client ID and secret in `wp-config.php` constants
4. Identity key: `employeeId` → the `sis_user_id` meta already stamped on your and Persis's accounts
5. *Create user if none exists*, role Subscriber
6. Profile sync on every login (display name, email)

**Test in this order:** IT's test account → you → Persis → Amanda. Each must land on their **existing** account. A duplicate means `sis_user_id` didn't match — stop before anyone else signs in.

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
4. **Flip the switches:** `blog_public=1`, remove Live password protection, publish the privacy policy, upload the Canvas button to production

**Done when:** a logged-out stranger reaches `https://ctle.dom.edu` and sees a finished site.

---

## Not yours

Nothing here needs you this week.

- **Amanda** — Live profile, merge window, SSH key, then content build on Live
- **Persis** — who may sign in (DOMFaculty proposed), Events Calendar licence, catalog structure, confidentiality language, forum categories, admin training
- **DU IT** — the two tickets
- **Post-launch** — off-site 30-day backup, HSTS, disabling `ductle.kinsta.cloud`
- **2026-08-24** — verify TLS auto-renewed; certificates expire 08-31

---

## Changelog

| Version | Date | Notes |
|---|---|---|
| 1.2.2 | 2026-08-04 | Mail constants added; token verified to carry `roles: Mail.Send` as an app identity, so the Entra side is confirmed correct. `sendMail` blocked by Exchange's app-only access policy (`403 [RAOP]`) — drafted the ticket update and made explicit that the fix is to scope the `RestrictAccess` policy, not to widen the app's reach. |
| 1.2.1 | 2026-08-04 | `ctle-mail.php` deployed to Live and WP Mail SMTP deleted. Job 3 is down to the `wp-config.php` constants and the delivery test. Added a step to refresh `AS_BUILT.md` from the audit script once mail is proven — the plugin list and the mail row are both stale. |
| 1.2.0 | 2026-08-04 | Job 3 code written — `mu-plugins/ctle-mail.php` takes over `wp_mail()` through `pre_wp_mail` and posts to Graph `sendMail`. README documents the constants and the test. Stale WP Mail SMTP references removed from the other two mu-plugins. Job 3 resequenced: deploy and remove WP Mail SMTP now, constants and testing when IT delivers. |
| 1.1.0 | 2026-08-04 | Job 1 closed — CTLE email sent; DU IT moved to two tickets, so the outbound email drafts were deleted. Jobs renumbered. Merge sequence reordered to match what was committed to in writing: Amanda's profile first, then the window, then lockdown, then backups. Mail promoted to the active job since it is the only substantial unblocked work. Recorded the deliberate deferral of theme accessibility and the launch date so neither gets re-raised before the platform is delivered. |
| 1.0.0 | 2026-08-03 | Written from the audit of both environments. Replaced `NOW.md`, the `STATUS_AND_ACTIONS.md` register and `SELF_SERVE_CHECKLIST.md`. Merge method changed from Kinsta push to WP-CLI table export/import — no custom tables on Staging, so a content-level transfer is sufficient and far safer. Mail settled as a custom Graph mu-plugin. |

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

# PLAN — current state to production

**This file is the only to-do list.** If it disagrees with any other document, fix this one first.

For what the site *currently is*, see `docs/AS_BUILT.md`. Don't read it to find out what to do.

---

# → RIGHT NOW: Job 1

**Send three emails. All three are already written.**

⏱ **30 minutes.** When they're sent, stop. Job 2 can be another day.

---

## The jobs

| # | Job | Needs | Time |
|---|---|---|---|
| **1** | **Send three emails** | you | **30 min** |
| 2 | Lock down Live | you | 20 min |
| 3 | Get Amanda onto Live | Amanda | 20 min |
| 4 | Move Amanda's site to Live | you | 90 min |
| 5 | Mail | IT session | 2 hrs |
| 6 | SSO | IT + LT session | 3 hrs |
| 7 | Content and features | Amanda + Persis | weeks |
| 8 | Pre-launch | everyone | 1 day |

Jobs 1–4 are yours and unblocked. Jobs 5 and 6 need scheduling now, because the sessions are the long pole.

---

## Job 1 — Send three emails

**Why:** two are chases that unblock other people; one prevents Amanda destroying the Live build with a button that looks routine.

1. **Amanda — do not push.** `docs/outbound/2026-07-29-amanda-do-not-push.md`. Copy from *"Subject:"*, add her address (`anorris@dom.edu`), send. Still needed even though she's moving to Live in Job 4 — until that's done, a push is still destructive.
2. **DU IT — mail app registration.** `docs/outbound/2026-08-03-it-mail-app.md`. Confirm Ellen is the right contact, send.
3. **CTLE — status.** `docs/outbound/2026-07-29-ctle.md`. Send **after** email 1. Needs Persis's and Amanda's addresses.

In the same pass, ask IT and LT to **book the two working sessions** (Jobs 5 and 6). Scheduling is the long pole, not the work.

**Done when:** three emails sent, two sessions requested.

---

## Job 2 — Lock down Live

**Why:** the site is publicly reachable right now, with "Hello world!" on it, and Job 4 puts real CTLE content there.

1. MyKinsta → Sites → DU-CTLE → **Live** → Tools → **Password Protection** → Enable
2. Set a username and password. Record in the CTLE vault, **not this repo**.
3. Confirm from a logged-out browser that `https://ctle.dom.edu` challenges.
4. Send Persis and Amanda, individually via DU SecureTransfer, **three** things: the Live Basic Auth credentials, the Staging Basic Auth credentials, and the custom login path.

⏱ **20 minutes.**

**Done when:** Live challenges anonymously and both people can still get in.

> Basic Auth sits in front of WordPress, so it also sits in front of the Entra redirect. Browser sign-in carries cached credentials and works; any back-channel check from Entra gets a 401. Job 6 tests this — don't discover it during the session.

---

## Job 3 — Get Amanda onto Live

**Why:** Job 4 remaps page authorship to real Live accounts. Amanda has no Live account, so hers can't be preserved until she makes one. **This must happen before Job 4.**

1. Amanda: MyKinsta → Sites → DU-CTLE → **Live** → *Create admin and log in*. One click, no password.
2. You, over SSH on Live — find her new ID and stamp it:
   ```bash
   cd public
   wp user list --fields=ID,user_login,user_email --format=csv
   wp user meta update <her-ID> sis_user_id <her-employeeId>
   ```
3. Note her Live user ID. **Job 4 needs it.**
4. While you have her: get her SSH public key and add it in MyKinsta → User Settings → SSH Keys. That's the second recovery key, currently missing.

⏱ **20 minutes**, mostly waiting on her.

**Done when:** she appears in `wp user list` on Live with `sis_user_id` set, and you've written her ID down.

---

## Job 4 — Move Amanda's site to Live

**Why:** 9 pages, 27 menu items, 12 media files and a theme currently exist only on Staging.

**Not a Kinsta push.** Kinsta carries environment settings unconditionally, and Staging would drag across `topsecretuser`, wipe Live's plugin state, and revert the hardening. This moves exactly the content tables and files, nothing else.

### 4a. Back up both — 5 min

```bash
# MyKinsta → Backups → Manual, on BOTH environments, label: pre-merge-2026-08-03
```

Staging has never had a manual backup. Take one.

### 4b. Pull from Staging — 10 min

```bash
KEY=~/.ssh/id_ed25519_ctle_sendres_kinsta
STG="-i $KEY -p 50378 ductle@163.192.209.112"
LIVE="-i $KEY -p 26769 ductle@163.192.209.112"

# Content tables only
ssh $STG 'cd public && wp db export /tmp/ctle-content.sql \
  --tables=wp_posts,wp_postmeta,wp_terms,wp_termmeta,wp_term_taxonomy,wp_term_relationships'
scp $STG:/tmp/ctle-content.sql /tmp/

# Theme mods — carries menu locations and customiser settings
ssh $STG 'cd public && wp option get theme_mods_educational-university --format=json' \
  > /tmp/theme-mods.json

# Files
rsync -avz -e "ssh $KEY_OPTS" ...   # see 4c
```

### 4c. Copy files — 15 min

```bash
rsync -avz -e "ssh -i $KEY -p 50378" \
  ductle@163.192.209.112:~/public/wp-content/uploads/ /tmp/ctle-uploads/
rsync -avz -e "ssh -i $KEY -p 26769" \
  /tmp/ctle-uploads/ ductle@163.192.209.112:~/public/wp-content/uploads/

rsync -avz -e "ssh -i $KEY -p 50378" \
  ductle@163.192.209.112:~/public/wp-content/themes/educational-university/ /tmp/ctle-theme/
rsync -avz -e "ssh -i $KEY -p 26769" \
  /tmp/ctle-theme/ ductle@163.192.209.112:~/public/wp-content/themes/educational-university/
```

13 MB, 60 files. Quick.

### 4d. Import on Live — 20 min

```bash
scp -i $KEY -P 26769 /tmp/ctle-content.sql ductle@163.192.209.112:/tmp/
ssh -i $KEY -p 26769 ductle@163.192.209.112
cd public

wp db export /tmp/live-pre-merge-$(date +%F).sql     # local undo, on top of Kinsta's
wp db import /tmp/ctle-content.sql
```

### 4e. Remap authorship — 10 min

Staging and Live user IDs differ. Offset first so no mapping collides mid-flight:

```bash
wp db query "UPDATE wp_posts SET post_author = post_author + 1000;"
wp db query "UPDATE wp_posts SET post_author = 2    WHERE post_author = 1003;"  # pdriver
wp db query "UPDATE wp_posts SET post_author = 3    WHERE post_author = 1005;"  # sendres
wp db query "UPDATE wp_posts SET post_author = <AMANDA_LIVE_ID> WHERE post_author = 1002;"
wp db query "UPDATE wp_posts SET post_author = 3    WHERE post_author > 1000;"  # topsecretuser → you
wp db query "SELECT post_author, COUNT(*) FROM wp_posts GROUP BY post_author;"
```

Last query must show only IDs 2, 3, and Amanda's. Anything ≥1000 means a step was skipped.

### 4f. Fix URLs and settings — 15 min

```bash
wp search-replace 'https://stg-ductle-staging.kinsta.cloud' 'https://ctle.dom.edu' \
  --all-tables-with-prefix --precise --skip-columns=guid --report-changed-only

wp theme activate educational-university
wp option update show_on_front page
wp option update page_on_front 18
wp option update page_for_posts 26
wp option update theme_mods_educational-university "$(cat /tmp/theme-mods.json)" --format=json
```

Copy `/tmp/theme-mods.json` up first.

### 4g. Verify — 15 min

```bash
wp post list --post_type=page --fields=ID,post_title,post_status --format=csv   # expect 11
wp plugin list --status=must-use --format=csv                                   # both ours
wp user list --fields=ID,user_login --format=csv                                # no topsecretuser
wp option get blog_public                                                       # 0
wp eval 'var_dump(has_filter("authenticate","wp_authenticate_username_password"));'  # false
```

Then in a browser, past Basic Auth: front page renders, menu works, images load, MyKinsta auto-login still works.

**Done when:** all five checks pass and the front page looks like Staging.

**If it goes wrong:** `wp db import /tmp/live-pre-merge-<date>.sql`, or restore the Kinsta manual backup. Nothing here is one-way.

### 4h. Hand Live to Amanda — 5 min

Tell her Live is now the build environment and Staging is frozen. Everything from here happens on Live.

---

## Job 5 — Mail

**Blocked on:** the IT working session requested in Job 1.

**IT's side:** separate app registration; Graph `Mail.Send` **application** permission; admin consent; scoped to `ctle-noreply@dom.edu` via application access policy. Full detail and prepared rebuttals in `docs/outbound/2026-08-03-it-mail-app.md`.

**Our side:**

1. Write `mu-plugins/ctle-mail.php` — client-credentials token, cached in a transient; `pre_wp_mail` filter posting to `/v1.0/users/ctle-noreply@dom.edu/sendMail`. ~100 lines. **Can be written before the session.**
2. Put tenant ID, client ID and secret in `wp-config.php` as constants. Never the database, never this repo.
3. Test: `wp eval 'wp_mail("sendres@dom.edu","test","body");'`
4. Confirm `ctle-admin-alerts.php` now delivers — log in as an admin and check the alert arrives.
5. **Delete WP Mail SMTP.** It can't do this job and shouldn't stay installed.
6. Diary the client secret's expiry.

**Done when:** an Administrator login produces an email.

---

## Job 6 — SSO

**Blocked on:** the joint IT + LT session requested in Job 1.

Confirm the plugin choice before the session — **OpenID Connect Generic** (free) is the assumption below.

**IT's side, in Entra:**

1. App registration, single tenant
2. Redirect URI: `https://ctle.dom.edu/wp-admin/admin-ajax.php?action=openid-connect-authorize`
3. Gate on the SIS-fed faculty group — *Assignment required* = Yes
4. Claims: `email`, `given_name`, `family_name`, and `employeeId` mapped into the token
5. Hand back tenant ID, client ID, client secret

**Our side, in WordPress:**

1. Install and activate OpenID Connect Generic
2. Endpoints: authorize, token, and userinfo from the tenant's OIDC discovery document
3. Client ID and secret in `wp-config.php` constants
4. Identity key: `employeeId` → the `sis_user_id` user meta already stamped on your and Persis's accounts
5. Enable *Create user if none exists*, role Subscriber
6. Enable profile sync on every login (display name, email)

**Test, in this order:** IT's test account → you → Persis → Amanda. Each must land on their **existing** account, not a duplicate. If a duplicate appears, `sis_user_id` didn't match — stop and fix the mapping before anyone else signs in.

Then LT: set the real SSO-initiation URL in `canvas/ctle-global-nav.js`, flip `enabled: true`, beta-test against a teacher and a student, add `declared_user_type` to the nightly SIS `users.csv`.

**Done when:** Amanda signs in from Canvas and lands logged into Live on her existing account.

---

## Job 7 — Content and features

Amanda and Persis lead. Yours is the plumbing.

1. **Course catalog** — custom post type recommended over static pages
2. **Events calendar** — needs the Events Calendar Pro licence decision and a budget line
3. **Search** — activate Relevanssi, index, tune
4. **Forums** — activate wpForo; anonymous visitors must see no forum content. Needs Persis's OPC privacy language, which has a long lead time — **start that conversation now**
5. **Real home page** — replacing the imported demo front page

---

## Job 8 — Pre-launch

Nothing here is optional and none of it can be done early.

1. **WCAG 2.1 AA audit** of `educational-university` — it's a third-party theme nobody has assessed, and remediating one late is the classic way a launch slips
2. **DU brand review**
3. **Delete the sample content** — `Hello world!`, `Sample Page`, the default comment
4. **Update the theme** — 0.3.5 has an update pending
5. **Flip the switches:** `blog_public=1`, remove Live password protection, publish the privacy policy, upload the Canvas button to production

**Done when:** a logged-out stranger can reach `https://ctle.dom.edu` and see a finished site.

---

## Not yours

Nothing here needs you this week. It's listed so you can stop holding it.

- **Persis** — Events Calendar licence, catalog structure, forum privacy language, series names, forum categories, admin training
- **Amanda** — content build, theme accessibility remediation
- **DU IT** — the two working sessions, `ctle@dom.edu` access-list decision
- **Post-launch** — off-site 30-day backup, HSTS, disabling `ductle.kinsta.cloud`, forum rollout if it slips
- **2026-08-24** — verify TLS auto-renewed; certificates expire 08-31

---

## Changelog

| Version | Date | Notes |
|---|---|---|
| 1.0.0 | 2026-08-03 | Written from the 2026-08-03 audit of both environments. Replaces `NOW.md`, the `STATUS_AND_ACTIONS.md` register, and `SELF_SERVE_CHECKLIST.md`. Merge method changed from Kinsta selective push to WP-CLI table export/import — the audit found no custom tables on Staging, so a content-level transfer is both sufficient and far safer than a push that would carry environment settings, `topsecretuser`, and Staging's plugin state. Mail transport settled as a custom Graph mu-plugin after confirming WP Mail SMTP's Microsoft 365 mailer is both Pro-only and delegated-only. Job 3 added: Amanda must exist on Live before Job 4 can preserve her authorship. |

*Maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

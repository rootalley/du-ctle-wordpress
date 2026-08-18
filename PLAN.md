# PLAN — current state to production

**This file is the only to-do list.** If it disagrees with any other document, fix this one first.

For what the site *currently is*, see `docs/AS_BUILT.md`. Don't read it to find out what to do.

---

# ⛔ COMMUNICATIONS HOLD — read before writing to anyone

**Do not contact Persis Driver or Amanda Norris.** Not email, not a drafted note, not a message
that "just" asks a quick question. Decided 2026-08-18 for **political reasons, not technical
ones** — Steven is opening this with a face-to-face conversation, and a written note arriving
first would spend that conversation for him.

**This overrides the send condition written into the draft itself.** The CTLE note at
`docs/outbound/2026-08-14_ctle-merge-cancelled.md` says to send once Aidan replies. **That
condition is now met and it still does not go** — it is marked `HELD`. The second held item is
the explanation that the 08-05 Administrator alert was a test, which was never drafted and now
belongs in the conversation.

**Only that one file is held.** `docs/outbound/2026-08-18_aidan-admin-consent.md` went to DU IT and
**Aidan granted consent the same day**, which unblocked Job 4. A second DU IT note is now owed —
`4b`, the missing `employeeId` claim.

**Aidan Acosta and Pete are not covered by this hold.** They are DU IT, reached by ticket, and
the SSO work with Aidan continues normally. Ellen Alamilla is a judgement call — she sits in IT
but is also an adjunct, so treat any approach to her as Steven's to make, not a session's.

**The hold had a machine-side leak, and `2a` closed it on 2026-08-18.** `ctle-admin-alerts.php`
emails **Persis** on every Administrator login and every role change, so the SSO sign-in test and
Amanda's account creation would both have breached the hold without anyone writing a word.
`ctle-alerts-hold.php` is deployed and verified: `ctle_alert_recipients()` returns
`sendres@dom.edu` alone. **Both events have since happened cleanly.** The file is a debt — Job 6
deletes it.

**What the hold costs, and the answer to each:**

| Was blocked on | Now |
|---|---|
| Amanda creating her Live profile | ✅ **Done 2026-08-18** — created for her at ID 4 by `2b`. |
| Amanda's SSH key, for two-person recovery | Accepted risk for the duration — `2f`. Not new; it has been outstanding since July. |
| The theme answer | Genuinely hers. Live stays on `twentytwentyfive` and nothing is lost by waiting — `2e`. |
| Password-protection credentials | Handed over in person at the same conversation — `2d`. Better than SecureTransfer anyway. |
| Persis testing her sign-in | Deferred, and now cheaper — Steven's 08-18 sign-in **proved** the same matching path on the same kind of account. |

**The one thing that must come out of the face-to-face:** *the list of people who go in the
`CTLE WordPress` SSO group*, and **who maintains it after launch**. That is the only CTLE input
standing between a configured platform and handover. Everything else on this plan can be finished
without them.

---

# → RIGHT NOW: SSO is done, bar one claim from Aidan.

**Sign-in works end to end, and the caching defect that broke it is fixed at the root.** 2026-08-18: Aidan granted admin consent, Steven signed in and matched his existing account, and WPS Hide Login was removed in favour of `auto` login mode.

| Check | Result |
|---|---|
| User count after sign-in | **2 at the time** — no duplicate created |
| ID 3 `openid-connect-generic-subject-identity` | `9Gs6NfLULHe_GOLpPUHeeYl2-mB422IB0jnJGrA-0Es` |
| ID 3 `sis_user_id` | `904238` — unchanged |
| `/wp-login.php` | `302` → `login.microsoftonline.com`, **fresh `state` per request**, `x-kinsta-cache: BYPASS` |
| WordPress login form | **Never shown to anyone** |

## What is left

1. **`4b` — ask Aidan why `employeeId` is absent from the ID token.** Drafted at `docs/outbound/2026-08-18_aidan-employeeid-claim.md`. DU IT, not covered by the hold. **The only open item on SSO**, and it blocks Job 5's Canvas linkage rather than authentication. **`4c` waits behind it** so Ellen's sign-in is run once, not twice.
2. **Re-run `scripts/audit-env.sh` and regenerate `docs/AS_BUILT.md`.** Overdue since 08-06 and its precondition — working SSO — is now met. Both environments changed today.
3. **Two small cleanups:** delete the orphaned `whl_page` option, and re-state the DU IT security sign-off request without the obfuscated login URL (`REQUIREMENTS.md` §5 deviation, 2026-08-18).
4. **`4c` — Ellen's test**, the one path still unproven: provisioning an account that does not yet exist. Steven's call.

**Persis's and Amanda's tests stay behind the face-to-face** and now cost nothing to defer — Steven's sign-in proved the identical `email_exists()` path against an identical pre-existing account.

## ✅ B1 — CLOSED 2026-08-14. `ctle.dom.edu` resolves.

```
dig +short ctle.dom.edu        → 162.159.135.42
dig +short www.ctle.dom.edu    → ctle.dom.edu. → 162.159.135.42
curl -I https://ctle.dom.edu/  → HTTP/2 200
TLS: CN=ctle.dom.edu, Google Trust Services WE1, expires 2026-08-31
```

This is the A record and `www` CNAME that ticket 26363781 originally recorded, so DU IT's delivery stands. **No DNS ticket is needed.**

**What actually happened on 08-06 is unresolved, and worth one paragraph because it changes how to test this.** The NXDOMAIN was taken from `ns1.dom.edu` with the authoritative-answer flag. As of today `ns1.dom.edu` does not resolve at all — the `dom.edu` NS set is `dc1`, `dc2`, `dc3`, `az-dc1`, `dc2012r2-2` and `prioryserv`, none of which answer from off-campus. So either the record was absent on 08-06 and has since been restored, or `ns1.dom.edu` was a decommissioned server still answering for a zone it no longer held. **Both are consistent with the evidence and it is not worth chasing.** The lesson to keep: *query the NS set the zone actually publishes, not a nameserver hostname you remember.*

**Consequences of DNS being back:**

- Job 4 can be tested end to end. The redirect URI Aidan registered is reachable.
- Job 6's launch precondition is met.
- **The 2026-08-24 TLS renewal check is live again and is no longer moot.** The certificate expires 08-31. Put it in a calendar.
- `ductle.kinsta.cloud` can go back to being a post-launch cleanup item rather than the production entrance.

## ✅ B2 — CLOSED 2026-08-14 by Amanda. Staging holds nothing worth saving.

**Amanda confirms she only did basic testing on Staging — it was never the site build.** The 11 pages captured there are the `educational-university` theme's demo content, imported by `ansar-import`; the 13 MB of uploads are that demo's images.

So the database instability threatened nothing. **No Kinsta manual backup of Staging is needed, no Kinsta support ticket, and the defensive dump on the container is now just a curiosity.** If Staging's MariaDB keeps flapping after it is reset from Live, report it then — a fault on an environment with nothing in it is not worth anyone's time this month.

**The much larger consequence: Job 2 is no longer a merge.** See below.

---

## The jobs

| # | Job | Needs | Time | State |
|---|---|---|---|---|
| 1 | Communications | — | — | ✅ Done 2026-08-04 |
| 2 | Complete Live's configuration | — | — | ✅ Done 2026-08-18 |
| 3 | Mail | — | — | ✅ Done 2026-08-05 |
| 4 | SSO | Aidan — the `employeeId` claim | — | ✅ **Working 2026-08-18.** Sign-in, matching and the Canvas entry point all proven; only `employeeId` is outstanding |
| 5 | Content and features | Amanda + Persis | weeks | ⏸ Gated on the face-to-face |
| 6 | Pre-launch | everyone | 1 day | Not started |

---

## Deliberately deferred — do not re-raise

Decided 2026-08-04.

- **Launch date.** Not restated since IT delivered.
- **DU brand review.** Belongs to content.

**Theme accessibility has come off this list** — not because the deferral was wrong, but because its cost changed. It was deferred on the grounds that changing theme meant discarding Amanda's build. There is no build to discard. See `2e`. **Under the communications hold the question waits for the face-to-face**, which costs nothing: she is not building yet, so the moment stays cheap.

---

## Job 1 — Communications ✅

**Done 2026-08-04.** CTLE email sent to Persis and Amanda (`docs/outbound/2026-08-04_ctle.md`). DU IT handled through tickets, except the Aidan thread which has run as email.

**Owed, drafted, and `HELD`:** a short note to Persis and Amanda saying the merge is cancelled and Live is the build environment. The merge sequence was committed to in writing on 08-04, so its withdrawal should be too — that reasoning still stands and the note keeps its value.

**It is not sent.** See the communications hold at the top. The face-to-face covers the same ground with better bandwidth, and the note becomes a written follow-up afterwards rather than the opening move. Keep it current so it is ready to send that day; don't delete it.

---

## Job 2 — Complete Live's configuration ✅ **done 2026-08-18**

**This was "merge Staging into Live", a 2–3 hour operation with a real blast radius. Amanda's answer deleted it**, and the communications hold has now removed what little was left that needed her. Nothing is transferred; nothing waits on CTLE. If you want to see what the merge was, it is in git history at version 1.6.0.

**Total: about 45 minutes, all of it yours.**

### 2a. Deploy the alerts recipient hold — ✅ **DONE 2026-08-18**

`ctle-admin-alerts.php` emails **Persis** on every Administrator login and every role change. Your SSO test is an Administrator login. Creating Amanda's account is a role change. **Either one breaches the communications hold**, and it arrives as an unexplained security alert — the second she'd have had.

`mu-plugins/ctle-alerts-hold.php` narrows the recipient list to `sendres@dom.edu` through the plugin's own `ctle_alert_recipients` filter. It suppresses nothing, so a real security event during the hold still reaches you.

```bash
scp -P 26769 -i ~/.ssh/id_ed25519_ctle_sendres_kinsta \
  mu-plugins/ctle-alerts-hold.php \
  ductle@163.192.209.112:~/public/wp-content/mu-plugins/
cd public
wp plugin list --status=must-use
```

Expect four must-use plugins. Then prove the filter is live rather than merely present:

```bash
wp eval 'print_r( ctle_alert_recipients() );'
```

**One address only.** If Persis's is still there the file did not load — check the path before you sign in anywhere.

**Verified 2026-08-18.** Five must-use plugins present; `ctle_alert_recipients()` returns `sendres@dom.edu` alone. The SSO sign-in test and Amanda's account creation both ran behind it.

> **This file is a debt, not a feature.** Delete it at handover. Left in place it quietly removes the Director from a security control she is meant to hold, and the alerts keep arriving for whoever is left on the list, so nothing looks wrong. It is listed again in Job 6.

### 2b. Create Amanda's Live account yourself — ✅ **DONE 2026-08-18**

She was only ever needed for this because nobody had thought to do it another way. SSO matches on email, so what matters is that an account carrying `anorris@dom.edu` exists **before** her first sign-in. Who created it is irrelevant.

```bash
cd public
wp user create anorris anorris@dom.edu --role=administrator --display_name="Amanda Norris"
wp user list --fields=ID,user_login,user_email,roles --format=csv
```

**Do not pass `--send-email`.** Without it WP-CLI sends nothing; with it, Amanda gets a new-account email and the hold is breached. The generated password is inert — `ctle-hardening.php` has removed password authentication site-wide — so the account cannot be used until she signs in through SSO.

Her `sis_user_id` cannot be stamped yet; it needs her `employeeId`, which we would have to ask her or Aidan for. **Leave it.** It is belt-and-braces for Job 5's Canvas work, not authentication, and it can be stamped the day she first signs in.

> **This closes a real risk, not just a scheduling one.** Had she signed in through SSO with no Live account, the plugin would have created a fresh one — a second Amanda, with the first appearing only on Staging. Matching on email only works if there is something to match.

**Created 2026-08-18 as `anorris`, user ID 4.** Live now holds three users: `pdriveru8gf` (2), `sendresiq78` (3), `anorris` (4).

> ### ✅ Kinsta auto-login matches on email — established by test, 2026-08-18
>
> **The question this answered:** MyKinsta mints a *fresh random* username per environment — Persis is `pdriveru8gf` on Live and `pdriverdebl` on Staging, created one minute apart — so it plainly does not match people by username. If it matched on an internal MyKinsta-user → WP-user-ID mapping instead of on email, then a hand-created `anorris` would be invisible to it, and Amanda's first auto-login on Live would mint a *second* account. That is the precise duplicate this step exists to prevent, just moved one step later.
>
> **It could not be answered by reading the container.** Kinsta's provisioning is server-side: there is no `wp_insert_user` anywhere in `kinsta-mu-plugins`, and no Kinsta-owned user meta on any account.
>
> **So it was tested on Staging, which is disposable.** Steven's Staging account (`sendres19xb`, ID 5) was deleted and recreated by hand as `sendres` (ID 7) with the same address, reproducing Amanda's exact situation — an account with a matching email and no prior Kinsta mapping. MyKinsta auto-login then **landed on `sendres`, ID 7, and created nothing.**
>
> **Consequence, and it reaches further than Amanda.** A pre-created account carrying the right email absorbs *both* provisioning paths — MyKinsta auto-login and SSO. That is what `REQUIREMENTS.md` §5 asks for in its multi-path admin reconciliation, and it now rests on evidence rather than assumption. It also means pre-creating accounts for Ellen or any later admin is safe.
>
> **Staging's user table changed as a side effect:** ID 5 is gone, ID 7 is Steven. Staging is disposable and due to be reset from Live anyway — see `2g`.

### 2c. Finish the configuration that needs nobody — ✅ **DONE 2026-08-18**

Pulled forward out of Job 6. None of it touches content anyone has authored, and all of it is easier now than during a launch checklist.

```bash
cd public
wp option update timezone_string 'America/Chicago'
wp option get timezone_string

wp post delete 1 --force                      # "Hello world!"
wp post delete 2 --force                      # "Sample Page"
wp comment delete 1 --force                   # the default comment
wp post list --post_type=post,page --format=csv --fields=ID,post_title,post_status

wp db query "DROP TABLE IF EXISTS wp_wpmailsmtp_debug_events, wp_wpmailsmtp_tasks_meta;"
wp db tables --format=count
```

**Done 2026-08-18.** Timezone `America/Chicago`; "Hello world!", "Sample Page" and the default comment deleted; both orphan `wp_wpmailsmtp_*` tables dropped. Live now holds one post row — the Privacy Policy draft at ID 3 — and zero comments.

> **`wp comment delete 1 --force` reported "Failed deleting comment 1" and that was correct behaviour, not an error.** Force-deleting post 1 takes its comments with it, so by the time the comment command ran there was nothing left to delete. Verify with `wp comment count` rather than trusting the warning.

**The timezone matters more than it looks.** It is unset, so every timestamp on the site renders as UTC — including the ones inside the admin alert emails, which is precisely where you will be reading times while debugging sign-ins.

**Leave the Privacy Policy draft at page ID 3 alone.** Publishing it is a content decision and stays in Job 6.

**The two `wp_wpmailsmtp_*` tables are orphans** left behind when WP Mail SMTP was deleted on 08-04. Nothing reads them.

### 2d. Password protection stays OFF until handover — a decision, not a task

Version 1.6.0 had Basic Auth going on early with Job 4 testing around it. **That stays reversed**, and the hold gives the decision a cleaner shape than it had.

Basic Auth and SSO are not incompatible — the browser caches the credentials for the session, so a user authenticates once at the edge and then signs in through Entra normally. But it is a second prompt in front of every test, and there is no reason to debug a 401 we chose to create.

Live is reachable at `https://ctle.dom.edu` with `blog_public=0`, and after `2c` it holds nothing but a draft privacy policy. **That exposure is acceptable while the site is empty.**

**Turn it on at handover, and hand the credentials over in the face-to-face.** That is better than SecureTransfer for a shared credential anyway, and it means the protection arrives exactly when the first real content does.

### 2e. The theme decision — Amanda's, and `HELD`

Live runs `twentytwentyfive`. Whatever Amanda builds on needs installing there.

**The question is unchanged and still worth asking:** she picked `educational-university` 0.3.5, nobody has assessed it against WCAG 2.1 AA, and it was deferred because re-theming meant discarding her build. There is no build to discard, so this is the one cheap moment to change course.

**Ask it face-to-face, once, and take the answer.** Nothing is lost by waiting — she is not building yet, so the moment stays cheap. Don't install `educational-university` in advance either; that reads as having decided for her.

### 2f. Two-person recovery stays unsatisfied — accepted, and named

Only Steven holds an SSH key. Amanda's is outstanding and asking for it is a communication.

**This is an accepted risk for the duration of the hold, not an oversight.** It is also not new — it has been open since July. The exposure is that MyKinsta account standing plus one SSH key is the whole recovery path. **Collect her key at the same conversation**, and until then don't let it drift out of view: it is the one item here where waiting has a real cost.

### 2g. Reset Staging from Live — later, optional, 15 min

Once Live has a build, Kinsta push **Live → Staging** gives a true mirror. That direction is Kinsta's supported one and is safe now that Staging holds nothing.

> **The reverse is still forbidden.** Staging → Live would overwrite production's security configuration, and Kinsta carries environment settings unconditionally even on a files-only push.

Not urgent. Nobody needs Staging until there is something to test against.

---

## Job 3 — Mail ✅

**Done 2026-08-05.** `wp_mail()` reaches `ctle-noreply@dom.edu` through Microsoft Graph, scoped by an Exchange access policy to that mailbox alone — verified as a live restriction, sending as `sendres@dom.edu` is refused with `403 [RAOP]`. **Re-run that check if the app registration is ever modified.**

**It was propagation the whole time.** IT's `RestrictAccess` policy was correct when built on 08-04; it took **over 24 hours** to take effect against a documented "up to 30 minutes". While inert, a granted mailbox and a denied one failed identically — which read as the policy not being consulted and sent DU IT down two dead ends. *A discriminating test means nothing until propagation is known to be complete.*

**Client secret expires 2028-08-02.** Its lapse is silent: mail stops, and the only trace is `ctle-mail:` in the PHP error log — nothing emails you, because the thing that would is this plugin.

Remaining: **re-run `scripts/audit-env.sh` and refresh `AS_BUILT.md`** after Job 4 lands, so one capture covers both.

> **Don't let IT "fix" anything here by widening the app's access.** Unrestricted app-only mailbox access would let a leaked secret send as anyone at DU.

---

## Job 4 — SSO ✅ working 2026-08-18; only the `employeeId` claim outstanding

**Aidan delivered on 2026-08-14.** Tenant ID, client ID, client secret and secret expiry arrived by SecureTransfer. All three of the questions that were blocking this job are answered.

### What Aidan answered

| Question | Answer |
|---|---|
| Does the allowlist group exist? | **Yes — `CTLE WordPress`**, containing Persis, Amanda, Ellen and Steven. |
| Is this a separate registration from mail? | **Yes.** He describes it as an additional app registration. |
| What is Entra's `employeeId` populated with? | **The J1 (Jenzabar) value**, for accounts provisioned through a normal onboarding request. |
| Is Ellen's `employeeId` populated? | **Yes.** |

**The `employeeId` answer is the good news and it is worth registering properly.** The custom claims policy was the step most likely to stall this job, and it didn't. It also confirms the assumption behind `sis_user_id`: the value stamped on Steven's and Persis's accounts is the same value Entra holds.

**His caveat, recorded because it will matter later:** accounts created manually — NAPs, student workers (`sw_`) — may have `employeeId` empty. Not relevant to the four test users, all normally provisioned. It becomes relevant at launch if any `DOMFaculty` member was onboarded by hand.

### His open question, and the answer to send back

He asks where the OIDC discovery document is configured in Entra. **It isn't, and there is nothing for him to do.**

The document is published automatically for every tenant. The URL he quoted is correct:

```
https://login.microsoftonline.com/<tenant-id>/v2.0/.well-known/openid-configuration
```

It is a value *we* consume, not one he supplies. And this plugin does not read a discovery document at all — it takes discrete endpoint URLs, so we read them out of the document by hand. Reply drafted at `docs/outbound/2026-08-14_aidan-sso-discovery-and-go.md`.

### ⚠ The identity model in this plan was wrong up to version 1.6.0

Every version of this plan until now said: *identity key `employeeId` → the `sis_user_id` meta already stamped on Steven's and Persis's accounts.* **OpenID Connect Generic cannot do that.** Read from the plugin source on Live, 2026-08-14:

- `get_subject_identity()` returns `$id_token_claim['sub']` and nothing else. **Hardcoded**, not a setting — `includes/openid-connect-generic-client-wrapper.php:796`.
- Users are found by matching that `sub` against the plugin's own user meta key `openid-connect-generic-subject-identity` — `get_user_by_identity()`, same file.
- The **Identity Key** setting only chooses which claim becomes the WordPress *username* on a newly created account. It plays no part in matching.
- The only fallback is **Link Existing Users**, which matches `email_exists($email)` — or `username_exists($username)` if *Identify with Username* is on — at `:589`.

There is no configuration in which an arbitrary claim is matched against an arbitrary user meta key. **Configured as previously written, Aidan's `employeeId` claim would have been emitted perfectly, matched nothing, and silently created duplicate accounts for Steven and Persis — the precise failure the plan was written to prevent.**

**So: match on email.** Both existing accounts carry their real `dom.edu` addresses, and after a first successful sign-in the plugin stamps `openid-connect-generic-subject-identity` with `sub`, so every later sign-in matches on that instead. Email only has to be right once.

`employeeId` keeps its value as the SIS key for Canvas work in Job 5, and as confirmation the claims policy fired. It is no longer load-bearing for authentication.

### ⚠ Leave the userinfo endpoint blank

At `:543` the plugin decides where user claims come from:

```php
if ( ! empty( $this->settings->endpoint_userinfo ) && isset( $token_response['access_token'] ) ) {
	$user_claim = $client->get_user_claim( $token_response );
} else {
	$user_claim = $id_token_claim;
}
```

Graph's `/oidc/userinfo` returns a fixed, small set of standard claims and **will never include `employeeId`**. Set that endpoint and the claim vanishes before WordPress sees it.

**Leave `OIDC_ENDPOINT_USERINFO_URL` unset.** Claims then come from the ID token, where Aidan put them.

**The cost of that choice, which Aidan needs to know about:** with no userinfo endpoint there is no second request to fill in missing values, so the ID token must carry everything account creation needs — `email`, `given_name`, `family_name` and `preferred_username`. If `email` is missing, sign-in fails outright. That is the right failure: loud, at the first test, rather than a silent duplicate.

### ✅ Configured and activated 2026-08-14 — what was actually done

All of the following is **done and verified on Live**; it is recorded rather than pending.

- Constants written into `wp-config.php` above the "That's all" line, backed up first and `php -l` clean. **The client ID and secret written that day were the mail app's — see the correction below; "a valid UUID" and "40 characters" were true of the wrong values.** Corrected 2026-08-18. **`OIDC_ENDPOINT_USERINFO_URL` is absent, which is correct.**
- All five endpoints confirmed against the tenant's live discovery document, tenant `e363050e-fa18-48f7-aefc-7db1230b452a` — the same tenant the mail app uses.
- **Plugin activated.** WordPress bootstraps, `home` returns 200, `wp-admin` 302, and MyKinsta auto-login was confirmed working afterwards **in a fresh private session, not just a reloaded tab** — the reloaded tab only proves an existing cookie, which is not the thing at risk.
- Non-constant settings written: `identity_key` and `nickname_key` `preferred_username`, `email_format` `{email}`, **`displayname_format` `{given_name} {family_name}`** (the default is empty), `identify_with_username` **false** — which is what makes matching fall back to email.
- **Redirect URI verified identical** to the value being asked of Aidan, computed from `admin_url()`.
- **Issuer and JWKS verification are both active.** `issuer` reaches the settings object through a constant-mapping table rather than the defaults array, and the validator enforces the `iss` match.

> **One stray backtick cost twenty minutes.** A `set -e` guard failed silently, and a subsequent hand-edit left a backtick at the start of a `define(` line — PHP then reported "unexpected end of file, expecting `` ` ``" at the *last* line of the file, pointing 17 lines away from the fault. **When PHP reports an unterminated-string error at EOF, the real fault is the first unbalanced quote or backtick, not the reported line.**

### Configure — reference, already done

Secrets go in `wp-config.php`, above the "That's all" line. **Never in this repo, never in the database.**

```php
define( 'OIDC_CLIENT_ID',            '<client-id>' );
define( 'OIDC_CLIENT_SECRET',        '<client-secret>' );
define( 'OIDC_ISSUER',               'https://login.microsoftonline.com/<tenant-id>/v2.0' );
define( 'OIDC_ENDPOINT_LOGIN_URL',   'https://login.microsoftonline.com/<tenant-id>/oauth2/v2.0/authorize' );
define( 'OIDC_ENDPOINT_TOKEN_URL',   'https://login.microsoftonline.com/<tenant-id>/oauth2/v2.0/token' );
define( 'OIDC_ENDPOINT_LOGOUT_URL',  'https://login.microsoftonline.com/<tenant-id>/oauth2/v2.0/logout' );
define( 'OIDC_ENDPOINT_JWKS_URL',    'https://login.microsoftonline.com/<tenant-id>/discovery/v2.0/keys' );
define( 'OIDC_CLIENT_SCOPE',         'openid email profile' );
define( 'OIDC_LINK_EXISTING_USERS',  1 );
define( 'OIDC_CREATE_IF_DOES_NOT_EXIST', 1 );
define( 'OIDC_ENABLE_LOGGING',       1 );
```

Confirm every URL against the discovery document rather than trusting the shapes above.

**`OIDC_ENDPOINT_USERINFO_URL` is deliberately absent.** If you find it defined, remove it.

**`OIDC_ENDPOINT_JWKS_URL` and `OIDC_ISSUER` are not optional.** 3.11.0 was a security release that added JWT signature verification against the tenant's JWKS, specifically to stop token forgery. Without them the plugin warns and the protection is off.

**`OIDC_ENABLE_LOGGING` on for the test round, off before launch** — the log holds decoded claims.

Then in Settings → OpenID Connect Client, which holds what has no constant:

| Setting | Value | Why |
|---|---|---|
| Identity Key | `preferred_username` | The UPN, and only ever the new-account username |
| Nickname Key | `preferred_username` | |
| Display Name Format | `{given_name} {family_name}` | Default is empty, which yields ugly accounts |
| Link Existing Users | **on** | This is the whole matching strategy |
| Identify with User Name | **off** | Leaving it off means match on email, which is what we want |
| Create user if none exists | **on** | JIT provisioning for faculty |
| Enforce Privacy | off | Job 6 decision, not this one |

### Activate — 15 min, and don't do it distracted

> **Password authentication is removed site-wide.** MyKinsta auto-login is the only interactive way into wp-admin. Activating an auth plugin is the one action that can plausibly break it.
>
> **Have a second browser already logged in via MyKinsta auto-login before you activate.** If wp-admin becomes unreachable, `wp plugin deactivate daggerhart-openid-connect-generic` over SSH is the undo, and `ctle-hardening.php` moved aside is the recovery behind that — see `AS_BUILT.md`.

```bash
cd public
wp plugin activate daggerhart-openid-connect-generic
wp plugin list --status=active --format=csv
```

Then immediately confirm MyKinsta auto-login still works. **Don't proceed to testing until you have.**

> **Two known snags on activation.** Live runs WordPress 7.0.2 and the plugin declares "tested up to 6.9.0" — not a known break, but suspect it first if activation misbehaves. And WPS Hide Login is active: the redirect URI targets `admin-ajax.php`, which it doesn't intercept, so the round trip should be unaffected — but any plugin redirect landing on `wp-login.php` will 404. **Verify the whole round trip, not just the callback.**

### Test — Ellen, then you, then Persis, then Amanda

Aidan's group has four members. **Confirm they are the four we asked for before testing** — Ellen, Steven, Persis, Amanda. He said "four users" without naming them.

| Order | Person | What it proves | Under the hold |
|---|---|---|---|
| 1 | **You** | **Matching.** Must land on user ID 3, not a new account. | **Go** — after `2a` |
| 2 | **Ellen** | JIT provisioning against a real faculty identity. Her `employeeId` is confirmed populated. | Steven's call — IT-side, but she is also an adjunct |
| 3 | **Persis** | Matching again, ID 2. | ⛔ After the face-to-face |
| 4 | **Amanda** | Matching, on the account `2b` created for her. | ⛔ After the face-to-face |

> **Order changed 2026-08-14, and again 2026-08-18.** Earlier versions put Ellen first, to prove the flow with a real faculty identity. Then all four joined the group and the reasoning inverted: **take the first failure yourself.** Your account is where the logs are readable, a duplicate costs one `wp user delete`, and if matching works for you it works for the others for the same reason.
>
> **The communications hold now settles the rest of the order for us.** Steps 3 and 4 wait, and that costs less than it appears: your own sign-in exercises the identical code path — `email_exists()` against an existing account — so Persis's and Amanda's tests confirm rather than discover. **What genuinely remains untested until they sign in is only whether their particular Entra records carry the claims we expect**, and Aidan has already confirmed the claim policy fires tenant-wide.

**A duplicate account at step 1 means email matching failed. Stop there** rather than let anyone else sign in and compound it.

After the first successful sign-in, before trusting anything:

```bash
wp user list --fields=ID,user_login,user_email --format=csv        # no new rows for known people
wp user meta get 3 openid-connect-generic-subject-identity          # populated after your sign-in
wp user meta get 3 sis_user_id                                      # still 904238
```

**Then check `employeeId` actually arrived** — with logging on, read the decoded claim out of the plugin's log. If it is present, stamp it forward and Job 5's Canvas work has its key. If it is absent, sign-in still works and only the SIS linkage needs revisiting; tell Aidan, don't panic.

### ⚠ The 08-14 failure was the wrong client ID, ours — closed 2026-08-18

`AADSTS500113` was read as "Aidan hasn't registered a redirect URI." **It wasn't.** `OIDC_CLIENT_ID` and `OIDC_CLIENT_SECRET` both held the **mail** app's values. The error was accurate and we misattributed it: the mail app genuinely has no reply address, because it has no interactive sign-in.

**Aidan diagnosed it from his side**, without access to our configuration, by finding Steven's sign-in attempts logged against the mail registration (`ddf…`) rather than the SSO one (`7b8…`). Four days were spent waiting on a field that was already in place.

**What let it through.** The 08-14 verification recorded *"Client ID is a valid UUID; secret is 40 characters."* Both were true, and both were true of the wrong credentials. **A shape check is not an identity check.** With two registrations live in one tenant, a well-formed value proves nothing about which app it belongs to. The check that would have caught it is one command:

```bash
wp eval 'echo (OIDC_CLIENT_ID === CTLE_MAIL_CLIENT_ID ? "STILL MAIL" : "ok"), PHP_EOL;'
```

**Also worth keeping: `AADSTS700016` is not the only wrong-client-ID signal.** The 08-14 note reasoned that a wrong client ID would give `700016` ("application not found"), so `500113` must mean the registration was incomplete. That inference holds only for an ID belonging to *no* app. An ID belonging to the *wrong* app in the *same tenant* resolves fine and fails later, on whatever that app lacks.

**Corrected with `wp config set`**, not a hand edit — it rewrites the existing `define()` in place, which sidesteps the stray-backtick class of fault entirely. Prefer it for any future `wp-config.php` constant change.

### ✅ Group membership confirmed 2026-08-14

`CTLE WordPress` contains **Persis, Amanda, Ellen and Steven** — exactly the four asked for. **Ellen's `employeeId` is populated**, so the adjunct-record worry is closed and she is a valid test subject.

### Open questions

- **Who maintains the group after launch?** Expected to mirror `DOMFaculty`. If it is hand-maintained rather than synced, faculty hired later silently cannot sign in. **Persis's question, not IT's.**
- **Does CTLE accept the account-linking deviation?** `REQUIREMENTS.md` §5 specifies matching on `sis_user_id` and explicitly *"not by email"*; the plugin cannot do that, and what was built matches on email then `sub`. Recorded as a deviation note in that document rather than an amendment, since the spec is stakeholder-reviewed. **Raise with Persis once SSO is proven working** — evidence first, then the decision.

### The launch-day membership swap is a cutover, not a checkbox

The group starts as those four and switches to `DOMFaculty`-populated membership at launch. **Make that change a couple of days before launch and confirm a real `DOMFaculty` member can sign in.** Exchange policy propagation on Job 3 took over 24 hours; assume nothing about group and assignment changes.

**Steven drops out of the group at that swap.** Persis, Amanda and Ellen all hold faculty appointments and survive it; Director of Learning Technologies sits in the Provost's Office and does not. Admin access survives through MyKinsta auto-login, so this is not a lockout — but SSO sign-in stops working, which means debugging faculty sign-in problems without being able to reproduce them.

**Decide deliberately: a permanent manual addition to the group, or accept auto-login as the only path. Drifting into the second by accident is the failure mode.** Recommendation is the manual addition; it costs Aidan one click and buys the ability to reproduce a faculty report.

### 4a. ✅ Kinsta edge-caching broke SSO — **fixed 2026-08-18 by removing WPS Hide Login**

**Symptom.** `ERROR (invalid-state): Invalid state.` on every callback, including from freshly
opened private windows.

**Cause.** WPS Hide Login moved the login form to a custom slug. Kinsta's cache excludes
`/wp-login.php` and `/wp-admin/`; an arbitrary slug is just a public page, so the edge cached it
under the ordinary policy — **`s-maxage=86400`**. The OpenID button's `href` carries a one-time
`state` whose transient lives **180 seconds** (`openid-connect-generic-client.php:128`), so one
dead nonce was served to every visitor for a day.

```
x-kinsta-cache: HIT   age: 1440   s-maxage=86400
state=3d3d5f6cb12f34e44cecdc7235ad38f9      ← identical across independent requests
```

**A redirect would not have escaped it.** 302 responses on that path were *also* served from cache
(`HTTP/2 302` with `x-kinsta-cache: HIT`), so simply auto-redirecting from the obfuscated slug
would have frozen the nonce in a `Location` header instead of in HTML — the same bug with nothing
left on screen to diagnose it from.

**The fix, and why this shape:**

```bash
wp plugin deactivate wps-hide-login
wp plugin delete wps-hide-login
wp config set OIDC_LOGIN_TYPE auto
```

`auto` mode makes `/wp-login.php` build a fresh authorization request server-side and redirect
straight to Entra — `login-form.php:82`, gated on `pagenow == 'wp-login.php'`, which is precisely
the page WPS Hide Login was 404ing. **Verified 2026-08-18:**

```
GET /wp-login.php → 302 login.microsoftonline.com   x-kinsta-cache: BYPASS
   request 1: state=5e6a0ff6ac16f8ac2036c8b9e2281b29
   request 2: state=3ecbda7f0aed87ee0cf2df6ef2104ea2      ← fresh every time
```

**No user ever sees a WordPress login form**, in either journey: a deep link into a restricted area
redirects through `wp-login.php` to Entra, and the Canvas button targets the same URL.

> **The Canvas button must point at `/wp-login.php`, never at a hand-copied authorize URL.** That
> URL embeds a single-use 180-second `state`; pasted into Canvas it would fail for every user,
> forever, with no cache expiry to rescue it. The reasoning is recorded inline in
> `canvas/ctle-global-nav.js` so nobody re-derives it.

**This was the same plugin failing the same way twice** — it had already forfeited Kinsta's
brute-force ban, measured as `POST /wp-login.php` → 403 at the edge versus `POST` to the custom
path → 200, processed. Removing it restores that ban. **What the obfuscation was protecting is
nothing:** `ctle-hardening.php` removed password authentication site-wide, so there is no form
behind that slug to guess against.

**Recorded as a dated deviation in `REQUIREMENTS.md` §5**, since item (2) of *Administrator access
protection* is a stakeholder-approved requirement. **Two follow-ups from it:**

- **Delete the orphaned `whl_page` option** — it still holds `turbulent-fansite` in the database.
  Harmless and no longer confidential, but it reads as live configuration.
- **Re-state the DU IT security sign-off request without item (2).** The model to sign off is
  MyKinsta 2FA + audit-log alerting + Entra-enforced MFA + no password-authenticated account.
  DU IT, by ticket, **not** covered by the communications hold.

> **The durable lesson:** *a host's protections and its caching policy are both keyed to the URLs
> it knows about. Moving a well-known URL forfeits both, and the caching loss stays invisible until
> something time-sensitive is served stale.*

### 4b. `employeeId` did not arrive in the ID token — a note to Aidan

**Read from the stored claim on user 3 after the successful sign-in**, so this is what Entra
actually issued, not an inference:

```
aud iss iat nbf exp oid rh sub tid uti ver sid
email               sendres@dom.edu
preferred_username  sendres@dom.edu
given_name          Steven
family_name         Endres
name                Endres, Steven

employeeId          *** ABSENT ***
```

**Nothing is broken by this.** Everything account creation and matching need is present, which is
exactly why the userinfo endpoint was deliberately left unset — claims come from the ID token, and
the ID token carries `email`. Sign-in works.

**What it costs is Job 5's Canvas SIS linkage.** `employeeId` was to be the key tying WordPress
accounts to Canvas via `sis_user_id`. Aidan confirmed on 08-14 that Entra holds the J1 value and
that Ellen's is populated — so the value exists on the directory objects; it is the **claims
policy emitting it into this app's ID token** that is missing or not applied to this registration.

**Also worth telling him:** the granted scope came back as `email profile openid User.Read` — he
added `User.Read` when granting consent. Harmless, and we do not use it. **Thank him for the
08-17 diagnosis too** — he found our wrong client ID in the tenant sign-in logs, from his side,
with no access to our configuration.

> **Do not let this be "fixed" by setting a userinfo endpoint.** Graph's `/oidc/userinfo` returns a
> fixed set of standard claims and will never include `employeeId`; setting it would *replace* the
> ID token claims with a smaller set and lose ground rather than gain it.

**`sis_user_id` on the two existing accounts is unaffected** — Steven's is still `904238`, stamped
by hand. Amanda's ID 4 remains unstamped, as Job 6 records.

### 4c. Ellen's test — **after `4b`**, and Steven's call

Her sign-in is the one remaining thing that tests something Steven's did not: **JIT provisioning of
an account that does not yet exist.** Everything else — email matching against a pre-existing
account — is proven.

**Sequenced deliberately behind `4b`.** `4a` is fixed so nothing technical blocks her, but her
sign-in is worth running **once**, not twice: wait until `employeeId` is arriving and stamping onto
accounts correctly, then her test checks provisioning *and* the SIS key in a single pass.

Send her `https://ctle.dom.edu/wp-login.php`; she should land in WordPress without seeing a login
form. Watch for a **third** user appearing with her address — here that is the expected outcome
rather than a failure, since hers is the account that *should* be created. Then check her
`sis_user_id` was stamped from the claim.

**She is a judgement call, not a session's to make:** IT-side, but also an adjunct.

### Then hand back to LT and Pete

Real SSO URL into `canvas/ctle-global-nav.js`, `enabled: true`, beta-test against a teacher and a student, then Pete adds `declared_user_type` to the nightly SIS `users.csv`.

**Done when:** Amanda signs in from Canvas and lands logged into Live on her existing account.

---

## Job 5 — Content and features ⏸ gated on the face-to-face

Amanda and Persis lead. Yours is the plumbing. **Nothing here starts until the conversation happens** — every item needs a decision one of them owns.

**What to walk out of that conversation with**, in priority order:

1. **The SSO group list** — who goes in `CTLE WordPress`, and **who maintains it after launch**. This is the only thing standing between a configured platform and handover. If it is hand-maintained rather than synced from `DOMFaculty`, faculty hired later silently cannot sign in and nobody notices for months.
2. **Amanda's SSH public key** — two-person recovery, outstanding since July
3. **The theme answer** — see `2e`, and don't push
4. **Password-protection credentials handed over** — see `2d`

Then the build work:

1. **Turn Live's password protection on** — the deferred half of `2d`, due the moment real content exists
2. **Course catalog** — custom post type recommended over static pages
3. **Events calendar** — needs the Events Calendar Pro licence decision and a budget line
4. **Search** — activate Relevanssi, index, tune
5. **Forums** — activate wpForo; anonymous visitors must see no forum content. Needs the confidentiality language Persis owns

---

## Job 6 — Pre-launch

1. **Delete `ctle-alerts-hold.php`** — restores Persis to the administrator alerts. **Do this first and verify it**, because nothing about a healthy-looking site reveals that she is missing from a security control:

   ```bash
   rm ~/public/wp-content/mu-plugins/ctle-alerts-hold.php
   cd public && wp eval 'print_r( ctle_alert_recipients() );'   # both addresses back
   ```

2. **WCAG 2.1 AA audit** — scope depends on Amanda's answer in `2e`
3. **DU brand review**
4. **Publish the privacy policy** — the draft at page ID 3 survives, since nothing is being imported over it
5. **Flip the switches:** `blog_public=1`, remove password protection, turn `OIDC_ENABLE_LOGGING` off, upload the Canvas button
6. **Stamp `sis_user_id` on Amanda's account** once her `employeeId` is known — deferred from `2b`, wanted by the Canvas and SIS work

> **Sample content, timezone and the orphan `wp_wpmailsmtp_*` tables moved to `2c`** and are done there. Nothing needed anyone's permission and all three are cheaper away from a launch checklist.

**Done when:** a logged-out stranger reaches `https://ctle.dom.edu` and sees a finished site.

---

## Not yours

- **Amanda** — SSH key, the theme answer, then the content build on Live. **Her Live profile is no longer hers** — `2b` creates it, so she is off the critical path entirely
- **Persis** — **the SSO group list and who maintains it after launch, which is the one blocking CTLE input**; then Events Calendar licence, catalog structure, confidentiality language, forum categories, admin training
- **Aidan** — **why `employeeId` is absent from the SSO app's ID token** (`4b`) — **the only open item on SSO**; then hold the group membership swap until the launch cutover. Admin consent, the redirect URI, the `email` claim, group membership and Ellen's `employeeId` are all **delivered and confirmed** — consent granted 2026-08-18 and sign-in proven the same day
- **Pete** — `declared_user_type` in the nightly Canvas import, after SSO works
- **Post-launch** — off-site 30-day backup, HSTS, disabling `ductle.kinsta.cloud`
- **2026-08-24** — **verify TLS auto-renewed; the certificate expires 08-31.** No longer moot: DNS resolves, so validation can complete. Put it in a calendar
- **2028-07-02** — ask IT to reissue the Graph mail client secret, expiring **2028-08-02**. Its lapse is silent
- **`<SSO secret expiry>`** — diary a reminder a month before the SSO client secret expires, on the same reasoning. Fill the date in from SecureTransfer

---

## Changelog

| Version | Date | Notes |
|---|---|---|
| 1.10.0 | 2026-08-18 | **SSO is finished bar one claim, and Job 2 is closed.** `4a` fixed at the root: WPS Hide Login removed and `OIDC_LOGIN_TYPE` set to `auto`, so `/wp-login.php` mints a fresh authorization request server-side and redirects straight to Entra — verified as distinct `state` values on consecutive requests with `x-kinsta-cache: BYPASS`, and **no WordPress login form is shown to any user** in either journey. Recorded the trap that a hand-copied authorize URL cannot be used as the Canvas target, because it embeds a single-use 180-second nonce; `canvas/ctle-global-nav.js` now points at `/wp-login.php` with that reasoning inline. Recorded that **auto-redirecting from the obfuscated slug would not have worked either** — Kinsta caches 302s on that path, so the nonce would have frozen in a `Location` header with nothing on screen to diagnose. Logged as a dated deviation in `REQUIREMENTS.md` §5 against *Administrator access protection* item (2), with two follow-ups: delete the orphaned `whl_page` option, and re-state the DU IT sign-off request without the obfuscated URL. `4c` unblocked. Remaining on Job 4: **`employeeId` absent from the ID token**, which costs Job 5's Canvas linkage and nothing else. |
| 1.9.0 | 2026-08-18 | **SSO signs in and matches correctly — Job 4's core question is answered.** Aidan granted admin consent; Steven signed in at 21:33:07 UTC, landed on user ID 3 with no duplicate created, `openid-connect-generic-subject-identity` stamped and `sis_user_id` intact at `904238`. **Found the defect that made it fail twice first:** WPS Hide Login's custom slug is not in Kinsta's cache exclusions, so the edge cached the login page for 24 hours and served one frozen 180-second `state` to every visitor — measured as identical `state` values across independent requests with `x-kinsta-cache: HIT`. Recorded as `4a` with a decision attached, because **this is the same plugin failing the same way twice**: it already forfeited Kinsta's brute-force protection for the identical reason. **`employeeId` is absent from the ID token** — everything authentication needs is present, so this costs only Job 5's Canvas linkage; recorded as `4b`. **Established by test that Kinsta auto-login matches on email**, not on username or an internal mapping: Steven's Staging account was deleted and recreated by hand, and auto-login adopted it rather than minting a duplicate. That makes pre-created accounts safe for both provisioning paths and puts `REQUIREMENTS.md` §5's multi-path reconciliation on evidence. `2a`, `2b` and `2c` all completed — alerts hold deployed and verified, Amanda created at ID 4, timezone set, sample content and orphan mail tables removed. |
| 1.8.0 | 2026-08-18 | **Communications hold on Persis and Amanda, and a replan around it.** Political rather than technical, so both drafted notes are marked `HELD` — including the one explaining the 08-05 alert, which now belongs in the face-to-face. **Found that the hold has a machine-side leak:** `ctle-admin-alerts.php` emails Persis on every Administrator login and role change, so a single SSO test would have breached it silently. Closed with `ctle-alerts-hold.php`, which narrows recipients through the plugin's own filter rather than unhooking anything, and is recorded as a debt to delete at handover. **Job 2 stops waiting on Amanda:** we create her Live account ourselves, which was never actually hers to do and which also closes the duplicate-account risk her first sign-in carried. Pulled timezone, sample content and the orphan mail tables forward from Job 6 — none needed anyone's permission. Password protection now goes on at handover with credentials passed in person. Job 5 gated on the conversation, whose one required output is **the SSO group list and who maintains it**. Recorded that Steven's own sign-in exercises the same `email_exists()` path as Persis's and Amanda's, so deferring their tests confirms rather than discovers. |
| 1.7.3 | 2026-08-18 | **The redirect URI was never the blocker.** `OIDC_CLIENT_ID` and `OIDC_CLIENT_SECRET` held the mail app's credentials, not the SSO app's — Aidan found it in the tenant sign-in logs on 08-17 after confirming the redirect URI and `email` claim had been present on the SSO registration since 08-14. Corrected with `wp config set` and verified by comparison against the mail constants rather than by shape. **Sign-in now reaches the right app and the redirect URI matches**; it stops at a tenant admin-consent prompt for `openid email profile`, which are ordinarily user-consentable, so the cause is the tenant user-consent policy. Consent requested, together with a pre-emptive question about whether *Assignment required* is on and the group assigned to the app. Recorded the durable lesson — **a shape check is not an identity check** — and the corollary that `AADSTS700016` only indicates an ID belonging to no app at all, not one belonging to the wrong app in the same tenant. |
| 1.7.2 | 2026-08-14 | **End of day.** Reply to Aidan **sent**, asking for the redirect URI. He confirmed the group holds exactly the four intended people and that **Ellen's `employeeId` is populated**, closing both remaining IT questions. Cross-document audit found a real conflict: `REQUIREMENTS.md` §5 specifies account linking by `sis_user_id` and explicitly *"not by email"*, which the plugin cannot do. Recorded there as a dated deviation note rather than an amendment, since the spec is stakeholder-reviewed, and added to the open questions for Persis once SSO is proven. Test order adjusted — Ellen moves ahead of Amanda, whose test is gated on her Live account. The CTLE note remains unsent and is what asks Amanda for that account. |
| 1.7.1 | 2026-08-14 | **SSO configured, activated and verified on Live.** Constants written, plugin activated, auto-login re-confirmed in a fresh private session rather than a reloaded tab, non-constant settings written, redirect URI verified identical to the registered value, issuer and JWKS verification confirmed active. First sign-in returned `AADSTS500113` — no reply address on the registration — which also proves the client ID valid and the request well-formed. **One field from Aidan is all that remains.** Test order inverted to put Steven first: with all four now in the group, taking the first failure on the account whose logs are readable beats spending an external favour on it. |
| 1.7.0 | 2026-08-14 | **Both blockers closed and Job 2 mostly deleted.** B1: `ctle.dom.edu` resolves, serves 200 and holds a valid certificate — the 08-06 NXDOMAIN came from `ns1.dom.edu`, a name that no longer resolves at all, so it was either restored or never really absent; unresolvable and not worth chasing. B2: Amanda confirms Staging was only ever basic testing, so the database instability threatened nothing — no backup, no Kinsta ticket, and the merge is cancelled outright. Job 2 drops from a 3-hour transfer to Amanda creating a profile. **Job 4's identity model corrected against the plugin source:** matching is on `sub` then email, never on an arbitrary claim against `sis_user_id`, so the previous configuration would have silently duplicated the two stamped accounts. Recorded that setting the userinfo endpoint would strip `employeeId` from the claims. Aidan delivered credentials and answered all three blocking questions; `employeeId` carries the J1 value as hoped. Basic Auth deferred to Job 5 so it isn't in front of SSO testing, and theme accessibility un-deferred because a rebuild no longer costs a build. |
| 1.6.0 | 2026-08-06 | **Archive integrity check.** Recaptured both environments and reconciled every live document. Two blockers found and recorded as B1 and B2: `ctle.dom.edu` returned authoritative NXDOMAIN despite `AS_BUILT` recording DNS as delivered under ticket 26363781, and Staging's MariaDB had gone down twice on the environment then believed to hold the only copy of the build. Took a verified defensive dump of Staging. Added export verification to Job 2, a DNS precondition to Jobs 4 and 6, and the unset site timezone to pre-launch. |
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

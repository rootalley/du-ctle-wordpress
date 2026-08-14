# PLAN — current state to production

**This file is the only to-do list.** If it disagrees with any other document, fix this one first.

For what the site *currently is*, see `docs/AS_BUILT.md`. Don't read it to find out what to do.

---

# → RIGHT NOW: waiting on Aidan for one field. Then sign in and test.

**Everything on our side of Job 4 is built, activated and verified.** The only thing missing is a redirect URI on the Entra app registration. The first sign-in attempt on 2026-08-14 returned:

```
AADSTS500113: No reply address is registered for the application.
```

**Asked for and sent 2026-08-14** — `docs/outbound/2026-08-14_aidan-sso-discovery-and-go.md`. That error also confirms the client ID is valid and the request well-formed; a wrong client ID gives `AADSTS700016`.

**When he replies, the next action is a sign-in test — 15 minutes.** Private window → your custom login path (`wp option get whl_page`) → the OpenID Connect button. Then:

```bash
cd public
wp user list --fields=ID,user_login,user_email,roles --format=csv
wp user meta get 3 openid-connect-generic-subject-identity
wp user meta get 3 sis_user_id
```

Expect two users still, ID 3 gaining a subject-identity value, `sis_user_id` unchanged at `904238`. **A third user means email matching failed — stop there** and read the plugin log before anyone else signs in.

> **Second thing owed, and it gates part of the testing:** the CTLE note at `docs/outbound/2026-08-14_ctle-merge-cancelled.md` is still unsent. **Amanda cannot be tested until she has a Live account**, and that note is what asks her for it.

> **Also still open, from before travel:** tell Persis the Administrator-login alert she received on 08-05 was a test.

---

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
| 2 | Stand Live up as the build environment | Amanda | 40 min | ⏸ Waiting on her · **was a 3-hour merge, now isn't** |
| 3 | Mail | — | — | ✅ Done 2026-08-05 |
| 4 | SSO | Aidan — redirect URI | testing only | **→ ACTIVE.** Our side done; one field outstanding |
| 5 | Content and features | Amanda + Persis | weeks | Not started |
| 6 | Pre-launch | everyone | 1 day | Not started |

---

## Deliberately deferred — do not re-raise

Decided 2026-08-04.

- **Launch date.** Not restated since IT delivered.
- **DU brand review.** Belongs to content.

**Theme accessibility has come off this list** — not because the deferral was wrong, but because its cost changed. It was deferred on the grounds that changing theme meant discarding Amanda's build. There is no build to discard. See Job 2c.

---

## Job 1 — Communications ✅

**Done 2026-08-04.** CTLE email sent to Persis and Amanda (`docs/outbound/2026-08-04_ctle.md`). DU IT handled through tickets, except the Aidan thread which has run as email.

**Owed now:** a short note to Persis and Amanda saying the merge is cancelled and Live is the build environment — the merge sequence was committed to in writing on 08-04, so its withdrawal should be too. Drafted at `docs/outbound/2026-08-14_ctle-merge-cancelled.md`.

---

## Job 2 — Stand Live up as the build environment

**This was "merge Staging into Live", a 2–3 hour operation with a real blast radius. Amanda's answer deleted it.** Nothing is being transferred. No export, no import, no author remapping, no search-replace, no truncated-dump risk. All of that machinery is gone; if you need to see what it was, it is in git history at version 1.6.0.

What remains is small.

### 2a. Amanda creates her Live profile — her action, 10 min

MyKinsta → Live → *Create admin and log in*. Then find and stamp her:

```bash
cd public
wp user list --fields=ID,user_login,user_email --format=csv
wp user meta update <her-ID> sis_user_id <her-employeeId>
```

> **The stamp is now belt-and-braces, not the mechanism.** SSO matches on email, not on `sis_user_id` — see Job 4. Stamp it anyway: Canvas and the SIS work in Job 5 want the Jenzabar ID on the account, and it costs one command.

While you have her, get her SSH public key — two-person recovery still isn't satisfied.

### 2b. Leave Live's password protection OFF until SSO is tested — a decision, not a task

Version 1.6.0 had Basic Auth going on early, and Job 4 testing around it. **Reverse that.** Basic Auth sits in front of the Entra redirect, and there is no reason to debug a 401 that we chose to create.

Live is currently reachable at `https://ctle.dom.edu` with `blog_public=0` and nothing on it but `Hello world!` and a `Sample Page`. That is an acceptable exposure for a few days. **Turn Basic Auth on at the start of Job 5**, when there is real content to keep private, and SecureTransfer the credentials to Amanda and Persis then.

### 2c. The theme decision — Amanda's, and it just got cheap

Live runs `twentytwentyfive`. Whatever Amanda builds on needs installing there.

**Ask her before she starts, once:** she picked `educational-university` 0.3.5, nobody has assessed it for WCAG 2.1 AA, and the reason we deferred that question was that re-theming meant throwing away her build. It doesn't any more — she has an empty site either way. **If she is going to move, now is the only cheap moment.** If she wants to stay with it, that is a fine answer and the accessibility work goes back to being a Job 6 audit item.

Don't push. Ask once, take the answer, record it here.

### 2d. Reset Staging from Live — later, optional, 15 min

Once Live has the build, Kinsta push **Live → Staging** gives a true mirror. That direction is Kinsta's supported one and is now safe, because Staging holds nothing.

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

## Job 4 — SSO ← **you are here**

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

- Constants written into `wp-config.php` above the "That's all" line, backed up first and `php -l` clean. Client ID is a valid UUID; secret is 40 characters; **`OIDC_ENDPOINT_USERINFO_URL` is absent, which is correct.**
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

| Order | Person | What it proves |
|---|---|---|
| 1 | **You** | **Matching.** Must land on user ID 3, not a new account. |
| 2 | **Persis** | Matching again, ID 2. |
| 3 | **Ellen** | JIT provisioning — no existing account, so this is the easy case. Her `employeeId` is confirmed populated. |
| 4 | **Amanda** | Matching — **gated on 2a**, since she has no Live account yet. |

> **Order changed 2026-08-14.** Earlier versions put Ellen first, on the reasoning that she proves the flow with a real faculty identity. That held when she was to be the group's only member and Steven was travelling. Now all four are in the group and the reasoning inverts: **take the first failure yourself.** Your account is the one where the logs are readable, a duplicate costs one `wp user delete`, and if matching works for you it works for Persis and Amanda for the same reason. A failure on Ellen's account costs an external favour and some credibility with IT.

**A duplicate account at step 1 means email matching failed. Stop there** rather than let anyone else sign in and compound it.

After the first successful sign-in, before trusting anything:

```bash
wp user list --fields=ID,user_login,user_email --format=csv        # no new rows for known people
wp user meta get 3 openid-connect-generic-subject-identity          # populated after your sign-in
wp user meta get 3 sis_user_id                                      # still 904238
```

**Then check `employeeId` actually arrived** — with logging on, read the decoded claim out of the plugin's log. If it is present, stamp it forward and Job 5's Canvas work has its key. If it is absent, sign-in still works and only the SIS linkage needs revisiting; tell Aidan, don't panic.

### ✅ Group membership confirmed 2026-08-14

`CTLE WordPress` contains **Persis, Amanda, Ellen and Steven** — exactly the four asked for. **Ellen's `employeeId` is populated**, so the adjunct-record worry is closed and she is a valid test subject.

### Open questions

- **Who maintains the group after launch?** Expected to mirror `DOMFaculty`. If it is hand-maintained rather than synced, faculty hired later silently cannot sign in. **Persis's question, not IT's.**
- **Does CTLE accept the account-linking deviation?** `REQUIREMENTS.md` §5 specifies matching on `sis_user_id` and explicitly *"not by email"*; the plugin cannot do that, and what was built matches on email then `sub`. Recorded as a deviation note in that document rather than an amendment, since the spec is stakeholder-reviewed. **Raise with Persis once SSO is proven working** — evidence first, then the decision.

### The launch-day membership swap is a cutover, not a checkbox

The group starts as those four and switches to `DOMFaculty`-populated membership at launch. **Make that change a couple of days before launch and confirm a real `DOMFaculty` member can sign in.** Exchange policy propagation on Job 3 took over 24 hours; assume nothing about group and assignment changes.

**Steven drops out of the group at that swap.** Persis, Amanda and Ellen all hold faculty appointments and survive it; Director of Learning Technologies sits in the Provost's Office and does not. Admin access survives through MyKinsta auto-login, so this is not a lockout — but SSO sign-in stops working, which means debugging faculty sign-in problems without being able to reproduce them.

**Decide deliberately: a permanent manual addition to the group, or accept auto-login as the only path. Drifting into the second by accident is the failure mode.** Recommendation is the manual addition; it costs Aidan one click and buys the ability to reproduce a faculty report.

### Then hand back to LT and Pete

Real SSO URL into `canvas/ctle-global-nav.js`, `enabled: true`, beta-test against a teacher and a student, then Pete adds `declared_user_type` to the nightly SIS `users.csv`.

**Done when:** Amanda signs in from Canvas and lands logged into Live on her existing account.

---

## Job 5 — Content and features

Amanda and Persis lead. Yours is the plumbing.

1. **Turn Live's password protection on** — the deferred half of 2b, due the moment real content exists
2. **Course catalog** — custom post type recommended over static pages
3. **Events calendar** — needs the Events Calendar Pro licence decision and a budget line
4. **Search** — activate Relevanssi, index, tune
5. **Forums** — activate wpForo; anonymous visitors must see no forum content. Needs the confidentiality language Persis owns

---

## Job 6 — Pre-launch

1. **WCAG 2.1 AA audit** — scope depends on Amanda's answer in 2c
2. **DU brand review**
3. **Delete the sample content** — `Hello world!`, `Sample Page`, the default comment
4. **Set the site timezone** — currently unset, so every timestamp including the admin alert emails renders as UTC rather than Central
5. **Publish the privacy policy** — the draft at page ID 3 survives, since nothing is being imported over it
6. **Flip the switches:** `blog_public=1`, remove password protection, turn `OIDC_ENABLE_LOGGING` off, upload the Canvas button
7. **Drop the two orphan WP Mail SMTP tables** — `wp_wpmailsmtp_debug_events`, `wp_wpmailsmtp_tasks_meta`

**Done when:** a logged-out stranger reaches `https://ctle.dom.edu` and sees a finished site.

---

## Not yours

- **Amanda** — Live profile, SSH key, the theme answer, then the content build on Live
- **Persis** — who may sign in **and who maintains that list after launch**, Events Calendar licence, catalog structure, confidentiality language, forum categories, admin training
- **Aidan** — **the redirect URI, which is the only thing blocking SSO**; then confirm the four group members, check Ellen's `employeeId`, and hold the group membership swap until the launch cutover
- **Pete** — `declared_user_type` in the nightly Canvas import, after SSO works
- **Post-launch** — off-site 30-day backup, HSTS, disabling `ductle.kinsta.cloud`
- **2026-08-24** — **verify TLS auto-renewed; the certificate expires 08-31.** No longer moot: DNS resolves, so validation can complete. Put it in a calendar
- **2028-07-02** — ask IT to reissue the Graph mail client secret, expiring **2028-08-02**. Its lapse is silent
- **`<SSO secret expiry>`** — diary a reminder a month before the SSO client secret expires, on the same reasoning. Fill the date in from SecureTransfer

---

## Changelog

| Version | Date | Notes |
|---|---|---|
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

# canvas

Canvas-side assets for the CTLE launch mechanism. **Owner: DU Learning Technologies** (LT-1 / LT-3).

Since LTI was withdrawn (HANDOFF decision 10), faculty reach the CTLE WordPress site by clicking the **CTLE button in the Canvas global navigation**, retargeted to the site's Entra **SSO-initiation URL**. Canvas and CTLE WordPress share an Entra tenant, so the click completes SSO silently and lands the user logged in.

| File | Purpose |
|---|---|
| `ctle-global-nav.js` | Faculty-gates the global-nav CTLE button and points it at the SSO-initiation URL. |

---

## The gate is cosmetic — say this out loud in review

Access to the CTLE site is enforced by the **Entra faculty group** on the enterprise app (assignment required, IT-1). The `declared_user_type` check in this script only decides whether a button is drawn. A student who learns the URL still cannot sign in. Nothing in this file is an access control, and it must never be treated as one.

---

## Installing

Canvas allows **one custom JavaScript file per account theme**. If DU already has a theme JS bundle, **append this IIFE to it** — do not replace the file.

1. Canvas Admin → **Themes** → open the DU theme → **Edit Theme**
2. **Upload** tab → Custom JavaScript file
3. Save and apply. Global JS applies account-wide, including sub-accounts.

**Test in beta first.** `dominican.beta.instructure.com` is refreshed from production weekly, so theme JS uploaded to beta is a safe rehearsal that never touches faculty. Verify against a real teacher account and a real student account before the production upload.

## Configuration

Everything is in the `CONFIG` object at the top of the file.

| Key | Default | Notes |
|---|---|---|
| `enabled` | `false` | Master switch. While false the script returns immediately and touches nothing — deploying it early is a guaranteed no-op on today's button. |
| `ssoUrl` | `https://ctle.dom.edu/` | **Placeholder.** Replace with the SSO-initiation URL once IT-1 lands and §13 SSO config is done. |
| `label` / `hrefMatch` | `CTLE` / `ctle` | How the existing nav item is located when it carries no `data-ctle-nav` attribute. |
| `createIfMissing` | `true` | Inject a nav item if none is found. Injection happens only for faculty, so there is no flash. |
| `failOpen` | `false` | On lookup failure the button stays hidden. Flip to `true` only if a Canvas-side problem is hiding it from faculty. |
| `facultyTypes` | `['teacher']` | Which `declared_user_type` values count as faculty. |
| `cacheTtlMinutes` | `120` | `sessionStorage` cache, so the lookup costs one API call per session, not one per page view. |
| `debug` | `false` | Console logging under the `[ctle-nav]` prefix. |

### Rollout order

1. **Now** — upload to Canvas **beta** with `enabled: true`, `debug: true`, and the placeholder `ssoUrl`. Confirm the button appears for a teacher account and not for a student account. This is fully testable before SSO exists.
2. **When IT-1 lands** — set `ssoUrl` to the real SSO-initiation URL; re-test the end-to-end click in beta.
3. **At launch** — upload to production with `enabled: true` and `debug: false`.

Leaving step 3 until launch matters: the CTLE site is `noindex`ed and unannounced, and pointing faculty at it early surfaces an unfinished site.

---

## SIS specification — `declared_user_type`

The script reads `declared_user_type` from `GET /api/v1/users/self/logins`, which **non-admin users can read for themselves** (validated 2026-07-28). The value is not in `window.ENV` and not on `/api/v1/users/self`; the logins endpoint is the only client-side source.

### What changes

Add the `declared_user_type` column to the nightly SIS `users.csv` export and populate it with `teacher` for every faculty row.

```csv
user_id,login_id,first_name,last_name,email,status,declared_user_type
1001,jsmith,Jane,Smith,jsmith@dom.edu,active,teacher
1002,rjones,Robin,Jones,rjones@dom.edu,active,student
```

**Allowed values** (Canvas enum — anything else is rejected at import):
`administrative` · `observer` · `staff` · `student` · `teacher` · `unknown`

### Notes for whoever owns the export

- The field is **per login**, not per user — it lives on the `users.csv` row.
- It is **currently unused** at DU, so there is no collision and no existing consumer to break.
- Adding a column to an existing export is additive; rows already present are updated in place.
- ⚠️ **If the nightly import runs in batch mode**, confirm the change does not alter which rows are included — batch mode deletes users absent from the file. Adding a column should not affect row selection, but verify on a single-file test import before the nightly job picks it up.
- Faculty who are *not* in the SIS export (adjuncts provisioned manually, for instance) will not get the button. They can still reach the site directly and sign in, because Entra — not this field — governs access. If that population is large, tell CTLE so the site carries a direct link somewhere findable.

### Verifying

As a faculty user, in the browser console on any Canvas page:

```js
fetch('/api/v1/users/self/logins', { credentials: 'same-origin' })
  .then(r => r.json())
  .then(l => console.log(l.map(x => x.declared_user_type)));
// → ["teacher"]
```

As an admin, for a specific user: `GET /api/v1/users/:id/logins`.

---

*This directory is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository. See `docs/kinsta_onboarding.md` §16 and `docs/REQUIREMENTS.md` §6.*

# Ticket update to DU IT: Graph mail blocked by app-only access policy

**Sent 2026-08-04**, appended to the existing mailbox/Graph ticket. Awaiting IT.

---

**Subject:** CTLE WordPress — Graph `sendMail` returning 403 (app-only access policy)

The app registration and consent are correct. The remaining block is on the Exchange side.

**What is confirmed working.** Using the tenant ID, client ID and secret you supplied, the
client-credentials flow issues a token successfully. Decoding it shows:

```
aud:    https://graph.microsoft.com
roles:  Mail.Send
idtyp:  app
```

So the application permission is present and admin consent has been granted. Nothing further
is needed on the app registration itself.

**What fails.** `POST https://graph.microsoft.com/v1.0/users/ctle-noreply@dom.edu/sendMail`
returns:

```
403 — Access to OData is disabled: [RAOP] : Blocked by tenant configured
      AppOnly AccessPolicy settings.
```

That message is Exchange Online's application access policy layer, not Entra. It means one of
three things: no `RestrictAccess` policy exists for this app in a tenant that denies app-only
mailbox access by default; a policy exists but is scoped to a group that does not contain
`ctle-noreply@dom.edu`; or an explicit `DenyAccess` policy covers the app, which wins over any
grant.

**To confirm which**, in Exchange Online PowerShell:

```powershell
Test-ApplicationAccessPolicy -Identity ctle-noreply@dom.edu -AppId <client-id>
Get-ApplicationAccessPolicy | Where-Object { $_.AppId -eq "<client-id>" }
```

**To fix it.** A `RestrictAccess` policy scoped to a group holding only that one mailbox is
exactly the control we asked for in the original ticket — the goal is not to remove the
restriction but to scope it correctly:

```powershell
New-DistributionGroup -Name "CTLE Graph Mail Scope" -Type Security `
  -PrimarySmtpAddress ctle-graph-scope@dom.edu -Members ctle-noreply@dom.edu

New-ApplicationAccessPolicy -AppId <client-id> `
  -PolicyScopeGroupId ctle-graph-scope@dom.edu `
  -AccessRight RestrictAccess `
  -Description "CTLE WordPress noreply sender"
```

If `Get-ApplicationAccessPolicy` shows a `DenyAccess` entry covering this app, remove it —
a deny overrides the grant above regardless of scope.

**Please note the propagation delay.** These policies can take up to 30 minutes to take
effect. A test immediately after the change may still return the same 403, so please allow
that window before concluding it did not work.

**How we will verify.** Re-running the same `sendMail` call. Success is an HTTP 202 with an
empty body and the message arriving at `sendres@dom.edu`. I will confirm on the ticket either
way.

---

## Notes for us, not for the ticket

- Everything on the WordPress side is deployed and correct. `ctle-mail.php` is live, WP Mail
  SMTP is gone, and the constants are in `wp-config.php`. This is entirely IT's to resolve.
- The `RestrictAccess` scoping is a control we asked for deliberately. Do not let it be
  "fixed" by granting the app unrestricted app-only mailbox access across the tenant — that
  would let a leaked secret send as anyone at DU.
- The client secret's expiry still needs diarising.

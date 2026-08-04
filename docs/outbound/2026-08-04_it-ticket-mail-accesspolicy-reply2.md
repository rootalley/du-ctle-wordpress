# DRAFT — second reply to DU IT: the block is above the app access policy

**Status:** draft, not sent. Append to the same mailbox/Graph ticket.

---

Thanks — your policy is correct, and I don't think it's what's blocking us.

**On your question about the group's delivery management and message approval settings:
those don't apply here.** `CTLE-NoReplyGroup` is only acting as a scoping container for the
access policy — Exchange reads its membership to decide which mailboxes the app may touch.
Nothing ever sends mail *to* the group, so its delivery restrictions, moderation and
membership-approval settings are never evaluated. Please leave them as they are; changing
them would add moving parts without addressing this.

**A test that narrows it down.** I ran the same `sendMail` call twice from WordPress, changing
only which mailbox the app sends as:

| Sending as | Expected per your `Test-ApplicationAccessPolicy` | Actual |
|---|---|---|
| `ctle-noreply@dom.edu` | Granted | `403 [RAOP] Access to OData is disabled` |
| `sendres@dom.edu` | Denied | `403 [RAOP] Access to OData is disabled` — *identical* |

A mailbox your policy explicitly **denies** and one it explicitly **grants** produce the same
error, byte for byte. If the CTLE-NoReplyGroup policy were the thing refusing us, those two
would differ. They don't — which suggests the app is being refused *before* Exchange gets as
far as evaluating per-mailbox scope. Something tenant-wide is declining app-only OData access
for this application, and the access policy you built is not being reached.

That also explains why `Test-ApplicationAccessPolicy` returns `Granted` while the live call
fails: that cmdlet only evaluates the mailbox policy layer, which is genuinely fine.

**Two things worth checking, in this order.**

1. The EWS/OData application gate. The "Access to OData is disabled" wording is characteristic
   of this switch, and it governs Graph REST mailbox calls as well as EWS:

   ```powershell
   Get-OrganizationConfig | fl EwsEnabled, EwsApplicationAccessPolicy, EwsAllowList, EwsBlockList
   Get-CASMailbox ctle-noreply@dom.edu | fl EwsEnabled, OWAEnabled, MAPIEnabled
   ```

   If `EwsApplicationAccessPolicy` is `EnforceAllowList`, our app has to be added to
   `EwsAllowList` — otherwise it is refused regardless of any ApplicationAccessPolicy. If
   `EwsEnabled` is `$false` on the mailbox, that alone would do it.

2. Whether the tenant has moved to **RBAC for Applications**, which supersedes
   `ApplicationAccessPolicy`. Where that model is in force, a policy can look correct and
   still be ignored, because entitlement now comes from a role assignment instead:

   ```powershell
   Get-ManagementRoleAssignment -RoleAssigneeType App | fl Name, Role, App, CustomResourceScope
   ```

   If nothing is assigned, the equivalent of what you built is:

   ```powershell
   New-ManagementRoleAssignment -App <client-id> `
     -Role "Application Mail.Send" `
     -CustomResourceScope "ctle-mailscope@dom.edu"
   ```

**One caveat on timing.** Microsoft's own guidance is that these changes can take several
hours to propagate, not the 30 minutes I quoted earlier — I was wrong about that. My last test
was roughly two hours after you created the policy. Before you spend much time on the above,
it may be worth my simply re-testing tomorrow morning; I'll do that regardless and report back
either way.

Happy to run anything from the WordPress side that would help.

---

## Notes for us, not for the ticket

- Nothing here is ours to fix. `ctle-mail.php` is deployed, the constants are in
  `wp-config.php`, and the token carries `roles: Mail.Send` as an app identity. The request
  reaches Graph and is refused by Exchange.
- The discriminator test is the useful artefact — keep it. If IT changes something and the two
  mailboxes start producing *different* errors, that alone proves the app policy layer is now
  being reached.
- Still do not accept a fix that grants the app unrestricted app-only mailbox access.

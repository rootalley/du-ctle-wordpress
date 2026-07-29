# NOW

This file answers one question: **what do I do next?**

Everything else in this repo is reference. You don't need to read it. If this file and any other file disagree, fix this one first.

---

## → Next

**Send Amanda the email telling her not to push staging to live.**

It's already written. Open `docs/outbound/2026-07-29-amanda-do-not-push.md`, copy everything under *"Subject:"*, add her address, send.

⏱ **10 minutes.** Why it's first: she can undo three weeks of work with one button and has no reason to think it's dangerous.

- [ ] Sent

**When that's done, you're done for the day.** Anything below is a bonus, not a debt.

---

## After that, in order

- [ ] **Match staging's PHP to Live** — MyKinsta → Staging → Tools → PHP engine → 8.4. Also turn on redirect-to-primary. ⏱ 15 min
- [ ] **Ask Kinsta one question** — live chat: *"If I push files from staging to live, does it delete files that exist on live but not on staging?"* Paste the answer to me. ⏱ 5 min
- [ ] **Send the IT email** — `docs/outbound/2026-07-29-it.md`, already written, just needs Ellen's address. ⏱ 5 min
- [ ] **SecureTransfer to Persis + Amanda** — the login path and the staging password, one message each. ⏱ 10 min
- [ ] **Send the CTLE email** — `docs/outbound/2026-07-29-ctle.md`. Only after the Amanda email above. ⏱ 5 min
- [ ] **Merge Amanda's staging work onto Live** — don't start until she replies about what she's built. Do it with me, not alone. ⏱ ~1 hr

### Only got 5 minutes?
Do the Kinsta chat question. Smallest one on the list.

### Got an hour and good focus?
Two emails and the staging PHP fix. That clears everything except the merge.

---

## Not yours

You are not blocking any of this. Nothing here needs a decision from you this week.

- **DU IT** — SSO setup and the mailbox. Chased twice. Escalate around **Aug 7** if still silent.
- **Persis** — theme, launch scope, and four smaller calls.
- **Amanda** — what she's built in staging.
- **Parked until after launch** — off-site backups, HSTS, old hostname removal, TLS check on Aug 24.

---

*Detail and history live in `docs/STATUS_AND_ACTIONS.md`. Go there only when you want the why — not to find out what to do.*

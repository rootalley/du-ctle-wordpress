# CTLE WordPress Platform — Technical Requirements

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Timeline & Milestones](#2-timeline--milestones)
3. [Hosting & Infrastructure](#3-hosting--infrastructure)
4. [User Roles & Access Control](#4-user-roles--access-control)
5. [Authentication & Single Sign-On](#5-authentication--single-sign-on)
6. [Canvas LMS Integration](#6-canvas-lms-integration)
7. [Courses & Enrollment](#7-courses--enrollment)
8. [Badges & Credentials](#8-badges--credentials)
9. [Events Calendar & Registration](#9-events-calendar--registration)
10. [Discussion Forums](#10-discussion-forums)
11. [Content Management & Workflow](#11-content-management--workflow)
12. [Search](#12-search)
13. [Notifications & Microsoft 365 Integration](#13-notifications--microsoft-365-integration)
14. [Privacy & Data Handling](#14-privacy--data-handling)
15. [Accessibility](#15-accessibility)
16. [Home Page Requirements](#16-home-page-requirements)
17. [Recommended Plugin Stack](#17-recommended-plugin-stack)
18. [Open Questions & Future Considerations](#18-open-questions--future-considerations)
19. [Changelog](#19-changelog)

---

## 1. Project Overview

The Center for Teaching & Learning Excellence (CTLE) at Dominican University (DU) maintains a resource website for faculty professional development. The site hosts self-paced training courses, an events calendar with registration, faculty discussion forums, and various resource collections. The CTLE intends to transition this website from the Canvas learning management system (LMS) to a standalone, publicly accessible WordPress site. This transition is motivated by the need for the CTLE to manage the site in its entirety without being dependent on Information Technology (IT), Learning Technologies, the Office of Marketing and Communications, or other stakeholders.

- **Audience:** The primary audience for the CTLE is DU faculty—full-time and adjunct. However, most content will be visible to the public to promote in faculty recruitment and support effective teaching across higher education.
- **Public vs. Protected Content:** CTLE resources will be public with selected exceptions. Discussion forums, professional development courses, certain event features (Zoom links and Add-to-Calendar functionality), and the authenticated user's self-view dashboard (see Faculty Profiles, below §5) require DU authentication.
- **URL:** Subdomain of university website (e.g., `ctle.dom.edu`).

---

## 2. Timeline & Milestones

| Milestone | Target Date |
|---|---|
| Requirements defined & hosting vendor selected | February 2026 |
| WordPress platform up and running | March 2026 |
| Content development & migration from Canvas | March–July 2026 |
| User acceptance testing & soft launch | July 2026 |
| Official launch | August 2026  |

---

## 3. Hosting & Infrastructure

| Requirement | Detail |
|---|---|
| **Hosting Type** | Managed WordPress hosting (third-party vendor) |
| **Budget** | ~$30/month tier (adequate for expected traffic) |
| **IT involvement** | IT will vet the vendor for security compliance, configure the `ctle.dom.edu` subdomain, set up Entra SSO, and configure Canvas LTI. Day-to-day tech support handled by the hosting vendor. |
| **SSL** | Required (HTTPS enforced) |
| **Backups** | Daily automated backups with point-in-time restore |
| **Staging environment** | Recommended — a staging/dev copy for testing updates before production |
| **PHP version** | 8.1+ (current WordPress recommendation) |
| **Database** | MySQL 8.0+ or MariaDB 10.6+ (current WordPress requirements)|

### Hosting Vendor Evaluation Criteria

- Managed WordPress specialization (auto-updates, caching, content delivery network)
- SOC 2 or equivalent security certification (required by DU IT)
- Uptime SLA ≥ 99.9%
- Breach notification SLA: contractual commitment to notify DU IT within 24 hours of any confirmed or suspected security incident affecting CTLE data (see §14)
- Built-in staging environment (preferred)
- SSH/SFTP access for the developer
- Support for custom plugins and PHP configurations (some budget hosts restrict this)

### Performance & Caching

| Requirement | Detail |
|---|---|
| **Page load target (uncached)** | Under 3 seconds for a full page load on a standard broadband connection. Applies to the home page, course catalog, event calendar, and other content-heavy pages. |
| **Page load target (cached)** | Under 2 seconds for subsequent page loads with browser caching and server-side page caching active. |
| **Server-side page caching** | Provided by the managed hosting vendor. Static and public pages are served from cache. Logged-in (authenticated) users bypass page cache so that personalized content (enrolled courses, dashboard, forum access) renders correctly. The developer must verify that the chosen LMS plugin's student dashboard, forum access controls, and event registration state all function correctly when page caching is active for anonymous visitors and bypassed for authenticated users. |
| **CDN** | Provided by the managed hosting vendor (static assets served from edge servers). |
| **Image optimization** | All uploaded images are automatically compressed and converted to modern formats (e.g., WebP) on upload. This is handled by an image optimization plugin (see §17) so that CTLE staff do not need to manually resize or compress images before uploading. |
| **Lazy loading of Panopto embeds** | Panopto video embeds on course content pages and past-event pages must be lazy-loaded — the embed iframe and player script should not load until the user scrolls the video into view. This prevents external Panopto resources from blocking initial page render. |
| **Front-end asset management** | The developer should audit CSS and JavaScript loading across all plugins to ensure that plugin assets are loaded only on pages where they are actually used. This is particularly important given the number of plugins in the stack (SSO, LMS, events, forums, badges, search, permissions, page builder, activity log). |

---

## 4. User Roles & Access Control

| Role | Capabilities |
|---|---|
| **CTLE Admin** | Full site administration: create/edit/publish all content, manage courses and enrollments, manage events, moderate forums, configure badges, manage faculty users, approve contributed content. |
| **Developer Admin** | WordPress admin access for SSO configuration, plugin management, security settings, hosting-level operations. Also includes content authoring responsibilities. |
| **Contributor** | A faculty member (or other subject-matter expert) invited by a CTLE Admin to author a specific page or course. Contributor status is granted manually per assignment, not via SSO or LTI — a CTLE Admin initializes the target page or course in WordPress, then assigns one or more contributors to it. Contributors can edit only the content they've been explicitly assigned to and cannot see other contributors' unpublished work unless co-assigned. All contributed content enters a pending review state and must be approved by a CTLE Admin before publication. |
| **Faculty (authenticated)** | View protected content, self-enroll in courses, complete assessments, participate in forums, RSVP/register for events, earn badges. |
| **Public (anonymous)** | View public pages, event listings (with restricted fields), blog posts, and resource descriptions. Cannot enroll, post, or register. |

---

## 5. Authentication & Single Sign-On

| Requirement | Detail |
|---|---|
| **Identity provider** | Microsoft Entra ID (Azure AD) via OIDC/SAML |
| **Scope** | DU IT will restrict the Entra app registration to faculty users, Learning Technologies team members, and CTLE staff only. |
| **Plugin** | A WordPress SSO plugin compatible with Entra ID (e.g., miniOrange SAML/OIDC, OAuth Single Sign On). |
| **Primary access method** | All regular operation — including CTLE Admin and Developer Admin work — is performed via Entra SSO or LTI launch from Canvas. Local WordPress login is not used in normal operation. |
| **User provisioning** | On first successful SSO or LTI login, a WordPress user account is auto-created with the Faculty role. |
| **Default role** | All users provisioned via Entra SSO or LTI launch receive the Faculty role by default. Elevation to Contributor, CTLE Admin, or Developer Admin is performed manually within WordPress and is not driven by Entra claims or Canvas context. |
| **Account linking** | The SSO and LTI plugins must be configured to link authenticating users to existing WordPress accounts by DU employee identifier (the stable, claim-provided ID from Entra and Canvas — see §6), not by email. This protects continuity of badges, completion records, and forum history across name or email changes. For the small number of local admin accounts created at initial setup (see Break-glass recovery account below), email-matching is used once to bind the local account to the user's first SSO login; thereafter the employee identifier becomes the primary key. |
| **Profile field sync on login** | On every successful SSO or LTI login, WordPress overwrites the user's profile fields — display name, email, and avatar URL (see Faculty Profiles, below) — from the incoming Entra claim or LTI launch payload. This ensures that name changes, email changes, and updated avatars propagate automatically without manual intervention. |
| **Role preservation on login** | SSO/LTI login must never modify a user's WordPress role. Roles are assigned on first login (default: Faculty) and thereafter are managed exclusively within WordPress by CTLE Admin or Developer Admin. Re-sync of profile fields on login must not cascade into role changes. |
| **Session management** | WordPress session lifetime is 24 hours. After session expiry, re-authentication via SSO or LTI is required. This short lifetime is the primary mechanism for revoking access to departed users: once DU IT disables a user's Entra account and Canvas enrollment, the user's next WP re-authentication will fail, typically within 24 hours of the external revocation. The brief window between external revocation and WP session expiry is accepted as a tolerable risk given the sensitivity of CTLE content. Single logout (SLO) preferred but not required. |
| **Break-glass recovery account** | One local WordPress administrator account is provisioned at initial setup and held by DU IT for emergency recovery if SSO is unavailable. This account is used once during initial setup to elevate the first CTLE Admin and Developer Admin users (who SSO in first to create their Faculty-default accounts, then are promoted via the recovery account). After initial setup, the account is dormant and used only for SSO recovery. |
| **Recovery account protection** | The recovery account is protected by: (1) strong password enforced via WordPress password policy; (2) TOTP two-factor authentication (e.g., Two Factor or WP 2FA plugin), with the TOTP seed stored alongside the password in IT's credential vault; (3) an obfuscated login URL (the default `wp-login.php` path is changed) as an additional layer against automated scanning; (4) real-time audit-log alerting (e.g., WP Activity Log) that emails all CTLE Admins immediately upon any successful or failed login to the recovery account. |
| **Recovery account rationale** | This account is a shared credential with full site administration privileges, used outside the normal SSO flow. It is treated differently from ordinary campus services (which do not require 2FA under DU IT policy) because a compromise of this single credential would grant total control of the site. TOTP plus audit alerting is the minimum defensible protection for a dormant break-glass admin credential. |

### IT Responsibilities

- Register the WordPress site as an application in Entra ID.
- Configure claims to pass: display name, email, and a DU employee identifier (e.g., `employeeId` or `netID`) suitable for matching WordPress completion records to Interfolio entries (see §7).
- Confirm that the Entra email claim matches the email addresses used when local CTLE Admin / Developer Admin accounts are created, to ensure clean email-based account linking on first SSO login.
- Restrict the app to faculty users, Learning Technologies team members, and CTLE staff via Entra group assignment.
- Hold the break-glass recovery credential (password + TOTP seed) in IT's credential vault. Rotate the password after any use of the account.
- Confirm IT security sign-off on the recovery-account protection model (TOTP + obfuscated URL + audit-log alerting) as the agreed substitute for broader 2FA or IP-allowlist requirements on this specific credential.

---

## Faculty Profiles

Each authenticated user has a WordPress profile consisting of (a) an account record storing identity and activity data, and (b) a self-view dashboard where the user can see their own course progress and earned badges. Profiles are not public — there is no browsable member directory, and no faculty member can view another's profile page.

### Profile Data Model

| Field | Source | Visibility | Editable by user |
|---|---|---|---|
| **Display name** | Entra claim / LTI launch payload, refreshed on every login | Shown publicly on forum posts, completion certificates, and badge displays | No |
| **Email** | Entra claim / LTI launch payload, refreshed on every login | Internal only (not displayed to other users) | No |
| **DU employee identifier** | Entra claim / LTI launch payload, set on first login as the account's primary key | Internal only; used for linking completion records to Interfolio (see §7) | No |
| **Avatar** | LTI launch claim from Canvas (URL to user's Canvas avatar); refreshed on every LTI login | Shown on forum posts and on the user's own dashboard | No |
| **Role** | Assigned in WordPress (default Faculty on first login); never modified by subsequent SSO/LTI login | N/A | No |
| **Enrolled courses & progress** | Generated by the LMS plugin from user activity | Self-view only | N/A |
| **Earned badges** | Generated by the badge plugin from course completions, event attendance, and forum participation | Self-view only; may be shared externally via Open Badges (see §8) | N/A |
| **Completion records** | Generated by the LMS plugin from course completions | Self-view (own records) + CTLE Admin view (all records); exportable as CSV per §7 | N/A |

### Self-View Dashboard

- Powered by the chosen LMS plugin's built-in student dashboard (LearnDash, LifterLMS, and Tutor LMS all ship with one). No separate profile plugin (BuddyPress, Ultimate Member, etc.) is required or planned.
- The dashboard displays the user's enrolled courses with progress indicators, completed courses with links to their PDF certificates, and earned badges.
- Badges are displayed in reverse chronological order (most recently earned first). Faculty cannot reorder, hide, or delete earned badges.
- Faculty do not need a bulk-download mechanism for their badges; the Open Badges standard provides external sharing (see §8).

### Avatar Handling

- When a user launches the site via LTI from Canvas, the LTI payload includes a URL to the user's Canvas avatar (128×128 pixels). Canvas provides a generic "no picture" icon by default for users who have not uploaded their own photo, so every LTI-launched user has an avatar URL available.
- WordPress stores or references the avatar URL from the LTI launch claim. Implementation choice (direct reference vs. local copy on the WP media library) is left to the developer based on reliability and performance considerations.
- Users who authenticate only via Entra SSO (without ever launching from Canvas) — principally CTLE Admin and Developer Admin users — will not have a Canvas-provided avatar, since Entra OIDC claims do not include a profile photo by default. For these users, WordPress displays a locally-hosted copy of the Canvas "no picture provided" icon as a default placeholder. Learning Technologies will provide this icon image to the developer at initial setup.

### Forum Display

- When a faculty member posts in a discussion forum (§10), their forum author name is their profile display name (synced from Entra/Canvas on login). Their avatar is shown alongside their posts.

### Profile Persistence on Departure

- When a faculty member leaves DU, DU IT disables their Entra account and Canvas enrollment externally. Their WordPress account is not deleted — it remains in place to preserve the integrity of completion records, forum history, and any audit trail CTLE may need for Interfolio lookback or institutional records.
- The departed user's next re-authentication attempt (after their 24-hour WP session expires) will fail because SSO/LTI external access is revoked, effectively preventing further login without any WordPress-side action.
- CTLE Admins may optionally terminate a departed user's active WordPress session manually if immediate revocation is required before natural session expiry.

---

## 6. Canvas LMS Integration

### Learning Tools Interoperability (LTI) 1.3 Launch (Preferred)

- A WordPress LTI plugin (e.g., LTI Platform for WordPress) will allow Canvas users having a faculty role to launch the CTLE site as an external tool.
- **Integration level:** LTI 1.3 launch only (SSO passthrough). The CTLE site recognizes the Canvas user context but does **not** pass grades or completions back to Canvas.
- This provides a seamless SSO bridge for faculty navigating from Canvas.

### Navigation Link (Fallback Option)

- DU IT has an existing custom JavaScript injection that adds a **CTLE button** to the Canvas global navigation menu (visible to faculty only).
- This button currently points to the Canvas-based CTLE site and will be updated to point to the new WordPress URL (`ctle.dom.edu`).
- No plugin or LTI required for this — it is a simple URL redirect.

### Learning Technologies Responsibilities

- Register the WordPress site as an LTI 1.3 tool in Canvas.
- Provide the platform's OIDC and JWKS endpoints to the WordPress developer.
- Confirm that the LTI tool configuration in Canvas includes the email claim, a DU employee identifier claim (e.g., `lis_person_sourcedid` or a custom user attribute), and the user avatar URL in the launch payload. The employee identifier is used as the primary account key (see §5), and the avatar URL is stored for display on the user's profile and forum posts (see Faculty Profiles under §5).
- Provide the developer with a copy of the Canvas "no picture provided" default avatar icon, to be hosted locally in WordPress as a placeholder for users who authenticate only via Entra SSO (see Faculty Profiles under §5).
- Update the existing Canvas global-nav JavaScript to point to the new URL.

---

## 7. Courses & Enrollment

### Course Structure

| Element | Description |
|---|---|
| **Course** | A self-paced training unit with a title, description, and thumbnail. |
| **Modules** | Courses are divided into ordered modules (sections/chapters). |
| **Content pages** | Each module contains one or more content pages with rich text, images, and embedded Panopto video. |
| **Assessments** | Modules may include quizzes or knowledge checks (multiple choice, true/false, short answer). |
| **Completion tracking** | Per-module and per-course. A course is complete when all modules are marked complete (content viewed + assessments passed). |

### Enrollment

| Feature | Description |
|---|---|
| **Self-enrollment** | Authenticated faculty can browse a course catalog and self-enroll in courses of interest. |
| **Admin enrollment** | CTLE Admin can bulk-enroll faculty into mandatory training courses. |
| **Enrollment status** | Tracked per user: Not Started, In Progress, Completed. |
| **Completion records** | Records of completed faculty training are durably stored in WordPress with user identity (including DU employee/netID), course title, completion date, and a unique record ID. A PDF certificate of completion is generated for each completion. Records are retained indefinitely and can be exported by CTLE Admin via CSV for eventual inclusion in Interfolio, DU's faculty credentials system. The specific Interfolio ingestion mechanism is a future decision (see §18). |
| **Course catalog** | Public-facing catalog page listing available courses with descriptions. Enrollment requires sign-in. |
| **My Courses** | A dashboard for authenticated faculty showing their enrolled courses and progress. |

### Recommended Plugin Approach

A WordPress LMS plugin such as LearnDash, LifterLMS, or Tutor LMS can provide:
- Course/module/lesson hierarchy
- Quiz engine with grading
- Enrollment management (self-enroll + admin-enroll)
- Progress tracking and completion certificates
- Integration with badge plugins

The chosen LMS plugin must support:
- Per-user completion records with timestamps and a unique record identifier
- PDF certificate generation on completion (including faculty name, course title, completion date, DU/CTLE branding, and the record ID)
- Multiple completion records for the same user/course pair, to support recurring or refresher training (each re-completion creates a new dated record rather than overwriting)
- CTLE Admin access to completion reports exportable as CSV

These capabilities are standard in the major LMS plugins but should be verified during plugin evaluation — some reporting features may be restricted to paid tiers.

> The developer should evaluate these plugins for compatibility with Entra SSO user accounts and badge/forum requirements.

---

## 8. Badges & Credentials

| Requirement | Detail |
|---|---|
| **Standard** | Open Badges 2.0 (or later). |
| **Cost** | Must be a free or low-cost solution. |
| **Shareability** | Faculty must be able to share badges externally (LinkedIn, email, personal sites). |
| **On-site display** | Badges earned by faculty are automatically displayed on their CTLE profiles. |
| **Triggers** | Badges awarded for: course completion, event attendance milestones, forum participation thresholds, and other achievements to be defined by the CTLE Admin. |

### Recommended Plugin Approach

- BadgeOS (free, Open Badges compatible) or equivalent.
- Integration with the chosen LMS plugin for automatic course-completion triggers.
- Integration with the forum plugin for participation-based triggers.

---

## 9. Events Calendar & Registration

### Event Data Model

| Field | Type | Notes |
|---|---|---|
| **Title** | Text | Required |
| **Abstract / Description** | Rich text | Required |
| **Presenter(s)** | Repeater field | Each entry: Name, Title/Role. Multiple presenters per event. |
| **Date** | Date | Required |
| **Time** | Time (start – end) | Required |
| **Location** | Text | Physical location for in-person events |
| **Series** | Taxonomy (select) | Admin-defined list (e.g., Conversation Series, Faculty Seminar Series). CTLE creates/manages series names. |
| **Parent Event** | Reference | For nested events (sessions under a workshop). See §9.4. |
| **Zoom Link** | URL | For webinars/virtual events |
| **Zoom Visibility** | Toggle | Public (anyone can see) vs. Protected (DU sign-in required) |
| **Panopto Recording Link** | URL | Added post-event when recording is available |
| **Capacity** | Number | Max registrations allowed (0 = unlimited) |
| **Pinned** | Boolean | Pin event to the home page (see §16) |

### Registration & RSVP

| Feature | Description |
|---|---|
| **Registration** | Authenticated (DU) users can register for events via an "Add to Calendar" button. |
| **Outlook integration** | Registration generates an `.ics` calendar invite or uses Microsoft Graph API to add the event directly to the faculty member's Outlook calendar. |
| **Capacity limits** | When capacity is reached, new registrants are added to a waitlist. |
| **Waitlist** | Waitlisted users are automatically promoted (and notified) when a spot opens. |
| **Attendance tracking** | CTLE Admin can mark who actually attended (post-event). Feeds into badge triggers. |

### Event Display Logic

| State | Media Area | Link/Info Area |
|---|---|---|
| **Upcoming event** | Placeholder or event image | Zoom link (respecting visibility toggle) and/or physical location |
| **Past event (recording available)** | Panopto embedded thumbnail player | Link to full Panopto recording |
| **Past event (no recording)** | Event image or placeholder | "Recording not available" |

### Nested / Parent-Child Events

- A parent event (e.g., "Fall Faculty Workshop Day") can contain multiple child sessions (e.g., "Session 1: Active Learning — 9:00 AM, Room 101").
- Each child session is a full event record (with its own time, location, presenters, Zoom link, recording, etc.).
- The parent event page displays an agenda/schedule of all child sessions.
- A workshop or multi-session event may be treated as its own "series" for organizational purposes.

### Event Series

- Series are a custom taxonomy managed by CTLE Admin (create, rename, archive).
- Examples: *Conversation Series*, *Faculty Seminar Series*, *New Faculty Orientation*, *Workshop Day 2026*.
- Events are filtered/browsable by series on the front end.
- Each series may have its own landing page showing all past and upcoming events in that series.

---

## 10. Discussion Forums

### Structure

| Forum Type | Description |
|---|---|
| **Category-based forums** | General discussion areas organized by topic (e.g., "Teaching with AI," "Assessment Strategies," "New Faculty"). CTLE Admin creates and manages categories. |
| **Course-specific forums** | Each course has an associated discussion board for enrolled faculty to discuss course content. |

### Access & Moderation

- All forums are restricted to authenticated (DU) faculty.
- Forum content is not visible to the public.
- CTLE Admin has full moderation capabilities (edit, delete, pin, lock threads).
- Faculty can post new topics and reply to existing threads.
- Forum posts display the author's profile display name and avatar as synced from Entra/LTI (see Faculty Profiles under §5). There is no forum-specific pseudonym or display name — forum identity is always the faculty member's institutional identity.
- Forum participation is tracked for badge awards.

### Recommended Plugin Approach

- bbPress (free, tightly integrated with WordPress) or BuddyPress (if broader community features are desired).
- Must integrate with the badge plugin for participation tracking.
- Must respect the SSO-provisioned user accounts.

---

## 11. Content Management & Workflow

### Content Types

| Content Type | Description |
|---|---|
| **Pages** | Static informational pages (About CTLE, Contact, Resources, etc.) |
| **Blog / News posts** | CTLE announcements, teaching tips, faculty spotlights |
| **Courses** | Managed via LMS plugin (see §7) |
| **Events** | Custom post type (see §9) |
| **Forum topics** | Managed via forum plugin (see §10) |
| **Resources** | Downloadable guides, templates, toolkits (could be a custom post type or media library category) |

### Editorial Workflow

| Author | Workflow |
|---|---|
| **CTLE Admin** | Create → Publish (immediate) |
| **Contributor** | Assigned by CTLE Admin to a specific page or course → Create/Edit within assignment → Submit for Review → CTLE Admin Approves/Rejects → Publish. Contributors cannot create new top-level content on their own initiative. |

- WordPress's built-in `pending review` status can handle the review step.
- Per-assignment authoring scope (ensuring contributors see only their own assigned content) is not native to WordPress and will require a permissions plugin (e.g., PublishPress Permissions) for pages, and/or the LMS plugin's built-in instructor-scoping for courses. Plugin selection in §7 and §17 should account for this requirement.
- The developer should configure a notification when content is submitted for review.

---

## 12. Search

Site-wide search is a first-class feature. Faculty need to reliably find courses, events, blog posts, resources, and (when authenticated) forum discussions from a single search interface.

### Requirements

| Requirement | Detail |
|---|---|
| **Plugin** | Relevanssi (free version) replaces WordPress's default search with a relevance-ranked index. CTLE may upgrade to Relevanssi Premium in the future if usage patterns warrant (see Upgrade triggers below). |
| **Indexed content** | Blog posts, static pages, events (title, abstract, presenters, series, location), course catalog entries (titles and descriptions only — see below), downloadable resources (titles, descriptions, and filenames), and forum topics/replies (with access filtering — see below). |
| **Course content scope** | Only course titles and descriptions from the catalog are indexed at launch. The interior content of courses (module text, lesson content) is not indexed, to avoid entangling search with enrollment and protected-content visibility. This may be revisited in a future version. |
| **PDF content** | At launch, only PDF filenames and media-library titles/descriptions are indexed. Indexing the full text inside PDF files is a Relevanssi Premium feature and is not required for v1. |
| **Stemming** | English stemming enabled (Relevanssi free handles this). Searches for `assess` should match `assessing`, `assessment`, `assessments`, etc. |
| **Relevance ranking** | Results ordered by relevance, not date. Title matches should weight more heavily than body matches. |

### Access-Aware Results

Search results must respect content visibility rules so that no user sees a result they cannot access:

| Viewer | Sees in results |
|---|---|
| **Anonymous visitor** | Public blog posts, public pages, public event listings (with restricted fields per §9), course catalog entries, public resource descriptions. Not forum threads, protected event fields, or protected course interiors. |
| **Authenticated faculty** | All of the above, plus forum topics and replies from forums they have access to. |

Relevanssi supports per-user access filtering via standard WordPress capability checks; the developer should verify this works correctly with the chosen forum plugin (§10) and LMS plugin (§7).

### Search UI

- A search box is present in the site header on every page, visible to both anonymous visitors and authenticated users.
- Submitting a search takes the user to a dedicated results page showing matched items with title, short excerpt, content type label, and a link to the item.
- The results page includes content-type filters allowing the user to narrow results by category: Courses, Events, Blog/News, Resources, Pages, and (for authenticated users) Forums.
- Search is submit-and-show-results, not live-as-you-type. Live search is out of scope for v1.
- When a search returns zero results, the page displays a helpful "no results" message with links to browse the course catalog, event calendar, and resource library, and a contact link for CTLE.

### Search Analytics

- Relevanssi free logs user queries, including zero-result queries, to the WordPress database. The developer should enable this logging at launch.
- Actively reviewing search analytics is not a priority for v1 and no dashboard or reporting surface needs to be built. The logged data will simply accumulate and be available if CTLE later wants to review it.

### Upgrade Triggers to Relevanssi Premium

CTLE should consider upgrading to the paid version if any of the following occur post-launch:

1. Search analytics (or direct faculty feedback) reveal a pattern of zero-result queries that correspond to content that actually exists on the site — particularly content inside PDFs.
2. The CTLE resource library grows to include substantial PDF content where filename-only indexing is insufficient.
3. CTLE adds non-English content (see open question on multilingual support).
4. CTLE wants formal search reporting, "did you mean" suggestions, or search redirects.

The upgrade decision is deferrable and does not affect launch scope.

---

## 13. Notifications & Microsoft 365 Integration

### Email Notifications

| Trigger | Recipient | Content |
|---|---|---|
| Event registration confirmation | Registrant | Event details + .ics attachment |
| Event reminder (24 hrs before) | All registrants | Reminder with Zoom link / location |
| Waitlist promotion | Promoted registrant | "A spot has opened" + event details |
| Forum reply | Thread subscribers | Excerpt of reply + link to thread |
| Course enrollment confirmation | Enrolled faculty | Course link + getting-started info |
| Course completion | Completer | Congratulations + badge notification |
| Content submitted for review | CTLE Admin | Link to pending content |

### Microsoft 365 Integration

| Integration | Description |
|---|---|
| **Outlook Calendar** | Event registration adds an `.ics` invite or uses Microsoft Graph API to create a calendar event in the faculty member's Outlook. |

### IT Responsibilities

- Provision a shared application mailbox in Microsoft 365 (e.g., `ctle@dom.edu`) for WordPress to use as the sender for all system-generated email (registration confirmations, event reminders, forum notifications, etc.). Configure the mailbox with appropriate SPF/DKIM/DMARC alignment for `dom.edu`.
- Confirm that automated email volume from this mailbox (estimated 50–200 messages per day at peak, with occasional bursts for event reminders or bulk enrollment notifications) is acceptable under DU's Exchange Online sending limits and acceptable-use policies.
- Configure WP Mail SMTP (or equivalent plugin) to authenticate to the mailbox via Microsoft 365 SMTP relay or Microsoft Graph API for mail sending.
- Microsoft Graph API integration for Outlook calendar will require an Entra app registration with `Calendars.ReadWrite` delegated permissions.

---

## 14. Privacy & Data Handling

The CTLE site collects personal data about DU faculty: identity (name, email, employee ID, avatar), training completion records, course enrollment and progress, forum activity, event registrations, earned badges, and search query history. This section establishes the privacy obligations that apply to that data.

### Applicable Regimes

| Regime | Applicability |
|---|---|
| **FERPA** | Not applicable. CTLE training records are employee records, not student education records. Students cannot create accounts on or access protected content in the CTLE site. If this scope ever expands to include students (e.g., graduate TAs, peer mentors), FERPA applicability must be re-evaluated. |
| **Illinois PIPA** | Applicable to breach notification and reasonable security obligations for personal information. |
| **DU OPC (Office of People and Culture) policy** | Applicable to employee records. CTLE will consult OPC on retention, disclosure, correction, and departure-handling policies (see §18). |
| **GDPR** | Not a primary design driver. The site primarily serves U.S. faculty. If an EU data subject (e.g., a visiting scholar) asserts GDPR rights, CTLE Admin must be able to fulfill access, correction, and erasure requests via WordPress's built-in privacy tools. |

### Privacy Policy

| Requirement | Detail |
|---|---|
| **Published privacy policy page** | The CTLE site must have a publicly accessible privacy policy page (linked from the site footer on every page) before launch. |
| **Required content** | The policy must name: (1) what personal data is collected, (2) how it is used, (3) all third-party recipients of that data (hosting provider, Panopto, Interfolio, Microsoft Graph if used, and any analytics service if used), (4) retention periods, (5) the process by which faculty can request access to, correction of, or deletion of their data, and (6) a contact point for privacy concerns. |
| **Authoring** | CTLE drafts the policy, using the WordPress built-in privacy policy template as a starting point. OPC and/or DU legal review the draft before publication. |

### Faculty Data Rights

| Capability | Detail |
|---|---|
| **Self-view** | Faculty can view their own profile, completion records, enrolled courses, and earned badges via the self-view dashboard (see Faculty Profiles under §5). |
| **Data export** | CTLE Admin can export a specific faculty member's personal data on request, using WordPress's built-in Export Personal Data tool. |
| **Data correction** | CTLE Admin can correct inaccurate records on request. The exact process is to be defined in consultation with OPC (see §18). |
| **Data erasure** | CTLE Admin can redact or erase a faculty member's personal data on request, using WordPress's built-in Erase Personal Data tool. Completion records retained for institutional purposes may be anonymized rather than deleted; the exact boundary is to be defined in consultation with OPC (see §18). |

### Forum Privacy Disclosure

Because forum posts are linked to institutional identity (see §10) and visible to all authenticated DU faculty, users must be clearly informed of the visibility and moderation rules before and during forum use.

| Disclosure mechanism | When it appears |
|---|---|
| **First-visit acknowledgment** | On a user's first visit to any forum page, a modal or inline acknowledgment displays the forum privacy notice and requires the user to click to confirm before proceeding. The acknowledgment reappears whenever the forum privacy policy is materially updated. |
| **Persistent footer link** | Every forum category, topic, and thread page displays a footer link to the full forum privacy policy. |
| **Posting-time reminder** | When a user is composing a post or reply, a short note near the submit button reminds them that forum content is visible to all DU faculty and is moderated by CTLE, with a link to the full policy. |

The exact language of these disclosures is to be drafted by CTLE in consultation with OPC (see §18).

### Moderation Audit Trail

- All CTLE Admin actions affecting forum content (edits, deletions, pinning, locking) are logged using WP Activity Log (see §17).
- The audit trail records the admin identity, timestamp, action, and affected post.
- This protects faculty (transparency about what happens to their posts) and CTLE (defensibility of moderation decisions).

### Third-Party Data Flows

The following third parties will receive CTLE faculty data in the course of normal operation. Each requires a data processing agreement or equivalent arrangement, handled by DU IT at vendor selection:

| Vendor / Service | Data received |
|---|---|
| **Managed WordPress hosting provider** | Full database and file access (all site data) |
| **Microsoft 365 (email)** | Recipient addresses and message content for all system-generated email, sent via a DU shared application mailbox |
| **Panopto** | Video viewing activity, potentially including identified-user analytics (see §18) |
| **Interfolio** | Training completion records (mechanism TBD per §18) |
| **Microsoft Graph API** (if used for Outlook calendar integration) | Event data and calendar writes |
| **Analytics service** (if used — currently TBD per §18) | Page view and session data |

### Breach Notification

- The managed hosting vendor contract must require the vendor to notify DU IT within 24 hours of any confirmed or suspected security incident affecting CTLE data.
- This requirement is added to the Hosting Vendor Evaluation Criteria in §3.
- In the event of a breach affecting personal information, DU follows its standard notification procedures under Illinois PIPA and any applicable OPC policies.

---

## 15. Accessibility

| Requirement | Standard |
|---|---|
| **Compliance level** | WCAG 2.1 Level AA or greater |
| **Theme** | The WordPress theme must be accessibility-ready (tested against WCAG criteria). |
| **Plugins** | All plugins (LMS, forums, events) must produce accessible front-end markup. |
| **Media** | Panopto videos should include captions. The site should support alt text for all images. |
| **Testing** | Accessibility audit recommended before launch (automated + manual testing). |
| **DU branding** | Follow Dominican University's brand guidelines for colors, typography, and logo usage. CTLE and the developer should coordinate with DU Marketing/Communications. |

---

## 16. Home Page Requirements

| Element | Description |
|---|---|
| **Upcoming events** | Display the next several upcoming events (configurable count, e.g., 3–5) with title, date, series, and a registration CTA. |
| **Pinned event** | CTLE Admin can "pin" a specific event to a prominent home-page position — either to drive registrations for an upcoming event or to highlight the recording of a signature past event. |
| **Course highlights** | Optional: featured or new courses. |
| **Announcements / News** | Latest blog post or CTLE announcement. |
| **Quick links** | Links to the course catalog, event calendar, forums, and resources. |

---

## 17. Recommended Plugin Stack

The following is a starting-point recommendation for the developer to evaluate. Final selections should consider compatibility, licensing costs, and long-term maintenance.

| Function | Recommended Plugin(s) | Notes |
|---|---|---|
| **SSO / Authentication** | miniOrange SAML SSO, or OpenID Connect Generic | Must support Entra ID; must support account linking by employee identifier (not email) and preserve existing roles on re-login (see §5) |
| **Two-factor authentication** | Two Factor, or WP 2FA (Melapress) | Required on the break-glass recovery account (TOTP). See §5. |
| **Audit logging & alerting** | WP Activity Log | Real-time email alerts to CTLE Admins on any login to the recovery account. See §5. |
| **Login URL obfuscation** | WPS Hide Login (or equivalent security plugin feature) | Changes the default `wp-login.php` path. See §5. |
| **LMS / Courses** | LearnDash, LifterLMS, or Tutor LMS | Evaluate for enrollment mgmt, quiz engine, completion tracking |
| **Events** | The Events Calendar (Pro) + Event Tickets | Custom post type with series taxonomy, registration, capacity |
| **Forums** | bbPress | Free, lightweight, WP-native |
| **Badges** | BadgeOS + Open Badges integration | Free tier; auto-award from course/forum triggers |
| **LTI** | LTI Platform for WordPress | LTI 1.3 launch from Canvas |
| **Email** | WP Mail SMTP | Configured to send via DU's Microsoft 365 shared application mailbox (see §13). Reliable delivery via institutional email infrastructure. |
| **Page builder** | Elementor, Beaver Builder, or Gutenberg blocks | Developer preference; must be accessible |
| **Access control / per-post permissions** | PublishPress Permissions (or equivalent) | Needed to scope Contributor authoring to specific assigned pages/courses (see §4, §11) |
| **Search** | Relevanssi (free) | Replaces WP core search with relevance-ranked indexing of custom post types and custom fields; access-aware results. See §12. Upgrade to Relevanssi Premium deferrable post-launch. |
| **Forum privacy disclosure** | Custom development or a terms/consent plugin (e.g., Complianz, CookieYes) | Implements first-visit acknowledgment modal (with re-display on policy change), posting-time reminder, and persistent footer link per §14. Footer link and posting-time note are minor template customizations; the first-visit acknowledgment with tracked consent and re-display logic is more involved. Developer to evaluate build-vs-buy. |
| **Image optimization** | ShortPixel, Smush, or Imagify | Automatically compresses and converts uploaded images (e.g., to WebP). Free tiers are adequate for CTLE's volume. See §3. |
| **Calendar integration** | ICS export or Microsoft Graph API | For Outlook calendar adds |

---

## 18. Open Questions & Future Considerations

| # | Question / Consideration | Status |
|---|---|---|
| 1 | **Badge plugin selection:** BadgeOS vs. alternatives — developer to evaluate compatibility with chosen LMS plugin. | Open |
| 2 | **Microsoft Graph API scope:** IT to confirm willingness to grant `Calendars.ReadWrite` for direct Outlook calendar integration vs. simpler .ics download approach. | Needs IT input |
| 3 | **Content migration:** Inventory of existing Canvas course materials to be ported. Determine formats (HTML, PDF, video links) and migration effort. | Future work |
| 4 | **Analytics:** Does CTLE need reporting dashboards (course completion rates, event attendance trends, popular content)? If so, built-in LMS reporting + Google Analytics or a WP analytics plugin. | To be determined |
| 5 | **Multilingual support:** Any need for content in languages other than English? If yes, this would also be a trigger for upgrading to Relevanssi Premium for multilingual stemming (see §12). | To be determined |
| 6 | **Data retention:** Completion records will live in WordPress indefinitely by default (see §7). Align with DU OPC policy on retention of employee training records, including what happens to records after a faculty member separates from DU. Rolled into the broader OPC consultation (#11). | Needs OPC input |
| 7 | **Custom theme vs. commercial theme:** Developer to recommend based on DU brand requirements and budget. | Open |
| 8 | **Disaster recovery:** Confirm RPO/RTO requirements with IT. | Needs IT input |
| 9 | **Interfolio ingestion mechanism:** Completion records will exist in WordPress from day one as exportable CSV (see §7). The mechanism for getting those records *into* Interfolio — manual CSV upload, scheduled export, or direct API integration if Interfolio provides one — is a separate decision and does not block launch. **Associate Provost** to confirm how Interfolio ingests records. | Needs Associate Provost input |
| 10 | **Course-contributor mechanism:** Determine whether the chosen LMS plugin's native instructor/author model is sufficient for scoping course contributors, or whether a separate permissions plugin is needed for courses as well as pages. | Open |
| 11 | **OPC (Office of People and Culture) consultation:** CTLE to consult OPC on all HR-adjacent aspects of the project, including: (a) retention policy for completion records; (b) handling of forum posts and other authored content when a faculty member separates from DU; (c) process and authority for correcting inaccurate records; (d) process and authority for handling faculty data access, export, and erasure requests; (e) any other employee-records requirements OPC wishes to impose. Findings feed back into §7, §14, and any other affected sections. | Needs OPC input |
| 12 | **Privacy policy authoring:** CTLE to draft the site's privacy policy page (using WordPress's built-in template as a starting point) before launch. The policy must identify (a) what data is collected, (b) how it is used, (c) all third-party recipients of data, (d) retention periods, (e) the process for faculty data access and correction, and (f) a privacy contact. OPC and/or DU legal review required before publication. See §14. | CTLE task, pre-launch |
| 13 | **Forum privacy disclosure language:** Draft the exact language for the three forum privacy disclosures (first-visit acknowledgment, persistent footer link text, posting-time reminder — see §14). To be drafted by CTLE in consultation with OPC. | Needs CTLE / OPC input |
| 14 | **Panopto tracking review:** Learning Technologies to review whether DU's Panopto instance is configured to collect identified-user viewing analytics, and confirm how that data flow should be disclosed in the site's privacy policy (see §14). | Needs Learning Technologies input |
| 15 | **Microsoft 365 email for WordPress:** IT to confirm: (a) willingness to provision a shared application mailbox (e.g., `ctle@dom.edu`) for automated WordPress email; (b) that estimated volume (50–200 messages/day, occasional bursts) is acceptable under Exchange Online sending limits; (c) preferred SMTP relay or Graph API configuration for WP Mail SMTP. See §13. | Needs IT input |

---

## 19. Changelog

| Version | Date       | Author  | Changes |
|---------|------------|---------|---------|
| 0.1.0   | 2026-02-11 | sendres | Initial version for CTLE and IT review |
| 0.1.1   | 2026-03-09 | sendres | Minor revisions after pdriver and kodell review |
| 0.1.2   | 2026-03-09 | sendres | Add Interfolio export requirement for completed faculty training records |
| 0.1.3   | 2026-03-10 | sendres | Fix document repository link |
| 0.1.4   | 2026-04-13 | sendres | Clarify Contributor role |
| 0.1.5   | 2026-04-13 | sendres | Revise authentication process |
| 0.1.6   | 2026-04-13 | sendres | Update Interfolio requirements; add DU ID to Entra and LTI login requirements |
| 0.1.7   | 2026-04-13 | sendres | Clarify requirements for faculty profiles |
| 0.1.8   | 2026-04-13 | sendres | Remove numbering and ToC entry from Faculty Profiles subsection |
| 0.1.9   | 2026-04-13 | sendres | Add search requirements |
| 0.1.10  | 2026-04-16 | sendres | Add Privacy & Data Handling section |
| 0.1.11  | 2026-04-16 | sendres | Minor copy edits and formatting |
| 0.1.12  | 2026-04-16 | sendres | Audit plugin stack; clarify recovery account password strength is manually enforced |
| 0.1.13  | 2026-04-16 | sendres | Clarify transactional email and IT support requirements |
| 0.1.14  | 2026-04-16 | sendres | Add Performance & Caching subsection |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

---

**End of Specification**
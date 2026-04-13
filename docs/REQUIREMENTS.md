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
12. [Notifications & Microsoft 365 Integration](#12-notifications--microsoft-365-integration)
13. [Accessibility](#13-accessibility)
14. [Home Page Requirements](#14-home-page-requirements)
15. [Recommended Plugin Stack](#15-recommended-plugin-stack)
16. [Open Questions & Future Considerations](#16-open-questions--future-considerations)
17. [Changelog](#17-changelog)

---

## 1. Project Overview

The Center for Teaching & Learning Excellence (CTLE) at Dominican University (DU) maintains a resource website for faculty professional development. The site hosts self-paced training courses, an events calendar with registration, faculty discussion forums, and various resource collections. The CTLE intends to transition this website from the Canvas learning management system (LMS) to a standalone, publicly accessible WordPress site. This transition is motivated by the need for the CTLE to manage the site in its entirety without being dependent on Information Technology (IT), Learning Technologies, the Office of Marketing and Communications, or other stakeholders.

- **Audience:** The primary audience for the CTLE is DU faculty—full-time and adjunct. However, most content will be visible to the public to promote in faculty recruitment and support effective teaching across higher education.
- **Public vs. Protected Content:** CTLE resources will be public with selected exceptions. Discussion forums for DU faculty, professional development courses, and certain event features (Zoom links and Add-to-Calendar functionality), and access to user profiles require DU authentication.
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
- Built-in staging environment (preferred)
- SSH/SFTP access for the developer
- Support for custom plugins and PHP configurations (some budget hosts restrict this)

---

## 4. User Roles & Access Control

| Role | Capabilities |
|---|---|
| **CTLE Admin** | Full site administration: create/edit/publish all content, manage courses and enrollments, manage events, moderate forums, configure badges, manage faculty users, approve contributed content. |
| **Developer Admin** | WordPress admin access for SSO configuration, plugin management, security settings, hosting-level operations. Also includes content authoring responsibilities. |
| **Contributor** | A faculty member (or other subject-matter expert) invited by a CTLE Admin to author a specific page or course. Contributor status is **granted manually per assignment**, not via SSO or LTI. A CTLE Admin initializes the target page or course in WordPress, then assigns one or more contributors to it. Contributors can edit only the content they've been explicitly assigned to and cannot see other contributors' unpublished work unless co-assigned. All contributed content enters a **pending review** state and must be approved by a CTLE Admin before publication. |
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
| **User provisioning** | On first successful SSO or LTI login, a WordPress user account is auto-created with the **Faculty** role. |
| **Default role** | All users provisioned via Entra SSO or LTI launch receive the **Faculty** role by default. Elevation to Contributor, CTLE Admin, or Developer Admin is performed manually within WordPress and is not driven by Entra claims or Canvas context. |
| **Account linking** | The SSO and LTI plugins must be configured to **link by email** to existing WordPress accounts rather than create duplicates. SSO/LTI login must **never downgrade** a manually-assigned role — on subsequent logins, existing role assignments are preserved. |
| **Session management** | WP session should respect Entra token lifetime. Single logout (SLO) preferred. |
| **Break-glass recovery account** | **One** local WordPress administrator account is provisioned at initial setup and held by DU IT for emergency recovery if SSO is unavailable. This account is used once during initial setup to elevate the first CTLE Admin and Developer Admin users (who SSO in first to create their Faculty-default accounts, then are promoted via the recovery account). After initial setup, the account is dormant and used only for SSO recovery. |
| **Recovery account protection** | The recovery account is protected by: (1) strong password enforced via WordPress password policy; (2) **TOTP two-factor authentication** (e.g., Two Factor or WP 2FA plugin), with the TOTP seed stored alongside the password in IT's credential vault; (3) an **obfuscated login URL** (the default `wp-login.php` path is changed) as an additional layer against automated scanning; (4) **real-time audit-log alerting** (e.g., WP Activity Log) that emails all CTLE Admins immediately upon any successful or failed login to the recovery account. |
| **Recovery account rationale** | This account is a shared credential with full site administration privileges, used outside the normal SSO flow. It is treated differently from ordinary campus services (which do not require 2FA under DU IT policy) because a compromise of this single credential would grant total control of the site. TOTP plus audit alerting is the minimum defensible protection for a dormant break-glass admin credential. |

### IT Responsibilities

- Register the WordPress site as an application in Entra ID.
- Configure claims to pass: display name, email.
- Confirm that the Entra email claim matches the email addresses used when local CTLE Admin / Developer Admin accounts are created, to ensure clean email-based account linking on first SSO login.
- Restrict the app to faculty users, Learning Technologies team members, and CTLE staff via Entra group assignment.
- Hold the break-glass recovery credential (password + TOTP seed) in IT's credential vault. Rotate the password after any use of the account.
- Confirm IT security sign-off on the recovery-account protection model (TOTP + obfuscated URL + audit-log alerting) as the agreed substitute for broader 2FA or IP-allowlist requirements on this specific credential.

---

## 6. Canvas LMS Integration

### Learning Tools Interoperability (LTI) 1.3 Launch (Preferred)

- A WordPress LTI plugin (e.g., LTI Platform for WordPress) will allow Canvas users having a faculty role to launch the CTLE site as an external tool.
- **Integration level:** LTI 1.3 launch only (SSO passthrough). The CTLE site recognizes the Canvas user context but does **not** pass grades or completions back to Canvas.
- This provides a seamless SSO bridge for faculty navigating from Canvas.

### Navigation Link (Fallback Option)

- DU IT has an existing custom JavaScript injection that adds a **CTLE button** to the Canvas global navigation menu (visible to faculty only).
- This button currently points to the Canvas-based CTLE site and will be updated to point to the new WordPress URL (`ctle.dom.edu`).
- **No plugin or LTI required** for this — it is a simple URL redirect.

### Learning Technologies Responsibilities

- Register the WordPress site as an LTI 1.3 tool in Canvas.
- Provide the platform's OIDC and JWKS endpoints to the WordPress developer.
- Confirm that the LTI tool configuration in Canvas includes the **email claim** in the launch payload, so that the WordPress LTI plugin can perform email-based account linking (see §5).
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
| **Interfolio export** | Records of completed faculty training are exported to Interfolio for retention. |
| **Course catalog** | Public-facing catalog page listing available courses with descriptions. Enrollment requires sign-in. |
| **My Courses** | A dashboard for authenticated faculty showing their enrolled courses and progress. |

### Recommended Plugin Approach

A WordPress LMS plugin such as **LearnDash**, **LifterLMS**, or **Tutor LMS** can provide:
- Course/module/lesson hierarchy
- Quiz engine with grading
- Enrollment management (self-enroll + admin-enroll)
- Progress tracking and completion certificates
- Integration with badge plugins

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

- **BadgeOS** (free, Open Badges compatible) or equivalent.
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
| **Pinned** | Boolean | Pin event to the home page (see §14) |

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

- A **parent event** (e.g., "Fall Faculty Workshop Day") can contain multiple **child sessions** (e.g., "Session 1: Active Learning — 9:00 AM, Room 101").
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

- All forums are **restricted to authenticated (DU) faculty**.
- Forum content is **not visible to the public**.
- CTLE Admin has full moderation capabilities (edit, delete, pin, lock threads).
- Faculty can post new topics and reply to existing threads.
- Forum participation is tracked for badge awards.

### Recommended Plugin Approach

- **bbPress** (free, tightly integrated with WordPress) or **BuddyPress** (if broader community features are desired).
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
- Per-assignment authoring scope (ensuring contributors see only their own assigned content) is **not native to WordPress** and will require a permissions plugin (e.g., PublishPress Permissions) for pages, and/or the LMS plugin's built-in instructor-scoping for courses. Plugin selection in §7 and §15 should account for this requirement.
- The developer should configure a notification when content is submitted for review.

---

## 12. Notifications & Microsoft 365 Integration

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

- Email delivery should use a transactional email service (e.g., SendGrid, Mailgun, or the hosting provider's SMTP) to avoid deliverability issues.
- Microsoft Graph API integration for Outlook calendar will require an Entra app registration with `Calendars.ReadWrite` delegated permissions.

---

## 13. Accessibility

| Requirement | Standard |
|---|---|
| **Compliance level** | WCAG 2.1 Level AA or greater |
| **Theme** | The WordPress theme must be accessibility-ready (tested against WCAG criteria). |
| **Plugins** | All plugins (LMS, forums, events) must produce accessible front-end markup. |
| **Media** | Panopto videos should include captions. The site should support alt text for all images. |
| **Testing** | Accessibility audit recommended before launch (automated + manual testing). |
| **DU branding** | Follow Dominican University's brand guidelines for colors, typography, and logo usage. CTLE and the developer should coordinate with DU Marketing/Communications. |

---

## 14. Home Page Requirements

| Element | Description |
|---|---|
| **Upcoming events** | Display the next several upcoming events (configurable count, e.g., 3–5) with title, date, series, and a registration CTA. |
| **Pinned event** | CTLE Admin can "pin" a specific event to a prominent home-page position — either to drive registrations for an upcoming event or to highlight the recording of a signature past event. |
| **Course highlights** | Optional: featured or new courses. |
| **Announcements / News** | Latest blog post or CTLE announcement. |
| **Quick links** | Links to the course catalog, event calendar, forums, and resources. |

---

## 15. Recommended Plugin Stack

The following is a starting-point recommendation for the developer to evaluate. Final selections should consider compatibility, licensing costs, and long-term maintenance.

| Function | Recommended Plugin(s) | Notes |
|---|---|---|
| **SSO / Authentication** | miniOrange SAML SSO, or OpenID Connect Generic | Must support Entra ID; must support email-based account linking and preserve existing roles on re-login (see §5) |
| **Two-factor authentication** | Two Factor, or WP 2FA (Melapress) | Required on the break-glass recovery account (TOTP). See §5. |
| **Audit logging & alerting** | WP Activity Log | Real-time email alerts to CTLE Admins on any login to the recovery account. See §5. |
| **Login URL obfuscation** | WPS Hide Login (or equivalent security plugin feature) | Changes the default `wp-login.php` path. See §5. |
| **LMS / Courses** | LearnDash, LifterLMS, or Tutor LMS | Evaluate for enrollment mgmt, quiz engine, completion tracking |
| **Events** | The Events Calendar (Pro) + Event Tickets | Custom post type with series taxonomy, registration, capacity |
| **Forums** | bbPress | Free, lightweight, WP-native |
| **Badges** | BadgeOS + Open Badges integration | Free tier; auto-award from course/forum triggers |
| **LTI** | LTI Platform for WordPress | LTI 1.3 launch from Canvas |
| **Email** | WP Mail SMTP + transactional email provider | Reliable delivery |
| **Page builder** | Elementor, Beaver Builder, or Gutenberg blocks | Developer preference; must be accessible |
| **Access control / per-post permissions** | PublishPress Permissions (or equivalent) | Needed to scope Contributor authoring to specific assigned pages/courses (see §4, §11) |
| **Calendar integration** | ICS export or Microsoft Graph API | For Outlook calendar adds |

---

## 16. Open Questions & Future Considerations

| # | Question / Consideration | Status |
|---|---|---|
| 1 | **Badge plugin selection:** BadgeOS vs. alternatives — developer to evaluate compatibility with chosen LMS plugin. | Open |
| 2 | **Microsoft Graph API scope:** IT to confirm willingness to grant `Calendars.ReadWrite` for direct Outlook calendar integration vs. simpler .ics download approach. | Needs IT input |
| 3 | **Content migration:** Inventory of existing Canvas course materials to be ported. Determine formats (HTML, PDF, video links) and migration effort. | Future work |
| 5 | **Analytics:** Does CTLE need reporting dashboards (course completion rates, event attendance trends, popular content)? If so, built-in LMS reporting + Google Analytics or a WP analytics plugin. | To be determined |
| 6 | **Multilingual support:** Any need for content in languages other than English? | To be determined |
| 7 | **Data retention:** Faculty training data will be exported to Interfolio for retention. Confirm whether IT has any other retention requirements. | Needs IT input |
| 8 | **Custom theme vs. commercial theme:** Developer to recommend based on DU brand requirements and budget. | Open |
| 9 | **Disaster recovery:** Confirm RPO/RTO requirements with IT. | Needs IT input |
| 10 | **Interfolio integration:** Determine export format, frequency, and whether Interfolio offers an API for automated delivery of completed training records. | Needs CTLE, Provost's Office input |
| 11 | **Course-contributor mechanism:** Determine whether the chosen LMS plugin's native instructor/author model is sufficient for scoping course contributors, or whether a separate permissions plugin is needed for courses as well as pages. | Open |

---

## 17. Changelog

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1.0 | 2026-02-11 | sendres | Initial version for CTLE and IT review |
| 0.1.1 | 2026-03-09 | sendres | Minor revisions after pdriver and kodell review |
| 0.1.2 | 2026-03-09 | sendres | Add Interfolio export requirement for completed faculty training records |
| 0.1.3 | 2026-03-10 | sendres | Fix document repository link |
| 0.1.4 | 2026-04-13 | sendres | Clarify Contributor role |
| 0.1.5 | 2026-04-13 | sendres | Rework authentication with SSO/LTI as primary access and single break-glass recovery account |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

---

**End of Specification**

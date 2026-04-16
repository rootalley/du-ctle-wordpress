# CTLE WordPress Hosting — Vendor Requirements

## Table of Contents

1. [Purpose & Scope](#1-purpose--scope)
2. [Project Context](#2-project-context)
3. [Hosting Infrastructure](#3-hosting-infrastructure)
4. [Security & Compliance](#4-security--compliance)
5. [WordPress Platform Capabilities](#5-wordpress-platform-capabilities)
6. [Performance & Reliability](#6-performance--reliability)
7. [Support & Service Level](#7-support--service-level)
8. [Migration & Onboarding](#8-migration--onboarding)
9. [Contractual & Commercial](#9-contractual--commercial)
10. [Vendor Evaluation Checklist](#10-vendor-evaluation-checklist)
11. [Changelog](#11-changelog)

---

## 1. Purpose & Scope

This document defines the requirements that a managed WordPress hosting vendor must meet to successfully host the Center for Teaching & Learning Excellence (CTLE) website for Dominican University (DU). It is derived from the project's [Technical Requirements](REQUIREMENTS.md) (v0.1.18) and is intended to be shared with prospective vendors and used internally to evaluate proposals.

**What is in scope:** Hosting infrastructure, platform management, security posture, support services, and commercial terms.

**What is out of scope:** Content development, WordPress theme/plugin development, and Canvas LMS or Microsoft Entra ID configuration (handled by DU IT and the site developer).

---

## 2. Project Context

The CTLE is moving its faculty professional-development website from the Canvas LMS to a standalone WordPress site at `ctle.dom.edu`. The site will serve approximately 200–400 faculty users (full-time and adjunct) and an unknown volume of public visitors. Key functional areas include:

- **Self-paced courses** with enrollment, quizzes, completion tracking, and PDF certificate generation (via an LMS plugin such as LearnDash, LifterLMS, or Tutor LMS)
- **Events calendar** with registration, capacity management, waitlists, and Microsoft Outlook calendar integration
- **Discussion forums** restricted to authenticated DU faculty (via wpForo, which uses its own database tables)
- **Achievements and engagement system** with cross-platform triggers spanning courses, events, and forums (via GamiPress and wpForo's built-in reputation system)
- **Site-wide search** with relevance ranking, custom post type indexing, and access-aware results (via Relevanssi)
- **Single Sign-On** via Microsoft Entra ID (OIDC/SAML) with 24-hour WordPress session lifetime and automatic profile sync on every login
- **LTI 1.3 integration** with Canvas LMS for seamless faculty launch from the university's LMS
- **Email notifications** sent via a DU Microsoft 365 shared application mailbox (e.g., `ctle@dom.edu`) using WP Mail SMTP

The site will be managed day-to-day by a CTLE administrator (non-technical content author) and a contract developer. DU IT's involvement is limited to SSO configuration, DNS, M365 mailbox provisioning, and security vetting.

---

## 3. Hosting Infrastructure

### 3.1 Core Requirements

| # | Requirement | Priority |
|---|---|---|
| H-1 | **Managed WordPress hosting** — vendor handles WordPress core updates, server patching, and infrastructure maintenance. | Required |
| H-2 | **PHP 8.1 or later** with support for current WordPress-recommended PHP versions as they evolve. | Required |
| H-3 | **MySQL 8.0+ or MariaDB 10.6+** database engine. Note: the forum plugin (wpForo) creates its own database tables outside the standard WordPress schema; the database engine must support this without restrictions. | Required |
| H-4 | **SSL/TLS** — free, auto-renewing SSL certificates with HTTPS enforced site-wide. | Required |
| H-5 | **Custom domain** — ability to map the `ctle.dom.edu` subdomain (DU IT will manage DNS; vendor must support external domain mapping). | Required |
| H-6 | **Staging environment** — a one-click (or equivalent) staging/dev copy of the production site for testing updates, plugin changes, and theme modifications before they are pushed live. | Required |
| H-7 | **SSH and/or SFTP access** for the developer to perform command-line operations, deploy code, and troubleshoot. | Required |
| H-8 | **WP-CLI access** or equivalent command-line tooling for efficient site management. | Preferred |
| H-9 | **Server location** — United States preferred. If infrastructure is outside the US, vendor must document data residency and any implications for data sovereignty. | Preferred |
| H-10 | **Scalability** — ability to handle traffic surges (e.g., start-of-semester enrollment spikes, event registration bursts of 50–100 concurrent users) without manual intervention. Auto-scaling or burst capacity is preferred. | Preferred |

### 3.2 Storage & Transfer

| # | Requirement | Priority |
|---|---|---|
| S-1 | **Disk storage** — sufficient for the site's expected content (course materials, documents, images, PDF certificates, Relevanssi search index). Minimum 10 GB; 20+ GB preferred. Vendor should specify included storage and overage pricing. | Required |
| S-2 | **Bandwidth / data transfer** — unmetered or generous monthly allocation appropriate for a site of this scale. Vendor should specify any bandwidth caps. | Required |
| S-3 | **File upload limits** — PHP `upload_max_filesize` and `post_max_size` configurable to at least 64 MB (for course materials and media uploads). | Required |

---

## 4. Security & Compliance

| # | Requirement | Priority |
|---|---|---|
| C-1 | **SOC 2 Type II certification** (or equivalent such as ISO 27001). Vendor must provide current certification documentation for DU IT review. | Required |
| C-2 | **Daily automated backups** with a minimum 30-day retention period. | Required |
| C-3 | **Point-in-time restore** capability — ability to restore the site to a specific backup point, not just the most recent backup. | Required |
| C-4 | **Off-site backup storage** — backups stored in a separate geographic location or cloud region from the production environment. | Preferred |
| C-5 | **On-demand backup** — ability for the developer or CTLE Admin to trigger a manual backup at any time (e.g., before a major update). | Required |
| C-6 | **Web Application Firewall (WAF)** — built-in or integrated WAF protecting against OWASP Top 10 threats (SQL injection, XSS, CSRF, etc.). | Required |
| C-7 | **DDoS mitigation** — network-level and application-level DDoS protection. | Required |
| C-8 | **Malware scanning and removal** — automated malware scanning with alerting; vendor-assisted cleanup if malware is detected. | Required |
| C-9 | **Brute-force protection** — login rate limiting, CAPTCHA support, or IP-based blocking for the WordPress login page. Note: the site uses an obfuscated login URL (the default `wp-login.php` path is changed); the vendor's brute-force protection must support or not interfere with this configuration. | Required |
| C-10 | **Two-factor authentication (2FA)** support — hosting must not block WordPress 2FA plugins (e.g., Two Factor, WP 2FA). The site uses TOTP-based 2FA on a break-glass recovery account. | Required |
| C-11 | **Access logging** — web server access logs and error logs available to the developer for troubleshooting and auditing. | Required |
| C-12 | **Data handling on termination** — vendor must describe their data return/destruction policy upon contract termination, including timelines and data export formats. | Required |
| C-13 | **Breach notification SLA** — vendor must contractually commit to notifying DU IT **within 24 hours** of any confirmed or suspected security incident affecting CTLE data. This is a firm requirement derived from the project's privacy obligations under Illinois PIPA (see REQUIREMENTS.md §14). | Required |
| C-14 | **Data processing agreement** — vendor must be willing to execute DU's data processing agreement or equivalent data protection addendum. The hosting provider is named as a third-party recipient of faculty data in the site's privacy policy. | Required |

---

## 5. WordPress Platform Capabilities

The CTLE site relies on several third-party plugins with specific server-side requirements. The vendor must not restrict the use of custom plugins or PHP configurations beyond what is necessary for security and performance.

### 5.1 Plugin & Configuration Support

| # | Requirement | Priority |
|---|---|---|
| W-1 | **No plugin restrictions** — ability to install any WordPress plugin from the official repository or from third-party sources (zip upload). Some budget managed hosts restrict custom plugins; this is not acceptable. The site runs 15+ plugins including an LMS, event management, forums (wpForo), achievements (GamiPress), search (Relevanssi), SSO, 2FA, activity logging, and a page builder. | Required |
| W-2 | **Custom PHP configuration** — ability to modify `php.ini` or equivalent settings (memory limits, execution time, upload sizes) as needed by LMS, event, and forum plugins. | Required |
| W-3 | **Cron job support** — reliable execution of WordPress cron (`wp-cron`) or support for server-level cron to replace WordPress's pseudo-cron. Server-level cron is preferred for reliable scheduled tasks (email notifications, event reminders, waitlist processing, search index updates). | Required |
| W-4 | **Outbound SMTP** — the site sends email via a DU Microsoft 365 shared application mailbox using WP Mail SMTP. Outbound SMTP on ports 587 and 465 must not be blocked. | Required |
| W-5 | **REST API and external API calls** — no restrictions on outgoing HTTP/HTTPS requests from PHP. Required for Microsoft Entra SSO token endpoints, Microsoft Graph API (Outlook calendar integration), LTI 1.3 OIDC/JWKS endpoints, and Panopto video embeds. | Required |
| W-6 | **Multisite support** — not currently required, but if the university later chooses to run multiple WordPress sites under one installation, the hosting plan should support (or offer an upgrade path to) WordPress Multisite. | Preferred |

### 5.2 PHP Requirements

| # | Requirement | Priority |
|---|---|---|
| W-7 | **PHP memory limit** — minimum 256 MB. LMS plugins with quiz engines, GamiPress achievement triggers, and Relevanssi search indexing can be memory-intensive. | Required |
| W-8 | **PHP `max_execution_time`** — minimum 120 seconds (for bulk enrollment operations, CSV exports of completion records, and Relevanssi index rebuilds). | Required |

### 5.3 Database Access

| # | Requirement | Priority |
|---|---|---|
| W-9 | **Database access** — phpMyAdmin or equivalent web-based database management tool, plus direct MySQL/MariaDB access for the developer if needed. Note: wpForo creates approximately 20+ custom database tables; the vendor must not restrict plugins from creating their own tables. | Preferred |

---

## 6. Performance & Reliability

| # | Requirement | Priority |
|---|---|---|
| P-1 | **Uptime SLA** — 99.9% or higher, measured monthly. Vendor must define how uptime is measured and what remedies (credits, etc.) apply when the SLA is not met. | Required |
| P-2 | **Content Delivery Network (CDN)** — integrated or bundled CDN for static asset delivery (images, CSS, JS). | Required |
| P-3 | **Server-side caching** — object caching (Redis or Memcached) and/or full-page caching built into the hosting stack. Must be compatible with WordPress plugins for cache invalidation. **Critical:** page caching must correctly bypass cache for logged-in (authenticated) users so that personalized content (enrolled courses, forum access, self-view dashboard) renders correctly while still serving cached pages to anonymous visitors. | Required |
| P-4 | **HTTP/2 or HTTP/3** support. | Required |
| P-5 | **Page load targets** — the site targets under 3 seconds for a full page load (uncached) and under 2 seconds for subsequent loads (cached) on a standard broadband connection. Vendor should describe what caching, CDN, and optimization features they provide to support these targets. | Required |
| P-6 | **Resource isolation** — the site should not be affected by "noisy neighbor" issues. Dedicated or containerized resources preferred over traditional shared hosting. | Preferred |
| P-7 | **Image optimization** — built-in or supported image compression/WebP conversion. If not built in, the vendor must not block image optimization plugins (ShortPixel, Smush, Imagify). | Preferred |

---

## 7. Support & Service Level

| # | Requirement | Priority |
|---|---|---|
| T-1 | **Support hours** — Monday through Friday, US business hours (Central or Eastern time zone). After-hours support for critical/site-down issues is preferred but not required. | Required |
| T-2 | **Support channels** — ticket/email support at a minimum. Live chat during business hours is preferred. | Required |
| T-3 | **Response time targets** — vendor should specify initial response time commitments by severity level (e.g., site-down < 1 hour, degraded < 4 hours, general inquiry < 1 business day). | Required |
| T-4 | **WordPress expertise** — support staff should be knowledgeable about WordPress hosting, plugin conflicts, PHP errors, and common performance issues. General server support alone is insufficient. | Required |
| T-5 | **Escalation path** — a defined escalation process for issues that frontline support cannot resolve. | Required |
| T-6 | **Status page** — a public status page or incident notification system so the CTLE and developer can monitor platform health. | Preferred |
| T-7 | **Proactive monitoring** — the vendor should monitor the hosting environment and proactively notify the customer of issues (e.g., resource exhaustion, certificate expiration, failed backups). | Preferred |

---

## 8. Migration & Onboarding

| # | Requirement | Priority |
|---|---|---|
| M-1 | **Free site migration** — vendor should offer at least one free migration from an existing WordPress installation or provide tooling and documentation for self-migration. (Note: the initial build will likely be a fresh install, but migration support is valuable for future vendor changes.) | Preferred |
| M-2 | **Onboarding documentation** — clear documentation or onboarding guide covering: DNS configuration, SSL setup, staging workflow, backup management, SFTP/SSH credentials, and SMTP configuration. | Required |
| M-3 | **DNS configuration guidance** — vendor must provide the necessary DNS records (A/CNAME) for DU IT to configure the `ctle.dom.edu` subdomain. | Required |
| M-4 | **Time to provision** — new site environments should be provisioned within 24 hours of account activation. | Required |

---

## 9. Contractual & Commercial

| # | Requirement | Priority |
|---|---|---|
| K-1 | **Pricing transparency** — vendor must provide clear, all-inclusive pricing. Itemize any additional costs for overage (storage, bandwidth), premium support, CDN, backups, or staging environments. Target budget is approximately $30/month. | Required |
| K-2 | **No long-term lock-in** — the university must be able to terminate the agreement with reasonable notice (30–60 days) without penalty, regardless of billing cycle. | Required |
| K-3 | **Data portability** — upon termination, vendor must provide a full export of all site data (files, database, media, backups) in standard formats (SQL dump, file archive) within 30 days. | Required |
| K-4 | **Data processing agreement** — vendor must be willing to execute DU's data processing agreement or equivalent data protection addendum if required. | Required |
| K-5 | **Payment terms** — vendor should accept purchase order or institutional invoicing if required by DU procurement. Credit-card-only billing may be acceptable if the budget can be processed that way. | Preferred |
| K-6 | **Higher-education references** — vendor should be able to provide references from other higher-education institutions, or demonstrate experience hosting sites with similar requirements. | Preferred |
| K-7 | **Service roadmap** — vendor should describe how they handle major WordPress version upgrades, PHP version transitions, and end-of-life policies for deprecated technologies. | Preferred |

---

## 10. Vendor Evaluation Checklist

Use this checklist when reviewing vendor proposals. Score each item: **Yes** (fully meets), **Partial** (meets with caveats), **No** (does not meet), or **N/A** (not applicable). Add the vendor's monthly cost in the header for side-by-side comparison.

### Instructions

1. Complete one checklist per vendor.
2. Fill in the vendor name and the plan/tier being evaluated.
3. For each requirement, record the vendor's response and your assessment.
4. Use the **Notes** column for caveats, limitations, or follow-up questions.
5. Tally the results and compare vendors using the summary at the bottom.

---

**Vendor Name:** ___________________________  
**Plan / Tier:** ___________________________  
**Monthly Cost:** ___________________________  
**Evaluator:** ___________________________  
**Date:** ___________________________

### Hosting Infrastructure

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| H-1 | Managed WordPress hosting (core updates, patching) | | |
| H-2 | PHP 8.1+ | | |
| H-3 | MySQL 8.0+ or MariaDB 10.6+ (supports custom tables) | | |
| H-4 | Free auto-renewing SSL with HTTPS enforced | | |
| H-5 | Custom domain mapping (`ctle.dom.edu`) | | |
| H-6 | Staging environment | | |
| H-7 | SSH and/or SFTP access | | |
| H-8 | WP-CLI access | | |
| H-9 | US-based server location (or documented alternative) | | |
| H-10 | Auto-scaling or burst capacity | | |

### Storage & Transfer

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| S-1 | Storage ≥ 10 GB (specify included amount: _____ GB) | | |
| S-2 | Unmetered or generous bandwidth (specify: _____) | | |
| S-3 | File upload limit configurable to ≥ 64 MB | | |

### Security & Compliance

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| C-1 | SOC 2 Type II or ISO 27001 (provide cert date: _____) | | |
| C-2 | Daily automated backups (retention: _____ days) | | |
| C-3 | Point-in-time restore | | |
| C-4 | Off-site backup storage | | |
| C-5 | On-demand manual backups | | |
| C-6 | Web Application Firewall (WAF) | | |
| C-7 | DDoS mitigation | | |
| C-8 | Malware scanning and removal | | |
| C-9 | Brute-force login protection (compatible with obfuscated login URL) | | |
| C-10 | 2FA plugin support (does not block TOTP plugins) | | |
| C-11 | Access and error log availability | | |
| C-12 | Data return/destruction policy on termination | | |
| C-13 | 24-hour breach notification SLA (contractual) | | |
| C-14 | Willing to execute data processing agreement | | |

### WordPress Platform

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| W-1 | No restrictions on custom plugin installation (15+ plugins) | | |
| W-2 | Configurable PHP settings (memory, timeouts, uploads) | | |
| W-3 | Server-level cron support | | |
| W-4 | Outbound SMTP (ports 587/465 open for M365 relay) | | |
| W-5 | No restrictions on outbound HTTP/HTTPS from PHP | | |
| W-6 | Multisite support or upgrade path | | |
| W-7 | PHP memory limit ≥ 256 MB | | |
| W-8 | PHP max_execution_time ≥ 120 seconds | | |
| W-9 | Database management tool; supports custom tables | | |

### Performance & Reliability

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| P-1 | Uptime SLA ≥ 99.9% (specify: _____%) | | |
| P-2 | Integrated CDN | | |
| P-3 | Server-side caching with authenticated-user bypass | | |
| P-4 | HTTP/2 or HTTP/3 | | |
| P-5 | Page load: < 3s uncached, < 2s cached (describe support) | | |
| P-6 | Resource isolation (container/dedicated) | | |
| P-7 | Image optimization (built-in or plugin-compatible) | | |

### Support

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| T-1 | Business-hours support (M-F, US time zones) | | |
| T-2 | Ticket/email support; live chat preferred | | |
| T-3 | Published response time targets by severity | | |
| T-4 | WordPress-knowledgeable support staff | | |
| T-5 | Defined escalation process | | |
| T-6 | Public status page | | |
| T-7 | Proactive monitoring and alerting | | |

### Migration & Onboarding

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| M-1 | Free migration assistance | | |
| M-2 | Onboarding documentation | | |
| M-3 | DNS configuration guidance for `ctle.dom.edu` | | |
| M-4 | Environment provisioned within 24 hours | | |

### Contractual & Commercial

| # | Requirement | Y/P/N | Notes |
|---|---|---|---|
| K-1 | Transparent, all-inclusive pricing (target ~$30/mo) | | |
| K-2 | Termination with 30–60 days notice, no penalty | | |
| K-3 | Full data export within 30 days of termination | | |
| K-4 | Willing to execute data processing agreement | | |
| K-5 | Accepts PO or institutional invoicing | | |
| K-6 | Higher-education references | | |
| K-7 | Documented service roadmap / EOL policies | | |

### Summary

| Category | Required Met | Required Total | Preferred Met | Preferred Total |
|---|---|---|---|---|
| Hosting Infrastructure (H, S) | /9 | 9 | /4 | 4 |
| Security & Compliance (C) | /12 | 12 | /2 | 2 |
| WordPress Platform (W) | /7 | 7 | /2 | 2 |
| Performance & Reliability (P) | /5 | 5 | /2 | 2 |
| Support (T) | /5 | 5 | /2 | 2 |
| Migration & Onboarding (M) | /2 | 2 | /2 | 2 |
| Contractual & Commercial (K) | /4 | 4 | /3 | 3 |
| **Totals** | **/44** | **44** | **/17** | **17** |

**Monthly Cost:** $ _________

**Overall Assessment:** ☐ Recommended &ensp; ☐ Acceptable with caveats &ensp; ☐ Not recommended

**Key Strengths:**

1. _____
2. _____
3. _____

**Key Gaps or Risks:**

1. _____
2. _____
3. _____

**Evaluator Signature:** ___________________________  
**Date:** ___________________________

---

## 11. Changelog

| Version | Date       | Author  | Changes |
|---------|------------|---------|---------|
| 0.1.3   | 2026-03-10 | sendres | Initial version derived from REQUIREMENTS.md v0.1.3 |
| 0.1.18  | 2026-04-16 | sendres | Full refresh to align with REQUIREMENTS.md v0.1.18 |

*This document is maintained in the [du-ctle-wordpress](https://github.com/rootalley/du-ctle-wordpress/) repository.*

---

**End of Specification**

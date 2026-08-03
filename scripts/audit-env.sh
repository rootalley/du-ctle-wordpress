#!/usr/bin/env bash
#
# Capture the full state of a Kinsta WordPress environment.
#
# Designed to run over SSH without being uploaded first:
#
#   ssh <user>@<host> -p <port> 'bash -s' < scripts/audit-env.sh > docs/audit/staging-$(date +%F).txt
#   ssh <user>@<host> -p <port> 'bash -s' < scripts/audit-env.sh > docs/audit/live-$(date +%F).txt
#
# Everything goes to stdout so the redirect captures it. Output is plain text
# with === MARKERS === so sections can be pulled out mechanically later.
#
# Read-only. Nothing here writes to the database or the filesystem.

set -uo pipefail

WEBROOT="${WEBROOT:-$HOME/public}"

# A non-login shell over `bash -s` may not inherit the full PATH.
export PATH="$PATH:/usr/local/bin:/usr/bin:$HOME/bin"

section() { printf '\n=== %s ===\n' "$1"; }

# Run a wp command, but never let a failure abort the rest of the audit —
# a missing table or an inactive plugin should show up as a gap, not a halt.
try() { "$@" 2>&1 || echo "(command failed: $*)"; }

section "AUDIT META"
echo "captured:  $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
echo "host:      $(hostname -f 2>/dev/null || hostname)"
echo "user:      $(whoami)"
echo "webroot:   $WEBROOT"

if ! command -v wp >/dev/null 2>&1; then
  echo
  echo "FATAL: wp-cli not found on PATH. Nothing further can be captured."
  exit 1
fi

cd "$WEBROOT" || { echo "FATAL: cannot cd to $WEBROOT"; exit 1; }

section "ENVIRONMENT"
echo "wp-cli:    $(wp --version 2>/dev/null)"
echo "php-cli:   $(php -v 2>/dev/null | head -1)"
echo "site-url:  $(try wp option get siteurl)"
# The CLI SAPI reports its own php.ini, not php-fpm's. Web-facing limits must
# come from Site Health or Query Monitor — do not trust these for the site.
echo "NOTE: php-cli above is the CLI SAPI. Web values differ; read them from"
echo "      Site Health -> Info -> Server."

section "CORE"
try wp core version
echo "--- updates available ---"
try wp core check-update --format=csv

section "THEMES"
try wp theme list --fields=name,status,version,update --format=csv

section "PLUGINS"
try wp plugin list --fields=name,status,version,update --format=csv

section "MUST-USE PLUGINS"
try wp plugin list --status=must-use --fields=name,version --format=csv
echo "--- files on disk ---"
ls -la wp-content/mu-plugins/ 2>/dev/null || echo "(no mu-plugins directory)"

section "USERS"
# Contains real email addresses. Review before committing the output.
try wp user list --fields=ID,user_login,user_email,roles,user_registered --format=csv

section "USER META (sso linkage)"
for uid in $(wp user list --field=ID 2>/dev/null); do
  printf 'ID=%s sis_user_id=%s\n' "$uid" "$(wp user meta get "$uid" sis_user_id 2>/dev/null)"
done

section "CONTENT COUNTS"
for pt in $(wp post-type list --field=name 2>/dev/null); do
  printf '%s=%s\n' "$pt" "$(wp post list --post_type="$pt" --format=count 2>/dev/null)"
done

section "PAGES"
try wp post list --post_type=page --fields=ID,post_title,post_status,post_modified --format=csv

section "POSTS"
try wp post list --post_type=post --fields=ID,post_title,post_status,post_modified --format=csv

section "POST TYPES"
try wp post-type list --fields=name,label,public --format=csv

section "OPTIONS"
for o in siteurl home blog_public template stylesheet \
         show_on_front page_on_front page_for_posts \
         users_can_register default_role admin_email \
         wp_page_for_privacy_policy timezone_string; do
  printf '%s=%s\n' "$o" "$(wp option get "$o" 2>/dev/null)"
done

section "ACTIVE PLUGINS (raw option)"
try wp option get active_plugins --format=json

section "DATABASE TABLES"
# Page builders and form plugins create their own tables. Anything beyond the
# core set changes what a selective table push has to carry.
try wp db query "SHOW TABLES" --skip-column-names

section "DATABASE SIZE"
try wp db size --tables --format=csv

section "UPLOADS"
du -sh wp-content/uploads 2>/dev/null || echo "(no uploads directory)"
echo "file count: $(find wp-content/uploads -type f 2>/dev/null | wc -l | tr -d ' ')"

section "DISK"
du -sh "$WEBROOT" 2>/dev/null
df -h "$WEBROOT" 2>/dev/null | tail -1

section "CRON"
echo "DISABLE_WP_CRON: $(wp config get DISABLE_WP_CRON 2>/dev/null || echo '(not set)')"
try wp cron event list --fields=hook,next_run_relative --format=csv

section "END"
echo "audit complete"

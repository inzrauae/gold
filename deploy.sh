#!/usr/bin/env bash
# One-shot cPanel deploy / update for Gold Price Today Sri Lanka.
#
# Run from cPanel Terminal (see README "Deploy on cPanel"):
#   cd ~ && (git -C gold-src pull --ff-only 2>/dev/null || git clone -b main https://github.com/inzrauae/gold.git gold-src) && ADMIN_EMAIL=you@example.lk bash gold-src/deploy.sh
#
# Safe to re-run for every update: .env, storage/ (database, logs, cache) and anything else
# already in public_html (.well-known, cgi-bin, ...) are never deleted.

set -euo pipefail

DOMAIN="${DOMAIN:-www.goldpricetoday.lk}"
BASE_DOMAIN="${DOMAIN#www.}"
SRC="${SRC:-$HOME/gold-src}"
APP="$HOME/goldprice-app"
WEB="$HOME/public_html"

say() { printf '\n\033[1;33m==> %s\033[0m\n' "$*"; }
die() { printf '\n\033[1;31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }

[ -d "$SRC/goldprice-app" ] && [ -d "$SRC/public_html" ] || die "Source checkout not found at $SRC"
[ -d "$WEB" ] || die "$WEB does not exist"

# --- PHP 8.1+ with pdo_sqlite ---------------------------------------------------------
say "Finding PHP 8.1+ with pdo_sqlite"
PHP=""
for c in php /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php \
         /opt/cpanel/ea-php82/root/usr/bin/php /opt/cpanel/ea-php81/root/usr/bin/php /usr/local/bin/php; do
    command -v "$c" >/dev/null 2>&1 || continue
    if "$c" -r 'exit(version_compare(PHP_VERSION, "8.1.0", ">=") && extension_loaded("pdo_sqlite") ? 0 : 1);' 2>/dev/null; then
        PHP="$(command -v "$c")"
        break
    fi
done
[ -n "$PHP" ] || die "No PHP 8.1+ with pdo_sqlite found. In cPanel > Select PHP Version choose 8.2+ and tick pdo_sqlite, then re-run."
echo "Using $PHP ($("$PHP" -r 'echo PHP_VERSION;'))"

# --- Application (outside the web root) ------------------------------------------------
say "Syncing application to $APP"
mkdir -p "$APP"
if command -v rsync >/dev/null 2>&1; then
    rsync -a --delete --exclude '/.env' --exclude '/storage/' "$SRC/goldprice-app/" "$APP/"
else
    tar -C "$SRC/goldprice-app" --exclude=./.env --exclude=./storage -cf - . | tar -C "$APP" -xf -
fi
mkdir -p "$APP"/storage/{cache,logs,feeds,locks,rate-limit}
chmod 755 "$APP" "$APP/storage" "$APP"/storage/*

# --- .env (first deploy only) ------------------------------------------------------------
set_env() {
    local key="$1" value="$2"
    if grep -q "^${key}=" "$APP/.env"; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$APP/.env"
    else
        printf '%s=%s\n' "$key" "$value" >> "$APP/.env"
    fi
}

if [ ! -f "$APP/.env" ]; then
    say "Creating $APP/.env"
    cp "$APP/.env.example" "$APP/.env"
    set_env APP_URL "https://$DOMAIN"
    set_env APP_ENV production
    set_env APP_DEBUG false
    set_env APP_KEY "$("$PHP" -r 'echo bin2hex(random_bytes(32));')"
    set_env FORCE_HTTPS true
    set_env DB_CONNECTION sqlite
    set_env DB_SQLITE_PATH "$APP/storage/database.sqlite"
    # Key-free providers so the site works immediately; add paid/free keys later (see DEPLOYMENT.md step 5).
    set_env FX_PRIMARY_PROVIDER exchangerate_api_open
    set_env MAX_FX_READING_AGE_MINUTES 1500
    set_env ADMIN_EMAIL "${ADMIN_EMAIL:-info@$BASE_DOMAIN}"
    set_env MAIL_FROM "noreply@$BASE_DOMAIN"
    set_env CRON_TOKEN "$("$PHP" -r 'echo bin2hex(random_bytes(16));')"
    chmod 600 "$APP/.env"
else
    echo ".env already exists - kept as is"
fi

# --- Web root -----------------------------------------------------------------------------
MARKER="$APP/storage/.deployed"
if [ ! -f "$MARKER" ]; then
    BACKUP="$HOME/public_html_backup_$(date +%Y%m%d_%H%M%S).tar.gz"
    say "First deploy: backing up current public_html to $BACKUP"
    tar -czf "$BACKUP" -C "$HOME" public_html
    rm -f "$WEB"/index.html "$WEB"/index.htm "$WEB"/default.htm "$WEB"/default.html
fi

say "Syncing web files to $WEB"
if command -v rsync >/dev/null 2>&1; then
    rsync -a --exclude 'router.php' "$SRC/public_html/" "$WEB/"
else
    cp -a "$SRC/public_html/." "$WEB/"
    rm -f "$WEB/router.php"
fi
find "$WEB/assets" -type d -exec chmod 755 {} + 2>/dev/null || true
find "$WEB/assets" -type f -exec chmod 644 {} + 2>/dev/null || true
chmod 644 "$WEB/index.php" "$WEB/.htaccess"

# --- Database, cache, first price -------------------------------------------------------
say "Migrating database"
"$PHP" "$APP/cli/migrate.php"
"$PHP" "$APP/cli/clear-cache.php" || true

if [ ! -f "$MARKER" ]; then
    say "Fetching first verified price"
    "$PHP" "$APP/cli/update-prices.php" || echo "Price update failed - check /admin after creating an admin user."
fi

# --- Cron (every 5 minutes, idempotent) ---------------------------------------------------
say "Installing cron job"
CRON_LINE="*/5 * * * * $PHP $APP/cli/scheduler.php >/dev/null 2>&1"
( crontab -l 2>/dev/null | grep -v 'goldprice-app/cli/scheduler.php' || true; echo "$CRON_LINE" ) | crontab -

date -u +%FT%TZ > "$MARKER"

say "Done"
cat <<EOF
Site:   https://$DOMAIN
Admin:  https://$DOMAIN/admin
Config: $APP/.env   (edit, then run: $PHP $APP/cli/clear-cache.php)

Create your admin login (first time only):
  $PHP $APP/cli/create-admin.php --email=YOUR_EMAIL
EOF

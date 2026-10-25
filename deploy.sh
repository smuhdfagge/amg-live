#!/usr/bin/env bash
#
# SWATCH website deployment script
#
# Run it from the project folder on the server:
#
#   bash deploy.sh                          # normal deploy (VPS, or cPanel with document root on /public)
#   bash deploy.sh --public-html ~/public_html   # cPanel, when the site must be served from public_html
#
# Options:
#   --public-html PATH   Copy the public folder into PATH and point its index.php back to this project
#   --no-pull            Skip "git pull" (use when you upload files by FTP or File Manager)
#   --branch NAME        Branch to pull (default: main)
#   --seed               Run the database seeder even when content exists (resets the admin password!)
#   --php PATH           Use this PHP binary instead of searching for one
#   -h, --help           Show this help
#
# What it does: finds a PHP 8.3+ binary, checks extensions, pulls the latest code,
# installs Composer packages, prepares .env, runs migrations, adds starting content
# on the first deploy only, links storage, caches config/routes/views, and fixes
# folder permissions. The site is put in maintenance mode during the update and
# brought back up even if a step fails.

set -Eeuo pipefail

MIN_PHP="8.3.0"
BRANCH="main"
PULL=1
FORCE_SEED=0
PUBLIC_HTML=""
PHP_BIN="${PHP_BIN:-}"

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

bold=$'\e[1m'; green=$'\e[32m'; yellow=$'\e[33m'; red=$'\e[31m'; reset=$'\e[0m'
[ -t 1 ] || { bold=""; green=""; yellow=""; red=""; reset=""; }

step() { printf '\n%s==> %s%s\n' "$bold" "$*" "$reset"; }
ok()   { printf '%s    %s%s\n' "$green" "$*" "$reset"; }
warn() { printf '%s    %s%s\n' "$yellow" "$*" "$reset"; }
fail() { printf '\n%sERROR: %s%s\n' "$red" "$*" "$reset" >&2; exit 1; }

usage() { sed -n '3,22p' "$0" | sed 's/^# \{0,1\}//'; exit 0; }

while [ $# -gt 0 ]; do
    case "$1" in
        --public-html) PUBLIC_HTML="${2:-}"; shift 2 ;;
        --public-html=*) PUBLIC_HTML="${1#*=}"; shift ;;
        --no-pull) PULL=0; shift ;;
        --branch) BRANCH="${2:-main}"; shift 2 ;;
        --branch=*) BRANCH="${1#*=}"; shift ;;
        --seed) FORCE_SEED=1; shift ;;
        --php) PHP_BIN="${2:-}"; shift 2 ;;
        --php=*) PHP_BIN="${1#*=}"; shift ;;
        -h|--help) usage ;;
        *) fail "Unknown option: $1 (run: bash deploy.sh --help)" ;;
    esac
done

# ---------------------------------------------------------------------------
# 1. Find a suitable PHP binary
# ---------------------------------------------------------------------------
step "Looking for PHP $MIN_PHP or newer"

php_ok() {
    [ -x "$1" ] || command -v "$1" >/dev/null 2>&1 || return 1
    "$1" -r "exit(version_compare(PHP_VERSION, '$MIN_PHP', '>=') ? 0 : 1);" >/dev/null 2>&1
}

if [ -n "$PHP_BIN" ]; then
    php_ok "$PHP_BIN" || fail "$PHP_BIN is not PHP $MIN_PHP or newer."
else
    candidates=(
        php8.5 php8.4 php8.3 php85 php84 php83
        /opt/cpanel/ea-php85/root/usr/bin/php /opt/cpanel/ea-php84/root/usr/bin/php /opt/cpanel/ea-php83/root/usr/bin/php
        /opt/alt/php85/usr/bin/php /opt/alt/php84/usr/bin/php /opt/alt/php83/usr/bin/php
        /usr/local/bin/php85 /usr/local/bin/php84 /usr/local/bin/php83
        /opt/plesk/php/8.5/bin/php /opt/plesk/php/8.4/bin/php /opt/plesk/php/8.3/bin/php
        /usr/local/php85/bin/php /usr/local/php84/bin/php /usr/local/php83/bin/php
        php /usr/local/bin/php /usr/bin/php
    )
    for c in "${candidates[@]}"; do
        if php_ok "$c"; then PHP_BIN="$c"; break; fi
    done
fi

if [ -z "$PHP_BIN" ]; then
    found="$(command -v php >/dev/null 2>&1 && php -r 'echo PHP_VERSION;' 2>/dev/null || echo 'none')"
    fail "No PHP $MIN_PHP+ found (default php is $found).
       In cPanel, open 'MultiPHP Manager' or 'Select PHP Version' and choose 8.3 or 8.4,
       then run again, or pass the binary directly, for example:
       bash deploy.sh --php /opt/cpanel/ea-php84/root/usr/bin/php"
fi

PHP_VERSION="$("$PHP_BIN" -r 'echo PHP_VERSION;')"
ok "Using $PHP_BIN (PHP $PHP_VERSION)"

# ---------------------------------------------------------------------------
# 2. Check PHP extensions
# ---------------------------------------------------------------------------
step "Checking PHP extensions"

required_ext=(ctype curl dom fileinfo filter mbstring openssl pdo session tokenizer xml)
missing=()
loaded="$("$PHP_BIN" -m | tr '[:upper:]' '[:lower:]')"
for e in "${required_ext[@]}"; do
    grep -qx "$e" <<<"$loaded" || missing+=("$e")
done
grep -qxE 'pdo_mysql|pdo_sqlite|pdo_pgsql' <<<"$loaded" || missing+=("pdo_mysql (or pdo_sqlite)")
grep -qx 'gd' <<<"$loaded" || warn "The gd extension is missing. The site works, but turn it on for image handling."

if [ ${#missing[@]} -gt 0 ]; then
    fail "Missing PHP extensions: ${missing[*]}
       Turn them on in cPanel > Select PHP Version > Extensions, or install them with your package manager."
fi
ok "All required extensions are loaded"

# ---------------------------------------------------------------------------
# 3. Find or download Composer
# ---------------------------------------------------------------------------
step "Preparing Composer"

COMPOSER=""
if [ -n "${COMPOSER_BIN:-}" ]; then
    COMPOSER="$COMPOSER_BIN"
elif [ -f "$APP_DIR/composer.phar" ]; then
    COMPOSER="$APP_DIR/composer.phar"
elif command -v composer >/dev/null 2>&1; then
    COMPOSER="$(command -v composer)"
    # Some hosts wrap composer in a shell script; only use it if it is a PHP file.
    if ! head -c 200 "$COMPOSER" | grep -q 'php'; then COMPOSER=""; fi
fi

if [ -z "$COMPOSER" ]; then
    warn "Composer not found. Downloading composer.phar into the project folder."
    "$PHP_BIN" -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');" \
        || fail "Could not download Composer. Upload composer.phar into $APP_DIR and run again."
    COMPOSER="$APP_DIR/composer.phar"
fi

composer() { "$PHP_BIN" "$COMPOSER" "$@"; }
artisan()  { "$PHP_BIN" "$APP_DIR/artisan" "$@"; }

ok "$(composer --version 2>/dev/null | head -n1)"

# ---------------------------------------------------------------------------
# 4. Maintenance mode (only when the app is already installed)
# ---------------------------------------------------------------------------
IN_MAINTENANCE=0
finish() {
    local code=$?
    if [ "$IN_MAINTENANCE" = 1 ]; then
        artisan up >/dev/null 2>&1 || true
        [ $code -ne 0 ] && warn "The site has been taken out of maintenance mode."
    fi
    if [ $code -ne 0 ]; then
        printf '\n%sDeployment stopped. Fix the error above and run bash deploy.sh again.%s\n' "$red" "$reset" >&2
    fi
}
trap finish EXIT

if [ -f vendor/autoload.php ] && [ -f .env ]; then
    step "Putting the site in maintenance mode"
    if artisan down --retry=30 >/dev/null 2>&1; then
        IN_MAINTENANCE=1
        ok "Visitors will see a short maintenance page"
    else
        warn "Could not enable maintenance mode. Continuing."
    fi
fi

# ---------------------------------------------------------------------------
# 5. Get the latest code
# ---------------------------------------------------------------------------
if [ "$PULL" = 1 ] && [ -d .git ]; then
    step "Pulling the latest code from $BRANCH"
    if ! git diff --quiet || ! git diff --cached --quiet; then
        warn "There are local changes on the server:"
        git status --short | sed 's/^/      /'
        fail "Commit, move or discard them first (git stash), or run with --no-pull."
    fi
    git fetch --prune origin "$BRANCH"
    git checkout -q "$BRANCH"
    git pull --ff-only origin "$BRANCH"
    ok "Now at $(git log -1 --pretty='%h %s')"
elif [ "$PULL" = 1 ]; then
    warn "This folder is not a git clone. Skipping the pull (upload new files before running)."
fi

# ---------------------------------------------------------------------------
# 6. Install PHP packages
# ---------------------------------------------------------------------------
step "Installing Composer packages"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
ok "Packages installed"

# ---------------------------------------------------------------------------
# 7. Environment file
# ---------------------------------------------------------------------------
step "Checking the .env file"
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i 's/^APP_ENV=.*/APP_ENV=production/; s/^APP_DEBUG=.*/APP_DEBUG=false/' .env
    artisan key:generate --force >/dev/null
    trap - EXIT
    printf '\n%sA new .env file has been created with a fresh app key.%s\n' "$yellow" "$reset"
    cat <<EOF

Open $APP_DIR/.env and fill in:
  - APP_URL            (https://swatch.org.ng)
  - DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
  - MAIL_MAILER=smtp and your mail server details (for form alerts)
  - ADMIN_EMAIL and a strong ADMIN_PASSWORD

Then run this script again:  bash deploy.sh $( [ -n "$PUBLIC_HTML" ] && printf -- '--public-html %q' "$PUBLIC_HTML" )
EOF
    exit 0
fi

if ! grep -qE '^APP_KEY=base64:.+' .env; then
    artisan key:generate --force >/dev/null
    ok "Generated a new APP_KEY"
fi

if grep -qE '^APP_DEBUG=true' .env && grep -qE '^APP_ENV=production' .env; then
    warn "APP_DEBUG is true in production. Set APP_DEBUG=false in .env to hide error details from visitors."
fi

if grep -qE '^DB_CONNECTION=sqlite' .env; then
    db_path="$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"' || true)"
    db_path="${db_path:-$APP_DIR/database/database.sqlite}"
    [ -f "$db_path" ] || { touch "$db_path"; ok "Created SQLite database at $db_path"; }
fi
ok ".env is in place"

# ---------------------------------------------------------------------------
# 8. Database
# ---------------------------------------------------------------------------
step "Clearing old caches"
artisan config:clear >/dev/null
artisan route:clear >/dev/null
artisan view:clear >/dev/null
artisan event:clear >/dev/null
ok "Done"

step "Running database migrations"
artisan migrate --force

artisan cache:clear >/dev/null 2>&1 || warn "Could not clear the data cache. Continuing."

step "Adding starting content (first deploy only)"
if [ "$FORCE_SEED" = 1 ]; then
    warn "--seed given: running the full seeder. The admin password will be reset to ADMIN_PASSWORD."
    artisan db:seed --force
else
    artisan swatch:seed-if-empty
fi

# ---------------------------------------------------------------------------
# 9. Storage, permissions, caches
# ---------------------------------------------------------------------------
step "Linking public storage"
if [ -L public/storage ] || [ -d public/storage ]; then
    ok "public/storage already exists"
else
    artisan storage:link >/dev/null 2>&1 || ln -s "$APP_DIR/storage/app/public" "$APP_DIR/public/storage"
    ok "Linked public/storage"
fi

step "Setting folder permissions"
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || warn "Could not change permissions on storage and bootstrap/cache."
ok "storage and bootstrap/cache are writable"

step "Caching configuration, routes, views and events"
artisan package:discover --ansi >/dev/null
artisan optimize
artisan view:cache >/dev/null

# ---------------------------------------------------------------------------
# 10. cPanel: publish into public_html
# ---------------------------------------------------------------------------
if [ -n "$PUBLIC_HTML" ]; then
    PUBLIC_HTML="${PUBLIC_HTML/#\~/$HOME}"
    step "Publishing the public folder into $PUBLIC_HTML"
    [ -d "$PUBLIC_HTML" ] || mkdir -p "$PUBLIC_HTML"
    case "$(cd "$PUBLIC_HTML" && pwd)" in
        "$APP_DIR"|"$APP_DIR/public") fail "--public-html must be outside the project folder." ;;
    esac

    # Copy everything except index.php and the storage link, overwriting old copies.
    (cd public && find . -mindepth 1 \( -path ./storage -o -path ./index.php -o -path ./hot \) -prune -o -print) |
    while IFS= read -r item; do
        if [ -d "public/$item" ]; then mkdir -p "$PUBLIC_HTML/$item"; else cp -p "public/$item" "$PUBLIC_HTML/$item"; fi
    done

    # index.php that loads the app from this folder
    sed -e "s#__DIR__.'/../storage/framework/maintenance.php'#'$APP_DIR/storage/framework/maintenance.php'#" \
        -e "s#__DIR__.'/../vendor/autoload.php'#'$APP_DIR/vendor/autoload.php'#" \
        -e "s#__DIR__.'/../bootstrap/app.php'#'$APP_DIR/bootstrap/app.php'#" \
        public/index.php > "$PUBLIC_HTML/index.php"
    grep -q "$APP_DIR/bootstrap/app.php" "$PUBLIC_HTML/index.php" \
        || fail "Could not rewrite $PUBLIC_HTML/index.php. Edit its paths by hand to point to $APP_DIR."

    if [ ! -e "$PUBLIC_HTML/storage" ]; then
        ln -s "$APP_DIR/storage/app/public" "$PUBLIC_HTML/storage"
    fi

    ok "public_html now serves the site"
fi

# ---------------------------------------------------------------------------
# 11. Back online
# ---------------------------------------------------------------------------
if [ "$IN_MAINTENANCE" = 1 ]; then
    step "Bringing the site back online"
    artisan up >/dev/null
    IN_MAINTENANCE=0
fi
trap - EXIT

app_url="$(grep -E '^APP_URL=' .env | cut -d= -f2- | tr -d '"' || true)"
printf '\n%sDeployment finished.%s\n' "$green$bold" "$reset"
printf '  Website: %s\n  Admin:   %s/admin\n\n' "${app_url:-your domain}" "${app_url:-your domain}"

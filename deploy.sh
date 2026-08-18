#!/usr/bin/env bash
set -Eeuo pipefail

# Slepé Slunce: první instalace i opakovaný produkční deploy.
# Spouštějte jako root z kořene projektu. Volitelný první argument je git větev.

APP_DIR="${APP_DIR:-$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)}"
APP_USER="${APP_USER:-www-data}"
APP_GROUP="${APP_GROUP:-www-data}"
BRANCH="${1:-${DEPLOY_BRANCH:-}}"
PHP_BIN="${PHP_BIN:-$(command -v php || true)}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer || true)}"
LOCK_FILE="/var/lock/slepe-slunce-deploy.lock"
LOG_FILE="/var/log/slepe-slunce-deploy.log"
QUEUE_SERVICE="slepe-slunce-queue.service"
CRON_FILE="/etc/cron.d/slepe-slunce"
BACKUP_DIR="/var/backups/slepe-slunce"
FIRST_INSTALL=false
MAINTENANCE=false
BACKUP_TEMP=""

if [[ ${EUID} -ne 0 ]]; then
    echo "Tento skript musí běžet jako root: sudo ./deploy.sh [větev]" >&2
    exit 1
fi

if [[ -z ${PHP_BIN} || -z ${COMPOSER_BIN} ]]; then
    echo "Chybí PHP CLI nebo Composer." >&2
    exit 1
fi

cd "${APP_DIR}"
exec 9>"${LOCK_FILE}"
if ! flock -n 9; then
    echo "Jiný deploy právě probíhá." >&2
    exit 1
fi

touch "${LOG_FILE}"
chmod 640 "${LOG_FILE}"
exec > >(tee -a "${LOG_FILE}") 2>&1

cleanup() {
    local exit_code=$?
    [[ -n ${BACKUP_TEMP} && -f ${BACKUP_TEMP} ]] && rm -f "${BACKUP_TEMP}"
    if [[ ${MAINTENANCE} == true ]]; then
        "${PHP_BIN}" artisan up || true
    fi
    if (( exit_code != 0 )); then
        echo "Deploy selhal (řádek ${BASH_LINENO[0]}, kód ${exit_code}). Aplikace byla vrácena do provozu."
    fi
    exit "${exit_code}"
}
trap cleanup EXIT

env_value() {
    local key=$1
    sed -nE "s/^${key}=(.*)$/\1/p" .env | tail -n 1 | sed -E 's/^"(.*)"$/\1/'
}

env_set() {
    local key=$1 value=$2 raw=${3:-false}
    ENV_FILE="${APP_DIR}/.env" ENV_KEY="${key}" ENV_VALUE="${value}" ENV_RAW="${raw}" "${PHP_BIN}" -r '
        $file = getenv("ENV_FILE"); $key = getenv("ENV_KEY"); $value = getenv("ENV_VALUE");
        $contents = file_get_contents($file); $raw = getenv("ENV_RAW") === "true";
        $escaped = $raw ? $value : "\"" . addcslashes($value, "\\\"") . "\"";
        $line = $key . "=" . $escaped;
        $pattern = "/^" . preg_quote($key, "/") . "=.*$/m";
        $contents = preg_match($pattern, $contents)
            ? preg_replace($pattern, $line, $contents)
            : rtrim($contents) . PHP_EOL . $line . PHP_EOL;
        file_put_contents($file, $contents);
    '
}

echo
echo "Slepé Slunce deploy — $(date '+%Y-%m-%d %H:%M:%S %Z')"
echo "Projekt: ${APP_DIR}"

if [[ -d .git ]]; then
    if [[ -n $(git status --porcelain) ]]; then
        echo "Pracovní kopie obsahuje lokální změny. Deploy je z bezpečnostních důvodů zastaven." >&2
        exit 1
    fi
    [[ -n ${BRANCH} ]] || BRANCH=$(git branch --show-current)
    echo "Aktualizuji větev ${BRANCH}…"
    git fetch origin "${BRANCH}"
    git checkout "${BRANCH}"
    git pull --ff-only origin "${BRANCH}"
fi

if [[ ! -f .env ]]; then
    FIRST_INSTALL=true
    cp .env.example .env
    chmod 640 .env

    read -r -p "Databáze [slepe_slunce]: " DB_NAME
    DB_NAME=${DB_NAME:-slepe_slunce}
    read -r -p "Databázový uživatel [slepe_slunce]: " DB_USER
    DB_USER=${DB_USER:-slepe_slunce}
    if [[ ! ${DB_NAME} =~ ^[A-Za-z0-9_]+$ || ! ${DB_USER} =~ ^[A-Za-z0-9_]+$ ]]; then
        echo "Název databáze a uživatele smí obsahovat pouze písmena, číslice a podtržítko." >&2
        exit 1
    fi
    DB_PASSWORD=$(openssl rand -hex 24)

    if ! command -v mariadb >/dev/null 2>&1; then
        echo "Chybí klient MariaDB. Nainstalujte mariadb-client/server." >&2
        exit 1
    fi

    echo "Vytvářím databázi a samostatného uživatele…"
    mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

    env_set APP_ENV production
    env_set APP_DEBUG false true
    env_set APP_URL "${APP_URL:-https://slepeslunce.cz}"
    env_set DB_CONNECTION mysql
    env_set DB_HOST 127.0.0.1
    env_set DB_PORT 3306
    env_set DB_DATABASE "${DB_NAME}"
    env_set DB_USERNAME "${DB_USER}"
    env_set DB_PASSWORD "${DB_PASSWORD}"
    echo "Databáze ${DB_NAME} byla připravena. Heslo je bezpečně uložené pouze v .env."
fi

if [[ $(env_value DB_CONNECTION) == mysql ]]; then
    DB_DUMP_BIN=$(command -v mariadb-dump || command -v mysqldump || true)
    if [[ -n ${DB_DUMP_BIN} ]]; then
        mkdir -p "${BACKUP_DIR}"
        chmod 700 "${BACKUP_DIR}"
        BACKUP_FILE="${BACKUP_DIR}/database-$(date '+%Y%m%d-%H%M%S').sql.gz"
        BACKUP_TEMP="${BACKUP_FILE}.tmp"
        echo "Zálohuji databázi do ${BACKUP_FILE}…"
        MYSQL_PWD="$(env_value DB_PASSWORD)" "${DB_DUMP_BIN}" \
            --single-transaction --quick --lock-tables=false \
            --host="$(env_value DB_HOST)" --port="$(env_value DB_PORT)" \
            --user="$(env_value DB_USERNAME)" "$(env_value DB_DATABASE)" | gzip -9 >"${BACKUP_TEMP}"
        mv "${BACKUP_TEMP}" "${BACKUP_FILE}"
        BACKUP_TEMP=""
        chmod 600 "${BACKUP_FILE}"
    else
        echo "Upozornění: mariadb-dump/mysqldump není dostupný, automatická záloha byla přeskočena."
    fi
fi

if [[ -f artisan ]]; then
    "${PHP_BIN}" artisan down --retry=30 || true
    MAINTENANCE=true
fi

echo "Instaluji produkční PHP závislosti…"
COMPOSER_ALLOW_SUPERUSER=1 "${COMPOSER_BIN}" install \
    --no-dev --prefer-dist --no-interaction --optimize-autoloader

if ! grep -qE '^APP_KEY=base64:.+' .env; then
    "${PHP_BIN}" artisan key:generate --force
fi

mkdir -p storage/app/public storage/framework/{cache/data,sessions,testing,views} storage/logs bootstrap/cache
"${PHP_BIN}" artisan migrate --force
"${PHP_BIN}" artisan storage:link || true
"${PHP_BIN}" artisan app:generate-post-thumbnails
"${PHP_BIN}" artisan optimize:clear
"${PHP_BIN}" artisan optimize

echo "Nastavuji vlastnictví a oprávnění…"
chown -R root:"${APP_GROUP}" "${APP_DIR}"
find "${APP_DIR}" -type d -exec chmod 755 {} +
find "${APP_DIR}" -type f -exec chmod 644 {} +
chown -R "${APP_USER}":"${APP_GROUP}" storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} +
find storage bootstrap/cache -type f -exec chmod 664 {} +
chmod 640 .env
chmod 750 deploy.sh

PHP_PATH=$(readlink -f "${PHP_BIN}")
cat >"/etc/systemd/system/${QUEUE_SERVICE}" <<UNIT
[Unit]
Description=Slepe Slunce Laravel queue worker
After=network.target mariadb.service

[Service]
Type=simple
User=${APP_USER}
Group=${APP_GROUP}
WorkingDirectory=${APP_DIR}
ExecStart=${PHP_PATH} artisan queue:work database --sleep=3 --tries=3 --timeout=120 --max-time=3600
ExecReload=${PHP_PATH} artisan queue:restart
Restart=always
RestartSec=5
TimeoutStopSec=130
KillSignal=SIGTERM

[Install]
WantedBy=multi-user.target
UNIT

cat >"${CRON_FILE}" <<CRON
* * * * * ${APP_USER} cd ${APP_DIR} && ${PHP_PATH} artisan schedule:run >> ${APP_DIR}/storage/logs/scheduler.log 2>&1
CRON
chmod 644 "${CRON_FILE}"

systemctl daemon-reload
systemctl enable --now "${QUEUE_SERVICE}"
systemctl restart "${QUEUE_SERVICE}"

FPM_SERVICE=$(systemctl list-unit-files --type=service --no-legend 'php*-fpm.service' 2>/dev/null | awk 'NR==1 {print $1}')
if [[ -n ${FPM_SERVICE} ]]; then
    systemctl reload-or-restart "${FPM_SERVICE}"
fi

if [[ -f /etc/nginx/sites-enabled/slepe-slunce.cz ]]; then
    nginx -t
    systemctl reload nginx
fi

"${PHP_BIN}" artisan up
MAINTENANCE=false

echo "Kontroluji aplikaci…"
"${PHP_BIN}" artisan about
"${PHP_BIN}" artisan migrate:status --no-interaction
systemctl --no-pager --full status "${QUEUE_SERVICE}" | sed -n '1,12p'

if [[ ${FIRST_INSTALL} == true ]]; then
    echo
    echo "První instalace je hotová. Nyní vytvořte správce:"
    echo "  cd ${APP_DIR} && ${PHP_PATH} artisan app:create-admin"
fi

echo "Deploy byl úspěšně dokončen."

#!/bin/bash
set -e

MOODLE_CONFIG="/var/www/html/config.php"
INSTALL_LOCK="/var/moodledata/.installed"
STUDENT_ACCESS_VERSION_FILE="/var/moodledata/.student-access-version"
STUDENT_ACCESS_VERSION="2026100701"
DEMO_DATA_VERSION_FILE="/var/moodledata/.edtech-demo-data-version"
DEMO_DATA_VERSION="2026100701"
THEME_CACHE_VERSION_FILE="/var/moodledata/.edtech-theme-cache-version"
MOODLE_CLI="/var/www/html/admin/cli"

# Wait for database
echo "Waiting for database..."
until php -r "
\$conn = new mysqli('${MOODLE_DB_HOST}', '${MOODLE_DB_USER}', '${MOODLE_DB_PASSWORD}', '${MOODLE_DB_NAME}');
if (\$conn->connect_error) { exit(1); }
exit(0);
" 2>/dev/null; do
    sleep 2
done
echo "Database is ready."

# Generate config.php from environment on every start so proxy/domain changes
# are applied cleanly during redeploys.
echo "Creating config.php..."
UPDATE_NOTIFICATIONS_CONFIG=""
if [ "${MOODLE_DISABLE_UPDATE_NOTIFICATIONS:-false}" = "true" ]; then
    UPDATE_NOTIFICATIONS_CONFIG="\$CFG->disableupdatenotifications = true;"
fi

# Optional SMTP configuration. Keep credentials in environment variables so
# they never need to be committed to config.php or the repository.
SMTP_CONFIG=""
if [ -n "${MOODLE_SMTP_HOST:-}" ]; then
    escape_php_single_quote() {
        local value="$1"
        value=${value//\\/\\\\}
        value=${value//\'/\\\'}
        printf '%s' "$value"
    }

    SMTP_HOST_VALUE=$(escape_php_single_quote "${MOODLE_SMTP_HOST}")
    SMTP_PORT_VALUE=$(escape_php_single_quote "${MOODLE_SMTP_PORT:-2525}")
    SMTP_USER_VALUE=$(escape_php_single_quote "${MOODLE_SMTP_USER:-}")
    SMTP_PASSWORD_VALUE=$(escape_php_single_quote "${MOODLE_SMTP_PASSWORD:-}")
    SMTP_SECURITY_VALUE=$(escape_php_single_quote "${MOODLE_SMTP_SECURITY:-tls}")
    SMTP_FROM_VALUE=$(escape_php_single_quote "${MOODLE_EMAIL_FROM:-${MOODLE_ADMIN_EMAIL:-admin@example.com}}")

    SMTP_CONFIG=$(printf '%s\n' \
        "\$CFG->smtphosts = '${SMTP_HOST_VALUE}:${SMTP_PORT_VALUE}';" \
        "\$CFG->smtpuser = '${SMTP_USER_VALUE}';" \
        "\$CFG->smtppass = '${SMTP_PASSWORD_VALUE}';" \
        "\$CFG->smtpsecure = '${SMTP_SECURITY_VALUE}';" \
        "\$CFG->noreplyaddress = '${SMTP_FROM_VALUE}';")
fi

cat > "$MOODLE_CONFIG" <<PHPEOF
<?php
unset(\$CFG);
global \$CFG;
\$CFG = new stdClass();

\$CFG->dbtype    = 'mariadb';
\$CFG->dblibrary = 'native';
\$CFG->dbhost    = '${MOODLE_DB_HOST}';
\$CFG->dbname    = '${MOODLE_DB_NAME}';
\$CFG->dbuser    = '${MOODLE_DB_USER}';
\$CFG->dbpass    = '${MOODLE_DB_PASSWORD}';
\$CFG->prefix    = 'mdl_';

\$CFG->wwwroot   = '${MOODLE_WWWROOT:-http://localhost:8080}';
\$CFG->dataroot  = '/var/moodledata';
\$CFG->directorypermissions = 0777;
\$CFG->sslproxy = ${MOODLE_SSLPROXY:-false};
\$CFG->reverseproxy = ${MOODLE_REVERSEPROXY:-false};
\$CFG->theme = '${MOODLE_THEME:-edtech}';
${UPDATE_NOTIFICATIONS_CONFIG}
${SMTP_CONFIG}

\$CFG->admin = 'admin';

require_once(__DIR__ . '/lib/setup.php');
PHPEOF
chown www-data:www-data "$MOODLE_CONFIG"

# Run Moodle install only once (lock file in persistent volume)
if [ ! -f "$INSTALL_LOCK" ]; then
    echo "Running Moodle installation..."
    php "$MOODLE_CLI/install_database.php" \
        --lang=en \
        --adminuser="${MOODLE_ADMIN_USER:-admin}" \
        --adminpass="${MOODLE_ADMIN_PASSWORD}" \
        --adminemail="${MOODLE_ADMIN_EMAIL:-admin@example.com}" \
        --fullname="${MOODLE_SITE_NAME:-Moodle}" \
        --shortname="${MOODLE_SITE_SHORTNAME:-moodle}" \
        --agree-license
    touch "$INSTALL_LOCK"
    echo "Moodle installation complete."
else
    echo "Moodle already installed, skipping."
    echo "Checking Moodle upgrades..."
    php "$MOODLE_CLI/upgrade.php" --non-interactive
fi

# Synchronise the curated demo catalogue after installation. The versioned
# marker makes this safe across restarts while allowing future data migrations
# to run against an existing persistent production volume.
PREVIOUS_DEMO_DATA_VERSION=""
if [ -f "$DEMO_DATA_VERSION_FILE" ]; then
    PREVIOUS_DEMO_DATA_VERSION=$(cat "$DEMO_DATA_VERSION_FILE")
fi
if [ "$PREVIOUS_DEMO_DATA_VERSION" != "$DEMO_DATA_VERSION" ]; then
    echo "Demo data version changed (${PREVIOUS_DEMO_DATA_VERSION:-none} -> ${DEMO_DATA_VERSION}); synchronising courses..."
    php /usr/local/bin/seed-arabic-islamic-courses.php
    php /usr/local/bin/seed-course-content.php
    printf '%s\n' "$DEMO_DATA_VERSION" > "$DEMO_DATA_VERSION_FILE"
    chown www-data:www-data "$DEMO_DATA_VERSION_FILE"
fi

# Configure student account creation and course-level self-enrolment. The
# versioned marker allows this migration to repair existing production sites
# that were initialized before per-course enrolment was added.
PREVIOUS_STUDENT_ACCESS_VERSION=""
if [ -f "$STUDENT_ACCESS_VERSION_FILE" ]; then
    PREVIOUS_STUDENT_ACCESS_VERSION=$(cat "$STUDENT_ACCESS_VERSION_FILE")
fi
if [ "$PREVIOUS_STUDENT_ACCESS_VERSION" != "$STUDENT_ACCESS_VERSION" ]; then
    php /usr/local/bin/configure-student-access.php
    printf '%s\n' "$STUDENT_ACCESS_VERSION" > "$STUDENT_ACCESS_VERSION_FILE"
    chown www-data:www-data "$STUDENT_ACCESS_VERSION_FILE"
fi

# Moodle stores compiled theme CSS and other theme assets in moodledata. The
# volume survives image redeploys, so clear those caches when the deployed
# theme version changes. The version marker avoids purging caches on every
# ordinary container restart.
THEME_VERSION=$(sed -n 's/.*\$plugin->version[[:space:]]*=[[:space:]]*\([0-9][0-9]*\).*/\1/p' \
    /var/www/html/theme/edtech/version.php | head -n 1)
PREVIOUS_THEME_VERSION=""
if [ -f "$THEME_CACHE_VERSION_FILE" ]; then
    PREVIOUS_THEME_VERSION=$(cat "$THEME_CACHE_VERSION_FILE")
fi

if [ -n "$THEME_VERSION" ] && [ "$THEME_VERSION" != "$PREVIOUS_THEME_VERSION" ]; then
    echo "Theme version changed (${PREVIOUS_THEME_VERSION:-none} -> ${THEME_VERSION}); purging Moodle caches..."
    php "$MOODLE_CLI/purge_caches.php"
    printf '%s\n' "$THEME_VERSION" > "$THEME_CACHE_VERSION_FILE"
    chown www-data:www-data "$THEME_CACHE_VERSION_FILE"
fi

# Run Moodle cron every minute in the container. Running it in a loop avoids
# relying on a second daemon inside the Apache container and keeps failures
# visible in the container lifecycle.
(
    while true; do
        if ! /usr/local/bin/php "$MOODLE_CLI/cron.php" >/dev/null 2>&1; then
            echo "Moodle cron failed; it will be retried in 60 seconds." >&2
        fi
        sleep 60
    done
) &

exec "$@"

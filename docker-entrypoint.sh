#!/bin/bash
set -e

MOODLE_CONFIG="/var/www/html/config.php"
INSTALL_LOCK="/var/moodledata/.installed"

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

\$CFG->admin = 'admin';

require_once(__DIR__ . '/lib/setup.php');
PHPEOF
chown www-data:www-data "$MOODLE_CONFIG"

# Run Moodle install only once (lock file in persistent volume)
if [ ! -f "$INSTALL_LOCK" ]; then
    echo "Running Moodle installation..."
    php /var/www/html/admin/cli/install_database.php \
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
fi

# Setup cron
echo "*/1 * * * * www-data /usr/local/bin/php /var/www/html/admin/cli/cron.php >/dev/null 2>&1" > /etc/cron.d/moodle
chmod 0644 /etc/cron.d/moodle
cron

exec "$@"

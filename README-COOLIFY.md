# Moodle on Coolify

Use `docker-compose.coolify.yml` for the Coolify deployment.

## Coolify setup

1. Create a new `Docker Compose` application in Coolify.
2. Point it to this repository.
3. Set the compose file path to `docker-compose.coolify.yml`.
4. Add a domain in Coolify for the `moodle` service.
5. Set these environment variables in Coolify:

```env
MYSQL_ROOT_PASSWORD=change-this
MYSQL_DB_NAME=moodle
MYSQL_DB_USER=moodle
MYSQL_DB_PASSWORD=change-this
MOODLE_ADMIN_USER=admin
MOODLE_ADMIN_PASSWORD=change-this
MOODLE_ADMIN_EMAIL=admin@example.com
MOODLE_SITE_NAME=Ranees EdTech
MOODLE_SITE_SHORTNAME=edtech
MOODLE_WWWROOT=https://your-domain.com
MOODLE_SSLPROXY=true
MOODLE_REVERSEPROXY=true
```

## Important notes

- `MOODLE_WWWROOT` must exactly match the public HTTPS URL configured in Coolify.
- The database data is persisted in `moodle_db_data`.
- Moodle uploaded files and generated content are persisted in `moodledata`.
- The first deployment runs Moodle installation automatically.
- Later redeploys reuse the existing database and `moodledata` volume.

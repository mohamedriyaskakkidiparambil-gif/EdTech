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
MOODLE_THEME=edtech
MOODLE_SSLPROXY=true
MOODLE_REVERSEPROXY=true
MOODLE_SMTP_HOST=sandbox.smtp.mailtrap.io
MOODLE_SMTP_PORT=2525
MOODLE_SMTP_USER=your_mailtrap_username
MOODLE_SMTP_PASSWORD=your_mailtrap_password
MOODLE_SMTP_SECURITY=tls
MOODLE_EMAIL_FROM=noreply@example.com
```

## Important notes

- `MOODLE_WWWROOT` must exactly match the public HTTPS URL configured in Coolify.
- `MOODLE_THEME=edtech` makes the deployed site use the custom EdTech theme by default.
- The database data is persisted in `moodle_db_data`.
- For development email testing, use Mailtrap Sandbox SMTP credentials. Store the username and password as Coolify secrets, not in Git.
- Moodle uploaded files and generated content are persisted in `moodledata`.
- The first deployment runs Moodle installation automatically.
- Later redeploys reuse the existing database and `moodledata` volume. The container checks for Moodle upgrades on startup and purges compiled Moodle/theme caches automatically when the EdTech theme version changes.
- The container also runs a versioned, idempotent demo-data sync for the stable course short names. It creates missing English technical and Arabic Islamic courses, reuses known legacy JavaScript/AI course records when present, adds the local lesson pages, quizzes, PDF guides, video embeds, section unlock rules, and Self enrolment instances without deleting unrelated courses or users.

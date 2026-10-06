# Arabic Islamic Courses

The project includes an idempotent Moodle seeder for five introductory Arabic
Islamic courses.

## Seed the courses

Build the image if needed, then run:

```bash
docker compose build moodle
docker compose up -d moodle
docker compose exec moodle php /usr/local/bin/seed-arabic-islamic-courses.php
```

The seeder creates or reuses the `الدراسات الإسلامية` category and adds:

- القرآن الكريم وتدبره
- الحديث النبوي الشريف
- الفقه الإسلامي وأحكام العبادات
- السيرة النبوية
- الأخلاق والقيم الإسلامية

All five courses are visible and have Moodle course language `ar`. The script
uses stable short names, so running it again updates the existing records
instead of creating duplicates.

## Full course content

The container startup also runs `seed-course-content.php` for all ten catalogue
courses. It adds or updates:

- five structured Arabic lessons per Islamic course and eight structured English lessons per technical course;
- lesson focus, practical activity, reflection prompt, and a lesson-specific true/false quick check;
- an embedded introductory video and downloadable course guide PDF for every course;
- automatic completion tracking, where each next section is locked until the previous section's quick check is completed.

The migration is versioned in `docker-entrypoint.sh`, so an existing production
volume receives the same content on the next image deployment without creating
duplicate activities.

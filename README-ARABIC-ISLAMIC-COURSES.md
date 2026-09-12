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

# Demo Courses for Production

This repo now includes a bulk upload file at `demo-courses.csv`.

## What it does

- Creates 6 visible demo courses.
- Works with Moodle's built-in **Upload courses** tool.
- Helps the EdTech theme show real course cards on the front page and catalog.

## Important limitation

The CSV creates courses, but it does **not** create course categories.

If you want richer category cards on the EdTech home page, first create categories in Moodle such as:

1. Web Development
2. Artificial Intelligence
3. Graphic Design
4. Business Management
5. Language Learning
6. Photography

Then either:

- edit `demo-courses.csv` and replace the `category` value `1` with the real category IDs, or
- import the file into category `1` first and move the courses later from Moodle admin.

## Upload in Moodle

According to Moodle's official Upload courses docs, `shortname`, `fullname`, and `category` are required when creating courses, and categories must already exist. See:

- [Upload courses (MoodleDocs)](https://docs.moodle.org/500/en/Upload_courses)
- [Course categories (MoodleDocs)](https://docs.moodle.org/502/en/Add/edit_course_categories)

## Production steps

1. Log in as admin.
2. Go to `Site administration -> Courses -> Create new category` and add any categories you want.
3. Go to `Site administration -> Courses -> Upload courses`.
4. Upload `demo-courses.csv`.
5. Use `Preview` first.
6. Confirm the upload.
7. Go to `Site administration -> Development -> Purge all caches`.

## Notes

- The current file uses category `1` so it can import immediately on a fresh site.
- Featured courses in the EdTech theme are pulled from real Moodle courses, ordered by newest first.
- Course images are optional; without them Moodle will use its default course image.

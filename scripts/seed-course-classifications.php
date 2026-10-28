<?php

/**
 * Seed the language-specific Moodle categories used by the primary menu.
 *
 * The migration is idempotent. It creates four native Moodle categories and
 * assigns the curated courses to a certification or diploma classification
 * without duplicating course records.
 */

define('CLI_SCRIPT', true);

$moodleroot = is_file(__DIR__ . '/../config.php') ? dirname(__DIR__) : '/var/www/html';
require($moodleroot . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB;

$categories = [
    'certifications-en' => [
        'name' => 'Certifications',
        'description' => 'Practical English courses grouped into certification learning tracks.',
    ],
    'diploma-courses-en' => [
        'name' => 'Diploma Courses',
        'description' => 'Advanced English courses grouped into diploma learning tracks.',
    ],
    'certifications-ar' => [
        'name' => 'الشهادات',
        'description' => 'دورات عربية مجمعة ضمن مسارات الشهادات.',
    ],
    'diploma-courses-ar' => [
        'name' => 'دورات الدبلوم',
        'description' => 'دورات عربية مجمعة ضمن مسارات الدبلوم.',
    ],
];

$categoryrecords = [];
foreach ($categories as $idnumber => $categoryinfo) {
    $category = $DB->get_record('course_categories', ['idnumber' => $idnumber], '*', IGNORE_MISSING);
    if (!$category) {
        $category = core_course_category::create((object)[
            'name' => $categoryinfo['name'],
            'parent' => 0,
            'idnumber' => $idnumber,
            'description' => $categoryinfo['description'],
            'descriptionformat' => FORMAT_HTML,
            'visible' => 1,
        ]);
        echo "Created category: {$categoryinfo['name']} (id {$category->id})\n";
    } else {
        $DB->update_record('course_categories', (object)[
            'id' => $category->id,
            'name' => $categoryinfo['name'],
            'description' => $categoryinfo['description'],
            'descriptionformat' => FORMAT_HTML,
            'visible' => 1,
        ]);
        echo "Using category: {$categoryinfo['name']} (id {$category->id})\n";
    }
    $categoryrecords[$idnumber] = $category;
}

$assignments = [
    'certifications-en' => ['TECH-JS', 'TECH-PHP', 'TECH-PYTHON'],
    'diploma-courses-en' => ['TECH-DOCKER', 'TECH-AI'],
    'certifications-ar' => ['ISLAMIC-QURAN', 'ISLAMIC-HADITH', 'ISLAMIC-FIQH'],
    'diploma-courses-ar' => ['ISLAMIC-SEERAH', 'ISLAMIC-ETHICS'],
];

foreach ($assignments as $categoryidnumber => $shortnames) {
    $category = $categoryrecords[$categoryidnumber];
    foreach ($shortnames as $shortname) {
        $course = $DB->get_record('course', ['shortname' => $shortname], 'id,fullname,category', IGNORE_MISSING);
        if (!$course) {
            echo "Skipped missing course: {$shortname}\n";
            continue;
        }
        if ((int)$course->category !== (int)$category->id) {
            $DB->set_field('course', 'category', $category->id, ['id' => $course->id]);
        }
        echo "Assigned {$course->fullname} to {$category->name}\n";
    }
}

if (function_exists('purge_all_caches')) {
    purge_all_caches();
    echo "Purged Moodle caches.\n";
}

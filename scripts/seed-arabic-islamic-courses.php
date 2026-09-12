<?php

/**
 * Seed the Arabic Islamic Studies category and its introductory courses.
 *
 * This script is intentionally idempotent. It creates missing records and
 * normalises the language/category fields for records with our stable course
 * short names, but never creates duplicates.
 */

define('CLI_SCRIPT', true);

$moodleroot = is_file(__DIR__ . '/../config.php') ? dirname(__DIR__) : '/var/www/html';
require($moodleroot . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB;

$categorydata = (object)[
    'name' => 'الدراسات الإسلامية',
    'parent' => 0,
    'idnumber' => 'islamic-studies-ar',
    'description' => 'دورات إسلامية باللغة العربية.',
    'descriptionformat' => FORMAT_HTML,
];

$category = $DB->get_record(
    'course_categories',
    ['idnumber' => $categorydata->idnumber],
    '*',
    IGNORE_MISSING
);

if (!$category) {
    $category = core_course_category::create($categorydata);
    echo "Created category: {$categorydata->name} (id {$category->id})\n";
} else {
    echo "Using category: {$category->name} (id {$category->id})\n";
}

$courses = [
    [
        'shortname' => 'ISLAMIC-QURAN',
        'fullname' => 'القرآن الكريم وتدبره',
        'summary' => 'دورة تمهيدية لفهم سور القرآن الكريم وتدبر معانيه واستخلاص الهدايات العملية من آياته.',
    ],
    [
        'shortname' => 'ISLAMIC-HADITH',
        'fullname' => 'الحديث النبوي الشريف',
        'summary' => 'تعرّف على مكانة السنة النبوية ومبادئ فهم الحديث الشريف وتطبيقه في الحياة اليومية.',
    ],
    [
        'shortname' => 'ISLAMIC-FIQH',
        'fullname' => 'الفقه الإسلامي وأحكام العبادات',
        'summary' => 'دراسة مبسطة لأهم أحكام الطهارة والصلاة والصيام والزكاة وفق أصول الفقه الإسلامي.',
    ],
    [
        'shortname' => 'ISLAMIC-SEERAH',
        'fullname' => 'السيرة النبوية',
        'summary' => 'رحلة تعليمية في حياة النبي محمد صلى الله عليه وسلم وأهم دروس السيرة والقيم المستفادة منها.',
    ],
    [
        'shortname' => 'ISLAMIC-ETHICS',
        'fullname' => 'الأخلاق والقيم الإسلامية',
        'summary' => 'استكشف مبادئ الصدق والأمانة والرحمة والتعاون وبناء الشخصية المسلمة في ضوء القرآن والسنة.',
    ],
];

foreach ($courses as $courseinfo) {
    $existing = $DB->get_record(
        'course',
        ['shortname' => $courseinfo['shortname']],
        '*',
        IGNORE_MISSING
    );

    if ($existing) {
        $updates = (object)[
            'id' => $existing->id,
            'category' => $category->id,
            'lang' => 'ar',
        ];
        $DB->update_record('course', $updates);
        echo "Updated course: {$existing->fullname} (id {$existing->id})\n";
        continue;
    }

    $data = (object)[
        'category' => $category->id,
        'fullname' => $courseinfo['fullname'],
        'shortname' => $courseinfo['shortname'],
        'idnumber' => $courseinfo['shortname'],
        'summary' => $courseinfo['summary'],
        'summaryformat' => FORMAT_HTML,
        'format' => 'topics',
        'numsections' => 5,
        'visible' => 1,
        'lang' => 'ar',
        'showgrades' => 1,
        'enablecompletion' => 1,
    ];

    $created = create_course($data);
    echo "Created course: {$created->fullname} (id {$created->id})\n";
}

if (function_exists('purge_all_caches')) {
    purge_all_caches();
    echo "Purged Moodle caches.\n";
}

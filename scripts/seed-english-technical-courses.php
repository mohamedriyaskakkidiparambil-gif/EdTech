<?php

/**
 * Seed the five stable English technical courses used by the curated catalogue.
 *
 * The production database may have been initialized from demo-courses.csv,
 * whose legacy short names are intentionally left untouched. This migration
 * creates the stable courses when they are missing, so the content seeder can
 * populate the same curriculum in every environment without deleting older
 * unrelated courses.
 */

define('CLI_SCRIPT', true);

$moodleroot = is_file(__DIR__ . '/../config.php') ? dirname(__DIR__) : '/var/www/html';
require($moodleroot . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB;

$categorydata = (object)[
    'name' => 'Technology',
    'parent' => 0,
    'idnumber' => 'technology-en',
    'description' => 'Technical courses covering programming, development tools, infrastructure, and artificial intelligence.',
    'descriptionformat' => FORMAT_HTML,
];

$category = $DB->get_record(
    'course_categories',
    ['idnumber' => $categorydata->idnumber],
    '*',
    IGNORE_MISSING
);

if (!$category) {
    // Older imports may have the same visible name without a stable
    // idnumber. Reuse that category before creating another one.
    $category = $DB->get_record('course_categories', [
        'name' => $categorydata->name,
        'parent' => 0,
    ], '*', IGNORE_MISSING);
    if ($category) {
        $DB->set_field('course_categories', 'idnumber', $categorydata->idnumber, ['id' => $category->id]);
        echo "Normalised category: {$category->name} (id {$category->id})\n";
    } else {
        $category = core_course_category::create($categorydata);
        echo "Created category: {$categorydata->name} (id {$category->id})\n";
    }
} else {
    echo "Using category: {$category->name} (id {$category->id})\n";
}

$courses = [
    [
        'shortname' => 'TECH-JS',
        'fullname' => 'JavaScript Fundamentals',
        'summary' => 'Learn modern JavaScript fundamentals, including syntax, functions, objects, asynchronous programming, and browser development.',
    ],
    [
        'shortname' => 'TECH-PHP',
        'fullname' => 'PHP Web Development',
        'summary' => 'Build dynamic web applications with PHP, covering language fundamentals, forms, databases, sessions, APIs, and secure development.',
    ],
    [
        'shortname' => 'TECH-PYTHON',
        'fullname' => 'Python Programming',
        'summary' => 'Learn Python through syntax, collections, functions, modules, testing, automation, and a practical application project.',
    ],
    [
        'shortname' => 'TECH-DOCKER',
        'fullname' => 'Docker Essentials',
        'summary' => 'Understand containers, images, volumes, networking, Dockerfiles, Compose, security, and deployable application stacks.',
    ],
    [
        'shortname' => 'TECH-AI',
        'fullname' => 'Artificial Intelligence Fundamentals',
        'summary' => 'Explore artificial intelligence concepts, machine learning foundations, responsible AI, and practical applications.',
    ],
];

$legacyshortnames = [
    'TECH-JS' => ['web-js-bootcamp'],
    'TECH-AI' => ['ai-practical'],
];

foreach ($courses as $courseinfo) {
    $existing = $DB->get_record(
        'course',
        ['shortname' => $courseinfo['shortname']],
        '*',
        IGNORE_MISSING
    );

    if (!$existing) {
        foreach ($legacyshortnames[$courseinfo['shortname']] ?? [] as $legacyshortname) {
            $existing = $DB->get_record(
                'course',
                ['shortname' => $legacyshortname],
                '*',
                IGNORE_MISSING
            );
            if ($existing) {
                echo "Reusing legacy course {$legacyshortname} as {$courseinfo['shortname']}\n";
                break;
            }
        }
    }

    if ($existing) {
        $updates = (object)[
            'id' => $existing->id,
            'category' => $category->id,
            'shortname' => $courseinfo['shortname'],
            'idnumber' => $courseinfo['shortname'],
            'fullname' => $courseinfo['fullname'],
            'summary' => $courseinfo['summary'],
            'summaryformat' => FORMAT_HTML,
            'format' => 'topics',
            'visible' => 1,
            'lang' => 'en',
            'showgrades' => 1,
            'enablecompletion' => 1,
        ];
        $DB->update_record('course', $updates);
        echo "Updated course: {$courseinfo['fullname']} (id {$existing->id})\n";
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
        'numsections' => 8,
        'visible' => 1,
        'lang' => 'en',
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

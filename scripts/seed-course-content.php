<?php
/**
 * Add meaningful section names and starter lesson pages to the demo courses.
 *
 * The script is idempotent: curated names and previously created demo pages
 * are updated consistently, while unrelated course activities are preserved.
 */

define('CLI_SCRIPT', true);

$moodleroot = is_file(__DIR__ . '/../config.php') ? dirname(__DIR__) : '/var/www/html';
require($moodleroot . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/resourcelib.php');
require_once($CFG->dirroot . '/mod/resource/lib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

global $DB, $USER;

// The Moodle module API performs capability checks. Run this maintenance
// script as the configured site administrator without creating a session.
$USER = $DB->get_record('user', ['username' => $CFG->admin], '*', MUST_EXIST);

$courses = [
    'TECH-JS' => [
        'JavaScript Foundations',
        'Variables and Data Types',
        'Control Flow and Functions',
        'Arrays and Objects',
        'DOM and Browser Events',
        'Asynchronous JavaScript',
        'APIs and Error Handling',
        'Project: Interactive Web App',
    ],
    'TECH-PHP' => [
        'PHP Foundations',
        'Forms and Validation',
        'Functions and Arrays',
        'Object-Oriented PHP',
        'MySQL and PDO',
        'Sessions and Authentication',
        'Building APIs',
        'Project: CRUD Web App',
    ],
    'TECH-PYTHON' => [
        'Python Foundations',
        'Collections and Comprehensions',
        'Functions and Modules',
        'Files and Exceptions',
        'Object-Oriented Python',
        'Testing and Debugging',
        'Automation and APIs',
        'Project: Python Application',
    ],
    'TECH-DOCKER' => [
        'Containers and Images',
        'Writing Dockerfiles',
        'Volumes and Persistent Data',
        'Container Networking',
        'Docker Compose',
        'Environment Variables and Secrets',
        'Security and Image Optimization',
        'Project: Deployable Application Stack',
    ],
    'TECH-AI' => [
        'AI and Machine Learning Concepts',
        'Data Preparation',
        'Supervised Learning',
        'Neural Networks',
        'Natural Language Processing',
        'Computer Vision',
        'Responsible AI',
        'Project: AI Workflow',
    ],
    'ISLAMIC-QURAN' => [
        'مقدمة في تدبر القرآن',
        'سورة الفاتحة',
        'فهم السياق والمعاني',
        'الهدايات العملية',
        'مشروع التدبر الأسبوعي',
    ],
    'ISLAMIC-HADITH' => [
        'مدخل إلى السنة النبوية',
        'أنواع الحديث ومصطلحاته',
        'فهم الحديث وتوثيقه',
        'تطبيق السنة في الحياة',
        'مشروع حديث عملي',
    ],
    'ISLAMIC-FIQH' => [
        'مقدمة في الفقه والعبادة',
        'الطهارة',
        'الصلاة',
        'الصيام والزكاة',
        'تطبيقات فقهية يومية',
    ],
    'ISLAMIC-SEERAH' => [
        'مدخل إلى السيرة النبوية',
        'مكة قبل البعثة',
        'الدعوة والهجرة',
        'بناء المجتمع في المدينة',
        'دروس وقيم من السيرة',
    ],
    'ISLAMIC-ETHICS' => [
        'مفهوم الأخلاق في الإسلام',
        'الصدق والأمانة',
        'الرحمة والتعاون',
        'ضبط النفس والمسؤولية',
        'مشروع القيم في الحياة',
    ],
];

// Public introductory videos for the technical demo courses. Islamic course
// videos are intentionally left for instructor-provided material.
$videos = [
    'TECH-JS' => [
        'title' => 'JavaScript course for beginners',
        'embed' => 'https://www.youtube-nocookie.com/embed/W6NZfCO5SIk',
        'watch' => 'https://www.youtube.com/watch?v=W6NZfCO5SIk',
    ],
    'TECH-PHP' => [
        'title' => 'PHP programming language full course',
        'embed' => 'https://www.youtube-nocookie.com/embed/OK_JCtrrv-c',
        'watch' => 'https://www.youtube.com/watch?v=OK_JCtrrv-c',
    ],
    'TECH-PYTHON' => [
        'title' => 'Python full course for beginners',
        'embed' => 'https://www.youtube-nocookie.com/embed/rfscVS0vtbw',
        'watch' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
    ],
    'TECH-DOCKER' => [
        'title' => 'Docker tutorial for beginners',
        'embed' => 'https://www.youtube-nocookie.com/embed/fqMOX6JJhGo',
        'watch' => 'https://www.youtube.com/watch?v=fqMOX6JJhGo',
    ],
    'TECH-AI' => [
        'title' => 'What is a neural network?',
        'embed' => 'https://www.youtube-nocookie.com/embed/aircAruvnKk',
        'watch' => 'https://www.youtube.com/watch?v=aircAruvnKk',
    ],
];

function demo_content(string $shortname, string $coursefullname, string $sectiontitle): string {
    $course = htmlspecialchars($coursefullname, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($sectiontitle, ENT_QUOTES, 'UTF-8');
    $arabic = strpos($shortname, 'ISLAMIC-') === 0;

    if ($arabic) {
        return "<h3>{$title}</h3>"
            . "<p>يتناول هذا القسم موضوع <strong>{$title}</strong> ضمن دورة {$course}، مع التركيز على الفهم والتطبيق العملي.</p>"
            . '<h4>مخرجات التعلم</h4>'
            . '<ul><li>فهم المفاهيم الأساسية المرتبطة بالموضوع.</li>'
            . '<li>ربط المعرفة بالمواقف اليومية بصورة عملية.</li>'
            . '<li>كتابة تأمل قصير أو مثال تطبيقي بعد إكمال الدرس.</li></ul>'
            . '<h4>نشاط مقترح</h4>'
            . '<p>اقرأ المادة، دوّن أهم الفوائد، ثم شارك فائدة واحدة أو سؤالاً في منتدى الدورة.</p>';
    }

    return "<h3>{$title}</h3>"
        . "<p>This lesson introduces <strong>{$title}</strong> as part of {$course}, with a focus on practical understanding and application.</p>"
        . '<h4>Learning outcomes</h4>'
        . '<ul><li>Explain the key ideas covered in this section.</li>'
        . '<li>Apply the concepts in a small practical example.</li>'
        . '<li>Reflect on one question or challenge related to the lesson.</li></ul>'
        . '<h4>Suggested activity</h4>'
        . '<p>Read the lesson, complete a short practice task, and share one takeaway or question in the course forum.</p>';
}

function demo_video_content(string $shortname, array $video): string {
    $title = htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8');
    $embed = htmlspecialchars($video['embed'], ENT_QUOTES, 'UTF-8');
    $watch = htmlspecialchars($video['watch'], ENT_QUOTES, 'UTF-8');

    return '<h3>' . $title . '</h3>'
        . '<p>Watch this introductory lesson before working through the course sections.</p>'
        . '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;">'
        . '<iframe src="' . $embed . '" title="' . $title . '" '
        . 'style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" '
        . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
        . 'allowfullscreen></iframe></div>'
        . '<p><a href="' . $watch . '" target="_blank" rel="noopener">Open this video on YouTube</a></p>'
        . '<p><strong>Reflection:</strong> Write down two ideas from the video that you want to practise in this course.</p>';
}

function pdf_ascii(string $text): string {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($converted !== false) {
        $text = $converted;
    }
    return preg_replace('/[^\x20-\x7E]/', '', $text);
}

function pdf_escape(string $text): string {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function demo_pdf(string $shortname, string $coursefullname, array $sectiontitles): string {
    $lines = [
        'Course guide: ' . pdf_ascii($shortname),
        'Course: ' . pdf_ascii($coursefullname),
        '',
        'Use this guide alongside the Moodle lessons and course forum.',
        'Course sections:',
    ];

    foreach ($sectiontitles as $index => $sectiontitle) {
        $clean = pdf_ascii($sectiontitle);
        $lines[] = ($index + 1) . '. ' . ($clean !== '' ? $clean : 'Lesson ' . ($index + 1));
    }

    $lines[] = '';
    $lines[] = 'Suggested study routine:';
    $lines[] = '1. Read the lesson page and watch the featured video when available.';
    $lines[] = '2. Complete a small practice task and record your questions.';
    $lines[] = '3. Share one useful takeaway in the course forum.';

    // Keep the PDF dependency-free: Moodle can generate this basic handout
    // without installing a second PDF library in the Docker image.
    $stream = "BT\n/F1 18 Tf\n50 760 Td\n";
    foreach ($lines as $index => $line) {
        $size = $index === 0 ? 18 : 11;
        if ($index !== 0) {
            $stream .= "0 -18 Td\n";
        }
        $stream .= '/' . 'F1 ' . $size . " Tf\n(" . pdf_escape($line) . ") Tj\n";
    }
    $stream .= "ET\n";

    $objects = [];
    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
    $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] '
        . '/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>';
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $number => $object) {
        $offsets[$number + 1] = strlen($pdf);
        $pdf .= ($number + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($number = 1; $number <= count($objects); $number++) {
        $pdf .= sprintf('%010d 00000 n \n', $offsets[$number]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n"
        . "startxref\n" . $xref . "\n%%EOF\n";

    return $pdf;
}

function upsert_demo_page(int $courseid, int $sectionnumber, string $idnumber, string $name, string $content): array {
    global $DB;

    $existingcm = $DB->get_record('course_modules', [
        'course' => $courseid,
        'idnumber' => $idnumber,
    ], '*', IGNORE_MISSING);

    if ($existingcm) {
        $page = $DB->get_record('page', ['id' => $existingcm->instance], '*', IGNORE_MISSING);
        if ($page) {
            $page->name = $name;
            $page->content = $content;
            $page->contentformat = FORMAT_HTML;
            $page->timemodified = time();
            $DB->update_record('page', $page);
            set_coursemodule_name($existingcm->id, $name);
            return ['created' => 0, 'updated' => 1];
        }
    }

    $moduleinfo = (object)[
        'modulename' => 'page',
        'course' => $courseid,
        'section' => $sectionnumber,
        'visible' => 1,
        'visibleoncoursepage' => 1,
        'name' => $name,
        'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0],
        'content' => $content,
        'contentformat' => FORMAT_HTML,
        'display' => RESOURCELIB_DISPLAY_OPEN,
        'printintro' => 0,
        'printlastmodified' => 1,
        'popupwidth' => 0,
        'popupheight' => 0,
        'cmidnumber' => $idnumber,
        'groupmode' => 0,
        'groupingid' => 0,
        'completion' => COMPLETION_TRACKING_NONE,
        'completionview' => 0,
        'completionexpected' => 0,
        'completiongradeitemnumber' => '',
        'tags' => [],
    ];
    create_module($moduleinfo);
    return ['created' => 1, 'updated' => 0];
}

function upsert_demo_pdf(int $courseid, int $sectionnumber, string $idnumber, string $name, string $intro, string $pdf): array {
    global $DB;

    $existingcm = $DB->get_record('course_modules', [
        'course' => $courseid,
        'idnumber' => $idnumber,
    ], '*', IGNORE_MISSING);

    if ($existingcm) {
        $resource = $DB->get_record('resource', ['id' => $existingcm->instance], '*', IGNORE_MISSING);
        $cmid = $existingcm->id;
        if ($resource) {
            $resource->name = $name;
            $resource->intro = $intro;
            $resource->introformat = FORMAT_HTML;
            $resource->timemodified = time();
            $DB->update_record('resource', $resource);
            set_coursemodule_name($cmid, $name);
        }
        $created = 0;
        $updated = 1;
    } else {
        $moduleinfo = (object)[
            'modulename' => 'resource',
            'course' => $courseid,
            'section' => $sectionnumber,
            'visible' => 1,
            'visibleoncoursepage' => 1,
            'name' => $name,
            'introeditor' => ['text' => $intro, 'format' => FORMAT_HTML, 'itemid' => 0],
            'display' => RESOURCELIB_DISPLAY_EMBED,
            'printintro' => 0,
            'popupwidth' => 0,
            'popupheight' => 0,
            'showdescription' => 0,
            'filterfiles' => 0,
            'cmidnumber' => $idnumber,
            'groupmode' => 0,
            'groupingid' => 0,
            'completion' => COMPLETION_TRACKING_NONE,
            'completionview' => 0,
            'completionexpected' => 0,
            'completiongradeitemnumber' => '',
            'tags' => [],
        ];
        $createdcm = create_module($moduleinfo);
        $cmid = $createdcm->coursemodule;
        $created = 1;
        $updated = 0;
    }

    $fs = get_file_storage();
    $context = context_module::instance($cmid);
    foreach ($fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'id', false) as $file) {
        $file->delete();
    }
    $fs->create_file_from_string([
        'component' => 'mod_resource',
        'filearea' => 'content',
        'contextid' => $context->id,
        'itemid' => 0,
        'filepath' => '/',
        'filename' => 'course-guide.pdf',
    ], $pdf);

    return ['created' => $created, 'updated' => $updated];
}

function demo_question_id(int $categoryid, string $idnumber): ?int {
    global $DB;

    $sql = 'SELECT qv.questionid
              FROM {question_bank_entries} qbe
              JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
             WHERE qbe.questioncategoryid = ?
               AND qbe.idnumber = ?
          ORDER BY qv.version DESC';
    $questionid = $DB->get_field_sql($sql, [$categoryid, $idnumber], IGNORE_MISSING);
    return $questionid ? (int)$questionid : null;
}

function create_demo_question(int $courseid, int $sectionnumber, string $sectiontitle, string $shortname): int {
    global $CFG, $DB, $USER;

    $context = context_course::instance($courseid);
    $category = question_get_top_category($context->id, true);
    $idnumber = 'demo-question-' . strtolower($shortname) . '-' . $sectionnumber;
    $existingid = demo_question_id($category->id, $idnumber);
    if ($existingid) {
        return $existingid;
    }

    $question = (object)[
        'id' => 0,
        'qtype' => 'truefalse',
        'createdby' => $USER->id,
        'modifiedby' => $USER->id,
        'category' => $category->id,
        'contextid' => $context->id,
        'parent' => 0,
        'idnumber' => null,
        'status' => 0,
    ];
    $fromform = (object)[
        'category' => (string)$category->id,
        'name' => 'Quick check: ' . $sectiontitle,
        'idnumber' => $idnumber,
        'questiontext' => [
            'text' => 'After studying "' . $sectiontitle . '", the learner should be able to explain one key idea from this section.',
            'format' => FORMAT_HTML,
        ],
        'generalfeedback' => [
            'text' => 'Review the lesson page and try the question again if needed.',
            'format' => FORMAT_HTML,
        ],
        'defaultmark' => 1,
        'penalty' => 0,
        'correctanswer' => 1,
        'feedbacktrue' => [
            'text' => 'Correct. Continue to the next section.',
            'format' => FORMAT_HTML,
        ],
        'feedbackfalse' => [
            'text' => 'Review the section content, then try again.',
            'format' => FORMAT_HTML,
        ],
        'status' => 0,
    ];

    $saved = question_bank::get_qtype('truefalse')->save_question($question, $fromform);
    return (int)$saved->id;
}

function upsert_demo_quiz(int $courseid, int $sectionnumber, string $sectiontitle, string $shortname): array {
    global $CFG, $DB;

    $idnumber = 'demo-quiz-' . strtolower($shortname) . '-' . $sectionnumber;
    $existingcm = $DB->get_record('course_modules', [
        'course' => $courseid,
        'idnumber' => $idnumber,
    ], '*', IGNORE_MISSING);

    if ($existingcm) {
        $cmid = (int)$existingcm->id;
        $quiz = $DB->get_record('quiz', ['id' => $existingcm->instance], '*', MUST_EXIST);
        $created = 0;
        $updated = 1;
    } else {
        $moduleinfo = (object)[
            'modulename' => 'quiz',
            'course' => $courseid,
            'section' => $sectionnumber,
            'visible' => 1,
            'visibleoncoursepage' => 1,
            'name' => 'Quick check: ' . $sectiontitle,
            'introeditor' => [
                'text' => '<p>Complete this quick check to unlock the next section.</p>',
                'format' => FORMAT_HTML,
                'itemid' => 0,
            ],
            'timeopen' => 0,
            'timeclose' => 0,
            'preferredbehaviour' => 'deferredfeedback',
            'canredoquestions' => 0,
            'attempts' => 0,
            'attemptonlast' => 0,
            'grademethod' => QUIZ_GRADEHIGHEST,
            'decimalpoints' => 2,
            'questiondecimalpoints' => -1,
            'questionsperpage' => 1,
            'shuffleanswers' => 1,
            'sumgrades' => 1,
            'grade' => 1,
            'timelimit' => 0,
            'overduehandling' => 'autosubmit',
            'graceperiod' => 86400,
            'quizpassword' => '',
            'subnet' => '',
            'browsersecurity' => '',
            'delay1' => 0,
            'delay2' => 0,
            'showuserpicture' => 0,
            'showblocks' => 0,
            'navmethod' => QUIZ_NAVMETHOD_FREE,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => 0,
            'completionusegrade' => 0,
            'completionpassgrade' => 0,
            'completionattemptsexhausted' => 0,
            'completionminattemptsenabled' => 1,
            'completionminattempts' => 1,
            'completionunlocked' => 1,
            'cmidnumber' => $idnumber,
            'groupmode' => 0,
            'groupingid' => 0,
            'completionexpected' => 0,
            'completiongradeitemnumber' => '',
            'tags' => [],
        ];
        $createdcm = create_module($moduleinfo);
        $cmid = (int)$createdcm->coursemodule;
        $quiz = $DB->get_record('quiz', ['id' => $createdcm->instance], '*', MUST_EXIST);
        $created = 1;
        $updated = 0;
    }

    // Keep the completion rule explicit even if this activity already existed.
    $quiz->completionminattempts = 1;
    $quiz->completionattemptsexhausted = 0;
    $DB->update_record('quiz', $quiz);
    $DB->set_field('course_modules', 'completion', COMPLETION_TRACKING_AUTOMATIC, ['id' => $cmid]);
    $DB->set_field('course_modules', 'completionpassgrade', 0, ['id' => $cmid]);

    if (!$DB->record_exists('quiz_slots', ['quizid' => $quiz->id])) {
        $questionid = create_demo_question($courseid, $sectionnumber, $sectiontitle, $shortname);
        $quiz->cmid = $cmid;
        quiz_add_quiz_question($questionid, $quiz, 1, 1);
    }

    return ['cmid' => $cmid, 'created' => $created, 'updated' => $updated];
}

function lock_demo_sections(int $courseid, array $sectionsbynumber, array $quizcms): int {
    global $DB;

    $locked = 0;
    foreach ($quizcms as $sectionnumber => $quizcmid) {
        $nextsectionnumber = $sectionnumber + 1;
        if (!isset($sectionsbynumber[$nextsectionnumber])) {
            continue;
        }

        $availability = json_encode(
            \core_availability\tree::get_root_json([
                \availability_completion\condition::get_json($quizcmid, COMPLETION_COMPLETE),
            ], '&')
        );
        $section = $sectionsbynumber[$nextsectionnumber];
        if ((string)$section->availability !== $availability) {
            $DB->set_field('course_sections', 'availability', $availability, ['id' => $section->id]);
            $locked++;
        }
    }
    return $locked;
}

$createdpages = 0;
$updatedpages = 0;
$renamedsections = 0;
$createdvideos = 0;
$updatedvideos = 0;
$createdpdfs = 0;
$updatedpdfs = 0;
$createdquizzes = 0;
$updatedquizzes = 0;
$lockedsections = 0;

foreach ($courses as $shortname => $sectiontitles) {
    $course = $DB->get_record('course', ['shortname' => $shortname], '*', IGNORE_MISSING);
    if (!$course) {
        echo "Skipped missing course: {$shortname}\n";
        continue;
    }

    $sections = $DB->get_records('course_sections', ['course' => $course->id], 'section');
    $sectionsbynumber = [];
    foreach ($sections as $sectionrecord) {
        $sectionsbynumber[(int)$sectionrecord->section] = $sectionrecord;
    }

    // The general section is reserved for announcements/discussions.
    if (isset($sectionsbynumber[0]) && trim((string)$sectionsbynumber[0]->name) !== '') {
        $DB->set_field('course_sections', 'name', null, ['id' => $sectionsbynumber[0]->id]);
        $renamedsections++;
    }

    foreach ($sectiontitles as $index => $sectiontitle) {
        $sectionnumber = $index + 1;
        if (!isset($sectionsbynumber[$sectionnumber])) {
            echo "Skipped missing section {$shortname}:{$sectionnumber}\n";
            continue;
        }

        $section = $sectionsbynumber[$sectionnumber];
        if ((string)$section->name !== $sectiontitle) {
            $DB->set_field('course_sections', 'name', $sectiontitle, ['id' => $section->id]);
            $renamedsections++;
        }

        $idnumber = 'demo-content-' . strtolower($shortname) . '-' . $sectionnumber;
        $content = demo_content($shortname, $course->fullname, $sectiontitle);
        $result = upsert_demo_page($course->id, $sectionnumber, $idnumber, $sectiontitle, $content);
        $createdpages += $result['created'];
        $updatedpages += $result['updated'];
    }

    if (isset($videos[$shortname]) && isset($sectionsbynumber[1])) {
        $video = $videos[$shortname];
        $videoname = 'Featured video: ' . $video['title'];
        $result = upsert_demo_page(
            $course->id,
            1,
            'demo-video-' . strtolower($shortname),
            $videoname,
            demo_video_content($shortname, $video)
        );
        $createdvideos += $result['created'];
        $updatedvideos += $result['updated'];
    }

    $pdfname = 'Course guide PDF';
    $pdfintro = '<p>Download this course guide for a printable overview of the lessons and study routine.</p>';
    $result = upsert_demo_pdf(
        $course->id,
        1,
        'demo-pdf-' . strtolower($shortname),
        $pdfname,
        $pdfintro,
        demo_pdf($shortname, $course->fullname, $sectiontitles)
    );
    $createdpdfs += $result['created'];
    $updatedpdfs += $result['updated'];

    $quizcms = [];
    foreach ($sectiontitles as $index => $sectiontitle) {
        $sectionnumber = $index + 1;
        if (!isset($sectionsbynumber[$sectionnumber])) {
            continue;
        }
        $result = upsert_demo_quiz($course->id, $sectionnumber, $sectiontitle, $shortname);
        $quizcms[$sectionnumber] = $result['cmid'];
        $createdquizzes += $result['created'];
        $updatedquizzes += $result['updated'];
    }
    $lockedsections += lock_demo_sections($course->id, $sectionsbynumber, $quizcms);

    rebuild_course_cache($course->id, true);
    echo "Seeded {$shortname}: " . count($sectiontitles) . " sections\n";
}

purge_all_caches();
echo "Renamed sections: {$renamedsections}; created pages: {$createdpages}; updated pages: {$updatedpages}; "
    . "created videos: {$createdvideos}; updated videos: {$updatedvideos}; "
    . "created PDFs: {$createdpdfs}; updated PDFs: {$updatedpdfs}; "
    . "created quizzes: {$createdquizzes}; updated quizzes: {$updatedquizzes}; "
    . "locked sections: {$lockedsections}\n";

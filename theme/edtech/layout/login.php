<?php
// theme/edtech/layout/login.php
// Custom full-page login layout — split panel design.
// Left: brand panel with features. Right: Moodle login form.

defined('MOODLE_INTERNAL') || die();

global $CFG, $SITE;

// Required — Moodle checks output contains id="maincontent"
$maincontent = $OUTPUT->main_content();

// Primary nav for language switcher
$primarymenu     = new \core\navigation\output\primary($PAGE);
$primarymenudata = $primarymenu->export_for_template($OUTPUT);

// Build language list for custom switcher
$langlist = [];
$currentlang    = current_language();
$installedlangs = get_string_manager()->get_list_of_translations();
if (count($installedlangs) > 1) {
    foreach ($installedlangs as $code => $name) {
        $url = new moodle_url($PAGE->url, ['lang' => $code]);
        $langlist[] = [
            'code'   => $code,
            'name'   => $name,
            'url'    => $url->out(false),
            'active' => ($code === $currentlang),
        ];
    }
}

$templatecontext = [
    'output'          => $OUTPUT,
    'bodyattributes'  => $OUTPUT->body_attributes(['edt-login-page']),
    'sitename'        => format_string($SITE->fullname),
    'wwwroot'         => $CFG->wwwroot,
    'maincontent'     => $maincontent,

    // Language
    'langmenu'        => $primarymenudata['lang'] ?? false,
    'langlist'        => $langlist,
    'haslangmenu'     => count($langlist) > 1,
    'currentlang'     => $installedlangs[$currentlang] ?? strtoupper($currentlang),
    'isrtl'           => right_to_left(),

    // Left panel features
    'features' => [
        ['icon' => 'fa-play-circle',        'text' => get_string('feat_interactive_title', 'theme_edtech')],
        ['icon' => 'fa-medal',              'text' => get_string('feat_certified_title', 'theme_edtech')],
        ['icon' => 'fa-globe',              'text' => get_string('feat_multilang_title', 'theme_edtech')],
        ['icon' => 'fa-headset',            'text' => get_string('feat_support_title', 'theme_edtech')],
    ],

    // Stats
    'stats' => [
        ['num' => get_config('theme_edtech', 'stat_courses')     ?: '15K+',  'lbl' => get_string('stat_courses', 'theme_edtech')],
        ['num' => get_config('theme_edtech', 'stat_instructors') ?: '800+',  'lbl' => get_string('stat_instructors', 'theme_edtech')],
        ['num' => get_config('theme_edtech', 'stat_students')    ?: '120K+', 'lbl' => get_string('stat_students', 'theme_edtech')],
    ],

    // Links
    'homeurl'         => (new moodle_url('/'))->out(false),
    'forgoturl'       => (new moodle_url('/login/forgot_password.php'))->out(false),
    'signupurl'       => (new moodle_url('/login/signup.php'))->out(false),
    'signupenabled'   => !empty($CFG->registerauth),
];

echo $OUTPUT->render_from_template('theme_edtech/login', $templatecontext);

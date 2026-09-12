<?php
// theme/edtech/lib.php
// ─────────────────────────────────────────────────────────────────────────
// SCSS pipeline hooks + frontpage context builder.
// ─────────────────────────────────────────────────────────────────────────

defined('MOODLE_INTERNAL') || die();

// ── SCSS pipeline ─────────────────────────────────────────────────────────

/**
 * Injected BEFORE Bootstrap compiles — variable overrides ($primary, fonts…).
 */
function theme_edtech_get_pre_scss($theme) {
    return file_get_contents(__DIR__ . '/scss/pre.scss');
}

/**
 * Main SCSS content: Boost's default preset (imports Bootstrap + Moodle SCSS).
 * Must be defined — without it Moodle skips the entire compilation pipeline.
 */
function theme_edtech_get_main_scss_content($theme) {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
}

/**
 * Injected AFTER full compilation — custom rules on top of everything.
 * Do NOT use @import url() here — it crashes ScssPhp; load fonts in head.mustache.
 */
function theme_edtech_get_extra_scss($theme) {
    return file_get_contents(__DIR__ . '/scss/post.scss');
}

// ── Shared navbar context ─────────────────────────────────────────────────

/**
 * Builds the shared navbar context used by ALL layout files.
 *
 * Single source of truth — call this from every layout PHP file so that
 * adding a new navbar variable only requires editing lib.php, not every
 * layout file individually.
 *
 * Used by:
 *   layout/frontpage.php  (merged into full frontpage context)
 *   layout/drawers.php    (merged into drawers context)
 *   layout/login.php      (merged into login context)
 */
function theme_edtech_get_navbar_context($OUTPUT, $PAGE) {
    global $CFG, $SITE;

    // Primary navigation data (usermenu, langmenu, moremenu)
    $primarymenu     = new \core\navigation\output\primary($PAGE);
    $primarymenudata = $primarymenu->export_for_template($OUTPUT);

    // Language switcher list
    $currentlang    = current_language();
    $installedlangs = get_string_manager()->get_list_of_translations();
    $langlist = [];
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

    // ── Active nav item detection ─────────────────────────────────────────
    // Uses $PAGE->pagetype and URL path — more reliable than URL string matching.
    $pagetype = $PAGE->pagetype;               // e.g. 'site-index', 'course-index'
    $urlpath  = $PAGE->url->get_path();        // e.g. '/course/index.php', '/my/'

    // Normalise: strip query string, trailing slash
    $urlpath  = strtok($urlpath, '?');

    $nav_active_home      = ($pagetype === 'site-index');
    $nav_active_courses   = (strpos($urlpath, '/course/') === 0 && !$nav_active_home);
    $nav_active_dashboard = (strpos($urlpath, '/my/') === 0 || $pagetype === 'my-index');
    $nav_active_admin     = (strpos($urlpath, '/admin/') === 0 || strpos($urlpath, '/report/') === 0);

    // Role detection — is_siteadmin() is a core Moodle function, always available.
    $is_admin = is_siteadmin();

    return [
        'sitename'        => format_string($SITE->fullname),
        'wwwroot'         => $CFG->wwwroot,
        'loginurl'        => (new moodle_url('/login/'))->out(false),
        'loggedin'        => isloggedin() && !isguestuser(),
        'isrtl'           => right_to_left(),
        'is_admin'        => $is_admin,

        // Primary nav
        'primarymoremenu' => $primarymenudata['moremenu'] ?? false,
        'usermenu'        => $primarymenudata['user']     ?? false,
        'langmenu'        => $primarymenudata['lang']     ?? false,
        'mobileprimarynav'=> $primarymenudata['mobileprimarynav'] ?? false,

        // Language switcher
        'langlist'        => $langlist,
        'haslangmenu'     => count($langlist) > 1,
        'currentlang'     => $installedlangs[$currentlang] ?? strtoupper($currentlang),
        'currentlangcode' => $currentlang,

        // Active nav item flags (used in navbar.mustache)
        'nav_active_home'      => $nav_active_home,
        'nav_active_courses'   => $nav_active_courses,
        'nav_active_dashboard' => $nav_active_dashboard,
        'nav_active_admin'     => $nav_active_admin,

        // Topbar contact info
        'contact_phone'   => get_config('theme_edtech', 'contact_phone') ?: '+974 4412 3456',
        'contact_email'   => get_config('theme_edtech', 'contact_email') ?: 'support@edtech.qa',
    ];
}

/**
 * Returns BOTH navbar + footer context merged — convenience wrapper.
 * Call this from any layout file instead of calling the two functions separately.
 *
 * Usage in layout/*.php:
 *   $sharedctx = theme_edtech_get_shared_context($OUTPUT, $PAGE, $CFG);
 *   $templatecontext = array_merge($sharedctx, [...page-specific vars]);
 */
function theme_edtech_get_shared_context($OUTPUT, $PAGE, $CFG) {
    return array_merge(
        theme_edtech_get_navbar_context($OUTPUT, $PAGE),
        theme_edtech_get_footer_context($CFG)
    );
}

/**
 * Builds the shared footer context used by all layout files.
 *
 * Single source of truth for footer data.
 */
function theme_edtech_get_footer_context($CFG) {
    return [
        'footer' => [
            'about'      => get_string('footer_about', 'theme_edtech'),
            'copyright'  => get_string('footer_copyright', 'theme_edtech'),
            'courseurl'  => (new moodle_url('/course/'))->out(false),
            'dashurl'    => (new moodle_url('/my/'))->out(false),
            'blogurl'    => (new moodle_url('/blog/'))->out(false),
            'privacyurl' => (new moodle_url('/admin/tool/policy/'))->out(false),
        ],
        'socials' => [
            ['icon' => 'fa-brands fa-facebook-f',  'url' => get_config('theme_edtech', 'social_facebook')  ?: '#', 'label' => 'Facebook'],
            ['icon' => 'fa-brands fa-x-twitter',   'url' => get_config('theme_edtech', 'social_twitter')   ?: '#', 'label' => 'Twitter'],
            ['icon' => 'fa-brands fa-instagram',   'url' => get_config('theme_edtech', 'social_instagram')  ?: '#', 'label' => 'Instagram'],
            ['icon' => 'fa-brands fa-linkedin-in', 'url' => get_config('theme_edtech', 'social_linkedin')   ?: '#', 'label' => 'LinkedIn'],
            ['icon' => 'fa-brands fa-youtube',     'url' => get_config('theme_edtech', 'social_youtube')    ?: '#', 'label' => 'YouTube'],
        ],
    ];
}

// ── Frontpage context builder ──────────────────────────────────────────────

/**
 * Return the two-letter language family used for content matching.
 */
function theme_edtech_language_family($language) {
    $language = strtolower(str_replace('-', '_', trim((string)$language)));
    return substr($language, 0, 2);
}

/**
 * Return whether text contains Arabic script.
 */
function theme_edtech_contains_arabic($text) {
    return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', (string)$text) === 1;
}

/**
 * Match content to the selected language. Arabic content must contain Arabic
 * script; non-Arabic content is treated as English for the English view.
 */
function theme_edtech_text_matches_current_language($text) {
    $is_arabic_view = theme_edtech_language_family(current_language()) === 'ar';
    $has_arabic = theme_edtech_contains_arabic($text);

    return $is_arabic_view ? $has_arabic : !$has_arabic;
}

/**
 * Returns whether a course belongs in the currently selected language view.
 *
 * Moodle stores the course language in course.lang. When it is empty, infer
 * the course language from its script so manually created courses do not
 * appear in the opposite language view.
 */
function theme_edtech_course_matches_current_language($course) {
    $courselang = trim((string)($course->lang ?? ''));

    if ($courselang !== '') {
        if (theme_edtech_language_family($courselang) !== theme_edtech_language_family(current_language())) {
            return false;
        }

        // Do not allow an incorrectly tagged English record into the Arabic
        // view. English records may still mention Arabic terms or names.
        if (theme_edtech_language_family(current_language()) === 'ar') {
            $taggedcontent = implode(' ', array_filter([
                (string)($course->fullname ?? ''),
                (string)($course->shortname ?? ''),
                (string)($course->summary ?? ''),
            ]));
            return theme_edtech_contains_arabic($taggedcontent);
        }

        return true;
    }

    $coursecontent = implode(' ', array_filter([
        (string)($course->fullname ?? ''),
        (string)($course->shortname ?? ''),
        (string)($course->summary ?? ''),
    ]));

    return theme_edtech_text_matches_current_language($coursecontent);
}

/**
 * Return whether a category name belongs in the current language view.
 */
function theme_edtech_category_matches_current_language($category) {
    $name = is_object($category) ? ($category->name ?? '') : $category;
    return theme_edtech_text_matches_current_language($name);
}

/**
 * Count visible courses in a category that belong in the current language.
 */
function theme_edtech_category_language_course_count($categoryid) {
    global $DB;

    $courses = $DB->get_records_sql(
        'SELECT id, fullname, shortname, summary, lang
           FROM {course}
          WHERE category = ? AND id <> ? AND visible = 1',
        [$categoryid, SITEID]
    );

    $count = 0;
    foreach ($courses as $course) {
        if (theme_edtech_course_matches_current_language($course)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Count all visible courses in the current language, optionally by category.
 */
function theme_edtech_language_course_count($categoryid = null) {
    global $DB;

    $params = [SITEID];
    $categorycondition = '';
    if ($categoryid !== null) {
        $categorycondition = ' AND category = ?';
        $params[] = $categoryid;
    }

    $courses = $DB->get_records_sql(
        'SELECT id, fullname, shortname, summary, lang
           FROM {course}
          WHERE id <> ? AND visible = 1' . $categorycondition,
        $params
    );

    $count = 0;
    foreach ($courses as $course) {
        if (theme_edtech_course_matches_current_language($course)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Builds the complete context array for theme_edtech/frontpage.
 * Called from layout/frontpage.php.
 *
 * Data flow:
 *   frontpage.php (layout)
 *     → theme_edtech_build_frontpage_context()   [this function, in lib.php]
 *       → queries Moodle DB for categories + courses
 *       → returns plain PHP array
 *     → $OUTPUT->render_from_template('theme_edtech/frontpage', $context)
 *       → Mustache renders frontpage.mustache
 *         → {{> theme_edtech/sections/hero }}  etc.
 */
function theme_edtech_build_frontpage_context($OUTPUT, $PAGE) {
    global $CFG, $SITE, $USER, $DB;

    // ── Hero stats (configurable via settings, fall back to defaults) ──────
    $stats = [
        ['num' => get_config('theme_edtech', 'stat_courses')     ?: '15K+',  'lbl' => get_string('stat_courses', 'theme_edtech')],
        ['num' => get_config('theme_edtech', 'stat_instructors') ?: '800+',  'lbl' => get_string('stat_instructors', 'theme_edtech')],
        ['num' => get_config('theme_edtech', 'stat_students')    ?: '120K+', 'lbl' => get_string('stat_students', 'theme_edtech')],
        ['num' => get_config('theme_edtech', 'stat_rating')      ?: '4.9★',  'lbl' => get_string('stat_rating', 'theme_edtech')],
    ];

    // ── Course categories (top-level only, max 6) ─────────────────────────
    $categories = [];
    try {
        $topcategory = core_course_category::top();
        $children    = $topcategory->get_children();
        $caticons    = ['fa-code', 'fa-chart-line', 'fa-palette', 'fa-language', 'fa-camera', 'fa-microchip'];
        $i = 0;
        foreach ($children as $cat) {
            if (!theme_edtech_category_matches_current_language($cat)) {
                continue;
            }
            $coursecount = theme_edtech_category_language_course_count($cat->id);
            if (!$coursecount) {
                continue;
            }
            $categories[] = [
                'id'    => $cat->id,
                'name'  => format_string($cat->name),
                'count' => $coursecount . ' ' . get_string('courses'),
                'icon'  => $caticons[$i % count($caticons)],
                'url'   => (new moodle_url('/course/index.php', ['categoryid' => $cat->id]))->out(false),
            ];
            $i++;
            if (count($categories) >= 6) {
                break;
            }
        }
    } catch (Exception $e) {
        // Silently fail — categories are non-critical
    }

    // ── Featured courses (max 6, ordered by time created desc) ────────────
    $courses = [];
    try {
        // Fetch the full visible list before slicing so a newer Arabic course
        // cannot displace English courses from the English featured section.
        $courselist = get_courses('all', 'c.timecreated DESC', 'c.*', 0, 0);
        // Remove site course (id=1)
        unset($courselist[SITEID]);
        foreach ($courselist as $course) {
            if ($course->id == SITEID) continue;
            if (isset($course->visible) && !$course->visible) continue;
            if (!theme_edtech_course_matches_current_language($course)) continue;
            if (count($courses) >= 6) break;

            $courseobj  = new core_course_list_element($course);
            $courseurl  = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
            $enrolurl   = (new moodle_url('/enrol/index.php', ['id' => $course->id]))->out(false);

            // Course image
            $imageurl = '';
            foreach ($courseobj->get_course_overviewfiles() as $file) {
                $imageurl = moodle_url::make_pluginfile_url(
                    $file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), null,
                    $file->get_filepath(), $file->get_filename()
                )->out(false);
                break; // first image only
            }
            if (!$imageurl) {
                $imageurl = $OUTPUT->image_url('course_defaultimage', 'moodle')->out(false);
            }

            // Category name
            $catname = '';
            if ($course->category) {
                try {
                    $cat     = core_course_category::get($course->category, IGNORE_MISSING);
                    $catname = $cat ? format_string($cat->name) : '';
                } catch (Exception $e) {}
            }

            // Summary (plain, max 120 chars)
            $summary = strip_tags(format_text($course->summary, $course->summaryformat));
            if (core_text::strlen($summary) > 120) {
                $summary = core_text::substr($summary, 0, 120) . '…';
            }

            $courses[] = [
                'id'       => $course->id,
                'title'    => format_string($course->fullname),
                'category' => $catname,
                'summary'  => $summary,
                'image'    => $imageurl,
                'url'      => $courseurl,
                'enrolurl' => $enrolurl,
                'tag'      => get_string('featured', 'theme_edtech'),
            ];
        }
    } catch (Exception $e) {
        // Silently fail — courses are non-critical
    }

    // ── Features (Why Choose Us) ──────────────────────────────────────────
    $features = [
        ['icon' => 'fa-book-open-reader', 'title' => get_string('feat_interactive_title', 'theme_edtech'), 'text' => get_string('feat_interactive_text', 'theme_edtech'), 'gold' => false],
        ['icon' => 'fa-medal',            'title' => get_string('feat_certified_title', 'theme_edtech'),   'text' => get_string('feat_certified_text', 'theme_edtech'),   'gold' => true],
        ['icon' => 'fa-globe',            'title' => get_string('feat_multilang_title', 'theme_edtech'),   'text' => get_string('feat_multilang_text', 'theme_edtech'),   'gold' => false],
        ['icon' => 'fa-headset',          'title' => get_string('feat_support_title', 'theme_edtech'),     'text' => get_string('feat_support_text', 'theme_edtech'),     'gold' => false],
    ];

    // ── Testimonials ──────────────────────────────────────────────────────
    $testimonials = [
        ['text' => get_string('testi1_text', 'theme_edtech'), 'name' => get_string('testi1_name', 'theme_edtech'), 'role' => get_string('testi1_role', 'theme_edtech'), 'avatar' => 'https://i.pravatar.cc/80?img=47'],
        ['text' => get_string('testi2_text', 'theme_edtech'), 'name' => get_string('testi2_name', 'theme_edtech'), 'role' => get_string('testi2_role', 'theme_edtech'), 'avatar' => 'https://i.pravatar.cc/80?img=12'],
        ['text' => get_string('testi3_text', 'theme_edtech'), 'name' => get_string('testi3_name', 'theme_edtech'), 'role' => get_string('testi3_role', 'theme_edtech'), 'avatar' => 'https://i.pravatar.cc/80?img=25'],
    ];

    // ── Assemble full context ─────────────────────────────────────────────
    // One call — navbar + footer defined once in lib.php, used everywhere.
    return array_merge(theme_edtech_get_shared_context($OUTPUT, $PAGE, $CFG), [
        // Moodle standard
        'output'         => $OUTPUT,
        'bodyattributes' => $OUTPUT->body_attributes(),
        'courseurl'      => (new moodle_url('/course/'))->out(false),

        // Hero
        'hero' => [
            'eyebrow'      => get_string('hero_eyebrow', 'theme_edtech'),
            'title'        => get_string('hero_title', 'theme_edtech'),
            'title_accent' => get_string('hero_title_accent', 'theme_edtech'),
            'subtitle'     => get_string('hero_subtitle', 'theme_edtech'),
            'cta1_text'    => get_string('hero_cta1', 'theme_edtech'),
            'cta1_url'     => (new moodle_url('/course/'))->out(false),
            'cta2_text'    => get_string('hero_cta2', 'theme_edtech'),
            'cta2_url'     => '#',
            'stats'        => $stats,
        ],

        // Frontpage sections
        'categories'    => $categories,
        'hascategories' => !empty($categories),
        'courses'       => $courses,
        'hascourses'    => !empty($courses),
        'features'      => $features,
        'testimonials'  => $testimonials,

        // CTA band
        'cta' => [
            'title'    => get_string('cta_title', 'theme_edtech'),
            'subtitle' => get_string('cta_subtitle', 'theme_edtech'),
            'btn1'     => get_string('cta_btn1', 'theme_edtech'),
            'btn1_url' => '#',
            'btn2'     => get_string('cta_btn2', 'theme_edtech'),
            'btn2_url' => '#',
            'badge'    => get_string('cta_badge', 'theme_edtech'),
        ],
    ]);
}

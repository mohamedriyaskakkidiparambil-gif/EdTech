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
    require_once($CFG->libdir . '/authlib.php');

    // Primary navigation data (usermenu, langmenu, moremenu)
    $primarymenu     = new \core\navigation\output\primary($PAGE);
    $primarymenudata = $primarymenu->export_for_template($OUTPUT);

    // Language switcher list
    $currentlang    = current_language();
    $installedlangs = get_string_manager()->get_list_of_translations();
    $langlist = [];
    if (count($installedlangs) > 1) {
        $iscoursecategorypage = $PAGE->url->get_path() === '/course/index.php'
            && $PAGE->url->get_param('categoryid', null) !== null;
        $iscourseviewpage = $PAGE->url->get_path() === '/course/view.php'
            && $PAGE->url->get_param('id', null) !== null;
        $iscourseenrolpage = $PAGE->url->get_path() === '/enrol/index.php'
            && $PAGE->url->get_param('id', null) !== null;
        foreach ($installedlangs as $code => $name) {
            $url = new moodle_url($PAGE->url, ['lang' => $code]);
            if (($iscourseviewpage || $iscourseenrolpage) && $code !== $currentlang) {
                // Course detail and enrolment pages are language-specific. If
                // the user switches language, take them to the target
                // language's catalogue instead of keeping the same course.
                $targetcategoryid = theme_edtech_single_language_category_id($code);
                $url = new moodle_url('/course/index.php', ['lang' => $code]);
                if ($targetcategoryid !== null) {
                    $url->param('categoryid', $targetcategoryid);
                }
            } else if ($iscoursecategorypage) {
                $targetcategoryid = theme_edtech_single_language_category_id($code);
                if ($targetcategoryid !== null) {
                    $url->param('categoryid', $targetcategoryid);
                }
            }
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
        'signupurl'       => (new moodle_url('/login/signup.php'))->out(false),
        'signupenabled'   => signup_is_enabled(),
        'loggedin'        => isloggedin() && !isguestuser(),
        'isrtl'           => right_to_left(),
        'is_admin'        => $is_admin,

        // Native Moodle category pages used by the classification links.
        'certificationsurl' => theme_edtech_classification_category_url('certifications'),
        'diplomacoursesurl' => theme_edtech_classification_category_url('diploma-courses'),

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
 * Resolve a language-specific classification category into a Moodle URL.
 */
function theme_edtech_classification_category_url($classification) {
    global $DB;

    $language = current_language() === 'ar' ? 'ar' : 'en';
    $category = $DB->get_record(
        'course_categories',
        ['idnumber' => $classification . '-' . $language],
        'id',
        IGNORE_MISSING
    );

    if (!$category) {
        return (new moodle_url('/course/'))->out(false);
    }

    return (new moodle_url('/course/index.php', ['categoryid' => $category->id]))->out(false);
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
 * Match content to a language. Arabic content must contain Arabic script;
 * non-Arabic content is treated as English for the English view.
 */
function theme_edtech_text_matches_language($text, $language) {
    $is_arabic_view = theme_edtech_language_family($language) === 'ar';
    $has_arabic = theme_edtech_contains_arabic($text);

    return $is_arabic_view ? $has_arabic : !$has_arabic;
}

/**
 * Match content to the currently selected language.
 */
function theme_edtech_text_matches_current_language($text) {
    return theme_edtech_text_matches_language($text, current_language());
}

/**
 * Returns whether a course belongs in the requested language view.
 *
 * Moodle stores the course language in course.lang. When it is empty, infer
 * the course language from its script so manually created courses do not
 * appear in the opposite language view.
 */
function theme_edtech_course_matches_language($course, $language) {
    $courselang = trim((string)($course->lang ?? ''));

    if ($courselang !== '') {
        if (theme_edtech_language_family($courselang) !== theme_edtech_language_family($language)) {
            return false;
        }

        // Do not allow an incorrectly tagged English record into the Arabic
        // view. English records may still mention Arabic terms or names.
        if (theme_edtech_language_family($language) === 'ar') {
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

    return theme_edtech_text_matches_language($coursecontent, $language);
}

/**
 * Returns whether a course belongs in the currently selected language view.
 */
function theme_edtech_course_matches_current_language($course) {
    return theme_edtech_course_matches_language($course, current_language());
}

/**
 * Return whether a category name belongs in the current language view.
 */
function theme_edtech_category_matches_current_language($category) {
    $name = is_object($category) ? ($category->name ?? '') : $category;
    return theme_edtech_text_matches_current_language($name);
}

/**
 * Return whether a category name belongs to a requested language.
 */
function theme_edtech_category_matches_language($category, $language) {
    $name = is_object($category) ? ($category->name ?? '') : $category;
    return theme_edtech_text_matches_language($name, $language);
}

/**
 * Count visible courses in a category that belong to a requested language.
 */
function theme_edtech_category_language_course_count_for_language($categoryid, $language) {
    global $DB;

    $courses = $DB->get_records_sql(
        'SELECT id, fullname, shortname, summary, lang
           FROM {course}
          WHERE category = ? AND id <> ? AND visible = 1',
        [$categoryid, SITEID]
    );

    $count = 0;
    foreach ($courses as $course) {
        if (theme_edtech_course_matches_language($course, $language)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Count visible courses in a category that belong in the current language.
 */
function theme_edtech_category_language_course_count($categoryid) {
    return theme_edtech_category_language_course_count_for_language($categoryid, current_language());
}

/**
 * Return the only populated top-level category for a language, if there is
 * exactly one. This keeps category redirects and language switching aligned.
 */
function theme_edtech_single_language_category_id($language) {
    $rootcategory = \core_course_category::user_top();
    $matchingids = [];

    foreach ($rootcategory->get_children() as $category) {
        if (!theme_edtech_category_matches_language($category, $language)) {
            continue;
        }
        if (!theme_edtech_category_language_course_count_for_language($category->id, $language)) {
            continue;
        }
        $matchingids[] = (int)$category->id;
        if (count($matchingids) > 1) {
            return null;
        }
    }

    return $matchingids[0] ?? null;
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
 * Returns a configured theme value, or false when no value has been saved.
 * Keeping the distinction between false and an empty string lets the slider
 * fall back from an empty Arabic field to its English equivalent.
 */
function theme_edtech_get_slider_setting($name) {
    $value = get_config('theme_edtech', $name);
    return $value === false ? false : trim((string)$value);
}

/**
 * Returns the uploaded image URL for one homepage slider slot.
 */
function theme_edtech_get_slider_image_url($slot) {
    $fs = get_file_storage();
    $context = context_system::instance();
    $files = $fs->get_area_files(
        $context->id,
        'theme_edtech',
        'slideimage' . $slot,
        0,
        'itemid, filepath, filename',
        false
    );

    foreach ($files as $file) {
        return moodle_url::make_pluginfile_url(
            $context->id,
            'theme_edtech',
            'slideimage' . $slot,
            0,
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    return '';
}

/**
 * Returns the built-in bilingual defaults for a slider slot.
 */
function theme_edtech_get_slider_defaults($slot, $lang) {
    $defaultkeys = [
        1 => [
            'eyebrow' => 'hero_eyebrow',
            'title'   => 'hero_title',
            'accent'  => 'hero_title_accent',
            'subtitle'=> 'hero_subtitle',
            'cta1'    => 'hero_cta1',
            'cta2'    => 'hero_cta2',
        ],
        2 => [
            'eyebrow' => 'slider_default_2_eyebrow',
            'title'   => 'slider_default_2_title',
            'accent'  => 'slider_default_2_accent',
            'subtitle'=> 'slider_default_2_subtitle',
            'cta1'    => 'slider_default_2_cta1',
            'cta2'    => 'slider_default_2_cta2',
        ],
        3 => [
            'eyebrow' => 'slider_default_3_eyebrow',
            'title'   => 'slider_default_3_title',
            'accent'  => 'slider_default_3_accent',
            'subtitle'=> 'slider_default_3_subtitle',
            'cta1'    => 'slider_default_3_cta1',
            'cta2'    => 'slider_default_3_cta2',
        ],
    ];

    $keys = $defaultkeys[$slot];
    return [
        'eyebrow'  => get_string($keys['eyebrow'], 'theme_edtech', null, $lang),
        'title'    => get_string($keys['title'], 'theme_edtech', null, $lang),
        'accent'   => get_string($keys['accent'], 'theme_edtech', null, $lang),
        'subtitle' => get_string($keys['subtitle'], 'theme_edtech', null, $lang),
        'cta1'     => get_string($keys['cta1'], 'theme_edtech', null, $lang),
        'cta2'     => get_string($keys['cta2'], 'theme_edtech', null, $lang),
        'cta1_url' => '/course/',
        'cta2_url' => '#',
    ];
}

/**
 * Builds the three configurable homepage hero slides.
 */
function theme_edtech_get_frontpage_slides($stats) {
    $currentlang = current_language();
    $languageorder = $currentlang === 'ar' ? ['ar', 'en'] : ['en', 'ar'];
    $fields = ['eyebrow', 'title', 'accent', 'subtitle', 'cta1', 'cta2', 'cta1_url', 'cta2_url'];
    $slides = [];
    $enabledslots = [];

    for ($slot = 1; $slot <= 3; $slot++) {
        $enabled = theme_edtech_get_slider_setting("slider_{$slot}_enabled");
        if ($enabled !== false && !$enabled) {
            continue;
        }
        $enabledslots[] = $slot;
    }

    // Never leave the homepage without a hero. If an administrator disables
    // every slot, keep the original hero as a single-slide fallback.
    if (empty($enabledslots)) {
        $enabledslots = [1];
    }

    foreach ($enabledslots as $slot) {
        $defaults = [
            'en' => theme_edtech_get_slider_defaults($slot, 'en'),
            'ar' => theme_edtech_get_slider_defaults($slot, 'ar'),
        ];
        $copy = [];

        foreach ($fields as $field) {
            foreach ($languageorder as $lang) {
                $value = theme_edtech_get_slider_setting("slider_{$slot}_{$field}_{$lang}");
                if ($value !== false && $value !== '') {
                    $copy[$field] = $value;
                    break;
                }
            }
            if (!isset($copy[$field])) {
                $copy[$field] = $defaults[$languageorder[0]][$field];
            }
        }

        $slides[] = [
            'id'            => 'home-slide-' . $slot,
            'slot'           => $slot,
            'image'          => theme_edtech_get_slider_image_url($slot),
            'eyebrow'        => $copy['eyebrow'],
            'title'          => $copy['title'],
            'title_accent'   => $copy['accent'],
            'subtitle'       => $copy['subtitle'],
            'cta1_text'      => $copy['cta1'],
            'cta1_url'       => $copy['cta1_url'],
            'cta2_text'      => $copy['cta2'],
            'cta2_url'       => $copy['cta2_url'],
            'stats'          => $stats,
            'card_title'     => get_string('hero_card_title', 'theme_edtech'),
            'play_label'     => get_string('hero_play_intro', 'theme_edtech'),
            'badge_title'    => get_string('hero_badge', 'theme_edtech'),
            'badge_sub'      => get_string('hero_completion', 'theme_edtech'),
            'joined_title'   => get_string('hero_joined', 'theme_edtech'),
            'joined_sub'     => get_string('hero_this_week', 'theme_edtech'),
        ];
    }

    $total = count($slides);
    foreach ($slides as $index => &$slide) {
        $slide['index'] = $index;
        $slide['isactive'] = $index === 0;
        $slide['slide_number'] = $index + 1;
        $slide['status_label'] = get_string('slider_status', 'theme_edtech', [
            'current' => $index + 1,
            'total'   => $total,
        ]) . ': ' . strip_tags($slide['title'] . ' ' . $slide['title_accent']);
        $slide['indicator_label'] = get_string('slider_indicator', 'theme_edtech', $index + 1);
    }
    unset($slide);

    return $slides;
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

    // ── Recent courses (the three newest courses for the current language) ──
    $recentcourses = array_slice($courses, 0, 3);

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

    $slides = theme_edtech_get_frontpage_slides($stats);

    // ── Assemble full context ─────────────────────────────────────────────
    // One call — navbar + footer defined once in lib.php, used everywhere.
    return array_merge(theme_edtech_get_shared_context($OUTPUT, $PAGE, $CFG), [
        // Moodle standard
        'output'         => $OUTPUT,
        'bodyattributes' => $OUTPUT->body_attributes(),
        'courseurl'      => (new moodle_url('/course/'))->out(false),

        // Hero
        'hero' => [
            'slides'     => $slides,
            'has_slides' => !empty($slides),
        ],

        // Frontpage sections
        'categories'       => $categories,
        'hascategories'    => !empty($categories),
        'courses'          => $courses,
        'hascourses'       => !empty($courses),
        'recentcourses'    => $recentcourses,
        'hasrecentcourses' => !empty($recentcourses),
        'features'      => $features,
        'testimonials'  => $testimonials,

        // About Us section
        'about' => [
            'kicker'   => get_string('about_kicker', 'theme_edtech'),
            'title'    => get_string('about_title', 'theme_edtech'),
            'subtitle' => get_string('about_subtitle', 'theme_edtech'),
            'body'     => get_string('about_body', 'theme_edtech'),
            'more'     => get_string('about_more', 'theme_edtech'),
            'more_url' => '#about-us',
            'cards'    => [
                [
                    'icon'  => 'fa-eye',
                    'title' => get_string('about_vision_title', 'theme_edtech'),
                    'text'  => get_string('about_vision_text', 'theme_edtech'),
                ],
                [
                    'icon'  => 'fa-heart',
                    'title' => get_string('about_mission_title', 'theme_edtech'),
                    'text'  => get_string('about_mission_text', 'theme_edtech'),
                ],
                [
                    'icon'  => 'fa-compass',
                    'title' => get_string('about_method_title', 'theme_edtech'),
                    'text'  => get_string('about_method_text', 'theme_edtech'),
                ],
            ],
        ],
    ]);
}

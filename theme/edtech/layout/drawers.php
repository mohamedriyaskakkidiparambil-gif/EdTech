<?php
// theme/edtech/layout/drawers.php
// Extends Boost's drawers layout.
// Uses shared helpers from lib.php so navbar + footer context
// is defined in ONE place for all pages.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

// ── Block / drawer state ──────────────────────────────────────────────────
$addblockbutton = $OUTPUT->addblockbutton();

if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING') && get_user_preferences('behat_keep_drawer_closed') != 1) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks  = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$bodyattributes       = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

// ── Secondary navigation ──────────────────────────────────────────────────
$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav  = $PAGE->has_tablist_secondary_navigation();
    $moremenu    = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

// ── Renderer / activity header ────────────────────────────────────────────
$renderer      = $PAGE->get_renderer('core');
$header        = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions()
                           && !$PAGE->has_secondary_navigation();
$regionmainsettingsmenu  = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

// ── Shared header + footer context (one call, defined once in lib.php) ───────
$sharedctx = theme_edtech_get_shared_context($OUTPUT, $PAGE, $CFG);

$templatecontext = array_merge($sharedctx, [
    // sitename comes from $sharedctx → get_navbar_context() → $SITE->fullname
    // Do NOT override it here with shortname (causes case mismatch across pages)
    'output'                    => $OUTPUT,
    'sidepreblocks'             => $blockshtml,
    'hasblocks'                 => $hasblocks,
    'bodyattributes'            => $bodyattributes,
    'courseindexopen'           => $courseindexopen,
    'blockdraweropen'           => $blockdraweropen,
    'courseindex'               => $courseindex,
    'secondarymoremenu'         => $secondarynavigation ?: false,
    'forceblockdraweropen'      => $forceblockdraweropen,
    'regionmainsettingsmenu'    => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow'                  => $overflow,
    'headercontent'             => $headercontent,
    'addblockbutton'            => $addblockbutton,
]);

$PAGE->requires->js_call_amd('theme_edtech/navbar', 'init');
echo $OUTPUT->render_from_template('theme_edtech/drawers', $templatecontext);

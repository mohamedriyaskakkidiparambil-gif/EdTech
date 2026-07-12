<?php
// theme/edtech/config.php
// ─────────────────────────────────────────────────────────────────────────
// EdTech — Boost child theme.
// Only the frontpage layout is overridden here; all other layouts fall back
// to Boost automatically (Moodle checks parent chain when file is missing).
// ─────────────────────────────────────────────────────────────────────────

defined('MOODLE_INTERNAL') || die();

$THEME->name        = 'edtech';
$THEME->parents     = ['boost'];
$THEME->sheets      = [];
$THEME->usefallback = true;

// ── SCSS compilation pipeline ────────────────────────────────────────────
// Without $THEME->scss Moodle skips the entire pipeline (pre+post never run).
$THEME->scss = function ($theme) {
    return theme_edtech_get_main_scss_content($theme);
};
$THEME->prescsscallback     = 'theme_edtech_get_pre_scss';
$THEME->extrascsscallback   = 'theme_edtech_get_extra_scss';
$THEME->scss_compiler_class = 'theme_boost\\autoprefixer';

// ── Renderer factory ─────────────────────────────────────────────────────
$THEME->rendererfactory = 'theme_overridden_renderer_factory';

// ── Misc ─────────────────────────────────────────────────────────────────
$THEME->enable_dock      = false;
$THEME->requiredblocks   = '';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;

// ── Layouts ──────────────────────────────────────────────────────────────
// Only declare the frontpage layout — everything else inherits from Boost.
$THEME->layouts = [
    // Custom full-width layout — no sidebars, no drawers
    'frontpage' => [
        'file'    => 'frontpage.php',
        'regions' => [],
        'options' => ['nonavbar' => false, 'nofooter' => false],
    ],

    // ── All pages use EdTech branded header — nav links vary by role ─────
    // Admins see gold admin nav items; regular users see public nav items.
    // Role detection happens in theme_edtech_get_navbar_context() via is_siteadmin().
    'base'           => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'standard'       => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'course'         => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'coursecategory' => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'incourse'       => ['file' => 'drawers.php', 'regions' => ['side-pre']],
    'admin'          => ['file' => 'drawers.php', 'regions' => []],
    'report'         => ['file' => 'drawers.php', 'regions' => []],
    'mydashboard'    => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'mycourses'      => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'mypublic'       => ['file' => 'drawers.php', 'regions' => ['side-pre'], 'defaultregion' => 'side-pre'],
    'login'       => ['file' => 'login.php',   'regions' => []],
    'popup'       => ['file' => 'columns1.php', 'regions' => [], 'options' => ['nofooter' => true, 'nonavbar' => true]],
    'frametop'    => ['file' => 'columns1.php', 'regions' => [], 'options' => ['nofooter' => true]],
    'embedded'    => ['file' => 'embedded.php', 'regions' => []],
    'maintenance' => ['file' => 'maintenance.php', 'regions' => []],
    'print'       => ['file' => 'columns1.php', 'regions' => [], 'options' => ['nofooter' => true]],
    'redirect'    => ['file' => 'embedded.php', 'regions' => []],
    'secure'      => ['file' => 'secure.php',   'regions' => []],
];

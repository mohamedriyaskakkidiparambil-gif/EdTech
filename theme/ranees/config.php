<?php
// theme/ranees/config.php

defined('MOODLE_INTERNAL') || die();

$THEME->name        = 'ranees';
$THEME->parents     = ['boost'];
$THEME->sheets      = [];
$THEME->usefallback = true;

// This closure triggers SCSS compilation — without it Moodle skips the
// entire pipeline and pre/post callbacks never fire.
$THEME->scss = function($theme) {
    return theme_ranees_get_main_scss_content($theme);
};

$THEME->extrascsscallback   = 'theme_ranees_get_extra_scss';
$THEME->prescsscallback     = 'theme_ranees_get_pre_scss';
$THEME->scss_compiler_class = 'theme_boost\\autoprefixer';
$THEME->rendererfactory     = 'theme_overridden_renderer_factory';
$THEME->requiredblocks      = '';
$THEME->addblockposition    = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->enable_dock         = false;

$THEME->layouts = [
    'base' => [
        'file'    => 'drawers.php',
        'regions' => ['side-pre'],
    ],
    'standard' => [
        'file'    => 'drawers.php',
        'regions' => ['side-pre'],
    ],
    'course' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'coursecategory' => [
        'file'    => 'drawers.php',
        'regions' => ['side-pre'],
    ],
    'incourse' => [
        'file'    => 'drawers.php',
        'regions' => ['side-pre'],
    ],
    'frontpage' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'admin' => [
        'file'    => 'drawers.php',
        'regions' => ['side-pre'],
    ],
    'mydashboard' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mycourses' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['nonavbar' => false],
    ],
    'mypublic' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'login' => [
        'file'    => 'login.php',
        'regions' => [],
        'options' => ['nofooter' => false, 'nonavbar' => false],
    ],
    'popup' => [
        'file'    => 'columns1.php',
        'regions' => [],
        'options' => ['nofooter' => true, 'nonavbar' => true],
    ],
    'frametop' => [
        'file'    => 'columns1.php',
        'regions' => [],
        'options' => ['nofooter' => true],
    ],
    'embedded' => [
        'file'    => 'embedded.php',
        'regions' => [],
    ],
    'maintenance' => [
        'file'    => 'maintenance.php',
        'regions' => [],
    ],
    'print' => [
        'file'    => 'columns1.php',
        'regions' => [],
        'options' => ['nofooter' => true, 'nonavbar' => false],
    ],
    'redirect' => [
        'file'    => 'embedded.php',
        'regions' => [],
    ],
    'report' => [
        'file'    => 'drawers.php',
        'regions' => ['side-pre'],
    ],
    'secure' => [
        'file'    => 'secure.php',
        'regions' => [],
    ],
];

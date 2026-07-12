<?php
// theme/ranees/lib.php

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content — Boost's default preset which imports
 * Bootstrap and all Moodle core SCSS. Defining this is required so Moodle
 * triggers the full compilation pipeline (pre + main + post callbacks).
 */
function theme_ranees_get_main_scss_content($theme) {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
}

/**
 * Injected BEFORE Bootstrap compiles — variable overrides go here.
 * $primary here overrides every Bootstrap component that uses it.
 */
function theme_ranees_get_pre_scss($theme) {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/ranees/scss/pre.scss');
}

/**
 * Injected AFTER full compilation — custom rules on top of everything.
 * Do NOT use @import url() here — it crashes ScssPhp.
 * Load external fonts via templates/head.mustache instead.
 */
function theme_ranees_get_extra_scss($theme) {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/ranees/scss/post.scss');
}

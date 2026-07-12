<?php
// theme/edtech/layout/frontpage.php
// ─────────────────────────────────────────────────────────────────────────
// Custom full-width layout for the Moodle front page.
//
// How it works:
//   1. This file is declared in config.php as the 'frontpage' layout.
//   2. Moodle instantiates $OUTPUT (core_renderer) and $PAGE before running it.
//   3. We call theme_edtech_build_frontpage_context() (defined in lib.php)
//      which queries the DB for categories and courses and returns a plain array.
//   4. $OUTPUT->render_from_template() passes that array to Mustache.
//   5. Mustache renders theme_edtech/frontpage which includes all section partials.
// ─────────────────────────────────────────────────────────────────────────

defined('MOODLE_INTERNAL') || die();

// Moodle REQUIRES main_content() to be called in every layout file.
// It triggers the page rendering pipeline (blocks, notifications, etc.).
// Moodle requires the main-content placeholder to be present in the rendered
// layout. The template keeps it hidden because the custom front page owns the
// visible content and already has its own featured-courses section.
$maincontent = $OUTPUT->main_content();

$context = theme_edtech_build_frontpage_context($OUTPUT, $PAGE);
$context['maincontent'] = $maincontent;

$PAGE->requires->js_call_amd('theme_edtech/navbar', 'init');
echo $OUTPUT->render_from_template('theme_edtech/frontpage', $context);

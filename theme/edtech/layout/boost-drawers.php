<?php
// theme/edtech/layout/boost-drawers.php
// ─────────────────────────────────────────────────────────────────────────
// Used by admin, report, and base layouts.
// Delegates entirely to Boost's drawers layout — no EdTech topbar, navbar,
// or footer. Gives admin users the standard Moodle admin interface.
// ─────────────────────────────────────────────────────────────────────────

defined('MOODLE_INTERNAL') || die();

require($CFG->dirroot . '/theme/boost/layout/drawers.php');

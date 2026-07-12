<?php
// theme/edtech/classes/output/core_renderer.php
// ─────────────────────────────────────────────────────────────────────────
// Extends Boost's core_renderer.
//
// How it works:
//   Moodle's rendererfactory ('theme_overridden_renderer_factory') checks
//   each theme in the parent chain for a file at classes/output/core_renderer.php.
//   When found, it instantiates THIS class as the $OUTPUT object used everywhere.
//
// Add methods here to override specific HTML output (breadcrumbs, user menus,
// notifications, etc.) without touching templates.
// ─────────────────────────────────────────────────────────────────────────

namespace theme_edtech\output;

defined('MOODLE_INTERNAL') || die();

// Boost's renderer lives in theme_boost\output\core_renderer.
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Returns the site logo URL or null.
     * Used by navbar.mustache: {{output.get_compact_logo_url}}
     */
    public function get_compact_logo_url($maxwidth = 300, $maxheight = 50) {
        return parent::get_compact_logo_url($maxwidth, $maxheight);
    }

    /**
     * Returns true when the navbar logo should be rendered.
     * Used by navbar.mustache: {{#output.should_display_navbar_logo}}
     */
    public function should_display_navbar_logo() {
        return parent::should_display_navbar_logo();
    }
}

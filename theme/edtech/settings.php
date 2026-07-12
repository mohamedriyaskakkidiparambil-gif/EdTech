<?php
// theme/edtech/settings.php
// Admin settings page — Site administration → Appearance → Themes → EdTech

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // ── Contact (shown in topbar) ─────────────────────────────────────────
    $settings->add(new admin_setting_heading('theme_edtech_contact',
        get_string('configtitle', 'theme_edtech') . ' — Contact',
        ''
    ));

    $settings->add(new admin_setting_configtext('theme_edtech/contact_phone',
        get_string('setting_contact_phone', 'theme_edtech'),
        '', '+974 4412 3456', PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext('theme_edtech/contact_email',
        get_string('setting_contact_email', 'theme_edtech'),
        '', 'support@edtech.qa', PARAM_EMAIL
    ));

    // ── Hero stats ────────────────────────────────────────────────────────
    $settings->add(new admin_setting_heading('theme_edtech_stats',
        get_string('configtitle', 'theme_edtech') . ' — Hero Stats',
        ''
    ));

    $settings->add(new admin_setting_configtext('theme_edtech/stat_courses',
        get_string('setting_stat_courses', 'theme_edtech'),
        '', '15K+', PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext('theme_edtech/stat_instructors',
        get_string('setting_stat_instructors', 'theme_edtech'),
        '', '800+', PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext('theme_edtech/stat_students',
        get_string('setting_stat_students', 'theme_edtech'),
        '', '120K+', PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext('theme_edtech/stat_rating',
        get_string('setting_stat_rating', 'theme_edtech'),
        '', '4.9★', PARAM_TEXT
    ));

    // ── Social links ──────────────────────────────────────────────────────
    $settings->add(new admin_setting_heading('theme_edtech_social',
        get_string('configtitle', 'theme_edtech') . ' — Social Links',
        ''
    ));

    foreach (['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'] as $net) {
        $settings->add(new admin_setting_configtext(
            "theme_edtech/social_{$net}",
            get_string("setting_social_{$net}", 'theme_edtech'),
            '', '#', PARAM_URL
        ));
    }
}

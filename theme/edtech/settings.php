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

    // ── Homepage hero slider ──────────────────────────────────────────────
    $settings->add(new admin_setting_heading('theme_edtech_slider',
        get_string('slider_settings_heading', 'theme_edtech'),
        get_string('slider_settings_description', 'theme_edtech')
    ));

    $sliderdefaults = [
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

    foreach ($sliderdefaults as $slot => $defaultkeys) {
        $settings->add(new admin_setting_heading(
            "theme_edtech_slider_slot{$slot}",
            get_string('slider_slot', 'theme_edtech', $slot),
            ''
        ));

        $settings->add(new admin_setting_configcheckbox(
            "theme_edtech/slider_{$slot}_enabled",
            get_string('slider_enabled', 'theme_edtech'),
            '',
            1
        ));

        $settings->add(new admin_setting_configstoredfile(
            "theme_edtech/slider_{$slot}_image",
            get_string('slider_image', 'theme_edtech'),
            get_string('slider_image_description', 'theme_edtech'),
            'slideimage' . $slot,
            0,
            [
                'maxfiles' => 1,
                'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp'],
            ]
        ));

        foreach (['en', 'ar'] as $lang) {
            $langname = strtoupper($lang);
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_eyebrow_{$lang}",
                get_string('slider_eyebrow', 'theme_edtech') . " ({$langname})",
                '',
                get_string($defaultkeys['eyebrow'], 'theme_edtech', null, $lang),
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_title_{$lang}",
                get_string('slider_title', 'theme_edtech') . " ({$langname})",
                '',
                get_string($defaultkeys['title'], 'theme_edtech', null, $lang),
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_accent_{$lang}",
                get_string('slider_accent', 'theme_edtech') . " ({$langname})",
                '',
                get_string($defaultkeys['accent'], 'theme_edtech', null, $lang),
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configtextarea(
                "theme_edtech/slider_{$slot}_subtitle_{$lang}",
                get_string('slider_subtitle', 'theme_edtech') . " ({$langname})",
                '',
                get_string($defaultkeys['subtitle'], 'theme_edtech', null, $lang),
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_cta1_{$lang}",
                get_string('slider_cta1', 'theme_edtech') . " ({$langname})",
                '',
                get_string($defaultkeys['cta1'], 'theme_edtech', null, $lang),
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_cta2_{$lang}",
                get_string('slider_cta2', 'theme_edtech') . " ({$langname})",
                '',
                get_string($defaultkeys['cta2'], 'theme_edtech', null, $lang),
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_cta1_url_{$lang}",
                get_string('slider_cta1_url', 'theme_edtech') . " ({$langname})",
                '',
                '/course/',
                PARAM_URL
            ));
            $settings->add(new admin_setting_configtext(
                "theme_edtech/slider_{$slot}_cta2_url_{$lang}",
                get_string('slider_cta2_url', 'theme_edtech') . " ({$langname})",
                '',
                '#',
                PARAM_URL
            ));
        }
    }

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

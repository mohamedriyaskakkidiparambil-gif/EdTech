<?php
/**
 * Enable the Moodle settings required for students to create accounts and
 * self-enrol in courses. Safe to run repeatedly during container startup.
 */

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/enrollib.php');

// Email-based self-registration must be enabled as an authentication plugin
// and selected as the site's self-registration method.
$authplugins = array_values(array_filter(array_map('trim', explode(',', (string)get_config('', 'auth')))));
foreach (['manual', 'email'] as $requiredplugin) {
    if (!in_array($requiredplugin, $authplugins, true)) {
        $authplugins[] = $requiredplugin;
    }
}
set_config('auth', implode(',', $authplugins));
set_config('registerauth', 'email');

// Self enrolment must be available before it can be added to individual
// courses by an administrator or teacher.
$enrolplugins = array_values(array_filter(array_map('trim', explode(',', (string)get_config('', 'enrol_plugins_enabled')))));
foreach (['manual', 'self'] as $requiredplugin) {
    if (!in_array($requiredplugin, $enrolplugins, true)) {
        $enrolplugins[] = $requiredplugin;
    }
}
set_config('enrol_plugins_enabled', implode(',', $enrolplugins));

// Make newly created Self enrolment instances active by default and allow
// new learners to enrol. Existing instances are enabled by the one-time
// migration in the deployment task when the site is first configured.
set_config('status', ENROL_INSTANCE_ENABLED, 'enrol_self');
set_config('newenrols', 1, 'enrol_self');
set_config('defaultenrol', 1, 'enrol_self');

echo "Student account registration and self-enrolment are enabled.\n";

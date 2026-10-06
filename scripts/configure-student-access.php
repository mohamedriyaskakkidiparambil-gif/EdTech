<?php
/**
 * Enable the Moodle settings required for students to create accounts and
 * self-enrol in courses. Safe to run repeatedly during container startup.
 */

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/enrollib.php');

global $DB;

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
// new learners to enrol.
set_config('status', ENROL_INSTANCE_ENABLED, 'enrol_self');
set_config('newenrols', 1, 'enrol_self');
set_config('defaultenrol', 1, 'enrol_self');

// A global plugin setting is not enough for Moodle to show self-enrolment on
// a course. Every existing course also needs an enrol_self instance.
$selfplugin = enrol_get_plugin('self');
$createdinstances = 0;
$updatedinstances = 0;
$courses = $DB->get_records_select('course', 'id <> ?', [SITEID], 'id', 'id,fullname');
foreach ($courses as $course) {
    $instance = $DB->get_record('enrol', [
        'courseid' => $course->id,
        'enrol' => 'self',
    ], '*', IGNORE_MISSING);

    if (!$instance) {
        $fields = $selfplugin->get_instance_defaults();
        $fields['name'] = 'Self enrolment';
        $fields['status'] = ENROL_INSTANCE_ENABLED;
        $fields['customint6'] = 1;
        $selfplugin->add_instance($course, $fields);
        $createdinstances++;
        continue;
    }

    $changed = false;
    if ((int)$instance->status !== ENROL_INSTANCE_ENABLED) {
        $instance->status = ENROL_INSTANCE_ENABLED;
        $changed = true;
    }
    if ((int)$instance->customint6 !== 1) {
        $instance->customint6 = 1;
        $changed = true;
    }
    if ($changed) {
        $instance->timemodified = time();
        $DB->update_record('enrol', $instance);
        $updatedinstances++;
    }
}

echo "Student account registration and self-enrolment are enabled. "
    . "Created enrolment instances: {$createdinstances}; "
    . "updated enrolment instances: {$updatedinstances}.\n";

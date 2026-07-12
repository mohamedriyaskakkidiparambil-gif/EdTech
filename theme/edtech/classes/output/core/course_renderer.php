<?php
// Theme overrides for Moodle's public course catalogue renderer.

namespace theme_edtech\output\core;

defined('MOODLE_INTERNAL') || die();

class course_renderer extends \core_course_renderer {

    /**
     * Add the branded catalogue hero while retaining Moodle's native category output.
     */
    public function course_category($category) {
        global $DB;

        $coursecat = empty($category)
            ? \core_course_category::user_top()
            : ($category instanceof \core_course_category
                ? $category
                : \core_course_category::get(is_object($category) ? $category->id : $category));

        $isroot = empty($coursecat->id);
        $description = '';
        if (!$isroot) {
            $helper = new \coursecat_helper();
            $description = trim(strip_tags($helper->get_category_formatted_description($coursecat)));
        }

        $hero = $this->render_from_template('theme_edtech/catalog_hero', [
            'breadcrumbs' => $this->output->navbar(),
            'title' => $isroot ? get_string('catalog_title', 'theme_edtech') : $coursecat->get_formatted_name(),
            'description' => $description ?: get_string(
                $isroot ? 'catalog_subtitle' : 'catalog_category_subtitle',
                'theme_edtech'
            ),
            'count' => $isroot
                ? $DB->count_records_select('course', 'id <> ? AND visible = 1', [SITEID])
                : $coursecat->get_courses_count(),
            'countlabel' => get_string('courses'),
        ]);

        $content = $hero . parent::course_category($coursecat);
        if (!$isroot && !$coursecat->get_courses_count() && !$coursecat->get_children_count()) {
            $content .= $this->render_from_template('theme_edtech/catalog_empty', [
                'title' => get_string('catalog_empty_title', 'theme_edtech'),
                'description' => get_string('catalog_empty_description', 'theme_edtech'),
                'url' => (new \moodle_url('/course/index.php'))->out(false),
            ]);
        }

        return $content;
    }

    /**
     * Render a category as a visual navigation card while preserving Moodle's data attributes.
     */
    protected function coursecat_category(\coursecat_helper $chelper, $coursecat, $depth) {
        $description = trim(strip_tags($chelper->get_category_formatted_description($coursecat)));

        return $this->render_from_template('theme_edtech/catalog_category_card', [
            'id' => $coursecat->id,
            'depth' => $depth,
            'showcourses' => $chelper->get_show_courses(),
            'name' => $coursecat->get_formatted_name(),
            'description' => $description,
            'count' => $coursecat->get_courses_count(),
            'url' => (new \moodle_url('/course/index.php', ['categoryid' => $coursecat->id]))->out(false),
            'visible' => !empty($coursecat->visible),
        ]);
    }

    /**
     * Render one course as an EdTech catalogue card.
     */
    protected function coursecat_coursebox(\coursecat_helper $chelper, $course, $additionalclasses = '') {
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        if ($course instanceof \stdClass) {
            $course = new \core_course_list_element($course);
        }

        $summary = '';
        if ($course->has_summary()) {
            $summary = trim(strip_tags($chelper->get_course_formatted_summary($course, [
                'overflowdiv' => false,
                'noclean' => true,
                'para' => false,
            ])));
            if (\core_text::strlen($summary) > 155) {
                $summary = \core_text::substr($summary, 0, 155) . '...';
            }
        }

        $category = \core_course_category::get($course->category, IGNORE_MISSING);
        $url = (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false);

        return $this->render_from_template('theme_edtech/catalog_course_card', [
            'id' => $course->id,
            'classes' => trim($additionalclasses),
            'name' => $chelper->get_course_formatted_name($course),
            'plainname' => format_string($course->fullname),
            'summary' => $summary,
            'category' => $category ? $category->get_formatted_name() : get_string('courses'),
            'url' => $url,
            'image' => $this->catalog_course_image($course),
            'enrolmenticons' => $this->course_enrolment_icons($course),
            'visible' => !empty($course->visible),
        ]);
    }

    /**
     * Resolve an uploaded overview image or a local technology illustration.
     */
    private function catalog_course_image(\core_course_list_element $course): string {
        global $CFG;

        foreach ($course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                return \moodle_url::make_file_url(
                    "$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    false
                )->out(false);
            }
        }

        $haystack = \core_text::strtolower($course->fullname . ' ' . $course->shortname);
        $illustration = 'technology';
        $matches = [
            'javascript' => ['javascript', 'java script', 'tech-js'],
            'php' => ['php', 'tech-php'],
            'python' => ['python', 'tech-python'],
            'docker' => ['docker', 'container', 'tech-docker'],
            'ai' => ['artificial intelligence', 'machine learning', 'tech-ai'],
        ];
        foreach ($matches as $name => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($haystack, $keyword) !== false) {
                    $illustration = $name;
                    break 2;
                }
            }
        }

        return $this->image_url('catalog/' . $illustration, 'theme_edtech')->out(false);
    }
}

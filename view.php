<?php
require('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course module ID

$cm = get_coursemodule_from_id('aicodeassignment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$assignment = $DB->get_record('aicodeassignment', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

// Check time restrictions
$now = time();
if ($now < $assignment->timestart) {
    notice(get_string('notavailableuntil', 'mod_aicodeassignment', userdate($assignment->timestart)), new moodle_url('/course/view.php', ['id' => $course->id]));
}
if ($assignment->timeend > 0 && $now > $assignment->timeend) {
    notice(get_string('expired', 'mod_aicodeassignment'), new moodle_url('/course/view.php', ['id' => $course->id]));
}
//  var_dump($assignment->timestart, $assignment->timeend, $now);
//  die();
$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/aicodeassignment/view.php', ['id' => $id]);
$PAGE->set_title(format_string($assignment->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

// Template data
$templatecontext = [
    'name' => format_string($assignment->name),
    'intro' => format_module_intro('aicodeassignment', $assignment, $cm->id),
    'assignment' => []
];

// Decode AI-generated JSON
$json = json_decode($assignment->aigeneratedjson, true);

if (is_array($json)) {
    $templatecontext['assignment'] = [
        'title' => $json['title'] ?? '',
        'description' => $json['description'] ?? '',
        'input_format' => $json['input_format'] ?? '',
        'output_format' => $json['output_format'] ?? '',
        'test_cases' => []
    ];

    if (!empty($json['test_cases'])) {
        foreach ($json['test_cases'] as $tc) {
            $templatecontext['assignment']['test_cases'][] = [
                'input' => is_array($tc['input']) ? json_encode($tc['input']) : $tc['input'],
                'target' => isset($tc['target']) ? (is_array($tc['target']) ? json_encode($tc['target']) : $tc['target']) : '',
                'output' => is_array($tc['output']) ? json_encode($tc['output']) : $tc['output']
            ];
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_aicodeassignment/view', $templatecontext);
echo $OUTPUT->footer();

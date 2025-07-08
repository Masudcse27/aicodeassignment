<?php
// defined('MOODLE_INTERNAL') || die();
require('../../config.php');
require_once('lib.php');
require_once(__DIR__ . '/classes/form/submissionform.php');

use mod_aicodeassignment\api\codegrader;

$id = required_param('id', PARAM_INT); // Course module ID

$cm = get_coursemodule_from_id('aicodeassignment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$assignment = $DB->get_record('aicodeassignment', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/aicodeassignment/submit.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('submit', 'mod_aicodeassignment'));
$PAGE->set_heading(format_string($course->fullname));

// Pass id and assignmentid into the form
$mform = new \mod_aicodeassignment\form\submissionform(null, [
    'assignmentid' => $assignment->id,
    'id' => $id
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/aicodeassignment/view.php', ['id' => $id]));
} elseif ($data = $mform->get_data()) {
    $record = new stdClass();
    $record->assignmentid = $data->assignmentid;
    $record->userid = $USER->id;
    $record->code = $data->code;
    $record->language = $data->language;
    $record->timecreated = time();
    $record->timemodified = time();

    $DB->insert_record('aicodeassignment_submissions', $record);
    $json = json_decode($assignment->aigeneratedjson, true);
    $question = $json['description'] ?? '';
    $result = codegrader::evaluate_submission($question, $record->code, $record->language, $assignment->solutioncode, $record->language);
    if ($result) {
        $submissionid = $DB->get_field('aicodeassignment_submissions', 'id', [
            'assignmentid' => $assignment->id,
            'userid' => $USER->id
        ], IGNORE_MULTIPLE);

        $update = (object) [
            'id' => $submissionid,
            'grade' => $result['grade'],
            'aifeedback' => $result['feedback'],
            'timemodified' => time()
        ];
        $DB->update_record('aicodeassignment_submissions', $update);
        $grades = [
            $USER->id => [
                'userid' => $USER->id,
                'rawgrade' => $result['grade']? ($result['grade']/$assignment->grade)*100 : 0,
                'feedback' => $result['feedback'],
                'feedbackformat' => FORMAT_PLAIN
            ]
        ];

        aicodeassignment_grade_item_update($assignment, $grades);
    }
    redirect(
        new moodle_url('/mod/aicodeassignment/view.php', ['id' => $data->id]),
        get_string('submission_saved', 'mod_aicodeassignment'),
        3
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('submit', 'mod_aicodeassignment'));
$mform->display();
echo $OUTPUT->footer();

<?php
defined('MOODLE_INTERNAL')||die();
use mod_aicodeassignment\api\assignmentgenerate;
use moodle_exception;

function aicodeassignment_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    // Step 1: Call AI API
    $airesponse = assignmentgenerate::generate($data->prompt, $data->difficulty);
    // var_dump($airesponse);
    // die(); // Debugging line, remove in production
    // Step 2: Decode outer response (OpenRouter format)
    $responseData = json_decode($airesponse, true);
    if (
        empty($responseData['choices'][0]['message']['content']) ||
        !is_string($responseData['choices'][0]['message']['content'])
    ) {
        throw new moodle_exception('Failed to parse assignment from AI response.');
    }

    // Step 3: Clean and decode the assignment JSON (inner content)
    $rawjson = trim($responseData['choices'][0]['message']['content']);

    // Remove surrounding ``` or whitespace
    $rawjson = preg_replace('/^[`\s]*({.*})[\s`]*$/s', '$1', $rawjson);

    $json = json_decode($rawjson, true);
    if (!is_array($json) || empty($json['assignment'])) {
        throw new moodle_exception('Failed to parse assignment from AI response.');
    }

    $assignment = $json['assignment'];

    // Step 4: Save assignment info to main table
    // $data->name = $assignment['title'] ?? $data->name;
    $data->solutioncode = $assignment['solution_cpp'] ?? '';
    $data->aigeneratedjson = json_encode($assignment);

    $id = $DB->insert_record('aicodeassignment', $data);

    // Step 5: Save test cases to testcases table
    if (!empty($assignment['test_cases'])) {
        foreach ($assignment['test_cases'] as $tc) {
            if (!isset($tc['input']) || !isset($tc['output'])) {
                continue;
            }

            $testcase = new stdClass();
            $testcase->assignmentid = $id;

            // Support nested arrays or string input
            $testcase->inputdata = is_array($tc['input']) ? json_encode($tc['input']) : (string) $tc['input'];

            // If output is array, convert to JSON; otherwise string
            $testcase->expectedoutput = is_array($tc['output']) ? json_encode($tc['output']) : (string) $tc['output'];

            $testcase->ispublic = 1; // Visible to students

            $DB->insert_record('aicodeassignment_testcases', $testcase);
        }
    }

    // Step 6: Gradebook integration
    aicodeassignment_grade_item_update($data);

    return $id;
}




function aicodeassignment_update_instance($data, $mform) {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();

    $DB->update_record('aicodeassignment', $data);

    aicodeassignment_grade_item_update($data);

    return true;
}

function aicodeassignment_delete_instance($id) {
    global $DB;

    // Get the assignment instance
    if (!$assignment = $DB->get_record('aicodeassignment', ['id' => $id])) {
        return false;
    }

    require_once(__DIR__ . '/../../lib/gradelib.php');
    grade_update('mod/aicodeassignment', $assignment->course, 'mod', 'aicodeassignment', $assignment->id, 0, null, ['deleted' => 1]);

    // Delete related submissions and testcases
    $DB->delete_records('aicodeassignment_submissions', ['assignmentid' => $assignment->id]);
    $DB->delete_records('aicodeassignment_testcases', ['assignmentid' => $assignment->id]);

    // Delete the assignment record itself
    $DB->delete_records('aicodeassignment', ['id' => $assignment->id]);

    // Remove grade item from gradebook
    
    return true;
}

function aicodeassignment_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        default:
            return null;
    }
}

function aicodeassignment_grade_item_update($assignment, $grades = null) {
    require_once(__DIR__ . '/../../lib/gradelib.php');

    $item = [
        'itemname' => clean_param($assignment->name, PARAM_NOTAGS),
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => $assignment->grade,
        'grademin' => 0,
    ];

    return grade_update('mod/aicodeassignment', $assignment->course, 'mod', 'aicodeassignment', $assignment->id, 0, $grades, $item);
}

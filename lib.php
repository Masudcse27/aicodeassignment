<?php


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

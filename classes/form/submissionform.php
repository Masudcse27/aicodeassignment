<?php
namespace mod_aicodeassignment\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class submissionform extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $assignmentid = $this->_customdata['assignmentid'] ?? 0;
        $cmid = $this->_customdata['id'] ?? 0;

        // Code textarea
        $mform->addElement('textarea', 'code', get_string('yourcode', 'mod_aicodeassignment'), 'rows="15" cols="80"');
        $mform->setType('code', PARAM_RAW);
        $mform->addRule('code', null, 'required');

        // Language input
        $mform->addElement('text', 'language', get_string('language', 'mod_aicodeassignment'));
        $mform->setType('language', PARAM_TEXT);
        $mform->addRule('language', null, 'required');

        // Hidden fields
        $mform->addElement('hidden', 'assignmentid', $assignmentid);
        $mform->setType('assignmentid', PARAM_INT);

        $mform->addElement('hidden', 'id', $cmid); // course module ID
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('submit', 'mod_aicodeassignment'));
    }
}

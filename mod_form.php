<?php
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_aicodeassignment_mod_form extends moodleform_mod
{

    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $this->standard_intro_elements();

        $mform->addElement('textarea', 'prompt', get_string('prompt', 'aicodeassignment'), ['rows' => 5, 'cols' => 60]);
        $mform->addRule('prompt', null, 'required');
        $mform->setType('prompt', PARAM_RAW);

        $mform->addElement('select', 'difficulty', get_string('difficulty', 'aicodeassignment'), [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard',
        ]);
        $mform->setType('difficulty', PARAM_TEXT);

        // $mform->addElement('advcheckbox', 'restrictlanguage', get_string('restrictlanguage', 'aicodeassignment'));
        // $mform->setType('restrictlanguage', PARAM_INT);
        $mform->addGroup([
            $mform->createElement('advcheckbox', 'restrictlanguage', '')
        ], 'restrictlanguagegroup', get_string('restrictlanguage', 'aicodeassignment'), null, false);

        $mform->addElement('text', 'allowedlanguages', get_string('allowedlanguages', 'aicodeassignment'));
        $mform->setType('allowedlanguages', PARAM_TEXT);
        $mform->addHelpButton('allowedlanguages', 'allowedlanguages', 'aicodeassignment');
        $mform->hideIf('allowedlanguages', 'restrictlanguage', 'notchecked');

        // Start and end deadlines
        $mform->addElement('date_time_selector', 'timestart', get_string('timestart', 'aicodeassignment'));
        $mform->setDefault('timestart', time());

        $mform->addElement('date_time_selector', 'timeend', get_string('timeend', 'aicodeassignment'));
        $mform->setDefault('timeend', time() + 7 * 24 * 60 * 60);

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        if ($data['timeend'] <= $data['timestart']) {
            $errors['timeend'] = get_string('endbeforestart', 'aicodeassignment');
        }

        if (!empty($data['restrictlanguage']) && empty(trim($data['allowedlanguages']))) {
            $errors['allowedlanguages'] = get_string('allowedlanguagesrequired', 'aicodeassignment');
        }

        return $errors;
    }
}

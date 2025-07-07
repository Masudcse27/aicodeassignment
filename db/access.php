<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'mod/aicodeassignment:code_submit' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => ['student' => CAP_ALLOW],
    ],
];

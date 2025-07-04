<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // Show only to site admins
    $settings = new admin_settingpage('modsettingaicodeassignment', get_string('pluginname', 'mod_aicodeassignment'));

    // Add API Endpoint setting.
    $settings->add(new admin_setting_configtext(
        'mod_aicodeassignment/apiendpoint',
        get_string('apiendpoint', 'mod_aicodeassignment'),
        get_string('apiendpoint_desc', 'mod_aicodeassignment'),
        'https://openrouter.ai/api/v1/chat/completions',
    ));

    // Add API Key setting.
    $settings->add(new admin_setting_configtext(
        'mod_aicodeassignment/apikey',
        get_string('apikey', 'mod_aicodeassignment'),
        get_string('apikey_desc', 'mod_aicodeassignment'),
        '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',
    ));

    $ADMIN->add('modsettings', $settings);
}

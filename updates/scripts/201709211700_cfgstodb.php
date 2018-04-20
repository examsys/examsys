<?php

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2156')) {
    $configObject->set_setting('misc_company', $configObject->get('cfg_company'), Config::STRING);
    $configObject->set_setting('system_maintenance_mode', 0, Config::BOOLEAN);
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'rogo_version', '" . $configObject->get('rogo_version') . "', '" . Config::VERSION . "')";
    $updater_utils->execute_query($sql, false);
    // Need to remove old cfg line.
    $replace = "\$rogo_version = '" . $configObject->get('rogo_version') . "';";
    $updater_utils->replace_line($string, $replace, '', $cfg_web_root);
    // Clean value.
    $clean_cfg_summative_mgmt = param::clean($configObject->get('cfg_summative_mgmt'), param::BOOLEAN);
    if (is_null($clean_cfg_summative_mgmt) or $clean_cfg_summative_mgmt == '') {
      $clean_cfg_summative_mgmt = 0;
    }
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'cfg_summative_mgmt', " . $clean_cfg_summative_mgmt . ", '" . Config::BOOLEAN . "')";
    $updater_utils->execute_query($sql, false);
    $hostname = true;
    if ($configObject->get('cfg_client_lookup') === 'ipaddress') {
      $hostname = false;
    }
    $configObject->set_setting('system_hostname_lookup', $hostname, Config::BOOLEAN);
    $configObject->set_setting('system_academic_year_start', $configObject->get('cfg_academic_year_start'), Config::STRING);
    $configObject->set_setting('misc_search_leadin_length', $configObject->get('cfg_search_leadin_length'), Config::INTEGER);
    $configObject->set_setting('rpt_percent_decimals', $configObject->get('percent_decimals'), Config::INTEGER);
    $currenthofstee = $configObject->get('hofstee_defaults');
    $configObject->set_setting('stdset_hofstee_pass', array(
        'min_pass'=> $currenthofstee['pass'][0],
        'max_pass' => $currenthofstee['pass'][1],
        'min_fail' => $currenthofstee['pass'][2],
        'max_fail' => $currenthofstee['pass'][3]
        ), Config::ASSOC);
    $configObject->set_setting('stdset_hofstee_distinction', array(
        'min_pass'=> $currenthofstee['distinction'][0],
        'max_pass' => $currenthofstee['distinction'][1],
        'min_fail' => $currenthofstee['distinction'][2],
        'max_fail' => $currenthofstee['distinction'][3]
        ), Config::ASSOC);
    $configObject->set_setting('stdset_hofstee_whole_numbers', $configObject->get('hofstee_whole_numbers'), Config::BOOLEAN);
    $configObject->set_setting('summative_hour_warning', $configObject->get('cfg_hour_warning'), Config::INTEGER);
    $configObject->set_setting('system_install_type', $configObject->get('cfg_install_type'), Config::STRING);
    // Clean value.
    $clean_cfg_ims_enabled = param::clean($configObject->get('cfg_ims_enabled'), param::BOOLEAN);
    if (is_null($clean_cfg_ims_enabled) or $clean_cfg_ims_enabled == '') {
      $clean_cfg_ims_enabled = 0;
    }
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'cfg_ims_enabled', " . $clean_cfg_ims_enabled . ", '" . Config::BOOLEAN . "')";
    $updater_utils->execute_query($sql, false);
    $contact_count = 0;
    foreach ($configObject->get('emergency_support_numbers') as $number => $name) {
      $contact_count++;
      $configObject->set_setting('emergency_support_contact' . $contact_count, array(
          'name' => $name,
          'number' => $number
          ), Config::ASSOC);
    }
    $i = $contact_count;
    while ($i < 3) {
      $i++;
      $configObject->set_setting('emergency_support_contact' . $i, array(
          'name' => '',
          'number' => ''
          ), Config::ASSOC);
    }
    $configObject->set_setting('support_contact_email', array($configObject->get('support_email')), Config::EMAIL);
    $configObject->set_setting('api_oauth_access_lifetime', $configObject->get('cfg_oauth_access_lifetime'), Config::INTEGER);
    $configObject->set_setting('api_oauth_refresh_token_lifetime', $configObject->get('cfg_oauth_refresh_token_lifetime'), Config::INTEGER);
    $configObject->set_setting('api_oauth_always_issue_new_refresh_token', $configObject->get('cfg_oauth_always_issue_new_refresh_token'), Config::BOOLEAN);
    $configObject->set_setting('paper_autosave_settimeout', $configObject->get('cfg_autosave_settimeout'), Config::INTEGER);
    $configObject->set_setting('paper_autosave_frequency', $configObject->get('cfg_autosave_frequency'), Config::INTEGER);
    $configObject->set_setting('paper_autosave_retrylimit', $configObject->get('cfg_autosave_retrylimit'), Config::INTEGER);
    $configObject->set_setting('paper_autosave_backoff_factor', $configObject->get('cfg_autosave_backoff_factor'), Config::DOUBLE);
    $configObject->set_setting('summative_midexam_clarification', $configObject->get('midexam_clarification'), Config::CSV);
    $configObject->set_setting('system_password_expire', $configObject->get('cfg_password_expire'), Config::INTEGER);
    $configObject->set_setting('misc_editor_name', $configObject->get('cfg_editor_name'), Config::STRING);
    $updater_utils->record_update('rogo2156');
  }
}
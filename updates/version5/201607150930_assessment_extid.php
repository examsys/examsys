<?php

if ($updater_utils->check_version("6.2.0")) {
    if (!$updater_utils->has_updated('rogo1829_assessmentextid')) {
        
        $altersql = "ALTER TABLE properties ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `properties` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `properties` ADD COLUMN `externalsys` VARCHAR(255) NULL DEFAULT NULL AFTER `externalid`";
        $updater_utils->execute_query($altersql, true);
        
        $updater_utils->record_update('rogo1829_assessmentextid');
    }
    // Auth user need config perms.
    if (!$updater_utils->has_updated('rogo1829_config_perms_auth')) {
        // Database details.
        $dbname = $configObject->get('cfg_db_database');
        $cfg_web_host = $configObject->get('cfg_web_host');
        // User details.
        $cfg_db_username = $configObject->get('cfg_db_username');
        $permissions = "GRANT SELECT ON $dbname.config TO '$cfg_db_username'@'$cfg_web_host'";
        $updater_utils->execute_query($permissions, false);
        $updater_utils->record_update('rogo1829_config_perms_auth');
    }
    // New configs.
    if (!$updater_utils->has_updated('rogo1829_newconfigs')) {
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('summative_hide_external', 0, 'boolean');
        $configObject->set_setting('summative_warn_external', 0, 'boolean');
        // Existing file configs added to db config.
        $configObject->set_setting('cfg_lti_allow_module_self_reg', $configObject->get('cfg_lti_allow_module_self_reg'), 'boolean');
        $configObject->set_setting('cfg_lti_allow_staff_module_register', $configObject->get('cfg_lti_allow_staff_module_register'), 'boolean');
        $configObject->set_setting('cfg_lti_allow_module_create', $configObject->get('cfg_lti_allow_module_create'), 'boolean');
        $configObject->set_setting('lti_integration', $configObject->get('lti_integration'), 'string');
        $configObject->set_setting('lti_auth_timeout', $configObject->get('lti_auth_timeout'), 'integer');
        $configObject->set_setting('cfg_gradebook_enabled', $configObject->get('cfg_gradebook_enabled'), 'boolean');
        $configObject->set_setting('cfg_api_enabled', $configObject->get('cfg_api_enabled'), 'boolean');
        $updater_utils->record_update('rogo1829_newconfigs');
    }
    if (!$updater_utils->has_updated('rogo1829_updateconfigs')) {
      // Update config names.
      $updatesql = "UPDATE config set setting = 'summative_cohort_sizes' WHERE setting = 'cohort_sizes'";
      $updater_utils->execute_query($updatesql, true);
      $updatesql = "UPDATE config set setting = 'summative_max_sittings' WHERE setting = 'max_sittings'";
      $updater_utils->execute_query($updatesql, true);
      $updatesql = "UPDATE config set setting = 'paper_max_duration' WHERE setting = 'max_duration'";
      $updater_utils->execute_query($updatesql, true);
      $updatesql = "UPDATE config set setting = 'paper_timezones', type = 'timezones' WHERE setting = 'timezones'";
      $updater_utils->execute_query($updatesql, true);
      // New file config override setting.
      $new_lines = array("// Override db config settings with configs in this file?\n","\$file_config_override = true;\r\n");
      $target_line = '$cfg_api_enabled ';
      $updater_utils->add_line($string, '$file_config_override', $new_lines, 191, $cfg_web_root, $target_line, 1);
      $updater_utils->record_update('rogo1829_updateconfigs');
    }
}

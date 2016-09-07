<?php

if ($updater_utils->check_version("6.2.0")) {

    if (!$updater_utils->has_updated('rogo1876_internalreviwer')) {
        $dbname = $configObject->get('cfg_db_database');
        $cfg_web_host = $configObject->get('cfg_web_host');
        $cfg_db_internal_user = $dbname . '_int';
        $cfg_db_internal_passwd = gen_password(16);
    
        $createsql ="CREATE USER  '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "' IDENTIFIED BY '" . $cfg_db_internal_passwd . "'";
        $updater_utils->execute_query($createsql, true);
        // Grants
        $grantsql = array();
        $grantsql[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_log TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_searches TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".keywords_question TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".modules TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".modules_staff TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".options TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".papers TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".properties TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".questions TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".review_metadata TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".staff_help TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".users TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $dbname . ".access_log TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".properties_reviewers TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".keywords_link TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
            
        foreach ($grantsql as $sql) {
            $updater_utils->execute_query($sql, true);
        }
        // Add cron user to config file.
        $new_lines = array("// internal reviwer db user\n","\$cfg_db_internal_user = '$cfg_db_internal_user';\n", "\$cfg_db_internal_passwd = '$cfg_db_internal_passwd';\n");
        $target_line = '$cfg_db_inv_passwd';
        $updater_utils->add_line($string, '$cfg_db_internal_user', $new_lines, 28, $cfg_web_root, $target_line, -2);

        $updater_utils->record_update('rogo1876_internalreviwer');
    }
}
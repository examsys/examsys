<?php

if ($updater_utils->check_version('7.1.1')) {
    if (!$updater_utils->has_updated('rogo2767')) {
        // Add select grant to student user.
        $sql = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".toilet_breaks TO '" . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('rogo2767');
    }
}

<?php

if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo2510')) {
    // Add select grant to student user.
    $sql = "GRANT SELECT ON " . $configObject->get('cfg_db_database') . ".lti_user TO '". $configObject->get('cfg_db_student_user') . "'@'". $configObject->get('cfg_web_host') . "'";
    $updater_utils->execute_query($sql, false);
    // Add select grant to staff user.
    $sql = "GRANT SELECT ON " . $configObject->get('cfg_db_database') . ".lti_user TO '". $configObject->get('cfg_db_staff_user') . "'@'". $configObject->get('cfg_web_host') . "'";
    $updater_utils->execute_query($sql, false);
    $updater_utils->record_update('rogo2510');
  }
}
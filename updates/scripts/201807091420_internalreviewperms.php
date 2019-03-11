<?php
if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo282')) {
    $sql = "GRANT SELECT ON " . $configObject->get('cfg_db_database') . ".config TO '". $configObject->get('cfg_db_internal_user') . "'@'". $configObject->get('cfg_web_host') . "'";
    $updater_utils->execute_query($sql, false);
    $updater_utils->record_update('rogo282');
  }
}

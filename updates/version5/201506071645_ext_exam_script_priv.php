<?php

if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'sessions', $cfg_web_host)) {
  $sql = "GRANT SELECT ON " . $cfg_db_database . ".sessions TO '" . $cfg_db_external_user . "'@'" . $cfg_web_host . "'";
  $updater_utils->execute_query($sql, true);
}

if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'objectives', $cfg_web_host)) {
  $sql = "GRANT SELECT ON " . $cfg_db_database . ".objectives TO '" . $cfg_db_external_user . "'@'" . $cfg_web_host . "'";
  $updater_utils->execute_query($sql, true);
}



<?php
if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo2325')) {
    $sql = "UPDATE track_changes SET old = '********', new = '********' WHERE part = 'password'";
    $update = $mysqli->prepare($sql);
    $update->execute();
    $update->close();
    $updater_utils->record_update('rogo2325');
  }
}

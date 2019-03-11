<?php

if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo2367')) {
    $configObject->set_setting('lti_ssl_verifypeer', 1, Config::BOOLEAN);
    $configObject->set_setting('lti_ssl_verifyhost', 2, Config::INTEGER);
    $updater_utils->record_update('rogo2367');
  }
}
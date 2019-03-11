<?php

if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo2362')) {
    $filetypes = array();
    foreach (media_handler::SUPPORTED as $name => $type) {
      // Threejs disabled by default.
      if ($type === questiondata::THREED or $type === questiondata::ARCHIVE) {
        $filetypes[$name] = 0;
      } else {
        $filetypes[$name] = 1;
      }
    }
    $configObject->set_setting('system_mediatypes', $filetypes, Config::ASSOC);
    $configObject->set_setting('paper_threejs', 0, Config::BOOLEAN);
    $maxsize = ini_get('upload_max_filesize');
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $maxsize);
    $maxsize = preg_replace('/[^0-9\.]/', '', $maxsize);
    if ($unit) {
      // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
      $maxsize = round($maxsize * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
      $maxsize = round($maxsize);
    }
    $configObject->set_setting('system_maxmediasize', $maxsize, Config::INTEGER);
    $updater_utils->record_update('rogo2362');
  }
}
<?php

// Your code here

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */

$new_lines = array("\$cfg_web_host = '$cfg_db_host';    //default to the db host\n");
$target_line = '$cfg_db_host';
$updater_utils->add_line($string, '$cfg_web_host', $new_lines, 28, $cfg_web_root, $target_line, -5);
if(!isset($cfg_web_host)) {
  $cfg_web_host=$cfg_db_host;
}

<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is used to install Rogo.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 The University of Nottingham
 */
 
// Only run from the command line!
if (PHP_SAPI != 'cli') {
  die("Please run this script from the CLI!\n");
}

set_time_limit(0);

$error = PHP_EOL . 'For details about installing Rogo visit: ' . PHP_EOL . 'https://rogo-eassessment-docs.atlassian.net/wiki/pages/viewpage.action?pageId=491546';

$language = 'en';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'load_config.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'index.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'version5.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'errors.php';

// Lets look to see what arguments have been passed.
$options = 'hu:p:o::q::l::n::';
$longoptions = array(
  'help',
);

$optionslist = getopt($options, $longoptions);

$help = 'Rogo initialisation script options'
        . PHP_EOL . PHP_EOL . "-h, --help \t\tDisplay help"
        . PHP_EOL . PHP_EOL . "-u, --user, \t\tDatabase username"
        . PHP_EOL . PHP_EOL . "-p, --passwd, \t\tDatabase password"
        . PHP_EOL . PHP_EOL . "-o, --staff_help, \tLoad staff help (0/1, default 0)"
        . PHP_EOL . PHP_EOL . "-q, --student_help, \tLoad student help (0/1, default 0)"
        . PHP_EOL . PHP_EOL . "-l, --langpacks, \tLoad language packs (0/1, default 0)"
        . PHP_EOL . PHP_EOL . "-n, --npm, \t\tUpdate NPM library (0/1, default 1)";

if (isset($optionslist['h']) or isset($optionslist['help'])) {
  // Display some help information.
  cli_utils::prompt($help);
  exit(0);
}

$cfg_db_username = $optionslist['u'];
$databasepassword = $optionslist['p'];
$cfg_db_host = $configObject->get('cfg_db_host');
$databaseport = $configObject->get('cfg_db_port');
$cfg_db_database = $configObject->get('cfg_db_database');
$databasecharset = $configObject->get('cfg_db_charset');
$cfg_db_student_user  = $configObject->get('cfg_db_student_user');
$cfg_db_staff_user    = $configObject->get('cfg_db_staff_user');
$cfg_db_external_user = $configObject->get('cfg_db_external_user');
$cfg_db_inv_username  = $configObject->get('cfg_db_inv_user');

$cfg_web_host = $configObject->get('cfg_web_host');
if ($cfg_web_host == '') {
  $cfg_web_host = $cfg_db_host;
}

InstallUtils::$cli = true;

@$mysqli = new mysqli($cfg_db_host, $cfg_db_username, $databasepassword, $cfg_db_database, $databaseport);
if ($mysqli->connect_error == '') {
  $mysqli->set_charset($databasecharset);
} else {
  cli_utils::prompt('Unable to connect to database');
  exit(0);
}

// Set db object in config.
$configObject->set_db_object($mysqli);

if (isset($optionslist['o'])) {
  $update_staff_help = $optionslist['o'];
} else {
  $update_staff_help = 0;
}
if (isset($optionslist['q'])) {
  $update_student_help = $optionslist['q'];
} else {
  $update_student_help = 0;
}
if (isset($optionslist['l'])) {
  $update_langpacks = $optionslist['l'];
} else {
  $update_langpacks = 0;
}
if (isset($optionslist['n'])) {
  $update_npm = $optionslist['n'];
} else {
  $update_npm = 1;
}
// Ensure any caches are cleared.
if (function_exists('opcache_reset')) {
    opcache_reset();
}


$updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));
// Get the code version.
$version = $configObject->getxml('version');
// Get the installed version.
$old_version = $configObject->get('rogo_version');
if ($version == $old_version) {
  cli_utils::prompt('Nothing to update.');
  exit(0);
}
if ($updater_utils->check_version("6.3.0")) {
  cli_utils::prompt('Please upgrade via the user interface to version 6.3.X before using the command line updater.');
  exit(0);
}
// Get update file dir.
$migration_path = 'updates' . DIRECTORY_SEPARATOR . 'version5';
// Check pre-requisites.
InstallUtils::checkSoftware();
if (!InstallUtils::configFileIsWriteable()) {
  cli_utils::prompt($string['updatefromversion'] . ' ' . $old_version . ' to ' . $version);
  cli_utils::prompt($string['warning1']);
  cli_utils::prompt($string['warning2']);
  exit(0);
} elseif (!InstallUtils::configPathIsWriteable()) {
  cli_utils::prompt($string['updatefromversion'] . ' ' . $old_version . ' to ' . $version);
  cli_utils::prompt($string['warning3']);
  cli_utils::prompt($string['warning4']);
  exit(0);
}
// Backup the config file before proceeding.
$updater_utils->backup_file($cfg_web_root, $old_version);
// Update.
ob_start();
cli_utils::prompt($string['startingupdate']);
cli_utils::prompt("Starting at " . date("H:i:s"));
ob_flush();
flush();
$mysqli->autocommit(false);

// Run individual update files
$files = scandir($migration_path);
foreach ($files as $file) {
  if (StringUtils::ends_with($file, '.php')) {
    cli_utils::prompt($migration_path . '/' . $file);
    include $migration_path . '/' . $file;
    $mysqli->commit();
  }
}

$mysqli->commit();

// Update the staff online help files.
if ($update_staff_help) {
  $updater_utils->execute_query("TRUNCATE staff_help", false);
  $file = file_get_contents('install/staff_help.sql');
  $mysqli->multi_query($file);
  if ($mysqli->error) {
    cli_utils::prompt($string['showerror']);
    exit(0);
  }
  $ext = '';
  while ($mysqli->more_results()) {
    $mysqli->next_result();
    if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
  }
  try {
      // Ensure all help images are in the correct location.
      $staffhelp = rogo_directory::get_directory('help_staff');
      $staffhelp->create();
      $staffhelp->copy_from_default();
      // Fix path of help file images as may not be in root web dir.
      InstallUtils::correct_staff_path();
      cli_utils::prompt("LOADED staff_help: " . $ext);
  } catch (Exception $e) {
    cli_utils::prompt($e->getMessage());
  }
}
// Update the student online help files.
if ($update_student_help) {
  $updater_utils->execute_query("TRUNCATE student_help", false);

  $file = file_get_contents('install/student_help.sql');
  $mysqli->multi_query($file);
  if ($mysqli->error) {
    cli_utils::prompt($string['showerror']);
    exit(0);
  }
  $ext = '';
  while ($mysqli->more_results()) {
    $mysqli->next_result();
    if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
  }
  try {
      // Ensure all help images are in the correct location.
      $studenthelp = rogo_directory::get_directory('help_student');
      $studenthelp->create();
      $studenthelp->copy_from_default();
      // Fix path of help file images as may not be in root web dir.
      InstallUtils::correct_student_path();
      cli_utils::prompt("LOADED student_help: " . $ext);
  } catch (Exception $e) {
    cli_utils::prompt($e->getMessage());
  }
}
$mysqli->commit();

// Update language packs.
if ($update_langpacks) {
  InstallUtils::download_langpacks();
}

// Update npm and dependencies.
if ($update_npm) {
  try {
    $npm_method = npm_utils::INSTALL_NODEV;
    npm_utils::setup($npm_method);
  } catch (Exception $e) {
    cli_utils::prompt($e->getMessage());
  }
}

// Final housekeeping activities - put all updates above this line
$updated = $updater_utils->update_version($version, $string, $cfg_web_root);
if ($updated !== true) {
  cli_utils::prompt($string['couldnotwrite']);
}
$updater_utils->execute_query('FLUSH PRIVILEGES', false);
$updater_utils->execute_query('TRUNCATE sys_errors', false);
$mysqli->close();
cli_utils::prompt("Ended at " . date("H:i:s"));
$filter = FILTER_SANITIZE_STRING;
$options = array(
  'options' => array(
    'default' => null,
   ),
  'flags' => FILTER_FLAG_NO_ENCODE_QUOTES
);
cli_utils::prompt($string['finished']);
cli_utils::prompt($string['actionrequired']);
cli_utils::prompt(filter_var($string['readonly'], $filter, $options));

exit(0);
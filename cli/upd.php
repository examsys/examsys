<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file is used to install ExamSys.
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 The University of Nottingham
 */

// Only run from the command line!
if (PHP_SAPI != 'cli') {
    die("Please run this script from the CLI!\n");
}

set_time_limit(0);

$error = PHP_EOL . 'For details about installing ExamSys visit: ' . PHP_EOL . 'https://rogo-eassessment-docs.atlassian.net/wiki/pages/viewpage.action?pageId=491546';

$language = 'en';

$rogo_path = dirname(__DIR__);
if (!file_exists($rogo_path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
    echo 'ExamSys is not installed.' . $error;
    exit(0);
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'load_config.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'install.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . 'upgrade.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'errors.php';

// Lets look to see what arguments have been passed.
$options = 'hu:p:o::q::l::';
$longoptions = array(
  'help',
);

$optionslist = getopt($options, $longoptions);

$help = 'ExamSys initialisation script options'
    . PHP_EOL . PHP_EOL . "-h, --help \t\tDisplay help"
    . PHP_EOL . PHP_EOL . "-u, --user, \t\tDatabase username"
    . PHP_EOL . PHP_EOL . "-p, --passwd, \t\tDatabase password"
    . PHP_EOL . PHP_EOL . "-o, --staff_help, \tLoad staff help (0/1, default 0)"
    . PHP_EOL . PHP_EOL . "-q, --student_help, \tLoad student help (0/1, default 0)"
    . PHP_EOL . PHP_EOL . "-l, --langpacks, \tLoad language packs (0/1, default 0)";

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
// Ensure any caches are cleared.
if (function_exists('opcache_reset')) {
    opcache_reset();
}


$updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));
// Get the code version.
$version = $configObject->getxml('version');
// Get the installed version.
$old_version = $configObject->get_setting('core', 'rogo_version');

if ($version == $old_version) {
    cli_utils::prompt('Nothing to update.');
    exit(0);
}
if ($updater_utils->check_version('7.2.0')) {
    cli_utils::prompt('This version of ExamSys requires at least version 7.2.0 is installed prior to upgrade.');
    exit(0);
}
// Get update file dir.
$migration_path = 'updates' . DIRECTORY_SEPARATOR . 'scripts';
// Check pre-requisites.
try {
    requirements::check();
} catch (Exception $e) {
    cli_utils::prompt($e->getMessage());
    exit(0);
}
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
cli_utils::prompt($string['startingupdate']);
cli_utils::prompt('Starting at ' . date('H:i:s'));
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
    try {
        OnlineHelp::load_staff_help();
        cli_utils::prompt($string['staffloaded']);
    } catch (Exception $e) {
        if ($e->getMessage() === 'CANNOT_FIND') {
            cli_utils::prompt($string['logwarning2']);
        } else {
            cli_utils::prompt($string['logwarning1']);
        }
    }
}
// Update the student online help files.
if ($update_student_help) {
    try {
        $ext = OnlineHelp::load_student_help();
        cli_utils::prompt($string['studentloaded']);
    } catch (Exception $e) {
        if ($e->getMessage() === 'CANNOT_FIND') {
            cli_utils::prompt($string['logwarning4']);
        } else {
            cli_utils::prompt($string['logwarning3']);
        }
    }
}
$mysqli->commit();

// Update language packs.
if ($update_langpacks) {
    try {
        InstallUtils::download_langpacks();
        cli_utils::prompt($string['langsuccess']);
    } catch (Exception $e) {
        switch ($e->getMessage()) {
            case 'CANNOT_DOWNLOAD_XML':
                cli_utils::prompt($string['cannotdownloadxml']);
            case 'CANNOT_DOWNLOAD_ZIP':
                cli_utils::prompt($string['cannotdownloadzip']);
                break;
            default:
                cli_utils::prompt($string['cannotextract']);
                break;
        }
    }
}

// Final housekeeping activities - put all updates above this line
$configObject->set_setting('rogo_version', $version, Config::VERSION);
$updater_utils->execute_query('FLUSH PRIVILEGES', false);
$updater_utils->execute_query('TRUNCATE sys_errors', false);
$mysqli->close();
cli_utils::prompt('Ended at ' . date('H:i:s'));
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

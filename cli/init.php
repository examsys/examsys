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

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'autoload.inc.php';
autoloader::init();

$error = PHP_EOL . 'For details about installing ExamSys visit: ' . PHP_EOL . 'https://examsys-eassessment-docs.atlassian.net/wiki/pages/viewpage.action?pageId=491546';

if (!file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'settings.xml')) {
    cli_utils::prompt('settings.xml is requried to perform an install of ExamSys.' . $error);
    exit(0);
}

$language = 'en';

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'path_functions.inc.php';
$cfg_web_root = get_root_path();
// Ensure there is a trailing slash.
if (mb_substr($cfg_web_root, -1) !== '/') {
    $cfg_web_root .= '/';
}
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'install.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'timezones.php';

// Lets look to see what arguments have been passed.
$options = 'hu:p:s:t:n:';
$longoptions = array(
  'help',
);

$optionslist = getopt($options, $longoptions);

$help = 'ExamSys initialisation script options:'
    . PHP_EOL . "\t-h, --help \tDisplay help"
    . PHP_EOL . "\t-u \t\tDatabase username (required)"
    . PHP_EOL . "\t-p \t\tDatabase password (required)"
    . PHP_EOL . "\t-s \t\tDatabase host (required)"
    . PHP_EOL . "\t-t \t\tDatabase port (required)"
    . PHP_EOL . "\t-n \t\tDatabase name (required)";

$display_help = false;

if (
    !isset($optionslist['u']) or
    !isset($optionslist['p']) or
    !isset($optionslist['s']) or
    !isset($optionslist['t']) or
    !isset($optionslist['n'])
) {
    // A required command line argument is missing.
    $display_help = true;
}

if ($display_help or isset($optionslist['h']) or isset($optionslist['help'])) {
    // Display some help information.
    cli_utils::prompt($help);
    exit(0);
}

$databaseuser = $optionslist['u'];
$databasepassword = $optionslist['p'];
$databasehost = $optionslist['s'];
$databaseport = $optionslist['t'];
$databasename = $optionslist['n'];

// Ensure any caches are cleared.
if (function_exists('opcache_reset')) {
    opcache_reset();
}

try {
    InstallUtils::$cli = true;
    // Check if already installed.
    InstallUtils::checkDirPermissionsPre();
    InstallUtils::configFile();
    $configObject = Config::get_instance();
    $version = $configObject->getxml('version');
    // Load an verifiy settings.xml
    InstallUtils::loadSettings();
    // Check pre-requisites.
    try {
        requirements::check();
    } catch (Exception $e) {
        cli_utils::prompt($e->getMessage());
        exit(0);
    }
    // Install.
    InstallUtils::checkDirPermissionsPost();
    $args = array(
    'mysql_admin_user' => $databaseuser,
    'mysql_admin_pass' => $databasepassword,
    'mysql_db_host' => $databasehost,
    'mysql_db_port' => $databaseport,
    'mysql_db_name' => $databasename
    );
    InstallUtils::processForm($args);
} catch (Exception $e) {
    cli_utils::prompt($e->getMessage());
    cli_utils::prompt($error);
}
cli_utils::prompt('You should now remove settings.xml from this system.');
exit(0);

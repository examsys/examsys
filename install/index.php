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
* 
* Installation script for inital setup of Rogō.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

// Start class autoloading.
require_once dirname(__DIR__) . '/include/autoload.inc.php';
autoloader::init();

// check for PHP.
if ( false ) {
  ?>
  <!DOCTYPE html>
  <html>
  <head>
    <title>Error: PHP is Missing</title>
  </head>
  <body>
    <h2>Error: PHP is Missing</h2>
    <p>Rogō requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>
  </body>
  </html>
  <?php
  exit;
}

// The config class must be loaded for the new version checking code to work.
// It must be loaded before require_once '../include/path_functions.inc.php';
// As the config file (if it exists) can require that same file. Which causes
// a fatal error.
$configObject = Config::get_instance();

require_once '../include/path_functions.inc.php';
$cfg_web_root = get_root_path() . '/';
$cfg_root_path = ltrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $cfg_web_root), '/');

require_once dirname(__DIR__) . '/include/auth.inc';
$includes = array('install/index.php');
$language = LangUtils::getLang($cfg_web_root);
foreach ($includes as $file) {
  $lang_path = "{$cfg_web_root}lang/$language/" . $file;
  if (file_exists($lang_path)) {
    require $lang_path;
  }
}
require_once dirname(__DIR__) . '/include/timezones.php';
// Get the code version.
$version = $configObject->getxml('version');

set_time_limit(0);

//basic checks
InstallUtils::displayHeader();
//InstallUtils::checkHTTPS();
InstallUtils::checkSoftware();
InstallUtils::checkDirPermissionsPre();

//have we got a config file? exits if we do, as this is an install
InstallUtils::configFile();

//output form
if (isset($_POST['install'])) {
  InstallUtils::checkDirPermissionsPost();
  InstallUtils::processForm();
} else {
  InstallUtils::displayForm();
}

InstallUtils::displayfooter();
?>
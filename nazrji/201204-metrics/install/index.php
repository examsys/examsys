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
* Installation script for inital setup of Rogo.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

// check for PHP.
if ( false ) {
  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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

require '../include/path_functions.inc.php';
$cfg_web_root = get_root_path() . '/';
$cfg_root_path = ltrim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $cfg_web_root), '/');

require $cfg_web_root . 'classes/installutils.class.php';

//basic checks
InstallUtils::displayHeader();
//InstallUtils::checkHTTPS();
InstallUtils::checkSoftware();
InstallUtils::checkDirPermissionsPre();

//have we got a config file ?
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
<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Installation script for inital setup of TouchStone.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
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
    <h2>Error: PHP Missing</h2>
    <p>TouchStone requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>
  </body>
  </html>
  <?php
  exit;
}

require '../classes/installutils.class.php';

//basic checks
InstallUtils::displayHeader();
InstallUtils::checkHTTPS();
InstallUtils::checkSoftware();
InstallUtils::checkDirPermissions();

//have we got a config file ?
InstallUtils::configFile();

//output form
if(isset($_POST['install'])) {
  InstallUtils::processForm();
} else {
  InstallUtils::displayForm();
}
InstallUtils::displayfooter();

?>
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
 * A script to retrive a file specified by the parameters passed.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @package core
 */

require_once './include/staff_student_auth.inc';
require_once './include/errors.php';

// Get the request variables.
$type = check_var('type', 'REQUEST', false, true, true);
$filename = check_var('filename', 'REQUEST', false, true, true);
$forcedownload = check_var('forcedownload', 'REQUEST', false, true, true);
$forcedownload = !empty($forcedownload);

try {
  $directory = rogo_directory::get_directory($type);
} catch (directory_not_found $e) {
  send_404();
}

try {
  $directory->send_file($filename, $forcedownload);
} catch (file_not_found $e) {
  // The file does not exist or is not readable by the web server, exit.
  send_404();
}

/**
 * Send a not found response.
 */
function send_404() {
  if (substr(php_sapi_name(), 0, 3) == 'cgi') {
    header("Status: 404 Not Found");
  } else {
    header('HTTP/1.0 404 not found');
  }
  exit;
}

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
* Script to obtain module enrolements from Student Management System (SMS). Run via a cron job.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

// Only run from the command line!
if (PHP_SAPI != 'cli') {
  die("Please run this test from CLI!\n");
}

set_time_limit(0);

$path = str_replace('/admin', '', str_replace('\\', '/', dirname(__FILE__)));
if ($path == '') {
  $path = $_SERVER['DOCUMENT_ROOT'];
}
require_once $path . '/include/load_config.php';
require_once $path . '/include/auth.inc';
require_once $path . '/include/errors.inc';

$cron = new Sms_Cron();
$cron->process();

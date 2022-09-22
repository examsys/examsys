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
 *
 * Script for processing IMS Enterprise files to create/update users, schools, faculties, modules and enrolments
 *
 * @author Barry Oosthuizen <barry.oosthuizen@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use plugins\ims\ims_enterprise;

// Only run from the command line!
if (PHP_SAPI != 'cli') {
    die("Please run this test from CLI!\n");
}

set_time_limit(0);

require_once '../../include/load_config.php';
require_once '../../include/custom_error_handler.inc';

// Start class autoloading.
require_once '../../include/autoload.inc.php';
autoloader::init();

$configObject = \Config::get_instance();
if ($configObject->get_setting('core', 'cfg_ims_enabled')) {
    $mysqli = \DBUtils::get_mysqli_link(
        $configObject->get('cfg_db_host'),
        $configObject->get('cfg_db_sysadmin_user'),
        $configObject->get('cfg_db_sysadmin_passwd'),
        $configObject->get('cfg_db_database'),
        $configObject->get('cfg_db_charset'),
        $notice,
        $configObject->get('dbclass'),
        $configObject->get('cfg_db_port')
    );

    $ims_enterprise = new ims_enterprise($mysqli);
    $ims_enterprise->process();

    $mysqli->close();
}

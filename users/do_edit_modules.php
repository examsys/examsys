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
 * Edit a students modules
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/admin_auth.inc';

$mod_count = param::required('mod_count', param::TEXT, param::FETCH_POST);
$session = param::required('session', param::INT, param::FETCH_POST);
$userid = param::required('userID', param::INT, param::FETCH_POST);

for ($attempt = 1; $attempt <= 3; $attempt++) {
    // Clear the student of all modules.
    UserUtils::clear_student_modules_by_userID($userid, $session, $attempt, $mysqli);

    // Insert a record for each module.
    for ($i = 0; $i <= $mod_count; $i++) {
        $postmod = param::optional('mod' . $attempt . '_' . $i, '', param::TEXT, param::FETCH_POST);
        if ($postmod != '') {
            UserUtils::add_student_to_module($userid, $postmod, $attempt, $session, $mysqli, 0);
        }
    }
}
echo json_encode('SUCCESS');

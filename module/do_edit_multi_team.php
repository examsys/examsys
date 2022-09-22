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
 * Edit teams.
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/admin_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'REQUEST', true, false, true);

// Clear the team of all members.
UserUtils::clear_staff_modules_by_userID($userID, $mysqli);

// Insert a record for each team member.
for ($i = 0; $i < $_POST['module_no']; $i++) {
    if (isset($_POST["mod$i"]) and $_POST["mod$i"] != '') {
        UserUtils::add_staff_to_module($userID, $_POST["mod$i"], $mysqli);
    }
}

echo json_encode('SUCCESS');

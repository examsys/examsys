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
 * Edit team members.
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$moduleID = check_var('moduleID', 'POST', true, false, true);

$module_details = module_utils::get_full_details_by_ID($moduleID, $mysqli);

if (!$module_details) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

if (!$userObject->has_role(array('SysAdmin', 'Admin'))) {
    if ($module_details['add_team_members'] == 0) {
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
        exit();
    }
}

// Clear the team of all members.
UserUtils::clear_staff_modules_by_moduleID($moduleID, $mysqli);

// Insert a record for each team member.
$staffno = param::optional('staff_no', 0, param::INT, param::FETCH_POST);
for ($i = 0; $i < $staffno; $i++) {
    $staff = param::optional("staff$i", '', param::TEXT, param::FETCH_POST);
    if ($staff != '') {
        UserUtils::add_staff_to_module($staff, $moduleID, $mysqli);
    }
}

echo json_encode('SUCCESS');

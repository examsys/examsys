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
 * Authentication routine which permits Admin only access.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once 'staff_student_auth.inc';

if (!$userObject->has_role(array('SysAdmin', 'Admin'))) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['accessdenied'], $configObject->get('cfg_root_path') . '/artwork/exclamation_48.png', '#C00000', true, true);
}

// Get staff_modules.
$staff_modules = $userObject->get_staff_modules();

$types_array = array('Formative Self-Assessments', 'Progress Tests', 'Summative Exams', 'Surveys', 'OSCE Stations', 'Offline Papers', 'Peer Review');

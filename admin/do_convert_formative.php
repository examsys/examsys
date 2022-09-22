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
 * Changes a summative exam into a formativ quiz. Part of summative exam scheduling system.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/admin_auth.inc';
require_once '../include/errors.php';

$paperid = check_var('paperID', 'POST', true, false, true);

if (!Paper_utils::paper_exists($paperid, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Remove the record from scheduling.
$result = $mysqli->prepare('DELETE FROM scheduling WHERE paperID = ?');
$result->bind_param('i', $paperid);
$result->execute();
$result->close();

// Set start/end dates and the type to 0 (i.e. formative).
$now = date('Y-m-d H:i:s');
$assessment = new assessment($mysqli, $configObject);
$update_params = array(
    'start_date' => array('s', $now),
    'end_date' => array('s', $now),
    'paper_type' => array('s' , $assessment::TYPE_FORMATIVE)
);
$assessment->db_update_assessment($paperid, $update_params);
$mysqli->close();
echo json_encode('SUCCESS');

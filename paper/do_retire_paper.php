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
 * Set a paper as retired.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once  '../include/errors.php';

$paperID = check_var('paperID', 'POST', true, false, true);
$questions = param::required('questions', param::BOOLEAN, param::FETCH_POST);

if (!Paper_utils::paper_exists($paperID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode($notice->ajax_notice($string['pagenotfound'], $msg));
    exit();
}

$logger = new Logger($mysqli);

if ($questions) {
    $status_array = QuestionStatus::get_all_statuses($mysqli, $string);
    $retired_status_id = -1;
    // TODO: ask which retired status to use if there is more than one?
    foreach ($status_array as $status) {
        if ($status->get_retired()) {
            $retired_status_id = $status->id;
            break;
        }
    }

    if ($retired_status_id != -1) {
        $mysqli->autocommit(false);

        // Look up and retire the questions
        $result = $mysqli->prepare('SELECT question FROM papers WHERE paper = ?');
        $result->bind_param('i', $paperID);
        $result->execute();
        $result->store_result();
        $result->bind_result($question_id);
        while ($result->fetch()) {
            $stmt = $mysqli->prepare('UPDATE questions SET status=? WHERE q_id = ?');
            $stmt->bind_param('ii', $retired_status_id, $question_id);
            $stmt->execute();
            $stmt->close();

            $logger->track_change('Retire question', $question_id, $userObject->get_user_ID(), '', '', 'retired');
        }
        $result->close();

        $mysqli->commit();
        $mysqli->autocommit(true);
    }
}

// Retire the paper itself
$assessment = new assessment($mysqli, $configObject);
$now = date('Y-m-d H:i:s');
$update_params = array(
    'retired' => array('s', $now)
);
if (!$assessment->db_update_assessment($paperID, $update_params)) {
    echo json_encode('ERROR');
    exit();
}

$logger->track_change('paper', $paperID, $userObject->get_user_ID(), '', '', 'retired');

$mysqli->close();

echo json_encode('SUCCESS');

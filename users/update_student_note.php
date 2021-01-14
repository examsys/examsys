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
 * Add a note to a students file
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'REQUEST', true, false, true);
$paperID = check_var('paperID', 'REQUEST', false, false, true);

// Does the paper exist?
if (!is_null($paperID) and !Paper_utils::paper_exists($paperID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode(array('response' => 'ERROR', 'type' => $notice->ajax_notice($string['pagenotfound'], $msg)));
    exit();
}
// Does the student exist?
if (!UserUtils::userid_exists($userID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    echo json_encode(array('response' => 'ERROR', 'type' => $notice->ajax_notice($string['pagenotfound'], $msg)));
    exit();
}

$note_id = param::optional('note_id', '', param::INT, param::FETCH_POST);
$note = param::required('note', param::TEXT, param::FETCH_POST);
$calling = param::optional('calling', '', param::TEXT, param::FETCH_POST);

if ($note_id == '' or $note_id == '0') {
    StudentNotes::add_note($userID, $note, $paperID, $userObject->get_user_ID(), $mysqli);
} else {
    StudentNotes::update_note($note, $note_id, $mysqli);
}

echo json_encode(array('response' => 'SUCCESS', 'type' => $calling));

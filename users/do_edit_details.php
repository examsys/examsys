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
 * Update the user details
 *
 * @author Simon Wilkinson, Anthony Brown
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require_once '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'POST', true, false, true);

$errors = false;

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$username = param::optional('username', null, param::ALPHANUM, param::FETCH_POST);
$prev_username = param::optional('prev_username', null, param::ALPHANUM, param::FETCH_POST);

if ($username != $prev_username) {
    // Check new username is valid and is not already used. Overwriting usernames could screw up other accounts.
    if (!UserUtils::username_is_valid($username)) {
        $errors = $string['usernameinvalid'];
    } elseif (UserUtils::username_exists($username, $mysqli)) {
        $errors = $string['usernameexists'];
    }
}

if (!$errors) {
    $cfg_web_root = $configObject->get('cfg_web_root');

    if (!empty($_FILES['photofile']['name'])) {
        $photodirectory = rogo_directory::get_directory('user_photo');
        // First check if the user has an image already, if they do delete it.
        // This stops an image type of a different type blocking the display of the uploaded image.
        $student_photo = UserUtils::student_photo_exist($username);
        if ($student_photo !== false) {
            unlink($photodirectory->fullpath($student_photo));
        }
        // Add the file.
        $filename = $_FILES['photofile']['name'];
        $explode = explode('.', $filename);
        $count = count($explode) - 1;
        // Ensure the file extenstion is lower case or it will not load on some Operating systems.
        $file_ext = mb_strtolower($explode[$count]);

        if (!move_uploaded_file($_FILES['photofile']['tmp_name'], $photodirectory->fullpath($username . '.' . $file_ext))) {
            log_error($userObject->get_user_ID(), 'Edit User', 'Application Error', 'Error uploading user photo - error: ' . $_FILES['photofile']['error'], $_SERVER['PHP_SELF'], 49, '', null, null, null);
        }
    }

    $initials = '';
    $first_names = param::optional('first_names', null, param::TEXT, param::FETCH_POST);
    $first_names_array = explode(' ', $first_names);
    foreach ($first_names_array as $individual_name) {
        $initials .= trim(mb_substr($individual_name, 0, 1));
    }
    // Update 'users' table.
    $tmp_roles = param::optional('roles', null, param::TEXT, param::FETCH_POST);

    $gender = param::optional('gender', null, param::TEXT, param::FETCH_POST);
    if ($gender == '') {
        $gender = null;
    }

    $title = param::optional('title', null, param::TEXT, param::FETCH_POST);
    $surname = param::optional('surname', null, param::TEXT, param::FETCH_POST);
    $grade = param::optional('grade', null, param::TEXT, param::FETCH_POST);
    $year = param::optional('year', null, param::INT, param::FETCH_POST);
    $email = param::optional('email', null, param::EMAIL, param::FETCH_POST);
    $sid = param::optional('sid', null, param::TEXT, param::FETCH_POST);

    if (false === UserUtils::update_user($userID, $username, '', $title, $first_names, $surname, $email, $grade, $gender, $year, $tmp_roles, $sid, $mysqli, $initials)) {
        $errors = $string['unabletosaveuserdetails'];
    }
}
if (!$errors) {
    echo json_encode('SUCCESS');
} else {
    echo json_encode($errors);
}

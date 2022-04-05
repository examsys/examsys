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
 * Displays a summary of a particular paper. Initial screen called by a VLE and is used to launch start.php.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_student_auth.inc';
require_once '../include/errors.php';
require_once '../include/paper_security.php';
require_once '../include/toprightmenu.inc';


// Redirect Invigilators to their own areas.
if ($userObject->has_role('Invigilator')) {
    header('location: ../invigilator/');
    exit();
}

// Redirect Internal Reviewers to their own area.
if ($userObject->has_role('Internal Reviewer')) {
    header('location: ../reviews/');
    exit();
}

$id = check_var('id', 'GET', true, false, true, param::ALPHANUM);
$mode = param::optional('mode', '', param::ALPHA, param::FETCH_GET);

function is_timedate_ok($startdate, $enddate)
{
    if (time() < $startdate or time() > $enddate) {
        return false;
    } else {
        return true;
    }
}

function is_timedate_ok_and_within_15min($startdate, $enddate)
{
    if ((time() + (15 * 60)) < $startdate or time() > $enddate) {
        return false;
    } else {
        return true;
    }
}

function has_time_remaining($propertyObj, $remaining_time)
{
    if ($propertyObj->get_exam_duration() === null) {
        return true;
    }

    if ($remaining_time === false) {
        return true;
    }

    if ((int)$remaining_time === 0) {
        return false;
    }

    return true;
}

function calculate_duration($normal, $extra_time_mins, $special_needs_percentage)
{
    $mins = $normal;
    if ($extra_time_mins != null) {
        $mins += $extra_time_mins;
    }
    if ($special_needs_percentage != null) {
        $mins += ($normal / 100) * $special_needs_percentage;
    }
    return $mins;
}

$special_needs_percentage = 0;
$textsize = 100;
$font = 'Arial';

if ($userObject->is_special_needs()) {
    // Look up special_needs data
    $special_needs_percentage = $userObject->get_special_needs_percentage();
    $textsize = $userObject->get_textsize($textsize);
    $font = $userObject->get_font($font);
}

if ($userObject->is_temporary_account()) {
    $person = '<img src="../artwork/guest_account_16.png" width="16" height="16" alt="Guest User" /> ' . $string['guestaccount'] . ' (' . $userObject->get_temp_title() . ' ' . $userObject->get_temp_surname() . ')';
} else {
    $person = $userObject->get_title() . ' ' . $userObject->get_initials() . ' ' . $userObject->get_surname();
}
$total_random_mark = 0;
$total_marks = 0;

// Create paper object.
$propertyObj = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

if (!$propertyObj->isEnabled()) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['papertypenotenabled'], '/artwork/exclamation_48.png', '#C00000', true, true);
}
// Get lab information.
$current_address = NetworkUtils::get_client_address();
$lab_factory = new LabFactory($mysqli);
if ($lab_object = $lab_factory->get_lab_based_on_client($current_address)) {
    $lab_id   = $lab_object->get_id();
} else {
    $lab_id = null;
}

$property_id        = $propertyObj->get_property_id();
$paper_title        = $propertyObj->get_paper_title();
$total_random_mark  = $propertyObj->get_random_mark();
$total_marks        = $propertyObj->get_total_mark();
$navigation         = $propertyObj->get_bidirectional();
$paper_screens      = $propertyObj->get_max_screen();
$test_type          = $propertyObj->get_paper_type();
$paper_start        = $propertyObj->get_start_date();
$paper_end          = $propertyObj->get_end_date();
$timezone           = $propertyObj->get_timezone();
$fullscreen         = $propertyObj->get_fullscreen();
$marking            = $propertyObj->get_marking();
$labs               = $propertyObj->get_labs();
$rubric             = $propertyObj->get_rubric();
$exam_duration      = $propertyObj->get_exam_duration();
$exam_duration_sec  = $exam_duration * 60;
$calendar_year      = $propertyObj->get_calendar_year();
$sound_demo         = $propertyObj->get_sound_demo();
$password           = $propertyObj->get_password();
$modIDs             = array_keys($propertyObj->get_modules());
$deleted            = $propertyObj->get_deleted();
$remote = $propertyObj->getSetting('remote_summative');

// If OSCE paper or if the paper has been deleted we should exit as this is an invalid page.
if ($test_type == '4' or $deleted != null) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '/artwork/exclamation_48.png', '#C00000', true, true);
}

// If the start / end date has not been set yet we need to set $display_start_date to '' to prevent errors on hidden input variables.
if (empty($paper_start) or empty($paper_end)) {
    $display_start_date = '';
} else {
    // Adjust for timezones.
    $UK_time = new DateTimeZone('Europe/London');
    $target_timezone    = new DateTimeZone($timezone);
    $display_start_date = DateTime::createFromFormat('U', $paper_start, $UK_time);
    $display_end_date   = DateTime::createFromFormat('U', $paper_end, $UK_time);

    $display_start_date->setTimezone($target_timezone);
    $display_end_date->setTimezone($target_timezone);

    $tmp_cfg_long_date_time = str_replace('%', '', $configObject->get('cfg_long_date_time'));

    $display_start_date = $display_start_date->format($tmp_cfg_long_date_time);
    $display_end_date   = $display_end_date->format($tmp_cfg_long_date_time);
}
$previously_submitted = 0;

$low_bandwidth = 0;
if ($userObject->has_role('Student')) {
    // Check for additional password on the paper if remote summatives not in operation.
    if (!$remote) {
        check_paper_password($propertyObj->get_property_id(), $password, $string, $mysqli, true);
    }
    //Check this PC is registered for this exam
    $low_bandwidth = check_labs($test_type, $labs, $current_address, $password, $string, $mysqli);

    $attempt = check_modules($userObject, $modIDs, $calendar_year, $string, $mysqli);
}

$display_remaining_time = false;
$remaining_minutes = '';
$remaining_seconds = '';

/*
 * BP If the duration is set then create a timer to calculate and display the remaining time
 */
$extra_time = null;
$remaining_time = 0;
$log_metadata = new LogMetadata($userObject->get_user_ID(), $propertyObj->get_property_id(), $mysqli);
// $log_metadata->get_record will return true if this user has stared this exam. false otherwise
$exam_started = $log_metadata->get_record('', false);
$ipmismatch = false;

if ($exam_duration !== null) {
    if ($test_type == '2' and !$remote) {
        $student_object['special_needs_percentage'] = $special_needs_percentage;
        $student_object['user_ID']   = $userObject->get_user_ID();
        $log_lab_end_time = $propertyObj->getLogLabEndTime($lab_id);
        $log_extra_time   = new LogExtraTime($log_lab_end_time, $student_object, $mysqli);
        $extra_time_secs  = $log_extra_time->get_extra_time_secs();
        $extra_time_mins  = $extra_time_secs / 60;
        $summative_timer  = new SummativeTimer($log_extra_time);
        $remaining_time   = $summative_timer->calculate_remaining_time_secs();
        if ($remaining_time !== false) {
            $display_remaining_time = true;

            if ($exam_started == false and $remaining_time == 0) {
                // Sanity check if we have not started the exam but time remaing is 0
                // happens in summative exams if we have the start and end time set wider
                // then the paper duration e.g in multiple sittings
                $remaining_time = $exam_duration_sec + $extra_time_secs;
                $display_remaining_time = false;
            }
        }
        // Check current IP address with that of attempt in log.
        // Warn user that they need to log out if they are logged into mulitple devices in this exam.
        if ($current_address !== $log_metadata->get_ipaddress()) {
            if (!is_null($log_metadata->get_ipaddress())) {
                $ipmismatch = true;
            }
            if ($exam_started) {
                $log_metadata->set_ipaddress($current_address);
            }
        }
        $extra_time_mins    = $extra_time_secs / 60;
    } else {
        if ($test_type == '1' or $test_type == '2') {
            $display_remaining_time = true;
        }
        $studentID       = $userObject->get_user_ID();
        if ($remote) {
            $timer = new RemoteSummativeTimer($log_metadata, $exam_duration, $special_needs_percentage);
        } else {
            $timer = new Timer($log_metadata, $exam_duration, $special_needs_percentage);
        }
        $remaining_time  = $timer->calculate_remaining_time();

        // We are a remote summative.
        if ($test_type == '2') {
            // Check current IP address with that of attempt in log.
            // Warn user that they need to log out if they are logged into mulitple devices in this exam.
            if ($current_address !== $log_metadata->get_ipaddress()) {
                if (!is_null($log_metadata->get_ipaddress())) {
                    $ipmismatch = true;
                }
                if ($exam_started) {
                    $log_metadata->set_ipaddress($current_address);
                }
            }
        }
        $extra_time_mins = null;
    }

    $remaining_minutes = (int) ($remaining_time / 60);
    $remaining_seconds = (int) ($remaining_time % 60);
}

$render = new render($configObject);
$headerdata = array(
    'css' => array(
        '/css/user_index.css',
        '/css/html5.css',
        '/node_modules/mediaelement/build/mediaelementplayer.min.css',
    ),
    'scripts' => array(
        '/js/userindexinit.min.js',
    ),
);

$lang['title'] = $string['startscreen'];
$render->render($headerdata, $lang, 'header.html');
$icon_types = array('formative', 'progress', 'summative', 'survey');
$contentdata = array(
    'toprightmenu' => draw_toprightmenu(14),
    'papericon' => '../artwork/' . $icon_types[$test_type] . '.png',
    'papertitle' => $paper_title,
    'papertype' => $test_type,
    'rubric' => $rubric,
    'screens' => $paper_screens,
    'currentuser' => $person,
    'sounddemo' => '',
    'switch' => false,
    'issuelink' => '',
    'version' => $configObject->get_setting('core', 'rogo_version'),
    'photourl' => '',
);

// Display user photo if summative exam.
if ($test_type == 2) {
    $student_photo = UserUtils::student_photo_exist($userObject->get_username());
    $photodirectory = rogo_directory::get_directory('user_photo');
    if ($student_photo !== false) {
        $photo_size = getimagesize($photodirectory->fullpath($student_photo));
        $contentdata['photodimensions'] = $photo_size[3];
        $contentdata['photourl'] = $photodirectory->url($student_photo);
    }
}

if ($test_type != '2') {
    $contentdata['warn'] = false;
    if ((time() < $paper_start or time() > $paper_end) and !$userObject->has_role('External Examiner')) {
        $contentdata['warnavail'] = true;
    }
    if (empty($paper_start) or empty($paper_end)) {
        // The start / end date has not been set yet so display Availability: Not set to the user.
        $contentdata['availability'] = $string['notset'];
    } else {
        $contentdata['availability'] = $display_start_date . ' ' . $string['to'] . ' ' . $display_end_date;
    }
    $contentdata['timezone'] = '';
    if ($timezone != 'Europe/London') {
        $contentdata['timezone'] = ' (' . str_replace('_', ' ', $timezone) . ')';
    }
}
$contentdata['startdate'] = $display_start_date;


$contentdata['candidates']  = '';
foreach ($modIDs as $modID) {
    $mod_details = module_utils::get_full_details_by_ID($modID, $mysqli);
    if ($contentdata['candidates']  == '') {
        $contentdata['candidates']  = $mod_details['moduleid'];
    } else {
        $contentdata['candidates']  .= ', ' . $mod_details['moduleid'];
    }
}

// Display any metadata
$metadata_security = true;
$metadata_msg = '';
$metadata = Paper_utils::get_security_metadata($property_id, $mysqli);
if (!$userObject->is_temporary_account()) {         // Do not check metadata security if temporary account
    $i = 0;
    foreach ($metadata as $security_type => $security_value) {
        $contentdata['metadata'][$i]['warn'] = false;
        $contentdata['metadata'][$i]['type'] = $security_type;
        $contentdata['metadata'][$i]['value'] = $security_value;
        if (!$userObject->has_metadata($modIDs, $security_type, $security_value)) {
            $metadata_security = false;
            $metadata_msg = sprintf($string['metadata_msg'], $security_type, $security_value);
            $contentdata['metadata'][$i]['warn'] = true;
        }
        $i++;
    }
}

if ($navigation == 1) {
    $contentdata['navigation'] = $string['bidirectional'];
    $contentdata['navigationtooltip'] = $string['tooltip_bidirectional'];
} else {
    $contentdata['navigation'] = $string['unidirectional'];
    $contentdata['navigationtooltip'] = $string['tooltip_unidirectional'];
}

if ($test_type < 3) {
    $contentdata['marks'] = $total_marks;
    $contentdata['adjustedmarks'] = '';
    if ($marking == 1) {
        $contentdata['adjustedmarks'] = '(' . $string['adjusted'] . ' ' . number_format($total_random_mark, 2, '.', ',') . ')';
    }
}

if ($exam_duration) {
    $duration_mins = calculate_duration($exam_duration, $extra_time_mins, $special_needs_percentage);
    $contentdata['duration'] = StringUtils::nice_duration($duration_mins, $string);
} else {
    $contentdata['duration'] = '';
}

$contentdata['displaytimeremaining'] = $display_remaining_time;
if ($display_remaining_time === true) {
    $contentdata['timeremaining'] = $remaining_minutes . ' ' . $string['mins'] . ' ' . $remaining_seconds . ' ' . $string['secs'];
    $contentdata['notime'] = false;
    if ($remaining_time == 0) {
        $contentdata['notime'] = true;
    }
}

if ($sound_demo == '1') {
    $contentdata['sounddemo'] = $configObject->get('cfg_root_path') . '/paper/sound_demo.mp3';
}

if ($userObject->has_role(array('Staff', 'Admin', 'SysAdmin', 'External Examiner'))) {
    $start_available      = true;
    $remaining_available  = true;
    $metadata_security    = true;
} else {
    $start_available = false;
    $remaining_available = false;

    switch ($test_type) {
        case '0':
            $start_available = is_timedate_ok($paper_start, $paper_end);
            $remaining_available = true;
            break;
        case '1':
            $start_available = is_timedate_ok($paper_start, $paper_end);
            $remaining_available = has_time_remaining($propertyObj, $remaining_time);
            break;
        case '2':
            $start_available = is_timedate_ok_and_within_15min($paper_start, $paper_end);
            $remaining_available = has_time_remaining($propertyObj, $remaining_time);
            break;
        case '3':
            $start_available = is_timedate_ok($paper_start, $paper_end);
            $remaining_available = has_time_remaining($propertyObj, $remaining_time);
            break;
    }
}
$contentdata['sebrequired'] = false;
$contentdata['papernotavailable'] = false;
$contentdata['metadatasecurity'] = '';
$contentdata['waitforpassword'] = false;
$contentdata['donotstart'] = false;
if (!check_seb_headers($propertyObj->get_property_id(), $userObject, $string, $mysqli, false)) {
    $contentdata['sebrequired'] = true;
    $start_available = false;
} elseif ($start_available === false) {
    $contentdata['papernotavailable'] = true;
} elseif ($remaining_available === false) {
    $contentdata['timeexpired'] = true;
} elseif ($metadata_security === false) {
    $contentdata['metadatasecurity'] = $metadata_msg;
} elseif ($test_type == '2' and !$userObject->has_role('External Examiner')) {
    if ($remote) {
        $contentdata['waitforpassword'] = true;
    } else {
        $contentdata['donotstart'] = true;
    }
}

if ($test_type == 2) {
    $paper_utils = Paper_utils::get_instance();
    $paper_display = array();
    $paper_no = $paper_utils->get_active_papers($paper_display, array('1', '2'), $userObject, $mysqli, $property_id);
    if ($paper_no > 0) {
        $contentdata['switch'] = true;
    }
}

$display_date = '';
$contentdata['oktostart'] = false;
if ($start_available and $remaining_available and $metadata_security) {
    $contentdata['oktostart'] = true;
}

// Display a link for issue reporting for remote summative exams.
if ($test_type == '2' and $remote) {
    $link = $configObject->get_setting('core', 'summative_issuelink');
    if (!empty($link)) {
        $contentdata['issuelink'] = $link;
    }
}

if ($test_type != '2') {
    // Display previous attempts
    $contentdata['displaypreviousattempts'] = true;
    if (log::hasPreviousAttempts($property_id, $userObject->get_user_ID())) {
        $contentdata['previousattemptlink'] = $configObject->get('cfg_root_path') . '/users/previous.php?id=' . $id;
    } else {
        $contentdata['previousattemptlink'] = '';
    }
} else {
    $contentdata['displaypreviousattempts'] = false;
}

$render->render($contentdata, $string, 'paper/start.html');

// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$dataset['name'] = 'dataset';
$dataset['attributes']['ipmismatch'] = $ipmismatch;
$dataset['attributes']['id'] = $id;
$dataset['attributes']['mode'] = $mode;
$dataset['attributes']['fullscreen'] = $fullscreen;
$dataset['attributes']['remotesummative'] = $remote;
$render->render($dataset, array(), 'dataset.html');
$mysqli->close();

$render->render(array(), array(), 'footer.html');

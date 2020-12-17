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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/invigilator_auth.inc';
require_once '../include/errors.php';

$invigilation = new Invigilation();
$examstart = param::optional('start_exam_form', null, param::ALPHA, param::FETCH_POST);
if (!is_null($examstart)) {
    check_var('paper_id', 'POST', false, false, false);
}

// Do we want to view remote summative exams.
$remote = param::optional('remote', false, param::BOOLEAN, param::FETCH_GET);
$properties_list = array();

if (!$remote) {
    $current_address = NetworkUtils::get_client_address();

    $lab = new LabFactory($mysqli);

    $lab_object = $lab->get_lab_based_on_client($current_address);

    if ($lab_object !== false) {
        $lab_id = $lab_object->get_id();
        $room_name = $lab_object->get_name();

        $properties_list = PaperProperties::get_paper_properties_by_lab($lab_object, $mysqli);
    }
} else {
    $properties_list = PaperProperties::getRemoteSummativePaperProperties($mysqli);
}
$texteditorplugin = \plugins\plugins_texteditor::get_editor();
$renderpath = $texteditorplugin->get_render_paths();
$renderpath[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates';
$render = new render($configObject, $renderpath);
$headerdata = array(
    'css' => array(
        '/css/header.css',
        '/css/invigilator.css',
        '/css/popup_menu.css',
    ),
    'scripts' => array(
        '/js/invigilatorinit.min.js',
    ),
);
$headerdata['mathjax'] = false;
$headerdata['three'] = false;
$headerdata['mee'] = false;
$headerdata['texteditor'] = $texteditorplugin->get_header_file();
$editor = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
$headerdata['editor'] = $editor[0];
$lang = $string;
$lang['title'] = $string['invigilatoraccess'];
$render->render($headerdata, $lang, 'header.html');

$midexam_clarification = $configObject->get_setting('core', 'summative_midexam_clarification');

if (!$lab_object and !$remote) {
    $loggerObj = new Logger($mysqli);
    $loggerObj->record_access_denied($userObject->get_user_ID(), $string['fatalerrormsg0'], $string['unknowncomputer']);
    $data['emergency'] = $invigilation->emergencyNumbers();
    $lang['deniedtitle'] = $string['unknowncomputer'];
    $lang['deniedmsg'] = $string['unknowncomputermsg'];
    $render->render($data, $lang, 'invigilator/accessdenied.html');
} else {
    $papers = array();
    foreach ($properties_list as $property_object) {
        $id = $property_object->get_property_id();
        $papers[$id]['title'] = $property_object->get_paper_title();
        $exam_duration = $property_object->get_exam_duration();
        $papers[$id]['duration'] = StringUtils::nice_duration($exam_duration, $string);
        $papers[$id]['start_date'] = $property_object->get_display_start_time();
        $papers[$id]['year'] = $property_object->get_calendar_year();
        $papers[$id]['rubric'] = $property_object->get_rubric();
        $papers[$id]['modules'] = array_keys($property_object->get_modules());
        $papers[$id]['action'] = Url::fromGlobals();
        $papers[$id]['timing'] = false;
        $papers[$id]['remote'] = $remote;

        if (!$remote) {
            // Get modules for this paper and check if timing is allowed
            $papers[$id]['timing'] = module_utils::modules_allow_timing($papers[$id]['modules'], $mysqli);

            $papers[$id]['started'] = false;

            // Has 'Start' button been submitted

            $log_lab_end_time = new LogLabEndTime($lab_object->get_id(), $property_object, $mysqli);

            $end_datetime = $log_lab_end_time->get_session_end_date_datetime();

            if ($end_datetime == false) {
                $end_datetime = $log_lab_end_time->calculate_default_session_end_datetime();
            } else {
                $papers[$id]['started'] = true;
                $started_timestamp = $log_lab_end_time->get_started_timestamp();
                $papers[$id]['start_date'] = date($configObject->get('cfg_long_time_php'), $started_timestamp);
            }

            $disptimezone = new DateTimeZone($property_object->get_timezone());
            $paper_id = check_var('paper_id', param::FETCH_POST, false, false, true, param::INT);

            if ($papers[$id]['timing'] and !is_null($examstart)) {
                // Does the submitted paperID correspond it to the currently iterated paper?
                if ($paper_id === $id) {
                    $invigilator_id = $userObject->get_user_ID();
                    $end_datetime = $log_lab_end_time->save($invigilator_id);
                    $papers[$id]['started'] = true;
                    $papers[$id]['start_date'] = date($configObject->get('cfg_long_time_php'));
                }
            }
            $end = check_var('end_exam_form', param::FETCH_POST, false, false, true, param::ALPHA);

            if ($papers[$id]['timing'] and !is_null($end)) {
                // Does the submitted paperID correspond it to the currently iterated paper?

                if ($paper_id === $id) {
                    $hour = check_var('hour', param::FETCH_POST, false, false, true, param::INT);
                    $minute = check_var('minute', param::FETCH_POST, false, false, true, param::INT);
                    $end_timestamp = date_utils::getTimestampFromTime($hour, $minute, $disptimezone);
                    $exam_duration_s = $exam_duration * 60;

                    if (($end_timestamp - $started_timestamp) > $exam_duration_s) {
                        // End time is past start time + duration so is OK
                        $invigilator_id = $userObject->get_user_ID();
                        $time = 'PT' . $hour . 'H' . $minute . 'M';
                        $end_datetime = $log_lab_end_time->save($invigilator_id, $time);
                    } else {
                        $notice = UserNotices::get_instance();
                        $notice->display_notice(
                            $string['timeerror'],
                            sprintf($string['timeerrormsg'], $exam_duration),
                            '../artwork/summative_scheduling.png',
                            '#C00000',
                            false,
                            false
                        );
                    }
                }
            }

            $end_datetime->setTimezone($disptimezone);

            $papers[$id]['end_date'] = $end_datetime->format($configObject->get('cfg_long_time_php'));

            $papers[$id]['end_time_h'] = $end_datetime->format('H');
            $papers[$id]['end_time_m'] = $end_datetime->format('i');
        } else {
            $tmp_cfg_long_date_time = str_replace('%', '', $configObject->get('cfg_long_date_time'));
            $tmp_start_date  = DateTime::createFromFormat('U', $property_object->get_start_date());
            $tmp_end_date    = DateTime::createFromFormat('U', $property_object->get_end_date());
            $papers[$id]['start_date'] = $tmp_start_date->format($tmp_cfg_long_date_time);
            $papers[$id]['end_date'] = $tmp_end_date->format($tmp_cfg_long_date_time);
        }
        $papers[$id]['password'] = $property_object->get_decrypted_password();

        $papers[$id]['emergency'] = $invigilation->emergencyNumbers();

        $papers[$id]['clarification'] = in_array('invigilators', $midexam_clarification);
        if ($remote) {
            $log_lab_end_time = null;
        }
        $papers[$id]['students'] = $invigilation->getStudents(
            implode(',', $papers[$id]['modules']),
            $property_object,
            $log_lab_end_time,
            $papers[$id]['timing']
        );
    }

    if (count($papers) > 0) {
        $headdata['remote'] = $remote;
        $render->render($headdata, $lang, 'invigilator/header.html');
        $render->render($papers, $lang, 'invigilator/tabs.html');
        if ($remote) {
            $lang['preexamlist'] = $string['remotepreexamlist'];
            $lang['midexamlist'] = $string['remotemidexamlist'];
            $lang['postexamlist'] = $string['remotepostexamlist'];
        }
        $render->render($papers, $lang, 'invigilator/paper.html');
    } else {
        $data['emergency'] = $invigilation->emergencyNumbers();
        $lang['deniedtitle'] = $string['nopapersfound'];
        $lang['deniedmsg'] = $string['nopapersfoundmsg'];
        $render->render($data, $lang, 'invigilator/accessdenied.html');
    }
    $mysqli->close();

    // JS utils dataset.
    $miscdataset['name'] = 'dataset';
    $miscdataset['attributes']['clarification'] = 0;
    if (in_array('invigilators', $midexam_clarification)) {
        $miscdataset['attributes']['clarification'] = 1;
    }
    $miscdataset['attributes']['tab'] = param::optional('tab', '', param::INT, param::FETCH_GET);
    $render->render($miscdataset, array(), 'dataset.html');
}
$render->render(array(), array(), 'footer.html');

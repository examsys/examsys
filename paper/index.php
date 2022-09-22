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
 * This script is the summative exam homepage of ExamSys.
 * It takes the user details of the student together with the IP address
 * for the log and redirects to the correct paper.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_student_auth.inc';

// Redirect special users to their own areas.
if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {
    header('location: ../reviews/');
    exit();
} elseif ($userObject->has_role('Invigilator')) {
    header('location: ../invigilator/');
    exit();
}

function get_labs($mysqli, $lablist)
{
    $lab_list = array();
    if ($lablist != '') {
        $stmt = $mysqli->prepare("SELECT room_no, name FROM labs WHERE id IN ({$lablist})");
        $stmt->execute();
        $stmt->bind_result($room_no, $name);
        while ($stmt->fetch()) {
            $lab_list[] = ($room_no == '') ? $name : $room_no;
        }
        $stmt->close();
    }

    return $lab_list;
}

$logger = new Logger($mysqli);
$logger->record_access($userObject->get_user_ID(), 'Summative homepage', '/paper/');

$paper_utils = Paper_utils::get_instance();
$paper_display = array();
$paper_no = $paper_utils->get_active_papers($paper_display, array('1', '2'), $userObject, $mysqli);     // Get active Progress Tests and Summative Exams.

// Go straight to paper if only one exam and no password set (or remote summatives in operation).
$remote = false;
if ($paper_no == 1) {
    $properties = PaperProperties::get_paper_properties_by_id($paper_display[0]['id'], $mysqli, $string);
    $remote = $properties->getSetting('remote_summative');
}
if (
    $paper_no == 1 and
    ($paper_display[0]['password'] == '' or $remote)
) {
    header('location: user_index.php?id=' . $paper_display[0]['crypt_name']);
    exit();
} else {
    $render = new render($configObject);
    $headerdata = array(
        'css' => array(
            '/css/rogo_logo.css',
            '/css/index.css',
        ),
    );
    $lang['title'] = $string['exams'];
    $render->render($headerdata, $lang, 'header.html');

    require_once '../include/toprightmenu.inc';

    $indexheaderdata['toprightmenu'] = draw_toprightmenu();

    $render->render($indexheaderdata, $string, 'paper/indexheader.html');

    if ($paper_no == 0) {
        $data['staff'] = false;
        if ($userObject->has_role('Staff')) {
            $data['staff'] = true;
        }

        $current_address = NetworkUtils::get_client_address();
        $ip_info = $mysqli->prepare('SELECT name, room_no FROM (labs, client_identifiers) WHERE labs.id = client_identifiers.lab AND address = ?');
        $ip_info->bind_param('s', $current_address);
        $ip_info->execute();
        $ip_info->store_result();
        $ip_info->bind_result($computer_lab, $computer_lab_short);
        $ip_info->fetch();
        if ($ip_info->num_rows() == 0) {
            $computer_lab = $computer_lab_short = '<span style="color:#C00000">' . $string['unknownip'] . '</span>';
        }
        $computer_lab_short = ($computer_lab_short == '') ? $computer_lab : $computer_lab_short;
        $ip_info->close();
        $data['issues']['ip'] = array(
            'name' => $string['IPaddress'],
            'desc' => NetworkUtils::get_client_address() . ' ' . $computer_lab
        );
        $data['issues']['time'] = array(
            'name' => $string['Time/Date'],
            'desc' => date($configObject->get('cfg_long_datetime_php'))
        );
        if ($userObject->get_year() == '') {
            $data['issues'][$string['yearofstudy']] = array('name' => $string['yearofstudy'], 'desc' => 0);
        } else {
            $data['issues']['year'] = array('name' => $string['yearofstudy'], 'desc' => $userObject->get_year());
        }
        $last_cal_year = '';
        $info = $mysqli->prepare('SELECT moduleID, calendar_year FROM modules_student, modules WHERE modules.id = modules_student.idMod AND userID = ? ORDER BY calendar_year DESC, moduleID');
        $info->bind_param('i', $userObject->get_user_ID());
        $info->execute();
        $info->bind_result($user_moduleID, $user_calendar_year);
        $info->store_result();
        if ($info->num_rows() == 0) {
            $data['issues']['modules'] = array('name' => $string['Modules'], 'desc' => 0);
        } else {
            $mod = array();
            while ($info->fetch()) {
                if ($last_cal_year != $user_calendar_year) {
                    $mod[$user_calendar_year] = '';
                    $i = 0;
                }
                if ($i > 0) {
                    $mod[$user_calendar_year] .= ', ';
                }
                $mod[$user_calendar_year] .= $user_moduleID;
                $last_cal_year = $user_calendar_year;
                $i++;
            }
            $data['issues']['modules'] = array('name' => $string['Modules'], 'desc' => $mod);
        }
        $info->close();
        $userRolesArray = $userObject->list_user_roles();
        $users = array();
        foreach ($userRolesArray as $key => $ur) {
            if ($ur != 'Student') {
                $ur = str_replace('Demo', '', $ur);
                if ($ur != '') {
                    $users[$ur] = $string[mb_strtolower($ur)];
                    if ($key < count($userRolesArray) - 1) {
                        $users[$ur] .= ',';
                    }
                }
            } else {
                $users[$ur] = $string[mb_strtolower($ur)];
                if ($key < count($userRolesArray) - 1) {
                    $users[$ur] .= ',';
                }
            }
        }
        $data['issues']['user'] = array('name' => $string['UserRoles'], 'desc' => $users);

        $link = $configObject->get_setting('core', 'summative_issuelink');
        $data['summativeissuelink'] = $link;
        // Show staff a list of summative papers in the next 6 weeks with a link to test & preview
        if ($userObject->has_role('Staff')) {
            if (!isset($staff_modules)) {
                $staff_modules = $userObject->get_staff_modules();
            }
            $papers = array();
            foreach ($staff_modules as $idMod => $moduleID) {
                $paper_q = $mysqli->prepare(
                    "SELECT DISTINCT
                        properties.property_id,
                        MAX(papers.screen) AS screens,
                        properties.paper_title,
                        DATE_FORMAT(start_date,'" . $configObject->get('cfg_long_date_time') . "') AS display_start_date,
                        properties.exam_duration,
                        properties.crypt_name,
                        properties.fullscreen,
                        properties.labs
                    FROM properties
                    LEFT JOIN papers ON properties.property_id = papers.paper
                    LEFT JOIN properties_modules ON properties.property_id = properties_modules.property_id
                    WHERE paper_type='2' AND start_date > NOW()
                    AND start_date < DATE_ADD(NOW(), INTERVAL 42 DAY)
                    AND idMod = ?
                    AND deleted IS NULL
                    AND retired IS NULL
                    GROUP BY
                        properties.paper_title,
                        properties.property_id,
                        properties.exam_duration,
                        properties.crypt_name,
                        properties.fullscreen,
                        properties.labs
                    HAVING
                        MAX(screen) > 0
                    ORDER BY
                        properties.paper_type,
                        properties.paper_title"
                );
                $paper_q->bind_param('i', $idMod);
                $paper_q->execute();
                $paper_q->store_result();
                $paper_q->bind_result(
                    $property_id,
                    $screens,
                    $paper_title,
                    $start_date,
                    $exam_duration,
                    $crypt_name,
                    $fullscreen,
                    $labs
                );
                while ($paper_q->fetch()) {
                    $papers[$moduleID][] = array(
                        'id' => $property_id,
                        'screens' => $screens,
                        'title' => $paper_title,
                        'start_date' => $start_date,
                        'duration' => $exam_duration,
                        'crypt_name' => $crypt_name,
                        'fullscreen' => $fullscreen,
                        'labs' => $labs
                    );
                }
                $paper_q->close();
            }
            $data['futurepapers'] = count($papers);
            $data['future'] = array();
            if ($data['futurepapers'] > 0) {
                $staff_module = '';
                $hourwarning = $configObject->get_setting('core', 'summative_hour_warning');
                foreach ($papers as $moduleID => $paper_list) {
                    if ($moduleID != $staff_module) {
                        $staff_module = $moduleID;
                        $data['future'][$moduleID] = array();
                    }
                    foreach ($paper_list as $paper) {
                        $warnings = array();
                        $screen_plural = ($paper['screens'] > 1) ? 'screens' : 'screen';
                        $start_hour = mb_substr($paper['start_date'], 11, 2);
                        if (intval($start_hour) < $hourwarning) {
                            $warnings[] = sprintf($string['startwarning'], $hourwarning);
                        }

                        $labs = get_labs($mysqli, $paper['labs']);
                        if (count($labs) == 0) {
                            $warnings[] = $string['nolabswarning'];
                        }
                        if ($paper['duration'] == '' or $paper['duration'] == 0) {
                            $warnings[] = $string['nodurationwarning'];
                            $duration = '';
                        } else {
                            $duration = $paper['duration'] . $string['mins'];
                        }
                        $data['future'][$moduleID][$paper['crypt_name']] = array(
                            'fullscreen' => $paper['fullscreen'],
                            'title' => $paper['title'],
                            'screens' => $paper['screens'] . ' ' . ucfirst($string[$screen_plural]),
                            'date' => $paper['start_date'],
                            'duration' => $duration,
                            'warnings' => $warnings,
                            'labs' => $labs,
                            'currentlab' => $computer_lab_short,
                        );
                    }
                }
            }
        }
        $render->render($data, $string, 'paper/indexnopapers.html');
        exit;
    } else {
        $exams = array();
        for ($i = 0; $i < $paper_no; $i++) {
            $exams[$i]['cryptname'] = $paper_display[$i]['crypt_name'];
            $exams[$i]['title'] = $paper_display[$i]['paper_title'];
            $exams[$i]['password'] = false;
            if ($paper_display[$i]['password'] != '') {
                $exams[$i]['password'] = true;
            }
            if ($paper_display[$i]['completed'] == '') {
                $exams[$i]['completed'] = false;
                $exams[$i]['maxscreen'] = $paper_display[$i]['max_screen'];
                if ($paper_display[$i]['bidirectional'] == 1) {
                    $exams[$i]['direction'] = $string['Bidirectional'];
                } else {
                    $exams[$i]['direction'] = $string['Unidirectional'];
                }
            } else {
                $exams[$i]['completed'] = true;
            }
        }
        $render->render($exams, $string, 'paper/indexmultipapers.html');
    }
}
$mysqli->close();
$render->render(array(), array(), 'footer.html');
